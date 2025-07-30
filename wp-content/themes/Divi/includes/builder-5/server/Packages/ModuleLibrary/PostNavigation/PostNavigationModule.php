<?php
/**
 * ModuleLibrary: Post Navigation Module class.
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\PostNavigation;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

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
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use stdClass;
use WP_Block_Type_Registry;
use WP_Block;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * `PostNavigationModule` is consisted of functions used for Post Navigation Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class PostNavigationModule implements DependencyInterface {

	/**
	 * Module custom CSS fields.
	 *
	 * This function is equivalent of JS function cssFields located in
	 * visual-builder/packages/module-library/src/components/post-nav/custom-css.ts.
	 *
	 * @since ??
	 *
	 * @return array The array of custom CSS fields.
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/post-nav' )->customCssFields;
	}

	/**
	 * Set CSS class names to the module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/post-nav/module-classnames.ts.
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

		// Module Classname.
		$classnames_instance->add( 'nav-single' );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => $attrs['module']['decoration'] ?? [],
				]
			)
		);
	}

	/**
	 * Set script data of used module options.
	 *
	 * This function is equivalent of JS function ModuleScriptData located in
	 * visual-builder/packages/module-library/src/components/post-navigation/module-script-data.tsx.
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
						'selector'      => $selector . ' .et_pb_posts_nav',
						'data'          => $attrs['title']['innerContent'] ?? [],
						'valueResolver' => function ( $value ) {
							return $value ?? '';
						},
						'sanitizer'     => 'et_core_esc_previously',
					],
				],
			]
		);
	}

	/**
	 * Overflow style declaration.
	 *
	 * This function is responsible for declaring the overflow style for the PostNavigation module.
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
	 * PostNavigationModule::overflow_style_declaration($params);
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
	 * visual-builder/packages/module-library/src/components/post-nav/module-styles.tsx.
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
											'attr' => $attrs['module']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),

					// Links.
					$elements->style(
						[
							'attrName' => 'links',
						]
					),
					CommonStyle::style(
						[
							'selector'            => ".et_pb_posts_nav{$order_class} span.nav-previous a, .et_pb_posts_nav{$order_class} span.nav-next a",
							'attr'                => $attrs['links']['decoration']['border'] ?? [],
							'declarationFunction' => [ self::class, 'overflow_style_declaration' ],
							'orderClass'          => $order_class,
						]
					),

					CssStyle::style(
						[
							'selector'   => $order_class,
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
	 * Get current post ID.
	 *
	 * @since ??
	 *
	 * @return int The current post ID.
	 */
	protected static function _get_current_post_id() {
		$post_id = get_queried_object_id();

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		return $post_id;
	}

	/**
	 * Get the Post Navigation data.
	 *
	 * @since ??
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return array The array of Post Navigation data.
	 */
	public static function get_post_navigation( array $args = [] ) {
		global $post;

		$defaults = [
			'post_id'       => self::_get_current_post_id(),
			'in_same_term'  => 'off',
			'taxonomy_name' => 'category',
			'prev_text'     => '%title',
			'next_text'     => '%title',
		];

		$args = wp_parse_args( $args, $defaults );

		// taxonomy name overwrite if in_same_term option is set to off and no taxonomy name defined.
		if ( '' === $args['taxonomy_name'] || 'off' === $args['in_same_term'] ) {
			$args['taxonomy_name'] = is_singular( 'project' ) ? 'project_category' : 'category';
		}

		$in_same_term = ! ( ! $args['in_same_term'] || 'off' === $args['in_same_term'] );

		et_core_nonce_verified_previously();
		if ( $args['post_id'] ) {
			$post_id = $args['post_id'];
		} elseif ( is_object( $post ) && isset( $post->ID ) ) {
			$post_id = $post->ID;
		} elseif ( is_singular() ) {
			// If it's a single post or page.
			$post_id = self::_get_current_post_id();
		} else {
			return [
				'posts_navigation' => [
					'next' => '',
					'prev' => '',
				],
			];
		}

		// Set current post as global $post.
		$post = get_post( $post_id ); // phpcs:ignore WordPress.Variables.GlobalVariables.OverrideProhibited -- Override global $post.

		// Get next post.
		$next_post = get_next_post( $in_same_term, '', $args['taxonomy_name'] );

		$next = new stdClass();

		if ( ! empty( $next_post ) ) {

			$next_title = isset( $next_post->post_title ) ? esc_html( $next_post->post_title ) : esc_html__( 'Next Post' );

			$next_date      = mysql2date( get_option( 'date_format' ), $next_post->post_date );
			$next_permalink = isset( $next_post->ID ) ? esc_url( get_the_permalink( $next_post->ID ) ) : '';

			$next_processed_title = '' === $args['next_text'] ? '%title' : $args['next_text'];

			// Process WordPress' wildcards.
			$next_processed_title = str_replace(
				[ '%title', '%date', '%link' ],
				[
					$next_title,
					$next_date,
					$next_permalink,
				],
				$next_processed_title
			);

			$next->title     = $next_processed_title;
			$next->id        = isset( $next_post->ID ) ? (int) $next_post->ID : '';
			$next->permalink = $next_permalink;
		}

		// Get prev post.
		$prev_post = get_previous_post( $in_same_term, '', $args['taxonomy_name'] );

		$prev = new stdClass();

		if ( ! empty( $prev_post ) ) {

			$prev_title = isset( $prev_post->post_title ) ? esc_html( $prev_post->post_title ) : esc_html__( 'Previous Post' );

			$prev_date = mysql2date( get_option( 'date_format' ), $prev_post->post_date );

			$prev_permalink = isset( $prev_post->ID ) ? esc_url( get_the_permalink( $prev_post->ID ) ) : '';

			$prev_processed_title = '' === $args['prev_text'] ? '%title' : $args['prev_text'];

			// Process WordPress' wildcards.
			$prev_processed_title = str_replace(
				[ '%title', '%date', '%link' ],
				[
					$prev_title,
					$prev_date,
					$prev_permalink,
				],
				$prev_processed_title
			);

			$prev->title     = $prev_processed_title;
			$prev->id        = isset( $prev_post->ID ) ? (int) $prev_post->ID : '';
			$prev->permalink = $prev_permalink;
		}

		// Formatting returned value.
		return [
			'next' => $next,
			'prev' => $prev,
		];
	}

	/**
	 * Module render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function PostNavEdit located in
	 * visual-builder/packages/module-library/src/components/post-nav/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs    Block attributes that were saved by VB.
	 * @param string         $content  Block content.
	 * @param WP_Block       $block    Parsed block object that being rendered.
	 * @param ModuleElements $elements Instance of ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements class.
	 * @param array          $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return string The module HTML output.
	 */
	public static function render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs ) {
		$show_prev = ModuleUtils::has_value(
			$attrs['links']['advanced']['showPrev'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'off' !== $value;
				},
			]
		);
		$show_next = ModuleUtils::has_value(
			$attrs['links']['advanced']['showNext'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'off' !== $value;
				},
			]
		);

		if ( ! $show_prev && ! $show_next ) {
			return '';
		}

		$in_same_term = ModuleUtils::has_value(
			$attrs['module']['advanced']['inSameTerm'] ?? [],
			[
				'valueResolver' => function ( $value ) {
					return 'off' !== $value;
				},
			]
		);

		$taxonomy_name = $attrs['module']['advanced']['taxonomyName']['desktop']['value'] ?? 'category';
		$prev_text     = $attrs['links']['advanced']['prevText']['desktop']['value'] ?? '';
		$next_text     = $attrs['links']['advanced']['nextText']['desktop']['value'] ?? '';

		$args = [
			'post_id'       => self::_get_current_post_id(),
			'in_same_term'  => $in_same_term,
			'taxonomy_name' => $taxonomy_name,
			'prev_text'     => $prev_text,
			'next_text'     => $next_text,
		];

		$posts_navigation = self::get_post_navigation( $args );

		$style_components = $elements->style_components(
			[
				'attrName' => 'module',
			]
		);

		$left_arrow = HTMLUtility::render(
			[
				'tag'        => 'span',
				'attributes' => [
					'class' => 'meta-nav',
				],
				'children'   => '&larr; ',
			]
		);

		$prev_nav_inner = HTMLUtility::render(
			[
				'tag'               => 'a',
				'attributes'        => [
					'href' => esc_url( $posts_navigation['prev']->permalink ?? '' ),
					'rel'  => 'prev',
				],
				'children'          => [
					$style_components,
					$left_arrow,
					HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class' => 'nav-label',
							],
							'children'   => esc_html( $posts_navigation['prev']->title ?? '' ),
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);

		$right_arrow = HTMLUtility::render(
			[
				'tag'        => 'span',
				'attributes' => [
					'class' => 'meta-nav',
				],
				'children'   => ' &rarr;',
			]
		);

		$next_nav_inner = HTMLUtility::render(
			[
				'tag'               => 'a',
				'attributes'        => [
					'href' => esc_url( $posts_navigation['next']->permalink ?? '' ),
					'rel'  => 'next',
				],
				'children'          => [
					$style_components,
					HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class' => 'nav-label',
							],
							'children'   => esc_html( $posts_navigation['next']->title ?? '' ),
						]
					),
					$right_arrow,
				],
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);

		$prev_nav_html = $show_prev && ! empty( $posts_navigation['prev']->permalink ) ? HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => 'nav-previous',
				],
				'children'          => $prev_nav_inner,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : null;

		$next_nav_html = $show_next && ! empty( $posts_navigation['next']->permalink ) ? HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => 'nav-next',
				],
				'children'          => $next_nav_inner,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		) : null;

		return Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'                    => $attrs,
				'id'                       => $block->parsed_block['id'],
				'elements'                 => $elements,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'name'                     => $block->block_type->name,
				'moduleCategory'           => $block->block_type->category,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'parentAttrs'              => [],
				'parentId'                 => '',
				'parentName'               => '',
				'children'                 => [
					$prev_nav_html,
					$next_nav_html,
				],
			]
		);
	}

	/**
	 * Loads `PostNavigationModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/post-nav/';

		add_filter( 'divi_conversion_presets_attrs_map', array( PostNavigationPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
