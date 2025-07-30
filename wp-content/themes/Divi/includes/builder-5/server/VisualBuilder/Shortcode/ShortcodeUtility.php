<?php
/**
 * Shortcode: Shortcode class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\VisualBuilder\Shortcode\ShortcodeTraits;

/**
 * ShortcodeUtility class.
 *
 * A utility class for handling shortcodes.
 *
 * @since ??
 */
class ShortcodeUtility {

	use ShortcodeTraits\GetShortcodeTagsTrait;

}
