<?php
/**
 * Module: DynamicData main class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicData;

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicData class.
 *
 * Dynamic data is a special type of dynamic value that can be used in the content.
 * It can be `content`, `color`, `preset`, etc. The dynamic data is identified and wrapped
 * in `$variables()` format. At this moment, it only process `content` type value and
 * it's called dynamic content.
 *
 * {@see ET\Builder\Packages\Module\Layout\Components\DynamicContent}
 *
 * This class handle dynamic data processing. This includes:
 * - Extracting the `$variables()` from the given content.
 * - Converting variables to data values.
 * - Processing the dynamic data based on the type (i.e. `content`).
 * - Replacing the `$variables()` with the processed dynamic data.
 *
 * @since ??
 */
class DynamicData {

	/**
	 * Retrieves the data value based on the given string value.
	 *
	 * This function takes a string value, decodes it from JSON format,
	 * and returns it as an associative array. Any escaped double quotes
	 * ("\u0022") in the string value are replaced with actual double quotes
	 * before decoding.
	 *
	 * @since ??
	 *
	 * @param string $string_value The string value to be decoded.
	 *
	 * @return array The decoded data value. If the decoded value is not an array,
	 *               an empty array is returned.
	 *
	 * @example:
	 * ```php
	 * // Decode a JSON string value with unescaped double quotes
	 * $string_value = '{"type":"content", "value":{"name":"site_title"}}';
	 * $data_value = DynamicData::get_data_value($string_value);
	 * print_r( $data_value );
	 * ```
	 *
	 * @output:
	 * ```php
	 *  [
	 *    "type" => "content",
	 *    "value" => [
	 *      "name" => "site_title"
	 *    ]
	 *  ]
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Decode a JSON string value with escaped double quotes
	 * $string_value = '{\\u0022type\\u0022:\\u0022content\\u0022,\\u0022value\\u0022:{\\u0022name\\u0022:\\u0022site_title\\u0022}}';
	 * $data_value = DynamicData::get_data_value($string_value);
	 * print_r( $data_value );
	 * ```
	 *
	 * @output:
	 * ```php
	 *  [
	 *    "type" => "content",
	 *    "value" => [
	 *      "name" => "site_title"
	 *    ]
	 *  ]
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Decode a JSON string value with escaped single quotes (invalid JSON string)
	 * $string_value = '{\\u0027type\\u0027:\\u0027content\\u0027,\\u0027value\\u0027:{\\u0027name\\u0027:\\u0027site_title\\u0027}}';
	 * $data_value = DynamicData::get_data_value($string_value);
	 * print_r( $data_value );
	 * ```
	 *
	 * @output:
	 * ```php
	 *  []
	 * ```
	 */
	public static function get_data_value( string $string_value ): array {
		$json_value = self::construct_json_string( $string_value );

		$data_value = json_decode( $json_value, true );

		return is_array( $data_value ) ? $data_value : [];
	}

	/**
	 * Construct JSON string by replacing Unicode escape double quotes.
	 *
	 * - Replaces `\\u0022:{` with `":{`.
	 * - Replaces `\\u0022:\\u0022` with `":"`.
	 * - Replaces `\\u0022,\\u0022` with `","`.
	 * - Replaces `{\\u0022` with `{"`.
	 * - Replaces `\\u0022}` with `"}`.
	 *
	 * @since ??
	 *
	 * @param string $string_value The string value to process.
	 * @return string The processed string with replaced Unicode escape double quotes.
	 */
	public static function construct_json_string( string $string_value ): string {
		$string_value = str_replace( '\\u0022:{', '":{', $string_value );
		$string_value = str_replace( '\\u0022:\\u0022', '":"', $string_value );
		$string_value = str_replace( '\\u0022,\\u0022', '","', $string_value );
		$string_value = str_replace( '{\\u0022', '{"', $string_value );
		$string_value = str_replace( '\\u0022}', '"}', $string_value );

		return $string_value;
	}

