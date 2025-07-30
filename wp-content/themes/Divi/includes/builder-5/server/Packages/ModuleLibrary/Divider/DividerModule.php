<?php
/**
 * Module Library: Divider Module
 *
 * @package Divi
 * @since   ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Divider;

if ( ! defined( 'ABSPATH' ) ) {
		die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * DividerModule class.
 *
 * This class implements the functionality of a divider component in a frontend
 * application. It provides functions for rendering the divider, managing REST
 * API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class DividerModule implements DependencyInterface {

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Divider module.
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
	 *     'classnamesInstance' => $classnamesInstance,
	 *     'attrs' => $attrs,
	 * ];
	 *
	 * DividerModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance     = $args['classnamesInstance'];
		$attrs                   = $args['attrs'];
		$show_divider            = $attrs['divider']['advanced']['line']['desktop']['value']['show'] ?? 'on';
		$divider_position        = $attrs['divider']['advanced']['line']['desktop']['value']['position'] ?? '';
		$divider_position_tablet = $attrs['divider']['advanced']['line']['tablet']['value']['position'] ?? '';
		$divider_position_phone  = $attrs['divider']['advanced']['line']['phone']['value']['position'] ?? '';
		if ( 'off' === $show_divider ) {
			$classnames_instance->add( 'et_pb_divider_hidden' );
			$classnames_instance->remove( 'et_pb_divider' );
		}

		$classnames_instance->add( 'et_pb_divider', 'on' === $show_divider );
		$classnames_instance->add( 'et_pb_space' );
		$classnames_instance->add( "et_pb_divider_position_{$divider_position}", 'top' !== $divider_position_tablet );
		$classnames_instance->add( "et_pb_divider_position_{$divider_position_tablet}_tablet", 'on' === $show_divider && boolval( $divider_position_tablet ) );
		$classnames_instance->add( "et_pb_divider_position_{$divider_position_phone}_phone", 'on' === $show_divider && boolval( $divider_position_phone ) );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
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
	 * Divider module script data.
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
	 * DividerModule::module_script_data( $args );
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
							'et_pb_divider_hidden' => $attrs['divider']['advanced']['line'] ?? [],
							'et_pb_divider'        => $attrs['divider']['advanced']['line'] ?? [],
						],
						'valueResolver' => function ( $value, $resolver_args ) {
							if ( 'et_pb_divider_hidden' === $resolver_args['className'] ) {
								return 'off' === ( $value['show'] ?? 'on' ) ? 'add' : 'remove';
							}

							return 'on' === ( $value['show'] ?? 'on' ) ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * Style declaration for divider's border overflow.
	 *
	 * This function is used to generate the style declaration for the border overflow of a divider module.
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
	 * Generates a style declaration to ensure the Divider module has `width: 100%`
	 * when its position is set to `absolute`.
	 *
	 * This resolves an issue where the Divider module would disappear due to a missing width definition.
	 *
	 * @since ??
	 *
	 * @param array $params An array containing the `attrValue` with position attributes.
	 *
	 * @return string The generated CSS declarations.
	 *
	 * @example
	 * ```php
	 * $styles = self::position_width_style_declaration([
	 *     'attrValue' => [ 'mode' => 'absolute' ]
	 * ]);
	 *
	 * echo $styles; // Outputs: "width: 100%;"
	 * ```
	 */
	public static function position_width_style_declaration( array $params ): string {
		$mode = $params['attrValue']['mode'] ?? [];

		if ( ! $mode ) {
			return '';
		}

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( 'absolute' === $mode ) {
			$style_declarations->add( 'width', '100%' );
		}

		return $style_declarations->value();
	}

	/**
	 * Divider Module's style components.
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
	 *      @type string $id                Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *      @type string $name              Module name.
	 *      @type string $attrs             Module attributes.
	 *      @type string $parentAttrs       Parent attrs.
	 *      @type string $orderClass        Selector class name.
	 *      @type string $parentOrderClass  Parent selector class name.
	 *      @type string $wrapperOrderClass Wrapper selector class name.
	 *      @type string $settings          Custom settings.
	 *      @type string $state             Attributes state.
	 *      @type string $mode              Style mode.
	 *      @type ModuleElements $elements  ModuleElements instance.
	 * }
	 *
	 * @return void
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs']['module']['decoration'] ?? [];

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
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['divider']['advanced']['line'] ?? [],
											'declarationFunction' => [ self::class, 'divider_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']}:before",
											'attr'     => $attrs['divider']['advanced']['line'] ?? [],
											'declarationFunction' => [ self::class, 'divider_style_declaration_line_border' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']}:before",
											'attr'     => $attrs['module']['decoration']['spacing'] ?? [],
											'declarationFunction' => [ self::class, 'divider_style_declaration_line_spacing' ],
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
											'attr' => $attrs['module']['decoration']['position'] ?? [],
											'declarationFunction' => [ self::class, 'position_width_style_declaration' ],
										],
									],
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
	 * Render callback for the Divider module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ DividerEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 *
	 * @return string The HTML rendered output of the Divider module.
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
	 * DividerModule::render_callback( $attrs, $content, $block, $elements );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {
		$rendered_divider = HTMLUtility::render(
			[
				'tag'        => 'div',
				'attributes' => [
					'class' => 'et_pb_divider_internal',
				],
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
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'moduleCategory'      => $block->block_type->category,
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $rendered_divider,
			]
		);
	}

	/**
	 * Divider style declaration.
	 *
	 * This function is used to generate the initial style declaration for the divider module, defining the `box-sizing` property.
	 *
	 * @since ??
	 *
	 * @return string The generated CSS style declaration.
	 */
	public static function divider_style_declaration(): string {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
			]
		);

		$style_declarations->add( 'box-sizing', 'content-box' );

		return $style_declarations->value();
	}

	/**
	 * Generate style declarations for a line border divider.
	 *
	 * This function takes an array of arguments to generate style declarations
	 * for a line border divider. The generated style declarations can be used
	 * to add CSS for displaying a line border based on the provided attributes.
	 * The function supports different customization options such as the
	 * position, style, color, and weight of the line border.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array  $attrValue  The value (breakpoint > state > value) of the module attribute.
	 *     @type string $returnType Optional. The type of value that the function will return.
	 *                              Can be either "string" or "key_value_pair". Default is "string".
	 * }
	 *
	 * @return string|array The generated style declarations as a string.
	 *
	 * @example
	 * ```php
	 *      $params = [
	 *          'attrValue' => [
	 *              'show' => 'on',
	 *              'position' => 'center',
	 *              'style' => 'dashed',
	 *              'color' => '#a0ce4e',
	 *              'weight' => '4px',
	 *          ],
	 *      ];
	 *
	 *      $style = DividerModule::divider_style_declaration_line_border( $params );
	 *      echo $style;
	 *
	 *      // Output:
	 *      // border-top-style: dashed;
	 *      // border-top-color: #a0ce4e;
	 *      // border-top-width: 4px;
	 *      // top: 50% !important;
	 * ```
	 *
	 * @example
	 * ```php
	 *      $params = [
	 *          'attrValue' => [
	 *              'show' => 'off',
	 *          ],
	 *      ];
	 *
	 *      $style = DividerModule::divider_style_declaration_line_border( $params );
	 *      print_r( $style );
	 *
	 *      // Output:
	 *      // Array (
	 *      //    [border-top-style] => solid
	 *      //    [border-top-color] => #7ebec5
	 *      //    [border-top-width] => 1px
	 *      //    [top] => 50% !important
	 *      // )
	 * ```
	 */
	public static function divider_style_declaration_line_border( array $params ) {
		$divider_attr = $params['attrValue'];
		$show_divider = $divider_attr['show'] ?? 'on';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => $params['returnType'] ?? 'string',
			]
		);

		if ( 'off' === $show_divider ) {
			return '';
		}

		if ( ! empty( $divider_attr['position'] ) ) {
			switch ( $divider_attr['position'] ) {
				case 'center':
					$style_declarations->add( 'top', '50% !important' );
					break;
				case 'bottom':
					$style_declarations->add( 'top', 'auto !important' );
					$style_declarations->add( 'bottom', '0 !important' );
					break;
				default:
					$style_declarations->add( 'top', '0' );
			}
		}

		if ( isset( $divider_attr['color'] ) ) {
			$style_declarations->add( 'border-top-color', $divider_attr['color'] );
		}

		if ( isset( $divider_attr['style'] ) ) {
			$style_declarations->add( 'border-top-style', $divider_attr['style'] );
		}

		if ( isset( $divider_attr['weight'] ) ) {
			$style_declarations->add( 'border-top-width', $divider_attr['weight'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Generate style declarations for divider line spacing.
	 *
	 * This function generates style declarations for the spacing of a divider
	 * line. It takes an array of arguments as input and returns the generated
	 * style declarations as output.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments for generating the style declarations.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of the module attribute.
	 *     @type bool|array $important  Optional. If set to true, the CSS will be added with !important.
	 *     @type string     $returnType Optional. The type of value that the function will return.
	 *                                  Can be either string or key_value_pair. Default is 'string'.
	 * }
	 *
	 * @return string|array The generated style declarations.
	 *
	 * @example
	 * ```php
	 *     DividerModule::divider_style_declaration_line_spacing( [
	 *         'attrValue'  => [ 'padding' => [ 'top' => '10px', 'bottom' => '20px' ] ],
	 *         'important'  => true,
	 *         'returnType' => 'key_value_pair',
	 *     ] );
	 *     // Output:
	 *     // Array (
	 *     //    [width] => auto
	 *     //    [top] => 10px
	 *     //     [right] => 0
	 *     //     [left] => 0
	 *     //     [bottom] => 20px
	 *     // )
	 * ```
	 */
	public static function divider_style_declaration_line_spacing( array $params ) {
		$padding = $params['attrValue']['padding'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => $params['returnType'] ?? 'string',
			]
		);

		if ( ! $padding ) {
			return '';
		}

		$padding_top    = $padding['top'] ?? '';
		$padding_right  = $padding['right'] ?? '';
		$padding_left   = $padding['left'] ?? '';
		$padding_bottom = $padding['bottom'] ?? '';

		if ( $padding_top || $padding_right || $padding_left || $padding_bottom ) {
			$style_declarations->add( 'width', 'auto' );

			if ( $padding_top ) {
				$style_declarations->add( 'top', $padding_top );
			} else {
				$style_declarations->add( 'top', '0' );
			}

			if ( $padding_right ) {
				$style_declarations->add( 'right', $padding_right );
			} else {
				$style_declarations->add( 'right', '0' );
			}

			if ( $padding_left ) {
				$style_declarations->add( 'left', $padding_left );
			} else {
				$style_declarations->add( 'left', '0' );
			}
		}

		return $style_declarations->value();
	}

	/**
	 * Loads `DividerModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/divider/';

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
