<?php
/**
 * Classic Editor: ClassicEditor class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\ClassicEditor;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use \WP_Post;

/**
 * ClassicEditor class.
 *
 * This class has functionality to enable certain features and provide a placeholder for Classic Editor.
 *
 * @since ??
 */
class ClassicEditor {

	/**
	 * Initialize ClassicEditor
	 *
	 * @since ??
	 */
	public function initialize() {
		// Ajax function used to enable the Divi Builder and save the post.
		add_action( 'wp_ajax_enable_divi_in_classic_editor', [ __CLASS__, 'set_divi_builder_redirect' ] );
		add_action( 'save_post', [ __CLASS__, 'redirect_to_builder' ], 10, 3 );
	}

	/**
	 * Check if the Classic Editor is enabled.
	 *
	 * This function checks the value of the 'et_enable_classic_editor' option and returns a boolean indicating
	 * whether the Classic Editor is enabled or not. The option value is compared to `'on'` to determine the status.
	 *
	 * @since ??
	 *
	 * @return bool Whether the Classic Editor is enabled or not.
	 *
	 * @example:
	 * ```php
	 * if (EditorUtils::is_enabled()) {
	 *     // Classic Editor is enabled
	 * } else {
	 *     // Classic Editor is not enabled
	 * }
	 * ```
	 */
	public static function is_enabled(): bool {
		return 'on' === et_get_option( 'et_enable_classic_editor', 'off' );
	}

