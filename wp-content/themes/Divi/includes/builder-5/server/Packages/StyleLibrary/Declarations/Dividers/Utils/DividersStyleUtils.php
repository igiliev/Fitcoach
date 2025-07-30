<?php
/**
 * DividersStyleUtils class
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Dividers\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * DividersStyleUtils is a helper class for Dividers style declaration.
 *
 * @since ??
 */
class DividersStyleUtils {

	/**
	 * Get CSS to flip the SVG horizontally and/or vertically.
	 *
	 * This function is equivalent to the JS function
	 * {@link /docs/builder-api/js/style-library/get-dividers-transform-css getDividersTransformCss} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param bool $horizontal Whether to Flip Horizontally.
	 * @param bool $vertical Whether to Flip Vertically.
	 *
	 * @return string Transform CSS Style.
	 */
	public static function transform_css( bool $horizontal, bool $vertical ): string {
		$flip_h = $horizontal ? '-1' : '1';
		$flip_v = $vertical ? '-1' : '1';

		return "scale($flip_h, $flip_v)";
	}

	/**
	 * Helper function to return CSS to Transform the SVG.
	 *
	 * Flip the SVG horizontally and/or vertically.
	 *
	 * This function is equivalent to the JS function:
	 * {@link /docs/builder-api/js/style-library/get-dividers-transform-state getDividersTransformState} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array  $value Value of the Transform option field.
	 * @param string $state One of `horizontal` or `vertical`.
	 *
	 * @return bool Whether the provided `$state` was found in the `$value`.
	 */
	public static function transform_state( array $value, string $state ): bool {
		$result = false;

		if ( empty( $value ) ) {
			return $result;
		}

		switch ( $state ) {
			case 'horizontal':
				$result = in_array( 'horizontal', $value, true );
				break;
			case 'vertical':
				$result = in_array( 'vertical', $value, true );
				break;
			default:
		}

		return $result;
	}

	/**
	 * Get background colors for a divider.
	 *
	 * The function will, using this component's id, get either the previous or
	 * the next sibling component's background color if set. If sibling is not
	 * found or sibling's background color is not set, it return an empty string(s).
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Dividers background color arguments.
	 *
	 *     @type string $sibling_background_attr Sibling component's background attribute.
	 *     @type string $module_background_attr  Module's background attribute.
	 *     @type string $default_color           Default color.
	 *     @type string $breakpoint              Breakpoint. One of `desktop`, `tablet`, `phone`.
	 *     @type string $state                   The state. One of `active`, `hover`, `disabled`, or `value`.
	 * }
	 * @return array {
	 *     Sibling component's background color and module's background color.
	 *
	 *     @type string $sibling_background_color Sibling component's background color. Default to `$args[default_color]`.
	 *     @type string $module_background_color  Module's background color. Default to `$args[default_color]`.
	 * }
	 */
	public static function get_dividers_background_colors( array $args ): array {
		$sibling_background_attr = $args['sibling_background_attr'];
		$module_background_attr  = $args['module_background_attr'];
		$default_color           = $args['default_color'];
		$breakpoint              = $args['breakpoint'];
		$state                   = $args['state'];

		// Bail early if siblingBackgroundAttr and moduleBackgroundAttr are not found.
		if ( ! $sibling_background_attr && ! $module_background_attr ) {
			return [
				'sibling_background_color' => $default_color,
				'module_background_color'  => $default_color,
			];
		}

		// Get sibling module's background attributes.
		$get_sibling_background_attr = ModuleUtils::use_attr_value(
			[
				'attr'       => $sibling_background_attr,
				'breakpoint' => $breakpoint,
				'state'      => $state,
				'mode'       => 'getAndInheritAll',
			]
		);

		// Get module background attributes.
		$get_module_background_attr = ModuleUtils::use_attr_value(
			[
				'attr'       => $module_background_attr,
				'breakpoint' => $breakpoint,
				'state'      => $state,
				'mode'       => 'getAndInheritAll',
			]
		);

		// Get the color value from siblingBackgroundAttr, otherwise fallback to section background color.
		$sibling_background_color = $get_sibling_background_attr['color'] ?? $default_color;

		// Get the color value from moduleBackgroundAttr, otherwise fallback to section background color.
		$module_background_color = $get_module_background_attr['color'] ?? $default_color;

		// If the found color is transparent, fallback to section background color.
		if ( 'rgba(0, 0, 0, 0)' === $sibling_background_color ) {
			$sibling_background_color = $default_color;
		}

		return [
			'sibling_background_color' => $sibling_background_color,
			'module_background_color'  => $module_background_color,
		];
	}
}
