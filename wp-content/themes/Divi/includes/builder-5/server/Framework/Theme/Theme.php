<?php
/**
 * Theme: Theme Specific Functionalities.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Theme class.
 *
 * This class provides theme specific functionalities such as retrieving sidebars.
 *
 * @since ??
 */
class Theme {
	/**
	 * Get the registered sidebar areas.
	 *
	 * This function retrieves the registered sidebar areas from the global
	 * `$wp_registered_sidebars` variable and formats the data into an array.
	 *
	 * @since ??
	 *
	 * @return array {
	 *     The sidebar areas.
	 *
	 *     @type array  $widget_areas An associative array of the registered sidebar areas, where
	 *                                the keys are the sidebar IDs and the values are the sidebar names.
	 *     @type string $area         The first sidebar area ID from the registered sidebar areas.
	 *               - 'widget_areas': An associative array of the registered sidebar areas, where
	 *                 the keys are the sidebar IDs and the values are the sidebar names.
	 *               - 'area': The first sidebar area ID from the registered sidebar areas.
	 * }
	 *
	 * @example:
	 * ```php
	 *  $sidebarAreas = My_Namespace\My_Class::get_sidebar_areas();
	 * ```
	 *
	 * @output:
	 * ```php
	 *  [
	 *      'widget_areas' => [
	 *          'sidebar-1' => [ 'label' => 'Primary Sidebar' ],
	 *          'sidebar-2' => [ 'label' => 'Secondary Sidebar' ],
	 *          ...
	 *      ],
	 *      'area' => 'sidebar-1'
	 *  ]
	 * ```
	 */
	public static function get_sidebar_areas(): array {
		global $wp_registered_sidebars;

		// Sidebars.
		$sidebar_ids   = wp_list_pluck( $wp_registered_sidebars, 'id' );
		$sidebar_names = wp_list_pluck( $wp_registered_sidebars, 'name' );

		$sidebars_formatted = [];

		$sidebars = array_combine( $sidebar_ids, $sidebar_names );

		foreach ( $sidebars as $id => $name ) {
			$sidebars_formatted[ $id ] = [ 'label' => $name ];
		}

		return [
			'widget_areas' => $sidebars_formatted,
			'area'         => array_shift( $sidebar_ids ),
		];
	}

	/**
	 * Get the default sidebar area.
	 *
	 * Retrieves the ID of the first registered sidebar area from the global `$wp_registered_sidebars`.
	 * If there are no registered sidebars, an empty string is returned.
	 *
	 * @since ??
	 *
	 * @return string The ID of the default sidebar area.
	 *                If there are no registered sidebars, an empty string is returned.
	 */
	public static function get_default_area(): string {
		global $wp_registered_sidebars;

		if ( ! empty( $wp_registered_sidebars ) ) {
			// Pluck sidebar ids.
			$sidebar_ids = wp_list_pluck( $wp_registered_sidebars, 'id' );

			// Return first sidebar id.
			return array_shift( $sidebar_ids );
		}

		return '';
	}
}
