<?php
/**
 * Module: BackgroundClassnames class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Packages\Module\Options\Background\BackgroundUtils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * BackgroundClassnames class
 *
 * @since ??
 */
class BackgroundClassnames {

	/**
	 * Retrieve the classnames for the background parallax effect.
	 *
	 * This function retrieves the classnames for the background parallax effect based on the provided background attribute.
	 * The background attribute should be an array that represents the background settings for different breakpoints and states.
	 * The classnames are used in CSS selectors to display the parallax effect on different states.
	 * There are three possible classnames that can be returned by this function:
	 * - `et_pb_section_parallax` (for the default state)
	 * - `et_pb_section_parallax_hover` (for the hover state)
	 * - `et_pb_section_parallax_sticky` (for the sticky state)
	 *
	 * @since ??
	 *
	 * @param array $background_attr The background attribute with the parallax settings.
	 *
	 * @return string The classnames for the background parallax effect.
	 *                An empty string if the provided attribute is falsey.
	 *
	 * @example:
	 * ```php
	 * $background_attr = [
	 *     'desktop' => [
	 *         'value' => [
	 *             'image' => [
	 *                 'parallax' => [
	 *                     'enabled' => 'on',
	 *                 ],
	 *                 'url' => 'https://example.com/image.jpg',
	 *             ],
	 *         ],
	 *     ],
	 * ];
	 * $classnames = BackgroundClassnames::get_background_parallax_classnames( $background_attr );
	 * ```
	 */
	public static function get_background_parallax_classnames( $background_attr ): string {
		// Exit early if attribute value is falsey.
		if ( ! $background_attr ) {
			return '';
		}

		// Collect the extracted parallax classname.
		$parallax_classnames = [];

		// Keep track of the `enabled` value on larger breakpoint.
		$larger_breakpoint_enabled = 'off';

		if ( is_array( $background_attr ) ) {
			// The order of breakpoint matters because larger breakpoint value cascades into the smaller breakpoint
			// value. Thus instead of looping the attribute value which is object which has no guaranteed order, loop
			// over the breakpoints list with guaranteed order.
			foreach ( Breakpoint::get_enabled_breakpoint_names() as $breakpoint ) {
				$state_value = $background_attr[ $breakpoint ] ?? null;

				if ( is_array( $state_value ) ) {
					// The order of states matters because `value` state cascades into the other states.
					// Thus instead of looping the attribute value which is object which has no guaranteed order, loop
					// over the states list with guaranteed order.
					foreach ( ModuleUtils::states() as $state ) {
						// Current value on current breakpoint and state.
						$current_value   = $state_value[ $state ] ?? null;
						$current_enabled = $current_value['image']['parallax']['enabled'] ?? null;
						$current_url     = $current_value['image']['url'] ?? null;

						// Print module parallax classname when:
						// 1. Current enabled + current URL exist.
						// 2. Current enabled doesn't exist + parallax enabled on larger breakpoint + current URL exist.
						if ( ( 'on' === $current_enabled || ( is_null( $current_enabled ) && 'on' === $larger_breakpoint_enabled ) ) && $current_url ) {
							$suffix = 'value' === $state ? '' : "_{$state}";

							$parallax_classnames[] = "et_pb_section_parallax{$suffix}";
						}

						// Overwrite "enabled on larger breakpoint" when current state is "value" and current `enabled` value exist.
						// Only "value" state overwrites the larger breakpoint due to how things are cascading:
						// - desktop => tablet => phone (smaller breakpoint use larger breakpoint value)
						// - hover => value || sticky => value (hover OR sticky use `value` when it doesn't exist).
						if ( 'value' === $state && ! is_null( $current_enabled ) ) {
							$larger_breakpoint_enabled = $current_enabled;
						}
					}
				}
			}
		}

		return implode( ' ', array_unique( $parallax_classnames ) );
	}

