<?php
/**
 * Class that handles conversion for Divi 5 Readiness.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server;

use ET\Builder\Packages\Conversion\Conversion as D5BuilderConversion;

/**
 * Class that handles conversion for Divi 5 Readiness.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class Conversion {
	/**
	 * Convert single post from D4 to D5 format.
	 *
	 * @param string $post_id The post ID.
	 *
	 * @return array
	 */
	public static function convert_d4_to_d5_single( $post_id ) {
		$utils = \ET_Core_Data_Utils::instance();

		// Get saved D4 post.
		$d4_post    = get_post( $post_id );
		$d4_content = $d4_post->post_content;

		$post_conversion_content = D5BuilderConversion::maybeConvertContent( $d4_content );

		// Wrap content within placeholder.
		$post_conversion_content = "<!-- wp:divi/placeholder -->{$post_conversion_content}<!-- /wp:divi/placeholder -->";

		// Implement conversion here.
		// For now we will leave the content unchanged.
		$post_conversion = [
			'status'          => 'success', // Can be success, partial, or failed.
			'content'         => $post_conversion_content,
			'error'           => '', // Error, if returned by the conversion method.
			'builder_version' => ET_BUILDER_VERSION,
		];

		$d5_content = $post_conversion['content'];

		$conversion_status = [
			'status'          => $post_conversion['status'],
			'error'           => $post_conversion['error'],
			'builder_version' => $post_conversion['builder_version'],
		];

		// Serialize the remaining data.
		$serialized_conversion_status = wp_json_encode( $conversion_status );

		// Update the post meta.
		update_post_meta( $post_id, '_et_pb_divi_5_conversion_status', $serialized_conversion_status );

		// Return conversion status if conversion failed.
		if ( 'success' !== $post_conversion['status'] ) {
			return [
				'status' => $post_conversion['status'],
				'error'  => $post_conversion['error'],
			];
		}

		// Turn off adding post_meta while developing the feature
		// to prevent everything being set as converted to D5.
		// Update post meta so we know D5 is used.
		update_post_meta( $post_id, '_et_pb_use_divi_5', 'on' );

		// Update old D4 content.
		update_post_meta( $post_id, '_et_pb_divi_4_content', $d4_content );

		// Update post.
		$update = wp_update_post(
			[
				'ID'           => $post_id,
				'post_content' => wp_slash( $d5_content ),
				'post_status'  => $d4_post->post_status,
			]
		);

		if ( $update ) {
			// Get saved post, verify its content against the one that is being sent.
			$saved_post             = get_post( $update );
			$saved_post_content     = $saved_post->post_content;
			$converted_post_content = stripslashes( $d4_content );

			// If `post_content` column on wp_posts table doesn't use `utf8mb4` charset, the saved post
			// content's emoji will be encoded which means the check of saved post_content vs
			// builder's post_content will be false; Thus check the charset of `post_content` column
			// first then encode the builder's post_content if needed
			// @see https://make.wordpress.org/core/2015/04/02/omg-emoji-%f0%9f%98%8e/
			// @see https://make.wordpress.org/core/2015/04/02/the-utf8mb4-upgrade/.
			global $wpdb;

			if ( 'utf8' === $wpdb->get_col_charset( $wpdb->posts, 'post_content' ) ) {
				$converted_post_content = wp_encode_emoji( $converted_post_content );
			}

			$saved_verification = $saved_post_content === $converted_post_content;

			/**
			 * Hook triggered when the Post is updated.
			 *
			 * @param int $post_id Post ID.
			 *
			 * @since 3.29
			 */
			do_action( 'et_update_post', $post_id );

			return [
				'postId'           => $post_id,
				'postTitle'        => $d4_post->post_title,
				'postType'         => $d4_post->post_type,
				'postStatus'       => get_post_status( $update ),
				'postUrl'          => get_permalink( $post_id ),
				'saveVerification' => apply_filters( 'et_fb_ajax_save_verification_result', $saved_verification ),
				'status'           => 'success',
			];
		} else {
			return [
				'postId'     => $post_id,
				'postTitle'  => $d4_post->post_title,
				'postType'   => $d4_post->post_type,
				'postStatus' => get_post_status( $update ),
				'postUrl'    => get_permalink( $post_id ),
				'status'     => 'error',
			];
		}
	}

	/**
	 * Get the meta query for posts for which conversion failed or was only partially successful.
	 *
	 * @since ??
	 *
	 * @param string $post_type The post type.
	 * @param bool   $use_meta  Whether to use meta.
	 *
	 * @return array
	 */
	private static function _get_conversion_failed_meta_query( $post_type, $use_meta ) {
		$additional = [];

		$meta_query = [];

		// Get only posts for which conversion is partial or failed.
		if ( $use_meta ) {
			$meta_query[] = [
				[
					'relation' => 'AND',
					[
						'key'     => '_et_pb_use_builder',
						'value'   => 'on',
						'compare' => '=',
					],
					[
						'relation' => 'OR',
						[
							'key'     => '_et_pb_use_divi_5',
							'value'   => 'partial',
							'compare' => '=',
						],
						[
							'key'     => '_et_pb_use_divi_5',
							'value'   => 'off',
							'compare' => '=',
						],
					],
				],
			];
		}

		// Add additional condition if it exists.
		if ( ! empty( $additional ) ) {
			$meta_query[] = $additional;
		}

		return $meta_query;
	}

	/**
	 * Get posts for which conversion failed or was partially successful.
	 *
	 * @since ??
	 *
	 * @param string $post_type The post type.
	 * @param bool   $use_meta  Whether to use meta.
	 *
	 * @return array
	 */
	public static function get_posts_conversion_failed( $post_type, $use_meta ) {
		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'meta_query'     => self::_get_conversion_failed_meta_query( $post_type, $use_meta ),
		];

		$query = new \WP_Query( $args );
		$index = 0;

		$results = [];
		foreach ( $query->posts as $post ) {
			$results[ $index ] = [
				'ID'         => $post->ID,
				'post_title' => $post->post_title,
			];

			if ( 'et_template' === $post_type ) {
				$results[ $index ]['meta'] = [
					'_et_theme_builder_marked_as_unused' => get_post_meta( $post->ID, '_et_theme_builder_marked_as_unused', true ),
				];
			}

			$index++;
		}

		return $query->have_posts() ? $results : [];
	}

	/**
	 * Get the meta query for posts that have not been converted.
	 *
	 * @param string $post_type The post type.
	 * @param bool   $use_meta  Whether to use meta.
	 *
	 * @return array
	 * @since ??
	 */
	public static function _get_pending_conversion_meta_query( $post_type, $use_meta ) {
		// Initialize an empty meta query array.
		$meta_query = [];

		if ( $use_meta ) {
			// If using meta, add an 'AND' relation and both conditions.
			$meta_query = [
				'relation' => 'AND',
				[
					'key'     => '_et_pb_use_builder',
					'value'   => 'on',
					'compare' => '=',
				],
				[
					'key'     => '_et_pb_use_divi_5',
					'compare' => 'NOT EXISTS',
				],
			];

			if ( in_array( $post_type, [ ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE, ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE, ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE ], true ) ) {
				$meta_query[] = [
					'key'     => '_et_theme_builder_marked_as_unused',
					'compare' => 'NOT EXISTS',
				];
			}
		} else {
			if ( in_array( $post_type, [ ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE, ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE, ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE ], true ) ) {
				$meta_query[] = [
					'relation' => 'AND',
					[
						'key'     => '_et_theme_builder_marked_as_unused',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => '_et_pb_use_divi_5',
						'compare' => 'NOT EXISTS',
					],
				];
			} else {
				// If not using meta, only add the _et_pb_use_divi_5 condition.
				$meta_query[] = [
					'key'     => '_et_pb_use_divi_5',
					'compare' => 'NOT EXISTS',
				];
			}
		}

		return $meta_query;
	}

	/**
	 * Get posts to convert.
	 *
	 * @since ??
	 *
	 * @param string  $post_type The post type.
	 * @param boolean $use_meta Default FALSE.
	 *
	 * @return array
	 */
	public static function get_posts_pending_conversion( $post_type, $use_meta = false ) {
		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'meta_query'     => self::_get_pending_conversion_meta_query( $post_type, $use_meta ),
		];

		$query = new \WP_Query( $args );

		return $query->have_posts() ? wp_list_pluck( $query->posts, 'ID' ) : [];
	}
}
