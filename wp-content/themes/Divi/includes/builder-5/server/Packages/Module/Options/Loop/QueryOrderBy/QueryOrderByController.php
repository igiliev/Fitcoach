<?php
/**
 * Loop QueryOrderBy: QueryOrderByController.
 *
 * @package Builder\Packages\Module\Options\Loop\QueryOrderBy
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Loop\QueryOrderBy;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Query Order By REST Controller class.
 *
 * @since ??
 */
class QueryOrderByController extends RESTController {
	/**
	 * Return ordering options based on the specified query_type.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$query_type = $request->get_param( 'query_type' );
		$post_type  = $request->get_param( 'post_type' );
		$taxonomy   = $request->get_param( 'post_taxonomies' ); // For direct taxonomy access.

		// Normalize query type.
		$query_type = self::_normalize_query_type( $query_type );

		$order_by_options = [];

		switch ( $query_type ) {
			case 'post_type':
				// Handle multiple post types.
				if ( is_string( $post_type ) && strpos( $post_type, ',' ) !== false ) {
					$post_types       = array_map( 'trim', explode( ',', $post_type ) );
					$order_by_options = self::_get_multiple_post_types_order_by_options( $post_types );
				} else {
					$order_by_options = self::_get_post_type_order_by_options( $post_type );
				}
				break;

			case 'post_taxonomies':
				// Handle multiple taxonomies.
				if ( is_string( $taxonomy ) && strpos( $taxonomy, ',' ) !== false ) {
					$taxonomies       = array_map( 'trim', explode( ',', $taxonomy ) );
					$order_by_options = self::_get_multiple_taxonomies_order_by_options( $post_type, $taxonomies );
				} else {
					// If no specific taxonomy is provided, use post type to get related taxonomies.
					$order_by_options = self::_get_post_taxonomies_order_by_options( $post_type, $taxonomy );
				}
				break;

			case 'user_roles':
				// User roles doesn't need any parameters.
				$order_by_options = self::_get_user_roles_order_by_options();
				break;

			default:
				return rest_ensure_response( self::response_error( 'Invalid query_type specified' ) );
		}

		return self::response_success( $order_by_options );
	}

	/**
	 * Normalize query type parameter.
	 *
	 * @since ??
	 *
	 * @param string $query_type The query type from request.
	 *
	 * @return string Normalized query type.
	 */
	private static function _normalize_query_type( string $query_type ): string {
		switch ( $query_type ) {
			case 'post_types':
				return 'post_type';
			case 'post_taxonomy':
				return 'post_taxonomies';
			case 'user_role':
				return 'user_roles';
			// Legacy support.
			case 'users':
			case 'user':
				return 'user_roles';
			case 'terms':
			case 'term':
				return 'post_taxonomies';
			default:
				return $query_type;
		}
	}

	/**
	 * Get order by options for post type queries.
	 *
	 * @since ??
	 *
	 * @param string $post_type The post type.
	 *
	 * @return array Order by options for post type queries.
	 */
	private static function _get_post_type_order_by_options( string $post_type = 'post' ): array {
		// Core options that should always be available.
		$order_by_options = [
			[
				'value' => 'date',
				'label' => esc_html__( 'Publish Date', 'et_builder' ),
			],
			[
				'value' => 'none',
				'label' => esc_html__( 'None', 'et_builder' ),
			],
			[
				'value' => 'ID',
				'label' => esc_html__( 'ID', 'et_builder' ),
			],
			[
				'value' => 'title',
				'label' => esc_html__( 'Title', 'et_builder' ),
			],
			[
				'value' => 'name',
				'label' => esc_html__( 'Post Name', 'et_builder' ),
			],
			[
				'value' => 'modified',
				'label' => esc_html__( 'Last Modified Date', 'et_builder' ),
			],
			[
				'value' => 'rand',
				'label' => esc_html__( 'Random', 'et_builder' ),
			],
		];

		// Add type for hierarchical post types.
		if ( is_post_type_hierarchical( $post_type ) ) {
			$order_by_options[] = [
				'value' => 'type',
				'label' => esc_html__( 'Post Type', 'et_builder' ),
			];
		}

		// Add author option if post type supports author.
		if ( post_type_supports( $post_type, 'author' ) ) {
			$order_by_options[] = [
				'value' => 'author',
				'label' => esc_html__( 'Author', 'et_builder' ),
			];
		}

		// Add parent if post type is hierarchical.
		if ( is_post_type_hierarchical( $post_type ) ) {
			$order_by_options[] = [
				'value' => 'parent',
				'label' => esc_html__( 'Parent', 'et_builder' ),
			];
		}

		// Add menu order if post type supports page attributes.
		if ( post_type_supports( $post_type, 'page-attributes' ) ) {
			$order_by_options[] = [
				'value' => 'menu_order',
				'label' => esc_html__( 'Menu Order', 'et_builder' ),
			];
		}

		// Add comment count if post type supports comments.
		if ( post_type_supports( $post_type, 'comments' ) ) {
			$order_by_options[] = [
				'value' => 'comment_count',
				'label' => esc_html__( 'Comment Count', 'et_builder' ),
			];
		}

		// Add WooCommerce specific options.
		if ( 'product' === $post_type && function_exists( 'WC' ) ) {
			$order_by_options[] = [
				'value' => 'price',
				'label' => esc_html__( 'Price', 'et_builder' ),
			];
			$order_by_options[] = [
				'value' => 'popularity',
				'label' => esc_html__( 'Popularity (Sales)', 'et_builder' ),
			];
			$order_by_options[] = [
				'value' => 'rating',
				'label' => esc_html__( 'Rating', 'et_builder' ),
			];
		}

		// Allow plugins/themes to add custom order by options for post types.
		return apply_filters( "et_builder_loop_order_by_options_{$post_type}", $order_by_options );
	}

