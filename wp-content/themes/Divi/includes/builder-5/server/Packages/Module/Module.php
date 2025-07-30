<?php
/**
 * Module: Module class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\Assets\StaticCSS;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\Packages\GlobalData\GlobalPresetItem;
use ET\Builder\Packages\Module\Layout\Components\Classnames;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\NoResultsRenderer\NoResultsRenderer;
use ET\Builder\Packages\Module\Layout\Components\Wrapper\ModuleWrapper;
use ET\Builder\Packages\Module\Options\IdClasses\IdClassesClassnames;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use WP_Block_Type_Registry;

/**
 * Module class.
 *
 * @since ??
 */
class Module {

	/**
	 * Module renderer.
	 *
	 * This function is used to render a module in FE.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/Module Module}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $attrs                     Optional. Module attributes data. Default `[]`.
	 *     @type array    $htmlAttrs                 Optional. Custom HTML attributes. Default `null`.
	 *     @type string   $id                        Optional. Module ID. Default empty string.
	 *                                               In Visual Builder, the ID of module is a UUIDV4 string.
	 *                                               In FrontEnd, it is module name + order index.
	 *     @type string   $children                  Optional. The children element(s). Default empty string.
	 *     @type string   $childrenIds               Optional. Module inner blocks. Default `[]`.
	 *     @type bool     $hasModule                 Optional. Whether the module has module or not. Default `true`.
	 *     @type string   $moduleCategory            Optional. Module category. Default empty string.
	 *     @type string   $classname                 Optional. Custom CSS class attribute. Default empty string.
	 *     @type bool     $isFirst                   Optional. Is first child flag. Default `false`.
	 *     @type bool     $isLast                    Optional. Is last child flag. Default `false`.
	 *     @type bool     $hasModuleClassName        Optional. Has module class name. Default `true`.
	 *     @type callable $classnamesFunction        Optional. Function that will be invoked to generate module CSS class. Default `null`.
	 *     @type array    $styles                    Optional. Custom inline style attribute. Default `[]`.
	 *     @type string   $tag                       Optional. HTML tag. Default `div`.
	 *     @type bool     $hasModuleWrapper          Optional. Has module wrapper flag. Default `false`.
	 *     @type string   $wrapperTag                Optional. Wrapper HTML tag. Default `div`.
	 *     @type array    $wrapperHtmlAttrs          Optional. Wrapper custom html attributes. Default `[]`.
	 *     @type string   $wrapperClassname          Optional. Wrapper custom CSS class. Default empty string.
	 *     @type callable $wrapperClassnamesFunction Optional. Function that will be invoked to generate module wrapper CSS class. Default `null`.
	 *     @type callable $stylesComponent           Optional. Function that will be invoked to generate module styles. Default `null`.
	 *     @type array    $parentAttrs               Optional. Parent module attributes data. Default `[]`.
	 *     @type string   $parentId                  Optional. Parent Module ID. Default empty string.
	 *                                               In Visual Builder, the ID of module is a UUIDV4 string.
	 *                                               In FrontEnd, it is parent module name + parent order index.
	 *     @type string   $parentName                Optional. Parent module name. Default empty string.
	 *     @type array    $siblingAttrs              Optional. Module sibling attributes data. Default `[]`.
	 *     @type array    $settings                  Optional. Custom settings. Default `[]`.
	 *     @type int      $orderIndex                Optional. Module order index. Default `0`.
	 *     @type int      $storeInstance             Optional. The ID of instance where this block stored in BlockParserStore class. Default `null`.
	 * }
	 *
	 * @return string The module HTML.
	 *
	 * @example:
	 * ```php
	 *  ET_Builder_Module::render( array(
	 *    'arg1' => 'value1',
	 *    'arg2' => 'value2',
	 *  ) );
	 * ```
	 *
	 * @example:
	 * ```php
	 *  $module = new ET_Builder_Module();
	 *  $module->render( array(
	 *    'arg1' => 'value1',
	 *    'arg2' => 'value2',
	 *   ) );
	 * ```
	 */
	public static function render( array $args ): string {
		$name          = $args['name'];
		$module_config = WP_Block_Type_Registry::get_instance()->get_registered( $name );

		$args = array_replace_recursive(
			[
				'attrs'                     => [],
				'elements'                  => null,
				'htmlAttrs'                 => [],
				'htmlAttributesFunction'    => null,
				'id'                        => '',
				'children'                  => '',
				'childrenIds'               => [],
				'defaultPrintedStyleAttrs'  => [],
				'hasModule'                 => true,
				'moduleCategory'            => '',
				'className'                 => '',
				'isFirst'                   => false,
				'isLast'                    => false,
				'hasModuleClassName'        => true,
				'classnamesFunction'        => null,
				'styles'                    => [],
				'tag'                       => $module_config->wrapper['tag'] ?? 'div',
				'hasModuleWrapper'          => $module_config->wrapper['status'] ?? false,
				'wrapperTag'                => 'div',
				'wrapperHtmlAttrs'          => [],
				'wrapperClassname'          => '',
				'wrapperClassnamesFunction' => null,
				'stylesComponent'           => null,
				'scriptDataComponent'       => null,
				'parentAttrs'               => [],
				'parentId'                  => '',
				'parentName'                => '',
				'siblingAttrs'              => [],
				'settings'                  => [],

				// FE only.
				'orderIndex'                => 0,
				'storeInstance'             => null,
			],
			$args
		);

		$attrs                       = $args['attrs'];
		$elements                    = $args['elements'];
		$html_attrs                  = $args['htmlAttrs'];
		$html_attributes_function    = $args['htmlAttributesFunction'];
		$id                          = $args['id'];
		$children                    = $args['children'];
		$children_ids                = $args['childrenIds'];
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'];
		$has_module                  = $args['hasModule'];
		$module_category             = $args['moduleCategory'];
		$class_name                  = $args['className'];
		$is_first                    = $args['isFirst'];
		$is_last                     = $args['isLast'];
		$has_module_class_name       = $args['hasModuleClassName'];
		$classnames_function         = $args['classnamesFunction'];
		$styles                      = $args['styles'];
		$tag                         = $args['tag'];
		$has_module_wrapper          = $args['hasModuleWrapper'];
		$wrapper_tag                 = $args['wrapperTag'];
		$wrapper_html_attrs          = $args['wrapperHtmlAttrs'];
		$wrapper_classname           = $args['wrapperClassname'];
		$wrapper_classnames_function = $args['wrapperClassnamesFunction'];
		$styles_component            = $args['stylesComponent'];
		$script_data_component       = $args['scriptDataComponent'];
		$parent_attrs                = $args['parentAttrs'];
		$parent_id                   = $args['parentId'];
		$parent_name                 = $args['parentName'];
		$sibling_attrs               = $args['siblingAttrs'];
		$settings                    = $args['settings'];
		$order_index                 = $args['orderIndex'];
		$store_instance              = $args['storeInstance'];

		// Early return for loop no-results case.
		$is_no_results = isset( $attrs['__loop_no_results'] ) && true === $attrs['__loop_no_results'];
		if ( $is_no_results ) {
			// Generate basic module classnames for no-results rendering.
			$module_class_by_name = ModuleUtils::get_module_class_by_name( $name );
			$module_class_name    = ModuleUtils::get_module_class_name( $name );
			$selector_classname   = ModuleUtils::get_module_order_class_name( $id, $store_instance );

			if ( ! $module_class_name ) {
				$module_class_name = $module_class_by_name;
			}

			if ( ! $selector_classname ) {
				$selector_classname = $module_class_by_name . '_' . $order_index;
			}

			// Generate module styles for no-results case.
			$module_styles = '';
			if ( is_callable( $styles_component ) && $elements instanceof ModuleElements ) {
				// Set up minimal context for style generation.
				$elements->set_order_id( $order_index );

				// Generate styles using the module's style component.
				$module_styles = $elements->style_components(
					[
						'attrName' => 'module',
					]
				);
			}

			// Merge additional classes if provided.
			$additional_classes = [];
			if ( ! empty( $class_name ) ) {
				$additional_classes[] = $class_name;
			}

			$excluded_categories = [
				'structure',
				'child-module',
			];

			if ( ! in_array( $module_category, $excluded_categories, true ) && $has_module_class_name ) {
				$additional_classes[] = 'et_pb_module';
			}

			return NoResultsRenderer::render(
				[
					'moduleClassName'   => $module_class_name,
					'moduleOrderClass'  => $selector_classname,
					'additionalClasses' => implode( ' ', $additional_classes ),
					'htmlAttrs'         => $html_attrs,
					'tag'               => $tag,
					'moduleStyles'      => $module_styles,
				]
			);
		}

		$settings = array_merge(
			[
				'disabledModuleVisibility' => 'hidden', // TODO feat(D5, Frontend Rendering): Set this value dynamically taken from from the builder settings.
			],
			$settings
		);

		// Base classnames params.
		// Both module and wrapper classnames filters need this. Module and wrapper classnames
		// action hooks need this + `classnamesInstance` property.
		$base_classnames_params = [
			'attrs'         => $attrs,
			'childrenIds'   => $children_ids,
			'hasModule'     => $has_module,
			'id'            => $id,
			'isFirst'       => $is_first,
			'isLast'        => $is_last,
			'name'          => $name,
			'parentAttrs'   => $parent_attrs,

			// FE only.
			'storeInstance' => $store_instance,
			'orderIndex'    => $order_index,
			'layoutType'    => BlockParserStore::get_layout_type(),
		];

		/*
		 * In Visual Builder (VB), 'hasModule' correctly indicates whether a Row contains any modules.
		 * However, on the Front-end (FE), 'hasModule' may return TRUE even if the Row only contains empty columns (i.e., no actual modules),
		 * due to differences in how the block structure is parsed. This can lead to incorrect classnames being applied in FE.
		 * To address this, we pass the 'hasModuleInColumns' property, which is computed specifically for FE to reflect whether any columns
		 * actually contain modules. This ensures that module_classnames implementations can apply the correct logic and classnames
		 * (such as 'et-vb-row--no-module') consistently between VB and FE.
		 */
		// Add this after $base_classnames_params is defined.
		if ( isset( $args['hasModuleInColumns'] ) ) {
			$base_classnames_params['hasModuleInColumns'] = $args['hasModuleInColumns'];
		}

		// Module wrapper classnames.
		$wrapper_classnames_instance = new Classnames();
		$wrapper_classnames_params   = array_merge(
			$base_classnames_params,
			[ 'classnamesInstance' => $wrapper_classnames_instance ]
		);

		$wrapper_classnames_instance->add( $wrapper_classname, ! empty( $wrapper_classname ) );

		if ( is_callable( $wrapper_classnames_function ) ) {
			call_user_func( $wrapper_classnames_function, $wrapper_classnames_params );
		}

		// Module classnames.
		$classnames_instance = new Classnames();
		$classnames_params   = array_merge(
			$base_classnames_params,
			[ 'classnamesInstance' => $classnames_instance ]
		);

		$module_class_by_name = ModuleUtils::get_module_class_by_name( $name );

		$module_class_name = ModuleUtils::get_module_class_name( $name );

		if ( ! $module_class_name ) {
			$module_class_name = $module_class_by_name;
		}

		$selector_classname = ModuleUtils::get_module_order_class_name( $id, $store_instance );

		if ( ! $selector_classname ) {
			$selector_classname = $module_class_by_name . '_' . $order_index;
		}

		$classnames_instance->add( $selector_classname );
		$classnames_instance->add( $module_class_by_name, empty( $module_class_name ) );
		$classnames_instance->add( $module_class_name, ! empty( $module_class_name ) );

		if ( is_callable( $classnames_function ) ) {
			call_user_func( $classnames_function, $classnames_params );
		}

		$classnames_instance->add( $class_name, ! empty( $class_name ) );

		$excluded_categories = [
			'structure',
			'child-module',
		];

		$classnames_instance->add(
			'et_pb_module',
			! in_array( $module_category, $excluded_categories, true ) && $has_module_class_name
		);

		// Module styles output.
		$parent_order_class = $parent_id ? '.' . ModuleUtils::get_module_order_class_name( $parent_id, $store_instance ) : '';

		if ( $parent_id && ! $parent_order_class ) {
			$parent_order_class = '.' . ModuleUtils::get_module_class_by_name( $parent_id );
		}

		// Whether $elements is an instance of ModuleElements.
		$is_module_elements_instance = $elements instanceof ModuleElements;

		if ( $is_module_elements_instance ) {
			$elements->set_order_id( $order_index );
		}

		// Fetch module htmlAttributes.
		if ( is_callable( $html_attributes_function ) ) {
			$id_class_values = call_user_func(
				$html_attributes_function,
				[
					'id'    => $id,
					'name'  => $name,
					'attrs' => $attrs,
				]
			);
		} else {
			$id_class_values = IdClassesClassnames::get_html_attributes(
				$attrs['module']['advanced']['htmlAttributes'] ?? []
			);
		}

		$html_id         = $id_class_values['id'] ?? '';
		$html_classnames = $id_class_values['classNames'] ?? '';

		// Module CSS Id.
		if ( ! empty( $html_id ) ) {
			$html_attrs['id'] = $html_id;
		}

		// Module CSS Class.
		$classnames_instance->add(
			$html_classnames,
			! empty( $html_classnames )
		);

		// Condition where current page builder's style has been enqueued as static css.
		$is_style_enqueued_as_static_css = StaticCSS::$styles_manager->enqueued ?? false;

		if ( is_callable( $styles_component ) ) {
			// Conditions.
			$is_custom_post_type = Conditions::is_custom_post_type();

			// Selector prefix.
			$selector_prefix = $is_custom_post_type ? '.et-db #et-boc .et-l ' : '';

			// Reder Preset Styles.
			self::render_styles_preset_module(
				[
					'name'                       => $name,
					'attrs'                      => $attrs,
					'defaultPrintedStyleAttrs'   => $default_printed_style_attrs,
					'parentId'                   => $parent_id,
					'parentName'                 => $parent_name,
					'id'                         => $id,
					'storeInstance'              => $store_instance,
					'elements'                   => $elements,
					'classnamesInstance'         => $classnames_instance,
					'wrapperClassnamesInstance'  => $wrapper_classnames_instance,
					'selectorPrefix'             => $selector_prefix,
					'hasModuleWrapper'           => $has_module_wrapper,
					'isStyleEnqueuedAsStaticCss' => $is_style_enqueued_as_static_css,
					'stylesComponent'            => $styles_component,
					'settings'                   => $settings,
					'orderIndex'                 => $order_index,
					'siblingAttrs'               => $sibling_attrs,
				]
			);

			// Reder Group Preset Styles.
			self::render_styles_preset_group(
				[
					'parentId'                   => $parent_id,
					'parentName'                 => $parent_name,
					'defaultPrintedStyleAttrs'   => $default_printed_style_attrs,
					'name'                       => $name,
					'elements'                   => $elements,
					'classnamesInstance'         => $classnames_instance,
					'wrapperClassnamesInstance'  => $wrapper_classnames_instance,
					'id'                         => $id,
					'storeInstance'              => $store_instance,
					'selectorPrefix'             => $selector_prefix,
					'hasModuleWrapper'           => $has_module_wrapper,
					'isStyleEnqueuedAsStaticCss' => $is_style_enqueued_as_static_css,
					'stylesComponent'            => $styles_component,
					'settings'                   => $settings,
					'orderIndex'                 => $order_index,
					'attrs'                      => $attrs,
					'siblingAttrs'               => $sibling_attrs,
				]
			);

			// Render Module Styles.
			Style::set_group_style( 'module' );

			if ( $is_module_elements_instance ) {
				$elements->set_style_group( 'module' );
			}

			// Process Module Style output only when module selector is available.
			if ( $selector_classname ) {
				// Order class names.
				$base_order_class = '.' . $selector_classname;
				$order_class      = $selector_prefix . $base_order_class;

				// Wrapper order class names.
				$base_wrapper_order_class = $has_module_wrapper ? '.' . $selector_classname . '_wrapper' : '';
				$wrapper_order_class      = $has_module_wrapper ? $selector_prefix . $base_wrapper_order_class : '';
				$is_inside_sticky_module  = $elements->get_is_inside_sticky_module();
				$is_parent_layout_flex    = $elements->get_is_parent_layout_flex();

				if ( $is_module_elements_instance ) {
					$elements->set_base_order_class( $base_order_class );
					$elements->set_order_class( $order_class );
					$elements->set_base_wrapper_order_class( $base_wrapper_order_class );
					$elements->set_wrapper_order_class( $wrapper_order_class );
					$elements->set_module_name_class( $module_class_name );
				}

				if ( ! $is_style_enqueued_as_static_css ) {
					// Set styles for module.
					call_user_func(
						$styles_component,
						[
							'id'                       => $id,
							'isCustomPostType'         => $is_custom_post_type,
							'elements'                 => $elements,
							'name'                     => $name,
							'attrs'                    => $attrs,
							'parentAttrs'              => $parent_attrs,
							'siblingAttrs'             => $sibling_attrs,
							'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
							'isInsideStickyModule'     => $is_inside_sticky_module,
							'isParentLayoutFlex'       => $is_parent_layout_flex,
							'baseOrderClass'           => $base_order_class,
							'orderClass'               => $order_class,
							'parentOrderClass'         => $parent_order_class,
							'baseWrapperOrderClass'    => $base_wrapper_order_class,
							'wrapperOrderClass'        => $wrapper_order_class,
							'selectorPrefix'           => $selector_prefix,
							'settings'                 => $settings,

							// Style's state is only affecting module's style component when module's settings modal is opened (edited).
							'state'                    => 'value',
							'mode'                     => 'frontend',

							// FE only.
							'storeInstance'            => $store_instance,
							'orderIndex'               => $order_index,
							'styleGroup'               => 'module',
						]
					);
				}
			}
		}

		// Registering module's script data.
		if ( is_callable( $script_data_component ) ) {
			call_user_func(
				$script_data_component,
				[
					'name'          => $name,
					'attrs'         => $attrs,
					'parentAttrs'   => $parent_attrs,
					'id'            => $id,
					'selector'      => '.' . $selector_classname,
					'elements'      => $elements,

					// FE only.
					'storeInstance' => $store_instance,
					'orderIndex'    => $order_index,
				]
			);
		}

		$module_classnames_value = $classnames_instance->value();

		/**
		 * Filter the module classnames.
		 *
		 * @since ??
		 *
		 * @param string $module_classnames_value The module classnames value.
		 * @param array  $base_classnames_params  The base classnames params.
		 */
		$module_classname = apply_filters(
			'divi_module_classnames_value',
			$module_classnames_value,
			$base_classnames_params
		);

		$wrapper_classnames_value = $wrapper_classnames_instance->value();

		/**
		 * Filter the module wrapper classnames.
		 *
		 * @since ??
		 *
		 * @param string $wrapper_classnames_value The wrapper classnames value.
		 * @param array  $base_classnames_params   The base classnames params.
		 */
		$module_wrapper_classname = apply_filters(
			'divi_module_wrapper_classnames_value',
			$wrapper_classnames_value,
			$base_classnames_params
		);

		// Enqueue inline font assets.
		if ( ! empty( $attrs['content']['decoration']['inlineFont'] ) ) {
			ModuleUtils::load_module_inline_font( $attrs );
		}

		$module_wrapper = ModuleWrapper::render(
			[
				'children'         => $children,
				'classname'        => $module_classname,
				'name'             => $name,
				'styles'           => $styles,
				'htmlAttrs'        => $html_attrs,
				'parentAttrs'      => $parent_attrs,
				'siblingAttrs'     => $sibling_attrs,
				'tag'              => $tag,
				'hasModuleWrapper' => $has_module_wrapper,
				'wrapperTag'       => $wrapper_tag,
				'wrapperHtmlAttrs' => $wrapper_html_attrs,
				'wrapperClassname' => $module_wrapper_classname,
			]
		);

		$module_wrapper_filter_args = array_merge(
			$args,
			[
				'htmlAttrs'              => $html_attrs,
				'moduleClassname'        => $module_classname,
				'moduleWrapperClassname' => $module_wrapper_classname,
			]
		);

		/**
		 * Filter the module wrapper rendered output.
		 *
		 * @since ??
		 *
		 * @param string $module_wrapper             The rendered module wrapper.
		 * @param array  $module_wrapper_filter_args The module wrapper filter args.
		 */
		$module_wrapper_output = apply_filters( 'divi_module_wrapper_render', $module_wrapper, $module_wrapper_filter_args );

		return $module_wrapper_output;
	}

