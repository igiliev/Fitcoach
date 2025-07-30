<?php
/**
 * ShortcodeModule: Module.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ShortcodeModule\Module;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Script;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\FrontEnd;

/**
 * Module class.
 *
 * This class implements the `DependencyInterface` and is responsible for loading the shortcode module handling
 * module registration, enqueueing script(s), etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class Module implements DependencyInterface {

	/**
	 * Load the shortcode module.
	 *
	 * This function is responsible for loading the shortcode module by registering it and enqueuing the necessary scripts.
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/shortcode-module/src/module/';

		add_action(
			'init',
			function() use ( $module_json_folder_path ) {
				ModuleRegistration::register_module( $module_json_folder_path );
			}
		);

		Script::register(
			[
				'handle'    => 'divi-shortcode-module-script-module',
				'src'       => ET_BUILDER_5_URI . '/visual-builder/build/shortcode-module-script-module.js',
				'deps'      => [ 'jquery', 'easypiechart' ],
				'module'    => [ 'divi/shortcode-module' ],
				'version'   => ET_CORE_VERSION,
				'in_footer' => true,
			]
		);
	}

	/**
	 * Post ID for current shortcode content.
	 *
	 * @since ??
	 *
	 * @var integer
	 */
	private static $_post_id = 0;

	/**
	 * Shortcode list for current shortcode content.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_shortcode_list = [];

	/**
	 * Lazy load the currently used modules.
	 *
	 * This function is responsible for lazy loading module data by retrieving all registered
	 * shortcode module slugs and class names from the `ET_Builder_Module_Shortcode_Manager` class. These
	 * slugs and class names will be used later when currently used modules are registered. The
	 * `ET_Builder_Module_Shortcode_Manager` class is responsible for registering shortcode modules and
	 * their class names, and initializing class module instances. It is part of the D4 Dynamic Assets
	 * feature and will not be rewritten in D5 due to the different method of registering modules.
	 *
	 * @since ??
	 *
	 * @return bool Status of whether builder should load all module data or not.
	 *              Always returns `false`.
	 */
	public static function lazy_load_module_data(): bool {
		$old_modules_map = \ET_Builder_Module_Shortcode_Manager::$modules_map;
		$new_modules_map = [];

		$add_module_to_map = function( $module_list ) use ( $old_modules_map, &$new_modules_map, &$add_module_to_map ) {
			foreach ( $module_list as $module_item ) {
				if ( ! isset( $old_modules_map[ $module_item ] ) ) {
					continue;
				}

				$new_modules_map[ $module_item ] = $old_modules_map[ $module_item ];

				if ( ! empty( $old_modules_map[ $module_item ]['deps'] ) ) {
					$add_module_to_map( $old_modules_map[ $module_item ]['deps'] );
				}

				if ( ! empty( $old_modules_map[ $module_item ]['preload_deps'] ) ) {
					$add_module_to_map( $old_modules_map[ $module_item ]['preload_deps'] );
				}
			}
		};

		$add_module_to_map( self::$_shortcode_list );

		// We need to override current registered shortcode module slugs and class names
		// with the currently used modules only. So, the ET_Builder_Module_Shortcode_Manager
		// class will only initialize those selected class module instances. This class is
		// part of D4 Dynamic Assets feature and won't be rewritten in D5 due to different
		// way of registering modules.
		\ET_Builder_Module_Shortcode_Manager::$modules_map = $new_modules_map;

		unset( $old_modules_map );
		unset( $new_modules_map );
		unset( $add_module_to_map );

		return false;
	}

	/**
	 * Override post data.
	 *
	 * Overrides and temporarily sets the global variables `$wp_query` and `$post` to new values based on the specified post ID.
	 *
	 * This function uses the following globals:
	 * - @global WP_Query $wp_query          The main WP_Query object.
	 * - @global WP_Post  $post              The current post object.
	 * - @global WP_Query $original_wp_query The original WP_Query object.
	 * - @global WP_Post  $original_post     The original post object.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function override_post_data(): void {
		global $wp_query, $post, $original_wp_query, $original_post;

		$original_wp_query = $wp_query;
		$original_post     = $post;

		$post_data = get_post( self::$_post_id );
		if ( ! $post_data ) {
			return;
		}

		$post     = $post_data; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need override the post because it's empty.
		$wp_query = new \WP_Query(
			[
				'p'         => $post_data->ID,
				'post_type' => $post_data->post_type,
			]
		); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need override the WP Query because it's empty.
	}

	/**
	 * Restore the original post data and `WP query`.
	 *
	 * This function restores the global variables `$wp_query` and `$post` to their original values.
	 * It is useful to call this function after modifying the `WP query` or post data, to revert back to the original values.
	 * This function uses the following globals:
	 * - @global WP_Query $wp_query          The main `WP query` object.
	 * - @global WP_Post  $post              The current post object.
	 * - @global WP_Query $original_wp_query The original `WP query` object.
	 * - @global WP_Post  $original_post     The original post object.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * $original_wp_query = new WP_Query( $args );
	 * $original_post = $post;
	 *
	 * // Modify the WP query and post data.
	 * // ...
	 *
	 * ShortcodeModule::restore_post_data();
	 *
	 * // The WP query and post data are now restored to their original values.
	 * // ...
	 * ```
	 */
	public static function restore_post_data(): void {
		global $wp_query, $post, $original_wp_query, $original_post;

		$wp_query = $original_wp_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need override the WP Query because it's empty.
		$post     = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need override the post because it's empty.

		wp_reset_postdata();
	}

	/**
	 * Get the rendered content of a shortcode module.
	 *
	 * This function takes an array of arguments and returns the rendered content of a shortcode module.
	 * The arguments can include the content, post ID, shortcode name, and the list of shortcodes to be used.
	 * The function first checks if the shortcode module data needs to be loaded and initializes the global settings and main elements if necessary.
	 * If the `WooCommerce` plugin is active and the shortcode is a `WooCommerce` module, it loads the necessary `WooCommerce` related stuff.
	 * It then overrides the global `$wp_query` and $post variables for the shortcode module, resets the existing styles in the `ET_Builder_Element` class,
	 * sets the media queries list, and renders the shortcode module content.
	 * Finally, it applies the `divi_shortcode_module_rendered_content` filter to the rendered content and returns it.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments for getting the rendered content.
	 *
	 *     @type string $content        Optional. The content to be rendered. Default empty string.
	 *     @type int    $post_id        Optional. The post ID to be used for the shortcode module. Default `0`.
	 *     @type string $shortcode_name Optional. The name of the shortcode to be used for the shortcode module. Default empty string.
	 *     @type array  $shortcode_list Optional. The list of shortcodes to be used for the shortcode module. Default `[]`.
	 * }
	 *
	 * @return string The rendered content of the shortcode module.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'content'        => 'Lorem ipsum dolor sit amet.',
	 *         'post_id'        => 123,
	 *         'shortcode_name' => 'my_shortcode',
	 *         'shortcode_list' => [ 'shortcode1', 'shortcode2' ],
	 *     ];
	 *
	 *     $rendered_content = ShortcodeModule::get_rendered_content( $args );
	 * ```
	 * @example:
	 * ```php
	 *     $rendered_content = ShortcodeModule::get_rendered_content( [] );
	 * ```
	 */
	public static function get_rendered_content( $args = [] ): string {
		$content        = ! empty( $args['content'] ) ? $args['content'] : '';
		$shortcode_name = ! empty( $args['shortcode_name'] ) ? $args['shortcode_name'] : '';

		self::$_post_id        = ! empty( $args['post_id'] ) ? $args['post_id'] : 0;
		self::$_shortcode_list = ! empty( $args['shortcode_list'] ) ? $args['shortcode_list'] : [];

		// We need to trigger the `et_builder_ready` event for 3rd party modules to render correctly.
		if ( ! did_action( 'et_builder_ready' ) ) {
			// Shortcode module need to load module data to render the shortcode module. By
			// default, all module data will be loaded every time Builder framework is called.
			// However, loading all module data here is one of the causes of memory allocation
			// issues in our PHP tests and also burdens on the rendering process. To reduce
			// the memory usage, we have to lazy load only the currently used modules. We need
			// to ensure the Builder doesn't load all module data, then override `$modules_map`
			// in ET_Builder_Module_Shortcode_Manager with currently used modules.
			if ( self::$_shortcode_list ) {
				add_filter( 'et_builder_should_load_all_module_data', [ self::class, 'lazy_load_module_data' ] );
			}

			// Loads builder related stuff. Those two methods won't be rewritten in D5 due to
			// different way of registering modules and loading builder stuff.
			et_builder_init_global_settings();
			et_builder_add_main_elements();
		}

		// Loads WooCommerce related stuff.
		// TODO feat(D5, Woo Modules): This is only temporary solution to display existing
		// D4 Woo Modules until they have D5 support. We may need to evaluate the usage later.
		$is_wc_module = in_array( $shortcode_name, \ET_Builder_Element::get_woocommerce_modules(), true );
		if ( et_is_woocommerce_plugin_active() && $is_wc_module ) {
			WC()->frontend_includes();

			wc_load_cart();

			// Some Woo module default values rely on current post to define the value. Hence
			// we need to set post for current single product page before module definition is
			// processed.
			if ( 'product' === get_post_type( self::$_post_id ) ) {
				self::override_post_data();
			}

			// Disable add to cart button for all Woo modules that use loop item template.
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		}

		// Override global $wp_query and $post, then restore it later.
		if ( $shortcode_name ) {
			add_action( "et_module_before_render_output_{$shortcode_name}", [ self::class, 'override_post_data' ] );
			add_action( "et_module_after_render_output_{$shortcode_name}", [ self::class, 'restore_post_data' ] );
		}

		// Reset existing styles in `ET_Builder_Element` class to ensure the module styles
		// don't stack on the next shortcode module. This method won't be rewritten in D5
		// due to different way of generating the module styles.
		\ET_Builder_Element::reset_styles();

		// Set media queries list to generate CSS media query and their styles. This method
		// won't be rewritten in D5 due to different way of generating the module styles.
		\ET_Builder_Element::set_media_queries();

		// Render shortcode module content.
		$rendered_content = do_shortcode( $content );

		/*
		 * TODO fix(D5, Static CSS): D5 Shortcode Module should use Static Cache for Styles output
		 *  See: https://github.com/elegantthemes/Divi/issues/39691
		 */
		// Render shortcode module styles if any. This method won't be rewritten in D5 due
		// to different way of generating the module styles.
		$rendered_styles = trim( \ET_Builder_Element::get_style() );
		if ( ! empty( $rendered_styles ) ) {
			$rendered_content .= '<style type="text/css">' . et_core_esc_previously( $rendered_styles ) . '</style>';
		}

		/**
		 * Filter Shortcode module rendered content.
		 *
		 * @since ??
		 *
		 * @param string $rendered_content Rendered content along with the module styles if any.
		 * @param array  $args             Argument passed from the REST API callback.
		 */
		return apply_filters( 'divi_shortcode_module_rendered_content', $rendered_content, $args );
	}
}
