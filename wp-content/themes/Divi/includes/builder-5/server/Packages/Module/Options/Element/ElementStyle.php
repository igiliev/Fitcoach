<?php
/**
 * Module: ElementStyle class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Element;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Customizer\Customizer;
use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ET\Builder\Packages\Module\Options\Background\BackgroundStyle;
use ET\Builder\Packages\Module\Options\Border\BorderStyle;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowStyle;
use ET\Builder\Packages\Module\Options\Button\ButtonStyle;
use ET\Builder\Packages\Module\Options\DisabledOn\DisabledOnStyle;
use ET\Builder\Packages\Module\Options\Filters\FiltersStyle;
use ET\Builder\Packages\Module\Options\FontBodyGroup\FontBodyStyle;
use ET\Builder\Packages\Module\Options\FontHeaderGroup\FontHeaderStyle;
use ET\Builder\Packages\Module\Options\Font\FontStyle;
use ET\Builder\Packages\Module\Options\Icon\IconStyle;
use ET\Builder\Packages\Module\Options\Layout\LayoutStyle;
use ET\Builder\Packages\Module\Options\Order\OrderStyle;
use ET\Builder\Packages\Module\Options\Overflow\OverflowStyle;
use ET\Builder\Packages\Module\Options\Position\PositionStyle;
use ET\Builder\Packages\Module\Options\Sizing\SizingStyle;
use ET\Builder\Packages\Module\Options\Spacing\SpacingStyle;
use ET\Builder\Packages\Module\Options\Transform\TransformStyle;
use ET\Builder\Packages\Module\Options\Transition\TransitionStyle;
use ET\Builder\Packages\Module\Options\ZIndex\ZIndexStyle;
use ET\Builder\Packages\Module\Options\Element\ElementFilterFunctions;
use ET\Builder\Packages\Module\Options\Element\ElementStyleAdvancedStyles;

/**
 * ElementStyle class.
 *
 * This class provides the functionality for handling element styles.
 *
 * @since ??
 */
class ElementStyle {

