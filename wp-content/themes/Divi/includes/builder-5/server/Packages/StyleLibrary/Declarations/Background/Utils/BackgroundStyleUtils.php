<?php
/**
 * BackgroundStyleUtils class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Background\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * BackgroundStyleUtils is a helper class for Background Style Declaration.
 *
 * @since ??
 */
class BackgroundStyleUtils {

	/**
	 * Get CSS for background position.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/get-background-position-css getBackgroundPositionCss} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param string $position          Position value.
	 * @param string $horizontal_offset Horizontal offset value.
	 * @param string $vertical_offset   Vertical offset value.
	 *
	 * @return string Formatted position value for CSS output.
	 */
	public static function get_background_position_css( string $position, string $horizontal_offset, string $vertical_offset ): string {
		$position_array = is_string( $position ) ? explode( ' ', $position ) : [];
		$position_x     = null;
		$position_y     = null;

		// Horizontal Offset.
		if ( isset( $position_array[0] ) ) {
			switch ( $position_array[0] ) {
				case 'left':
				case 'right':
					// Left/Right doesn't need suffix when value is 0.
					$position_x = 0 === intval( $horizontal_offset ) ? $position_array[0] : "{$position_array[0]} {$horizontal_offset}";
					break;
				case 'center':
				default:
					$position_x = 'center';
			}
		}

		// Vertical Offset.
		if ( isset( $position_array[1] ) ) {
			switch ( $position_array[1] ) {
				case 'top':
				case 'bottom':
					// Top/Bottom doesn't need suffix when value is 0.
					$position_y = 0 === intval( $vertical_offset ) ? $position_array[1] : "{$position_array[1]} {$vertical_offset}";
					break;
				case 'center':
				default:
					$position_y = 'center';
			}
		} else {
			// When positionArray[1] is absent.
			$position_y = 'center';
		}

		// diff(D4, Backgrounds): returns as `positionX positionY` to comply with CSS rule.
		// eslint-disable-next-line max-len
		// @see https://elegantthemes.slack.com/archives/C02TFGQRSP5/p1662054073672009?thread_ts=1661969758.778879&cid=C02TFGQRSP5 .
		return 'center' === $position_x && 'center' === $position_y ? 'center' : "{$position_x} {$position_y}";
	}

	/**
	 * Get CSS for background size.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/get-background-size-css getBackgroundSizeCss} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param string $size   Size. One of `auto`, `cover`, `contain`, `stretch`, `custom`.
	 * @param string $width  Width.
	 * @param string $height Height.
	 * @param string $type   Type, used to handle special `mask` case.
	 *
	 * @return string|null Formatted size value for CSS output.
	 */
	public static function get_background_size_css( string $size, string $width, string $height, string $type = '' ): ?string {
		$output = null;

		switch ( $size ) {
			case 'custom':
				// We need to check if the value is 0 with any unit, but we also need to support non integer values such as CSS variables, auto, unset etc.
				$is_width_auto  = 'auto' === $width || '' === $width || ( strpos( $width, '--' ) === false && preg_match( '/\d/', $width ) && 0 === intval( $width ) );
				$is_height_auto = 'auto' === $height || '' === $height || ( strpos( $height, '--' ) === false && preg_match( '/\d/', $height ) && 0 === intval( $height ) );
				$width_value    = $is_width_auto ? 'auto' : $width;
				$height_value   = $is_height_auto ? 'auto' : $height;

				if ( $is_width_auto && $is_height_auto ) {
					$output = 'initial';
				} else {
					$output = "{$width_value} {$height_value}";
				}
				break;
			case 'stretch':
				$output = 'mask' === $type ? 'calc(100% + 2px) calc(100% + 2px)' : '100% 100%';
				break;
			case 'cover':
			case 'contain':
				$output = $size;
				break;
			default:
				$output = 'initial';
		}

		return $output;
	}

