<?php
/**
 * Module Library: Icon Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use WP_Block_Type_Registry;
use WP_Block;

/**
 * IconModule class.
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
class IconModule implements DependencyInterface {

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Icon module.
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
	 * IconModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						[
							'link' => $attrs['icon']['innerContent'] ?? [],
						],
						$attrs['module']['decoration'] ?? []
					),
				]
			)
		);
	}

	/**
	 * Icon module script data.
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
	 * IconModule::module_script_data( $args );
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
				'setContent'    => [
					[
						'selector'      => $selector . ' .et_pb_icon_wrap .et-pb-icon',
						'data'          => $attrs['icon']['innerContent'] ?? [],
						'valueResolver' => function ( $value ) {

							// process_font_icon returns `null` or falsey, empty string needs to be returned
							// because MultiViewUtils::populate_data_content() throws exception if `valueResolver` returns `null`.
							$font_icon = Utils::process_font_icon( $value );

							return $font_icon ? $font_icon : '';
						},
					],
				],
			]
		);
	}

	/**
	 * Render callback for the Icon module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ IconEdit}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by Divi Builder.
	 * @param string         $content                     The block's content.
	 * @param WP_Block       $block                       Parsed block object that is being rendered.
	 * @param ModuleElements $elements                    An instance of the ModuleElements class.
	 *
	 * @return string The HTML rendered output of the Icon module.
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
	 * IconModule::render_callback( $attrs, $content, $block, $elements );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {
		$target_value = $attrs['icon']['innerContent']['desktop']['value']['target'] ?? '';
		$target       = 'on' === $target_value ? '_blank' : '';
		$title        = $attrs['icon']['innerContent']['desktop']['value']['title'] ?? '';
		$link         = $attrs['icon']['innerContent']['desktop']['value']['url'] ?? '';

		$icon_classnames = [ 'et-pb-icon' ];
		$children        = '';

		$rendered_icon = HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => implode( ' ', $icon_classnames ),
				],
				'childrenSanitizer' => 'esc_html',
				'children'          => Utils::process_font_icon( $attrs['icon']['innerContent']['desktop']['value'] ?? [] ),
			]
		);

		if ( ! empty( $link ) ) {
			$rendered_icon = HTMLUtility::render(
				[
					'tag'               => 'a',
					'attributes'        => [
						'href'   => esc_url( $link ),
						'target' => esc_attr( $target ),
						'title'  => esc_html( $title ),
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => $rendered_icon,
				]
			);
		}

		$icon_wrapper = HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_icon_wrap' => true,
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $rendered_icon,
			]
		);

		$children .= $icon_wrapper;

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'moduleClassName'     => 'et_pb_icon',
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'children'            => $children,
			]
		);
	}

	/**
	 * Get the custom CSS fields for the Divi Icon module.
	 *
	 * This function retrieves the custom CSS fields defined for the Divi icon module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi icon module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the icon module.
	 * ```
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/icon' )->customCssFields;
	}

	/**
	 * Icon module style declaration
	 *
	 * This function will declare icon style for Icon module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string The CSS for icon style.
	 */
	public static function icon_style_declaration( array $params ): string {
		$icon_attr = $params['attrValue'];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'font-family' => true,
					'font-weight' => true,
				],
			]
		);

		if ( isset( $icon_attr['type'] ) ) {
			$font_family = 'fa' === $icon_attr['type'] ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', $font_family );
		}

		if ( ! empty( $icon_attr['weight'] ) ) {
			$style_declarations->add( 'font-weight', $icon_attr['weight'] );
		}

		if ( ! empty( $icon_attr['color'] ) ) {
			$style_declarations->add( 'color', $icon_attr['color'] );
		}

		if ( ! empty( $icon_attr['size'] ) ) {
			$style_declarations->add( 'font-size', $icon_attr['size'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Content alignment style declaration
	 *
	 * This function will declare content alignment style for Icon module.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  If set to true, the CSS will be added with !important.
	 * }
	 *
	 * @return string The CSS for icon style.
	 */
	public static function content_alignment_style_declaration( array $params ): string {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
			]
		);

		if ( 'center' !== $params['attrValue'] ) {
			$style_declarations->add( 'text-align', $params['attrValue'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Icon alignment
	 *
	 * This function will declare alignment style for Icon module.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 *
	 * @return string The CSS for icon alignment.
	 */
	public static function icon_alignment_declaration( array $params ): string {
		$alignment_attr = $params['attrValue'];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( $alignment_attr ) {
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
				default:
					break;
			}
		}

		return $style_declarations->value();
	}

	/**
	 * Icon Module's style components.
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
	 *     @type string         $id                Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *     @type string         $name              Module name.
	 *     @type string         $attrs             Module attributes.
	 *     @type string         $parentAttrs       Parent attrs.
	 *     @type string         $orderClass        Selector class name.
	 *     @type string         $parentOrderClass  Parent selector class name.
	 *     @type string         $wrapperOrderClass Wrapper selector class name.
	 *     @type string         $settings          Custom settings.
	 *     @type string         $state             Attributes state.
	 *     @type string         $mode              Style mode.
	 *     @type int            $orderIndex        Module order index.
	 *     @type int            $storeInstance     The ID of instance where this block stored in BlockParserStore class.
	 *     @type ModuleElements $elements          ModuleElements instance.
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
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),

					// Common Style.
					$elements->style(
						[
							'attrName'   => 'icon',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'],
											'attr'     => $attrs['icon']['advanced']['align'] ?? [],
											'declarationFunction' => [ self::class, 'icon_alignment_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['icon']['innerContent'] ?? [],
											'declarationFunction' => [ self::class, 'icon_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr'     => $attrs['icon']['advanced']['color'] ?? [],
											'property' => 'color',
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr'     => $attrs['icon']['advanced']['size'] ?? [],
											'property' => 'font-size',
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
	 * Loads `IconModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/icon/';

		add_filter( 'divi_conversion_presets_attrs_map', array( IconPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
