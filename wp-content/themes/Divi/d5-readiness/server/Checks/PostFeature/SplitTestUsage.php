<?php
/**
 * Class for checking if post contains split test usage that makes post content not compatible with Divi 5.
 *
 * @since ??
 *
 * @package Divi
 */

namespace Divi\D5_Readiness\Server\Checks\PostFeature;

use Divi\D5_Readiness\Server\Checks\PostFeatureCheck;

/**
 * Class for checking if post contains split test usage that makes post content not compatible with Divi 5.
 *
 * @since ??
 *
 * @package Divi
 */
class SplitTestUsage extends PostFeatureCheck {

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

		$this->_feature_name = __( 'Split Test Usage', 'et_builder' );
	}

	/**
	 * Check the post content for split test usage.
	 *
	 * @param string $content The post content.
	 */
	protected function _check_post_content( $content ) {
		$pattern = '/ab_subject="on"|ab_goal="on"/';
		preg_match_all( $pattern, $content, $matches );

		if ( empty( $matches[0] ) ) {
			return false;
		}

		$results = [
			'detected'    => true,
			'description' => __( 'Split Test usage found', 'et_builder' ),
		];

		return $results;
	}
}
