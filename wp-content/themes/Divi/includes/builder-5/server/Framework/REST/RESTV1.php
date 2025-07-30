<?php
/**
 * Rest: REST API V1 controller class abstraction.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\REST;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\REST\REST;

/**
 * REST API V1 controller class abstraction.
 *
 * @since ??
 */
abstract class RESTV1 extends REST {

	/**
	 * Get the namespace version.
	 *
	 * This function retrieves the version of the namespace used in the endpoint.
	 *
	 * @since ??
	 *
	 * @return string The version of the namespace used in the endpoint.
	 */
	public static function get_namespace_version(): string {
		return 'v1';
	}
}
