<?php
/**
 * GlobalPresetItemGroup class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\GlobalData\GlobalPresetItem;
use ET\Builder\Packages\GlobalData\GlobalPresetItemUtils;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupUtils;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupAttrNameResolver;
use ET\Builder\Packages\GlobalData\GlobalPresetItemGroupAttrNameResolved;
use ET\Builder\Framework\Utility\ArrayUtility;
use InvalidArgumentException;

/**
 * GlobalPresetItemGroup class.
 *
 * @since ??
 */
class GlobalPresetItemGroup extends GlobalPresetItem {

	/**
	 * The module name where the preset item being used.
	 *
	 * @var string
	 */
	protected $_module_name;

	/**
	 * The group ID where the preset item being used.
	 *
	 * @var string
	 */
	protected $_group_id;

	/**
	 * Constructor for the GlobalPresetItem class.
	 *
	 * @param array $args {
	 *     Array of arguments.
	 *
	 *     @type array $data {
	 *         Data array.
	 *
	 *         @type string $type        The type of data (e.g., 'module', 'group').
	 *         @type array  $attrs       Attributes data.
	 *         @type array  $renderAttrs Render attributes data.
	 *         @type array  $styleAttrs  Style attributes data.
	 *         @type string $id          The ID of the data.
	 *         @type string $name        The name of the data.
	 *         @type int    $created     The creation timestamp.
	 *         @type int    $updated     The update timestamp.
	 *         @type string $version     The version of the data.
	 *         @type string $groupName   The name of the group.
	 *         @type string $groupId     The ID of the group.
	 *         @type string $moduleName  The name of the module.
	 *     }
	 *     @type array  $defaultPrintedStyleAttrs Default printed style attributes.
	 *     @type bool   $asDefault                Whether this is set as default.
	 *     @type bool   $isExist                  Whether the preset is exist or not.
	 *     @type string $moduleName               The module name where the preset item being used.
	 *     @type string $groupId                  The group ID where the preset item being used.
	 * }
	 *
	 * @throws InvalidArgumentException If the `moduleName` argument is not provided.
	 * @throws InvalidArgumentException If the `groupId` argument is not provided.
	 */
	public function __construct( array $args ) {
		parent::__construct( $args );

		if ( $this->_is_exist ) {
			$this->_module_name = $args['moduleName'] ?? null;

			if ( ! $this->_module_name ) {
				throw new InvalidArgumentException( 'The `moduleName` argument is required.' );
			}

			$this->_group_id = $args['groupId'] ?? null;

			if ( ! $this->_group_id ) {
				throw new InvalidArgumentException( 'The `groupId` argument is required.' );
			}
		}
	}

	/**
	 * Retrieves the data group name of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data group name of the preset item.
	 */
	public function get_data_group_name(): string {
		return $this->_data['groupName'] ?? '';
	}

	/**
	 * Retrieves the data group ID of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data group ID of the preset item.
	 */
	public function get_data_group_id(): string {
		return $this->_data['groupId'] ?? '';
	}

	/**
	 * Retrieves the data primary attribute name of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data primary attribute name of the preset item.
	 */
	public function get_data_primary_attr_name(): string {
		return $this->_data['primaryAttrName'] ?? '';
	}

	/**
	 * Resolves the attribute name for a global preset item group.
	 *
	 * This method generates a resolved attribute name by utilizing the
	 * `GlobalPresetItemGroupAttrNameResolver` class. It combines the provided
	 * attribute name with module and group-specific data to produce the final
	 * resolved name.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The original attribute name to be resolved.
	 *
	 * @return GlobalPresetItemGroupAttrNameResolved The resolved attribute name.
	 */
	public function get_resolved_attr_name( string $attr_name ):GlobalPresetItemGroupAttrNameResolved {
		$module_name            = $this->get_module_name();
		$group_id               = $this->get_group_id();
		$data_module_name       = $this->get_data_module_name();
		$data_group_id          = $this->get_data_group_id();
		$data_primary_attr_name = $this->get_data_primary_attr_name() ?? '';

		return GlobalPresetItemGroupAttrNameResolver::get_attr_name(
			$attr_name,
			$module_name,
			$group_id,
			$data_module_name,
			$data_group_id,
			$data_primary_attr_name
		);
	}

