<?php
/**
 * Module: VisibilitySettingsPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\VisibilitySettings;

use ET\Builder\Packages\Module\Options\DisabledOn\DisabledOnPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Overflow\OverflowPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * VisibilitySettingsPresetAttrsMap class.
 *
 * This class provides the static map for the visibility settings preset attributes.
 *
 * @since ??
 */
class VisibilitySettingsPresetAttrsMap {
	/**
	 * Get the map for the visibility settings preset attributes.
	 *
	 * @since ??
	 *
	 * @return array The map for the visibility settings preset attributes.
	 */
	public static function get_map() {
		$result = [];

		$disabled_on_group = DisabledOnPresetAttrsMap::get_map( 'module.decoration.disabledOn' );
		$overflow_group    = OverflowPresetAttrsMap::get_map( 'module.decoration.overflow' );

		$result = array_merge(
			[],
			$disabled_on_group,
			$overflow_group
		);

		return $result;
	}
}
