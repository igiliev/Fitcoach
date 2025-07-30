<?php
/**
 * Module Options: Scroll Effects Utils Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Scroll;

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Transform\Transform;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ScrollEffectsUtils class.
 *
 * This class provides utility methods for working with scroll-effect on modules.
 *
 * @since ??
 */
class ScrollEffectsUtils {

	/**
	 * Scroll Effect default group attributes.
	 *
	 * @since ??
	 *
	 * @var array $scroll_effect_default_group_attr
	 */
	public static $scroll_effect_default_group_attr = [
		'desktop' => [
			'value' => [
				'motionTriggerStart' => 'middle',
				'verticalMotion'     => [
					'enable'   => 'off',
					'viewport' => [
						'bottom' => '0',
						'end'    => '50',
						'start'  => '50',
						'top'    => '100',
					],
					'offset'   => [
						'start' => '4',
						'mid'   => '0',
						'end'   => '-4',
					],
				],
				'horizontalMotion'   => [
					'enable'   => 'off',
					'viewport' => [
						'bottom' => '0',
						'end'    => '50',
						'start'  => '50',
						'top'    => '100',
					],
					'offset'   => [
						'start' => '4',
						'mid'   => '0',
						'end'   => '-4',
					],
				],
				'fade'               => [
					'enable'   => 'off',
					'viewport' => [
						'bottom' => '0',
						'end'    => '50',
						'start'  => '50',
						'top'    => '100',
					],
					'offset'   => [
						'start' => '0',
						'mid'   => '100',
						'end'   => '100',
					],
				],
				'scaling'            => [
					'enable'   => 'off',
					'viewport' => [
						'bottom' => '0',
						'end'    => '50',
						'start'  => '50',
						'top'    => '100',
					],
					'offset'   => [
						'start' => '70',
						'mid'   => '100',
						'end'   => '100',
					],
				],
				'rotating'           => [
					'enable'   => 'off',
					'viewport' => [
						'bottom' => '0',
						'end'    => '50',
						'start'  => '50',
						'top'    => '100',
					],
					'offset'   => [
						'start' => '90',
						'mid'   => '0',
						'end'   => '0',
					],
				],
				'blur'               => [
					'enable'   => 'off',
					'viewport' => [
						'bottom' => '0',
						'end'    => '40',
						'start'  => '60',
						'top'    => '100',
					],
					'offset'   => [
						'start' => '10',
						'mid'   => '0',
						'end'   => '0',
					],
				],
			],
		],
	];

	/**
	 * Scroll Effect resolver map.
	 *
	 * @since ??
	 *
	 * @var array $scroll_effect_resolver_map
	 */
	public static $scroll_effect_resolver_map = [
		'blur'             => 'blur',
		'fade'             => 'opacity',
		'rotating'         => 'rotate',
		'scaling'          => 'scale',
		'horizontalMotion' => 'translateX',
		'verticalMotion'   => 'translateY',
	];

