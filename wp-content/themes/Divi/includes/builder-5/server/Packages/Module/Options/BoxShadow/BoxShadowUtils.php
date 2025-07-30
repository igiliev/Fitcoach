<?php
/**
 * Module: BoxShadowUtils class.
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


/**
 * BoxShadowUtils class.
 *
 * This class provides utility functions for working with box shadow.
 *
 * @since ??
 */
class BoxShadowUtils {

	/**
	 * Retrieve the selectors with overlay.
	 *
	 * This function takes an array of attributes and returns an array of selectors with overlay.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array       $attr       The box shadow group attributes.
	 *     @type string      $selector   The CSS selector.
	 *     @type string      $breakpoint The breakpoint. One of `desktop`, `tablet`, or `phone`.
	 *     @type string      $state      The state. One of `active`, `hover`, `disabled`, or `value`.
	 *     @type string|null $orderClass Optional. The selector class name.
	 * }
	 *
	 * @return array The array of selectors with overlay.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'attr'       => $attr,
	 *         'selector'   => $selector,
	 *         'breakpoint' => $breakpoint,
	 *         'state'      => $state,
	 *         'orderClass' => $orderClass,
	 *     ];
	 *
	 *     $selectors = BoxShadowUtils::get_selectors_with_overlay( $args );
	 * ```
	 */
	public static function get_selectors_with_overlay( array $args ): array {
		$attr        = $args['attr'];
		$selectors   = array_map( 'trim', explode( ',', $args['selector'] ) );
		$order_class = $args['orderClass'] ?? null;
		$results     = [];

		foreach ( $attr as $breakpoint => $states ) {
			$states = array_keys( $states );

			foreach ( $states as $state ) {
				$parts = [];

				// Process the selector that delimited by comma partially.
				foreach ( $selectors as $selector ) {
					$parts[] = self::get_selector_with_overlay(
						[
							'attr'       => $attr,
							'breakpoint' => $breakpoint,
							'state'      => $state,
							'selector'   => $selector,
							'orderClass' => $order_class,
						]
					);
				}

				$results[ $breakpoint ][ $state ] = implode( ',', $parts );
			}
		}

		return $results;
	}

	/**
	 * Get the selector with overlay, adding suffix ` > .box-shadow-overlay` to the given selector when box shadow overlay is enabled.
	 *
	 * This function is used to generate a CSS selector with an overlay for box shadow. If the `'breakpoint'` and `'state'`
	 * arguments are provided, the function will first generate a selector using the `Utils::get_selector()` function
	 * and then append the overlay suffix if the box shadow overlay is enabled for the given attributes.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array       $attr       The box shadow group attributes.
	 *     @type string      $selector   The CSS selector.
	 *     @type string      $breakpoint Optional. The breakpoint of the selector.
	 *                                   One of `desktop`, `tablet`, or `phone`. Default `null`.
	 *     @type string      $state      Optional. The state of the selector.
	 *                                   One of `value`, `hover`, or `sticky`. Default `null`.
	 *     @type string|null $orderClass Optional. The selector class name.
	 * }
	 *
	 * @return string The generated CSS selector with overlay.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attr'       => $attr,
	 *     'selector'   => $selector,
	 *     'breakpoint' => $breakpoint,
	 *     'state'      => $state,
	 *     'orderClass' => $orderClass,
	 * ];
	 *
	 * $selectorWithOverlay = BoxShadowUtils::get_selector_with_overlay( $args );
	 * ```
	 */
	public static function get_selector_with_overlay( array $args ): string {
		$attr        = $args['attr'];
		$selector    = $args['selector'];
		$breakpoint  = $args['breakpoint'] ?? null;
		$state       = $args['state'] ?? null;
		$order_class = $args['orderClass'] ?? null;

		if ( $breakpoint && $state ) {
			$selector = Utils::get_selector(
				[
					'selectors'  => [
						'desktop' => [
							'value' => $selector,
						],
					],
					'breakpoint' => $breakpoint,
					'state'      => $state,
					'orderClass' => $order_class,
				]
			);
		}

		if ( self::is_overlay_enabled( $attr ) ) {
			$selector .= ' > .box-shadow-overlay';
		}

		return $selector;
	}

	/**
	 * Determines if the box shadow overlay is enabled based on the given box shadow group attributes.
	 *
	 * This function checks if the box shadow overlay is enabled by iterating through the attributes and
	 * checking if any of the box shadow styles are set to '`inner'`. If an inner box shadow style is found,
	 * the $overlay_enabled variable is set to `true`. Otherwise, it remains `false`.
	 *
	 * @since ??
	 *
	 * @param array $attr The box shadow group attributes.
	 *
	 * @return bool Returns `true` if the box shadow overlay is enabled, `false` otherwise.
	 *
	 * @example:
	 * ```php
	 *  $attr = [
	 *    'state1' => [
	 *      ['style' => 'none', 'position' => 'outer'],
	 *      ['style' => 'inner', 'position' => 'inner']
	 *    ],
	 *    'state2' => [
	 *      ['style' => 'none', 'position' => 'outer']
	 *    ],
	 *  ];
	 *  $is_enabled = BoxShadowUtils::is_overlay_enabled($attr);
	 *
	 *  // Returns true
	 * ```
	 */
	public static function is_overlay_enabled( array $attr ): bool {
		$box_shadow_presets = BoxShadow::presets();

		$overlay_enabled = false;

		foreach ( $attr as $state_values ) {
			foreach ( $state_values as $attr_value ) {
				if ( empty( $attr_value ) ) {
					continue;
				}

				$style    = $attr_value['style'] ?? 'none';
				$position = $attr_value['position'] ?? 'outer';

				if ( isset( $box_shadow_presets[ $style ] ) && 'inner' === $position ) {
					$overlay_enabled = true;
				}
			}
		}

		return $overlay_enabled;
	}

}
