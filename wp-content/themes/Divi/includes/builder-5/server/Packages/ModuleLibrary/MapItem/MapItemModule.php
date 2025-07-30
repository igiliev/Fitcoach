<?php
/**
 * Module: Map Pin class.
 *
 * @package ET\Builder\Packages\ModuleLibrary\MapItem
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\MapItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * `MapItem` is consisted of functions used for Map Pin such as Front-End rendering, REST API Endpoints etc.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class MapItemModule implements DependencyInterface {

	/**
	 * Map Pin render callback which outputs server side rendered HTML on the Front-End.
	 *
	 * This function is equivalent of JS function MapItemEdit located in
	 * visual-builder/packages/module-library/src/components/map-pin/edit.tsx.
	 *
	 * @since ??
	 *
	 * @param array          $attrs                       Block attributes that were saved by VB.
	 * @param string         $content                     Block content.
	 * @param WP_Block       $block                       Parsed block object that being rendered.
	 * @param ModuleElements $elements                    ModuleElements instance.
	 *
	 * @return string HTML rendered of Map Pin module.
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

		$default_parent_attrs = ModuleRegistration::get_default_attrs( 'divi/map' );
		$parent_attrs         = array_replace_recursive( $default_parent_attrs, $parent->attrs ?? [] );

		$title_value = $attrs['title']['innerContent']['desktop']['value'] ?? '';
		$pin_lat     = $attrs['pin']['innerContent']['desktop']['value']['lat'] ?? null;
		$pin_lng     = $attrs['pin']['innerContent']['desktop']['value']['lng'] ?? null;

		if ( ! isset( $pin_lat, $pin_lng ) ) {
			return '';
		}

		$has_title   = ModuleUtils::has_value( $attrs['title']['innerContent'] ?? [] );
		$has_content = ModuleUtils::has_value( $attrs['content']['innerContent'] ?? [] );

		// Title.
		$title = $elements->render(
			[
				'attrName'   => 'title',
				'attributes' => [
					'style' => [
						'margin-top' => '10px',
					],
				],
			]
		);

		// Content.
		$content = ( $has_title || $has_content ) ? $elements->render(
			[
				'attrName' => 'content',
			]
		) : '';

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'attrs'               => $attrs,
				'htmlAttrs'           => [
					'data-lat'   => $pin_lat,
					'data-lng'   => $pin_lng,
					'data-title' => $title_value,
				],
				'elements'            => $elements,
				'className'           => 'et_pb_map_pin',
				'hasModuleClassName'  => false,
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'parentAttrs'         => $parent_attrs,
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $title . $content,
			]
		);
	}

	/**
	 * Set script data of used module options.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *   Array of arguments.
	 *
	 *   @type string         $id            Module id.
	 *   @type string         $name          Module name.
	 *   @type string         $selector      Module selector.
	 *   @type array          $attrs         Module attributes.
	 *   @type int            $storeInstance The ID of instance where this block stored in BlockParserStore class.
	 *   @type ModuleElements $elements      ModuleElements instance.
	 * }
	 */
	public static function module_script_data( $args ) {
		// Assign variables.
		$id             = $args['id'] ?? '';
		$name           = $args['name'] ?? '';
		$selector       = $args['selector'] ?? '';
		$attrs          = $args['attrs'] ?? [];
		$elements       = $args['elements'];
		$store_instance = $args['storeInstance'] ?? null;

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);

		MultiViewScriptData::set(
			[
				'id'            => $id,
				'name'          => $name,
				'storeInstance' => $store_instance,
				'selector'      => $selector,
				'hoverSelector' => $selector,
				'setAttrs'      => [
					[
						'data'          => [
							'data-title' => $attrs['title']['innerContent'] ?? [],
						],
						'valueResolver' => function( $value ) {
							return $value ?? '';
						},
					],
				],
			]
		);
	}

	/**
	 * Loads `MapItem` and registers Front-End render callback and REST API Endpoints.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load() {
		$module_json_folder_path = dirname( __DIR__, 4 ) . '/visual-builder/packages/module-library/src/components/map-pin/';

		add_filter( 'divi_conversion_presets_attrs_map', array( MapItemPresetAttrsMap::class, 'get_map' ), 10, 2 );

		// Ensure that all filters and actions applied during module registration are registered before calling `ModuleRegistration::register_module()`.
		// However, for consistency, register all module-specific filters and actions prior to invoking `ModuleRegistration::register_module()`.
		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);

	}

}
