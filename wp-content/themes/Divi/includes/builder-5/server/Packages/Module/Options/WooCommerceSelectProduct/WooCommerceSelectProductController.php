<?php
/**
 * Module Library: WooCommerce Select Product REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\WooCommerceSelectProduct;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Select Product REST Controller class.
 *
 * @since ??
 */
class WooCommerceSelectProductController extends RESTController {

	/**
	 * Search products based on the provided arguments.
	 *
	 * This function searches for products based on the provided arguments and returns the results in a structured format.
	 *
	 * @since ??
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return array
	 *
	 * @example
	 * ```php
	 *   $args = [
	 *     'search' => 'example',
	 *     'per_page' => 10,
	 *     'page' => 1,
	 *     'fields' => 'id,title,slug,description',
	 *     'id' => 123,
	 *     'post_type' => 'product',
	 *     'include_current_post' => '1',
	 *     'include_latest_post' => '1',
	 *   ];
	 *   $products_data = WooCommerceSelectProductController::search_products( $args );
	 * ```
	 */
	public static function search_products( array $args = [] ): array {

		$current_page     = isset( $args['page'] ) ? (int) $args['page'] : 0;
		$current_page     = max( $current_page, 1 );
		$value            = isset( $args['id'] ) ? $args['id'] : '';
		$search           = isset( $args['search'] ) ? $args['search'] : '';
		$prepend_value    = (int) $value > 0;
		$results_per_page = isset( $args['per_page'] ) ? (int) $args['per_page'] : 20;
		$results          = array(
			'results' => array(),
			'meta'    => array(),
		);

		$include_current_post = '1' === (string) ( $args['include_current_post'] ?? '0' );
		$include_latest_post  = '1' === (string) ( $args['include_latest_post'] ?? '0' );

		$public_post_types = et_builder_get_public_post_types();

		$post_type = 'product';

		if ( ! isset( $public_post_types[ $post_type ] ) ) {
			$post_type = 'post';
		}

		$post_type_object = get_post_type_object( $post_type );
		$post_type_label  = $post_type_object ? $post_type_object->labels->singular_name : '';

		$query = array(
			'post_type'      => $post_type,
			'posts_per_page' => $results_per_page,
			'post_status'    => 'publish',
			's'              => $search,
			'orderby'        => 'date',
			'order'          => 'desc',
			'paged'          => $current_page,
		);

		if ( $prepend_value ) {
			$value_post = get_post( $value );

			if ( $value_post && 'publish' === $value_post->post_status && $value_post->post_type === $post_type ) {
				$results['results'][] = array(
					'id'    => $value,
					'title' => et_core_intentionally_unescaped( wp_strip_all_tags( $value_post->post_title ), 'react_jsx' ),
					'meta'  => array(
						'post_type' => et_core_intentionally_unescaped( $post_type_label, 'react_jsx' ),
					),
				);

				// We will manually prepend the current id so we need to reduce the number of results.
				$query['posts_per_page'] -= 1;
				$query['post__not_in']    = array( $value );
			}
		}

		if ( $include_current_post ) {
			$query['posts_per_page'] -= 1;
		}

		if ( $include_latest_post ) {
			$query['posts_per_page'] -= 1;
		}

		$posts = new \WP_Query( $query );

		if ( $include_current_post && ! empty( $posts->posts ) ) {
			$current_post_type        = $args['current_post_type'] ?? 'post';
			$current_post_type        = isset( $public_post_types[ $current_post_type ] ) ? $current_post_type : 'post';
			$current_post_type_object = get_post_type_object( $current_post_type );
			$current_post_type_label  = $current_post_type_object ? $current_post_type_object->labels->singular_name : '';

			$results['results'][] = array(
				'id'    => 'current',
				// Translators: %1$s: Post type singular name.
				'title' => et_core_intentionally_unescaped( sprintf( __( 'This %1$s', 'et_builder' ), $current_post_type_label ), 'react_jsx' ),
				'meta'  => array(
					'post_type' => et_core_intentionally_unescaped( $current_post_type_label, 'react_jsx' ),
				),
			);
		}

		if ( $include_latest_post && ! empty( $posts->posts ) ) {
			$results['results'][] = array(
				'id'    => 'latest',
				// Translators: %1$s: Post type singular name.
				'title' => et_core_intentionally_unescaped(
					sprintf(
						__( 'Latest %1$s', 'et_builder' ),
						$post_type_label
					),
					'react_jsx'
				),
				'meta'  => array(
					'post_type' => et_core_intentionally_unescaped( $post_type_label, 'react_jsx' ),
				),
			);
		}

		foreach ( $posts->posts as $post ) {
			$results['results'][] = array(
				'id'    => (int) $post->ID,
				'title' => et_core_intentionally_unescaped( wp_strip_all_tags( $post->post_title ), 'react_jsx' ),
				'meta'  => array(
					'post_type' => et_core_intentionally_unescaped( $post_type_label, 'react_jsx' ),
				),
			);
		}

		$results['meta']['pagination'] = array(
			'results' => array(
				'per_page' => (int) $results_per_page,
				'total'    => (int) $posts->found_posts,
			),
			'pages'   => array(
				'current' => (int) $current_page,
				'total'   => (int) $posts->max_num_pages,
			),
		);

		return $results;
	}

	/**
	 * Retrieve the rendered HTML for the Select Product option.
	 *
	 * This function retrieves the rendered HTML for the Select Product option based on a search string passed as an argument.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object containing the rendered HTML.
	 *
	 * @example: Example usage of the Select Product option REST API endpoint.
	 * ```php
	 * $request = new WP_REST_Request( 'GET' );
	 * $request->set_param( 'search', '' );
	 * $request->set_param( 'per_page', 10 );
	 * $request->set_param( 'page', 1 );
	 * $request->set_param( 'fields', 'id,title,slug' );
	 * $response = WooCommerceSelectProductController::index( $request );
	 * $products_data = $response->get_data();
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'search'               => $request->get_param( 'search' ),
			'per_page'             => $request->get_param( 'per_page' ),
			'page'                 => $request->get_param( 'page' ),
			'fields'               => $request->get_param( 'fields' ),
			'id'                   => $request->get_param( 'id' ),
			'include_current_post' => $request->get_param( 'include_current_post' ),
			'include_latest_post'  => $request->get_param( 'include_latest_post' ),
			'id'                   => $request->get_param( 'id' ),
			'current_post_type'    => $request->get_param( 'current_post_type' ),
		];

		$response = self::search_products( $args );

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
	 *
	 * @example
	 * ```php
	 * $args = WooCommerceSelectProductController::index_args();
	 * ```
	 */
	public static function index_args(): array {
		return [
			'search'               => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'per_page'             => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'absint',
				'validate_callback' => function( $param, $request, $key ) {
					return is_int( (int) $param );
				},
			],
			'page'                 => [
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'absint',
				'validate_callback' => function( $param, $request, $key ) {
					return is_int( (int) $param );
				},
			],
			'fields'               => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'id'                   => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'include_current_post' => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'include_latest_post'  => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'id'                   => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'current_post_type'    => [
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
		];
	}

	/**
	 * Provides the permission status for the index action.
	 *
	 * This function checks if the current user has the permission to use the Visual Builder.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 *
	 * @example
	 * ```php
	 * $permission = WooCommerceSelectProductController::index_permission();
	 * ```
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
