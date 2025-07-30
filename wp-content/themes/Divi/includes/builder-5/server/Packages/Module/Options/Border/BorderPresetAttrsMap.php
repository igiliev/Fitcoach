<?php
/**
 * Module: BorderPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Border;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * BorderPresetAttrsMap class.
 *
 * This class provides the static map for the border preset attributes.
 *
 * @since ??
 */
class BorderPresetAttrsMap {
	/**
	 * Get the map for the border preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the border preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__radius"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'radius',
			],
			"{$attr_name}__styles"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles',
			],
			"{$attr_name}__styles.all.width"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.all.width',
			],
			"{$attr_name}__styles.top.width"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.top.width',
			],
			"{$attr_name}__styles.right.width"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.right.width',
			],
			"{$attr_name}__styles.bottom.width" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.bottom.width',
			],
			"{$attr_name}__styles.left.width"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.left.width',
			],
			"{$attr_name}__styles.all.color"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.all.color',
			],
			"{$attr_name}__styles.top.color"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.top.color',
			],
			"{$attr_name}__styles.right.color"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.right.color',
			],
			"{$attr_name}__styles.bottom.color" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.bottom.color',
			],
			"{$attr_name}__styles.left.color"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.left.color',
			],
			"{$attr_name}__styles.all.style"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.all.style',
			],
			"{$attr_name}__styles.top.style"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.top.style',
			],
			"{$attr_name}__styles.right.style"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.right.style',
			],
			"{$attr_name}__styles.bottom.style" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.bottom.style',
			],
			"{$attr_name}__styles.left.style"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'styles.left.style',
			],
		];
	}
}
