<?php
/**
 * Module: TransitionPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Transition;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TransitionPresetAttrsMap class.
 *
 * This class provides the static map for the transition preset attributes.
 *
 * @since ??
 */
class TransitionPresetAttrsMap {
	/**
	 * Get the map for the transition preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the transition preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__duration"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'duration',
			],
			"{$attr_name}__delay"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'delay',
			],
			"{$attr_name}__speedCurve" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'speedCurve',
			],
		];
	}
}
