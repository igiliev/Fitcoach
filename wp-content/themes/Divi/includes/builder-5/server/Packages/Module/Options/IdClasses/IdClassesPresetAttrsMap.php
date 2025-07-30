<?php
/**
 * Module: IdClassesPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\IdClasses;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * IdClassesPresetAttrsMap class.
 *
 * This class provides static map for the IdClasses preset attributes.
 *
 * @since ??
 */
class IdClassesPresetAttrsMap {
	/**
	 * Get the map for the IdClasses preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the IdClasses preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__id"    => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'id',
			],
			"{$attr_name}__class" => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'class',
			],
		];
	}
}
