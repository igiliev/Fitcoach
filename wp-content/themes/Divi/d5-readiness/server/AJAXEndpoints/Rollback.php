<?php
/**
 * Class that handles endpoints callback for rollback site's content.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\AJAXEndpoints;

use Divi\D5_Readiness\Server\PostTypes;
use ET_Core_PageResource;

/**
 * Class that handles endpoints callback for rollback site's content.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class Rollback {
	/**
	 * Register endpoints for rollback site's content.
	 */
	public static function register_endpoints() {
		add_action( 'wp_ajax_et_d5_readiness_prepare_rollback_ids', [ self::class, 'get_rollback_ids' ] );
		add_action( 'wp_ajax_et_d5_readiness_rollback_d5_to_d4', [ self::class, 'rollback_d5_to_d4' ] );
	}

	/**
	 * Ajax Callback :: Get post IDs that are ready to be rolled back.
	 */
	public static function get_rollback_ids() {
		et_core_security_check( 'edit_posts', 'et_d5_readiness_prepare_rollback_ids', 'wp_nonce' );

		$post_types = PostTypes::get_post_type_slugs();

		$post_ids = [];

		// Collect post IDs from all relevant post types.
		foreach ( $post_types as $post_type ) {
			$args = [
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_query'     => [
					[
						'key'     => '_et_pb_use_divi_5',
						'value'   => 'on',
						'compare' => '=',
					],
				],
			];

			$post_ids = array_merge( $post_ids, get_posts( $args ) );
		}

		// Check if there are any post IDs to convert.
		if ( empty( $post_ids ) ) {
			wp_send_json_success(
				[
					'message' => __( 'There are no posts to rollback!', 'Divi' ),
					'status'  => 'no_rollbacks',
				]
			);
		}

		wp_send_json_success( [ 'ids' => $post_ids ] );
	}

	/**
	 * Ajax Callback :: Rollback D5 content to D4 format.
	 */
	public static function rollback_d5_to_d4() {
		et_core_security_check( 'edit_posts', 'et_d5_readiness_rollback_d5_to_d4', 'wp_nonce' );

		// Retrieve raw post IDs from the request and sanitize input.
		$raw_post_ids = filter_input( INPUT_POST, 'post_ids', FILTER_SANITIZE_STRING );
		$post_ids     = json_decode( $raw_post_ids, true );

		if ( ! is_array( $post_ids ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Invalid post IDs format.', 'Divi' ),
				]
			);
		}

		// Ensure all post IDs are integers and positive.
		$post_ids = array_filter(
			$post_ids,
			function ( $id ) {
				return is_int( $id ) && $id > 0;
			}
		);

		$results = [];

		foreach ( $post_ids as $post_id ) {
			$has_d4_field = metadata_exists( 'post', $post_id, '_et_pb_divi_4_content' );
			$d4_content   = get_post_meta( $post_id, '_et_pb_divi_4_content', true );

			if ( $has_d4_field ) {
				// Update post content with the D4 content.
				$update_id = wp_update_post(
					[
						'ID'           => $post_id,
						'post_content' => $d4_content,
					]
				);

				if ( $update_id && ! is_wp_error( $update_id ) ) {
					// Remove the D5 meta for a successful rollback.

					delete_post_meta( $post_id, '_et_pb_use_divi_5' );
					delete_post_meta( $post_id, '_et_pb_divi_4_content' );
					delete_post_meta( $post_id, '_et_pb_divi_5_conversion_status' );

					$results[ $post_id ] = [
						'status'  => 'success',
						'message' => __( 'Post content has been rolled back to Divi 4 format.', 'Divi' ),
					];
				}
			}
		}

		// Clear the conversion finished flag.
		et_delete_option( 'et_d5_readiness_conversion_finished' );
		ET_Core_PageResource::remove_static_resources( 'all', 'all', true );

		wp_send_json_success( $results );
	}
}
