<?php
/**
 * Generate Dynamic Assets.
 *
 * This file combines the logic from the following Divi 4 files:
 * - includes/builder/feature/dynamic-assets/class-dynamic-assets.php
 * - includes/functions/dynamic-assets.php
 *
 * @since   ??
 * @package Divi
 */

namespace ET\Builder\FrontEnd\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\FrontEnd;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\GlobalData\GlobalData;
use ET_Builder_Dynamic_Assets_Feature;
use ET_Core_Cache_Directory;
use ET_Core_PageResource;
use ET_Post_Stack;
use Feature\ContentRetriever\ET_Builder_Content_Retriever;
use InvalidArgumentException;


/**
 * Dynamic Assets class.
 *
 * Perform content analysis that marks that content should considered `above the fold` or `below the fold`.
 *
 * @since ??
 */
class DynamicAssets implements DependencyInterface {

	/**
	 * Is the current request cachable.
	 *
	 * @var null|bool
	 */
	private static $_is_cachable_request = null;

	/**
	 * TB template ids.
	 *
	 * @var array
	 */
	private $_tb_template_ids = [];

	/**
	 * Post ID.
	 *
	 * @var int
	 */
	private $_post_id;

	/**
	 * Object ID.
	 *
	 * @var int
	 */
	private $_object_id;

	/**
	 * Entire page content, including TB Header, TB Body Layout, Post Content and TB Footer.
	 *
	 * Content is not passed through `the_content` filter, This means that `$_all_content` will include auto-embedded
	 * videos or expanded blocks, the content is considered raw.
	 *
	 * @var string
	 */
	private $_all_content = '';

	/**
	 * Folder Name.
	 *
	 * @var string
	 */
	private $_folder_name = '';

	/**
	 * Cache Directory Path.
	 *
	 * @var string
	 */
	private $_cache_dir_path = '';

	/**
	 * Cache Directory URL.
	 *
	 * @var string
	 */
	private $_cache_dir_url = '';

	/**
	 * Product directory.
	 *
	 * @var string
	 */
	private $_product_dir = '';

	/**
	 * Resource owner.
	 *
	 * @var string
	 */
	private $_owner = '';

	/**
	 * Suffix used for files on custom post types.
	 *
	 * @var string
	 */
	private $_cpt_suffix = '';

	/**
	 * Check if RTL is used.
	 *
	 * @var bool
	 */
	public $is_rtl = false;

	/**
	 * Suffix used for files on RTL websites.
	 *
	 * @var string
	 */
	private $_rtl_suffix = '';

	/**
	 * Prefix used for files that contain css from theme builder templates.
	 *
	 * @var string
	 */
	private $_tb_prefix = '';

	/**
	 * List of early modules.
	 *
	 * @var array
	 */
	private $_early_modules = [];

	/**
	 * Cached list of blocks saved in post meta.
	 *
	 * @var array
	 */
	private $_early_blocks = [];

	/**
	 * Cached list of shortcodes saved in post meta.
	 *
	 * @var array
	 */
	private $_early_shortcodes = [];

	/**
	 * Missed modules detected by late detection.
	 *
	 * @var array
	 */
	private $_missed_modules = [];

	/**
	 * Cached list of attributes saved in post meta.
	 *
	 * @var array
	 */
	private $_early_attributes = [];

	/**
	 * Missed blocks detected by late detection.
	 *
	 * @var array
	 */
	private $_missed_blocks = [];

	/**
	 * Missed shortcodes detected by late detection.
	 *
	 * @var array
	 */
	private $_missed_shortcodes = [];

	/**
	 * Track all modules used in the page.
	 *
	 * @var array
	 */
	private $_all_modules = [];

	/**
	 * List of modules to process for data collection.
	 *
	 * @var array
	 */
	private $_processed_modules = [];

	/**
	 * Check whether to use late detection mechanism in other areas.
	 *
	 * @var bool
	 */
	private $_need_late_generation = false;

	/**
	 * Keep track of processed files.
	 *
	 * @var array
	 */
	private $_processed_files = [];

	/**
	 * Is page builder used.
	 *
	 * @var array
	 */
	private $_page_builder_used = false;

	/**
	 * Default Gutter widths found during early detection.
	 *
	 * @var array
	 */
	private $_default_gutters = [];

	/**
	 * Gutter widths found during late detection.
	 *
	 * @var array
	 */
	private $_late_gutter_width = [];

	/**
	 * Preset attributes.
	 *
	 * @var bool
	 */
	private $_presets_attributes = [];

	/**
	 * Whether animations are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_use_animation_style = false;

	/**
	 * Whether link is found during late detection.
	 *
	 * @var bool
	 */
	private $_late_use_link = false;

	/**
	 * Whether parallax is found during late detection.
	 *
	 * @var bool
	 */
	private $_late_use_parallax = false;

	/**
	 * Whether specialty sections are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_use_specialty = false;

	/**
	 * Whether sticky options are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_use_sticky = false;

	/**
	 * Whether motion effects are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_use_motion_effect = false;

	/**
	 * Whether custom icons are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_custom_icon = false;

	/**
	 * Whether social icons are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_social_icon = false;

	/**
	 * Whether FontAwesome icons are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_fa_icon = false;

	/**
	 * Whether lightbox use is found during late detection.
	 *
	 * @var bool
	 */
	private $_late_show_in_lightbox = false;

	/**
	 * Whether blog modules set to 'show content' are found during late detection.
	 *
	 * @var bool
	 */
	private $_late_show_content = false;

	/**
	 * Whether fitvids should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_fitvids = [];

	/**
	 * Whether comments should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_comments = [];

	/**
	 * Whether jquery mobile should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_jquery_mobile = [];

	/**
	 * Whether magnific popup should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_magnific_popup = [];

	/**
	 * Whether easy pie chart should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_easypiechart = [];

	/**
	 * Whether toggle script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_toggle = [];

	/**
	 * Whether audio script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_audio = [];

	/**
	 * Whether video overlay script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_video_overlay = [];

	/**
	 * Whether search script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_search = [];

	/**
	 * Whether woo script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_woo = [];

	/**
	 * Whether fullwidth header script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_fullwidth_header = [];

	/**
	 * Whether blog script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_blog = [];

	/**
	 * Whether pagination script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_pagination = [];

	/**
	 * Whether fullscreen section script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_fullscreen_section = [];

	/**
	 * Whether section divider script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_section_dividers = [];

	/**
	 * Whether link script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_link = [];

	/**
	 * Whether slider script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_slider = [];

	/**
	 * Whether map script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_map = [];

	/**
	 * Whether sidebar script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_sidebar = [];

	/**
	 * Whether testimonial script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_testimonial = [];

	/**
	 * Whether tabs script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_tabs = [];

	/**
	 * Whether fullwidth portfolio script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_fullwidth_portfolio = [];

	/**
	 * Whether filterable portfolio script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_filterable_portfolio = [];

	/**
	 * Whether video slider script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_video_slider = [];

	/**
	 * Whether signup script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_signup = [];

	/**
	 * Whether countdown timer script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_countdown_timer = [];

	/**
	 * Whether bar counter script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_bar_counter = [];

	/**
	 * Whether circle counter script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_circle_counter = [];

	/**
	 * Whether number counter script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_number_counter = [];

	/**
	 * Whether contact form script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_contact_form = [];

	/**
	 * Whether form conditions script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_form_conditions = [];

	/**
	 * Whether menu module script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_menu = [];

	/**
	 * Whether animation module script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_animation = [];

	/**
	 * Whether gallery module script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_gallery = [];

	/**
	 * Whether logged in script should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_logged_in = [];

	/**
	 * Whether salvattore should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_salvattore = [];

	/**
	 * Whether split testing scripts should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_split_testing = [];

	/**
	 * Whether Google Maps should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_google_maps = [];

	/**
	 * Whether motion effects scrtipts should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_motion_effects = [];

	/**
	 * Whether sticky scripts should be enqueued.
	 *
	 * @var array
	 */
	private $_enqueue_sticky = [];

	/**
	 * Used global modules.
	 *
	 * @var array
	 */
	private $_global_modules = [];

	/**
	 * Used builder global presets.
	 *
	 * @var array
	 */
	private $_presets_feaure_used = [];

	/**
	 * Dynamic Enqueued Assets list.
	 *
	 * @var null
	 */
	private $_enqueued_assets = [];

	/**
	 * Whether to load resources to print all Divi icons ( icons_all.scss ).
	 *
	 * @var bool
	 */
	private $_use_divi_icons = false;

	/**
	 * Whether to load resources to print FA icons ( icons_fa_all.scss ).
	 *
	 * @var bool
	 */
	private $_use_fa_icons = false;

	/**
	 * Whether to load resources to print icons used in Social Follow Module ( icons_base_social.scss ).
	 *
	 * @var bool
	 */
	private $_use_social_icons = false;

	/**
	 * Global asset list found during early detection.
	 *
	 * @var array
	 */
	private $_early_global_asset_list = [];

	/**
	 * Global asset list found during late detection.
	 *
	 * @var array
	 */
	private $_late_global_asset_list = [];

	/**
	 * Block/Module Used.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_blocks_used = [];

	/**
	 * Module Attr Values Used.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_block_feature_used = [];

	/**
	 * Valid Block Names.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_verified_blocks = [];

	/**
	 * Valid Shortcode Slugs.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_verified_shortcodes = [];

	/**
	 * Keep track of shortcode use in the page.
	 *
	 * @var array
	 */
	protected $_shortcode_used = [];

	/**
	 * Keep track of interested attributes for late detection.
	 *
	 * @var array
	 */
	protected $_interested_attrs = [];

	/**
	 * Keep track of attribute use in the page.
	 *
	 * @var string
	 */
	protected $_attribute_used = '';

	/**
	 * Option for feature detections.
	 *
	 * @var array
	 */
	protected $_options = [
		'has_block'     => false,
		'has_shortcode' => false,
	];

	/**
	 * Keep Track of Divi WooCommerce Block detection.
	 *
	 * @var bool
	 */
	protected $_has_woocommerce_module_block = false;

	/**
	 * Keep Track of Divi WooCommerce Shortcode detection.
	 *
	 * @var bool
	 */
	protected $_has_woocommerce_module_shortcode = false;

	/**
	 * Whether to load global colors css.
	 *
	 * @var bool
	 */
	private $_use_global_colors = false;

	/**
	 * Track global color ids in early detections.
	 *
	 * @var array
	 */
	protected $_early_global_color_ids = [];

	/**
	 * Track global color ids in late detections.
	 *
	 * @var array
	 */
	protected $_late_global_color_ids = [];

	/**
	 * Class instance
	 *
	 * @since ??
	 *
	 * @var DynamicAssets Dynamic Assets Class instance.
	 */
	protected static $_instance = null;

