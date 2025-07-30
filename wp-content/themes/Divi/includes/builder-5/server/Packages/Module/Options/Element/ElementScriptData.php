<?php
/**
 * Module: ElementScriptData class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Element;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Animation\AnimationScriptData;
use ET\Builder\Packages\Module\Options\Link\LinkScriptData;
use ET\Builder\Packages\Module\Options\Scroll\ScrollEffectsScriptData;
use ET\Builder\Packages\Module\Options\Sticky\StickyScriptData;
use ET\Builder\Packages\Module\Options\Background\BackgroundParallaxScriptData;
use ET\Builder\Packages\Module\Options\Background\BackgroundVideoScriptData;

/**
 * `ElementScriptData`
 *
 * @since ??
 */
/**
 * ElementScriptData class.
 *
 * This class provides functionality to set data in script data element.
 *
 * @since ??
 */
class ElementScriptData {

	/**
	 * Set the attributes and options and generate script data for a given element.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/UseElementScriptData useElementScriptData} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string        $id             Optional. The ID of the element. Default empty string.
	 *     @type string|null   $selector       Optional. The CSS selector of the element. Default `null`.
	 *     @type array         $attrs          Optional. The attributes for the element. Default `[]`.
	 *     @type array         $animation      Optional. The animation settings for the element. Default `[]`.
	 *     @type array         $background     Optional. The background settings for the element. Default `[]`.
	 *     @type array         $link           Optional. The link settings for the element. Default `[]`.
	 *     @type array         $scroll         Optional. The scroll settings for the element. Default `[]`.
	 *     @type array         $sticky         Optional. The sticky settings for the element. Default `[]`.
	 *     @type null|string   $storeInstance  Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
	 * }
	 *
	 * @return void
	 */
	public static function set( array $args ): void {
		$args = wp_parse_args(
			$args,
			array(
				'id'            => '',
				'selector'      => null,
				'attrs'         => [],
				'animation'     => [],
				'background'    => [],
				'link'          => [],
				'scroll'        => [],
				'sticky'        => [],

				// FE Only.
				'storeInstance' => null,
			)
		);

		// Assign attributes.
		$id             = $args['id'];
		$selector       = $args['selector'];
		$store_instance = $args['storeInstance'];
		$attrs          = $args['attrs'];
		$animation      = $args['animation'];
		$background     = $args['background'];
		$link           = $args['link'];
		$scroll         = $args['scroll'];
		$sticky         = $attrs['sticky'] ?? $args['sticky'];

		/*
		 * Scroll Effects.
		 *
		 * We're calling `ScrollEffectsScriptData` when `$attrs['scroll']` has value. also for the accordion-item
		 * module and bar counter (`divi/accordion-item`/`divi/counter`) even if `$attrs['scroll']` is empty.
		 * Because scroll effects might need
		 * to be inherited from the parent module (divi/accordion/divi/counters) when the parent has `gridMotion` option enabled.
		 */
		if ( ! empty( $attrs['scroll'] ) || false !== strpos( $id, 'divi/accordion-item' ) || false !== strpos( $id, 'divi/counter-' ) ) {
			ScrollEffectsScriptData::set(
				array(
					'id'            => $id,
					'selector'      => $scroll['selector'] ?? $selector,
					'attr'          => $attrs['scroll'] ?? [],
					'transform'     => $attrs['transform'] ?? [],

					// FE only.
					'storeInstance' => $store_instance,
				)
			);
		}

		// Bail early if no attrs is given.
		// NOTE: Because scroll effects can be inherited from parent module i.e grid motion is on,
		// we set scroll effects even if no attrs is given as above.
		if ( empty( $args['attrs'] ) ) {
			return;
		}

		// Sticky Options.
		if ( ! empty( $attrs['sticky'] ) ) {
			StickyScriptData::set(
				array(
					'id'             => $id,
					'selector'       => $sticky['selector'] ?? $selector,
					'affectingAttrs' => $sticky['affectingAttrs'] ?? [
						'position' => $attrs['position'] ?? [],
						'sizing'   => $attrs['sizing'] ?? [],
						'scroll'   => $attrs['scroll'] ?? [],
					],
					'attr'           => $attrs['sticky'] ?? [],

					// FE only.
					'storeInstance'  => $store_instance,
				)
			);
		}

		// Animation Options.
		if ( ! empty( $attrs['animation'] ) ) {
			AnimationScriptData::set(
				array(
					'id'            => $id,
					'selector'      => $animation['selector'] ?? $selector,
					'attr'          => $attrs['animation'] ?? [],

					// FE only.
					'storeInstance' => $store_instance,
				)
			);
		}

		// Link Options.
		if ( ! empty( $attrs['link'] ) ) {
			LinkScriptData::set(
				array(
					'id'            => $id,
					'selector'      => $link['selector'] ?? $selector,
					'attr'          => $attrs['link'] ?? [],

					// FE only.
					'storeInstance' => $store_instance,
				)
			);
		}

		// Background Parallax Options.
		if ( ! empty( $attrs['background'] ) ) {
			BackgroundParallaxScriptData::set(
				array(
					'id'            => $id,
					'selector'      => $background['selector'] ?? $selector,
					'attr'          => $attrs['background'] ?? [],

					// FE only.
					'storeInstance' => $store_instance,
				)
			);

			BackgroundVideoScriptData::set(
				array(
					'id'            => $id,
					'selector'      => $background['selector'] ?? $selector,
					'attr'          => $attrs['background'] ?? [],

					// FE only.
					'storeInstance' => $store_instance,
				)
			);
		}

	}

}
