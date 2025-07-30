<?php
/**
 * Rest: REST API controller class abstraction.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\REST;

use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * REST API controller class abstraction.
 *
 * @since ??
 */
abstract class REST {

	/**
	 * Flag to perform nonce validation before execute callbacks.
	 *
	 * @since ??
	 *
	 * @var boolean
	 */
	protected $_validate_nonce_before_callbacks = true;

	/**
	 * Initialize the class.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	final public static function init() {
		$instance = new static();

		$instance->register_routes();

		if ( ! has_filter( 'rest_request_before_callbacks', array( $instance, 'rest_request_before_callbacks' ) ) ) {
			add_filter( 'rest_request_before_callbacks', array( $instance, 'rest_request_before_callbacks' ), 10, 3 );
		}
	}

	/**
	 * Perform additional validation before executing callbacks for a REST API request.
	 *
	 * This function is hooked to the `rest_request_before_callbacks` filter, which allows it to be run before other callbacks on a REST API request.
	 * It checks if a response has already been claimed for the request. If a response is already present, it is returned immediately.
	 * If not, it validates the route path of the request and performs additional validation steps if the route matches.
	 * If the nonce validation is enabled and the request nonce fails verification, an error response is returned.
	 * If the permission_callback is missing for the REST API route definition, a `WP_Error` object is returned with an error message.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|null $response Result to send to the client. Default null.
	 * @param array                                           $handler  Route handler used for the request.
	 * @param WP_REST_Request                                 $request  Request used to generate the response.
	 *
	 * @return WP_REST_Response|WP_HTTP_Response|WP_Error|null Result to send to the client.
	 */
	public function rest_request_before_callbacks( $response, array $handler, WP_REST_Request $request ) {
		if ( null !== $response ) {
			// Core starts with a null value.
			// If it is no longer null, another callback has claimed this request.
			return $response;
		}

		$endpoint_path  = '/' . $this->get_namespace() . '/' . static::get_endpoint();
		$request_path   = $request->get_route();
		$is_route_match = $request_path === $endpoint_path;

		if ( ! $is_route_match ) {
			$is_route_match = 0 === strpos( $request_path, $endpoint_path . '/' );
		}

		if ( $is_route_match ) {
			if ( $this->_validate_nonce_before_callbacks && ! $this->verify_nonce( $request ) ) {
				return $this->response_error_nonce();
			}

			if ( empty( $handler['permission_callback'] ) ) {
				return new WP_Error(
					'missing_permission_callback',
					wp_sprintf(
						esc_html__( 'The REST API route definition for %s is missing the required permission_callback argument.', 'et_builder' ),
						$request_path
					)
				);
			}
		}

		return $response;
	}

	/**
	 * Generate and return a `WP_REST_Response` success object with status code `200`.
	 *
	 * This function prepares a `WP_REST_Response` object with the provided data, headers, and status code `200`.
	 *
	 * @since ??
	 *
	 * @param mixed|null $data    Optional. The response data. Default `null`.
	 * @param array      $headers Optional. HTTP headers map. Default `[]`
	 *                            A default set of headers `{'content-type' => 'application/json'}` is
	 *                            always set unless the key is overwritten.
	 *
	 * @return WP_REST_Response The prepared `WP_REST_Response` object.
	 *
	 * @example
	 * ```php
	 * $response = RESTController::response_success( [ 'foo' => 'bar', 'message' => 'Success!' ] );
	 * ```
	 */
	public function response_success( $data = null, array $headers = array() ): WP_REST_Response {
		return new WP_REST_Response( $data, 200, $headers );
	}

	/**
	 * Generate and return a `WP_Error` response error object.
	 *
	 * This function prepares a `WP_Error` object with the provided data,
	 * message, error code, and status code.
	 *
	 * @since ??
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
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
	 *
	 * $response = RESTController::response_error( $code, $message, $data, $status );
	 * ```
	 */
	public function response_error( string $code, string $message, array $data = array(), int $status = 400 ): WP_Error {
		return new WP_Error(
			$code,
			$message,
			array_merge(
				$data,
				array(
					'status' => $status,
				)
			)
		);
	}

