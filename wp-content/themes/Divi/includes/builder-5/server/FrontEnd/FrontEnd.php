<?php
/**
 * FrontEnd Class.
 *
 * This class is responsible for loading all the necessary functionality on the frontend.
 * It accepts a DependencyTree on construction, which specifies the dependencies and their priorities for loading.
 *
 * @package Builder\FrontEnd
 *
 * @since ??
 */

namespace ET\Builder\FrontEnd;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\DependencyManagement\DependencyTree;
use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\FrontEnd\Module\Script;
use ET\Builder\FrontEnd\Module\Fonts;
use ET\Builder\FrontEnd\Assets\CriticalCSS;
use ET\Builder\FrontEnd\Assets\DynamicAssets;
use ET\Builder\FrontEnd\Assets\StaticCSS;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewAssets;
use ET\Builder\Packages\Module\Options\Background\BackgroundAssets;
use ET\Builder\Packages\WooCommerce\WooCommerceHooks;

/**
 * FrontEnd Class.
 *
 * This class is responsible for loading all the necessary functionality on the frontend. It accepts
 * a DependencyTree on construction, specifying the dependencies and their priorities for loading.
 *
 * @since ??
 *
 * @param DependencyTree $dependencyTree The dependency tree instance specifying the dependencies and priorities.
 */
class FrontEnd {

	/**
	 * Stores the dependencies that were passed to the constructor.
	 *
	 * This property holds an instance of the DependencyTree class that represents the dependencies
	 * passed to the constructor of the current object.
	 *
	 * @since ??
	 *
	 * @var DependencyTree $dependencies An instance of DependencyTree representing the dependencies.
	 */
	private $_dependency_tree;

