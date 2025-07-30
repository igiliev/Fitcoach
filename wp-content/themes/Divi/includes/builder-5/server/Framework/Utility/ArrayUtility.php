<?php
/**
 * ArrayUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ArrayUtility class.
 *
 * This class has helper methods to make working with arrays easier.
 *
 * @since ??
 */
class ArrayUtility {

	use ArrayUtilityTraits\GetValueTrait;
	use ArrayUtilityTraits\FindTrait;
	use ArrayUtilityTraits\DiffTrait;
	use ArrayUtilityTraits\IsListTrait;
	use ArrayUtilityTraits\IsAssocTrait;
	use ArrayUtilityTraits\FilterDeepTrait;
	use ArrayUtilityTraits\MapDeepTrait;

	/**
	 * Checks if a given variable is an array of strings.
	 *
	 * This function iterates over each element of the provided variable and verifies
	 * if it's a string. If any element is not a string the function returns `false`.
	 *
	 * @param mixed $var The variable to check.
	 *
	 * @return bool `true` if the variable is an array of strings, `false` otherwise.
	 */
	public static function is_array_of_strings( $var ) {
		if ( ! is_array( $var ) ) {
			return false;
		}

		foreach ( $var as $value ) {
			if ( ! is_string( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Sets a value at a given path in a nested PHP array without using references.
	 *
	 * @since ??
	 *
	 * @param array $array The array to modify.
	 * @param array $path The path as a dot-separated string or an array of keys.
	 * @param mixed $value The value to set.
	 * @return array The modified array.
	 */
	public static function set_value( array $array, array $path, $value ):array {
		$result  = $array;
		$current = &$result;

		foreach ( $path as $key ) {
			if ( ! isset( $current[ $key ] ) || ! is_array( $current[ $key ] ) ) {
				$current[ $key ] = [];
			}

			$current = &$current[ $key ];
		}

		$current = $value;

		return $result;
	}

	/**
	 * Checks if path is a direct property of an array.
	 *
	 * @since ??
	 *
	 * @param array $data The array to check.
	 * @param array $path The path to check.
	 *
	 * @return bool Returns true if path exists, else false.
	 */
	public static function has( array $data, array $path ) {
		foreach ( $path as $key ) {
			if ( is_array( $data ) && array_key_exists( $key, $data ) ) {
				$data = $data[ $key ];
			} else {
				return false;
			}
		}

		return true;
	}
}
