<?php
/**
 * DividerUtils class
 *
 * This class is equivalent of JS package:
 * {@link /docs/category/dividerlibrary @divi/divider-library}
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Packages\DividerLibrary\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Packages\DividerLibrary\DividerSvg\DividerSvg;
use Exception;

/**
 * DividerUtils class.
 *
 * This class is equivalent of JS package:
 * {@link /docs/category/dividerlibrary @divi/divider-library}
 *
 * @since ??
 */
class DividerUtils {

	/**
	 * Helper function to encode the SVG with encodeURIComponent.
	 *
	 * Use it before passing SVG to data URI, so that browser can render it correctly.
	 * Note: we don't decode it anywhere, the browser does that.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/divider-library/encode-svg-for-data-uri encodeSvgForDataURI}
	 * in `@divi/divider-library` package.
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
		// Match example: https://regex101.com/r/3HVsjS/1.
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
			// Equivalent of encodeURIComponent()
			// Source: https://stackoverflow.com/a/1734255/1482443.
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
	 * Default viewBox Settings for Divider Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-divider-library/functions/getDividerDefaultViewboxSettings getDividerDefaultViewboxSettings}
	 * in `@divi/divider-library` package.
	 *
	 * @since ??
	 *
	 * @return array {
	 *   Divider viewBox settings (array of space-delimited strings).
	 *
	 *   @type string $top    Top divider viewBox settings. Default `0 0 1280 140`.
	 *   @type string $bottom Bottom divider viewBox settings. Default `0 0 1280 140`.
	 * }
	 */
	public static function get_divider_default_viewbox_settings(): array {
		return [
			'top'    => '0 0 1280 140',
			'bottom' => '0 0 1280 140',
		];
	}

	/**
	 * Get SVG Settings for a Divider Style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-divider-library/variables/getDividerJson getDividerJson}
	 * in `@divi/divider-library` package.
	 *
	 * @since ??
	 *
	 * @param string $style_name Divider style name.
	 *
	 * @throws Exception If divider style is not found.
	 *
	 * @return array An array of divider style data, or `null` if the style name is not found.
	 */
	public static function get_divider_json( string $style_name ): ?array {
		$divider_settings = self::get_divider_settings();
		$divider_style    = ArrayUtility::find(
			$divider_settings,
			function( $style ) use ( $style_name ) {
				return $style['name'] === $style_name;
			}
		);

		return $divider_style ? $divider_style['dataJSON'] : null;
	}

