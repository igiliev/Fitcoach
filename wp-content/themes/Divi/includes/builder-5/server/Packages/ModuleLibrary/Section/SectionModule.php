<?php
/**
 * ModuleLibrary: Section Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Section;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Settings\Settings;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\Framework\Utility\TextTransform;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\Classnames;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\Module\Options\Dividers\DividersComponent;
use ET\Builder\Packages\Module\Options\Dividers\DividersUtils;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleLibrary\Section\SectionPresetAttrsMap;
use ET\Builder\Packages\StyleLibrary\Declarations\BoxShadow\BoxShadow;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block_Type_Registry;
use WP_Block;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * SectionModule class.
 *
 * This class contains functionality used for Section module such as FrontEnd rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class SectionModule implements DependencyInterface {

	/**
	 * List of render-able row structure.
	 *
	 * @since ??
	 *
	 * @var string[]
	 */
	private static $_renderable_row_structure = [
		'1-4_3-4',
		'3-4_1-4',
		'1-4_1-4_1-2',
		'1-4_1-2_1-4',
		'1-2_1-4_1-4',
	];

	/**
	 * Get render-able specialty section's row structure which is based on given columnIds.
	 *
	 * This function determines the specialty row structure for a section module based on the column types of the inner blocks.
	 * The specialty row structure is obtained by concatenating the column types with underscores and converting them
	 * to param case using `TextTransform::param_case()`.
	 * If the resulting structure is in the renderable row structures list, it is returned with the `'et_pb_row-'` prefix; otherwise, `null` is returned.
	 *
	 * @since ??
	 *
	 * @param array $inner_blocks The inner blocks of the section module.
	 *
	 * @return string|null Returns the class name for the specialty row structure, or `null` if it is not renderable.
	 */
	public static function get_specialty_row_structure_classname( array $inner_blocks ): ?string {
		$column_layout = [];

		foreach ( $inner_blocks as $inner_block ) {
			$column_type = $inner_block['attrs']['module']['advanced']['type']['desktop']['value'];

			if ( $column_type ) {
				$column_layout[] = $column_type;
			}
		}

		$structure = implode(
			'_',
			array_map(
				function ( $string ) {
					return TextTransform::param_case( $string );
				},
				$column_layout
			)
		);

		return $structure && in_array( $structure, self::$_renderable_row_structure, true ) ? 'et_pb_row-' . $structure : null;
	}

	/**
	 * Get the module classnames for the Section module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/section-module-classnames moduleClassnames}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance An instance of `ET\Builder\Packages\Module\Layout\Components\Classnames` class.
	 *     @type array  $attrs              Block attributes data that is being rendered.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * // Example 1: Adding classnames for the toggle options.
	 * SectionModule::module_classnames( [
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'attrs' => [
	 *         'module' => [
	 *             'advanced' => [
	 *                 'text' => ['red', 'bold']
	 *             ]
	 *         ]
	 *     ]
	 * ] );
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Example 2: Adding classnames for the module.
	 * SectionMemberModule::module_classnames( [
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'attrs' => [
	 *         'module' => [
	 *             'decoration' => ['shadow', 'rounded']
	 *         ]
	 *     ]
	 * ] );
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];
		$is_flexbox_enabled  = et_get_experiment_flag( 'flexbox' );

		// Module components.
		$type         = $attrs['module']['advanced']['type']['desktop']['value'] ?? null;
		$is_fullwidth = 'fullwidth' === $type;

		$classnames_instance->add( 'et_pb_fullwidth_section', $is_fullwidth );

		$position_value = $attrs['module']['decoration']['position']['desktop']['value'] ?? [];

		if ( 'absolute' === ( $position_value['mode'] ?? null ) ) {
			$classnames_instance->add( 'et_pb_section--absolute' );
		}

		if ( 'fixed' === ( $position_value['mode'] ?? null ) ) {
			$classnames_instance->add( 'et_pb_section--fixed' );
		}

		$layout_value = $attrs['module']['decoration']['layout']['desktop']['value']['display'] ?? ( $is_flexbox_enabled ? 'flex' : 'block' );

		if ( 'specialty' === $type ) {
			$classnames_instance->add( 'et_section_specialty' );

			$make_equal       = $attrs['module']['advanced']['gutter']['desktop']['value']['makeEqual'] ?? 'off';
			$is_make_equal_on = 'on' === $make_equal;

			$classnames_instance->add( 'et_pb_equal_columns', $is_make_equal_on );

			$gutter_width = $attrs['module']['advanced']['gutter']['desktop']['value']['width'] ?? 3;

			// Set layout_value based on feature flag: 'block' if flexbox is not enabled, otherwise use 'flex' as default.
			$is_flex_layout = 'flex' === $layout_value;

			$classnames_instance->add( 'et_pb_gutters' . $gutter_width, '' !== $gutter_width && ! $is_flex_layout );
		} else {
			$classnames_instance->add( 'et_section_regular' );
		}

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
					'attrs'    => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link'     => $args['attrs']['module']['advanced']['link'] ?? [],
							'dividers' => $args['attrs']['module']['advanced']['dividers'] ?? [],
						]
					),
					'dividers' => true,
				]
			)
		);
	}

	/**
	 * Set Section module script data.
	 *
	 * This function generates and sets the script data for the module,
	 * which includes assigning variables, setting element script data options,
	 * and setting visibility for certain elements based on the provided attributes.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments for generating the script data.
	 *
	 *     @type object $elements       The elements object.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     // Generate the script data for a module with specific arguments.
	 *     $args = array(
	 *         'id'             => 'my-module',
	 *         'name'           => 'My Module',
	 *         'selector'       => '.my-module',
	 *         'attrs'          => array(
	 *             'team_member' => array(
	 *                 'advanced' => array(
	 *                     'showTitle'       => false,
	 *                     'showCategories'  => true,
	 *                     'showPagination' => true,
	 *                 )
	 *             )
	 *         ),
	 *         'elements'       => $elements,
	 *         'storeInstance' => 123,
	 *     );
	 *
	 *     SectionMemberModule::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( array $args ): void {
		// Assign variables.
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
				'setClassName'  => [
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_section--absolute' => $attrs['module']['decoration']['position'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'absolute' === ( $value['mode'] ?? '' ) ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_section--fixed' => $attrs['module']['decoration']['position'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'fixed' === ( $value['mode'] ?? '' ) ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * Get the custom CSS fields for the Divi Section module.
	 *
	 * This function retrieves the custom CSS fields defined for the Divi Section module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi Section module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the Section module.
	 * ```
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/section' )->customCssFields;
	}

	/**
	 * Load Section module style components.
	 *
	 * This function is responsible for loading styles for the module. It takes an array of arguments
	 * which includes the module ID, name, attributes, settings, and other details. The function then
	 * uses these arguments to dynamically generate and add the required styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/section-module-styles ModuleStyles}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string         $id                       The module ID. In Visual Builder (VB), the ID of the module is a UUIDV4 string.
	 *                                                    In FrontEnd (FE), the ID is the order index.
	 *     @type string         $name                     The module name.
	 *     @type array          $attrs                    Optional. The module attributes. Default `[]`.
	 *     @type ModuleElements $elements                 ModuleElements instance.
	 *     @type array          $settings                 Optional. The module settings. Default `[]`.
	 *     @type string         $orderClass               The selector class name.
	 *     @type int            $orderIndex               The order index of the module.
	 *     @type int            $storeInstance            The ID of instance where this block stored in BlockParserStore.
	 *     @type array          $defaultPrintedStyleAttrs Optional. Default printed style attributes. Default `[]`.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     SectionModule::module_styles([
	 *         'id'        => 'module-1',
	 *         'name'      => 'Accordion Module',
	 *         'attrs'     => [],
	 *         'elements'  => $elementsInstance,
	 *         'settings'  => $moduleSettings,
	 *         'orderClass'=> '.accordion-module'
	 *     ]);
	 * ```
	 */
	public static function module_styles( array $args ): void {
		// Get the post type.
		$post_type = get_post_type();

		// Selector prefix (default to what we use for Posts).
		$selector_prefix = '.et-l--post';

		// Compare the post type against our defined types, changing the selector prefix if needed.
		// These are defined in includes/builder/frontend-builder/theme-builder/theme-builder.php.
		switch ( $post_type ) {
			case ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE:
				$selector_prefix = '.et-l--header';
				break;

			case ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE:
				$selector_prefix = '.et-l--body';
				break;

			case ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE:
				$selector_prefix = '.et-l--footer';
				break;
		}

		$attrs       = $args['attrs'] ?? [];
		$elements    = $args['elements'];
		$settings    = $args['settings'] ?? [];
		$order_class = $args['orderClass'];

		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];

		$base_order_class = $args['baseOrderClass'] ?? '';

		// Add module styles.
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
								'background'               => [
									'propertySelectors' => [
										'desktop' => [
											'value' => [
												'background-color' => "{$selector_prefix} > .et_builder_inner_content > .et_pb_section{$base_order_class}",
											],
										],
									],
									'important'         => true,
								],
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/dividers',
										'props'         => [
											'name'      => $args['name'],
											'index'     => $args['orderIndex'],
											'placement' => 'top',
											'attr'      => $attrs['module']['advanced']['dividers']['top'] ?? [],
											'backgroundColors' => [
												'siblingBackgroundAttr' => $args['siblingAttrs']['previous']['background'] ?? '',
												'moduleBackgroundAttr'  => $attrs['module']['decoration']['background'] ?? '',
												'defaultColor'          => $args['parentAttrs']['section_background_color'] ?? '',
											],
											'fullwidth' => 'fullwidth' === ( $attrs['module']['advanced']['type']['desktop']['value'] ?? '' ),
										],
									],
									[
										'componentName' => 'divi/dividers',
										'props'         => [
											'name'      => $args['name'],
											'index'     => $args['orderIndex'],
											'placement' => 'bottom',
											'attr'      => $attrs['module']['advanced']['dividers']['bottom'] ?? [],
											'backgroundColors' => [
												'siblingBackgroundAttr' => $args['siblingAttrs']['next']['background'] ?? '',
												'moduleBackgroundAttr'  => $attrs['module']['decoration']['background'] ?? '',
												'defaultColor'          => $args['parentAttrs']['section_background_color'] ?? '',
											],
											'fullwidth' => 'fullwidth' === ( $attrs['module']['advanced']['type']['desktop']['value'] ?? '' ),
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['decoration']['boxShadow'] ?? [],
											'declarationFunction' => [ self::class, 'section_box_shadow_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/css',
										'props'         => [
											'selector'  => $order_class,
											'attr'      => $attrs['css'] ?? [],
											'cssFields' => self::custom_css(),
										],
									],
								],
							],
						]
					),
					// Column 1.
					$elements->style(
						[
							'attrName' => 'column1',
						]
					),
					// Column 2.
					$elements->style(
						[
							'attrName' => 'column2',
						]
					),
					// Column 3.
					$elements->style(
						[
							'attrName' => 'column3',
						]
					),
					// Inner Sizing.
					$elements->style(
						[
							'attrName' => 'innerSizing',
						]
					),
				],
			]
		);
	}

	/**
	 * Render callback function for the Section module.
	 *
	 * This function generates HTML for rendering on the FrontEnd (FE).
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       The attributes passed to the block.
	 * @param string         $content                     The content inside the block.
	 * @param WP_Block       $block                       The parsed block object.
	 * @param ModuleElements $elements                    The elements object containing style components.
	 * @param array          $default_printed_style_attrs The default printed style attributes.
	 *
	 * @return string The rendered HTML content.
	 *
	 * @example:
	 * ```php
	 * // Render the block with an empty content and default attributes.
	 * $attrs = [];
	 * $content = '';
	 * $block = new Block();
	 * $elements = new Elements();
	 *
	 * $rendered_content = SectionModule::render_callback($attrs, $content, $block, $elements);
	 * ```

	 * @example:
	 * ```php
	 * // Render the block with custom attributes and content.
	 * $attrs = [
	 *     'param1' => 'value1',
	 *     'param2' => 'value2',
	 * ];
	 * $content = '<p>Block content</p>';
	 * $block = new Block();
	 * $elements = new Elements();
	 *
	 * $rendered_content = SectionModule::render_callback($attrs, $content, $block, $elements);
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$type         = $attrs['module']['advanced']['type']['desktop']['value'] ?? '';
		$is_specialty = 'specialty' === $type;
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function ( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		if ( $is_specialty ) {
			$row_structure_classname = self::get_specialty_row_structure_classname( $block->parsed_block['innerBlocks'] );

			$specialty_row_classnames = new Classnames( [ 'et_pb_row' => true ] );
			$specialty_row_classnames->add( $row_structure_classname, ! empty( $row_structure_classname ) );

			$section_children = HTMLUtility::render(
				[
					'tag'               => 'div',
					'attributes'        => [
						'class' => $specialty_row_classnames->value(),
					],
					'children'          => $content,
					'childrenSanitizer' => 'et_core_esc_previously',
				]
			);
		} else {
			$section_children = $content;
		}

		// Page settings.
		$page_settings = [];

		// Parent Attributes.
		$parent_attrs = [];

		// Sibling Attributes.
		$sibling_attrs = [
			'previous' => [],
			'next'     => [],
		];

		// Check if module is a section.
		$is_section = 'divi/section' === $block->block_type->name;

		// Check if any dividers are enabled.
		$module_has_dividers = isset( $attrs['module']['advanced']['dividers'] ) && DividersUtils::has_divider( $attrs['module']['advanced']['dividers'] );

		// Check if module is a section and has dividers.
		$section_has_dividers = $is_section && $module_has_dividers;

		// Get the parsed block ID.
		$parsed_block_id = $block->parsed_block['id'];

		/**
		 * Filters to get the sibling_attrs.
		 *
		 * @since ??
		 *
		 * @param boolean $section_has_dividers Whether module has dividers or not.
		 * @param array   $block_attributes     Block attributes to be rendered.
		 * @param array   $parsed_block_id      Block ID.
		 */
		$has_sibling_attrs = apply_filters(
			'divi_module_library_section_has_sibling_attrs',
			$section_has_dividers,
			$attrs,
			$parsed_block_id
		);

		// Get immediate siblings.
		if ( $has_sibling_attrs ) {
			// Get the previous item, if it exists.
			$previous_sibling = BlockParserStore::get_sibling( $block->parsed_block['id'], 'before', $block->parsed_block['storeInstance'] );

			// Get the next item, if it exists.
			$next_sibling = BlockParserStore::get_sibling( $block->parsed_block['id'], 'after', $block->parsed_block['storeInstance'] );

			// Get the page settings.
			$page_settings = Settings::get_settings_values();
			$post_content  = $page_settings['post_content'] ?? [];

			// Capture the page-wide Section Background Color (if set).
			$page_section_background_color = $post_content['et_pb_section_background_color'] ?? null;

			// Update the parent and sibling attributes.
			if ( $previous_sibling ) {
				$sibling_attrs['previous']['background'] = $previous_sibling->attrs['module']['decoration']['background'] ?? null;
			}
			if ( $next_sibling ) {
				$sibling_attrs['next']['background'] = $next_sibling->attrs['module']['decoration']['background'] ?? null;
			}
			if ( $page_section_background_color ) {
				$parent_attrs['section_background_color'] = $page_section_background_color;
			}
		}

		$preset_attrs = GlobalPreset::get_selected_preset(
			[
				'moduleName'  => $block->block_type->name,
				'moduleAttrs' => $attrs ?? [],
			]
		)->get_data_attrs();

		// Merge dividers attributes with preset attributes to get the top dividers element is rendered.
		$dividers_attrs_top = array_replace_recursive(
			[],
			$preset_attrs['module']['advanced']['dividers']['top'] ?? [],
			$attrs['module']['advanced']['dividers']['top'] ?? []
		);

		// Merge dividers attributes with preset attributes to get the bottom dividers element is rendered.
		$dividers_attrs_bottom = array_replace_recursive(
			[],
			$preset_attrs['module']['advanced']['dividers']['bottom'] ?? [],
			$attrs['module']['advanced']['dividers']['bottom'] ?? []
		);

		$output = Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'                    => $attrs,
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'id'                       => $parsed_block_id,
				'childrenIds'              => $children_ids,
				'name'                     => $block->block_type->name,
				'parentAttrs'              => $parent_attrs,
				'siblingAttrs'             => $sibling_attrs,
				'page_settings'            => $page_settings,
				'classnamesFunction'       => function ( array $classnames_function_params ) use ( $preset_attrs, $is_specialty ) {
					SectionModule::module_classnames(
						array_merge(
							$classnames_function_params,
							[
								// Merge attributes with preset attributes to determine dividers and gutters classnames.
								'attrs' => array_replace_recursive(
									[],
									[
										'module' => [
											'advanced' => [
												'dividers' => $preset_attrs['module']['advanced']['dividers'] ?? [],
												'gutter'   => $is_specialty ? ( $preset_attrs['module']['advanced']['gutter'] ?? [] ) : [],
											],
										],
									],
									$classnames_function_params['attrs']
								),
							]
						)
					);
				},
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => function ( array $styles_component_params ) use ( $preset_attrs ) {
					if ( 'preset' === $styles_component_params['styleGroup'] ) {
						SectionModule::module_styles( $styles_component_params );
					} else {
						SectionModule::module_styles(
							array_merge(
								$styles_component_params,
								[
									// Merge the dividers attributes to get the dividers styles rendered properly in the module.
									// Dividers styles need multiple attributes to be rendered properly. So needs to set the preset styles
									// to behave as default attributes.
									'attrs' => array_replace_recursive(
										[],
										[
											'module' => [
												'advanced' => [
													'dividers' => $preset_attrs['module']['advanced']['dividers'] ?? [],
												],
											],
										],
										$styles_component_params['attrs']
									),
								]
							)
						);
					}
				},
				'moduleCategory'           => $block->block_type->category,
				'children'                 => DividersComponent::container(
					[
						'placement'     => 'top',
						'attr'          => $dividers_attrs_top,
						'id'            => $block->parsed_block['id'],
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				) . $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $section_children . DividersComponent::container(
					[
						'placement'     => 'bottom',
						'attr'          => $dividers_attrs_bottom,
						'id'            => $parsed_block_id,
						'orderIndex'    => $block->parsed_block['orderIndex'],
						'storeInstance' => $block->parsed_block['storeInstance'],
					]
				),
			]
		);

		return $output;
	}

	/**
	 * Retrieve the style declaration for the box shadow of a section.
	 *
	 * This function is used to generate the style declaration for the box shadow
	 * of a Section.It takes an array of parameters
	 * that include the attribute value for the style attribute of the section.
	 * The function determines the `box shadow style` based on the given attribute value,
	 * and also considers the position attribute in case it is available.
	 * If the `box shadow style` is outer, it adds a` z-index` of `10` to the style declarations.
	 * Finally, it returns the generated style declarations as a string.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of parameters.
	 *
	 *     @type array $attrValue {
	 *         The attribute value for the style attribute of the section.
	 *
	 *         @type string $style    The box shadow style.
	 *     `   @type string $position The box shadow position.
	 *     }
	 * }
	 * @return string The generated style declarations.
	 *
	 * @example:
	 * ```php
	 *     $params = [
	 *         'attrValue' => [
	 *             'style'    => 'none',
	 *             'position' => 'outer',
	 *         ],
	 *     ];
	 *     $styleDeclaration = SectionModule::section_box_shadow_style_declaration( $params );
	 * ```
	 */
	public static function section_box_shadow_style_declaration( array $params ) {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$style_attr = $params['attrValue'];
		$presets    = BoxShadow::presets();

		// Get box shadow style.
		$style = $style_attr['style'] ?? 'none';

		// Position attribute only available in desktop mode.
		$position = $style_attr['position'] ?? $presets[ $style ]['position'] ?? null;

		$is_outer_box_shadow = isset( $presets[ $style ] ) && 'outer' === $position;

		if ( $is_outer_box_shadow ) {
			$style_declarations->add( 'z-index', '10' );
		}

		return $style_declarations->value();
	}

	/**
	 * Get the border style declaration for a Section.
	 *
	 * This function takes an array of parameters and generates the border style declaration based on the provided attributes.
	 * The generated declaration is then returned as a string.
	 * If the `'radius'` attribute is empty, the `'overflow'` style declaration will not be added to the returned result.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of parameters.
	 *
	 *     @type array $attrValue {
	 *         An array containing the attributes for the section.
	 *
	 *         @type string $radius The border radius.
	 *     }
	 * }
	 *
	 * @return string The border style declaration.
	 *
	 * @example:
	 * ```php
	 *     $params = [
	 *         'attrValue' => [
	 *             'radius' => '10px',
	 *         ],
	 *     ];
	 *     $overflow_style_declaration = SectionModule::overflow_style_declaration( $params );
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
	 * Load the Section module.
	 *
	 * This function registers the Section module and sets the render callback via WordPress `init` action hook.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/section/';

		add_filter( 'divi_conversion_presets_attrs_map', array( SectionPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
