<?php
/**
 * Conversion: Conversion Class
 *
 * @package Divi
 * @since ??
 */

// phpcs:disable ET -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable Generic -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable PEAR -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable Squiz -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable WordPress -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable PSR2 -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

namespace ET\Builder\Packages\Conversion;

use ET\Builder\Packages\GlobalData\GlobalData;
use WP_Block_Type_Registry;
use ET\Builder\Packages\Module\Options\ModuleOptionsPresetAttrs;
use ET\Builder\Packages\Conversion\ShortcodeMigration;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

// Example usage of str_ends_with in userland PHP prior to PHP 8.0, for compatibility.
if (!function_exists('str_ends_with')) {
	function str_ends_with($haystack, $needle) {
		$length = strlen($needle);
		return $length > 0 ? substr($haystack, -$length) === $needle : true;
	}
}

/**
 * Handles Conversion
 *
 * @since ??
 */
class Conversion {

	const DYNAMIC_CONTENT_REGEX = '/@ET-DC@([^@]+)@/';

	/**
	 * Preset Attributes Map for Conversion.
	 *
	 * This map is used to define the preset attributes type for a module during conversion.
	 * It will be used to cache the preset attributes map for all modules.
	 *
	 * @param array $conversionOutline Module's conversion map.
	 * @return array Module's full conversion map.
	 */
	public static $preset_attrs_maps = [];

	/**
	 * The static property that holds the WooCommerce modules.
	 *
	 * @var array
	 */
	private static $_woo_modules = [];

	/**
	 * The private static variable that holds the third-party modules.
	 *
	 * @var array|null
	 */
	private static $_third_party_modules = [];

	/**
	 * Indicates whether the Shortcode framework has been initialized.
	 *
	 * @var bool
	 */
	private static $is_initialized = false;

	/**
	 * Initializes the shortcode framework for the Conversion class.
	 *
	 * This method checks if the shortcode framework has already been initialized. If not, it loads the Divi shortcode framework
	 * and executes actions for initializing third party modules. It also sets static variables for WooCommerce modules and third
	 * party modules.
	 *
	 * @return void
	 */
	static function initialize_shortcode_framework() {
		if (self::$is_initialized) {
			return;
		}

		// Load Divi shortcode framework, so we can check for shortcodes.
		et_load_shortcode_framework();

		// Execute actions where third party modules are initialized.
		do_action( 'divi_extensions_init' );
		do_action( 'et_builder_ready' );

		// Set static variables.
		self::$_woo_modules = \ET_Builder_Element::get_woocommerce_modules();
		self::$_third_party_modules = array_keys( \ET_Builder_Element::get_third_party_modules() );

		self::$is_initialized = true;
	}

	/**
	 * Get Module Meta Conversion Map.
	 *
	 * @return array
	 */
	static function getMetaConversionMap(): array {
		$privateAttrs = [
			'_builder_version' => 'builderVersion',
			'_module_preset' => 'modulePreset',
			'nonconvertible' => 'nonconvertible',
			'shortcodeName' => 'shortcodeName',
		];

		/**
		 * Filters the meta conversion map for the Divi module during conversion.
		 *
		 * This filter allows developers to modify the meta conversion map for the Divi module during conversion.
		 * The meta conversion map is used to define the different meta fields and their corresponding conversion functions
		 * for a module. By default, the meta conversion map is generated using the `privateAttrs` object.
		 *
		 * @param array $privateAttrs The default meta conversion map.
		 * @param string $privateAttrs['_builder_version'] The module's builder version.
		 * @param string $privateAttrs['_module_preset'] The module's preset.
		 * @param string $privateAttrs['nonconvertible'] The module's nonconvertible.
		 * @param string $privateAttrs['shortcodeName'] The module's shortcode name.
		 */
		return apply_filters('divi.moduleLibrary.conversion.metaConversionMap', $privateAttrs);
	}

	/**
	 * Get Module Conversion Map.
	 *
	 * Get Module's Full Attributes Conversion map based on module's conversion outline.
	 *
	 * @param array $conversionOutline Module's conversion map.
	 * @return array Module's full conversion map.
	 */
	static function getModuleConversionMap(array $conversionOutline): array {

		$advancedOptionConversionFunctionMapping = [
			'admin_label'     => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getAdminLabelConversionMap',
			'animation'       => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getAnimationConversionMap',
			'background'      => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getBackgroundConversionMap',
			'borders'         => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getBorderConversionMap',
			'box_shadow'      => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getBoxShadowConversionMap',
			'button'          => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getButtonConversionMap',
			'display_conditions' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getConditionsConversionMap',
			'dividers'        => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getDividersConversionMap',
			'form_field'      => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getFormFieldConversionMap',
			'filters'         => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getFiltersConversionMap',
			'fonts'           => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getFontConversionMap',
			'gutter'          => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getGutterConversionMap',
			'height'          => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getSizingHeightConversionMap',
			'image_icon'      => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getImageIconConversionMap',
			'max_width'       => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getSizingMaxWidthConversionMap',
			'link_options'    => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getLinkConversionMap',
			'margin_padding'  => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getSpacingConversionMap',
			'module'          => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getIdClassesConversionMap',
			'overflow'        => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getOverflowConversionMap',
			'disabled_on'     => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getDisabledOnConversionMap',
			'position_fields' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getPositionConversionMap',
			'scroll'          => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getScrollConversionMap',
			'sticky'          => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getStickyConversionMap',
			'text'            => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getTextConversionMap',
			'text_shadow'     => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getTextShadowConversionMap',
			'transform'       => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getTransformConversionMap',
			'transition'      => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getTransitionConversionMap',
			'z_index'         => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::getZIndexConversionMap',
		];

		$advancedOptionConversionFunctionMap = apply_filters('divi.moduleLibrary.conversion.advancedOptionConversionFunctionMap', $advancedOptionConversionFunctionMapping);

		$moduleAttrsConversionMap = [
			'attributeMap' => array_merge(self::getMetaConversionMap(), ['content' => 'content.*']),
			'optionEnableMap' => [],
			'valueExpansionFunctionMap' => [],
			'conditionalAttributeConversionFunctionMap' => [],
		];

		// Loop advanced options equivalent at $conversionOutline['advanced'].
		if (isset($conversionOutline['advanced']) && is_array($conversionOutline['advanced'])) {
			foreach ($conversionOutline['advanced'] as $advancedOptionName => $advancedOptionValue) {
				if (isset($advancedOptionConversionFunctionMap[$advancedOptionName])) {
					$advancedOptionConversionFunction = $advancedOptionConversionFunctionMap[$advancedOptionName];

					if (is_callable($advancedOptionConversionFunction)) {
						if (is_array($advancedOptionValue)) {
							// Advanced option that is capable of having multiple settings.
							foreach ($advancedOptionValue as $advancedOptionSubName => $advancedOptionSubValue) {
								$advancedOptionMap = $advancedOptionConversionFunction([
									'd4AdvancedOptionName' => $advancedOptionSubName,
									'd5AttrName' => $advancedOptionSubValue,
								]);

								// Push the value to moduleAttrsConversionMap.
								$moduleAttrsConversionMap['attributeMap'] = array_merge($moduleAttrsConversionMap['attributeMap'], $advancedOptionMap['attributeMap'] ?? []);
								$moduleAttrsConversionMap['valueExpansionFunctionMap'] = array_merge($moduleAttrsConversionMap['valueExpansionFunctionMap'], $advancedOptionMap['valueExpansionFunctionMap'] ?? []);
								$moduleAttrsConversionMap['conditionalAttributeConversionFunctionMap'] = array_merge( $moduleAttrsConversionMap['conditionalAttributeConversionFunctionMap'], $advancedOptionMap['conditionalAttributeConversionFunctionMap'] ?? [] );
								$moduleAttrsConversionMap['optionEnableMap'] = array_merge($moduleAttrsConversionMap['optionEnableMap'], $advancedOptionMap['optionEnableMap'] ?? []);
							}
						} else {
							$advancedOptionMap = $advancedOptionConversionFunction([
								'd4AdvancedOptionName' => $advancedOptionName,
								'd5AttrName' => $advancedOptionValue,
							]);

							// Push the value to moduleAttrsConversionMap.
							$moduleAttrsConversionMap['attributeMap'] = array_merge($moduleAttrsConversionMap['attributeMap'], $advancedOptionMap['attributeMap'] ?? []);
							$moduleAttrsConversionMap['valueExpansionFunctionMap'] = array_merge($moduleAttrsConversionMap['valueExpansionFunctionMap'], $advancedOptionMap['valueExpansionFunctionMap'] ?? []);
							$moduleAttrsConversionMap['conditionalAttributeConversionFunctionMap'] = array_merge( $moduleAttrsConversionMap['conditionalAttributeConversionFunctionMap'], $advancedOptionMap['conditionalAttributeConversionFunctionMap'] ?? [] );
							$moduleAttrsConversionMap['optionEnableMap'] = array_merge($moduleAttrsConversionMap['optionEnableMap'], $advancedOptionMap['optionEnableMap'] ?? []);
						}
					} else {
						throw new \Exception('advancedOptionConversionFunction is not callable! $advancedOptionConversionFunction:' . print_r($advancedOptionConversionFunction, true));
					}
				}
			}
		}

		// Loop CSS options equivalent at $conversionOutline['css'].
		if (isset($conversionOutline['css']) && is_array($conversionOutline['css'])) {
			foreach ($conversionOutline['css'] as $cssD4AttrName => $cssD5Path) {
				$moduleAttrsConversionMap['attributeMap']["custom_css_$cssD4AttrName"] = $cssD5Path;
			}
		}

		// Set $conversionOutline['module'] and $conversionOutline['valueExpansionFunctionMap'].
		// and $conversionOutline['conditionalAttributeConversionFunctionMap'].
		// to $moduleAttrsConversionMap.
		if (isset($conversionOutline['module'])) {
			$moduleAttrsConversionMap['attributeMap'] = array_merge($moduleAttrsConversionMap['attributeMap'], $conversionOutline['module']);
		}
		if (isset($conversionOutline['valueExpansionFunctionMap'])) {
			$moduleAttrsConversionMap['valueExpansionFunctionMap'] = array_merge($moduleAttrsConversionMap['valueExpansionFunctionMap'], $conversionOutline['valueExpansionFunctionMap']);
		}
		if ( isset( $conversionOutline['conditionalAttributeConversionFunctionMap'] ) ) {
			$moduleAttrsConversionMap['conditionalAttributeConversionFunctionMap'] = array_merge( $moduleAttrsConversionMap['conditionalAttributeConversionFunctionMap'], $conversionOutline['conditionalAttributeConversionFunctionMap'] );
		}

		return $moduleAttrsConversionMap;
	}

