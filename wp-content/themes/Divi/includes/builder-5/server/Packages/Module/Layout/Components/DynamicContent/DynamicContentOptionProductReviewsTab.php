<?php
/**
 * Module: DynamicContentOptionProductReviewsTab class.
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
 * Module: DynamicContentOptionProductReviewsTab class.
 *
 * @since ??
 */
class DynamicContentOptionProductReviewsTab extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the product reviews tab option.
	 *
	 * @since ??
	 *
	 * @return string The name of the product reviews tab option.
	 */
	public function get_name(): string {
		return 'product_reviews_tab';
	}

	/**
	 * Get the label for the product reviews tab option.
	 *
	 * This function retrieves the localized label for the product reviews tab option,
	 * which is used to describe the product reviews tab in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the product reviews tab option.
	 */
	public function get_label(): string {
		return esc_html__( 'Product Reviews', 'et_builder' );
	}

	/**
	 * Callback for registering product reviews tab option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for product reviews tab by adding them to the options array passed to the function .
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

		// TODO feat(D5, Theme Builder): Replace `et_theme_builder_is_layout_post_type` once
		// the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$is_tb_layout_post_type = et_theme_builder_is_layout_post_type( $post_type );

		if ( ! isset( $options[ $this->get_name() ] ) && et_is_woocommerce_plugin_active() && ( 'product' === $post_type || $is_tb_layout_post_type ) ) {
			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => $this->get_label(),
				'type'   => 'url',
				'custom' => false,
				'group'  => 'Default',
			];
		}

		return $options;
	}

	/**
	 * Render callback for product reviews tab option.
	 *
	 * Retrieves the value of product reviews tab option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the product reviews tab option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the product reviews tab.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the product reviews tab option.
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
	 *        'product_title' => 'Product reviews tab',
	 *      ],
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name = $data_args['name'] ?? '';

		if ( $this->get_name() !== $name ) {
			return $value;
		}

		$value = '#product_reviews_tab';

		return $value;
	}
}
