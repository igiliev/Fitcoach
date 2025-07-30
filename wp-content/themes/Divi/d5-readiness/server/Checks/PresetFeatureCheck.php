<?php
/**
 * Class for checking if preset exist.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks;

use Divi\D5_Readiness\Server\Checks\FeatureCheck;

use ET\Builder\Packages\GlobalData\GlobalPreset;

/**
 * Class for checking if preset exist.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class PresetFeatureCheck extends FeatureCheck {

	/**
	 * Constructor.
	 *
	 * @since ??
	 */
	public function __construct() {
		$this->_feature_name = __( 'Presets', 'et_builder' );
	}

	/**
	 * Check preset exist.
	 *
	 * @since ??
	 *
	 * @return bool Boolean value for preset exist.
	 */
	protected function _check_preset_exist() {
		if ( empty( GlobalPreset::get_legacy_data() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Run preset check.
	 *
	 * @since ??
	 */
	public function run_check() {
		$this->_detected = $this->_check_preset_exist();
	}
}
