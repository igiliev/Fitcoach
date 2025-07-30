<?php
/**
 * Module: LoopUtils class.
 *
 * @package Builder\Packages\Module\Options\Loop
 */

namespace ET\Builder\Packages\Module\Options\Loop;

use WP_Query;
use WP_Term_Query;
use WP_User_Query;
use ET\Builder\Framework\Utility\HTMLUtility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * LoopUtils class.
 *
 * @since ??
 */
class LoopUtils {
	/**
	 * Build WP_Query arguments from module $attrs.
	 *
	 * @since ??
	 *
	 * @param array $attrs The block attributes that were saved by the Visual Builder.
	 *
	 * @return array The WP_Query arguments array.
	 */
	public static function get_query_args_from_attrs( $attrs ) {
		// Extract loop settings from $attrs with defaults.
		$loop = isset( $attrs['module']['advanced']['loop'] )
			? $attrs['module']['advanced']['loop']
			: array();

		$loop_enabled = isset( $loop['enable']['desktop']['value'] )
			? $loop['enable']['desktop']['value']
			: '';

		$post_type = isset( $loop['postType']['desktop']['value'] )
			? $loop['postType']['desktop']['value']
			: 'post';

		$query_type = isset( $loop['queryType']['desktop']['value'] )
			? $loop['queryType']['desktop']['value']
			: 'post_types';

		$order_by_raw = isset( $loop['orderBy']['desktop']['value'] )
			? $loop['orderBy']['desktop']['value']
			: 'publishDate';

		$order_raw = isset( $loop['order']['desktop']['value'] )
			? $loop['order']['desktop']['value']
			: 'descending';

		$post_per_page = isset( $loop['postPerPage']['desktop']['value'] )
			? (int) $loop['postPerPage']['desktop']['value']
			: (int) get_option( 'posts_per_page' );

		$post_offset = isset( $loop['postOffset']['desktop']['value'] )
			? (int) $loop['postOffset']['desktop']['value']
			: 0;

		$ignore_stickys_post = isset( $loop['ignoreStickysPost']['desktop']['value'] )
			? $loop['ignoreStickysPost']['desktop']['value']
			: '';

		$exclude_current_post = isset( $loop['excludeCurrentPost']['desktop']['value'] )
			? $loop['excludeCurrentPost']['desktop']['value']
			: 'off';

		// Map orderBy and order values to WP_Query args.
		$order_by_map = array(
			'publishDate' => 'date',
			// Add more mappings if needed.
		);

		$order_map = array(
			'descending' => 'DESC',
			'ascending'  => 'ASC',
		);

		$order_by = isset( $order_by_map[ $order_by_raw ] )
			? $order_by_map[ $order_by_raw ]
			: $order_by_raw;

		$order = isset( $order_map[ $order_raw ] )
			? $order_map[ $order_raw ]
			: $order_raw;

		// Build WP_Query arguments, only including non-defaults for additional params.
		$query_args = array(
			'post_type'   => $post_type,
			'post_status' => 'attachment' === $post_type ? [ 'inherit', 'private' ] : 'publish',
			'orderby'     => $order_by,
			'order'       => $order,
		);

		// Only include posts_per_page if set by attribute (not default from get_option).
		if ( isset( $loop['postPerPage']['desktop']['value'] ) ) {
			$query_args['posts_per_page'] = $post_per_page;
		}

		// Only include offset if not 0.
		if ( 0 !== $post_offset ) {
			$query_args['offset'] = $post_offset;
		}

		// Only include ignore_sticky_posts if set to 'on'.
		if ( 'on' === $ignore_stickys_post ) {
			$query_args['ignore_sticky_posts'] = 1;
		}

		// Handle post exclusions.
		$excluded_ids = array();

		// Always exclude the current post to prevent infinite recursion.
		// This is a safety measure regardless of the excludeCurrentPost setting.
		$current_post_id = get_the_ID();
		if ( $current_post_id && 'post_types' === $query_type ) {
			$excluded_ids[] = $current_post_id;
		}

		// Apply post exclusions if any.
		if ( ! empty( $excluded_ids ) ) {
			$query_args['post__not_in'] = array_unique( $excluded_ids );
		}

		return [
			'loop_enabled' => $loop_enabled,
			'query_args'   => $query_args,
			'query_type'   => $query_type,
			'post_type'    => $post_type,
		];
	}

	/**
	 * Render the standardized 'No Results Found' message for Loop Builder modules.
	 *
	 * This should be used by any module implementing loop queries to ensure consistent UI.
	 *
	 * @since ??
	 *
	 * @return string The rendered HTML for the no results message.
	 */
	public static function render_no_results_found_message() {
		// Use HTMLUtility for consistent markup and escaping.
		return HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [ 'class' => 'entry' ],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => HTMLUtility::render(
					[
						'tag'        => 'h2',
						'attributes' => [ 'class' => 'not-found-title' ],
						'children'   => __( 'No Results Found.', 'et_builder' ),
					]
				) . HTMLUtility::render(
					[
						'tag'      => 'p',
						'children' => __( 'The page you requested could not be found.', 'et_builder' ) . ' ' . __( 'Try refining your search, or use the navigation above to locate the post.', 'et_builder' ),
					]
				),
			]
		);
	}
}
