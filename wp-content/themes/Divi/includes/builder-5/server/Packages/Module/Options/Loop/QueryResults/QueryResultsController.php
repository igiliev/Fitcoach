<?php
/**
 * Loop QueryResults: QueryResultsController.
 *
 * @package Builder\Packages\Module\Options\Loop\QueryResults
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Loop\QueryResults;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;
use WP_User_Query;
use WP_Term_Query;

/**
 * Query Result REST Controller class.
 *
 * @since ??
 */
class QueryResultsController extends RESTController {

	/**
	 * Default items per page for all query types.
	 *
	 * @var int
	 */
	const DEFAULT_PER_PAGE = 10;

	/**
	 * Return query results based on the specified query_type.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$query_type = $request->get_param( 'query_type' );
		$params     = $request->get_params();

		// Get current post ID with full fallback logic.
		$params['current_post_id'] = self::_get_current_post_id( $params );
		$result                    = [];

		switch ( $query_type ) {
			case 'post_type':
				$result = self::_get_post_type_results( $params );
				break;

			case 'terms':
				$result = self::_get_terms_results( $params );
				break;

			case 'users':
				$result = self::_get_users_results( $params );
				break;

			default:
				return rest_ensure_response( self::response_error( 'Invalid query_type specified' ) );
		}

		return self::response_success( $result );
	}

	/**
	 * Get current post ID with fallback mechanisms.
	 *
	 * @since ??
	 *
	 * @param array $params Request parameters.
	 *
	 * @return int Current post ID.
	 */
	private static function _get_current_post_id( array $params ): int {
		// Try to get from request params first.
		if ( isset( $params['current_post_id'] ) ) {
			return (int) $params['current_post_id'];
		}

		// Fall back to global state.
		$current_post_id = get_the_ID();
		if ( ! empty( $current_post_id ) ) {
			return (int) $current_post_id;
		}

		// Last resort: try to get from HTTP referer.
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer_url   = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			$referer_parts = wp_parse_url( $referer_url );
			$referer_query = [];

			if ( isset( $referer_parts['query'] ) ) {
				parse_str( $referer_parts['query'], $referer_query );
			}

			// Try to get post ID from URL query parameters.
			if ( isset( $referer_query['post'] ) ) {
				return (int) $referer_query['post'];
			}
		}

