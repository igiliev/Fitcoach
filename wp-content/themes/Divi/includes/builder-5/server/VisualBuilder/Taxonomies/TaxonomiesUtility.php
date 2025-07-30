<?php
/**
 * Taxonomies: TaxonomiesUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Taxonomies;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TaxonomiesUtility class.
 *
 * This class provides functionality for retrieving values related to taxonomies.
 *
 * @since ??
 */
class TaxonomiesUtility {

	/**
	 * Retrieves all WP taxonomies for Visual Builder.
	 *
	 * @internal This method is port of `et_fb_get_taxonomy_terms()`.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_taxonomy_terms() {
		$result = array();

		$taxonomies = get_taxonomies();
		foreach ( $taxonomies as $taxonomy => $name ) {
			$terms = get_terms( $name, array( 'hide_empty' => false ) ); // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed -- Need to get the terms for each taxonomy.
			if ( $terms ) {
				$terms_count = count( $terms );
				for ( $i = 0; $i < $terms_count; $i++ ) {
					// `count` gets updated frequently and it causes static cached helpers update.
					// Since we don't use it anywhere, we can exclude the value to avoid the issue.
					unset( $terms[ $i ]->count );
				}
				$result[ $name ] = $terms;
			}
		}

		return $result;
	}
}
