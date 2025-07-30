<?php
/**
 * Module Library: SocialMediaFollowItem Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\SocialMediaFollowItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class SocialMediaFollowItemPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\SocialMediaFollowItem
 */
class SocialMediaFollowItemPresetAttrsMap {
	/**
	 * Get the preset attributes map for the SocialMediaFollowItem module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/social-media-follow-network' !== $module_name ) {
			return $map;
		}

		return [];
	}
}
