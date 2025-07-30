<?php
/**
 * Module: BackgroundStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\GlobalData\GlobalData;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;
use ET\Builder\Packages\Module\Options\Background\BackgroundUtils;
use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;

/**
 * BackgroundStyle class.
 *
 * This class provides a set of background style options.
 *
 * @since ??
 */
class BackgroundStyle {

	/**
	 * Get background style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundStyle BackgroundStyle} in
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
	 *     @type array         $featureSelectors         Optional. The feature selectors that you want to unpack. Default `[]`.
	 *     @type array         $attr                     An array of module attribute data.
	 *     @type array         $defaultPrintedStyleAttr  Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type array|bool    $important                Optional. Whether to apply "!important" flag to the style declarations.
	 *                                                   Default `false`.
	 *     @type bool          $asStyle                  Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                   Default `true`.
	 *     @type string        $mode                     Optional. The mode of the style. Default `builder`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside sticky module or not. Default `false`.
	 *     @type string        $attrs_json               Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The background style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = BackgroundStyle::style( $args );
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
	 * $style = BackgroundStyle::style( $args );
	 * ```
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'selectorFunction'  => null,
				'propertySelectors' => [],
				'featureSelectors'  => null,
				'important'         => false,
				'asStyle'           => true,
				'mode'              => 'builder',
				'orderClass'        => null,
				'attrs_json'        => null,
				'returnType'        => 'array',
				'atRules'           => '',
			]
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$selector_function  = $args['selectorFunction'];
		$property_selectors = $args['propertySelectors'];
		$feature_selectors  = $args['featureSelectors'];
		$attr               = ModuleUtils::get_and_inherit_background_attr(
			[
				'attr' => $args['attr'] ?? [],
			]
		);
		$important          = $args['important'];
		$mode               = $args['mode'];
		$order_class        = $args['orderClass'];
		$return_as_array    = 'array' === $args['returnType'];
		$at_rules           = $args['atRules'];
		$children           = $return_as_array ? [] : '';

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return $children;
		}

		// If attrs_json is provided use that, otherwise JSON encode the attributes array.
		$attr_json = null === $args['attrs_json'] ? wp_json_encode( $attr ) : $args['attrs_json'];

		// Check if a module attribute has a color, gradient or image.
		$has_background = strpos( $attr_json, '"gradient"' ) || strpos( $attr_json, '"color"' ) || strpos( $attr_json, '"image"' );

		$children_background = $has_background ? Utils::style_statements(
			[
				'selectors'               => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'        => $selector_function,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr'] ?? [],
				'important'               => $important,
				'mode'                    => $mode,
				'declarationFunction'     => function( $props ) {
					return Background::style_declaration(
						array_merge(
							$props,
							[
								'defaultAttr' => [
									'gradient' => Background::$background_default_attr['gradient'] ?? null,
								],
							]
						)
					);
				},
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
				'atRules'                 => $at_rules,

			]
		) : null;

		if ( $children_background && $return_as_array ) {
			array_push( $children, ...$children_background );
		} elseif ( $children_background ) {
			$children .= $children_background;
		}

		// Check if a module attribute has a mask.
		$has_mask            = (bool) strpos( $attr_json, 'mask' );
		$children_background = $has_mask ? Utils::style_statements(
			[
				'selectors'            => $feature_selectors['mask']
											?? BackgroundUtils::get_background_mask_selectors( $selector ),
				'attr'                 => $attr,
				'important'            => $important,
				'mode'                 => $mode,
				'declarationFunction'  => function( $props ) {
					// Below we check if the mask color value is a global color value i.e a CSS variable.
					// If it is a global color value, we get the HEX/RGBA color value from the global colors store.
					// We cannot use CSS variables as dynamic values in SVGs (usage in `backgroundMaskStyleDeclaration`).
					// So we need to replace the CSS variable with the actual color value.
					// see https://chatgpt.com/share/66fc3ee3-b810-8004-80e4-660256d8361c and  https://stackoverflow.com/a/42331003.
					$background_mask_color = $props['attrValue']['mask']['color'] ?? '';

					if ( ! empty( $background_mask_color ) ) {
						$global_color_id = GlobalData::get_global_color_id_from_value( $background_mask_color );

						if ( $global_color_id ) {
							$mask_color = GlobalData::get_global_color_by_id( $global_color_id )['color'] ?? '';

							if ( ! empty( $mask_color ) ) {
								$props['attrValue']['mask']['color'] = $mask_color;
							}
						}
					}

					return Background::background_mask_style_declaration( $props );
				},
				'orderClass'           => $order_class,
				'isInsideStickyModule' => $is_inside_sticky_module,
				'returnType'           => $args['returnType'],
			]
		) : null;

		if ( $children_background && $return_as_array ) {
			array_push( $children, ...$children_background );
		} elseif ( $children_background ) {
			$children .= $children_background;
		}

		// Check if a module attribute has a pattern.
		$has_pattern         = (bool) strpos( $attr_json, 'pattern' );
		$children_background = $has_pattern ? Utils::style_statements(
			[
				'selectors'            => $feature_selectors['pattern']
											?? BackgroundUtils::get_background_pattern_selectors( $selector ),
				'attr'                 => $attr,
				'important'            => $important,
				'mode'                 => $mode,
				'declarationFunction'  => function( $props ) {
					// Below we check if the mask color value is a global color value i.e a CSS variable.
					// If it is a global color value, we get the HEX/RGBA color value from the global colors store.
					// We cannot use CSS variables as dynamic values in SVGs (usage in `backgroundMaskStyleDeclaration`).
					// So we need to replace the CSS variable with the actual color value.
					// see https://chatgpt.com/share/66fc3ee3-b810-8004-80e4-660256d8361c and  https://stackoverflow.com/a/42331003.
					$background_pattern_color = $props['attrValue']['pattern']['color'] ?? '';

					if ( ! empty( $background_pattern_color ) ) {
						$global_color_id = GlobalData::get_global_color_id_from_value( $background_pattern_color );

						if ( $global_color_id ) {
							$pattern_color = GlobalData::get_global_color_by_id( $global_color_id )['color'] ?? '';

							if ( ! empty( $pattern_color ) ) {
								$props['attrValue']['pattern']['color'] = $pattern_color;
							}
						}
					}

					return Background::background_pattern_style_declaration( $props );
				},
				'orderClass'           => $order_class,
				'isInsideStickyModule' => $is_inside_sticky_module,
				'returnType'           => $args['returnType'],
			]
		) : null;

		if ( $children_background && $return_as_array ) {
			array_push( $children, ...$children_background );
		} elseif ( $children_background ) {
			$children .= $children_background;
		}

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'children' => $children,
			]
		);
	}
}
