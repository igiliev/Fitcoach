<?php
/**
 * Module: FontBodyPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FontBodyGroup;

use ET\Builder\Packages\Module\Options\Font\FontPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FontBodyPresetAttrsMap class.
 *
 * This class provides static map for the body font preset attributes.
 *
 * @since ??
 */
class FontBodyPresetAttrsMap {
	/**
	 * Get the map for the body font preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the body font preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		$body_font_attrs_map  = FontPresetAttrsMap::get_map( "{$attr_name}.body" );
		$link_font_attrs_map  = FontPresetAttrsMap::get_map( "{$attr_name}.link" );
		$ul_font_attrs_map    = FontPresetAttrsMap::get_map(
			"{$attr_name}.ul",
			[
				'has_list' => true,
			]
		);
		$ol_font_attrs_map    = FontPresetAttrsMap::get_map(
			"{$attr_name}.ol",
			[
				'has_list' => true,
			]
		);
		$quote_font_attrs_map = FontPresetAttrsMap::get_map(
			"{$attr_name}.quote",
			[
				'has_border' => true,
			]
		);

		return array_merge( $body_font_attrs_map, $link_font_attrs_map, $ul_font_attrs_map, $ol_font_attrs_map, $quote_font_attrs_map );
	}
}
