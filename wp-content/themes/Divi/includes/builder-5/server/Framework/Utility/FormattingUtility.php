<?php
/**
 * FormattingUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FormattingUtility class.
 *
 * This class contains methods for formatting text.
 *
 * @since ??
 */
class FormattingUtility {

	/**
	 * Conditionally applies wpautop to a string.
	 *
	 * This method checks if the input string contains consecutive newline characters or
	 * consecutive `<br>` tags. If neither is found, it returns the original string.
	 * Otherwise, it applies the wpautop function to the string.
	 *
	 * @since ??
	 *
	 * @param string $string The input string to process.
	 * @return string The processed string, with wpautop applied if conditions are met.
	 */
	public static function maybe_wpautop( string $string ): string {
		// Check if the string contains consecutive newline characters or consecutive `<br>` tags.
		if ( false !== strpos( $string, "\n\n" ) || preg_match( '|<br\s*/?>\s*<br\s*/?>|', $string ) ) {
			return wpautop( $string );
		}

		return $string;
	}
}
