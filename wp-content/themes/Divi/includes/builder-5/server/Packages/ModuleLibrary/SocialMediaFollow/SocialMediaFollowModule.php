<?php
/**
 * Module: Social Media Follow class.
 *
 * @package ET\Builder\Packages\ModuleLibrary\SocialMediaFollow
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\SocialMediaFollow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;

/**
 * `SocialMediaFollow` is consisted of functions used for Social Media Follow such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class SocialMediaFollowModule implements DependencyInterface {

	/**
	 * Module classnames function for Social Media Follow module.
	 *
	 * This function is equivalent of JS function moduleClassnames located in
	 * visual-builder/packages/module-library/src/components/social-media-follow/module-classnames.ts.
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

		$classnames_instance->add( 'clearfix' );

		// Text Options classnames.
		$text_options_classnames = TextClassnames::text_options_classnames( $attrs['module']['advanced']['text'] );

		if ( $text_options_classnames ) {
			$classnames_instance->add( $text_options_classnames, true );
		}

		$has_follow_button = 'on' === ( $attrs['socialNetwork']['advanced']['followButton']['desktop']['value'] ?? 'off' );
		$classnames_instance->add( 'has_follow_button', $has_follow_button );

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of decoration property, remove this.
					'attrs'  => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $attrs['module']['advanced']['link'] ?? [],
						]
					),
					'border' => false,
				]
			)
		);
	}

	/**
	 * Set script data of used module options.
	 *
	 * This function is equivalent of JS function ModuleScriptData located in
	 * visual-builder/packages/module-library/src/components/social-media-follow/module-script-data.tsx.
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
				'attrName'        => 'module',
				'scriptDataProps' => [
					'animation' => [
						'selector' => $selector,
					],
				],
			]
		);

		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'selector'      => $selector,
				'setClassName'  => [
					[
						'data'          => [
							'has_follow_button' => $attrs['socialNetwork']['advanced']['followButton'] ?? [],
						],
						'valueResolver' => function ( $value, $resolver_args ) {
							return 'has_follow_button' === $resolver_args['className'] && 'on' === ( $value ?? '' ) ? 'add' : 'remove';
						},
					],
				],
			]
		);
	}

	/**
	 * Social Media Follow render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function SocialMediaFollowEdit located in
	 * visual-builder/packages/module-library/src/components/social-media-follow/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by VB.
	 * @param string         $content                     Block content.
	 * @param WP_Block       $block                       Parsed block object that being rendered.
	 * @param ModuleElements $elements                    ModuleElements instance.
	 * @param array          $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return string HTML rendered of Social Media Follow module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements, $default_printed_style_attrs ) {
		$children_ids = $block->parsed_block['innerBlocks'] ? array_map(
			function( $inner_block ) {
				return $inner_block['id'];
			},
			$block->parsed_block['innerBlocks']
		) : [];

		$children = '';

		$module_components = $elements->style_components(
			[
				'attrName' => 'module',
			]
		);

		if ( $module_components ) {
			$children .= $module_components;
		}

		$children .= $content;

		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		return Module::render(
			[
				// FE only.
				'orderIndex'               => $block->parsed_block['orderIndex'],
				'storeInstance'            => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                       => $block->parsed_block['id'],
				'name'                     => $block->block_type->name,
				'tag'                      => 'ul',
				'moduleCategory'           => $block->block_type->category,
				'attrs'                    => $attrs,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
				'elements'                 => $elements,
				'classnamesFunction'       => [ self::class, 'module_classnames' ],
				'scriptDataComponent'      => [ self::class, 'module_script_data' ],
				'stylesComponent'          => [ self::class, 'module_styles' ],
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
	 * visual-builder/packages/module-library/src/components/social-media-follow/custom-css.ts.
	 *
	 * A minor difference with the JS const cssFields, this function did not have `label` property on each array item.
	 *
	 * @since ??
	 */
	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'divi/social-media-follow' )->customCssFields;
	}

	/**
	 * Icon style declaration for social media follow module.
	 *
	 * This function will declare Icon style for Social Media Follow module.
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
	public static function icon_size_style_declaration( $params ) {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$size     = $params['attrValue']['size'] ?? '';
		$use_size = $params['attrValue']['useSize'] ?? '';

		if ( ! empty( $use_size ) && 'on' === $use_size ) {
			$parsed_size = self::numeric_parse_value( $size );
			$style_declarations->add( 'font-size', $size );
			$style_declarations->add( 'line-height', $parsed_size['value_number'] * 2 . $parsed_size['value_unit'] );
		}

		return $style_declarations->value();
	}

	/**
	 * Icon dimension style declaration for social media follow icon.
	 *
	 * This function will declare Icon dimension style style for Social Media Follow module.
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
	public static function icon_dimension_style_declaration( $params ) {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => false,
			]
		);

		$size     = $params['attrValue']['size'] ?? '';
		$use_size = $params['attrValue']['useSize'] ?? '';

		if ( ! empty( $use_size ) && 'on' === $use_size ) {
			$parsed_size = self::numeric_parse_value( $size );
			if ( $parsed_size ) {
				$style_declarations->add( 'width', $parsed_size['value_number'] * 2 . $parsed_size['value_unit'] );
				$style_declarations->add( 'height', $parsed_size['value_number'] * 2 . $parsed_size['value_unit'] );
			}
		}

		return $style_declarations->value();
	}

	/**
	 * Content alignment style declaration
	 *
	 * This function will declare content alignment style for Social Media Follow module.
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
	public static function alignment_style_declaration( $params ) {
		$alignment = $params['attrValue']['orientation'] ?? '';

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
	 * SocialMediaFollow Module's style components.
	 *
	 * This function is equivalent of JS function ModuleStyles located in
	 * visual-builder/packages/module-library/src/components/social-media-follow/styles.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *       @type string         $id                Module ID. In VB, the ID of module is UUIDV4. In FE, the ID is order index.
	 *       @type string         $name              Module name.
	 *       @type string         $attrs             Module attributes.
	 *       @type string         $parentAttrs       Parent attrs.
	 *       @type string         $orderClass        Selector class name.
	 *       @type string         $parentOrderClass  Parent selector class name.
	 *       @type string         $wrapperOrderClass Wrapper selector class name.
	 *       @type string         $settings          Custom settings.
	 *       @type string         $state             Attributes state.
	 *       @type string         $mode              Style mode.
	 *       @type ModuleElements $elements          ModuleElements instance.
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
								'advancedStyles'           => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['module']['advanced']['text'] ?? [],
											'declarationFunction' => [ self::class, 'alignment_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/text',
										'props'         => [
											'attr' => $attrs['module']['advanced']['text'] ?? [],
										],
									],
								],
							],
						]
					),

					// Icon.
					$elements->style(
						[
							'attrName'   => 'icon',
							'styleProps' => [
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']} li a.icon:before",
											'attr'     => $attrs['icon']['advanced']['color'] ?? [],
											'property' => 'color',
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => "{$args['orderClass']} li a.icon:before",
											'attr'     => $attrs['icon']['advanced']['size'] ?? [],
											'declarationFunction' => [ self::class, 'icon_size_style_declaration' ],
										],
									],
									[
										'componentName' => 'divi/common',
										'props'         => [
											'selector' => implode(
												', ',
												[
													"{$args['orderClass']} li a.icon",
													"{$args['orderClass']} li a.icon:before",
												]
											),
											'attr'     => $attrs['icon']['advanced']['size'] ?? [],
											'declarationFunction' => [ self::class, 'icon_dimension_style_declaration' ],
										],
									],
								],
							],
						]
					),

					// Button.
					$elements->style(
						[
							'attrName' => 'button',
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
	 * Parses the unit from a given value.
	 *
	 * @param mixed  $raw_val      The value to parse the unit from.
	 * @param string $default_unit The default unit to use if the value does not have a unit.
	 *
	 * @return string The unit of the value, or the default unit if no unit is found.
	 */
	public static function get_unit( $raw_val, $default_unit = 'px' ) {
		$value                   = empty( $raw_val ) ? '' : $raw_val;
		$value_length            = strlen( $value );
		$valid_one_char_units    = [ '%', '°' ];
		$valid_two_chars_units   = [ 'em', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw', 'ms' ];
		$valid_three_chars_units = [ 'deg', 'rem' ];
		$valid_four_chars_units  = [ 'vmin', 'vmax' ];
		$important               = '!important';
		$important_length        = strlen( $important );

		if ( '' === $value || is_numeric( $value ) ) {
			return $default_unit;
		}

		if ( substr( $value, ( -1 * $important_length ) ) === $important ) {
			$value_length -= $important_length;
			$value         = trim( substr( $value, 0, $value_length ) );
		}

		if ( in_array( substr( $value, -4 ), $valid_four_chars_units, true ) ) {
			return substr( $value, -4 );
		}

		if ( in_array( substr( $value, -3 ), $valid_three_chars_units, true ) ) {
			return substr( $value, -3 );
		}

		if ( in_array( substr( $value, -2 ), $valid_two_chars_units, true ) ) {
			return substr( $value, -2 );
		}

		if ( in_array( substr( $value, -1 ), $valid_one_char_units, true ) ) {
			return substr( $value, -1 );
		}

		return $default_unit;
	}

	/**
	 * Parses a numeric value and returns the value number and unit.
	 *
	 * @param mixed $value The value to parse.
	 *
	 * @return array|null Returns an array with the parsed value number and unit, or null if the value could not be parsed.
	 */
	public static function numeric_parse_value( $value ) {
		$value_number = $value ? (float) $value : false;

		if ( false === $value_number ) {
			return null;
		}

		return [
			'value_number' => $value_number,
			'value_unit'   => self::get_unit( $value, '' ),
		];
	}

	/**
	 * Loads `SocialMediaFollow` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/social-media-follow/';

		add_filter( 'divi_conversion_presets_attrs_map', array( SocialMediaFollowPresetAttrsMap::class, 'get_map' ), 10, 2 );

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
