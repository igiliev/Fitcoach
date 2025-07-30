<?php
/**
 * Custom class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Custom;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Custom is a helper class for working with custom style declarations.
 *
 * @since ??
 */
class Custom {

	/**
	 * Get custom CSS declaration based on given properties.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/custom-style-declaration customStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $property  The CSS declaration property.
	 *     @type string $value     The CSS declaration value.
	 *     @type bool   $important Optional. Whether to add `!important` tag. Default `false`.
	 * }
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important' => false,
			]
		);

		$property      = $args['property'];
		$value         = $args['value'];
		$important     = $args['important'];
		$important_tag = $important ? ' !important' : '';

		return $property . ': ' . $value . $important_tag;
	}
}