		return 0;
	}

	/**
	 * Check if a parameter represents a boolean true value.
	 *
	 * @since ??
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool Whether the value represents true.
	 */
	private static function _is_true( $value ): bool {
		return 'on' === $value;
	}

	/**
	 * Get pagination parameters for a query.
	 *
	 * @since ??
	 *
	 * @param array  $params        Query parameters.
	 * @param string $per_page_key  Query-specific per page key.
	 * @param string $offset_key    Query-specific offset key.
	 *
	 * @return array Array containing per_page, page, and offset.
	 */
	private static function _get_pagination_params( array $params, string $per_page_key, string $offset_key ): array {
		$per_page = isset( $params['per_page'] ) && '' !== $params['per_page'] ?
			(int) $params['per_page'] : self::DEFAULT_PER_PAGE;

		// Support for query-specific per_page parameter.
		if ( isset( $params[ $per_page_key ] ) && '' !== $params[ $per_page_key ] ) {
			$per_page = (int) $params[ $per_page_key ];
		}

		// Ensure per_page is at least 1 to prevent WordPress errors.
		$per_page = max( 1, $per_page );

		$page = isset( $params['page'] ) ? (int) $params['page'] : 1;

		// Check if direct offset parameter is provided first.
		if ( isset( $params[ $offset_key ] ) && '' !== $params[ $offset_key ] ) {
			$offset = (int) $params[ $offset_key ];
			// Calculate the corresponding page number based on the offset.
			$page = floor( $offset / $per_page ) + 1;
		} else {
			// Calculate offset from page if no direct offset is provided.
			$offset = ( $page - 1 ) * $per_page;
		}

		return [
			'per_page' => $per_page,
			'page'     => $page,
			'offset'   => $offset,
		];
	}

	// _add_ordering_params method removed as it's no longer used.

	/**
	 * Format pagination response.
	 *
	 * @since ??
	 *
	 * @param array $items      Result items.
	 * @param int   $total      Total number of items.
	 * @param int   $per_page   Items per page.
	 * @param int   $page       Current page.
	 * @param int   $offset     Applied offset.
	 *
	 * @return array Formatted response with pagination info.
	 */
	private static function _format_pagination_response( array $items, int $total, int $per_page, int $page, int $offset = 0 ): array {
		// Adjust total items and pages when offset is applied.
		$adjusted_total = max( 0, $total - $offset );
		$adjusted_pages = $adjusted_total > 0 ? ceil( $adjusted_total / $per_page ) : 0;

		return [
			'items'       => $items,
			'total_items' => $adjusted_total,
			'total_pages' => $adjusted_pages,
			'page'        => $page,
			'per_page'    => $per_page,
		];
	}

	/**
	 * Get post type query results.
	 *
	 * @since ??
	 *
	 * @param array $params Query parameters.
	 *
	 * @return array Post type query results.
	 */
	private static function _get_post_type_results( array $params ): array {
		$post_type       = isset( $params['post_type'] ) ? $params['post_type'] : 'post';
		$pagination      = self::_get_pagination_params( $params, 'posts_per_page', 'post_offset' );
		$current_post_id = isset( $params['current_post_id'] ) ? (int) $params['current_post_id'] : 0;

		// Handle multiple post types.
		if ( is_string( $post_type ) && strpos( $post_type, ',' ) !== false ) {
			$post_type = array_map( 'trim', explode( ',', $post_type ) );
		}

		$query_args = [
			'post_type'      => $post_type,
			'posts_per_page' => $pagination['per_page'],
			'offset'         => $pagination['offset'],
			'post_status'    => 'attachment' === $post_type ? [ 'inherit', 'private' ] : 'publish',
		];

		// Handle post status for attachments.
		if ( is_array( $post_type ) ) {
			if ( in_array( 'attachment', $post_type, true ) ) {
				$query_args['post_status'] = [ 'publish', 'inherit', 'private' ];
			}
		} elseif ( 'attachment' === $post_type ) {
			$query_args['post_status'] = [ 'inherit', 'private' ];
		}

		// Add taxonomy query if specified.
		if ( isset( $params['taxonomy'] ) && isset( $params['term_id'] ) ) {
			// Handle multiple taxonomies.
			$taxonomies = is_string( $params['taxonomy'] ) && strpos( $params['taxonomy'], ',' ) !== false
				? array_map( 'trim', explode( ',', $params['taxonomy'] ) )
				: $params['taxonomy'];

			// Handle multiple term IDs.
			$term_ids = $params['term_id'];
			if ( is_string( $term_ids ) && strpos( $term_ids, ',' ) !== false ) {
				$term_ids = array_map( 'intval', array_map( 'trim', explode( ',', $term_ids ) ) );
			} else {
				$term_ids = (int) $term_ids;
			}

			if ( is_array( $taxonomies ) ) {
				// Multiple taxonomies - create tax_query with OR relation.
				$tax_queries = [];
				foreach ( $taxonomies as $taxonomy ) {
					$tax_queries[] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					];
				}
				$query_args['tax_query'] = [
					'relation' => 'OR',
					array_merge( [], $tax_queries ),
				];
			} else {
				// Single taxonomy.
				$query_args['tax_query'] = [
					[
						'taxonomy' => $taxonomies,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					],
				];
			}
		}

		// Add search query if specified.
		if ( isset( $params['search'] ) ) {
			$query_args['s'] = $params['search'];
		}

		// Add ordering parameters.
		if ( isset( $params['order_by'] ) ) {
			// Validate that this is a supported order_by parameter.
			$valid_order_by = [
				'none',
				'ID',
				'author',
				'title',
				'name',
				'type',
				'date',
				'modified',
				'parent',
				'rand',
				'comment_count',
				'menu_order',
			];

			// Add WooCommerce specific options if any post type is product.
			$post_types = is_array( $post_type ) ? $post_type : [ $post_type ];
			if ( in_array( 'product', $post_types, true ) && function_exists( 'WC' ) ) {
				$valid_order_by[] = 'price';
				$valid_order_by[] = 'popularity';
				$valid_order_by[] = 'rating';
			}

			if ( in_array( $params['order_by'], $valid_order_by, true ) ) {
				$query_args['orderby'] = $params['order_by'];
			}
		}

		if ( isset( $params['order'] ) ) {
			$order               = 'descending' === $params['order'] ? 'DESC' : 'ASC';
			$query_args['order'] = $order;
		}

		// Handle exclude_current_post for all post types.
		if ( isset( $params['exclude_current_post'] ) ) {
			if ( self::_is_true( $params['exclude_current_post'] ) ) {
				$excluded_ids = [];

				// Add current post ID if available.
				if ( 0 !== $current_post_id ) {
					$excluded_ids[] = $current_post_id;
				}

				// If post_id parameter is provided, exclude that specific post.
				if ( isset( $params['post_id'] ) && ! empty( $params['post_id'] ) ) {
					// Handle multiple post IDs.
					if ( is_string( $params['post_id'] ) && strpos( $params['post_id'], ',' ) !== false ) {
						$post_ids     = array_map( 'intval', array_map( 'trim', explode( ',', $params['post_id'] ) ) );
						$excluded_ids = array_merge( $excluded_ids, $post_ids );
					} else {
						$excluded_ids[] = (int) $params['post_id'];
					}
				}

				if ( ! empty( $excluded_ids ) ) {
					$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ?
						array_merge( $query_args['post__not_in'], $excluded_ids ) :
						$excluded_ids;
				}
			}
		}

		// Handle sticky posts for post type 'post'.
		$post_types = is_array( $post_type ) ? $post_type : [ $post_type ];
		if ( in_array( 'post', $post_types, true ) ) {
			// Always set ignore_sticky_posts to 1 when ordering by non-date fields.
			// This ensures sticky posts are included in the sorting.
			if ( isset( $params['order_by'] ) && 'date' !== $params['order_by'] ) {
				$query_args['ignore_sticky_posts'] = 1;
			}

			// Handle explicit ignore_sticky_posts parameter.
			if ( isset( $params['ignore_sticky_posts'] ) && self::_is_true( $params['ignore_sticky_posts'] ) ) {
				// Ensure sticky posts are completely ignored.
				$query_args['ignore_sticky_posts'] = 1;

				// Get all sticky posts.
				$sticky_posts = get_option( 'sticky_posts' );

				if ( ! empty( $sticky_posts ) ) {
					if ( isset( $query_args['post__not_in'] ) ) {
						// Add sticky posts to the existing exclusion list.
						$query_args['post__not_in'] = array_unique(
							array_merge( $query_args['post__not_in'], $sticky_posts )
						);
					} else {
						// Create a new exclusion list with sticky posts.
						$query_args['post__not_in'] = $sticky_posts;
					}
				}
			}
		}

		$query = new WP_Query( $query_args );
		$posts = [];

		foreach ( $query->posts as $post ) {
			$posts[] = [
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'excerpt'   => get_the_excerpt( $post ),
				'permalink' => get_permalink( $post->ID ),
				'date'      => get_the_date( '', $post ),
				'author'    => get_the_author_meta( 'display_name', $post->post_author ),
				'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ),
				'post_type' => $post->post_type,
			];
		}

		return self::_format_pagination_response(
			$posts,
			$query->found_posts,
			$pagination['per_page'],
			$pagination['page'],
			$pagination['offset']
		);
	}

	/**
	 * Get terms query results.
	 *
	 * @since ??
	 *
	 * @param array $params Query parameters.
	 *
	 * @return array Terms query results.
	 */
	private static function _get_terms_results( array $params ): array {
		$taxonomy   = isset( $params['taxonomy'] ) ? $params['taxonomy'] : 'category';
		$pagination = self::_get_pagination_params( $params, 'terms_per_page', 'term_offset' );

		// Handle multiple taxonomies.
		if ( is_string( $taxonomy ) && strpos( $taxonomy, ',' ) !== false ) {
			$taxonomy = array_map( 'trim', explode( ',', $taxonomy ) );
		}

		$query_args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'number'     => $pagination['per_page'],
			'offset'     => $pagination['offset'],
		];

		// Add search query if specified.
		if ( isset( $params['search'] ) ) {
			$query_args['search'] = $params['search'];
		}

		// Add ordering parameters.
		if ( isset( $params['order_by'] ) ) {
			// Validate that this is a supported order_by parameter.
			$valid_order_by = [
				'name',
				'slug',
				'term_id',
				'id',
				'description',
				'count',
				'none',
				'parent',
				'term_order',
			];

			// If WooCommerce is active and it's a product category, add meta_value_num.
			$taxonomies = is_array( $taxonomy ) ? $taxonomy : [ $taxonomy ];
			if ( function_exists( 'WC' ) && in_array( 'product_cat', $taxonomies, true ) ) {
				$valid_order_by[] = 'meta_value_num';
			}

			if ( in_array( $params['order_by'], $valid_order_by, true ) ) {
				$query_args['orderby'] = $params['order_by'];
			}
		}

		if ( isset( $params['order'] ) ) {
			$order               = 'descending' === $params['order'] ? 'DESC' : 'ASC';
			$query_args['order'] = $order;
		}

		$term_query = new WP_Term_Query( $query_args );
		$terms      = [];

		if ( ! empty( $term_query->terms ) ) {
			foreach ( $term_query->terms as $term ) {
				$terms[] = [
					'id'          => $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
					'count'       => $term->count,
					'permalink'   => get_term_link( $term ),
					'taxonomy'    => $term->taxonomy,
				];
			}
		}

		// Count total terms for pagination.
		$count_args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'fields'     => 'count',
		];

		if ( isset( $params['search'] ) ) {
			$count_args['search'] = $params['search'];
		}

		$total_terms = wp_count_terms( $count_args );

		return self::_format_pagination_response(
			$terms,
			$total_terms,
			$pagination['per_page'],
			$pagination['page'],
			$pagination['offset']
		);
	}

	/**
	 * Get users query results.
	 *
	 * @since ??
	 *
	 * @param array $params Query parameters.
	 *
	 * @return array Users query results.
	 */
	private static function _get_users_results( array $params ): array {
		$pagination = self::_get_pagination_params( $params, 'users_per_page', 'user_offset' );

		$query_args = [
			'number' => $pagination['per_page'],
			'offset' => $pagination['offset'],
		];

		// Add role filter if specified.
		if ( isset( $params['role'] ) ) {
			// Handle multiple roles.
			if ( is_string( $params['role'] ) && strpos( $params['role'], ',' ) !== false ) {
				$roles                  = array_map( 'trim', explode( ',', $params['role'] ) );
				$query_args['role__in'] = $roles;
			} else {
				$query_args['role'] = $params['role'];
			}
		}

		// Add search query if specified.
		if ( isset( $params['search'] ) ) {
			$query_args['search'] = '*' . $params['search'] . '*';
		}

		// Add ordering parameters.
		if ( isset( $params['order_by'] ) ) {
			// Validate that this is a supported order_by parameter.
			$valid_order_by = [
				'login',
				'nicename',
				'email',
				'url',
				'registered',
				'display_name',
				'name',
				'ID',
				'post_count',
			];

			if ( in_array( $params['order_by'], $valid_order_by, true ) ) {
				$query_args['orderby'] = $params['order_by'];
			}
		}

		if ( isset( $params['order'] ) ) {
			$order               = 'descending' === $params['order'] ? 'DESC' : 'ASC';
			$query_args['order'] = $order;
		}

		$user_query = new WP_User_Query( $query_args );
		$users      = [];

		foreach ( $user_query->get_results() as $user ) {
			$users[] = [
				'id'          => $user->ID,
				'name'        => $user->display_name,
				'username'    => $user->user_login,
				'email'       => $user->user_email,
				'avatar'      => get_avatar_url( $user->ID ),
				'description' => $user->description,
				'url'         => get_author_posts_url( $user->ID ),
				'roles'       => $user->roles,
			];
		}

		return self::_format_pagination_response(
			$users,
			$user_query->get_total(),
			$pagination['per_page'],
			$pagination['page'],
			$pagination['offset']
		);
	}

	/**
	 * Index action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function index_args(): array {
		return [
			'query_type'           => [
				'required'    => true,
				'type'        => 'string',
				'description' => 'Type of query to perform (post_type, terms, or users)',
				'enum'        => [ 'post_type', 'terms', 'users' ],
			],
			'post_type'            => [
				'type'        => 'string',
				'description' => 'Post type to query (when query_type is post_type). Can be a single post type or comma-separated list.',
			],
			'taxonomy'             => [
				'type'        => 'string',
				'description' => 'Taxonomy to query (when query_type is terms). Can be a single taxonomy or comma-separated list.',
			],
			'term_id'              => [
				'oneOf'       => [
					[
						'type' => 'integer',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Term ID to filter by (when query_type is post_type). Can be a single ID or comma-separated list.',
			],
			'role'                 => [
				'type'        => 'string',
				'description' => 'User role to filter by (when query_type is users). Can be a single role or comma-separated list.',
			],
			'search'               => [
				'type'        => 'string',
				'description' => 'Search term',
			],
			'per_page'             => [
				'oneOf'       => [
					[
						'type'    => 'integer',
						'minimum' => 1,
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Number of items per page',
				'default'     => self::DEFAULT_PER_PAGE,
			],
			'posts_per_page'       => [
				'oneOf'       => [
					[
						'type'    => 'integer',
						'minimum' => 1,
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Number of posts per page (used when query_type is post_type)',
				'default'     => self::DEFAULT_PER_PAGE,
			],
			'terms_per_page'       => [
				'oneOf'       => [
					[
						'type'    => 'integer',
						'minimum' => 1,
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Number of terms per page (used when query_type is terms)',
				'default'     => self::DEFAULT_PER_PAGE,
			],
			'users_per_page'       => [
				'oneOf'       => [
					[
						'type'    => 'integer',
						'minimum' => 1,
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Number of users per page (used when query_type is users)',
				'default'     => self::DEFAULT_PER_PAGE,
			],
			'page'                 => [
				'type'        => 'integer',
				'description' => 'Current page',
				'default'     => 1,
			],
			'post_offset'          => [
				'oneOf'       => [
					[
						'type' => 'integer',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Offset for posts query (overrides page calculation)',
				'default'     => 0,
			],
			'term_offset'          => [
				'oneOf'       => [
					[
						'type' => 'integer',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Offset for terms query (overrides page calculation)',
				'default'     => 0,
			],
			'user_offset'          => [
				'oneOf'       => [
					[
						'type' => 'integer',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Offset for users query (overrides page calculation)',
				'default'     => 0,
			],
			'order_by'             => [
				'type'        => 'string',
				'description' => 'Field to order results by (directly passed to the WordPress query)',
				'default'     => 'date',
			],
			'order'                => [
				'type'        => 'string',
				'description' => 'Order direction (ascending or descending)',
				'default'     => 'descending',
				'enum'        => [ 'ascending', 'descending' ],
			],
			'exclude_current_post' => [
				'oneOf'       => [
					[
						'type' => 'boolean',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Whether to exclude the current post from results (used when query_type is post_type, except for attachments)',
				'default'     => false,
			],
			'ignore_sticky_posts'  => [
				'oneOf'       => [
					[
						'type' => 'boolean',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Whether to ignore sticky posts in results order (used only when query_type is post_type and post_type is post)',
				'default'     => false,
			],
			'current_post_id'      => [
				'type'        => 'integer',
				'description' => 'The ID of the current post (used for exclude_current_post)',
				'default'     => 0,
			],
			'post_id'              => [
				'oneOf'       => [
					[
						'type' => 'integer',
					],
					[
						'type' => 'string',
					],
				],
				'description' => 'Specific post ID to exclude when exclude_current_post is true. Can be a single ID or comma-separated list.',
				'default'     => 0,
			],
		];
	}

	/**
	 * Index action permission.
	 *
	 * Endpoint permission callback as used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}
}
