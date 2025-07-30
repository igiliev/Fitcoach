<?php
/**
 * Background::style_declaration()
 *
 * @package Builder\FrontEnd
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Background\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Utils\BackgroundStyleUtils;

trait StyleDeclarationTrait {

	/**
	 * Style declaration for background.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/background-style-declaration backgroundStyleDeclaration} in:
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
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
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
		$breakpoint         = $args['breakpoint'] ?? null;
		$attr_value         = $args['attrValue'] ?? null;
		$preview            = $attr_value['preview'] ?? false;
		$color              = $attr_value['color'] ?? null;
		$gradient           = $attr_value['gradient'] ?? null;
		$image              = $attr_value['image'] ?? [];
		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
				'keyFormat'  => $key_format,
			]
		);

		$is_image_not_enabled    = is_array( $image ) && array_key_exists( 'enabled', $image ) && 'off' === $image['enabled'];
		$is_gradient_not_enabled = is_array( $gradient ) && array_key_exists( 'enabled', $gradient ) && 'off' === $gradient['enabled'];

		$background_default_attr = Background::$background_default_attr;
		$default_attr            = $attr_value['defaultAttr'] ?? $background_default_attr;
		$default_attr            = array_merge( $background_default_attr, $default_attr );
		$background_images       = [];

		// Load default so if the attribute lacks required value, it'll be rendered using default.
		$image_values = array_merge( $default_attr['image'], $image );

		if ( $image && ! $is_image_not_enabled ) {
			$url               = $image_values['url'];
			$parallax          = $image_values['parallax'];
			$size              = $image_values['size'];
			$width             = $image_values['width'];
			$height            = $image_values['height'];
			$position          = $image_values['position'];
			$horizontal_offset = $image_values['horizontalOffset'];
			$vertical_offset   = $image_values['verticalOffset'];
			$repeat            = $image_values['repeat'];
			$blend             = $image_values['blend'];

			if ( isset( $image['url'] ) && isset( $parallax['enabled'] ) && 'on' !== $parallax['enabled'] ) {
				$background_images[] = "url({$url})";

				if ( isset( $image['size'] ) ) {
					$css_image_size = BackgroundStyleUtils::get_background_size_css( $size, $width, $height, 'image' );

					$style_declarations->add( 'background-size', $css_image_size );
				}

				if ( isset( $image['position'] ) ) {
					$css_image_position = BackgroundStyleUtils::get_background_position_css( $position, $horizontal_offset, $vertical_offset );

					$style_declarations->add( 'background-position', $css_image_position );
				}

				if ( isset( $repeat ) ) {
					$style_declarations->add( 'background-repeat', $repeat );
				}

				if ( isset( $image['blend'] ) ) {
					$style_declarations->add( 'background-blend-mode', $blend );
				}
			}

			if ( $preview && $image['url'] && isset( $parallax['enabled'] ) && 'on' === $parallax['enabled'] ) {
				$background_images[] = "url({$url})";

				// Background styles for preview area when parallax is on.
				$style_declarations->add( 'background-size', 'cover' );
				$style_declarations->add( 'background-position', 'center' );
				$style_declarations->add( 'background-repeat', 'no-repeat' );
				$style_declarations->add( 'background-blend-mode', $blend );
			}
		}

		if ( $gradient ) {
			// Render gradient when enabled.
			if ( isset( $gradient['enabled'] ) && 'on' === $gradient['enabled'] ) {
				// Load default so if the attribute lacks required value, it'll be rendered using default.
				$gradient_background = array_merge( $default_attr['gradient'], $gradient );

				$background_images[] = Background::gradient_style_declaration( $gradient_background );
			}

			// Render 'none' when disabled and breakpoint isn't desktop.
			if ( 'desktop' !== $breakpoint && $is_gradient_not_enabled ) {
				$background_images[] = 'none';
			}
		}

		if ( ! empty( $background_images ) ) {
			// Swap background gradient on top of background image when gradient has stops and overlayImage option is on.
			if ( $gradient && ! empty( $gradient['stops'] ) && isset( $gradient['overlaysImage'] ) && 'on' === $gradient['overlaysImage'] ) {
				$background_images = array_reverse( $background_images );
			}
		} elseif ( $is_image_not_enabled || $is_gradient_not_enabled ) {
			// If both image and gradient are disabled, empty the array.
			$background_images = [ 'initial' ];
		}

		if ( ! empty( $background_images ) ) {
			$style_declarations->add( 'background-image', implode( ', ', $background_images ) );
		}

		if ( $color ) {
			// If background gradient and image exist, background color should be reset to initial to
			// prevent blend mode from colliding.
			$should_force_initial = count( $background_images ) >= 2 && 'normal' !== $image_values['blend'];
			$background_color     = $should_force_initial ? 'initial' : $color;

			$style_declarations->add( 'background-color', $background_color );
		}

		return $style_declarations->value();

	}

}
