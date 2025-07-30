<?php
/**
 * Dividers class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Dividers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\DividerLibrary\Utils\DividerUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Dividers\Utils\DividersStyleUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use Exception;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Utils\BackgroundStyleUtils;

/**
 * Dividers class for working with Dividers style declaration
 *
 * @since ??
 */
class Dividers {

	/**
	 * Dividers style declaration constants.
	 *
	 * This const is equivalent of JS const:
	 * {@link /docs/builder-api/js/style-library/dividers-default-attr dividersDefaultAttr} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @var array $dividers_default_attr
	 */
	public static $dividers_default_attr = [
		'top'    => [
			'style'       => 'none',
			'height'      => '100px',
			'repeat'      => '1x',
			'arrangement' => 'below',
		],
		'bottom' => [
			'style'       => 'none',
			'height'      => '100px',
			'repeat'      => '1x',
			'arrangement' => 'below',
		],
	];

	/**
	 * Get Dividers CSS declaration based on given attrValue.
	 *
	 * This function is equivalent to the JS function:
	 * {@link /docs/builder-api/js/style-library/dividers-style-declaration dividersStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $attrValue         The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important    Optional. Whether to add `!important` tag. Default `false`.
	 *     @type array|string $returnType Optional. This is the type of value that the function will return.
	 *                                    Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @throws Exception If divider style is not found by `DividerUtils::get_divider_json()`.
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		if ( 0 >= count( $args['attrValue'] ) ) {
			return '';
		}

		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		// Load default so if the attribute lacks required value, it'll be rendered using default.
		$placement            = $args['additional']['placement'] ?? $args['placement'] ?? '';
		$divider_default_attr = self::$dividers_default_attr[ $placement ] ?? [];
		$default_attr         = $args['attrValue']['defaultAttr'] ?? $divider_default_attr;
		$default_attr         = array_merge( $divider_default_attr, $default_attr );
		$values               = array_merge( $default_attr, $args['attrValue'] ?? [] );

		// Get Dividers settings value.
		$style       = $values['style'] ?? '';
		$flip        = $values['flip'] ?? [];
		$height      = $values['height'] ?? '';
		$repeat      = $values['repeat'] ?? '';
		$arrangement = $values['arrangement'] ?? '';
		$fullwidth   = $values['fullwidth'] ?? '';

		$return_type = $args['returnType'] ?? 'string';
		$important   = $args['important'] ?? false;

		$style_declarations = new StyleDeclarations(
			[
				'returnType' => $return_type,
				'important'  => $important,
			]
		);

		if ( $style && 'none' !== $style ) {
			$background_colors       = $args['additional']['backgroundColors'] ?? [];
			$sibling_background_attr = $background_colors['siblingBackgroundAttr'] ?? null;
			$module_background_attr  = $background_colors['moduleBackgroundAttr'] ?? null;
			$default_color           = $background_colors['defaultColor'] ?? null;
			$breakpoint              = $args['breakpoint'] ?? null;
			$state                   = $args['state'] ?? null;

			if ( ! $default_color ) {
				$default_color = '#ffffff';
			}

			$background_args = [
				'sibling_background_attr' => $sibling_background_attr,
				'module_background_attr'  => $module_background_attr,
				'default_color'           => $default_color,
				'breakpoint'              => $breakpoint,
				'state'                   => $state,
			];

			$dividers_background_colors = DividersStyleUtils::get_dividers_background_colors( $background_args );

			$sibling_background_color = $dividers_background_colors['sibling_background_color'] ?? $default_color;
			$module_background_color  = $dividers_background_colors['module_background_color'] ?? $default_color;

			// Get whole module background attribute for current breakpoint and state.
			$module_whole_background_attr = ModuleUtils::use_attr_value(
				[
					'attr'       => $module_background_attr ?? [],
					'mode'       => 'getAndInheritAll',
					'breakpoint' => $breakpoint,
					'state'      => $state,
				]
			);

			$module_has_advanced_background = BackgroundStyleUtils::has_background_style( $module_whole_background_attr ?? [], [ 'gradient', 'image', 'video', 'pattern', 'mask' ] );

			// If sibling background color is same as module background color
			// and the divider does not have a specific color assigned, set the
			// divider color to black.
			if ( $module_background_color === $sibling_background_color && ! $module_has_advanced_background && empty( $values['color'] ) ) {
				$color = '#000000';
			} else {
				// Set value from divider color, if any, otherwise set from the sibling background color.
				$color = $values['color'] ?? $sibling_background_color;
			}

			// Get Dividers Transform state and CSS.
			$horizontal    = DividersStyleUtils::transform_state( $flip, 'horizontal' );
			$vertical      = DividersStyleUtils::transform_state( $flip, 'vertical' );
			$css_transform = DividersStyleUtils::transform_css( $horizontal, $vertical );

			// Dividers Placement Helper.
			$reverse_position = [
				'top'    => 'bottom',
				'bottom' => 'top',
			];

			// Reverse placement when vertical flip is enabled.
			if ( ( $vertical ? $reverse_position[ $placement ] : $placement ) ) {
				$maybe_reverse_placement = ( $vertical ? $reverse_position[ $placement ] : $placement );
			} else {
				$maybe_reverse_placement = 'top';
			}

			// Get Dividers Settings.
			$settings = DividerUtils::get_divider_json( $style );

			// By default, the divider is repeatable unless the repeatable attribute is set to false.
			$repeatable = ! isset( $settings['repeatable'] ) || (bool) $settings['repeatable'];

			// By default, dynamic_position is enabled unless the dynamic_position attribute is set to false.
			$dynamic_position = ! isset( $settings['svgDimension'][ $maybe_reverse_placement ]['dynamic_position'] ) || (bool) $settings['svgDimension'][ $maybe_reverse_placement ]['dynamic_position'];

			// Dividers attributes to pass to get_divider_svg().
			$divider_args = [
				'style'     => $style,
				'color'     => $color,
				'height'    => $height,
				'placement' => $maybe_reverse_placement,
				'escape'    => true,
			];

			// Get SVG for the divider style.
			$css_svg = DividerUtils::get_divider_svg( $divider_args );

			// Set `background-image`.
			$style_declarations->add( 'background-image', 'url("data:image/svg+xml;utf8,' . $css_svg . '")' );

			// Set `transform`.
			$style_declarations->add( 'transform', $css_transform );

			// Set `top` or `bottom` position.
			if ( $placement ) {
				$style_declarations->add( $placement, '0' );
			}

			// Set height.
			$style_declarations->add( 'height', $height );

			// Set divisor for repeatable divider background size.
			// Convert repeat value to float, it will also handle the case when repeat value is like '1x' or '0x'.
			$repeat_value = (float) $repeat;

			// If repeat value is 0, set it to 1.
			$repeat_value = $repeat_value ? $repeat_value : 1;

			// For non-repeatable dividers, set the background size and position.
			if ( ! $repeatable ) {
				// Set `background-size`.
				$style_declarations->add( 'background-size', 'cover' );

				// When dynamic_position is false for the non-repeatable divider, set only horizontal background position.
				if ( ! $dynamic_position ) {
					$style_declarations->add( 'background-position-x', 'center' );
				} else {
					$dynamic_position_value = null;

					// Set dynamic_position_value based on placement and flip vertical state.
					if ( 'top' === $placement ) {
						$dynamic_position_value = ( 'top' === $maybe_reverse_placement || true === $vertical ) ? 'top' : 'bottom';
					} elseif ( 'bottom' === $placement ) {
						$dynamic_position_value = 'top';
					}

					$style_declarations->add( 'background-position', "center {$dynamic_position_value}" );
				}
			} elseif ( strpos( $height, '%' ) !== false ) {
				// Set background size, and adjust height when percentages are used.
				$style_declarations->add( 'background-size', ( 100 / ( $repeat_value ) ) . '% 100%' );
			} else {
				// Set background size to repeat the image with percentages.
				$style_declarations->add( 'background-size', ( 100 / ( $repeat_value ) ) . '% ' . $height );
			}
		}

		// Set z-index.
		if ( true === $fullwidth || 'above' === $arrangement ) {
			$style_declarations->add( 'z-index', '10' );
		} else {
			$style_declarations->add( 'z-index', '1' );
		}

		return $style_declarations->value();
	}
}
