<?php
/**
 * Transform::hovered_style_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transform\TransformTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait HoveredStyleDeclarationTrait {

	/**
	 * Get the Transform CSS declaration based on the given arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/transform-hovered-style-declaration/ transformHoveredStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Whether to add `!important` to the CSS.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return string The CSS declaration string.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'attrValue' => [
	 *             'translate' => [
	 *                 'x' => '100px',
	 *                 'y' => '50px',
	 *             ],
	 *             'origin' => [
	 *                 'x' => '25%',
	 *                 'y' => '75%',
	 *             ],
	 *         ],
	 *         'important' => true,
	 *         'returnType' => 'key_value_pair',
	 *     ];
	 *     $declaration = Transform::hovered_style_declaration($args);
	 *     echo $declaration;
	 *
	 *     // Output: "transform: translateX(100px) translateY(50px); transform-origin: 25% 75%; transition: none !important;"
	 * ```
	 */
	public static function hovered_style_declaration( array $args ) {
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
		$translate             = isset( $attr_value['translate'] ) ? $attr_value['translate'] : [];
		$origin                = isset( $attr_value['origin'] ) ? $attr_value['origin'] : [];
		$style_declarations    = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);
		$transform_declaration = [];

		if ( $translate ) {
			if ( isset( $translate['x'] ) ) {
				$transform_declaration[] = 'translateX(' . $translate['x'] . ')';
			}

			if ( isset( $translate['y'] ) ) {
				$transform_declaration[] = 'translateY(' . $translate['y'] . ')';
			}

			if ( $transform_declaration ) {
				$style_declarations->add( 'transform', implode( ' ', $transform_declaration ) );
			}
		} else {
			$style_declarations->add( 'transform', 'none' );
		}

		if ( $origin ) {
			$default_origin   = [
				'x' => '50%',
				'y' => '50%',
			];
			$transform_origin = array_merge( $default_origin, $origin );

			$style_declarations->add( 'transform-origin', "{$transform_origin['x']} {$transform_origin['y']}" );
		}

		$style_declarations->add( 'transition', 'none' );

		return $style_declarations->value();

	}
}
