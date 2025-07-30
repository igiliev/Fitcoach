<?php
/**
 * Module: MultiViewScriptData::set_class_name() method.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptDataTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;

trait SetClassNameTrait {

	/**
	 * Populate multi view data to set HTML element class name.
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
	 *     @type array    $data          A key-value pair array where the key is the class name and the value is a formatted breakpoint and state array.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *                                     - The function has 2 arguments.
	 *                                     - The function 1st argument is the original attribute value that being processed.
	 *                                     - The function 2nd argument is a key-value pair array with 3 keys `className`, `breakpoint` and `state`.
	 *                                          -- @type string $breakpoint Current breakpoint that being processed. Can be `desktop`, `tablet`, or `phone`.
	 *                                          -- @type string $state      Current state that being processed. Can be `value` or `hover`.
	 *                                          -- @type string $className  Current class name that being processed.
	 *                                     - The function can return a boolean value.
	 *                                          -- Return `true` to add the class name to the element.
	 *                                          -- Return `false` to add the class name from the element.
	 *                                     - The function can return explicitly `add` or `remove` string value.
	 *                                          -- Return `add` to add the class name to the element.
	 *                                          -- Return `hidden` to add the class name from the element.
	 *                                     - The function will throw an `UnexpectedValueException` if the return value is not a boolean or `add` or `remove`.
	 *     @type callable $sanitizer     Optional. A function that will be invoked to sanitize/escape the class name. Default `esc_attr`.
	 *                                     - The function will be invoked after the `valueResolver` function.
	 *                                     - The function has 1 argument.
	 *                                     - The function 1st argument is the class name that being sanitized.
	 *                                     - The function must return a `string`.
	 *     @type int|null  $storeInstance The ID of instance where this block stored in BlockParserStore.
	 * }
	 *
	 * @return array A key-value pair array where the key is the breakpoint and the value is a key-value pair array
	 *               where the key is `add` or `remove` and the value is an array of class names.
	 *
	 * @example:
	 * ```php
	 * MultiViewUtils::set_class_name([
	 *     'id'            => 'divi/blurb-0',
	 *     'name'          => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'et-use-icon' => [
	 *             'desktop' => [
	 *                 'value' => 'on',
	 *                 'hover' => 'off',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => 'off',
	 *             ],
	 *         ],
	 *         'et-no-icon'   => [
	 *             'desktop' => [
	 *                 'value' => 'on',
	 *                 'hover' => 'off',
	 *             ],
	 *             'tablet'  => [
	 *                 'value' => 'off',
	 *             ],
	 *         ],
	 *     ],
	 *     'valueResolver' => function( $value, array $resolver_args ) {
	 *         if ( 'et-no-icon' === $resolver_args['className'] ) {
	 *             return 'off' === $value ? 'add' : 'remove';
	 *         }
	 *
	 *         return 'on' === $value ? 'add' : 'remove';
	 *     },
	 *     'sanitizer'     => 'et_core_esc_previously',
	 * ]);
	 * ```
	 * @output:
	 * ```php
	 * [
	 *     'action'        => 'setClassName',
	 *     'moduleId'      => 'divi/blurb-0',
	 *     'moduleName'    => 'divi/blurb',
	 *     'selector'      => '.et_pb_blurb_0',
	 *     'hoverSelector' => '.et_pb_blurb_0',
	 *     'data'          => [
	 *         'desktop'        => [
	 *             'add'    => ['et-use-icon'],
	 *             'remove' => ['et-no-icon'],
	 *         ],
	 *         'desktop--hover' => [
	 *             'add'    => ['et-no-icon'],
	 *             'remove' => ['et-use-icon'],
	 *         ],
	 *         'tablet'         => [
	 *             'add'    => ['et-no-icon'],
	 *             'remove' => ['et-use-icon'],
	 *         ],
	 *     ],
	 * ]
	 * ```
	 */
	public static function set_class_name( array $args ): array {
		$data = MultiViewUtils::populate_data_class_name( $args );

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
						'action'        => 'setClassName',
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
