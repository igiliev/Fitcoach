<?php
/**
 * Divi Builder's Class for handling breakpoints.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Breakpoint;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\ScriptData;

/**
 * Class for handling breakpoints.
 *
 * @since ??
 */
class Breakpoint {
	/**
	 * WordPress Option name that is used to save breakpoints.
	 *
	 * @var string
	 */
	public static $option_name = 'et_divi_builder_breakpoints';

	/**
	 * Base breakpoint name.
	 *
	 * @var string
	 */
	public static $base_breakpoint = 'desktop';

	/**
	 * Cache group
	 *
	 * @var string
	 */
	public static $cache_group = 'divi_breakpoint';

	/**
	 * Base state name.
	 *
	 * @var string
	 */
	public static $base_state = 'value';

	/**
	 * Get default breakpoint names.
	 *
	 * @since ??
	 */
	public static function get_default_breakpoint_names() {
		return [
			'desktop',
			'tablet',
			'phone',
		];
	}

	/**
	 * Get default breakpoints settings values.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_default_settings_values() {
		return [
			'items'           => [
				'phone'      => [
					'order'    => 10,
					'enable'   => true,
					'maxWidth' => [
						'value'   => '767px',
						'default' => 767,
					],
					'name'     => 'phone',
				],
				'phoneWide'  => [
					'order'    => 20,
					'enable'   => false,
					'maxWidth' => [
						'value'   => '860px',
						'default' => 860,
					],
					'name'     => 'phoneWide',
				],
				'tablet'     => [
					'order'    => 30,
					'enable'   => true,
					'maxWidth' => [
						'value'   => '980px',
						'default' => 980,
					],
					'name'     => 'tablet',
				],
				'tabletWide' => [
					'order'    => 40,
					'enable'   => false,
					'maxWidth' => [
						'value'   => '1024px',
						'default' => 1024,
					],
					'name'     => 'tabletWide',
				],
				'desktop'    => [
					'order'      => 50,
					'baseDevice' => true,
					'enable'     => true,
					'name'       => 'desktop',
				],
				'widescreen' => [
					'order'    => 60,
					'enable'   => false,
					'minWidth' => [
						'value'   => '1280px',
						'default' => 1280,
					],
					'name'     => 'widescreen',
				],
				'ultraWide'  => [
					'order'    => 70,
					'enable'   => false,
					'minWidth' => [
						'value'   => '1440px',
						'default' => 1440,
					],
					'name'     => 'ultraWide',
				],
			],
			'disabledOnItems' => [
				'desktopAbove' => [
					'name'   => 'desktopAbove',
					'enable' => true,
					'order'  => 45,
				],
				'tabletOnly'   => [
					'name'   => 'tabletOnly',
					'enable' => true,
					'order'  => 25,
				],
			],
		];
	}

	/**
	 * Get default style breakpoint order.
	 *
	 * @since ??
	 */
	public static function get_default_style_breakpoint_order() {
		return [
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
	}

	/**
	 * Get default style breakpoint settings.
	 *
	 * @since ??
	 */
	public static function get_default_style_breakpoint_settings() {
		$default_settings = self::get_default_settings_values();

		return [
			'desktop' => [
				'baseDevice' => $default_settings['items']['desktop']['baseDevice'] ?? false,
				'order'      => $default_settings['items']['desktop']['order'] ?? 50,
			],
			'tablet'  => [
				'maxWidth' => [
					'value' => $default_settings['items']['tablet']['maxWidth']['value'] ?? '980px',
				],
				'order'    => $default_settings['items']['tablet']['order'] ?? 30,
			],
			'phone'   => [
				'maxWidth' => [
					'value' => $default_settings['items']['phone']['maxWidth']['value'] ?? '767px',
				],
				'order'    => $default_settings['items']['phone']['order'] ?? 10,
			],
		];
	}

	/**
	 * Get breakpoints settings values.
	 *
	 * @since ??
	 */
	public static function get_settings_values() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		// Get saved breakpoints.
		$saved_breakpoints = get_option( self::$option_name, [] );

		// TODO feat(D5, breakpoints) adjust returned value once there are more than `items` property.
		$settings_values = [
			'items' => empty( $saved_breakpoints )
				? self::get_default_settings_values()['items']
				: self::validate_items( $saved_breakpoints['items'] ?? [] ),
		];

		// Cache the result.
		wp_cache_set( $cache_key, $settings_values, self::$cache_group );

		return $settings_values;
	}

