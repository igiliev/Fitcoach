<?php
/**
 * Module: DynamicContentOptionAnyPostLinkUrl class.
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
 * Module: DynamicContentOptionAnyPostLinkUrl class.
 *
 * @since ??
 */
class DynamicContentOptionAnyPostLinkUrl extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the option.
	 *
	 * This function returns the name of the option as a string.
	 *
	 * @since ??
	 *
	 * @return string The name of the option.
	 *
	 * @example:
	 * ```php
	 *  $example = new DynamicContentOptionAnyPostLinkUrl();
	 *  echo $example->get_name();
	 * ```
	 *
	 * @output:
	 * ```php
	 * any_post_link_url
	 * ```
	 */
	public function get_name(): string {
		return 'any_post_link_url';
	}

	/**
	 * Get the label of the option.
	 *
	 * This function retrieves the localized label of the option.
	 * The label is used to describe the option in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label of the option.
	 *
	 * @example
	 * ```php
	 *  $example = new DynamicContentOptionAnyPostLinkUrl();
	 *  echo $example->get_label();
	 * ```

	 * @output
	 * ```php
	 *  Any %1$s Link
	 *  // where `%1$s` is the post type name
	 * ```
	 */
	public function get_label(): string {
		// Translators: %1$s: Post type name.
		return __( 'Any %1$s Link', 'et_builder' );
	}

	/**
	 * Register option callback.
	 *
	 * This is a callback for `divi_module_dynamic_content_options` filter.
	 * This function is used to satisfy the interface requirement, but it doesn't have any specific functionality as of now.
	 * It simply returns all the options passed to it.
	 *
	 * @since ??
	 *
	 * @param array  $options  The options array.
	 * @param int    $post_id  The post ID.
	 * @param string $context  The context e.g `edit`, `display`.
	 *
	 * @return array The options array.
	 *
	 * @example:
	 * ```php
	 *  $options = ['option1' => 'value1', 'option2' => 'value2'];
	 *  $post_id = 123;
	 *  $context = 'edit';
	 *  $registered_options = register_option_callback($options, $post_id, $context);
	 * ```
	 *
	 * @output:
	 * ```php
	 *  ['option1' => 'value1', 'option2' => 'value2']
	 * ```
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array {
		return $options;
	}

	/**
	 * Render callback for post title option.
	 *
	 * Retrieves the value of post title option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post title option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post title.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the post title option.
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
	 *        'product_title' => 'post title',
	 *      ],
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

		$selected_post_id = $settings['post_id'] ?? DynamicContentUtils::get_default_setting_value(
			[
				'post_id' => $post_id,
				'name'    => $name,
				'setting' => 'post_id',
			]
		);

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
}
