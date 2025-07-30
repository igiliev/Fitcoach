<?php
/**
 * Declarations::transition_style_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transition;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait TransitionStyleDeclarationTrait {
	use TransitionTraits\StyleDeclarationTrait;

	/**
	 * Get transition CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This is a wrapper function for `Transition::style_declaration()`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/transition-style-declaration/ transitionStyleDeclaration}
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
	 */
	public static function transition_style_declaration( array $args ) {
		return self::style_declaration( $args );
	}

}
