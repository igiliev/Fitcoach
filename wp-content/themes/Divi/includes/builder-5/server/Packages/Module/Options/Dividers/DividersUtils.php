<?php
/**
 * Module Options: Dividers Utils Class.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Dividers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * DividersUtils class.
 *
 * Utility class wit functionality for handling classnames and dividers.
 *
 * @since ??
 */
class DividersUtils {

	/**
	 * A utility function for conditionally joining class names together.
	 *
	 * This function takes an array of attributes and checks if there is a divider present.
	 * If there is no divider, an empty string is returned. Otherwise, the class names
	 * are constructed based on the divider attributes and returned as a string.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/DividersClassnames dividersClassnames} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $attrs {
	 *     The array of attributes to be checked for dividers.
	 *
	 *     @type array $top     The array of attributes for the top divider.
	 *     @type array $bottom  The array of attributes for the bottom divider.
	 * }
	 *
	 * @return string The constructed class names, or an empty string if no divider is present.
	 *
	 * @example:
	 * ```php
	 * $attrs = [
	 *     'top' => [ 'divider' => true ],
	 *     'bottom' => []
	 * ];
	 * $result = classnames( $attrs );
	 *
	 * // Returns "section_has_divider et_pb_top_divider"
	 * ```
	 */
	public static function classnames( array $attrs ): string {
		$has_divider_top    = false !== self::has_divider( [ 'top' => $attrs['top'] ?? [] ] );
		$has_divider_bottom = false !== self::has_divider( [ 'top' => $attrs['bottom'] ?? [] ] );
		$has_divider        = $has_divider_top || $has_divider_bottom;

		// If there is no divider, return an empty string.
		if ( ! $has_divider ) {
			return '';
		}

		$class_names = [
			'section_has_divider',
		];

		if ( $has_divider_top ) {
			$class_names[] = 'et_pb_top_divider';
		}

		if ( $has_divider_bottom ) {
			$class_names[] = 'et_pb_bottom_divider';
		}

		return implode( ' ', $class_names );
	}

	/**
	 * Check if the attribute array has an enabled divider.
	 *
	 * This function checks if the given attribute array contains any divider settings and
	 * returns true if a divider is enabled, otherwise returns false.
	 *
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/HasDividers hasDividers} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $attr An array of attributes to check for dividers.
	 *
	 * @return bool `true` if a divider is enabled, `false` otherwise.
	 */
	public static function has_divider( array $attr ): bool {
		// Bail early if attr is not available.
		if ( empty( $attr ) ) {
			return false;
		}

		// Loop over the DividersGroupAttr.
		foreach ( $attr as $placement ) {
			// Bail early if placement is not available.
			if ( empty( $placement ) ) {
				continue;
			}

			// Loop over the placement (top|bottom).
			foreach ( $placement as $breakpoint ) {
				// Bail early if breakpoint is not available.
				if ( empty( $breakpoint ) ) {
					continue;
				}

				// Loop over the breakpoint.
				foreach ( $breakpoint as $state ) {
					// Bail early if state is not available.
					if ( empty( $state ) ) {
						continue;
					}

					// Check if divider is enabled.
					if ( isset( $state['style'] ) && 'none' !== $state['style'] ) {
						return true;
					}
				}
			}
		}

		// If we reached this point, it means no divider is enabled anywhere.
		return false;
	}

}
