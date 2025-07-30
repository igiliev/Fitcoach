<?php
/**
 * Global Data: GlobalDataController Class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ET_Core_PageResource;

/**
 * Global Data REST Controller class.
 *
 * @since ??
 */
class GlobalDataController extends RESTController {

	/**
	 * Updates the global colors palette for Divi.
	 *
	 * This function takes a WP_REST_Request object as a parameter and updates the global colors palette for the
	 * Divi based on the `global_colors` parameter of the request.
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type array[] $global_colors Global colors data.
	 * }
	 *
	 * @return WP_REST_Response Returns a REST response object containing the updated Divi color palette.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/global-data/global-colors' );
	 *
	 * $request->set_param(
	 *    'global_colors',
	 *    [
	 *        'gcid-98eb727a-9088-4709-8ec8-2fee0213c5c3' => [
	 *            'color'       => '#ca5500',
	 *            'lastUpdated' => '2020-01-01T00:00:00.000Z',
	 *            'status'      => 'active',
	 *            'usedInPosts' => [],
	 *        ],
	 *        'gcid-98eb727ac3' => [
	 *            'color'       => '#ca5500',
	 *            'lastUpdated' => '2020-01-01T00:00:00.000Z',
	 *            'status'      => 'active',
	 *            'usedInPosts' => [],
	 *        ],
	 *    ]
	 * );
	 *
	 * $response = GlobalDataController::update_global_colors( $request );
	 *
	 * echo $response->get_data();
	 * ```
	 */
	public static function update_global_colors( WP_REST_Request $request ): WP_REST_Response {
		$global_colors = $request->get_param( 'global_colors' );

		// Note: $global_colors has already been sanitized via `update_global_colors_args` function.
		GlobalData::set_global_colors( $global_colors, true );

		return self::response_success( GlobalData::get_global_colors() );
	}

	/**
	 * Retrieves the arguments for updating global colors.
	 *
	 * @return array Associative array containing the arguments for updating global colors.
	 *               Each argument is represented by a key-value pair.
	 *               The available keys are:
	 *               - 'global_colors': (array) The global colors data to be updated.
	 *                   - 'type': (string) The type of the global colors data. Should be 'array'.
	 *                   - 'required': (bool) Determines if the global colors data is required. Should be true.
	 *                   - 'sanitize_callback': (callable) The callback function to sanitize the global colors data.
	 *                                         Should be [GlobalData::class, 'sanitize_global_colors_data'].
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *  $args = GlobalDataController::update_global_colors_args();
	 *  // Returns an associative array of arguments for the update action endpoint.
	 *  ```
	 */
	public static function update_global_colors_args(): array {
		return [
			'global_colors' => [
				'type'              => 'array',
				'required'          => true,
				'sanitize_callback' => [ GlobalData::class, 'sanitize_global_colors_data' ],
			],
		];
	}

