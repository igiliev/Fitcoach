<?php
/**
 * Frontend Scripts Class
 *
 * The Frontend Scripts class provides a set of functions that can be used to
 * register scripts and retrieve them for rendering in the frontend.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\FrontEnd\Module;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Registration class for handling frontend scripts.
 *
 * This class is responsible for registering and enqueuing the necessary
 * frontend scripts required by the module. It provides a set of functions that
 * can be used to register and enqueue scripts.
 *
 * @since ??
 */
class Script {

	/**
	 * Stores scripts that will be registered and enqueued.
	 *
	 * This property is used to store an array of scripts that will be
	 * registered and enqueued in WordPress. It helps to organize and manage
	 * the scripts needed for a specific functionality or feature.
	 *
	 * @since ??
	 * @var array $_scripts Scripts to be registered and enqueued.
	 */
	private static $_scripts;

	/**
	 * Register a new script.
	 *
	 * This function allows you to register a new script that can be enqueued
	 * later on. The script can be either a local file or a remote file.
	 * Optionally, you can specify dependencies, module names, version numbers,
	 * and whether the script should be enqueued in the footer.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for registering the script.
	 *
	 *     @type string           $handle    Required. The name of the script. Should be unique.
	 *     @type string|false     $src       Optional. The full URL of the script, or the path of
	 *                                       the script relative to the WordPress root directory. If
	 *                                       set to `false`, the script is an alias of other scripts
	 *                                       it depends on. Default empty string.
	 *     @type string[]         $deps      Optional. An array of registered script handles that
	 *                                       this script depends on. Default `[]`.
	 *     @type string[]         $module    Optional. An array of module names. If set, the script
	 *                                       should only be rendered if module exists on this page.
	 *                                       Default `[]`.
	 *     @type string|bool|null $ver       Optional. The version number of the script. If `null`,
	 *                                       no version number is added. If `false`, a version
	 *                                       number equal to the current installed WordPress version
	 *                                       is automatically added. Default `null`.
	 *     @type bool             $in_footer Optional. Whether to enqueue the script before the
	 *                                       closing `body` tag. Default `false` will enqueue the
	 *                                       script before the closing `head` tag. Default `false`.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $args = array(
	 *     'handle'    => 'my-script',
	 *     'src'       => 'https://example.com/js/my-script.js',
	 *     'deps'      => array( 'jquery' ),
	 *     'module'    => array( 'my-module' ),
	 *     'ver'       => '1.0.0',
	 *     'in_footer' => true,
	 * );
	 * wp_register_script( $args );
	 * ```
	 */
	public static function register( array $args = [
		'handle'     => '',
		'src'        => '',
		'deps'       => [],
		'module'     => [],
		'ver'        => false,
		'in_footer'  => false,
		'is_enqueue' => false,
	] ): void {
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			_doing_it_wrong(
				__METHOD__,
				'Scripts should be registered before `wp_enqueue_scripts` action is executed.',
				'5.0.0'
			);
			return;
		}

		if ( ! isset( $args['handle'] ) || '' === $args['handle'] ) {
			return;
		}

		self::$_scripts[ $args['handle'] ] = array(
			'src'        => $args['src'] ?? '',
			'deps'       => $args['deps'] ?? [],
			'module'     => $args['module'] ?? [],
			'ver'        => $args['ver'] ?? false,
			'in_footer'  => $args['in_footer'] ?? false,
			'is_enqueue' => $args['is_enqueue'] ?? false,
		);
	}

	/**
	 * Get all registered scripts.
	 *
	 * Retrieves an array of all registered scripts in the application.
	 *
	 * @since ??
	 *
	 * @return array An array of registered scripts.
	 */
	public static function get_all(): array {
		return self::$_scripts;
	}
}
