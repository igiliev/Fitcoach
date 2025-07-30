<?php
/**
 * Module Library: WooCommerce Product Reviews Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductReviews;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductReviews\WooCommerceProductReviewsModule;
use ET\Builder\Packages\WooCommerce\WooCommerceUtils;
use WP_REST_Request;
use WP_REST_Response;

/**
 * WooCommerce Product Reviews REST Controller class.
 *
 * @since ??
 */
class WooCommerceProductReviewsController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Product Reviews module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Returns the REST response object containing the rendered HTML.
	 *                                   If the request is invalid, a `WP_Error` object is returned.
	 */
	public static function index( WP_REST_Request $request ) {
		$product_id = $request->get_param( 'productId' );

		$product = WooCommerceUtils::get_product( $product_id ?? '' );

		if ( ! $product ) {
			return self::response_error(
				'product_not_found',
				__( 'Product not found.', 'divi' ),
				[ 'status' => 404 ],
				404
			);
		}

		$common_required_params = WooCommerceUtils::retrieve_common_required_params_for_woocommerce_html_rendering( $request );

		// If the conditional tags are not set, the returned value is an error.
		if ( ! isset( $common_required_params['conditional_tags'] ) ) {
			return self::response_error( ...$common_required_params );
		}

		$conditional_tags = $common_required_params['conditional_tags'];
		$current_page     = $common_required_params['current_page'];

		$header_level = $request->get_param( 'headerLevel' );

		$args = [ 'product' => $product->get_id() ];

		if ( ! empty( $header_level ) ) {
			$args['header_level'] = $header_level;
		}

		// Retrieve the product reviews using the WooCommerceProductReviewsModule class.
		$reviews_html = WooCommerceProductReviewsModule::get_reviews_html( $args, $conditional_tags, $current_page );

		$response = [
			'html' => $reviews_html,
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
			'productId'   => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					$param = sanitize_text_field( $param );

					return ( 'current' !== $param && 'latest' !== $param ) ? absint( $param ) : $param;
				},
				'validate_callback' => function( $param, $request ) {
					// The request is passed here to be used later as static variable `WooCommerceUtils::$_current_rest_request_query_params`.
					return WooCommerceUtils::validate_product_id( $param, $request );
				},
			],
			'headerLevel' => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return sanitize_text_field( $param );
				},
				'validate_callback' => function( $param ) {
					if ( ! in_array( $param, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ) {
						return false;
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
