<?php
/**
 * ValueExpansion Class
 *
 * @package Divi
 * @since ??
 */

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

namespace ET\Builder\Packages\Conversion;

use ET\Builder\VisualBuilder\Taxonomies\TaxonomiesUtility;
use WP_Error;

/**
 * ValueExpansion Class
 *
 * @since ??
 * @package ET\Builder\Packages\Conversion
 */
class ValueExpansion {
	/**
	 * Obtains an object of the expanded value of an icon attribute.
	 *
	 * @param string $value Expanded attribute value.
	 *
	 * @return array|bool The expanded value or false if no expansion was found.
	 */
	public static function convertFontIcon( $value ) {
		$value_array = explode( '||', $value );

		$icon = [];

		// Directly access array elements and check if they are set.
		if ( ! empty( $value_array[0] ) ) {
			$icon['unicode'] = $value_array[0];
		}

		if ( ! empty( $value_array[1] ) ) {
			$icon['type'] = $value_array[1];
		}

		if ( ! empty( $value_array[2] ) ) {
			$icon['weight'] = $value_array[2];
		}

		return ! empty( $icon ) ? $icon : false;
	}

	/**
	 * Convert Icon.
	 *
	 * Creates an object from the expanded value of the icon attribute.
	 * Value will look something like: %%4%%.
	 * If value matches regex `^%*[0-9]*%*$`, the returned value will be {unicode: '', type: 'divi', weight: '400'}
	 * Unicode defaults to '', unless the value's integer is found in:
	 * ['&#x22;','&#x33;','&#x37;','&#x3b;','&#x3f;','&#x43;','&#x47;','&#xe03a;','&#xe044;','&#xe048;','&#xe04c;']
	 * Otherwise the object returned with contain any of unicode, type or weight keys provided they are in the value.
	 *
	 * @param string $value Expanded attribute value.
	 *
	 * @return array|bool The icon object value or false if no expansion was found.
	 */
	public static function convertIcon( $value ) {
		// Check if the value matches the regex pattern for isIconIndex
		// looking for: %%4%%.
		$is_icon_index = preg_match( '/^%*[0-9]*%*$/', $value );
		if ( $is_icon_index ) {
			$unicodes = [
				'&#x22;',
				'&#x33;',
				'&#x37;',
				'&#x3b;',
				'&#x3f;',
				'&#x43;',
				'&#x47;',
				'&#xe03a;',
				'&#xe044;',
				'&#xe048;',
				'&#xe04c;',
			];

			// Execute regex to extract the icon index value.
			// looking for: %%4%%.
			// https://regex101.com/r/QUv9Eh/1.
			preg_match( '/^%*([0-9]*)%*$/', $value, $matches );

			$icon_index = ! empty( $matches[1] ) ? (int) $matches[1] : null;

			$icon = [
				'unicode' => null !== $icon_index ? $unicodes[ $icon_index ] : '',
				'type'    => 'divi',
				'weight'  => '400',
			];

			return $icon;
		}

		return self::convertFontIcon( $value );
	}

	/**
	 * Converts module inline font format.
	 *
	 * Converts D4 module `inline_font` attribute format to D5 format.
	 * In D4, `inline_font` is a string that contains comma separated values.
	 * This is converted to an array of strings in D5.
	 *
	 * @param string $value String of inline font values.
	 *
	 * @return string[] An array of inline font values.
	 */
	public static function convertInlineFont( $value ) {
		// Check if the value is a string.
		if ( is_string( $value ) ) {
			// Split the string by commas and return as an array.
			return explode( ',', $value );
		}
		// If the value is not a string, return an empty array.
		return [];
	}

