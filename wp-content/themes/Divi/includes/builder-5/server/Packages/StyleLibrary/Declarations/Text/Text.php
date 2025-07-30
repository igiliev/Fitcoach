<?php
/**
 * Text class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Text;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Text class.
 *
 * This class provides functionality to work with with text styles.
 *
 * @since ??
 */
class Text {

	/**
	 * Get text CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/text-style-declaration/ textStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated text CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue'   => ['orientation' => 'center'], // The attribute value.
	 *     'important'   => true,                        // Whether the declaration should be marked as important.
	 *     'returnType'  => 'key_value_pair',            // The return type of the style declaration.
	 * ];
	 * $style = Text::style_declaration( $args );
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

		$attr_value         = $args['attrValue'];
		$important          = $args['important'];
		$return_type        = $args['returnType'];
		$orientation        = $attr_value['orientation'] ?? null;
		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( $orientation ) {
			$style_declarations->add( 'text-align', $orientation );
		}

		return $style_declarations->value();

	}
}
