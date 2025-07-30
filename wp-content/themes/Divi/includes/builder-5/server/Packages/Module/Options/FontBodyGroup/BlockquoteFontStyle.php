<?php
/**
 * Module: BlockquoteFontStyle class.
 *
 * @package Builder\FrontEnd
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FontBodyGroup;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\Font\FontStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;

/**
 * `BlockquoteFontStyle`
 *
 * @since ??
 */
class BlockquoteFontStyle {

	/**
	 * Get blockquote Font Style
	 *
	 * Applies font and border style to the blockquote element and its children.
	 * This function uses `FontStyle::style()` and `BorderStyle::style()` to generate the CSS styles for
	 * the blockquote element and its children.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BlockquoteFontStyle BlockquoteFontStyle} in
	 * `@divi/module` package.
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
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $attrs_json               Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The generated CSS styles for the blockquote element and its children.
	 *                      If $asStyle is true, the styles are returned as a string.
	 *                      Otherwise, an array of styles is returned.
	 *
	 * @example:
	 * ```php
	 *     // Apply font and border style to the blockquote element and its children with the default arguments.
	 *     $args = [];
	 *     blockquote_font_style( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 *     // Apply font and border style to the blockquote element and its children with custom selectors and attributes.
	 *     $args = [
	 *         'selector'  => '.my-blockquote',
	 *         'selectors' => ['.my-blockquote .inner'],
	 *         'attr'      => [
	 *             'font-family' => 'Arial, sans-serif',
	 *             'font-size'   => '16px',
	 *             'color'       => '#333',
	 *             'border'      => '1px solid #ccc',
	 *         ],
	 *     ];
	 *     blockquote_font_style( $args );
	 * ```
	 */
	public static function blockquote_font_style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'selectorFunction'  => null,
				'important'         => false,
				'asStyle'           => true,
				'orderClass'        => null,
				'attrs_json'        => null,
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
		$order_class        = $args['orderClass'];
		$return_as_array    = 'array' === $args['returnType'];
		$children           = $return_as_array ? [] : '';

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return $children;
		}

		// If attrs_json is provided use that, otherwise JSON encode the attributes array.
		$attr_json = null === $args['attrs_json'] ? wp_json_encode( $attr ) : $args['attrs_json'];

		$children_font = FontStyle::style(
			[
				'selector'             => $selector,
				'selectors'            => $selectors,
				'selectorFunction'     => $selector_function,
				'propertySelectors'    => $property_selectors,
				'attrs_json'           => $attr_json,
				'attr'                 => $attr,
				'important'            => $important,
				'asStyle'              => false,
				'orderClass'           => $order_class,
				'isInsideStickyModule' => $is_inside_sticky_module,
				'returnType'           => $args['returnType'],
			]
		);

		if ( $children_font && $return_as_array ) {
			array_push( $children, ...$children_font );
		} elseif ( $children_font ) {
			$children .= $children_font;
		}

		if ( ! empty( $attr['border'] ) ) {
			$children_border = BorderStyle::style(
				[
					'selector'             => $selector,
					'selectors'            => $selectors,
					'selectorFunction'     => $selector_function,
					'propertySelectors'    => $property_selectors['border'] ?? [],
					'attrs_json'           => $attr_json,
					'attr'                 => $attr['border'],
					'important'            => is_bool( $important ) ? $important : ( $important['border'] ?? [] ),
					'asStyle'              => false,
					'orderClass'           => $order_class,
					'isInsideStickyModule' => $is_inside_sticky_module,
					'returnType'           => $args['returnType'],
				]
			);

			if ( $children_border && $return_as_array ) {
				array_push( $children, ...$children_border );
			} elseif ( $children_border ) {
				$children .= $children_border;
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
