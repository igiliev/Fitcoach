<?php
/**
 * Assets: Assets Registration class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


use ET\Builder\Framework\Utility\Conditions;

/**
 * AssetsUtility class.
 *
 * This class provides utility methods for handling assets such as scripts, styles, and preferences data for packages,
 * with functionality related to asset enqueueing, data retrieval, and injection.
 *
 * @since ??
 */
class AssetsUtility {

	/**
	 * Keep track of validated scripts.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_validated = [];

	/**
	 * Validates the dependencies of enqueued scripts.
	 *
	 * This function iterates through all scripts enqueued via wp_enqueue_script and checks if their dependencies
	 * are registered.
	 *
	 * By default, WordPress lacks a validation mechanism for script dependencies. This leads to a silent failure
	 * and the script is not enqueued if any of its dependencies are missing. Given our extensive use of scripts
	 * a missing dependency can lead us down to the rabbit hole.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function validate_enqueue_script_dependencies(): void {
		if ( ! defined( 'ET_DEBUG' ) || ! ET_DEBUG ) {
			return;
		}

		global $wp_scripts;

		foreach ( $wp_scripts->queue as $handle ) {
			self::_validate_dependencies_recursively( $handle, $wp_scripts );
		}
	}


	/**
	 * Recursively validates the dependencies of a given script handle.
	 *
	 * This private method is used to check the dependencies of each enqueued script, and recursively checks the
	 * dependencies of those dependencies as well. If a dependency is not registered, it triggers a user warning.
	 *
	 * @since ??
	 *
	 * @param string      $handle     The handle of the script to validate.
	 * @param \WP_Scripts $wp_scripts Global variable that contains the list of registered scripts.
	 *
	 * @return void
	 */
	private static function _validate_dependencies_recursively( $handle, $wp_scripts ): void {
		if ( ! isset( self::$_validated[ $handle ] ) ) {
			self::$_validated[ $handle ] = $handle;
		}

		// Already validated, skip.
		if ( isset( self::$_validated[ $handle ] ) ) {
			return;
		}

		if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
			// Script is not registered.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- This is to be triggered only when WP_DEBUG is on.
			trigger_error( esc_html( "Script '{$handle}' is not registered." ), E_USER_WARNING );

			return;
		}

