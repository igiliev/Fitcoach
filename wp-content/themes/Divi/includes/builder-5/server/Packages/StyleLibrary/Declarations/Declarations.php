<?php
/**
 * ModuleStyleLibrary\Declarations class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;
use ET\Builder\Packages\StyleLibrary\Declarations\Border\Border;
use ET\Builder\Packages\StyleLibrary\Declarations\BoxShadow\BoxShadow;
use ET\Builder\Packages\StyleLibrary\Declarations\ButtonIcon\ButtonIcon;
use ET\Builder\Packages\StyleLibrary\Declarations\Button\Button;
use ET\Builder\Packages\StyleLibrary\Declarations\Custom\Custom;
use ET\Builder\Packages\StyleLibrary\Declarations\DisabledOn\DisabledOn;
use ET\Builder\Packages\StyleLibrary\Declarations\Dividers\Dividers;
use ET\Builder\Packages\StyleLibrary\Declarations\Filters\Filters;
use ET\Builder\Packages\StyleLibrary\Declarations\Font\Font;
use ET\Builder\Packages\StyleLibrary\Declarations\Icon\Icon;
use ET\Builder\Packages\StyleLibrary\Declarations\Layout\Layout;
use ET\Builder\Packages\StyleLibrary\Declarations\Order\Order;
use ET\Builder\Packages\StyleLibrary\Declarations\Overflow\Overflow;
use ET\Builder\Packages\StyleLibrary\Declarations\OverlayIcon\OverlayIcon;
use ET\Builder\Packages\StyleLibrary\Declarations\Position\Position;
use ET\Builder\Packages\StyleLibrary\Declarations\Sizing\Sizing;
use ET\Builder\Packages\StyleLibrary\Declarations\Spacing\Spacing;
use ET\Builder\Packages\StyleLibrary\Declarations\TextShadow\TextShadow;
use ET\Builder\Packages\StyleLibrary\Declarations\Text\Text;
use ET\Builder\Packages\StyleLibrary\Declarations\ZIndex\ZIndex;


/**
 * Declarations is a helper class to for working with the style library.
 *
 * @since ??
 */
class Declarations {

	use Transform\TransformHoveredStyleDeclarationTrait;
	use Transform\TransformStyleDeclarationTrait;

	/**
	 * Get background's CSS declaration based on given attrValue.
	 *
	 * This is a wrapper function for `Background::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/background-style-declaration backgroundStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType Optional. This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 *     @type string     $keyFormat  Optional. This is the format of the key that the function will return.
	 *                                  Default `param-case`.
	 * }
	 *
	 * @return string|array
	 */
	public static function background_style_declaration( array $args ) {
		return Background::style_declaration( $args );
	}

	/**
	 * Get border's CSS declaration based on given attrValue.
	 *
	 * This is a wrapper functon for `Border::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/border-style-declaration borderStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function border_style_declaration( array $args ) {
		return Border::style_declaration( $args );
	}

	/**
	 * Get Box Shadow's CSS declaration based on given attrValue.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/box-shadow-declaration boxShadowDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Whether to add `!important` tag.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`.
	 * }
	 *
	 * @return array|string
	 */
	public static function box_shadow_style_declaration( array $args ) {
		return BoxShadow::style_declaration( $args );
	}

