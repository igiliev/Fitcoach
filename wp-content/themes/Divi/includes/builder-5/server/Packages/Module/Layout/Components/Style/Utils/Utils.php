<?php
/**
 * Utils class
 *
 * @package Builder\FrontEnd
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\Style\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElementsUtils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\Utils as StyleLibraryUtils;

/**
 * Utils class is a helper class for working with related CSS functionality.
 *
 * @since ??
 */
class Utils {

	use UtilsTraits\GetStatementsTrait;

	/**
	 * Generates a hover state selector by adding the `:hover` class to the specified selectors.
	 *
	 * @since ??
	 *
	 * @param string      $breakpoint_base_selector The base selector for the breakpoint.
	 * @param string|null $order_class              Optional. The selector class name.
	 *
	 * @return string The generated selector string.
	 */
	public static function generate_hover_state_selector(
		string $breakpoint_base_selector,
		?string $order_class = null
	): string {
		$maybe_has_multiple_selectors = array_map( 'trim', preg_split( '/,\s?/', $breakpoint_base_selector ) );
		$breakpoint_base_selectors    = [];

		foreach ( $maybe_has_multiple_selectors as $selector ) {
			// The process is different from VB, because in VB we always need to add hover class to the
			// Module selector. However, in FE, we should always add the `:hover` at the end of the selector.
			// This is the default behaviour on D4.
			if ( false !== strpos( $selector, ':hover' ) ) {
				$breakpoint_base_selectors[] = $selector;
			} elseif ( false !== strpos( $selector, ':' ) ) {
				// If the selector has pseudo selector, then we need to add the `:hover` before the pseudo selector.
				$breakpoint_base_selectors[] = implode( ':hover:', explode( ':', $selector, 2 ) );
			} else {
				$breakpoint_base_selectors[] = $selector . ':hover';
			}
		}

		return implode( ', ', $breakpoint_base_selectors );
	}

	/**
	 * Generates a sticky state selector by adding the `et_pb_sticky` class to the specified selectors.
	 *
	 * @since ??
	 *
	 * @param string      $breakpoint_base_selector The base selector for the breakpoint.
	 * @param string|null $order_class              Optional. The selector class name.
	 * @param bool|null   $is_inside_sticky_module  Optional. Whether the module is inside sticky module or not.
	 *
	 * @return string The generated selector string.
	 */
	public static function generate_sticky_state_selector(
		string $breakpoint_base_selector,
		?string $order_class = null,
		?bool $is_inside_sticky_module = false
	): string {
		$maybe_has_multiple_selectors = array_map( 'trim', preg_split( '/,\s?/', $breakpoint_base_selector ) );
		$breakpoint_base_selectors    = [];

		foreach ( $maybe_has_multiple_selectors as $selector ) {
			if ( $is_inside_sticky_module ) {
				// At this point, if the module itself is inside another sticky module, it means
				// we shouldn't add the `et_pb_sticky` class directly to the module selector due
				// to the sticky styles won't be triggered anyway.

				if ( Conditions::is_custom_post_type() ) {
					// We should add custom_post_type selector and sticky class followed by single space
					// to catch parent/ancestor module with sticky enabled.
					$selector = '.et-db #et-boc .et-l .et_pb_sticky ' . $selector;
				} else {
					// We should add sticky class followed by single space
					// to catch parent/ancestor module with sticky enabled.
					$selector = '.et_pb_sticky ' . $selector;
				}

				$breakpoint_base_selectors[] = $selector;
			} elseif ( empty( $selector ) ) { // When selector is empty string.
				$breakpoint_base_selectors[] = '.et_pb_sticky';
			} elseif ( is_null( $order_class ) || empty( $order_class ) ) { // When orderClass isn't provided or empty, use the fallback process.
				// In order for the `et_pb_sticky` to work, it needs to be added to the parent selector
				// and some selectors include child selectors. So we need to split the selector and
				// extract the parent selector and child selectors and add the `et_pb_sticky` to the
				// parent selector to generate the proper styles when hover state is activated.
				$maybe_has_parent_selector = explode( ' ', $selector );

				// If there is more than one selector, then it means there is a parent selector and
				// we only need to apply `et_pb_sticky` class to the parent selector.
				// Simplified example:
				// `.et_pb_blurb .et_pb_module_header` must be changed to:
				// `.et_pb_blurb.et_pb_sticky .et_pb_module_header`.
				if ( count( $maybe_has_parent_selector ) > 1 ) {
					// Extract the parent selector.
					$parent_selector = array_shift( $maybe_has_parent_selector );

					// Create a selector string from the child selectors.
					$child_selectors = implode( ' ', $maybe_has_parent_selector );

					// Add the `et_pb_sticky` to the parent selector and append the child selectors.
					$breakpoint_base_selectors[] = $parent_selector . '.et_pb_sticky ' . $child_selectors;
				} else {
					$breakpoint_base_selectors[] = $selector . '.et_pb_sticky';
				}
			} elseif ( false !== strpos( $selector, '.et_pb_sticky' ) ) {
				// If the selector already contains the `et_pb_sticky` class, then we don't need to add it again.
				// This is needed to prevent duplicate selectors.
				// Please refer VB logic at visual-builder/packages/module/src/layout/components/style/utils/generate-sticky-state-selector/index.ts.
				$breakpoint_base_selectors[] = $selector;
			} else {
				// Create regex pattern for matching orderClass within the selector, but only when it forms
				// its own complete selector (not part of a larger selector).
				// Match example: https://regex101.com/r/nblpti/1 .
				$order_class_regex = '/(?<=\\s|^|>)' . $order_class . '(?=\\s|$|:|\\.|>)/';
				$replacement       = $order_class . '.et_pb_sticky';

				// Append "et_pb_sticky" to the $selector or $order_class.
				$selector_output             = preg_replace( $order_class_regex, $replacement, $selector );
				$breakpoint_base_selectors[] = empty( $order_class ) || $selector_output === $selector
					? $selector . '.et_pb_sticky'
					: $selector_output;
			}
		}

		return implode( ', ', $breakpoint_base_selectors );
	}

