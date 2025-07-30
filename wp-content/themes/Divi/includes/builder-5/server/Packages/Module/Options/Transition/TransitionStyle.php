<?php
/**
 * Module: TransitionStyle class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Transition;

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Sizing\SizingStyle;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Spacing\SpacingStyle;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;
use ET\Builder\Packages\StyleLibrary\Declarations\Dividers\Dividers;
use ET\Builder\Packages\StyleLibrary\Declarations\TextShadow\TextShadow;
use ET\Builder\Packages\StyleLibrary\Declarations\Transition\Transition;
use ET\Builder\Packages\StyleLibrary\Declarations\Transition\TransitionUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TransitionStyle class.
 *
 * @since ??
 */
class TransitionStyle {

	/**
	 * Get Transition style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/TransitionStyle TransitionStyle} in
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
	 *     @type array         $attrs                    An array of all attributes for a module.
	 *     @type array|boolean $important                Optional. Whether to apply "!important" flag to the style declarations.
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
	 * @return string|array The transition style component.
	 *                If there are no `hover` or `sticky` styles, an empty string is returned.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'selectors'         => ['.class1', '#id1'],
	 *         'propertySelectors' => ['color', 'background-color'],
	 *         'selectorFunction'  => 'my_selector_function',
	 *         'important'         => true,
	 *         'asStyle'           => false,
	 *     ];
	 *     self::style( $args );
	 * ```
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'selectors' => ['.class2', '#id2'],
	 *         'asStyle'   => true,
	 *     ];
	 *     self::style( $args );
	 * ```
	 */
	public static function style( array $args ) {
		$args = array_replace_recursive(
			[
				'selectors'         => [],
				'propertySelectors' => [],
				'selectorFunction'  => null,
				'important'         => false,
				'asStyle'           => true,
				'orderClass'        => null,
				'attrs_json'        => null,
				'returnType'        => 'array',
			],
			$args
		);

		$selector           = $args['selector'];
		$selectors          = $args['selectors'];
		$selector_function  = $args['selectorFunction'];
		$property_selectors = $args['propertySelectors'];
		$attr               = $args['attr'] ?? [];
		$important          = $args['important'];
		$as_style           = $args['asStyle'];
		$advanced_styles    = $args['advancedStyles'] ?? [];
		$order_class        = $args['orderClass'];

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		$attrs = $args['transitionData']['attrs'] ?? [];
		$props = $args['transitionData']['props'] ?? [];

		$main_selector              = $selector;
		$return_as_array            = 'array' === $args['returnType'];
		$children                   = $return_as_array ? [] : '';
		$children_heading_tags      = [];
		$heading_tags_child         = $return_as_array ? [] : '';
		$children_body_tags         = [];
		$body_tags_child            = $return_as_array ? [] : '';
		$advanced_transition_styles = [];
		$advanced_tags_child        = $return_as_array ? [] : '';
		$all_selectors              = [];

		// Bail early if both of `attrs` and `advanced_styles` are empty because nothing to process. In VB, there is no
		// check like this since the `attrs` are always set.
		if ( empty( $attrs ) && empty( $advanced_styles ) ) {
			return $children;
		}

		// Split the main selector to later get the transitions for already added styles.
		$main_selectors = explode( ',', $main_selector );

		// Default transition attributes.
		$duration_default_value    = '300ms';
		$delay_default_value       = '0ms';
		$speed_curve_default_value = 'ease';

		// Process and get transitions css properties for AdvancedStyles.
		$advanced_styles_transitions = self::get_advanced_transition_styles( $advanced_styles, $selector );

		// If attrs_json is provided use that, otherwise JSON encode the attributes array.
		$attrs_json = null === $args['attrs_json'] ? wp_json_encode( $attrs ) : $args['attrs_json'];

		// JSON encode the attributes array for faster search using strpos and avoid any loops.
		$hover  = (bool) strpos( $attrs_json, 'hover' ); // Check if a module attribute has hover state.
		$sticky = (bool) strpos( $attrs_json, 'sticky' ); // Check if a module attribute has sticky state.

		// Set initial transition attribute.
		$transition_attr  = [];
		$attr_breakpoints = ! empty( $attr ) ? array_keys( $attr ) : [ 'desktop' ];
		foreach ( $attr_breakpoints as $breakpoint ) {
			$transition_attr[ $breakpoint ] = [
				'value' => [
					'hover'              => $hover,
					'sticky'             => $sticky,
					'moduleAttrs'        => [],
					'advancedProperties' => [],
					'duration'           => $attr[ $breakpoint ]['value']['duration'] ?? $duration_default_value,
					'delay'              => $attr[ $breakpoint ]['value']['delay'] ?? $delay_default_value,
					'speedCurve'         => $attr[ $breakpoint ]['value']['speedCurve'] ?? $speed_curve_default_value,
				],
			];
		}

		// TODO: fix(D5, Advanced Styles Transition) Consider to remove this since we're going merge all process.
		// @see https://github.com/elegantthemes/Divi/issues/39774
		// Get the transitions for already added styles with common selector between
		// main selector and selector from advanced and merge the results so
		// we can have a common transition style added to the selector.
		if ( ! empty( $advanced_styles_transitions ) ) {
			foreach ( $main_selectors as $each_selector ) {
				$each_selector = trim( $each_selector );
				if ( isset( $advanced_styles_transitions[ $each_selector ] ) ) {
					// Set `advancedProperties` to all breakpoints of transition attributes.
					foreach ( $transition_attr as $breakpoint => $states ) {
						$transition_attr[ $breakpoint ]['value']['advancedProperties'] = $advanced_styles_transitions[ $each_selector ];
					}

					// Add main selectors with advanced properties to the list of all selectors to make sure we don't miss them.
					// This may not be needed later once we handle advanced styles.
					if ( ! in_array( $each_selector, $all_selectors, true ) ) {
						$all_selectors[] = $each_selector;
					}
				}
			}
		}

		// For element styles with sub-selectors, the processed selectors from `selectors`, `propertySelectors`, and
		// `selectorFunction` shouldn't be added directly to the list of all selectors because they have a special handling
		// to generate base and sub-selectors combination based on the processed selectors. The sub-selectors info is
		// not available in the prop, so we need to check it manually just like how related style components do it. We
		// need to ensure only the generated base and sub-selectors combination are added to the list of all selectors.
		$styles_with_sub_selectors_list = [
			'bodyFont',
			'headingFont',
		];

		// 1. Process any element styles.
		// Unlike the other style components, the `TransitionStyle` need to collect all styles props and extract them to get
		// the selectors. So, the transition styles can be applied to the correct elements. The list are generated from the
		// prop main selector (`selectors` and `selector`), `propertySelectors`, and `selectorFunction`. For the prop main
		// selector, we use the `selector` of `TransitionStyle` as the fallback selector because some element styles don't
		// have specific selectors defined and the `selector` is the main selector fallback of the element.
		// Set `moduleAttrs` to all breakpoints of transition attributes.
		foreach ( $transition_attr as $breakpoint => $states ) {
			$transition_attr[ $breakpoint ]['value']['moduleAttrs'] = [];
		}

		foreach ( $props as $prop_key => $prop ) {
			$prop_attr = $attrs[ $prop_key ] ?? [];

			// Bail early if the attr doesn't have `hover` or `sticky` states.
			if ( ! self::has_multi_state_attr( $prop_attr ) ) {
				continue;
			}

			// The flag whether the prop has sub-selectors or not.
			$has_sub_selectors = in_array( $prop_key, $styles_with_sub_selectors_list, true );

			// 1.a. Element style prop `selectors` or `selector`.
			// The `selectors` and `selector` types are string, no need to extract the value.
			$prop_selectors = $prop['selectors']['desktop']['value'] ?? $prop['selector'] ?? $selector ?? '';
			if ( $prop_selectors && ! in_array( $prop_selectors, $all_selectors, true ) && ! $has_sub_selectors ) {
				$all_selectors[] = $prop_selectors;
			}

			// 1.b. Element style prop `propertySelectors`.
			// There are two types of `propertySelectors` handled here:
			// - Grouped property selectors.
			// - Ungrouped property selectors identified by direct access to `desktop.value`.
			$prop_property_selectors_raw = $prop['propertySelectors'] ?? [];
			$prop_property_selectors     = isset( $prop_property_selectors_raw['desktop'] )
				? [ $prop_property_selectors_raw ]
				: $prop_property_selectors_raw;

			$prop_property_selectors_pairs = [];

			if ( ! empty( $prop_property_selectors ) ) {
				foreach ( $prop_property_selectors as $property_selector ) {
					$property_selector_list = $property_selector['desktop']['value'] ?? [];
					foreach ( $property_selector_list as $css_property => $css_property_selector ) {
						if ( $css_property_selector && ! in_array( $css_property_selector, $all_selectors, true ) ) {
							if ( ! $has_sub_selectors ) {
								// Check if the main selectors ($prop_selectors) include :before or :after pseudo-selectors.
								// Necessary cause some icons are styled intentionally on pseudo-elements (e.g., Accordion module icons).
								if ( false === strpos( $prop_selectors, ':before' ) && false === strpos( $prop_selectors, ':after' ) ) {
									// Do not add the property selector if the selector containing :before or :after. It's causing icon transition issue.
									if ( false === strpos( $css_property_selector, ':before' ) && false === strpos( $css_property_selector, ':after' ) ) {
										// Add the property selector to the list of all selectors.
										$all_selectors[] = $css_property_selector;
									}
								} else {
									// Add the property selector to the list of all selectors.
									$all_selectors[] = $css_property_selector;
								}
							} else {
								// Add the property-selector pair to the list to be used for sub-selectors processing later (1.d).
								$prop_property_selectors_pairs[ $css_property ] = $css_property_selector;
							}
						}
					}
				}

				// In some cases, we don't set specific selectors for certain CSS properties due to
				// those CSS properties inheriting the main selector. So, we need to add the main
				// selector as fallback for those. We only add it here to avoid redundancy.
				if ( $main_selector && ! in_array( $main_selector, $all_selectors, true ) && ! $has_sub_selectors ) {
					$all_selectors[] = $main_selector;
				}
			}

			// 1.c. Element style prop `selectorFunction`.
			$prop_selector_function  = $prop['selectorFunction'] ?? null;
			$prop_generated_selector = is_callable( $prop_selector_function )
				? call_user_func(
					$prop_selector_function,
					[
						'attr'       => $prop_attr,
						'selector'   => $prop_selector ?? $selector,
						'breakpoint' => 'desktop',
						'state'      => 'value',
					]
				)
				: '';

			if ( $prop_generated_selector && ! in_array( $prop_generated_selector, $all_selectors, true ) && ! $has_sub_selectors ) {
				$all_selectors[] = $prop_generated_selector;
			}

			// 1.d. Element style prop with sub-selectors.
			if ( $has_sub_selectors ) {
				$prop_base_selectors = [
					'selectors'              => $prop_selectors,
					'propertySelectorsPairs' => $prop_property_selectors_pairs,
					'generatedSelector'      => $prop_generated_selector,
				];
				$prop_sub_selectors  = self::_get_prop_sub_selectors( $prop_key, $prop_attr, $prop_base_selectors );

				if ( $prop_sub_selectors && ! in_array( $prop_sub_selectors, $all_selectors, true ) ) {
					$all_selectors[] = $prop_sub_selectors;
				}
			}

			// Set `moduleAttrs` to all breakpoints of transition attributes.
			foreach ( $transition_attr as $breakpoint => $states ) {
				$transition_attr[ $breakpoint ]['value']['moduleAttrs'][ $prop_key ] = $prop_attr;
			}
		}

		// 2. Make sure to process only if all selectors list is not empty.
		if ( ! empty( $all_selectors ) ) {
			// 2.a. Make sure to add the Transition Style `selectors` to `allSelectors` to ensure it's also covered. We need to
			// do it only when we have all selectors processed before to avoid unexpected rendered transition styles.
			if ( ! empty( $selectors ) ) {
				$transition_selectors_list = explode( ',', $selectors['desktop']['value'] ?? '' );
				foreach ( $transition_selectors_list as $each_selector ) {
					$each_selector = trim( $each_selector );
					if ( ! in_array( $each_selector, $all_selectors, true ) ) {
						$all_selectors[] = $each_selector;
					}
				}
			}

			// 2.b. Make sure the collected element styles selectors are unique and not empty.
			// From: [ '.selector1, .selector2', '.selector2', '.selector3' ].
			// To:   '.selector1, .selector2, .selector3'.
			$all_unique_selectors = array_reduce(
				$all_selectors,
				function ( $prev_selectors, $current_selector ) {
					if ( $current_selector ) {
						// Split the selectors by comma, trim whitespace, and filter out empty strings.
						$selectors = array_filter( array_map( 'trim', explode( ',', $current_selector ) ) );

						// Add and override each selector to the `$prev_selectors` array to make it unique.
						foreach ( $selectors as $selector ) {
							$prev_selectors[ $selector ] = true;
						}
					}

					return $prev_selectors;
				},
				[]
			);

			// 2.c. Make sure to process only if unique selectors is not empty.
			if ( ! empty( $all_unique_selectors ) ) {
				$all_selectors_string = implode( ', ', array_keys( $all_unique_selectors ) );

				// If the `selectors` prop is not empty, use it and override the `desktop.value` with the `all_selectors_string`
				// because we already add `selectors.desktop.value` to the `all_selectors` (2.a).
				$transition_selectors = array_merge(
					! empty( $selectors ) ? $selectors : [],
					[
						'desktop' => [
							'value' => $all_selectors_string,
						],
					]
				);

				$children_statements = Utils::style_statements(
					array(
						'selectors'            => $transition_selectors,
						'selectorFunction'     => $selector_function,
						'propertySelectors'    => $property_selectors,
						'attr'                 => $transition_attr,
						'important'            => $important,
						'declarationFunction'  => function( $params ) {
							return Transition::style_declaration( $params );
						},
						'orderClass'           => $order_class,
						'isInsideStickyModule' => $is_inside_sticky_module,
						'returnType'           => $args['returnType'],
					)
				);

				if ( $children_statements && $return_as_array ) {
					array_push( $children, ...$children_statements );
				} elseif ( $children_statements ) {
					$children .= $children_statements;
				}
			}
		}

		// 3. Process advanced styles.
		if ( ! empty( $advanced_styles_transitions ) ) {
			// Process advanced with the selector that is not common
			// to the main selector and is added inside the props of
			// advanced for a module.
			// Set initial advanced styles transition attribute.
			$advanced_transition_attr = [];
			foreach ( $attr_breakpoints as $breakpoint ) {
				$advanced_transition_attr[ $breakpoint ] = [
					'value' => [
						'advancedProperties' => [],
						'duration'           => $attr[ $breakpoint ]['value']['duration'] ?? $duration_default_value,
						'delay'              => $attr[ $breakpoint ]['value']['delay'] ?? $delay_default_value,
						'speedCurve'         => $attr[ $breakpoint ]['value']['speedCurve'] ?? $speed_curve_default_value,
					],
				];
			}

			foreach ( $advanced_styles_transitions as $transition_selector => $value ) {
				// Main selectors already processed above.
				if ( in_array( $transition_selector, $main_selectors, true ) && $advanced_styles_transitions[ $transition_selector ] ) {
					continue;
				}

				// Set `advancedProperties` to all breakpoints of advanced style transition attribute.
				foreach ( $advanced_transition_attr as $breakpoint => $states ) {
					$advanced_transition_attr[ $breakpoint ]['value']['advancedProperties'] = $advanced_styles_transitions[ $transition_selector ];
				}

				$advanced_transition_style_statements = Utils::style_statements(
					array(
						'selectors'            => ! empty( $selectors ) ? $selectors : array( 'desktop' => array( 'value' => $transition_selector ) ),
						'selectorFunction'     => $selector_function,
						'propertySelectors'    => $property_selectors,
						'attr'                 => $advanced_transition_attr,
						'important'            => $important,
						'declarationFunction'  => function( $params ) {
							return Transition::style_declaration( $params );
						},
						'orderClass'           => $order_class,
						'isInsideStickyModule' => $is_inside_sticky_module,
						'returnType'           => $args['returnType'],
					)
				);

				if ( $advanced_transition_style_statements && $return_as_array ) {
					array_push( $advanced_transition_styles, ...$advanced_transition_style_statements );
				} elseif ( $advanced_transition_style_statements ) {
					$advanced_transition_styles[] = $advanced_transition_style_statements;
				}
			}
		}

		// Concatenate child tags if they are not empty.
		// Assign values based on the existence of data and $return_as_array status.
		if ( ! empty( $children_heading_tags ) ) {
			$heading_tags_child = $return_as_array ? $children_heading_tags : implode( '', $children_heading_tags );
		}

		if ( ! empty( $children_body_tags ) ) {
			$body_tags_child = $return_as_array ? $children_body_tags : implode( '', $children_body_tags );
		}

		if ( ! empty( $advanced_transition_styles ) ) {
			$advanced_tags_child = $return_as_array ? $advanced_transition_styles : implode( '', $advanced_transition_styles );
		}

		$all_children = $return_as_array ? [] : '';

		if ( $return_as_array ) {
			array_push( $all_children, ...$children_heading_tags, ...$children_body_tags, ...$children, ...$advanced_transition_styles );
		} else {
			$all_children = $heading_tags_child . $body_tags_child . $children . $advanced_tags_child;
		}

		return Utils::style_wrapper(
			array(
				'attr'     => $transition_attr,
				'asStyle'  => $as_style,
				'children' => $all_children,
			)
		);
	}

