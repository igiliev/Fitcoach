<?php
/**
 * BackgroundComponentParallax::component()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Background\BackgroundComponentParallaxTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\Module\Options\Background\BackgroundClassnames;
use ET\Builder\Packages\StyleLibrary\Declarations\Background\Background;
use ET\Builder\Packages\Module\Options\Background\BackgroundComponentParallaxItemTraits\ComponentTrait as ParallaxItemComponentTrait;

trait ComponentTrait {

	/** Component function for handling background attributes and generating parallax backgrounds.
	 *
	 * This function generates parallax backgrounds based on the provided attributes.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/BackgroundComponentParallax BackgroundComponentParallax} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for generating the parallax backgrounds.
	 *
	 *     @type array|null  $backgroundAttr   The background attributes. Default `null`.
	 *     @type string|null $moduleId         The module ID. Default `null`.
	 *     @type array       $enable           The enable settings for different states.
	 *                                         Default `[value'  => true, 'hover'  => true, 'sticky' => false,]`.
	 * }
	 *
	 * @return string The generated parallax backgrounds.
	 *
	 * @example:
	 * ```php
	 *     $args = array(
	 *         'backgroundAttr' => array(
	 *             'desktop' => array(
	 *                 'value' => array(
	 *                     'image' => array(
	 *                         'parallax' => array(
	 *                             'enabled' => true,
	 *                             'method'  => 'mouse',
	 *                         ),
	 *                         'url'   => 'https://example.com/image.jpg',
	 *                     ),
	 *                     'gradient' => array(
	 *                         'overlaysImage' => true,
	 *                         'stops'         => array(
	 *                             array(
	 *                                 'color' => '#000000',
	 *                                 'stop'  => '0%',
	 *                             ),
	 *                             array(
	 *                                 'color' => '#ffffff',
	 *                                 'stop'  => '100%',
	 *                             ),
	 *                         ),
	 *                     ),
	 *                 ),
	 *             ),
	 *             // Add other breakpoints and states here.
	 *         ),
	 *         'moduleId' => 'my-module',
	 *         'enable'   => array(
	 *             'value'  => true,
	 *             'hover'  => true,
	 *             'sticky' => false,
	 *         ),
	 *     );
	 *     $parallaxBackgrounds = ClassName::component( $args );
	 * ```
	 */
	public static function component( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'backgroundAttr' => null,
				'moduleId'       => null,
				'enable'         => [
					'value'  => true,
					'hover'  => true,
					'sticky' => false,
				],
			]
		);

		$background_default_attr = Background::$background_default_attr;
		$background_attr         = $args['backgroundAttr'];
		$module_id               = $args['moduleId'];
		$enable                  = $args['enable'];

		// Populate exist classnames on all breakpoint and states.
		$exist_classnames    = [];
		$gradient_classnames = [];

		// Background component needs to wait until `existClassNames` gets populated. Thus the component is
		// populated as a function which will be executed later once `existClassNames` have been populated.
		$parallax_background_functions = [];

		// If current breakpoint / state does not have `enabled` value, value of larger breakpoint gets cascaded and used.
		$larger_breakpoint_enabled        = $background_default_attr['image']['parallax']['enabled'] ?? null;
		$larger_breakpoint_method         = $background_default_attr['image']['parallax']['method'] ?? null;
		$larger_breakpoint_overlays_image = $background_default_attr['gradient']['overlaysImage'] ?? null;
		$larger_breakpoint_gradient       = $background_default_attr['gradient'] ?? null;
		$larger_breakpoint_blend          = $background_default_attr['image']['blend'] ?? null;
		$larger_breakpoint_url            = $background_default_attr['image']['url'] ?? null;

		// Skip if `backgroundAttr` isn't an array.
		if ( is_array( $background_attr ) ) {
			// The order of breakpoint matters because larger breakpoint value cascades into the smaller breakpoint
			// value. Thus loop over the breakpoints list with guaranteed order instead of looping `backgroundAttr` properties.
			foreach ( Breakpoint::get_enabled_breakpoint_names() as $breakpoint ) {
				$state_values = $background_attr[ $breakpoint ] ?? null;

				// Continue if stateValues is an array.
				if ( is_array( $state_values ) ) {
					// The order of state matters because default (`value`) state value cascades into the onther states. Thus
					// loop over the states list with guaranteed order instead of looping `backgroundAttr[breakpoint]` properties.
					foreach ( ModuleUtils::states() as $state ) {
						$current_value = $state_values[ $state ] ?? null;

						if ( ! is_array( $current_value ) ) {
							continue;
						}

						$current_enabled = $current_value['image']['parallax']['enabled'] ?? null;
						$current_method  = $current_value['image']['parallax']['method'] ?? null;
						$current_url     = $current_value['image']['url'] ?? null;

						$current_overlays_image = $current_value['gradient']['overlaysImage'] ?? null;
						$current_gradient       = $current_value['gradient'] ?? null;
						$current_blend          = $current_value['image']['blend'] ?? null;

						$parallax_name = "{$breakpoint}-{$state}";

						// Background parallax is considered valid on current breakpoint + state when:
						// 1. Current enabled + current URL exist.
						// 2. Current enabled doesn't exist + parallax enabled on larger breakpoint + current URL exist.
						if ( ( 'on' === $current_enabled || ( is_null( $current_enabled ) && 'on' === $larger_breakpoint_enabled ) ) && isset( $enable[ $state ] ) && $enable[ $state ] && ( $current_url ?? $larger_breakpoint_url ) ) {
							// existClassName is empty string on desktop breakpoint + default (`value`) state.
							$exist_classname = BackgroundClassnames::get_background_parallax_exist_classnames( $breakpoint, $state );

							if ( $exist_classname ) {
								$exist_classnames[ $exist_classname ] = true;
							}

							// Check if the gradient is enabled.
							$gradient_enabled = ! empty( $current_gradient['stops'] ) || ( is_null( $current_gradient ) && ! empty( $larger_breakpoint_gradient['stops'] ) );

							// Prepare gradientOverlaysImage to be passed on parallax item component.
							$gradient_overlays_image = $gradient_enabled && ( 'on' === $current_overlays_image || ( is_null( $current_overlays_image ) && 'on' === $larger_breakpoint_overlays_image ) );

							// gradientClassName is empty string on desktop breakpoint + default (`value`) state.
							$gradient_classname = BackgroundClassnames::get_background_parallax_exist_classnames( $breakpoint, $state, $gradient_overlays_image );

							if ( $gradient_classname ) {
								$gradient_classnames[ $gradient_classname ] = true;
							}

							$gradient_css = '';

							if ( $gradient_overlays_image && is_array( $current_gradient ) ) {
								$gradient_css = Background::gradient_style_declaration(
									array_merge(
										$background_default_attr['gradient'],
										$current_gradient
									)
								);
							}

							// This array is added for readability so that we don't have to use `use()` for all the variables.
							$function_args = [
								'background_attr'          => $background_attr,
								'breakpoint'               => $breakpoint,
								'current_method'           => $current_method,
								'larger_breakpoint_method' => $larger_breakpoint_method,
								'module_id'                => $module_id,
								'state'                    => $state,
								'gradient_overlays_image'  => $gradient_overlays_image,
								'gradient_css'             => $gradient_css,
								'current_blend'            => $current_blend,
								'larger_breakpoint_blend'  => $larger_breakpoint_blend,
								'larger_breakpoint_url'    => $larger_breakpoint_url,
							];

							// Background component needs to wait until `$exist_classnames` and `$gradient_classnames` gets
							// populated. Thus, the component is populated as a function which will be executed later once
							// `$exist_classnames` and `$gradient_classnames` have been populated.
							$parallax_background_functions[] = function( $all_exist_classnames, $all_gradient_classnames ) use ( $function_args ) {
								// Added for readability, the values are the same as the variables outside of the closure.
								$background_attr          = $function_args['background_attr'];
								$breakpoint               = $function_args['breakpoint'];
								$current_method           = $function_args['current_method'];
								$larger_breakpoint_method = $function_args['larger_breakpoint_method'];
								$module_id                = $function_args['module_id'];
								$state                    = $function_args['state'];
								$gradient_overlays_image  = $function_args['gradient_overlays_image'];
								$gradient_css             = $function_args['gradient_css'];
								$current_blend            = $function_args['current_blend'];
								$larger_breakpoint_blend  = $function_args['larger_breakpoint_blend'];
								$larger_breakpoint_url    = $function_args['larger_breakpoint_url'];

								// Using anonymous class trick, to invoke recursive call on a trait. Using `self` or `static` does not work here.
								return ( new class() { use ParallaxItemComponentTrait; } )::component(
									[
										'breakpoint'      => $breakpoint,
										'cssParallax'     => $current_method ? 'off' === $current_method : 'off' === $larger_breakpoint_method,
										'existClassNames' => $all_exist_classnames,
										'moduleId'        => $module_id,
										'state'           => $state,
										'url'             => $background_attr[ $breakpoint ][ $state ]['image']['url'] ?? $larger_breakpoint_url,
										'gradientOverlaysImage' => $gradient_overlays_image,
										'gradientCSS'     => $gradient_css,
										'blend'           => $current_blend || $larger_breakpoint_blend,
										'gradientClassNames' => $all_gradient_classnames,
									]
								);
							};

							// Overwrite larger breakpoint values when current state is "value" (default).
							// Only "value" state overwrites the larger breakpoint due to how things are cascading:
							// - desktop => tablet => phone (smaller breakpoint use larger breakpoint value)
							// - hover => value || sticky => value (hover OR sticky use `value` when it doesn't exist).
							if ( 'value' === $state ) {
								if ( ! is_null( $current_enabled ) ) {
									$larger_breakpoint_enabled = $current_enabled;
								}

								if ( ! is_null( $current_method ) ) {
									$larger_breakpoint_method = $current_method;
								}

								if ( ! is_null( $current_overlays_image ) ) {
									$larger_breakpoint_overlays_image = $current_overlays_image;
								}

								if ( ! is_null( $current_gradient ) ) {
									$larger_breakpoint_gradient = $current_gradient;
								}

								if ( ! is_null( $current_url ) ) {
									$larger_breakpoint_url = $current_url;
								}

								if ( is_null( $current_blend ) ) {
									$larger_breakpoint_blend = $current_blend;
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $parallax_background_functions ) ) {
			return '';
		}

		$parallax_backgrounds = '';

		foreach ( $parallax_background_functions as $parallax_background_function ) {
			$parallax_backgrounds .= $parallax_background_function( $exist_classnames, $gradient_classnames );
		}

		return $parallax_backgrounds;
	}
}