	/**
	 * Add/load CSS for the Classic Editor placeholder overlay.
	 *
	 * This function adds a style block containing CSS code to customize the appearance of the Classic Editor overlay in the Divi builder.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function add_scripts(): void {
		global $typenow;

		wp_enqueue_style(
			'et-classic-editor',
			ET_BUILDER_5_URI . '/visual-builder/build/classic-editor.css',
			[],
			ET_BUILDER_PRODUCT_VERSION,
			'all'
		);

		wp_enqueue_script(
			'et-classic-editor',
			ET_BUILDER_5_URI . '/visual-builder/build/script-library-classic-editor.js',
			[ 'jquery' ],
			ET_BUILDER_PRODUCT_VERSION,
			true
		);

		$settings = array(
			'ajax_url'                            => admin_url( 'admin-ajax.php' ),
			'enable_divi_in_classic_editor_nonce' => wp_create_nonce( 'enable_divi_in_classic_editor_nonce' ),
			'post_type'                           => $typenow,
			'is_third_party_post_type'            => et_builder_is_post_type_custom( $typenow ) ? 'yes' : 'no',
		);

		wp_localize_script( 'et-classic-editor', 'classic_editor_settings', apply_filters( 'classic_editor_settings', $settings ) );
	}

	/**
	 * Get HTML element(s) that open Divi Builder placeholder overlay wrapper for Classic Editor.
	 *
	 * This method is called before the classic editor, and renders the opening
	 * HTML wrapper for the classic editor' Divi Builder placeholder overlay.
	 *
	 * @since ??
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return void
	 */
	public static function html_enable_divi_button( \WP_Post $post ): void {
		$post_id           = get_the_ID();
		$post_status       = get_post_status( $post_id );
		$post_status_class = 'auto-draft' === $post_status ? 'is-auto-draft' : '';
		?>
		<div class="et-fb-app__return-to-divi-editor">
			<a id="et_pb_use_the_builder" href="<?php echo esc_url( self::vb_activation_link() ); ?>" class="button button-primary button-large et_pb_ready <?php echo esc_attr( $post_status_class ); ?>">
				<?php esc_html_e( 'Use the Divi Builder', 'et_builder' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Get HTML element(s) that open Divi Builder placeholder overlay wrapper for Classic Editor.
	 *
	 * This method is called before the classic editor, and renders the opening
	 * HTML wrapper for the classic editor' Divi Builder placeholder overlay.
	 *
	 * @since ??
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return void
	 */
	public static function html_prefix( \WP_Post $post ): void {
		if ( ! et_builder_enabled_for_post( $post->ID ) ) {
			return;
		}
		// TODO feat(D5, Settings) Add "Return To Default Editor" button. @see https://github.com/elegantthemes/Divi/issues/31405.

		?>
		<div class="et-fb-app--classic-editor-overlay block-editor__container">
		<div class="et-fb-app__return-to-divi-editor">
			<a id="et_pb_use_the_builder" href="<?php echo esc_url( self::vb_activation_link() ); ?>" class="button button-primary button-large et_pb_ready" style="display: none">
				<?php esc_html_e( 'Use the Divi Builder', 'et_builder' ); ?>
			</a>
		</div>
		<div class="et-fb-app__overlay wp-block-divi-placeholder">
			<div class="et-fb-app__overlay-content">
				<a href="#" id="et_pb_toggle_builder" data-builder="divi" data-editor="visual-builder" class="components-button et-fb-app__overlay-button et_pb_builder_is_used">
					<?php esc_html_e( 'Switch Back To Classic Editor', 'et_builder' ); ?>
				</a>
				<span class="et-fb-app__overlay-title et-icon"></span>
				<h3 class="et-fb-app__overlay-description">
					<?php esc_html_e( 'This Layout Is Built With Divi', 'et_builder' ); ?>
				</h3>
				<div class="et-fb-app__overlay-buttons et-controls">
					<a href="<?php echo esc_url( self::vb_activation_link() ); ?>" id="overlay_edit_with_divi" class="components-button is-button is-default is-large et-fb-app__overlay-button et-fb-app__overlay-button--use-divi-builder">
						<?php esc_html_e( 'Edit with the Divi Builder', 'et_builder' ); ?>
					</a>
				</div>
				<div id="et-vb-switch-modal-wrapper" style="display: none;">
					<div class="et-vb-switch-modal">
						<div class="et-vb-modal-header">
							<h3 class="et-vb-settings-heading"><?php esc_html_e( 'Disable Builder', 'et_builder' ); ?></h3>
							<a href="#" id="et-vb-cancel-switch" type="button"></a>
						</div>
						<div class="et-vb-modal-body">
							<p><?php esc_html_e( 'All content created in the Divi Builder will be lost. Previous content will be restored. Do you wish to proceed?', 'et_builder' ); ?></p>
						</div>
						<button id="et-vb-confirm-switch" class="et-vb-modal-button"><?php esc_html_e( 'Yes', 'et_builder' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get HTML element Open Divi Builder placeholder overlay wrapper for Classic Editor.
	 *
	 * This method is called after the classic editor, and renders the closing
	 * HTML wrapper for the classic editor' Divi Builder placeholder overlay.
	 *
	 * If `et_builder_enabled_for_post( $post->ID )` is `true`, no action is taken,
	 * otherwise `</div>` is printed.
	 *
	 * @since ??
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return void
	 */
	public static function html_suffix( \WP_Post $post ): void {
		if ( ! et_builder_enabled_for_post( $post->ID ) ) {
			return;
		}

		print '</div>';
	}

	/**
	 * Get HTML element(s) that open Divi Builder placeholder overlay wrapper for Classic Editor.
	 *
	 * This method is called before the classic editor, and renders the opening
	 * HTML wrapper for the classic editor' Divi Builder placeholder overlay.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public static function vb_activation_link(): string {
		$post_id     = get_the_ID();
		$page_url    = get_the_permalink();
		$post_status = get_post_status( $post_id );

		// If Divi isn't enabled on the post, we need to supply the VB activation nonce to handle VB activation.
		$use_visual_builder_url = et_pb_is_pagebuilder_used( $post_id ) ?
		et_fb_get_vb_url() :
		add_query_arg(
			array(
				'et_fb_activation_nonce' => wp_create_nonce( 'et_fb_activation_nonce_' . $post_id ),
			),
			$page_url
		);

		// If the post is a newly-created auto-draft, we need to use ajax to save the post and redirect.
		// The button shouldn't go anywhere when clicked as we click the save button instead.
		if ( 'auto-draft' === $post_status ) {
			$use_visual_builder_url = '#';
		}

		return $use_visual_builder_url;
	}

	/**
	 * Set Redirect Flag When Use Builder Button Is Clicked
	 *
	 * When the Use Divi Builder button is clicked, we want the post to save, the VB to be activated and the user to get redirected to the VB.
	 * We tap into the save_post action, and redirect users the VB if they just activated it.
	 * Post meta is updated via Ajax prior to saving the post, setting a temporary flag.
	 * If the flag exists during a post save that isn't an auto-save, we redirect them to the builder and activate the VB.
	 */
	public static function set_divi_builder_redirect() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'enable_divi_in_classic_editor_nonce' ) ) {
			wp_die();
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

		// Ensure user has permission to edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die();
		}

		// Set a flag (via post meta) to redirect Divi Builder after the post is saved.
		// We'll remove this after redirecting so it only happens once.
		update_post_meta( $post_id, '_et_enable_divi_redirect', 'yes' );

		// On success, the JS function initiates a normal post save by clicking the save button in the post editor.
		wp_send_json_success();
	}

	/**
	 * Redirect To Builder After Saving Post
	 *
	 * If the user has just activated the Divi Builder on a post, we need to redirect them to the Visual Builder.
	 * If _et_enable_divi_redirect is set to 'yes' in post meta, we know the user just activated Divi.
	 * In that case, we activate the builder, redirect them to the VB, and delete the flag.
	 *
	 * @param string $post_id Post ID of the post being saved.
	 * @param object $post    Post object contains post data of post being saved.
	 * @param bool   $update  Whether or not the post is being updated or created.
	 */
	public static function redirect_to_builder( $post_id, $post, $update ) {
		// Return if the post is being saved via Ajax or a cron job.
		// We only need to redirect when a user is present.
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		// If this is a new post being created, we don't need to do anything.
		if ( ! $update ) {
			return;
		}

		// Check if the current save action is an auto-save or a revision.
		// We don't want to redirect people unless they initiate a purposeful save.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Ensure user has permission to edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Retrieve the current post data.
		$post = get_post( $post_id );

		// If this post is older than 24 hours, it's not relevant.
		// Users should only get reirected when they are making a new post.
		if ( strtotime( $post->post_date ) < time() - DAY_IN_SECONDS ) {
			// Delete the redirect flag so it doesn't cause issues in the future.
			delete_post_meta( $post_id, '_et_enable_divi_redirect' );
			return;
		}

		// If the post was just created, it will trigger the save_post action.
		// We don't want to run this function when a post is first created, so we bail if the post is an auto-draft.
		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		// If the redirect flag is not set, we don't need to continue.
		if ( 'yes' !== get_post_meta( $post_id, '_et_enable_divi_redirect', true ) ) {
			return;
		}

		// Remove the flag so it doesn't persist for future saves.
		delete_post_meta( $post_id, '_et_enable_divi_redirect' );

		// Generate the redirect URL to the Visual Builder.
		// We need to activate the builder using the et_fb_activation_nonce.
		$redirect_url = esc_url_raw(
			add_query_arg(
				'et_fb_activation_nonce',
				wp_create_nonce( 'et_fb_activation_nonce_' . $post_id ),
				et_fb_prepare_ssl_link( get_permalink( $post_id ) )
			)
		);

		// Redirect to the Visual Builder and activate it.
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Remove D4 actions.
	 *
	 * This function removes various actions related to the Divi Builder D4 version.
	 * It disables the D4 preboot script, dequeues D4 scripts, dequeues the code snippet library in VB,
	 * and dequeues D4 assets (styles & scripts).
	 * It ideally should be called during the initialization of the Divi Builder to remove these default D4 actions.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *   // This function is typically called during the initialization of the Divi Builder,
	 *   // to remove the default D4 actions.
	 *
	 *   ClassicEditor::remove_d4_actions();
	 * ```
	 */
	public static function remove_d4_actions(): void {
		// Disable D4 preboot script.
		remove_action( 'wp_head', 'et_builder_inject_preboot_script', 0 );

		// Dequeue D4 scripts.
		remove_action( 'wp_footer', 'et_fb_wp_footer' );

		// Dequeue code snippet library in VB because it causes devtool failed to be loaded.
		remove_action( 'wp_enqueue_scripts', 'et_code_snippets_vb_enqueue_scripts' );

		// Dequeue D4 assets (styles & scripts).
		remove_action( 'wp_enqueue_scripts', 'et_builder_enqueue_assets_main', 99999999 );
		remove_action( 'init', 'et_builder_settings_init', 100 );
		remove_action( 'current_screen', 'et_builder_settings_init' );
		remove_action( 'init', 'et_builder_register_assets', 11 );
		remove_action( 'add_meta_boxes', 'et_pb_add_custom_box' );
		remove_action( 'add_meta_boxes', 'et_builder_prioritize_meta_box', 999999 );
		remove_action( 'init', 'et_pb_setup_theme', 11 );
	}
}

$classic_editor = new ClassicEditor();
$classic_editor->initialize();
