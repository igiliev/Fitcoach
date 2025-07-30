<?php
/**
 * Module: MultiViewUtils class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewInfo;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\VisualBuilder\Saving\SavingUtility;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Module: MultiViewUtils class.
 *
 * @since ??
 */
class MultiViewUtils {

	/**
	 * Convert selector to module class.
	 *
	 * @since ??
	 *
	 * @param string   $selector       The original selector.
	 * @param string   $id             The module id.
	 * @param int|null $store_instance Optional. The ID of instance where this block is stored in BlockParserStore.
	 *                                 Default `null`.
	 *
	 * @return string
	 */
	public static function convert_selector( string $selector, string $id, $store_instance = null ): string {
		if ( false !== strpos( $selector, '{{selector}}' ) ) {
			$order_class = ModuleUtils::get_module_order_class_name( $id, $store_instance );

			if ( ! $order_class ) {
				$order_class = ModuleUtils::get_module_class_by_name( $id );
			}

			$selector = str_replace(
				'{{selector}}',
				'.' . $order_class,
				$selector
			);
		}

		if ( false !== strpos( $selector, '{{parentSelector}}' ) ) {
			$parent_order_class = ModuleUtils::get_module_order_class_name( BlockParserStore::get_parent( $id, $store_instance )->id ?? '', $store_instance );

			if ( ! $parent_order_class ) {
				$parent_order_class = ModuleUtils::get_module_class_by_name( BlockParserStore::get_parent( $id, $store_instance )->id ?? '' );
			}

			$selector = str_replace(
				'{{parentSelector}}',
				'.' . $parent_order_class,
				$selector
			);
		}

		if ( false !== strpos( $selector, '{{selectorPrefix}}' ) ) {
			$parent_order_class = ModuleUtils::get_module_order_class_name( BlockParserStore::get_parent( $id, $store_instance )->id ?? '', $store_instance );

			if ( ! $parent_order_class ) {
				$parent_order_class = ModuleUtils::get_module_class_by_name( BlockParserStore::get_parent( $id, $store_instance )->id ?? '' );
			}

			$selector = str_replace(
				'{{selectorPrefix}}',
				'.' . $parent_order_class,
				$selector
			);
		}

		if ( false !== strpos( $selector, '{{baseSelector}}' ) ) {
			$base_selector = ModuleUtils::get_module_order_class_name( $id, $store_instance );

			if ( ! $base_selector ) {
					$base_selector = ModuleUtils::get_module_class_by_name( $id );
			}

			$selector = str_replace(
				'{{baseSelector}}',
				'.' . $base_selector,
				$selector
			);
		}

		return $selector;
	}

	/**
	 * Concatenates a breakpoint and a state.
	 *
	 * If the value of state is "value", only the breakpoint is returned.
	 *
	 * The breakpoints and states come from calling `MultiViewUtils::get_breakpoints_states()`.
	 * If the given breakpoint or state is not in the list, the function will return null.
	 *
	 * @since ??
	 *
	 * @param string $breakpoint The attribute breakpoint. One of `desktop`, `tablet`, or `mobile`.
	 * @param string $state      The attribute state. One of `value`, or `hover`.
	 *
	 * @return string|null The concatenated value, or `null` if the given breakpoint or state is
	 *                     not in the `MultiViewUtils::get_breakpoints_states()` list.
	 */
	public static function data_key( string $breakpoint, string $state ): ?string {
		$breakpoint_states = self::get_breakpoints_states();

		if ( isset( $breakpoint_states[ $breakpoint ] ) && in_array( $state, $breakpoint_states[ $breakpoint ], true ) ) {
			if ( 'value' === $state ) {
				return $breakpoint;
			}

			return $breakpoint . '--' . $state;
		}

		return null;
	}

	/**
	 * Retrieves the breakpoints states information.
	 *
	 * This function returns an array containing the mapping of breakpoints and their states,
	 * along with the default breakpoint and default state.
	 *
	 * @since ??
	 *
	 * @return MultiViewInfo The array containing the breakpoints states information.
	 */
	public static function get_breakpoints_states_info(): MultiViewInfo {
		static $mapping;

		if ( $mapping ) {
			return $mapping;
		}

		// Get breakpoint states.
		$breakpoint_states = self::get_breakpoints_states();

		// Get default breakpoint and state.
		$default_breakpoint = Breakpoint::get_base_breakpoint_name();
		$default_state      = Breakpoint::get_base_state_name();

		$mapping = [
			'mapping'           => $breakpoint_states,
			'defaultBreakpoint' => $default_breakpoint,
			'defaultState'      => $default_state,
		];

		$mapping = new MultiViewInfo(
			[
				'mapping'           => $breakpoint_states,
				'defaultBreakpoint' => $default_breakpoint,
				'defaultState'      => $default_state,
			]
		);

		return $mapping;
	}

	/**
	 * Filterable breakpoint list paired with state list.
	 *
	 * @since ??
	 *
	 * @return array A key-value pair of breakpoints and states where the key is the breakpoint and the value is the states.
	 */
	public static function get_breakpoints_states(): array {
		static $paired = [];

		if ( $paired ) {
			return $paired;
		}

		$breakpoints = Breakpoint::get_enabled_breakpoint_names();
		$states      = ModuleUtils::states();

		// Base breakpoint.
		// In customizable breapoints, there is no plan to make base breakpoint editable but it is safer to be safe
		// and assume that this is something that is editable so if in the future this is made editable, things
		// will just fall into place neatly.
		$base_breakpoint = Breakpoint::get_base_breakpoint_name();

		foreach ( $breakpoints as $breakpoint ) {
			if ( ! isset( $paired[ $breakpoint ] ) ) {
				$paired[ $breakpoint ] = [];
			}

			foreach ( $states as $state ) {
				/**
				 * Default breakpoint and state pair:
				 *
				 * - Desktop: value, hover, sticky (assuming the desktop is the base breakpoint)
				 * - Tablet:  value
				 * - Phone:   value
				 */
				$is_enable_state_default = $base_breakpoint === $breakpoint || 'value' === $state;

				/**
				 * Filters whether the state is enabled for the breakpoint.
				 *
				 * @since ??
				 *
				 * @param bool   $is_enable_state_default Whether the state is enabled for the breakpoint.
				 * @param string $breakpoint              The breakpoint.
				 * @param string $state                   The state.
				 */
				$is_enable_state = apply_filters( 'divi_breakpoint_state', $is_enable_state_default, $breakpoint, $state );

				if ( $is_enable_state ) {
					$paired[ $breakpoint ][] = $state;
				}
			}
		}

		return $paired;
	}

