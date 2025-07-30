<?php
/**
 * Module Library: FullwidthPostContent Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FullwidthPostContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class FullwidthPostContentPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\FullwidthPostContent
 */
class FullwidthPostContentPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Fullwidth Post Content module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/fullwidth-post-content' !== $module_name ) {
			return $map;
		}

		return array_merge(
			$map,
			[
				'css__freeForm' => [
					'attrName' => 'css',
					'preset'   => [ 'style' ],
					'subName'  => 'freeForm',
				],
			]
		);
	}
}
