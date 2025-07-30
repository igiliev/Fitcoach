<?php
/**
 * Divi Builder's Class for handling customizer related data.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Customizer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class for handling Customizer settings.
 *
 * Customizer is theme-based experience while Divi Builder is used on at least three products: Divi (Theme),
 * Extra (Theme), and Divi Builder Plugin. This class abstracting expected interaction between Divi builder and
 * theme customizer. Everything that Divi builder needs regarding customizer should be handled and retrieved here.
 * On the other side, theme also passes customizer settings values based on filters defined on this class.
 * Note: not every customizer settings are passed into Divi Builder.
 *
 * @since ??
 */
class Customizer {
	/**
	 * Get customizer's settings values that is needed by Divi Builder.
	 *
	 * @since ??
	 *
	 * @return array.
	 */
	public static function get_settings_values() {
		$options = [
			'buttonOptions' => [],
		];

		return apply_filters( 'divi_framework_customizer_settings_values', $options );
	}

	/**
	 * Get customizer setting based on given element type.
	 *
	 * @since ??
	 *
	 * @param array $params An array of arguments.
	 */
	public static function get_customizer_setting_for_element_style( $params ) {
		$element_type = $params['elementType'] ?? 'module';

		// At the moment this is specifically made for handling customizer button options that
		// cascades into visual builder options. Thus no setting for other element type.
		// TODO feat(D5, Customizer) Update this if there are more customizer setting for different element type.
		if ( 'button' !== $element_type ) {
			return [];
		}

		$customizer_setting = self::get_settings_values();

		$element_setting = $customizer_setting['buttonOptions']['decoration'] ?? [];

		return $element_setting;
	}
}
