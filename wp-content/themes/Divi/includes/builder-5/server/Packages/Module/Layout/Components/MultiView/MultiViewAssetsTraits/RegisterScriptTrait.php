<?php
/**
 * MultiViewAssets::register_script() method.
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

trait RegisterScriptTrait {

	/**
	 * Register `script-library-multi-view` script via `wp_register_script`.
	 *
	 * This script is has `jquery` as dependency.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function register_script(): void {
		wp_register_script(
			MultiViewAssets::script_handle(),
			ET_BUILDER_5_URI . '/visual-builder/build/' . MultiViewAssets::script_name() . '.js',
			[ 'jquery' ],
			ET_CORE_VERSION,
			true
		);
	}
}