	/**
	 * Get order by options for post taxonomies queries.
	 *
	 * @since ??
	 *
	 * @param string $post_type The post type.
	 * @param string $taxonomy  Specific taxonomy (optional).
	 *
	 * @return array Order by options for post taxonomies queries.
	 */
	private static function _get_post_taxonomies_order_by_options( string $post_type = 'post', string $taxonomy = '' ): array {
		// Core taxonomy ordering options available in WordPress.
		$order_by_options = [
			[
				'value' => 'name',
				'label' => esc_html__( 'Name', 'et_builder' ),
			],
			[
				'value' => 'slug',
				'label' => esc_html__( 'Slug', 'et_builder' ),
			],
			[
				'value' => 'term_id',
				'label' => esc_html__( 'Term ID', 'et_builder' ),
			],
			[
				'value' => 'id',
				'label' => esc_html__( 'ID', 'et_builder' ),
			],
			[
				'value' => 'description',
				'label' => esc_html__( 'Description', 'et_builder' ),
			],
			[
				'value' => 'count',
				'label' => esc_html__( 'Count', 'et_builder' ),
			],
			[
				'value' => 'none',
				'label' => esc_html__( 'None', 'et_builder' ),
			],
			[
				'value' => 'term_order',
				'label' => esc_html__( 'Term Order', 'et_builder' ),
			],
		];

		// Only include parent option if we have a specific taxonomy and it's hierarchical.
		if ( ! empty( $taxonomy ) ) {
			$taxonomy_obj = get_taxonomy( $taxonomy );

			if ( $taxonomy_obj && ! empty( $taxonomy_obj->hierarchical ) ) {
				// Insert parent option after description for logical grouping.
				$parent_option = [
					'value' => 'parent',
					'label' => esc_html__( 'Parent', 'et_builder' ),
				];

				// Insert after description (index 4), so insert at index 5.
				array_splice( $order_by_options, 5, 0, [ $parent_option ] );

				// Add WooCommerce specific options if applicable.
				if ( 'product_cat' === $taxonomy && function_exists( 'WC' ) ) {
					$order_by_options[] = [
						'value' => 'meta_value_num',
						'label' => esc_html__( 'Custom Order (WooCommerce)', 'et_builder' ),
					];
				}
			}
		} else {
			// When no specific taxonomy is provided, we don't include the parent option
			// as we cannot determine which taxonomy the user will be working with.
			// Add WooCommerce options if post type is product and WooCommerce is active.
			if ( function_exists( 'WC' ) && 'product' === $post_type ) {
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );
				if ( isset( $taxonomies['product_cat'] ) ) {
					$order_by_options[] = [
						'value' => 'meta_value_num',
						'label' => esc_html__( 'Custom Order (WooCommerce)', 'et_builder' ),
					];
				}
			}
		}

