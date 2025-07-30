<?php
/**
 * Module: DynamicContentGlobalVariableOptions class.
 *
 * @package Builder\Packages\Module
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

use ET\Builder\Packages\GlobalData\GlobalData;
use ET\Builder\FrontEnd\Module\Fonts;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentGlobalVariableOptions class.
 *
 * @since ??
 */
class DynamicContentGlobalVariableOptions extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the global variable option.
	 *
	 * @since ??
	 *
	 * @return string The name of the global variable option.
	 */
	public function get_name(): string {
		return 'gvid-';
	}

	/**
	 * Get the label for the global variable option.
	 *
	 * This function retrieves the localized label for the global variable option,
	 * which is used to describe the global variable in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the global variable option.
	 */
	public function get_label(): string {
		return __( 'Global Variables', 'et_builder' );
	}

	/**
	 * Callback for registering global variable option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for global variable by adding them to the options array passed to the function .
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
		// The global variable option doesn't have any settings. Meanwhile, this method is
		// needed to satisfy the interface. So, we simply return all the options here.
		return $options;
	}

	/**
	 * Get the value of a global variable by its id.
	 *
	 * @since ??
	 *
	 * @param string $id The id of the global variable.
	 *
	 * @return string The value of the global variable.
	 */
	public function get_variable_value_by_id( $id ) {
		foreach ( GlobalData::get_global_variables() as $variable_type => $variables ) {
			if ( is_object( $variables ) ) {
				foreach ( $variables as $variable ) {
					if ( isset( $variable['id'] ) && $variable['id'] === $id ) {
						if ( in_array( $variable_type, [ 'numbers', 'fonts' ], true ) ) {
							if ( 'fonts' === $variable_type && isset( $variable['value'] ) ) {
								// Load required fonts.
								Fonts::add( $variable['value'] );
							}

							return sprintf( 'var(--%s)', $variable['id'] );
						}
						return $variable['value'];
					}
				}
			}
		}
		return '';
	}

	/**
	 * Render callback for globa variable option.
	 *
	 * Retrieves the value of global variable option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the global variable option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the global variable value.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 *     @type bool    $is_content Optional. Whether dynamic content used in module's main_content field.
	 *                               Default `false`.
	 * }
	 *
	 * @return string The formatted value of the global variable option.
	 *
	 * @example:
	 * ```php
	 *  $element = new MyDynamicContentElement();
	 *
	 *  // Render the element with a specific value and data arguments.
	 *  $html = $element->render_callback( $value, [
	 *      'name'     => 'gvid-ags7885ww',
	 *      'settings' => [],
	 *      'post_id'  => 456,
	 *      'value'    => 'My Element',
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name    = $data_args['name'] ?? '';
		$post_id = $data_args['post_id'] ?? null;

		if ( strpos( $name, $this->get_name() ) !== 0 ) {
			return $value;
		}

		$value = $this->get_variable_value_by_id( $name );

		return DynamicContentElements::get_wrapper_element(
			[
				'post_id' => $post_id,
				'name'    => $name,
				'value'   => esc_html( $value ),
			]
		);
	}
}
