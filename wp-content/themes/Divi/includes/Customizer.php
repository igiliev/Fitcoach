<?php
/**
 * Class for handling Customizer configuration in Divi Theme.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Divi;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class for handling Customizer configuration in Divi Theme.
 *
 * In Divi 4, there is no centralized class for handling customizer settings. This class is introduced for Divi 5
 * to abstracting customizer settings relation towards builder (eg. some of customizer settings are being passed into
 * visual builder as default values). Thus, at the moment (to be revised as this goes), this class is not being used
 * for handling customizer in Divi 4 (it is possible to do it later). Instead, this is used to handle Customizer
 * settings' interaction with Divi 5's builder.
 *
 * @since ??
 */
class Customizer {
	/**
	 * Unique instance of class.
	 *
	 * @var Customizer
	 */
	public static $instance;

	/**
	 * Customizer settings, derived from Divi 4 so Divi 4 code doesn't need to be enqueued.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public static $settings = [
		'button' => [

			// background.
			'background_color'          => [
				'property_name' => 'all_buttons_bg_color',
				'default'       => 'rgba(0,0,0,0)',
			],
			'background_color_hover'    => [
				'property_name' => 'all_buttons_bg_color_hover',
				'default'       => 'rgba(255,255,255,0.2)',
			],

			// border.
			'border_width'              => [
				'property_name' => 'all_buttons_border_width',
				'default'       => '2',
			],
			'border_color'              => [
				'property_name' => 'all_buttons_border_color',
				'default'       => '#ffffff',
			],
			'border_radius'             => [
				'property_name' => 'all_buttons_border_radius',
				'default'       => '3',
			],
			'border_color_hover'        => [
				'property_name' => 'all_buttons_border_color_hover',
				'default'       => 'rgba(0,0,0,0)',
			],
			'border_radius_hover'       => [
				'property_name' => 'all_buttons_border_radius_hover',
				'default'       => '3',
			],

			// button icon.
			'icon_enable'               => [
				'property_name' => 'all_buttons_icon',
				'default'       => 'yes',
			],
			'icon_settings_unicode'     => [
				'property_name' => 'all_buttons_selected_icon',
				'default'       => '5',
			],
			'icon_color'                => [
				'property_name' => 'all_buttons_icon_color',
				'default'       => '#ffffff',
			],
			'icon_placement'            => [
				'property_name' => 'all_buttons_icon_placement',
				'default'       => 'right',
			],
			'icon_on_hover'             => [
				'property_name' => 'all_buttons_icon_hover',
				'default'       => 'yes',
			],

			// font.
			'font_size'                 => [
				'property_name' => 'all_buttons_font_size',
				'default'       => '20',
			],
			'font_color'                => [
				'property_name' => 'all_buttons_text_color',
				'default'       => '',
			],
			'font_letter_spacing'       => [
				'property_name' => 'all_buttons_spacing',
				'default'       => '0',
			],
			'font_style'                => [
				'property_name' => 'all_buttons_font_style',
				'default'       => '',
			],
			'font_family'               => [
				'property_name' => 'all_buttons_font',
				'default'       => 'none',
			],
			'font_color_hover'          => [
				'property_name' => 'all_buttons_text_color_hover',
				'default'       => '',
			],
			'font_letter_spacing_hover' => [
				'property_name' => 'all_buttons_spacing_hover',
				'default'       => '0',
			],
		],
	];

	/**
	 * Customizer constructor.
	 *
	 * @since ??
	 */
	public function __construct() {
		add_filter( 'divi_framework_customizer_settings_values', [ self::class, 'settings_values_callback' ] );
	}

	/**
	 * Gets the instance of the class.
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if a customizer setting is modified or not.
	 *
	 * @since ??
	 *
	 * @param string $setting_group Setting group.
	 * @param string $name          Setting name.
	 *
	 * @return bool
	 */
	public static function is_setting_modified( $setting_group, $name ) {
		$property_name = self::$settings[ $setting_group ][ $name ]['property_name'] ?? '';

		// Option name not found.
		if ( '' === $property_name ) {
			return false;
		}

		$value = et_get_option( $property_name );

		// If the returned value is `false` (boolean), it means the option is not set on `et_divi` table, thus
		// no value found.
		if ( false === $value ) {
			return false;
		}

		$default = self::$settings[ $setting_group ][ $name ]['default'] ?? '';

		// At this stage, convert value into string to ensure accurate comparison. All defaults are string. The value,
		// if it is number, can be returned as string or integer which could cause incorrect comparison.
		$value = strval( $value );

		return $value !== $default;
	}

