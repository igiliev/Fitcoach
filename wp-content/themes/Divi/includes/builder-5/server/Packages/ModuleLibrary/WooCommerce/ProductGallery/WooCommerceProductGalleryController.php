<?php
/**
 * Module Library: WooCommerce Product Gallery Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductGallery;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Framework\Utility\HTMLUtility;
use WP_REST_Request;
use WP_REST_Response;

/**
 * WooCommerce Product Gallery REST Controller class.
 *
 * @since ??
 */
class WooCommerceProductGalleryController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Product Gallery module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object containing the rendered HTML.
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$param_1 = $request->get_param( 'param_1' );

		$response = [
			'html' => HTMLUtility::render(
				[
					'tag'      => 'div',
					'children' => $param_1,
				]
			),
		];

		return self::response_success( $response );
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
			'param_1' => [
				'type'      => 'string',
				'required'  => true,
				'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
				'minLength' => 1, // Prevent empty string.
			],
		];
	}

	/**
	 * Provides the permission status for the index action.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the rest endpoint, otherwise `false`.
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
