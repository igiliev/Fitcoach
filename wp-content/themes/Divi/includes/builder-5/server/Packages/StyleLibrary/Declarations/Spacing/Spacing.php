<?php
/**
 * Spacing class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Spacing;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Spacing class.
 *
 * This class provides utility functions for working with spacing styles.
 *
 * @since ??
 */
class Spacing {

	/**
	 * Get spacing CSS declaration based on given arguments.
	 *
	 * This function generates style declarations based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/spacing-style-declaration/ spacingStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated CSS style declarations.
	 *
	 * @example:
	 * ```php
	 *     // Generate style declarations with default arguments.
	 *     $style_declarations = Spacing::style_declaration([
	 *         'important'  => false,
	 *         'returnType' => 'string',
	 *         'attrValue'  => [
	 *             'margin'  => [
	 *                 'top'    => '10px',
	 *                 'right'  => '20px',
	 *                 'bottom' => '10px',
	 *                 'left'   => '20px',
	 *             ],
	 *             'padding' => [
	 *                 'top'    => '5px',
	 *                 'right'  => '10px',
	 *                 'bottom' => '5px',
	 *                 'left'   => '10px',
	 *             ],
	 *         ],
	 *     ]);
	 *
	 *     echo $style_declarations;
	 *
	 *     // Output: 'margin-top: 10px; margin-right: 20px; margin-bottom: 10px; margin-left: 20px; padding-top: 5px; padding-right: 10px; padding-bottom: 5px; padding-left: 10px;'
	 * ```
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];
		$sides       = [ 'top', 'right', 'bottom', 'left' ];
		$margin      = isset( $attr_value['margin'] ) ? $attr_value['margin'] : [];
		$padding     = isset( $attr_value['padding'] ) ? $attr_value['padding'] : [];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( $margin ) {
			foreach ( $sides as $side ) {
				// empty() considers certain values like "0" (string), 0 (integer), false, and null as empty, which might not be what we intend.
				// This can lead to unintended results when we want "0" to be treated as a valid value.
				// Margin value requires string.
				// If there is any value other than string then it should not be applied.
				$margin_side = $margin[ $side ] ?? '';
				if ( '' !== $margin_side ) {
					$style_declarations->add( 'margin-' . $side, $margin_side );
				}
			}
		}

		if ( $padding ) {
			foreach ( $sides as $side ) {
				// empty() considers certain values like "0" (string), 0 (integer), false, and null as empty, which might not be what we intend.
				// This can lead to unintended results when we want "0" to be treated as a valid value.
				// Padding value requires string.
				// If there is any value other than string then it should not be applied.
				$padding_side = $padding[ $side ] ?? '';
				if ( '' !== $padding_side ) {
					$style_declarations->add( 'padding-' . $side, $padding_side );
				}
			}
		}

		return $style_declarations->value();

	}
}
