<?php
/**
 * Module Options: Transition Utils Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transition;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * TransitionUtils class.
 *
 * This class provides a set of utility functions for working with CSS transitions.
 *
 * @since ??
 */
class TransitionUtils {

	/**
	 * Get animatable options for transitions.
	 *
	 * Returns an array of CSS properties that can be animated using CSS transitions.
	 * This function runs the value through the `divi_style_library_declarations_transition_animatable_options` filter hook.
	 *
	 * @since ??
	 *
	 * @return array An array of animatable options for transitions.
	 *
	 * @example:
	 * ```
	 * $animatable_options = TransitionUtils::get_animatable_options_array();
	 * // $animatable_options is now an array containing various CSS properties
	 * ```
	 *
	 * @example:
	 * ```
	 * // $animatable_options can be used to modify the array of animatable options through the 'divi_style_library_declarations_transition_animatable_options' filter hook
	 * $animatable_options = apply_filters( 'divi_style_library_declarations_transition_animatable_options', array() );
	 * ```
	 */
	public static function get_animatable_options_array(): array {
		$animatable_options = array(
			'font-size',
			'font-weight',
			'color',
			'letter-spacing',
			'line-height',
			'background',
			'background-color',
			'background-position',
			'background-size',
			'width',
			'height',
			'max-width',
			'max-height',
			'min-height',
			'padding',
			'padding-top',
			'padding-bottom',
			'padding-left',
			'padding-right',
			'margin',
			'margin-top',
			'margin-bottom',
			'margin-left',
			'margin-right',
			'border',
			'border-width',
			'border-color',
			'border-top-left-radius',
			'border-top-right-radius',
			'border-bottom-left-radius',
			'border-bottom-right-radius',
			'border-top-width',
			'border-top-color',
			'border-top-style',
			'border-right-width',
			'border-right-color',
			'border-right-style',
			'border-left-width',
			'border-left-color',
			'border-left-style',
			'border-bottom-width',
			'border-bottom-color',
			'border-bottom-style',
			'top',
			'bottom',
			'left',
			'right',
			'filter',
			'z-index',
			'text-shadow',
			'box-shadow',
			'transform',
			'transform-origin',
			'translate',
			'mask-size',
			'mask-position',
		);

		/**
		 * Filters animatable options for transitions.
		 *
		 * @since ??
		 *
		 * @param array $animatable_options The animatable options.
		 */
		return apply_filters( 'divi_style_library_declarations_transition_animatable_options', $animatable_options );
	}

	/**
	 * Get the required sticky property for a transition.
	 *
	 * This function is used to get the sticky property for a transition if a module has the sticky attribute.
	 *
	 * @since ??
	 *
	 * @param array $attrs  Array containing the sticky attribute and affecting attributes.
	 *
	 * @return array An array of options representing the sticky transition properties.
	 *               If there are no sticky properties, an empty array is returned.
	 *
	 * @example:
	 * ```php
	 * // Define the attributes
	 * $attrs = array(
	 *     'sticky' => true,
	 *     'background-color' => '#000',
	 *     'color' => '#fff',
	 * );
	 *
	 * // Get the sticky transition properties
	 * $sticky_properties = TransitionUtils::get_sticky_transition_property( $attrs );
	 *
	 * // Output the sticky transition properties
	 * print_r( $sticky_properties );
	 *
	 * // Output:
	 * // Array
	 * // (
	 * //     [0] => position:sticky
	 * //     [1] => background-color:#000
	 * //     [2] => color:#fff
	 * // )
	 * ```
	 */
	public static function get_sticky_transition_property( array $attrs ): array {
		$sticky_properties = self::compose_transition_css_properties( 'sticky', $attrs );

		return $sticky_properties;
	}

	/**
	 * Get the CSS transition properties for hover state.
	 *
	 * This function retrieves the CSS transition properties based on the provided attributes for the hover state of a module.
	 *
	 * @since ??
	 *
	 * @param array $attrs Array containing the attributes to compose the transition properties.
	 *
	 * @return array The CSS transition properties for the hover state.
	 *
	 * @example:
	 * ```php
	 *     $attrs = array(
	 *         'background-color' => '#000000',
	 *         'color' => '#ffffff',
	 *         'font-size' => '14px'
	 *     );
	 *     $hover_properties = TransitionUtils::get_hover_transition_property( $attrs );
	 *     print_r( $hover_properties );
	 *
	 *     // Array(
	 *     //     'transition-property: background-color, color, font-size',
	 *     //     'transition-duration: 0.3s',
	 *     //     'transition-timing-function: ease-in-out'
	 *     // )
	 * ```
	 */
	public static function get_hover_transition_property( array $attrs ): array {
		$hover_properties = self::compose_transition_css_properties( 'hover', $attrs );

		return $hover_properties;
	}

