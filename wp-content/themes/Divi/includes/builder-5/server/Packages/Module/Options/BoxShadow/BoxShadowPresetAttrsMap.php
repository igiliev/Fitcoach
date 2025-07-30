<?php
/**
 * Module: BoxShadowPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\BoxShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * BoxShadowPresetAttrsMap class.
 *
 * This class provides the static map for the box-shadow preset attributes.
 *
 * @since ??
 */
class BoxShadowPresetAttrsMap {
	/**
	 * Get the map for the box-shadow preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the box-shadow preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__style"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'style',
			],
			"{$attr_name}__horizontal" => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'horizontal',
			],
			"{$attr_name}__vertical"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'vertical',
			],
			"{$attr_name}__blur"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'blur',
			],
			"{$attr_name}__spread"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'spread',
			],
			"{$attr_name}__color"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'color',
			],
			"{$attr_name}__position"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'html', 'style' ],
				'subName'  => 'position',
			],
		];
	}
}
