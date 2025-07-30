<?php
/**
 * REST: EmailServiceController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\EmailService;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Services\EmailAccountService\EmailAccountService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * EmailServiceController class.
 *
 * @since ??
 */
class EmailServiceController extends RESTController {

		/**
		 * Create a new account for a email service provider.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request REST request object.
		 *
		 * @return WP_REST_Response Returns the REST response object.
		 */
	public static function create( WP_REST_Request $request ): WP_REST_Response {
		return self::response_success( EmailAccountService::create_account( $request->get_param( 'provider' ), $request->get_param( 'account' ), $request->get_param( 'data' ) ) );
	}

	/**
	 * Get the arguments for the create action.
	 *
	 * This function returns an array that defines the arguments for the create action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the create action.
	 */
	public static function create_args(): array {
		return [
			'provider' => [
				'description'       => esc_html__( 'The provider slug.', 'et_builder' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'account'  => [
				'description'       => esc_html__( 'The account name.', 'et_builder' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'data'     => [
				'description'       => esc_html__( 'The account data.', 'et_builder' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => function( array $value, WP_REST_Request $request, string $param ) {
					return EmailAccountService::validate_account_data( $request->get_param( 'provider' ), $request->get_param( 'account' ), $value );
				},
				'sanitize_callback' => function( array $value, WP_REST_Request $request, string $param ):array {
					return EmailAccountService::sanitize_account_data( $request->get_param( 'provider' ), $request->get_param( 'account' ), $value );
				},
			],
		];
	}

	/**
	 * Provides the permission status for the create action.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 */
	public static function create_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

	/**
	 * Delete an account for a email service provider.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object.
	 */
	public static function delete( WP_REST_Request $request ): WP_REST_Response {
		return self::response_success( EmailAccountService::delete_account( $request->get_param( 'provider' ), $request->get_param( 'account' ) ) );
	}

	/**
	 * Get the arguments for the delete action.
	 *
	 * This function returns an array that defines the arguments for the delete action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the delete action.
	 */
	public static function delete_args(): array {
		return [
			'provider' => [
				'description'       => esc_html__( 'The provider slug.', 'et_builder' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'account'  => [
				'description'       => esc_html__( 'The account ID.', 'et_builder' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Provides the permission status for the delete action.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 */
	public static function delete_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

		/**
		 * Read account for specific email service provider.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request REST request object.
		 *
		 * @return WP_REST_Response Returns the REST response object.
		 */
	public static function read( WP_REST_Request $request ): WP_REST_Response {
		return self::response_success( EmailAccountService::fetch_account( $request->get_param( 'provider' ), $request->get_param( 'account' ) ) );
	}

	/**
	 * Get the arguments for the read action.
	 *
	 * This function returns an array that defines the arguments for the read action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the read action.
	 */
	public static function read_args(): array {
		return [
			'provider' => [
				'description'       => esc_html__( 'The provider slug.', 'et_builder' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'account'  => [
				'description'       => esc_html__( 'The account name.', 'et_builder' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Provides the permission status for the read action.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 */
	public static function read_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
