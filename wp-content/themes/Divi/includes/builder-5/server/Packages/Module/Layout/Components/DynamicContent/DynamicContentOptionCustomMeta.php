<?php
/**
 * Module: DynamicContentOptionCustomMeta class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;
use ET\Builder\Framework\Utility\ArrayUtility;

/**
 * Module: DynamicContentOptionCustomMeta class.
 *
 * @since ??
 */
class DynamicContentOptionCustomMeta extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Retrieves the name of the custom meta option.
	 *
	 * This function returns the name of the custom meta option as a string.
	 *
	 * @since ??
	 *
	 * @return string The name of the custom meta option.
	 */
	public function get_name(): string {
		return 'custom_meta_';
	}

	/**
	 * Get the label of the custom meta option.
	 *
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
	 *  ''
	 * ```
	 */
	public function get_label(): string {
		return '';
	}

	/**
	 * Register option callback.
	 *
	 * This is a callback for `divi_module_dynamic_content_options` filter.
	 *
	 * This function is used to register options for dynamic content.
	 * The function returns all the options passed to it.
	 * This function passes the options array to the `divi_module_dynamic_content_custom_meta_options` filter.
	 * The filter allows third parties to add custom meta options to the options array.
	 *
	 * The context must be `display` or `et_pb_is_allowed( 'read_dynamic_content_custom_fields' ) === true`. This is used  is used to determine whether the user has permission to read the dynamic content custom fields
	 *
	 * @since ??
	 *
	 * @param array  $options The options array to be filtered.
	 * @param int    $post_id Post Id.
	 * @param string $context Context e.g `edit`, `display`.
	 *
	 * @return array The options array.
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array {
		// The `read_dynamic_content_custom_fields` is a capability to read the dynamic
		// content and already widely used in D4. So, we keep the same capability name.
		if ( 'display' === $context || et_pb_is_allowed( 'read_dynamic_content_custom_fields' ) ) {
			$raw_meta_keys  = get_post_meta( $post_id );
			$raw_meta_keys  = is_array( $raw_meta_keys ) ? $raw_meta_keys : [];
			$custom_options = [];

			$post_meta_keys = [];
			/**
			 * Filter post meta accepted as custom meta options in dynamic content.
			 *
			 * Post meta prefixed with `_` is considered hidden from dynamic content options by
			 * default due to its nature as "hidden meta keys". This filter allows third parties
			 * to circumvent this limitation.
			 *
			 * @since ??
			 *
			 * @param string[] $post_meta_keys Post meta keys.
			 * @param int      $post_id        Post Id.
			 */
			$post_meta_keys = apply_filters( 'divi_module_dynamic_content_display_hidden_meta_keys', $post_meta_keys, $post_id );

			$display_hidden_meta_keys = (array) $post_meta_keys;

			// Custom meta dynamic content options to be displayed on the TB.
			// TODO feat(D5, Theme Builder): Replace `et_theme_builder_is_layout_post_type` once
			// the Theme Builder is implemented in D5.
			// @see https://github.com/elegantthemes/Divi/issues/25149.
			if ( et_theme_builder_is_layout_post_type( get_post_type( $post_id ) ) ) {
				$raw_meta_keys = array_merge(
					$raw_meta_keys,
					array_flip( DynamicContentOptions::get_most_used_meta_keys() ),
					array_flip( DynamicContentOptions::get_used_meta_keys( $post_id ) )
				);
			}

			foreach ( $raw_meta_keys as $key => $values ) {
				if ( substr( $key, 0, 1 ) === '_' && ! in_array( $key, $display_hidden_meta_keys, true ) ) {
					// Ignore hidden meta keys.
					continue;
				}

				if ( substr( $key, 0, 3 ) === 'et_' ) {
					// Ignore ET meta keys as they are not suitable for dynamic content use.
					continue;
				}

				$option_key = $this->get_name() . $key;

				// Skip if the option is already registered.
				if ( isset( $options[ $option_key ] ) ) {
					continue;
				}

				$label = DynamicContentUtils::get_custom_meta_label( $key );

				/**
				 * Filter the display label for a custom meta.
				 *
				 * @since ??
				 *
				 * @param string $label Custom meta label.
				 * @param string $key   Custom meta key.
				 */
				$label = apply_filters( 'divi_module_dynamic_content_custom_meta_label', $label, $key );

				$fields = [
					'before' => [
						'label'   => et_builder_i18n( 'Before' ),
						'type'    => 'text',
						'default' => '',
						'show_on' => 'text',
					],
					'after'  => [
						'label'   => et_builder_i18n( 'After' ),
						'type'    => 'text',
						'default' => '',
						'show_on' => 'text',
					],
				];

				if ( current_user_can( 'unfiltered_html' ) ) {
					$fields['enable_html'] = [
						'label'   => esc_html__( 'Enable raw HTML', 'et_builder' ),
						'type'    => 'yes_no_button',
						'options' => [
							'on'  => et_builder_i18n( 'Yes' ),
							'off' => et_builder_i18n( 'No' ),
						],
						'default' => 'off',
						'show_on' => 'text',
					];
				}

				$option = [
					'id'       => $option_key,
					'label'    => $label,
					'type'     => 'any',
					'fields'   => $fields,
					'meta_key' => $key,
					'custom'   => true,
					// The 'Custom Fields' is the official group name for custom meta options
					// group. So, we keep the same group name and not rename it into 'Options'.
					'group'    => __( 'Custom Fields', 'et_builder' ),
				];

				$custom_options[ $option_key ] = $option;
			}

			/**
			 * Filter available custom meta options for dynamic content.
			 *
			 * @since ??
			 *
			 * @param array[] $custom_options Custom meta options to filter.
			 * @param int     $post_id        Post Id.
			 * @param mixed[] $raw_meta_keys  Raw meta keys.
			 */
			$custom_options = apply_filters( 'divi_module_dynamic_content_custom_meta_options', $custom_options, $post_id, $raw_meta_keys );

			$custom_options = (array) $custom_options;

			$options = array_merge( $options, $custom_options );
		}

		return $options;
	}

	/**
	 * Render callback for a dynamic content element.
	 *
	 * Retrieves the value of a dynamic content element based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * This function requires permission to read dynamic content.
	 * This is determined by `'edit' === $context && ! et_pb_is_allowed( 'read_dynamic_content_custom_fields' )`
	 *
	 * @since ??
	 *
	 * @param mixed $value      The current value of the dynamic content element.
	 * @param array $data_args  {
	 *   Optional. An array of arguments for retrieving the dynamic content.
	 *   Default `[]`.
	 *
	 *   @type string  $name       Optional. Option name. Default empty string.
	 *   @type array   $settings   Optional. Option settings. Default `[]`.
	 *   @type integer $post_id    Optional. Post Id. Default `null`.
	 *   @type string  $context    Context e.g `edit`, `display`.
	 *   @type array   $overrides  An associative array of option_name => value to override option value(s).
	 *   @type bool    $is_content Whether dynamic content used in module's main_content field.
	 * }
	 *
	 * @return string           The formatted value of the dynamic content element.
	 *
	 * @example:
	 * ```php
	 * // Get the current value of a dynamic content element named "my_date" with default settings.
	 * $value = $this->render_callback( '', [
	 *     'name' => 'my_custom_meta'
	 * ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name     = $data_args['name'] ?? '';
		$settings = $data_args['settings'] ?? [];
		$post_id  = $data_args['post_id'] ?? 0;
		$context  = $data_args['context'] ?? '';

		$allow_render_on_empty = $data_args['allow_render_on_empty'] ?? true;

		// Bail early if the option name doesn't start with `custom_meta_`.
		if ( 0 !== strpos( $name, $this->get_name() ) ) {
			return $value;
		}

		$options     = DynamicContentOptions::get_options( $post_id, $context );
		$option_key  = $options[ $name ]['meta_key'] ?? '';
		$option_type = $options[ $name ]['type'] ?? '';

		// Bail early if the meta key doesn't exist.
		if ( ! $allow_render_on_empty && empty( $option_key ) ) {
			return $value;
		}

		// Bail early if there is no permission to read the dynamic content custom fields.
		// The `read_dynamic_content_custom_fields` is a capability to read the dynamic
		// content and already widely used in D4. So, we keep the same capability name.
		if ( 'edit' === $context && ! et_pb_is_allowed( 'read_dynamic_content_custom_fields' ) ) {
			if ( 'text' === $option_type ) {
				return esc_html__( 'You don\'t have sufficient permissions to access this content.', 'et_builder' );
			}

			return '';
		}

		$enable_html = $settings['enable_html'] ?? DynamicContentUtils::get_default_setting_value(
			[
				'post_id' => $post_id,
				'name'    => $name,
				'setting' => 'enable_html',
			]
		);

		$post = get_post( $post_id );

		if ( $post && ! empty( $option_key ) ) {
			$value = get_post_meta( $post_id, $option_key, true ) ?? '';

			// We want to ensure that custom field conditions work correctly with ACF checkboxes.
			// Since ACF checkboxes return arrays, we need to handle this specific case.
			$value = ArrayUtility::is_array_of_strings( $value ) ? implode( ', ', $value ) : $value;
		}

		/**
		* Filters custom meta value allowing third party to format the values.
		*
		* @since ??
		*
		* @param string  $value     Custom meta option value.
		* @param string  $option_key Custom meta option key.
		* @param integer $post_id   Post ID.
		*/
		$value = apply_filters( 'divi_module_dynamic_content_resolved_custom_meta_value', $value, $option_key, $post_id );

		// Sanitize HTML contents.
		$value = wp_kses_post( $value );

		if ( 'on' !== $enable_html ) {
			$value = esc_html( $value );
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
