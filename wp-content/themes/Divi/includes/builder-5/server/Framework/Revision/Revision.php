<?php
/**
 * Post Revision
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Revision;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Handles post revision.
 *
 * @since ??
 */
class Revision {
	/**
	 * Inserts post data into the posts table as a post revision.
	 *
	 * This function is a copy of WordPress core's `_wp_put_post_revision()` that has been added since version 2.6.0.
	 * The reason this function is being copied is because it is marked as private which means it is not intended
	 * to be used by third party plugin or themes although the function is accessible by third party.
	 * Thus the next best possible option is to have it copied into Divi codebase (with slight adjustment).
	 *
	 * @since ??
	 *
	 * @param int|WP_Post|array|null $post     Post ID, post object OR post array.
	 * @param bool                   $autosave Optional. Whether the revision is an autosave or not.
	 * @return int|WP_Error WP_Error or 0 if error, new revision ID if success.
	 */
	public static function put_post_revision( $post = null, $autosave = false ) {
		if ( is_object( $post ) ) {
			$post = get_object_vars( $post );
		} elseif ( ! is_array( $post ) ) {
			$post = get_post( $post, ARRAY_A );
		}

		if ( ! $post || empty( $post['ID'] ) ) {
			return new WP_Error( 'invalid_post', __( 'Invalid post ID.' ) );
		}

		if ( isset( $post['post_type'] ) && 'revision' === $post['post_type'] ) {
			return new WP_Error( 'post_type', __( 'Cannot create a revision of a revision' ) );
		}

		// Original Post ID.
		$post_id = $post['ID'];

		$post = self::post_revision_data( $post, $autosave );
		$post = wp_slash( $post ); // Since data is from DB.

		$revision_id = wp_insert_post( $post, true );
		if ( is_wp_error( $revision_id ) ) {
			return $revision_id;
		}

		if ( $revision_id ) {
			/**
			 * Fires once a revision has been saved.
			 *
			 * @param int $revision_id Post revision ID.
			 * @param int $post_id     Post ID.
			 */
			do_action( '_wp_put_post_revision', $revision_id, $post_id );
		}

		return $revision_id;
	}

	/**
	 * Returns a post array ready to be inserted into the posts table as a post revision.
	 *
	 * This function is a copy of WordPress core's `_wp_post_revision_data()` that has been added since version 4.5.0.
	 * The reason this function is being copied is because it is marked as private which means it is not intended
	 * to be used by third party plugin or themes although the function is accessible by third party.
	 * Thus the next best possible option is to have it copied into Divi codebase with slight adjustment.
	 *
	 * @since ??
	 *
	 * @param array|WP_Post $post     Optional. A post array or a WP_Post object to be processed
	 *                                for insertion as a post revision. Default empty array.
	 * @param bool          $autosave Optional. Is the revision an autosave? Default false.
	 * @return array Post array ready to be inserted as a post revision.
	 */
	public static function post_revision_data( $post = array(), $autosave = false ) {
		if ( ! is_array( $post ) ) {
			$post = get_post( $post, ARRAY_A );
		}

		$fields = self::post_revision_fields( $post );

		$revision_data = array();

		foreach ( array_intersect( array_keys( $post ), array_keys( $fields ) ) as $field ) {
			$revision_data[ $field ] = $post[ $field ];
		}

		$revision_data['post_parent']   = $post['ID'];
		$revision_data['post_status']   = 'inherit';
		$revision_data['post_type']     = 'revision';
		$revision_data['post_name']     = $autosave ? "$post[ID]-autosave-v1" : "$post[ID]-revision-v1"; // "1" is the revisioning system version.
		$revision_data['post_date']     = isset( $post['post_modified'] ) ? $post['post_modified'] : '';
		$revision_data['post_date_gmt'] = isset( $post['post_modified_gmt'] ) ? $post['post_modified_gmt'] : '';

		return $revision_data;
	}

	/**
	 * Determines which fields of posts are to be saved in revisions.
	 *
	 * This function is a copy of WordPress core's `_wp_post_revision_fields()` that has been added since version 2.6.0.
	 * The reason this function is being copied is because it is marked as private which means it is not intended
	 * to be used by third party plugin or themes although the function is accessible by third party.
	 * Thus the next best possible option is to have it copied into Divi codebase with slight adjustment.
	 *
	 * @since ??
	 *
	 * @param array|WP_Post $post       Optional. A post array or a WP_Post object being processed
	 *                                  for insertion as a post revision. Default empty array.
	 * @return string[] Array of fields that can be versioned.
	 */
	public static function post_revision_fields( $post = array() ) {
		static $fields = null;

		if ( ! is_array( $post ) ) {
			$post = get_post( $post, ARRAY_A );
		}

		if ( is_null( $fields ) ) {
			// Allow these to be versioned.
			$fields = array(
				'post_title'   => __( 'Title' ),
				'post_content' => __( 'Content' ),
				'post_excerpt' => __( 'Excerpt' ),
			);
		}

		/**
		 * Filters the list of fields saved in post revisions.
		 *
		 * Included by default: 'post_title', 'post_content' and 'post_excerpt'.
		 *
		 * Disallowed fields: 'ID', 'post_name', 'post_parent', 'post_date',
		 * 'post_date_gmt', 'post_status', 'post_type', 'comment_count',
		 * and 'post_author'.
		 *
		 * @param string[] $fields List of fields to revision. Contains 'post_title',
		 *                         'post_content', and 'post_excerpt' by default.
		 * @param array    $post   A post array being processed for insertion as a post revision.
		 */
		$fields = apply_filters( '_wp_post_revision_fields', $fields, $post );

		// WP uses these internally either in versioning or elsewhere - they cannot be versioned.
		foreach ( array( 'ID', 'post_name', 'post_parent', 'post_date', 'post_date_gmt', 'post_status', 'post_type', 'comment_count', 'post_author' ) as $protect ) {
			unset( $fields[ $protect ] );
		}

		return $fields;
	}
}
