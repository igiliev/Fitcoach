<?php
/**
 * Background class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Utils\BackgroundStyleUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Sizing\Sizing;
use ET\Builder\Packages\MaskAndPatternLibrary\Utils\MaskAndPatternUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Background class with helper methods for working with Background style declaration.
 *
 * @since ??
 */
class Background {

	use Traits\ConstantsTrait;
	use Traits\StyleDeclarationTrait;

	/**
	 * Style declaration for gradient background.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/gradient-background-style-declaration gradientBackgroundStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $gradient {
	 *     Gradient property of background attributes.
	 *
	 *     @type string $type            Gradient type. One of `linear`, `radial`, `circular`, or `conic`.
	 *     @type string $direction       Gradient direction. One of `to top`, `to top right`, `to right`, `to bottom right`,
	 *                                   `to bottom`, `to bottom left`, `to left`, `to top left`, or `angle`.
	 *     @type string $directionRadial Gradient radial direction. One of `center`, `top`, `right`, `bottom`, `left`,
	 *                                   `top right`, `top left`, `bottom right`, or `bottom left`.
	 *     @type array  $stops           Array of gradient stops.
	 *     @type string $repeat          Gradient repeat. One of `on` or `off`.
	 *     @type string $length          Gradient length.
	 * }
	 *
	 * @return string
	 */
	public static function gradient_style_declaration( array $gradient ): string {
		$background_default_attr = self::$background_default_attr;
		$type                    = null;
		$direction               = null;
		$stops                   = [];
		$length_default          = $background_default_attr['gradient']['length'];
		$unit_default            = BackgroundStyleUtils::get_unit_from_length( $length_default );
		$length                  = isset( $gradient['length'] ) ? $gradient['length'] : $length_default;
		$sizing_units            = Sizing::$sizing_units;
		$unit_find               = ArrayUtility::find(
			$sizing_units,
			function( $sizing_unit ) use ( $length ) {
				return BackgroundStyleUtils::get_unit_from_length( $length ) === $sizing_unit;
			}
		);
		$unit                    = null !== $unit_find ? $unit_find : $unit_default;
		$max_length              = $length ? " {$length}" : '';

		if ( isset( $gradient['stops'] ) && count( $gradient['stops'] ) >= 2 ) {
			foreach ( $gradient['stops'] as $stop ) {
				$color    = $stop['color'] ?? '';
				$position = $stop['position'] ?? '';

				$stops[] = "{$color} {$position}{$unit}";
			}
		}

		switch ( $gradient['type'] ) {
			case 'conic':
				$type      = 'conic';
				$direction = "from {$gradient['direction']} at {$gradient['directionRadial']}";
				break;
			case 'elliptical':
				$type      = 'radial';
				$direction = "ellipse at {$gradient['directionRadial']}";
				break;
			case 'circular':
				$type      = 'radial';
				$direction = "circle at {$gradient['directionRadial']}";
				break;
			case 'linear':
			default:
				$type      = 'linear';
				$direction = $gradient['direction'];
		}

		// Apply gradient repeat (if set).
		$type = isset( $gradient['repeat'] ) && 'on' === $gradient['repeat'] ? "repeating-{$type}" : $type;

		return "{$type}-gradient({$direction}, " . implode( ',', $stops ) . "{$max_length})";
	}

