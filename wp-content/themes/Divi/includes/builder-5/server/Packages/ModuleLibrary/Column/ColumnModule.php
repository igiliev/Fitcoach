<?php
/**
 * Module Library: Column Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Column;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP Core use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\ModuleLibrary\Column\ColumnPresetAttrsMap;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block;

/**
 * ColumnModule class.
 *
 * This class implements the functionality of a column component in a frontend
 * application. It provides functions for rendering the column, managing REST
 * API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class ColumnModule implements DependencyInterface {

	/**
	 * Get column inner's column type which is affected by its own column type and column parent's type.
	 *
	 * This function is equivalent to the JS function
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs getColumnInnerType}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param string $section_column_type Section column type which is passed down as.
	 * @param string $column_type         Column inner's column type.
	 *
	 * @return string Column inner's column type.
	 */
	public static function get_column_inner_type( string $section_column_type, string $column_type ): string {
		if ( $column_type ) {
			if ( '1_2' === $column_type ) {
				switch ( $section_column_type ) {
					case '1_2':
						return '1_4';

					case '3_4':
						return '3_8';

					case '2_3':
						return '1_3';

					default:
						return '4_4';
				}
			} elseif ( '1_3' === $column_type ) {
				switch ( $section_column_type ) {
					case '1_2':
						return '1_6';

					case '3_4':
						return '1_4';

					case '2_3':
						return '2_9';

					default:
						return '4_4';
				}
			} elseif ( '1_4' === $column_type ) {
				if ( '2_3' === $section_column_type ) {
					return '1_6';
				}
			}
		}

		return '4_4';
	}

	/**
	 * Render styles component
	 *
	 * @param array $args {
	 *      An array of arguments.
	 *
	 *      @type ModuleElements   $elements The instance of ModuleElements class.
	 *      @type WP_Block         $block The block object.
	 *      @type BlockParserBlock $parent The parent block object.
	 *      @type int|null         $storeInstance The store instance.
	 * }
	 * @return string
	 */
	public static function render_style_components( array $args ): string {
		$elements       = $args['elements'];
		$block          = $args['block'];
		$parent         = $args['parent'];
		$store_instance = $args['storeInstance'] ?? null;

		if ( 'divi/column' === $block->name && 'divi/section' === $parent->blockName ) {
			$column_attr_name = self::get_column_attr_name( $block->parsed_block['id'], $parent->id );
			$column_attrs     = $parent->attrs[ $column_attr_name ] ?? [];
			$decoration_attr  = $column_attrs['decoration'] ?? [];

			return ElementComponents::component(
				[
					'id'            => $block->parsed_block['id'],
					'attrs'         => $decoration_attr,

					// FE Only.
					'orderIndex'    => $block->orderIndex,
					'storeInstance' => $store_instance,
				]
			);
		}

		return $elements->style_components(
			[
				'attrName' => 'module',
			]
		);
	}

	/**
	 * Render callback for the Column module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ ColumnEdit}
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
	 * @return string The HTML rendered output of the Column module.
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
	 * ColumnModule::render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function ( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$is_last = BlockParserStore::is_last( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

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
				'parentAttrs'              => $parent->attrs ?? [],
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'htmlAttributesFunction'   => function ( array $params ) use ( $block, $parent ): array {
					return ColumnModule::module_html_attributes( $params, $block, $parent, $block->parsed_block['storeInstance'] );
				},
				'id'                       => $block->parsed_block['id'],
				'isLast'                   => $is_last,
				'childrenIds'              => $children_ids,
				'name'                     => $block->block_type->name,
				'moduleCategory'           => $block->block_type->category,
				'children'                 => [
					self::render_style_components(
						[
							'elements'      => $elements,
							'block'         => $block,
							'parent'        => $parent,
							'storeInstance' => $block->parsed_block['storeInstance'],
						]
					),
					$content,
				],
			]
		);
	}

	/**
	 * Column module front-end render_block_data filter.
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
		if ( 'divi/column' !== $parsed_block['blockName'] ) {
			return $parsed_block;
		}

		/**
		 * Pass custom attribute into attrs if current module is section-column (direct child
		 * of specialty section) so column knows that it is section-column, not regular column.
		 *
		 * @since ??
		 *
		 * @see https://github.com/elegantthemes/submodule-builder-5/blob/9d27e56991790d438a3bc89faa6abd22a3615a2a/visual-builder/packages/module/src/layout/components/child-modules/component.tsx#L129-L139
		 */
		if ( ! isset( $parsed_block['attrs']['sectionType'] ) ) {
			if ( 'divi/section' === BlockParserStore::get_parent( $parsed_block['id'], $parsed_block['storeInstance'] )->blockName ) {
				$parsed_block['attrs']['sectionType'] = [
					'desktop' => [
						'value' => 'specialty',
					],
				];
			}
		}

		return $parsed_block;
	}

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Column module.
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
	 *     @type string $name               Nodule name.
	 *     @type bool   $isLast             Whether this item is the last child.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'attrs' => $attrs,
	 * ];
	 *
	 * ColumnModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];
		$is_last             = $args['isLast'];
		$name                = $args['name'];
		$parent_attrs        = $args['parentAttrs'] ?? [];

		$is_flexbox_enabled     = et_get_experiment_flag( 'flexbox' );
		$layout_display_default = $is_flexbox_enabled ? 'flex' : 'block';

		$is_column_inner     = 'divi/column-inner' === $name;
		$is_section_column   = 'specialty' === ( $attrs['sectionType']['desktop']['value'] ?? null );
		$column_type         = $attrs['module']['advanced']['type']['desktop']['value'] ?? '4_4';
		$specialty_columns   = $attrs['module']['advanced']['specialtyColumns']['desktop']['value'] ?? '';
		$section_column_type = $attrs['module']['advanced']['sectionColumnType']['desktop']['value'] ?? '';

		$parent_layout_display    = $parent_attrs['module']['decoration']['layout']['desktop']['value']['display'] ?? $layout_display_default;
		$is_parent_layout_is_flex = $is_flexbox_enabled ? 'flex' === $parent_layout_display : false;

		if ( ! $is_parent_layout_is_flex ) {
			$classnames_instance->add( 'et_pb_column_' . self::get_column_inner_type( $section_column_type, $column_type ), $is_column_inner );
			$classnames_instance->add( 'et_pb_column_' . $column_type, ! $is_column_inner );
			$classnames_instance->add( 'et_pb_specialty_column', (bool) $specialty_columns );
			$classnames_instance->add( 'et_pb_column', $is_column_inner );
			$classnames_instance->add( 'et_pb_column_single', $is_section_column && empty( $specialty_columns ) );
		}

		$classnames_instance->add( 'et-last-child', $is_last );

		if ( $is_parent_layout_is_flex ) {
			$classnames_instance->add( 'et_flex_column', true );

			$breakpoints_mapping = [
				'desktop' => '',
				'tablet'  => '_tablet',
				'phone'   => '_phone',
			];

			foreach ( $breakpoints_mapping as $breakpoint => $suffix ) {
				$flex_type = $attrs['module']['advanced']['flexType'][ $breakpoint ]['value'] ?? null;

				if ( $flex_type && 'none' !== $flex_type ) {
					$classnames_instance->add( "et_flex_column_{$flex_type}{$suffix}" );
				}
			}
		}

		$has_mix_blend_mode   = ! empty( $attrs['module']['decoration']['filters']['desktop']['value']['blendMode'] );
		$mix_blend_class_name = $has_mix_blend_mode ? 'et_pb_css_mix_blend_mode' : 'et_pb_css_mix_blend_mode_passthrough';

		// Columns need to pass through if no mix-blend mode is selected.
		$classnames_instance->add( $mix_blend_class_name );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
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
	 * Style declaration for column's border overflow.
	 *
	 * This function is used to generate the style declaration for the border overflow of a column module.
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
	 * Column Module's style components.
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
	 * Column module script data.
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
	 * ColumnModule::module_script_data( $args );
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
	 * Get attribute name for columns in the specialty section.
	 *
	 * @since ??
	 *
	 * @param string   $column_id Column ID.
	 * @param string   $parent_id Parent ID.
	 * @param null|int $store_instance Store instance.
	 *
	 * @return string
	 */
	public static function get_column_attr_name( string $column_id, string $parent_id, ?int $store_instance = null ): string {
		$siblings = BlockParserStore::get_children( $parent_id, $store_instance );

		$column_index = -1;

		foreach ( $siblings as $index => $sibling ) {
			if ( $sibling->id === $column_id ) {
				$column_index = $index;
				break;
			}
		}

		if ( -1 === $column_index ) {
			return '';
		}

		return 'column' . ( $column_index + 1 );
	}

	/**
	 * Retrieves HTML attributes for a module based on the block and parent block names.
	 *
	 * @since ??
	 *
	 * @param array            $params The original params array passed by the the `htmlAttributesFunction` function.
	 * @param WP_Block         $block The object of the current block.
	 * @param BlockParserBlock $parent The object of the parent block.
	 * @param int              $store_instance The block parser store instance.
	 *
	 * @return array An array contains the id and classNames of the module.
	 */
	public static function module_html_attributes( array $params, WP_Block $block, BlockParserBlock $parent, ?int $store_instance = null ): array {
		if ( 'divi/column' === $block->name && 'divi/section' === $parent->blockName ) {
			$column_attr_name = self::get_column_attr_name( $block->parsed_block['id'], $parent->id, $store_instance );
			$column_attrs     = $parent->attrs[ $column_attr_name ] ?? [];

			return [
				'id'         => $column_attrs['advanced']['htmlAttributes']['desktop']['value']['id'] ?? '',
				'classNames' => $column_attrs['advanced']['htmlAttributes']['desktop']['value']['class'] ?? '',
			];
		}

		return [
			'id'         => $params['attrs']['module']['advanced']['htmlAttributes']['desktop']['value']['id'] ?? '',
			'classNames' => $params['attrs']['module']['advanced']['htmlAttributes']['desktop']['value']['class'] ?? '',
		];
	}

	/**
	 * Get the preset attributes map for the Column module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/column' !== $module_name ) {
			return $map;
		}

		unset( $map['module.decoration.sizing__alignment'] );

		return $map;
	}

	/**
	 * Loads `ColumnModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/column/';

		add_filter( 'divi_conversion_presets_attrs_map', array( self::class, 'get_map' ), 10, 2 );

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
