<?php
/**
 * StyleLibrary\Utils class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\GlobalData\GlobalData;

/**
 * Utils class is a helper class with helper methods to work with the style library.
 *
 * @since ??
 */
class Utils {
	/**
	 * Join array of declarations into `;` separated string, suffixed by `;`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/join-declarations joinDeclarations} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $declarations Array of declarations.
	 *
	 * @return string
	 */
	public static function join_declarations( array $declarations ): string {
		$joined = implode( '; ', $declarations );

		if ( 0 < count( $declarations ) ) {
			$joined = $joined . ';';
		}

		return $joined;
	}

	/**
	 * Recursively resolve any `$variable(...)$` strings within an array or string.
	 *
	 * @since ??
	 *
	 * @param mixed $value The raw input, string or array.
	 *
	 * @return mixed The resolved value with all dynamic variables normalized.
	 */
	public static function resolve_dynamic_variables_recursive( $value ) {
		if ( ! is_array( $value ) ) {
			return self::resolve_dynamic_variable( $value );
		}

		foreach ( $value as $key => $subvalue ) {
			$value[ $key ] = self::resolve_dynamic_variables_recursive( $subvalue );
		}

		return $value;
	}

	/**
	 * Resolves a `$variable(...)$` encoded dynamic content string into a CSS variable.
	 *
	 * Example:
	 * Input:  $variable({"type":"content","value":{"name":"gvid-abc123"}})$
	 * Output: var(--gvid-abc123)
	 *
	 * @since ??
	 *
	 * @param string $value The raw string to be resolved.
	 *
	 * @return string The resolved CSS variable or original value if not matched.
	 */
	public static function resolve_dynamic_variable( $value ) {
		if ( is_string( $value ) && preg_match( '/^\$variable\((.+)\)\$$/', $value, $matches ) ) {
			$decoded = json_decode( $matches[1], true );
			$type    = $decoded['type'] ?? '';
			$name    = $decoded['value']['name'] ?? null;

			if ( $name ) {
				$css_variable = "var(--{$name})";

				switch ( $type ) {
					case 'color':
						return GlobalData::transform_state_into_global_color_value( $css_variable, $decoded['value']['settings'] ?? [] );
					default:
						return $css_variable;
				}
			}
		}

		return $value;
	}
}
