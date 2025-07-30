<?php
/**
 * ArrayUtility::diff()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility\ArrayUtilityTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\ArrayUtility;

trait DiffTrait {

	/**
	 * Find difference between two arrays and return an array differences between those arrays.
	 *
	 * This function can handle both sequential and associative arrays. If both arrays are sequential, it will use
	 * the `array_diff()` function. If array1 is multidimensional array, it will recursively
	 * compare array1 against array2.
	 *
	 * @since ??
	 *
	 * @param array $array1 The array to compare from.
	 * @param array $array2 Arrays to compare against.
	 *
	 * @return array containing all the entries from $array1 that are not present in any of the other arrays.
	 * Keys in the $array1 array are preserved.
	 */
	public static function diff( array $array1, array $array2 ): array {
		// If both arrays are sequential, use array_diff() function.
		if ( ArrayUtility::is_list( $array1 ) && ArrayUtility::is_list( $array2 ) ) {
			return array_diff( $array1, $array2 );
		}

		$difference = [];

		foreach ( $array1 as $key => $value ) {
			if ( is_array( $value ) && isset( $array2[ $key ] ) && is_array( $array2[ $key ] ) ) {
				$recursive_diff = self::diff( $value, $array2[ $key ] );
				if ( count( $recursive_diff ) > 0 ) {
					$difference[ $key ] = $recursive_diff;
				}
			} else {
				if ( ! array_key_exists( $key, $array2 ) || $array2[ $key ] !== $value ) {
					$difference[ $key ] = $value;
				}
			}
		}

		return $difference;
	}

}
