<?php
/**
 * Fonts: FontsUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Fonts;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\VisualBuilder\Hooks\HooksRegistration;
use WP_Error;

/**
 * FontsUtility class.
 *
 * This class provides functionality for adding and removing fonts and retrieving their corresponding
 * MIME types.
 *
 * @since ??
 */
class FontsUtility {

	/**
	 * Save the font-file.
	 *
	 * @since ??
	 *
	 * @param array  $font_files Font files.
	 * @param string $font_name Font name.
	 * @param array  $font_settings Font settings.
	 * @param array  $overrides Custom parameters that will be passed to wp_handle_upload.
	 *
	 * @return array|WP_Error Will return WP_Error on failure.
	 */
	/**
	 * Adds a custom font to the list of uploaded fonts.
	 *
	 * This function allows you to upload and add a custom font to the list of uploaded fonts.
	 * The font files must be passed as an array with each file's extension as the key and the file path as the value.
	 * The font name and font settings must also be provided.
	 * Optionally, you can provide an array of overrides.
	 *
	 * @since ??
	 *
	 * @param array  $font_files        An array of font files to be uploaded. Each file should be in the form of `['extension' => 'file_path']`.
	 * @param string $font_name         The name of the font to be added. Must not be empty and should not contain special characters.
	 * @param array  $font_settings     Font settings for the uploaded font. The 'font_weights' key should contain a string of font weights
	 *                                  separated by commas ('all' for all weights). The 'generic_family' key should contain the generic font
	 *                                  family for the font ('serif' by default).
	 * @param array  $overrides         Optional. An array of overrides. Currently only supports 'action' override for `wp_handle_upload()`.
	 *                                  Default `[]`.
	 *
	 * @return array|WP_Error           Returns an array containing the uploaded font name and the updated list of all custom fonts on success.
	 *                                  Returns a WP_Error object on failure, with the error code specifying the issue.
	 *
	 * @example:
	 * ```php
	 * $font_files = array(
	 *    'ttf' => '/path/to/font.ttf',
	 *    'woff' => '/path/to/font.woff',
	 * );
	 *
	 * $font_name = 'Custom Font';
	 *
	 * $font_settings = array(
	 *    'font_weights' => '400,600,700',
	 *    'generic_family' => 'sans-serif',
	 * );
	 *
	 * $overrides = array(
	 *    'action' => 'custom_upload_action',
	 * );
	 *
	 * $result = FontsUtility::font_add( $font_files, $font_name, $font_settings, $overrides );
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     echo 'Error: ' . $result->get_error_message();
	 * } else {
	 *     echo 'Custom font "' . $result['uploaded_font'] . '" added successfully.<br>';
	 *     echo 'Updated custom fonts: <pre>' . print_r( $result['updated_fonts'], true ) . '</pre>';
	 * }
	 * ```
	 */
	public static function font_add( array $font_files, string $font_name, array $font_settings, array $overrides = array() ) {
		if ( ! isset( $font_files ) || empty( $font_files ) ) {
			return new WP_Error( 'file_empty', esc_html__( 'No Font File Provided.', 'et_builder' ) );
		}

		// remove all special characters from the font name.
		$font_name = preg_replace( '/[^A-Za-z0-9\s\_-]/', '', $font_name );

		if ( '' === $font_name ) {
			return new WP_Error( 'font_name_empty', esc_html__( 'Font Name Cannot be Empty and Cannot Contain Special Characters.', 'et_builder' ) );
		}

		$google_fonts     = et_builder_get_google_fonts();
		$all_custom_fonts = get_option( 'et_uploaded_fonts', array() );

		// Don't allow to add fonts with the names which already used by User Fonts or Google Fonts.
		if ( isset( $all_custom_fonts[ $font_name ] ) || isset( $google_fonts[ $font_name ] ) ) {
			return new WP_Error( 'font_name_exists', esc_html__( 'Font With This Name Already Exists. Please Use a Different Name.', 'et_builder' ) );
		}

		// Set the upload Directory for builder font files.
		add_filter( 'upload_dir', [ HooksRegistration::class, 'upload_dir_font' ] );

		// Set the wp_check_filetype_and_ext filter to whitelist the font file mime types before uploading font file.
		add_filter( 'wp_check_filetype_and_ext', [ HooksRegistration::class, 'check_filetype_and_ext_font' ], 999, 3 );

		$upload_error   = new WP_Error();
		$uploaded_files = array();

		foreach ( $font_files as $ext => $font_file ) {
			// Try to upload font file.
			// phpcs:ignore ET.Sniffs.DangerousFunctions.ET_handle_upload -- test_type is enabled and proper type and extension checking are implemented.
			$upload = wp_handle_upload(
				$font_file,
				wp_parse_args(
					array(
						'test_size' => true,
						'test_type' => true,
						'test_form' => false,
					),
					$overrides
				)
			);

			if ( empty( $upload['error'] ) ) {
				if ( ! isset( $uploaded_files['font_file'] ) ) {
					$uploaded_files['font_file'] = array();
				}

				$uploaded_files['font_file'][ $ext ] = esc_url( $upload['file'] );

				if ( ! isset( $uploaded_files['font_url'] ) ) {
					$uploaded_files['font_url'] = array();
				}

				$uploaded_files['font_url'][ $ext ] = esc_url( $upload['url'] );
			} else {
				$upload_error->add( 'upload_error_handler', $upload['error'] );
			}
		}

		// Reset the upload Directory after uploading font file.
		remove_filter( 'upload_dir', [ HooksRegistration::class, 'upload_dir_font' ] );

		// Reset the wp_check_filetype_and_ext filter after uploading font file.
		remove_filter( 'wp_check_filetype_and_ext', [ HooksRegistration::class, 'check_filetype_and_ext_font' ], 999 );

		if ( empty( $uploaded_files ) ) {
			if ( ! extension_loaded( 'fileinfo' ) ) {
				$upload_error->add( 'fileinfo_extension_not_loaded', __( 'An error occurred while uploading the font file. We detected that the \'fileinfo\' PHP extension is not loaded in your server. Enabling the extension may help.', 'et_builder' ) );
			}

			if ( ! $upload_error->has_errors() ) {
				$upload_error->add( 'upload_error_unknown', __( 'An unknown error occurred while uploading the font file. Please try again.', 'et_builder' ) );
			}

			return $upload_error;
		}

		if ( ! empty( $font_settings ) ) {
			$uploaded_files['styles'] = ! isset( $font_settings['font_weights'] ) || 'all' === $font_settings['font_weights'] ? '100,200,300,400,500,600,700,800,900' : $font_settings['font_weights'];
			$uploaded_files['type']   = isset( $font_settings['generic_family'] ) ? $font_settings['generic_family'] : 'serif';
		}

		// organize uploaded files.
		$all_custom_fonts[ $font_name ] = $uploaded_files;

		update_option( 'et_uploaded_fonts', $all_custom_fonts );

		/**
		 * Action hook to fire after custom font has been added.
		 *
		 * @since ??
		 */
		do_action( 'divi_visual_builder_fonts_custom_font_added' );

		return array(
			'uploaded_font' => $font_name,
			'updated_fonts' => $all_custom_fonts,
		);
	}

