<?php
/**
 * MaskSvg class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\MaskAndPatternLibrary\MaskSvg;

use ET\Builder\Framework\Utility\Filesystem;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * MaskSvg class.
 *
 * @since ??
 */
class MaskSvg {
	/**
	 * Mask SVG Settings.
	 *
	 * @since ??
	 *
	 * @throws Exception Throw error when an expected json file does not exist.
	 *
	 * @return array {
	 *   Mask style list.
	 *
	 *   @type string $name     Mask style name.
	 *   @type array  $dataJSON Mask style data.
	 * }
	 */
	public static function mask_style_list(): array {
		// Mask style list.
		$mask_style_defaults = [];

		$filesystem = Filesystem::get();

		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$library_dir = dirname( __DIR__, 4 ) . '/visual-builder/packages/mask-and-pattern-library';

		// Default mask styles.
		$mask_style_names = [
			'arch',
			'bean',
			'blades',
			'caret',
			'chevrons',
			'corner-blob',
			'corner-lake',
			'corner-paint',
			'corner-pill',
			'corner-square',
			'diagonal',
			'diagonal-bars',
			'diagonal-bars-2',
			'diagonal-pills',
			'ellipse',
			'floating-squares',
			'honeycomb',
			'layer-blob',
			'paint',
			'rock-stack',
			'square-stripes',
			'triangles',
			'wave',
		];

		// Build out the mask style list using the default mask styles.
		foreach ( $mask_style_names as $mask_style ) {
			$json_file = "$library_dir/src/components/mask-svg/components/$mask_style.json";

			// Verify that the mask style json file exists.
			if ( ! file_exists( $json_file ) ) {
				throw new Exception( 'File does not exist: ' . $json_file, 1 );
			}

			// Get the mask style data from the json file.
			$json_data = json_decode( $filesystem->get_contents( $json_file ), true );

			// Add the mask style data to the mask style list.
			$mask_style_defaults[ $mask_style ] = $json_data;
		}

		return $mask_style_defaults;
	}

}
