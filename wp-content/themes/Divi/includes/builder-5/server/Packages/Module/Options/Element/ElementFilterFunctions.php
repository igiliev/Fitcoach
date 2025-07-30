<?php
/**
 * Module: ElementFilterFunctions class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Element;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ElementFilterFunctions class.
 *
 * This class provides common filter functions for elements, such as button type attributes
 * and a filter function map.
 *
 * @since ??
 */
class ElementFilterFunctions {
	/**
	 * Filters the ElementStyle attributes based on the button's 'enable' style value.
	 *
	 * This function is used in button elements to determine if custom styles should be rendered or not.
	 * If the 'Use custom styles for button' setting is turned off, no styles are applied to the button.
	 *
	 * @param array $attrs The attributes of the element.
	 *
	 * @return array The modified attributes of the element, with or without the 'button' key depending on the 'enable' style value.
	 *
	 * @example
	 * ```php
	 *     $element_attrs = [
	 *         'button' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'enable' => 'on' // Enable custom styles for button
	 *                 ]
	 *             ]
	 *         ]
	 *     ];
	 *
	 *     $modified_attrs = $this->button_type_attrs( $element_attrs );
	 *
	 *     // Use the modified attributes
	 * ```
	 */
	public static function button_type_attrs( array $attrs ): array {
		$custom_style_enabled = $attrs['button']['desktop']['value']['enable'] ?? 'off';
		$has_custom_style     = 'on' === $custom_style_enabled;

		return $has_custom_style
			? $attrs
			: [
				'button' => $attrs['button'] ?? [],
			];
	}

	/**
	 * Map of filter functions for element attribute.
	 *
	 * @var array $filter_function_map {
	 *     @type string $button Button type attributes filter function.
	 * }
	 *
	 * @since ??
	 */
	public static $filter_function_map = [
		'button' => '\ET\Builder\Packages\Module\Options\Element\ElementFilterFunctions::button_type_attrs',
	];
}
