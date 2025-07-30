<?php
/**
 * Flexbox Migration
 *
 * Handles the migration of flexbox-related features and configurations.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Migration;

use ET\Builder\Packages\Conversion\Utils\ConversionUtils;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\VisualBuilder\Saving\SavingUtility;

/**
 * Flexbox Migration Class.
 *
 * @since ??
 */
class FlexboxMigration implements MigrationInterface {

	/**
	 * The migration name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private static $_name = 'flexbox.v1';

	/**
	 * List of module names that use flexbox layout.
	 *
	 * @since ??
	 *
	 * @var string[]
	 */
	private static $_flexbox_modules = [
		'divi/section',
		'divi/row',
		'divi/column',
		'divi/group',
	];

	/**
	 * The flexbox release version string.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private static $_release_version = '5.0.0-public-alpha.14.1'; // TODO fix(D5, Flexbox Migration): Update this to the latest release version. [https://github.com/elegantthemes/Divi/issues/43484].

	/**
	 * Run the flexbox migration.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function load(): void {
		$is_flexbox_enabled = \et_get_experiment_flag( 'flexbox' );

		if ( ! $is_flexbox_enabled ) {
			return;
		}

		add_action( 'wp', [ __CLASS__, 'migrate_fe_content' ] );
		add_action( 'et_fb_load_raw_post_content', [ __CLASS__, 'migrate_vb_content' ], 10, 2 );
	}

	/**
	 * Get the migration name.
	 *
	 * @since ??
	 *
	 * @return string The migration name.
	 */
	public static function get_name() {
		return self::$_name;
	}

	/**
	 * Migrate the content for the frontend.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function migrate_fe_content(): void {
		global $post;

		if (
			! Conditions::is_d5_enabled()
			|| ! Conditions::is_tb_admin_screen()
			|| ! Conditions::is_wp_post_edit_screen()
			|| ! Conditions::is_vb_app_window()
			|| ! Conditions::is_ajax_request()
			|| ! Conditions::is_rest_api_request()
		) {
			return;
		}

		$content = self::_get_current_content();
		if ( $content ) {

			$new_content = self::_migrate_the_content( $content );

			// Update the post content using filter.
			add_filter(
				'the_content',
				function( $content ) use ( $new_content ) {
					remove_filter( 'the_content', __FUNCTION__ );
					return $new_content;
				},
				8 // BEFORE do_blocks().
			);

		}
	}

	/**
	 * Migrate the Visual Builder content.
	 *
	 * @since ??
	 *
	 * @param string $content The content to migrate.
	 * @return string The migrated content.
	 */
	public static function migrate_vb_content( $content ) {
		$new_content = self::_migrate_the_content( $content );

		return $new_content;
	}

	/**
	 * Migrate the content.
	 *
	 * @since ??
	 *
	 * @param string $content The content to migrate.
	 *
	 * @return string The migrated content.
	 */
	private static function _migrate_the_content( $content ) {
		$flat_objects = ConversionUtils::parseSerializedPostIntoFlatModuleObject( $content );

		foreach ( $flat_objects as $module_id => $module_data ) {
			if (
				in_array( $module_data['name'], self::$_flexbox_modules, true )
				&& version_compare( $module_data['props']['attrs']['builderVersion'] ?? '0.0.0', self::$_release_version, '<' )
			) {

				$new_value = [
					'props' => [
						'attrs' => [
							'builderVersion' => self::$_release_version,
							'module'         => [
								'decoration' => [
									'layout' => [
										'desktop' => [
											'value' => [
												'display' => 'block',
											],
										],
									],
								],
							],
						],
					],
				];

				$flat_objects[ $module_id ] = array_replace_recursive( $flat_objects[ $module_id ], $new_value );
			}
		}

		// Serialize the flat objects back into the content.
		$blocks      = self::_flat_objects_to_blocks( $flat_objects );
		$new_content = SavingUtility::serialize_sanitize_blocks( $blocks );

		// Reset the block parser store and order index to avoid conflicts with rendering.
		BlockParserStore::reset();
		BlockParserBlock::reset_order_index();

		return $new_content;
	}

	/**
	 * Get current post content.
	 *
	 * @since ??
	 *
	 * @return string|null Post content or null if not in post context.
	 */
	private static function _get_current_content(): ?string {
		global $post;
		return $post instanceof \WP_Post ? get_the_content( null, false, $post ) : null;
	}

	/**
	 * Convert flat module objects back to block array structure.
	 *
	 * @since ??
	 *
	 * @param array $flat_objects The flat module objects.
	 * @return array The block array structure.
	 */
	private static function _flat_objects_to_blocks( array $flat_objects ): array {
		// Find the root object.
		$root = null;
		foreach ( $flat_objects as $object ) {
			if ( isset( $object['parent'] ) && ( null === $object['parent'] || 'root' === $object['parent'] ) ) {
				$root = $object;
				break;
			}
		}
		if ( ! $root ) {
			return [];
		}
		return array_map(
			function( $child_id ) use ( $flat_objects ) {
				return self::_build_block_from_flat( $child_id, $flat_objects );
			},
			$root['children']
		);
	}

	/**
	 * Recursively build a block from a flat object.
	 *
	 * @since ??
	 *
	 * @param string $id The object ID.
	 * @param array  $flat_objects The flat module objects.
	 * @return array The block array.
	 */
	private static function _build_block_from_flat( string $id, array $flat_objects ): array {
		$object = $flat_objects[ $id ];
		$block  = [
			'blockName'    => $object['name'],
			'attrs'        => $object['props']['attrs'] ?? [],
			'innerBlocks'  => [],
			'innerContent' => [],
		];
		if ( ! empty( $object['children'] ) ) {
			foreach ( $object['children'] as $child_id ) {
				$block['innerBlocks'][]  = self::_build_block_from_flat( $child_id, $flat_objects );
				$block['innerContent'][] = null; // Placeholder, will be filled by serializer.
			}
		}
		if ( isset( $object['props']['innerHTML'] ) ) {
			$block['innerContent'][] = $object['props']['innerHTML'];
		}
		return $block;
	}
}