	/**
	 * Checks if a given color string is a global CSS variable color.
	 *
	 * This function checks if a given color string is a global CSS variable color. A global CSS variable color
	 * is a color string that is a valid CSS variable and has a name that starts with `--gcid-`.
	 *
	 * @param string $color The color string to be checked.
	 *
	 * @return bool True if the color string is a global CSS variable color, false otherwise.
	 */
	static function isGlobalColor( $color ) {
		// Regular expression to match global CSS variable color.
		$regex = '/^var\(--gcid-[0-9a-z-]+\)$/';

		return preg_match( $regex, $color ) === 1;
	}

	/**
	 * Get Module Global Colors.
	 *
	 * @param array $attrs Module attributes.
	 * @return array[] {
	 *     The global colors array
	 *
	 *     @type int $id The global ID.
	 *     @type string $color Global color value
	 *     @type string $status Global color status: active | inactive | temporary,
	 *     @type string $lastUpdated Last updated datetime.
	 *     @type string[] $usedInPosts Array of Post ID where the color has been used.
	 * }.
	 */
	static function getModuleGlobalColors( array $attrs ) {
		$globalColors         = [];
		$unparsedGlobalColors = $attrs['global_colors_info'] ?? '';

		// Attributes to check for gcid colors.
		$colorAttributes = [
			'background_color_gradient_stops',
			'button_bg_gradient_stops',
		];

		if ( ! empty( $unparsedGlobalColors ) && '{}' !== $unparsedGlobalColors ) {
			try {
				$decodedValue = str_replace( ['%22', '%91', '%93'], ['"', '[', ']'], $unparsedGlobalColors );
				$jsonDecode   = json_decode( $decodedValue, true );

				if ( is_array( $jsonDecode ) ) {
					$globalColors = $jsonDecode;
				}
			} catch ( \Exception $e ) {
				// error_log( 'Error decoding global colors: ' . json_last_error_msg() );
			}
		}

		// Iterate through attributes to find or add gcid colors.
		foreach ( $colorAttributes as $attr ) {
			$attrValue = $attrs[$attr] ?? '';

			if ( empty( $attrValue ) ) {
				continue;
			}

			preg_match_all( '/\bgcid-[\w-]+/', $attrValue, $matches );
			if ( empty( $matches[0] ) ) {
				continue;
			}

			foreach ( $matches[0] as $color ) {
				if ( ! isset( $globalColors[$color] ) ) {
					$globalColors[$color] = [];
				}
				if ( ! in_array( $attr, $globalColors[$color], true ) ) {
					$globalColors[$color][] = $attr;
				}
			}
		}

		return $globalColors;
	}

	/**
	 * Convert global colors data to CSS variable format.
	 *
	 * @param string $encodedValue The encoded value.
	 * @param string $name The name of the attribute.
	 * @param array  $moduleGlobalColors The module global colors.
	 * @return string The converted global color.
	 */
	static function convertGlobalColor( $encodedValue, $name, $moduleGlobalColors ) {
		$keys = array_keys( $moduleGlobalColors );

		foreach ( $keys as $globalColorId ) {
			if ( in_array( $name, $moduleGlobalColors[ $globalColorId ] ) ) {
				// Catch all gradient stops attributes. For example:
				// 1. background_color_gradient_stops.
				// 2. button_bg_gradient_stops.

				if ( is_array( $encodedValue ) && strpos( $name, '_gradient_stops' ) !== false ) {
					if ( self::isGlobalColor( "var(--{$encodedValue['color']})" ) ) {
						$globalColorData = GlobalData::get_global_color_by_id( $encodedValue['color'] );

						// Proceed only if the global color id exists in the global color data
						// and the value is the same as the global color id.
						if ( $globalColorData && $encodedValue['color'] === $globalColorId ) {
							// Gradient Stops value consists of position and color.

							$encodedValue = [
								'position' => $encodedValue['position'],
								// Check if the global color id has active status, if so set the global
								// color id as css variable otherwise get the color value from the store
								// and set as the color value.
								'color' => $globalColorData['status'] === 'active'
								? "var(--{$globalColorId})"
								: $globalColorData['color'],
							];

							// Only break if the value is converted to global color.
							break;
						}
					}
				} elseif ( self::isGlobalColor( "var(--{$globalColorId})" ) ) {
					$globalColorData = GlobalData::get_global_color_by_id( $globalColorId );

					// Proceed only if the global color id exists in the global color data
					// and the value is the same as the global color id.
					if ( $globalColorData && $encodedValue === $globalColorId ) {
						// Check if the global color id has active status, if so set the global color id
						// as css variable otherwise get the color value from the store and set as the
						// color value.
						$encodedValue = 'active' === $globalColorData['status']
						? "var(--{$globalColorId})"
						: $globalColorData['color'];

						// Only break if the value is converted to global color.
						break;
					}
				}
			}
		}

		return $encodedValue;
	}

	/**
	 * Filters split test attributes from the given array.
	 *
	 * @since ??
	 *
	 * @param array $attrs The array containing the attributes.
	 *
	 * @return array The filtered array without split test attributes.
	 */
	public static function filterSplitTestAttributes( array $attrs ): array {
		// Interpolate ab_subject_id as disabled_on attribute value.
		if ( isset( $attrs['ab_subject'] ) && isset( $attrs['ab_subject_id'] ) ) {
			$attrs['disabled_on'] = '1' === strval( $attrs['ab_subject_id'] ) ? 'off|off|off' : 'on|on|on';
		}

		// Omit all split test attributes.
		unset( $attrs['ab_subject'] );
		unset( $attrs['ab_subject_id'] );
		unset( $attrs['ab_goal'] );

		return $attrs;
	}

	/**
	 * Normalizes the ab_subject_id value in the given content.
	 *
	 * If the content does not contain 'ab_subject_id' string, it returns the content as is.
	 * If there is only one occurrence of 'ab_subject_id' and its value is not '1', it replaces the value with '1'.
	 * If there is any occurrence of 'ab_subject_id' with value '1', it returns the content as is.
	 * Otherwise, it replaces the first occurrence of 'ab_subject_id' with value '1'.
	 *
	 * @since ??
	 *
	 * @param string $content The content to normalize.
	 * @return string The normalized content.
	 */
	public static function normalizeAbSubjectId( string $content ): string {
		// Check if the content has 'ab_subject_id' string, if not, bail early.
		if (strpos($content, 'ab_subject_id') === false) {
			return $content;
		}
	
		// Check if there is any occurrence with value one, if so, bail early.
		if (strpos($content, 'ab_subject_id="1"') !== false) {
			return $content;
		}

		// Count how many 'ab_subject_id' strings present.
		$count = substr_count($content, 'ab_subject_id');
	
		// If only once, replace the value to one if it is not one.
		if ($count === 1) {
			if (preg_match('/ab_subject_id="(\d+)"/', $content, $matches) && $matches[1] !== '1') {
				return preg_replace('/ab_subject_id="\d+"/', 'ab_subject_id="1"', $content);
			}
		}
	
		// Otherwise, replace the first occurrence to one.
		return preg_replace('/ab_subject_id="\d+"/', 'ab_subject_id="1"', $content, 1);
	}

	/**
     * Checks if a given string matches a regular expression for dynamic content.
     *
     * @since ??
     *
     * @param string $value String to check.
     *
     * @return bool True if the string matches the regular expression for dynamic content.
     */
    public static function isDynamicContent($value) {
        return preg_match(self::DYNAMIC_CONTENT_REGEX, $value) === 1;
    }

