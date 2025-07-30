<?php
/**
 * ModuleLibrary: Text Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Text;

use ET\Builder\Packages\ModuleLibrary\Text\TextPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use WP_Block;

/**
 * TextModule class.
 *
 * This class contains functions used for Text Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class TextModule implements DependencyInterface {

	/**
	 * Get the module classnames for the Text module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/text-module-classnames moduleClassnames}
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
	 * // Example 1: Adding classnames for the text options.
	 * TextModule::module_classnames( [
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
	 * TextModule::module_classnames( [
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

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

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
	 * Generate the script data for the Text module based on the provided arguments.
	 *
	 * This function assigns variables and sets element script data options.
	 * It then uses `MultiViewScriptData` to set module specific FrontEnd (FE) data.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments for generating the script data.
	 *
	 *     @type string  $id             Optional. The ID of the module. Default empty string.
	 *     @type string  $name           Optional. The name of the module. Default empty string.
	 *     @type string  $selector       Optional. The selector of the module. Default empty string.
	 *     @type array   $attrs          Optional. The attributes of the module. Default `[]`.
	 *     @type object  $elements       The elements object.
	 *     @type integer $storeInstance  Optional. The ID of instance where this block is stored in BlockParserStore. Default `null`.
	 * }
	 *
	 * @return void
	 *
	 * @example
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
	 * Text::module_script_data( $args );
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
	 * Style declaration for text's border overflow.
	 *
	 * This function is used to generate the style declaration for the border overflow of a text module.
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
	 * Add Text module style components.
	 *
	 * This function adds styles for a module to the Style class.
	 * It takes an array of arguments and uses them to define the styles for the module.
	 * The styles are then added to the Style class instance using the `Style::add()` method.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/text-module-styles ModuleStyles}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for defining the module styles.
	 *
	 *     @type string  $id               Optional. The ID of the module. Default empty string.
	 *                                     In Visual Builder (VB), the ID of a module is a UUIDV4 string.
	 *                                     In FrontEnd (FE), the ID is order index.
	 *     @type string  $name             Optional. The name of the module. Default empty string.
	 *     @type int     $orderIndex       The order index of the module style.
	 *     @type array   $attrs            Optional. The attributes of the module. Default `[]`.
	 *     @type array   $settings         Optional. An array of settings for the module style. Default `[]`.
	 *     @type integer $storeInstance    Optional. The ID of instance where this block is stored in BlockParserStore. Default `null`.
	 *     @type string  $orderClass       The order class for the module style.
	 *     @type ModuleElements $elements  ModuleElements instance.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     // Example usage of the module_styles() function.
	 *     TextModule::module_styles( [
	 *         'id'            => 'my-module-style',
	 *         'name'          => 'My Module Style',
	 *         'orderIndex'    => 1,
	 *         'storeInstance' => null,
	 *         'attrs'         => [
	 *             'css' => [
	 *                 'color' => 'red',
	 *             ],
	 *         ],
	 *         'elements'      => $elements,
	 *         'settings'      => [
	 *             'disabledModuleVisibility' => true,
	 *         ],
	 *         'orderClass'    => '.my-module',
	 *     ] );
	 * ```
	 *
	 * @example:
	 * ```php
	 *     // Another example usage of the module_styles() function.
	 *     $args = [
	 *         'id'            => 'my-module-style',
	 *         'name'          => 'My Module Style',
	 *         'orderIndex'    => 1,
	 *         'storeInstance' => null,
	 *         'attrs'         => [
	 *             'css' => [
	 *                 'color' => 'blue',
	 *             ],
	 *         ],
	 *         'elements'      => $elements,
	 *         'settings'      => [
	 *             'disabledModuleVisibility' => false,
	 *         ],
	 *         'orderClass'    => '.my-module',
	 *     ];
	 *     TextModule::module_styles( $args );
	 * ```
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		$color_important = [
			'font' => [
				'desktop' => [
					'value' => [
						'color' => true,
					],
				],
			],
		];

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
											'attr' => $attrs['module']['advanced']['text'] ?? [],
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
					// Ideally selector should be different, but as in D4 it is same so we are keeping it same.
					// Content.
					$elements->style(
						[
							'attrName' => 'content',
						]
					),
					// Module - Only for Custom CSS.
					CssStyle::style(
						[
							'selector' => $args['orderClass'],
							'attr'     => $attrs['css'] ?? [],
						]
					),
				],
			]
		);
	}

	/**
	 * Render callback for the Text module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the FrontEnd (FE).
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/text-edit TextEdit}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    The block attributes that were saved by the Visual Builder.
	 * @param string         $content  The block content.
	 * @param WP_Block       $block    The parsed block object that is being rendered.
	 * @param ModuleElements $elements An instance of the ModuleElements class.
	 *
	 * @return string The rendered HTML for the module.
	 *
	 * @example:
	 * ```php
	 * $attrs = [
	 *     'attrName' => 'value',
	 *     //...
	 * ];
	 * $content = 'This is the content';
	 * $block = new WP_Block();
	 * $elements = new ModuleElements();
	 *
	 * $html = Text::render_callback( $attrs, $content, $block, $elements );
	 * echo $html;
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {
		// Content.
		$content = $elements->render(
			[
				'attrName' => 'content',
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
				) . $content,
			]
		);
	}

	/**
	 * Load the module by registering a render callback for the Text module.
	 *
	 * This function is responsible for registering the module for the Text module
	 * by adding the render callback to the WordPress `init` action hook.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/text/';

		add_filter( 'divi_conversion_presets_attrs_map', array( TextPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
