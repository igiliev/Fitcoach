<?php
/**
 * ArrayUtility::find()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility\ArrayUtilityTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait FindTrait {

	/**
	 * Find first element that satisfies the testing function.
	 *
	 * Returns the first element in the provided array that satisfies the provided testing function.
	 * If no values satisfy the testing function, `null` is returned.
	 *
	 * This function is equivalent of JS function `Array.prototype.find()`.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
	 *
	 * @since ??
	 *
	 * @param array    $array         The input array.
	 * @param callable $test_function Function that will be invoked to match array element. This function must
	 *                                returns `true` to have the element considered as match.
	 *
	 * @return mixed|null The value that satisfies required criteria, or null if none of the items match.
	 **/
	public static function find( $array, $test_function ) {
		foreach ( $array as $index => $item ) {
			if ( true === call_user_func( $test_function, $item, $index, $array ) ) {
				return $item;
			}
		}

		return null;
	}

}
