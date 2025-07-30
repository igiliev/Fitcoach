<?php
/**
 * Module Options: Sticky Script Data Class.
 *
 * @since ??
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Sticky;

use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\Packages\Module\Options\Sticky\StickyUtils;


if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * StickyScriptData class.
 *
 * This class is used to store and retrieve sticky data.
 *
 * @since ??
 */
class StickyScriptData {

	/**
	 * Sets the sticky data settings into the script data.
	 *
	 * This function sets the sticky data settings into the script data for the specified module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of input arguments used to set the sticky data settings.
	 *
	 *     @type string $id             Optional. The ID of the module. Default empty string.
	 *     @type string $selector       Optional. The selector used to target the module. Default empty string.
	 *     @type array  $affectingAttrs Optional. Attributes that the sticky settings affect. Default `[]`.
	 *     @type array  $attr           Optional. Module attributes. Default `[]`.
	 *     @type object $storeInstance  Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     // Setting sticky data for a module.
	 *     $args = array(
	 *         'id'             => 'my_module',
	 *         'selector'       => '.module-selector',
	 *         'affectingAttrs' => array( 'attr1', 'attr2' ),
	 *         'attr'           => array( 'attr1' => 'value1', 'attr2' => 'value2' ),
	 *         'storeInstance'  => null,
	 *     );
	 *     self::set( $args );
	 * ```
	 */
	public static function set( array $args ): void {
		$args = wp_parse_args(
			$args,
			[
				'id'             => '',
				'selector'       => '',
				'affectingAttrs' => [],
				'attr'           => [],

				// FE only.
				'storeInstance'  => null,
			]
		);

		// Bail early if no attr is given.
		if ( empty( $args['attr'] ) ) {
			return;
		}

		// Skip if sticky status isn't true.
		$is_sticky = StickyUtils::is_sticky_module(
			$args['id'],
			BlockParserStore::get_all( $args['storeInstance'] )
		);

		if ( ! $is_sticky ) {
			return;
		}

		// Configured script data settings format.
		$sticky_setting = StickyUtils::get_sticky_setting(
			[
				'affectingAttrs' => $args['affectingAttrs'],
				'attr'           => $args['attr'],
				'id'             => $args['id'],
				'selector'       => $args['selector'],

				// TODO fix(D5, Refactor) This might not be needed.
				'breakpoint'     => 'desktop',
				'state'          => 'value',
			]
		);

		// Register script data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'sticky',
				'data_item_id' => $args['id'],
				'data_item'    => $sticky_setting,
			]
		);
	}

}
