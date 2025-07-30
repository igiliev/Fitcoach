<?php
/**
 * Settings' overflow helper methods.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Framework\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\ArrayUtility;

/**
 * Settings' overflow helper methods.
 *
 * @internal This class is equivalent of Divi 4's `ET_Builder_Module_Helper_Overflow` class with some adjustment.
 * In Divi 4, ET_Builder_Module_Helper_Overflow is also used for handling module's Overflow Options. However
 * attribute structure in Divi 5 is different and this is no longer used for handling module's Overflow Options.
 * Thus it is being located inside `ET\Builder\Framework\Settings` because it is only being used for Settings in Divi 5.
 *
 * @since ??
 */
class Overflow {
	const OVERFLOW_DEFAULT = '';
	const OVERFLOW_VISIBLE = 'visible';
	const OVERFLOW_HIDDEN  = 'hidden';
	const OVERFLOW_SCROLL  = 'scroll';
	const OVERFLOW_AUTO    = 'auto';

	/**
	 * Returns overflow settings X axis field
	 *
	 * @param string $prefix Field prefix.
	 *
	 * @return string
	 */
	public static function get_field_x( $prefix = '' ) {
		return $prefix . 'overflow-x';
	}

	/**
	 * Returns overflow settings Y axis field
	 *
	 * @param string $prefix Field prefix.
	 *
	 * @return string
	 */
	public static function get_field_y( $prefix = '' ) {
		return $prefix . 'overflow-y';
	}

	/**
	 * Return overflow X axis value
	 *
	 * @param array  $props Value property.
	 * @param mixed  $default Default value.
	 * @param string $prefix Field prefix.
	 *
	 * @return string
	 */
	public static function get_value_x( $props, $default = null, $prefix = '' ) {
		return ArrayUtility::get_value( $props, self::get_field_x( $prefix ), $default );
	}

	/**
	 * Return overflow Y axis value
	 *
	 * @param array  $props Value property.
	 * @param mixed  $default Default value.
	 * @param string $prefix Field prefix.
	 *
	 * @return string
	 */
	public static function get_value_y( $props, $default = null, $prefix = '' ) {
		return ArrayUtility::get_value( $props, self::get_field_y( $prefix ), $default );
	}

	/**
	 * Returns overflow valid values
	 *
	 * @return array
	 */
	public static function get_overflow_values() {
		return array(
			self::OVERFLOW_DEFAULT,
			self::OVERFLOW_VISIBLE,
			self::OVERFLOW_HIDDEN,
			self::OVERFLOW_AUTO,
			self::OVERFLOW_SCROLL,
		);
	}
}
