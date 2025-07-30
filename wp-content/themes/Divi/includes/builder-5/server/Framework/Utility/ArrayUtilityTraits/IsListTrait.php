<?php
/**
 * ArrayUtility::is_list()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility\ArrayUtilityTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait IsListTrait {

	/**
	 * Checks whether a given array is a sequential array. Empty array are considered lists.
	 *
	 * @since ??
	 *
	 * @param array $array The array being evaluated.
	 *
	 * @return bool Returns true if array is a list, false otherwise.
	 **/
	public static function is_list( array $array ): bool {
		$i = 0;

		foreach ( $array as $k => $v ) {
			if ( $k !== $i++ ) {
				return false;
			}
		}

		return true;
	}

}
