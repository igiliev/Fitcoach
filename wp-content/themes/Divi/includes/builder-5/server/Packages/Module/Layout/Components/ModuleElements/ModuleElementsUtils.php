<?php
/**
 * ModuleElementsUtils Class
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Layout\Components\ModuleElements;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET_Builder_Post_Features;

/**
 * ModuleElementsUtils class.
 *
 * @since ??
 */
class ModuleElementsUtils {

	/**
	 * Interpolate a selector template with a value.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/InterpolateSelector interpolateSelector} in
	 * `@divi/module` packages.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $value                  The value to interpolate.
	 *     @type string|array $selectorTemplate The selector template to interpolate.
	 *     @type string $placeholder            Optional. The placeholder to replace. Default `{{selector}}`.
	 * }
	 *
	 * @return string|array The interpolated selector.
	 *                      If the selector template is a string, a string is returned.
	 *                      Otherwise an array is returned.
	 */
	public static function interpolate_selector( array $args ) {
		static $cached = null;

		$cache_key = md5( json_encode( $args ) );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		$value             = $args['value'];
		$selector_template = $args['selectorTemplate'];
		$placeholder       = $args['placeholder'] ?? '{{selector}}';

		if ( is_string( $selector_template ) ) {
			$cached[ $cache_key ] = str_replace( $placeholder, $value, $selector_template );

			return $cached[ $cache_key ];
		}

		$stringify_selector_template = wp_json_encode( $selector_template );

		$updated_selector_template = str_replace( $placeholder, $value, $stringify_selector_template );

		$cached[ $cache_key ] = json_decode( $updated_selector_template, true );

		return $cached[ $cache_key ];
	}

	/**
	 * Extracts the attachment URL from the image source.
	 *
	 * @since ??
	 *
	 * @param string $image_src The URL of the image attachment.
	 * @return array {
	 *    An array containing the image path without the scaling suffix and the query string,
	 *    and the scaling suffix if found.
	 *
	 *    @type string $path   The image path without the scaling suffix and query string.
	 *    @type string $suffix The scaling suffix if found. Otherwise an empty string.
	 * }
	 */
	public static function extract_attachment_url( string $image_src ): array {
		// Remove the query string from the image URL.
		list( $image_src ) = explode( '?', $image_src );

		// If the image source contains a scaling suffix, extract it.
		// The scaling suffix is in the format of "-{width}x{height}.".
		// Regex pattern test: https://regex101.com/r/USnFl3/1.
		if ( strpos( $image_src, 'x' ) && preg_match( '/-\d+x\d+\./', $image_src, $match ) ) {
			return [
				'path'   => str_replace( $match[0], '.', $image_src ),
				'suffix' => $match[0],
			];
		}

		return [
			'path'   => $image_src,
			'suffix' => '',
		];
	}

	/**
	 * Converts an attachment URL to its corresponding ID.
	 *
	 * @since ??
	 *
	 * @param string $image_src The URL of the attachment image.
	 * @return int The ID of the attachment.
	 */
	public static function attachment_url_to_id( string $image_src ): int {
		// If the image source is a data URL, return 0.
		if ( 0 === strpos( $image_src, 'data:' ) ) {
			return 0;
		}

		// Get the instance of ET_Builder_Post_Features.
		$post_features = ET_Builder_Post_Features::instance();

		// Get the attachment ID from the cache.
		$attachment_id = $post_features->get(
			// Cache key.
			$image_src,
			// Callback function if the cache key is not found.
			function() use ( $image_src ) {
				$extracted_image_src = ModuleElementsUtils::extract_attachment_url( $image_src );

				// First attempt to get the attachment ID from the image source URL.
				$attachment_id = attachment_url_to_postid( $extracted_image_src['path'] );

				// If no attachment ID is found and the image source contains a scaling suffix, try to get the attachment ID from the image source with `-scaled.` suffix.
				// This could happens when the uploaded image larger than the threshold size (threshold being either width or height of 2560px), WordPress core system
				// will generate image file name with `-scaled.` suffix.
				//
				// @see https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
				// @see https://wordpress.org/support/topic/media-images-renamed-to-xyz-scaled-jpg/.
				if ( ! $attachment_id && $extracted_image_src['suffix'] ) {
					$attachment_id = attachment_url_to_postid( str_replace( $extracted_image_src['suffix'], '-scaled.', $image_src ) );
				}

				return $attachment_id;
			},
			// Cache group.
			'attachment_url_to_id',
			// Whether to forcefully update the cache,
			// in this case we are setting to true, because we want to update the cache,
			// even if the attachment ID is not found, so that we don't have to make the same
			// query again and again.
			true
		);

		return $attachment_id;
	}

	/**
	 * Populates the image element attributes with additional information.
	 *
	 * This function takes an array of attributes and populates it with additional information
	 * related to the image element, such as the attachment ID, width, height, srcset, and sizes.
	 *
	 * @since ??
	 *
	 * @param array $attrs The array of attributes to be populated.
	 * @return array The updated array of attributes.
	 */
	public static function populate_image_element_attrs( array $attrs ): array {
		// Get the instance of ET_Builder_Post_Features.
		$post_features = ET_Builder_Post_Features::instance();

		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( $states as $state => $state_value ) {
				$image_src     = $state_value['src'] ?? '';
				$attachment_id = 0;

				// Only calculate attachment ID if the image source is a valid URL.
				if ( $image_src ) {
					// First try to get the attachment ID that is provided in the state value.
					$attachment_id = absint( $state_value['id'] ?? 0 );

					if ( ! $attachment_id ) {
						// If the attachment ID is not provided, try to get it from the image source URL.
						$attachment_id = self::attachment_url_to_id( $image_src );
					}
				}

				// Update the attachment ID.
				$attrs[ $breakpoint ][ $state ]['id'] = $attachment_id;

				// Only proceed if the attachment ID is valid.
				if ( $attachment_id ) {
					// Get the $image_meta from the cache.
					$image_meta = $post_features->get(
						// Cache key.
						'attachment_image_meta_' . $attachment_id,
						// Callback function if the cache key is not found.
						function() use ( $attachment_id ) {
							return wp_get_attachment_metadata( $attachment_id );$image_meta;
						},
						// Cache group.
						'attachment_image_meta',
						// Whether to forcefully update the cache,
						// in this case we are setting to true, because we want to update the cache,
						// even if the attachment ID is not found, so that we don't have to make the same
						// query again and again.
						true
					);

					// Only proceed if the image meta is available.
					if ( $image_meta ) {
						$size_array = wp_image_src_get_dimensions( $image_src, $image_meta, $attachment_id );

						// Only proceed if the image size array is available.
						if ( $size_array ) {
							$attrs[ $breakpoint ][ $state ]['width']  = strval( $size_array[0] );
							$attrs[ $breakpoint ][ $state ]['height'] = strval( $size_array[1] );

							// Calculate srcset and sizes if responsive images are enabled.
							if ( et_is_responsive_images_enabled() ) {
								$image_srcset = wp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id );

								if ( is_string( $image_srcset ) ) {
									$attrs[ $breakpoint ][ $state ]['srcset'] = $image_srcset;
								}

								$image_sizes = wp_calculate_image_sizes( $size_array, $image_src, $image_meta, $attachment_id );

								if ( is_string( $image_sizes ) ) {
									$attrs[ $breakpoint ][ $state ]['sizes'] = $image_sizes;
								}
							}
						}
					}
				}
			}
		}

		return $attrs;
	}
}
