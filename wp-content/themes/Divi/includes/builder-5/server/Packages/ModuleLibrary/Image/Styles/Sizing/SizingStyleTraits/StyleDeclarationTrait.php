<?php
/**
 * Module Library: Image Module Sizing Style Declaration Trait
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Image\Styles\Sizing\SizingStyleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait StyleDeclarationTrait {

	/**
	 * Get width and alignment CSS declaration from Sizing style and based on given attrValue.
	 *
	 * @since ??
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return string The CSS declaration.
	 */
	public static function style_declaration( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value      = $args['attrValue'];
		$important       = $args['important'];
		$return_type     = $args['returnType'];
		$width           = $attr_value['width'] ?? null;
		$max_width       = $attr_value['maxWidth'] ?? null;
		$alignment       = $attr_value['alignment'] ?? null;
		$force_fullwidth = $attr_value['forceFullwidth'] ?? null;

		$always_important = [
			'margin-right' => true,
			'margin-left'  => true,
		];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important ? array_merge(
					$always_important,
					[
						'width'     => true,
						'max-width' => true,
					]
				) : $always_important,
				'returnType' => $return_type,
			]
		);

		// Only add alignment, width and max-width if forceFullwidth is not enabled.
		if ( 'on' !== $force_fullwidth ) {
			if ( $width ) {
				$style_declarations->add( 'width', $width );
			}

			if ( $max_width ) {
				$style_declarations->add( 'max-width', $max_width );
			}

			switch ( $alignment ) {
				case 'left':
					$style_declarations->add( 'margin-left', '0' );
					$style_declarations->add( 'margin-right', 'auto' );
					break;

				case 'center':
					$style_declarations->add( 'margin-left', 'auto' );
					$style_declarations->add( 'margin-right', 'auto' );
					break;

				case 'right':
					$style_declarations->add( 'margin-left', 'auto' );
					$style_declarations->add( 'margin-right', '0' );
					break;

				default:
					// Do nothing.
					break;
			}
		}

		return $style_declarations->value();
	}

	/**
	 * Get height CSS declaration from Sizing style and based on given attrValue.
	 *
	 * @since ??
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return string The CSS declaration.
	 */
	public static function height_style_declaration( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value      = $args['attrValue'];
		$important       = $args['important'];
		$return_type     = $args['returnType'];
		$min_height      = $attr_value['minHeight'] ?? null;
		$height          = $attr_value['height'] ?? null;
		$max_height      = $attr_value['maxHeight'] ?? null;
		$force_fullwidth = $attr_value['forceFullwidth'] ?? null;

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( null !== $min_height ) {
			$style_declarations->add( 'min-height', $min_height );
		}

		if ( null !== $height ) {
			$style_declarations->add( 'height', $height );

			// Set width to auto if forceFullwidth is not enabled and maxHeight is not auto.
			if ( 'on' !== $force_fullwidth && 'auto' !== $height ) {
				$style_declarations->add( 'width', 'auto' );
			}
		}

		if ( null !== $max_height ) {
			$style_declarations->add( 'max-height', $max_height );

			// Set width to auto if forceFullwidth is not enabled and maxHeight is not none.
			if ( 'on' !== $force_fullwidth && 'none' !== $max_height ) {
				$style_declarations->add( 'width', 'auto' );
			}
		}

		return $style_declarations->value();
	}

}
