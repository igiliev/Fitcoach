<?php
/**
 * Module Options: Background Assets Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module Options: Background assets class.
 *
 * @since ??
 */
class BackgroundAssets {

	/**
	 * Enqueues the background parallax script if it is registered and not already enqueued.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  BackgroundAssets::parallax_script_enqueue();
	 * ```
	 */
	public static function parallax_script_enqueue(): void {
		if ( wp_script_is( self::parallax_script_handle(), 'registered' ) && ! wp_script_is( self::parallax_script_handle(), 'enqueued' ) ) {
			wp_enqueue_script( self::parallax_script_handle() );
		}
	}

	/**
	 * Get the handle for the background parallax script.
	 *
	 * This function returns the handle for the background parallax script,
	 * which is used for enqueuing the script.
	 *
	 * @since ??
	 *
	 * @return string The handle for the background parallax script.
	 *
	 * @example
	 * ```php
	 *  $handle = BackgroundAssets::parallax_script_handle();
	 * ```
	 */
	public static function parallax_script_handle(): string {
		return 'divi-' . self::parallax_script_name();
	}

	/**
	 * Get the name for the background parallax script.
	 *
	 * This function returns the name for the background parallax script,
	 * which is used for enqueuing the script.
	 *
	 * @since ??
	 *
	 * @return string The name for the background parallax script.
	 *
	 * @example
	 * ```php
	 *  $handle = BackgroundAssets::parallax_script_name();
	 * ```
	 */
	public static function parallax_script_name(): string {
		return 'module-script-background-parallax';
	}

	/**
	 * Register background parallax script.
	 *
	 * @since ??
	 */
	/**
	 * Register the background parallax script.
	 *
	 * This function checks if the background parallax script
	 * is not already registered and then registers it.
	 * The script is registered with the handle, source URL,
	 * dependencies, version, and whether it should be loaded in the header or footer.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  // Register the background parallax script.
	 *  BackgroundAssets::parallax_script_register();
	 * ```
	 */
	public static function parallax_script_register(): void {
		if ( ! wp_script_is( self::parallax_script_handle(), 'registered' ) ) {
			wp_register_script(
				self::parallax_script_handle(),
				ET_BUILDER_5_URI . '/visual-builder/build/' . self::parallax_script_name() . '.js',
				[ 'jquery' ],
				ET_CORE_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueues the background parallax style if it is registered and not already enqueued.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  BackgroundAssets::parallax_style_enqueue();
	 * ```
	 */
	public static function parallax_style_enqueue(): void {
		if ( wp_style_is( self::parallax_style_name(), 'registered' ) && ! wp_style_is( self::parallax_style_name(), 'enqueued' ) ) {
			wp_enqueue_style( self::parallax_style_name() );
		}
	}

	/**
	 * Get the name of the background parallax style.
	 *
	 * @since ??
	 *
	 * @return string The name of the background parallax style.
	 *
	 * @example:
	 * ```php
	 *  $styleName = BackgroundAssets::parallax_style_name();
	 * ```
	 */
	public static function parallax_style_name(): string {
		return 'module-style-static-background-parallax';
	}

	/**
	 * Register the parallax style for the background.
	 *
	 * This function registers the parallax style for the background in the Divi Builder.
	 * It is used to enqueue the necessary CSS file for the parallax effect.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  BackgroundAssets::parallax_style_register();
	 * ```
	 */
	public static function parallax_style_register(): void {
		wp_register_style(
			self::parallax_style_name(),
			ET_BUILDER_5_URI . '/visual-builder/build/module-style-static-background-parallax.css',
			[],
			ET_CORE_VERSION
		);
	}

	/**
	 * Enqueues the background video script if it is registered and not already enqueued.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  BackgroundAssets::video_script_enqueue();
	 * ```
	 */
	public static function video_script_enqueue(): void {
		if (
			wp_script_is( self::video_script_handle(), 'registered' )
			&& ! wp_script_is( self::video_script_handle(), 'enqueued' )
		) {
			wp_enqueue_script( self::video_script_handle() );
		}
	}

	/**
	 * Get the handle for the background video script.
	 *
	 * This function returns the handle for the background video script,
	 * which is used for enqueuing the script.
	 *
	 * @since ??
	 *
	 * @return string The handle for the background video script.
	 *
	 * @example
	 * ```php
	 *  $handle = BackgroundAssets::video_script_handle();
	 * ```
	 */
	public static function video_script_handle(): string {
		return 'divi-' . self::video_script_name();
	}

	/**
	 * Get the name for the background video script.
	 *
	 * This function returns the name for the background video script,
	 * which is used for enqueuing the script.
	 *
	 * @since ??
	 *
	 * @return string The name for the background video script.
	 *
	 * @example
	 * ```php
	 *  $handle = BackgroundAssets::video_script_name();
	 * ```
	 */
	public static function video_script_name(): string {
		return 'module-script-background-video';
	}

	/**
	 * Register the background video script.
	 *
	 * This function checks if the background video script
	 * is not already registered and then registers it.
	 * The script is registered with the handle, source URL,
	 * dependencies, version, and whether it should be loaded in the header or footer.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  // Register the background video script.
	 *  BackgroundAssets::video_script_register();
	 * ```
	 */
	public static function video_script_register(): void {
		if ( ! wp_script_is( self::video_script_handle(), 'registered' ) ) {
			wp_register_script(
				self::video_script_handle(),
				ET_BUILDER_5_URI . '/visual-builder/build/' . self::video_script_name() . '.js',
				[ 'jquery' ],
				ET_CORE_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueues the background video style if it is registered and not already enqueued.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  BackgroundAssets::video_style_enqueue();
	 * ```
	 */
	public static function video_style_enqueue(): void {
		if ( wp_style_is( self::video_style_name(), 'registered' ) && ! wp_style_is( self::video_style_name(), 'enqueued' ) ) {
			wp_enqueue_style( self::video_style_name() );
		}
	}

	/**
	 * Get the name of the background video style.
	 *
	 * @since ??
	 *
	 * @return string The name of the background video style.
	 *
	 * @example:
	 * ```php
	 *  $styleName = BackgroundAssets::video_style_name();
	 * ```
	 */
	public static function video_style_name(): string {
		return 'module-style-static-background-video';
	}

	/**
	 * Register the video style for the background.
	 *
	 * This function registers the video style for the background in the Divi Builder.
	 * It is used to enqueue the necessary CSS file for the video effect.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *  BackgroundAssets::video_style_register();
	 * ```
	 */
	public static function video_style_register(): void {
		wp_register_style(
			self::video_style_name(),
			ET_BUILDER_5_URI . '/visual-builder/build/module-style-static-background-video.css',
			[],
			ET_CORE_VERSION
		);
	}
}
