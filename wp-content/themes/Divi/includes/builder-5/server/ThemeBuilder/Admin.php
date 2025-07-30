<?php
/**
 * ThemeBuilder: Class for ThemeBuilder Admin.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\VisualBuilder\Assets\AssetsUtility;
use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;

/**
 * Theme Builder Admin class.
 *
 * @since ??
 */
class Admin implements DependencyInterface {
	/**
	 * Load the class.
	 */
	public function load(): void {
		if ( et_builder_is_tb_admin_screen() ) {
			add_action( 'et_theme_builder_enqueue_scripts', [ $this, 'load_top_window_visual_builder_dependencies' ] );
		}
	}

	/**
	 * Enqueue scripts and styles on Theme Bulder's admin page.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load_top_window_visual_builder_dependencies(): void {
		// Load Divi Cloud class to enable import and export functionality.
		if ( defined( 'ET_BUILDER_PLUGIN_ACTIVE' ) ) {
			require_once ET_BUILDER_PLUGIN_DIR . '/cloud/cloud-app.php';
		} else {
			require_once get_template_directory() . '/cloud/cloud-app.php';
		}

		\ET_Cloud_App::load_js( false, true );

		// Injected script that is required early (loading external .js will be too late).
		AssetsUtility::inject_preboot_script();

		AssetsUtility::enqueue_visual_builder_dependencies();

		// Injected style that is required early (loading external .css will be too late).
		AssetsUtility::inject_preboot_style();

		wp_register_script(
			'react-tiny-mce',
			ET_BUILDER_5_URI . '/visual-builder/assets/tinymce/tinymce.min.js',
			[],
			ET_BUILDER_VERSION,
			false
		);

		// Enqueue Google Maps API if needed.
		if ( et_pb_enqueue_google_maps_script() ) {
			wp_enqueue_script(
				'google-maps-api',
				esc_url(
					add_query_arg(
						array(
							'key' => et_pb_get_google_api_key(),
						),
						is_ssl() ? 'https://maps.googleapis.com/maps/api/js' : 'http://maps.googleapis.com/maps/api/js'
					)
				),
				array(),
				'3',
				true
			);
		}

		// Enqueue visual builder's core dependencies, which are built by WebPack as externals e.g. react, wp-data, wp-blocks.
		AssetsUtility::enqueue_visual_builder_dependencies();

		// Register package builds.
		PackageBuildManager::register_divi_package_builds();

		// Enqueue visual builder's packages' styles and scripts.
		PackageBuildManager::enqueue_scripts();
		PackageBuildManager::enqueue_styles();

		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue cor font family, which is used for Visual Builder UI.
		et_core_load_main_fonts();

		/**
		 * Validate enqueued scripts dependencies and trigger error if non-existing script is found.
		 *
		 * By default, WordPress lacks a validation mechanism for script dependencies. This leads to a silent failure
		 * and the script is not enqueued if any of its dependencies are missing. Given our extensive use of scripts
		 * a missing dependency can lead us down to the rabbit hole.
		 */
		AssetsUtility::validate_enqueue_script_dependencies();
	}
}
