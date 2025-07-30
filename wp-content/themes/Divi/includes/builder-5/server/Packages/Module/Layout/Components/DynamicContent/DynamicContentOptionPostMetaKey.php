<?php
/**
 * Module: DynamicContentOptionPostMetaKey class.
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
use ET\Builder\Framework\Utility\Conditions;

/**
 * Module: DynamicContentOptionPostMetaKey class.
 *
 * @since ??
 */
class DynamicContentOptionPostMetaKey extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post meta key option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post meta key option.
	 */
	public function get_name(): string {
		return 'post_meta_key';
	}

	/**
	 * Get the label for the post meta key option.
	 *
	 * This function retrieves the localized label for the post meta key option,
	 * which is used to describe the post meta key in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post meta key option.
	 */
	public function get_label(): string {
		// The 'Manual Custom Field Name' is the official label name for custom meta option
		// key. So, we keep the same key name and not rename it into 'Option'.
		return esc_html__( 'Manual Custom Field Name', 'et_builder' );
	}

	/**
	 * Callback for registering post meta key option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post meta key by adding them to the options array passed to the function .
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
		if ( ! isset( $options[ $this->get_name() ] ) ) {
			$fields = [
				'before'   => [
					'label'   => esc_html__( 'Before', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'after'    => [
					'label'   => esc_html__( 'After', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'meta_key' => [
					'label' => esc_html__( 'Field Name', 'et_builder' ),
					'type'  => 'text',
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

			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => $this->get_label(),
				'type'   => 'any',
				'custom' => false,
				// The 'Custom Fields' is the official group name for custom meta options
				// group. So, we keep the same group name and not rename it into 'Options'.
				'group'  => esc_html__( 'Custom Fields', 'et_builder' ),
				'fields' => $fields,
			];
		}

		return $options;
	}

	/**
	 * Render callback for post meta key option.
	 *
	 * Retrieves the value of post meta key option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post meta key option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post meta key.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the post meta key option.
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
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name     = $data_args['name'] ?? '';
		$settings = $data_args['settings'] ?? [];
		$post_id  = $data_args['post_id'] ?? null;

		if ( $this->get_name() !== $name ) {
			return $value;
		}

		// TODO feat(D5, Theme Builder): Replace it once the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$is_fe    = 'fe' === et_builder_get_current_builder_type() && ! is_et_theme_builder_template_preview() && ! Conditions::is_rest_api_request() ? true : false;
		$meta_key = $settings['meta_key'] ?? '';
		$value    = get_post_meta( $post_id, $meta_key, true );

		// We want to ensure that custom field conditions work correctly with ACF checkboxes.
		// Since ACF checkboxes return arrays, we need to handle this specific case.
		$value = ArrayUtility::is_array_of_strings( $value ) ? implode( ', ', $value ) : $value;

		if ( ( $is_fe && empty( $value ) ) || empty( $meta_key ) ) {
			$value = '';
		} else {
			if ( empty( $meta_key ) && empty( $value ) ) {
				$value = '';
			} elseif ( empty( $value ) && ! empty( $meta_key ) ) {
				$value = DynamicContentUtils::get_custom_meta_label( $meta_key );
			} elseif ( 'on' !== ( $settings['enable_html'] ?? 'off' ) ) {
				$value = esc_html( $value );
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
