<?php
/**
 * Utils class
 *
 * @package Builder\FrontEnd
 * @since ??
 */

namespace ET\Builder\Packages\IconLibrary\IconFont;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Utils class.
 *
 * This class contains methods to work with icon font(s).
 *
 * This class is equivalent of JS package:
 * {@link /docs/category/icon-library @divi/icon-library}
 *
 * @since ??
 */
class Utils {

	/**
	 * Get required icon font from the list of icons.
	 *
	 * Check the provided icons list and return the icon that match the provided icon attribute value.
	 * If the provided icon attribute value does not match any icon in the list, return `null`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-icon-library/functions/findIconInList findIconInList}
	 * in `@divi/icon-library` package.
	 *
	 * @since ??
	 *
	 * @param array $icon_list {
	 *   A list of icons.
	 *
	 *   @type array $key {
	 *     @type string $unicode         The unicode representation of the icon symbol.
	 *     @type string $fontWeight      The font weight of the font icon.
	 *     @type array  $styles          The font styles of the font icon.
	 *     @type string $decodedUnicode  The decoded unicode representation of the icon symbol.
	 *   }
	 * }
	 * @param array $icon {
	 *     Icon attribute value.
	 *
	 *     @type string $unicode The unicode representation of the icon symbol.
	 *     @type string $type    The font type.
	 *     @type string $weight  The font weight of the font icon.
	 * }
	 *
	 * @return array The icon that match the provided icon attribute value.
	 *               If the provided icon attribute value does not match any icon in the list, return `null`.
	 */
	public static function find_icon_in_list( array $icon_list, array $icon ): ?array {
		if ( ! isset( $icon['unicode'], $icon['weight'], $icon['type'] ) ) {
			return null;
		}

		$icon_weight  = intval( $icon['weight'] );
		$icon_unicode = $icon['unicode'];

		foreach ( $icon_list as $icon_symbol ) {
			$unicode = strlen( $icon_unicode ) < 2 ? $icon_symbol['decodedUnicode'] : $icon_symbol['unicode'];

			if (
				$icon_unicode === $unicode &&
				intval( $icon_symbol['fontWeight'] ) === $icon_weight &&
				in_array( $icon['type'], $icon_symbol['styles'], true )
			) {
				return $icon_symbol;
			}
		}

		return null;
	}

	/**
	 * Check if the given icon font is `FontAwesome` icon font.
	 *
	 * The font icon is considered to be `FontAwesome` if the icon's type attribute value is `fa`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-icon-library/functions/isFaIcon isFaIcon}
	 * in `@divi/icon-library` package.
	 *
	 * @since ??
	 *
	 * @param array|null $icon {
	 *     Icon attribute value.
	 *
	 *     @type string $type The font type.
	 * }
	 *
	 * @return bool
	 */
	public static function is_fa_icon( ?array $icon ): bool {
		return isset( $icon['type'] ) && 'fa' === $icon['type'];
	}

	/**
	 * Process font icon.
	 *
	 * Process the font icon and return the decoded unicode.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-icon-library/functions/processFontIcon processFontIcon}
	 * in `@divi/icon-library` package.
	 *
	 * @since ??
	 *
	 * @param array|string $icon {
	 *     Icon attribute value.
	 *
	 *     @type string $unicode The unicode representation of the icon symbol.
	 *     @type string $type The font type.
	 *     @type string $weight  The font weight of the font icon.
	 * }
	 * @param bool         $is_font_icons_down Optional. Whether the icon is a font icon from the `downIcons` list.
	 *                                                   Default `false`.
	 * @param bool         $is_unicode         Optional. Whether the icon is a unicode representation of the icon symbol.
	 *                                                   Default `false`.
	 *
	 * @throws \Exception Throw error when the icon JSON file is not exist.
	 *
	 * @return string The decoded unicode representation of the icon symbol.
	 *                If the icon JSON file is not exist, `null` is returned.
	 */
	public static function process_font_icon( $icon, bool $is_font_icons_down = false, bool $is_unicode = false ): ?string {
		global $wp_filesystem;
		static $icon_symbols = [
			'downIcons' => null,
			'iconList'  => null,
		];

		$icon_types = [ 'divi', 'fa' ];
		/**
		* Filter icon library font icon types.
		 *
		* @since ??
		*
		* @param array $icon_types Icon types. Default `['divi', 'fa']`
		*/
		$font_icon_types = apply_filters( 'divi_icon_library_font_icon_types', $icon_types );

		if ( ! isset( $icon['unicode'] ) || ( array_key_exists( 'type', $icon ) && ! in_array( $icon['type'], $font_icon_types, true ) ) ) {
			return null;
		}

		$icon_json_filename = $is_font_icons_down ? 'downIcons' : 'iconList';

		// Load the icon JSON file if it is not loaded yet.
		if ( ! isset( $icon_symbols[ $icon_json_filename ] ) ) {
			$icon_json_file = dirname( __DIR__, 4 ) . '/visual-builder/packages/icon-library/src/components/icon-font/' . $icon_json_filename . '.json';

			$_icon_symbols = json_decode( $wp_filesystem->get_contents( $icon_json_file ), true );

			$icon_symbols[ $icon_json_filename ] = $_icon_symbols;
		}

		// Get the icon symbols list.
		$icon_symbols_list = $icon_symbols[ $icon_json_filename ] ?? [];

		$font_icon = self::find_icon_in_list( $icon_symbols_list, $icon );

		$font_icon_unicode         = $font_icon['unicode'] ?? null;
		$font_icon_decoded_unicode = $font_icon['decodedUnicode'] ?? null;

		return $is_unicode ? $font_icon_unicode : $font_icon_decoded_unicode;
	}

	/**
	 * Escape decoded font icon.
	 *
	 * This function is equivalent of JS function processFontIcon located in:
	 * visual-builder/packages/icon-library/src/components/icon-font/utils/escape-font-icon/index.ts.
	 *
	 * @since ??
	 *
	 * @param string $icon Decoded unicode Icon value.
	 *
	 * @return string|null
	 * @throws \Exception Throw error when the icon json file is not exist.
	 */
	public static function escape_font_icon( $icon = '' ) {
		if ( ! is_string( $icon ) || '' === $icon ) {
			return null;
		}

		switch ( $icon ) {
			case '\\':
				$icon = str_replace( '\\', '\\\\', $icon );
				break;
			case "'":
				$icon = str_replace( "'", "\\'", $icon );
				break;
			case '<':
				$icon = str_replace( '<', '\\003C', $icon );
				break;
			case '>':
				$icon = str_replace( '>', '\\003E', $icon );
				break;
			default:
				break;
		}

		// Convert HTML entities to unicode.
		// Make sure we don't do anything if the icon is in decodedUnicode format.
		// Example: &#x3c; -> \3c.
		if ( strpos( $icon, '#x' ) !== false ) {
			// Replacing `&amp;#x` or `&#x` from the icon with `\`.
			// For example: &amp;#x3c; -> \3c; or &#x3c; -> \3c;.
			$icon = str_replace( [ '&amp;#x', '&#x' ], '\\', $icon );
			// Replacing `;` from the icon. For example: \3c; -> \3c.
			$icon = str_replace( ';', '', $icon );
		}

		return $icon;
	}
}
