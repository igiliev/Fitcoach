<?php
/**
 * Controllers: REST Controller class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Controllers;

use WP_Error;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * REST Controller class.
 *
 * @since ??
 */
abstract class RESTController {

	/**
	 * Generate and return a successful `WP_REST_Response` object.
	 *
	 * This function prepares a `WP_REST_Response` object with the provided data, headers, and status code.
	 *
	 * @since ??
	 *
	 * @param mixed|null $data    Optional. The response data. Default `null`.
	 * @param array      $headers Optional. HTTP headers map. Default `[]`
	 *                            A default set of headers `{'content-type' => 'application/json'}` is
	 *                            always set unless the key is overwritten.
	 * @param int        $status  Optional. HTTP status code. Default `200`.
	 *
	 * @return WP_REST_Response The prepared `WP_REST_Response` object.
	 *
	 * @example:
	 * ```php
	 * $response = RESTController::response_success( [ 'foo' => 'bar', 'message => 'Success!' ] );
	 * ```
	 */
	public static function response_success( $data = null, array $headers = [], int $status = 200 ): WP_REST_Response {
		$default_headers  = [ 'content-type' => 'application/json' ];
		$response_headers = array_merge( $default_headers, $headers );
		return new WP_REST_Response( $data, $status, $response_headers );
	}

	/**
	 * Generate and return a `WP_Error` response error object.
	 *
	 * This function prepares a `WP_Error` object with the provided data,
	 * message, error code and and status code.
	 *
	 * @since ??
	 *
	 * @param string $code    Optional. Error code. Default empty string.
	 * @param string $message Optional. Error message. Default empty string.
	 * @param array  $data    Optional. Error data. Default `[]`.
	 * @param int    $status  Optional. HTTP status code. Default `400`.
	 *
	 * @return WP_Error The prepared `WP_Error` object.
	 *
	 * @example:
	 * ```php
	 * $code = 'example_code';
	 * $message = 'Example message.';
	 * $data = [ 'foo' => 'bar' ];
	 * $status = 500;
	 * $response = RESTController::response_error( $code, $message, $data, $status );
	 * ```
	 */
	public static function response_error( string $code = '', string $message = '', array $data = [], int $status = 400 ): WP_Error {
		return new WP_Error(
			$code,
			$message,
			array_merge(
				$data,
				[
					'status' => $status,
				]
			)
		);
	}

	/**
	 * Generate a server response error for insufficient permission.
	 *
	 * This function returns a `WP_Error` object with an error code and message for insufficient permission.
	 * It can be used to handle cases where the user does not have sufficient permission to perform certain actions.
	 *
	 * @since ??
	 *
	 * @param array $data Optional. Error data. Default `[]`.
	 *
	 * @return WP_Error The WP_Error object representing the error response.
	 *
	 * @example:
	 * ```php
	 * $data = [ 'foo' => 'bar' ];
	 *
	 * $response = RESTController::response_error_permission( $data );
	 * ```
	 */
	public static function response_error_permission( array $data = [] ): WP_Error {
		return self::response_error( 'insufficient_permission', esc_html__( 'Insufficient permission.', 'et_builder' ), $data );
	}

	/**
	 * Generate server response error for invalid nonce.
	 *
	 * This function generates a `WP_Error` object with an error code and message for an invalid nonce.
	 * It can be used to handle cases where the provided nonce is invalid.
	 *
	 * @since ??
	 *
	 * @param array $data Optional. Error data. Default `[]`.
	 *
	 * @return WP_Error The WP_Error object representing the error response.
	 *
	 * @example:
	 * ```php
	 * $data = [ 'foo' => 'bar' ];
	 *
	 * $response = RESTController::response_error_nonce( $data );
	 * ```
	 */
	public static function response_error_nonce( array $data = [] ): WP_Error {
		return self::response_error( 'invalid_nonce', esc_html__( 'Invalid nonce.', 'et_builder' ), $data );
	}

	/**
	 * Get the nonce name based on the provided namespace, route, and method.
	 *
	 * This function concatenates the full route, namespace, and method to create a unique nonce name.
	 *
	 * @since ??
	 *
	 * @param string $namespace The namespace of the route.
	 * @param string $route     The route to get the nonce name for.
	 * @param string $method    The HTTP method used for the request.
	 *
	 * @return string The nonce name for the specified route and method.
	 *
	 * @example:
	 * ```php
	 * $namespace = 'my_namespace';
	 * $route = 'my_route';
	 * $method = 'GET';
	 * $nonceName = RESTController::get_nonce_name( $namespace, $route, $method );
	 *
	 * // Result: 'my_namespace/my_route--GET'
	 * ```
	 */
	public static function get_nonce_name( string $namespace, string $route, string $method ): string {
		return self::get_full_route( $namespace, $route ) . '--' . $method;
	}

	/**
	 * Create nonce based on give namespace, route and request method.
	 *
	 * Creates a cryptographic token tied to a specific action
	 * (uses `REST::get_nonce_name($namespace, $route, $method)`), user, user session, and window of time.
	 *
	 * @since ??
	 *
	 * @param string $namespace The REST API namespace.
	 * @param string $route     The REST API route.
	 * @param string $method    The HTTP method to use for the request.
	 *
	 * @return string The nonce token.
	 */
	public static function create_nonce( string $namespace, string $route, string $method ): string {
		return wp_create_nonce( self::get_nonce_name( $namespace, $route, $method ) );
	}

	/**
	 * Get the full route by concatenating the namespace and route.
	 *
	 * This function takes a namespace and route and concatenates them with a forward slash to form the full route.
	 *
	 * @since ??
	 *
	 * @param string $namespace The namespace of the route.
	 * @param string $route     The route to be concatenated.
	 *
	 * @return string The full route formed by concatenating the namespace and route.
	 *
	 * @example:
	 * ```php
	 * $namespace = 'my_namespace';
	 * $route = 'my_route';
	 * $fullRoute = RESTController::get_full_route( $namespace, $route );
	 *
	 * // Result: '/my_namespace/my_route'
	 * ```
	 */
	public static function get_full_route( string $namespace, string $route ): string {
		return '/' . trim( $namespace, '/' ) . '/' . trim( $route, '/' );
	}

}