	/**
	 * Get the class name for the hidden element if the attribute is hidden on load.
	 *
	 * @since ??
	 *
	 * @param array $attr The attribute that needs to be checked.
	 * @param array $options {
	 *     Additional options for checking the value (optional).
	 *
	 *     @type string|null   $subName       Optional. The sub-name to extract from the attribute value. Default `null`.
	 *     @type callable|null $valueResolver Optional. A callable function to resolve the attribute value. Default `null`.
	 *     @type string|null   $inheritedMode Optional. The inherit mode specifying how the attribute value will be inherited.
	 *                                        One of `inherited`, `inheritedClosest`, `inheritedAll`, `inheritedOrClosest`,
	 *                                        `inheritedOrAll`, `closest`, `all`. Default `null`.
	 *
	 * @return string Whether the given value is an array with specific breakpoints and states.
	 */
	public static function hidden_on_load_class_name( array $attr, array $options = [] ): string {
		if ( self::is_hidden_on_load( $attr, $options ) ) {
			return 'et_multi_view_hidden';
		}

		return '';
	}

	/**
	 * Checks if a given value is an array with specific breakpoints and states.
	 *
	 * The breakpoints and states come from calling `MultiViewUtils::get_breakpoints_states()`.
	 *
	 * @since ??
	 *
	 * @param array $value The value to check.
	 *
	 * @return bool Whether the given value is an array with specific breakpoints and states.
	 */
	public static function is_format_value( array $value ): bool {
		$breakpoint_states = self::get_breakpoints_states();
		$is_format_value   = $value && is_array( $value );

		if ( $is_format_value ) {
			foreach ( $value as $breakpoint => $states_values ) {
				if ( ! isset( $breakpoint_states[ $breakpoint ] ) || ! is_array( $states_values ) ) {
					return false;
				}

				$states = array_keys( $states_values );

				foreach ( $states as $state ) {
					if ( ! in_array( $state, $breakpoint_states[ $breakpoint ], true ) ) {
						return false;
					}
				}
			}
		}

		return $is_format_value;
	}

	/**
	 * Check if element should be hidden on load based on the given attribute.
	 *
	 * @since ??
	 *
	 * @param array $attr The attribute that needs to be checked.
	 * @param array $options {
	 *     Additional options for checking the value (optional).
	 *
	 *     @type string|null   $subName       Optional. The sub-name to extract from the attribute value. Default `null`.
	 *     @type callable|null $valueResolver Optional. A callable function to resolve the attribute value. Default `null`.
	 *     @type string|null   $inheritedMode Optional. The inherit mode specifying how the attribute value will be inherited.
	 *                                        One of `inherited`, `inheritedClosest`, `inheritedAll`, `inheritedOrClosest`,
	 *                                        `inheritedOrAll`, `closest`, `all`. Default `null`.
	 *
	 * @return bool Whether the given value is an array with specific breakpoints and states.
	 *
	 * @throws InvalidArgumentException If the provided `$options['valueResolver']` is not a callable function.
	 * @throws UnexpectedValueException If the attribute value or the value returned by the `$options['valueResolver']` function is not either `visible` or `hidden`.
	 */
	public static function is_hidden_on_load( array $attr, array $options = [] ): bool {
		$sub_name       = $options['subName'] ?? null;
		$value_resolver = $options['valueResolver'] ?? null;
		$inherited_mode = $options['inheritedMode'] ?? 'getAndInheritAll';

		if ( ! $attr || self::is_only_value( $attr ) ) {
			return false;
		}

		$is_hidden_on_load = ModuleUtils::has_value(
			$attr,
			[
				'subName'       => $sub_name,
				'inheritedMode' => $inherited_mode,
				'valueResolver' => function( $value, array $resolver_args ) use ( $value_resolver ) :bool {
					if ( $value_resolver ) {
						if ( is_callable( $value_resolver ) ) {
							$value = call_user_func( $value_resolver, $value, $resolver_args );
						} else {
							throw new InvalidArgumentException( 'The `valueResolver` argument must be a callable function' );
						}
					}

					if ( ! is_string( $value ) ) {
						throw new UnexpectedValueException( sprintf( 'Expected a string value, but a %s value was given', gettype( $value ) ) );
					}

					if ( ! in_array( $value, [ 'visible', 'hidden' ], true ) ) {
						throw new UnexpectedValueException( sprintf( 'Expected a `visible` or `hidden` value, but `%s` value was given', $value ) );
					}

					return 'hidden' === $value;
				},
			]
		);

		return $is_hidden_on_load;
	}

	/**
	 * Determines if the given value is a many value.
	 *
	 * @since ??
	 *
	 * @param array  $value              The value to check.
	 * @param string $default_breakpoint The default breakpoint to remove from the value array.
	 * @param string $default_state      The default state to remove from the value array.
	 *
	 * @return bool Returns true if the value is a many value.
	 */
	public static function is_many_value( array $value, ?string $default_breakpoint = null, ?string $default_state = null ) {
		return ! self::is_only_value( $value, $default_breakpoint, $default_state );
	}

	/**
	 * Determines if the given value is not a many value.
	 *
	 * @since ??
	 *
	 * @param array  $value              The value to check.
	 * @param string $default_breakpoint The default breakpoint to remove from the value array.
	 * @param string $default_state      The default state to remove from the value array.
	 *
	 * @throws InvalidArgumentException If the value is empty.
	 *
	 * @return bool Returns true if the value is not a many value.
	 */
	public static function is_only_value( array $value, ?string $default_breakpoint = null, ?string $default_state = null ) {
		if ( ! $value ) {
			throw new InvalidArgumentException( 'Value cannot be empty.' );
		}

		if ( ! $default_breakpoint || ! $default_state ) {
			$breakpoints_states_info = self::get_breakpoints_states_info();

			if ( ! $default_breakpoint ) {
				$default_breakpoint = $breakpoints_states_info->default_breakpoint();
			}

			if ( ! $default_state ) {
				$default_state = $breakpoints_states_info->default_state();
			}
		}

		$has_default_breakpoint_state = isset( $value[ $default_breakpoint ][ $default_state ] );

		if ( $has_default_breakpoint_state ) {
			// Remove the default state value from the default breakpoint.
			unset( $value[ $default_breakpoint ][ $default_state ] );

			// If there is no states remains within the default breakpoint, remove the default breakpoint.
			if ( empty( $value[ $default_breakpoint ] ) ) {
				unset( $value[ $default_breakpoint ] );

				return empty( $value );
			}
		}

		return false;
	}

