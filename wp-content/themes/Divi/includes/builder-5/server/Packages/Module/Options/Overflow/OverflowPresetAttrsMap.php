<?php
/**
 * Module: OverflowPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Overflow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * OverflowPresetAttrsMap class.
 *
 * This class provides static map for the overflow preset attributes.
 *
 * @since ??
 */
class OverflowPresetAttrsMap {
	/**
	 * Get the map for the overflow preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the overflow preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__x" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'x',
			],
			"{$attr_name}__y" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'y',
			],
		];
	}
}
