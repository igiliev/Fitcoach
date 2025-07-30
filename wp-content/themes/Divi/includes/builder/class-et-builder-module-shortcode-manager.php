<?php
/**
 * Shortcode Manager Class.
 *
 * @package Divi
 * @subpackage Builder
 * @since 4.10.0
 */

/**
 * Handles module shortcodes.
 *
 * @since 4.10.0
 */
class ET_Builder_Module_Shortcode_Manager {

	/**
	 * Whether the shortcode manager has been initialized.
	 */
	private static $initialzed;

	/**
	 * Modules container.
	 *
	 * @access public
	 * @var array
	 */
	public static $modules_map = [];

	/**
	 * Additional slugs to register.
	 *
	 * @access public
	 * @var array
	 */
	public static $additional_slugs_to_register = [];

	/**
	 * WooCommerce modules container.
	 *
	 * @access public
	 * @var array
	 */
	public static $woo_modules_map = [];

	/**
	 * Structural Modules container.
	 *
	 * @access public
	 * @var array
	 */
	public static $structural_modules_map = [];

	/**
	 * Initialized Modules.
	 *
	 * @access public
	 * @var array
	 */
	public static $initialized_modules = [];

	/**
	 * Initialize shortcode manager class.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function init() {
		if ( self::$initialzed ) {
			return;
		}

		self::$initialzed = true;

		$this->register_modules();
		$this->register_fullwidth_modules();
		$this->register_structural_modules();
		$this->register_woo_modules();
		$this->register_shortcode();

		// hook into theme/plugin activation, so we can get the list of third party
		// modules that just initialized themselves, and store them in the db.
		add_action( 'after_switch_theme', [ $this, 'register_third_party_modules' ] );
		add_action( 'activated_plugin', [ $this, 'register_third_party_modules' ] );
		// Lets also make sure this runs at least once,
		// in case the theme/plugin was activated before this code was added,
		// This will ensure that the third party modules are registered.
		if ( false === et_get_option( 'all_third_party_shortcode_slugs', false ) ) {
			$this->register_third_party_modules();
		}
	}

	/**
	 * Register third party modules.
	 *
	 * This method is called on theme/plugin activation.
	 *
	 * This way we can get list of third party module slugs that just initialized themselves, and store them in the db.
	 * Then we can use this list of slugs to register the shortcodes on the fly, and also to ensure that the modules are loaded.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_third_party_modules() {
		// Load the shortcode framework.
		et_load_shortcode_framework();

		// Fire off the hooks that will initialize the third party modules.
		do_action( 'divi_extensions_init' );
		do_action( 'et_builder_ready' );

		// Get the list of third party modules that were initialized.
		$third_party_modules = \ET_Builder_Element::get_third_party_modules();

		// save the slugs to the database.
		et_update_option( 'all_third_party_shortcode_slugs', array_keys( $third_party_modules ) );
	}

	/**
	 * Get modules map.
	 *
	 * @since 4.14.5
	 *
	 * @param string $type Modules map type.
	 *
	 * @return array Modules map.
	 */
	public static function get_modules_map( $type = false ) {
		if ( 'woo_modules' === $type ) {
			return self::$woo_modules_map;
		}

		if ( 'structural_modules' === $type ) {
			return self::$structural_modules_map;
		}

		return self::$modules_map;
	}

