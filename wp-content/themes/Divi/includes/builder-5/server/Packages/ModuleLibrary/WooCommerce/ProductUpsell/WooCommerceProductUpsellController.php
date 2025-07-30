<?php
/**
 * Module Library: WooCommerce Product Upsell Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductUpsell;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * WooCommerce Product Upsell REST Controller class.
 *
 * @since ??
 */
class WooCommerceProductUpsellController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Product Upsell module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object containing the rendered HTML.
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$product_id     = $request->get_param( 'productId' );
		$posts_number   = $request->get_param( 'posts_number' );
		$columns_number = $request->get_param( 'columns_number' );
		$orderby        = $request->get_param( 'orderby' );
		$offset_number  = $request->get_param( 'offset_number' );

		$args = empty( $product_id ) ? [] : [ 'product' => $product_id ];

		if ( 0 === $posts_number || ! empty( $posts_number ) ) {
			$args['posts_number'] = $posts_number;
		}

		if ( empty( $columns_number ) ) {
			$args['columns_number'] = $columns_number;
		}

		if ( ! empty( $orderby ) ) {
			$args['orderby'] = $orderby;
		}

		if ( 0 === $offset_number || ! empty( $offset_number ) ) {
			$args['offset_number'] = $offset_number;
		}

		// Retrieve the product upsell HTML using the WooCommerceProductUpsellModule class.
		$upsell_html = WooCommerceProductUpsellModule::get_upsells( $args );

		$response = [
			'html' => $upsell_html,
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
			'productId'      => [
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
					$param = absint( $param );

					if ( $param <= 0 || empty( get_post( $param ) ) ) {
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
			'posts_number'   => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return absint( $param );
				},
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param >= 0;
				},
			],
			'columns_number' => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return absint( $param );
				},
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && in_array( absint( $param ), [ 1, 2, 3, 4, 5, 6 ], true );
				},
			],
			'orderby'        => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return sanitize_text_field( $param );
				},
				'validate_callback' => function( $param ) {
					return is_string( $param ) && in_array(
						sanitize_text_field( $param ),
						[
							'default',
							'menu_order',
							'popularity',
							'date',
							'date-desc',
							'price',
							'price-desc',
						],
						true
					);
				},
			],
			'offset_number'  => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return absint( $param );
				},
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) && $param >= 0;
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