		$deps = $wp_scripts->registered[ $handle ]->deps;
		foreach ( $deps as $dep ) {
			if ( ! isset( $wp_scripts->registered[ $dep ] ) ) {
				// Dependency is not registered.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- This is to be triggered only when WP_DEBUG is on.
				trigger_error( esc_html( "Dependency '{$dep}' of script '{$handle}' is not registered." ), E_USER_WARNING );
			} else {
				// Check dependencies of this dependency.
				self::_validate_dependencies_recursively( $dep, $wp_scripts );
			}
		}
	}

	/**
	 * Enqueue visual builder's core dependencies, which are built by WebPack as externals e.g. react, wp-data, wp-blocks.
	 * Package version enqueued here has to match with the version on visual builder's package.json.
	 * See: `/visual-builder/yarn.config.cjs`
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *   enqueue_visual_builder_dependencies();
	 * ```
	 */
	public static function enqueue_visual_builder_dependencies(): void {
		self::enqueue_dev_or_prod_script(
			'divi-vendor-react',
			'/visual-builder-dependencies/react',
			[],
			'18.2.0',
			true
		);

		// Display component of app window on top window's react dev tools.
		wp_add_inline_script(
			'divi-vendor-react',
			'if (window.parent !== window) { window.__REACT_DEVTOOLS_GLOBAL_HOOK__ = window.parent.__REACT_DEVTOOLS_GLOBAL_HOOK__; }',
			'after'
		);

		self::enqueue_dev_or_prod_script(
			'divi-vendor-react-dom',
			'/visual-builder-dependencies/react-dom',
			[],
			'18.2.0',
			true
		);

		// Pass the "DiviDevFlags" global variable.
		wp_localize_script(
			'divi-vendor-react',
			'DiviDevFlags',
			et_get_experiment_flag()
		);

		// WordPress package dependencies.
		// Script dependencies should be listed on `/visual-builder/node_modules/@wordpress/PACKAGE_NAME/package.json`.
		if ( Conditions::is_vb_app_window() ) {
			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-element',
				'/visual-builder-dependencies/wordpress/element',
				[
					'divi-vendor-react',
					'divi-vendor-react-dom',
				],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-compose',
				'/visual-builder-dependencies/wordpress/compose',
				[
					'lodash',
					'divi-vendor-wp-element',
				],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-data',
				'/visual-builder-dependencies/wordpress/data',
				[
					'lodash',
					'divi-vendor-wp-compose',
					'divi-vendor-wp-element',
					'divi-vendor-wp-private-apis',
				],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-private-apis',
				'/visual-builder-dependencies/wordpress/private-apis',
				[],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-i18n',
				'/visual-builder-dependencies/wordpress/i18n',
				[
					'divi-vendor-wp-hooks',
				],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-hooks',
				'/visual-builder-dependencies/wordpress/hooks',
				[],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-block-serialization-default-parser',
				'/visual-builder-dependencies/wordpress/block-serialization-default-parser',
				[],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-shortcode',
				'/visual-builder-dependencies/wordpress/shortcode',
				[],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-blocks',
				'/visual-builder-dependencies/wordpress/blocks',
				[

					'lodash',
					'divi-vendor-wp-autop',
					'divi-vendor-wp-block-serialization-default-parser',
					'divi-vendor-wp-data',
					'divi-vendor-wp-element',
					'divi-vendor-wp-hooks',
					'divi-vendor-wp-html-entities',
					'divi-vendor-wp-i18n',
					'divi-vendor-wp-shortcode',
					'divi-vendor-wp-private-apis',
				],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-api-fetch',
				'/visual-builder-dependencies/wordpress/api-fetch',
				[
					'divi-vendor-wp-i18n',
				],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-autop',
				'/visual-builder-dependencies/wordpress/autop',
				[],
				'1.0.0',
				true
			);

			self::enqueue_dev_or_prod_script(
				'divi-vendor-wp-html-entities',
				'/visual-builder-dependencies/wordpress/html-entities',
				[],
				'1.0.0',
				true
			);
		}
	}

	/**
	 * Conditionally enqueues a JavaScript file if it exists in the filesystem.
	 *
	 * Note: We don't ship dev dependencies, this method is used so when `ET_DEBUG` is set to `true` on customer's website, enqueuing won't fail.
	 *
	 * @param string   $handle        Unique identifier for the script. This handle is used to register the script.
	 * @param string   $relative_path Relative path to the JavaScript file from the '/includes/builder-5' directory in the theme. It should not contain .js suffix.
	 * @param string[] $dependencies  Optional. An array of registered script handles that this script depends on. Default is an empty array.
	 * @param string   $version       Optional. The script version number for cache busting. Default is '1.0.0'.
	 * @param bool     $in_footer     Optional. Whether to enqueue the script in the footer. Default is true.
	 *
	 * @return void
	 */
	public static function enqueue_dev_or_prod_script( $handle, $relative_path, $dependencies = [], $version = '1.0.0', $in_footer = true ) {
		// If `ET_DEBUG` constant is set to `true`, load non-minified version of the scripts.
		$suffix = defined( 'ET_DEBUG' ) && ET_DEBUG ? '' : '.min';

		$prod_url = get_template_directory_uri() . '/includes/builder-5' . $relative_path . '.min.js';
		$dev_url  = get_template_directory_uri() . '/includes/builder-5' . $relative_path . '.js';

		$dev_path = get_template_directory() . '/includes/builder-5' . $relative_path . '.js';

		$dev_version_exist = file_exists( $dev_path );

		$valid_url = ( '' === $suffix && $dev_version_exist ) ? $dev_url : $prod_url;

		wp_enqueue_script(
			$handle,
			$valid_url,
			$dependencies,
			$version,
			$in_footer
		);
	}

	/**
	 * Retrieves the settings data for the Visual Builder.
	 *
	 * The settings data includes various information required for the visual builder to function properly,
	 * such as post ID, post content, post type, post status, layout type, current URL, fonts, Google API settings,
	 * Divi Taxonomies, GMT offset, sidebar values, raw post content, TinyMCE plugins, and more.
	 *
	 * This function runs the value through `divi_visual_builder_settings_data` filter.
	 *
	 * NOTE: The returned value is equivalent to data attached over window.ETBuilderBackend in D4 which
	 * is equivalent of the returned array values of these three functions merged:
	 * - et_fb_get_static_backend_helpers( $post_type )
	 * - et_fb_get_dynamic_backend_helpers()
	 * - et_fb_get_builder_shortcode_object( $post_type, $post_id, $layout_type )
	 *
	 * In D5, the returned value is organized to be more consistent.
	 *
	 * @since ??
	 *
	 * @return array The settings data for the Visual Builder.
	 */
	public static function get_settings_data(): array {
		/**
		 * Filters the settings data that will be attached to `divi-scripts`.
		 *
		 * @since ??
		 *
		 * @param array $settings Settings data that will be attached to `divi-scripts`.
		 */
		return apply_filters( 'divi_visual_builder_settings_data', [] );
	}

	/**
	 * Inject the preboot script for the Divi theme.
	 *
	 * This function injects a preboot script adapted from preboot.js to make the window variable available for the Divi theme.
	 * The preboot.js file cannot be enqueued directly because it contains an override mechanism that is used for "moving assets
	 * from top to app window" approach.
	 *
	 * Ideally, this is used in `wp_head`.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *   AssetsUtility::inject_preboot_script();
	 * ```
	 */
	public static function inject_preboot_script(): void {
		// D5 actually not using any preboot script but 'divi-custom-script' uses `window.ET_Builder`
		// object so the following is adapted from preboot.js and loaded to make the window variable
		// available. NOTE: D5 can't simply enqueue preboot.js because it contains override mechanism
		// which is used for "moving assets from top to app window" approach that D4 VB is using.
		// Simply loading it will break initial state of top window in D5.
		echo "
			<script id='et-vb-builder-preboot'>
				window.ET_Builder = {
					API:    {},
					Frames: {
						top: window.top,
					},
					Misc: {},
				}
			</script>
		";
	}

	/**
	 * Inject preboot style: Style that needs to be printed so early enqueueing as external .css
	 * would be too late for it. This is presumable used at `wp_head`.
	 *
	 * @since ??
	 */
	/**
	 * Injects preboot style to hide Divi's heading and footer on visual builder load.
	 *
	 * These styles are used to hide Divi's heading and footer on visual builder load so preloader
	 * elements will appear without distraction. This is needed at both top window only since header
	 * and footer are expected to appear on app window. The paradox is to make this work this needs
	 * at both top and app window on very limited time because as long as the app hasn't been rendered,
	 * the header and footer are better hidden. `.et-vb-app-ancestor` is added by Visual Builder app
	 * so the following in app window translates into "hide header and footer until app is rendered
	 * which is indicated by existence of `.et-vb-app-ancestor` classname".
	 *
	 * Ideally, this is used in `wp_head`.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 *   // Injects the preboot style
	 *   AssetsUtility::inject_preboot_style();
	 * ```
	 */
	public static function inject_preboot_style(): void {
		// These styles are used to hide Divi's heading and footer on visual builder load so preloader
		// elements will appear without distraction. This is needed at both top window only since header
		// and footer is expected to appear on app window. The paradox is to make this work this needs
		// at both top and app window on very limited time because as long as the app hasn't been rendered,
		// the header and footer are better hidden. `.et-vb-app-ancestor` is added by Visual Builder app
		// so the following in app window translates into "hide header and footer until app is rendered
		// which is indicated by existence of `.et-vb-app-ancestor` classname".
		echo "
    <style id='et-vb-hide-elements-for-preloading'>
      html:not(.et-vb-app-ancestor) #main-header,
      html:not(.et-vb-app-ancestor) #main-footer { display: none; }
    </style>
	";
	}

	/**
	 * Dequeue queued scripts and styles that is not needed on top window which are registered early on `wp_enqueue_scripts`.
	 *
	 * @since ??
	 */
	public static function dequeue_top_window_early_scripts(): void {
		if ( Conditions::is_vb_top_window() ) {
			global $wp_scripts, $wp_styles;

			$known_unwanted_scripts = [
				'admin-bar',
				'autosave',
				'comment-reply',
				'easypiechart',
				'es6-promise',
				'et_pb_media_librar',
				'et-jquery-visible-viewport',
				'fitvids',
				'google-maps-api',
				'heartbeat',
				'jquery-mobile',
				'magnific-popup',
				'salvattore',
				'divi-custom-script',
				'divi-theme-scripts-library-menu',
				'divi-theme-scripts-library-search-menu',
				'divi-theme-scripts-library-woocommerce',
				'divi-script-library-global-functions',
				'divi-script-library-ext-waypoint',
				et_get_combined_script_handle(),
				'wc-add-to-cart',
				'wc-order-attribution',
				'woocommerce',
				'sourcebuster-js',
				'js-cookie',
			];

			foreach ( $wp_scripts->queue as $script_name ) {
				$is_known_unwanted_script = in_array( $script_name, $known_unwanted_scripts, true );

				if ( $is_known_unwanted_script ) {
					wp_dequeue_script( $script_name );
				}
			}

			$known_unwanted_styles = [
				'wp-block-library',
				'wp-block-library-theme',
				'global-styles',
				'et-divi-open-sans',
				'divi-style',
				'woocommerce-layout',
				'woocommerce-smallscreen',
				'woocommerce-general',
				'woocommerce-inline',
			];

			foreach ( $wp_styles->queue as $style_name ) {
				$is_known_unwanted_style = in_array( $style_name, $known_unwanted_styles, true );

				if ( $is_known_unwanted_style ) {
					wp_dequeue_style( $style_name );
				}
			}

			remove_action( 'wp_enqueue_scripts', 'et_builder_load_modules_styles', 11 );
		}
	}

	/**
	 * Dequeue queued scripts and styles that is not needed on top window which are registered late on `wp_footer`.
	 *
	 * @since ??
	 */
	public static function dequeue_top_window_late_scripts(): void {
		if ( Conditions::is_vb_top_window() ) {
			global $wp_scripts, $wp_styles;

			$known_unwanted_scripts = [
				'admin-bar',
				'autosave',
				'comment-reply',
				'easypiechart',
				'es6-promise',
				'et_pb_media_librar',
				'et-core-common',
				'et-jquery-visible-viewport',
				'fitvids',
				'google-maps-api',
				'heartbeat',
				'jquery-mobile',
				'magnific-popup',
				'salvattore',
				'wc-add-to-cart',
				'wc-cart-fragments',
				'wc-checkout',
				'wc-single-product',
				'woocommerce',
				'flexslider',
				'photoswipe-ui-default',
				'select2',
				'selectWoo',
				'zoom',
			];

			foreach ( $wp_scripts->queue as $script_name ) {
				$is_d4_dynamic_assets_script = 0 === strpos( $script_name, 'et-builder-modules-script-' );
				$is_known_unwanted_script    = in_array( $script_name, $known_unwanted_scripts, true );

				if ( $is_d4_dynamic_assets_script || $is_known_unwanted_script ) {
					wp_dequeue_script( $script_name );
				}
			}

			$known_unwanted_styles = [
				'wp-block-library',
				'wp-block-library-theme',
				'global-styles',
				'et-divi-open-sans',
				'divi-style',
				'core-block-supports',
				'core-block-supports-duotone',
				'imgareaselect',
				'photoswipe-default-skin',
				'select2',
				'wc-blocks-style',
				'woocommerce-layout',
				'woocommerce-smallscreen',
				'woocommerce-general',
			];

			foreach ( $wp_styles->queue as $style_name ) {
				$is_known_unwanted_style = in_array( $style_name, $known_unwanted_styles, true );

				if ( $is_known_unwanted_style ) {
					wp_dequeue_style( $style_name );
				}
			}

			remove_action( 'wp_footer', 'et_builder_maybe_ensure_heartbeat_script', 19 );
		}
	}
}