	/**
	 * Remove a font from the list of uploaded fonts.
	 *
	 * This function removes the specified font from the list of uploaded fonts.
	 *
	 * @since ??
	 *
	 * @param string $font_name The name of the font to be removed.
	 *
	 * @return array|WP_Error If the font is successfully removed, an array is returned with the updated fonts.
	 *                        If the font name is empty, a `WP_Error` object is returned with the 'file_name_empty' error code.
	 *                        If the font does not exist in the list, a `WP_Error` object is returned with the 'font_not_exist' error code.
	 *
	 * @example:
	 * ```php
	 * // Remove a font with the name 'MyFont'
	 * $result = FontsUtility::font_remove( 'MyFont' );
	 *
	 * if ( is_wp_error( $result ) ) {
	 *     $error_code = $result->get_error_code();
	 *     // Handle the error based on the error code
	 * } else {
	 *     $updated_fonts = $result['updated_fonts'];
	 *     // Process the updated fonts array
	 * }
	 * ```
	 */
	public static function font_remove( string $font_name ) {
		if ( '' === $font_name ) {
			return new WP_Error( 'file_name_empty', esc_html__( 'Font Name Cannot be Empty.', 'et_builder' ) );
		}

		$all_custom_fonts = get_option( 'et_uploaded_fonts', array() );

		if ( ! isset( $all_custom_fonts[ $font_name ] ) || ! isset( $all_custom_fonts[ $font_name ]['font_file'] ) ) {
			return new WP_Error( 'font_not_exist', esc_html__( 'Font Does not Exist.', 'et_builder' ) );
		}

		// remove all uploaded font files if array.
		if ( is_array( $all_custom_fonts[ $font_name ]['font_file'] ) ) {
			foreach ( $all_custom_fonts[ $font_name ]['font_file'] as $font_file ) {
				et_pb_safe_unlink_font_file( $font_file );
			}
		} else {
			et_pb_safe_unlink_font_file( $all_custom_fonts[ $font_name ]['font_file'] );
		}

		unset( $all_custom_fonts[ $font_name ] );

		update_option( 'et_uploaded_fonts', $all_custom_fonts );

		/**
		 * Action hook to fire after custom font has been removed.
		 *
		 * @since ??
		 */
		do_action( 'divi_visual_builder_fonts_custom_font_removed' );

		return array(
			'updated_fonts' => $all_custom_fonts,
		);
	}

	/**
	 * Get an array of allowed MIME types associated with font file extensions.
	 *
	 * This function returns an associative array where the keys are font file extensions and
	 * the values are arrays of MIME types associated with those extensions.
	 *
	 * @link https://www.iana.org/assignments/media-types/media-types.xml#font
	 *
	 * @since ??
	 *
	 * @return array An associative array of font file extensions and their corresponding MIME types.
	 */
	public static function mime_types_font(): array {
		return array(
			'otf'   => array(
				'font/otf',
				'application/x-font-opentype',
				'application/x-font-ttf',
				'application/vnd.ms-opentype',
			),
			'ttf'   => array(
				'font/ttf',
				'font/sfnt',
				'application/font-sfnt',
				'application/x-font-ttf',
			),
			'woff'  => array(
				'font/woff',
				'application/font-woff',
			),
			'woff2' => array(
				'font/woff2',
				'application/font-woff2',
			),
			'eot'   => array(
				'application/vnd.ms-fontobject',
			),
		);
	}
}
