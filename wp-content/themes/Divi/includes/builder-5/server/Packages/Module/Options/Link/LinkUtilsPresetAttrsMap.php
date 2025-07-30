<?php
/**
 * Module: LinkUtilsPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Link;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * LinkUtilsPresetAttrsMap class.
 *
 * This class provides the static map for the link group preset attributes.
 *
 * @since ??
 */
class LinkUtilsPresetAttrsMap {
	/**
	 * Get the map for the link group preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the link group preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__url"    => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'url',
			],
			"{$attr_name}__target" => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'target',
			],
		];
	}
}
