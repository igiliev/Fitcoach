<?php
/**
 * Position class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Position;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Position class.
 *
 * This class provides functionality for working with CSS position.
 *
 * @since ??
 */
class Position {

	/**
	 * Generate position CSS style declarations based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/position-style-declaration/ positionStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for generating style declarations.
	 *
	 *     @type string $attrValue         The value (`breakpoint > state > value`) of module attribute.
	 *     @type bool   $important         Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string $returnType        Optional. The return type of the style declaration. Default `string`.
	 *                                     One of `string`, or `key_value_pair`
	 *                                       - If `string`, the style declaration will be returned as a string.
	 *                                       - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 *     @type array  $defaultAttrValue  {
	 *         An array defining the default attribute values.
	 *
	 *         @type string    $mode     The default mode value. One of `default`, `relative`, `absolute`, or `fixed`. Default 'default'.
	 *         @type array     $offset   {
	 *             The default offset values.
	 *
	 *             @type string $horizontal The default horizontal offset value. Default `0px`.
	 *             @type string $vertical   The default vertical offset value. Default `0px`.
	 *         }
	 *     }
	 * }
	 *
	 * @return array|string The generated position style declarations.
	 *
	 * @example:
	 * ```php
	 * // Generate style declarations with default arguments.
	 * $args = [
	 *     'attrValue'        => 'value',
	 *     'important'        => false,
	 *     'returnType'       => 'string',
	 *     'defaultAttrValue' => [
	 *         'mode'   => 'default',
	 *         'offset' => ['horizontal' => '0px', 'vertical' => '0px'],
	 *     ],
	 * ];
	 *
	 * $styleDeclarations = Position::style_declaration($args);
	 * ```
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'        => false,
				'returnType'       => 'string',
				'defaultAttrValue' => [
					'mode'   => 'default',
					'offset' => [
						'horizontal' => '0px',
						'vertical'   => '0px',
					],
				],
			]
		);

		$attr_value         = $args['attrValue'];
		$default_attr_value = $args['defaultAttrValue'];
		$important          = $args['important'];
		$return_type        = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		$mode               = $attr_value['mode'] ?? '';
		$mode_checking_only = $attr_value['mode'] ?? $default_attr_value['mode'] ?? 'relative';
		$origin             = $attr_value['origin'] ?? $default_attr_value['origin'] ?? [];
		$offset             = $attr_value['offset'] ?? $default_attr_value['offset'] ?? [
			'horizontal' => '0px',
			'vertical'   => '0px',
		];
		$valid_positions    = [ 'relative', 'absolute', 'fixed' ];
		$opposite_sides     = [
			'left'   => 'right',
			'right'  => 'left',
			'top'    => 'bottom',
			'bottom' => 'top',
		];

		if ( in_array( $mode_checking_only, $valid_positions, true ) ) {
			$origin_position = isset( $origin[ $mode ] ) ? $origin[ $mode ] : 'top left';
			$origin_array    = explode( ' ', $origin_position );
			$vertical        = isset( $origin_array[0] ) ? $origin_array[0] : 'center';
			$horizontal      = isset( $origin_array[1] ) ? $origin_array[1] : 'center';

			// Relative positioning declarations.
			if ( 'relative' === $mode_checking_only ) {
				// Set positions.
				if ( 'relative' === $mode ) {
					$style_declarations->add( 'position', $mode );
				}

				// Set selected sides' value.
				$relative_vertical   = isset( $offset['vertical'] ) ? $offset['vertical'] : '0px';
				$relative_horizontal = isset( $offset['horizontal'] ) ? $offset['horizontal'] : '0px';

				if ( 'center' !== $vertical ) {
					$style_declarations->add( $vertical, $relative_vertical );
				}

				if ( 'center' !== $horizontal ) {
					$style_declarations->add( $horizontal, $relative_horizontal );
				}

				// Set the vertical opposite sides' value.
				if ( isset( $opposite_sides[ $vertical ] ) ) {
					$style_declarations->add( $opposite_sides[ $vertical ], 'auto' );
				}

				// Set the horizontal opposite sides' value.
				if ( isset( $opposite_sides[ $horizontal ] ) ) {
					$style_declarations->add( $opposite_sides[ $horizontal ], 'auto' );
				}
			}

			// Absolute / Fixed positioning declarations.
			if ( 'absolute' === $mode_checking_only || 'fixed' === $mode_checking_only ) {
				$is_vertically_centered   = 'center' === $vertical;
				$is_horizontally_centered = 'center' === $horizontal;
				$actual_vertical          = $is_vertically_centered ? 'top' : $vertical;
				$actual_horizontal        = $is_horizontally_centered ? 'left' : $horizontal;
				$vertical_value           = $is_vertically_centered ? '50%' : ( isset( $offset['vertical'] ) ? $offset['vertical'] : '0px' );
				$horizontal_value         = $is_horizontally_centered ? '50%' : ( isset( $offset['horizontal'] ) ? $offset['horizontal'] : '0px' );

				// Set positions.
				if ( ! empty( $mode ) ) {
					$position_mode = $mode;

					if ( 'absolute' === $mode_checking_only ) {
						$is_important = is_array( $important )
						? ( isset( $important['position'] ) ? $important['position'] : false )
						: $important;

						// we only want to add !important to the position property
						// if its not already applied via the `important` prop.
						$position_mode = $mode . ( $is_important ? '' : ' !important' );
					}

					$style_declarations->add( 'position', $position_mode );
				}

				// Set selected sides' value.
				$style_declarations->add( $actual_vertical, $vertical_value );
				$style_declarations->add( $actual_horizontal, $horizontal_value );

				// Set the vertical opposite sides' value.
				if ( isset( $opposite_sides[ $actual_vertical ] ) ) {
					$style_declarations->add( $opposite_sides[ $actual_vertical ], 'auto' );
				}

				// Set the horizontal opposite sides' value.
				if ( isset( $opposite_sides[ $actual_horizontal ] ) ) {
					$style_declarations->add( $opposite_sides[ $actual_horizontal ], 'auto' );
				}

				// Set translate.
				$position_translate = [];

				if ( $is_vertically_centered ) {
					$position_translate[] = 'translateY(-50%)';
				}

				if ( $is_horizontally_centered ) {
					$position_translate[] = 'translateX(-50%)';
				}

				if ( $is_vertically_centered || $is_horizontally_centered ) {
					$style_declarations->add( 'transform', implode( ' ', $position_translate ) );
				}
			}
		}

		return $style_declarations->value();

	}
}
