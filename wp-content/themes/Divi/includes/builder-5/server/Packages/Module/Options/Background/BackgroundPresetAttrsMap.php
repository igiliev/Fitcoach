<?php
/**
 * Module: BackgroundPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * BackgroundPresetAttrsMap class.
 *
 * This class provides static map for the background preset attributes.
 *
 * @since ??
 */
class BackgroundPresetAttrsMap {
	/**
	 * Get the map for the background preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the background preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__color"                      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'color',
			],
			"{$attr_name}__gradient.stops"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.stops',
			],
			"{$attr_name}__gradient.enabled"           => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.enabled',
			],
			"{$attr_name}__gradient.type"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.type',
			],
			"{$attr_name}__gradient.direction"         => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.direction',
			],
			"{$attr_name}__gradient.directionRadial"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.directionRadial',
			],
			"{$attr_name}__gradient.repeat"            => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.repeat',
			],
			"{$attr_name}__gradient.length"            => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.length',
			],
			"{$attr_name}__gradient.overlaysImage"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'gradient.overlaysImage',
			],
			"{$attr_name}__image.url"                  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'image.url',
			],
			"{$attr_name}__image.parallax.enabled"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html', 'script' ],
				'subName'  => 'image.parallax.enabled',
			],
			"{$attr_name}__image.parallax.method"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'image.parallax.method',
			],
			"{$attr_name}__image.size"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.size',
			],
			"{$attr_name}__image.width"                => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.width',
			],
			"{$attr_name}__image.height"               => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.height',
			],
			"{$attr_name}__image.position"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.position',
			],
			"{$attr_name}__image.horizontalOffset"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.horizontalOffset',
			],
			"{$attr_name}__image.verticalOffset"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.verticalOffset',
			],
			"{$attr_name}__image.repeat"               => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'image.repeat',
			],
			"{$attr_name}__image.blend"                => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'image.blend',
			],
			"{$attr_name}__video.mp4"                  => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'video.mp4',
			],
			"{$attr_name}__video.webm"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'video.webm',
			],
			"{$attr_name}__video.width"                => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'video.width',
			],
			"{$attr_name}__video.height"               => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'video.height',
			],
			"{$attr_name}__video.allowPlayerPause"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'video.allowPlayerPause',
			],
			"{$attr_name}__video.pauseOutsideViewport" => [
				'attrName' => $attr_name,
				'preset'   => [ 'html' ],
				'subName'  => 'video.pauseOutsideViewport',
			],
			"{$attr_name}__pattern.style"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'pattern.style',
			],
			"{$attr_name}__pattern.enabled"            => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'pattern.enabled',
			],
			"{$attr_name}__pattern.color"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'pattern.color',
			],
			"{$attr_name}__pattern.transform"          => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.transform',
			],
			"{$attr_name}__pattern.size"               => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.size',
			],
			"{$attr_name}__pattern.width"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.width',
			],
			"{$attr_name}__pattern.height"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.height',
			],
			"{$attr_name}__pattern.repeatOrigin"       => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.repeatOrigin',
			],
			"{$attr_name}__pattern.horizontalOffset"   => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.horizontalOffset',
			],
			"{$attr_name}__pattern.verticalOffset"     => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.verticalOffset',
			],
			"{$attr_name}__pattern.repeat"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.repeat',
			],
			"{$attr_name}__pattern.blend"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'pattern.blend',
			],
			"{$attr_name}__mask.style"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'mask.style',
			],
			"{$attr_name}__mask.enabled"               => [
				'attrName' => $attr_name,
				'preset'   => [ 'style', 'html' ],
				'subName'  => 'mask.enabled',
			],
			"{$attr_name}__mask.color"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.color',
			],
			"{$attr_name}__mask.transform"             => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.transform',
			],
			"{$attr_name}__mask.aspectRatio"           => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.aspectRatio',
			],
			"{$attr_name}__mask.size"                  => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.size',
			],
			"{$attr_name}__mask.width"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.width',
			],
			"{$attr_name}__mask.height"                => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.height',
			],
			"{$attr_name}__mask.position"              => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.position',
			],
			"{$attr_name}__mask.horizontalOffset"      => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.horizontalOffset',
			],
			"{$attr_name}__mask.verticalOffset"        => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.verticalOffset',
			],
			"{$attr_name}__mask.blend"                 => [
				'attrName' => $attr_name,
				'preset'   => [ 'style' ],
				'subName'  => 'mask.blend',
			],
		];
	}
}
