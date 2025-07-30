<?php
/**
 * D5 Readiness Helper functions.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Helpers;

use ET\Builder\FrontEnd\Assets\DetectFeature;
use ET\Builder\FrontEnd\BlockParser\BlockParser;
use Divi\D5_Readiness\Server\AJAXEndpoints\CompatibilityChecks;
use Divi\D5_Readiness\Server\PostTypes;

/**
 * Get the list of modules that are ready and not ready for D5 conversation.
 *
 * @return array
 */
function get_modules_conversation_status() : array {
	$used_modules_names = maybe_unserialize( get_transient( 'et_d5_readiness_used_modules' ) );

	$ready_modules     = isset( $used_modules_names['will_convert'] ) ? array_values( $used_modules_names['will_convert'] ) : [];
	$not_ready_modules = isset( $used_modules_names['will_not_convert'] ) ? array_values( $used_modules_names['will_not_convert'] ) : [];

	return [
		'ready'     => $ready_modules,
		'not_ready' => $not_ready_modules,
	];
}

/**
 * Get the list of road map items that are in-progress and completed items.
 *
 * @return array
 */
function get_roadmap_items() {
	// Fetch the Roadmap Items JSON file from the remote Divi Docs URL.
	$response = wp_remote_get( 'https://devalpha.elegantthemes.com/json/roadmapItems.json' );

	// Check for errors.
	if ( is_wp_error( $response ) ) {
		$roadmap_items = []; // Empty array in case of error.
	} else {
		$roadmap_items = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	return $roadmap_items;
}

/**
 * Get the list of cached road map items.
 *
 * @return array
 */
function get_cached_roadmap_items() {
	$transient_name = 'et-d5-roadmap-items';

	// Try to get the data from the cache (transient).
	$cached_data = get_transient( $transient_name );

	// If cached data exists, return it.
	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$data = get_roadmap_items();

	// Store the data in a transient for future use.
	set_transient( $transient_name, $data, HOUR_IN_SECONDS );

	return $data;
}

/**
 * Gets the count of incompatible items.
 *
 * This count is used next to Divi 5 Migrator menu item in the admin menu.
 *
 * @since ??
 *
 * @return int
 */
function get_incompatible_items_count() {
	$incompatible_items_count    = 0;
	$get_overview_status_results = CompatibilityChecks::get_overview_status_results( true, true );

	// Loop through conversion_failed array.
	$conversion_failed = $get_overview_status_results['conversion_failed'];

	foreach ( $conversion_failed as $key => $items ) {
		// Notification bubble count should be 1 when there are incompatible items.
		// So, break the loop when we find the first incompatible item.
		if ( $incompatible_items_count > 0 ) {
			break;
		}

		if ( ! empty( $items ) && 0 === $incompatible_items_count ) {
			$incompatible_items_count++;
		}
	}

	return $incompatible_items_count;
}

/**
 * Check if the rollback is needed.
 *
 * @return bool
 */
function is_rollback_needed() {
	$post_types = PostTypes::get_post_type_slugs();

	$post_ids = [];

	// Collect post IDs from all relevant post types.
	foreach ( $post_types as $post_type ) {
		$args = [
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'meta_query'     => [
				[
					'key'     => '_et_pb_divi_4_content',
					'compare' => 'EXISTS',
				],
			],
		];

		$post_ids = array_merge( $post_ids, get_posts( $args ) );
	}

	return ! empty( $post_ids );
}

/**
 * Check if the conversion is finished.
 *
 * @return bool
 */
function is_conversion_finished() {
	return et_get_option( 'et_d5_readiness_conversion_finished', false );
}

/**
 * Get Module Name from Registered Modules.
 *
 * @since ??
 *
 * @param string $slug The module slug.
 * @param array  $modules The list of registered modules.
 *
 * @return string $results Comma separated list of shortcode names found in widget areas.
 */
function readiness_get_module_name_from_slug( $slug, $modules ) : string {
	if ( ! isset( $modules[ $slug ] ) ) {
		return $slug;
	}

	return is_array( $modules[ $slug ] ) ? $modules[ $slug ]['name'] : $modules[ $slug ]->name;
}

/**
 * Get the module names used in the post content.
 *
 * @since ??
 *
 * @param string $content The post content.
 * @param array  $modules The list of registered modules.
 * @param array  $third_party_module_slugs The list of third party module slugs.
 *
 * @return array The module names used in the post content.
 */
function readiness_get_modules_names_from_content( $content, $modules, $third_party_module_slugs ) : array {
	// force the content to be a string.
	$content = empty( $content ) ? '' : $content;
	$shortcode_slugs = DetectFeature::get_shortcode_names( $content );

	$modules_names = [
		'will_convert'     => [],
		'will_not_convert' => [],
	];

	$ignored_slugs = [
		'et_pb_section',
		'et_pb_row',
		'et_pb_column',
		'et_pb_row_inner',
		'et_pb_column_inner',
	];

	foreach ( $shortcode_slugs as $slug ) {
		if ( ! $slug ) {
			continue;
		}

		if ( array_key_exists( $slug, $third_party_module_slugs ) ) { // Third party modules.
			$modules_names['will_not_convert'][] = readiness_get_module_name_from_slug( $slug, $third_party_module_slugs );
			continue;
		}

		if ( false !== strpos( $slug, 'et_pb_wc_' ) || 'et_pb_shop' === $slug ) { // WooCommerce modules.
			$modules_names['will_not_convert'][] = readiness_get_module_name_from_slug( $slug, $modules );
			continue;
		}

		if ( ! in_array( $slug, $ignored_slugs, true ) ) { // Divi Builder modules.
			$modules_names['will_convert'][] = readiness_get_module_name_from_slug( $slug, $modules );
		}
	}

	return $modules_names;
}

/**
 * Update the used modules names.
 *
 * @param array $used_modules_names The used modules names.
 *
 * @since ??
 *
 * @return void
 */
function readiness_update_used_modules_names( $used_modules_names ) {
	$used_modules_names = [
		'will_convert'     => array_unique( $used_modules_names['will_convert'] ),
		'will_not_convert' => array_unique( $used_modules_names['will_not_convert'] ),
	];

	$fifthteen_minutes = 900;

	set_transient( 'et_d5_readiness_used_modules', maybe_serialize( $used_modules_names ), $fifthteen_minutes );
}
