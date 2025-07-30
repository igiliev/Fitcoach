<?php
/**
 * Module Library: WooCommerce Product Stock Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductStock;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductStock\WooCommerceProductStockModule;
use WP_REST_Request;
use WP_REST_Response;

/**
 * WooCommerce Product Stock REST Controller class.
 *
 * @since ??
 */
class WooCommerceProductStockController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Product Stock module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object containing the rendered HTML.
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$product_id = $request->get_param( 'productId' );

		$args = empty( $product_id ) ? [] : [ 'product' => $product_id ];

		// Retrieve the product stock using the WooCommerceProductStockModule class.
		$stock_html = WooCommerceProductStockModule::get_stock( $args );

		$response = [
			'html' => $stock_html,
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
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					$param = sanitize_text_field( $param );

					return ( 'current' !== $param && 'latest' !== $param ) ? absint( $param ) : $param;
				},
				'validate_callback' => function( $param ) {
					if ( 'current' === $param || 'latest' === $param ) {
						return $param;
					}

					if ( $param <= 0 ) {
						return self::response_error(
							'product_not_found',
							__( 'Product not found', 'divi' ),
							[ 'code' => 'product_not_found' ],
							404
						);
					}

					return true;
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
