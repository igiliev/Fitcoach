<?php
/**
 * MultiViewAssets::script_handle() method.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewAssetsTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewAssets;

trait ScriptHandleTrait {

	/**
	 * Get script name.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public static function script_handle(): string {
		return 'divi-' . MultiViewAssets::script_name();
	}
}
