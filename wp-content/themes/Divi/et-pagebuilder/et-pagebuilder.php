<?php

define( 'ET_BUILDER_THEME', true );

/**
 * Check whether shortcode framework should be loaded.
 */
function et_should_load_shortcode_framework() {
	static $divi_options = null;
	// Default to false, don't load shortcode framework.
	// The shortcode framework will lazy load if needed.
	$load_shortcode_framework = false;

	// Get Divi options.
	if ( null === $divi_options ) {
		$divi_options = get_option( 'et_divi' );
	}

	// If the `et_force_enable_shortcode_framework` option exists, then use it.
	if ( isset( $divi_options['et_force_enable_shortcode_framework'] ) ) {
		$load_shortcode_framework = 'on' === $divi_options['et_force_enable_shortcode_framework'];
	}

	// If the `ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK` constant exists, then use it.
	if ( defined( 'ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK' ) ) {
		$load_shortcode_framework = ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK;
	}

	$load_shortcode_framework_before = $load_shortcode_framework;

	/**
	 * Filter whether to load Divi shortcode framework.
	 *
	 * @since ??
	 *
	 * @param bool $load_shortcode_framework Whether to load Divi shortcode framework. Default is true.
	 */
	$load_shortcode_framework = apply_filters( 'et_should_load_shortcode_framework', $load_shortcode_framework );

	define( 'ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK_CHANGED_VIA_HOOK', $load_shortcode_framework !== $load_shortcode_framework_before );

	return $load_shortcode_framework;
}

/**
 * Load D4 shortcode framework for certain ajax actions.
 */
function et_load_shortcode_framework_for_ajax_action() {
	$should_load_shortcode_framework = false;

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// load for these actions
		$load_for_ajax_actions = [
			'et_pb_add_new_layout',
		];

		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $load_for_ajax_actions, true ) ) {
			$should_load_shortcode_framework = true;
		}
	}

	if ( $should_load_shortcode_framework ) {
		add_filter( 'et_should_load_shortcode_framework', '__return_true' );
	}
}
add_action( 'init', 'et_load_shortcode_framework_for_ajax_action', -1 );

/**
 * Initialize the Divi Builder.
 */
function et_setup_builder() {
	define( 'ET_BUILDER_DIR', get_template_directory() . '/includes/builder/' );
	define( 'ET_BUILDER_URI', get_template_directory_uri() . '/includes/builder' );
	define( 'ET_BUILDER_LAYOUT_POST_TYPE', 'et_pb_layout' );

	$theme_version = et_get_theme_version();
	define( 'ET_BUILDER_VERSION', $theme_version );

	load_theme_textdomain( 'et_builder', ET_BUILDER_DIR . 'languages' );

	// Load the builder's constants file.
	require_once ET_BUILDER_DIR . 'constants.php';

	// This is the base framework file that is always required.
	require_once ET_BUILDER_DIR . 'framework.php';

	// This is the shortcode specific framework file that is only required if the theme is not set to disable it.
	if ( et_should_load_shortcode_framework() ) {
		et_load_shortcode_framework();
	}

	// Always load the shortcode manager, this is
	// where the magic happens, with lazy loading.
	et_builder_init_shortcode_manager();

	et_pb_register_posttypes();
}

$et_shortcode_framework_shortcodes_used = [];

/**
 * Load the Divi shortcode framework.
 */
function et_load_shortcode_framework( $shortcode = '' ) {
	static $shortcode_framework_loaded = null;
	global $et_shortcode_framework_shortcodes_used;

	// if a specific shortcode is requested, log it.
	if ( !empty( $shortcode ) ) {
		if (!in_array($shortcode, $et_shortcode_framework_shortcodes_used)) {
			$et_shortcode_framework_shortcodes_used[] = $shortcode;
		}
	}

	if ( $shortcode_framework_loaded ) {
		return;
	}

	/**
	 * Fires before the Divi shortcode framework is loaded.
	 *
	 * @since ??
	 *
	 * @param string $shortcode The shortcode that will be loaded.
	 */
	do_action( 'et_will_load_shortcode_framework', $shortcode );

	$shortcode_framework_loaded = true;

	define( 'ET_BUILDER_SHORTCODE_FRAMEWORK_LOADED', true );

	require_once ET_BUILDER_DIR . 'shortcode-framework.php';
}

