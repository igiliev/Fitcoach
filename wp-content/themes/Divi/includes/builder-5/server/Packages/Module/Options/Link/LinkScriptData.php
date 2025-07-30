<?php
/**
 * Module: LinkScriptData class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Link;

use ET\Builder\Packages\Module\Options\Link\LinkUtils;
use ET\Builder\FrontEnd\Module\ScriptData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * LinkScriptData class.
 *
 * This class provides functionality for setting properties of a link script data object.
 *
 * @since ??
 */
class LinkScriptData {

	/**
	 * Set script data for link options.
	 *
	 * This function generates script data for link options based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id            Optional. The module ID. Example: `divi/cta-0`. Default empty string.
	 *     @type string $selector      Optional. The module selector. Example: `.et_pb_cta_0`. Default empty string.
	 *     @type array  $attr          Optional. The module link group attributes. Default `[]`.
	 *     @type int    $storeInstance Optional. The ID of the instance where this block is stored in the BlockParserStore. Default `null`.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * ET_Core_Cache::set( [
	 *     'id'            => 'divi/cta-0',
	 *     'selector'      => '.et_pb_cta_0',
	 *     'attr'          => [],
	 *     'storeInstance' => null
	 * ] );
	 * ```
	 */
	public static function set( array $args ): void {
		$data = LinkUtils::generate_data(
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
				'data_name'    => 'link',
				'data_item_id' => null,
				'data_item'    => $data,
			]
		);
	}

}
