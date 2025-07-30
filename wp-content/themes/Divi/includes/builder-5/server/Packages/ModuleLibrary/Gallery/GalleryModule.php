<?php
/**
 * ModuleLibrary: Gallery Module class.
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Gallery;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils as IconFontUtils;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
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
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use Exception;
use WP_Block_Type_Registry;
use WP_Block;

/**
 * `GalleryModule` is consisted of functions used for Gallery Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class GalleryModule implements DependencyInterface {

	/**
	 * Filters the module.decoration attributes.
	 *
	 * This function is equivalent of JS function filterModuleDecorationAttrs located in
	 * visual-builder/packages/module-library/src/components/gallery/attrs-filter/filter-module-decoration-attrs/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $decoration_attrs The original decoration attributes.
	 * @param array $attrs The attributes of the Gallery module.
	 *
	 * @return array The filtered decoration attributes.
	 */
	public static function filter_module_decoration_attrs( array $decoration_attrs, array $attrs ): array {
		// Attribute `module.advanced.fullwidth` is desktop only.
		$is_slider = $attrs['module']['advanced']['fullwidth']['desktop']['value'] ?? null;

		// If the module layout is Grid, it returns the decoration attributes with empty `boxShadow`.
		if ( 'on' !== $is_slider ) {
			$decoration_attrs = array_merge(
				$decoration_attrs,
				[
					'boxShadow' => [],
				]
			);
		}

		return $decoration_attrs;
	}

	/**
	 * Filters the image.decoration attributes.
	 *
	 * This function is equivalent of JS function filterImageDecorationAttrs located in
	 * visual-builder/packages/module-library/src/components/gallery/attrs-filter/filter-image-decoration-attrs/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $decoration_attrs The decoration attributes to be filtered.
	 * @param array $attrs           The whole module attributes.
	 *
	 * @return array The filtered decoration attributes.
	 */
	public static function filter_image_decoration_attrs( array $decoration_attrs, array $attrs ): array {
		// Attribute `module.advanced.fullwidth` is desktop only.
		$is_slider = $attrs['module']['advanced']['fullwidth']['desktop']['value'] ?? null;

		// If the module layout is Slider, it returns the image decoration attributes with empty `border` and `boxShadow`.
		if ( 'on' === $is_slider ) {
			$decoration_attrs = array_merge(
				$decoration_attrs,
				[
					'border'    => [],
					'boxShadow' => [],
				]
			);
		}

		return $decoration_attrs;
	}

	/**
	 * Module custom CSS fields.
	 *
	 * This function is equivalent of JS function cssFields located in
	 * visual-builder/packages/module-library/src/components/gallery/custom-css.ts.
	 *
	 * @since ??
	 *
	 * @return array The array of custom CSS fields.
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/gallery' )->customCssFields;
	}

	/**
	 * Set CSS class names to the module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/gallery/module-classnames.ts.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id                  Module unique ID.
	 *     @type string $name                Module name with namespace.
	 *     @type array  $attrs               Module attributes.
	 *     @type array  $childrenIds         Module children IDs.
	 *     @type bool   $hasModule           Flag that indicates if module has child modules.
	 *     @type bool   $isFirst             Flag that indicates if module is first in the row.
	 *     @type bool   $isLast              Flag that indicates if module is last in the row.
	 *     @type object $classnamesInstance  Instance of Instance of ET\Builder\Packages\Module\Layout\Components\Classnames class.
	 *
	 *     // FE only.
	 *     @type int|null $storeInstance The ID of instance where this block stored in BlockParserStore.
	 *     @type int      $orderIndex    The order index of the element.
	 * }
	 */
	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

		$auto       = $attrs['module']['advanced']['auto']['desktop']['value'] ?? 'off';
		$auto_speed = $attrs['module']['advanced']['autoSpeed']['desktop']['value'] ?? '7000';
		$fullwidth  = $attrs['module']['advanced']['fullwidth']['desktop']['value'] ?? 'off';

		if ( 'on' === $fullwidth ) {
			$classnames_instance->add( 'et_pb_slider' );
			$classnames_instance->add( 'et_pb_gallery_fullwidth' );
			$classnames_instance->add( 'clearfix' );

			if ( 'on' === $auto ) {
				$classnames_instance->add( 'et_slider_auto' );
				$classnames_instance->add( 'et_slider_speed_' . $auto_speed );
			}
		} else {
			$classnames_instance->add( 'et_pb_gallery_grid' );
		}

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['item']['decoration'] ?? [],
						[
							'link' => $args['attrs']['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	/**
	 * Set script data to the module.
	 *
	 * This function is equivalent of JS function ModuleScriptData located in
	 * visual-builder/packages/module-library/src/components/gallery/module-script-data.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string         $id            Module unique ID.
	 *     @type string         $name          Module name with namespace.
	 *     @type string         $selector      Module CSS selector.
	 *     @type array          $attrs         Module attributes.
	 *     @type array          $parentAttrs   Parent module attributes.
	 *     @type ModuleElements $elements      Instance of ModuleElements class.
	 *
	 *     // FE only.
	 *     @type int|null $storeInstance The ID of instance where this block stored in BlockParserStore.
	 *     @type int      $orderIndex    The order index of the element.
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

		// Fullwidth is desktop only attribute.
		$is_fullwidth = 'on' === $attrs['module']['advanced']['fullwidth']['desktop']['value'] ?? '';

		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'hoverSelector' => $selector,
				'setVisibility' => [
					[
						'selector'      => $selector . ' .et_pb_gallery_title, ' . $selector . ' .et_pb_gallery_caption',
						'data'          => $attrs['module']['advanced']['showTitleAndCaption'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
					$is_fullwidth ? [] : [
						'selector'      => $selector . ' .et_pb_gallery_pagination',
						'data'          => $attrs['pagination']['advanced']['showPagination'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === ( $value ?? 'on' ) ? 'visible' : 'hidden';
						},
					],
				],
			]
		);
	}

	/**
	 * Get overlay icon style declaration for Gallery module.
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
	 * $style = GalleryModule::icon_font_style_declaration( $params );
	 * // Result: 'color: #ff0000; font-weight: 400;'
	 * ```
	 */
	public static function icon_font_style_declaration( array $params ): string {
		$overlay_icon_attr = $params['attrValue'];
		$hover_icon        = $overlay_icon_attr['icon'] ?? [];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => true,
			]
		);

		$font_icon = IconFontUtils::escape_font_icon( IconFontUtils::process_font_icon( $hover_icon ) );

		if ( ! empty( $hover_icon['type'] ) ) {
			$font_family = IconFontUtils::is_fa_icon( $hover_icon ) ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', "'{$font_family}'" );
		}

		if ( ! empty( $hover_icon['weight'] ) ) {
			$style_declarations->add( 'font-weight', $hover_icon['weight'] );
		}

		if ( ! empty( $hover_icon['unicode'] ) ) {
			$style_declarations->add( 'content', "'{$font_icon}'" );
		}

		return $style_declarations->value();
	}

	/**
	 * Declare the overlay background style for the Gallery module.
	 *
	 * This function takes an array of arguments and declares the overlay background style for the Gallery module.
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
	 * GalleryModule::hover_overlay_color_style_declaration( $params );
	 * // Result: 'background-color: #000000;'
	 * ```
	 */
	public static function hover_overlay_color_style_declaration( array $params ): string {
		$overlay_color = $params['attrValue'];

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => $params['important'],
			]
		);

		if ( ! empty( $overlay_color ) ) {
			$style_declarations->add( 'background-color', $overlay_color );
		}

		return $style_declarations->value();
	}

	/**
	 * Overflow style declaration.
	 *
	 * This function is responsible for declaring the overflow style for the Gallery module.
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
	 * Set CSS styles to the module.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/gallery/module-styles.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id                       Module unique ID.
	 *     @type string $name                     Module name with namespace.
	 *     @type array  $attrs                    Module attributes.
	 *     @type array  $parentAttrs              Parent module attributes.
	 *     @type array  $siblingAttrs             Sibling module attributes.
	 *     @type array  $defaultPrintedStyleAttrs Default printed style attributes.
	 *     @type string $orderClass               Module CSS selector.
	 *     @type string $parentOrderClass         Parent module CSS selector.
	 *     @type string $wrapperOrderClass        Wrapper module CSS selector.
	 *     @type array  $settings                 Custom settings.
	 *     @type object $elements                 Instance of ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements class.
	 *
	 *     // VB only.
	 *     @type string $state Attributes state.
	 *     @type string $mode  Style mode.
	 *
	 *     // FE only.
	 *     @type int|null $storeInstance The ID of instance where this block stored in BlockParserStore.
	 *     @type int      $orderIndex    The order index of the element.
	 * }
	 */
	public static function module_styles( $args ) {
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
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs ) {
									return GalleryModule::filter_module_decoration_attrs( $decoration_attrs, $attrs );
								},
								'advancedStyles'           => [
									[
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => "{$args['orderClass']}.et_pb_gallery .et_pb_gallery_title, {$args['orderClass']}.et_pb_gallery .mfp-title, {$args['orderClass']}.et_pb_gallery .et_pb_gallery_caption, {$args['orderClass']}.et_pb_gallery .et_pb_gallery_pagination a",
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
											'propertySelectors' => [
												'textShadow' => [
													'desktop' => [
														'value' => [
															'text-shadow' => "{$args['orderClass']}.et_pb_gallery.et_pb_gallery_grid",
														],
													],
												],
											],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_overlay',
											'attr'     => $attrs['module']['advanced']['hoverOverlayColor'] ?? [],
											'declarationFunction' => [ self::class, 'hover_overlay_color_style_declaration' ],
										],
									],
								],
							],
						]
					),

					// title.
					$elements->style(
						[
							'attrName' => 'title',
						]
					),
					// caption.
					$elements->style(
						[
							'attrName' => 'caption',
						]
					),
					// pagination.
					$elements->style(
						[
							'attrName' => 'pagination',
						]
					),
					// item.
					$elements->style(
						[
							'attrName'   => 'item',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . '.et_pb_gallery .et_pb_gallery_item',
											'attr'     => $attrs['item']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),
					// image.
					$elements->style(
						[
							'attrName'   => 'image',
							'styleProps' => [
								'attrsFilter'    => function ( $decoration_attrs ) use ( $attrs ) {
									return GalleryModule::filter_image_decoration_attrs( $decoration_attrs, $attrs );
								},
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . '.et_pb_gallery .et_pb_gallery_image',
											'attr'     => $attrs['image']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),
					// Overlay.
					$elements->style(
						[
							'attrName'   => 'overlay',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_overlay:before',
											'attr'     => $attrs['overlay']['innerContent'] ?? [],
											'declarationFunction' => [ self::class, 'icon_font_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector'  => $args['orderClass'] . ' .et_overlay:before',
											'attr'      => $attrs['overlay']['advanced']['zoomIconColor'] ?? [],
											'property'  => 'color',
											'important' => true,
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $args['orderClass'] . ' .et_overlay',
											'attr'     => $attrs['overlay']['advanced']['hoverOverlayColor'] ?? [],
											'declarationFunction' => [ self::class, 'hover_overlay_color_style_declaration' ],
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
	 * Module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function GalleryEdit located in
	 * visual-builder/packages/module-library/src/components/gallery/edit.tsx.
	 *
	 * @param array          $attrs block attributes that were saved by VB.
	 * @param string         $content block content.
	 * @param WP_Block       $block parsed block object that being rendered.
	 * @param ModuleElements $elements instance of ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements class.
	 * @param array          $default_printed_style_attrs default printed style attributes.
	 *
	 * @return string the module HTML output
	 * @since ??
	 */
	public static function render_callback( array $attrs, string $content, WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {
		$hover_icon        = $attrs['overlay']['decoration']['icon']['desktop']['value'] ?? '';
		$hover_icon_tablet = $attrs['overlay']['decoration']['icon']['tablet']['value'] ?? '';
		$hover_icon_phone  = $attrs['overlay']['decoration']['icon']['phone']['value'] ?? '';

		$icon        = ! empty( $hover_icon ) ? Utils::process_font_icon( $hover_icon ) : '';
		$icon_tablet = ! empty( $hover_icon_tablet ) ? Utils::process_font_icon( $hover_icon_tablet ) : '';
		$icon_phone  = ! empty( $hover_icon_phone ) ? Utils::process_font_icon( $hover_icon_phone ) : '';

		// Fullwidth is desktop only attribute.
		$is_fullwidth = 'on' === ( $attrs['module']['advanced']['fullwidth']['desktop']['value'] ?? '' );

		$show_pagination = ModuleUtils::has_value(
			$attrs['pagination']['advanced']['showPagination'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === $value;
				},
			]
		);

		$has_title_and_caption = ModuleUtils::has_value(
			$attrs['module']['advanced']['showTitleAndCaption'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === $value;
				},
			]
		);

		$fullwidth             = $attrs['module']['advanced']['fullwidth']['desktop']['value'] ?? 'off';
		$posts_number          = $attrs['module']['advanced']['postsNumber']['desktop']['value'] ?? '4';
		$gallery_ids           = $attrs['image']['advanced']['galleryIds']['desktop']['value'] ?? [];
		$gallery_orderby       = $attrs['image']['advanced']['galleryOrderby']['desktop']['value'] ?? 'default';
		$gallery_captions      = $attrs['image']['advanced']['galleryCaptions']['desktop']['value'] ?? '';
		$heading_level         = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h2';
		$orientation           = $attrs['image']['advanced']['orientation']['desktop']['value'] ?? '';
		$pagination_text_align = $attrs['pagination']['decoration']['font']['textAlign']['desktop']['value'] ?? '';
		$auto_rotate           = $attrs['module']['advanced']['autoRotate']['desktop']['value'] ?? 'off';
		$auto_rotate_speed     = $attrs['module']['advanced']['autoRotateSpeed']['desktop']['value'] ?? '';
		$module_order_index    = $block->parsed_block['orderIndex'];

		// Get gallery item data.
		$attachments = self::get_gallery_items(
			[
				'gallery_ids'     => $gallery_ids,
				'gallery_orderby' => $gallery_orderby,
				'fullwidth'       => $fullwidth,
				'orientation'     => $orientation,
			]
		);

		if ( empty( $attachments ) ) {
			return '';
		}
		$posts_number = 0 === (int) $posts_number ? 4 : (int) $posts_number;

		$overlay_output = HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class'            => HTMLUtility::classnames(
						[
							'et_overlay'               => true,
							'et_pb_inline_icon'        => ! empty( $icon ),
							'et_pb_inline_icon_tablet' => ! empty( $icon_tablet ),
							'et_pb_inline_icon_phone'  => ! empty( $icon_phone ),
						]
					),
					'data-icon'        => $icon,
					'data-icon-tablet' => $icon_tablet,
					'data-icon-phone'  => $icon_phone,
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);

		$images_count = 0;

		$output = '';
		foreach ( $attachments as $id => $attachment ) {
			$image_render_attributes = [
				'src'   => $attachment->image_src_thumb[0],
				'alt'   => $attachment->image_alt_text,
				'class' => 'wp-image-' . $attachment->ID,
			];

			if ( ! $is_fullwidth && et_is_responsive_images_enabled() ) {
				$image_render_attributes['srcset'] = $attachment->image_src_full[0] . ' 479w, ' . $attachment->image_src_thumb[0] . ' 480w';
				$image_render_attributes['sizes']  = '(max-width:479px) 479px, 100vw';
			}

			$image_html = HTMLUtility::render(
				[
					'tag'        => 'img',
					'attributes' => $image_render_attributes,
				]
			);

			$image_anchor = HTMLUtility::render(
				[
					'tag'               => 'a',
					'attributes'        => [
						'href'  => esc_url( $attachment->image_src_full[0] ),
						'title' => esc_attr( $attachment->post_title ),
					],
					'children'          => [
						$image_html,
						et_core_esc_previously( $overlay_output ),
					],
					'childrenSanitizer' => 'et_core_esc_previously',
				]
			);

			$image_title   = '';
			$image_caption = '';

			if ( ! $is_fullwidth && $has_title_and_caption ) {
				$image_title_classes = HTMLUtility::classnames(
					[
						'et_pb_gallery_title' => true,
					],
					MultiViewUtils::hidden_on_load_class_name(
						$attrs['module']['advanced']['showTitleAndCaption'] ?? [],
						[
							'valueResolver' => function ( $value ) {
								return 'off' === ( $value ?? 'off' ) ? 'hidden' : 'visible';
							},
						]
					)
				);

				if ( trim( $attachment->post_title ) ) {
					$image_title = HTMLUtility::render(
						[
							'tag'        => esc_attr( $heading_level ),
							'attributes' => [
								'class' => $image_title_classes,
							],
							'children'   => wptexturize( $attachment->post_title ),
						]
					);
				}
				if ( trim( $attachment->post_excerpt ) ) {
					$image_caption .= HTMLUtility::render(
						[
							'tag'        => 'p',
							'attributes' => [
								'class' => 'et_pb_gallery_caption',
							],
							'children'   => wptexturize( $attachment->post_excerpt ),
						]
					);
				}
			}

			$image_container = HTMLUtility::render(
				[
					'tag'               => 'div',
					'attributes'        => [
						'class'         => HTMLUtility::classnames(
							[
								'et_pb_gallery_image' => true,
								'landscape'           => 'portrait' !== $orientation,
								'portrait'            => 'portrait' === $orientation,
							],
							BoxShadowClassnames::has_overlay( $attrs['image']['decoration']['boxShadow'] ?? [] )
						),
						'data-per_page' => $posts_number,
					],
					'children'          => [
						$elements->style_components(
							[
								'attrName' => 'image',
							]
						),
						$image_anchor,
					],
					'childrenSanitizer' => 'et_core_esc_previously',
				]
			);

			$gallery_item = HTMLUtility::render(
				[
					'tag'               => 'div',
					'attributes'        => [
						'class' => 'et_pb_gallery_item' .
						( ! $is_fullwidth ? ' et_pb_grid_item' : '' ) .
						sprintf( ' et_pb_gallery_item_%1$s_%2$s', $module_order_index, $images_count ),
					],
					'children'          => [
						$image_container,
						$image_title,
						$image_caption,
					],
					'childrenSanitizer' => 'et_core_esc_previously',
				]
			);

			++$images_count;

			$output .= $gallery_item;
		}

		$pagination_html = '';
		if ( ! $is_fullwidth && $show_pagination ) {
			$pagination_classes = HTMLUtility::classnames(
				[
					'et_pb_gallery_pagination'         => true,
					'et_pb_gallery_pagination_justify' => 'justify' === $pagination_text_align,
				],
				MultiViewUtils::hidden_on_load_class_name(
					$attrs['pagination']['advanced']['showPagination'] ?? [],
					[
						'valueResolver' => function ( $value ) {
							return 'on' === ( $value ?? 'on' ) ? 'visible' : 'hidden';
						},
					]
				)
			);

			$pagination_html = HTMLUtility::render(
				[
					'tag'        => 'div',
					'attributes' => [
						'class' => $pagination_classes,
					],
				]
			);
		}

		$output_wrapper = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class'         => 'et_pb_gallery_items et_post_gallery clearfix',
					'data-per_page' => $posts_number,
				],
				'children'          => $output,
				'childrenSanitizer' => 'et_core_esc_previously',
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
				'htmlAttrs'                => [
					'data-auto-rotate'       => $auto_rotate,
					'data-auto-rotate-speed' => $auto_rotate_speed,
				],
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'id'                       => $block->parsed_block['id'],
				'name'                     => $block->block_type->name,
				'parentAttrs'              => $parent->attrs ?? [],
				'parentId'                 => $parent->id ?? '',
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'moduleCategory'           => $block->block_type->category,
				'children'                 => $elements->style_components(
					[
						'attrName' => 'module',
					]
				) . $output_wrapper . $pagination_html,
			]
		);
	}

	/**
	 * Get Gallery Items.
	 *
	 * @since ??
	 *
	 * @param array $args Gallery Item request params.
	 *
	 * @return array The processed content.
	 */
	public static function get_gallery_items( array $args ) {
		$defaults = [
			'gallery_ids'      => [],
			'gallery_orderby'  => '',
			'gallery_captions' => [],
			'fullwidth'        => 'off',
			'orientation'      => 'landscape',
		];

		$args = wp_parse_args( $args, $defaults );

		$attachments_args = [
			'include'        => $args['gallery_ids'],
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => 'ASC',
			'orderby'        => 'post__in',
		];

		// Woo Gallery module shouldn't display placeholder image when no Gallery image is available.
		// @see https://github.com/elegantthemes/submodule-builder/pull/6706#issuecomment-542275647.
		if ( isset( $args['attachment_id'] ) ) {
			$attachments_args['attachment_id'] = $args['attachment_id'];
		}

		if ( 'rand' === $args['gallery_orderby'] ) {
			$attachments_args['orderby'] = 'rand';
		}

		$width  = 400;
		$height = ( 'landscape' === $args['orientation'] ) ? 284 : 516;

		if ( 'on' === $args['fullwidth'] ) {
			$width  = 1080;
			$height = 9999;
		}

		$width  = (int) apply_filters( 'et_pb_gallery_image_width', $width );
		$height = (int) apply_filters( 'et_pb_gallery_image_height', $height );

		$_attachments = get_posts( $attachments_args );
		$attachments  = [];

		foreach ( $_attachments as $key => $val ) {
			$attachments[ $key ]                  = $_attachments[ $key ];
			$attachments[ $key ]->image_alt_text  = get_post_meta( $val->ID, '_wp_attachment_image_alt', true );
			$attachments[ $key ]->image_src_full  = wp_get_attachment_image_src( $val->ID, 'full' );
			$attachments[ $key ]->image_src_thumb = wp_get_attachment_image_src( $val->ID, [ $width, $height ] );
		}

		return $attachments;
	}

	/**
	 * Get image attachment class.
	 *
	 * - wp-image-{$id}
	 *   Add `wp-image-{$id}` class to let `wp_filter_content_tags()` fill in missing
	 *   height and width attributes on the image. Those attributes are required to add
	 *   loading "lazy" attribute on the image. WP doesn't have specific method to only
	 *   generate this class. It's included in get_image_tag() to generate image tags.
	 *
	 * @since 4.6.4
	 *
	 * @param array   $attrs         All module attributes.
	 * @param string  $source_key    Key of image source.
	 * @param integer $attachment_id Attachment ID. Optional.
	 *
	 * @return string
	 */
	public static function get_image_attachment_class( $attrs, $source_key, $attachment_id = 0 ) {
		$attachment_class = '';

		// 1.a. Find attachment ID by URL. Skip if the source key is empty.
		if ( ! empty( $source_key ) ) {
			$attachment_src = et_()->array_get( $attrs, $source_key, '' );
			$attachment_id  = et_get_attachment_id_by_url( $attachment_src );
		}

		// 1.b. Generate attachment ID class.
		if ( $attachment_id > 0 ) {
			$attachment_class = "wp-image-{$attachment_id}";
		}

		return $attachment_class;
	}

	/**
	 * Loads `GalleryModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/gallery/';

		add_filter( 'divi_conversion_presets_attrs_map', array( GalleryPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
