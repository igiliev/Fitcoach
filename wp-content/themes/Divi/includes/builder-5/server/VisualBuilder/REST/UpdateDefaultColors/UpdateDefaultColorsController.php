<?php
/**
 * REST: UpdateDefaultColorsController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\UpdateDefaultColors;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;


/**
 * UpdateDefaultColorsController class.
 *
 * REST API controller responsible for updating the default colors.
 *
 * @since ??
 */
class UpdateDefaultColorsController extends RESTController {

	/**
	 * Updates divi default colors option.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response
	 */
	/**
	 * Update the default colors for the Divi color palette.
	 *
	 * This function takes a WP_REST_Request object as a parameter and updates the default colors for the
	 * Divi color palette based on the `default_colors` parameter of the request.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $default_colors A comma-separated list of color values.
	 * }
	 *
	 * @return WP_REST_Response Returns a REST response object containing the updated Divi color palette.
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/update-color-palette' );
	 * $request->set_param( 'default_colors', 'blue,red,green' );
	 *
	 * $response = UpdateDefaultColors::update( $request );
	 *
	 * echo $response->get_data();
	 * // Output: "blue|red|green"
	 * ```
	 */
	public static function update( WP_REST_Request $request ): WP_REST_Response {
		$default_colors = $request->get_param( 'default_colors' );
		$default_colors = str_replace( ',', '|', $default_colors );

		et_update_option( 'divi_color_palette', $default_colors );

		return self::response_success( et_get_option( 'divi_color_palette', false ) );
	}

	/**
	 * Retrieve the arguments for the update action endpoint.
	 *
	 * This function returns an associative array of arguments that are used
	 * in `register_rest_route()` to define the endpoint parameters. The
	 * arguments are used for updating a post.
	 *
	 * @since ??
	 *
	 * @return array An associative array of arguments for the update action endpoint.
	 *
	 * @example:
	 * ```php
	 * $args = UpdateDefaultColors::update_args();
	 * // Returns an associative array of arguments for the update action endpoint.
	 * ```
	 */
	public static function update_args(): array {
		return [
			'default_colors' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Update action permission for a post.
	 *
	 * Checks if the current user has permission to update the post with the given ID and status.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $post_id      The ID of the post to check permission for.
	 *     @type string $post_status  The status of the post to check permission for.
	 * }
	 *
	 * @return bool|WP_Error Returns `true` if the current user has permission, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 *     $request = new WP_REST_Request();
	 *     $request->set_param( 'post_id', $post_id );
	 *     $request->set_param( 'post_status', $post_status );
	 *
	 *     $result = UpdateDefaultColors::update_permission( $request );
	 * ```
	 */
	public static function update_permission( WP_REST_Request $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}



}
