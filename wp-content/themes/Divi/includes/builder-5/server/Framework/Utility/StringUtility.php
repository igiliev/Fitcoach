<?php
/**
 * StringUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

/**
 * StringUtility class.
 *
 * This class contains methods to remove characters from strings.
 *
 * @since ??
 */
class StringUtility {

	/**
	 * Remove provided characters from given string.
	 *
	 * @since ??
	 *
	 * @param string $string     The string to trim.
	 * @param array  $characters An array of single character to trim.
	 *
	 * @return string
	 */
	public static function trim_extended( $string, $characters ) {
		// Allow only single character.
		if ( $characters ) {
			$characters = array_filter(
				$characters,
				function( $character ) {
					return is_string( $character ) && 1 === strlen( $character );
				}
			);
		}

		if ( ! $characters ) {
			return $string;
		}

		$first_char = substr( $string, 0, 1 );

		while ( '' !== $string && in_array( $first_char, $characters, true ) ) {
			// Remove the first character.
			$string = substr_replace( $string, '', 0, 1 );

			if ( '' === $string ) {
				break;
			}

			// Get the first character of the string for next iteration.
			$first_char = substr( $string, 0, 1 );
		}

		$last_char = substr( $string, -1 );

		while ( '' !== $string && in_array( $last_char, $characters, true ) ) {
			// Remove the last character.
			$string = substr_replace( $string, '', -1, 1 );

			if ( '' === $string ) {
				break;
			}

			// Get the last character of the string for next iteration.
			$last_char = substr( $string, -1 );
		}

		return $string;
	}

	/**
	 * Trim string if the first and last character of a string are the same and are in the list of
	 * characters to remove.
	 *
	 * @since ??
	 *
	 * @param string $string     The string to trim.
	 * @param array  $characters An array of single character to trim.
	 *
	 * @return string
	 */
	public static function trim_pair( $string, $characters ) {
		// Allow only single character and not a new line character.
		if ( $characters ) {
			$characters = array_filter(
				$characters,
				function( $character ) {
					return is_string( $character ) && 1 === strlen( $character );
				}
			);
		}

		if ( ! $characters ) {
			return $string;
		}

		$first_char = substr( $string, 0, 1 );
		$last_char  = substr( $string, -1 );

		while ( '' !== $string && $first_char === $last_char && in_array( $first_char, $characters, true ) ) {
			// Remove the first character.
			$string = substr_replace( $string, '', 0, 1 );

			// Remove the last character.
			$string = substr_replace( $string, '', -1, 1 );

			if ( '' === $string ) {
				break;
			}

			// Get the first character of the string for next iteration.
			$first_char = substr( $string, 0, 1 );

			// Get the last character of the string for next iteration.
			$last_char = substr( $string, -1 );
		}

		return $string;
	}

	/**
	 * Checks if a string starts with a given substring
	 *
	 * Performs a case-sensitive check indicating if haystack begins with needle.
	 *
	 * @since ??
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle The substring to search for in the haystack.
	 * @return bool Returns true if haystack begins with needle, false otherwise.
	 */
	public static function starts_with( string $haystack, string $needle ):bool {
		if ( ! function_exists( 'str_starts_with' ) ) {
			return str_starts_with( $haystack, $needle );
		}

		return strlen( $needle ) === 0 || strpos( $haystack, $needle ) === 0;
	}

	/**
	 * Checks if a string ends with a given substring
	 *
	 * Performs a case-sensitive check indicating if haystack ends with needle.
	 *
	 * @since ??
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle The substring to search for in the haystack.
	 *
	 * @return bool Returns true if haystack ends with needle, false otherwise.
	 */
	public static function ends_with( string $haystack, string $needle ):bool {
		if ( ! function_exists( 'str_ends_with' ) ) {
			return str_ends_with( $haystack, $needle );
		}

		$needle_len = strlen( $needle );

		return ( 0 === $needle_len || 0 === substr_compare( $haystack, $needle, - $needle_len ) );
	}
}
