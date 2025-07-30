<?php
/**
 * Module: StickyPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Sticky;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * StickyPresetAttrsMap class.
 *
 * This class provides the static map for the sticky preset attributes.
 *
 * @since ??
 */
class StickyPresetAttrsMap {
	/**
	 * Get the map for the sticky preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the sticky preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__position"           => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'position',
			],
			"{$attr_name}__offset.top"         => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'offset.top',
			],
			"{$attr_name}__offset.bottom"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'offset.bottom',
			],
			"{$attr_name}__limit.top"          => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'limit.top',
			],
			"{$attr_name}__limit.bottom"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'limit.bottom',
			],
			"{$attr_name}__offset.surrounding" => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'offset.surrounding',
			],
			"{$attr_name}__transition"         => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'transition',
			],
		];
	}
}
