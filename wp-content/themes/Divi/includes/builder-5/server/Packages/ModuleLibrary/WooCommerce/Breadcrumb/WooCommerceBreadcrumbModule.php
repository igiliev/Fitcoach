<?php
/**
 * Module Library: WooCommerce Breadcrumb Module
 *
 * @since   ??
 * @package Divi
 */

namespace ET\Builder\Packages\ModuleLibrary\WooCommerce\Breadcrumb;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
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
use ET_Post_Stack;
use WP_Block_Type_Registry;
use WP_Block;

/**
 * WooCommerceBreadcrumbModule class.
 *
 * This class implements the functionality of a call-to-action component
 * in a frontend application. It provides functions for rendering the
 * WooCommerceBreadcrumb module,
 * managing REST API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class WooCommerceBreadcrumbModule implements DependencyInterface {
	/**
	 * Home URL.
	 *
	 * @var string
	 */
	public static $home_url;

	/**
	 * Render callback for the WooCommerceBreadcrumb module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ WooCommerceBreadcrumbEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 *
	 * @return string The HTML rendered output of the WooCommerceBreadcrumb module.
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
	 * WooCommerceBreadcrumbModule::render_callback( $attrs, $content, $block, $elements );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		// Get breadcrumb parameters from attributes.
		$product_id = $attrs['productId'] ?? 'current';

		// Extract from the nested structure if available.
		// TODO fix(D5, WooCommerceBreadcrumb): Refactor based on legacy `render` output [https://github.com/elegantthemes/Divi/issues/25705].
		$breadcrumb_home_url  = $attrs['homeLink']['innerContent']['desktop']['value']['linkUrl'] ?? '';
		$breadcrumb_home_text = $attrs['homeLink']['innerContent']['desktop']['value']['text'] ?? '';
		$breadcrumb_separator = $attrs['separator']['desktop']['value'] ?? '';

		// Get the breadcrumb HTML.
		$breadcrumb_html = self::get_breadcrumb(
			[
				'product'              => $product_id,
				'breadcrumb_home_text' => $breadcrumb_home_text,
				'breadcrumb_home_url'  => $breadcrumb_home_url,
				'breadcrumb_separator' => $breadcrumb_separator,
			]
		);

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
					// Use the generated breadcrumb HTML.
					$breadcrumb_html,
				],
			]
		);
	}

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the WooCommerceBreadcrumb module.
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
	 * WooCommerceBreadcrumbModule::module_classnames($args);
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
	 * WooCommerceBreadcrumb module script data.
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
	 * WooCommerceBreadcrumbModule::module_script_data( $args );
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
	 * WooCommerceBreadcrumb Module's style components.
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
											'selector' => "{$order_class} .woocommerce-breadcrumb",
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
										],
									],
								],
							],
						]
					),
					// Content.
					$elements->style(
						[
							'attrName' => 'content',
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
	 * Get the custom CSS fields for the Divi WooCommerceBreadcrumb module.
	 *
	 * This function retrieves the custom CSS fields defined for the Divi WooCommerceBreadcrumb module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi WooCommerceBreadcrumb module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the WooCommerceBreadcrumb module.
	 * ```
	 */
	public static function custom_css(): array {
		$registered_block = WP_Block_Type_Registry::get_instance()->get_registered( 'divi/woocommerce-breadcrumb' );

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
	 * Modify home URL for the breadcrumb.
	 *
	 * This method is used as a callback for the 'woocommerce_breadcrumb_home_url' filter
	 * to modify the home URL used in the breadcrumb.
	 *
	 * @since ??
	 *
	 * @return string The modified home URL.
	 */
	public static function modify_home_url(): string {
		return self::$home_url;
	}

	/**
	 * Load WooCommerce Breadcrumb Module.
	 *
	 * This function loads the WooCommerce Breadcrumb module by registering it
	 * with the `ModuleRegistration` class. It ensures that the module is only
	 * loaded if the WooCommerce plugin is active and the `wooProductPageModules`
	 * feature flag is enabled.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// Bail if WooCommerce plugin is not active or the WooCommerce Modules feature flag `wooProductPageModules` is disabled.
		if ( ! et_is_woocommerce_plugin_active() || ! et_get_experiment_flag( 'wooProductPageModules' ) ) {
			return;
		}

		$module_json_folder_path = dirname( __DIR__, 5 ) . '/visual-builder/packages/module-library/src/components/woocommerce/breadcrumb/';

		// Ensure that all filters and actions applied during module registration are registered before calling `ModuleRegistration::register_module()`.
		// However, for consistency, register all module-specific filters and actions before invoking `ModuleRegistration::register_module()`.
		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);
	}

	/**
	 * Generate the WooCommerce breadcrumb trail.
	 *
	 * This method generates an HTML representation of the WooCommerce breadcrumb trail, allowing customization
	 * through parameters and filters. It returns formatted breadcrumb content based on provided or default
	 * arguments. If in the theme builder or preview mode, a placeholder breadcrumb is returned instead.
	 *
	 * @param array $args {
	 *     Optional. Array of arguments to customize the breadcrumb.
	 *
	 *     @type string $product Specifies the context of the product ('current' or a specific ID).
	 *     @type string $breadcrumb_home_text Text label for the home page link in the breadcrumb. Default 'Home'.
	 *     @type string $breadcrumb_home_url URL for the home page link in the breadcrumb. Default site home URL.
	 *     @type string $breadcrumb_separator Separator character or string between breadcrumb items. Default '/'.
	 * }
	 *
	 * @return string The generated HTML content for the breadcrumb.
	 */
	public static function get_breadcrumb( array $args = [] ): string {
		// Set default values if not provided.
		$args = [
			'product'              => empty( $args['product'] ) ? 'current' : $args['product'],
			'breadcrumb_home_text' => empty( $args['breadcrumb_home_text'] ) ? __( 'Home', 'et_builder' ) : $args['breadcrumb_home_text'],
			'breadcrumb_home_url'  => empty( $args['breadcrumb_home_url'] ) ? get_home_url() : $args['breadcrumb_home_url'],
			'breadcrumb_separator' => empty( $args['breadcrumb_separator'] ) ? '/' : esc_html( $args['breadcrumb_separator'] ),
		];

		// TODO fix(D5, WooCommerceBreadcrumb): Refactor based on legacy `get_breadcrumb` for VB output [https://github.com/elegantthemes/Divi/issues/25705].
		// See: https://github.com/elegantthemes/submodule-builder-5/pull/5657#discussion_r2116418280.
		if ( Conditions::is_tb_enabled() || is_et_pb_preview() ) {
			return '<div class="woocommerce-breadcrumb"><a href="' . esc_url( home_url() ) . '">' . esc_html__( 'Home', 'divi' ) . '</a> ' . esc_html( $args['breadcrumb_separator'] ) . ' <a href="#">' . esc_html__( 'Product Category', 'divi' ) . '</a> ' . esc_html( $args['breadcrumb_separator'] ) . ' ' . esc_html__( 'Sample Product', 'divi' ) . '</div>';
		}

		// Update home URL which is rendered inside the breadcrumb function and pluggable via filter.
		self::$home_url = $args['breadcrumb_home_url'];
		add_filter(
			'woocommerce_breadcrumb_home_url',
			[ self::class, 'modify_home_url' ]
		);

		// Generate breadcrumb HTML using WooCommerceUtils::render_module_template.
		$breadcrumb = WooCommerceUtils::render_module_template(
			'woocommerce_breadcrumb',
			$args,
			[
				'product',
				'post',
				'wp_query',
			]
		);

		// Reset home URL.
		self::$home_url = get_home_url();
		remove_filter(
			'woocommerce_breadcrumb_home_url',
			[ self::class, 'modify_home_url' ]
		);

		return $breadcrumb;
	}
}
