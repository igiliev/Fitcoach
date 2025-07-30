<?php
/**
 * Library: LibraryUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Library;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Library\LibraryUtilityTraits;

/**
 * LibraryUtility class.
 *
 * This class contains helper methods to work with the library.
 *
 * @since ??
 */
class LibraryUtility {

	use LibraryUtilityTraits\PrepareLibraryTermsTrait;

}
