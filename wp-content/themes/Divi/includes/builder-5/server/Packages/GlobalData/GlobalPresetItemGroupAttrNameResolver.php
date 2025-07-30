<?php
/**
 * GlobalPresetItemGroupAttrNameResolver class.
 *
 * @package ET\Builder\Packages\GlobalData
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\Memoize;
use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupAttrNameResolved;

/**
 * Class GlobalPresetItemGroupAttrNameResolver
 *
 * @package ET\Builder\Packages\GlobalData
 */
class GlobalPresetItemGroupAttrNameResolver {

	/**
	 * Special group names for attributes.
	 *
	 * @var array
	 */
	private static $_special_group = [
		'css'                      => [
			'css',
		],
		'advancedVisibilityModule' => [
			'module.decoration.disabledOn',
			'module.decoration.overflow',
		],
		'advancedPositionModule'   => [
			'module.decoration.position',
			'module.decoration.zIndex',
		],
		'advancedScrollModule'     => [
			'module.decoration.sticky',
			'module.decoration.scroll',
		],
	];

	/**
	 * Resolves the attribute name for an option group preset by applying filters and logic.
	 *
	 * This method determines the appropriate attribute name based on the provided parameters,
	 * applying filters and memoization to optimize performance. It handles cases where the
	 * attribute name needs to be resolved based on module and group information, as well as
	 * custom logic for prefix and suffix matching.
	 *
	 * @since ??
	 *
	 * @param string      $attr_name The original attribute name to resolve.
	 * @param string      $module_name The name of the module associated with the attribute.
	 * @param string      $group_id The ID of the group associated with the attribute.
	 * @param string      $data_module_name The name of the data module associated with the attribute.
	 * @param string      $data_group_id The ID of the data group associated with the attribute.
	 * @param string      $data_primary_attr_name The primary attribute name associated with the data group.
	 * @param string|null $attr_sub_name An optional sub-name for the attribute.
	 *
	 * @return GlobalPresetItemGroupAttrNameResolved The resolved attribute name.
	 */
	public static function get_attr_name(
		string $attr_name,
		string $module_name,
		string $group_id,
		string $data_module_name,
		string $data_group_id,
		string $data_primary_attr_name,
		?string $attr_sub_name = null
	): GlobalPresetItemGroupAttrNameResolved {
		// If the module name and group ID are the same as the data module name and data group ID,
		// it indicates that the preset item is created by the same module and group that resolving the attribute name.
		// In this case, we can return the attribute name as is.
		if ( $data_module_name === $module_name && $data_group_id === $group_id ) {
			return new GlobalPresetItemGroupAttrNameResolved(
				[
					'attrName'    => $attr_name,
					'attrSubName' => $attr_sub_name,
				]
			);
		}

		$memoize_params = [
			'attrName'            => $attr_name,
			'moduleName'          => $module_name,
			'groupId'             => $group_id,
			'dataModuleName'      => $data_module_name,
			'dataGroupId'         => $data_group_id,
			'dataPrimaryAttrName' => $data_primary_attr_name,
			'attrSubName'         => $attr_sub_name,
		];

		if ( Memoize::has( __METHOD__, $memoize_params ) ) {
			return Memoize::get( __METHOD__, $memoize_params );
		}

		// By default, the attribute name to resolve is null.
		$attr_name_to_resolve = null;

		// The filter params are used to pass additional information to the filter function.
		$filter_params = [
			'attrName'            => $attr_name,
			'moduleName'          => $module_name,
			'groupId'             => $group_id,
			'dataModuleName'      => $data_module_name,
			'dataGroupId'         => $data_group_id,
			'dataPrimaryAttrName' => $data_primary_attr_name,
			'attrSubName'         => $attr_sub_name,
		];

		/**
		 * Resolves the attribute name for an option group preset by applying filters.
		 *
		 * @param {GlobalPresetItemGroupAttrNameResolved|null} attrNameToResolve The attribute name to be resolved.
		 * @param {object} filterParams The parameters for the filter.
		 *
		 * @returns {GlobalPresetItemGroupAttrNameResolved|null} The resolved attribute name if available, otherwise null.
		 */
		$resolved = apply_filters( 'divi_option_group_preset_resolver_attr_name', $attr_name_to_resolve, $filter_params );

		// If the resolved attribute name is not null, return it.
		// This allows for the possibility of a filter returning a different attribute name.
		// If the filter returns null, we proceed with the default logic.
		if ( null !== $resolved ) {
			return Memoize::set( $resolved, __METHOD__, $memoize_params );
		}

		$attr_names_to_pairs = strpos( $group_id, '.' ) ? [ $group_id ] : self::get_attr_names_by_group( $module_name, $group_id );

		if ( ! self::is_attr_name_prefix_matched( $attr_name, $attr_names_to_pairs ) ) {
			return Memoize::set(
				new GlobalPresetItemGroupAttrNameResolved(
					[
						'attrName'    => $attr_name,
						'attrSubName' => $attr_sub_name,
					]
				),
				__METHOD__,
				$memoize_params
			);
		}

		$attr_names_to_pairs_with = strpos( $data_group_id, '.' ) ? [ $data_group_id ] : self::get_attr_names_by_group( $data_module_name, $data_group_id );

		// Process when there is exactly one attribute name in both `attrNamesToPairs` and `attrNamesToPairsWith`.
		if ( 1 === count( $attr_names_to_pairs ) && 1 === count( $attr_names_to_pairs_with ) ) {
			$attr_name_to_pair      = $attr_names_to_pairs[0];
			$attr_name_to_pair_with = $attr_names_to_pairs_with[0];

			if ( self::is_attr_name_prefix_matched( $attr_name_to_pair, $attr_name_to_pair_with ) ) {
				return Memoize::set(
					new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => $attr_name,
							'attrSubName' => $attr_sub_name,
						]
					),
					__METHOD__,
					$memoize_params
				);
			}