	/**
	 * Check if the given value is not empty.
	 *
	 * This function checks if the given value is not empty by removing the default breakpoint and state from the value array.
	 * If the value becomes empty after removing the default breakpoint, it is also removed from the array.
	 *
	 * @since ??
	 *
	 * @param array  $value              The value array to check.
	 * @param string $default_breakpoint The default breakpoint to remove from the value array.
	 * @param string $default_state      The default state to remove from the value array.
	 *
	 * @return bool Returns true if the value is not empty, false otherwise.
	 */
	private static function _check_value( array $value, string $default_breakpoint, string $default_state ) {
		unset( $value[ $default_breakpoint ][ $default_state ] );

		if ( empty( $value[ $default_breakpoint ] ) ) {
			unset( $value[ $default_breakpoint ] );
		}

		return ! empty( $value );
	}

	/**
	 * Merge several key-value pair formatted attributes array into single formatted attributes array.
	 *
	 * The key will be used as attribute subname and the value is the formatted attributes array.
	 *
	 * This is useful when there are several attributes is needed to execute a single action.
	 * An example use case is when we need attribute `artistName` and attribute `albumName` to
	 * render Audio module meta element.
	 * In this case, we need to merge both attributes into single formatted attributes array before it is passed
	 * into the `data` param of `MultiViewUtils::populate_data_content()` method and can be processed at the same time.
	 *
	 * @since ??
	 *
	 * @param array $values A key-value pair array where the key will be used as attribute subname and
	 *                      the value is the formatted attributes array.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::merge_values([
	 *     'artistName' => [
	 *         'desktop' => [
	 *             'value' => 'Artist Foo',
	 *             'Hover' => 'Artist Bar',
	 *         ],
	 *         'tablet'  => [
	 *             'value' => 'Artist Baz',
	 *         ],
	 *     ],
	 *     'albumName'  => [
	 *         'desktop' => [
	 *             'value' => 'Album Foo',
	 *             'Hover' => 'Album Bar',
	 *         ],
	 *         'tablet'  => [
	 *             'value' => 'Album Baz',
	 *         ],
	 *     ],
	 * ]);
	 * ```
	 *
	 * @output:
	 * ```php
	 * [
	 *      'desktop' => [
	 *         'value' => [
	 *             'artistName' => 'Artist Foo',
	 *             'albumName'  => 'Album Foo',
	 *         ],
	 *         'hover' => [
	 *             'artistName' => 'Artist Bar',
	 *             'albumName'  => 'Album Bar',
	 *         ],
	 *      ],
	 *      'tablet'  => [
	 *         'value' => [
	 *             'artistName' => 'Artist Baz',
	 *             'albumName'  => 'Album Baz',
	 *         ],
	 *      ],
	 * ]
	 * ```
	 * @return array
	 */
	public static function merge_values( array $values ): array {
		$breakpoint_states = self::get_breakpoints_states();
		$merged            = [];

		foreach ( $values as $key => $attr ) {
			if ( ! is_array( $attr ) ) {
				continue;
			}

			$attr = self::normalize_value( $attr );

			foreach ( $attr as $breakpoint => $states_values ) {
				if ( ! isset( $breakpoint_states[ $breakpoint ] ) || ! is_array( $states_values ) ) {
					continue;
				}

				$states = array_keys( $states_values );

				foreach ( $states as $state ) {
					if ( ! in_array( $state, $breakpoint_states[ $breakpoint ], true ) ) {
						continue;
					}

					if ( ! isset( $merged[ $breakpoint ] ) ) {
						$merged[ $breakpoint ] = [];
					}

					if ( ! isset( $merged[ $breakpoint ][ $state ] ) ) {
						$merged[ $breakpoint ][ $state ] = [];
					}

					$merged[ $breakpoint ][ $state ][ $key ] = ModuleUtils::use_attr_value(
						[
							'attr'       => $attr,
							'breakpoint' => $breakpoint,
							'state'      => $state,
							'mode'       => 'getAndInheritAll',
						]
					);
				}
			}
		}

		return $merged;
	}

	/**
	 * Compare two values and return a boolean indicating whether they are equal.
	 *
	 * If both values are arrays, the function uses the ArrayUtility::diff() method to compare them.
	 *
	 * @since ??
	 *
	 * @param mixed $value1 The first value to compare.
	 * @param mixed $value2 The second value to compare.
	 *
	 * @return bool True if the values are equal, false otherwise.
	 */
	private static function _is_equal( $value1, $value2 ): bool {
		if ( $value1 === $value2 ) {
			return true;
		}

		if ( is_array( $value1 ) && is_array( $value2 ) ) {
			// Note : ArrayUtility::diff function is set instead of array_diff for considering multidimensional array compare.
			// Earlier for array_diff, it was throwing notice of array to string conversion for multi dimensional array.
			// Hence we needed one function which can get difference for multi dimensional array which will be recursive as well.
			return ! ArrayUtility::diff( $value1, $value2 ) && ! ArrayUtility::diff( $value2, $value1 );
		}

		return false;
	}

