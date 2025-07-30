<?php
/**
 * Module: CssStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Css;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\Css\CssStyleUtils;

/**
 * CssStyle class.
 *
 * @since ??
 */
class CssStyle {

	/**
	 * Get custom CSS style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/CssStyle CssStyle} in
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
	 *     @type array         $attr                     An array of module attribute data.
	 *     @type bool          $asStyle                  Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                   Default `true`.
	 *     @type array         $cssFields                Optional. CSS fields. Default `[]`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The custom CSS style component.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'selectors'        => [ 'h1', '.container' ],
	 *     'selectorFunction' => null,
	 *     'asStyle'          => true,
	 *     'cssFields'        => [
	 *         'color'    => '#000',
	 *         'font-size' => '16px',
	 *     ],
	 * ];
	 *
	 * $style = CssStyle::style( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'selectors'        => [ '#header', '#footer' ],
	 *     'selectorFunction' => 'get_custom_selector',
	 *     'asStyle'          => true,
	 *     'cssFields'        => [
	 *         'background-color' => '#fff',
	 *         'font-family'      => 'Arial, sans-serif',
	 *     ],
	 * ];
	 *
	 * $style = CssStyle::style( $args );
	 * ```
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'        => [],
				'selectorFunction' => null,
				'asStyle'          => true,
				'cssFields'        => [],
				'orderClass'       => null,
				'returnType'       => 'array',
				'atRules'          => '',
			]
		);

		$selector          = $args['selector'];
		$selectors         = $args['selectors'];
		$selector_function = $args['selectorFunction'];
		$attr              = $args['attr'];
		$as_style          = $args['asStyle'];
		$css_fields        = $args['cssFields'];
		$order_class       = $args['orderClass'];
		$at_rules          = $args['atRules'];

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$children = CssStyleUtils::get_statements(
			[
				'selectors'        => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction' => $selector_function,
				'attr'             => $attr,
				'cssFields'        => $css_fields,
				'orderClass'       => $order_class,
				'returnType'       => $args['returnType'],
				'atRules'          => $at_rules,
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
