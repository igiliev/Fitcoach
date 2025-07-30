<?php
/**
 * Module: BackgroundUtils class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * A utility class for working with backgrounds.
 *
 * This class provides various methods and traits for getting background mask selectors,
 * getting background pattern selectors, and checking if a background has a video.
 *
 * @since ??
 */
class BackgroundUtils {

	/**
	 * Returns CSS Selectors for Background Pattern.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetBackgroundPatternSelectors getBackgroundPatternSelectors} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param string $selector CSS Selector.
	 *
	 * @return array[] Pattern Selectors.
	 *
	 * @example
	 * ```php
	 * GetBackgroundPatternSelectors = BackgroundUtils::has_background_video('.module-selector');
	 * ```
	 */
	public static function get_background_pattern_selectors( string $selector ): array {
		$selector_array = explode( ',', $selector );
		$selectors      = [
			'value' => [],
			'hover' => [],
		];

		if ( $selector_array ) {
			foreach ( $selector_array as $item ) {
				$selectors['value'][] = sanitize_text_field( $item ) . ' > .et_pb_background_pattern';
				// In VB, the hover selector is `.example.et_vb_hover > .et_pb_background_pattern`, however for the
				// frontend, we need to have `.example:hover > .et_pb_background_pattern` so it would work properly.
				$selectors['hover'][] = sanitize_text_field( $item ) . ':hover > .et_pb_background_pattern';
			}
		}

		return [
			'desktop' => [
				'value' => join( ',', $selectors['value'] ),
				'hover' => join( ',', $selectors['hover'] ),
			],
		];
	}

	/**
	 * Returns CSS Selectors for Background Mask.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetBackgroundMaskSelectors getBackgroundMaskSelectors} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param string $selector CSS Selector.
	 *
	 * @return array[] Mask Selectors.
	 *
	 * @example
	 * ```php
	 * $backgroundMaskSelectors = BackgroundUtils::has_background_video('.module-selector');
	 * ```
	 */
	public static function get_background_mask_selectors( string $selector ): array {
		$selector_array = explode( ',', $selector );
		$selectors      = [
			'value' => [],
			'hover' => [],
		];

		if ( $selector_array ) {
			foreach ( $selector_array as $item ) {
				$selectors['value'][] = sanitize_text_field( $item ) . ' > .et_pb_background_mask';
				// In VB, the hover selector is `.example.et_vb_hover > .et_pb_background_mask`, however for the
				// frontend, we need to have `.example:hover > .et_pb_background_mask` so it would work properly.
				$selectors['hover'][] = sanitize_text_field( $item ) . ':hover > .et_pb_background_mask';
			}
		}

		return [
			'desktop' => [
				'value' => join( ',', $selectors['value'] ),
				'hover' => join( ',', $selectors['hover'] ),
			],
		];
	}

	/**
	 * Check if background video is enabled.
	 *
	 * This function checks whether a background video is enabled based on the given attributes.
	 *
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/HasBackgroundVideo hasBackgroundVideo} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $attr The attributes of the background group.
	 *
	 * @return bool Returns true if a background video is enabled, false otherwise.
	 *
	 * @example
	 * ```php
	 * $attr = array(
	 *   'desktop' => array(
	 *     'hover' => array(
	 *       'video' => array(
	 *         'mp4' => 'video.mp4',
	 *         'webm' => 'video.webm',
	 *       ),
	 *     ),
	 *   ),
	 * );
	 *
	 * $backgroundVideoEnabled = BackgroundUtils::has_background_video($attr);
	 * ```
	 */
	public static function has_background_video( array $attr ): bool {
		// Bail early if attr is not available.
		if ( empty( $attr ) ) {
			return false;
		}

		// Loop over the BackgroundGroupAttr.
		foreach ( $attr as $breakpoint ) {
			// Loop over the breakpoint.
			foreach ( $breakpoint as $state ) {
				$mp4  = $state['video']['mp4'] ?? '';
				$webm = $state['video']['webm'] ?? '';

				// Check if background video is enabled.
				if ( ! empty( $mp4 ) || ! empty( $webm ) ) {
					return true;
				}
			}
		}

		// If we reached this point, it means no background video is enabled anywhere.
		return false;
	}
}
