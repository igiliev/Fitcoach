<?php
/**
 * Module: IdClasses class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\IdClasses;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * IdClassesClassnames class.
 *
 * This class provides method(s) to retrieve HTML attributes for an element.
 *
 * @since ??
 */
class IdClassesClassnames {

	/**
	 * Get the HTML attributes for IdClasses Options.
	 *
	 * This function retrieves the HTML attributes for the IdClasses group.
	 *
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetHtmlAttributes getHtmlAttributes} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $attr The IdClasses group attributes.
	 *
	 * @return array The HTML attributes for the IdClasses group.
	 *
	 * @example:
	 * ```php
	 *      $id_class_values = self::get_html_attributes( $attrs['module']['advanced']['htmlAttributes'] ?? [] );
	 *      $html_id         = $id_class_values['id'] ?? '';
	 *      $html_classnames = $id_class_values['classNames'] ?? '';
	 * ```
	 */
	public static function get_html_attributes( array $attr ): array {
		$id         = isset( $attr['desktop']['value']['id'] ) ? $attr['desktop']['value']['id'] : null;
		$classnames = isset( $attr['desktop']['value']['class'] ) ? $attr['desktop']['value']['class'] : null;

		return [
			'id'         => $id,
			'classNames' => $classnames,
		];
	}

}
