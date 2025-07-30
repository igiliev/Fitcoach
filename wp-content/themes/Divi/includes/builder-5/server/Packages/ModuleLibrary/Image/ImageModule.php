<?php
/**
 * Module Library: Image Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Image;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils as IconFontUtils;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Sizing\SizingStyle;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use Exception;
use WP_Block;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupAttrNameResolved;

/**
 * ImageModule class.
 *
 * This class implements the functionality of an icon component in a frontend
 * application. It provides functions for rendering the icon, managing REST API
 * endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class ImageModule implements DependencyInterface {

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Image module.
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
	 * ImageModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$show_bottom_space        = $attrs['module']['advanced']['spacing']['desktop']['value']['showBottomSpace'] ?? 'on';
		$show_bottom_space_tablet = $attrs['module']['advanced']['spacing']['tablet']['value']['showBottomSpace'] ?? null;
		$show_bottom_space_phone  = $attrs['module']['advanced']['spacing']['phone']['value']['showBottomSpace'] ?? null;

		$url              = $attrs['image']['innerContent']['desktop']['value']['linkUrl'] ?? '';
		$show_in_lightbox = $attrs['image']['advanced']['lightbox']['desktop']['value'] ?? 'off';
		$use_overlay      = $attrs['image']['advanced']['overlay']['desktop']['value']['use'] ?? 'off';
		$is_lightbox      = 'on' === $show_in_lightbox;
		$is_overlay       = 'on' === $use_overlay && ( $is_lightbox || ( ! $is_lightbox && '' !== $url ) );

		$classnames_instance->add( 'et_pb_image_bottom_space_tablet', 'on' === $show_bottom_space_tablet );
		$classnames_instance->add( 'et_pb_image_bottom_space_phone', 'on' === $show_bottom_space_phone );
		$classnames_instance->add( 'et_pb_image_sticky', 'off' === $show_bottom_space );
		$classnames_instance->add( 'et_pb_image_sticky_tablet', 'off' === $show_bottom_space_tablet );
		$classnames_instance->add( 'et_pb_image_sticky_phone', 'off' === $show_bottom_space_phone );
		$classnames_instance->add( 'et_pb_has_overlay', $is_overlay );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'border' => $attrs['image']['decoration']['border'] ?? [],
						]
					),
				]
			)
		);
	}

	/**
	 * Image module script data.
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
	 * ImageModule::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( array $args ): void {
		// Assign variables.
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName'      => 'module',
				// Image module doesn't have link script data.
				'attrsResolver' => function ( $attrs_to_resolve ) {
					if ( isset( $attrs_to_resolve['link'] ) ) {
						unset( $attrs_to_resolve['link'] );
					}

					return $attrs_to_resolve;
				},
			]
		);
	}

	/**
	 * Alignment style declaration.
	 *
	 * This function will declare alignment style for Image module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string The alignment style declaration.
	 *
	 * @example: Declare alignment style for Image module.
	 * ```php
	 * $params = [
	 *   'attrValue' => [
	 *     'alignment' => 'left',
	 *     // ... other attributes
	 *   ],
	 * ];
	 * $style = ImageModule::alignment_style_declaration( $params );
	 *
	 * echo $style;
	 *
	 * // Output: 'text-align: left; margin-left: 0;'
	 * ```
	 */
	public static function alignment_style_declaration( array $params ): string {
		$alignment_attr = $params['attrValue'];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( ! empty( $alignment_attr ) ) {
			switch ( $alignment_attr ) {
				case 'left':
					$style_declarations->add( 'text-align', 'left' );
					$style_declarations->add( 'margin-left', '0' );
					break;
				case 'center':
					$style_declarations->add( 'text-align', 'center' );
					break;
				case 'right':
					$style_declarations->add( 'text-align', 'right' );
					$style_declarations->add( 'margin-right', '0' );
					break;
			}
		}

		return $style_declarations->value();
	}

	/**
	 * Fullwidth module style declaration.
	 *
	 * This function will declare fullwidth module style for Image module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string
	 *
	 * @example: Declare fullwidth module style for Image module.
	 * ```php
	 * $params = [
	 *   'attrValue' => [
	 *     'fullwidth' => 'on',
	 *     // ... other attributes
	 *   ],
	 * ];
	 * $style = ImageModule::fullwidth_module_style_declaration( $params );
	 * // Result: 'width: 100%; max-width: 100% !important'
	 * ```
	 */
	public static function fullwidth_module_style_declaration( array $params ): string {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => $params['returnType'] ?? 'array',
				'important'  => false,
			]
		);

		$force_fullwidth = $params['attrValue']['forceFullwidth'] ?? 'off';

		if ( 'on' === $force_fullwidth ) {
			$style_declarations->add( 'width', '100%' );
		}

		return $style_declarations->value();
	}

	/**
	 * Fullwidth image style declaration.
	 *
	 * This function will declare fullwidth image style for Image module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string
	 *
	 * @example: Declare fullwidth image style for Image module.
	 * ```php
	 * $params = [
	 *   'attrValue' => [
	 *     'fullwidth' => 'on',
	 *     // ... other attributes
	 *   ],
	 * ];
	 * $style = ImageModule::fullwidth_image_style_declaration( $params );
	 * // Result: 'width: 100%'
	 * ```
	 */
	public static function fullwidth_image_style_declaration( array $params ): string {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => $params['returnType'] ?? 'array',
				'important'  => [
					'max-width' => true,
				],
			]
		);

		$force_fullwidth = $params['attrValue']['forceFullwidth'] ?? 'off';

		if ( 'on' === $force_fullwidth ) {
			$style_declarations->add( 'width', '100%' );
			$style_declarations->add( 'max-width', '100%' );
		}

		return $style_declarations->value();
	}

	/**
	 * Declare the overlay background style for the Image module.
	 *
	 * This function takes an array of arguments and declares the overlay background style for the Image module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string The overlay background style declaration.
	 *
	 * @example
	 * ```php
	 * $params = array(
	 *     'attrValue' => array(
	 *         'backgroundColor' => '#000000'
	 *     ),
	 *     'important' => true,
	 * );
	 * ImageModule::overlay_background_style_declaration( $params );
	 * // Result: 'background-color: #000000;'
	 * ```
	 */
	public static function overlay_background_style_declaration( array $params ): string {
		$overlay_attr = $params['attrValue'];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => $params['important'],
			]
		);

		if ( ! empty( $overlay_attr['backgroundColor'] ) ) {
			$style_declarations->add( 'background-color', $overlay_attr['backgroundColor'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Get overlay icon style declaration for Fullwidth Image module.
	 *
	 * This function takes an array of parameters and returns a CSS style
	 * declaration for the overlay icon. The style declaration includes
	 * properties such as color, font-family, and font-weight. It uses the
	 * values provided in the parameters to generate the style declaration.
	 *
	 * @since ??
	 *
	 * @param array $params An array of parameters.
	 *
	 * @throws Exception Throws an exception if the hover icon type is not supported.
	 *
	 * @return string The CSS style declaration for the overlay icon.
	 *
	 * @example
	 * ```php
	 * $params = [
	 *   'attrValue' => [
	 *     'hoverIcon' => [
	 *       'type' => 'font',
	 *       'weight' => 400
	 *     ],
	 *     'iconColor' => '#ff0000'
	 *   ],
	 * ];
	 * $style = ImageModule::overlay_icon_style_declaration( $params );
	 * // Result: 'color: #ff0000; font-weight: 400;'
	 * ```
	 */
	public static function overlay_icon_style_declaration( array $params ): string {
		$overlay_icon_attr = $params['attrValue'];
		$hover_icon        = $overlay_icon_attr['hoverIcon'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => true,
			]
		);

		if ( ! empty( $overlay_icon_attr['iconColor'] ) ) {
			$style_declarations->add( 'color', $overlay_icon_attr['iconColor'] );
		}

		$font_icon = IconFontUtils::escape_font_icon( IconFontUtils::process_font_icon( $hover_icon ) );

		if ( ! empty( $hover_icon['type'] ) ) {
			$font_family = IconFontUtils::is_fa_icon( $hover_icon ) ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', "'{$font_family}'" );
			$style_declarations->add( 'content', "'{$font_icon}'" );
		}

		if ( ! empty( $hover_icon['weight'] ) ) {
			$style_declarations->add( 'font-weight', $hover_icon['weight'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Overflow style declaration.
	 *
	 * This function is responsible for declaring the overflow style for the Image module.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array  $attrValue Optional. The value (breakpoint > state > value) of the module attribute. Default `[]`.
	 * }
	 *
	 * @return string The value of the overflow style declaration.
	 *
	 * @example:
	 * ```php
	 * $params = [
	 *     'attrValue' => [
	 *         'radius' => true,
	 *     ],
	 *     'important' => false,
	 *     'returnType' => 'string',
	 * ];
	 *
	 * FilterablePortfolioModule::overflow_style_declaration($params);
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
	 * Style declaration for SVG images.
	 *
	 * This function is responsible for declaring the display style for SVG images.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array  $attrValue Optional. The value (breakpoint > state > value) of the module attribute. Default `[]`.
	 * }
	 *
	 * @return string The value of the display style declaration.
	 *
	 * @example:
	 * ```php
	 * $params = [
	 *     'attrValue' => [
	 *         'src' => 'https://example.com/image.svg?version=1.2.3',
	 *     ],
	 *     'important' => false,
	 *     'returnType' => 'string',
	 * ];
	 *
	 * ImageModule::svg_style_declaration($params);
	 * ```
	 */
	public static function svg_style_declaration( $params ) {
		$attr_value = $params['attrValue'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$src = $attr_value['src'] ?? '';

		// Use pathinfo to get the file extension.
		$src_pathinfo = pathinfo( $src );
		$is_src_svg   = isset( $src_pathinfo['extension'] ) ? 'svg' === $src_pathinfo['extension'] : false;

		if ( $is_src_svg ) {
			$style_declarations->add( 'display', 'block' );
		}

		return $style_declarations->value();
	}

	/**
	 * Style declaration for image absolute positioning.
	 *
	 * This function ensures that images maintain their intrinsic width while allowing horizontal offsets
	 * to affect position instead of resizing the image. This is specifically required for the Image Module.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array  $attrValue Optional. The value (breakpoint > state > value) of the module attribute. Default `[]`.
	 * }
	 *
	 * @return string The value of the position style declaration.
	 *
	 * @example
	 * ```php
	 * $params = [
	 *     'attrValue' => [
	 *         'mode' => 'absolute',
	 *     ],
	 * ];
	 *
	 * ImageModule::position_absolute_style_declaration($params);
	 * ```
	 */
	public static function position_absolute_style_declaration( $params ) {
		$attr_value = $params['attrValue'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		// This is required to fix the issue with Horizontal Positioning
		// for Image Module https://github.com/elegantthemes/Divi/issues/41068.
		if ( 'absolute' === $attr_value['mode'] ) {
			$style_declarations->add( 'width', 'max-content' );
			$style_declarations->add( 'max-width', '100%' );
		}

		return $style_declarations->value();
	}

	/**
	 * Image Module's style components.
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
										// Important: This style must be added before `divi/image-spacing`
										// and `divi/image-sizing` to make sure the width and max-width
										// works correctly when position is absolute.
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['decoration']['position'] ?? [],
											'declarationFunction' => [ self::class, 'position_absolute_style_declaration' ],
										],
									],
									[
										// Custom Styles.
										// Important: This style must be added before `divi/image-sizing` to make sure the module alignment is correct.
										'componentName' => 'divi/image-spacing',
										'props'         => [
											'attr'      => $attrs['module']['advanced']['spacing'] ?? [],
											'important' => [
												'desktop' => [
													'value' => [
														'margin' => true,
													],
												],
											],
										],
									],
									[
										'componentName' => 'divi/image-sizing',
										'props'         => [
											'imageSelector' => "{$args['orderClass']} .et_pb_image_wrap img",
											'attr' => $attrs['module']['advanced']['sizing'] ?? [],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['advanced']['align'] ?? [],
											'declarationFunction' => [ self::class, 'alignment_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['advanced']['sizing'] ?? [],
											'declarationFunction' => [ self::class, 'fullwidth_module_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} img",
													"{$args['orderClass']} .et_pb_image_wrap",
												]
											),
											'attr'     => $attrs['module']['advanced']['sizing'] ?? [],
											'declarationFunction' => [ self::class, 'fullwidth_image_style_declaration' ],
										],
									],
								],
							],
						]
					),
					// Image.
					$elements->style(
						[
							'attrName'   => 'image',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']} .et_overlay",
											'attr'     => $attrs['image']['advanced']['overlay'] ?? [],
											'declarationFunction' => [ self::class, 'overlay_background_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']} .et_overlay:before",
											'attr'     => $attrs['image']['advanced']['overlayIcon'] ?? [],
											'declarationFunction' => [ self::class, 'overlay_icon_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_pb_image_wrap',
											'attr'     => $attrs['image']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_pb_image_wrap',
											'attr'     => $attrs['image']['innerContent'] ?? [],
											'declarationFunction' => [ self::class, 'svg_style_declaration' ],
										],
									],
								],
							],
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
	 * Render callback for the Image module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ ImageEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 *
	 * @return string The HTML rendered output of the Image module.
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
	 * ImageModule::render_callback( $attrs, $content, $block, $elements );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {

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
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $elements->render(
					[
						'attrName'              => 'image',
						'imageWrapperClassName' => 'et_pb_image_wrap',
					]
				),
			]
		);
	}

	/**
	 * Loads `ImageModule` and registers Front-End render callback.
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/image/';

		add_filter( 'divi_conversion_presets_attrs_map', array( ImagePresetAttrsMap::class, 'get_map' ), 10, 2 );

		// Ensure that all filters and actions applied during module registration are registered before calling `ModuleRegistration::register_module()`.
		// However, for consistency, register all module-specific filters and actions prior to invoking `ModuleRegistration::register_module()`.
		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);
	}

	/**
	 * Resolve the group preset attribute name for the Image module.
	 *
	 * @param GlobalPresetItemGroupAttrNameResolved|null $attr_name_to_resolve The attribute name to be resolved.
	 * @param array                                      $params               The filter parameters.
	 *
	 * @return GlobalPresetItemGroupAttrNameResolved|null The resolved attribute name.
	 */
	public static function option_group_preset_resolver_attr_name( $attr_name_to_resolve, array $params ):?GlobalPresetItemGroupAttrNameResolved {
		// Bydefault, $attr_name_to_resolve is a null value.
		// If it is not null, it means that the attribute name is already resolved.
		// In this case, we return the resolved attribute name.
		if ( null !== $attr_name_to_resolve ) {
			return $attr_name_to_resolve;
		}

		if ( $params['moduleName'] !== $params['dataModuleName'] ) {
			if ( 'divi/image' === $params['moduleName'] ) {
				if ( 'module.advanced.sizing' === $params['attrName'] ) {
					return new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => 'module.decoration.sizing',
							'attrSubName' => $params['attrSubName'] ?? null,
						]
					);
				}

				if ( 'module.advanced.spacing' === $params['attrName'] ) {
					return new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => 'module.decoration.spacing',
							'attrSubName' => $params['attrSubName'] ?? null,
						]
					);
				}
			}

			if ( 'divi/image' === $params['dataModuleName'] ) {
				if ( 'module.decoration.sizing' === $params['attrName'] ) {
					return new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => 'module.advanced.sizing',
							'attrSubName' => $params['attrSubName'] ?? null,
						]
					);
				}

				if ( 'module.decoration.spacing' === $params['attrName'] ) {
					return new GlobalPresetItemGroupAttrNameResolved(
						[
							'attrName'    => 'module.advanced.spacing',
							'attrSubName' => $params['attrSubName'] ?? null,
						]
					);
				}
			}
		}

		return $attr_name_to_resolve;
	}
}
