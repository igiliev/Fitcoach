<?php
/**
 * Module Options: Background Parallax Data Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\SanitizerUtility;
use ET\Builder\Framework\Utility\TextTransform;
use ET\Builder\FrontEnd\Module\ScriptData;
use ET\Builder\Packages\Module\Options\Background\BackgroundAssets;
use ET\Builder\Packages\Module\Options\Background\BackgroundComponentParallaxItem;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * Module Options: Background assets class.
 *
 * @since ??
 */
/**
 * BackgroundParallaxScriptData class.
 *
 * This class has functionality to set and generate the script data BackgroundParallaxScript.
 *
 *  @since ??
 */
class BackgroundParallaxScriptData {

	/**
	 * Set the script data for background parallax options.
	 *
	 * This function sets the script data for background parallax options for the specified module.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of input arguments used to set the sticky data settings.
	 *
	 *     @type string $id             Optional. The ID of the module. Default empty string.
	 *     @type string $selector       Optional. The selector used to target the module. Default empty string.
	 *     @type array  $name           Optional. The module name. Default `'module'`.
	 *     @type array  $attr           Optional. Module attributes. Default `[]`.
	 *     @type int    $storeInstance  Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
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
	 *         'name'           => 'divi/cta',
	 *         'attr'           => array( 'attr1' => 'value1', 'attr2' => 'value2' ),
	 *         'storeInstance'  => null,
	 *     );
	 *
	 *     StickyScriptData::set( $args );
	 * ```
	 */
	public static function set( array $args ): void {
		$data = self::generate(
			[
				'id'            => $args['id'] ?? '',
				'selector'      => $args['selector'] ?? '',
				'attr'          => $args['attr'] ?? [],
				'name'          => $args['name'] ?? 'module',
				'storeInstance' => $args['storeInstance'] ?? null,
			]
		);

		if ( ! $data ) {
			return;
		}

		// Register script data item.
		ScriptData::add_data_item(
			[
				'data_name'    => 'background_parallax',
				'data_item_id' => null,
				'data_item'    => $data,
			]
		);
	}

	/**
	 * Generate background parallax script data.
	 *
	 * This function generates an array of background parallax script data based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id       Module ID. Example: `divi/cta-0`.
	 *     @type string $selector Module selector. Example: `.et_pb_cta_0`.
	 *     @type array  $attr     Module background group attributes.
	 *     @type string $name     Optional. Parallax name. Traditionally, parallax is part of background options and only being used
	 *                            as module's background only. This parallax name anticipates the case of parallax being
	 *                            used on various elements inside a module. Default is `module`.
	 * }
	 *
	 * @return array Returns an array containing the selector and the data for each breakpoint and state.
	 *               If there is no parallax, an empty array will be returned.
	 *
	 * @example:
	 * ```php
	 *     $args = [
	 *         'id'       => 'divi/cta-0',
	 *         'selector' => '.et_pb_cta_0',
	 *         'attr'     => [
	 *             'breakpoint1' => [
	 *                 'state1' => [
	 *                     'image' => [
	 *                         'url'     => 'image1.jpg',
	 *                         'parallax'=> [
	 *                             'enabled' => 'on',
	 *                             'method'  => 'on',
	 *                         ],
	 *                     ],
	 *                 ],
	 *                 'state2' => [
	 *                     'image' => [
	 *                         'url'     => 'image2.jpg',
	 *                         'parallax'=> [
	 *                             'enabled' => 'off',
	 *                             'method'  => 'on',
	 *                         ],
	 *                     ],
	 *                 ],
	 *             ],
	 *             'breakpoint2' => [
	 *                 'state1' => [
	 *                     'image' => [
	 *                         'url'     => 'image3.jpg',
	 *                         'parallax'=> [
	 *                             'enabled' => 'on',
	 *                             'method'  => 'off',
	 *                         ],
	 *                     ],
	 *                 ],
	 *             ],
	 *         ],
	 *     ];
	 *
	 *     $data = BackgroundParallaxData::generate( $args );
	 * ```
	 *
	 *  @output:
	 * ```php
	 *     [
	 *        'selector' => '.et_pb_cta_0',
	 *        'data' => [
	 *            [
	 *                'uniqueSelector' => '.et_pb_background_parallaxitem-breakpoint1-state1-module--divi-cta-0',
	 *                'breakpoint' => 'breakpoint1',
	 *                'state' => 'state1',
	 *                'enabled' => true,
	 *                'trueParallax' => true,
	 *                'imageUrl' => 'image1.jpg',
	 *            ],
	 *            [
	 *                'uniqueSelector' => '.et_pb_background_parallaxitem-breakpoint1-state2-module--divi-cta-0',
	 *                'breakpoint' => 'breakpoint1',
	 *                'state' => 'state2',
	 *                'enabled' => false,
	 *                'trueParallax' => true,
	 *                'imageUrl' => 'image2.jpg',
	 *            ],
	 *            [
	 *                'uniqueSelector' => '.et_pb_background_parallaxitem-breakpoint2-state1-module--divi-cta-0',
	 *                'breakpoint' => 'breakpoint2',
	 *                'state' => 'state1',
	 *                'enabled' => true,
	 *                'trueParallax' => false,
	 *                'imageUrl' => 'image3.jpg',
	 *            ],
	 *        ],
	 *    ]
	 * ```
	 */
	public static function generate( array $args ): array {
		$id                = $args['id'] ?? '';
		$selector          = $args['selector'] ?? '';
		$attrs             = $args['attr'] ?? [];
		$name              = $args['name'] ?? 'module';
		$data              = [];
		$has_parallax      = false;
		$has_true_parallax = false;
		$parallax_name     = "{$name}--" . TextTransform::param_case( $id );

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

				$image_url = $attr_breakpoint_state['image']['url'] ?? null;

				if ( $image_url ) {
					$image_url = SanitizerUtility::sanitize_image_src( $image_url );
				}

				$image_parallax_enabled = $attr_breakpoint_state['image']['parallax']['enabled'] ?? 'off';
				$image_parallax_method  = $attr_breakpoint_state['image']['parallax']['method'] ?? 'on';
				$parallax_enabled       = $image_url && 'on' === $image_parallax_enabled;

				if ( $parallax_enabled && ! $has_parallax ) {
					$has_parallax = true;
				}

				$true_parallax_enabled = $parallax_enabled && 'on' === $image_parallax_method;

				if ( $true_parallax_enabled && ! $has_true_parallax ) {
					$has_true_parallax = true;
				}

				$data[] = [
					'uniqueSelector' => '.' . BackgroundComponentParallaxItem::get_parallax_classname( $breakpoint, $state ) . '-' . $parallax_name,
					'breakpoint'     => $breakpoint,
					'state'          => $state,
					'enabled'        => $parallax_enabled,
					'trueParallax'   => $true_parallax_enabled,
					'imageUrl'       => $image_url,
				];
			}
		}

		if ( ! $has_parallax ) {
			return [];
		}

		BackgroundAssets::parallax_style_enqueue();

		if ( $has_true_parallax ) {
			BackgroundAssets::parallax_script_enqueue();
		}

		return [
			'selector' => $selector,
			'data'     => $data,
		];
	}
}
