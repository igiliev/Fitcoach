<?php
/**
 * Transition::style_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transition\TransitionTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\StyleLibrary\Declarations\Transition\TransitionUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait StyleDeclarationTrait {

	/**
	 * Get transition CSS declaration based on given arguments.
	 *
	 * This function accepts an array of arguments that define the style declaration.
	 * It parses the arguments, sets default values, and generates a CSS style declaration based on the provided arguments.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/transition-style-declaration/ transitionStyleDeclaration}
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
	 * @return array|string The generated transform CSS style declaration.
	 */
	public static function style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'important'  => false,
				'returnType' => 'string',
			)
		);

		$attr_value          = $args['attrValue'];
		$attrs               = $args['attrValue']['moduleAttrs'] ?? [];
		$advanced_properties = isset( $args['attrValue']['advancedProperties'] ) ? $args['attrValue']['advancedProperties'] : [];
		$sticky              = $args['attrValue']['sticky'] ?? false;
		$hover               = $args['attrValue']['hover'] ?? false;
		$important           = $args['important'];
		$return_type         = $args['returnType'];

		$style_declarations = new StyleDeclarations(
			array(
				'important'  => $important,
				'returnType' => $return_type,
			)
		);

		$transition_properties = TransitionUtils::get_transition_properties( $attrs, $hover, $sticky );
		$transition_duration   = array_key_exists( 'duration', $attr_value ) && $attr_value['duration'] && '' !== $attr_value['duration'] ? $attr_value['duration'] : '300ms';
		$transition_delay      = array_key_exists( 'delay', $attr_value ) && $attr_value['delay'] && '' !== $attr_value['delay'] ? $attr_value['delay'] : '0ms';
		$transition_speed      = array_key_exists( 'speedCurve', $attr_value ) && $attr_value['speedCurve'] && '' !== $attr_value['speedCurve'] ? $attr_value['speedCurve'] : '';

		if ( ! empty( $advanced_properties ) ) {
			$transition_properties = array_merge( $transition_properties, $advanced_properties );

			// Remove duplicate transition CSS properties.
			$transition_properties = array_values( array_unique( $transition_properties ) );
		}

		switch ( $transition_speed ) {
			case 'easeInOut':
				$transition_timing_function = 'ease-in-out';
				break;
			case 'ease':
				$transition_timing_function = 'ease';
				break;
			case 'easeIn':
				$transition_timing_function = 'ease-in';
				break;
			case 'easeOut':
				$transition_timing_function = 'ease-out';
				break;
			case 'linear':
				$transition_timing_function = 'linear';
				break;
			default:
				$transition_timing_function = 'ease';
		}

		if ( empty( $transition_properties ) ) {
			return '';
		}

		$transition_properties_string = implode( ',', $transition_properties );
		$transition_properties_string = TransitionUtils::sort_css_properties( $transition_properties_string );

		if ( $transition_properties_string ) {
			$style_declarations->add( 'transition-property', $transition_properties_string );
		}
		if ( $transition_duration ) {
			$style_declarations->add( 'transition-duration', $transition_duration );
		}
		if ( $transition_timing_function ) {
			$style_declarations->add( 'transition-timing-function', $transition_timing_function );
		}
		if ( $transition_delay ) {
			$style_declarations->add( 'transition-delay', $transition_delay );
		}

		return $style_declarations->value();

	}
}