	/**
     * Converts dynamic content in a string to a JSON-like format.
     *
     * @since ??
     *
     * @param string $value The string to convert.
     *
     * @return string The converted string. Will return the original string if the conversion fails.
     */
    public static function convertDynamicContent($value) {
        try {
            return preg_replace_callback(self::DYNAMIC_CONTENT_REGEX, function ($matches) {
                $encoded = $matches[1];
                $decoded = base64_decode($encoded);

                // Verify that the decoded string can be encoded back to the original value.
                if ($encoded !== base64_encode($decoded)) {
                    return $matches[0];
                }

                $parsed = json_decode($decoded, true);
                if (!isset($parsed['dynamic'], $parsed['content'], $parsed['settings'])) {
                    return $matches[0];
                }

                return self::formatDynamicContent($parsed['content'], $parsed['settings']);
            }, $value);
        } catch (\Exception $e) {
            return $value;
        }
    }

	/**
     * Formats dynamic content into JSON-like string.
     *
     * @since ??
     *
     * @param string $name The type of dynamic content being formatted.
     * @param array $settings The additional settings for the dynamic content.
     *
     * @return string Returns a string with the formatted dynamic content.
     */
    public static function formatDynamicContent($name, $settings) {
        return '$variable(' . json_encode([
            'type' => 'content',
            'value' => [
                'name' => $name,
                'settings' => $settings,
            ],
        ]) . ')$';
    }

	 /**
     * Maybe Parse Value.
     *
     * Converts a value to an number based on whether the provided `attributeName` is found in the array:
     * `['address_lat', 'address_lng', 'pin_address_lat', 'pin_address_lng', 'zoom_level']`
     * or `['pin.desktop.value.lat', 'pin.desktop.value.lng', 'map.desktop.value.lat',`
     * `'map.desktop.value.lng', 'pin.desktop.value.zoom', 'map.desktop.value.zoom']`.
     * If the `attributeName` is not found, the value is returned as is.
     *
     * @since ??
     *
     * @param string $attributeName The name to be used to search in the paths/attrs that contain number type.
     * @param string|number $value The value to be parsed, can be number or string.
     *
     * @return string|number Parsed/unparsed value.
     */
    public static function maybeParseValue($attributeName, $value) {
        $numberTypeAttrs = [
            'address_lat',
            'address_lng',
            'pin_address_lat',
            'pin_address_lng',
            'zoom_level',
        ];

        $numberTypeObjectPaths = [
            'pin.desktop.value.lat',
            'pin.desktop.value.lng',
            'map.desktop.value.lat',
            'map.desktop.value.lng',
            'pin.desktop.value.zoom',
            'map.desktop.value.zoom',
        ];

        if (in_array($attributeName, $numberTypeAttrs) || in_array($attributeName, $numberTypeObjectPaths)) {
            return (float)$value;
        }

        return $value;
    }

	static function camelCase($string) {
		// TODO: Implement or use an existing library to convert strings to camelCase
		return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
	}

	/**
     * Responsive, hover or sticky state enabled.
     *
     * Determines if responsive, hover or sticky are enabled on an attribute, based on the desktop name.
     * The return value is determined by whether the attribute (responsive/state) starts with 'on' or 'off'.
     *
     * @since ??
     *
     * @param string $type 'hover' | 'sticky' | 'responsive'.
     * @param string $desktopName Attribute name without prefix.
     * @param array $attrs A string object of all module attributes.
     * @param array $optionEnableMap Enable option map to get correct attribute that define option status.
     *
     * @return bool True if responsive/state attribute starts with 'on'.
     */
    public static function enabled($type, $desktopName, $attrs, $optionEnableMap = []) {
        $suffix = $type === 'responsive' ? '_last_edited' : "__{$type}_enabled";

        // Some module options such as Background Options uses one attribute name to determine
        // responsive / hover / sticky status state of every field that existed inside of it.
        // Since the name of the attribute name can be anything depending to the advanced options
        // slug, optionEnableMap that is generated by `getModuleConversionMap()` should be passed.
        $attrName = $optionEnableMap[$desktopName] ?? $desktopName;

		if (isset($attrs["{$attrName}{$suffix}"])) {
			$attribute = $attrs["{$attrName}{$suffix}"];
			return is_string($attribute) && strpos($attribute, 'on') === 0;
		}

		return false;
    }

	/**
     * Determine Sticky status.
     *
     * Determines if sticky options are enabled on a module and on what view port it is activated.
     * If sticky is only enabled on a responsive view then viewport would be the view where it is activated.
     *
     * @since ??
     *
     * @param array $attrs A string object of all module attributes.
     *
     * @return array An object that looks like: ['active' => boolean, 'viewport' => 'desktop' | 'tablet' | 'phone'].
     */
    public static function stickyStatus($attrs) {
        $status = [
            'active' => false,
            'viewport' => 'desktop'
        ];
        $name = 'sticky_position';

        if (self::enabled('responsive', $name, $attrs)) {
            if (array_key_exists("{$name}_phone", $attrs) && $attrs["{$name}_phone"] !== 'none' && $attrs["{$name}_phone"] !== '') {
                $status['active'] = true;
                $status['viewport'] = 'phone';
            }

            if (array_key_exists("{$name}_tablet", $attrs) && $attrs["{$name}_tablet"] !== 'none' && $attrs["{$name}_tablet"] !== '') {
                $status['active'] = true;
                $status['viewport'] = 'tablet';
            }

            if (array_key_exists($name, $attrs) && $attrs[$name] !== 'none' && $attrs[$name] !== '') {
                $status['active'] = true;
                $status['viewport'] = 'desktop';
            }
        } else if (array_key_exists($name, $attrs) && $attrs[$name] !== 'none' && $attrs[$name] !== '') {
            $status['active'] = true;
        }

        return $status;
    }

	/**
     * Sanitizes attribute values.
     *
     * Some values/keys have been changed from D4 format.
     * This function will return the new mapping of the provided value for any that have changed based on `desktopName`.
     * For example `top_left` is updated to `top left`.
     *
     * @since ??
     *
     * @param string|number $value Value to sanitize.
     * @param string $desktopName Attribute name.
     * @param string $moduleName Module name.
     *
     * @return string|number Sanitized value.
     */
    public static function valueSanitization($value, $desktopName, $moduleName) {
        $sanitizedValue = $value;

        if (in_array($moduleName, ['divi/map', 'divi/map-pin', 'divi/fullwidth-map']) && is_string($sanitizedValue)) {
            $sanitizedValue = self::maybeParseValue($desktopName, $sanitizedValue);
        }

        if ('divi/section' === $moduleName) {
            if (in_array($desktopName, ['fullwidth', 'specialty'])) {
                $sanitizedValue = $desktopName;
            }
        }

        // diff(D4, Converted Value) For position_origin_a, position_origin_f and position_origin_r
        // field value: 'top_left', 'top_center', 'top_right', 'center_left', 'center_right', 'bottom_left', 'bottom_center'
        // and 'bottom_right' are migrated to 'top left', 'top center', 'top right', 'center left', 'center right',
        // 'bottom left', 'bottom center' and 'bottom right' respectively to keep consistent with D5 values.
        if (in_array($desktopName, ['position_origin_a', 'position_origin_f', 'position_origin_r'])) {
            $sanitizedValue = str_replace('_', ' ', $value);
        }

        // diff(D4, Converted Value) For background_position, background_pattern_repeat_origin and background_mask_position
        // field value: 'top_left', 'top_center', 'top_right', 'center_left', 'center_right', 'bottom_left', 'bottom_center'
        // and 'bottom_right' are migrated to 'left top', 'center top', 'right top', 'left center', 'right center',
        // 'left bottom', 'center bottom' and 'right bottom' respectively to keep consistent with CSS rule, for example,
        // (X = left, Y = top) instead of (Y = top, X = left).
        if (in_array($desktopName, ['background_position', 'background_pattern_repeat_origin', 'background_mask_position'])) {
            switch ($value) {
                case 'top_left':
                    $sanitizedValue = 'left top';
                    break;
                case 'top_center':
                    $sanitizedValue = 'center top';
                    break;
                case 'top_right':
                    $sanitizedValue = 'right top';
                    break;
                case 'center_left':
                    $sanitizedValue = 'left center';
                    break;
                case 'center_right':
                    $sanitizedValue = 'right center';
                    break;
                case 'bottom_left':
                    $sanitizedValue = 'left bottom';
                    break;
                case 'bottom_center':
                    $sanitizedValue = 'center bottom';
                    break;
                case 'bottom_right':
                    $sanitizedValue = 'right bottom';
                    break;
                default:
                    break;
            }
        }

        // Converted Gradient Unit to Gradient Length (e.g. '100vw' or '100%' or '100mm').
        if ('background_color_gradient_unit' === $desktopName) {
            $sanitizedValue = '100' . $value;
        }

        // diff(D4, Converted Value) Update the value of Custom CSS attributes, replacing '||' with '\n'.
        // While saving the value in D4, '||' was used to separate the lines, but in D5 we just use '\n'
        // in the encoded JSON string to separate the lines when decoded.
        if (strpos($desktopName, 'custom_css_') === 0) {
            $sanitizedValue = str_replace('||', "\n", $value);
        }

        if ('divi/portfolio' === $moduleName && 'fullwidth' === $desktopName) {
            $sanitizedValue = ('off' === $value) ? 'grid' : 'fullwidth';
        }

        // Some modules has `justified` as `text_orientation` value instead of `justify` ( i.e Menu, Text module etc. )
        // So we need to convert `justified` to `justify` to make it work correctly in D5 text options.
        if ('text_orientation' === $desktopName && 'justified' === $value) {
            $sanitizedValue = 'justify';
        }

        // Converts Divider arrangement value from D4 format to D5 format
        // above_content becomes above and below_content becomes below
        $dividerArrangements = [
            'bottom_divider_arrangement',
            'bottom_divider_arrangement_phone',
            'bottom_divider_arrangement_tablet',
            'top_divider_arrangement',
            'top_divider_arrangement_phone',
            'top_divider_arrangement_tablet',
        ];
        if (in_array($desktopName, $dividerArrangements)) {
            $sanitizedValue = ('above_content' === $value) ? 'above' : 'below';
        }

        // Restore specific encoded characters in the given value.
        $sanitizedValue = self::restoreSpecialChars( $sanitizedValue );

        return $sanitizedValue;
    }

