<?php
/**
 * Frontend Font Loader
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\FrontEnd\Module;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\Conditions;

/**
 * Font Loader class.
 *
 * Responsible for loading fonts in the frontend.
 *
 * @since ??
 */
class Fonts {

	/**
	 * Keep track of Fonts added.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public static $_fonts_added = [];

	/**
	 * Add a font family to the store.
	 *
	 * Enqueue a given font family for use in the Builder.
	 *
	 * @since ??
	 *
	 * @param string $font_family The name of the font family.
	 *
	 * @return void
	 *
	 * @example: Enqueue the 'Open Sans' font family.
	 * ```php
	 * add_font_family( 'Open Sans' );
	 * ```
	 */
	public static function add( string $font_family ): void {
		if ( ! empty( $font_family ) && ! in_array( $font_family, self::$_fonts_added, true ) ) {
			self::$_fonts_added[] = $font_family;

			// TODO feat(D5, FE Rendering): Need to rewrite et_builder_enqueue_font in D5.
			et_builder_enqueue_font( $font_family );
		}
	}

	/**
	 * Enqueue user custom fonts
	 *
	 * This function is used to enqueue custom fonts specified by the user. It takes in an array of
	 * font URLs and registers them using the WordPress `wp_enqueue_style` function. This allows the
	 * fonts to be loaded on the front-end of the website.
	 *
	 * @since ??
	 *
	 * @see wp_enqueue_style() To register and enqueue the custom font stylesheets.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		$heading_font        = et_get_option( 'heading_font', 'Open Sans' );
		$body_font           = et_get_option( 'body_font', 'Open Sans' );
		$body_font_weight    = et_get_option( 'body_font_weight', '500' );
		$heading_font_weight = et_get_option( 'heading_font_weight', '500' );
		$global_font_vars = '';
		$customizer_fonts = array(
			'--et_global_heading_font'        => $heading_font ? $heading_font : 'Open Sans',
			'--et_global_body_font'           => $body_font ? $body_font : 'Open Sans',
			'--et_global_heading_font_weight' => $heading_font_weight ? $heading_font_weight : '500',
			'--et_global_body_font_weight'    => $body_font_weight ? $body_font_weight : '500',
		);

		foreach ( $customizer_fonts as $var_name => $value ) {
			if ( ! empty( $value ) ) {
				$global_font_vars .= esc_html( $var_name ) . ': ' . esc_html( $value ) . ';';
			}
		}

		// Only load on FE. VB loads this via the modules root component.
		if ( ! empty( $global_font_vars ) && ! Conditions::is_vb_enabled() ) {
			$global_fonts_style = ':root{' . $global_font_vars . '}';

			echo '<style class="et-vb-global-data et-vb-global-fonts">';
			echo et_core_esc_previously( ( $global_fonts_style ) );
			echo '</style>';
		}
	}
}
