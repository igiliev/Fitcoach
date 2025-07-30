<?php
/**
 * Module Library: Gallery Module REST Controller class.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Gallery;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gallery REST Controller class.
 *
 * @since ??
 */
class GalleryController extends RESTController {

	/**
	 * Retrieve the rendered HTML for the Gallery module.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response returns the REST response object containing the rendered HTML
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'gallery_ids'      => $request->get_param( 'galleryIds' ),
			'gallery_orderby'  => $request->get_param( 'galleryOrderby' ),
			'gallery_captions' => $request->get_param( 'galleryCaptions' ),
			'fullwidth'        => $request->get_param( 'fullwidth' ),
			'orientation'      => $request->get_param( 'orientation' ),
		];

		$attachments = GalleryModule::get_gallery_items( $args );

		$response = [
			'attachments' => $attachments,
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
	 * @return array an array of arguments for the index action
	 */
	public static function index_args(): array {
		return [
			'galleryIds'      => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param, $request, $key ) {
					return is_string( $param );
				},
			],
			'galleryOrderby'  => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'galleryCaptions' => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'fullwidth'       => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'orientation'     => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'showPagination'  => [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Provides the permission status for the index action.
	 *
	 * @since ??
	 *
	 * @return bool returns `true` if the current user has the permission to use the rest endpoint, otherwise `false`
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}
}