	/**
	 * Get Button Icon's Hover CSS declaration based on given placement.
	 *
	 * This is a wrapper function for `ButtonIcon::hover_style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-icon-hover-style-declaration buttonIconHoverStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function button_icon_hover_style_declaration( array $args ) {
		return ButtonIcon::hover_style_declaration( $args );
	}

	/**
	 * Get Button Icon's CSS declaration based on given attrValue.
	 *
	 * This is a wrapper function for `ButtonIcon::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-icon-style-declaration buttonStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.'
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function button_icon_style_declaration( array $args ) {
		return ButtonIcon::style_declaration( $args );
	}

	/**
	 * Hide Button Right Icon only if the placement is set to the left.
	 *
	 * This function is a wrapper function for `ButtonIcon::right_style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-right-icon-style-declaration buttonRightIconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function button_right_icon_style_declaration( array $args ) {
		return ButtonIcon::right_style_declaration( $args );
	}

	/**
	 * Disable the icon if `Show Button Icon` is set to the `false`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/disable-button-icon-style-declaration disableButtonIconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.'
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function disable_button_icon_style_declaration( array $args ) {
		return ButtonIcon::disable_style_declaration( $args );
	}

	/**
	 * Get button's CSS declaration based on given attrValue.
	 *
	 * This is a wrapper function for `Button::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-style-declaration buttonStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function button_style_declaration( array $args ) {
		return Button::style_declaration( $args );
	}

	/**
	 * Get custom CSS declaration based on given properties.
	 *
	 * This is a wrapper function for `Custom::style_declaration`.å
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/custom-style-declaration customStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $property  The CSS declaration property.
	 *     @type string $value     The CSS declaration value.
	 *     @type bool   $important Optional. Whether to add `!important` tag. Default `false`.
	 * }
	 *
	 * @return array|string
	 */
	public static function custom_style_declaration( array $args ) {
		return Custom::style_declaration( $args );
	}

	/**
	 * Get disabled on CSS declaration based on given attrValue.
	 *
	 * This is a wrapper for `DisabledOn::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/disabled-on-style-declaration disabledOnStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attrValue                 The value (breakpoint > state > value) of module attribute.
	 *     @type string $disabledModuleVisibility Optional. Disabled module visibility.
	 *                                            One of `transparent` or `hidden`. Default `hidden`.
	 *     @type string $returnType               This is the type of value that the function will return.
	 *                                            Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function disabled_on_style_declaration( array $args ) {
		return DisabledOn::style_declaration( $args );
	}

	/**
	 * Get Dividers CSS declaration based on given attrValue.
	 *
	 * This is a wrapper function for: `Dividers::style_declaration`.
	 *
	 * This function is equivalent of JS function
	 * {@link /docs/builder-api/js/style-library/dividers-style-declaration dividersStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attrValue      The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string $returnType    This is the type of value that the function will return.
	 *                                 Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @throws Exception If divider style is not found by `DividersUtils::get_divider_json()`.
	 *
	 * @return array|string
	 **/
	public static function dividers_style_declaration( array $args ) {
		return Dividers::style_declaration( $args );
	}

	/**
	 * Get Filter's CSS declaration based on given attrValue.
	 *
	 * This function is a wrapper for `Filters::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/filters-style-declaration filtersStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType Optional. This is the type of value that the function will return.
	 *                                  Can be either string or key_value_pair. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function filters_style_declaration( array $args ) {
		return Filters::style_declaration( $args );
	}

	/**
	 * Get Font's CSS declaration based on given attrValue.
	 *
	 * This is a wrapper function for `Font::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/font-style-declaration fontStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType Optional. This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function font_style_declaration( array $args ) {
		return Font::style_declaration( $args );
	}

	/**
	 * Get Icon's CSS declaration based on given attrValue.
	 *
	 * This is a wrapper function for `Icon::style_declaration` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/icon-style-declaration iconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.'
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function icon_style_declaration( array $args ) {
		return Icon::style_declaration( $args );
	}

	/**
	 * Generate CSS declaration for layout style based on the given arguments.
	 *
	 * This function generates the style declaration for layout properties based on the given arguments.
	 *
	 * This function calls `Layout::style_declaration()` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/layout-style-declaration/ layoutStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for generating the style declaration.
	 *
	 *     @type string     $attrValue   The attribute value for layout properties.
	 *     @type bool|array $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string     $returnType  Optional. The desired return type of the style declaration. Default `string`.
	 *                                   One of `string`, or `key_value_pair`
	 *                                     - If `string`, the style declaration will be returned as a string.
	 *                                     - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return string|array The generated style declaration based on the provided arguments.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue'   => 'value',
	 *     'important'   => true,
	 *     'returnType'  => 'array',
	 * ];
	 * $styleDeclaration = Declarations::layout_style_declaration($args);
	 * ```
	 */
	public static function layout_style_declaration( array $args ) {
		return Layout::style_declaration( $args );
	}