	/**
	 * Get processed dynamic data.
	 *
	 * Proceessing dynamic data includes:
	 * - Extracting the `$variables()` from the given content.
	 * - Cache the resolved value to be used later for the same variables.
	 * - Converting variables to data values.
	 * - Processing the dynamic data based on the type (i.e. `content`).
	 * - Replacing the `$variables()` with the processed dynamic data.
	 *
	 * If `$serialize=true` the following will be done:
	 * - any `--` will be replaced with `\u002d\u002d`.
	 * - any `<` will be replaced with `\u003c`.
	 * - any `>` will be replaced with `\u003e`.
	 * - any `&` will be replaced with `\u0026`.
	 * - any `"` will be replaced with `\u0022`.
	 *
	 * This function can currently only process dynamic content type.
	 *
	 * @since ??
	 *
	 * @param string   $content   Content to process.
	 * @param int|null $post_id   Optional. The post ID. Default `null`.
	 * @param bool     $serialize Optional. Flag to serialize the resolved value. Default `false`.
	 *
	 * @return string|null Processed dynamic data.
	 */
	public static function get_processed_dynamic_data( string $content, ?int $post_id = null, bool $serialize = false ): ?string {
		static $cache = [];

		// Bail early if no dynamic data `$variable` found.
		if ( false === strpos( $content, '$variable(' ) ) {
			return $content;
		}

		$string_values = self::get_variable_values( $content );

		foreach ( $string_values as $string_value ) {
			$resolved_value = null;

			if ( isset( $cache[ $string_value ] ) ) {
				// Use cached resolved value just in case this function is being called again
				// and the same variables exist.
				$resolved_value = $cache[ $string_value ];
			} else {
				$data_value = self::get_data_value( $string_value );
				$type       = $data_value['type'] ?? '';
				$value      = $data_value['value'] ?? [];
				$name       = $value['name'] ?? '';

				if ( $post_id && ! isset( $value['post_id'] ) ) {
					$value['post_id'] = $post_id;
				}

				// Customizer fonts saved as css variables already. Just keep it.
				if ( ! empty( $name ) && in_array( $name, [ '--et_global_body_font', '--et_global_heading_font' ], true ) ) {
					$resolved_value = sprintf( 'var(%s)', $name );
				} elseif ( 'content' === $type ) {
					// Currently only process `content` type for Dynamic Content.
					$resolved_value = DynamicContentUtils::get_processed_dynamic_content( $value );
				}

					// Serialize the resolved value if required.
				if ( $serialize && null !== $resolved_value ) {
						// Serialize the resolved value using serialize_block_attributes function to ensure
						// that characters potentially interfering with block attributes parsing are escaped.
						$serialized = serialize_block_attributes( [ 'value' => $resolved_value ] );

						// Extract the serialized resolved value by trimming specific parts:
						// - Remove the first 10 characters (`{"value":"`).
						// - Remove the last 2 characters (`"}`).
						$resolved_value = substr( $serialized, 10, -2 );
				}

				$resolved_value_args = [
					'type'    => $type,
					'value'   => $value,
					'content' => $content,
				];

				/**
				 * Filter dynamic data resolved value to resolve based on provided value and arguments.
				 *
				 * @since ??
				 *
				 * @param string $resolved_value      Dynamic data resolved value.
				 * @param array  $resolved_value_args {
				 *     An array of arguments.
				 *
				 *     @type string $type    Dynamic data type i.e. `content`.
				 *     @type array  $value   Dynamic data value before processed.
				 *     @type string $content Post content or document (blocks).
				 * }
				 */
				$resolved_value = apply_filters( 'divi_module_dynamic_data_resolved_value', $resolved_value, $resolved_value_args );
			}

			// Replace the variable string with the resolved value. We should not cache `null`
			// value as well to anticipate the dynamic data value is intentionally set to `null`
			// to skip or repeat the dynamic data resolving process.
			if ( null !== $resolved_value ) {
				$cache[ $string_value ] = $resolved_value;
				$content                = str_replace( '$variable(' . $string_value . ')$', $resolved_value, $content );
			}
		}

		return $content;
	}

	/**
	 * Get dynamic data variable values based on the given content.
	 *
	 * This function uses regex to find the variables value in the given content.
	 * {@link https://regex101.com/r/534mcR/1 Regex101}
	 *
	 * @since ??
	 *
	 * @param string $content Content to search for variables.
	 *
	 * @return array Matched variable values.
	 */
	public static function get_variable_values( string $content ): array {
		preg_match_all( '/\$variable\((.+?)\)\$/', $content, $variable_matches );

		return $variable_matches[1];
	}

}
