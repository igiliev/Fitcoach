<?php
/**
 * ShortcodeModule: ShortcodeModuleController.
 *
 * @package Divi
 * @since ??
 *
 * TODO feat(D5, Shortcode Module): Rename `ShortcodeModuleBatchController` class into
 * `BatchController` later once we can move Shortcode module REST API route registration
 * to ShortcodeModule package.
 * @see https://github.com/elegantthemes/Divi/issues/32183
 */

namespace ET\Builder\Packages\ShortcodeModule\Module;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Shortcode Module REST Controller class for single request.
 *
 * @since ??
 */
class ShortcodeModuleController extends RESTController {

	/**
	 * Retrieve the Shortcode rendered HTML content using the provided arguments and return it as a REST response.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $content       The content to render.
	 *     @type int    $postId        The post ID to use for the shortcode.
	 *     @type string $shortcodeName The shortcode name to use for the shortcode.
	 *     @type array  $shortcodeList The list of shortcodes to use for the shortcode.
	 * }
	 *
	 * @return WP_REST_Response The REST response object containing the rendered HTML content.
	 *
	 * @example:
	 * ```php
	 *     $request = new WP_REST_Request( 'GET', '/your-endpoint' );
	 *     $request->set_param( 'content', 'Lorem ipsum dolor sit amet.' );
	 *     $request->set_param( 'postId', 123 );
	 *     $request->set_param( 'shortcodeName', 'my_shortcode' );
	 *     $request->set_param( 'shortcodeList', [ 'shortcode1', 'shortcode2' ] );
	 *     $response = ShortcodeModuleController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		// Excute regex to remove display_conditions attribute from content.
		// https://regex101.com/r/DxMJb9/1.
		$pattern = '/display_conditions="[^"]*"/';
		$args = [
			'content'        => preg_replace( $pattern, '', $request->get_param( 'content' ) ),
			'post_id'        => $request->get_param( 'postId' ),
			'shortcode_name' => $request->get_param( 'shortcodeName' ),
			'shortcode_list' => $request->get_param( 'shortcodeList' ),
		];

		$response = [
			'html' => Module::get_rendered_content( $args ),
		];

		return self::response_success( $response );
	}

	/**
	 * Get the arguments for the index action.
	 *
	 * This function returns the arguments for the index action, and can be used in `register_rest_route()`.
	 *
	 * @return array Returns an array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'content'       => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
				'sanitize_callback' => 'wp_kses_post',
			],
			'postId'        => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return (int) sanitize_text_field( $value );
				},
			],
			'shortcodeName' => [
				'type'              => 'string',
				'default'           => '',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
				'sanitize_callback' => 'sanitize_text_field',
			],
			'shortcodeList' => [
				'type'              => 'string',
				'default'           => '',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
				'sanitize_callback' => function( $value, $request, $param ) {
					return explode( ',', sanitize_text_field( $value ) );
				},
			],
		];
	}

	/**
	 * Get index action permission.
	 *
	 * This function checks the permission for the index action, and can be used in `register_rest_route()`.
	 * It determines whether the current user has the necessary privileges to access the endpoint.
	 *
	 * @return bool Returns `true` if the current user has permission, `false` otherwise.
	 */
	public static function index_permission(): bool {
		return true;
	}

}