	/**
	 * Get order CSS declaration based on given arguments.
	 *
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This is a wrapper function for `Order::style_declaration()`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/order-style-declaration/ orderStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated order CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue'   => '2', // The order value.
	 *     'important'   => true, // Whether the declaration should be marked as important.
	 *     'returnType'  => 'key_value_pair', // The return type of the style declaration.
	 * ];
	 * $style = Declarations::order_style_declaration($args);
	 * ```
	 */
	public static function order_style_declaration( array $args ) {
		return Order::style_declaration( $args );
	}

	/**
	 * Generate CSS declaration for overflow style based on the given arguments.
	 *
	 * This function calls `Overflow::style_declaration()` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/overflow-style-declaration/ overflowStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for generating the style declaration.
	 *
	 *     @type string     $attrValue   The attribute value for `overflow-x` and `overflow-y`.
	 *     @type bool|array $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string     $returnType  Optional. The desired return type of the style declaration. Default `string`.
	 *                                   One of `string`, or `key_value_pair`
	 *                                     - If `string`, the style declaration will be returned as a string.
	 *                                     - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return string|array The generated style declaration based on the provided arguments.
	 *
	 * @example:
	 * ```php
	 *     // Example usage of the overflow_style_declaration() function:
	 *     $args = [
	 *         'attrValue'   => [
	 *             'x' => 'hidden',
	 *             'y' => 'visible',
	 *         ],
	 *         'important'   => true,
	 *         'returnType'  => 'array',
	 *     ];
	 *
	 *     $style_declaration = Overflow::overflow_style_declaration( $args );
	 *
	 *     // The resulting style declaration will be:
	 *     // [
	 *     //    [overflow-x] => hidden !important
	 *     //    [overflow-y] => visible !important
	 *     //]
	 * ```
	 */
	public static function overflow_style_declaration( array $args ) {
		return Overflow::style_declaration( $args );
	}

	/**
	 * Generate overlay icon CSS declaration.
	 *
	 * This function takes an array of arguments and generates a style declaration string
	 * based on the provided arguments. The generated style declaration can be used
	 * to apply CSS styles to elements.
	 *
	 * This function calls `OverlayIcon::style_declaration()` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/overflow-icon-style-declaration/ overlayIconStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string      $attrValue   The attribute value.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 *
	 * }
	 *
	 * @return array|string The generated style declaration.
	 *
	 * @example:
	 * ```php
	 *   $args = [
	 *       'attrValue'   => 'value',
	 *       'important'   => true,
	 *       'returnType'  => 'array',
	 *   ];
	 *   $styleDeclaration = Declarations::style_declaration( $args );
	 * ```
	 */
	public static function overlay_icon_style_declaration( array $args ) {
		return OverlayIcon::style_declaration( $args );
	}

	/**
	 * Generate position CSS style declarations based on the provided arguments.
	 *
	 * This function calls `Position::style_declaration()` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/position-style-declaration/ positionStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for generating style declarations.
	 *
	 *     @type string $attrValue         The value (`breakpoint > state > value`) of module attribute.
	 *     @type bool   $important         Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string $returnType        Optional. The return type of the style declaration. Default `string`.
	 *                                     One of `string`, or `key_value_pair`
	 *                                       - If `string`, the style declaration will be returned as a string.
	 *                                       - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 *     @type array  $defaultAttrValue  {
	 *         An array defining the default attribute values.
	 *
	 *         @type string    $mode     The default mode value. One of `default`, `relative`, `absolute`, or `fixed`. Default 'default'.
	 *         @type array     $offset   {
	 *             The default offset values.
	 *
	 *             @type string $horizontal The default horizontal offset value. Default `0px`.
	 *             @type string $vertical   The default vertical offset value. Default `0px`.
	 *         }
	 *     }
	 * }
	 *
	 * @return array|string The generated position style declarations.
	 *
	 * @example:
	 * ```php
	 * // Generate style declarations with default arguments.
	 * $args = [
	 *     'attrValue'        => 'value',
	 *     'important'        => false,
	 *     'returnType'       => 'string',
	 *     'defaultAttrValue' => [
	 *         'mode'   => 'default',
	 *         'offset' => ['horizontal' => '0px', 'vertical' => '0px'],
	 *     ],
	 * ];
	 *
	 * $styleDeclarations = Declarations::position_style_declaration($args);
	 * ```
	 */
	public static function position_style_declaration( array $args ) {
		return Position::style_declaration( $args );
	}

