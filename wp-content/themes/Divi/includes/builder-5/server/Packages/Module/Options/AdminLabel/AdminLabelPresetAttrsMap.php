<?php
/**
 * Module: AdminLabelPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\AdminLabel;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * AdminLabelPresetAttrsMap class.
 *
 * This class provides the static map for the admin label preset attributes.
 *
 * @since ??
 */
class AdminLabelPresetAttrsMap {
	/**
	 * Get the map for the admin label preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the admin label preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			$attr_name => [
				'attrName' => $attr_name,
				'preset'   => 'meta',
			],
		];
	}
}
