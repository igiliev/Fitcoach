<?php
/**
 * GlobalPresetItemGroupUtils class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Framework\Utility\Memoize;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupAttrNameResolver;

/**
 * GlobalPresetItemGroupUtils class.
 *
 * @since ??
 */
class GlobalPresetItemGroupUtils {

	/**
	 * Conditionally sets a value in an array at the specified property path.
	 *
	 * This function complements the `createImmutableAttrs` function in JavaScript.
	 *
	 * @param array $args {
	 *   An array of arguments.
	 *
	 *  @type mixed $attr The attribute value to set.
	 *  @type array $propertyPath The property path to set the attribute value.
	 *  @type array $accumulator The array to set the attribute value in.
	 * }
	 */
	public static function maybe_set_attrs( array $args ): array {
		// Extract the arguments.
		$attr          = $args['attr'];
		$property_path = $args['propertyPath'];
		$accumulator   = $args['accumulator'] ?? [];

		if ( null === $attr ) {
			return $accumulator;
		}

		return ArrayUtility::set_value( $accumulator, $property_path, $attr );
	}


	/**
	 * Filters attributes by group and suffix.
	 *
	 * This method filters the attributes of a module based on the provided attribute name,
	 * module name, and group slug. It first checks if the result is cached and returns the
	 * cached result if available. If not, it retrieves the module settings and filters the
	 * attributes accordingly.
	 *
	 * @param string $attr_name   The attribute name to filter by.
	 * @param string $module_name The name of the module.
	 * @param string $group_slug  The slug of the group to filter by.
	 *
	 * @return array The filtered attributes.
	 */
	public static function filter_attributes_by_group_and_suffix( string $attr_name, string $module_name, string $group_slug ): array {
		if ( Memoize::has( __METHOD__, $module_name, $group_slug, $attr_name ) ) {
			return Memoize::get( __METHOD__, $module_name, $group_slug, $attr_name );
		}

		$attr_names = GlobalPresetItemGroupAttrNameResolver::get_attr_names_by_group( $module_name, $group_slug );

		$filtered = array_filter(
			$attr_names,
			function ( $attr_name_to_compare ) use ( $attr_name ) {
				return GlobalPresetItemGroupAttrNameResolver::is_attr_name_suffix_matched( $attr_name, $attr_name_to_compare );
			}
		);

		return Memoize::set( array_values( $filtered ), __METHOD__, $module_name, $group_slug, $attr_name );
	}

	/**
	 * Checks if a given attribute name exists in the module settings definition.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name to check, typically in a dot-separated format.
	 * @param string $module_name The name of the module whose settings should be checked.
	 * @param string $group_slug  The slug of the group to filter by.
	 *
	 * @return bool Wether the attribute name exists in the module settings.
	 */
	public static function is_attr_name_exist_in_module_settings( string $attr_name, string $module_name, $group_slug ): bool {
		if ( Memoize::has( __METHOD__, $module_name, $group_slug, $attr_name ) ) {
			return Memoize::get( __METHOD__, $module_name, $group_slug, $attr_name );
		}

		$attr_names = GlobalPresetItemGroupAttrNameResolver::get_attr_names_by_group( $module_name, $group_slug );

		$found = ArrayUtility::find(
			$attr_names,
			function ( $attr_name_to_compare ) use ( $attr_name ) {
				return GlobalPresetItemGroupAttrNameResolver::is_attr_name_prefix_matched( $attr_name, $attr_name_to_compare );
			}
		);

		return Memoize::set( ! ! $found, __METHOD__, $module_name, $group_slug, $attr_name );
	}

	/**
	 * Finds an attribute base name in the module settings definition that matches the suffix
	 * of the provided attribute name.
	 *
	 * The matching is performed by comparing the portion of the attribute name after the first dot-delimited segment.
	 * Example: "title.decoration.font" will match "body.decoration.font" since "decoration.font" is the same.
	 *
	 * @param string $attr_name The dot-delimited attribute name to find a match for.
	 * @param string $module_name The name of the module whose settings should be checked.
	 * @param string $group_slug  The slug of the group to filter by.
	 *
	 * @return string|null The attribute base name that matches the suffix of the provided attribute name.
	 */
	public static function find_suffix_matched_attr_name_in_module_settings( string $attr_name, string $module_name, $group_slug ): ?string {
		if ( Memoize::has( __METHOD__, $module_name, $group_slug, $attr_name ) ) {
			return Memoize::get( __METHOD__, $module_name, $group_slug, $attr_name );
		}

		$attr_names = GlobalPresetItemGroupAttrNameResolver::get_attr_names_by_group( $module_name, $group_slug );

		$found = ArrayUtility::find(
			$attr_names,
			function ( $attr_name_to_compare ) use ( $attr_name ) {
				return GlobalPresetItemGroupAttrNameResolver::is_attr_name_suffix_matched( $attr_name, $attr_name_to_compare );
			}
		);

		return Memoize::set( $found, __METHOD__, $module_name, $group_slug, $attr_name );
	}

	/**
	 * Replaces the prefix of an attribute name with a new prefix
	 *
	 * @since ??
	 *
	 * @param string $attr_name The original attribute name, which may contain dot-separated parts.
	 * @param string $attr_name_prefix The new prefix to replace the original prefix.
	 *
	 * @return string The attribute name with the new prefix.
	 */
	public static function replace_attr_name_prefix( string $attr_name, string $attr_name_prefix ): string {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes. [https://github.com/elegantthemes/Divi/issues/41805].
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-12', 'GlobalPresetItemGroupAttrNameResolver::replace_attr_name_prefix' );

		return GlobalPresetItemGroupAttrNameResolver::replace_attr_name_prefix( $attr_name, $attr_name_prefix );
	}

	/**
	 * Splits an attribute name into its components.
	 *
	 * Splits the attribute name into an array using `.` as the delimiter
	 * and limits it to a maximum parts. By default, it's 3. But we need to define
	 * it sometimes for some cases. This ensures we only consider
	 * the first levels of the attribute hierarchy, which are relevant
	 * for module settings structure.
	 *
	 * @param string $attr_name The attribute name to split.
	 * @param int    $max_parts The maximum number of parts to return. Default is 3.
	 *
	 * @return array The split attribute name as an array.
	 */
	public static function split_attr_name( string $attr_name, int $max_parts = 3 ): array {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes. [https://github.com/elegantthemes/Divi/issues/41805].
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-12', 'GlobalPresetItemGroupAttrNameResolver::split_attr_name' );

		return GlobalPresetItemGroupAttrNameResolver::split_attr_name( $attr_name, $max_parts );
	}

	/**
	 * Checks if the suffix of an attribute name matches another attribute name.
	 *
	 * Splits both attribute names by '.' and compares each part, ensuring
	 * they have the same number of parts and that all parts except the first
	 * are identical.
	 *
	 * @since ??
	 *
	 * @param string $attr_name             The attribute name to check.
	 * @param string $attr_name_to_compare  The attribute name to compare against.
	 *
	 * @return bool True if the suffixes match, false otherwise.
	 */
	public static function is_attr_name_suffix_matched( string $attr_name, string $attr_name_to_compare ): bool {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes. [https://github.com/elegantthemes/Divi/issues/41805].
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-12', 'GlobalPresetItemGroupAttrNameResolver::is_attr_name_suffix_matched' );

		return GlobalPresetItemGroupAttrNameResolver::is_attr_name_suffix_matched( $attr_name, $attr_name_to_compare );
	}
}