	/**
	 * Retrieves the scroll settings for a module.
	 *
	 * Scroll settings are stored in the module's `decoration` attributes. These settings
	 * are gathered and presented as a JSON object named `diviElementScrollData` that can be
	 * accessed by the frontend as a global variable.
	 *
	 * @param array $args {
	 *     An array of arguments for retrieving scroll settings.
	 *
	 *     @type array               $attr An array of scroll attributes.
	 *     @type string              $id The ID of the module.
	 *     @type string              $selector The CSS selector for the module.
	 *     @type \WP_Block_Type|null $module_config The configuration of the module.
	 *     @type array               $transform Optional. An array of transform attributes.
	 *     @type array               $defaultAttr Optional. An array of default scroll attributes.
	 *     @type bool                $is_child_module Optional. Whether the module is a child module. Default `false`.
	 *     @type array               $parent_script_setting Optional. The parent script setting. Default `[]`.
	 *     @type BlockParserBlock    $module_data The BlockParserBlock instance.
	 * }
	 * @return array The retrieved scroll settings.
	 *
	 * @since ??
	 *
	 * @example How scroll settings are stored in the module's `decoration` attributes:
	 *
	 * ```
	 * <!-- wp:divi/text {
	 *   "module":{
	 *     "decoration":{
	 *       "scroll":{
	 *         "desktop":{
	 *           "value":{
	 *             "horizontalMotion":{
	 *               "enable": "on",
	 *               "viewport":{
	 *                 "bottom":"0",
	 *                 "end":"50",
	 *                 "start":"50",
	 *                 "top":"100"
	 *               },
	 *               "offset":{
	 *                 "start":"-1",
	 *                 "mid":"0",
	 *                 "end":"0"
	 *               }
	 *             },
	 *             "fade":{
	 *               "enable": "on",
	 *               "viewport":{
	 *                 "bottom":"19",
	 *                 "end":"30",
	 *                 "start":"80",
	 *                 "top":"90"
	 *               },
	 *               "offset":{
	 *                 "start":"0",
	 *                 "mid":"100",
	 *                 "end":"0%"
	 *               }
	 *             }
	 *           }
	 *         }
	 *       }
	 *     }
	 *   }
	 * } /-->
	 * ```
	 */
	public static function get_scroll_setting( array $args ): array {
		$args = array_merge(
			[
				'attr'                  => [],
				'defaultAttr'           => [
					'desktop' => [
						'value' => [],
					],
				],
				'id'                    => '',
				'selector'              => '',
				'transform'             => [],
				'is_child_module'       => false,
				'parent_script_setting' => [],
				'module_data'           => null,

				// FE only.
				'storeInstance'         => null,
			],
			$args
		);

		// Prepare defaults for the scroll-effects options.
		$default_attr = array_replace_recursive(
			self::$scroll_effect_default_group_attr,
			$args['defaultAttr']
		);

		/**
		 * Module Block Data.
		 *
		 * @var BlockParserBlock $module_data
		 */
		$module_data = $args['module_data'];

		$grid_motion_enabled = ( $args['is_child_module'] && ! empty( $args['parent_script_setting'] ) );

		// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
		// Immediately return false if $attr is undefined and grid motion is not enabled on the parent of the current module.
		// i.e when the parent module scroll settings are not applied to the child module,
		// which is the module we are currently processing.
		if ( empty( $args['attr'] ) && ( empty( $module_data ) || empty( $module_data->blockName ) ) && ! $grid_motion_enabled ) {
			return [];
		}

		/**
		 * Module Configurations.
		 *
		 * @var \WP_Block_Type $module_config
		 */
		$module_config = \WP_Block_Type_Registry::get_instance()->get_registered( $module_data->blockName );

		$base_class_name = ModuleUtils::get_module_order_class_name_base( $module_data->blockName );
		$order_index     = $module_data->orderIndex;

		// phpcs:enable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

		// Scroll Effects attributes.
		$scroll_effects_options = array(
			'verticalMotion',
			'horizontalMotion',
			'fade',
			'scaling',
			'rotating',
			'blur',
		);

		// Grid modules.
		$grid_modules = array(
			'divi/blog',
			'divi/filterable-portfolio',
			'divi/fullwidth-portfolio',
			'divi/gallery',
			'divi/portfolio',
			'divi/shop',
		);

		$setting     = [];
		$breakpoints = Breakpoint::get_enabled_breakpoint_names();

		// Loop through the scroll effects options to check the enable status.
		foreach ( $scroll_effects_options as $option ) {

			// Loop through all the breakpoints.
			foreach ( $breakpoints as $breakpoint ) {
				// Get user defined module attribute.
				$user_defined_attr_value = ModuleUtils::use_attr_value(
					[
						'attr'         => $args['attr'],
						'defaultValue' => $default_attr['desktop']['value'],
						'breakpoint'   => $breakpoint,
						'state'        => 'value',
						'mode'         => 'getAndInheritAll',
					]
				);

				// See if the effect is enabled based on user defined attribute value. If the value is `undefined` it means
				// user doesn't specifically enables it which could mean either a) user is not enables it, or b) user
				// doesn't enable it but its status later will be inherited by fallback breakpoint / base breakpoint.
				$is_effect_enabled = 'on' === ( $user_defined_attr_value[ $option ]['enable'] ?? null );

				// Merge default attributes to user defined attributes because for scroll option to work, it'll need
				// all properties to be present.
				$attr_value = array_replace_recursive(
					$default_attr['desktop']['value'],
					$user_defined_attr_value
				);

				$option_value           = $attr_value[ $option ];
				$trigger_start          = $attr_value['motionTriggerStart'] ?? 'middle';
				$is_grid_motion_enabled = 'on' === ( $attr_value['gridMotion']['enable'] ?? 'off' );

				if ( $is_effect_enabled ) {
					$item = [
						'id'            => $args['selector'],
						'start'         => self::get_start_limit( $option_value ),
						'midStart'      => self::get_start_middle( $option_value ),
						'midEnd'        => self::get_end_middle( $option_value ),
						'end'           => self::get_end_limit( $option_value ),
						'startValue'    => self::get_start_value( $option_value ),
						'midValue'      => self::get_middle_value( $option_value ),
						'endValue'      => self::get_end_value( $option_value ),
						'resolver'      => self::$scroll_effect_resolver_map[ $option ],
						'trigger_start' => esc_html( $trigger_start ),
						'trigger_end'   => 'middle', // The field does not exists in D4, therefore not available in D5.
					];

					// If the module has transform attributes, include the values.
					if ( $args['transform'] ) {
						$transform_attr_value = ModuleUtils::use_attr_value(
							[
								'attr'       => $args['transform'],
								'breakpoint' => $breakpoint,
								'state'      => 'value',
								'mode'       => 'getAndInheritAll',
							]
						);

						// Set the transforms value in key_value_pair format.
						$item['transforms'] = Transform::value( $transform_attr_value ?? [], 'key_value_pair' );
					}

					// When the current item had grid motion enabled, we need to add the effects to the children items.
					if ( $is_grid_motion_enabled && in_array( $module_config->name, $grid_modules, true ) ) {
						$children_count = self::get_module_children_count( $module_data->get_merged_attrs(), $module_config->name, $breakpoint, 'value' );

						if ( $children_count > 0 ) {
							for ( $j = 0; $j < $children_count; $j++ ) {
								$child_item = $item;

								// Generate child item selectors.
								$child_item['id'] = esc_html(
									sprintf(
										'.%s_item_%s_%s',
										$base_class_name,
										$order_index,
										$j
									)
								);

								$setting[ $breakpoint ][] = $child_item;
							}

							// immediately continue so as not to add the effects to the parent module.
							continue;
						}
					}

					if ( $is_grid_motion_enabled ) {
						$item['grid_motion'] = 'on';
					}

					$setting[ $breakpoint ][] = $item;
				} else {

					// Check if user intentionally disable the effect. This value is derived from user defined attribute value.
					// This could mean either a) user disables the effect entirely, or b) user has the effect enabled on larger
					// smaller breakpoints but want to disables it on the current breakpoint. The later actually requires us
					// to pass item with no configuration as a signal to disables already registered effect.
					$is_intentionally_disabled = 'off' === ( $user_defined_attr_value[ $option ]['enable'] ?? null );

					if ( $is_intentionally_disabled ) {
						$setting[ $breakpoint ][] = [
							'id' => $args['selector'],
						];
					}
				}

				// When parent module has grid motion enabled, we need to include the parent effects.
				// NOTE: This should always run whether the module has effects enabled or not
				// because there are cases where the child module e.g accordion-item does not have scroll effects
				// enabled (!$is_effect_enabled) but grid motion is on so the accordion-item should inherit the accordion's effects.
				if (
					$args['is_child_module']
					&& isset( $args['parent_script_setting'][ $breakpoint ][0]['grid_motion'] )
					&& 'on' === $args['parent_script_setting'][ $breakpoint ][0]['grid_motion']
				) {

					$setting[ $breakpoint ] = array_merge(
						$setting[ $breakpoint ] ?? [],
						$args['parent_script_setting'][ $breakpoint ] ?? []
					);
				}
			}
		}

		// Cleanup.
		unset( $module_data );
		unset( $module_config );
		unset( $parent_data );
		unset( $parent_attr );

		return $setting;
	}

