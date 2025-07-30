<?php
/**
 * Module: TextShadowPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\TextShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TextShadowPresetAttrsMap class.
 *
 * This class provides static map for the text shadow preset attributes.
 *
 * @since ??
 */
class TextShadowPresetAttrsMap {
	/**
	 * Get the map for the text shadow preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the text shadow preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__style"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'style',
			],
			"{$attr_name}__horizontal" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'horizontal',
			],
			"{$attr_name}__vertical"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'vertical',
			],
			"{$attr_name}__blur"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'blur',
			],
			"{$attr_name}__color"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'color',
			],
		];
	}
}
