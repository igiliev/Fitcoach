<?php
/**
 * SanitizerUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * SanitizerUtility class.
 *
 * This class contains methods to sanitize data.
 *
 * @since ??
 */
class SanitizerUtility {

	/**
	 * Sanitize HTML heading tag.
	 *
	 * Only these tags are valid: `h1`, `h2`, `h3`, `h4`, `h5`, `h6`.
	 *
	 * @since ??
	 *
	 * @param string $heading_tag HTML heading tag to sanitize.
	 * @param string $fallback    Optional. The fallback value when the passed heading tag is invalid. Default `h2`.
	 *
	 * @return string A valid and safe HTML heading tag.
	 **/
	public static function sanitize_heading_tag( $heading_tag, $fallback = 'h2' ) {
		$allowed_tags = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

		if ( $heading_tag && in_array( $heading_tag, $allowed_tags, true ) ) {
			return $heading_tag;
		}

		if ( $fallback && in_array( $fallback, $allowed_tags, true ) ) {
			return $fallback;
		}

		return 'h2';
	}

	/**
	 * Sanitize Image source URL.
	 *
	 * The function sanitizes an image source URL by allowing only certain protocols and escaping the URL.
	 * The function uses `wp_allowed_protocols` + `data` for allowed protocols for the URL.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_allowed_protocols/
	 *
	 * @param string $value The image src value.
	 *
	 * @return string The sanitized URL string.
	 */
	public static function sanitize_image_src( $value ) {
		$protocols = array_merge( wp_allowed_protocols(), [ 'data' ] ); // Need to add `data` protocol for default image.

		return esc_url( $value, $protocols );
	}

	/**
	 * Maybe NaN.
	 *
	 * Return the value if the value is numeric.
	 * Return fallback value otherwise.
	 *
	 * This function is equivalent of JS function maybeNaN located in:
	 * visual-builder/packages/numbers/src/utils/maybe-nan/index.ts
	 *
	 * @since ??
	 *
	 * @param string $value   Value to check.
	 * @param string $or_else Fallback value.
	 *
	 * @return string Value or fallback value.
	 **/
	public static function maybe_nan( $value, $or_else = null ) {
		if ( ( is_numeric( $value ) || is_string( $value ) ) && preg_match( '/^(-?\d+)/', $value ) ) {
			return $value;
		}
		return $or_else;
	}

	/**
	 * Maybe Float.
	 *
	 * Return the value if the value can be parsed to Float number.
	 * Return fallback value otherwise.
	 *
	 * This function is equivalent of JS function maybeFloat located in:
	 * visual-builder/packages/numbers/src/utils/maybe-float/index.ts
	 *
	 * @since ??
	 *
	 * @param string $value   Value to check.
	 * @param string $or_else Fallback value.
	 *
	 * @return float|mixed Float number or fallback value.
	 **/
	public static function maybe_float( $value = '', $or_else = null ) {
		// Check if the value is a number or a string and if it matches the regex for a float.
		// https://regex101.com/r/upOw7D/1 - Regex.
		if ( ( is_numeric( $value ) || is_string( $value ) ) && preg_match( '/^(-?\d*\.?\d+)/', $value ) ) {
			return floatval( $value );
		}

		return $or_else;
	}

	/**
	 * Sanitizes a value to a number.
	 *
	 * Returns a number (or a default value if the value is not a number).
	 *
	 * This function is equivalent of JS function getNumber located in:
	 * visual-builder/packages/sanitize/src/utils/get-number/index.ts
	 *
	 * @since ??
	 *
	 * @param string $value         The value to be parsed.
	 * @param string $default_value The default value to be returned if the value is not a number. Can be `null`.
	 *
	 * @return float|mixed The parsed value or the default value. Can also be a `boolean` or `null`.
	 **/
	public static function get_number( $value, $default_value ) {
		return self::maybe_float( $value, $default_value );
	}

	/**
	 * Sanitize the unit of a value.
	 *
	 * This function is equivalent of JS function getUnit located in:
	 * visual-builder/packages/sanitize/src/utils/get-unit/index.ts
	 *
	 * @since ??
	 *
	 * @param string $raw_val      Value to get the unit from.
	 * @param string $default_unit Default unit if value has no unit.
	 *
	 * @return string Unit of the value.
	 **/
	public static function get_unit( $raw_val = '', $default_unit = 'px' ) {
		$value = is_string( $raw_val ) ? $raw_val : '';

		if ( '' === $value || is_numeric( $value ) ) {
			return $default_unit;
		}

		$valid_one_char_units    = [ '%', '°' ];
		$valid_two_chars_units   = [ 'em', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw', 'ms' ];
		$valid_three_chars_units = [ 'deg', 'rem' ];
		$valid_four_chars_units  = [ 'vmin', 'vmax' ];

		$important        = '!important';
		$important_length = strlen( $important );
		$value_length     = strlen( $value );

		if ( substr( $value, ( 0 - $important_length ), $important_length ) === $important ) {
			$value_length -= $important_length;

			$value = trim( substr( $value, 0, $value_length ) );
		}

		$last_4_char = substr( $value, -4, 4 );

		if ( in_array( $last_4_char, $valid_four_chars_units, true ) ) {
			return $last_4_char;
		}

		$last_3_char = substr( $value, -3, 3 );

		if ( in_array( $last_3_char, $valid_three_chars_units, true ) ) {
			return $last_3_char;
		}

		$last_2_char = substr( $value, -2, 2 );

		if ( in_array( $last_2_char, $valid_two_chars_units, true ) ) {
			return $last_2_char;
		}

		$last_1_char = substr( $value, -1, 1 );

		if ( in_array( $last_1_char, $valid_one_char_units, true ) ) {
			return $last_1_char;
		}

		return $default_unit;
	}

	/**
	 * Parse value as number and CSS unit.
	 *
	 * This function is equivalent of JS function numericParseValue located in:
	 * visual-builder/packages/field-library/src/components/common/numeric-input/utils/numeric-parse-value/index.ts
	 *
	 * @since ??
	 *
	 * @param string $value Raw value.
	 *
	 * @return array|null Will return null on failure.
	 **/
	public static function numeric_parse_value( $value ) {
		$value_number = self::get_number( $value, false );

		if ( false === $value_number ) {
			return null;
		}

		return [
			'valueNumber' => $value_number,
			'valueUnit'   => self::get_unit( $value, '' ),
		];
	}
}
