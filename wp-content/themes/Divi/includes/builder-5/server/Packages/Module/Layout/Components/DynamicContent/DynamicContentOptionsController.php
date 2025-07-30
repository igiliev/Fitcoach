<?php
/**
 * Module: DynamicContentOptionsController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;

/**
 * Dynamic Content Options REST Controller class.
 *
 * @since ??
 */
class DynamicContentOptionsController extends RESTController {

	/**
	 * Get dynamic content options.
	 *
	 * Retrieves the options for Dynamic Content and returns a WP_REST_Response object with the options.
	 *
	 * @since ??
	 *
	 * @param \WP_REST_Request $request {
	 *   The REST request object.
	 *
	 *   @type string $postId The ID of the post.
	 * }
	 *
	 * @return \WP_REST_Response|\WP_Error The REST response object with the options,
	 *                                     or a WP_Error object if the request fails.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $request->set_param( 'postId', '123' );
	 *  $response = DynamicContentOptionsController::index( $request );
	 * ```
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request();
	 * $request->set_param( 'postId', '456' );
	 * $response = DynamicContentOptionsController::index( $request );
	 * ```
	 */
	public static function index( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = $request->get_param( 'postId' );

		$options = DynamicContentOptions::get_options( $post_id, 'display' );

		return self::response_success( $options );
	}

	/**
	 * Get the arguments for the index action.
	 *
	 * This function returns an array that defines the arguments for the index action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'postId' => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) $value;
				},
			],
		];
	}

	/**
	 * Get the permission status for the index action.
	 *
	 * This function checks if the current user has the permission to use the Visual Builder.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
