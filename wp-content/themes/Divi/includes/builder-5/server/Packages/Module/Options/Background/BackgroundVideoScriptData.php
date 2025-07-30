<?php
/**
 * Module Options: Background Video Data Class.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\Packages\Module\Options\Background\BackgroundAssets;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * BackgroundVideoScriptData class.
 *
 * This class has functionality for setting and generating the script data.
 *
 * @since ??
 */
class BackgroundVideoScriptData {

	/**
	 * Generates and sets the background video script data.
	 *
	 * This function takes an array of arguments and generates the background video
	 * script data using the `BackgroundVideoScriptData::generate()`. It then registers the
	 * script data item using the `ScriptData::add_data_item()`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id            Optional. The ID of the background video. Defaults empty string.
	 *     @type string $selector      Optional. The CSS selector of the element to apply the background video to.
	 *                                 Default empty string.
	 *     @type array  $attr          Optional. The additional HTML attributes for the element. Defaults `[]`.
	 *     @type object $storeInstance Optional. The ID of instance where this block stored in BlockParserStore.
	 *                                 Default `null`.
	 * }
	 *
	 * @return void
	 */
	public static function set( array $args ): void {
		$data = self::generate(
			[
				'id'            => $args['id'] ?? '',
				'selector'      => $args['selector'] ?? '',
				'attr'          => $args['attr'] ?? [],
				'storeInstance' => $args['storeInstance'] ?? null,
			]
		);

		if ( ! $data ) {
			return;
		}

		// Register script data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'background_video',
				'data_item_id' => null,
				'data_item'    => $data,
			]
		);
	}

	/**
	 * Generate background video script data.
	 *
	 * This function generates background video script data based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id       Module ID. Example: `divi/cta-0`.
	 *     @type string $selector Module selector. Example: `.et_pb_cta_0`.
	 *     @type array  $attr     Module background group attributes.
	 * }
	 *
	 * @return array Returns an array in the format: `['selector' => $value]`.
	 *               If there is no background video, an empty array is returned.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'id'       => 'divi/cta-0',
	 *     'selector' => '.et_pb_cta_0',
	 *     'attr'     => [
	 *         'breakpoint1' => [
	 *             'state1' => [
	 *                 'video' => [
	 *                     'mp4'  => 'video1.mp4',
	 *                     'webm' => 'video1.webm',
	 *                 ],
	 *             ],
	 *             'state2' => [
	 *                 'video' => [
	 *                     'mp4'  => '',
	 *                     'webm' => 'video2.webm',
	 *                 ],
	 *             ],
	 *         ],
	 *         'breakpoint2' => [
	 *             'state1' => [
	 *                 'video' => [
	 *                     'mp4'  => 'video3.mp4',
	 *                     'webm' => '',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 * ];
	 *
	 * $data = BackgroundVideoData::generate( $args );
	 *
	 * // Returns:
	 * // [ 'selector' => '.et_pb_cta_0 > .et-pb-background-video' ]
	 * ```
	 */
	public static function generate( $args ) {
		$id        = $args['id'] ?? '';
		$selector  = $args['selector'] ?? '';
		$attrs     = $args['attr'] ?? [];
		$has_video = false;

		// Loop through all breakpoints and states to populate data.
		foreach ( $attrs as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$attr_breakpoint_state = ModuleUtils::use_attr_value(
					[
						'attr'       => $attrs,
						'breakpoint' => $breakpoint,
						'state'      => $state,
						'mode'       => 'getAndInheritAll',
					]
				);

				$mp4  = $attr_breakpoint_state['video']['mp4'] ?? '';
				$webm = $attr_breakpoint_state['video']['webm'] ?? '';

				if ( ! empty( $mp4 ) || ! empty( $webm ) ) {
					$has_video = true;

					// Exist from the loop.
					break 2;
				}
			}
		}

		if ( ! $has_video ) {
			return [];
		}

		// Enqueue wp-mediaelement styles/scripts.
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );

		// Enqueue video styles/scripts.
		BackgroundAssets::video_style_enqueue();
		BackgroundAssets::video_script_enqueue();

		return [
			'selector' => "{$selector} > .et-pb-background-video",
		];
	}

}
