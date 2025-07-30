<?php
/**
 * Module: ZIndexStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\ZIndex;

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\StyleLibrary\Declarations\ZIndex\ZIndex;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ZIndexStyle class.
 *
 * @since ??
 */
class ZIndexStyle {

	/**
	 * Get z-index's style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ZIndexStyle/ ZIndexStyle} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for styling.
	 *
	 *     @type array    $selectors         Optional. List of selectors. Default `[]`.
	 *     @type callable $selectorFunction  Optional. Selector function. Default `null`.
	 *     @type bool     $important         Optional. Whether to apply "!important" flag to the style declarations.
	 *                                       Default `false`.
	 *                                       Optional. Default is false.
	 *     @type bool     $asStyle           Optional. Whether to wrap the styled output in a style tag.
	 *                                       Default `true`.
	 *     @type string|null   $orderClass   Optional. The selector class name.
	 *     @type bool     $isInsideStickyModule Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string   $returnType        Optional. This is the type of value that the function will return.
	 *                                       Can be either `string` or `array`. Default `array`.
	 * }
	 * @return string|array The z-index style component.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'selectors'        => ['.my-selector'],
	 *     'selectorFunction' => 'my_selector_function',
	 *     'important'        => true,
	 *     'asStyle'          => false,
	 * ];
	 * $style = self::style($args);
	 * ```
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'        => [],
				'selectorFunction' => null,
				'important'        => false,
				'asStyle'          => true,
				'orderClass'       => null,
				'returnType'       => 'array',
			]
		);

		$selector          = $args['selector'];
		$selectors         = $args['selectors'];
		$selector_function = $args['selectorFunction'];
		$attr              = $args['attr'];
		$important         = $args['important'];
		$as_style          = $args['asStyle'];
		$order_class       = $args['orderClass'];

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$children = Utils::style_statements(
			[
				'selectors'               => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'        => $selector_function,
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr'] ?? [],
				'important'               => $important,
				'declarationFunction'     => function( $params ) {
					return ZIndex::style_declaration( $params );
				},
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
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
