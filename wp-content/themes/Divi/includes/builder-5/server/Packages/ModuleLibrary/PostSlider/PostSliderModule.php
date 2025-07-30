<?php
/**
 * ModuleLibrary: Post Slider Module class.
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\PostSlider;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\Classnames;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\Blog\BlogModule;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block_Type_Registry;
use WP_Block;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

// phpcs:disable Squiz.Commenting.InlineComment -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

/**
 * `PostSliderModule` is consisted of functions used for Post Slider Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class PostSliderModule implements DependencyInterface {
	/**
	 * Track if the module is currently rendering to prevent unnecessary rendering and recursion.
	 *
	 * @var bool
	 */
	protected static $_rendering = false;

	/**
	 * Module custom CSS fields.
	 *
	 * This function is equivalent of JS function cssFields located in
	 * visual-builder/packages/module-library/src/components/post-slider/custom-css.ts.
	 *
	 * @since ??
	 *
	 * @return array The array of custom CSS fields.
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/post-slider' )->customCssFields;
	}

	/**
	 * Button Alignment Style Declaration
	 *
	 * This function will declare button alignment style for Post Slider module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/post-slider/style-declarations/button-alignment/index.ts
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
	 * Set CSS class names to the module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/post-slider/module-classnames.ts.
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

		$show_arrows          = $attrs['arrows']['advanced']['enable'] ?? 'on';
		$show_pagination      = $attrs['pagination']['advanced']['enable'] ?? 'on';
		$auto                 = $attrs['module']['advanced']['auto']['desktop']['value'] ?? '';
		$auto_speed           = $attrs['module']['advanced']['autoSpeed']['desktop']['value'] ?? '7000';
		$auto_ignore_hover    = $attrs['module']['advanced']['autoIgnoreHover']['desktop']['value'] ?? '';
		$show_image_on_mobile = $attrs['image']['advanced']['showOnMobile']['desktop']['value'] ?? '';
		$image_placement      = $attrs['image']['advanced']['placement']['desktop']['value'] ?? '';
		$show_slide_overlay   = $attrs['slideOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$show_content_overlay = $attrs['contentOverlay']['advanced']['use']['desktop']['value'] ?? '';

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [], [ 'orientation' => false ] ), true );

		$classnames_instance->add( 'et_pb_slider', true );
		$classnames_instance->add( 'et_pb_slider_fullwidth_off', true );
		$classnames_instance->add( 'et_pb_slider_no_arrows', 'off' === $show_arrows );
		$classnames_instance->add( 'et_pb_slider_no_pagination', 'off' === $show_pagination );
		$classnames_instance->add( "et_slider_speed_{$auto_speed}", 'on' === $auto );
		$classnames_instance->add( 'et_slider_auto', 'on' === $auto );
		$classnames_instance->add( 'et_slider_auto_ignore_hover', 'on' === $auto_ignore_hover );
		$classnames_instance->add( 'et_pb_slider_show_image', 'on' === $show_image_on_mobile );
		$classnames_instance->add( 'et_pb_post_slider_image_' . $image_placement, true );
		$classnames_instance->add( 'et_pb_slider_with_overlay', 'on' === $show_slide_overlay );
		$classnames_instance->add( 'et_pb_slider_with_text_overlay', 'on' === $show_content_overlay );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'border' => $attrs['module']['decoration']['border'] ?? $attrs['image']['decoration']['border'] ?? [],
							'link'   => $attrs['module']['advanced']['link'] ?? [],
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
	 * visual-builder/packages/module-library/src/components/post-slider/module-script-data.tsx.
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
		$post_ids       = $args['post_ids'] ?? [];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);

		// Post meta set content.
		$set_content = [];

		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {

				// Post excerpt.
				$set_content[] = [
					'selector'      => $selector . ' .et_pb_post_slide-' . $post_id . ' .et_pb_slide_content > div',
					'data'          => MultiViewUtils::merge_values(
						[
							'contentSource' => $attrs['post']['advanced']['contentSource'] ?? [],
							'excerptManual' => $attrs['post']['advanced']['excerptManual'] ?? [],
							'excerptLength' => $attrs['post']['advanced']['excerptLength'] ?? [],
						]
					),
					'sanitizer'     => 'wp_kses_post',
					'valueResolver' => function ( $value ) use ( $post_id ) {
						$content_source = $value['contentSource'] ?? '';
						$excerpt_manual = $value['excerptManual'] ?? '';
						$excerpt_length = $value['excerptLength'] ?? '';

						return BlogModule::render_content(
							[
								'excerpt_content' => $content_source,
								'show_excerpt'    => 'on',
								'excerpt_manual'  => $excerpt_manual,
								'excerpt_length'  => $excerpt_length,
								'post_id'         => $post_id,
							]
						);
					},
				];
			}
		}

		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'hoverSelector' => $selector,
				'setContent'    => $set_content,
				'setVisibility' => [
					[
						'selector'      => $selector . ' .post-meta',
						'data'          => $attrs['meta']['advanced']['enable'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
					[
						'selector'      => $selector . ' .et_pb_more_button',
						'data'          => $attrs['button']['advanced']['enable'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
					[
						'selector'      => $selector . ' .et_pb_slide_image',
						'data'          => $attrs['image']['advanced']['enable'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
					[
						'selector'      => $selector . ' .et-pb-slider-arrows',
						'data'          => $attrs['arrows']['advanced']['enable'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
					[
						'selector'      => $selector . ' .et-pb-controllers',
						'data'          => $attrs['pagination']['advanced']['enable'] ?? [],
						'valueResolver' => function ( $value ) {
							return 'on' === $value ? 'visible' : 'hidden';
						},
					],
				],
			]
		);
	}

	/**
	 * Style declaration for post slider's border.
	 *
	 * This function is used to generate the style declaration for the border of a post slider module.
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
	 * Set CSS styles to the module.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/post-slider/module-styles.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string         $id                       Module unique ID.
	 *     @type string         $name                     Module name with namespace.
	 *     @type array          $attrs                    Module attributes.
	 *     @type array          $parentAttrs              Parent module attributes.
	 *     @type array          $siblingAttrs             Sibling module attributes.
	 *     @type array          $defaultPrintedStyleAttrs Default printed style attributes.
	 *     @type string         $orderClass               Module CSS selector.
	 *     @type string         $parentOrderClass         Parent module CSS selector.
	 *     @type string         $wrapperOrderClass        Wrapper module CSS selector.
	 *     @type array          $settings                 Custom settings.
	 *     @type ModuleElements $elements                 ModuleElements instance.
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
		$attrs                       = $args['attrs'] ?? [];
		$elements                    = $args['elements'];
		$settings                    = $args['settings'] ?? [];
		$order_class                 = $args['orderClass'] ?? '';
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
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} .et-pb-slider-arrows .et-pb-arrow-prev",
													"{$args['orderClass']} .et-pb-slider-arrows .et-pb-arrow-next",
												]
											),
											'attr'     => $attrs['arrows']['advanced']['color'] ?? [],
											'property' => 'color',
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/text',
										'props'         => [
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
											'selector' => "{$order_class} .et_pb_slide .et_pb_slide_description .et_pb_slide_title, {$order_class} .et_pb_slide .et_pb_slide_description .et_pb_slide_title a, {$order_class} .et_pb_slide .et_pb_slide_description .et_pb_slide_content, {$order_class} .et_pb_slide .et_pb_slide_description .et_pb_slide_content .post-meta, {$order_class} .et_pb_slide .et_pb_slide_description .et_pb_slide_content .post-meta a, {$order_class} .et_pb_slide .et_pb_slide_description .et_pb_slide_content .et_pb_button",
											'propertySelectors' => [
												'text' => [
													'desktop' => [
														'value' => [
															'text-align' => "{$order_class} .et_pb_slide .et_pb_slide_description",
														],
													],
												],
												'textShadow' => [
													'desktop' => [
														'value' => [
															'text-shadow' => "{$order_class} .et_pb_slide .et_pb_slide_description",
														],
													],
												],
											],
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
					// content.
					$elements->style(
						[
							'attrName' => 'content',
						]
					),
					// meta.
					$elements->style(
						[
							'attrName' => 'meta',
						]
					),
					// image.
					$elements->style(
						[
							'attrName' => 'image',
						]
					),
					// content overlay.
					$elements->style(
						[
							'attrName' => 'contentOverlay',
						]
					),
					// slide overlay.
					$elements->style(
						[
							'attrName' => 'slideOverlay',
						]
					),
					// button.
					$elements->style(
						[
							'attrName'   => 'button',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$order_class} .et_pb_button_wrapper",
											'attr'     => $attrs['button']['decoration']['button'] ?? [],
											'declarationFunction' => [ self::class, 'button_alignment_style_declaration' ],
										],
									],
								],
							],
						]
					),

					// pagination.
					$elements->style(
						[
							'attrName' => 'pagination',
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
	 * This function is equivalent of JS function PostSliderEdit located in
	 * visual-builder/packages/module-library/src/components/post-slider/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements Instance of ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements class.
	 *
	 * @return string The module HTML output.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		global $paged;

		if ( self::$_rendering ) {
			return '';
		}

		self::$_rendering = true;

		$use_current_loop     = $attrs['post']['advanced']['currentLoop']['desktop']['value'] ?? 'off';
		$posts_per_page       = $attrs['post']['advanced']['number']['desktop']['value'] ?? '';
		$categories           = $attrs['post']['advanced']['categories']['desktop']['value'] ?? [];
		$orderby              = $attrs['post']['advanced']['orderby']['desktop']['value'] ?? '';
		$offset               = $attrs['post']['advanced']['offset']['desktop']['value'] ?? '';
		$show_slide_overlay   = $attrs['slideOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$image_placement      = $attrs['image']['advanced']['placement']['desktop']['value'] ?? '';
		$show_content_overlay = $attrs['contentOverlay']['advanced']['use']['desktop']['value'] ?? '';
		$heading_level        = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h2';
		$content_source       = $attrs['post']['advanced']['contentSource']['desktop']['value'] ?? '';
		$excerpt_length       = $attrs['post']['advanced']['excerptLength']['desktop']['value'] ?? '';
		$excerpt_manual       = $attrs['post']['advanced']['excerptManual']['desktop']['value'] ?? '';

		$show_image = ModuleUtils::has_value(
			$attrs['image']['advanced']['enable'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === $value;
				},
			]
		);

		$show_post_meta = ModuleUtils::has_value(
			$attrs['meta']['advanced']['enable'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === $value;
				},
			]
		);

		$show_button = ModuleUtils::has_value(
			$attrs['button']['advanced']['enable'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === $value;
				},
			]
		);

		$query_args = array(
			'posts_per_page' => (int) $posts_per_page,
			'post_status'    => array( 'publish', 'private' ),
			'perm'           => 'readable',
			'cat'            => $categories,
		);

		if ( 'on' === $use_current_loop ) {
			$query_args['cat'] = '';
			$orderby           = '';
		}

		// WP_Query doesn't return sticky posts when it performed via Ajax.
		// This happens because `is_home` is false in this case, but on FE it's true if no category set for the query.
		// Set `is_home` = true to emulate the FE behavior with sticky posts in VB.
		if ( empty( $query_args['cat'] ) ) {
			add_action(
				'pre_get_posts',
				function ( $query ) {
					if ( true === $query->get( 'et_is_home' ) ) {
						$query->is_home = true;
					}
				}
			);

			$query_args['et_is_home'] = true;
		}

		if ( 'date_desc' !== $orderby ) {
			switch ( $orderby ) {
				case 'date_asc':
					$query_args['orderby'] = 'date';
					$query_args['order']   = 'ASC';
					break;
				case 'title_asc':
					$query_args['orderby'] = 'title';
					$query_args['order']   = 'ASC';
					break;
				case 'title_desc':
					$query_args['orderby'] = 'title';
					$query_args['order']   = 'DESC';
					break;
				case 'rand':
					$query_args['orderby'] = 'rand';
					break;
			}
		}

		if ( '' !== $offset && ! empty( $offset ) ) {
			/**
			 * Offset + pagination don't play well. Manual offset calculation required
			 *
			 * @see: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
			 */
			if ( $paged > 1 ) {
				$query_args['offset'] = ( ( $paged - 1 ) * intval( $posts_per_page ) ) + intval( $offset );
			} else {
				$query_args['offset'] = intval( $offset );
			}
		}

		$query = new \WP_Query( $query_args );

		$slides   = [];
		$post_ids = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_ids[] = get_the_ID();

				$slide_classnames = new Classnames();

				$has_image_at_left_or_right = has_post_thumbnail() && $show_image && in_array( $image_placement, [ 'left', 'right' ], true );

				$slide_classnames->add( 'et_pb_slide', true );
				$slide_classnames->add( 'et_pb_slide_with_image', $has_image_at_left_or_right );
				$slide_classnames->add( 'et_pb_media_alignment_center', $has_image_at_left_or_right );
				$slide_classnames->add( 'et_pb_slide_with_no_image', ! has_post_thumbnail() || $show_image );
				$slide_classnames->add( 'et_pb_post_slide-' . get_the_ID(), true );

				// Slide/Background Overlay.
				$slide_overlay = 'on' === $show_slide_overlay ? HTMLUtility::render(
					[
						'tag'        => 'div',
						'attributes' => [
							'class' => 'et_pb_slide_overlay_container',
						],
					]
				) : '';

				// Slide/Featured Image.
				$slide_image = has_post_thumbnail() && $show_image ? HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_slide_image',
						],
						'children'          => get_the_post_thumbnail(),
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				) : '';

				// Post title.
				$title = HTMLUtility::render(
					[
						'tag'               => $heading_level,
						'attributes'        => [
							'class' => 'et_pb_slide_title',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
						'children'          => HTMLUtility::render(
							[
								'tag'        => 'a',
								'attributes' => [
									'href' => get_the_permalink(),
								],
								'children'   => get_the_title(),
							]
						),
					]
				);

				// Post meta.
				$post_meta = $show_post_meta ? HTMLUtility::render(
					[
						'tag'               => 'p',
						'attributes'        => [
							'class' => 'post-meta',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
						'children'          => implode(
							' | ',
							[
								et_get_safe_localization( sprintf( __( 'by %s', 'et_builder' ), '<span class="author vcard">' . et_pb_get_the_author_posts_link() . '</span>' ) ),
								'<span class="published">' . esc_html( get_the_date() ) . '</span>',
								get_the_category_list( ', ' ),
								esc_html(
									sprintf(
										_nx(
											'%s Comment',
											'%s Comments',
											get_comments_number(),
											'number of comments',
											'et_builder'
										),
										number_format_i18n( get_comments_number() )
									)
								),
							]
						),
					]
				) : '';

				// Post Content.
				$slide_content = HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_slide_content',
						],
						'children'          => [
							$post_meta,
							HTMLUtility::render(
								[
									'tag'               => 'div',
									'attributes'        => [
										'class' => 'et_pb_slide_content_body',
									],
									'childrenSanitizer' => 'wp_kses_post',
									'children'          => BlogModule::render_content(
										[
											'excerpt_content' => $content_source,
											'show_excerpt' => 'on',
											'excerpt_manual' => $excerpt_manual,
											'excerpt_length' => $excerpt_length,
											'post_id'      => get_the_ID(),
										]
									),
								]
							),
						],
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				);

				// Read more Button.
				$show_button_on_mobile = $attrs['button']['advanced']['showOnMobile']['desktop']['value'] ?? '';
				$button                = $elements->render(
					[
						'attrName'     => 'button',
						'attributes'   => [
							'class' => 'off' === $show_button_on_mobile ? 'et_pb_more_button et_pb_button et-hide-mobile' : 'et_pb_more_button et_pb_button',
						],
						'elementProps' => [
							'innerContent' => array_replace_recursive(
								$attrs['button']['innerContent'] ?? [],
								[
									'desktop' => [
										'value' => [
											'linkUrl' => get_the_permalink(),
										],
									],
								]
							),
						],
					]
				);

				// Slide Description.
				$slide_description = HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_slide_description',
						],
						'children'          => 'on' === $show_content_overlay ? [
							HTMLUtility::render(
								[
									'tag'               => 'div',
									'attributes'        => [
										'class' => 'et_pb_text_overlay_wrapper',
									],
									'children'          => [
										$title,
										$slide_content,
									],
									'childrenSanitizer' => 'et_core_esc_previously',
								]
							),
							$show_button ? $button : '',
						] : [
							$title,
							$slide_content,
							$show_button ? $button : '',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				);

				// Slide container inner.
				$slide_container_inner = HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_slider_container_inner',
						],
						'children'          => [
							! in_array( $image_placement, [ 'background', 'bottom' ], true ) ? $slide_image : '',
							$slide_description,
							'bottom' === $image_placement ? $slide_image : '',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				);

				// Slide container.
				$slide_container = HTMLUtility::render(
					[
						'tag'               => 'div',
						'attributes'        => [
							'class' => 'et_pb_container clearfix',
						],
						'children'          => $slide_container_inner,
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				);

				// Featured image URL.
				$featured_image = esc_url( wp_get_attachment_url( get_post_thumbnail_id() ) );

				// Slides.
				$slides[] = $elements->render(
					[
						'tagName'           => 'div',
						'attributes'        => [
							'class' => $slide_classnames->value(),
							'style' => [
								'background-image' => [
									'attr'          => $attrs['image']['advanced']['enable'] ?? [],
									'valueResolver' => function ( $value ) use ( $image_placement, $featured_image ) {

										if ( ! empty( $featured_image ) && 'background' === $image_placement && 'on' === $value ) {
											return 'url(' . ( $featured_image ) . ')';
										}
										return '';
									},
									'selector'      => '{{selector}} .et_pb_post_slide-' . get_the_ID(),
								],
							],
						],
						'children'          => [
							$slide_overlay,
							$slide_container,
						],
						'childrenSanitizer' => 'et_core_esc_previously',
					]
				);
			}
		}

		wp_reset_postdata();

		// All slides.
		$slides_html = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_slides',
				],
				'children'          => $slides,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);

		$no_posts_output = '';

		ob_start();

		get_template_part( 'includes/no-results', 'index' );

		if ( ob_get_length() > 0 ) {
			$no_posts_output  = ob_get_clean();
			$no_posts_content = '<div class="et_pb_no_results">' . $no_posts_output . '</div>';
			$no_posts_html    = HTMLUtility::render(
				[
					'tag'               => 'div',
					'attributes'        => [
						'class' => 'et_pb_slides',
					],
					'children'          => $no_posts_content,
					'childrenSanitizer' => 'et_core_esc_previously',
				]
			);
		}

		$module_html = Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'id'                  => $block->parsed_block['id'],
				'elements'            => $elements,
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => function ( $args ) use ( $post_ids ) {
					PostSliderModule::module_script_data(
						array_merge(
							$args,
							[
								'post_ids' => $post_ids,
							]
						)
					);
				},
				'parentAttrs'         => [],
				'parentId'            => '',
				'parentName'          => '',
				'children'            => ! empty( $slides ) ? [
					$elements->style_components(
						[
							'attrName' => 'module',
						]
					),
					$slides_html,
				] : $no_posts_html,
			]
		);

		self::$_rendering = false;
		return $module_html;
	}

	/**
	 * Loads `PostSliderModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		add_filter( 'divi_conversion_presets_attrs_map', array( PostSliderPresetAttrsMap::class, 'get_map' ), 10, 2 );

		add_filter(
			'divi.moduleLibrary.conversion.moduleConversionOutline',
			function ( $conversion_outline, $module_name ) {

				// Add custom conversion functions for this module
				if ( 'divi/post-slider' !== $module_name ) {
					return $conversion_outline;
				}

				// Non static expansion functions like this
				// dont automatically get converted correctly in the
				// autogenerated .json conversion outline,
				// so lets hook in and provide the correct conversion functions.
				//
				// valueExpansionFunctionMap: {
				//   text_border_radius: borderValueConversionFunctionMap.radius,
				// },
				$conversion_outline['valueExpansionFunctionMap'] = [
					'text_border_radius' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertBorderRadii',
				];

				return $conversion_outline;
			},
			10,
			2
		);

		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/post-slider/';

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
