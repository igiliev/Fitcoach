<?php
/**
 * Module Library: Audio Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Audio;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowClassnames;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block_Type_Registry;
use WP_Block;

/**
 * AudioModule class.
 *
 * This class implements the functionality of an audio component in a frontend
 * application. It provides functions for rendering the audio, managing REST API
 * endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class AudioModule implements DependencyInterface {

	/**
	 * Render callback for the Audio module.
	 *
	 * This function is responsible for rendering the server-side HTML of the
	 * module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ AudioEdit}
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
	 * @return string The HTML rendered output of the Audio module.
	 *
	 * @example
	 * ```php
	 * $attrs = [
	 *  'audio' => [
	 *   'innerContent' => [
	 *    'desktop' => [
	 *      'value' => 'https://example.com/audio.mp3',
	 *     ],
	 *   ],
	 *   'title' => [
	 *     'innerContent' => [
	 *       'desktop' => [
	 *         'value' => 'Title',
	 *       ],
	 *     ],
	 *   ],
	 *   'artistName' => [
	 *     'innerContent' => [
	 *       'desktop' => [
	 *         'value' => 'Artist Name',
	 *       ],
	 *     ],
	 *   ],
	 * ];
	 * $content = '';
	 * $block = new WP_Block( [
	 *   'id' => '123',
	 *   'name' => 'et_pb_audio',
	 *   'orderIndex' => 0,
	 *   'storeInstance' => '123',
	 * ] );
	 * $elements = new ModuleElements( $attrs );
	 * $default_printed_style_attrs = [];
	 *
	 * AudioModule::render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {

		$audio = $attrs['audio']['innerContent']['desktop']['value'] ?? '';

		$has_image_url = ModuleUtils::has_value(
			$attrs['image']['innerContent'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return ! empty( $value['src'] );
				},
			]
		);

		$image_link = $attrs['image']['innerContent']['desktop']['value']['src'] ?? '';

		$title = $elements->render(
			[
				'attrName' => 'title',
			]
		);

		$image_url = $has_image_url ? HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_audio_cover_art' => ! empty( $image_link ),
						],
						BoxShadowClassnames::has_overlay( $attrs['image']['decoration']['boxShadow'] ?? [] )
					),
					'style' => [
						'background-image' => sprintf( 'url(%1$s)', esc_url( $image_link ) ),
					],
				],
				'children'          => $elements->style_components(
					[
						'attrName' => 'image',
					]
				),
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		$meta = self::render_element_meta(
			[
				'artistName' => $attrs['artistName']['innerContent']['desktop']['value'] ?? '',
				'albumName'  => $attrs['albumName']['innerContent']['desktop']['value'] ?? '',
				'no_wrapper' => false,
				'elements'   => $elements,
			]
		);

		// Some themes do not include these styles/scripts so we need to enqueue them in this module.
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'et-builder-mediaelement' );

		// Remove all filters from WP audio shortcode to make sure current theme doesn't add any elements into audio module.
		remove_all_filters( 'wp_audio_shortcode_library' );
		remove_all_filters( 'wp_audio_shortcode' );
		remove_all_filters( 'wp_audio_shortcode_class' );

		$children = et_core_esc_previously( $image_url ) . HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_audio_module_content et_audio_container',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => et_core_esc_previously( $title ) . et_core_esc_previously( $meta ) . et_core_esc_previously( do_shortcode( sprintf( '[audio src="%s" /]', esc_url( $audio ) ) ) ),
			]
		);

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
				'name'                     => $block->name,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'moduleCategory'           => $block->block_type->category,
				'parentAttrs'              => $parent->attrs ?? [],
				'parentId'                 => $parent->id ?? '',
				'children'                 => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $children,
			]
		);
	}

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Audio module.
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
	 *   'classnamesInstance' => $classnamesInstance,
	 *   'attrs' => $attrs,
	 * ];
	 *
	 * AudioModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Module classname.
		$classnames_instance->add( [ 'et_pb_audio_module', 'clearfix' ], true );

		// Text Options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

		$background_layout      = $attrs['module']['advanced']['text']['text']['desktop']['value']['color'] ?? '';
		$text_layout_class_name = 'light' === $background_layout ? [ 'et_pb_text_color_dark' ] : '';
		$classnames_instance->add( $text_layout_class_name );

		// Image Url classname.
		$image_url = $attrs['image']['innerContent']['desktop']['value']['src'] ?? '';
		if ( ! $image_url ) {
			$classnames_instance->add( 'et_pb_audio_no_image' );
		}

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
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
	 * Audio module script data.
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
	 *   'id'             => 'my-module',
	 *   'name'           => 'My Module',
	 *   'selector'       => '.my-module',
	 *   'attrs'          => array(
	 *     'portfolio' => array(
	 *       'advanced' => array(
	 *         'showTitle'       => false,
	 *         'showCategories'  => true,
	 *         'showPagination' => true,
	 *       )
	 *     )
	 *   ),
	 *   'elements'       => $elements,
	 *   'store_instance' => 123,
	 * );
	 *
	 * AudioModule::module_script_data( $args );
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
						'selector'      => $selector . ' .et_audio_module_meta',
						'data'          => MultiViewUtils::merge_values(
							[
								'artistName' => $attrs['artistName']['innerContent'] ?? [],
								'albumName'  => $attrs['albumName']['innerContent'] ?? [],
							]
						),
						'valueResolver' => function ( $value ) use ( $elements ) {
							$artist_name = $value['artistName'] ?? '';
							$album_name  = $value['albumName'] ?? '';

							return AudioModule::render_element_meta(
								[
									'artistName' => $artist_name,
									'albumName'  => $album_name,
									'no_wrapper' => true,
									'elements'   => $elements,
								]
							);
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
				],
				'setStyle'      => [
					[
						'selector'      => $selector . ' > div:nth-child(1)',
						'data'          => [
							'background-image' => $attrs['image']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'url(' . ( $value['src'] ?? '' ) . ')';
						},
					],
				],
				'setClassName'  => [
					[
						'selector'      => $selector . ' > div:nth-child(1)',
						'data'          => [
							'et_pb_audio_cover_art' => $attrs['image']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return '' !== ( $value['src'] ?? '' ) ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_audio_no_image' => $attrs['image']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return '' === ( $value['src'] ?? '' ) ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * Get the custom CSS fields for the Divi Audio module.
	 *
	 * This function retrieves the custom CSS fields defined for the Divi audio module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi audio module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the audio module.
	 * ```
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/audio' )->customCssFields;
	}

	/**
	 * Overflow style declaration.
	 *
	 * This function is responsible for declaring the overflow style for the Audio module.
	 *
	 * This function is the equivalent of the `overflowStyleDeclaration` JS function located in
	 * visual-builder/packages/module-library/src/components/audio/style-declarations/overflow/index.ts.
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
	 * AudioModule::overflow_style_declaration($params);
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
	 * Retrieve the style components for the Audio module.
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
	 *     @type string         $id                       The ID of the module. In VB, the ID of the module is UUIDV4.
	 *                                                    In the frontend (FE), the ID is the order index.
	 *     @type string         $name                     The name of the module.
	 *     @type string         $attrs                    The attributes of the module.
	 *     @type string         $defaultPrintedStyleAttrs The default printed style attributes.
	 *     @type string         $parentAttrs              The parent attrs.
	 *     @type string         $orderClass               The selector class name.
	 *     @type string         $parentOrderClass         The parent selector class name.
	 *     @type string         $wrapperOrderClass        The wrapper selector class name.
	 *     @type string         $settings                 The custom settings.
	 *     @type string         $state                    The attributes state.
	 *     @type string         $mode                     The style mode.
	 *     @type ModuleElements $elements                 The ModuleElements instance.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * AudioModule::module_styles( [
	 *   'id'                       => 'module-id',
	 *   'name'                     => 'module-name',
	 *   'attrs'                    => 'module-attributes',
	 *   'defaultPrintedStyleAttrs' => 'default-printed-style-attributes',
	 *   'parentAttrs'              => 'parent-attributes',
	 *   'orderClass'               => 'selector-class',
	 *   'parentOrderClass'         => 'parent-selector-class',
	 *   'wrapperOrderClass'        => 'wrapper-selector-class',
	 *   'settings'                 => 'custom-settings',
	 *   'state'                    => 'attributes-state',
	 *   'mode'                     => 'style-mode',
	 *   'elements'                 => ModuleElements::instance(),
	 * ] );
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
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles'           => [
									[
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_pb_audio_module_content',
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
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

					// Image.
					$elements->style(
						[
							'attrName'   => 'image',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_pb_audio_cover_art',
											'attr'     => $attrs['image']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),

					// Title.
					$elements->style(
						[
							'attrName' => 'title',
						]
					),

					// Caption.
					$elements->style(
						[
							'attrName' => 'caption',
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
	 * Render HTML for displaying artist and album metadata for an audio module.
	 *
	 * This function renders a string of HTML code that includes a paragraph tag with a class of
	 * "et_audio_module_meta" and two pieces of information about an audio element: the artist name (if
	 * provided) and the album name (if provided). The artist name is enclosed in a strong tag and
	 * preceded by the text "by". The two pieces of information are separated by a pipe symbol.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string         $artist_name Artist name for the audio element.
	 *     @type string         $album_name  Album name for the audio element.
	 *     @type bool           $no_wrapper  Flag to indicate whether or not to include a paragraph tag with a class.
	 *     @type ModuleElements $elements    An instance of the ModuleElements class.
	 * }
	 *
	 * @return string The rendered HTML of the Audio module.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'artist_name' => 'Artist Name',
	 *     'album_name'  => 'Album Name',
	 *     'no_wrapper'  => false,
	 *     'elements'    => new ModuleElements( $attrs ),
	 * ];
	 * $html = RenderElementMetaTrait::render_element_meta( $args );
	 * ```
	 */
	public static function render_element_meta( array $args = [] ): string {
		$artist_name = $args['artistName'] ?? '';
		$album_name  = $args['albumName'] ?? '';
		$no_wrapper  = $args['no_wrapper'] ?? false;
		$elements    = $args['elements'];
		$items       = [];

		if ( ! empty( $artist_name ) ) {
			$items[] = sprintf(
				et_get_safe_localization( _x( 'by %1$s', 'Audio Module meta information', 'et_builder' ) ),
				$elements->render(
					[
						'attrName' => 'artistName',
					]
				)
			);
		}

		if ( ! empty( $album_name ) ) {
			$items[] = $elements->render(
				[
					'attrName' => 'albumName',
				]
			);
		}

		$children = implode( ' | ', $items );

		if ( $no_wrapper ) {
			return $children;
		}

		return HTMLUtility::render(
			[
				'tag'               => 'p',
				'attributes'        => [
					'class' => 'et_audio_module_meta',
				],
				'children'          => $children,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);
	}

	/**
	 * Load the Audio Module.
	 *
	 * This function is responsible for loading the AudioModule and registering
	 * the necessary callbacks and REST API endpoints. It retrieves the path of
	 * the AudioModule JSON folder and uses it to register the module with the
	 * ModuleRegistration class. The module is registered with the specified
	 * render callback function, which is a method within the current class.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $module_loader = new ModuleLoader();
	 * $module_loader->load();
	 * ```
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/audio/';

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
