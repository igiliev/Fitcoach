<?php
/**
 * ShortcodeModule: ShortcodeModule.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ShortcodeModule;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ShortcodeModule\Module\Module;
use ET\Builder\Framework\DependencyManagement\DependencyTree;

/**
 * `ShortcodeModule` main class.
 *
 * @since ??
 */
class ShortcodeModule {

	/**
	 * Create an instance of `ShortcodeModule` class.
	 *
	 * Creates an instance of `ShortcodeModule` class and calls `divi_module_library_modules_dependency_tree` action passing self::register_module`.
	 *
	 * @since ??
	 */
	public function __construct() {
		add_action( 'divi_module_library_modules_dependency_tree', [ self::class, 'register_module' ] );
		add_filter( 'et_is_display_conditions_functionality_enabled', [ self::class, 'disable_display_conditions_for_preview' ] );
	}

	/**
	 * Register Shortcode module.
	 *
	 * @param DependencyTree $dependency_tree Dependency tree for VisualBuilder to load.
	 *
	 * @since ??
	 */
	/**
	 * Registers a Shortcode module with the given dependency tree.
	 *
	 * This function adds a module dependency to the specified dependency tree.
	 *
	 * @since ??
	 *
	 * @param DependencyTree $dependency_tree The dependency tree to which the module will be added.
	 *
	 * @return void
	 */
	public static function register_module( DependencyTree $dependency_tree ): void {
		$dependency_tree->add_dependency( new Module() );
	}

	/**
	 * Disable display conditions for preview.
	 *
	 * @param bool $enabled Whether display conditions are enabled.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function disable_display_conditions_for_preview( $enabled ) {
		// Disable Display Conditions when page is loaded in D5 shortcode module iframe preview.
		if ( $enabled && isset( $_GET['et_vb_preview_id'] ) ) {
			// Verify nonce to prevent URL forgery.
			$preview_nonce = sanitize_text_field( $_GET['preview_nonce'] ?? '' );
			if ( wp_verify_nonce( $preview_nonce, 'post_preview_' . get_the_ID() ) ) {
				return false;
			}
		}

		return $enabled;
	}
}

new ShortcodeModule();
