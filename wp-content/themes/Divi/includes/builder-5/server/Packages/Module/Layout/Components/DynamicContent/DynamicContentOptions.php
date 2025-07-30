<?php
/**
 * Module: DynamicContentOptions class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicData;


if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptions class.
 *
 * To use the dynamic content feature, we need to generate the options first. The options
 * will be used in the Visual Builder and the Frontend. This class is responsible to
 * generate the dynamic content options. This includes:
 * - All options that contains:
 *   - Built-in options.
 *   - Product options.
 *   - Custom meta options that includes:
 *     - Most used meta keys in the site.
 *     - Used meta keys on the post content.
 *
 * In addition, all options are sorted by the `group` and the `id` as fallback.
 *
 * @since ??
 */
class DynamicContentOptions {
	/**
	 * Get an array of options for dynamic content elements.
	 *
	 * This function retrieves an array of options for dynamic content elements based on the provided post ID and context.
	 * This function runs the options through the `divi_module_dynamic_content_options` filter hook.
	 *
	 * @since ??
	 *
	 * @param int|string $post_id The ID of the post.
	 * @param string     $context The context in which the options are retrieved e.g `edit`, `display`.
	 *
	 * @return array An array of options for dynamic content elements.
	 *
	 * @example:
	 * ```php
	 *  // Get the options for dynamic content elements in edit context for a post with ID 123.
	 *  $options = DynamicContentOptions::get_options( 123, 'edit' );
	 * ```
	 *
	 * @example:
	 * ```php
	 *  // Get the options for dynamic content elements in display context for a post with ID 456.
	 *  $options = DynamicContentOptions::get_options( 456, 'display' );
	 * ```
	 */
	public static function get_options( $post_id, string $context ): array {
		// All dynamic content options.
		$dynamic_content_options = [];

		// Type cast variable for the filter hooks.
		$post_id = (int) $post_id;
		$context = (string) $context;

		/**
		 * Filter the dynamic content options.
		 *
		 * @since ??
		 *
		 * @param array  $dynamic_content_options Dynamic content options.
		 * @param int    $post_id                 Post Id.
		 * @param string $context                 Context e.g `edit`, `display`.
		 */
		$dynamic_content_options = apply_filters( 'divi_module_dynamic_content_options', $dynamic_content_options, $post_id, $context );

		$all_options = (array) $dynamic_content_options;
		foreach ( $all_options as $id => $option ) {
			$all_options[ $id ]['id'] = $id;
		}

		$all_option_keys = array_flip( array_keys( $all_options ) );

		// Sort options by group based on the existence `group` and the order of `id`.
		uasort(
			$all_options,
			function ( $first_option, $second_option ) use ( $all_option_keys ) {
				return self::get_sorted_options_comparison_result( $first_option, $second_option, $all_option_keys );
			}
		);

		return $all_options;
	}

	/**
	 * Get the most used meta keys in dynamic content.
	 *
	 * Retrieves an array of the most used meta keys in dynamic content.
	 * The function first checks if the most used meta keys are stored in the
	 * transient `divi_module_dynamic_content_most_used_meta_keys`.
	 * If found, it returns the stored array.
	 * Otherwise, it queries the database to determine the most used meta keys,
	 * stores them in the transient for future use, and returns the array.
	 *
	 * @since ??
	 *
	 * @return array An array of the most used meta keys in dynamic content.
	 */
	public static function get_most_used_meta_keys(): array {
		global $wpdb;

		$most_used_meta_keys = get_transient( 'divi_module_dynamic_content_most_used_meta_keys' );
		if ( false !== $most_used_meta_keys ) {
			return $most_used_meta_keys;
		}

		// TODO feat(D5, Theme Builder): Replace `et_builder_get_public_post_types` once
		// the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$public_post_types = array_keys( et_builder_get_public_post_types() );
		$post_types        = "'" . implode( "','", esc_sql( $public_post_types ) ) . "'";

		$sql = "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm
      INNER JOIN {$wpdb->posts} p ON ( p.ID = pm.post_id AND p.post_type IN ({$post_types}) )
      WHERE pm.meta_key NOT LIKE '\_%'
      GROUP BY pm.meta_key
      ORDER BY COUNT(pm.meta_key) DESC
      LIMIT 50";

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql query does not use users/visitor input
		$most_used_meta_keys = $wpdb->get_col( $sql );

		set_transient( 'divi_module_dynamic_content_most_used_meta_keys', $most_used_meta_keys, 5 * MINUTE_IN_SECONDS );

		return $most_used_meta_keys;
	}

