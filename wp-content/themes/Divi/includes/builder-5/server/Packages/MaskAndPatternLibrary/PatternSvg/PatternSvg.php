<?php
/**
 * PatternSvg class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\MaskAndPatternLibrary\PatternSvg;

use ET\Builder\Framework\Utility\Filesystem;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * PatternSvg class.
 *
 * @since ??
 */
class PatternSvg {
	/**
	 * Pattern SVG Settings.
	 *
	 * @since ??
	 *
	 * @throws Exception Throw error when an expected json file does not exist.
	 *
	 * @return array {
	 *   @type string $name     Pattern style name.
	 *   @type array  $dataJSON Pattern style data.
	 * }
	 */
	public static function pattern_style_list(): array {
		// Pattern style list.
		$pattern_style_defaults = [];

		// Get the filesystem trait.
		$filesystem = Filesystem::get();

		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.dirname_levelsFound -- We have PHP 7 support now, This can be deleted once PHPCS config is updated.
		$library_dir = dirname( __DIR__, 4 ) . '/visual-builder/packages/mask-and-pattern-library';

		// Default pattern styles.
		$pattern_style_names = [
			'3d-diamonds',
			'checkerboard',
			'confetti',
			'crosses',
			'cubes',
			'diagonal-stripes',
			'diagonal-stripes-2',
			'diamonds',
			'honeycomb',
			'inverted-chevrons',
			'inverted-chevrons-2',
			'ogees',
			'pills',
			'pinwheel',
			'polka-dots',
			'scallops',
			'shippo',
			'smiles',
			'squares',
			'triangles',
			'tufted',
			'waves',
			'zig-zag',
			'zig-zag-2',
		];

		// Build out the pattern style list using the default pattern styles.
		foreach ( $pattern_style_names as $pattern_style ) {
			$json_file = "$library_dir/src/components/pattern-svg/components/$pattern_style.json";

			// Verify that the pattern style json file exists.
			if ( ! file_exists( $json_file ) ) {
				throw new Exception( 'File does not exist: ' . $json_file, 1 );
			}

			// Get the pattern style data from the json file.
			$json_data = json_decode( $filesystem->get_contents( $json_file ), true );

			// Add the pattern style data to the pattern style list.
			$pattern_style_defaults[ $pattern_style ] = $json_data;
		}

		return $pattern_style_defaults;
	}

}