	/**
	 * Constructs a new instance of the `FrontEnd` class and sets its dependencies.
	 *
	 * @param DependencyTree $dependency_tree The dependency tree for the `FrontEnd` class to load.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $dependency_tree = new DependencyTree();
	 * $front_end = new FrontEnd($dependency_tree);
	 * ```
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}

	/**
	 * Loads and initializes the Frontend for the application.
	 *
	 * This function ensures the proper setup and configuration required for the Frontend to
	 * function correctly. This includes loading of resources, setting up routes, and initializing
	 * necessary classes or traits.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( ! Conditions::is_d5_enabled() ) {
			return;
		}

		if ( ! Conditions::is_vb_enabled() ) {
			// Some 3P plugins will break if DiviExtensions class is not loaded this early.
			$this->maybe_init_divi_4_exensions();
			// Check again later because D4-only hooks might be registered after this point.
			add_action( 'et_head_meta', [ $this, 'maybe_init_divi_4_exensions' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'register_fe_styles' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'register_fe_scripts' ] );

			// The priority needs to be 11 so that Dynamic Assets run first.
			add_action( 'wp_footer', [ $this, 'enqueue_footer_script_data' ], 11 );
			add_action( 'wp_footer', [ $this, 'enqueue_footer_fonts' ] );
			add_action( 'wp_footer', [ $this, 'maybe_enqueue_global_colors_style' ] );
			add_action( 'wp_footer', [ $this, 'enqueue_global_numeric_and_fonts_vars' ] );
			add_action( 'show_admin_bar', [ $this, 'preview_hide_admin_bar' ], 10, 1 );
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'register_global_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'override_d4_fe_scripts' ], 100 );

		$this->_dependency_tree->load_dependencies();

		/**
		 * Fires after frontend initialization is complete.
		 *
		 * This action hook runs after all frontend dependencies and functionality have been initialized,
		 * including styles, scripts, and core components. It executes both on the frontend and in the
		 * Visual Builder context, allowing modules and extensions to perform additional setup or
		 * registration once the frontend system is ready.
		 *
		 * @since ??
		 */
		do_action( 'divi_frontend_initialize' );
	}

	/**
	 * Register global scripts.
	 *
	 * Some scripts need to be registered on the entire site.
	 * Global scripts are used by the build and the Divi Theme.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function register_global_scripts() {
		wp_enqueue_script(
			'divi-script-library-global-functions',
			ET_BUILDER_5_URI . '/visual-builder/build/script-library-frontend-global-functions.js',
			[
				'jquery',
			],
			ET_CORE_VERSION,
			true
		);
		wp_enqueue_script(
			'divi-script-library-ext-waypoint',
			ET_BUILDER_5_URI . '/visual-builder/build/script-library-ext-waypoint.js',
			[],
			ET_CORE_VERSION,
			true
		);
	}

	/**
	 * Register frontend styles.
	 *
	 * This function is responsible for registering the styles used in the frontend of the
	 * application. It should be called during the plugin initialization to ensure that the styles
	 * are enqueued properly.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function register_fe_styles(): void {
		BackgroundAssets::parallax_style_register();
		BackgroundAssets::video_style_register();
	}

	/**
	 * Get frontend scripts list.
	 *
	 * Retrieves an array of scripts that are used in the frontend.
	 *
	 * @since ??
	 *
	 * @return array An array of script handles that are used in the frontend.
	 */
	public function get_fe_scripts(): array {
		return [
			'script-library'                         => [
				'handle'                  => 'divi-script-library',
				'additional-dependencies' => [],
			],
			BackgroundAssets::parallax_script_name() => [
				'handle'                  => BackgroundAssets::parallax_script_handle(),
				// Technically it'd be more accurate to set `divi-script-library-window-event-emitter` as dependency
				// because `WindowEventEmitter` is the part that is needed. However the exported function of
				// `divi-script-library-window-event-emitter` is `window.divi.scriptLibrary.scriptLibraryWindowEventEmitter`
				// instead of `window.divi.scriptLibrary.WindowEventEmitter` so right now we're still using `divi-script-library`.
				'additional-dependencies' => [ 'divi-script-library' ],
			],
			BackgroundAssets::video_script_name()    => [
				'handle'                  => BackgroundAssets::video_script_handle(),
				// Technically it'd be more accurate to set `divi-script-library-window-event-emitter` as dependency
				// because `WindowEventEmitter` is the part that is needed. However the exported function of
				// `divi-script-library-window-event-emitter` is `window.divi.scriptLibrary.scriptLibraryWindowEventEmitter`
				// instead of `window.divi.scriptLibrary.WindowEventEmitter` so right now we're still using `divi-script-library`.
				'additional-dependencies' => [ 'wp-mediaelement', 'divi-script-library' ],
			],
			MultiViewAssets::script_name()           => [
				'handle'                  => MultiViewAssets::script_handle(),
				'additional-dependencies' => [ 'jquery' ],
			],
		];
	}

	/**
	 * Registers frontend scripts.
	 *
	 * This function is used to register scripts that will be loaded on the front-end of the website.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function register_fe_scripts(): void {
		$scripts = self::get_fe_scripts();

		foreach ( $scripts as $script_name => $script ) {
			// Front-End scripts handle.
			$handle   = $script['handle'];
			$register = true;

			/**
			 * Filter to determine whether to register Front-End scripts or not.
			 *
			 * @since ??
			 *
			 * @param bool   $register Whether to register Front-End scripts.
			 * @param string $handle   Front-End scripts handle.
			 */
			$should_register_front_end_scripts = apply_filters( 'divi_front_end_register_scripts_enable', $register, $handle );

			if ( ! $should_register_front_end_scripts ) {
				continue;
			}

			$asset_data = [];
			$version    = Conditions::is_debug_mode() ? ( $asset_data['version'] ?? ET_CORE_VERSION ) : ET_CORE_VERSION;

			$dependencies = array_merge( $asset_data['dependencies'] ?? [], $script['additional-dependencies'] );
			/**
			 * Filters Front-End scripts dependencies.
			 *
			 * @since ??
			 *
			 * @param array  $dependencies Front-End scripts dependencies.
			 * @param string $handle       Front-End script handle.
			 */
			$script_dependencies = apply_filters( 'divi_front_end_register_scripts_dependencies', $dependencies, $handle );

			wp_register_script(
				$script['handle'],
				untrailingslashit( ET_BUILDER_5_URI ) . '/visual-builder/build/' . $script_name . '.js',
				$script_dependencies,
				$version,
				true
			);
		}

		foreach ( Script::get_all() as $handle => $script ) {
			$register = true;

			/**
			 * Filter to determine whether to register module script.
			 *
			 * @since ??
			 *
			 * @param bool   $register Whether to register module script.
			 * @param string $handle   Module script handle.
			 */
			$register = apply_filters( 'divi_front_end_register_module_script', $register, $handle );

			if ( ! $register ) {
				continue;
			}

			// Only register script when the module is used / saved in the frontend page if the script has 'module' property.
			// The complete expectation when 'module' property exist:
			// 1. D5 is enabled.
			// 2. Visual Builder is not enabled.
			// 3. The page content does not have any D4 shortcode.
			// 4. The page content has the saved block.
			if ( ! empty( $script['module'] ) ) {
				// Skip if Divi 5 is not enabled.
				if ( ! Conditions::is_d5_enabled() ) {
					continue;
				}

				// Skip if current page is visual builder.
				if ( Conditions::is_vb_enabled() ) {
					continue;
				}

				// Skip if current page content has no saved D5 format module (serialized block).
				// TODO feat(D5, Conditions) :: maybe create util function to check if current page has saved D5 format module.
				// right now it simply assume that if current page has saved D5 format, it'll wrapped inside section at least.
				if ( ! has_block( 'divi/section' ) ) {
					continue;
				}

				// The initial assumption is current page has no passed block.
				$has_block = false;

				foreach ( $script['module'] as $module_name ) {
					if ( $has_block ) {
						break;
					}

					$has_block = has_block( $module_name );
				}

				if ( ! $has_block ) {
					continue;
				}
			}

			$dependencies = $script['deps'];
			/**
			 * Filters Front-End scripts dependencies.
			 *
			 * @since ??
			 *
			 * @param array  $dependencies Front-End scripts dependencies.
			 * @param string $handle       Front-End script handle.
			 */
			$script_dependencies = apply_filters( 'divi_front_end_register_scripts_dependencies', $dependencies, $handle );

			wp_register_script(
				$handle,
				$script['src'],
				$script_dependencies,
				$script['ver'],
				$script['in_footer']
			);

			$is_enqueue = $script['is_enqueue'] ?? false;

			if ( $is_enqueue ) {
				wp_enqueue_script( $handle );
			}
		}
	}

	/**
	 * Override the D4 FE scripts.
	 *
	 * This function is responsible for overriding the D4 FE (Front-end) scripts. It can be used to modify or extend
	 * the existing functionality of the D4 FE scripts.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function override_d4_fe_scripts(): void {
		$is_fe_or_vb_app_window = ! Conditions::is_vb_enabled() || Conditions::is_vb_app_window();

		// Bail, if the current window is neither FE nor a visual builder app window.
		if ( ! $is_fe_or_vb_app_window ) {
			return;
		}

		$current_page_id = get_the_ID();
		/**
		 * Filter to determine if the current post/page is being A-B tested.
		 *
		 * @since 4.x
		 * @deprecated 5.0.0 Use `divi_front_end_is_ab_testing_active_post_id` hook instead.
		 *
		 * @param int $current_page_id The current page ID.
		 */
		$current_page_id = apply_filters(
			'et_is_ab_testing_active_post_id',
			$current_page_id
		);

		/**
		 * Filter to determine if the current post/page is being A-B tested.
		 *
		 * @since ??
		 *
		 * @param int $current_page_id The current page ID.
		 */
		$current_page_id = apply_filters( 'divi_front_end_is_ab_testing_active_post_id', $current_page_id );

		$ab_tests      = function_exists( 'et_builder_ab_get_current_tests' ) ? et_builder_ab_get_current_tests() : array();
		$is_ab_testing = ! empty( $ab_tests );

		// Enqueues et_get_combined_script_handle's deps.
		DynamicAssetsUtils::enqueue_combined_script();

		$widget_search_selector = '.widget_search';
		/**
		 * Filter to add the CSS class selector for widget search.
		 *
		 * @since 4.x
		 * @deprecated 5.0.0 Use `divi_front_end_widget_search_selector` hook instead.
		 *
		 * @param string $widget_search_selector The widget search selector class.
		 */
		$widget_search_selector = apply_filters(
			'et_pb_widget_search_selector',
			$widget_search_selector
		);

		/**
		 * Filter to add the CSS class selector for widget search.
		 *
		 * @since ??
		 *
		 * @param string $widget_search_selector The widget search selector class.
		 */
		$widget_search_selector = apply_filters( 'divi_front_end_widget_search_selector', $widget_search_selector );

		// Type cast for filter hook.
		$options = [];

		/**
		 * Filters Waypoints options for client side rendering.
		 *
		 * This is for backward compatibility with hooks written for Divi version <5.0.0.
		 *
		 * @since 4.15.0
		 * @deprecated 5.0.0 Use `divi_front_end_waypoints_options` hook instead.
		 *
		 * @param array $options {
		 *     Filtered Waypoints options.Only support `context` at this moment because
		 *     there is no test case for other properties.
		 *
		 *     @type string[] $context List of container selectors for the Waypoint. The
		 *                             element will iterate and looking for the closest
		 *                             parent element matches the given selectors.
		 * }
		 */
		$options = apply_filters(
			'et_builder_waypoints_options',
			$options
		);

		/**
		 * Filters Waypoints options for client side rendering.
		 *
		 * @since ??
		 *
		 * @param array $options {
		 *     Filtered Waypoints options. Only support `context` at this moment because
		 *     there is no test case for other properties.
		 *
		 *     @type string[] $context List of container selectors for the Waypoint. The
		 *                             element will iterate and looking for the closest
		 *                             parent element matches the given selectors.
		 * }
		 */
		$waypoints_options = apply_filters( 'divi_front_end_waypoints_options', $options );

		$pb_custom_data = array(
			'ajaxurl'                => is_ssl() ? admin_url( 'admin-ajax.php' ) : admin_url( 'admin-ajax.php', 'http' ),
			'images_uri'             => get_template_directory_uri() . '/images',
			'builder_images_uri'     => ET_BUILDER_5_URI . '/images',
			'et_frontend_nonce'      => wp_create_nonce( 'et_frontend_nonce' ),
			'subscription_failed'    => esc_html__( 'Please, check the fields below to make sure you entered the correct information.', 'et_builder' ),
			'et_ab_log_nonce'        => wp_create_nonce( 'et_ab_testing_log_nonce' ),
			'fill_message'           => esc_html__( 'Please, fill in the following fields:', 'et_builder' ),
			'contact_error_message'  => esc_html__( 'Please, fix the following errors:', 'et_builder' ),
			'invalid'                => esc_html__( 'Invalid email', 'et_builder' ),
			'captcha'                => esc_html__( 'Captcha', 'et_builder' ),
			'prev'                   => esc_html__( 'Prev', 'et_builder' ),
			'previous'               => esc_html__( 'Previous', 'et_builder' ),
			'next'                   => esc_html__( 'Next', 'et_builder' ),
			'wrong_captcha'          => esc_html__( 'You entered the wrong number in captcha.', 'et_builder' ),
			'wrong_checkbox'         => esc_html__( 'Checkbox', 'et_builder' ),
			'ignore_waypoints'       => et_is_ignore_waypoints() ? 'yes' : 'no',
			'is_divi_theme_used'     => function_exists( 'et_divi_fonts_url' ),
			'widget_search_selector' => $widget_search_selector,
			'ab_tests'               => $ab_tests,
			'is_ab_testing_active'   => $is_ab_testing,
			'page_id'                => $current_page_id,
			'unique_test_id'         => get_post_meta( $current_page_id, '_et_pb_ab_testing_id', true ),
			'ab_bounce_rate'         => '' !== get_post_meta( $current_page_id, '_et_pb_ab_bounce_rate_limit', true ) ? get_post_meta( $current_page_id, '_et_pb_ab_bounce_rate_limit', true ) : 5,
			'is_cache_plugin_active' => false === et_pb_detect_cache_plugins() ? 'no' : 'yes',
			'is_shortcode_tracking'  => get_post_meta( $current_page_id, '_et_pb_enable_shortcode_tracking', true ),
			'tinymce_uri'            => defined( 'ET_FB_ASSETS_URI' ) ? ET_FB_ASSETS_URI . '/vendors' : '',
			'accent_color'           => et_builder_accent_color(),
			'waypoints_options'      => $waypoints_options,
		);

		wp_localize_script( et_get_combined_script_handle(), 'et_pb_custom', $pb_custom_data );

		wp_localize_script(
			et_get_combined_script_handle(),
			'et_frontend_scripts',
			[
				'builderCssContainerPrefix' => ET_BUILDER_CSS_CONTAINER_PREFIX,
				'builderCssLayoutPrefix'    => ET_BUILDER_CSS_LAYOUT_PREFIX,
			]
		);

		wp_localize_script(
			et_get_combined_script_handle(),
			'et_builder_utils_params',
			array(
				'condition'              => array(
					'diviTheme'  => function_exists( 'et_divi_fonts_url' ),
					'extraTheme' => function_exists( 'et_extra_fonts_url' ),
				),
				'scrollLocations'        => et_builder_get_window_scroll_locations(),
				'builderScrollLocations' => et_builder_get_onload_scroll_locations(),
				'onloadScrollLocation'   => et_builder_get_onload_scroll_location(),
				'builderType'            => et_builder_get_current_builder_type(),
			)
		);
	}

	/**
	 * Enqueue script data at footer
	 *
	 * This function is used to enqueue script data in the footer of the HTML document. It is typically used to load
	 * JavaScript files that need to be executed after the main content has been loaded.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function enqueue_footer_script_data(): void {
		// No need to enqueue Front End script data in Visual Builder's top window because no FE element is being
		// rendered on VB top window. It is better to keep it as light as possible.
		if ( Conditions::is_vb_top_window() ) {
			return;
		}

		// Set script data for breakpoint.
		Breakpoint::set_script_data();

		// TODO feat(Script Data, Refactoring) - Refactor script so all of the script uses the same script data.

		ScriptData::enqueue_data( 'breakpoint' );
		ScriptData::enqueue_data( 'background_parallax' );
		ScriptData::enqueue_data( 'background_video' );
		ScriptData::enqueue_data( 'multi_view' );
	}

	/**
	 * Enqueue fonts in footer.
	 *
	 * This function enqueues fonts in the footer of the webpage. It can be used to add custom fonts
	 * or external font libraries to the webpage. The function does not return any value.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function enqueue_footer_fonts(): void {
		Fonts::enqueue();
	}

	/**
	 * Enqueue global data style when Dynamic Assets are disabled.
	 *
	 * This function is responsible for enqueuing the necessary global colors styles for the
	 * front end. It is called during the initialization of the FrontEnd class.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function maybe_enqueue_global_colors_style(): void {
		if ( ! DynamicAssetsUtils::use_dynamic_assets() && ! Conditions::is_vb_enabled() ) {
			// Get all the global colors CSS variable.
			$global_colors_style = Style::get_global_colors_style();

			if ( ! empty( $global_colors_style ) ) {
				echo '<style class="et-vb-global-data et-vb-global-colors">';
				echo et_core_esc_previously( $global_colors_style );
				echo '</style>';
			}
		}
	}

	/**
	 * Preview hide admin bar.
	 *
	 * This function is used to hide the admin bar when previewing a layout in the Divi Builder.
	 *
	 * @since ??
	 *
	 * @param boolean $return The current value of the admin bar.
	 *
	 * @return boolean The new value of the admin bar.
	 */
	public function preview_hide_admin_bar( bool $return ): bool {
		if ( isset( $_GET['preview_id'] ) && isset( $_GET['preview_nonce'] ) ) {
			$id        = (int) $_GET['preview_id'];
			$post_type = get_post_type( $id );

			if ( 'et_pb_layout' === $post_type && wp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . $id ) ) { // phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- wp_verify_nonce() function does sanitation.
				return false;
			}
		}
		return $return;
	}

	/**
	 * Enqueue global numeric variables.
	 *
	 * This function outputs global numeric CSS variables as inline styles when the Visual Builder is not enabled.
	 *
	 * @since ??
	 */
	public function enqueue_global_numeric_and_fonts_vars(): void {
		if ( ! Conditions::is_vb_enabled() && ! Conditions::is_admin_request() ) {
			// Get all the global colors CSS variable.
			$global_vars_style = Style::get_global_numeric_and_fonts_vars_style();

			if ( ! empty( $global_vars_style ) ) {
				echo '<style class="et-vb-global-data et-vb-global-numeric-vars">';
				echo et_core_esc_previously( $global_vars_style );
				echo '</style>';
			}
		}
	}

	/**
	 * Conditionally load Divi extension class.
	 *
	 * This function loads D4 API Class when D4-only extensions are detected, if we don't do it
	 * this early, some extensions will fail to enqueue scripts/styles.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function maybe_init_divi_4_exensions(): void {
		// Need to manually require this because d5-readiness doesn't run include() on frontend.
		require_once get_template_directory() . '/d5-readiness/server/Checks/PluginHooksCheck.php';

		if ( Conditions::has_divi_4_only_extension() ) {
			require_once get_template_directory() . '/includes/builder/api/DiviExtensions.php';
		}
	}
}

$dependency_tree = new DependencyTree();

$dependency_tree->add_dependency( new CriticalCSS() );
$dependency_tree->add_dependency( new DynamicAssets() );
$dependency_tree->add_dependency( new StaticCSS() );
$dependency_tree->add_dependency( new WooCommerceHooks() );

$frontend = new FrontEnd( $dependency_tree );

$frontend->initialize();
