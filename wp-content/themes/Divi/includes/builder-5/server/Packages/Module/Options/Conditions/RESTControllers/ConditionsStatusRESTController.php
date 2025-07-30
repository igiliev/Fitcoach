<?php
/**
 * Conditions: ConditionsStatusRESTController class.
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
use ET\Builder\Packages\Module\Options\Conditions\Conditions;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Conditions Status REST Controller class.
 *
 * @since ??
 */
class ConditionsStatusRESTController extends RESTController {

	/**
	 * Stores the enable conditions configuration and returns a REST response.
	 *
	 * This function runs the value through `et_is_display_conditions_functionality_enabled` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing conditions configuration status.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $request->set_param( 'conditions', true );
	 *  $user_roles = UserRoleConditionRESTController::store( $request );
	 * ```
	 */
	public static function store( WP_REST_Request $request ): WP_REST_Response {
		$conditions = $request->get_param( 'conditions' );

		$enabled = true;

		/**
		 * Filters "Conditions Option" functionality to determine whether to enable or disable the functionality.
		 *
		 * Useful for disabling/enabling "Conditions Option" feature site-wide.
		 *
		 * @since ??
		 *
		 * @param boolean $enabled True to enable the functionality, False to disable it.
		 */
		$is_display_conditions_enabled = apply_filters( 'et_is_display_conditions_functionality_enabled', $enabled );

		if ( ! $is_display_conditions_enabled ) {
			self::response_error();
		}

		$status = ( new Conditions() )->is_displayable( $conditions, true );

		if ( ! $status ) {
			self::response_error();
		}

		return self::response_success( $status );
	}

	/**
	 * Get the arguments for the store action.
	 *
	 * This function returns an array that defines the arguments for the store action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the store action.
	 */
	public static function store_args(): array {
		return [
			'conditions' => [
				'type'              => 'array',
				'required'          => true,
				'validate_callback' => function( $param, $request, $key ) {
					return is_array( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return $value;
				},
			],
		];
	}

	/**
	 * Get the permission status for the store action.
	 *
	 * This function checks if the current user has the permission for the store action.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission, `false` otherwise.
	 */
	public static function store_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
