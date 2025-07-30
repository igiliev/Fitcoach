<?php
/**
 * FeaturesManager::get()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\FeaturesManager\FeaturesManagerTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// TODO feat(D5, Feature Manager): Will be developed. Porting/refactor from `ET_Builder_Module_Features::get` in D4.

trait GetTrait {

	/**
	 * Retrieve the cached value, if it exists.
	 *
	 * The contents will be first attempted to be retrieved by searching by the
	 * key in the cache group. If the cache is hit (success) then the contents
	 * are returned.
	 *
	 * @since ??
	 *
	 * @return bool|mixed Cache result.
	 */
	public static function get() {
		return true;
	}

}
