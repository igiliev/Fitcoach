<?php
/**
 * ModuleLibrary: Tabs Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Conditions\ConditionsRenderer;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Sticky\StickyUtils;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleLibrary\Tabs\TabsPresetAttrsMap;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use WP_Block_Type_Registry;
use WP_Block;


/**
 * TabsModule class.
 *
 * This class contains functions used for Tabs Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class TabsModule implements DependencyInterface {

	/**
	 * Get the module classnames for the Tabs module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/tabs-module-classnames moduleClassnames}
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
	 * @example:
	 * ```php
	 * // Example 1: Adding classnames for the toggle options.
	 * TabsModule::module_classnames( [
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
	 * TabsModule::module_classnames( [
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

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
					'attrs' => array_merge(
						$args['attrs']['module']['decoration'] ?? [],
						[
							'link' => $args['attrs']['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	/**
	 * Generate the script data for the Tabs module based on the provided arguments.
	 *
	 * This function assigns variables and sets element script data options.
	 * It then uses `MultiViewScriptData` to set module specific FrontEnd (FE) data.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments for generating the script data.
	 *
	 *     @type ModuleElements $elements      ModuleElements instance.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * // Generate the script data for a module with specific arguments.
	 * $args = array(
	 *     'elements'       => $elements,
	 * );
	 *
	 * Tabs::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( array $args ): void {
		// Assign attributes.
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}

	/**
	 * Get the custom CSS fields for the `'divi/tabs'` block type.
	 *
	 * This function retrieves the custom CSS fields for the `'divi/tabs'` block type.
	 *
	 * These fields are equivalent to the JS constant:
	 * {@link /docs/builder-api/js/module-library/css-fields cssFields}
	 * located in `@divi/module-library` package.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 * @return array An array of custom CSS fields for the `'divi/tabs'` block type.
	 *
	 * @example:
	 * ```php
	 * $customCssFields = DiviTabsModule::custom_css();
	 *
	 * // Example output: [
	 * //     [
	 * //         'name'  => 'background_color',
	 * //         'label' => 'Background Color',
	 * //         'type'  => 'color',
	 * //     ],
	 * //     [
	 * //         'name'  => 'text_color',
	 * //         'label' => 'Text Color',
	 * //         'type'  => 'color',
	 * //     ],
	 * //     ...
	 * //]
	 * ```
	 */
	public static function custom_css(): array {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/tabs' )->customCssFields;
	}

	/**
	 * Get the style declaration for overflow attribute.
	 *
	 * This function gets the style declaration for the overflow attribute based on the provided parameters.
	 * If the 'radius' parameter is set and not empty, the style declaration will include 'overflow: hidden'.
	 *
	 * @param array $params {
	 *     An array of parameters.
	 *
	 *     @type array $attrValue {
	 *         An array of attribute values.
	 *
	 *         @type string $radius The radius value.
	 *    }
	 * }
	 *
	 * @return string The style declaration for the overflow attribute.
	 *
	 * @example:
	 * ```php
	 *   $params = [
	 *       'attrValue' => [
	 *           'radius' => '10px',
	 *       ],
	 *   ];
	 *   $styleDeclaration = overflow_style_declaration( $params );
	 *
	 *   // Output: 'overflow: hidden;'
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
	 * Load Tabs module style components.
	 *
	 * This function is responsible for loading styles for the module. It takes an array of arguments
	 * which includes the module ID, name, attributes, settings, and other details. The function then
	 * uses these arguments to dynamically generate and add the required styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/tabs-module-styles ModuleStyles}
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
	 *     @type array          $elements                 The module elements.
	 *     @type array          $settings                 Optional. The module settings. Default `[]`.
	 *     @type string         $orderClass               The selector class name.
	 *     @type int            $orderIndex               The order index of the module.
	 *     @type int            $storeInstance            The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     TabsModule::module_styles([
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
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		// Main tab wrapper class.
		$main_selector = "{$args['orderClass']}.et_pb_tabs";

		// Get Box Shadow position.
		$box_shadow_position = $attrs['module']['decoration']['boxShadow']['desktop']['value']['position'] ?? '';
		$is_inner_box_shadow = 'inner' === $box_shadow_position;

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
								'boxShadow'      => [
									'selector' => $is_inner_box_shadow ? implode(
										',',
										[
											"{$args['orderClass']} .et-pb-active-slide",
											"{$args['orderClass']} .et_pb_active_content",
										]
									) : $args['orderClass'],
								],
								'disabledOn'     => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $main_selector,
											'attr'     => $attrs['module']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
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
					// All Tabs Content.
					$elements->style(
						[
							'attrName'   => 'content',
							'styleProps' => [
								'background' => [
									'featureSelectors' => [
										'mask'    => [
											'desktop' => [
												'value' => "{$main_selector} > .et_pb_background_mask",
											],
										],
										'pattern' => [
											'desktop' => [
												'value' => "{$main_selector} > .et_pb_background_pattern",
											],
										],
									],
								],
							],
						]
					),
					// Active Tab.
					$elements->style(
						[
							'attrName' => 'activeTab',
						]
					),
					// Inactive Tab.
					$elements->style(
						[
							'attrName' => 'inactiveTab',
						]
					),
					// Title.
					$elements->style(
						[
							'attrName' => 'tab',
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
	 * Render callback function for the Tabs module.
	 *
	 * This function generates HTML for rendering on the FrontEnd (FE).
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       The attributes passed to the block.
	 * @param string         $content                     The content inside the block.
	 * @param WP_Block       $block                       The parsed block object.
	 * @param ModuleElements $elements                    The elements object containing style components.
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
	 * $rendered_content = TabsModule::render_callback($attrs, $content, $block, $elements);
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
	 * $rendered_content = TabsModule::render_callback($attrs, $content, $block, $elements);
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {
		$inner_blocks = $block->parsed_block['innerBlocks'] ?? [];
		$children_ids = $inner_blocks ? array_map(
			function ( $inner_block ) {
				return $inner_block['id'];
			},
			$inner_blocks
		) : [];

		$tabs_controls_rendered = '';
		$all_tabs_rendered      = '';
		$is_first               = true; // Initialize before loop.

		foreach ( $inner_blocks as $key => $inner_block ) {
			$inner_block_attrs = $inner_block['attrs'] ?? [];

			$child_block_instance = new \WP_Block( $inner_block );
			// First parameter `Tab` is for the fake block_content, if condition doesn't meet, it will return empty string.
			// We need to check ConditionsRenderer::should_render() to ensure the child block is renderable.
			// If the child block is not renderable, we need to skip the Tab title rendering process.
			if ( ! ConditionsRenderer::should_render( true, $child_block_instance, $inner_block_attrs ) ) {
				continue;
			}

			if ( isset( $inner_block_attrs['title'] ) || isset( $inner_block_attrs['content'] ) ) {
				// The `title` attribute need to be exists when `content` attribute exists,
				// hence we need to inject it for tabs to work properly.
				if ( ! isset( $inner_block_attrs['title'] ) ) {
					$inner_block_attrs['title'] = [
						'innerContent' => [
							'desktop' => [
								'value' => '',
							],
						],
					];
				}

				// The `content` attribute need to be exists when `title` attribute exists,
				// hence we need to inject it for tabs to work properly.
				if ( ! isset( $inner_block_attrs['content'] ) ) {
					$inner_block_attrs['content'] = [
						'innerContent' => [
							'desktop' => [
								'value' => '',
							],
						],
					];
				}
			}

			// Get Inner Block Type.
			$inner_block_type = WP_Block_Type_Registry::get_instance()->get_registered( $inner_block['blockName'] );

			// Check whether the current module is inside another sticky module or not. The FE
			// implementation is bit different than VB where we use store related function due
			// to we need access to the store instance to get all blocks. Meanwhile in FE, we
			// can directly check all blocks from the parsed block.
			$is_inside_sticky_module = StickyUtils::is_inside_sticky_module(
				$inner_block['id'],
				BlockParserStore::get_all( $inner_block['storeInstance'] )
			);

			// Create instance of module elements so element can be rendered consistently.
			$child_element = new ModuleElements(
				[
					'id'                   => $inner_block['id'],
					'name'                 => $inner_block['blockName'],
					'moduleAttrs'          => $inner_block_attrs,
					'moduleMetadata'       => $inner_block_type,
					'orderIndex'           => $inner_block['orderIndex'],
					'storeInstance'        => $inner_block['storeInstance'],
					'isInsideStickyModule' => $is_inside_sticky_module,
				]
			);

			// Title.
			$title = $child_element->render(
				[
					'attrName'          => 'title',
					'attributes'        => [
						'class' => 'et_pb_tab_nav_item_link',
						'href'  => '#',
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'valueResolver'     => function ( $value ) {
						return '' === ( $value ?? '' ) ? esc_html__( 'Tab', 'et_builder' ) : $value;
					},
					'hoverSelector'     => '{{parentSelector}}',
				]
			);

			$title_render = HTMLUtility::render(
				[
					'tag'               => 'li',
					'attributes'        => [
						'class' => HTMLUtility::classnames(
							[
								"et_pb_tab_nav_item_{$inner_block['orderIndex']}" => true,
								'et_pb_tab_nav_item' => true,
								"et_pb_tab_{$inner_block['orderIndex']}" => true,
								'et_pb_tab_active'   => $is_first,
							]
						),
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => $title,
				]
			);

			$tabs_controls_rendered .= $title_render;

			// Now, $is_first will be true only for the first renderable block.
			if ( $is_first ) {
				$is_first = false; // Reset after the first valid block.
			}
		}

		// If the block has no children, return empty string.
		if ( empty( $tabs_controls_rendered ) ) {
			return '';
		}

		$tabs_controls_rendered = HTMLUtility::render(
			[
				'tag'               => 'ul',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_tabs_controls' => true,
							'clearfix'            => true,
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $tabs_controls_rendered,
			]
		);

		// Render all child tab elements.
		$all_tabs_rendered = ! empty( trim( $content ) ) ? HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_all_tabs' => true,
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $content,
			]
		) : '';

		return Module::render(
			[
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],
				'attrs'               => $attrs,
				'id'                  => $block->parsed_block['id'],
				'elements'            => $elements,
				'name'                => $block->block_type->name,
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'cssPosition'         => 'before',
				'childrenIds'         => $children_ids,
				'children'            => [
					$elements->style_components(
						[
							'attrName' => 'module',
						]
					),
					$elements->style_components(
						[
							'attrName' => 'content',
						]
					),
					$tabs_controls_rendered,
					$all_tabs_rendered,
				],
			]
		);
	}

	/**
	 * Load the module by registering it with the specified render callback.
	 *
	 * This function registers the module by adding an WordPress action hook on 'init'.
	 * The action hook calls `ModuleRegistration::register_module()`,
	 * passing the module JSON folder path and the render callback function as arguments.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/tabs/';

		add_filter( 'divi_conversion_presets_attrs_map', array( TabsPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