	/**
	 * Get a sub-selectors based on the provided prop key, attr, and base selectors.
	 *
	 * There are some special element styles with sub-selectors that need to be handled manually. This function will
	 * generate sub-selectors based on the provided prop key, attr, and base selectors.
	 *
	 * @param string $prop_key            The key of the prop to generate sub-selectors for.
	 * @param array  $prop_attr           The attribute.
	 * @param array  $prop_base_selectors {
	 *     The base selectors.
	 *
	 *     @type string $selectors              The main selectors.
	 *     @type array  $propertySelectorsPairs The pairs of property-selector.
	 *     @type string $generatedSelector      The generated selector from `selectorFunction`.
	 * }
	 *
	 * @return string The sub-selectors string.
	 */
	private static function _get_prop_sub_selectors( string $prop_key, array $prop_attr, array $prop_base_selectors ): string {
		$sub_selectors_refs = [];

		// Get the sub-selectors ref based on the prop key.
		if ( 'bodyFont' === $prop_key ) {
			// The list of sub-selectors ref for `bodyFont` based on `subSelector` defined in `FontBodyStyle` class.
			$sub_selectors_refs = [
				'body'  => '', // This means `body` doesn't have sub-selectors and use the base selector directly.
				'link'  => 'a',
				'quote' => 'blockquote',
				'ul'    => 'ul li',
				'ol'    => 'ol li',
			];
		} elseif ( 'headingFont' === $prop_key ) {
			// The list of sub-selectors ref for `headingFont` based on `subSelector` defined in `FontHeaderStyle` class.
			$sub_selectors_refs = [
				'h1' => 'h1',
				'h2' => 'h2',
				'h3' => 'h3',
				'h4' => 'h4',
				'h5' => 'h5',
				'h6' => 'h6',
			];
		}

		// Bail early if the sub-selectors ref is not defined.
		if ( ! $sub_selectors_refs ) {
			return '';
		}

		// 1. Generate the sub-selectors list based on the sub-selectors ref and base selectors.
		// Example output: [ '.base h1, .base-2 h1', '.base h3, .base-2 h3' ].
		// Unlike in VB, we can access both of key and value of prop with `Object.entries` in JS. In FE, we need to use
		// `array_keys` to get the keys of the prop and manually get the value of the prop (sub-attribute) based on the key.
		$sub_selectors = array_filter(
			array_map(
				function( $sub_prop_key ) use ( $sub_selectors_refs, $prop_attr, $prop_base_selectors ) {
					// 1.a. Get the sub-selector.
					// Example input: 'h1'.
					$sub_selector = $sub_selectors_refs[ $sub_prop_key ] ?? null;
					$sub_attr     = $prop_attr[ $sub_prop_key ] ?? [];

					// Bail early if the sub-prop key is not in the sub-selectors ref and not a string. There is a chance that
					// the sub-selector is empty string, so we can't use falsy check here.
					if ( ! is_string( $sub_selector ) ) {
						return '';
					}

					// Bail early if the sub-attr doesn't have `hover` or `sticky` states.
					if ( ! self::has_multi_state_attr( $sub_attr ) ) {
						return '';
					}

					// 1.b. Get the base selector.
					// The base selector is prefix of the sub-selector. The order of base selector based on `get_statements` is:
					// - `generatedSelector` from `selectorFunction` of the prop.
					// - `propertySelectorsPairs` from `propertySelectors` for that sub prop key of the prop.
					// - `selectors` from `selectors` of the prop.
					// Example input: '.base, .base-2'.
					$base_selectors = $prop_base_selectors['generatedSelector']
						? $prop_base_selectors['generatedSelector']
						: ( $prop_base_selectors['propertySelectorsPairs'][ $sub_prop_key ] ?? $prop_base_selectors['selectors'] );

					// 1.c. Extracts the base selectors and add the sub-selector to each of them (combining them).
					// From: '.base, .base-2' and 'h1'.
					// To:   '.base h1, .base-2 h1'.
					return implode(
						', ',
						array_map(
							function( $base_selector ) use ( $sub_selector ) {
								return trim( $base_selector ) . ' ' . $sub_selector;
							},
							explode( ',', $base_selectors )
						)
					);
				},
				array_keys( $prop_attr )
			)
		);

		// 2. Return the sub-selectors string.
		// Example output: '.base h1, .base-2 h1, .base h3, .base-2 h3'.
		return implode( ', ', $sub_selectors );
	}

