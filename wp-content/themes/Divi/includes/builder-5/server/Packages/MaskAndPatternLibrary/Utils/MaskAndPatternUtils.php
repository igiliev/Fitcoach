<?php
/**
 * MaskAndPatternUtils class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\MaskAndPatternLibrary\Utils;

use ET\Builder\Packages\MaskAndPatternLibrary\MaskSvg\MaskSvg;
use ET\Builder\Packages\MaskAndPatternLibrary\PatternSvg\PatternSvg;
use ET\Builder\Framework\Utility\ArrayUtility;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * MaskAndPatternUtils class.
 *
 * This class contains methods to work with mask and pattern styles.
 *
 * This class is equivalent of JS package:
 * {@link /docs/category/maskandpatternlibrary @divi/mask-and-pattern-library}.
 *
 * @since ??
 */
class MaskAndPatternUtils {

	/**
	 * Helper function to encode the SVG with `encodeURIComponent`.
	 *
	 * Use it before passing SVG to data URI, so that browser can render it correctly.
	 * Note: we don't decode it anywhere, the browser does that.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/encodeSvgForDataUri encodeSvgForDataURI}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @param string $data String to escape.
	 * @param string $external_quotes_type  Optional. External quote type, one of `double` or `single`. Default `double`.
	 *
	 * @return string
	 */
	public static function encode_svg_for_data_uri( string $data, string $external_quotes_type = 'double' ): string {
		// Symbols that need to be URI encoded.
		// Regex matches SVG symbols to be passed through encodeSvgForDataURI() PHP equivalent.
		// Match example: https://regex101.com/r/3HVsjS/1 .
		$symbols = '/[\r\n%#()<>?[\\\\\]^`{|}]/';

		// Use single quotes instead of double to avoid encoding.
		if ( 'double' === $external_quotes_type ) {
			$data = preg_replace( '/"/', '\'', $data );
		} else {
			$data = preg_replace( "/'/", '"', $data );
		}

		$data = preg_replace( '/>\s+</', '><', $data );
		$data = preg_replace( '/\s{2,}/', ' ', $data );

		return preg_replace_callback(
			$symbols,
			// Equivalent ot encodeURIComponent()
			// Source: https://stackoverflow.com/a/1734255/1482443 .
			function ( $string ) {
				$revert = array(
					'%21' => '!',
					'%2A' => '*',
					'%27' => "'",
					'%28' => '(',
					'%29' => ')',
				);

				return strtr( rawurlencode( $string[0] ), $revert );
			},
			$data
		);
	}

	/**
	 * Default viewBox Settings for Mask Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/functions/getMaskDefaultViewboxSettings getMaskDefaultViewboxSettings}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @return array {
	 *   viewBox Settings.
	 *
	 *   @type string $landscape Landscape viewBox Settings. Default `0 0 1920 1440`.
	 *   @type string $portrait  Portrait viewBox Settings. Default `0 0 1920 2560`.
	 *   @type string $square    Square viewBox Settings. Default `0 0 1920 1920`.
	 *   @type string $thumbnail Thumbnail viewBox Settings. Default `0 0 1920 1440`.
	 * }
	 */
	public static function get_mask_default_viewbox_settings(): array {
		return [
			'landscape' => '0 0 1920 1440',
			'portrait'  => '0 0 1920 2560',
			'square'    => '0 0 1920 1920',
			'thumbnail' => '0 0 1920 1440',
		];
	}

	/**
	 * Get SVG Settings for a Mask Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getMaskJson getMaskJson}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @param string $style_name Mask Style name.
	 *
	 * @return array The mask JSON, or null if a mask style matching given style name is not found.
	 */
	public static function get_mask_json( string $style_name ): ?array {
		$mask_settings = self::get_mask_settings();
		$mask_style    = ArrayUtility::find(
			$mask_settings,
			function( $style ) use ( $style_name ) {
				return $style['name'] === $style_name;
			}
		);

		return $mask_style ? $mask_style['dataJSON'] : null;
	}