	/**
	 * Checks if the current user has the permission to update global colors.
	 *
	 * @return true|WP_Error Returns true if the current user has the permission to update global colors.
	 *                      Returns a WP_Error object if the user does not have the necessary permission.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $update_global_colors_permission = GlobalDataController::update_global_colors_permission();
	 * // Returns true or WP_Error object.
	 * ```
	 */
	public static function update_global_colors_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Updates the global fonts in customizer.
	 *
	 * This function takes a WP_REST_Request object as a parameter and updates the global fonts in Customizer.
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $heading_font Global heading font name.
	 *     @type string $body_font Global body font name.
	 * }
	 *
	 * @return WP_REST_Response Returns a REST response object containing the updated Customizer fonts.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/global-data/global-fonts' );
	 *
	 * $request->set_param(
	 *    'heading_font',
	 *    'heading font name',
	 * );
	 * $request->set_param(
	 *    'body_font',
	 *    'body font name',
	 * );
	 *
	 * $response = GlobalDataController::update_global_fonts( $request );
	 *
	 * echo $response->get_data();
	 * ```
	 */
	public static function update_global_fonts( WP_REST_Request $request ): WP_REST_Response {
		$heading_font = $request->get_param( 'heading_font' );
		$body_font    = $request->get_param( 'body_font' );

		et_update_option( 'heading_font', $heading_font );
		et_update_option( 'body_font', $body_font );

		// We need to clear the entire website cache when updating a global font.
		ET_Core_PageResource::remove_static_resources( 'all', 'all', true );

		$customizer_fonts = [
			'heading' => [
				'label'    => esc_html__( 'Headings', 'et_builder' ),
				'fontId'   => '--et_global_heading_font',
				'fontName' => $heading_font,
			],
			'body'    => [
				'label'    => esc_html__( 'Body', 'et_builder' ),
				'fontId'   => '--et_global_body_font',
				'fontName' => $body_font,
			],
		];

		return self::response_success( $customizer_fonts );
	}

	/**
	 * Retrieves the arguments for updating global fonts.
	 *
	 * @return array Associative array containing the arguments for updating global fonts.
	 *               Each argument is represented by a key-value pair.
	 *               The available keys are:
	 *               - 'heading_font': (string) Heading font name to be saved.
	 *                   - 'required': (bool) Determines if the global font data is required. Should be true.
	 *                   - 'sanitize_callback': (callable) The callback function to sanitize the global font data.
	 *                                         Should be 'sanitize_text_field'.
	 *               - 'body_font': (string) Body font name to be saved.
	 *                   - 'required': (bool) Determines if the global font data is required. Should be true.
	 *                   - 'sanitize_callback': (callable) The callback function to sanitize the global font data.
	 *                                         Should be 'sanitize_text_field'.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *  $args = GlobalDataController::update_global_fonts_args();
	 *  // Returns an associative array of arguments for the update action endpoint.
	 *  ```
	 */
	public static function update_global_fonts_args(): array {
		return [
			'heading_font' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],

			'body_font'    => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Checks if the current user has the permission to update global fonts.
	 *
	 * @return true|WP_Error Returns true if the current user has the permission to update global fonts.
	 *                      Returns a WP_Error object if the user does not have the necessary permission.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $update_global_fonts_permission = GlobalDataController::update_global_fonts_permission();
	 * // Returns true or WP_Error object.
	 * ```
	 */
	public static function update_global_fonts_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Retrieves the arguments for updating global variables.
	 *
	 * @return array Associative array containing the arguments for updating global variables.
	 *               Each argument is represented by a key-value pair.
	 *               The available keys are:
	 *               - 'global_variables': (array) The global variables data to be updated.
	 *                   - 'type': (string) The type of the global variables data. Should be 'array'.
	 *                   - 'required': (bool) Determines if the global variables data is required. Should be true.
	 *                   - 'sanitize_callback': (callable) The callback function to sanitize the global variables data.
	 *                                         Should be [GlobalData::class, 'sanitize_global_variables_data'].
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *  $args = GlobalDataController::update_global_variables_args();
	 *  // Returns an associative array of arguments for the update action endpoint.
	 *  ```
	 */
	public static function update_global_variables_args() {
		return [
			'global_variables' => [
				'type'              => 'array',
				'required'          => true,
				'sanitize_callback' => [ GlobalData::class, 'sanitize_global_variables_data' ],
			],
		];
	}

	/**
	 * Updates the global variables for Divi.
	 *
	 * This function takes a WP_REST_Request object as a parameter and updates the global variables for Divi
	 * based on the `global_variables` parameter of the request.
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type array[] $global_variables Global variables data.
	 * }
	 *
	 * @return WP_REST_Response Returns a REST response object containing the updated Divi global variables.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/global-data/global-variables' );
	 *
	 * $request->set_param(
	 *    'global_variables',
	 *    [
	 *        'numbers' => [
	 *            'gvid-98eb727a-9088-4709-8ec8-2fee0213c5c3' => [
	 *                'label'  => 'Rounder Corners',
	 *                'value'  => '12px',
	 *                'order'  => 1,
	 *                'status' => 'active',
	 *            ],
	 *        ],
	 *        'strings' => [
	 *            'gvid-98eb727ac3' => [
	 *                'label'  => 'Font Size',
	 *                'value'  => '16px',
	 *                'order'  => 2,
	 *                'status' => 'active',
	 *            ],
	 *        ],
	 *    ]
	 * );
	 *
	 * $response = GlobalDataController::update_global_variables( $request );
	 *
	 * echo $response->get_data();
	 * ```
	 */
	public static function update_global_variables( WP_REST_Request $request ): WP_REST_Response {
		$global_variables = $request->get_param( 'global_variables' );

		GlobalData::set_global_variables( $global_variables );

		return self::response_success( GlobalData::get_global_variables() );
	}

	/**
	 * Checks if the current user has the permission to update global variables.
	 *
	 * @return true|WP_Error Returns true if the current user has the permission to update global variables.
	 *                      Returns a WP_Error object if the user does not have the necessary permission.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $update_global_variables_permission = GlobalDataController::update_global_variables_permission();
	 * // Returns true or WP_Error object.
	 * ```
	 */
	public static function update_global_variables_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

}
