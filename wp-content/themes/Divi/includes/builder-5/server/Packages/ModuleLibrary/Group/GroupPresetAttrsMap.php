<?php
/**
 * Module Library: Group Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Group;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class GroupPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\Library\Group
 */
class GroupPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Group module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/group' !== $module_name ) {
			return $map;
		}

		unset( $map['module.decoration.spacing__margin'] );

		return $map;
	}
}
