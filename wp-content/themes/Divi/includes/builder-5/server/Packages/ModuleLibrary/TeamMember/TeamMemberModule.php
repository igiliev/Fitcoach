<?php
/**
 * ModuleLibrary: Team Member Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\TeamMember;

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
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use WP_Block;

/**
 * `TeamMemberModule` is consisted of functions used for Team Member Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class TeamMemberModule implements DependencyInterface {

	/**
	 * Get the module classnames for the TeamMember module.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/team-member-module-classnames moduleClassnames}
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
	 * @return void
	 *
	 * @example:
	 * ```php
	 * // Example 1: Adding classnames for the toggle options.
	 * TeamMemberModule::module_classnames( [
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
	 * TeamMemberModule::module_classnames( [
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
		$image_url           = $attrs['image']['innerContent']['desktop']['value']['url'] ?? '';

		$classnames_instance->add( 'clearfix', true );
		$classnames_instance->add( 'et_pb_team_member_no_image', empty( $image_url ) );

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
	 * Set TeamMember module script data.
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
	 *     @type string $id             Optional. The ID of the module. Default empty string.
	 *     @type string $name           Optional. The name of the module. Default empty string.
	 *     @type string $selector       Optional. The selector of the module. Default empty string.
	 *     @type array  $attrs          Optional. The attributes of the module. Default `[]`.
	 *     @type object $elements       The elements object.
	 *     @type int    $store_instance Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
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
	 *             'team_member' => array(
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
	 *     TeamMemberModule::module_script_data( $args );
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
							'et_pb_team_member_no_image' => $attrs['image']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return empty( $value['url'] ?? '' ) ? 'add' : 'remove';
						},
					],
				],
				'setVisibility' => [
					[
						'selector'      => $selector . ' .et_pb_team_member_image',
						'data'          => $attrs['image']['innerContent'] ?? [],
						'valueResolver' => function ( $value ) {
							return ! empty( $value['url'] ?? '' ) ? 'visible' : 'hidden';
						},
					],
				],
			]
		);
	}

	/**
	 * Retrieve the custom CSS fields for the 'divi/team-member' block.
	 *
	 * This function returns an array of custom CSS fields for the specified block.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/team-member-css-fields cssFields}
	 * located in `@divi/module-library` package.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 *
	 * @return array The array of custom CSS fields for the 'divi/team-member' block.
	 */
	public static function custom_css(): array {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/team-member' )->customCssFields;
	}

	/**
	 * Generates the style declaration for image overflow for TeamMember module.
	 *
	 * This function accepts an array of parameters and generates a style declaration based on the provided values.
	 * The resulting style declaration can be used to apply CSS styles to an HTML image tag.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of parameters.
	 *
	 *     @type array  $attrValue     The attribute values for the image.
	 * }
	 *
	 * @return string The generated style declaration.
	 *
	 * @example:
	 * ```php
	 *   $params = array(
	 *       'attrValue' => array(
	 *           'radius' => '10px',
	 *       ),
	 *   );
	 *   $styleDeclaration = TeamMemberModule::image_overflow_style_declaration( $params );
	 * ```
	 *
	 * @example:
	 * ```php
	 *   $params = array(
	 *       'attrValue' => array(
	 *           'radius' => null,
	 *       ),
	 *   );
	 *   $styleDeclaration = TeamMemberModule::image_overflow_style_declaration( $params );
	 * ```
	 */
	public static function image_overflow_style_declaration( array $params ): string {
		$radius = $params['attrValue']['radius'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
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
	 * Get the icon size and color style declarations for an icon for TeamMember module.
	 *
	 * This function accepts an array of parameters, including the attribute value for the icon.
	 * The attribute value array should contain information about the size and color of the icon.
	 * The function checks if the size is enabled and adds a "font-size" declaration to the style declarations if it is enabled.
	 * It also adds a "color" declaration to the style declarations if the color is provided.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of parameters, including the attribute value for the icon.
	 *
	 *     @type array $attrValue {
	 *         The value (breakpoint > state > value) of module attribute.
	 *         The attribute value for the icon.
	 *
	 *         @type string $useSize Whether to enable the size of the icon. Default is 'off'.
	 *         @type string $size    The font size of the icon. Required if 'useSize' is 'on'.
	 *         @type string $color   The color of the icon.
	 *    }
	 * }
	 *
	 * @return string The style declarations for the icon, as a string.
	 *
	 * @example:
	 * ```php
	 * $params = [
	 *    'attrValue' => [
	 *        'useSize' => 'on',
	 *        'size' => '14px',
	 *        'color' => '#ff0000',
	 *    ],
	 * ];
	 * echo icon_size_color_style_declaration($params);
	 *
	 * // Output: 'font-size: 14px; color: #ff0000;'
	 * ```
	 *
	 * @example:
	 * ```php
	 * $params = [
	 *    'attrValue' => [
	 *        'useSize' => 'on',
	 *        'size' => '18px',
	 *    ],
	 * ];
	 * echo icon_size_color_style_declaration($params);
	 * // Output: 'font-size: 18px;'
	 * ```
	 *
	 * @example:
	 * ```php
	 * $params = [
	 *    'attrValue' => [
	 *        'color' => '#00ff00',
	 *    ],
	 * ];
	 * echo icon_size_color_style_declaration($params);
	 * // Output: 'color: #00ff00;'
	 * ```
	 */
	public static function icon_size_color_style_declaration( array $params ): string {
		$attr_value = $params['attrValue'];
		$use_size   = $attr_value['useSize'] ?? 'off';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
			]
		);

		if ( 'on' === $use_size && ! empty( $attr_value['size'] ) ) {
			$style_declarations->add( 'font-size', $attr_value['size'] );
		}

		if ( ! empty( $attr_value['color'] ) ) {
			$style_declarations->add( 'color', $attr_value['color'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Load TeamMember module style components.
	 *
	 * This function is responsible for loading styles for the module. It takes an array of arguments
	 * which includes the module ID, name, attributes, settings, and other details. The function then
	 * uses these arguments to dynamically generate and add the required styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/team-member-module-styles ModuleStyles}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id               The module ID. In Visual Builder (VB), the ID of the module is a UUIDV4 string.
	 *                                    In FrontEnd (FE), the ID is the order index.
	 *     @type string $name             The module name.
	 *     @type array  $attrs            Optional. The module attributes. Default `[]`.
	 *     @type array  $settings         Optional. The module settings. Default `[]`.
	 *     @type string $orderClass       The selector class name.
	 *     @type int    $orderIndex       The order index of the module.
	 *     @type int    $storeInstance    The ID of instance where this block stored in BlockParserStore.
	 *     @type ModuleElements $elements ModuleElements instance.
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
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => implode(
												',',
												[
													"{$args['orderClass']} .et_pb_module_header",
													"{$args['orderClass']} .et_pb_member_position",
													"{$args['orderClass']} .et_pb_team_member_description p",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
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
								'attrsFilter'    => function ( $decoration_attrs ) {
									$has_hover_value            = ! empty( $decoration_attrs['border']['desktop']['hover']['styles'] ?? [] );
									$has_sticky_value           = ! empty( $decoration_attrs['border']['desktop']['sticky']['styles'] ?? [] );
									$is_empty_border_in_desktop = empty( $decoration_attrs['border']['desktop']['value']['styles'] ?? [] ) ||
									empty( $decoration_attrs['border']['desktop']['value']['styles']['all']['width'] ?? '' );

									if ( ( $has_hover_value || $has_sticky_value ) && $is_empty_border_in_desktop ) {
										// Add border width to `0px` if hover and/or sticky has value. But desktop don't have value.
										// We need it to resolve the issue for transition. In D4, there was a `et_pb_with_border` class that
										// is adding a static CSS `border: 0 solid #333;`. But in D5, we don't have that class anymore.
										return array_replace_recursive(
											$decoration_attrs,
											[
												'border' => [
													'desktop' => [
														'value' => [
															'styles' => [
																'all' => [
																	'width' => '0px',
																],
															],
														],
													],
												],
											]
										);
									}
									return $decoration_attrs;
								},
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['image']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'image_overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),
					// Social Icon.
					$elements->style(
						[
							'attrName' => 'social',
						]
					),

					// Title.
					$elements->style(
						[
							'attrName' => 'name',
						]
					),

					// Position Text.
					$elements->style(
						[
							'attrName' => 'position',
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
	 * Render callback for the TeamMember module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the FrontEnd (FE).
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/team-member-edit TextEdit}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    The block attributes that were saved by the Visual Builder.
	 * @param string         $content  The block content.
	 * @param WP_Block       $block    The parsed block object that is being rendered.
	 * @param ModuleElements $elements An instance of the ModuleElements class.
	 *
	 * @return string The rendered HTML for the module.
	 *
	 * @example:
	 * ```php
	 * $attrs = [
	 *     'attrName' => 'value',
	 *     //...
	 * ];
	 * $content = 'This is the content';
	 * $block = new WP_Block();
	 * $elements = new ModuleElements();
	 *
	 * $html = TeamMember::render_callback( $attrs, $content, $block, $elements );
	 * echo $html;
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements ): string {

		$name_text = $attrs['name']['innerContent']['desktop']['value'] ?? '';
		$has_image = ModuleUtils::has_value( $attrs['image']['innerContent'] ?? [] );

		// Team Member Image.
		$team_member_image   = $attrs['image']['innerContent']['desktop']['value']['url'] ?? '';
		$has_image_animation = $attrs['image']['innerContent']['desktop']['value']['animation'] ?? 'off';

		$image_name_attrs = self::merge_image_name_attrs( $attrs['image']['innerContent'] ?? [], $attrs['name']['innerContent'] ?? [] );

		$image = $elements->render(
			[
				'attrName'    => 'image',
				'elementAttr' => $image_name_attrs,
			]
		);

		// Team Member Image Container.
		$image_container = $has_image ? HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_team_member_image et-waypoint et_pb_animation_' . $has_image_animation,
				],
				'children'          => $image,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		// Team Member Name.
		$name = $elements->render(
			[
				'attrName' => 'name',
			]
		);

		// Team Member Position.
		$position = $elements->render(
			[
				'attrName' => 'position',
			]
		);

		// Content.
		$content = $elements->render(
			[
				'attrName' => 'content',
			]
		);

		// Social Icons.
		$social_media_items = [
			'facebook' => 'Facebook',
			'twitter'  => 'Twitter',
			'google'   => 'Google+',
			'linkedin' => 'LinkedIn',
		];

		$social_media_links = [];

		foreach ( $social_media_items as $social_media_item => $social_media_item_name ) {
			$social_media_link = $attrs['social']['innerContent']['desktop']['value'][ "{$social_media_item}Url" ] ?? '';
			if ( ! empty( $social_media_link ) ) {
				$social_media_links[] = HTMLUtility::render(
					[
						'tag'               => 'li',
						'children'          => HTMLUtility::render(
							[
								'tag'               => 'a',
								'attributes'        => [
									'href'  => $social_media_link,
									'class' => "et_pb_font_icon et_pb_{$social_media_item}_icon",
									'title' => $social_media_item_name,
								],
								'children'          => HTMLUtility::render(
									[
										'tag'      => 'span',
										'children' => $social_media_item_name,
										'childrenSanitizer' => 'esc_html',
									]
								),
								'childrenSanitizer' => 'et_core_esc_previously',
							]
						),
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				);
			}
		}

		$social_media = ! empty( $social_media_links ) ? HTMLUtility::render(
			[
				'tag'               => 'ul',
				'attributes'        => [
					'class' => 'et_pb_member_social_links',
				],
				'children'          => $social_media_links,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : '';

		// Member Description.
		$description = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_team_member_description',
				],
				'children'          => [
					$name,
					$position,
					$content,
					$social_media,
				],
				'childrenSanitizer' => 'et_core_esc_previously',
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
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => [
					$elements->style_components(
						[
							'attrName' => 'module',
						]
					),
					$image_container,
					$description,
				],
			]
		);
	}

	/**
	 * Merges the image attributes and name attributes into a single array.
	 *
	 * @since ??
	 *
	 * @param array $image_attrs The image attributes array.
	 * @param array $name_attrs The name attributes array.
	 * @return array The merged array of image and name attributes.
	 */
	public static function merge_image_name_attrs( array $image_attrs, array $name_attrs ): array {
		$merged = [];

		foreach ( $image_attrs as $breakpoint => $states ) {
			foreach ( $states as $state => $state_value ) {
				foreach ( $state_value as $sub_name => $value ) {
					if ( ! isset( $merged[ $breakpoint ] ) ) {
						$merged[ $breakpoint ] = [];
					}

					if ( ! isset( $merged[ $breakpoint ][ $state ] ) ) {
						$merged[ $breakpoint ][ $state ] = [];
					}

					if ( 'url' === $sub_name ) {
						$merged[ $breakpoint ][ $state ]['src'] = $value;
					} else {
						$merged[ $breakpoint ][ $state ][ $sub_name ] = $value;
					}
				}
			}
		}

		foreach ( $name_attrs as $breakpoint => $states ) {
			foreach ( $states as $state => $state_value ) {
				if ( ! isset( $merged[ $breakpoint ] ) ) {
					$merged[ $breakpoint ] = [];
				}

				if ( ! isset( $merged[ $breakpoint ][ $state ] ) ) {
					$merged[ $breakpoint ][ $state ] = [];
				}

				$merged[ $breakpoint ][ $state ]['alt'] = $state_value;
			}
		}

		if ( empty( $merged ) ) {
			return [];
		}

		return [
			'innerContent' => $merged,
		];
	}

	/**
	 * Load and register the Team Member module.
	 *
	 * This function loads the team member module by registering it in the WordPress `init` action hook.
	 * It adds an action to the WordPress 'init' hook that calls `ModuleRegistration::register_module()`,
	 * passing the module JSON folder path and the render callback
	 * function as arguments.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/team-member/';

		add_filter( 'divi_conversion_presets_attrs_map', array( TeamMemberPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