	/**
	 * Renders the styles preset for a module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type string         $name                            The name of the module.
	 *     @type array          $attrs                           The attributes of the module.
	 *     @type array          $defaultPrintedStyleAttrs        The default printed style attributes.
	 *     @type string         $parentId                        The ID of the parent module.
	 *     @type string         $parentName                      The name of the parent module.
	 *     @type string         $id                              The ID of the module.
	 *     @type int            $storeInstance                   The store instance.
	 *     @type ModuleElements $elements                        The elements of the module.
	 *     @type Classnames     $classnamesInstance              The classnames instance.
	 *     @type Classnames     $wrapperClassnamesInstance       The wrapper classnames instance.
	 *     @type string         $selectorPrefix                  The selector prefix.
	 *     @type bool           $hasModuleWrapper                Whether the module has a wrapper.
	 *     @type bool           $isStyleEnqueuedAsStaticCss      Whether the style is enqueued as static CSS.
	 *     @type callable       $stylesComponent                 The styles component.
	 *     @type array          $settings                        The settings of the module.
	 *     @type int            $orderIndex                      The order index of the module.
	 * }
	 *
	 * @return void
	 */
	public static function render_styles_preset_module( array $args ): void {
		// Extract args.
		$name                            = $args['name'];
		$attrs                           = $args['attrs'];
		$default_printed_style_attrs     = $args['defaultPrintedStyleAttrs'];
		$parent_id                       = $args['parentId'];
		$parent_name                     = $args['parentName'];
		$id                              = $args['id'];
		$store_instance                  = $args['storeInstance'];
		$elements                        = $args['elements'];
		$classnames_instance             = $args['classnamesInstance'];
		$wrapper_classnames_instance     = $args['wrapperClassnamesInstance'];
		$selector_prefix                 = $args['selectorPrefix'];
		$has_module_wrapper              = $args['hasModuleWrapper'];
		$is_style_enqueued_as_static_css = $args['isStyleEnqueuedAsStaticCss'];
		$styles_component                = $args['stylesComponent'];
		$settings                        = $args['settings'];
		$order_index                     = $args['orderIndex'];
		$sibling_attrs                   = $args['siblingAttrs'];

		$preset_item = GlobalPreset::get_selected_preset(
			[
				'moduleName'  => $name,
				'moduleAttrs' => $attrs,
			]
		);

		$parent_preset_item = $parent_id ? GlobalPreset::get_selected_preset(
			[
				'moduleName'  => $parent_name,
				'moduleAttrs' => $parent_attrs ?? [],
			]
		) : null;

		$sibling_previous_preset_item = null;

		if ( ! empty( $sibling_attrs['previous'] ) ) {
			$sibling_previous = BlockParserStore::get_sibling( $id, 'before', $store_instance );

			if ( $sibling_previous ) {
				$sibling_previous_preset_item = GlobalPreset::get_selected_preset(
					[
						'moduleName'  => $sibling_previous->blockName,
						'moduleAttrs' => $sibling_previous->attrs ?? [],
					]
				);
			}
		}

		$sibling_next_preset_item = null;

		if ( ! empty( $sibling_attrs['next'] ) ) {
			$sibling_next = BlockParserStore::get_sibling( $id, 'after', $store_instance );

			if ( $sibling_next ) {
				$sibling_next_preset_item = GlobalPreset::get_selected_preset(
					[
						'moduleName'  => $sibling_next->blockName,
						'moduleAttrs' => $sibling_next->attrs ?? [],
					]
				);
			}
		}

		$is_parent_layout_flex = $elements->get_is_parent_layout_flex();

		self::render_styles_preset(
			[
				'name'                       => $name,
				'defaultPrintedStyleAttrs'   => $default_printed_style_attrs,
				'elements'                   => $elements,
				'classnamesInstance'         => $classnames_instance,
				'wrapperClassnamesInstance'  => $wrapper_classnames_instance,
				'id'                         => $id,
				'storeInstance'              => $store_instance,
				'selectorPrefix'             => $selector_prefix,
				'hasModuleWrapper'           => $has_module_wrapper,
				'isStyleEnqueuedAsStaticCss' => $is_style_enqueued_as_static_css,
				'isSelectorProcessed'        => Style::is_preset_selector_processed( $preset_item->get_selector_class_name() ),
				'stylesComponent'            => $styles_component,
				'settings'                   => $settings,
				'orderIndex'                 => $order_index,
				'styleGroup'                 => 'preset',
				'presetItem'                 => $preset_item,
				'parentPresetItem'           => $parent_preset_item,
				'siblingPreviousPresetItem'  => $sibling_previous_preset_item,
				'siblingNextPresetItem'      => $sibling_next_preset_item,
				'isParentLayoutFlex'         => $is_parent_layout_flex,
			]
		);
	}

