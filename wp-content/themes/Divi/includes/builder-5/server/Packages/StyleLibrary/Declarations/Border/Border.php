<?php
/**
 * Border class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Border;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Framework\Utility\TextTransform;

/**
 * Border class with helper methods for working with Border style declaration.
 *
 * @since ??
 */
class Border {

	/**
	 * Get Border's CSS declaration based on given attrValue.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/border-style-declaration borderStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'        => false,
				'returnType'       => 'string',
				'defaultAttrValue' => [],
			]
		);

		$attr_value         = $args['attrValue'];
		$default_attr_value = $args['defaultAttrValue'];
		$important          = $args['important'];
		$return_type        = $args['returnType'];
		$radius             = isset( $attr_value['radius'] ) ? $attr_value['radius'] : null;
		$styles             = isset( $attr_value['styles'] ) ? $attr_value['styles'] : null;
		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( $radius ) {
			$valid_radius = [ 'topLeft', 'topRight', 'bottomRight', 'bottomLeft' ];

			foreach ( $radius as $radii_size => $radii ) {
				if ( ! in_array( $radii_size, $valid_radius, true ) ) {
					continue;
				}
				$style_declarations->add( 'border-' . TextTransform::param_case( $radii_size ) . '-radius', $radii );
			}
		}

		if ( $styles ) {
			$valid_style_sides = [ 'all', 'top', 'right', 'bottom', 'left' ];
			$all_side_width    = $styles['all']['width'] ?? null;
			$all_side_color    = $styles['all']['color'] ?? null;
			$all_side_style    = $styles['all']['style'] ?? null;

			foreach ( $styles as $side => $style_side ) {
				if ( ! in_array( $side, $valid_style_sides, true ) ) {
					continue;
				}

				$width = $style_side['width'] ?? null;
				$color = $style_side['color'] ?? null;
				$style = $style_side['style'] ?? null;

				$is_all         = 'all' === $side;
				$width_property = $is_all ? 'border-width' : "border-{$side}-width";
				$color_property = $is_all ? 'border-color' : "border-{$side}-color";
				$style_property = $is_all ? 'border-style' : "border-{$side}-style";

				if ( $width ) {
					// All sides: Simply set the width.
					// Side Specific: If width is not the same with all side width, then set the width.
					if ( $is_all || ( ! $is_all && $width !== $all_side_width ) ) {
						$style_declarations->add( $width_property, $width );
					}
				}

				if ( $color ) {
					// All sides: Simply set the color.
					// Side Specific: If color is not the same with all side color, then set the color.
					if ( $is_all || ( ! $is_all && $color !== $all_side_color ) ) {
						$style_declarations->add( $color_property, $color );
					}
				} elseif ( $width ) {
					// Fallback of .et_pb_with_border class removal.
					// All sides: If width is set and color is not set, then set to #333.
					// Side Specific: If width is set and color is not set and all side color is not set, then set to #333.
					$should_render_fallback_border_color = $is_all || ( ! $is_all && ! $all_side_color );

					// `defaultAttrValue` is derived from default printed style attribute. If border-color has been printed either
					// from module's static style or customizer, then this fallback shouldn't be returned and rendered.
					$has_default_attr_color = (bool) (
						$default_attr_value['styles'][ $side ]['color'] ?? false
					);

					if ( $should_render_fallback_border_color && ! $has_default_attr_color ) {
						$style_declarations->add( $color_property, '#333' );
					}
				}

				if ( $style ) {
					// All sides: Simply set the style.
					// Side Specific: If style is not the same with all side style, then set the style.
					if ( $is_all || ( ! $is_all && $style !== $all_side_style ) ) {
						$style_declarations->add( $style_property, $style );
					}
				} elseif ( $width ) {
					// Fallback of .et_pb_with_border class removal.
					// All sides: If width is set and style is not set, then set to solid.
					// Side Specific: If width is set and style is not set and all side style is not set, then set to solid.
					$should_render_fallback_border_style = $is_all || ( ! $is_all && ! $all_side_style );

					// `defaultAttrValue` is derived from default printed style attribute. If border-style has been printed either
					// from module's static style or customizer, then this fallback shouldn't be returned and rendered.
					$has_default_attr_style = (bool) (
						$default_attr_value['styles'][ $side ]['style'] ?? false
					);

					if ( $should_render_fallback_border_style && ! $has_default_attr_style ) {
						$style_declarations->add( $style_property, 'solid' );
					}
				}
			}
		}

		return $style_declarations->value();

	}
}
