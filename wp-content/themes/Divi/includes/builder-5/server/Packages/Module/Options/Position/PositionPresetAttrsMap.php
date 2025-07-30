<?php
/**
 * Module: PositionPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Position;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * PositionPresetAttrsMap class.
 *
 * This class provides the static map for the position preset attributes.
 *
 * @since ??
 */
class PositionPresetAttrsMap {
	/**
	 * Get the map for the position preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the position preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__mode"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mode',
			],
			"{$attr_name}__origin.relative"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'origin.relative',
			],
			"{$attr_name}__origin.absolute"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'origin.absolute',
			],
			"{$attr_name}__origin.fixed"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'origin.fixed',
			],
			"{$attr_name}__offset.vertical"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'offset.vertical',
			],
			"{$attr_name}__offset.horizontal" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'offset.horizontal',
			],
		];
	}
}
