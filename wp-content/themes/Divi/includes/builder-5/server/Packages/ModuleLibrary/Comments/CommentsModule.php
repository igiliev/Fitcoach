<?php
/**
 * ModuleLibrary: Comments Module class.
 *
 * @package Builder\Packages\ModuleLibrary
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Comments;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\FormField\FormFieldStyle;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use WP_Block_Type_Registry;
use WP_Block;
use WP_Comment;
use WP_Post;

/**
 * `CommentsModule` is consisted of functions used for Comments Module such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class CommentsModule implements DependencyInterface {

	/**
	 * Module custom CSS fields.
	 *
	 * This function is equivalent of JS function cssFields located in
	 * visual-builder/packages/module-library/src/components/comments/custom-css.ts.
	 *
	 * @since ??
	 *
	 * @return array The array of custom CSS fields.
	 */
	public static function custom_css(): array {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'divi/comments' )->customCssFields;
	}

	/**
	 * Set CSS class names to the module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/comments/module-classnames.ts.
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

		$show_avatar = $attrs['image']['advanced']['showAvatar']['desktop']['value'] ?? 'on';
		$show_reply  = $attrs['module']['advanced']['showReply']['desktop']['value'] ?? 'on';
		$show_count  = $attrs['commentCount']['advanced']['showCount']['desktop']['value'] ?? 'on';
		$show_meta   = $attrs['meta']['advanced']['showMeta']['desktop']['value'] ?? 'on';

		if ( 'off' === $show_avatar ) {
			$classnames_instance->add( 'et_pb_no_avatar' );
		}

		if ( 'off' === $show_reply ) {
			$classnames_instance->add( 'et_pb_no_reply_button' );
		}

		if ( 'off' === $show_count ) {
			$classnames_instance->add( 'et_pb_no_comments_count' );
		}

		if ( 'off' === $show_meta ) {
			$classnames_instance->add( 'et_pb_no_comments_meta' );
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
	 * Set script data to the module.
	 *
	 * This function is equivalent of JS function ModuleScriptData located in
	 * visual-builder/packages/module-library/src/components/comments/module-script-data.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string         $id            The module ID.
	 *     @type string         $name          The module name.
	 *     @type string         $selector      The module selector.
	 *     @type array          $attrs         The module attributes.
	 *     @type int            $storeInstance The ID of the instance where this block is stored in the `BlockParserStore` class.
	 *     @type ModuleElements $elements      The `ModuleElements` instance.
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
				'setClassName'  => [
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_no_avatar' => $attrs['image']['advanced']['showAvatar'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'off' === $value ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_no_reply_button' => $attrs['module']['advanced']['showReply'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'off' === $value ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_no_comments_count' => $attrs['commentCount']['advanced']['showCount'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'off' === $value ? 'add' : 'remove';
						},
					],
					[
						'selector'      => $selector,
						'data'          => [
							'et_pb_no_comments_meta' => $attrs['meta']['advanced']['showMeta'] ?? [],
						],
						'valueResolver' => function ( $value ) {
							return 'off' === $value ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * Style declaration for comments' border overflow.
	 *
	 * This function is used to generate the style declaration for the border overflow of a comments module.
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
	 * visual-builder/packages/module-library/src/components/comments/module-styles.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id                       Module unique ID.
	 *     @type string $name                     Module name with namespace.
	 *     @type array  $attrs                    Module attributes.
	 *     @type array  $defaultPrintedStyleAttrs Default printed style attributes.
	 *     @type string $orderClass               Module CSS selector.
	 *     @type array  $settings                 Custom settings.
	 *     @type object $elements                 Instance of ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements class.
	 *     @type mixed  $storeInstance            The ID of instance where this block stored in BlockParserStore.
	 *     @type int    $orderIndex               The order index of the element.
	 *     @type ModuleElements $elements         The ModuleElements instance.
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
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$order_class} p",
													"{$order_class} .comment_postinfo *",
													"{$order_class} .page_title",
													"{$order_class} .comment-reply-title",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
											'propertySelectors' => [
												'textShadow' => [
													'desktop' => [
														'value' => [
															'text-shadow' => implode(
																', ',
																[
																	"{$order_class} p",
																	"{$order_class} .comment_postinfo",
																	"{$order_class} .page_title",
																	"{$order_class} .comment-reply-title",
																]
															),
														],
													],
												],
											],
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
					// Image.
					$elements->style(
						[
							'attrName' => 'image',
						]
					),
					// Form Fields.
					FormFieldStyle::style(
						[
							'selector'   => implode(
								', ',
								[
									"{$args['orderClass']} #commentform textarea",
									"{$args['orderClass']} #commentform input[type='text']",
									"{$args['orderClass']} #commentform input[type='email']",
									"{$args['orderClass']} #commentform input[type='url']",
								]
							),
							'attr'       => $attrs['field'] ?? [],
							'orderClass' => $order_class,
						]
					),
					// Button.
					$elements->style(
						[
							'attrName' => 'button',
						]
					),
					// Comment Count.
					$elements->style(
						[
							'attrName' => 'commentCount',
						]
					),
					// Form Title.
					$elements->style(
						[
							'attrName' => 'formTitle',
						]
					),
					// Meta.
					$elements->style(
						[
							'attrName' => 'meta',
						]
					),
					// Comment text.
					$elements->style(
						[
							'attrName' => 'commentText',
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
	 * This function is equivalent of JS function CommentsEdit located in
	 * visual-builder/packages/module-library/src/components/comments/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                      Block attributes that were saved by VB.
	 * @param string         $content                    Block content.
	 * @param WP_Block       $block                      Parsed block object that being rendered.
	 * @param ModuleElements $elements                   Instance of ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements class.
	 * @param array          $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return string The module HTML output.
	 */
	public static function render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs ) {
		$has_custom_button = 'on' === ( $attrs['button']['decoration']['button']['desktop']['value']['enable'] ?? 'off' );
		$header_level      = $attrs['commentCount']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h1';
		$form_title_level  = $attrs['formTitle']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h3';

		$button_icon     = $has_custom_button ? ( $attrs['button']['decoration']['button']['desktop']['value']['icon']['settings'] ?? [] ) : '';
		$has_button_icon = $has_custom_button && ! empty( $button_icon );

		$button_icon        = $has_button_icon
		? Utils::process_font_icon( $attrs['button']['decoration']['button']['desktop']['value']['icon']['settings'] ?? [] )
		: '';
		$button_icon_tablet = $has_button_icon
		? Utils::process_font_icon( $attrs['button']['decoration']['button']['tablet']['value']['icon']['settings'] ?? [] )
		: '';
		$button_icon_phone  = $has_button_icon
		? Utils::process_font_icon( $attrs['button']['decoration']['button']['phone']['value']['icon']['settings'] ?? [] )
		: '';

		// Action & filter hooks before comment content rendering.
		self::before_comments_content();

		// Comment content rendering.
		$comments_content = self::get_comments( $header_level, $form_title_level );

		// Action & filter hooks after comment content rendering.
		self::after_comments_content();

		return Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'                    => $attrs,
				'id'                       => $block->parsed_block['id'],
				'elements'                 => $elements,
				'htmlAttrs'                => [
					'data-icon'        => esc_attr( $button_icon ),
					'data-icon-tablet' => esc_attr( $button_icon_tablet ),
					'data-icon-phone'  => esc_attr( $button_icon_phone ),
				],
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
					$elements->style_components(
						[
							'attrName' => 'module',
						]
					) . $comments_content,
				],
			]
		);
	}

	/**
	 * Get comments markup for comments module
	 *
	 * @since ?? Add custom form title heading level.
	 *
	 * @param {string} $header_level Header level.
	 * @param {string} $form_title_level Form title heading level.
	 *
	 * @return string of comment section markup
	 */
	public static function get_comments( $header_level, $form_title_level ) {
		global $et_pb_comments_print, $et_comments_header_level, $et_comments_form_title_level;

		// Globally flag that comment module is being printed.
		$et_pb_comments_print = true;

		// set custom header level for comments form.
		$et_comments_header_level     = $header_level;
		$et_comments_form_title_level = $form_title_level;

		// remove filters to make sure comments module rendered correctly if the below filters were applied earlier.
		remove_filter( 'get_comments_number', '__return_zero' );
		remove_filter( 'comments_open', '__return_false' );
		remove_filter( 'comments_array', '__return_empty_array' );

		// Custom action before calling comments_template.
		do_action( 'et_fb_before_comments_template' );

		ob_start();
		comments_template( '', true );
		$comments_content = ob_get_clean();

		// Custom action after calling comments_template.
		do_action( 'et_fb_after_comments_template' );

		// Globally flag that comment module has been printed.
		$et_pb_comments_print     = false;
		$et_comments_header_level = '';

		return $comments_content;
	}

	/**
	 * Action and filter hooks that are called before comment content rendering. These are
	 * abstracted into method so module which extends comment module can modify these
	 *
	 * @since ??
	 */
	public static function before_comments_content() {
		// Modify the comments request to make sure it's unique.
		// Otherwise WP generates SQL error and doesn't allow multiple comments sections on single page.
		add_action( 'pre_get_comments', [ __CLASS__, 'et_pb_modify_comments_request' ], 1 );

		// include custom comments_template to display the comment section with Divi style.
		add_filter( 'comments_template', [ __CLASS__, 'et_pb_comments_template' ] );

		// Modify submit button to be advanced button style ready.
		add_filter( 'comment_form_submit_button', [ __CLASS__, 'et_pb_comments_submit_button' ] );
	}

	/**
	 * Action and filter hooks that are called after comment content rendering. These are
	 * abstracted into method so module which extends comment module can modify these
	 *
	 * @since ??
	 */
	public static function after_comments_content() {
		// remove all the actions and filters to not break the default comments section from theme.
		remove_filter( 'comments_template', [ __CLASS__, 'et_pb_comments_template' ] );
		remove_action( 'pre_get_comments', [ __CLASS__, 'et_pb_modify_comments_request' ], 1 );
	}

	/**
	 * Provides the path to the custom comments template.
	 *
	 * @since ??
	 *
	 * @return string The path to the comments template.
	 */
	public static function et_pb_comments_template() {
		return dirname( __DIR__ ) . '/templates/comments_template.php';
	}

	/**
	 * Renders the submit button for comments form.
	 *
	 * @param string $submit_button The submit button HTML.
	 *
	 * @since ??
	 *
	 * @return string The rendered submit button HTML.
	 */
	public static function et_pb_comments_submit_button( $submit_button ) {
		return sprintf(
			'<button name="submit" type="submit" id="et_pb_submit" class="submit">%1$s</button>',
			esc_html__( 'Submit Comment', 'et_builder' )
		);
	}

	/**
	 * Modifies the comments request parameters to make the request with unique parameters
	 *
	 * @param object $params The object that contains the request parameters.
	 *
	 * @since ??
	 */
	public static function et_pb_modify_comments_request( $params ) {
		// modify the request parameters the way it doesn't change the result just to make request with unique parameters.
		$params->query_vars['type__not_in'] = 'et_pb_comments_random_type_et_pb_comments';
	}

	/**
	 * Loads `CommentsModule` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/comments/';

		add_filter( 'divi_conversion_presets_attrs_map', [ CommentsPresetAttrsMap::class, 'get_map' ], 10, 2 );
		add_filter( 'the_content', [ self::class, 'content_main_query' ], 1500 );
		add_filter( 'et_builder_render_layout', [ self::class, 'content_main_query' ], 1500 );

		// Ensure that all filters and actions applied during module registration are registered before calling `ModuleRegistration::register_module()`.
		// However, for consistency, register all module-specific filters and actions prior to invoking `ModuleRegistration::register_module()`.
		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);
	}

	/**
	 * Add pseudo-action via the_content to hook filter/action at the end of main content
	 *
	 * @since ??
	 *
	 * @param string $content The content string.
	 * @return string The modified content string.
	 */
	public static function content_main_query( $content ) {
		global $et_pb_comments_print;

		// Perform filter on main query + if builder is used only.
		if ( is_main_query() && et_pb_is_pagebuilder_used( get_the_ID() ) ) {
			add_filter( 'comment_class', [ self::class, 'add_non_builder_comment_class' ], 10, 5 );

			// Actual front-end only adjustment.
			if ( false === $et_pb_comments_print && ! Conditions::is_vb_enabled() && has_block( 'divi/comments' ) ) {
				add_filter( 'get_comments_number', '__return_zero' );
				add_filter( 'comments_open', '__return_false' );
				add_filter( 'comments_array', '__return_empty_array' );
			}
		}

		return $content;
	}

	/**
	 * Added special class name for comment items that are placed outside builder
	 *
	 * @since ??
	 *
	 * @param  array       $classes    An array of comment classes.
	 * @param  array       $css_class  An array of additional classes added to the list.
	 * @param  string      $comment_id The comment ID as a numeric string.
	 * @param  WP_Comment  $comment    The comment object.
	 * @param  int|WP_Post $post       The post ID or WP_Post object.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/comment_class/
	 *
	 * @return array modified classname
	 */
	public static function add_non_builder_comment_class( $classes, $css_class, $comment_id, $comment, $post ) {
		if ( ! in_array( 'et-pb-non-builder-comment', $classes, true ) ) {
			$classes[] = 'et-pb-non-builder-comment';
		}

		return $classes;
	}
}
