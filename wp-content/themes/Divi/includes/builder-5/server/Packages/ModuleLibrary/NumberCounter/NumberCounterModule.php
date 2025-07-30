<?php
/**
 * ModuleLibrary: Number Counter Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\NumberCounter;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Script;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block;

/**
 * NumberCounterModule class.
 *
 * This class contains functions used by NumberCounter Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class NumberCounterModule implements DependencyInterface {

	/**
	 * Get the module classnames for the NumberCounter module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/number-counter-module-classnames moduleClassnames} located in `@divi/module-library` package.
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
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

		// Title.
		$title = $attrs['title']['innerContent']['desktop']['value'] ?? '';
		$classnames_instance->add( 'et_pb_with_title', ! empty( $title ) );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $args['attrs']['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	/**
	 * Get the number value data for the CircleCounter module.
	 *
	 * This function takes an array of module attributes and populates the `numberValue` data
	 * based on the provided attributes. It iterates through the attributes and retrieves the
	 * corresponding values using the `ModuleUtils::use_attr_value()` function. The retrieved
	 * values are then assigned to the `numberValue` data array.
	 *
	 * @since ??
	 *
	 * @param array $attrs The `number` module attributes.
	 *
	 * @return array The `numberValue` data array.
	 *
	 * @example:
	 * ```php
	 *      $attrs = [
	 *          'breakpoint1' => [
	 *              'state1' => 'value1',
	 *              'state2' => 'value2',
	 *          ],
	 *          'breakpoint2' => [
	 *              'state1' => 'value3',
	 *              'state2' => 'value4',
	 *          ],
	 *      ];
	 *      data_number_value($attrs);
	 *
	 *      // Output:
	 *      // [
	 *      //     'breakpoint1' => [
	 *      //         'state1' => 'value1',
	 *      //         'state2' => 'value2',
	 *      //     ],
	 *      //     'breakpoint2' => [
	 *      //         'state1' => 'value3',
	 *      //         'state2' => 'value4',
	 *      //     ],
	 *      // ]
	 * ```
	 */
	public static function data_number_value( array $attrs ): array {
		$attr_value = [];

		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$value = ModuleUtils::use_attr_value(
					[
						'attr'         => $attrs,
						'breakpoint'   => $breakpoint,
						'state'        => $state,
						'mode'         => 'getAndInheritAll',
						'defaultValue' => '0',
					]
				);

				$attr_value[ $breakpoint ][ $state ] = ! empty( $value ) ? $value : '0';
			}
		}

		return $attr_value;
	}

	/**
	 * Set Front End (FE) data for a specific module.
	 *
	 * This function sets front-end data for a specific module based on the provided arguments.
	 * It adds the data to the ScriptData class using the `add_data_item()` method.
	 * If the Virtual Builder (VB) is enabled, the function returns immediately without processing the data.
	 * The front-end data item consists of the module selector and the corresponding data.
	 * The data includes the number value, which is derived from the `number` attribute's inner content,
	 * using the `data_number_value()` method.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $selector        The module selector.
	 *     @type array  $attrs           The module attributes.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     // Example usage:
	 *     $args = [
	 *         'selector' => '.my-module',
	 *         'attrs'    => [
	 *             'number' => [
	 *                 'innerContent' => [1, 2, 3],
	 *             ],
	 *         ],
	 *     ];
	 *     set_front_end_data($args);
	 *
	 *     // This will add the following data item to the ScriptData class:
	 *     ScriptData::add_data_item(
	 *         [
	 *             'data_name'    => 'number_counter',
	 *             'data_item_id' => null,
	 *             'data_item'    => [
	 *                 'selector' => '.my-module',
	 *                 'data'     => [
	 *                     'numberValue' => self::data_number_value([1, 2, 3]),
	 *                 ],
	 *             ],
	 *         ]
	 *     );
	 * ```
	 */
	public static function set_front_end_data( array $args ): void {
		// Script data is not needed in VB.
		if ( Conditions::is_vb_enabled() ) {
			return;
		}

		$selector = $args['selector'] ?? '';
		$attrs    = $args['attrs'] ?? [];

		if ( ! $selector || ! $attrs ) {
			return;
		}

		// Register front-end data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'number_counter',
				'data_item_id' => null,
				'data_item'    => [
					'selector' => $selector,
					'data'     => [
						'numberValue' => self::data_number_value( $attrs['number']['innerContent'] ?? [] ),
					],
				],
			]
		);
	}

	/**
	 * Generate NumberCounter module script data.
	 *
	 * This function generates the script data for a NumberCounter module with.
	 * The script data is used for FrontEnd (FE) rendering and interaction of the module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. Array of arguments for generating the module script data.
	 *
	 *     @type string  $id             Optional. The ID of the module. Default empty string.
	 *     @type string  $name           Optional. The name of the module. Default empty string.
	 *     @type string  $selector       Optional. The selector of the module. Default empty string.
	 *     @type array   $attrs          Optional. The attributes of the module. Default `[]`.
	 *     @type object  $elements       The elements object.
	 *     @type integer $store_instance Optional. The ID of instance where this block is stored in BlockParserStore. Default `null`.
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
	 *     'storeInstance' => 1,
	 * );
	 *
	 * NumberCounterModule::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( $args ) {
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

		// Set module specific front-end data.
		self::set_front_end_data(
			[
				'selector' => $selector,
				'attrs'    => $attrs,
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
							'et_pb_with_title' => $args['attrs']['title']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return ! ( '' === ( $value ?? '' ) ) ? 'add' : 'remove';
						},
					],
				],
				'setContent'    => [
					[
						'selector'      => "$selector .title",
						'data'          => $args['attrs']['title']['innerContent'] ?? [],
						'valueResolver' => function ( $value ) {
							return $value ?? '';
						},
					],
					[
						'selector'      => "$selector .percent .percent-sign",
						'data'          => $attrs['number']['advanced']['enablePercentSign'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? '%' : '';
						},
					],
				],
			]
		);
	}

	/**
	 * Render callback for the NumberCounter module.
	 *
	 * Generates the HTML output for the NumberCounter module, including the percent value, percent symbol,
	 * percent wrapper, title, and other necessary components.
	 * This HTML is then rendered on the FrontEnd (FE).
	 *
	 * @since ??
	 *
	 * @param array          $attrs    The block attributes.
	 * @param string         $content  The block content.
	 * @param WP_Block       $block    The block object.
	 * @param ModuleElements $elements The elements object.
	 *
	 * @return string The rendered HTML output.
	 *
	 * @example:
	 * ```php
	 * $attrs = [
	 *     'number' => [
	 *         'advanced' => [
	 *             'enablePercentSign' => [
	 *                 'desktop' => [
	 *                     'value' => 'on',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     // Other attributes...
	 * ];
	 * $content = 'Block content';
	 * $result = NumberCounter::render_callback( $attrs, $content, $block, $elements );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ) {

		// Percent Value.
		$percent_value = HTMLUtility::render(
			[
				'tag'        => 'span',
				'attributes' => [
					'class' => 'percent-value',
				],
			]
		);

		// Percent Symbol.
		$percent_sign_value = $attrs['number']['advanced']['enablePercentSign']['desktop']['value'] ?? 'on';

		$percent_sign = HTMLUtility::render(
			[
				'tag'        => 'span',
				'attributes' => [
					'class' => 'percent-sign',
				],
				'children'   => 'on' === $percent_sign_value ? '%' : '',
			]
		);

		$percent_container = HTMLUtility::render(
			[
				'tag'               => 'p',
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $percent_value . $percent_sign,
			]
		);

		// Percent Wrapper.
		$percent_wrapper = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'percent',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $percent_container,
			]
		);

		// Title.
		$title = $elements->render(
			[
				'attrName' => 'title',
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
				) . $percent_wrapper . $title,
			]
		);
	}

	/**
	 * Retrieve the custom CSS fields for the `divi/number-counter block type.
	 *
	 * This function utilizes the `WP_Block_Type_Registry` class to retrieve the registered `divi/number-counter` block type
	 * and returns the custom CSS fields associated with it.
	 *
	 * @since ??
	 *
	 * @return array|false An array of custom CSS fields for the `divi/number-counter` block type, or false if the block type is not registered.
	 *
	 * @example:
	 * ```php
	 *    // Get the custom CSS fields for the `divi/number-counter` block type
	 *    $custom_css_fields = NumberCounterModule::custom_css();
	 *
	 *    if ( $custom_css_fields ) {
	 *        foreach ( $custom_css_fields as $field ) {
	 *            // Process each custom CSS field
	 *            // ...
	 *        }
	 *    }
	 * ```
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/number-counter' )->customCssFields;
	}

	/**
	 * Style declaration for number counter's border.
	 *
	 * This function is used to generate the style declaration for the border of a number counter module.
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
	 * Add NumberCounter module styles.
	 *
	 * This function is responsible for adding styles for the NumberCounter module.
	 * It handles various elements and settings to generate the necessary CSS styles for the module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/number-counter-module-styles ModuleStyles} located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type array       $attrs           Optional. The attributes array. Default `[]`.
	 *     @type object      $elements        The elements object.
	 *     @type array       $settings        Optional. The settings array. Default `[]`.
	 *     @type string      $id              The ID of the module style.
	 *     @type string      $name            The name of the module style.
	 *     @type int         $orderIndex      The order index for the module style.
	 *     @type string|null $storeInstance   The store instance for the module style.
	 *     @type string      $orderClass      The order class for the module style.
	 *     @type array       $css             Optional. The CSS attributes array. Default `[]`.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'attrs'          => [
	 *             // Attributes array.
	 *         ],
	 *         'elements'       => $elements,
	 *         'settings'       => [
	 *             // Settings array.
	 *         ],
	 *         'id'             => 'module-1',
	 *         'name'           => 'Module 1',
	 *         'orderIndex'     => 1,
	 *         'storeInstance'  => 'store-1',
	 *         'orderClass'     => 'module-1-container',
	 *         'css'            => [
	 *             // CSS attributes array.
	 *         ],
	 *     ];
	 *     NumberCounter::module_styles( $args );
	 * ```
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

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
													"{$args['orderClass']} .title",
													"{$args['orderClass']} .percent",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
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
					// Title.
					$elements->style(
						[
							'attrName' => 'title',
						]
					),
					// Number.
					$elements->style(
						[
							'attrName' => 'number',
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
	 * Load the NumberCounter module and register it's scripts.
	 *
	 * This function is responsible for loading the NumberCounter module, registering the FrontEnd (FE) render callback
	 * via WordPress `init` action hook and also registering the NumberCounter module's associated scripts.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/number-counter/';

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