	/**
	 * Normalize multi view value.
	 *
	 * - When the default breakpoint and default state values is missing, it will be set.
	 * - When the $unique is false, this function will normalize the value by setting missing breakpoints and states to the default breakpoint and state.
	 * - When the $unique is true, this function will normalize the value by removing the breakpoints and states that have the same value as the previous breakpoint and state.
	 *
	 * @since ??
	 *
	 * @param array $value  The value that will be normalized.
	 * @param bool  $unique Optional. Whether to return unique value or not. Default is true.
	 *
	 * @throws InvalidArgumentException If the value is in correct format.
	 *
	 * @return array The normalized value.
	 */
	public static function normalize_value( array $value, ?bool $unique = true ): array {
		// If the value is empty, bail early.
		if ( empty( $value ) ) {
			return [];
		}

		$all_breakpoint_names = Breakpoint::get_all_breakpoint_names();

		$breakpoint_states_info       = self::get_breakpoints_states_info();
		$breakpoint_states            = $breakpoint_states_info->mapping();
		$default_breakpoint           = $breakpoint_states_info->default_breakpoint();
		$default_state                = $breakpoint_states_info->default_state();
		$has_default_breakpoint_state = isset( $value[ $default_breakpoint ][ $default_state ] );

		// Check if the value is multi view value, otherwise return the value as is.
		if ( $unique && self::is_only_value( $value, $default_breakpoint, $default_state ) ) {
			return $value;
		}

		$value_invalid   = $value;
		$normalized      = [];
		$prev_breakpoint = $default_breakpoint;

		foreach ( $breakpoint_states as $breakpoint => $states ) {
			$is_default_breakpoint = $breakpoint === $default_breakpoint;

			foreach ( $states as $state ) {
				$is_default_state = $state === $default_state;

				// Remove the state from the invalid value list.
				if ( isset( $value_invalid[ $breakpoint ][ $state ] ) ) {
					unset( $value_invalid[ $breakpoint ][ $state ] );
				}

				if ( ! isset( $value[ $breakpoint ][ $state ] ) ) {
					continue;
				}

				$state_value = $value[ $breakpoint ][ $state ];

				// Set the missing default breakpoint and default state values to an empty array or empty string.
				if ( ! $has_default_breakpoint_state ) {
					if ( ! isset( $normalized[ $default_breakpoint ] ) ) {
						$normalized[ $default_breakpoint ] = [];
					}

					if ( ! isset( $normalized[ $default_breakpoint ][ $default_state ] ) ) {
						$normalized[ $default_breakpoint ][ $default_state ] = is_array( $state_value ) ? [] : '';
					}

					$has_default_breakpoint_state = true;
				}

				// If the current state and breakpoint is same as default state and breakpoint, set the value directly.
				// Otherwise, get the value from the attribute and inherit the value from the previous breakpoint and state if it's not set.
				if ( $is_default_breakpoint && $is_default_state ) {
					if ( ! isset( $normalized[ $breakpoint ] ) ) {
						$normalized[ $breakpoint ] = [];
					}

					$normalized[ $breakpoint ][ $state ] = $state_value;
				} else {
					$state_value = ModuleUtils::use_attr_value(
						[
							'attr'       => $value,
							'breakpoint' => $breakpoint,
							'state'      => $state,
							'mode'       => 'getAndInheritAll',
						]
					);

					if ( $unique ) {
						if ( ! $is_default_state && isset( $normalized[ $breakpoint ][ $default_state ] ) ) {
							$value_to_compare = $normalized[ $breakpoint ][ $default_state ];
						} else {
							$value_to_compare = $normalized[ $prev_breakpoint ][ $default_state ] ?? $normalized[ $default_breakpoint ][ $default_state ] ?? null;
						}

						// If the current state value is equal to the previous state value, skip it.
						if ( self::_is_equal( $state_value, $value_to_compare ) ) {
							continue;
						}
					}

					if ( ! isset( $normalized[ $breakpoint ] ) ) {
						$normalized[ $breakpoint ] = [];
					}

					$normalized[ $breakpoint ][ $state ] = $state_value;
				}
			}

			// Remove the breakpoint from the invalid value list if the breakpoint is empty.
			if ( isset( $value_invalid[ $breakpoint ] ) && empty( $value_invalid[ $breakpoint ] ) ) {
				unset( $value_invalid[ $breakpoint ] );
			}

			$prev_breakpoint = $breakpoint;
		}

		// Unset breakpoint from list of invalid value if it is actually comes from valid breakpoint.
		// This happens if a attribute has value in certain breakpoint but that breakpoint is later disabled.
		// Without doing this, Divi will throw exception due to invalid value format.
		foreach ( $value_invalid as $breakpoint_name => $breakpoint_item ) {
			if ( in_array( $breakpoint_name, $all_breakpoint_names, true ) ) {
				unset( $value_invalid[ $breakpoint_name ] );
			}
		}

		// At this point, the $value_invalid should be empty. If not, throw an exception.
		if ( ! empty( $value_invalid ) ) {
			throw new InvalidArgumentException( 'Invalid value format: ' . wp_json_encode( $value_invalid ) );
		}

		// If the normalized is empty, bail early.
		if ( empty( $normalized ) ) {
			return [];
		}

		if ( ! $unique ) {
			// Set the previous breakpoint to the default breakpoint.
			$prev_breakpoint = $default_breakpoint;

			foreach ( $breakpoint_states as $breakpoint => $states ) {
				$is_default_breakpoint = $breakpoint === $default_breakpoint;

				foreach ( $states as $state ) {
					$is_default_state = $state === $default_state;

					// Skip default breakpoint and state.
					if ( $is_default_breakpoint && $is_default_state ) {
						continue;
					}

					// If the current breakpoint and state is already set, skip it.
					if ( isset( $normalized[ $breakpoint ][ $state ] ) ) {
						continue;
					}

					if ( ! $is_default_state && isset( $normalized[ $breakpoint ][ $default_state ] ) ) {
						$normalized[ $breakpoint ][ $state ] = $normalized[ $breakpoint ][ $default_state ];
					} else {
						$normalized[ $breakpoint ][ $state ] = $normalized[ $prev_breakpoint ][ $default_state ] ?? $normalized[ $default_breakpoint ][ $default_state ];
					}
				}

				// Update the previous breakpoint with the current breakpoint.
				$prev_breakpoint = $breakpoint;
			}
		}

		return $normalized;
	}

