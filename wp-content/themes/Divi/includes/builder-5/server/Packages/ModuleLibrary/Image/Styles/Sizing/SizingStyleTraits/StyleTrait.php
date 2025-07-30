<?php
/**
 * Module Library: Image Module Sizing Style Trait
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Image\Styles\Sizing\SizingStyleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Sizing\SizingStyle;

trait StyleTrait {

	/**
	 * Get sizing (width, max-width, alignment, min-height, height, max-height) styles.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector            The CSS selector.
	 *     @type array         $selectors           Optional. An array of selectors for each breakpoint and state.
	 *     @type callable      $selectorFunction    Optional. The function to be called to generate CSS selector.
	 *     @type array         $propertySelectors   Optional. The property selectors that you want to unpack.
	 *     @type array         $attr                An array of module attribute data.
	 *     @type array|boolean $important           Optional. The important statement.
	 *     @type bool          $asStyle             Optional. Flag to wrap the style declaration with style tag.
	 *     @type string|null   $orderClass          Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule Optional. Whether the module is inside sticky module or not.
	 *     @type string        $returnType          Optional. This is the type of value that the function will return.
	 *                                              Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'            => [],
				'propertySelectors'    => [],
				'selectorFunction'     => null,
				'important'            => false,
				'asStyle'              => true,
				'orderClass'           => null,
				'isInsideStickyModule' => false,
				'returnType'           => 'array',
			]
		);

		$selector                = $args['selector'];
		$image_selector          = $args['imageSelector'];
		$selectors               = $args['selectors'];
		$selector_function       = $args['selectorFunction'];
		$property_selectors      = $args['propertySelectors'];
		$attr                    = $args['attr'];
		$important               = $args['important'];
		$as_style                = $args['asStyle'];
		$order_class             = $args['orderClass'];
		$is_inside_sticky_module = $args['isInsideStickyModule'];
		$return_as_array         = 'array' === $args['returnType'];
		$children                = $return_as_array ? [] : '';

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$children_statements = Utils::style_statements(
			[
				'selectors'            => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'     => $selector_function,
				'propertySelectors'    => $property_selectors,
				'attr'                 => $attr,
				'important'            => $important,
				'declarationFunction'  => [ SizingStyle::class, 'style_declaration' ],
				'orderClass'           => $order_class,
				'isInsideStickyModule' => $is_inside_sticky_module,
				'returnType'           => $args['returnType'],
			]
		);

		if ( $children_statements && $return_as_array ) {
			array_push( $children, ...$children_statements );
		} elseif ( $children_statements ) {
			$children .= $children_statements;
		}

		$children_image = Utils::style_statements(
			[
				'selectors'            => [ 'desktop' => [ 'value' => $image_selector ] ],
				'selectorFunction'     => $selector_function,
				'propertySelectors'    => $property_selectors,
				'attr'                 => $attr,
				'important'            => $important,
				'declarationFunction'  => [ SizingStyle::class, 'height_style_declaration' ],
				'orderClass'           => $order_class,
				'isInsideStickyModule' => $is_inside_sticky_module,
				'returnType'           => $args['returnType'],
			]
		);

		if ( $children_image && $return_as_array ) {
			array_push( $children, ...$children_image );
		} elseif ( $children_image ) {
			$children .= $children_image;
		}

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

}
