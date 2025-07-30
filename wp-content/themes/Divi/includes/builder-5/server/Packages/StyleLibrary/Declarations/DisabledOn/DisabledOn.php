<?php
/**
 * DisabledOn class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\DisabledOn;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * DisabledOn class.
 *
 * @since ??
 */
class DisabledOn {

	/**
	 * Get disabled on CSS declaration based on given attrValue.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/disabled-on-style-declaration disabledOnStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attrValue                 The value (breakpoint > state > value) of module attribute.
	 *     @type string $disabledModuleVisibility Optional. Disabled module visibility.
	 *                                            One of `transparent` or `hidden`. Default `hidden`.
	 *     @type string $returnType               This is the type of value that the function will return.
	 *                                            Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		$attr_value  = $args['attrValue'];
		$return_type = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => true,
				'returnType' => $return_type,
			]
		);

		if ( 'on' === $attr_value ) {
			$disabled_module_visibility = isset( $args['disabledModuleVisibility'] ) ? $args['disabledModuleVisibility'] : 'hidden';
			$is_transparent             = 'transparent' === $disabled_module_visibility;
			$property                   = $is_transparent ? 'opacity' : 'display';
			$property_value             = $is_transparent ? '0.5' : 'none';

			$style_declarations->add( $property, $property_value );
		}

		return $style_declarations->value();
	}
}
