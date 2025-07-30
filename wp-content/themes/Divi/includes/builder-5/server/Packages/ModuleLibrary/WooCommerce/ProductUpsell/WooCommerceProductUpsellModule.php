<?php
/**
 * Module Library: WooCommerceProductUpsell Module
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductUpsell;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\ArrayUtility;
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
 * WooCommerceProductUpsellModule class.
 *
 * This class implements the functionality of a call-to-action component
 * in a frontend application. It provides functions for rendering the
 * WooCommerceProductUpsell module,
 * managing REST API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class WooCommerceProductUpsellModule implements DependencyInterface {
	/**
	 * Holds prop values across static methods.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public static $static_props;

	/**
	 * Number of products to be offset.
	 *
	 * @since ??
	 *
	 * @var int Default 0.
	 */
	public static $offset = 0;

	/**
	 * Render callback for the WooCommerceProductUpsell module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ WooCommerceProductUpsellEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 *
	 * @return string The HTML rendered output of the WooCommerceProductUpsell module.
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
	 * WooCommerceProductUpsellModule::render_callback( $attrs, $content, $block, $elements );
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
								// Sale Badge.
								HTMLUtility::render(
									[
										'tag'        => 'div',
										'tagEscaped' => true,
										'attributes' => [
											'class' => 'element--saleBadge',
										],
										'childrenSanitizer' => 'et_core_esc_previously',
										'children'   => $elements->render(
											[
												'attrName' => 'saleBadge',
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
	 * arguments. It is used in the `render_callback` function of the WooCommerceProductUpsell module.
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
	 * WooCommerceProductUpsellModule::module_classnames($args);
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
	 * WooCommerceProductUpsell module script data.
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
	 * WooCommerceProductUpsellModule::module_script_data( $args );
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
	 * WooCommerceProductUpsell Module's style components.
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
											'selector' => "{$order_class}",
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
											'propertySelectors' => [
												'textShadow' => [
													'desktop' => [
														'value' => [
															'text-shadow' => "{$order_class} ul.products h3, {$order_class} ul.products  h1, {$order_class} ul.products  h2, {$order_class} ul.products  h4, {$order_class} ul.products  h5, {$order_class} ul.products  h6, {$order_class} ul.products .price, {$order_class} ul.products .price .amount",
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
					// Title.
					$elements->style(
						[
							'attrName' => 'title',
						]
					),
					// Image.
					$elements->style(
						[
							'attrName' => 'image',
						]
					),
					// Price.
					$elements->style(
						[
							'attrName' => 'price',
						]
					),
					// Product Title.
					$elements->style(
						[
							'attrName' => 'productTitle',
						]
					),
					// Rating.
					$elements->style(
						[
							'attrName' => 'rating',
						]
					),
					// Sale Badge.
					$elements->style(
						[
							'attrName' => 'saleBadge',
						]
					),
					// Sale Badge Text.
					$elements->style(
						[
							'attrName' => 'saleBadgeText',
						]
					),
					// Sale Price.
					$elements->style(
						[
							'attrName' => 'salePrice',
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
	 * Get the custom CSS fields for the Divi WooCommerceProductUpsell module.
	 *
	 * This function retrieves the custom CSS fields defined for the Divi WooCommerceProductUpsell module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi WooCommerceProductUpsell module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the WooCommerceProductUpsell module.
	 * ```
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/woocommerce-product-upsell' )->customCssFields;
	}

	/**
	 * Loads `WooCommerceProductUpsellModule` and registers Front-End render callback and REST API Endpoints.
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

		$module_json_folder_path = dirname( __DIR__, 5 ) . '/visual-builder/packages/module-library/src/components/woocommerce/product-upsell/';

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
	 * Gets the user set posts-per-page, columns and order-by values.
	 *
	 * This function is used to get the user set posts-per-page, columns and order-by values.
	 * Default values are set when parameters are empty, and are retrieved from {@see WooCommerceUtils::get_columns_posts_default_value()}.
	 *
	 * The static variable used in this method is set by {@see WooCommerceProductUpsellModule::get_upsells()}.
	 *
	 * @return array The selected args.
	 */
	public static function get_selected_upsell_display_args(): array {
		$selected_args = array();

		$selected_args['posts_per_page'] = ArrayUtility::get_value(
			self::$static_props,
			'posts_number',
			''
		);
		$selected_args['columns']        = ArrayUtility::get_value(
			self::$static_props,
			'columns_number',
			''
		);
		$selected_args['orderby']        = ArrayUtility::get_value(
			self::$static_props,
			'orderby',
			''
		);

		// Set default values when parameters are empty.
		$default = WooCommerceUtils::get_default_columns_posts_value();

		if ( empty( $selected_args['posts_per_page'] ) ) {
			$selected_args['posts_per_page'] = $default;
		}
		if ( empty( $selected_args['columns'] ) ) {
			$selected_args['columns'] = $default;
		}

		$selected_args = array_filter( $selected_args, 'strlen' );

		return $selected_args;
	}

	/**
	 * Returns the user selected posts-per-page, columns and order-by values for WooCommerce.
	 *
	 * This function merges the user selected values with the provided arguments.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $posts_per_page Optional. The number of posts per page. Default '-1'.
	 *     @type string $columns        Optional. The number of columns. Default '4'.
	 *     @type string $orderby        Optional. The order by. Default 'rand'.
	 * }
	 *
	 * @return array The selected args.
	 */
	public static function set_upsell_display_args( array $args ): array {
		$selected_args = self::get_selected_upsell_display_args();

		return wp_parse_args( $selected_args, $args );
	}

	/**
	 * Appends/updates offset value of the WP_Query that retrieves products.
	 *
	 * This function is used to add/updates offset value of the products retrieved by the WP_Query.
	 * If the passed `query_args` is not an array, the function will return the original value.
	 *
	 * @since ??
	 *
	 * @param array $query_args {
	 *     An array of arguments for retrieving products.
	 *
	 *     @type string $offset Optional. The offset number.
	 * }
	 *
	 * @return array The query args with the offset appended.
	 */
	public static function append_offset( $query_args ): array {
		if ( ! is_array( $query_args ) ) {
			return $query_args;
		}

		$query_args['offset'] = self::$offset;

		return $query_args;
	}

	/**
	 * Retrieves the product upsells markup for a given set of arguments.
	 *
	 * This function checks if the theme builder is enabled and returns a placeholder
	 * upsells if so. Otherwise, it uses the WooCommerceUtils to render the module template
	 * for the product upsells based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for rendering the product upsells markup.
	 *
	 *     @type string $product       Optional. The product identifier.
	 *     @type string $posts_number  Optional. The number of posts to display. Default '4'.
	 *     @type string $orderby       Optional. The order by. Default 'date'.
	 *     @type string $order         Optional. The order. Default 'desc'.
	 *     @type string $offset_number Optional. The offset number. Default '0'.
	 * }
	 *
	 * @param array $conditional_tags {
	 *     Optional. An array of conditional tags.
	 *
	 *     @type string $is_tb Optional. Whether the theme builder is enabled. Default 'false'.
	 * }
	 *
	 * @return string The rendered product upsells markup or a placeholder if in theme builder mode.
	 *
	 * @example:
	 * ```php
	 * $upsells = WooCommerceProductUpsellModule::get_upsells();
	 * // Returns the product upsells markup for the current product.
	 *
	 * $upsells = WooCommerceProductUpsellModule::get_upsells( [ 'product' => 123 ] );
	 * // Returns the product upsells markup for the product with ID 123.
	 * ```
	 */
	public static function get_upsells( array $args = [], array $conditional_tags = [] ): string {
		self::$static_props = $args;

		$offset_number = ArrayUtility::get_value( $args, 'offset_number', 0 );

		// TODO fix(D5, woocommerce modules product upsell) Update this to use the new D5 equivalent of `ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder` class in task: https://github.com/elegantthemes/Divi/issues/43080.
		// Force set product's class to ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder in TB
		// (via `woocommerce_product_class` filter) so related product can output visible content based on pre-filled value in TB.
		if ( 'true' === ArrayUtility::get_value( $conditional_tags, 'is_tb', false ) || is_et_pb_preview() ) {
			// Set upsells id; adjust it with module's arguments. This is specifically needed if
			// the module is fetching the value via REST API because some fields no longer use default value.
			ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder::set_tb_upsells_ids(
				array(
					'limit' => ArrayUtility::get_value( $args, 'posts_number', 4 ),
				)
			);

			add_filter( 'woocommerce_product_class', 'et_theme_builder_wc_product_class' );
		}

		$is_offset_valid = absint( $offset_number ) > 0;

		if ( $is_offset_valid ) {
			self::$offset = $offset_number;

			add_filter(
				'woocommerce_shortcode_products_query',
				array( self::class, 'append_offset' )
			);
		}

		add_filter(
			'woocommerce_upsell_display_args',
			array(
				self::class,
				'set_upsell_display_args',
			)
		);

		if ( isset( $args['orderby'] ) ) {
			$orderby = $args['orderby'];

			if ( in_array( $orderby, array( 'price', 'date' ), true ) ) {
				/*
				 * For the list of all allowed Orderby values, refer
				 *
				 * @see wc_products_array_orderby
				 */
				$args['order'] = 'asc';
			}
		}

		$output = WooCommerceUtils::render_module_template( 'woocommerce_upsell_display', $args );

		remove_filter(
			'woocommerce_upsell_display_args',
			array( self::class, 'set_upsell_display_args' )
		);

		if ( $is_offset_valid ) {
			remove_filter(
				'woocommerce_shortcode_products_query',
				array( self::class, 'append_offset' )
			);

			self::$offset = 0;
		}

		return $output;
	}
}
