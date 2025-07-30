<?php
/**
 * Module: DividersComponent class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Dividers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;

/**
 * DividersComponent class.
 *
 * This class provides functionality for managing a component with dividers.
 *
 * @since ??
 */
class DividersComponent {

	/**
	 * Container function for rendering a divider component.
	 *
	 * This function checks if a divider component should be rendered based on the provided arguments.
	 * If a valid style and placement are provided, the divider component is rendered.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for rendering the divider component.
	 *
	 *     @type array  $attr      Optional. Additional attributes for the divider component. Default `[]`.
	 *     @type string $placement Optional. Placement of the divider component. Default empty string.
	 * }
	 * @return string Rendered divider component HTML.
	 *                Empty string if no valid style or placement is provided.
	 *
	 * @example:
	 * ```php
	 * // Render a divider component with default options.
	 * echo DividersComponent::container();
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Render a divider component with custom style and placement.
	 * echo DividersComponent::container( [
	 *     'attr'      => [ 'color' => 'red' ],
	 *     'placement' => 'top',
	 * ] );
	 * ```
	 */
	public static function container( array $args ): string {
		$attr      = $args['attr'] ?? [];
		$placement = $args['placement'] ?? '';

		// Bail early if placement is not available.
		if ( empty( $placement ) ) {
			return '';
		}

		// If there is no style, then don't render the divider.
		if ( empty( $attr ) || ! DividersUtils::has_divider( [ $placement => $attr ] ) ) {
			return '';
		}

		$passed_args = [
			'placement' => $placement,
		];

		// If there is a style, then render the divider.
		return self::component( $passed_args );
	}

	/**
	 * Generates module element divider component.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. Array of arguments for generating the component.
	 *
	 *     @type string $className The class name for the component.
	 *                             Default is "et_pb_{$args['placement']}_inside_divider".
	 * }
	 *
	 * @return string The generated component HTML.
	 *
	 * @example:
	 * ``php
	 * // Generate a component with default class name.
	 * $args = [
	 *     'placement' => 'top', // Specify the placement of the component.
	 * ];
	 *
	 * $component = DividersComponent::component( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Generate a component with custom class name.
	 * $args = [
	 *     'placement'  => 'bottom', // Specify the placement of the component.
	 *     'className' => 'custom-class', // Specify a custom class name for the component.
	 * ];
	 * $component = DividersComponent::component( $args );
	 * ```
	 */
	public static function component( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'className' => "et_pb_{$args['placement']}_inside_divider",
			]
		);

		return HTMLUtility::render(
			[
				'tag'        => 'div',
				'attributes' => [
					'class' => HTMLUtility::classnames(
						[
							$args['className'] => ! empty( $args['className'] ),
						]
					),
				],
			]
		);
	}

}
