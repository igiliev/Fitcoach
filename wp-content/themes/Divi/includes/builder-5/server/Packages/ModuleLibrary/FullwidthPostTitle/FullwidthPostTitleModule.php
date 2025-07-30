<?php
/**
 * ModuleLibrary: FullwidthPostTitle Module class.
 *
 * @package Builder\Packages\ModuleLibrary\FullwidthPostTitleModule
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FullwidthPostTitle;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleLibrary\PostTitle\PostTitleModule;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * `FullwidthPostTitleModule` is consisted of functions used for FullwidthPostTitle Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class FullwidthPostTitleModule implements DependencyInterface {

	/**
	 * Filters the module.decoration attributes.
	 *
	 * This function is equivalent of JS function filterModuleDecorationAttrs located in
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/attrs-filter/filter-module-decoration-attrs/index.ts.
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
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/attrs-filter/filter-featured-image-decoration-attrs/index.ts.
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
						'attr'       => $attrs['featuredImage']['advanced']['enabled'],
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
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/attrs-filter/filter-text-wrapper-decoration-attrs/index.ts.
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
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/fullwidth-post-title' )->customCssFields;
	}

	/**
	 * Module classnames function for post type module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/module-classnames.ts.
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

		$classnames_instance->add( 'et_pb_image_above', 'above' === $featured_image_placement );
		$classnames_instance->add( 'et_pb_image_below', 'below' === $featured_image_placement );

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
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/module-script-data.tsx.
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

		$post_featured_image     = PostTitleModule::get_featured_image();
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
	 * Style declaration for fullwidth post title's border.
	 *
	 * This function is used to generate the style declaration for the border of a fullwidth post title module.
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
	 * PostTitle Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/module-styles.tsx.
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
	 *      @type ModuleElements $elements          ModuleElements instance.
	 * }
	 */
	public static function module_styles( $args ) {
		$order_class                 = $args['orderClass'] ?? '';
		$attrs                       = $args['attrs'] ?? [];
		$elements                    = $args['elements'];
		$settings                    = $args['settings'] ?? [];
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];
		$post_featured_image         = PostTitleModule::get_featured_image();
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
								'advancedStyles'           => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
								],
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs, $post_featured_image_src ) {
									return FullwidthPostTitleModule::filter_module_decoration_attrs( $decoration_attrs, $attrs, $post_featured_image_src );
								},
							],
						]
					),
					TextStyle::style(
						[
							'selector'   => implode(
								', ',
								[
									"{$args['orderClass']} .entry-title",
									"{$args['orderClass']} .et_pb_title_meta_container",
								]
							),
							'attr'       => $attrs['module']['advanced']['text'] ?? [],
							'orderClass' => $order_class,
						]
					),
					// Text Wrapper.
					$elements->style(
						[
							'attrName'   => 'textWrapper',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['textWrapper']['decoration'] ?? [],
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs ) {
									return FullwidthPostTitleModule::filter_text_wrapper_decoration_attrs( $decoration_attrs, $attrs );
								},
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
					// Featured Image.
					$elements->style(
						[
							'attrName'   => 'featuredImage',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['featuredImage']['decoration'] ?? [],
								'attrsFilter'              => function ( $decoration_attrs ) use ( $attrs ) {
									return FullwidthPostTitleModule::filter_featured_image_decoration_attrs( $decoration_attrs, $attrs );
								},
							],
						]
					),
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .et_pb_title_container',
							'attr'                => $attrs['textWrapper']['advanced']['useBackground'] ?? [],
							'declarationFunction' => [ self::class, 'title_container_style_declaration' ],
							'orderClass'          => $order_class,
						]
					),
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .et_pb_title_featured_image',
							'attr'                => $attrs['featuredImage']['decoration']['sizing'] ?? [],
							'declarationFunction' => function ( array $params ) use ( $attrs ) {
								return FullwidthPostTitleModule::title_featured_container_style_declaration( $params, $attrs );
							},
							'orderClass'          => $order_class,
						]
					),
					CommonStyle::style(
						[
							'selector'            => $order_class . ' .et_pb_image_wrap',
							'attr'                => $attrs['featuredImage']['decoration']['sizing'] ?? [],
							'declarationFunction' => function ( array $params ) use ( $attrs ) {
								return FullwidthPostTitleModule::image_wrap_style_declaration( $params, $attrs );
							},
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
	 * FullwidthPostTitle module render callback which outputs server side rendered HTML on the Front-End.
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
	 * @return string HTML rendered of FullwidthPostTitle module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$post_featured_image     = PostTitleModule::get_featured_image();
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
					PostTitleModule::render_featured_image( $attrs, 'above', $elements, true ),
					HTMLUtility::render(
						[
							'tag'               => 'div',
							'tagEscaped'        => true,
							'attributes'        => [
								'class' => 'et_pb_title_container',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => [
								PostTitleModule::render_title( $attrs, $elements ),
								PostTitleModule::render_meta( $attrs, $elements ),
							],
						]
					),
					PostTitleModule::render_featured_image( $attrs, 'below', $elements, true ),
				],
			]
		);
	}

	/**
	 * Style declaration for the image wrap.
	 *
	 * This function is equivalent of JS function imageWrapStyleDeclaration located in
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/style-declarations/image-wrap/index.ts.
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
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/style-declarations/title-container/index.ts.
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
	 * visual-builder/packages/module-library/src/components/fullwidth-post-title/style-declarations/title-featured-container/index.ts.
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
	 * Loads `FullwidthPostTitleModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/fullwidth-post-title/';

		add_filter( 'divi_conversion_presets_attrs_map', array( FullwidthPostTitlePresetAttrsMap::class, 'get_map' ), 10, 2 );

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
