<?php
/**
 * Module: ElementStyleAdvancedStyles class
 *
 * @package Builder\Packages\Module
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Element;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Background\BackgroundStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\DisabledOn\DisabledOnStyle;
use ET\Builder\Packages\Module\Options\Dividers\DividersStyle;
use ET\Builder\Packages\Module\Options\Filters\FiltersStyle;
use ET\Builder\Packages\Module\Options\Font\FontStyle;
use ET\Builder\Packages\Module\Options\FontBodyGroup\FontBodyStyle;
use ET\Builder\Packages\Module\Options\FontHeaderGroup\FontHeaderStyle;
use ET\Builder\Packages\Module\Options\Icon\IconStyle;
use ET\Builder\Packages\Module\Options\Overflow\OverflowStyle;
use ET\Builder\Packages\Module\Options\Position\PositionStyle;
use ET\Builder\Packages\Module\Options\Sizing\SizingStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\TextShadow\TextShadowStyle;
use ET\Builder\Packages\Module\Options\Transform\TransformStyle;
use ET\Builder\Packages\Module\Options\ZIndex\ZIndexStyle;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Sizing\SizingStyle as ImageSizingStyle;
use ET\Builder\Packages\ModuleLibrary\Image\Styles\Spacing\SpacingStyle as ImageSpacingStyle;

/**
 * ElementStyleAdvancedStyles class.
 *
 * This class provides the functionality for handling advanced styles.
 *
 * @since ??
 */
class ElementStyleAdvancedStyles {

	/**
	 * Get style component based on style component name.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ElementStyle/advanced-styles/utils/get-style-components getStyleComponents} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param string $component_name Style component name.
	 *
	 * @return string|object Style component. The style component must be a class object
	 *                       with static `style()` method. This follow the same pattern
	 *                       as module options component style.
	 *
	 * @example:
	 * ```php
	 * // Get style component using default arguments.
	 * $component_name = '';
	 * $style_component = ElementStyleAdvancedStyles::get_style_component( $component_name );
	 *
	 * // Get style component with specific component name.
	 * $component_name = 'divi/text';
	 * $style_component = ElementStyleAdvancedStyles::style( $component_name );
	 * ```
	 */
	public static function get_style_component( $component_name ) {
		$style_component_map = self::style_component_map();

		if ( isset( $style_component_map[ $component_name ]['component'] ) ) {
			return $style_component_map[ $component_name ]['component'];
		}

		return '';
	}

	/**
	 * Get style component map.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ElementStyle/advanced-styles/utils/style-component-map styleComponentMap} in
	 * `@divi/module` package.
	 *
	 * There are missing style components in this trait:
	 * - `divi/animation`
	 * VB has `divi/animation` style component, but not in FE.
	 *
	 * - `divi/button`
	 * ButtonStyle is special kind of "options" where the options are actually the entire
	 * element. We need to rethink this element-level module options.
	 *
	 * - `divi/form-field`
	 * FormFieldStyle is special kind of "options" where the options are actually the entire
	 * element. We need to rethink this element-level module options.
	 *
	 * @since ??
	 *
	 * @return array Array of style component map.
	 *
	 * @example:
	 * ```php
	 * // Get style component map.
	 * $style_component_map = ElementStyleAdvancedStyles::style_component_map();
	 * ```
	 */
	public static function style_component_map() {
		$style_component_map = [
			'divi/background'    => [
				'component' => BackgroundStyle::class,
			],
			'divi/border'        => [
				'component' => BorderStyle::class,
			],
			'divi/boxShadow'     => [
				'component' => BoxShadowStyle::class,
			],
			'divi/common'        => [
				'component' => CommonStyle::class,
			],
			'divi/css'           => [
				'component' => CssStyle::class,
			],
			'divi/disabledOn'    => [
				'component' => DisabledOnStyle::class,
			],
			'divi/dividers'      => [
				'component' => DividersStyle::class,
			],
			'divi/filters'       => [
				'component' => FiltersStyle::class,
			],
			'divi/font'          => [
				'component' => FontStyle::class,
			],
			'divi/fontBody'      => [
				'component' => FontBodyStyle::class,
			],
			'divi/fontHeader'    => [
				'component' => FontHeaderStyle::class,
			],
			'divi/icon'          => [
				'component' => IconStyle::class,
			],
			'divi/image-sizing'  => [
				'component' => ImageSizingStyle::class,
			],
			'divi/image-spacing' => [
				'component' => ImageSpacingStyle::class,
			],
			'divi/overFlow'      => [
				'component' => OverflowStyle::class,
			],
			'divi/position'      => [
				'component' => PositionStyle::class,
			],
			'divi/sizing'        => [
				'component' => SizingStyle::class,
			],
			'divi/spacing'       => [
				'component' => SpacingStyle::class,
			],
			'divi/text'          => [
				'component' => TextStyle::class,
			],
			'divi/textShadow'    => [
				'component' => TextShadowStyle::class,
			],
			'divi/transform'     => [
				'component' => TransformStyle::class,
			],
			'divi/zIndex'        => [
				'component' => ZIndexStyle::class,
			],
		];

		/**
		 * Filter map of style components based on the registered style components.
		 *
		 * @since ??
		 *
		 * @param array $style_component_map Array of style component map.
		 */
		return apply_filters( 'divi_module_options_element_style_components', $style_component_map );
	}

