<?php
/**
 * Module: DynamicContentOptionPostTags class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;

/**
 * Module: DynamicContentOptionPostTags class.
 *
 * @since ??
 */
class DynamicContentOptionPostTags extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post tags option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post tags option.
	 */
	public function get_name(): string {
		return 'post_tags';
	}

	/**
	 * Get the label for the post tags option.
	 *
	 * This function retrieves the localized label for the post tags option,
	 * which is used to describe the post tags in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post tags option.
	 */
	public function get_label(): string {
		// Translators: %1$s: Post type name.
		return __( '%1$s Tags', 'et_builder' );
	}

	/**
	 * Callback for registering post tags option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post tags by adding them to the options array passed to the function .
	 * It checks if the current module's name exists as a key in the options array.
	 * If not, it adds the module's name as a key and the specific options for that module as the value.
	 *
	 * @since ??
	 *
	 * @param array  $options The options array to be registered.
	 * @param int    $post_id The post ID.
	 * @param string $context The context in which the options are retrieved e.g `edit`, `display`.
	 *
	 * @return array The options array.
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array {
		$post_type = get_post_type( $post_id );
		$post_type = $post_type ? $post_type : 'post';

		// TODO feat(D5, Theme Builder): Replace `et_theme_builder_is_layout_post_type` and
		// `et_builder_get_public_post_types` once the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$is_tb_layout_post_type = et_theme_builder_is_layout_post_type( $post_type );
		$public_post_types      = et_builder_get_public_post_types();

		// Post type label and tag taxonomy post type.
		$tag_taxonomy_post_type = ! $is_tb_layout_post_type ? $post_type : 'post';

		// Default category type.
		$default_category_type = 'post' === $post_type ? 'category' : "{$post_type}_category";
		$post_taxonomy_types   = DynamicContentPosts::get_taxonomy_by_post_type( $post_type );

		if ( $is_tb_layout_post_type ) {
			$public_post_type_keys = array_keys( $public_post_types );

			foreach ( $public_post_type_keys as $public_post_type_key ) {
				$post_taxonomy_types = array_merge(
					$post_taxonomy_types,
					DynamicContentPosts::get_taxonomy_by_post_type( $public_post_type_key )
				);
			}
		}

		if ( ! isset( $post_taxonomy_types[ $default_category_type ] ) ) {
			$default_category_type = 'category';

			if ( ! empty( $post_taxonomy_types ) ) {
				// Use the 1st available taxonomy as the default value.
				$post_taxonomy_types_keys = array_keys( $post_taxonomy_types );
				$default_category_type    = $post_taxonomy_types_keys[0];
			}
		}

		if ( ! isset( $options[ $this->get_name() ] ) && isset( $post_taxonomy_types[ "{$tag_taxonomy_post_type}_tag" ] ) ) {
			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => esc_html( sprintf( $this->get_label(), DynamicContentUtils::get_post_type_label( $post_id ) ) ),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Default',
				'fields' => [
					'before'            => [
						'label'   => esc_html__( 'Before', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'after'             => [
						'label'   => esc_html__( 'After', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'link_to_term_page' => [
						'label'   => esc_html__( 'Link to Tag Index Pages', 'et_builder' ),
						'type'    => 'yes_no_button',
						'options' => [
							'on'  => et_builder_i18n( 'Yes' ),
							'off' => et_builder_i18n( 'No' ),
						],
						'default' => 'on',
					],
					'separator'         => [
						'label'   => esc_html__( 'Tags Separator', 'et_builder' ),
						'type'    => 'text',
						'default' => ' | ',
					],
					'category_type'     => [
						'label'   => esc_html__( 'Category Type', 'et_builder' ),
						'type'    => 'select',
						'options' => $post_taxonomy_types,
						'default' => "{$tag_taxonomy_post_type}_tag",
					],
				],
			];
		}

		if ( ! isset( $options[ 'loop_product_' . $this->get_name() ] ) && et_is_woocommerce_plugin_active() ) {
			$options[ 'loop_product_' . $this->get_name() ] = [
				'id'     => 'loop_product_' . $this->get_name(),
				'label'  => esc_html( sprintf( $this->get_label(), __( 'Loop Product', 'et_builder' ) ) ),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Loop Product',
				'fields' => [
					'before'        => [
						'label'   => esc_html__( 'Before', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'after'         => [
						'label'   => esc_html__( 'After', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'loop_position' => [
						'label'       => esc_html__( 'Loop Position', 'et_builder' ),
						'type'        => 'text',
						'default'     => '',
						'renderAfter' => 'n',
					],
				],
			];
		}

		return $options;
	}

	/**
	 * Render callback for post tags option.
	 *
	 * Retrieves the value of post tags option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post tags option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post tags.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 *     @type array   $overrides  Optional. An associative array of `option_name => value` to override option value.
	 *                                Default `[]`.
	 * }
	 *
	 * @return string The formatted value of the post tags option.
	 *
	 * @example:
	 * ```php
	 *  $element = new MyDynamicContentElement();
	 *
	 *  // Render the element with a specific value and data arguments.
	 *  $html = $element->render_callback( $value, [
	 *      'name'     => 'my_element',
	 *      'settings' => [
	 *          'post_id' => 123,
	 *          'foo'     => 'bar',
	 *      ],
	 *      'post_id'  => 456,
	 *      'overrides' => [
	 *        'my_element' => 'My Element',
	 *        'product_tags' => 'post tags',
	 *      ],
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name      = $data_args['name'] ?? '';
		$settings  = $data_args['settings'] ?? [];
		$post_id   = $data_args['post_id'] ?? null;
		$overrides = $data_args['overrides'] ?? [];

		if ( $this->get_name() !== $name ) {
			return $value;
		}

		$post = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;

		if ( $post ) {
			$overrides_map     = [
				'category' => 'post_categories',
				'post_tag' => 'post_tags',
			];
			$taxonomy          = $settings['category_type'] ?? '';
			$post_taxonomies   = DynamicContentPosts::get_taxonomy_by_post_type( get_post_type( $post_id ) );
			$tb_old_taxonomies = [
				'et_header_layout_category',
				'et_body_layout_category',
				'et_footer_layout_category',
			];

			// TB layouts were storing an invalid taxonomy in <= 4.0.3 so we have to correct it:.
			if ( in_array( $taxonomy, $tb_old_taxonomies, true ) ) {
				$taxonomy = DynamicContentUtils::get_default_setting_value(
					[
						'post_id' => $post_id,
						'name'    => $name,
						'setting' => 'category_type',
					]
				);
			}

			if ( isset( $post_taxonomies[ $taxonomy ] ) ) {
				$ids_key = $overrides_map[ $taxonomy ] ?? '';

				$overrides_ids_key = $overrides[ $ids_key ] ?? null;
				if ( is_string( $overrides_ids_key ) ) {
					$overrides_ids_key = explode( ',', $overrides_ids_key );
				}

				$ids   = is_array( $overrides_ids_key ) ? array_filter( array_map( 'intval', $overrides_ids_key ) ) : [];
				$terms = ! empty( $ids ) ? get_terms(
					[
						'taxonomy' => $taxonomy,
						'include'  => $ids,
					]
				) : get_the_terms( $post_id, $taxonomy );

				// The D4 uses `link_to_category_page` instead of `link_to_term_page` to get the
				// default value even though the `link_to_category_page` is not registered as a
				// setting. Due to that reason, it always return empty string as default value
				// and causes the categories/tags displayed without link by default. Meanwhile,
				// the default of `link_to_term_page` is `on` and expect to show the categories/
				// tags with link. Hence, we use `on` as default value for `link_to_term_page`
				// setting to fix that behavior.
				// @see https://github.com/elegantthemes/submodule-builder/commit/c3cdfdb52adcd2370714b5034c66262791b314ca.
				$is_link   = 'on' === ( $settings['link_to_term_page'] ?? 'on' );
				$separator = $settings['separator'] ?? ' | ';

				$terms_list_args = [
					'terms'     => $terms,
					'is_link'   => $is_link,
					'separator' => $separator,
				];

				$value = is_array( $terms ) ? DynamicContentElements::get_terms_list( $terms_list_args ) : '';
			}
		}

		return DynamicContentElements::get_wrapper_element(
			[
				'post_id'  => $post_id,
				'name'     => $name,
				'value'    => $value,
				'settings' => $settings,
			]
		);
	}
}
