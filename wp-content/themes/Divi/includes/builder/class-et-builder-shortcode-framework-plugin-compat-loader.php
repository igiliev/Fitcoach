<?php
/**
 * Load plugin compatibility file when supported plugins are activated.
 *
 * @since ??
 *
 * @package Divi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ET_Builder_Shortcode_Framework_Plugin_Compat_Loader
 *
 * Handles the loading of plugin compatibility files when supported plugins are activated.
 *
 * This particular class is for the shortcode framework.
 *
 * @see ET_Builder_Framework_Plugin_Compat_Loader
 *
 * @since ??
 *
 * @package Divi
 */
class ET_Builder_Shortcode_Framework_Plugin_Compat_Loader extends ET_Builder_Plugin_Compat_Loader_Base {
	/**
	 * Plugin compatibility directory.
	 *
	 * @var string
	 */
	public static $PLUGIN_COMPAT_DIR = 'plugin-compat'; // phpcs:ignore ET.Sniffs.ValidVariableName.PropertyNotSnakeCase -- Part of legacy code.

	/**
	 * Filter name for plugin compatibility path.
	 *
	 * @var string
	 */
	public static $FILTER_NAME = 'et_builder_plugin_compat_path_'; // phpcs:ignore ET.Sniffs.ValidVariableName.PropertyNotSnakeCase -- Part of legacy code.

	/**
	 * Gets the instance of the class.
	 */
	public static function init() {
		return parent::_init( self::class );
	}
};

ET_Builder_Shortcode_Framework_Plugin_Compat_Loader::init();
