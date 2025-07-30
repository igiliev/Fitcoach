<?php
/**
 * Module: ElementClassnames class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Element;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Classnames;
use ET\Builder\Packages\Module\Options\Animation\AnimationUtils;
use ET\Builder\Packages\Module\Options\Background\BackgroundClassnames;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowClassnames;
use ET\Builder\Packages\Module\Options\Dividers\DividersUtils;
use ET\Builder\Packages\Module\Options\Link\LinkUtils;

/**
 * Element Classnames class.
 *
 * This class provides methods to manipulate class names of elements.
 *
 * @since ??
 */
class ElementClassnames {

	/**
	 * Get element classnames based on the provided arguments.
	 *
	 * This function is used to generate a string of class names based on the provided arguments.
	 * It can be used to add class names to HTML elements dynamically.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ElementClassnames ElementClassnames} in
	 * `@divi/module` package.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments to customize the class names.
	 *
	 *     @type array    $attrs      Optional. Additional attributes for the class names. Default `[]`.
	 *     @type bool     $animation  Optional. Whether to include animation class names. Default `true`.
	 *     @type bool     $background Optional. Whether to include background class names. Default `true`.
	 *     @type bool     $border     Optional. Whether to include border class names. Default `true`.
	 *     @type bool     $link       Optional. Whether to include link class names. Default `true`.
	 *     @type bool     $dividers   Optional. Whether to include divider class names. Default `false`.
	 *     @type bool     $boxShadow  Optional. Whether to include box shadow class names. Default `false`.
	 * }
	 *
	 * @return string The generated class names.
	 *
	 * @example:
	 * ```php
	 * // Example 1: Provide only the 'division' argument.
	 * $args = [
	 *     'dividers' => true,
	 * ];
	 *
	 * $result = ElementClassnames::classnames( $args ); // Returns the class names related to dividers.
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Example 2: Provide multiple arguments.
	 * $args = [
	 *     'animation'  => true,
	 *     'background' => true,
	 *     'border'     => true,
	 * ];
	 * $result = ElementClassnames::classnames( $args ); // Returns the class names related to animation, background, and border.
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Example 3: Provide empty arguments.
	 * $args = [];
	 * $result = ElementClassnames::classnames( $args ); // Returns an empty string as no class names are included.
	 * ```
	 */
	public static function classnames( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'attrs'      => [],
				'animation'  => true,
				'background' => true,
				'link'       => true,
				'dividers'   => false,

				// Box shadow classname is added when box shadow element is added. Box shadow element is added if module
				// needs additional element for rendering box shadow inset element because module's content will fill up
				// the entire module area like video or image (unless padding is added to module). Thus many of module
				// won't needed this hence this is set to false by default.
				'boxShadow'  => false,
			]
		);

		$attrs      = $args['attrs'] ?? [];
		$animation  = $args['animation'] ?? true;
		$background = $args['background'] ?? true;
		$link       = $args['link'] ?? true;
		$dividers   = $args['dividers'] ?? false;
		$box_shadow = $args['boxShadow'] ?? false;

		// Assign Classnames instance.
		$classnames = new Classnames();

		// Add Classname of animation attrs.
		if ( $animation ) {
			$classnames->add( AnimationUtils::classnames( $attrs['animation'] ?? [] ) );
		}

		$is_background_enabled = is_array( $background ) ? in_array( true, $background, true ) : $background;
		// Add Classname of background attrs.
		if ( $is_background_enabled ) {
			$classnames->add( BackgroundClassnames::classnames( $attrs['background'] ?? [] ) );
		}

		$is_box_shadow_enabled = is_array( $box_shadow ) && isset( $box_shadow['overlay'] ) ? $box_shadow['overlay'] : $box_shadow;
		// Add Classname of boxShadow attrs.
		if ( $is_box_shadow_enabled ) {
			$classnames->add( BoxShadowClassnames::has_overlay( $attrs['boxShadow'] ?? [] ), true );
		}

		// Add Classname of divider attrs.
		if ( $dividers ) {
			$classnames->add( DividersUtils::classnames( $attrs['dividers'] ?? [] ) );
		}

		// Add Classname of link option attrs.
		if ( $link ) {
			$classnames->add( LinkUtils::classnames( $attrs['link'] ?? [] ) );
		}

		return $classnames->value();
	}

}