	/**
	 * Matches and processes attributes based on group IDs.
	 *
	 * This function checks if the provided attributes array matches certain conditions
	 * based on the data group ID and group ID. If the conditions are met, it processes
	 * and returns the attributes accordingly. Otherwise, it applies a filter to the
	 * attributes and returns the result.
	 *
	 * @since ??
	 *
	 * @param string $attrs_key The attribute key to be matched.
	 *
	 * @return array The processed attributes array.
	 */
	private function _attrs_resolver( string $attrs_key ): array {
		if ( ! $this->is_exist() ) {
			return [];
		}

		$attrs = $this->_data[ $attrs_key ] ?? [];

		if ( ! $attrs ) {
			return $attrs;
		}

		$module_name      = $this->get_module_name();
		$group_id         = $this->get_group_id();
		$data_module_name = $this->get_data_module_name();
		$data_group_id    = $this->get_data_group_id();

		$attr_names = strpos( $group_id, '.' )
			? [ $group_id ]
			: GlobalPresetItemGroupAttrNameResolver::get_attr_names_by_group(
				$module_name,
				$group_id
			);

		if ( $attr_names ) {
			if ( $data_module_name === $module_name && $data_group_id === $group_id ) {
				return array_reduce(
					$attr_names,
					function ( array $accumulator, string $attr_name ) use ( $attrs ): array {
						$property_path = explode( '.', $attr_name );
						$attr          = ArrayUtility::get_value_by_array_path( $attrs, $property_path );

						return GlobalPresetItemGroupUtils::maybe_set_attrs(
							[
								'attr'         => $attr,
								'propertyPath' => $property_path,
								'accumulator'  => $accumulator,
							]
						);
					},
					[]
				);
			}

			return array_reduce(
				$attr_names,
				function ( $accumulator, $attr_name ) use (
					$attrs
				) {
					$attr_name_resolved = $this->get_resolved_attr_name( $attr_name );

					if ( null !== $attr_name_resolved->get_attr_callback() ) {
						$attr = call_user_func( $attr_name_resolved->get_attr_callback(), $attrs );
					} else {
						$attr = ArrayUtility::get_value_by_array_path( $attrs, explode( '.', $attr_name_resolved->get_attr_name() ) );
					}

					if ( null !== $attr_name_resolved->get_property_path_callback() ) {
						$property_path = call_user_func( $attr_name_resolved->get_property_path_callback() );
					} else {
						$property_path = explode( '.', $attr_name );
					}

					return GlobalPresetItemGroupUtils::maybe_set_attrs(
						[
							'attr'         => $attr,
							'propertyPath' => $property_path,
							'accumulator'  => $accumulator,
						]
					);
				},
				[]
			);
		}

		$property_path = explode( '.', $attrs_key );
		$attr          = ArrayUtility::get_value_by_array_path( $attrs, $property_path );

		return GlobalPresetItemGroupUtils::maybe_set_attrs(
			[
				'attr'         => $attr,
				'propertyPath' => $property_path,
				'accumulator'  => [],
			]
		);
	}

	/**
	 * Retrieves the data attributes.
	 *
	 *  @since ??
	 *
	 * @return array The data attributes.
	 */
	public function get_data_attrs(): array {
		return $this->_attrs_resolver( 'attrs' );
	}

	/**
	 * Get the render attrs.
	 *
	 * @since ??
	 *
	 * @return array The render attrs of the preset.
	 */
	public function get_data_render_attrs(): array {
		return $this->_attrs_resolver( 'renderAttrs' );
	}

	/**
	 * Retrieves the data style attributes.
	 *
	 * @since ??
	 *
	 * @return array The data style attributes.
	 */
	public function get_data_style_attrs(): array {
		return $this->_attrs_resolver( 'styleAttrs' );
	}

	/**
	 * Retrieves the module name where the preset item being used.
	 *
	 * @since ??
	 *
	 * @return string The module name where the preset item being used.
	 */
	public function get_module_name(): string {
		return $this->_module_name;
	}

