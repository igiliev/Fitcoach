<?php
/**
 * Module: ButtonIconStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Button;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;

/**
 * ButtonIconStyle class.
 *
 * This class handles button icon styles.
 *
 * @since ??
 */
class ButtonIconStyle {

	/**
	 * Get button icon style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ButtonIconStyle ButtonIconStyle} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector                 The CSS selector.
	 *     @type array         $selectors                Optional. An array of selectors for each breakpoint and state. Default `[]`.
	 *     @type array         $propertySelectors        Optional. The property selectors that you want to unpack. Default `[]`.
	 *     @type array         $attr                     An array of module attribute data.
	 *     @type array|bool    $important                Optional. Whether to apply "!important" flag to the style declarations.
	 *                                                   Default `false`.
	 *     @type bool          $asStyle                  Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                   Default `true`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The button icon style component.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'selector' => '.my-button-icon',
	 *         'selectors' => [
	 *             'desktop' => [
	 *                 'value' => '.my-button-icon-desktop'
	 *             ],
	 *             'tablet' => [
	 *                 'value' => '.my-button-icon-tablet'
	 *             ]
	 *         ],
	 *         'propertySelectors' => [
	 *             '.my-button-icon .property1',
	 *             '.my-button-icon .property2',
	 *         ],
	 *         'attr' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'icon' => [
	 *                         'placement' => 'right'
	 *                     ]
	 *                 ]
	 *             ],
	 *             'tablet' => [
	 *                 'value' => [
	 *                     'icon' => [
	 *                         'placement' => 'left'
	 *                     ]
	 *                 ]
	 *             ]
	 *         ],
	 *         'important' => true,
	 *             'asStyle' => false
	 *     ];
	 *
	 *     $iconStyle = IconStyleTrait::icon_style( $args );
	 *
	 *     return $iconStyle;
	 * ```
	 */
	public static function icon_style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'important'         => false,
				'asStyle'           => true,
				'orderClass'        => null,
				'returnType'        => 'array',
			]
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$property_selectors = $args['propertySelectors'];
		$attr               = $args['attr'];
		$important          = $args['important'];
		$as_style           = $args['asStyle'];
		$order_class        = $args['orderClass'];
		$return_as_array    = 'array' === $args['returnType'];
		$children           = $return_as_array ? [] : '';

		$default_printed_style_attr = $args['defaultPrintedStyleAttr'] ?? [];

		$icon_selectors = ! empty( $selectors ) ? $selectors : [ 'desktop' => [ 'value' => $selector ] ];

		$children_statements = Utils::style_statements(
			[
				'selectors'               => $icon_selectors,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $default_printed_style_attr,
				'important'               => $important,
				'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\ButtonIcon\ButtonIcon::style_declaration',
				'selectorFunction'        => function( $params ) {
					$params = wp_parse_args(
						$params,
						[
							'selector'   => null,
							'breakpoint' => null,
							'state'      => null,
						]
					);

					$selector   = $params['selector'];
					$breakpoint = $params['breakpoint'];
					$state      = $params['state'];
					$attr       = $params['attr'];

					$default_printed_style_attr = $params['defaultPrintedStyleAttr'] ?? [];

					$default_placement = 'right';
					$is_main           = 'desktop' === $breakpoint && 'value' === $state;
					$main_placement    = $attr['desktop']['value']['icon']['placement']
						?? $default_printed_style_attr['desktop']['value']['icon']['placement']
						?? $default_placement;

					$current_placement = $is_main
						? $main_placement
						: $attr[ $breakpoint ][ $state ]['icon']['placement']
						?? $default_printed_style_attr[ $breakpoint ][ $state ]['icon']['placement']
						?? $main_placement;

					if ( 'left' === $current_placement ) {
						return $selector . ':before';
					}

					return $selector . ':after';
				},
				'orderClass'              => $order_class,
				'returnType'              => $args['returnType'],
			]
		);

		if ( $children_statements && $return_as_array ) {
			array_push( $children, ...$children_statements );
		} elseif ( $children_statements ) {
			$children .= $children_statements;
		}

		$children_hover = Utils::style_statements(
			[
				'selectors'               => $icon_selectors,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $default_printed_style_attr,
				'important'               => $important,
				'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\ButtonIcon\ButtonIcon::hover_style_declaration',
				'selectorFunction'        => function( $params ) {
					$params = wp_parse_args(
						$params,
						[
							'selector'   => null,
							'breakpoint' => null,
							'state'      => null,
						]
					);

					$selector   = $params['selector'];
					$breakpoint = $params['breakpoint'];
					$state      = $params['state'];
					$attr       = $params['attr'];

					$default_printed_style_attr = $params['defaultPrintedStyleAttr'];

					$default_placement = 'right';
					$is_main           = 'desktop' === $breakpoint && 'value' === $state;
					$main_placement    = $attr['desktop']['value']['icon']['placement']
						?? $default_printed_style_attr['desktop']['value']['icon']['placement']
						?? $default_placement;
					$current_placement = $is_main
						? $main_placement
						: $attr[ $breakpoint ][ $state ]['icon']['placement']
						?? $default_printed_style_attr[ $breakpoint ][ $state ]['icon']['placement']
						?? $main_placement;

					if ( 'left' === $current_placement ) {
						// TODO feat(D5, Module Styles): Avoid adding double :hover to the selector
						// @see https://github.com/elegantthemes/Divi/issues/33635.
						return false !== strpos( $selector, ':hover' )
							? $selector . ':before'
							: $selector . ':hover:before';
					}

					// TODO feat(D5, Module Styles): Avoid adding double :hover to the selector
					// @see https://github.com/elegantthemes/Divi/issues/33635.
					return false !== strpos( $selector, ':hover' )
						? $selector . ':after'
						: $selector . ':hover:after';
				},
				'orderClass'              => $order_class,
				'returnType'              => $args['returnType'],
			]
		);

		if ( $children_hover && $return_as_array ) {
			array_push( $children, ...$children_hover );
		} elseif ( $children_hover ) {
			$children .= $children_hover;
		}

		$children_right = Utils::style_statements(
			[
				'selectors'               => $icon_selectors,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $default_printed_style_attr,
				'important'               => $important,
				'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\ButtonIcon\ButtonIcon::right_style_declaration',
				'selectorFunction'        => function( $params ) {
					$params = wp_parse_args(
						$params,
						[
							'selector' => null,
						]
					);

					$selector = $params['selector'];

					return $selector . ':after';
				},
				'orderClass'              => $order_class,
				'returnType'              => $args['returnType'],
			]
		);

		if ( $children_right && $return_as_array ) {
			array_push( $children, ...$children_right );
		} elseif ( $children_right ) {
			$children .= $children_right;
		}

		$children_disable = Utils::style_statements(
			[
				'selectors'               => $icon_selectors,
				'propertySelectors'       => $property_selectors,
				'attr'                    => $attr,
				'defaultPrintedStyleAttr' => $default_printed_style_attr,
				'important'               => $important,
				'declarationFunction'     => '\ET\Builder\Packages\StyleLibrary\Declarations\ButtonIcon\ButtonIcon::disable_style_declaration',
				'selectorFunction'        => function( $params ) {
					$params = wp_parse_args(
						$params,
						[
							'selector' => null,
						]
					);

					$selector = $params['selector'];

					return implode( ',', [ $selector . ':before', $selector . ':after' ] );
				},
				'orderClass'              => $order_class,
				'returnType'              => $args['returnType'],
			]
		);

		if ( $children_disable && $return_as_array ) {
			array_push( $children, ...$children_disable );
		} elseif ( $children_disable ) {
			$children .= $children_disable;
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
