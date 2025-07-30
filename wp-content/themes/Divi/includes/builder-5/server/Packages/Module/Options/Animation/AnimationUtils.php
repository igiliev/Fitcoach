<?php
/**
 * Module Options: Animation Utils Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Animation;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\FeaturesManager\FeaturesManager;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * Module Options: Animation Utils
 */
class AnimationUtils {

	/**
	 * Get the class names for an animated element based on the provided attributes.
	 *
	 * This function checks if animations are enabled and the 'anim' feature is enabled via
	 * `AnimationUtils::is_enabled( $attr ) || ! FeaturesManager::get( 'anim' )`.
	 * If this returns false, an empty string is returned, otherwise `et_animated` is returned.
	 *
	 * @since ??
	 *
	 * @param array $attr The attributes used to determine the class names.
	 *
	 * @return string The class names for the animated element
	 *                 If animations are not enabled or the 'anim' feature is not enabled, it returns an empty string.
	 *
	 * @example:
	 * ```php
	 * $attr = array(
	 *     'animation' => 'fade',
	 *     'duration' => 1000,
	 *     'delay' => 500,
	 * );
	 * $class_names = AnimationUtils::classnames($attr);
	 * ```
	 * @output:
	 * ```php
	 *  'et_animated'
	 * ```
	 */
	public static function classnames( array $attr ): string {
		if ( ! self::is_enabled( $attr ) || ! FeaturesManager::get( 'anim' ) ) {
			return '';
		}

		return 'et_animated';
	}

	/**
	 * Generate animation data.
	 *
	 * This function generates the data for animations based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id            Optional. The module ID. Example: `divi/cta-0`. Default empty string.
	 *     @type string $selector      Optional. The module selector. Example: `.et_pb_cta_0`. Default empty string.
	 *     @type array  $attr          Optional. The module attributes for animation. Default `[]`.
	 *     @type mixed  $storeInstance Optional. The ID of the instance where this block is stored in the BlockParserStore. Default `null`.
	 * }
	 *
	 * @return array The generated animation data.
	 *               If the animation presets are not available or enabled, an empty array is returned.
	 *
	 * @example:
	 * ```php
	 * $args = [
	 *     'id'            => 'divi/cta-0',
	 *     'selector'      => '.et_pb_cta_0',
	 *     'attr'          => [
	 *         'duration' => 1000,
	 *         'delay' => 200
	 *     ],
	 *     'storeInstance' => 123,
	 * ];
	 *
	 * $animationData = AnimationUtils::generate_data( $args );
	 * ```
	 */
	public static function generate_data( array $args ): array {
		$args = wp_parse_args(
			$args,
			[
				'id'            => '',
				'selector'      => '',
				'attr'          => [],

				// FE only.
				'storeInstance' => null,
			]
		);

		$data = [];

		foreach ( array_keys( $args['attr'] ) as $breakpoint ) {
			// Generate Tablet & Phone data attributes. As default, tablet
			// default value will inherit desktop value and phone default value will inherit
			// tablet value.
			$attrs = ModuleUtils::use_attr_value(
				[
					'attr'       => $args['attr'],
					'breakpoint' => $breakpoint,
					'state'      => 'value',
					'mode'       => 'getAndInheritAll',
				]
			);

			// Get animation presets based on selected style.
			$presets = self::_get_presets( $attrs['style'] ?? 'none' );

			if ( ! $presets ) {
				continue;
			}

			$key_suffix = 'desktop' === $breakpoint ? '' : '_' . $breakpoint;

			foreach ( $presets as $attr_name => $preset ) {
				if ( isset( $preset['skip'] ) && true === $preset['skip'] ) {
					continue;
				}

				$value = $attrs[ $attr_name ] ?? $preset['default'];

				if ( isset( $preset['filter_value'] ) && is_callable( $preset['filter_value'] ) ) {
					$value = call_user_func( $preset['filter_value'], $value, $attrs, $presets );
				}

				$data[ $preset['key'] . $key_suffix ] = $value;
			}
		}

		if ( $data ) {
			$data['class'] = ltrim( $args['selector'], '.' );
		}

		/**
		* Filter module animation data.
		*
		* @internal Button module will need this filter to change the `style` value to `transformAnim`.
		*
		* {@link https://github.com/elegantthemes/submodule-builder/blob/90d68ab450dc6f8c4e5516a8c9fe8f114845020e
		* /class-et-builder-element.php#L3005 class-et-builder-element}
		*
		* @since ??
		*
		* @param array $data Original module animation data.
		* @param array $args The arguments that being passed to the `AnimationUtils::generate_data()` function.
		*/
		$data = apply_filters( 'divi_module_options_animation_data', $data, $args );

		return $data;
	}

