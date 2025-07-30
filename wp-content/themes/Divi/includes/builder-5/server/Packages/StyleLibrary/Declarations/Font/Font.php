<?php
/**
 * Font class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Font;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Font class.
 *
 * @since ??
 */
class Font {

	/**
	 * Get Font's CSS declaration based on given attrValue.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/font-style-declaration fontStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType Optional. This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
				'breakpoint' => 'desktop',
				'state'      => 'value',
			]
		);

		$attr        = $args['attr'];
		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];
		$breakpoint  = $args['breakpoint'];
		$state       = $args['state'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		$inherited_font_style = []; // Inherited font style from upper breakpoint.
		if ( 'tablet' === $breakpoint ) {
			$inherited_font_style = $attr['desktop'][ $state ]['style'] ?? [];
		} elseif ( 'phone' === $breakpoint ) {
			$inherited_font_style = $attr['tablet'][ $state ]['style'] ?? $attr['desktop'][ $state ]['style'] ?? [];
		}

		if ( isset( $attr_value['family'] ) && strtolower( $attr_value['family'] ) !== 'default' ) {
			/**
			 * Check if font family is a CSS variable.
			 * Test regex https://regex101.com/r/4cTjiQ/1.
			 */
			$regex           = '/var\(\s*(-{2,})([a-zA-Z0-9-_]+)\)/i';
			$is_css_variable = preg_match( $regex, $attr_value['family'] ) === 1;

			// The check has been done to avoid adding single quotes to CSS variable.
			if ( $is_css_variable ) {
				// Normalize CSS variable format to ensure consistent processing for both VB and FE.
				$font_family = preg_replace_callback(
					$regex,
					function( $matches ) {
						// Always use exactly two dashes for CSS variables.
						return 'var(--' . $matches[2] . ')';
					},
					$attr_value['family']
				);
			} else {
				$font_family = "'" . $attr_value['family'] . "'";
			}

			$style_declarations->add( 'font-family', $font_family );
		}

		if ( isset( $attr_value['weight'] ) ) {
			$style_declarations->add( 'font-weight', $attr_value['weight'] );
		}

		$font_style = isset( $attr_value['style'] ) ? $attr_value['style'] : null;

		if ( is_array( $font_style ) ) {
			if ( in_array( 'italic', $font_style, true ) ) {
				$style_declarations->add( 'font-style', 'italic' );
			} elseif ( in_array( 'italic', $inherited_font_style, true ) ) {
				$style_declarations->add( 'font-style', 'normal' );
			}

			if ( in_array( 'uppercase', $font_style, true ) ) {
				$style_declarations->add( 'text-transform', 'uppercase' );
			} elseif ( in_array( 'uppercase', $inherited_font_style, true ) ) {
				$style_declarations->add( 'text-transform', 'none' );
			}

			if ( in_array( 'capitalize', $font_style, true ) ) {
				$style_declarations->add( 'font-variant', 'small-caps' );
			} elseif ( in_array( 'capitalize', $inherited_font_style, true ) ) {
				$style_declarations->add( 'font-variant', 'normal' );
			}

			if ( in_array( 'underline', $font_style, true ) ) {
				$style_declarations->add( 'text-decoration-line', 'underline' );
			} elseif ( in_array( 'strikethrough', $font_style, true ) ) {
				$style_declarations->add( 'text-decoration-line', 'line-through' );
			} elseif ( in_array( 'underline', $inherited_font_style, true ) || in_array( 'strikethrough', $inherited_font_style, true ) ) {
				$style_declarations->add( 'text-decoration-line', 'none' );
			}
		}

		if ( isset( $attr_value['lineColor'] ) ) {
			$style_declarations->add( 'text-decoration-color', $attr_value['lineColor'] );
		}

		$line_style = isset( $attr_value['lineStyle'] ) ? $attr_value['lineStyle'] : 'solid';

		if ( is_array( $font_style ) && ( in_array( 'strikethrough', $font_style, true ) || in_array( 'underline', $font_style, true ) ) ) {
			$style_declarations->add( 'text-decoration-style', $line_style );
		}

		if ( isset( $attr_value['color'] ) ) {
			$style_declarations->add( 'color', $attr_value['color'] );
		}

		if ( isset( $attr_value['size'] ) ) {
			$style_declarations->add( 'font-size', $attr_value['size'] );
		}

		if ( isset( $attr_value['letterSpacing'] ) ) {
			$style_declarations->add( 'letter-spacing', $attr_value['letterSpacing'] );
		}

		if ( isset( $attr_value['lineHeight'] ) ) {
			$style_declarations->add( 'line-height', $attr_value['lineHeight'] );
		}

		if ( isset( $attr_value['textAlign'] ) ) {
			$style_declarations->add( 'text-align', $attr_value['textAlign'] );
		}

		return $style_declarations->value();
	}
}