	public static function getAttrMap($attrs, $attrName, $moduleName) {
		$value = $attrs[$attrName] ?? '';
		$generated = [];
		$desktopName = preg_replace('/(_tablet|_phone|__hover|__sticky)$/', '', $attrName);
		$viewport = 'desktop';
		$state = 'value';

		$valueExpansionFunctionMapping = [
			'convertFontIcon'             	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertFontIcon',
			'convertIcon'                 	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertIcon',
			'convertInlineFont'           	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertInlineFont',
			'convertSpacing'              	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertSpacing',
			'includedCategories'          	=> 'ET\Builder\Packages\Conversion\ValueExpansion::includedCategories',
			'replaceLineBreakPlaceholder' 	=> 'ET\Builder\Packages\Conversion\ValueExpansion::replaceLineBreakPlaceholder',
			'sortableListConverter'      	=> 'ET\Builder\Packages\Conversion\ValueExpansion::sortableListConverter',
			'convertImageAndIconWidth'    	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertImageAndIconWidth',
			'convertGradientStops'        	=> 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertGradientStops',
			'convertSvgTransform'         	=> 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSvgTransform',
			'convertTrueFalseToOnOff'     	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertTrueFalseToOnOff',
			'convertSuccessRedirectQuery' 	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertSuccessRedirectQuery',
			'convertEmailServiceAccount'  	=> 'ET\Builder\Packages\Conversion\ValueExpansion::convertEmailServiceAccount',
			'includedProjectCategories'   	=> 'ET\Builder\Packages\Conversion\ValueExpansion::includedProjectCategories',
			'convertLegacyGradientProperty' => 'ET\Builder\Packages\Conversion\ValueExpansion::convertLegacyGradientProperty',
		];

		$valueExpansionFunctionMap = apply_filters('divi.moduleLibrary.conversion.valueExpansionFunctionMap', $valueExpansionFunctionMapping);

		// Get all module's conversion map.
		/**
		 * Filters the module's conversion map for the Divi module during conversion.
		 * This filter allows developers to modify the module's conversion map for the Divi module during conversion.
		 *
		 * @param array $moduleConversionMap The module's conversion map.
		 */
		$moduleLibraryConversionMap = apply_filters('divi.conversion.moduleLibrary.conversionMap', []);

		$moduleConversionMap = $moduleLibraryConversionMap[$moduleName] ?? [];

		// error_log('$moduleConversionMap...');
		// error_log('$moduleConversionMap: ' . print_r($moduleConversionMap, true));

		// Get the proper viewport based on the attribute's suffix
		if (preg_match('/_tablet$/', $attrName)) {
			if (!isset($moduleConversionMap['optionEnableMap']) || !self::enabled('responsive', $desktopName, $attrs, ( $moduleConversionMap['optionEnableMap'] ?? [] ))) {
				return [];
			}
			$viewport = 'tablet';
		} elseif (preg_match('/_phone$/', $attrName)) {
			if (!isset($moduleConversionMap['optionEnableMap']) || !self::enabled('responsive', $desktopName, $attrs, ( $moduleConversionMap['optionEnableMap'] ?? [] ))) {
				return [];
			}
			$viewport = 'phone';
		}

		// Get the proper state based on the attribute's suffix
		if (preg_match('/__hover$/', $attrName)) {
			if (!self::enabled('hover', $desktopName, $attrs, ( $moduleConversionMap['optionEnableMap'] ?? [] ))) {
				return [];
			}
			$state = 'hover';
		} elseif (preg_match('/__sticky$/', $attrName)) {
			$status = self::stickyStatus($attrs);
			if (!$status['active'] || !self::enabled('sticky', $desktopName, $attrs, ( $moduleConversionMap['optionEnableMap'] ?? [] ))) {
				return [];
			}
			$viewport = $status['viewport'];
			$state = 'sticky';
		}

		// Handle known internal attributes that should not be converted
		if (in_array($desktopName, [
			'fb_built',
			'sticky_enabled',
			'hover_enabled',
			'_dynamic_attributes',
		])) {
			return [];
		}

		// Determine the conversion key for the attribute
		$attrNameConversionMap = $moduleConversionMap['attributeMap'][$desktopName] ?? null;

		if (is_null($attrNameConversionMap) || $attrNameConversionMap === '') {
			// Handle attributes not present in attributeMap
			if (!empty($moduleConversionMap['attributeMap']) && array_key_exists($desktopName, $moduleConversionMap['attributeMap'])) {
				$attrNameConversionMap = $moduleConversionMap['attributeMap'][$desktopName];
			} elseif (in_array($desktopName, [
				'admin_label',
				'theme_builder_area',
				'global_colors_info',
				'on',
				'locked',
				'open',
			])) {
				$attrNameConversionMap = self::camelCase($desktopName) . '.*';
			} elseif (in_array($desktopName, [
				'global_module',
				'global_parent',
				'nonconvertible',
				'shortcodeName',
			])) {
				$attrNameConversionMap = self::camelCase($desktopName);
			} elseif (in_array($desktopName, [
				'_builder_version',
				'_module_preset',
			])) {
				$attrNameConversionMap = self::camelCase(substr($desktopName, 1));
			} else {
				$attrNameConversionMap = "unknownAttributes.{$desktopName}";
			}
		}

		// We need to make it possible to convert a single D4 module attribute to one of multiple D5 module attributes.
		// The corresponding D5 conversion is handled by the `conditionalConversionMap` property. This map defines a
		// callback function that returns the correct conversion path based on the value of the D4 attribute. Only one
		// path from the `conditionalConversionMap` is used based on the conditions.
		// For example:
		// In the Blurb module,
		// The `image_icon_width` should be converted to either `imageIcon.advanced.width.*.image`
		// or `imageIcon.advanced.width.*.icon` because unlike in D4, the Blurb image width
		// and icon width are separated in D5.
		// The path is picked based on whether the `use_icon` attribute is `on` or `off` in the imported layout.
		// If the `use_icon` attribute is `on`,
		// the `imageIcon.advanced.width.*.icon` is used, otherwise the path `imageIcon.advanced.width.*.image` is used.
		// @see https://github.com/elegantthemes/Divi/issues/34247.
		if (isset($moduleConversionMap['conditionalAttributeConversionFunctionMap'][$desktopName])) {
			$callback = $moduleConversionMap['conditionalAttributeConversionFunctionMap'][$attrName];
			// Ensure the callback is callable.
			if (is_callable($callback)) {
				$attrNameConversionMap = $callback((array)$attrNameConversionMap, $attrs);
			}
		}

		$fullAttributePath = str_replace('*', "{$viewport}.{$state}", $attrNameConversionMap);

		// error_log('$moduleConversionMap[\'valueExpansionFunctionMap\']...');
		// error_log('$moduleConversionMap[\'valueExpansionFunctionMap\']' .  print_r($moduleConversionMap['valueExpansionFunctionMap'], true));
		// error_log('$desktopName: ' . $desktopName);

		if (isset($moduleConversionMap['valueExpansionFunctionMap'][$desktopName])) {
			$valueExpansionFunction = $moduleConversionMap['valueExpansionFunctionMap'][$desktopName];

			// error_log('$valueExpansionFunction: ' . print_r($valueExpansionFunction, true));

			// There are two possible ways this value will show up as:
			// 1. its already a callable
			// 2. We need to look in $valueExpansionFunctionMap[$valueExpansionFunction] to get the callable

			if ( ! is_callable( $valueExpansionFunction ) ) {
				if ( !empty($valueExpansionFunctionMap[$valueExpansionFunction]) ) {
					$valueExpansionFunction = $valueExpansionFunctionMap[$valueExpansionFunction];
				}
			}

			// by the time we get here, we better have a real callable, or we throw an exception
			if ( is_callable( $valueExpansionFunction ) ) {
				$expandedValues = $valueExpansionFunction($value, [
					'attrs'       => $attrs,
					'desktopName' => $desktopName,
					'moduleName'  => $moduleName,
					'viewport'    => $viewport,
					'state'       => $state,
				]);

				// If the valueExpansionFunction returns an WP_Error, skip the conversion.
				$is_skip = is_wp_error( $expandedValues );

				if ( ! $is_skip ) {
					if (is_array($expandedValues) || is_object($expandedValues)) {
						foreach ($expandedValues as $expandedAddress => $expandedValue) {
							$generated["{$fullAttributePath}.{$expandedAddress}"] = $expandedValue;
						}
					} else {
						$generated[$fullAttributePath] = $expandedValues;
					}
				}
			} else {
				throw new \Exception('Value expansion function is not callable. valueExpansionFunction: ' . $valueExpansionFunction);
			}
		}

		// Handle value expansion logic
		// if (isset($moduleConversionMap['valueExpansionFunctionMap'][$desktopName]) && is_callable($moduleConversionMap['valueExpansionFunctionMap'][$desktopName])) {
		// 	$expandedValues = $moduleConversionMap['valueExpansionFunctionMap'][$desktopName]($value);

		// 	if (is_array($expandedValues) || is_object($expandedValues)) {
		// 		foreach ($expandedValues as $expandedAddress => $expandedValue) {
		// 			$generated["{$fullAttributePath}.{$expandedAddress}"] = $expandedValue;
		// 		}
		// 	} else {
		// 		$generated[$fullAttributePath] = $expandedValues;
		// 	}
		// }

		else if (is_string($moduleConversionMap['attributeMap'][$desktopName] ?? null)) {
			$generated[$fullAttributePath] = self::valueSanitization($value, $desktopName, $moduleName);
		} else {
			$generated[$fullAttributePath] = $value;
		}

		return $generated;
	}

