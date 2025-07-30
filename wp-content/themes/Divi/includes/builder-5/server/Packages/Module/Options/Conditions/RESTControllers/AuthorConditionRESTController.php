<?php
/**
 * Conditions: AuthorConditionRESTController class.
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
 * Author Condition REST Controller class.
 *
 * @since ??
 */
class AuthorConditionRESTController extends RESTController {

	/**
	 * Retrieves the list of authors.
	 *
	 * This function retrieves the list of authors based on the provided roles.
	 * It filters the authors based on the roles that have the capability to publish posts.
	 * It then applies the 'divi_module_options_conditions_author_condition_roles' filter to
	 * allow third party to change the list.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the list of authors and pagination information.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $request->set_param( 'page', 1 );
	 *  $user_roles = AuthorConditionRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$current_page = $request->get_param( 'page' );
		$per_page     = 10;
		$users_data   = [];
		$role__in     = [];

		foreach ( wp_roles()->roles as $role_slug => $role ) {
			if ( ! empty( $role['capabilities']['publish_posts'] ) ) {
				$role__in[] = $role_slug;
			}
		}

		/**
		 * Filters included authors based on provided roles.
		 *
		 * @since ??
		 *
		 * @param array $role__in Array of roles to include.
		 */
		$role__in = apply_filters( 'et_builder_ajax_get_authors_included_roles', $role__in );

		$user_query  = new \WP_User_Query(
			[
				'role__in' => $role__in,
				'fields'   => [ 'ID', 'user_login' ],
				'number'   => $per_page,
				'paged'    => 1,
			]
		);
		$found_users = $user_query->get_results();

		if ( ! empty( $found_users ) ) {
			$users_data = array_map(
				function( $item ) {
					return [
						'label' => wp_strip_all_tags( $item->user_login ),
						'value' => $item->ID,
					];
				},
				$found_users
			);
		}

		$total       = $user_query->get_total();
		$pages_total = max( $total / $per_page, 1 );

		$data                       = [
			'authors' => $users_data,
			'meta'    => [],
		];
		$data['meta']['pagination'] = array(
			'authors' => array(
				'perPage' => (int) $per_page,
				'total'   => (int) $total,
			),
			'pages'   => array(
				'current' => (int) $current_page,
				'total'   => (int) $pages_total,
			),
		);

		/**
		 * Filters authors response data.
		 *
		 * @since ??
		 *
		 * @param array $data Array of authors to include.
		 */
		$data = apply_filters( 'divi_module_options_conditions_authors', $data );

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
	 */
	public static function index_args(): array {
		return [
			'page' => [
				'type'              => 'integer',
				'default'           => 0,
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					$sanitized_value = (int) $value;
					$sanitized_value = max( $sanitized_value, 1 );
					return $sanitized_value;
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
