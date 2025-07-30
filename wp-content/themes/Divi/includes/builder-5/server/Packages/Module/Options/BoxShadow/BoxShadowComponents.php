<?php
/**
 * Module: BoxShadowComponents class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\BoxShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowOverlay;

/**
 * BoxShadowComponents class.
 *
 * @since ??
 */
class BoxShadowComponents {

	/**
	 * BoxShadow components: Overlay.
	 *
	 * This function is equivalent of JS function BoxShadowComponent located in
	 * visual-builder/packages/module/src/options/box-shadow/components/component.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $attr           The box-shadow groups attribute.
	 *     @type string $id             The module ID.
	 *     @type array  $settings       The box-shadow settings.
	 *     @type int    $storeInstance  The ID of instance where this block stored in BlockParserStore class.
	 * }
	 *
	 * @return string
	 */
	/**
	 * Render box shadow component.
	 *
	 * This function takes an array of arguments and returns the rendered component.
	 *
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BoxShadowComponent BoxShadowComponent} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array        $attr            Optional. The attributes for the component. Default `[]`.
	 *     @type string       $id              Optional. The ID for the component. Default empty string.
	 *     @type array        $settings        Optional. The settings for the component. Default `[]`.
	 *     @type object       $storeInstance   Optional. The ID of instance where this block stored in BlockParserStore.
	 *                                         Default `null`.
	 * }
	 *
	 * @return string The rendered component.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'attr'          => [
	 *         'attribute1' => 'value1',
	 *         'attribute2' => 'value2',
	 *     ],
	 *     'id'            => 'component1',
	 *     'settings'      => [
	 *         'overlay' => true,
	 *     ],
	 *     'storeInstance' => $store_instance,
	 * ];
	 *
	 * $result = BoxShadowComponent::component( $args );
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

		if ( $is_enabled( $args['settings']['overlay'] ?? null ) ) {
			$children .= BoxShadowOverlay::component(
				[
					'attr'          => $args['attr'],
					'id'            => $args['id'],
					'storeInstance' => $args['storeInstance'] ?? null,
				]
			);
		}
		return $children;
	}
}
