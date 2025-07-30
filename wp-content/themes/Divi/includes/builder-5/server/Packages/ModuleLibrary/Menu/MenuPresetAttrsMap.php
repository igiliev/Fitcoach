<?php
/**
 * Module Library: Menu Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class MenuPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Menu
 */
class MenuPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Menu module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/menu' !== $module_name ) {
			return $map;
		}

		unset( $map['module.advanced.text.textShadow__style'] );
		unset( $map['module.advanced.text.textShadow__horizontal'] );
		unset( $map['module.advanced.text.textShadow__vertical'] );
		unset( $map['module.advanced.text.textShadow__blur'] );
		unset( $map['module.advanced.text.textShadow__color'] );
		unset( $map['logo.decoration.sizing__alignment'] );
		unset( $map['logo.decoration.sizing__minHeight'] );
		unset( $map['menu.decoration.font.font__textAlign'] );
		unset( $map['menuDropdown.decoration.font__color'] );
		unset( $map['menuMobile.decoration.font__color'] );
		unset( $map['cartQuantity.decoration.font.font__textAlign'] );
		unset( $map['cartIcon.decoration.font__color'] );
		unset( $map['cartIcon.decoration.font__size'] );
		unset( $map['searchIcon.decoration.font__color'] );
		unset( $map['searchIcon.decoration.font__size'] );
		unset( $map['hamburgerMenuIcon.decoration.font__color'] );
		unset( $map['hamburgerMenuIcon.decoration.font__size'] );
		unset( $map['title.decoration.font.font__headingLevel'] );

		return array_merge(
			$map,
			[
				'menuDropdown.decoration.font.font__color' => [
					'attrName' => 'menuDropdown.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'menuMobile.decoration.font.font__color'   => [
					'attrName' => 'menuMobile.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'cartIcon.decoration.font.font__color'     => [
					'attrName' => 'cartIcon.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'searchIcon.decoration.font.font__color'   => [
					'attrName' => 'searchIcon.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'hamburgerMenuIcon.decoration.font.font__color' => [
					'attrName' => 'hamburgerMenuIcon.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'cartIcon.decoration.font.font__size'      => [
					'attrName' => 'cartIcon.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'searchIcon.decoration.font.font__size'    => [
					'attrName' => 'searchIcon.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'hamburgerMenuIcon.decoration.font.font__size' => [
					'attrName' => 'hamburgerMenuIcon.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
			]
		);
	}
}
