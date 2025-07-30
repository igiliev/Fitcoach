<?php
/**
 * Module Library: BarCountersItem Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\BarCountersItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class BarCountersItemPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\BarCountersItem
 */
class BarCountersItemPresetAttrsMap {
	/**
	 * Get the preset attributes map for the BarCountersItem module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/counter' !== $module_name ) {
			return $map;
		}

		unset( $map['module.advanced.text.text__color'] );

		return array_merge(
			$map,
			[
				'barProgress.decoration.background__color' => [
					'attrName' => 'barProgress.decoration.background',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
			]
		);

	}
}
