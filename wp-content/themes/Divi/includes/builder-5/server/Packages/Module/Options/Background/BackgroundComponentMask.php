<?php
/**
 * Module: BackgroundComponentMask class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;

/**
 * BackgroundComponentMask class
 *
 * @since ??
 */
class BackgroundComponentMask {

	/**
	 * Component for rendering a mask background element.
	 *
	 * This function takes an array of arguments and returns a HTML `span` tag with specified class and attributes, used for masking a background.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundComponentMask BackgroundComponentMask} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $className Optional. Background mask CSS class name. Default empty string.
	 *     @type string $style     Optional. Background mask style. Default empty string.
	 *     @type string $enable    Optional. Whether the background mask enabled or not. One of `on` or `off`. Default `off`.
	 * }
	 *
	 * @return string The HTML markup for the mask background element.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'className' => 'custom-class',
	 *     'style'     => 'background-color: red;',
	 *     'enable'    => 'on',
	 * ];
	 * $result = BackgroundComponentMask::component( $args );
	 *
	 * // This example demonstrates how to use the `component()` function to render a mask background element.
	 * // if the 'enable' value is 'on' and 'style' value is not empty in the provided arguments.
	 * ```
	 *
	 * @output:
	 * ```php
	 * <span class="et_pb_background_mask custom-class"></span>
	 * ```
	 */
	public static function component( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'className' => '',
				'style'     => '',
				'enable'    => 'off',
			]
		);

		if ( 'on' === $args['enable'] && ! empty( $args['style'] ) ) {
			return HTMLUtility::render(
				[
					'tag'        => 'span',
					'attributes' => [
						'class' => HTMLUtility::classnames(
							[
								'et_pb_background_mask' => true,
								$args['className']      => ! empty( $args['className'] ),
							]
						),
					],
				]
			);
		}

		return '';
	}

	/**
	 * Container function that retrieves the background style and enable status based on the provided arguments.
	 *
	 * This function iterates through all the breakpoints and states in the provided `$args['backgroundAttr']` array
	 * and finds the first attribute with a non-empty `style` value and `enabled` set to `'on'`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $backgroundAttr The background attributes for different breakpoints and states.
	 * }
	 * @return mixed Returns the result of the `BackgroundComponentMask::component()` function.
	 *
	 * @example:
	 * ```php
	 *  $args = [
	 *      'backgroundAttr' => [
	 *          'desktop' => [
	 *              'normal' => [
	 *                  'mask' => [
	 *                      'style'   => 'background-color: red;',
	 *                      'enabled' => 'on',
	 *                  ],
	 *              ],
	 *          ],
	 *          'mobile'  => [
	 *              'hover' => [
	 *                  'mask' => [
	 *                      'style'   => 'background-color: blue;',
	 *                      'enabled' => 'on',
	 *                  ],
	 *              ],
	 *          ],
	 *      ],
	 *  ];
	 *  $result = BackgroundComponentMask::container( $args );
	 *
	 *  // This example demonstrates how to use the `container()` function to retrieve the background style and enable status.
	 *  // It uses an array with two breakpoints ('desktop' and 'mobile') and two states ('normal' and 'hover').
	 *  // The function will return the background style and enable status for the attribute with non-empty `style` value and `enabled` set to 'on'.
	 *  // In this case, the resulting style will be 'background-color: red;' and the enable status will be 'on'.
	 * ```
	 *
	 * @example:
	 * ```php
	 *  $args = [
	 *      'backgroundAttr' => [
	 *          'tablet' => [
	 *              'normal' => [
	 *                  'mask' => [
	 *                      'style'   => '',
	 *                      'enabled' => 'off',
	 *                  ],
	 *              ],
	 *          ],
	 *          'desktop' => [
	 *              'normal' => [
	 *                  'mask' => [
	 *                      'style'   => '',
	 *                      'enabled' => 'off',
	 *                  ],
	 *              ],
	 *          ],
	 *      ],
	 *  ];
	 *  $result = BackgroundComponentMask::container( $args );
	 *
	 *  // This example demonstrates how to use the `container()` function when all attributes have empty `style` values and `enabled` set to 'off'.
	 *  // In this case, the resulting style will be an empty string and the enable status will be 'off'.
	 * ```
	 */
	public static function container( array $args ): string {
		$attr   = $args['backgroundAttr'] ?? [];
		$style  = '';
		$enable = 'off';

		// The logic to determine the `$style` and `$enable` value in FE is a bit difference with the logic in VB.
		// In VB we can directly get the attribute values from current active `breakpoint` and `state`, but that is not the case in FE.
		// Hence we need to iterate all the breakpoints and the states to find the first attribute with value `style` is not empty and `enabled` is on.
		foreach ( $attr as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$attr_value = ModuleUtils::use_attr_value(
					[
						'attr'       => $attr,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$breakpoint_state_style  = $attr_value['mask']['style'] ?? Background::$background_default_attr['mask']['style'];
				$breakpoint_state_enable = $attr_value['mask']['enabled'] ?? Background::$background_default_attr['mask']['enabled'];

				if ( $breakpoint_state_style && 'on' === $breakpoint_state_enable ) {
					$style  = $breakpoint_state_style;
					$enable = $breakpoint_state_enable;
					break;
				}
			}

			if ( '' !== $style && 'on' === $enable ) {
				break;
			}
		}

		return self::component(
			array_merge(
				$args,
				[
					'style'  => $style,
					'enable' => $enable,
				]
			)
		);
	}

}
