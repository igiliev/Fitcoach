<?php
/**
 * Conditions: CategoriesRESTController class.
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
 * Categories REST Controller class.
 *
 * @since ??
 */
class CategoriesRESTController extends RESTController {

	/**
	 * Retrieves an array of categories and their corresponding label and values.
	 *
	 * This function retrieves and filters the list of categories.
	 *
	 * This function runs the value through `divi_module_options_conditions_categories` filter.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The REST response object containing the categories information.
	 *
	 * @example:
	 * ```php
	 *  $request    = new \WP_REST_Request();
	 *  $categories = CategoriesRESTController::index( $request );
	 * ```
	 */
	public static function index( WP_REST_Request $request ): WP_REST_Response {
		$data                = [];
		$excluded_taxonomies = [
			'post_tag',
			'project_tag',
			'product_tag',
			'nav_menu',
			'link_category',
			'post_format',
			'layout_category',
			'layout_pack',
			'layout_type',
			'scope',
			'module_width',
			'wp_theme',
		];

		/**
		 * Filters excluded taxonomies.
		 *
		 * @since ??
		 *
		 * @param array $excluded_taxonomies Array of excluded taxonomies.
		 */
		$excluded_taxonomies = apply_filters( 'et_builder_ajax_get_categories_excluded_taxonomies', $excluded_taxonomies );

		$taxonomies = array_diff( get_taxonomies(), $excluded_taxonomies );
		$categories = get_terms(
			[
				'taxonomy'   => $taxonomies,
				'hide_empty' => false,
			]
		);

		foreach ( $categories as $cat ) {
			$tax_name                 = get_taxonomy( $cat->taxonomy )->label;
			$tax_slug                 = get_taxonomy( $cat->taxonomy )->name;
			$data[ $cat->taxonomy ][] = [
				'name'         => et_core_intentionally_unescaped( wp_strip_all_tags( $cat->name ), 'react_jsx' ),
				'id'           => $cat->term_id,
				'taxonomyName' => et_core_intentionally_unescaped( wp_strip_all_tags( $tax_name ), 'react_jsx' ),
				'taxonomySlug' => $tax_slug,
			];
		}

		/**
		 * Filters categories response data.
		 *
		 * @since ??
		 *
		 * @param array $data Array of categories to include.
		 */
		$data = apply_filters( 'divi_module_options_conditions_categories', $data );

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
