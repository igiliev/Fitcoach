<?php
/**
 * REST: REST Nonce class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * REST: REST Nonce class.
 *
 * @since ??
 */
class Nonce {

	/**
	 * The nonces for the REST API.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_data = [];

	/**
	 * Retrieves registered nonces for the REST API.
	 *
	 * This function retrieves the nonces for the REST API from the class property `$_data`.
	 * The nonces are then filtered using the `divi_visual_builder_rest_nonces` filter, allowing third
	 * parties to modify the nonce data.
	 *
	 * @since ??
	 *
	 * @return array The nonces for the REST API.
	 *
	 * @example:
	 * ```php
	 * $nonces = Nonce::get_data();
	 * ```
	 */
	public static function get_data(): array {
		$data_to_return = self::$_data;

		/**
		 * Filter to modify the list of nonces for the REST API.
		 *
		 * @since ??
		 *
		 * @param array $data_to_return The nonces for the REST API.
		 */
		return apply_filters( 'divi_visual_builder_rest_nonces', $data_to_return );
	}

	/**
	 * Add nonce data to the class property `$_data`.
	 *
	 * This function adds data to the `$_data` property based on the given path, method, and nonce.
	 * If the given path does not exist in `$_data`, a new empty array will be created for that path.
	 * The method and nonce will then be added to the appropriate key-value pair in `$_data`.
	 *
	 * @since ??
	 *
	 * @param string $path   The path to store the data in.
	 * @param string $method The HTTP method associated with the data.
	 * @param string $nonce  The nonce value for the data.
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * $path = 'example_path';
	 * $method = 'GET';
	 * $nonce = 'example_nonce';
	 * Nonce::add_data( $path, $method, $nonce );
	 * ```
	 */
	public static function add_data( string $path, string $method, string $nonce ): void {
		if ( ! isset( self::$_data[ $path ] ) ) {
			self::$_data[ $path ] = [];
		}

		self::$_data[ $path ][ $method ] = $nonce;
	}

}
