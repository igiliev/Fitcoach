<?php
/**
 * Module Options: Sticky Utils Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Sticky;

use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * StickyUtils class.
 *
 * This class provides utility methods for working with sticky elements and modules.
 *
 * @since ??
 */
class StickyUtils {

	/**
	 * Retrieve the data for incompatible attribute path and value.
	 *
	 * This function retrieves the data for incompatible attribute path and value that determines
	 * whether the sticky mechanism can be used on the current element.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for retrieving the data.
	 *     Default `[]`.
	 *
	 *     @type string $position Optional. The position attribute name. Default `'position'`.
	 *     @type string $scroll   Optional. The scroll attribute name. Default `'scroll'`.
	 * }
	 *
	 * @return array The incompatible attribute path and value data.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'position' => 'position',
	 *     'scroll'   => 'scroll',
	 * ];
	 * $data = self::get_incompatible_attr_path_value( $args );
	 * ```
	 */
	public static function get_incompatible_attr_path_value( array $args = [] ): array {
		$args = wp_parse_args(
			$args,
			[
				'position' => 'position',
				'scroll'   => 'scroll',
			]
		);

		$position = $args['position'];
		$scroll   = $args['scroll'];

		$incompatible_attr_path_value = [
			"{$position}.desktop.value.mode"          => [ 'absolute', 'fixed' ],
			"{$scroll}.desktop.value.verticalMotion.enable" => [ 'on' ],
			"{$scroll}.desktop.value.horizontalMotion.enable" => [ 'on' ],
			"{$scroll}.desktop.value.fade.enable"     => [ 'on' ],
			"{$scroll}.desktop.value.scaling.enable"  => [ 'on' ],
			"{$scroll}.desktop.value.rotating.enable" => [ 'on' ],
			"{$scroll}.desktop.value.blur.enable"     => [ 'on' ],
		];

		/**
		 * Filter incompatible attribute path and value data.
		 *
		 * @since ??
		 *
		 * @param array $incompatible_attr_path_value Incompatible attribute path and value data.
		 */
		return apply_filters(
			'divi_module_options_sticky_incompatible_attr_path_value',
			$incompatible_attr_path_value
		);
	}

