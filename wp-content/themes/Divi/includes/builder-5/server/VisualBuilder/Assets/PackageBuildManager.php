<?php
/**
 * PackageBuildManager class.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\TextTransform;
use ET\Builder\VisualBuilder\Assets\DiviPackageBuild;
use ET\Builder\VisualBuilder\Assets\PackageBuild;


/**
 * Class for handling package builds: registering and populating it to be
 * enqueued in top or app window of the visual builder.
 *
 * @since ??
 */
class PackageBuildManager implements DependencyInterface {
	/**
	 * Registry of registered package builds.
	 *
	 * @var array
	 */
	private static $_package_builds = [];

	/**
	 * Package builds' scripts and styles that are registered to be enqueued on top window.
	 *
	 * @var array
	 */
	private static $_top_window = [
		'scripts' => [],
		'styles'  => [],
	];

	/**
	 * Package builds' scripts and styles that are registered to be enqueued on app window.
	 *
	 * @var array
	 */
	private static $_app_window = [
		'scripts' => [],
		'styles'  => [],
	];

	/**
	 * Defer specific stylesheets.
	 *
	 * @var array
	 */
	private static $_deferred_styles = [];


	/**
	 * Method that is automatically loaded by class which implements `DependencyInterface`
	 *
	 * @since ??
	 */
	public function load() {
		add_action( 'et_fb_framework_loaded', [ $this, 'register_divi_package_builds' ] );
	}