	/**
	 * Get element style component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ElementStyle ElementStyle} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $selector                  Optional. The CSS selector. Default `null`.
	 *     @type array         $attrs                     Optional. An array of module attribute data. Default `[]`.
	 *     @type array         $defaultPrintedStyleAttrs  Optional. An array of default printed style attribute data. Default `[]`.
	 *     @type callable      $attrsFilter               Optional. A callback function to filter the attributes. Default `null`.
	 *     @type string|null   $orderClass                Optional. The selector class name.
	 *     @type string        $type                      Optional. Element type. This might use built in callback for attributes.
	 *                                                    Default `module`.
	 *     @type bool          $isInsideStickyModule      Optional. Whether the module is inside a sticky module or not. Default `false`.
	 *     @type array         $background                Optional. An array of background style data. Default `[]`.
	 *     @type array         $font                      Optional. An array of font style data. Default `[]`.
	 *     @type array         $icon                      Optional. An array of icon style data. Default `[]`.
	 *     @type array         $bodyFont                  Optional. An array of bodyFont style data. Default `[]`.
	 *     @type array         $spacing                   Optional. An array of spacing style data. Default `[]`.
	 *     @type array         $sizing                    Optional. An array of sizing style data. Default `[]`.
	 *     @type array         $border                    Optional. An array of border style data. Default `[]`.
	 *     @type array         $boxShadow                 Optional. An array of boxShadow style data. Default `[]`.
	 *     @type array         $filters                   Optional. An array of filter style data. Default `[]`.
	 *     @type array         $transform                 Optional. An array of transform style data. Default `[]`.
	 *     @type array         $transition                Optional. An array of transition style data. Default `[]`.
	 *     @type array         $disabledOn                Optional. An array of disabledOn style data. Default `[]`.
	 *     @type array         $overflow                  Optional. An array of overflow style data. Default `[]`.
	 *     @type array         $position                  Optional. An array of position style data. Default `[]`.
	 *     @type array         $zIndex                    Optional. An array of zIndex style data. Default `[]`.
	 *     @type array         $advanced_styles           Optional. An array of module advanced styles. Default `[]`.
	 *     @type array         $button                    Optional. An array of button style data. Default `[]`.
	 *     @type array         $order                     Optional. An array of order style data. default '[]'.
	 *     @type bool          $asStyle                   Optional. Whether to wrap the style declaration with style tag or not.
	 *                                                    Default `true`
	 *     @type string        $returnType                Optional. This is the type of value that the function will return.
	 *                                                    Can be either `string` or `array`. Default `array`.
	 *     @type bool          $isParentLayoutFlex        Optional. Whether the module is inside a parent layout flex or not. Default `false`.
	 *     @type array         $layout                    Optional. An array of layout style data. Default `[]`.
	 * }
	 * }
	 *
	 * @return string|array The element style component.
	 *
	 * @example:
	 * ```php
	 * // Apply style using default arguments.
	 * $args = [];
	 * $style = ElementStyle::style( $args );
	 *
	 * // Apply style with specific selectors and properties.
	 * $args = [
	 *     'selectors' => [
	 *         '.element1',
	 *         '.element2',
	 *     ],
	 *     'propertySelectors' => [
	 *         '.element1 .property1',
	 *         '.element2 .property2',
	 *     ]
	 * ];
	 * $style = ElementStyle::style( $args );
	 * ```
	 */
	public static function style( $args ) {
		$args = array_replace_recursive(
			[
				'selector'                 => null,
				'attrs'                    => [],
				'defaultPrintedStyleAttrs' => [],
				'attrsFilter'              => null,
				'type'                     => 'module',
				'isInsideStickyModule'     => false,
				'isParentLayoutFlex'       => false,
				'background'               => [],
				'font'                     => [],
				'icon'                     => [],
				'bodyFont'                 => [],
				'headingFont'              => [],
				'spacing'                  => [],
				'sizing'                   => [],
				'border'                   => [],
				'boxShadow'                => [],
				'filters'                  => [],
				'transform'                => [],
				'transition'               => [],
				'disabledOn'               => [],
				'order'                    => [],
				'overflow'                 => [],
				'position'                 => [],
				'zIndex'                   => [],
				'button'                   => [],
				'layout'                   => [],
				'asStyle'                  => true,
				'advancedStyles'           => [],
				'orderClass'               => null,
				'returnType'               => 'array',
				'isParentLayoutFlex'       => false,
				'atRules'                  => '',
			],
			$args
		);

		// Assign attributes.
		$selector                    = $args['selector'];
		$attrs                       = $args['attrs'];
		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'];
		$is_inside_sticky_module     = $args['isInsideStickyModule'];
		$is_parent_layout_flex       = $args['isParentLayoutFlex'];
		$background                  = $args['background'];
		$font                        = $args['font'];
		$icon                        = $args['icon'];
		$body_font                   = $args['bodyFont'];
		$heading_font                = $args['headingFont'];
		$spacing                     = $args['spacing'];
		$sizing                      = $args['sizing'];
		$border                      = $args['border'];
		$box_shadow                  = $args['boxShadow'];
		$filters                     = $args['filters'];
		$transform                   = $args['transform'];
		$transition                  = $args['transition'];
		$disabled_on                 = $args['disabledOn'];
		$order                       = $args['order'];
		$overflow                    = $args['overflow'];
		$position                    = $args['position'];
		$z_index                     = $args['zIndex'];
		$button                      = $args['button'];
		$layout                      = $args['layout'];
		$as_style                    = $args['asStyle'];
		$advanced_styles             = $args['advancedStyles'];
		$order_class                 = $args['orderClass'];
		$return_as_array             = 'array' === $args['returnType'];
		$is_parent_layout_flex       = $args['isParentLayoutFlex'];
		$at_rules                    = $args['atRules'];

		// The order of importance for attribute filtering functions:
		// 1. props.attrsFilter (custom filter).
		// 2. filterFunctionMap (filter function based on type).
		// 3. null (no filter).
		$attrs_filter_function = is_callable( $args['attrsFilter'] )
			? $args['attrsFilter']
			: ElementFilterFunctions::$filter_function_map[ $args['type'] ] ?? null;

		// Filter the attributes if needed.
		$element_attrs = is_callable( $attrs_filter_function ) ? $attrs_filter_function( $attrs ) : $attrs;

		// Get relevant customizer options for this element type.
		$customizer_setting_for_element_style = Customizer::get_customizer_setting_for_element_style(
			[
				'elementType' => $args['type'],
			]
		);

		// If customizer setting for current element exist, merge it into `defaultPrintedStyleAttrs`. Customizer style
		// rendering is treated similarly as static css. It is expected to be already exist on the page, its value won't
		// be re-printed by visual builder, but in some occassion renderer component needs to know its value to prevent
		// unnecessary css style being rendered and overwrites customizer setting (eg. border-color when only border-width
		// attribute is defined).
		if ( ! empty( $customizer_setting_for_element_style ) ) {
			$default_printed_style_attrs = array_merge(
				$default_printed_style_attrs,
				$customizer_setting_for_element_style
			);
		}

		// JSON encode the attributes array for faster search.
		$attrs_json = wp_json_encode( $element_attrs );

		// Prepare element styles.
		$element = $return_as_array ? [] : '';

		// Prepare transition `attrs` and `props` by collecting the style data for each
		// element styles with rendered styles. We keep `attrs` and `props` separated to
		// make it easier to process transition styles later. This also aligns with
		// ElementStyle::style_declaration() function that accepts `attrs` and style data
		// (border, boxShadow, etc.) as separate parameters. We're not going to collect all
		// element styles data, only the ones that have rendered style and have supported
		// transition CSS properties for performance reasons.
		$transition_data = [
			'attrs' => [],
			'props' => [],
		];

		$element_background = ! empty( $element_attrs['background'] ) ? BackgroundStyle::style(
			[
				'selector'                => $background['selector'] ?? $selector,
				'selectors'               => $background['selectors'] ?? [],
				'propertySelectors'       => $background['propertySelectors'] ?? [],
				'selectorFunction'        => $background['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['background'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['background'] ?? $background['defaultPrintedStyleAttr'] ?? [],
				'important'               => $background['important'] ?? false,
				'featureSelectors'        => $background['featureSelectors'] ?? null,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
				'atRules'                 => $background['atRules'] ?? $at_rules,

			]
		) : null;

		if ( $element_background ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_background );
			} else {
				$element .= $element_background;
			}

			$transition_data['attrs']['background'] = $element_attrs['background'];
			$transition_data['props']['background'] = $background;
		}

