<?php
/**
 * Order declaration class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Order;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Order class.
 *
 * This class has functionality for handling order style declarations.
 *
 * @since ??
 */
class Order {

	/**
	 * Get Order's CSS declaration based on the given attribute value.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The layout attribute value.
	 *     @type bool       $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType Optional. This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return string|array The order CSS declaration.
	 */
	public static function style_declaration( array $params ) {
		$attr_value  = $params['attrValue'] ?? [];
		$important   = $params['important'] ?? false;
		$return_type = $params['returnType'] ?? 'string';

		// Create declarations.
		$declarations = new StyleDeclarations(
			[
				'returnType' => $return_type,
				'important'  => $important,
			]
		);

		$order = isset( $attr_value['order'] ) ? $attr_value['order'] : null;

		// Add order property if value is provided.
		if ( null !== $order ) {
			$declarations->add( 'order', $order );
		}

		return $declarations->value();
	}
}
