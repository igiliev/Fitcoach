<?php
/**
 * Module: ZIndexPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\ZIndex;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ZIndexPresetAttrsMap class.
 *
 * This class provides static map for the z-index preset attributes.
 *
 * @since ??
 */
class ZIndexPresetAttrsMap {
	/**
	 * Get the map for the z-index preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the z-index preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			$attr_name => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
			],
		];
	}
}