	/**
	 * Get CSS properties for the transition from `advanced_styles` property added to
	 * element style.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/TransitionStyle/utils/get-advanced-transition-styles-css-properties getAdvancedTransitionStylesCssProperties} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $style_data Advanced Styles data.
	 *
	 * @return string
	 */
	public static function get_advanced_transition_styles_css_properties( $style_data ) {
		$advanced_style_props = $style_data['props'];
		$advanced_style_attr  = $advanced_style_props['attr'] ?? [];

		if ( ! self::has_multi_state_attr( $advanced_style_attr ) ) {
			return '';
		}

		$css_properties = [];

		switch ( $style_data['componentName'] ) {
			case 'divi/background':
				$background_style_transition_css = self::get_advanced_transition_styles_background_style_properties( $advanced_style_props );
				if ( ! empty( $background_style_transition_css ) ) {
					$css_properties = $background_style_transition_css;
				}
				break;
			case 'divi/common':
				$common_style_transition_css = self::get_advanced_transition_styles_common_style_properties( $advanced_style_props );
				if ( ! empty( $common_style_transition_css ) ) {
					$css_properties = $common_style_transition_css;
				}
				break;
			case 'divi/image-sizing':
				$image_sizing_style_transition_css = self::get_advanced_transition_styles_image_sizing_style_properties( $advanced_style_props );
				if ( ! empty( $image_sizing_style_transition_css ) ) {
					$css_properties = $image_sizing_style_transition_css;
				}
				break;
			case 'divi/image-spacing':
				$image_spacing_style_transition_css = self::get_advanced_transition_styles_image_spacing_style_properties( $advanced_style_props );
				if ( ! empty( $image_spacing_style_transition_css ) ) {
					$css_properties = $image_spacing_style_transition_css;
				}
				break;
			case 'divi/dividers':
				$dividers_style_transition_css = self::get_advanced_transition_styles_dividers_style_properties( $advanced_style_props );
				if ( ! empty( $dividers_style_transition_css ) ) {
					$css_properties = $dividers_style_transition_css;
				}
				break;
			case 'divi/text':
				$text_style_transition_css = self::get_advanced_transition_styles_text_style_properties( $advanced_style_props );
				if ( ! empty( $text_style_transition_css ) ) {
					$css_properties = $text_style_transition_css;
				}
				break;
			default:
				break;
		}

		$transition_css_props = implode( ',', $css_properties );

		return $transition_css_props;
	}