	/**
	 * Get allowed options based on the provided animation style.
	 *
	 * This function takes an animation style as the input and returns an array
	 * of options associated with that style. The available animation styles are
	 * `fade`, `bounce`, `slide`, `zoom`, `flip`, `fold`, `roll`, and `none`.
	 *
	 * @since ??
	 *
	 * @param string $style The animation style.
	 *
	 * @return array An array of options for the given animation style.
	 *
	 * @example:
	 * ```php
	 *  AnimationUtils::_get_options('fade');
	 * ```
	 *
	 * @output:
	 * ```php
	 * ['style', 'duration', 'delay', 'intensity', 'startingOpacity', 'speedCurve', 'repeat']
	 * ```
	 *
	 * @example:
	 * ```php
	 *  AnimationUtils::_get_options('bounce');
	 * ```
	 *
	 * @output:
	 * ```php
	 *  ['style', 'direction', 'duration', 'delay', 'intensity', 'startingOpacity', 'speedCurve', 'repeat']
	 * ```
	 *
	 * @example:
	 * ```php
	 *  AnimationUtils::_get_options('slide');
	 *  AnimationUtils::_get_options('zoom');
	 *  AnimationUtils::_get_options('flip');
	 *  AnimationUtils::_get_options('fold');
	 *  AnimationUtils::_get_options('roll');
	 * ```
	 *
	 * @output:
	 * ```php
	 *  ['style', 'direction', 'duration', 'delay', 'intensity', 'startingOpacity', 'speedCurve', 'repeat']
	 * ```
	 *
	 * @example:
	 * ```php
	 *  AnimationUtils::_get_options('none');
	 * ```
	 *
	 * @output:
	 * ```php
	 *  []
	 * ```
	 */
	private static function _get_options( string $style ): array {
		switch ( $style ) {
			case 'fade':
				return [
					'style',
					'duration',
					'delay',
					'intensity', // Intensity setting is not exist in VB when the style is `fade`. But it is required in FE.
					'startingOpacity',
					'speedCurve',
					'repeat',
				];

			case 'bounce':
				return [
					'style',
					'direction',
					'duration',
					'delay',
					'intensity', // Intensity setting is not exist in VB when the style is `bounce`. But it is required in FE.
					'startingOpacity',
					'speedCurve',
					'repeat',
				];

			case 'slide':
			case 'zoom':
			case 'flip':
			case 'fold':
			case 'roll':
				return [
					'style',
					'direction',
					'duration',
					'delay',
					'intensity',
					'startingOpacity',
					'speedCurve',
					'repeat',
				];

			case 'none':
			default:
				return [];
		}
	}

