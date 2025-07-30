<?php
/**
 * REST: GlobalPresetController class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\GlobalData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * GlobalPreset REST Controller class.
 *
 * @since ??
 */
class GlobalPresetController extends RESTController {

	/**
	 * Sync global preset data with the server.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response Returns the REST response object.
	 */
	public static function sync( WP_REST_Request $request ): WP_REST_Response {
		$prepared_data = GlobalPreset::prepare_data( $request->get_param( 'presets' ) );

		$saved_data = GlobalPreset::save_data( $prepared_data );

		// Save the is converted flag for the global presets. It should save only once.
		$is_legacy_presets_imported  = GlobalPreset::is_legacy_presets_imported();
		$is_legacy_presets_importing = $request->get_param( 'converted' );
		if ( empty( $is_legacy_presets_imported ) && $is_legacy_presets_importing ) {
			GlobalPreset::save_is_legacy_presets_imported( boolval( $is_legacy_presets_importing ) );
		}

		return RESTController::response_success( (object) $saved_data );
	}

	/**
	 * Generates the properties for a preset type.
	 *
	 * @param string $preset_type The type of the preset.
	 * @param array  $extra_items_properties Additional properties to merge with the default item properties.
	 *
	 * @return array The array structure defining the properties of the preset type.
	 */
	public static function preset_type_properties( string $preset_type, array $extra_items_properties = [] ): array {
		$items_properties = array_merge(
			[
				'type'        => [
					'required' => true,
					'type'     => 'string',
					'enum'     => [ $preset_type ],
				],
				'id'          => [
					'required'  => true,
					'type'      => 'string',
					'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
					'minLength' => 1, // Prevent empty string.
				],
				'name'        => [
					'required'  => true,
					'type'      => 'string',
					'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
					'minLength' => 1, // Prevent empty string.
				],
				'created'     => [
					'required' => true,
					'type'     => 'integer',
				],
				'updated'     => [
					'required' => true,
					'type'     => 'integer',
				],
				'version'     => [
					'required'  => true,
					'type'      => 'string',
					'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
					'minLength' => 1, // Prevent empty string.
				],
				'attrs'       => [
					'required' => false,
					'type'     => 'object', // Will be sanitized using GlobalPreset::prepare_data().
				],
				'renderAttrs' => [
					'required' => false,
					'type'     => 'object', // Will be sanitized using GlobalPreset::prepare_data().
				],
				'styleAttrs'  => [
					'required' => false,
					'type'     => 'object', // Will be sanitized using GlobalPreset::prepare_data().
				],
			],
			$extra_items_properties
		);

		return [
			'required' => false,
			'type'     => 'array',
			'items'    => [
				'type'                 => 'object',
				'properties'           => [
					'default' => [
						'required' => true,
						'type'     => 'string',
						'format'   => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
					],
					'items'   => [
						'required' => true,
						'type'     => 'array',
						'items'    => [
							'type'                 => 'object',
							'properties'           => $items_properties,
							'additionalProperties' => false,
						],
					],
				],
				'additionalProperties' => false,
			],
		];
	}

	/**
	 * Get the arguments for the sync action.
	 *
	 * This function returns an array that defines the arguments for the sync action,
	 * which is used in the `register_rest_route()` function.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the sync action. The array should aligns with the GlobalData.Presets.RestSchemaItems TS interface.
	 */
	public static function sync_args(): array {
		return [
			'presets'   => [
				'required'             => true,
				'type'                 => 'object',
				'properties'           => [
					'module' => self::preset_type_properties(
						'module',
						[
							'moduleName' => [
								'required'  => true,
								'type'      => 'string',
								'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
								'minLength' => 1, // Prevent empty string.
							],
						]
					),
					'group'  => self::preset_type_properties(
						'group',
						[
							'groupId'         => [
								'required'  => true,
								'type'      => 'string',
								'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
								'minLength' => 1, // Prevent empty string.
							],
							'groupName'       => [
								'required'  => true,
								'type'      => 'string',
								'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
								'minLength' => 1, // Prevent empty string.
							],
							'moduleName'      => [
								'required'  => true,
								'type'      => 'string',
								'format'    => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
								'minLength' => 1, // Prevent empty string.
							],
							'primaryAttrName' => [
								'type'   => 'string',
								'format' => 'text-field', // Set format to 'text-field' to get the value sanitized using sanitize_text_field.
							],
						]
					),
				],
				'additionalProperties' => false,
			],
			'converted' => [
				'required' => false,
				'type'     => 'boolean',
			],
		];
	}

	/**
	 * Provides the permission status for the sync action.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user has the permission to use the Visual Builder, `false` otherwise.
	 */
	public static function sync_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
