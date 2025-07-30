<?php
/**
 * Module Library: Circle Counter Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\CircleCounter;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class CircleCounterPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Code
 */
class CircleCounterPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Circle Counter module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/circle-counter' !== $module_name ) {
			return $map;
		}

		unset( $map['number.decoration.font.font__lineHeight'] );

		return array_merge(
			$map,
			[
				'circle.advanced.color'                    => [
					'attrName' => 'circle.advanced.color',
					'preset'   => [
						'style',
					],
				],
				'circle.advanced.background__color'        => [
					'attrName' => 'circle.advanced.background',
					'preset'   => [
						'style',
					],
					'subName'  => 'color',
				],
				'circle.advanced.background__opacity'      => [
					'attrName' => 'circle.advanced.background',
					'preset'   => [
						'style',
					],
					'subName'  => 'opacity',
				],
				'title.decoration.font.font__headingLevel' => [
					'attrName' => 'title.decoration.font.font',
					'preset'   => [
						'html',
					],
					'subName'  => 'headingLevel',
				],
			]
		);
	}
}
