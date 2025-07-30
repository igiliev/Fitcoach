<?php
/**
 * ShortcodeModule: ShortcodeModuleBatchController.
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
 * Shortcode Module REST Controller class for batch requests.
 *
 * @since ??
 */
class ShortcodeModuleBatchController extends RESTController {

	/**
	 * Retrieve rendered HTML content for a bath of the Shortcode nodules using the provided arguments and return it as a REST response.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type array $data {
	 *         The shortcode modules.
	 *
	 *         @type string $content       The content to render.
	 *         @type int    $postId        The post ID to use for the shortcode.
	 *         @type string $shortcodeName The shortcode name to use for the shortcode.
	 *         @type array  $shortcodeList The list of shortcodes to use for the shortcode.
	 *     }
	 * }
	 *
	 * @return WP_REST_Response The REST response object containing the rendered HTML content.
	 *
	 * @example:
	 * ```php
	 *     $request = new WP_REST_Request( 'GET', '/your-endpoint' );
	 *     $request->set_param( 'data', [
	 *         [
	 *           'content'       => '[et_pb_registered_module][/et_pb_registered_module]',
	 *           'postId'        => '1',
	 *           'shortcodeName' => 'et_pb_registered_module',
	 *           'shortcodeList' => [ 'et_pb_registered_module' ],
	 *         ],
	 *         [
	 *           'content'       => '[et_pb_registered_module][/et_pb_registered_module]',
	 *           'postId'        => '2',
	 *           'shortcodeName' => 'et_pb_registered_module',
	 *           'shortcodeList' => [ 'et_pb_registered_module' ],
	 *         ],
	 *     ];
	 *
	 *    $response = ShortcodeModuleBatchController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$batch_data = $request->get_param( 'data' );
		$result     = [];

		if ( ! empty( $batch_data ) ) {
			// Collect all shortcode list for `et_builder_should_load_all_module_data` filter
			// before we can render each shortcode.
			$shortcode_list = [];
			foreach ( $batch_data as $item_data ) {
				if ( ! empty( $item_data['shortcodeList'] ) ) {
					$shortcode_list = array_merge( $shortcode_list, $item_data['shortcodeList'] );
				}
			}

			// Render each shortcode.
			foreach ( $batch_data as $item_data ) {
				// Skip if current data request is empty.
				if ( empty( $item_data ) ) {
					continue;
				}

				$item_args = [
					'content'        => $item_data['content'] ?? '',
					'post_id'        => $item_data['postId'] ?? '',
					'shortcode_name' => $item_data['shortcodeName'] ?? '',
					'shortcode_list' => $shortcode_list,
				];

				$item_response = [];

				try {
					$item_response['html'] = Module::get_rendered_content( $item_args );
				} catch ( \Error $e ) {
					$item_response['error'] = sprintf(
						__( 'Error when rendering Shortcode module with shortcode name %1$s and shortcode content %2$s', 'et_builder' ),
						$item_args['shortcode_name'],
						$item_args['content']
					);

					// Catch the fatal error, so the rendering process iteration won't break.
					\ET_Core_Logger::error( "{$e->getMessage()} \n{$e->getTraceAsString()}" );
				}

				$result[] = $item_response;
			}
		}

		return self::response_success( $result );
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
			'data' => [
				'type'              => 'array',
				'required'          => true,
				'validate_callback' => function( $batch_data, $request, $key ) {
					// By default, the validation status is `false`.
					$is_valid = false;

					// Batch data should not be empty and in array format.
					if ( ! empty( $batch_data ) && is_array( $batch_data ) ) {
						$is_valid = true;

						foreach ( $batch_data as $item_data ) {
							// Skip if current data request is empty. No need to break because we
							// need to check and process other data request, just in case they are not
							// empty. Empty data request won't be processed later.
							if ( empty( $item_data ) ) {
								continue;
							}

							// Validate each parameters in each data request. All parameter types are
							// string except `postId` as numeric and `shortcodeList` as array.
							foreach ( $item_data as $param_index => $param_value ) {
								if ( 'postId' === $param_index ) {
									$is_valid = is_numeric( $param_value );
								} elseif ( 'shortcodeList' === $param_index ) {
									$is_valid = is_array( $param_value );
								} else {
									$is_valid = is_string( $param_value );
								}

								// Break the parameters check once parameter found as invalid property.
								if ( ! $is_valid ) {
									break;
								}
							}

							// Break the data check once data request found as invalid data.
							if ( ! $is_valid ) {
								break;
							}
						}
					}

					return $is_valid;
				},
				'sanitize_callback' => function( $batch_data ) {
					// By default, the sanitized values is an empty array.
					$sanitized_values = [];

					// Batch data should not be empty and in array format.
					if ( ! empty( $batch_data ) && is_array( $batch_data ) ) {
						foreach ( $batch_data as $item_data ) {
							$content        = wp_kses_post( $item_data['content'] ?? '' );
							$post_id        = sanitize_text_field( $item_data['postId'] ?? 0 );
							$shortcode_name = sanitize_text_field( $item_data['shortcodeName'] ?? '' );

							$unsanitized_shortcode_list = $item_data['shortcodeList'] ?? [];
							$shortcode_list             = [];
							if ( ! empty( $unsanitized_shortcode_list ) ) {
								foreach ( $unsanitized_shortcode_list as $shortcode ) {
									$shortcode_list[] = sanitize_text_field( $shortcode );
								}
							}

							// We don't change the property names because we need it as referrence
							// for `$item_data` and `data` request result.
							$sanitized_values[] = [
								'content'       => $content,
								'postId'        => (int) $post_id,
								'shortcodeName' => $shortcode_name,
								'shortcodeList' => $shortcode_list,
							];
						}
					}

					return $sanitized_values;
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
