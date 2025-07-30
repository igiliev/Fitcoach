<?php
/**
 * TextTransform class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TextTransform class.
 *
 * This class contains methods for formatting text.
 *
 * @since ??
 */
class TextTransform {

	/**
	 * Transform string to param case: a-nice-day.
	 *
	 * Transforms a string into a lower cased string with dashes between words.
	 *
	 * @since ??
	 *
	 * @param string $string String to be transformed.
	 *
	 * @return string Transformed string.
	 **/
	public static function param_case( $string ) {
		$string = self::split_string( $string );
		$string = strtolower( $string );
		$parts  = explode( ' ', $string );

		return implode( '-', $parts );
	}

	/**
	 * Transform string to snake case: a_nice_day.
	 *
	 * Transforms string into a lower cased string with underscore between words.
	 *
	 * @since ??
	 *
	 * @param string $string String to be transformed.
	 *
	 * @return string Transformed string.
	 **/
	public static function snake_case( $string ) {
		$string = self::split_string( $string );
		$string = strtolower( $string );
		$parts  = explode( ' ', $string );

		return implode( '_', $parts );
	}

	/**
	 * Split string with whitespace between words
	 *
	 * @since ??
	 *
	 * @param string $string String to be split.
	 *
	 * @return string Split string with whitespace between words.
	 **/
	public static function split_string( $string ) {
		// Transform "camelCase" -> "camel Case".
		$string = preg_replace( '/([a-z0-9])([A-Z])/', '$1 $2', $string );

		// Transform "CAMELCase" -> "CAMEL Case".
		$string = preg_replace( '/([A-Z])([A-Z][a-z])/', '$1 $2', $string );

		// Transform "non alphanumeric" -> whitespace.
		$string = preg_replace( '/[^A-Za-z0-9\s]/', ' ', $string );

		// Replace multiple whitespace to single whitespace.
		$string = preg_replace( '/\s+/', ' ', $string );

		return trim( $string );
	}

	/**
	 * Transform string to kebab case: a-nice-day.
	 *
	 * This function is an alias for TextTransform::param_case().
	 *
	 * @since ??
	 *
	 * @param string $string String to be transformed.
	 *
	 * @return string Transformed string.
	 **/
	public static function kebab_case( string $string ): string {
		return self::param_case( $string );
	}

	/**
	 * Transfrom string to Pascal Case: ANiceDay.
	 *
	 * @since ??
	 *
	 * @param string $string String to be transformed.
	 *
	 * @return string Transformed string.
	 */
	public static function pascal_case( string $string ): string {
		$string = self::split_string( $string );
		$parts  = explode( ' ', $string );

		return implode( '', array_map( 'ucfirst', $parts ) );
	}
}