	private static function _should_register_shortcodes() {
		static $should_register_shortcodes = null;

		if ( null !== $should_register_shortcodes ) {
			// Use the cached value.
			return $should_register_shortcodes;
		}

		// If this is an ajax request.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// load for these actions.
			$load_for_ajax_actions = [
				'et_core_portability_import',
				'et_core_portability_export',
				'et_theme_builder_api_import_theme_builder',
				'et_theme_builder_api_import_theme_builder_step',
			];

			// phpcs:ignore WordPress.Security.NonceVerification -- This is just check, therefore nonce verification not required.
			if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $load_for_ajax_actions, true ) ) {
				$should_register_shortcodes = true;
				return $should_register_shortcodes;
			} else {
				$should_register_shortcodes = false;
				return $should_register_shortcodes;
			}
		}

		// always load builder files when WP CLI is running.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$should_register_shortcodes = true;
			return $should_register_shortcodes;
		}

		// always load builder files when in Test env.
		if ( defined( 'WP_TESTS_DOMAIN' ) ) {
			$should_register_shortcodes = true;
			return $should_register_shortcodes;
		}

		// Don't load builder files when in REST API request.
		$request_uri         = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		$is_rest_api_request = defined( 'REST_REQUEST' ) && REST_REQUEST || 0 === strpos( $request_uri, '/' . rest_get_url_prefix() ) || false !== strpos( $request_uri, '?rest_route=/' );

		if ( $is_rest_api_request ) {
			$should_register_shortcodes = false;
			return $should_register_shortcodes;
		}

		// If we are in the admin.
		if ( is_admin() ) {
			$should_register_shortcodes = false;
			return $should_register_shortcodes;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- This is just check, therefore nonce verification not required.
		$is_vb_enabled = isset( $_GET['et_fb'] ) && '1' === $_GET['et_fb'];

		// phpcs:ignore WordPress.Security.NonceVerification -- This is just check, therefore nonce verification not required.
		$has_page_id = ! empty( $_GET['page_id'] );

		if ( $is_vb_enabled && $has_page_id ) {
			// Don't load when this is a request for the VB from GB.
			$should_register_shortcodes = false;
			return $should_register_shortcodes;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- This is just check, therefore nonce verification not required.
		$is_app_window = isset( $_GET['app_window'] ) && '1' === $_GET['app_window'];
		if ( $is_vb_enabled && $is_app_window ) {
			// Return false if WooCommerce is not active.
			if ( ! class_exists( 'WooCommerce', false ) ) {
				$should_register_shortcodes = false;
				return false;
			}

			// phpcs:ignore WordPress.Security.NonceVerification -- This is just check, therefore nonce verification not required.
			if ( isset( $_REQUEST['REQUEST_METHOD'] ) && ! ( 'POST' === $_REQUEST['REQUEST_METHOD'] || 'GET' === $_REQUEST['REQUEST_METHOD'] ) ) {
				$should_register_shortcodes = false;
				return false;
			}

			global $post;

			// Return false if the post isn't available, or contains no Woo modules "[et_pb_wc" or "[et_pb_shop".
			if ( ! $post || isset( $post->post_content ) && ( false === strpos( $post->post_content, '[et_pb_wc' ) && false === strpos( $post->post_content, '[et_pb_shop' ) ) ) {
				return false;
			}

			// Only load these when this is a request for the VB and this is the app window.
			$should_register_shortcodes = true;
			return $should_register_shortcodes;
		}

		// If this is any other request for the VB, then we shouldn't register the shortcodes.
		if ( et_core_is_fb_enabled() ) {
			// Don't load on Visual Builder requests. No shortcode nor serialized block are ever parsed in Visual Builder
			// request due to two reasons:
			// 1. To optimize document size, D5 VB loads special blank template page that contains iframe to load app window.
			// 2. The saved content are parsed on the VB JS app directly.
			$should_register_shortcodes = false;
			return $should_register_shortcodes;
		}

		$should_register_shortcodes = false;
		return $should_register_shortcodes;
	}

	/**
	 * Start registering shortcodes.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_shortcode() {
		// check if we should register all shortcodes or just the lazy ones.
		if ( self::_should_register_shortcodes() ) {
			$action_hook = apply_filters( 'et_builder_modules_load_hook', is_admin() ? 'wp_loaded' : 'wp' );

			add_action( $action_hook, [ $this, 'register_all_shortcodes' ], 1 );
		} else {
			$this->register_lazy_shortcodes();
		}
	}

	/**
	 * Register normal modules.
	 *
	 * Modules dependent to each other will have
	 * to have a dependency parameter on them.
	 * Eg : et_pb_accordion_item needs et_pb_toggle so we
	 * have to pass add that on the `deps` key.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_modules() {
		$modules = [
			'et_pb_accordion'                   => [
				'classname' => 'ET_Builder_Module_Accordion',
			],
			'et_pb_accordion_item'              => [
				'classname' => 'ET_Builder_Module_Accordion_Item',
				'deps'      => array( 'et_pb_toggle' ),
			],
			'et_pb_audio'                       => [
				'classname' => 'ET_Builder_Module_Audio',
			],
			'et_pb_counters'                    => [
				'classname' => 'ET_Builder_Module_Bar_Counters',
			],
			'et_pb_counter'                     => [
				'classname' => 'ET_Builder_Module_Bar_Counters_Item',
			],
			'et_pb_blog'                        => [
				'classname' => 'ET_Builder_Module_Blog',
			],
			'et_pb_blurb'                       => [
				'classname' => 'ET_Builder_Module_Blurb',
			],
			'et_pb_button'                      => [
				'classname' => 'ET_Builder_Module_Button',
			],
			'et_pb_circle_counter'              => [
				'classname' => 'ET_Builder_Module_Circle_Counter',
			],
			'et_pb_code'                        => [
				'classname' => 'ET_Builder_Module_Code',
			],
			'et_pb_comments'                    => [
				'classname' => 'ET_Builder_Module_Comments',
			],
			'et_pb_contact_form'                => [
				'classname' => 'ET_Builder_Module_Contact_Form',
			],
			'et_pb_contact_field'               => [
				'classname' => 'ET_Builder_Module_Contact_Form_Item',
			],
			'et_pb_countdown_timer'             => [
				'classname' => 'ET_Builder_Module_Countdown_Timer',
			],
			'et_pb_cta'                         => [
				'classname' => 'ET_Builder_Module_Cta',
			],
			'et_pb_divider'                     => [
				'classname' => 'ET_Builder_Module_Divider',
			],
			'et_pb_filterable_portfolio'        => [
				'classname' => 'ET_Builder_Module_Filterable_Portfolio',
			],
			'et_pb_gallery'                     => [
				'classname' => 'ET_Builder_Module_Gallery',
			],
			'et_pb_image'                       => [
				'classname' => 'ET_Builder_Module_Image',
			],
			'et_pb_login'                       => [
				'classname' => 'ET_Builder_Module_Login',
			],
			'et_pb_map'                         => [
				'classname' => 'ET_Builder_Module_Map',
			],
			'et_pb_map_pin'                     => [
				'classname' => 'ET_Builder_Module_Map_Item',
			],
			'et_pb_menu'                        => [
				'classname' => 'ET_Builder_Module_Menu',
			],
			'et_pb_number_counter'              => [
				'classname' => 'ET_Builder_Module_Number_Counter',
			],
			'et_pb_portfolio'                   => [
				'classname' => 'ET_Builder_Module_Portfolio',
			],
			'et_pb_post_content'                => [
				'classname' => 'ET_Builder_Module_PostContent',
			],
			'et_pb_post_slider'                 => [
				'classname' => 'ET_Builder_Module_Post_Slider',
			],
			'et_pb_post_title'                  => [
				'classname' => 'ET_Builder_Module_Post_Title',
			],
			'et_pb_post_nav'                    => [
				'classname' => 'ET_Builder_Module_Posts_Navigation',
			],
			'et_pb_pricing_tables'              => [
				'classname' => 'ET_Builder_Module_Pricing_Tables',
			],
			'et_pb_pricing_table'               => [
				'classname' => 'ET_Builder_Module_Pricing_Tables_Item',
			],
			'et_pb_search'                      => [
				'classname' => 'ET_Builder_Module_Search',
			],
			'et_pb_sidebar'                     => [
				'classname' => 'ET_Builder_Module_Sidebar',
			],
			'et_pb_signup'                      => [
				'classname' => 'ET_Builder_Module_Signup',
			],
			'et_pb_signup_custom_field'         => [
				'classname'    => 'ET_Builder_Module_Signup_Item',
				'preload_deps' => array( 'et_pb_contact_field' ),
			],
			'et_pb_slider'                      => [
				'classname' => 'ET_Builder_Module_Slider',
			],
			'et_pb_slide'                       => [
				'classname' => 'ET_Builder_Module_Slider_Item',
			],
			'et_pb_social_media_follow'         => [
				'classname' => 'ET_Builder_Module_Social_Media_Follow',
			],
			'et_pb_social_media_follow_network' => [
				'classname' => 'ET_Builder_Module_Social_Media_Follow_Item',
			],
			'et_pb_tabs'                        => [
				'classname' => 'ET_Builder_Module_Tabs',
			],
			'et_pb_tab'                         => [
				'classname' => 'ET_Builder_Module_Tabs_Item',
			],
			'et_pb_team_member'                 => [
				'classname' => 'ET_Builder_Module_Team_Member',
			],
			'et_pb_testimonial'                 => [
				'classname' => 'ET_Builder_Module_Testimonial',
			],
			'et_pb_text'                        => [
				'classname' => 'ET_Builder_Module_Text',
			],
			'et_pb_toggle'                      => [
				'classname' => 'ET_Builder_Module_Toggle',
			],
			'et_pb_video'                       => [
				'classname' => 'ET_Builder_Module_Video',
			],
			'et_pb_video_slider'                => [
				'classname' => 'ET_Builder_Module_Video_Slider',
			],
			'et_pb_video_slider_item'           => [
				'classname' => 'ET_Builder_Module_Video_Slider_Item',
			],
			'et_pb_icon'                        => [
				'classname' => 'ET_Builder_Module_Icon',
			],
			'et_pb_heading'                     => [
				'classname' => 'ET_Builder_Module_Heading',
			],
		];

		/**
		 * Filters built-in Divi Builder module class names.
		 *
		 * 3rd-party plugins can use this filter to override Divi Builder modules.
		 *
		 * NOTE: Overriding built-in modules is not ideal and should only be used as a temporary solution.
		 * The recommended approach for achieving this is using the official API:
		 * https://www.elegantthemes.com/documentation/developers/divi-module/how-to-create-a-divi-builder-module/
		 *
		 * @since 4.11.0
		 *
		 * @param array $additional_modules Additional modules.
		 */
		$additional_modules = apply_filters( 'et_module_classes', [] );

		self::$modules_map = array_merge( self::$modules_map, $modules, $additional_modules );
	}

	/**
	 * Register fullwidth modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_fullwidth_modules() {
		$modules = [
			'et_pb_fullwidth_code'         => [
				'classname' => 'ET_Builder_Module_Fullwidth_Code',
			],
			'et_pb_fullwidth_header'       => [
				'classname' => 'ET_Builder_Module_Fullwidth_Header',
			],
			'et_pb_fullwidth_image'        => [
				'classname' => 'ET_Builder_Module_Fullwidth_Image',
			],
			'et_pb_fullwidth_map'          => [
				'classname' => 'ET_Builder_Module_Fullwidth_Map',
			],
			'et_pb_fullwidth_menu'         => [
				'classname' => 'ET_Builder_Module_Fullwidth_Menu',
			],
			'et_pb_fullwidth_portfolio'    => [
				'classname' => 'ET_Builder_Module_Fullwidth_Portfolio',
			],
			'et_pb_fullwidth_post_content' => [
				'classname' => 'ET_Builder_Module_Fullwidth_PostContent',
			],
			'et_pb_fullwidth_post_slider'  => [
				'classname' => 'ET_Builder_Module_Fullwidth_Post_Slider',
			],
			'et_pb_fullwidth_post_title'   => [
				'classname' => 'ET_Builder_Module_Fullwidth_Post_Title',
			],
			'et_pb_fullwidth_slider'       => [
				'classname' => 'ET_Builder_Module_Fullwidth_Slider',
			],
		];

		/**
		 * Filters built-in Divi Builder module class names.
		 *
		 * 3rd-party plugins can use this filter to override Divi Builder modules.
		 *
		 * NOTE: Overriding built-in modules is not ideal and should only be used as a temporary solution.
		 * The recommended approach for achieving this is using the official API:
		 * https://www.elegantthemes.com/documentation/developers/divi-module/how-to-create-a-divi-builder-module/
		 *
		 * @since 4.11.0
		 *
		 * @param array $additional_modules Additional modules.
		 */
		$additional_modules = apply_filters( 'et_fullwidth_module_classes', [] );

		self::$modules_map = array_merge( self::$modules_map, $modules, $additional_modules );
	}

	/**
	 * Register structural modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_structural_modules() {
		$modules = [
			'et_pb_section'   => [
				'classname' => 'ET_Builder_Section',
			],
			'et_pb_row'       => [
				'classname' => 'ET_Builder_Row',
			],
			'et_pb_row_inner' => [
				'classname' => 'ET_Builder_Row_Inner',
			],
			'et_pb_column'    => [
				'classname' => 'ET_Builder_Column',
			],
		];

		/**
		 * Filters built-in Divi Builder module class names.
		 *
		 * 3rd-party plugins can use this filter to override Divi Builder modules.
		 *
		 * NOTE: Overriding built-in modules is not ideal and should only be used as a temporary solution.
		 * The recommended approach for achieving this is using the official API:
		 * https://www.elegantthemes.com/documentation/developers/divi-module/how-to-create-a-divi-builder-module/
		 *
		 * @since 4.11.0
		 *
		 * @param array $additional_modules Additional modules.
		 */
		$additional_modules = apply_filters( 'et_structural_module_classes', [] );

		self::$structural_modules_map = array_merge( $modules, $additional_modules );
	}

	/**
	 * Register woocommerce modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_woo_modules() {
		$woo_modules = [
			'et_pb_wc_add_to_cart'              => [
				'classname' => 'ET_Builder_Module_Woocommerce_Add_To_Cart',
			],
			'et_pb_wc_additional_info'          => [
				'classname' => 'ET_Builder_Module_Woocommerce_Additional_Info',
			],
			'et_pb_wc_breadcrumb'               => [
				'classname' => 'ET_Builder_Module_Woocommerce_Breadcrumb',
			],
			'et_pb_wc_cart_notice'              => [
				'classname' => 'ET_Builder_Module_Woocommerce_Cart_Notice',
			],
			'et_pb_wc_description'              => [
				'classname' => 'ET_Builder_Module_Woocommerce_Description',
			],
			'et_pb_wc_gallery'                  => [
				'classname' => 'ET_Builder_Module_Woocommerce_Gallery',
			],
			'et_pb_wc_images'                   => [
				'classname' => 'ET_Builder_Module_Woocommerce_Images',
			],
			'et_pb_wc_meta'                     => [
				'classname' => 'ET_Builder_Module_Woocommerce_Meta',
			],
			'et_pb_wc_price'                    => [
				'classname' => 'ET_Builder_Module_Woocommerce_Price',
			],
			'et_pb_wc_rating'                   => [
				'classname' => 'ET_Builder_Module_Woocommerce_Rating',
			],
			'et_pb_wc_related_products'         => [
				'classname' => 'ET_Builder_Module_Woocommerce_Related_Products',
			],
			'et_pb_wc_reviews'                  => [
				'classname' => 'ET_Builder_Module_Woocommerce_Reviews',
			],
			'et_pb_wc_stock'                    => [
				'classname' => 'ET_Builder_Module_Woocommerce_Stock',
			],
			'et_pb_wc_tabs'                     => [
				'classname' => 'ET_Builder_Module_Woocommerce_Tabs',
			],
			'et_pb_wc_title'                    => [
				'classname' => 'ET_Builder_Module_Woocommerce_Title',
			],
			'et_pb_wc_upsells'                  => [
				'classname' => 'ET_Builder_Module_Woocommerce_Upsells',
			],
			'et_pb_wc_cart_products'            => [
				'classname' => 'ET_Builder_Module_Woocommerce_Cart_Products',
			],
			'et_pb_wc_cross_sells'              => [
				'classname' => 'ET_Builder_Module_Woocommerce_Cross_Sells',
			],
			'et_pb_wc_cart_totals'              => [
				'classname' => 'ET_Builder_Module_Woocommerce_Cart_Totals',
			],
			'et_pb_wc_checkout_billing'         => [
				'classname' => 'ET_Builder_Module_Woocommerce_Checkout_Billing',
			],
			'et_pb_wc_checkout_shipping'        => [
				'classname' => 'ET_Builder_Module_Woocommerce_Checkout_Shipping',
			],
			'et_pb_wc_checkout_order_details'   => [
				'classname' => 'ET_Builder_Module_Woocommerce_Checkout_Order_Details',
			],
			'et_pb_wc_checkout_payment_info'    => [
				'classname' => 'ET_Builder_Module_Woocommerce_Checkout_Payment_Info',
			],
			'et_pb_wc_checkout_additional_info' => [
				'classname' => 'ET_Builder_Module_Woocommerce_Checkout_Additional_Info',
			],
			'et_pb_shop'                        => [
				'classname' => 'ET_Builder_Module_Shop',
			],
		];

		/**
		 * Filters built-in Divi Builder module class names.
		 *
		 * 3rd-party plugins can use this filter to override Divi Builder modules.
		 *
		 * NOTE: Overriding built-in modules is not ideal and should only be used as a temporary solution.
		 * The recommended approach for achieving this is using the official API:
		 * https://www.elegantthemes.com/documentation/developers/divi-module/how-to-create-a-divi-builder-module/
		 *
		 * @since 4.11.0
		 *
		 * @param array $additional_modules Additional modules.
		 */
		$additional_modules = apply_filters( 'et_woo_module_classes', [] );

		self::$woo_modules_map = $woo_modules;
		self::$modules_map     = array_merge( self::$modules_map, $woo_modules, $additional_modules );
	}

	/**
	 * Register shortcode.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_all_shortcodes() {
		et_load_shortcode_framework();

		$et_builder_module_files = glob( ET_BUILDER_DIR . 'module/*.php' );
		$et_builder_module_types = glob( ET_BUILDER_DIR . 'module/type/*.php' );

		if ( ! $et_builder_module_files ) {
			return;
		}

		/**
		 * Fires before the builder's module classes are loaded.
		 *
		 * @since 3.0.77
		 */
		do_action( 'et_builder_modules_load' );

		foreach ( $et_builder_module_types as $module_type ) {
			require_once $module_type;
		}

		foreach ( $et_builder_module_files as $module_file ) {
			// skip this all caps version, if it exists.
			// See https://github.com/elegantthemes/Divi/issues/24780.
			if ( 'CTA.php' === basename( $module_file ) ) {
				continue;
			}

			require_once $module_file;
		}

		if ( apply_filters( 'et_builder_load_woocommerce_modules', et_is_woocommerce_plugin_active() ) ) {
			$et_builder_woocommerce_module_files = glob( ET_BUILDER_DIR . 'module/woocommerce/*.php' );
			foreach ( $et_builder_woocommerce_module_files as $module_type ) {
				require_once $module_type;
			}
		}

		/**
		 * Fires after the builder's module classes are loaded.
		 *
		 * NOTE: this hook only fires on :
		 * - Visual Builder pages
		 * - Front end cache prime initial request
		 *
		 * IT DOES NOT fire on ALL front end requests
		 *
		 * @since 3.0.77
		 * @deprecated ?? Introduced shortcode manager.
		 *                Use {@see et_builder_module_loading}/{@see et_builder_module_loaded}/{@see et_builder_ready} instead.
		 */
		do_action( 'et_builder_modules_loaded' );
	}

	/**
	 * Lazy load shortcodes.
	 *
	 * @since 4.10.0
	 * @access public
	 * @return void
	 */
	public function register_lazy_shortcodes() {
		// A fake handler has to be registered for every shortcode, otherways
		// code will exit early and the pre_do_shortcode_tag hook won't be executed.
		foreach ( self::$modules_map as $shortcode_slug => $module_data ) {
			add_shortcode( $shortcode_slug, '__return_empty_string' );
		}

		// lets register our structural modules
		foreach ( self::$structural_modules_map as $shortcode_slug => $module_data ) {
			add_shortcode( $shortcode_slug, '__return_empty_string' );
		}

		// Lets load all shortcode slugs that have been registered.
		$all_third_party_shortcode_slugs = et_get_option( 'all_third_party_shortcode_slugs', [], '', true );

		// Known 3rd party module slugs.
		$known_3rd_party_module_slugs = $this->get_known_3rd_party_module_slugs();

		// add all_third_party_shortcode_slugs to the known_3rd_party_module_slugs.
		$all_third_party_shortcode_slugs = array_merge( $all_third_party_shortcode_slugs, $known_3rd_party_module_slugs );

		// dedupe $all_third_party_shortcode_slugs.
		$all_third_party_shortcode_slugs = array_unique( $all_third_party_shortcode_slugs );

		// compute the difference between all shortcode slugs and the ones we have in the map.
		self::$additional_slugs_to_register = array_diff( $all_third_party_shortcode_slugs, array_keys( self::$modules_map ) );

		// Register the slugs that are not in the map.
		foreach ( self::$additional_slugs_to_register as $slug ) {
			add_shortcode( $slug, '__return_empty_string' );
		}

		// Load modules as needed.
		add_filter( 'pre_do_shortcode_tag', [ $this, 'load_modules' ], 99, 2 );

		// Ensure all our module slugs are always considered, even when not loaded (yet).
		add_filter( 'et_builder_get_module_slugs_by_post_type', [ $this, 'add_module_slugs' ] );

		add_filter( 'et_builder_get_woocommerce_modules', [ $this, 'add_woo_slugs' ] );

		// Ensure all our structural module slugs are always considered, even when not loaded (yet).
		add_filter( 'et_builder_get_structural_module_slugs', [ $this, 'add_structural_module_slugs' ] );

		/**
		 * Fires after the builder's module classes are loaded.
		 *
		 * This hook is fired here for legacy reasons only.
		 * Do not depend on this hook in the future.
		 *
		 * @since 3.0.77
		 * @deprecated ?? Introduced shortcode manager.
		 *                Use {@see et_builder_module_loading}/{@see et_builder_module_loaded}/{@see et_builder_ready} instead.
		 */
		do_action( 'et_builder_modules_loaded' );

		/**
		 * Fires after the builder's module shortcodes are lazy registered.
		 *
		 * @since 4.10.0
		 */
		do_action( 'et_builder_module_lazy_shortcodes_registered' );
	}

	/**
	 * Add slugs for all our woo modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @param array $loaded Loaded woo modules slugs.
	 * @return array
	 */
	public function add_woo_slugs( $loaded ) {
		static $module_slugs;

		// Only compute this once.
		if ( empty( $module_slugs ) ) {
			$module_slugs = array_keys( self::$woo_modules_map );
		}

		return array_unique( array_merge( $loaded, $module_slugs ) );
	}

	/**
	 * Add slugs for all our modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @param array $loaded Loaded modules slugs.
	 * @return array
	 */
	public function add_module_slugs( $loaded ) {
		static $module_slugs;

		// Only compute this once.
		if ( empty( $module_slugs ) ) {
			$module_slugs = array_keys( self::$modules_map );
		}

		return array_unique( array_merge( $loaded, $module_slugs ) );
	}

	/**
	 * Add slugs for all our structural modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @param array $loaded Loaded modules slugs.
	 * @return array
	 */
	public function add_structural_module_slugs( $loaded ) {
		static $structural_module_slugs;

		// Only compute this once.
		if ( empty( $structural_module_slugs ) ) {
			$structural_module_slugs = array_keys( self::$structural_modules_map );
		}

		return array_unique( array_merge( $loaded, $structural_module_slugs ) );
	}

	/**
	 * Load modules.
	 *
	 * @since 4.10.0
	 * @access public
	 * @param mixed  $override Whether to override do_shortcode return value or not.
	 * @param string $tag Shortcode tag.
	 * @return mixed
	 */
	public function load_modules( $override, $tag ) {
		$this->maybe_load_module_from_slug( $tag );
		return $override;
	}

	/**
	 * Get List of known 3rd party module slugs.
	 *
	 * @since ??
	 * @access public
	 * @return array
	 */
	public function get_known_3rd_party_module_slugs() {
		static $_module_slugs = null;

		if ( ! is_null( $_module_slugs ) ) {
			return $_module_slugs;
		}

		$module_slugs = [
			'ags_woo_shop_plus',
			'bck_advanced_divider',
			'bck_advanced_heading',
			'bck_advanced_team',
			'bck_animated_text',
			'bck_blog_plus',
			'bck_card',
			'bck_cf7_styler',
			'bck_content_toggle',
			'bck_dual_button',
			'bck_flipbox',
			'bck_floating_image',
			'bck_horizontal_timeline_child',
			'bck_horizontal_timeline',
			'bck_hotspots_child',
			'bck_hotspots',
			'bck_hover_box',
			'bck_icon_box',
			'bck_image_accordion',
			'bck_image_compare',
			'bck_image_masking',
			'bck_info_box',
			'bck_inline_svg',
			'bck_instagram_feed',
			'bck_list_group_child',
			'bck_list_group',
			'bck_lottie',
			'bck_price_menu_child',
			'bck_price_menu',
			'bck_review',
			'bck_social_share_child',
			'bck_social_share',
			'bck_testimonial',
			'bck_twitter_feed',
			'bck_vertical_timeline_child',
			'bck_vertical_timeline',
			'bck_video_popup',
			'brbl_author_list',
			'brbl_post_carousel',
			'brbl_post_grid',
			'brbl_post_list',
			'brbl_post_masonry',
			'brbl_post_ticker',
			'brbl_post_tiles',
			'brbl_posts_ticker',
			'brbl_smart_post_list',
			'brcr_image_carousel',
			'brcr_logo_carousel',
			'brcr_team_carousel_child',
			'brcr_team_carousel',
			'checkout_field',
			'chiac_divi_accordions_item',
			'chiac_divi_accordions',
			'cwp_business_hour',
			'cwp_image_collage',
			'dag_animated_gallery',
			'ddt_agent_grid',
			'ddt_agent_list',
			'ddt_cpt_posts_grid',
			'ddt_cpt_posts_list',
			'ddt_cpt_posts_slider',
			'ddt_cpt_slider_masonary',
			'ddt_event_grid',
			'ddt_event_list',
			'ddt_event_slider',
			'ddt_facebook',
			'ddt_flipbox',
			'ddt_image_swap',
			'ddt_image_tilt',
			'ddt_img_hover_box',
			'ddt_instagram',
			'ddt_property_fullwidth_slider',
			'ddt_property_grid',
			'ddt_property_list',
			'ddt_property_slider',
			'ddt_property_table',
			'ddt_team_members_slider',
			'ddt_teammember_grid',
			'ddt_teammember_horizontal',
			'ddt_teammember_list',
			'ddt_testimonial_grid',
			'ddt_testimonial_list',
			'ddt_testimonial_slider',
			'ddt_twitter',
			'ddt_woo_slider',
			'de_fb_form',
			'decm_event_display',
			'dfp_flipbox',
			'dhsp_hotspots_child',
			'dhsp_hotspots',
			'dico_copy',
			'dif_instagram_carousel',
			'dif_instagram_feed',
			'difl_marqueetext',
			'difl_marqueetextitem',
			'digr_social_share_item',
			'digr_social_share',
			'dip_animated_gallery',
			'dip_floating_image',
			'dip_image_accordion',
			'dip_image_compare',
			'dip_image_masking',
			'dipi_advanced_tabs_item',
			'dipi_advanced_tabs',
			'dipi_balloon',
			'dipi_blog_slider',
			'dipi_button_grid_child',
			'dipi_button_grid',
			'dipi_carousel_child',
			'dipi_content_slider_child',
			'dipi_counter',
			'dipi_fancy_text_child',
			'dipi_fancy_text',
			'dipi_flip_box',
			'dipi_floating_multi_images_child',
			'dipi_floating_multi_images',
			'dipi_horizontal_timeline_item',
			'dipi_hover_box',
			'dipi_hover_gallery_item',
			'dipi_hover_gallery',
			'dipi_image_accordion_child',
			'dipi_image_accordion',
			'dipi_image_gallery_child',
			'dipi_image_hotspot_child',
			'dipi_image_magnifier',
			'dipi_image_rotator',
			'dipi_image_showcase_child',
			'dipi_info_circle_item',
			'dipi_parallax_images_item',
			'dipi_reveal',
			'dipi_scroll_image',
			'dipi_svg_animator',
			'dipi_table_maker_child',
			'dipi_testimonial',
			'dipi_tile_scroll_item',
			'dipi_timeline_item',
			'ditp_countdown_timer',
			'divi_instagram_chat',
			'divi_pro_gallery',
			'divi_rocket_dummy',
			'divi_telegram_chat',
			'divi_whatsapp_chat',
			'dmnp_mega_menu',
			'dmnp_off_canvas',
			'dnxte_review',
			'dondivi_content_toggle',
			'dondivi_gallery',
			'dondivi_grid_item',
			'dondivi_grid',
			'dondivi_hotspot',
			'dondivi_hotspots',
			'dondivi_menu_item',
			'dondivi_menu',
			'dondivi_popup',
			'dondivi_tab',
			'dondivi_tabs',
			'dondivi_timeline_item',
			'dondivi_timeline',
			'dpevent_calendar',
			'dpevent_grid',
			'dpevent_list',
			'dpevent_slider',
			'ds_balloon',
			'ds_blog_slider',
			'ds_button_grid_child',
			'ds_button_grid',
			'ds_carousel_child',
			'ds_fancy_text_child',
			'ds_fancy_text',
			'ds_flip_box',
			'ds_floating_multi_images_child',
			'ds_floating_multi_images',
			'ds_hover_box',
			'ds_image_accordion_child',
			'ds_image_accordion',
			'ds_image_gallery_child',
			'ds_image_hotspot_child',
			'ds_image_magnifier',
			'ds_image_showcase_child',
			'ds_scroll_image',
			'ds_svg_animator',
			'ds_timeline_item',
			'dsm_image_accordion_child',
			'dsm_image_accordion',
			'dsp_instagram_carousel',
			'dsp_instagram_feed',
			'dsp_social_share_child',
			'dsp_social_share',
			'dsp_twitter_feed_carousel',
			'dsp_twitter_feed',
			'dssb_sharing_button',
			'dssb_sharing_buttons',
			'dtlp_horizontal_timeline_child',
			'dtlp_horizontal_timeline',
			'dtlp_vertical_timeline_child',
			'dtlp_vertical_timeline',
			'dvcr_video_carousel',
			'dvmd_image_box',
			'dvmd_simple_heading',
			'dvmd_table_maker_item',
			'dvmd_table_maker',
			'dvmd_tablepress_styler',
			'dvmd_text_on_a_path',
			'dvmd_typewriter',
			'dvmm_mad_menu',
			'dvmmv_madmenu_vertical',
			'dvppl_cf7_styler',
			'el_advanced_flipbox',
			'elegantGallery_main',
			'emods_gallery',
			'emods_rating',
			'et_db_stock_status',
			'et_pb_animals',
			'et_pb_before_after',
			'et_pb_blurb_extended',
			'et_pb_countdown_timer',
			'et_pb_db_account_nav',
			'et_pb_db_action_shortcode',
			'et_pb_db_add_info',
			'et_pb_db_atc',
			'et_pb_db_attribute',
			'et_pb_db_breadcrumbs',
			'et_pb_db_cart_products',
			'et_pb_db_cart_total',
			'et_pb_db_checkout_after_cust_details',
			'et_pb_db_checkout_before_cust_details',
			'et_pb_db_checkout_before_order_review',
			'et_pb_db_checkout_billing',
			'et_pb_db_checkout_coupon',
			'et_pb_db_checkout_order_review',
			'et_pb_db_checkout_payment',
			'et_pb_db_checkout_shipping',
			'et_pb_db_content',
			'et_pb_db_crosssell',
			'et_pb_db_images',
			'et_pb_db_login_form',
			'et_pb_db_login_password_confirmation',
			'et_pb_db_login_password_lost',
			'et_pb_db_login_password_reset',
			'et_pb_db_meta',
			'et_pb_db_notices',
			'et_pb_db_price',
			'et_pb_db_pro_before',
			'et_pb_db_pro_navigation',
			'et_pb_db_product_carousel',
			'et_pb_db_product_slider',
			'et_pb_db_product_summary',
			'et_pb_db_product_title',
			'et_pb_db_products_search',
			'et_pb_db_rating',
			'et_pb_db_register_form',
			'et_pb_db_related_products',
			'et_pb_db_reviews',
			'et_pb_db_sharing',
			'et_pb_db_shop_after',
			'et_pb_db_shop_button',
			'et_pb_db_shop_cat_loop',
			'et_pb_db_shop_cat_title',
			'et_pb_db_shop_loop',
			'et_pb_db_shop_thumbnail',
			'et_pb_db_short_desc',
			'et_pb_db_single_image',
			'et_pb_db_tabs',
			'et_pb_db_thankyou_cust_details',
			'et_pb_db_thankyou_details',
			'et_pb_db_thankyou_overview',
			'et_pb_db_thankyou_payment_details',
			'et_pb_db_upsell',
			'et_pb_db_woo_add_payment_method',
			'et_pb_db_woo_addresses',
			'et_pb_db_woo_avatar',
			'et_pb_db_woo_downloads',
			'et_pb_db_woo_edit_account',
			'et_pb_db_woo_get_name',
			'et_pb_db_woo_orders',
			'et_pb_db_woo_payment_methods',
			'et_pb_db_woo_user_name',
			'et_pb_db_woo_view_order',
			'et_pb_de_mach_archive_loop',
			'et_pb_de_mach_filter_posts',
			'et_pb_de_mach_search_posts_item',
			'et_pb_de_nitro_defer_video',
			'et_pb_de_protect',
			'et_pb_divimenus_flex_item',
			'et_pb_divimenus_flex',
			'et_pb_divimenus_item',
			'et_pb_divimenus',
			'et_pb_dm_stop_stacking',
			'et_pb_dmm_dropdown',
			'et_pb_dp_posts_slider',
			'et_pb_filterable_portfolio',
			'et_pb_fullwidth_kkheader_seg',
			'et_pb_icon_divider',
			'et_pb_image_divider',
			'et_pb_image_swap',
			'et_pb_jv_team_grid_members',
			'et_pb_jv_team_list_members',
			'et_pb_jv_team_members',
			'et_pb_jvt_testimonial_grid',
			'et_pb_jvt_testimonial_list',
			'et_pb_jvt_testimonial_slider',
			'et_pb_kkblogext_grid',
			'et_pb_kkblogext',
			'et_pb_kkcomplex_form',
			'et_pb_kkfilterable_grid',
			'et_pb_kkpost_title',
			'et_pb_masonry_post_type_gallery_fw',
			'et_pb_mm_tabs',
			'et_pb_signatures_item',
			'et_pb_signatures',
			'et_pb_star_rating',
			'et_pb_stop_stacking',
			'et_pb_team_members_horizontal',
			'et_pb_team_members_slider',
			'et_pb_team_members_table',
			'et_pb_testify',
			'et_pb_text_divider',
			'et_pb_wpdt_image_card_carousel_fw',
			'et_pb_wpdt_image_card_carousel_item_fw',
			'et_pb_wpdt_post_type_carousel_fw',
			'et_pb_wpdt_taxonomy_carousel_fw',
			'et_pb_wpdt_wc_product_carousel_fw',
			'et_pb_wpt_masonry_image_gallery_fw',
			'et_pb_wpt_recipe_image',
			'et_pb_wpt_schema_breadcrumbs_full_width',
			'et_pb_wpt_schema_special_announcement_full_width',
			'gdofilter',
			'north_checkout',
			'north_upsell',
			'pac_dcm_library_layouts',
			'pac_divi_table_of_contents',
			'pac_dth_taxonomy_list',
			'pac_dtm_child',
			'pac_dtm_parent',
			'snapway_rating',
			'testify_wpform',
			'torq_alert',
			'torq_basic_list_child',
			'torq_basic_list',
			'torq_blurb',
			'torq_card',
			'torq_carousel',
			'torq_checkmark_list_child',
			'torq_checkmark_list',
			'torq_compare_img',
			'torq_contact_form7',
			'torq_content_toggle',
			'torq_countdown',
			'torq_divider',
			'torq_filterable_gallery',
			'torq_flip_box',
			'torq_gradient_heading',
			'torq_heading',
			'torq_hotspot_child',
			'torq_hotspot',
			'torq_icon_box',
			'torq_icon_list_child',
			'torq_icon_list',
			'torq_instagram_chat',
			'torq_instagram_feed',
			'torq_logo_carousel',
			'torq_lottie',
			'torq_pricing_table_child',
			'torq_pricing_table',
			'torq_restro_menu_child',
			'torq_restro_menu',
			'torq_review_card',
			'torq_social_share_child',
			'torq_social_share',
			'torq_star_rating',
			'torq_stats_grid_child',
			'torq_stats_grid',
			'torq_svg',
			'torq_team',
			'torq_telegram_chat',
			'torq_testimonial',
			'torq_timeline_child',
			'torq_timeline_horizontal_child',
			'torq_timeline_horizontal',
			'torq_timeline',
			'torq_video_modal',
			'torq_whatsapp_chat',
			'wdc_card_carousel_child',
			'wdc_card_carousel',
			'wdc_content_carousel_child',
			'wdc_content_carousel',
			'wdc_divi_library_child',
			'wdc_divi_library',
			'wdc_google_reviews',
			'wdc_instagram_feed',
			'wdc_logo_carousel',
			'wdc_post_carousel',
			'wdc_product_carousel',
			'wdc_team_carousel',
			'wdc_testimonial_carousel',
			'wdc_twitter_feed_carousel',
			'wdc_video_carousel',
			'wdcl_logo_carousel',
			'wdcl_twitter_feed_carousel',
			'woofilter',
			'zinv_drop_cap',
			'ts_divi_image',
			'bda_breadcrumb',
			'bda_heading',
			'bda_business_hours',
			'bda_business_hours_child',
			'bda_faq',
			'bda_faq_child',
			'bda_how_to',
			'bda_howto_steps',
			'bda_icon_list',
			'bda_icon_list_item',
			'bda_info_box',
			'bda_login_form',
			'bda_marketing_button',
			'bda_video',
			'bda_registration_form',
			'bda_registration_form_item',
			'bda_retina_image',
			'bda_ribbon',
			'bda_social_share',
			'bda_social_share_item',
			'bda_spacer',
			'bda_table_of_contents',
			'bda_welcome_music',
		];

		/**
		 * Filters known 3rd party module slugs.
		 *
		 * Used to add shortcode slugs for 3rd party modules,
		 * which will be lazy loaded by the shortcode manager.
		 *
		 * Format:
		 * ```
		 * $module_slugs = [
		 *    'torq_alert',
		 *    'torq_basic_list',
		 * ];
		 * ```
		 *
		 * @param array $module_slugs Known 3rd party module slugs. Array of strings.
		 */
		$_module_slugs = apply_filters( 'et_builder_3rd_party_module_slugs', $module_slugs );

		return $_module_slugs;
	}

	/**
	 * Instantiate module from a shortcode tag (aka slug).
	 *
	 * @since 4.10.0
	 * @access public
	 * @param string $tag Shortcode tag (aka slug).
	 * @return void
	 */
	public function maybe_load_module_from_slug( $tag ) {
		// If the module is already initialized, return early.
		if ( ! empty( self::$initialized_modules[ $tag ] ) ) {
			return;
		}

		// Note that this module has been initialized.
		self::$initialized_modules[ $tag ] = $tag;

		/**
		 * Module loading configuration details.
		 *
		 * If Array, it will contain:
		 * - classname: string, class name of the module. Required.
		 * - preload_deps: array, slugs of modules to load before this module. Optional.
		 * - deps: array, slugs of modules to load after this module. Optional.
		 *
		 * If String, it will be the shortcode slug of the module.
		 *
		 * @var array|string $module
		 */
		$module = null;

		$tag_in_modules_map      = ! empty( self::$modules_map[ $tag ] );
		$tag_in_additional_slugs = in_array( $tag, self::$additional_slugs_to_register, true );

		if ( $tag_in_modules_map ) {
			// $module is an array, in this case, and it's a reference.
			$module =& self::$modules_map[ $tag ];
		} elseif ( ! empty( self::$woo_modules_map[ $tag ] ) ) {
			// $module is an array, in this case, and it's a reference.
			$module =& self::$woo_modules_map[ $tag ];
		} elseif ( ! empty( self::$structural_modules_map[ $tag ] ) ) {
			// $module is an array, in this case, and it's a reference.
			$module =& self::$structural_modules_map[ $tag ];
		} elseif ( $tag_in_additional_slugs ) {
			// $module is a string (a shortcode module slug, e.g. torq_alert), in this case.
			$module = $tag;
		} else {
			// None of our business, this is some other shortcode.
			return;
		}

		// If we don't have a module, return early.
		if ( empty( $module ) ) {
			return;
		}

		if ( empty( $module['instance'] ) ) {
			// Load shortcode framework.
			et_load_shortcode_framework( $tag );

			/**
			 * Fires before module class is instantiated.
			 *
			 * @since 4.10.0
			 *
			 * @param array|string $module Module loading configuration details, or shortcode slug.
			 *
			 * @param string       $tag    Shortcode tag for module.
			 */
			do_action( 'et_builder_module_loading', $tag, $module );

			/**
			 * Fires before module class is instantiated.
			 *
			 * The dynamic portion of the hook, `$tag`, refers to the shortcode tag.
			 *
			 * @since 4.10.0
			 *
			 * @param array|string $module Module loading configuration details, or shortcode slug.
			 */
			do_action( "et_builder_module_loading_{$tag}", $module );

			// Load dependency before the class if needed.
			if ( ! empty( $module['preload_deps'] ) ) {
				foreach ( $module['preload_deps'] as $slug ) {
					$this->maybe_load_module_from_slug( $slug );
				}
			}

			// Load the class, if there is one.
			if ( ! empty( $module['classname'] ) && class_exists( $module['classname'] ) ) {
				// FYI, This is setting by reference.
				$module['instance'] = new $module['classname']();
			}

			// Load dependency after the class if needed.
			if ( ! empty( $module['deps'] ) ) {
				foreach ( $module['deps'] as $slug ) {
					$this->maybe_load_module_from_slug( $slug );
				}
			}

			/**
			 * Fires after module class is instantiated.
			 *
			 * @since 4.10.0
			 *
			 * @param array|string $module Module loading configuration details, or shortcode slug.
			 *
			 * @param string       $tag    Shortcode tag for module.
			 */
			do_action( 'et_builder_module_loaded', $tag, $module );

			/**
			 * Fires after module class is instantiated.
			 *
			 * The dynamic portion of the hook, `$tag`, refers to the shortcode tag.
			 *
			 * @since 4.10.0
			 *
			 * @param array|string $module Module loading configuration details, or shortcode slug.
			 */
			do_action( "et_builder_module_loaded_{$tag}", $module );
		}
	}
}
