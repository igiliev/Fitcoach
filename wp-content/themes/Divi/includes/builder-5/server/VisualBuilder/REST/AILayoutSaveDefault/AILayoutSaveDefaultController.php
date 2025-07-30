<?php
/**
 * REST: AILayoutSaveDefaultController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\AILayoutSaveDefault;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;


/**
 * AILayoutSaveDefaultController class.
 *
 * This class handles the REST API request to save the AI default layout.
 *
 * The AI default layout is the layout that is used when the user creates a new page.
 *
 * @since ??
 */
class AILayoutSaveDefaultController extends RESTController {
	/**
	 * Update the default colors for the Divi color palette.
	 *
	 * This function takes a WP_REST_Request object as a parameter and updates the default colors for the
	 * Divi color palette based on the parameters of the request.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $heading_font The font for headings.
	 *     @type string $body_font The font for body text.
	 *     @type string $heading_font_color The color for heading fonts.
	 *     @type string $body_font_color The color for body fonts.
	 *     @type string $primary_color The primary color.
	 *     @type string $secondary_color The secondary color.
	 *     @type string $site_description The site description.
	 * }
	 *
	 * @return WP_REST_Response Returns a REST response object containing the updated Divi color palette.
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/update-color-palette' );
	 * $request->set_param( 'heading_font', 'Arial' );
	 * $request->set_param( 'body_font', 'Helvetica' );
	 * $request->set_param( 'heading_font_color', '#000000' );
	 * $request->set_param( 'body_font_color', '#333333' );
	 * $request->set_param( 'primary_color', '#ff0000' );
	 * $request->set_param( 'secondary_color', '#00ff00' );
	 * $request->set_param( 'site_description', 'This is a sample site description' );
	 *
	 * $response = AILayoutSaveDefaultController::update( $request );
	 *
	 * echo $response->get_data();
	 * ```
	 */
	public static function update( WP_REST_Request $request ): WP_REST_Response {
		$options = [
			'et_ai_layout_heading_font'       => $request->get_param( 'heading_font' ),
			'et_ai_layout_body_font'          => $request->get_param( 'body_font' ),
			'et_ai_layout_heading_font_color' => $request->get_param( 'heading_font_color' ),
			'et_ai_layout_body_font_color'    => $request->get_param( 'body_font_color' ),
			'et_ai_layout_primary_color'      => $request->get_param( 'primary_color' ),
			'et_ai_layout_secondary_color'    => $request->get_param( 'secondary_color' ),
			'et_ai_layout_site_description'   => $request->get_param( 'site_description' ),
		];

		foreach ( $options as $option_name => $option_value ) {
			if ( null !== $option_value ) {
				et_update_option( $option_name, sanitize_text_field( $option_value ) );
			}
		}

		return self::response_success();
	}

	/**
	 * Retrieve the arguments for the update action endpoint.
	 *
	 * This function returns an associative array of arguments that are used
	 * in `register_rest_route()` to define the endpoint parameters. The
	 * arguments are used for updating the AI layout options.
	 *
	 * @since ??
	 *
	 * @return array An associative array of arguments for the update action endpoint.
	 *
	 * @example:
	 * ```php
	 * $args = AILayoutSaveDefaultController::update_args();
	 * // Returns an associative array of arguments for the update action endpoint.
	 * ```
	 */
	public static function update_args(): array {
		return [
			'et_ai_layout_heading_font'       => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_ai_layout_body_font'          => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_ai_layout_heading_font_color' => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_ai_layout_body_font_color'    => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_ai_layout_primary_color'      => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_ai_layout_secondary_color'    => [
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_ai_layout_site_description'   => [
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
