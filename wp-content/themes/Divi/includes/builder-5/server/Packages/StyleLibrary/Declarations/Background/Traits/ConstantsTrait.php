<?php
/**
 * Background::$background_default_attr
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Background\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait ConstantsTrait {

	/**
	 * Background style declaration default attributes.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/background-default-attr backgroundDefaultAttr} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @var array $background_default_attr
	 */
	public static $background_default_attr = [
		'color'    => '',
		'gradient' => [
			'enabled'         => 'off',
			'stops'           => [
				[
					'position' => '0',
					'color'    => '#2B87DA',
				],
				[
					'position' => '100',
					'color'    => '#29C4A9',
				],
			],
			'length'          => '100%',
			'type'            => 'linear',
			'direction'       => '180deg',
			'directionRadial' => 'center',
			'overlaysImage'   => 'off',
		],
		'image'    => [
			'url'              => '',
			'parallax'         => [
				'enabled' => 'off',
				'method'  => 'on',
			],
			'size'             => 'cover',
			'width'            => 'auto',
			'height'           => 'auto',
			'position'         => 'center',
			'horizontalOffset' => '0',
			'verticalOffset'   => '0',
			'repeat'           => 'no-repeat',
			'blend'            => 'normal',
		],
		'video'    => [
			'mp4'                  => '',
			'webm'                 => '',
			'width'                => '',
			'height'               => '',
			'allowPlayerPause'     => 'off',
			'pauseOutsideViewport' => 'on',
		],
		'pattern'  => [
			'enabled'          => 'off',
			'style'            => 'polka-dots',
			'color'            => 'rgba(0,0,0,0.2)',
			'transform'        => [],
			'size'             => 'initial',
			'width'            => 'auto',
			'height'           => 'auto',
			'repeatOrigin'     => 'left top',
			'horizontalOffset' => '0',
			'verticalOffset'   => '0',
			'repeat'           => 'repeat',
			'blend'            => 'normal',
		],
		'mask'     => [
			'enabled'          => 'off',
			'style'            => 'layer-blob',
			'color'            => '#ffffff',
			'transform'        => [],
			'aspectRatio'      => 'landscape',
			'size'             => 'stretch',
			'width'            => 'auto',
			'height'           => 'auto',
			'position'         => 'center',
			'horizontalOffset' => '0',
			'verticalOffset'   => '0',
			'blend'            => 'normal',
		],
	];

}
