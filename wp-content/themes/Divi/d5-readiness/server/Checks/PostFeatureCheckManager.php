<?php
/**
 * Class for managing post feature checks.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks;

/**
 * This file handles AJAX requests for the Divi 5 Readiness System.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class PostFeatureCheckManager {

	/**
	 * Array of registered feature check classes.
	 *
	 * @var array
	 */
	private $_checks = array();

	/**
	 * Register a new feature check class.
	 *
	 * @param string $check_class The name of the feature check class to register.
	 */
	public function register_check( $check_class ) {
		if ( ! class_exists( $check_class ) ) {
			return;
		}

		$this->_checks[] = $check_class;
	}

	/**
	 * Run all registered feature checks and return the results.
	 *
	 * @param int $post_id The post ID to run the checks on.
	 * @param string $content The post content to run the checks on.
	 *
	 * @return mixed False if no features were detected, an array detailing detected features otherwise.
	 */
	public function run_all_checks( $post_id, $content ) {
		$failed_checks = array();

		$meta      = get_post_meta( $post_id );
		$post_type = get_post_type( $post_id );

		foreach ( $this->_checks as $check_class ) {
			$check_instance = new $check_class( $post_id, $content, $meta, $post_type );
			$check_instance->run_check();
			$check_instance->get_feature_name();

			if ( $check_instance->detected() ) {
				$failed_checks[] = $check_instance->get_feature_name();
			}
		}

		if ( empty( $failed_checks ) ) {
			return false;
		} else {
			$results = [
				'detected'    => count( $failed_checks ) > 0,
				'description' => '' . implode( ', ', $failed_checks ),
			];

			return $results;
		}
	}
}
