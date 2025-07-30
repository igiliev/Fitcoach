<?php
/**
 * Conditions: PostsRESTController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Conditions\RESTControllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Posts REST Controller class.
 *
 * @since ??
 */
class PostsRESTController extends RESTController {

	/**
	 * Retrieves an array of posts and their corresponding label and values.
	 *
	 * This function retrieves and filters the list of posts from the WordPress wp_posts database table.
	 * Each post is represented as an array with the 'label' key representing the post title
	 * and the 'value' key representing the post ID.
	 *
	 * This function runs the value through `divi_module_options_conditions_posts` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the posts and pagination information.
	 *
	 * @example:
	 * ```php
	 *  $request = new \WP_REST_Request();
	 *  $posts = PostsRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$page         = $request->get_param( 'page' );
		$current_page = max( $page, 1 );
		$post_type    = $request->get_param( 'postType' );
		$search       = $request->get_param( 'search' );
		$per_page     = 20;
		$data         = [
			'results' => [],
			'meta'    => [],
		];

		$query = array(
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'post_status'    => 'attachment' === $post_type ? 'inherit' : 'publish',
			's'              => $search,
			'orderby'        => 'date',
			'order'          => 'desc',
			'paged'          => $current_page,
		);

		if ( 'attachment' === $post_type ) {
			add_filter( 'posts_join', [ __CLASS__, 'query_join' ] );
			add_filter( 'posts_where', [ __CLASS__, 'query_where' ] );
		}

		$posts = new \WP_Query( $query );

		if ( 'attachment' === $post_type ) {
			remove_filter( 'posts_join', [ __CLASS__, 'query_join' ] );
			remove_filter( 'posts_where', [ __CLASS__, 'query_where' ] );
		}

		foreach ( $posts->posts as $post ) {
			$data['results'][] = [
				'label' => wp_strip_all_tags( $post->post_title ),
				'value' => $post->ID,
			];
		}

		$data['meta']['pagination'] = array(
			'results' => array(
				'per_page' => $per_page,
				'total'    => $posts->found_posts,
			),
			'pages'   => array(
				'current' => (int) $current_page,
				'total'   => $posts->max_num_pages,
			),
		);

		/**
		 * Filters posts response data.
		 *
		 * @since ??
		 *
		 * @param array $data Array of posts to include and pagination details.
		 */
		$data = apply_filters( 'divi_module_options_conditions_posts', $data );

		return self::response_success( $data );
	}

	/**
	 * Join the parent post for attachments queries.
	 *
	 * @since ??
	 *
	 * @param string $join  The JOIN clause of the query.
	 *
	 * @return string
	 */
	public static function query_join( $join ) {
		global $wpdb;

		$join .= " LEFT JOIN `$wpdb->posts` AS `parent` ON `parent`.`ID` = `$wpdb->posts`.`post_parent` ";

		return $join;
	}

	/**
	 * Filter attachments based on the parent post status, if any.
	 *
	 * @since ??
	 *
	 * @param string $where The WHERE clause of the query.
	 *
	 * @return string
	 */
	public static function query_where( $where ) {
		global $wpdb;

		$public_post_types = array_keys( et_builder_get_public_post_types() );

		// Add an empty value to:
		// - Avoid syntax error for `IN ()` when there are no public post types.
		// - Cause the query to only return posts with no parent when there are no public post types.
		$public_post_types[] = '';

		$where .= $wpdb->prepare(
			' AND (
		`parent`.`ID` IS NULL OR (
			`parent`.`post_status` = %s
			AND
			`parent`.`post_type` IN (' . implode( ',', array_fill( 0, count( $public_post_types ), '%s' ) ) . ')
		)
	)',
			array_merge( array( 'publish' ), $public_post_types )
		);

		return $where;
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
	 *               This function always returns `[]`.
	 */
	public static function index_args(): array {
		return [
			'postType' => [
				'type'              => 'string',
				'default'           => 'post',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
				'sanitize_callback' => 'sanitize_text_field',
			],
			'page'     => [
				'type'              => 'integer',
				'default'           => 1,
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) $value;
				},
			],
			'search'   => [
				'type'              => 'string',
				'default'           => '',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get the permission status for the index action.
	 *
	 * This function checks if the current user has the permission to use the Visual Builder.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 *              This function always returns `true`.
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
