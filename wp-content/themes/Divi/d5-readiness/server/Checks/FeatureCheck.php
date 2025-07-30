<?php
/**
 * Abstract class for feature checking.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks;

/**
 * Abstract class for feature checking.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
abstract class FeatureCheck {
	/**
	 * Feature name.
	 *
	 * @var string
	 *
	 * @since ??
	 */
	protected $_feature_name = 'Feature Name';

	/**
	 * Detected flag.
	 *
	 * @var array|bool
	 *
	 * @since ??
	 */
	protected $_detected = false;

	/**
	 * Check if the feature was detected.
	 *
	 * @since ??
	 */
	public function detected() {
		return $this->_detected;
	}

	/**
	 * Get the feature name.
	 *
	 * @since ??
	 */
	public function get_feature_name() {
		return $this->_feature_name;
	}
}
