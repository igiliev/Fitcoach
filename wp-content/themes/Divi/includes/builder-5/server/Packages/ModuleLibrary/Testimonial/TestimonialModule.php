<?php
/**
 * ModuleLibrary: Testimonial Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Testimonial;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\Module\Script;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleLibrary\Testimonial\TestimonialPresetAttrsMap;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * TestimonialModule class.
 *
 * This class contains functions used for Testimonial Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class TestimonialModule implements DependencyInterface {

	/**
	 * Render callback function for the Testimonial module.
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
	 * $rendered_content = TestimonialModule::render_callback($attrs, $content, $block, $elements, $default_printed_style_attrs);
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
	 * $rendered_content = TestimonialModule::render_callback($attrs, $content, $block, $elements, $default_printed_style_attrs);
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		// Portrait.
		// phpcs:disable ET.Sniffs.Todo.TodoFound -- Existing TODO from legacy code.
		// TODO feat(D5, Frontend Rendering) this needs to be abstracted into its own component.
		// phpcs:enable ET.Sniffs.Todo.TodoFound
		$portrait = $elements->render(
			[
				'attributes'    => [
					'class' => 'et_pb_testimonial_portrait',
					'style' => [
						'background-image' => [
							'attr'          => $attrs['portrait']['innerContent'] ?? [],
							'subName'       => 'url',
							'valueResolver' => function ( $value ) {
								return 'url(' . ( SanitizerUtility::sanitize_image_src( $value ) ) . ')';
							},
							'selector'      => '{{selector}} .et_pb_testimonial_portrait',
						],
					],
				],
				'hiddenIfFalsy' => [
					'attr'     => $attrs['portrait']['innerContent'] ?? [],
					'subName'  => 'url',
					'selector' => '{{selector}} .et_pb_testimonial_portrait',
				],
			]
		);

		// Content.
		$content = $elements->render(
			[
				'attrName'          => 'content',
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);

		// Description Inner.
		$description_inner = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_testimonial_description_inner',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $content,
			]
		);

		// Author.
		$author = $elements->render(
			[
				'attrName' => 'author',
			]
		);

		$meta_items = [];

		// Separator.
		$separator = $elements->render(
			[
				'tagName'       => 'span',
				'attributes'    => [
					'class' => [
						'et_pb_testimonial_separator' => true,
					],
				],
				'children'      => ', ',
				'hiddenIfFalsy' => [
					'attr'     => $attrs['company']['innerContent'] ?? [],
					'subName'  => 'text',
					'selector' => '{{selector}} .et_pb_testimonial_separator',
				],
			]
		);

		// Job Title.
		$job_title = $elements->render(
			[
				'attrName' => 'jobTitle',
			]
		);

		if ( $job_title ) {
			$meta_items[] = $job_title;
		}

		// Company.
		$company = $elements->render(
			[
				'attrName'    => 'company',
				'attrSubName' => 'text',
			]
		);

		if ( $company ) {
			$company_url        = $attrs['company']['innerContent']['desktop']['value']['linkUrl'] ?? '';
			$company_url_target = $attrs['company']['innerContent']['desktop']['value']['linkTarget'] ?? '';
			$company_target     = 'on' === $company_url_target ? '_blank' : '_self';
			$meta_items[]       = empty( $company_url ) ? $company : $elements->render(
				[
					'tagName'           => 'a',
					'attributes'        => [
						'href'   => $company_url,
						'target' => $company_target,
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => $company,
					'hiddenIfFalsy'     => [
						'attr'     => $attrs['company']['innerContent'] ?? [],
						'subName'  => 'text',
						'selector' => '{{selector}} .et_pb_testimonial_meta a',
					],
				]
			);
		}

		// Meta.
		$meta = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_testimonial_meta',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => implode( $separator, $meta_items ),
			]
		);

		// Description.
		$description = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_testimonial_description',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $description_inner . $author . $meta,
			]
		);

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
				'name'                     => $block->block_type->name,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'moduleCategory'           => $block->block_type->category,
				'children'                 => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $portrait . $description,
			]
		);
	}

	/**
	 * Get the module classnames for the Testimonial module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/testimonial-module-classnames moduleClassnames}
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
	 * // Example 1: Adding classnames for the toggle options.
	 * TestimonialModule::module_classnames( [
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
	 * TestimonialModule::module_classnames( [
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
		$show_icon           = $attrs['quoteIcon']['decoration']['icon']['desktop']['value']['show'] ?? 'on';

		$classnames_instance->add( 'clearfix', true );
		$classnames_instance->add( 'et_pb_icon_off', 'off' === $show_icon );

		// D4 FE outputs `et_pb_testimonial_no_image` class even if there's a image, which is wrong behavior
		// and that interferes with the D5 Responsive Content. So, we need to add the class only if there's no image.
		$portrait_image = $attrs['portrait']['innerContent']['desktop']['value']['url'] ?? '';
		$classnames_instance->add( 'et_pb_testimonial_no_image', empty( $portrait_image ) );

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
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
	 * Generate the script data for the Testimonial module based on the provided arguments.
	 *
	 * This function assigns variables and sets element script data options.
	 * It then uses `MultiViewScriptData` to set module specific FrontEnd (FE) data.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments for generating the script data.
	 *
	 *     @type string         $id            Optional. Module id. Default empty string.
	 *     @type string         $name          Optional. Module name. Default empty string.
	 *     @type array          $attrs         Optional. Module attributes. Default `[]`.
	 *     @type int            $storeInstance Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
	 *     @type ModuleElements $elements      ModuleElements instance.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```
	 * // Generate the script data for a module with specific arguments.
	 * $args = array(
	 *     'id'             => 'my-module',
	 *     'name'           => 'My Module',
	 *     'selector'       => '.my-module',
	 *     'attrs'          => array(
	 *         'testimonial' => array(
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
	 * Testimonial::module_script_data( $args );
	 * ```
	 */
	public static function module_script_data( array $args ): void {
		// Assign variables.
		$id             = $args['id'] ?? '';
		$name           = $args['name'] ?? '';
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
				'selector'      => '{{selector}}',
				'setClassName'  => [
					[
						'data'          => [
							'et_pb_icon_off'             => $attrs['quoteIcon']['decoration']['icon'] ?? [],
							'et_pb_testimonial_no_image' => $attrs['portrait']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value, $resolver_args ) {
							if ( 'et_pb_icon_off' === $resolver_args['className'] ) {
								return 'off' === ( $value['show'] ?? 'on' ) ? 'add' : 'remove'; // Add class `et_pb_icon_off` if `quoteIcon` value is `off`.
							}

							return '' === ( $value['url'] ?? '' ) ? 'add' : 'remove'; // Add class `et_pb_testimonial_no_image` if `portrait.image.url` value is empty.
						},
					],
				],
			]
		);
	}

	/**
	 * Retrieve the custom CSS fields for the "divi/testimonial" block.
	 *
	 * This function returns an array of custom CSS fields for the "divi/testimonial" block.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/testimonial-css-fields cssFields}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields.
	 */
	public static function custom_css(): array {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/testimonial' )->customCssFields;
	}

	/**
	 * Icon style declaration.
	 *
	 * This function declares the style of an icon for a module.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of the module attribute.
	 * }
	 *
	 * @return string The style declarations as a string.
	 */
	public static function icon_style_declaration( array $params ): string {
		$icon_attr = $params['attrValue'] ?? [];
		$use_size  = $icon_attr['useSize'] ?? '';
		$size      = $icon_attr['size'] ?? '';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( 'on' === $use_size && ! empty( $size ) ) {
			$style_declarations->add( 'border-radius', $size );

			// Handle parsed icon size numeric value.
			$icon_size       = SanitizerUtility::numeric_parse_value( $size );
			$icon_size_value = 0 - ( $icon_size['valueNumber'] ?? 0 );

			$style_declarations->add( 'top', 0 !== $icon_size_value ? round( $icon_size_value / 2 ) . $icon_size['valueUnit'] : 0 );
			$style_declarations->add( 'margin-left', 0 !== $icon_size_value ? round( $icon_size_value / 2 ) . $icon_size['valueUnit'] : 0 );
		}

		return $style_declarations->value();
	}

	/**
	 * Get the style declaration for the portrait size based on the given parameters.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     The parameters required to generate the style declaration.
	 *
	 *     @type array $attrValue {
	 *         The value (breakpoint > state > value) of the module attribute.
	 *
	 *         @type string $width  The width of the portrait.
	 *         @type string $height The height of the portrait.
	 *     }
	 * }
	 *
	 * @return string The generated style declaration for the portrait size.
	 *
	 * @example:
	 * ```php
	 * TestimonialModule::portrait_size_style_declaration( [
	 *     'attrValue' => [
	 *         'width' => '200px',
	 *         'height' => '300px',
	 *     ],
	 * ] );
	 *
	 * // Output: "width: 200px; height: 300px;"
	 * ```
	 *
	 * @example:
	 * ```php
	 * TestimonialModule::portrait_size_style_declaration( [
	 *     'attrValue' => [
	 *         'width' => 'auto',
	 *         'height' => '150px',
	 *     ],
	 * ] );
	 *
	 * // Output: "height: 150px;"
	 * ```
	 */
	public static function portrait_size_style_declaration( array $params ): string {
		$sizing_attrs = $params['attrValue'];
		$width        = $sizing_attrs['width'] ?? '';
		$height       = $sizing_attrs['height'] ?? '';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'width'  => true,
					'height' => true,
				],
			]
		);

		if ( ! empty( $width ) ) {
			$style_declarations->add( 'width', $width );
		}

		if ( ! empty( $height ) ) {
			$style_declarations->add( 'height', $height );
		}

		return $style_declarations->value();
	}

	/**
	 * Style declaration for testimonial's border overflow.
	 *
	 * This function is used to generate the style declaration for the border overflow of a testimonial module.
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
	 * Load Testimonial module styles.
	 *
	 * This function is responsible for loading styles for the module. It takes an array of arguments
	 * which includes the module ID, name, attributes, settings, and other details. The function then
	 * uses these arguments to dynamically generate and add the required styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/testimonial-module-styles ModuleStyles}
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
	 *     @type array          $defaultPrintedStyleAttrs Optional. The default printed style attributes. Default `[]`.
	 *     @type string         $orderClass               The selector class name.
	 *     @type int            $orderIndex               The order index of the module.
	 *     @type int            $storeInstance            The ID of instance where this block stored in BlockParserStore.
	 *     @type ModuleElements $elements                 ModuleElements instance.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     TestimonialModule::module_styles([
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
					// Quote Icon.
					$elements->style(
						[
							'attrName'   => 'quoteIcon',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']}:before",
											'attr'     => $attrs['quoteIcon']['decoration']['icon'] ?? [],
											'declarationFunction' => [ self::class, 'icon_style_declaration' ],
										],
									],
								],
							],
						]
					),
					// Portrait.
					$elements->style(
						[
							'attrName' => 'portrait',
						]
					),
					// Title.
					$elements->style(
						[
							'attrName' => 'author',
						]
					),
					// Position Text.
					$elements->style(
						[
							'attrName' => 'jobTitle',
						]
					),
					// Company Text.
					$elements->style(
						[
							'attrName' => 'company',
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
	 * Load the Testimonial module.
	 *
	 * This function is responsible for registering the testimonial module and enqueueing the required script.
	 * It adds an action to the WordPress 'init' hook that calls the `register_module` method
	 * of the `ModuleRegistration` class, passing the module JSON folder path and the render callback
	 * function as arguments.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/testimonial/';

		add_filter( 'divi_conversion_presets_attrs_map', array( TestimonialPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
