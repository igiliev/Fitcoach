<?php
/**
 * Video: VideoController.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Video;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Framework\UserRole\UserRole;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Video REST Controller class.
 *
 * @since ??
 */
class VideoHTMLController extends RESTController {

	/**
	 * Get the HTML markup for a video using the provided arguments.
	 *
	 * This function takes an array of arguments and generates the HTML markup for displaying a video.
	 * The function first checks if either the `'src'` or `'src_webm'` argument is empty. If both are empty, an empty string is returned.
	 * If a valid oEmbed provider is found for the `'src'` argument, the oEmbed HTML is retrieved using `et_builder_get_oembed()` function.
	 * If the `'src'` argument is a YouTube URL, it is normalized and then the oEmbed HTML is retrieved.
	 * If none of the above conditions are met, HTML markup of a video tag element with one or two source tag elements is generated.
	 * The `'src'` argument is used as the source for the 'video/mp4' format, and the `'src_webm'` argument is used for the 'video/webm' format.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. Array of arguments for generating the video HTML markup. Default `[]`.
	 *
	 *     @type string $src      Optional. The source URL of the video in 'video/mp4' format. Default empty string.
	 *     @type string $src_webm Optional. The source URL of the video in 'video/webm' format. Default empty string.
	 * }
	 * @return string The HTML markup for the video.
	 *
	 * @example:
	 * ```php
	 *     // Example usage within a class method.
	 *     $video_html = ClassName::get_video_html( [
	 *         'src'      => 'https://example.com/video.mp4',
	 *         'src_webm' => 'https://example.com/video.webm',
	 *     ] );
	 *
	 *     // Example usage with default arguments.
	 *     $video_html = get_video_html();
	 *
	 *     // Example usage with empty arguments.
	 *     $video_html = get_video_html( [] );
	 * ```
	 * @example:
	 * ```php
	 *     // Example usage with the 'src' argument only.
	 *     $video_html = get_video_html( [
	 *         'src' => 'https://example.com/video.mp4',
	 *     ] );
	 * ```
	 *
	 * @example:
	 * ```php
	 *     // Example usage with the 'src_webm' argument only.
	 *     $video_html = get_video_html( [
	 *         'src_webm' => 'https://example.com/video.webm',
	 *     ] );
	 * ```
	 */
	public static function get_video_html( array $args = array() ): string {
		$defaults = [
			'src'      => '',
			'src_webm' => '',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['src'] ) && empty( $args['src_webm'] ) ) {
			return '';
		}

		$video_src = '';

		if ( false !== et_pb_check_oembed_provider( esc_url( $args['src'] ) ) ) {
			$video_src = et_builder_get_oembed( esc_url( $args['src'] ) );
		} elseif ( false !== self::validate_youtube_url( esc_url( $args['src'] ) ) ) {
			$args['src'] = self::normalize_youtube_url( esc_url( $args['src'] ) );
			$video_src   = et_builder_get_oembed( esc_url( $args['src'] ) );
		} else {
			$video_src = sprintf(
				'
				<video controls>
					%1$s
					%2$s
				</video>',
				( '' !== $args['src'] ? sprintf( '<source type="video/mp4" src="%s" />', esc_url( $args['src'] ) ) : '' ),
				( '' !== $args['src_webm'] ? sprintf( '<source type="video/webm" src="%s" />', esc_url( $args['src_webm'] ) ) : '' )
			);
		}

