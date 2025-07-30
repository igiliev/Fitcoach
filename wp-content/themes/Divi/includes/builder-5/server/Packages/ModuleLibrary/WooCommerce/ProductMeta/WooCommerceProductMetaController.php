<?php
/**
 * Module Library: WooCommerce Product Meta Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductMeta;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\WooCommerce\WooCommerceUtils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * WooCommerce Product Meta REST Controller class.
 *
 * @since ??
 */
class WooCommerceProductMetaController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Product Meta module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Returns the REST response object containing the rendered HTML.
	 */
	public static function index( WP_REST_Request $request ) {
		$params     = $request->get_params();
		$product_id = $params['productId'] ?? 'current';

		// This will convert numeric IDs to an actual product ID.
		$product = WooCommerceUtils::get_product( $product_id );
		if ( ! $product ) {
			// Warn if not a valid product ID.
			return self::response_error(
				'product_not_found',
				__( 'Product not found.', 'divi' ),
				[ 'status' => 404 ],
				404
			);
		}

		// Set the product ID for the get_meta function.
		$args = [
			'product' => $product->get_id(),
		];

		// Get the meta HTML using our get_meta method.
		$meta_html = WooCommerceProductMetaModule::get_meta( $args );

		$response = [
			'html' => $meta_html,
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
			'productId' => [
				'required'          => false,
				'description'       => __( 'Product ID.', 'divi' ),
				'sanitize_callback' => function( $param ) {
					$param = sanitize_text_field( $param );
					return ( 'current' !== $param && 'latest' !== $param ) ? absint( $param ) : $param;
				},
				'validate_callback' => function( $param, $request, $key ) {
					return 'current' === $param || 'latest' === $param || is_numeric( $param );
				},
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
