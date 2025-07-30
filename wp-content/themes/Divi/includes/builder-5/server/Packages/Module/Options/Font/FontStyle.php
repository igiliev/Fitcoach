<?php
/**
 * Module: FontStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Font;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\TextShadow\TextShadowStyle;
use ET\Builder\FrontEnd\Module\Fonts;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * FontStyle class.
 *
 * This class has font style functionality.
 *
 * @since ??
 */
class FontStyle {

	/**
	 * Get font style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/FontStyle FontStyle} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector                 The CSS selector.
	 *     @type array         $selectors                Optional. An array of selectors for each breakpoint and state. Default `[]`.
	 *     @type callable      $selectorFunction         Optional. The function to be called to generate CSS selector. Default `null`.
	 *     @type array         $propertySelectors        Optional. The property selectors that you want to unpack. Default `[]`.
	 *     @type array         $attr                     An array of module attribute data.
	 *     @type array         $defaultPrintedStyleAttr  Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type array|bool    $important                Optional. Whether to apply "!important" flag to the style declarations.
	 *                                                   Default `false`.
	 *     @type bool          $asStyle                  Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                   Default `true`
	 *     @type string|bool   $headingLevel             Optional. HTML heading tag. Default `false`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $attrs_json               Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The font style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = FontStyle::style( $args );
	 *
	 * // Apply style with specific selectors and properties.
	 * $args = [
	 *     'selectors' => [
	 *         '.element1',
	 *         '.element2',
	 *     ],
	 *     'propertySelectors' => [
	 *         '.element1 .property1',
	 *         '.element2 .property2',
	 *     ]
	 * ];
	 * $style = FontStyle::style( $args );
	 * ```
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'selectorFunction'  => null,
				'important'         => false,
				'asStyle'           => true,
				'headingLevel'      => false,
				'orderClass'        => null,
				'attrs_json'        => null,
				'returnType'        => 'array',
			]
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$selector_function  = $args['selectorFunction'];
		$property_selectors = $args['propertySelectors'];
		$attr               = $args['attr'];
		$important          = $args['important'];
		$as_style           = $args['asStyle'];
		$heading_level      = $args['headingLevel'];
		$order_class        = $args['orderClass'];
		$return_as_array    = 'array' === $args['returnType'];
		$children           = $return_as_array ? [] : '';

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return $children;
		}

		// If attrs_json is provided use that, otherwise JSON encode the attributes array.
		$attr_json = null === $args['attrs_json'] ? wp_json_encode( $attr ) : $args['attrs_json'];

		// Enqueue font assets.
		$attr_font = $attr['font'] ?? [];

		if ( ! empty( $attr_font ) ) {
			// Check if a module attribute has a font family.
			$has_font_family = strpos( $attr_json, '"family"' );

			if ( $has_font_family ) {
				foreach ( $attr_font as $breakpoint => $states ) {
					foreach ( array_keys( $states ) as $state ) {
						$attr_value = ModuleUtils::use_attr_value(
							[
								'attr'       => $attr_font,
								'breakpoint' => $breakpoint,
								'state'      => $state,
								'mode'       => 'getAndInheritAll',
							]
						);

						$font_family = $attr_value['family'] ?? null;

						if ( $font_family ) {
							Fonts::add( $font_family );
						}
					}
				}
			}

			// headingLevel has no responsive / state support.
			$heading = is_string( $heading_level )
				? ( $attr['font']['desktop']['value']['headingLevel'] ?? $heading_level )
				: false;

			// Selector could contain multiple selectors separated by commas. Check each selector to see if it already
			// contains the headingLevel. If it does, we don't need to add the headingLevel to the selector.
			$selector_array       = explode( ',', $selector );
			$has_selector_heading = array_reduce(
				$selector_array,
				function ( $has_heading, $current_selector ) use ( $heading ) {
					return $current_selector && $heading
						? $has_heading || false !== strpos( $current_selector, $heading )
						: $has_heading;
				},
				false
			);

			// Generate correct selectors.
			// We need to make sure that headingLevel is not already included in the selector, ex: `h6.some-class`.
			$font_selector = ! $heading || $has_selector_heading
				? $selector
				: $selector . ' ' . $heading;

			// Statements selectors.
			$statements_selectors = ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $font_selector ] ];

			$children_statements = Utils::style_statements(
				[
					'selectors'               => $statements_selectors,
					'selectorFunction'        => $selector_function,
					'propertySelectors'       => $property_selectors['font'] ?? [],
					'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\Font\Font::style_declaration',
					'attr'                    => $attr['font'] ?? [],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['font'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['font'] ?? [] ),
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_statements && $return_as_array ) {
				array_push( $children, ...$children_statements );
			} elseif ( $children_statements ) {
				$children .= $children_statements;
			}
		}

		if ( ! empty( $attr['textShadow'] ) ) {
			$children_text_shadow = TextShadowStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'propertySelectors'       => $property_selectors['textShadow'] ?? [],
					'selectorFunction'        => $selector_function,
					'attrs_json'              => $attr_json,
					'attr'                    => $attr['textShadow'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['textShadow'] ?? [],
					'asStyle'                 => false,
					'important'               => is_bool( $important ) ? $important : ( $important['textShadow'] ?? false ),
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_text_shadow && $return_as_array ) {
				array_push( $children, ...$children_text_shadow );
			} elseif ( $children_text_shadow ) {
				$children .= $children_text_shadow;
			}
		}

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

}
