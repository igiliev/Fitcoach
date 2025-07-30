<?php
/**
 * ArrayUtility::filter_deep()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility\ArrayUtilityTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait FilterDeepTrait {

	/**
	 * Recursively filters an array using a callback function starting from the deepest level of the array.
	 *
	 * @since ??
	 *
	 * @param array    $array    The array to be filtered.
	 * @param callable $callback The callback function to decide whether the element should be removed. Falsy returned value will remove the element.
	 * @param array    $path     The path of the value. It will contain all the keys from the root to the current value.
	 *
	 * @return array The filtered array.
	 */
	public static function filter_deep( array $array, callable $callback, array $path = [] ): array {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$array[ $key ] = self::filter_deep( $value, $callback, array_merge( $path, [ $key ] ) );
			}

			$result = call_user_func( $callback, $array[ $key ] ?? null, $key, $path );

			if ( ! $result ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}

}
