<?php
/**
 * Module: DividersStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Dividers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\GlobalData\GlobalData;
use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\StyleLibrary\Declarations\Declarations;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * DividersStyle class.
 *
 * This class contains functionality to work with dividers styles.
 *
 * @since ??
 */
class DividersStyle {

	/**
	 * Get border style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BorderStyle BorderStyle} in
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
	 *     @type array         $featureSelectors         Optional. The feature selectors that you want to unpack. Default `[]`.
	 *     @type array         $attr                     An array of module attribute data.
	 *     @type array         $defaultPrintedStyleAttr  Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type array|bool    $important                Optional. Whether to apply "!important" flag to the style declarations.
	 *                                                   Default `false`.
	 *     @type bool          $asStyle                  Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                   Default `true`
	 *     @type bool          $fullwidth                Optional. Whether to apply fullwidth style or not. Default `false`.
	 *     @type string        $mode                     Optional. The mode of the style. Default `builder`.
	 *     @type string|null   $orderClass               Optional. The selector class name.
	 *     @type string        $returnType               Optional. This is the type of value that the function will return.
	 *                                                   Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The transform style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = TransformStyle::style( $args );
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
	 * $style = TransformStyle::style( $args );
	 * ```
	 */
	public static function style( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'propertySelectors' => [],
				'featureSelectors'  => null,
				'selector'          => null,
				'attr'              => null,
				'placement'         => 'top',
				'backgroundColors'  => null,
				'fullwidth'         => false,
				'important'         => false,
				'asStyle'           => true,
				'mode'              => 'builder',
				'orderClass'        => null,
				'returnType'        => 'array',
			]
		);

		$selector           = $args['selector'];
		$property_selectors = $args['propertySelectors'];
		$feature_selectors  = $args['featureSelectors'];
		$attr               = $args['attr'];
		$important          = $args['important'];
		$mode               = $args['mode'];
		$placement          = $args['placement'];
		$as_style           = $args['asStyle'];
		$order_class        = $args['orderClass'];
		$fullwidth          = $args['fullwidth'];
		$normalized_attr    = self::normalize_attr( $attr );

		// Bail, if noting is there to process.
		if ( empty( $normalized_attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		// Get the divider.
		$children = Utils::style_statements(
			[
				'selectors'           => $feature_selectors['dividers'][ $placement ] ?? [
					'desktop' => [
						'value'  => "{$selector} > .et_pb_{$placement}_inside_divider",
						'hover'  => "{$selector}.et_vb_hover > .et_pb_{$placement}_inside_divider",
						'sticky' => "{$selector}.et_pb_sticky > .et_pb_{$placement}_inside_divider",
					],
				],
				'propertySelectors'   => $property_selectors,
				'attr'                => $normalized_attr,
				'important'           => $important,
				'mode'                => $mode,
				'declarationFunction' => function ( $props ) use ( $fullwidth ) {
					// Below we check if the divider color value is a global color value i.e a CSS variable.
					// If it is a global color value, we get the HEX/RGBA color value from the global colors store.
					// We cannot use CSS variables as dynamic values in SVGs (usage in `Declarations::dividers_style_declaration()`).
					// So we need to replace the CSS variable with the actual color value.
					// see https://chatgpt.com/share/66fc3ee3-b810-8004-80e4-660256d8361c and  https://stackoverflow.com/a/42331003.
					$divider_color = $props['attrValue']['color'] ?? '';

					if ( ! empty( $divider_color ) ) {
						$global_color_id = GlobalData::get_global_color_id_from_value( $divider_color );

						if ( $global_color_id ) {
							$divider_color_value = GlobalData::get_global_color_by_id( $global_color_id )['color'] ?? '';

							if ( ! empty( $divider_color_value ) ) {
								$props['attrValue']['color'] = $divider_color_value;
							}
						}
					}

					$props['attrValue']['fullwidth'] = $fullwidth;

					return Declarations::dividers_style_declaration( $props );
				},
				'additionalArgs'      => [
					'backgroundColors' => $args['backgroundColors'],
					'placement'        => $placement,
				],
				'orderClass'          => $order_class,
				'returnType'          => $args['returnType'],
			]
		);

		return Utils::style_wrapper(
			[
				'attr'     => $normalized_attr,
				'asStyle'  => $as_style,
				'children' => $children,
			]
		);
	}

	/**
	 * Normalize the divider attributes.
	 *
	 * Some attributes are not available in all breakpoints and states. This function
	 * will normalize the attributes by filling the missing attributes with the
	 * inherited values.
	 *
	 * @since ??
	 *
	 * @param array $attr The array of attributes to be normalized.
	 * @return array The normalized array of attributes.
	 */
	public static function normalize_attr( array $attr ):array {
		$attr_normalized = $attr;

		if ( $attr_normalized ) {

			foreach ( $attr_normalized as $breakpoint => $states ) {
				foreach ( $states as $state => $values ) {
					if ( 'desktop' === $breakpoint && 'value' === $state ) {
						$attr_normalized[ $breakpoint ][ $state ] = $values;
					} else {
						$inherit = ModuleUtils::use_attr_value(
							[
								'attr'       => $attr,
								'breakpoint' => $breakpoint,
								'state'      => $state,
								'mode'       => 'getAndInheritAll',
							]
						);

						$attr_normalized[ $breakpoint ][ $state ] = array_merge(
							$inherit,
							$values
						);
					}
				}
			}
		}

		return $attr_normalized;
	}

}
