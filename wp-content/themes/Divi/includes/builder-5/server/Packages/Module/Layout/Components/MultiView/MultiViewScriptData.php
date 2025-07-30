<?php
/**
 * Module: MultiViewScriptData class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView;

use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: MultiViewScriptData class.
 *
 * @since ??
 */
class MultiViewScriptData {

	use MultiViewScriptDataTraits\SetClassNameTrait;
	use MultiViewScriptDataTraits\SetTrait;

	/**
	 * Set multi view data of HTML element attributes.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $id            The Module unique ID.
	 *     @type string   $name          The Module name.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 *     @type array    $data          A key-value pair array where the key is the attribute name and the
	 *                                   value is a formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 3 keys:
	 *                                       `attrName`, `breakpoint` and `state`.
	 *                                          - @type string $breakpoint Current breakpoint that being processed.
	 *                                                                     Can be `desktop`, `tablet`, or `phone`.
	 *                                          - @type string $state      Current state that being processed.
	 *                                                                     Can be `value` or `hover`.
	 *                                          - @type string $attrName   Current attribute name that being processed.
	 *                                     - The function must return a `string`.
	 *     @type callable $sanitizers    Optional. A key-value pair array where the key is the attribute name and the
	 *                                   value is function to sanitize/escape the attribute value.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 1 argument.
	 *                                     - The function 1st argument is the original attribute value that being sanitized.
	 *                                     - The function must return a `string`.
	 *     @type string   $tag           Optional. The element tag where the attributes will be used. Default `div`.
	 *     @type int|null $storeInstance The ID of instance where this block stored in BlockParserStore class.
	 * }
	 *
	 * @return array
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::set_attrs([
	 *     'id'            => 'divi/blurb-0',
	 *     'name'          => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'src' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/desktop.jpg',
	 *                     'title' => 'My Desktop Image',
	 *                 ],
	 *                 'hover' => [
	 *                     'url'   => 'http://example.com/hover.jpg',
	 *                     'title' => 'My Hover Image',
	 *                 ],
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/tablet.jpg',
	 *                     'title' => 'My Tablet Image',
	 *                 ],
	 *             ],
	 *         ],
	 *         'alt' => [
	 *             'desktop' => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/desktop.jpg',
	 *                     'title' => 'My Desktop Image',
	 *                 ],
	 *                 'hover' => [
	 *                     'url'   => 'http://example.com/hover.jpg',
	 *                     'title' => 'My Hover Image',
	 *                 ],
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => [
	 *                     'url'   => 'http://example.com/tablet.jpg',
	 *                     'title' => 'My Tablet Image',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     'sanitizers'    => [
	 *         'src' => 'esc_url',
	 *         'alt' => 'esc_attr',
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'alt' === $resolver_args['attrName'] ) {
	 *             return $value['title'] ?? '';
	 *         }
	 *
	 *         return $value['url'] ?? '';
	 *     },
	 *     'tag'           => 'img',
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'action'        => 'setAttrs',
	 *     'moduleId'      => 'divi/blurb-0',
	 *     'moduleName'    => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'desktop'        => [
	 *             'src' => 'http://example.com/desktop.jpg',
	 *             'alt' => 'My Desktop Image',
	 *         ],
	 *         'desktop--hover' => [
	 *             'src' => 'http://example.com/hover.jpg',
	 *             'alt' => 'My Hover Image',
	 *         ],
	 *         'tablet'         => [
	 *             'src' => 'http://example.com/tablet.jpg',
	 *             'alt' => 'My Tablet Image',
	 *         ],
	 *     ],
	 * ]
	 * ```
	 */
	public static function set_attrs( array $args ): array {
		$data = MultiViewUtils::populate_data_attrs( $args );

		// Add script data if has non desktop value.
		if ( 1 < count( $data ) ) {
			$id             = $args['id'] ?? '';
			$name           = $args['name'] ?? '';
			$selector       = $args['selector'] ?? '';
			$hover_selector = $args['hoverSelector'] ?? '{{selector}}';
			$store_instance = $args['storeInstance'] ?? null;
			$switch_on_load = $args['switchOnLoad'] ?? true;

			if ( $selector ) {
				$selector = MultiViewUtils::convert_selector( $selector, $id, $store_instance );
			}

			if ( $hover_selector ) {
				$hover_selector = MultiViewUtils::convert_selector( $hover_selector, $id, $store_instance );
			}

			ScriptData::add_data_item(
				[
					'data_name' => 'multi_view',
					'data_item' => [
						'action'        => 'setAttrs',
						'moduleId'      => $id,
						'moduleName'    => $name,
						'selector'      => $selector,
						'hoverSelector' => $hover_selector,
						'data'          => $data,
						'switchOnLoad'  => $switch_on_load,
					],
				]
			);
		}

		return $data;
	}

