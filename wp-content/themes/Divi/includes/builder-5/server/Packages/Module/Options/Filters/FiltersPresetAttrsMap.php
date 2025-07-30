<?php
/**
 * Module: FiltersPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Filters;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FiltersPresetAttrsMap class.
 *
 * This class provides static map for the filters preset attributes.
 *
 * @since ??
 */
class FiltersPresetAttrsMap {
	/**
	 * Get the map for the filters preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the filters preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__hueRotate"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'hueRotate',
			],
			"{$attr_name}__saturate"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'saturate',
			],
			"{$attr_name}__brightness" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'brightness',
			],
			"{$attr_name}__contrast"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'contrast',
			],
			"{$attr_name}__invert"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'invert',
			],
			"{$attr_name}__sepia"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'sepia',
			],
			"{$attr_name}__opacity"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'opacity',
			],
			"{$attr_name}__blur"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'blur',
			],
			"{$attr_name}__blendMode"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'blendMode',
			],
		];
	}
}
