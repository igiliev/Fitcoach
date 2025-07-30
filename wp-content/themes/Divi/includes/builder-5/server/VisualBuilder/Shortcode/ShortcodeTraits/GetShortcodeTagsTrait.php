<?php
/**
 * ShortcodeTraits: GetShortcodeTags.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Shortcode\ShortcodeTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait GetShortcodeTagsTrait {

	// TODO feat(D5, Shortcode) Move this trait into single Shortcode class under Framework https://github.com/elegantthemes/Divi/issues/31411.
	/**
	 * Get the list of registered shortcode tags.
	 *
	 * This function retrieves the list of registered shortcode tags by iterating through
	 * the global `$shortcode_tags` array and collecting the shortcode tag names.
	 *
	 * @since ??
	 *
	 * @return array An array containing the names of the registered shortcode tags.
	 *
	 * @example:
	 * ```php
	 * $shortcode_tags = ShortcodeUtility::get_shortcode_tags();
	 *
	 * foreach ( $shortcode_tags as $shortcode_tag ) {
	 *     echo $shortcode_tag; // Output each registered shortcode tag name
	 * }
	 * ```
	 */
	public static function get_shortcode_tags(): array {
		global $shortcode_tags;

		$shortcode_tag_names = array();

		foreach ( $shortcode_tags as $shortcode_tag_name => $shortcode_tag_callback ) {
			$shortcode_tag_names[] = $shortcode_tag_name;
		}

		return $shortcode_tag_names;
	}
}
