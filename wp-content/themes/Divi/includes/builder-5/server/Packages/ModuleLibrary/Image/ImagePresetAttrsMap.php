<?php
/**
 * Module Library: Image Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Image;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class ImagePresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Image
 */
class ImagePresetAttrsMap {
	/**
	 * Get the preset attributes map for the Image module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/image' !== $module_name ) {
			return $map;
		}

		$keys_to_remove = [
			'module.advanced.sizing',
		];

		foreach ( $keys_to_remove as $key ) {
			unset( $map[ $key ] );
		}

		return array_merge(
			$map,
			[
				'module.advanced.sizing__forceFullwidth' => [
					'attrName' => 'module.advanced.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'forceFullwidth',
				],
			]
		);

	}
}
