<?php
/**
 * Module Utils Class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use Divi\D5_Readiness\Server\Checks\FeatureCheck;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\Utility\TextTransform;
use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Fonts;
use ET\Builder\Packages\GlobalData\GlobalPresetItemUtils;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\Packages\GlobalData\GlobalPresetItem;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use Rogervila\ArrayDiffMultidimensional;
use WP_Block_Type_Registry;
use InvalidArgumentException;

/**
 * ModuleUtils class.
 *
 * This class provides utility methods for modules.
 *
 * @since ??
 */
class ModuleUtils {
	/**
	 * Cache group
	 *
	 * @var string
	 */
	public static $cache_group = 'divi_module_utils';

	/**
	 * Get the module breakpoints.
	 *
	 * Retrieves an array of module breakpoints including `desktop`, `tablet`, and `phone`.
	 * This function runs the value through the `divi_module_utils_breakpoints` filter.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module-utils/variables/breakpoints breakpoints } located in `@divi/module-utils`.
	 *
	 * @since ??
	 *
	 * @return array The module breakpoints.
	 *
	 * @example:
	 * ```
	 * $breakpoints = ModuleUtils::breakpoints();
	 *
	 * // Output: ['desktop', 'tablet', 'phone']
	 * ```
	 *
	 * @example:
	 * ```php
	 * $breakpoints = apply_filters( 'divi_module_utils_breakpoints', ['desktop', 'tablet', 'phone'] );
	 *
	 * // Output: ['desktop', 'tablet', 'phone']
	 * ```
	 */
	public static function breakpoints(): array {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes.
		// Right now we're using WordPress' `_deprecated_function()` but technically the second parameter here is
		// expected to be WordPress' version, not Divi version. However due to time constraint, we're using Divi version
		// here at the time being.
		// @see https://github.com/elegantthemes/Divi/issues/41575
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-5', 'Breakpoint::get_enabled_breakpoint_names' );

		return Breakpoint::get_enabled_breakpoint_names();
	}

