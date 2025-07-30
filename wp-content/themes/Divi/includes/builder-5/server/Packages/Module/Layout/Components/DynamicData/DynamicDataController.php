<?php
/**
 * Dynamic Data: DynamicDataController.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicData;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Dynamic Data REST Controller class.
 *
 * @since ??
 */
class DynamicDataController extends RESTController {

	/**
	 * Process and retrieve the resolved values for dynamic data.
	 *
	 * Iterates through the provided data array and calls the `DynamicData::get_processed_dynamic_data`
	 * to retrieve the resolved values for each element in the array.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the resolved values.
	 *
	 * @example
	 * ```php
	 *  $request = new WP_REST_Request();
	 *  $request->set_param( 'data', $data );
	 *  $response = DynamicDataController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$result = [];
		$data   = $request->get_param( 'data' );

		foreach ( $data as $datum ) {
			$result[] = [
				'resolvedValue' => DynamicData::get_processed_dynamic_data(
					$datum['value'],
					$datum['postId']
				),
			];
		}

		return self::response_success( $result );
	}

	/**
	 * Get the arguments for the index action.
	 *
	 * This function returns an array that defines the arguments for the index action, which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'data' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'postId' => [
							'type'     => 'integer',
							'required' => true,
						],
						'value'  => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
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
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
