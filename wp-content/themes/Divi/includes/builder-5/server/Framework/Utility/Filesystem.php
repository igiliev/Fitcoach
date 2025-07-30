<?php
/**
 * Filesystem class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

use WP_Filesystem_Direct;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Filesystem class.
 *
 * This class contains methods to work with the WordPress filesystem.
 *
 * @since ??
 */
class Filesystem {

	/**
	 * Proxy method for `Filesystem::set()` to avoid calling it multiple times.
	 *
	 * @since ??
	 *
	 * @link https://developer.wordpress.org/reference/classes/wp_filesystem_direct/
	 *
	 * @return \WP_Filesystem_Direct
	 */
	public static function get() {
		return self::set();
	}

	/**
	 * Get the filesystem method.
	 *
	 * This function should be used to set WordPress's filesystem method to direct.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public static function replace_method() {
		return 'direct';
	}

	/**
	 * Set WordPress filesystem to direct.
	 *
	 * This should only be use to create a temporary file.
	 *
	 * @since ??
	 *
	 * @link https://developer.wordpress.org/reference/classes/wp_filesystem_direct/
	 *
	 * @return \WP_Filesystem_Direct
	 */
	public static function set() {
		global $wp_filesystem;
		static $filesystem = null;

		if ( $wp_filesystem instanceof WP_Filesystem_Direct ) {
			return $wp_filesystem;
		}

		if ( null === $filesystem ) {
			add_filter( 'filesystem_method', array( self::class, 'replace_method' ) );

			WP_Filesystem();

			$filesystem = $wp_filesystem;

			remove_filter( 'filesystem_method', array( self::class, 'replace_method' ) );

			// Restore filesystem to the original method.
			WP_Filesystem();
		}

		return $filesystem;
	}
}
