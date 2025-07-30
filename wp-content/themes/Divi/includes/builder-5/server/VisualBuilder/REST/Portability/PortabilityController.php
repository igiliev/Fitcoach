<?php
/**
 * REST: PortabilityController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\Portability;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\Portability\PortabilityPost;
use ET\Builder\VisualBuilder\Hooks\HooksRegistration;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API Portability Controller class.
 *
 * This class extends the RESTController and provides functionality for displaying and storing portability information.
 *
 * @since ??
 */
class PortabilityController extends RESTController {

	/**
	 * Show function
	 *
	 * This function is used to handle the display of a Divi Builder layout based on the provided context parameter.
	 * It exports the layout as a portability post, which can be used for various purposes like importing/exporting, cloning, etc.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The request object containing the necessary parameters.
	 *
	 * @return WP_REST_Response|WP_Error Returns a success `WP_REST_Response` object containing the exported portability post.
	 *                          If the context parameter is invalid, it returns an `WP_Error` object.
	 *
	 * @example:
	 * ```php
	 * // Example usage in a class where the trait is used
	 *   $request = new \WP_REST_Request( 'GET' );
	 *   // Set necessary parameters in the request as needed
	 *   $response = Portability::show( $request );
	 *
	 *   // Do something with the response
	 * ```
	 */
	public static function show( WP_REST_Request $request ) {
		$context          = $request->get_param( 'context' );
		$portability_type = $request->get_param( 'portability_type' );

		if ( ! $context || ! in_array( $context, [ 'et_builder', 'et_builder_layouts' ], true ) ) {
			return self::response_error( 'invalid_context', esc_html__( 'Invalid context parameter.', 'et_builder' ) );
		}

		$portability_registered = et_core_cache_get( $context, 'et_core_portability' );

		if ( ! $portability_registered ) {
			et_core_portability_register(
				$context,
				array(
					'name' => esc_html__( 'Divi Builder Layout', 'et_builder' ),
					'type' => $portability_type,
					'view' => true,
				)
			);
		}

		$portability_post = new PortabilityPost( $context );

		if ( $request->has_param( 'timestamp' ) ) {
			$portability_post->set_param( 'timestamp', $request->get_param( 'timestamp' ) );
		}

		if ( $request->has_param( 'page' ) ) {
			$portability_post->set_param( 'page', $request->get_param( 'page' ) );
		}

		if ( $request->has_param( 'selection' ) ) {
			$portability_post->set_param( 'selection', $request->get_param( 'selection' ) );
		}

		if ( $request->has_param( 'post' ) ) {
			$portability_post->set_param( 'post', $request->get_param( 'post' ) );
		}

		if ( $request->has_param( 'content' ) ) {
			$portability_post->set_param( 'content', $request->get_param( 'content' ) );
		}

		if ( $request->has_param( 'global_presets' ) ) {
			$portability_post->set_param( 'include_global_presets', $request->get_param( 'global_presets' ) );
		}

		if ( $request->has_param( 'global_colors' ) ) {
			$portability_post->set_param( 'include_global_colors', $request->get_param( 'global_colors' ) );
		}

		if ( $request->has_param( 'global_variables' ) ) {
			$portability_post->set_param( 'include_global_variables', $request->get_param( 'global_variables' ) );
		}

		if ( $request->has_param( 'return_content' ) ) {
			$portability_post->set_param( 'return_content', $request->get_param( 'return_content' ) );
		}

		$result = $portability_post->export();

		return self::response_success( $result );
	}

