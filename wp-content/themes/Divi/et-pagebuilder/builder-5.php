<?php
/**
 * Builder-5 Helpers.
 *
 * @package Divi
 * @since ??
 */

define( 'ET_BUILDER_5_DIR', get_template_directory() . '/includes/builder-5/' );
define( 'ET_BUILDER_5_URI', get_template_directory_uri() . '/includes/builder-5' );

if ( ! function_exists( 'et_builder_d5_enabled' ) ) :
	/**
	 * Check whether D5 is enabled.
	 *
	 * @since ?? Removed the `et_enable_d5` option check because we no longer let people use the Divi 4 Visual Builder.
	 * @since 5.0.0-dev-alpha.10
	 *
	 * @return bool
	 */
	function et_builder_d5_enabled(): bool {
		static $enabled;

		// Early return if `et_builder_d5_enabled` was previously run, so that
		// we don't apply the `et_builder_d5_enabled` filter more than once.
		if ( isset( $enabled ) ) {
			return $enabled;
		}

		// Defining this here for clarity during doc generation.
		$enabled = true;

		/**
		 * Filter for D5 activation status
		 *
		 * If the `$enabled` variable has just been set, then pass its value
		 * here (but use the filter to allow other code to override this).
		 *
		 * @since 5.0.0-dev-alpha.10
		 *
		 * @param bool $enabled
		 */
		$enabled = apply_filters( 'et_builder_d5_enabled', $enabled );

		return $enabled;
	}
endif;

/**
 * Determine if WooCommerce blocks should be disabled based on various conditions.
 *
 * @since ??
 *
 * @return bool True if WooCommerce blocks should be disabled, false otherwise.
 */
function et_builder_should_disable_woocommerce_blocks(): bool {
	if ( ! class_exists( 'WooCommerce', false ) ) {
		return false;
	}

	static $should_disable_woocommerce_blocks = null;

	if ( null === $should_disable_woocommerce_blocks ) {
		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification not required.
		$is_vb_enabled   = isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'];
		$is_ajax_request = defined( 'DOING_AJAX' ) && DOING_AJAX;
		$request_uri     = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		$has_x_wp_nonce  = isset( $_SERVER['HTTP_X_WP_NONCE'] );

		/*
		 * The REST_REQUEST constant is defined in `parse_request` action only, which is why we're looking into
		 * REQUEST_URI as the fallback checks.
		 */
		$is_rest_api_request = defined( 'REST_REQUEST' ) && REST_REQUEST || 0 === strpos( $request_uri, '/' . rest_get_url_prefix() ) || false !== strpos( $request_uri, '?rest_route=/' );

		if (
			$is_vb_enabled ||
			$is_rest_api_request ||
			$has_x_wp_nonce ||
			$is_ajax_request
		) {
			$should_disable_woocommerce_blocks = true;
		} else {
			$should_disable_woocommerce_blocks = false;
		}

		/**
		 * Filter whether to disabled WooCommerce blocks.
		 *
		 * This filter is used to determine whether to disable all WooCommerce blocks on this page load.
		 *
		 * @since ??
		 *
		 * @param bool $should_disable_woocommerce_blocks
		 */
		$should_disable_woocommerce_blocks = apply_filters( 'et_builder_should_disable_woocommerce_blocks', $should_disable_woocommerce_blocks );
	}

	return (bool) $should_disable_woocommerce_blocks;
}

/**
 * Load D5 file.
 *
 * @since ??
 */
function et_setup_builder_5() {
	require_once ET_BUILDER_5_DIR . 'server/bootstrap.php';
}
add_action( 'init', 'et_setup_builder_5', 0 );

/**
 * Load D4 shortcode framework if d5 vb should be loaded.
 */
function et_setup_builder_5_shortcode_framework() {
	$is_vb_enabled = et_core_is_fb_enabled();
	$is_app_window = isset( $_GET['app_window'] ) && '1' === $_GET['app_window'];
	$has_x_wp_nonce = isset( $_SERVER['HTTP_X_WP_NONCE'] );

	$should_load_shortcode_framework = false;

	if (
		( $is_vb_enabled && $is_app_window )
		|| $has_x_wp_nonce
		// TODO, should be able to make this more specific to One or
		// many of these, or similar, conditions are needed for
		// the GB -> Use Divi Builder button to work,
		// to trigger auto-activation of the VB.
		// || ( $has_x_wp_nonce && $is_editpost_action )
		// || ( $has_x_wp_nonce && $is_post_php_edit )
		// || ( $has_x_wp_nonce && $is_rest_request )
	) {
		$should_load_shortcode_framework = true;
	}

	if ( $should_load_shortcode_framework ) {
		add_filter( 'et_should_load_shortcode_framework', '__return_true' );
	}
}
add_action( 'init', 'et_setup_builder_5_shortcode_framework', -1 );

