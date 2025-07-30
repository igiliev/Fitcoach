<?php

/**
 * Module Library: Modules class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\DependencyTree;

// Modules.
use ET\Builder\Framework\Utility\Conditions as ConditionsUtility;
use ET\Builder\Packages\Module\Options\Conditions;
use ET\Builder\Packages\ModuleLibrary\AccordionItem\AccordionItemModule;
use ET\Builder\Packages\ModuleLibrary\Accordion\AccordionModule;
use ET\Builder\Packages\ModuleLibrary\Audio\AudioModule;
use ET\Builder\Packages\ModuleLibrary\BarCountersItem\BarCountersItemModule;
use ET\Builder\Packages\ModuleLibrary\BarCounters\BarCountersModule;
use ET\Builder\Packages\ModuleLibrary\Blog\BlogModule;
use ET\Builder\Packages\ModuleLibrary\Blurb\BlurbModule;
use ET\Builder\Packages\ModuleLibrary\Button\ButtonModule;
use ET\Builder\Packages\ModuleLibrary\CTA\CTAModule;
use ET\Builder\Packages\ModuleLibrary\CircleCounter\CircleCounterModule;
use ET\Builder\Packages\ModuleLibrary\Code\CodeModule;
use ET\Builder\Packages\ModuleLibrary\ColumnInner\ColumnInnerModule;
use ET\Builder\Packages\ModuleLibrary\Column\ColumnModule;
use ET\Builder\Packages\ModuleLibrary\Comments\CommentsModule;
use ET\Builder\Packages\ModuleLibrary\ContactField\ContactFieldModule;
use ET\Builder\Packages\ModuleLibrary\ContactForm\ContactFormModule;
use ET\Builder\Packages\ModuleLibrary\CountdownTimer\CountdownTimerModule;
use ET\Builder\Packages\ModuleLibrary\Divider\DividerModule;
use ET\Builder\Packages\ModuleLibrary\FilterablePortfolio\FilterablePortfolioModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthCode\FullwidthCodeModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthHeader\FullwidthHeaderModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthImage\FullwidthImageModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthMap\FullwidthMapModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthMenu\FullwidthMenuModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthPortfolio\FullwidthPortfolioModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthPostContent\FullwidthPostContentModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthPostSlider\FullwidthPostSliderModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthPostTitle\FullwidthPostTitleModule;
use ET\Builder\Packages\ModuleLibrary\FullwidthSlider\FullwidthSliderModule;
use ET\Builder\Packages\ModuleLibrary\Gallery\GalleryModule;
use ET\Builder\Packages\ModuleLibrary\Heading\HeadingModule;
use ET\Builder\Packages\ModuleLibrary\Icon\IconModule;
use ET\Builder\Packages\ModuleLibrary\Image\ImageModule;
use ET\Builder\Packages\ModuleLibrary\Login\LoginModule;
use ET\Builder\Packages\ModuleLibrary\MapItem\MapItemModule;
use ET\Builder\Packages\ModuleLibrary\Map\MapModule;
use ET\Builder\Packages\ModuleLibrary\Menu\MenuModule;
use ET\Builder\Packages\ModuleLibrary\Group\GroupModule;
use ET\Builder\Packages\ModuleLibrary\NumberCounter\NumberCounterModule;
use ET\Builder\Packages\ModuleLibrary\Portfolio\PortfolioModule;
use ET\Builder\Packages\ModuleLibrary\PostContent\PostContentModule;
use ET\Builder\Packages\ModuleLibrary\PostNavigation\PostNavigationModule;
use ET\Builder\Packages\ModuleLibrary\PostSlider\PostSliderModule;
use ET\Builder\Packages\ModuleLibrary\PostTitle\PostTitleModule;
use ET\Builder\Packages\ModuleLibrary\PricingTablesItem\PricingTablesItemModule;
use ET\Builder\Packages\ModuleLibrary\PricingTables\PricingTablesModule;
use ET\Builder\Packages\ModuleLibrary\RowInner\RowInnerModule;
use ET\Builder\Packages\ModuleLibrary\Row\RowModule;
use ET\Builder\Packages\ModuleLibrary\Search\SearchModule;
use ET\Builder\Packages\ModuleLibrary\Section\SectionModule;
use ET\Builder\Packages\ModuleLibrary\Sidebar\SidebarModule;
use ET\Builder\Packages\ModuleLibrary\SignupCustomField\SignupCustomFieldModule;
use ET\Builder\Packages\ModuleLibrary\Signup\SignupModule;
use ET\Builder\Packages\ModuleLibrary\Slide\SlideModule;
use ET\Builder\Packages\ModuleLibrary\Slider\SliderModule;
use ET\Builder\Packages\ModuleLibrary\SocialMediaFollowItem\SocialMediaFollowItemModule;
use ET\Builder\Packages\ModuleLibrary\SocialMediaFollow\SocialMediaFollowModule;
use ET\Builder\Packages\ModuleLibrary\Tab\TabModule;
use ET\Builder\Packages\ModuleLibrary\Tabs\TabsModule;
use ET\Builder\Packages\ModuleLibrary\TeamMember\TeamMemberModule;
use ET\Builder\Packages\ModuleLibrary\Testimonial\TestimonialModule;
use ET\Builder\Packages\ModuleLibrary\Text\TextModule;
use ET\Builder\Packages\ModuleLibrary\Toggle\ToggleModule;
use ET\Builder\Packages\ModuleLibrary\VideoSliderItem\VideoSliderItemModule;
use ET\Builder\Packages\ModuleLibrary\VideoSlider\VideoSliderModule;
use ET\Builder\Packages\ModuleLibrary\Video\VideoModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\Breadcrumb\WooCommerceBreadcrumbModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\CartNotice\WooCommerceCartNoticeModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductAddToCart\WooCommerceProductAddToCartModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductAdditionalInfo\WooCommerceProductAdditionalInfoModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductDescription\WooCommerceProductDescriptionModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductGallery\WooCommerceProductGalleryModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductImages\WooCommerceProductImagesModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductMeta\WooCommerceProductMetaModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductPrice\WooCommerceProductPriceModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductRating\WooCommerceProductRatingModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductReviews\WooCommerceProductReviewsModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductStock\WooCommerceProductStockModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductTabs\WooCommerceProductTabsModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductTitle\WooCommerceProductTitleModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductUpsell\WooCommerceProductUpsellModule;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\RelatedProducts\WooCommerceRelatedProductsModule;

/**
 * Modules class.
 *
 * This class registers all modules on the backend upon calling `load()`. This is specifically curated toward Dynamic Modules.
 *
 * It accepts a `DependencyTree` on construction which tells `Modules` its dependencies and the priorities to
 * load them.
 *
 * @since ??
 */
