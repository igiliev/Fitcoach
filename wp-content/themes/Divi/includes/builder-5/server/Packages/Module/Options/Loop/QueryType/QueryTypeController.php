<?php
/**
 * Loop QueryType: QueryTypeController.
 *
 * @package Builder\Packages\Module\Options\Loop\QueryType
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Loop\QueryType;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Query Type REST Controller class.
 *
 * @since ??
 */
class QueryTypeController extends RESTController {
	/**
	 * Return query types for the Query module along with their taxonomies and user roles.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$post_types = et_get_registered_post_type_options( false, false );

		$post_type_list    = [];
		$post_taxonomy_map = [];

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $post_type_label ) {
				$post_type_list[ $post_type ] = $post_type_label;

				// Get taxonomies for each post type.
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );

				$taxonomy_list = [];
				foreach ( $taxonomies as $taxonomy ) {
					$taxonomy_list[ $taxonomy->name ] = $taxonomy->label;
				}

				$post_taxonomy_map[ $post_type ] = $taxonomy_list;
			}
		}

		// Get all user roles.
		global $wp_roles;
		$user_roles = [];

		if ( isset( $wp_roles ) && ! empty( $wp_roles->roles ) ) {
			foreach ( $wp_roles->roles as $role_key => $role_data ) {
				$user_roles[ $role_key ] = $role_data['name'];
			}
		}

		return self::response_success(
			[
				'post_types'      => $post_type_list,
				'user_roles'      => $user_roles,
				'post_taxonomies' => $post_taxonomy_map,
			]
		);
	}

	/**
	 * Index action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @return array
	 */
	public static function index_args(): array {
		return [];
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
};
