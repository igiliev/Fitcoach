<?php
/**
 * Conditions class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use Divi\D5_Readiness\Server\Checks\PluginHooksCheck;

/**
 * Conditions class.
 *
 * This class contains helper methods to check for certain conditions.
 *
 * @since ??
 */
class Conditions {

	/**
	 * Determine if Visual Builder (VB) is enabled on a post/page.
	 *
	 * This function is proxy function of existing D4 function `et_core_is_fb_enabled`.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_vb_enabled(): bool {
		return et_core_is_fb_enabled();
	}

	/**
	 * Check if the current screen is the Theme Builder administration screen.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_tb_admin_screen() {
		return et_builder_is_tb_admin_screen();
	}

	/**
	 * Check if the current screen is a WP post edit screen.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_wp_post_edit_screen() {
		global $pagenow;

		return in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ), true );
	}

	/**
	 * Check if current screen is custom post type page.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_custom_post_type() {
		static $is_custom_post_type = null;

		if ( null !== $is_custom_post_type ) {
			return $is_custom_post_type;
		}
		// Use queried object as reference of current page's $post object instead of global $post because in loop,
		// global $post refers to current item in the loop thus any TB elements in the loop will be considered
		// as custom post type (eg any header will have `et_header_type` for its $post->post_type value).
		$queried_object = get_queried_object();

		$post_type = isset( $queried_object->post_type ) ? $queried_object->post_type : false;

		$is_custom_post_type = et_builder_is_post_type_custom( $post_type );

		return $is_custom_post_type;
	}

	/**
	 * Determine if debug mode is enabled.
	 *
	 * This function checks the constant `ET_DEBUG`.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_debug_mode() {
		return defined( 'ET_DEBUG' ) && ! ! ET_DEBUG;
	}

	/**
	 * Check whether D5 is enabled
	 *
	 * This function is proxy function of existing D4 function `et_builder_d5_enabled`.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_d5_enabled() {
		return et_builder_d5_enabled();
	}

	// TODO feat(D5, Shortcode) Move this trait into single Shortcode class under Framework https://github.com/elegantthemes/Divi/issues/31411.
	/**
	 * Check if content contains D4 shortcode.
	 *
	 * If this function is called with `content=null`, and the current query is for an existing single post of any post
	 * type (`is_singular=true`), then the function will attempt to get the raw post content via `get_post_field` using
	 * `get_the_ID` to get the post ID.
	 *
	 * @link https://developer.wordpress.org/reference/functions/is_singular/
	 * @link https://developer.wordpress.org/reference/functions/get_post_field/
	 * @link https://developer.wordpress.org/reference/functions/get_the_id/
	 *
	 * @since ??
	 *
	 * @param string $shortcode_suffix Optional. Shortcode tag suffix to check. Default empty string.
	 * @param string $content          Optional. Content to check. Default `null`.
	 *
	 * @return bool
	 */
	public static function has_shortcode( $shortcode_suffix = '', $content = null ) {
		if ( null === $content && is_singular() ) {
			$content = get_post_field( 'post_content', get_the_ID(), 'raw' );
		}

		if ( ! is_string( $content ) ) {
			return false;
		}

		/**
		 * Regex pattern to match paired and self-closing shortcodes with prefix `et_pb_`.
		 *
		 * Test regex https://regex101.com/r/XfqdEC/1
		 */
		$regex_pattern = '/\[et_pb_' . $shortcode_suffix . '[^\]]*\/?\]/';

		return ! ! ( preg_match( $regex_pattern, $content ) );
	}

	// TODO feat(D5, Theme Builder):  This function is proxy function of existing D4
	// function `et_builder_tb_enabled`. Replace `et_builder_tb_enabled` once the Theme
	// Builder is implemented in D5.
	// @link https://github.com/elegantthemes/Divi/issues/25149.
	/**
	 * Check whether the Visual Builder is loaded through the Theme Builder.
	 *
	 * This function is proxy function of existing D4 function `et_builder_tb_enabled
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_tb_enabled() {
		return et_builder_tb_enabled();
	}

	/**
	 * Check if the current page is app window of visual builder page.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_vb_app_window() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
		return self::is_vb_enabled() && isset( $_GET['app_window'] ) && '1' === $_GET['app_window'];
	}

	/**
	 * Check if the current page is top window of visual builder page.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_vb_top_window() {
		return self::is_vb_enabled() && ! self::is_vb_app_window();
	}

	/**
	 * Check if this is a WP REST API request.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_rest_api_request() {
		// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput -- This is just check, therefore nonce verification not required.
		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

		/*
		 * The REST_REQUEST constant is defined in `parse_request` action only, which is why we're looking into
		 * REQUEST_URI as the fallback checks.
		 */
		return defined( 'REST_REQUEST' ) && REST_REQUEST ||
			0 === strpos( $request_uri, '/' . rest_get_url_prefix() ) ||
			false !== strpos( $request_uri, '?rest_route=/' );
	}

	/**
	 * Check if were in Unit Test Environment.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_test_env() {
		return defined( 'WP_TESTS_DOMAIN' );
	}

	/**
	 * Check if this is a WP AJAX request.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_ajax_request() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Check if this is a WP Admin request.
	 */
	public static function is_admin_request() {
		return is_admin();
	}

	/**
	 * Check whether to register all D5 modules.
	 *
	 * This function is used to determine whether to register all D5 modules on this page load.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function should_register_all_d5_modules() {
		$should_register = false;

		// if this is the VB, then we should register all modules.
		if (
			self::is_vb_app_window()
			|| self::is_rest_api_request()
			|| self::is_test_env()
		) {
			$should_register = true;
		}

		// If this is an ajax request.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$load_for_ajax_actions = [
				'et_core_portability_import',
				'et_d5_readiness_convert_d4_to_d5',
				'et_theme_builder_api_import_theme_builder',
				'et_theme_builder_api_import_theme_builder_step',
				'et_pb_submit_subscribe_form',
			];

			// phpcs:disable WordPress.Security.NonceVerification -- It just need to figure out if this correct ajax action.
			if ( ! empty( $_POST['action'] ) && in_array( $_POST['action'], $load_for_ajax_actions, true ) ) {
				$should_register = true;
			}
		}

		/**
		 * Filter whether to register all D5 modules.
		 *
		 * This filter is used to determine whether to register all D5 modules on this page load.
		 *
		 * @since ??
		 *
		 * @param bool $should_register Default is `false`.
		 */
		return apply_filters( 'divi_module_library_should_register_all_d5_modules', $should_register );
	}

	/**
	 * Check if WooCommerce plugin is enabled.
	 */
	public static function is_woocommerce_enabled(): bool {
		return class_exists( 'WooCommerce', false );
	}

	/**
	 * Check if there is active Divi 4 DiviExtension that is not compatible with Divi 5.
	 *
	 * This function is used to determine whether we need to initialize DiviExtensions class.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function has_divi_4_only_extension(): bool {
		// Create an instance of Plugin_Hooks_Check.
		$plugin_hooks_check = new PluginHooksCheck();

		// Run the check.
		$plugin_hooks_check->run_check();

		// Return true if D4 extension is detected.
		return $plugin_hooks_check->detected();
	}
}