class Modules {

	/**
	 * Whether the GB blocks are de-registered.
	 *
	 * @var bool
	 *
	 * @since ??
	 */
	private static $_gb_block_de_registered = false;

	/**
	 * Whether the GB blocks are re-registered.
	 *
	 * @var bool
	 *
	 * @since ??
	 */
	private static $_gb_block_re_registered = false;

	/**
	 * Stores dependencies that was passed to constructor.
	 *
	 * @var DependencyTree
	 *
	 * @since ??
	 */
	private $_dependency_tree;

	/**
	 * Create an instance of Modules class.
	 *
	 * Create an instance of the class and sets dependencies for `VisualBuilder` to load.
	 *
	 * @param DependencyTree $dependency_tree Dependency tree for VisualBuilder to load.
	 *
	 * @since ??
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}

	/**
	 * Initialize the module.
	 *
	 * Initializes the module by loading dependencies, registering custom block parser,
	 * registering conditions renderer, and enqueueing shared module scripts.
	 *
	 * Note: this function only executes when `et_builder_d5_enabled()` is `true`.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->_dependency_tree->load_dependencies();
		$this->_register_custom_block_parser();
		$this->_de_register_gb_blocks();
		$this->_register_option_group_preset_resolver_filters();

		Conditions\ConditionsRenderer::register();
		Conditions\ConditionsHooks::register();
	}

	/**
	 * Registers option group preset resolver filters for specific modules.
	 *
	 * This is done here because `Module::class` is conditionally loaded based on
	 * whether the content includes a particular module. However, global presets
	 * must be registered regardless of the module's presence in the content,
	 * since a preset created by Module A may be used by Module B.
	 *
	 * Without this, a preset created by Module A would only work if Module A
	 * is also present in the content, which defeats the purpose of global presets.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	private function _register_option_group_preset_resolver_filters(): void {
		add_filter( 'divi_option_group_preset_resolver_attr_name', [ BlogModule::class, 'option_group_preset_resolver_attr_name' ], 10, 2 );
		add_filter( 'divi_option_group_preset_resolver_attr_name', [ FullwidthHeaderModule::class, 'option_group_preset_resolver_attr_name' ], 10, 2 );
		add_filter( 'divi_option_group_preset_resolver_attr_name', [ FullwidthSliderModule::class, 'option_group_preset_resolver_attr_name' ], 10, 2 );
		add_filter( 'divi_option_group_preset_resolver_attr_name', [ ImageModule::class, 'option_group_preset_resolver_attr_name' ], 10, 2 );
		add_filter( 'divi_option_group_preset_resolver_attr_name', [ SliderModule::class, 'option_group_preset_resolver_attr_name' ], 10, 2 );
	}

	/**
	 * Get the core GB blocks.
	 *
	 * This method returns an array of core GB blocks.
	 *
	 * See wp-includes/blocks/require-dynamic-blocks.php, for
	 * the list of blocks that are registered, that
	 * we are populated here, that are used for de-registering
	 * and re-registering the blocks, if needed.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	protected static function _get_gb_blocks() {
		$gb_blocks = [
			'archives',
			'avatar',
			'block',
			'calendar',
			'categories',
			'comment_author_name',
			'comment_content',
			'comment_date',
			'comment_edit_link',
			'comment_reply_link',
			'comment_template',
			'comments',
			'comments_pagination',
			'comments_pagination_next',
			'comments_pagination_numbers',
			'comments_pagination_previous',
			'comments_title',
			'cover',
			'file',
			'footnotes',
			'gallery',
			'heading',
			'home_link',
			'image',
			'latest_comments',
			'latest_posts',
			'loginout',
			'navigation',
			'navigation_link',
			'navigation_submenu',
			'page_list',
			'page_list_item',
			'pattern',
			'post_author',
			'post_author_biography',
			'post_author_name',
			'post_comments_form',
			'post_content',
			'post_date',
			'post_excerpt',
			'post_featured_image',
			'post_navigation_link',
			'post_template',
			'post_terms',
			'post_title',
			'query',
			'query_no_results',
			'query_pagination',
			'query_pagination_next',
			'query_pagination_numbers',
			'query_pagination_previous',
			'query_title',
			'read_more',
			'rss',
			'search',
			'shortcode',
			'site_logo',
			'site_tagline',
			'site_title',
			'social_link',
			'tag_cloud',
			'template_part',
			'term_description',
		];

		return $gb_blocks;
	}

	/**
	 * Load the core GB blocks.
	 *
	 * This method is used to load the core GB blocks, if a block is used.
	 *
	 * See wp-includes/blocks/require-dynamic-blocks.php, for
	 * the list of blocks that are registered, that
	 * we are re-registering here, that were de-registered in the
	 * `_de_register_gb_blocks()` method.
	 *
	 * @since ??
	 */
	public static function _re_register_gb_blocks() {
		// if the GB blocks are not de-registered, then we don't need to re-register them.
		if ( ! self::$_gb_block_de_registered ) {
			return;
		}

		// only register the blocks once.
		if ( self::$_gb_block_re_registered ) {
			return;
		}

		// mark the blocks as re-registered.
		self::$_gb_block_re_registered = true;

		$gb_blocks = self::_get_gb_blocks();

		foreach ( $gb_blocks as $block ) {
			if ( function_exists( "register_block_core_{$block}" ) ) {
				call_user_func( "register_block_core_{$block}" );
			}
		}
	}

