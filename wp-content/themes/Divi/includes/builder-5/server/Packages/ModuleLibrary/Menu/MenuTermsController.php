<?php
/**
 * Menu: MenuTermsController.
 *
 * @package Builder\Framework\Route
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;

/**
 * MenuTerms REST Controller class.
 *
 * @since ??
 */
class MenuTermsController extends RESTController {

	/**
	 * Return list of created menus that will consumed by the setting component of Menu module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		return self::response_success(
			[
				'terms' => wp_get_nav_menus(),
			]
		);
	}

	/**
	 * Index action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @return array
	 */
	public static function index_args(): array {
		return [];
	}

	/**
	 * Index action permission.
	 *
	 * Endpoint permission callback as used in `register_rest_route()`.
	 *
	 * @return bool
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
