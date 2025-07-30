<?php
/**
 * Utility class for WooCommerce-related operations.
 *
 * This class serves as a container for utility methods and functionality
 * specific to interacting with WooCommerce.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\WooCommerce;

use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\Assets\DynamicAssets;
use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;
use ET\Builder\Packages\Conversion\Conversion;
use ET\Builder\VisualBuilder\SettingsData\SettingsDataCallbacks;
use ET_Core_Data_Utils;
use ET_Theme_Builder_Layout;
use ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\CartNotice\WooCommerceCartNoticeModule;
use stdClass;
use WP_Post;
use WP_Query;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Utility class for various WooCommerce-related operations and helper functions.
 *
 * @since ??
 */
class WooCommerceUtils {

	/**
	 * An array of allowed WooCommerce functions that can be safely called from templates.
	 * This list acts as a security allowlist to prevent unauthorized function execution.
	 *
	 * @since ??
	 *
	 * @var string[]
	 */
	private static $_allowed_functions = [
		'the_title',
		'woocommerce_breadcrumb',
		'woocommerce_template_single_price',
		'woocommerce_template_single_add_to_cart',
		'woocommerce_product_additional_information_tab',
		'woocommerce_template_single_meta',
		'woocommerce_template_single_rating',
		'woocommerce_show_product_images',
		'wc_get_stock_html',
		'wc_print_notices',
		'wc_print_notice',
		'woocommerce_output_related_products',
		'woocommerce_upsell_display',
		'woocommerce_checkout_login_form',
		'wc_cart_empty_template',
		'woocommerce_output_all_notices',
	];

	/**
	 * The current REST request query params.
	 *
	 * @since ??
	 *
	 * @var array|null
	 */
	private static $_current_rest_request_query_params = [];

