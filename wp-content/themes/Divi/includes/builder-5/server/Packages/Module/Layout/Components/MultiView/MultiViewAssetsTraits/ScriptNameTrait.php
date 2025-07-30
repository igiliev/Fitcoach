<?php
/**
 * MultiViewAssets::script_name() method.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewAssetsTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait ScriptNameTrait {

	/**
	 * Get script name.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public static function script_name(): string {
		return 'script-library-multi-view';
	}
}
