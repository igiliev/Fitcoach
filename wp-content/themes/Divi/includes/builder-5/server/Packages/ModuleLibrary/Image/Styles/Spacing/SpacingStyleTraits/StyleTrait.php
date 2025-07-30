<?php
/**
 * Module Library: Image Module Spacing Style Trait
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Image\Styles\Spacing\SpacingStyleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Spacing\SpacingStyle;

trait StyleTrait {

	/**
	 * Get spacing (margin & padding) style.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector            The CSS selector.
	 *     @type array         $selectors           Optional. An array of selectors for each breakpoint and state.
	 *     @type callable      $selectorFunction    The function to be called to generate CSS selector.
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
		$selectors               = $args['selectors'];
		$selector_function       = $args['selectorFunction'];
		$property_selectors      = $args['propertySelectors'];
		$attr                    = $args['attr'];
		$important               = $args['important'];
		$as_style                = $args['asStyle'];
		$order_class             = $args['orderClass'];
		$is_inside_sticky_module = $args['isInsideStickyModule'];

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$children = Utils::style_statements(
			[
				'selectors'                     => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'              => $selector_function,
				'propertySelectors'             => $property_selectors,
				'propertySelectorsShorthandMap' => [
					'padding' => [
						'padding-top',
						'padding-right',
						'padding-bottom',
						'padding-left',
					],
					'margin'  => [
						'margin-top',
						'margin-right',
						'margin-bottom',
						'margin-left',
					],
				],
				'attr'                          => $attr,
				'important'                     => $important,
				'declarationFunction'           => [ SpacingStyle::class, 'style_declaration' ],
				'orderClass'                    => $order_class,
				'isInsideStickyModule'          => $is_inside_sticky_module,
				'returnType'                    => $args['returnType'],
			]
		);

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

}
