<?php
/**
 * Video: VideoController.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Video;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET_Builder_Post_Features;
use WP_REST_Request;
use WP_Error;
use WP_REST_Response;

/**
 * Video REST Controller class.
 *
 * @since ??
 */
class VideoThumbnailController extends RESTController {

	/**
	 * Gets Embedded Video Thumbnail and Cover Image.
	 *
	 * @since ??
	 *
	 * @param array $args  Video arguments.
	 *
	 * @return array Video Thumbnail and Cover Image.
	 */
	public static function get_video_thumbnail( array $args = array() ): array {
		$defaults = array(
			'image_src' => '',
			'src'       => '',
		);

		$args = array_merge( $defaults, $args );

		$thumbnail_output = '';
		$cover_output     = '';

		if ( empty( $args['src'] ) && empty( $args['image_src'] ) ) {
			return [
				'thumbnail' => $thumbnail_output,
				'cover'     => $cover_output,
			];
		}

		// Get the instance of ET_Builder_Post_Features.
		$post_features = ET_Builder_Post_Features::instance();
		$cache_key     = md5( 'get_video_thumbnail_' . $args['image_src'] . $args['src'] );

		// Get the $video_cover_url from the cache.
		$response = $post_features->get(
			// Cache key.
			$cache_key,
			// Callback function if the cache key is not found.
			function() use ( $args ) {
				if ( ! empty( $args['image_src'] ) ) {
					$thumbnail_output = $args['image_src'];
					$cover_output     = et_pb_set_video_oembed_thumbnail_resolution( $args['image_src'], 'high' );
				} else {
					if ( false !== et_pb_check_oembed_provider( esc_url( $args['src'] ) ) ) {
						add_filter( 'oembed_dataparse', 'et_pb_video_oembed_data_parse', 10, 3 );
						// Get Video thumbnail.
						$thumbnail_output = et_builder_get_oembed( esc_url( $args['src'] ), 'image', true );
						// Get Video Cover Image.
						if ( ! empty( $thumbnail_output ) ) {
							$cover_output = et_pb_set_video_oembed_thumbnail_resolution( $thumbnail_output, 'high' );
						}
						remove_filter( 'oembed_dataparse', 'et_pb_video_oembed_data_parse', 10, 3 );
					} elseif ( false !== VideoHTMLController::validate_youtube_url( esc_url( $args['src'] ) ) ) {
						$args['src'] = VideoHTMLController::normalize_youtube_url( esc_url( $args['src'] ) );
						add_filter( 'oembed_dataparse', 'et_pb_video_oembed_data_parse', 10, 3 );
						// Get Video thumbnail.
						$thumbnail_output = et_builder_get_oembed( esc_url( $args['src'] ), 'image', true );
						// Get Video Cover Image.
						if ( ! empty( $thumbnail_output ) ) {
							$cover_output = et_pb_set_video_oembed_thumbnail_resolution( $thumbnail_output, 'high' );
						}
						remove_filter( 'oembed_dataparse', 'et_pb_video_oembed_data_parse', 10, 3 );
					}
				}

				return [
					'thumbnail' => $thumbnail_output,
					'cover'     => $cover_output,
				];
			},
			// Cache group.
			'get_video_thumbnail',
			// Whether to forcefully update the cache,
			// in this case we are setting to true, because we want to update the cache,
			// even if the item is not found, so that we don't have to make the same
			// query again and again.
			true
		);

		if ( ! is_array( $response ) ) {
			$response = [
				'thumbnail' => $thumbnail_output,
				'cover'     => $cover_output,
			];
		}

		return $response;
	}