	/**
	 * Compose CSS transition properties based on mode and attributes.
	 *
	 * Retrieves the CSS transition properties based on the provided `mode` and attributes.
	 * The function loops through the attributes and calls various helper functions to get the respective transition properties.
	 * If the `mode` is empty, it returns an empty array.
	 *
	 * @since ??
	 *
	 * @param string $mode  Optional. The mode of the transition. One of `sticky`, or `hover`. Default empty string.
	 * @param array  $attrs Optional. An array of attributes to compose the transition properties. Default empty array.
	 *
	 * @return array An array of animatable CSS transition properties.
	 *
	 * @example:
	 * ```php
	 * // Define the mode and attributes
	 * $mode = 'hover';
	 * $attrs = array(
	 *     'background' => '#000000',
	 *     'border' => '1px solid #000000',
	 *     'font-size' => '14px'
	 * );
	 *
	 * // Get the CSS transition properties
	 * $transition_properties = TransitionUtils::compose_transition_css_properties($mode, $attrs);
	 *
	 * // Output the transition properties
	 * print_r($transition_properties);
	 *
	 * // Output:
	 * // Array (
	 * //   [0] => transition-property: background, border, font-size
	 * //   [1] => transition-duration: 0.3s
	 * //   [2] => transition-timing-function: ease-in-out
	 * // )
	 * ```
	 *
	 * @example:
	 * ```php
	 * // Define the mode and attributes
	 * $mode = 'sticky';
	 * $attrs = array(
	 *     'background' => '#000',
	 *     'color' => '#fff',
	 * );
	 *
	 * // Get the CSS transition properties
	 * $transition_properties = TransitionUtils::compose_transition_css_properties($mode, $attrs);
	 *
	 * // Output the transition properties
	 * print_r($transition_properties);
	 *
	 * // Output:
	 * // Array (
	 * //   [0] => transition-property: background, color
	 * //   [1] => transition-duration: 0.3s
	 * //   [2] => transition-timing-function: ease-in-out
	 * // )
	 * ```
	 */
	public static function compose_transition_css_properties( string $mode = '', array $attrs = [] ): array {

		if ( '' === $mode ) {
			return [];
		}

		unset( $attrs['transition'] );

		$css_properties           = self::get_composed_transition_css_properties( $attrs, $mode );
		$flattened_css_properties = [];
		$animatable_properties    = self::get_animatable_options_array();

		array_walk_recursive(
			$css_properties,
			function ( $css_property ) use ( &$flattened_css_properties ) {
				$flattened_css_properties[] = $css_property;
			}
		);

		if ( ! empty( $flattened_css_properties ) ) {
			$css_properties = array_unique( $flattened_css_properties );

			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get transition CSS properties based on the attribute key.
	 *
	 * @since ??
	 *
	 * @param string $attr_key The attribute key.
	 * @param array  $attr_value The attribute value.
	 * @param string $active_mode Only in FE. The active mode.
	 *
	 * @return array The transition CSS properties.
	 */
	private static function _get_transition_css_properties( string $attr_key, array $attr_value, string $active_mode ): array {
		switch ( $attr_key ) {
			case 'background':
				return self::get_transition_background_properties( $attr_value, $active_mode );
			case 'border':
				return self::get_transition_border_properties( $attr_value, $active_mode );
			case 'boxShadow':
				return self::get_transition_box_shadow_properties( $attr_value, $active_mode );
			case 'filters':
				return self::get_transition_filters_properties( $attr_value, $active_mode );
			case 'position':
				return self::get_transition_position_properties( $attr_value, $active_mode );
			case 'sizing':
				return self::get_transition_sizing_properties( $attr_value, $active_mode );
			case 'spacing':
				return self::get_transition_spacing_properties( $attr_value, $active_mode );
			case 'transform':
				return self::get_transition_transform_properties( $attr_value, $active_mode );
			case 'zIndex':
				return self::get_transition_zindex_properties( $attr_value, $active_mode );
			case 'font':
				return self::get_transition_font_properties( $attr_value, $active_mode );
			case 'textShadow':
				return self::get_transition_text_shadow_properties( $attr_value, $active_mode );
			case 'icon':
				return self::get_transition_icon_properties( $attr_value, $active_mode );
			default:
				return [];
		}
	}

	/**
	 * Get composed transition CSS properties based on passed module attributes.
	 *
	 * @since ??
	 *
	 * @param array  $module_attrs The module attributes.
	 * @param string $active_mode  Only on FE. The active mode.
	 *
	 * @return array The composed transition CSS properties.
	 */
	public static function get_composed_transition_css_properties( array $module_attrs, string $active_mode ): array {
		$all_properties = [];

		foreach ( $module_attrs as $attr_key => $attr_value ) {
			// Bail early if the attribute value is not an array.
			if ( ! is_array( $attr_value ) ) {
				continue;
			}

			// Bail early if the attribute value is empty.
			if ( empty( $attr_value ) ) {
				continue;
			}

			$properties = [];

			if ( isset( $attr_value['desktop'] ) ) {
				// Direct - Basically, most of the cases pass this condition.
				$properties = self::_get_transition_css_properties( $attr_key, $attr_value['desktop'], $active_mode );
			} elseif ( 'font' === $attr_key || 'textShadow' === $attr_key ) {
				// 1 Nested Level - Specific case where the main attribute value (desktop) is wrapped inside another property.
				$properties = self::get_composed_transition_css_properties( $attr_value, $active_mode );
			} elseif ( 'headingFont' === $attr_key || 'bodyFont' === $attr_key ) {
				// 2 Nested Levels - Specific case where the main attribute value (desktop) is wrapped inside other properties.
				$properties = array_reduce(
					array_keys( $attr_value ),
					function ( $nested_all_properties, $nested_attr_key ) use ( $attr_value, $active_mode ) {
						$nested_attr_value = $attr_value[ $nested_attr_key ];
						$nested_properties = self::get_composed_transition_css_properties( $nested_attr_value, $active_mode );
						if ( ! empty( $nested_properties ) ) {
							array_push( $nested_all_properties, ...$nested_properties );
						}
						return $nested_all_properties;
					},
					[]
				);
			}

			if ( ! empty( $properties ) ) {
				array_push( $all_properties, ...$properties );
			}
		}

		return $all_properties;
	}

	/**
	 * Get the transition properties based on the given attributes, hover state, and sticky state.
	 *
	 * This function retrieves the transition properties for a specific element based on its attributes,
	 * hover state, and sticky state. It uses the TransitionUtils class to fetch the necessary properties.
	 *
	 * @since ??
	 *
	 * @param array $attrs  The attributes of the element.
	 * @param bool  $hover  Whether the element is in hover state or not.
	 * @param bool  $sticky Whether the element is in sticky state or not.
	 *
	 * @return array The array of transition properties.
	 *
	 * @example:
	 * ```php
	 * $attrs = array(
	 *     'color',
	 *     'background-color',
	 *     'padding',
	 * );
	 *
	 * $hover = true;
	 * $sticky = false;
	 *
	 * $properties = TransitionUtils::get_transition_properties( $attrs, $hover, $sticky );
	 * // Returns: array('color', 'background-color', 'padding')
	 * ```
	 *
	 * @example:
	 * ```php
	 * $attrs = array(
	 *     'font-size',
	 *     'line-height',
	 *     'margin',
	 * );
	 *
	 * $hover = false;
	 * $sticky = true;
	 *
	 * $properties = TransitionUtils::get_transition_properties( $attrs, $hover, $sticky );
	 * // Returns: array('font-size', 'line-height', 'margin')
	 * ```
	 *
	 * @example:
	 * ```php
	 * $attrs = array(
	 *     'width',
	 *     'height',
	 *     'opacity',
	 * );
	 *
	 * $hover = true;
	 * $sticky = true;
	 *
	 * $properties = TransitionUtils::get_transition_properties( $attrs, $hover, $sticky );
	 * // Returns: array('width', 'height', 'opacity')
	 * ```
	 */
	public static function get_transition_properties( array $attrs, bool $hover, bool $sticky ): array {

		$transition_properties        = array();
		$transition_hover_properties  = array();
		$transition_sticky_properties = array();

		if ( $hover ) {
			$transition_hover_properties = self::get_hover_transition_property( $attrs );
		}

		if ( $sticky ) {
			$transition_sticky_properties = self::get_sticky_transition_property( $attrs );
		}

		$transition_properties = array_unique( array_merge( $transition_hover_properties, $transition_sticky_properties ) );

		return $transition_properties;
	}

	/**
	 * Sorts a string of CSS properties in alphabetical order.
	 *
	 * This function takes a string of CSS properties, splits it into an array, sorts the array in alphabetical order,
	 * and then joins the sorted array back into a string.
	 * The resulting string will have the properties in alphabetical order.
	 *
	 * @since ??
	 *
	 * @param string $props The string of CSS properties.
	 *
	 * @return string The sorted string of CSS properties.
	 *
	 * @example:
	 * ```php
	 *   $properties = 'color, font-size, background-color';
	 *   $sorted_properties = TransitionUtils::sort_css_properties( $properties );
	 *
	 *   // Output: 'background-color, color, font-size'
	 * ```
	 *
	 * @example:
	 * ```php
	 *   $properties = '';
	 *   $sorted_properties = TransitionUtils::sort_css_properties( $properties );
	 *
	 *   // Output: ''
	 * ```
	 *
	 * @example:
	 * ```php
	 *   $properties = 'font-weight, margin, padding';
	 *   $sorted_properties = TransitionUtils::sort_css_properties( $properties );
	 *
	 *   // Output: 'font-weight, margin, padding'
	 * ```
	 */
	public static function sort_css_properties( string $props ): string {

		if ( '' === $props ) {
			return '';
		}

		// Remove leading and trailing whitespace.
		$string = trim( $props );

		// Split the string into an array of properties.
		$properties = explode( ',', $string );

		// Sort the properties in alphabetical order.
		sort( $properties );

		// Join the sorted properties into a string.
		$sorted_string = implode( ',', $properties );

		return $sorted_string;
	}

	/**
	 * Get animatable transition attributes for module elements.
	 *
	 * This function takes an array of module attributes and returns an array of transition attributes for each element.
	 * The transition attributes are automatically generated based on the module's transition attributes.
	 *
	 * @since ??
	 *
	 * @param array $attrs The module attributes.
	 *
	 * @return array The transition attributes for each element.
	 *
	 * @example:
	 * ```php
	 * // Example usage:
	 * $original_module_attrs = [
	 *     'module' => [
	 *         // Module attributes
	 *     ],
	 * ];
	 * $transition_attrs = TransitionUtils::get_module_elements_transition_attrs( $original_module_attrs );
	 *
	 * // $transition_attrs will contain the generated transition attributes for each element.
	 * ```
	 */
	public static function get_module_elements_transition_attrs( array $attrs ): array {
		$transition_default_attrs['desktop']['value'] = [
			'duration'   => '300ms',
			'delay'      => '0ms',
			'speedCurve' => 'ease',
		];

		$module_transition_attr = $attrs['module']['decoration']['transition'] ?? $transition_default_attrs;
		$transition_attrs       = [];

		// If Transition is enabled, we need to add transition styles to the module.
		if ( is_array( $module_transition_attr ) && ! empty( $module_transition_attr ) ) {
			// Check if $attrs is an array and contain module attribute.
			if ( is_array( $attrs ) && ! empty( $attrs ) ) {
				foreach ( $attrs as $attr_name => $attr ) {
					// Skip for css attribute.
					if ( 'css' === $attr_name ) {
						continue;
					}

					// Skip if module already has transition option.
					if ( 'module' === $attr_name && isset( $attrs['module']['decoration']['transition'] ) && ! empty( $attrs['module']['decoration']['transition'] ) ) {
						continue;
					}

					// Skip if there's no decoration for this element. No point of passing transition_attrs in this case.
					if ( ! isset( $attrs[ $attr_name ]['decoration'] ) ) {
						continue;
					}

					if ( is_array( $attrs ) ) {
						$transition_attrs[ $attr_name ]['decoration']['transition'] = $module_transition_attr;
					}
				}
			}
		}

		return $transition_attrs;
	}

	/**
	 * Get CSS properties from the background options.
	 *
	 * This function retrieves the CSS transition background properties from the background options provided in the module attributes.
	 *
	 * @since ??
	 *
	 * @param array  $attr The module attribute.
	 * @param string $mode The mode of the element. One of `sticky`, or `hover`.
	 *
	 * @return array An array of animatable CSS properties.
	 *
	 * ```php
	 * $attr = [
	 *    'desktop' => [
	 *        'color' => '#000000',
	 *        'image' => [
	 *            'url' => 'example.com/image.jpg',
	 *            'repeat' => 'no-repeat',
	 *        ],
	 *    ],
	 *    'mobile' => [
	 *        'color' => '#ffffff',
	 *    ],
	 * ];
	 *
	 * $properties = TransitionUtils::get_transition_background_properties( $attr, 'hover' );
	 * / / Returns: [ 'background-color', 'background-url', 'background-repeat' ]
	 * ```
	 */
	public static function get_transition_background_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $background_values ) {
				if ( $mode === $attr_key && is_array( $background_values ) && ! empty( $background_values ) ) {
					foreach ( $background_values as $background_key => $background_value ) {
						if ( 'color' === $background_key ) {
							$css_properties[] = 'background-color';
						} elseif ( 'image' === $background_key ) {
							if ( is_array( $background_value ) && ! empty( $background_value ) ) {
								foreach ( array_keys( $background_value ) as $origin_key ) {
									$css_properties[] = 'background-' . $origin_key;
								}
							}
						} elseif ( 'mask' === $background_key ) {
							if ( is_array( $background_value ) && ! empty( $background_value ) ) {
								foreach ( array_keys( $background_value ) as $origin_key ) {
									$css_properties[] = 'mask-' . $origin_key;
								}
							}
						} else {
							$css_properties[] = $background_key;
						}
					}
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get the transition border properties for a given set of attributes and mode.
	 *
	 * This function retrieves the CSS properties for the transition border animation based on the provided attributes
	 * and mode. It iterates through the attribute array and checks if the mode matches the attribute key. If there is a match,
	 * it then checks if the border values for that mode are not empty. If they are not empty, it proceeds to iterate through
	 * the border values and checks if the border key is 'radius' or 'styles'. For 'radius', it checks if the value is not empty
	 * and then retrieves the corresponding CSS properties based on the border radius key. For 'styles', it checks if the value is not empty
	 * and then retrieves the corresponding CSS properties based on the border position key and border style key. It also checks if
	 * the 'all' key exists and retrieves the CSS properties for the border style key if it is not 'style'.
	 *
	 * @since ??
	 *
	 * @param array  $attr The array of attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array The CSS properties for the transition border animation.
	 *
	 * @example:
	 * ```php
	 * // Get transition border properties for a given set of attributes and mode.
	 * $attr = array(
	 *     'border' => array(
	 *         'desktop' => array(
	 *             'radius' => array(
	 *                 'topLeft' => '10px',
	 *                 'topRight' => '10px',
	 *                 'bottomLeft' => '10px',
	 *                 'bottomRight' => '10px',
	 *             ),
	 *             'styles' => array(
	 *                 'top' => array(
	 *                     'style' => 'solid',
	 *                     'width' => '1px',
	 *                     'color' => '#000000',
	 *                 ),
	 *                 'bottom' => array(
	 *                     'style' => 'solid',
	 *                     'width' => '1px',
	 *                     'color' => '#000000',
	 *                 ),
	 *             ),
	 *         ),
	 *     ),
	 * );
	 * $mode = 'hover';
	 * $properties = TransitionUtils::get_transition_border_properties( $attr, $mode );
	 *
	 * // Result: Array(
	 * //     [0] => 'border-top-left-radius',
	 * //     [1] => 'border-top-right-radius',
	 * //     [2] => 'border-bottom-left-radius',
	 * //     [3] => 'border-bottom-right-radius',
	 * //     [4] => 'border-top-solid',
	 * //     [5] => 'border-top-width',
	 * //     [6] => 'border-top-color',
	 * //     [7] => 'border-bottom-solid',
	 * //     [8] => 'border-bottom-width',
	 * //     [9] => 'border-bottom-color',
	 * // )
	 * ```
	 */
	public static function get_transition_border_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $border_values ) {
				if ( $mode === $attr_key && is_array( $border_values ) && ! empty( $border_values ) ) {
					foreach ( $border_values as $border_key => $border_value ) {
						if ( 'radius' === $border_key ) {
							if ( is_array( $border_value ) && ! empty( $border_value ) ) {
								foreach ( $border_value as $border_radius_key => $border_radius_value ) {
									if ( 'topLeft' === $border_radius_key ) {
										$css_properties[] = 'border-top-left-radius';
									} elseif ( 'topRight' === $border_radius_key ) {
										$css_properties[] = 'border-top-right-radius';
									} elseif ( 'bottomLeft' === $border_radius_key ) {
										$css_properties[] = 'border-bottom-left-radius';
									} elseif ( 'bottomRight' === $border_radius_key ) {
										$css_properties[] = 'border-bottom-right-radius';
									}
								}
							}
						} elseif ( 'styles' === $border_key ) {
							if ( is_array( $border_value ) && ! empty( $border_value ) ) {
								foreach ( $border_value as $border_position_key => $border_position_values ) {
									if ( is_array( $border_position_values ) && ! empty( $border_position_values ) ) {
										foreach ( array_keys( $border_position_values ) as $border_style_key ) {
											$css_properties[] = 'border-' . $border_position_key . '-' . $border_style_key;
										}
									}
								}
								if ( array_key_exists( 'all', $border_value ) ) {
									$border_styles = $border_value['all'] ?? array();
									if ( is_array( $border_styles ) && ! empty( $border_styles ) ) {
										foreach ( array_keys( $border_styles ) as $border_style_key ) {
											if ( 'style' !== $border_style_key ) {
												$css_properties[] = 'border-' . $border_style_key;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get CSS properties from the box shadow options.
	 *
	 * This function retrieves the CSS properties from the box shadow options provided in the module attributes.
	 *
	 * @since ??
	 *
	 * @param array  $attr Array of module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of animatable CSS properties.
	 *               Returns an empty array if no `$mode` is given or if the `$attr` array is empty.
	 */
	public static function get_transition_box_shadow_properties( array $attr, string $mode ): array {
		$css_properties = [];

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $attr_value ) {
				if ( $mode === $attr_key && is_array( $attr_value ) && ! empty( $attr_value ) ) {
					$css_properties[] = 'box-shadow';
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get CSS properties from the filter options.
	 *
	 * This function retrieves the CSS properties from the filter options array based on the `mode` of the element.
	 *
	 * @param array  $attr The module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *     $attr = [
	 *         'filter' => [
	 *             'hover' => [
	 *                 'property1' => 'value1',
	 *                 'property2' => 'value2',
	 *             ],
	 *             'sticky' => [
	 *                 'property1' => 'value1',
	 *                 'property2' => 'value2',
	 *             ],
	 *         ],
	 *     ];
	 *     $mode = 'hover';
	 *     $css_properties = TransitionUtils::get_transition_filters_properties($attr, $mode);
	 *     // Returns:
	 *     // [
	 *     //   'property1' => 'value1',
	 *     //   'property2' => 'value2',
	 *     // ],
	 *
	 *     $attr = [
	 *         'filter' => [
	 *             'hover' => [
	 *                 'property1' => 'value1',
	 *                 'property2' => 'value2',
	 *             ],
	 *         ],
	 *     ];
	 *     $mode = 'sticky';
	 *     $css_properties = TransitionUtils::get_transition_filters_properties($attr, $mode);
	 *     // Returns []
	 * ```
	 */
	public static function get_transition_filters_properties( array $attr, string $mode ): array {
		$css_properties = [];

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $attr_value ) {
				if ( $mode === $attr_key && is_array( $attr_value ) && ! empty( $attr_value ) ) {
					$css_properties[] = 'filter';
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get CSS properties from the position options.
	 *
	 * This function takes an array of module attributes and a `mode` as input.
	 * It iterates through the attributes and checks if the `mode` matches the attribute key.
	 * If a match is found, it retrieves the position values and further processes them.
	 *
	 * @since ??
	 *
	 * @param array  $attr An array of module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties. Returns an empty array on failure.
	 *
	 * @example:
	 * ```php
	 *     $attr = array(
	 *         'position' => array(
	 *             'desktop' => array(
	 *                 'origin' => array(
	 *                     'top left',
	 *                     'center center',
	 *                     'bottom right'
	 *                 )
	 *             )
	 *         )
	 *     );
	 *     $mode = 'desktop';
	 *     $css_properties = get_transition_position_properties($attr, $mode);
	 *     // Returns ['top', 'left', 'center', 'bottom', 'right']
	 * ```
	 *
	 * @example:
	 * ```php
	 *     $attr = array(
	 *         'position' => array(
	 *             'desktop' => array(
	 *                 'origin' => array(
	 *                     'top left',
	 *                     '',
	 *                     'center center'
	 *                 )
	 *             )
	 *         )
	 *     );
	 *     $mode = 'desktop';
	 *     $css_properties = get_transition_position_properties($attr, $mode);
	 *     // Returns ['top', 'left', 'center']
	 * ```
	 */
	public static function get_transition_position_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $position_values ) {
				if ( $mode === $attr_key && is_array( $position_values ) && ! empty( $position_values ) ) {
					foreach ( $position_values as $position_key => $position_value ) {
						if ( 'origin' === $position_key ) {
							if ( ! empty( $position_value ) ) {
								foreach ( $position_value as $origin_value ) {
									if ( '' !== $origin_value ) {
										$origin_value = explode( ' ', $origin_value );
										if ( $origin_value ) {
											foreach ( $origin_value as $value ) {
												if ( $value ) {
													$css_properties[] = $value;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get the CSS properties for the sizing options.
	 *
	 * This function retrieves the CSS properties from the sizing options provided in the module attributes.
	 * It checks if the given mode matches the attribute key and if the sizing values are not empty.
	 * The function then iterates through the sizing values and determines the CSS property based on the sizing key.
	 * If the sizing key matches any of the predefined keys (e.g., maxHeight, minHeight, maxWidth, minWidth),
	 * the corresponding CSS property name is added to the $css_properties array. Otherwise, the sizing key itself is added.
	 * The function then removes any duplicate CSS properties and filters out any properties that are not animatable.
	 *
	 * @since ??
	 *
	 * @param array  $attr Array of module attributes.
	 * @param string $mode Mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array Array of CSS properties for transition sizing. Empty array if no valid properties found.
	 *
	 * @example:
	 * ```php
	 *     $attr = array(
	 *         'desktop' => array(
	 *             'maxHeight' => '100px',
	 *             'minWidth' => '50px',
	 *         ),
	 *     );
	 *     $mode = 'desktop';
	 *
	 *     $result = TransitionUtils::get_transition_sizing_properties($attr, $mode);
	 *
	 *     // $result = ['max-height', 'min-width']
	 * ```
	 */
	public static function get_transition_sizing_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $sizing_values ) {
				if ( $mode === $attr_key && is_array( $sizing_values ) && ! empty( $sizing_values ) ) {
					foreach ( array_keys( $sizing_values ) as $sizing_key ) {
						if ( 'maxHeight' === $sizing_key ) {
							$css_properties[] = 'max-height';
						} elseif ( 'minHeight' === $sizing_key ) {
							$css_properties[] = 'min-height';
						} elseif ( 'maxWidth' === $sizing_key ) {
							$css_properties[] = 'max-width';
						} elseif ( 'minWidth' === $sizing_key ) {
							$css_properties[] = 'min-width';
						} else {
							$css_properties[] = $sizing_key;
						}
					}
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get the spacing CSS properties for a given set of module attributes.
	 *
	 * This function iterates over the attributes array and extracts the CSS properties related to spacing for the provided mode.
	 * It checks if the attribute key matches the mode and if the corresponding value is an array with non-empty contents.
	 * For each spacing value, it extracts the individual spacing keys and appends them to the CSS properties array.
	 * The final array is filtered to remove any properties that are not animatable.
	 *
	 * @since ??
	 *
	 * @param array  $attr The module attributes array.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of spacing CSS properties applicable for the given mode, or an empty array if no properties found.
	 *
	 * @example:
	 * ```php
	 * $attributes = [
	 *     'desktop' => [
	 *         'padding' => [
	 *             'top' => '10px',
	 *             'bottom' => '10px',
	 *         ],
	 *         'margin' => [
	 *             'left' => '20px',
	 *             'right' => '20px',
	 *         ],
	 *     ],
	 *     'hover' => [
	 *         'padding' => [
	 *             'top' => '20px',
	 *             'bottom' => '20px',
	 *         ],
	 *     ],
	 * ];
	 * $mode = 'hover';
	 * $spacing_properties = TransitionUtils::get_transition_spacing_properties($attributes, $mode);
	 * // Result: ['padding-top', 'padding-bottom']
	 * ```
	 */
	public static function get_transition_spacing_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $spacing_values ) {
				if ( $mode === $attr_key && is_array( $spacing_values ) && ! empty( $spacing_values ) ) {
					foreach ( $spacing_values as $spacing_key => $spacing_value ) {
						if ( is_array( $spacing_value ) && ! empty( $spacing_value ) ) {
							foreach ( array_keys( $spacing_value ) as $spacing_value_key ) {
								$css_properties[] = $spacing_key . '-' . $spacing_value_key;
							}
						}
					}
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get the CSS properties from the transform options.
	 *
	 * @since ??
	 *
	 * @param array  $attr The module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties. Returns an empty array if there are no CSS properties.
	 *
	 * @example:
	 * ```php
	 * $attr = [
	 *     'transform' => [
	 *         'desktop' => [
	 *             'translateX' => '20px',
	 *             'translateY' => '10px',
	 *         ],
	 *         'hover' => [
	 *             'rotate' => '45deg',
	 *         ],
	 *     ],
	 * ];
	 * $mode = 'desktop';
	 *
	 * $css_properties = TransitionUtils::get_transition_transform_properties($attr, $mode);
	 * // Returns ['translateX', 'translateY', 'rotate']
	 * ```
	 */
	public static function get_transition_transform_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $transform_values ) {
				if ( $mode === $attr_key && is_array( $transform_values ) && ! empty( $transform_values ) ) {
					foreach ( $transform_values as $transform_key => $transform_value ) {
						if ( 'origin' === $transform_key ) {
							if ( array_key_exists( 'x', $transform_value ) && array_key_exists( 'y', $transform_value ) ) {
								$css_properties[] = 'transform-origin';
							}
						} else {
							$css_properties[] = $transform_key;
						}
					}

					$css_properties[] = 'transform';
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get CSS properties from the z-index options.
	 *
	 * This function retrieves the CSS properties related to z-index based on the provided module attributes and mode.
	 *
	 * @since ??
	 *
	 * @param array  $attr The module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties for z-index.
	 *               Returns an empty array if no properties are found.
	 *
	 * @example:
	 * ```php
	 *      $attr = array(
	 *          'zIndex' => array(
	 *              'desktop' => '10',
	 *              'tablet' => '5',
	 *              'phone' => '2',
	 *          ),
	 *      );
	 *      $mode = 'hover';
	 *      $css_properties = TransitionUtils::get_transition_zindex_properties($attr, $mode);
	 *      // Returns: ['z-index']
	 * ```
	 */
	public static function get_transition_zindex_properties( array $attr, string $mode ): array {
		$css_properties = [];

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $attr_value ) {
				if ( $mode === $attr_key && is_string( $attr_value ) && '' !== $attr_value ) {
					$css_properties[] = 'z-index';
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get transition font properties from module attributes.
	 *
	 * This function retrieves the CSS properties related to font transitions from the given module attributes.
	 * It checks the provided attributes array and extracts the font properties based on the specified `mode`.
	 * The extracted font properties are then filtered to remove duplicates and properties that are not animatable.
	 *
	 * @since ??
	 *
	 * @param array  $attr The module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties related to font transitions.
	 *               Returns an empty array if no valid properties are found.
	 *
	 * @example:
	 * ```php
	 *   $module_attr = [
	 *     'size' => [
	 *       'normal' => ['desktop' => [12]],
	 *       'hover' => ['desktop' => [14]],
	 *     ],
	 *     'weight' => [
	 *       'normal' => ['desktop' => ['normal']],
	 *       'hover' => ['desktop' => ['bold']],
	 *     ],
	 *   ];
	 *   $mode = 'hover';
	 *
	 *   $css_properties = TransitionUtils::get_transition_font_properties($module_attr, $mode);
	 *
	 *   // Expected output: ['font-size', 'font-weight']
	 * ```
	 *
	 * @example:
	 * ```php
	 *
	 *   $module_attr = [
	 *     'size' => [
	 *       'normal' => ['desktop' => [12]],
	 *       'hover' => ['desktop' => [14]],
	 *     ],
	 *     'weight' => [
	 *       'normal' => ['desktop' => ['normal']],
	 *       'hover' => ['desktop' => ['bold']],
	 *     ],
	 *   ];
	 *   $mode = 'sticky';
	 *
	 *   $css_properties = TransitionUtils::get_transition_font_properties($module_attr, $mode);
	 *
	 *   // Expected output: []
	 * ```
	 */
	public static function get_transition_font_properties( array $attr, string $mode ): array {
		$css_properties        = [];
		$animatable_properties = self::get_animatable_options_array();

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $font_values ) {
				if ( $mode === $attr_key && is_array( $font_values ) && ! empty( $font_values ) ) {
					foreach ( array_keys( $font_values ) as $text_font ) {
						if ( 'size' === $text_font ) {
							$css_properties[] = 'font-size';
						} elseif ( 'weight' === $text_font ) {
							$css_properties[] = 'font-weight';
						} elseif ( 'letterSpacing' === $text_font ) {
							$css_properties[] = 'letter-spacing';
						} elseif ( 'lineHeight' === $text_font ) {
							$css_properties[] = 'line-height';
						} else {
							$css_properties[] = $text_font;
						}
					}
				}
			}
		}

		if ( ! empty( $css_properties ) ) {
			$css_properties = array_unique( $css_properties );
			foreach ( $css_properties as $css_property_key => $css_property ) {
				if ( ! in_array( $css_property, $animatable_properties, true ) ) {
					unset( $css_properties[ $css_property_key ] );
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get the CSS properties for text-shadow options.
	 *
	 * This function takes an array of module attributes and a mode of the element (either hover or sticky) and
	 * returns an array of CSS properties for text-shadow options.
	 *
	 * @since ??
	 *
	 * @param array  $attr Array of module attributes.
	 * @param string $mode Mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties for text-shadow options. Returns an empty array on failure.
	 *
	 * @example:
	 * ```php
	 *  $attr = array(
	 *      'textShadow' => array(
	 *          'desktop' => array(
	 *              'color' => '#000000',
	 *              'x' => 2,
	 *              'y' => 2,
	 *              'blur' => 4,
	 *          ),
	 *      ),
	 *  );
	 *  $mode = 'hover';
	 *  $text_shadow_transition_properties = TransitionUtils::get_transition_text_shadow_properties( $attr, $mode );
	 *  // Returns: array( 'text-shadow' ).
	 * ```
	 */
	public static function get_transition_text_shadow_properties( array $attr, string $mode ): array {
		$css_properties = [];

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $attr_value ) {
				if ( $mode === $attr_key && is_array( $attr_value ) && ! empty( $attr_value ) ) {
					$css_properties[] = 'text-shadow';
				}
			}
		}

		return $css_properties;
	}

	/**
	 * Get CSS properties from the icon options.
	 *
	 * This function retrieves the CSS properties from the icon options array based on the `mode` of the element.
	 *
	 * @param array  $attr The module attributes.
	 * @param string $mode The mode of the element. One of `hover`, or `sticky`.
	 *
	 * @return array An array of CSS properties.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 *     $attr = [
	 *         'icon' => [
	 *             'hover' => [
	 *                 'color' => 'value1',
	 *                 'size' => 'value2',
	 *             ],
	 *             'sticky' => [
	 *                 'color' => 'value1',
	 *                 'size' => 'value2',
	 *             ],
	 *         ],
	 *     ];
	 *     $mode = 'hover';
	 *     $css_properties = TransitionUtils::get_transition_icon_properties($attr, $mode);
	 *     // Returns:
	 *     // ['color', 'font-size', 'line-height'],
	 *
	 *     $attr = [
	 *         'filter' => [
	 *             'hover' => [
	 *                 'color' => 'value1',
	 *                 'size' => 'value2',
	 *             ],
	 *         ],
	 *     ];
	 *     $mode = 'sticky';
	 *     $css_properties = TransitionUtils::get_transition_icon_properties($attr, $mode);
	 *     // Returns []
	 * ```
	 */
	public static function get_transition_icon_properties( array $attr, string $mode ): array {
		$css_properties = [];

		if ( is_array( $attr ) && ! empty( $attr ) ) {
			foreach ( $attr as $attr_key => $icon_values ) {
				if ( $mode === $attr_key && is_array( $icon_values ) && ! empty( $icon_values ) ) {
					foreach ( $icon_values as $icon_value_key => $icon_value ) {
						if ( 'color' === $icon_value_key ) {
							$css_properties[] = 'color';
						} elseif ( 'size' === $icon_value_key ) {
							$css_properties[] = 'font-size';
							$css_properties[] = 'line-height';
							$css_properties[] = 'margin-top';
							$css_properties[] = 'margin-left';
						}
					}
				}
			}
		}

		return $css_properties;
	}
}