	/**
	 * Deregister Gutenberg blocks.
	 *
	 * This function deregisters all Gutenberg blocks by removing the action for each block.
	 *
	 * See wp-includes/blocks/require-dynamic-blocks.php, for
	 * the list of blocks that are registered, that
	 * we are de-registering here.
	 *
	 * See ET\Builder\FrontEnd\BlockParser\BlockParser::parse()
	 * for where they are registered, if needed.
	 */
	protected static function _de_register_gb_blocks(): void {

		// Check if the current request is a block renderer request in an edit context.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not needed here.
		$is_block_renderer_request = isset( $_SERVER['REQUEST_URI'] ) && strpos( esc_url_raw( sanitize_text_field( $_SERVER['REQUEST_URI'] ) ), '/block-renderer/' ) !== false;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not needed here.
		$is_edit_context = isset( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context'];

		if ( $is_block_renderer_request && $is_edit_context ) {
			return;
		}
		// if the GB blocks are already de-registered, then we don't need to de-register them again.
		if ( self::$_gb_block_de_registered ) {
			return;
		}

		// Define constant to note that GB blocks are de-registered.
		self::$_gb_block_de_registered = true;

		$gb_blocks = self::_get_gb_blocks();

		// Deregister GB blocks, remove action for each block.
		foreach ( $gb_blocks as $block ) {
			remove_action( 'init', "register_block_core_{$block}" );
		}
	}

	/**
	 * Register custom block parser.
	 *
	 * This function registers the custom block parser for the Divi module library by adding a filter
	 * to the `block_parser_class` hook, allowing customization of the block parser class to be used.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	protected function _register_custom_block_parser(): void {
		add_filter(
			'block_parser_class',
			function ( $original_block_parser_class ) {

				/**
				 * Filter whether to enable block parser.
				 *
				 * @since ??
				 *
				 * @param bool Default is `true`.
				 */
				$enable_divi_block_parser = apply_filters( 'divi_module_library_block_parser_enable', true );

				if ( $enable_divi_block_parser ) {
					return 'ET\Builder\FrontEnd\BlockParser\BlockParser';
				}

				return $original_block_parser_class;
			},
			999
		);
	}
}