	/**
	 * Get CSS At-rules based on given breakpoint name.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetAtRules getAtRules}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param string $breakpoint Breakpoint name.
	 * @param array  $style_breakpoint_settings Style breakpoint settings.
	 *
	 * @return string|false Will return false if the breakpoint does not exist.
	 */
	public static function get_at_rules(
		string $breakpoint,
		array $style_breakpoint_settings
	) {
		$breakpoint_setting = $style_breakpoint_settings[ $breakpoint ] ?? null;

		if ( $breakpoint_setting ) {
			$max_width = $breakpoint_setting['maxWidth']['value'] ?? null;
			$min_width = $breakpoint_setting['minWidth']['value'] ?? null;

			if ( $max_width && $min_width ) {
				return sprintf(
					'@media only screen and (min-width: %1$s) and (max-width: %2$s)',
					$min_width,
					$max_width
				);
			}

			if ( $max_width ) {
				return sprintf( '@media only screen and (max-width: %1$s)', $max_width );
			}

			if ( $min_width ) {
				return sprintf( '@media only screen and (min-width: %1$s)', $min_width );
			}
		}

		// Infer `disabled on` rules from breakpoint settings. This used to be fixed value, but now it needs to infer
		// the value from customizable breakpoints' state value property.
		$rules = Breakpoint::get_disabled_on_rules();

		if ( isset( $rules[ $breakpoint ] ) ) {
			return $rules[ $breakpoint ];
		}

		return false;
	}