	/**
	 * Get sizing CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This is a wrapper function for `Sizing::style_declaration()`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/sizing-style-declaration/ sizingStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated sizing CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue'   => ['orientation' => 'center'], // The attribute value.
	 *     'important'   => true,                        // Whether the declaration should be marked as important.
	 *     'returnType'  => 'key_value_pair',            // The return type of the style declaration.
	 * ];
	 * $style = Declarations::sizing_style_declaration($args);
	 * ```
	 */
	public static function sizing_style_declaration( array $args ) {
		return Sizing::style_declaration( $args );
	}

	/**
	 * Get spacing CSS declaration based on given arguments.
	 *
	 * This function generates style declarations based on the provided arguments.
	 *
	 * This function is a wrapper function for ` Spacing::style_declaration()`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/spacing-style-declaration/ spacingStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated CSS style declarations.
	 *
	 * @example:
	 * ```php
	 *     // Generate style declarations with default arguments.
	 *     $style_declarations = Declarations::spacing_style_declaration([
	 *         'important'  => false,
	 *         'returnType' => 'string',
	 *         'attrValue'  => [
	 *             'margin'  => [
	 *                 'top'    => '10px',
	 *                 'right'  => '20px',
	 *                 'bottom' => '10px',
	 *                 'left'   => '20px',
	 *             ],
	 *             'padding' => [
	 *                 'top'    => '5px',
	 *                 'right'  => '10px',
	 *                 'bottom' => '5px',
	 *                 'left'   => '10px',
	 *             ],
	 *         ],
	 *     ]);
	 *
	 *     echo $style_declarations;
	 *
	 *     // Output: 'margin-top: 10px; margin-right: 20px; margin-bottom: 10px; margin-left: 20px; padding-top: 5px; padding-right: 10px; padding-bottom: 5px; padding-left: 10px;'
	 * ```
	 */
	public static function spacing_style_declaration( array $args ) {
		return Spacing::style_declaration( $args );
	}

	/**
	 * Get text-shadow CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This is a wrapper function for `TextShadow::style_declaration()`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/text-shadow-style-declaration/ textShadowStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Whether to add `!important` to the CSS.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated text-shadow CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue' => '#ff0000',
	 *     'important' => true,
	 *     'returnType' => 'string',
	 * ];
	 * $declaration = Declarations::style_declaration( $args );
	 *
	 * // Result: "text-shadow: #ff0000 !important;"
	 * ```
	 */
	public static function text_shadow_style_declaration( array $args ) {
		return TextShadow::style_declaration( $args );
	}

	/**
	 * Get text CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This function calls `Text::style_declaration()` function.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/text-style-declaration/ textStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The attribute value.
	 *     @type array|bool  $important   Optional. Whether to add `!important` to the CSS. Default `false`.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated text CSS style declaration.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'attrValue'   => ['orientation' => 'center'], // The attribute value.
	 *     'important'   => true,                        // Whether the declaration should be marked as important.
	 *     'returnType'  => 'key_value_pair',            // The return type of the style declaration.
	 * ];
	 * $style = Declarations::text_style_declaration($args);
	 * ```
	 */
	public static function text_style_declaration( $args ) {
		return Text::style_declaration( $args );
	}

	/**
	 * Get z-index CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This is a wrapper function for `ZIndex::style_declaration()`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/z-index-style-declaration/ zIndexStyleDeclaration}
	 * located in `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments that define the style declaration.
	 *
	 *     @type string      $attrValue   The value (breakpoint > state > value) of module attribute.
	 *     @type array|bool  $important   Whether to add `!important` to the CSS.
	 *     @type string      $returnType  Optional. The return type of the style declaration. Default `string`.
	 *                                    One of `string`, or `key_value_pair`
	 *                                      - If `string`, the style declaration will be returned as a string.
	 *                                      - If `key_value_pair`, the style declaration will be returned as an array of key-value pairs.
	 * }
	 *
	 * @return array|string The generated z-index CSS style declaration.
	 */
	public static function z_index_style_declaration( array $args ) {
		return ZIndex::style_declaration( $args );
	}

}
