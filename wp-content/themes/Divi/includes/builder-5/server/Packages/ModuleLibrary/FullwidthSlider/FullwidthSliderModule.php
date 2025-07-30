<?php
/**
 * ModuleLibrary: Fullwidth Slider Module class.
 *
 * @package Builder\Packages\ModuleLibrary\FullwidthSliderModule
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FullwidthSlider;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\ModuleLibrary\FullwidthSlider\FullwidthSliderPresetAttrsMap;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupAttrNameResolved;

// phpcs:disable Squiz.Commenting.InlineComment -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

/**
 * `FullwidthSliderModule` is consisted of functions used for Fullwidth Slider Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class FullwidthSliderModule implements DependencyInterface {

	/**
	 * Module classnames function for Fullwidth Slider module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/fullwidth-slider/module-classnames.ts.
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
		$children_ids        = $args['childrenIds'];

		$show_arrows             = $attrs['arrows']['advanced']['show']['desktop']['value'] ?? 'on';
		$show_pagination         = $attrs['pagination']['advanced']['show']['desktop']['value'] ?? 'on';
		$auto                    = $attrs['module']['advanced']['auto']['desktop']['value'] ?? '';
		$auto_speed              = $attrs['module']['advanced']['autoSpeed']['desktop']['value'] ?? '7000';
		$auto_ignore_hover       = $attrs['module']['advanced']['autoIgnoreHover']['desktop']['value'] ?? '';
		$show_image_video_mobile = $attrs['image']['advanced']['showOnMobile']['desktop']['value'] ?? '';

		$classnames_instance->add( 'et_pb_slider_empty', empty( $children_ids ) );
		$classnames_instance->add( 'et_pb_slider_no_arrows', 'on' !== $show_arrows );
		$classnames_instance->add( 'et_pb_slider_no_pagination', 'on' !== $show_pagination );
		$classnames_instance->add( 'et_slider_auto', 'on' === $auto );
		$classnames_instance->add( 'et_slider_auto_ignore_hover', 'on' === $auto_ignore_hover );
		$classnames_instance->add( 'et_pb_slider_show_image', 'on' === $show_image_video_mobile );
		$classnames_instance->add( "et_slider_speed_{$auto_speed}", 'on' === $auto );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
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
	 * Set script data of used module options.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *   Array of arguments.
	 *
	 *   @type string $id       Module id.
	 *   @type string $selector Module selector.
	 *   @type array  $attrs    Module attributes.
	 * }
	 */
	public static function module_script_data( $args ) {
		$id             = $args['id'] ?? '';
		$name           = $args['name'] ?? '';
		$selector       = $args['selector'] ?? '';
		$attrs          = $args['attrs'] ?? [];
		$elements       = $args['elements'];
		$store_instance = $args['storeInstance'] ?? null;

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
				'hoverSelector' => $selector,
				'setVisibility' => [
					[
						'selector'      => $selector . ' .et-pb-slider-arrows',
						'data'          => $attrs['arrows']['advanced']['show'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
					[
						'selector'      => $selector . ' .et-pb-controllers',
						'data'          => $attrs['pagination']['advanced']['show'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
				],
			]
		);
	}

	/**
	 * Fullwidth Slider module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function FullwidthSliderEdit located in
	 * visual-builder/packages/module-library/src/components/fullwidth-slider/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string HTML rendered of Fullwidth Slider module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function ( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

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
				'childrenIds'         => $children_ids,
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_slides',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
						'children'          => $content,
					]
				),
			]
		);
	}

	/**
	 * Custom CSS fields
	 *
	 * This function is equivalent of JS const cssFields located in
	 * visual-builder/packages/module-library/src/components/fullwidth-slider/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/fullwidth-slider' )->customCssFields;
	}

	/**
	 * Button Alignment Style Declaration
	 *
	 * This function will declare button alignment style for Fullwidth Slider module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/fullwidth-slider/style-declarations/button-alignment/index.ts
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
	public static function button_alignment_style_declaration( $params ) {
		$alignment = $params['attrValue']['alignment'] ?? '';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( ! empty( $alignment ) ) {
			$style_declarations->add( 'text-align', $alignment );
		}

		return $style_declarations->value();
	}

	/**
	 * Style declaration for fullwidth slider's border.
	 *
	 * This function is used to generate the style declaration for the border of a fullwidth slider module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string The generated CSS style declaration.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *   'attrValue' => [
	 *     'radius' => [
	 *       'desktop' => [
	 *         'default' => '10px',
	 *         'hover'   => '8px',
	 *       ],
	 *     ],
	 *   ],
	 *   'important'  => true,
	 *   'returnType' => 'string',
	 * ];
	 * $styleDeclaration = AccordionModule::overflow_style_declaration( $args );
	 * ```
	 */
	public static function overflow_style_declaration( array $params ): string {
		$radius = $params['attrValue']['radius'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( ! $radius ) {
			return $style_declarations->value();
		}

		$all_corners_zero = true;

		// Check whether all corners are zero.
		// If any corner is not zero, update the variable and break the loop.
		foreach ( $radius as $corner => $value ) {
			if ( 'sync' === $corner ) {
				continue;
			}

			$corner_value = SanitizerUtility::numeric_parse_value( $value ?? '' );
			if ( 0.0 !== ( $corner_value['valueNumber'] ?? 0.0 ) ) {
				$all_corners_zero = false;
				break;
			}
		}

		if ( $all_corners_zero ) {
			return $style_declarations->value();
		}

		// Add overflow hidden when any corner's border radius is not zero.
		$style_declarations->add( 'overflow', 'hidden' );

		return $style_declarations->value();
	}

	/**
	 * Fullwidth Slider Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/fullwidth-slider/styles.tsx.
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
	 *      @type ModuleElements $elements          ModuleElements instance.
	 * }
	 */
	public static function module_styles( $args ) {
		$attrs                       = $args['attrs'] ?? [];
		$elements                    = $args['elements'];
		$settings                    = $args['settings'] ?? [];
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];
		$order_class                 = $args['orderClass'] ?? '';

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
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),
					CommonStyle::style(
						[
							'selector'   => implode(
								', ',
								[
									"{$args['orderClass']} .et-pb-slider-arrows .et-pb-arrow-prev",
									"{$args['orderClass']} .et-pb-slider-arrows .et-pb-arrow-next",
								]
							),
							'attr'       => $attrs['arrows']['advanced']['color'] ?? [],
							'property'   => 'color',
							'orderClass' => $order_class,
						]
					),
					// Dot nav.
					$elements->style(
						[
							'attrName' => 'dotNav',
						]
					),
					// Image.
					$elements->style(
						[
							'attrName' => 'image',
						]
					),
					TextStyle::style(
						[
							'selector'   => "{$args['orderClass']} .et_pb_slide .et_pb_slide_description",
							'attr'       => $attrs['module']['advanced']['text'] ?? [],
							'orderClass' => $order_class,
						]
					),
					// Title.
					$elements->style(
						[
							'attrName' => 'title',
						]
					),
					// Content.
					$elements->style(
						[
							'attrName' => 'content',
						]
					),
					// Button.
					$elements->style(
						[
							'attrName' => 'button',
						]
					),
					CommonStyle::style(
						[
							'selector'            => "{$args['orderClass']} .et_pb_button_wrapper",
							'attr'                => $attrs['button']['decoration']['button'] ?? [],
							'declarationFunction' => [ self::class, 'button_alignment_style_declaration' ],
							'orderClass'          => $order_class,
						]
					),
					CssStyle::style(
						[
							'selector'   => $args['orderClass'],
							'attr'       => $attrs['css'] ?? [],
							'cssFields'  => self::custom_css(),
							'orderClass' => $order_class,
						]
					),
				],
			]
		);
	}

	/**
	 * Loads `FullwidthSliderModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/fullwidth-slider/';

		add_filter( 'divi_conversion_presets_attrs_map', array( FullwidthSliderPresetAttrsMap::class, 'get_map' ), 10, 2 );

		add_filter(
			'divi.moduleLibrary.conversion.moduleConversionOutline',
			function ( $conversion_outline, $module_name ) {

				// Add custom conversion functions for this module
				if ( 'divi/fullwidth-slider' !== $module_name ) {
					return $conversion_outline;
				}

				// Non static expansion functions like this
				// dont automatically get converted correctly in the
				// autogenerated .json conversion outline,
				// so lets hook in and provide the correct conversion functions.
				//
				// valueExpansionFunctionMap: {
				//   text_border_radius: borderValueConversionFunctionMap.radius,
				//   button_rel:         buttonValueConversionFunctionMap['innerContent.*.rel'],
				// },
				$conversion_outline['valueExpansionFunctionMap'] = [
					'text_border_radius' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertBorderRadii',
					'button_rel'         => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertButtonRel',
				];

				return $conversion_outline;
			},
			10,
			2
		);

		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/fullwidth-slider/';

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
	 * Resolve the group preset attribute name for the Fullwidth Slider module.
	 *
	 * @param GlobalPresetItemGroupAttrNameResolved|null $attr_name_to_resolve The attribute name to be resolved.
	 * @param array                                      $params               The filter parameters.
	 *
	 * @return GlobalPresetItemGroupAttrNameResolved|null The resolved attribute name.
	 */
	public static function option_group_preset_resolver_attr_name( $attr_name_to_resolve, array $params ):?GlobalPresetItemGroupAttrNameResolved {
		// Bydefault, $attr_name_to_resolve is a null value.
		// If it is not null, it means that the attribute name is already resolved.
		// In this case, we return the resolved attribute name.
		if ( null !== $attr_name_to_resolve ) {
			return $attr_name_to_resolve;
		}

		if ( $params['moduleName'] !== $params['dataModuleName'] ) {
			if ( 'divi/fullwidth-slider' === $params['dataModuleName'] && 'divi/slider' !== $params['moduleName'] ) {
				if ( 'module.decoration.background' === $params['attrName'] ) {
					return new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => 'children.module.decoration.background',
							'attrSubName' => $params['attrSubName'] ?? null,
						]
					);
				}
			}

			if ( 'divi/fullwidth-slider' === $params['moduleName'] && 'divi/slider' !== $params['dataModuleName'] ) {
				if ( 'children.module.decoration.background' === $params['attrName'] ) {
					return new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => 'module.decoration.background',
							'attrSubName' => $params['attrSubName'] ?? null,
						]
					);
				}
			}
		}

		return $attr_name_to_resolve;
	}
}
