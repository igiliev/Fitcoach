<?php
/**
 * Load plugin compatibility file if supported plugins are activated.
 *
 * @since 0.7 (builder version)
 *
 * @package Divi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ET_Builder_Plugin_Compat_Loader_Base.
 */
class ET_Builder_Plugin_Compat_Loader_Base {
	/**
	 * Unique instance of class.
	 *
	 * @var ET_Builder_Plugin_Compat_Loader_Base
	 */
	public static $instance;

	/**
	 * Plugin compatibility directory.
	 *
	 * Example: 'plugin-compat'.
	 *
	 * @var string
	 */
	public static $PLUGIN_COMPAT_DIR = ''; // phpcs:ignore ET.Sniffs.ValidVariableName.PropertyNotSnakeCase -- Part of legacy code.

	/**
	 * Filter name for plugin compatibility path.
	 *
	 * Example: 'et_builder_plugin_compat_path_'.
	 *
	 * @var string
	 */
	public static $FILTER_NAME = ''; // phpcs:ignore ET.Sniffs.ValidVariableName.PropertyNotSnakeCase -- Part of legacy code.

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->_init_hooks();
	}

	/**
	 * Gets the instance of the class.
	 *
	 * @param string $class Class name.
	 */
	public static function _init( $class ) {
		if ( empty( self::$instance[ $class ] ) ) {
			self::$instance[ $class ] = new $class();
		}

		return self::$instance[ $class ];
	}

	/**
	 * Hook methods to WordPress action and filter.
	 *
	 * @return void
	 */
	private function _init_hooks() {
		// phpcs:disable ET.Sniffs.ValidVariableName.PropertyNotSnakeCase, ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- Part of legacy code.
		// Load plugin.php for frontend usage.
		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// If constants are not defined, bail, this is a base class.
		if ( empty( static::$PLUGIN_COMPAT_DIR ) || empty( static::$FILTER_NAME ) ) {
			return;
		}

		// Loop plugin list and load active plugin compatibility file.
		foreach ( array_keys( get_plugins() ) as $plugin ) {
			// Load plugin compat file if plugin is active.
			if ( is_plugin_active( $plugin ) ) {
				$plugin_compat_name = dirname( $plugin );
				$plugin_compat_url  = apply_filters(
					static::$FILTER_NAME . $plugin_compat_name,
					ET_BUILDER_DIR . static::$PLUGIN_COMPAT_DIR . "/{$plugin_compat_name}.php",
					$plugin_compat_name
				);

				// Load plugin compat file (if compat file found).
				if ( file_exists( $plugin_compat_url ) ) {
					require_once $plugin_compat_url;
				}
			}
		}
		// phpcs:enable
	}
}
