<?php
/**
 * Module: IconPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * IconPresetAttrsMap class.
 *
 * This class provides the static map for the icon preset attributes.
 *
 * @since ??
 */
class IconPresetAttrsMap {
	/**
	 * Get the map for the icon preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the icon preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__color"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'color',
			],
			// "{$attr_name}__style_html" => [
			// 	'attrName' => $attr_name,
			// 	'preset'   => [ 'style', 'html' ],
			// ],
			"{$attr_name}__useSize"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'useSize',
			],
			"{$attr_name}__size"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'size',
			],
		];
	}
}
