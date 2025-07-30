<?php
/**
 * Sidebar: SidebarController.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Sidebar;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\Theme\Theme;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Sidebar REST Controller class.
 *
 * @since ??
 */
class SidebarController extends RESTController {

	/**
	 * Retrieve the HTML content of a sidebar based on the specified area.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the HTML content.
	 *
	 * @example:
	 * ```php
	 *     $request = new WP_REST_Request();
	 *     $request->set_param( 'area', 'sidebar-1' );
	 *     $response = ClassName::index( $request );
	 *     $html_content = $response->get_data()['html'];
	 *
	 *     echo $html_content;
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$defaults = [
			'area' => '',
		];

		$args = [
			'area' => $request->get_param( 'area' ),
		];

		$args = wp_parse_args( $args, $defaults );

		// Get any available widget areas so it isn't empty.
		if ( '' === $args['area'] ) {
			$args['area'] = Theme::get_default_area();
		}

		// Outputs sidebar.

		ob_start();

		if ( is_active_sidebar( $args['area'] ) ) {
			dynamic_sidebar( $args['area'] );
		}

		$widgets = ob_get_clean();

		$response = [
			'html' => normalize_whitespace( $widgets ),
		];

		return self::response_success( $response );
	}

	/**
	 * Get the index action arguments.
	 *
	 * This method returns an array of arguments that can be used in the `register_rest_route()` function
	 * to define the necessary parameters for the index action.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'area' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'esc_attr',
				'validate_callback' => function( $param ) {
					return is_string( $param );
				},
			],
		];
	}

	/**
	 * Index action permission.
	 *
	 * Checks if the current user has permission to run index actions.
	 * This function is used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool Whether the current user has permission.
	 *              Note: this function currently always returns `true`.
	 */
	public static function index_permission(): bool {
		return true;
	}

}