	/**
	 * Get current property's important value based on given breakpoint and state.
	 *
	 * See below for fallback flow:
	 *
	 * |       | value | hover | sticky |
	 * |-------|-------|-------|--------|
	 * | Desktop |   *   |  <--  |  <--   |
	 * | Tablet  |   ^   |  <--  |  <--   |
	 * | Phone   |   ^   |  <--  |  <--   |
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetCurrentImportant getCurrentImportant}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array|boolean $important                     An array key-value pairs with property name
	 *                                                        as the key and selectors as the value.
	 *                                                        Note if an array value is passed, then it should not be in shorthand format.
	 *                                                        Use `Utils::get_expanded_shorthand_important()` to expand
	 *                                                        shorthand format property before passing it to this function.
	 *     @type string        $breakpoint                    The breakpoint of the selector.
	 *                                                        Can be either `desktop`, `tablet`, or `phone`.
	 *     @type string        $state                         The state of the selector.
	 *                                                        Can be either `value`, `hover`, or `sticky`.
	 * }
	 *
	 * @return array|bool
	 */
	public static function get_current_important( array $args ) {
		$important  = $args['important'];
		$breakpoint = $args['breakpoint'];
		$state      = $args['state'];

		if ( is_bool( $important ) ) {
			return $important;
		}

		return ModuleUtils::use_attr_value(
			[
				'attr'         => $important,
				'breakpoint'   => $breakpoint,
				'state'        => $state,
				'mode'         => 'getOrInheritAll',
				'defaultValue' => [],
			]
		);
	}

	/**
	 * Get property selector names of current breakpoint + state based on given property selectors.
	 *
	 * See below for fallback flow:
	 *
	 * |       | value | hover | sticky |
	 * |-------|-------|-------|--------|
	 * | Desktop |   *   |  <--  |  <--   |
	 * | Tablet  |   ^   |  <--  |  <--   |
	 * | Phone   |   ^   |  <--  |  <--   |
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetCurrentPropertySelectorNames getCurrentPropertySelectorNames}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $propertySelectors An array key-value pairs with property name as the key and selectors as the value.
	 *     @type string $breakpoint        The breakpoint of the selector. Can be either `desktop`, `tablet`, or `phone`.
	 *     @type string $state             The state of the selector. Can be either `value`, `hover`, or `sticky`.
	 * }
	 *
	 * @return array
	 */
	public static function get_current_property_selector_names( array $args ): array {
		$property_selectors = $args['propertySelectors'];
		$breakpoint         = $args['breakpoint'];
		$state              = $args['state'];

		$property_selector = ModuleUtils::use_attr_value(
			[
				'attr'       => $property_selectors,
				'breakpoint' => $breakpoint,
				'state'      => $state,
				'mode'       => 'getAndInheritAll',
			]
		);

		return is_array( $property_selector ) ? array_keys( $property_selector ) : [];
	}

	/**
	 * Returns expanded shorthand property values for important.
	 *
	 * This functions expands any shorthand property values for important based on the provided `$propertySelectorsShorthandMap`.
	 * If the given important value is a boolean, it is returned as is.
	 * If important is an array and it contains any shorthand property, theses are expanded to longhand properties.
	 * We then update the values in the important array and return the updated important array.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetExpandedShorthandImportant getExpandedShorthandImportant}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array|boolean $important                     An array key-value pairs with property name
	 *                                                        as the key and selectors as the value.
	 *                                                        This can be shorthand format property or longhand format property.
	 *     @type array         $propertySelectorsShorthandMap Optional. This is the map of shorthand properties
	 *                                                        to their longhand properties.
	 *                                                        Default `[]`.
	 * }
	 *
	 * @return array|bool
	 */
	public static function get_expanded_shorthand_important( array $args ) {
		static $cache = [];

		// Create a unique key for/using the given arguments.
		$key = md5( wp_json_encode( $args ) );

		// Return cached value if available.
		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}

		$important                        = $args['important'] ?? [];
		$property_selectors_shorthand_map = $args['propertySelectorsShorthandMap'] ?? [];

		if ( is_bool( $important ) ) {
			$cache[ $key ] = $important;

			return $important;
		}

