<?php
/**
 * Visual Builder's Template Placeholder Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class for rendering Visual Builder's element that should be rendered on server then delivered to Visual Builder.
 *
 * @since ??
 */
class TemplatePlaceholder {
	/**
	 * Comments template cannot be generated via AJAX so prepare it beforehand.
	 *
	 * @since ??
	 */
	public static function comments() {
		global $post;

		$post_type = isset( $post->post_type ) ? $post->post_type : false;

		// Modify the Comments content for the Comment Module preview in TB.
		if ( et_theme_builder_is_layout_post_type( $post_type ) ) {
			add_filter( 'comments_open', '__return_true' );
			add_filter( 'comment_form_field_comment', 'et_builder_set_comment_fields' );
			add_filter( 'get_comments_number', 'et_builder_set_comments_number' );
			add_filter( 'comments_array', 'et_builder_add_fake_comments' );
		}

		// Modify the comments request to make sure it's unique.
		// Otherwise WP generates SQL error and doesn't allow multiple comments sections on single page.
		add_action( 'pre_get_comments', 'et_fb_modify_comments_request', 1 );

		// include custom comments_template to display the comment section with Divi style.
		add_filter( 'comments_template', 'et_fb_comments_template' );

		// Modify submit button to be advanced button style ready.
		add_filter( 'comment_form_submit_button', 'et_fb_comments_submit_button' );

		// Custom action before calling comments_template.
		do_action( 'et_fb_before_comments_template' );

		ob_start();
		comments_template( '', true );
		$comments_content = ob_get_contents();
		ob_end_clean();

		// Custom action after calling comments_template.
		do_action( 'et_fb_after_comments_template' );

		// Remove all the actions and filters to not break the default comments section from theme.
		remove_filter( 'comments_template', 'et_fb_comments_template' );
		remove_action( 'pre_get_comments', 'et_fb_modify_comments_request', 1 );

		return $comments_content;
	}
}
