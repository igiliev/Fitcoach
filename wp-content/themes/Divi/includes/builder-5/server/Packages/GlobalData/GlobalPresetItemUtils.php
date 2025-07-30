<?php
/**
 * GlobalPresetItemUtils class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\TextTransform;


/**
 * GlobalPresetItemUtils class.
 *
 * @since ??
 */
class GlobalPresetItemUtils {


	/**
	 * Generate preset class name.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $presetType       The Preset type. Can be 'module' or 'group'.
	 *     @type string $presetModuleName The Preset Module Name.
	 *     @type string $presetGroupName  The Preset Group Name.
	 *     @type string $presetId         The Preset ID.
	 * }
	 *
	 * @return string The preset class name.
	 */
	public static function generate_preset_class_name( array $args ): string {
		$list_of_excluded_groups_for_class_name = [ 'divi/id-classes', 'divi/animation' ];

		$preset_type        = $args['presetType'] ?? 'module';
		$preset_module_name = $args['presetModuleName'] ?? '';
		$preset_group_name  = $args['presetGroupName'] ?? '';
		$preset_id          = $args['presetId'] ?? 'default';

		if ( in_array( $preset_group_name, $list_of_excluded_groups_for_class_name, true ) ) {
			return '';
		}

		if ( $preset_module_name && $preset_group_name ) {
			return sprintf( 'preset--%s--%s--%s--%s', $preset_type, TextTransform::kebab_case( $preset_module_name ), TextTransform::kebab_case( $preset_group_name ), $preset_id );
		}

		if ( $preset_module_name ) {
			return sprintf( 'preset--%s--%s--%s', $preset_type, TextTransform::kebab_case( $preset_module_name ), $preset_id );
		}

		if ( $preset_group_name ) {
			return sprintf( 'preset--%s--%s--%s', $preset_type, TextTransform::kebab_case( $preset_group_name ), $preset_id );
		}

		return sprintf( 'preset--%s--%s', $preset_type, $preset_id );
	}
}
