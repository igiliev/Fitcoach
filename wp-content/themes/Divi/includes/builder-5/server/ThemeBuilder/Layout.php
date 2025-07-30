<?php
/**
 * ThemeBuilder: Theme Builder Class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Assets\StaticCSS;
use ET\Builder\FrontEnd\Page;
use ET_Post_Stack;
use ET_Theme_Builder_Layout;

/**
 * Theme Builder Layout class.
 *
 * @since ??
 */
class Layout {
	/**
	 * Get post type to layout map.
	 *
	 * @since ??
	 *
	 * @return array Array of post type to layout map.
	 */
	public static function get_post_type_to_layout_map(): array {
		return [
			'et_header_layout' => 'header',
			'et_body_layout'   => 'body',
			'et_footer_layout' => 'footer',
		];
	}

	/**
	 * Get current layout based on given post type.
	 *
	 * @since ??
	 *
	 * @param string $post_type post type.
	 *
	 * @return string get layout based on given post type.
	 */
	public static function get_layout_based_on_post_type( $post_type = '' ): string {
		return self::get_post_type_to_layout_map()[ $post_type ] ?? 'postContent';
	}

	/**
	 * Render a template builder layout.
	 * This is Divi 5 adjusted version of `et_theme_builder_frontend_render_layout()`.
	 *
	 * Wrapper cases:
	 * 1. Header/Footer are replaced.
	 *   => Common is open and closed. Header/Footer do not get opened/closed because
	 *      Common is opened before them.
	 *
	 * 2. Body is replaced.
	 *   => Common is NOT opened/closed. Body is open/closed.
	 *
	 * 3. Header/Body/Footer are replaced.
	 *   => Common is open and closed. Header/Body/Footer do not get opened/closed because
	 *      Common is opened before them.
	 *
	 * @since ??
	 *
	 * @param string  $layout_type Layout Type.
	 * @param integer $layout_id   Layout ID.
	 *
	 * @return void
	 */
	public static function render( $layout_type, $layout_id ) {
		if ( $layout_id <= 0 ) {
			return;
		}

		$layout = get_post( $layout_id );

		if ( null === $layout || $layout->post_type !== $layout_type ) {
			return;
		}

		et_theme_builder_frontend_render_common_wrappers( $layout_type, true );

		/**
		 * Fires after Theme Builder layout opening wrappers have been output but before any
		 * other processing has been done (e.g. replacing the current post).
		 *
		 * @since 4.0.10
		 *
		 * @param string $layout_type
		 * @param integer $layout_id
		 */
		do_action( 'et_theme_builder_after_layout_opening_wrappers', $layout_type, $layout_id );

		ET_Theme_Builder_Layout::begin_theme_builder_layout( $layout_id );

		ET_Post_Stack::replace( $layout );

		$is_visual_builder     = isset( $_GET['et_fb'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Value is not used
		$theme_builder_layouts = array( 'et_header_layout', 'et_footer_layout' );

		// Do not pass header and footer content here if visual builder is loaded,
		// they will be loaded inside the builder itself.
		if ( et_pb_is_allowed( 'theme_builder' ) && $is_visual_builder && in_array( $layout_type, $theme_builder_layouts, true ) ) {
			$post_content = '';
		} else {
			$post_content = get_the_content();
		}

		echo et_core_intentionally_unescaped( et_builder_render_layout( $post_content ), 'html' );

		// Get dynamic content.
		$has_dynamic_content = et_builder_frontend_get_dynamic_contents( get_the_content() );

		// Handle style output.
		if ( is_singular() && ! et_core_is_fb_enabled() ) {
			$result = StaticCSS::setup_styles_manager( ET_Post_Stack::get_main_post_id() );
		} elseif ( is_tax() && ! empty( $has_dynamic_content ) ) {
			// Set post id to 0 if its a taxonomy page.
			// This is because of the dynamic content not working properly,
			// With the theme builder cache.
			$result = StaticCSS::setup_styles_manager( 0 );
		} else {
			$result = StaticCSS::setup_styles_manager( $layout->ID );
		}

		$styles_manager = $result['manager'];
		if ( isset( $result['deferred'] ) ) {
			$deferred_styles_manager = $result['deferred'];
		}

		// Pass styles to page resource which will handle their output.
		if ( StaticCSS::$forced_inline_styles || ! $styles_manager->has_file() || $styles_manager->forced_inline ) {
			$custom = Page::custom_css( $layout->ID );

			// Pass styles to the page resource.
			StaticCSS::style_output(
				[
					'styles_manager'          => $styles_manager,
					'deferred_styles_manager' => $deferred_styles_manager ?? null,
					'custom'                  => $custom,
					'element_id'              => $layout_id,
				]
			);
		}

		ET_Post_Stack::restore();

		ET_Theme_Builder_Layout::end_theme_builder_layout();

		/**
		 * Fires before Theme Builder layout closing wrappers have been output and after any
		 * other processing has been done (e.g. replacing the current post).
		 *
		 * @since 4.0.10
		 *
		 * @param string $layout_type
		 * @param integer $layout_id
		 */
		do_action( 'et_theme_builder_before_layout_closing_wrappers', $layout_type, $layout_id );

		et_theme_builder_frontend_render_common_wrappers( $layout_type, false );
	}
}
