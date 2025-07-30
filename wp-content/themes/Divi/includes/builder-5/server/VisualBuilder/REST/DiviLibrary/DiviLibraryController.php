<?php
/**
 * REST: DiviLibraryController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST\DiviLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\Portability\PortabilityPost;
use ET\Builder\Library\LibraryUtility;
use ET\Builder\Packages\Conversion\Conversion as BuilderConversion;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\VisualBuilder\DiviLibrary\DiviLibraryUtility;
use ET\Builder\VisualBuilder\Saving\SavingUtility;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * DiviLibraryController class.
 *
 * The DiviLibraryController class extends the RESTController class and provides functionality to handle CRUD operations for the Divi Library.
 *
 * @since ??
 */
class DiviLibraryController extends RESTController {

		/**
		 * Export function for Divi Library.
		 *
		 * This function is used to handle the export of a Divi Builder layout based on the provided request parameter.
		 * It exports the layout as a portability post, which can be used for various purposes like importing/exporting, cloning, etc.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request The request object containing the necessary parameters.
		 * @param int             $id      The post ID.
		 * @param string          $content The post content.
		 *
		 * @return WP_REST_Response|WP_Error Returns a success `WP_REST_Response` object containing the exported portability post.
		 *                          If the context parameter is invalid, it returns an `WP_Error` object.
		 *
		 * @example:
		 * ```php
		 * // Example usage in a class where the trait is used
		 *   $request = new \WP_REST_Request( 'GET' );
		 *   // Set necessary parameters in the request as needed
		 *   $response = self::export( $request );
		 *
		 *   // Do something with the response
		 * ```
		 */
	public static function export( WP_REST_Request $request, $id, $content ) {
		$context = 'et_builder';

		$portability_registered = et_core_cache_get( $context, 'et_core_portability' );

		if ( ! $portability_registered ) {
			et_core_portability_register(
				$context,
				array(
					'name' => esc_html__( 'Divi Builder Layout', 'et_builder' ),
					'type' => 'post',
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

		$portability_post->set_param( 'post', $id );
		$portability_post->set_param( 'content', $content );
		$portability_post->set_param( 'return_content', true );

		return $portability_post->export();
	}

		/**
		 * Get a single library layout.
		 *
		 * This function is used to handle the display of a Divi Builder layout based on the provided request parameter.
		 * It retrieves the post or layout content and returns it as a response.
		 * If the content is too large, it also includes a custom HTTP header 'X-Content-Length' as a fallback for the Content-Length header.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request The request object containing the necessary parameters.
		 *
		 * @return WP_REST_Response|WP_Error Returns a success `WP_REST_Response` object containing the retrieved content.
		 *                                   If the post or layout is not found, it returns a `WP_Error` object with an error message.
		 *
		 * @example:
		 * ```php
		 * $request = new WP_REST_Request( 'GET' );
		 * // Set necessary parameters in the request as needed
		 * $response = DiviLibrary::show( $request );
		 *
		 * // Do something with the response
		 * ```
		 */
	public static function show( WP_REST_Request $request ) {
		$id   = $request->get_param( 'id' );
		$post = \get_post( $id );

		if ( ! $post ) {
			return self::response_error( 'no_entry_found', esc_html__( 'No entry found.', 'et_builder' ) );
		}

		$result = array(
			'content' => '',
		);

		switch ( $post->post_type ) {
			case \ET_BUILDER_LAYOUT_POST_TYPE:
				$library_type = $request->get_param( 'libraryType' );
				$built_for    = $request->get_param( 'builtFor' );
				$layouts      = \et_pb_retrieve_templates( $library_type, '', 'all', '0', $built_for, 'all', array() );

				foreach ( $layouts as $layout ) {
					if ( $post->ID === $layout['ID'] ) {
						$result = $layout;
						break;
					}
				}

				if ( isset( $result['shortcode'] ) ) {
					$result['content'] = $result['shortcode'];

					// Remove shortcode key.
					unset( $result['shortcode'] );
				}

				if ( empty( $result['content'] ) ) {
					$result['content'] = $post->post_content;
				}
				break;

			default:
				$result['content'] = $post->post_content;
				break;
		}

		if ( 'exported' === $request->get_param( 'contentType' ) ) {
			$result['content'] = self::export( $request, $id, $result['content'] );
		}

		if ( function_exists( 'mb_strlen' ) ) {
			$x_content_length = mb_strlen( wp_json_encode( $result, JSON_NUMERIC_CHECK ), '8bit' );
		} else {
			$x_content_length = strlen( wp_json_encode( $result, JSON_NUMERIC_CHECK ) );
		}

		return self::response_success(
			$result,
			array(
				// Custom HTTP header that will be used as fallback of the Content-Length header.
				// Mostly when the JSON data size is quite big, server will send response header Transfer-Encoding as chunked
				// and not send the Content-Length header.
				'X-Content-Length' => $x_content_length,
			)
		);
	}

	/**
	 * Get arguments for show action.
	 *
	 * Defines an array of arguments for the show action used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for show action.
	 */
	public static function show_args(): array {
		return [
			'id'          => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'libraryType' => [
				'default'           => 'layout',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'builtFor'    => [
				'default'           => 'page',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'contentType' => [
				'default'           => 'processed',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Check if user has permission for show action.
	 *
	 * Checks if the current user has the permission to edit posts, used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has permission, or `WP_Error` if the user does not have permission.
	 *
	 * @example:
	 * ```php
	 *     // Check if the user has permission to edit posts
	 *     $permission = DiviLibrary::show_permission();
	 *     if ( $permission instanceof WP_Error ) {
	 *         echo $permission->get_error_message();
	 *     } else {
	 *         echo "Permission granted!";
	 *     }
	 * ```
	 */
	public static function show_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Updates library item terms.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	/**
	 * Update library item terms.
	 *
	 * This function takes a `WP_REST_Request` object as a parameter and updates library item terms based on the `data` parameter of the request.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type array $data {
	 *         An array of term data.
	 *
	 *         @type string $filterType The type of filter ('tags' or 'categories').
	 *         @type string $updateType The type of update ('add', 'rename', or 'remove').
	 *         @type int    $id         The ID of the term.
	 *         @type string $newName    The new name of the term (only for 'rename' update type).
	 *         @type string $term_name  The name of the term (only for 'add' update type).
	 *     }
	 * }
	 *
	 * @return array Returns an array of data including the updated filters, the filter type, and the local library terms.
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/update-terms' );
	 * $request->set_param( 'data', [
	 *     [
	 *         'filterType' => 'tags',
	 *         'updateType' => 'add',
	 *         'id' => 1,
	 *         'term_name' => 'New Tag',
	 *     ],
	 *     [
	 *         'filterType' => 'categories',
	 *         'updateType' => 'rename',
	 *         'id' => 2,
	 *         'newName' => 'New Category Name',
	 *     ],
	 *     [
	 *         'filterType' => 'tags',
	 *         'updateType' => 'remove',
	 *         'id' => 3,
	 *     ],
	 * ] );
	 *
	 * $response = DiviLibrary::update( $request );
	 *
	 * print_r( $response );
	 * ```
	 *
	 * @output:
	 * ```php
	 *  Array(
	 *      'newFilters' => Array(
	 *          Array(
	 *              'name' => 'New Tag',
	 *              'id' => 1,
	 *              'location' => 'local',
	 *          ),
	 *      ),
	 *      'filterType' => 'tags',
	 *      'localLibraryTerms' => Array(
	 *          'layout_category' => ...,
	 *          'layout_tag' => ...,
	 *      ),
	 *  )
	 * ```
	 */
	public static function update( $request ) {
		$filter_type = '';
		$new_terms   = [];
		$data        = $request->get_param( 'data' );

		if ( empty( $data ) ) {
			return self::response_error( 'terms_data_empty', esc_html__( 'terms data cannot be empty.', 'et_builder' ) );
		}

		foreach ( $data as $single_item ) {
			if ( ! $filter_type ) {
				$filter_type = $single_item['filterType'];
			}

			$taxonomy = 'tags' === $single_item['filterType'] ? 'layout_tag' : 'layout_category';

			switch ( $single_item['updateType'] ) {
				case 'remove':
					$term_id = (int) $single_item['id'];
					wp_delete_term( $term_id, $taxonomy );
					break;
				case 'rename':
					$term_id  = (int) $single_item['id'];
					$new_name = (string) $single_item['newName'];

					if ( '' !== $new_name ) {
						$updated_term_data = wp_update_term( $term_id, $taxonomy, [ 'name' => $new_name ] );

						if ( ! is_wp_error( $updated_term_data ) ) {
							$new_terms[] = [
								'name'     => $new_name,
								'id'       => $updated_term_data['term_id'],
								'location' => 'local',
							];
						}
					}
					break;
				case 'add':
					$term_name     = (string) $single_item['id'];
					$new_term_data = wp_insert_term( $term_name, $taxonomy );

					if ( ! is_wp_error( $new_term_data ) ) {
						$new_terms[] = [
							'name'     => $term_name,
							'id'       => $new_term_data['term_id'],
							'location' => 'local',
						];
					}
					break;
			}
		}

		return self::response_success(
			[
				'newFilters'        => $new_terms,
				'filterType'        => $filter_type,
				'localLibraryTerms' => [
					'layout_category' => LibraryUtility::prepare_library_terms(),
					'layout_tag'      => LibraryUtility::prepare_library_terms( 'layout_tag' ),
				],
			]
		);
	}

	/**
	 * Retrieves the arguments array for update action.
	 *
	 * This function returns an array containing the arguments for update action, used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array The array containing the arguments for update action.
	 */
	public static function update_args(): array {
		return [
			'data' => [
				'required'          => true,
				'sanitize_callback' => function( $data ) {
					$sanitized = [];

					foreach ( $data as $item ) {
						if ( ! $item || ! is_array( $item ) ) {
							continue;
						}

						$sanitized_item = [];

						foreach ( $item as $key => $value ) {
							$sanitized_item[ $key ] = sanitize_text_field( $value );
						}

						$sanitized[] = $sanitized_item;
					}

					return $sanitized;
				},
			],
		];
	}

	/**
	 * Check if user has permission for update action.
	 *
	 * This function checks if the current user has the capability to edit posts, used in `register_rest_route()`.
	 * If the user does not have the necessary permissions, an error response is returned.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the required permissions, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * $result = DiviLibrary::update_permission();
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     echo $result->get_error_message();
	 * } else {
	 *     echo "Permission granted.";
	 * }
	 * ```
	 */
	public static function update_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

		/**
		 * Create a new item in the WordPress REST API.
		 *
		 * This function receives a `WP_REST_Request` object and creates a new item based on the provided parameters.
		 * It saves the layout to the database and returns the layout data, updated terms, and save verification result.
		 *
		 * @since ??
		 *
		 * @param WP_REST_Request $request {
		 *     The REST request object.
		 *
		 *     @type string $layout_name          The name of the new layout.
		 *     @type string $layout_type          The type of the new layout.
		 *     @type string $post_type            The post type of the new layout.
		 *     @type string $layout_selected_cats The selected categories of the new layout.
		 *     @type string $layout_selected_tags The selected tags of the new layout.
		 *     @type string $layout_new_cat       The new category of the new layout.
		 *     @type string $layout_new_tag       The new tag of the new layout.
		 *     @type string $columns_layout       The columns layout of the new layout.
		 *     @type string $layout_content       The content of the new layout.
		 *     @type string $module_type          The module type of the new layout.
		 *     @type string $module_width         The module width of the new layout.
		 *     @type string $layout_scope         The scope of the new layout.
		 * }
		 *
		 * @return array {
		 *     The layout data, updated terms, and save verification result.
		 *
		 *     @type array $layoutData       The meta data of the new layout, including post ID, name, type, and scope.
		 *     @type array $updatedTerms     An array containing updated terms for layout categories and tags.
		 *     @type bool  $saveVerification The result of the save verification.
		 * }
		 *
		 * @example:
		 * ```php
		 *     // Creating a new item with layout content and custom parameters
		 *     $request = new \WP_REST_Request('POST');
		 *     $request->set_param('post_type', 'layout');
		 *     $request->set_param('layout_content', 'Lorem ipsum dolor sit amet');
		 *     $request->set_param('layout_name', 'My Layout');
		 *     // ... provide other parameters as needed
		 *
		 *     $response = DiviLibrary::create_item($request);
		 * ```

		 * @example:
		 * ```php
		 *     // Creating a new item with default parameters
		 *     $request = new \WP_REST_Request('POST');
		 *
		 *     $response = DiviLibrary::create_item($request);
		 * ```

		 * @example:
		 * ```php
		 *     // Creating a new item in a specific post type and with specific layout content
		 *     $request = new \WP_REST_Request('POST');
		 *     $request->set_param('post_type', 'page');
		 *     $request->set_param('layout_content', 'Sed ut perspiciatis unde omnis iste natus error');
		 *
		 *     $response = DiviLibrary::create_item($request);
		 * ```

		 * @example:
		 * ```php
		 *     // Creating a new item with empty layout content
		 *     $request = new \WP_REST_Request('POST');
		 *     $request->set_param('layout_content', '');
		 *
		 *     $response = DiviLibrary::create_item($request);
		 * ```
		 */
	public static function create_item( WP_REST_Request $request ): array {
		$post_type = $request->get_param( 'post_type' );

		if ( et_theme_builder_is_layout_post_type( $post_type ) ) {
			// Treat TB layouts as normal posts when storing layouts from the library.
			$post_type = 'page';
		}

		$layout_content = $request->get_param( 'layout_content' );

		// Prepare args for saving layout to the database.
		$args = [
			'layout_name'          => $request->get_param( 'layout_name' ),
			'template_type'        => $request->get_param( 'layout_type' ),
			'layout_type'          => $request->get_param( 'layout_type' ),
			'built_for_post_type'  => $post_type,
			'layout_scope'         => $request->get_param( 'layout_scope' ),
			'layout_selected_cats' => $request->get_param( 'layout_selected_cats' ),
			'layout_selected_tags' => $request->get_param( 'layout_selected_tags' ),
			'layout_new_cat'       => $request->get_param( 'layout_new_cat' ),
			'layout_new_tag'       => $request->get_param( 'layout_new_tag' ),
			'columns_layout'       => $request->get_param( 'columns_layout' ),
			'module_type'          => $request->get_param( 'module_type' ),
			'module_width'         => $request->get_param( 'module_width' ),
			'layout_content'       => wp_slash( $layout_content ),
		];

		$presets = json_decode( $request->get_param( 'presets' ), true );
		$images  = json_decode( $request->get_param( 'images' ), true );

		if ( ! empty( $presets ) ) {
			do_action( 'et_pb_before_library_preset_import' );

			self::_update_presets( $presets );
		}

		if ( ! empty( $images ) ) {
			$args['layout_content'] = self::_update_images( $images, $args['layout_content'] );
		}

		// Save layout to the database.
		$new_layout_meta = et_pb_submit_layout( $args );

		foreach ( [ 'layout_category', 'layout_tag' ] as $taxonomy ) {
			$raw_terms_defaults = get_terms( $taxonomy, [ 'hide_empty' => false ] );

			/**
			 * Filter to modify the default terms for the layout_category and layout_tag taxonomies.
			 *
			 * @since ??
			 * @deprecated 5.0.0 Use the {@see 'divi_visual_builder_rest_divi_library_new_layout_taxonomy_terms'} filter instead.
			 *
			 * @param array $raw_terms_defaults Array of default terms.
			 */
			$raw_terms_defaults = apply_filters(
				'et_pb_new_layout_cats_array',
				$raw_terms_defaults
			);

			/**
			 * Filter to modify the default terms for the layout_category and layout_tag taxonomies.
			 *
			 * @since ??
			 *
			 * @param array $raw_terms_defaults Array of default terms.
			 */
			$raw_terms_array = apply_filters( 'divi_visual_builder_rest_divi_library_new_layout_taxonomy_terms', $raw_terms_defaults );

			$clean_terms_array = [];

			if ( is_array( $raw_terms_array ) && ! empty( $raw_terms_array ) ) {
				foreach ( $raw_terms_array as $term ) {
					$clean_terms_array[] = [
						'name' => html_entity_decode( $term->name ),
						'id'   => $term->term_id,
						'slug' => $term->slug,
					];
				}
			}

			$updated_terms[ $taxonomy ] = $clean_terms_array;
		}

		$new_layout_meta      = json_decode( $new_layout_meta, true );
		$saved_layout_content = get_post_field( 'post_content', $new_layout_meta['post_id'], 'raw' );
		$verification         = $layout_content === $saved_layout_content;

		/**
		 * Filter to modify the save verification result.
		 *
		 * @since ??
		 * @deprecated 5.0.0 Use the {@see 'divi_visual_builder_rest_divi_library_save_verification'} filter instead.
		 *
		 * @param bool $verification Whether to save the verification result.
		 */
		$verification = apply_filters(
			'et_fb_ajax_save_verification_result',
			$verification
		);

		/**
		 * Filter to modify the save verification result.
		 *
		 * @since ??
		 *
		 * @param bool $verification Whether to save the verification result.
		 */
		$save_verification = apply_filters( 'divi_visual_builder_rest_divi_library_save_verification', $verification );

		return [
			'layoutData'       => $new_layout_meta,
			'updatedTerms'     => $updated_terms,
			'saveVerification' => $save_verification,
		];
	}

	/**
	 * Update presets data.
	 *
	 * This function updates the presets data in the database.
	 * Presets can be from D4 library.
	 *
	 * @since ??
	 *
	 * @param array $presets The presets data to be updated.
	 *
	 * @return void
	 */
	protected static function _update_presets( $presets ) {
		$d5_presets        = BuilderConversion::maybe_convert_presets_data( $presets );
		$processed_presets = GlobalPreset::process_presets( $d5_presets );

		GlobalPreset::save_data( $processed_presets );
	}

	/**
	 * Update images data.
	 *
	 * This function updates the images data in the layout content.
	 *
	 * @since ??
	 *
	 * @param array  $images  The images data to be updated.
	 * @param string $content The layout content.
	 *
	 * @return string The updated layout content.
	 */
	protected static function _update_images( $images, $content ) {
		$context                = 'et_builder_layouts';
		$portability_registered = et_core_cache_get( $context, 'et_core_portability' );

		if ( ! $portability_registered ) {
			et_core_portability_register(
				$context,
				array(
					'name' => esc_html__( 'Divi Builder Layout', 'et_builder' ),
					'type' => 'post_type',
					'view' => true,
				)
			);
		}

		$portability_post = new PortabilityPost( $context );

		$temp_id = wp_rand();
		$images  = $portability_post->maybe_paginate_images( $images, 'upload_images', (string) microtime( true ) );
		$content = $portability_post->replace_images_urls( $images, [ $temp_id => $content ] );

		return $content[ $temp_id ];
	}

	/**
	 * Retrieves the arguments array for create item action.
	 *
	 * This function returns an array containing the arguments for create item action used, in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array The array containing the arguments for create item action.
	 */
	public static function create_item_args(): array {
		return [
			'layout_name'          => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'layout_type'          => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'layout',
			],
			'post_type'            => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'page',
			],
			'layout_selected_cats' => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			],
			'layout_selected_tags' => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			],
			'layout_new_cat'       => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			],
			'layout_new_tag'       => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			],
			'columns_layout'       => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '0',
			],
			'module_type'          => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'divi/shortcode-module',
			],
			'module_width'         => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'regular',
			],
			'layout_content'       => [
				'required'          => false,
				'sanitize_callback' => [
					__CLASS__,
					'sanitize_layout_content',
				],
				'default'           => '',
			],
			'presets'              => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			],
		];
	}

	/**
	 * Check if user has permission to create an item.
	 *
	 * This function checks if the current user has the capability to edit posts and if they are allowed
	 * to save items to the Divi library.
	 * If the user does not have the necessary permissions, an error response is returned.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the required permissions, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * $result = DiviLibrary::create_item_permission();
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     echo $result->get_error_message();
	 * } else {
	 *     echo "Permission granted.";
	 * }
	 * ```
	 */
	public static function create_item_permission() {
		$is_allowed_save_to_library = et_pb_is_allowed( 'divi_library' ) && et_pb_is_allowed( 'save_library' );

		if ( ! current_user_can( 'edit_posts' ) || ! $is_allowed_save_to_library ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Sanitizes the layout content, preparing it for database storage.
	 *
	 * This function sanitizes the layout content using `SavingUtility::prepare_content_for_db()`.
	 * It ensures that the content is safe to be stored in the database.
	 *
	 * @since ??
	 *
	 * @param string $layout_content The layout content to be sanitized.
	 *
	 * @return string The sanitized layout content.
	 */
	public static function sanitize_layout_content( string $layout_content ): string {
		return SavingUtility::prepare_content_for_db( $layout_content );
	}

	/**
	 * Get cloud access token using a `WP_REST_Request` object.
	 *
	 * This method retrieves the token and returns it as a response.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error The REST response object.
	 *
	 * @example:
	 * ```php
	 * // Example usage when loading a post with valid post ID.
	 * $request = new WP_REST_Request( 'GET', '/my-api/v1/cloud-token' );
	 *
	 * $response = DiviLibraryController::load( $request );
	 * ```
	 */
	public static function get_token( WP_REST_Request $request ) {
		$token = [
			'cloudToken' => get_transient( 'et_cloud_access_token' ),
		];

		return self::response_success( $token );
	}

	/**
	 * Load the arguments required for the token.
	 * This function returns an array of arguments required for a token used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array Empty array.
	 */
	public static function get_token_args(): array {
		return [];
	}

	/**
	 * Check permission.
	 *
	 * @return bool|WP_Error
	 * @since ??
	 */
	/**
	 * Check edit posts permission for current user.
	 *
	 * Checks if the current user has the capability to edit posts.
	 * If the user does not have the capability, it returns a permission error response.
	 * Otherwise, it returns `true`.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the capability to edit posts, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * // Check if the current user has the capability to edit posts.
	 * $result = DiviLibraryController::get_token_permission();
	 *
	 * if ( $result instanceof \WP_Error ) {
	 *     // Handle error response.
	 * } else {
	 *     // Continue with execution.
	 * }
	 * ```
	 */
	public static function get_token_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Get a list of library layouts.
	 *
	 * Retrieves a list of library layouts and returns a `WP_REST_Response` object with the options.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request     The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error  The `WP_REST_Response` object with the options, or a `WP_Error` object if the request fails.
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request();
	 * $request->set_param( 'postId', '123' );
	 *
	 * $response = DiviLibrary::index( $request );
	 * ```
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request();
	 * $request->set_param( 'postId', '456' );
	 *
	 * $response = DiviLibrary::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ) {
		$type = $request->get_param( 'type' );

		if ( $type ) {
			$data  = [];
			$types = explode( ',', $type );

			foreach ( $types as $data_type ) {

				switch ( $data_type ) {
					case 'page':
						$exclude = [];

						if ( $request->get_param( 'exclude' ) ) {
							$exclude = array_map( 'intval', explode( ',', $request->get_param( 'exclude' ) ) );
						}

						$data[ $data_type ] = \ET_Builder_Library::instance()->builder_library_modal_custom_tabs_existing_pages( $exclude );
						break;

					default:
						$saved_data = \ET_Builder_Library::instance()->builder_library_layouts_data( $data_type );

						if ( isset( $saved_data['layouts_data'] ) ) {
							$data[ $data_type ] = $saved_data['layouts_data'];
						}
				}
			}

			if ( $data ) {
				return self::response_success( $data );
			}
		}

		return self::response_error( 'no_entries_found', esc_html__( 'No entries found.', 'et_builder' ) );
	}

	/**
	 * Retrieves the arguments array for index action.
	 *
	 * This function returns an array containing the arguments for index action, used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array The array containing the arguments for index action.
	 */
	public static function index_args(): array {
		return [
			'type'    => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'exclude' => [
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Check if user has permission for index action.
	 *
	 * This function checks if the current user has the capability to edit posts, used in `register_rest_route()`.
	 * If the user does not have the necessary permissions, an error response is returned.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the required permissions, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * $result = DiviLibrary::index_permission();
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     echo $result->get_error_message();
	 * } else {
	 *     echo "Permission granted.";
	 * }
	 * ```
	 */
	public static function index_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Update the location of a Divi Builder layout.
	 *
	 * This function is used to handle the item location action in the Divi Library.
	 * It retrieves the post or layout content and returns it as a response.
	 * If the content is too large, it also includes a custom HTTP header 'X-Content-Length' as a fallback for the Content-Length header.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The request object containing the necessary parameters.
	 *
	 * @return WP_REST_Response|WP_Error Returns a success `WP_REST_Response` object containing the retrieved content.
	 *                                   If the post or layout is not found, it returns a `WP_Error` object with an error message.
	 *
	 * @example:
	 * ```php
	 * $request = new WP_REST_Request( 'GET' );
	 * // Set necessary parameters in the request as needed
	 * $response = DiviLibrary::item_location( $request );
	 *
	 * // Do something with the response
	 * ```
	 */
	public static function item_location( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		$unsupported_post_types = array(
			ET_BUILDER_LAYOUT_POST_TYPE,
			ET_THEME_BUILDER_TEMPLATE_POST_TYPE,
			ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE,
			ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE,
			ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE,
			ET_THEME_BUILDER_THEME_BUILDER_POST_TYPE,
		);

		if ( ! in_array( get_post_type( $id ), $unsupported_post_types, true ) ) {
			return self::response_error( 'unsupported_post_type', esc_html__( 'Unsupported post type.', 'et_builder' ) );
		}

		wp_delete_post( $id, true );

		return self::response_success(
			array(
				'localLibraryTerms' => [
					'layout_category' => LibraryUtility::prepare_library_terms(),
					'layout_tag'      => LibraryUtility::prepare_library_terms( 'layout_tag' ),
				],
			)
		);
	}

	/**
	 * Get arguments for show action.
	 *
	 * Defines an array of arguments for the show action used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for show action.
	 */
	public static function item_location_args(): array {
		return [
			'id' => [
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
		];
	}

	/**
	 * Check if user has permission for show action.
	 *
	 * Checks if the current user has the permission to edit posts, used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has permission, or `WP_Error` if the user does not have permission.
	 *
	 * @example:
	 * ```php
	 *     // Check if the user has permission to edit posts
	 *     $permission = DiviLibrary::show_permission();
	 *     if ( $permission instanceof WP_Error ) {
	 *         echo $permission->get_error_message();
	 *     } else {
	 *         echo "Permission granted!";
	 *     }
	 * ```
	 */
	public static function item_location_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Load a library item by ID using a `WP_REST_Request` object.
	 *
	 * This method retrieves a post by the provided post ID and returns it as a response.
	 * If the post ID is invalid or the post is not found, an `WP_Error` object response is returned.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error The REST response object.
	 *
	 * @example:
	 * ```php
	 * // Example usage when loading a post with valid post ID.
	 * $request = new WP_REST_Request( 'GET', '/my-api/v1/load-post' );
	 * $request->set_param( 'post_id', 123 );
	 *
	 * $response = DiviLibraryController::load( $request );
	 *
	 * @example:
	 * ```php
	 * // Example usage when loading a post with invalid post ID.
	 * $request = new WP_REST_Request( 'GET', '/my-api/v1/load-post' );
	 * $request->set_param( 'post_id', 'abc' );
	 *
	 * $response = DiviLibraryController::load( $request );
	 * ```
	 */
	public static function load( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'post_id' );

		if ( ! $post_id ) {
			return self::response_error( 'invalid_post_id', esc_html__( 'Invalid Post ID.', 'et_builder' ) );
		}

		$post = DiviLibraryUtility::get_post(
			$post_id,
			[
				'post_type'   => ET_BUILDER_LAYOUT_POST_TYPE,
				'post_status' => 'publish',
			]
		);

		if ( ! $post ) {
			return self::response_error( 'post_not_found', esc_html__( 'Post not found.', 'et_builder' ) );
		}

		// Mask post_password field as it may store private credential data.
		if ( ! empty( $post->post_password ) ) {
			$post->post_password = '***';
		}

		return self::response_success( $post );
	}

	/**
	 * Load the arguments required for a post.
	 *
	 * This function returns an array of arguments required for a post used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array {
	 *     An array of arguments required for a specific post ID.
	 *
	 *     @type array $post_id The arguments for the post.
	 * }
	 */
	public static function load_args(): array {
		return [
			'post_id' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Check permission.
	 *
	 * @return bool|WP_Error
	 * @since ??
	 */
	/**
	 * Check edit posts permission for current user.
	 *
	 * Checks if the current user has the capability to edit posts.
	 * If the user does not have the capability, it returns a permission error response.
	 * Otherwise, it returns `true`.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the capability to edit posts, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * // Check if the current user has the capability to edit posts.
	 * $result = DiviLibraryController::load_permission();
	 *
	 * if ( $result instanceof \WP_Error ) {
	 *     // Handle error response.
	 * } else {
	 *     // Continue with execution.
	 * }
	 * ```
	 */
	public static function load_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Save library item to the database.
	 *
	 * This function receives a `WP_REST_Request` object that contains the layout ID and content parameters.
	 * It then creates an array with the necessary information to update the post's content in the database.
	 * The post_content parameter is fetched from the request using the `content`'key, and the ID is fetched
	 * using the ``layout_id` key. Once the array is created, it is passed to the `wp_update_post()` function to
	 * update the post's content in the database.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The request received with the layout ID and content parameters.
	 *
	 * @return int|WP_Error The ID of the updated post or a `WP_Error` object if the post was not updated.
	 *
	 * @example:
	 * ```php
	 * // Update layout with ID 123 and set its content to 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
	 * $request = new WP_REST_Request( 'POST' );
	 * $request->set_param( 'layout_id', 123 );
	 * $request->set_param( 'content', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' );
	 *
	 * $result = DiviLibrary::save( $request );
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     // Handle error.
	 * } else {
	 *     // Post was updated successfully.
	 *     echo 'Layout updated with ID: ' . $result;
	 * }
	 * ```
	 */
	public static function save( WP_REST_Request $request ) {
		// Apply wp_slash, so the content can be properly processed by wp_update_post and all escaped characters decoded.
		$save = [
			'ID'           => $request->get_param( 'layout_id' ),
			'post_content' => wp_slash( $request->get_param( 'content' ) ),
		];

		return wp_update_post( $save );
	}

	/**
	 * Get arguments for save action.
	 *
	 * Defines an array of arguments for the save action used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for save action.
	 */
	public static function save_args(): array {
		return [
			'layout_id' => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'content'   => [
				'required'          => true,
				'sanitize_callback' => [
					__CLASS__,
					'sanitize_layout_content',
				],
			],
		];
	}

	/**
	 * Check if user has permission for save action.
	 *
	 * Checks if the current user has the permission to edit posts and global library items.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has permission, or `WP_Error` if the user does not have permission.
	 *
	 * @example:
	 * ```php
	 *     // Check if the user has permission to edit posts and global library items
	 *     $permission = DiviLibrary::save_permission();
	 *     if ( $permission instanceof WP_Error ) {
	 *         echo $permission->get_error_message();
	 *     } else {
	 *         echo "Permission granted!";
	 *     }
	 * ```
	 */
	public static function save_permission() {
		$is_allowed_edit_global_items = et_pb_is_allowed( 'edit_global_library' );

		if ( ! current_user_can( 'edit_posts' ) || ! $is_allowed_edit_global_items ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Upload library featured image using a `WP_REST_Request` object.
	 *
	 * This method retrieves the token and returns it as a response.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error The REST response object.
	 *
	 * @example:
	 * ```php
	 * // Example usage when loading a post with valid post ID.
	 * $request = new WP_REST_Request( 'GET', '/my-api/v1/upload_image' );
	 *
	 * $response = DiviLibraryController::upload_image( $request );
	 * ```
	 */
	public static function upload_image( WP_REST_Request $request ) {
		// Require necessary files for media_handle_sideload to work.
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$post_id       = $request->get_param( 'postId' );
		$image_url_raw = $request->get_param( 'imageURL' );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return self::response_error_permission();
		}

		$upload         = media_sideload_image( $image_url_raw, $post_id, $post_id, 'id' );
		$attachment_id  = is_wp_error( $upload ) ? 0 : $upload;
		$image_url      = get_attached_file( $attachment_id );
		$image_metadata = wp_generate_attachment_metadata( $attachment_id, $image_url );

		wp_update_attachment_metadata( $attachment_id, $image_metadata );

		set_post_thumbnail( $post_id, $attachment_id );

		return self::response_success();
	}

	/**
	 * Load the arguments required for the token.
	 * This function returns an array of arguments required for a token used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array Empty array.
	 */
	public static function upload_image_args(): array {
		return [
			'postId'   => [
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'imageURL' => [
				'required'          => true,
				'sanitize_callback' => 'esc_url_raw',
			],
		];
	}

	/**
	 * Check edit posts and library permissions for current user.
	 *
	 * Checks if the current user has the capability to edit posts.
	 * If the user does not have the capability, it returns a permission error response.
	 * Otherwise, it returns `true`.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the capability to edit posts, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * // Check if the current user has the capability to edit posts.
	 * $result = DiviLibraryController::upload_image_permission();
	 *
	 * if ( $result instanceof \WP_Error ) {
	 *     // Handle error response.
	 * } else {
	 *     // Continue with execution.
	 * }
	 * ```
	 */
	public static function upload_image_permission() {
		$is_allowed_save_to_library = et_pb_is_allowed( 'divi_library' ) && et_pb_is_allowed( 'save_library' );

		if ( ! current_user_can( 'edit_posts' ) || ! $is_allowed_save_to_library ) {
			return self::response_error_permission();
		}

		return true;
	}

	/**
	 * Update library item.
	 *
	 * This function takes a `WP_REST_Request` object as a parameter and updates library item terms based on the `data` parameter of the request.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     The REST request object.
	 *
	 *     @type array $data {
	 *         Item update payload.
	 *
	 *         @type array $clickedItem {
	 *            Clicked item details.
	 *         }

	 *         @type string $isCloudItem Is item coming from cloud or local item.
	 *         @type array $itemPayload {
	 *             Update details.
	 *
	 *             @type array $itemTags       List of tags.
	 *             @type array $itemCategories List of categories.
	 *             @type string $updateType    Update type.
	 *             @type string $cloud         Is it a cloud item update.
	 *             @type string $global        Is it a global item update.
	 *             @type string $itemName      New name for item.
	 *         }
	 *     }
	 * }
	 *
	 * @return array Returns an array of data including the updated item and update details.
	 *
	 * @example:
	 * ```php
	 * $request = new \WP_REST_Request( 'POST', '/v1/update-item' );
	 * $request->set_param( 'data', [
	 *     clickedItem => [
	 *       'id' => 1,
	 *       ....
	 *     ],
	 *     isCloudItem => 'no',
	 *     itemPayload => [
	 *         'itemTags' => [1,2,3],
	 *         'itemCategories' => [1,2,3],
	 *         'updateType' => 'delete',
	 *         'cloud' => 'no',
	 *         'global' => 'no',
	 *         'itemName' => 'New item name',
	 *     ],
	 * ] );
	 *
	 * $response = DiviLibrary::update_item( $request );
	 *
	 * print_r( $response );
	 * ```
	 *
	 * @output:
	 * ```php
	 *  Array(
	 *      'updatedItem'  => '123',
	 *      'newItem'  => '',
	 *      'updateType'  => 'delete',
	 *      'categories'  => [1,2,3],
	 *      'tags'  => [1,2,3],
	 *      'updatedTerms' => Array(
	 *           'categories' => Array(),
	 *           'tags' => Array(),
	 *       ),
	 *  )
	 * ```
	 */
	public static function update_item( $request ) {
		$filter_type = '';
		$new_terms   = [];
		$data        = $request->get_param( 'data' );

		$update_details = $data['itemPayload'];
		$update_type    = $update_details['updateType'];
		$item_id        = intval( $data['clickedItem']['id'] );
		$new_id         = '';
		$categories     = empty( $update_details['itemCategories'] ) ? [] : array_unique( array_map( 'intval', $update_details['itemCategories'] ) );
		$tags           = empty( $update_details['itemTags'] ) ? [] : array_unique( array_map( 'intval', $update_details['itemTags'] ) );
		$new_categories = array();
		$new_tags       = array();

		$item_update = array(
			'ID' => $item_id,
		);

		$is_library_post_type = 'et_pb_layout' === get_post_type( $item_id );

		if ( ! empty( $update_details['newCategoryName'] ) && current_user_can( 'manage_categories' ) ) {
			$new_names_array = explode( ',', $update_details['newCategoryName'] );
			foreach ( $new_names_array as $new_name ) {
				if ( '' !== $new_name ) {
					$new_term = wp_insert_term( $new_name, 'layout_category' );

					if ( ! is_wp_error( $new_term ) ) {
						$categories[] = $new_term['term_id'];

						$new_categories[] = array(
							'name'  => $new_name,
							'id'    => $new_term['term_id'],
							'count' => 1,
						);
					} elseif ( ! empty( $new_term->error_data ) && ! empty( $new_term->error_data['term_exists'] ) ) {
						$categories[] = $new_term->error_data['term_exists'];
					}
				}
			}
		}

		if ( ! empty( $update_details['newTagName'] ) && current_user_can( 'manage_categories' ) ) {
			$new_names_array = explode( ',', $update_details['newTagName'] );

			foreach ( $new_names_array as $new_name ) {
				if ( '' !== $new_name ) {
					$new_term = wp_insert_term( $new_name, 'layout_tag' );

					if ( ! is_wp_error( $new_term ) ) {
						$tags[] = $new_term['term_id'];

						$new_tags[] = array(
							'name'  => $new_name,
							'id'    => $new_term['term_id'],
							'count' => 1,
						);
					} elseif ( ! empty( $new_term->error_data ) && ! empty( $new_term->error_data['term_exists'] ) ) {
						$tags[] = $new_term->error_data['term_exists'];
					}
				}
			}
		}

		switch ( $update_type ) {
			case 'duplicate':
				$is_item_from_cloud = isset( $update_details['shortcode'] );
				$title              = sanitize_text_field( $update_details['itemName'] );
				$meta_input         = array();
				$item_thumbnail     = false;

				if ( $is_item_from_cloud ) {
					$content         = $update_details['shortcode'];
					$built_for       = 'page';
					$scope           = ! empty( $update_details['global'] ) && 'on' === $update_details['global'] ? 'global' : 'non_global';
					$layout_type     = isset( $update_details['layoutType'] ) ? sanitize_text_field( $update_details['layoutType'] ) : 'layout';
					$module_width    = isset( $update_details['moduleWidth'] ) ? sanitize_text_field( $update_details['moduleWidth'] ) : 'regular';
					$favorite_status = isset( $update_details['favoriteStatus'] ) && 'on' === sanitize_text_field( $update_details['favoriteStatus'] ) ? 'favorite' : '';

					if ( 'row' === $layout_type ) {
						$meta_input['_et_pb_row_layout'] = $update_details['rowLayout'];
					}

					if ( 'module' === $layout_type ) {
						$meta_input['_et_pb_module_type'] = $update_details['moduleType'];
					}

					if ( '' !== $favorite_status ) {
						$meta_input['favorite_status'] = $favorite_status;
					}
				} else {
					$content        = get_the_content( null, false, $item_id );
					$built_for      = get_post_meta( $item_id, '_et_pb_built_for_post_type', true );
					$module_width   = wp_get_post_terms( $item_id, 'module_width', array( 'fields' => 'names' ) );
					$module_width   = is_wp_error( $module_width ) ? 'regular' : sanitize_text_field( $module_width[0] );
					$layout_type    = wp_get_post_terms( $item_id, 'layout_type', array( 'fields' => 'names' ) );
					$layout_type    = is_wp_error( $layout_type ) || '' === $layout_type ? 'layout' : sanitize_text_field( $layout_type[0] );
					$item_thumbnail = get_post_thumbnail_id( $item_id );

					if ( ! empty( $update_details['global'] ) ) {
						$scope = 'on' === $update_details['global'] ? 'global' : 'non_global';
					} else {
						$scope = wp_get_post_terms( $item_id, 'scope', array( 'fields' => 'names' ) );
						$scope = is_wp_error( $scope ) ? 'non_global' : sanitize_text_field( $scope[0] );
					}

					if ( 'row' === $layout_type ) {
						$row_layout = get_post_meta( $item_id, '_et_pb_row_layout', true );

						$meta_input['_et_pb_row_layout'] = $row_layout;
					}

					if ( 'module' === $layout_type ) {
						$module_type = get_post_meta( $item_id, '_et_pb_module_type', true );

						$meta_input['_et_pb_module_type'] = $module_type;
					}
				}

				$meta_input['_et_pb_built_for_post_type'] = $built_for;

				$new_item = array(
					'post_title'   => $title,
					'post_content' => wp_slash( $content ),
					'post_status'  => 'publish',
					'post_type'    => 'et_pb_layout',
					'tax_input'    => array(
						'layout_category' => $categories,
						'layout_tag'      => $tags,
						'layout_type'     => $layout_type,
						'scope'           => $scope,
						'module_width'    => $module_width,
					),
					'meta_input'   => $meta_input,
				);

				$new_id = wp_insert_post( $new_item );

				if ( $item_thumbnail ) {
					set_post_thumbnail( $new_id, $item_thumbnail );
				}

				break;
			case 'edit_cats':
				if ( ! current_user_can( 'manage_categories' ) ) {
					return;
				}

				wp_set_object_terms( $item_id, $categories, 'layout_category' );
				wp_set_object_terms( $item_id, $tags, 'layout_tag' );
				break;
			case 'rename':
				if ( ! current_user_can( 'edit_post', $item_id ) ) {
					return;
				}

				$item_update['post_title'] = sanitize_text_field( $update_details['itemName'] );
				wp_update_post( $item_update );
				break;
			case 'toggle_fav':
				if ( ! current_user_can( 'edit_post', $item_id ) ) {
					return;
				}

				$favorite_status = 'on' === sanitize_text_field( $update_details['favoriteStatus'] ) ? 'favorite' : '';
				update_post_meta( $item_id, 'favorite_status', $favorite_status );

				break;
			case 'delete':
				if ( current_user_can( 'delete_post', $item_id ) && $is_library_post_type ) {
					wp_trash_post( $item_id );
				}
				break;
			case 'delete_permanently':
				if ( current_user_can( 'delete_post', $item_id ) && $is_library_post_type ) {
					wp_delete_post( $item_id, true );
				}
				break;
			case 'restore':
				if ( ! current_user_can( 'edit_post', $item_id ) || ! $is_library_post_type ) {
					return;
				}

				// wp_untrash_post() restores the post to `draft` by default, we have to set `publish` status via filter.
				add_filter(
					'wp_untrash_post_status',
					function() {
						return 'publish';
					}
				);
				wp_untrash_post( $item_id );
				break;
		}

		return self::response_success(
			[
				'updatedItem'  => $item_id,
				'newItem'      => $new_id,
				'updateType'   => $update_type,
				'categories'   => $categories,
				'tags'         => $tags,
				'updatedTerms' => [
					'categories' => LibraryUtility::prepare_library_terms(),
					'tags'       => LibraryUtility::prepare_library_terms( 'layout_tag' ),
				],
			]
		);
	}

	/**
	 * Retrieves the arguments array for update action.
	 *
	 * This function returns an array containing the arguments for update action, used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return array The array containing the arguments for update action.
	 */
	public static function update_item_args(): array {
		return [
			'data' => [
				'clickedItem' => [
					'required'          => true,
					'sanitize_callback' => function( $data ) {
						$sanitized = [];

						foreach ( $data as $item ) {
							if ( ! $item || ! is_array( $item ) ) {
								continue;
							}

							$sanitized_item = [];

							foreach ( $item as $key => $value ) {
								$sanitized_item[ $key ] = sanitize_text_field( $value );
							}

							$sanitized[] = $sanitized_item;
						}

						return $sanitized;
					},
				],
				'isCloudItem' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'itemPayload' => [
					'itemTags'       => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'itemCategories' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'updateType'     => [
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'cloud'          => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'global'         => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'itemName'       => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			],
		];
	}

	/**
	 * Check if user has permission for update action.
	 *
	 * This function checks if the current user has the capability to edit posts, used in `register_rest_route()`.
	 * If the user does not have the necessary permissions, an error response is returned.
	 *
	 * @since ??
	 *
	 * @return bool|WP_Error Returns `true` if the user has the required permissions, `WP_Error` object otherwise.
	 *
	 * @example:
	 * ```php
	 * $result = DiviLibrary::update_permission();
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     echo $result->get_error_message();
	 * } else {
	 *     echo "Permission granted.";
	 * }
	 * ```
	 */
	public static function update_item_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}


	/**
	 * Splits an item based on the provided request parameters.
	 *
	 * @param WP_REST_Request $request - The WordPress REST request object.
	 * @return WP_REST_Response - The function does not return a value; it sends a JSON response.
	 *
	 * @example
	 * // Assuming a valid WP_REST_Request object named $request
	 * SplitItemTrait::split_item($request);
	 */
	public static function split_item( WP_REST_Request $request ) {

		$id         = $request->get_param( 'id' ) ? absint( $request->get_param( 'id' ) ) : false;
		$prefix     = $request->get_param( 'itemName' ) ? sanitize_text_field( $request->get_param( 'itemName' ) ) : false;
		$to_cloud   = $request->get_param( 'cloud' ) ? sanitize_text_field( $request->get_param( 'cloud' ) ) : 'off';
		$split_type = $request->get_param( 'updateType' ) ? sanitize_text_field( $request->get_param( 'updateType' ) ) : false;
		$origin     = $request->get_param( 'actionOrigin' ) ? sanitize_text_field( $request->get_param( 'actionOrigin' ) ) : '';

		if ( ! $id || ! $split_type || ! $prefix ) {
			wp_send_json_error();
		}

		if ( ! in_array( $split_type, array( 'split_layout', 'split_section', 'split_row' ), true ) ) {
			wp_send_json_error();
		}

		$cloud_content = $request->get_param( 'content' ) ?? '';

		if ( $cloud_content ) {
			$item_content = wp_unslash( reset( $cloud_content['data'] ) );
		} else {
			$item_content = get_the_content( null, false, $id );
		}

		switch ( $split_type ) {
			case 'split_layout':
				$pattern     = '/<!-- wp:divi\/section.*?<!-- \/wp:divi\/section -->/s';
				$layout_type = 'section';
				break;

			case 'split_section':
				$pattern     = '/<!-- wp:divi\/row(?:-inner)? .*?<!-- \/wp:divi\/row(?:-inner)? -->/s';
				$layout_type = 'row';
				break;

			case 'split_row':
				$pattern     = '/<!-- wp:divi\/(?!row|section|column|row-inner|column-inner)[^ ]*.*?-->/s';
				$layout_type = 'module';
				break;
		}

		// Get the intented content array based on split type pattern.
		preg_match_all( $pattern, $item_content, $matches );

		$args = array(
			'split_type'           => $split_type,
			'layout_type'          => $layout_type,
			'layout_selected_cats' => $request->get_param( 'itemCategories' ) ? array_map( 'sanitize_text_field', $request->get_param( 'itemCategories' ) ) : array(),
			'layout_selected_tags' => $request->get_param( 'itemTags' ) ? array_map( 'sanitize_text_field', $request->get_param( 'itemTags' ) ) : array(),
			'built_for_post_type'  => 'page',
			'layout_new_cat'       => $request->get_param( 'newCategoryName' ) ? sanitize_text_field( $request->get_param( 'newCategoryName' ) ) : '',
			'layout_new_tag'       => $request->get_param( 'newTagName' ) ? sanitize_text_field( $request->get_param( 'newTagName' ) ) : '',
			'columns_layout'       => '0',
			'module_type'          => 'et_pb_unknown',
			'layout_scope'         => $request->get_param( 'global' ) && ( 'on' === $request->get_param( 'global' ) ) ? 'global' : 'not_global',
			'module_width'         => 'regular',
		);

		$layouts   = array();
		$processed = false;

		foreach ( $matches[0] as $key => $content ) {
			$title = $prefix;

			if ( 'split_row' === $split_type && 'save_modal' === $origin ) {
				$module_name = explode( ' ', $content )[0];
				$module_name = str_replace( '[et_pb_', '', $module_name );
				$module_name = ucfirst( str_replace( '_', ' ', $module_name ) );
				$title       = str_replace( '%module_type%', $module_name, $prefix );
			}

			$args['layout_name'] = $title . ' ' . ( ++$key );

			$content = self::_get_content_with_type( $content, $layout_type );

			if ( 'on' === $to_cloud ) {
				if ( $cloud_content ) {
					/* From cloud to cloud */
					$layouts[] = self::_get_cloud_to_cloud_formatted_data( $cloud_content, $content, $args );
				} else {
					/* From local to cloud */
					$layouts[] = self::_get_local_to_cloud_formatted_data( $content, $args );
				}
			} else {
				if ( $cloud_content ) {
					/* From cloud to local */
					$cloud_content['data']['1'] = $content;

					$layouts[] = self::_get_cloud_to_local_formatted_data( $cloud_content, $content, $args );

					// We only need to insert these data once into the database.
					unset( $cloud_content['presets'] );
					unset( $cloud_content['global_colors'] );
					unset( $cloud_content['images'] );
					unset( $cloud_content['thumbnails'] );
				} else {
					/* From local to Local */
					$args['layout_content']       = wp_slash( $content );
					$args['layout_selected_cats'] = is_array( $args['layout_selected_cats'] ) ? implode( ',', $args['layout_selected_cats'] ) : '';
					$args['layout_selected_tags'] = is_array( $args['layout_selected_tags'] ) ? implode( ',', $args['layout_selected_tags'] ) : '';

					$new_saved = json_decode( et_pb_submit_layout( $args ) );

					// Only need to process once because all the split item's taxonomies are the same.
					if ( ! $processed ) {
						$layouts[] = [
							'newId'         => $new_saved->post_id,
							'categories'    => $args['layout_selected_cats'],
							'tags'          => $args['layout_selected_tags'],
							'updated_terms' => self::_get_all_updated_terms(),
						];
						$processed = true;
					}
				}
			}
		}

		return self::response_success( $layouts );
	}

	/**
	 * Returns an array of arguments for the split_item function.
	 *
	 * @return array - An array of arguments with their requirements and sanitize callbacks.
	 *
	 * @example
	 * // Get the arguments for the split_item function
	 * $args = SplitItemTrait::split_item_args();
	 */
	public static function split_item_args() {
		return [
			'id'              => [
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
			'itemName'        => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'cloud'           => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'updateType'      => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'actionOrigin'    => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'global'          => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'content'         => [
				'required' => false,
			],
			'itemCategories'  => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'itemTags'        => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'newCategoryName' => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'newTagName'      => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Checks if the current user has the permission to split items.
	 *
	 * This method checks if the current user has the 'edit_posts' capability.
	 * If the user does not have the capability, it returns an error response.
	 * Otherwise, it returns true.
	 *
	 * @return bool|WP_REST_Response True if the user has the 'edit_posts' capability, or an error response otherwise.
	 *
	 * @example
	 * ```php
	 * // Example usage for checking the user's permission to split items.
	 * $permission = SplitItemTrait::split_item_permission();
	 * if ( is_wp_error( $permission ) ) {
	 *     // Handle error.
	 * }
	 * ```
	 */
	public static function split_item_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}


	/**
	 * Get content with type.
	 *
	 * @since 4.20.3
	 *
	 * @param string $content Content to be processed.
	 * @param string $type    Type of the content.
	 */
	public static function _get_content_with_type( $content, $type ) {
		$pattern = '/^(\[\w+\s)/';
		$replace = '$0template_type="' . $type . '" '; // e.g. [et_pb_row template_type="row" ...].

		return preg_replace( $pattern, $replace, $content );
	}

		/**
		 * Get formatted data for cloud item split to cloud.
		 *
		 * @since 4.20.3
		 *
		 * @param array  $cloud_content Cloud Item data.
		 * @param string $content       Shortcode after split cloud item.
		 * @param array  $assoc_data    Related data after split cloud item.
		 */
	public static function _get_cloud_to_cloud_formatted_data( $cloud_content, $content, $assoc_data ) {
		$data = self::_get_common_cloud_formatted_data( $content, $assoc_data );

		$images        = array();
		$presets       = array();
		$global_colors = array();

		if ( ! empty( $cloud_content['images'] ) ) {
			foreach ( $cloud_content['images'] as $url => $img ) {
				// Use strpos because str_contains() is not present in PHP version 7.4 or earlier.
				if ( strpos( $content, $url ) !== false ) {
					$images[ $url ] = $img;
				}
			}
		}

		if ( ! empty( $cloud_content['presets'] ) ) {
			foreach ( $cloud_content['presets'] as $module => $preset ) {
				// Use strpos because str_contains() is not present in PHP version 7.4 or earlier.
				if ( strpos( $content, $module ) !== false ) {
					$presets[ $module ] = $preset;
				}
			}
		}

		if ( ! empty( $cloud_content['global_colors'] ) ) {
			foreach ( $cloud_content['global_colors'] as $key => $global_color ) {
				// Use strpos because str_contains() is not present in PHP version 7.4 or earlier.
				if ( strpos( $content, $global_color[0] ) !== false ) {
					$global_colors[ $key ] = $global_color;
				}
			}
		}

		$data['images']        = $images;
		$data['presets']       = $presets;
		$data['global_colors'] = $global_colors;

		return $data;
	}

		/**
		 * Get formatted data for cloud item split to local.
		 *
		 * @since 4.20.3
		 *
		 * @param array  $cloud_content Cloud Item data.
		 * @param string $content       Shortcode after split cloud item.
		 * @param array  $assoc_data    Related data after split cloud item.
		 *
		 * @return array
		 */
	public static function _get_cloud_to_local_formatted_data( $cloud_content, $content, $assoc_data ) {
		return array(
			'itemName'        => $assoc_data['layout_name'],
			'itemCategories'  => $assoc_data['layout_selected_cats'],
			'itemTags'        => $assoc_data['layout_selected_tags'],
			'newCategoryName' => $assoc_data['layout_new_cat'],
			'newTagName'      => $assoc_data['layout_new_tag'],
			'cloud'           => 'off',
			'global'          => $assoc_data['layout_scope'],
			'layoutType'      => $assoc_data['layout_type'],
			'updateType'      => $assoc_data['split_type'],
			'content'         => $cloud_content,
			'shortcode'       => wp_json_encode( $content ),
		);
	}

	/**
	 * Get common formatted data for cloud item.
	 *
	 * @since 4.20.3
	 *
	 * @param string $content    Shortcode after split cloud item.
	 * @param array  $assoc_data Related data after split cloud item.
	 *
	 * @return array
	 */
	public static function _get_common_cloud_formatted_data( $content, $assoc_data ) {
		$data = array(
			'post_title'   => $assoc_data['layout_name'],
			'post_content' => $content,
			'terms'        => array(
				array(
					'name'     => $assoc_data['layout_type'],
					'slug'     => $assoc_data['layout_type'],
					'taxonomy' => 'layout_type',
				),
			),
		);

		foreach ( $assoc_data['layout_selected_cats'] as $category ) {
			$data['terms'][] = array(
				'name'     => $category,
				'slug'     => $category,
				'taxonomy' => 'layout_category',
			);
		}

		foreach ( $assoc_data['layout_selected_tags'] as $tag ) {
			$data['terms'][] = array(
				'name'     => $tag,
				'slug'     => $tag,
				'taxonomy' => 'layout_tag',
			);
		}

		return $data;
	}

		/**
		 * Get all the updated terms.
		 *
		 * @since 4.20.3
		 *
		 * @return array
		 */
	public static function _get_all_updated_terms() {
		$updated_terms = array();

		foreach ( [ 'layout_category', 'layout_tag' ] as $taxonomy ) {
			$raw_terms_array   = get_terms( $taxonomy, array( 'hide_empty' => false ) );
			$clean_terms_array = array();

			if ( is_array( $raw_terms_array ) && ! empty( $raw_terms_array ) ) {
				foreach ( $raw_terms_array as $term ) {
					$clean_terms_array[] = array(
						'name' => html_entity_decode( $term->name ),
						'id'   => $term->term_id,
						'slug' => $term->slug,
					);
				}
			}

			$updated_terms[ $taxonomy ] = $clean_terms_array;
		}

		return $updated_terms;
	}


	/**
	 * Get formatted data for local item split to cloud.
	 *
	 * @since 4.20.3
	 *
	 * @param string $content    Shortcode after split cloud item.
	 * @param array  $assoc_data Related data after split cloud item.
	 *
	 * @return array
	 */
	public static function _get_local_to_cloud_formatted_data( $content, $assoc_data ) {
		return self::_get_common_cloud_formatted_data( $content, $assoc_data );
	}

		/**
		 * Converts a Divi Builder item from one type to another.
		 *
		 * This method is used to convert a Divi Builder item (like a row or a section) from one type to another.
		 * The type of conversion is determined by the 'action' parameter in the WP_REST_Request object.
		 *
		 * @param WP_REST_Request $request The REST request object containing the 'action' and 'id' parameters.
		 *
		 * @example
		 * ```php
		 * // Example usage for converting a row to a section.
		 * $request = new WP_REST_Request( 'GET', '/my-api/v1/convert-item' );
		 * $request->set_param( 'action', 'convert_row_to_section' );
		 * $request->set_param( 'id', '123' );
		 *
		 * ConvertItemTrait::convert_item( $request );
		 * ```
		 */
	public static function convert_item( WP_REST_Request $request ) {
		$action  = $request->get_param( 'action' );
		$id      = $request->get_param( 'id' );
		$content = $request->get_param( 'content' );

		$placeholder_start = '<!-- wp:divi/placeholder -->';
		$placeholder_end   = '<!-- /wp:divi/placeholder -->';
		$section_start     = '<!-- wp:divi/section -->';
		$row_start         = '<!-- wp:divi/row {"module":{"advanced":{"columnStructure":{"desktop":{"value":"4_4"}}}}} -->';
		$column_start      = '<!-- wp:divi/column {"module":{"advanced":{"type":{"desktop":{"value":"4_4"}}}}} -->';
		$column_end        = '<!-- /wp:divi/column -->';
		$row_end           = '<!-- /wp:divi/row -->';
		$section_end       = '<!-- /wp:divi/section -->';
		$fullwidth_wrapper = str_replace( 'fullwidth="off"', 'fullwidth="on" template_type="section"', $section_start );

		switch ( $action ) {
			case 'convert_row_to_section':
				$wrapper_start = $section_start;
				$wrapper_end   = $section_end;
				$from_type     = 'row';
				$to_type       = 'section';
				break;

			case 'convert_module_to_row':
				$wrapper_start = $row_start . $column_start;
				$wrapper_end   = $column_end . $row_end;
				$from_type     = 'module';
				$to_type       = 'row';
				break;

			case 'convert_module_to_section':
				$wrapper_start = $section_start . $row_start . $column_start;
				$wrapper_end   = $column_end . $row_end . $section_end;
				$from_type     = 'module';
				$to_type       = 'section';
				break;
		}

		/**
		 * For cloud item.
		 */
		if ( ! empty( $content ) ) {
			$post_content = $placeholder_start . $wrapper_start . $content . $wrapper_end . $placeholder_end;

			$response = [
				'data' => [ '1' => $post_content ],
			];

			return self::response_success( $response );
		}

		/**
		 * For local item.
		 */
		$post_id = isset( $id ) ? absint( $id ) : 0;
		$item    = get_post( $post_id );

		if ( ! $item ) {
			return self::response_error( 'post_not_found', esc_html__( 'Post not found.', 'et_builder' ) );
		}

		if ( 'convert_module_to_section' === $action ) {
			$module_type = get_post_meta( $post_id, '_et_pb_module_type', true );

			if ( false !== strpos( $module_type, 'et_pb_fullwidth' ) ) {
				// For fullwidth module, there is no row and column.
				$wrapper_start = $fullwidth_wrapper;
				$wrapper_end   = $section_end;
			}
		}

		$pattern      = '/<!-- wp:divi\/placeholder -->([\s\S]*?)<!-- \/wp:divi\/placeholder -->/s';
		$post_content = $item->post_content;
		preg_match( $pattern, $post_content, $matches );
		if ( isset( $matches[1] ) ) {
			$post_content = $matches[1];
		}

		$scope_terms  = get_the_terms( $post_id, 'scope' );
		$scope_terms  = wp_list_pluck( $scope_terms, 'slug' );
		$is_global    = in_array( 'global', $scope_terms, true );
		$post_content = $placeholder_start . $wrapper_start . $post_content . $wrapper_end . $placeholder_end;

		$new_id = wp_insert_post(
			[
				'ID'           => $is_global ? 0 : $post_id, // If global item, create a new post.
				'post_content' => wp_slash( $post_content ),
				'post_date'    => $item->post_date,
				'post_title'   => $item->post_title,
				'post_type'    => $item->post_type,
				'post_status'  => 'publish',
			]
		);

		if ( is_wp_error( $new_id ) ) {
			return self::response_error( $new_id->get_error_code(), $new_id->get_error_message() );
		}

		if ( ! $is_global ) {
			wp_remove_object_terms( $post_id, $from_type, 'layout_type' );
		}

		add_post_meta( $new_id, '_et_pb_built_for_post_type', 'page' );
		delete_post_meta( $new_id, '_et_pb_module_type' );
		wp_set_object_terms( $new_id, $to_type, 'layout_type' );

		return self::response_success( [ $post_content ] );
	}

	/**
	 * Defines the arguments for the `convert_item` method.
	 *
	 * This method returns an array of arguments that are required for the `convert_item` method.
	 * Each argument has a 'required' flag and a 'sanitize_callback' function for validation and sanitization.
	 *
	 * @return array The array of arguments for the `convert_item` method.
	 *
	 * @example
	 * ```php
	 * // Example usage for getting the arguments for the `convert_item` method.
	 * $args = ConvertItemTrait::convert_item_args();
	 * ```
	 */
	public static function convert_item_args(): array {
		return [
			'id'      => [
				'required'          => false,
				'sanitize_callback' => 'absint',
			],
			'content' => [
				'required' => false,
			],
			'action'  => [
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Checks if the current user has the permission to convert items.
	 *
	 * This method checks if the current user has the 'edit_posts' capability.
	 * If the user does not have the capability, it returns an error response.
	 * Otherwise, it returns true.
	 *
	 * @return bool|WP_REST_Response True if the user has the 'edit_posts' capability, or an error response otherwise.
	 *
	 * @example
	 * ```php
	 * // Example usage for checking the user's permission to convert items.
	 * $permission = ConvertItemTrait::convert_item_permission();
	 * if ( is_wp_error( $permission ) ) {
	 *     // Handle error.
	 * }
	 * ```
	 */
	public static function convert_item_permission() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::response_error_permission();
		}

		return true;
	}
}
