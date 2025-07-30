<?php
/**
 * ModuleLibrary: Slide Module class.
 *
 * @package Builder\Packages\ModuleLibrary\SlideModule
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Slide;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use WP_Block;
use ET\Builder\Packages\ModuleLibrary\Slide\SlidePresetAttrsMap;

use ET_Builder_Post_Features;

// phpcs:disable Squiz.Commenting.InlineComment -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

/**
 * `SlideModule` is consisted of functions used for Slide Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class SlideModule implements DependencyInterface {

	/**
	 * Module classnames function for Slide module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/slide/module-classnames.ts.
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

		$image_src              = $attrs['image']['innerContent']['desktop']['value']['src'] ?? '';
		$video_url              = $attrs['video']['innerContent']['desktop']['value'] ?? '';
		$use_background_overlay = $attrs['slideOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$use_text_overlay       = $attrs['contentOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$alignment              = $attrs['image']['advanced']['alignment']['desktop']['value'] ?? '';

		$classnames_instance->add( 'et_pb_slide_with_image', ! empty( $image_src ) || ! empty( $video_url ) );
		$classnames_instance->add( 'et_pb_slide_with_video', ! empty( $video_url ) );
		$classnames_instance->add( 'et_pb_slider_with_overlay', 'on' === $use_background_overlay );
		$classnames_instance->add( 'et_pb_slider_with_text_overlay', 'on' === $use_text_overlay );
		$classnames_instance->add( 'et_pb_media_alignment_' . $alignment, 'bottom' !== $alignment );

		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'border' => array_merge(
								$attrs['module']['decoration']['border'] ?? [],
								$attrs['image']['decoration']['border'] ?? []
							),
							'link'   => $attrs['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	/**
	 * Get video embed.
	 *
	 * @param string $url Video URL.
	 *
	 * @since ??
	 *
	 * @return string Video embed.
	 */
	public static function get_video_embed( $url ) {
		global $wp_embed;

		static $cached = [];

		// Get the instance of ET_Builder_Post_Features.
		$post_features = ET_Builder_Post_Features::instance();

		$video_url = esc_url( $url );

		// Bail early if video URL is empty.
		if ( empty( $video_url ) ) {
			return '';
		}

		$cache_key = md5( $video_url );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		// Get the attachment ID from the cache.
		$video_embed = $post_features->get(
		// Cache key.
			$cache_key,
			// Callback function if the cache key is not found.
			function() use ( $video_url, $wp_embed ) {
				$autoembed      = $wp_embed->autoembed( $video_url );
				$is_local_video = has_shortcode( $autoembed, 'video' );

				if ( $is_local_video ) {
					$video_embed = wp_video_shortcode( array( 'src' => $video_url ) );
				} else {
					$video_embed = et_builder_get_oembed( $video_url );

					$video_embed = preg_replace( '/<embed /', '<embed wmode="transparent" ', $video_embed );

					$video_embed = preg_replace( '/<\/object>/', '<param name="wmode" value="transparent" /></object>', $video_embed );
				}

				return $video_embed;
			},
			// Cache group.
			'video_html'
		);

		if ( ! is_string( $video_embed ) ) {
			$video_embed = '';
		}

		$cached[ $cache_key ] = $video_embed;

		return $video_embed;
	}

	/**
	 * Determine the heading level for an slider item.
	 *
	 * This function determines the heading level for an slider item based on the attributes provided
	 * and the attributes of its parent module. If the heading level is set in the module attributes,
	 * that value is used. If the heading level is not set in the module attributes, the function checks
	 * the heading level set in the parent module attributes. If the heading level is not set in either,
	 * the default heading level is h5.
	 *
	 * This function is equivalent to the JavaScript function
	 * {@link /docs/builder-api/js/module-library/ getHeadingLevel}
	 * located in `@divi/module-library`.
	 *
	 * @since ??
	 *
	 * @param array $attrs        Module attributes.
	 * @param array $parent_attrs Parent module attributes.
	 *
	 * @return string The heading level ('h1', 'h2', 'h3', 'h4', 'h5', 'h6').
	 *
	 * @example
	 * ```php
	 * $attrs = [];
	 * $parent_attrs = [];
	 * $heading_level = SlideModule::get_heading_level($attrs, $parent_attrs);
	 *
	 * // Result: $heading_level = 'h5'
	 * ```
	 * @example: Example with heading level set in module attributes.
	 * ```php
	 * $attrs = ['title' => ['decoration' => ['font' => ['font' => [ 'desktop' => ['value' => ['headingLevel' => 'h3']]]]]]]];
	 * $parent_attrs = ['title' => ['decoration' => ['font' => ['font' => ['desktop' => ['value' => ['headingLevel' => 'h2']]]]]]];
	 * $heading_level = SlideModule::get_heading_level($attrs, $parent_attrs);
	 *
	 * // Result: $heading_level = 'h3'
	 * ```
	 */
	public static function get_heading_level( array $attrs, array $parent_attrs ): string {
		$merged_attrs = ModuleUtils::merge_attrs(
			[
				'defaultAttrs' => $parent_attrs['title']['decoration']['font']['font'] ?? [],
				'attrs'        => $attrs['title']['decoration']['font']['font'] ?? [],
			]
		);

		$heading_level = $merged_attrs['desktop']['value']['headingLevel'] ?? '';

		if ( ! in_array( $heading_level, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ) {
			return 'h2';
		}

		return $heading_level;
	}

	/**
	 * Slide module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function SlideEdit located in
	 * visual-builder/packages/module-library/src/components/slide/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string HTML rendered of Slide module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		$parent_attrs = ModuleUtils::get_all_attrs( $parent );

		$heading_level = self::get_heading_level( $attrs, $parent_attrs );

		$use_slide_overlay = $attrs['slideOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$overlay           = 'on' === $use_slide_overlay ? HTMLUtility::render(
			[
				'tag'        => 'div',
				'attributes' => [
					'class' => 'et_pb_slide_overlay_container',
				],
			]
		) : '';

		// Image.
		$image = $elements->render(
			[
				'attrName' => 'image',
			]
		);

		// Image wrapper.
		$image_wrapper = ! empty( $image ) ? HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_slide_image',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $image,
			]
		) : '';

		// Slide Video.
		$video_url = $attrs['video']['innerContent']['desktop']['value'] ?? '';
		$video     = ! empty( $video_url ) ? HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_slide_video',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => self::get_video_embed( $video_url ),
			]
		) : '';

		// Slide Video/Image.
		$video_image = ! empty( $video ) ? $video : $image_wrapper;

		// Slide Title.
		$button_link        = $attrs['button']['innerContent']['desktop']['value']['linkUrl'] ?? '';
		$button_link_target = $attrs['button']['innerContent']['desktop']['value']['linkTarget'] ?? '';
		$has_button_link    = ! empty( $button_link ) && '#' !== $button_link;
		$title              = $has_button_link ? HTMLUtility::render(
			[
				'tag'               => $heading_level,
				'childrenSanitizer' => 'et_core_esc_previously',
				'attributes'        => [
					'class' => 'et_pb_slide_title',
				],
				'children'          => $elements->render(
					[
						'attrName'   => 'title',
						'tagName'    => 'a',
						'attributes' => [
							'href'   => $button_link,
							'target' => $button_link_target,
						],

					]
				),
			]
		) : $elements->render(
			[
				'attrName'   => 'title',
				'tagName'    => $heading_level,
				'attributes' => [
					'class' => 'et_pb_slide_title',
				],
			]
		);

		// Slide Content.
		$show_content_on_mobile = $attrs['content']['advanced']['showOnMobile']['desktop']['value'] ?? '';
		$content                = $elements->render(
			[
				'attrName'   => 'content',
				'attributes' => [
					'class' => 'off' === $show_content_on_mobile ? 'et_pb_slide_content et-hide-mobile' : 'et_pb_slide_content',
				],
			]
		);

		// Slide Content Wrapper.
		$use_content_overlay = $attrs['contentOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$content_wrapper     = 'on' === $use_content_overlay ? HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_text_overlay_wrapper',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $title . $content,
			]
		) : $title . $content;

		// Slide Button.
		$show_button_on_mobile = $attrs['button']['advanced']['showOnMobile']['desktop']['value'] ?? '';
		$button                = $elements->render(
			[
				'attrName'   => 'button',
				'attributes' => [
					'class' => 'off' === $show_button_on_mobile ? 'et_pb_more_button et-hide-mobile' : 'et_pb_more_button',
				],
			]
		);

		// Slide Description.
		$description = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_slide_description',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $content_wrapper . $button,
			]
		);

		// Slide Container Inner.
		$container_inner = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_slider_container_inner',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $video_image . $description,
			]
		);

		// Slide Container.
		$container = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_container',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $container_inner,
			]
		);

		// Get parent attributes.
		$parent_attrs = $parent->attrs ?? [];
		// If the module does not have transition set, then use the parent's transition.
		// Issue has been opened for why Slide receiving its parent background but not the transition:
		// @see https://github.com/elegantthemes/Divi/issues/39877
		if (
			empty( $attrs['module']['decoration']['transition'] ) &&
			! empty( $parent_attrs['module']['decoration']['transition'] )
		) {
			// Set the parent's transition to the module's transition.
			$elements->module_attrs['module']['decoration']['transition'] = $parent_attrs['module']['decoration']['transition'];
		}

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'htmlAttrs'           => [
					'data-slide-id' => $block->parsed_block['id'],
				],
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'moduleCategory'      => $block->block_type->category,
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'hasModuleClassName'  => false,
				'children'            => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $overlay . $container,
			]
		);
	}

	/**
	 * Custom CSS fields
	 *
	 * This function is equivalent of JS const cssFields located in
	 * visual-builder/packages/module-library/src/components/slide/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/slide' )->customCssFields;
	}

	/**
	 * Button Alignment Style Declaration
	 *
	 * This function will declare button alignment style for Slide module.
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
	public static function button_alignment_style_declaration( $params ) {
		$alignment = $params['attrValue']['alignment'] ?? '';

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( ! empty( $alignment ) ) {
			$style_declarations->add( 'text-align', $alignment );
		}

		return $style_declarations->value();
	}

	/**
	 * Slide Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/slide/styles.tsx.
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
	 * }
	 */
	public static function module_styles( $args ) {
		$attrs                       = $args['attrs'] ?? [];
		$elements                    = $args['elements'];
		$settings                    = $args['settings'] ?? [];
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];

		$base_order_class = $args['baseOrderClass'] ?? '';
		$selector_prefix  = $args['selectorPrefix'] ?? '';

		$use_slide_overlay   = $attrs['slideOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$use_content_overlay = $attrs['contentOverlay']['advanced']['use']['desktop']['value'] ?? '';

		$slide_overlay   = 'on' === $use_slide_overlay ? $elements->style(
			[
				'attrName' => 'slideOverlay',
			]
		) : [];
		$content_overlay = 'on' === $use_content_overlay ? $elements->style(
			[
				'attrName' => 'contentOverlay',
			]
		) : [];

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
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} .et_pb_slide_description .et_pb_slide_title",
													"{$args['orderClass']} .et_pb_slide_description .et_pb_slide_title a",
													"{$args['orderClass']} .et_pb_slide_description .et_pb_slide_content",
													"{$args['orderClass']} .et_pb_slide_description .et_pb_slide_content .post-meta",
													"{$args['orderClass']} .et_pb_slide_description .et_pb_slide_content .post-meta a",
													"{$args['orderClass']} .et_pb_slide_description .et_pb_slide_content .et_pb_button",
													"{$selector_prefix}.et_pb_slides {$base_order_class}.et_pb_slide .et_pb_slide_description",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['parentOrderClass']}[data-active-slide=\"{$args['id']}\"] .et-pb-slider-arrows .et-pb-arrow-prev",
													"{$args['parentOrderClass']}[data-active-slide=\"{$args['id']}\"] .et-pb-slider-arrows .et-pb-arrow-next",
												]
											),
											'attr'     => $attrs['arrows']['advanced']['color'] ?? [],
											'property' => 'color',
										],
									],
									[
										'componentName' => 'divi/background',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['parentOrderClass']}[data-active-slide=\"{$args['id']}\"] .et-pb-controllers a",
													"{$args['parentOrderClass']}[data-active-slide=\"{$args['id']}\"] .et-pb-controllers .et-pb-active-control",
												]
											),
											'attr'     => $attrs['dotNav']['decoration']['background'] ?? [],
										],
									],
								],
							],
						]
					),
					$slide_overlay,
					$content_overlay,
					// Image.
					$elements->style(
						[
							'attrName' => 'image',
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
							'attrName' => 'content',
						]
					),
					// Button.
					$elements->style(
						[
							'attrName'   => 'button',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$selector_prefix}.et_pb_slider {$base_order_class} .et_pb_slide_description .et_pb_button_wrapper",
											'attr'     => $attrs['button']['decoration']['button'] ?? [],
											'declarationFunction' => [ self::class, 'button_alignment_style_declaration' ],
										],
									],
								],
							],
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
	 * Set script data of used module options.
	 *
	 * This function is equivalent of JS function ModuleScriptData located in
	 * visual-builder/packages/module-library/src/components/slide/module-script-data.tsx.
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
						'selector'      => $selector . ' .et_pb_slide_video',
						'data'          => $attrs['video']['innerContent'] ?? [],
						'valueResolver' => function( $value ) {
							return SlideModule::get_video_embed( $value );
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
				],
			]
		);
	}

	/**
	 * Loads `SlideModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		add_filter( 'divi_conversion_presets_attrs_map', array( SlidePresetAttrsMap::class, 'get_map' ), 10, 2 );

		add_filter(
			'divi_module_library_register_module_attrs',
			function( $module_attrs, $args ) {
				if ( 'divi/slide' !== $args['name'] ) {
					return $module_attrs;
				}

				$attrs        = $args['attrs'];
				$parent_attrs = $args['parentAttrs'];

				if ( ! isset( $attrs['slideOverlay']['advanced']['use']['desktop']['value'] ) ) {
					$module_attrs['slideOverlay'] = $parent_attrs['children']['slideOverlay'] ?? [];
				}
				if ( ! isset( $attrs['contentOverlay']['advanced']['use']['desktop']['value'] ) ) {
					$module_attrs['contentOverlay'] = $parent_attrs['children']['contentOverlay'] ?? [];
				}

				return $module_attrs;
			},
			10,
			2
		);

		add_filter(
			'divi.moduleLibrary.conversion.moduleConversionOutline',
			function( $conversion_outline, $module_name ) {

				// Add custom conversion functions for this module
				if ( 'divi/slide' !== $module_name ) {
					return $conversion_outline;
				}

				// Non static expansion functions like this
				// dont automatically get converted correctly in the
				// autogenerated .json conversion outline,
				// so lets hook in and provide the correct conversion functions.
				//
				// valueExpansionFunctionMap: {
				// 	inline_fonts:       convertInlineFont,
				// 	text_border_radius: borderValueConversionFunctionMap.radius,
				// },
				$conversion_outline['valueExpansionFunctionMap'] = [
					'text_border_radius' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertBorderRadii',
				];

				return $conversion_outline;
			},
			10,
			2
		);

		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/slide/';

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