	/**
	 * Mask SVG Settings.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getMaskSettings getMaskSettings}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @return array {
	 *   Pattern style list.
	 *
	 *   @type string $name     Pattern style name.
	 *   @type array  $dataJSON Pattern style data.
	 * }
	 */
	public static function get_mask_settings(): array {
		static $settings = null;

		if ( null !== $settings ) {
			return $settings;
		}

		// Default mask styles.
		$mask_style_defaults = MaskSvg::mask_style_list();

		$default_style_name = 'layer-blob';
		$default_style_data = ArrayUtility::find(
			$mask_style_defaults,
			function( $style ) use ( $default_style_name ) {
				return $style['name'] === $default_style_name;
			}
		);

		// Push default style to be on top.
		$settings[] = [
			'name'     => $default_style_data['name'],
			'dataJSON' => $default_style_data,
		];

		/**
		 * Filter the mask style list.
		 *
		 * Add custom mask styles to the mask library.
		 *
		 * @since ??
		 *
		 * @param array $mask_style_defaults The mask style list.
		 */
		$mask_style_filtered = apply_filters( 'divi_mask_and_pattern_library_mask', $mask_style_defaults );

		// Remove any filter mask styles with duplicate names.
		$mask_style_filtered = array_unique( $mask_style_filtered, SORT_REGULAR );

		// Sort the filtered mask styles by their name values.
		uasort(
			$mask_style_filtered,
			function( $a, $b ) {
				return $a['name'] <=> $b['name'];
			}
		);

		// Sort the filtered mask styles by their priority values, if found.
		// If no priority value is found, use a default priority value of 10.
		uasort(
			$mask_style_filtered,
			function( $a, $b ) {
				$priority_a = $a['priority'] ?? 10;
				$priority_b = $b['priority'] ?? 10;
				return $priority_a - $priority_b;
			}
		);

		// If the same mask name exists in both the filtered and default
		// mask styles, then add the default mask style to the mask
		// settings list. Otherwise, add the filtered mask style to the
		// mask settings list.
		foreach ( $mask_style_filtered as $mask_style_name => $mask_style ) {
			if ( array_key_exists( $mask_style_name, $mask_style_defaults ) ) {
				// Add the mask style data to the mask style list.
				$settings[] = [
					'name'     => $mask_style_defaults[ $mask_style_name ]['name'],
					'dataJSON' => $mask_style_defaults[ $mask_style_name ],
				];
			} else {
				// Add the mask style data to the mask style list.
				$settings[] = [
					'name'     => $mask_style_name,
					'dataJSON' => $mask_style,
				];
			}
		}

		return $settings;
	}

	/**
	 * Get Mask Style Options.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getMaskStyleOptions getMaskStyleOptions}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @return array {
	 *   Mask style options.
	 *
	 *   @type array $name {
	 *     @type string $name  Mask style name. This comes from the `name` property in the mask style json file.
	 *     @type string $label Mask style label. This comes from the `label` property in the mask style json file.
	 *   }
	 * }
	 */
	public static function get_mask_style_options(): array {
		$settings = self::get_mask_settings();
		$options  = [];

		foreach ( $settings as $style ) {
			$option = [
				'name'  => $style['name'],
				'label' => $style['dataJSON']['label'],
			];

			$options = array_merge(
				$options,
				[
					$style['name'] => $option,
				]
			);
		}

		return $options;
	}

	/**
	 * Get SVG for a Mask style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getMaskSvg getMaskSvg}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string  $style    Mask SVG style.
	 *     @type string  $color    Mask SVG color.
	 *     @type string  $type     Mask SVG type.
	 *     @type boolean $rotated  Optional. Whether the mask SVG should be rotated. Default `false`.
	 *     @type boolean $inverted Optional. Whether the mask SVG should be inverted inverted. Default `false`.
	 *     @type boolean $escape   Optional. Whether the mask SVG should be escape. Default `true`.
	 *                             If `true`, the function return value will be passed through `MaskAndPatternUtils::encode_svg_for_data_uri` first.
	 * }
	 *
	 * @return string
	 */
	public static function get_mask_svg( array $args ): string {
		$style    = $args['style'];
		$color    = $args['color'];
		$size     = $args['size'];
		$type     = $args['type'];
		$rotated  = $args['rotated'] ?? false;
		$inverted = $args['inverted'] ?? false;
		$escape   = $args['escape'] ?? true;

		$settings = self::get_mask_json( $style );

		if ( ! $settings ) {
			return '';
		}

		$view_box_settings = self::get_mask_default_viewbox_settings();

		$content  = null;
		$view_box = null;

		if ( 'thumbnail' === $type ) {
			// For thumbnail content, use `landscape' from 'regular' and 'default' group.
			$content  = isset( $settings['svgContent']['regular']['default']['landscape'] ) ? $settings['svgContent']['regular']['default']['landscape'] : null;
			$view_box = $view_box_settings['thumbnail'];
		} else {
			$svg_group              = $inverted ? 'inverted' : 'regular';
			$svg_group_content_type = $rotated ? 'rotated' : 'default';

			$content  = isset( $settings['svgContent'][ $svg_group ][ $svg_group_content_type ][ $type ] ) ? $settings['svgContent'][ $svg_group ][ $svg_group_content_type ][ $type ] : null;
			$view_box = isset( $view_box_settings[ $type ] ) ? $view_box_settings[ $type ] : null;
		}

		$stretched = 'stretch' === $size || '' === $size;
		$props     = self::get_svg_attrs(
			[
				'fill'                => esc_attr( $color ),
				'preserveAspectRatio' => $stretched ? 'none' : 'xMinYMin slice',
				'viewBox'             => esc_attr( $view_box ),
			]
		);

		return $escape ? self::encode_svg_for_data_uri( "<svg {$props}>{$content}</svg>" ) : "<svg {$props}>{$content}</svg>";
	}

