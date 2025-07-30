<?php
/**
 * Utils::get_statements()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\Style\Utils\UtilsTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Layout\Components\Style\Utils\GroupedStatements;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Packages\StyleLibrary\Utils\Utils as StyleUtils;

trait GetStatementsTrait {

	/**
	 * Get CSS statements based on given params.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetStatements getStatements}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array         $selectors                     An array of selectors for each breakpoint and state.
	 *     @type array         $attr                          An array of module attribute data.
	 *     @type array         $defaultPrintedStyleAttr       Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type array|boolean $important                     Optional. Whether to add `!important` to the declaration. Default `false`.
	 *     @type callable      $declarationFunction           The function to be called to generate CSS declaration.
	 *     @type array         $additionalArgs                Optional. The additional arguments that you want to
	 *                                                        pass to the declaration function. Default `[]`.
	 *     @type callable      $selectorFunction              Optional. The function to be called to generate CSS selector. Default `null`.
	 *     @type array         $propertySelectors             Optional. The property selectors that you want to unpack. Default `[]`.
	 *     @type array         $propertySelectorsShorthandMap Optional. This is the map of shorthand properties to their longhand properties. Default `[]`.
	 *     @type string        $orderClass                    Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule          Optional. Whether the module is inside sticky module or not.
	 *     @type string        $returnType                    Optional. This is the type of value that the function will return.
	 *                                                        Can be either `string` or `array`. Default `array`.
	 *     @type array         $styleBreakpointOrder          Optional. The style breakpoint order. Default `Breakpoint::get_default_style_breakpoint_order()`.
	 *     @type array         $styleBreakpointSettings       Optional. The style breakpoint settings. Default `Breakpoint::get_default_style_breakpoint_settings()`.
	 * }
	 *
	 * @return string|array
	 */
	public static function get_statements( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'                     => false,
				'selectorFunction'              => null,
				'propertySelectors'             => [],
				'propertySelectorsShorthandMap' => [],
				'returnType'                    => 'array',
				'styleBreakpointOrder'          => Breakpoint::get_default_style_breakpoint_order(),
				'styleBreakpointSettings'       => Breakpoint::get_default_style_breakpoint_settings(),
				'atRules'                       => '',
			]
		);

		$args['attr'] = StyleUtils::resolve_dynamic_variables_recursive( $args['attr'] );

		$selectors                        = $args['selectors'];
		$default_printed_style_attr       = $args['defaultPrintedStyleAttr'] ?? [];
		$attr                             = $args['attr'];
		$important                        = $args['important'];
		$declaration_function             = $args['declarationFunction'];
		$additional_args                  = $args['additionalArgs'] ?? [];
		$selector_function                = $args['selectorFunction'];
		$property_selectors               = $args['propertySelectors'];
		$property_selectors_shorthand_map = $args['propertySelectorsShorthandMap'];
		$order_class                      = $args['orderClass'] ?? null;
		$is_inside_sticky_module          = $args['isInsideStickyModule'] ?? false;
		$is_parent_layout_flex            = $args['isParentLayoutFlex'] ?? false;
		$style_breakpoint_order           = $args['styleBreakpointOrder'];
		$style_breakpoint_settings        = $args['styleBreakpointSettings'];
		$at_rules                         = $args['atRules'];

		// Bail, if noting is there to process.
		if ( empty( $attr ) ) {
			return 'array' === $args['returnType'] ? [] : '';
		}

		$grouped_statements = new GroupedStatements();

		// We need to expand shorthand important values before we process the attr values.
		// This is because we need to know the complete important values for each property
		// before we process the attr values in order to get the correct breakpoint and
		// state fallback values.
		$expanded_important = Utils::get_expanded_shorthand_important(
			[
				'important'                     => $important,
				'propertySelectorsShorthandMap' => $property_selectors_shorthand_map,
			]
		);

		// We need to find and replace `{{:hover}}` placeholder with actual hover selector value,
		// and we are doing it here instead of in the `Utils::get_selector()` util because `Utils::get_selector()`
		// are being call in below loop, but we only need to replace `{{:hover}}` placeholder once.
		if ( Utils::has_hover_selectors( $selectors ) ) {
			$selectors = Utils::replace_hover_selector_placeholder( $selectors );
		}

		// We need to find and replace `{{:hover}}` placeholder with actual hover selector value,
		// and we are doing it here instead of in the `Utils::get_selector_of_property_selectors()` util because
		// `Utils::get_selector_of_property_selectors()` are being call in below loop, but we only need to
		// replace `{{:hover}}` placeholder once.
		if ( Utils::has_hover_selectors( $property_selectors ) ) {
			$property_selectors = Utils::replace_hover_selector_placeholder( $property_selectors );
		}

		if ( is_array( $attr ) ) {
			// Sort the attribute by breakpoints.
			$attr = ModuleUtils::sort_breakpoints( $attr, $style_breakpoint_order );

			foreach ( $attr as $breakpoint => $state_values ) {

				// Sort the attribute by states.
				$state_values = ModuleUtils::sort_states( $state_values );

				foreach ( $state_values as $state => $attr_value ) {
					// Get default attrValue on current breakpoint + state. Breakpoint and state
					// need to be type casted so proper types are being passed into `declarationFunction`.
					$default_attr_value = $args['defaultPrintedStyleAttr'][ $breakpoint ][ $state ] ?? [];

					$selector               = Utils::get_selector(
						[
							'selectors'            => $selectors,
							'breakpoint'           => $breakpoint,
							'state'                => $state,
							'orderClass'           => $order_class,
							'isInsideStickyModule' => $is_inside_sticky_module,
						]
					);
					$has_property_selectors = ! empty( $property_selectors );
					$return_type            = $has_property_selectors ? 'key_value_pair' : 'string';
					$important_processed    = Utils::get_current_important(
						[
							'important'  => $expanded_important,
							'breakpoint' => $breakpoint,
							'state'      => $state,
						]
					);
					$declaration            = call_user_func(
						$declaration_function,
						[
							'attrValue'          => $attr_value,
							'attr'               => $attr,
							'defaultAttrValue'   => $default_attr_value,
							'important'          => $important_processed,
							'returnType'         => $return_type,
							'breakpoint'         => $breakpoint,
							'state'              => $state,
							'additional'         => $additional_args,
							'isParentLayoutFlex' => $is_parent_layout_flex,
						]
					);

					if ( $declaration ) {
						// If property selectors are used, returned declaration is in object and need further processing.
						if ( $has_property_selectors && is_array( $declaration ) ) {
							$complete_property_selectors = Utils::unpack_property_selectors_shorthand_map(
								[
									'propertySelectors' => $property_selectors,
									'propertySelectorsShorthandMap' => $property_selectors_shorthand_map,
								]
							);

							// Get current property selector names (eg. ['font-size', 'color']). Property selectors
							// can be set into specific breakpoint and state and have fallback mechanism: Direct
							// fallback to `value` for `hover` and `sticky` state and cascaded `phone` > `tablet` > `desktop`
							// fallback for breakpoint therefore current property selector names can be vary depending
							// to given property selectors and current breakpoint + state.
							$property_selector_names = Utils::get_current_property_selector_names(
								[
									'propertySelectors' => $complete_property_selectors,
									'breakpoint'        => $breakpoint,
									'state'             => $state,
								]
							);

							// Grouped declaration based on property selector names. Ungrouped declaration will be
							// rendered using default `prop.selector`.
							$grouped_declarations = Utils::group_declarations_by_property_selector_names(
								[
									'propertySelectorNames' => $property_selector_names,
									'declarations' => $declaration,
								]
							);

							foreach ( $grouped_declarations as $property_selector_name => $property_selector_declaration ) {
								$current_property_selector = 'ungrouped' === $property_selector_name
									? $selector
									: Utils::get_selector_of_property_selectors(
										[
											'selectors'    => $complete_property_selectors,
											'propertyName' => $property_selector_name,
											'breakpoint'   => $breakpoint,
											'state'        => $state,
											'orderClass'   => $order_class,
											'isInsideStickyModule' => $is_inside_sticky_module,
										]
									);

								if ( is_callable( $selector_function ) ) {
									$ruleset_selector = call_user_func(
										$selector_function,
										[
											'attr'       => $attr,
											'selector'   => $current_property_selector,
											'breakpoint' => $breakpoint,
											'state'      => $state,
											'defaultPrintedStyleAttr' => $default_printed_style_attr,
											'isParentLayoutFlex' => $is_parent_layout_flex,
										]
									);
								} else {
									$ruleset_selector = $current_property_selector;
								}

								$grouped_statements->add(
									[
										'atRules'     => ! empty( $at_rules ) ? $at_rules : Utils::get_at_rules( $breakpoint, $style_breakpoint_settings ),
										'selector'    => $ruleset_selector,
										'declaration' => $property_selector_declaration,
									]
								);
							}
						} elseif ( is_string( $declaration ) ) {
							if ( $selector_function ) {
								$ruleset_selector = call_user_func(
									$selector_function,
									[
										'attr'       => $attr,
										'selector'   => $selector,
										'breakpoint' => $breakpoint,
										'state'      => $state,
										'defaultPrintedStyleAttr' => $default_printed_style_attr,
										'isParentLayoutFlex' => $is_parent_layout_flex,
									]
								);
							} else {
								$ruleset_selector = $selector;
							}

							$grouped_statements->add(
								[
									'atRules'     => ! empty( $at_rules ) ? $at_rules : Utils::get_at_rules( $breakpoint, $style_breakpoint_settings ),
									'selector'    => $ruleset_selector,
									'declaration' => $declaration,
								]
							);
						}
					}
				}
			}
		}

		if ( 'array' === $args['returnType'] ) {
			return $grouped_statements->value_as_array();
		}

		return $grouped_statements->value();
	}

	/**
	 * Function for rendering style statements.
	 *
	 * Get style breakpoints parameter then pass it into `Utils::get_statements()`.
	 * Previously, style component directly uses `Utils::get_statements()` function. However since the introduction of
	 * Customizable Breakpoint, it is preferable to use function component to render statements because this
	 * automatically gets necessary breakpoints configuration.
	 *
	 * This function is equivalent of JS function: {@link /docs/builder-api/js-beta/divi-module/functions/StyleStatements
	 * styleStatements} in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array         $selectors                     An array of selectors for each breakpoint and state.
	 *     @type array         $attr                          An array of module attribute data.
	 *     @type array         $defaultPrintedStyleAttr       Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type array|boolean $important                     Optional. Whether to add `!important` to the declaration. Default `false`.
	 *     @type callable      $declarationFunction           The function to be called to generate CSS declaration.
	 *     @type array         $additionalArgs                Optional. The additional arguments that you want to
	 *                                                        pass to the declaration function. Default `[]`.
	 *     @type callable      $selectorFunction              Optional. The function to be called to generate CSS selector. Default `null`.
	 *     @type array         $propertySelectors             Optional. The property selectors that you want to unpack. Default `[]`.
	 *     @type array         $propertySelectorsShorthandMap Optional. This is the map of shorthand properties to their longhand properties. Default `[]`.
	 *     @type string        $orderClass                    Optional. The selector class name.
	 *     @type bool          $isInsideStickyModule          Optional. Whether the module is inside sticky module or not.
	 *     @type string        $returnType                    Optional. This is the type of value that the function will return.
	 *                                                        Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array
	 */
	public static function style_statements( array $args ) {
		$style_breakpoint_order    = Breakpoint::get_style_breakpoint_order();
		$style_breakpoint_settings = Breakpoint::get_style_breakpoint_settings();

		// Automatically merge style breakpoint order and settings into the arguments and execute `Utils::get_statements()`.
		return self::get_statements(
			array_merge(
				$args,
				[
					'styleBreakpointOrder'    => $style_breakpoint_order,
					'styleBreakpointSettings' => $style_breakpoint_settings,
				]
			)
		);
	}
}