	/**
	 * Gets Embedded Video URL.
	 *
	 * @param array $args  Video arguments.
	 *
	 * @since ??
	 *
	 * @return string OEmbed Video URL.
	 */
	public static function get_oembed_video_url( $args = array() ) {
		$defaults = array(
			'src' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( false !== VideoHTMLController::validate_youtube_url( esc_url( $args['src'] ) ) ) {
			$args['src'] = VideoHTMLController::normalize_youtube_url( esc_url( $args['src'] ) );
		}

		// Save thumbnail.
		$thumbnail_track_output = et_builder_get_oembed( esc_url( $args['src'] ), 'image', true );

		return $thumbnail_track_output;
	}

	/**
	 * Video module REST API callback.
	 *
	 * @param WP_REST_Request $request WP Core class used to implement a REST request object. Contains data from the request.
	 * @see https://developer.wordpress.org/reference/classes/wp_rest_request/
	 *
	 * @return string|boolean valid oembed url or false if oembed url not found.
	 */
	public static function validate_video_is_oembed( $request ) {
		$args = [
			'src' => $request->get_param( 'src' ),
		];

		return self::validate_oembed_url( $args );
	}

	/**
	 * Get valid oembed video url if video found as valid oembed.
	 *
	 * @param array $args Video arguments.
	 *
	 * @since ??
	 *
	 * @return string|boolean valid oembed video url or false if oembed url not found.
	 */
	public static function validate_oembed_url( $args = array() ) {
		$defaults    = [
			'src' => '',
		];
		$args        = wp_parse_args( $args, $defaults );
		$args['src'] = self::get_oembed_url( $args['src'] );

		return et_pb_check_oembed_provider( esc_url( $args['src'] ) );
	}

	/**
	 * Get normalized video oembed url from the video url.
	 *
	 * @param string $url video url.
	 *
	 * @since ??
	 *
	 * @return string normalized video oembed url.
	 */
	public static function get_oembed_url( $url ) {
		if ( false !== VideoHTMLController::validate_youtube_url( esc_url( $url ) ) ) {
			return VideoHTMLController::normalize_youtube_url( esc_url( $url ) );
		}
		return $url;
	}

	/**
	 * Index function for retrieving the image src of a video from a given URL.
	 *
	 * This function takes a `WP_REST_Request` object as its parameter and retrieves
	 * the video URL from the `'src'` parameter. It then checks if the URL can be `oembedded`
	 * using the WordPress function `'wp_oembed_get'`. If the URL can be oembedded,
	 * the function adds a filter `'oembed_dataparse'` to parse the data and retrieve
	 * the image thumbnail. It then saves the thumbnail URL to the `$image_src` variable.
	 * After saving the thumbnail, the function removes the filter `'oembed_dataparse'`
	 * to revert back to the normal behavior.
	 *
	 * If the `'wp_oembed_get'` function returns false for the video URL,
	 * the function returns a response error with the message
	 * `'Invalid parameter(s): Unable to fetch the embed HTML for the provided URL.'`.
	 *
	 * This function makes use of the `et_pb_video_oembed_data_parse` function and removes the `et_pb_video_oembed_data_parse` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Returns a `WP_REST_Response` object containing the image src on success, or a `WP_Error` on failure.
	 *
	 * @example:
	 * ```php
	 * // Usage example in a class that uses the trait containing this function.
	 * $video_url = 'https://www.example.com/video';
	 * $request = new WP_REST_Request();
	 * $request->set_param( 'src', $video_url );
	 * $result = MyClass::index( $request );
	 *
	 * if ( is_array( $result ) ) {
	 *    echo $result['image_src'];
	 * } else {
	 *    echo $result->get_error_message();
	 * }
	 * ```
	 */
	public static function index( WP_REST_Request $request ) {
		$video_url = $request->get_param( 'src' );

		if ( false !== wp_oembed_get( $video_url ) ) {
			// Get image thumbnail.
			add_filter( 'oembed_dataparse', 'et_pb_video_oembed_data_parse', 10, 3 );
			// Save thumbnail.
			$image_src = wp_oembed_get( $video_url );
			// Set back to normal.
			remove_filter( 'oembed_dataparse', 'et_pb_video_oembed_data_parse', 10 );
			if ( false === $image_src ) {
				return self::response_error( 'rest_invalid_param', 'Invalid parameter(s): Unable to fetch the embed HTML for the provided URL.' );
			}
			return self::response_success( $image_src );
		}

		return self::response_error( 'rest_invalid_param', 'Invalid parameter(s): Unable to fetch the embed HTML for the provided URL.' );
	}

	/**
	 * Get the index action arguments.
	 *
	 * This method returns an array of arguments that can be used in the `register_rest_route()` function
	 * to define the necessary parameters for the index action
	 * The index action allows the user to retrieve dynamic content options based on the provided postId parameter.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'src' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => function( $value, $request, $param ) {
					return esc_url( sanitize_text_field( $value ) );
				},
				'validate_callback' => function ( $value, $request, $key ) {
					// When value is set, validate it's a URL, or, an empty string.
					return isset( $key ) && ! empty( $value )
						? wp_http_validate_url( $value )
						: isset( $key ) && is_string( $value );
				},
			],
		];
	}

	/**
	 * Index action permission.
	 *
	 * This function checks if the current user has permission to use the VisualBuilder (VB).
	 * The function is used as an endpoint permission callback in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has permission to use the VisualBuilder (VB), `false` otherwise.
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
