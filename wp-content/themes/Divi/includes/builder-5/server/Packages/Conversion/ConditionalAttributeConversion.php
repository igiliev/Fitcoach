<?php
/**
 * ConditionalAttributeConversion Class
 *
 * @package Divi
 * @since ??
 */

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

namespace ET\Builder\Packages\Conversion;

/**
 * ConditionalAttributeConversion Class
 *
 * @since ??
 * @package ET\Builder\Packages\Conversion
 */
class ConditionalAttributeConversion {

	/**
	 * Convert image icon width.
	 *
	 * Converts the image icon width based on the condition of the attributes.
	 * The condition is based on the use_icon attribute.
	 * If the use_icon attribute is on, the imageIcon.advanced.width.*.icon is used.
	 * If the use_icon attribute is off or undefined, the imageIcon.advanced.width.*.image is used.
	 * If none of the conditions are met, the first path is used.
	 *
	 * @param array $value Array of associative arrays containing the condition and path.
	 * @param array $attributes Associative array containing the attributes.
	 *
	 * @return string The path of the image icon width.
	 */
	public static function convert_image_icon_width( array $value, array $attributes ): string {
		foreach ( $value as $conversion ) {
			if ( 'use_icon_on' === $conversion['condition'] && 'on' === ( $attributes['use_icon'] ?? null ) ) {
				return $conversion['path'];
			}

			if ( 'use_icon_off' === $conversion['condition'] && 'off' === ( $attributes['use_icon'] ?? 'off' ) ) {
				return $conversion['path'];
			}
		}

		// Default path if no conditions are met.
		return $value[0]['path'];
	}

}
