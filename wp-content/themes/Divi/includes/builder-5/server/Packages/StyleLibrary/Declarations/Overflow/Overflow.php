<?php
/**
 * Overflow class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Overflow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Overflow class.
 *
 * This class represents and provides the necessary functionality for the Overflow feature in a CSS style declaration.
 *
 * @since ??
 */
class Overflow {

	/**
	 * Generate CSS declaration for overflow style based on the given arguments.
	 *
	 * This function generates the style declaration for `overflow-x` and `overflow-y` properties based on the given arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/overflow-style-declaration/ overflowStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for generating the style declaration.
	 *
	 *     @type string     $attrValue   The attribute value for `overflow-x` and `overflow-y`.
	 *     @type bool|array $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string     $returnType  Optional. The desired return type of the style declaration. Default `string`.
	 *                                   One of `string`, or `key_value_pair`
	 *                                     - If `string`, the style declaration will be returned as a string.
	 *                                     - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return string|array The generated style declaration based on the provided arguments.
	 *
	 * @example:
	 * ```php
	 *     // Example usage of the style_declaration() function:
	 *     $args = [
	 *         'attrValue'   => [
	 *             'x' => 'hidden',
	 *             'y' => 'visible',
	 *         ],
	 *         'important'   => true,
	 *         'returnType'  => 'array',
	 *     ];
	 *
	 *     $style_declaration = Overflow::style_declaration( $args );
	 *
	 *     // The resulting style declaration will be:
	 *     // [
	 *     //    [overflow-x] => hidden !important
	 *     //    [overflow-y] => visible !important
	 *     //]
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

		$attr_value      = $args['attrValue'];
		$important       = $args['important'];
		$return_type     = $args['returnType'];
		$overflow_values = [ 'visible', 'scroll', 'hidden', 'auto' ];
		$attr_value_x    = isset( $attr_value['x'] ) ? $attr_value['x'] : null;
		$attr_value_y    = isset( $attr_value['y'] ) ? $attr_value['y'] : null;

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( null !== $attr_value_x && in_array( $attr_value_x, $overflow_values, true ) ) {
			$style_declarations->add( 'overflow-x', $attr_value_x );
		}

		if ( null !== $attr_value_y && in_array( $attr_value_y, $overflow_values, true ) ) {
			$style_declarations->add( 'overflow-y', $attr_value_y );
		}

		return $style_declarations->value();

	}
}