	/**
	 * Populate multi view data to set HTML element inner content.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $id            The Module unique ID.
	 *     @type string   $name          The Module name.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 2 keys `breakpoint` and `state`.
	 *                                          - @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          - @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                     - The function must return a `string`.
	 *     @type callable $sanitizer     Optional. A function that will be invoked to sanitize/escape the value. Default `esc_html`.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 1 argument.
	 *                                     - The function 1st argument is the original attribute value that being sanitized.
	 *                                     - The function must return a `string`.
	 *     @type int|null  $storeInstance The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::set_content([
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'id'            => 'divi/blurb-0',
	 *     'name'          => 'divi/blurb',
	 *     'data'          => [
	 *         'desktop' => [
	 *             'value' => '<p>Foo</p>',
	 *             'hover' => '<p>Bar</p>',
	 *         ],
	 *         'tablet' => [
	 *             'value' => '<p>Baz</p>',
	 *         ],
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'phone' === $resolver_args['breakpoint'] ) {
	 *             return $value . '<p>Custom Baz</p>';
	 *         }
	 *
	 *         return $value;
	 *     },
	 *     'sanitizer'     => 'et_core_esc_previously',
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'action'        => 'setContent',
	 *     'moduleId'      => 'divi/blurb-0',
	 *     'moduleName'    => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'desktop'        => '<p>Foo</p>',
	 *         'desktop--hover' => '<p>Bar</p>',
	 *         'tablet'         => '<p>Baz</p>',
	 *         'phone'          => '<p>Baz</p><p>Custom Baz</p>',
	 *     ],
	 * ]
	 * ```
	 */
	public static function set_content( array $args ): array {
		$data = MultiViewUtils::populate_data_content( $args );

		// Add script data if has non desktop value.
		if ( 1 < count( $data ) ) {
			$id             = $args['id'] ?? '';
			$name           = $args['name'] ?? '';
			$selector       = $args['selector'] ?? '';
			$hover_selector = $args['hoverSelector'] ?? '{{selector}}';
			$store_instance = $args['storeInstance'] ?? null;
			$switch_on_load = $args['switchOnLoad'] ?? true;

			if ( $selector ) {
				$selector = MultiViewUtils::convert_selector( $selector, $id, $store_instance );
			}

			if ( $hover_selector ) {
				$hover_selector = MultiViewUtils::convert_selector( $hover_selector, $id, $store_instance );
			}

			ScriptData::add_data_item(
				[
					'data_name' => 'multi_view',
					'data_item' => [
						'action'        => 'setContent',
						'moduleId'      => $id,
						'moduleName'    => $name,
						'selector'      => $selector,
						'hoverSelector' => $hover_selector,
						'data'          => $data,
						'switchOnLoad'  => $switch_on_load,
					],
				]
			);
		}

		return $data;
	}

	/**
	 * Populate multi view data to set HTML element visibility.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $id            The Module unique ID.
	 *     @type string   $name          The Module name.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 2 keys `breakpoint` and `state`.
	 *                                          -- @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          -- @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                     - The function can return a boolean value.
	 *                                          -- Return `true` to indicate the element is visible.
	 *                                          -- Return `false` to indicate the element is hidden.
	 *                                     - The function can return explicitly `visible` or `hidden` string value.
	 *                                          -- Return `visible` to indicate the element is visible.
	 *                                          -- Return `hidden` to indicate the element is hidden.
	 *                                     - The function will throw an `UnexpectedValueException` if the return value is not a boolean or `visible` or `hidden`.
	 *    @type int|null  $storeInstance The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::set_visibility([
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'id'            => 'divi/blurb-0',
	 *     'name'          => 'divi/blurb',
	 *     'data'          => [
	 *         'desktop' => [
	 *             'value' => 'on',
	 *             'hover' => 'off',
	 *         ],
	 *         'tablet' => [
	 *             'value' => 'off',
	 *         ],
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         return 'on' === $value ? 'visible' : 'hidden';
	 *     },
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'action'        => 'setVisibility',
	 *     'moduleId'      => 'divi/blurb-0',
	 *     'moduleName'    => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'desktop'        => 'visible',
	 *         'desktop--hover' => 'hidden',
	 *         'tablet'         => 'hidden',
	 *         'phone'          => 'hidden',
	 *     ]
	 * ]
	 * ```
	 */
	public static function set_visibility( array $args ): array {
		$data = MultiViewUtils::populate_data_visibility( $args );

		// Add script data if has non desktop value.
		if ( 1 < count( $data ) ) {
			$id             = $args['id'] ?? '';
			$name           = $args['name'] ?? '';
			$selector       = $args['selector'] ?? '';
			$hover_selector = $args['hoverSelector'] ?? '{{selector}}';
			$store_instance = $args['storeInstance'] ?? null;
			$switch_on_load = $args['switchOnLoad'] ?? true;

			if ( $selector ) {
				$selector = MultiViewUtils::convert_selector( $selector, $id, $store_instance );
			}

			if ( $hover_selector ) {
				$hover_selector = MultiViewUtils::convert_selector( $hover_selector, $id, $store_instance );
			}

			ScriptData::add_data_item(
				[
					'data_name' => 'multi_view',
					'data_item' => [
						'action'        => 'setVisibility',
						'moduleId'      => $id,
						'moduleName'    => $name,
						'selector'      => $selector,
						'hoverSelector' => $hover_selector,
						'data'          => $data,
						'switchOnLoad'  => $switch_on_load,
					],
				]
			);
		}

		return $data;
	}

