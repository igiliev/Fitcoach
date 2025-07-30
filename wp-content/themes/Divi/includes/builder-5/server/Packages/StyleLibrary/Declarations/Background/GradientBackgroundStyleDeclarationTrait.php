<?php
/**
 * Declarations::gradient_background_style_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait GradientBackgroundStyleDeclarationTrait {

	/**
	 * Style declaration for gradient background.
	 *
	 * This is a wrapper function for `Background::gradient_style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/gradient-background-style-declaration gradientBackgroundStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $gradient {
	 *     Gradient property of background attributes.
	 *
	 *     @type string $type            Gradient type. One of `linear`, `radial`, `circular`, or `conic`.
	 *     @type string $direction       Gradient direction. One of `to top`, `to top right`, `to right`, `to bottom right`,
	 *                                   `to bottom`, `to bottom left`, `to left`, `to top left`, or `angle`.
	 *     @type string $directionRadial Gradient radial direction. One of `center`, `top`, `right`, `bottom`, `left`,
	 *                                   `top right`, `top left`, `bottom right`, or `bottom left`.
	 *     @type array  $stops           Array of gradient stops.
	 *     @type string $repeat          Gradient repeat. One of `on` or `off`.
	 *     @type string $length          Gradient length.
	 * }
	 *
	 * @return string
	 */
	public static function gradient_background_style_declaration( array $gradient ): string {
		return Background::gradient_style_declaration( $gradient );
	}

}