		$element_font = ! empty( $element_attrs['font'] ) ? FontStyle::style(
			[
				'selector'                => $font['selector'] ?? $selector,
				'selectors'               => $font['selectors'] ?? [],
				'propertySelectors'       => $font['propertySelectors'] ?? [],
				'selectorFunction'        => $font['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['font'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['font'] ?? $font['defaultPrintedStyleAttr'] ?? [],
				'important'               => $font['important'] ?? false,
				'headingLevel'            => $font['headingLevel'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_font ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_font );
			} else {
				$element .= $element_font;
			}

			$transition_data['attrs']['font'] = $element_attrs['font'];
			$transition_data['props']['font'] = $font;
		}

		$element_icon = ! empty( $element_attrs['icon'] ) ? IconStyle::style(
			[
				'selector'                => $icon['selector'] ?? $selector,
				'selectors'               => $icon['selectors'] ?? [],
				'propertySelectors'       => $icon['propertySelectors'] ?? [],
				'selectorFunction'        => $icon['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['icon'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['icon'] ?? $icon['defaultPrintedStyleAttr'] ?? [],
				'important'               => $icon['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
				'atRules'                 => $icon['atRules'] ?? $at_rules,
			]
		) : null;

		if ( $element_icon ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_icon );
			} else {
				$element .= $element_icon;
			}

			$transition_data['attrs']['icon'] = $element_attrs['icon'];
			$transition_data['props']['icon'] = $icon;
		}

		$element_font_body = ! empty( $element_attrs['bodyFont'] ) ? FontBodyStyle::font_body_style(
			[
				'selector'                => $body_font['selector'] ?? $selector,
				'selectors'               => $body_font['selectors'] ?? [],
				'propertySelectors'       => $body_font['propertySelectors'] ?? [],
				'selectorFunction'        => $body_font['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['bodyFont'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['bodyFont'] ?? $body_font['defaultPrintedStyleAttr'] ?? [],
				'important'               => $body_font['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_font_body ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_font_body );
			} else {
				$element .= $element_font_body;
			}

			$transition_data['attrs']['bodyFont'] = $element_attrs['bodyFont'];
			$transition_data['props']['bodyFont'] = $body_font;
		}

		$element_font_heading = ! empty( $element_attrs['headingFont'] ) ? FontHeaderStyle::style(
			[
				'selector'                => $heading_font['selector'] ?? $selector,
				'selectors'               => $heading_font['selectors'] ?? [],
				'propertySelectors'       => $heading_font['propertySelectors'] ?? [],
				'selectorFunction'        => $heading_font['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['headingFont'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['headingFont'] ?? $heading_font['defaultPrintedStyleAttr'] ?? [],
				'important'               => $heading_font['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_font_heading ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_font_heading );
			} else {
				$element .= $element_font_heading;
			}

			$transition_data['attrs']['headingFont'] = $element_attrs['headingFont'];
			$transition_data['props']['headingFont'] = $heading_font;
		}

		$element_spacing = ! empty( $element_attrs['spacing'] ) ? SpacingStyle::style(
			[
				'selector'                => $spacing['selector'] ?? $selector,
				'selectors'               => $spacing['selectors'] ?? [],
				'propertySelectors'       => $spacing['propertySelectors'] ?? [],
				'selectorFunction'        => $spacing['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['spacing'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['spacing'] ?? $spacing['defaultPrintedStyleAttr'] ?? [],
				'important'               => $spacing['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_spacing ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_spacing );
			} else {
				$element .= $element_spacing;
			}

			$transition_data['attrs']['spacing'] = $element_attrs['spacing'];
			$transition_data['props']['spacing'] = $spacing;
		}

		$element_sizing = ! empty( $element_attrs['sizing'] ) ? SizingStyle::style(
			[
				'selector'                => $sizing['selector'] ?? $selector,
				'selectors'               => $sizing['selectors'] ?? [],
				'propertySelectors'       => $sizing['propertySelectors'] ?? [],
				'selectorFunction'        => $sizing['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['sizing'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['sizing'] ?? $sizing['defaultPrintedStyleAttr'] ?? [],
				'important'               => $sizing['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'isParentLayoutFlex'      => $is_parent_layout_flex,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_sizing ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_sizing );
			} else {
				$element .= $element_sizing;
			}

			$transition_data['attrs']['sizing'] = $element_attrs['sizing'];
			$transition_data['props']['sizing'] = $sizing;
		}

		$element_border = ! empty( $element_attrs['border'] ) ? BorderStyle::style(
			[
				'selector'                => $border['selector'] ?? $selector,
				'selectors'               => $border['selectors'] ?? [],
				'propertySelectors'       => $border['propertySelectors'] ?? [],
				'selectorFunction'        => $border['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['border'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['border'] ?? $border['defaultPrintedStyleAttr'] ?? [],
				'important'               => $border['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_border ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_border );
			} else {
				$element .= $element_border;
			}

			$transition_data['attrs']['border'] = $element_attrs['border'];
			$transition_data['props']['border'] = $border;
		}

		$element_box_shadow = ! empty( $element_attrs['boxShadow'] ) ? BoxShadowStyle::style(
			[
				'selector'                => $box_shadow['selector'] ?? $selector,
				'selectors'               => $box_shadow['selectors'] ?? [],
				'propertySelectors'       => $box_shadow['propertySelectors'] ?? [],
				'selectorFunction'        => $box_shadow['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['boxShadow'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['boxShadow'] ?? $box_shadow['defaultPrintedStyleAttr'] ?? [],
				'important'               => $box_shadow['important'] ?? false,
				'useOverlay'              => $box_shadow['useOverlay'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_box_shadow ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_box_shadow );
			} else {
				$element .= $element_box_shadow;
			}

			$transition_data['attrs']['boxShadow'] = $element_attrs['boxShadow'];
			$transition_data['props']['boxShadow'] = $box_shadow;
		}

		$element_filter = ! empty( $element_attrs['filters'] ) ? FiltersStyle::style(
			[
				'selector'                => $filters['selector'] ?? $selector,
				'selectors'               => $filters['selectors'] ?? [],
				'propertySelectors'       => $filters['propertySelectors'] ?? [],
				'selectorFunction'        => $filters['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['filters'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['filters'] ?? $filters['defaultPrintedStyleAttr'] ?? [],
				'important'               => $filters['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_filter ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_filter );
			} else {
				$element .= $element_filter;
			}

			$transition_data['attrs']['filters'] = $element_attrs['filters'];
			$transition_data['props']['filters'] = $filters;
		}

		$element_disabled_on = ! empty( $element_attrs['disabledOn'] ) ? DisabledOnStyle::style(
			[
				'selector'                 => $disabled_on['selector'] ?? $selector,
				'selectors'                => $disabled_on['selectors'] ?? [],
				'propertySelectors'        => $disabled_on['propertySelectors'] ?? [],
				'selectorFunction'         => $disabled_on['selectorFunction'] ?? null,
				'attrs_json'               => $attrs_json,
				'attr'                     => $element_attrs['disabledOn'],
				'defaultPrintedStyleAttr'  => $default_printed_style_attrs['disabledOn'] ?? $disabled_on['defaultPrintedStyleAttr'] ?? [],
				'disabledModuleVisibility' => $disabled_on['disabledModuleVisibility'] ?? null,
				'orderClass'               => $order_class,
				'isInsideStickyModule'     => $is_inside_sticky_module,
				'returnType'               => $args['returnType'],
			]
		) : null;

		if ( $element_disabled_on && $return_as_array ) {
			array_push( $element, ...$element_disabled_on );
		} elseif ( $element_disabled_on ) {
			$element .= $element_disabled_on;
		}

		$element_overflow = ! empty( $element_attrs['overflow'] ) ? OverflowStyle::style(
			[
				'selector'                => $overflow['selector'] ?? $selector,
				'selectors'               => $overflow['selectors'] ?? [],
				'propertySelectors'       => $overflow['propertySelectors'] ?? [],
				'selectorFunction'        => $overflow['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['overflow'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['overflow'] ?? $overflow['defaultPrintedStyleAttr'] ?? [],
				'important'               => $overflow['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_overflow && $return_as_array ) {
			array_push( $element, ...$element_overflow );
		} elseif ( $element_overflow ) {
			$element .= $element_overflow;
		}

		$element_position = ! empty( $element_attrs['position'] ) ? PositionStyle::style(
			[
				'selector'                => $position['selector'] ?? $selector,
				'selectors'               => $position['selectors'] ?? [],
				'propertySelectors'       => $position['propertySelectors'] ?? [],
				'selectorFunction'        => $position['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['position'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['position'] ?? $position['defaultPrintedStyleAttr'] ?? [],
				'important'               => $position['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_position ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_position );
			} else {
				$element .= $element_position;
			}

			$transition_data['attrs']['position'] = $element_attrs['position'];
			$transition_data['props']['position'] = $position;
		}

		$element_transform = ! empty( $element_attrs['transform'] ) ? TransformStyle::style(
			[
				'selector'                => $transform['selector'] ?? $selector,
				'selectors'               => $transform['selectors'] ?? [],
				'propertySelectors'       => $transform['propertySelectors'] ?? [],
				'selectorFunction'        => $transform['selectorFunction'] ?? null,
				'attr'                    => $element_attrs['transform'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['transform'] ?? $transform['defaultPrintedStyleAttr'] ?? [],
				'important'               => $transform['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_transform ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_transform );
			} else {
				$element .= $element_transform;
			}

			$transition_data['attrs']['transform'] = $element_attrs['transform'];
			$transition_data['props']['transform'] = $transform;
		}

		$element_z_index = ! empty( $element_attrs['zIndex'] ) ? ZIndexStyle::style(
			[
				'selector'                => $z_index['selector'] ?? $selector,
				'selectors'               => $z_index['selectors'] ?? [],
				'selectorFunction'        => $z_index['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['zIndex'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['zIndex'] ?? $z_index['defaultPrintedStyleAttr'] ?? [],
				'important'               => $z_index['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_z_index ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_z_index );
			} else {
				$element .= $element_z_index;
			}

			$transition_data['attrs']['zIndex'] = $element_attrs['zIndex'];
			$transition_data['props']['zIndex'] = $z_index;
		}

		$is_flexbox_enabled = et_get_experiment_flag( 'flexbox' );

		if ( $is_flexbox_enabled && $is_parent_layout_flex ) {

			$element_order = ! empty( $element_attrs['order'] ) ? OrderStyle::style(
				[
					'selector'                => $order['selector'] ?? $selector,
					'selectors'               => $order['selectors'] ?? [],
					'propertySelectors'       => $order['propertySelectors'] ?? [],
					'selectorFunction'        => $order['selectorFunction'] ?? null,
					'attrs_json'              => $attrs_json,
					'attr'                    => $element_attrs['order'],
					'defaultPrintedStyleAttr' => $default_printed_style_attrs['order'] ?? $order['defaultPrintedStyleAttr'] ?? [],
					'important'               => $order['important'] ?? false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			) : null;

			if ( $element_order ) {
				if ( $return_as_array ) {
					array_push( $element, ...$element_order );
				} else {
					$element .= $element_order;
				}
			}
		}

		$element_button = ! empty( $element_attrs['button'] ) ? ButtonStyle::style(
			[
				'selector'                => $button['selector'] ?? $selector,
				'selectors'               => $button['selectors'] ?? [],
				'propertySelectors'       => $button['propertySelectors'] ?? [],
				'selectorFunction'        => $button['selectorFunction'] ?? null,
				'attrs_json'              => $attrs_json,
				'attr'                    => $element_attrs['button'],
				'defaultPrintedStyleAttr' => $default_printed_style_attrs['button'] ?? $button['defaultPrintedStyleAttr'] ?? [],
				'affectingAttrs'          => $button['affectingAttrs'] ?? [
					'spacing' => $element_attrs['spacing'] ?? [],
				],
				'important'               => $button['important'] ?? false,
				'orderClass'              => $order_class,
				'isInsideStickyModule'    => $is_inside_sticky_module,
				'returnType'              => $args['returnType'],
			]
		) : null;

		if ( $element_button ) {
			if ( $return_as_array ) {
				array_push( $element, ...$element_button );
			} else {
				$element .= $element_button;
			}

			$transition_data['attrs']['button'] = $element_attrs['button'];
			$transition_data['props']['button'] = $button;
		}

		$is_flexbox_enabled = et_get_experiment_flag( 'flexbox' );

		if ( $is_flexbox_enabled ) {
			$element_layout = ! empty( $element_attrs['layout'] ) ? LayoutStyle::style(
				[
					'selector'                => $layout['selector'] ?? $selector,
					'selectors'               => $layout['selectors'] ?? [],
					'propertySelectors'       => $layout['propertySelectors'] ?? [],
					'selectorFunction'        => $layout['selectorFunction'] ?? null,
					'attrs_json'              => $attrs_json,
					'attr'                    => $element_attrs['layout'],
					'defaultPrintedStyleAttr' => $default_printed_style_attrs['layout'] ?? $layout['defaultPrintedStyleAttr'] ?? [],
					'important'               => $layout['important'] ?? false,
					'orderClass'              => $order_class,
					'isInsideStickyModule'    => $is_inside_sticky_module,
					'returnType'              => $args['returnType'],
				]
			) : null;

			if ( $element_layout ) {
				if ( $return_as_array ) {
					array_push( $element, ...$element_layout );
				} else {
					$element .= $element_layout;
				}
			}
		}

		// Process transition styles only if one of `attrs` or `advanced_styles` is not empty and has hover/sticky state.
		// In VB, there is no check like this since the `attrs` are always set.
		if ( ! empty( $transition_data['attrs'] ) || ! empty( $advanced_styles ) ) {
			// TODO: fix(D5, Advanced Styles Transition) Revisit `advanced_styles` check once we start working on selector
			// conflicts issue in advanced styles. There is a chance we can optimize this check by moving it to the Transition
			// Style component itself to cover Element Style usage and Transition Style individually.
			// @see https://github.com/elegantthemes/Divi/issues/39774
			// JSON encode the attributes array for faster search using strpos and avoid any loops.
			$transition_attrs_json = ! empty( $transition_data['attrs'] ) ? wp_json_encode( $transition_data['attrs'] ) : '';
			$advanced_styles_json  = ! empty( $advanced_styles ) ? wp_json_encode( $advanced_styles ) : '';

			// Check if transition module attribute and advanced styles have hover/sticky state.
			$has_hover  = strpos( $transition_attrs_json, 'hover' ) || strpos( $advanced_styles_json, 'hover' );
			$has_sticky = strpos( $transition_attrs_json, 'sticky' ) || strpos( $advanced_styles_json, 'sticky' );

			$needs_transition = $has_hover || $has_sticky;

			// If the element needs transition, we need to generate transition attribute for every element.
			if ( $needs_transition ) {
				// Automatically generate transition attribute for every element based on module's transition attribute.
				// Element does not and will not have its own transition options so module's transition attribute
				// needs to be passed into other elements' decoration attribute.
				$element_transition = TransitionStyle::style(
					[
						'selector'                => $transition['selector'] ?? $selector,
						'selectors'               => $transition['selectors'] ?? [],
						'propertySelectors'       => $transition['propertySelectors'] ?? [],
						'selectorFunction'        => $transition['selectorFunction'] ?? null,
						'attrs_json'              => $attrs_json,
						'attrs'                   => $attrs,
						'attr'                    => $element_attrs['transition'] ?? [],
						'defaultPrintedStyleAttr' => $default_printed_style_attrs['transition'] ?? $transition['defaultPrintedStyleAttr'] ?? [],
						'important'               => $transition['important'] ?? false,
						'advancedStyles'          => $advanced_styles ?? [],
						'transitionData'          => $transition_data,
						'orderClass'              => $order_class,
						'isInsideStickyModule'    => $is_inside_sticky_module,
						'returnType'              => $args['returnType'],
					]
				);

				if ( $element_transition && $return_as_array ) {
					array_push( $element, ...$element_transition );
				} elseif ( $element_transition ) {
					$element .= $element_transition;
				}
			}
		}

		// Advanced styles.
		if ( ! empty( $advanced_styles ) ) {
			$element_advanced_styles = ElementStyleAdvancedStyles::style(
				[
					'selector'             => $selector,
					'advancedStyles'       => $advanced_styles,
					'orderClass'           => $order_class,
					'isInsideStickyModule' => $is_inside_sticky_module,
					'attrs_json'           => $attrs_json,
					'returnType'           => $args['returnType'],
					'atRules'              => $at_rules,

				]
			);

			if ( $element_advanced_styles ) {
				if ( $return_as_array ) {
					array_push( $element, ...$element_advanced_styles );
				} else {
					$element .= $element_advanced_styles;
				}

				// When the element attrs are empty because there is no modified settings, even
				// though the advanced styles parameter is not empty, the style wrapper will
				// return empty string and all the advanced styles won't be rendered. To fix
				// this issue, we need to set the advanced styles as element attr so that style
				// wrapper can render the children element from advanced styles.
				$element_attrs['advancedStyles'] = $advanced_styles;
			}
		}

		return Utils::style_wrapper(
			[
				'attr'     => $element_attrs,
				'asStyle'  => $as_style,
				'children' => $element,
			]
		);
	}
}
