<?php
/**
 * FullwidthPortfolio: FullwidthPortfolioController.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FullwidthPortfolio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ET\Builder\Packages\ModuleLibrary\FullwidthPortfolio\FullwidthPortfolioModule;

/**
 * Fullwidth Portfolio REST Controller class.
 *
 * @since ??
 */
class FullwidthPortfolioController extends RESTController {

	/**
	 * Index function to retrieve Fullwidth Portfolio posts based on the given parameters.
	 *
	 * This function makes use of `et_pb_portfolio_image_width` and `et_pb_portfolio_image_height`
	 * filters to retrieve the fullwidth portfolio image width and height.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Returns `WP_REST_Response` object, or `WP_Error` object on failure.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *      FullwidthPortfolioController::index( new WP_REST_Request( array(
	 *          'postsNumber' => 10,
	 *          'categories'  => array( 1, 2, 3 ),
	 *      ) ) );
	 * ```
	 */
	public static function index( WP_REST_Request $request ) {
		$posts = [];

		$args = [
			'posts_number' => $request->get_param( 'postsNumber' ),
			'categories'   => $request->get_param( 'categories' ),
		];

		// Get Portfolio Items based upon request parameters.
		$posts = FullwidthPortfolioModule::get_portfolio_items( $args );

		// Prepare response.
		$response = [
			'posts' => $posts,
		];

		return self::response_success( $response );
	}

	/**
	 * Get the index action arguments.
	 *
	 * This method returns an array of arguments that can be used in the `register_rest_route()` function
	 * to define the necessary parameters for the index action
	 * The index action allows the user to retrieve dynamic content options based on the provided postId parameter.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'postsNumber' => [
				'type'              => 'string',
				'default'           => '-1',
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) $value;
				},
			],
			'categories'  => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function( $value, $request, $param ) {
					return explode( ',', $value );
				},
			],
		];
	}

	/**
	 * Index action permission.
	 *
	 * Checks if the current user has permission to use the VisualBuilder (VB).
	 * This function is used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool Whether the current user has permission to use the VisualBuilder (VB).
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
