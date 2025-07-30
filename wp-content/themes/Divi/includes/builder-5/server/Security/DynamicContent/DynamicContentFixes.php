<?php
/**
 * Module: DynamicContentFixes class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Security\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicData;

/**
 * Module: DynamicContentFixes class.
 *
 * This class provides a set of security fixes for dynamic content.
 *
 * @since ??
 */
class DynamicContentFixes {
	/**
	 * Disable html for dynamic content.
	 *
	 * We need to disable the enable_html flag from the dynamic content item,
	 * and then re-encode it and put the new value back in the post content.
	 *
	 * @since ??
	 *
	 * @param array $data  An array of slashed post data.
	 *
	 * @return array $data Modified post data.
	 */
	public static function disable_html( $data ) {

		$post_content = wp_unslash( $data['post_content'] );
		$replace      = array();
		$bad          = '\u0022enable_html\u0022:\u0022on\u0022';
		$good         = '\u0022enable_html\u0022:\u0022off\u0022';
		// Find all dynamic content items in the post content.
		foreach ( DynamicData::get_variable_values( $post_content ) as $original ) {
			// Remove the enable_html flag from the dynamic content item.
			$replace[ $original ] = str_replace( $bad, $good, $original );
		}
		// Perform the replacements and update the post content.
		$data['post_content'] = wp_slash( strtr( $post_content, $replace ) );

		return $data;
	}
}
