<?php
/**
 * Module Library: WooCommerce Related Products Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\RelatedProducts;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\RelatedProducts\WooCommerceRelatedProductsModule;
use ET\Builder\Packages\WooCommerce\WooCommerceUtils;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * WooCommerce Related Products REST Controller class.
 *
 * @since ??
 */
class WooCommerceRelatedProductsController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Related Products module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Returns the REST response object containing the rendered HTML.
	 *                                  If the request is invalid, a `WP_Error` object is returned.
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

		$include_categories = $request->get_param( 'includeCategories' );
		$show_price         = $request->get_param( 'showPrice' );
		$offset_number      = $request->get_param( 'offsetNumber' );
		$posts_number       = $request->get_param( 'postsNumber' );
		$columns_number     = $request->get_param( 'columnsNumber' );
		$orderby            = $request->get_param( 'orderby' );

		$args = [ 'product' => $product->get_id() ];

		if ( ! empty( $include_categories ) ) {
			$args['include_categories'] = $include_categories;
		}

		if ( ! empty( $show_price ) ) {
			$args['show_price'] = $show_price;
		}

		if ( ! empty( $offset_number ) ) {
			$args['offset_number'] = $offset_number;
		}

		if ( 0 === $posts_number || ! empty( $posts_number ) ) {
			$args['posts_number'] = $posts_number;
		}

		if ( ! empty( $columns_number ) ) {
			$args['columns_number'] = $columns_number;
		}

		if ( ! empty( $orderby ) ) {
			$args['orderby'] = $orderby;
		}

		// Retrieve the related products using the WooCommerceRelatedProductsModule class.
		$related_products_html = WooCommerceRelatedProductsModule::get_related_products( $args, $conditional_tags );

		$response = [
			'html' => $related_products_html,
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
			'productId'         => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					$param = sanitize_text_field( $param );

					return ( 'current' !== $param && 'latest' !== $param ) ? absint( $param ) : $param;
				},
				'validate_callback' => function( $param, $request ) {
					return WooCommerceUtils::validate_product_id( $param, $request );
				},
			],
			'includeCategories' => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return sanitize_text_field( $param );
				},
				'validate_callback' => function( $param ) {
					$param = explode( ',', $param );

					return is_array( $param ) && count( $param ) > 0 && ! in_array( '', $param, true );
				},
			],
			'showPrice'         => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return sanitize_text_field( $param );
				},
				'validate_callback' => function( $param ) {
					return in_array( $param, [ 'on', 'off' ], true );
				},
			],
			'offsetNumber'      => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return absint( $param );
				},
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param >= 0;
				},
			],
			'postsNumber'       => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return absint( $param );
				},
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param >= 0;
				},
			],
			'columnsNumber'     => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return absint( $param );
				},
				'validate_callback' => function( $param ) {
					return in_array( absint( $param ), [ 1, 2, 3, 4, 5, 6 ], true );
				},
			],
			'orderby'           => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return sanitize_text_field( $param );
				},
				'validate_callback' => function( $param ) {
					return in_array( $param, [ 'default', 'menu_order', 'popularity', 'date', 'date-desc', 'price', 'price-desc' ], true );
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
