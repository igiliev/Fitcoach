<?php
/**
 * Module Library: Image Module Spacing Style Declaration Trait
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Image\Styles\Spacing\SpacingStyleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait StyleDeclarationTrait {

	/**
	 * Get Spacing's CSS declaration based on given attrValue.
	 *
	 * @since ??
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return string The string containing the CSS style declarations for the spacing.
	 *
	 * @example: Get the spacing style declarations.
	 * ```php
	 * $params = [
	 *   'attrValue' => [
	 *     'margin' => [
	 *       'top' => '10px',
	 *       'right' => '20px',
	 *       'bottom' => '30px',
	 *       'left' => '40px',
	 *     ],
	 *     'padding' => [
	 *       'top' => '10px',
	 *       'right' => '20px',
	 *       'bottom' => '30px',
	 *       'left' => '40px',
	 *     ],
	 *   ],
	 *   'important' => false,
	 *   'returnType' => 'string',
	 * ];
	 *
	 * $style_declarations = ImageModule::style_declaration( $params );
	 *
	 * // Result: 'margin-top: 10px; margin-right: 20px; margin-bottom: 30px; margin-left: 40px; padding-top: 10px; padding-right: 20px; padding-bottom: 30px; padding-left: 40px;'
	 * ```
	 */
	public static function style_declaration( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value        = $args['attrValue'];
		$important         = $args['important'];
		$return_type       = $args['returnType'];
		$sides             = [ 'top', 'right', 'bottom', 'left' ];
		$margin            = $attr_value['margin'] ?? [];
		$padding           = $attr_value['padding'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( $margin ) {
			foreach ( $sides as $side ) {
				if ( isset( $margin[ $side ] ) ) {
					$style_declarations->add( 'margin-' . $side, $margin[ $side ] );
				}
			}
		}

		if ( $padding ) {
			foreach ( $sides as $side ) {
				if ( isset( $padding[ $side ] ) ) {
					$style_declarations->add( 'padding-' . $side, $padding[ $side ] );
				}
			}
		}

		return $style_declarations->value();

	}

}