	/**
	 * Retrieves the group ID where the preset item being used.
	 *
	 * @since ??
	 *
	 * @return string The group ID where the preset item being used.
	 */
	public function get_group_id(): string {
		return $this->_group_id;
	}

	/**
	 * Get the selector class name.
	 *
	 * @since ??
	 *
	 * @return string The selector class name.
	 */
	public function get_selector_class_name():string {
		return GlobalPresetItemUtils::generate_preset_class_name(
			[
				'presetType'       => $this->get_data_type(),
				'presetModuleName' => $this->get_module_name(),
				'presetGroupName'  => $this->get_data_group_name(),
				'presetId'         => $this->as_default() ? 'default' : $this->get_data_id(),
			]
		);
	}

	/**
	 * Replaces preset attributes paths with their corresponding values from the provided attributes.
	 *
	 * @since ??
	 *
	 * @param array  $attrs_items The attributes items mapping.
	 * @param string $attrs_key The attribute key to be matched.
	 *
	 * @return array The filtered attributes.
	 */
	public function maybe_replace_attrs_path( array $attrs_items, string $attrs_key ): array {
		$attrs = $this->_data[ $attrs_key ] ?? [];

		if ( ! $attrs ) {
			return $attrs;
		}

		$module_name      = $this->get_module_name();
		$group_id         = $this->get_group_id();
		$data_module_name = $this->get_data_module_name();
		$data_group_id    = $this->get_data_group_id();
		$preset_item      = $this;

		// If the module name and group ID are the same as the data module name and data group ID,
		// it indicates that the preset item is created by the same module and group that being edited.
		// In this case, we can return the attributes as is.
		if ( $data_module_name === $module_name && $data_group_id === $group_id ) {
			return array_reduce(
				array_keys( $attrs_items ),
				function ( array $accumulator, string $attr_name ) use ( $attrs ): array {
					$property_path = explode( '.', $attr_name );
					$attr          = ArrayUtility::get_value_by_array_path( $attrs, $property_path );

					return GlobalPresetItemGroupUtils::maybe_set_attrs(
						[
							'attr'         => $attr,
							'propertyPath' => $property_path,
							'accumulator'  => $accumulator,
						]
					);
				},
				[]
			);
		}

		return array_reduce(
			array_keys( $attrs_items ),
			function ( $accumulator, $attr_name ) use (
				$attrs,
				$attrs_items,
				$preset_item,
				$attrs_key
			) {
				$attrs_item   = $attrs_items[ $attr_name ] ?? [];
				$use_fallback = $attrs_item['useFallback'] ?? true;

				if ( is_callable( $use_fallback ) ) {
					$falback_result = call_user_func(
						$use_fallback,
						[
							'attrsKey'   => $attrs_key,
							'presetItem' => $preset_item,
						]
					);

					if ( $falback_result ) {
						return GlobalPresetItemGroupUtils::maybe_set_attrs(
							[
								'attr'         => $falback_result['attr'],
								'propertyPath' => $falback_result['propertyPath'],
								'accumulator'  => $accumulator,
							]
						);
					}

					return $accumulator;
				}

				if ( true === $use_fallback ) {
					$attr_name_resolved = $preset_item->get_resolved_attr_name( $attr_name );

					$property_path = explode( '.', $attr_name );
					$attr_path     = ( $attr_name_resolved->get_attr_name() === $attr_name ) ? $property_path : explode( '.', $attr_name_resolved->get_attr_name() );
					$attr          = ArrayUtility::get_value_by_array_path( $attrs, $attr_path );

					return GlobalPresetItemGroupUtils::maybe_set_attrs(
						[
							'attr'         => $attr,
							'propertyPath' => $property_path,
							'accumulator'  => $accumulator,
						]
					);
				}

				$property_path = explode( '.', $attr_name );
				$attr          = ArrayUtility::get_value_by_array_path( $attrs, $property_path );

				return GlobalPresetItemGroupUtils::maybe_set_attrs(
					[
						'attr'         => $attr,
						'propertyPath' => $property_path,
						'accumulator'  => $accumulator,
					]
				);
			},
			[]
		);
	}
}
