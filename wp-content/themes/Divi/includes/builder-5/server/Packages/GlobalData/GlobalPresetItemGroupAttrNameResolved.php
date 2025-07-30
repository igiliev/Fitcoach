<?php
/**
 * GlobalPresetItemGroupAttrNameResolved class.
 *
 * @package ET\Builder\Packages\GlobalData
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class GlobalPresetItemGroupAttrNameResolved
 *
 * @package ET\Builder\Packages\GlobalData
 */
class GlobalPresetItemGroupAttrNameResolved {

	/**
	 * Attribute name.
	 *
	 * @var string
	 */
	private $_attr_name = null;

	/**
	 * Attribute sub name.
	 *
	 * @var string|null
	 */
	private $_attr_sub_name = null;

	/**
	 * Attribute callback.
	 *
	 * @var callable|null
	 */
	private $_attr_callback = null;

	/**
	 * Property path callback.
	 *
	 * @var callable|null
	 */
	private $_property_path_callback = null;

	/**
	 * Constructor for the GlobalPresetItemGroupAttrNameResolved class.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An associative array of arguments.
	 *
	 *     @type string   $attrName              The attribute name.
	 *     @type string   $attrSubName           The attribute sub-name.
	 *     @type callable $attrCallback          A callback function for the attribute.
	 *     @type callable $propertyPathCallback  A callback function for the property path.
	 * }
	 */
	public function __construct( array $args ) {
		if ( isset( $args['attrName'] ) ) {
			$this->_attr_name = $args['attrName'];
		}

		if ( isset( $args['attrSubName'] ) ) {
			$this->_attr_sub_name = $args['attrSubName'];
		}

		if ( isset( $args['attrCallback'] ) ) {
			$this->_attr_callback = $args['attrCallback'];
		}

		if ( isset( $args['propertyPathCallback'] ) ) {
			$this->_property_path_callback = $args['propertyPathCallback'];
		}
	}

	/**
	 * Retrieves the attribute name.
	 *
	 * @since ??
	 *
	 * @return string The resolved attribute name.
	 */
	public function get_attr_name():string {
		return $this->_attr_name;
	}

	/**
	 * Retrieves the attribute sub-name.
	 *
	 * @since ??
	 *
	 * @return string|null The attribute sub-name, or null if not set.
	 */
	public function get_attr_sub_name():?string {
		return $this->_attr_sub_name;
	}

	/**
	 * Retrieves the attribute callback function.
	 *
	 * @since ??
	 *
	 * @return callable|null The callback function if set, or null otherwise.
	 */
	public function get_attr_callback():?callable {
		return $this->_attr_callback;
	}

	/**
	 * Retrieves the callback function for resolving the property path.
	 *
	 * @since ??
	 *
	 * @return callable|null The callback function if set, or null otherwise.
	 */
	public function get_property_path_callback():?callable {
		return $this->_property_path_callback;
	}
}