/**
 * Load the WooCommerce framework.
 *
 * @since ??
 */
function et_load_woocommerce_framework() {
	// Static variable to ensure the WooCommerce framework is only loaded once.
	static $woocommerce_framework_loaded = null;

	if ( $woocommerce_framework_loaded ) {
		return;
	}

	$woocommerce_framework_loaded = true;

	if ( et_is_woocommerce_plugin_active() ) {
		require_once ET_BUILDER_DIR . 'feature/woocommerce-modules.php';
	}
}

/**
 * Check whether the shortcode framework was loaded.
 */
function et_is_shortcode_framework_loaded() {
	return defined( 'ET_BUILDER_SHORTCODE_FRAMEWORK_LOADED' ) && ET_BUILDER_SHORTCODE_FRAMEWORK_LOADED;
}

/**
 * Hook into the footer, to display a note if the shortcode framework was loaded.
 */
function et_display_shortcode_framework_loaded_note() {
	global $et_shortcode_framework_shortcodes_used;
	// Only display this note to logged in users, and not in the Divi Builder and not in Preview mode.
	if ( ! is_user_logged_in() || et_core_is_fb_enabled() || is_et_pb_preview() ) {
		return;
	}

	$css = '';
	$js  = 'jQuery( document ).ready( function() {
		const shortcodeFrameworkViewportWidth = jQuery(window).width();
		const shortcodeFrameworkWarningOffset = jQuery("#wp-admin-bar-et-builder-shortcode-framework").offset().left;
		const shortcodeFrameworkSubmenuWidth = shortcodeFrameworkViewportWidth - shortcodeFrameworkWarningOffset - 32;
		jQuery("#wp-admin-bar-et-builder-shortcode-framework > .ab-sub-wrapper").css("width", shortcodeFrameworkSubmenuWidth + "px");
		});';

	// Display an HTML comment noting whether
	// the shortcode framework was loaded or not.
	if ( et_is_shortcode_framework_loaded() ) {
		?>
		<!-- Divi Shortcode Framework Loaded -->
		<?php

		$css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework { display: block !important } ';
		$css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-loaded { display: block !important } ';
	} else {
		?>
		<!-- Divi Shortcode Framework Not Loaded -->
		<?php
	}

	// Display an HTML comment noting shortcodes that were used.
	if ( !empty( $et_shortcode_framework_shortcodes_used ) ) {
		?>
		<!-- Shortcodes used: <?php echo implode( ', ', $et_shortcode_framework_shortcodes_used ); ?> -->
		<?php

		$css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-shortcodes-used { display: block !important } ';
		$js .= 'jQuery( document ).ready( function() { jQuery( "#wp-admin-bar-et-builder-shortcode-framework-shortcodes-used" ).find( "div" ).html( "<strong>Legacy Modules Detected</strong>: ' . implode( ', ', $et_shortcode_framework_shortcodes_used ) . '" ); } );';
	}

	// Get Divi options.
	$divi_options = get_option( 'et_divi' );

	// If the `et_force_enable_shortcode_framework` option exists, then use it.
	if ( isset( $divi_options['et_force_enable_shortcode_framework'] ) ) {
		?>
		<!-- Divi Option: et_force_enable_shortcode_framework was set to value: <?php echo 'on' === $divi_options['et_force_enable_shortcode_framework'] ? 'true' : 'false'; ?> -->
		<?php

		$css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-option { display: block !important; }';
		// $css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-option a { content: "Option: et_force_enable_shortcode_framework was set to value: ' . ( 'on' === $divi_options['et_force_enable_shortcode_framework'] ? 'true' : 'false' ) . '" } ';
		$js .= 'jQuery( document ).ready( function() { jQuery( "#wp-admin-bar-et-builder-shortcode-framework-option" ).find( "div" ).html( "<p>Option: et_force_enable_shortcode_framework was set to value: ' . ( 'on' === $divi_options['et_force_enable_shortcode_framework'] ? 'true' : 'false' ) . '</p>" ); } );';
	}

	// Note if ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK, was set and its value
	if ( defined( 'ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK' ) ) {
		?>
		<!-- Constant: ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK was set to value: <?php echo ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK ? 'true' : 'false'; ?> -->
		<?php

		$css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-constant { display: block !important; }';
		$js .= 'jQuery( document ).ready( function() { jQuery( "#wp-admin-bar-et-builder-shortcode-framework-constant" ).find( "div" ).html( "<p>Constant: ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK was set to value: ' . ( ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK ? 'true' : 'false' ) . '</p>" ); } );';
	}

	// if ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK_CHANGED_VIA_HOOK, was true
	if ( defined( 'ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK_CHANGED_VIA_HOOK' ) && ET_SHOULD_LOAD_SHORTCODE_FRAMEWORK_CHANGED_VIA_HOOK ) {
		?>
		<!-- Hook: et_should_load_shortcode_framework was used to change the value of $load_shortcode_framework -->
		<?php

		$css .= '#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-hook { display: block !important; }';
		$js .= 'jQuery(document).ready(function() { jQuery("#wp-admin-bar-et-builder-shortcode-framework-hook").find("div").html("<p>Hook: et_should_load_shortcode_framework was used to change the value of $load_shortcode_framework</p>"); });';
	}

	// Output the CSS to show the admin bar items.
	if ( !empty( $css ) ) {
		?>
		<style>
			<?php echo $css; ?>
		</style>
		<?php
	}

	// Output the JS to update the admin bar items.
	if ( !empty( $js ) ) {
		?>
		<script>
			<?php echo $js; ?>
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'et_display_shortcode_framework_loaded_note', 9999 );


