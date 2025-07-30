<?php
/**
 * Module: SpacingPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Spacing;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * SpacingPresetAttrsMap class.
 *
 * This class provides static map for the spacing preset attributes.
 *
 * @since ??
 */
class SpacingPresetAttrsMap {
	/**
	 * Get the map for the spacing preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the spacing preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__margin"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'margin',
			],
			"{$attr_name}__padding" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'padding',
			],
		];
	}
}
