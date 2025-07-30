<?php
/**
 * REST: GlobalPreset class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\VisualBuilder\Saving\SavingUtility;
use ET\Builder\Packages\GlobalData\GlobalPresetItem;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroup;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use InvalidArgumentException;
use WP_Block_Type;
use ET_Core_PageResource;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * GlobalPreset class.
 *
 * @since ??
 */
class GlobalPreset {

	/**
	 * The data cache.
	 *
	 * @since ??
	 *
	 * @var mixed
	 */
	private static $_data = null;

	/**
	 * Get the option name for the global presets.
	 *
	 * @since ??
	 *
	 * @return string The option name.
	 */
	public static function option_name(): string {
		return 'builder_global_presets_d5';
	}

	/**
	 * Get the option name to check the legacy preset's import check.
	 *
	 * @since ??
	 *
	 * @return string The option name.
	 */
	public static function is_legacy_presets_imported_option_name(): string {
		return 'builder_is_legacy_presets_imported_to_d5';
	}

	/**
	 * Delete the data from the DB.
	 *
	 * @since ??
	 */
	public static function delete_data():void {
		et_delete_option( self::option_name() );

		// Reset the data cache.
		self::$_data = null;
	}

	/**
	 * Get the data from the DB.
	 *
	 * @since ??
	 *
	 * @return array The data from the DB. The array structure is aligns with GlobalData.Presets.Items TS interface.
	 */
	public static function get_data(): array {
		if ( null !== self::$_data ) {
			return self::$_data;
		}

		$data = et_get_option( self::option_name(), [], '', true, false, '', '', true );

		if ( is_array( $data ) ) {
			self::$_data = $data;
			return $data;
		}

		return [];
	}

	/**
	 * Get the data from the DB for legacy presets import check.
	 *
	 * @since ??
	 *
	 * @return string The data from the DB.
	 */
	public static function is_legacy_presets_imported(): string {
		$data = et_get_option( self::is_legacy_presets_imported_option_name(), '', '', true, false, '', '', true );

		return $data;
	}

	/**
	 * Prepare the data to be saved to DB.
	 *
	 * @since ??
	 *
	 * @param array $schema_items The schema items. The array structure is aligns with GlobalData.Presets.RestSchemaItems TS interface.
	 *
	 * @return array Prepared data to be saved to DB. The array structure is aligns with GlobalData.Presets.Items TS interface.
	 */
	public static function prepare_data( array $schema_items ): array {
		$prepared   = [];
		$attrs_keys = [
			'attrs',
			'renderAttrs',
			'styleAttrs',
		];

		foreach ( $schema_items as $preset_type => $schema_item ) {
			if ( ! isset( $prepared[ $preset_type ] ) ) {
				$prepared[ $preset_type ] = [];
			}

			foreach ( $schema_item as $record ) {
				$default = $record['default'];
				$items   = $record['items'];

				foreach ( $items as $item ) {
					if ( 'module' === $preset_type ) {
						$preset_sub_type = $item['moduleName'];
					} elseif ( 'group' === $preset_type ) {
						$preset_sub_type = $item['groupName'];
					}

					if ( ! isset( $prepared[ $preset_type ][ $preset_sub_type ] ) ) {
						$prepared[ $preset_type ][ $preset_sub_type ] = [
							'default' => $default,
							'items'   => [],
						];
					}

					foreach ( $attrs_keys as $key ) {
						if ( isset( $item[ $key ] ) ) {
							$preset_attrs = $item[ $key ];

							if ( ! is_array( $preset_attrs ) ) {
								unset( $item[ $key ] );
								continue;
							}

							$preset_attrs = ModuleUtils::remove_empty_array_attributes( $preset_attrs );

							if ( empty( $preset_attrs ) ) {
								unset( $item[ $key ] );
								continue;
							}

							if ( 'module' === $preset_type ) {
								$item[ $key ] = SavingUtility::sanitize_block_attrs( $preset_attrs, $preset_sub_type );
							} elseif ( 'group' === $preset_type ) {
								$item[ $key ] = SavingUtility::sanitize_group_attrs( $preset_attrs, $preset_sub_type );
							}
						}
					}

					$prepared[ $preset_type ][ $preset_sub_type ]['items'][ $item['id'] ] = $item;
				}
			}
		}

		return $prepared;
	}

