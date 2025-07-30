<?php
/**
 * Module: ConditionsPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ConditionsPresetAttrsMap class.
 *
 * This class provides the static map for the conditions preset attributes.
 *
 * @since ??
 */
class ConditionsPresetAttrsMap {
	/**
	 * Get the map for the conditions preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the conditions preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			$attr_name => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
			],
		];
	}
}