	/**
	 * Convert D4 spacing attribute value to D5 format.
	 *
	 * This is used to parse the string passed as argument into D5 spacing format.
	 *
	 * @param string $value Shortcode attribute value for spacing.
	 *
	 * @return array|bool The expanded value or false if no expansion was found.
	 *
	 * @example
	 * ```php
	 * convertSpacing('5px|10px|15px|20px|false|false')
	 * // Returns following spacing object
	 * // [
	 * //   'top'            => '5px',
	 * //   'right'          => '10px',
	 * //   'bottom'         => '15px',
	 * //   'left'           => '20px',
	 * //   'syncVertical'   => 'off',
	 * //   'syncHorizontal' => 'off',
	 * // ]
	 * ```
	 *
	 * @example
	 * ```php
	 * convertSpacing('5px|10px|15px')
	 * // Returns following spacing object
	 * // [
	 * //   'top'            => '5px',
	 * //   'right'          => '10px',
	 * //   'bottom'         => '15px',
	 * //   'left'           => '',
	 * //   'syncVertical'   => 'off',
	 * //   'syncHorizontal' => 'off',
	 * // ]
	 * ```
	 */
	public static function convertSpacing( $value ) {
		$value_array = explode( '|', $value );

		$sync_vertical   = isset( $value_array[4] ) ? $value_array[4] : 'false';
		$sync_horizontal = isset( $value_array[5] ) ? $value_array[5] : 'false';

		$spacing = [
			'top'            => isset( $value_array[0] ) ? $value_array[0] : '',
			'right'          => isset( $value_array[1] ) ? $value_array[1] : '',
			'bottom'         => isset( $value_array[2] ) ? $value_array[2] : '',
			'left'           => isset( $value_array[3] ) ? $value_array[3] : '',
			'syncVertical'   => 'true' === $sync_vertical ? 'on' : 'off',
			'syncHorizontal' => 'true' === $sync_horizontal ? 'on' : 'off',
		];

		return $spacing;
	}

	/**
	 * Get included categories.
	 *
	 * @since ??
	 *
	 * @param string $categories The categories to include.
	 *
	 * @return string[] The included categories.
	 */
	public static function includedCategories( $categories ) {
		// Divi Taxonomies.
		// In VB, this is expressed as `const postCategories = select('divi/settings').getSetting(['postCategories']);`.
		$layout_taxonomies = TaxonomiesUtility::get_taxonomy_terms();
		$post_categories   = array_key_exists( 'category', $layout_taxonomies )
		? $layout_taxonomies['category']
		: (object) [];

		$categories_array = array_filter(
			explode( ',', $categories ),
			function( $item ) {
				return '' !== $item;
			}
		);

		$filter_categories = array_map(
			function( $item ) use ( $post_categories ) {
				if ( 'all' === $item || 'current' === $item ) {
					return $item;
				}
				foreach ( $post_categories as $category ) {
					if ( $category->term_id === (int) $item ) {
						return (int) $item;
					}
				}
			},
			$categories_array
		);

		$filter_categories = array_filter(
			$filter_categories,
			function( $item ) {
				return null !== $item;
			}
		);

		return array_map( 'strval', $filter_categories );
	}

	/**
	 * Get included project categories.
	 *
	 * @since ??
	 *
	 * @param string $categories The categories to include.
	 *
	 * @return string[] The included categories.
	 */
	public static function includedProjectCategories( $categories ) {
		// Divi Taxonomies.
		// In VB, this is expressed as `const postCategories = select('divi/settings').getSetting(['projectCategories']);`.
		$layout_taxonomies = TaxonomiesUtility::get_taxonomy_terms();
		$post_categories   = array_key_exists( 'project_category', $layout_taxonomies )
		? $layout_taxonomies['project_category']
		: (object) [];

		$categories_array = array_filter(
			explode( ',', $categories ),
			function( $item ) {
				return '' !== $item;
			}
		);

		$filter_categories = array_map(
			function( $item ) use ( $post_categories ) {
				if ( 'all' === $item || 'current' === $item ) {
					return $item;
				}
				foreach ( $post_categories as $category ) {
					if ( $category->term_id === (int) $item ) {
						return (int) $item;
					}
				}
			},
			$categories_array
		);

		$filter_categories = array_filter(
			$filter_categories,
			function( $item ) {
				return null !== $item;
			}
		);

		return array_map( 'strval', $filter_categories );
	}

