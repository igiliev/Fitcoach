<?php
/**
 * Module: MultiViewScriptData::set() method.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptDataTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;

trait SetTrait {

	/**
	 * Set multi view data: HTML element attributes, HTML element class name, HTML element inner content,
	 * HTML element inline style, and HTML element visibility.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $id            The Module unique ID.
	 *     @type string   $name          The Module name.
	 *     @type int|null $storeInstance The ID of instance where this block stored in BlockParserStore.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 *     @type array    $setAttrs      Array of actions to set HTML element attributes.
	 *                                   @see `MultiViewScriptData::set_attrs()` method.
	 *     @type array    $setClassName  Array of actions to set HTML element class name.
	 *                                   @see `MultiViewScriptData::set_class_name()` method.
	 *     @type array    $setContent    Array of actions to set HTML element inner content.
	 *                                   @see `MultiViewScriptData::set_content()` method.
	 *     @type array    $setStyle      Array of actions to set HTML element inline style.
	 *                                   @see `MultiViewScriptData::set_style()` method.
	 *     @type array    $setVisibility Array of actions to set HTML element visibility.
	 *                                   @see `MultiViewScriptData::set_visibility()` method.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * MultiViewScriptData::set([
	 *     'id'            => 'divi/blurb-0',
	 *     'name'          => 'divi/blurb',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'setAttrs'      => [
	 *         [
	 *             'selector'      => '.et_pb_blurb_0 img',
	 *             'data'          => [
	 *                 'src' => [
	 *                     'desktop' => [
	 *                         'value' => [
	 *                             'url'   => 'http://example.com/desktop.jpg',
	 *                             'title' => 'My Desktop Image',
	 *                         ],
	 *                         'hover' => [
	 *                             'url'   => 'http://example.com/hover.jpg',
	 *                             'title' => 'My Hover Image',
	 *                         ],
	 *                     ],
	 *                     'tablet'  => [
	 *                         'value' => [
	 *                             'url'   => 'http://example.com/tablet.jpg',
	 *                             'title' => 'My Tablet Image',
	 *                         ],
	 *                     ],
	 *                 ],
	 *                 'alt' => [
	 *                     'desktop' => [
	 *                         'value' => [
	 *                             'url'   => 'http://example.com/desktop.jpg',
	 *                             'title' => 'My Desktop Image',
	 *                         ],
	 *                         'hover' => [
	 *                             'url'   => 'http://example.com/hover.jpg',
	 *                             'title' => 'My Hover Image',
	 *                         ],
	 *                     ],
	 *                     'tablet'  => [
	 *                         'value' => [
	 *                             'url'   => 'http://example.com/tablet.jpg',
	 *                             'title' => 'My Tablet Image',
	 *                         ],
	 *                     ],
	 *                 ],
	 *             ],
	 *             'sanitizers'    => [
	 *                 'src' => 'esc_url',
	 *                 'alt' => 'esc_attr',
	 *             ],
	 *             'valueResolver' => function( $value, array $resolver_args ) {
	 *                 if ( 'alt' === $resolver_args['attrName'] ) {
	 *                     return $value['title'] ?? '';
	 *                 }
	 *
	 *                 return $value['url'] ?? '';
	 *             },
	 *             'tag'           => 'img',
	 *         ],
	 *     ],
	 *     'setClassName'  => [
	 *         [
	 *             'selector'      => '.et_pb_blurb_0 .description',
	 *             'data'          => [
	 *                 'et-use-icon' => [
	 *                     'desktop' => [
	 *                         'value' => 'on',
	 *                         'hover' => 'off',
	 *                     ],
	 *                     'tablet'  => [
	 *                         'value' => 'off',
	 *                     ],
	 *                 ],
	 *                 'et-no-icon'   => [
	 *                     'desktop' => [
	 *                         'value' => 'on',
	 *                         'hover' => 'off',
	 *                     ],
	 *                     'tablet'  => [
	 *                         'value' => 'off',
	 *                     ],
	 *                 ],
	 *             ],
	 *             'valueResolver' => function( $value, array $resolver_args ) {
	 *                 if ( 'et-no-icon' === $resolver_args['className'] ) {
	 *                     return 'off' === $value ? 'add' : 'remove';
	 *                 }
	 *
	 *                 return 'on' === $value ? 'add' : 'remove';
	 *             },
	 *         ],
	 *     ],
	 *     'setContent'    => [
	 *         [
	 *             'hoverSelector' => '.et_pb_blurb_0 .description',
	 *             'data'          => [
	 *                 'desktop' => [
	 *                     'value' => '<p>Foo</p>',
	 *                     'hover' => '<p>Bar</p>',
	 *                 ],
	 *                 'tablet' => [
	 *                     'value' => '<p>Baz</p>',
	 *                 ],
	 *             ],
	 *             'valueResolver' => function( $value, array $resolver_args ) {
	 *                 if ( 'phone' === $resolver_args['breakpoint'] ) {
	 *                     return $value . '<p>Custom Baz</p>';
	 *                 }
	 *
	 *                 return $value;
	 *             },
	 *             'sanitizer'     => 'et_core_esc_previously',
	 *         ],
	 *     ],
	 *     'setStyle'      => [
	 *         [
	 *             'selector'      => '.et_pb_blurb_0 .description',
	 *             'data'          => [
	 *                 'background-color' => [
	 *                     'desktop' => [
	 *                         'value' => '#aaa',
	 *                         'hover' => '#bbb',
	 *                     ],
	 *                     'tablet'  => [
	 *                         'value' => '#ccc',
	 *                     ],
	 *                 ],
	 *                 'background-image' => [
	 *                     'desktop' => [
	 *                         'value' => 'http://example.com/desktop.jpg',
	 *                         'hover' => 'http://example.com/hover.jpg',
	 *                     ],
	 *                     'tablet'  => [
	 *                         'value' => 'http://example.com/tablet.jpg',
	 *                     ],
	 *                 ],
	 *             ],
	 *             'sanitizer'     => 'esc_attr',
	 *             'valueResolver' => function( $value, array $resolver_args ) {
	 *                 if ( 'background-image' === $resolver_args['property'] ) {
	 *                     return 'url(' . $value . ')';
	 *                 }
	 *
	 *                 return $value;
	 *             },
	 *         ],
	 *     ],
	 *     'setVisibility' => [
	 *         [
	 *             'selector'      => '.et_pb_blurb_0 img',
	 *             'data'          => [
	 *                 'desktop' => [
	 *                     'value' => 'on',
	 *                     'hover' => 'off',
	 *                 ],
	 *                 'tablet' => [
	 *                     'value' => 'off',
	 *                 ],
	 *             ],
	 *             'valueResolver' => function( $value, array $resolver_args ) {
	 *                 return 'on' === $value ? 'visible' : 'hidden';
	 *             },
	 *         ],
	 *     ],
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     [
	 *         'action'        => 'setAttrs',
	 *         'moduleId'      => 'divi/blurb-0',
	 *         'moduleName'    => 'divi/blurb',
	 *         'selector'      => '.et_pb_blurb_0 img',
	 *         'hoverSelector' => '.et_pb_blurb_0',
	 *         'data'          => [
	 *             'desktop'        => [
	 *                 'src' => 'http://example.com/desktop.jpg',
	 *                 'alt' => 'My Desktop Image',
	 *             ],
	 *             'desktop--hover' => [
	 *                 'src' => 'http://example.com/hover.jpg',
	 *                 'alt' => 'My Hover Image',
	 *             ],
	 *             'tablet'         => [
	 *                 'src' => 'http://example.com/tablet.jpg',
	 *                 'alt' => 'My Tablet Image',
	 *             ],
	 *         ],
	 *     ],
	 *     [
	 *         'action'        => 'setClassName',
	 *         'moduleId'      => 'divi/blurb-0',
	 *         'moduleName'    => 'divi/blurb',
	 *         'selector'      => '.et_pb_blurb_0 .description',
	 *         'hoverSelector' => '.et_pb_blurb_0',
	 *         'data'          => [
	 *             'desktop'        => [
	 *                 'add'    => ['et-use-icon'],
	 *                 'remove' => ['et-no-icon'],
	 *             ],
	 *             'desktop--hover' => [
	 *                 'add'    => ['et-no-icon'],
	 *                 'remove' => ['et-use-icon'],
	 *             ],
	 *             'tablet'         => [
	 *                 'add'    => ['et-no-icon'],
	 *                 'remove' => ['et-use-icon'],
	 *             ],
	 *         ],
	 *     ],
	 *     [
	 *         'action'        => 'setContent',
	 *         'moduleId'      => 'divi/blurb-0',
	 *         'moduleName'    => 'divi/blurb',
	 *         'selector'      => '.et_pb_blurb_0 .description',
	 *         'hoverSelector' => '.et_pb_blurb_0',
	 *         'data'          => [
	 *             'desktop'        => '<p>Foo</p>',
	 *             'desktop--hover' => '<p>Bar</p>',
	 *             'tablet'         => '<p>Baz</p>',
	 *             'phone'          => '<p>Baz</p><p>Custom Baz</p>',
	 *         ],
	 *     ],
	 *     [
	 *         'action'        => 'setStyle',
	 *         'moduleId'      => 'divi/blurb-0',
	 *         'moduleName'    => 'divi/blurb',
	 *         'selector'      => '.et_pb_blurb_0 .description',
	 *         'hoverSelector' => '.et_pb_blurb_0',
	 *         'data'          => [
	 *             'desktop'        => [
	 *                 'background-color' => '#aaa',
	 *                 'background-image' => 'url(http://example.com/desktop.jpg)',
	 *             ],
	 *             'desktop--hover' => [
	 *                 'background-color' => '#bbb',
	 *                 'background-image' => 'url(http://example.com/hover.jpg)',
	 *             ],
	 *             'tablet'         => [
	 *                 'background-color' => '#ccc',
	 *                 'background-image' => 'url(http://example.com/tablet.jpg)',
	 *             ],
	 *         ]
	 *     ],
	 *     [
	 *         'action'        => 'setVisibility',
	 *         'moduleId'      => 'divi/blurb-0',
	 *         'moduleName'    => 'divi/blurb',
	 *         'selector'      => '.et_pb_blurb_0 img',
	 *         'hoverSelector' => '.et_pb_blurb_0',
	 *         'data'          => [
	 *             'desktop'        => 'visible',
	 *             'desktop--hover' => 'hidden',
	 *             'tablet'         => 'hidden',
	 *         ]
	 *     ],
	 * ]
	 * ```
	 */
	public static function set( array $args ): void {
		$methods = [
			'set_attrs'      => $args['setAttrs'] ?? [],
			'set_class_name' => $args['setClassName'] ?? [],
			'set_content'    => $args['setContent'] ?? [],
			'set_style'      => $args['setStyle'] ?? [],
			'set_visibility' => $args['setVisibility'] ?? [],
		];

		foreach ( $methods as $method => $actions ) {
			if ( ! $actions || ! is_array( $actions ) ) {
				continue;
			}

			foreach ( $actions as $action ) {
				if ( empty( $action['data'] ) || ! is_array( $action['data'] ) ) {
					continue;
				}

				call_user_func(
					[ MultiViewScriptData::class, $method ],
					array_merge(
						$action,
						[
							'id'            => $action['id'] ?? $args['id'] ?? '',
							'name'          => $action['name'] ?? $args['name'] ?? '',
							'selector'      => $action['selector'] ?? $args['selector'] ?? '',
							'hoverSelector' => $action['hoverSelector'] ?? $args['hoverSelector'] ?? null,
							'storeInstance' => $action['storeInstance'] ?? $args['storeInstance'] ?? null,
						]
					)
				);
			}
		}
	}
}
