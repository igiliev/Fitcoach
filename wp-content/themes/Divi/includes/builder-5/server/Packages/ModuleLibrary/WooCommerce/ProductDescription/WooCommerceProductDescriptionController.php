<?php
/**
 * Module Library: Product Description Module REST Controller class.
 *
 * Registers the REST API endpoint for retrieving product descriptions.
 *
 * @package Builder\Packages\ModuleLibrary\WooCommerce\ProductDescription
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductDescription;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductDescription\WooCommerceProductDescriptionModule;
use ET\Builder\Framework\Controllers\RESTController;

/**
 * WooCommerceProductDescription REST Controller class.
 *
 * This controller handles the REST API endpoint to fetch the WooCommerce
 * product description. It validates incoming request parameters, retrieves the product
 * description via the module, and returns a sanitized HTML output.
 *
 * @package ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductDescription
 */
class WooCommerceProductDescriptionController extends RESTController {

	/**
	 * Returns the arguments for the REST endpoint.
	 *
	 * This function returns an array that defines the arguments for the index action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @return array
	 */
	public static function index_args() {
		return array(
			'productId'       => array(
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					$param = sanitize_text_field( $param );
					return ( 'current' !== $param ) ? absint( $param ) : $param;
				},
			),
			'descriptionType' => array(
				'required'          => false,
				'sanitize_callback' => function( $param ) {
					$allowed = array( 'short_description', 'description' );
					$param   = sanitize_text_field( $param );
					return in_array( $param, $allowed, true ) ? $param : 'short_description';
				},
			),
		);
	}

	/**
	 * Retrieve the rendered HTML for the Woo Product Description module.
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ) {
		$params = $request->get_params();

		// Validate productId parameter.
		if ( ! isset( $params['productId'] ) ) {
			return self::response_error(
				'rest_missing_callback_param',
				__( 'The "productId" parameter is required.', 'divi' ),
				[],
				400
			);
		}

		// Validate descriptionType parameter.
		if ( ! isset( $params['descriptionType'] ) || ! in_array( $params['descriptionType'], array( 'short_description', 'description' ), true ) ) {
			return self::response_error(
				'rest_invalid_param',
				__( 'The "descriptionType" parameter is invalid.', 'divi' ),
				[],
				400
			);
		}

		$product_id       = $params['productId'];
		$description_type = $params['descriptionType'];

		// Additional check: if a specific product ID is provided, verify that the product exists.
		if ( 'current' !== $product_id && ! get_post( $product_id ) ) {
			return self::response_error(
				'product_not_found',
				__( 'Product not found', 'divi' ),
				[ 'code' => 'product_not_found' ],
				404
			);
		}

		// Retrieve the product description using the module method.
		$html = WooCommerceProductDescriptionModule::get_description( $product_id, $description_type );

		// Allow external filtering of the HTML output.
		$html = apply_filters( 'et_wc_product_description_html', $html, $product_id, $description_type );

		return self::response_success(
			array(
				'html' => $html,
				'code' => 'success',
			)
		);
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