	/**
	 * Renders styles for a preset group.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type array          $attrs                           Attributes of the module.
	 *     @type string         $parentId                        ID of the parent module.
	 *     @type array          $defaultPrintedStyleAttrs        Default printed style attributes.
	 *     @type string         $name                            Name of the module.
	 *     @type ModuleElements $elements                        Elements of the module.
	 *     @type Classnames     $classnamesInstance              Instance of classnames.
	 *     @type Classnames     $wrapperClassnamesInstance       Instance of wrapper classnames.
	 *     @type string         $id                              ID of the module.
	 *     @type int            $storeInstance                   Instance of the store.
	 *     @type string         $selectorPrefix                  Prefix for the selector.
	 *     @type bool           $hasModuleWrapper                Whether the module has a wrapper.
	 *     @type bool           $isStyleEnqueuedAsStaticCss      Whether the style is enqueued as static CSS.
	 *     @type callable       $stylesComponent                 Component for styles.
	 *     @type array          $settings                        Settings for the module.
	 *     @type int            $orderIndex                      Order index of the module.
	 * }
	 *
	 * @return void
	 */
	public static function render_styles_preset_group( array $args ): void {
		// Extract args.
		$attrs                           = $args['attrs'];
		$parent_id                       = $args['parentId'];
		$parent_name                     = $args['parentName'];
		$default_printed_style_attrs     = $args['defaultPrintedStyleAttrs'];
		$name                            = $args['name'];
		$elements                        = $args['elements'];
		$classnames_instance             = $args['classnamesInstance'];
		$wrapper_classnames_instance     = $args['wrapperClassnamesInstance'];
		$id                              = $args['id'];
		$store_instance                  = $args['storeInstance'];
		$selector_prefix                 = $args['selectorPrefix'];
		$has_module_wrapper              = $args['hasModuleWrapper'];
		$is_style_enqueued_as_static_css = $args['isStyleEnqueuedAsStaticCss'];
		$styles_component                = $args['stylesComponent'];
		$settings                        = $args['settings'];
		$order_index                     = $args['orderIndex'];
		$sibling_attrs                   = $args['siblingAttrs'];

		$selected_group_presets = GlobalPreset::get_selected_group_presets(
			[
				'moduleAttrs' => $attrs,
				'moduleName'  => $name,
			]
		);

		$parent_selected_group_presets = $parent_id ? GlobalPreset::get_selected_group_presets(
			[
				'moduleAttrs' => $parent_attrs ?? [],
				'moduleName'  => $parent_name,
			]
		) : [];

		$sibling_previous_selected_group_presets = [];

		if ( ! empty( $sibling_attrs['previous'] ) ) {
			$sibling_previous = BlockParserStore::get_sibling( $id, 'before', $store_instance );

			if ( $sibling_previous ) {
				$sibling_previous_selected_group_presets = GlobalPreset::get_selected_group_presets(
					[
						'moduleAttrs' => $sibling_previous->attrs ?? [],
						'moduleName'  => $sibling_previous->blockName,
					]
				);
			}
		}

		$sibling_next_selected_group_presets = [];

		if ( ! empty( $sibling_attrs['next'] ) ) {
			$sibling_next = BlockParserStore::get_sibling( $id, 'after', $store_instance );

			if ( $sibling_next ) {
				$sibling_next_selected_group_presets = GlobalPreset::get_selected_group_presets(
					[
						'moduleAttrs' => $sibling_next->attrs ?? [],
						'moduleName'  => $sibling_next->blockName,
					]
				);
			}
		}

		foreach ( $selected_group_presets as $group_id => $group_preset_item ) {
			if ( ! $group_preset_item->is_exist() ) {
				continue;
			}

			$parent_group_preset_item = $parent_selected_group_presets[ $group_id ] ?? null;

			// Populate sibling previous module preset for current group.
			$sibling_previous_group_preset_item = $sibling_previous_selected_group_presets[ $group_id ] ?? null;

			// Populate sibling nrxt module preset for current group.
			$sibling_next_group_preset_item = $sibling_next_selected_group_presets[ $group_id ] ?? null;

			self::render_styles_preset(
				[
					'name'                       => $name,
					'defaultPrintedStyleAttrs'   => $default_printed_style_attrs,
					'elements'                   => $elements,
					'classnamesInstance'         => $classnames_instance,
					'wrapperClassnamesInstance'  => $wrapper_classnames_instance,
					'id'                         => $id,
					'storeInstance'              => $store_instance,
					'selectorPrefix'             => $selector_prefix,
					'hasModuleWrapper'           => $has_module_wrapper,
					'isStyleEnqueuedAsStaticCss' => $is_style_enqueued_as_static_css,
					'isSelectorProcessed'        => Style::is_preset_selector_processed( $group_preset_item->get_selector_class_name() . '--' . $name . '--' . $group_id ),
					'stylesComponent'            => $styles_component,
					'settings'                   => $settings,
					'orderIndex'                 => $order_index,
					'styleGroup'                 => 'presetGroup',
					'presetItem'                 => $group_preset_item,
					'parentPresetItem'           => $parent_group_preset_item,
					'siblingPreviousPresetItem'  => $sibling_previous_group_preset_item,
					'siblingNextPresetItem'      => $sibling_next_group_preset_item,
				]
			);
		}
	}