	/**
	 * Get the animation presets based on the selected animation style.
	 *
	 * This function retrieves animation setting presets for a given style. The presets include various properties such as
	 * `filter_value`, `key`, `default`, and `skip`. The `filter_value` property is a callback function that filters the
	 * value based on the original value, animation group attributes, and style presets. The `key` property represents the
	 * key that will be printed in the front-end. The `default` property is the default value for the given property. The
	 * `skip` property is a flag that determines whether the item should be skipped during processing.
	 *
	 * @since ??
	 *
	 * @param string $style The selected style.
	 *
	 * @return array The animation presets for the selected style.
	 *
	 * @example
	 * ```php
	 * $presets = AnimationUtils::_get_presets( 'fade' );
	 * ```
	 *
	 * @example
	 * ```php
	 * $presets = AnimationUtils::_get_presets( 'slide' );
	 * ```
	 *
	 * @example
	 * ```php
	 * $presets = AnimationUtils::_get_presets( 'zoom' );
	 * ```
	 */
	private static function _get_presets( string $style ): array {
		// Animation setting presets:
		// - `filter_value` => A callable function that will be invoked to filter the value.
		// - `key`          => The key that will be printed in FE.
		// - `default`      => The default value.
		// - `skip`         => Flag to skip the item for being processed.
		$presets = [
			'style'           => [
				/**
				 * A callback function that is used to filter the value
				 *
				 * @since ??
				 *
				 * @param string $value         Original value.
				 * @param array  $attrs         Animation group attributes.
				 * @param array  $style_presets Setting presets based on selected style.
				 *
				 * @return string Filtered value.
				 */
				'filter_value' => function( $value, $attrs, $style_presets ) {
					if ( isset( $style_presets['direction'] ) ) {
						$direction = $attrs['direction'] ?? $style_presets['direction']['default'];
						$direction = call_user_func( $style_presets['direction']['filter_value'], $direction, $attrs, $style_presets );

						if ( 'center' !== $direction ) {
							$value .= ucfirst( $direction );
						}
					}

					return $value;
				},
				'key'          => 'style',
				'default'      => 'none',
			],
			'direction'       => [
				/**
				 * A callback function that is used to filter the value
				 *
				 * @since ??
				 *
				 * @param string $value         Original value.
				 * @param array  $attrs         Animation group attributes.
				 * @param array  $style_presets Setting presets based on selected style.
				 *
				 * @return string Filtered value.
				 */
				'filter_value' => function( $value, $attrs, $style_presets ) {
					$values = [
						'center',
						'top',
						'bottom',
						'left',
						'right',
					];

					if ( ! in_array( $value, $values, true ) ) {
						return 'center';
					}

					return $value;
				},
				'key'          => 'direction',
				'default'      => 'center',
				'skip'         => true, // Skip, `direction` will be appended to `style`.
			],
			'duration'        => [
				'key'     => 'duration',
				'default' => '1000ms',
			],
			'delay'           => [
				'key'     => 'delay',
				'default' => '0ms',
			],
			'intensity'       => [
				/**
				 * A callback function that is used to filter the value
				 *
				 * @since ??
				 *
				 * @param string $value         Original value.
				 * @param array  $attrs         Animation group attributes.
				 * @param array  $style_presets Setting presets based on selected style.
				 *
				 * @return string Filtered value.
				 */
				'filter_value' => function( $value, $attrs, $style_presets ) {
					$style = $attrs['style'] ?? 'none';

					// List of styles that did not have intensity setting in VB. But it is required in FE.
					$styles_without_intensity = [
						'fade',
						'bounce',
					];

					// List of styles that has its own intensity field.
					$styles_with_intensity = [
						'slide',
						'zoom',
						'flip',
						'fold',
						'roll',
					];

					// Use the default value for styles that did not have intensity setting in VB.
					if ( in_array( $style, $styles_without_intensity, true ) ) {
						return '50%';
					}

					// Get the intensity value that associated with the selected style.
					if ( is_array( $value ) ) {
						if ( in_array( $style, $styles_with_intensity, true ) ) {
							return $value[ $style ] ?? '50%';
						}

						return '50%';
					}

					return $value;
				},
				'key'          => 'intensity',
				'default'      => '50%',
			],
			'startingOpacity' => [
				'key'     => 'starting_opacity',
				'default' => '0%',
			],
			'speedCurve'      => [
				/**
				 * A callback function that is used to filter the value
				 *
				 * @since ??
				 *
				 * @param string $value         Original value.
				 * @param array  $attrs         Animation group attributes.
				 * @param array  $style_presets Setting presets based on selected style.
				 *
				 * @return string Filtered value.
				 */
				'filter_value' => function( $value, $attrs, $style_presets ) {
					$values = [
						'ease-in-out',
						'ease',
						'ease-in',
						'ease-out',
						'linear',
					];

					if ( ! in_array( $value, $values, true ) ) {
						return 'ease-in-out';
					}

					return $value;
				},
				'key'          => 'speed_curve',
				'default'      => 'ease-in-out',
			],
			'repeat'          => [
				/**
				 * A callback function that is used to filter the value
				 *
				 * @since ??
				 *
				 * @param string $value         Original value.
				 * @param array  $attrs         Animation group attributes.
				 * @param array  $style_presets Setting presets based on selected style.
				 *
				 * @return string Filtered value.
				 */
				'filter_value' => function( $value, $attrs, $style_presets ) {
					$values = [
						'once',
						'loop',
					];

					if ( ! in_array( $value, $values, true ) ) {
						return 'once';
					}

					return $value;
				},
				'key'          => 'repeat',
				'default'      => 'once',
			],
		];

		$options = self::_get_options( $style );

		if ( ! $options ) {
			return [];
		}

		return array_reduce(
			$options,
			function( $carry, $option ) use ( $presets ) {
				if ( isset( $presets[ $option ] ) ) {
					$carry[ $option ] = $presets[ $option ];
				}

				return $carry;
			},
			[]
		);
	}

	/**
	 * Check if animation is enabled for the given attributes.
	 *
	 * This function checks if the animation is enabled for the provided attributes.
	 * It counts the number of animation options available in the `desktop` state mode style attribute
	 * and returns true if the count is greater than zero.
	 *
	 * Note: The style attribute is only available in the desktop state mode.
	 *
	 * @since ??
	 *
	 * @param array $attr The attributes for which to check if the animation is enabled.
	 *
	 * @return bool `true` if the animation is enabled, `false` otherwise.
	 *
	 * @example:
	 * ```php
	 *  $attr = array(
	 *      'desktop' => array(
	 *          'value' => array(
	 *              'style' => 'fade',
	 *          ),
	 *      ),
	 *  );
	 * ```
	 *
	 * @output
	 * ```php
	 *  $enabled = AnimationUtils::is_enabled( $attr );
	 * ```
	 *
	 * @example:
	 * ```php
	 *  $attr = array(
	 *      'desktop' => array(
	 *          'value' => array(
	 *              'style' => 'none',
	 *          ),
	 *      ),
	 *   );
	 * ```
	 *
	 * @output
	 * ```php
	 *  $enabled = AnimationUtils::is_enabled( $attr );
	 * ```
	 */
	public static function is_enabled( array $attr ): bool {
		// Style attribute only available in desktop mode.
		return 0 < count( self::_get_options( $attr['desktop']['value']['style'] ?? 'none' ) );
	}
}
