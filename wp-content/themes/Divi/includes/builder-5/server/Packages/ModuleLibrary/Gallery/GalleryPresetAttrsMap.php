<?php
/**
 * Module Library: Gallery Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Gallery;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class GalleryPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Gallery
 */
class GalleryPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Gallery module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/gallery' !== $module_name ) {
			return $map;
		}

		$keys_to_remove = [
			'pagination.decoration.font.font__textAlign',
		];

		foreach ( $keys_to_remove as $key ) {
			unset( $map[ $key ] );
		}

		return array_merge(
			$map,
			[
				'module.decoration.scroll__gridMotion.enable' => [
					'attrName' => 'module.decoration.scroll',
					'preset'   => [
						'script',
					],
					'subName'  => 'gridMotion.enable',
				],
				'pagination.decoration.font__textAlign' => [
					'attrName' => 'pagination.decoration.font',
					'preset'   => [
						'style',
					],
					'subName'  => 'textAlign',
				],
			]
		);
	}
}
