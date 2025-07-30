<?php
/**
 * Module Library:Search Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Search;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class SearchPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Search
 */
class SearchPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Search module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/search' !== $module_name ) {
			return $map;
		}

		unset( $map['field.decoration.background__gradient.stops'] );
		unset( $map['field.decoration.background__gradient.enabled'] );
		unset( $map['field.decoration.background__gradient.type'] );
		unset( $map['field.decoration.background__gradient.direction'] );
		unset( $map['field.decoration.background__gradient.directionRadial'] );
		unset( $map['field.decoration.background__gradient.repeat'] );
		unset( $map['field.decoration.background__gradient.length'] );
		unset( $map['field.decoration.background__gradient.overlaysImage'] );
		unset( $map['field.decoration.background__image.url'] );
		unset( $map['field.decoration.background__image.parallax.enabled'] );
		unset( $map['field.decoration.background__image.parallax.method'] );
		unset( $map['field.decoration.background__image.size'] );
		unset( $map['field.decoration.background__image.width'] );
		unset( $map['field.decoration.background__image.height'] );
		unset( $map['field.decoration.background__image.position'] );
		unset( $map['field.decoration.background__image.horizontalOffset'] );
		unset( $map['field.decoration.background__image.verticalOffset'] );
		unset( $map['field.decoration.background__image.repeat'] );
		unset( $map['field.decoration.background__image.blend'] );
		unset( $map['field.decoration.background__video.mp4'] );
		unset( $map['field.decoration.background__video.webm'] );
		unset( $map['field.decoration.background__video.width'] );
		unset( $map['field.decoration.background__video.height'] );
		unset( $map['field.decoration.background__video.allowPlayerPause'] );
		unset( $map['field.decoration.background__video.pauseOutsideViewport'] );
		unset( $map['field.decoration.background__pattern.style'] );
		unset( $map['field.decoration.background__pattern.enabled'] );
		unset( $map['field.decoration.background__pattern.color'] );
		unset( $map['field.decoration.background__pattern.transform'] );
		unset( $map['field.decoration.background__pattern.size'] );
		unset( $map['field.decoration.background__pattern.width'] );
		unset( $map['field.decoration.background__pattern.height'] );
		unset( $map['field.decoration.background__pattern.repeatOrigin'] );
		unset( $map['field.decoration.background__pattern.horizontalOffset'] );
		unset( $map['field.decoration.background__pattern.verticalOffset'] );
		unset( $map['field.decoration.background__pattern.repeat'] );
		unset( $map['field.decoration.background__pattern.blend'] );
		unset( $map['field.decoration.background__mask.style'] );
		unset( $map['field.decoration.background__mask.enabled'] );
		unset( $map['field.decoration.background__mask.color'] );
		unset( $map['field.decoration.background__mask.transform'] );
		unset( $map['field.decoration.background__mask.aspectRatio'] );
		unset( $map['field.decoration.background__mask.size'] );
		unset( $map['field.decoration.background__mask.width'] );
		unset( $map['field.decoration.background__mask.height'] );
		unset( $map['field.decoration.background__mask.position'] );
		unset( $map['field.decoration.background__mask.horizontalOffset'] );
		unset( $map['field.decoration.background__mask.verticalOffset'] );
		unset( $map['field.decoration.background__mask.blend'] );
		unset( $map['field.decoration.spacing__margin'] );
		unset( $map['field.decoration.spacing__padding'] );

		unset( $map['button.decoration.font.font__textAlign'] );
		unset( $map['title.decoration.font.font__headingLevel'] );

		return array_merge(
			$map,
			[
				'searchPlaceholder.innerContent'           => [
					'attrName' => 'searchPlaceholder.innerContent',
					'preset'   => 'content',
				],
				'field.advanced.placeholder.font.font__color' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.advanced.placeholder.font.font__size' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'field.advanced.placeholder.font.font__letterSpacing' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'letterSpacing',
				],
				'field.advanced.placeholder.font.font__lineHeight' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineHeight',
				],
				'field.advanced.focus.background__color'   => [
					'attrName' => 'field.advanced.focus.background',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.advanced.focus.font.font__color'    => [
					'attrName' => 'field.advanced.focus.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.advanced.focus.font.font__size'     => [
					'attrName' => 'field.advanced.focus.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'field.advanced.focus.font.font__letterSpacing' => [
					'attrName' => 'field.advanced.focus.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'letterSpacing',
				],
				'field.advanced.focus.font.font__lineHeight' => [
					'attrName' => 'field.advanced.focus.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineHeight',
				],
				'field.decoration.font.font__headingLevel' => [
					'attrName' => 'field.decoration.font.font',
					'preset'   => [ 'html' ],
					'subName'  => 'headingLevel',
				],
				'field.advanced.focus.border__radius'      => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'radius',
				],
				'field.advanced.focus.border__styles'      => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles',
				],
				'field.advanced.focus.border__styles.all.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.all.width',
				],
				'field.advanced.focus.border__styles.top.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.top.width',
				],
				'field.advanced.focus.border__styles.right.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.right.width',
				],
				'field.advanced.focus.border__styles.bottom.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.bottom.width',
				],
				'field.advanced.focus.border__styles.left.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.left.width',
				],
				'field.advanced.focus.border__styles.all.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.all.color',
				],
				'field.advanced.focus.border__styles.top.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.top.color',
				],
				'field.advanced.focus.border__styles.right.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.right.color',
				],
				'field.advanced.focus.border__styles.bottom.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.bottom.color',
				],
				'field.advanced.focus.border__styles.left.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.left.color',
				],
				'field.advanced.focus.border__styles.all.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.all.style',
				],
				'field.advanced.focus.border__styles.top.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.top.style',
				],
				'field.advanced.focus.border__styles.right.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.right.style',
				],
				'field.advanced.focus.border__styles.bottom.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.bottom.style',
				],
				'field.advanced.focus.border__styles.left.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.left.style',
				],
			]
		);
	}
}
