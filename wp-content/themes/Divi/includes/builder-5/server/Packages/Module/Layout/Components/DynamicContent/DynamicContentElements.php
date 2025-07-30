<?php
/**
 * Module: DynamicContentElements class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

use WP_Term;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentElements class.
 *
 * When dynamic content is being processed, it's not just about pure string value. In
 * some cases, we need to wrap the value or even render certain elements. This class
 * handles rendering of certain dynamic content elements.
 * - Wrapper for the value.
 * - Wrapper for the Woo Module element value that contains non plain text value.
 * - Terms list.
 *
 * @since ??
 */
class DynamicContentElements {
	/**
	 * Get terms list with or without link and separator based on the given terms.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array   $terms     List of terms. Default `[]`.
	 *     @type boolean $is_link   Whether to return link or label. Default `true`.
	 *     @type string  $separator Terms separator. Default `' | '`.
	 * }
	 *
	 * @return string The terms list.
	 *
	 * @example:
	 * ```php
	 *  $args = [
	 *      'terms'     => [$term1, $term2], // List of WP_Term objects
	 *      'is_link'   => true,             // Whether to return links
	 *      'separator' => ' / '             // Separator to use between terms
	 *  ];
	 *  $terms_list = GetTermsListTrait::get_terms_list( $args );
	 * ```
	 */
	public static function get_terms_list( array $args ): string {
		$terms     = $args['terms'] ?? [];
		$is_link   = $args['is_link'] ?? true;
		$separator = $args['separator'] ?? ' | ';

		$output = [];

		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$label = esc_html( $term->name );

			if ( $is_link ) {
				$label = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( get_term_link( $term ) ),
					et_core_esc_previously( $label )
				);
			}

			$output[] = $label;
		}

		return implode( esc_html( $separator ), $output );
	}

	/**
	 * Get the wrapper element that contains the option value based on the given arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string  $name     Optional. The option name. Default empty string.
	 *     @type int     $post_id  Optional. The ID of the post. Default `0`.
	 *     @type string  $value    Optional. The option value. Default empty string.
	 *     @type array   $settings Optional. The option settings. Default `[]`.
	 * }
	 *
	 * @return string The wrapper element containing the option value.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'name'     => 'example_option',
	 *     'post_id'  => 1,
	 *     'value'    => 'Example Value',
	 *     'settings' => [
	 *         'before' => '<div>',
	 *         'after'  => '</div>',
	 *     ],
	 * ];
	 * $result = GetWrapperElementTrait::get_wrapper_element( $args );
	 *
	 * echo $result;
	 * ```
	 * @output:
	 * ```html
	 * <div>Example Value</div>
	 * ```
	 */
	public static function get_wrapper_element( array $args ): string {
		$name     = $args['name'] ?? '';
		$post_id  = $args['post_id'] ?? 0;
		$value    = $args['value'] ?? '';
		$settings = $args['settings'] ?? [];

		// TODO feat(D5, Theme Builder): Replace it once the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$tb_post_id = \ET_Theme_Builder_Layout::get_theme_builder_layout_id();

		// Get default setting value based on current post ID, option name, and setting name.
		$default_setting = function ( $setting ) use ( $post_id, $name ) {
			return DynamicContentUtils::get_default_setting_value(
				[
					'post_id' => $post_id,
					'name'    => $name,
					'setting' => $setting,
				]
			);
		};

		$before      = $settings['before'] ?? $default_setting( 'before' );
		$after       = $settings['after'] ?? $default_setting( 'after' );
		$cap_post_id = $tb_post_id ? $tb_post_id : $post_id;
		$user_id     = get_post_field( 'post_author', $cap_post_id );

		if ( ! user_can( $user_id, 'unfiltered_html' ) ) {
			$allowlist = array_merge(
				wp_kses_allowed_html( '' ),
				[
					'h1'   => [],
					'h2'   => [],
					'h3'   => [],
					'h4'   => [],
					'h5'   => [],
					'h6'   => [],
					'ol'   => [],
					'ul'   => [],
					'li'   => [],
					'span' => [],
					'p'    => [],
				]
			);

			$before = wp_kses( $before, $allowlist );
			$after  = wp_kses( $after, $allowlist );
		}

		return $before . $value . $after;
	}

	/**
	 * Get wrapper Woo Module element that contains non plain text Woo Module element data.
	 *
	 * This function wraps non plain text woo data in a custom selector for styling inheritance.
	 * It checks if the content has an HTML tag and adds the necessary wrapper element if it does.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $name  The option name.
	 *     @type string $value The option value.
	 * }
	 *
	 * @return string The wrapper element that contains the option value.
	 *
	 * @example:
	 * ```php
	 *     $value = get_wrapper_woo_module_element( [
	 *         'name'  => 'example_name',
	 *         'value' => '<p>This is a paragraph</p>'
	 *     ] );
	 * ```

	 * @output:
	 * ```html
	 *  <div class="woocommerce et-dynamic-content-woo et-dynamic-content-woo--example_name"><p>This is a paragraph</p></div>
	 * ```
	 */
	public static function get_wrapper_woo_module_element( array $args ): string {
		$name  = $args['name'] ?? '';
		$value = $args['value'] ?? '';

		if ( $value && preg_match( '/<\s?[^\>]*\/?\s?>/i', $value ) ) {
			$value = sprintf( '<div class="woocommerce et-dynamic-content-woo et-dynamic-content-woo--%2$s">%1$s</div>', $value, $name );
		}

		return $value;
	}
}
