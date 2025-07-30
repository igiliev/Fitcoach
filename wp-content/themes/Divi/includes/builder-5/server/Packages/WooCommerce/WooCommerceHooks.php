<?php
/**
 * Handles WooCommerce-specific hooks and dependencies.
 *
 * This class is responsible for registering and managing actions
 * and filters related to WooCommerce integration.
 *
 * @since   ??
 * @package Divi
 */

namespace ET\Builder\Packages\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\Assets\DetectFeature;
use WP_Post;

/*
 * Define required constants.
 *
 * The constants are copied from legacy (D4) code in `includes/builder/feature/woocommerce-modules.php` which would
 * define these constant if D5 page has woo modules shortcodes, however these constant won't be fined otherwise. This
 * is why it's being copied here for a page which has only D5 WC modules.
 *
 * If needed these constants would be modified for D5 purposes in a future iteration.
 */
if ( ! defined( 'ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY' ) ) {
	// Post meta key to retrieve/save Long description metabox content.
	define( 'ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY', '_et_pb_old_content' );
}

if ( ! defined( 'ET_BUILDER_WC_PRODUCT_PAGE_LAYOUT_META_KEY' ) ) {
	// Post meta key to retrieve/save Long description metabox content.
	define( 'ET_BUILDER_WC_PRODUCT_PAGE_LAYOUT_META_KEY', '_et_pb_product_page_layout' );
}

if ( ! defined( 'ET_BUILDER_WC_PRODUCT_PAGE_CONTENT_STATUS_META_KEY' ) ) {
	// Post meta key to track Product page content status changes.
	define( 'ET_BUILDER_WC_PRODUCT_PAGE_CONTENT_STATUS_META_KEY', '_et_pb_woo_page_content_status' );
}

/**
 * Manages WooCommerce-related hooks and functionalities.
 *
 * This class facilitates the initialization of WooCommerce-specific
 * actions and filters required for proper integration.
 *
 * @since ??
 */
class WooCommerceHooks implements DependencyInterface {

	/**
	 * Initializes and registers necessary WooCommerce actions and filters.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// Bail when WooCommerce plugin is not active.
		if ( ! et_is_woocommerce_plugin_active() ) {
			return;
		}

		/*
		 * Bail when the feature flag is disabled.
		 *
		 * Ensure the following actions/filters are only executed
		 * when the wooProductPageModules feature flag is enabled.
		 */
		if ( ! et_get_experiment_flag( 'wooProductPageModules' ) ) {
			return;
		}

		// global $post won't be available with `after_setup_theme` hook and hence `wp` hook is used.
		add_action( 'wp', [ self::class, 'override_default_layout' ] );

		// Add WooCommerce class names on non-`product` CPT which uses builder.
		// Note: following filters are being called from `et_builder_wc_init` for legacy wc modules, too.
		add_filter( 'body_class', [ self::class, 'add_body_class' ] );
		add_filter( 'et_builder_inner_content_class', [ self::class, 'add_inner_content_class' ] );
		add_filter( 'et_pb_preview_wrap_class', [ self::class, 'add_preview_wrap_class' ] );
		add_filter( 'et_builder_outer_content_class', [ self::class, 'add_outer_content_class' ] );

		// Parse product description for shortcode and block output.
		add_filter( 'et_builder_wc_description', [ self::class, 'parse_description' ] );

		// Remove legacy hooks before adding new ones to avoid duplicate functionality.
		remove_action( 'add_meta_boxes_product', 'et_builder_wc_long_description_metabox_register' );
		// Register metabox for product long description editing.
		add_action( 'add_meta_boxes_product', [ self::class, 'long_description_metabox_register' ] );

		remove_action( 'et_pb_old_content_updated', 'et_builder_wc_long_description_metabox_save', 10, 3 );
		// Save the product long description content when updated.
		add_action( 'et_pb_old_content_updated', [ self::class, 'long_description_metabox_save' ], 10, 3 );

		remove_filter( 'the_content', 'et_builder_avoid_nested_shortcode_parsing' );
		// Strip Builder shortcodes to prevent nested parsing issues.
		add_filter( 'the_content', [ self::class, 'avoid_nested_shortcode_parsing' ] );

		remove_action( 'rest_after_insert_page', 'et_builder_wc_delete_post_meta' );
		// Clean up product page content status meta when Builder is disabled.
		add_action( 'rest_after_insert_page', [ self::class, 'delete_post_meta' ] );

		// Relocate WooCommerce single product summary hooks to any suitable modules.
		add_action( 'divi_frontend_initialize', [ self::class, 'relocate_woocommerce_single_product_summary' ] );

		// Add cache invalidation for WooCommerce product descriptions when that product is updated.
		add_action( 'save_post_product', [ self::class, 'invalidate_product_description_caches' ] );

		// Add cache invalidation for WooCommerce breadcrumbs when a product is updated.
		add_action( 'save_post_product', [ self::class, 'invalidate_breadcrumb_caches' ] );

		// Remove legacy hook before adding the new one to avoid duplicate functionality.
		remove_filter( 'et_builder_settings_definitions', 'et_builder_wc_add_settings' );
		// Adds WooCommerce Module settings to the Builder settings.
		// Adding in the Builder Settings tab will ensure that the field is available in Extra Theme and
		// Divi Builder Plugin. Divi Theme Options ⟶ Builder ⟶ Post Type Integration.
		add_filter( 'et_builder_settings_definitions', [ WooCommerceUtils::class, 'add_settings' ] );

