<?php
/**
 * Module: DynamicContentOptionBase class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;

/**
 * Module: DynamicContentOptionBase class.
 *
 * @since ??
 */
abstract class DynamicContentOptionBase implements DynamicContentOptionBaseInterface, DependencyInterface {

	/**
	 * Load the dynamic content options and resolved values for a module.
	 *
	 * This function adds filters for the 'divi_module_dynamic_content_options' and 'divi_module_dynamic_content_resolved_value'
	 * hooks to register callback functions that will be executed when the hooks are triggered. These hooks are used to
	 * retrieve and render dynamic content options and resolved values for a module.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $this->load();
	 * ```
	 */
	public function load(): void {
		add_filter( 'divi_module_dynamic_content_options', [ $this, 'register_option_callback' ], 10, 3 );
		add_filter( 'divi_module_dynamic_content_resolved_value', [ $this, 'render_callback' ], 10, 2 );
	}

}
