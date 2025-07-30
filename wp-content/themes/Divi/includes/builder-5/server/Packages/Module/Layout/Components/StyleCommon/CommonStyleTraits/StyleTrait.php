<?php
/**
 * CommonStyle::style()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait StyleTrait {

	/**
	 * Render custom CSS.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/CommonStyle CommonStyle}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector             The CSS selector.
	 *     @type array         $attr                 An array of module attribute data.
	 *     @type string        $property             Optional. CSS Property. Default empty string.
	 *     @type array|boolean $important            Optional. Whether to add `!important` to the declaration. Default `false`.
	 *     @type bool          $asStyle              Optional. Flag to wrap the style declaration with style tag. Default `true`.
	 *     @type callable      $selectorFunction     Optional. The function to be called to generate CSS selector. Default `null`.
	 *     @type array         $declarationFunction  Optional. The function to be called to generate CSS declaration. Default `null`.
	 *     @type string|null   $orderClass           Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string        $returnType           Optional. This is the type of value that the function will return.
	 *                                               Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'               => [],
				'important'               => false,
				'asStyle'                 => true,
				'property'                => '',
				'selectorFunction'        => null,
				'declarationFunction'     => null,
				'orderClass'              => null,
				'returnType'              => 'array',
				'defaultPrintedStyleAttr' => [],
				'atRules'                 => '',
			]
		);

		$selector                   = $args['selector'];
		$selectors                  = $args['selectors'];
		$attr                       = $args['attr'];
		$default_printed_style_attr = $args['defaultPrintedStyleAttr'];
		$property                   = $args['property'];
		$important                  = $args['important'];
		$as_style                   = $args['asStyle'];
		$selector_function          = $args['selectorFunction'];
		$declaration_function       = $args['declarationFunction'];
		$order_class                = $args['orderClass'];
		$at_rules                   = $args['atRules'];

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		$children = Utils::style_statements(
			[
				'important'               => $important,
				'selectors'               => ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ],
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $default_printed_style_attr,
				'declarationFunction'     => function( $params ) use ( $declaration_function, $property ) {
					if ( is_callable( $declaration_function ) ) {
						return call_user_func( $declaration_function, $params );
					}

					$style_declarations = new StyleDeclarations(
						[
							'returnType' => 'string',
							'important'  => $params['important'],
						]
					);

					$style_declarations->add( $property, $params['attrValue'] );

					return $style_declarations->value();
				},
				'selectorFunction'        => $selector_function,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
				'atRules'                 => $at_rules,
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
