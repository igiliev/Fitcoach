<?php
/**
 * Conditions: PostMetaFieldsRESTController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Conditions\RESTControllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * PostMetaFields REST Controller class.
 *
 * @since ??
 */
class PostMetaFieldsRESTController extends RESTController {

	/**
	 * Retrieves an array of PostMetaFields and their corresponding label and values.
	 *
	 * This function retrieves and filters the list of PostMetaFields from the WordPress wp_postmeta database table.
	 *
	 * This function runs the value through `divi_module_options_conditions_post_meta_fields` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the post meta fields information.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $post_metas = PostMetaFieldsRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$data        = [];
		$post_id     = $request->get_param( 'postId' );
		$meta_fields = get_post_meta( $post_id );

		/**
		 * Filters included post meta fields.
		 *
		 * @since ??
		 *
		 * @param array $meta_fields Array of post meta fields to include.
		 */
		$meta_fields = apply_filters( 'et_builder_ajax_get_post_meta_fields', $meta_fields );

		$data = is_array( $meta_fields ) ? $meta_fields : [];

		return self::response_success( $data );
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
	 *               This function always returns `[]`.
	 */
	public static function index_args(): array {
		return [
			'postId' => [
				'type'              => 'integer',
				'default'           => 'post',
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
	 *              This function always returns `true`.
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
