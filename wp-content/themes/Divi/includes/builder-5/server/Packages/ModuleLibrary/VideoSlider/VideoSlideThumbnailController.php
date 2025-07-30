<?php
/**
 * Video Slider: VideoSlideThumbnailController.
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\VideoSlider;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleLibrary\Video\VideoHTMLController;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET_Builder_Post_Features;
use WP_REST_Request;
use WP_REST_Response;

/**
 * VideoSlideThumbnail REST Controller class.
 *
 * @since ??
 */
class VideoSlideThumbnailController extends RESTController {

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
				$thumbnail_output = '';
				$cover_output     = '';

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
	 * Return VideoSlider module Video Slide Thumbnail.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Fetch video slide thumbnail URL.
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$image_src     = $request->get_param( 'src' );
		$thumbnail_src = $request->get_param( 'thumbnailSrc' );
		$args          = [
			'src'       => $image_src,
			'image_src' => $thumbnail_src,
		];

		$output = self::get_video_thumbnail( $args );

		return self::response_success( $output );
	}

	/**
	 * Index action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @return array
	 */
	public static function index_args(): array {
		return [
			'src'          => [
				'default'           => '',
				'type'              => 'string',
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
			'thumbnailSrc' => [
				'default'           => '',
				'type'              => 'string',
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
	 * Endpoint permission callback as used in `register_rest_route()`.
	 *
	 * @return bool
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
