<?php
/**
 * Module: FontBodyStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FontBodyGroup;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\Font\FontStyle;
use ET\Builder\Packages\Module\Options\FontBodyGroup\BlockquoteFontStyle;
use ET\Builder\Packages\Module\Options\FontBodyGroup\ListFontStyle;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * FontBodyStyle class.
 *
 * This class provides additional functionality for managing body styles for a font.
 *
 * @since ??
 */
class FontBodyStyle {

	/**
	 * Adjusts the font style component for the body group and its group tabs.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/FontBodyGroupStyle FontBodyStyle} in
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
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule     Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $attrs_json               Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The adjusted font style component.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'selector'           => '.my-element',
	 *         'selectors'          => [
	 *             'default' => '.my-element',
	 *             'tablet'  => '.my-element-tablet',
	 *             'phone'   => '.my-element-phone',
	 *         ],
	 *         'selectorFunction'   => 'my_selector_function',
	 *         'propertySelectors'  => [
	 *             'body' => ['color', 'font-size'],
	 *         ],
	 *         'attr'               => [
	 *             'body' => [
	 *                 'color'      => '#000000',
	 *                 'font-size'  => '16px',
	 *             ],
	 *         ],
	 *         'defaultPrintedStyleAttr' => [
	 *             'body' => [
	 *                 'color'     => true,
	 *                 'font-size' => false,
	 *             ],
	 *         ],
	 *         'important'          => true,
	 *     ];
	 *
	 *     $adjusted_font_style = FontBodyStyle::font_body_style( $args );
	 * ```
	 */
	public static function font_body_style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'selectorFunction'  => null,
				'important'         => false,
				'orderClass'        => null,
				'attrs_json'        => null,
				'returnType'        => 'string',
			]
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$selector_function  = $args['selectorFunction'];
		$property_selectors = $args['propertySelectors'];
		$attr               = $args['attr'];
		$important          = $args['important'];
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

		if ( ! empty( $attr['body'] ) ) {
			$children_body = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => $selector_function,
					'propertySelectors'       => $property_selectors['body'] ?? [],
					'attrs_json'              => $attr_json,
					'attr'                    => $attr['body'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['body'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['body'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_body && $return_as_array ) {
				array_push( $children, ...$children_body );
			} elseif ( $children_body ) {
				$children .= $children_body;
			}
		}

		if ( ! empty( $attr['link'] ) ) {
			$children_link = FontStyle::style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = isset( $selector_function ) ? call_user_func( $selector_function, $params ) : $params['selector'];
						$sub_selector  = 'a';
						return ModuleUtils::generate_combined_selectors( $base_selector, $sub_selector );
					},
					'propertySelectors'       => $property_selectors['link'] ?? [],
					'attrs_json'              => $attr_json,
					'attr'                    => $attr['link'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['link'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['link'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_link && $return_as_array ) {
				array_push( $children, ...$children_link );
			} elseif ( $children_link ) {
				$children .= $children_link;
			}
		}

		if ( ! empty( $attr['ul'] ) ) {
			$children_ul = ListFontStyle::list_font_style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = isset( $selector_function ) ? call_user_func( $selector_function, $params ) : $params['selector'];
						$sub_selector  = 'ul';
						return ModuleUtils::generate_combined_selectors( $base_selector, $sub_selector );
					},
					'propertySelectors'       => $property_selectors['ul'] ?? [],
					'attrs_json'              => $attr_json,
					'attr'                    => $attr['ul'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['ul'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['ul'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_ul && $return_as_array ) {
				array_push( $children, ...$children_ul );
			} elseif ( $children_ul ) {
				$children .= $children_ul;
			}
		}

		if ( ! empty( $attr['ol'] ) ) {
			$children_ol = ListFontStyle::list_font_style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = isset( $selector_function ) ? call_user_func( $selector_function, $params ) : $params['selector'];
						$sub_selector  = 'ol';
						return ModuleUtils::generate_combined_selectors( $base_selector, $sub_selector );
					},
					'propertySelectors'       => $property_selectors['ol'] ?? [],
					'attrs_json'              => $attr_json,
					'attr'                    => $attr['ol'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['ol'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['ol'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_ol && $return_as_array ) {
				array_push( $children, ...$children_ol );
			} elseif ( $children_ol ) {
				$children .= $children_ol;
			}
		}

		if ( ! empty( $attr['quote'] ) ) {
			$children_quote = BlockquoteFontStyle::blockquote_font_style(
				[
					'selector'                => $selector,
					'selectors'               => $selectors,
					'selectorFunction'        => function( $params ) use ( $selector_function ) {
						$base_selector = isset( $selector_function ) ? call_user_func( $selector_function, $params ) : $params['selector'];
						$sub_selector  = 'blockquote';
						return ModuleUtils::generate_combined_selectors( $base_selector, $sub_selector );
					},
					'propertySelectors'       => $property_selectors['quote'] ?? [],
					'attrs_json'              => $attr_json,
					'attr'                    => $attr['quote'],
					'defaultPrintedStyleAttr' => $args['defaultPrintedStyleAttr']['quote'] ?? [],
					'important'               => is_bool( $important ) ? $important : ( $important['quote'] ?? [] ),
					'asStyle'                 => false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			);

			if ( $children_quote && $return_as_array ) {
				array_push( $children, ...$children_quote );
			} elseif ( $children_quote ) {
				$children .= $children_quote;
			}
		}

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'asStyle'  => true,
				'children' => $children,
			]
		);
	}

}