		// Allow plugins/themes to add custom order by options for post taxonomies.
		$taxonomy_key = ! empty( $taxonomy ) ? "taxonomy_{$taxonomy}" : "post_type_{$post_type}";
		return apply_filters(
			"et_builder_loop_order_by_options_taxonomy_{$taxonomy_key}",
			apply_filters( 'et_builder_loop_order_by_options_post_taxonomies', $order_by_options )
		);
	}

	/**
	 * Get order by options for user roles queries.
	 *
	 * @since ??
	 *
	 * @return array Order by options for user role queries.
	 */
	private static function _get_user_roles_order_by_options(): array {
		// Base options for user role ordering - these are always the same regardless of parameters.
		$order_by_options = [
			[
				'value' => 'display_name',
				'label' => esc_html__( 'Display Name', 'et_builder' ),
			],
			[
				'value' => 'name',
				'label' => esc_html__( 'Username', 'et_builder' ),
			],
			[
				'value' => 'login',
				'label' => esc_html__( 'User Login', 'et_builder' ),
			],
			[
				'value' => 'nicename',
				'label' => esc_html__( 'Nice Name', 'et_builder' ),
			],
			[
				'value' => 'email',
				'label' => esc_html__( 'Email', 'et_builder' ),
			],
			[
				'value' => 'url',
				'label' => esc_html__( 'URL', 'et_builder' ),
			],
			[
				'value' => 'registered',
				'label' => esc_html__( 'Registration Date', 'et_builder' ),
			],
			[
				'value' => 'post_count',
				'label' => esc_html__( 'Post Count', 'et_builder' ),
			],
			[
				'value' => 'ID',
				'label' => esc_html__( 'ID', 'et_builder' ),
			],
		];

		// Allow plugins/themes to add custom order by options for user roles.
		return apply_filters( 'et_builder_loop_order_by_options_user_roles', $order_by_options );
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
			'query_type'      => [
				'required'    => true,
				'type'        => 'string',
				'description' => 'Type of query to get order by options for (post_type, post_taxonomies, user_roles)',
				'enum'        => [
					'post_types',
					'post_taxonomies',
					'user_roles',
				],
			],
			'post_type'       => [
				'type'        => 'string',
				'description' => 'Post type to get order by options for (when query_type is post_type or post_taxonomies). Can be a single post type or comma-separated list.',
				'default'     => 'post',
			],
			'post_taxonomies' => [
				'type'        => 'string',
				'description' => 'Post taxonomies to get order by options for (when query_type is post_taxonomies). Can be a single taxonomy or comma-separated list.',
				'default'     => 'category',
			],
			'user_roles'      => [
				'type'        => 'string',
				'description' => 'User roles to get order by options for (when query_type is user_roles)',
				'default'     => 'subscriber',
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

	/**
	 * Get all columns for a post type using WordPress filters.
	 *
	 * @since ??
	 *
	 * @param string $post_type The post type to retrieve columns for.
	 *
	 * @return array Array of columns with 'value' and 'label' keys.
	 */
	private static function _get_all_columns_for_post_type( string $post_type ): array {
		// Get additional columns from the WP filter but don't rely on it for core columns.
		$columns = apply_filters(
			"manage_{$post_type}_posts_columns",
			[]
		);

		// Skip checkbox and other non-ordering columns.
		$skip_columns = [ 'cb', 'comments' ];

		// Populate column options from the filter result.
		$columns_options = [];
		foreach ( $columns as $column_key => $column_label ) {
			// Skip non-ordering columns.
			if ( in_array( $column_key, $skip_columns, true ) ) {
				continue;
			}

			// Skip columns that return HTML instead of text.
			if ( is_string( $column_label ) && false !== strpos( $column_label, '<' ) ) {
				continue;
			}

			// Use the column label or create one from the key.
			$label = is_string( $column_label ) && ! empty( $column_label )
				? sanitize_text_field( $column_label )
				: ucfirst( $column_key );

			$columns_options[] = [
				'value' => $column_key,
				'label' => $label,
			];
		}

		return apply_filters( 'et_builder_loop_columns_for_ordering', $columns_options, $post_type );
	}

	/**
	 * Get order by options for multiple post types.
	 *
	 * @since ??
	 *
	 * @param array $post_types Array of post types.
	 *
	 * @return array Order by options that are common to all post types.
	 */
	private static function _get_multiple_post_types_order_by_options( array $post_types ): array {
		// Guard: Ensure $post_types is an array.
		if ( ! is_array( $post_types ) || empty( $post_types ) ) {
			return [];
		}

		// Start with core options that are always available.
		$common_options = [
			[
				'value' => 'date',
				'label' => esc_html__( 'Publish Date', 'et_builder' ),
			],
			[
				'value' => 'none',
				'label' => esc_html__( 'None', 'et_builder' ),
			],
			[
				'value' => 'ID',
				'label' => esc_html__( 'ID', 'et_builder' ),
			],
			[
				'value' => 'title',
				'label' => esc_html__( 'Title', 'et_builder' ),
			],
			[
				'value' => 'name',
				'label' => esc_html__( 'Post Name', 'et_builder' ),
			],
			[
				'value' => 'modified',
				'label' => esc_html__( 'Last Modified Date', 'et_builder' ),
			],
			[
				'value' => 'rand',
				'label' => esc_html__( 'Random', 'et_builder' ),
			],
		];

		// Check if all post types are hierarchical.
		$all_hierarchical = true;
		foreach ( $post_types as $post_type ) {
			if ( ! is_post_type_hierarchical( $post_type ) ) {
				$all_hierarchical = false;
				break;
			}
		}

		if ( $all_hierarchical ) {
			$common_options[] = [
				'value' => 'type',
				'label' => esc_html__( 'Post Type', 'et_builder' ),
			];
			$common_options[] = [
				'value' => 'parent',
				'label' => esc_html__( 'Parent', 'et_builder' ),
			];
		}

		// Check if all post types support author.
		$all_support_author = true;
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'author' ) ) {
				$all_support_author = false;
				break;
			}
		}

		if ( $all_support_author ) {
			$common_options[] = [
				'value' => 'author',
				'label' => esc_html__( 'Author', 'et_builder' ),
			];
		}

		// Check if all post types support page attributes.
		$all_support_page_attributes = true;
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'page-attributes' ) ) {
				$all_support_page_attributes = false;
				break;
			}
		}

		if ( $all_support_page_attributes ) {
			$common_options[] = [
				'value' => 'menu_order',
				'label' => esc_html__( 'Menu Order', 'et_builder' ),
			];
		}

		// Check if all post types support comments.
		$all_support_comments = true;
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'comments' ) ) {
				$all_support_comments = false;
				break;
			}
		}

		if ( $all_support_comments ) {
			$common_options[] = [
				'value' => 'comment_count',
				'label' => esc_html__( 'Comment Count', 'et_builder' ),
			];
		}

		// Add WooCommerce specific options if product is included.
		if ( in_array( 'product', $post_types, true ) && function_exists( 'WC' ) ) {
			$common_options[] = [
				'value' => 'price',
				'label' => esc_html__( 'Price', 'et_builder' ),
			];
			$common_options[] = [
				'value' => 'popularity',
				'label' => esc_html__( 'Popularity (Sales)', 'et_builder' ),
			];
			$common_options[] = [
				'value' => 'rating',
				'label' => esc_html__( 'Rating', 'et_builder' ),
			];
		}

		// Allow plugins/themes to add custom order by options for multiple post types.
		return apply_filters( 'et_builder_loop_order_by_options_multiple_post_types', $common_options, $post_types );
	}

	/**
	 * Get order by options for multiple taxonomies.
	 *
	 * @since ??
	 *
	 * @param string $post_type  The post type.
	 * @param array  $taxonomies Array of taxonomies.
	 *
	 * @return array Order by options that are common to all taxonomies.
	 */
	private static function _get_multiple_taxonomies_order_by_options( string $post_type, array $taxonomies ): array {
		// Core taxonomy ordering options available in WordPress.
		$order_by_options = [
			[
				'value' => 'name',
				'label' => esc_html__( 'Name', 'et_builder' ),
			],
			[
				'value' => 'slug',
				'label' => esc_html__( 'Slug', 'et_builder' ),
			],
			[
				'value' => 'term_id',
				'label' => esc_html__( 'Term ID', 'et_builder' ),
			],
			[
				'value' => 'id',
				'label' => esc_html__( 'ID', 'et_builder' ),
			],
			[
				'value' => 'description',
				'label' => esc_html__( 'Description', 'et_builder' ),
			],
			[
				'value' => 'parent',
				'label' => esc_html__( 'Parent', 'et_builder' ),
			],
			[
				'value' => 'count',
				'label' => esc_html__( 'Count', 'et_builder' ),
			],
			[
				'value' => 'none',
				'label' => esc_html__( 'None', 'et_builder' ),
			],
			[
				'value' => 'term_order',
				'label' => esc_html__( 'Term Order', 'et_builder' ),
			],
		];

		// If WooCommerce is active and product_cat is included.
		if ( function_exists( 'WC' ) && in_array( 'product_cat', $taxonomies, true ) ) {
			$order_by_options[] = [
				'value' => 'meta_value_num',
				'label' => esc_html__( 'Custom Order (WooCommerce)', 'et_builder' ),
			];
		}

		// Allow plugins/themes to add custom order by options for multiple taxonomies.
		return apply_filters( 'et_builder_loop_order_by_options_multiple_taxonomies', $order_by_options, $taxonomies, $post_type );
	}
}
