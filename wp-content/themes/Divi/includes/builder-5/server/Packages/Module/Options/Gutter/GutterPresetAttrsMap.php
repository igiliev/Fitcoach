<?php
/**
 * Module: GutterPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Gutter;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * GutterPresetAttrsMap class.
 *
 * This class provides the static map for the gutter preset attributes.
 *
 * @since ??
 */
class GutterPresetAttrsMap {
	/**
	 * Get the map for the gutter preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the gutter preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__width"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'width',
			],
			"{$attr_name}__makeEqual" => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'makeEqual',
			],
		];
	}
}
