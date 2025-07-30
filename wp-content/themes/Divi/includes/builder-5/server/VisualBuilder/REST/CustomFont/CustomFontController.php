<?php
/**
 * REST: CustomFontController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\CustomFont;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\VisualBuilder\Fonts\FontsUtility;
use ET\Builder\VisualBuilder\Saving\SavingUtility;
use WP_REST_Request;
use WP_Error;

/**
 * CustomFontController class.
 *
 * This class extends the `RESTController` class and includes functionality for storing and destroying custom fonts.
 *
 * @since ??
 */
class CustomFontController extends RESTController {

		/**
		 * Store the font files and settings.
		 *
		 * This function is responsible for storing the font files and settings.
		 * It retrieves the supported font formats, font name, and font settings from the request object.
		 * It processes the font settings and checks for uploaded font files.
		 * It then calls `FontsUtility::font_add()` to save the fonts.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request The REST request object.
		 *
		 * @return array|WP_Error Returns an array or `WP_Error` on failure.
		 *
		 * @example:
		 * ```php
		 *   // Example usage within a class that uses the trait.
		 *   $request = new WP_REST_Request();
		 *   $request->set_param( 'et_pb_font_name', 'Arial' );
		 *   $request->set_param( 'et_pb_font_settings', '{"size":"14px","weight":"400"}' );
		 *   $request->set_file_params( 'et_pb_font_file_woff', 'example.woff' );
		 *   $response = CustomFontController::store( $request );
		 * ```
		 */
	public static function store( WP_REST_Request $request ) {
		$supported_font_files    = et_pb_get_supported_font_formats();
		$font_name               = $request->get_param( 'et_pb_font_name' );
		$font_settings           = $request->get_param( 'et_pb_font_settings' );
		$font_settings_processed = SavingUtility::is_json( $font_settings ) ? json_decode( $font_settings, true ) : [];
		$file_params             = $request->get_file_params();
		$fonts_array             = [];

		foreach ( $supported_font_files as $format ) {
			if ( isset( $file_params[ 'et_pb_font_file_' . $format ] ) ) {
				$fonts_array[ $format ] = $file_params[ 'et_pb_font_file_' . $format ];
			}
		}

		return FontsUtility::font_add(
			$fonts_array,
			$font_name,
			$font_settings_processed,
			[
				'action' => 'wp_handle_upload_rest',
			]
		);
	}

	/**
	 * Get the arguments for store action.
	 *
	 * This method returns an associative array containing the arguments required by store action.
	 * The returned array includes the `et_pb_font_name` and `et_pb_font_settings` arguments.
	 *
	 * @since ??
	 *
	 * @return array An associative array containing the arguments.
	 */
	public static function store_args(): array {
		return [
			'et_pb_font_name'     => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'et_pb_font_settings' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Store permission based on the context of the request.
	 *
	 * This function checks the context of the request and determines the appropriate capability
	 * required for the user to perform the action. It then checks if the current user has the
	 * required capability.
	 * If not, it returns an error `WP_Error` object.
	 *
	 * @since ??
	 *
	 * @return bool|\WP_Error Returns `true` if the user has the required capability, otherwise returns a `WP_Error` object.
	 *
	 * @example:
	 * ```php
	 *   // Example usage within a class that uses the trait.
	 *   $request = new \WP_REST_Request();
	 *   $request->set_param( 'context', 'et_pb_roles' );
	 *   $result = CustomFontController::store_permission( $request );
	 *
	 *   if ( $result === true ) {
	 *       // Continue with the action
	 *   } else {
	 *       echo $result->get_error_message();
	 *   }
	 * ```
	 *
	 * @example:
	 * ```php
	 *   // Example usage within a class that uses the trait.
	 *   $request = new \WP_REST_Request();
	 *   $request->set_param( 'context', 'et_builder' );
	 *   $result = CustomFontController::store_permission( $request );
	 *
	 *   if ( $result === true ) {
	 *       // Continue with the action
	 *   } else {
	 *       echo $result->get_error_message();
	 *   }
	 * ```
	 */
	public static function store_permission() {
		if ( ! current_user_can( 'upload_files' ) ) {
			return self::response_error_permission();
		}

		return true;
	}


	/**
	 * Destroy a font by removing it.
	 *
	 * This function takes a request object as a parameter and retrieves the font name from it.
	 * It then calls `FontsUtility::font_remove()` to remove the font.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The request object containing the font name.
	 *
	 * @return array|WP_Error Returns an array of the updated fonts on success and a `WP_Error` object on failure.
	 *
	 * @example:
	 * ```php
	 * $request = new WP_REST_Request();
	 * $request->set_param('et_pb_font_name', 'Open Sans');
	 * $result = CustomFontController::destroy( $request );
	 * ```
	 */
	public static function destroy( WP_REST_Request $request ) {
		$font_name = $request->get_param( 'et_pb_font_name' );

		return FontsUtility::font_remove( $font_name );
	}

	/**
	 * Retrieves the arguments for destroy action used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array An associative array containing the arguments for destroy action.
	 */
	public static function destroy_args() {
		return [
			'et_pb_font_name' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get destroy action permission.
	 *
	 * Check if the current user has permission to destroy a font.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the current user has permission, or `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * // Check if the current user has permission
	 * if (CustomFontController::destroy_permission()) {
	 *     // Code to execute if permission is granted
	 * } else {
	 *     // Code to execute if permission is denied
	 * }
	 * ```
	 */
	public static function destroy_permission() {
		if ( ! current_user_can( 'upload_files' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

}
