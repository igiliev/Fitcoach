<?php
/**
 * Module: TextPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Text;

use ET\Builder\Packages\Module\Options\TextShadow\TextShadowPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TextPresetAttrsMap class.
 *
 * This class provides static map for the text preset attributes.
 *
 * @since ??
 */
class TextPresetAttrsMap {
	/**
	 * Get the map for the text preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the text preset attributes.
	 */
	public static function get_map( string $attr_name ) {

		$text_shadow_attrs_map = TextShadowPresetAttrsMap::get_map( "{$attr_name}.textShadow" );

		$text_attrs_map = [
			"{$attr_name}.text__orientation" => [
				'attrName' => "{$attr_name}.text",
				'preset'   => [ 'html' ],
				'subName'  => 'orientation',
			],
			"{$attr_name}.text__color"       => [
				'attrName' => "{$attr_name}.text",
				'preset'   => [ 'html' ],
				'subName'  => 'color',
			],
		];

		return array_merge( $text_attrs_map, $text_shadow_attrs_map );
	}
}
