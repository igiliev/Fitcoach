<?php
/**
 * OverlayIcon class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\OverlayIcon;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * OverlayIcon class.
 *
 * This class provides functionality for managing the style declaration of the overlay icon.
 *
 * @since ??
 */
class OverlayIcon {

	/**
	 * Generate overlay icon CSS declaration.
	 *
	 * This function takes an array of arguments and generates a style declaration string
	 * based on the provided arguments. The generated style declaration can be used
	 * to apply CSS styles to elements.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/overflow-icon-style-declaration/ overlayIconStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string      $attrValue   The attribute value.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated style declaration.
	 *
	 * @example:
	 * ```php
	 *   $args = [
	 *       'attrValue'   => 'value',
	 *       'important'   => true,
	 *       'returnType'  => 'array',
	 *   ];
	 *   $styleDeclaration = OverlayIcon::style_declaration( $args );
	 * ```
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'returnType' => 'string',
			]
		);

		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( isset( $attr_value['type'] ) ) {
			$font_family = Utils::is_fa_icon( $attr_value ) ? 'FontAwesome' : 'ETmodules';

			$style_declarations->add( 'font-family', '\'' . $font_family . '\'' );
		}

		if ( isset( $attr_value['weight'] ) ) {
			$style_declarations->add( 'font-weight', $attr_value['weight'] );
		}

		return $style_declarations->value();

	}
}
