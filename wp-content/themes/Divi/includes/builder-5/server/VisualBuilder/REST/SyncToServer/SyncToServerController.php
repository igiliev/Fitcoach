<?php
/**
 * REST: SyncToServerController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\SyncToServer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\VisualBuilder\Saving\SavingUtility;
use ET\Builder\VisualBuilder\Workspace\Workspace;
use ET\Builder\Framework\Revision\Revision;
use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicData;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * SyncToServerController class.
 *
 * This class extends the RESTController class and provides functionality for syncing data to the server.
 *
 * @since ??
 */
class SyncToServerController extends RESTController {

	/**
	 * Update post content and status.
	 *
	 * This function updates the post content and status based on the given parameters.
	 * It retrieves the post ID, post status, content, and preferences from the provided `$request` object.
	 * It then calls the `wp_update_post()` function to update the post with the new content and status.
	 * If there is an autosave that is newer, it deletes the existing autosave.
	 * It also calls `et_save_post` and `divi_visual_builder_rest_save_post` action hooks to perform actions before the update
	 * as well as `et_update_post`, and `divi_visual_builder_rest_update_post` action hooks and also `et_fb_ajax_save_verification_result`,
	 * and `divi_visual_builder_rest_save_post_save_verification` filters all applied after the update.
	 * Finally, it sanitizes and updates the app preferences using the `SavingUtility::sanitize_app_preferences()`, filters the save
	 * verification result, and returns the updated post status, save verification status, and rendered content as a response.
	 *
	 * @since 3.2.0
	 *
	 * @deprecated 5.0.0 Use `divi_visual_builder_rest_save_post` hook instead.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error `WP_REST_Response` object on success, `WP_Error` object on failure.
	 */
	public static function update( WP_REST_Request $request ) {
		$post_id       = (int) $request->get_param( 'post_id' );
		$post_status   = $request->get_param( 'post_status' );
		$content       = $request->get_param( 'content' );
		$preferences   = $request->get_param( 'preferences' );
		$page_settings = $request->get_param( 'pageSettings' );
		$sync_type     = $request->get_param( 'syncType' );
		$options       = $request->get_param( 'options' );
		$workspace     = $request->get_param( 'workspace' );

		if ( isset( $page_settings['customCss'] ) ) {
			// update_post_meta() in et_builder_update_settings() removes backslashes from the value, this will be problematic for
			// Codemirror custom css, so we have to double escape the backslashes, to make sure backslashes are saved
			// correctly into the database. Context: https://github.com/elegantthemes/submodule-builder/pull/10314.
			$page_settings['customCss'] = str_replace( '\\', '\\\\', $page_settings['customCss'] );
		}

		$post_content = isset( $content['post_content'] ) ? $content['post_content'] : '';

		// Check if there is an autosave that is newer.
		$post_author = get_current_user_id();

		// Store one autosave per author. If there is already an autosave, overwrite it.
		$autosave = wp_get_post_autosave( $post_id, $post_author );

		if ( ! empty( $autosave ) ) {
			wp_delete_post_revision( $autosave->ID );
		}

		// Check if what is being synced to server is preview post.
		$is_saving_preview = 'preview' === $sync_type;

		// Preview post aims to have similar post previewing experience as WordPress block editor.
		// How preview post works in WordPress block editor:
		// 1. Preview button is clicked.
		// 2. REST request is sent to `/wp-json/wp/v2/pages/POST_ID/autosaves?_locale=user` endpoint.
		// 3. An autosave post for current post is created. Autosave post is another record on `wp_post` table that:
		// - has post_status: `inherit`
		// - has post_type: `revision`
		// - has post_parent pointing to the published / draft post ID
		// 4. Basically the autosave post is the most recent revision of the post. A user commonly can have only have
		// one autosave per post.
		// 5. Once autosave post is created, the preview post URL is generated and returned to the client.
		// 6. User is being redirected to the preview post URL
		// For WordPress block editor's preview post mechanism, see: `WP_REST_Autosaves_Controller->create_item()`.

		if ( $is_saving_preview ) {
			// Create new autosave as post revision that will be used for preview.
			$revision_id = Revision::put_post_revision(
				[
					'ID'           => $post_id,
					'post_title'   => $page_settings['postTitle'] ?? '',

					// `_wp_put_post_revision()` already does the `wp_slash()` for the content. No need apply `wp_slash()` here.
					'post_content' => $post_content,
				],
				true
			);

			// Create preview post link.
			// Equivalent of this on WordPress block editor can be seen at WP_REST_Autosaves_Controller->prepare_item_for_response().
			$preview_url = get_preview_post_link(
				$post_id,
				[
					'preview_id'    => $post_id,
					'preview_nonce' => wp_create_nonce( 'post_preview_' . $post_id ),
				]
			);

			return self::response_success(
				array(
					'previewId'         => $revision_id,
					'previewUrl'        => $preview_url,
					'syncType'          => 'preview',
					'post_status'       => $post_status,
					'save_verification' => $revision_id ? true : false,
				)
			);
		}

		$update = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => wp_slash( $post_content ),
				'post_status'  => $post_status,
			)
		);

		// Save page settings.
		if ( $page_settings ) {
			SavingUtility::save_page_settings( $page_settings, $post_id );
		}

		// Prime page cache.
		SavingUtility::prime_page_cache_on_save( $post_id );

		/**
		 * Action hook to fire when the Post is being saved.
		 *
		 * This is for backward compatibility with hooks written for Divi version <5.0.0.
		 *
		 * @since 3.20
		 * @deprecated 5.0.0 Use `divi_visual_builder_rest_save_post` hook instead.
		 *
		 * @param int $post_id Post ID.
		 */
		do_action(
			'et_save_post',
			$post_id
		);

		/**
		 * Action hook to fire when the Post is being saved.
		 *
		 * @since ??
		 *
		 * @param int $post_id Post ID.
		 */
		do_action( 'divi_visual_builder_rest_save_post', $post_id );

		if ( $update && ! is_wp_error( $update ) ) {
			// Update post meta so we know D5 is used and Readiness migrator will skip it.
			update_post_meta( $post_id, '_et_pb_use_divi_5', 'on' );

			$preferences = SavingUtility::sanitize_app_preferences( $preferences );
			foreach ( $preferences as $preference_key => $preference_value ) {
				$option_name = 'et_fb_pref_' . $preference_key;
				et_update_option( $option_name, $preference_value );
			}

			// Updated last used workspace.
			$last_used_workspace = $workspace['last-used'] ?? [];

			Workspace::update_item( 'builtIn', 'last-used', $last_used_workspace );

			/**
			 * Action hook to fire when the Post is updated.
			 *
			 * This is for backward compatibility with hooks written for Divi version <5.0.0.
			 *
			 * @param int $post_id Post ID.
			 *
			 * @since 3.29
			 * @deprecated 5.0.0 Use `divi_visual_builder_rest_update_post` hook instead.
			 */
			do_action(
				'et_update_post',
				$post_id
			);

			/**
			 * Action hook to fire when the Post is updated.
			 *
			 * @param int $post_id Post ID.
			 *
			 * @since ??
			 */
			do_action( 'divi_visual_builder_rest_update_post', $post_id );

			$saved_post_content = get_post_field( 'post_content', $update );
			$verification       = $post_content === $saved_post_content;

			/**
			 * Filter to modify the save verification result.
			 *
			 * @since ??
			 * @deprecated 5.0.0 Use the {@see 'divi_visual_builder_rest_save_verification'} filter instead.
			 *
			 * @param bool $verification Whether to save the verification result.
			 */
			$verification = apply_filters(
				'et_fb_ajax_save_verification_result',
				$verification
			);

			/**
			 * Filter to modify the save verification result.
			 *
			 * @since ??
			 *
			 * @param bool $verification Whether to save the verification result.
			 */
			$save_verification_filtered = apply_filters(
				'divi_visual_builder_rest_save_post_save_verification',
				$verification
			);

			$return_rendered_content = $options['return_rendered_content'] ?? false;

			if ( $return_rendered_content ) {
				// Replace dynamic data in the content with the actual value.
				$normalized_content = DynamicData::get_processed_dynamic_data( $saved_post_content, $post_id, true );

				// Apply the_content filter to the content.
				$rendered_content = apply_filters( 'the_content', $normalized_content );
			} else {
				$rendered_content = $saved_post_content;
			}

			return self::response_success(
				array(
					'post_status'       => get_post_status( $update ),
					'save_verification' => $save_verification_filtered,
					'rendered_content'  => $rendered_content,
				)
			);
		}

		if ( is_wp_error( $update ) ) {
			return self::response_error( $update->get_error_code(), $update->get_error_message(), $update->get_error_data() );
		}

		return self::response_error( 'unknown_error', esc_html__( 'Unknown error.', 'et_builder' ) );
	}

	/**
	 * Update action arguments.
	 *
	 * Retrieves the arguments for the update action endpoint.
	 * These arguments are used in `register_rest_route()` to define the endpoint parameters.
	 *
	 * @since ??
	 *
	 * @return array  An associative array of arguments for the update action endpoint.
	 *
	 * @example:
	 * ```php
	 * $args = SyncToServer::update_args();
	 *
	 * // Returns an associative array of arguments for the update action endpoint.
	 * ```
	 */
	public static function update_args(): array {
		return [
			'post_id'      => [
				'required'          => true,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'get_post',
			],
			'post_status'  => [
				'default'           => 'draft',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'syncType'     => [
				'default'           => 'draft',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'content'      => [
				'default'           => [],
				'sanitize_callback' => [ __CLASS__, 'sanitize_content' ],
			],
			'pageSettings' => [
				'default'           => [],
				'sanitize_callback' => [ __CLASS__, 'sanitize_page_settings' ],
			],
		];
	}

	/**
	 * Update action permission for a post.
	 *
	 * Checks if the current user has permission to update the post with the given ID and status.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $post_id     The ID of the post to check permission for.
	 *     @type string $post_status The status of the post to check permission for.
	 * }
	 *
	 * @return bool|WP_Error Returns `true` if the current user has permission, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * $request = new WP_REST_Request();
	 * $request->set_param( 'post_id', $post_id );
	 * $request->set_param( 'post_status', $post_status );
	 * $result = SyncToServer::update_permission( $request );
	 * ```
	 */
	public static function update_permission( WP_REST_Request $request ) {
		$post_id     = $request->get_param( 'post_id' );
		$post_status = $request->get_param( 'post_status' );

		if ( ! et_fb_current_user_can_save( $post_id, $post_status ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Sanitize the content array by preparing each content item for database storage.
	 *
	 * This function takes an array of contents and loops through the array and calls the
	 * `SavingUtility::prepare_content_for_db()` to sanitize each content item.
	 * The sanitized contents are then stored in a new array and returned.
	 *
	 * @since ??
	 *
	 * @param array $contents The array of contents to be sanitized.
	 *
	 * @return array The sanitized contents array
	 *
	 * @example:
	 * ```php
	 * $contents = [
	 *    'location1' => '<p>Content 1</p>',
	 *    'location2' => '<script>alert("Content 2")</script>'
	 * ];
	 *
	 * $sanitizedContents = SyncToServer::sanitize_content($contents);
	 * // Returns the sanitized contents array
	 * ```
	 */
	public static function sanitize_content( array $contents ): array {
		$sanitized = array();

		foreach ( $contents as $location => $content ) {
			$sanitized[ $location ] = SavingUtility::prepare_content_for_db( $content );
		}

		return $sanitized;
	}

	/**
	 * Sanitize the page settings array by preparing each content item for database storage.
	 *
	 * @since ??
	 *
	 * @param array $page_settings The array of page settings to be sanitized.
	 *
	 * @return array The sanitized page settings array
	 */
	public static function sanitize_page_settings( array $page_settings ): array {
		return SavingUtility::sanitize_page_settings( $page_settings );
	}

}
