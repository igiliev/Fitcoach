<?php
/**
 * Utility functions for checking conditions.
 *
 * To be included in this file a function must:
 *
 *   * Return a bool value
 *   * Have a name that asks a yes or no question (where the first word after
 *     the et_ prefix is a word like: is, can, has, should, was, had, must, or will)
 *
 * @package Divi
 * @subpackage Builder
 * @since 4.0.7
 */

// phpcs:disable Squiz.PHP.CommentedOutCode -- We may add `et_builder_()` in future.

/*
Function Template

if ( ! function_exists( '' ) ):
function et_builder_() {

}
endif;

*/
// phpcs:enable

// Note: Functions in this file are sorted alphabetically.

if ( ! function_exists( 'et_builder_is_frontend' ) ) :
	/**
	 * Determine whether current request is frontend.
	 * This excludes the visual builder.
	 *
	 * @return bool
	 */
	function et_builder_is_frontend() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Only used to disable some FE optmizations.
		$is_builder              = isset( $_GET['et_fb'] ) || isset( $_GET['et_bfb'] );
		$is_block_layout_preview = isset( $_GET['et_block_layout_preview'] );
		// phpcs:enable

		return $is_builder || is_admin() || wp_doing_ajax() || wp_doing_cron() || $is_block_layout_preview ? false : true;
	}
endif;

if ( ! function_exists( 'et_builder_is_frontend_or_builder' ) ) :
	/**
	 * Determine whether current request is frontend.
	 * This includes the visual builder.
	 *
	 * @since 4.10.0
	 *
	 * @return bool
	 */
	function et_builder_is_frontend_or_builder() {
		static $et_builder_is_frontend_or_builder = null;

		if ( null === $et_builder_is_frontend_or_builder ) {
			if (
				! is_admin()
				&& ! wp_doing_ajax()
				&& ! wp_doing_cron()
			) {
				$et_builder_is_frontend_or_builder = true;
			}
		}

		return $et_builder_is_frontend_or_builder;
	}
endif;

if ( ! function_exists( 'et_builder_is_loading_data' ) ) :
	/**
	 * Determine whether builder is loading full data or not.
	 *
	 * @param string $type Is it a bb or vb.
	 *
	 * @return bool
	 */
	function et_builder_is_loading_data( $type = 'vb' ) {
		// phpcs:disable WordPress.Security.NonceVerification -- This function does not change any stats, hence CSRF ok.
		if ( 'bb' === $type ) {
			return 'et_pb_get_backbone_templates' === et_()->array_get( $_POST, 'action' );
		}

		$data_actions = array(
			'et_fb_retrieve_builder_data',
			'et_fb_update_builder_assets',
			'et_pb_process_computed_property',
		);

		return isset( $_POST['action'] ) && in_array( $_POST['action'], $data_actions, true );
		// phpcs:enable
	}
endif;

if ( ! function_exists( 'et_builder_should_load_all_data' ) ) :
	/**
	 * Determine whether to load full builder data.
	 *
	 * @return bool
	 */
	function et_builder_should_load_all_data() {
		$needs_cached_definitions = et_core_is_fb_enabled() && ! et_fb_dynamic_asset_exists( 'definitions' );

		return $needs_cached_definitions || ( ET_Builder_Element::is_saving_cache() || et_builder_is_loading_data() );
	}
endif;

if ( ! function_exists( 'et_builder_modules_is_saving_cache') ):
	/**
	 * Determine whether builder is saving cache.
	 *
	 * @return bool
	 */
	function et_builder_modules_is_saving_cache() {
		return apply_filters( 'et_builder_modules_is_saving_cache', false );
	}
endif;

if ( ! function_exists( 'et_builder_should_load_all_module_data' ) ) :
	/**
	 * Determine whether to load all module data.
	 *
	 * @return bool
	 */
	function et_builder_should_load_all_module_data() {
		static $should_load_all_module_data = null;

		if ( null !== $should_load_all_module_data ) {
			// Use the cached value.
			return $should_load_all_module_data;
		}

		// If we are in the admin.
		if ( is_admin() ) {
			// Only load all module data when the builder framework is loaded.
			if ( ! et_builder_should_load_framework() ) {
				return false;
			}
		}

		$is_vb_enabled = isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'];
		$has_page_id   = ! empty( $_GET['page_id'] );
		if ( $is_vb_enabled && $has_page_id ) {
			// Don't load when this is a request for the VB from GB.
			return false;
		}

		$is_app_window  = isset( $_GET['app_window'] ) && '1' === $_GET['app_window'];
		if ( $is_vb_enabled && $is_app_window ) {
			// Return false if WooCommerce is not active.
			if ( ! class_exists( 'WooCommerce' ) ) {
				return false;
			}

			// Return false if the post isn't available, or contains no Woo modules "[et_pb_wc" or "[et_pb_shop".
			global $post;
			if ( ! $post || ( isset( $post->post_content ) && ( false !== strpos( $post->post_content, '[et_pb_wc' ) && false !== strpos( $post->post_content, '[et_pb_shop' ) ) ) ) {
				return false;
			}
			// Only load these when this is a request for the VB and this is the app window.
			return true;
		}

		if ( et_core_is_fb_enabled() ) {
			// Don't load on Visual Builder requests. No shortcode nor serialized block are ever parsed in Visual Builder
			// request due to two reasons:
			// 1. To optimize document size, D5 VB loads special blank template page that contains iframe to load app window.
			// 2. The saved content are parsed on the VB JS app directly.
			return false;
		}

		if ( ! et_builder_is_frontend() ) {
			// Always load everything when not a frontend request.
			return true;
		}

		$needs_cached_definitions = et_core_is_fb_enabled();


		$result = $needs_cached_definitions || ( et_builder_modules_is_saving_cache() || et_builder_is_loading_data() );

		/**
		 * Whether to load all module data,
		 * including all module classes, on a given page load.
		 *
		 * @since 4.10.0
		 *
		 * @param bool $result Whether to load all module data.
		 */
		return apply_filters( 'et_builder_should_load_all_module_data', $result );
	}
