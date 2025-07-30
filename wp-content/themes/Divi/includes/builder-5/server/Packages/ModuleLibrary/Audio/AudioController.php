<?php
/**
 * Module Library: Audio Module REST Controller class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Audio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Audio REST Controller class.
 *
 * @since ??
 */
class AudioController extends RESTController {

	/**
	 * Get the HTML representation of the Audio module.
	 *
	 * Retrieve the HTML representation of the Audio module based on the
	 * provided audio source passed as an argument.
	 *
	 * @since ??
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return string The rendered HTML of the Audio module.
	 *
	 * @example
	 * ```php
	 * $args = [ 'audio' => 'example.mp3' ];
	 * $html = AudioController::get_audio_html_by_src( $args );
	 * ```
	 */
	public static function get_audio_html_by_src( array $args = [] ): string {
		$audio = ! empty( $args['audio'] ) ? $args['audio'] : '';

		require_once ABSPATH . '/wp-includes/shortcodes.php';

		return do_shortcode( sprintf( '[audio src="%s" /]', $audio ) );
	}

	/**
	 * Retrieve the rendered HTML for the Audio module.
	 *
	 * This function retrieves the rendered HTML for the Audio module based on the audio source passed as an argument.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object containing the rendered HTML.
	 *
	 * @example: Example usage of the Audio module REST API endpoint.
	 * ```php
	 * $request = new WP_REST_Request( 'GET' );
	 * $request->set_param( 'audio', 'example.mp3' );
	 * $response = Audio_Module::index( $request );
	 * $data = $response->get_data();
	 *
	 * // Use $html to display or manipulate the rendered HTML for the Audio module.
	 * $html = $data['html'];
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'audio' => $request->get_param( 'audio' ),
		];

		$audio = self::get_audio_html_by_src( $args );

		$response = [
			'html' => $audio,
		];

		return self::response_success( $response );
	}

	/**
	 * Get the arguments for the index action.
	 *
	 * This function returns an array that defines the arguments for the index action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 *
	 * @example
	 * ```php
	 * $args = AudioController::index_args();
	 * ```
	 */
	public static function index_args(): array {
		return [
			'audio' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'esc_url_raw',
				'validate_callback' => 'wp_http_validate_url',
			],
		];
	}

	/**
	 * Provides the permission status for the index action.
	 *
	 * This function checks if the current user has the permission to use the Visual Builder.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 *
	 * @example
	 * ```php
	 * $permission = AudioController::index_permission();
	 * ```
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