// Add wp admin bar item
function et_builder_add_admin_bar_item() {
	global $wp_admin_bar;

	if ( ! is_admin_bar_showing() || et_core_is_fb_enabled() ) {
		return;
	}

	$wp_admin_bar->add_menu( array(
		'id'    => 'et-builder-shortcode-framework',
		'title' => esc_html__( 'Backwards Compatibility Mode Enabled', 'et_builder' ),
		'href'  => '#',
	) );

	// lets add submenu items for each shortcode framework message,
	// We'll hide each menu item with CSS by default, and in the footer, if the shortcode framework was loaded, we'll show the menu item via CSS.

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-loaded',
		'title'  => __( 'This page is running in backwards compatibility mode because it contains legacy Divi 4 modules. They will continue to function, however, this page won\'t benefit from all of Divi 5\'s performance improvements. This page may still need to be converted, or you may be using modules that aren\'t ready for Divi 5. This notice is for information purposes and is not an error.', 'et_builder' ),
		'href'   => false,
	) );

	// Shortcodes used menu item
	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-shortcodes-used',
		'title'  => esc_html__( 'Shortcodes Used', 'et_builder' ),
		'href'   => false,
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-option',
		'title'  => esc_html__( 'Shortcode Framework Option', 'et_builder' ),
		'href'   => false,
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-constant',
		'title'  => esc_html__( 'Shortcode Framework Constant', 'et_builder' ),
		'href'   => false,
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-hook',
		'title'  => esc_html__( 'Shortcode Framework Hook', 'et_builder' ),
		'href'   => false,
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder',
		'id'     => 'et-builder-shortcode-framework-constant-value',
		'title'  => esc_html__( 'Shortcode Framework Constant Value', 'et_builder' ),
		'href'   => false,
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-migrator',
		'title'  => esc_html__( 'Visit The Migrator', 'et_builder' ),
		'href'   => admin_url( 'admin.php?page=et_d5_readiness' ),
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'et-builder-shortcode-framework',
		'id'     => 'et-builder-shortcode-framework-learn-more',
		'title'  => esc_html__( 'Learn More', 'et_builder' ),
		'href'   => 'https://help.elegantthemes.com/en/articles/9824521',
	) );

}
add_action( 'wp_before_admin_bar_render', 'et_builder_add_admin_bar_item' );

