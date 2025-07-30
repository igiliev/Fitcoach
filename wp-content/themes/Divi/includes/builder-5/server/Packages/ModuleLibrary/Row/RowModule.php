<?php
/**
 * ModuleLibrary: Row Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Row;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block;
use ET\Builder\Packages\ModuleLibrary\Row\RowPresetAttrsMap;

/**
 * RowModule class.
 *
 * This class contains Row module functionality such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class RowModule implements DependencyInterface {

	use RowModuleTraits\GetColumnClassnameTrait;

	/**
	 * Get the module classnames for the Row module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/row-module-classnames moduleClassnames}
	 * located in `@divi/module-library` package.
	 *
	 * Adds the 'et-vb-row--no-module' class when the row has no module, matching the TypeScript implementation.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance An instance of `ET\Builder\Packages\Module\Layout\Components\Classnames` class.
	 *     @type array  $attrs              Block attributes data that is being rendered.
	 *     @type bool   $hasModule          Whether the row has inner blocks/modules.
	 * }
	 *
	 * @return void
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance    = $args['classnamesInstance'];
		$attrs                  = $args['attrs'];
		$has_module             = $args['hasModule'];
		$has_modules_in_columns = $args['hasModuleInColumns'] ?? false;

		$is_flexbox_enabled     = et_get_experiment_flag( 'flexbox' );
		$layout_display_default = $is_flexbox_enabled ? 'flex' : 'block';

		// Module components.
		$column_structure           = $attrs['module']['advanced']['columnStructure']['desktop']['value'] ?? null;
		$column_structure_classname = self::get_column_classname( $column_structure );
		$make_equal                 = $attrs['module']['advanced']['gutter']['desktop']['value']['makeEqual'] ?? 'off';
		$is_make_equal_on           = 'on' === $make_equal;
		$gutter_width               = $attrs['module']['advanced']['gutter']['desktop']['value']['width'] ?? 3;
		$layout_value               = $attrs['module']['decoration']['layout']['desktop']['value']['display'] ?? ( $is_flexbox_enabled ? 'flex' : 'block' );
		$is_flex_layout             = 'flex' === $layout_value;

		$classnames_instance->add( $column_structure_classname, true );

		$classnames_instance->add( 'et_pb_row_empty', ! $has_module );
		$classnames_instance->add( 'et-vb-row--no-module', ! $has_modules_in_columns );

		$classnames_instance->add( 'et_pb_equal_columns', $is_make_equal_on );

		$classnames_instance->add( 'et_pb_gutters' . $gutter_width, '' !== $gutter_width && ! $is_flex_layout );

		// Layout.
		$layout_display = $attrs['module']['decoration']['layout']['desktop']['value']['display'] ?? $layout_display_default;

		$is_flex_layout_display = 'flex' === $layout_display;

		$classnames_instance->add( 'et_flex_row', $is_flexbox_enabled && $is_flex_layout_display );

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
	 * Set Row module script data.
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
	 *             'portfolio' => array(
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
	 *     RowModule::module_script_data( $args );
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
	 * Style declaration for Row module element if it has border radius set.
	 *
	 * This function is the equivalent of the `overflowStyleDeclaration` JS function located in
	 * `packages/module-library/src/components/row/style-declarations/overflow/index.ts`.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of parameters.
	 *
	 *     @type array $attrValue The attribute value containing border radius information.
	 * }
	 *
	 * @return string The CSS overflow declaration string.
	 *
	 * @example:
	 * ```php
	 * $params = [
	 *     'attrValue' => [
	 *         'radius' => [
	 *             'topLeft' => '10px',
	 *             'topRight' => '10px',
	 *             'bottomLeft' => '10px',
	 *             'bottomRight' => '10px',
	 *         ],
	 *     ],
	 * ];
	 *
	 * $overflow_declaration = RowModule::overflow_style_declaration( $params );
	 * // Returns: 'overflow: hidden;'
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
	 * Add styles for the Row module.
	 *
	 * This function is responsible for adding styles for the Row module.
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
	 *     @type array          $defaultPrintedStyleAttrs Optional. The default printed style attributes. Default `[]`.
	 *     @type string         $orderClass               The selector class name.
	 *     @type int            $orderIndex               The order index of the module.
	 *     @type int            $storeInstance            The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * $args = array(
	 *     'id'                       => 'module-1',
	 *     'name'                     => 'Module 1',
	 *     'attrs'                    => array(),
	 *     'defaultPrintedStyleAttrs' => array(),
	 *     'orderClass'               => 'module-class',
	 *     'settings'                 => array(),
	 *     'elements'                 => new ModuleElements(),
	 * );
	 *
	 * Row::module_styles( $args );
	 * ```
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];

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
								'zIndex'                   => [
									'important' => true,
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
	 * Check if any column in the row has modules (non-empty innerBlocks).
	 *
	 * @param array $parsed_block The parsed block array for the row.
	 * @return bool True if any column has modules, false otherwise.
	 */
	private static function _has_modules_in_columns( $parsed_block ) {
		if ( isset( $parsed_block['innerBlocks'] ) && is_array( $parsed_block['innerBlocks'] ) ) {
			foreach ( $parsed_block['innerBlocks'] as $column ) {
				if ( isset( $column['innerBlocks'] ) && is_array( $column['innerBlocks'] ) && count( $column['innerBlocks'] ) > 0 ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Render callback for the Row module.
	 *
	 * Generates the HTML output for the Row module to be rendered on the FrontEnd (FE).
	 *
	 * @since ??
	 *
	 * @param array          $attrs    The block attributes.
	 * @param string         $content  The block content.
	 * @param WP_Block       $block    The block object.
	 * @param ModuleElements $elements The elements object.
	 * @param array          $default_printed_style_attrs The default printed style attributes.
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
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$has_modules_in_columns = self::_has_modules_in_columns( $block->parsed_block );

		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

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
				'hasModuleInColumns'       => $has_modules_in_columns,
				'moduleCategory'           => $block->block_type->category,
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'children'                 => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $content,
			]
		);
	}

	/**
	 * Load the module file and register the module.
	 *
	 * Loads Row module.json file, registers the module FrontEnd (FE) render callback via WordPress `init`
	 * action hook and registers REST API Endpoints.
	 *
	 * @since ??
	 * @return void
	 */
	public function load() {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/row/';

		add_filter( 'divi_conversion_presets_attrs_map', array( RowPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