	/**
	 * Save the data to DB.
	 *
	 * @since ??
	 *
	 * @param array $data The data to be saved. The array structure is aligns with GlobalData.Presets.Items TS interface.
	 *
	 * @return array The saved data. The array structure is aligns with GlobalData.Presets.Items TS interface.
	 */
	public static function save_data( array $data ): array {
		et_update_option( self::option_name(), $data, false, '', '', true );

		// We need to clear the entire website cache when updating a preset.
		ET_Core_PageResource::remove_static_resources( 'all', 'all', true );

		// Reset the data cache.
		self::$_data = null;

		return self::get_data();
	}

	/**
	 * Save conversion data to DB for legacy presets import check.
	 *
	 * @since ??
	 *
	 * @param bool $data The data to be saved.
	 *
	 * @return void
	 */
	public static function save_is_legacy_presets_imported( bool $data ): void {
		et_update_option( self::is_legacy_presets_imported_option_name(), $data ? 'yes' : '', false, '', '', true );

		// We need to clear the entire website cache when updating a preset.
		ET_Core_PageResource::remove_static_resources( 'all', 'all', true );
	}

	/**
	 * Get the legacy D4 global presets data from the DB for presets format.
	 *
	 * @since ??
	 *
	 * @return array The data from the DB. The array structure is in D4 which needs to be used for converting to D5 format.
	 */
	public static function get_legacy_data(): array {
		static $presets_attributes = null;

		if ( null !== $presets_attributes ) {
			return $presets_attributes;
		}

		$all_builder_presets = et_get_option( 'builder_global_presets_ng', (object) array(), '', true, false, '', '', true );
		$presets_attributes  = array();

		// If there is no global presets then return empty array.
		if ( empty( $all_builder_presets ) ) {
			return $presets_attributes;
		}

		foreach ( $all_builder_presets as $module => $module_presets ) {
			$module_presets = is_array( $module_presets ) ? (object) $module_presets : $module_presets;

			if ( ! is_object( $module_presets ) ) {
				continue;
			}

			foreach ( $module_presets->presets as $key => $value ) {
				if ( empty( (array) $value->settings ) ) {
					continue;
				}

				// Convert preset settings object to array format.
				$value_settings  = json_decode( wp_json_encode( $value->settings ), true );
				$value->settings = (array) $value_settings;
				unset( $value->is_temp );

				$presets_attributes[ $module ]['presets'][ $key ] = (array) $value;
			}

			// Get the default preset id.
			$default_preset_id = $module_presets->default;

			// If presets are available then only set default preset id.
			if ( ! empty( $presets_attributes[ $module ]['presets'] ) ) {
				// Set the default preset id if default preset id is there otherwise set as blank.
				$presets_attributes[ $module ]['default'] = $default_preset_id;
			}
		}

		return $presets_attributes;
	}

	/**
	 * Retrieve the selected preset from a module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $moduleName  The module name.
	 *     @type array  $moduleAttrs The module attributes.
	 *     @type array  $allData     The all data. If not provided, it will be fetched using `GlobalPreset::get_data()`.
	 * }
	 *
	 * @throws InvalidArgumentException If the `moduleName` argument is not provided.
	 * @throws InvalidArgumentException If the `moduleAttrs` argument is not provided.
	 *
	 * @return GlobalPresetItem The selected preset instance.
	 */
	public static function get_selected_preset( array $args ): GlobalPresetItem {
		if ( ! isset( $args['moduleName'] ) ) {
			throw new InvalidArgumentException( 'The `moduleName` argument is required.' );
		}

		if ( ! isset( $args['moduleAttrs'] ) ) {
			throw new InvalidArgumentException( 'The `moduleAttrs` argument is required.' );
		}

		// Extract the arguments.
		$module_name  = $args['moduleName'];
		$module_attrs = $args['moduleAttrs'];
		$all_data     = $args['allData'] ?? self::get_data();

		// Convert the module name to the preset module name.
		$module_name_converted = ModuleUtils::maybe_convert_preset_module_name( $module_name, $module_attrs );

		$default_preset_id    = $all_data['module'][ $module_name_converted ]['default'] ?? '';
		$preset_id            = $module_attrs['modulePreset'] ?? '';
		$preset_id_as_default = self::is_preset_id_as_default( $preset_id, $default_preset_id );

		// If the preset ID is not as default, and the preset ID is found, then use the preset ID.
		if ( ! $preset_id_as_default && isset( $all_data['module'][ $module_name_converted ]['items'][ $preset_id ] ) ) {
			return new GlobalPresetItem(
				[
					'data'      => $all_data['module'][ $module_name_converted ]['items'][ $preset_id ],
					'asDefault' => false,
					'isExist'   => true,
				]
			);
		}

		return new GlobalPresetItem(
			[
				'data'      => $all_data['module'][ $module_name_converted ]['items'][ $default_preset_id ] ?? [],
				'asDefault' => true,
				'isExist'   => isset( $all_data['module'][ $module_name_converted ]['items'][ $default_preset_id ] ),
			]
		);
	}

