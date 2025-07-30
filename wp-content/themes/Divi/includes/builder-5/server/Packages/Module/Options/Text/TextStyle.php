<?php
/**
 * Module: TextStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Text;

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\TextShadow\TextShadowStyle;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TextStyle class.
 *
 * @since ??
 */
class TextStyle {

	/**
	 * Get text style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/TextStyle TextStyle} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector                 The CSS selector.
	 *     @type array         $selectors                Optional. An array of selectors for each breakpoint and state. Default `[]`.
	 *     @type callable      $selectorFunction         Optional. The function to be called to generate CSS selector. Default `null`.
	 *     @type array         $propertySelectors        Optional. The property selectors that you want to unpack. Default `[]`.
	 *     @type array         $attr                     An array of module attribute data.
	 *     @type array         $defaultPrintedStyleAttr  Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type array|bool    $important                Optional. Whether to apply "!important" flag to the style declarations.
	 *                                                   Default `false`.
	 *     @type bool          $asStyle                  Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                   Default `true`
	 *     @type bool          $orientation              Optional. Whether to apply orientation style or not. Default `true`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $returnType Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The text style component
	 *
	 * @example:
	 * ```php
	 *     // Generate a basic stylesheet with a single selector and property.
	 *     $args = array(
	 *         'selectors'  => array(
	 *             array(
	 *                 'value' => '#my-element',
	 *             ),
	 *         ),
	 *         'propertySelectors' => array(
	 *             'text' => array(
	 *                 'color' => array(
	 *                     'value' => '#000000',
	 *                 ),
	 *             ),
	 *         ),
	 *     );
	 *     $stylesheet = My_Namespace\My_Class::style( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 *     // Generate a stylesheet with multiple selectors and multiple properties.
	 *     $args = array(
	 *         'selectors'  => array(
	 *             array(
	 *                 'value' => '.my-class',
	 *             ),
	 *             array(
	 *                 'value' => '#my-element',
	 *             ),
	 *         ),
	 *         'propertySelectors' => array(
	 *             'text' => array(
	 *                 'color' => array(
	 *                     'value' => '#000000',
	 *                 ),
	 *                 'font-size' => array(
	 *                     'value' => '16px',
	 *                 ),
	 *             ),
	 *             'background' => array(
	 *                 'background-color' => array(
	 *                     'value' => '#FFFFFF',
	 *                 ),
	 *             ),
	 *         ),
	 *         'orientation' => false,
	 *     );
	 *     $stylesheet = My_Namespace\My_Class::style( $args );
	 * ```
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'selectorFunction'  => null,
				'important'         => false,
				'asStyle'           => true,
				'orientation'       => true,
				'orderClass'        => null,
				'returnType'        => 'array',
			]
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$selector_function  = $args['selectorFunction'];
		$property_selectors = $args['propertySelectors'];
		$attr               = $args['attr'];
		$important          = $args['important'];
		$as_style           = $args['asStyle'];
		$orientation        = $args['orientation'];
		$order_class        = $args['orderClass'];
		$return_as_array    = 'array' === $args['returnType'];
		$children           = $return_as_array ? [] : '';

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		if ( $orientation ) {
			$children_statements = Utils::style_statements(
				[
					'selectors'            => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
					'selectorFunction'     => $selector_function,
					'propertySelectors'    => $property_selectors['text'] ?? [],
					'declarationFunction'  => '\ET\Builder\Packages\StyleLibrary\Declarations\Text\Text::style_declaration',
					'attr'                 => $attr['text'] ?? [],
					'important'            => is_bool( $important ) ? $important : ( isset( $important['text'] ) ? $important['text'] : false ),
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
		}

		if ( ! empty( $attr['textShadow'] ) ) {
			$children_text_shadow = TextShadowStyle::style(
				[
					'selector'             => $selector,
					'selectors'            => $selectors,
					'propertySelectors'    => $property_selectors['textShadow'] ?? [],
					'selectorFunction'     => $selector_function,
					'attr'                 => $attr['textShadow'] ?? [],
					'asStyle'              => false,
					'important'            => is_bool( $important ) ? $important : ( $important['textShadow'] ?? false ),
					'orderClass'           => $order_class,
					'isInsideStickyModule' => $is_inside_sticky_module,
					'returnType'           => $args['returnType'],
				]
			);

			if ( $children_text_shadow && $return_as_array ) {
				array_push( $children, ...$children_text_shadow );
			} elseif ( $children_text_shadow ) {
				$children .= $children_text_shadow;
			}
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
