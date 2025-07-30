<?php
/**
 * REST: BreakpointController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\Breakpoint;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\Controllers\RESTController;
use WP_REST_Request;
use ET_Core_PageResource;

/**
 * BreakpointController class.
 *
 * @since ??
 */
class BreakpointController extends RESTController {

	/**
	 * Update breakpoints settings.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 */
	public static function update( WP_REST_Request $request ) {
		// Get properties parameters that will be updated.
		$items = $request->get_param( 'items' );

		// Update breakpoints.
		$update_status = Breakpoint::update(
			[
				'items' => $items,
			]
		);

		// Return failed response.
		if ( ! $update_status ) {
			return self::response_error(
				'',
				esc_html__( 'Failed to update breakpoints.', 'et_builder' )
			);
		}

		// Reset cache when breakpoints are updated.
		ET_Core_PageResource::remove_static_resources( 'all', 'all', true );

		// Return response.
		return self::response_success(
			[
				'status' => $update_status,
			]
		);
	}

	/**
	 * Update action arguments.
	 *
	 * @since ??
	 */
	public static function update_args(): array {
		return [

			// No `validate_callback` and `sanitize_callback` provided because it'll be performed on `Breakpoint::update()`.
			'items' => [
				'default'  => [],
				'required' => true,
			],
		];
	}

	/**
	 * Update action permission.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 */
	public static function update_permission( WP_REST_Request $request ) {
		// TODO feat(D5, Customizable Breakpoint) Maybe update permissions and which user that can change breakpoints.
		return current_user_can( 'manage_options' );
	}
}
