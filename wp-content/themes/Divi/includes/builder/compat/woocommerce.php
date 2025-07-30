<?php

use ET\Builder\FrontEnd\Assets\DetectFeature;
use ET\Builder\Packages\WooCommerce\WooCommerceHooks;
use ET\Builder\Packages\WooCommerce\WooCommerceUtils;

$GLOBALS['et_builder_used_in_wc_shop'] = false;

/**
 * Determines if current page is WooCommerce's shop page + uses builder.
 * NOTE: This has to be used after pre_get_post (et_builder_wc_pre_get_posts).
 *
 * @return bool
 */
function et_builder_used_in_wc_shop(): bool {
	static $cached_result = null;

	// If the result is already cached, return it.
	if ( null !== $cached_result ) {
		return $cached_result;
	}

	global $et_builder_used_in_wc_shop;

	// Calculate the result and store it in the static variable.
	$cached_result = apply_filters(
		'et_builder_used_in_wc_shop',
		$et_builder_used_in_wc_shop
	);

	return $cached_result;
}

/**
 * Use page.php as template for a page which uses builder & being set as shop page
 *
 * @param string $template path to template.
 *
 * @return string modified path to template
 */
function et_builder_wc_template_include( string $template ): string {
	if ( et_builder_used_in_wc_shop() && '' !== locate_template( 'page.php' ) ) {
		$template = locate_template( 'page.php' );
	}

	return $template;
}
add_filter( 'template_include', 'et_builder_wc_template_include', 20 );

/**
 * Overwrite WooCommerce's custom query in shop page if the page uses builder.
 * After proper shop page setup (page selection + permalink flushed), the original
 * page permalink will be recognized as is_post_type_archive by WordPress' rewrite
 * URL when it is being parsed. This causes is_page() detection fails and no way
 * to get actual page ID on pre_get_posts hook, unless by doing reverse detection:
 *
 * 1. Check if current page is product archive page. Most page will fail on this.
 * 2. Afterward, if wc_get_page_id( 'shop' ) returns a page ID, it means that
 *    current page is shop page (product post type archive) which is configured
 *    in custom page. Next, check whether Divi Builder is used on this page or not.
 *
 * @param WP_Query $query query object.
 */
function et_builder_wc_pre_get_posts( WP_Query $query ) {
	global $et_builder_used_in_wc_shop;

	// Early bail if unnecessary to process.
	if ( is_admin() || ! $query->is_main_query() || $query->is_search() ) {
		return;
	}

	// Check requirements for WooCommerce and shop-related functions.
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}

	// Bail early if not on WooCommerce product archive to avoid unnecessary processing.
	if ( ! is_post_type_archive( 'product' ) ) {
		return;
	}

	// Fetch the shop page ID and ensure it exists.
	$shop_page_id = wc_get_page_id( 'shop' );
	if ( $shop_page_id <= 0 ) { // Guard against invalid IDs.
		return;
	}

	// Fetch the shop page object in a single call and validate it.
	$shop_page_object = get_post( $shop_page_id );
	if ( empty( $shop_page_object ) || 'page' !== $shop_page_object->post_type ) {
		return;
	}

	// Check if the shop page uses the builder, bail if not.
	if ( ! et_pb_is_pagebuilder_used( $shop_page_id ) ) {
		return;
	}

	// Set et_builder_used_in_wc_shop() global to true.
	$et_builder_used_in_wc_shop = true;

	// Overwrite page query. This overwrite enables is_page() and other standard
	// page-related function to work normally after pre_get_posts hook.
	$query->set( 'page_id', $shop_page_id );
	$query->set( 'post_type', 'page' );
	$query->set( 'posts_per_page', 1 );
	$query->set( 'wc_query', null );
	$query->set( 'meta_query', [] );

	// Correct query flags to enforce proper behavior.
	$query->is_singular          = true;
	$query->is_page              = true;
	$query->is_post_type_archive = false;
	$query->is_archive           = false;

	// Remove unwanted automatic paragraph tags in builder-rendered content.
	remove_filter( 'the_content', 'wpautop' );
}
add_action( 'pre_get_posts', 'et_builder_wc_pre_get_posts' );

