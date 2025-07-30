<?php
/**
 * ModuleLibrary: PostTitle Module class.
 *
 * @package Builder\Packages\ModuleLibrary\PostTitleModule
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\PostTitle;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentPosts;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * `PostTitleModule` is consisted of functions used for PostTitle Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class PostTitleModule implements DependencyInterface {

	/**
	 * Filters the module.decoration attributes.
	 *
	 * This function is equivalent of JS function filterModuleDecorationAttrs located in
	 * visual-builder/packages/module-library/src/components/post-title/attrs-filter/filter-module-decoration-attrs/index.ts.
	 *
	 * @since ??
	 *
	 * @param array  $decoration_attrs The original decoration attributes.
	 * @param array  $attrs The attributes of the post title module.
	 * @param string $featured_image_url The featured image URL.
	 *
	 * @return array The filtered decoration attributes.
	 */
	public static function filter_module_decoration_attrs( array $decoration_attrs, array $attrs, string $featured_image_url ): array {
		// Attribute `featuredImage.advanced.placement` is desktop only.
		$placement = $attrs['featuredImage']['advanced']['placement']['desktop']['value'] ?? null;

		// Checking if the value of the `placement` variable is not equal to `background`. If it
		// is not equal, it means that the featured image is not set to be displayed as a background, so the
		// function returns the original `decorationAttrs` without any modifications.
		if ( 'background' !== $placement ) {
			return $decoration_attrs;
		}

		$enabled_attr     = $attrs['featuredImage']['advanced']['enabled'] ?? [];
		$background_attrs = $decoration_attrs['background'] ?? [];

		// Iterate through each `attrState` for every `attrBreakpoint` in the `enabledAttr` object.
		// It checks if the featured image is active for that combination. If the value is `on`,
		// it updates the `backgroundAttrs.image.url` with the the value of `currentPage.thumbnailUrl`.
		// If not, it is checking the current value of the `backgroundAttrs.image.url`, if it is undefined and
		// the breakpoint is not `desktop` or the state is not `value` then it updates the `backgroundAttrs.image.url`
		// value to an empty string. This is intended to prevent the background image from being inherited
		// from a larger breakpoint or state.
		foreach ( $enabled_attr as $attr_breakpoint => $attr_state_values ) {
			foreach ( $attr_state_values as $attr_state => $enabled ) {
				if ( ! array_key_exists( $attr_breakpoint, $background_attrs ) ) {
					$background_attrs[ $attr_breakpoint ] = [];
				}

				if ( ! array_key_exists( $attr_state, $background_attrs[ $attr_breakpoint ] ) ) {
					$background_attrs[ $attr_breakpoint ][ $attr_state ] = [];
				}

				if ( ! array_key_exists( 'image', $background_attrs[ $attr_breakpoint ][ $attr_state ] ) ) {
					$background_attrs[ $attr_breakpoint ][ $attr_state ]['image'] = [];
				}

				if ( 'on' === $enabled ) {
					$background_attrs[ $attr_breakpoint ][ $attr_state ]['image']['url'] = $featured_image_url;
				} else {
					$url = $background_attrs[ $attr_breakpoint ][ $attr_state ]['image']['url'] ?? null;

					if ( null === $url && ( 'desktop' !== $attr_breakpoint || 'value' !== $attr_state ) ) {
						$background_attrs[ $attr_breakpoint ][ $attr_state ]['image']['url'] = '';
					}
				}
			}
		}

		return array_merge(
			$decoration_attrs,
			[
				'background' => $background_attrs,
			]
		);
	}

	/**
	 * Filters the featuredImage.decoration attributes.
	 *
	 * This function is equivalent of JS function filterFeaturedImageDecorationAttrs located in
	 * visual-builder/packages/module-library/src/components/post-title/attrs-filter/filter-featured-image-decoration-attrs/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $decoration_attrs The decoration attributes to be filtered.
	 * @param array $attrs           The whole module attributes.
	 *
	 * @return array The filtered decoration attributes.
	 */
	public static function filter_featured_image_decoration_attrs( array $decoration_attrs, array $attrs ): array {
		// Checking if the `decorationAttrs` array has a key called 'sizing'. If it does
		// not have this key, it means that the decoration attributes of the featured image do not
		// include any sizing information. In this case, the function simply returns the `decorationAttrs`
		// array as is, without making any changes.
		if ( ! array_key_exists( 'sizing', $decoration_attrs ) ) {
			return $decoration_attrs;
		}

		// Attribute `featuredImage.advanced.placement` is desktop only.
		$placement = $attrs['featuredImage']['advanced']['placement']['desktop']['value'] ?? null;

		// Checking if the value of the `placement` variable is equal to the string `background`.
		// If it is, it means that the featured image is set to be displayed as a background
		// image. In this case, the function returns a new array with the same keys as the
		// `decorationAttrs` array, but with an empty `sizing` key. This effectively removes any sizing
		// attributes from the decoration attributes of the featured image.
		if ( 'background' === $placement ) {
			return array_merge(
				$decoration_attrs,
				[
					'sizing' => [],
				]
			);
		}

		// Creating a new array called sizingAttrs and copying the values of the `decorationAttrs.sizing` array into it.
		$sizing_attrs = $decoration_attrs['sizing'] ?? [];

		// Iterate through each `attrState` for every `attrBreakpoint` in the `sizingAttrs` array.
		// It checks if the featured image is active for that combination. If not, it deletes its sizing attributes.
		// If it is, and `forceFullwidth` is turned on, it removes `width`, `maxWidth`, and `alignment` attributes
		// for that combination.
		foreach ( $sizing_attrs as $attr_breakpoint => $attr_state ) {
			foreach ( $attr_state as $key => $value ) {
				// Value of the `featuredImage.advanced.enabled` property for current `attrBreakpoint` and `attrState`.
				$enabled = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs['featuredImage']['advanced']['enabled'] ?? [],
						'state'      => $key,
						'breakpoint' => $attr_breakpoint,
					]
				);

				// Checking if the `enabled` variable is not equal to the string `on`. If it is not equal, it means
				// that the featured image is not enabled for that particular combination of `attrBreakpoint` and
				// `attrState`. In this case, it updates the `$sizingAttrs[$attrBreakpoint][$key]` array to an
				// empty array, effectively removing any sizing attributes for that combination.
				if ( 'on' !== $enabled ) {
						$sizing_attrs[ $attr_breakpoint ][ $key ] = [];
						continue;
				}

				// Attribute `featuredImage.advanced.forceFullwidth` is desktop only.
				$force_fullwidth = $attrs['featuredImage']['advanced']['forceFullwidth']['desktop']['value'] ?? null;

				// Checking if the value of the `forceFullwidth` variable is equal to the string `on`.
				// If it is, it means that the `forceFullwidth` option is turned on for the featured image. In
				// this case, the code block sets the `width`, `maxWidth`, and `alignment` attributes of the
				// `$sizingAttrs[$attrBreakpoint][$key]` array to `null`. This effectively removes any width,
				// maxWidth, and alignment attributes for that particular combination of `attrBreakpoint` and
				// `attrState`.
				if ( 'on' === $force_fullwidth ) {
					$sizing_attrs[ $attr_breakpoint ][ $key ]['width']     = null;
					$sizing_attrs[ $attr_breakpoint ][ $key ]['maxWidth']  = null;
					$sizing_attrs[ $attr_breakpoint ][ $key ]['alignment'] = null;
				}
			}
		}

		return array_merge(
			$decoration_attrs,
			[
				'sizing' => $sizing_attrs,
			]
		);
	}

	/**
	 * Filters the textWrapper.decoration attributes.
	 *
	 * This function is equivalent of JS function filterTextWrapperDecorationAttrs located in
	 * visual-builder/packages/module-library/src/components/post-title/attrs-filter/filter-text-wrapper-decoration-attrs/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $decoration_attrs The original decoration attributes.
	 * @param array $attrs The attributes of the post title module.
	 *
	 * @return array The filtered decoration attributes.
	 */
	public static function filter_text_wrapper_decoration_attrs( array $decoration_attrs, array $attrs ): array {
		// Checking if the `decorationAttrs` array has a key called 'background'. If it does
		// not have this key, it means that the background attribute is not present in the
		// `decorationAttrs` array. In this case, the function returns the `decorationAttrs` array as it is
		// without any modifications.
		if ( ! array_key_exists( 'background', $decoration_attrs ) ) {
			return $decoration_attrs;
		}

		// Attribute `textWrapper.advanced.useBackground` is desktop only.
		$use_background = $attrs['textWrapper']['advanced']['useBackground']['desktop']['value'] ?? null;

		// Checking if the value of the `useBackground` variable is not equal to the string `on`.
		// If the condition is true, it means that the background should not be used, so it returns a
		// new array with the same keys as `decorationAttrs`, but with an empty `background` key.
		// This effectively removes the background from the filtered decoration attributes.
		if ( 'on' !== $use_background ) {
			return array_merge( $decoration_attrs, [ 'background' => [] ] );
		}

		return $decoration_attrs;
	}

	/**
	 * Custom CSS fields
	 *
	 * This function is equivalent of JS const cssFields located in
	 * visual-builder/packages/module-library/src/components/post-title/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/post-title' )->customCssFields;
	}

	/**
	 * Module classnames function for post type module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/post-title/module-classnames.ts.
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

		$featured_image_placement = $attrs['featuredImage']['advanced']['placement']['desktop']['value'] ?? 'below';

		if ( 'background' === $featured_image_placement ) {
			$featured_image_enabled = $attrs['featuredImage']['advanced']['enabled']['desktop']['value'] ?? 'on';

			if ( 'on' === $featured_image_enabled ) {
				$classnames_instance->add( 'et_pb_featured_bg' );
			}
		}

		// Text options.
		$classnames_instance->add( TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] ?? [] ), true );

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
	 * Set script data of used module options.
	 *
	 * This function is equivalent of JS function ModuleScriptData located in
	 * visual-builder/packages/module-library/src/components/post-title/module-script-data.tsx.
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

		$post_featured_image     = self::get_featured_image();
		$post_featured_image_src = $post_featured_image['src'] ?? '';

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName'        => 'module',
				'scriptDataProps' => [
					'attrs' => self::filter_module_decoration_attrs(
						array_merge(
							$attrs['module']['decoration'] ?? [],
							[
								'link' => $args['attrs']['module']['advanced']['link'] ?? [],
							]
						),
						$attrs,
						$post_featured_image_src
					),
				],
			]
		);

		$placement = $attrs['featuredImage']['advanced']['placement']['desktop']['value'] ?? 'below';

		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'setClassName'  => [
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_featured_bg' => $attrs['featuredImage']['advanced']['enabled'] ?? [],
						],
						'valueResolver' => function ( $value ) use ( $placement ) {
							return 'on' === ( $value ?? 'on' ) && 'background' === $placement ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * PostTitle Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/post-title/module-styles.tsx.
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
	 *      @type ModuleElements $elements         ModuleElements instance.
	 * }
	 */
	public static function module_styles( $args ) {
		$order_class                 = $args['orderClass'] ?? '';
		$attrs                       = $args['attrs'] ?? [];
		$elements                    = $args['elements'];
		$settings                    = $args['settings'] ?? [];
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];
		$post_featured_image         = self::get_featured_image();
		$post_featured_image_src     = $post_featured_image['src'] ?? '';

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
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs, $post_featured_image_src ) {
									return PostTitleModule::filter_module_decoration_attrs( $decoration_attrs, $attrs, $post_featured_image_src );
								},
								'advancedStyles'           => [
									[
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$order_class} .entry-title",
													"{$order_class} .et_pb_title_meta_container",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$order_class}.et_pb_featured_bg,{$order_class}",
											'attr'     => $attrs['module']['decoration']['border'] ?? [],
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
							'attrName'   => 'title',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['title']['decoration'] ?? [],
							],
						]
					),
					// Meta.
					$elements->style(
						[
							'attrName'   => 'meta',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['meta']['decoration'] ?? [],
							],
						]
					),
					// Text Wrapper.
					$elements->style(
						[
							'attrName'   => 'textWrapper',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['textWrapper']['decoration'] ?? [],
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs ) {
									return PostTitleModule::filter_text_wrapper_decoration_attrs( $decoration_attrs, $attrs );
								},
								'advancedStyles'           => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $order_class . ' .et_pb_title_container',
											'attr'     => $attrs['textWrapper']['advanced']['useBackground'] ?? [],
											'declarationFunction' => [ self::class, 'title_container_style_declaration' ],
										],
									],
								],
							],
						]
					),

					// Featured Image.
					$elements->style(
						[
							'attrName'   => 'featuredImage',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['featuredImage']['decoration'] ?? [],
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs ) {
									return PostTitleModule::filter_featured_image_decoration_attrs( $decoration_attrs, $attrs );
								},
								'advancedStyles'           => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $order_class . ' .et_pb_title_featured_container',
											'attr'     => $attrs['featuredImage']['decoration']['sizing'] ?? [],
											'declarationFunction' => function ( array $params ) use ( $attrs ) {
												return PostTitleModule::title_featured_container_style_declaration( $params, $attrs );
											},
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => $order_class . ' .et_pb_image_wrap',
											'attr'     => $attrs['featuredImage']['decoration']['sizing'] ?? [],
											'declarationFunction' => function ( array $params ) use ( $attrs ) {
												return PostTitleModule::image_wrap_style_declaration( $params, $attrs );
											},
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
	 * PostTitle module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function CtaEdit located in
	 * visual-builder/packages/module-library/src/components/post-title/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param \WP_Block      $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string HTML rendered of PostTitle module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$post_featured_image     = self::get_featured_image();
		$post_featured_image_src = $post_featured_image['src'] ?? '';

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'id'                  => $block->parsed_block['id'],
				'elements'            => $elements,
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'parentAttrs'         => [],
				'parentId'            => '',
				'parentName'          => '',
				'children'            => [
					$elements->style_components(
						[
							'attrName'             => 'module',
							'styleComponentsProps' => [
								'attrs' => self::filter_module_decoration_attrs( $attrs['module']['decoration'] ?? [], $attrs, $post_featured_image_src ),
							],
						]
					),
					self::render_featured_image( $attrs, 'above', $elements ),
					HTMLUtility::render(
						[
							'tag'               => 'div',
							'tagEscaped'        => true,
							'attributes'        => [
								'class' => 'et_pb_title_container',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => [
								self::render_title( $attrs, $elements ),
								self::render_meta( $attrs, $elements ),
							],
						]
					),
					self::render_featured_image( $attrs, 'below', $elements ),
				],
			]
		);
	}

	/**
	 * Render the title.
	 *
	 * @since ??
	 *
	 * @param array          $attrs The module attributes.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string The rendered title.
	 */
	public static function render_title( array $attrs, ModuleElements $elements ): string {
		$heading_level = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h1';

		return $elements->render(
			[
				'tagName'    => $heading_level,
				'attributes' => [
					'class' => 'entry-title',
				],
				'children'   => [
					'attr'          => $attrs['title']['advanced']['showTitle'] ?? [],
					'valueResolver' => function ( $value ) {
						if ( 'on' !== ( $value ?? 'on' ) ) {
							return '';
						}

						return html_entity_decode( DynamicContentPosts::get_current_page_title() );
					},
					'selector'      => '{{selector}} .entry-title',
				],
			]
		);
	}

	/**
	 * Render the meta.
	 *
	 * @since ??
	 *
	 * @param array          $attrs The module attributes.
	 * @param ModuleElements $elements ModuleElements instance.
	 *
	 * @return string The rendered meta.
	 */
	public static function render_meta( array $attrs, ModuleElements $elements ): string {
		return $elements->render(
			[
				'tagName'           => 'p',
				'tagEscaped'        => true,
				'attributes'        => [
					'class' => 'et_pb_title_meta_container',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					'attr'          => MultiViewUtils::merge_values( $attrs['meta']['advanced'] ),
					'valueResolver' => function ( $value ) {
						if ( 'on' !== $value['showMeta'] ) {
							return '';
						}

						$meta = [];

						$meta_author = self::render_meta_author( $value['showAuthor'] );

						if ( $meta_author ) {
							$meta[] = $meta_author;
						}

						$meta_date = self::render_meta_date( $value['showDate'], $value['dateFormat'] ?? null );

						if ( $meta_date ) {
							$meta[] = $meta_date;
						}

						$meta_categories = self::render_meta_categories( $value['showCategories'] );

						if ( $meta_categories ) {
							$meta[] = $meta_categories;
						}

						$meta_comments_count = self::render_meta_comments_count( $value['showCommentsCount'] );

						if ( $meta_comments_count ) {
							$meta[] = $meta_comments_count;
						}

						return implode( ' | ', $meta );
					},
					'selector'      => '{{selector}} .et_pb_title_meta_container',
				],
			]
		);
	}

	/**
	 * Retrieves the author data for a post.
	 *
	 * This function retrieves the author name, author URL, and author URL title for a given post.
	 * The logic used here is cherry picked from the D4 et_fb_current_page_params function.
	 *
	 * @since ??
	 *
	 * @return array An array containing the author name, author URL, and author URL title.
	 */
	public static function get_author_data(): array {
		global $post, $authordata;

		// Fallback for preview.
		if ( empty( $authordata ) && isset( $post->post_author ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- A fallback to set global $authordata.
			$authordata = get_userdata( $post->post_author );
		}

		wp_reset_postdata();

		return [
			'authorName'     => esc_html( get_the_author() ),
			'authorUrl'      => isset( $authordata->ID ) && isset( $authordata->user_nicename ) ? esc_html( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ) : esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			'authorUrlTitle' => sprintf( esc_html__( 'Posts by %s', 'et_builder' ), get_the_author() ),
		];
	}

	/**
	 * Render the meta author.
	 *
	 * @since ??
	 *
	 * @param string $show_author The show author attribute.
	 *
	 * @return string The rendered meta author.
	 */
	public static function render_meta_author( string $show_author ): string {
		if ( 'on' !== $show_author ) {
			return '';
		}

		$author_data = self::get_author_data();

		return implode(
			'',
			[
				__( 'by', 'et_builder' ),
				' ',
				HTMLUtility::render(
					[
						'tag'               => 'span',
						'tagEscaped'        => true,
						'attributes'        => [
							'class' => 'author vcard',
						],
						'childrenSanitizer' => 'et_core_esc_previously',
						'children'          => HTMLUtility::render(
							[
								'tag'        => 'a',
								'tagEscaped' => true,
								'attributes' => [
									'href'  => $author_data['authorUrl'],
									'title' => $author_data['authorUrlTitle'],
								],
								'children'   => html_entity_decode( $author_data['authorName'] ),
							]
						),
					]
				),
			]
		);
	}

	/**
	 * Render the meta date.
	 *
	 * @since ??
	 *
	 * @param string $show_date The show date attribute.
	 * @param string $date_format The date format.
	 *
	 * @return string The rendered meta date.
	 */
	public static function render_meta_date( string $show_date, ?string $date_format = 'F j, Y' ): string {
		if ( 'on' !== $show_date || ! $date_format ) {
			return '';
		}

		return HTMLUtility::render(
			[
				'tag'        => 'span',
				'tagEscaped' => true,
				'attributes' => [
					'class' => 'published',
				],
				'children'   => get_the_time( $date_format, et_core_get_main_post_id() ),
			]
		);
	}

	/**
	 * Render the meta categories.
	 *
	 * @since ??
	 *
	 * @param string $show_categories The show categories attribute.
	 *
	 * @return string The rendered meta categories.
	 */
	public static function render_meta_categories( string $show_categories ): string {
		if ( 'on' !== $show_categories || ! is_singular( 'post' ) ) {
			return '';
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		return get_the_category_list( ', ', '', $post_id );
	}

	/**
	 * Render the meta comments count.
	 *
	 * @since ??
	 *
	 * @param string $show_comments_count The show comments count attribute.
	 *
	 * @return string The rendered meta comments count.
	 */
	public static function render_meta_comments_count( string $show_comments_count ): string {
		if ( 'on' !== $show_comments_count ) {
			return '';
		}

		return et_pb_get_comments_popup_link( esc_html__( '0 comments', 'et_builder' ), esc_html__( '1 comment', 'et_builder' ), '% ' . esc_html__( 'comments', 'et_builder' ) ) ?? '';
	}

	/**
	 * Render the featured image.
	 *
	 * @since ??
	 *
	 * @param array          $attrs The module attributes.
	 * @param string         $location The featured image location.
	 * @param ModuleElements $elements ModuleElements instance.
	 * @param bool           $has_wrapper Whether to wrap the featured image in a container.
	 *
	 * @return string The rendered featured image.
	 */
	public static function render_featured_image( array $attrs, string $location, ModuleElements $elements, ?bool $has_wrapper = false ): string {
		$enabled = ModuleUtils::has_value(
			$attrs['featuredImage']['advanced']['enabled'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'on' === ( $value ?? 'on' );
				},
			]
		);

		if ( ! $enabled ) {
			return '';
		}

		$placement = $attrs['featuredImage']['advanced']['placement']['desktop']['value'] ?? 'below';

		if ( $location !== $placement ) {
			return '';
		}

		$post_featured_image = self::get_featured_image();

		if ( ! $post_featured_image ) {
			return '';
		}

		$children = HTMLUtility::render(
			[
				'tag'               => 'span',
				'tagEscaped'        => true,
				'attributes'        => [
					'class' => 'et_pb_image_wrap',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => HTMLUtility::render(
					[
						'tag'        => 'img',
						'tagEscaped' => true,
						'attributes' => [
							'class' => $post_featured_image['class'],
							'src'   => $post_featured_image['src'],
							'alt'   => $post_featured_image['alt'],
							'title' => $post_featured_image['title'],
						],
					]
				),
			]
		);

		if ( $has_wrapper ) {
			$children = HTMLUtility::render(
				[
					'tag'               => 'div',
					'tagEscaped'        => true,
					'attributes'        => [
						'class' => 'et_pb_title_featured_image',
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => $children,
				]
			);
		}

		return $elements->render(
			[
				'tagName'           => 'div',
				'tagEscaped'        => true,
				'attributes'        => [
					'class' => [
						'et_pb_title_featured_container' => true,
						'et_multi_view_hidden'           => [
							'attr'          => $attrs['featuredImage']['advanced']['enabled'] ?? [],
							'valueResolver' => function ( $value ) {
								return 'on' !== ( $value ?? 'on' ) ? 'add' : 'remove';
							},
							'selector'      => '{{selector}} .et_pb_title_featured_container',
						],
					],
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $children,
			]
		);
	}

	/**
	 * Style declaration for the image wrap.
	 *
	 * This function is equivalent of JS function imageWrapStyleDeclaration located in
	 * visual-builder/packages/module-library/src/components/post-title/style-declarations/image-wrap/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $params The parameters of the declaration function.
	 * @param array $attrs The module attributes.
	 *
	 * @return string - The style declarations.
	 */
	public static function image_wrap_style_declaration( $params, $attrs ) {
		$state        = $params['state'];
		$breakpoint   = $params['breakpoint'];
		$declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$force_fullwidth = $attrs['featuredImage']['advanced']['forceFullwidth']['desktop']['value'] ?? 'on';

		if ( 'off' === $force_fullwidth ) {
			$enabled = ModuleUtils::use_attr_value(
				array(
					'attr'         => $attrs['featuredImage']['advanced']['enabled'] ?? [],
					'state'        => $state,
					'breakpoint'   => $breakpoint,
					'defaultValue' => 'on',
				)
			);

			if ( 'on' === $enabled ) {
				$declarations->add( 'width', 'auto' );
			}
		}

		return $declarations->value();
	}

	/**
	 * Style declaration for the title container.
	 *
	 * This function is equivalent of JS function titleContainerStyleDeclaration located in
	 * visual-builder/packages/module-library/src/components/post-title/style-declarations/title-container/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $params The parameters of the declaration function.
	 *
	 * @return string - The style declarations.
	 */
	public static function title_container_style_declaration( array $params ): string {
		$attr_value   = $params['attrValue'] ?? 'off';
		$declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		if ( 'on' === $attr_value ) {
			$declarations->add( 'padding', '1em 1.5em' );
		}

		return $declarations->value();
	}

	/**
	 * Style declaration for the title featured container.
	 *
	 * This function is equivalent of JS function titleFeaturedContainerStyleDeclaration located in
	 * visual-builder/packages/module-library/src/components/post-title/style-declarations/title-featured-container/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $params The parameters of the declaration function.
	 * @param array $attrs The module attributes.
	 *
	 * @return string - The style declarations.
	 */
	public static function title_featured_container_style_declaration( $params, $attrs ) {
		$declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$image_alignments = [
			'left'   => 'auto auto auto 0',
			'center' => 'auto',
			'right'  => 'auto 0 auto auto',
		];

		$alignment       = $params['attrValue']['alignment'] ?? 'center';
		$force_fullwidth = $attrs['featuredImage']['advanced']['forceFullwidth']['desktop']['value'] ?? 'on';
		$placement       = $attrs['featuredImage']['advanced']['placement']['desktop']['value'] ?? 'below';

		if ( 'off' === $force_fullwidth && 'background' !== $placement ) {
			$enabled = ModuleUtils::use_attr_value(
				[
					'attr'         => $attrs['featuredImage']['advanced']['enabled'],
					'state'        => $params['state'],
					'breakpoint'   => $params['breakpoint'],
					'defaultValue' => 'on',
				]
			);

			if ( 'on' === $enabled ) {
				$declarations->add( 'margin', $image_alignments[ $alignment ] );
				$declarations->add( 'text-align', $alignment );
			}
		}

		return $declarations->value();
	}

	/**
	 * Style declaration for post title's border.
	 *
	 * This function is used to generate the style declaration for the border of an post title module.
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
	 * $styleDeclaration = PostTitleModule::overflow_style_declaration( $args );
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
	 * Get information about the featured image of the current post.
	 *
	 * @return array An array containing the following keys: 'src', 'alt', 'title', 'class', and 'id'.
	 *               The 'src' key contains the URL of the featured image, 'alt' contains the alt text,
	 *               'title' contains the title of the image, 'class' contains the CSS class of the image,
	 *               and 'id' contains the ID of the attachment post.
	 */
	public static function get_featured_image(): array {
		$post_id = get_queried_object_id();

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Display the shortcode only on singular pages.
		if ( ! is_singular() && ! is_et_pb_preview() ) {
			$post_id = 0;
		}

		if ( ! $post_id ) {
			return [];
		}

		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $post_thumbnail_id ) {
			return [];
		}

		$featured_image_src   = is_et_theme_builder_template_preview() ? ET_BUILDER_PLACEHOLDER_LANDSCAPE_IMAGE_DATA : wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
		$featured_image_src   = is_array( $featured_image_src ) ? $featured_image_src[0] : '';
		$featured_image_alt   = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );
		$featured_image_title = get_the_title( $post_thumbnail_id );
		$featured_image_class = "wp-image-{$post_thumbnail_id}";

		return [
			'src'   => $featured_image_src,
			'alt'   => $featured_image_alt,
			'title' => $featured_image_title,
			'class' => $featured_image_class,
			'id'    => $post_thumbnail_id,
		];
	}

	/**
	 * Loads `PostTitleModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/post-title/';

		add_filter( 'divi_conversion_presets_attrs_map', array( PostTitlePresetAttrsMap::class, 'get_map' ), 10, 2 );

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