	/**
	 * Retrieves the children count for a grid module.
	 *
	 * @param array  $attrs The module attributes.
	 * @param string $name Name of the module.
	 * @param string $breakpoint The breakpoint.
	 * @param string $state The state.
	 *
	 * @since ??
	 *
	 * @return int Returns the children count for a grid module.
	 */
	public static function get_module_children_count( array $attrs, string $name, string $breakpoint, string $state ): int {
		$value = 0;

		switch ( $name ) {
			case 'divi/blog':
			case 'divi/shop':
				$value = (int) ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs['post']['advanced']['number'] ?? [],
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				return $value < 1 ? 10 : $value;

			case 'divi/gallery':
				$value = (int) ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs['module']['advanced']['postsNumber'] ?? [],
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				return $value < 1 ? 10 : $value;

			case 'divi/filterable-portfolio':
				$value = (int) ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs['portfolio']['advanced']['postsNumber'] ?? [],
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				return $value < 1 ? 10 : $value;

			case 'divi/fullwidth-portfolio':
			case 'divi/portfolio':
				$value = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs['portfolio']['innerContent'] ?? [],
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$posts_number = isset( $value['postsNumber'] ) ? (int) $value['postsNumber'] : 0;

				return $posts_number < 1 ? 10 : $posts_number;

			default:
				break;
		}

		return $value;
	}

