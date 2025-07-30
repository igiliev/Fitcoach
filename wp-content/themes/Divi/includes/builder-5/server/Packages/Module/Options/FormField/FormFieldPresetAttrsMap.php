<?php
/**
 * Module: FormFieldPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FormField;

use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Font\FontPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Spacing\SpacingPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Border\BorderPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FormFieldPresetAttrsMap class.
 *
 * This class provides the static map for the field preset attributes.
 *
 * @since ??
 */
class FormFieldPresetAttrsMap {
	/**
	 * Get the map for the field preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the field preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		$result = [];

		$background_color = [
			"{$attr_name}.decoration.background__color" => [
				'attrName' => "{$attr_name}.decoration.background",
				'subName'  => 'color',
				'preset'   => [ 'style' ],
			],
		];

		$focus_background_color = [
			"{$attr_name}.advanced.focus.background__color" => [
				'attrName' => "{$attr_name}.advanced.focus.background",
				'subName'  => 'color',
				'preset'   => [ 'style' ],
			],
		];

		$placeholder_font_group = FontPresetAttrsMap::get_map( "{$attr_name}.advanced.placeholder.font" );
		$focus_font_group       = FontPresetAttrsMap::get_map( "{$attr_name}.advanced.focus.font" );
		$font_top_group         = FontPresetAttrsMap::get_map( "{$attr_name}.decoration.font" );
		$spacing_group          = SpacingPresetAttrsMap::get_map( "{$attr_name}.decoration.spacing" );
		$box_shadow_group       = BoxShadowPresetAttrsMap::get_map( "{$attr_name}.decoration.boxShadow" );
		$border_group           = BorderPresetAttrsMap::get_map( "{$attr_name}.decoration.border" );

		$use_focus_border = [
			"{$attr_name}.advanced.focusUseBorder" => [
				'attrName' => "{$attr_name}.advanced.focusUseBorder",
				'preset'   => [ 'style' ],
			],
		];

		$focus_border_group = BorderPresetAttrsMap::get_map( "{$attr_name}.advanced.focus.border" );

		$result = array_merge(
			[],
			$background_color,
			$focus_background_color,
			$placeholder_font_group,
			$focus_font_group,
			$font_top_group,
			$spacing_group,
			$box_shadow_group,
			$border_group,
			$use_focus_border,
			$focus_border_group
		);

		return $result;
	}
}
