<?php
/**
 * FullwidthMenu: FullwidthMenuHTMLController.
 *
 * @package Builder\Framework\Route
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FullwidthMenu;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\ModuleLibrary\FullwidthMenu\FullwidthMenuUtils;

/**
 * FullwidthMenuHTML REST Controller class.
 *
 * @since ??
 */
class FullwidthMenuHTMLController extends RESTController {

	/**
	 * Return Fullwidth Menu HTML output for Fullwidth Menu module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$menu_dropdown_direction = $request->get_param( 'menuDropdownDirection' );
		$menu_id                 = $request->get_param( 'menuId' );

		if ( in_array( $menu_id, [ '', 'none' ], true ) ) {
			$menu_id = 'primary-menu';
		}

		return self::response_success(
			[
				'html' => FullwidthMenuUtils::render_fullwidth_menu(
					[
						'menuId'                => $menu_id,
						'menuDropdownDirection' => $menu_dropdown_direction,
					]
				),
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
		return [
			'menuId'                => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) || in_array( $param, [ '', 'none' ], true );
				},
			],
			'menuDropdownDirection' => [
				'type'              => 'string',
				'default'           => 'downwards',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param ) {
					return in_array( $param, [ 'downwards', 'upwards' ], true );
				},
			],
		];
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
