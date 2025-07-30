<?php
/**
 * Transform::style_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transform\TransformTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait StyleDeclarationTrait {

	use ValueTrait;

	/**
	 * Get transform CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/transform-style-declaration/ transformStyleDeclaration}
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
	 * @return array|string The generated transform CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * // Example: Generating style declarations with custom transform and transform origin
	 * $args = [
	 *     'attrValue'   => ['origin' => ['x' => '25%', 'y' => '75%']],
	 *     'important'   => true,
	 *     'returnType'  => 'array',
	 * ];
	 * $styleDeclarations = Transform::style_declaration($args);
	 * // Output: [
	 * //     'transform'          => 'none',
	 * //     'transform-origin'   => '25% 75% !important',
	 * // ]
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

		$attr_value            = $args['attrValue'];
		$important             = $args['important'];
		$return_type           = $args['returnType'];
		$origin                = isset( $attr_value['origin'] ) ? $attr_value['origin'] : null;
		$style_declarations    = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);
		$transform_declaration = self::value( $attr_value );

		if ( $transform_declaration ) {
			$style_declarations->add( 'transform', $transform_declaration );
		}

		if ( $origin ) {
			$default_origin   = [
				'x' => '50%',
				'y' => '50%',
			];
			$transform_origin = array_merge( $default_origin, $origin );

			$style_declarations->add( 'transform-origin', "{$transform_origin['x']} {$transform_origin['y']}" );
		}

		return $style_declarations->value();

	}
}
