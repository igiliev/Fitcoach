<?php
/**
 * GetGlobalColorsDataTrait
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Portability\PortabilityPostTraits;

use ET\Builder\Packages\GlobalData\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait GetGlobalColorsDataTrait {

	/**
	 * Get Global Colors array from provided global_colors_info.
	 *
	 * @since 4.10.8
	 *
	 * @param array $global_colors_info {
	 *     Array of global colors to process.
	 *
	 *     @type string|int $key         The key.
	 *     @type string     $color_value Color value.
	 * }
	 *
	 * @return array {
	 *     The list of the Global Colors.
	 *
	 *     @type int    $key         The key.
	 *     @type int    $color_id    The color ID.
	 *     @type string $color_value The color value
	 * }
	 */
	public function get_global_colors_data( $global_colors_info = array() ) {
		$global_color_ids = array_unique( array_keys( $global_colors_info ) );

		if ( empty( $global_color_ids ) ) {
			return array();
		}

		$all_global_colors = GlobalData::get_global_colors();
		$used_colors       = array();

		foreach ( $global_color_ids as $color_id ) {
			if ( isset( $all_global_colors[ $color_id ] ) ) {
				$color_data = array(
					$color_id,
					$all_global_colors[ $color_id ],
				);

				$used_colors[] = $color_data;
			}
		}

		return $used_colors;
	}

}
