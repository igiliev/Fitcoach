<?php
/**
 * Module: FontHeaderStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FontHeaderGroup;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\Font\FontStyle;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * FontHeaderStyle class.
 *
 * This class represents the header font style.
 *
 * @since ??
 */
class FontHeaderStyle {

	/**
	 * Adjusts the font style component for the header group and its group tabs.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/FontHeaderStyle/ FontHeaderStyle} in
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
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $attrs_json               Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The font header style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = FontHeaderStyle::style( $args );
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
	 * $style = FontHeaderStyle::style( $args );
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
				'orderClass'        => null,
				'attrs_json'        => null,
				'returnType'        => 'string',
			]
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$selector_function  = $args['selectorFunction'];
		$property_selectors = $args['propertySelectors'];
		$attr               = $args['attr'];
		$important          = $args['important'];
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

		if ( ! empty( $attr['h1'] ) ) {
			$children_h1 = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = $selector_function ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						return ModuleUtils::generate_combined_selectors( $base_selector, 'h1' );
					},
					'propertySelectors'       => $property_selectors['h1'] ?? [],
					'attr'                    => $attr['h1'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['h1'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['h1'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'attrs_json'              => $attr_json,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_h1 && $return_as_array ) {
				array_push( $children, ...$children_h1 );
			} elseif ( $children_h1 ) {
				$children .= $children_h1;
			}
		}

		if ( ! empty( $attr['h2'] ) ) {
			$children_h2 = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = $selector_function ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						return ModuleUtils::generate_combined_selectors( $base_selector, 'h2' );
					},
					'propertySelectors'       => $property_selectors['h2'] ?? [],
					'attr'                    => $attr['h2'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['h2'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['h2'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'attrs_json'              => $attr_json,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_h2 && $return_as_array ) {
				array_push( $children, ...$children_h2 );
			} elseif ( $children_h2 ) {
				$children .= $children_h2;
			}
		}

		if ( ! empty( $attr['h3'] ) ) {
			$children_h3 = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = $selector_function ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						return ModuleUtils::generate_combined_selectors( $base_selector, 'h3' );
					},
					'propertySelectors'       => $property_selectors['h3'] ?? [],
					'attr'                    => $attr['h3'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['h3'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['h3'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'attrs_json'              => $attr_json,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_h3 && $return_as_array ) {
				array_push( $children, ...$children_h3 );
			} elseif ( $children_h3 ) {
				$children .= $children_h3;
			}
		}

		if ( ! empty( $attr['h4'] ) ) {
			$children_h4 = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = $selector_function ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						return ModuleUtils::generate_combined_selectors( $base_selector, 'h4' );
					},
					'propertySelectors'       => $property_selectors['h4'] ?? [],
					'attr'                    => $attr['h4'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['h4'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['h4'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'attrs_json'              => $attr_json,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_h4 && $return_as_array ) {
				array_push( $children, ...$children_h4 );
			} elseif ( $children_h4 ) {
				$children .= $children_h4;
			}
		}

		if ( ! empty( $attr['h5'] ) ) {
			$children_h5 = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = $selector_function ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						return ModuleUtils::generate_combined_selectors( $base_selector, 'h5' );
					},
					'propertySelectors'       => $property_selectors['h5'] ?? [],
					'attr'                    => $attr['h5'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['h5'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['h5'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'attrs_json'              => $attr_json,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_h5 && $return_as_array ) {
				array_push( $children, ...$children_h5 );
			} elseif ( $children_h5 ) {
				$children .= $children_h5;
			}
		}

		if ( ! empty( $attr['h6'] ) ) {
			$children_h6 = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = $selector_function ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						return ModuleUtils::generate_combined_selectors( $base_selector, 'h6' );
					},
					'propertySelectors'       => $property_selectors['h6'] ?? [],
					'attr'                    => $attr['h6'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['h6'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['h6'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'attrs_json'              => $attr_json,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_h6 && $return_as_array ) {
				array_push( $children, ...$children_h6 );
			} elseif ( $children_h6 ) {
				$children .= $children_h6;
			}
		}

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'asStyle'  => true,
				'children' => $children,
			]
		);
	}

}
