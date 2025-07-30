<?php
/**
 * Video: VideoController.
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Video;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Video REST Controller class.
 *
 * @since ??
 */
class VideoCoverController extends RESTController {

	/**
	 * Retrieve the image output based on the given image source.
	 *
	 * This function accepts a `WP_REST_Request` object which contains the image source parameter.
	 * It retrieves the image source from the request parameter and assigns it to the $args variable.
	 * The `$image_output` variable is initially empty.
	 * If the image source is not empty, the function calls the `\et_pb_set_video_oembed_thumbnail_resolution()` function to set the video oembed thumbnail resolution to 'high' and assigns the result to the $image_output variable.
	 * Finally, the function returns a `WP_REST_Response` object with the image output.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object with the image output.
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$image_src    = $request->get_param( 'src' );
		$args         = [
			'image_src' => $image_src,
		];
		$image_output = '';

		if ( '' !== $args['image_src'] ) {
			$image_output = \et_pb_set_video_oembed_thumbnail_resolution( $args['image_src'], 'high' );
		}

		return self::response_success( $image_output );
	}

	/**
	 * Get the index action arguments.
	 *
	 * This method returns an array of arguments that can be used in the `register_rest_route()` function
	 * to define the necessary parameters for the index action
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
	 * Checks if the current user has permission to use the VisualBuilder (VB).
	 * This function is used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool Whether the current user has permission to use the VisualBuilder (VB).
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
