<?php
/**
 * REST: ModuleRenderController class.
 *
 * @package Builder\VisualBuilder\REST
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\ModuleRender;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\VisualBuilder\Saving\SavingUtility;
use ET\Builder\Framework\Controllers\RESTController;

/**
 * Module Renderer REST API Controller class.
 *
 * @since ??
 */
class ModuleRenderController extends RESTController {

	/**
	 * Sanitize the content by preparing for database storage.
	 *
	 * This function takes string and calls the
	 * `SavingUtility::prepare_content_for_db()` to sanitize it.
	 * The sanitized string is returned.
	 *
	 * @since ??
	 *
	 * @param string $content The string to be sanitized.
	 *
	 * @return string The sanitized content string
	 *
	 * @example:
	 * ```php
	 * $content = '<p>Content 1</p>';
	 *
	 * $sanitizedContent = ModuleRenderController::sanitize_content($content);
	 * // Returns the sanitized content string
	 * ```
	 */
	public static function sanitize_content( string $content ): string {
		return SavingUtility::prepare_content_for_db( $content );
	}

	/**
	 * Renders provided blocks into HTML.
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return \WP_REST_Response
	 * @since ??
	 */
	public static function module_render( $request ): \WP_REST_Response {
		$content = $request->get_param( 'content' );

		$rendered = do_blocks( $content );

		return self::response_success( array( 'renderedHTML' => $rendered ) );
	}

	/**
	 * Module render action arguments.
	 *
	 * Endpoint arguments as used in `register_rest_route()`.
	 *
	 * @return array
	 */
	public static function module_render_args(): array {
		return [
			'content' => [
				'required'          => true,
				'sanitize_callback' => [ __CLASS__, 'sanitize_content' ],
			],
		];
	}

	/**
	 * Render module action permission.
	 *
	 * Endpoint permission callback as used in `register_rest_route()`.
	 *
	 * @return bool
	 */
	public static function module_render_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

}