/**
 * Remove WordPress block assets on Divi Builder.
 *
 * @since   ??
 */
function et_builder_remove_wp_block_assets() {
	// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification not required.
	$is_vb_enabled  = isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'];
	$has_x_wp_nonce = isset( $_SERVER['HTTP_X_WP_NONCE'] );

	if ( $is_vb_enabled || $has_x_wp_nonce ) {
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

		remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
	}
}

add_filter( 'init', 'et_builder_remove_wp_block_assets', 1000 );

/**
 * Clear WooCommerce block types.
 *
 * @since ??
 *
 * @param array $blocks WooCommerce block types.
 *
 * @return array An empty array to clear WooCommerce block types.
 */
function et_builder_clear_woocommerce_get_block_types( array $blocks ): array {
	if ( ! et_builder_should_disable_woocommerce_blocks() ) {
		return $blocks;
	}

	return [];
}

add_filter( 'woocommerce_get_block_types', 'et_builder_clear_woocommerce_get_block_types', 1000 );

/**
 * Disable WooCommerce blocks by removing actions and filters related to WooCommerce blocks.
 *
 * @since ??
 */
function et_builder_disable_woocommerce_blocks() {
	$asset_data_registry    = \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class;
	$asset_controller       = \Automattic\WooCommerce\Blocks\AssetsController::class;
	$block_types_controller = \Automattic\WooCommerce\Blocks\BlockTypesController::class;
	$block_pattern          = \Automattic\WooCommerce\Blocks\BlockPatterns::class;
	$package                = \Automattic\WooCommerce\Blocks\Package::class;

	if ( ! et_builder_should_disable_woocommerce_blocks() || ! class_exists( $package, false ) ) {
		return;
	}

	// Remove WooCommerce block script data.
	if ( class_exists( $asset_data_registry, false ) ) {
		remove_action( 'init', [ $package::container()->get( $asset_data_registry ), 'register_data_script' ] );
		remove_action( is_admin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts', [ $package::container()->get( $asset_data_registry ), 'enqueue_asset_data' ] );
	}

	// Remove WooCommerce block assets.
	if ( class_exists( $asset_data_registry, false ) ) {
		remove_action( 'init', [ $package::container()->get( $asset_controller ), 'register_assets' ] );
	}

	// Remove WooCommerce blocks.
	if ( class_exists( $block_types_controller, false ) ) {
		remove_action( 'init', [ $package::container()->get( $block_types_controller ), 'register_blocks' ] );
		remove_action( 'wp_loaded', [ $package::container()->get( $block_types_controller ), 'register_block_patterns' ] );
		remove_action( 'init', [ $package::container()->get( $block_types_controller ), 'block_categories_all' ], 10, 2 );
		remove_action( 'render_block', [ $package::container()->get( $block_types_controller ), 'add_data_attributes' ], 10, 2 );

		remove_filter(
			'woocommerce_is_checkout',
			function ( $ret ) use ( $package, $block_types_controller ) {
				return $ret || $package::container()->get( $block_types_controller )->has_block_variation( 'woocommerce/classic-shortcode', 'shortcode', 'checkout' );
			}
		);
		remove_filter(
			'woocommerce_is_cart',
			function ( $ret ) use ( $package, $block_types_controller ) {
				return $ret || $package::container()->get( $block_types_controller )->has_block_variation( 'woocommerce/classic-shortcode', 'shortcode', 'cart' );
			}
		);
	}

	// Remove WooCommerce block patterns.
	if ( class_exists( $block_pattern, false ) ) {
		remove_action( 'init', [ $package::container()->get( $block_pattern ), 'register_blocks' ] );
		remove_action( 'init', [ $package::container()->get( $block_pattern ), 'register_block_patterns' ] );
		remove_action( 'init', [ $package::container()->get( $block_pattern ), 'register_ptk_patterns' ] );
	}
}

add_action( 'woocommerce_init', 'et_builder_disable_woocommerce_blocks', 1000 );