		return $video_src;
	}

	/**
	 * Get the YouTube URL regex pattern.
	 *
	 * This function returns the regex pattern for matching a YouTube URL from any known/common YouTube URL format.
	 * The known formats include:
	 * - https://www.youtube.com/watch?v=XXXX
	 * - https://www.youtube.com/embed/XXXX
	 * - https://youtu.be/XXXX
	 *
	 * The regex pattern is case-insensitive and follows this format:
	 * /^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i
	 *
	 * To test the regex pattern, visit: https://regex101.com/r/4FbeMZ/1
	 *
	 * @since ??
	 *
	 * @return string The YouTube video URL regex pattern.
	 */
	public static function get_youtube_url_regex(): string {
		return '/^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i';
	}

	/**
	 * Normalize a YouTube URL from any known/common YouTube URL format.
	 *
	 * This function converts a YouTube URL into its normalized form: https://www.youtube.com/watch?v=XXXX.
	 *
	 * It supports the following YouTube URL formats:
	 * - https://www.youtube.com/watch?v=XXXX {@link https://regex101.com/r/B2qLJy/1 see regex}
	 * - https://www.youtube.com/embed/XXXX {@link https://regex101.com/r/oZ3iNP/1 see regex}
	 * - https://youtu.be/XXXX {@link https://regex101.com/r/5nqmhF/1 see regex}
	 *
	 * @since ??
	 *
	 * @param string $url The YouTube video URL.
	 *
	 * @return string The normalized YouTube URL.
	 */
	public static function normalize_youtube_url( string $url ): string {
		preg_match( self::get_youtube_url_regex(), esc_url( $url ), $youtube_embed_video );

		return 'https://www.youtube.com/watch?v=' . $youtube_embed_video[1];
	}


	/**
	 * Validate a YouTube URL.
	 *
	 * This function validates a YouTube URL to check if it is in a known/common YouTube URL format.
	 * The known formats include:
	 * - https://www.youtube.com/watch?v=XXXX {@link https://regex101.com/r/B2qLJy/1 see regex}.
	 * - https://www.youtube.com/embed/XXXX {@link https://regex101.com/r/oZ3iNP/1 see regex}.
	 * - https://youtu.be/XXXX {@link https://regex101.com/r/5nqmhF/1 see regex}.
	 *
	 * Regular expressions are used to match against the provided URL formats.
	 *
	 * @param string $url YouTube video URL.
	 *
	 * @return bool Returns `true` if the YouTube URL is valid, `false` otherwise.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $url = 'https://www.youtube.com/watch?v=XXXX';
	 *
	 * $isValid = VideoHTMLGeneration::validate_youtube_url( $url );
	 * if ( $isValid ) {
	 *     echo 'The YouTube URL is valid.';
	 * } else {
	 *     echo 'The YouTube URL is invalid.';
	 * }
	 * ```
	 */
	public static function validate_youtube_url( string $url ): bool {
		preg_match( self::get_youtube_url_regex(), $url, $youtube_embed_video );

		return is_array( $youtube_embed_video ) && ! empty( $youtube_embed_video );
	}

	/**
	 * Generates the HTML markup for a video using the provided arguments.
	 *
	 * This function takes an array of arguments and generates the HTML markup for displaying a video.
	 * If both the `'src'` and `'src_webm'` arguments are empty, an empty string is returned.
	 * If a valid oEmbed provider is found for the `'src'` argument, the oEmbed HTML is retrieved using the `'et_builder_get_oembed()'` function.
	 * If the `'src' `argument is a YouTube URL, it is normalized and then the oEmbed HTML is retrieved.
	 * If none of the above conditions are met, HTML markup of a video tag element with one or two source tag elements is generated.
	 * The `'src'` argument is used as the source for the `'video/mp4'` format, and the `'src_webm'` argument is used for the 'video/webm' format.
	 *
	 * @since ??
	 *
	 * @param WP_REST_Request $request {
	 *     A REST request object.
	 *
	 *     @type string $src     Optional. The source URL of the video in 'video/mp4' format.
	 *     @type string $srcWebm Optional. The source URL of the video in 'video/webm' format.
	 * }
	 *
	 * @return WP_REST_Response|WP_Error A `WP_REST_Response` object with the HTML markup for the video
	 *  `                                or a `WP_Error` object on failure.
	 *
	 * @example:
	 * ```php
	 *     // Example usage within a class method.
	 *     $video_html = VideoHTMLController::get_video_html( [
	 *         'src'      => 'https://example.com/video.mp4',
	 *         'src_webm' => 'https://example.com/video.webm',
	 *     ] );
	 *
	 *     // Example usage with default arguments.
	 *     $video_html = VideoHTMLController::get_video_html();
	 *
	 *     // Example usage with empty arguments.
	 *     $video_html = VideoHTMLController::get_video_html( [] );
	 *
	 *     // Example usage with the 'src' argument only.
	 *     $video_html = VideoHTMLController::get_video_html( [
	 *         'src' => 'https://example.com/video.mp4',
	 *     ] );
	 *
	 *     // Example usage with the 'src_webm' argument only.
	 *     $video_html = VideoHTMLController::get_video_html( [
	 *         'src_webm' => 'https://example.com/video.webm',
	 *     ] );
	 * ```
	 */
	public static function index( WP_REST_Request $request ) {
		$args = [
			'src'      => $request->get_param( 'src' ),
			'src_webm' => $request->get_param( 'srcWebm' ),
		];

		$output = self::get_video_html( $args );

		return self::response_success( $output );
	}

	/**
	 * Get the index action arguments.
	 *
	 * This method returns an array of arguments that can be used in the `register_rest_route()` function
	 * to define the necessary parameters for the index action
	 * The index action allows the user to retrieve dynamic content options based on the provided postId parameter.
	 *
	 * @since ??
	 *
	 * @return array An array of arguments for the index action.
	 */
	public static function index_args(): array {
		return [
			'src'     => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => function( $value, $request, $param ) {
					return esc_url( sanitize_text_field( $value ) );
				},
				'validate_callback' => function ( $value, $request, $key ) {
					// When value is set, validate it's a URL, or, an empty string.
					return isset( $key ) && ! empty( $value )
						? wp_http_validate_url( $value )
						: isset( $key ) && is_string( $value );
				},
			],
			'srcWebm' => [
				'default'           => '',
				'type'              => 'string',
				'sanitize_callback' => function( $value, $request, $param ) {
					return esc_url( sanitize_text_field( $value ) );
				},
				'validate_callback' => function ( $value, $request, $key ) {
					// When value is set, validate it's a URL, or, an empty string.
					return isset( $key ) && ! empty( $value )
						? wp_http_validate_url( $value )
						: isset( $key ) && is_string( $value );
				},
			],
		];
	}


	/**
	 * Index action permission.
	 *
	 * Checks if the current user has permission to use the VisualBuilder (VB).
	 * This function is used in `register_rest_route()`.
	 *
	 * @since ??
	 *
	 * @return bool Whether the current user has permission to use the VisualBuilder (VB).
	 */
	public static function index_permission(): bool {
		return UserRole::can_current_user_use_visual_builder();
	}

}
