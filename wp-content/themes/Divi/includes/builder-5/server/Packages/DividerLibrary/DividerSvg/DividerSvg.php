<?php
/**
 * DividerSvg class
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Packages\DividerLibrary\DividerSvg;

use ET\Builder\Framework\Utility\Filesystem;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * DividerSvg class.
 *
 * This class contains methods to work with divider svg.
 *
 * @since ??
 */
class DividerSvg {
	/**
	 * Divider SVG Settings.
	 *
	 * This function is the equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-divider-library/variables/defaultDividerStyles defaultDividerStyles}
	 * from `@divi/divider-library` package.
	 *
	 * @since ??
	 *
	 * @throws Exception Throw error when an expected json file does not exist.
	 *
	 * @return array
	 */
	public static function default_divider_styles(): array {
		// Divider style list.
		$divider_style_defaults = [];

		// Get the filesystem trait.
		$filesystem = Filesystem::get();

		$library_dir = dirname( __DIR__, 4 ) . '/visual-builder/packages/divider-library';

		// Default divider styles.
		$divider_style_names = [
			'arrow',
			'arrow2',
			'arrow3',
			'asymmetric',
			'asymmetric2',
			'asymmetric3',
			'asymmetric4',
			'clouds',
			'clouds2',
			'curve',
			'curve2',
			'graph',
			'graph2',
			'graph3',
			'graph4',
			'mountains',
			'mountains2',
			'ramp',
			'ramp2',
			'slant',
			'slant2',
			'triangle',
			'wave',
			'wave2',
			'waves',
			'waves2',
		];

		// Build out the divider style list using the default divider styles.
		foreach ( $divider_style_names as $divider_style ) {
			$json_file = "$library_dir/src/components/$divider_style.json";

			// Verify that the divider style json file exists.
			if ( ! file_exists( $json_file ) ) {
				throw new Exception( 'File does not exist: ' . $json_file, 1 );
			}

			// Get the divider style data from the json file.
			$json_data = json_decode( $filesystem->get_contents( $json_file ), true );

			// Add the divider style data to the divider style list.
			$divider_style_defaults[ $divider_style ] = $json_data;
		}

		return $divider_style_defaults;
	}

}