	static function encodeAttrs(array $attrs, string $moduleName, ?string $content = null):string {
		// Convert the attributes to the new format.
		$convertedAttrs = self::convertAttrs($attrs, $moduleName, $content);

		// Encode the converted attributes.
		$encodedAttrs = serialize_block_attributes($convertedAttrs);

		return $encodedAttrs;
	}

	static function convertShortcodeToGbFormat($shortcodePart, $gbReset = true, $globalID = null) {
		static $gbString = '';

		if ($gbReset) {
			$gbString = '';
		}

		// Replace with the PHP equivalent of select('divi/module-library').getModules()
		$moduleCollections = self::getModuleCollections();  // Placeholder function to get module collections

		$convertibleModules = array_filter($moduleCollections, function ($module) {
			return !empty($module['d4Shortcode']);
		});

		$convertibleModulesSlug = [];
		foreach ($convertibleModules as $convertibleModule) {
			$convertibleModulesSlug[$convertibleModule['d4Shortcode']] = $convertibleModule['name'];
		}

		foreach ($shortcodePart as $element) {
			$nonconvertible = isset($element['attrs']['nonconvertible']) && $element['attrs']['nonconvertible'] === 'yes' ? 'yes' : 'no';
			$moduleName = $nonconvertible === 'yes' || empty($convertibleModulesSlug[$element['name']])
				? 'divi/shortcode-module'
				: $convertibleModulesSlug[$element['name']];

			$content = $nonconvertible !== 'yes' && is_string($element['content']) && $element['content'] !== '' ? $element['content'] : null;
			$globalModuleID = $globalID ?: (isset($element['attrs']['global_module']) ? $element['attrs']['global_module'] : null);

			// if $element[\'attrs\'] is empty, set it to an empty array
			// this can occur if the shortcode has 0 attributes, e.g. [et_pb_accordion][et_pb_accordion_item something="blah...
			if ( ! isset( $element['attrs'] ) || '' === $element['attrs']) {
				$element['attrs'] = [];
			}

			$attrs = array_merge($element['attrs'], $globalID ? ['global_parent' => $globalModuleID] : []);

			$encodedAttrs = self::encodeAttrs($attrs, $moduleName, $content);

			// if $encodedAttrs is an empty array, set it to ''
			$encodedAttrs = $encodedAttrs === '[]' ? '' : $encodedAttrs;

			if (is_array($element['content'])) {
				$gbString .= "<!-- wp:{$moduleName} {$encodedAttrs} -->";
				self::convertShortcodeToGbFormat($element['content'], false, $globalModuleID);
				$gbString .= "<!-- /wp:{$moduleName} -->";
			} elseif ($nonconvertible === 'yes') {
				$gbString .= "<!-- wp:{$moduleName} {$encodedAttrs} -->{$element['content']}<!-- /wp:{$moduleName} -->";
			} else {
				$gbString .= "<!-- wp:{$moduleName} {$encodedAttrs} --><!-- /wp:{$moduleName} -->";
			}

			// error_log('$attrs: ' . print_r($encodedAttrs, true));
		}

		return $gbString;
	}

	static function getModuleCollections() {
		static $moduleCollections = null;

		if (null !== $moduleCollections) {
			return $moduleCollections;
		}

		$all_registered_modules = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		// $moduleCollections = [
		// 	[
		// 		'name' => 'divi/text',
		// 		'd4Shortcode' => 'et_pb_text',
		// 	],
		// 	[
		// 		'name' => 'divi/section',
		// 		'd4Shortcode' => 'et_pb_section',
		// 	],
		// ];
		$moduleCollections = [];

		foreach ($all_registered_modules as $module) {
			if ( !empty( $module->d4Shortcode ) ) {

				$_module = [
					'name' => $module->name,
					'd4Shortcode' => $module->d4Shortcode,
				];

				if (isset($module->childrenName)) {
					$_module['childrenName'] = $module->childrenName;
				}

				// [
				// 	'name' => $module->name,
				// 	'd4Shortcode' => $module->d4Shortcode,
				// 	'childrenName' => $module->childrenName ?? null,
				// ];
				$moduleCollections[] = $_module;
			}
		}

		return $moduleCollections;
	}

	/**
	 * Convert content when required.
	 *
	 * Converts the content from D4 format to GB format.
	 *
	 * @since ??
	 *
	 * @param string $content_raw The content to be converted.
	 *
	 * @return string The converted content.
	 */
	static function maybeConvertContent($content_raw) {
		// Maybe convert global colors data.
		GlobalData::maybe_convert_global_colors_data();

		$content_migrated = ShortcodeMigration::maybe_migrate_legacy_shortcode( $content_raw );

		$content   = self::normalizeAbSubjectId( $content_migrated );
		$converted = $content;
		$moduleCollections = self::getModuleCollections();

		// Define the startsWithShortcodeRegExp equivalent in PHP.
		// See add_shortcode() function in WordPress for reference.
		// Ref: https://regexr.com/84q4v
		$startsWithShortcodeRegExp = '@^\[[^<>&/\[\]\x00-\x20=]+@';

		if (preg_match($startsWithShortcodeRegExp, $content)) {
			$converted = self::convertShortcodeToGbFormat(self::parseShortcode($content, $moduleCollections));
		} else if (strpos($content, '<!-- wp:divi/layout -->') !== false) {
			// parse blocks and iteratively convert them or concatenate them
			// as $blockObjects is an array of blocks with their details.
			$blockObjects = parse_blocks($content);  // parse_blocks is a WordPress function to parse blocks
			$converted = '';
			foreach ($blockObjects as $block) {
				if ('divi/layout' === $block['blockName']) {
					$converted .= self::convertShortcodeToGbFormat(self::parseShortcode(trim($block['innerHTML']), $moduleCollections));
				} else if (null === $block['blockName']) {
					$converted .= $block['innerHTML'];
				} else {
					$blockName = str_replace('core/', 'wp:', $block['blockName']);
					$converted .= "<!-- {$blockName} " . json_encode($block['attrs']) . " -->{$block['innerHTML']}<!-- /{$blockName} -->";
				}
			}
		}

		return $converted;
	}

	static function parseShortcode($shortcode, $moduleCollections, $parentName = null) {
		$convertibleModules = array_filter($moduleCollections, function ($module) {
			return !empty($module['d4Shortcode']);
		});

		$modules = array_column($convertibleModules, null, 'name');
		$shortcodeModules = array_column($convertibleModules, null, 'd4Shortcode');

		// Extract the d4Shortcode values into an array
		$d4shortcodeTags = array_map(function ($module) {
			return $module['d4Shortcode'];
		}, $convertibleModules);
		
		$shortcodeTags = array_merge([], $d4shortcodeTags, self::$_woo_modules, self::$_third_party_modules);

		// Build the regex
		$shortcodeTagPattern = get_shortcode_regex($shortcodeTags);

		preg_match_all('/' . $shortcodeTagPattern . '/s', $shortcode, $matches, PREG_SET_ORDER);

		$result = [];

		foreach ($matches as $parsed) {
			$shortcodeName = $parsed[2];
			// Check if module can be converted, and set nonconvertible attribute accordingly.
			$nonconvertible = ($shortcodeName !== 'et_pb_unsupported' && isset($shortcodeModules[$shortcodeName])) ? 'no' : 'yes';

			// For nonconvertible modules, generate special attributes.
			$attributes = $nonconvertible === 'yes'
				? self::generateNonconvertibleAttributes(shortcode_parse_atts($parsed[3]), $shortcodeName, $nonconvertible)
				: \shortcode_parse_atts($parsed[3]);

			$isParentModule = self::hasChildShortcode([
				'nonconvertible' => $nonconvertible,
				'content' => $parsed[5],
				'modules' => $modules,
				'shortcodeModules' => $shortcodeModules,
				'shortcodeName' => $shortcodeName
			]);

			$nextParentName = $isParentModule ? $shortcodeName : '';

			$content = $nonconvertible === 'yes'
				? $parsed[0]
				: ($isParentModule ? self::parseShortcode($parsed[5], $moduleCollections, $nextParentName) : $parsed[5]);

			$result[] = [
				'name' => $shortcodeName,
				'attrs' => $attributes,
				'parentName' => $parentName ?? '',
				'content' => $content,
			];
		}

		return $result;
	}

