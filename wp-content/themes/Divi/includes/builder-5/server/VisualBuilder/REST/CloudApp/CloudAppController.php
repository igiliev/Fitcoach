<?php
/**
 * REST: CloudAppController class.
 *
 * @package Builder\VisualBuilder\REST
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\CloudApp;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;

/**
 * Cloud App REST API Controller class.
 *
 * @since ??
 */
class CloudAppController extends RESTController {

	/**
	 * Updates username and api_key options for et account.
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return \WP_REST_Response
	 * @since ??
	 */
	public static function update_account( $request ): \WP_REST_Response {
		$updates_options = get_site_option( 'et_automatic_updates_options', array() );

		$status  = $request->get_param( 'status' );
		$account = array(
			'username' => $request->get_param( 'username' ),
			'api_key'  => $request->get_param( 'apiKey' ),
		);

		update_site_option( 'et_automatic_updates_options', array_merge( $updates_options, $account ) );
		update_site_option( 'et_account_status', $status );

		return self::response_success( array( 'status' => $status ) );
	}

	/**
	 * Update account action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @return array
	 */
	public static function update_account_args(): array {
		return [
			'username' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'apiKey'   => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'status'   => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Update account action permission.
	 *
	 * Endpoint permission callback as used in `register_rest_route()`.
	 *
	 * @return bool
	 */
	public static function update_account_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

}
