<?php
/**
 * Settings Data: REST Controller class.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\VisualBuilder\SettingsData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\VisualBuilder\SettingsData\SettingsData;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Setting Data REST Controller class.
 *
 * @since ??
 */
class SettingsDataController extends RESTController {

	/**
	 * Retrieve the settings data after app load for Visual Builder
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response returns the REST response object containing the rendered HTML
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {

		// Due to lazy load mechanism which was introduced to boost performance by only registering modules that are
		// actually used on the current page, shortcode module data is not registered by default on REST request.
		// Thus without the following tweaks, no shortcode modules data is returned when
		// `ET_Builder_Element::get_shortcode_module_definitions()` is called. For this method to returned shortcode
		// module data on REST request, three things need to be done:
		//
		// 1. `ET_Builder_Module_Shortcode_Manager::_should_register_shortcodes()` should return true
		// 2. `et_builder_should_load_all_module_data()` should return true as well.
		// Otherwise only WC shortcode module’s definition being returned.
		// 3. The most important part: `$action_hook` at `ET_Builder_Module_Shortcode_Manager::register_shortcode()` has
		// to be `wp_loaded`. This is tricky one because `is_admin()` doesn’t return true on REST request page and the
		// constant `REST_REQUEST` hasn’t been defined yet since It is too early for it
		//
		// This means nothing that can be done at `ET_Builder_Module_Shortcode_Manager::_should_register_shortcodes()`
		// side to make it returns `true` because it is too early to call whether it is `REST` request page or not.
		// To overcome this, force `et_builder_should_load_all_module_data` to return `true` below then execute
		// `ET_Builder_Module_Shortcode_Manager->register_all_shortcodes()` method to register all shortcode modules here.

		// Force load all module data. Without forching this, only WooCommerce modules will be registered.
		add_filter( 'et_builder_should_load_all_module_data', '__return_true' );

		// Create instance of shortcode manager class then register all shortcode modules.
		$manager = new \ET_Builder_Module_Shortcode_Manager();
		$manager->register_all_shortcodes();

		return self::response_success(
			SettingsData::get_settings_data( [ 'usage' => 'after_app_load' ] )
		);
	}

	/**
	 * Arguments for the index actions.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [];
	}

	/**
	 * Provides the permission status for the index action.
	 *
	 * @since ??
	 *
	 * @return bool returns `true` if the current user has the permission to use the rest endpoint, otherwise `false`
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}
}