	/**
	 * Divider SVG Settings.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-divider-library/variables/getDividerSettings getDividerSettings}
	 * in `@divi/divider-library` package.
	 *
	 * @since ??
	 *
	 * @throws Exception Throw error when an expected json file does not exist.
	 *
	 * @return array {
	 *   Divider style list.
	 *
	 *   @type string $name     Divider style name.
	 *   @type array  $dataJSON Divider style data.
	 * }
	 */
	public static function get_divider_settings(): array {
		static $settings = null;

		if ( null !== $settings ) {
			return $settings;
		}

		// Default divider styles.
		$default_divider_styles = DividerSvg::default_divider_styles();

		$default_style_name = 'arrow';
		$default_style_data = ArrayUtility::find(
			$default_divider_styles,
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
		 * Filters the divider style list.
		 *
		 * Add custom divider styles to the divider library.
		 *
		 * @since ??
		 *
		 * @param array $default_divider_styles The divider style list.
		 */
		$filtered_styles = apply_filters( 'divi_divider_library_divider', $default_divider_styles );

		// Remove any filter divider styles with duplicate names.
		$filtered_styles = array_unique( $filtered_styles, SORT_REGULAR );

		// Sort the filtered divider styles by their name values.
		uasort(
			$filtered_styles,
			function( $a, $b ) {
				return $a['name'] <=> $b['name'];
			}
		);

		// Sort the filtered divider styles by their priority values, if found.
		// If no priority value is found, use a default priority value of 10.
		uasort(
			$filtered_styles,
			function( $a, $b ) {
				// Check if the style has repeatable attribute, default is true.
				$repeatable_a = (bool) ( $a['repeatable'] ?? true );
				$repeatable_b = (bool) ( $b['repeatable'] ?? true );

				// Set default priority, repeatable styles should be at top.
				$default_priority_a = $repeatable_a ? 10 : 20;
				$default_priority_b = $repeatable_b ? 10 : 20;

				// If priority is not defined, use default priority.
				$priority_a = (int) ( $a['priority'] ?? $default_priority_a );
				$priority_b = (int) ( $b['priority'] ?? $default_priority_b );

				return $priority_a - $priority_b;
			}
		);

		// If the same divider name exists in both the filtered and default
		// divider styles, then add the default divider style to the divider
		// settings list. Otherwise, add the filtered divider style to the
		// divider settings list.
		foreach ( $filtered_styles as $divider_style_name => $divider_style ) {
			if ( array_key_exists( $divider_style_name, $default_divider_styles ) ) {
				// Add the divider style data to the divider style list.
				$settings[] = [
					'name'     => $default_divider_styles[ $divider_style_name ]['name'],
					'dataJSON' => $default_divider_styles[ $divider_style_name ],
				];
			} else {
				// Add the divider style data to the divider style list.
				$settings[] = [
					'name'     => $divider_style_name,
					'dataJSON' => $divider_style,
				];
			}
		}

		return $settings;
	}

	/**
	 * Divider Style Options.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-divider-library/variables/getDividerStyleOptions getDividerStyleOptions}
	 * in `@divi/divider-library` package.
	 *
	 * @since ??
	 *
	 * @throws Exception Throw error when an expected json file does not exist.
	 * @return array {
	 *   Divider style options.
	 *
	 *   @type array $name {
	 *     @type string $name  Divider style name. This comes from the `name` property in the divider style json file.
	 *     @type string $label Divider style label. This comes from the `label` property in the divider style json file.
	 *   }
	 * }
	 */
	public static function get_divider_style_options(): array {
		static $options = [];

		if ( ! empty( $options ) ) {
			return $options;
		}

		$settings = self::get_divider_settings();

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
	 * SVG for a Divider style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-divider-library/variables/getDividerSvg getDividerSvg}
	 * in `@divi/divider-library` package.
	 *
	 * @since ??
	 *
	 * @param array $divider_svg_params {
	 *   An array of arguments.
	 *
	 *   @type string $style     Divider SVG style.
	 *   @type string $color     Divider SVG color.
	 *   @type string $height    Divider SVG height.
	 *   @type string $placement Divider SVG placement. One of `top` or `bottom`.
	 *   @type boolean $escape   Optional. Divider SVG escape. Default `true`.
	 *   @type boolean $preview  Optional. Whether previewing divider SVG. Default `false`.
	 * }
	 *
	 * @throws Exception If divider style is not found.
	 *
	 * @return string If `$escape=true`, the value will be encoded via `DividerUtils::encode_svg_for_data_uri`.
	 */
	public static function get_divider_svg( array $divider_svg_params ): string {
		$style     = $divider_svg_params['style'];
		$color     = $divider_svg_params['color'];
		$placement = $divider_svg_params['placement'];
		$escape    = $divider_svg_params['escape'] ?? true;
		$preview   = $divider_svg_params['preview'] ?? false;

		if ( ! $style ) {
			return '';
		}

		$settings = self::get_divider_json( $style );

		// Fail early if no divider settings are found for this style.
		if ( ! $settings ) {
			return '';
		}

		// Get default viewBox settings.
		$view_box_settings = self::get_divider_default_viewbox_settings();

		// Set default width and height.
		$default_width  = '100%';
		$default_height = '140px';

		if ( ! isset( $settings['svgContent'][ $placement ] ) ) {
			return '';
		}

		// Get SVG content.
		$content = $settings['svgContent'][ $placement ];

		// Get SVG attributes from the JSON data.
		$svg_width  = $settings['svgDimension'][ $placement ]['width'] ?? null;
		$svg_height = $settings['svgDimension'][ $placement ]['height'] ?? null;
		$view_box   = $settings['svgDimension'][ $placement ]['viewBox'] ?? $view_box_settings[ $placement ];

		// By default, the divider is repeatable, unless the repeatable attribute is set to false.
		$repeatable = (bool) ( $settings['repeatable'] ?? true );

		// Set SVG attributes.
		$props_settings = [
			'fill'                => $color,
			'preserveAspectRatio' => $repeatable ? 'none' : 'xMidYMid slice',
			'width'               => $svg_width ?? $default_width,
			'height'              => $svg_height ?? $default_height,
			'viewBox'             => $preview ? $view_box_settings[ $placement ] : $view_box,
		];

		// For preview, we don't need width and height.
		if ( $preview ) {
			unset( $props_settings['width'] );
			unset( $props_settings['height'] );
		}

		// Get SVG attributes output.
		$props = self::get_svg_attrs( $props_settings );

		return $escape
			? self::encode_svg_for_data_uri( "<svg $props>$content</svg>" )
			: "<svg $props>$content</svg>";
	}

	/**
	 * Helper function to format SVG attributes.
	 *
	 * This function adds `xmlns => http://www.w3.org/2000/svg` attribute to the provided props.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/divider-library/get-svg-attrs getSvgAttrs}
	 * in `@divi/divider-library` package.
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
	 * @return string Provided props formatted as `$key="$value"`.
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