	/**
	 * Populate multi view data to set HTML element inline style.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $id            The Module unique ID.
	 *     @type string   $name          The Module name.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 *     @type array    $data          A key-value pair array where the key is the CSS property name and the value is a formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 3 keys `property`, `breakpoint` and `state`.
	 *                                          - @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          - @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                          - @type string $property   Current CSS property name that being processed.
	 *                                     - The function must return a `string`.
	 *     @type callable $sanitizer     Optional. A function that will be invoked to sanitize/escape the value. Default `SavingUtility::sanitize_css_properties`.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being sanitized.
	 *                                     - The function 2nd argument is the CSS property name that being sanitized.
	 *                                     - The function must return a `string`.
	 *     @type int|null  $storeInstance The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @return array The populated multi view data that uniquely grouped by breakpoint and state.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::set_style([
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'id'            => 'divi/blurb-0',
	 *     'name'          => 'divi/blurb',
	 *     'data'          => [
	 *         'background-color' => [
	 *             'desktop' => [
	 *                 'value' => '#aaa',
	 *                 'hover' => '#bbb',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => '#ccc',
	 *             ],
	 *         ],
	 *         'background-image' => [
	 *             'desktop' => [
	 *                 'value' => 'http://example.com/desktop.jpg',
	 *                 'hover' => 'http://example.com/hover.jpg',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => 'http://example.com/tablet.jpg',
	 *             ],
	 *         ],
	 *     ],
	 *     'sanitizer'     => 'esc_attr',
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'background-image' === $resolver_args['property'] ) {
	 *             return 'url(' . $value . ')';
	 *         }
	 *
	 *         return $value;
	 *     },
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'action'        => 'setStyle',
	 *     'moduleId'      => 'divi/blurb-0',
	 *     'moduleName'    => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'desktop'        => [
	 *             'background-color' => '#aaa',
	 *             'background-image' => 'url(http://example.com/desktop.jpg)',
	 *         ],
	 *         'desktop--hover' => [
	 *             'background-color' => '#bbb',
	 *             'background-image' => 'url(http://example.com/hover.jpg)',
	 *         ],
	 *         'tablet'         => [
	 *             'background-color' => '#ccc',
	 *             'background-image' => 'url(http://example.com/tablet.jpg)',
	 *         ],
	 *     ]
	 * ]
	 * ```
	 */
	public static function set_style( array $args ): array {
		$data = MultiViewUtils::populate_data_style( $args );

		// Add script data if has non desktop value.
		if ( 1 < count( $data ) ) {
			$id             = $args['id'] ?? '';
			$name           = $args['name'] ?? '';
			$selector       = $args['selector'] ?? '';
			$hover_selector = $args['hoverSelector'] ?? '{{selector}}';
			$store_instance = $args['storeInstance'] ?? null;
			$switch_on_load = $args['switchOnLoad'] ?? true;

			if ( $selector ) {
				$selector = MultiViewUtils::convert_selector( $selector, $id, $store_instance );
			}

			if ( $hover_selector ) {
				$hover_selector = MultiViewUtils::convert_selector( $hover_selector, $id, $store_instance );
			}

			ScriptData::add_data_item(
				[
					'data_name' => 'multi_view',
					'data_item' => [
						'action'        => 'setStyle',
						'moduleId'      => $id,
						'moduleName'    => $name,
						'selector'      => $selector,
						'hoverSelector' => $hover_selector,
						'data'          => $data,
						'switchOnLoad'  => $switch_on_load,
					],
				]
			);
		}

		return $data;
	}
}
