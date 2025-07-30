<?php
/**
 * Module Library: Icon Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class IconPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Icon
 */
class IconPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Icon module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/icon' !== $module_name ) {
			return $map;
		}

		$keys_to_remove = [
			'module.decoration.background__image.parallax.enabled',
			'module.decoration.background__image.parallax.method',
			'module.decoration.background__image.size',
			'module.decoration.background__image.width',
			'module.decoration.background__image.height',
			'module.decoration.background__image.position',
			'module.decoration.background__image.horizontalOffset',
			'module.decoration.background__image.verticalOffset',
			'module.decoration.background__image.repeat',
		];

		foreach ( $keys_to_remove as $key ) {
			unset( $map[ $key ] );
		}

		return array_merge(
			$map,
			[
				'icon.innerContent'         => [
					'attrName' => 'icon.innerContent',
					'preset'   => 'content',
				],
				'icon.innerContent__url'    => [
					'attrName' => 'icon.innerContent',
					'preset'   => [
						'html',
					],
					'subName'  => 'url',
				],
				'icon.innerContent__target' => [
					'attrName' => 'icon.innerContent',
					'preset'   => [
						'html',
					],
					'subName'  => 'target',
				],
				'icon.innerContent__title'  => [
					'attrName' => 'icon.innerContent',
					'preset'   => [ 'html' ],
					'subName'  => 'title',
				],
			]
		);
	}
}