	/**
	 * Style declaration for background mask.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/background-mask-style-declaration backgroundMaskStyleDeclaration} in:
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
	 *     @type string     $keyFormat  Optional. This is the format of the key that the function will return.
	 *                                  Default `param-case`.
	 * }
	 *
	 * @return string|array
	 */
	public static function background_mask_style_declaration( array $args ) {
		$attr_value              = $args['attrValue'] ?? null;
		$background_default_attr = self::$background_default_attr;
		$default_attr            = $attr_value['defaultAttr'] ?? [];
		$default_attr            = array_merge( $background_default_attr, $default_attr );

		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
				'keyFormat'  => 'param-case',
			]
		);

		$important          = $args['important'];
		$return_type        = $args['returnType'];
		$key_format         = $args['keyFormat'];
		$mask               = $attr_value['mask'] ?? null;
		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
				'keyFormat'  => $key_format,
			]
		);

		if ( $mask && isset( $mask['enabled'] ) && 'on' === $mask['enabled'] ) {
			$values = array_merge( $default_attr['mask'], $mask );

			$style             = $values['style'];
			$color             = $values['color'];
			$transform         = $values['transform'];
			$aspect_ratio      = $values['aspectRatio'];
			$size              = $values['size'];
			$width             = $values['width'];
			$height            = $values['height'];
			$position          = $values['position'];
			$horizontal_offset = $values['horizontalOffset'];
			$vertical_offset   = $values['verticalOffset'];
			$blend             = $values['blend'];

			// Mask SVG (Style + Color).
			$rotated  = BackgroundStyleUtils::get_background_transform_state( $transform, 'rotate' );
			$inverted = BackgroundStyleUtils::get_background_transform_state( $transform, 'invert' );

			$css_svg = MaskAndPatternUtils::get_mask_svg(
				[
					'style'    => $style,
					'color'    => $color,
					'size'     => $size,
					'type'     => $aspect_ratio ? $aspect_ratio : 'landscape', // Force 'landscape' when $aspect_ratio is empty.
					'rotated'  => $rotated,
					'inverted' => $inverted,
				]
			);

			$style_declarations->add( 'background-image', 'url("data:image/svg+xml;utf8,' . $css_svg . '")' );

			// Mask Transform.
			$horizontal    = BackgroundStyleUtils::get_background_transform_state( $transform, 'horizontal' );
			$vertical      = BackgroundStyleUtils::get_background_transform_state( $transform, 'vertical' );
			$css_transform = BackgroundStyleUtils::get_background_transform_css( $horizontal, $vertical );

			$style_declarations->add( 'transform', $css_transform );

			// Mask Size.
			if ( isset( $mask['size'] ) ) {
				$css_size = BackgroundStyleUtils::get_background_size_css( $size, $width, $height, 'mask' );

				$style_declarations->add( 'background-size', $css_size );
			}

			// Mask Position.
			// Print mask position/offset when mask size is not 'stretch'.
			if ( isset( $mask['position'] ) && 'stretch' !== $size ) {
				$css_repeat_origin = BackgroundStyleUtils::get_background_position_css( $position, $horizontal_offset, $vertical_offset );

				$style_declarations->add( 'background-position', $css_repeat_origin );
			}

			// Mask Blend Mode.
			if ( isset( $mask['blend'] ) ) {
				$style_declarations->add( 'mix-blend-mode', $blend );
			}
		}

		return $style_declarations->value();

	}

	/**
	 * Style declaration for background pattern.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/background-pattern-style-declaration backgroundPatternStyleDeclaration} in:
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
	 *     @type string     $keyFormat  Optional. This is the format of the key that the function will return.
	 *                                  Default `param-case`.
	 * }
	 *
	 * @return string|array
	 */
	public static function background_pattern_style_declaration( array $args ) {
		$attr_value              = $args['attrValue'] ?? false;
		$background_default_attr = self::$background_default_attr;
		$default_attr            = $attr_value['defaultAttr'] ?? [];
		$default_attr            = wp_parse_args( $default_attr, $background_default_attr );

		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
				'keyFormat'  => 'param-case',
			]
		);

		$important          = $args['important'];
		$return_type        = $args['returnType'];
		$key_format         = $args['keyFormat'];
		$pattern            = $attr_value['pattern'] ?? null;
		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
				'keyFormat'  => $key_format,
			]
		);

		if ( $pattern && isset( $pattern['enabled'] ) && 'on' === $pattern['enabled'] ) {
			$values = array_merge( $default_attr['pattern'], $pattern );

			$style             = $values['style'];
			$color             = $values['color'];
			$transform         = $values['transform'];
			$size              = $values['size'];
			$width             = $values['width'];
			$height            = $values['height'];
			$repeat_origin     = $values['repeatOrigin'];
			$horizontal_offset = $values['horizontalOffset'];
			$vertical_offset   = $values['verticalOffset'];
			$repeat            = $values['repeat'];
			$blend             = $values['blend'];

			// Pattern SVG (Style + Color).
			$rotated  = BackgroundStyleUtils::get_background_transform_state( $transform, 'rotate' );
			$inverted = BackgroundStyleUtils::get_background_transform_state( $transform, 'invert' );

			$css_svg = MaskAndPatternUtils::get_pattern_svg(
				[
					'style'    => $style,
					'color'    => $color,
					'type'     => 'default',
					'rotated'  => $rotated,
					'inverted' => $inverted,
				]
			);

			$style_declarations->add( 'background-image', 'url("data:image/svg+xml;utf8,' . $css_svg . '")' );

			// Pattern Transform.
			$horizontal    = BackgroundStyleUtils::get_background_transform_state( $transform, 'horizontal' );
			$vertical      = BackgroundStyleUtils::get_background_transform_state( $transform, 'vertical' );
			$css_transform = BackgroundStyleUtils::get_background_transform_css( $horizontal, $vertical );

			$style_declarations->add( 'transform', $css_transform );

			// Pattern Size.
			if ( isset( $pattern['size'] ) ) {
				$css_size = BackgroundStyleUtils::get_background_size_css( $size, $width, $height, 'pattern' );

				$style_declarations->add( 'background-size', $css_size );
			}

			// Pattern Repeat Origin.
			// Print pattern repeat origin/offset when pattern size is not 'stretch', and pattern repeat is not 'space'.
			if ( isset( $pattern['repeatOrigin'] ) && 'stretch' !== $size && 'space' !== $repeat ) {
				$css_repeat_origin = BackgroundStyleUtils::get_background_position_css( $repeat_origin, $horizontal_offset, $vertical_offset );

				$style_declarations->add( 'background-position', $css_repeat_origin );
			}

			// Pattern Repeat.
			if ( isset( $pattern['repeat'] ) && 'stretch' !== $size ) {
				$style_declarations->add( 'background-repeat', $repeat );
			}

			// Pattern Blend Mode.
			if ( isset( $pattern['blend'] ) ) {
				$style_declarations->add( 'mix-blend-mode', $blend );
			}
		}

		return $style_declarations->value();

	}
}