	/**
	 * Populate multi view data to set HTML element attributes.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $subName           Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type array    $data              A key-value pair array where the key is the attribute name and the value is a formatted breakpoint and state array.
	 *     @type callable $valueResolver     Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                         - The function has 2 arguments.
	 *                                         - The function 1st argument is the original attribute value that being processed.
	 *                                         - The function 2nd argument is a key-value pair array with 3 keys `attrName`, `breakpoint` and `state`.
	 *                                              - @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                              - @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                              - @type string $attrName   Current attribute name that being processed.
	 *                                         - The function must return a `string`.
	 *     @type array<callable> $sanitizers Optional. A key-value pair array where the key is the attribute name and the value is
	 *                                       function to sanitize/escape the attribute value.
	 *                                         - The function will be invoked after the `valueResolver` function.
	 *                                         - The function has 1 argument.
	 *                                         - The function 1st argument is the original attribute value that being sanitized.
	 *                                         - The function must return a `string`.
	 *     @type string   $tag               Optional - The element tag where the attributes will be used. Default `div`.
	 * }
	 *
	 * @throws InvalidArgumentException If the provided `$args['valueResolver']` is not a callable function or the `$args['data']` array has keys `style` and/or `class`.
	 * @throws UnexpectedValueException If the attribute value or the value returned by
	 *                                  the `$args['valueResolver']` function is not a string.
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::populate_data_attrs([
	 *     'data'          => [
	 *         'src' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/desktop.jpg',
	 *                     'title' => 'My Desktop Image',
	 *                 ],
	 *                 'hover' => [
	 *                     'url'   => 'http://example.com/hover.jpg',
	 *                     'title' => 'My Hover Image',
	 *                 ],
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/tablet.jpg',
	 *                     'title' => 'My Tablet Image',
	 *                 ],
	 *             ],
	 *         ],
	 *         'alt' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/desktop.jpg',
	 *                     'title' => 'My Desktop Image',
	 *                 ],
	 *                 'hover' => [
	 *                     'url'   => 'http://example.com/hover.jpg',
	 *                     'title' => 'My Hover Image',
	 *                 ],
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/tablet.jpg',
	 *                     'title' => 'My Tablet Image',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     'sanitizers'    => [
	 *         'src' => 'esc_url',
	 *         'alt' => 'esc_attr',
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'alt' === $resolver_args['attrName'] ) {
	 *             return $value['title'] ?? '';
	 *         }
	 *
	 *         return $value['url'] ?? '';
	 *     },
	 *     'tag'           => 'img',
	 * ]);
	 * ```
	 * @output:
	 * ```
	 * [
	 *     'desktop'        => [
	 *         'src' => 'http://example.com/desktop.jpg',
	 *         'alt' => 'My Desktop Image',
	 *     ],
	 *     'desktop--hover' => [
	 *         'src' => 'http://example.com/hover.jpg',
	 *         'alt' => 'My Hover Image',
	 *     ],
	 *     'tablet'         => [
	 *         'src' => 'http://example.com/tablet.jpg',
	 *         'alt' => 'My Tablet Image',
	 *     ],
	 * ]
	 * ```
	 */
	public static function populate_data_attrs( array $args ): array {
		$data_raw         = $args['data'];
		$sub_name         = $args['subName'] ?? null;
		$value_resolver   = $args['valueResolver'] ?? null;
		$sanitizers       = $args['sanitizers'] ?? [];
		$tag              = $args['tag'] ?? 'div';
		$populated        = [];
		$normalized_items = [];

		if ( ! $data_raw || ! is_array( $data_raw ) ) {
			return [];
		}

		foreach ( $data_raw as $attr_name => $attr ) {
			if ( 'style' === $attr_name ) {
				throw new InvalidArgumentException( 'Attribute `style` must be defined using MultiViewUtils::populate_data_style()' );
			}

			if ( 'class' === $attr_name ) {
				throw new InvalidArgumentException( 'Attribute `class` must be defined using MultiViewUtils::populate_data_class_name()' );
			}

			$valid_attr = HTMLUtility::is_valid_attribute( $attr_name, $tag );

			if ( ! $valid_attr ) {
				continue;
			}

			$sanitizer = $sanitizers[ $attr_name ] ?? null;

			if ( $sanitizer && ! is_callable( $sanitizer ) ) {
				throw new InvalidArgumentException( sprintf( 'The `sanitizers` argument must be an array of key-value pairs, where each key is associated with a callable function. Invalid key: `%s`.', $attr_name ) );
			}

			if ( $sanitizer ) {
				$sanitizers[ $attr_name ] = $sanitizer;
			} else {
				$sanitizers[ $attr_name ] = $valid_attr['sanitizer'] ?? 'esc_attr';
			}

			$attr_normalized = self::normalize_value( $attr );

			if ( $attr_normalized ) {
				$normalized_items[ $attr_name ] = $attr_normalized;
			}
		}

		if ( ! $normalized_items ) {
			return [];
		}

		foreach ( $normalized_items as $attr_name => $normalized ) {
			foreach ( $normalized as $breakpoint => $state_values ) {
				foreach ( $state_values as $state => $value ) {
					$data_key = self::data_key( $breakpoint, $state );

					if ( null === $data_key ) {
						continue;
					}

					if ( $sub_name ) {
						$value = ArrayUtility::get_value( $value ?? [], $sub_name, '' );
					}

					if ( $value_resolver ) {
						if ( is_callable( $value_resolver ) ) {
							$value = call_user_func(
								$value_resolver,
								$value,
								[
									'breakpoint' => $breakpoint,
									'state'      => $state,
									'attrName'   => $attr_name,
								]
							);
						} else {
							throw new InvalidArgumentException( 'The `valueResolver` argument must be a callable function' );
						}
					}

					if ( is_string( $value ) ) {
						$value = call_user_func( $sanitizers[ $attr_name ], $value );
					}

					if ( ! is_string( $value ) ) {
						throw new UnexpectedValueException( sprintf( 'Expected a string value, but %s value was given', gettype( $value ) ) );
					}

					if ( ! isset( $populated[ $data_key ] ) ) {
						$populated[ $data_key ] = [];
					}

					$populated[ $data_key ][ $attr_name ] = $value;
				}
			}
		}

		return self::unique_data( $populated );
	}