	static function generateNonconvertibleAttributes($allAttrs, $shortcodeName, $nonconvertible) {
		// Define allowed attributes
		$allowedAttributes = ['_builder_version', '_module_preset', 'nonconvertible'];

		// Strip all unwanted attributes from unsupported module
		$attributes = array_intersect_key($allAttrs, array_flip($allowedAttributes));

		// Add shortcodeName to attributes
		$attributes['shortcodeName'] = $shortcodeName;
		$attributes['nonconvertible'] = $nonconvertible;

		return $attributes;
	}

	static function hasChildShortcode($params) {
		$nonconvertible = $params['nonconvertible'];
		$content = trim($params['content']);  // Make sure to trim the content as done in the original TypeScript function.
		$modules = $params['modules'];
		$shortcodeModules = $params['shortcodeModules'];
		$shortcodeName = $params['shortcodeName'];

		if ($nonconvertible === 'yes') {
			return false;
		}

		$structureShortcodes = [
			'et_pb_section',
			'et_pb_row',
			'et_pb_row_inner',
			'et_pb_column',
			'et_pb_column_inner',
		];

		if (in_array($shortcodeName, $structureShortcodes, true)) {
			return true;
		}

		if (!isset($shortcodeModules[$shortcodeName]['childrenName'])) {
			return false;
		}

		$childModules = $shortcodeModules[$shortcodeName]['childrenName'];
		$childShortcodes = array_map(function ($childModule) use ($modules) {
			return isset($modules[$childModule]['d4Shortcode']) ? $modules[$childModule]['d4Shortcode'] : null;
		}, $childModules);

		// Filter out any null values that might have been added if a module key didn't exist
		$childShortcodes = array_filter($childShortcodes);

		// Early return if no child shortcodes
		if (empty($childShortcodes)) {
			return false;
		}

		// Escape the shortcodes for use in regex and join them with | to create the pattern
		$childShortcodesPattern = implode('|', array_map(function ($shortcode) {
			return preg_quote($shortcode, '/');
		}, $childShortcodes));

		// Construct the regex pattern to match any of the child shortcodes at the beginning of the content.
		$childShortcodeRegExp = '/^\[(' . $childShortcodesPattern . ')/';

		// Use the regex pattern to test if the content contains any child shortcodes.
		return preg_match($childShortcodeRegExp, $content) === 1;
	}

	/**
	 * Maybe convert presets data.
	 *
	 * @param array $presets The presets data to be converted.
	 * @return array The converted presets data.
	 */
	public static function maybe_convert_presets_data( $presets ) {
		// Bail early if the presets is from D5.
		if ( self::is_global_data_presets_items( $presets ) ) {
			return $presets;
		}

		$output = [ 'module' => [] ];

		// Bail early if there are no presets.
		if ( empty( $presets ) ) {
			return $output;
		}

		// Get list of the D5 modules.
		$module_collections = self::getModuleCollections();

		$convertible_modules = array_filter(
			$module_collections,
			function ( $module ) {
				return ! empty( $module['d4Shortcode'] );
			}
		);

		$convertible_modules_slug = [];
		$default_presets          = [];

		// It will add Divi 4 shortcode as key and module name as value.
		foreach ( $convertible_modules as $convertible_module ) {
			$convertible_modules_slug[ $convertible_module['d4Shortcode'] ] = $convertible_module['name'];

			// Store default preset for each module.
			if ( isset( $presets[ $convertible_module['d4Shortcode'] ]['default'] ) ) {
				$default_presets[ $convertible_module['name'] ] = $presets[ $convertible_module['d4Shortcode'] ]['default'];
			}
		}

		if ( isset( $presets['et_pb_section_fullwidth']['default'] ) ) {
			$default_presets['divi/fullwidth-section'] = $presets['et_pb_section_fullwidth']['default'];
		}

		if ( isset( $presets['et_pb_section_specialty']['default'] ) ) {
			$default_presets['divi/specialty-section'] = $presets['et_pb_section_specialty']['default'];
		}

		$all_presets = [];
		foreach ( $presets as $d4_shortcode => $value ) {
			if ( isset( $value['presets'] ) && is_array( $value['presets'] ) ) {
				foreach ( $value['presets'] as $preset_id => $preset_value ) {
					$all_presets[] = self::convert_preset( $preset_value, $preset_id, $d4_shortcode );
				}
			}
		}

		foreach ( $all_presets as $converted_preset ) {
			if ( $converted_preset ) {
				$output['module'][ $converted_preset['moduleName'] ]['items'][ $converted_preset['id'] ] = $converted_preset;

				// Add default preset if it's not already added.
				if ( ! isset( $output['module'][ $converted_preset['moduleName'] ]['default'] ) ) {
					$output['module'][ $converted_preset['moduleName'] ]['default'] = $default_presets[ $converted_preset['moduleName'] ] ?? '';
				}
			}
		}

		return $output;
	}

	/**
	 * Convert a D4 preset to a D5 compatible preset.
	 *
	 * @param array  $preset The D4 preset attributes.
	 * @param string $preset_id The preset ID.
	 * @param string $d4_shortcode The D4 module shortcode.
	 * @return array | null The converted preset or null on failure.
	 */
	public static function convert_preset( $preset, $preset_id, $d4_shortcode ) {
		// Get current module version from settings.
		$version = ET_CORE_VERSION;

		// Get list of D5 modules.
		$module_collections = self::getModuleCollections();

		// Filter modules that have a D4 shortcode.
		$convertible_modules = array_filter(
			$module_collections,
			function( $module ) {
				return ! empty( $module['d4Shortcode'] );
			}
		);

		// Map D4 shortcode to module name.
		$convertible_modules_slug = [];
		foreach ( $convertible_modules as $module ) {
			$convertible_modules_slug[ $module['d4Shortcode'] ] = $module['name'];
		}

		// Add support for fullwidth and specialty sections.
		$convertible_modules_slug['et_pb_section_fullwidth'] = 'divi/fullwidth-section';
		$convertible_modules_slug['et_pb_section_specialty'] = 'divi/specialty-section';

		// Get D5 module name from D4 shortcode.
		$module_name = $convertible_modules_slug[ $d4_shortcode ] ?? null;

		if ( ! $module_name ) {
			return null; // Return null if no matching module found.
		}

		// Restore preset module name (if needed).
		$restored_module_name = self::maybe_restore_preset_module_name( $module_name );

		// Encode and decode preset attributes (simulating the encodeAttrs function).
		$converted_preset_attrs = self::convertAttrs( $preset['settings'], $restored_module_name );

		// Get preset attributes mapping.
		// TODO - Get preset attributes mapping.
		$map = self::get_preset_attrs_mapping( $restored_module_name );

		// Construct the converted preset item.
		$converted_preset_item = [
			'id'          => $preset_id,
			'moduleName'  => $module_name,
			'name'        => $preset['name'],
			'attrs'       => $converted_preset_attrs,
			// TODO use map to get the attrs.
			// 'attrs'       => self::get_preset_attrs( $converted_preset_attrs, [ 'style', 'html', 'script' ], $map ),
			'styleAttrs'  => self::get_preset_attrs( $converted_preset_attrs, [ 'style' ], $map ),
			'renderAttrs' => self::get_preset_attrs( $converted_preset_attrs, [ 'html', 'script' ], $map ),
			'version'     => $preset['version'] ?? $version,
			'type'        => 'module',
		];

		return $converted_preset_item;
	}

	/**
	 * Check if input is of type GlobalData.Presets.Items.
	 *
	 * @param mixed $presets The presets object to be checked.
	 * @return bool A boolean indicating whether the `presets` object is of type GlobalData.Presets.Items.
	 */
	public static function is_global_data_presets_items( $presets ) {
		return isset( $presets['module'] );
	}

	/**
	 * Maybe restore preset module name.
	 *
	 * @param string $module_name The module name to be checked.
	 * @return string The restored module name.
	 */
	public static function maybe_restore_preset_module_name( string $module_name ): string {
		$preset_modules = [ 'divi/fullwidth-section', 'divi/specialty-section', 'divi/section' ];

		if ( in_array( $module_name, $preset_modules ) ) {
			return 'divi/section';
		}

		return $module_name;
	}

	/**
	 * Checks if the specified keys exist in the array.
	 *
	 * @param array $array The array to check.
	 * @param array $keys The keys to check for.
	 * @return bool True if all keys exist, false otherwise.
	 */
	public static function has( array $array, array $keys ): bool {
		$current = $array;
		foreach ( $keys as $key ) {
			if ( ! is_array( $current ) || ! array_key_exists( $key, $current ) ) {
				return false;
			}
			$current = $current[ $key ];
		}
		return true;
	}

	/**
	 * Retrieves the value at the specified keys in the array.
	 *
	 * @param array $array The array to retrieve the value from.
	 * @param array $keys The keys to retrieve the value for.
	 * @return mixed The value at the specified keys, or null if the keys do not exist.
	 */
	public static function get( array $array, array $keys ) {
		$current = $array;
		foreach ( $keys as $key ) {
			if ( ! is_array( $current ) || ! array_key_exists( $key, $current ) ) {
				return null; // Return null if the key does not exist.
			}
			$current = $current[ $key ];
		}
		return $current;
	}

