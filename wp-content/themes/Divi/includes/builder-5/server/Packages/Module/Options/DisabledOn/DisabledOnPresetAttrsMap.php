<?php
/**
 * Module: DisabledOnPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\DisabledOn;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * DisabledOnPresetAttrsMap class.
 *
 * This class provides the static map for the disabledOn preset attributes.
 *
 * @since ??
 */
class DisabledOnPresetAttrsMap {
	/**
	 * Get the map for the disabledOn preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the disabledOn preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			$attr_name => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
			],
		];
	}
}
