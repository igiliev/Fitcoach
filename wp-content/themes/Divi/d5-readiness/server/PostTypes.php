<?php
/**
 * Class that handles post types for Divi 5 Readiness.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server;

/**
 * Class that handles post types for Divi 5 Readiness.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class PostTypes {
	/**
	 * Get post type slugs that needs to be checked / converted.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_post_type_slugs() {
		$internal_post_types = [
			// Theme Builder Templates.
			ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE,
			ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE,
			ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE,

			// Library Item.
			'et_pb_layout',

			// WordPress.
			'page',
			'post',

			// Divi/Extra/DBP.
			'project',
		];

		// Enabled post types.
		$enabled_post_types = et_builder_get_enabled_builder_post_types();

		$post_types = array_merge(
			$internal_post_types,
			$enabled_post_types
		);

		return $post_types;
	}

	/**
	 * Helper function to query and count library posts by status.
	 *
	 * @param string $status The post status to query.
	 * @return int   The count of posts with the given status.
	 */
	public static function get_library_posts_count_by_status( $status ) {
		$library_layouts = \ET_Builder_Post_Type_Layout::instance();
		$posts           = $library_layouts->query()->run( [ 'post_status' => $status ] );
		return is_array( $posts ) ? count( $posts ) : ( $posts instanceof \WP_Post ? 1 : 0 );
	}

	/**
	 * Get post types that need to be checked / converted.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$post_types = [];

		foreach ( self::get_post_type_slugs() as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$post_count       = wp_count_posts( $post_type );

			$post_types[ $post_type ] = [
				'slug'          => $post_type,
				'label'         => isset( $post_type_object->label ) ? $post_type_object->label : '',
				'singularLabel' => isset( $post_type_object->labels->singular_name ) ? $post_type_object->labels->singular_name : '',
				'count'         => $post_count,
			];

			if ( 'et_pb_layout' === $post_type ) {
				$publish_posts_count = self::get_library_posts_count_by_status( 'publish' );
				$draft_posts_count   = self::get_library_posts_count_by_status( 'draft' );
				$trash_posts_count   = self::get_library_posts_count_by_status( 'trash' );

				$post_types[ $post_type ]['count']         = [
					'publish' => $publish_posts_count . '',
					'draft'   => $draft_posts_count . '',
					'trash'   => $trash_posts_count . '',
				];
				$post_types[ $post_type ]['label']         = 'Library Items';
				$post_types[ $post_type ]['singularLabel'] = 'Library Items';
			}
		}

		return $post_types;
	}
}