	/**
	 * Key for start limit.
	 *
	 * @since ??
	 *
	 * @var string $start_limit_key
	 */
	public static $start_limit_key = 'bottom';

	/**
	 * Key for start middle.
	 *
	 * @since ??
	 *
	 * @var string $start_middle_key
	 */
	public static $start_middle_key = 'end';

	/**
	 * Key for end middle.
	 *
	 * @since ??
	 *
	 * @var string $end_middle_key
	 */
	public static $end_middle_key = 'start';

	/**
	 * Key for start limit.
	 *
	 * @since ??
	 *
	 * @var string $end_limit_key
	 */
	public static $end_limit_key = 'top';

	/**
	 * Key for option start.
	 *
	 * @since ??
	 *
	 * @var string $option_start_key
	 */
	public static $option_start_key = 'start';

	/**
	 * Key for option middle.
	 *
	 * @since ??
	 *
	 * @var string $option_middle_key
	 */
	public static $option_middle_key = 'mid';

	/**
	 * Key for option end.
	 *
	 * @since ??
	 *
	 * @var string $option_end_key
	 */
	public static $option_end_key = 'end';


	/**
	 * Returns scroll effects start limit.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return int
	 */
	public static function get_start_limit( array $value ): int {
		$value  = self::get_sorted_range( $value );
		$result = $value['viewport'][ self::$start_limit_key ];

		return (int) $result;
	}

	/**
	 * Get scroll effects middle start limit.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return int
	 */
	public static function get_start_middle( array $value ): int {
		$value  = self::get_sorted_range( $value );
		$result = $value['viewport'][ self::$start_middle_key ];

		return (int) $result;
	}

	/**
	 * Get scroll effects middle end limit.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return int
	 */
	public static function get_end_middle( array $value ): int {
		$value  = self::get_sorted_range( $value );
		$result = $value['viewport'][ self::$end_middle_key ];

		return (int) $result;
	}

	/**
	 * Returns scroll effects end limit.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return int
	 */
	public static function get_end_limit( array $value ): int {
		$value  = self::get_sorted_range( $value );
		$result = $value['viewport'][ self::$end_limit_key ];

		return (int) $result;
	}


	/**
	 * Returns scroll effects value for start.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return float
	 */
	public static function get_start_value( array $value ): float {
		$result = $value['offset'][ self::$option_start_key ];

		return (float) $result;
	}

	/**
	 * Returns scroll effects value for middle.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return float
	 */
	public static function get_middle_value( array $value ): float {
		$result = $value['offset'][ self::$option_middle_key ];

		return (float) $result;
	}

	/**
	 * Returns scroll effects value for end.
	 *
	 * @since ??
	 *
	 * @param array $value Value.
	 *
	 * @return float
	 */
	public static function get_end_value( array $value ): float {
		$result = $value['offset'][ self::$option_end_key ];

		return (float) $result;
	}

	/**
	 * Sorts and updates the range values of a motion value object.
	 *
	 * @since ??
	 *
	 * @param array $value The value array to update.
	 *
	 * @return array The updated value array.
	 */
	public static function get_sorted_range( array $value ): array {
		static $cache = [];

		// Create a unique key for/using the given arguments.
		$key = md5( wp_json_encode( $value ) );

		// Return cached value if available.
		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}

		$range              = [];
		$start_limit_key_n  = 0;
		$start_middle_key_n = 1;
		$end_middle_key_n   = 2;
		$end_limit_key_n    = 3;

		$range[ $start_limit_key_n ]  = $value['viewport'][ self::$start_limit_key ] ?? 0;
		$range[ $start_middle_key_n ] = $value['viewport'][ self::$start_middle_key ] ?? 50;
		$range[ $end_middle_key_n ]   = $value['viewport'][ self::$end_middle_key ] ?? 50;
		$range[ $end_limit_key_n ]    = $value['viewport'][ self::$end_limit_key ] ?? 100;

		// Sort the range.
		sort( $range, SORT_NUMERIC );

		// Make sure `start >= 0`.
		$range[ $start_limit_key_n ] = max( $range[ $start_limit_key_n ], 0 );