	/**
	 * Populate multi view data to set HTML element class name.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $data          A key-value pair array where the key is the class name and the value is a formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 3 keys `className`, `breakpoint` and `state`.
	 *                                          -- @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          -- @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                          -- @type string $className  Current class name that being processed.
	 *                                     - The function can return a boolean value.
	 *                                          -- Return `true` to add the class name to the element.
	 *                                          -- Return `false` to add the class name from the element.
	 *                                     - The function can return explicitly `add` or `remove` string value.
	 *                                          -- Return `add` to add the class name to the element.
	 *                                          -- Return `hidden` to add the class name from the element.
	 *                                     - The function will throw an `UnexpectedValueException` if the return value is not a boolean or `add` or `remove`.
	 *     @type callable $sanitizer     Optional. A function that will be invoked to sanitize/escape the class name. Default `esc_attr`.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 1 argument.
	 *                                     - The function 1st argument is the class name that being sanitized.
	 *                                     - The function must return a `string`.
	 * }
	 *
	 * @throws InvalidArgumentException If the provided `$args['valueResolver']` is not a callable function.
	 * @throws UnexpectedValueException If the attribute value or the value returned by the `$args['valueResolver']` function is not `add` or `remove`.
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::populate_data_class_name([
	 *     'data'          => [
	 *         'et-use-icon' => [
	 *             'desktop' => [
	 *                 'value' => 'on',
	 *                 'hover' => 'off',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => 'off',
	 *             ],
	 *         ],
	 *         'et-no-icon'   => [
	 *             'desktop' => [
	 *                 'value' => 'on',
	 *                 'hover' => 'off',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => 'off',
	 *             ],
	 *         ],
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'et-no-icon' === $resolver_args['className'] ) {
	 *             return 'off' === $value ? 'add' : 'remove';
	 *         }
	 *
	 *         return 'on' === $value ? 'add' : 'remove';
	 *     },
	 *     'sanitizer'     => 'et_core_esc_previously',
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'desktop'        => [
	 *         'add'    => ['et-use-icon'],
	 *         'remove' => ['et-no-icon'],
	 *     ],
	 *     'desktop--hover' => [
	 *         'add'    => ['et-no-icon'],
	 *         'remove' => ['et-use-icon'],
	 *     ],
	 *     'tablet'         => [
	 *         'add'    => ['et-no-icon'],
	 *         'remove' => ['et-use-icon'],
	 *     ],
	 * ]
	 * ```
	 */
	public static function populate_data_class_name( array $args ):array {
		$data_raw         = $args['data'];
		$sub_name         = $args['subName'] ?? null;
		$value_resolver   = $args['valueResolver'] ?? null;
		$sanitizer        = $args['sanitizer'] ?? 'sanitize_html_class';
		$populated        = [];
		$normalized_items = [];

		if ( ! $data_raw || ! is_array( $data_raw ) ) {
			return [];
		}

		foreach ( $data_raw as $class_name => $attr ) {
			$attr_normalized = self::normalize_value( $attr );

			if ( $attr_normalized ) {
				$normalized_items[ $class_name ] = $attr_normalized;
			}
		}

		if ( ! $normalized_items ) {
			return [];
		}

		foreach ( $normalized_items as $class_name => $normalized ) {
			foreach ( $normalized as $breakpoint => $state_values ) {
				foreach ( $state_values as $state => $value ) {
					$data_key = self::data_key( $breakpoint, $state );

					if ( null === $data_key ) {
						continue;
					}

					if ( $sub_name ) {
						$value = ArrayUtility::get_value( $value ?? [], $sub_name, '' );
					}

					if ( $value_resolver ) {
						if ( is_callable( $value_resolver ) ) {
							$value = call_user_func(
								$value_resolver,
								$value,
								[
									'className'  => $class_name,
									'breakpoint' => $breakpoint,
									'state'      => $state,
								]
							);
						} else {
							throw new InvalidArgumentException( 'The `valueResolver` argument must be a callable function' );
						}
					}

					// If the `$value` is not `add` or `remove` string, then we will try to convert it to boolean.
					if ( 'add' !== $value && 'remove' !== $value ) {
						// Covert scalar value to boolean.
						if ( is_scalar( $value ) ) {
							$value = ! ! $value;
						}

						// Null is assumed to be `false`.
						if ( null === $value ) {
							$value = false;
						}
					}

					// Convert boolean to string as `add` or `remove`.
					if ( is_bool( $value ) ) {
						$value = $value ? 'add' : 'remove';
					}

					if ( ! is_string( $value ) ) {
						throw new UnexpectedValueException( sprintf( 'Expected a string value, but %s value was given', gettype( $value ) ) );
					}

					if ( ! in_array( $value, [ 'add', 'remove' ], true ) ) {
						throw new UnexpectedValueException( sprintf( 'Expected a `add` or `remove` value, but `%s` value was given', $value ) );
					}

					if ( ! isset( $populated[ $data_key ] ) ) {
						$populated[ $data_key ] = [];
					}

					if ( ! isset( $populated[ $data_key ][ $value ] ) ) {
						$populated[ $data_key ][ $value ] = [];
					}

					$populated[ $data_key ][ $value ][] = call_user_func( $sanitizer, $class_name );
				}
			}
		}

		return self::unique_data( $populated );
	}

	/**
	 * Populate multi view data to set HTML element inner content.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 2 keys `breakpoint` and `state`.
	 *                                          - @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          - @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                     - The function must return a `string`.
	 *     @type callable $sanitizer     Optional. A function that will be invoked to sanitize/escape the value. Default `esc_html`.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 1 argument.
	 *                                     - The function 1st argument is the original attribute value that being sanitized.
	 *                                     - The function must return a `string`.
	 * }
	 *
	 * @throws InvalidArgumentException  If the provided `$args['valueResolver']` is not a callable function.
	 * @throws UnexpectedValueException  If in debug mode i.e `Conditions::is_debug_mode() ===  true`, and the attribute value or the value returned by
	 *                                   the `$args['valueResolver']` function is not a string.
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::populate_data_content([
	 *     'data'          => [
	 *         'desktop' => [
	 *             'value' => '<p>Foo</p>',
	 *             'hover' => '<p>Bar</p>',
	 *         ],
	 *         'tablet' => [
	 *             'value' => '<p>Baz</p>',
	 *         ],
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'phone' === $resolver_args['breakpoint'] ) {
	 *             return $value . '<p>Custom Baz</p>';
	 *         }
	 *
	 *         return $value;
	 *     },
	 *     'sanitizer'     => 'et_core_esc_previously',
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'desktop'        => '<p>Foo</p>',
	 *     'desktop--hover' => '<p>Bar</p>',
	 *     'tablet'         => '<p>Baz</p>',
	 *     'phone'          => '<p>Baz</p><p>Custom Baz</p>',
	 * ]
	 * ```
	 */
	public static function populate_data_content( array $args ): array {
		$data_raw       = $args['data'];
		$sub_name       = $args['subName'] ?? null;
		$value_resolver = $args['valueResolver'] ?? null;
		$sanitizer      = $args['sanitizer'] ?? 'esc_html';
		$populated      = [];
		$normalized     = self::normalize_value( $data_raw );

		if ( ! $normalized ) {
			return [];
		}

		foreach ( $normalized as $breakpoint => $state_values ) {
			foreach ( $state_values as $state => $value ) {
				$data_key = self::data_key( $breakpoint, $state );

				if ( null === $data_key ) {
					continue;
				}

				if ( $sub_name ) {
					$value = ArrayUtility::get_value( $value ?? [], $sub_name, '' );
				}

				if ( $value_resolver ) {
					if ( is_callable( $value_resolver ) ) {
						$value = call_user_func(
							$value_resolver,
							$value,
							[
								'breakpoint' => $breakpoint,
								'state'      => $state,
							]
						);
					} else {
						throw new InvalidArgumentException( 'The `valueResolver` argument must be a callable function' );
					}
				}

				if ( is_string( $value ) ) {
					$value = call_user_func( $sanitizer, $value );
				}

				if ( ! is_string( $value ) ) {
					throw new UnexpectedValueException( sprintf( 'Expected a string value, but a %s value was given', gettype( $value ) ) );
				}

				$populated[ $data_key ] = $value;
			}
		}

		return self::unique_data( $populated );
	}

