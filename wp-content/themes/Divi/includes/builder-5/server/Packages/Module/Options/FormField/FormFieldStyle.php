<?php
/**
 * Module: FormFieldStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\FormField;

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\Element\ElementStyle;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * FormFieldStyle class.
 *
 * This class is responsible for applying styles to form fields.
 *
 * @since ??
 */
class FormFieldStyle {

	/**
	 * Get form-field styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/FormFieldStyle FormFieldStyle} in
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
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The form-field style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = self::style( $args );
	 *
	 * // Apply style with specific selectors and properties.
	 * $args = [
	 *     'selectors' => [
	 *         '.element1',
	 *         '.element2',
	 *     ],
	 *     'propertySelectors' => [
	 *         '.element1 .property1',
	 *         '.element2 .property2',
	 *     ]
	 * ];
	 * $style = self::style( $args );
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
		$use_focus_border   = $attr['advanced']['focusUseBorder']['desktop']['value'] ?? 'off';
		$order_class        = $args['orderClass'];
		$return_as_array    = 'array' === $args['returnType'];
		$children           = $return_as_array ? [] : '';

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$element_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'attrs'      => $attr['decoration'] ?? [],
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'background' => [
					'selectors'         => $selectors,
					'selectorFunction'  => $selector_function,
					'propertySelectors' => $property_selectors['background'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['background'] ?? false ),
				],
				'border'     => [
					'selectors'         => $selectors,
					'selectorFunction'  => $selector_function,
					'propertySelectors' => $property_selectors['border'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['border'] ?? false ),
				],
				'boxShadow'  => [
					'selectors'         => $selectors,
					'selectorFunction'  => $selector_function,
					'propertySelectors' => $property_selectors['boxShadow'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['boxShadow'] ?? false ),
				],
				'font'       => [
					'selectors'         => $selectors,
					'selectorFunction'  => $selector_function,
					'propertySelectors' => $property_selectors['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['font'] ?? false ),
				],
				'spacing'    => [
					'selectors'         => $selectors,
					'selectorFunction'  => $selector_function,
					'propertySelectors' => $property_selectors['spacing'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['spacing'] ?? false ),
				],
			]
		);

		if ( $element_style && $return_as_array ) {
			array_push( $children, ...$element_style );
		} elseif ( $element_style ) {
			$children .= $element_style;
		}

		// Focus Style.
		$element_focus_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'background' => $attr['advanced']['focus']['background'] ?? [],
					'border'     => 'on' === $use_focus_border ? $attr['advanced']['focus']['border'] ?? [] : [],
					'font'       => $attr['advanced']['focus']['font'] ?? [],
				],
				'background' => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['focus']['background'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['focus']['background'] ?? false ),
				],
				'border'     => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['focus']['border'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['focus']['border'] ?? false ),
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['focus']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['focus']['font'] ?? false ),
				],
			]
		);

		if ( $element_focus_style && $return_as_array ) {
			array_push( $children, ...$element_focus_style );
		} elseif ( $element_focus_style ) {
			$children .= $element_focus_style;
		}

		// ::*placeholder style can't handle multiple selectors used the same statements.
		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['placeholder']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . '::placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['placeholder']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . '::-webkit-input-placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['placeholder']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . '::-moz-placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['placeholder']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . '::-ms-input-placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		// more placeholder styles to cover focus placeholders.

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['focus']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus::placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['focus']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus::-webkit-input-placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['focus']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus::-moz-placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
		}

		$element_placeholder_style = ElementStyle::style(
			[
				'selector'   => $selector,
				'orderClass' => $order_class,
				'returnType' => $args['returnType'],
				'attrs'      => [
					'font' => $attr['advanced']['focus']['font'] ?? [],
				],
				'font'       => [
					'selectorFunction'  => function ( $params ) use ( $selector_function ) {
						$maybe_multiple_selectors = is_callable( $selector_function ) ? call_user_func( $selector_function, $params ) : ( $params['selector'] ?? '' );
						$splitted_selectors       = explode( ',', $maybe_multiple_selectors );

						$modified_selectors = array_map(
							function ( $splitted_selector ) {
								return rtrim( $splitted_selector ) . ':focus::-ms-input-placeholder';
							},
							$splitted_selectors
						);

						return implode( ',', $modified_selectors );
					},
					'propertySelectors' => $property_selectors['placeholder']['font'] ?? [],
					'important'         => is_bool( $important ) ? $important : ( $important['placeholder']['font'] ?? false ),
				],
			]
		);

		if ( $element_placeholder_style && $return_as_array ) {
			array_push( $children, ...$element_placeholder_style );
		} elseif ( $element_placeholder_style ) {
			$children .= $element_placeholder_style;
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
