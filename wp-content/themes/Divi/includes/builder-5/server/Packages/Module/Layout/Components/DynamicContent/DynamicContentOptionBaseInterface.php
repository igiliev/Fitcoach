<?php
/**
 * Module: DynamicContentOptionBaseInterface interface.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

interface DynamicContentOptionBaseInterface {

	/**
	 * Callback for the `divi_module_dynamic_content_options` filter.
	 * This function is used to register options for dynamic content.
	 * The function returns all the options passed to it.
	 *
	 * @since ??
	 *
	 * @param array  $options The options array to be registered.
	 * @param int    $post_id The post ID.
	 * @param string $context The context e.g. `edit`, `display`.
	 *
	 * @return array The registered options array.
	 *
	 * @example
	 * ```php
	 *  $options = ['option1' => 'value1', 'option2' => 'value2'];
	 *  $post_id = 123;
	 *  $context = 'edit';
	 *  $registered_options = $instance->register_option_callback($options, $post_id, $context);
	 * ```
	 *
	 * @output
	 * ```php
	 *  ['option1' => 'value1', 'option2' => 'value2']
	 * ```
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array;


	/**
	 * Render callback for a dynamic content element.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * This function generates and returns the rendered HTML for a dynamic
	 * content element based on the provided value and data arguments.
	 * If the value of `$this->get_name()` is not equal to `$name`, then the function returns the value as is.
	 *
	 * @since ??
	 *
	 * @param mixed $value      The value of the dynamic content element.
	 * @param array $args  {
	 *     Optional. An array of data arguments.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 *     @type string  $context    Context e.g `edit`, `display`.
	 *     @type array   $overrides  An associative array of option_name => value to override option value(s).
	 *     @type bool    $is_content Whether dynamic content used in module's main_content field.
	 * }
	 *
	 * @return string The rendered HTML for the dynamic content element.
	 *
	 * @example:
	 * ```php
	 *    $element = new MyDynamicContentElement();
	 *
	 *    // Render the element with a specific value and data arguments.
	 *    $html = $element->render_callback( $value, [
	 *        'name'     => 'my_element',
	 *        'settings' => [
	 *            'post_id' => 123,
	 *            'foo'     => 'bar',
	 *        ],
	 *        'post_id'  => 456,
	 *    ] );
	 * ```
	 */
	public function render_callback( $value, array $args = [] ): string;
}
