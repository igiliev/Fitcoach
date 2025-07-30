<?php
/**
 * Layout declarations class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Layout;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * Layout declarations class.
 *
 * This class has functionality for handling layout style declarations.
 *
 * @since ??
 */
class Layout {

	/**
	 * Get layout style declaration.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     Parameters for the layout style declaration.
	 *
	 *     @type bool          $important  Optional. Whether to add !important to the declarations.
	 *     @type array         $attrValue  The layout attribute value.
	 *     @type string        $returnType Optional. The return type of the declaration.
	 *                                     Can be either 'string' or 'key_value_pair'. Default 'string'.
	 * }
	 *
	 * @return string|array Layout style declaration.
	 */
	public static function style_declaration( array $params ) {
		$important   = $params['important'] ?? false;
		$attr_value  = $params['attrValue'] ?? [];
		$return_type = $params['returnType'] ?? 'string';

		// Create new style declarations instance.
		$declarations = new StyleDeclarations(
			[
				'returnType' => $return_type,
				'important'  => $important,
			]
		);

		$display         = $attr_value['display'] ?? '';
		$column_gap      = $attr_value['columnGap'] ?? '';
		$row_gap         = $attr_value['rowGap'] ?? '';
		$flex_direction  = $attr_value['flexDirection'] ?? '';
		$justify_content = $attr_value['justifyContent'] ?? '';
		$align_items     = $attr_value['alignItems'] ?? '';
		$flex_wrap       = $attr_value['flexWrap'] ?? '';
		$align_content   = $attr_value['alignContent'] ?? '';

		if ( $display ) {
			$declarations->add( 'display', $display );
		}

		if ( 'flex' === $display ) {
			if ( $column_gap ) {
				$declarations->add( 'column-gap', $column_gap );
			}

			if ( $row_gap ) {
				$declarations->add( 'row-gap', $row_gap );
			}

			if ( $flex_direction ) {
				$declarations->add( 'flex-direction', $flex_direction );
			}

			if ( $justify_content ) {
				$declarations->add( 'justify-content', $justify_content );
			}

			if ( $align_items ) {
				$declarations->add( 'align-items', $align_items );
			}

			if ( $flex_wrap ) {
				$declarations->add( 'flex-wrap', $flex_wrap );

				if ( 'nowrap' !== $flex_wrap && $align_content ) {
					$declarations->add( 'align-content', $align_content );
				}
			}
		}

		return $declarations->value();
	}
}
