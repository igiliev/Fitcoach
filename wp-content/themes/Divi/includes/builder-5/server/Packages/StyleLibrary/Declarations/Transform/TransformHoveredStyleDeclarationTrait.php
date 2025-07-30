<?php
/**
 * Declarations::transform_style_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transform;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait TransformHoveredStyleDeclarationTrait {
	use TransformTraits\HoveredStyleDeclarationTrait;

	/**
	 * Get the Transform CSS declaration based on the given arguments.
	 *
	 * This is a wrapper function for `Transform::hovered_style_declaration()`.
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
	 *     $declaration = Declarations::hovered_style_declaration($args);
	 *     echo $declaration;
	 *
	 *     // Output: "transform: translateX(100px) translateY(50px); transform-origin: 25% 75%; transition: none !important;"
	 * ```
	 */
	public static function transform_hovered_style_declaration( array $args ) {
		return self::hovered_style_declaration( $args );
	}

}