	/**
	 * Default Thumbnail Settings for Pattern Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/functions/getPatternDefaultThumbnailSettings getPatternDefaultThumbnailSettings}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @return array {
	 *   Thumbnail Settings.
	 *
	 *   @type string $height The default thumbnail height. Default `60px`.
	 *   @type string $width  The default thumbnail width. Default `80px`.
	 * }
	 */
	public static function get_pattern_default_thumbnail_settings(): array {
		return [
			'height' => '60px',
			'width'  => '80px',
		];
	}

	/**
	 * Get SVG Settings for a Pattern Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getPatternJson getPatternJSON}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @param string $style_name Pattern Style name.
	 *
	 * @return string The settings JSON, or null if a pattern style matching given style name is not found.
	 */
	public static function get_pattern_json( string $style_name ): ?array {
		$pattern_settings = self::get_pattern_settings();
		$pattern_style    = ArrayUtility::find(
			$pattern_settings,
			function( $style ) use ( $style_name ) {
				return $style['name'] === $style_name;
			}
		);

		return $pattern_style ? $pattern_style['dataJSON'] : null;
	}

	/**
	 * Pattern SVG Settings.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getPatternSettings getPatternSettings}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @throws Exception Throw error when an expected json file does not exist.
	 *
	 * @return array {
	 *   Pattern style list.
	 *
	 *   @type string $name     Pattern style name.
	 *   @type array  $dataJSON Pattern style data.
	 * }
	 */
	public static function get_pattern_settings(): array {
		static $settings = null;

		if ( null !== $settings ) {
			return $settings;
		}

		// Default pattern styles.
		$pattern_style_defaults = PatternSvg::pattern_style_list();

		$default_style_name = 'polka-dots';
		$default_style_data = ArrayUtility::find(
			$pattern_style_defaults,
			function( $style ) use ( $default_style_name ) {
				return $style['name'] === $default_style_name;
			}
		);

		// Push default style to be on top.
		$settings[] = [
			'name'     => $default_style_data['name'],
			'dataJSON' => $default_style_data,
		];

		/**
		 * Filter the pattern style list.
		 *
		 * Add custom pattern styles to the pattern library.
		 *
		 * @since ??
		 *
		 * @param array $pattern_style_defaults The pattern style list.
		 */
		$pattern_style_filtered = apply_filters( 'divi_mask_and_pattern_library_pattern', $pattern_style_defaults );

		// Remove any filter pattern styles with duplicate names.
		$pattern_style_filtered = array_unique( $pattern_style_filtered, SORT_REGULAR );

		// Sort the filtered pattern styles by their name values.
		uasort(
			$pattern_style_filtered,
			function( $a, $b ) {
				return $a['name'] <=> $b['name'];
			}
		);

		// Sort the filtered pattern styles by their priority values, if found.
		// If no priority value is found, use a default priority value of 10.
		uasort(
			$pattern_style_filtered,
			function( $a, $b ) {
				$priority_a = $a['priority'] ?? 10;
				$priority_b = $b['priority'] ?? 10;
				return $priority_a - $priority_b;
			}
		);

		// If the same pattern name exists in both the filtered and default
		// pattern styles, then add the default pattern style to the pattern
		// settings list. Otherwise, add the filtered pattern style to the
		// pattern settings list.
		foreach ( $pattern_style_filtered as $pattern_style_name => $pattern_style ) {
			if ( array_key_exists( $pattern_style_name, $pattern_style_defaults ) ) {
				// Add the pattern style data to the pattern style list.
				$settings[] = [
					'name'     => $pattern_style_defaults[ $pattern_style_name ]['name'],
					'dataJSON' => $pattern_style_defaults[ $pattern_style_name ],
				];
			} else {
				// Add the pattern style data to the pattern style list.
				$settings[] = [
					'name'     => $pattern_style_name,
					'dataJSON' => $pattern_style,
				];
			}
		}

		return $settings;
	}