	/**
	 * Load Critical CSS class.
	 *
	 * @since ??
	 */
	public function load() {
		global $shortname;

		DynamicAssetsUtils::ensure_cache_directory_exists();

		add_action( 'wp', array( $this, 'pre_initial_setup' ), 0 );
		add_action( 'wp', array( $this, 'initial_setup' ), 999 );

		// Enqueue early assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dynamic_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dynamic_scripts_early' ) );

		// If this is the Divi theme, add the divi filter to the global assets list.
		if ( 'divi' === $shortname ) {
			add_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', array( $this, 'divi_get_global_assets_list' ) );
		}

		// Detect Module/Block use.
		add_filter( 'render_block_data', [ $this, 'log_block_used' ], 99, 3 );
		add_filter( 'pre_do_shortcode_tag', [ $this, 'log_shortcode_used' ], 99, 4 );

		// Enqueue scripts and generate assets if late blocks or attributes are detected.
		add_action( 'wp_footer', array( $this, 'process_late_detection_and_output' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_dynamic_scripts_late' ) );

		add_action( 'wp_footer', [ $this, 'wp_footer' ] );

		// Add script that loads fallback .css during blog module ajax pagination.
		add_action( 'wp_footer', array( $this, 'maybe_inject_fallback_dynamic_assets' ) );
		// If a late file was generated, we grab it in the footer and then inject it into the header.
		add_action( 'divi_frontend_assets_dynamic_assets_utils_late_assets_generated', array( $this, 'maybe_inject_late_dynamic_assets' ), 0 );

		// Prepare list of modules based on assets list.
		self::_setup_verified_modules();
		add_action( 'et_builder_ready', array( $this, 'late_setup_verified_modules' ) );

		// Save the instance.
		self::$_instance = $this;
	}

	/**
	 * Get valid shortcodes slugs.
	 *
	 * @since ??
	 */
	public function _setup_verified_modules() {
		// Value for the filter.
		$additional_valid_names = [
			'core/gallery',
		];

		/**
		 * The "core/gallery" block is not part of the Divi modules but is used for enqueuing MagnificPopup
		 * when Divi Gallery is enabled under Theme Options > Enable Divi Gallery, so we need to include
		 * it in late detection for edge cases such as shortcodes hardcoded into child themes.
		 *
		 * @since ??
		 *
		 * @param array $additional_valid_names Additional Block Names.
		 */
		$additional_valid_names = apply_filters(
			'divi_frontend_assets_dynamic_assets_valid_blocks',
			$additional_valid_names
		);

		$this->_verified_blocks = array_unique( array_merge( DynamicAssetsUtils::get_divi_block_names(), $additional_valid_names ) );

		// Value for the filter.
		$additional_valid_shortcodes = [
			'gallery',
		];

		/**
		 * The "gallery" shortcode is not part of the Divi modules but is used for enqueuing MagnificPopup
		 * when Divi Gallery is enabled under Theme Options > Enable Divi Gallery, so we need to include
		 * it in late detection for edge cases such as shortcodes hardcoded into child themes.
		 *
		 * This filter is the replacement of Divi 4 filter `et_builder_valid_module_slugs`.
		 *
		 * @since ??
		 *
		 * @param array $additional_valid_shortcodes Additional Shortcode Tags.
		 */
		$additional_valid_shortcodes = apply_filters(
			'divi_frontend_assets_dynamic_assets_valid_module_slugs',
			$additional_valid_shortcodes
		);

		$this->_verified_shortcodes = array_unique( array_merge( DynamicAssetsUtils::get_divi_shortcode_slugs(), $additional_valid_shortcodes ) );

		// Value for the filter.
		$interested_attrs_and_values = [
			'gutter_width',
			'animation_style',
			'sticky_position',
			'specialty',
			'use_custom_gutter',
			'font_icon',
			'button_icon',
			'hover_icon',
			'scroll_down_icon',
			'social_network',
			'show_in_lightbox',
			'fullwidth',
			'scroll_vertical_motion_enable',
			'scroll_horizontal_motion_enable',
			'scroll_fade_enable',
			'scroll_scaling_enable',
			'scroll_rotating_enable',
			'scroll_blur_enable',
			'show_content',
		];

		/**
		 * Filters interested shortcode attributes to detect feature use.
		 *
		 * This filter is the replacement of Divi 4 filter `et_builder_module_attrs_values_used`.
		 *
		 * @since ??
		 *
		 * @param array $interested_attrs_and_values List of shortcode attribute name.
		 */
		$this->_interested_attrs = apply_filters(
			'divi_frontend_assets_dynamic_assets_module_attribute_used',
			$interested_attrs_and_values
		);
	}

	/**
	 * Late setup verified modules.
	 *
	 * During `_setup_verified_modules` and `pre_initial_setup` calls, it's not possible to set third party modules for
	 * `_verified_shortcodes` and `_early_shortcodes` properties because the shortcode framework is not loaded yet. So,
	 * there are no D4 modules initialized yet. This causes D4 third party modules to be missed in the early detection
	 * process and also not logged in `_shortcode_used` property that is used for the late detection process. It doesn't
	 * happen to D4 official modules because we define them in `DynamicAssetsUtils::get_divi_shortcode_slugs()`.
	 *
	 * Hence, to make sure D4 third party modules work with Dynamic Assets, we need to add them to `_verified_shortcodes`
	 * property for the late detection process once the D4 module shortcodes are initialized on `et_builder_ready` action.
	 *
	 * @see ET\Builder\VisualBuilder\SettingsData\SettingsDataCallbacks::shortcode_module_definitions()
	 *
	 * @since ??
	 */
	public function late_setup_verified_modules() {
		$third_party_modules      = \ET_Builder_Element::get_third_party_modules();
		$third_party_module_slugs = array_keys( $third_party_modules );

		$this->_verified_shortcodes = array_merge(
			$this->_verified_shortcodes,
			$third_party_module_slugs
		);
	}

	/**
	 * Pre initial setup.
	 *
	 * Initially, it's part of the `initial_setup` method. However, there are some processes that we need to run earlier
	 * due to `initial_setup` method runs too late on `wp` action with order `999`. Hence the `pre_initial_setup` method
	 * is created to run the process earlier on `wp` action with order `0`.
	 *
	 * NOTE: Please use `initial_setup` method for the main process and late detection. Only use `pre_initial_setup` for
	 * the process that needs to run earlier.
	 *
	 * @since ??
	 */
	public function pre_initial_setup() {
		global $post;

		$content_retriever = ET_Builder_Content_Retriever::init();
		$current_post_id   = is_singular() ? $post->ID : DynamicAssetsUtils::get_current_post_id();
		$current_post      = get_post( $current_post_id );

		// Set some Dynamic Assets class base properties.
		$_page_content      = $content_retriever->get_entire_page_content( $current_post );
		$this->_all_content = $this->maybe_add_global_modules_content( $_page_content );
		$this->_post_id     = ! empty( $current_post ) ? intval( $current_post_id ) : - 1;

		// When Dynamic Assets are disabled.
		if ( ! DynamicAssetsUtils::should_initiate_dynamic_assets() ) {
			$block_names = DetectFeature::get_block_names( $this->_all_content );

			if ( $block_names ) {
				// Check if any WooCommerce module exists in the post-content.
				$this->_has_woocommerce_module_block = DynamicAssetsUtils::contains_woocommerce_module_block( $block_names );
			}

			// Check whether the content have shortcodes.
			if ( DetectFeature::get_shortcode_names( $this->_all_content ) ) {
				// Add filters to get rid of random p tags.
				add_filter( 'the_content', [ HTMLUtility::class, 'fix_builder_shortcodes' ] );
				add_filter( 'et_builder_render_layout', [ HTMLUtility::class, 'fix_builder_shortcodes' ] );
				add_filter( 'the_content', 'et_pb_the_content_prep_code_module_for_wpautop', 0 );
				add_filter( 'et_builder_render_layout', 'et_pb_the_content_prep_code_module_for_wpautop', 0 );

				// Check if we need to load WooCommerce framework early.
				$this->maybe_load_early_framework( $this->_all_content );
			}

			// Bail early.
			return;
		}

		// If cached blocks exist, grab them from the post meta.
		if ( $this->metadata_exists( '_divi_dynamic_assets_cached_modules' ) ) {
			$used_modules = $this->metadata_get( '_divi_dynamic_assets_cached_modules' );

			$this->_early_blocks     = $used_modules['blocks'] ?? [];
			$this->_early_shortcodes = $used_modules['shortcodes'] ?? [];
		} else {
			// If there are no cached modules, parse the post content to retrieve used blocks.
			$used_modules = $this->get_early_modules( $this->_all_content );

			$this->_early_blocks     = $used_modules['blocks'] ?? [];
			$this->_early_shortcodes = $used_modules['shortcodes'] ?? [];

			// Cache the early blocks/shortcodes to the meta.
			$this->metadata_set(
				'_divi_dynamic_assets_cached_modules',
				[
					'blocks'     => $this->_early_blocks,
					'shortcodes' => $this->_early_shortcodes,
				]
			);
		}

		if ( ! empty( $this->_early_shortcodes ) ) {
			// Add filters to fix the shortcodes.
			add_filter( 'the_content', [ HTMLUtility::class, 'fix_builder_shortcodes' ] );
			add_filter( 'et_builder_render_layout', [ HTMLUtility::class, 'fix_builder_shortcodes' ] );
			add_filter( 'the_content', 'et_pb_the_content_prep_code_module_for_wpautop', 0 );
			add_filter( 'et_builder_render_layout', 'et_pb_the_content_prep_code_module_for_wpautop', 0 );

			// Check if we need to load WooCommerce framework early.
			$this->maybe_load_early_framework( $this->_all_content );
		}

		// Update _early_modules.
		if ( ! empty( $this->_early_shortcodes ) ) {
			// Convert shortcode names to block names for asset management.
			$this->_early_modules = array_unique(
				array_merge(
					$this->_early_blocks,
					array_map( [ DynamicAssetsUtils::class, 'get_block_name_from_shortcode' ], $this->_early_shortcodes )
				)
			);
		} else {
			$this->_early_modules = $this->_early_blocks;
		}

		// Track block/shortcode use.
		$this->_options['has_block']     = ! empty( $this->_early_blocks );
		$this->_options['has_shortcode'] = ! empty( $this->_early_shortcodes );
	}

	/**
	 * Initial setup.
	 */
	public function initial_setup() {
		// Don't do anything if it's not needed.
		if ( ! DynamicAssetsUtils::should_initiate_dynamic_assets() ) {
			return;
		}

		global $shortname, $post;

		if ( DynamicAssetsUtils::is_taxonomy() ) {
			$this->_object_id = intval( get_queried_object()->term_id );
		} elseif ( is_search() || DynamicAssetsUtils::is_virtual_page() ) {
			$this->_object_id = - 1;
		} elseif ( is_singular() ) {
			$this->_object_id = $post->ID;
		}

		$post_stack_replaced = false;

		if ( 'extra' === $shortname ) {
			if ( ( ( DynamicAssetsUtils::is_extra_layout_used_as_home() || DynamicAssetsUtils::is_extra_layout_used_as_front() ) && ! is_null( DynamicAssetsUtils::extra_get_home_layout_id() ) ) ) {
				$this->_object_id = DynamicAssetsUtils::extra_get_home_layout_id();
			} elseif ( ( is_category() || is_tag() ) && ! is_null( DynamicAssetsUtils::extra_get_tax_layout_id() ) ) {
				$this->_object_id = DynamicAssetsUtils::extra_get_tax_layout_id();
			}

			// Replace the post stack if an Extra Layout is used.
			if ( DynamicAssetsUtils::extra_get_home_layout_id() === $this->_object_id || DynamicAssetsUtils::extra_get_tax_layout_id() === $this->_object_id ) {
				ET_Post_Stack::replace( get_post( $this->_object_id ) );
				$post_stack_replaced = true;
			}
		}

		$this->_folder_name = $this->get_folder_name();

		// Don't process Dynamic CSS logic if it's not needed or can't be processed.
		if ( ! $this->is_cachable_request() ) {
			return;
		}

		if ( 'divi' === $shortname ) {
			$this->_owner = 'divi';
		} elseif ( 'extra' === $shortname ) {
			$this->_owner = 'extra';
		} elseif ( et_is_builder_plugin_active() ) {
			$this->_owner = 'divi-builder';
		}

		$cache_dir                = ET_Core_Cache_Directory::instance();
		$this->_tb_template_ids   = DynamicAssetsUtils::get_theme_builder_template_ids();
		$this->_cache_dir_path    = $cache_dir->path;
		$this->_cache_dir_url     = $cache_dir->url;
		$this->_product_dir       = et_is_builder_plugin_active() ? ET_BUILDER_PLUGIN_URI : get_template_directory_uri();
		$this->_cpt_suffix        = et_builder_should_wrap_styles() && ! et_is_builder_plugin_active() ? '_cpt' : '';
		$this->is_rtl             = is_rtl();
		$this->_rtl_suffix        = $this->is_rtl ? '_rtl' : '';
		$this->_page_builder_used = is_singular() && et_pb_is_pagebuilder_used( $this->_post_id );
		$this->_tb_prefix         = $this->_tb_template_ids ? '-tb' : '';
		$generate_assets          = true;

		// Create asset directory, if it does not exist.
		$ds       = DIRECTORY_SEPARATOR;
		$file_dir = "{$this->_cache_dir_path}{$ds}{$this->_folder_name}{$ds}";

		et_()->ensure_directory_exists( $file_dir );

		// If cached attributes exist, grab them from the post meta.
		if ( $this->metadata_exists( '_divi_dynamic_assets_cached_feature_used' ) ) {
			$this->_early_attributes = $this->metadata_get( '_divi_dynamic_assets_cached_feature_used' );
		}

		$files = (array) glob( "{$this->_cache_dir_path}/{$this->_folder_name}/et*-dynamic*{$this->_tb_prefix}*" );

		foreach ( $files as $file ) {
			if ( ! et_()->includes( $file, '-late' ) ) {
				$generate_assets = false;
				break;
			}
		}

		if ( $generate_assets ) {
			// If we are regenerating early assets that are missing, we should clear the old assets as well.
			if ( $files && ! $this->_need_late_generation ) {
				ET_Core_PageResource::remove_static_resources( $this->_post_id, 'all', false, 'dynamic' );
			}

			$this->generate_dynamic_assets();
		}

		// Restore the post stack if it's replaced earlier.
		if ( $post_stack_replaced ) {
			ET_Post_Stack::restore();
		}
	}

	/**
	 * Get cache directory name for the current page.
	 *
	 * @since  ??
	 * @return string
	 */
	public function get_folder_name() {
		$folder_name = $this->_object_id;

		if ( DynamicAssetsUtils::is_taxonomy() ) {
			$queried     = get_queried_object();
			$taxonomy    = sanitize_key( $queried->taxonomy );
			$folder_name = "taxonomy/{$taxonomy}/" . $this->_object_id;
		} elseif ( is_search() ) {
			$folder_name = 'search';
		} elseif ( is_author() ) {
			$author_id   = get_queried_object_id();
			$folder_name = "author/{$author_id}";
		} elseif ( is_archive() ) {
			$folder_name = 'archive';
		} elseif ( is_home() ) {
			$folder_name = 'home';
		} elseif ( is_404() ) {
			$folder_name = 'notfound';
		}

		if ( DynamicAssetsUtils::is_extra_layout_used_as_home() ) {
			$folder_name = $this->_object_id;
		}

		return $folder_name;
	}

	/**
	 * Get dynamic assets of a page.
	 *
	 * @since  ??
	 * @return array|void
	 */
	public function get_dynamic_assets_files() {
		if ( ! $this->is_cachable_request() ) {
			return;
		}

		$dynamic_assets_files = [];
		$files                = (array) glob( "{$this->_cache_dir_path}/{$this->_folder_name}/et*-dynamic*{$this->_tb_prefix}*" );

		if ( empty( $files ) ) {
			return [];
		}

		foreach ( $files as $file ) {
			$file_path              = et_()->normalize_path( $file );
			$dynamic_assets_files[] = et_()->path( $this->_cache_dir_url, $this->_folder_name, basename( $file_path ) );
		}

		return $dynamic_assets_files;
	}

	/**
	 * Get class instance.
	 *
	 * @since ??
	 *
	 * @return DynamicAssets Dynamic Assets Class instance.
	 */
	public static function get_instance(): ?DynamicAssets {
		return self::$_instance;
	}

	/**
	 * Get a list of blocks used in current page.
	 *
	 * @return array
	 */
	public function get_saved_page_blocks(): array {
		$used_modules = $this->metadata_get( '_divi_dynamic_assets_cached_modules' );

		if ( empty( $used_modules ) ) {
			return [];
		}

		if ( ! empty( $used_modules['shortcodes'] ) ) {
			// Convert shortcode names to block names.
			$all_modules = array_unique(
				array_merge(
					$used_modules['blocks'],
					array_map( [ DynamicAssetsUtils::class, 'get_block_name_from_shortcode' ], $used_modules['shortcodes'] )
				)
			);
		} else {
			$all_modules = $used_modules['blocks'];
		}

		return $all_modules;
	}

	/**
	 * Get the handle of current theme's style.css handle.
	 *
	 * @since 4.10.0
	 */
	public function get_style_css_handle(): string {
		$child_theme_suffix  = is_child_theme() && ! et_is_builder_plugin_active() ? '-parent' : '';
		$inline_style_suffix = et_core_is_inline_stylesheet_enabled() ? '-inline' : '';
		$product_prefix      = $this->_owner . '-style';

		$handle = 'divi-builder-style' === $product_prefix . $inline_style_suffix ? $product_prefix : $product_prefix . $child_theme_suffix . $inline_style_suffix;

		return $handle;
	}

	/**
	 * Check to see if Dynamic Assets ia applicable to current page request.
	 *
	 * @since  ??
	 * @return bool.
	 */
	public function is_cachable_request(): bool {
		if ( is_null( self::$_is_cachable_request ) ) {
			self::$_is_cachable_request = true;

			// Bail if this is not a front-end page request.
			if ( ! DynamicAssetsUtils::should_generate_dynamic_assets() ) {
				self::$_is_cachable_request = false;

				return self::$_is_cachable_request;
			}

			// Bail if Dynamic CSS is disabled.
			if ( ! DynamicAssetsUtils::use_dynamic_assets() ) {
				self::$_is_cachable_request = false;

				return self::$_is_cachable_request;
			}

			// Bail if the page has no designated cache folder and is not cachable.
			if ( ! $this->_folder_name ) {
				self::$_is_cachable_request = false;

				return self::$_is_cachable_request;
			}
		}

		return self::$_is_cachable_request;
	}

	/**
	 * Enqueues the assets needed for the modules that are present on the page.
	 *
	 * @since 4.10.0
	 */
	public function enqueue_dynamic_assets() {
		$dynamic_assets = $this->get_dynamic_assets_files();

		if ( empty( $dynamic_assets ) || ! DynamicAssetsUtils::use_dynamic_assets() ) {
			return;
		}

		$body = [];
		$head = [];

		$cache_dir = ET_Core_Cache_Directory::instance();
		$base_url  = $cache_dir->url;
		$base_path = $cache_dir->path;

		$version = ET_BUILDER_PRODUCT_VERSION;

		foreach ( $dynamic_assets as $dynamic_asset ) {
			// Ignore empty files.
			$abs_file = str_replace( $base_url, $base_path, $dynamic_asset );
			if ( 0 === et_()->WPFS()->size( $abs_file ) ) {
				continue;
			}

			$type     = pathinfo( wp_parse_url( $dynamic_asset, PHP_URL_PATH ), PATHINFO_EXTENSION );
			$filename = pathinfo( wp_parse_url( $dynamic_asset, PHP_URL_PATH ), PATHINFO_FILENAME );
			$filepath = et_()->path( $this->_cache_dir_path, $this->_folder_name, "{$filename}.{$type}" );

			// Bust PHP's stats cache for the resource file to ensure we get the latest timestamp.
			clearstatcache( true, $filepath );

			$filetime    = filemtime( $filepath );
			$version     = $filetime ? $filetime : ET_BUILDER_PRODUCT_VERSION;
			$is_late     = false !== strpos( $filename, 'late' );
			$is_critical = false !== strpos( $filename, 'critical' );
			$is_css      = 'css' === $type;
			$late_slug   = true === $is_late ? '-late' : '';

			$deps   = array( $this->get_style_css_handle() );
			$handle = $this->_owner . '-dynamic' . $late_slug;

			if ( wp_style_is( $handle ) ) {
				continue;
			}

			$in_footer = false !== strpos( $dynamic_asset, 'footer' );

			$asset = (object) array(
				'type'      => $type,
				'src'       => $dynamic_asset,
				'deps'      => $deps,
				'in_footer' => $is_css ? 'all' : $in_footer,
			);

			if ( $is_critical ) {
				$body[ $handle ] = $asset;
			} else {
				$head[ $handle ] = $asset;
			}
		}

		// Enqueue inline styles.
		if ( ! empty( $body ) ) {
			$this->_enqueued_assets = (object) array(
				'head' => $head,
				'body' => $body,
			);

			$cache_dir = ET_Core_Cache_Directory::instance();
			$path      = $cache_dir->path;
			$url       = $cache_dir->url;
			$styles    = '';
			$handle    = '';

			foreach ( $this->_enqueued_assets->body as $handle => $asset ) {
				$file    = str_replace( $url, $path, $asset->src );
				$styles .= et_()->WPFS()->get_contents( $file );
			}

			$handle .= '-critical';

			// Create empty style which will enqueue no external file but still allow us
			// to add inline content to it.
			wp_register_style( $handle, false, array( $this->get_style_css_handle() ), $version );
			wp_enqueue_style( $handle );
			wp_add_inline_style( $handle, $styles );

			add_filter( 'style_loader_tag', array( $this, 'defer_head_style' ), 10, 4 );
		}

		// Enqueue styles.
		foreach ( $head as $handle => $asset ) {
			$is_css           = 'css' === $asset->type;
			$enqueue_function = $is_css ? 'wp_enqueue_style' : 'wp_enqueue_script';

			$enqueue_function(
				$handle,
				$asset->src,
				$asset->deps,
				$version,
				$asset->in_footer
			);
		}
	}

	/**
	 * Print deferred styles in the head.
	 *
	 * @since ??
	 *
	 * @param string $tag    The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $href   The stylesheet's source URL.
	 * @param string $media  The stylesheet's media attribute.
	 *
	 * @return string
	 */
	public function defer_head_style( string $tag, string $handle, string $href, string $media ): string {
		if ( empty( $this->_enqueued_assets->head[ $handle ] ) ) {
			// Ignore assets not enqueued by this class.
			return $tag;
		}

		// Use 'prefetch' when Mod PageSpeed is detected because it removes 'preload' links.
		$rel = et_builder_is_mod_pagespeed_enabled() ? 'prefetch' : 'preload';

		/* This filter is documented in includes/builder-5/server/FrontEnd/Assets/CriticalCSS.php */
		$rel = apply_filters( 'divi_frontend_assets_ctitical_css_deferred_styles_rel', $rel );

		return sprintf(
			"<link rel='%s' id='%s-css' href='%s' as='style' media='%s' onload=\"%s\" />\n",
			$rel,
			$handle,
			$href,
			$media,
			"this.onload=null;this.rel='stylesheet'"
		);
	}

	/**
	 * Generates asset files to be combined on the front-end.
	 *
	 * @since ??
	 *
	 * @param array  $assets_data Assets Data.
	 * @param string $suffix      Additional file name suffix.
	 *
	 * @return void
	 */
	public function generate_dynamic_assets_files( array $assets_data = [], string $suffix = '' ) {
		global $wp_filesystem;

		$tb_ids                  = '';
		$current_tb_template_ids = $this->_tb_template_ids;
		$late_suffix             = '';
		$file_contents           = '';

		if ( $this->_need_late_generation ) {
			$late_suffix = '-late';
		}

		if ( ! empty( $current_tb_template_ids ) ) {
			foreach ( $current_tb_template_ids as $key => $value ) {
				$current_tb_template_ids[ $key ] = 'tb-' . $value;
			}
			$tb_ids = '-' . implode( '-', $current_tb_template_ids );
		}

		$ds            = DIRECTORY_SEPARATOR;
		$file_dir      = "{$this->_cache_dir_path}{$ds}{$this->_folder_name}{$ds}";
		$maybe_post_id = is_singular() ? '-' . $this->_post_id : '';

		if ( DynamicAssetsUtils::is_extra_layout_used_as_home() && ! is_null( DynamicAssetsUtils::extra_get_home_layout_id() ) ) {
			$maybe_post_id = '-' . DynamicAssetsUtils::extra_get_home_layout_id();
		}

		$suffix    = empty( $suffix ) ? '' : "-{$suffix}";
		$file_name = "et-{$this->_owner}-dynamic{$tb_ids}{$maybe_post_id}{$late_suffix}{$suffix}.css";
		$file_path = et_()->normalize_path( "{$file_dir}{$file_name}" );

		if ( file_exists( $file_path ) ) {
			return;
		}

		// Iterate over all the asset data to generate dynamic asset files.
		foreach ( $assets_data as $file_type => $data ) {
			$file_contents .= implode( "\n", array_unique( $data['content'] ) );
		}

		$wp_filesystem->put_contents( $file_path, $file_contents, FS_CHMOD_FILE );
	}

	/**
	 * Inject fallback assets when needed.
	 * We don't know what content might appear on blog module pagination.
	 * Fallback .css is injected on these pages.
	 *
	 * @since ??
	 * @return void
	 */
	public function maybe_inject_fallback_dynamic_assets() {
		if ( ! $this->is_cachable_request() ) {
			return;
		}

		$show_content = DetectFeature::has_excerpt_content_on( $this->_all_content, $this->_options ) || $this->_late_show_content;

		if ( in_array( 'divi/blog', $this->_all_modules, true ) && $show_content ) {
			$assets_path   = DynamicAssetsUtils::get_dynamic_assets_path( true );
			$fallback_file = "{$assets_path}/css/_fallback{$this->_cpt_suffix}{$this->_rtl_suffix}.css";

			// Inject the fallback assets into `<head>`.
			?>
			<script type="application/javascript">
				(function() {
					var fallback_styles = <?php echo wp_json_encode( $fallback_file ); ?>;
					var pagination_link = document.querySelector('.et_pb_ajax_pagination_container .wp-pagenavi a,.et_pb_ajax_pagination_container .pagination a');

					if (pagination_link && fallback_styles.length) {
						pagination_link.addEventListener('click', function(event) {
							if (0 === document.querySelectorAll('link[href="' + fallback_styles + '"]').length) {
								var link = document.createElement('link');
								link.rel = "stylesheet";
								link.id = 'et-dynamic-fallback-css';
								link.href = fallback_styles;

								document.getElementsByTagName('head')[0].appendChild(link);
							}
						});
					}
				})();
			</script>
			<?php
		}
	}

	/**
	 * Inject late dynamic assets when needed.
	 * If late .css files exist, we need to grab them and
	 * inject them in the head.
	 *
	 * @since ??
	 * @return void
	 */
	public function maybe_inject_late_dynamic_assets() {
		if ( ! $this->is_cachable_request() ) {
			return;
		}

		$late_assets         = [];
		$late_files          = (array) glob( "{$this->_cache_dir_path}/{$this->_folder_name}/et-{$this->_owner}-dynamic*late*" );
		$style_handle        = $this->get_style_css_handle();
		$inline_style_suffix = et_core_is_inline_stylesheet_enabled() ? '-inline' : '';

		if ( ! empty( $late_files ) ) {
			foreach ( $late_files as $file ) {
				$file_path       = et_()->normalize_path( $file );
				$late_asset_url  = esc_url_raw( et_()->path( $this->_cache_dir_url, $this->_folder_name, basename( $file_path ) ) );
				$late_asset_size = filesize( $file_path );

				if ( $late_asset_size ) {
					$late_assets[] = $late_asset_url;
				}
			}
		}

		// Don't inject empty files.
		if ( ! $late_assets ) {
			return;
		}

		// Inject the late assets into `<head>`.
		?>
		<script type="application/javascript">
			(function() {
				var file = <?php echo wp_json_encode( $late_assets ); ?>;
				var handle = document.getElementById('<?php echo esc_html( $style_handle . $inline_style_suffix . '-css' ); ?>');
				var location = handle.parentNode;

				if (0 === document.querySelectorAll('link[href="' + file + '"]').length) {
					var link = document.createElement('link');
					link.rel = 'stylesheet';
					link.id = 'et-dynamic-late-css';
					link.href = file;

					location.insertBefore(link, handle.nextSibling);
				}
			})();
		</script>
		<?php
	}

	/**
	 * Merges global assets and blocks assets and
	 * sends the list to generate_dynamic_assets_files() for file generation.
	 *
	 * @since ??
	 * @return void
	 */
	public function generate_dynamic_assets() {
		if ( ! $this->is_cachable_request() ) {
			return;
		}

		$split_global_data = [];
		$atf_blocks        = [];

		if ( $this->_need_late_generation ) {
			$this->_processed_modules = $this->_missed_modules;
			$global_assets_list       = DynamicAssetsUtils::get_new_array_values( $this->get_late_global_assets_list(), $this->_early_global_asset_list );
		} else {
			$this->_presets_feaure_used = $this->presets_feature_used( $this->_all_content );
			$this->_processed_modules   = $this->_early_modules;
			$global_assets_list         = $this->get_global_assets_list();

			// Value for the `divi_frontend_assets_dynamic_assets_modules_atf` filter.
			$content = $this->_all_content;

			/**
			 * Filters the Above The Fold blocks.
			 *
			 * This filter is the replacement of Divi 4 filter `et_dynamic_assets_modules_atf`.
			 *
			 * @since ??
			 *
			 * @param array  $atf_blocks Above The Fold blocks.
			 * @param string $content    Theme Builder Content / Post Content.
			 */
			$atf_blocks = apply_filters( 'divi_frontend_assets_dynamic_assets_modules_atf', $atf_blocks, $content );

			// Initial value for the `et_dynamic_assets_content` filter.
			$split_content = false;

			/**
			 * Filters whether Content can be split in Above The Fold / Below The Fold.
			 *
			 * This filter is the replacement of Divi 4 filter `et_dynamic_assets_content`.
			 *
			 * @since ??
			 *
			 * @param bool|object $split_content Builder Post Content.
			 */
			$split_content = apply_filters( 'divi_frontend_assets_dynamic_assets_content', $split_content );

			if ( 'object' === gettype( $split_content ) ) {
				$split_global_data = $this->split_global_assets_data( $split_content, $global_assets_list );
			}
		}

		$block_assets_list = $this->get_block_assets_list();

		if ( empty( $split_global_data ) ) {
			$assets_data = $this->get_assets_data( array_merge( $global_assets_list, $block_assets_list ) );
			$this->generate_dynamic_assets_files( $assets_data );
		} else {
			$btf_block_assets_list = $block_assets_list;
			$atf_block_assets_list = [];

			foreach ( $atf_blocks as $block_name ) {
				if ( isset( $block_assets_list[ $block_name ] ) ) {
					$atf_block_assets_list[ $block_name ] = $block_assets_list[ $block_name ];
					unset( $btf_block_assets_list[ $block_name ] );
				}
			}

			$atf_assets_data = $this->get_assets_data( array_merge( $split_global_data['atf'], $atf_block_assets_list ) );

			// Gotta reset this or else `get_assets_data` not going to return the correct set.
			$this->_processed_files = [];
			$btf_assets_data        = $this->get_assets_data( array_merge( $split_global_data['btf'], $btf_block_assets_list ) );

			$this->generate_dynamic_assets_files( $atf_assets_data, 'critical' );
			$this->generate_dynamic_assets_files( $btf_assets_data );
		}
	}

	/**
	 * Generate late assets if needed.
	 *
	 * @since 4.10.0
	 */
	public function process_late_detection_and_output() {
		// Late detection.
		$this->get_late_blocks();
		$this->get_late_attributes();

		// Late assets determination.
		if ( $this->_need_late_generation ) {
			$this->generate_dynamic_assets();

			/**
			 * Fires after late detected assets are generated.
			 *
			 * @since 4.10.0
			 */
			do_action( 'divi_frontend_assets_dynamic_assets_utils_late_assets_generated' );
		}
	}

	/**
	 * Get block assets data.
	 *
	 * @since ??
	 *
	 * @param array $asset_list Assets list.
	 *
	 * @return array
	 */
	public function get_assets_data( array $asset_list = [] ): array {
		global $wp_filesystem;

		$assets_data           = [];
		$newly_processed_files = [];
		$files_with_url        = array( 'signup', 'icons_base', 'icons_base_social', 'icons_all', 'icons_fa_all' );
		$no_protocol_path      = str_replace( array( 'http:', 'https:' ), '', $this->_product_dir );

		foreach ( $asset_list as $asset => $asset_data ) {
			foreach ( $asset_data as $file_type => $files ) {
				$files = (array) $files;

				foreach ( $files as $file ) {
					// Make sure same file's content is not loaded more than once.
					if ( in_array( $file, $this->_processed_files, true ) ) {
						continue;
					}

					$newly_processed_files[] = $file;

					// For global colors css, we're passing the content instead of file path.
					if ( ( 'et_early_global_colors' === $asset || 'et_late_global_colors' === $asset ) && 'css' === $file_type ) {
						$file_content = $file;
					} else {
						$file_content = $wp_filesystem->get_contents( $file );

						if ( in_array( basename( $file, '.css' ), $files_with_url, true ) ) {
							$file_content = preg_replace( '/#dynamic-product-dir/i', $no_protocol_path, $file_content );
						}

						$file_content = trim( $file_content );
					}

					if ( empty( $file_content ) ) {
						continue;
					}

					$assets_data[ $file_type ]['assets'][]  = $asset;
					$assets_data[ $file_type ]['content'][] = $file_content;

					if ( $this->is_rtl ) {
						$file_rtl = str_replace( ".{$file_type}", "-rtl.{$file_type}", $file );

						if ( file_exists( $file_rtl ) ) {
							$file_content_rtl = $wp_filesystem->get_contents( $file_rtl );

							$assets_data[ $file_type ]['assets'][]  = "{$asset}-rtl";
							$assets_data[ $file_type ]['content'][] = $file_content_rtl;
						}
					}
				}
			}
		}

		$this->_processed_files = DynamicAssetsUtils::get_unique_array_values( $this->_processed_files, $newly_processed_files );

		return $assets_data;
	}

	/**
	 * Gets a list of global asset files.
	 *
	 * @since ??
	 * @return array
	 */
	public function get_global_assets_list(): array {
		if ( ! $this->is_cachable_request() ) {
			return [];
		}

		if ( ! $this->_use_global_colors ) {
			$this->_use_global_colors = true;

			// Get the page settings attributes.
			$page_setting_attributes = DynamicAssetsUtils::get_page_setting_attributes(
				$this->_post_id,
				[
					'et_pb_content_area_background_color',
					'et_pb_section_background_color',
					'et_pb_light_text_color',
					'et_pb_dark_text_color',
					'et_pb_custom_css',
				]
			);

			// Get global color ids from the content and page settings.
			$_early_global_color_ids = DetectFeature::get_global_color_ids( $this->_all_content . wp_json_encode( $page_setting_attributes ) );

			// Merge global color ids from the content and page settings, ensuring that global colors from `Customizer` is always included.
			$this->_early_global_color_ids = DynamicAssetsUtils::get_unique_array_values(
				$_early_global_color_ids,
				array_keys( GlobalData::get_customizer_colors() )
			);

			// Set global colors variable for the Critical CSS.
			$this->_early_global_asset_list['et_early_global_colors'] = array(
				'css' => Style::get_global_colors_style( $this->_early_global_color_ids ),
			);
		}

		$assets_prefix     = DynamicAssetsUtils::get_dynamic_assets_path();
		$dynamic_icons     = DynamicAssetsUtils::use_dynamic_icons();
		$social_icons_deps = array(
			'divi/social-media-follow',
			'divi/team-member',
		);

		if ( ! $this->_use_divi_icons || ! $this->_use_fa_icons ) {
			if ( empty( $this->_presets_attributes ) ) {
				$this->_presets_attributes = $this->presets_feature_used( $this->_all_content );
			}

			// Check for icons existence in presets.
			$maybe_presets_contain_divi_icon = false;
			$maybe_presets_contain_fa_icon   = false;

			if ( ! $this->_use_divi_icons && $this->_presets_attributes['icon_font_divi'] ) {
				$maybe_presets_contain_divi_icon = true;
			}

			if ( ! $this->_use_fa_icons && $this->_presets_attributes['icon_font_fa'] ) {
				$maybe_presets_contain_fa_icon = true;
			}

			$maybe_post_contains_divi_icon = $this->_use_divi_icons || $maybe_presets_contain_divi_icon;

			if ( ! $maybe_post_contains_divi_icon ) {
				$maybe_post_contains_divi_icon = DetectFeature::has_icon_font( $this->_all_content, 'divi', $this->_options );
			}

			// Load the icon font needed based on the icons being used.
			$this->_use_divi_icons = $this->_use_divi_icons || ( 'on' !== $dynamic_icons || $maybe_post_contains_divi_icon || $this->check_if_class_exits( 'et-pb-icon', $this->_all_content ) );

			$this->_use_fa_icons = $this->_use_fa_icons || $maybe_presets_contain_fa_icon;

			if ( ! $this->_use_fa_icons ) {
				$this->_use_fa_icons = ( $this->check_for_dependency( DynamicAssetsUtils::get_font_icon_modules(), $this->_processed_modules ) && DetectFeature::has_icon_font( $this->_all_content, 'fa', $this->_options ) );
			}
		}

		if ( ! $this->_use_social_icons ) {
			$this->_use_social_icons = $this->check_for_dependency( $social_icons_deps, $this->_processed_modules );

			if ( $this->_use_social_icons && ! $this->_use_fa_icons ) {
				$this->_use_fa_icons = DetectFeature::has_social_follow_icon_font( $this->_all_content, 'fa', $this->_options );
			}
		}

		if ( $this->_use_divi_icons ) {
			$this->_early_global_asset_list['et_icons_all'] = array(
				'css' => "{$assets_prefix}/css/icons_all.css",
			);
		} elseif ( $this->_use_social_icons ) {
			$this->_early_global_asset_list['et_icons_social'] = array(
				'css' => "{$assets_prefix}/css/icons_base_social.css",
			);
		} else {
			$this->_early_global_asset_list['et_icons_base'] = array(
				'css' => "{$assets_prefix}/css/icons_base.css",
			);
		}

		if ( $this->_use_fa_icons ) {
			$this->_early_global_asset_list['et_icons_fa'] = array(
				'css' => "{$assets_prefix}/css/icons_fa_all.css",
			);
		}

		// Only include the following assets on post feeds and posts that aren't using the builder.
		if ( ( is_single() && ! $this->_page_builder_used ) || ( is_home() && ! is_front_page() ) || ! is_singular() ) {
			$this->_early_global_asset_list['et_post_formats'] = array(
				'css' => array(
					"{$assets_prefix}/css/post_formats{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/slider_base{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/slider_controls{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/overlay{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/audio_player{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/video_player{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/wp_gallery{$this->_cpt_suffix}.css",
				),
			);
		}

		// Load posts styles on posts and post feeds.
		if ( ! is_page() ) {
			$this->_early_global_asset_list['et_posts'] = array(
				'css' => "{$assets_prefix}/css/posts{$this->_cpt_suffix}.css",
			);
		}

		if ( $this->is_rtl ) {
			$this->_early_global_asset_list['et_divi_shared_conditional_rtl'] = array(
				'css' => "{$assets_prefix}/css/shared-conditional-style{$this->_cpt_suffix}-rtl.css",
			);
		}

		// Check for specialty sections.
		$specialty_used = DetectFeature::has_specialty_section( $this->_all_content, $this->_options );

		// Check for custom gutter widths.
		$page_custom_gutter = is_singular()
			? [ intval( get_post_meta( $this->_post_id, '_et_pb_gutter_width', true ) ) ]
			: [];

		// Add custom gutters in TB templates.
		if ( ! empty( $this->_tb_template_ids ) ) {
			foreach ( $this->_tb_template_ids as $template_id ) {
				$page_custom_gutter[] = intval( get_post_meta( $template_id, '_et_pb_gutter_width', true ) );
			}
		}

		$preset_gutter_val      = $this->_presets_feaure_used['gutter_widths'];
		$customizer_gutter      = intval( et_get_option( 'gutter_width', '3' ) );
		$this->_default_gutters = array_merge( $page_custom_gutter, (array) $customizer_gutter );

		// Here we are combining the custom gutters in the page with Default gutters and then keeping only the unique gutters.
		$gutter_widths = DynamicAssetsUtils::get_unique_array_values(
			DetectFeature::get_gutter_widths( $this->_all_content, $this->_options ),
			$this->_default_gutters,
			$preset_gutter_val
		);
		$gutter_length = count( $gutter_widths );

		$grid_items_deps = array(
			'divi/filterable-portfolio',
			'divi/fullwidth-portfolio',
			'divi/portfolio',
			'divi/gallery',
			'divi/woocommerce-product-gallery',
			'divi/blog',
			'divi/sidebar',
			'divi/shop',
		);

		$grid_items_used = $this->check_for_dependency( $grid_items_deps, $this->_processed_modules );

		if ( ! empty( $gutter_widths ) ) {
			$this->_early_global_asset_list = array_merge(
				$this->_early_global_asset_list,
				$this->get_gutters_asset_list( $gutter_widths, $specialty_used, $grid_items_used )
			);
		}

		// Load WooCommerce css when WooCommerce is active.
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->_early_global_asset_list['et_divi_woocommerce_modules'] = array(
				'css' => array(
					"{$assets_prefix}/css/woocommerce{$this->_cpt_suffix}.css",
					"{$assets_prefix}/css/woocommerce_shared{$this->_cpt_suffix}.css",
				),
			);
		}

		// Load PageNavi css when PageNavi is active.
		if ( is_plugin_active( 'wp-pagenavi/wp-pagenavi.php' ) ) {
			$this->_early_global_asset_list['et_divi_wp_pagenavi'] = array(
				'css' => "{$assets_prefix}/css/wp-page_navi{$this->_cpt_suffix}.css",
			);
		}

		$show_in_lightbox = DetectFeature::has_lightbox( $this->_all_content, $this->_options );

		if ( $show_in_lightbox ) {
			$this->_early_global_asset_list['et_jquery_magnific_popup'] = array(
				'css' => "{$assets_prefix}/css/magnific_popup.css",
			);
		}

		$has_animation_style = DetectFeature::has_animation_style( $this->_all_content, $this->_options );

		// Load animation assets if any module uses animations.
		if ( $has_animation_style || in_array( 'divi/circle-counter', $this->_processed_modules, true ) ) {
			$this->_early_global_asset_list['animations'] = array(
				'css' => "{$assets_prefix}/css/animations{$this->_cpt_suffix}.css",
			);
		}

		$sticky_used = DetectFeature::has_sticky_position_enabled( $this->_all_content, $this->_options );

		if ( $sticky_used ) {
			$this->_early_global_asset_list['sticky'] = array(
				'css' => "{$assets_prefix}/css/sticky_elements{$this->_cpt_suffix}.css",
			);
		}

		// Collect and pass all needed assets arguments.
		$assets_args = array(
			'assets_prefix'       => $assets_prefix,
			'dynamic_icons'       => $dynamic_icons,
			'cpt_suffix'          => $this->_cpt_suffix,
			'use_all_icons'       => $this->_use_divi_icons,
			'show_in_lightbox'    => $show_in_lightbox,
			'has_animation_style' => $has_animation_style,
			'sticky_used'         => $sticky_used,
			// Gutter/grid items processed info.
			'gutter_widths'       => $gutter_widths,
			'gutter_length'       => $gutter_length,
			'specialty_used'      => $specialty_used,
			'grid_items_used'     => $grid_items_used,
		);

		// Value for the filter.
		$early_global_asset_list = $this->_early_global_asset_list;

		/**
		 * Use this filter to add additional assets to the global asset list.
		 *
		 * This filter is the replacement of Divi 4 filter `et_global_assets_list`.
		 *
		 * @since ??
		 *
		 * @param array         $early_global_asset_list global assets on the list.
		 * @param array         $assets_args             Additional assets arguments.
		 * @param DynamicAssets $this                    Instance of DynamicAssets class.
		 */
		$this->_early_global_asset_list = apply_filters(
			'divi_frontend_assets_dynamic_assets_global_assets_list',
			$early_global_asset_list,
			$assets_args,
			$this
		);

		return $this->_early_global_asset_list;
	}

	/**
	 * Gets a list of global asset files during late detection.
	 *
	 * @since ??
	 * @return array
	 */
	public function get_late_global_assets_list(): array {
		if ( ! $this->is_cachable_request() ) {
			return [];
		}

		// Get global colors based on attribute used, excluding what already included in early detections.
		if ( $this->_late_global_color_ids ) {
			$late_global_color_ids = array_diff( $this->_late_global_color_ids, $this->_early_global_color_ids );

			// Set global colors variable for the late CSS, if any.
			if ( ! empty( $late_global_color_ids ) ) {
				$this->_late_global_asset_list['et_late_global_colors'] = array(
					'css' => Style::get_global_colors_style( $late_global_color_ids ),
				);
			}
		}

		$assets_prefix = DynamicAssetsUtils::get_dynamic_assets_path();

		if ( $this->_late_custom_icon ) {
			$this->_late_global_asset_list['et_icons_all'] = array(
				'css' => "{$assets_prefix}/css/icons_all.css",
			);
		} elseif ( $this->_late_social_icon ) {
			$this->_late_global_asset_list['et_icons_social'] = array(
				'css' => "{$assets_prefix}/css/icons_base_social.css",
			);
		}

		if ( $this->_late_fa_icon ) {
			$this->_late_global_asset_list['et_icons_fa'] = array(
				'css' => "{$assets_prefix}/css/icons_fa_all.css",
			);
		}

		$gutter_length = count( $this->_late_gutter_width );
		$gutter_widths = DynamicAssetsUtils::get_unique_array_values( $this->_late_gutter_width, $this->_default_gutters );

		$grid_items_deps = array(
			'divi/filterable-portfolio',
			'divi/fullwidth-portfolio',
			'divi/portfolio',
			'divi/gallery',
			'divi/woocommerce-product-gallery',
			'divi/blog',
			'divi/sidebar',
			'divi/shop',
		);

		$grid_items_used = $this->check_for_dependency( $grid_items_deps, $this->_processed_modules );

		if ( ! empty( $gutter_widths ) ) {
			$this->_late_global_asset_list = array_merge(
				$this->_late_global_asset_list,
				$this->get_gutters_asset_list( $gutter_widths, $this->_late_use_specialty, $grid_items_used )
			);
		}

		if ( $this->_late_show_in_lightbox ) {
			$this->_late_global_asset_list['et_jquery_magnific_popup'] = array(
				'css' => "{$assets_prefix}/css/magnific_popup.css",
			);
		}

		if ( $this->_late_use_animation_style ) {
			$this->_late_global_asset_list['animations'] = array(
				'css' => "{$assets_prefix}/css/animations{$this->_cpt_suffix}.css",
			);
		}

		if ( $this->_late_use_sticky ) {
			$this->_late_global_asset_list['sticky'] = array(
				'css' => "{$assets_prefix}/css/sticky_elements{$this->_cpt_suffix}.css",
			);
		}

		// Collect and pass all needed assets arguments.
		$assets_args = array(
			'assets_prefix'       => $assets_prefix,
			'dynamic_icons'       => DynamicAssetsUtils::use_dynamic_icons(),
			'cpt_suffix'          => $this->_cpt_suffix,
			'use_all_icons'       => $this->_late_custom_icon,
			'show_in_lightbox'    => $this->_late_show_in_lightbox,
			'has_animation_style' => $this->_late_use_animation_style,
			'sticky_used'         => $this->_late_use_sticky,
			// Gutter/grid items processed info.
			'gutter_widths'       => $gutter_widths,
			'gutter_length'       => $gutter_length,
			'specialty_used'      => $this->_late_use_specialty,
			'grid_items_used'     => $grid_items_used,
		);

		// Value for the filter.
		$late_global_asset_list = $this->_late_global_asset_list;

		/**
		 * Use this filter to add additional assets to the late global asset list.
		 *
		 * This filter is the replacement of Divi 4 filter `et_late_global_assets_list`.
		 *
		 * @since ??
		 *
		 * @param array         $late_global_asset_list Current late global assets on the list.
		 * @param array         $assets_args            Additional assets arguments.
		 * @param DynamicAssets $this                   Instance of DynamicAssets class.
		 */
		$this->_late_global_asset_list = apply_filters(
			'divi_frontend_assets_dynamic_assets_late_global_assets_list',
			$late_global_asset_list,
			$assets_args,
			$this
		);

		return $this->_late_global_asset_list;
	}

	/**
	 * Generate gutters CSS file list.
	 *
	 * @since  ?? Removed `$gutter_length` parameter.
	 * @since  4.10.0
	 *
	 * @param array $gutter_widths array of gutter widths used.
	 * @param bool  $specialty     are specialty sections used.
	 * @param bool  $grid_items    are grid modules used.
	 *
	 * @return array  $assets_list of gutter assets
	 */
	public function get_gutters_asset_list( array $gutter_widths, bool $specialty = false, bool $grid_items = false ): array {
		// Check if flexbox experiment is enabled.
		$is_flexbox_enabled = et_get_experiment_flag( 'flexbox' );

		$assets_list = [];

		// If flexbox is enabled, add flex grid assets.
		if ( $is_flexbox_enabled ) {
			$assets_list = array_merge( $assets_list, $this->get_flex_grid_asset_list() );
		}

		$temp_widths      = $gutter_widths;
		$gutter_length    = count( $gutter_widths );
		$specialty_suffix = $specialty ? '_specialty' : '';
		$assets_prefix    = DynamicAssetsUtils::get_dynamic_assets_path();

		// Put default gutter `3` at beginning, otherwise it would mess up the layout.
		if ( in_array( 3, $temp_widths, true ) ) {
			$gutter_widths = array_diff( $temp_widths, array( 3 ) );
			array_unshift( $gutter_widths, 3 );
		}

		// Replace legacy gutter width values of 0 with 1.
		$gutter_widths = str_replace( 0, 1, $gutter_widths );

		for ( $i = 0; $i < $gutter_length; $i++ ) {
			$assets_list[ 'et_divi_gutters' . $gutter_widths[ $i ] ] = array(
				'css' => "{$assets_prefix}/css/gutters" . $gutter_widths[ $i ] . "{$this->_cpt_suffix}.css",
			);

			$assets_list[ 'et_divi_gutters' . $gutter_widths[ $i ] . "{$specialty_suffix}" ] = array(
				'css' => "{$assets_prefix}/css/gutters" . $gutter_widths[ $i ] . "{$specialty_suffix}{$this->_cpt_suffix}.css",
			);

			if ( $grid_items ) {
				$assets_list[ 'et_divi_gutters' . $gutter_widths[ $i ] . '_grid_items' ] = array(
					'css' => "{$assets_prefix}/css/gutters" . $gutter_widths[ $i ] . "_grid_items{$this->_cpt_suffix}.css",
				);

				$assets_list[ 'et_divi_gutters' . $gutter_widths[ $i ] . "{$specialty_suffix}_grid_items" ] = array(
					'css' => "{$assets_prefix}/css/gutters" . $gutter_widths[ $i ] . "{$specialty_suffix}_grid_items{$this->_cpt_suffix}.css",
				);
			}
		}

		return $assets_list;
	}

	/**
	 * Generate flex grid CSS file list when flexbox experiment is enabled.
	 * Note: Flex grid system doesn't use specialty sections or fullwidth modules.
	 *
	 * @since ??
	 *
	 * @return array $assets_list of flex grid assets
	 */
	public function get_flex_grid_asset_list(): array {
		$assets_prefix = DynamicAssetsUtils::get_dynamic_assets_path();
		$assets_list   = [];

		// Add flex grid CSS file.
		$assets_list['et_divi_flex_grid'] = array(
			'css' => "{$assets_prefix}/css/flex_grid{$this->_cpt_suffix}.css",
		);

		return $assets_list;
	}

	/**
	 * Gets a list of asset files and can be useful for getting all Divi module blocks.
	 *
	 * @since ??
	 *
	 * @param bool $used_modules if blocks are used.
	 *
	 * @return array
	 */
	public function get_block_assets_list( bool $used_modules = true ): array {
		$assets_prefix    = DynamicAssetsUtils::get_dynamic_assets_path();
		$specialty_suffix = '';

		$specialty_used = DetectFeature::has_specialty_section( $this->_all_content, $this->_options ) || $this->_late_use_specialty;

		if ( $specialty_used ) {
			$specialty_suffix = '_specialty';
		}

		$assets_list = DynamicAssetsUtils::get_assets_list(
			[
				'prefix'           => $assets_prefix,
				'suffix'           => $this->_cpt_suffix,
				'specialty_suffix' => $specialty_suffix,
			]
		);

		// Initial value for the apply_filters.
		$required_assets = [];
		$all_content     = $this->_all_content;

		/**
		 * This filter can be used to force loading of a certain Divi module in case their custom one relies on its styles.
		 *
		 * This filter is the replacement of Divi 4 filter `et_required_module_assets`.
		 *
		 * @since ??
		 *
		 * @param array  $required_assets Custom required module slugs.
		 * @param string $all_content     All content.
		 */
		$required_assets = apply_filters(
			'divi_frontend_assets_dynamic_assets_required_module_assets',
			$required_assets,
			$all_content
		);

		if ( $used_modules ) {
			foreach ( $assets_list as $asset => $asset_data ) {
				if (
					! in_array( $asset, $this->_processed_modules, true ) &&
					! in_array( $asset, $required_assets, true )
				) {
					unset( $assets_list[ $asset ] );
				}
			}
		}

		return $assets_list;
	}

	/**
	 * Adds global modules' content (if any) on top of post content so that
	 * that all blocks can be properly registered.
	 *
	 * @since ??
	 *
	 * @param string $content The post content.
	 *
	 * @return string
	 */
	public function maybe_add_global_modules_content( string $content ): string {
		// Ensure the $content is valid string.
		$content = is_string( $content ) ? $content : '';

		// Get a list of any global modules used in the post content.
		$found_global_modules = DetectFeature::get_global_module_ids( $content );

		// Deduplicate the new global modules with the existing global modules.
		$global_modules = DynamicAssetsUtils::get_unique_array_values( $found_global_modules, $this->_global_modules );

		// When a Global module is added, the block is also added in post content. But afterwards if the Global
		// module is changed, the respective block in post content doesn't change accordingly.
		// Here We are detecting the changes using the `global_module` attribute. We are appending the *actual*
		// Global module content at the end, and we need to put the Global module content at beginning,
		// otherwise the Dynamic Asset mechanism won't be able to detect the changes.
		if ( ! empty( $global_modules ) ) {
			foreach ( $global_modules as $global_post_id ) {
				$global_module = get_post( $global_post_id );

				if ( isset( $global_module->post_content ) ) {
					$content = $global_module->post_content . $content;
				}
			}
		}

		return $content;
	}

	/**
	 * Either load the framework early or not.
	 *
	 * @since ??
	 *
	 * @param string $content The post content.
	 *
	 * @return void
	 */
	public function maybe_load_early_framework( string $content = '' ) {
		// Bail early if the content is empty to avoid unnecessary processing.
		if ( '' === $content ) {
			$this->_has_woocommerce_module_shortcode = false;
			return;
		}

		// Check if we need to load WooCommerce framework early.
		if ( DetectFeature::has_woocommerce_module_shortcode( $content ) ) {
			$this->_has_woocommerce_module_shortcode = true;

			et_load_woocommerce_framework();
		}
	}

	/**
	 * Determines if the current context contains a WooCommerce block.
	 *
	 * @since ??
	 *
	 * @return bool True if a WooCommerce block is present, false otherwise.
	 */
	public function has_woocommerce_module_block(): bool {
		return $this->_has_woocommerce_module_block;
	}

	/**
	 * Determines if the current context contains a WooCommerce shortcode.
	 *
	 * @since ??
	 *
	 * @return bool True if a WooCommerce shortcode is present, false otherwise.
	 */
	public function has_woocommerce_module_shortcode(): bool {
		return $this->_has_woocommerce_module_shortcode;
	}

	/**
	 * Early module detection.
	 *
	 * Retrieves list of modules from the content.
	 *
	 * @since ??
	 *
	 * @param string $content The post content.
	 *
	 * @return array
	 */
	public function get_early_modules( string $content ): array {
		// Detect block and shortcode names found in the given post-content.
		// We do this early because the shortcode-framework may not be loaded yet.
		$block_names     = DetectFeature::get_block_names( $content );
		$shortcode_names = DetectFeature::get_shortcode_names( $content );

		// Check if any WooCommerce module exists in the post-content.
		if ( $block_names ) {
			$this->_has_woocommerce_module_block = DynamicAssetsUtils::contains_woocommerce_module_block( $block_names );
		}

		// Check if any WooCommerce module shortcode exists in the post-content.
		if ( $shortcode_names ) {
			$this->_has_woocommerce_module_shortcode = DynamicAssetsUtils::contains_woocommerce_module_shortcode( $shortcode_names );
		}

		// Return all detected block and shortcode names.
		return [
			'blocks'     => $block_names,
			'shortcodes' => $shortcode_names,
		];
	}

	/**
	 * Process late detection.
	 *
	 * Get blocks from the feature manager that might have been missed during early detection.
	 *
	 * @since ??
	 */
	public function get_late_blocks() {
		$this->_missed_blocks     = array_diff( $this->_blocks_used, $this->_early_blocks );
		$this->_missed_shortcodes = array_diff( $this->_shortcode_used, $this->_early_shortcodes );

		$all_blocks     = array_merge(
			$this->_missed_blocks,
			$this->_early_blocks
		);
		$all_shortcodes = array_merge(
			$this->_missed_shortcodes,
			$this->_early_shortcodes
		);

		// Update _missed_modules.
		if ( $this->_missed_shortcodes ) {
			$this->_missed_modules = array_unique(
				array_merge(
					$this->_missed_blocks,
					array_map( [ DynamicAssetsUtils::class, 'get_block_name_from_shortcode' ], $this->_missed_shortcodes )
				)
			);
		} else {
			$this->_missed_modules = $this->_missed_blocks;
		}

		if ( $this->_missed_blocks || $this->_missed_shortcodes ) {
			$this->_need_late_generation = true;

			// Cache the all blocks/all shortcodes to the meta.
			$this->metadata_set(
				'_divi_dynamic_assets_cached_modules',
				[
					'blocks'     => $all_blocks,
					'shortcodes' => $all_shortcodes,
				]
			);

			// Check if any WooCommerce module exists in the post-content.
			$this->_has_woocommerce_module_block = DynamicAssetsUtils::contains_woocommerce_module_block( $all_blocks );

			// Check if any WooCommerce module shortcode exists in the post-content.
			$this->_has_woocommerce_module_shortcode = DynamicAssetsUtils::contains_woocommerce_module_shortcode( $all_shortcodes );

			// Update block/shortcode use.
			$this->_options['has_block']     = ! empty( $all_blocks );
			$this->_options['has_shortcode'] = ! empty( $all_shortcodes );
		}

		// Update _all_modules.
		$this->_all_modules = array_unique(
			array_merge(
				$all_blocks,
				array_map( [ DynamicAssetsUtils::class, 'get_block_name_from_shortcode' ], $all_shortcodes )
			)
		);
	}

	/**
	 * Get module attributes used from the feature manager.
	 *
	 * @since ??
	 */
	public function get_late_attributes() {
		global $post;

		// Detect feature used based on block/shortcode attributes.
		self::detect_attribute_feature_use( $this->_attribute_used );

		$feature_used = [];

		if ( ! $this->_early_attributes ) {
			if ( post_password_required() ) {
				// If a password is required to view the current post, the main content block will not run,
				// therefor, DetectBlockUse->_block_feature_used would have incomplete values,
				// to fix this and any other problems caused by not running the main content block, it is run here.
				do_blocks( $post->post_content );

				// if the shortcode framework is loaded, run do_shortcode.
				if ( et_is_shortcode_framework_loaded() ) {
					do_shortcode( $post->post_content );
				}
			}

			if ( empty( $this->_presets_feaure_used ) ) {
				$this->_presets_feaure_used = $this->presets_feature_used( $this->_all_content );
			}

			$feature_used = array_replace_recursive( $this->_block_feature_used, $this->_presets_feaure_used );

			$this->metadata_set( '_divi_dynamic_assets_cached_feature_used', $feature_used );

			$this->_need_late_generation = true;
		}

		if ( $this->_early_attributes ) {
			$feature_used                = $this->_early_attributes;
			$this->_need_late_generation = true;
		}

		if ( $feature_used ) {
			foreach ( $feature_used as $attribute => $value ) {
				switch ( $attribute ) {
					case 'animation_style':
						$this->_late_use_animation_style = ! empty( $value );
						break;

					case 'excerpt_content_on':
						$this->_late_show_content = ! empty( $value );
						break;

					case 'gutter_widths':
						$this->_late_gutter_width = ! empty( $value ) ? array_map( 'intval', $value ) : [];
						break;

					case 'global_color_ids':
						$this->_late_global_color_ids = ! empty( $value ) ? $value : [];
						break;

					case 'icon_font_fa':
						$this->_late_fa_icon = ! empty( $value );
						break;

					case 'icon_font_divi':
						$this->_late_custom_icon = ! empty( $value );
						break;

					case 'lightbox':
						$this->_late_show_in_lightbox = ! empty( $value );
						break;

					case 'link_enabled':
						$this->_late_use_link = ! empty( $value );
						break;

					case 'scroll_effects_enabled':
						$this->_late_use_motion_effect = ! empty( $value );
						break;

					case 'social_follow_icon_font_fa':
						$this->_late_fa_icon = ! empty( $value );
						break;

					case 'social_follow_icon_font_divi':
						$this->_late_social_icon = ! empty( $value );
						break;

					case 'specialty_section':
						$this->_late_use_specialty = ! empty( $value );
						break;

					case 'sticky_position_enabled':
						$this->_late_use_sticky = ! empty( $value );
						break;

					default:
						break;
				}
			}
		}
	}

	/**
	 * Check if metadata exists.
	 *
	 * @since ??
	 *
	 * @param string $key Meta key to check against.
	 *
	 * @return boolean
	 */
	public function metadata_exists( string $key ): bool {
		if ( is_singular() ) {
			return metadata_exists( 'post', $this->_post_id, $key );
		}

		$metadata_manager = ET_Builder_Dynamic_Assets_Feature::instance();
		$metadata_cache   = $metadata_manager->cache_get( $key, $this->_folder_name );

		return ! empty( $metadata_cache );
	}

	/**
	 * Get saved metadata.
	 *
	 * @since ??
	 *
	 * @param string $key Meta key to get data for.
	 *
	 * @return array
	 */
	public function metadata_get( string $key ): array {
		if ( is_singular() ) {
			return metadata_exists( 'post', $this->_post_id, $key )
				? get_post_meta( $this->_post_id, $key, true )
				: [];
		}

		$metadata_manager = ET_Builder_Dynamic_Assets_Feature::instance();

		return $metadata_manager->cache_get( $key, $this->_folder_name );
	}

	/**
	 * Set metadata.
	 *
	 * @since ?
	 *
	 * @param string $key   Meta key to set data for.
	 * @param array  $value The data to be set.
	 *
	 * @return void
	 */
	public function metadata_set( string $key, array $value ) {
		if ( is_singular() ) {
			update_post_meta( $this->_post_id, $key, $value );

			return;
		}

		$metadata_manager = ET_Builder_Dynamic_Assets_Feature::instance();

		$metadata_manager->cache_set( $key, $value, $this->_folder_name );
	}

	/**
	 * Check if the current post could require assets for post formats.
	 * For example, audio scripts and css could be required on a post using
	 * the audio post format, as well as on index pages where posts with the
	 * post format may be listed.
	 *
	 * @since ??
	 *
	 * @param string $format to check for, such as "audio," "video," etc.
	 *
	 * @return bool
	 */
	public function check_post_format_dependency( string $format = 'standard' ): bool {
		// If this is a category page with posts, return true.
		// We don't know what post formats might show up in the list.
		if ( ( is_home() && ! is_front_page() ) || ! is_singular() ) {
			return true;
		}

		// If this is a single post with the builder disabled and the post format in question, return true.
		if ( is_single() && ! $this->_page_builder_used && has_post_format( $format ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the current post is a non-builder post that matches a specific post type.
	 *
	 * @since ??
	 *
	 * @param string $type to check for, such "product".
	 *
	 * @return bool
	 */
	public function check_post_type_dependency( string $type = 'post' ): bool {
		// If this is a category page with posts, return true.
		// We don't know what post formats might show up in the list.
		if ( is_singular( $type ) && ! $this->_page_builder_used ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a list of dependencies exist in the content.
	 *
	 * @since ??
	 *
	 * @param array $needles  Shortcodes to detect.
	 * @param array $haystack All blocks.
	 */
	public function check_for_dependency( array $needles = [], array $haystack = [] ): bool {
		$detected = false;

		foreach ( $needles as $needle ) {
			if ( in_array( $needle, $haystack, true ) ) {
				$detected = true;
			}
		}

		return $detected;
	}

	/**
	 * Enqueue early dynamic JavaScript files.
	 *
	 * @since 4.10.0
	 */
	public function enqueue_dynamic_scripts_early() {
		$this->enqueue_dynamic_scripts();
	}

	/**
	 * Enqueue late dynamic JavaScript files.
	 *
	 * @since 4.10.0
	 */
	public function enqueue_dynamic_scripts_late() {
		$this->enqueue_dynamic_scripts( 'late' );
	}

	/**
	 * Enqueue dynamic JavaScript files.
	 *
	 * @since ??
	 *
	 * @param string $request_type whether early or late request.
	 */
	public function enqueue_dynamic_scripts( string $request_type = 'early' ) {
		// No need to print dynamic scripts on Visual Builder's top window because no frontend is being rendered
		// nor procesed in VB's top window. Short-circuiting it to make it render as small possible element.
		if ( Conditions::is_vb_top_window() ) {
			return;
		}

		if ( ! et_builder_is_frontend_or_builder() ) {
			return;
		}

		$current_modules = 'late' === $request_type ? $this->_all_modules : $this->_early_modules;

		// Handle comments script.
		if ( ! $this->_enqueue_comments ) {
			$comments_deps = array(
				'divi/comments',
			);

			$this->_enqueue_comments = $this->check_for_dependency( $comments_deps, $current_modules );

			// If comments module is found, enqueued scripts.
			// If this is a post with the builder disabled and comments enabled, enqueue scripts.
			if ( $this->_enqueue_comments || ( ( is_single() || is_page() || is_home() ) && comments_open() && ! $this->_page_builder_used ) || DynamicAssetsUtils::disable_js_on_demand() ) {
				wp_enqueue_script( 'comment-reply' );
				DynamicAssetsUtils::enqueue_comments_script();
			}
		}

		// Handle jQuery mobile script.
		if ( ! $this->_enqueue_jquery_mobile ) {
			$jquery_mobile_deps = array(
				'divi/portfolio',
				'divi/slider',
				'divi/post-slider',
				'divi/fullwidth-slider',
				'divi/fullwidth-post-slider',
				'divi/video-slider',
				'divi/slide',
				'divi/tabs',
				'divi/woocommerce-product-tabs',
			);

			$this->_enqueue_jquery_mobile = $this->check_for_dependency( $jquery_mobile_deps, $current_modules );

			if ( $this->_enqueue_jquery_mobile || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_jquery_mobile_script();
			}
		}

		// Handle magnific popup script.
		if ( ! $this->_enqueue_magnific_popup ) {
			$magnific_popup_deps = array(
				'divi/gallery',
				'core/gallery',
				'divi/woocommerce-product-gallery',
			);

			if ( $this->check_for_dependency( $magnific_popup_deps, $this->_all_modules )
				|| $this->check_if_attribute_exits( 'lightbox', $this->_all_content )
				|| $this->_late_show_in_lightbox
			) {
				$this->_enqueue_magnific_popup = true;
			}

			if ( $this->_enqueue_magnific_popup || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_magnific_popup_script();
			}
		}

		// Handle toggle module script.
		if ( ! $this->_enqueue_toggle ) {
			$toggle_deps = array(
				'divi/toggle',
				'divi/accordion',
			);

			$this->_enqueue_toggle = $this->check_for_dependency( $toggle_deps, $current_modules );

			if ( $this->_enqueue_toggle || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_toggle_script();
			}
		}

		// Handle audio module script.
		if ( ! $this->_enqueue_audio ) {
			$audio_deps = array(
				'divi/audio',
			);

			$this->_enqueue_audio = $this->check_post_format_dependency( 'audio' ) || $this->check_for_dependency( $audio_deps, $current_modules );

			if ( $this->_enqueue_audio || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_audio_script();
			}
		}

		// Handle video overlay script.
		if ( ! $this->_enqueue_video_overlay ) {
			$video_overlay_deps = array(
				'divi/video',
				'divi/video-slider',
				'divi/blog',
			);

			$this->_enqueue_video_overlay = $this->check_post_format_dependency( 'video' ) || $this->check_for_dependency( $video_overlay_deps, $current_modules );

			if ( $this->_enqueue_video_overlay || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_video_overlay_script();
			}
		}

		// Handle search module script.
		if ( ! $this->_enqueue_search ) {
			$search_deps = array(
				'divi/search',
			);

			$this->_enqueue_search = $this->check_for_dependency( $search_deps, $current_modules );

			if ( $this->_enqueue_search || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_search_script();
			}
		}

		// Handle woo module script.
		if ( ! $this->_enqueue_woo ) {
			$woo_deps = array(
				'divi/shop',
				'divi/woocommerce-product-upsells',
				'divi/woocommerce-related-products',
				'divi/woocommerce-cart-totals',
				'divi/woocommerce-product-meta',
				'divi/woocommerce-product-rating',
				'divi/woocommerce-checkout-shipping',
				'divi/woocommerce-checkout-payment-info',
				'divi/woocommerce-checkout-billing',
				'divi/woocommerce-cart-notice',
				'divi/woocommerce-cart-products',
				'divi/woocommerce-checkout-order-details',
			);

			$this->_enqueue_woo = $this->check_for_dependency( $woo_deps, $current_modules );

			if ( $this->_enqueue_woo || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_woo_script();
			}
		}

		// Handle fullwidth_header module script.
		if ( ! $this->_enqueue_fullwidth_header ) {
			$fullwidth_header_deps = array(
				'divi/fullwidth-header',
			);

			$this->_enqueue_fullwidth_header = $this->check_for_dependency( $fullwidth_header_deps, $current_modules );

			if ( $this->_enqueue_fullwidth_header || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_fullwidth_header_script();
			}
		}

		// Handle blog script.
		if ( ! $this->_enqueue_blog ) {
			$blog_deps = array(
				'divi/blog',
			);

			$this->_enqueue_blog = $this->check_for_dependency( $blog_deps, $current_modules );

			if ( $this->_enqueue_blog || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_blog_script();
			}
		}

		// Handle pagination script.
		if ( ! $this->_enqueue_pagination ) {
			$pagination_deps = array(
				'divi/blog',
				'divi/portfolio',
				'divi/filterable-portfolio',
			);

			$this->_enqueue_pagination = $this->check_for_dependency( $pagination_deps, $current_modules );

			if ( $this->_enqueue_pagination || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_pagination_script();
			}
		}

		// Handle fullscreen section script.
		if ( ! $this->_enqueue_fullscreen_section ) {
			$this->_enqueue_fullscreen_section = DetectFeature::has_fullscreen_section_enabled( $this->_all_content, $this->_options );

			if ( $this->_enqueue_fullscreen_section || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_fullscreen_section_script();
			}
		}

		// Handle section divider script.
		if ( ! $this->_enqueue_section_dividers ) {
			$this->_enqueue_section_dividers = DetectFeature::has_section_dividers_enabled( $this->_all_content, $this->_options );

			if ( $this->_enqueue_section_dividers || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_section_dividers_script();
			}
		}

		// Handle slider module script.
		if ( ! $this->_enqueue_slider ) {
			$slider_deps = array(
				'divi/slider',
				'divi/fullwidth-slider',
				'divi/post-slider',
				'divi/fullwidth-post-slider',
				'divi/video-slider',
				'divi/gallery',
				'divi/blog',
				'core/gallery',
			);

			$this->_enqueue_slider = $this->check_post_format_dependency( 'gallery' ) || $this->check_for_dependency( $slider_deps, $current_modules );

			if ( $this->_enqueue_slider || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_slider_script();
			}
		}

		// Handle map module script.
		if ( ! $this->_enqueue_map ) {
			$map_deps = array(
				'divi/map',
				'divi/fullwidth-map',
			);

			$this->_enqueue_map = $this->check_for_dependency( $map_deps, $current_modules );

			if ( $this->_enqueue_map || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_map_script();
			}
		}

		// Handle sidebar module script.
		if ( ! $this->_enqueue_sidebar ) {
			$sidebar_deps = array(
				'divi/sidebar',
			);

			$this->_enqueue_sidebar = $this->check_for_dependency( $sidebar_deps, $current_modules );

			if ( $this->_enqueue_sidebar || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_sidebar_script();
			}
		}

		// Handle testimonial module script.
		if ( ! $this->_enqueue_testimonial ) {
			$testimonial_deps = array(
				'divi/testimonial',
			);

			$this->_enqueue_testimonial = $this->check_for_dependency( $testimonial_deps, $current_modules );

			if ( $this->_enqueue_testimonial || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_testimonial_script();
			}
		}

		// Handle tabs module script.
		if ( ! $this->_enqueue_tabs ) {
			$tabs_deps = array(
				'divi/tabs',
				'divi/woocommerce-product-tabs',
			);

			$this->_enqueue_tabs = $this->check_for_dependency( $tabs_deps, $current_modules ) || $this->check_post_type_dependency( 'product' );

			if ( $this->_enqueue_tabs || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_tabs_script();
			}
		}

		// Handle fullwidth portfolio module script.
		if ( ! $this->_enqueue_fullwidth_portfolio ) {
			$fullwidth_portfolio_deps = array(
				'divi/fullwidth-portfolio',
			);

			$this->_enqueue_fullwidth_portfolio = $this->check_for_dependency( $fullwidth_portfolio_deps, $current_modules );

			if ( $this->_enqueue_fullwidth_portfolio || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_fullwidth_portfolio_script();
			}
		}

		// Handle filterable portfolio module script.
		if ( ! $this->_enqueue_filterable_portfolio ) {
			$filterable_portfolio_deps = array(
				'divi/filterable-portfolio',
			);

			$this->_enqueue_filterable_portfolio = $this->check_for_dependency( $filterable_portfolio_deps, $current_modules );

			if ( $this->_enqueue_filterable_portfolio || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_filterable_portfolio_script();
			}
		}

		// Handle video slider module script.
		if ( ! $this->_enqueue_video_slider ) {
			$video_slider_deps = array(
				'divi/video-slider',
			);

			$this->_enqueue_video_slider = $this->check_for_dependency( $video_slider_deps, $current_modules );

			if ( $this->_enqueue_video_slider || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_video_slider_script();
			}
		}

		// Handle countdown timer module script.
		if ( ! $this->_enqueue_countdown_timer ) {
			$countdown_timer_deps = array(
				'divi/countdown-timer',
			);

			$this->_enqueue_countdown_timer = $this->check_for_dependency( $countdown_timer_deps, $current_modules );

			if ( $this->_enqueue_countdown_timer || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_countdown_timer_script();
			}
		}

		// Handle bar counter module script.
		if ( ! $this->_enqueue_bar_counter ) {
			$bar_counter_deps = array(
				'divi/bar-counter',
			);

			$this->_enqueue_bar_counter = $this->check_for_dependency( $bar_counter_deps, $current_modules );

			if ( $this->_enqueue_bar_counter || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_bar_counter_script();
			}
		}

		// Handle easy pie chart script.
		if ( ! $this->_enqueue_easypiechart ) {
			$easypiechart_deps = array(
				'divi/blog',
				'divi/circle-counter',
				'divi/number-counter',
			);

			$this->_enqueue_easypiechart = $this->check_for_dependency( $easypiechart_deps, $current_modules );

			if ( $this->_enqueue_easypiechart || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_easypiechart_script();
			}
		}

		// Handle form conditions script.
		if ( ! $this->_enqueue_form_conditions ) {
			$form_conditions_deps = array(
				'divi/signup',
				'divi/contact-form',
			);

			$this->_enqueue_form_conditions = $this->check_for_dependency( $form_conditions_deps, $current_modules );

			if ( $this->_enqueue_form_conditions || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_form_conditions_script();
			}
		}

		// Handle menu module script.
		if ( ! $this->_enqueue_menu ) {
			$menu_deps = array(
				'divi/menu',
				'divi/fullwidth-menu',
			);

			$this->_enqueue_menu = $this->check_for_dependency( $menu_deps, $current_modules );

			if ( $this->_enqueue_menu || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_menu_script();
			}
		}

		// Handle gallery module script.
		if ( ! $this->_enqueue_gallery ) {
			$gallery_deps = array(
				'divi/gallery',
				'core/gallery',
				'divi/woocommerce-product-gallery',
			);

			$this->_enqueue_gallery = $this->check_post_format_dependency( 'gallery' ) || $this->check_for_dependency( $gallery_deps, $current_modules );

			if ( $this->_enqueue_gallery || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_gallery_script();
			}
		}

		// Handle lscripts that are only needed when logged in.
		if ( ! $this->_enqueue_logged_in ) {

			$this->_enqueue_logged_in = is_user_logged_in();

			if ( $this->_enqueue_logged_in || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_logged_in_script();
			}
		}

		// Handle fitvids script.
		if ( ! $this->_enqueue_fitvids ) {
			$fitvids_deps = array(
				'divi/blog',
				'divi/slider',
				'divi/video',
				'divi/video-slider',
				'divi/slide-video',
				'divi/code',
				'divi/fullwidth-code',
				'divi/portfolio',
				'divi/filterable-portfolio',
			);

			$this->_enqueue_fitvids = $this->check_for_dependency( $fitvids_deps, $current_modules );

			if ( ( is_single() && ! $this->_page_builder_used ) || ( is_home() && ! is_front_page() ) || ! is_singular() ) {
				$this->_enqueue_fitvids = true;
			}

			if ( $this->_enqueue_fitvids
				|| DynamicAssetsUtils::disable_js_on_demand()
				|| DynamicAssetsUtils::is_media_embedded_in_content( $this->_all_content )
			) {
				DynamicAssetsUtils::enqueue_fitvids_script();
			}
		}

		// Handle salvattore script.
		if ( ! $this->_enqueue_salvattore ) {
			$salvattore_deps = array(
				'divi/blog',
				'divi/portfolio',
				'divi/fullwidth-portfolio',
				'divi/filterable-portfolio',
				'divi/gallery',
			);

			$this->_enqueue_salvattore = $this->check_for_dependency( $salvattore_deps, $current_modules );

			if ( $this->_enqueue_salvattore || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_salvattore_script();
			}
		}

		// Handle split testing script.
		if ( ! $this->_enqueue_split_testing ) {

			$this->_enqueue_split_testing = DetectFeature::has_split_testing_enabled( $this->_all_content, $this->_options );

			if ( $this->_enqueue_split_testing || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_split_testing_script();
			}
		}

		// Handle Google Maps script.
		if ( ! $this->_enqueue_google_maps ) {
			$google_maps_deps = array(
				'divi/map',
				'divi/fullwidth-map',
			);

			$this->_enqueue_google_maps = $this->check_for_dependency( $google_maps_deps, $current_modules );

			if ( ( et_pb_enqueue_google_maps_script() && $this->_enqueue_google_maps ) || ( et_pb_enqueue_google_maps_script() && DynamicAssetsUtils::disable_js_on_demand() ) ) {
				DynamicAssetsUtils::enqueue_google_maps_script();
			}
		}

		/*
		 * Other than the animation, sticky and scroll effects, the scripts such as "link", "contact-form" etc that
		 * are dependent on script-data, logically should be enqueued during the late detection phase.
		 *
		 * On the other hands, script-data must be enqueued in the late phase, because we need the modules to be
		 * rendered first which adds the script-data as required.
		 */

		/*
		 * Scripts that process script-data, must be enqueued in wp_footer so that we have all localized data, so we
		 * need to process these during late detection only.
		 *
		 * Note: for module dependency check, here the `$current_modules` is the alias for `$this->_all_modules`.
		 */
		if ( 'late' === $request_type ) {
			// Handle animation script/script-data.
			$animation_deps = array(
				'divi/circle-counter',
				'divi/number-counter',
			);

			if (
				$this->check_for_dependency( $animation_deps, $current_modules )
				|| DetectFeature::has_animation_style( $this->_all_content, $this->_options )
				|| $this->_late_use_animation_style
			) {
				$this->_enqueue_animation = true;
			}

			if ( $this->_enqueue_animation || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_animation_script();
			}

			// Handle link script/script-data.
			if ( DetectFeature::has_link_enabled( $this->_all_content, $this->_options ) || $this->_late_use_link ) {
				$this->_enqueue_link = true;
			}

			if ( $this->_enqueue_link || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_link_script();
			}

			// Handle contact form script/script-data.
			$contact_form_deps = array(
				'divi/contact-form',
			);

			if ( $this->check_for_dependency( $contact_form_deps, $current_modules ) ) {
				$this->_enqueue_contact_form = true;
			}

			if ( $this->_enqueue_contact_form || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_contact_form_script();
			}

			// Handle circle counter script/script-data.
			$circle_counter_deps = array(
				'divi/circle-counter',
			);

			if ( $this->check_for_dependency( $circle_counter_deps, $current_modules ) ) {
				$this->_enqueue_circle_counter = true;
			}

			if ( $this->_enqueue_circle_counter || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_circle_counter_script();
			}

			// Handle number counter script/script-data.
			$number_counter_deps = array(
				'divi/number-counter',
			);

			if ( $this->check_for_dependency( $number_counter_deps, $current_modules ) ) {
				$this->_enqueue_number_counter = true;
			}

			if ( $this->_enqueue_number_counter || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_number_counter_script();
			}

			// Handle signup script/script-data.
			$signup_deps = array(
				'divi/signup',
			);

			if ( $this->check_for_dependency( $signup_deps, $current_modules ) ) {
				$this->_enqueue_signup = true;
			}

			if ( $this->_enqueue_signup || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_signup_script();
			}

			// Handle motion effects script/script-data.
			if ( $this->check_if_attribute_exits( 'scroll_effects_enabled', $this->_all_content ) || $this->_late_use_motion_effect ) {
				$this->_enqueue_motion_effects = true;
			}

			if ( $this->_enqueue_motion_effects || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_scroll_script();
			}

			// Handle sticky script/script-data.
			if ( DetectFeature::has_sticky_position_enabled( $this->_all_content, $this->_options ) || $this->_late_use_sticky ) {
				$this->_enqueue_sticky = true;
			}

			if ( $this->_enqueue_sticky || DynamicAssetsUtils::disable_js_on_demand() ) {
				DynamicAssetsUtils::enqueue_sticky_script();
			}

			/*
			 * Script-data must be enqueued in the `late` phase, because we need the modules to be rendered first
			 * which adds the script-data as required.
			 */
			if ( $this->_enqueue_animation || $this->_late_use_animation_style || DynamicAssetsUtils::disable_js_on_demand() ) {
				ScriptData::enqueue_data( 'animation' );
			}

			if ( $this->_enqueue_link || $this->_late_use_link || DynamicAssetsUtils::disable_js_on_demand() ) {
				ScriptData::enqueue_data( 'link' );
			}

			if ( $this->_enqueue_contact_form || DynamicAssetsUtils::disable_js_on_demand() ) {
				ScriptData::enqueue_data( 'contact_form' );
			}

			if ( $this->_enqueue_circle_counter || DynamicAssetsUtils::disable_js_on_demand() ) {
				ScriptData::enqueue_data( 'circle_counter' );
			}

			if ( $this->_enqueue_number_counter || DynamicAssetsUtils::disable_js_on_demand() ) {
				ScriptData::enqueue_data( 'number_counter' );
			}

			if ( $this->_enqueue_signup || DynamicAssetsUtils::disable_js_on_demand() ) {
				ScriptData::enqueue_data( 'signup' );
			}

			/*
			 * Originally `et_get_combined_script_handle()` enqueues from `FrontEnd::override_d4_fe_scripts` via
			 * `wp_enqueue_scripts` hook.
			 *
			 * However, we need to dequeue and enqueue `et_get_combined_script_handle()` here because the scripts for
			 * "link", "contact form", "circle counter", "number counter" and "signup" would only work if the
			 * `et_get_combined_script_handle()` loads after these scripts been loaded.
			 *
			 * At the moment, feature use could be detected late in 404 page where there are no "content" found, so
			 * the detection happens based on rendered module attribute (e.g based on TB templates on 404 page).
			 */
			if (
				$this->_enqueue_link ||
				$this->_enqueue_contact_form ||
				$this->_enqueue_circle_counter ||
				$this->_enqueue_number_counter ||
				$this->_enqueue_signup ||
				DynamicAssetsUtils::disable_js_on_demand()
			) {
				FrontEnd::override_d4_fe_scripts();
			}
		}
	}

	/**
	 * Get feature detection data from presets used within the $content.
	 *
	 * @sine ??
	 *
	 * @param string $content Content to look for preset ids in.
	 *
	 * @return array
	 */
	public function presets_feature_used( string $content ): array {
		static $cached = [];

		// Create a unique key for/using the given arguments.
		$key = md5( $content );

		// Return cached value if available.
		if ( isset( $cached[ $key ] ) ) {
			return $cached[ $key ];
		}

		$preset_content = '';

		if ( $this->_options['has_block'] ) {
			// Get both types of presets.
			$module_preset_ids = DetectFeature::get_block_preset_ids( $content );
			$group_preset_ids  = DetectFeature::get_group_preset_ids( $content );

			// Get attributes from both preset types.
			$module_attrs = DynamicAssetsUtils::get_module_preset_attributes( $module_preset_ids );
			$group_attrs  = DynamicAssetsUtils::get_group_preset_attributes( $group_preset_ids );

			$combined_attrs = array_merge( $module_attrs, $group_attrs );

			if ( ! empty( $combined_attrs ) ) {
				$preset_content = wp_json_encode( $combined_attrs );
			}
		}

		if ( $this->_options['has_shortcode'] ) {
			$shortcode_preset_ids         = DetectFeature::get_shortcode_preset_ids( $content );
			$shortcode_presets_attributes = DynamicAssetsUtils::get_shortcode_preset_attributes( $shortcode_preset_ids );

			if ( ! empty( $shortcode_presets_attributes ) ) {
				foreach ( $shortcode_presets_attributes as $attribute_group ) {
					// Add `[` at the start as a proxy to shortcode format.
					$shortcode_preset_content = ' [';

					foreach ( $attribute_group as $name => $value ) {
						$shortcode_preset_content = $shortcode_preset_content . ' ' . $name . '="' . $value . '" ';
					}

					// Add `]` at the end as a proxy to shortcode format.
					$preset_content = $preset_content . $shortcode_preset_content . ' ]';
				}
			}
		}

		// Cache the results.
		$cached[ $key ] = self::detect_preset_feature_use( $preset_content );

		return $cached[ $key ];
	}

	/**
	 * Detect feature use in the content.
	 *
	 * @since ??
	 *
	 * @param string $preset_content Block Attribute string / Shortcode string.
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException In case the callback is not a callable function.
	 */
	public function detect_preset_feature_use( string $preset_content ): array {
		// Detect feature use in the preset content.
		$feature_detection_map = DynamicAssetsUtils::get_feature_detection_map( $this->_options );
		$preset_feature_used   = [];

		foreach ( $feature_detection_map as $name => $params ) {
			if ( ! isset( $preset_feature_used[ $name ] ) ) {
				$preset_feature_used[ $name ] = [];
			}

			// Get feature detection data from the provided functions.
			if ( is_callable( $params['callback'] ) ) {
				$result = call_user_func_array(
					$params['callback'],
					array_merge(
						[ 'content' => $preset_content ],
						$params['additional_args']
					)
				);

				if ( is_bool( $result ) ) {
					// Combine all the results, and remove false value.
					$preset_feature_used[ $name ][] = $result;
					$preset_feature_used[ $name ]   = array_unique(
						array_filter(
							$preset_feature_used[ $name ]
						)
					);
				} else {
					// Combine all the results, and keep unique values.
					$preset_feature_used[ $name ] = array_unique(
						array_merge(
							$preset_feature_used[ $name ],
							$result
						)
					);
				}
			} else {
				throw new InvalidArgumentException( 'The argument must be a callable function' );
			}
		}

		return $preset_feature_used;
	}

	/**
	 * Check for available attributes.
	 *
	 * @since ??
	 *
	 * @param string $feature Feature to check.
	 * @param string $content to search for feature in.
	 *
	 * @throws InvalidArgumentException In case the callback is not a callable function.
	 */
	public function check_if_attribute_exits( string $feature, string $content ): bool {
		$feature_detection_map = DynamicAssetsUtils::get_feature_detection_map( $this->_options );

		$has_attribute = false;

		if ( isset( $feature_detection_map[ $feature ] ) ) {
			$params = $feature_detection_map[ $feature ];

			// Get feature detection data from the provided functions.
			if ( is_callable( $params['callback'] ) ) {
				$has_attribute = call_user_func_array(
					$params['callback'],
					array_merge(
						[ 'content' => $content ],
						$params['additional_args']
					)
				);
			} else {
				throw new InvalidArgumentException( 'The argument must be a callable function' );
			}
		}

		if ( ! empty( $this->_presets_feaure_used ) ) {
			$preset_feaure_used = isset( $this->_presets_feaure_used[ $feature ] ) && ! empty( $this->_presets_feaure_used[ $feature ] );

			return $has_attribute && $preset_feaure_used;
		}

		return $has_attribute;
	}

	/**
	 * Check class exists in post content.
	 *
	 * @since ??
	 *
	 * @param string $class   class to check.
	 * @param string $content to search for class in.
	 */
	public function check_if_class_exits( string $class, string $content ) {
		// Ensure the $content is valid string.
		$content = is_string( $content ) ? $content : '';

		return preg_match( '/class=".*' . preg_quote( $class, '/' ) . '/', $content );
	}

	/**
	 * Get custom global asset list.
	 *
	 * @since ??
	 *
	 * @param string $content post content.
	 *
	 * @return array
	 */
	public function get_custom_global_assets_list( string $content ): array {
		// Save the current values of some properties.
		$all_content = $this->_all_content;
		$all_modules = $this->_all_modules;

		if ( '' === $content ) {
			$this->_all_modules = [];
		}

		// Since `get_global_assets_list` has no parameters, the only way to run it on custom content
		// is to change `_all_content` and `_all_modules`. The current values were previosly saved.
		// and will be restored right after the method call.
		$this->_all_content = $content;
		$list               = $this->get_global_assets_list();
		$this->_all_content = $all_content;
		$this->_all_modules = $all_modules;

		return $list;
	}

	/**
	 * Get global assets data.
	 *
	 * @since ??
	 *
	 * @param object $split_content      Above the fold and Bellow the fold content.
	 * @param array  $global_assets_list List of global assets.
	 *
	 * @return array
	 */
	public function split_global_assets_data( object $split_content, array $global_assets_list ): array {
		// Value for the filter.
		$include = false;

		/**
		 * Filters whether Required Assets should be considered Above The Fold.
		 *
		 * This filter is the replacement of Divi 4 filter `et_dynamic_assets_atf_includes_required`.
		 *
		 * @since ??
		 *
		 * @param bool $include Whether to consider Required Assets Above The Fold or not.
		 */
		$atf_includes_required = apply_filters( 'divi_frontend_assets_dynamic_assets_atf_includes_required', $include );

		$required    = $atf_includes_required ? [] : array_keys( $this->get_custom_global_assets_list( '' ) );
		$content_atf = ! empty( $split_content->atf ) ? $split_content->atf : '';
		$atf         = $this->get_custom_global_assets_list( $content_atf );
		$assets      = $global_assets_list;
		$has_btf     = ! empty( $split_content->btf );

		global $post;

		$post_id = (int) ! empty( $post ) ? $post->ID : 0;

		if ( $post_id > 0 ) {
			// Value for the filter.
			$img_attrs = [];

			/**
			 * Filters omit image attributes.
			 *
			 * This filter is the replacement of Divi 4 filter `et_dynamic_assets_atf_omit_image_attributes`.
			 *
			 * @since ??
			 *
			 * @param array $img_attrs Image attributes.
			 */
			$additional_img_attrs = apply_filters( 'divi_frontend_assets_dynamic_assets_atf_omit_image_attributes', $img_attrs );
			$default_img_attrs    = array(
				'src',
				'image_url',
				'image',
				'logo_image_url',
				'header_image_url',
				'logo',
				'portrait_url',
				'image_src',
			);

			if ( ! is_array( $additional_img_attrs ) ) {
				$additional_img_attrs = [];
			}

			$sanitized_additional_img_attrs = [];
			foreach ( $additional_img_attrs as $attr ) {
				$sanitized_additional_img_attrs[] = sanitize_text_field( $attr );
			}

			$img_attrs   = array_merge( $default_img_attrs, $sanitized_additional_img_attrs );
			$img_pattern = '';

			foreach ( $img_attrs as $img_attr ) {
				$or_conj      = ! empty( $img_pattern ) ? '|' : '';
				$img_pattern .= "{$or_conj}({$img_attr}=)";
			}

			$result = preg_match_all( '/' . $img_pattern . '/', $content_atf, $matches );

			$matched_attrs = $result ? count( $matches[0] ) : 0;
			$skip_images   = max( $matched_attrs, 0 );

			if ( $skip_images > 1 ) {
				update_post_meta(
					$post_id,
					'_et_builder_dynamic_assets_loading_attr_threshold',
					$skip_images
				);
			}
		}

		$atf = array_keys( $atf );
		$all = array_keys( $global_assets_list );

		$icon_set   = false;
		$icons_sets = array(
			'et_icons_base',
			'et_icons_social',
			'et_icons_all',
		);

		foreach ( $icons_sets as $set ) {
			if ( in_array( $set, $all, true ) ) {
				$icon_set = $set;
				break;
			}
		}

		if ( false !== $icon_set ) {
			$replace = function ( $value ) use ( $icon_set, $icons_sets ) {
				return in_array( $value, $icons_sets, true ) ? $icon_set : $value;
			};
			$atf     = array_values( array_unique( array_map( $replace, $atf ) ) );
			if ( ! empty( $required ) ) {
				$required = array_values( array_unique( array_map( $replace, $required ) ) );
			}
		}

		if ( empty( $required ) ) {
			$atf = array_flip( $atf );
		} else {
			$atf = array_flip( array_diff( $atf, $required ) );
		}

		$atf_assets = [];
		$btf_assets = [];

		foreach ( $assets as $key => $asset ) {
			$has_css     = isset( $asset['css'] );
			$is_required = isset( $required[ $key ] );
			$is_atf      = isset( $atf[ $key ] );
			$is_atf      = $is_atf || ( $atf_includes_required && $is_required );
			$force_defer = $has_btf && isset( $asset['maybe_defer'] );

			// In order for a (global) asset to be considered Above The Fold:
			// 1.0 It needs to include a CSS section (some of the assets are JS only).
			// 2.0 It needs to be used in the ATF Content.
			// 2.1 Or is a required asset (as in always used, doesn't depends on content) and
			// required assets are considered ATF (configurable behaviour via WP filter)
			// 3.0 It needs not be marked as `maybe_defer`, which are basically required assets
			// that will be deferred if the page has Below The Fold Content.
			if ( $has_css && $is_atf && ! $force_defer ) {
				$atf_assets[ $key ]['css'] = $asset['css'];
				unset( $asset['css'] );
			}

			// Some assets are CSS only (no JS), hence if they considered ATF by the previous code
			// there will be nothing else to do for them when processing BTF Content.
			if ( ! empty( $asset ) ) {
				$btf_assets[ $key ] = $asset;
			}
		}

		return array(
			'atf' => $atf_assets,
			'btf' => $btf_assets,
		);
	}

	/**
	 * Gets a list of global asset files.
	 *
	 * @since ??
	 *
	 * @param array $global_list List of globally needed assets.
	 *
	 * @return array
	 */
	public function divi_get_global_assets_list( array $global_list ): array {
		$post_id                = get_the_ID();
		$assets_list            = [];
		$assets_prefix          = get_template_directory() . '/css/dynamic-assets';
		$js_assets_prefix       = get_template_directory() . '/js/src/dynamic-assets';
		$shared_assets_prefix   = get_template_directory() . '/includes/builder/feature/dynamic-assets/assets';
		$is_page_builder_used   = et_pb_is_pagebuilder_used( $post_id );
		$side_nav               = get_post_meta( $post_id, '_et_pb_side_nav', true );
		$has_tb_header          = false;
		$has_tb_body            = false;
		$has_tb_footer          = false;
		$layouts                = et_theme_builder_get_template_layouts();
		$is_blank_page_tpl      = is_page_template( 'page-template-blank.php' );
		$vertical_nav           = et_get_option( 'vertical_nav', false );
		$header_style           = et_get_option( 'header_style', 'left' );
		$et_slide_header        = in_array( $header_style, [ 'slide', 'fullscreen' ], true );
		$color_scheme           = et_get_option( 'color_schemes', 'none' );
		$page_custom_gutter     = get_post_meta( $post_id, '_et_pb_gutter_width', true );
		$customizer_gutter      = et_get_option( 'gutter_width', '3' );
		$gutter_width           = ! empty( $page_custom_gutter ) ? $page_custom_gutter : $customizer_gutter;
		$back_to_top            = et_get_option( 'divi_back_to_top', 'false' );
		$et_secondary_nav_items = et_divi_get_top_nav_items();
		$et_top_info_defined    = $et_secondary_nav_items->top_info_defined;
		$button_icon            = et_get_option( 'all_buttons_selected_icon', '5' );
		$page_layout            = get_post_meta( $post_id, '_et_pb_page_layout', true );

		if ( ! empty( $layouts ) ) {
			if ( $layouts[ ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE ]['override'] ) {
				$has_tb_header = true;
			}
			if ( $layouts[ ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE ]['override'] ) {
				$has_tb_body = true;
			}
			if ( $layouts[ ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE ]['override'] ) {
				$has_tb_footer = true;
			}
		}

		if ( '5' !== $button_icon ) {
			$assets_list['et_icons'] = array(
				'css' => "{$shared_assets_prefix}/css/icons_all.css",
			);
		}

		if ( ! $has_tb_header && ! $is_blank_page_tpl ) {
			$assets_list['et_divi_header'] = array(
				'css' => array(
					"{$assets_prefix}/header.css",
					"{$shared_assets_prefix}/css/header_animations.css",
					"{$shared_assets_prefix}/css/header_shared.css",
				),
			);

			if ( et_divi_is_transparent_primary_nav() ) {
				$assets_list['et_divi_transparent_nav'] = array(
					'css' => "{$assets_prefix}/transparent_nav.css",
				);
			}

			if ( $et_top_info_defined && ! $et_slide_header ) {
				$assets_list['et_divi_secondary_nav'] = array(
					'css' => "{$assets_prefix}/secondary_nav.css",
				);
			}

			switch ( $header_style ) {
				case 'slide':
					$assets_list['et_divi_header_slide_in'] = array(
						'css' => "{$assets_prefix}/slide_in_menu.css",
					);
					break;

				case 'fullscreen':
					$assets_list['et_divi_header_fullscreen'] = array(
						'css' => array(
							"{$assets_prefix}/slide_in_menu.css",
							"{$assets_prefix}/fullscreen_header.css",
						),
					);
					break;

				case 'centered':
					$assets_list['et_divi_header_centered'] = array(
						'css' => "{$assets_prefix}/centered_header.css",
					);
					break;

				case 'split':
					$assets_list['et_divi_header_split'] = array(
						'css' => array(
							"{$assets_prefix}/centered_header.css",
							"{$assets_prefix}/split_header.css",
						),
					);
					break;

				default:
					break;
			}

			if ( $vertical_nav ) {
				$assets_list['et_divi_vertical_nav'] = array(
					'css' => "{$assets_prefix}/vertical_nav.css",
				);
			}
		}

		if ( ! $has_tb_footer && ! $is_blank_page_tpl ) {
			$assets_list['et_divi_footer'] = array(
				'css' => "{$assets_prefix}/footer.css",
			);

			$assets_list['et_divi_gutters_footer'] = array(
				'css' => "{$assets_prefix}/gutters{$gutter_width}_footer.css",
			);
		}

		if ( ( ! $has_tb_header || ! $has_tb_footer ) && ! $is_blank_page_tpl ) {
			$assets_list['et_divi_social_icons'] = array(
				'css' => "{$assets_prefix}/social_icons.css",
			);
		}

		if ( et_divi_is_boxed_layout() ) {
			$assets_list['et_divi_boxed_layout'] = array(
				'css' => "{$assets_prefix}/boxed_layout.css",
			);
		}

		if ( is_singular( 'project' ) ) {
			$assets_list['et_divi_project'] = array(
				'css' => "{$assets_prefix}/project.css",
			);
		}

		if ( $is_page_builder_used && is_single() ) {
			$assets_list['et_divi_pagebuilder_posts'] = array(
				'css' => "{$assets_prefix}/pagebuilder_posts.css",
			);
		}

		if ( // Sidebar exists on the homepage blog feed.
			( is_home() )
			// Sidebar exists on all non-singular pages, such as categories, except when using a theme builder template.
			|| ( ! is_singular() && ! $has_tb_body )
			// Sidebar exists on posts, except when using a theme builder body template or a page template that doesn't include a sidebar.
			|| ( is_single() && ! $has_tb_body && ! in_array( $page_layout, array( 'et_full_width_page', 'et_no_sidebar' ), true ) )
			// Sidebar is used on pages when the builder is disabled.
			|| ( ( is_page() || is_front_page() ) && ! $has_tb_body && ! $is_page_builder_used && ! in_array( $page_layout, array( 'et_full_width_page', 'et_no_sidebar' ), true ) )
		) {
			$assets_list['et_divi_sidebar'] = array(
				'css' => "{$assets_prefix}/sidebar.css",
			);
		}

		if ( ( is_single() || is_page() || is_home() ) && comments_open( $post_id ) ) {
			$assets_list['et_divi_comments'] = array(
				'css' => array(
					"{$assets_prefix}/comments.css",
					"{$shared_assets_prefix}/css/comments_shared.css",
				),
			);
		}

		if ( DynamicAssetsUtils::has_builder_widgets() ) {
			$assets_list['et_divi_widgets_shared'] = array(
				'css' => "{$shared_assets_prefix}/css/widgets_shared.css",
			);
		}

		if (
			is_active_widget( false, false, 'calendar' ) || DynamicAssetsUtils::is_active_block_widget( 'core/calendar' )
		) {
			$assets_list['et_divi_widget_calendar'] = array(
				'css' => "{$assets_prefix}/widget_calendar.css",
			);
		}

		if (
			is_active_widget( false, false, 'search' ) || DynamicAssetsUtils::is_active_block_widget( 'core/search' )
		) {
			$assets_list['et_divi_widget_search'] = array(
				'css' => "{$assets_prefix}/widget_search.css",
			);
		}

		if (
			is_active_widget( false, false, 'tag_cloud' ) || DynamicAssetsUtils::is_active_block_widget( 'core/tag-cloud' )
		) {
			$assets_list['et_divi_widget_tag_cloud'] = array(
				'css' => "{$assets_prefix}/widget_tag_cloud.css",
			);
		}

		if (
			is_active_widget( false, false, 'media_gallery' ) || DynamicAssetsUtils::is_active_block_widget( 'core/gallery' )
		) {
			$assets_list['et_divi_widget_gallery'] = array(
				'css' => array(
					"{$shared_assets_prefix}/css/wp_gallery.css",
					"{$shared_assets_prefix}/css/magnific_popup.css",
				),
			);
		}

		if ( is_active_widget( false, false, 'aboutmewidget' ) ) {
			$assets_list['et_divi_widget_about'] = array(
				'css' => "{$assets_prefix}/widget_about.css",
			);
		}

		if ( ( is_singular() || is_home() || is_front_page() ) && 'on' === $side_nav && $is_page_builder_used ) {
			$assets_list['et_divi_side_nav'] = array(
				'css' => "{$assets_prefix}/side_nav.css",
			);
		}

		if ( 'on' === $back_to_top ) {
			$assets_list['et_divi_back_to_top'] = array(
				'css' => "{$assets_prefix}/back_to_top.css",
			);
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$assets_list['et_divi_woocommerce'] = array(
				'css' => array(
					"{$assets_prefix}/woocommerce.css",
					"{$shared_assets_prefix}/css/woocommerce_shared.css",
				),
			);
		}

		if ( ! is_customize_preview() && 'none' !== $color_scheme ) {
			$assets_list['et_color_scheme'] = array(
				'css' => "{$assets_prefix}/color_scheme_{$color_scheme}.css",
			);
		}

		if ( is_rtl() ) {
			$assets_list['et_divi_rtl'] = array(
				'css' => "{$assets_prefix}/rtl.css",
			);
		}

		return array_merge( $global_list, $assets_list );
	}

	/**
	 * Detect feature use in the content.
	 *
	 * @param string $content Block Attribute string / Shortcode string.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException In case the callback is not a callable function.
	 */
	public function detect_attribute_feature_use( string $content ) {
		if ( empty( $content ) ) {
			return;
		}

		$feature_detection_map = DynamicAssetsUtils::get_feature_detection_map( $this->_options );

		foreach ( $feature_detection_map as $name => $params ) {
			if ( ! isset( $this->_block_feature_used[ $name ] ) ) {
				$this->_block_feature_used[ $name ] = [];
			}

			$feature_used = $this->_block_feature_used[ $name ];
			if ( true !== array_shift( $feature_used ) ) {
				// Get feature detection data from the provided functions.
				if ( is_callable( $params['callback'] ) ) {
					$result = call_user_func_array(
						$params['callback'],
						array_merge(
							[ 'content' => $content ],
							$params['additional_args']
						)
					);

					if ( is_bool( $result ) ) {
						// Combine all the results, and remove false value.
						$this->_block_feature_used[ $name ][] = $result;
						$this->_block_feature_used[ $name ]   = array_unique(
							array_filter(
								$this->_block_feature_used[ $name ]
							)
						);
					} else {
						// Combine all the results, and keep unique values.
						$this->_block_feature_used[ $name ] = array_unique(
							array_merge(
								$this->_block_feature_used[ $name ],
								$result
							)
						);
					}
				} else {
					throw new InvalidArgumentException( 'The argument must be a callable function' );
				}
			}
		}
	}

	/**
	 * Log the Block name.
	 *
	 * @since ??
	 *
	 * @param mixed $parsed_block Parsed Block.
	 *
	 * @return mixed
	 */
	public function log_block_used( $parsed_block ) {
		// If no `parentId` is found, this block isn't Divi 5 module thus it can be skipped.
		if ( empty( $parsed_block['parentId'] ) ) {
			return $parsed_block;
		}

		// Module name.
		$block_name = $parsed_block['blockName'];

		// Log the block tags used.
		if ( in_array( $block_name, $this->_verified_blocks, true ) ) {

			// Log the block used.
			if ( ! in_array( $block_name, $this->_blocks_used, true ) ) {
				$this->_blocks_used[]        = $block_name;
				$this->_options['has_block'] = true;
			}

			// Set block attributes for late detection.
			$this->_attribute_used = $this->_attribute_used . wp_json_encode( $parsed_block['attrs'] ?? [] );
		}

		return $parsed_block;
	}

	/**
	 * Log the Shortcode Tag/Slug.
	 *
	 * @since  ??
	 * @access public
	 *
	 * @param mixed  $override Whether to override do_shortcode return value or not.
	 * @param string $tag      Shortcode tag.
	 * @param array  $attrs    Shortcode attrs.
	 * @param array  $m        Shortcode match array.
	 *
	 * @return mixed
	 */
	public function log_shortcode_used( $override, string $tag, array $attrs, array $m ) {
		if ( in_array( $tag, $this->_verified_shortcodes, true ) ) {
			// Log the shortcode tags used.
			if ( ! in_array( $tag, $this->_shortcode_used, true ) ) {
				$this->_shortcode_used[]         = $tag;
				$this->_options['has_shortcode'] = true;
			}

			// Check for shortcode attribute that we're interested in.
			$found_interested_attribute = array_intersect( array_keys( $attrs ), $this->_interested_attrs );

			if ( $found_interested_attribute ) {
				// Set `$m[0]` for late detection, which is the shortcode string.
				$this->_attribute_used = $this->_attribute_used . $m[0];
			}
		}

		return $override;
	}

	/**
	 * Add footer actions.
	 *
	 * @since ??
	 */
	public function wp_footer() {
		// Value for the filter.
		$used_blocks     = $this->_blocks_used;
		$used_shortcodes = $this->_shortcode_used;
		$used_modules    = $this->_all_modules;

		/**
		 * Fires after wp_footer hook and contains unique array of names of the modules that were used on the page.
		 *
		 * This action is the replacement of Divi 4 action `et_builder_modules_used`.
		 *
		 * @since ??
		 *
		 * @param array $used_modules    Module name used on the page load.
		 * @param array $used_blocks     Block name used on the page load.
		 * @param array $used_shortcodes Shortcode name used on the page load.
		 */
		do_action(
			'divi_frontend_assets_dynamic_assets_modules_used',
			$used_modules,
			$used_blocks,
			$used_shortcodes
		);
	}
}
