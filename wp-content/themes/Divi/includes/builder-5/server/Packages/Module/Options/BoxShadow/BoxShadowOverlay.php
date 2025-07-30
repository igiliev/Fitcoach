<?php
/**
 * Module: BoxShadowOverlay class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\BoxShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowUtils;

/**
 * BoxShadowOverlay class.
 *
 * @since ??
 */
class BoxShadowOverlay {

	/**
	 * Render box shadow overlay component.
	 *
	 * This function returns an HTML `div` tag with the class `box-shadow-overlay`,
	 * which is used to create a box shadow overlay effect.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attr Optional. Additional attributes for the overlay element. Default empty array.
	 * }
	 *
	 * @return string The HTML markup for the box shadow overlay component.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'attr' => [
	 *         'data-foo'   => 'bar',
	 *         'data-color' => 'red',
	 *     ],
	 * ];
	 * $result = BoxShadowUtils::component( $args );
	 *
	 * // This example demonstrates how to use the `component()` function to render a box shadow overlay component.
	 * // The resulting markup will be a `div` tag with the class `box-shadow-overlay` and additional attributes.
	 * ```
	 *
	 * @output:
	 * ```php
	 * <div class="box-shadow-overlay" data-foo="bar" data-color="red"></div>
	 * ```
	 */
	public static function component( array $args ): string {
		$attr = $args['attr'] ?? [];

		if ( ! BoxShadowUtils::is_overlay_enabled( $attr ) ) {
			return '';
		}

		return HTMLUtility::render(
			[
				'tag'        => 'div',
				'attributes' => [
					'class' => 'box-shadow-overlay',
				],
			]
		);
	}
}
