<?php
/**
 * Module: BoxShadowStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\BoxShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\StyleLibrary\Declarations\BoxShadow\BoxShadow;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowUtils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * BoxShadowStyle class.
 *
 * This class provides methods for manipulating the box shadow style.
 *
 * @since ??
 */
class BoxShadowStyle {

	/**
	 * Get box shadow style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BoxShadowStyle BoxShadowStyle} in
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
	 *     @type bool          $useOverlay               Optional. Whether to generate the `selectors` and `selector` that  are suffixed
	 *                                                   with box shadow overlay element (` > .box-shadow-overlay`).
	 *                                                   Note: this is only applicable when the `selectors` params is empty.
	 *     @type string|null   $orderClass Optional.     The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The transform style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = BoxShadowStyle::style( $args );
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
	 * $style = BoxShadowStyle::style( $args );
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
				'useOverlay'        => false,
				'orderClass'        => null,
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
		$use_overlay        = $args['useOverlay'];
		$order_class        = $args['orderClass'];

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$attr_normalized = self::normalize_attr( $attr );

		if ( $use_overlay ) {
			if ( empty( $selectors ) ) {
				$selectors = BoxShadowUtils::get_selectors_with_overlay(
					[
						'attr'       => $attr_normalized,
						'selector'   => $selector,
						'orderClass' => $order_class,
					]
				);

				$selector = BoxShadowUtils::get_selector_with_overlay(
					[
						'attr'       => $attr_normalized,
						'selector'   => $selector,
						'orderClass' => $order_class,
					]
				);
			}
		}

		$children = Utils::style_statements(
			[
				'selectors'               => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'        => $selector_function,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr_normalized,
				'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr'] ?? [],
				'important'               => $important,
				'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\BoxShadow\BoxShadow::style_declaration',
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
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
	 * Normalize the box shadow attributes.
	 *
	 * Some attributes are not available in all breakpoints and states. This function
	 * will normalize the attributes by filling the missing attributes with the
	 * inherited values and presets.
	 *
	 * @since ??
	 *
	 * @param array $attr The array of attributes to be normalized.
	 * @return array The normalized array of attributes.
	 */
	public static function normalize_attr( array $attr ):array {
		$attr_normalized = $attr;

		if ( $attr_normalized ) {
			$presets = BoxShadow::presets();

			// Style attribute only available in desktop + value.
			$style = $attr_normalized['desktop']['value']['style'] ?? 'none';

			foreach ( $attr_normalized as $breakpoint => $states ) {
				foreach ( $states as $state => $values ) {
					// 1. Attributes from presets, overridden by attributes from desktop + value.
					// 2. Attributes from desktop + value, overridden by attributes from current breakpoint and state iteration.
					// 3. Attributes from current breakpoint and state iteration.
					if ( 'desktop' === $breakpoint && 'value' === $state ) {
						$attr_normalized[ $breakpoint ][ $state ] = array_merge(
							$presets[ $style ] ?? [],
							$values
						);
					} else {
						$inherit = ModuleUtils::use_attr_value(
							[
								'attr'       => $attr,
								'breakpoint' => $breakpoint,
								'state'      => $state,
								'mode'       => 'getAndInheritAll',
							]
						);

						$attr_normalized[ $breakpoint ][ $state ] = array_merge(
							$presets[ $style ] ?? [],
							$inherit
						);
					}
				}
			}
		}

		return $attr_normalized;
	}
}
