<?php
/**
 * Module: IconStyle class.
 *
 * @package Builder\FrontEnd
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Icon;

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * `IconStyle`
 *
 * @since ??
 */
class IconStyle {

	/**
	 * Get icon styles.
	 *
	 * This function is equivalent of JS function ButtonIconStyle located in
	 * visual-builder/packages/module/src/options/icon/style/component.tsx.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector                    The CSS selector.
	 *     @type array         $selectors                   Optional. An array of selectors for each breakpoint and state.
	 *     @type callable      $selectorFunction            Optional. The function to be called to generate CSS selector.
	 *     @type array         $propertySelectors           Optional. The property selectors that you want to unpack.
	 *     @type array         $attr                        An array of module attribute data.
	 *     @type array         $default_printed_style_attr  Optional. An array of default printed style attribute data.
	 *     @type array|boolean $important                   Optional. The important statement.
	 *     @type bool          $asStyle                     Optional. Flag to wrap the style declaration with style tag.
	 *     @type string|null   $orderClass                  Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule        Optional. Flag to check if the module is inside a sticky module.
	 *     @type string        $attrs_json                  Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string        $returnType                  Optional. This is the type of value that the function will return.
	 *                                                      Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array
	 */
	public static function style( $args ) {
		$selector                   = $args['selector'];
		$selectors                  = $args['selectors'] ?? [];
		$selector_function          = $args['selectorFunction'] ?? null;
		$property_selectors         = $args['propertySelectors'] ?? [];
		$attr                       = $args['attr'];
		$important                  = $args['important'] ?? false;
		$default_printed_style_attr = $args['defaultPrintedStyleAttr'] ?? [];
		$as_style                   = $args['asStyle'] ?? true;
		$order_class                = $args['orderClass'] ?? null;
		$is_inside_sticky_module    = $args['isInsideStickyModule'] ?? false;
		$return_type                = $args['returnType'] ?? 'array';
		$at_rules                   = $args['atRules'] ?? '';

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $return_type ? [] : '';
		}

		// Go through, and inherit icon style attributes when needed.
		$attr_value_with_inherited = ModuleUtils::get_and_inherit_icon_style_attr(
			[
				'attr' => $attr,
			]
		);

		$children = Utils::style_statements(
			[
				'selectors'               => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'selectorFunction'        => $selector_function,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr_value_with_inherited,
				'defaultPrintedStyleAttr' => $default_printed_style_attr,
				'important'               => $important,
				'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\Icon\Icon::style_declaration',
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $return_type,
				'atRules'                 => $at_rules,
			]
		);

		return Utils::style_wrapper(
			[
				'attr'     => $attr_value_with_inherited,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

}
