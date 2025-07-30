<?php
/**
 * ModuleLibrary: Loop Handler class
 *
 * Centralizes loop logic for Divi modules, mirroring the Visual Builder's approach.
 * Supports multiple query types: post_types, post_taxonomies, user_roles.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Loop\LoopUtils;
use WP_Query;
use WP_Term_Query;
use WP_User_Query;

/**
 * LoopHandler class.
 *
 * This class provides centralized loop handling for Divi modules, eliminating
 * the need for each module to implement its own loop logic.
 *
 * Key behavior: Loop logic only applies to the specific module where loop is
 * explicitly enabled. Child modules render normally even if their parent has
 * loop enabled, preventing cascading loop effects.
 *
 * @since ??
 */
class LoopHandler {

	/**
	 * Query type mappings that mirror the Visual Builder implementation.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_query_type_mappings = [
		'post_types'      => [
			'api_type'        => 'post_type',
			'param_name'      => 'post_type',
			'per_page_param'  => 'posts_per_page',
			'offset_param'    => 'post_offset',
			'entity_id_param' => 'post_id',
		],
		'post_taxonomies' => [
			'api_type'        => 'terms',
			'param_name'      => 'taxonomy',
			'per_page_param'  => 'terms_per_page',
			'offset_param'    => 'term_offset',
			'entity_id_param' => 'term_id',
		],
		'user_roles'      => [
			'api_type'        => 'users',
			'param_name'      => 'role',
			'per_page_param'  => 'users_per_page',
			'offset_param'    => 'user_offset',
			'entity_id_param' => 'user_id',
		],
	];

	/**
	 * Wraps a render callback with loop handling logic.
	 *
	 * @since ??
	 *
	 * @param callable $original_callback The original render callback.
	 *
	 * @return callable The wrapped render callback.
	 */
	public static function wrap_render_callback( callable $original_callback ): callable {
		return function( $attrs, $content, $block, $elements, $default_printed_style_attrs ) use ( $original_callback ) {
			// Check for loop settings in the current module's attributes.
			$query_data   = LoopUtils::get_query_args_from_attrs( $attrs );
			$loop_enabled = $query_data['loop_enabled'];

			// Only process loop logic if loop is explicitly enabled for THIS module.
			// This prevents child modules from inheriting parent loop behavior.
			if ( 'on' !== $loop_enabled ) {
				// No loop enabled for this specific module - render normally.
				return call_user_func(
					$original_callback,
					$attrs,
					$content,
					$block,
					$elements,
					$default_printed_style_attrs
				);
			}

			// Loop is explicitly enabled for this module - handle the iteration.
			return self::_handle_loop_rendering( $original_callback, $attrs, $content, $block, $elements, $default_printed_style_attrs, $query_data );
		};
	}

	/**
	 * Handles loop rendering by iterating over query results.
	 *
	 * @since ??
	 *
	 * @param callable $callback                     The render callback to execute for each item.
	 * @param array    $attrs                        Module attributes.
	 * @param string   $content                      Module content.
	 * @param WP_Block $block                        Block instance.
	 * @param object   $elements                     ModuleElements instance.
	 * @param array    $default_printed_style_attrs  Default printed style attributes.
	 * @param array    $query_data                   Loop query data from LoopUtils.
	 *
	 * @return string The rendered output for all loop iterations.
	 */
	private static function _handle_loop_rendering( callable $callback, $attrs, $content, $block, $elements, $default_printed_style_attrs, $query_data ): string {
		$query_type  = $query_data['query_type'];
		$entity_type = $query_data['post_type'] ?? '';

		// Validate query type.
		if ( ! isset( self::$_query_type_mappings[ $query_type ] ) ) {
			// Fallback to single render for unsupported query types.
			return call_user_func( $callback, $attrs, $content, $block, $elements, $default_printed_style_attrs );
		}

		// Get query results based on type.
		$query_results = self::_get_query_results( $query_type, $query_data );

		if ( empty( $query_results ) ) {
			// Generate "No Results Found" content and render it within the module wrapper.
			$no_results_content = LoopUtils::render_no_results_found_message();

			// Add flag to attrs to indicate this is a no-results render.
			$attrs['__loop_no_results'] = true;

			return call_user_func(
				$callback,
				$attrs,
				$no_results_content,
				$block,
				$elements,
				$default_printed_style_attrs
			);
		}

		// Render each result.
		$output = '';
		foreach ( $query_results as $result ) {
			// Set up global context for the current result.
			self::_setup_loop_context( $query_type, $result );

			// Call the original render callback.
			$output .= call_user_func(
				$callback,
				$attrs,
				$content,
				$block,
				$elements,
				$default_printed_style_attrs
			);

			// Reset context.
			self::_reset_loop_context( $query_type );
		}

		return $output;
	}

