<?php
/**
 * Module: BackgroundComponentVideo class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;

/**
 * BackgroundComponentVideo class.
 *
 * This class contains functionality for managing the background component video.
 *
 * @since ??
 */
class BackgroundComponentVideo {

	/**
	 * Render component for the background video element.
	 *
	 * This function takes an array of arguments and returns a HTML markup for the background video component.
	 * The component includes a video element with specified attributes and a parent span element with a class for styling.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundComponentVideo BackgroundComponentVideo} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $className            Optional. The class name for the parent span element. Default empty string.
	 *     @type string $mp4                  Optional. The URL of the MP4 video source. Default value is retrieved from the Background class.
	 *     @type string $webm                 Optional. The URL of the WebM video source. Default value is retrieved from the Background class.
	 *     @type int    $width                Optional. The width of the video element. Default value is retrieved from the Background class.
	 *     @type int    $height               Optional. The height of the video element. Default value is retrieved from the Background class.
	 *     @type string $allowPlayerPause     Optional. Whether to allow the player to pause the video.
	 *                                        Default value is retrieved from the Background class.
	 *     @type string $pauseOutsideViewport Optional. Whether to pause the video when it is outside the viewport.
	 *                                        Default value is retrieved from the Background class.
	 * }
	 *
	 * @return string The HTML markup for the background video component.
	 *                Returns empty string if there is no video source.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'className'            => 'custom-class',
	 *     'mp4'                  => 'http://example.com/video.mp4',
	 *     'webm'                 => 'http://example.com/video.webm',
	 *     'width'                => 800,
	 *     'height'               => 600,
	 *     'allowPlayerPause'     => 'on',
	 *     'pauseOutsideViewport' => 'off',
	 * ];
	 * $result = BackgroundComponentVideo::component( $args );
	 *
	 * // The resulting HTML markup will include a video element with the specified attributes and a parent span element with the `'et-pb-background-video'` class.
	 * ```
	 *
	 * @output:
	 * ```php
	 * <span class="et-pb-background-video custom-class"></span>
	 * ```
	 */
	public static function component( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'className'            => '',
				'mp4'                  => Background::$background_default_attr['video']['mp4'],
				'webm'                 => Background::$background_default_attr['video']['webm'],
				'width'                => Background::$background_default_attr['video']['width'],
				'height'               => Background::$background_default_attr['video']['height'],
				'allowPlayerPause'     => Background::$background_default_attr['video']['allowPlayerPause'],
				'pauseOutsideViewport' => Background::$background_default_attr['video']['pauseOutsideViewport'],
			]
		);

		$has_value = ! empty( $args['mp4'] ) || ! empty( $args['webm'] );

		// Return empty string if there is no video source.
		if ( ! $has_value ) {
			return '';
		}

		// Get video element.
		$background_video_html = self::render_background_video(
			[
				'mp4'    => $args['mp4'],
				'webm'   => $args['webm'],
				'width'  => $args['width'],
				'height' => $args['height'],
			]
		);

		return HTMLUtility::render(
			[
				'tag'               => 'span',
				'attributes'        => [
					'class' => HTMLUtility::classnames(
						[
							'et-pb-background-video'   => true,
							// TODO feat(D5, Refactor): Remove `et_pb_allow_player_pause`, currently it is not being used.
							'et_pb_allow_player_pause' => 'on' === $args['allowPlayerPause'],
							'et-pb-background-video--play-outside-viewport' => 'off' === $args['pauseOutsideViewport'],
							$args['className']         => ! empty( $args['className'] ),
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $background_video_html,
			]
		);
	}

	/**
	 * Container function that retrieves the background style and enable status based on the provided arguments.
	 *
	 * This function iterates through all the breakpoints and states in the provided `$args['backgroundAttr']` array
	 * and finds the first attribute with a non-empty `style` value and `enabled` set to `'on'`.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundComponentVideoContainer BackgroundComponentVideoContainer} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array $backgroundAttr The background attributes for different breakpoints and states. Default `[]`.
	 * }
	 *
	 * @return string The container component.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'backgroundAttr' => [
	 *         'desktop' => [
	 *             'normal' => [
	 *                 'mask' => [
	 *                     'style'   => 'background-color: red;',
	 *                     'enabled' => 'on',
	 *                 ],
	 *             ],
	 *         ],
	 *         'mobile'  => [
	 *             'hover' => [
	 *                 'mask' => [
	 *                     'style'   => 'background-color: blue;',
	 *                     'enabled' => 'on',
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 * ];
	 * $result = BackgroundComponentMask::container( $args );
	 *
	 * // The example uses an array with two breakpoints ('desktop' and 'mobile') and two states ('normal' and 'hover').
	 * // The function will return the background style and enable status for the attribute with a non-empty `style` value and `enabled` set to 'on'.
	 * // In this case, the resulting style will be 'background-color: red;' and the enable status will be 'on'.
	 * ```
	 */
	public static function container( array $args ): string {
		$attr   = $args['backgroundAttr'] ?? [];
		$return = '';

		// The logic to determine the `$mp4` and `$webm` value in FE is a bit of different from the logic in VB.
		// In VB, we can directly get the attribute values from current active `breakpoint` and `state`, but that
		// is not the case in FE. Hence, we need to iterate all the breakpoints and the states.
		foreach ( $attr as $breakpoint => $states ) {
			foreach ( array_keys( $states ) as $state ) {
				$mp4  = $states[ $state ]['video']['mp4'] ?? '';
				$webm = $states[ $state ]['video']['webm'] ?? '';

				if ( ! empty( $mp4 ) || ! empty( $webm ) ) {
					$attr_value = ModuleUtils::use_attr_value(
						[
							'attr'       => $attr,
							'breakpoint' => $breakpoint,
							'state'      => $state,

							// Fetch value only when it's enabled for the breakpoint/state.
							'mode'       => 'getAndInheritAll',
						]
					);

					$classname              = self::get_video_classname( $breakpoint, $state );
					$width                  = $attr_value['video']['width'] ?? '';
					$height                 = $attr_value['video']['height'] ?? '';
					$allow_player_pause     = $attr_value['video']['allowPlayerPause'] ?? Background::$background_default_attr['video']['allowPlayerPause'];
					$pause_outside_viewport = $attr_value['video']['pauseOutsideViewport'] ?? Background::$background_default_attr['video']['pauseOutsideViewport'];

					// Render the video background component.
					$return .= self::component(
						array_merge(
							$args,
							[
								'className'            => $classname,
								'mp4'                  => $mp4,
								'webm'                 => $webm,
								'width'                => $width,
								'height'               => $height,
								'allowPlayerPause'     => $allow_player_pause,
								'pauseOutsideViewport' => $pause_outside_viewport,
							]
						)
					);
				}
			}
		}

		return $return;
	}

	/**
	 * Get the video background classname based on the given breakpoint and state.
	 *
	 * This function appends the appropriate suffix to the classname based on the values
	 * of the `$breakpoint` and `$state` parameters.
	 * If the `$breakpoint` is not equal to 'desktop', the suffix "_{$breakpoint}" is appended.
	 * If the `$state` is not equal to 'value', the suffix "__{$state}" is appended.
	 *
	 * The root classname for CSS video in D4 is `'et_pb_section_video_bg'`.
	 * However, since D4 scripts are still enqueued and automatically implement themselves on this classname
	 * on page load, using the same classname can potentially cause conflicts.
	 * Therefore, this function renames the classname to 'et-pb-background-video'.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/GetVideoClassName getVideoClassName} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param string $breakpoint The attribute breakpoint. One of `desktop`, `tablet`, or `mobile`.
	 * @param string $state      The attribute state. One of `active`, `hover`, `disabled`, or `value`.
	 *
	 * @return string The generated video background classname.
	 *
	 * @example:
	 * ```php
	 *   $breakpoint = 'mobile';
	 *   $state = 'hover';
	 *   $class = BackgroundComponentVideo::get_video_classname($breakpoint, $state);
	 *
	 *   // Output: 'et-pb-background-video_mobile__hover'
	 * ```
	 */
	public static function get_video_classname( string $breakpoint, string $state ): string {
		$breakpoint_suffix = 'desktop' === $breakpoint ? '' : "_{$breakpoint}";
		$state_suffix      = 'value' === $state ? '' : "__{$state}";

		// The root classname for css video in D4 is `et_pb_section_video_bg`. Since D4 scripts is still
		// enqueued and automatically implement itself on this classname on page load, using the same classname create
		// possible cause of conflict. Thus, it is being renamed into `et-pb-background-video`.
		return "et-pb-background-video{$breakpoint_suffix}{$state_suffix}";
	}

	/**
	 * Render video element for background.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $mp4    Optional. MP4 URL. Default empty string.
	 *     @type string $webm   Optional. Webm URL. Default empty string.
	 *     @type string $width  Optional. Video Width. Default empty string.
	 *     @type string $height Optional. Video Height. Default empty string.
	 * }
	 *
	 * @return string
	 */
	public static function render_background_video( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'mp4'    => '',
				'webm'   => '',
				'width'  => '',
				'height' => '',
			]
		);

		$has_value = ! empty( $args['mp4'] ) || ! empty( $args['webm'] );

		// Return empty string if there is no video source.
		if ( ! $has_value ) {
			return '';
		}

		$video_attributes = [
			'autoplay'    => true,
			'loop'        => true,
			'playsinline' => true,
			'muted'       => true,
		];

		if ( $args['width'] ) {
			// width element expects number for pixel value.
			$video_attributes['width'] = (int) $args['width'];
		}

		if ( $args['height'] ) {
			// height element expects number for pixel value.
			$video_attributes['height'] = (int) $args['height'];
		}

		return HTMLUtility::render(
			[
				'tag'               => 'video',
				'attributes'        => $video_attributes,
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					! empty( $args['mp4'] ) ? HTMLUtility::render(
						[
							'tag'               => 'source',
							'attributes'        => [
								'type'      => 'video/mp4',
								'src'       => $args['mp4'],
								'sanitizer' => 'esc_url',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
						]
					) : '',
					! empty( $args['webm'] ) ? HTMLUtility::render(
						[
							'tag'               => 'source',
							'attributes'        => [
								'type'      => 'video/webm',
								'src'       => $args['webm'],
								'sanitizer' => 'esc_url',
							],
							'childrenSanitizer' => 'et_core_esc_previously',
						]
					) : '',
				],
			]
		);
	}
}