	/**
	 * Generate a server response error for insufficient permission.
	 *
	 * This function returns a `WP_Error` object with an error code and message for insufficient permission.
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
	public function response_error_permission( array $data = array() ): WP_Error {
		return $this->response_error( 'insufficient_permission', esc_html__( 'Insufficient permission.', 'et_builder' ), $data );
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
	public function response_error_nonce( array $data = array() ): WP_Error {
		return $this->response_error( 'invalid_nonce', esc_html__( 'Invalid nonce.', 'et_builder' ), $data );
	}

	/**
	 * Verify the nonce associated with the request.
	 *
	 * This function verifies the nonce included in the request's header against the stored nonce value.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return int|false 1 if the nonce is valid and generated between 0-12 hours ago,
	 *                   2 if the nonce is valid and generated between 12-24 hours ago.
	 *                   False if the nonce is invalid.
	 */
	public function verify_nonce( WP_REST_Request $request ) {
		return wp_verify_nonce( $request->get_header( 'X-ET-Nonce' ), static::get_nonce_name() );
	}

	/**
	 * Register a nonce.
	 *
	 * This function is used to register a nonce by adding it to the existing array
	 * of nonces. The nonce name is obtained from `Rest::get_nonce_name()` and
	 * the nonce is created using `Rest::create_nonce()`.
	 *
	 * @since ??
	 *
	 * @param array $nonces An associative array of registered nonces.
	 *
	 * @return array The updated array of nonces with the newly registered nonce.
	 *
	 * @example:
	 * ```php
	 * $nonces = array(
	 *     'nonce1' => '123456',
	 *     'nonce2' => '789012',
	 * );
	 *
	 * $updated_nonces = Rest::register_nonce( $nonces );
	 *
	 * // Result:
	 * // $updated_nonces = array(
	 * //     'nonce1' => '123456',
	 * //     'nonce2' => '789012',
	 * //     'new_nonce' => '654321',
	 * // );
	 * ```
	 */
	public static function register_nonce( array $nonces ): array {
		$nonce_name = static::get_nonce_name();

		return array_merge(
			$nonces,
			array(
				$nonce_name => static::create_nonce(),
			)
		);
	}

	/**
	 * Create a nonce.
	 *
	 * Creates a cryptographic token tied to a specific action (uses `REST::get_nonce_name()`), user, user session,
	 * and window of time.
	 *
	 * @since ??
	 *
	 * @return string The nonce token.
	 */
	public static function create_nonce(): string {
		return wp_create_nonce( static::get_nonce_name() );
	}

	/**
	 * Get the nonce name based on the provided namespace, route, and method.
	 *
	 * @since ??
	 *
	 * @return string Camel case of endpoint, and added prefix etRest.
	 */
	public static function get_nonce_name(): string {
		return preg_replace_callback(
			'/-(.?)/',
			function( $matches ) {
				return ucfirst( $matches[1] );
			},
			'et-rest-' . static::get_endpoint()
		);
	}

	/**
	 * Retrieves the namespace for the endpoint.
	 *
	 * This function returns the namespace for the endpoint by concatenating
	 * the string 'divi/' with the namespace version (`REST::get_namespace_version()`).
	 *
	 * @since ??
	 *
	 * @return string The namespace for the endpoint.
	 */
	public function get_namespace(): string {
		return 'divi/' . static::get_namespace_version();
	}

	/**
	 * Get the request endpoint with the specified suffix.
	 *
	 * This function returns the request endpoint with the specified suffix appended to it. The suffix should not contain any leading or trailing slashes.
	 *
	 * @see `REST::get_endpoint()`
	 *
	 * @since ??
	 *
	 * @param string $suffix The endpoint suffix to be added to the request endpoint.
	 *
	 * @return string The request endpoint with the specified suffix.
	 *
	 * @example:
	 * ```php
	 * $suffix = 'posts';
	 * $endpoint = REST::get_endpoint_with_suffix($suffix);
	 * // E.g., if the original endpoint is "/api/v1", the result will be "/api/v1/posts"
	 * ```
	 */
	public static function get_endpoint_with_suffix( string $suffix ): string {
		return static::get_endpoint() . '/' . trim( $suffix, '/' );
	}

	/**
	 * Get request endpoint.
	 *
	 * @since ??
	 *
	 * @return string The request endpoint. Expected no leading and trailing slash.
	 */
	abstract public static function get_endpoint();

	/**
	 * Get namespace version.
	 *
	 * @since ??
	 *
	 * @return string The namespace version.
	 */
	abstract public static function get_namespace_version();

	/**
	 * Register routes.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	abstract public function register_routes();
}
