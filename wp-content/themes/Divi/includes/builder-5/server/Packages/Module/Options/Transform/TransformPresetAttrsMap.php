<?php
/**
 * Module: TransformPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Transform;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TransformPresetAttrsMap class.
 *
 * This class provides the static map for the transform preset attributes.
 *
 * @since ??
 */
class TransformPresetAttrsMap {
	/**
	 * Get the map for the transform preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the transform preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__scale"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'scale',
			],
			"{$attr_name}__translate" => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'translate',
			],
			"{$attr_name}__rotate"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'rotate',
			],
			"{$attr_name}__skew"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'skew',
			],
			"{$attr_name}__origin"    => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'origin',
			],
		];
	}
}
