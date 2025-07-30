<?php
/**
 * FilterablePortfolio: FilterablePortfolioController.
 *
 * @package Builder\Framework\Route
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FilterablePortfolio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\ModuleLibrary\FilterablePortfolio\FilterablePortfolioModule;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * FilterablePortfolio REST Controller class.
 *
 * @since ??
 */
class FilterablePortfolioController extends RESTController {

	/**
	 * Return Project terms for Filterable Portfolio module.
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 * @since ??
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'categories'      => $request->get_param( 'categories' ),
			'fullwidth'       => $request->get_param( 'fullwidth' ),
			'show_pagination' => $request->get_param( 'showPagination' ),
		];

		$posts = FilterablePortfolioModule::get_portfolio_items( $args );

		$response = [
			'posts' => $posts,
		];

		return self::response_success( $response );

	}

	/**
	 * Index action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @return array
	 */
	public static function index_args(): array {
		return [
			'categories'     => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function ( $value, $request, $param ) {
					return explode( ',', $value );
				},
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'fullwidth'      => [
				'type'              => 'string',
				'default'           => 'on',
				'validate_callback' => function ( $param, $request, $key ) {
					return 'on' === $param || 'off' === $param;
				},
			],
			'postsPerPage'   => [
				'type'              => 'string',
				'default'           => '10',
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) $value;
				},
			],
			'showPagination' => [
				'type'              => 'string',
				'default'           => 'on',
				'validate_callback' => function ( $param, $request, $key ) {
					return 'on' === $param || 'off' === $param;
				},
			],
		];
	}

	/**
	 * Index action permission.
	 *
	 * Endpoint permission callback as used in `register_rest_route()`.
	 *
	 * @return bool
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
