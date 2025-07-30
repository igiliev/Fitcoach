<?php
/**
 * Module: AnimationPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Animation;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * AnimationPresetAttrsMap class.
 *
 * This class provides the static map for the animation preset attributes.
 *
 * @since ??
 */
class AnimationPresetAttrsMap {
	/**
	 * Get the map for the animation preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the animation preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__style"           => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'style',
			],
			"{$attr_name}__direction"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'direction',
			],
			"{$attr_name}__duration"        => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'duration',
			],
			"{$attr_name}__delay"           => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'delay',
			],
			"{$attr_name}__intensity.slide" => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'intensity.slide',
			],
			"{$attr_name}__intensity.zoom"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'intensity.zoom',
			],
			"{$attr_name}__intensity.flip"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'intensity.flip',
			],
			"{$attr_name}__intensity.fold"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'intensity.fold',
			],
			"{$attr_name}__intensity.roll"  => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'intensity.roll',
			],
			"{$attr_name}__startingOpacity" => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'startingOpacity',
			],
			"{$attr_name}__speedCurve"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'speedCurve',
			],
			"{$attr_name}__repeat"          => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'repeat',
			],
		];
	}
}