	/**
	 * Populate multi view data to set HTML element inline style.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $data          A key-value pair array where the key is the CSS property name and the value is a formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 3 keys `property`, `breakpoint` and `state`.
	 *                                          - @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          - @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                          - @type string $property   Current CSS property name that being processed.
	 *                                     - The function must return a `string`.
	 *     @type callable $sanitizer     Optional. A function that will be invoked to sanitize/escape the value. Default `SavingUtility::sanitize_css_properties`.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being sanitized.
	 *                                     - The function 2nd argument is the CSS property name that being sanitized.
	 *                                     - The function must return a `string`.
	 * }
	 *
	 * @throws InvalidArgumentException If the provided `$args['valueResolver']` is not a callable function.
	 * @throws UnexpectedValueException If in debug mode i.e `Conditions::is_debug_mode() ===  true`, and the attribute value or the value returned by
	 *                                  the `$args['valueResolver']` function is not a string.
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::populate_data_style([
	 *     'data'          => [
	 *         'background-color' => [
	 *             'desktop' => [
	 *                 'value' => '#aaa',
	 *                 'hover' => '#bbb',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => '#ccc',
	 *             ],
	 *         ],
	 *         'background-image' => [
	 *             'desktop' => [
	 *                 'value' => 'http://example.com/desktop.jpg',
	 *                 'hover' => 'http://example.com/hover.jpg',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => 'http://example.com/tablet.jpg',
	 *             ],
	 *         ],
	 *     ],
	 *     'sanitizer'     => 'esc_attr',
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'background-image' === $resolver_args['property'] ) {
	 *             return 'url(' . $value . ')';
	 *         }
	 *
	 *         return $value;
	 *     },
	 * ]);
	 * ```

	 * @output:
	 * ```php
	 * [
	 *     'desktop'        => [
	 *         'background-color' => '#aaa',
	 *         'background-image' => 'url(http://example.com/desktop.jpg)',
	 *     ],
	 *     'desktop--hover' => [
	 *         'background-color' => '#bbb',
	 *         'background-image' => 'url(http://example.com/hover.jpg)',
	 *     ],
	 *     'tablet'         => [
	 *         'background-color' => '#ccc',
	 *         'background-image' => 'url(http://example.com/tablet.jpg)',
	 *     ],
	 * ]
	 * ```
	 */
	public static function populate_data_style( array $args ): array {
		$data_raw         = $args['data'];
		$sub_name         = $args['subName'] ?? null;
		$value_resolver   = $args['valueResolver'] ?? null;
		$sanitizer        = $args['sanitizer'] ?? [ SavingUtility::class, 'sanitize_css_properties' ];
		$populated        = [];
		$normalized_items = [];

		if ( ! $data_raw || ! is_array( $data_raw ) ) {
			return [];
		}

		foreach ( $data_raw as $property => $attr ) {
			$attr_normalized = self::normalize_value( $attr );

			if ( $attr_normalized ) {
				$normalized_items[ $property ] = $attr_normalized;
			}
		}

		if ( ! $normalized_items ) {
			return [];
		}

		foreach ( $normalized_items as $property => $normalized ) {
			foreach ( $normalized as $breakpoint => $state_values ) {
				foreach ( $state_values as $state => $value ) {
					$data_key = self::data_key( $breakpoint, $state );

					if ( null === $data_key ) {
						continue;
					}

					if ( $sub_name ) {
						$value = ArrayUtility::get_value( $value ?? [], $sub_name, '' );
					}

					if ( $value_resolver ) {
						if ( is_callable( $value_resolver ) ) {
							$value = call_user_func(
								$value_resolver,
								$value,
								[
									'property'   => $property,
									'breakpoint' => $breakpoint,
									'state'      => $state,
								]
							);
						} else {
							throw new InvalidArgumentException( 'The `valueResolver` argument must be a callable function' );
						}
					}

					if ( is_string( $value ) ) {
						$value = call_user_func( $sanitizer, $property . ':' . $value );

						if ( $value ) {
							$value = substr_replace( $value, '', 0, strlen( $property . ':' ) );
						}
					}

					if ( ! is_string( $value ) ) {
						throw new UnexpectedValueException( sprintf( 'Expected a string value, but %s value was given', gettype( $value ) ) );
					}

					if ( ! isset( $populated[ $data_key ] ) ) {
						$populated[ $data_key ] = [];
					}

					$populated[ $data_key ][ $property ] = $value;
				}
			}
		}

		return self::unique_data( $populated );
	}