	/**
	 * Retrieve the preset item.
	 *
	 * This method is used to find the preset item for a module. It will convert the module name to the preset module name if needed.
	 *
	 * @since ??
	 *
	 * @param string $module_name  The module name.
	 * @param array  $module_attrs The module attributes.
	 * @param array  $default_printed_style_attrs The default printed style attributes.
	 *
	 * @return GlobalPresetItem The preset item instance.
	 */
	public static function get_item( string $module_name, array $module_attrs, array $default_printed_style_attrs = [] ): GlobalPresetItem {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes. [https://github.com/elegantthemes/Divi/issues/41805].
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-9', 'GlobalPreset::get_selected_preset' );

		return self::get_selected_preset(
			[
				'moduleName'               => $module_name,
				'moduleAttrs'              => $module_attrs,
				'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
			]
		);
	}

	/**
	 * Retrieve the preset item by ID.
	 *
	 * @since ??
	 *
	 * @param string $module_name The module name. The module name should be already converted to the preset module name.
	 * @param string $preset_id The module attributes. The preset ID should be the actual preset ID.
	 *
	 * @return GlobalPresetItem The preset item instance.
	 */
	public static function get_item_by_id( string $module_name, string $preset_id ): GlobalPresetItem {
		// TODO feat(D5, Deprecated) Create class for handling deprecating functions / methdos / constructor / classes. [https://github.com/elegantthemes/Divi/issues/41805].
		_deprecated_function( __METHOD__, '5.0.0-public-alpha-9', 'GlobalPreset::get_selected_preset' );

		return self::get_selected_preset(
			[
				'moduleName'               => $module_name,
				'moduleAttrs'              => [
					'modulePreset' => $preset_id,
				],
				'defaultPrintedStyleAttrs' => [],
			]
		);
	}

	/**
	 * Process the presets.
	 * This function adapts the logic from its js counterpart `processPresets` from `visual-builder/packages/module-utils/src/process-presets/index.ts`
	 * to use during Readiness migration. This function takes an array of converted D5 presets and processes them by merging them with the existing presets.
	 * It returns the processed presets.
	 *
	 * @param array $presets The array of presets to be processed.
	 * @return array The processed presets.
	 */
	public static function process_presets( $presets ) {
		$processed_presets = [ 'module' => [] ];
		$all_presets       = self::get_data();

		foreach ( $presets['module'] as $module_name => $preset_items ) {
			if ( empty( $module_name ) ) {
				continue;
			}

			$processed_items = [];

			foreach ( $preset_items['items'] as $item_id => $item ) {
				if ( empty( $item ) ) {
					continue;
				}

				$existing_preset_item = $all_presets[ $module_name ]['items'][ $item_id ] ?? null;
				$is_default_preset    = $preset_items['default'] === $item_id;

				$current_timestamp = time();
				$new_id            = ! empty( $existing_preset_item ) ? uniqid() : $item_id;

				$processed_items[ $new_id ] = array_merge(
					$item,
					[
						'id'      => $new_id,
						'name'    => ! empty( $existing_preset_item ) || $is_default_preset ? sprintf( '%s imported', $item['name'] ) : $item['name'],
						'created' => $current_timestamp,
						'updated' => $current_timestamp,
					]
				);
			}

			// Merge processed items with all_presets.
			if ( ! empty( $processed_items ) ) {
				$all_presets[ $module_name ]['items'] = array_merge(
					$all_presets[ $module_name ]['items'] ?? [],
					$processed_items
				);

				$processed_presets['module'][ $module_name ] = [
					'items'   => $all_presets[ $module_name ]['items'],
					'default' => $preset_items['default'],
				];
			}
		}

		return $processed_presets;
	}

	/**
	 * Get default preset ID for a specific preset type.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type string $preset_type     The preset type (module/group).
	 *     @type string $preset_sub_type The preset subtype (module name/group name).
	 * }
	 *
	 * @return string
	 */
	public static function get_default_preset_id( array $args ): string {
		$all_presets = self::get_data();
		$preset_type = $args['preset_type'] ?? 'module';
		$sub_type    = $args['preset_sub_type'] ?? '';

		if ( 'group' === $preset_type ) {
			return $all_presets['group'][ $sub_type ]['default'] ?? '';
		}

		$module_name = ModuleUtils::maybe_convert_preset_module_name(
			$sub_type,
			[]
		);

		return $all_presets['module'][ $module_name ]['default'] ?? '';
	}

	/**
	 * Get preset class name.
	 *
	 * @since ??
	 *
	 * @param string $group_name Group name.
	 * @param string $preset_id Preset ID.
	 * @param string $preset_type Preset type (module/group).
	 *
	 * @return string
	 */
	public static function get_preset_class_name(
		string $group_name,
		string $preset_id,
		string $preset_type = 'group'
	): string {
		$normalized_group  = str_replace( '/', '-', $group_name );
		$normalized_preset = 'default' === $preset_id ? 'default' : $preset_id;

		return sprintf(
			'divi-%s-%s-%s',
			$preset_type,
			$normalized_group,
			$normalized_preset
		);
	}

	/**
	 * Get group preset class names for a module.
	 *
	 * @since ??
	 *
	 * @param array $presets Group presets data.
	 *
	 * @return array
	 */
	public static function get_group_preset_class_name_for_module( array $presets ): array {
		$class_names = [];

		// Early bail if presets are not provided or invalid.
		if ( empty( $presets ) || ! is_array( $presets ) ) {
			return $class_names;
		}

		foreach ( $presets as $group_id => $preset_item ) {
			if ( ! $preset_item instanceof GlobalPresetItemGroup ) {
				continue;
			}

			$group_name = $preset_item->get_data_group_name() ?? '';
			$preset_id  = $preset_item->get_data_id() ?? '';

			// Get default preset ID.
			$default_preset_id = self::get_default_preset_id(
				[
					'preset_type'     => 'group',
					'preset_sub_type' => $group_name,
				]
			);

			// Determine if this is the default preset.
			$is_default = self::is_preset_id_as_default( $preset_id, $default_preset_id ) || ( $preset_item instanceof GlobalPresetItem && $preset_item->as_default() );

			// Generate class name.
			$class_name = ! empty( $group_name ) ? self::get_preset_class_name(
				$group_name,
				$is_default ? 'default' : $preset_id,
				'group'
			) : '';

			// Add class name if not already in list.
			if ( ! in_array( $class_name, $class_names, true ) ) {
				$class_names[] = $class_name;
			}
		}

		return $class_names;
	}

	/**
	 * Retrieve the selected group presets.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $moduleAttrs The module attributes.
	 *     @type string|WP_Block_Type  $moduleData  The module name or configuration data.
	 *     @type array  $allData     The all data. If not provided, it will be fetched using `GlobalPreset::get_data()`.
	 * }
	 *
	 * @throws InvalidArgumentException If the `moduleAttrs` argument is not provided.
	 *
	 * @return array<GlobalPresetItemGroup> The selected group presets.
	 */
	public static function get_selected_group_presets( array $args ): array {
		if ( ! isset( $args['moduleName'] ) ) {
			throw new InvalidArgumentException( 'The `moduleName` argument is required.' );
		}

		if ( ! isset( $args['moduleAttrs'] ) ) {
			throw new InvalidArgumentException( 'The `moduleAttrs` argument is required.' );
		}

		// Extract the arguments.
		$module_name  = $args['moduleName'];
		$module_attrs = $args['moduleAttrs'];
		$all_data     = $args['allData'] ?? self::get_data();

		$selected      = [];
		$module_config = ModuleRegistration::get_module_settings( $module_name );

		// Get default and merged group presets.
		$default_group_preset_attrs = self::get_group_preset_default_attr( $module_config );

		$group_presets = array_merge(
			$default_group_preset_attrs,
			$module_attrs['groupPreset'] ?? []
		);

		foreach ( $group_presets as $group_id => $attr_value ) {
			$group_name           = $attr_value['groupName'];
			$preset_id            = $attr_value['presetId'] ?? '';
			$default_preset_id    = $all_data['group'][ $group_name ]['default'] ?? '';
			$preset_id_as_default = self::is_preset_id_as_default( $preset_id, $default_preset_id );

			if ( ! $preset_id_as_default && isset( $all_data['group'][ $group_name ]['items'][ $preset_id ] ) ) {
				$selected[ $group_id ] = new GlobalPresetItemGroup(
					[
						'data'       => $all_data['group'][ $group_name ]['items'][ $preset_id ],
						'asDefault'  => false,
						'isExist'    => true,
						'groupId'    => $group_id,
						'moduleName' => $args['moduleName'],
					]
				);

				// If the preset ID is found, then continue to the next iteration.
				continue;
			}

			$selected[ $group_id ] = new GlobalPresetItemGroup(
				[
					'data'       => $all_data['group'][ $group_name ]['items'][ $default_preset_id ] ?? [],
					'asDefault'  => true,
					'isExist'    => isset( $all_data['group'][ $group_name ]['items'][ $default_preset_id ] ),
					'groupId'    => $group_id,
					'moduleName' => $args['moduleName'],
				]
			);
		}

		return $selected;
	}

	/**
	 * Get default group preset attributes from module configuration.
	 *
	 * @since ??
	 *
	 * @param string|WP_Block_Type $module_data Module name or configuration object.
	 *
	 * @return array<string, array<string, string>> The default group preset attributes.
	 */
	public static function get_group_preset_default_attr( $module_data ): array {
		static $group_preset_cache = [];

		$module_name = $module_data->name ?? '';

		if ( isset( $group_preset_cache[ $module_name ] ) ) {
			return $group_preset_cache[ $module_name ];
		}

		$default_attrs = [];
		$attributes    = $module_data->attributes ?? [];

		foreach ( $attributes as $attr_name => $attribute ) {
			$settings = $attribute['settings'] ?? [];

			foreach ( [ 'decoration', 'advanced' ] as $attr_type ) {
				$groups = $settings[ $attr_type ] ?? [];

				foreach ( $groups as $group_id => $group_config ) {
					$group_name = '';

					// Handle different group types.
					if ( empty( $group_config ) || ! isset( $group_config['groupType'] ) ) {
						// Empty group or missing groupType.
						$group_name = self::get_default_group_name( $attr_type, $group_id );
					} elseif ( 'group' === $group_config['groupType'] ) {
						$group_name = $group_config['groupName'] ?? self::get_default_group_name( $attr_type, $group_id );

						// Skip if grouped prop is explicitly false.
						if ( isset( $group_config['component']['props']['grouped'] )
							&& false === $group_config['component']['props']['grouped'] ) {
							continue;
						}
					} elseif ( 'group-item' === $group_config['groupType'] ) {
						// Nested group item.
						$item_component = $group_config['item']['component'] ?? [];
						if ( 'group' === ( $item_component['type'] ?? '' )
							&& ( false !== ( $item_component['props']['grouped'] ?? true ) ) ) {
							$group_name = $item_component['name'] ?? '';
						}
					}

					// Final fallback to default name.
					$group_name = $group_name ? self::get_default_group_name( $attr_type, $group_id ) : '';

					if ( ! empty( $group_name ) ) {
						$default_attrs[ "{$attr_name}.{$attr_type}.{$group_id}" ] = [
							'groupName' => $group_name,
						];
					}
				}
			}
		}

		// Process composite groups from module metadata.
		$composite_groups = $module_data->settings['groups'] ?? [];

		foreach ( $composite_groups as $group_id => $group ) {
			if ( isset( $group['component']['name'] ) && 'divi/composite' === $group['component']['name'] ) {
				$preset_group_name = $group['component']['props']['presetGroup'] ?? '';
				if ( ! empty( $preset_group_name ) ) {
					$default_attrs[ $group_id ] = [
						'groupName' => $preset_group_name,
					];
				}
			}
		}

		$group_preset_cache[ $module_name ] = $default_attrs;

		return $default_attrs;
	}

	/**
	 * Get default group name mapping.
	 *
	 * @since ??
	 *
	 * @param string $attr_type Attribute type (decoration/advanced).
	 * @param string $group_id  Group ID.
	 *
	 * @return string
	 */
	public static function get_default_group_name( string $attr_type, string $group_id ): string {
		$group_name_map = [
			'decoration' => [
				'animation'   => 'divi/animation',
				'background'  => 'divi/background',
				'bodyFont'    => 'divi/font-body',
				'border'      => 'divi/border',
				'boxShadow'   => 'divi/box-shadow',
				'button'      => 'divi/button',
				'conditions'  => 'divi/conditions',
				'disabledOn'  => 'divi/disabled-on',
				'filters'     => 'divi/filters',
				'font'        => 'divi/font',
				'headingFont' => 'divi/font-header',
				'overflow'    => 'divi/overflow',
				'position'    => 'divi/position',
				'scroll'      => 'divi/scroll',
				'sizing'      => 'divi/sizing',
				'spacing'     => 'divi/spacing',
				'sticky'      => 'divi/sticky',
				'transform'   => 'divi/transform',
				'transition'  => 'divi/transition',
				'zIndex'      => 'divi/z-index',
			],
			'advanced'   => [
				'htmlAttributes' => 'divi/id-classes',
				'text'           => 'divi/text',
			],
		];

		return $group_name_map[ $attr_type ][ $group_id ] ?? '';
	}

	/**
	 * Checks if the given preset ID is considered as a default preset.
	 *
	 * This function determines if the provided preset ID matches any of the default
	 * preset identifiers: an empty string, 'default', '_initial' (for legacy presets), or equal to the default preset ID.
	 *
	 * @since ??
	 *
	 * @param string $preset_id The preset ID to check.
	 * @param string $default_preset_id The default preset ID.
	 *
	 * @return bool True if the preset ID is a default preset, false otherwise.
	 */
	public static function is_preset_id_as_default( string $preset_id, string $default_preset_id ): bool {
		return '' === $preset_id || 'default' === $preset_id || '_initial' === $preset_id || $default_preset_id === $preset_id;
	}

	/**
	 * Merges module attributes with preset and group preset attributes.
	 *
	 * This method retrieves and merges attributes from a specified module,
	 * its selected preset, and any applicable group presets.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $moduleName  The module name.
	 *     @type array  $moduleAttrs The module attributes.
	 *     @type array  $allData     The all data. If not provided, it will be fetched using `GlobalPreset::get_data()`.
	 * }
	 *
	 * @throws InvalidArgumentException If 'moduleName' or 'moduleAttrs' is not provided.
	 *
	 * @return array The merged attributes array.
	 */
	public static function get_merged_attrs( array $args ): array {
		if ( ! isset( $args['moduleName'] ) ) {
			throw new InvalidArgumentException( 'The `moduleName` argument is required.' );
		}

		if ( ! isset( $args['moduleAttrs'] ) ) {
			throw new InvalidArgumentException( 'The `moduleAttrs` argument is required.' );
		}

		// Extract the arguments.
		$module_name  = $args['moduleName'];
		$module_attrs = $args['moduleAttrs'];

		$module_presets_attrs = [];

		$selected_preset = self::get_selected_preset(
			[
				'moduleName'  => $module_name,
				'moduleAttrs' => $module_attrs,
			]
		);

		if ( $selected_preset->is_exist() ) {
			$module_presets_attrs = $selected_preset->get_data_attrs();
		}

		$group_presets_attrs = [];

		$selected_group_presets = self::get_selected_group_presets(
			[
				'moduleName'  => $module_name,
				'moduleAttrs' => $module_attrs,
			]
		);

		foreach ( $selected_group_presets as $selected_group_preset ) {
			if ( $selected_group_preset->is_exist() ) {
				$group_presets_attrs = array_replace_recursive( $group_presets_attrs, $selected_group_preset->get_data_attrs() );
			}
		}

		$merged_attrs = array_replace_recursive( $module_presets_attrs, $group_presets_attrs, $module_attrs );

		return $merged_attrs;
	}
}
