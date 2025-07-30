<?php
/**
 * Experiment and Debug Flags for Divi Development Environment
 *
 * This file contains functionality for managing and retrieving experiment
 * and debug feature flags in the Divi development environment. Experiment
 * flags allow developers to toggle incomplete or experimental features, while
 * debug flags enable enhanced debugging capabilities during development.
 *
 * @package Divi
 * @since ??
 */

/**
 * Retrieves experiment and debug flags for the Divi Development environment.
 *
 * This function enables the retrieval of specific feature or debug flags,
 * or the complete set of experiment and debug flags. These flags are used
 * to manage and toggle experimental development features or debugging
 * behaviors temporarily during implementation or testing.
 *
 * @since ??
 *
 * @param string $flag Optional. The specific flag to retrieve. If provided, the function
 *                     returns the value of the specified flag. If omitted, it returns
 *                     an array containing all experiment and debug flags.
 *
 * @return mixed If $flag is provided, returns the value of the specified flag.
 *               If $flag is omitted, returns an associative array containing 'experiments'
 *               and 'debug' flags.
 */
function et_get_experiment_flag( string $flag = '' ) {
	// Bail when ET_DEBUG isn't set to true.
	if ( ! defined( 'ET_DEBUG' ) || ! ET_DEBUG ) {
		return $flag ? false : [];
	}

	// Experiment feature flags are intended to be temporary. They allow features
	// to be introduced in smaller chunks, which can be merged into the release
	// branch but remain disabled until all associated chunks are completed and the
	// feature is deemed ready for release. These flags will eventually be removed.
	$feature_flags = [
		// Flag for enabling D4 3P extension backwards compatibility improvements feature.
		'backCompat'            => false,

		// Flag for enabling the Global Sync feature.
		'globalModuleSync'      => false,

		// Flag for enabling WooCommerce Product Page Modules.
		'wooProductPageModules' => false,

		// Flag for Flexbox feature.
		'flexbox'               => false,

		// Flag for enabling the new Divi Loop Builder experience.
		'loopBuilder'           => false,

		// Flag for new color picker.
		'newColorPicker'        => false,
	];

	/**
	 * Filters the experiment feature flags for the development environment.
	 *
	 * @since ??
	 *
	 * @param string[] $feature_flags The array of experimental feature flags.
	 */
	$feature_flags = apply_filters( 'divi_experiment_feature_flags', $feature_flags );

	if ( defined( 'DIVI_EXPERIMENT_FEATURE_FLAGS' ) && is_array( DIVI_EXPERIMENT_FEATURE_FLAGS ) ) {
		$feature_flags = array_merge( $feature_flags, DIVI_EXPERIMENT_FEATURE_FLAGS );
	}

	// @phpcs:disable -- This is cleaner
	if ( $env_flags = getenv( 'DIVI_EXPERIMENT_FEATURE_FLAGS' ) ) {
		$env_flags     = array_fill_keys( explode( ',', $env_flags ), true );
		$feature_flags = array_merge( $feature_flags, $env_flags );
	}
	// @phpcs:enable

	// Debug flags are meant to support the development and debugging process.
	// These should typically remain set to `false` but can be temporarily enabled
	// (`true`) during debugging to aid troubleshooting efforts.
	$debug_flags = [
		// Flag to add a debug classname to the body for the module mousetrap feature.
		'moduleMousetrap'          => false,

		// Flags to automatically display the store state modal on app load for debugging purposes.
		// Both 'storeStateModal' and 'storeStateModalOnAppLoad' should be enabled to display the modal.
		'storeStateModalOnAppLoad' => false,
		'storeStateModal'          => false,

		// Flag to display debugging information for user activity tracking.
		'userActivity'             => false,
	];

	/**
	 * Filters the experiment debug flags for the development environment.
	 *
	 * @since ??
	 *
	 * @param string[] $debug_flags The array of debug configuration flags.
	 */
	$debug_flags = apply_filters( 'divi_experiment_debug_flags', $debug_flags );

	// If a specific flag is provided, retrieve its value and apply a filter for customization.
	if ( $flag ) {
		$flag_value = $feature_flags[ $flag ] ?? $debug_flags[ $flag ] ?? false;

		// Apply filter to the specific flag's value.
		return apply_filters( "divi_experiment_flag_$flag", $flag_value );
	}

	// If no specific flag is provided, return both the experiment and debug flags.
	return [
		'experiments' => $feature_flags,
		'debug'       => $debug_flags,
	];
}
