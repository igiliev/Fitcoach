<?php
/**
 * Module: DynamicContentOptionProductShortDescription class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptionProductShortDescription class.
 *
 * @since ??
 */
class DynamicContentOptionProductShortDescription extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the product short description option.
	 *
	 * @since ??
	 *
	 * @return string The name of the product short description option.
	 */
	public function get_name(): string {
		return 'product_short_description';
	}

	/**
	 * Get the label for the product short description option.
	 *
	 * This function retrieves the localized label for the product short description option,
	 * which is used to describe the product short description in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the product short description option.
	 */
	public function get_label(): string {
		return esc_html__( 'Product Short Description', 'et_builder' );
	}

	/**
	 * Callback for registering product short description option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for product short description by adding them to the options array passed to the function .
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
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Default',
				'fields' => [
					'before' => [
						'label'   => esc_html__( 'Before', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'after'  => [
						'label'   => esc_html__( 'After', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
				],
			];
		}

		return $options;
	}

	/**
	 * Render callback for product short description option.
	 *
	 * Retrieves the value of product short description option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the product short description option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the product short description.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the product short description option.
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
	 *        'product_title' => 'Product Short Description',
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

		$post = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;

		if ( $post ) {
			// TODO feat(D5, Woo Modules): Replace it later once the foundation of Woo
			// module is implemented in D5.
			$dynamic_product = \ET_Builder_Module_Helper_Woocommerce_Modules::get_product( $post_id );
			$value           = '';

			if ( $dynamic_product ) {
				// TODO feat(D5, Woo Modules): Replace it later once the Woo Description module
				// is implemented in D5.
				// @see https://github.com/elegantthemes/Divi/issues/25690.
				$value = \ET_Builder_Module_Woocommerce_Description::get_description(
					[
						'product'          => $dynamic_product->get_id(),
						'description_type' => 'short_description',
					]
				);

				// Wrap non plain text woo data to add custom selector for styling inheritance.
				$value = DynamicContentElements::get_wrapper_woo_module_element(
					[
						'name'  => $name,
						'value' => $value,
					]
				);
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
