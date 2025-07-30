<?php
/**
 * PostNavigation: PostNavigationController.
 *
 * @package Builder\Framework\Route
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\PostNavigation;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * PostNavigation REST Controller class.
 *
 * @since ??
 */
class PostNavigationController extends RESTController {

	/**
	 * Return Project terms for Filterable Portfolio module.
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response
	 * @since ??
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'post_id'       => $request->get_param( 'postId' ),
			'in_same_term'  => $request->get_param( 'inSameTerm' ),
			'taxonomy_name' => $request->get_param( 'taxonomyName' ),
			'prev_text'     => $request->get_param( 'prevText' ),
			'next_text'     => $request->get_param( 'nextText' ),
		];

		$posts_navigation = PostNavigationModule::get_post_navigation( $args );

		$response = [
			'postsNavigation' => $posts_navigation,
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
			'postId'       => [
				'type'              => 'number',
				'default'           => -1,
				'sanitize_callback' => function ( $value, $request, $param ) {
					return (int) $value;
				},
			],
			'inSameTerm'   => [
				'type'              => 'string',
				'default'           => 'off',
				'validate_callback' => function ( $param, $request, $key ) {
					return 'on' === $param || 'off' === $param;
				},
			],
			'taxonomyName' => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function ( $value, $request, $param ) {
					return is_string( $value ) ? $value : '';
				},
			],
			'prevText'     => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function ( $value, $request, $param ) {
					return is_string( $value ) ? $value : '';
				},
			],
			'nextText'     => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function ( $value, $request, $param ) {
					return is_string( $value ) ? $value : '';
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
