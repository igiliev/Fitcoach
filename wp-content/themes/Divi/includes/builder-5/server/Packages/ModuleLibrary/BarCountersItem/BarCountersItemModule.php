<?php
/**
 * Module Library: Bar Counters Item Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\BarCountersItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Script;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use WP_Block_Type_Registry;
use WP_Block;

/**
 * BarCountersItemModule class.
 *
 * This class implements the functionality of a bar counters item component in a
 * frontend application. It provides functions for rendering the bar counters
 * item, managing REST API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class BarCountersItemModule implements DependencyInterface {

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Bar
	 * Counters Item module.
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
	 *   'classnamesInstance' => $classnamesInstance,
	 *   'attrs' => $attrs,
	 * ];
	 *
	 * BarCountersItemModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];
		$parent_attrs        = $args['parentAttrs'] ?? [];

		// Text Options classnames.
		$text_options_classnames = TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] );

		if ( $text_options_classnames ) {
			$classnames_instance->add( $text_options_classnames, true );
		}
		$border_attrs = ! isset( $attrs['barCounter']['decoration']['border'] ) && isset( $parent_attrs['barCounter']['decoration']['border'] )
			? $parent_attrs['barCounter']['decoration']['border']
			: $attrs['barCounter']['decoration']['border'] ?? [];

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						[],
						[
							'link' => $attrs['module']['advanced']['link'] ?? [],
						],
						[
							'border' => $border_attrs,
						],
						$attrs['module']['decoration'] ?? []
					),
				]
			)
		);
	}

	/**
	 * Render callback for the Bar Counters Item module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ BarCountersItemEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 * @param array          $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return string The HTML rendered output of the Bar Counters Item module.
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
	 * BarCountersItemModule::render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		$default_parent_attrs = ModuleRegistration::get_default_attrs( 'divi/counters' );
		$parent_attrs         = array_replace_recursive( $default_parent_attrs, $parent->attrs ?? [] );

		// Counter Title.
		$counter_title = $elements->render(
			[
				'attrName'      => 'title',
				'hoverSelector' => '.' . ModuleUtils::get_module_class_by_name( $parent->id ),
			]
		);

		// Counter Number Inner.
		$counter_amount_number_inner = $elements->render(
			[
				'tagName'    => 'span',
				'attributes' => [
					'class' => 'et_pb_counter_amount_number_inner',
				],
				'children'   => [
					'selector'      => '{{selector}} .et_pb_counter_amount_number_inner',
					'attr'          => MultiViewUtils::merge_values( // Note since we need multiple attributes, we use MultiViewUtils::merge_values to merge the attributes as one.
						[
							'percent'        => $attrs['barProgress']['innerContent'] ?? [],
							'usePercentages' => $parent->attrs['barProgress']['advanced']['usePercentages'] ?? [],
						]
					),
					'valueResolver' => function( $value ) {
						$percent             = $value['percent'] ?? '';
						$use_percentages     = $value['usePercentages'] ?? 'on';
						$percent_with_symbol = $percent ?? '0';

						// Add % only if it hasn't been added to the percent value.
						if ( '%' !== substr( trim( $percent_with_symbol ), -1 ) ) {
							$percent_with_symbol = $percent_with_symbol . '%';
						}

						return 'on' === $use_percentages ? $percent_with_symbol : '';
					},
				],
			]
		);

		$counter_amount_number = HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => 'et_pb_counter_amount_number',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $counter_amount_number_inner,
			]
		);

		// Counter Amount.
		$counter_amount = $elements->render(
			[
				'tagName'           => 'span',
				'attributes'        => [
					'class'      => 'et_pb_counter_amount',
					'data-width' => [
						'selector'      => '{{selector}} .et_pb_counter_amount',
						'attr'          => $attrs['barProgress']['innerContent'] ?? [],
						'valueResolver' => function( $percent ) {
							// Add % only if it hasn't been added to the percent value.
							if ( '%' !== substr( trim( $percent ), -1 ) ) {
								$percent .= '%';
							}

							return $percent;
						},
					],
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $counter_amount_number,
			]
		);

		$counter_amount_overlay = $elements->render(
			[
				'tagName'           => 'span',
				'attributes'        => [
					'class'      => 'et_pb_counter_amount overlay',
					'data-width' => [
						'selector'      => '{{selector}} .et_pb_counter_amount .overlay',
						'attr'          => $attrs['barProgress']['innerContent'] ?? [],
						'valueResolver' => function( $percent ) {
							// Add % only if it hasn't been added to the percent value.
							if ( '%' !== substr( trim( $percent ), -1 ) ) {
								$percent .= '%';
							}

							return $percent;
						},
					],
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $counter_amount_number,
			]
		);

		// Counter container.
		$counter_container = HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_counter_container' => true,
						],
						BoxShadowClassnames::has_overlay( $attrs['barCounter']['decoration']['boxShadow'] ?? [] )
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $elements->style_components(
					[
						'attrName' => 'barCounter',
					]
				) . $counter_amount . $counter_amount_overlay,
			]
		);

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'attrs'               => $attrs,
				'elements'            => $elements,
				'tag'                 => 'li',
				'hasModuleClassName'  => false,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'parentAttrs'         => $parent_attrs,
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $counter_title . $counter_container,
			]
		);
	}

	/**
	 * Get the custom CSS fields for the Divi Bar Counters Item module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi bar counters item module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the bar counters item module.
	 * ```
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/counter' )->customCssFields;
	}

	/**
	 * Bar Width style declaration.
	 *
	 * This function is used to declare the width style for a Bar Counters Item.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string The value of the style declaration based on the `returnType` parameter.
	 *
	 * @example
	 * ```php
	 * BarWidthStyle::bar_width_style_declaration( [
	 *   'attrValue'  => 75,
	 * ] );
	 *
	 * // Returns: 'width:75%;'
	 * ```
	 */
	public static function bar_width_style_declaration( array $params ): string {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
			]
		);

		if ( isset( $params['attrValue'] ) ) {
			$percent = $params['attrValue'] ?? '0';

			// Add % only if it hasn't been added to the percent value.
			if ( '%' !== substr( trim( $percent ), -1 ) ) {
				$percent = $percent . '%';
			}

			$style_declarations->add( 'width', $percent );
		}

		return $style_declarations->value();
	}

	/**
	 * Bar Overlay Color style declaration.
	 *
	 * This function is used to declare the overlay color style for a Bar Counters Item.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array $attrValue {
	 *         The value (breakpoint > state > value) of the module attribute.
	 *
	 *         @type string $color The color of the overlay.
	 *     }
	 * }
	 *
	 * @return string The value of the style declaration.
	 *
	 * @example
	 * ```php
	 * $params = [
	 *   'attrValue'  => [
	 *     'color' => '#FF0000',
	 *   ],
	 * ];
	 * BarCountersItem::bar_overlay_color_style_declaration( $params );
	 * ```
	 */
	public static function bar_overlay_color_style_declaration( array $params ): string {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
			]
		);

		if ( isset( $params['attrValue']['color'] ) ) {
			$style_declarations->add( 'background-color', $params['attrValue']['color'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Alignment Style Declaration.
	 *
	 * This function is used to declare the margin styles used to align a Bar Counters Item.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array $attrValue The value (breakpoint > state > value) of the module attribute.
	 * }
	 *
	 * @return string The style declarations as a string.
	 *
	 * @example: Declare content alignment style for BarCountersItem module.
	 * ```php
	 * $params = [
	 *   'attrValue' => [
	 *     'alignment' => 'left',
	 *     // ... other attributes
	 *   ],
	 * ];
	 * $style = BarCountersItem::alignment_style_declaration($params);
	 *
	 * echo $style;
	 *
	 * // Result: 'margin-left: 0 !important; margin-right: auto !important;'
	 * ```
	 */
	public static function alignment_style_declaration( array $params ): string {
		$alignment = $params['attrValue']['alignment'] ?? '';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => true,
			]
		);

		if ( ! empty( $alignment ) ) {
			switch ( $alignment ) {
				case 'left':
					$style_declarations->add( 'margin-left', '0' );
					$style_declarations->add( 'margin-right', 'auto' );
					break;
				case 'center':
					$style_declarations->add( 'margin-left', 'auto' );
					$style_declarations->add( 'margin-right', 'auto' );
					break;
				case 'right':
					$style_declarations->add( 'margin-left', 'auto' );
					$style_declarations->add( 'margin-right', '0' );
					break;
				default:
					break;
			}
		}

		return $style_declarations->value();
	}

	/**
	 * BarCountersItem Module's style components.
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
	 *     @type string         $id                The module ID. In VB, the ID of the module is UUIDV4.
	 *                                             In the frontend, the ID is the order index.
	 *     @type string         $name              The module name.
	 *     @type array          $attrs             The module attributes.
	 *     @type array          $parentAttrs       The parent attrs.
	 *     @type string         $orderClass        The selector class name.
	 *     @type string         $parentOrderClass  The parent selector class name.
	 *     @type string         $wrapperOrderClass The wrapper selector class name.
	 *     @type array          $settings          The custom settings.
	 *     @type string         $state             The attributes state.
	 *     @type string         $mode              The style mode.
	 *     @type ModuleElements $elements          An instance of ModuleElements.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * BarCountersItem::module_styles( [
	 *   'id'                => 'module-id',
	 *   'name'              => 'module-name',
	 *   'attrs'             => [],
	 *   'parentAttrs'       => [],
	 *   'orderClass'        => 'selector-class',
	 *   'parentOrderClass'  => 'parent-selector-class',
	 *   'wrapperOrderClass' => 'wrapper-selector-class',
	 *   'settings'          => [],
	 *   'state'             => 'attributes-state',
	 *   'mode'              => 'style-mode',
	 *   'elements'          => ModuleElements::instance(),
	 * ] );
	 * ```
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		$base_order_class = $args['baseOrderClass'] ?? '';
		$selector_prefix  = $args['selectorPrefix'] ?? '';

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
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} .et_pb_counter_title",
													"{$args['orderClass']} .et_pb_counter_amount",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
											'propertySelectors' => [
												'text' => [
													'desktop' => [
														'value' => [
															'text-align' => implode(
																', ',
																[
																	"{$args['orderClass']} .et_pb_counter_title",
																	"{$args['orderClass']} .et_pb_counter_amount",
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

					// Counter Container.
					$elements->style(
						[
							'attrName'   => 'barCounter',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$selector_prefix}.et_pb_counters {$base_order_class}",
											'attr'     => $attrs['barCounter']['decoration']['sizing'] ?? [],
											'declarationFunction' => [ self::class, 'alignment_style_declaration' ],
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

					// Content.
					$elements->style(
						[
							'attrName'   => 'barProgress',
							'styleProps' => [
								'advancedStyles' => [
									// Declare `bar_width_style_declaration` here to ensure `width` transition property process correctly.
									// Inline `style={{ width: percentValueWithSymbol }}` can't be removed, as bar width won't work on hover.
									// Hence, the `width` style declaration must be set in both places.
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']} .et_pb_counter_container .et_pb_counter_amount",
											'attr'     => $attrs['barProgress']['innerContent'] ?? [],
											'declarationFunction' => [ self::class, 'bar_width_style_declaration' ],
										],
									],
									// Declare `barOverlayColorStyleDeclaration` here to ensure `background-color` transition property process
									// correctly. This is needed to ensure the `background-color` will be grouped together with `width` in the
									// same `transition-property` with the same selector. This causes duplicate `background-color` rendered.
									// TODO fix(D5, Transition Styles) Will be fixed separately @see https://github.com/elegantthemes/Divi/issues/39774.
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']} .et_pb_counter_container .et_pb_counter_amount",
											'attr'     => $attrs['barProgress']['decoration']['background'] ?? [],
											'declarationFunction' => [
												self::class,
												'bar_overlay_color_style_declaration',
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
	 * Bar Counters module script data.
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
	 * @example: Generate the script data for a module with specific arguments.
	 * ```php
	 * $args = array(
	 *   'id'             => 'my-module',
	 *   'name'           => 'My Module',
	 *   'selector'       => '.my-module',
	 *   'attrs'          => array(
	 *     'portfolio' => array(
	 *       'advanced' => array(
	 *         'showTitle'       => false,
	 *         'showCategories'  => true,
	 *         'showPagination' => true,
	 *       )
	 *     )
	 *   ),
	 *   'elements'       => $elements,
	 *   'store_instance' => 123,
	 * );
	 *
	 * BarCountersItemModule::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( array $args ): void {
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
				'selector'      => $selector,
				'hoverSelector' => $selector,
				'setStyle'      => [
					[
						'selector'      => $selector . ' .et_pb_counter_amount',
						'data'          => [
							'width' => $attrs['barProgress']['innerContent'] ?? [],
						],
						'valueResolver' => function( $value ) {
							$percent = $value ?? '0';

							// Add % only if it hasn't been added to the percent value.
							if ( '%' !== substr( trim( $percent ), -1 ) ) {
								$percent = $percent . '%';
							}

							return $percent;
						},
						'sanitizer'     => 'esc_attr',
					],
				],
			]
		);
	}

	/**
	 * Load the Bar Counters Item module.
	 *
	 * This function is responsible for loading the `BarCountersItem` class and
	 * registering the Frontend render callback and REST API endpoints for it.
	 * It ensures that the necessary dependencies are available and sets up the
	 * required functionality for the class to work properly.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/counter/';

		add_filter( 'divi_conversion_presets_attrs_map', array( BarCountersItemPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
