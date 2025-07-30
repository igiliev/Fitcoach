<?php
/**
 * DiviLibrary: DiviLibraryUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\DiviLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * DiviLibraryUtility class.
 *
 * This class provides various utility methods for working with the Divi Library.
 *
 * @since ??
 */
class DiviLibraryUtility {

	/**
	 * Retrieve a post by ID with optional field filtering and capability checks.
	 *
	 * This function retrieves a post object based on the provided post ID.
	 * It also supports optional field filtering and capability checks.
	 * If the post is found and passes the field filtering and capability checks,
	 * it can be returned with all its fields intact.
	 * However, if the post contains a password, the password field will be masked for security reasons.
	 *
	 * @since ??
	 *
	 * @param int|\WP_Post $post_id              The ID of the post to retrieve.
	 * @param array        $fields               Optional. An optional array of field names and corresponding values to filter the post by.
	 *                                           Each field name must exist in the post object, otherwise the function will return `null`.
	 *                                           If a field name exists and the provided value matches the field's value,
	 *                                           the post will be considered a match.
	 *                                           Default `[]`.
	 * @param array        $capabilities         Optional. An optional array of user capabilities to check against.
	 *                                           If at least one of the capabilities is not possessed by the current user for the post,
	 *                                           the function will return `null`.
	 *                                           Default `[]`.
	 * @param bool         $mask_post_password   Optional. Whether to mask the post password field if it exists. Default `true`.
	 *
	 * @return WP_Post|null                      The post object if it is found and passes the filtering and capability checks.
	 *                                           If the post is not found or fails the checks, the function will return null.
	 *
	 * @example:
	 * ```php
	 * // Retrieve a post with ID 123.
	 * $post = get_post(123);
	 *
	 * // Retrieve a post with ID 456 and filter it to only include posts with the title "Hello World".
	 * $post = get_post(456, ['post_title' => 'Hello World']);
	 *
	 * // Retrieve a post with ID 789, filter it to only include posts with the title "Hello World", and require the "edit_posts" capability.
	 * $post = get_post(789, ['post_title' => 'Hello World'], ['edit_posts']);
	 *
	 * // Retrieve a post with ID 987, mask the password field, and require the "edit_posts" capability.
	 * $post = get_post(987, [], ['edit_posts'], true);
	 *
	 * ```
	 */
	public static function get_post( $post_id, array $fields = [], array $capabilities = [], bool $mask_post_password = true ): ?\WP_Post {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return null;
		}

		$match = true;

		if ( $fields ) {
			foreach ( $fields as $field => $value ) {
				if ( ! isset( $post->{$field} ) ) {
					$match = false;
					break;
				}

				$match = is_array( $value ) && ! is_array( $post->{$field} ) ? in_array( $post->{$field}, $value, true ) : $post->{$field} === $value;

				if ( ! $match ) {
					break;
				}
			}
		}

		if ( $match && $capabilities ) {
			foreach ( $capabilities as $capability ) {
				if ( ! current_user_can( $capability, $post->ID ) ) {
					$match = false;
					break;
				}
			}
		}

		if ( $match ) {
			if ( $mask_post_password && $post->post_password ) {
				$post->post_password = '***';
			}

			return $post;
		}

		return null;
	}
}
