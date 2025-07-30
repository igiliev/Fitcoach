<?php
/**
 * Class BlockParser
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\FrontEnd\BlockParser;

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block.

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicData;
use ET\Builder\Packages\ModuleLibrary\Modules;

/**
 * Class BlockParser
 *
 * Parses a document and constructs a list of parsed block objects
 *
 * @since ??
 */
class BlockParser extends \WP_Block_Parser {

	/**
	 * It's a property that is used to store the instance of the BlockParserStore class.
	 *
	 * @since ??
	 *
	 * @var number
	 */
	protected $_store_instance = null;

	/**
	 * An array to hold empty attributes for a block.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public $empty_attrs = [];

	/**
	 * An array to hold the modules that have been loaded.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	protected static $_modules_loaded = null;

	/**
	 * Get the instance of the BlockParserStore class
	 *
	 * @since ??
	 *
	 * @return number
	 */
	public function get_store_instance() {
		return $this->_store_instance;
	}

	/**
	 * Gets the block class map list.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_block_class_map_list() {
		// Define the base namespace for all modules.
		$base_namespace = 'ET\Builder\Packages\ModuleLibrary\\';

		$modules = [
			'divi/accordion-item'              => $base_namespace . 'AccordionItem\AccordionItemModule',
			'divi/accordion'                   => $base_namespace . 'Accordion\AccordionModule',
			'divi/audio'                       => $base_namespace . 'Audio\AudioModule',
			'divi/counter'                     => $base_namespace . 'BarCountersItem\BarCountersItemModule',
			'divi/counters'                    => $base_namespace . 'BarCounters\BarCountersModule',
			'divi/blog'                        => $base_namespace . 'Blog\BlogModule',
			'divi/blurb'                       => $base_namespace . 'Blurb\BlurbModule',
			'divi/button'                      => $base_namespace . 'Button\ButtonModule',
			'divi/cta'                         => $base_namespace . 'CTA\CTAModule',
			'divi/circle-counter'              => $base_namespace . 'CircleCounter\CircleCounterModule',
			'divi/code'                        => $base_namespace . 'Code\CodeModule',
			'divi/column-inner'                => $base_namespace . 'ColumnInner\ColumnInnerModule',
			'divi/column'                      => $base_namespace . 'Column\ColumnModule',
			'divi/comments'                    => $base_namespace . 'Comments\CommentsModule',
			'divi/contact-field'               => $base_namespace . 'ContactField\ContactFieldModule',
			'divi/contact-form'                => $base_namespace . 'ContactForm\ContactFormModule',
			'divi/countdown-timer'             => $base_namespace . 'CountdownTimer\CountdownTimerModule',
			'divi/divider'                     => $base_namespace . 'Divider\DividerModule',
			'divi/filterable-portfolio'        => $base_namespace . 'FilterablePortfolio\FilterablePortfolioModule',
			'divi/fullwidth-code'              => $base_namespace . 'FullwidthCode\FullwidthCodeModule',
			'divi/fullwidth-header'            => $base_namespace . 'FullwidthHeader\FullwidthHeaderModule',
			'divi/fullwidth-image'             => $base_namespace . 'FullwidthImage\FullwidthImageModule',
			'divi/fullwidth-map'               => $base_namespace . 'FullwidthMap\FullwidthMapModule',
			'divi/fullwidth-menu'              => $base_namespace . 'FullwidthMenu\FullwidthMenuModule',
			'divi/fullwidth-portfolio'         => $base_namespace . 'FullwidthPortfolio\FullwidthPortfolioModule',
			'divi/fullwidth-post-content'      => $base_namespace . 'FullwidthPostContent\FullwidthPostContentModule',
			'divi/fullwidth-post-slider'       => $base_namespace . 'FullwidthPostSlider\FullwidthPostSliderModule',
			'divi/fullwidth-post-title'        => $base_namespace . 'FullwidthPostTitle\FullwidthPostTitleModule',
			'divi/fullwidth-slider'            => $base_namespace . 'FullwidthSlider\FullwidthSliderModule',
			'divi/gallery'                     => $base_namespace . 'Gallery\GalleryModule',
			'divi/heading'                     => $base_namespace . 'Heading\HeadingModule',
			'divi/icon'                        => $base_namespace . 'Icon\IconModule',
			'divi/image'                       => $base_namespace . 'Image\ImageModule',
			'divi/login'                       => $base_namespace . 'Login\LoginModule',
			'divi/map-pin'                     => $base_namespace . 'MapItem\MapItemModule',
			'divi/map'                         => $base_namespace . 'Map\MapModule',
			'divi/menu'                        => $base_namespace . 'Menu\MenuModule',
			'divi/group'                       => $base_namespace . 'Group\GroupModule',
			'divi/number-counter'              => $base_namespace . 'NumberCounter\NumberCounterModule',
			'divi/portfolio'                   => $base_namespace . 'Portfolio\PortfolioModule',
			'divi/post-content'                => $base_namespace . 'PostContent\PostContentModule',
			'divi/post-nav'                    => $base_namespace . 'PostNavigation\PostNavigationModule',
			'divi/post-slider'                 => $base_namespace . 'PostSlider\PostSliderModule',
			'divi/post-title'                  => $base_namespace . 'PostTitle\PostTitleModule',
			'divi/pricing-table'               => $base_namespace . 'PricingTablesItem\PricingTablesItemModule',
			'divi/pricing-tables'              => $base_namespace . 'PricingTables\PricingTablesModule',
			'divi/row-inner'                   => $base_namespace . 'RowInner\RowInnerModule',
			'divi/row'                         => $base_namespace . 'Row\RowModule',
			'divi/search'                      => $base_namespace . 'Search\SearchModule',
			'divi/section'                     => $base_namespace . 'Section\SectionModule',
			'divi/sidebar'                     => $base_namespace . 'Sidebar\SidebarModule',
			'divi/signup-custom-field'         => $base_namespace . 'SignupCustomField\SignupCustomFieldModule',
			'divi/signup'                      => $base_namespace . 'Signup\SignupModule',
			'divi/slide'                       => $base_namespace . 'Slide\SlideModule',
			'divi/slider'                      => $base_namespace . 'Slider\SliderModule',
			'divi/social-media-follow-network' => $base_namespace . 'SocialMediaFollowItem\SocialMediaFollowItemModule',
			'divi/social-media-follow'         => $base_namespace . 'SocialMediaFollow\SocialMediaFollowModule',
			'divi/tab'                         => $base_namespace . 'Tab\TabModule',
			'divi/tabs'                        => $base_namespace . 'Tabs\TabsModule',
			'divi/team-member'                 => $base_namespace . 'TeamMember\TeamMemberModule',
			'divi/testimonial'                 => $base_namespace . 'Testimonial\TestimonialModule',
			'divi/text'                        => $base_namespace . 'Text\TextModule',
			'divi/toggle'                      => $base_namespace . 'Toggle\ToggleModule',
			'divi/video-slider-item'           => $base_namespace . 'VideoSliderItem\VideoSliderItemModule',
			'divi/video-slider'                => $base_namespace . 'VideoSlider\VideoSliderModule',
			'divi/video'                       => $base_namespace . 'Video\VideoModule',
		];

		/*
		 * Additional WooCommerce Modules dependencies.
		 *
		 * Ensure the following dependencies are only added when the wooProductPageModules feature flag is enabled.
		 */
		if ( et_is_woocommerce_plugin_active() && et_get_experiment_flag( 'wooProductPageModules' ) ) {
			$modules['divi/woocommerce-breadcrumb']              = $base_namespace . 'WooCommerce\Breadcrumb\WooCommerceBreadcrumbModule';
			$modules['divi/woocommerce-cart-notice']             = $base_namespace . 'WooCommerce\CartNotice\WooCommerceCartNoticeModule';
			$modules['divi/woocommerce-product-add-to-cart']     = $base_namespace . 'WooCommerce\ProductAddToCart\WooCommerceProductAddToCartModule';
			$modules['divi/woocommerce-product-additional-info'] = $base_namespace . 'WooCommerce\ProductAdditionalInfo\WooCommerceProductAdditionalInfoModule';
			$modules['divi/woocommerce-product-description']     = $base_namespace . 'WooCommerce\ProductDescription\WooCommerceProductDescriptionModule';
			$modules['divi/woocommerce-product-gallery']         = $base_namespace . 'WooCommerce\ProductGallery\WooCommerceProductGalleryModule';
			$modules['divi/woocommerce-product-images']          = $base_namespace . 'WooCommerce\ProductImages\WooCommerceProductImagesModule';
			$modules['divi/woocommerce-product-meta']            = $base_namespace . 'WooCommerce\ProductMeta\WooCommerceProductMetaModule';
			$modules['divi/woocommerce-product-price']           = $base_namespace . 'WooCommerce\ProductPrice\WooCommerceProductPriceModule';
			$modules['divi/woocommerce-product-rating']          = $base_namespace . 'WooCommerce\ProductRating\WooCommerceProductRatingModule';
			$modules['divi/woocommerce-product-reviews']         = $base_namespace . 'WooCommerce\ProductReviews\WooCommerceProductReviewsModule';
			$modules['divi/woocommerce-product-stock']           = $base_namespace . 'WooCommerce\ProductStock\WooCommerceProductStockModule';
			$modules['divi/woocommerce-product-tabs']            = $base_namespace . 'WooCommerce\ProductTabs\WooCommerceProductTabsModule';
			$modules['divi/woocommerce-product-title']           = $base_namespace . 'WooCommerce\ProductTitle\WooCommerceProductTitleModule';
			$modules['divi/woocommerce-product-upsell']          = $base_namespace . 'WooCommerce\ProductUpsells\WooCommerceProductUpsellModule';
			$modules['divi/woocommerce-related-products']        = $base_namespace . 'WooCommerce\RelatedProducts\WooCommerceRelatedProductsModule';
		}

