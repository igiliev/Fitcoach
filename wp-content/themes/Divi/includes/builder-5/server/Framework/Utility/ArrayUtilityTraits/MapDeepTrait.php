<?php
/**
 * ArrayUtility::map_deep()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility\ArrayUtilityTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait MapDeepTrait {

	/**
	 * Maps a function to all non-iterable elements of an array or an object.
	 *
	 * This is a modified version of the WordPress core `map_deep` function. The main difference is that this function
	 * passes the path to the current value in the original array to the callback function.
	 *
	 * @since ??
	 *
	 * @param mixed    $value    The array, object, or scalar.
	 * @param callable $callback The function to map onto $value.
	 * @param array    $path     Array of keys to represent the path to the current value in the original array.
	 *
	 * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
	 */
	public static function map_deep( $value, $callback, $path = [] ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$new_path   = $path;
				$new_path[] = $index;

				$value[ $index ] = self::map_deep( $item, $callback, $new_path );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );

			foreach ( $object_vars as $property_name => $property_value ) {
				$new_path   = $path;
				$new_path[] = $property_name;

				$value->$property_name = self::map_deep( $property_value, $callback, $new_path );
			}
		} else {
			$value = call_user_func( $callback, $value, $path );
		}

		return $value;
	}


}