	/**
	 * Get exist classname(s) that signifies the presence of parallax on other breakpoints or states.
	 *
	 * The rule for exist classname(s) is that the "desktop" breakpoint and "value" state return
	 * an empty string instead of an exist classname(s). Other breakpoints and states return
	 * a combination of the class prefix, breakpoint suffix, state suffix, and "_exist" suffix.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundClassnames backgroundClassnames} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param string  $breakpoint              The breakpoint name. One of `desktop`, `tablet`, or `phone`.
	 * @param string  $state                   The state name. One of `active`, `hover`, `disabled`, or `value`.
	 * @param boolean $gradient_overlays_image Whether the gradient overlays the image.
	 *
	 * @return string The exist classname(s) for the parallax background.
	 *
	 * @example:
	 * ```php
	 * $exist_classname = BackgroundClassnames::get_background_parallax_exist_classnames( 'tablet', 'hover', true );
	 * ```
	 *
	 * @example:
	 * ```php
	 * $exist_classname = BackgroundClassnames::get_background_parallax_exist_classnames( 'desktop', 'value' );
	 * ```
	 */
	public static function get_background_parallax_exist_classnames( string $breakpoint, string $state, ?bool $gradient_overlays_image = false ): string {
		$breakpoint_suffix = 'desktop' === $breakpoint ? '' : "_{$breakpoint}";
		$state_suffix      = 'value' === $state ? '' : "_{$state}";
		$class_prefix      = $gradient_overlays_image ? 'et_parallax_gradient' : 'et_parallax_bg';

		return $breakpoint_suffix || $state_suffix ? "{$class_prefix}{$breakpoint_suffix}{$state_suffix}_exist" : '';
	}

	/**
	 * Get the class names based on the provided attributes.
	 *
	 * This function takes an array of attributes and returns a string of class names
	 * based on the conditions defined in the function. It checks for the presence of
	 * background videos, parallax class names, and appends them to the class names array.
	 * Finally, it returns the array of class names imploded with spaces, or an empty string
	 * if no class names are found.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundClassnames backgroundClassnames} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $attr The background attributes to be used for generating the class names.
	 *
	 * @return string The generated class names.
	 *
	 * @example:
	 * ```php
	 *  $attr = array(
	 *    'desktop' => array(
	 *      'hover' => array(
	 *        'video' => array(
	 *          'mp4' => 'video.mp4',
	 *          'webm' => 'video.webm',
	 *        ),
	 *      ),
	 *    ),
	 *  );
	 *  $class_names = BackgroundClassnames::classnames($attr);
	 * ```
	 *
	 * @output
	 * ```php
	 *  'et-pb-has-background-video et_pb_preload et-pb-has-background-video__hover'
	 * ```
	 */
	public static function classnames( array $attr ): string {
		// Exit early if attribute value is empty.
		if ( empty( $attr ) ) {
			return '';
		}

		$classnames = [];

		// Check if background video is enabled.
		if ( BackgroundUtils::has_background_video( $attr ) ) {
			$classnames[] = 'et-pb-has-background-video';
			$classnames[] = 'et_pb_preload';

			// Check if background video is enabled for hover.
			if (
				! empty( $attr['desktop']['hover']['video']['mp4'] ?? '' )
				|| ! empty( $attr['desktop']['hover']['video']['webm'] ?? '' )
			) {
				$classnames[] = 'et-pb-has-background-video__hover';
			}

			// Check if background video is enabled for sticky.
			if (
				! empty( $attr['desktop']['sticky']['video']['mp4'] ?? '' )
				|| ! empty( $attr['desktop']['sticky']['video']['webm'] ?? '' )
			) {
				$classnames[] = 'et-pb-has-background-video__sticky';
			}
		}

		$parallax_classnames = self::get_background_parallax_classnames( $attr );

		// Check if parallax classnames is not empty string.
		if ( ! empty( $parallax_classnames ) ) {
			$classnames[] = $parallax_classnames;
		}

		return ! empty( $classnames ) ? implode( ' ', $classnames ) : '';
	}

}