	/**
	 * Get CSS to Transform the SVG.
	 *
	 * We use `scale` here because CSS Transform's `rotateX`/`rotateY` triggers a 10+
	 * year old Safari bug that hides rotated background images (including SVGs)
	 * {@link https://bugs.webkit.org/show_bug.cgi?id=6182 see}.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/get-background-transform-css getBackgroundTransformCss} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param boolean $horizontal Whether to Flip Horizontally.
	 * @param boolean $vertical   Whether to Flip Vertically.
	 *
	 * @return string Transform CSS Style.
	 */
	public static function get_background_transform_css( bool $horizontal, bool $vertical ): string {
		$flip_h = $horizontal ? '-1' : '1';
		$flip_v = $vertical ? '-1' : '1';

		return "scale({$flip_h}, {$flip_v})";
	}

	/**
	 * Get state for the Transform of Pattern/Mask Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/get-background-transform-state getBackgroundTransformState} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array  $value Value of the Transform option field.
	 * @param string $state One of the `horizontal`, `invert`, `rotate`, or `vertical`.
	 *
	 * @return boolean Whether state found in the value.
	 */
	public static function get_background_transform_state( array $value, string $state ): bool {
		$result = false;

		if ( empty( $value ) ) {
			return $result;
		}

		switch ( $state ) {
			case 'horizontal':
				$result = in_array( 'flipHorizontal', $value, true );
				break;
			case 'invert':
				$result = in_array( 'invert', $value, true );
				break;
			case 'rotate':
				$result = in_array( 'rotate', $value, true );
				break;
			case 'vertical':
				$result = in_array( 'flipVertical', $value, true );
				break;
			default:
		}

		return $result;
	}

	/**
	 * Get unit of measurement used for gradient stop positions.
	 *
	 * The "Gradient Length" setting contains both a number value and a unit of measurement.
	 * The unit of measurement gets used in other places, so this function will parse that
	 * and make that data available elsewhere.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/get-unit-from-length getUnitFromLength} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param string $gradient_length Gradient length.
	 *
	 * @return string
	 */
	public static function get_unit_from_length( string $gradient_length ): string {
		preg_match( '/[^\d.,-]/', $gradient_length, $matches, PREG_OFFSET_CAPTURE );

		if ( ! isset( $matches[0][1] ) ) {
			return '%';
		}

		return substr( $gradient_length, $matches[0][1] );
	}

	/**
	 * Check whether current module has background or not for specific type.
	 *
	 * @since ??
	 *
	 * @param array  $attr_value The value of the background attribute.
	 * @param string $background_type The type of background to check.
	 *
	 * @return bool Has background or not.
	 */
	public static function has_background_style_by_type( array $attr_value, string $background_type ): bool {
		if ( ! $attr_value ) {
			return false;
		}

		$is_has_style = false;

		switch ( $background_type ) {
			case 'color':
				$is_has_style = '' !== ( $attr_value['color'] ?? '' );
				break;

			case 'gradient':
				$is_has_style = count( $attr_value['gradient']['stops'] ?? [] ) > 0;
				break;

			case 'image':
				$is_has_style = '' !== ( $attr_value['image']['url'] ?? '' );
				break;

			case 'video':
				$is_has_background_mp4  = '' !== ( $attr_value['video']['mp4'] ?? '' );
				$is_has_background_webm = '' !== ( $attr_value['video']['webm'] ?? '' );

				$is_has_style = $is_has_background_mp4 || $is_has_background_webm;
				break;

			case 'pattern':
			case 'mask':
				$enabled = 'on' !== ( $attr_value[ $background_type ]['enabled'] ?? '' );
				$style   = $attr_value[ $background_type ]['style'] ?? '';

				if ( 'none' === $style ) {
					$style = '';
				}

				$is_has_style = $enabled && '' !== $style;
				break;

			default:
				// Do nothing.
				break;
		}

		return $is_has_style;
	}

	/**
	 * Check whether current module has background or not.
	 *
	 * @since ??
	 *
	 * @param array $attr_value The value of the background attribute.
	 * @param array $background_types The types of background to check.
	 *
	 * @return bool Has background or not.
	 */
	public static function has_background_style( array $attr_value, array $background_types = [ 'color', 'gradient', 'image', 'video', 'pattern', 'mask' ] ): bool {
		if ( ! $attr_value ) {
			return false;
		}

		foreach ( $background_types as $background_type ) {
			if ( self::has_background_style_by_type( $attr_value, $background_type ) ) {
				return true;
			}
		}

		return false;
	}
}
