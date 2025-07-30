<?php
/**
 * Library: PrepareLibraryTermsTrait
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Library\LibraryUtilityTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait PrepareLibraryTermsTrait {

	/**
	 * Prepare Library Categories or Tags List.
	 *
	 * Runs the provided data through `et_pb_new_layout_cats_array`
	 * and `divi_library_new_layout_cats_array` filters.
	 * Converts HTML entities in the terms's name to their corresponding characters.
	 *
	 * @since ??
	 *
	 * @param string $taxonomy Optional. Name of the taxonomy. Default `layout_category`.
	 *
	 * @return array {
	 *   Clean Categories/Tags array.
	 *
	 *   @type array $key {
	 *     Category/Tag data.
	 *     This can be an empty array if the filter `et_pb_new_layout_cats_array` and/or
	 *     the filter `divi_library_new_layout_cats_array` returns an empty/non-array value.
	 *
	 *     @type string $name  Name of the term.
	 *     @type int    $id    Term ID.
	 *     @type string $slug  Term slug.
	 *     @type int    $count Number of posts using the term.
	 *   }
	 * }
	 **/
	public static function prepare_library_terms( $taxonomy = 'layout_category' ): array {
		// Library terms.
		$terms = get_terms( $taxonomy, [ 'hide_empty' => false ] );

		/**
		 * Filters new layout category array.
		 *
		 * @since 4.x
		 * @deprecated 5.0.0 Use `divi_library_new_layout_cats_array` hook instead.
		 *
		 * @param array $terms Library terms.
		 */
		$terms = apply_filters(
			'et_pb_new_layout_cats_array',
			$terms
		);

		/**
		 * Filters new layout category array.
		 *
		 * @since ??
		 *
		 * @param array $terms Library terms.
		 */
		$raw_terms_array = apply_filters( 'divi_library_new_layout_cats_array', $terms );

		$clean_terms_array = [];

		if ( is_array( $raw_terms_array ) && ! empty( $raw_terms_array ) ) {
			foreach ( $raw_terms_array as $term ) {
				$clean_terms_array[] = [
					'name'  => html_entity_decode( $term->name ),
					'id'    => $term->term_id,
					'slug'  => $term->slug,
					'count' => $term->count,
				];
			}
		}

		return $clean_terms_array;
	}

}
