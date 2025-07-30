<?php
/**
 * Module: LinkUtils class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Link;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * LinkUtils class.
 *
 * @since ??
 */
class LinkUtils {

	/**
	 * Get link options classnames based on given link group attributes.
	 *
	 * @since ??
	 *
	 * @param array $attr The link group attributes.
	 *
	 * @return string The return value will be empty string when the link group
	 *                attributes is empty i.e `false === LinkUtils::is_enabled( $attr )`.
	 */
	public static function classnames( array $attr ): string {
		if ( ! self::is_enabled( $attr ) ) {
			return '';
		}

		return 'et_clickable';
	}

	/**
	 * Generate link script data.
	 *
	 * This include the link classnames, URL and target.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $selector      Module selector. Example: `.et_pb_cta_0`.
	 *     @type array  $attr          Module link group attributes.
	 * }
	 *
	 * @return array The generated link data.
	 *               If the link is not enabled, an empty array is returned.
	 *
	 * @example
	 * ```php
	 * $args = [
	 *     'selector' => '.et_pb_cta_0',
	 *     'attr'     => [
	 *         'desktop' => [
	 *             'value' => [
	 *                 'url'    => 'https://example.com',
	 *                 'target' => 'on',
	 *             ],
	 *         ],
	 *     ],
	 * ];
	 * $animationData = LinkUtils::generate_data( $args );
	 * ```
	 */
	public static function generate_data( array $args ): array {
		if ( ! self::is_enabled( $args['attr'] ?? [] ) ) {
			return [];
		}

		return [
			'class'  => ltrim( $args['selector'], '.' ),
			'url'    => $args['attr']['desktop']['value']['url'],
			'target' => 'on' === ( $args['attr']['desktop']['value']['target'] ?? 'off' ) ? '_blank' : '_self',
		];
	}

	/**
	 * Checks if the link is enabled based on given link group attributes.
	 *
	 * @since ??
	 *
	 * @param array $attr The link group attributes.
	 *
	 * @return bool
	 */
	public static function is_enabled( array $attr ): bool {
		// Only account valid URL.
		return ! ! esc_url( $attr['desktop']['value']['url'] ?? '' );
	}

}
