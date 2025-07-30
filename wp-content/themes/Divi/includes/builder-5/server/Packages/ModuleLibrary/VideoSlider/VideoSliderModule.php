<?php
/**
 * ModuleLibrary: VideoSlider Module class.
 *
 * @package Builder\Packages\ModuleLibrary\VideoSliderModule
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\VideoSlider;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowClassnames;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleLibrary\VideoSlider\VideoSliderPresetAttrsMap;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * `VideoSliderModule` is consisted of functions used for Video Slider Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class VideoSliderModule implements DependencyInterface {

	/**
	 * Module classnames function for Video Slider module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/video-slider/module-classnames.ts.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object $classnamesInstance Instance of ET\Builder\Packages\Module\Layout\Components\Classnames.
	 *     @type array  $attrs              Block attributes data that being rendered.
	 * }
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => $attrs['module']['decoration'] ?? [],
				]
			)
		);

		// Video.
		// Note. we need to add here classnames for border and box shadow hence it is set.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => $attrs['video']['decoration'] ?? [],
				]
			)
		);
	}

	/**
	 * Set module script data of VideoSlider Module options.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *   Array of arguments.
	 *
	 *   @type string         $id            Module id.
	 *   @type string         $name          Module name.
	 *   @type string         $selector      Module selector.
	 *   @type array          $attrs         Module attributes.
	 *   @type int            $storeInstance The ID of instance where this block stored in BlockParserStore class.
	 *   @type ModuleElements $elements      ModuleElements instance.
	 * }
	 */
	public static function module_script_data( $args ) {
		// Assign variables.
		$id             = $args['id'] ?? '';
		$name           = $args['name'] ?? '';
		$selector       = $args['selector'] ?? '';
		$attrs          = $args['attrs'] ?? [];
		$store_instance = $args['storeInstance'] ?? null;
		$elements       = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);

		// Show video slide script data.
		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'hoverSelector' => $selector,
				'setClassName'  => [
					[
						'selector'      => $selector . ' > div.et_pb_slider',
						'data'          => [
							'et_pb_slider_no_arrows' => $attrs['sliderControls']['advanced'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							$show_arrows = $value['useArrows'] ?? 'on';
							return 'off' === $show_arrows ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector . ' > div.et_pb_slider',
						'data'          => [
							'et_pb_slider_carousel' => $attrs['sliderControls']['advanced'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							$show_thumbnails = $value['useThumbnails'] ?? 'on';
							return 'on' === $show_thumbnails ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector . ' > div.et_pb_slider',
						'data'          => [
							'et_pb_slider_no_pagination' => $attrs['sliderControls']['advanced'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							$show_thumbnails = $value['useThumbnails'] ?? 'on';
							return 'on' === $show_thumbnails ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector . ' > div.et_pb_slider',
						'data'          => [
							'et_pb_slider_dots' => $attrs['sliderControls']['advanced'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							$show_thumbnails = $value['useThumbnails'] ?? 'on';
							return 'off' === $show_thumbnails ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * VideoSlider module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function VideoEdit located in
	 * visual-builder/packages/module-library/src/components/video-slider/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by VB.
	 * @param string         $content                     Block content.
	 * @param WP_Block       $block                       Parsed block object that being rendered.
	 * @param ModuleElements $elements                    ModuleElements instance.
	 * @param array          $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return string HTML rendered of VideoSlider module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs ) {
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function ( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$show_arrows           = $attrs['sliderControls']['advanced']['desktop']['value']['useArrows'] ?? 'on';
		$show_thumbnails       = $attrs['sliderControls']['advanced']['desktop']['value']['useThumbnails'] ?? 'on';
		$slider_controls_color = $attrs['sliderControls']['advanced']['desktop']['value']['color'] ?? '';

		$children = '';

		// VideoSlider Slides Content.
		$output = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et_pb_slider'               => true,
							'et_pb_preload'              => true,
							'et_pb_slider_no_arrows'     => 'off' === $show_arrows,
							'et_pb_slider_carousel'      => 'on' === $show_thumbnails,
							'et_pb_slider_no_pagination' => 'on' === $show_thumbnails,
							'et_pb_slider_dots'          => 'off' === $show_thumbnails,
							"et_pb_controls_{$slider_controls_color}" => true,
							'has-box-shadow-overlay'     => '' !== BoxShadowClassnames::has_overlay( $attrs['video']['decoration']['boxShadow'] ?? [] ),
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $elements->style_components(
					[
						'attrName' => 'video',
					]
				) . HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_slides',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
						'children'          => $content,
					]
				),
			]
		);

		$children .= $output;

		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		return Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                       => $block->parsed_block['id'],
				'name'                     => $block->block_type->name,
				'moduleCategory'           => $block->block_type->category,
				'attrs'                    => $attrs,
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'parentId'                 => $parent->id ?? '',
				'parentName'               => $parent->blockName ?? '',
				'parentAttrs'              => $parent->attrs ?? [],
				'children'                 => $children,
				'childrenIds'              => $children_ids,
			]
		);
	}

	/**
	 * Custom CSS fields
	 *
	 * This function is equivalent of JS const cssFields located in
	 * visual-builder/packages/module-library/src/components/video-slider/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/video-slider' )->customCssFields;
	}

	/**
	 * Icon size style declaration.
	 *
	 * This function will declare icon size style for Video module.
	 *
	 * This function is the equivalent of the `iconSizeStyleDeclaration` JS function located in
	 * visual-builder/packages/module-library/src/components/video-slider/style-declarations/icon-size/index.ts.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  If set to true, the CSS will be added with !important.
	 *     @type string     $returnType This is the type of value that the function will return. Can be either string or key_value_pair.
	 * }
	 *
	 * @since ??
	 */
	public static function icon_size_style_declaration( $args ) {

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$attr_value = $args['attrValue'];
		$size       = $attr_value['size'] ?? null;
		$use_size   = $attr_value['useSize'] ?? null;

		if ( 'on' === $use_size && ! empty( $size ) ) {
			// Handle parsed icon size numeric value.
			$parsed_size     = SanitizerUtility::numeric_parse_value( $size );
			$icon_size_value = 0 - ( $parsed_size['valueNumber'] ?? 0 );

			if ( ! is_null( $parsed_size ) ) {
				$style_declarations->add( 'margin-top', 0 !== $icon_size_value ? round( $icon_size_value / 2 ) . $parsed_size['valueUnit'] : 0 );
				$style_declarations->add( 'margin-left', 0 !== $icon_size_value ? round( $icon_size_value / 2 ) . $parsed_size['valueUnit'] : 0 );
			}
		}

		return $style_declarations->value();
	}

	/**
	 * Video overlay with border style declaration.
	 *
	 * This function will declare Video overlay with border style for Video module.
	 *
	 * This function is the equivalent of the `videoOverlayBorderStyleDeclaration` JS function located in
	 * visual-builder/packages/module-library/src/components/video-slider/style-declarations/video-border/index.ts.
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  If set to true, the CSS will be added with !important.
	 *     @type string     $returnType This is the type of value that the function will return. Can be either string or key_value_pair.
	 * }
	 *
	 * @since ??
	 */
	public static function video_overlay_overflow_style_declaration( array $params ): string {
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
	 * VideoSlider Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/video-slider/module-styles.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
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
	 *      @type int            $orderIndex        Module order index.
	 *      @type int            $storeInstance     The ID of instance where this block stored in BlockParserStore class.
	 *      @type ModuleElements $elements          ModuleElements instance.
	 * }
	 */
	public static function module_styles( $args ) {
		$attrs       = $args['attrs'] ?? [];
		$elements    = $args['elements'];
		$settings    = $args['settings'] ?? [];
		$order_class = $args['orderClass'] ?? '';

		$base_order_class = $args['baseOrderClass'] ?? '';
		$selector_prefix  = $args['selectorPrefix'] ?? '';

		// Defaulted printed style attributes.
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
							],
						]
					),
					// Video.
					$elements->style(
						[
							'attrName'   => 'video',
							'styleProps' => [
								'boxShadow' => [
									'selectorFunction' => function ( $params ) {
										$attr       = $params['attr'] ?? [];
										$breakpoint = $params['breakpoint'] ?? [];
										$attr_state = $params['state'] ?? [];
										$position   = $attr[ $breakpoint ][ $attr_state ]['position'] ?? '';

										if ( 'inner' === $position ) {
											return implode(
												', ',
												[
													"{$params['selector']}>.et_pb_slider>.box-shadow-overlay",
													"{$params['selector']}>.et_pb_slider.et-box-shadow-no-overlay",
													"{$params['selector']}>.et_pb_carousel .et_pb_carousel_item>.box-shadow-overlay",
													"{$params['selector']}>.et_pb_carousel .et_pb_carousel_item.et-box-shadow-no-overlay",
												]
											);
										} else {
											return implode(
												', ',
												[
													"{$params['selector']}>.et_pb_slider",
													"{$params['selector']}>.et_pb_carousel .et_pb_carousel_item",
												]
											);
										}
									},
								],
							],
						]
					),
					// Video Overlay.
					$elements->style(
						[
							'attrName' => 'overlay',
						]
					),
					// Video Play Icon.
					$elements->style(
						[
							'attrName' => 'playIcon',
						]
					),
					// Video play icon size styles.
					CommonStyle::style(
						[
							'selector'            => implode(
								', ',
								[
									"{$args['orderClass']} .et_pb_video_wrap .et_pb_video_play",
									"{$args['orderClass']} .et_pb_video_wrap .et_pb_carousel .et_pb_video_play",
								]
							),
							'attr'                => $attrs['playIcon']['decoration']['icon'] ?? [],
							'declarationFunction' => [ self::class, 'icon_size_style_declaration' ],
							'orderClass'          => $order_class,
						]
					),
					CommonStyle::style(
						[
							'selector'            => implode(
								', ',
								[
									"{$selector_prefix}.et_pb_video_slider{$base_order_class} .et_pb_slider",
									"{$selector_prefix}.et_pb_video_slider{$base_order_class} .et_pb_carousel_item",
								]
							),
							'attr'                => $attrs['video']['decoration']['border'] ?? [],
							'declarationFunction' => [ self::class, 'video_overlay_overflow_style_declaration' ],
							'orderClass'          => $order_class,
						]
					),
					CssStyle::style(
						[
							'selector'   => $args['orderClass'],
							'attr'       => $attrs['css'] ?? [],
							'cssFields'  => self::custom_css(),
							'orderClass' => $order_class,
						]
					),
				],
			]
		);
	}

	/**
	 * Loads `VideoSliderModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @return void
	 */
	public function load() {
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/video-slider/';

		add_filter( 'divi_conversion_presets_attrs_map', array( VideoSliderPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
