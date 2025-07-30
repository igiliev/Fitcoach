<?php
/**
 * PostUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * PostUtility class.
 *
 * This class contains methods to work with Post.
 *
 * @since ??
 */
class PostUtility {

	/**
	 * Truncate post content to generate post excerpt.
	 *
	 * @since ??
	 *
	 * @param integer $amount           Amount of text that should be kept.
	 * @param boolean $echo             Whether to print the output or not.
	 * @param object  $post             Post object.
	 * @param boolean $strip_shortcodes Whether to strip the shortcodes or not.
	 * @param boolean $is_words_length  Whether to cut the text based on words length or not.
	 *
	 * @return string Generated post post excerpt.
	 */
	public static function truncate_post( $amount, $echo = true, $post = '', $strip_shortcodes = false, $is_words_length = false ) {
		global $shortname;

		if ( empty( $post ) ) {
			global $post;
		}

		if ( post_password_required( $post ) ) {
			$post_excerpt = get_the_password_form();

			if ( $echo ) {
				echo et_core_intentionally_unescaped( $post_excerpt, 'html' );
				return;
			}

			return $post_excerpt;
		}

		$post_excerpt = apply_filters( 'the_excerpt', $post->post_excerpt );

		if ( 'on' === et_get_option( $shortname . '_use_excerpt' ) && ! empty( $post_excerpt ) ) {
			if ( $echo ) {
				echo et_core_intentionally_unescaped( $post_excerpt, 'html' );
			} else {
				return $post_excerpt;
			}
		} else {
			// get the post content.
			$truncate = $post->post_content;

			// remove caption shortcode from the post content.
			$truncate = preg_replace( '@\[caption[^\]]*?\].*?\[\/caption]@si', '', $truncate );

			// remove post nav shortcode from the post content
			// TODO fix(D5, PostUtility) Replace the et_pb_post_nav with D5's post navigation module.
			$truncate = preg_replace( '@\[et_pb_post_nav[^\]]*?\].*?\[\/et_pb_post_nav]@si', '', $truncate );

			// Remove audio shortcode from post content to prevent unwanted audio file on the excerpt
			// due to unparsed audio shortcode.
			$truncate = preg_replace( '@\[audio[^\]]*?\].*?\[\/audio]@si', '', $truncate );

			// Remove embed shortcode from post content.
			$truncate = preg_replace( '@\[embed[^\]]*?\].*?\[\/embed]@si', '', $truncate );

			// Apply the content filters to the post content to parse blocks.
			$truncate = apply_filters( 'the_content', $truncate );

			// Remove script and style tags from the post content.
			$truncate = wp_strip_all_tags( $truncate );

			if ( $strip_shortcodes ) {
				$truncate = et_strip_shortcodes( $truncate );
				$truncate = DynamicContentUtils::get_strip_dynamic_content( $truncate );
			} else {
				// Check if content should be overridden with a custom value.
				$custom = apply_filters( 'et_truncate_post_use_custom_content', false, $truncate, $post );
				// apply content filters.
				$truncate = false === $custom ? apply_filters( 'the_content', $truncate ) : $custom;
			}

			/**
			 * Filter automatically generated post excerpt before it gets truncated.
			 *
			 * @since 3.17.2
			 *
			 * @param string $excerpt
			 * @param integer $post_id
			 */
			$truncate = apply_filters( 'et_truncate_post', $truncate, $post->ID );

			// decide if we need to append dots at the end of the string.
			if ( strlen( $truncate ) <= $amount ) {
				$echo_out = '';
			} else {
				$echo_out = '...';
			}

			$trim_words = '';

			if ( $is_words_length ) {
				// Reset `$echo_out` text because it will be added by wp_trim_words() with
				// default WordPress `excerpt_more` text.
				$echo_out     = '';
				$excerpt_more = apply_filters( 'excerpt_more', ' [&hellip;]' );
				$trim_words   = wp_trim_words( $truncate, $amount, $excerpt_more );
			} else {
				$trim_words = et_wp_trim_words( $truncate, $amount, '' );
			}

			// trim text to a certain number of characters, also remove spaces from the end of a string ( space counts as a character ).
			$truncate = rtrim( $trim_words );

			// remove the last word to make sure we display all words correctly.
			if ( ! empty( $echo_out ) ) {
				$new_words_array = (array) explode( ' ', $truncate );
				// Remove last word if word count is more than 1.
				if ( count( $new_words_array ) > 1 ) {
					array_pop( $new_words_array );
				}

				$truncate = implode( ' ', $new_words_array );

				// Dots should not add to empty string.
				if ( '' !== $truncate ) {
					// append dots to the end of the string.
					$truncate .= $echo_out;
				}
			}

			if ( $echo ) {
				echo et_core_intentionally_unescaped( $truncate, 'html' );
			} else {
				return $truncate;
			}
		};
	}

