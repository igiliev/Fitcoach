<?php
/**
 * ButtonIcon class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\ButtonIcon;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

/**
 * ButtonIcon is a helper class for working with ButtonIcon style declaration.
 *
 * @since ??
 */
class ButtonIcon {

	/**
	 * Get Button Icon's CSS declaration based on given attrValue.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-icon-style-declaration buttonIconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *                                  Note if `icon` key is not set, an empty string is returned.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function style_declaration( array $args ) {
		if ( ! isset( $args['attrValue']['icon'] ) ) {
			return '';
		}

		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value         = $args['attrValue'];
		$default_attr_value = $args['defaultAttrValue'] ?? [];
		$return_type        = $args['returnType'];
		$enable             = $attr_value['icon']['enable'] ?? $default_attr_value['icon']['enable'] ?? null;
		$settings           = $attr_value['icon']['settings'] ?? $default_attr_value['icon']['settings'] ?? [];
		$color              = $attr_value['icon']['color'] ?? $default_attr_value['icon']['color'] ?? null;
		$on_hover           = $attr_value['icon']['onHover'] ?? $default_attr_value['icon']['onHover'] ?? null;
		$placement          = $attr_value['icon']['placement'] ?? $default_attr_value['icon']['placement'] ?? null;
		$always_important   = [
			'font-family' => true,
			'font-weight' => true,
			'font-size'   => (bool) ( $settings['unicode'] ?? false ),
			'line-height' => true,
		];
		$important          = $args['important'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => is_bool( $important ) ? array_merge(
					$always_important,
					[
						'content'     => $important,
						'display'     => $important,
						'margin-left' => $important,
						'color'       => $important,
						'opacity'     => $important,
						'left'        => $important,
						'right'       => $important,
					]
				) : array_merge( $always_important, $important ),
				'returnType' => $return_type,
			]
		);

		if ( 'on' === $enable ) {
			$icon_margin = '.3em';

			if ( 'left' === $placement ) {
				$icon_margin = '-1.3em';
			}

			if ( 'left' !== $placement && 'off' !== $on_hover ) {
				$icon_margin = '-1em';
			}

			$icon_value = Utils::escape_font_icon( Utils::process_font_icon( $settings ) );
			$weight     = isset( $settings['weight'] ) ? $settings['weight'] : '400';

			if ( $settings ) {
				$font_family = Utils::is_fa_icon( $settings ) ? 'FontAwesome' : 'ETmodules';

				$style_declarations->add( 'content', "'" . $icon_value . "'" );
				$style_declarations->add( 'font-family', "\"{$font_family}\"" );
				$style_declarations->add( 'font-weight', $weight );
				$style_declarations->add( 'font-size', 'inherit' );
				$style_declarations->add( 'line-height', '1.7em' );
				$style_declarations->add( 'display', 'inline-block' );
				$style_declarations->add( 'margin-left', $icon_margin );
			}

			if ( empty( $icon_value ) ) {
				$style_declarations->add( 'font-size', '1.6em' );
			}
		} else {
			$style_declarations->add( 'font-size', '1.6em' );
		}

		if ( $color ) {
			$style_declarations->add( 'color', $color );
		}

		if ( 'off' === $on_hover ) {
			$icon_position = 'left' === $placement ? 'right' : 'left';
			$style_declarations->add( $icon_position, 'auto' );
			$style_declarations->add( 'display', 'inline-block' );
			$style_declarations->add( 'opacity', '1' );
		} elseif ( 'on' === $on_hover ) {
			$style_declarations->add( 'opacity', '0' );
		}

		return $style_declarations->value();

	}

	/**
	 * Get Button Icon's Hover CSS declaration based on given placement.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-icon-hover-style-declaration buttonIconHoverStyleDeclaration} in:
	 * `@divi/style-library` package. buttonIconHoverStyleDeclaration located in:
	 * visual-builder/packages/style-library/src/declarations/button-icon/index.ts.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function hover_style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'returnType' => 'string',
			]
		);

		$attr_value         = $args['attrValue'];
		$default_attr_value = $args['defaultAttrValue'] ?? [];
		$return_type        = $args['returnType'];
		$enable             = $attr_value['icon']['enable'] ?? $default_attr_value['icon']['enable'] ?? null;
		$placement          = $attr_value['icon']['placement'] ?? $default_attr_value['icon']['placement'] ?? 'right';

		$style_declarations = new StyleDeclarations(
			[
				'important'  => [
					'margin-left' => true,
				],
				'returnType' => $return_type,
			]
		);

		if ( 'on' === $enable ) {
			if ( 'left' === $placement ) {
				$style_declarations->add( 'margin-right', '0.3em' );
				$style_declarations->add( 'right', 'auto' );
				$style_declarations->add( 'opacity', '1' );
			} elseif ( 'right' === $placement ) {
				$style_declarations->add( 'margin-left', '0' );
				$style_declarations->add( 'left', 'auto' );
				$style_declarations->add( 'opacity', '1' );
			}
		}

		return $style_declarations->value();

	}

	/**
	 * Hide Button Right Icon only if the placement is set to the left.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/button-right-icon-style-declaration buttonRightIconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return array|string
	 */
	public static function right_style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value         = $args['attrValue'];
		$default_attr_value = $args['defaultAttrValue'] ?? [];
		$return_type        = $args['returnType'];
		$enable             = $attr_value['icon']['enable'] ?? $default_attr_value['icon']['enable'] ?? null;
		$placement          = $attr_value['icon']['placement'] ?? $default_attr_value['icon']['placement'] ?? null;
		$important          = $args['important'];

		$style_declarations = new StyleDeclarations(
			[
				'important'  => $important,
				'returnType' => $return_type,
			]
		);

		if ( 'on' === $enable && 'left' === $placement ) {
			$style_declarations->add( 'display', 'none' );
		}

		return $style_declarations->value();

	}

	/**
	 * Disable the icon if `Show Button Icon` is set to the `false`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/style-library/disable-button-icon-style-declaration disableButtonIconStyleDeclaration} in:
	 * `@divi/style-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array      $attrValue  The value (breakpoint > state > value) of module attribute.
	 *     @type bool|array $important  Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType This is the type of value that the function will return.'
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 *
	 * @return string|array
	 */
	public static function disable_style_declaration( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$attr_value         = $args['attrValue'];
		$default_attr_value = $args['defaultAttrValue'] ?? [];
		$return_type        = $args['returnType'];
		$enable             = $attr_value['icon']['enable'] ?? $default_attr_value['icon']['enable'] ?? null;

		$style_declarations = new StyleDeclarations(
			[
				'important'  => true,
				'returnType' => $return_type,
			]
		);

		if ( 'off' === $enable ) {
			$style_declarations->add( 'display', 'none' );
		}

		return $style_declarations->value();

	}
}