	/**
	 * Get advanced styles style declaration.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ElementStyle/advanced-styles AdvancedStyles} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string      $selector             Optional. The CSS selector. Default `''`.
	 *     @type array       $advanced_styles      Optional. An array of module advanced styles. Default `[]`.
	 *     @type string|null $orderClass           Optional. The selector class name.
	 *     @type bool        $isInsideStickyModule Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type string      $attrs_json           Optional. The JSON string of module attribute data, use to improve performance.
	 *     @type string      $returnType           Optional. This is the type of value that the function will return.
	 *                                             Can be either `string` or `array`. Default `array`.
	 * }
	 *
	 * @return string|array The advanced styles style declaration.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = ElementStyleAdvancedStyles::style( $args );
	 *
	 * // Apply style with specific selector and advanced styles.
	 * $args = [
	 *     'selector' => '.element1',
	 *     'advanced_styles' => [
	 *         [
	 *             'componentName' => 'divi/text',
	 *             'props' => [
	 *                 'attr' => [
	 *                     'text' => [
	 *                         'desktop' => [
	 *                             'value' => [
	 *                                 'orientation' => 'left',
	 *                             ],
	 *                         ],
	 *                     ],
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 * ];
	 * $style = ElementStyleAdvancedStyles::style( $args );
	 * // Result: ".element1 {text-align: left;}"
	 * ```
	 */
	public static function style( $args ) {
		$selector        = $args['selector'] ?? '';
		$advanced_styles = $args['advancedStyles'] ?? [];
		$order_class     = $args['orderClass'] ?? null;
		$attrs_json      = $args['attrs_json'] ?? null;
		$return_type     = $args['returnType'] ?? 'array';
		$return_as_array = 'array' === $return_type;
		$style_output    = $return_as_array ? [] : '';
		$at_rules        = $args['atRules'] ?? '';

		$is_inside_sticky_module = $args['isInsideStickyModule'] ?? false;

		if ( ! is_array( $advanced_styles ) || empty( $advanced_styles ) ) {
			return $style_output;
		}

		foreach ( $advanced_styles as $advanced_style ) {
			$component_name  = $advanced_style['componentName'] ?? null;
			$component_props = $advanced_style['props'] ?? null;

			if ( empty( $component_name ) || empty( $component_props ) ) {
				continue;
			}

			$style_component = self::get_style_component( $component_name );

			if ( ! isset( $component_props['selector'] ) ) {
				$component_props['selector'] = $selector;
			}

			// Set the orderClass in the $component_props, when not provided.
			// Note: usually for a module ElementStyleAdvancedStyles declaration, it doesn't need to pass the
			// orderClass as it's getting passed down from the ModuleElements via the ElementStyle component.
			if ( ! isset( $component_props['orderClass'] ) ) {
				$component_props['orderClass'] = $order_class;
			}

			if ( ! isset( $component_props['isInsideStickyModule'] ) ) {
				$component_props['isInsideStickyModule'] = $is_inside_sticky_module;
			}

			if ( ! isset( $component_props['attrs_json'] ) ) {
				$component_props['attrs_json'] = $attrs_json;
			}

			if ( ! isset( $component_props['returnType'] ) ) {
				$component_props['returnType'] = $return_type;
			}

			if ( ! isset( $component_props['atRules'] ) ) {
				$component_props['atRules'] = $at_rules;
			}

			// The style component must be a class object with static `style()` method. This
			// follow the same pattern as module options component style.
			if ( is_callable( [ $style_component, 'style' ] ) ) {
				$component_output = $style_component::style( $component_props );

				if ( $component_output && $return_as_array ) {
					array_push( $style_output, ...$component_output );
				} elseif ( $component_output ) {
					$style_output .= $component_output;
				}
			}
		}

		return $style_output;
	}
}
