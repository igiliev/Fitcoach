<?php
/**
 * Icon class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Icon is a helper class for working with Icon style declaration.
 *
 * @since ??
 */
class Icon {

	/**
	 * Get Icon's CSS declaration based on given attrValue.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/icon-style-declaration IconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *                                  Note if `icon` key is not set, an empty string is returned.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		if ( ! isset( $args['attrValue'] ) ) {
			return '';
		}

		$attr_value  = $args['attrValue'];
		$return_type = $args['returnType'] ?? 'string';
		$color       = isset( $attr_value['color'] ) ? $attr_value['color'] : null;
		$unicode     = isset( $attr_value['unicode'] ) ? $attr_value['unicode'] : [];
		$weight      = isset( $attr_value['weight'] ) ? $attr_value['weight'] : null;
		$size        = isset( $attr_value['size'] ) ? $attr_value['size'] : null;
		$use_size    = isset( $attr_value['useSize'] ) ? $attr_value['useSize'] : null;
		$important   = $args['important'] ?? false;

		$style_declarations = new StyleDeclarations(
			[
				'important'  => is_bool( $important ) ?
				[
					'font-size'   => $important,
					'font-family' => $important,
					'font-weight' => $important,
					'line-height' => $important,
					'content'     => $important,
					'color'       => $important,
					'margin-top'  => $important,
					'margin-left' => $important,
				] : $important,
				'returnType' => $return_type,
			]
		);

		$font_icon = Utils::process_font_icon( $attr_value ?? [], false, true );
		if ( $font_icon ) {
			$font_family = Utils::is_fa_icon( $attr_value ?? [] ) ? 'FontAwesome' : 'ETmodules';

			// Icon Font Family.
			$style_declarations->add( 'font-family', "\"{$font_family}\"" );

			// Icon Content.
			if ( ! empty( $unicode ) ) {
				$font_icon = Utils::escape_font_icon( $font_icon );

				$style_declarations->add( 'content', "'" . $font_icon . "'" );
			}

			// Icon Weight.
			if ( ! empty( $weight ) ) {
				$style_declarations->add( 'font-weight', $weight );
			}
		}

		// Icon Color.
		if ( ! empty( $color ) ) {
			$style_declarations->add( 'color', $color );
		}

		// Icon Font Size.
		if ( 'on' === $use_size && ! empty( $size ) ) {
			$style_declarations->add( 'font-size', $size );
			$style_declarations->add( 'line-height', $size );
		}

		return $style_declarations->value();

	}
}