			if ( self::is_attr_name_suffix_matched( $attr_name_to_pair, $attr_name_to_pair_with ) ) {
				return Memoize::set(
					new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => self::replace_attr_name_prefix(
								$attr_name,
								$attr_name_to_pair_with
							),
							'attrSubName' => $attr_sub_name,
						]
					),
					__METHOD__,
					$memoize_params
				);
			}
		}

		return Memoize::set(
			new GlobalPresetItemGroupAttrNameResolved(
				[
					'attrName'    => self::maybe_replace_prefix_by_suffix_match(
						$attr_name,
						$attr_names_to_pairs,
						$attr_names_to_pairs_with,
						$data_primary_attr_name
					),
					'attrSubName' => $attr_sub_name,
				]
			),
			__METHOD__,
			$memoize_params
		);
	}

	/**
	 * Attempts to replace the prefix of the given attribute name with a matched suffix.
	 *
	 * @param string $attr_name                The attribute name to process.
	 * @param array  $attr_names_to_pairs      List of potential base attribute names.
	 * @param array  $attr_names_to_pairs_with List of attribute names that could be matched by suffix.
	 * @param string $data_primary_attr_name   Optional base attribute name for sorting priority.
	 *
	 * @return string Possibly modified attribute name after prefix replacement.
	 */
	public static function maybe_replace_prefix_by_suffix_match(
		string $attr_name,
		array $attr_names_to_pairs,
		array $attr_names_to_pairs_with,
		string $data_primary_attr_name = ''
	) {
		$to_pairs = array_filter(
			$attr_names_to_pairs,
			function ( $item ) use ( $attr_names_to_pairs_with ) {
				return ! self::is_attr_name_prefix_matched( $item, $attr_names_to_pairs_with );
			}
		);

		$pairs_with = array_filter(
			$attr_names_to_pairs_with,
			function ( $item ) use ( $attr_names_to_pairs ) {
				return ! self::is_attr_name_prefix_matched( $item, $attr_names_to_pairs );
			}
		);

		$pairs_with_sorted = ( ! empty( $pairs_with ) && $data_primary_attr_name )
			? self::_sort_primary_first( $pairs_with, $data_primary_attr_name )
			: $pairs_with;

		if ( ! empty( $to_pairs ) && ! empty( $pairs_with ) ) {
			$pairs_with_filtered = $pairs_with_sorted;

			$paireds = array_reduce(
				$to_pairs,
				function ( $accumulator, $item ) use ( &$pairs_with_filtered ) {
					$pairs_with_suffix_matched = array_values(
						array_filter(
							$pairs_with_filtered,
							function ( $pair_item ) use ( $item ) {
								return self::is_attr_name_suffix_matched( $item, $pair_item );
							}
						)
					);

					$pairs_with_suffix_matched_count = count( $pairs_with_suffix_matched );

					if ( 0 < $pairs_with_suffix_matched_count ) {
						$first_matched = $pairs_with_suffix_matched[0];

						$accumulator[] = [
							'attr_name'      => $item,
							'suffix_matched' => $first_matched,
						];

						// Removes the first matched suffix from the list of $pairs_with_filtered.
						// This prevents matching the same suffix again in the next iteration,
						// ensuring each matching suffix is only used once.
						$pairs_with_filtered = array_values(
							array_filter(
								$pairs_with_filtered,
								function ( $data_attr_name ) use ( $first_matched ) {
									return $data_attr_name !== $first_matched;
								}
							)
						);
					}

					return $accumulator;
				},
				[]
			);

			$paired = ArrayUtility::find(
				$paireds,
				function ( $item ) use ( $attr_name ) {
					return self::is_attr_name_prefix_matched( $attr_name, $item['attr_name'] );
				}
			);

			if ( $paired ) {
				return self::replace_attr_name_prefix( $attr_name, $paired['suffix_matched'] );
			}
		}

		return $attr_name;
	}

	/**
	 * Sorts attributes by prioritizing those that start with the primary prefix.
	 *
	 * @param array  $items          An array of attribute names to be sorted.
	 * @param string $primary_prefix The prefix to prioritize in the sorting.
	 *
	 * @return array The sorted array of attribute names.
	 */
	private static function _sort_primary_first( array $items, $primary_prefix ) {
		usort(
			$items,
			function ( $a, $b ) use ( $primary_prefix ) {
				$a_is_primary = strpos( $a, "{$primary_prefix}." ) === 0;
				$b_is_primary = strpos( $b, "{$primary_prefix}." ) === 0;

				if ( $a_is_primary === $b_is_primary ) {
					return 0;
				}

				return $a_is_primary ? -1 : 1;
			}
		);

		return $items;
	}

	/**
	 * Retrieves attribute names associated with a specific group for a given module.
	 *
	 * This method fetches attribute names by processing the module's configuration
	 * and applying group-specific logic. The results are memoized for performance.
	 *
	 * @since ??
	 *
	 * @param string $module_name The name of the module to retrieve attributes for.
	 * @param string $group_slug  The slug of the group to filter attributes by.
	 *
	 * @return array An array of attribute names associated with the specified group.
	 */
	public static function get_attr_names_by_group(
		string $module_name,
		string $group_slug
	): array {
		if ( Memoize::has( __METHOD__, $module_name, $group_slug ) ) {
			return Memoize::get( __METHOD__, $module_name, $group_slug );
		}

		if ( strpos( $group_slug, '.' ) ) {
			return Memoize::set( [ $group_slug ], __METHOD__, $module_name, $group_slug );
		}

		$module_config   = ModuleRegistration::get_module_settings( $module_name );
		$attributes      = $module_config->attributes ?? [];
		$attr_names_base = array_keys( $attributes );

		$all_attr_names = array_reduce(
			$attr_names_base,
			function ( $accumulator, $attr_name_base ) use ( $group_slug, $attributes ) {
				$settings = $attributes[ $attr_name_base ]['settings'] ?? [];

				foreach ( $settings as $attr_type => $attr_type_settings ) {
					switch ( $attr_type ) {
						case 'advanced':
						case 'decoration':
							$attr_names = GlobalPresetItemGroupAttrNameResolver::get_attr_names(
								$attr_type_settings,
								$attr_type,
								$group_slug,
								$attr_name_base
							);

							if ( $attr_names ) {
								$accumulator = array_merge( $accumulator, $attr_names );
							}
							break;

						default:
							// code...
							break;
					}
				}

				return $accumulator;
			},
			[]
		);

		if ( isset( self::$_special_group[ $group_slug ] ) ) {
			$all_attr_names = array_merge( $all_attr_names, self::$_special_group[ $group_slug ] );
		}

		return Memoize::set( $all_attr_names, __METHOD__, $module_name, $group_slug );
	}

	/**
	 * Retrieves attribute names based on the provided attribute type settings, attribute type, group slug,
	 * and base attribute name.
	 *
	 * @param array  $attr_type_settings The settings for the attribute type.
	 * @param string $attr_type          The type of attribute: 'decoration' or 'advanced'.
	 * @param string $group_slug         The slug of the group to filter attributes by.
	 * @param string $attr_name_base     The base name for the attribute, used as a fallback.
	 *
	 * @return string[] An array of attribute names derived from the provided settings and parameters.
	 */
	public static function get_attr_names( array $attr_type_settings, string $attr_type, string $group_slug, string $attr_name_base ): array {
		$attr_names = [];

		foreach ( $attr_type_settings as $attr_sub_type => $group ) {
			$attr_names = array_merge(
				$attr_names,
				self::_process_group( $group, $attr_sub_type, $attr_type, $group_slug, $attr_name_base )
			);
		}

		return $attr_names;
	}
	/**
	 * Checks if the given attribute name matches the prefix of another attribute name or names.
	 *
	 * This method determines if the provided attribute name (`$attr_name`) matches either:
	 * - Exactly matches the `$attr_name_to_compare`, or
	 * - Starts with the `$attr_name_to_compare` followed by a dot (`.`).
	 *
	 * If `$attr_name_to_compare` is an array, the method recursively checks each item in the array
	 * to see if any of them match the `$attr_name`.
	 *
	 * @since ??
	 *
	 * @param string       $attr_name            The attribute name to check.
	 * @param string|array $attr_name_to_compare The attribute name or array of attribute names to compare against.
	 *
	 * @return bool True if a match is found, otherwise false.
	 */
	public static function is_attr_name_prefix_matched( string $attr_name, $attr_name_to_compare ): bool {
		if ( is_array( $attr_name_to_compare ) ) {
			$match = ArrayUtility::find(
				$attr_name_to_compare,
				function ( $item ) use ( $attr_name ) {
					return self::is_attr_name_prefix_matched( $attr_name, $item );
				}
			);

			return ! ! $match;
		}

		return $attr_name === $attr_name_to_compare || 0 === strpos( $attr_name, $attr_name_to_compare . '.' );
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
	 * @param string $attr_name            The attribute name to check.
	 * @param mixed  $attr_name_to_compare The attribute name to compare against.
	 *
	 * @return bool True if the suffixes match, false otherwise.
	 */
	public static function is_attr_name_suffix_matched( string $attr_name, $attr_name_to_compare ): bool {
		if ( is_array( $attr_name_to_compare ) ) {
			$match = ArrayUtility::find(
				$attr_name_to_compare,
				function ( $item ) use ( $attr_name ) {
					return self::is_attr_name_suffix_matched( $attr_name, $item );
				}
			);

			return ! ! $match;
		}

		$parts        = self::split_attr_name( $attr_name );
		$parts_length = count( $parts );

		if ( $parts_length < 2 ) {
			return false;
		}

		$parts_to_compare        = self::split_attr_name( $attr_name_to_compare );
		$parts_to_compare_length = count( $parts_to_compare );

		if ( $parts_to_compare_length < 2 ) {
			return false;
		}

		// Check if the lengths of the two arrays are equal.
		// If not, return false.
		if ( $parts_length !== $parts_to_compare_length ) {
			return false;
		}

		$is_matched = true;

		foreach ( $parts as $index => $value ) {
			if ( 0 === $index ) {
				continue;
			}

			if ( $parts_to_compare[ $index ] !== $value ) {
				$is_matched = false;
				break;
			}
		}

		return $is_matched;
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
		$attr_name_prefix_parts = explode( '.', $attr_name_prefix );
		$attr_name_parts        = explode( '.', $attr_name );

		$attr_name_mapped = array_map(
			function ( $part, $index ) use ( $attr_name_prefix_parts ) {
				if ( isset( $attr_name_prefix_parts[ $index ] ) ) {
					return $attr_name_prefix_parts[ $index ];
				}

				return $part;
			},
			$attr_name_parts,
			array_keys( $attr_name_parts )
		);

		return implode( '.', $attr_name_mapped );
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
		return array_slice( explode( '.', $attr_name ), 0, $max_parts );
	}

	/**
	 * Processes a group and resolves attribute names based on the group type.
	 *
	 * This method handles different group types and extracts attribute names
	 * by traversing the group structure. It supports nested groups, group items,
	 * and other configurations.
	 *
	 * @since ??
	 *
	 * @param array  $group           The group data to process.
	 * @param string $attr_sub_type   The attribute sub-type.
	 * @param string $attr_type       The attribute type.
	 * @param string $group_slug      The slug of the group to match.
	 * @param string $attr_name_base  The base name for the attribute.
	 *
	 * @return array An array of resolved attribute names.
	 */
	private static function _process_group( array $group, string $attr_sub_type, string $attr_type, string $group_slug, string $attr_name_base ): array {
		$attr_names         = [];
		$attr_name_fallback = "{$attr_name_base}.{$attr_type}.{$attr_sub_type}";

		switch ( $group['groupType'] ?? null ) {
			case 'group':
				$fields = $group['component']['props']['fields'] ?? [];
				foreach ( $fields as $field ) {
					if ( isset( $field['attrName'] ) ) {
						$attr_names[] = self::_resolve_item( $field, $field['attrName'] );
					}
				}
				break;

			case 'group-item':
				if ( ( $group['item']['groupSlug'] ?? null ) === $group_slug ) {
					$attr_names[] = self::_resolve_item( $group['item'], $attr_name_fallback );
				}
				break;

			case 'group-items':
				foreach ( $group['items'] ?? [] as $item ) {
					if ( ( $item['groupSlug'] ?? null ) === $group_slug ) {
						$attr_names[] = self::_resolve_item( $item, $attr_name_fallback );
					}
				}
				break;

			case 'into-multiple-groups':
				foreach ( $group['groups'] ?? [] as $nested_group ) {
					$attr_names = array_merge(
						$attr_names,
						self::_process_group( $nested_group, $attr_sub_type, $attr_type, $group_slug, $attr_name_base )
					);
				}
				break;

			case 'inside-group':
			case 'shared-group':
				// Currently not used by Option Group Presets.
				break;

			default:
				// Do nothing.
				break;
		}

		return $attr_names;
	}

	/**
	 * Resolves the attribute name from a given item array.
	 *
	 * This method checks for the presence of specific keys in the provided item array
	 * to determine the attribute name. If no valid attribute name is found, it falls
	 * back to the provided fallback value.
	 *
	 * @since ??
	 *
	 * @param array  $item              The item array containing potential attribute name data.
	 * @param string $attr_name_fallback The fallback attribute name to use if none is found in the item.
	 *
	 * @return string The resolved attribute name.
	 */
	private static function _resolve_item( array $item, string $attr_name_fallback ): string {
		if ( isset( $item['component']['props']['attrName'] ) ) {
			return $item['component']['props']['attrName'];
		} elseif ( isset( $item['attrName'] ) ) {
			return $item['attrName'];
		} else {
			return $attr_name_fallback;
		}
	}
}
