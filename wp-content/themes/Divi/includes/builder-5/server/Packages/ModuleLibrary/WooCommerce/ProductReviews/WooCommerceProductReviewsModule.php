<?php
/**
 * Module Library: WooCommerceProductReviews Module
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductReviews;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\WooCommerce\WooCommerceUtils;
use WP_Block_Type_Registry;
use WP_Block;

/**
 * WooCommerceProductReviewsModule class.
 *
 * This class implements the functionality of a call-to-action component
 * in a frontend application. It provides functions for rendering the
 * WooCommerceProductReviews module,
 * managing REST API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class WooCommerceProductReviewsModule implements DependencyInterface {

	/**
	 * Render callback for the WooCommerceProductReviews module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ WooCommerceProductReviewsEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 *
	 * @return string The HTML rendered output of the WooCommerceProductReviews module.
	 *
	 * @example
	 * ```php
	 * $attrs = [
	 *   'attrName' => 'value',
	 *   //...
	 * ];
	 * $content = 'The block content.';
	 * $block = new WP_Block();
	 * $elements = new ModuleElements();
	 *
	 * WooCommerceProductReviewsModule::render_callback( $attrs, $content, $block, $elements );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => [
					$elements->style_components(
						[
							'attrName' => 'module',
						]
					),
					HTMLUtility::render(
						[
							'tag'               => 'div',
							'tagEscaped'        => true,
							'attributes'        => [
								'class' => 'elements--wrapper',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => [
								// Button.
								HTMLUtility::render(
									[
										'tag'        => 'div',
										'tagEscaped' => true,
										'attributes' => [
											'class' => 'element--button',
										],
										'childrenSanitizer' => 'et_core_esc_previously',
										'children'   => $elements->render(
											[
												'attrName' => 'button',
											]
										),
									]
								),
								// Field.
								HTMLUtility::render(
									[
										'tag'        => 'div',
										'tagEscaped' => true,
										'attributes' => [
											'class' => 'element--field',
										],
										'childrenSanitizer' => 'et_core_esc_previously',
										'children'   => $elements->render(
											[
												'attrName' => 'field',
											]
										),
									]
								),
							],
						]
					),
				],
			]
		);
	}

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the WooCommerceProductReviews module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js/module-library/module-classnames moduleClassnames}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance Module classnames instance.
	 *     @type array  $attrs              Block attributes data for rendering the module.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'attrs' => $attrs,
	 * ];
	 *
	 * WooCommerceProductReviewsModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$classnames_instance->add(
			TextClassnames::text_options_classnames(
				$attrs['module']['advanced']['text'] ?? [],
				[
					'orientation' => false,
				]
			),
			true
		);

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $attrs['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	/**
	 * WooCommerceProductReviews module script data.
	 *
	 * This function assigns variables and sets script data options for the module.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs ModuleScriptData}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for setting the module script data.
	 *
	 *     @type string         $id            The module ID.
	 *     @type string         $name          The module name.
	 *     @type string         $selector      The module selector.
	 *     @type array          $attrs         The module attributes.
	 *     @type int            $storeInstance The ID of the instance where this block is stored in the `BlockParserStore` class.
	 *     @type ModuleElements $elements      The `ModuleElements` instance.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * // Generate the script data for a module with specific arguments.
	 * $args = array(
	 *     'id'             => 'my-module',
	 *     'name'           => 'My Module',
	 *     'selector'       => '.my-module',
	 *     'attrs'          => array(
	 *         'portfolio' => array(
	 *             'advanced' => array(
	 *                 'showTitle'       => false,
	 *                 'showCategories'  => true,
	 *                 'showPagination' => true,
	 *             )
	 *         )
	 *     ),
	 *     'elements'       => $elements,
	 *     'store_instance' => 123,
	 * );
	 *
	 * WooCommerceProductReviewsModule::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( array $args ): void {
		// Assign variables.
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}

	/**
	 * WooCommerceProductReviews Module's style components.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js/module-library/module-styles moduleStyles}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *      @type string $id                Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *      @type string $name              Module name.
	 *      @type string $attrs             Module attributes.
	 *      @type string $parentAttrs       Parent attrs.
	 *      @type string $orderClass        Selector class name.
	 *      @type string $parentOrderClass  Parent selector class name.
	 *      @type string $wrapperOrderClass Wrapper selector class name.
	 *      @type string $settings          Custom settings.
	 *      @type string $state             Attributes state.
	 *      @type string $mode              Style mode.
	 *      @type ModuleElements $elements  ModuleElements instance.
	 * }
	 *
	 * @return void
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		// Extract the order class.
		$order_class = $args['orderClass'] ?? '';

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					// Module.
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn'     => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles' => [
									[
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => "{$order_class} p, {$order_class} .comment_postinfo *, {$order_class} .page_title, {$order_class} .comment-reply-title, {$order_class} .form-submit",
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
											'propertySelectors' => [
												'textShadow' => [
													'desktop' => [
														'value' => [
															'text-shadow' => "{$order_class} p, {$order_class} .comment_postinfo, {$order_class} .page_title, {$order_class} .comment-reply-title",
														],
													],
												],
											],
										],
									],
								],
							],
						]
					),
					// Image.
					$elements->style(
						[
							'attrName' => 'image',
						]
					),
					// Button.
					$elements->style(
						[
							'attrName' => 'button',
						]
					),
					// Comment.
					$elements->style(
						[
							'attrName' => 'comment',
						]
					),
					// Field.
					$elements->style(
						[
							'attrName' => 'field',
						]
					),
					// Form Title.
					$elements->style(
						[
							'attrName' => 'formTitle',
						]
					),
					// Meta.
					$elements->style(
						[
							'attrName' => 'meta',
						]
					),
					// Review Count.
					$elements->style(
						[
							'attrName' => 'reviewCount',
						]
					),
					// Star Rating.
					$elements->style(
						[
							'attrName' => 'starRating',
						]
					),

					// Module - Only for Custom CSS.
					CssStyle::style(
						[
							'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? [],
							'cssFields' => self::custom_css(),
						]
					),
				],
			]
		);
	}

	/**
	 * Get the custom CSS fields for the Divi WooCommerceProductReviews module.
	 *
	 * This function retrieves the custom CSS fields defined for the Divi WooCommerceProductReviews module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi WooCommerceProductReviews module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the WooCommerceProductReviews module.
	 * ```
	 */
	public static function custom_css(): array {
		$registered_block = WP_Block_Type_Registry::get_instance()->get_registered( 'divi/woocommerce-product-reviews' );

		if ( ! $registered_block ) {
			return [];
		}

		$custom_css_fields = $registered_block->customCssFields;

		if ( ! is_array( $custom_css_fields ) ) {
			return [];
		}

		return $custom_css_fields;
	}

	/**
	 * Loads `WooCommerceProductReviewsModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		/*
		 * Bail if  WooCommerce plugin is not active or the WooCommerce Modules feature flag `wooProductPageModules` is disabled.
		 */
		if ( ! et_is_woocommerce_plugin_active() || ! et_get_experiment_flag( 'wooProductPageModules' ) ) {
			return;
		}

		$module_json_folder_path = dirname( __DIR__, 5 ) . '/visual-builder/packages/module-library/src/components/woocommerce/product-reviews/';

		// Ensure that all filters and actions applied during module registration are registered before calling `ModuleRegistration::register_module()`.
		// However, for consistency, register all module-specific filters and actions prior to invoking `ModuleRegistration::register_module()`.
		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);
	}

	/**
	 * Gets the Reviews markup.
	 *
	 * This function returns the HTML for the product reviews markup based on the provided arguments.
	 * This includes the Reviews and the Review comment form.
	 *
	 * @since ??
	 *
	 * @param \WC_Product|false $product        WooCommerce Product.
	 * @param string            $header_level   Heading level.
	 * @param bool              $is_api_request Whether this is for a REST API request.
	 *                                          Should be set to TRUE when used in REST API call for proper results.
	 *
	 * @return string The rendered product reviews markup HTML.
	 */
	public static function get_reviews_markup( $product, string $header_level, bool $is_api_request = false ): string {
		if ( ! ( $product instanceof \WC_Product ) ) {
			return '';
		}

		if ( ! comments_open( $product->get_id() ) ) {
			return '';
		}

		$reviews_title = WooCommerceUtils::get_reviews_title( $product );
		// The product can be changed using the Product filter in the Settings modal.
		// Hence we provide the product ID to fetch data based on the selected product.
		$reviews         = get_comments(
			array(
				'post_id' => $product->get_id(),
				'status'  => 'approve',
			)
		);
		$total_pages     = get_comment_pages_count( $reviews );
		$reviews_content = wp_list_comments(
			array(
				'callback' => 'woocommerce_comments',
				'echo'     => false,
			),
			$reviews
		);

		// Provide the `$total_pages` var, otherwise `$pagination` will always be empty.
		if ( $is_api_request ) {
			$page = get_query_var( 'cpage' );
			if ( ! $page ) {
				$page = 1;
			}

			$args = array(
				'base'         => add_query_arg( 'cpage', '%#%' ),
				'format'       => '',
				'total'        => $total_pages,
				'current'      => $page,
				'echo'         => false,
				'add_fragment' => '#comments',
				'type'         => 'list',
			);

			global $wp_rewrite;

			if ( $wp_rewrite->using_permalinks() ) {
				$args['base'] = user_trailingslashit( trailingslashit( get_permalink() ) . $wp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
			}

			$pagination = paginate_links( $args );
		} else {
			$pagination = paginate_comments_links(
				array(
					'echo'  => false,
					'type'  => 'list',
					'total' => $total_pages,
				)
			);
		}

		// Pass $product to unify the flow of data.
		// Note in D4 this call also passes the $reviews variable to the function, but the function definition does not accept it.
		$reviews_comment_form = WooCommerceUtils::get_reviews_comment_form( $product );

		return sprintf(
			'
			<div id="reviews" class="woocommerce-Reviews">
				<div id="comments">
					<%3$s class="woocommerce-Reviews-title">
						%1$s
					</%3$s>
					<ol class="commentlist">
						%2$s
					</ol>
					<nav class="woocommerce-pagination">
						%4$s
					</nav>
				</div>
				<div id="review_form_wrapper">
					%5$s
				</div>
				<div class="clear"></div>
			</div>
			',
			/* 1$s */
			$reviews_title,
			/* 2$s */
			$reviews_content,
			/* 3$s */
			$header_level,
			/* 4$s */
			$pagination,
			/* 5$s */
			$reviews_comment_form
		);
	}

	/**
	 * Retrieves the product reviews markup HTML for a given set of arguments.
	 *
	 * This function returns the HTML for the product reviews markup based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for rendering the product reviews.
	 *
	 *     @type string $product      Optional. The product ID. Default 'current'.
	 *     @type string $header_level Optional. The heading level. Default 'h2'.
	 * }
	 *
	 * @param array $conditional_tags {
	 *     Optional. An array of conditional tags.
	 *
	 *     @type string $is_api_request Whether the request is an AJAX request.
	 * }
	 *
	 * @param array $current_page {
	 *     Optional. An array of current page args.
	 *
	 *     @type string $id Optional. The current page ID.
	 * }
	 *
	 * @return string The rendered product reviews markup HTML.
	 *
	 * @example:
	 * ```php
	 * $reviews = WooCommerceProductReviewsModule::get_reviews_html();
	 * // Returns the product reviews for the current product.
	 *
	 * $reviews = WooCommerceProductReviewsModule::get_reviews_html( [ 'product' => 123 ] );
	 * // Returns the product reviews for the product with ID 123.
	 * ```
	 */
	public static function get_reviews_html( array $args = [], array $conditional_tags = [], array $current_page = [] ): string {
		$maybe_product_id = 'current';

		$defaults = array(
			'header_level' => 'h2',
		);

		$args = wp_parse_args( $args, $defaults );

		// Get correct product ID when current request is computed callback request.
		if ( DynamicAssetsUtils::get_current_post_id() && ! Conditions::is_tb_enabled() ) {
			$maybe_product_id = DynamicAssetsUtils::get_current_post_id();
		}

		if ( array_key_exists( 'id', $current_page ) ) {
			$maybe_product_id = $current_page['id'];
		}

		if ( array_key_exists( 'product', $args ) && ! empty( $args['product'] ) ) {
			$maybe_product_id = $args['product'];
		}

		$is_tb = Conditions::is_tb_enabled();

		if ( $is_tb || is_et_pb_preview() ) {
			global $product;

			WooCommerceUtils::set_global_objects_for_theme_builder();
		} else {
			$product = WooCommerceUtils::get_product( $maybe_product_id );
		}

		if ( ! ( $product instanceof \WC_Product ) ) {
			return '';
		}

		$reviews_markup = self::get_reviews_markup( $product, $args['header_level'], true );

		if ( $is_tb || is_et_pb_preview() ) {
			WooCommerceUtils::reset_global_objects_for_theme_builder();
		}

		return $reviews_markup;
	}
}
