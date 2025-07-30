<?php
/**
 * Module Library: WooCommerce Product Tabs Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductTabs;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductTabs\WooCommerceProductTabsModule;
use WP_REST_Request;
use WP_REST_Response;

/**
 * WooCommerce Product Tabs REST Controller class.
 *
 * @since ??
 */
class WooCommerceProductTabsController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the WooCommerce Product Tabs module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object containing the rendered HTML.
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$product_id   = $request->get_param( 'productId' );
		$include_tabs = $request->get_param( 'includeTabs' );

		$args = empty( $product_id ) ? [] : [ 'product' => $product_id ];

		if ( empty( $include_tabs ) ) {
			$include_tabs = [];
		} else {
			$include_tabs = explode( ',', $include_tabs );
		}

		$args['include_tabs'] = $include_tabs;

		$product_tabs = WooCommerceProductTabsModule::get_product_tabs( $args );

		$response = [
			'html' => $product_tabs,
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
				'minLength'         => 1, // Prevent empty string.
				'sanitize_callback' => function( $param ) {
					$param = sanitize_text_field( $param );
					return 'current' === $param || 'latest' === $param ? $param : absint( $param );
				},
				'validate_callback' => function( $param ) {
					// First check if it's a special value or valid numeric ID.
					if ( 'current' === $param || 'latest' === $param ) {
						return true;
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
			'includeTabs' => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					return sanitize_text_field( $param );
				},
				'validate_callback' => function( $param ) {
					if ( '' === $param ) {
						return true;
					}

					$param_items = explode( ',', $param );
					foreach ( $param_items as $param_item ) {
						if ( ! in_array( $param_item, [ 'description', 'reviews', 'additional_information' ], true ) ) {
							return false;
						}
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
