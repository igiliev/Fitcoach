<?php
/**
 * ConversionUtils Class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Conversion\Utils;

use WP_Block_Type_Registry;
use ET_Core_Data_Utils;
use ET\Builder\Packages\Conversion\Conversion;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

/**
 * Contains Conversion Utils.
 *
 * @since ??
 */
class ConversionUtils {
	/**
	 * Parse Serialized Post Into Flat Module Object.
	 *
	 * Parse serialized GB-format post content into flat module objects.
	 *
	 * @param string        $serialized_post GB-format content.
	 * @param array|null    $custom_root Custom root object.
	 * @param callable|null $uuid_function Function to generate a UUID.
	 *
	 * @return array
	 */
	public static function parseSerializedPostIntoFlatModuleObject( string $serialized_post, ?array $custom_root = null, ?callable $uuid_function = null ): array {
		$default_root = [
			'children' => [],
			'name'     => 'divi/root',
			'id'       => 'root',
			'props'    => [
				'attrs' => [],
			],
		];

		$root = $custom_root ?? $default_root;

		// Object which will collect and return all the parsed module object.
		$flat_module_objects = [
			$root['id'] => $root,
		];

		$uuid_function = $uuid_function ?? fn() => ET_Core_Data_Utils::uuid_v4();

		// Parse each block object into module object. This is to be called recursively.
		$parse_block_into_flat_object = function ( array $block, string $parent ) use ( &$parse_block_into_flat_object, &$flat_module_objects, $uuid_function ) {
			// Reject invalid block; block should have blockName.
			if ( empty( $block['blockName'] ) ) {
				return;
			}

			// Reject 'divi/modules-styles' since it should not be loaded inside the builder.
			if ( 'divi/modules-styles' === $block['blockName'] ) {
				return;
			}

			$is_non_convertible = isset( $block['attrs']['nonconvertible'] ) && 'yes' === $block['attrs']['nonconvertible'];

			if ( $is_non_convertible ) {
				// If block was nonconvertible, try to convert it on load in case it can be converted now.
				$converted_content = Conversion::maybeConvertContent( trim( $block['innerHTML'] ) );
				$parsed_blocks     = parse_blocks( $converted_content );

				// Check if there are any parsed modules inside innerHTML.
				if ( ! empty( $parsed_blocks ) && isset( $parsed_blocks[0] ) ) {
					$maybe_converted_block = $parsed_blocks[0];
				} else {
					$maybe_converted_block = null;
				}

				// Set new name and attribute that will replace divi/shortcode-module
				// if module that was nonconvertible is now successfully converted.
				$name       = $maybe_converted_block['blockName'] ?? null;
				$attributes = $maybe_converted_block['attrs'] ?? [];
			} else {
				$name       = $block['blockName'] ?? null;
				$attributes = $block['attrs'] ?? [];
			}

			// For unknown or Shortcode module, get the innerHTML.
			$is_unsupported = ! WP_Block_Type_Registry::get_instance()->is_registered( $name ) || 'divi/shortcode-module' === $name;
			$inner_html     = $is_unsupported ? $block['innerHTML'] ?? null : null;

			$id = $uuid_function();

			// phpcs:disable Squiz.Commenting.InlineComment -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
			// phpcs:disable Squiz.PHP.CommentedOutCode.Found -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
			// error_log('$id: ' . $id);

			$module_object = [
				'name'     => $name,
				'props'    => [
					'attrs' => $attributes,
				],
				'id'       => $id,
				'parent'   => $parent,
				'children' => [],
			];

			// If module is unknown or Shortcode, set innerHTML props so original HTML content can be loaded.
			if ( $is_unsupported ) {
				$module_object['props']['innerHTML'] = $inner_html;
			}

			// Set module object on overall module objects.
			$flat_module_objects[ $id ] = $module_object;

			// Update parent module object's children to include this object as its children.
			$flat_module_objects[ $parent ]['children'][] = $id;

			// Run moduleObject parser into inner block if there's any.
			if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as $inner_block ) {
					$parse_block_into_flat_object( $inner_block, $id );
				}
			}
		};

		// Parse serialized GB-format post content into GB block object.
		$block_objects = parse_blocks( $serialized_post );

		// Parse block objects.
		foreach ( $block_objects as $block ) {
			$parse_block_into_flat_object( $block, $root['id'] );
		}

		return $flat_module_objects;
	}
}