	/**
	 * Sorts the options and returns the comparison result.
	 *
	 * This function compares two options and determines their order based on the following rules:
	 *  - If only the first option has a top group and the second option does not, it returns -1.
	 *  - If only the second option has a top group and the first option does not, it returns 1.
	 *  - If both options have a top group and their groups are different, it returns the difference of their top group values.
	 *  - If none of the above conditions are met, it compares the order of the options based on their index in the $all_option_keys array.
	 *
	 * @since ??
	 *
	 * @param array $first_option      The first option to compare.
	 * @param array $second_option     The second option to compare.
	 * @param array $all_option_keys   The array that maps option keys to their indices.
	 *
	 * @return int   The comparison result as an integer.
	 *
	 * @example:
	 * ```php
	 * $first_option = [
	 *     'group'  => 'Default',
	 *     'id'     => 'option_one'
	 * ];
	 *
	 * $second_option = [
	 *     'group'  => 'Custom Fields',
	 *     'id'     => 'option_two'
	 * ];
	 *
	 * $all_option_keys = [
	 *     'option_one' => 0,
	 *     'option_two' => 1
	 * ];
	 *
	 * $result = get_sorted_options_comparison_result( $first_option, $second_option, $all_option_keys );
	 * echo $result;
	 * ```
	 *
	 * @output:
	 * ```php
	 *  -1
	 * ```
	 */
	public static function get_sorted_options_comparison_result( array $first_option, array $second_option, array $all_option_keys ): int {
		$top = array_flip(
			[
				'Default',
				// The 'Custom Fields' is the official group name for custom meta options
				// group. So, we keep the same group name and not rename it into 'Options'.
				__( 'Custom Fields', 'et_builder' ),
			]
		);

		$first_option_group   = $first_option['group'] ?? 'Default';
		$first_option_is_top  = isset( $top[ $first_option_group ] );
		$second_option_group  = $second_option['group'] ?? 'Default';
		$second_option_is_top = isset( $top[ $second_option_group ] );

		// If the `group` of first option is on top and second option is not simply return -1
		// to keep first option on current order.
		if ( $first_option_is_top && ! $second_option_is_top ) {
			return -1;
		}

		// Otherwise, if the `group` of second option is on top and first option is not simply
		// return 1 to move first option after the second option.
		if ( ! $first_option_is_top && $second_option_is_top ) {
			return 1;
		}

		// If both options are on top and the `group` are not the same, sort it based on the
		// top `group` order. `Default` should be above `Custom Fields`.
		if ( $first_option_is_top && $second_option_is_top && $first_option_group !== $second_option_group ) {
			return $top[ $first_option_group ] - $top[ $second_option_group ];
		}

		// Otherwise, sort it based on the order of the option `id`. The option `id` won't
		// be the same, so it may only return less or more than 0.
		$first_option_index  = $all_option_keys[ ( $first_option['id'] ?? '' ) ] ?? 0;
		$second_option_index = $all_option_keys[ ( $second_option['id'] ?? '' ) ] ?? 0;

		return $first_option_index - $second_option_index;
	}

	/**
	 * Get an array of the most used meta keys for the given post ID.
	 *
	 * The function first checks if the most used meta keys are cached in a transient before retrieving them from the database.
	 *
	 * The returned array is in the format of `[meta_key => meta_key_label]`.
	 * The returned array is sorted by the most used meta keys first.
	 * The returned array is limited to 10 meta keys.
	 * The returned array is cached for 5 minutes in as a transient (`divi_module_dynamic_content_most_used_meta_keys_{$post_id}`).
	 *
	 * @since ??
	 *
	 * @param int|null $post_id The ID of the post.
	 *
	 * @return array An array of the most used meta keys for the given post ID.
	 */
	public static function get_used_meta_keys( ?int $post_id ): array {
		$transient      = 'divi_module_dynamic_content_most_used_meta_keys_' . $post_id;
		$used_meta_keys = get_transient( $transient );

		if ( false !== $used_meta_keys ) {
			return $used_meta_keys;
		}

		// The most used meta keys will change from time to time so we will also retrieve
		// the used meta keys in the layout content to make sure that the previously selected
		// meta keys always stay in the list even if they are not in the most used meta keys
		// list anymore.
		$layout_post    = get_post( $post_id );
		$layout_content = $layout_post->post_content ?? '';
		$used_meta_keys = [];
		$string_values  = DynamicData::get_variable_values( $layout_content );

		foreach ( $string_values as $string_value ) {
			$data_value = DynamicData::get_data_value( $string_value );
			$type       = $data_value['type'] ?? '';
			$value      = $data_value['value'] ?? [];

			if ( 'content' !== $type || empty( $value ) ) {
				continue;
			}

			$name               = $value['name'] ?? '';
			$custom_meta_length = strlen( 'custom_meta_' );

			if ( 'custom_meta_' === substr( $name, 0, $custom_meta_length ) ) {
				$meta_key         = substr( $name, $custom_meta_length );
				$used_meta_keys[] = $meta_key;
			}
		}

		set_transient( $transient, $used_meta_keys, 5 * MINUTE_IN_SECONDS );

		return $used_meta_keys;
	}
}
