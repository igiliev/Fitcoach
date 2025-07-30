<?php
/**
 * Module Options: Animation Script Data Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Animation;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Animation\AnimationUtils;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\Framework\FeaturesManager\FeaturesManager;

/**
 * Module Options: Animation script data
 *
 * @since ??
 */
class AnimationScriptData {

	/**
	 * Set the animation data item.
	 *
	 * This function sets the animation data item by registering it with the ScriptData class.
	 * The animation data item contains information such as the animation ID, selector, attributes, and store instance.
	 *
	 * Note: If the animation group attributes is disabled or animation feature is disabled
	 * (i.e `false === AnimationScriptData::is_enabled( $args['attr'] ) || ! FeaturesManager::get( 'anim' )`)
	 * then this function will not generate any data.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments.
	 *
	 *     @type string $id            Optional. The animation ID. Default empty string.
	 *     @type string $selector      Optional. The selector for the animated element. Default empty string.
	 *     @type array  $attr          Optional. The animation attributes. Default `[]`.
	 *     @type int    $storeInstance Optional. The ID of instance where this block stored in BlockParserStore.
	 *                                 Default `null`.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * AnimationScriptData::set( [
	 *     'id'            => 'divi/cta-0',
	 *     'selector'      => '.et_pb_cta_0',
	 *     'attr'          => [
	 *       'duration' => 1000,
	 *       'delay' => 200
	 *     ],
	 *     'storeInstance' => 123,
	 * ] );
	 * ```
	 */
	public static function set( array $args ): void {
		$args = wp_parse_args(
			$args,
			[
				'id'            => '',
				'selector'      => '',
				'attr'          => [],
				'storeInstance' => null,
			]
		);

		if ( ! AnimationUtils::is_enabled( $args['attr'] ) || ! FeaturesManager::get( 'anim' ) ) {
			return;
		}

		// Register script data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'animation',
				'data_item_id' => null,
				'data_item'    => AnimationUtils::generate_data(
					[
						'id'            => $args['id'],
						'selector'      => $args['selector'],
						'attr'          => $args['attr'],
						'storeInstance' => $args['storeInstance'],
					]
				),
			]
		);
	}

}
