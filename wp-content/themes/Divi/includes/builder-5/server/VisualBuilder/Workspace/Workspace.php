<?php
/**
 * Visual Builder Workspace.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Workspace;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class that handles Visual Builder Workspace.
 *
 * @since ??
 */
class Workspace {
	/**
	 * WordPress Option name that is used to save workspace items.
	 *
	 * @var string
	 */
	public static $option_name = 'et_divi_builder_workspaces';

	/**
	 * Get workspace items.
	 *
	 * @since ??
	 */
	public static function get_items() {
		$default_workspace_items = [
			'builtIn' => [
				'last-used' => [
					'name' => esc_html__( 'Last Used', 'et_builder' ),
					'id'   => 'last-used',
				],
			],
			'custom'  => [],
			'cloud'   => [],
		];

		$workspace_items = get_option( self::$option_name, $default_workspace_items );

		// NOTE: `custom` and `cloud` workspaces are not used yet. These are placed here as a foundation to ensure data structure
		// remains consistent once workspace is introduced.
		return [
			'builtIn' => $workspace_items['builtIn'] ?? [],
			'custom'  => $workspace_items['custom'] ?? [],
			'cloud'   => $workspace_items['cloud'] ?? [],
		];
	}

	/**
	 * Update workspace item.
	 *
	 * @since ??
	 *
	 * @param string $type      Workspace type.
	 * @param string $name      Workspace name.
	 * @param array  $workspace Workspace settings.
	 */
	public static function update_item( $type, $name, $workspace ) {
		$all_workspace_items = self::get_items();

		$saved_workspace_item = $all_workspace_items[ $type ][ $name ] ?? [];

		// Check if a) item is not empty, and b) different from last save.
		if ( ! empty( $workspace ) && $saved_workspace_item !== $workspace ) {
			$all_workspace_items[ $type ][ $name ] = $workspace;

			update_option( self::$option_name, $all_workspace_items );
		}
	}
}