	/**
	 * Check if hover or sticky are enabled for the advanced styles.
	 *
	 * @since ??
	 *
	 * @param array $attrs The module attribute.
	 *
	 * @return bool True if exist hover or sticky styles, false otherwise.
	 */
	public static function has_multi_state_attr( array $attrs ): bool {
		$attrs_json = wp_json_encode( $attrs );

		static $cached = [];

		$cache_key = md5( $attrs_json );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		// JSON encode the attributes array for faster search using strpos and avoid any loops.
		$hover  = (bool) strpos( $attrs_json, 'hover' ); // Check if a module attribute has hover state.
		$sticky = (bool) strpos( $attrs_json, 'sticky' ); // Check if a module attribute has sticky state.

		// Do not output anything if there is no 'hover' or 'sticky' styles for a module.
		if ( ! $hover && ! $sticky ) {
			$cached[ $cache_key ] = false;
			return false;
		}

		$cached[ $cache_key ] = true;

		return true;
	}

	/**
	 * Get transition styles for Advanced Styles.
	 *
	 * @since ??
	 *
	 * @param array  $advanced_styles An array for advanced styles.
	 * @param string $selector        Module selector.
	 *
	 * @return array Transition styles.
	 */
	public static function get_advanced_transition_styles( array $advanced_styles, string $selector ): array {
		if ( ! $advanced_styles ) {
			return [];
		}

		static $cached = [];

		$cache_key = md5( wp_json_encode( $advanced_styles ) . $selector );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		$animatable_options     = TransitionUtils::get_animatable_options_array();
		$transition_data        = [];
		$property_selector_data = [];
		$merged_transition_data = [];

		foreach ( $advanced_styles as $style ) {
			$css_properties          = [];
			$trans_selector          = $style['props']['selector'] ?? $selector;
			$property_selectors      = $style['props']['propertySelectors'] ?? [];
			$prop_property_selectors = ! empty( $property_selectors ) && array_key_exists( 'desktop', $property_selectors )
				? [ $property_selectors ]
				: array_values( $property_selectors );

			// The `imageSelector` only exists in the `divi/image-sizing` right now and used to set the image height style.
			$image_selector = $style['props']['imageSelector'] ?? null;

			$style_data = array(
				'props'         => $style['props'],
				'componentName' => $style['componentName'],
			);

			$property_selector_list                       = [];
			$property_selector_desktop_value_css_property = [];

			// Check if there are any items in $prop_property_selectors.
			if ( count( $prop_property_selectors ) > 0 ) {
				// Loop through each property selector in $prop_property_selectors.
				foreach ( $prop_property_selectors as $property_selector ) {
					// Retrieve the 'desktop' value from the property selector if it exists, otherwise set as an empty string.
					$property_selector_desktop_value = $property_selector['desktop']['value'] ?? '';

					// Add the desktop value to the property selector list array.
					$property_selector_list[] = $property_selector_desktop_value;

					// Ensure the property_selector_desktop_value is an array before extracting keys.
					if ( is_array( $property_selector_desktop_value ) && null !== $property_selector_desktop_value ) {
						/**
						 * Extract the keys directly using array_keys.
						 * Example Data: {"color":".et_pb_pricing_heading","fontSize":".et_pb_pricing_title"}.
						 * Result: The array $keys will contain ['color', 'fontSize']
						 */
						$keys = array_keys( $property_selector_desktop_value );

						// Check if $keys is not empty before merging.
						if ( ! empty( $keys ) ) {
							// Merge the extracted keys into the CSS property array.
							$property_selector_desktop_value_css_property = array_merge(
								$property_selector_desktop_value_css_property ?? [],
								$keys
							);
						}
					}
				}
			}

			$transition_css_props = self::get_advanced_transition_styles_css_properties( $style_data );

			if ( is_string( $transition_css_props ) && '' !== $transition_css_props ) {
				if ( strpos( $transition_css_props, ',' ) !== false ) {
					$transition_css_props_array = explode( ',', $transition_css_props );
				} else {
					$transition_css_props_array[] = $transition_css_props;
				}
			}

			if ( isset( $transition_css_props_array ) && is_array( $transition_css_props_array ) && ! empty( $transition_css_props_array ) ) {
				foreach ( $transition_css_props_array as $transition_css_prop ) {
					if ( '' !== $transition_css_prop && in_array( $transition_css_prop, $animatable_options, true ) ) {
						$css_properties[] = $transition_css_prop;
					}
				}
			}

			// Add CSS properties to transitionData if $css_properties is set, an array, and not empty.
			if ( isset( $css_properties ) && is_array( $css_properties ) && ! empty( $css_properties ) ) {
				// Check if $property_selector_list is not empty and has items to process.
				if ( ! empty( $property_selector_list ) && count( $property_selector_list ) > 0 ) {
					foreach ( $property_selector_list as $selector_value ) {
						if ( is_array( $selector_value ) && null !== $selector_value ) {
							// Get all keys from the selector value to check against CSS properties.
							$selector_keys = array_keys( $selector_value );

							foreach ( $css_properties as $index => $css_property ) {
								// Check if the CSS property exists in the selector's keys.
								$has_css_property = in_array( $css_property, $selector_keys, true );

								if ( $has_css_property ) {
									foreach ( $selector_keys as $key ) {
										// Ensure selectorValue is defined before adding properties.
										if ( $selector_value ) {
											// Initialize the property selector data array if not already set.
											if ( ! isset( $property_selector_data[ $selector_value[ $key ] ] ) ) {
												$property_selector_data[ $selector_value[ $key ] ] = [];
											}
											// Add the current CSS property to the property selector data.
											$property_selector_data[ $selector_value[ $key ] ][] = $css_property;
										}
									}

									// Remove the CSS property from $css_properties as it has been added to property_selector_data.
									unset( $css_properties[ $index ] );
								}
							}
						}
					}
				}

				// List of height-related properties to support.
				$height_properties = [ 'height', 'max-height', 'min-height' ];

				// Check if `$image_selector` is not empty and CSS properties contains related properties to add. For now, the
				// only property that has a relation with the image selector is `height`.
				if ( ! empty( $image_selector ) && is_array( $css_properties ) && count( $css_properties ) > 0 ) {
					// Find the intersection between $css_properties and $height_properties.
					$matched_properties = array_intersect( $css_properties, $height_properties );

					if ( ! empty( $matched_properties ) ) {
						// Add all matched properties to transition data for the image selector.
						$transition_data[ $image_selector ] = array_values( $matched_properties );

						// Remove the matched properties from $css_properties.
						$css_properties = array_diff( $css_properties, $matched_properties );
					}
				}

				// If there are still CSS properties left after the loop, add them to transition data.
				if ( count( $css_properties ) > 0 ) {
					// Initialize the transition data array if the selector key is not already set.
					if ( ! isset( $transition_data[ $trans_selector ] ) ) {
						$transition_data[ $trans_selector ] = [];
					}
					$transition_data[ $trans_selector ] = array_merge( $transition_data[ $trans_selector ], $css_properties );
				}
			}

			// Merge the remaining CSS properties into the transition data for the current selector.
			$merged_transition_data = array_merge( $transition_data, $property_selector_data );

			// Remove duplicate css values.
			if ( is_array( $merged_transition_data ) && ! empty( $merged_transition_data ) ) {
				foreach ( $merged_transition_data as $key => $value ) {
					$merged_transition_data[ $key ] = array_values( array_unique( $value ) );
				}
			}

			// Reset data.
			$css_properties             = [];
			$transition_css_props_array = [];
		}

		$cached[ $cache_key ] = $merged_transition_data;

		return $merged_transition_data;
	}

