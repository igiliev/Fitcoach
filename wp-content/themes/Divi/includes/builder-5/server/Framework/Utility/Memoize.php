<?php
/**
 * Memoize class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Memoize class.
 *
 * This class provides methods for caching results based on a cache key and additional parameters.
 * It allows setting, getting, and checking the existence of cached results.
 *
 * @since ??
 */
class Memoize {

	/**
	 * The cached results data.
	 *
	 * @var array
	 */
	private static $_cached_results = [];

	/**
	 * Generates a unique cache key by combining a base key with additional parameters.
	 *
	 * If additional parameters are provided, they are JSON-encoded and hashed using MD5,
	 * then appended to the base cache key with a separator (`--`). If no additional
	 * parameters are provided, the base cache key is returned as-is.
	 *
	 * @since ??
	 *
	 * @param string $cache_key    The base cache key.
	 * @param mixed  ...$other_params Additional parameters to include in the key generation.
	 *                                These parameters will be JSON-encoded and hashed.
	 *
	 * @return string The generated unique cache key.
	 */
	public static function generate_key( $cache_key, ...$other_params ) {
		return $other_params ? $cache_key . '--' . md5( wp_json_encode( $other_params ) ) : $cache_key;
	}

	/**
	 * Sets a cache entry for the given cache key and parameters.
	 *
	 * This method stores the result in a cache array using a combination of the cache key and other parameters.
	 * If the result is null, it stores a placeholder value '{{nullValuePlaceholder}}'.
	 *
	 * @param mixed  $result The result to be cached. If null, a placeholder value is stored.
	 * @param string $cache_key The key to identify the cache entry.
	 * @param mixed  ...$other_params Additional parameters to create a unique sub-key for the cache entry.
	 * @return mixed The cached result retrieved using the cache key and parameters.
	 */
	public static function set( $result, string $cache_key, ...$other_params ) {
		$key = self::generate_key( $cache_key, ...$other_params );

		if ( null === $result ) {
			self::$_cached_results[ $key ] = '{{nullValuePlaceholder}}';
		} else {
			self::$_cached_results[ $key ] = $result;
		}

		return self::get( $cache_key, ...$other_params );
	}

	/**
	 * Retrieves a cached result based on the provided cache key and additional parameters.
	 *
	 * @param string $cache_key The primary key used to identify the cached result.
	 * @param mixed  ...$other_params Additional parameters used to form a sub-key for the cache.
	 *
	 * @return mixed The cached result if found, or null if not found or if the result is a placeholder for null.
	 */
	public static function get( string $cache_key, ...$other_params ) {
		$key    = self::generate_key( $cache_key, ...$other_params );
		$result = self::$_cached_results[ $key ] ?? null;

		if ( '{{nullValuePlaceholder}}' === $result ) {
			return null;
		}

		return $result;
	}

	/**
	 * Checks if a cache entry exists for the given cache key and additional parameters.
	 *
	 * @param string $cache_key The primary key for the cache entry.
	 * @param mixed  ...$other_params Additional parameters to form the cache sub-key.
	 * @return bool True if the cache entry exists, false otherwise.
	 */
	public static function has( string $cache_key, ...$other_params ) {
		$key = self::generate_key( $cache_key, ...$other_params );

		return isset( self::$_cached_results[ $key ] );
	}
}
