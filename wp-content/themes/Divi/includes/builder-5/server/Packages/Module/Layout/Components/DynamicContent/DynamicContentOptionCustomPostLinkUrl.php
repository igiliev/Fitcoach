<?php
/**
 * Module: DynamicContentOptionCustomPostLinkUrl class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\ArrayUtility;

/**
 * Module: DynamicContentOptionCustomPostLinkUrl class.
 *
 * @since ??
 */
class DynamicContentOptionCustomPostLinkUrl extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Retrieves the name of the custom post link URL option.
	 *
	 * This function returns the name of the custom post link URL option as a string.
	 *
	 * @since ??
	 *
	 * @return string The name of the custom post link URL option.
	 */
	public function get_name(): string {
		return 'post_link_url_%1$s';
	}

	/**
	 * Get the label of the custom post link URL option.
	 *
	 * Retrieves the localized label of the option.
	 * The label is used to describe the option in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label of the option.
	 *
	 * @example:
	 * ```php
	 *     $example = new DynamicContentOptionBase();
	 *     echo $example->get_label();
	 * ```
	 *
	 * @output:
	 * ```php
	 *  %1$s Link
	 * // where %1$s is the current post type name e.g page
	 * ```
	 */
	public function get_label():string {
		// Translators: %1$s: Post type name.
		return __( '%1$s Link', 'et_builder' );
	}

	/**
	 * Callback for `divi_module_dynamic_content_options` filter.
	 *
	 * @since ??
	 *
	 * @param array  $options The options array to be filtered.
	 * @param int    $post_id Post Id.
	 * @param string $context Context e.g `edit`, `display`.
	 *
	 * @return array The options array.
	 *
	 * @example:
	 * ```php
	 *  $options = ['option1' => 'value1', 'option2' => 'value2'];
	 *  $post_id = 123;
	 *  $context = 'display';
	 *  $registered_options = register_option_callback($options, $post_id, $context);
	 * ```
	 *
	 * @output:
	 * ```php
	 * [
	 *   'post_link_url_portfolios' => [
	 *     'id'     => 'post_link_url_portfolios',
	 *     'label'  => 'Portfolio Link',,
	 *     'type'   => 'url',
	 *     'custom' => false,
	 *     'group'  => 'Default',
	 *     'fields' => [
	 *         'post_id' => [
	 *             'label'     => 'Post',
	 *             'type'      => 'select_post',
	 *             'post_type' => 'portfolio',
	 *             'default'   => '',
	 *         ],
	 *      ],
	 *    ],
	 * ]
	 * ```
	 */
	public function register_option_callback( array $options, int $post_id, string $context ):array {
		// Handle in post type URL options.
		// TODO feat(D5, Theme Builder): Replace `et_builder_get_public_post_types` once
		// the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$post_types = et_builder_get_public_post_types();

		// Fill in post type URL options.
		foreach ( $post_types as $post_type ) {
			$post_type_name  = $post_type->name;
			$post_type_label = $post_type->labels->singular_name;
			$key             = sprintf( $this->get_name(), $post_type_name );

			if ( isset( $options[ $key ] ) ) {
				continue;
			}

			$options[ $key ] = [
				'id'     => $key,
				'label'  => esc_html( sprintf( $this->get_label(), $post_type_label ) ),
				'type'   => 'url',
				'custom' => false,
				'group'  => 'Default',
				'fields' => [
					'post_id' => [
						'label'     => $post_type_label,
						'type'      => 'select_post',
						'post_type' => $post_type_name,
						'default'   => '',
					],
				],
			];
		}

		return $options;
	}

	/**
	 * Callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param string $value The original output to filter.
	 * @param array  $data_args {
	 *     An array of arguments.
	 *
	 *     @type string  $name       Option name.
	 *     @type array   $settings   Option settings.
	 *     @type integer $post_id    Post Id.
	 *     @type string  $context    Context e.g `edit`, `display`.
	 *     @type array   $overrides  An associative array of option_name => value to override option value.
	 *     @type bool    $is_content Whether dynamic content used in module's main_content field.
	 * }
	 *
	 * @return string
	 */

	/**
	 * Render callback function that generates a URL based on the selected post ID and given settings.
	 *
	 * This function checks if the provided name matches the name format for a valid post type in the site.
	 * It retrieves the selected post ID from the settings, and generates a URL using the permalink of the post.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the dynamic content element.
	 * @param array $data_args {
	 *   Array of data arguments.
	 *
	 *   @type string  $name       Optional. Option name. Default empty string.
	 *   @type array   $settings   Optional. Option settings. Default `[]`.
	 *   @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 * @return string The generated URL wrapped in a dynamic content element.
	 *
	 * @example:
	 * ```php
	 *   $value = render_callback( '', [
	 *     'name'      => 'post_link',
	 *     'settings'  => [ 'post_id' => 123 ],
	 *     'post_id'   => 123,
	 *   ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name     = $data_args['name'] ?? '';
		$settings = $data_args['settings'] ?? [];
		$post_id  = $data_args['post_id'] ?? null;

		// Handle in post type URL options.
		// TODO feat(D5, Theme Builder): Replace `et_builder_get_public_post_types` once
		// the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$post_types = et_builder_get_public_post_types();

		if ( ! ArrayUtility::find(
			$post_types,
			function( $post_type ) use ( $name ) {
				return sprintf( $this->get_name(), $post_type->name ) === $name;
			}
		) ) {
			return $value;
		}

		$selected_post_id = $settings['post_id'] ?? DynamicContentUtils::get_default_setting_value(
			[
				'post_id' => $post_id,
				'name'    => $name,
				'setting' => 'post_id',
			]
		);

		// If the selected post ID is not numeric, try to get the post ID by slug.
		if ( $selected_post_id && ! is_numeric( $selected_post_id ) ) {
			$selected_post_id = self::get_post_id_by_slug( $selected_post_id, str_replace( 'post_link_url_', '', $name ) ) ?? $selected_post_id;
		}

		$value = esc_url( get_permalink( $selected_post_id ) );

		return DynamicContentElements::get_wrapper_element(
			[
				'post_id'  => $post_id,
				'name'     => $name,
				'value'    => $value,
				'settings' => $settings,
			]
		);
	}

	/**
	 * Retrieves the post ID by its slug and post type.
	 *
	 * @since ??
	 *
	 * @param string $slug The slug of the post.
	 * @param string $post_type The post type.
	 *
	 * @return int|null The post ID if found, null otherwise.
	 */
	public static function get_post_id_by_slug( string $slug, string $post_type ): ?int {
		$args = [
			'name'        => $slug,
			'post_type'   => $post_type,
			'numberposts' => 1,
		];

		// If the post type is 'attachment', we need to modify the query because attachments can have different statuses.
		$args['post_status'] = 'attachment' === $post_type ? [ 'inherit', 'private', 'publish' ] : 'publish';

		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0]->ID : null;
	}
}
