<?php
/**
 * Module: ScrollSettingsPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\ScrollSettings;

use ET\Builder\Packages\Module\Options\Scroll\ScrollPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Sticky\StickyPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ScrollSettingsPresetAttrsMap class.
 *
 * This class provides the static map for the scroll settings preset attributes.
 *
 * @since ??
 */
class ScrollSettingsPresetAttrsMap {
	/**
	 * Get the map for the scroll settings preset attributes.
	 *
	 * @since ??
	 *
	 * @return array The map for the scroll settings preset attributes.
	 */
	public static function get_map() {
		$result = [];

		$scroll_group = ScrollPresetAttrsMap::get_map( 'module.decoration.scroll' );
		$sticky_group = StickyPresetAttrsMap::get_map( 'module.decoration.sticky' );

		$result = array_merge(
			[],
			$scroll_group,
			$sticky_group
		);

		return $result;
	}
}
