<?php
/**
 * Module: PositionSettingsPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\PositionSettings;

use ET\Builder\Packages\Module\Options\Position\PositionPresetAttrsMap;
use ET\Builder\Packages\Module\Options\ZIndex\ZIndexPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * PositionSettingsPresetAttrsMap class.
 *
 * This class provides the static map for the position settings preset attributes.
 *
 * @since ??
 */
class PositionSettingsPresetAttrsMap {
	/**
	 * Get the map for the position settings preset attributes.
	 *
	 * @since ??
	 *
	 * @return array The map for the position settings preset attributes.
	 */
	public static function get_map() {
		$result = [];

		$position_group = PositionPresetAttrsMap::get_map( 'module.decoration.position' );
		$z_index_group  = ZIndexPresetAttrsMap::get_map( 'module.decoration.zIndex' );

		$result = array_merge(
			[],
			$position_group,
			$z_index_group
		);

		return $result;
	}
}
