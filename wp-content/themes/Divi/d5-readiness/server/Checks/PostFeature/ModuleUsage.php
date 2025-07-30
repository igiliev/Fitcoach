<?php
/**
 * Class for checking if post contains modules that makes post content not compatible with Divi 5.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks\PostFeature;

/**
 * Class for checking if post contains modules that makes post content not compatible with Divi 5.
 *
 * @package D5_Readiness
 */

use Divi\D5_Readiness\Server\Checks\PostFeatureCheck;

/**
 * Class for checking if post contains modules that makes post content not compatible with Divi 5.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class ModuleUsage extends PostFeatureCheck {

	/**
	 * Module's post feature check results.
	 *
	 * @var array
	 *
	 * @since ??
	 */
	protected $_module_results = [];

	/**
	 * Constructor.
	 *
	 * @param int    $post_id      The post ID.
	 * @param string $post_content The post content.
	 * @param array  $post_meta    The post meta.
	 *
	 * @return void
	 */
	public function __construct( $post_id, $post_content, $post_meta ) {
		$this->_post_id      = $post_id;
		$this->_post_content = $post_content;
		$this->_post_meta    = $post_meta;

		$this->_feature_name = __( 'Module Use', 'et_builder' );
	}

	/**
	 * Check the post content for certain module use.
	 *
	 * @param string $content The post content.
	 *
	 * @return bool True if any checked for module was detected, false otherwise.
	 */
	protected function _check_post_content( $content ) {
		global $wp_filter;

		// Execute actions where third party modules are initialized.
		// Without this the third party modules hasn't registered and list of third party modules hasn't been populated.
		do_action( 'divi_extensions_init' );
		do_action( 'et_builder_ready' );

		$module_results = [];

		// No need to list all WooCommerce modules; Module slug prefix is enough.
		$woocommerce_module_slugs = [
			'et_pb_wc_' => __( 'WooCommerce Module', 'et_builder' ),
		];

		et_load_shortcode_framework();

		// Get list of third party module slugs.
		$third_party_module_slugs = array_map(
			function( $module ) {
				return $module['name'];
			},
			\ET_Builder_Element::get_third_party_modules()
		);

		$module_slugs = array_merge(
			$woocommerce_module_slugs,
			$third_party_module_slugs
		);

		// Loop over all the module slugs and check if they are present in the content.
		foreach ( $module_slugs as $slug => $module_name ) {
			if ( strpos( $content, $slug ) !== false ) {
				$module_results[] = $module_name;
			}
		}

		if ( empty( $module_results ) ) {
			return false;
		}

		$this->_module_results = $module_results;

		$results = [
			'detected'    => count( $module_results ) > 0,
			'description' => 'Modules found: ' . implode( ', ', $module_results ),
		];

		return $results;
	}

	/**
	 * Get feature name.
	 *
	 * @since ??
	 */
	public function get_feature_name() {
		// implode the module results and return them, appending "Use" at the end of each module name.
		$module_results = array_map(
			function( $module ) {
				return $module . ' Use';
			},
			$this->_module_results
		);

		return implode( ', ', $module_results );
	}
}
