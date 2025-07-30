<?php
/**
 * ModuleLibrary: Tab Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Tab;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use WP_Block;
use ET\Builder\Packages\ModuleLibrary\Tab\TabPresetAttrsMap;

/**
 * TabModule class.
 *
 * This class contains functions used for Tab Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class TabModule implements DependencyInterface {

	/**
	 * Get the module classnames for the Tab module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/tab-module-classnames moduleClassnames}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance An instance of `ET\Builder\Packages\Module\Layout\Components\Classnames` class.
	 *     @type array  $isFirst            Whether the child element is the first element.
	 * }
	 *
	 * @example:
	 * ```php
	 * // Example 1: Adding classnames for the toggle options.
	 * TabModule::module_classnames( [
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'isFirst' => false,
	 * ] );
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Example 2: Adding classnames for the module.
	 * TabModule::module_classnames( [
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'isFirst' => true,
	 * ] );
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$is_first            = $args['isFirst'];

		// et_pb_tab_0 or et_pb_tab_1 according to child item index.
		$classnames_instance->add( 'et_pb_tab' );
		$classnames_instance->add( 'clearfix' );
		$classnames_instance->add( 'et_pb_active_content', $is_first );

	}

	/**
	 * Set Tab module script data.
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
	 * Load TeamMember module style components.
	 *
	 * This function is responsible for loading styles for the module. It takes an array of arguments
	 * which includes the module ID, name, attributes, settings, and other details. The function then
	 * uses these arguments to dynamically generate and add the required styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/tab-module-styles ModuleStyles}
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
	 *     TeamMemberModule::module_styles([
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
							'attrName' => 'module',
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
	 * Retrieves the title attribute for each breakpoint and state.
	 *
	 * Note: In FrontEnd (FE), the title should not be empty, if there is no title value, `"Tab"` string
	 * will be used.
	 *
	 * @since ??
	 *
	 * @param array $attr The array containing the attribute data for each breakpoint and state.
	 *
	 * @return array The updated array with the normalized title attribute for each breakpoint and state.
	 *
	 * @example:
	 * ```php
	 *   $attr = [
	 *     'desktop' => [
	 *       'normal' => 'Normal',
	 *       'hover' => 'Hover',
	 *     ],
	 *     'tablet' => [
	 *       'normal' => '',
	 *       'hover' => 'Tablet Hover',
	 *     ],
	 *   ];
	 *   $new_attr = TabModule::get_title_attr( $attr );
	 *
	 *   // Returns:
	 *   // [
	 *   //   'desktop' => [
	 *   //     'normal' => 'Normal',
	 *   //     'hover' => 'Hover',
	 *   //   ],
	 *   //   'tablet' => [
	 *   //     'normal' => 'Tab',
	 *   //     'hover' => 'Tablet Hover',
	 *   //   ],
	 *   // ]
	 * ```
	 */
	public static function get_title_attr( array $attr ): array {
		$new_attr = [];

		foreach ( $attr as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$normalized = ModuleUtils::use_attr_value(
					[
						'attr'       => $attr,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$new_attr[ $breakpoint ][ $state ] = '' === $normalized ? esc_html__( 'Tab', 'et_builder' ) : $normalized;
			}
		}

		return $new_attr;
	}

	/**
	 * Render callback function for the Tab module.
	 *
	 * This function generates HTML for rendering on the FrontEnd (FE).
	 *
	 * @since ??
	 *
	 * @param array          $attrs    The attributes passed to the block.
	 * @param string         $content  The content inside the block.
	 * @param WP_Block       $block    The parsed block object.
	 * @param ModuleElements $elements The elements object containing style components.
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
	 * $rendered_content = TabModule::render_callback($attrs, $content, $block, $elements);
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
	 * $rendered_content = TabModule::render_callback($attrs, $content, $block, $elements);
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ) {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		$is_first = BlockParserStore::is_first( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		// Content.
		$content = $elements->render(
			[
				'attrName'      => 'content',
				'hoverSelector' => '{{parentSelector}}',
			]
		);

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'isFirst'             => $is_first,
				'name'                => $block->block_type->name,
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'hasModuleClassName'  => false,
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $content,
			]
		);
	}

	/**
	 * Load the module and register its render callback.
	 *
	 * This function registers the tab module to be used in the Visual Builder (VB).
	 * It adds an action to the WordPress 'init' hook that calls the `register_module` method
	 * of the `ModuleRegistration` class, passing the module JSON folder path and the render callback
	 * function as arguments.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/tab/';

		add_filter( 'divi_conversion_presets_attrs_map', array( TabPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
