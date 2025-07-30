<?php
/**
 * Module: DynamicContentOptionProductID class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptionProductID class.
 *
 * @since ??
 */
class DynamicContentOptionProductID extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the product id option.
	 *
	 * @since ??
	 *
	 * @return string The name of the product id option.
	 */
	public function get_name(): string {
		return 'product_id';
	}

	/**
	 * Get the label for the product id option.
	 *
	 * This function retrieves the localized label for the product id option,
	 * which is used to describe the product id in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the product id option.
	 */
	public function get_label(): string {
		return esc_html__( 'Product ID', 'et_builder' );
	}

	/**
	 * Callback for registering product id option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for product id by adding them to the options array passed to the function .
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
		if ( ! isset( $options[ 'loop_' . $this->get_name() ] ) && et_is_woocommerce_plugin_active() ) {
			$options[ 'loop_' . $this->get_name() ] = [
				'id'     => 'loop_' . $this->get_name(),
				'label'  => esc_html__( 'Loop', 'et_builder' ) . ' ' . $this->get_label(),
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
	 * Render callback for product id option.
	 *
	 * Retrieves the value of product id option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the product id option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the product id.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the product id option.
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
	 *        'product_id' => 'Product ID',
	 *      ],
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		return $value;
	}
}
