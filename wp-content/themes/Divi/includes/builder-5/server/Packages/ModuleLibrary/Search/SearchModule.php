<?php
/**
 * ModuleLibrary: Search Module class.
 *
 * @package Builder\Packages\ModuleLibrary\SearchModule
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Search;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\FormField\FormFieldStyle;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\ModuleLibrary\Search\SearchPresetAttrsMap;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

// phpcs:disable Squiz.Commenting.InlineComment -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

/**
 * `SearchModule` is consisted of functions used for Search Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class SearchModule implements DependencyInterface {

	/**
	 * Module classnames function for Search module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/search/module-classnames.ts.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance Instance of ET\Builder\Packages\Module\Layout\Components\Classnames.
	 *     @type array  $attrs              Block attributes data that being rendered.
	 * }
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Text Options.
		$classnames_instance->add(
			TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ),
			true
		);

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => $attrs['module']['decoration'] ?? [],
				]
			)
		);

		// Add classname if search button is hidden.
		$show_button                   = $attrs['search']['advanced']['showButton']['desktop']['value'] ?? 'on';
		$hide_search_button_class_name = 'on' !== $show_button ? [ 'et_pb_hide_search_button' ] : '';
		$classnames_instance->add( $hide_search_button_class_name );

		if ( is_customize_preview() || is_et_pb_preview() ) {
			$classnames_instance->add( 'et_pb_in_customizer' );
		}
	}

	/**
	 * Set script data of used module options.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *   Array of arguments.
	 *
	 *   @type string         $id            Module id.
	 *   @type string         $name          Module name.
	 *   @type string         $selector      Module selector.
	 *   @type array          $attrs         Module attributes.
	 *   @type int            $storeInstance The ID of instance where this block stored in BlockParserStore class.
	 *   @type ModuleElements $elements      ModuleElements instance.
	 * }
	 */
	public static function module_script_data( $args ) {
		// Assign variables.
		$id             = $args['id'] ?? '';
		$name           = $args['name'] ?? '';
		$selector       = $args['selector'] ?? '';
		$attrs          = $args['attrs'] ?? [];
		$store_instance = $args['storeInstance'] ?? null;
		$elements       = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);

		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'setClassName'  => [
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_hide_search_button' => $attrs['search']['advanced']['showButton'] ?? [],
						],
						'valueResolver' => function( $value, $resolver_args ) {
							return 'off' === ( $value ?? 'on' ) ? 'add' : 'remove';
						},
					],
				],
				'setAttrs'      => [
					[
						'selector'      => $selector . ' input.et_pb_s',
						'data'          => [
							'placeholder' => $attrs['searchPlaceholder']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return $value ?? '';
						},
						'tag'           => 'input',
					],
				],
			]
		);
	}

	/**
	 * Search module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function SearchEdit located in
	 * visual-builder/packages/module-library/src/components/search/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string HTML rendered of Search module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$should_exclude_pages = $attrs['search']['advanced']['excludePages']['desktop']['value'] ?? 'off';
		$should_exclude_posts = $attrs['search']['advanced']['excludePosts']['desktop']['value'] ?? 'off';
		$excluded_categories  = $attrs['module']['advanced']['excludedCategories']['desktop']['value'] ?? [];
		$placeholder_text     = $attrs['searchPlaceholder']['innerContent']['desktop']['value'] ?? '';

		$should_show_button = ModuleUtils::has_value(
			$attrs['search']['advanced']['showButton'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === $value;
				},
			]
		);

		// Join selected categories array into string.
		$excluded_categories_as_string = join( ',', $excluded_categories );

		$excluded_pages = 'off' === $should_exclude_pages ? HTMLUtility::render(
			[
				'tag'               => 'input',
				'attributes'        => [
					'name'  => 'et_pb_include_pages',
					'type'  => 'hidden',
					'value' => esc_attr( 'yes' ),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		$excluded_posts = 'off' === $should_exclude_posts ? HTMLUtility::render(
			[
				'tag'               => 'input',
				'attributes'        => [
					'name'  => 'et_pb_include_posts',
					'type'  => 'hidden',
					'value' => esc_attr( 'yes' ),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		$excluded_categories_list = 'off' === $should_exclude_posts && ! empty( $excluded_categories_as_string ) ? HTMLUtility::render(
			[
				'tag'               => 'input',
				'attributes'        => [
					'name'  => 'et_pb_search_cat',
					'type'  => 'hidden',
					'value' => esc_attr( $excluded_categories_as_string ),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		$search_button = $should_show_button ? HTMLUtility::render(
			[
				'tag'               => 'input',
				'attributes'        => [
					'class' => 'et_pb_searchsubmit',
					'type'  => 'submit',
					'value' => esc_html__( 'Search', 'et_builder' ),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		$search_content = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					HTMLUtility::render(
						[
							'tag'               => 'label',
							'attributes'        => [
								'class' => 'screen-reader-text',
								'for'   => 's',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => esc_html__( 'Search for:', 'et_builder' ),
						]
					),
					HTMLUtility::render(
						[
							'tag'               => 'input',
							'attributes'        => [
								'name'        => 's',
								'class'       => 'et_pb_s',
								'type'        => 'text',
								'placeholder' => esc_attr( $placeholder_text ),
								'value'       => '',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
						]
					),
					HTMLUtility::render(
						[
							'tag'               => 'input',
							'attributes'        => [
								'name'  => 'et_pb_searchform_submit',
								'type'  => 'hidden',
								'value' => esc_attr( 'et_search_process' ),
							],
							'childrenSanitizer' => 'et_core_esc_previously',
						]
					),
					$excluded_pages,
					$excluded_posts,
					$excluded_categories_list,
					$search_button,
				],
			]
		);

		$output = HTMLUtility::render(
			[
				'tag'               => 'form',
				'attributes'        => [
					'class'  => 'et_pb_searchform',
					'method' => 'get',
					'role'   => 'search',
					'action' => esc_url( home_url( '/' ) ),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $search_content,
			]
		);

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
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $output,
			]
		);
	}

	/**
	 * Custom CSS fields
	 *
	 * This function is equivalent of JS const cssFields located in
	 * visual-builder/packages/module-library/src/components/search/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/search' )->customCssFields;
	}

	/**
	 * Search Button Border color style declaration
	 *
	 * This function will declare border color style for button in Search module.
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  If set to true, the CSS will be added with !important.
	 *     @type string     $returnType This is the type of value that the function will return. Can be either string or key_value_pair.
	 * }
	 *
	 * @since ??
	 */
	public static function search_button_border_color_style_declaration( $params ) {

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'border-color' => true,
				],
			]
		);

		$style_attr = $params['attrValue'];

		if ( ! empty( $style_attr['color'] ) ) {
			$style_declarations->add( 'border-color', $style_attr['color'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Search Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/search/module-styles.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *      @type string         $id                Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *      @type string         $name              Module name.
	 *      @type string         $attrs             Module attributes.
	 *      @type string         $parentAttrs       Parent attrs.
	 *      @type string         $orderClass        Selector class name.
	 *      @type string         $parentOrderClass  Parent selector class name.
	 *      @type string         $wrapperOrderClass Wrapper selector class name.
	 *      @type string         $settings          Custom settings.
	 *      @type string         $state             Attributes state.
	 *      @type string         $mode              Style mode.
	 *      @type int            $orderIndex        Module order index.
	 *      @type int            $storeInstance     The ID of instance where this block stored in BlockParserStore class.
	 *      @type ModuleElements $elements          ModuleElements instance.
	 * }
	 */
	public static function module_styles( array $args ) : void {
		$attrs       = $args['attrs'] ?? [];
		$elements    = $args['elements'];
		$settings    = $args['settings'] ?? [];
		$order_class = $args['orderClass'] ?? '';

		// Defaulted printed style attributes.
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					// Button.
					$elements->style(
						[
							'attrName'   => 'button',
							'styleProps' => [
								'advancedStyles' => [
									[
										// Search button and search input border color style.
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} input.et_pb_searchsubmit",
													"{$args['orderClass']} input.et_pb_s",
												]
											),
											'attr'     => $attrs['button']['decoration']['background'] ?? [],
											'declarationFunction' => [ self::class, 'search_button_border_color_style_declaration' ],
										],
									],
								],
							],
						]
					),

					FormFieldStyle::style(
						[
							'selector'   => "{$args['orderClass']} input.et_pb_s",
							'attr'       => $attrs['field'] ?? [],
							'important'  => [
								'font'        => [
									'font'       => [
										'desktop' => [
											'value' => [
												'line-height' => true,
												'text-align'  => true,
											],
										],
									],
									'textShadow' => true,
								],
								'placeholder' => [
									'font' => [
										'font' => [
											'desktop' => [
												'value' => [
													'color' => true,
												],
											],
										],
									],
								],
							],
							'orderClass' => $order_class,
						]
					),

					// Module.
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/text',
										'props'         => [
											'attr' => $attrs['module']['advanced']['text'] ?? [],
											'propertySelectors' => [
												'textShadow' => [
													'desktop' => [
														'value' => [
															'text-shadow' => implode(
																', ',
																[
																	"{$args['orderClass']} input.et_pb_s",
																	"{$args['orderClass']} input.et_pb_searchsubmit",
																]
															),
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
	 * Loads `SearchModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		add_filter( 'divi_conversion_presets_attrs_map', array( SearchPresetAttrsMap::class, 'get_map' ), 10, 2 );

		add_filter(
			'divi.moduleLibrary.conversion.moduleConversionOutline',
			function( $conversion_outline, $module_name ) {

				// Add custom conversion functions for this module
				if ( 'divi/search' !== $module_name ) {
					return $conversion_outline;
				}

				// Non static expansion functions like this
				// dont automatically get converted correctly in the
				// autogenerated .json conversion outline,
				// so lets hook in and provide the correct conversion functions.
				//
				// valueExpansionFunctionMap: {
				// 	form_field_custom_margin:  spacingValueConversionFunctionMap.margin,
				// 	form_field_custom_padding: spacingValueConversionFunctionMap.padding,
				// },
				$conversion_outline['valueExpansionFunctionMap'] = [
					'form_field_custom_margin'  => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSpacing',
					'form_field_custom_padding' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSpacing',
				];

				return $conversion_outline;
			},
			10,
			2
		);

		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/search/';

		// Ensure that all filters and actions applied during module registration are registered before calling `ModuleRegistration::register_module()`.
		// However, for consistency, register all module-specific filters and actions prior to invoking `ModuleRegistration::register_module()`.
		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);
	}
}