/**
 * Remove woocommerce body classes if current shop page uses builder.
 * woocommerce-page body class causes builder's shop column styling to be irrelevant.
 *
 * @param array $classes body classes.
 *
 * @return array modified body classes
 */
function et_builder_wc_body_class( array $classes ): array {
	if ( et_builder_used_in_wc_shop() ) {
		$classes = array_diff( $classes, array( 'woocommerce-page' ) );
	}

	return $classes;
}

add_filter( 'body_class', 'et_builder_wc_body_class' );

/**
 * Determine whether given content has WooCommerce module inside it or not.
 *
 * @param string $content Content.
 *
 * @return bool
 */
function et_builder_has_woocommerce_module( string $content = '' ): bool {
	// Bail early if the content is empty to avoid unnecessary processing.
	if ( '' === $content ) {
		return false;
	}

	// Static cache to store results for given content.
	static $cache = [];

	// Generate a unique cache key using the md5 hash of the content.
	$cache_key = md5( $content );

	// Check if the result is already cached.
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	// Call to DetectFeature::has_woocommerce_module_shortcode to check if the content has a WooCommerce module.
	$has_woocommerce_module = DetectFeature::has_woocommerce_module_shortcode( $content );

	// Apply a WordPress filter to allow modification of the result.
	$has_woocommerce_module = apply_filters( 'et_builder_has_woocommerce_module', $has_woocommerce_module );

	// Cache the result for subsequent calls.
	$cache[ $cache_key ] = $has_woocommerce_module;

	return $has_woocommerce_module;
}

/**
 * Check if current global $post uses builder / layout block, not `product` CPT, and contains
 * WooCommerce module inside it. This check is needed because WooCommerce by default only adds
 * scripts and style to `product` CPT while WooCommerce Modules can be used at any CPT
 *
 * @since ?? Deprecated. Please use \ET\Builder\Packages\WooCommerce\WooCommerceUtils::is_non_product_post_type instead.
 * @since 4.1.0 check if layout block is used instead of builder
 * @since 3.29
 *
 * @return bool
 * @deprecated
 */
function et_builder_wc_is_non_product_post_type(): bool {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\Packages\WooCommerce\WooCommerceUtils::is_non_product_post_type() instead.' );

	return WooCommerceUtils::is_non_product_post_type();
}

/**
 * Add WooCommerce body class name on non `product` CPT builder page.
 *
 * @since ?? Deprecated. Please use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_body_class instead.
 * @since 3.29
 *
 * @param array $classes CSS class names.
 *
 * @return array
 * @deprecated
 */
function et_builder_wc_add_body_class( array $classes ): array {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_body_class() instead.' );

	return WooCommerceHooks::add_body_class( $classes );
}

/**
 * Add product class name on inner content wrapper page on non `product` CPT builder page with woocommerce modules
 * And on Product posts.
 *
 * @since ?? Deprecated. Please use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_inner_content_class instead.
 * @since 3.29
 *
 * @param array $classes Product class names.
 *
 * @return array
 * @deprecated
 */
function et_builder_wc_add_inner_content_class( array $classes ): array {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_inner_content_class() instead.' );

	return WooCommerceHooks::add_inner_content_class( $classes );
}

/**
 * Adds the Preview class to the wrapper.
 *
 * @since ?? Deprecated. Please use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_preview_wrap_class instead.
 *
 * @param string $maybe_class_string Classnames string.
 *
 * @return string
 * @deprecated
 */
function et_builder_wc_add_preview_wrap_class( string $maybe_class_string ): string {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_preview_wrap_class() instead.' );

	return WooCommerceHooks::add_preview_wrap_class( $maybe_class_string );
}

/**
 * Add WooCommerce class names on Divi Shop Page (not WooCommerce Shop).
 *
 * @since ?? Deprecated. Please use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_outer_content_class instead.
 * @since 4.0.7
 *
 * @param array $classes Array of Classes.
 *
 * @return array
 * @deprecated
 */
function et_builder_wc_add_outer_content_class( array $classes ): array {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\Packages\WooCommerce\WooCommerceHooks::add_outer_content_class() instead.' );

	return WooCommerceHooks::add_outer_content_class( $classes );
}