	/**
	 * Get background style transition CSS properties.
	 *
	 * @since ??
	 *
	 * @param array $style_props Background style props.
	 *
	 * @return array CSS properties.
	 */
	public static function get_advanced_transition_styles_background_style_properties( $style_props ) {
		$attr = $style_props['attr'] ?? [];

		if ( ! self::has_multi_state_attr( $attr ) ) {
			return [];
		}

		$css_properties = [];
		$desktop_attr   = $attr['desktop'] ?? [];

		if ( ! empty( $desktop_attr ) ) {
			$hover_sticky_attr = array_merge(
				$desktop_attr['hover'] ?? [],
				$desktop_attr['sticky'] ?? []
			);

			if ( ! empty( $hover_sticky_attr ) ) {
				$declaration_props      = array(
					'attrValue' => $hover_sticky_attr,
				);
				$declaration_css_all    = [];
				$declaration_css_all[]  = Background::style_declaration( $declaration_props );
				$declaration_css_all[]  = Background::background_mask_style_declaration( $declaration_props );
				$declaration_css_all[]  = Background::background_pattern_style_declaration( $declaration_props );
				$declaration_css        = implode( '', $declaration_css_all );
				$declaration_css_string = is_string( $declaration_css ) ? $declaration_css : '';

				if ( '' !== $declaration_css_string ) {
					$items = explode( ';', $declaration_css_string );
					foreach ( $items as $item ) {
						if ( $item && '' !== $item ) {
							$parts    = explode( ':', $item );
							$property = trim( $parts[0] ?? '' );
							if ( $property && preg_match( '/^(-[a-zA-Z]+-|--)?[a-zA-Z-]+$/', $property ) ) {
								$css_properties[] = $property;
							}
						}
					}
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get common style CSS properties.
	 *
	 * @since ??
	 *
	 * @param array $common_style_props Common style props.
	 *
	 * @return array CSS properties.
	 */
	public static function get_advanced_transition_styles_common_style_properties( $common_style_props ) {
		$common_style_attr = $common_style_props['attr'];

		if ( ! self::has_multi_state_attr( $common_style_attr ) ) {
			return [];
		}

		$css_properties                    = [];
		$common_style_property             = $common_style_props['property'] ?? null;
		$common_style_declaration_function = $common_style_props['declarationFunction'] ?? null;

		// 1. CSS property directly defined by `property`.
		if ( $common_style_property ) {
			$css_properties[] = $common_style_property;
		}

		// 2. CSS string declared by `declarationFunction` callback function.
		if ( $common_style_declaration_function ) {
			$attr_desktop_value = isset( $common_style_attr['desktop'] ) ? $common_style_attr['desktop'] : null;

			if ( is_array( $attr_desktop_value ) && count( $attr_desktop_value ) > 0 ) {
				$attr_state_values = array_merge(
					isset( $attr_desktop_value['hover'] ) ? [ $attr_desktop_value['hover'] ] : [],
					isset( $attr_desktop_value['sticky'] ) ? [ $attr_desktop_value['sticky'] ] : []
				);

				if ( ! empty( $attr_state_values ) ) {
					foreach ( $attr_state_values as $attr_value ) {
						$declaration_function_props = array(
							'important' => isset( $common_style_props['important'] ) ? $common_style_props['important'] : false,
							'attrValue' => $attr_value,
						);

						$css_declaration        = is_callable( $common_style_declaration_function ) ? call_user_func( $common_style_declaration_function, $declaration_function_props ) : '';
						$css_declaration_string = is_string( $css_declaration ) ? $css_declaration : '';

						if ( '' !== $css_declaration_string ) {
							$css_declaration_blocks = explode( ';', $css_declaration_string );

							if ( is_array( $css_declaration_blocks ) && ! empty( $css_declaration_blocks ) ) {
								foreach ( $css_declaration_blocks as $css_declaration_block ) {
									$css_declaration_property_value = explode( ':', $css_declaration_block );
									$css_declaration_property       = $css_declaration_property_value[0];

									if ( '' !== $css_declaration_property ) {
										$css_properties[] = trim( $css_declaration_property );
									}
								}
							}
						}
					}
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get dividers style transition CSS properties.
	 *
	 * @since ??
	 *
	 * @param array $style_props Dividers style props.
	 *
	 * @return array CSS properties.
	 */
	public static function get_advanced_transition_styles_dividers_style_properties( $style_props ) {
		$attr      = $style_props['attr'] ?? [];
		$fullwidth = $style_props['fullwidth'] ?? false;
		$placement = $style_props['placement'] ?? '';

		if ( ! self::has_multi_state_attr( $attr ) ) {
			return [];
		}

		$css_properties = [];
		$desktop_attr   = $attr['desktop'] ?? [];

		if ( ! empty( $desktop_attr ) ) {
			$hover_sticky_attr = array_merge(
				$desktop_attr['hover'] ?? [],
				$desktop_attr['sticky'] ?? []
			);

			if ( ! empty( $hover_sticky_attr ) ) {
				$declaration_props      = array(
					'attrValue' => $hover_sticky_attr,
					'fullwidth' => $fullwidth,
					'placement' => $placement,
				);
				$declaration_css        = Dividers::style_declaration( $declaration_props );
				$declaration_css_string = is_string( $declaration_css ) ? $declaration_css : '';

				if ( '' !== $declaration_css_string ) {
					$items = explode( ';', $declaration_css_string );
					foreach ( $items as $item ) {
						if ( $item && '' !== $item ) {
							$parts    = explode( ':', $item );
							$property = trim( $parts[0] ?? '' );
							if ( $property && preg_match( '/^(-[a-zA-Z]+-|--)?[a-zA-Z-]+$/', $property ) ) {
								$css_properties[] = $property;
							}
						}
					}
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get image sizing style transition CSS properties.
	 *
	 * @since ??
	 *
	 * @param array $style_props Image sizing style props.
	 *
	 * @return array CSS properties.
	 */
	public static function get_advanced_transition_styles_image_sizing_style_properties( $style_props ) {
		$css_properties = [];
		$style_attr     = $style_props['attr'];

		if ( ! self::has_multi_state_attr( $style_attr ) ) {
			return [];
		}

		$desktop_attr = $style_attr['desktop'] ?? [];

		if ( ! empty( $desktop_attr ) ) {
			$hover_sticky_attr = array_merge(
				$desktop_attr['hover'] ?? [],
				$desktop_attr['sticky'] ?? []
			);

			if ( ! empty( $hover_sticky_attr ) ) {
				foreach ( $hover_sticky_attr as $sizing_key => $sizing_value ) {
					switch ( $sizing_key ) {
						case 'maxWidth':
							$css_properties[] = 'max-width';
							break;
						case 'maxHeight':
							$css_properties[] = 'max-height';
							break;
						case 'minHeight':
							$css_properties[] = 'min-height';
							break;
						default:
							$css_properties[] = $sizing_key;
							break;
					}
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get image spacing style transition CSS properties.
	 *
	 * @since ??
	 *
	 * @param array $style_props Image spacing style props.
	 *
	 * @return array CSS properties.
	 */
	public static function get_advanced_transition_styles_image_spacing_style_properties( $style_props ) {
		$css_properties = [];
		$style_attr     = $style_props['attr'];

		if ( ! self::has_multi_state_attr( $style_attr ) ) {
			return [];
		}

		$desktop_attr = $style_attr['desktop'] ?? [];

		if ( ! empty( $desktop_attr ) ) {
			$hover_sticky_attr = array_merge(
				$desktop_attr['hover'] ?? [],
				$desktop_attr['sticky'] ?? []
			);

			if ( ! empty( $hover_sticky_attr ) ) {
				foreach ( $hover_sticky_attr as $spacing_key => $spacing_value ) {
					if ( ! empty( $spacing_value ) && is_array( $spacing_value ) ) {
						foreach ( $spacing_value as $side_key => $side_value ) {
							// The `sync*` properties are not valid CSS properties.
							if ( 'syncVertical' === $side_key || 'syncHorizontal' === $side_key ) {
								continue;
							}

							$css_properties[] = $spacing_key . '-' . $side_key;
						}
					}
				}
			}
		}

		sort( $css_properties );

		return $css_properties;
	}

	/**
	 * Get text style transition CSS properties.
	 *
	 * @since ??
	 *
	 * @param array $text_style_props Text style props.
	 *
	 * @return array CSS properties.
	 */
	public static function get_advanced_transition_styles_text_style_properties( $text_style_props ) {
		$css_properties  = [];
		$text_style_attr = $text_style_props['attr'];

		if ( ! self::has_multi_state_attr( $text_style_attr ) ) {
			return [];
		}

		foreach ( $text_style_attr as $key => $text_group_attr ) {
			if ( is_array( $text_style_attr ) && array_key_exists( $key, $text_style_attr ) ) {
				$attr_desktop_value = $text_style_attr[ $key ]['desktop'] ?? null;

				if ( is_array( $attr_desktop_value ) && ! empty( $attr_desktop_value ) ) {
					$attr_mode_value = array_merge(
						isset( $attr_desktop_value['hover'] ) ? $attr_desktop_value['hover'] : [],
						isset( $attr_desktop_value['sticky'] ) ? $attr_desktop_value['sticky'] : []
					);

					if ( is_array( $attr_mode_value ) && ! empty( $attr_mode_value ) ) {
						if ( 'textShadow' === $key ) {
							$text_shadow_declaration_props      = array(
								'attrValue'  => $attr_desktop_value['value'],
								'important'  => false,
								'returnType' => 'string',
							);
							$text_shadow_declaration_css        = TextShadow::style_declaration( $text_shadow_declaration_props );
							$text_shadow_declaration_css_string = is_string( $text_shadow_declaration_css ) ? $text_shadow_declaration_css : '';

							if ( '' !== $text_shadow_declaration_css_string ) {
								foreach ( explode( ';', $text_shadow_declaration_css_string ) as $item ) {
									if ( $item && '' !== $item ) {
										$parts = explode( ':', $item );
										if ( $parts[0] && '' !== $parts[0] ) {
											$css_properties[] = trim( $parts[0] );
										}
									}
								}
							}
						} elseif ( 'text' === $key ) {
							if ( is_array( $attr_mode_value ) && ! empty( $attr_mode_value ) ) {
								foreach ( array_keys( $attr_mode_value ) as $attr_mode_key ) {
									$css_properties[] = $attr_mode_key;
								}
							}
						}
					}
				}
			}
		}

		return $css_properties;
	}
}