	/**
	 * Returns formatted subname-based values that are used on certain style properties.
	 * NOTE: this is a legacy of D4 sticky settings.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $attr     Attribute of sizing or position module options.
	 *     @type string $subname  Subname of the attribute.
	 * }
	 *
	 * @return array|boolean Formatted subname-based values. Returns an array of values if successful,
	 *                        or false if no values are found.
	 */
	public static function get_formatted_subname_values( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'attr'    => [],
				'subname' => '',
			]
		);

		$attr    = $args['attr'];
		$subname = $args['subname'];
		$values  = [];

		if ( is_array( $attr ) ) {
			foreach ( $attr as $attr_breakpoint => $state_value ) {
				$attr_value = ArrayUtility::get_value( $state_value['value'] ?? [], $subname );

				if ( is_string( $attr_value ) ) {
					$values[ $attr_breakpoint ] = $attr_value;
				}
			}
		}

		return ! empty( $values ) ? $values : false;
	}

	/**
	 * Format sticky setting for responsive and non-responsive capability.
	 *
	 * This function is used to format the sticky setting for responsive and non-responsive capabilities.
	 * NOTE: this is legacy of D4 sticky settings.
	 *
	 * @since ??
	 *
	 * @param bool|array $value          The sticky setting. It can be a boolean value or an array.
	 * @param string     $default_value  The default setting.
	 *
	 * @return array|string The formatted sticky setting.
	 *                     If the value is false, it returns the default value.
	 *                     If the value is an array with only one element and that element is `'desktop'`,
	 *                     it returns the value of `'desktop'` key from the array.
	 *                     Otherwise, it returns the value as it is.
	 *
	 * @example:
	 * ```php
	 * $value = false;
	 * $default_value = 'default';
	 * $formatted_value = format_sticky_setting($value, $default_value);
	 *
	 * // Result: $formatted_value is 'default'
	 * ```
	 *
	 * @example:
	 * ```php
	 * $value = ['desktop' => 'sticky'];
	 * $default_value = 'default';
	 * $formatted_value = format_sticky_setting($value, $default_value);
	 *
	 * // Result: $formatted_value is 'sticky'
	 * ```
	 */
	public static function format_sticky_setting( $value, string $default_value ) {
		if ( false === $value ) {
			return $default_value;
		}

		return ( 1 === count( $value ) && isset( $value['desktop'] ) ) ? $value['desktop'] : $value;
	}

	/**
	 * Get sticky settings based on sticky attribute and affecting attributes.
	 * NOTE: This function is based on D4 function that is used at D4's ET_Builder_Element->process_sticky() that is used
	 * to generate `window.diviElementStickyData` value that is used by sticky elements on frontend. Apparently the
	 * property that is generated in D4 VB and D4 FE differs. Thus we sticky to the one that is used on FE.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $affectingAttrs Attributes that affect sticky
	 *     @type array $attr           Sticky attribute
	 *     @type string $breakpoint    Given breakpoint
	 *     @type array $defaultAttr    Default sticky attribute
	 *     @type string $id            Element id
	 *     @type string $selector      Element selector
	 *     @type string $state         Given state.
	 * }
	 *
	 * @return array
	 */
	/**
	 * Get sticky setting(s) based on sticky attribute and affecting attributes.
	 *
	 * This function retrieves the sticky setting based on the provided arguments.
	 *
	 *
	 * NOTE: This function is based on D4 function that is used at D4's `ET_Builder_Element->process_sticky()` that is used
	 * to generate `window.diviElementStickyData` value that is used by sticky elements on frontend. Apparently the
	 * property that is generated in D4 Visual Builder and D4 FrontEnd differs. Thus we sticky to the one that is used on FrontEnd.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for retrieving the sticky setting.
	 *
	 *     @type array $affectingAttrs  Optional. The affecting attributes that may change the behavior of the sticky element. Default `[]`.
	 *     @type array $attr            Optional. The attributes of the sticky element. Default `[]`.
	 *     @type string $breakpoint     Optional. The current breakpoint. Default empty string.
	 *     @type array $defaultAttr     Optional. The default attributes for a sticky element. Default `[]`.
	 *     @type string $id             Optional. The ID of the sticky element. Default empty string.
	 *     @type string $selector       Optional. The selector of the sticky element. Default empty string.
	 *     @type string $state          Optional. The state of the sticky element. Default empty string.
	 * }
	 *
	 * @return array The sticky setting(s).
	 *
	 * @example:
	 * ```php
	 * $sticky_setting = self::get_sticky_setting( [
	 *     'affectingAttrs' => [
	 *         'position' => [
	 *             'desktop' => [
	 *                 'mode' => 'relative',
	 *             ],
	 *         ],
	 *         'sizing' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'alignment' => 'center',
	 *                     'width' => '300px',
	 *                     'maxWidth' => '600px',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     'attr' => [
	 *         'position' => 'sticky',
	 *         'offset' => [
	 *             'top' => '10px',
	 *             'bottom' => '20px',
	 *         ],
	 *         'limit' => [
	 *             'top' => 'none',
	 *             'bottom' => '100px',
	 *         ],
	 *         'offset' => [
	 *             'surrounding' => 'off',
	 *         ],
	 *         'transition' => 'off',
	 *     ],
	 *     'id' => 'my-sticky-element',
	 *     'selector' => '.my-sticky-selector',
	 *     'state' => 'active',
	 * ] );
	 * ```
	 *
	 * @output:
	 * ```php
	 *   [
	 *     'id' => 'my-sticky-element',
	 *     'selector' => '.my-sticky-selector',
	 *     'position' => 'relative',
	 *     'topOffset' => '10px',
	 *     'bottomOffset' => '20px',
	 *     'topLimit' => 'none',
	 *     'bottomLimit' => '100px',
	 *     'offsetSurrounding' => 'off',
	 *     'transition' => 'off',
	 *     'styles' => [
	 *         'module_alignment' => [
	 *             'desktop' => 'center',
	 *             'tablet' => '',
	 *             'phone' => '',
	 *         ],
	 *         'width' => '300px',
	 *         'max-width' => '600px',
	 *     ],
	 *     'stickyStyles' => [
	 *         'width' => '300px',
	 *     ],
	 *   ]
	 * ```
	 */
	public static function get_sticky_setting( array $args ): array {
		$args = wp_parse_args(
			$args,
			[
				'affectingAttrs' => [],
				'attr'           => [],
				'breakpoint'     => '',
				'defaultAttr'    => [
					'desktop' => [
						'value' => [
							'position'   => 'none',
							'offset'     => [
								'top'         => '0px',
								'bottom'      => '0px',
								'surrounding' => 'on',
							],
							'limit'      => [
								'top'    => 'none',
								'bottom' => 'none',
							],
							'transition' => 'on',
						],
					],
				],
				'id'             => '',
				'selector'       => '',
				'state'          => '',
			]
		);

		// Relative position affects offsets so whenever relative positioning values are updated, it'll
		// need to be included on setting sync so offsets are being regenerated due to the settings
		// changes (synced settings are being compared to prevent unnecessary sync).
		$position               = ModuleUtils::get_attr_subname_value(
			[
				'attr'       => $args['affectingAttrs']['position'] ?? null,
				'breakpoint' => $args['breakpoint'],
				'state'      => $args['state'],
				'subname'    => 'mode',
			]
		);
		$incompatible_positions = [ 'absolute', 'fixed' ];

		// If incompatible position value is used, exit early.
		if ( in_array( $position, $incompatible_positions, true ) ) {
			return [];
		}

		$sticky_position_raw = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'position',
			]
		);
		$sticky_position     = is_array( $sticky_position_raw )
			? array_map( 'ET\Builder\Framework\Utility\TextTransform::snake_case', $sticky_position_raw )
			: $sticky_position_raw;
		$top_offset          = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'offset.top',
			]
		);
		$bottom_offset       = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'offset.bottom',
			]
		);
		$top_limit           = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'limit.top',
			]
		);
		$bottom_limit        = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'limit.bottom',
			]
		);
		$offset_surrounding  = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'offset.surrounding',
			]
		);
		$transition          = self::get_formatted_subname_values(
			[
				'attr'    => $args['attr'],
				'subname' => 'transition',
			]
		);

		// Configured script data settings format.
		$sticky_setting = [
			'id'                => $args['id'],
			'selector'          => $args['selector'],
			'position'          => self::format_sticky_setting( $sticky_position, $args['defaultAttr']['desktop']['value']['position'] ?? null ),
			'topOffset'         => self::format_sticky_setting( $top_offset, $args['defaultAttr']['desktop']['value']['offset']['top'] ?? null ),
			'bottomOffset'      => self::format_sticky_setting( $bottom_offset, $args['defaultAttr']['desktop']['value']['offset']['bottom'] ?? null ),
			'topLimit'          => self::format_sticky_setting( $top_limit, $args['defaultAttr']['desktop']['value']['limit']['top'] ?? null ),
			'bottomLimit'       => self::format_sticky_setting( $bottom_limit, $args['defaultAttr']['desktop']['value']['limit']['bottom'] ?? null ),
			'offsetSurrounding' => self::format_sticky_setting( $offset_surrounding, $args['defaultAttr']['desktop']['value']['offset']['surrounding'] ?? null ),
			'transition'        => self::format_sticky_setting( $transition, $args['defaultAttr']['desktop']['value']['transition'] ?? null ),
			'styles'            => [],
		];

		// This property is always passed even if empty therefore `getFormattedSubnameValues()` is not used.
		// Sticky needs the following to set correct adjustment based on element's alignment.
		$sticky_setting['styles']['module_alignment'] = [
			'desktop' => $args['affectingAttrs']['sizing']['desktop']['value']['alignment'] ?? '',
			'tablet'  => $args['affectingAttrs']['sizing']['tablet']['value']['alignment'] ?? '',
			'phone'   => $args['affectingAttrs']['sizing']['phone']['value']['alignment'] ?? '',
		];

		// Element's styles properties.
		$styles_width = self::get_formatted_subname_values(
			[
				'attr'    => $args['affectingAttrs']['sizing'] ?? null,
				'subname' => 'width',
			]
		);

		if ( $styles_width ) {
			$sticky_setting['styles']['width'] = $styles_width;
		}

		$styles_max_width = self::get_formatted_subname_values(
			[
				'attr'    => $args['affectingAttrs']['sizing'] ?? null,
				'subname' => 'maxWidth',
			]
		);

		if ( $styles_max_width ) {
			$sticky_setting['styles']['max-width'] = $styles_max_width;
		}

		// Element's sticky styles properties.
		// Sticky style of property which is used for sticky element's inline style needs to be passed
		// into sticky configuration because it needs to be adjustend and then added on inline style.
		$sticky_styles_width = ModuleUtils::get_attr_subname_value(
			[
				'attr'       => $args['affectingAttrs']['sizing'] ?? null,
				'breakpoint' => 'desktop',
				'state'      => 'sticky',
				'subname'    => 'width',
				'mode'       => 'get',
			]
		);

		if ( $sticky_styles_width ) {
			$sticky_setting['stickyStyles']['width'] = $sticky_styles_width;
		}

		$sticky_styles_max_width = ModuleUtils::get_attr_subname_value(
			[
				'attr'       => $args['affectingAttrs']['sizing'] ?? null,
				'breakpoint' => 'desktop',
				'state'      => 'sticky',
				'subname'    => 'maxWidth',
				'mode'       => 'get',
			]
		);

		if ( $sticky_styles_max_width ) {
			$sticky_setting['stickyStyles']['max-width'] = $sticky_styles_max_width;
		}

		if ( $position ) {
			$sticky_setting['styles']['positioning'] = $position;

			$position_sticky_origin = ModuleUtils::get_attr_subname_value(
				[
					'attr'         => $args['affectingAttrs']['position'] ?? null,
					'breakpoint'   => $args['breakpoint'],
					'state'        => $args['state'],
					'subname'      => 'origin',
					'defaultValue' => [
						'relative' => 'top left',
					],
				]
			);

			if ( ! empty( $position_sticky_origin ) ) {
				// The script is uses D4 expected value where the value format is underscore separated value.
				// In D5, value is space separated value to make it compatible with CSS property value.
				$sticky_setting['stickyStyles']['position_origin_r'] = str_replace( ' ', '_', $position_sticky_origin['relative'] ?? '' );
			}

			$position_sticky_offset = ModuleUtils::get_attr_subname_value(
				[
					'attr'         => $args['affectingAttrs']['position'] ?? null,
					'breakpoint'   => $args['breakpoint'],
					'state'        => 'sticky',
					'subname'      => 'offset',
					'defaultValue' => [],
				]
			);

			if ( $position_sticky_offset ) {
				$sticky_setting['stickyStyles']['horizontal_offset'] = $position_sticky_offset['horizontal'] ?? null;
				$sticky_setting['stickyStyles']['vertical_offset']   = $position_sticky_offset['vertical'] ?? null;
			}
		}

		return $sticky_setting;
	}

	/**
	 * Get valid sticky position(ss).
	 *
	 * This function returns an array of valid sticky positions that can be used for elements.
	 *
	 * @since ??
	 *
	 * @return string[] An array of valid sticky positions. The valid positions are `'top'`, `'bottom'`, and `'topBottom'`.
	 */
	public static function get_valid_sticky_position(): array {
		return [
			'top',
			'bottom',
			'topBottom',
		];
	}

	/**
	 * Check if the given array of affecting attributes has any attributes incompatible with sticky mechanism.
	 *
	 * This function checks each attribute in the array against a list of possible incompatible values.
	 * If any attribute has a value that matches one of the incompatible values, the function will return true,
	 * indicating that there are incompatible attributes present.
	 *
	 * @since ??
	 *
	 * @param array $affecting_attrs The array of affecting attributes to check.
	 *
	 * @return bool Whether or not the array of affecting attributes has any incompatible attributes.
	 *
	 * @example:
	 * ```php
	 *     self::has_incompatible_attrs( [
	 *         'position' => [ 'sticky', 'fixed' ],
	 *         'scroll'   => [ 'visible' ],
	 *     ] );
	 * ```
	 */
	public static function has_incompatible_attrs( array $affecting_attrs ): bool {
		$affecting_attrs = wp_parse_args(
			$affecting_attrs,
			[]
		);

		$incompatible = false;

		foreach ( self::get_incompatible_attr_path_value() as $attr_path => $possible_values ) {
			$attr_value = ArrayUtility::get_value( $affecting_attrs, $attr_path );

			// If the value exist on current incompatible field's options, stop loop and return true.
			if ( in_array( $attr_value, $possible_values, true ) ) {
				$incompatible = true;

				break;
			}
		}

		return $incompatible;
	}

	/**
	 * Determines if a module is inside a sticky module, i.e one of module's ancestor is sticky, in the layout state.
	 *
	 * This function checks if a given module is inside a sticky module based on the layout state.
	 * It iterates through the ancestors of the module, starting from its parent, and checks if any of them
	 * have the 'sticky' decoration attribute set.
	 * If a module is inside a sticky module, it means it will be affected by sticky behaviors such as sticky positioning and sticky scrolling.
	 *
	 * @since ??
	 *
	 * @param string             $id           The ID of the module.
	 * @param BlockParserBlock[] $layout_state The layout state containing module attributes.
	 *
	 * @return bool Returns `true` if the module is inside a sticky module.
	 *              Returns `false` if the module is not inside a sticky module or if the module itself is sticky.
	 *
	 * @example:
	 * ```php
	 * // Example 1: Check if a module is inside a sticky module
	 *
	 * // Given a module ID and the layout state
	 * $module_id = 'example-module';
	 * $layout_state = [...]; // array of BlockParserBlock objects
	 *
	 * // Check if the module is inside a sticky module
	 * $is_inside_sticky_module = self::is_inside_sticky_module($module_id, $layout_state);
	 *
	 * if ($is_inside_sticky_module) {
	 *     // Module is inside a sticky module
	 *     // Perform actions for modules inside a sticky module
	 * } else {
	 *     // Module is not inside a sticky module
	 *     // Perform actions for modules not inside a sticky module
	 * }
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Example 2: Check if a module is inside a sticky module using module objects from BlockParserStore
	 *
	 * // Given a module ID and the store instance
	 * $module_id = 'example-module';
	 * $store_instance = 'example-store';
	 *
	 * // Get the module objects from BlockParserStore
	 * $module_objects = BlockParserStore::get_all($store_instance);
	 *
	 * // Check if the module is inside a sticky module
	 * $is_inside_sticky_module = self::is_inside_sticky_module($module_id, $module_objects);
	 *
	 * if ($is_inside_sticky_module) {
	 *     // Module is inside a sticky module
	 *     // Perform actions for modules inside a sticky module
	 * } else {
	 *     // Module is not inside a sticky module
	 *     // Perform actions for modules not inside a sticky module
	 * }
	 * ```
	 */
	public static function is_inside_sticky_module( string $id, array $layout_state ): bool {
		// Unlike VB, ancestor id is generated when module is being added into BlockParserStore
		// because it won't change like VB thus it can be simply retrieved.
		$ancestors = [];
		$parent_id = $layout_state[ $id ]->parentId ?? null;

		while ( $parent_id ) {
			$ancestors[] = $parent_id;
			$parent_id   = $layout_state[ $parent_id ]->parentId ?? null;
		}

		$is_inside_sticky = false;

		foreach ( $ancestors as $ancestor ) {
			$module_attrs = isset( $layout_state[ $ancestor ] ) ? $layout_state[ $ancestor ]->get_merged_attrs() : [];
			// TODO feat(D5, ModuleAttributeRefactor) Remove this when all modules are migrated to new format and get values from module->options.
			if ( ! empty( $module_attrs['module']['decoration'] ) ) {
				$module_attrs = $module_attrs['module']['decoration'] ?? [];
			}

			if ( self::is_sticky_element(
				[
					'attr'           => $module_attrs['sticky'] ?? [],
					'affectingAttrs' => [
						'position' => $module_attrs['position'] ?? [],
						'scroll'   => $module_attrs['scroll'] ?? [],
					],
				]
			) ) {
				$is_inside_sticky = true;

				break;
			}
		}

		return $is_inside_sticky;
	}

	/**
	 * Check if given element (module, module element) is a sticky element.
	 *
	 * This function determines if a given element has the attribute 'sticky' defined as true.
	 * A sticky element is marked by valid sticky position. However if the element has incompatible
	 * attribute (position or scroll), the sticky element will be disabled.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attr           The attributes of the element. Default `[]`.
	 *     @type array $affectingAttrs Attributes that can affect the sticky behavior of the element. Default `[]`.
	 * }
	 *
	 * @return bool `true` if the element is sticky, `false` otherwise.
	 *
	 * @example:
	 * ```php
	 *     self::is_sticky_element( [
	 *         'attr' => [
	 *             [ 'value' => [ 'position' => 'sticky' ] ],
	 *             [ 'value' => [ 'position' => 'fixed' ] ],
	 *         ],
	 *         'affectingAttrs' => [
	 *             'position' => [ 'hidden' ],
	 *             'scroll'   => [ 'visible' ],
	 *         ],
	 *     ] );
	 * ```
	 */
	public static function is_sticky_element( array $args ): bool {
		$args = wp_parse_args(
			$args,
			[
				'attr'           => [],
				'affectingAttrs' => [],
			]
		);

		// Immediately return false if attrs.sticky is undefined.
		if ( empty( $args['attr'] ) ) {
			return false;
		}

		// Bail if there is fields which its selected value are incompatible to sticky mechanism.
		if ( self::has_incompatible_attrs( $args['affectingAttrs'] ) ) {
			return false;
		}

		// Evaluate sticky status.
		$is_sticky = false;

		foreach ( $args['attr'] as $state_value ) {
			if ( in_array( $state_value['value']['position'] ?? null, self::get_valid_sticky_position(), true ) ) {
				$is_sticky = true;

				break;
			}
		}

		return $is_sticky;
	}

	/**
	 * Check if module with given id is sticky or inside a sticky module.
	 *
	 * This function checks if a module is either sticky or nested inside a sticky module.
	 * It returns `true` if the module is sticky.
	 * If the module  is  inside a sticky module, then `false` is returned.
	 *
	 * @since ??
	 *
	 * @param string             $id           The ID of the module.
	 * @param BlockParserBlock[] $layout_state The layout state containing module attributes.
	 * @param bool               $no_nested_sticky Optional. If `true`, the function will return `false` if the module is inside a sticky module.
	 *
	 * @return bool Returns `true` if the module is sticky.
	 *              If the module is  inside a sticky module, or is not sticky `false` is returned.
	 *
	 * @example:
	 * ```php
	 * $is_sticky = self::is_sticky_module( 'module1', $layout_state );
	 * if ( $is_sticky ) {
	 *     // Perform actions for sticky module or inside a sticky module.
	 * } else {
	 *     // Perform actions for non-sticky module.
	 * }
	 * ```
	 *
	 * @example:
	 * ```php
	 * $module_id = 'module2';
	 * $module_objects = BlockParserStore::get_all( $storeInstance );
	 * $is_sticky = self::is_sticky_module( $module_id, $module_objects );
	 * if ( $is_sticky ) {
	 *     // Perform actions for sticky module or inside a sticky module.
	 * } else {
	 *     // Perform actions for non-sticky module.
	 * }
	 * ```
	 */
	public static function is_sticky_module( string $id, array $layout_state, bool $no_nested_sticky = true ): bool {
		$module_attrs = isset( $layout_state[ $id ] ) ? $layout_state[ $id ]->get_merged_attrs() : [];
		// TODO feat(D5, ModuleAttributeRefactor) Remove this when all modules are migrated to new format and get values from module->options.
		if ( ! empty( $module_attrs['module']['decoration'] ) ) {
			$module_attrs = $module_attrs['module']['decoration'] ?? [];
		}

		$is_sticky = self::is_sticky_element(
			[
				'attr'           => $module_attrs['sticky'] ?? [],
				'affectingAttrs' => [
					'position' => $module_attrs['position'] ?? [],
					'scroll'   => $module_attrs['scroll'] ?? [],
				],
			]
		);

		// If the module is sticky, check if it is inside another sticky module.
		// If it is, return false.
		if ( $is_sticky && $no_nested_sticky ) {
			$is_inside_sticky = self::is_inside_sticky_module( $id, $layout_state );

			// No nested sticky element.
			if ( $is_inside_sticky ) {
				return false;
			}
		}

		return $is_sticky;
	}

}
