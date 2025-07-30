<?php
/**
 * Module Library: Circle Counter Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\CircleCounter;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Script;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Background\BackgroundClassnames;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\CircleCounter\CircleCounterPresetAttrsMap;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use WP_Block_Type_Registry;
use WP_Block;


/**
 * CircleCounterModule class.
 *
 * This class implements the functionality of a circle counter component in a
 * frontend application. It provides functions for rendering the circle counter,
 * managing REST API endpoints, and other related tasks.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 *
 * @see DependencyInterface
 */
class CircleCounterModule implements DependencyInterface {

	/**
	 * Get the custom CSS fields for the Divi Circle Counter module.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js-beta/divi-module-library/functions/generateDefaultAttrs cssFields}
	 * located in `@divi/module-library`. Note that this function does not have
	 * a `label` property on each array item, unlike the JS const cssFields.
	 *
	 * @since ??
	 *
	 * @return array An array of custom CSS fields for the Divi circle counter module.
	 *
	 * @example
	 * ```php
	 * $customCssFields = CustomCssTrait::custom_css();
	 * // Returns an array of custom CSS fields for the circle counter module.
	 * ```
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/circle-counter' )->customCssFields;
	}

	/**
	 * CircleCounter Module's style components.
	 *
	 * This function is equivalent to the JavaScript constant
	 * {@link /docs/builder-api/js/module-library/module-styles moduleStyles}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *      An array of arguments.
	 *
	 *      @type string         $id                Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *      @type string         $name              Module name.
	 *      @type string         $attrs             Module attributes.
	 *      @type string         $parentAttrs       Parent attrs.
	 *      @type string         $orderClass        Selector class name.
	 *      @type string         $parentOrderClass  Parent selector class name.
	 *      @type string         $wrapperOrderClass Wrapper selector class name.
	 *      @type string         $settings          Custom settings.
	 *      @type string         $state             Attributes state.
	 *      @type string         $mode              Style mode.
	 *      @type ModuleElements $elements          ModuleElements instance.
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
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} .percent p",
													"{$args['orderClass']} .et_pb_module_header",
												]
											),
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
					// Title.
					$elements->style(
						[
							'attrName' => 'title',
						]
					),
					// Content.
					$elements->style(
						[
							'attrName' => 'number',
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
	 * Populate the `numberValue` data.
	 *
	 * This function takes an array of attributes for the `number` module and
	 * populates the `numberValue` data based on those attributes. It loops through
	 * the attributes and retrieves the attribute value using the `get_attr_value()` function
	 * from the ModuleUtils class, and assigns it to the corresponding breakpoint
	 * and state in the `$attr_value` array. The `$attr_value` array is then returned.
	 *
	 * @since ??
	 *
	 * @param array $attrs The attributes for the `number` module.
	 *
	 * @return array The populated `numberValue` data.
	 *
	 * @example
	 * ```php
	 * $attrs = [
	 *   'breakpoint1' => [
	 *     'state1' => 'value1',
	 *     'state2' => 'value2',
	 *   ],
	 *   'breakpoint2' => [
	 *     'state1' => 'value3',
	 *     'state2' => 'value4',
	 *   ],
	 * ];
	 * $numberValue = CircleCounterModule::data_number_value($attrs);
	 * // $numberValue = [
	 * //   'breakpoint1' => [
	 * //     'state1' => 'value1',
	 * //     'state2' => 'value2',
	 * //   ],
	 * //   'breakpoint2' => [
	 * //     'state1' => 'value3',
	 * //     'state2' => 'value4',
	 * //   ],
	 * // ];
	 * ```
	 */
	public static function data_number_value( array $attrs ): array {
		$attr_value = [];

		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$attr_value[ $breakpoint ][ $state ] = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);
			}
		}

		return $attr_value;
	}

	/**
	 * Populate `barColor` data.
	 *
	 * This function takes an array of module attributes representing the `circle.color` module
	 * and populates the `barColor` data. It iterates through each breakpoint and state within the
	 * `$attrs` array and retrieves the normalized color value using the `ModuleUtils::get_attr_value`
	 * method. The normalized color value is then stored in a multi-dimensional array structure where
	 * the first level represents the breakpoint and the second level represents the state.
	 *
	 * @since ??
	 *
	 * @param array $attrs The `circle.color` module attributes.
	 *
	 * @return array The `barColor` data.
	 *
	 * @example
	 * ```php
	 *     $attrs = [
	 *         'breakpoint1' => [
	 *             'state1' => '#ff0000',
	 *             'state2' => '#00ff00',
	 *         ],
	 *         'breakpoint2' => [
	 *             'state1' => '#0000ff',
	 *             'state2' => '#ffff00',
	 *         ],
	 *     ];
	 *     $barColorData = CircleCounterModule::data_bar_color($attrs);
	 *     // $barColorData will contain the following structure:
	 *     // [
	 *     //    'breakpoint1' => [
	 *     //        'state1' => '#ff0000',
	 *     //        'state2' => '#00ff00',
	 *     //    ],
	 *     //    'breakpoint2' => [
	 *     //        'state1' => '#0000ff',
	 *     //        'state2' => '#ffff00',
	 *     //    ],
	 *     // ]
	 * ```
	 */
	public static function data_bar_color( array $attrs ): array {
		$attr_value = [];

		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$attr_value[ $breakpoint ][ $state ] = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);
			}
		}

		return $attr_value;
	}

	/**
	 * Populate `trackColor` data.
	 *
	 * This function takes an array of module attributes representing the `circle.background` module
	 * and populates the `trackColor` data. It iterates through each breakpoint and state within the
	 * `$attrs` array and retrieves the normalized color value using the
	 * `ModuleUtils::get_attr_value` method. The normalized color value is then stored in a
	 * multi-dimensional array structure where the first level represents the breakpoint and the
	 * second level represents the state.
	 *
	 * @since ??
	 *
	 * @param array $attrs The array of module attributes representing the `circle.background` module.
	 *
	 * @return array The multi-dimensional array structure that represents the `trackColor` data.
	 *               The first level represents the breakpoint and the second level represents the
	 *               state. Each value in the array is the normalized color or null if not found.
	 *
	 * @example
	 * ```php
	 * $attrs = [
	 *     'breakpoint1' => [
	 *         'state1' => [
	 *             'color' => '#ff0000',
	 *         ],
	 *         'state2' => [
	 *             'color' => '#00ff00',
	 *         ],
	 *     ],
	 *     'breakpoint2' => [
	 *         'state1' => [
	 *             'color' => '#0000ff',
	 *         ],
	 *         'state2' => [
	 *             'color' => '#ffffff',
	 *         ],
	 *     ],
	 * ];
	 *
	 * $trackColorData = CircleCounterModule::data_track_color($attrs);
	 * // $trackColorData = [
	 * //     'breakpoint1' => [
	 * //         'state1' => '#ff0000',
	 * //         'state2' => '#00ff00',
	 * //     ],
	 * //     'breakpoint2' => [
	 * //         'state1' => '#0000ff',
	 * //         'state2' => '#ffffff',
	 * //     ],
	 * // ];
	 * ```
	 */
	public static function data_track_color( array $attrs ): array {
		$attr_value = [];

		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$normalized = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$attr_value[ $breakpoint ][ $state ] = $normalized['color'] ?? null;
			}
		}

		return $attr_value;
	}

	/**
	 * Populate `trackAlpha` data.
	 *
	 * This function takes an array of attributes and returns an array with the
	 * `opacity` values for each breakpoint and state.
	 *
	 * @since ??
	 *
	 * @param array $attrs The attributes for the `circle.background` module.
	 *
	 * @return array An array with the `opacity` values for each breakpoint and state.
	 *
	 * @example
	 * ```php
	 *   $attrs = [
	 *      'breakpoint_1' => [
	 *          'state_1' => [
	 *              'opacity' => 0.5,
	 *          ],
	 *          'state_2' => [
	 *              'opacity' => null,
	 *          ],
	 *      ],
	 *      'breakpoint_2' => [
	 *          'state_1' => [
	 *              'opacity' => 0.8,
	 *          ],
	 *          'state_2' => [
	 *              'opacity' => 0.3,
	 *          ],
	 *      ],
	 *   ];
	 *
	 *   $result = CircleCounterModule::data_track_alpha($attrs);
	 *   // $result will be:
	 *   // [
	 *   //    'breakpoint_1' => [
	 *   //        'state_1' => 0.5,
	 *   //        'state_2' => null,
	 *   //    ],
	 *   //    'breakpoint_2' => [
	 *   //        'state_1' => 0.8,
	 *   //        'state_2' => 0.3,
	 *   //    ],
	 *   // ]
	 * ```
	 */
	public static function data_track_alpha( array $attrs ): array {
		$attr_value = [];

		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$normalized = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$attr_value[ $breakpoint ][ $state ] = $normalized['opacity'] ?? null;
			}
		}

		return $attr_value;
	}

	/**
	 * Get the border style declaration for a Section.
	 *
	 * This function takes an array of parameters and generates the border style declaration based on the provided attributes.
	 * The generated declaration is then returned as a string.
	 * If the `'radius'` attribute is empty, the `'overflow'` style declaration will not be added to the returned result.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of parameters.
	 *
	 *     @type array $attrValue {
	 *         An array containing the attributes for the section.
	 *
	 *         @type string $radius The border radius.
	 *     }
	 * }
	 *
	 * @return string The border style declaration.
	 *
	 * @example:
	 * ```php
	 *     $params = [
	 *         'attrValue' => [
	 *             'radius' => '10px',
	 *         ],
	 *     ];
	 *     $overflow_style_declaration = overflow_style_declaration( $params );
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
	 * Set front-end script data for the Circle Counter module.
	 *
	 * This function is responsible for setting the frontend script data for the
	 * Circle Counter module. It registers the necessary data items for the
	 * module based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for setting the front-end script data.
	 *
	 *     @type string $selector The module selector.
	 *     @type array  $attrs    The module attributes.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * CircleCounterModule::set_front_end_data( [
	 *   'selector' => '#circle-counter-1',
	 *   'attrs'    => [
	 *     'number' => [
	 *       'innerContent' => [ '10' ],
	 *     ],
	 *     'circle' => [
	 *       'advanced' => [
	 *         'color'      => '#ff0000',
	 *         'background' => '#000000',
	 *       ],
	 *     ],
	 *   ],
	 * ] );
	 * ```
	 */
	public static function set_front_end_data( array $args ): void {
		// Script data is not needed in VB.
		if ( Conditions::is_vb_enabled() ) {
			return;
		}

		$selector = $args['selector'] ?? '';
		$attrs    = $args['attrs'] ?? [];

		if ( ! $selector || ! $attrs ) {
			return;
		}

		// Register front-end data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'circle_counter',
				'data_item_id' => null,
				'data_item'    => [
					'selector' => $selector,
					'data'     => [
						'numberValue' => self::data_number_value( $attrs['number']['innerContent'] ?? [] ),
						'barColor'    => self::data_bar_color( $attrs['circle']['advanced']['color'] ?? [] ),
						'trackColor'  => self::data_track_color( $attrs['circle']['advanced']['background'] ?? [] ),
						'trackAlpha'  => self::data_track_alpha( $attrs['circle']['advanced']['background'] ?? [] ),
					],
				],
			]
		);
	}

	/**
	 * Circle Counter module script data.
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
	 * CircleCounterModule::module_script_data( $args );
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
				'attrName'        => 'module',
				'scriptDataProps' => [
					'background' => [
						// Based on visual-builder/packages/module-library/src/components/circle-counter/module-styles.tsx.
						'selector' => "{$selector} .et_pb_circle_counter_inner",
					],
				],
			]
		);

		// Set module specific front-end data.
		self::set_front_end_data(
			[
				'selector' => $selector,
				'attrs'    => $attrs,
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
						'selector'      => "$selector .et_pb_module_header, $selector .et_pb_module_header a",
						'data'          => $attrs['title']['innerContent'] ?? [],
						'valueResolver' => function ( $value ) {
							return $value ?? '';
						},
					],
				],
				'setClassName'  => [
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_with_title' => $attrs['title']['innerContent'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return ! empty( $value ) ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * Generate classnames for the module.
	 *
	 * This function generates classnames for the module based on the provided
	 * arguments. It is used in the `render_callback` function of the Circle
	 * Counter module.
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
	 * CircleCounterModule::module_classnames($args);
	 * ```
	 */
	public static function module_classnames( array $args ): void {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		$title                = $attrs['title']['innerContent']['desktop']['value'] ?? null;
		$with_title_classname = $title ? 'et_pb_with_title' : '';

		$classnames_instance->add( $with_title_classname );

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );
		$classnames_instance->add( 'container-width-change-notify', true );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
					'attrs'      => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $attrs['module']['advanced']['link'] ?? [],
						]
					),
					'background' => false,
				]
			)
		);
	}

	/**
	 * Render callback for the Circle Counter module.
	 *
	 * This function is responsible for rendering the server-side HTML of the module on the frontend.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ CircleCounterEdit}
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
	 * @return string The HTML rendered output of the Circle Counter module.
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
	 * CircleCounterModule::render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs );
	 * ```
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$title = $elements->render(
			[
				'attrName' => 'title',
			]
		);

		$percent_sign = $attrs['number']['advanced']['percentSign']['desktop']['value'] ?? 'on';

		$percent_value_html = HTMLUtility::render(
			[
				'tag'        => 'span',
				'attributes' => [
					'class' => HTMLUtility::classnames(
						[
							'percent-value' => true,
						]
					),
				],
			]
		);

		$percent_sign_html = HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'percent-sign' => true,
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					( 'on' === $percent_sign ? '%' : '' ),
				],
			]
		);

		$output = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_circle_counter_inner' => true,

							// Add background classnames, if any.
							BackgroundClassnames::classnames( $attrs['module']['decoration']['background'] ?? [] ) => true,
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				// By default, it will render only background. So, we don't need multiple `$elements->style_components`.
				'children'          => [
					$elements->style_components(
						[
							'attrName' => 'module',
						]
					),
					HTMLUtility::render(
						[
							'tag'               => 'div',
							'attributes'        => [
								'class' => HTMLUtility::classnames(
									[
										'percent' => true,
									]
								),
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => [
								HTMLUtility::render(
									[
										'tag'      => 'p',
										'childrenSanitizer' => 'et_core_esc_previously',
										'children' => [
											$percent_value_html,
											$percent_sign_html,
										],
									]
								),
							],
						]
					),
					$title,
				],
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
				'name'                     => $block->block_type->name,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'moduleCategory'           => $block->block_type->category,
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'parentAttrs'              => $parent->attrs ?? [],
				'parentId'                 => $parent->id ?? '',
				'parentName'               => $parent->blockName ?? '',
				'children'                 => $output,
			]
		);
	}

	/**
	 * Loads `CircleCounterModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/circle-counter/';

		add_filter( 'divi_conversion_presets_attrs_map', array( CircleCounterPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