	/**
	 * Retrieve the inherited attribute value based on the given arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module-utils/functions/inheritAttrValue inheritAttrValue} located in
	 * `@divi/module-utils`.
	 *
	 * This function takes an array of arguments and returns the value of the specified attribute.
	 * It first parses the arguments using `wp_parse_args()` and then retrieves the attribute value based on the provided `breakpoint`, `state`, and `mode`.
	 * If the attribute value for the specified `breakpoint` and `state` is not found, it retrieves the inherited value based on the specified `mode`.
	 * If no value is found, it returns `null`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $attr        An array of attribute data.
	 *     @type string $breakpoint  The breakpoint to inherit from.
	 *     @type string $state       The state of the attribute.
	 *     @type string $inheritMode Optional. The mode of inheritance. Default `all`.
	 * }
	 *
	 * @return mixed|null The value of the attribute based on the specified arguments, or null if no value is found.
	 *
	 * @example:
	 * ``php
	 * // Get the value of the 'color' attribute for the 'tablet' breakpoint and 'hover' state.
	 * $args = [
	 *     'attr' => [
	 *         'desktop' => [
	 *             'hover' => '#000000',
	 *         ],
	 *         'tablet' => [
	 *             'hover' => '#ffffff',
	 *         ],
	 *         'phone' => [
	 *             'hover' => '#cccccc',
	 *         ],
	 *     ],
	 *     'breakpoint' => 'tablet',
	 *     'state' => 'hover',
	 *     'inheritMode' => 'all',
	 * ];
	 *
	 * $value = ModuleUtils::inherit_attr_value( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Get the value of the 'font-size' attribute for the 'phone' breakpoint and 'value' state,
	 * // and inherit the closest value from larger breakpoints.
	 * $args = [
	 *     'attr' => [
	 *         'desktop' => [
	 *             'value' => '14px',
	 *         ],
	 *         'tablet' => [
	 *             'value' => '16px',
	 *         ],
	 *         'phone' => [
	 *             'value' => '18px',
	 *         ],
	 *     ],
	 *     'breakpoint' => 'phone',
	 *     'state' => 'value',
	 *     'inheritMode' => 'closest',
	 * ];
	 *
	 * $value = ModuleUtils::inherit_attr_value( $args );
	 * ```
	 */
	public static function inherit_attr_value( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'inheritMode'     => 'all',
				'baseBreakpoint'  => 'desktop',
				'breakpointNames' => Breakpoint::get_default_breakpoint_names(),
			]
		);

		$attr             = $args['attr'];
		$base_breakpoint  = $args['baseBreakpoint'];
		$breakpoint       = $args['breakpoint'];
		$breakpoint_names = $args['breakpointNames'];
		$state            = $args['state'];
		$inherit_mode     = $args['inheritMode'];

		// `state` has no order. If the state is not `value`, it means it'll fallback to existing breakpoint + value
		// before fallback to larger breakpoint + value.
		$is_default_state   = 'value' === $state;
		$is_base_breakpoint = $base_breakpoint === $breakpoint;

		// if baseBreakpoint does not exist on breakpointsName, return null.
		if ( false === array_search( $base_breakpoint, $breakpoint_names, true ) ) {
			return null;
		}

		// No breakpoint / state to fallback into. Exit early.
		if ( $is_base_breakpoint && $is_default_state ) {
			return null;
		}

		// Get base breakpoint index.
		$base_breakpoint_index = array_search( $base_breakpoint, $breakpoint_names, true );

		// Get breakpoint index.
		$breakpoint_index = array_search( $breakpoint, $breakpoint_names, true );

		// Check if current breakpoint is wider (min/max-width of media query wise) than base breakpoint.
		$is_wider_than_base_breakpoint = $base_breakpoint_index > $breakpoint_index;

		// Inherit mechanism is derived from base breakpoint, not simply from larger to smaller. Thus in the case of
		// [`ultraWide`, `widescreen`, `desktop`, `tabletWide`, `tablet`, `phoneWide`, `phone`] where the base
		// breakpoint is `desktop`, the breakpoint larger than `desktop` inherits value from `desktop` (in reverse) while
		// breakpoint smaller than `desktop` ALSO inherits value from `desktop`. See the following slack canvas for more:
		// https://elegantthemes.slack.com/docs/T0J2HJAJ2/F08A2KM7BQB
		$filtered_breakpoint_names = $is_wider_than_base_breakpoint
			? array_reverse( array_slice( $breakpoint_names, 0, $base_breakpoint_index + 1 ) )
			: array_slice( $breakpoint_names, $base_breakpoint_index, count( $breakpoint_names ) );

		// `breakpoints` are ordered in order (pun intended) of size. Thus breakpoints in previous order are
		// guaranteed to be larger breakpoint and cascaded in terms of order.
		$breakpoint_index_on_filtered_breakpoint_names = array_search( $breakpoint, $filtered_breakpoint_names, true );

		// Breakpoints that has larger order (NOT has larger window width) than given breakpoint.
		// The matching breakpoint then needs to be reserved so it is the fallback order.
		$larger_order_breakpoints = array_reverse( array_slice( $filtered_breakpoint_names, 0, $breakpoint_index_on_filtered_breakpoint_names ) );

		// NOTE: The order should be reversed so it fallback in order.
		// Populate inherited attr value.
		$inherited_attr_value = null;

		// If current state isn't default, get value of current breakpoint's default state value.
		if ( ! $is_default_state && isset( $attr[ $breakpoint ]['value'] ) ) {
			$inherited_attr_value = $attr[ $breakpoint ]['value'];
		}

		// Loop for larger breakpoint's default state value.
		$larger_order_breakpoints_count = count( $larger_order_breakpoints );
		for ( $larger_order_breakpoints_index = 0; $larger_order_breakpoints_index < $larger_order_breakpoints_count; $larger_order_breakpoints_index++ ) {
			$current_larger_breakpoint     = $larger_order_breakpoints[ $larger_order_breakpoints_index ];
			$larger_order_breakpoint_value = $attr[ $current_larger_breakpoint ]['value'] ?? null;

			// If the attribute value is object and inheritMode is all (combined all possible inherited value),
			// merge all object from larger breakpoints.
			if ( is_array( $larger_order_breakpoint_value ) && 'all' === $inherit_mode ) {
				$inherited_attr_value = array_replace_recursive( $larger_order_breakpoint_value, (array) $inherited_attr_value );

				// If the attribute value is 1) not object, or 2) an object but inheritMode is closest,
				// simply overwrite the closest one if it isn't exist yet.
			} elseif ( null !== $larger_order_breakpoint_value && null === $inherited_attr_value ) {
				$inherited_attr_value = $larger_order_breakpoint_value;

				// Break loop once valid inherited attr value is found.
				break;

				// Prevent unnecessary loop. Might fall into this if state is not default and inherited attr value
				// is already found.
			} elseif ( null !== $inherited_attr_value && 'closest' === $inherit_mode ) {
				// Break loop once valid inherited attr value is found.
				break;
			}
		}

		return $inherited_attr_value;
	}

	/**
	 * Get an array of breakpoints used for inheritance.
	 *
	 * The static array returned by this function represents the breakpoints for responsive views used in the inheritance logic.
	 *
	 * Top level keys are of type `breakpoint` and second level keys are of type `AttrState`.
	 * The values of the second level keys are arrays of length 2, where both elements are strings.
	 *
	 * @since ??
	 *
	 * @return array The array of breakpoints used for inheritance.
	 *
	 * @example:
	 * ```php
	 * $inheritance = ModuleUtils::inherit_breakpoints();
	 * // Returns:
	 * // [
	 * //    'phone' => [
	 * //        'sticky' => ['phone', 'value'],
	 * //        'hover' => ['phone', 'value'],
	 * //        'value' => ['tablet', 'value']
	 * //    ],
	 * //    'tablet' => [
	 * //        'sticky' => ['tablet', 'value'],
	 * //        'hover' => ['tablet', 'value'],
	 * //        'value' => ['desktop', 'value']
	 * //    ],
	 * //    'desktop' => [
	 * //        'sticky' => ['desktop', 'value'],
	 * //        'hover' => ['desktop', 'value'],
	 * //        'value' => ['desktop', 'value']
	 * //    ]
	 * // ]
	 * ```
	 */
	public static function inherit_breakpoints(): array {
		// TODO feat(D5, Responsive Views): replace this static array with a dynamic one generated from the Builder's settings.
		return [
			'phone'   => [
				'sticky' => [
					'phone',
					'value',
				],
				'hover'  => [
					'phone',
					'value',
				],
				'value'  => [
					'tablet',
					'value',
				],
			],
			'tablet'  => [
				'sticky' => [
					'tablet',
					'value',
				],
				'hover'  => [
					'tablet',
					'value',
				],
				'value'  => [
					'desktop',
					'value',
				],
			],
			'desktop' => [
				'sticky' => [
					'desktop',
					'value',
				],
				'hover'  => [
					'desktop',
					'value',
				],
				'value'  => [
					'desktop',
					'value',
				],
			],
		];
	}

	/**
	 * Generates an inherit breakpoint map based on a base breakpoint and a list of breakpoint names.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $base_breakpoint The base breakpoint name.
	 *     @type array $breakpoint_names List of breakpoint names.
	 * }
	 *
	 * @return array Inherit breakpoint map.
	 */
	public static function get_inherit_breakpoint_map( array $args ): array {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		$base_breakpoint  = $args['base_breakpoint'] ?? 'desktop';
		$breakpoint_names = $args['breakpoint_names'] ?? [
			'desktop',
			'tablet',
			'phone',
		];

		$inherit_breakpoint_map = [];

		$base_breakpoint_index = array_search( $base_breakpoint, $breakpoint_names, true );

		foreach ( $breakpoint_names as $index => $name ) {
			if ( $base_breakpoint === $name ) {
				$inherit_breakpoint_map[ $name ] = [
					'sticky' => [ $name, 'value' ],
					'hover'  => [ $name, 'value' ],
					'value'  => [ $name, 'value' ],
				];
			} else {
				$inherit_breakpoint_index = $index > $base_breakpoint_index ? $index - 1 : $index + 1;

				$inherit_breakpoint_map[ $name ] = [
					'sticky' => [ $name, 'value' ],
					'hover'  => [ $name, 'value' ],
					'value'  => [ $breakpoint_names[ $inherit_breakpoint_index ], 'value' ],
				];
			}
		}

		// Cache the result.
		wp_cache_set( $cache_key, $inherit_breakpoint_map, self::$cache_group );

		return $inherit_breakpoint_map;
	}

	/**
	 * Retrieve the value of an attribute based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module-utils/functions/getAttrValue/ getAttrValue} located in
	 * `@divi/module-utils`.
	 *
	 * This function takes an array of arguments and returns the value of the specified attribute.
	 * The function first parses the arguments using `wp_parse_args()`. It then retrieves the attribute value based on the specified breakpoint, state, and mode.
	 * If the attribute value for the specified breakpoint and state is not found, it retrieves the inherited value based on the specified mode.
	 * If no value is found, the function returns the default value specified in the arguments.
	 *
	 * Getter and inheritance model can be changed based on `mode` parameter:
	 * 1. `get`                  : Get attr value of given breakpoint + state.
	 * 2. `getAndInheritAll`     : Get attr value combined by all possible inherited attr value on all larger breakpoints.
	 * 3. `getAndInheritClosest` : Get attr value combined by inherited attr value from closest available breakpoint.
	 * 4. `getOrInheritAll`      : Get attr value or inherited attr value from all larger breakpoints.
	 * 5. `getOrInheritClosest`  : Get attr value or inherited attr value from closest available breakpoint.
	 * 6. `inheritAll`           : Get inherited attr value from all larger breakpoints.
	 * 7. `inheritClosest`       : Get inherited attr value from all closest available breakpoint.
	 *
	 *
	 * See below for inherited attribute fallback flow:
	 *
	 * |        | value | hover | sticky |
	 * |--------|-------|-------|--------|
	 * | Desktop|   *   |  <--  |  <--   |
	 * | Tablet |   ^   |  <--  |  <--   |
	 * | Phone  |   ^   |  <--  |  <--   |
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array        $attr          The attribute to retrieve the value from.
	 *     @type string       $breakpoint    The breakpoint.
	 *     @type string       $state         The state.
	 *     @type string       $mode          Optional. The mode. Default `getOrInheritAll`.
	 *     @type mixed|null   $defaultValue  Optional. The default value. Default `null`.
	 *     @type string       $baseBreakpoint Optional. The base breakpoint. Default `desktop`.
	 *     @type array        $breakpointNames Optional. The breakpoint names. Default `['desktop', 'tablet', 'phone']`.
	 * }
	 *
	 * @return mixed|null The value of the attribute based on the specified arguments, or the default value if no value is found.
	 */
	public static function get_attr_value( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'mode'            => 'getOrInheritAll',
				'defaultValue'    => null,
				'baseBreakpoint'  => 'desktop',
				'breakpointNames' => Breakpoint::get_default_breakpoint_names(),
			]
		);

		$attr             = $args['attr'];
		$base_breakpoint  = $args['baseBreakpoint'];
		$breakpoint       = $args['breakpoint'];
		$breakpoint_names = $args['breakpointNames'];
		$state            = $args['state'];
		$mode             = $args['mode'];
		$default_value    = $args['defaultValue'];

		// Get attribute value.
		$attr_value = isset( $attr[ $breakpoint ][ $state ] ) ? $attr[ $breakpoint ][ $state ] : null;

		// Get inherited value.
		$inherited_attr_value = null;

		switch ( $mode ) {
			case 'getAndInheritClosest':
			case 'getOrInheritClosest':
			case 'inheritClosest':
				$inherited_attr_value = self::inherit_attr_value(
					[
						'attr'            => $attr,
						'baseBreakpoint'  => $base_breakpoint,
						'breakpoint'      => $breakpoint,
						'breakpointNames' => $breakpoint_names,
						'state'           => $state,
						'inheritMode'     => 'closest',
					]
				);
				break;

			// Default is for *InheritAll mode:
			// - 'getAndInheritAll'
			// - 'getOrInheritAll'
			// - 'inheritAll'
			// - 'get'.
			default:
				$inherited_attr_value = self::inherit_attr_value(
					[
						'attr'            => $attr,
						'baseBreakpoint'  => $base_breakpoint,
						'breakpoint'      => $breakpoint,
						'breakpointNames' => $breakpoint_names,
						'state'           => $state,
						'inheritMode'     => 'all',
					]
				);
				break;
		}

		// Get returned value based on its mode.
		$returned_attr_value = null;

		switch ( $mode ) {
			case 'getAndInheritAll':
			case 'getAndInheritClosest':
				// Combine attrValue and inherited value.
				if ( is_array( $attr_value ) && is_array( $inherited_attr_value ) ) {
					$returned_attr_value = array_replace_recursive( $inherited_attr_value, $attr_value );
				} else {
					$returned_attr_value = null !== $attr_value ? $attr_value : $inherited_attr_value;
				}
				break;
			case 'getOrInheritAll':
			case 'getOrInheritClosest':
				$returned_attr_value = null !== $attr_value ? $attr_value : $inherited_attr_value;
				break;
			case 'inheritAll':
			case 'inheritClosest':
				$returned_attr_value = $inherited_attr_value;
				break;

			// Default stands for mode === 'get'.
			default:
				$returned_attr_value = $attr_value;
				break;
		}

		return null !== $returned_attr_value ? $returned_attr_value : $default_value;
	}

	/**
	 * Retrieve the value of an attribute based on the provided arguments and factoring it enabled breakpoints and base
	 * breakpoint value. This is function is wrapper for `ModuleUtils::get_attr_value()` which automatically pass
	 * `breakpointNames` and `baseBreakpoint` property arguments to simplify its usage.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array        $attr          The attribute to retrieve the value from.
	 *     @type string       $breakpoint    The breakpoint.
	 *     @type string       $state         The state.
	 *     @type string       $mode          Optional. The mode. Default `getOrInheritAll`.
	 *     @type mixed|null   $defaultValue  Optional. The default value. Default `null`.
	 * }
	 *
	 * @since ??
	 *
	 * @return mixed|null The value of the attribute based on the specified arguments, or the default value if no value is found.
	 */
	public static function use_attr_value( array $args ) {
		$updated_args = array_merge(
			$args,
			[
				'baseBreakpoint'  => Breakpoint::get_base_breakpoint_name(),
				'breakpointNames' => Breakpoint::get_enabled_breakpoint_names(),
			]
		);

		return self::get_attr_value( $updated_args );
	}

	/**
	 * Get the inheritance breakpoint for a given breakpoint and state.
	 *
	 * This function retrieves the target inheritance breakpoint for a given breakpoint and state.
	 * It is used to determine the inherited attribute values.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $breakpoint The breakpoint to get the inheritance breakpoint for.
	 *     @type string $state      The state to get the inheritance breakpoint for.
	 *     @type string $baseBreakpoint The base breakpoint.
	 *     @type array  $breakpointNames The breakpoint names.
	 * }
	 *
	 * @return string The inheritance breakpoint for the given breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * // Get the inheritance breakpoint for the 'tablet' breakpoint and 'hover' state
	 * $inherit_breakpoint = ModuleUtils::get_inherit_breakpoint(
	 *   [
	 *     'breakpoint'      => 'tablet',
	 *     'state'           => 'hover',
	 *     'baseBreakpoint'  => 'desktop',
	 *     'breakpointNames' => [ 'desktop', 'tablet', 'phone' ],
	 *   ]
	 * );
	 * echo $inherit_breakpoint;
	 *
	 * // Output: 'desktop'
	 * ```

	 * @example:
	 * ```php
	 * // Get the inheritance breakpoint for the default 'desktop' breakpoint and 'value' state
	 * $inherit_breakpoint = ModuleUtils::get_inherit_breakpoint();
	 * echo $inherit_breakpoint;
	 *
	 * // Output: 'desktop'
	 * ```
	 */
	public static function get_inherit_breakpoint( array $args ): string {
		$breakpoint       = $args['breakpoint'] ?? 'desktop';
		$state            = $args['state'] ?? 'value';
		$base_breakpoint  = $args['baseBreakpoint'] ?? 'desktop';
		$breakpoint_names = $args['breakpointNames'] ?? [
			'desktop',
			'tablet',
			'phone',
		];

		$inherit_breakpoints = self::get_inherit_breakpoint_map(
			[
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		return $inherit_breakpoints[ $breakpoint ][ $state ][0];
	}

	/**
	 * Get the inheritance state for a given breakpoint and state.
	 *
	 * This function retrieves the target inheritance state for a given breakpoint and state.
	 * It is used in conjunction with the ModuleUtils::get_inherit_breakpoint()` function to determine the inherited attribute values.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $breakpoint The breakpoint to get the inheritance state for.
	 *     @type string $state      The state to get the inheritance state for.
	 *     @type string $baseBreakpoint The base breakpoint.
	 *     @type array  $breakpointNames The breakpoint names.
	 * }
	 *
	 * @return string The inheritance state for the given breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * // Get the inheritance state for the 'tablet' breakpoint and 'hover' state
	 * $inherit_state = ModuleUtils::get_inherit_state(
	 *   [
	 *     'breakpoint'      => 'tablet',
	 *     'state'           => 'hover',
	 *     'baseBreakpoint'  => 'desktop',
	 *     'breakpointNames' => [ 'desktop', 'tablet', 'phone' ],
	 *   ]
	 * );
	 * echo $inherit_state;
	 *
	 * // Output: 'value_hover'
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Get the inheritance state for the default 'desktop' breakpoint and 'value' state
	 * $inherit_state = ModuleUtils::get_inherit_state();
	 * echo $inherit_state;
	 *
	 * // Output: 'value'
	 * ```
	 */
	public static function get_inherit_state( array $args ): string {
		$breakpoint       = $args['breakpoint'] ?? 'desktop';
		$state            = $args['state'] ?? 'value';
		$base_breakpoint  = $args['baseBreakpoint'] ?? 'desktop';
		$breakpoint_names = $args['breakpointNames'] ?? [
			'desktop',
			'tablet',
			'phone',
		];

		$inherit_breakpoints = self::get_inherit_breakpoint_map(
			[
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		return $inherit_breakpoints[ $breakpoint ][ $state ][1];
	}

	/**
	 * Recursively trim all values in an array.
	 *
	 * This function calls `ModuleUtils::_array_trim()` to trim the values.
	 *
	 * @since ??
	 *
	 * @param array $input The input array.
	 *
	 * @return array The trimmed array.
	 */
	private static function _array_trim( array $input ): array {
		return array_filter(
			$input,
			function ( $value, $key ) {
				if ( is_array( $value ) ) {
					$value = self::_array_trim( $value );
				}
				// In the background, we have "remove" (trash icon) concept where we can remove value from certain breakpoint without
				// inheriting the value from the larger breakpoint. In this case, we need to allow empty string as a valid value for
				// certain properties.
				$is_allowed_empty_string = '' === $value && in_array(
					$key,
					[
						'url',
						'color',
					],
					true
				);

				return ! empty( $value ) || $is_allowed_empty_string;
			},
			ARRAY_FILTER_USE_BOTH
		);
	}

	/**
	 * Recursively compare two multidimensional arrays to check if they are the same.
	 *
	 * This function trims all values in the arrays recursively using the `ModuleUtils::_array_trim()` method.
	 * It then uses the `ArrayDiffMultidimensional::compare()` to compare the difference between
	 * the two multidimensional arrays.
	 *
	 * This function works like the PHP `array_diff()` function, but with multidimensional arrays.
	 *
	 * @since ??
	 *
	 * @param array $array1 The first array to compare.
	 * @param array $array2 The second array to compare.
	 *
	 * @return bool Returns `true` if the arrays are the same, `false` otherwise.
	 */
	private static function _is_same( array $array1, array $array2 ): bool {
		$array1 = self::_array_trim( $array1 );
		$array2 = self::_array_trim( $array2 );

		$diff = ArrayDiffMultidimensional::compare( $array1, $array2 );

		return empty( $diff );
	}

	/**
	 * Check if the background attribute setting is enabled.
	 *
	 * This function checks if the `enabled` attribute is set in the given attribute group.
	 *
	 * If the `enabled` attribute is not present and strict comparison is enabled, it returns `false`.
	 * If the `enabled` attribute is not present and strict comparison is not enabled, it returns true.
	 * If the `enabled` attribute is present, it returns `true` if it is set to `'on'`, and `false` otherwise.
	 *
	 * @since ??
	 *
	 * @param array $attr_group The attribute group to check.
	 * @param bool  $strict     Whether to make a strict comparison. Default `false`.
	 *
	 * @return bool Whether the background attribute setting is enabled.
	 *
	 * @example:
	 * ```php
	 *   // Example 1: Check if background is enabled without strict comparison.
	 *   $attr_group = [
	 *       'enabled' => 'on',
	 *       // other attributes...
	 *   ];
	 *   $result = ModuleUtils::_is_background_attr_enabled( $attr_group );
	 *   // Output: true
	 *
	 *   // Example 2: Check if background is enabled with strict comparison.
	 *   $attr_group = [
	 *       'enabled' => 'on',
	 *       // other attributes...
	 *   ];
	 *   $result = ModuleUtils::_is_background_attr_enabled( $attr_group, true) ;
	 *   // Output: false
	 *
	 *   // Example 3: Check if background is disabled without strict comparison.
	 *   $attr_group = [
	 *       'enabled' => 'off',
	 *       // other attributes...
	 *   ];
	 *   $result = ModuleUtils::_is_background_attr_enabled( $attr_group );
	 *   // Output: false
	 * ```
	 */
	private static function _is_background_attr_enabled( array $attr_group, bool $strict = false ): bool {
		$has_enabled = isset( $attr_group['enabled'] );

		// If we're making a strict comparison, we'll presume this is disabled if we
		// don't have an `enabled` attribute.
		if ( ! $has_enabled && $strict ) {
			return false;
		}

		// If we don't have an `enabled` attribute at this point, we'll presume that
		// the setting is enabled.
		if ( ! $has_enabled ) {
			return true;
		}

		// If we have an `enabled` attribute, we'll return whether it's set to `on`.
		return 'on' === $attr_group['enabled'];
	}

	/**
	 * Inherit attribute values for background.
	 *
	 * This function takes an array of attribute values with inherited values and a breakpoint and state
	 * to determine the appropriate inheritance. It then merges the attribute values from the specified
	 * breakpoint and state with their parent values, accounting for enabled or disabled attributes.
	 *
	 * @since ??
	 *
	 * @param array  $attr_value_with_inherited The attribute values with inherited values. This is a multi-dimensional array
	 *                                          with breakpoints and states as keys.
	 * @param string $breakpoint                The breakpoint to get the inheritance breakpoint for. One of `desktop`, `tablet`, `phone`.
	 * @param string $state                     The state to get the inheritance breakpoint for.
	 *                                          One of `value`, `hover`, `tablet_value`, `tablet_hover`, `phone_value`, `phone_hover`.
	 *
	 * @return array The attribute values with inherited values.
	 *
	 * @example:
	 * ```php
	 *     $attr_value_with_inherited = [
	 *         'desktop' => [
	 *             'value' => [
	 *                 'color' => '#000',
	 *                 'gradient' => [
	 *                     'enabled' => 'on',
	 *                     'stops' => [
	 *                         'stop1' => '#fff',
	 *                         'stop2' => '#000'
	 *                     ]
	 *                 ],
	 *                 'image' => [
	 *                     'enabled' => 'off',
	 *                     'source' => 'image.jpg'
	 *                 ]
	 *             ]
	 *         ]
	 *     ];
	 *     $breakpoint = 'desktop';
	 *     $state = 'value';
	 *
	 *     $result = ModuleUtils_inherit_background_values( $attr_value_with_inherited, $breakpoint, $state );
	 *
	 *     // $result is:
	 *     // [
	 *     //     'desktop' => [
	 *     //         'value' => [
	 *     //             'color' => '#000',
	 *     //             'gradient' => [
	 *     //                 'enabled' => 'on',
	 *     //                 'stops' => [
	 *     //                     'stop1' => '#fff',
	 *     //                     'stop2' => '#000'
	 *     //                 ]
	 *     //             ],
	 *     //             'image' => [
	 *     //                 'enabled' => 'off',
	 *     //                 'source' => 'image.jpg'
	 *     //             ]
	 *     //         ]
	 *     //     ]
	 *     // ]
	 * ```
	 */
	private static function _inherit_background_values( array $attr_value_with_inherited, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint    = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names   = Breakpoint::get_enabled_breakpoint_names();
		$inherit_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$inherit_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		$attr_values        = $attr_value_with_inherited[ $breakpoint ][ $state ] ?? [];
		$attr_parent_values = $attr_value_with_inherited[ $inherit_breakpoint ][ $inherit_state ] ?? [];

		$attr_value_with_inherited[ $breakpoint ][ $state ] = self::_array_trim(
			[
				'color'    => $attr_values['color'] ?? $attr_parent_values['color'] ?? null,
				'gradient' => self::_array_trim(
					self::_is_background_attr_enabled( $attr_values['gradient'] ?? [] )
						? array_merge(
							[],
							$attr_parent_values['gradient'] ?? [],
							$attr_values['gradient'] ?? [],
							[
								'stops' => $attr_values['gradient']['stops'] ?? $attr_parent_values['gradient']['stops'] ?? [],
							]
						)
						: [
							'enabled' => $attr_values['gradient']['enabled'] ?? 'off',
						]
				),
				'image'    => self::_array_trim(
					self::_is_background_attr_enabled( $attr_values['image'] ?? [] )
					? array_merge(
						[],
						$attr_parent_values['image'] ?? [],
						$attr_values['image'] ?? []
					)
					: [
						'enabled' => $attr_values['image']['enabled'] ?? 'off',
					]
				),
				'mask'     => self::_array_trim(
					is_array( $attr_values['mask'] ?? [] ) && self::_is_background_attr_enabled( $attr_values['mask'] ?? [] )
						? array_merge( [], $attr_parent_values['mask'] ?? [], $attr_values['mask'] ?? [] )
						: [
							'enabled' => $attr_values['mask']['enabled'],
						]
				),
				'pattern'  => self::_array_trim(
					is_array( $attr_values['pattern'] ?? [] ) && self::_is_background_attr_enabled( $attr_values['pattern'] ?? [] )
						? array_merge( [], $attr_parent_values['pattern'] ?? [], $attr_values['pattern'] ?? [] )
						: [
							'enabled' => $attr_values['pattern']['enabled'],
						]
				),
			]
		);

		return $attr_value_with_inherited;
	}

	/**
	 * Get attribute values with inherited values.
	 *
	 * This function compares each breakpoint and state using the `inheritBreakpoints` object
	 * and, starting with `phone.sticky` and moving to `desktop.value`, deletes any object
	 * that completely matches its parent breakpoint and state. It will always keep the
	 * `desktop.value` object if it exists. The function retrieves the default `attrValue` on
	 * the current breakpoint and state.
	 *
	 * @since ??
	 *
	 * @param array  $attr_to_be_returned The attribute values with inherited values.
	 * @param string $breakpoint          The breakpoint to get the inheritance breakpoint for. One of `desktop`, `tablet`, `phone`.
	 * @param string $state               The state to get the inheritance breakpoint for.
	 *                                    One of `value`, `hover`, `tablet_value`, `tablet_hover`, `phone_value`, `phone_hover`.
	 *
	 * @return array Cleaned attribute values.
	 *
	 * @example:
	 * ```php
	 * $attr_to_be_returned = [
	 *     'desktop' => [
	 *         'value' => [
	 *             'color'    => '#ffffff',
	 *             'mask'     => [],
	 *             'pattern'  => [],
	 *             'image'    => [],
	 *             'gradient' => [],
	 *         ],
	 *     ],
	 *     'tablet'  => [
	 *         'value' => [
	 *             'color'    => '#000000',
	 *             'mask'     => [],
	 *             'pattern'  => [],
	 *             'image'    => [],
	 *             'gradient' => [],
	 *         ],
	 *     ],
	 *     'phone'   => [
	 *         'value' => [
	 *             'color'    => '#ff0000',
	 *             'mask'     => [],
	 *             'pattern'  => [],
	 *         'image'    => [],
	 *         'gradient' => [],
	 *     ],
	 *    ],
	 * ];
	 * $breakpoint = 'desktop';
	 * $state = 'value';
	 *
	 * $result = ModuleUtils_return_background_values( $attr_to_be_returned, $breakpoint, $state );
	 * // $result is:
	 * // [
	 * //     'desktop' => [
	 * //         'value' => [
	 * //             'color' => '#ffffff',
	 * //         ],
	 * //     ],
	 * //     'tablet' => [
	 * //         'value' => [
	 * //             'color' => '#000000',
	 * //         ],
	 * //     ],
	 * //     'phone' => [
	 * //         'value' => [
	 * //             'color' => '#ff0000',
	 * //         ],
	 * //     ],
	 * // ]
	 * ```
	 */
	private static function _return_background_values( array $attr_to_be_returned, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint   = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names  = Breakpoint::get_enabled_breakpoint_names();
		$parent_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$parent_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$is_desktop_value  = 'desktop' === $breakpoint && 'value' === $state;

		// Background Color.
		$parent_color           = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['color'] ?? null;
		$current_color          = $attr_to_be_returned[ $breakpoint ][ $state ]['color'] ?? null;
		$is_colors_match        = $current_color === $parent_color;
		$is_current_color_empty = is_null( $current_color );

		/*
		 * If the current color is an empty string, then it's intentionally blank
		 * and should not inherit the parent's value.
		 */
		$color_or_initial = ! $is_desktop_value && '' === $current_color ? 'initial' : $current_color;

		// Add inherited background color values if necessary.
		if ( ! $is_current_color_empty && ( $is_desktop_value || ! $is_colors_match ) ) {
			$attr_to_be_returned[ $breakpoint ][ $state ]['color'] = $color_or_initial;
		} else {
			unset( $attr_to_be_returned[ $breakpoint ][ $state ]['color'] );
		}

		// Background Mask.
		$parent_mask           = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['mask'] ?? [];
		$current_mask          = $attr_to_be_returned[ $breakpoint ][ $state ]['mask'] ?? [];
		$is_masks_match        = self::_is_same( $current_mask, $parent_mask );
		$is_current_mask_empty = empty( self::_array_trim( $current_mask ) );

		// Add inherited background mask values if necessary.
		if ( ! $is_current_mask_empty && ( $is_desktop_value || ! $is_masks_match ) ) {
			$attr_to_be_returned[ $breakpoint ][ $state ]['mask'] = $current_mask;
		} else {
			unset( $attr_to_be_returned[ $breakpoint ][ $state ]['mask'] );
		}

		// Background Pattern.
		$parent_pattern           = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['pattern'] ?? [];
		$current_pattern          = $attr_to_be_returned[ $breakpoint ][ $state ]['pattern'] ?? [];
		$is_patterns_match        = self::_is_same( $current_pattern, $parent_pattern );
		$is_current_pattern_empty = empty( self::_array_trim( $current_pattern ) );

		// Add inherited background pattern values if necessary.
		if ( ! $is_current_pattern_empty && ( $is_desktop_value || ! $is_patterns_match ) ) {
			$attr_to_be_returned[ $breakpoint ][ $state ]['pattern'] = $current_pattern;
		} else {
			unset( $attr_to_be_returned[ $breakpoint ][ $state ]['pattern'] );
		}

		// Background Image and Gradient.
		$parent_gradient           = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['gradient'] ?? [];
		$current_gradient          = $attr_to_be_returned[ $breakpoint ][ $state ]['gradient'] ?? [];
		$is_gradients_match        = self::_is_same( $current_gradient, $parent_gradient );
		$is_current_gradient_empty = empty( self::_array_trim( $current_gradient ) );

		$parent_image           = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['image'] ?? [];
		$current_image          = $attr_to_be_returned[ $breakpoint ][ $state ]['image'] ?? [];
		$is_images_match        = self::_is_same( $current_image, $parent_image );
		$is_current_image_empty = empty( self::_array_trim( $current_image ) );

		$is_image_and_gradient_empty = $is_current_image_empty && $is_current_gradient_empty;

		// Add background image and gradient values together.
		if ( ! $is_image_and_gradient_empty && ( $is_desktop_value || ( ! $is_images_match || ! $is_gradients_match ) ) ) {
			if ( ! $is_current_gradient_empty ) {
				$attr_to_be_returned[ $breakpoint ][ $state ]['gradient'] = $current_gradient;
			}
			if ( ! $is_current_image_empty ) {
				$attr_to_be_returned[ $breakpoint ][ $state ]['image'] = $current_image;
			}
		} elseif ( $is_image_and_gradient_empty ) {
			// If both image and gradient are empty, inherit from parent one at a time.
			if ( ! $is_gradients_match && ! empty( $parent_gradient ) ) {
				$attr_to_be_returned[ $breakpoint ][ $state ]['gradient'] = $parent_gradient;
			}
			if ( ! $is_images_match && ! empty( $parent_image ) ) {
				$attr_to_be_returned[ $breakpoint ][ $state ]['image'] = $parent_image;
			}
		}

		if ( ! $is_desktop_value ) {
			$is_images_match    = false;
			$is_gradients_match = false;

			if (
				self::_is_same(
					$attr_to_be_returned[ $breakpoint ][ $state ]['image'] ?? [],
					$attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['image'] ?? []
				)
			) {
				$is_images_match = true;
			}

			if (
				self::_is_same(
					$attr_to_be_returned[ $breakpoint ][ $state ]['gradient'] ?? [],
					$attr_to_be_returned[ $parent_breakpoint ][ $parent_state ]['gradient'] ?? []
				)
			) {
				$is_gradients_match = true;
			}

			if ( $is_images_match && $is_gradients_match ) {
				unset( $attr_to_be_returned[ $breakpoint ][ $state ]['image'] );
				unset( $attr_to_be_returned[ $breakpoint ][ $state ]['gradient'] );
			}

			if ( isset( $attr_to_be_returned[ $breakpoint ][ $state ] ) ) {
				// If the entire background style is empty, remove it.
				if ( empty( self::_array_trim( $attr_to_be_returned[ $breakpoint ][ $state ] ) ) ) {
					unset( $attr_to_be_returned[ $breakpoint ][ $state ] );
				}

				// If the entire breakpoint style matches the parent, remove it.
				if (
					self::_is_same(
						$attr_to_be_returned[ $breakpoint ][ $state ] ?? [],
						$attr_to_be_returned[ $parent_breakpoint ][ $parent_state ] ?? []
					)
				) {
					unset( $attr_to_be_returned[ $breakpoint ][ $state ] );
				}
			}
		}

		if ( ! $is_desktop_value && isset( $attr_to_be_returned[ $breakpoint ][ $state ] ) ) {
			// If the entire background style is empty, remove it.
			if ( empty( self::_array_trim( $attr_to_be_returned[ $breakpoint ][ $state ] ) ) ) {
				unset( $attr_to_be_returned[ $breakpoint ][ $state ] );
			}

			// If the entire breakpoint style matches the parent, remove it.
			if (
				self::_is_same(
					$attr_to_be_returned[ $breakpoint ][ $state ] ?? [],
					$attr_to_be_returned[ $parent_breakpoint ][ $parent_state ] ?? []
				)
			) {
				unset( $attr_to_be_returned[ $breakpoint ][ $state ] );
			}
		}

		return $attr_to_be_returned;
	}

	/**
	 * Get and inherit background attributes for all breakpoints and states.
	 *
	 * Iterates through each breakpoint and state to inherit values from the previous
	 * breakpoint and state if they are not set. Also removes values that are the
	 * same as the inherited value.
	 *
	 * @since ???
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attr An array of module attribute data.
	 * }
	 *
	 * @return array An array of background attributes with inherited values.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attr' => [
	 *         'desktop' => [
	 *             'value' => 'red',
	 *             'hover' => 'blue',
	 *         ],
	 *         'tablet' => [
	 *             'value' => null,
	 *             'hover' => 'green',
	 *         ],
	 *     ],
	 * ];
	 *
	 * $result = ModuleUtils::get_and_inherit_background_attr( $args );
	 * ```
	 *
	 * @output:
	 * ```php
	 *   [
	 *       'desktop' => [
	 *           'value' => 'red',
	 *           'hover' => 'blue',
	 *       ],
	 *       'tablet' => [
	 *           'value' => 'red',
	 *           'hover' => 'green',
	 *       ],
	 *   ]
	 * ```
	 */
	public static function get_and_inherit_background_attr( array $args ): array {
		$initial_style_attr = $args['attr'] ?? [];

		// Pre-populate with the passed style attributes.
		$attr_value_with_inherited = $initial_style_attr;

		// If we have a background style, we need to check if it contains
		// multiple breakpoints and/or states. If it does, we need to step
		// through each breakpoint and state and inherit values from the
		// previous breakpoint and state if they are not set.
		if ( ! empty( $attr_value_with_inherited ) ) {
			// TODO feat(D5, Responsive Views): Replace this with a loop once we have a sort/priority system for breakpoints.

			// Desktop attributes first, if they exist.
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'desktop', 'value' );
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'desktop', 'hover' );
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'desktop', 'sticky' );

			// Tablet attributes second, if they exist.
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'tablet', 'value' );
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'tablet', 'hover' );
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'tablet', 'sticky' );

			// Phone attributes last, if they exist.
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'phone', 'value' );
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'phone', 'hover' );
			$attr_value_with_inherited = self::_inherit_background_values( $attr_value_with_inherited, 'phone', 'sticky' );
		}

		// Pre-populate with the passed style attributes.
		$attr_to_be_returned = $attr_value_with_inherited;

		// If we have a background style, we need to check if any values is the
		// same as the inherited breakpoint/state value. If it is, we can delete
		// it from the inheritor.
		if ( ! empty( $attr_to_be_returned ) ) {
			// TODO feat(D5, Responsive Views): Replace this with a loop once we have a sort/priority system for breakpoints.

			// Phone attributes first, if they exist.
			if ( array_key_exists( 'phone', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'phone', 'sticky' );
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'phone', 'hover' );
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'phone', 'value' );

				// Delete the phone breakpoint if it is empty.
				if ( empty( self::_array_trim( $attr_to_be_returned['phone'] ) ) ) {
					unset( $attr_to_be_returned['phone'] );
				}
			}

			// Tablet attributes second, if they exist.
			if ( array_key_exists( 'tablet', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'tablet', 'sticky' );
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'tablet', 'hover' );
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'tablet', 'value' );

				// Delete the tablet breakpoint if it is empty.
				if ( empty( self::_array_trim( $attr_to_be_returned['tablet'] ) ) ) {
					unset( $attr_to_be_returned['tablet'] );
				}
			}

			// Desktop attributes last, if they exist.
			if ( array_key_exists( 'desktop', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'desktop', 'sticky' );
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'desktop', 'hover' );
				$attr_to_be_returned = self::_return_background_values( $attr_to_be_returned, 'desktop', 'value' );

				// Delete the desktop breakpoint if it is empty.
				if ( empty( self::_array_trim( $attr_to_be_returned['desktop'] ) ) ) {
					unset( $attr_to_be_returned['desktop'] );
				}
			}
		}

		return $attr_to_be_returned;
	}

	/**
	 * Inherit icon style attribute values for a given breakpoint and state.
	 *
	 * This function takes an array of attribute values with inherited values and updates them
	 * for a specific breakpoint and state.
	 *
	 * If the breakpoint or state is not set, it will be defined.
	 * If the state is not set, it will be inherited completely from the previous breakpoint.
	 * Finally, it will merge the inherited printed style attribute with the attribute values and return the updated array.
	 *
	 * @since ??
	 *
	 * @param array  $attr_value_with_inherited The attribute values with inherited values.
	 * @param string $breakpoint                The breakpoint to get the inheritance breakpoint for.
	 * @param string $state                     The state to get the inheritance breakpoint for.
	 *
	 * @return array The updated attribute values with inherited values.
	 *
	 * @example:
	 * ```php
	 * $attr_value_with_inherited = [
	 *    'desktop' => [
	 *        'hover' => [
	 *            'useSize' => 'on',
	 *            'size' => '12px',
	 *        ],
	 *    ],
	 *    'tablet' => [
	 *        'value' => [
	 *            'useSize' => 'off',
	 *            'size' => '10px',
	 *        ],
	 *        'hover' => [
	 *            'useSize' => 'on',
	 *            'size' => '20px',
	 *        ],
	 *        'sticky' => [
	 *            'useSize' => 'on',
	 *            'size' => '25px',
	 *        ],
	 *    ],
	 * ];
	 *
	 * $breakpoint = 'tablet';
	 * $state = 'value';
	 *
	 * $updated_attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, $breakpoint, $state );
	 * ```
	 */
	private static function _inherit_icon_style_values( array $attr_value_with_inherited, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint    = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names   = Breakpoint::get_enabled_breakpoint_names();
		$inherit_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$inherit_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		$attr_values        = $attr_value_with_inherited[ $breakpoint ][ $state ] ?? [];
		$attr_parent_values = $attr_value_with_inherited[ $inherit_breakpoint ][ $inherit_state ] ?? [];

		$attr_value_with_inherited[ $breakpoint ][ $state ] = self::_array_trim(
			[
				'color'   => $attr_values['color'] ?? $attr_parent_values['color'] ?? null,
				'useSize' => $attr_values['useSize'] ?? $attr_parent_values['useSize'] ?? '',
				'size'    => $attr_values['size'] ?? $attr_parent_values['size'] ?? '',
				'weight'  => $attr_values['weight'] ?? $attr_parent_values['weight'] ?? '',
				'unicode' => $attr_values['unicode'] ?? $attr_parent_values['unicode'] ?? '',
				'type'    => $attr_values['type'] ?? $attr_parent_values['type'] ?? '',
				'show'    => $attr_values['show'] ?? $attr_parent_values['show'] ?? '',
			]
		);

		return $attr_value_with_inherited;
	}

	/**
	 * Return attribute values with inherited icon style CSS declarations.
	 *
	 * This function takes an array of attribute values with inherited values and calculates the final
	 * icon style CSS declarations for a given breakpoint and state.
	 * It checks if the attribute values match the inherited values and removes any redundant
	 * entries (i.e the values are the same as the parent breakpoint and state).
	 * It also filters out empty attribute values.
	 *
	 * @since ??
	 *
	 * @param array  $attr_to_be_returned The attribute values with inherited values.
	 * @param string $breakpoint          The breakpoint to calculate the inheritance for.
	 * @param string $state               The state to calculate the inheritance for.
	 *
	 * @return array The attribute values after applying inheritance and filtering.
	 *
	 * @example:
	 * ```php
	 * // Single usage example:
	 * $attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'desktop', 'hover' );
	 *
	 * // Multiple usage example:
	 * if ( array_key_exists( 'phone', $attr_to_be_returned ) ) {
	 *     $attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'phone', 'sticky' );
	 *     $attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'phone', 'hover' );
	 *     $attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'phone', 'value' );
	 * }
	 * ```
	 */
	private static function _return_icon_style_values( array $attr_to_be_returned, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint   = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names  = Breakpoint::get_enabled_breakpoint_names();
		$parent_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$parent_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		$is_desktop_value   = 'desktop' === $breakpoint && 'value' === $state;
		$current_icon_style = $attr_to_be_returned[ $breakpoint ][ $state ] ?? [];
		$parent_icon_style  = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ] ?? [];
		$icon_styles_match  = self::_is_same( $current_icon_style, $parent_icon_style );
		$is_current_empty   = ! $current_icon_style;

		// Update the attr object to add inherited icon-style values if toJSON matches.
		if ( $is_desktop_value && ! $is_current_empty ) {
			$attr_to_be_returned[ $breakpoint ][ $state ] = $current_icon_style;
		} elseif ( ! $icon_styles_match && ! $is_current_empty ) {
			$attr_to_be_returned[ $breakpoint ][ $state ] = $current_icon_style;
		} else {
			unset( $attr_to_be_returned[ $breakpoint ][ $state ] );
		}

		return $attr_to_be_returned;
	}

	/**
	 * Get and inherit icon style CSS declarations with inheritance for all breakpoints and states.
	 *
	 * This function takes an array of attribute values with inherited values and updates them
	 * for a specific breakpoint and state. If the breakpoint or state is not set, it will be defined.
	 * If the state is not set, it will be inherited completely from the previous breakpoint.
	 * Finally, it will merge the inherited printed style attribute with the attribute values and return the updated array.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attr The attribute values with inherited values.
	 * }
	 *
	 * @return array The attribute values with updated inheritance.
	 *
	 * @example:
	 * ```php
	 *   $args = [
	 *     'attr' => [
	 *       'desktop' => [
	 *         'value' => [
	 *           'useSize' => 'on',
	 *           'size' => '2px',
	 *         ],
	 *         'hover' => [
	 *           'useSize' => 'on',
	 *         ],
	 *         'sticky' => [
	 *           'useSize' => 'on',
	 *           'size' => '8px',
	 *         ],
	 *       ],
	 *       'tablet' => [
	 *         'value' => [
	 *           'useSize' => 'on',
	 *           'size' => '22px',
	 *         ],
	 *         'hover' => [
	 *           'size' => '35px',
	 *         ],
	 *         'sticky' => [
	 *           'size' => '2px',
	 *         ],
	 *       ],
	 *       'phone' => [
	 *         'value' => [
	 *           'useSize' => 'on',
	 *           'size' => '12px',
	 *         ],
	 *         'sticky' => [
	 *           'useSize' => 'on',
	 *           'size' => '8px',
	 *         ],
	 *       ]
	 *     ]
	 *   ];
	 *
	 *   $result = ModuleUtils::get_and_inherit_icon_style_attr( $args );
	 * ```

	 * @example:
	 * ```php
	 *   $args = [
	 *     'attr' => [
	 *       'desktop' => [
	 *         'value' => [
	 *           'useSize' => 'on',
	 *           'size' => '2px',
	 *         ],
	 *         'hover' => [
	 *           'useSize' => 'on',
	 *           'size' => '2px',
	 *         ],
	 *         'sticky' => [
	 *           'useSize' => 'on',
	 *           'size' => '8px',
	 *         ],
	 *       ],
	 *       'tablet' => [
	 *         'value' => [
	 *           'useSize' => 'on',
	 *           'size' => '22px',
	 *         ],
	 *         'hover' => [
	 *           'useSize' => 'on',
	 *           'size' => '35px',
	 *         ],
	 *         'sticky' => [
	 *           'useSize' => 'on',
	 *           'size' => '2px',
	 *         ],
	 *       ],
	 *       'phone' => [
	 *         'value' => [
	 *           'useSize' => 'on',
	 *           'size' => '12px',
	 *         ],
	 *         'hover' => [
	 *           'useSize' => 'on',
	 *           'size' => '12px',
	 *         ],
	 *         'sticky' => [
	 *           'useSize' => 'on',
	 *           'size' => '8px',
	 *         ],
	 *       ]
	 *     ]
	 *   ];
	 *
	 *   $result = ModuleUtils::get_and_inherit_icon_style_attr( $args );
	 * ```
	 */
	public static function get_and_inherit_icon_style_attr( array $args ): array {
		$initial_style_attr = $args['attr'] ?? [];

		// Pre-populate with the passed style attributes.
		$attr_value_with_inherited = $initial_style_attr;

		// If we have a icon-style style, we need to check if it contains
		// multiple breakpoints and/or states. If it does, we need to step
		// through each breakpoint and state and inherit values from the
		// previous breakpoint and state if they are not set.
		if ( ! empty( $attr_value_with_inherited ) ) {
			// TODO feat(D5, Responsive Views): Replace this with a loop once we have a sort/priority system for breakpoints.

			// Desktop attributes first, if they exist.
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'desktop', 'value' );
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'desktop', 'hover' );
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'desktop', 'sticky' );

			// Tablet attributes second, if they exist.
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'tablet', 'value' );
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'tablet', 'hover' );
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'tablet', 'sticky' );

			// Phone attributes last, if they exist.
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'phone', 'value' );
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'phone', 'hover' );
			$attr_value_with_inherited = self::_inherit_icon_style_values( $attr_value_with_inherited, 'phone', 'sticky' );
		}

		// Pre-populate with the passed style attributes.
		$attr_to_be_returned = $attr_value_with_inherited;

		// If we have a icon-style style, we need to check if any values is the
		// same as the inherited breakpoint/state value. If it is, we can delete
		// it from the inheritor.
		if ( ! empty( $attr_to_be_returned ) ) {
			// TODO feat(D5, Responsive Views): Replace this with a loop once we have a sort/priority system for breakpoints.

			// Phone attributes first, if they exist.
			if ( array_key_exists( 'phone', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'phone', 'sticky' );
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'phone', 'hover' );
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'phone', 'value' );

				// Delete the phone breakpoint if it is empty.
				if ( empty( self::_array_trim( $attr_to_be_returned['phone'] ) ) ) {
					unset( $attr_to_be_returned['phone'] );
				}
			}

			// Tablet attributes second, if they exist.
			if ( array_key_exists( 'tablet', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'tablet', 'sticky' );
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'tablet', 'hover' );
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'tablet', 'value' );

				// Delete the tablet breakpoint if it is empty.
				if ( empty( self::_array_trim( $attr_to_be_returned['tablet'] ) ) ) {
					unset( $attr_to_be_returned['tablet'] );
				}
			}

			// Desktop attributes last, if they exist.
			if ( array_key_exists( 'desktop', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'desktop', 'sticky' );
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'desktop', 'hover' );
				$attr_to_be_returned = self::_return_icon_style_values( $attr_to_be_returned, 'desktop', 'value' );

				// Delete the desktop breakpoint if it is empty.
				if ( empty( self::_array_trim( $attr_to_be_returned['desktop'] ) ) ) {
					unset( $attr_to_be_returned['desktop'] );
				}
			}
		}

		return $attr_to_be_returned;
	}

	/**
	 * Inherit text shadow attribute values for a given breakpoint and state.
	 *
	 * This function takes an array of attribute values with inherited values and updates them
	 * for a specific breakpoint and state.
	 *
	 * If the breakpoint or state is not set, it will be defined.
	 * If the state is not set, it will be inherited completely from the previous breakpoint.
	 * Finally, it will merge the inherited printed style attribute with the attribute values and return the updated array.
	 *
	 * @since ??
	 *
	 * @param array  $attr_value_with_inherited The attribute values with inherited values.
	 * @param string $breakpoint                The breakpoint to get the inheritance breakpoint for.
	 * @param string $state                     The state to get the inheritance breakpoint for.
	 *
	 * @return array The updated attribute values with inherited values.
	 *
	 * @example:
	 * ```php
	 * $attr_value_with_inherited = [
	 *    'desktop' => [
	 *        'hover' => [
	 *            'color' => '#000000',
	 *            'text-shadow' => '2px 2px 2px #000000',
	 *        ],
	 *    ],
	 *    'tablet' => [
	 *        'value' => [
	 *            'color' => '#ffffff',
	 *            'text-shadow' => 'none',
	 *        ],
	 *        'hover' => [
	 *            'color' => '#ff0000',
	 *            'text-shadow' => 'none',
	 *        ],
	 *        'sticky' => [
	 *            'color' => '#00ff00',
	 *            'text-shadow' => 'none',
	 *        ],
	 *    ],
	 * ];
	 *
	 * $breakpoint = 'tablet';
	 * $state = 'value';
	 *
	 * $updated_attr_value_with_inherited = ModuleUtils::_inherit_text_shadow_values( $attr_value_with_inherited, $breakpoint, $state );
	 * ```
	 */
	private static function _inherit_text_shadow_values( array $attr_value_with_inherited, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint    = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names   = Breakpoint::get_enabled_breakpoint_names();
		$inherit_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$inherit_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		// If the breakpoint is not set, we need to define it.
		if ( ! isset( $attr_value_with_inherited[ $breakpoint ] ) ) {
			$attr_value_with_inherited[ $breakpoint ] = [];
		}

		// If the state is not set, we need to define it.
		if ( ! isset( $attr_value_with_inherited[ $breakpoint ][ $state ] ) ) {
			$attr_value_with_inherited[ $breakpoint ][ $state ] = [];
		}

		// If the state is not set, we need to inherit it completely from the previous breakpoint.
		if ( isset( $attr_value_with_inherited[ $inherit_breakpoint ][ $inherit_state ] ) && ! isset( $attr_value_with_inherited[ $breakpoint ][ $state ] ) ) {
			$attr_value_with_inherited[ $breakpoint ][ $state ] = $attr_value_with_inherited[ $inherit_breakpoint ][ $inherit_state ];
		}

		// Ensure both previous and current state values are arrays before merging.
		$inherited_values     = $attr_value_with_inherited[ $inherit_breakpoint ][ $inherit_state ] ?? [];
		$current_state_values = $attr_value_with_inherited[ $breakpoint ][ $state ] ?? [];

		// Merge the inherited printed style attribute with the attribute values.
		$attr_value_with_inherited[ $breakpoint ][ $state ] = array_merge( $inherited_values, $current_state_values );

		return $attr_value_with_inherited;
	}

	/**
	 * Return attribute values with inherited text shadow CSS declarations.
	 *
	 * This function takes an array of attribute values with inherited values and calculates the final
	 * text shadow CSS declarations for a given breakpoint and state.
	 * It checks if the attribute values match the inherited values and removes any redundant
	 * entries (i.e the values are the same as the parent breakpoint and state).
	 * It also filters out empty attribute values.
	 *
	 * @since ??
	 *
	 * @param array  $attr_to_be_returned The attribute values with inherited values.
	 * @param string $breakpoint          The breakpoint to calculate the inheritance for.
	 * @param string $state               The state to calculate the inheritance for.
	 *
	 * @return array The attribute values after applying inheritance and filtering.
	 *
	 * @example:
	 * ```php
	 * // Single usage example:
	 * $attr_to_be_returned = ModuleUtils::_return_text_shadow_values( $attr_to_be_returned, 'desktop', 'hover' );
	 *
	 * // Multiple usage example:
	 * if ( array_key_exists( 'phone', $attr_to_be_returned ) ) {
	 *     $attr_to_be_returned = ModuleUtils::_return_text_shadow_values( $attr_to_be_returned, 'phone', 'sticky' );
	 *     $attr_to_be_returned = ModuleUtils::_return_text_shadow_values( $attr_to_be_returned, 'phone', 'hover' );
	 *     $attr_to_be_returned = ModuleUtils::_return_text_shadow_values( $attr_to_be_returned, 'phone', 'value' );
	 * }
	 * ```
	 */
	private static function _return_text_shadow_values( array $attr_to_be_returned, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint    = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names   = Breakpoint::get_enabled_breakpoint_names();
		$inherit_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$inherit_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		// If the inherited breakpoint is not set, return the attribute values.
		if ( ! isset( $attr_to_be_returned[ $inherit_breakpoint ][ $inherit_state ] ) ) {
			return $attr_to_be_returned;
		}

		// If the attribute value matches the inherited value, we can delete it.
		if ( $attr_to_be_returned[ $breakpoint ][ $state ] === $attr_to_be_returned[ $inherit_breakpoint ][ $inherit_state ] ) {
			unset( $attr_to_be_returned[ $breakpoint ][ $state ] );

			return $attr_to_be_returned;
		}

		// If the attribute value matches the inherited value, we can delete it.
		if ( empty( $attr_to_be_returned[ $breakpoint ][ $state ] ) ) {
			$attr_to_be_returned[ $breakpoint ][ $state ] = array_filter( $attr_to_be_returned[ $breakpoint ][ $state ], 'strlen' );
		}

		return $attr_to_be_returned;
	}

	/**
	 * Get and inherit text shadow CSS declarations with inheritance for all breakpoints and states.
	 *
	 * This function takes an array of attribute values with inherited values and updates them
	 * for a specific breakpoint and state. If the breakpoint or state is not set, it will be defined.
	 * If the state is not set, it will be inherited completely from the previous breakpoint.
	 * Finally, it will merge the inherited printed style attribute with the attribute values and return the updated array.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attr The attribute values with inherited values.
	 * }
	 *
	 * @return array The attribute values with updated inheritance.
	 *
	 * @example:
	 * ```php
	 *   $args = [
	 *     'attr' => [
	 *       'desktop' => [
	 *         'value' => 'text-shadow: 2px 2px 2px black;',
	 *         'hover' => 'text-shadow: 4px 4px 4px black;',
	 *         'sticky' => 'text-shadow: 8px 8px 8px black;'
	 *       ],
	 *       'tablet' => [
	 *         'value' => 'text-shadow: 3px 3px 3px black;',
	 *         'hover' => 'text-shadow: 5px 5px 5px black;',
	 *         'sticky' => 'text-shadow: 9px 9px 9px black;'
	 *       ],
	 *       'phone' => [
	 *         'value' => 'text-shadow: 6px 6px 6px black;',
	 *         'hover' => 'text-shadow: 7px 7px 7px black;',
	 *         'sticky' => 'text-shadow: 10px 10px 10px black;'
	 *       ]
	 *     ]
	 *   ];
	 *
	 *   $result = ModuleUtils::get_and_inherit_text_shadow_attr( $args );
	 * ```

	 * @example:
	 * ```php
	 *   $args = [
	 *     'attr' => [
	 *       'desktop' => [
	 *         'value' => 'text-shadow: 2px 2px 2px black;',
	 *         'hover' => 'text-shadow: 4px 4px 4px black;',
	 *         'sticky' => 'text-shadow: 8px 8px 8px black;'
	 *       ]
	 *     ]
	 *   ];
	 *
	 *   $result = ModuleUtils::get_and_inherit_text_shadow_attr( $args );
	 * ```
	 */
	public static function get_and_inherit_text_shadow_attr( array $args ): array {
		$initial_style_attr = $args['attr'] ?? [];

		// Pre-populate with the passed style attributes.
		$attr_value_with_inherited = $initial_style_attr;

		// If we have a text-shadow style, we need to check if it contains
		// multiple breakpoints and/or states. If it does, we need to step
		// through each breakpoint and state and inherit values from the
		// previous breakpoint and state if they are not set.
		if ( ! empty( $attr_value_with_inherited ) ) {
			// TODO feat(D5, Responsive Views): Replace this with a loop once we have a sort/priority system for breakpoints.

			// Desktop attributes first, if they exist.
			if ( array_key_exists( 'desktop', $attr_value_with_inherited ) ) {
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'desktop', 'hover' );
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'desktop', 'sticky' );
			}

			// Tablet attributes second, if they exist.
			if ( array_key_exists( 'tablet', $attr_value_with_inherited ) ) {
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'tablet', 'value' );
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'tablet', 'hover' );
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'tablet', 'sticky' );
			}

			// Phone attributes last, if they exist.
			if ( array_key_exists( 'phone', $attr_value_with_inherited ) ) {
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'phone', 'value' );
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'phone', 'hover' );
				$attr_value_with_inherited = self::_inherit_text_shadow_values( $attr_value_with_inherited, 'phone', 'sticky' );
			}
		}

		// Pre-populate with the passed style attributes.
		$attr_to_be_returned = $attr_value_with_inherited;

		// If we have a text-shadow style, we need to check if any values is the
		// same as the inherited breakpoint/state value. If it is, we can delete
		// it from the inheritor.
		if ( ! empty( $attr_to_be_returned ) ) {
			// TODO feat(D5, Responsive Views): Replace this with a loop once we have a sort/priority system for breakpoints.

			// Phone attributes first, if they exist.
			if ( array_key_exists( 'phone', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'phone', 'sticky' );
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'phone', 'hover' );
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'phone', 'value' );
			}

			// Tablet attributes second, if they exist.
			if ( array_key_exists( 'tablet', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'tablet', 'sticky' );
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'tablet', 'hover' );
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'tablet', 'value' );
			}
			// Desktop attributes last, if they exist.
			if ( array_key_exists( 'desktop', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'desktop', 'sticky' );
				$attr_to_be_returned = self::_return_text_shadow_values( $attr_to_be_returned, 'desktop', 'hover' );
			}
		}

		return $attr_to_be_returned;
	}

	/**
	 * Get module class by module name.
	 *
	 * This function is equivalent of JS function getModuleClassByName located in
	 * visual-builder/packages/module-utils/src/get-module-class-by-name/index.ts.
	 *
	 * @since ??
	 *
	 * @param string $namespaced_module_name Module name including namespace.
	 *
	 * @return string Module class name with snake case format. Built-in modules will return
	 * class name with `et_pb_` prefix. Third party modules will return class name with `namespace_` prefix.
	 */
	/**
	 * Get the module class name by the given namespaced module name.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module-utils/functions/getModuleClassByName/ getModuleClassByName} located in
	 * `@divi/module-utils`.
	 *
	 * This function takes a namespaced module name as input and returns the corresponding module class name.
	 * The namespaced module name should be in the format `namespace/module`.
	 * Built-in modules have a `divi` namespace and have a `et_pb_` prefix in the class name.
	 * Third-party modules have a `namespace` namespace and have a `namespace_` prefix in the class name.
	 *
	 * @since ??
	 *
	 * @param string $namespaced_module_name The namespaced module name.
	 *
	 * @return string The module class name with snake case format.
	 */
	public static function get_module_class_by_name( string $namespaced_module_name ): string {
		$parts = explode( '/', $namespaced_module_name, 2 );

		if ( 2 !== count( $parts ) || ! $parts[0] || ! $parts[1] ) {
			return '';
		}

		$prefix = 'divi' === $parts[0] ? 'et_pb' : TextTransform::snake_case( $parts[0] );

		return $prefix . '_' . TextTransform::snake_case( $parts[1] );
	}

	/**
	 * Get subname value of attr and/or its inherited value from larger breakpoint / default state.
	 *
	 * This function takes an array of arguments and retrieves the value of a subname attribute based on the provided arguments.
	 *
	 * Getter and inheritance model can be changed based on `mode` parameter:
	 * 1. `get`                  : Get attr value of given breakpoint + state.
	 * 2. `getAndInheritAll`     : Get attr value combined by all possible inherited attr value on all larger breakpoints.
	 * 3. `getAndInheritClosest` : Get attr value combined by inherited attr value from closest available breakpoint.
	 * 4. `getOrInheritAll`      : Get attr value or inherited attr value from all larger breakpoints.
	 * 5. `getOrInheritClosest`  : Get attr value or inherited attr value from closest available breakpoint.
	 * 6. `inheritAll`           : Get inherited attr value from all larger breakpoints.
	 * 7. `inheritClosest`       : Get inherited attr value from all closest available breakpoint.
	 *
	 * See below for inherited attribute fallback flow:
	 *
	 * |        | value | hover | sticky |
	 * |--------|-------|-------|--------|
	 * | Desktop|   *   |  <--  |  <--   |
	 * | Tablet |   ^   |  <--  |  <--   |
	 * | Phone  |   ^   |  <--  |  <--   |
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attr           The main attribute array from which the subname value will be extracted.
	 *     @type string $breakpoint    The breakpoint value to consider while retrieving the subname value.
	 *     @type string $state         The state value to consider while retrieving the subname value.
	 *     @type string $defaultValue  Optional. The default value to return if the subname value is not found. Default empty string.
	 *     @type string $mode          Optional. The mode to control the retrieval behavior. Default is `getOrInheritAll`.
	 *     @type string $subname       The subname value to retrieve from the attribute array.
	 * }
	 * @return mixed The retrieved subname value.
	 *               Returns the default value if the subname value is not found.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attr'           => ['desktop' => ['value' => ['position' => 'none']]],
	 *     'breakpoint'     => 'desktop',
	 *     'state'          => '',
	 *     'defaultValue'   => '',
	 *     'mode'           => 'getOrInheritAll',
	 *     'subname'        => 'position',
	 * ];
	 *
	 * $subname_value = ModuleUtils::get_attr_subname_value( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attr'           => ['desktop' => ['value' => ['alignment' => 'center']]],
	 *     'breakpoint'     => '',
	 *     'state'          => '',
	 *     'defaultValue'   => '',
	 *     'mode'           => 'getOrInheritAll',
	 *     'subname'        => 'alignment',
	 * ];
	 *
	 * $subname_value = ModuleUtils::get_attr_subname_value( $args );
	 * ```
	 */
	public static function get_attr_subname_value( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'mode'         => 'getOrInheritAll',
				'defaultValue' => '',
			]
		);

		$attr          = $args['attr'];
		$breakpoint    = $args['breakpoint'];
		$state         = $args['state'];
		$default_value = $args['defaultValue'];
		$mode          = $args['mode'];
		$subname       = $args['subname'];

		$attr_value = self::use_attr_value(
			[
				'attr'       => $attr,
				'breakpoint' => $breakpoint,
				'state'      => $state,
				'mode'       => $mode,
			]
		);

		if ( ! is_array( $attr_value ) ) {
			$attr_value = [];
		}

		return ArrayUtility::get_value( $attr_value, $subname, $default_value );
	}

	/**
	 * Get module states.
	 *
	 * This function returns an array containing the default states of a module.
	 * This function runs the value through the `divi_module_utils_states` filter.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module-utils/variables/states/ states } located in `@divi/module-utils`.
	 *
	 * @since ??
	 *
	 * @return array An array of module states. The default values are `['value', 'hover', 'sticky']`.
	 */
	public static function states(): array {
		$states = [
			'value',
			'hover',
			'sticky',
		];

		/**
		 * Filters the module states.
		 *
		 * @since ??
		 *
		 * @param array $states The module states. Default `['value', 'hover', 'sticky']`.
		 */
		return apply_filters( 'divi_module_utils_states', $states );
	}

	/**
	 * Check if an attribute has a value across breakpoints and states based on specified options.
	 *
	 * @since ??
	 *
	 * @param array $attr    The attribute that needs to be checked.
	 * @param array $options {
	 *     Additional options for checking the value (optional).
	 *
	 *     @type string|null   $breakpoint    Optional. The breakpoint to check for the attribute value. One of `desktop`, `tablet`, `phone`.
	 *                                        Default `null`.
	 *     @type string|null   $state         Optional. The state to check for the attribute value.
	 *                                        One of `value`, `hover`, `tablet_value`, `tablet_hover`, `phone_value`, `phone_hover`.
	 *                                        Default `null`.
	 *     @type string|null   $subName       Optional. The sub-name to extract from the attribute value. Default `null`.
	 *     @type callable|null $valueResolver Optional. A callable function to resolve the attribute value. Default `null`.
	 *     @type string|null   $inheritedMode Optional. The inherit mode specifying how the attribute value will be inherited.
	 *                                        One of `inherited`, `inheritedClosest`, `inheritedAll`, `inheritedOrClosest`,
	 *                                        `inheritedOrAll`, `closest`, `all`. Default `getAndInheritAll`.
	 *
	 * @throws InvalidArgumentException If the provided `$options['valueResolver']` is not a callable function.
	 *
	 * @return bool Whether the attribute has a value based on the specified options.
	 *
	 * @example:
	 * ```php
	 * $attr = [
	 *     'desktop' => [
	 *         'normal' => 'Value for desktop',
	 *         'hover' => 'Hover value for desktop',
	 *     ],
	 *     'mobile' => [
	 *         'normal' => 'Value for mobile',
	 *         'hover' => '',
	 *     ],
	 * ];
	 *
	 * // Check if the attribute has a value for the breakpoint 'desktop' and state 'normal'
	 * $result = ModuleUtils::has_value( $attr, [
	 *     'breakpoint' => 'desktop',
	 *     'state' => 'normal',
	 * ] );
	 *
	 * // Check if the attribute has a value for the breakpoint 'mobile' and state 'hover',
	 * // and extract the sub-name 'hover'
	 * $result = ModuleUtils::has_value( $attr, [
	 *     'breakpoint' => 'mobile',
	 *     'state' => 'hover',
	 *     'subName' => 'hover',
	 * ] );
	 *
	 * // Check if the attribute has a value for any breakpoint and state using a value resolver function
	 * $result = ModuleUtils::has_value( $attr, [
	 *     'valueResolver' => function( $value, $args ) {
	 *         // Custom value resolution logic
	 *         // ...
	 *         return $resolved_value;
	 *     },
	 * ] );
	 *
	 * // Check if the attribute has a value for the breakpoint 'desktop' and state 'hover',
	 * // using the 'inherited' mode for resolving the attribute value
	 * $result = ModuleUtils::has_value( $attr, [
	 *     'breakpoint' => 'desktop',
	 *     'state' => 'hover',
	 *     'inheritedMode' => 'inherited',
	 * ] );
	 * ```
	 */
	public static function has_value( array $attr, array $options = [] ): bool {
		if ( ! $attr ) {
			return false;
		}

		$breakpoint        = $options['breakpoint'] ?? null;
		$state             = $options['state'] ?? null;
		$breakpoint_states = MultiViewUtils::get_breakpoints_states();

		// When both breakpoint and state are specified, do not need to iterate through all breakpoints and states.
		// Simply calculate the value based on the specified breakpoint and state.
		if ( $breakpoint && $state ) {
			if ( ! self::_validate_breakpoint_and_state( $breakpoint, $state, $breakpoint_states ) ) {
				return false;
			}

			return self::_calculate_value(
				$attr,
				array_merge(
					$options,
					[
						'breakpoint' => $breakpoint,
						'state'      => $state,
					]
				)
			);
		}

		foreach ( $breakpoint_states as $breakpoint_check => $states ) {
			foreach ( $states as $state_check ) {
				if ( ! self::_validate_breakpoint_and_state( $breakpoint_check, $state_check, $breakpoint_states ) ) {
					continue;
				}

				if ( $breakpoint && $breakpoint_check !== $breakpoint ) {
					continue;
				}

				if ( $state && $state_check !== $state ) {
					continue;
				}

				if ( self::_calculate_value(
					$attr,
					array_merge(
						$options,
						[
							'breakpoint' => $breakpoint_check,
							'state'      => $state_check,
						]
					)
				) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Calculates the value based on the given attributes and options.
	 *
	 * @since ??
	 *
	 * @param array $attr The attributes array.
	 * @param array $options The options array.
	 *
	 * @return bool Returns true if the value is calculated successfully, false otherwise.
	 *
	 * @throws InvalidArgumentException If the provided `$options['valueResolver']` is not a callable function.
	 */
	private static function _calculate_value( array $attr, array $options ) {
		$breakpoint     = $options['breakpoint'] ?? 'desktop';
		$state          = $options['state'] ?? 'value';
		$sub_name       = $options['subName'] ?? null;
		$value_resolver = $options['valueResolver'] ?? null;
		$inherited_mode = $options['inheritedMode'] ?? 'getAndInheritAll';

		if ( ! isset( $attr[ $breakpoint ][ $state ] ) ) {
			return false;
		}

		if ( $inherited_mode ) {
			$value = self::use_attr_value(
				[
					'attr'       => $attr,
					'breakpoint' => $breakpoint,
					'state'      => $state,
					'mode'       => $inherited_mode,
				]
			);
		} else {
			$value = $attr[ $breakpoint ][ $state ];
		}

		if ( $sub_name ) {
			$value = ArrayUtility::get_value( $value ?? [], $sub_name );
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

		if ( is_bool( $value ) ) {
			$has_value = $value;
		} elseif ( is_scalar( $value ) ) {
			// Check the value length.
			$has_value = strlen( strval( $value ) ) > 0;
		} else {
			// Check if the value is not empty.
			$has_value = ! ! $value;
		}

		if ( $has_value ) {
			return true;
		}

		return false;
	}

	/**
	 * Validates the given breakpoint and state against the provided breakpoint-states mapping.
	 *
	 * @param string $breakpoint The breakpoint to validate.
	 * @param string $state The state to validate.
	 * @param array  $breakpoint_states_mapping The mapping of breakpoints to states.
	 * @return bool Returns true if the breakpoint and state are valid, false otherwise.
	 */
	private static function _validate_breakpoint_and_state( string $breakpoint, string $state, array $breakpoint_states_mapping ): bool {
		if ( ! isset( $breakpoint_states_mapping[ $breakpoint ] ) ) {
			return false;
		}

		if ( ! in_array( $state, $breakpoint_states_mapping[ $breakpoint ], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get module class name defined in module.json config.
	 *
	 * - If moduleClassName property in module.json config is falsy, it will fallback to
	 * use convert module name to class name.
	 *
	 * This function is equivalent of JS function getModuleClassName located in
	 * /visual-builder/packages/module-utils/src/get-module-class-name/index.ts
	 *
	 * @since ??
	 *
	 * @param string $module_name Module name.
	 *
	 * @return string Module class name configured in module.json config. Will return empty string on failure.
	 */
	public static function get_module_class_name( $module_name ) {
		$module_config = WP_Block_Type_Registry::get_instance()->get_registered( $module_name );

		$module_class_name = '';

		if ( $module_config ) {
			// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
			$module_class_name = $module_config->moduleClassName ?? '';
		}

		if ( ! $module_class_name ) {
			$module_class_name = self::get_module_class_by_name( $module_name );
		}

		return $module_class_name;
	}

	/**
	 * Get module order class name base defined in module.json config.
	 *
	 * - If moduleOrderClassName property in module.json config is falsy, it will fallback to
	 * use moduleClassName property that is defined in module.json config.
	 * - If moduleClassName property in module.json config is falsy, it will fallback to
	 * convert module name to class name.
	 *
	 * This function is equivalent of JS function getModuleOrderClassBase located in
	 * /visual-builder/packages/module-utils/src/get-module-order-class-base/index.ts
	 *
	 * @since ??
	 *
	 * @param string $module_name Module name.
	 *
	 * @return string Module order class name base. Will return empty string if module is not found.
	 */
	public static function get_module_order_class_name_base( $module_name ) {
		$module_config = WP_Block_Type_Registry::get_instance()->get_registered( $module_name );

		$module_order_class_name_base = '';

		if ( $module_config ) {
			// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
			$module_order_class_name_base = $module_config->moduleOrderClassName ?? '';

			if ( ! $module_order_class_name_base ) {
				// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
				$module_order_class_name_base = $module_config->moduleClassName ?? '';
			}
		}

		if ( ! $module_order_class_name_base ) {
			$module_order_class_name_base = self::get_module_class_by_name( $module_name );
		}

		return $module_order_class_name_base;
	}

	/**
	 * Get module order class name defined in module.json config and add module order index as suffix.
	 *
	 * The base of module order class is populated as follows:
	 * - It will use the moduleOrderClassName property in module.json config if it is not falsy.
	 * - It will use the moduleClassName property in module.json config if it is not falsy.
	 * - It will convert module name to class name
	 *
	 * This function is equivalent of JS function getModuleOrderClassName located in
	 * /visual-builder/packages/module-utils/src/get-module-order-class-name/index.ts
	 *
	 * @since ??
	 *
	 * @param string   $module_id      Module unique ID.
	 * @param int|null $store_instance The ID of instance where this block stored in BlockParserStore class.
	 *
	 * @return string Module order class name. Will return empty string if module is not found.
	 */
	public static function get_module_order_class_name( $module_id, $store_instance = null ) {
		$module_object = BlockParserStore::get( $module_id, $store_instance );

		$layout_type = BlockParserStore::get_layout_type();

		$layout_map = apply_filters(
			'et_builder_order_class_name_suffix_map',
			[
				'default'          => '',
				'et_header_layout' => '_tb_header',
				'et_body_layout'   => '_tb_body',
				'et_footer_layout' => '_tb_footer',
			]
		);

		$selector_suffix = $layout_map[ $layout_type ] ?? '';

		if ( $module_object ) {
			// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
			$module_order_class_name_base = self::get_module_order_class_name_base( $module_object->blockName );

			if ( $module_order_class_name_base ) {
				// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
				return $module_order_class_name_base . '_' . $module_object->orderIndex . $selector_suffix;
			}
		}

		return '';
	}

	/**
	 * Loads inline fonts for a module.
	 *
	 * This function enqueues the inline font from a module's inline fonts list,
	 * such that the font assets will be loaded in the browser.
	 *
	 * @since ??
	 *
	 * @param array $attrs The attributes of the module.
	 *
	 * @returns void
	 *
	 * @example
	 * ```php
	 * $attrs = [
	 *     // ... rest of the attributes
	 *    'content' => [
	 *      'decoration' => [
	 *        'inlineFont' => [
	 *          'desktop' => [
	 *            'value' => [
	 *              'families' => [
	 *                'Arima',
	 *                'Yatra One',
	 *              ],
	 *            ],
	 *          ],
	 *        ],
	 *      ],
	 *   ],
	 * ];
	 *
	 * ModuleUtils::load_module_inline_font( $attrs );
	 * ```
	 */
	public static function load_module_inline_font( array $attrs ): void {
		$inline_font = $attrs['content']['decoration']['inlineFont'] ?? [];

		foreach ( $inline_font as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$attr_value = self::use_attr_value(
					[
						'attr'       => $inline_font,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$font_family_names = $attr_value['families'] ?? [];

				foreach ( $font_family_names as $font_family ) {
					Fonts::add( $font_family );
				}
			}
		}
	}

	/**
	 * Merge Attrs.
	 *
	 * This function is used to merge attrs with default attrs.
	 *
	 * This function is equivalent of JS function mergeAttrs located in
	 * visual-builder/packages/module-utils/src/merge-attrs/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of options.
	 *
	 *     @type array $defaultAttrs Default attrs.
	 *     @type array $presetAttrs Preset attrs.
	 *     @type array $attrs Attrs.
	 * }
	 *
	 * @return array Merged attrs.
	 */
	public static function merge_attrs( array $args = [] ): array {
		$default_attrs = $args['defaultAttrs'] ?? [];
		$preset_attrs  = $args['presetAttrs'] ?? [];
		$attrs         = $args['attrs'] ?? [];

		return array_replace_recursive( [], $default_attrs, $preset_attrs, $attrs );
	}

	/**
	 * This method sorts the breakpoints based on a predetermined order.
	 *
	 * It takes an associative array as input and returns a new array that has the keys
	 * sorted based on a defined order. If a key doesn't exist in the defined order, it's assumed
	 * it should be placed last. The order currently is 'desktop', 'desktopAbove', 'tablet',
	 * 'tabletOnly', and then 'phone'.
	 *
	 * @since ??
	 *
	 * @param array $attr The associative array which keys are to be sorted.
	 *
	 * @return array $sorted_attr An associative array which keys are sorted in the defined order.
	 *
	 * @example
	 *
	 * $input = ['phone' => 'val1', 'tablet' => 'val2', 'desktop' => 'val3'];
	 * print_r(\ModuleUtils::sort_breakpoints($input));
	 * // Outputs: Array('desktop' => 'val3', 'tablet' => 'val2', 'phone' => 'val1')
	 */
	public static function sort_breakpoints(
		array $attr,
		?array $breakpoint_order = null
	): array {
		$order = $breakpoint_order ?? [

			// baseDevice.
			'desktop',

			// Smaller than baseDevice, large to small.
			'desktopAbove', // disabled-on specific.
			'tabletWide',
			'tablet',
			'tabletOnly', // disabled-on specific.
			'phoneWide',
			'phone',

			// Larger than baseDevice, small to large.
			'widescreen',
			'ultraWide',
		];

		// A copy of the array keys in their current order.
		$keys = array_keys( $attr );

		// Sort the keys based on their position in $order.
		usort(
			$keys,
			function ( $a, $b ) use ( $order ) {
				$position_a = array_search( $a, $order, true );
				$position_b = array_search( $b, $order, true );

				// If a key is not found in $order, we assume it comes last.
				$position_a = false === $position_a ? count( $order ) : $position_a;
				$position_b = false === $position_b ? count( $order ) : $position_b;

				return $position_a <=> $position_b;
			}
		);

		// Create a new array with the keys sorted as required.
		$sorted_attr = [];
		foreach ( $keys as $key ) {
			$sorted_attr[ $key ] = $attr[ $key ];
		}

		return $sorted_attr;
	}

	/**
	 * This method sorts the states based on a predetermined order.
	 *
	 * It takes an associative array as input and returns a new array that has the keys
	 * sorted based on a defined order. If a key doesn't exist in the defined order, it's assumed
	 * it should be placed last. The order currently is 'value', 'hover', 'sticky',
	 * 'tabletOnly', and then 'phone'.
	 *
	 * @since ??
	 *
	 * @param array $attr The associative array which keys are to be sorted.
	 *
	 * @return array $sorted_attr An associative array which keys are sorted in the defined order.
	 *
	 * @example
	 *
	 * $input = ['hover' => 'val1', 'sticky' => 'val2', 'value' => 'val3'];
	 * print_r(\ModuleUtils::sort_breakpoints($input));
	 * // Outputs: Array('value' => 'val3', 'hover' => 'val2', 'sticky' => 'val1')
	 */
	public static function sort_states( array $attr ): array {
		// TODO feat(D5, Responsive Views): Replace when we have a sort/priority system for states.
		$order = [
			'value',
			'hover',
			'sticky',
		];

		// A copy of the array keys in their current order.
		$keys = array_keys( $attr );

		// Sort the keys based on their position in $order.
		usort(
			$keys,
			function ( $a, $b ) use ( $order ) {
				$position_a = array_search( $a, $order, true );
				$position_b = array_search( $b, $order, true );

				// If a key is not found in $order, we assume it comes last.
				$position_a = false === $position_a ? count( $order ) : $position_a;
				$position_b = false === $position_b ? count( $order ) : $position_b;

				return $position_a <=> $position_b;
			}
		);

		// Create a new array with the keys sorted as required.
		$sorted_attr = [];
		foreach ( $keys as $key ) {
			$sorted_attr[ $key ] = $attr[ $key ];
		}

		return $sorted_attr;
	}

	/**
	 * Generate preset class name.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $presetType       The Preset type. Can be 'module' or 'group'.
	 *     @type string $presetModuleName The Preset Module Name.
	 *     @type string $presetGroupName  The Preset Group Name.
	 *     @type string $presetId         The Preset ID.
	 * }
	 *
	 * @return string The preset class name.
	 */
	public static function generate_preset_class_name( array $args ): string {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes. [https://github.com/elegantthemes/Divi/issues/41805].
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-10', 'GlobalPresetItemUtils::generate_preset_class_name' );

		return GlobalPresetItemUtils::generate_preset_class_name( $args );
	}

	/**
	 * Convert the module name for the section preset.
	 *
	 * @since ??
	 *
	 * @param string $module_name The module name.
	 * @param array  $attrs The module attributes.
	 *
	 * @return string The converted module name.
	 */
	public static function maybe_convert_preset_module_name( string $module_name, array $attrs ): string {
		if ( 'divi/section' === $module_name ) {
			$section_type = $attrs['module']['advanced']['type']['desktop']['value'] ?? null;

			if ( 'fullwidth' === $section_type ) {
					return 'divi/fullwidth-section';
			}

			if ( 'specialty' === $section_type ) {
					return 'divi/specialty-section';
			}
		}

		return $module_name;
	}

	/**
	 * Removes empty attributes.
	 *
	 * This function recursively filters the provided attributes, removing any elements that are empty arrays.
	 * It makes an exception for the 'style' attribute of a 'font' group, which is allowed to be an empty array.
	 *
	 * @since ??
	 *
	 * @param array $attrs The array of attributes to filter.
	 * @return array The filtered array with empty attributes removed.
	 */
	public static function remove_empty_array_attributes( array $attrs ): array {
		return ArrayUtility::filter_deep(
			$attrs,
			function( $value, $key, $path ) {
				// Return true if the value is an empty array and the path is the style attribute of a font group.
				$path_items = array_slice( $path, -3 );

				if ( count( $path_items ) && 'font' === $path_items[0] && 'style' === $key ) {
					return true;
				}

				return is_array( $value ) && empty( $value ) ? false : true;
			}
		);
	}

	/**
	 * Nest an array of attributes under a base path.
	 *
	 * This function takes a base path and an array of attributes and nests the attributes under the base path.
	 * The base path is a string that represents a path to the nested array. The function returns an array with the
	 * attributes nested under the base path.
	 *
	 * @since ??
	 *
	 * @param string $base_path The base path under which the attributes will be nested.
	 * @param array  $attrs     The array of attributes to nest.
	 *
	 * @return array The nested array of attributes.
	 */
	public static function nest_array_attributes( string $base_path, array $attrs ): array {
		$keys   = explode( '.', $base_path );
		$nested = $attrs;

		while ( $keys ) {
			$key    = array_pop( $keys );
			$nested = [ $key => $nested ];
		}

		return $nested;
	}

	/**
	 * Recursively removes keys from a target array that also exist in a reference array.
	 *
	 * This function compares a target array and a reference array. If a key exists in both,
	 * the key-value pair is removed from the target array. This process is performed recursively
	 * for nested arrays. If the reference array is empty, the target array is returned without changes.
	 *
	 * @since ??
	 *
	 * @param array $target_attrs The array from which keys will be removed.
	 * @param array $reference_attrs The array used to determine which keys to remove from the target.
	 *
	 * @return array The target array, modified with keys removed if they exist in the reference array.
	 */
	public static function remove_matching_attrs( array $target_attrs, array $reference_attrs ): array {
		if ( empty( $reference_attrs ) ) {
			return $target_attrs;
		}

		foreach ( $target_attrs as $key => $value ) {
			if ( array_key_exists( $key, $reference_attrs ) ) {
				$reference_value = $reference_attrs[ $key ];

				if ( is_array( $value ) && is_array( $reference_value ) ) {
					$target_attrs[ $key ] = self::remove_matching_attrs( $value, $reference_value );
				} elseif ( is_scalar( $value ) && is_scalar( $reference_value ) ) {
					unset( $target_attrs[ $key ] );
				}
			}
		}

		return $target_attrs;
	}

	/**
	 * Recursively replace value in a target array with value from a reference array.
	 *
	 * This function compares a target array and a reference array. If a key exists in both,
	 * the value in a target array will be replaced with the value from the reference array. This process is performed recursively
	 * for nested arrays. If the reference array is empty, the target array is returned without changes.
	 *
	 * @since ??
	 *
	 * @param array $target_attrs The array from which keys will be removed.
	 * @param array $reference_attrs The array used to determine which keys to remove from the target.
	 *
	 * @return array The target array, modified with keys removed if they exist in the reference array.
	 */
	public static function replace_matching_attrs( array $target_attrs, array $reference_attrs ): array {
		if ( empty( $reference_attrs ) ) {
			return $target_attrs;
		}

		foreach ( $target_attrs as $key => $value ) {
			if ( array_key_exists( $key, $reference_attrs ) ) {
				$reference_value = $reference_attrs[ $key ];

				if ( is_array( $value ) && is_array( $reference_value ) ) {
					$target_attrs[ $key ] = self::replace_matching_attrs( $value, $reference_value );
				} elseif ( is_scalar( $value ) && is_scalar( $reference_value ) ) {
					$target_attrs[ $key ] = $reference_value;
				}
			}
		}

		return $target_attrs;
	}

	/**
	 * Recursively removes key-value pairs from a target array that have matching values in a reference array.
	 *
	 * This function compares a target array and a reference array. If a key exists in both and the values are equal,
	 * the key-value pair is removed from the target array. This process is performed recursively for nested arrays.
	 * If the reference array is empty, the target array is returned without changes.
	 *
	 * @since ??
	 *
	 * @param array $target_attrs The array from which key-value pairs will be removed.
	 * @param array $reference_attrs The array used to determine which key-value pairs to remove from the target.
	 *
	 * @return array The target array, modified with key-value pairs removed if they have matching values in the reference array.
	 */
	public static function remove_matching_values( array $target_attrs, array $reference_attrs ): array {
		if ( empty( $reference_attrs ) ) {
			return $target_attrs;
		}

		foreach ( $target_attrs as $key => $value ) {
			if ( array_key_exists( $key, $reference_attrs ) ) {
				$reference_value = $reference_attrs[ $key ];

				if ( is_array( $value ) && is_array( $reference_value ) ) {
					$target_attrs[ $key ] = self::remove_matching_values( $value, $reference_value );
					if ( empty( $target_attrs[ $key ] ) ) {
						unset( $target_attrs[ $key ] );
					}
				} elseif ( $value === $reference_value ) {
					unset( $target_attrs[ $key ] );
				}
			}
		}

		return $target_attrs;
	}

	/**
	 * Extract the title for a link.
	 *
	 * @since ??
	 *
	 * @param string $html_text The HTML content of the link.
	 *
	 * @return string The extracted title.
	 */
	public static function extract_link_title( string $html_text ): string {
		return wp_kses(
			$html_text,
			[
				'strong' => [
					'id'    => [],
					'class' => [],
					'style' => [],
				],
				'em'     => [
					'id'    => [],
					'class' => [],
					'style' => [],
				],
				'i'      => [
					'id'    => [],
					'class' => [],
					'style' => [],
				],
			]
		);
	}

	/**
	 * Processes and inherits position style attributes across breakpoints and states.
	 *
	 * This function calculates the position style attributes for different breakpoints
	 * (desktop, tablet, phone) and states (value, hover, sticky), ensuring that missing
	 * values inherit from parent breakpoints or states. Redundant or empty attributes
	 * are removed to streamline the final output.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for position style inheritance.
	 *
	 *     @type array $attr                    Position style attributes to process.
	 *                                          to use if values are not explicitly defined.
	 * }
	 * @return array Final processed position style attributes with inheritance applied.
	 *
	 * @example:
	 * ```php
	 * $attr_value_with_inherited = [
	 *    'desktop' => [
	 *        'value' => [
	 *            'mode' => 'relative',
	 *            'offset' => [
	 *                'vertical' => '10px',
	 *                'horizontal' => '20px',
	 *            ],
	 *        ],
	 *    ],
	 *    'tablet' => [
	 *        'value' => [
	 *            'mode' => 'absolute',
	 *            'offset' => [
	 *                'vertical' => '15px',
	 *            ],
	 *        ],
	 *        'hover' => [
	 *            'mode' => 'fixed',
	 *            'offset' => [
	 *                'vertical' => '5px',
	 *                'horizontal' => '10px',
	 *            ],
	 *        ],
	 *    ],
	 * ];
	 *
	 * $breakpoint = 'tablet';
	 * $state = 'value';
	 *
	 * $updated_attr_value_with_inherited = self::get_and_inherit_position_style_attr([
	 *    'attr' => $attr_value_with_inherited
	 * ]);
	 * ```
	 */
	public static function get_and_inherit_position_style_attr( array $args ): array {
		$initial_style_attr = $args['attr'] ?? [];

		// Pre-populate with the passed style attributes.
		$attr_value_with_inherited = $initial_style_attr;

		// If we have a position-style, handle inheritance for breakpoints and states.
		if ( ! empty( $attr_value_with_inherited ) ) {
			if ( array_key_exists( 'desktop', $attr_value_with_inherited ) ) {
				// Desktop attributes first.
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'desktop', 'value' );
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'desktop', 'hover' );
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'desktop', 'sticky' );
			}

			if ( array_key_exists( 'tablet', $attr_value_with_inherited ) ) {
				// Tablet attributes.
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'tablet', 'value' );
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'tablet', 'hover' );
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'tablet', 'sticky' );
			}

			if ( array_key_exists( 'phone', $attr_value_with_inherited ) ) {
				// Phone attributes.
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'phone', 'value' );
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'phone', 'hover' );
				$attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, 'phone', 'sticky' );
			}
		}

		// Prepare attributes to be returned.
		$attr_to_be_returned = $attr_value_with_inherited;

		// Remove redundant inherited values.
		if ( ! empty( $attr_to_be_returned ) ) {
			// Phone attributes first.
			if ( array_key_exists( 'phone', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'phone', 'sticky' );
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'phone', 'hover' );
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'phone', 'value' );

				if ( empty( self::_array_trim( $attr_to_be_returned['phone'] ) ) ) {
					unset( $attr_to_be_returned['phone'] );
				}
			}

			// Tablet attributes.
			if ( array_key_exists( 'tablet', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'tablet', 'sticky' );
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'tablet', 'hover' );
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'tablet', 'value' );

				if ( empty( self::_array_trim( $attr_to_be_returned['tablet'] ) ) ) {
					unset( $attr_to_be_returned['tablet'] );
				}
			}

			// Desktop attributes.
			if ( array_key_exists( 'desktop', $attr_to_be_returned ) ) {
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'desktop', 'sticky' );
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'desktop', 'hover' );
				$attr_to_be_returned = self::_return_position_style_values( $attr_to_be_returned, 'desktop', 'value' );

				if ( empty( self::_array_trim( $attr_to_be_returned['desktop'] ) ) ) {
					unset( $attr_to_be_returned['desktop'] );
				}
			}
		}

		return $attr_to_be_returned;
	}

	/**
	 * Removes redundant or empty position style values for a specific breakpoint and state.
	 *
	 * This function compares the current position style values for a given breakpoint and state
	 * with the parent breakpoint and state. If the values are identical or empty, they are removed.
	 * Otherwise, the current values are retained in the output.
	 *
	 * @since ??
	 *
	 * @param array  $attr_to_be_returned Position style attributes being processed.
	 * @param string $breakpoint          The breakpoint being processed (e.g., 'desktop', 'tablet', 'phone').
	 * @param string $state               The state being processed (e.g., 'value', 'hover', 'sticky').
	 *
	 * @return array Processed position style attributes with redundant values removed.
	 *
	 * @example:
	 * ```php
	 * $attr_to_be_returned = [
	 *    'desktop' => [
	 *        'value' => [
	 *            'mode' => 'absolute',
	 *            'offset' => [
	 *                'vertical' => '10px',
	 *                'horizontal' => '20px',
	 *            ],
	 *        ],
	 *    ],
	 *    'tablet' => [
	 *        'value' => [
	 *            'mode' => 'absolute',
	 *            'offset' => [
	 *                'vertical' => '15px',
	 *            ],
	 *        ],
	 *    ],
	 * ];
	 *
	 * $breakpoint = 'tablet';
	 * $state = 'value';
	 *
	 * $cleaned_attr_values = self::_return_position_style_values( $attr_to_be_returned, $breakpoint, $state );
	 * ``
	 */
	private static function _return_position_style_values( array $attr_to_be_returned, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint   = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names  = Breakpoint::get_enabled_breakpoint_names();
		$parent_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$parent_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		$is_desktop_value       = 'desktop' === $breakpoint && 'value' === $state;
		$current_position_style = $attr_to_be_returned[ $breakpoint ][ $state ] ?? [];
		$parent_position_style  = $attr_to_be_returned[ $parent_breakpoint ][ $parent_state ] ?? [];
		$position_styles_match  = self::_is_same( $current_position_style, $parent_position_style );
		$is_current_empty       = ! $current_position_style;

		if ( $is_desktop_value && ! $is_current_empty ) {
			$attr_to_be_returned[ $breakpoint ][ $state ] = $current_position_style;
		} elseif ( ! $position_styles_match && ! $is_current_empty ) {
			$attr_to_be_returned[ $breakpoint ][ $state ] = $current_position_style;
		} else {
			unset( $attr_to_be_returned[ $breakpoint ][ $state ] );
		}

		return $attr_to_be_returned;
	}

	/**
	 * Inherits position style values for a specific breakpoint and state.
	 *
	 * This function ensures that missing position style values in a given breakpoint
	 * and state are inherited from the parent breakpoint and state. Defaults are applied
	 * when values are missing in both the current and parent contexts.
	 *
	 * @since ??
	 *
	 * @param array  $attr_value_with_inherited Position style attributes being processed.
	 * @param string $breakpoint               The breakpoint being processed (e.g., 'desktop', 'tablet', 'phone').
	 * @param string $state                    The state being processed (e.g., 'value', 'hover', 'sticky').
	 *
	 * @return array Position style attributes with inherited values applied.
	 *
	 * @example:
	 * ```php
	 * $attr_value_with_inherited = [
	 *    'desktop' => [
	 *        'value' => [
	 *            'mode' => 'relative',
	 *            'offset' => [
	 *                'vertical' => '10px',
	 *                'horizontal' => '20px',
	 *            ],
	 *        ],
	 *    ],
	 *    'tablet' => [],
	 * ];
	 *
	 * $breakpoint = 'tablet';
	 * $state = 'value';
	 *
	 * $updated_attr_value_with_inherited = self::_inherit_position_style_values( $attr_value_with_inherited, $breakpoint, $state );
	 * ```
	 */
	private static function _inherit_position_style_values( array $attr_value_with_inherited, string $breakpoint, string $state ): array {
		// TODO feat(D5, Refactor) pass breakpointNames and baseBreakpoint as arguments.
		// See: https://github.com/elegantthemes/Divi/issues/41620.
		$base_breakpoint    = Breakpoint::get_base_breakpoint_name();
		$breakpoint_names   = Breakpoint::get_enabled_breakpoint_names();
		$inherit_breakpoint = self::get_inherit_breakpoint(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);
		$inherit_state      = self::get_inherit_state(
			[
				'breakpoint'      => $breakpoint,
				'state'           => $state,
				'baseBreakpoint'  => $base_breakpoint,
				'breakpointNames' => $breakpoint_names,
			]
		);

		$attr_values        = $attr_value_with_inherited[ $breakpoint ][ $state ] ?? [];
		$attr_parent_values = $attr_value_with_inherited[ $inherit_breakpoint ][ $inherit_state ] ?? [];

		$attr_value_with_inherited[ $breakpoint ][ $state ] = self::_array_trim(
			[
				'mode'   => $attr_values['mode'] ?? $attr_parent_values['mode'] ?? 'default',
				'origin' => $attr_values['origin'] ?? $attr_parent_values['origin'] ?? [],
				'offset' => [
					'vertical'   => $attr_values['offset']['vertical'] ?? $attr_parent_values['offset']['vertical'] ?? '0',
					'horizontal' => $attr_values['offset']['horizontal'] ?? $attr_parent_values['offset']['horizontal'] ?? '0',
				],
			]
		);

		return $attr_value_with_inherited;
	}

	/**
	 * Get all merged attributes for a given block.
	 *
	 * This function will merge the default attributes, module preset attributes, and group preset attributes
	 * with the attributes of the block. The final attributes will be returned as an array.
	 *
	 * @since ??
	 *
	 * @param   BlockParserBlock $block Parsed block.
	 *
	 * @return array   Parent module attributes.
	 */
	public static function get_all_attrs( BlockParserBlock $block ) {
		$default_attrs = ModuleRegistration::get_default_attrs( $block->blockName );
		$attrs         = $block->attrs ?? [];

		$group_presets = GlobalPreset::get_selected_group_presets(
			[
				'moduleName'  => $block->blockName,
				'moduleAttrs' => $attrs,
			]
		);

		$group_render_attrs = [];
		foreach ( $group_presets as $group_id => $group_preset_item ) {
			if ( $group_preset_item instanceof GlobalPresetItem ) {
				$group_render_attrs = array_replace_recursive(
					$group_render_attrs,
					$group_preset_item->get_data_render_attrs()
				);
			}
		}

		// Get preset attributes for this module.
		$item_preset = GlobalPreset::get_selected_preset(
			[
				'moduleName'  => $block->blockName,
				'moduleAttrs' => $attrs ?? [],
			]
		);

		$preset_render_attrs = $item_preset->get_data_render_attrs();

		return array_replace_recursive(
			$default_attrs,
			$preset_render_attrs,
			$group_render_attrs,
			$attrs
		);
	}

	/**
	 * Check if the provided CSS unit is a math function or not.
	 * https://regex101.com/r/eHZbiF/1 - Regex.
	 *
	 * @since ??
	 *
	 * @param string $value CSS unit.
	 *
	 * @return boolean True if the string starts with one of the math functions; otherwise, false.
	 */
	public static function is_css_math_function( string $value ): bool {
		return preg_match( '/^(clamp|min|max|calc)\s*\(/', trim( $value ) ) === 1;
	}

	/**
	 * Check if parent module's layout is set to flex.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module-utils/functions/isParentLayoutIsFlex isParentLayoutIsFlex}
	 * in `@divi/module-utils` package.
	 *
	 * @since ??
	 *
	 * @param string $module_id The ID of the current module.
	 *
	 * @return bool Returns true if the parent module's layout is set to "flex", otherwise false.
	 */
	public static function is_parent_layout_is_flex( string $module_id ): bool {
		$is_flexbox_enabled = et_get_experiment_flag( 'flexbox' );

		if ( ! $is_flexbox_enabled ) {
			return false;
		}

		$parent = BlockParserStore::get_parent( $module_id );

		if ( ! $parent ) {
			return false;
		}

		$parent_attrs = self::get_all_attrs( $parent );

		if ( ! isset( $parent_attrs['module']['decoration']['layout'] ) ) {
			return false;
		}

		$layout_attr = self::get_attr_value(
			[
				'attr'            => $parent_attrs['module']['decoration']['layout'],
				'breakpoint'      => 'desktop',
				'state'           => 'value',
				'breakpointNames' => Breakpoint::get_enabled_breakpoint_names(),
				'baseBreakpoint'  => 'desktop',
				'mode'            => 'getAndInheritAll',
			]
		);

		// Set default to 'flex' only if the feature flag is enabled.
		return 'flex' === ( $layout_attr['display'] ?? ( $is_flexbox_enabled ? 'flex' : 'block' ) );
	}

	/**
	 * Generate selectors for a given base selector and sub selector.
	 *
	 * If a selector contains ':hover', the sub selector is inserted before ':hover'.
	 * Otherwise, the sub selector is appended to the selector.
	 *
	 * @since ??
	 *
	 * @param string $base_selector Base selector (can be comma-separated).
	 * @param string $sub_selector  Sub selector.
	 *
	 * @return string Combined selectors string.
	 */
	public static function generate_combined_selectors( string $base_selector, string $sub_selector ): string {
		$selectors = array_map(
			function ( $selector ) use ( $sub_selector ) {
				$selector = trim( $selector );
				if ( false !== strpos( $selector, ':hover' ) ) {
					// Insert sub_selector before :hover pseudo-class.
					return preg_replace( '/:hover/', ' ' . $sub_selector . ':hover', $selector );
				}
				return $selector . ' ' . $sub_selector;
			},
			explode( ',', $base_selector )
		);
		return implode( ', ', $selectors );
	}
}
