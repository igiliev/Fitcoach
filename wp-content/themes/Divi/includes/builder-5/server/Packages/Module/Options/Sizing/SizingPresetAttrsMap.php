<?php
/**
 * Module: SizingPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Sizing;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * SizingPresetAttrsMap class.
 *
 * This class provides the static map for the sizing preset attributes.
 *
 * @since ??
 */
class SizingPresetAttrsMap {
	/**
	 * Get the map for the sizing preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the sizing preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__width"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'width',
			],
			"{$attr_name}__maxWidth"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'maxWidth',
			],
			"{$attr_name}__alignment" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'alignment',
			],
			"{$attr_name}__minHeight" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'minHeight',
			],
			"{$attr_name}__height"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'height',
			],
			"{$attr_name}__maxHeight" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'maxHeight',
			],
		];
	}
}
