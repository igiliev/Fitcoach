<?php
/**
 * Module: DynamicContentOptionTermDescription class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\Conditions;

/**
 * Module: DynamicContentOptionTermDescription class.
 *
 * @since ??
 */
class DynamicContentOptionTermDescription extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the term/category description option.
	 *
	 * @since ??
	 *
	 * @return string The name of the term/category description option.
	 */
	public function get_name(): string {
		return 'term_description';
	}

	/**
	 * Retrieves the label for the term/category description option.
	 *
	 * This function retrieves the localized label for the term/category description option,
	 * which is used to describe the term/category in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the term/category description option.
	 *
	 * @example:
	 * ```php
	 *  $example = new DynamicContentOptionBase();
	 *  echo $example->get_label();
	 * ```
	 *
	 * @output
	 * ```php
	 *  Category Description
	 * ```
	 */
	public function get_label(): string {
		return esc_html__( 'Category Description', 'et_builder' );
	}

	/**
	 * Callback for registering term/category description option.
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter.
	 * This function adds the term/category description option to the options array.
	 * It is used to register options for term/category description based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array  $options The options array to be registered.
	 * @param int    $post_id The post ID.
	 * @param string $context The context in which the options are retrieved e.g `edit`, `display`.
	 *
	 * @return array The registered options array.
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array {
		$is_tb_enabled = Conditions::is_tb_enabled();

		if ( ! isset( $options[ $this->get_name() ] ) && $is_tb_enabled ) {
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
	 * Render callback for term/category description option.
	 *
	 * This is a callback for the `divi_module_dynamic_content_resolved_value` filter.
	 * This function generates and returns the rendered HTML for the term/category option
	 * based on the provided value and data arguments.
	 * If the value of `$this->get_name()` is not equal to `$name`, then the function returns the provided  value as is.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The value of the term/category description option.
	 * @param array $data_args {
	 *   Optional. An array of data arguments.
	 *   Default is an empty array.
	 *
	 *   @type string $name       Optional. Option name. Default is an empty string.
	 *   @type array  $settings   Optional. Option settings. Default is an empty array.
	 *   @type int    $post_id    Optional. Post ID. Default is null.
	 * }
	 *
	 * @return string The rendered HTML for the term/category option.
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

		$value = et_core_intentionally_unescaped( term_description(), 'cap_based_sanitized' );

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
