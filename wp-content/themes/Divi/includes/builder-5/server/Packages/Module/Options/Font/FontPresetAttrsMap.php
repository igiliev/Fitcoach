<?php
/**
 * Module: FontPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Font;

use ET\Builder\Packages\Module\Options\TextShadow\TextShadowPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FontPresetAttrsMap class.
 *
 * This class provides static map for the text shadow preset attributes.
 *
 * @since ??
 */
class FontPresetAttrsMap {
	/**
	 * Get the map for the text shadow preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 * @param array  $args      The arguments.
	 *
	 * @return array The map for the text shadow preset attributes.
	 */
	public static function get_map( string $attr_name, array $args = [] ) {
		$default_args = [
			'has_list'          => false,
			'has_border'        => false,
			'has_heading_level' => false,
		];

		$args = array_merge( $default_args, $args );

		$font_attrs_map = [
			"{$attr_name}.font__family"        => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'family',
			],
			"{$attr_name}.font__weight"        => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'weight',
			],
			"{$attr_name}.font__style"         => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'style',
			],
			"{$attr_name}.font__lineColor"     => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'lineColor',
			],
			"{$attr_name}.font__lineStyle"     => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'lineStyle',
			],
			"{$attr_name}.font__textAlign"     => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'textAlign',
			],
			"{$attr_name}.font__color"         => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'color',
			],
			"{$attr_name}.font__size"          => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'size',
			],
			"{$attr_name}.font__letterSpacing" => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'letterSpacing',
			],
			"{$attr_name}.font__lineHeight"    => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'style' ],
				'subName'  => 'lineHeight',
			],
		];

		$heading_level_attrs_map = $args['has_heading_level'] ? [
			"{$attr_name}.font__headingLevel" => [
				'attrName' => "{$attr_name}.font",
				'preset'   => [ 'html' ],
				'subName'  => 'headingLevel',
			],
		] : [];

		$list_font_attrs_map = $args['has_list'] ? [
			"{$attr_name}.list__type"       => [
				'attrName' => "{$attr_name}.list",
				'preset'   => [ 'style' ],
				'subName'  => 'type',
			],
			"{$attr_name}.list__position"   => [
				'attrName' => "{$attr_name}.list",
				'preset'   => [ 'style' ],
				'subName'  => 'position',
			],
			"{$attr_name}.list__itemIndent" => [
				'attrName' => "{$attr_name}.list",
				'preset'   => [ 'style' ],
				'subName'  => 'itemIndent',
			],
		] : [];

		$border_font_attrs_map = $args['has_border'] ? [
			"{$attr_name}.border__styles.left.width" => [
				'attrName' => "{$attr_name}.border",
				'preset'   => [ 'style' ],
				'subName'  => 'styles.left.width',
			],
			"{$attr_name}.border__styles.left.color" => [
				'attrName' => "{$attr_name}.border",
				'preset'   => [ 'style' ],
				'subName'  => 'styles.left.color',
			],
		] : [];

		$text_shadow_attrs_map = TextShadowPresetAttrsMap::get_map( "{$attr_name}.textShadow" );

		return array_merge( $font_attrs_map, $heading_level_attrs_map, $text_shadow_attrs_map, $list_font_attrs_map, $border_font_attrs_map );
	}
}
