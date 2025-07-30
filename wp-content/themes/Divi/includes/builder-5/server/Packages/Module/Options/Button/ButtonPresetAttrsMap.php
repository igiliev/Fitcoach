<?php
/**
 * Module: ButtonPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Button;

use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Font\FontPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Spacing\SpacingPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Border\BorderPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Background\BackgroundPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Attributes\AttributesPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ButtonPresetAttrsMap class.
 *
 * This class provides the static map for the button preset attributes.
 *
 * @since ??
 */
class ButtonPresetAttrsMap {
	/**
	 * Get the map for the button preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the button preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		$result = [];

		// Button text.
		$button_text = [
			"{$attr_name}.innerContent__text" => [
				'attrName' => "{$attr_name}.innerContent",
				'subName'  => 'text',
				'preset'   => 'content',
			],
		];

		// Button Link.
		$button_link = [
			"{$attr_name}.innerContent__linkUrl"    => [
				'attrName' => "{$attr_name}.innerContent",
				'subName'  => 'linkUrl',
				'preset'   => 'content',
			],
			"{$attr_name}.innerContent__linkTarget" => [
				'attrName' => "{$attr_name}.innerContent",
				'subName'  => 'linkTarget',
				'preset'   => 'content',
			],
		];

		// Button Rel.
		$button_rel = AttributesPresetAttrsMap::get_map( "{$attr_name}.innerContent" );

		// Button Group.
		$button_group = [
			"{$attr_name}.decoration.button__enable"       => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'enable',
				'preset'   => [ 'style' ],
			],
			"{$attr_name}.decoration.button__icon.enable"  => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'icon.enable',
				'preset'   => [ 'style' ],
			],
			"{$attr_name}.decoration.button__icon.settings" => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'icon.settings',
				'preset'   => [ 'html', 'style' ],
			],
			"{$attr_name}.decoration.button__icon.color"   => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'icon.color',
				'preset'   => [ 'style' ],
			],
			"{$attr_name}.decoration.button__icon.placement" => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'icon.placement',
				'preset'   => [ 'style' ],
			],
			"{$attr_name}.decoration.button__icon.onHover" => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'icon.onHover',
				'preset'   => [ 'style' ],
			],
			"{$attr_name}.decoration.button__alignment"    => [
				'attrName' => "{$attr_name}.decoration.button",
				'subName'  => 'alignment',
				'preset'   => [ 'style' ],
			],
		];

		$background_group = BackgroundPresetAttrsMap::get_map( "{$attr_name}.decoration.background" );

		$border_group = BorderPresetAttrsMap::get_map( "{$attr_name}.decoration.border" );

		$font_group = FontPresetAttrsMap::get_map( "{$attr_name}.decoration.font" );

		$spacing_group = SpacingPresetAttrsMap::get_map( "{$attr_name}.decoration.spacing" );

		$box_shadow_group = BoxShadowPresetAttrsMap::get_map( "{$attr_name}.decoration.boxShadow" );

		$result = array_merge(
			[],
			$button_text,
			$button_link,
			$button_rel,
			$button_group,
			$background_group,
			$border_group,
			$spacing_group,
			$box_shadow_group,
			$font_group
		);

		return $result;
	}
}