	/**
	 * Get customizer setting value
	 *
	 * @since ??
	 *
	 * @param string $setting_group Setting group.
	 * @param string $name          Setting name.
	 *
	 * @return mixed
	 */
	public static function get_setting_value( $setting_group, $name ) {
		$property_name = self::$settings[ $setting_group ][ $name ]['property_name'] ?? '';

		// Option name not found.
		if ( '' === $property_name ) {
			return false;
		}

		$value = et_get_option( $property_name );

		return $value;
	}

	/**
	 * Get customizer's button options.
	 *
	 * The button options will be used as default on module's button options.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_button_options_values() {
		$options = [
			'decoration' => [
				'background' => [
					'desktop' => [
						'value' => [],
						'hover' => [],
					],
				],
				'border'     => [
					'desktop' => [
						'value' => [
							'styles' => [
								'all' => [],
							],
						],
						'hover' => [
							'styles' => [
								'all' => [],
							],
						],
					],
				],
				'button'     => [
					'desktop' => [
						'value' => [
							'settings' => [],
						],
					],
				],
				'font'       => [
					'font' => [
						'desktop' => [
							'value' => [],
							'hover' => [],
						],
					],
				],
			],
		];

		// Button background.
		if ( self::is_setting_modified( 'button', 'background_color' ) ) {
			$options['decoration']['background']['desktop']['value']['color'] = self::get_setting_value( 'button', 'background_color' );
		}

		if ( self::is_setting_modified( 'button', 'background_color_hover' ) ) {
			$options['decoration']['background']['desktop']['hover']['color'] = self::get_setting_value( 'button', 'background_color_hover' );
		}

		// Button border.
		if ( self::is_setting_modified( 'button', 'border_width' ) ) {
			$options['decoration']['border']['desktop']['value']['styles']['all']['width'] = self::get_setting_value( 'button', 'border_width' );
		}

		if ( self::is_setting_modified( 'button', 'border_color' ) ) {
			$options['decoration']['border']['desktop']['value']['styles']['all']['color'] = self::get_setting_value( 'button', 'border_color' );
		}

		if ( self::is_setting_modified( 'button', 'border_radius' ) ) {
			$border_radius = self::get_setting_value( 'button', 'border_radius' );

			$options['decoration']['border']['desktop']['value']['radius'] = [
				'topLeft'     => $border_radius,
				'topRight'    => $border_radius,
				'bottomRight' => $border_radius,
				'bottomLeft'  => $border_radius,
			];
		}

		if ( self::is_setting_modified( 'button', 'border_color_hover' ) ) {
			$options['decoration']['border']['desktop']['hover']['styles']['all']['color'] = self::get_setting_value( 'button', 'border_color_hover' );
		}

		if ( self::is_setting_modified( 'button', 'border_radius_hover' ) ) {
			$border_radius_hover = self::get_setting_value( 'button', 'border_radius_hover' );

			$options['decoration']['border']['desktop']['hover']['radius'] = [
				'topLeft'     => $border_radius_hover,
				'topRight'    => $border_radius_hover,
				'bottomRight' => $border_radius_hover,
				'bottomLeft'  => $border_radius_hover,
			];
		}

		// Button button.
		if ( self::is_setting_modified( 'button', 'icon_enable' ) ) {
			// Button Options' icon enable in customizer is set as `yes` / `no` value while the equivalent option
			// in Visual Builder's button option has value of `on` / `off`. Thus, the value needs to be converted.
			$options['decoration']['button']['desktop']['value']['icon']['enable'] = 'yes' === self::get_setting_value( 'button', 'icon_enable' ) ? 'on' : 'off';
		}

		if ( self::is_setting_modified( 'button', 'icon_settings_unicode' ) ) {
			$options['decoration']['button']['desktop']['value']['icon']['settings']['type']    = 'divi';
			$options['decoration']['button']['desktop']['value']['icon']['settings']['weight']  = '400';
			$options['decoration']['button']['desktop']['value']['icon']['settings']['unicode'] = '&#x' . dechex( mb_ord( self::get_setting_value( 'button', 'icon_settings_unicode' ), 'UTF-8' ) ) . ';';
		}

		if ( self::is_setting_modified( 'button', 'icon_color' ) ) {
			$options['decoration']['button']['desktop']['value']['icon']['color'] = self::get_setting_value( 'button', 'icon_color' );
		}

		if ( self::is_setting_modified( 'button', 'icon_placement' ) ) {
			$options['decoration']['button']['desktop']['value']['icon']['placement'] = self::get_setting_value( 'button', 'icon_placement' );
		}

		if ( self::is_setting_modified( 'button', 'icon_on_hover' ) ) {
			$options['decoration']['button']['desktop']['value']['icon']['onHover'] = 'yes' === self::get_setting_value( 'button', 'icon_on_hover' ) ? 'on' : 'off';
		}

		// Button font.
		if ( self::is_setting_modified( 'button', 'font_size' ) ) {
			$options['decoration']['font']['font']['desktop']['value']['size'] = self::get_setting_value( 'button', 'font_size' );
		}

		if ( self::is_setting_modified( 'button', 'font_color' ) ) {
			$options['decoration']['font']['font']['desktop']['value']['color'] = self::get_setting_value( 'button', 'font_color' );
		}

		if ( self::is_setting_modified( 'button', 'font_letter_spacing' ) ) {
			$options['decoration']['font']['font']['desktop']['value']['letterSpacing'] = self::get_setting_value( 'button', 'font_letter_spacing' );
		}

		if ( self::is_setting_modified( 'button', 'font_style' ) ) {
			// Get font style value as array, then set it to the options.
			$font_style = explode( '|', self::get_setting_value( 'button', 'font_style' ) );

			$options['decoration']['font']['font']['desktop']['value']['style'] = $font_style;

			// Customizer button font style consist of `bold | italic | uppercase | underline` while visual builder font
			// options' style consist of `italic | uppercase | capitalize | underline | strikethrough` while `bold` style
			// has its own select option. This mean for customizer's bold style to be applicable on visual builder's
			// font options' style, it needs to:
			// 1. Get `bold` value from font style array and set it for `weight` property
			// 2. Remove `bold` from font style array.
			if ( in_array( 'bold', $font_style, true ) ) {
				$options['decoration']['font']['font']['desktop']['value']['weight'] = '700';

				$bold_index = array_search( 'bold', $font_style, true );

				unset( $font_style[ $bold_index ] );

				$options['decoration']['font']['font']['desktop']['value']['style'] = array_values( $font_style );
			}
		}

		if ( self::is_setting_modified( 'button', 'font_family' ) ) {
			$options['decoration']['font']['font']['desktop']['value']['family'] = self::get_setting_value( 'button', 'font_family' );
		}

		// Button font:hover.
		if ( self::is_setting_modified( 'button', 'font_color_hover' ) ) {
			$options['decoration']['font']['font']['desktop']['hover']['color'] = self::get_setting_value( 'button', 'font_color_hover' );
		}

		if ( self::is_setting_modified( 'button', 'font_letter_spacing_hover' ) ) {
			$options['decoration']['font']['font']['desktop']['hover']['letterSpacing'] = self::get_setting_value( 'button', 'font_letter_spacing_hover' );
		}

		// Remove empty array from the returned value. Keyed array when parsed as json read by JS is considered object
		// while empty array remains considered as an array and could cause issue on JS side.
		return self::remove_empty_array( $options );
	}

	/**
	 * Recursively remove empty property from keyed array.
	 *
	 * @since ??
	 *
	 * @param array $array Array to be cleaned.
	 */
	public static function remove_empty_array( array $array ): array {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$array[ $key ] = self::remove_empty_array( $value );
				if ( empty( $array[ $key ] ) ) {
					unset( $array[ $key ] );
				}
			}
		}
		return $array;
	}

	/**
	 * Callback function that is used to filter and return customizer settings values that is being used on Divi Theme.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function settings_values_callback() {
		return [
			'buttonOptions' => self::get_button_options_values(),
		];
	}
}

Customizer::init();