	/**
	 * Renders preset styles.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type string           $styleGroup                  The style group. Either 'preset' or 'presetGroup'.
	 *     @type string           $name                        The name of the module.
	 *     @type array            $defaultPrintedStyleAttrs    Default printed style attributes.
	 *     @type ModuleElements   $elements                    Instance of ModuleElements.
	 *     @type Classnames       $classnamesInstance          Instance of Classnames for the module.
	 *     @type Classnames       $wrapperClassnamesInstance   Instance of Classnames for the module wrapper.
	 *     @type string           $id                          The ID of the module.
	 *     @type int              $storeInstance               Instance of the store.
	 *     @type string           $selectorPrefix              The selector prefix.
	 *     @type bool             $hasModuleWrapper            Whether the module has a wrapper.
	 *     @type bool             $isStyleEnqueuedAsStaticCss  Whether the style is enqueued as static CSS.
	 *     @type bool             $isSelectorProcessed         Whether the selector has been processed.
	 *     @type callable         $stylesComponent             The styles component callback.
	 *     @type array            $settings                    The settings array.
	 *     @type int              $orderIndex                  The order index.
	 *     @type GlobalPresetItem $presetItem                  Instance of GlobalPresetItem for the current preset.
	 *     @type GlobalPresetItem $parentPresetItem            Instance of GlobalPresetItem for the parent preset.
	 *     @type GlobalPresetItem $siblingPreviousPresetItem   Instance of GlobalPresetItem for the previous sibling preset.
	 *     @type GlobalPresetItem $siblingNextPresetItem       Instance of GlobalPresetItem for the next sibling preset.
	 * }
	 *
	 * @return void
	 */
	public static function render_styles_preset( array $args ): void {
		// Extract args.
		$style_group                     = $args['styleGroup'];
		$name                            = $args['name'];
		$default_printed_style_attrs     = $args['defaultPrintedStyleAttrs'];
		$elements                        = $args['elements'];
		$classnames_instance             = $args['classnamesInstance'];
		$wrapper_classnames_instance     = $args['wrapperClassnamesInstance'];
		$id                              = $args['id'];
		$store_instance                  = $args['storeInstance'];
		$selector_prefix                 = $args['selectorPrefix'];
		$has_module_wrapper              = $args['hasModuleWrapper'];
		$is_style_enqueued_as_static_css = $args['isStyleEnqueuedAsStaticCss'];
		$is_selector_processed           = $args['isSelectorProcessed'];
		$styles_component                = $args['stylesComponent'];
		$settings                        = $args['settings'];
		$order_index                     = $args['orderIndex'];
		$preset_item                     = $args['presetItem'];
		$parent_preset_item              = $args['parentPresetItem'];
		$sibling_previous_preset_item    = $args['siblingPreviousPresetItem'];
		$sibling_next_preset_item        = $args['siblingNextPresetItem'];

		// Only proceed if the preset item has data attributes.
		if ( $preset_item instanceof GlobalPresetItem && $preset_item->has_data_attrs() ) {
			Style::set_group_style( $style_group );

			if ( $elements instanceof ModuleElements ) {
				$elements->set_style_group( $style_group );
			}

			// Preset's selector class name.
			$preset_item_selector_class_name = $preset_item->get_selector_class_name();

			// Add preset classname to module.
			if ( $classnames_instance instanceof Classnames ) {
				$classnames_instance->add( $preset_item_selector_class_name );
			}

			// Add preset classname (wrapper version) to module wrapper.
			if ( $wrapper_classnames_instance instanceof Classnames ) {
				$wrapper_classnames_instance->add( "{$preset_item_selector_class_name}_wrapper" );
			}

			// Populate parent module preset data.
			$parent_preset_item_attrs               = [];
			$parent_preset_item_selector_class_name = '';
			$parent_preset_item_order_class         = '';

			if ( $parent_preset_item instanceof GlobalPresetItem && $parent_preset_item->has_data_attrs() ) {
				$parent_preset_item_attrs               = $parent_preset_item->get_data_attrs();
				$parent_preset_item_selector_class_name = $parent_preset_item->get_selector_class_name();
				$parent_preset_item_order_class         = '.' . $parent_preset_item_selector_class_name;
			}

			// Populate sibling module preset data.
			$siblings_preset_item_attrs = [
				'previous' => [],
				'next'     => [],
			];

			if ( $sibling_previous_preset_item instanceof GlobalPresetItem && $sibling_previous_preset_item->has_data_attrs() ) {
				$sibling_previous_preset_attrs                        = $sibling_previous_preset_item->get_data_attrs();
				$siblings_preset_item_attrs['previous']['background'] = $sibling_previous_preset_attrs['module']['decoration']['background'] ?? null;
			}

			if ( $sibling_next_preset_item instanceof GlobalPresetItem && $sibling_next_preset_item->has_data_attrs() ) {
				$sibling_next_preset_attrs                        = $sibling_next_preset_item->get_data_attrs();
				$siblings_preset_item_attrs['next']['background'] = $sibling_next_preset_attrs['module']['decoration']['background'] ?? null;
			}

			// Preset's order class names.
			$preset_item_base_order_class = '.' . $preset_item_selector_class_name;
			$preset_item_order_class      = $selector_prefix . $preset_item_base_order_class;

			// Set styles for presets.
			if ( $elements instanceof ModuleElements ) {
				$elements->set_order_class( $preset_item_order_class );
				$elements->set_base_order_class( $preset_item_base_order_class );
			}

			// Preset wrapper order class names.
			$preset_item_base_wrapper_order_class = $has_module_wrapper ? $preset_item_base_order_class . '_wrapper' : '';
			$preset_item_wrapper_order_class      = $has_module_wrapper ? $selector_prefix . $preset_item_base_wrapper_order_class : '';

			if ( $elements instanceof ModuleElements ) {
				$elements->set_wrapper_order_class( $preset_item_wrapper_order_class );
			}

			// If the style has not been enqueued as static CSS and the preset style selector hasn't been
			// processed, then we need to call the styles component.
			if ( ! $is_style_enqueued_as_static_css && ! $is_selector_processed ) {
				$preset_item_attrs_raw = $preset_item->get_data_attrs();
				$preset_item_attrs     = ModuleUtils::remove_matching_values( $preset_item_attrs_raw, $default_printed_style_attrs );

				// Set preset attributes as the attributes data that are used by the ModuleElements instance during the styles rendering.
				if ( $elements instanceof ModuleElements ) {
					$elements->use_custom_module_attrs( $preset_item_attrs );
				}

				// Calls the styles component.
				call_user_func(
					$styles_component,
					[
						'id'                       => $id,
						'elements'                 => $elements,
						'name'                     => $name,
						'attrs'                    => $preset_item_attrs,
						'parentAttrs'              => $parent_preset_item_attrs,
						'siblingAttrs'             => $siblings_preset_item_attrs,
						'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
						'baseOrderClass'           => $preset_item_base_order_class,
						'orderClass'               => $preset_item_order_class,
						'parentOrderClass'         => $parent_preset_item_order_class,
						'baseWrapperOrderClass'    => $preset_item_base_wrapper_order_class,
						'wrapperOrderClass'        => $preset_item_wrapper_order_class,
						'settings'                 => $settings,

						// Preset's state is set to 'value'. This is to ensure that these styles specifically affect
						// the style component when the module's settings modal is open (being edited).
						'state'                    => 'value',
						'mode'                     => 'frontend',
						'styleGroup'               => $style_group,

						// Following parameters are only for the FrontEnd.
						'storeInstance'            => $store_instance,
						'orderIndex'               => $order_index,
					]
				);

				// Reset the custom module attributes so the next styles rendering will use the original module attributes.
				if ( $elements instanceof ModuleElements ) {
					$elements->clear_custom_attributes();
				}
			}
		}
	}
}