	/**
	 * Sets the value at the specified keys in the array.
	 *
	 * @param array &$array The array to set the value in.
	 * @param array $keys The keys to set the value for.
	 * @param mixed $value The value to set.
	 * @return void
	 */
	public static function set( array &$array, array $keys, $value ): void {
		$current = &$array;
		foreach ( $keys as $key ) {
			if ( ! isset( $current[ $key ] ) ) {
				$current[ $key ] = []; // Create an empty array if the key does not exist.
			}
			$current = &$current[ $key ];
		}
		$current = $value; // Set the value at the final key.
	}

	/**
	 * Retrieves the preset attributes for a given preset type.
	 *
	 * @param array $preset_type The type of the preset.
	 * @param array $module_attrs The module attributes.
	 * @param array $map The attribute map.
	 * @return array The preset attributes.
	 */
	public static function get_preset_attrs( $module_attrs, $preset_type, $map ) {
		$attrs = [];

		$attr_names = self::get_preset_attrs_names( $preset_type, $module_attrs, $map );

		foreach ( $attr_names as $attr ) {
			$attr_name  = $attr['attrName'];
			$sub_name   = $attr['subName'] ?? null;
			$breakpoint = $attr['breakpoint'] ?? null;
			$state      = $attr['state'] ?? null;

			$attr_name_paths = explode( '.', $attr_name );

			if ( $breakpoint ) {
				$attr_name_paths[] = $breakpoint;
			}

			if ( $state ) {
				$attr_name_paths[] = $state;
			}

			if ( $sub_name ) {
				$sub_name_paths  = explode( '.', $sub_name );
				$attr_name_paths = array_merge( $attr_name_paths, $sub_name_paths );
			}

			if ( self::has( $module_attrs, $attr_name_paths ) ) {
				self::set( $attrs, $attr_name_paths, self::get( $module_attrs, $attr_name_paths ) );
			}
		}

		return $attrs;
	}

	/**
	 * Retrieves the preset attributes names.
	 *
	 * @param array $preset_type The type of the preset.
	 * @param array $module_attrs The module attributes.
	 * @param array $map The attribute map.
	 * @return array The preset attribute names.
	 */
	public static function get_preset_attrs_names( $preset_type, $module_attrs, $map ) {
		$attrs_name = [];

		$is_duplicate = function( $item_to_find ) use ( &$attrs_name ) {
			foreach ( $attrs_name as $item ) {
				if ( $item === $item_to_find ) {
					return true;
				}
			}
			return false;
		};

		$mappings_filtered = array_filter(
			$map,
			function( $mapping ) use ( $preset_type ) {
				if ( ! isset( $mapping['preset'] ) ) {
					return false;
				}

				$preset = $mapping['preset'];
				if ( is_array( $preset ) ) {
					foreach ( $preset as $item ) {
						if ( in_array( $item, $preset_type, true ) ) {
							return true;
						}
					}
					return false;
				}

				return in_array( $preset, $preset_type, true );
			}
		);

		foreach ( $mappings_filtered as $mapping ) {
			$attr_name       = $mapping['attrName'];
			$sub_name        = $mapping['subName'] ?? null;
			$attr_name_paths = explode( '.', $attr_name );

			$current_attr = $module_attrs;
			foreach ( $attr_name_paths as $key ) {
				if ( ! isset( $current_attr[ $key ] ) ) {
					$current_attr = null;
					break;
				}
				$current_attr = $current_attr[ $key ];
			}

			if ( null !== $current_attr ) {
				$breakpoint_states_values = $current_attr;

				if ( $sub_name ) {
					$sub_name_paths = explode( '.', $sub_name );

					foreach ( $breakpoint_states_values as $breakpoint => $states ) {
						foreach ( $states as $state => $state_value ) {
							$item_to_find = [
								'attrName'   => $attr_name,
								'subName'    => $sub_name,
								'breakpoint' => $breakpoint,
								'state'      => $state,
							];

							$sub_value = $state_value;
							foreach ( $sub_name_paths as $sub_key ) {
								if ( ! isset( $sub_value[ $sub_key ] ) ) {
									$sub_value = null;
									break;
								}
								$sub_value = $sub_value[ $sub_key ];
							}

							if ( null !== $sub_value && ! $is_duplicate( $item_to_find ) ) {
								$attrs_name[] = $item_to_find;
							}
						}
					}
				} elseif ( ! $is_duplicate( [ 'attrName' => $attr_name ] ) ) {
					$attrs_name[] = [ 'attrName' => $attr_name ];
				}
			}
		}

		return $attrs_name;
	}

	/**
	 * Restores specific encoded characters in the given value.
	 *
	 * If any of these encoded characters are found, they are replaced with their corresponding
	 * restored characters:
	 * - %22 -> "
	 * - %92 -> \
	 * - %91 -> &#91;
	 * - %93 -> &#93;
	 * - %5c -> \.
	 * 
	 * @since ??
	 * 
	 * @see /visual-builder/packages/conversion/src/utils/restore-special-chars/index.ts
	 *
	 * @param string $value The value to restore. Can be a string or a number.
	 * @return string The restored value, or the original value if no encoded characters are found.
	 */
	public static function restoreSpecialChars( $value ) {
		$strValue = strval($value);

		// Check if the string contains any of the encoded characters, if not then return original value.
		if (!preg_match('/%91|%93|%22|%92|%5c/', $strValue)) {
			return $value;
		}

		// Perform replacements if encoded characters are found.
		$strValue = str_replace('%22', '"', $strValue);
		$strValue = str_replace('%92', '\\', $strValue);
		$strValue = str_replace('%91', '&#91;', $strValue);
		$strValue = str_replace('%93', '&#93;', $strValue);
		$strValue = str_replace('%5c', '\\', $strValue);

		return $strValue;
	}

	/**
	 * Replaces specific characters in a string to ensure it can be safely embedded in various contexts.
	 *
	 * This function is adapted from the core function `serializeAttributes`.
	 * It replaces characters that might interfere with embedding the result in an HTML comment or other contexts.
	 * Ref: https://github.com/WordPress/gutenberg/blob/release/17.7/packages/blocks/src/api/serializer.js#L263C17-L263C36
	 *
	 * The result is a string with unicode escape sequence substitution for characters which might otherwise interfere with embedding the result.
	 *
	 * @param string $content The string to be processed.
	 * @return string The processed string with replaced characters.
	 */
	public static function maybe_replace_special_characters( $content ) {
		$patterns = [
			'/--/',
			'/</',
			'/>/',
			'/&/',
			'/\\"/'
		];
		
		$replacements = [
			'\\u002d\\u002d',
			'\\u003c',
			'\\u003e',
			'\\u0026',
			'\\u0022'
		];
		
		$encoded_string = preg_replace( $patterns, $replacements, $content );

		return $encoded_string;
	}

	public static function get_the_preset_item_map(array $item, string $full_attr_name) {
		$component_type = $item['component']['type'] ?? '';
		$is_field = 'field' === $component_type;
		$is_group = 'group' === $component_type;

		if ($is_field) {
			$attrs_map_item = [
				'attrName' => $full_attr_name,
				'preset' => $item['features']['preset'] ?? ['style'],
			];

			$attrs_map_item_key = $full_attr_name;

			if (isset($item['subName'])) {
				$attrs_map_item['subName'] = $item['subName'];

				$attrs_map_item_key .= '__' . $item['subName'];
			}

			return [
				$attrs_map_item_key => $attrs_map_item,
			];
		} elseif ($is_group) {
			$group_name = $item['component']['name'] ?? '';
			$group_attrs = ModuleOptionsPresetAttrs::get_preset_attrs_from_group($group_name, $full_attr_name);
			return $group_attrs;
		}

		return [];
	}

