<?php
/**
 * Module: BorderStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Border;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * BorderStyle class
 *
 * @since ??
 */
class BorderStyle {

	/**
	 * Get border style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BorderStyle BorderStyle} in
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
	 *                                                   Default `true`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the blockquote element is inside a sticky module.
	 *     @type string        $attrs_json               Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The border style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = BorderStyle::style( $args );
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
	 * $style = BorderStyle::style( $args );
	 * ```
	 */
	public static function style( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'selectorFunction'  => null,
				'important'         => false,
				'asStyle'           => true,
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
		$order_class        = $args['orderClass'];

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$attr_normalized = self::normalize_attr( $attr );

		$border_style_map = [
			'border-style',
			'border-top-style',
			'border-right-style',
			'border-bottom-style',
			'border-left-style',
			'border-color',
			'border-top-color',
			'border-right-color',
			'border-bottom-color',
			'border-left-color',
			'border-width',
			'border-top-width',
			'border-right-width',
			'border-bottom-width',
			'border-left-width',
		];

		$border_radius_map = [
			'border-top-left-radius',
			'border-top-right-radius',
			'border-bottom-left-radius',
			'border-bottom-right-radius',
		];

		$border_map = array_merge( $border_style_map, $border_radius_map );

		$children = Utils::style_statements(
			[
				'selectors'                     => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'              => $selector_function,
				'propertySelectors'             => $property_selectors,
				'propertySelectorsShorthandMap' => [
					'border'        => $border_map,
					'border-radius' => $border_radius_map,
					'border-style'  => $border_style_map,
				],
				'attr'                          => $attr_normalized,
				'defaultPrintedStyleAttr'       => $args['defaultPrintedStyleAttr'] ?? [],
				'important'                     => $important,
				'declarationFunction'           => '\ET\Builder\Packages\StyleLibrary\Declarations\Border\Border::style_declaration',
				'orderClass'                    => $order_class,
				'isInsideStickyModule'          => $is_inside_sticky_module,
				'returnType'                    => $args['returnType'],
			]
		);

		return Utils::style_wrapper(
			[
				'attr'     => $attr_normalized,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

	/**
	 * Normalize the border attributes.
	 *
	 * Some attributes are not available in all breakpoints and states. This function
	 * will normalize the attributes by filling the missing attributes with the
	 * inherited values.
	 *
	 * @since ??
	 *
	 * @param array $attr The array of attributes to be normalized.
	 * @return array The normalized array of attributes.
	 */
	public static function normalize_attr( array $attr ):array {
		$attr_normalized = $attr;

		if ( $attr_normalized ) {
			foreach ( $attr_normalized as $breakpoint => $states ) {
				foreach ( $states as $state => $values ) {
					$values_normalized = $values;

					// Only apply when the breakpoint is not desktop or the state is not value.
					if ( 'desktop' !== $breakpoint || 'value' !== $state ) {
						$styles = $values_normalized['styles'] ?? [];

						if ( $styles ) {
							$inherit = ModuleUtils::use_attr_value(
								[
									'attr'       => $attr,
									'breakpoint' => $breakpoint,
									'state'      => $state,
									'mode'       => 'getAndInheritAll',
								]
							);

							foreach ( $styles as $style => $style_value ) {
								$width = $style_value['width'] ?? null;

								// If width is set, then get the inherited for the other sub-attributes.
								// In this case, we need to inherit the border color and border style.
								if ( $width ) {
									$values_normalized['styles'][ $style ] = array_merge(
										$inherit['styles'][ $style ] ?? [],
										$style_value
									);
								}
							}
						}
					}

					$attr_normalized[ $breakpoint ][ $state ] = $values_normalized;
				}
			}
		}

		return $attr_normalized;
	}

}
