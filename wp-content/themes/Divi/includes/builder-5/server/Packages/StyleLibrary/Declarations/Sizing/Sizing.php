<?php
/**
 * Sizing class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Sizing;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Sizing class
 *
 * This class provides sizing functionality.
 *
 * @since ??
 */
class Sizing {

	/**
	 * Get sizing CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/sizing-style-declaration/ sizingStyleDeclaration}
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
	 * @return array|string The generated sizing CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue'   => ['orientation' => 'center'], // The attribute value.
	 *     'important'   => true,                        // Whether the declaration should be marked as important.
	 *     'returnType'  => 'key_value_pair',            // The return type of the style declaration.
	 * ];
	 * $style = Sizing::style_declaration( $args );
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

		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];
		$width       = isset( $attr_value['width'] ) ? $attr_value['width'] : null;
		$max_width   = isset( $attr_value['maxWidth'] ) ? $attr_value['maxWidth'] : null;
		$alignment   = isset( $attr_value['alignment'] ) ? $attr_value['alignment'] : null;
		$min_height  = isset( $attr_value['minHeight'] ) ? $attr_value['minHeight'] : null;
		$height      = isset( $attr_value['height'] ) ? $attr_value['height'] : null;
		$max_height  = isset( $attr_value['maxHeight'] ) ? $attr_value['maxHeight'] : null;

		// Flexbox sizing options.
		$size        = isset( $attr_value['size'] ) ? $attr_value['size'] : null;
		$flex_shrink = isset( $attr_value['flexShrink'] ) ? $attr_value['flexShrink'] : null;
		$flex_grow   = isset( $attr_value['flexGrow'] ) ? $attr_value['flexGrow'] : null;
		$align_self  = isset( $attr_value['alignSelf'] ) ? $attr_value['alignSelf'] : null;

		$is_parent_layout_flex = $args['isParentLayoutFlex'] ?? false;

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( $is_parent_layout_flex && isset( $size ) && is_array( $size ) ) {
			if ( in_array( 'custom', $size, true ) ) {
				// Custom mode is selected.
				// Apply flex-grow only if the value is set, not empty string, and not '0'.
				if ( isset( $flex_grow ) && '' !== $flex_grow && '0' !== $flex_grow ) {
					$style_declarations->add( 'flex-grow', $flex_grow );
				}
				// Apply flex-shrink only if the value is set, not empty string, and not '1'.
				if ( isset( $flex_shrink ) && '' !== $flex_shrink && '1' !== $flex_shrink ) {
					$style_declarations->add( 'flex-shrink', $flex_shrink );
				}
			} else {
				// Custom mode is NOT selected. Handle individual toggles.
				// If 'flexGrow' toggle is selected, apply flex-grow: 1.
				if ( in_array( 'flexGrow', $size, true ) ) {
					$style_declarations->add( 'flex-grow', '1' );
				}
				// If 'flexShrink' toggle is NOT selected, apply flex-shrink: 0.
				// (If 'flexShrink' is selected, it implies default shrink behavior, so print nothing).
				if ( ! in_array( 'flexShrink', $size, true ) ) {
					$style_declarations->add( 'flex-shrink', '0' );
				}
			}
		}

		if ( $is_parent_layout_flex && null !== $align_self ) {
			$style_declarations->add( 'align-self', $align_self );
		}

		if ( null !== $width ) {
			$style_declarations->add( 'width', $width );
		}

		if ( null !== $max_width ) {
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
		}

		if ( null !== $min_height ) {
			$style_declarations->add( 'min-height', $min_height );
		}

		if ( null !== $height ) {
			$style_declarations->add( 'height', $height );
		}

		if ( null !== $max_height ) {
			$style_declarations->add( 'max-height', $max_height );
		}

		return $style_declarations->value();
	}

	/**
	 * Array of sizing units.
	 *
	 * This array contains various sizing units that can be used for CSS properties, such as `width`, `height`, `font-size`, etc.
	 * These units define the measurement of the value assigned to the CSS property.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/sizing-units/ sizingUnits}
	 * located in `@divi/style-library` package.
	 *
	 * @var array $sizing_units Array of sizing units.
	 *                          Default `['%', 'ch', 'cm', 'em', 'ex', 'in', 'mm', 'pc', 'pt', 'px', 'rem', 'vh', 'vmax', 'vmin', 'vw']`.
	 *
	 * @since ??
	 */
	public static $sizing_units = [
		'%',
		'ch',
		'cm',
		'em',
		'ex',
		'in',
		'mm',
		'pc',
		'pt',
		'px',
		'rem',
		'vh',
		'vmax',
		'vmin',
		'vw',
	];
}