	/**
	 * Check if current global $post uses builder / layout block, not `product` CPT, and contains
	 * WooCommerce module inside it. This check is needed because WooCommerce by default only adds
	 * scripts and style to `product` CPT while WooCommerce Modules can be used at any CPT.
	 *
	 * Based on legacy `et_builder_wc_is_non_product_post_type` function.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_non_product_post_type(): bool {
		static $is_non_product_post_type;

		// If the result is already cached, return it immediately.
		if ( null !== $is_non_product_post_type ) {
			return $is_non_product_post_type;
		}

		// Bail early for specific request types (e.g., AJAX requests, REST API requests, or VB top window requests).
		if ( Conditions::is_ajax_request() || Conditions::is_rest_api_request() || Conditions::is_vb_top_window() ) {
			$is_non_product_post_type = false;
			return $is_non_product_post_type;
		}

		global $post;

		// If the global $post is missing or is a WooCommerce 'product', immediately return false.
		if ( empty( $post ) || 'product' === $post->post_type ) {
			$is_non_product_post_type = false;
			return $is_non_product_post_type;
		}

		// Skip further checks if the builder or layout block isn't used.
		$is_builder_used           = et_pb_is_pagebuilder_used( $post->ID );
		$is_layout_block_used      = has_block( 'divi/layout', $post->post_content );
		$is_builder_or_layout_used = $is_builder_used || $is_layout_block_used;

		if ( ! $is_builder_used && ! $is_layout_block_used ) {
			$is_non_product_post_type = false;
			return $is_non_product_post_type;
		}

		// Check if the WooCommerce module is used in the post-content.
		$has_wc_module_block     = DynamicAssets::get_instance()->has_woocommerce_module_block();
		$has_wc_module_shortcode = DynamicAssets::get_instance()->has_woocommerce_module_shortcode();
		$has_woocommerce_module  = $has_wc_module_block || $has_wc_module_shortcode;

		// Set the result based on the above checks.
		$is_non_product_post_type = $is_builder_or_layout_used && $has_woocommerce_module;

		return $is_non_product_post_type;
	}

	/**
	 * Returns TRUE if the Product attribute value is valid.
	 *
	 * Valid values are Product Ids, `current` and `latest`.
	 *
	 * @since ??
	 *
	 * @param string $maybe_product_id Product ID.
	 *
	 * @return bool
	 */
	public static function is_product_attr_valid( string $maybe_product_id ): bool {
		if ( empty( $maybe_product_id ) ) {
			return false;
		}

		if (
			absint( $maybe_product_id ) === 0
			&& ! in_array( $maybe_product_id, [ 'current', 'latest' ], true )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the default column settings for WooCommerce posts.
	 *
	 * This method applies the 'divi_woocommerce_get_default_columns' filter
	 * to determine and return the default column configuration.
	 *
	 * Based on the legacy `get_columns_posts_default` function.
	 *
	 * @since ??
	 *
	 * @return string The default column configuration for WooCommerce posts, as filtered by the applied hook.
	 */
	public static function get_default_columns_posts(): string {
		// Get the value for columns.
		$columns = self::get_default_columns_posts_value();

		/**
		 * Filters the default column configuration for WooCommerce posts.
		 *
		 * @since ??
		 *
		 * @param string $columns The default column configuration for WooCommerce posts.
		 */
		return apply_filters( 'divi_woocommerce_get_default_columns', $columns );
	}

	/**
	 * Retrieves the default number of columns for displaying posts.
	 *
	 * Determines the appropriate default column value based on the current page's
	 * layout and context. If the page has a sidebar, it returns a value indicative
	 * of a layout with a sidebar; otherwise, it defaults to standard values often
	 * influenced by WooCommerce settings.
	 *
	 * Based on the legacy `get_columns_posts_default_value` function.
	 *
	 * @since ??
	 *
	 * @return string The number of columns as a string. Returns '3' for layouts
	 *                with a sidebar or '4' as the default value.
	 */
	public static function get_default_columns_posts_value(): string {
		$post_id = et_core_page_resource_get_the_ID();

		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification is not required.
		$post_id = (int) $post_id ? $post_id : ArrayUtility::get_value( $_POST, 'current_page.id' );

		$page_layout = get_post_meta( $post_id, '_et_pb_page_layout', true );

		if ( $page_layout && 'et_full_width_page' !== $page_layout && ! ET_Theme_Builder_Layout::is_theme_builder_layout() ) {
			return '3'; // Set to 3 if page has sidebar.
		}

		/*
		* Default number is based on the WooCommerce plugin default value.
		*
		* @see woocommerce_output_related_products()
		*/
		return '4';
	}

	/**
	 * Retrieves the default product configuration.
	 *
	 * Applies a filter to allow the retrieval or modification of the default product configuration.
	 * The returned value is expected to be an associative array containing details about the default product.
	 *
	 * Based on the legacy `get_product_default` function.
	 *
	 * @since ??
	 *
	 * @return string The default product configuration. The structure and content of the array
	 *               depend on the filter `divi_woocommerce_get_default_product`.
	 */
	public static function get_default_product(): string {
		// Get the value for the $default_product.
		$default_product = self::get_default_product_value();

		/**
		 * Filters the default product configuration.
		 *
		 * @since ??
		 *
		 * @param string $default_product The default product configuration.
		 */
		return apply_filters( 'divi_woocommerce_get_default_product', $default_product );
	}

	/**
	 * Retrieves the default product value identifier.
	 *
	 * Determines the default product value based on the current context, including post type
	 * or page resource. This method assesses whether the current post type is a "product"
	 * or a "theme builder layout" and returns an appropriate default value.
	 *
	 * Based on the legacy `get_product_default_value` function.
	 *
	 * @since ??
	 *
	 * @return string The default product value, either 'current' if the context relates to
	 *                a product or theme builder layout, or 'latest' as a fallback.
	 */
	public static function get_default_product_value(): string {
		$post_id = Conditions::is_rest_api_request()
			? get_the_ID()
			: et_core_page_resource_get_the_ID();

		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification is not required.
		$post_id   = (int) $post_id ? $post_id : ArrayUtility::get_value( $_POST, 'current_page.id' );
		$post_type = get_post_type( $post_id );

		if ( 'product' === $post_type || et_theme_builder_is_layout_post_type( $post_type ) ) {
			return 'current';
		}

		return 'latest';
	}

	/**
	 * Retrieves the default WooCommerce tabs.
	 *
	 * This method returns the default WooCommerce tabs, allowing filters
	 * to modify the data before it is returned. It is primarily used
	 * to obtain the currently configured set of WooCommerce product tabs.
	 *
	 * Based on the legacy `get_woo_default_tabs` function.
	 *
	 * @since ??
	 *
	 * @return array The default WooCommerce tabs after applying filters.
	 */
	public static function get_default_product_tabs(): array {
		// Get the value for the $default_tabs.
		$default_tabs = self::get_default_product_tabs_options();

		/**
		 * Filters the default WooCommerce tabs.
		 *
		 * @since ??
		 *
		 * @param array $default_tabs The default WooCommerce tabs.
		 */
		return apply_filters( 'divi_woocommerce_get_default_product_tabs', $default_tabs );
	}

	/**
	 * Retrieves default WooCommerce product tabs options.
	 *
	 * Processes the current product data, applies necessary filters, and returns
	 * a list of available WooCommerce product tabs. Handles resetting global variables
	 * after usage to maintain consistent behavior.
	 *
	 * Based on the legacy `get_woo_default_tabs_options` function.
	 *
	 * @since ??
	 *
	 * @return array Array of default WooCommerce product tabs. Returns an empty array
	 *               if no valid tabs are found, or if the current product cannot be retrieved.
	 */
	public static function get_default_product_tabs_options(): array {
		// Bail if WooCommerce is not enabled.
		if ( ! function_exists( 'wc_get_product' ) ) {
			return [];
		}

		$maybe_product_id = self::get_default_product_value();
		$product_id       = self::get_product( $maybe_product_id );

		$current_product = wc_get_product( $product_id );
		if ( ! $current_product ) {
			return [];
		}

		global $product, $post;
		$original_product = $product;
		$original_post    = $post;
		$product          = $current_product;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentionally done.
		$post = get_post( $product->get_id() );

		$tabs = apply_filters( 'woocommerce_product_tabs', [] );

		// Reset global $product.
		$product = $original_product;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentionally done.
		$post = $original_post;

		if ( ! empty( $tabs ) ) {
			return array_keys( $tabs );
		}

		return [];
	}

	/**
	 * Retrieves the WooCommerce product tabs.
	 *
	 * This method fetches the available WooCommerce product tabs by invoking the
	 * 'woocommerce_product_tabs' filter. It ensures that appropriate product context
	 * is set globally in cases where it is not already defined. If no valid product
	 * context can be established, default product tab options are returned.
	 *
	 * Based on the legacy `et_fb_woocommerce_tabs` function.
	 *
	 * @since ??
	 *
	 * @return array An associative array of product tabs, where each key is the tab name,
	 *               and each value is an array containing 'value' (tab's name) and
	 *               'label' (tab's title).
	 */
	public static function get_product_tabs_options(): array {
		global $product, $post;

		$old_product = $product;
		$old_post    = $post;
		$is_product  = isset( $product ) && is_a( $product, 'WC_Product' );

		if ( ! $is_product && Conditions::is_woocommerce_enabled() ) {
			$product = self::get_product( 'latest' );

			if ( $product ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Overriding global post is safe as the original $ post has been restored at the end.
				$post = get_post( $product->get_id() );
			} else {
				$product = $old_product;
				return self::set_default_product_tabs_options();
			}
		}

		// On non-product post-types, the filter will cause a fatal error unless we have a global $product set.
		$tabs    = apply_filters( 'woocommerce_product_tabs', [] );
		$options = array();

		foreach ( $tabs as $name => $tab ) {
			$options[ $name ] = array(
				'value' => $name,
				'label' => $tab['title'],
			);
		}

		// Reset global $product.
		$product = $old_product;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Restoring original global $post data.
		$post = $old_post;

		return $options;
	}

	/**
	 * Retrieves the default page type configuration.
	 *
	 * Provides an array of default page type settings, which can be filtered
	 * by the 'divi_woocommerce_get_default_page_type' filter.
	 *
	 * Based on the legacy `get_page_type_default` function.
	 *
	 * @since ??
	 *
	 * @return string The default page type configuration.
	 */
	public static function get_default_page_type(): string {
		// Get the value for the $default_page_type.
		$default_page_type = self::get_default_page_type_value();

		/**
		 * Filters the default page type configuration.
		 *
		 * @since ??
		 *
		 * @param string $default_page_type The default page type configuration.
		 */
		return apply_filters( 'divi_woocommerce_get_default_page_type', $default_page_type );
	}

	/**
	 * Retrieves the default page type value based on the current page.
	 *
	 * Determines the page type by checking if the current page is a cart or checkout page.
	 * If neither condition is met, it defaults to the "product" page type.
	 *
	 * Based on the legacy `get_page_type_default_value` function.
	 *
	 * @since ??
	 *
	 * @return string The determined page type, which can be "cart", "checkout", or "product".
	 */
	public static function get_default_page_type_value(): string {
		$is_cart_page     = function_exists( 'is_cart' ) && is_cart();
		$is_checkout_page = function_exists( 'is_checkout' ) && is_checkout();

		if ( $is_cart_page ) {
			return 'cart';
		} elseif ( $is_checkout_page ) {
			return 'checkout';
		} else {
			return 'product';
		}
	}

	/**
	 * Retrieves the product ID based on the provided product attribute.
	 *
	 * Determines the correct product ID to return based on the given attribute,
	 * handling cases like "current", "latest", and numeric values. If the attribute
	 * is invalid, fallback mechanisms are employed to retrieve a relevant product ID.
	 *
	 * @since ??
	 *
	 * @param string $valid_product_attr The input attribute used to determine the product ID.
	 *                                   Acceptable values include "current", "latest",
	 *                                   or a numeric product ID.
	 *
	 * @return int The determined product ID. Returns 0 if no valid product ID can be resolved.
	 */
	public static function get_product_id_by_prop( string $valid_product_attr ): int {
		if ( ! self::is_product_attr_valid( $valid_product_attr ) ) {
			return 0;
		}

		if ( 'current' === $valid_product_attr ) {
			$current_post_id = DynamicAssetsUtils::get_current_post_id();

			if ( et_theme_builder_is_layout_post_type( get_post_type( $current_post_id ) ) ) {
				// We want to use the latest product when we are editing a TB layout.
				$valid_product_attr = 'latest';
			}
		}

		if (
			! in_array( $valid_product_attr, [ 'current', 'latest' ], true )
			&& false === get_post_status( $valid_product_attr )
		) {
			$valid_product_attr = 'latest';
		}

		if ( 'current' === $valid_product_attr ) {
			$product_id = DynamicAssetsUtils::get_current_post_id();
		} elseif ( 'latest' === $valid_product_attr ) {
			$args = [
				'limit'       => 1,
				'post_status' => [ 'publish', 'private' ],
				'perm'        => 'readable',
			];

			if ( ! function_exists( 'wc_get_products' ) ) {
				return 0;
			}

			$products = wc_get_products( $args );

			if ( empty( $products ) || ! is_array( $products ) ) {
				return 0;
			}

			if ( isset( $products[0] ) && is_a( $products[0], 'WC_Product' ) ) {
				$product_id = $products[0]->get_id();
			} else {
				return 0;
			}
		} elseif ( is_numeric( $valid_product_attr ) && 'product' !== get_post_type( $valid_product_attr ) ) {
			// There is a condition that $valid_product_attr value passed here is not the product ID.
			// For example when you set product breadcrumb as Blurb Title when building layout in TB.
			// So we get the most recent product ID in date descending order.
			$query = new \WC_Product_Query(
				[
					'limit'   => 1,
					'orderby' => 'date',
					'order'   => 'DESC',
					'return'  => 'ids',
					'status'  => [ 'publish' ],
				]
			);

			$products = $query->get_products();

			if ( $products && ! empty( $products[0] ) ) {
				$product_id = absint( $products[0] );
			} else {
				$product_id = absint( $valid_product_attr );
			}
		} else {
			$product_id = absint( $valid_product_attr );
		}

		return $product_id;
	}

	/**
	 * Retrieves the product object based on the provided product identifier.
	 *
	 * Resolves the WooCommerce product object corresponding to the given product identifier.
	 * Utilizes a helper method to determine the appropriate product ID and fetches the product
	 * if it exists. Returns false if no valid product can be retrieved.
	 *
	 * @since ??
	 *
	 * @param string $maybe_product_id The input value which may represent a product ID,
	 *                                 or another attribute to determine the product.
	 *
	 * @return \WC_Product|false The WooCommerce product object if successfully resolved,
	 *                           or false if no valid product can be found.
	 */
	public static function get_product( string $maybe_product_id ) {
		$product_id = self::get_product_id_by_prop( $maybe_product_id );

		if ( ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		$product = wc_get_product( $product_id );

		if ( empty( $product ) ) {
			return false;
		}

		return $product;
	}

	/**
	 * Gets the Product layout for a given Post ID.
	 *
	 * This function retrieves the product layout associated with a specific post ID.
	 * It checks if the post exists and returns the layout value stored in the post meta.
	 *
	 * @since ??
	 *
	 * @param int $post_id This is the ID of the Post requesting the product layout.
	 *
	 * @return string|false The return value will be one of the values from
	 *                      {@see et_builder_wc_get_page_layouts()} when the
	 *                      Post ID is valid, or false if the post is not found.
	 */
	public static function get_product_layout( int $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		return get_post_meta( $post_id, ET_BUILDER_WC_PRODUCT_PAGE_LAYOUT_META_KEY, true );
	}

	/**
	 * Retrieves the product ID from a given input.
	 *
	 * Resolves and returns the product ID associated with the input. If the input does not
	 * correspond to a valid product, the function will return 0.
	 *
	 * @since ??
	 *
	 * @param string $maybe_product_id A potential product identifier that will be validated
	 *                                 and used to retrieve the corresponding product ID.
	 *
	 * @return int The ID of the resolved product. Returns 0 if the input does not correspond
	 *             to a valid product.
	 */
	public static function get_product_id( string $maybe_product_id ): int {
		$product = self::get_product( $maybe_product_id );
		if ( ! $product ) {
			return 0;
		}

		return $product->get_id();
	}

	/**
	 * Retrieves the product ID based on the provided attributes.
	 *
	 * Determines the appropriate product ID by evaluating the provided arguments,
	 * handling cases such as "latest", "current", or a specific product ID.
	 * If the provided product ID is not valid or does not exist, a fallback mechanism
	 * is used to retrieve the latest product ID.
	 *
	 * @since ??
	 *
	 * @param array $args The input arguments containing product-related attributes.
	 *                    The "product" key may have a value of "latest", "current",
	 *                    or a specific product ID.
	 *
	 * @return int The resolved product ID. Returns 0 if no valid product ID can be determined.
	 */
	public static function get_product_id_from_attributes( array $args ): int {
		$maybe_product_id        = ArrayUtility::get_value( $args, 'product', 'latest' );
		$is_latest_product       = 'latest' === $maybe_product_id;
		$is_current_product_page = 'current' === $maybe_product_id;

		if ( $is_latest_product ) {
			// Dynamic filter's product_id need to be translated into correct id.
			$product_id = self::get_product_id( $maybe_product_id );
		} elseif ( $is_current_product_page && Conditions::is_rest_api_request() ) {
			/*
			 * $product global doesn't exist in REST request; thus get the fallback post id.
			 */
			$product_id = DynamicAssetsUtils::get_current_post_id();
		} else {
			// Besides two situation above, $product_id is current $args['product'].
			if ( false !== get_post_status( $maybe_product_id ) ) {
				$product_id = $maybe_product_id;
			} else {
				// Fallback to Latest product if saved product ID doesn't exist.
				$product_id = self::get_product_id( 'latest' );
			}
		}

		return $product_id;
	}

	/**
	 * Renders module templates based on specified actions and arguments. This function manages global context and
	 * ensures proper handling of the WooCommerce environment for different module templates.
	 *
	 * Legacy function: et_builder_wc_render_module_template()
	 *
	 * @since ??
	 *
	 * @param string $function_name The action or function name to process. It must be within the allowlist of
	 *                              supported functions.
	 * @param array  $args          Optional. An array of arguments to pass to the action or function. Default
	 *                              empty array.
	 * @param array  $overwrite     Optional. An array specifying which global variables should be temporarily
	 *                              overwritten (e.g., 'product', 'post', 'wp_query'). Default includes 'product'.
	 *
	 * @return string The generated output for the module template when applicable, or an empty string if the
	 * function cannot process the requested action.
	 */
	public static function render_module_template(
		string $function_name,
		array $args = [],
		array $overwrite = [ 'product' ]
	): string {
		// Bail early.
		if ( is_admin() && ! Conditions::is_rest_api_request() ) {
			return '';
		}

		// Check if passed function name is allowlisted or not.
		if ( ! in_array( $function_name, self::$_allowed_functions, true ) ) {
			return '';
		}

		// phpcs:disable WordPress.WP.GlobalVariablesOverride -- Overwrite global variables when rendering templates which are restored before this function exist.
		global $product, $post, $wp_query;

		/*
		 * TODO feat(D5, WooCommerce Imaegs Module): $defaults should be in D5 attribute, will be refactored in https://github.com/elegantthemes/Divi/issues/42668
		 */
		$defaults = [
			'product' => 'current',
		];

		$args               = wp_parse_args( $args, $defaults );
		$overwrite_global   = self::need_overwrite_global( $args['product'] );
		$overwrite_product  = in_array( 'product', $overwrite, true );
		$overwrite_post     = in_array( 'post', $overwrite, true );
		$overwrite_wp_query = in_array( 'wp_query', $overwrite, true );
		$is_tb              = et_builder_tb_enabled();
		$is_use_placeholder = $is_tb || is_et_pb_preview();

		if ( $is_use_placeholder ) {
			// global object needs to be set before output rendering. This needs to be performed on each
			// module template rendering instead of once for all module template rendering because some
			// module's template rendering uses `wp_reset_postdata()` which resets global query.
			self::set_global_objects_for_theme_builder();
		} elseif ( $overwrite_global ) {
			$product_id = self::get_product_id_from_attributes( $args );

			if ( 'product' !== get_post_type( $product_id ) ) {
				// We are in a Theme Builder layout and the current post is not a product - use the latest one instead.
				$products = new WP_Query(
					[
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
						'no_found_rows'  => true,
					]
				);

				if ( ! $products->have_posts() ) {
					return '';
				}

				$product_id = $products->posts[0]->ID;
			}

			// Overwrite product.
			if ( $overwrite_product ) {
				$original_product = $product;
				$product          = wc_get_product( $product_id );
			}

			// Overwrite post.
			if ( $overwrite_post ) {
				$original_post = $post;
				$post          = get_post( $product_id );
			}

			// Overwrite wp_query.
			if ( $overwrite_wp_query ) {
				$original_wp_query = $wp_query;
				$wp_query          = new WP_Query( [ 'p' => $product_id ] );
			}
		}

		ob_start();

		switch ( $function_name ) {
			case 'woocommerce_breadcrumb':
				if ( is_a( $product, 'WC_Product' ) ) {
					$breadcrumb_separator = $args['breadcrumb_separator'] ?? '';
					$breadcrumb_separator = str_replace( '&#8221;', '', $breadcrumb_separator );

					woocommerce_breadcrumb(
						[
							'delimiter' => ' ' . $breadcrumb_separator . ' ',
							'home'      => $args['breadcrumb_home_text'] ?? '',
						]
					);
				}
				break;
			case 'woocommerce_show_product_images':
				if ( is_a( $product, 'WC_Product' ) ) {
					// WC Images module needs to modify global variable's property.
					// This is done here instead of the module class since the $product global might be modified.
					$gallery_ids     = $product->get_gallery_image_ids();
					$image_id        = $product->get_image_id();
					$show_image      = 'on' === $args['show_product_image'];
					$show_gallery    = 'on' === $args['show_product_gallery'];
					$show_sale_badge = 'on' === $args['show_sale_badge'];

					// If featured image is disabled, and gallery is enabled, replace it with first gallery image's ID.
					// If featured image is disabled, and gallery is disabled, replace it with empty string.
					if ( ! $show_image ) {
						if ( $show_gallery && isset( $gallery_ids[0] ) ) {
									$product->set_image_id( $gallery_ids[0] );

									// Remove first image from the gallery because it'll be added as thumbnail and will be duplicated.
									unset( $gallery_ids[0] );
									$product->set_gallery_image_ids( $gallery_ids );
						} else {
							$product->set_image_id( '' );
						}
					}

					// Replace gallery image IDs with an empty array if gallery is disabled.
					if ( ! $show_gallery ) {
						$product->set_gallery_image_ids( array() );
					}

					if ( $show_sale_badge && function_exists( 'woocommerce_show_product_sale_flash' ) ) {
						woocommerce_show_product_sale_flash();
					}

          // @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Only whitelisted functions reach here.
					call_user_func( $function_name );

					// Reset product's actual featured image ID.
					if ( ! $show_image ) {
						$product->set_image_id( $image_id );
					}

					// Reset product's actual gallery image ID.
					if ( ! $show_gallery ) {
						$product->set_gallery_image_ids( $gallery_ids );
					}
				}
				break;
			case 'woocommerce_template_single_price':
			case 'woocommerce_template_single_meta':
				if ( is_a( $product, 'WC_Product' ) ) {
					$function_name();
				}
				break;
			case 'wc_get_stock_html':
				if ( is_a( $product, 'WC_Product' ) ) {
					echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput -- `wc_get_stock_html` used to include WooCommerce's `single-product/stock.php` template.
				}
				break;
			case 'wc_print_notice':
				$message = ArrayUtility::get_value( $args, 'wc_cart_message', '' );

				// @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Functions that reach here are whitelisted.
				call_user_func( $function_name, $message );
				break;
			case 'wc_print_notices':
				if ( isset( WC()->session ) ) {
					// Save existing notices to restore them as many times as we need.
					$et_wc_cached_notices = WC()->session->get( 'wc_notices', array() );

					// @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Functions that reach here are whitelisted.
					call_user_func( $function_name );

					// Restore notices which were removed after wc_print_notices() executed to render multiple modules on page.
					if ( ! empty( $et_wc_cached_notices ) && empty( WC()->session->get( 'wc_notices', array() ) ) ) {
						WC()->session->set( 'wc_notices', $et_wc_cached_notices );
					}
				}
				break;
			case 'woocommerce_checkout_login_form':
				if ( function_exists( 'woocommerce_checkout_login_form' ) ) {
					woocommerce_checkout_login_form();
				}
				if ( function_exists( 'woocommerce_checkout_coupon_form' ) ) {
					woocommerce_checkout_coupon_form();
				}

				$is_builder = ArrayUtility::get_value( $args, 'is_builder', false );
				if ( $is_builder ) {
					WooCommerceCartNoticeModule::output_coupon_error_message();
				}
				break;
			case 'woocommerce_upsell_display':
			  // @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Only whitelisted functions reach here.
				call_user_func( $function_name, '', '', '', $args['order'] ?? '' );
				break;
			case 'wc_cart_empty_template':
				wc_get_template( 'cart/cart-empty.php' );
				break;
			case 'woocommerce_output_all_notices':
				if ( isset( WC()->session ) ) {
					// Save existing notices to restore them as many times as we need.
					$et_wc_cached_notices = WC()->session->get( 'wc_notices', array() );

					if ( function_exists( $function_name ) ) {
						// @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Functions that reach here are whitelisted.
						call_user_func( $function_name );
					}

					// Restore notices which were removed after wc_print_notices() executed to render multiple modules on page.
					if ( ! empty( $et_wc_cached_notices ) && empty( WC()->session->get( 'wc_notices', array() ) ) ) {
						WC()->session->set( 'wc_notices', $et_wc_cached_notices );
					}
				}
				break;
			default:
				// Only whitelisted functions shall be allowed until this point of execution.
				if ( is_a( $product, 'WC_Product' ) ) {
					// @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Only whitelisted functions reach here.
					call_user_func( $function_name );
				}
		}

		$output = ob_get_clean();

		// Reset original product variable to global $product.
		if ( $is_use_placeholder ) {
			self::reset_global_objects_for_theme_builder();
		} elseif ( $overwrite_global ) {
			// Reset $product global.
			if ( $overwrite_product ) {
				$product = $original_product;
			}

			// Reset post.
			if ( $overwrite_post ) {
				$post = $original_post;
			}

			// Reset wp_query.
			if ( $overwrite_wp_query ) {
				$wp_query = $original_wp_query;
			}
			// phpcs:enable WordPress.WP.GlobalVariablesOverride -- Enable global variable override check.
		}

		return $output;
	}

	/**
	 * Determines if WooCommerce's `$product` global needs to be overwritten.
	 *
	 * IMPORTANT: Ensure that the `$product` global is reset to its original state after use.
	 * Overwriting the global is necessary in specific scenarios to avoid using incorrect
	 * or stale product information.
	 *
	 * @since ??
	 *
	 * @param string $product_id Product ID to check against. Defaults to 'current', which means
	 *                           the current product page is being referenced.
	 *
	 * @return bool True if the global `$product` needs to be overwritten, false otherwise.
	 */
	public static function need_overwrite_global( string $product_id = 'current' ): bool {
		// Check if the provided product ID corresponds to the current product page.
		$is_current_product_page = 'current' === $product_id;

		/*
		 * The global `$product` variable needs to be overwritten in the following scenarios:
		 *
		 * 1. The specified `$product_id` is not for the current product page.
		 *
		 * 2. The current request is a WordPress REST API request.
		 *    This includes:
		 *    - Any REST requests made during AJAX calls (such as VB actions),
		 *      where the global `$product` is often inconsistent or incorrect.
		 *    - Special requests with `?rest_route=/` or prefixed with the REST API base URL.
		 */
		$need_overwrite_global = ! $is_current_product_page || Conditions::is_rest_api_request();

		// Return true if a global overwrite is needed, otherwise false.
		return $need_overwrite_global;
	}

	/**
	 * Sets the default product tabs for WooCommerce products.
	 *
	 * Defines the structure and properties of the default WooCommerce product tabs,
	 * including their titles, display priority, and callback functions for rendering
	 * the tab content. If Theme Builder is enabled, additional processing is performed
	 * to apply any customizations to the product tabs.
	 *
	 * Based on the legacy `get_default_product_tabs` function.
	 *
	 * @since ??
	 *
	 * @return array An array of default product tabs with their respective configurations.
	 */
	public static function set_default_product_tabs(): array {
		$tabs = [
			'description'            => [
				'title'    => esc_html__( 'Description', 'et_builder' ),
				'priority' => 10,
				'callback' => 'woocommerce_product_description_tab',
			],
			'additional_information' => [
				'title'    => esc_html__( 'Additional information', 'et_builder' ),
				'priority' => 20,
				'callback' => 'woocommerce_product_additional_information_tab',
			],
			'reviews'                => [
				'title'    => esc_html__( 'Reviews', 'et_builder' ),
				'priority' => 30,
				'callback' => 'comments_template',
			],
		];

		// Add custom tabs on default for theme builder.
		if ( et_builder_tb_enabled() ) {
			self::set_global_objects_for_theme_builder();

			$tabs = apply_filters( 'woocommerce_product_tabs', $tabs );

			self::reset_global_objects_for_theme_builder();
		}

		return $tabs;
	}

	/**
	 * Sets default product tabs options.
	 *
	 * Processes the default product tabs to generate an array of options
	 * containing tab names, values, and labels. Each option corresponds
	 * to a tab with a title attribute. Special handling is applied for the
	 * "reviews" tab to set its label.
	 *
	 * Based on the legacy `get_default_tab_options` function.
	 *
	 * @since ??
	 *
	 * @return array An associative array of default product tab options,
	 *               where each key represents a tab name and its value
	 *               contains value-label pairs. Returns an empty array
	 *               if no valid tabs are available.
	 */
	public static function set_default_product_tabs_options(): array {
		$tabs    = self::set_default_product_tabs();
		$options = [];

		foreach ( $tabs as $name => $tab ) {
			if ( ! isset( $tab['title'] ) ) {
				continue;
			}

			$options[ $name ] = [
				'value' => $name,
				'label' => 'reviews' === $name
					? esc_html__( 'Reviews', 'et_builder' )
					: esc_html( $tab['title'] ),
			];
		}

		return $options;
	}

	/**
	 * Sets global objects for the theme builder.
	 *
	 * Configures global variables and placeholders to ensure compatibility and rendering
	 * functionality within the theme builder. This includes preparing global `$product` and
	 * `$post` objects with correct placeholder or existing values.
	 *
	 * Based on the legacy `et_theme_builder_wc_set_global_objects` function.
	 *
	 * @since ??
	 *
	 * @param array $conditional_tags Associative array of conditional tags used for internal checks.
	 *                                Example keys:
	 *                                - 'is_tb' (bool): Whether the current request is related to the theme builder.
	 *
	 * @return void
	 */
	public static function set_global_objects_for_theme_builder( array $conditional_tags = [] ) {
		$is_tb              = $conditional_tags['is_tb'] ?? false;
		$is_use_placeholder = $is_tb || is_et_pb_preview();

		// Check if current request is theme builder (direct page / AJAX request).
		if ( ! et_builder_tb_enabled() && ! $is_use_placeholder ) {
			return;
		}

		// Global variable that affects WC module rendering.
		global $product, $post, $tb_original_product, $tb_original_post, $tb_wc_post, $tb_wc_product;

		// Making sure the correct comment template is loaded on WC tabs' review tab.
		// TODO feat(D5, WooCommerce Product Tabs Module): update the callback once we have the module for tabs in place [https://github.com/elegantthemes/Divi/issues/25756].
		add_filter( 'comments_template', [ 'ET_Builder_Module_Woocommerce_Tabs', 'comments_template_loader' ], 20 );

		// Force display related posts; technically sets all products as related.
		add_filter( 'woocommerce_product_related_posts_force_display', '__return_true' );

		// Make sure review's form is opened.
		add_filter( 'comments_open', '__return_true' );

		// Save original $post for reset later.
		$tb_original_post = $post;

		// Save original $product for reset later.
		$tb_original_product = $product;

		// If modified global existed, use it for efficiency.
		if ( ! is_null( $tb_wc_post ) && ! is_null( $tb_wc_product ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need to override the post with the theme builder post.
			$post    = $tb_wc_post;
			$product = $tb_wc_product;

			return;
		}

		// Get placeholders.
		$placeholders = et_theme_builder_wc_placeholders();

		if ( $is_use_placeholder ) {
			$placeholder_src = wc_placeholder_img_src( 'full' );
			$placeholder_id  = attachment_url_to_postid( $placeholder_src );

			if ( absint( $placeholder_id ) > 0 ) {
				$placeholders['gallery_image_ids'] = [ $placeholder_id ];
			}
		} else {
			$placeholders['gallery_image_ids'] = [];
		}

		// $post might be null if current request is computed callback (ie. WC gallery)
		if ( is_null( $post ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentionally done.
			$post = new stdClass();
		}

		// Overwrite $post global.
		$post->post_title     = $placeholders['title'];
		$post->post_slug      = $placeholders['slug'];
		$post->post_excerpt   = $placeholders['short_description'];
		$post->post_content   = $placeholders['description'];
		$post->post_status    = $placeholders['status'];
		$post->comment_status = $placeholders['comment_status'];

		// Overwrite global $product.
		$product = new ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder();

		// Set current post ID as product's ID. `ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder`
		// handles all placeholder related value but product ID need to be manually set to match current
		// post's ID. This is especially needed when add-ons is used and accessing get_id() method.
		if ( isset( $post->ID ) ) {
			$product->set_id( $post->ID );
		}

		// Save modified global for later use.
		$tb_wc_post    = $post;
		$tb_wc_product = $product;
	}

	/**
	 * Resets global objects for use in the theme builder.
	 *
	 * Adjusts global variables and removes specific filters to prepare
	 * the environment for theme builder rendering or processing. This ensures
	 * proper behavior and compatibility when building or previewing themes.
	 *
	 * Based on the legacy `et_theme_builder_wc_reset_global_objects` function.
	 *
	 * @since ??
	 *
	 * @param array $conditional_tags Optional. An array of conditional tags to indicate
	 *                                the current context. Supports:
	 *                                - 'is_tb' (bool): Whether the current context is the
	 *                                  theme builder.
	 *
	 * @return void
	 */
	public static function reset_global_objects_for_theme_builder( array $conditional_tags = array() ) {
		$is_tb              = $conditional_tags['is_tb'] ?? false;
		$is_use_placeholder = $is_tb || is_et_pb_preview();

		// Check if current request is theme builder (direct page / AJAX request).
		if ( ! et_builder_tb_enabled() && ! $is_use_placeholder ) {
			return;
		}

		global $product, $post, $tb_original_product, $tb_original_post;

		// TODO feat(D5, WooCommerce Product Tabs Module): update the callback once we have the module for tabs in place [https://github.com/elegantthemes/Divi/issues/25756.
		remove_filter( 'comments_template', [ 'ET_Builder_Module_Woocommerce_Tabs', 'comments_template_loader' ], 20 );
		remove_filter( 'woocommerce_product_related_posts_force_display', '__return_true' );
		remove_filter( 'comments_open', '__return_true' );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need to override the post with the theme builder post.
		$post = $tb_original_post;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need to override the product with the theme builder product.
		$product = $tb_original_product;
	}

	/**
	 * Retrieves available product page layouts.
	 *
	 * This method returns an array of product page layout options with their labels.
	 * It handles different translation contexts and applies a filter to allow customization.
	 *
	 * Legacy function: et_builder_wc_get_page_layouts()
	 *
	 * @since ??
	 *
	 * @param string $translation_context Translation Context to indicate if translation origins
	 *                                    from Divi Theme or from the Builder. Default 'et_builder'.
	 * @return array Array of product page layouts with their labels.
	 */
	public static function get_page_layouts( string $translation_context = 'et_builder' ): array {
		switch ( $translation_context ) {
			case 'Divi':
				$product_page_layouts = [
					'et_build_from_scratch' => esc_html__( 'Build From Scratch', 'Divi' ),
					'et_default_layout'     => esc_html__( 'Default', 'Divi' ),
				];
				break;
			default:
				$product_page_layouts = [
					'et_build_from_scratch' => esc_html__( 'Build From Scratch', 'et_builder' ),
					'et_default_layout'     => et_builder_i18n( 'Default' ),
				];
				break;
		}

		/**
		 * Filters the available product page layouts.
		 *
		 * @since ??
		 *
		 * @param array $product_page_layouts Array of product page layouts.
		 */
		return apply_filters( 'divi_woocommerce_get_page_layouts', $product_page_layouts );
	}

	/**
	 * Adds WooCommerce settings to the Builder settings.
	 *
	 * This method adds WooCommerce-specific settings to the Builder settings via the
	 * 'et_builder_settings_definitions' filter. It includes settings for product page
	 * layouts and product layout.
	 *
	 * Legacy function: et_builder_wc_add_settings()
	 *
	 * @since ??
	 *
	 * @param array $builder_settings_fields Current builder settings.
	 * @return array Modified builder settings with WooCommerce options.
	 */
	public static function add_settings( array $builder_settings_fields ): array {
		// Bail early if WooCommerce is not active.
		if ( ! function_exists( 'wc_get_product' ) ) {
			return $builder_settings_fields;
		}

		$fields = [
			'et_pb_woocommerce_product_layout' => [
				'type'            => 'select',
				'id'              => 'et_pb_woocommerce_product_layout',
				'index'           => -1,
				'label'           => esc_html__( 'Product Layout', 'et_builder' ),
				'description'     => esc_html__( 'Here you can choose Product Page Layout for WooCommerce.', 'et_builder' ),
				'options'         => [
					'et_right_sidebar'   => esc_html__( 'Right Sidebar', 'et_builder' ),
					'et_left_sidebar'    => esc_html__( 'Left Sidebar', 'et_builder' ),
					'et_no_sidebar'      => esc_html__( 'No Sidebar', 'et_builder' ),
					'et_full_width_page' => esc_html__( 'Fullwidth', 'et_builder' ),
				],
				'default'         => 'et_right_sidebar',
				'validation_type' => 'simple_text',
				'et_save_values'  => true,
				'tab_slug'        => 'post_type_integration',
				'toggle_slug'     => 'performance',
			],
			'et_pb_woocommerce_page_layout'    => [
				'type'            => 'select',
				'id'              => 'et_pb_woocommerce_product_page_layout',
				'index'           => -1,
				'label'           => esc_html__( 'Product Content', 'et_builder' ),
				'description'     => esc_html__( '\"Build From Scratch\" loads a pre-built WooCommerce page layout, with which you build on when the Divi Builder is enabled. \"Default\" option lets you use default WooCommerce page layout.', 'et_builder' ),
				'options'         => self::get_page_layouts(),
				'default'         => 'et_build_from_scratch',
				'validation_type' => 'simple_text',
				'et_save_values'  => true,
				'tab_slug'        => 'post_type_integration',
				'toggle_slug'     => 'performance',
			],
		];

		// Hide setting in Divi Builder Plugin.
		if ( et_is_builder_plugin_active() ) {
			unset( $fields['et_pb_woocommerce_product_layout'] );
		}

		return array_merge( $builder_settings_fields, $fields );
	}

	/**
	 * Sets the pre-built layout for WooCommerce product pages.
	 *
	 * This method sets the initial content for product pages based on the selected layout.
	 * It checks if the post is a valid product, gets the product page layout, and returns
	 * the appropriate content.
	 *
	 * Note: This function is implemented but intentionally not hooked into any action or filter
	 * in Builder 5. In Builder 5, the functionality it provided is now handled differently,
	 * likely through the use of Gutenberg blocks instead of shortcodes for initial content.
	 * The legacy function was not directly hooked either, but was potentially called from
	 * other functions. Once WC Modules are created, this function may be re-implemented and refactored
	 * to work with the new Builder 5 functionality.
	 *
	 * Legacy function: et_builder_wc_set_prebuilt_layout()
	 *
	 * @since ??
	 *
	 * @param string $maybe_shortcode_content Post content.
	 * @param int    $post_id Post ID.
	 * @return string The content to use for the product page.
	 */
	public static function set_initial_content( string $maybe_shortcode_content, int $post_id ): string {
		$post = get_post( absint( $post_id ) );
		$args = [];

		if ( ! ( $post instanceof WP_Post ) || 'product' !== $post->post_type ) {
			return $maybe_shortcode_content;
		}

		// $post_id is a valid Product ID by now.
		$product_page_layout = self::get_product_layout( $post_id );

		/*
		 * When FALSE, this means the Product doesn't use Builder at all;
		 * Or the Product has been using the Builder before WooCommerce Modules QF launched.
		 */
		if ( ! $product_page_layout ) {
			$product_page_layout = et_get_option(
				'et_pb_woocommerce_page_layout',
				'et_build_from_scratch'
			);
		}

		$is_product_content_modified = 'modified' === get_post_meta(
			$post_id,
			ET_BUILDER_WC_PRODUCT_PAGE_CONTENT_STATUS_META_KEY,
			true
		);

		// Content was already saved or default content should be loaded.
		if ( $is_product_content_modified || 'et_default_layout' === $product_page_layout ) {
			return $maybe_shortcode_content;
		}

		if ( has_shortcode( $maybe_shortcode_content, 'et_pb_section' ) && 'et_build_from_scratch' === $product_page_layout && ! empty( $maybe_shortcode_content ) ) {
			$args['existing_shortcode'] = $maybe_shortcode_content;
		}

		return self::get_prefilled_product_page_content( $args );
	}

	/**
	 * Gets the pre-built layout for WooCommerce product pages.
	 *
	 * This method returns a string containing the prefilled content for product pages.
	 * It includes a default layout with common product modules and applies a filter
	 * to allow customization.
	 *
	 * The content is pre-converted from shortcode format to Gutenberg block format
	 *  to avoid calling Conversion::maybeConvertContent every time this method is called.
	 *  The function also handles existing shortcode content by converting it and
	 *  appending it, as well as existing block content by appending it directly.
	 *
	 * Legacy function: et_builder_wc_get_prefilled_product_page_content().
	 *
	 * TODO fix(D5, WooCommerce): Once D5 WC Modules are completed, fix the Gutenberg formatted content [https://github.com/elegantthemes/Divi/issues/41852].
	 *
	 * @since ??
	 *
	 * @param array $args Additional args.
	 * @return string The prefilled content for product pages.
	 */
	public static function get_prefilled_product_page_content( array $args = [] ): string {
		/**
		 * Filters the Top section Background in the default WooCommerce Modules layout.
		 *
		 * @since ??
		 *
		 * @param string $color Default empty.
		 */
		$et_builder_wc_initial_top_section_bg = apply_filters( 'et_builder_wc_initial_top_section_bg', '' );

		// Pre-converted content from shortcode to Gutenberg block format.
		// This content was generated by running Conversion::maybeConvertContent on the shortcode content
		// from legacy et_builder_wc_get_prefilled_product_page_content function.
		$content = '<!-- wp:divi/section {"module":{"decoration":{"spacing":{"desktop":{"value":{"padding":{"top":"0px","right":"","bottom":"","left":"","syncVertical":"off","syncHorizontal":"off"}}}},"background":{"desktop":{"value":{"color":"' . esc_attr( $et_builder_wc_initial_top_section_bg ) . '"}}}}}} --><!-- wp:divi/row {"module":{"decoration":{"sizing":{"desktop":{"value":{"width":"100%"}}},"spacing":{"desktop":{"value":{"padding":{"top":"0px","right":"","bottom":"0px","left":"","syncVertical":"off","syncHorizontal":"off"}}}}}}} --><!-- wp:divi/column {"module":{"advanced":{"type":{"desktop":{"value":"4_4"}}}}} --><!-- wp:divi/woocommerce-breadcrumb  --><!-- /wp:divi/woocommerce-breadcrumb --><!-- wp:divi/woocommerce-cart-notice  --><!-- /wp:divi/woocommerce-cart-notice --><!-- /wp:divi/column --><!-- /wp:divi/row --><!-- wp:divi/row {"module":{"decoration":{"spacing":{"desktop":{"value":{"padding":{"top":"0px","right":"","bottom":"","left":"","syncVertical":"off","syncHorizontal":"off"}}}},"sizing":{"desktop":{"value":{"width":"100%"}}}}}} --><!-- wp:divi/column {"module":{"advanced":{"type":{"desktop":{"value":"1_2"}}}}} --><!-- wp:divi/woocommerce-product-images  --><!-- /wp:divi/woocommerce-product-images --><!-- /wp:divi/column --><!-- wp:divi/column {"module":{"advanced":{"type":{"desktop":{"value":"1_2"}}}}} --><!-- wp:divi/woocommerce-product-title  --><!-- /wp:divi/woocommerce-product-title --><!-- wp:divi/woocommerce-product-rating  --><!-- /wp:divi/woocommerce-product-rating --><!-- wp:divi/woocommerce-product-price  --><!-- /wp:divi/woocommerce-product-price --><!-- wp:divi/woocommerce-product-description  --><!-- /wp:divi/woocommerce-product-description --><!-- wp:divi/woocommerce-product-add-to-cart {"unknownAttributes":{"form_field_text_align":"center"}} --><!-- /wp:divi/woocommerce-product-add-to-cart --><!-- wp:divi/woocommerce-product-meta  --><!-- /wp:divi/woocommerce-product-meta --><!-- /wp:divi/column --><!-- /wp:divi/row --><!-- wp:divi/row {"module":{"decoration":{"sizing":{"desktop":{"value":{"width":"100%"}}}}}} --><!-- wp:divi/column {"module":{"advanced":{"type":{"desktop":{"value":"4_4"}}}}} --><!-- wp:divi/woocommerce-product-tabs {"content":{"desktop":{"value":"\n\t\t\t\t\t"}}} --><!-- /wp:divi/woocommerce-product-tabs --><!-- wp:divi/woocommerce-product-upsell {"content":{"advanced":{"columnsNumber":{"desktop":{"value":"3"}}}}} --><!-- /wp:divi/woocommerce-product-upsell --><!-- wp:divi/woocommerce-related-products {"content":{"advanced":{"columnsNumber":{"desktop":{"value":"3"}}}}} --><!-- /wp:divi/woocommerce-related-products --><!-- /wp:divi/column --><!-- /wp:divi/row --><!-- /wp:divi/section -->';

		if ( ! empty( $args['existing_shortcode'] ) ) {
			// If there's existing shortcode content, convert it and append it.
			$existing_content = Conversion::maybeConvertContent( $args['existing_shortcode'] );
			$content         .= $existing_content;
		}

		if ( ! empty( $args['existing_block'] ) ) {
			// If there's existing block content, append it.
			$content .= $args['existing_block'];
		}

		/**
		 * Filters the prefilled content for product pages.
		 *
		 * @since ??
		 *
		 * @param string $content Prefilled content for product pages.
		 * @param array  $args    Additional args.
		 */
		return apply_filters( 'divi_woocommerce_prefilled_product_page_content', $content, $args );
	}

	/**
	 * Modifies the product image HTML to use a placeholder when needed.
	 *
	 * This method is hooked into the 'woocommerce_single_product_image_thumbnail_html'
	 * filter and modifies the HTML for product images to use a placeholder when needed.
	 *
	 * Legacy function: et_builder_wc_placeholder_img() (partial implementation)
	 *
	 * @since ??
	 *
	 * @param string $html Original image HTML.
	 * @return string Modified image HTML.
	 */
	public static function placeholder_img( string $html ): string {
		// Only modify the HTML if we're in the builder or if the image is missing.
		if ( ! et_core_is_fb_enabled() && ! empty( $html ) ) {
			return $html;
		}

		// Get placeholder image.
		$placeholder_src = wc_placeholder_img_src( 'full' );

		// Create placeholder HTML.
		$placeholder_html  = '<div class="woocommerce-product-gallery__image--placeholder">';
		$placeholder_html .= '<img src="' . esc_url( $placeholder_src ) . '" alt="' . esc_attr__( 'Placeholder', 'et_builder' ) . '" class="wp-post-image" />';
		$placeholder_html .= '</div>';

		return $placeholder_html;
	}

	/**
	 * Returns an HTML img tag for the default image placeholder.
	 *
	 * This method returns an HTML img tag for a placeholder image. It supports
	 * both 'portrait' and 'landscape' modes.
	 *
	 * Legacy function: et_builder_wc_placeholder_img() (direct implementation)
	 *
	 * @since ??
	 *
	 * @param string $mode Default 'portrait'. Either 'portrait' or 'landscape' image mode.
	 * @return string HTML img tag for the placeholder image.
	 */
	public static function get_placeholder_img( string $mode = 'portrait' ): string {
		$allowed_list = [
			'portrait'  => ET_BUILDER_PLACEHOLDER_PORTRAIT_VARIATION_IMAGE_DATA,
			'landscape' => ET_BUILDER_PLACEHOLDER_LANDSCAPE_IMAGE_DATA,
		];

		if ( ! in_array( $mode, array_keys( $allowed_list ), true ) ) {
			$mode = 'portrait';
		}

		return sprintf(
			'<img src="%1$s" alt="%2$s" />',
			et_core_esc_attr( 'placeholder', $allowed_list[ $mode ] ),
			esc_attr__( 'Product image', 'et_builder' )
		);
	}

	/**
	 * Gets the Title header tag.
	 *
	 * WooCommerce version influences the returned header.
	 *
	 * Legacy function: get_title_header()
	 *
	 * @since ??
	 *
	 * @return string The appropriate HTML header tag ('h2' or 'h3') for product titles.
	 */
	public static function get_title_header(): string {
		$header = 'h3';

		if ( ! Conditions::is_woocommerce_enabled() ) {
			return $header;
		}

		global $woocommerce;
		if ( version_compare( '3.0.0', $woocommerce->version, '<=' ) ) {
			$header = 'h2';
		}

		return $header;
	}

	/**
	 * Gets the Title selector.
	 *
	 * WooCommerce changed the title tag from h3 to h2 in v3.0.0.
	 * This function returns a CSS selector for product titles.
	 *
	 * Legacy function: get_title_selector()
	 *
	 * @since ??
	 *
	 * @return string CSS selector for product titles.
	 */
	public static function get_title_selector(): string {
		return sprintf( 'li.product %s', self::get_title_header() );
	}

	/**
	 * Sets the display type to render only products.
	 *
	 * This method is used to control how products are displayed in RelatedProducts and Upsells modules.
	 * It temporarily changes the display type and returns the original value for later restoration.
	 *
	 * Legacy function: set_display_type_to_render_only_products()
	 *
	 * @since ??
	 *
	 * @param string $option_name  The WooCommerce option name to modify.
	 *                             Allowed values: 'woocommerce_shop_page_display', 'woocommerce_category_archive_display'.
	 * @param string $display_type The new display type value. Default empty string.
	 *
	 * @return string The original display type value.
	 */
	public static function set_display_type_to_render_only_products( string $option_name, string $display_type = '' ): string {
		// Allowlist of permitted option names.
		$allowed_option_names = [
			'woocommerce_shop_page_display',
			'woocommerce_category_archive_display',
		];

		// Validate the option name.
		if ( ! in_array( $option_name, $allowed_option_names, true ) ) {
			return '';
		}

		$existing_display_type = get_option( $option_name );
		update_option( $option_name, $display_type );

		return $existing_display_type;
	}

	/**
	 * Resets the display type to the original value.
	 *
	 * This method is used to restore the original display type after rendering
	 * RelatedProducts and Upsells modules.
	 *
	 * Legacy function: reset_display_type()
	 *
	 * @since ??
	 *
	 * @param string $option_name  The WooCommerce option name to modify.
	 *                             Allowed values: 'woocommerce_shop_page_display', 'woocommerce_category_archive_display'.
	 * @param string $display_type The original display type value to restore.
	 *
	 * @return void
	 */
	public static function reset_display_type( string $option_name, string $display_type ): void {
		// Allowlist of permitted option names.
		$allowed_option_names = [
			'woocommerce_shop_page_display',
			'woocommerce_category_archive_display',
		];

		// Validate the option name.
		if ( ! in_array( $option_name, $allowed_option_names, true ) ) {
			return;
		}

		update_option( $option_name, $display_type );
	}

	/**
	 * Gets the HTML for the product reviews comment form.
	 *
	 * This method returns the HTML for the product reviews comment form.
	 * It handles cases where comments are closed or the user is not logged in.
	 *
	 * Legacy function: get_reviews_comment_form()
	 *
	 * @since ??
	 *
	 * @param \WC_Product|false $product The product object. Default false.
	 * @return string The HTML for the product reviews comment form.
	 */
	public static function get_reviews_comment_form( $product = false ): string {
		if ( false === $product ) {
			$product = self::get_product( self::get_default_product() );
		}

		if ( false === $product ) {
			return '';
		}

		$product_id = $product->get_id();

		// Save the current global post to restore it later.
		global $post;
		$original_post = $post;

		// Set the global post to the product post.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Need to override the post for comment_form to work correctly.
		$post = get_post( $product_id );

		// Start output buffering to capture the form HTML.
		ob_start();

		// Check if comments are open for this product.
		if ( comments_open( $product_id ) ) {
			// Get the comment form.
			comment_form(
				[
					'title_reply'   => esc_html__( 'Add a review', 'et_builder' ),
					'label_submit'  => esc_html__( 'Submit', 'et_builder' ),
					'comment_field' => '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'et_builder' ) . '</label><select name="rating" id="rating" required>
						<option value="">' . esc_html__( 'Rate&hellip;', 'et_builder' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'et_builder' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'et_builder' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'et_builder' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'et_builder' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'et_builder' ) . '</option>
					</select></div>
					<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'et_builder' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
				],
				$product_id
			);
		} else {
			echo '<p class="woocommerce-verification-required">' . esc_html__( 'Only logged in customers who have purchased this product may leave a review.', 'et_builder' ) . '</p>';
		}

		// Get the buffered content.
		$comment_form = ob_get_clean();

		// Restore the original global post.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring original global $post data.
		$post = $original_post;

		/**
		 * Filters the product reviews comment form HTML.
		 *
		 * @since ??
		 *
		 * @param string      $comment_form The HTML for the product reviews comment form.
		 * @param \WC_Product $product      The product object.
		 */
		return apply_filters( 'divi_woocommerce_product_reviews_comment_form', $comment_form, $product );
	}

	/**
	 * Gets the reviews title for a product.
	 *
	 * This method returns a formatted title for the product reviews section,
	 * including the review count. It handles cases where there are no reviews
	 * and supports customization through a filter.
	 *
	 * Legacy function: get_reviews_title()
	 *
	 * @since ??
	 *
	 * @param \WC_Product|false $product The product object. Default false.
	 * @return string The formatted reviews title.
	 */
	public static function get_reviews_title( $product = false ): string {
		if ( false === $product ) {
			$product = self::get_product( self::get_default_product() );
		}

		if ( false === $product ) {
			return esc_html__( 'Reviews', 'et_builder' );
		}

		$review_count = $product->get_review_count();

		if ( 0 === $review_count ) {
			$reviews_title = esc_html__( 'Reviews', 'et_builder' );
		} else {
			$reviews_title = sprintf(
				esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $review_count, 'et_builder' ) ),
				esc_html( number_format_i18n( $review_count ) ),
				'<span>' . esc_html( $product->get_name() ) . '</span>'
			);
		}

		/**
		 * Filters the product reviews title.
		 *
		 * @since ??
		 *
		 * @param string      $reviews_title The formatted reviews title.
		 * @param \WC_Product $product       The product object.
		 */
		return apply_filters( 'divi_woocommerce_product_reviews_title', $reviews_title, $product );
	}

	/**
	 * Sanitizes text values with octets.
	 *
	 * This function is used to sanitize text values in such a way that octets are preserved.
	 *
	 * This function is based partly on the legacy `et_pb_process_computed_property` function.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string The sanitized value.
	 */
	public static function sanitize_text_field_values_with_octets( string $value ): string {
		$sanitized_value = $value;

		if ( false !== strpos( $value, '%' ) ) {
			// `sanitize_text_fields()` removes octets `%[a-f0-9]{2}` and would zap/corrupt icon and/or `%date` values,
			// so we prefix octets with `_` to protect them and remove the prefix after sanitization.
			$prepared_value  = preg_replace( '/%([a-f0-9]{2})/', '%_$1', $value );
			$sanitized_value = preg_replace( '/%_([a-f0-9]{2})/', '%$1', sanitize_text_field( $prepared_value ) );
		}

		return $sanitized_value;
	}

	/**
	 * Get the current REST request query params, equivalent to `$_GET` in REST API request.
	 *
	 * @since ??
	 *
	 * @return array|null The current REST request query params, equivalent to `$_GET` in REST API request.
	 */
	public static function get_current_rest_request_query_params(): ?array {
		return self::$_current_rest_request_query_params;
	}

	/**
	 * Validate the product ID.
	 *
	 * Validates the given product ID.
	 * Ideally used in REST API validation callbacks.
	 * This function caches the result of the validation per request using static variable.
	 *
	 * @since ??
	 *
	 * @param mixed                $param   The product ID.
	 * @param WP_REST_Request|null $request Optional. The REST request. Default null.
	 *
	 * @return bool
	 */
	public static function validate_product_id( $param, $request = null ): bool {
		if ( $request instanceof WP_REST_Request ) {
			// Set the current REST request query params, equivalent to `$_GET` in REST API request.
			self::$_current_rest_request_query_params = $request->get_query_params();
		}

		return 'current' === $param || 'latest' === $param || ( is_numeric( $param ) && absint( $param ) > 0 );
	}

	/**
	 * Retrieve common required parameters for WooCommerce HTML rendering.
	 *
	 * This function is used to retrieve the common required parameters for WooCommerce HTML rendering.
	 * This function should be called in the `index` method of the REST controller for the respective WooCommerce module HTML endpoint.
	 * The function will return a `WP_Error` object if the request is invalid, e.g. missing required parameters.
	 * Otherwise, it will return an array of the common required parameters including:
	 * - `conditional_tags`
	 * - `current_page`
	 * - `request_type`
	 * - `product_id`
	 *
	 * This function also validates the product ID and returns a 404 error array if the product ID is not valid i.e not one of the following:
	 * - 'current'
	 * - 'latest'
	 * - a valid absolute integer product ID
	 *
	 * This function is based on the legacy `et_pb_process_computed_property` function.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The REST request.
	 *
	 * @return array The common required parameters for WooCommerce HTML rendering.
	 *               If the request is invalid, e.g. missing required parameters, an array with the error code, message, and status is returned.
	 *               If an error is encountered, an array with the error code, message, and status is returned. The array contains the following keys:
	 *               - `conditional_tags`
	 *               - `current_page`
	 *               - `product_id`
	 */
	public static function retrieve_common_required_params_for_woocommerce_html_rendering( WP_REST_Request $request ) {
		$missing_params = [];
		if ( ! $request->has_param( 'conditionalTags' ) ) {
			$missing_params[] = 'conditionalTags';
		}

		if ( ! $request->has_param( 'currentPage' ) ) {
			$missing_params[] = 'currentPage';
		}

		if ( ! empty( $missing_params ) ) {
			return [
				'invalid_request',
				sprintf( __( 'Invalid request. Missing required parameters %s.', 'divi' ), implode( ', ', $missing_params ) ),
				[ 'code' => 'invalid_request' ],
				400,
			];
		}

		$conditional_tags = $request->get_param( 'conditionalTags' ) ?? [];
		$current_page     = $request->get_param( 'currentPage' ) ?? [];
		$request_type     = $request->get_param( 'requestType' ) ?? '';

		$utils = ET_Core_Data_Utils::instance();

		// Keep only allowed keys.
		$conditional_tags = array_intersect_key( $conditional_tags, SettingsDataCallbacks::conditional_tags() );
		$current_page     = array_intersect_key( $current_page, SettingsDataCallbacks::current_page() );

		// Sanitize values.
		$conditional_tags = $utils->sanitize_text_fields( $conditional_tags );
		$current_page     = $utils->sanitize_text_fields( $current_page );
		$request_type     = sanitize_text_field( $request_type );

		if ( in_array( $request_type, array( '404', 'archive', 'home' ), true ) ) {
			// On non-singular page, we do not have $current_page id, so we will check if user has theme_builder capability.
			if ( ! et_pb_is_allowed( 'theme_builder' ) ) {
				return [
					'invalid_request',
					__( 'Invalid request. You do not have permission to access this Theme Builder page.', 'divi' ),
					[ 'code' => 'invalid_request' ],
					403,
				];
			}
		} else {
			// For other pages, we will check if user can edit specific post.
			if ( ! current_user_can( 'edit_post', ( $current_page['id'] ?? 0 ) ) ) {
				return [
					'invalid_request',
					__( 'Invalid request. You do not have permission to edit this post.', 'divi' ),
					[ 'code' => 'invalid_request' ],
					403,
				];
			}
		}

		// Check if there is page id.
		if ( empty( $current_page['id'] ) && '404' !== $request_type ) {
			return [
				'invalid_request',
				__( 'Invalid request. Missing required parameter: `currentPage.id`.', 'divi' ),
				[ 'code' => 'invalid_request' ],
				400,
			];
		}

		return [
			'conditional_tags' => $conditional_tags,
			'current_page'     => $current_page,
		];
	}

}
