<?php
/**
 * Abstract class for post feature checking.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks;

use Divi\D5_Readiness\Server\Checks\FeatureCheck;

/**
 * Abstract class for post feature checking.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
abstract class PostFeatureCheck extends FeatureCheck {

	/**
	 * Post ID.
	 *
	 * @var int
	 *
	 * @since ??
	 */
	protected $_post_id;

	/**
	 * Post Content.
	 *
	 * @var string
	 *
	 * @since ??
	 */
	protected $_post_content;

	/**
	 * Post Meta.
	 *
	 * @var mixed
	 *
	 * @since ??
	 */
	protected $_post_meta;

	/**
	 * Constructor.
	 *
	 * @param int    $post_id      The post ID.
	 * @param string $post_content The post content.
	 * @param array  $post_meta    The post meta.
	 *
	 * @since ??
	 */
	public function __construct( $post_id, $post_content, $post_meta ) {
		$this->_post_id      = $post_id;
		$this->_post_content = $post_content;
		$this->_post_meta    = $post_meta;
	}

	/**
	 * Check the post content for a specific feature.
	 *
	 * @param string $content The post content.
	 *
	 * @return bool True if the feature was detected, false otherwise.
	 */
	protected function _check_post_content( $content ) {
		return false;
	}

	/**
	 * Check the post meta for a specific feature.
	 *
	 * @param array $meta The post meta.
	 *
	 * @return bool True if the feature was detected, false otherwise.
	 */
	protected function _check_post_meta( $meta ) {
		return false;
	}

	/**
	 * Check the post for a specific feature.
	 *
	 * @since ??
	 *
	 * @return bool True if the feature was detected, false otherwise.
	 */
	protected function _check_post() {
		$content_results = $this->_check_post_content( $this->_post_content );
		$meta_results    = $this->_check_post_meta( $this->_post_meta );

		if ( $content_results || $meta_results ) {
			return true;
		}

		return false;
	}

	/**
	 * Run the check.
	 *
	 * @since ??
	 */
	public function run_check() {
		$this->_detected = $this->_check_post();
	}
}
