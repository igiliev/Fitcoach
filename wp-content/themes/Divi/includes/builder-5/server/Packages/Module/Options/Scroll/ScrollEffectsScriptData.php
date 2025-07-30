<?php
/**
 * Module Options: Scroll Effects Script Data Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Scroll;

use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\ScriptData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ScrollEffectsScriptData class.
 *
 * This class is used to set scroll effects script data.
 *
 * @since ??
 */
class ScrollEffectsScriptData {

	/**
	 * Sets the scroll data settings into the script data.
	 *
	 * This function sets the scroll data settings into the script data for the specified module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of input arguments used to set the scroll data settings.
	 *
	 *     @type string $id             The ID of the module.
	 *     @type string $selector       The selector used to target the module.
	 *     @type array  $attr           Scroll Effects attributes. Default `[]`.
	 *     @type array  $transform      Optional. Transform attributes. Default `[]`.
	 *     @type object $storeInstance  Optional. The ID of instance where this block stored in BlockParserStore for FE only. Default `null`.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     // Setting scroll data for a module.
	 *     $args = array(
	 *         'id'            => 'my_module',
	 *         'selector'      => '.module-selector',
	 *         'attr'          => array( 'attr1' => 'value1', 'attr2' => 'value2' ),
	 *         'transform'     => array( 'attr1' => 'value1', 'attr2' => 'value2' ),
	 *         'storeInstance' => null,
	 *     );
	 *     self::set( $args );
	 * ```
	 *
	 * @example How scroll settings presented as a JSON object named `diviElementScrollData`:
	 *
	 *  ```html
	 *  <script type="text/javascript" id="et-builder-modules-script-motion-js-extra">
	 *    var diviElementScrollData = {
	 *      "divi/text-0": {
	 *        "desktop": [
	 *          {
	 *            "id": ".et_pb_text_0",
	 *            "start": 0,
	 *            "midStart": 50,
	 *            "midEnd": 50,
	 *            "end": 100,
	 *            "startValue": 4.5,
	 *            "midValue": 1,
	 *            "endValue": 8.5,
	 *            "resolver": "translateY",
	 *            "trigger_start": "bottom",
	 *            "trigger_end": "middle",
	 *            "transforms": {
	 *              "scaleX": 0.64,
	 *              "scaleY": 0.64,
	 *              "translateX": "34px",
	 *              "translateY": "34px"
	 *            }
	 *          },
	 *          //... rest of array entries
	 *        ],
	 *        "tablet": [
	 *          //... similar tablet array as desktop
	 *        ],
	 *        "phone": [
	 *          //... similar phone array as desktop
	 *        ]
	 *      },
	 *      "divi/accordion-0": {
	 *        //... similar structure as "divi/text-0"
	 *      },
	 *    };
	 *  </script>
	 *  ```
	 */
	public static function set( array $args ): void {
		$args = array_merge(
			[
				'id'            => '',
				'selector'      => '',
				'attr'          => [],
				'transform'     => [],

				// FE only.
				'storeInstance' => null,
			],
			$args
		);

		// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
		/**
		 * Module Block Data.
		 *
		 * @var BlockParserBlock $module_data
		 */
		$module_data = BlockParserStore::get( $args['id'], $args['storeInstance'] );

		// bail if no module data is found or no block name is found.
		if ( empty( $args['attr'] ) && ( empty( $module_data ) || empty( $module_data->blockName ) ) ) {
			return;
		}

		/**
		 * Module Configurations.
		 *
		 * @var \WP_Block_Type $module_config
		 */
		$module_config = \WP_Block_Type_Registry::get_instance()->get_registered( $module_data->blockName );

		$parent_script_setting = [];
		$parent_id             = $module_data->parentId;
		$is_child_module       = 'child-module' === $module_config->category;

		if ( $is_child_module ) {
			$parent_data  = BlockParserStore::get( $parent_id, $args['storeInstance'] );
			$parent_attrs = $parent_data->get_merged_attrs();
			$parent_attr  = $parent_attrs['module'] ?? [];

			// regex test: https://regex101.com/r/9DZVS6/1.
			$regex = '/"gridMotion":\{"enable":"on"\}/m';

			// only process parent scroll effects if grid motion is enabled in the attributes.
			if ( preg_match( $regex, wp_json_encode( $parent_attr ) ) === 1 ) {
				$parent_script_setting = ScrollEffectsUtils::get_scroll_setting(
					[
						'attr'          => $parent_attr['decoration']['scroll'] ?? [],
						'id'            => $parent_id,
						'transform'     => $parent_attr['decoration']['transform'] ?? [],

						// Use child item selector.
						'selector'      => $args['selector'],

						// FE only.
						'storeInstance' => $args['storeInstance'],

						// Module Data.
						'module_data'   => $module_data,
					]
				);
			}
		}
		// phpcs:enable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

		// Bail early if no attr is given.
		// NOTE: Because scroll effects can be inherited from parent module i.e grid motion,
		// we should only bail early if no parent script setting is given for a child module.
		if ( empty( $args['attr'] ) && ( $is_child_module && empty( $parent_script_setting ) ) ) {
			return;
		}

		// get scroll effects enabled status.
		$is_enabled = ScrollEffectsUtils::is_scroll_effects_enabled(
			$args['id'],
			BlockParserStore::get_all( $args['storeInstance'] ),
			$is_child_module,
			$parent_script_setting
		);

		// Skip if scroll status isn't true.
		if ( ! $is_enabled ) {
			return;
		}

		$scroll_setting = ScrollEffectsUtils::get_scroll_setting(
			[
				'attr'                  => $args['attr'],
				'id'                    => $args['id'],
				'selector'              => $args['selector'],
				'transform'             => $args['transform'],
				'is_child_module'       => $is_child_module,
				'parent_script_setting' => $parent_script_setting,
				'module_data'           => $module_data,

				// FE only.
				'storeInstance'         => $args['storeInstance'],
			]
		);

		// Register script data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'scroll',
				'data_item_id' => $args['selector'],
				'data_item'    => $scroll_setting,
			]
		);

		// Cleanup.
		unset( $scroll_setting );
	}

}
