<?php
/**
 * Conditions: PostTypeConditionRESTController class.
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
 * Post Type Condition REST Controller class.
 *
 * @since ??
 */
class PostTypeConditionRESTController extends RESTController {


	/**
	 * Retrieves an array of Post Types and their corresponding labels and values.
	 *
	 * This function retrieves and filters the list of Post Types from the WordPress post database table.
	 * Each Post type is represented as an array with the 'label' key representing the post type label
	 * and the 'value' key representing the post type name.
	 *
	 * This function runs the value through `divi_module_options_conditions_post_type_condition_post_types` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the Post Types information.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $post_types = PostTypeConditionRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		/**
		 * Filters included post types.
		 *
		 * @since ??
		 *
		 * @param array $post_types Array of post types to include.
		 */
		$post_types = apply_filters( 'et_builder_ajax_get_post_types', $post_types );

		$post_types_data = array_map(
			function ( $item ) {
				return [
					'label' => wp_strip_all_tags( $item->labels->name ),
					'value' => $item->name,
				];
			},
			$post_types
		);

		// Reindex the array to ensure it's an array not an object.
		$data = array_values( $post_types_data );

		/**
		 * Filters post types response data.
		 *
		 * @since ??
		 *
		 * @param array $data Array of post types to include.
		 */
		$data = apply_filters( 'divi_module_options_conditions_post_types', $data );

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
		return [];
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