	/**
	 * Get all breakpoint names, including disabled-on items.
	 *
	 * @since ??
	 */
	public static function get_all_breakpoint_names() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		$default_items = self::get_default_settings_values()['items'];

		$all_breakpoint_names = array_keys( $default_items );

		// Cache the result.
		wp_cache_set( $cache_key, $all_breakpoint_names, self::$cache_group );

		return $all_breakpoint_names;
	}

	/**
	 * Get enabled breakpoints settings.
	 *
	 * @since ??
	 */
	public static function get_enabled_breakpoints() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		// Get saved settings values.
		$settings = self::get_settings_values()['items'];

		// Filter enabled breakpoints based on `enable` property.
		$enabled_breakpoints = array_filter(
			$settings,
			function( $breakpoint ) {
				return $breakpoint['enable'] ?? false;
			}
		);

		// Sort breakpoints based on their `order` property.
		usort(
			$enabled_breakpoints,
			function( $a, $b ) {
				return $b['order'] - $a['order'];
			}
		);

		// Cache the result.
		wp_cache_set( $cache_key, $enabled_breakpoints, self::$cache_group );

		return $enabled_breakpoints;
	}

	/**
	 * Get enabled breakpoint names.
	 *
	 * @since ??
	 */
	public static function get_enabled_breakpoint_names() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		$enabled_breakpoints = self::get_enabled_breakpoints();

		$names = array_map(
			function( $breakpoint ) {
				return $breakpoint['name'];
			},
			$enabled_breakpoints
		);

		// Cache the result.
		wp_cache_set( $cache_key, $names, self::$cache_group );

		return $names;
	}

	/**
	 * Get style breakpoint order.
	 * This breakpoint order is used for sorting style that will be rendered using `get_statements()` method.
	 * The order for style renderer is tailored to how media query behaves. Some rules:
	 * 1. Base device is rendered first
	 * 2. Breakpoints that are larger than base device are rendered from small to large.
	 * 3. Breakpoints that are smaller than base device are rendered from large to small.
	 *
	 * @since ??
	 */
	public static function get_style_breakpoint_order() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		// Get breakpoints (customizable), convert it into array.
		$layout_breakpoints = array_values( self::get_settings_values()['items'] );

		// Get disabled on options's breakpoints, convert it into array.
		$disabled_on_breakpoints = array_values( self::get_default_settings_values()['disabledOnItems'] );

		// Combine both breakpoints into one array..
		$breakpoint_settings = array_merge( $layout_breakpoints, $disabled_on_breakpoints );

		// Get the order value of base device. So far `desktop` is set as baseDevice and there is no plan yet
		// on making it editable nor change it into something else, but better be safe to get it dynamically.
		// The value itself is already there anyway.
		$base_device_order = array_reduce(
			$breakpoint_settings,
			function( $carry, $breakpoint ) {
				return ( $breakpoint['baseDevice'] ?? false ) ? $breakpoint['order'] : $carry;
			},
			0
		);

		// Ensure base device order is properly set.
		if ( null === $base_device_order ) {
			return []; // No base device found, return empty array.
		}

		// Sort breakpoints order.
		usort(
			$breakpoint_settings,
			function ( $a, $b ) use ( $base_device_order ) {
				// If `a` is the base device, put it before `b`.
				if ( ! empty( $a['baseDevice'] ) ) {
					return -1;
				}

				// If `b` is the base device, put it after `a`.
				if ( ! empty( $b['baseDevice'] ) ) {
					return 1;
				}

				if ( ( $a['order'] > $base_device_order ) && ( $b['order'] > $base_device_order ) ) {
						// Both are larger than base device, sort from small to large.
						return $a['order'] - $b['order'];
				} elseif ( ( $a['order'] < $base_device_order ) && ( $b['order'] < $base_device_order ) ) {
						// Both are smaller than base device → sort from large to small.
						return $b['order'] - $a['order'];
				} else {
						// If one is larger and one is smaller, maintain relative positioning.
						return ( $a['order'] > $b['order'] ) ? 1 : -1;
				}
			}
		);

		$style_breakpoint_order = array_map(
			function( $breakpoint ) {
				return $breakpoint['name'];
			},
			$breakpoint_settings
		);

		// Cache the result.
		wp_cache_set( $cache_key, $style_breakpoint_order, self::$cache_group );

		return $style_breakpoint_order;
	}

	/**
	 * Get breakpoint settings for rendering style.
	 * This settings are specifically used to render style's media query.
	 *
	 * @since ??
	 */
	public static function get_style_breakpoint_settings() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		$enabled_breakpoints = self::get_enabled_breakpoints();

		$settings = [];

		foreach ( $enabled_breakpoints as $breakpoint ) {
			$name = $breakpoint['name'] ?? null;

			if ( ! $name ) {
				continue;
			}

			$settings[ $name ] = [
				'order' => $breakpoint['order'],
			];

			if ( $breakpoint['baseDevice'] ?? false ) {
				$settings[ $name ]['baseDevice'] = true;
			}

			if ( $breakpoint['maxWidth']['value'] ?? false ) {
				$settings[ $name ]['maxWidth'] = [
					'value' => $breakpoint['maxWidth']['value'],
				];
			}

			if ( $breakpoint['minWidth']['value'] ?? false ) {
				$settings[ $name ]['minWidth'] = [
					'value' => $breakpoint['minWidth']['value'],
				];
			}
		}

		// Cache the result.
		wp_cache_set( $cache_key, $settings, self::$cache_group );

		return $settings;
	}

	/**
	 * Get rules for disabled-on breakpoint.
	 *
	 * TODO feat(D5, Customizable Breakpoints) Upgrade disabled-on spec so it can handle all possible breakpoints.
	 * Right now it only handles breakpoint pre-customizable breakpoints due to absence of specification.
	 *
	 * @since ??
	 *
	 * @return array array of rules for disabled-on breakpoint.
	 */
	public static function get_disabled_on_rules() {
		// Returned value of this method is expected to remain the same throughout the page render.
		// Thus for performance reason calculate this once, cache the result, and return the cached value for next calls.
		$cache_key = __FUNCTION__;

		$cached_value = wp_cache_get( $cache_key, self::$cache_group );

		if ( false !== $cached_value ) {
			return $cached_value;
		}

		$rules = [];

		$enabled_breakpoint_names = self::get_enabled_breakpoint_names();
		$enabled_breakpoints      = self::get_enabled_breakpoints();

		// Desktop is base device, so it is guaranteed to exist.
		$desktop_index = array_search( 'desktop', $enabled_breakpoint_names, true );

		// Get index of breakpoint right below `desktop`.
		$below_desktop_index = $desktop_index + 1;

		// Get breakpoint settings of breakpoint right below `desktop`.
		$below_desktop_breakpoint = $enabled_breakpoints[ $below_desktop_index ] ?? false;

		// `desktopAbove` breakpoints requires existence of breakpoint smaller than breakpoint; This means
		// all `desktop` above breakpoints is media query which its min-width limit is minimum width of
		// `desktop` breakpoint which is max width of breakpoint right below `desktop`.
		if ( $below_desktop_breakpoint ) {
			$desktop_above_min_width = ( intval( $below_desktop_breakpoint['maxWidth']['value'] ) + 1 ) . 'px';

			$rules['desktopAbove'] = "@media only screen and (min-width: {$desktop_above_min_width})";
		}

		// Tablet can be disabled so it isn't guaranteed to exist.
		// `tabletOnly` breakpoints requires existence of breakpoint smaller than tablet breakpoint; this means
		// the range for `tabletOnly` breakpoint is maximum width for tablet AND minimum width for tablet breakpoint
		// which is maximum width of breakpoint right below `tablet`. Not that due to its naming, this breakpoint
		// requires `tablet` breakpoint to be enabled.
		$tablet_index = array_search( 'tablet', $enabled_breakpoint_names, true );

		if ( -1 < $tablet_index ) {
			$tablet = $enabled_breakpoints[ $tablet_index ] ?? false;

			$below_tablet_index = $tablet_index + 1;
			$below_tablet       = $enabled_breakpoints[ $below_tablet_index ] ?? false;

			if ( $tablet && $below_tablet ) {
				$tablet_only_min_width = ( intval( $below_tablet['maxWidth']['value'] ) + 1 ) . 'px';
				$tablet_only_max_width = $tablet['maxWidth']['value'];

				$rules['tabletOnly'] = "@media only screen and (min-width: {$tablet_only_min_width}) and (max-width: {$tablet_only_max_width})";
			}
		}

		// Cache the result.
		wp_cache_set( $cache_key, $rules, self::$cache_group );

		return $rules;
	}

	/**
	 * Get base breakpoint name.
	 *
	 * @since ??
	 */
	public static function get_base_breakpoint_name() {
		return self::$base_breakpoint;
	}

	/**
	 * Get base state name.
	 *
	 * @since ??
	 */
	public static function get_base_state_name() {
		return self::$base_state;
	}

	/**
	 * Method for updating breakpoints on saved WordPress options.
	 *
	 * @param array $breakpoints breakpoints settings.
	 *
	 * @since ??
	 */
	public static function update( $breakpoints ) {
		// Sanitize breakpoints.
		$sanitized_breakpoints = [
			'items' => self::validate_items( $breakpoints['items'] ?? [] ),
		];

		// Update breakpoints on WordPress options.
		return update_option( self::$option_name, $sanitized_breakpoints );
	}

	/**
	 * Set script data for breakpoint.
	 *
	 * @since ??
	 */
	public static function set_script_data() {
		ScriptData::add_data_item(
			[
				'data_name'    => 'breakpoint',
				'data_item_id' => 'enabledBreakpoints',
				'data_item'    => self::get_enabled_breakpoints(),
			]
		);

		ScriptData::add_data_item(
			[
				'data_name'    => 'breakpoint',
				'data_item_id' => 'enabledBreakpointNames',
				'data_item'    => self::get_enabled_breakpoint_names(),
			]
		);

		ScriptData::add_data_item(
			[
				'data_name'    => 'breakpoint',
				'data_item_id' => 'baseBreakpointName',
				'data_item'    => self::get_base_breakpoint_name(),
			]
		);
	}

	/**
	 * Validate breakpoint item.
	 *
	 * @since ??
	 *
	 * @param string $breakpoint_name Breakpoint name.
	 * @param array  $item            Breakpoint item.
	 * @param array  $items           Breakpoint items.
	 *
	 * @return array | null
	 */
	public static function validate_item( $breakpoint_name, $item, $items = [] ) {
		// Get default breakpoint items.
		$default_items = self::get_default_settings_values()['items'];

		// Get default breakpoint item.
		$default_item = $default_items[ $breakpoint_name ] ?? null;

		// If no default item is found, then the given breakpoint name is not valid.
		// TODO feat(D5, breakpoints) reconsider this if user is allowed to add custom breakpoints.
		if ( is_null( $default_item ) ) {
			return null;
		}

		// Get passed property values.
		$enable    = $item['enable'] ?? null;
		$min_width = $item['minWidth'] ?? null;
		$max_width = $item['maxWidth'] ?? null;

		// Start populating the validated item.
		$validated_item = [
			'enable' => ! is_null( $enable ) ? ( $enable ? true : false ) : $default_item['enable'],
		];

		// Get list of breakpoint names based on default items. At the moment user can't add custom breakpoints thus
		// the order of breakpoints on default items is reliable.
		$breakpoint_names = array_keys( $default_items );

		// Get current breakpoint name index based on breakpoint names.
		$current_breakpoint_name_index = array_search( $breakpoint_name, $breakpoint_names, true );

		// Smallest and biggest breakpoint has special case.
		$is_smallest_breakpoint = 0 === $current_breakpoint_name_index;
		$is_biggest_breakpoint  = count( $breakpoint_names ) - 1 === $current_breakpoint_name_index;

		// Calculate smaller breakpoint value number.
		$smaller_breakpoint_value_number = null;

		if ( $is_smallest_breakpoint ) {
			$smaller_breakpoint_value_number = 0;
		} else {
			// Get smaller breakpoint name.
			$smaller_breakpoint_name = $breakpoint_names[ $current_breakpoint_name_index - 1 ] ?? null;

			// If the immediate smaller breakpoint is base device, look into the second closest breakpoint.
			$base_device = $default_items[ $smaller_breakpoint_name ]['baseDevice'] ?? false;

			if ( true === $base_device ) {
				$smaller_breakpoint_name = $breakpoint_names[ $current_breakpoint_name_index - 2 ] ?? null;
			}

			// Get "value" of smaller breakpoint based on minWidth / maxWidth; fallback to default value if needed.
			$smaller_breakpoint_value = $items[ $smaller_breakpoint_name ]['maxWidth']['value']
				?? $items[ $smaller_breakpoint_name ]['minWidth']['value']
				?? $default_items[ $smaller_breakpoint_name ]['maxWidth']['default']
				?? $default_items[ $smaller_breakpoint_name ]['minWidth']['default']
				?? null;

			if ( ! is_null( $smaller_breakpoint_value ) ) {
				$smaller_breakpoint_value_number = intval( $smaller_breakpoint_value );
			}
		}

		// If smaller breakpoint value number remains null, something is wrong and return null to stop the data validation.
		// One possible case: the given breakpoint name is not valid.
		if ( is_null( $smaller_breakpoint_value_number ) ) {
			return null;
		}

		// Calculate bigger breakpoint value number.
		$bigger_breakpoint_value_number = null;

		if ( $is_biggest_breakpoint ) {
			// Let's assume none set media query bigger than this (1,000,000px).
			$bigger_breakpoint_value_number = 1000000;
		} else {
			// Get bigger breakpoint name.
			$bigger_breakpoint_name = $breakpoint_names[ $current_breakpoint_name_index + 1 ] ?? null;

			// if the immidiate bigger breakpoint is base device, look into the second closest breakpoint.
			$base_device = $default_items[ $bigger_breakpoint_name ]['baseDevice'] ?? false;

			if ( true === $base_device ) {
				$bigger_breakpoint_name = $breakpoint_names[ $current_breakpoint_name_index + 2 ] ?? null;
			}

			// Get "value" of bigger breakpoint based on minWidth / maxWidth; fallback to default value if needed.
			$bigger_breakpoint_value = $items[ $bigger_breakpoint_name ]['minWidth']['value']
				?? $items[ $bigger_breakpoint_name ]['maxWidth']['value']
				?? $default_items[ $bigger_breakpoint_name ]['minWidth']['default']
				?? $default_items[ $bigger_breakpoint_name ]['maxWidth']['default']
				?? null;

			if ( ! is_null( $bigger_breakpoint_value ) ) {
				$bigger_breakpoint_value_number = intval( $bigger_breakpoint_value );
			}
		}

		// If bigger breakpoint value remains null, something is wrong and return null to stop the data validation.
		// One possible case: the given breakpoint name is not valid.
		if ( is_null( $bigger_breakpoint_value_number ) ) {
			return null;
		}

		// Check for `minWidth` property.
		if ( ! is_null( $min_width ) && $min_width['value'] ) {
			$min_width_number = intval( $min_width['value'] );

			// Updated value should be larger than smaller breakpoint but lower than bigger breakpoint.
			if ( $smaller_breakpoint_value_number < $min_width_number && $min_width_number < $bigger_breakpoint_value_number ) {
				$validated_item['minWidth'] = [
					'value' => $min_width_number . 'px',
				];
			}
		}

		// Check for `maxWidth` property.
		if ( ! is_null( $max_width ) && $max_width['value'] ) {
			$max_width_number = intval( $max_width['value'] );

			// Updated value should be larger than smaller breakpoint but lower than bigger breakpoint.
			if ( $smaller_breakpoint_value_number < $max_width_number && $max_width_number < $bigger_breakpoint_value_number ) {
				$validated_item['maxWidth'] = [
					'value' => $max_width_number . 'px',
				];
			}
		}

		// Include `order` property from default settings.
		if ( isset( $default_item['order'] ) ) {
			$validated_item['order'] = $default_item['order'];
		}

		// Include `name` property from default settings.
		// TODO feat(D5, Customizable Breakpoint) save custom breakpoint name when breakpoint settings is saved.
		// See: https://github.com/elegantthemes/Divi/issues/41550.
		if ( ! isset( $validated_item['name'] ) ) {
			$validated_item['name'] = $breakpoint_name;
		}

		return $validated_item;
	}

	/**
	 * Validate breakpoint items.
	 *
	 * @since ??
	 *
	 * @param array $items Breakpoint items.
	 */
	public static function validate_items( $items ) {
		// Get default items.
		$default_items = self::get_default_settings_values()['items'];

		// Generate valid item names based on default values' items.
		$item_names = array_keys( $default_items );

		// Populate validated items.
		$validated_items = [];

		$items[ self::$base_breakpoint ] = $default_items[ self::$base_breakpoint ];

		// Loop over items, and populate validated items.
		foreach ( $items as $item_name => $item ) {
			// If the passed items doesn't exist in item names, skip it.
			if ( ! in_array( $item_name, $item_names, true ) ) {
				return;
			}

			// Validate updated item.
			$validated_item = self::validate_item( $item_name, $item, $items );

			// Set base device property.
			if ( $item_name === self::$base_breakpoint ) {
				$validated_item['baseDevice'] = true;
			}

			// Only populate validated item if it's an array. Otherwise, it doesn't pass validation.
			if ( is_array( $validated_item ) ) {
				$validated_items[ $item_name ] = $validated_item;
			}
		}

		return $validated_items;
	}
}
