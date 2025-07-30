<?php
/**
 * Conditions: UserRoleConditionRESTController class.
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
 * User Role Condition REST Controller class.
 *
 * @since ??
 */
class UserRoleConditionRESTController extends RESTController {

	/**
	 * Retrieves an array of user roles and their corresponding labels and values.
	 *
	 * This function retrieves and filters the list of user roles from the WordPress roles database table.
	 * Each role is represented as an array with the 'label' key representing the role name
	 * and the 'value' key representing the role ID.
	 *
	 * This function runs the value through `divi_module_options_conditions_user_role_condition_roles` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the user roles information.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $user_roles = UserRoleConditionRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$data = [];

		foreach ( wp_roles()->roles as $key => $value ) {
			$data[] = [
				'label' => wp_strip_all_tags( $value['name'] ),
				'value' => (string) $key,
			];
		}

		/**
		 * Filters user roles response data.
		 *
		 * @since ??
		 *
		 * @param array $data Array of user roles to include.
		 */
		$data = apply_filters( 'et_builder_ajax_get_user_roles_included_roles', $data );

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
