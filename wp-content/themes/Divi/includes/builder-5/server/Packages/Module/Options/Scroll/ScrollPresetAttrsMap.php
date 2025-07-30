<?php
/**
 * Module: ScrollPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Scroll;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ScrollPresetAttrsMap class.
 *
 * This class provides the static map for the scroll preset attributes.
 *
 * @since ??
 */
class ScrollPresetAttrsMap {
	/**
	 * Get the map for the scroll preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the scroll preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			// "{$attr_name}__gridMotion.enable"       => [
			// 	'attrName' => $attr_name,
			// 	'preset'   => [ 'script' ],
			// 	'subName'  => 'gridMotion.enable',
			// ],
			"{$attr_name}__verticalMotion.enable"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'verticalMotion.enable',
			],
			"{$attr_name}__verticalMotion"          => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'verticalMotion',
			],
			"{$attr_name}__horizontalMotion.enable" => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'horizontalMotion.enable',
			],
			"{$attr_name}__horizontalMotion"        => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'horizontalMotion',
			],
			"{$attr_name}__fade.enable"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'fade.enable',
			],
			"{$attr_name}__fade"                    => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'fade',
			],
			"{$attr_name}__scaling.enable"          => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'scaling.enable',
			],
			"{$attr_name}__scaling"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'scaling',
			],
			"{$attr_name}__rotating.enable"         => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'rotating.enable',
			],
			"{$attr_name}__rotating"                => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'rotating',
			],
			"{$attr_name}__blur.enable"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'blur.enable',
			],
			"{$attr_name}__blur"                    => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'blur',
			],
			"{$attr_name}__motionTriggerStart"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'script' ],
				'subName'  => 'motionTriggerStart',
			],
		];
	}
}