	/**
	 * Populate multi view data to set HTML element visibility.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 2 keys `breakpoint` and `state`.
	 *                                          -- @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          -- @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                     - The function can return a boolean value.
	 *                                          -- Return `true` to indicate the element is visible.
	 *                                          -- Return `false` to indicate the element is hidden.
	 *                                     - The function can return explicitly `visible` or `hidden` string value.
	 *                                          -- Return `visible` to indicate the element is visible.
	 *                                          -- Return `hidden` to indicate the element is hidden.
	 *                                     - The function will throw an `UnexpectedValueException` if the return value is not either `visible` or `hidden`.
	 * }
	 *
	 * @throws InvalidArgumentException If the provided `$args['valueResolver']` is not a callable function.
	 * @throws UnexpectedValueException If the attribute value or the value returned by the `$args['valueResolver']` function is not either `visible` or `hidden`.
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::populate_data_visibility([
	 *     'data'          => [
	 *         'desktop' => [
	 *             'value' => 'on',
	 *             'hover' => 'off',
	 *         ],
	 *         'tablet' => [
	 *             'value' => 'off',
	 *         ],
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         return 'on' === $value ? 'visible' : 'hidden';
	 *     },
	 * ]);
	 * ```
	 *
	 * @output:
	 * ```php
	 * [
	 *     'desktop'        => 'visible',
	 *     'desktop--hover' => 'hidden',
	 *     'tablet'         => 'hidden',
	 * ]
	 * ```
	 */
	public static function populate_data_visibility( array $args ): array {
		$data_raw       = $args['data'] ?? [];
		$sub_name       = $args['subName'] ?? null;
		$value_resolver = $args['valueResolver'] ?? null;
		$populated      = [];
		$normalized     = self::normalize_value( $data_raw );

		if ( ! $normalized ) {
			return [];
		}

		foreach ( $normalized as $breakpoint => $state_values ) {
			foreach ( $state_values as $state => $value ) {
				$data_key = self::data_key( $breakpoint, $state );

				if ( null === $data_key ) {
					continue;
				}

				if ( $sub_name ) {
					$value = ArrayUtility::get_value( $value ?? [], $sub_name, '' );
				}

				if ( $value_resolver ) {
					if ( is_callable( $value_resolver ) ) {
						$value = call_user_func(
							$value_resolver,
							$value,
							[
								'breakpoint' => $breakpoint,
								'state'      => $state,
							]
						);
					} else {
						throw new InvalidArgumentException( 'The `valueResolver` argument must be a callable function' );
					}
				}

				// If the `$result` is not `visible` or `hidden` string, then we will try to convert it to boolean.
				if ( 'visible' !== $value && 'hidden' !== $value ) {
					// Covert scalar value to boolean.
					if ( is_scalar( $value ) ) {
						$value = ! ! $value;
					}

					// Null is assumed to be `false`.
					if ( null === $value ) {
						$value = false;
					}
				}

				// Convert boolean to string as `visible` or `hidden`.
				if ( is_bool( $value ) ) {
					$value = $value ? 'visible' : 'hidden';
				}

				if ( ! is_string( $value ) ) {
					throw new UnexpectedValueException( sprintf( 'Expected a string value, but %s value was given', gettype( $value ) ) );
				}

				if ( ! in_array( $value, [ 'visible', 'hidden' ], true ) ) {
					throw new UnexpectedValueException( sprintf( 'Expected a `visible` or `hidden` value, but `%s` value was given', $value ) );
				}

				$populated[ $data_key ] = $value;
			}
		}

		return self::unique_data( $populated );
	}

	/**
	 * Get unique data across all breakpoints and states.
	 *
	 * @since ??
	 *
	 * @param array $data Raw data.
	 *
	 * @return array
	 */
	public static function unique_data( array $data, array $breakpoint_names = null ):array {
		$unique_data = [];

		// Get breakpoint names, sorted from large to small.
		// TODO feat(D5, Customizable Breakpoints) Added more test case for newly introduced breakpoints.
		$breakpoint_names = $breakpoint_names ?? Breakpoint::get_enabled_breakpoint_names() ?? [
			'desktop',
			'tablet',
			'phone',
		];

		$breakpoint_size = count( $breakpoint_names );

		// Get base breakpoint.
		$base_breakpoint = 'desktop';

		// Get base breakpoint index.
		$base_breakpoint_index = array_search( $base_breakpoint, $breakpoint_names, true );

		foreach ( $data as $key => $value ) {
			$parts      = explode( '--', $key );
			$breakpoint = $parts[0];
			$state      = $parts[1] ?? 'value';

			// Verify if given breakpoint and state is valid or not based on the enabled breakpoints + state.
			if ( null === self::data_key( $breakpoint, $state ) ) {
				continue;
			}

			$skip = false;

			// Get breakpoint index.
			$breakpoint_index = array_search( $breakpoint, $breakpoint_names, true );

			// Check if breakpoint index is valid or not.
			$is_valid_breakpoint_index = $breakpoint_index > -1 && $breakpoint_index < $breakpoint_size;

			if ( ! $is_valid_breakpoint_index ) {
				continue;
			}

			// Check if state is default.
			$is_default_state = 'value' === $state;

			// Check if current breakpoint is larger or smaller than base breakpoint.
			$is_larger_than_base_breakpoint = $breakpoint_index < $base_breakpoint_index;

			// Inherited value's initial value.
			$value_inherit = null;

			// If current $value is not default state, look for value from its own breakpoint first.
			if ( ! $is_default_state && isset( $data[ $breakpoint ] ) ) {
				$value_inherit = $data[ $breakpoint ];

				$skip = $value_inherit === $value;
			}

			// If $value_inherit remains null, look for values from breakpoint next to it.
			if ( is_null( $value_inherit ) ) {
				// Calculate maximum possible loop to get inherited value.
				$max_loop = $is_larger_than_base_breakpoint
					? $base_breakpoint_index - $breakpoint_index
					: $breakpoint_index - $base_breakpoint_index;

				// If max loop is `0`, it means current breakpoint is the base breakpoint thus no need to get
				// inherited value because base breakpoint doesn't inherit value from other breakpoint.
				if ( $max_loop > 0 ) {
					// Find inherited value by loop until value is found or max loop value is reached. IMPORTANT:
					// 1. If the breakpoint index is smaller than base breakpoint index, the iteration is from small to large
					// 2. If the breakpoint index is larger than base breakpoint index, the iteration is from large to small.
					for ( $iteration_index = 0; $iteration_index < $max_loop; $iteration_index++ ) {
						// Get the index of current breakpoint, relative to the $breakpoint_names.
						$current_breakpoint_index = $is_larger_than_base_breakpoint
							? $base_breakpoint_index - ( $max_loop - $iteration_index ) + 1
							: $base_breakpoint_index + ( $max_loop - $iteration_index ) - 1;

						// Get current breakpoint name.
						$current_breakpoint_name = $breakpoint_names[ $current_breakpoint_index ];

						// Get current breakpoint's value.
						$current_breakpoint_value = $data[ $current_breakpoint_name ] ?? null;

						// Set current breakpoint's value as inherited value if it's not null.
						if ( ! is_null( $current_breakpoint_value ) ) {
							$value_inherit = $current_breakpoint_value;
						}

						// Update skip value based on currently set `$value_inherit` against `$value`.
						$skip = $value_inherit === $value;

						if ( ! is_null( $value_inherit ) ) {
							break;
						}
					}
				}
			}

			if ( $skip ) {
				continue;
			}

			$unique_data[ $key ] = $value;
		}

		return $unique_data;
	}
}
