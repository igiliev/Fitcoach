<?php
/**
 * ModuleLibrary: Inner Row Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\RowInner;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block;

/**
 * RowInnerModule class.
 *
 * This class contains functions used for Row Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class RowInnerModule implements DependencyInterface {

	use \ET\Builder\Packages\ModuleLibrary\Row\RowModuleTraits\GetColumnClassnameTrait;

	/**
	 * Row inner column structures.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_row_inner_column_structures = [
		'1_2-1_2,1_2'         => '1-4_1-4',
		'1_2-1_3,1_3,1_3'     => '1-6_1-6_1-6',
		'3_4-1_3,1_3,1_3'     => '1-4_1-4_1-4',
		'2_3-1_4,1_4,1_4,1_4' => '1-6_1-6_1-6_1-6',
	];

	/**
	 * Retrieve the classname for the row inner structure based on the section column type and column structure.
	 *
	 * This function is equivalent to the JavaScript function getRowInnerStructureClassname located in
	 * visual-builder/packages/module-library/src/components/row/utils/get-row-inner-structure-classname/index.ts.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/get-row-inner-structure-classname getRowInnerStructureClassname}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param string|null $section_column_type The section column's type.
	 * @param string|null $column_structure    The column structure.
	 *
	 * @return string|false The classname for the row inner structure, or `false` if not found.
	 *
	 * @example:
	 * ```php
	 *      $classname = RowInnerModule::get_row_inner_structure_classname('1_2', '1_2,1_2');
	 *
	 *      if ($classname) {
	 *          echo $classname;
	 *      } else {
	 *          echo "Classname not found";
	 *      }
	 * ```
	 */
	public static function get_row_inner_structure_classname( ?string $section_column_type, ?string $column_structure ) {
		$index                      = $section_column_type . '-' . $column_structure;
		$row_inner_column_structure = isset( self::$_row_inner_column_structures[ $index ] ) ? self::$_row_inner_column_structures[ $index ] : false;

		if ( $row_inner_column_structure ) {
			return 'et_pb_row-' . $row_inner_column_structure;
		}

		return false;
	}

	/**
	 * Retrieve the module class names for RowInner.
	 *
	 * This function retrieves the class names for RowInner module based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance An instance of `ET\Builder\Packages\Module\Layout\Components\Classnames` class.
	 *     @type array  $attrs              The block attributes data that is being rendered.
	 *     @type bool   $hasModule          Whether the module has inner modules/blocks.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *      $args = [
	 *          'classnamesInstance' => $classnamesInstance,
	 *          'attrs' => $attrs,
	 *          'hasModule' => $hasModule,
	 *      ];
	 *
	 *      RowInnerModule::module_classnames( $args );
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];
		$has_module          = $args['hasModule'];

		// Module components.
		$column_structure           = $attrs['module']['advanced']['columnStructure']['desktop']['value'] ?? null;
		$column_structure_classname = self::get_column_classname( $column_structure );
		$make_equal                 = $attrs['module']['advanced']['gutter']['desktop']['value']['makeEqual'] ?? 'off';
		$is_make_equal_on           = 'on' === $make_equal;
		$enable_gutter              = $attrs['module']['advanced']['gutter']['desktop']['value']['enable'] ?? 'off';
		$gutter_width               = $attrs['module']['advanced']['gutter']['desktop']['value']['width'] ?? 3;
		$use_custom_gutter_width    = 'on' === $enable_gutter;

		$classnames_instance->add( $column_structure_classname, true );

		$classnames_instance->add( 'et_pb_row_empty', ! $has_module );

		$classnames_instance->add( 'et_pb_equal_columns', $is_make_equal_on );

		$classnames_instance->add( 'et_pb_gutters' . $gutter_width, $use_custom_gutter_width );

		$row_inner_structure_classname = self::get_row_inner_structure_classname(
			$attrs['module']['advanced']['sectionColumnType']['desktop']['value'] ?? null,
			$attrs['module']['advanced']['columnStructure']['desktop']['value'] ?? null
		);

		$classnames_instance->add( $row_inner_structure_classname, true );
		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO fix(D5, module attribute refactor), these will be replaced by `'attrs' => $attrs['module']['decoration'] ?? []`.
					'attrs' => [
						'animation'  => $attrs['animation'] ?? [],
						'background' => $attrs['background'] ?? [],
						'border'     => $attrs['border'] ?? [],
						'boxShadow'  => $attrs['boxShadow'] ?? [],
						'link'       => $attrs['module']['advanced']['link'] ?? [],
					],
				]
			)
		);
	}

	/**
	 * Generate the script data for RowInner module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments for generating the script data.
	 *
	 *     @type object  $elements       The elements object.
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
	 *     'storeInstance' => 123,
	 * );
	 *
	 * RowInnerModule::module_script_data( $args );
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
	 * Style declaration for inner row's border.
	 *
	 * This function is used to generate the style declaration for the border of an inner row module.
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
	 * Add styles for RowInner module.
	 *
	 * This function adds styles for RowInner module based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/row-inner-module-styles ModuleStyles}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for adding module styles.
	 *
	 *     @type string $id                       The ID of the module.
	 *                                            In Visual Builder (VB), the ID of module is a UUIDV4 string.
	 *                                            In FrontEnd (FE), the ID is order index.
	 *     @type string $name                     The name of the module.
	 *     @type array  $attrs                    Optional. The attributes of the module. Default `[]`.
	 *     @type ModuleElements $elements         ModuleElements instance.
	 *     @type array  $settings                 Optional. The custom settings. Default `[]`.
	 *     @type array  $defaultPrintedStyleAttrs Optional. The default printed style attributes. Default `[]`.
	 *     @type string $orderClass               The selector class name for the module.
	 *     @type array  $disabledModuleVisibility The visibility settings for the disabled module.
	 *     @type mixed  $storeInstance            The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @return void
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];
		$main_selector               = "{$args['orderClass']}.et_pb_row_inner";

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
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/css',
										'props'         => [
											'attr' => $attrs['css'] ?? [],
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
				],
			]
		);
	}

	/**
	 * Render callback function for the RowInner module.
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
	 * $default_printed_style_attrs = [];
	 *
	 * $rendered_content = RowInnerModule::render_callback($attrs, $content, $block, $elements, $default_printed_style_attrs);
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
	 * $default_printed_style_attrs = [
	 *     'color' => '#000000',
	 *     'font-size' => '14px',
	 * ];
	 *
	 * $rendered_content = RowInnerModule::render_callback($attrs, $content, $block, $elements, $default_printed_style_attrs);
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function ( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];
		$has_module   = isset( $block->parsed_block['innerBlocks'] ) && 0 < count( $block->parsed_block['innerBlocks'] );

		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		return Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'                    => $attrs,
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'id'                       => $block->parsed_block['id'],
				'childrenIds'              => $children_ids,
				'name'                     => $block->block_type->name,
				'parentAttrs'              => $parent->attrs ?? [],
				'parentId'                 => $parent->id ?? '',
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'hasModule'                => $has_module,
				'moduleCategory'           => $block->block_type->category,
				'children'                 => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $content,
			]
		);
	}

	/**
	 * Render the block data for the RowInner module.
	 *
	 * This function is responsible for processing the parsed block data and updating
	 * it based on certain conditions. It specifically handles the case when the block
	 * name is `'divi/row-inner'`. It checks if the section column's type is set in the
	 * `'attrs'` array of the parsed block. If it is not set, it determines the section
	 * column's type based on its ancestor block, `'divi/column'`. The section column's
	 * type is then added to the `'attrs'` array of the parsed block.
	 *
	 * @since ??
	 *
	 * @param array         $parsed_block  The parsed block data.
	 * @param array         $source_block  The source block data.
	 * @param WP_Block|null $parent_block The parent block object.
	 *
	 * @return array  The updated parsed block data.
	 */
	public static function render_block_data( array $parsed_block, array $source_block, ?WP_Block $parent_block ): array {
		if ( 'divi/row-inner' !== $parsed_block['blockName'] ) {
			return $parsed_block;
		}

		// Pass section column's type so row-inner can calculate its structure column which is
		// its columnStructure attribute affected by section column's type.
		// @see https://github.com/elegantthemes/submodule-builder-5/blob/9d27e56991790d438a3bc89faa6abd22a3615a2a/visual-builder/packages/module/src/layout/components/child-modules/component.tsx#L109-L113.
		if ( ! isset( $parsed_block['attrs']['module']['advanced']['sectionColumnType'] ) ) {
			$parsed_block['attrs']['module']['advanced']['sectionColumnType'] = BlockParserStore::find_ancestor(
				$parsed_block['id'],
				function ( BlockParserBlock $ancestor ) {
					// phpcs:ignore ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP core use snakeCase in \WP_Block_Parser_Block
					return 'divi/column' === $ancestor->blockName;
				},
				$parsed_block['storeInstance']
			)->attrs['type'] ?? null;
		}

		return $parsed_block;
	}

	/**
	 * Load RowInner module.
	 *
	 * Loads RowInnerModule and registers Front-End render callback and REST API Endpoints.
	 * This function uses WordPress `init` action hook to register the module.
	 * This function also uses WordPress `render_block_data` filter to register the module's block data.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		add_filter(
			'render_block_data',
			[ self::class, 'render_block_data' ],
			10,
			3
		);

		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/row-inner/';

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