		return $modules;
	}

	/**
	 * Load the module corresponding to the given block name
	 *
	 * @param string $block_name The name of the block to load the module for.
	 */
	protected static function _load_module_from_block_name( $block_name ) {
		// Mapping from block name to class name,
		// The keys represent the block names, and the values represent the corresponding class names.
		$block_to_class_map = self::get_block_class_map_list();

		/**
		 * Filter to add or modify the block to class mapping.
		 *
		 * This filter allows you to add or modify the block to class mapping. The block to class mapping is used to
		 * determine the class name of the module corresponding to the given block name. The block to class mapping is
		 * used to load the module corresponding to the given block name.
		 *
		 * @since ??
		 *
		 * @param array $block_to_class_map The block to class mapping. The keys represent the block names, and the values
		 *                                  represent the corresponding (FQCN) class names.
		 */
		$block_to_class_map = apply_filters( 'divi_block_parser_block_to_class_map', $block_to_class_map );

		/**
		 * Fires before loading the module corresponding to the given block name.
		 *
		 * @since ??
		 *
		 * @param string $block_name The name of the block.
		 */
		do_action( 'divi_block_parser_before_load_module', $block_name );

		// Look for the class name in the block to class mapping.
		$class_name = isset( $block_to_class_map[ $block_name ] ) ? $block_to_class_map[ $block_name ] : null;

		// bail early if the class name is empty.
		if ( empty( $class_name ) ) {
			return null;
		}

		// if the class name is already loaded, bail early.
		if ( ! empty( self::$_modules_loaded ) && in_array( $class_name, self::$_modules_loaded, true ) ) {
			return null;
		}

		/**
		 * Holds an instance of the module loader class.
		 *
		 * @var ET\Builder\Packages\ModuleLibrary\Module $module_loader
		 */
		$module_loader = new $class_name();
		$module_loader->load();

		if ( empty( self::$_modules_loaded ) ) {
			self::$_modules_loaded = [];
		}

		self::$_modules_loaded[] = $class_name;
	}

	/**
	 * Processes the next token from the input document
	 * and returns whether to proceed processing more tokens
	 *
	 * This is the "next step" function that essentially
	 * takes a token as its input and decides what to do
	 * with that token before descending deeper into a
	 * nested block tree or continuing along the document
	 * or breaking out of a level of nesting.
	 *
	 * @internal
	 * @since ??
	 *
	 * @return bool
	 */
	public function proceed() {
		$next_token = $this->next_token();
		[ $token_type, $block_name, $attrs, $start_offset, $token_length ] = $next_token;
		$stack_depth = count( $this->stack );

		// If this is a VB load, skip loading the module,
		// as VB loads all modules at once. See:
		// server/Packages/ModuleLibrary/Modules.php.
		if ( Conditions::should_register_all_d5_modules() ) {
			// do nothing, because VB loads all modules at once.
		} else {
			// proceed to load the module, just in time,
			// since they werent all loaded at once.
			self::_load_module_from_block_name( $block_name );
		}

		// we may have some HTML soup before the next block.
		$leading_html_start = $start_offset > $this->offset ? $this->offset : null;

		switch ( $token_type ) {
			case 'no-more-tokens':
				// if not in a block then flush output.
				if ( 0 === $stack_depth ) {
					$this->add_freeform();
					return false;
				}

				/*
				 * Otherwise we have a problem
				 * This is an error
				 *
				 * we have options
				 * - treat it all as freeform text
				 * - assume an implicit closer (easiest when not nesting)
				 */

				// for the easy case we'll assume an implicit closer.
				if ( 1 === $stack_depth ) {
					$this->add_block_from_stack();
					return false;
				}

				/*
				 * for the nested case where it's more difficult we'll
				 * have to assume that multiple closers are missing
				 * and so we'll collapse the whole stack piecewise
				 */
				$stack_count = count( $this->stack );
				while ( 0 < $stack_count ) {
					$this->add_block_from_stack();
					$stack_count = count( $this->stack );
				}
				return false;

			case 'void-block':
				/*
				 * easy case is if we stumbled upon a void block
				 * in the top-level of the document
				 */
				if ( 0 === $stack_depth ) {
					if ( isset( $leading_html_start ) ) {
						$this->output[] = (array) $this->freeform(
							substr(
								$this->document,
								$leading_html_start,
								$start_offset - $leading_html_start
							)
						);
					}

					$this->output[] = (array) BlockParserStore::add(
						new BlockParserBlock(
							$block_name,
							$attrs,
							array(),
							'',
							array(),
							$this->get_store_instance(),
							'divi/root',
							BlockParserStore::get_layout_type()
						)
					);
					$this->offset   = $start_offset + $token_length;
					return true;
				}

				$inner_block  = BlockParserStore::add(
					new BlockParserBlock(
						$block_name,
						$attrs,
						array(),
						'',
						array(),
						$this->get_store_instance(),
						'divi/root',
						BlockParserStore::get_layout_type()
					)
				);
				$parent_index = count( $this->stack ) - 1;
				$parent_block = $this->stack[ $parent_index ] ?? null;

				if ( $parent_block ) {
					$inner_block->parentId = $parent_block->block->id;
				}

				// otherwise we found an inner block.
				$this->add_inner_block(
					$inner_block,
					$start_offset,
					$token_length
				);
				$this->offset = $start_offset + $token_length;
				return true;

			case 'block-opener':
				// track all newly-opened blocks on the stack.
				array_push(
					$this->stack,
					new BlockParserFrame(
						BlockParserStore::add(
							new BlockParserBlock(
								$block_name,
								$attrs,
								array(),
								'',
								array(),
								$this->get_store_instance(),
								'divi/root',
								BlockParserStore::get_layout_type()
							)
						),
						$start_offset,
						$token_length,
						$start_offset + $token_length,
						$leading_html_start
					)
				);
				$this->offset = $start_offset + $token_length;
				return true;

			case 'block-closer':
				/*
				 * if we're missing an opener we're in trouble
				 * This is an error
				 */
				if ( 0 === $stack_depth ) {
					/*
					 * we have options
					 * - assume an implicit opener
					 * - assume _this_ is the opener
					 * - give up and close out the document
					 */
					$this->add_freeform();
					return false;
				}

				// if we're not nesting then this is easy - close the block.
				if ( 1 === $stack_depth ) {
					$this->add_block_from_stack( $start_offset );
					$this->offset = $start_offset + $token_length;
					return true;
				}

				/*
				 * otherwise we're nested, and we have to close out the current
				 * block and add it as a new innerBlock to the parent
				 */
				$stack_top                        = array_pop( $this->stack );
				$html                             = substr( $this->document, $stack_top->prev_offset, $start_offset - $stack_top->prev_offset );
				$stack_top->block->innerHTML     .= $html;
				$stack_top->block->innerContent[] = $html;
				$stack_top->prev_offset           = $start_offset + $token_length;

				$parent_index = count( $this->stack ) - 1;
				$parent_block = $this->stack[ $parent_index ] ?? null;

				if ( $parent_block ) {
					$stack_top->block->parentId = $parent_block->block->id;
				}

				$this->add_inner_block(
					$stack_top->block,
					$stack_top->token_start,
					$stack_top->token_length,
					$start_offset + $token_length
				);
				$this->offset = $start_offset + $token_length;
				return true;

			default:
				// This is an error.
				$this->add_freeform();
				return false;
		}
	}

	/**
	 * Returns a new block object for freeform HTML
	 *
	 * @internal
	 * @since ??
	 *
	 * @param string $inner_html HTML content of block.
	 *
	 * @return BlockParserBlock freeform block object.
	 */
	public function freeform( $inner_html ): BlockParserBlock {
		return BlockParserStore::add(
			new BlockParserBlock(
				null,
				$this->empty_attrs,
				array(),
				$inner_html,
				array( $inner_html ),
				$this->get_store_instance(),
				'divi/root',
				BlockParserStore::get_layout_type()
			)
		);
	}

	/**
	 * Combine post attributes with unsynced attributes.
	 *
	 * Combine by swapping out the values of the post attributes with the unsynced attributes.
	 *
	 * @since ??
	 *
	 * @param array $attrs Original attributes (passed by reference).
	 * @param array $unsync_attrs Unsynced attributes.
	 *
	 * @return array Combined attributes.
	 */
	public function combine_unsync_attrs( array &$attrs, array $unsync_attrs ): array {
		foreach ( $unsync_attrs as $key => $value ) {
			if ( is_array( $value ) ) {
				$this->combine_unsync_attrs( $attrs[ $key ], $value );
			} else {
				$attrs[ $key ] = $value;
			}
		}

		return $attrs;
	}

	/**
	 * Get `post_content` of a global layout if the post exists and matches the given arguments.
	 *
	 * This helper is intended to simplify the way to get `post_content` object of a global layout since we already know the ID.
	 * Instead of using the complex and heavy `WP_Query` class, we use the light and cached `get_post` build-in function.
	 *
	 * @since ??
	 *
	 * @param string  $content The content of the global layout.
	 * @param string  $post_id The ID of the post.
	 * @param array   $fields Optional. An array of `key => value` arguments to match against the post object. Default `[]`.
	 * @param array   $capabilities Optional. An array of user capability to match against the current user. Defaults `[]`.
	 * @param boolean $mask_post_password Optional. Whether to mask `post_password` field. Default `true`.
	 *
	 * @return string|null The post content or null on failure.
	 */
	public function get_global_layout_content( string $content, string $post_id, array $fields = array(), array $capabilities = array(), bool $mask_post_password = true ) {
		global $_is_parsing_global_layout;

		$post = get_post( $post_id );

		if ( ! $post ) {
			return null;
		}

		// Set $_is_parsing_global_layout so parser knows that it's parsing a global layout.
		$_is_parsing_global_layout = true;

		$parsed_global_layout = parse_blocks( $content );
		$parsed_actual_post   = parse_blocks( $post->post_content );

		// Unset $_is_parsing_global_layout so parser can continue working normally.
		$_is_parsing_global_layout = false;

		$post_attrs   = $parsed_actual_post[0]['attrs'] ?? [];
		$unsync_attrs = $parsed_global_layout[0]['attrs']['unsyncAttrs'] ?? [];

		// Update post attributes with the ones updated with unsynced attributes.
		$parsed_actual_post[0]['attrs'] = $this->combine_unsync_attrs( $post_attrs, $unsync_attrs );

		// serialize updated post content and add it to the post object.
		$post->post_content = serialize_blocks( $parsed_actual_post );

		$match = true;

		if ( $fields ) {
			foreach ( $fields as $field => $value ) {
				if ( ! isset( $post->{$field} ) ) {
					$match = false;
					break;
				}

				$match = is_array( $value ) && ! is_array( $post->{$field} ) ? in_array( $post->{$field}, $value, true ) : $post->{$field} === $value;

				if ( ! $match ) {
					break;
				}
			}
		}

		if ( $match && $capabilities ) {
			foreach ( $capabilities as $capability ) {
				if ( ! current_user_can( $capability, $post->ID ) ) {
					$match = false;
					break;
				}
			}
		}

		if ( $match ) {
			if ( $mask_post_password && $post->post_password ) {
				$post->post_password = '***';
			}

			return $post->post_content;
		}

		return null;
	}

	/**
	 * Checks if the content includes any Divi modules.
	 *
	 * @param string $content The block serialized content to be checked.
	 *
	 * @return bool True if a Divi module is found, false otherwise.
	 */
	public static function has_any_divi_block( $content ) {
		// Bail early if content is empty.
		if ( empty( $content ) ) {
			return false;
		}

		// Currently the only feasible way of detecting a Divi block is by looking for desktop/value key pairs.
		$has_desktop_value_pattern = strpos( $content, '"desktop":{"value"' ) !== false;

		// Check if there are divi modules in the content.
		// This check is needed because some modules may not have the desktop/value key pattern.
		// Example is global module.
		$has_divi_block = strpos( $content, '<!-- wp:divi' ) !== false;

		return $has_desktop_value_pattern || $has_divi_block;
	}

	/**
	 * Parses a document and returns a list of block structures
	 *
	 * When encountering an invalid parse will return a best-effort
	 * parse. In contrast to the specification parser this does not
	 * return an error on invalid inputs.
	 *
	 * @since ??
	 *
	 * @param string $document Input document to be parsed.
	 *
	 * @return BlockParserBlock[]
	 */
	public function parse( $document ) {
		global $_is_parsing_global_layout;

		$document_has_blocks = has_blocks( $document );

		// Exit early if $document doesn't contain any Divi blocks, use WordPress's block parser to process the blocks.
		if ( ! $this->has_any_divi_block( $document ) ) {
			if ( $document_has_blocks ) {
				// Re-register GB blocks.
				Modules::_re_register_gb_blocks();
			}

			return parent::parse( $document );
		}

		/**
		 * Filter to determine whether to create new store instance or not.
		 *
		 * @since ??
		 *
		 * @param bool $document_has_blocks Whether the `$document` contains blocks or not. Default is `true` when
		 *                                  the `$content` contains blocks.
		 */
		$new_store_instance = apply_filters( 'divi_front_end_block_parser_new_store_instance', $document_has_blocks );

		if ( $new_store_instance ) {
			$this->_store_instance = BlockParserStore::new_instance();
		} else {
			$this->_store_instance = BlockParserStore::maybe_new_instance();
		}

		// If post has global layout, replace it with actual content.
		// We do it here to avoid processing blocks multiple times.
		$global_layout_regex_pattern = '/<!-- wp:divi\/global-layout {"globalModule":"([0-9]+)".*\/-->/';
		$this->document              = $_is_parsing_global_layout
			? $document
			: preg_replace_callback(
				$global_layout_regex_pattern,
				function ( $matches ) {
					return BlockParserStore::get_global_layout_content(
						$matches[0],
						$matches[1],
						[
							'post_type'   => ET_BUILDER_LAYOUT_POST_TYPE,
							'post_status' => 'publish',
						]
					);
				},
				$document
			);

		$is_doing_content_filter = doing_filter( 'the_content' ) || doing_filter( 'et_builder_render_layout' );

		// Only run Dynamic Data processing when the content is being rendered on FE, by checking `the_content` filter
		// (visual builder on default layout / page) or `et_builder_render_layout` filter (theme builder / library layout).
		// Because we want Dynamic Data `$variable` only being replaced by its actual value when it is being rendered
		// not when it is being queried or retrieved programmatically.
		if ( $is_doing_content_filter ) {
			// Get processed dynamic data for all variables.
			$this->document = DynamicData::get_processed_dynamic_data( $this->document, null, true );
		}

		$this->offset      = 0;
		$this->output      = array();
		$this->stack       = array();
		$this->empty_attrs = json_decode( '{}', true );

		// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedDo -- This is WP core codebase
		do {
			// twiddle our thumbs.
		} while ( $this->proceed() );

		return $this->output;
	}
}
