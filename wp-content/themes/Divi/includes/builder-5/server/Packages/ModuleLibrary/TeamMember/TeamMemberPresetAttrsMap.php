<?php
/**
 * Module Library: TeamMember Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\TeamMember;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class TeamMemberPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\TeamMember
 */
class TeamMemberPresetAttrsMap {
	/**
	 * Get the preset attributes map for the TeamMember module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/team-member' !== $module_name ) {
			return $map;
		}

		unset( $map['social.decoration.icon__style_html'] );

		return $map;
	}
}
