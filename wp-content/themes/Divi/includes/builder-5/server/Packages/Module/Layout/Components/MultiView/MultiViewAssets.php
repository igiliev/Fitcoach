<?php
/**
 * Module Options: MultiView Assets Class.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module Options: MultiView assets class.
 *
 * @since ??
 */
class MultiViewAssets {

	use MultiViewAssetsTraits\RegisterScriptTrait;
	use MultiViewAssetsTraits\ScriptNameTrait;
	use MultiViewAssetsTraits\ScriptHandleTrait;

}