	/**
	 * Retrieves the arguments for showing information.
	 *
	 * This function returns an array of arguments that control what information should be displayed.
	 *
	 * @since ??
	 *
	 * @return array {
	 *     An array of arguments.
	 *
	 *     @type array $context {
	 *         Context related arguments.
	 *
	 *         @type bool   $required          Whether the context is required or not. Default true.
	 *         @type string $sanitize_callback The callback function to sanitize the context value. Default 'sanitize_text_field'.
	 *     }
	 *     @type array $post {
	 *         Post related arguments.
	 *
	 *         @type bool   $required          Whether the post is required or not. Default true.
	 *         @type string $sanitize_callback The callback function to sanitize the post value. Default 'sanitize_text_field'.
	 *     }
	 *     @type array $content {
	 *         Content related arguments.
	 *
	 *         @type bool $required Whether the content is required or not. Default true.
	 *     }
	 *     @type string $portability_type {
	 *         Portability type related arguments.
	 *
	 *         @type bool   $required          Whether the portability type is required or not. Default false.
	 *         @type string $default           The default value for the portability type. Default 'post'.
	 *         @type string $sanitize_callback The callback function to sanitize the portability type value. Default 'sanitize_text_field'.
	 *     }
	 * }
	 *
	 * @example:
	 * ```php
	 * // Example 1.
	 * $args = Portability::show_args();
	 *
	 * // Example 2.
	 * $args = Portability::show_args();
	 * ```
	 */
	public static function show_args(): array {
		return [
			'context'          => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'post'             => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'content'          => [
				'required' => true,
			],
			'portability_type' => [
				'required'          => false,
				'default'           => 'post',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Check if the current user has the permission to show content based on the given context.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type string $context The context in which the permission is being checked.
	 * }
	 *
	 * @return bool|WP_Error Whether the current user has the permission to show content or an `WP_Error` error object on failure.
	 *
	 * @example:
	 * ```php
	 * // Check permission for a specific context
	 * $request = new WP_REST_Request(array('context' => 'et_pb_roles'));
	 * $result = Portability::show_permission($request);
	 *
	 * if (is_wp_error($result)) {
	 *     // Handle permission error
	 * } else {
	 *     // Proceed with showing content
	 * }
	 * ```
	 */
	public static function show_permission( WP_REST_Request $request ) {
		$context = $request->get_param( 'context' );

		$capability       = '';
		$options_contexts = array(
			'et_pb_roles',
			'et_builder_layouts',
			'epanel',
			'et_divi_mods',
			'et_extra_mods',
		);
		$post_contexts    = array(
			'et_builder',
			'et_theme_builder',
		);

		if ( in_array( $context, $options_contexts, true ) ) {
			$capability = 'edit_theme_options';
		} elseif ( in_array( $context, $post_contexts, true ) ) {
			$capability = 'edit_posts';
		}

		if ( ! current_user_can( $capability ) ) {
			return self::response_error_permission();
		}

		return true;
	}

		/**
		 * Store the Divi Builder Layout content.
		 *
		 * This function is responsible for storing the Divi Builder layout content.
		 * It validates the context parameter, checks for required parameters, registers the portability if not already registered,
		 * imports the layout content, and updates the post if the `replace` parameter is set to true.
		 *
		 * This method imports a layout and stores it in the Divi Builder. It requires a valid REST request object that
		 * contains the necessary parameters. It returns a WP_Error object if the request is invalid or an error occurs
		 * during the import process. If the import is successful, it returns a WP_REST_Response object containing the
		 * result of the import.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request The REST request object.
		 *
		 * @return WP_Error|WP_REST_Response Returns a WP_Error object if the request is invalid or an error occurs during
		 *                                   the import process.
		 *                                   If the import is successful, it returns a WP_REST_Response object containing the
		 *                                   result of the import.
		 *
		 * @example:
		 * ```php
		 *   // Import the layout content for a specific post.
		 *   $request = new \WP_REST_Request( 'POST', '/v2/divi/layouts' );
		 *   $request->set_param( 'context', 'et_builder' );
		 *   $request->set_param( 'post', 123 );
		 *   $response = Portability::store( $request );
		 *
		 *   if ( is_wp_error( $response ) ) {
		 *       echo $response->get_error_message();
		 *   } else {
		 *       echo 'Layout content imported successfully!';
		 *   }
		 * ```
		 */
	public static function store( WP_REST_Request $request ) {
		$context = $request->get_param( 'context' );

		if ( 'et_builder' !== $context ) {
			return self::response_error( 'invalid_context', esc_html__( 'Invalid context parameter.', 'et_builder' ) );
		}

		$files                  = $request->get_file_params();
		$layout                 = (string) $request->get_param( 'layout' );
		$post_id                = (int) $request->get_param( 'post' );
		$replace                = 'on' === $request->get_param( 'replace' );
		$include_global_presets = 'on' === $request->get_param( 'include_global_presets' );

		// Get the allowed file extensions and MIME Types for JSON files.
		$check_filetype_and_ext_hook = [ HooksRegistration::class, 'check_filetype_and_ext_json' ];

		/**
		 * Filters the allowed file extensions and MIME Types for JSON files.
		 *
		 * This filter allows developers to add additional file extensions and MIME Types to the list of allowed file
		 * extensions and MIME Types for JSON files.
		 *
		 * @since ??
		 *
		 * @param array $check_filetype_and_ext_hook The array of allowed file extensions and MIME Types for JSON files.
		 */
		add_filter( 'wp_check_filetype_and_ext', $check_filetype_and_ext_hook, 999, 3 );

		// Get WP defined MIME Types after adding the filter to ensure JSON files are allowed.
		$mime_types = wp_get_mime_types();

		$portability_registered = et_core_cache_get( 'et_builder', 'et_core_portability' );

		if ( ! $portability_registered ) {
			et_core_portability_register(
				'et_builder',
				array(
					'name' => esc_html__( 'Divi Builder Layout', 'et_builder' ),
					'type' => 'post',
					'view' => true,
				)
			);
		}

		$portability_post = new PortabilityPost( 'et_builder' );

		if ( $request->has_param( 'timestamp' ) ) {
			$portability_post->set_param( 'timestamp', $request->get_param( 'timestamp' ) );
		}

		if ( $request->has_param( 'page' ) ) {
			$portability_post->set_param( 'page', $request->get_param( 'page' ) );
		}

		$result = $portability_post->import(
			$files,
			$layout,
			$post_id,
			$include_global_presets,
			[
				'action' => 'wp_handle_upload_rest',
				'mimes'  => $mime_types,
			]
		);

		if ( ! $result ) {
			return self::response_error( 'no_response', esc_html__( 'There was no response.', 'et_builder' ) );
		} elseif ( is_array( $result ) && isset( $result['message'] ) ) {
			return self::response_error( 'invalid_response', esc_html( $result['message'] ) );
		} else {
			if ( $replace && $post_id > 0 && current_user_can( 'edit_post', $post_id ) ) {
				wp_update_post(
					[
						'ID'           => $post_id,
						'post_content' => $result['postContent'],
					]
				);
			}

			return self::response_success( $result );
		}
	}

	/**
	 * Returns the arguments for the store action.
	 *
	 * This function returns an associative array containing the arguments
	 * required for a specific function. The returned array includes the `context`,
	 * `post`, `replace`, and `include_global_presets` arguments.
	 *
	 * @since ??
	 *
	 * @return array An associative array containing the arguments.
	 *
	 * @example:
	 * ```php
	 * $args = Portability::store_args();
	 * ```
	 */
	public static function store_args(): array {
		return [
			'context'                => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'post'                   => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'replace'                => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'include_global_presets' => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Store permission based on the context of the request.
	 *
	 * This function checks the context of the request and determines the appropriate capability
	 * required for the user to perform the action. It then checks if the current user has the
	 * required capability. If not, it returns an error `WP_Error` object.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return true|\WP_Error Returns true if the user has the required capability, otherwise returns a WP_Error object.
	 *
	 * @example:
	 * ```php
	 *      // Example usage within a class that uses the trait.
	 *      $request = new WP_REST_Request();
	 *      $request->set_param( 'context', 'et_pb_roles' );
	 *      $result = Portability::store_permission( $request );
	 *
	 *      if ( $result === true ) {
	 *          // Continue with the action
	 *      } else {
	 *          echo $result->get_error_message();
	 *      }
	 * ```
	 *
	 * @example
	 *      // Example usage within a class that uses the trait.
	 *      $request = new WP_REST_Request();
	 *      $request->set_param( 'context', 'et_builder' );
	 *      $result = Portability::store_permission( $request );
	 *
	 *      if ( $result === true ) {
	 *          // Continue with the action
	 *      } else {
	 *          echo $result->get_error_message();
	 *      }
	 */
	public static function store_permission( WP_REST_Request $request ) {
		$context = $request->get_param( 'context' );

		$capability       = '';
		$options_contexts = array(
			'et_pb_roles',
			'et_builder_layouts',
			'epanel',
			'et_divi_mods',
			'et_extra_mods',
		);
		$post_contexts    = array(
			'et_builder',
			'et_theme_builder',
		);

		if ( in_array( $context, $options_contexts, true ) ) {
			$capability = 'edit_theme_options';
		} elseif ( in_array( $context, $post_contexts, true ) ) {
			$capability = 'edit_posts';
		}

		if ( ! current_user_can( $capability ) ) {
			return self::response_error_permission();
		}

		return true;
	}


	/**
	 * Get the allowed file extensions and MIME Types for JSON files.
	 *
	 * This function retrieves an array of MIME Types to compare against server settings when a JSON file is uploaded.
	 *
	 * @since ??
	 *
	 * @return array The array of allowed file extensions and MIME Types for JSON files.
	 */
	public static function mime_types_json(): array {
		return array(
			'json' => array(
				'application/json',
				'application/vnd.api+json',
				'application/x-javascript',
				'text/javascript',
				'text/plain',
				'text/x-javascript',
				'text/x-json',
			),
		);
	}

}
