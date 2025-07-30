<?php
/**
 * D5 Readiness Plugin Hooks Check
 *
 * This file contains the functionality to check if Divi hooks are used by plugins.
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks;

/**
 * This file checks for plugins that use Divi hooks.
 *
 * @package D5_Readiness
 */
class PluginHooksCheck {

	/**
	 * Divi 4 hooks that we should detect.
	 *
	 * @var array
	 */
	protected $_divi_4_hooks = [
		'et_builder_ready',
		'et_builder_modules_loaded',
		'divi_extensions_init',
	];

	/**
	 * Divi 5 hooks that we should detect.
	 *
	 * @var array
	 */
	protected $_divi_5_hooks = [
		'divi_module_library_modules_dependency_tree',
		'divi_visual_builder_assets_before_enqueue_packages',
		'divi_visual_builder_assets_before_enqueue_scripts',
		'divi_visual_builder_assets_before_enqueue_styles',
	];

	/**
	 * List of plugins that use only Divi 4 hooks.
	 *
	 * @var array
	 */
	protected $_incompatible_plugins = [];

	/**
	 * Run the hook checks.
	 */
	public function run_check() {
		$this->_check_hooks();
	}

	/**
	 * Check if plugins are hooked into the specified Divi hooks.
	 */
	protected function _check_hooks() {
		global $wp_filter;

		// Temporary array to store plugins using Divi 5 hooks.
		$divi_5_plugins = [];

		// Check for Divi 5 hooks first and store the plugins using them.
		foreach ( $this->_divi_5_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) ) {
				foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
					foreach ( $callbacks as $callback ) {
						$plugin_name = $this->_get_plugin_name( $callback['function'] );

						// Only include callbacks from plugins, not from themes or core.
						if ( false !== $plugin_name ) {
							$divi_5_plugins[ $plugin_name ] = true; // Use associative array to avoid duplicates.
						}
					}
				}
			}
		}

		// Add plugins that use Divi 4 hooks to `_incompatible_plugins` only if they don't use Divi 5 hooks.
		foreach ( $this->_divi_4_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) ) {
				foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
					foreach ( $callbacks as $callback ) {
						$plugin_name = $this->_get_plugin_name( $callback['function'] );

						// Only include callbacks from plugins, not from themes or core, and not if they use Divi 5 hooks.
						if ( false !== $plugin_name && ! isset( $divi_5_plugins[ $plugin_name ] ) ) {
							$this->_incompatible_plugins[ $plugin_name ] = true; // Use associative array to avoid duplicates.
						}
					}
				}
			}
		}
	}


	/**
	 * Get the plugin name of the callback function.
	 *
	 * @param mixed $function The callback function.
	 *
	 * @return string|false The plugin name if found, false otherwise.
	 */
	private function _get_plugin_name( $function ) {
		if ( is_array( $function ) ) {
			$reflector = new \ReflectionClass( $function[0] );
		} elseif ( is_string( $function ) && function_exists( $function ) ) {
			$reflector = new \ReflectionFunction( $function );
		} else {
			return false;
		}

		$file       = $reflector->getFileName();
		$plugin_dir = WP_PLUGIN_DIR;

		// Check if the file is within the plugins directory.
		if ( strpos( $file, $plugin_dir ) === 0 ) {
			$relative_path = str_replace( $plugin_dir . '/', '', $file );
			$plugin_name   = explode( '/', $relative_path )[0];

			return $plugin_name;
		}

		// The above code doesn't work when plugins are symlinked, so we need to check the file name.
		$path_parts    = explode( '/', $file );
		$folder_name   = array_slice( $path_parts, -2, 2 );
		$relative_path = implode( '/', $folder_name );

		// Check if this folder/file exists in the plugins directory.
		if ( file_exists( $plugin_dir . '/' . $relative_path ) ) {
			return $folder_name[0];
		}

		return false;
	}

	/**
	 * Get the name of the feature being checked along with the description.
	 *
	 * @return int[]|string[] The name of the feature with a detailed
	 *     description of detected plugins.
	 */
	public function get_detected_plugins() {
		if ( $this->_incompatible_plugins ) {
			return array_keys( $this->_incompatible_plugins );
		}

		return [];
	}

	/**
	 * Determine if any plugins were detected using Divi hooks.
	 *
	 * @return bool True if plugins were detected, false otherwise.
	 */
	public function detected() {
		return ! empty( $this->_incompatible_plugins );
	}

}
