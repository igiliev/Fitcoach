<?php
/**
 * Module: AttributesPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Attributes;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * AttributesPresetAttrsMap class.
 *
 * This class provides static map for the attributes preset attributes.
 *
 * @since ??
 */
class AttributesPresetAttrsMap {
	/**
	 * Get the map for the attributes preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the attributes preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__rel" => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'rel',
			],
		];
	}
}
