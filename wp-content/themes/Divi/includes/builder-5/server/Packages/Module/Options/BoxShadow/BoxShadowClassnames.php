<?php
/**
 * Module: BoxShadowClassnames class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\BoxShadow;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowUtils;


/**
 * BoxShadowClassnames class.
 *
 * This class allows  adding overlay functionality to the box shadow classnames.
 *
 * @since ??
 */
class BoxShadowClassnames {
	/**
	 * Check if the box shadow overlay is enabled and return the corresponding classname(s).
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BoxShadowHasOverlayClassnames boxShadowHasOverlayClassnames} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $attr The box shadow group attributes.
	 *
	 * @return string The box shadow overlay class name.
	 *                Returns an empty string if the overlay is not enabled.
	 *
	 * @example:
	 * ```php
	 *     $attr = []; // The box shadow group attributes
	 *     $overlayClass = BoxShadowClassnames::has_overlay( $attr );
	 *     echo $overlayClass;
	 *     // Output: 'has-box-shadow-overlay'
	 * ```
	 */
	public static function has_overlay( $attr ) {
		if ( ! BoxShadowUtils::is_overlay_enabled( $attr ) ) {
			return '';
		}

		return 'has-box-shadow-overlay';
	}
}
