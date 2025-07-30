<?php
/**
 * Module: DividersPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Dividers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * DividersPresetAttrsMap class.
 *
 * This class provides the static map for the dividers preset attributes.
 *
 * @since ??
 */
class DividersPresetAttrsMap {
	/**
	 * Get the map for the dividers preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the dividers preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		$tabs   = [ 'top', 'bottom' ];
		$result = [];

		foreach ( $tabs as $tab ) {
			$result = array_merge(
				$result,
				[
					"{$attr_name}.{$tab}__style"       => [
						'attrName' => "{$attr_name}.{$tab}",
						'subName'  => 'style',
						'preset'   => [ 'html', 'style' ],
					],
					"{$attr_name}.{$tab}__color"       => [
						'attrName' => "{$attr_name}.{$tab}",
						'subName'  => 'color',
						'preset'   => [ 'style' ],
					],
					"{$attr_name}.{$tab}__height"      => [
						'attrName' => "{$attr_name}.{$tab}",
						'subName'  => 'height',
						'preset'   => [ 'style' ],
					],
					"{$attr_name}.{$tab}__repeat"      => [
						'attrName' => "{$attr_name}.{$tab}",
						'subName'  => 'repeat',
						'preset'   => [ 'style' ],
					],
					"{$attr_name}.{$tab}__flip"        => [
						'attrName' => "{$attr_name}.{$tab}",
						'subName'  => 'flip',
						'preset'   => [ 'style' ],
					],
					"{$attr_name}.{$tab}__arrangement" => [
						'attrName' => "{$attr_name}.{$tab}",
						'subName'  => 'arrangement',
						'preset'   => [ 'style' ],
					],
				]
			);
		}

		return $result;
	}
}
