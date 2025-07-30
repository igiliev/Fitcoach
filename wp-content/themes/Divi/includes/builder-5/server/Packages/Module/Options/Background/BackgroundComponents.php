<?php
/**
 * Module: BackgroundComponents class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Background\BackgroundComponentMask;
use ET\Builder\Packages\Module\Options\Background\BackgroundComponentParallax;
use ET\Builder\Packages\Module\Options\Background\BackgroundComponentPattern;
use ET\Builder\Packages\Module\Options\Background\BackgroundComponentVideo;


/**
 * BackgroundComponents class.
 *
 * This class provides a set of methods for working with background components.
 * It encapsulates common functionality related to background components.
 *
 * @since ??
 */
class BackgroundComponents {

	/**
	 * Retrieves child components based on the provided arguments.
	 *
	 * The function will return Video, Parallax, Mask, and Pattern, each added if it is enabled in the provided settings.
	 *
	 *  This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundComponent BackgroundComponent} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for retrieving child components.
	 *
	 *     @type array          $attr          Optional. The attributes for the component. Default `[]`.
	 *     @type string         $id            Optional. The unique identifier for the component. Default empty string.
	 *     @type array          $settings      Optional. The settings for the component. Default `[]`.
	 *     @type int            $storeInstance Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
	 * }
	 *
	 * @return string The child components generated based on the enabled settings.
	 *
	 * @example:
	 * ```php
	 * BackgroundComponents::component(
	 *     [
	 *         'attr' => [
	 *             'background-color' => '#000000',
	 *             'background-size'  => 'cover',
	 *         ],
	 *         'id' => 'my-component-1',
	 *         'settings' => [
	 *             'parallax' => true,
	 *             'video'    => false,
	 *             'pattern'  => true,
	 *             'mask'     => false,
	 *         ],
	 *         'storeInstance' => $store,
	 *     ]
	 * );
	 * ```
	 */
	public static function component( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'attr'          => [],
				'id'            => '',
				'settings'      => [],
				'storeInstance' => null,
			]
		);

		if ( ! $args['attr'] ) {
			return '';
		}

		$is_enabled = function( $value ) {
			return ! isset( $value ) || ! ! $value;
		};

		$children = '';

		if ( $is_enabled( $args['settings']['parallax'] ?? null ) ) {
			$children .= BackgroundComponentParallax::container(
				[
					'backgroundAttr' => $args['attr'],
					'moduleId'       => $args['id'],
					'storeInstance'  => $args['storeInstance'] ?? null,
				]
			);
		}

		if ( $is_enabled( $args['settings']['video'] ?? null ) ) {
			$children .= BackgroundComponentVideo::container(
				[
					'backgroundAttr' => $args['attr'],
					'moduleId'       => $args['id'],
				]
			);
		}

		if ( $is_enabled( $args['settings']['pattern'] ?? null ) ) {
			$children .= BackgroundComponentPattern::container(
				[
					'backgroundAttr' => $args['attr'],
					'moduleId'       => $args['id'],
					'storeInstance'  => $args['storeInstance'] ?? null,
				]
			);
		}

		if ( $is_enabled( $args['settings']['mask'] ?? null ) ) {
			$children .= BackgroundComponentMask::container(
				[
					'backgroundAttr' => $args['attr'],
					'moduleId'       => $args['id'],
					'storeInstance'  => $args['storeInstance'] ?? null,
				]
			);
		}

		return $children;
	}
}
