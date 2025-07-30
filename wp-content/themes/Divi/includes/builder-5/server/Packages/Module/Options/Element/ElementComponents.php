<?php
/**
 * Module: ElementComponents class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Element;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Background\BackgroundComponents;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowComponents;

/**
 * ElementComponents class.
 *
 * This class is responsible for handling the components of an element.
 *
 * @since ??
 */
class ElementComponents {

	/**
	 * Component function for rendering a background element.
	 *
	 * This function takes an array of arguments and returns a string containing the rendered background element.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ElementClassnames ElementComponents} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrs         Optional. The attributes of the background element. Default `[]`.
	 *     @type string     $id            Optional. The ID of the background element. Default empty string.
	 *     @type bool|array $background    Optional. The background settings of the element. Default `null`.
	 *     @type bool|array $boxShadow     Optional. Whether to include a box shadow for the element. Default `false`.
	 *     @type int|null   $orderIndex    Optional. The order index of the element. Default `null`.
	 *     @type int        $storeInstance Optional. The ID of instance where this block stored in BlockParserStore.
	 *                                     Default `null`.
	 * }
	 * @return string The rendered background element.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrs'         => [
	 *         'attribute_1' => 'value_1',
	 *         'attribute_2' => 'value_2',
	 *     ],
	 *     'id'            => 'element_id',
	 *     'background'    => [
	 *         'settings' => [
	 *             'color' => 'red',
	 *         ],
	 *     ],
	 *     'boxShadow'     => true,
	 *     'orderIndex'    => 1,
	 *     'storeInstance' => 'store_instance',
	 * ];
	 * $result = ElementComponents::component( $args );
	 *
	 * // This example demonstrates how to use the `component()` function to render a background element with custom attributes, ID, background settings, box shadow, order index, and store instance.
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Example of rendering a background element with default values.
	 * $result = ElementComponents::component( [] );
	 * ```
	 */
	public static function component( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'attrs'         => [],
				'id'            => '',
				'background'    => null,
				'boxShadow'     => false,
				'orderIndex'    => null,
				'storeInstance' => null,
			]
		);

		if ( empty( $args['attrs'] ) ) {
			return '';
		}

		$attrs = $args['attrs'];

		$is_enabled = function( $value ) {
			return ! isset( $value ) || ! ! $value;
		};

		$children = '';

		if ( $is_enabled( $args['background'] ?? null ) ) {
			$children .= BackgroundComponents::component(
				[
					'attr'          => $attrs['background'] ?? [],
					'id'            => $args['id'],
					'settings'      => isset( $attrs['background']['settings'] ) && ! is_bool( $attrs['background'] ) ? $attrs['background']['settings'] : false,
					'storeInstance' => $args['storeInstance'] ?? null,
				]
			);
		}

		if ( $is_enabled( $args['boxShadow'] ?? null ) ) {
			$children .= BoxShadowComponents::component(
				[
					'attr'          => $attrs['boxShadow'] ?? [],
					'id'            => $args['id'],
					'settings'      => isset( $attrs['boxShadow']['settings'] ) && ! is_bool( $attrs['boxShadow'] ) ? $attrs['boxShadow']['settings'] : false,
					'storeInstance' => $args['storeInstance'] ?? null,
				]
			);
		}

		return $children;
	}

}
