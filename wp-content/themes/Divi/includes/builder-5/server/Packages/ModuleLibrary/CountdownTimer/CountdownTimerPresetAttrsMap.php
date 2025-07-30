<?php
/**
 * Module Library:Countdown Timer Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\CountdownTimer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class CountdownTimerPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\CountdownTimer
 */
class CountdownTimerPresetAttrsMap {
	/**
	 * Get the preset attributes map for the CountdownTimer module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/countdown-timer' !== $module_name ) {
			return $map;
		}

		unset( $map['separator.decoration.font.font__textAlign'] );

		return array_merge(
			$map,
			[
				'title.decoration.font.font__headingLevel' => [
					'attrName' => 'title.decoration.font.font',
					'preset'   => [ 'html' ],
					'subName'  => 'headingLevel',
				],
			]
		);
	}
}