	/**
	 * Get the first video URL and embed code from the post content.
	 *
	 * This fixes the issue with thumbnail video player not working, when video url is added to content without
	 * shortcode.
	 *
	 * This function is the replacement of Divi 4 function `et_get_first_video`.
	 *
	 * @since ??
	 *
	 * @return string|false First video embed code if found, false otherwise.
	 */
	public static function get_first_video() {
		$first_url    = '';
		$first_video  = '';
		$video_width  = (int) apply_filters( 'et_blog_video_width', 1080 );
		$video_height = (int) apply_filters( 'et_blog_video_height', 630 );

		$i       = 0;
		$content = get_the_content();

		preg_match_all( '|^\s*https?://[^\s"]+\s*$|im', $content, $urls );

		foreach ( $urls[0] as $url ) {
			$i++;

			if ( 1 === $i ) {
				$first_url = trim( $url );
			}

			$oembed = wp_oembed_get( esc_url( $url ) );

			if ( ! $oembed ) {
				continue;
			}

			$first_video = $oembed;
			$first_video = preg_replace( '/<embed /', '<embed wmode="transparent" ', $first_video );
			$first_video = preg_replace( '/<\/object>/', '<param name="wmode" value="transparent" /></object>', $first_video );

			// If the url comes from a GB embed block.
			if ( preg_match( '|wp-block-embed.+?' . preg_quote( $url, null ) . '|s', $content ) ) {
				// We need to remove some useless markup later.
				add_filter( 'the_content', 'et_delete_post_video' );
			}
			break;
		}

		if ( '' === $first_video ) {
			// Gutenberg compatibility.
			if ( ! has_shortcode( $content, 'video' ) && empty( $first_url ) ) {
				preg_match( '/<!-- wp:video[^\]]+?class="wp-block-video"><video[^\]]+?src="([^\]]+?)"[^\]]+?<!-- \/wp:video -->/', $content, $gb_video );
				$first_url = isset( $gb_video[1] ) ? $gb_video[1] : false;
			}

			if ( ! has_shortcode( $content, 'video' ) && ! empty( $first_url ) ) {
				$video_shortcode = sprintf( '[video src="%1$s" /]', esc_attr( $first_url ) );

				if ( ! empty( $gb_video ) ) {
					$content = str_replace( $gb_video[0], $video_shortcode, $content );
				} else {
					$content = str_replace( $first_url, $video_shortcode, $content );
				}
			}

			if ( has_shortcode( $content, 'video' ) ) {
				$regex = get_shortcode_regex();
				preg_match( "/{$regex}/s", $content, $match );

				$first_video = preg_replace( '/width="[0-9]*"/', "width=\"{$video_width}\"", $match[0] );
				$first_video = preg_replace( '/height="[0-9]*"/', "height=\"{$video_height}\"", $first_video );

				add_filter( 'the_content', 'et_delete_post_video' );

				$first_video = do_shortcode( HTMLUtility::fix_shortcodes( $first_video ) );
			}
		}

		return ( '' !== $first_video ) ? $first_video : false;
	}

	/**
	 * Delete the first video url from the post content.
	 *
	 * This function is the replacement of Divi 4 function `et_delete_post_first_video`.
	 *
	 * @since ??
	 *
	 * @param string $content post content.
	 */
	public static function delete_post_first_video( $content ) {
		if ( 'video' !== et_pb_post_format() ) {
			return $content;
		}

		$first_video = self::get_first_video();
		if ( false !== $first_video ) {
			preg_match_all( '|^\s*https?:\/\/[^\s"]+\s*|im', $content, $urls );

			if ( ! empty( $urls[0] ) ) {
				$content = str_replace( $urls[0], '', $content );
			}
		}

		return $content;
	}
}
