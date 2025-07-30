<?php
/**
 * Portfolio: PortfolioController.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Portfolio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Portfolio REST Controller class.
 *
 * @since ??
 */
class PortfolioController extends RESTController {

	/**
	 * Index function to retrieve Portfolio posts based on the given parameters.
	 *
	 * This function makes use of `et_pb_portfolio_image_width` and `et_pb_portfolio_image_height`
	 * filters to retrieve the portfolio image width and height.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Returns `WP_REST_Response` object, or `WP_Error` object on failure.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *      PortfolioController::index( new WP_REST_Request( array(
	 *          'postsPerPage' => 10,
	 *          'paged' => 1,
	 *          'categories' => array( 1, 2, 3 ),
	 *          'fullwidth' => 'on'
	 *      ) ) );
	 * ```
	 */
	public static function index( WP_REST_Request $request ) {
		$posts = [];

		$args = [
			'posts_per_page' => $request->get_param( 'postsPerPage' ),
			'paged'          => $request->get_param( 'paged' ),
			'categories'     => $request->get_param( 'categories' ),
			'fullwidth'      => $request->get_param( 'fullwidth' ),
		];

		$query_args = [
			'posts_per_page' => $args['posts_per_page'],
			'paged'          => $args['paged'],
			'post_type'      => 'project',
			'post_status'    => [ 'publish', 'private' ],
			'perm'           => 'readable',
		];

		$selected_term_ids        = $args['categories'];
		$is_all_category_selected = in_array( 'all', $selected_term_ids, true );

		$term_ids = array_map(
			function( $value ) {
				return ( 'current' === $value && is_tax( 'project_category' ) ) ? get_queried_object()->term_id : (int) $value;
			},
			$selected_term_ids
		);

		$term_ids = array_filter( $term_ids );

		if ( $term_ids && ! $is_all_category_selected ) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'project_category',
					'field'    => 'id',
					'terms'    => $term_ids,
					'operator' => 'IN',
				],
			];
		}

		$query = new \WP_Query( $query_args );

		// Portfolio image width.
		$width = 'on' === $args['fullwidth'] ? 1080 : 400;

		/**
		 * Filter the portfolio image width.
		 *
		 * @since ??
		 * @deprecated 5.0.0 Use {@see 'divi_module_library_portfolio_image_width'} instead.
		 *
		 * @param int $width The portfolio image width.
		 */
		$width = apply_filters(
			'et_pb_portfolio_image_width',
			$width
		);

		// Type cast here for proper doc generation.
		$width = (int) $width;

		/**
		 * Filter the portfolio image width.
		 *
		 * @since ??
		 *
		 * @param int $width The portfolio image width.
		 */
		$width = apply_filters( 'divi_module_library_portfolio_image_width', $width );

		// Type cast here for proper doc generation.
		$width = (int) $width;

		// Portfolio image height.
		$height = 'on' === $args['fullwidth'] ? 9999 : 284;

		/**
		 * Filter the portfolio image height.
		 *
		 * @since ??
		 * @deprecated 5.0.0 Use {@see 'divi_module_library_portfolio_image_height'} instead.
		 *
		 * @param int $height The portfolio image height.
		 */
		$height = apply_filters(
			'et_pb_portfolio_image_height',
			$height
		);

		// Type cast here for proper doc generation.
		$height = (int) $height;

		/**
		 * Filter the portfolio image height.
		 *
		 * @since ??
		 *
		 * @param int $height The portfolio image height.
		 */
		$height = apply_filters( 'divi_module_library_portfolio_image_height', $height );

		// Type cast here for proper doc generation.
		$height = (int) $height;

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_id            = get_the_ID();
				$categories         = [];
				$categories_object  = get_the_terms( $post_id, 'project_category' );
				$has_post_thumbnail = has_post_thumbnail( $post_id );

				if ( ! empty( $categories_object ) ) {
					foreach ( $categories_object as $category ) {
						$categories[] = [
							'id'        => (int) $category->term_id,
							'label'     => $category->name,
							'permalink' => get_term_link( $category ),
						];
					}
				}

				if ( $has_post_thumbnail ) {
					$alt_text            = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );
					$thumbnail_grid      = wp_get_attachment_image_src( get_post_thumbnail_id(), 'et-pb-portfolio-image' );
					$thumbnail_fullwidth = wp_get_attachment_image_src( get_post_thumbnail_id(), 'et-pb-portfolio-image-single' );
					$thumbnails          = [
						'grid'      => [
							'src'     => $thumbnail_grid[0],
							'width'   => (int) $thumbnail_grid[1],
							'height'  => (int) $thumbnail_grid[2],
							'altText' => $alt_text,
						],
						'fullwidth' => [
							'src'     => $thumbnail_fullwidth[0],
							'width'   => (int) $thumbnail_fullwidth[1],
							'height'  => (int) $thumbnail_fullwidth[2],
							'altText' => $alt_text,
						],
					];
				}

				$new_post                 = [];
				$new_post['id']           = $post_id;
				$new_post['title']        = get_the_title( $post_id );
				$new_post['permalink']    = get_permalink( $post_id );
				$new_post['thumbnails']   = $has_post_thumbnail ? $thumbnails : null;
				$new_post['categories']   = $categories;
				$new_post['classNames']   = get_post_class( '', $post_id );
				$new_post['classNames'][] = 'et_pb_portfolio_item';
				$posts[]                  = $new_post;
			}
		}

		$metadata = [];

		$metadata['maxNumPages'] = $query->max_num_pages;

		$metadata['nextPageButtonLabel'] = esc_html__( '&laquo; Older Entries', 'et_builder' );

		$metadata['prevPageButtonLabel'] = esc_html__( 'Next Entries &raquo;', 'et_builder' );

		// Adds WP-PageNavi plugin support.
		$metadata['wpPagenavi'] = function_exists( 'wp_pagenavi' ) ? \wp_pagenavi(
			[
				'query' => $query,
				'echo'  => false,
			]
		) : null;

		wp_reset_postdata();

		$response = [
			'posts'    => $posts,
			'metadata' => $metadata,
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
			'postsPerPage' => [
				'type'              => 'string',
				'default'           => '10',
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) $value;
				},
			],
			'paged'        => [
				'type'              => 'string',
				'default'           => '1',
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) $value;
				},
			],
			'categories'   => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function( $value, $request, $param ) {
					return explode( ',', $value );
				},
			],
			'fullwidth'    => [
				'type'              => 'string',
				'default'           => 'on',
				'validate_callback' => function( $param, $request, $key ) {
					return 'on' === $param || 'off' === $param;
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