/**
 * Output css to hide admin bar items.
 */
function et_builder_admin_bar_css() {
	if ( is_user_logged_in() ) {
	?>
	<style>

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework {
			padding: 4px 6px;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework .ab-sub-wrapper {
			max-width: 500px;
			max-height: calc(100vh - 64px);
			overflow-y: hidden;
			padding: 6px;
			margin-top: 4px;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework .ab-sub-wrapper div.ab-item {
			height: max-content;
			word-wrap: break-word;
			white-space: normal;
			line-height: 1.4em;
			display: inline-block;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework .ab-sub-wrapper div.ab-item * {
			line-height: 1em;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework .ab-sub-wrapper div.ab-item strong {
			color: #fff;
			font-weight: 500;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework > a {
			background-color: orangered;
			position: relative;
			border-radius: 3px;
			height: 24px;
			line-height: 24px;
			font-size: 12px;
		}

		#wp-admin-bar-et-builder-shortcode-framework-migrator {
			border-top: 1px solid #494e56;
			margin-top: 10px !important;
			padding-top: 10px !important;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework > a:hover,
		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework > a:hover::before,
		#wpadminbar li#wp-admin-bar-et-builder-shortcode-framework.hover .ab-item::before,
		#wpadminbar li#wp-admin-bar-et-builder-shortcode-framework.hover > a,
		#wpadminbar:not(.mobile) .ab-top-menu>li:hover > .ab-item,
		#wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus {
			color: #fff !important;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework > a::before {
			font-family: ETmodules !important;
			font-weight: 400;
			content: "";
			color: #fff;
			padding: 4px 0;
			font-size: 16px;
		}

		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework,
		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-loaded,
		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-option,
		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-constant,
		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-hook,
		#wpadminbar #wp-admin-bar-et-builder-shortcode-framework-constant-value {
			display: none;
		}
		</style>
		<?php
	}
}
add_action( 'wp_head', 'et_builder_admin_bar_css' );
add_action( 'admin_head', 'et_builder_admin_bar_css' );

/**
 * Setup builder based on the priority and context.
 *
 * WP CLI `admin` context execute some admin level stuff on `init` action with priority
 * PHP_INT_MIN. Due to this action and its priority, all admin related hooks are fired
 * so early and cause some fatal errors in builder due to some functions are not loaded
 * yet. The `et_setup_builder` method is responsible to load all builder functions. But,
 * it's fired too late after all those admin related hooks are fired. It's not possible
 * to call `et_setup_builder` with priority lower than PHP_INT_MIN. To fix this issue,
 * we need to increase the WP CLI `admin` context `init` action by one, then call the
 * `et_setup_builder` with priority PHP_INT_MIN. In that way, `et_setup_builder` will
 * be on top and called first.
 *
 * Note: The process above only run on WP CLI `admin` context.
 *
 * @since ??
 *
 * @see WP_CLI\Context\Admin
 * @see https://github.com/elegantthemes/Divi/issues/31631
 */
function et_setup_builder_based_on_priority() {
	global $wp_filter;

	$priority = 0;

	// Check WP CLI `admin` context `init` action if any.
	if ( defined( 'WP_CLI' ) && WP_CLI && is_admin() && ! empty( $wp_filter['init'] ) ) {
		// WP CLI `admin` context uses `init` action with priority PHP_INT_MIN and uses
		// -2147483648 as fallback. Otherwise, we can ignore it.
		$hook_priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : -2147483648; // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound -- It's used by WP CLI `admin` context and already add constant check to make sure it exists.
		if ( ! empty( $wp_filter['init'][ $hook_priority ] ) ) {
			foreach ( $wp_filter['init'][ $hook_priority ] as $hook ) {
				$hook_function      = isset( $hook['function'] ) ? $hook['function'] : '';
				$hook_accepted_args = isset( $hook['accepted_args'] ) ? $hook['accepted_args'] : 0;

				// WP CLI `admin` context uses closure as callback. We can assume all hooks
				// with closure as callback on current priority should be moved.
				if ( is_a( $hook_function, 'Closure' ) && is_callable( $hook_function ) ) {
					$priority = $hook_priority;

					// Remove the action temporarily. Re-add the action and increase the priority
					// by one to ensure `et_setup_builder` is called first.
					remove_action( 'init', $hook_function, $hook_priority, $hook_accepted_args );
					add_action( 'init', $hook_function, $hook_priority + 1, $hook_accepted_args );
				}
			}
		}
	}

	// Setup builder based on the priority.
	add_action( 'init', 'et_setup_builder', $priority );
}
et_setup_builder_based_on_priority();

if ( ! function_exists( 'et_divi_maybe_adjust_row_advanced_options_config' ) ):
function et_divi_maybe_adjust_row_advanced_options_config( $advanced_options ) {
	// Row in Divi needs to be further wrapped
	$selector = array(
		'%%order_class%%',
		'body #page-container .et-db #et-boc .et-l %%order_class%%.et_pb_row',
		'body.et_pb_pagebuilder_layout.single #page-container #et-boc .et-l %%order_class%%.et_pb_row',
		'%%row_selector%%',
	);

	$selector = implode( ', ', $selector );

	et_()->array_set( $advanced_options, 'max_width.css.width', $selector );
	et_()->array_set( $advanced_options, 'max_width.css.max_width', $selector );
	et_()->array_set( $advanced_options, 'max_width.options.max_width.default', et_divi_get_content_width() . 'px' );

	if ( ! et_divi_is_boxed_layout() ) {
		return $advanced_options;
	}

	$selector = implode( ', ', array(
		'%%order_class%%',
		'body.et_boxed_layout #page-container %%order_class%%.et_pb_row',
		'body.et_boxed_layout.et_pb_pagebuilder_layout.single #page-container #et-boc .et-l %%order_class%%.et_pb_row',
		'body.et_boxed_layout.et_pb_pagebuilder_layout.single.et_full_width_page #page-container #et-boc .et-l %%order_class%%.et_pb_row',
		'body.et_boxed_layout.et_pb_pagebuilder_layout.single.et_full_width_portfolio_page #page-container #et-boc .et-l %%order_class%%.et_pb_row',
	) );

	et_()->array_set( $advanced_options, 'max_width.css.width', $selector );
	et_()->array_set( $advanced_options, 'max_width.css.max_width', $selector );
	et_()->array_set( $advanced_options, 'max_width.options.width.default', '90%' );

	return $advanced_options;
}
add_filter( 'et_pb_row_advanced_fields', 'et_divi_maybe_adjust_row_advanced_options_config' );
endif;

function et_divi_get_row_advanced_options_selector_replacement() {
	static $replacement;

	if ( empty( $replacement ) ) {
		$post_type = get_post_type();

		if ( 'project' !== $post_type ) {
			// Builder automatically adds `#et-boc` on selector on non official post type; hence
			// alternative selector wrapper for non official post type
			if ( et_builder_is_post_type_custom( $post_type ) ) {
				$replacement = 'body.et_pb_pagebuilder_layout.single.et_full_width_page #page-container %%order_class%%.et_pb_row';
			} else {
				$replacement = 'body.et_pb_pagebuilder_layout.single.et_full_width_page #page-container #et-boc .et-l %%order_class%%.et_pb_row';
			}
		} else {
			// `project` post type has its own specific selector
			$replacement = 'body.et_pb_pagebuilder_layout.single.et_full_width_portfolio_page #page-container #et-boc .et-l %%order_class%%.et_pb_row';
		}
	}

	return $replacement;
}

function et_divi_maybe_adjust_row_advanced_options_selector( $selector ) {
	if ( ! is_string( $selector ) ) {
		return $selector;
	}

	return str_replace( '%%row_selector%%', et_divi_get_row_advanced_options_selector_replacement(), $selector );
}
add_filter( 'et_pb_row_css_selector', 'et_divi_maybe_adjust_row_advanced_options_selector' );

if ( ! function_exists( 'et_divi_maybe_adjust_section_advanced_options_config' ) ):
function et_divi_maybe_adjust_section_advanced_options_config( $advanced_options ) {
	$is_post_type = is_singular( 'post' ) || ( 'et_fb_update_builder_assets' === et_()->array_get( $_POST, 'action' ) && 'post' === et_()->array_get( $_POST, 'et_post_type' ) );

	if ( ! $is_post_type ) {
		$is_tax          = is_tag() || is_category() || is_tax();
		$is_saving_cache = function_exists( 'et_core_is_saving_builder_modules_cache' ) && et_core_is_saving_builder_modules_cache();

		if ( $is_tax && $is_saving_cache ) {
			// If this is a taxonomy request and builder modules cache is being generated, we have to consider
			// `is_post_type` true because the same cached data will be also used for regular posts.
			// This already happens when generating definitions via the AJAX request (see the `et_fb_update_builder_assets`
			// check in the first conditional) and the reason why, before this patch, VB would always reload
			// when loaded for a taxonomy after clearing the cache.
			$is_post_type = true;
		}
	}

	et_()->array_set( $advanced_options, 'max_width.extra.inner.options.max_width.default', et_divi_get_content_width() . 'px' );

	if ( et_divi_is_boxed_layout() ) {
		$selector = implode( ', ', array(
			'%%order_class%% > .et_pb_row',
			'body.et_boxed_layout #page-container %%order_class%% > .et_pb_row',
			'body.et_boxed_layout.et_pb_pagebuilder_layout.single #page-container #et-boc .et-l %%order_class%% > .et_pb_row',
			'body.et_boxed_layout.et_pb_pagebuilder_layout.single.et_full_width_page #page-container #et-boc .et-l %%order_class%% > .et_pb_row',
			'body.et_boxed_layout.et_pb_pagebuilder_layout.single.et_full_width_portfolio_page #page-container #et-boc .et-l %%order_class%% > .et_pb_row',
		) );

		et_()->array_set( $advanced_options, 'max_width.extra.inner.options.width.default', '90%' );
		et_()->array_set( $advanced_options, 'max_width.extra.inner.css.main', $selector );
	} else if ( $is_post_type ) {
		$selector = implode( ', ', array(
			'%%order_class%% > .et_pb_row',
			'body #page-container .et-db #et-boc .et-l %%order_class%% > .et_pb_row',
			'body.et_pb_pagebuilder_layout.single #page-container #et-boc .et-l %%order_class%% > .et_pb_row',
			'body.et_pb_pagebuilder_layout.single.et_full_width_page #page-container #et-boc .et-l %%order_class%% > .et_pb_row',
			'body.et_pb_pagebuilder_layout.single.et_full_width_portfolio_page #page-container #et-boc .et-l %%order_class%% > .et_pb_row',
		) );
		et_()->array_set( $advanced_options, 'max_width.extra.inner.css.main', $selector );
	}

	et_()->array_set( $advanced_options, 'margin_padding.css.main', '%%order_class%%.et_pb_section' );

	return $advanced_options;
}
add_filter( 'et_pb_section_advanced_fields', 'et_divi_maybe_adjust_section_advanced_options_config' );
endif;

/**
 * Modify blog module's advanced options configuration
 *
 * @since ??
 *
 * @param array $advanced_options
 *
 * @return array
 */
function et_divi_maybe_adjust_blog_advanced_options_config( $advanced_options ) {
	// Adding more specific selector for post meta
	$meta_selectors = et_()->array_get( $advanced_options, 'fonts.meta.css' );

	// Main post meta selector
	if ( isset( $meta_selectors['main'] ) ) {
		$main_selectors = explode( ', ', $meta_selectors['main'] );

		$main_selectors[] = '#left-area %%order_class%% .et_pb_post .post-meta';
		$main_selectors[] = '#left-area %%order_class%% .et_pb_post .post-meta a';

		et_()->array_set( $advanced_options, 'fonts.meta.css.main', implode( ', ', $main_selectors ) );
	}

	// Hover post meta selector
	if ( isset( $meta_selectors['hover'] ) ) {
		$hover_selectors = explode( ', ', $meta_selectors['hover'] );

		$hover_selectors[] = '#left-area %%order_class%% .et_pb_post .post-meta:hover';
		$hover_selectors[] = '#left-area %%order_class%% .et_pb_post .post-meta:hover a';
		$hover_selectors[] = '#left-area %%order_class%% .et_pb_post .post-meta:hover span';

		et_()->array_set( $advanced_options, 'fonts.meta.css.hover', implode( ', ', $hover_selectors ) );
	}

	return $advanced_options;
}
add_filter( 'et_pb_blog_advanced_fields', 'et_divi_maybe_adjust_blog_advanced_options_config' );

/**
 * Added custom data attribute to builder's section
 * @param array  initial custom data-* attributes for builder's section
 * @param array  section attributes
 * @param int    section order of appearances. zero based
 * @return array modified custom data-* attributes for builder's section
 */
function et_divi_section_data_attributes( $attributes, $atts, $num ) {
	$custom_padding        = isset( $atts['custom_padding'] ) ? $atts['custom_padding'] : '';
	$custom_padding_tablet = isset( $atts['custom_padding_tablet'] ) ? $atts['custom_padding_tablet'] : '';
	$custom_padding_phone  = isset( $atts['custom_padding_phone'] ) ? $atts['custom_padding_phone'] : '';
	$is_first_section      = 0 === $num;
	$is_transparent_nav    = et_divi_is_transparent_primary_nav();

	// Custom data-* attributes for transparent primary nav support.
	// Note: in customizer, the data-* attributes have to be printed for live preview purpose
	if ( $is_first_section && ( $is_transparent_nav || is_customize_preview() ) ) {
		if ( '' !== $custom_padding && 4 === count( explode( '|', $custom_padding ) ) ) {
			$attributes['padding'] = $custom_padding;
		}

		if ( '' !== $custom_padding_tablet && 4 === count( explode( '|', $custom_padding_tablet ) ) ) {
			$attributes['padding-tablet'] = $custom_padding_tablet;
		}

		if ( '' !== $custom_padding_phone && 4 === count( explode( '|', $custom_padding_phone ) ) ) {
			$attributes['padding-phone'] = $custom_padding_phone;
		}
	}

	return $attributes;
}
add_filter( 'et_pb_section_data_attributes', 'et_divi_section_data_attributes', 10, 3 );

/**
 * Switch the translation of Visual Builder interface to current user's language
 * @return void
 */
if ( ! function_exists( 'et_fb_set_builder_locale' ) ) :
function et_fb_set_builder_locale( $locale ) {
	// apply translations inside VB only
	if ( empty( $_GET['et_fb'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		return $locale;
	}

	$user = get_user_locale();

	if ( $user === $locale ) {
		return $locale;
	}

	if ( ! function_exists( 'switch_to_locale' ) ) {
		return $locale;
	}

	switch_to_locale( $user );

	return $user;
}
endif;
add_filter( 'theme_locale', 'et_fb_set_builder_locale' );

/**
 * Added custom post class
 * @param array $classes array of post classes
 * @param array $class   array of additional post classes
 * @param int   $post_id post ID
 * @return array modified array of post classes
 */
function et_pb_post_class( $classes, $class, $post_id ) {
	global $post;

	// Added specific class name if curent post uses comment module. Use global $post->post_content
	// instead of get_the_content() to retrieve the post's unparsed shortcode content
	if ( is_single() && has_shortcode( $post->post_content, 'et_pb_comments' ) ) {
		$classes[] = 'et_pb_no_comments_section';
	}

	return $classes;
}
add_filter( 'post_class', 'et_pb_post_class', 10, 3 );
