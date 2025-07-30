<?php
/**
 * Module Library:Section Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Section;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class SectionPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Section
 */
class SectionPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Section module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/section' !== $module_name ) {
			return $map;
		}

		// Column 1.
		unset( $map['column1.advanced.htmlAttributes__id'] );
		unset( $map['column1.advanced.htmlAttributes__class'] );
		unset( $map['column1.decoration.background__color'] );
		unset( $map['column1.decoration.background__gradient.stops'] );
		unset( $map['column1.decoration.background__gradient.enabled'] );
		unset( $map['column1.decoration.background__gradient.type'] );
		unset( $map['column1.decoration.background__gradient.direction'] );
		unset( $map['column1.decoration.background__gradient.directionRadial'] );
		unset( $map['column1.decoration.background__gradient.repeat'] );
		unset( $map['column1.decoration.background__gradient.length'] );
		unset( $map['column1.decoration.background__gradient.overlaysImage'] );
		unset( $map['column1.decoration.background__image.url'] );
		unset( $map['column1.decoration.background__image.parallax.enabled'] );
		unset( $map['column1.decoration.background__image.parallax.method'] );
		unset( $map['column1.decoration.background__image.size'] );
		unset( $map['column1.decoration.background__image.width'] );
		unset( $map['column1.decoration.background__image.height'] );
		unset( $map['column1.decoration.background__image.position'] );
		unset( $map['column1.decoration.background__image.horizontalOffset'] );
		unset( $map['column1.decoration.background__image.verticalOffset'] );
		unset( $map['column1.decoration.background__image.repeat'] );
		unset( $map['column1.decoration.background__image.blend'] );
		unset( $map['column1.decoration.background__video.mp4'] );
		unset( $map['column1.decoration.background__video.webm'] );
		unset( $map['column1.decoration.background__video.width'] );
		unset( $map['column1.decoration.background__video.height'] );
		unset( $map['column1.decoration.background__video.allowPlayerPause'] );
		unset( $map['column1.decoration.background__video.pauseOutsideViewport'] );
		unset( $map['column1.decoration.background__pattern.style'] );
		unset( $map['column1.decoration.background__pattern.enabled'] );
		unset( $map['column1.decoration.background__pattern.color'] );
		unset( $map['column1.decoration.background__pattern.transform'] );
		unset( $map['column1.decoration.background__pattern.size'] );
		unset( $map['column1.decoration.background__pattern.width'] );
		unset( $map['column1.decoration.background__pattern.height'] );
		unset( $map['column1.decoration.background__pattern.repeatOrigin'] );
		unset( $map['column1.decoration.background__pattern.horizontalOffset'] );
		unset( $map['column1.decoration.background__pattern.verticalOffset'] );
		unset( $map['column1.decoration.background__pattern.repeat'] );
		unset( $map['column1.decoration.background__pattern.blend'] );
		unset( $map['column1.decoration.background__mask.style'] );
		unset( $map['column1.decoration.background__mask.enabled'] );
		unset( $map['column1.decoration.background__mask.color'] );
		unset( $map['column1.decoration.background__mask.transform'] );
		unset( $map['column1.decoration.background__mask.aspectRatio'] );
		unset( $map['column1.decoration.background__mask.size'] );
		unset( $map['column1.decoration.background__mask.width'] );
		unset( $map['column1.decoration.background__mask.height'] );
		unset( $map['column1.decoration.background__mask.position'] );
		unset( $map['column1.decoration.background__mask.horizontalOffset'] );
		unset( $map['column1.decoration.background__mask.verticalOffset'] );
		unset( $map['column1.decoration.background__mask.blend'] );
		unset( $map['column1.decoration.spacing__margin'] );
		unset( $map['column1.decoration.spacing__padding'] );

		// Column 2.
		unset( $map['column2.advanced.htmlAttributes__id'] );
		unset( $map['column2.advanced.htmlAttributes__class'] );
		unset( $map['column2.decoration.background__color'] );
		unset( $map['column2.decoration.background__gradient.stops'] );
		unset( $map['column2.decoration.background__gradient.enabled'] );
		unset( $map['column2.decoration.background__gradient.type'] );
		unset( $map['column2.decoration.background__gradient.direction'] );
		unset( $map['column2.decoration.background__gradient.directionRadial'] );
		unset( $map['column2.decoration.background__gradient.repeat'] );
		unset( $map['column2.decoration.background__gradient.length'] );
		unset( $map['column2.decoration.background__gradient.overlaysImage'] );
		unset( $map['column2.decoration.background__image.url'] );
		unset( $map['column2.decoration.background__image.parallax.enabled'] );
		unset( $map['column2.decoration.background__image.parallax.method'] );
		unset( $map['column2.decoration.background__image.size'] );
		unset( $map['column2.decoration.background__image.width'] );
		unset( $map['column2.decoration.background__image.height'] );
		unset( $map['column2.decoration.background__image.position'] );
		unset( $map['column2.decoration.background__image.horizontalOffset'] );
		unset( $map['column2.decoration.background__image.verticalOffset'] );
		unset( $map['column2.decoration.background__image.repeat'] );
		unset( $map['column2.decoration.background__image.blend'] );
		unset( $map['column2.decoration.background__video.mp4'] );
		unset( $map['column2.decoration.background__video.webm'] );
		unset( $map['column2.decoration.background__video.width'] );
		unset( $map['column2.decoration.background__video.height'] );
		unset( $map['column2.decoration.background__video.allowPlayerPause'] );
		unset( $map['column2.decoration.background__video.pauseOutsideViewport'] );
		unset( $map['column2.decoration.background__pattern.style'] );
		unset( $map['column2.decoration.background__pattern.enabled'] );
		unset( $map['column2.decoration.background__pattern.color'] );
		unset( $map['column2.decoration.background__pattern.transform'] );
		unset( $map['column2.decoration.background__pattern.size'] );
		unset( $map['column2.decoration.background__pattern.width'] );
		unset( $map['column2.decoration.background__pattern.height'] );
		unset( $map['column2.decoration.background__pattern.repeatOrigin'] );
		unset( $map['column2.decoration.background__pattern.horizontalOffset'] );
		unset( $map['column2.decoration.background__pattern.verticalOffset'] );
		unset( $map['column2.decoration.background__pattern.repeat'] );
		unset( $map['column2.decoration.background__pattern.blend'] );
		unset( $map['column2.decoration.background__mask.style'] );
		unset( $map['column2.decoration.background__mask.enabled'] );
		unset( $map['column2.decoration.background__mask.color'] );
		unset( $map['column2.decoration.background__mask.transform'] );
		unset( $map['column2.decoration.background__mask.aspectRatio'] );
		unset( $map['column2.decoration.background__mask.size'] );
		unset( $map['column2.decoration.background__mask.width'] );
		unset( $map['column2.decoration.background__mask.height'] );
		unset( $map['column2.decoration.background__mask.position'] );
		unset( $map['column2.decoration.background__mask.horizontalOffset'] );
		unset( $map['column2.decoration.background__mask.verticalOffset'] );
		unset( $map['column2.decoration.background__mask.blend'] );
		unset( $map['column2.decoration.spacing__margin'] );
		unset( $map['column2.decoration.spacing__padding'] );

		// Column 3.
		unset( $map['column3.advanced.htmlAttributes__id'] );
		unset( $map['column3.advanced.htmlAttributes__class'] );
		unset( $map['column3.decoration.background__color'] );
		unset( $map['column3.decoration.background__gradient.stops'] );
		unset( $map['column3.decoration.background__gradient.enabled'] );
		unset( $map['column3.decoration.background__gradient.type'] );
		unset( $map['column3.decoration.background__gradient.direction'] );
		unset( $map['column3.decoration.background__gradient.directionRadial'] );
		unset( $map['column3.decoration.background__gradient.repeat'] );
		unset( $map['column3.decoration.background__gradient.length'] );
		unset( $map['column3.decoration.background__gradient.overlaysImage'] );
		unset( $map['column3.decoration.background__image.url'] );
		unset( $map['column3.decoration.background__image.parallax.enabled'] );
		unset( $map['column3.decoration.background__image.parallax.method'] );
		unset( $map['column3.decoration.background__image.size'] );
		unset( $map['column3.decoration.background__image.width'] );
		unset( $map['column3.decoration.background__image.height'] );
		unset( $map['column3.decoration.background__image.position'] );
		unset( $map['column3.decoration.background__image.horizontalOffset'] );
		unset( $map['column3.decoration.background__image.verticalOffset'] );
		unset( $map['column3.decoration.background__image.repeat'] );
		unset( $map['column3.decoration.background__image.blend'] );
		unset( $map['column3.decoration.background__video.mp4'] );
		unset( $map['column3.decoration.background__video.webm'] );
		unset( $map['column3.decoration.background__video.width'] );
		unset( $map['column3.decoration.background__video.height'] );
		unset( $map['column3.decoration.background__video.allowPlayerPause'] );
		unset( $map['column3.decoration.background__video.pauseOutsideViewport'] );
		unset( $map['column3.decoration.background__pattern.style'] );
		unset( $map['column3.decoration.background__pattern.enabled'] );
		unset( $map['column3.decoration.background__pattern.color'] );
		unset( $map['column3.decoration.background__pattern.transform'] );
		unset( $map['column3.decoration.background__pattern.size'] );
		unset( $map['column3.decoration.background__pattern.width'] );
		unset( $map['column3.decoration.background__pattern.height'] );
		unset( $map['column3.decoration.background__pattern.repeatOrigin'] );
		unset( $map['column3.decoration.background__pattern.horizontalOffset'] );
		unset( $map['column3.decoration.background__pattern.verticalOffset'] );
		unset( $map['column3.decoration.background__pattern.repeat'] );
		unset( $map['column3.decoration.background__pattern.blend'] );
		unset( $map['column3.decoration.background__mask.style'] );
		unset( $map['column3.decoration.background__mask.enabled'] );
		unset( $map['column3.decoration.background__mask.color'] );
		unset( $map['column3.decoration.background__mask.transform'] );
		unset( $map['column3.decoration.background__mask.aspectRatio'] );
		unset( $map['column3.decoration.background__mask.size'] );
		unset( $map['column3.decoration.background__mask.width'] );
		unset( $map['column3.decoration.background__mask.height'] );
		unset( $map['column3.decoration.background__mask.position'] );
		unset( $map['column3.decoration.background__mask.horizontalOffset'] );
		unset( $map['column3.decoration.background__mask.verticalOffset'] );
		unset( $map['column3.decoration.background__mask.blend'] );
		unset( $map['column3.decoration.spacing__margin'] );
		unset( $map['column3.decoration.spacing__padding'] );

		unset( $map['css__column_1_before'] );
		unset( $map['css__column_1_main'] );
		unset( $map['css__column_1_after'] );
		unset( $map['css__column_2_before'] );
		unset( $map['css__column_2_main'] );
		unset( $map['css__column_2_after'] );
		unset( $map['css__column_3_before'] );
		unset( $map['css__column_3_main'] );
		unset( $map['css__column_3_after'] );

		return array_merge(
			$map,
			[
				'module.advanced.innerShadow'             => [
					'attrName' => 'module.advanced.innerShadow',
					'preset'   => [ 'style' ],
				],
				'module.advanced.dividers.top__style'     => [
					'attrName' => 'module.advanced.dividers.top',
					'preset'   => [ 'html', 'style' ],
					'subName'  => 'style',
				],
				'module.advanced.dividers.bottom__style'  => [
					'attrName' => 'module.advanced.dividers.bottom',
					'preset'   => [ 'html', 'style' ],
					'subName'  => 'style',
				],
				'module.advanced.dividers.top__color'     => [
					'attrName' => 'module.advanced.dividers.top',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'module.advanced.dividers.bottom__color'  => [
					'attrName' => 'module.advanced.dividers.bottom',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'module.advanced.dividers.top__height'    => [
					'attrName' => 'module.advanced.dividers.top',
					'preset'   => [ 'style' ],
					'subName'  => 'height',
				],
				'module.advanced.dividers.bottom__height' => [
					'attrName' => 'module.advanced.dividers.bottom',
					'preset'   => [ 'style' ],
					'subName'  => 'height',
				],
				'module.advanced.dividers.top__repeat'    => [
					'attrName' => 'module.advanced.dividers.top',
					'preset'   => [ 'style' ],
					'subName'  => 'repeat',
				],
				'module.advanced.dividers.bottom__repeat' => [
					'attrName' => 'module.advanced.dividers.bottom',
					'preset'   => [ 'style' ],
					'subName'  => 'repeat',
				],
				'module.advanced.dividers.top__flip'      => [
					'attrName' => 'module.advanced.dividers.top',
					'preset'   => [ 'style' ],
					'subName'  => 'flip',
				],
				'module.advanced.dividers.bottom__flip'   => [
					'attrName' => 'module.advanced.dividers.bottom',
					'preset'   => [ 'style' ],
					'subName'  => 'flip',
				],
				'module.advanced.dividers.top__arrangement' => [
					'attrName' => 'module.advanced.dividers.top',
					'preset'   => [ 'style' ],
					'subName'  => 'arrangement',
				],
				'module.advanced.dividers.bottom__arrangement' => [
					'attrName' => 'module.advanced.dividers.bottom',
					'preset'   => [ 'style' ],
					'subName'  => 'arrangement',
				],
			]
		);
	}
}