endif;


if ( ! function_exists( 'et_builder_dynamic_module_framework' ) ) :
	/**
	 * Determine whether module framework is on.
	 *
	 * @return string
	 */
	function et_builder_dynamic_module_framework() {
		global $shortname;

		if ( et_is_builder_plugin_active() ) {
			$options                     = get_option( 'et_pb_builder_options', array() );
			$et_dynamic_module_framework = isset( $options['performance_main_dynamic_module_framework'] ) ? $options['performance_main_dynamic_module_framework'] : 'on';
		} else {
			$et_dynamic_module_framework = et_get_option( $shortname . '_dynamic_module_framework', 'on' );
		}
		return $et_dynamic_module_framework;
	}
endif;

if ( ! function_exists( 'et_builder_is_mod_pagespeed_enabled' ) ) :
	/**
	 * Determine whether Mod PageSpeed is enabled.
	 *
	 * @return bool
	 */
	function et_builder_is_mod_pagespeed_enabled() {
		static $enabled;

		if ( isset( $enabled ) ) {
			// Use the cached value.
			return $enabled;
		}

		$key     = 'et_check_mod_pagespeed';
		$version = get_transient( $key );

		if ( false === $version ) {
			// Mod PageSpeed is an output filter, hence it can't be detected from within the request.
			// To figure out whether it is active or not:
			// 1. Use `wp_remote_get` to make another request.
			// 2. Retrieve PageSpeed version from response headers (if set).
			// 3. Save the value in a transient for 24h.
			// The `et_check_mod_pagespeed` url parameter is also added to the request so
			// we can exit early (content is irrelevant, only headers matter).
			$args = [
				$key => 'on',
			];

			// phpcs:disable WordPress.Security.NonceVerification -- Only checking arg is set.
			if ( isset( $_REQUEST['PageSpeed'] ) ) {
				// This isn't really needed but it's harmless and makes testing a lot easier.
				$args['PageSpeed'] = sanitize_text_field( $_REQUEST['PageSpeed'] );
			}
			// phpcs:enable

			$request = wp_remote_get( add_query_arg( $args, get_home_url() ) );
			// Apache header.
			$version = wp_remote_retrieve_header( $request, 'x-mod-pagespeed' );
			if ( empty( $version ) ) {
				// Nginx header.
				$version = wp_remote_retrieve_header( $request, 'x-page-speed' );
			}

			set_transient( $key, $version, DAY_IN_SECONDS );
		}

		// Cache the value.
		$enabled = ! empty( $version );
		return $enabled;
	}
endif;

if ( ! function_exists( 'is_et_theme_builder_template_preview' ) ) :
	/**
	 * Check whether current page is library template preview page.
	 *
	 * @return bool
	 */
	function is_et_theme_builder_template_preview() {
		global $wp_query;
		// phpcs:ignore WordPress.Security.NonceVerification -- This function does not change any state, and is therefore not susceptible to CSRF.
		return ( 'true' === $wp_query->get( 'et_pb_preview' ) && isset( $_GET['et_pb_preview_nonce'] ) && isset( $_GET['item_id'] ) );
	}
endif;

if ( ! function_exists( 'is_et_theme_builder_live_preview' ) ) :
	/**
	 * Check whether current page is the theme builder template preview page.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	function is_et_theme_builder_live_preview(): bool {
		// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- wp_verify_nonce() function does sanitation.
		return isset( $_GET['et_tb_preview'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'et_theme_builder_template_preview' );
	}
endif;


if ( ! function_exists( 'et_is_test_env') ) :
	/**
	 * Check whether we are in test environment.
	 */
	function et_is_test_env() {
		return defined( 'WP_TESTS_DOMAIN' );
	}
endif;

/**
 * Check to see if this is a front end request.
 *
 * @since 4.10.0
 *
 * @return bool
 */
function et_is_front_end_request() {
	static $et_is_front_end_request = null;

	if ( null === $et_is_front_end_request ) {
		if (
			// Disable for WordPress admin requests.
			! is_admin()
			&& ! wp_doing_ajax()
			&& ! wp_doing_cron()
		) {
			$et_is_front_end_request = true;
		}
	}

	return $et_is_front_end_request;
}

/**
 * Check if Dynamic CSS is enabled.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_assets() instead.
 * @since 4.10.0
 *
 * @return bool
 * @deprecated
 */
function et_use_dynamic_css() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_assets() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_assets();
}

/**
 * Check if the current request should generate Dynamic Assets.
 * We only generate dynamic assets on the front end and when cache dir is writable.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::should_generate_dynamic_assets()
 *        instead.
 * @since 4.10.0
 *
 * @return bool
 * @deprecated
 */
function et_should_generate_dynamic_assets() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::should_generate_dynamic_assets() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::should_generate_dynamic_assets();
}