		// Only add the placeholder image filter when specific conditions are met.
		// This matches the legacy behavior in et_fb_current_page_woocommerce_components().
		$is_product_cpt        = 'product' === get_post_type();
		$is_tb                 = et_builder_tb_enabled();
		$cpt_has_wc_components = $is_product_cpt || $is_tb;
		// We already checked et_is_woocommerce_plugin_active() at the beginning of this method.

		if ( $cpt_has_wc_components && $is_tb ) {
			// Remove legacy hook before adding the new one to avoid duplicate functionality.
			remove_filter( 'woocommerce_single_product_image_thumbnail_html', 'et_builder_wc_placeholder_img' );

			// Provides placeholder image HTML for WooCommerce product images.
			// Used when product images are missing or when in the builder.
			add_filter( 'woocommerce_single_product_image_thumbnail_html', [ WooCommerceUtils::class, 'placeholder_img' ] );
		}

	}

	/**
	 * Add WooCommerce body class name on non `product` CPT builder page
	 *
	 * Based on the legacy `et_builder_wc_add_body_class` function.
	 *
	 * @since ??
	 *
	 * @param array $classes CSS class names.
	 *
	 * @return array
	 */
	public static function add_body_class( array $classes ): array {
		if ( WooCommerceUtils::is_non_product_post_type() || is_et_pb_preview() ) {
			$classes[] = 'woocommerce';
			$classes[] = 'woocommerce-page';
		}

		return $classes;
	}

	/**
	 * Add product class name on inner content wrapper page on non `product` CPT builder page with woocommerce modules
	 * And on Product posts.
	 *
	 * Based on legacy `et_builder_wc_add_inner_content_class` function.
	 *
	 * @since ??
	 *
	 * @param array $classes Product class names.
	 *
	 * @return array
	 */
	public static function add_inner_content_class( array $classes ): array {
		// The class is required on any post with woocommerce modules and on product pages.
		if ( WooCommerceUtils::is_non_product_post_type() || is_product() || is_et_pb_preview() ) {
			$classes[] = 'product';
		}

		return $classes;
	}

	/**
	 * Add WooCommerce class names on Divi Shop Page (not WooCommerce Shop).
	 *
	 * Based on legacy `et_builder_wc_add_outer_content_class` function.
	 *
	 * @since ??
	 *
	 * @param array $classes Array of Classes.
	 *
	 * @return array
	 */
	public static function add_outer_content_class( array $classes ): array {
		// Bail early if not on the WooCommerce shop page or if the shop page is not built using Divi.
		if ( ! ( function_exists( 'is_shop' ) && is_shop() && WooCommerceUtils::is_non_product_post_type() ) ) {
			return $classes;
		}

		// Get body classes once and ensure it's an array.
		$body_classes = get_body_class();
		if ( ! is_array( $body_classes ) ) {
			return $classes;
		}

		// Check if both required WooCommerce classes are already present.
		$woocommerce_classes = [ 'woocommerce', 'woocommerce-page' ];
		if ( array_intersect( $woocommerce_classes, $body_classes ) === $woocommerce_classes ) {
			return $classes;
		}

		// Append WooCommerce classes to the array.
		$classes = array_merge( $classes, $woocommerce_classes );

		return $classes;
	}

	/**
	 * Adds the Preview class to the wrapper.
	 *
	 * Based on legacy `et_builder_wc_add_preview_wrap_class` function.
	 *
	 * @since ??
	 *
	 * @param string $maybe_class_string Classnames string.
	 *
	 * @return string
	 */
	public static function add_preview_wrap_class( string $maybe_class_string ): string {
		// Sanity Check.
		if ( ! is_string( $maybe_class_string ) ) {
			return $maybe_class_string;
		}

		$classes   = explode( ' ', $maybe_class_string );
		$classes[] = 'product';

		return implode( ' ', $classes );
	}

	/**
	 * Disable all default WooCommerce single layout hooks.
	 *
	 * @since ??
	 */
	public static function disable_default_layout() {
		// To remove a hook, the $function_to_remove and $priority arguments must match
		// with which the hook was added.
		remove_action(
			'woocommerce_before_main_content',
			'woocommerce_breadcrumb',
			20
		);

		remove_action(
			'woocommerce_before_single_product_summary',
			'woocommerce_show_product_sale_flash',
			10
		);
		remove_action(
			'woocommerce_before_single_product_summary',
			'woocommerce_show_product_images',
			20
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_title',
			5
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_rating',
			10
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_price',
			10
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_excerpt',
			20
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_add_to_cart',
			30
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_meta',
			40
		);
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_sharing',
			50
		);
		remove_action(
			'woocommerce_after_single_product_summary',
			'woocommerce_output_product_data_tabs',
			10
		);
		remove_action(
			'woocommerce_after_single_product_summary',
			'woocommerce_upsell_display',
			15
		);
		remove_action(
			'woocommerce_after_single_product_summary',
			'woocommerce_output_related_products',
			20
		);
	}

	/**
	 * Invalidate product description cache.
	 *
	 * The product description is rendered in Divi modules using the `et_builder_wc_description` filter,
	 * which caches the result. This function invalidates both short and long description caches for a
	 * product when it's updated. This is to ensure that the product description is always up to date
	 * in Divi modules that display product descriptions.
	 *
	 * @since ??
	 *
	 * @param int $post_id The product post ID.
	 *
	 * @return void
	 */
	public static function invalidate_product_description_caches( int $post_id ) {
		// Invalidate both short and long description caches.
		foreach ( [ 'short_description', 'description' ] as $desc_type ) {
			$cache_key = 'divi_wc_product_desc_' . md5( $post_id . '_' . $desc_type );
			delete_transient( $cache_key );
		}
	}

	/**
	 * Invalidate breadcrumb caches.
	 *
	 * The breadcrumb HTML is cached using transients for performance. This function invalidates
	 * all breadcrumb caches when a product is updated. This is necessary because breadcrumbs
	 * often include category hierarchies that might be affected by changes to any product.
	 *
	 * @since ??
	 *
	 * @param int $post_id The product post ID.
	 *
	 * @return void
	 */
	public static function invalidate_breadcrumb_caches( int $post_id ) {
		// We can't know all possible cache keys since they depend on various arguments,
		// so we'll use a wildcard pattern to delete all transients that might contain
		// breadcrumb HTML.
		global $wpdb;

		// Get the option name prefix for transients.
		$prefix = '_transient_divi_wc_breadcrumb_';

		// Delete all breadcrumb transients - this is a broader approach
		// but ensures all breadcrumb caches are refreshed when any product changes
		// since breadcrumbs often include category hierarchies that might be affected
		// by changes to other products.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$prefix . '%'
			)
		);
	}

	/**
	 * Overrides the default WooCommerce layout.
	 *
	 * This method customizes the WooCommerce product page layout by checking various conditions,
	 * such as the current post type, layout configurations, and supported themes. It disables
	 * the default WooCommerce layout and registers custom layout logic, ensuring compatibility
	 * with Divi and Extra themes.
	 *
	 * @see woocommerce/includes/wc-template-functions.php
	 *
	 * @since ??
	 */
	public static function override_default_layout() {
		// Bail if the current page is not a single product page.
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		// The `global $post` variable is required here as it's not available during `after_setup_theme`.
		global $post;

		// Bail if the current page is not using the page builder.
		if ( ! et_pb_is_pagebuilder_used( $post->ID ) ) {
			return;
		}

		// Get the product page layout setting for this page and the content's modification status.
		$product_page_layout         = WooCommerceUtils::get_product_layout( $post->ID );
		$is_product_content_modified = 'modified' === get_post_meta( $post->ID, ET_BUILDER_WC_PRODUCT_PAGE_CONTENT_STATUS_META_KEY, true );
		$is_preview_loading          = is_preview();

		/*
		 * Bail if the layout is set to "build from scratch" (`et_build_from_scratch`),
		 * but the product content hasn't been modified yet and it's not in preview mode.
		 */
		if ( 'et_build_from_scratch' === $product_page_layout && ! $is_product_content_modified && ! $is_preview_loading ) {
			return;
		}

		/*
		 * Bail if:
		 * 1. No specific product page layout is configured, and the front-end builder is not enabled.
		 * 2. A specific layout is configured, but it's not "build from scratch".
		 */
		if (
			( ! $product_page_layout && ! et_core_is_fb_enabled() ) ||
			( $product_page_layout && 'et_build_from_scratch' !== $product_page_layout )
		) {
			return;
		}

		/*
		 * If the active theme is not Divi or Extra, enforce WooCommerce's default templates.
		 * This ensures compatibility with themes that may use custom templates (e.g., child themes or DBP).
		 */
		if ( ! in_array( wp_get_theme()->get( 'Name' ), [ 'Divi', 'Extra' ], true ) ) {
			// Override the WooCommerce template part logic using a custom filter.
			add_filter( 'wc_get_template_part', [ self::class, 'override_template_part' ], 10, 3 );
		}

		// Disable all default WooCommerce layout hooks for single product pages.
		self::disable_default_layout();

		// Trigger an action hook to notify that custom Divi layout registration is about to occur.
		do_action( 'divi_woocommerce_product_before_render_layout_registration' );

		// Remove the legacy function that renders content on the single product page.
		remove_action( 'woocommerce_after_single_product_summary', 'et_builder_wc_product_render_layout', 5 );

		// Add the updated function to render the content on the single product page.
		add_action( 'woocommerce_after_single_product_summary', [ self::class, 'product_render_layout' ], 5 );
	}

	/**
	 * Force WooCommerce to load default template over theme's custom template when builder's
	 * et_builder_from_scratch is used to prevent unexpected custom layout which makes builder
	 * experience inconsistent
	 *
	 * @since ??
	 *
	 * @param string $template  Path to template file.
	 * @param string $slug      Template slug.
	 * @param string $name      Template name.
	 *
	 * @return string
	 */
	public static function override_template_part( string $template, string $slug, string $name ): string {
		// Only force load default `content-single-product.php` template.
		$is_content_single_product = 'content' === $slug && 'single-product' === $name;

		return $is_content_single_product ? WC()->plugin_path() . "/templates/{$slug}-{$name}.php" : $template;
	}

	/**
	 * Parses and formats the WooCommerce product description for use in Divi modules.
	 *
	 * This method processes the product description by:
	 * - Stripping builder-specific shortcodes to avoid nested or duplicate rendering
	 * - Running WordPress embed and shortcode processing to convert shortcodes and embeds to HTML
	 * - Optionally running block parsing (do_blocks) for Gutenberg compatibility
	 * - Wrapping the result in <p> tags for proper HTML formatting
	 * - Caching the result for performance
	 *
	 * This ensures that product descriptions are rendered consistently and safely in Divi WooCommerce modules,
	 * whether the content comes from the post content, custom meta, or is dynamically generated. It is especially
	 * important for modules that display product descriptions in custom layouts, as it prevents issues with
	 * nested shortcodes, missing formatting, or unprocessed blocks.
	 *
	 * Based on legacy `et_builder_wc_parse_description` function.
	 *
	 * This function is registered on the `et_builder_wc_description` filter.
	 *
	 * Currently used by:
	 * - WooCommerce Product Description module (@see WooCommerceProductDescriptionModule::get_description) for
	 * a long description when builder is used.
	 *
	 * To be used by:
	 * - WooCommerce Tabs module (@see ET_Builder_Module_Woocommerce_Tabs).
	 * In Divi 4, the equivalent filter is applied to tab content for the product description tab.
	 * As of now, there is no direct equivalent in D5, or the filter is not yet applied in a D5 Tabs module.
	 *
	 * TODO feat(D5, WooCommerce Modules): Integrate this filter into the Divi 5 WooCommerce Product Tabs module to match Divi 4 behavior and ensure consistent description parsing. [https://github.com/elegantthemes/Divi/issues/43121]
	 *
	 * @param string|mixed $description Product description (e.g., post content, excerpt, or custom meta).
	 *
	 * @return string|mixed Parsed and formatted product description.
	 */
	public static function parse_description( $description ) {
		if ( ! is_string( $description ) ) {
			return $description;
		}

		// Use cached description if available, otherwise parse the description and cache it for future use.
		static $cache = [];
		$cache_key    = md5( $description );
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		global $wp_embed;

		// Strip unnecessary shortcodes.
		$parsed_description = et_strip_shortcodes( $description );

		// Run shortcode.
		$parsed_description = $wp_embed->run_shortcode( $parsed_description );

		// Run do_blocks if available and log timing.
		if ( function_exists( 'has_blocks' ) && has_blocks( $parsed_description ) ) {
			$parsed_description = do_blocks( $parsed_description );
		}

		// If the shortcode framework is loaded, process shortcodes.
		if ( et_is_shortcode_framework_loaded() ) {
			$parsed_description = do_shortcode( $parsed_description );
		}

		$parsed_description  = wpautop( $parsed_description );
		$cache[ $cache_key ] = $parsed_description;

		return $parsed_description;
	}

	/**
	 * Renders the content.
	 *
	 * Rendering the content will enable Divi Builder to take over the entire
	 * post content area.
	 *
	 * @since ??
	 */
	public static function product_render_layout() {
		do_action( 'divi_woocommerce_product_before_render_layout' );

		the_content();

		do_action( 'divi_woocommerce_product_after_render_layout' );
	}

	/**
	 * Relocates all registered callbacks from `woocommerce_single_product_summary` hook to suitable WooCommerce modules.
	 *
	 * This function is responsible for relocating the WooCommerce single product summary hooks to
	 * suitable modules. It checks if the current page is a product-related page, whether the
	 * Theme Builder is enabled, and if the WooCommerce modules are present in the content.
	 * It then copies the hooks to the appropriate modules and removes them from the original
	 * location if necessary.
	 *
	 * This function is based on legacy `et_builder_wc_relocate_single_product_summary` function.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function relocate_woocommerce_single_product_summary(): void {
		global $post, $wp_filter;

		if ( ! $post ) {
			return;
		}

		$tb_body_layout = ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE;
		$tb_layouts     = et_theme_builder_get_template_layouts();
		// Get whether TB overrides the specified layout for the current request.
		$tb_body_override          = ! empty( $tb_layouts ) && $tb_layouts[ $tb_body_layout ]['override'];
		$tb_body_layout_id         = $tb_body_override ? $tb_layouts[ $tb_body_layout ]['id'] : false;
		$tb_body_content           = $tb_body_layout_id ? get_post_field( 'post_content', $tb_body_layout_id ) : '';
		$has_woocommerce_module    = DetectFeature::has_woocommerce_module_block( $post->post_content );
		$has_woocommerce_module_tb = DetectFeature::has_woocommerce_module_block( $tb_body_content );
		$hook                      = $wp_filter['woocommerce_single_product_summary'] ?? null;

		// Bail early if there is no `woocommerce_single_product_summary` hook callbacks or
		// if there is no WooCommerce module in the content of current page and TB body layout.
		if ( empty( $hook->callbacks ) || ( ! $has_woocommerce_module && ! $has_woocommerce_module_tb ) ) {
			return;
		}

		$is_copy_needed = false;
		$is_move_needed = false;
		$post_id        = empty( $post->ID ) ? false : $post->ID;

		// Product related pages.
		$is_product          = function_exists( 'is_product' ) && is_product();
		$is_shop             = function_exists( 'is_shop' ) && is_shop();
		$is_product_category = function_exists( 'is_product_category' ) && is_product_category();
		$is_product_tag      = function_exists( 'is_product_tag' ) && is_product_tag();

		// Copy single product summary hooks when current page is:
		// - Product related pages: single, shop, category, & tag.
		// - Theme Builder or Page Builder.
		// - Before & after components AJAX request.
		// - Has TB layouts contain WC modules.
		if (
		$is_product
		|| $is_shop
		|| $is_product_category
		|| $is_product_tag
		|| et_builder_tb_enabled()
		|| et_core_is_fb_enabled()
		|| et_fb_is_before_after_components_callback_ajax()
		|| WooCommerceUtils::is_non_product_post_type()
		) {
			$is_copy_needed = true;
		}

		// Move single product summary hooks when current page is single product with:
		// - Builder is used.
		// - TB Body layout overrides the content.
		if ( $is_product ) {
			if ( et_pb_is_pagebuilder_used( $post_id ) || $tb_body_override ) {
				$is_move_needed = true;
			}
		}

		/**
		 * Filters whether to copy single product summary hooks output or not.
		 *
		 * 3rd-party plugins can use this filter to force enable or disable this action.
		 *
		 * @since 4.14.5
		 *
		 * @param boolean $is_copy_needed Whether to copy single product summary or not.
		 */
		$is_copy_needed = apply_filters( 'divi_woocommerce_relocate_single_product_summary_is_copy_needed', $is_copy_needed );

		/**
		 * Filters whether to move (remove the original) single product summary or not.
		 *
		 * 3rd-party plugins can use this filter to force enable or disable this action.
		 *
		 * @since 4.14.5
		 *
		 * @param boolean $is_move_needed Whether to move single product summary or not.
		 */
		$is_move_needed = apply_filters( 'divi_woocommerce_relocate_single_product_summary_is_move_needed', $is_move_needed );

		// Bail early if copy action is not needed.
		if ( ! $is_copy_needed ) {
			return;
		}

		$modules_with_relocation = array();

		/**
		 * Filters the list of ignored `woocommerce_single_product_summary` hook callbacks.
		 *
		 * 3rd-party plugins can use this filter to keep their callbacks so they won't be
		 * relocated from `woocommerce_single_product_summary` hook. The value is string of
		 * `function_name` or `class::method` combination. By default, it contanis all single
		 * product summary actions from WooCommerce plugin.
		 *
		 * @since 4.14.5
		 *
		 * @param array $ignored_callbacks List of ignored callbacks.
		 */
		$ignored_callbacks = apply_filters(
			'divi_woocommerce_relocate_single_product_summary_ignored_callbacks',
			array(
				'WC_Structured_Data::generate_product_data',
				'woocommerce_template_single_title',
				'woocommerce_template_single_rating',
				'woocommerce_template_single_price',
				'woocommerce_template_single_excerpt',
				'woocommerce_template_single_add_to_cart',
				'woocommerce_template_single_meta',
				'woocommerce_template_single_sharing',
			)
		);

		// Pair of WooCommerce layout priority numbers and WooCommerce module slugs.
		$modules_priority = array(
			'5'  => 'divi/woocommerce-product-title',
			'10' => 'divi/woocommerce-product-price',
			'10' => 'divi/woocommerce-product-rating',
			'20' => 'divi/woocommerce-product-description', // Description defaults to `excerpt` on WooCommerce default layout.
			'30' => 'divi/woocommerce-product-add-to-cart',
			'40' => 'divi/woocommerce-product-meta',
		);

		foreach ( $hook->callbacks as $callback_priority => $callbacks ) {
			foreach ( $callbacks as $callback_args ) {
				// 1. Generate 'callback name' (string).
				// Get the callback name stored on the `function` argument.
				$callback_function = $callback_args['function'] ?? '';
				$callback_name     = $callback_function;

				// Bail early if the callback is not callable to avoid any unexpected issue.
				if ( ! is_callable( $callback_function ) ) {
					continue;
				}

				// If the `function` is an array, it's probably a class based function.
				// We should convert it into string based callback name for validating purpose.
				if ( is_array( $callback_function ) ) {
					$callback_name   = '';
					$callback_object = $callback_function[0] ?? '';
					$callback_method = $callback_function[1] ?? '';

					// Ensure the index `0` is an object and the index `1` is string. We're going to
					// use the class::method combination as callback name.
					if ( is_object( $callback_object ) && is_string( $callback_method ) ) {
						$callback_class = get_class( $callback_object );
						$callback_name  = "{$callback_class}::{$callback_method}";
					}
				}

				// Bail early if callback name is not string or empty to avoid unexpected issues.
				if ( ! is_string( $callback_name ) || empty( $callback_name ) ) {
					continue;
				}

				// Bail early if current callback is listed on ignored callbacks list.
				if ( in_array( $callback_name, $ignored_callbacks, true ) ) {
					continue;
				}

				// 2. Generate 'module priority' to get suitable 'module slug'.
				// Find the module priority number by round down the priority to the nearest 10.
				// It's needed to get suitable WooCommerce module. For example, a callback with priority
				// 41 means we have to put it on module with priority 40 which is `et_pb_wc_meta`.
				$rounded_callback_priority = intval( floor( $callback_priority / 10 ) * 10 );
				$module_priority           = $rounded_callback_priority;

				// Additional rules for module priority:
				// - 0  : Make it 5 as default to target `et_pb_wc_title` because there is no
				// module with priority less than 5.
				// - 50 : Make it 40 as default to target `et_pb_wc_meta` because there is no
				// module with priority more than 40.
				if ( 0 === $rounded_callback_priority ) {
					$module_priority = 5;
				} elseif ( $rounded_callback_priority >= 50 ) {
					$module_priority = 40;
				}

				$module_slug = $modules_priority[ $module_priority ] ?? '';

				/**
				 * Filters target module for the current callback.
				 *
				 * 3rd-party plugins can use this filter to target different module slug.
				 *
				 * @since 4.14.5
				 *
				 * @param string $module_slug     Module slug.
				 * @param string $callback_name   Callback name.
				 * @param string $module_priority Module priority.
				 */
				$module_slug = apply_filters( 'divi_woocommerce_relocate_single_product_summary_module_slug', $module_slug, $callback_name, $module_priority );

				// Bail early if module slug is empty.
				if ( empty( $module_slug ) ) {
					continue;
				}

				// 3. Determine 'output location'.
				// Move the callback to the suitable WooCommerce module. Since we can't call the action
				// inside the module render, we have to buffer the output and prepend/append it
				// to the module output or preview. By default, the default location is 'after'
				// the module output or preview. But, for priority less than 5, we have to put it
				// before the `et_pb_wc_title` because there is no module on that location.
				$output_location = $callback_priority < 5 ? 'before' : 'after';

				/**
				 * Filters output location for the current module and callback.
				 *
				 * 3rd-party plugins can use this filter to change the output location.
				 *
				 * @since 4.14.5
				 *
				 * @param string $output_location   Output location.
				 * @param string $callback_name     Callback name.
				 * @param string $module_slug       Module slug.
				 * @param string $callback_priority Callback priority.
				 */
				$output_location = apply_filters( 'divi_woocommerce_relocate_single_product_summary_output_location', $output_location, $callback_name, $module_slug, $callback_priority );

				// Bail early if the output location is not 'before' or 'after'.
				if ( ! in_array( $output_location, array( 'before', 'after' ), true ) ) {
					continue;
				}

				// 4. Determine 'module output priority'.
				// Get the "{$module_slug}_{$hook_suffix_name}}" filter priority number by sum up
				// default hook priority number (10) and the remainder. This part is important,
				// so we can prepend and append the layout output more accurate. For example:
				// Callback A with priority 42 should be added after callback B with priority 41
				// on `et_pb_wc_meta` module. So, "et_pb_wc_meta_{$hook_suffix_name}_output" hook
				// priority for callback A will be 12, meanwhile callback B will be 11.
				$remainder_priority = $rounded_callback_priority > 0 ? $callback_priority % 10 : $callback_priority - 5;
				$output_priority    = 10 + $remainder_priority;

				/**
				 * Filters module output priority number for the current module and callback.
				 *
				 * 3rd-party plugins can use this filter to rearrange the output priority.
				 *
				 * @since 4.14.5
				 *
				 * @param string $output_priority   Module output priority number.
				 * @param string $callback_name     Callback name.
				 * @param string $module_slug       Module slug.
				 * @param string $callback_priority Callback priority.
				 */
				$output_priority = apply_filters( 'divi_woocommerce_relocate_single_product_summary_output_priority', $output_priority, $callback_name, $module_slug, $callback_priority );

				// Remove the callback from `woocommerce_single_product_summary` when it's needed.
				if ( $is_move_needed ) {
					remove_action( 'woocommerce_single_product_summary', $callback_function, $callback_priority );
				}

				// And, copy and paste it to suitable location & module.
				add_action( "divi_woocommerce_single_product_summary_{$output_location}_{$module_slug}", $callback_function, $output_priority );

				$modules_with_relocation[] = $module_slug;
			}
		}

		// Finally, move it to suitable WooCommerce modules.
		if ( ! empty( $modules_with_relocation ) ) {
			foreach ( $modules_with_relocation as $module_slug ) {
				// Builder - Before and/or after components.
				add_filter( "{$module_slug}_fb_before_after_components", [ self::class, 'single_product_summary_before_after_components' ], 10, 3 );

				// FE - Block output.
				add_filter( "render_block_{$module_slug}", [ self::class, 'single_product_summary_module_output' ], 10, 3 );
			}
		}
	}

	/**
	 * Prepends and/or append callback output to the suitable module output on FE.
	 *
	 * This function is responsible for processing the output of WooCommerce modules in the FE.
	 * It checks if the module output is a string and retrieves the current product.
	 * It then appends the before and after components to the module's output.
	 * The function also handles the case where the WooCommerce module is being used in the Theme Builder or FE.
	 * It ensures that the global product and post objects are set correctly based on the target product ID.
	 * The function returns the processed module output.
	 *
	 * This function is based on legacy `et_builder_wc_single_product_summary_module_output` function.
	 *
	 * @since ??
	 *
	 * @param string $module_output   Module output.
	 * @param string $module_slug     Module slug.
	 * @param mixed  $product_id      Product ID.
	 *
	 * @return string Processed module output.
	 */
	public static function single_product_summary_module_output( string $module_output, string $module_slug, $product_id ): string {
		// Bail early if module output is not string.
		if ( ! is_string( $module_output ) ) {
			return $module_output;
		}

		global $post, $product;

		$original_post    = $post;
		$original_product = $product;
		$target_id        = '';
		$is_overwritten   = false;

		if ( ! empty( $product_id ) ) {
			// Get target ID if any.
			$target_id = WoocommerceUtils::get_product_id( $product_id );
		}

		// Determine whether global product and post objects need to be overwritten or not.
		if ( 'current' !== $target_id ) {
			$target_product = wc_get_product( $target_id );

			if ( $target_product instanceof \WC_Product ) {
				$is_overwritten = false;
				$product        = $target_product;
				$post           = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Overriding global post is safe as original $post is restored at the function end.
			}
		}

		// Get before & after outputs only if product is WC_Product instance.
		if ( $product instanceof \WC_Product ) {
			$before_output = self::single_product_summary_before_module( $module_slug );
			$after_output  = self::single_product_summary_after_module( $module_slug );
			$module_output = $before_output . $module_output . $after_output;
		}

		// Reset product and/or post object.
		if ( $is_overwritten ) {
			$product = $original_product;
			$post    = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Restoring global post.
		}

		return $module_output;
	}

	/**
	 * Sets callback output as before and/or after components on builder.
	 *
	 * This function is responsible for processing the before and after components
	 * of a WooCommerce module. It checks if the module is a WooCommerce module,
	 * retrieves the current product, and appends the before and after components
	 * to the module's output. The function also handles the case where the
	 * WooCommerce module is being used in the Theme Builder or Frontend Builder.
	 * It ensures that the global product and post objects are set correctly
	 * based on the target product ID. The function returns the processed module
	 * before and after components.
	 *
	 * This function is based on legacy `et_builder_wc_single_product_summary_before_after_components` function.
	 *
	 * @since ??
	 *
	 * @param array  $module_components Default module before & after components.
	 * @param string $module_slug       Module slug.
	 * @param array  $module_data       Module data.
	 *
	 * @return array Processed module before & after components.
	 */
	public static function single_product_summary_before_after_components( array $module_components, string $module_slug, array $module_data ): array {
		// Bail early if module components variable is not an array.
		if ( ! is_array( $module_components ) ) {
			return $module_components;
		}

		global $post, $product;

		$original_post    = $post;
		$original_product = $product;
		$target_id        = '';
		$overwritten_by   = '';
		$is_tb_enabled    = et_builder_tb_enabled();
		$is_fb_enabled    = et_core_is_fb_enabled() || is_et_pb_preview();

		if ( ! empty( $module_data ) ) {
			// Get target ID if any.
			$target_id = WooCommerceUtils::get_product_id( et_()->array_get( $module_data, array( 'module_attrs', 'product' ) ) );
		}

		// Determine whether global product and post objects need to be overwritten or not.
		// - Dummy product:  TB and FB initial load.
		// - Target product: Components request from builder.
		if ( $is_tb_enabled || $is_fb_enabled ) {
			et_theme_builder_wc_set_global_objects( array( 'is_tb' => true ) );
			$overwritten_by = 'dummy_product';
		} elseif ( 'current' !== $target_id && et_fb_is_before_after_components_callback_ajax() ) {
			$target_product = wc_get_product( $target_id );

			if ( $target_product instanceof \WC_Product ) {
				$overwritten_by = 'target_product';
				$product        = $target_product;
				$post           = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Overriding global post is safe as original $post is restored at the function end.
			}
		}

		// Get before and after components only if product is WC_Product instance.
		if ( $product instanceof \WC_Product ) {
			$default_before_component = et_()->array_get( $module_components, '__before_component', '' );
			$default_after_component  = et_()->array_get( $module_components, '__after_component', '' );
			$current_before_component = self::single_product_summary_before_module( $module_slug );
			$current_after_component  = self::single_product_summary_after_module( $module_slug );

			$module_components['has_components']     = true;
			$module_components['__before_component'] = $default_before_component . $current_before_component;
			$module_components['__after_component']  = $default_after_component . $current_after_component;
		}

		// Reset product and/or post-object.
		if ( 'dummy_product' === $overwritten_by ) {
			et_theme_builder_wc_reset_global_objects( array( 'is_tb' => true ) );
		} elseif ( 'target_product' === $overwritten_by ) {
			$product = $original_product;
			$post    = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Restoring global post.
		}

		return $module_components;
	}

	/**
	 * Renders single product summary before WooCommerce module output.
	 *
	 * This function is responsible for rendering the output before a specific WooCommerce module.
	 * It captures the output of the action hook
	 * `divi_woocommerce_single_product_summary_before_{module_slug}` and returns it as a string.
	 * This allows for additional content or modifications to be added before the module's output.
	 *
	 * This function is based on legacy `et_builder_wc_single_product_summary_before_module` function.
	 *
	 * @since ??
	 *
	 * @param string $module_slug Module slug.
	 *
	 * @return string Rendered output.
	 */
	public static function single_product_summary_before_module( string $module_slug ): string {
		ob_start();

		/**
		 * Fires additional output for single product summary before module output.
		 *
		 * @since ??
		 */
		do_action( "divi_woocommerce_single_product_summary_before_{$module_slug}" );

		return ob_get_clean();
	}

	/**
	 * Renders single product summary after WooCommerce module output.
	 *
	 * This function is responsible for rendering the output after a specific WooCommerce module.
	 * It captures the output of the action hook
	 * `divi_woocommerce_single_product_summary_after_{module_slug}` and returns it as a string.
	 * This allows for additional content or modifications to be added after the module's output.
	 *
	 * This function is based on legacy `et_builder_wc_single_product_summary_after_module` function.
	 *
	 * @since ??
	 *
	 * @param string $module_slug Module slug.
	 *
	 * @return string Rendered output.
	 */
	public static function single_product_summary_after_module( string $module_slug ): string {
		ob_start();

		/**
		 * Fires additional output for single product summary after module output.
		 *
		 * @since ??
		 */
		do_action( "divi_woocommerce_single_product_summary_after_{$module_slug}" );

		return ob_get_clean();
	}

	/**
	 * Saves the WooCommerce long description metabox content.
	 *
	 * The content is stored as post-meta w/ the key `ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY`.
	 *
	 * Legacy function: et_builder_wc_long_description_metabox_save()
	 *
	 * @since ??
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post Object.
	 * @param array   $request The $_POST Request variables.
	 *
	 * @return void
	 */
	public static function long_description_metabox_save( int $post_id, WP_Post $post, array $request ): void {
		if ( ! isset( $request['et_bfb_long_description_nonce'] ) ) {
			return;
		}

		// First, verify the nonce.
		$nonce_valid = wp_verify_nonce( $request['et_bfb_long_description_nonce'], '_et_bfb_long_description_nonce' );
		if ( ! $nonce_valid ) {
			return;
		}

		// Then, check if the user can edit posts.
		// Skip this check in test environments.
		if ( ! Conditions::is_test_env() && ! current_user_can( 'edit_posts', $post_id ) ) {
			return;
		}

		if ( 'product' !== $post->post_type ) {
			return;
		}

		if ( ! isset( $request['et_builder_wc_product_long_description'] ) ) {
			return;
		}

		$long_desc_content = $request['et_builder_wc_product_long_description'];
		update_post_meta( $post_id, ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY, wp_kses_post( $long_desc_content ) );
	}

	/**
	 * Output Callback for Product long description metabox.
	 *
	 * Legacy function: et_builder_wc_long_description_metabox_render()
	 *
	 * @since ??
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return void
	 */
	public static function long_description_metabox_render( WP_Post $post ): void {
		$settings = [
			'textarea_name' => 'et_builder_wc_product_long_description',
			'quicktags'     => [ 'buttons' => 'em,strong,link' ],
			'tinymce'       => [
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			],
			'editor_css'    => '<style>#wp-et_builder_wc_product_long_description-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
		];

		// Since we use $post_id in more than one place, use a variable.
		$post_id = $post->ID;

		// Long description metabox content. Default Empty.
		$long_desc_content = get_post_meta( $post_id, ET_BUILDER_WC_PRODUCT_LONG_DESC_META_KEY, true );
		$long_desc_content = ! empty( $long_desc_content ) ? $long_desc_content : '';

		/**
		 * Filters the wp_editor settings used in the Long description metabox.
		 *
		 * @since ??
		 *
		 * @param array $settings WP Editor settings.
		 */
		$settings = apply_filters( 'divi_woocommerce_product_long_description_editor_settings', $settings );

		wp_nonce_field( '_et_bfb_long_description_nonce', 'et_bfb_long_description_nonce' );

		wp_editor(
			$long_desc_content,
			'et_builder_wc_product_long_description',
			$settings
		);
	}

	/**
	 * Adds the Long description metabox to Product post type.
	 *
	 * Legacy function: et_builder_wc_long_description_metabox_register()
	 *
	 * @since ??
	 *
	 * @param WP_Post $post WP Post.
	 *
	 * @return void
	 */
	public static function long_description_metabox_register( WP_Post $post ): void {
		if ( 'on' !== get_post_meta( $post->ID, '_et_pb_use_builder', true ) ) {
			return;
		}

		add_meta_box(
			'et_builder_wc_product_long_description_metabox',
			__( 'Product long description', 'et_builder' ),
			[ self::class, 'long_description_metabox_render' ],
			'product',
			'normal'
		);
	}

	/**
	 * Strip Builder shortcodes to avoid nested parsing.
	 *
	 * Legacy function: et_builder_avoid_nested_shortcode_parsing()
	 *
	 * @see   https://github.com/elegantthemes/Divi/issues/18682
	 *
	 * @since ??
	 *
	 * @param string $content Post content.
	 *
	 * @return string
	 */
	public static function avoid_nested_shortcode_parsing( string $content ): string {
		if ( is_et_pb_preview() ) {
			return $content;
		}

		// Strip shortcodes only on non-builder pages that contain Builder shortcodes.
		if ( et_pb_is_pagebuilder_used( get_the_ID() ) ) {
			return $content;
		}

		// WooCommerce layout loads when builder is not enabled.
		// So strip builder shortcodes from Post content.
		if ( function_exists( 'is_product' ) && is_product() ) {
			return et_strip_shortcodes( $content );
		}

		// Strip builder shortcodes from non-product pages.
		// Only Tabs shortcode is checked since that causes nested rendering.
		if ( has_shortcode( $content, 'et_pb_wc_tabs' ) ) {
			return et_strip_shortcodes( $content );
		}

		return $content;
	}

	/**
	 * Deletes PRODUCT_PAGE_CONTENT_STATUS_META_KEY when Builder is OFF.
	 *
	 * The deletion allows switching between Divi Builder and the GB builder smoothly.
	 *
	 * Legacy function: et_builder_wc_delete_post_meta()
	 *
	 * @link https://github.com/elegantthemes/Divi/issues/22477
	 *
	 * @since ??
	 *
	 * @param WP_Post $post Post Object.
	 *
	 * @return void
	 */
	public static function delete_post_meta( $post ): void {
		if ( ! ( $post instanceof \WP_Post ) ) {
			return;
		}

		if ( et_pb_is_pagebuilder_used( $post->ID ) ) {
			return;
		}

		delete_post_meta( $post->ID, ET_BUILDER_WC_PRODUCT_PAGE_CONTENT_STATUS_META_KEY );
	}

}
