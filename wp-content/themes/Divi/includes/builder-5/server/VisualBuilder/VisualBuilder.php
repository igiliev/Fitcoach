<?php
/**
 * VisualBuilder: Visual Builder class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\DependencyTree;
use ET\Builder\Framework\Settings\PageSettings;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\VisualBuilder\Assets\AssetsUtility;
use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use ET\Builder\VisualBuilder\ClassicEditor\ClassicEditor;
use ET\Builder\VisualBuilder\Hooks\HooksRegistration;
use ET\Builder\VisualBuilder\REST\RESTRegistration;
use ET\Builder\VisualBuilder\SettingsData\SettingsData;
use ET\Builder\VisualBuilder\TopWindow;

/**
 * VisualBuilder class.
 *
 * This class essentially initiates the Visual Builder on the backend and loads all the functionalities needed for
 * Visual Builder to work.
 *
 * It accepts a `DependencyTree` on construction which tells `VisualBuilder` its dependencies and the priorities to
 * load them.
 *
 * @since ??
 */
class VisualBuilder {

	/**
	 * Stores dependencies that were passed to constructor.
	 *
	 * @since ??
	 *
	 * @var DependencyTree Dependency tree for VisualBuilder to load.
	 */
	private $_dependency_tree;

	/**
	 * Create an instance of the VisualBuilder class.
	 *
	 * Constructs class and sets dependencies for `VisualBuilder` to load.
	 *
	 * @since ??
	 *
	 * @param DependencyTree $dependency_tree Dependency tree for VisualBuilder to load.
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}

	/**
	 * Load and initialize the Divi Builder if `et_builder_d5_enabled()` is true.
	 *
	 * If D5 is enabled in settings, the Visual Builder will be loaded, otherwise D4 is loaded.
	 *
	 * Checks if the content is built with Divi, if the Divi 5 Visual Builder should be loaded,
	 * and if the Classic Editor is enabled. If the content meets all the conditions, the Visual Builder
	 * will be forced to be used for the Classic Editor in the backend. The D4 default actions are removed,
	 * and new actions are added to include CSS and HTML prefixes and suffixes for the Visual Builder.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *   // This function is typically called during the initialization of the Divi Builder,
	 *   // to set up the necessary actions and configurations for the Visual Builder.
	 *
	 *   VisualBuilder::initialize();
	 * ```
	 */
	public function initialize(): void {
		// Is this content built with Divi?
		$is_built_with_divi = $this->is_built_with_divi();

		// Should we load the Divi 5 Visual Builder?
		$load_d5 = $this->should_load_d5_visual_builder();

		$post_type = isset( $_GET['post'] ) ? get_post_type( intval( $_GET['post'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification is not required here.

		// Define whether the Divi Classic Editor is enabled.
		$divi_classic_editor_enabled = false;
		if ( class_exists( 'ET\\Builder\\VisualBuilder\\ClassicEditor\\ClassicEditor' ) ) {
			$divi_classic_editor_enabled = ClassicEditor::is_enabled();
		}

		// If the WordPress Classic Editor Plugin exists, define whether it is used on the current page.
		$wp_classic_editor_enabled = false;
		if ( class_exists( 'Classic_Editor' ) ) {
			$wp_classic_editor_replace_option = get_option( 'classic-editor-replace' );

			$wp_classic_editor_block_settings = [ 'block', 'no-replace' ];

			// If the option value is not set to 'block' or 'no-replace', then the Classic Editor is enabled here.
			$wp_classic_editor_enabled = ! in_array( $wp_classic_editor_replace_option, $wp_classic_editor_block_settings, true );
		}

		// Check if post type is `et_pb_layout` or WooCommerce `product` because these use Classic Editor by default.
		$is_et_pb_layout_post_type        = ET_BUILDER_LAYOUT_POST_TYPE === $post_type;
		$is_woocommerce_product_post_type = et_is_woocommerce_plugin_active() && 'product' === $post_type;
		$is_classic_editor_post_type      = $is_et_pb_layout_post_type || $is_woocommerce_product_post_type;

		// Check if either Divi Classic Editor or WordPress Classic Editor is enabled.
		$classic_editor_enabled = $divi_classic_editor_enabled || $wp_classic_editor_enabled || $is_classic_editor_post_type;

		// If we are in the classic Editor, remove unused D4 actions and add D5 Classic Editor CSS.
		if ( is_admin() && $classic_editor_enabled ) {
			// Add CSS & JS that styles the UI for switching between Divi and the Classic Editor.
			add_action( 'admin_enqueue_scripts', [ ClassicEditor::class, 'add_scripts' ] );
		}

		// If we are in the classic Editor and the builder is not enabled, add the Use The Divi Builder button.
		if ( is_admin() && ! $is_built_with_divi && $classic_editor_enabled ) {
			add_action( 'edit_form_after_title', [ ClassicEditor::class, 'html_enable_divi_button' ] );

			return;
		}

		// If we are in the Classic Editor and the builder is enabled, hide the Classic Editor and add the Divi Builder block.
		if ( is_admin() && $is_built_with_divi && $load_d5 && $classic_editor_enabled ) {
			// Remove the default D4 actions.
			ClassicEditor::remove_d4_actions();

			add_action( 'edit_form_after_title', [ ClassicEditor::class, 'html_prefix' ] );
			add_action( 'edit_form_after_editor', [ ClassicEditor::class, 'html_suffix' ] );

			return;
		}

		// TODO feat(D5, Cache) Switching from D5 to D4 or the reverse might require some refreshes before the cache
		// is fully replaced. Try exiting Visual Builder to Front End then go back to Visual Builder.

		$this->_dependency_tree->load_dependencies();

		if ( $this->should_load_d5_visual_builder() ) {
			add_action( 'et_fb_framework_loaded', [ $this, 'load_d5_visual_builder' ] );

			do_action( 'divi_visual_builder_initialize' );
		}
	}

	/**
	 * Determine if the current content in the WordPress Admin is built using the Divi.
	 *
	 * This function checks if the current page is a WP-Admin "Add" or "Edit" page and determines if Divi
	 * has been used to build the content. It accesses the `$_GET` global variable to get the post ID and
	 * performs a check to verify if the content is built with Divi using the `et_pb_is_pagebuilder_used` function.
	 *
	 * @since ??
	 *
	 * @return bool Whether the current content is built with Divi (`true`) or not (`false`).
	 *
	 * @example:
	 * ```php
	 *   // This function can be used to conditionally load certain resources or configurations
	 *   // specific to Divi-built content. It can be called in different parts of the code to
	 *   // determine if Divi has been used to build the current page.
	 *
	 *   $is_built_with_divi = $this->is_built_with_divi();
	 *   if ( $is_built_with_divi ) {
	 *       // Perform specific actions for Divi-built content
	 *   } else {
	 *       // Perform actions for non-Divi built content
	 *   }
	 * ```
	 */
	public function is_built_with_divi(): bool {
		global $pagenow;

		$is_divi_page = false;
		$post_id      = 0;

		// Is this a WP-Admin "Add" or "Edit" page?
		$is_admin_add_or_edit_page = in_array( $pagenow, array( 'edit.php', 'post-new.php', 'post.php' ), true );

		if ( $is_admin_add_or_edit_page ) {
			// reason: Since we are accessing $_GET only for the comparision, nonce verification is not required.
			// phpcs:disable WordPress.Security.NonceVerification
			$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		if ( $post_id ) {
			// Has Divi has been used to build the page?
			$is_divi_page = et_pb_is_pagebuilder_used( $post_id );
		}

		return $is_divi_page;
	}

	/**
	 * Determine if the Divi 5 Visual Builder should be loaded.
	 *
	 * This function checks the URL parameters to determine if the Divi 5 Visual Builder should be loaded.
	 * It checks for the presence of the following parameters: `et_tb`, `et_bfb`, `et_block_layout_preview` in the URL.
	 * If any of these parameters are present with a value of '1', the function returns `true`, indicating that
	 * the Divi 5 Visual Builder should be loaded. Otherwise, it returns `false`.
	 *
	 * Note: This is a temporary solution to prevent D5 Visual Builder from loading on certain pages.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the Divi 5 Visual Builder should be loaded, `false` otherwise.
	 *
	 * @example:
	 * ```php
	 * if ( should_load_d5_visual_builder() ) {
	 *     // Load the Divi 5 Visual Builder
	 *     load_d5_visual_builder();
	 * }
	 * ```
	 */
	public function should_load_d5_visual_builder(): bool {
		// reason: Since we are accessing $_GET only for the comparision, nonce verification is not required.
		// phpcs:disable WordPress.Security.NonceVerification

		// Check for a declaration of BFB in the URL.
		$is_using_bfb = isset( $_GET['et_bfb'] ) && '1' === $_GET['et_bfb'];

		// Check for a declaration of the Layout Block in the URL.
		$is_using_layout_block = isset( $_GET['et_block_layout_preview'] ) && '1' === $_GET['et_block_layout_preview'];

		// phpcs:enable WordPress.Security.NonceVerification

		// Force D4 if any of the known exceptions are true.
		$force_d4 = $is_using_bfb || $is_using_layout_block;

		// If D5 is enabled and we are not forcing D4, then we should load D5.
		if ( \et_builder_d5_enabled() && ! $force_d4 ) {
			return true;
		}

		// If D5 is disabled or we are forcing D4, then we should load D4.
		return false;
	}

	/**
	 * Load D5 Visual Builder and all its dependencies.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load_d5_visual_builder(): void {
		// Load Divi Cloud class to enable import and export functionality.
		if ( defined( 'ET_BUILDER_PLUGIN_ACTIVE' ) ) {
			require_once ET_BUILDER_PLUGIN_DIR . '/cloud/cloud-app.php';
		} else {
			require_once get_template_directory() . '/cloud/cloud-app.php';
		}

		/**
		 * Since `wp-color-picker` and `wp-color-picker-alpha` need `wp-i18n` but don't declare it as their dependency,
		 * we'll manually enqueue it here.
		 *
		 * This is to be removed once `wp-color-picker` and `wp-color-picker-alpha` are removed from `visual-builder` dependencies
		 * in `GetPackageListTrait:get_package_list()`.
		 */
		wp_enqueue_script( 'wp-i18n' );

		// Disable D4 preboot script.
		remove_action( 'wp_head', 'et_builder_inject_preboot_script', 0 );

		// Dequeue D4 scripts.
		remove_action( 'wp_footer', 'et_fb_wp_footer' );

		// Injected script that is required early (loading external .js will be too late).
		add_action( 'wp_head', [ AssetsUtility::class, 'inject_preboot_script' ], 0 );

		// Injected style that is required early (loading external .css will be too late).
		add_action( 'wp_head', [ AssetsUtility::class, 'inject_preboot_style' ], 0 );

		wp_register_script(
			'react-tiny-mce',
			ET_BUILDER_5_URI . '/visual-builder/assets/tinymce/tinymce.min.js',
			[],
			ET_BUILDER_VERSION,
			false
		);

		// Enqueue visual builder's core dependencies, which are built by WebPack as externals e.g. react, wp-data, wp-blocks.
		add_action( 'wp_enqueue_scripts', [ AssetsUtility::class, 'enqueue_visual_builder_dependencies' ] );

		// Enqueue visual builder's packages' styles and scripts.
		add_action( 'wp_enqueue_scripts', [ PackageBuildManager::class, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ PackageBuildManager::class, 'enqueue_styles' ] );

		// Dequeue D4 assets (styles & scripts).
		remove_action( 'wp_enqueue_scripts', 'et_builder_enqueue_assets_main', 99999999 );

		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue cor font family, which is used for Visual Builder UI.
		et_core_load_main_fonts();

		// Dequeue scripts that are enqueued but not needed on top window.
		add_action( 'wp_enqueue_scripts', [ AssetsUtility::class, 'dequeue_top_window_early_scripts' ] );
		add_action( 'wp_footer', [ AssetsUtility::class, 'dequeue_top_window_late_scripts' ] );

		/**
		 * Validate enqueued scripts dependencies and trigger error if non-existing script is found.
		 *
		 * By default, WordPress lacks a validation mechanism for script dependencies. This leads to a silent failure
		 * and the script is not enqueued if any of its dependencies are missing. Given our extensive use of scripts
		 * a missing dependency can lead us down to the rabbit hole.
		 */
		add_action( 'wp_enqueue_scripts', [ AssetsUtility::class, 'validate_enqueue_script_dependencies' ], PHP_INT_MAX );

		/**
		 * Remove WP Admin Bar on the app window.
		 */
		if ( Conditions::is_vb_app_window() ) {
			add_filter( 'show_admin_bar', '__return_false' );

			// Enqueue Divi Cloud App bundle script file an its script data.
			\ET_Cloud_App::load_js( false, true );
		} else {
			// Enqueue Divi Cloud app bundle style on top window because cloud app is being rendered on top window
			// thus Cloud app's style should exist on top window.
			\ET_Cloud_App::enqueue_style();
		}
	}

	/**
	 * Remove actions from D4 which are deprecated in D5, and then add new actions for the D5.
	 *
	 * TODO: feat(d5, refactor) Utilize this method to replace deprecated D4 action/filter hooks.
	 *
	 * @link https://elegantthemes.slack.com/archives/C04S2RE9N1J/p1694531769374299?thread_ts=1694530913.949609&cid=C04S2RE9N1J
	 *
	 * @since ??
	 *
	 * @ignore
	 *
	 * @return void
	 */
	public function replace_deprecated_actions() {
		// D5 has deprecated the `et_save_post` action with `divi_visual_builder_rest_save_post` hook.
		remove_action( 'et_save_post', 'et_divi_save_post', 1 );
		add_action( 'divi_visual_builder_rest_save_post', 'et_divi_save_post', 1 );

		remove_action( 'et_save_post', 'et_builder_set_product_page_layout_meta' );
		add_action( 'divi_visual_builder_rest_save_post', 'et_builder_set_product_page_layout_meta' );

		remove_action( 'et_save_post', 'et_theme_builder_clear_wp_post_cache' );
		add_action( 'divi_visual_builder_rest_save_post', 'et_theme_builder_clear_wp_post_cache' );

		// D5 has deprecated the `et_update_post` action with `divi_visual_builder_rest_update_post` hook.
		remove_action( 'et_update_post', 'et_builder_wc_set_page_content_status' );
		add_action( 'divi_visual_builder_rest_update_post', 'et_builder_wc_set_page_content_status' );
	}
}

$dependency_tree = new DependencyTree();
$dependency_tree->add_dependency( new RESTRegistration() );
$dependency_tree->add_dependency( new HooksRegistration() );
$dependency_tree->add_dependency( new PageSettings() );
$dependency_tree->add_dependency( new PackageBuildManager() );
$dependency_tree->add_dependency( new SettingsData() );
$dependency_tree->add_dependency( new TopWindow() );

$visual_builder = new VisualBuilder( $dependency_tree );
$visual_builder->initialize();
