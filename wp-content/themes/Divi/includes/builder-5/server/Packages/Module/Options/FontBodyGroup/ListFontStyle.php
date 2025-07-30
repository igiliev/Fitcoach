<?php
/**
 * Module: ListFontStyle class.
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
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * ListFontStyle class.
 *
 * @since ??
 */
class ListFontStyle {

	/**
	 * Get List Font CSS declaration based on given attributes value.
	 *
	 * This function generates a list style declaration with optional customization options.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for customizing the list style declaration.
	 *
	 *     @type string     $attrValue    The attribute value (breakpoint > state > value) for the list item.
	 *     @type bool|array $important    Optional.  Whether to apply "!important" flag to the style declarations. Default `false`.
	 *     @type string     $returnType   Optional. The return type of the function. Default `'string'`.
	 * }
	 *
	 * @return array|string The generated list style declaration.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'attrValue'   => 'itemIndent',
	 *     'important'   => true,
	 *     'returnType'  => 'string',
	 * ];
	 *
	 * $declaration = ListFontStyleTrait::list_style_declaration( $args );
	 * ```
	 */
	public static function list_style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		$important_tag = $important ? ' !important' : '';

		if ( isset( $attr_value['itemIndent'] ) ) {
			$style_declarations->add( 'padding-left', $attr_value['itemIndent'] . $important_tag );
		}

		return $style_declarations->value();
	}

	/**
	 * Generate CSS style declaration for list items.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for generating the style declaration.
	 *
	 *     @type string     $attrValue    The attribute value (breakpoint > state > value) for the list item.
	 *     @type bool|array $important    Optional.  Whether to apply "!important" flag to the style declarations. Default `false`.
	 *     @type string     $returnType   Optional. The return type of the function. Default `'string'`.
	 * }
	 *
	 * @return array|string The generated CSS style declaration.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'attrValue' => [
	 *         'type'     => 'disc',
	 *         'position' => 'inside',
	 *     ],
	 *     'important'  => true,
	 *     'returnType' => 'string',
	 * ];
	 * $styleDeclaration = ListFontStyleTrait::list_item_style_declaration( $args );
	 *
	 * // Returns: "list-style-type: disc !important; list-style-position: inside !important;"
	 * ```
	 */
	public static function list_item_style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value  = $args['attrValue'];
		$important   = $args['important'];
		$return_type = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => false, // TODO feat(D5, Refactor) this should get value from params.
				'returnType' => $return_type,
			]
		);

		$important_tag = $important ? ' !important' : '';

		if ( isset( $attr_value['type'] ) ) {
			$style_declarations->add( 'list-style-type', $attr_value['type'] . $important_tag );
		}

		if ( isset( $attr_value['position'] ) ) {
			$style_declarations->add( 'list-style-position', $attr_value['position'] . $important_tag );
		}

		return $style_declarations->value();
	}

	/**
	 * Get List Font style.
	 *
	 * This function retrieves the style for a list font.
	 *
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/3 ListFontStyle} in
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
	 * @return string|array The generated CSS style.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'selector'           => '.list',
	 *     'selectors'          => [
	 *         'desktop' => [ 'value' => '.list' ],
	 *     ],
	 *     'selectorFunction'   => function( $params ) {
	 *         // Custom selector function logic.
	 *         return $params['selector'];
	 *     },
	 *     'propertySelectors'  => [
	 *         'list' => [ 'font-family', 'font-size' ],
	 *     ],
	 *     'attr'               => [
	 *         'list' => [ 'font-family' => 'Arial', 'font-size' => '14px' ],
	 *     ],
	 *     'important'          => false,
	 *     'asStyle'            => true,
	 * ];
	 * $result = ListFontStyle::list_font_style( $args );
	 *
	 * // Resulting CSS style:
	 * // .list {
	 * //     font-family: Arial;
	 * //     font-size: 14px;
	 * // }
	 * ```
	 */
	public static function list_font_style( array $args ) {
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

		if ( ! empty( $attr['list'] ) ) {
			$children_list = Utils::style_statements(
				[
					'selectors'            => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
					'selectorFunction'     => $selector_function,
					'propertySelectors'    => $property_selectors['list'] ?? [],
					'declarationFunction'  => function ( $params ) {
						return self::list_style_declaration( $params );
					},
					'attr'                 => $attr['list'],
					'important'            => is_bool( $important ) ? $important : ( $important['list'] ?? [] ),
					'orderClass'           => $order_class,
					'isInsideStickyModule' => $is_inside_sticky_module,
					'returnType'           => $args['returnType'],
				]
			);

			if ( $children_list && $return_as_array ) {
				array_push( $children, ...$children_list );
			} elseif ( $children_list ) {
				$children .= $children_list;
			}

			$children_lis_item = Utils::style_statements(
				[
					'selectors'            => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
					'selectorFunction'     => function( $params ) use ( $selector_function ) {
						if ( $selector_function ) {
							return call_user_func( $selector_function, $params ) . ' li';
						}

						if ( isset( $params['selector'] ) ) {
							return $params['selector'] . ' li';
						}

						return ' li';
					},
					'propertySelectors'    => $property_selectors['list'] ?? [],
					'declarationFunction'  => function ( $params ) {
						return self::list_item_style_declaration( $params );
					},
					'attr'                 => $attr['list'],
					'important'            => is_bool( $important ) ? $important : ( $important['list'] ?? [] ),
					'orderClass'           => $order_class,
					'isInsideStickyModule' => $is_inside_sticky_module,
					'returnType'           => $args['returnType'],
				]
			);

			if ( $children_lis_item && $return_as_array ) {
				array_push( $children, ...$children_lis_item );
			} elseif ( $children_lis_item ) {
				$children .= $children_lis_item;
			}
		}

		$children_font = FontStyle::style(
			[
				'selector'             => $selector,
				'selectors'            => $selectors,
				'selectorFunction'     => function( $params ) use ( $selector_function ) {
					if ( $selector_function ) {
						return call_user_func( $selector_function, $params ) . ' li';
					}

					if ( isset( $params['selector'] ) ) {
						return $params['selector'] . ' li';
					}

					return ' li';
				},
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

		return Utils::style_wrapper(
			[
				'attr'     => $attr,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

}