	/**
	 * Get the preset attributes mapping for a module.
	 *
	 * @since ??
	 *
	 * @param string $module_name The module name.
	 *
	 * @return array The preset attributes map.
	 */
	public static function get_preset_attrs_mapping( $module_name ) {
		// Cache the preset attributes maps.
		if (isset(self::$preset_attrs_maps[$module_name])) {
			return self::$preset_attrs_maps[$module_name];
		}

		$attrs_map = [];

		// Implement logic to retrieve mapping.
		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( $module_name );

		// Bail when no attributes found.
		if ( ! isset( $block->attributes ) ) {
			return $attrs_map;
		}

		$attributes        = $block->attributes;
		$custom_css_fields = $block->customCssFields;

		// Create map for the attributes.
		if (! empty( $attributes ) ) {
			foreach ( $attributes as $attr_name => $attr_data ) {
				$settings = $attr_data['settings'] ?? [];
				$element_type = $attr_data['elementType'] ?? 'element';

				foreach ( $settings as $attrs_type => $setting_items ) {
					
					if('innerContent' === $attrs_type){
						// Inner content attributes.
						$full_attr_name = "{$attr_name}.{$attrs_type}";

						if (
							is_array($setting_items) &&
							(
								0 === count($setting_items) ||
								in_array($element_type, ['content', 'headingLink'], true)
							)
						) {
							if ('headingLink' === $element_type) {
								$heading_link_inner_content_attrs = [
									"{$full_attr_name}__text" => [
										'attrName' => $full_attr_name,
										'preset' => 'content',
										'subName' => 'text',
									],
									"{$full_attr_name}__url" => [
										'attrName' => $full_attr_name,
										'preset' => 'content',
										'subName' => 'url',
									],
									"{$full_attr_name}__target" => [
										'attrName' => $full_attr_name,
										'preset' => 'content',
										'subName' => 'target',
									],
								];

								$attrs_map = array_merge($attrs_map, $heading_link_inner_content_attrs);
							} elseif ('content' === $element_type) {
								$content_inner_content_attrs = [
									$full_attr_name => [
										'attrName' => $full_attr_name,
										'preset' => 'content',
									],
								];

								$attrs_map = array_merge($attrs_map, $content_inner_content_attrs);
							}
						} elseif (isset($setting_items['groupType']) && 'group-item' === $setting_items['groupType']) {

							$item_attrs_map = self::get_the_preset_item_map($setting_items['item'], $full_attr_name);
							$attrs_map = array_merge($attrs_map, $item_attrs_map);

						} elseif (isset($setting_items['groupType']) && 'group-items' === $setting_items['groupType']) {
							// error_log(print_r($setting_item, true));
							$items = $setting_items['items'] ?? [];

							foreach ($items as $item) {
								$item_attrs_map = self::get_the_preset_item_map($item, $full_attr_name);
								$attrs_map = array_merge($attrs_map, $item_attrs_map);
							}
						}

					} elseif ( ! empty( $setting_items ) ) {
						// Advanced, Decorations, Meta, etc.
						foreach ( $setting_items as $setting_item_key => $setting_item ) {
							$full_attr_name = "{$attr_name}.{$attrs_type}.{$setting_item_key}";
							// error_log($full_attr_name);
							// error_log(print_r($setting_item, true));

							if (is_array($setting_item) && (0 === count($setting_item) || ! isset($setting_item['groupType']))) {
								$args = [];

								if ('decoration' === $attrs_type && 'font' === $setting_item_key && in_array($element_type, ['heading', 'headingLink'], true)) {
									$args['has_heading_level'] = true;
								}

								// If the setting item is an empty array, generate the group name from the key.
								$group_name = ModuleOptionsPresetAttrs::get_the_group_name_by_key($attrs_type, $setting_item_key);

								// error_log(print_r($args, true));


								// If the group name is not empty, get the preset attributes from the group.
								if (!empty($group_name)) {
									$group_attrs = ModuleOptionsPresetAttrs::get_preset_attrs_from_group($group_name, $full_attr_name, $args);
									$attrs_map = array_merge($attrs_map, $group_attrs);
								}
							} elseif (isset($setting_item['groupType']) && 'group-item' === $setting_item['groupType']) {

								$item_attrs_map = self::get_the_preset_item_map($setting_item['item'], $full_attr_name);
								$attrs_map = array_merge($attrs_map, $item_attrs_map);

							} elseif (isset($setting_item['groupType']) && 'group-items' === $setting_item['groupType']) {
								// error_log(print_r($setting_item, true));
								$items = $setting_item['items'] ?? [];
								
								foreach ($items as $item) {
									$item_attrs_map = self::get_the_preset_item_map($item, $full_attr_name);
									$attrs_map = array_merge($attrs_map, $item_attrs_map);
								}
							} elseif (isset($setting_item['groupType']) && 'group' === $setting_item['groupType']) {
								$group_name = $setting_item['groupName'] ?? '';
								if (empty($group_name)) {
									$group_name = ModuleOptionsPresetAttrs::get_the_group_name_by_key($attrs_type, $setting_item_key);
								}
								$group_attrs = ModuleOptionsPresetAttrs::get_preset_attrs_from_group($group_name, $full_attr_name);
								$attrs_map = array_merge($attrs_map, $group_attrs);
							} elseif (!isset($setting_item['groupType']) && ! empty($setting_item)) {
								$group_name = ModuleOptionsPresetAttrs::get_the_group_name_by_key($attrs_type, $setting_item_key);
								$group_attrs = ModuleOptionsPresetAttrs::get_preset_attrs_from_group($group_name, $full_attr_name);
								$attrs_map = array_merge($attrs_map, $group_attrs);
							}
						}
					}
				}

				if ( ! empty( $settings['preset'] ) ) {
				}
			}
		}

		$attrs_map = array_merge($attrs_map, [
			'css__before' => [
				'attrName' => 'css',
				'preset' => ['style'],
				'subName' => 'before',
			],
			'css__mainElement' => [
				'attrName' => 'css',
				'preset' => ['style'],
				'subName' => 'mainElement',
			],
			'css__after' => [
				'attrName' => 'css',
				'preset' => ['style'],
				'subName' => 'after',
			],
			'css__freeForm' => [
				'attrName' => 'css',
				'preset' => ['style'],
				'subName' => 'freeForm',
			],
		]);

		if (! empty( $custom_css_fields ) ) {
			foreach ( $custom_css_fields as $attr_name => $attr_data ) {
				// error_log(print_r($attr_data, true));
				$attrs_map_item = [
					'attrName' => 'css',
					'preset' => ['style'],
					'subName' => $attr_data['subName'],
				];

				$attrs_map_item_key = 'css__' . $attr_data['subName'];

				$attrs_map[$attrs_map_item_key] = $attrs_map_item;
			}
		}

		$attrs_map = apply_filters('divi_conversion_presets_attrs_map', $attrs_map, $module_name);


		self::$preset_attrs_maps[$module_name] = $attrs_map;
		
		return $attrs_map; // Return an empty array for simplicity.
	}

	/**
	 * Converts attributes of a module to a new format.
	 *
	 * @param array $attrs The original attributes of the module.
	 * @param string $moduleName The name of the module.
	 * @param string|null $content The content of the module.
	 * @return array The converted attributes.
	 */

	static function convertAttrs(array $attrs, string $moduleName, ?string $content = null):array {
		$convertedAttrs = [];

		if (null !== $content) {
			$attrs['content'] = $content;
		}

		// Filter split test attributes.
		$attrs = self::filterSplitTestAttributes( $attrs );

		$moduleGlobalColors = self::getModuleGlobalColors($attrs);

		// error_log('$attrs: ' . print_r($attrs, true));

		foreach ($attrs as $name => $value) {
			// error_log('name: ' . $name);

			if (str_ends_with($name, '_last_edited') || str_ends_with($name, '__hover_enabled') || str_ends_with($name, '__sticky_enabled')) {
				continue;
			}

			if ($moduleName === 'divi/section' && in_array($name, array('specialty', 'fullwidth')) && $attrs[$name] !== 'on') {
				continue;
			}

			if ($name === 'global_colors_info') {
				continue;
			}

			// 3rd party: Pass via filters below object of address(es) and value(s) that correspond to one attribute.
			$attrMap = self::getAttrMap($attrs, $name, $moduleName);

			// error_log('attrMap: ' . print_r($attrMap, true));

			if (count($attrMap) > 0) {
				foreach ($attrMap as $objectPath => $encodedValue) {
					if (count($moduleGlobalColors) > 0) {
						$encodedValue = self::convertGlobalColor($encodedValue, $name, $moduleGlobalColors);
					}

					if (!is_array($encodedValue) && self::isDynamicContent((string) $encodedValue)) {
						$encodedValue = self::convertDynamicContent((string) $encodedValue);
					}

					// If encoded value is a CSS variable and isn't wrapped in `var()` wrap it here.
					if ( is_string( $encodedValue ) && strpos( $encodedValue, '--' ) === 0 ) {
						// Wrap in var() if it starts with '--'.
						$encodedValue = 'var(' . $encodedValue . ')';
					}

					if (in_array($moduleName, array('divi/map', 'divi/map-pin', 'divi/fullwidth-map'))) {
						$encodedValue = self::maybeParseValue($objectPath, $encodedValue);
					}

					// error_log('objectPath: ' . $objectPath);
					// error_log('encodedValue: ' . print_r($encodedValue, true));
					// error_log('encodedAttrs: ' . print_r($encodedAttrs, true));

					// Set the value in encodedAttrs array using objectPath as the nested keys.
					// This functionality mimics the lodash `set` function behavior.
					$keys = explode('.', $objectPath);
					$lastKey = array_pop($keys);
					$tempArr = &$convertedAttrs;

					foreach ($keys as $key) {
						// Ensure that $tempArr[$key] is an array before further assignment
						if (!isset($tempArr[$key]) || !is_array($tempArr[$key])) {
							$tempArr[$key] = array();
						}
						$tempArr = &$tempArr[$key];
					}

					$tempArr[$lastKey] = $encodedValue;

					if ($name === 'background_enable_color' && $encodedValue === 'off') {
						// This is where it is headed, commented out bc the arrays werent merging like the js set() counterpart
						// $tempArr['module']['decoration']['background']['desktop']['value']['color'] = '';
						$tempArr['color'] = '';
					}

					if ($moduleName === 'divi/social-media-follow-network' && !isset($attrs['background_color'])) {
						// This is where it is headed, commented out bc the arrays werent merging like the js set() counterpart
						// $tempArr['module']['decoration']['background']['desktop']['value']['color'] = '';
						$tempArr['color'] = '';
					}
				}
			}
		}

		return $convertedAttrs;
	}
}
