<?php
/**
 * Module Library: MapItem Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\MapItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class MapItemPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\MapItem
 */
class MapItemPresetAttrsMap {
	/**
	 * Get the preset attributes map for the MapItem module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/map-pin' !== $module_name ) {
			return $map;
		}

		return [
			'title.innerContent'   => [
				'attrName' => 'title.innerContent',
				'preset'   => 'content',
			],
			'content.innerContent' => [
				'attrName' => 'content.innerContent',
				'preset'   => [ 'style' ],
			],
			'pin.innerContent'     => [
				'attrName' => 'pin.innerContent',
				'preset'   => 'content',
			],
		];
	}
}