$dependency_tree = new DependencyTree();

/*
 * Only register all modules under certain conditions, otherwise,
 * see BlockParser::proceed() and BlockParser::_load_module_from_block_name()
 * for how we lazy register modules on the front end page loads.
 */
if ( ConditionsUtility::should_register_all_d5_modules() ) {
	$dependency_tree->add_dependency( new AccordionItemModule() );
	$dependency_tree->add_dependency( new AccordionModule() );
	$dependency_tree->add_dependency( new AudioModule() );
	$dependency_tree->add_dependency( new BarCountersItemModule() );
	$dependency_tree->add_dependency( new BarCountersModule() );
	$dependency_tree->add_dependency( new BlogModule() );
	$dependency_tree->add_dependency( new BlurbModule() );
	$dependency_tree->add_dependency( new ButtonModule() );
	$dependency_tree->add_dependency( new CTAModule() );
	$dependency_tree->add_dependency( new CircleCounterModule() );
	$dependency_tree->add_dependency( new CodeModule() );
	$dependency_tree->add_dependency( new ColumnInnerModule() );
	$dependency_tree->add_dependency( new ColumnModule() );
	$dependency_tree->add_dependency( new CommentsModule() );
	$dependency_tree->add_dependency( new ContactFieldModule() );
	$dependency_tree->add_dependency( new ContactFormModule() );
	$dependency_tree->add_dependency( new CountdownTimerModule() );
	$dependency_tree->add_dependency( new DividerModule() );
	$dependency_tree->add_dependency( new FilterablePortfolioModule() );
	$dependency_tree->add_dependency( new FullwidthCodeModule() );
	$dependency_tree->add_dependency( new FullwidthHeaderModule() );
	$dependency_tree->add_dependency( new FullwidthImageModule() );
	$dependency_tree->add_dependency( new FullwidthMapModule() );
	$dependency_tree->add_dependency( new FullwidthMenuModule() );
	$dependency_tree->add_dependency( new FullwidthPortfolioModule() );
	$dependency_tree->add_dependency( new FullwidthPostContentModule() );
	$dependency_tree->add_dependency( new FullwidthPostSliderModule() );
	$dependency_tree->add_dependency( new FullwidthPostTitleModule() );
	$dependency_tree->add_dependency( new FullwidthSliderModule() );
	$dependency_tree->add_dependency( new GalleryModule() );
	$dependency_tree->add_dependency( new HeadingModule() );
	$dependency_tree->add_dependency( new IconModule() );
	$dependency_tree->add_dependency( new ImageModule() );
	$dependency_tree->add_dependency( new LoginModule() );
	$dependency_tree->add_dependency( new MapItemModule() );
	$dependency_tree->add_dependency( new MapModule() );
	$dependency_tree->add_dependency( new MenuModule() );
	$dependency_tree->add_dependency( new GroupModule() );
	$dependency_tree->add_dependency( new NumberCounterModule() );
	$dependency_tree->add_dependency( new PortfolioModule() );
	$dependency_tree->add_dependency( new PostContentModule() );
	$dependency_tree->add_dependency( new PostNavigationModule() );
	$dependency_tree->add_dependency( new PostSliderModule() );
	$dependency_tree->add_dependency( new PostTitleModule() );
	$dependency_tree->add_dependency( new PricingTablesItemModule() );
	$dependency_tree->add_dependency( new PricingTablesModule() );
	$dependency_tree->add_dependency( new RowInnerModule() );
	$dependency_tree->add_dependency( new RowModule() );
	$dependency_tree->add_dependency( new SearchModule() );
	$dependency_tree->add_dependency( new SectionModule() );
	$dependency_tree->add_dependency( new SidebarModule() );
	$dependency_tree->add_dependency( new SignupCustomFieldModule() );
	$dependency_tree->add_dependency( new SignupModule() );
	$dependency_tree->add_dependency( new SlideModule() );
	$dependency_tree->add_dependency( new SliderModule() );
	$dependency_tree->add_dependency( new SocialMediaFollowItemModule() );
	$dependency_tree->add_dependency( new SocialMediaFollowModule() );
	$dependency_tree->add_dependency( new TabModule() );
	$dependency_tree->add_dependency( new TabsModule() );
	$dependency_tree->add_dependency( new TeamMemberModule() );
	$dependency_tree->add_dependency( new TestimonialModule() );
	$dependency_tree->add_dependency( new TextModule() );
	$dependency_tree->add_dependency( new ToggleModule() );
	$dependency_tree->add_dependency( new VideoModule() );
	$dependency_tree->add_dependency( new VideoSliderItemModule() );
	$dependency_tree->add_dependency( new VideoSliderModule() );
	$dependency_tree->add_dependency( new WooCommerceBreadcrumbModule() );
	$dependency_tree->add_dependency( new WooCommerceCartNoticeModule() );
	$dependency_tree->add_dependency( new WooCommerceProductAddToCartModule() );
	$dependency_tree->add_dependency( new WooCommerceProductAdditionalInfoModule() );
	$dependency_tree->add_dependency( new WooCommerceProductDescriptionModule() );
	$dependency_tree->add_dependency( new WooCommerceProductGalleryModule() );
	$dependency_tree->add_dependency( new WooCommerceProductImagesModule() );
	$dependency_tree->add_dependency( new WooCommerceProductMetaModule() );
	$dependency_tree->add_dependency( new WooCommerceProductPriceModule() );
	$dependency_tree->add_dependency( new WooCommerceProductRatingModule() );
	$dependency_tree->add_dependency( new WooCommerceProductReviewsModule() );
	$dependency_tree->add_dependency( new WooCommerceProductStockModule() );
	$dependency_tree->add_dependency( new WooCommerceProductTabsModule() );
	$dependency_tree->add_dependency( new WooCommerceProductTitleModule() );
	$dependency_tree->add_dependency( new WooCommerceProductUpsellModule() );
	$dependency_tree->add_dependency( new WooCommerceRelatedProductsModule() );
}

// Parent modules.

// Child modules.

/**
 * A hook for adding modules from 3PS extension.
 *
 * @since ??
 *
 * @param DependencyTree $dependency_tree Dependency tree for VisualBuilder to load.
 *                                        See [DependencyTree](/docs/builder-api/php/Framework/DependencyManagement/DependencyTree).
 */
do_action( 'divi_module_library_modules_dependency_tree', $dependency_tree );

$modules = new Modules( $dependency_tree );
$modules->initialize();