		// Make sure `end <= 100`.
		$range[ $end_limit_key_n ] = min( $range[ $end_limit_key_n ], 100 );

		// Make sure `start middle >= start`.
		$range[ $start_middle_key_n ] = max( $range[ $start_middle_key_n ], $range[ $start_limit_key_n ] );

		// Make sure `end middle <= end`.
		$range[ $end_middle_key_n ] = min( $range[ $end_limit_key_n ], $range[ $end_middle_key_n ] );

		// Prepare the return value.
		$updated_value = $value;

		$updated_value['viewport'][ self::$start_limit_key ]  = $range[ $start_limit_key_n ];
		$updated_value['viewport'][ self::$start_middle_key ] = $range[ $start_middle_key_n ];
		$updated_value['viewport'][ self::$end_middle_key ]   = $range[ $end_middle_key_n ];
		$updated_value['viewport'][ self::$end_limit_key ]    = $range[ $end_limit_key_n ];

		$cache[ $key ] = $updated_value;

		return $updated_value;
	}

	/**
	 * Check if module with given id has any scroll effects enabled.
	 *
	 * If $is_child_module is true and $parent_script_setting is not empty,
	 * it will be considered as scroll effects enabled since this means
	 * that the child module is inheriting the parent's effects.
	 *
	 * @param string             $id The ID of the module.
	 * @param BlockParserBlock[] $layout_state The layout state containing module attributes.
	 * @param bool               $is_child_module Whether the module is a child module.
	 * @param array              $parent_script_setting The parent script setting.
	 *
	 * @return bool Returns `true` if the module has any scroll effects enabled.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $is_scroll_effects_enabled = self::is_scroll_effects_enabled( 'module1', $layout_state );
	 * if ( $is_scroll_effects_enabled ) {
	 *     // Perform actions for scroll effects.
	 * }
	 * ```
	 *
	 * @example:
	 * ```php
	 * $module_id = 'module2';
	 * $layout_state = BlockParserStore::get_all( $storeInstance );
	 * $is_scroll_effects_enabled = self::is_scroll_effects_enabled( $module_id, $layout_state );
	 * if ( $is_scroll_effects_enabled ) {
	 *     // Perform actions for scroll effects.
	 * }
	 * ```
	 */
	public static function is_scroll_effects_enabled( string $id, array $layout_state, bool $is_child_module, array $parent_script_setting ): bool {
		$module_attrs = isset( $layout_state[ $id ] ) ? $layout_state[ $id ]->get_merged_attrs() : [];

		// TODO feat(D5, ModuleAttributeRefactor) Remove this when all modules are migrated to new format and get values from module->options.
		if ( ! empty( $module_attrs['module']['decoration'] ) ) {
			$module_attrs = $module_attrs['module']['decoration'] ?? [];
		}

		$attr = $module_attrs['scroll'] ?? [];

		// Immediately return when $attr is empty and there are no parent script settings to inherit.
		if ( empty( $attr ) && ( $is_child_module && empty( $parent_script_setting ) ) ) {
			return false;
		}

		// Evaluate scroll option status.
		$is_scroll_effects_enabled = false;

		// Scroll Effects attributes.
		$scroll_effects_options = array(
			'verticalMotion',
			'horizontalMotion',
			'fade',
			'scaling',
			'rotating',
			'blur',
		);

		$default_attr = self::$scroll_effect_default_group_attr;

		// Loop through the breakpoints that are available in the attributes.
		foreach ( array_keys( $attr ) as $breakpoint ) {

			// Get the values for the breakpoint.
			$attr_value = ModuleUtils::use_attr_value(
				[
					'attr'         => $attr,
					'defaultValue' => $default_attr['desktop']['value'],
					'breakpoint'   => $breakpoint,
					'state'        => 'value',
					'mode'         => 'getAndInheritAll',
				]
			);

			// Loop through the scroll effects options to check the enable status.
			foreach ( $scroll_effects_options as $option ) {
				if ( isset( $attr_value[ $option ]['enable'] ) && 'on' === $attr_value[ $option ]['enable'] ) {
					$is_scroll_effects_enabled = true;

					break;
				}
			}

			// Break out of loop whem $is_scroll_effects_enabled is true.
			if ( $is_scroll_effects_enabled ) {
				break;
			}
		}

		// Return true if scroll effects are enabled or if the module is a child module and has parent script settings.
		return $is_scroll_effects_enabled || ( $is_child_module && ! empty( $parent_script_setting ) );
	}
}
