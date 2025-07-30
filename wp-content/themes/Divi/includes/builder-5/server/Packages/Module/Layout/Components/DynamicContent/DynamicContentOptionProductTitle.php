<?php
/**
 * Module: DynamicContentOptionProductTitle class.
 *
 * @package Builder\Packages\Module
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptionProductTitle class.
 *
 * @since ??
 */
class DynamicContentOptionProductTitle extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the product title option.
	 *
	 * @since ??
	 *
	 * @return string The name of the product title option.
	 */
	public function get_name(): string {
		return 'product_title';
	}

	/**
	 * Get the label for the product title option.
	 *
	 * This function retrieves the localized label for the product title option,
	 * which is used to describe the product title in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the product title option.
	 */
	public function get_label(): string {
		// Translators: %1$s: Post type name.
		return __( '%1$s/Archive Title', 'et_builder' );
	}

	/**
	 * Callback for registering product title option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for product title by adding them to the options array passed to the function .
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
	public function register_option_callback( array $options, $post_id, $context ): array {
		if ( ! isset( $options[ 'loop_' . $this->get_name() ] ) && et_is_woocommerce_plugin_active() ) {
			$fields = [
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
			];

			$options[ 'loop_' . $this->get_name() ] = [
				'id'     => 'loop_' . $this->get_name(),
				'label'  => esc_html__( 'Loop Product Title' ),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Loop Product',
				'fields' => array_merge(
					[
						'loop_position' => [
							'label'       => esc_html__( 'Loop Position', 'et_builder' ),
							'type'        => 'text',
							'default'     => '',
							'renderAfter' => 'n',
						],
					],
					$fields
				),
			];
		}

		// The `product_title` option doesn't have any settings. Meanwhile, this method is
		// needed to satisfy the interface. So, we simply return all the options here.
		return $options;
	}

	/**
	 * Render callback for product title option.
	 *
	 * Retrieves the value of product title option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the product title option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the product title.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 *     @type array   $overrides  Optional. An associative array of option_name => value to override option value.
	 *                               Default `[]`.
	 *     @type bool    $is_content Optional. Whether dynamic content used in module's main_content field.
	 *                               Default `false`.
	 * }
	 *
	 * @return string The formatted value of the product title option.
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
	 *        'product_title' => 'Product Title',
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

		$value = $overrides[ $name ] ?? DynamicContentPosts::get_current_page_title( $post_id );
		$value = et_core_intentionally_unescaped( $value, 'cap_based_sanitized' );

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
