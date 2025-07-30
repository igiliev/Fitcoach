<?php
/**
 * Page Class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\FrontEnd;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Settings\Overflow;
use ET\Builder\Framework\Settings\Settings;
use ET_Post_Stack;

/**
 * Page Class.
 *
 * This class is responsible for handling functionality related to Page rendering (specifically that uses Divi Builder)
 *
 * @since ??
 */
class Page {
	/**
	 * Return page custom style.
	 *
	 * @internal Equivalent of Divi 4's `et_pb_get_page_custom_css()`.
	 *
	 * @since ??
	 *
	 * @param int $post_id post id.
	 */
	public static function custom_css( $post_id = 0 ) {
		$post_id          = $post_id ? $post_id : get_the_ID();
		$post_type        = get_post_type( $post_id );
		$page_id          = apply_filters( 'et_pb_page_id_custom_css', $post_id );
		$exclude_defaults = true;
		$page_settings    = Settings::get_values( 'page', $page_id, $exclude_defaults );
		$selector_prefix  = '.et-l--post';

		switch ( $post_type ) {
			case ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE:
				$selector_prefix = '.et-l--header';
				break;

			case ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE:
				$selector_prefix = '.et-l--body';
				break;

			case ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE:
				$selector_prefix = '.et-l--footer';
				break;
		}

		$wrap_post_id = $page_id;

		if ( et_theme_builder_is_layout_post_type( $post_type ) ) {
			$main_post_id = ET_Post_Stack::get_main_post_id();

			if ( $main_post_id ) {
				$wrap_post_id = $main_post_id;
			}
		}

		$wrap_selector = et_pb_is_pagebuilder_used( $wrap_post_id ) && ( et_is_builder_plugin_active() || et_builder_post_is_of_custom_post_type( $wrap_post_id ) );

		if ( $wrap_selector ) {
			$selector_prefix = ' ' . ET_BUILDER_CSS_PREFIX . $selector_prefix;
		}

		$output = get_post_meta( $page_id, '_et_pb_custom_css', true );

		if ( isset( $page_settings['et_pb_light_text_color'] ) ) {
			$output .= sprintf(
				'%2$s .et_pb_bg_layout_dark { color: %1$s !important; }',
				esc_html( $page_settings['et_pb_light_text_color'] ),
				esc_html( $selector_prefix )
			);
		}

		if ( isset( $page_settings['et_pb_dark_text_color'] ) ) {
			$output .= sprintf(
				'%2$s .et_pb_bg_layout_light { color: %1$s !important; }',
				esc_html( $page_settings['et_pb_dark_text_color'] ),
				esc_html( $selector_prefix )
			);
		}

		if ( isset( $page_settings['et_pb_content_area_background_color'] ) ) {
			$content_area_bg_selector = et_is_builder_plugin_active() ? $selector_prefix : ' .page.et_pb_pagebuilder_layout #main-content';
			$output                  .= sprintf(
				'%1$s { background-color: %2$s; }',
				esc_html( $content_area_bg_selector ),
				esc_html( $page_settings['et_pb_content_area_background_color'] )
			);
		}

		if ( isset( $page_settings['et_pb_section_background_color'] ) ) {
			$output .= sprintf(
				'%2$s > .et_builder_inner_content > .et_pb_section { background-color: %1$s; }',
				esc_html( $page_settings['et_pb_section_background_color'] ),
				esc_html( $selector_prefix )
			);
		}

		$overflow_x = Overflow::get_value_x( $page_settings, '', 'et_pb_' );
		$overflow_y = Overflow::get_value_y( $page_settings, '', 'et_pb_' );

		if ( ! empty( $overflow_x ) ) {
			$output .= sprintf(
				'%2$s .et_builder_inner_content { overflow-x: %1$s; }',
				esc_html( $overflow_x ),
				esc_html( $selector_prefix )
			);
		}

		if ( ! empty( $overflow_y ) ) {
			$output .= sprintf(
				'%2$s .et_builder_inner_content { overflow-y: %1$s; }',
				esc_html( $overflow_y ),
				esc_html( $selector_prefix )
			);
		}

		if ( isset( $page_settings['et_pb_page_z_index'] ) && '' !== $page_settings['et_pb_page_z_index'] ) {
			$output .= sprintf(
				'%2$s .et_builder_inner_content { z-index: %1$s; }',
				esc_html( $page_settings['et_pb_page_z_index'] ),
				esc_html( '.et-db #et-boc .et-l' . $selector_prefix )
			);
		}

		return apply_filters( 'et_pb_page_custom_css', $output );
	}
}
