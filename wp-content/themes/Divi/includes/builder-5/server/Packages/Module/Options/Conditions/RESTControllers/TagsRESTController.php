<?php
/**
 * Conditions: TagsRESTController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Conditions\RESTControllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Tags REST Controller class.
 *
 * @since ??
 */
class TagsRESTController extends RESTController {

	/**
	 * Retrieves an array of tags and their corresponding label and values.
	 *
	 * This function retrieves and filters the list of tags.
	 *
	 * This function runs the value through `divi_module_options_conditions_tags` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the term information.
	 *
	 * @example:
	 * ```php
	 *  $request    = new \WP_REST_Request();
	 *  $tags = TagsRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$data                = [];
		$included_taxonomies = [ 'post_tag', 'project_tag', 'product_tag' ];

		/**
		 * Filters included taxonomies.
		 *
		 * @since ??
		 *
		 * @param array $included_taxonomies
		 */
		$included_taxonomies = apply_filters( 'et_builder_ajax_get_tags_included_taxonomies', $included_taxonomies );

		$included_taxonomies = array_filter(
			$included_taxonomies,
			function( $taxonomy_slug ) {
				return taxonomy_exists( $taxonomy_slug );
			}
		);

		$tags = get_terms(
			[
				'taxonomy'   => $included_taxonomies,
				'hide_empty' => false,
			]
		);

		foreach ( $tags as $tag ) {
			$tax_name                 = get_taxonomy( $tag->taxonomy )->label;
			$tax_slug                 = get_taxonomy( $tag->taxonomy )->name;
			$data[ $tag->taxonomy ][] = [
				'name'         => et_core_intentionally_unescaped( wp_strip_all_tags( $tag->name ), 'react_jsx' ),
				'id'           => $tag->term_id,
				'taxonomyName' => et_core_intentionally_unescaped( wp_strip_all_tags( $tax_name ), 'react_jsx' ),
				'taxonomySlug' => $tax_slug,
			];
		}

		/**
		 * Filters tags response data.
		 *
		 * @since ??
		 *
		 * @param array $terms Array of tags to include.
		 */
		$data = apply_filters( 'divi_module_options_conditions_tags', $data );

		return self::response_success( $data );
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
	 *               This function always returns `[]`.
	 */
	public static function index_args(): array {
		return [];
	}

	/**
	 * Get the permission status for the index action.
	 *
	 * This function checks if the current user has the permission to use the Visual Builder.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 *              This function always returns `true`.
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
