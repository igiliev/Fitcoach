<?php
/**
 * TextShadow class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\TextShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * TextShadow class.
 *
 * This class provides functionality to work with with text shadow styles.
 *
 * @since ??
 */
class TextShadow {

	/**
	 * Presets for text shadow effect.
	 *
	 * This trait provides preset values for text shadow effect.
	 * The presets array contains multiple presets, each represented as an associative array with the following keys:
	 * - horizontal: The horizontal offset of the effect in `em` units.
	 * - vertical: The vertical offset of the effect in `em` units.
	 * - blur: The blur radius of the effect in `em` units.
	 * - color: The color of the effect in `rgba` format.
	 *
	 * @since ??
	 *
	 * @var array $_presets The array of presets for the effect.
	 */
	protected static $_presets = [
		'preset1' => [
			'horizontal' => '0em',
			'vertical'   => '0.1em',
			'blur'       => '0.1em',
			'color'      => 'rgba(0,0,0,0.4)',
		],
		'preset2' => [
			'horizontal' => '0.08em',
			'vertical'   => '0.08em',
			'blur'       => '0.08em',
			'color'      => 'rgba(0,0,0,0.4)',
		],
		'preset3' => [
			'horizontal' => '0em',
			'vertical'   => '0em',
			'blur'       => '0.3em',
			'color'      => 'rgba(0,0,0,0.4)',
		],
		'preset4' => [
			'horizontal' => '0em',
			'vertical'   => '0.08em',
			'blur'       => '0em',
			'color'      => 'rgba(0,0,0,0.4)',
		],
		'preset5' => [
			'horizontal' => '0.08em',
			'vertical'   => '0.08em',
			'blur'       => '0em',
			'color'      => 'rgba(0,0,0,0.4)',
		],
	];

	/**
	 * Get Text Shadow CSS property value based on given attributes.
	 *
	 * This function retrieves the CSS property value for Text Shadow based on a given attribute value.
	 * Note: if no color is given, CSS' text-shadow will use element's `color` as text-shadow's color.
	 *
	 * @since ??
	 *
	 * @param array $attr_value {
	 *     The value (breakpoint > state > value) of the module attribute.
	 *
	 *     @type string $style      Optional. The style of the Text Shadow. Default `none`.
	 *     @type string $horizontal Optional. The horizontal offset of the Text Shadow.
	 *     @type string $vertical   Optional. The vertical offset of the Text Shadow.
	 *     @type string $blur       Optional. The blur radius of the Text Shadow.
	 *     @type string $color      Optional. The color of the Text Shadow.
	 * }
	 *
	 * @return string The computed Text Shadow CSS property value.
	 *
	 * @example:
	 * ```php
	 * TextShadow::value( [
	 *     'style' => 'solid',
	 *     'horizontal' => '2px',
	 *     'vertical' => '2px',
	 *     'blur' => '5px',
	 *     'color' => '#000000',
	 * ] );
	 * ```
	 */
	public static function value( array $attr_value ): string {
		$style  = $attr_value['style'] ?? 'none';
		$preset = isset( self::$_presets[ $style ] ) ? self::$_presets[ $style ] : array();

		if ( ! $style || 'none' === $preset || ! $preset ) {
			return '';
		}

		$text_shadow  = array_merge( $preset, $attr_value );
		$horizontal   = isset( $text_shadow['horizontal'] ) ? $text_shadow['horizontal'] : '';
		$vertical     = isset( $text_shadow['vertical'] ) ? $text_shadow['vertical'] : '';
		$blur         = isset( $text_shadow['blur'] ) ? $text_shadow['blur'] : '';
		$color        = isset( $text_shadow['color'] ) ? $text_shadow['color'] : '';
		$shadow_color = $color ? ' ' . $color : '';

		return $horizontal . ' ' . $vertical . ' ' . $blur . $shadow_color;

	}

	/**
	 * Get text-shadow CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/text-shadow-style-declaration/ textShadowStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Whether to add `!important` to the CSS.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated text-shadow CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue' => '#ff0000',
	 *     'important' => true,
	 *     'returnType' => 'string',
	 * ];
	 * $declaration = TextShadow::style_declaration( $args );
	 *
	 * // Result: "text-shadow: #ff0000 !important;"
	 * ```
	 */
	public static function style_declaration( array $args ) {
		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		$processed_value = self::value( $attr_value ?? [] );

		if ( $processed_value ) {
			$style_declarations->add( 'text-shadow', $processed_value );
		}

		return $style_declarations->value();

	}
}
