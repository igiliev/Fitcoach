<?php
/**
 * Module: FontHeaderPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FontHeaderGroup;

use ET\Builder\Packages\Module\Options\Font\FontPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FontHeaderPresetAttrsMap class.
 *
 * This class provides static map for the header font preset attributes.
 *
 * @since ??
 */
class FontHeaderPresetAttrsMap {
	/**
	 * Get the map for the header font preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the header font preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		$h1_font_attrs_map = FontPresetAttrsMap::get_map( "{$attr_name}.h1" );
		$h2_font_attrs_map = FontPresetAttrsMap::get_map( "{$attr_name}.h2" );
		$h3_font_attrs_map = FontPresetAttrsMap::get_map( "{$attr_name}.h3" );
		$h4_font_attrs_map = FontPresetAttrsMap::get_map( "{$attr_name}.h4" );
		$h5_font_attrs_map = FontPresetAttrsMap::get_map( "{$attr_name}.h5" );
		$h6_font_attrs_map = FontPresetAttrsMap::get_map( "{$attr_name}.h6" );

		return array_merge( $h1_font_attrs_map, $h2_font_attrs_map, $h3_font_attrs_map, $h4_font_attrs_map, $h5_font_attrs_map, $h6_font_attrs_map );
	}
}
