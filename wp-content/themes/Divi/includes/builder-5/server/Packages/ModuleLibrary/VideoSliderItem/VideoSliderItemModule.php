<?php
/**
 * ModuleLibrary: VideoSliderItem Module class.
 *
 * @package Builder\Packages\ModuleLibrary\VideoSliderItem
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\VideoSliderItem;

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
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleLibrary\Video\VideoHTMLController;
use ET\Builder\Packages\ModuleLibrary\VideoSlider\VideoSlideThumbnailController;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET_Builder_Post_Features;
use WP_Block;

/**
 * `VideoSliderItemModule` is consisted of functions used for VideoSliderItem Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class VideoSliderItemModule implements DependencyInterface {

	/**
	 * Module classnames function for video slider item module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/video-slider-item/module-classnames.ts.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type object  $classnamesInstance Instance of ET\Builder\Packages\Module\Layout\Components\Classnames.
	 *     @type array   $attrs              Block attributes data that being rendered.
	 *     @type boolean $isFirst            Is the child element the first element.
	 * }
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Video slide.
		$classnames_instance->add( 'et_pb_slide', true );

		// Slider controls background layout Options.
		$slide_controls_color = $attrs['sliderControls']['advanced']['desktop']['value']['color'] ?? '';
		$has_slide_controls   = ! empty( $slide_controls_color );
		// Add `et_pb_bg_layout_{light|dark}` class if slide control color is found.
		$classnames_instance->add( "et_pb_bg_layout_{$slide_controls_color}", $has_slide_controls );
	}

	/**
	 * Set script data of video slider item module options.
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
		$parent_attrs   = $args['parentAttrs'] ?? [];
		$parent_order   = $parent_attrs['orderIndex'] ?? 0;
		$order_selector = ".et_pb_video_slider_{$parent_order} .et_pb_carousel_item_{$args['orderIndex']}";

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
				'setContent'    => [
					[
						'selector'      => $selector . ' .et_pb_video_wrap .et_pb_video_box',
						'data'          => $attrs['video']['innerContent'] ?? [],
						'valueResolver' => function( $value ) {
							// Get Video Urls.
							$video_mp4_url  = $value['src'] ?? '';
							$video_webm_url = $value['webm'] ?? '';

							return VideoSliderItemModule::get_video_html( $video_mp4_url, $video_webm_url );
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
				],
				'setAttrs'      => [
					[
						'selector'      => $selector . '.et_pb_video_slider_item',
						'data'          => [
							'data-image' => MultiViewUtils::merge_values(
								[
									'video'     => $attrs['video']['innerContent'] ?? [],
									'thumbnail' => $attrs['thumbnail']['innerContent'] ?? [],
								]
							),
						],
						'valueResolver' => function( $value ) {
							$video               = $value['video'] ?? [];
							$thumbnail           = $value['thumbnail'] ?? [];
							$video_mp4_url       = $video['src'] ?? '';
							$video_thumbnail_url = $thumbnail['src'] ?? '';
							$video_cover_url     = VideoSliderItemModule::get_video_slide_overlay_cover_image_url( $video_mp4_url, $video_thumbnail_url );
							return $video_cover_url;
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
				],
				'setStyle'      => [
					[
						'selector'      => $selector . ' .et_pb_video_overlay',
						'data'          => [
							'background-image' => MultiViewUtils::merge_values(
								[
									'video'     => $attrs['video']['innerContent'] ?? [],
									'thumbnail' => $attrs['thumbnail']['innerContent'] ?? [],
								]
							),
						],
						'valueResolver' => function( $value ) {
							$video               = $value['video'] ?? [];
							$thumbnail           = $value['thumbnail'] ?? [];
							$video_mp4_url       = $video['src'] ?? '';
							$video_thumbnail_url = $thumbnail['src'] ?? '';
							$video_cover_url     = VideoSliderItemModule::get_video_slide_overlay_cover_image_url( $video_mp4_url, $video_thumbnail_url );
							return 'url(' . ( $video_cover_url ?? '' ) . ')';
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
					[
						'selector'      => $order_selector . ' .et_pb_video_overlay',
						'data'          => [
							'background-image' => MultiViewUtils::merge_values(
								[
									'video'     => $attrs['video']['innerContent'] ?? [],
									'thumbnail' => $attrs['thumbnail']['innerContent'] ?? [],
								]
							),
						],
						'valueResolver' => function( $value ) {
							$video               = $value['video'] ?? [];
							$thumbnail           = $value['thumbnail'] ?? [];
							$video_mp4_url       = $video['src'] ?? '';
							$video_thumbnail_url = $thumbnail['src'] ?? '';
							$video_cover_url     = VideoSliderItemModule::get_video_slide_overlay_cover_image_url( $video_mp4_url, $video_thumbnail_url );
							return 'url(' . ( $video_cover_url ) . ')';
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
				],
				'setVisibility' => [
					[
						'selector'      => $selector . ' .et_pb_video_overlay',
						'data'          => $parent_attrs['overlay']['advanced'] ?? [],
						'valueResolver' => function ( $value ) {
							$show_image_overlay = $value['showImageOverlay'] ?? '';
							return 'on' === $show_image_overlay ? 'visible' : 'hidden';
						},
					],
				],
			]
		);
	}

	/**
	 * Icon size style declaration.
	 *
	 * This function will declare icon size style for Video module.
	 *
	 * This function is the equivalent of the `iconSizeStyleDeclaration` JS function located in
	 * visual-builder/packages/module-library/src/components/video-slider-item/style-declarations/icon-size/index.ts.
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
	 * Video Slider Item Table Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/video-slider-item/module-styles.tsx.
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
	 *      @type ModuleElements $elements          ModuleElements instance.
	 * }
	 * @since ??
	 */
	public static function module_styles( array $args ) : void {
		$attrs       = $args['attrs'] ?? [];
		$elements    = $args['elements'];
		$settings    = $args['settings'] ?? [];
		$order_class = $args['orderClass'] ?? '';

		$base_order_class = $args['baseOrderClass'] ?? '';
		$selector_prefix  = $args['selectorPrefix'] ?? '';

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
							],
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
							'selector'            => "{$selector_prefix}.et_pb_video_slider {$base_order_class}.et_pb_slide .et_pb_video_wrap .et_pb_video_overlay .et_pb_video_play",
							'attr'                => $attrs['playIcon']['decoration']['icon'] ?? [],
							'declarationFunction' => [ self::class, 'icon_size_style_declaration' ],
							'orderClass'          => $order_class,
						]
					),
				],
			]
		);
	}

	/**
	 * Get Video Slide Thumbnail Trait.
	 *
	 * @since ??
	 *
	 * @param string|null $video_url           Video URL.
	 * @param string|null $video_thumbnail_url Video thumbnail url.
	 *
	 * @return string Video Slide Thumbnail URL.
	 */
	public static function get_video_slide_thumbnail( $video_url, $video_thumbnail_url ):string {
		static $cached = [];

		$cache_key = md5( $video_url . $video_thumbnail_url );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		$video_slide_thumbnail = $video_thumbnail_url;

		// If no thumbnail url found, try to get it from oembed src.
		if ( empty( $video_thumbnail_url ) ) {
			$video_thumbnail_data = VideoSlideThumbnailController::get_video_thumbnail(
				[
					'image_src' => esc_url( $video_thumbnail_url ),
					'src'       => esc_url( $video_url ),
				]
			);

			$video_slide_thumbnail = $video_thumbnail_data['thumbnail'] ?? $video_thumbnail_url;
		}

		if ( ! is_string( $video_slide_thumbnail ) ) {
			$video_slide_thumbnail = $video_thumbnail_url;
		}

		$cached[ $cache_key ] = $video_slide_thumbnail;

		return $video_slide_thumbnail;
	}

	/**
	 * Get Video HTML Trait.
	 *
	 * @since ??
	 *
	 * @param string|null $video_mp4_url  Video Mp4 URL.
	 * @param string|null $video_webm_url Video Webm url.
	 *
	 * @return string Video HTML.
	 */
	public static function get_video_html( $video_mp4_url, $video_webm_url ): string {
		static $cached = [];

		$cache_key = md5( $video_mp4_url . $video_webm_url );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		// Get the instance of ET_Builder_Post_Features.
		$post_features = ET_Builder_Post_Features::instance();

		// Generate Video HTML.
		$video_params = [
			'src'      => esc_url( $video_mp4_url ),
			'src_webm' => esc_url( $video_webm_url ),
		];

		// Get the attachment ID from the cache.
		$video = $post_features->get(
			// Cache key.
			$cache_key,
			// Callback function if the cache key is not found.
			function() use ( $video_params ) {
				// Generate Video HTML.
				if ( false !== et_pb_check_oembed_provider( $video_params['src'] ) ) {
					$video = et_builder_get_oembed( $video_params['src'] );
				} elseif ( false !== VideoHTMLController::validate_youtube_url( $video_params['src'] ) ) {
					$video = et_builder_get_oembed( VideoHTMLController::normalize_youtube_url( $video_params['src'] ) );
				} else {
					$video = HTMLUtility::render(
						[
							'tag'               => 'video',
							'attributes'        => [
								'controls' => true,
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => [
								'' !== $video_params['src'] ? HTMLUtility::render(
									[
										'tag'        => 'source',
										'attributes' => [
											'type' => 'video/mp4',
											'src'  => $video_params['src'],
										],
										'childrenSanitizer' => 'et_core_esc_previously',
									]
								) : '',
								'' !== $video_params['src_webm'] ? HTMLUtility::render(
									[
										'tag'        => 'source',
										'attributes' => [
											'type' => 'video/webm',
											'src'  => $video_params['src_webm'],
										],
										'childrenSanitizer' => 'et_core_esc_previously',
									]
								) : '',
							],
						]
					);
				}

				return $video;
			},
			// Cache group.
			'video_html'
		);

		if ( ! is_string( $video ) ) {
			$video = '';
		}

		if ( ! empty( $video ) ) {
			// Include MediaElement JS and CSS if any element with <video> is there.
			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'wp-mediaelement' );
		}

		$cached[ $cache_key ] = $video;

		return $video;
	}

	/**
	 * Get Video Slide Overlay Cover Image Trait.
	 *
	 * @since ??
	 *
	 * @param string|null $video_url           Video URL.
	 * @param string|null $video_thumbnail_url Video cover url.
	 *
	 * @return string Video Slide Cover URL.
	 */
	public static function get_video_slide_overlay_cover_image_url( $video_url, $video_thumbnail_url ):string {
		static $cached = [];

		$cache_key = md5( $video_url . $video_thumbnail_url );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		$video_cover_url = $video_thumbnail_url;

		// If thumbnail url found, try to get high resolution image.
		if ( ! empty( $video_thumbnail_url ) ) {
			$video_cover_data = VideoSlideThumbnailController::get_video_thumbnail(
				[
					'image_src' => esc_url( $video_thumbnail_url ),
					'src'       => esc_url( $video_url ),
				]
			);

			$video_cover_url = $video_cover_data['cover'] ?? $video_thumbnail_url;
		}

		if ( ! is_string( $video_cover_url ) ) {
			$video_cover_url = $video_thumbnail_url;
		}

		$cached[ $cache_key ] = $video_cover_url;

		return $video_cover_url;
	}

	/**
	 * VideoSliderItem Table render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by VB.
	 * @param string         $content                     Block content.
	 * @param WP_Block       $block                       Parsed block object that being rendered.
	 * @param ModuleElements $elements                    ModuleElements instance.
	 * @param array          $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return string HTML rendered of BarCountersItem module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs ) {
		$parent               = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$default_parent_attrs = ModuleRegistration::get_default_attrs( 'divi/video-slider' );
		$parent_attrs         = array_replace_recursive( $default_parent_attrs, $parent->attrs ?? [] );

		// Adding orderIndex to the parent attributes.
		$parent_attrs['orderIndex'] = $parent->orderIndex ?? 0;

		// Video Params.
		$video_mp4_url             = $attrs['video']['innerContent']['desktop']['value']['src'] ?? '';
		$video_thumbnail_url       = $attrs['thumbnail']['innerContent']['desktop']['value']['src'] ?? '';
		$show_image_overlay        = $parent_attrs['overlay']['advanced']['desktop']['value']['showImageOverlay'] ?? '';
		$should_show_image_overlay = 'on' === $show_image_overlay;

		// Video HTML wrapped with div having classname et_pb_video_box.
		$video_html = $elements->render(
			[
				'attrName'      => 'video',
				'valueResolver' => function( $value ) {
					// Get Video Urls.
					$video_mp4_url  = $value['src'] ?? '';
					$video_webm_url = $value['webm'] ?? '';
					return VideoSliderItemModule::get_video_html( $video_mp4_url, $video_webm_url );
				},
			]
		);

		// Video Overlay Image Html.
		$video_cover_url = self::get_video_slide_overlay_cover_image_url( $video_mp4_url, $video_thumbnail_url );

		// Video Overlay Image Html.
		$video_overlay_image_html = $video_cover_url
			? HTMLUtility::render(
				[
					'tag'               => 'div',
					'attributes'        => [
						'class' => 'et_pb_video_overlay',
						'style' => [
							'background-image' => "url({$video_cover_url})",
						],
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => [
						HTMLUtility::render(
							[
								'tag'               => 'div',
								'attributes'        => [
									'class' => 'et_pb_video_overlay_hover',
								],
								'childrenSanitizer' => 'et_core_esc_previously',
								'children'          => [
									HTMLUtility::render(
										[
											'tag'        => 'a',
											'attributes' => [
												'class' => 'et_pb_video_play',
												'href'  => '#',
											],
											'childrenSanitizer' => 'et_core_esc_previously',
										]
									),
								],
							]
						),
					],
				]
			)
			: '';

		// Video Slide Wrapper.
		$video_slide_wrapper_html = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_video_wrap',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					$video_html,
					$should_show_image_overlay ? $video_overlay_image_html : '',
				],
			]
		);

		// Video Thumbnail.
		$video_thumbnail = self::get_video_slide_thumbnail( $video_mp4_url, $video_thumbnail_url );

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
				'htmlAttrs'                => [
					'data-image' => esc_attr( $video_thumbnail ),
				],
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'hasModuleClassName'       => false,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'parentAttrs'              => $parent_attrs,
				'parentId'                 => $parent->id ?? '',
				'parentName'               => $parent->blockName ?? '',
				'children'                 => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $video_slide_wrapper_html,
			]
		);
	}

	/**
	 * Loads `VideoSliderItemModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/video-slider-item/';

		add_filter( 'divi_conversion_presets_attrs_map', array( VideoSliderItemPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