	/**
	 * Gets query results based on the query type and parameters.
	 *
	 * @since ??
	 *
	 * @param string $query_type The type of query (post_types, post_taxonomies, user_roles).
	 * @param array  $loop_data  Loop configuration data.
	 *
	 * @return array Array of query results.
	 */
	private static function _get_query_results( string $query_type, array $loop_data ): array {
		switch ( $query_type ) {
			case 'post_types':
				return self::_get_post_results( $loop_data );

			case 'post_taxonomies':
				return self::_get_term_results( $loop_data );

			case 'user_roles':
				return self::_get_user_results( $loop_data );

			default:
				return [];
		}
	}

	/**
	 * Gets post query results.
	 *
	 * @since ??
	 *
	 * @param array $loop_data Loop configuration data.
	 *
	 * @return array Array of WP_Post objects.
	 */
	private static function _get_post_results( array $loop_data ): array {
		$query = new WP_Query( $loop_data['query_args'] );

		return $query->have_posts() ? $query->posts : [];
	}

	/**
	 * Gets term query results.
	 *
	 * @since ??
	 *
	 * @param array $loop_data Loop configuration data.
	 *
	 * @return array Array of WP_Term objects.
	 */
	private static function _get_term_results( array $loop_data ): array {
		// Convert post-style args to term query args.
		$term_args = self::_convert_to_term_args( $loop_data );
		$query     = new WP_Term_Query( $term_args );

		return ! empty( $query->terms ) ? $query->terms : [];
	}

	/**
	 * Gets user query results.
	 *
	 * @since ??
	 *
	 * @param array $loop_data Loop configuration data.
	 *
	 * @return array Array of WP_User objects.
	 */
	private static function _get_user_results( array $loop_data ): array {
		// Convert post-style args to user query args.
		$user_args = self::_convert_to_user_args( $loop_data );
		$query     = new WP_User_Query( $user_args );

		return ! empty( $query->results ) ? $query->results : [];
	}

	/**
	 * Converts loop data to term query arguments.
	 *
	 * @since ??
	 *
	 * @param array $loop_data Loop configuration data.
	 *
	 * @return array Term query arguments.
	 */
	private static function _convert_to_term_args( array $loop_data ): array {
		$query_args  = $loop_data['query_args'] ?? [];
		$entity_type = $loop_data['post_type'] ?? '';

		return [
			'taxonomy'   => $entity_type,
			'number'     => $query_args['posts_per_page'] ?? 10,
			'offset'     => $query_args['offset'] ?? 0,
			'orderby'    => $query_args['orderby'] ?? 'name',
			'order'      => $query_args['order'] ?? 'ASC',
			'hide_empty' => false,
		];
	}

	/**
	 * Converts loop data to user query arguments.
	 *
	 * @since ??
	 *
	 * @param array $loop_data Loop configuration data.
	 *
	 * @return array User query arguments.
	 */
	private static function _convert_to_user_args( array $loop_data ): array {
		$query_args  = $loop_data['query_args'] ?? [];
		$entity_type = $loop_data['post_type'] ?? '';

		return [
			'role'    => $entity_type,
			'number'  => $query_args['posts_per_page'] ?? 10,
			'offset'  => $query_args['offset'] ?? 0,
			'orderby' => $query_args['orderby'] ?? 'login',
			'order'   => $query_args['order'] ?? 'ASC',
		];
	}

	/**
	 * Sets up global context for the current loop iteration.
	 *
	 * @since ??
	 *
	 * @param string $query_type The query type.
	 * @param mixed  $result     The current result object.
	 *
	 * @return void
	 */
	private static function _setup_loop_context( string $query_type, $result ): void {
		switch ( $query_type ) {
			case 'post_types':
				setup_postdata( $result );
				break;

			case 'post_taxonomies':
				// For terms, we'll rely on modules to access the result directly.
				// This avoids overriding WordPress globals.
				break;

			case 'user_roles':
				// For users, we'll rely on modules to access the result directly.
				// This avoids overriding WordPress globals.
				break;
		}
	}

	/**
	 * Resets global context after loop iteration.
	 *
	 * @since ??
	 *
	 * @param string $query_type The query type.
	 *
	 * @return void
	 */
	private static function _reset_loop_context( string $query_type ): void {
		switch ( $query_type ) {
			case 'post_types':
				wp_reset_postdata();
				break;

			case 'post_taxonomies':
				// No global reset needed since we don't set globals.
				break;

			case 'user_roles':
				// No global reset needed since we don't set globals.
				break;
		}
	}
}