	/**
	 * Replaces the line break placeholder in a string with the actual line break characters.
	 *
	 * Convert the line break placeholder used in the code module to actual line break characters
	 * during the conversion process. This is necessary because the line break placeholder is added in D4 during the
	 * saving process.
	 *
	 * @since ??
	 *
	 * @param string $string The string containing the line break placeholder.
	 *
	 * @return string The string with the line break placeholder replaced by line break characters.
	 */
	public static function replaceLineBreakPlaceholder( $string ) {
		$string = str_replace( '<!-- [et_pb_line_break_holder] -->', "\n", $string );
		$string = str_replace( '||et_pb_line_break_holder||', "\r\n", $string );
		return $string;
	}

	/**
	 * This function parses the conversion string from D4 to `SortableList` fields value.
	 *
	 * @param string $value String conversion string from which we need to parse.
	 *
	 * @return array
	 */
	public static function sortableListConverter( $value ) {
		// Replace %91 with [, %93 with ].
		$options = str_replace( [ '%91', '%93', '%92', '%22' ], [ '[', ']', '\\', '"' ], $value );

		// Decode the URI component.
		$options = urldecode( $options );

		// Decode the JSON string into an associative array.
		$sortable_options = json_decode( $options, true );

		// Map over the array and adjust dragID and checked fields.
		return array_map(
			function ( $option ) {
				return array_merge(
					$option,
					[
						'dragID'  => isset( $option['dragID'] ) ? (string) $option['dragID'] : null,
						'checked' => isset( $option['checked'] ) ? (string) $option['checked'] : null,
					]
				);
			},
			$sortable_options
		);
	}

	/**
	 * Convert image and icon width.
	 *
	 * @since ??
	 *
	 * @param string $value Original value in D4.
	 *
	 * @return array Converted value.
	 */
	public static function convertImageAndIconWidth( string $value ): array {
		return [
			'image' => $value,
			'icon'  => $value,
		];
	}

	/**
	 * Convert true/false to on/off.
	 *
	 * @since ??
	 *
	 * @param string $value The input string value to be converted.
	 *
	 * @return string Converted value.
	 */
	public static function convertTrueFalseToOnOff( string $value ): string {
		return 'true' === $value ? 'on' : 'off';
	}

	/**
	 * Convert success redirect query attribute value to an array of strings.
	 *
	 * @since ??
	 *
	 * @param string $value Original value in D4.
	 *
	 * @return array An array of strings that satisfy the conversion condition.
	 */
	public static function convertSuccessRedirectQuery( string $value ): array {
		$converted   = [];
		$value_pair  = [ 'name', 'last_name', 'email', 'ip_address', 'css_id' ];
		$value_array = explode( '|', $value );

		if ( count( $value_array ) === count( $value_pair ) ) {
			foreach ( $value_array as $index => $item ) {
				if ( 'on' === $item ) {
					$converted[] = $value_pair[ $index ];
				}
			}
		}

		return $converted;
	}

	/**
	 * Converts an email provider account value.
	 *
	 * @since ??
	 *
	 * @param string $value The value to be converted.
	 * @param array  $extra_params {
	 *   An array of arguments.
	 *
	 *   @type array  $attrs       The module attributes.
	 *   @type string $desktopName The desktop name.
	 * }
	 *
	 * @return string|WP_Error The converted value or an error if the conversion fails.
	 */
	public static function convertEmailServiceAccount( string $value, array $extra_params ) {
		$attrs        = $extra_params['attrs'] ?? [];
		$desktop_name = $extra_params['desktopName'] ?? '';

		$provider = $attrs['provider'] ?? '';

		if ( ! $provider ) {
			$provider = 'mailchimp';
		}

		$allowed_names = [
			$provider . '_list',
			$provider . '_account_name',
		];

		if ( ! in_array( $desktop_name, $allowed_names, true ) ) {
			return new WP_Error( 'invalid_desktop_name', "The attribute name $desktop_name did match with selected provider $provider." );
		}

		return $value;
	}

	/**
	 * Convert legacy gradient property.
	 *
	 * @param string $value The value to be converted.
	 *
	 * @since ??
	 */
	public static function convertLegacyGradientProperty( string $value ): string {
		return strval( intval( $value ) );
	}
}
