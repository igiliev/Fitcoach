<?php
/**
 * Module: BackgroundComponentParallax class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Sticky\StickyUtils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;

/**
 * BackgroundComponentParallax class.
 *
 * @since ??
 */
class BackgroundComponentParallax {

	use BackgroundComponentParallaxTraits\ComponentTrait;

	/**
	 * Container of Parallax Backgrounds element that automatically checks for state enable status.
	 *
	 * Gets the background style and enable status based on the provided arguments.
	 *
	 * This function iterates through all the breakpoints and states in the provided `$args['backgroundAttr']` array
	 * and finds the first attribute with a non-empty `style` value and `enabled` set to `'on'`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundComponentParallaxContainer BackgroundComponentParallaxContainer} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $backgroundAttr  The background attributes for different breakpoints and states.
	 *     @type string $moduleId        The module ID.
	 *     @type int    $storeInstance   Optional. The ID of instance where this block stored in BlockParserStore.
	 *                                   Default `null`.
	 * }
	 *
	 * @return string The container component.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'backgroundAttr' => [
	 *         'desktop' => [
	 *             'normal' => [
	 *                 'mask' => [
	 *                     'style'   => 'background-color: red;',
	 *                     'enabled' => 'on',
	 *                 ],
	 *             ],
	 *         ],
	 *         'mobile'  => [
	 *             'hover' => [
	 *                 'mask' => [
	 *                     'style'   => 'background-color: blue;',
	 *                     'enabled' => 'on',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     'moduleId' => 'module1',
	 *     'storeInstance' => 1,
	 * ];
	 * $result = BackgroundComponentMask::container( $args );
	 *
	 * // This example demonstrates how to use the `container()` function to retrieve the background style and enable status.
	 * // It uses an array with two breakpoints ('desktop' and 'mobile') and two states ('normal' and 'hover').
	 * // The function will return the background style and enable status for the attribute with a non-empty `style` value and `enabled` set to 'on'.
	 * // In this case, the resulting style will be 'background-color: red;' and the enable status will be 'on'.
	 * ```
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'backgroundAttr' => [
	 *         'tablet' => [
	 *             'normal' => [
	 *                 'mask' => [
	 *                     'style'   => '',
	 *                     'enabled' => 'off',
	 *                 ],
	 *             ],
	 *         ],
	 *         'desktop' => [
	 *             'normal' => [
	 *                 'mask' => [
	 *                     'style'   => '',
	 *                     'enabled' => 'off',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     'moduleId' => 'module2',
	 *     'storeInstance' => 2,
	 * ];
	 * $result = BackgroundComponentMask::container( $args );
	 *
	 * // This example demonstrates how to use the `container()` function when all attributes have empty `style` values and `enabled` set to 'off'.
	 * // In this case, the resulting style will be an empty string and the enable status will be 'off'.
	 * ```
	 */
	public static function container( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'storeInstance' => null,
			]
		);

		$background_attr = $args['backgroundAttr'] ?? null;

		// Default enable value. By default value and hover is enabled but sticky is only enabled if
		// current module is sticky or inside sticky element.
		$enable = [
			'value'  => true,
			'hover'  => true,
			'sticky' => true,
		];

		// Only continue to check for sticky enable status if desktop sticky url exist.
		// @todo feat(D5, FE :: Options :: HTML Components) Update the check if responsive + sticky combination is possible.
		if ( $background_attr && isset( $background_attr['desktop']['sticky'] ) ) {
			$module_id      = $args['moduleId'] ?? null;
			$module_objects = BlockParserStore::get_all( $args['storeInstance'] );

			// Set sticky enable status if current module is sticky or inside sticky module.
			$enable['sticky'] = StickyUtils::is_sticky_module( $module_id, $module_objects, false ) || StickyUtils::is_inside_sticky_module( $module_id, $module_objects );
		}

		return self::component(
			wp_parse_args(
				$args,
				[
					'enable' => $enable,
				]
			)
		);
	}

}