		foreach ( $important as $breakpoint => $state_values ) {
			foreach ( $state_values as $state => $important_value ) {

				$important_values          = $important['desktop']['value'] ?? [];
				$complete_important_values = [];

				// Create complete important values for properties from the shorthand property important values.
				if ( ! empty( $important_value ) ) {
					foreach ( array_keys( $important_value ) as $possible_shorthand_property ) {
						if ( array_key_exists( $possible_shorthand_property, $property_selectors_shorthand_map ) ) {
							foreach ( $property_selectors_shorthand_map as $property_name_shorthand => $property_name_array ) {
								if ( $property_name_shorthand === $possible_shorthand_property ) {
									// Iterate over the mapped list of possible shorthand properties and construct a new list
									// of important values. For instance, transform `{ margin: true }` to
									// `{ margin-top: true, margin-right: true, margin-bottom: true, margin-left: true }`.

									foreach ( $property_name_array as $property_name ) {
										$complete_important_values[ $property_name ] = $important_value[ $possible_shorthand_property ];
									}
								}
							}
						}
					}

					if ( ! empty( $complete_important_values ) ) {
						$important[ $breakpoint ][ $state ] = $complete_important_values;
					}
				}
			}
		}

		$cache[ $key ] = $important;

		return $important;
	}

	/**
	 * Get CSS ruleset based on given selector and declaration.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetRuleset getRuleset}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $selector    The CSS selector.
	 *     @type string $declaration The CSS declaration.
	 * }
	 *
	 * @return string
	 */
	public static function get_ruleset( array $args ): string {
		$selector    = $args['selector'];
		$declaration = $args['declaration'];

		if ( '' === $selector ) {
			return $declaration;
		}

		return sprintf( '%1$s {%2$s}', $selector, $declaration );
	}

	/**
	 * Get CSS selector from property selectors based on sets of selectors, current state, and property name given.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetSelectorOfPropertySelectors getSelectorOfPropertySelectors}
	 * in `@divi/module` package.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array  $selectors            The selectors data.
	 *     @type string $propertyName         The name of the property.
	 *     @type string $breakpoint           Optional. The attribute breakpoint.
	 *                                        Can be either `desktop`, `tablet`, or `phone`. Default `desktop`.
	 *     @type string $state                Optional. The state of the selector.
	 *                                        Can be either `value`, `hover`, or `sticky`. Default `value`.
	 *     @type string $orderClass           Optional. Module CSS selector.
	 *     @type bool   $isInsideStickyModule Optional. Whether the module is inside sticky module or not.
	 * }
	 *
	 * @return string
	 */
	public static function get_selector_of_property_selectors( array $args ): string {
		$args = array_merge(
			[
				'breakpoint' => 'desktop',
				'state'      => 'value',
			],
			$args
		);

		$selectors     = $args['selectors'];
		$property_name = $args['propertyName'];
		$breakpoint    = $args['breakpoint'];
		$state         = $args['state'];
		$order_class   = $args['orderClass'] ?? null;

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Ger base selector for fallback in case current breakpoint + state selector doesn't exist.
		$base_selector            = $selectors['desktop']['value'][ $property_name ] ?? '';
		$is_desktop               = 'desktop' === $breakpoint;
		$is_default_state         = 'value' === $state;
		$breakpoint_base_selector = ! $is_desktop && ! $is_default_state && isset( $selectors[ $breakpoint ]['value'][ $property_name ] )
			? $selectors[ $breakpoint ]['value'][ $property_name ]
			: $base_selector;

		$current_state_selector = $selectors[ $breakpoint ][ $state ][ $property_name ] ?? '';

		if ( 'hover' === $state ) {
			// We'll need to always add `:hover` to the selector.
			return self::generate_hover_state_selector(
				! $current_state_selector ? $breakpoint_base_selector : $current_state_selector,
				$order_class
			);
		}

		if ( 'sticky' === $state ) {
			// We'll need to always add `.et_pb_sticky` to the selector.
			return self::generate_sticky_state_selector(
				! $current_state_selector ? $breakpoint_base_selector : $current_state_selector,
				$order_class,
				$is_inside_sticky_module
			);
		}

		return '' !== $current_state_selector ? $current_state_selector : $breakpoint_base_selector;
	}

	/**
	 * Get CSS selector based on sets of selectors, breakpoint, and state is given.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetSelector getSelector}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *      @type array  $selectors            An array of selectors for each breakpoint and state.
	 *      @type string $breakpoint           Optional. The breakpoint of the selector.
	 *                                         Can be either `desktop`, `tablet`, or `phone`. Default `desktop`.
	 *      @type string $state                Optional. The state of the selector.
	 *                                         Can be either `value`, `hover`, or `sticky`. Default `value`.
	 *      @type string $orderClass           Optional. The selector class name.
	 *      @type bool   $isInsideStickyModule Optional. Whether the module is inside sticky module or not.
	 * }
	 *
	 * @return string
	 */
	public static function get_selector( array $args ): string {
		$args = array_merge(
			[
				'breakpoint' => 'desktop',
				'state'      => 'value',
			],
			$args
		);

		$selectors   = $args['selectors'];
		$breakpoint  = $args['breakpoint'];
		$state       = $args['state'];
		$order_class = $args['orderClass'] ?? null;

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		// Get base selector for fallback in case current breakpoint + state selector doesn't exist.
		$base_selector            = $selectors['desktop']['value'] ?? '';
		$is_desktop               = 'desktop' === $breakpoint;
		$is_default_state         = 'value' === $state;
		$breakpoint_base_selector = ! $is_desktop && ! $is_default_state && isset( $selectors[ $breakpoint ]['value'] )
		? $selectors[ $breakpoint ]['value']
		: $base_selector;

		$current_state_selector = $selectors[ $breakpoint ][ $state ] ?? '';

		if ( 'hover' === $state ) {
			// We'll need to always add `:hover` to the selector.
			return self::generate_hover_state_selector(
				! $current_state_selector ? $breakpoint_base_selector : $current_state_selector,
				$order_class
			);
		}

		if ( 'sticky' === $state ) {
			// We'll need to always add `.et_pb_sticky` to the selector.
			return self::generate_sticky_state_selector(
				! $current_state_selector ? $breakpoint_base_selector : $current_state_selector,
				$order_class,
				$is_inside_sticky_module
			);
		}

		return '' !== $current_state_selector ? $current_state_selector : $breakpoint_base_selector;
	}

	/**
	 * Get CSS statement based on given At-rules and ruleset.
	 *
	 * If a non-string value is provided for `atRules`, the `ruleset` is returned as-is.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetStatement getStatement}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string|boolean $atRules At rules.
	 *     @type string         $ruleset The ruleset.
	 * }
	 *
	 * @return string
	 */
	public static function get_statement( array $args ): string {
		$at_rules = $args['atRules'];
		$ruleset  = $args['ruleset'];

		if ( is_string( $at_rules ) ) {
			return sprintf( '%1$s {%2$s}', $at_rules, $ruleset );
		}

		return $ruleset;
	}

	/**
	 * Group object of declarations based on given group name.
	 *
	 * Group declaration that has no group name
	 * into `otherDeclaration` group.
	 * Initially created to group declaration that has property-specific selector.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GroupDeclarationsByPropertySelectorNames groupDeclarationsByPropertySelectorNames}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $propertySelectorNames Zero indexed array of property selector names.
	 *     @type array $declarations          Key-value pair array of CSS property as the key and CSS declaration as the value.
	 * }
	 *
	 * @return array
	 */
	public static function group_declarations_by_property_selector_names( array $args ): array {
		$property_selector_names = $args['propertySelectorNames'];
		$declarations            = $args['declarations'];
		$grouped                 = [];
		$ungrouped               = [];

		foreach ( $declarations as $css_property => $declaration ) {
			if ( in_array( $css_property, $property_selector_names, true ) ) {
				$grouped[ $css_property ] = $css_property . ': ' . $declaration . ';';
			} else {
				$ungrouped[] = $css_property . ': ' . $declaration;
			}
		}

		if ( $ungrouped ) {
			$grouped['ungrouped'] = StyleLibraryUtils::join_declarations( $ungrouped );
		}

		return $grouped;
	}

	/**
	 * Checks if any of the given selectors has the 'hover' key.
	 *
	 * @since ??
	 *
	 * @param array $selectors The array of selectors to check.
	 *
	 * @return bool Returns true if any of the selectors has the 'hover' key, otherwise false.
	 */
	public static function has_hover_selectors( array $selectors ): bool {
		if ( empty( $selectors ) ) {
			return false;
		}

		foreach ( $selectors as $selector ) {
			if ( isset( $selector['hover'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replaces the hover selector placeholder in a given selector template.
	 *
	 * In FE, `{{:hover}}` suffix being replaced with ':hover'.
	 *
	 * @since ??
	 *
	 * @param array $selectors An array containing selectors property.
	 *
	 * @return array The modified selector template.
	 */
	public static function replace_hover_selector_placeholder( array $selectors ): array {
		return ModuleElementsUtils::interpolate_selector(
			[
				'selectorTemplate' => $selectors,
				'value'            => ':hover',
				'placeholder'      => '{{:hover}}',
			]
		);
	}

	/**
	 * Utils component to wrap style component output.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/StyleWrapper StyleWrapper}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *      @type array  $attr     An array of module attribute data.
	 *      @type string $children The content of the style tag.
	 * }
	 *
	 * @return string|array
	 */
	public static function style_wrapper( array $args ) {
		$attr     = $args['attr'];
		$children = $args['children'];

		if ( empty( $attr ) ) {
			return is_string( $children ) ? '' : [];
		}

		return $children;
	}

	/**
	 * Unpack property selectors shorthand map into property selectors.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/UnpackPropertySelectorsShorthandMap unpackPropertySelectorsShorthandMap}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $propertySelectors             The property selectors that you want to unpack.
	 *     @type array $propertySelectorsShorthandMap This is the map of shorthand properties to their longhand properties.
	 * }
	 *
	 * @return array
	 */
	public static function unpack_property_selectors_shorthand_map( array $args ): array {
		$property_selectors               = $args['propertySelectors'];
		$property_selectors_shorthand_map = $args['propertySelectorsShorthandMap'];

		// No need to continue if empty `propertySelectors` is given.
		if ( ! $property_selectors ) {
			return $property_selectors;
		}

		// No need to continue if empty `propertySelectorsShorthandMap` is given.
		if ( ! $property_selectors_shorthand_map ) {
			return $property_selectors;
		}

		// Passed property selector and unpacked property selector based from shorthand map
		// should be populated differently so later both can be merged in order to ensure passed
		// property selector has higher priority than unpacked selector.
		$specific_selectors = [];
		$unpacked_selectors = [];

		// Extracted shorthand css properties based on keys of shorthand map.
		$shorthand_css_properties = array_keys( $property_selectors_shorthand_map );

		foreach ( $property_selectors as $breakpoint => $state_value ) {
			foreach ( $state_value as $attr_state => $property_selector ) {
				foreach ( $property_selector as $property_name => $selector ) {
					if ( in_array( $property_name, $shorthand_css_properties, true ) ) {
						if ( isset( $property_selectors_shorthand_map[ $property_name ] ) && is_array( $property_selectors_shorthand_map[ $property_name ] ) ) {
							foreach ( $property_selectors_shorthand_map[ $property_name ] as $shorthand_property_name ) {
								if ( ! isset( $unpacked_selectors[ $breakpoint ] ) ) {
									$unpacked_selectors[ $breakpoint ] = [];
								}

								if ( ! isset( $unpacked_selectors[ $breakpoint ][ $attr_state ] ) ) {
									$unpacked_selectors[ $breakpoint ][ $attr_state ] = [];
								}

								$unpacked_selectors[ $breakpoint ][ $attr_state ][ $shorthand_property_name ] = $selector;
							}
						}
					} else {
						if ( ! isset( $specific_selectors[ $breakpoint ] ) ) {
							$specific_selectors[ $breakpoint ] = [];
						}

						if ( ! isset( $specific_selectors[ $breakpoint ][ $attr_state ] ) ) {
							$specific_selectors[ $breakpoint ][ $attr_state ] = [];
						}

						if ( ! isset( $specific_selectors[ $breakpoint ][ $attr_state ][ $property_name ] ) ) {
							$specific_selectors[ $breakpoint ][ $attr_state ][ $property_name ] = [];
						}

						$specific_selectors[ $breakpoint ][ $attr_state ][ $property_name ] = $selector;
					}
				}
			}
		}

		return array_replace_recursive( $unpacked_selectors, $specific_selectors );
	}
}
