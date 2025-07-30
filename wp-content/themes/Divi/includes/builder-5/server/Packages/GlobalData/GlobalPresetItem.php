<?php
/**
 * GlobalPresetItem class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\GlobalData\GlobalPresetItemUtils;

/**
 * GlobalPresetItem class.
 *
 * @since ??
 */
class GlobalPresetItem {

	/**
	 * The preset data.
	 *
	 * @var array
	 */
	protected $_data = [];

	/**
	 * Flag weather the preset has data attrs or not.
	 *
	 * @var bool
	 */
	protected $_has_data_attrs;

	/**
	 * Is the preset as default.
	 *
	 * This is used as flag to generate the class name for the preset
	 * wether it is default or not.
	 *
	 * @since ??
	 *
	 * @var bool
	 */
	protected $_as_default = false;

	/**
	 * Flag weather the preset is exist or not in the database.
	 *
	 * @var mixed
	 */
	protected $_is_exist;

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
	 *         @type string $moduleName  The name of the module.
	 *         @type array  $attrs       Attributes data.
	 *         @type array  $renderAttrs Render attributes data.
	 *         @type array  $styleAttrs  Style attributes data.
	 *         @type string $id          The ID of the data.
	 *         @type string $name        The name of the data.
	 *         @type int    $created     The creation timestamp.
	 *         @type int    $updated     The update timestamp.
	 *         @type string $version     The version of the data.
	 *     }
	 *     @type array  $defaultPrintedStyleAttrs Default printed style attributes.
	 *     @type bool   $asDefault                Whether this is set as default.
	 *     @type bool   $isExist                  Whether the preset is exist or not.
	 * }
	 */
	public function __construct( array $args ) {
		$this->_is_exist = $args['isExist'];

		if ( $this->_is_exist ) {
			$this->_init_data( $args['data'] ?? [] );
			$this->_as_default = $args['asDefault'] ?? false;
		}
	}

	/**
	 * Initializes the data array by processing each key-value pair.
	 *
	 * @since ??
	 *
	 * @param array $data The data array to be initialized.
	 */
	protected function _init_data( array $data ) {
		$this->_data = $data;
	}

	/**
	 * Retrieves the data type of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data type of the preset item.
	 */
	public function get_data_type():string {
		return $this->_data['type'] ?? '';
	}

	/**
	 * Retrieves the data ID of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data ID of the preset item.
	 */
	public function get_data_id(): string {
		return $this->_data['id'] ?? '';
	}

	/**
	 * Retrieves the data name of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data name of the preset item.
	 */
	public function get_data_name(): string {
		return $this->_data['name'] ?? '';
	}

	/**
	 * Retrieves the timestamp of when the data was created.
	 *
	 * @since ??
	 *
	 * @return int The timestamp of data creation.
	 */
	public function get_data_created(): int {
		return $this->_data['created'] ?? 0;
	}

	/**
	 * Retrieves the timestamp of when the data was last updated.
	 *
	 * @since ??
	 *
	 * @return int The timestamp of data update.
	 */
	public function get_data_updated(): int {
		return $this->_data['updated'] ?? 0;
	}

	/**
	 * Retrieves the data version of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data version of the preset item.
	 */
	public function get_data_version(): string {
		return $this->_data['version'] ?? '';
	}

	/**
	 * Retrieves the data module name of the preset item.
	 *
	 * @since ??
	 *
	 * @return string The data module name of the preset item.
	 */
	public function get_data_module_name(): string {
		return $this->_data['moduleName'] ?? '';
	}

	/**
	 * Retrieves the data attributes.
	 *
	 *  @since ??
	 *
	 * @return array The data attributes.
	 */
	public function get_data_attrs(): array {
		return $this->_data['attrs'] ?? [];
	}

	/**
	 * Get the render attrs.
	 *
	 * @since ??
	 *
	 * @return array The render attrs of the preset.
	 */
	public function get_data_render_attrs(): array {
		return $this->_data['renderAttrs'] ?? [];
	}

	/**
	 * Retrieves the data style attributes.
	 *
	 * @since ??
	 *
	 * @return array The data style attributes.
	 */
	public function get_data_style_attrs(): array {
		return $this->_data['styleAttrs'] ?? [];
	}

	/**
	 * Check the preset has attrs.
	 *
	 * @since ??
	 *
	 * @return bool True if the preset has attrs, false otherwise.
	 */
	public function has_data_attrs(): bool {
		// If the preset is not exist, bail early and return false.
		if ( ! $this->_is_exist ) {
			return false;
		}

		if ( ! is_bool( $this->_has_data_attrs ) ) {
			// Get the data attrs.
			$attrs = $this->get_data_attrs();

			// Set the flag.
			$this->_has_data_attrs = ! empty( $attrs );
		}

		return $this->_has_data_attrs;
	}

	/**
	 * Check the preset is used as default or not.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public function as_default() {
		return $this->_as_default;
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
				'presetModuleName' => $this->get_data_module_name(),
				'presetId'         => $this->as_default() ? 'default' : $this->get_data_id(),
			]
		);
	}

	/**
	 * Check the preset is exist or not.
	 *
	 * @since ??
	 *
	 * @return bool True if the preset is exist, false otherwise.
	 */
	public function is_exist(): bool {
		return $this->_is_exist;
	}
}