	/**
	 * Register divi package builds.
	 *
	 * @since ??
	 */
	public static function register_divi_package_builds() {
		self::register_divi_package_build(
			[
				'name'   => 'divi-app-frame',
				'script' => [
					'data_top_window'    => [
						'iframeSrc' => ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) ?
							( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( $_SERVER['HTTP_HOST'] )
							. sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '',
					],
					'enqueue_app_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-app-preferences',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-app-ui',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-clipboard',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-cloud-app',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => true,
					'defer'              => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-colors',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-constant-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-context-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-conversion',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-data',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		// divi/debug package should not be enqueued in production / by default. It should only be enqueued
		// intentionally for debugging purpose on development.
		$is_debug_store_state = et_get_experiment_flag( 'storeStateModalOnAppLoad' );

		if ( $is_debug_store_state ) {
			self::register_divi_package_build(
				[
					'name'   => 'divi-debug',
					'script' => [
						'enqueue_top_window' => false,
					],
				]
			);
		}

		self::register_divi_package_build(
			[
				'name'   => 'divi-divider-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-draggable',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => true,
					'defer'              => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-dynamic-data',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-edit-post',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-email-marketing',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-error-boundary',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-events',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-field-library',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);
		self::register_divi_package_build(
			[
				'name'   => 'divi-fonts',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-global-data',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-global-layouts',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-help',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-history',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => true,
					'defer'              => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-hooks',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-icon-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => true,
					'defer'              => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-keyboard-shortcuts',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-mask-and-pattern-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-middleware',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-modal-library',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-modal',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-module-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
					'defer'              => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-module-utils',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
					'defer'              => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-module',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-numbers',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-object-renderer',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-page-settings',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-rest',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-right-click-options',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-root',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-sanitize',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-script-library',
				'script' => [
					'deps'               => [],
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-seamless-immutable-extension',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-serialized-post',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-settings',
				'script' => [
					'data_app_window'    => isset( $_GET['app_window'] ) ? AssetsUtility::get_settings_data() : [],
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-shortcode-module',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'defer' => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-spam-protection',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-style-library',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-tooltip',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'defer' => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-ui-library',
				'script' => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-url',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'divi-window',
				'script' => [
					'enqueue_top_window' => false,
				],
				'style'  => [
					'enqueue_top_window' => false,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'visual-builder',
				'script' => [
					'deps'               => [
						'iris',
						'wp-color-picker',
						'wp-color-picker-alpha',
						'wp-mediaelement',
						'react-tiny-mce',
						'jquery',
						'lodash',
					],
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
				],
			]
		);

		self::register_divi_package_build(
			[
				'name'   => 'visual-builder-loader',
				'script' => [
					'deps'               => [
						/**
						 * Fix(D5, Dependencies): Essentially every 3rd party dependency listed below should eventually be removed and
						 * moved to Divi's externals. This allows us to have full control over Divi's dependencies, increasing
						 * stability and reliability of the Visual Builder on future versions of WordPress.
						 */
						'iris',
						'wp-color-picker',
						'wp-color-picker-alpha',
						'wp-mediaelement',
						'react-tiny-mce',
						'jquery',
						'lodash',
					],
					'enqueue_top_window' => true,
					'enqueue_app_window' => false,
				],
			]
		);
	}

	/**
	 * Register divi package build.
	 *
	 * @since ??
	 *
	 * @param array $params Package build's params.
	 */
	public static function register_package_build( $params ) {
		// Create package build instance.
		$package_build = new PackageBuild( $params );

		// Get generated properties.
		$package_build_properties = $package_build->get_properties();

		self::register( $package_build_properties );
	}

	/**
	 * Register divi package build.
	 *
	 * @since ??
	 *
	 * @param array $params Package build's params.
	 */
	public static function register_divi_package_build( $params ) {
		// Create package build instance.
		$package_build = new DiviPackageBuild( $params );

		// Get generated properties.
		$package_build_properties = $package_build->get_properties();

		self::register( $package_build_properties );
	}

	/**
	 * Register package build item.
	 *
	 * @since ??
	 *
	 * @param array $properties package build's properties.
	 */
	public static function register( $properties ) {
		if ( '' === $properties['name'] ?? '' ) {
			return;
		}

		// Package build name.
		$name = $properties['name'];

		// Registered package build to the registry.
		self::$_package_builds[ $name ] = $properties;

		// Register top window's scripts.
		if ( $properties['script']['enqueue_top_window'] ) {
			self::$_top_window['scripts'][ $name ] = [
				'name'    => $name,
				'src'     => $properties['script']['src'],
				'deps'    => $properties['script']['deps'],
				'version' => $properties['version'],
				'args'    => $properties['script']['args'],
				'data'    => $properties['script']['data_top_window'],
			];
		}

		// Register app window's scripts.
		if ( $properties['script']['enqueue_app_window'] ) {
			self::$_app_window['scripts'][ $name ] = [
				'name'    => $name,
				'src'     => $properties['script']['src'],
				'deps'    => $properties['script']['deps'],
				'version' => $properties['version'],
				'args'    => $properties['script']['args'],
				'data'    => $properties['script']['data_app_window'],
			];
		}

		// Register top window's styles.
		if ( $properties['style']['enqueue_top_window'] ) {
			self::$_top_window['styles'][ $name ] = [
				'name'    => $name,
				'src'     => $properties['style']['src'],
				'deps'    => $properties['style']['deps'],
				'version' => $properties['version'],
				'args'    => $properties['style']['args'],
				'media'   => $properties['style']['media'],
				'defer'   => $properties['style']['defer'],

			];
		}

		// Register app window's styles.
		if ( $properties['style']['enqueue_app_window'] ) {
			self::$_app_window['styles'][ $name ] = [
				'name'    => $name,
				'src'     => $properties['style']['src'],
				'deps'    => $properties['style']['deps'],
				'version' => $properties['version'],
				'args'    => $properties['style']['args'],
				'media'   => $properties['style']['media'],
				'defer'   => $properties['style']['defer'],
			];
		}
	}

	/**
	 * Enqueue styles.
	 *
	 * @since ??
	 */
	public static function enqueue_styles() {
		$is_top_window = Conditions::is_vb_top_window() || Conditions::is_tb_admin_screen();
		$window_prefix = $is_top_window ? '_top_window' : '_app_window';

		/**
		 * Previously both scripts and styles are enqueued at the same time hence the `package` hook name.
		 * Now Visual Builder has more precision on what's to enqueue because there are valid case where
		 * the script is not enqueued but the style is enqueued.
		 *
		 * TODO feat(D5, Deprecation): Remove this deprecated hook in recent releases, maximum public-beta-0.
		 */
		do_action_deprecated(
			'divi_visual_builder_assets_before_enqueue_packages',
			[],
			'5.0.0-public-alpha.0',
			'divi_visual_builder_assets_before_enqueue_styles'
		);

		do_action( 'divi_visual_builder_assets_before_enqueue_styles' );
		do_action( "divi_visual_builder_assets_before_enqueue{$window_prefix}_styles" );

		$assets = $is_top_window ? self::$_top_window : self::$_app_window;
		$styles = $assets['styles'];

		foreach ( $styles as $style ) {
			if ( isset( $style['defer'] ) && $style['defer'] ) {
				self::$_deferred_styles[] = $style['name'];
			}
			wp_enqueue_style( $style['name'], $style['src'], $style['deps'], $style['version'], $style['media'] );
		}
		add_filter( 'style_loader_tag', [ self::class, 'defer_styles' ], 10, 2 );

		/**
		 * TODO feat(D5, Deprecation): Remove this deprecated hook in recent releases, maximum public-beta-0.
		 */
		do_action_deprecated(
			'divi_visual_builder_assets_after_enqueue_packages',
			[],
			'5.0.0-public-alpha-0',
			'divi_visual_builder_assets_after_enqueue_styles'
		);

		do_action( 'divi_visual_builder_assets_after_enqueue_styles' );
		do_action( "divi_visual_builder_assets_after_enqueue{$window_prefix}_styles" );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since ??
	 */
	public static function enqueue_scripts() {
		$is_top_window = Conditions::is_vb_top_window() || Conditions::is_tb_admin_screen();
		$window_prefix = $is_top_window ? '_top_window' : '_app_window';

		do_action( 'divi_visual_builder_assets_before_enqueue_scripts' );
		do_action( "divi_visual_builder_assets_before_enqueue{$window_prefix}_scripts" );

		$assets  = $is_top_window ? self::$_top_window : self::$_app_window;
		$scripts = $assets['scripts'];

		foreach ( $scripts as $script ) {
			wp_enqueue_script( $script['name'], $script['src'], $script['deps'], $script['version'], $script['args'] );

			// Pass feature flag for the script if proper constant is configured.
			if ( et_get_experiment_flag( 'storeStateModalOnAppLoad' ) ) {

				// List of packages' script that has store. Right now store state modal is only outputs divi/app-ui, divi/rest,
				// and divi/settings' store state values. Eventually, more store state values can be outputted.
				$store_package_script_name = [
					// The following store state is outputted on store state modal.
					'divi-app-ui',
					'divi-edit-post',
					'divi-rest',
					'divi-settings',

					// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- intentionally disable because these haven't been used.
					// 'divi-app-preference',
					// 'divi-clipboard',
					// 'divi-colors',
					// 'divi-email-marketing',
					// 'divi-events',
					// 'divi-fonts',
					// 'divi-global-data',
					// 'divi-global-layouts',
					// 'divi-help',
					// 'divi-history',
					// 'divi-keyboard-shortcuts',
					// 'divi-modal-library',
					// 'divi-module',
					// 'divi-module-library',
					// 'divi-page-settings',
					// 'divi-right-click-options',
					// 'divi-serialized-post',
					// 'divi-shortcode-module',
					// 'divi-spam-protection',

					// `divi/debug` doesn't have store, but it needs the feature flag.
					'divi-debug',
				];

				// Add feature flag for the store state modal.
				if ( in_array( $script['name'], $store_package_script_name, true ) ) {
					if ( is_array( $script['data']['debug'] ?? false ) ) {
						$script['data']['debug']['storeStateModal'] = true;
					} else {
						$script['data']['debug'] = [
							'storeStateModal' => true,
						];
					}
				}
			}

			if ( ! empty( $script['data'] ) ) {
				wp_localize_script(
					$script['name'],
					TextTransform::pascal_case( $script['name'] . 'Data' ),
					$script['data']
				);
			}
		}

		do_action( 'divi_visual_builder_assets_after_enqueue_scripts' );
		do_action( "divi_visual_builder_assets_after_enqueue{$window_prefix}_scripts" );
	}
	/**
	 * Load specific stylesheets asynchronously by swapping the media attribute on load. This for stylesheets that not required to be loaded immediately.
	 *
	 * @since ??
	 *
	 * @param string $html HTML to replace.
	 * @param string $handle Stylesheet handle.
	 * @return string $html replacement html.
	 */
	public static function defer_styles( $html, $handle ) {
		if ( in_array( $handle, self::$_deferred_styles, true ) ) {
			return str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
		}
		return $html;
	}
}