	/**
	 * Pattern Style Options.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getPatternStyleOptions getPatternStyleOptions}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @return array {
	 *   Pattern style options.
	 *
	 *   @type array $name {
	 *     @type string $name  Pattern style name. This comes from the `name` property in the pattern style json file.
	 *     @type string $label Pattern style label. This comes from the `label` property in the pattern style json file.
	 *   }
	 * }
	 */
	public static function get_pattern_style_options(): array {
		$settings = self::get_pattern_settings();
		$options  = [];

		foreach ( $settings as $style ) {
			$option = [
				'name'  => $style['name'],
				'label' => $style['dataJSON']['label'],
			];

			$options = array_merge(
				$options,
				[
					$style['name'] => $option,
				]
			);
		}

		return $options;
	}

	/**
	 * Get SVG for a Pattern style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getPatternSvg getPatternSvg}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string  $style    Pattern SVG style.
	 *     @type string  $color    Pattern SVG color.
	 *     @type string  $type     Pattern SVG type.
	 *     @type boolean $rotated  Optional. Whether the pattern SVG should be rotated. Default `false`.
	 *     @type boolean $inverted Optional. Whether the pattern SVG should be inverted inverted. Default `false`.
	 *     @type boolean $escape   Optional. Whether the pattern SVG should be escape. Default `true`.
	 *                             If `true`, the function return value will be passed through `MaskAndPatternUtils::encode_svg_for_data_uri` first.
	 * }
	 *
	 * @return string
	 */
	public static function get_pattern_svg( array $args ): string {
		$style    = $args['style'];
		$color    = $args['color'];
		$type     = $args['type'];
		$rotated  = $args['rotated'] ?? false;
		$inverted = $args['inverted'] ?? false;
		$escape   = $args['escape'] ?? true;

		$settings = self::get_pattern_json( $style );

		if ( ! $settings ) {
			return '';
		}

		$content = null;
		$width   = null;
		$height  = null;

		if ( 'thumbnail' === $type ) {
			$thumbnail = self::get_pattern_default_thumbnail_settings();

			// Get thumbnail svg content from JSON data.
			$content = isset( $settings['svgContent']['thumbnail'] ) ? strval( $settings['svgContent']['thumbnail'] ) : null;
			$width   = $thumbnail['width'];
			$height  = $thumbnail['height'];
		} else {
			$svg_group              = $inverted ? 'inverted' : 'regular';
			$svg_group_content_type = $rotated ? 'rotated' : 'default';

			// Get svg content from JSON data.
			$content = isset( $settings['svgContent'][ $svg_group ][ $svg_group_content_type ] ) ? strval( $settings['svgContent'][ $svg_group ][ $svg_group_content_type ] ) : null;

			if ( $rotated ) {
				// When rotated, we need to swap the width/height.
				$width  = $settings['height'];
				$height = $settings['width'];
			} else {
				$width  = $settings['width'];
				$height = $settings['height'];
			}
		}

		$view_box = '0 0 ' . intval( $width ) . ' ' . intval( $height );
		$props    = self::get_svg_attrs(
			[
				'fill'                => esc_attr( $color ),
				'preserveAspectRatio' => 'none',
				'viewBox'             => esc_attr( $view_box ),
				'height'              => esc_attr( $height ),
				'width'               => esc_attr( $width ),
			]
		);

		return $escape ? self::encode_svg_for_data_uri( "<svg {$props}>{$content}</svg>" ) : "<svg {$props}>{$content}</svg>";
	}

	/**
	 * Helper function to format SVG attributes.
	 *
	 * This function adds `xmlns => http://www.w3.org/2000/svg` attribute to the provided props.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-mask-and-pattern-library/variables/getSvgAttrs getSvgAttrs}
	 * in `@divi/mask-and-pattern-library` package.
	 *
	 * @since ??
	 *
	 * @param array $props {
	 *   SVG props.
	 *
	 *   @type string $key   Prop value key.
	 *   @type mixed  $value Prop value.
	 * }
	 *
	 * @return string
	 */
	public static function get_svg_attrs( array $props ): string {
		$result = '';
		$attrs  = array_merge(
			$props,
			[
				'xmlns' => 'http://www.w3.org/2000/svg',
			]
		);

		foreach ( $attrs as $name => $value ) {
			$result .= ' ' . $name . '="' . esc_attr( $value ) . '"';
		}

		return $result;
	}
}
