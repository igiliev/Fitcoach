<?php
/**
 * Module Library: Column Inner Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\ColumnInner;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;
use ET\Builder\Packages\ModuleLibrary\Column\ColumnModule;

/**
 * ColumnInnerModule class.
 *
 * This class implements the functionality of an inner column component in a
 * frontend application. It provides functions for rendering the inner column,
 * managing REST API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class ColumnInnerModule implements DependencyInterface {

	/**
	 * Column Inner module script data.
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
	 * ColumnInnerModule::module_script_data( $args );
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
	 * ColumnInner module front-end render_block_data filter.
	 *
	 * @since ??
	 *
	 * @param array         $parsed_block The block being rendered.
	 * @param array         $source_block An un-modified copy of $parsed_block, as it appeared in the source content.
	 * @param null|WP_Block $parent_block If this is a nested block, a reference to the parent block.
	 *
	 * @return array Filtered block that being rendered.
	 */
	public static function render_block_data( array $parsed_block, array $source_block, ?WP_Block $parent_block ): array {
		if ( 'divi/column-inner' !== $parsed_block['blockName'] ) {
			return $parsed_block;
		}

		// Pass section column's type so column-inner can calculate its structure classname which
		// is its type attribute affected by section column's type. in D4, this is done by
		// physically saving section column as `saved_specialty_column_type` attribute. In D5i,
		// we don't need to duplicate it for grand children module (column-inner) because it can
		// be passed this way.
		// @see https://github.com/elegantthemes/submodule-builder-5/blob/9d27e56991790d438a3bc89faa6abd22a3615a2a/visual-builder/packages/module/src/layout/components/child-modules/component.tsx#L131-L139.
		if ( ! isset( $parsed_block['attrs']['module']['advanced']['sectionColumnType'] ) ) {
			$parsed_block['attrs']['module']['advanced']['sectionColumnType'] = BlockParserStore::find_ancestor(
				$parsed_block['id'],
				function( BlockParserBlock $ancestor ) {
					// phpcs:ignore ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP core use snakeCase in \WP_Block_Parser_Block
					return 'divi/column' === $ancestor->blockName;
				},
				$parsed_block['storeInstance']
			)->attrs['module']['advanced']['type'] ?? null;
		}

		return $parsed_block;
	}

	/**
	 * Column Inner Module's style components.
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
	 *     @type string         $id                       Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *     @type string         $name                     Module name.
	 *     @type string         $attrs                    Module attributes.
	 *     @type string         $defaultPrintedStyleAttrs Default printed style attributes.
	 *     @type string         $parentAttrs              Parent attrs.
	 *     @type string         $orderClass               Selector class name.
	 *     @type string         $parentOrderClass         Parent selector class name.
	 *     @type string         $wrapperOrderClass        Wrapper selector class name.
	 *     @type string         $settings                 Custom settings.
	 *     @type string         $state                    Attributes state.
	 *     @type string         $mode                     Style mode.
	 *     @type int            $orderIndex               Module order index.
	 *     @type int            $storeInstance            The ID of instance where this block stored in BlockParserStore class.
	 *     @type ModuleElements $elements                 ModuleElements instance.
	 * }
	 *
	 * @return void
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
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/css',
										'props'         => [
											'attr' => $attrs['css'] ?? [],
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
	 * Render callback for the ColumnInner module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ ColumnInnerEdit}
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
	 * @return string The HTML rendered output of the ColumnInner module.
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
	 * ColumnInnerModule::render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$is_last = BlockParserStore::is_last( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		return Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'                    => $attrs,
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'classnamesFunction'       => [ ColumnModule::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'id'                       => $block->parsed_block['id'],
				'isLast'                   => $is_last,
				'childrenIds'              => $children_ids,
				'name'                     => $block->block_type->name,
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
	 * Loads `ColumnInnerModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/column-inner/';

		add_filter(
			'render_block_data',
			[ self::class, 'render_block_data' ],
			10,
			3
		);

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
