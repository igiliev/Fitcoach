<?php
/**
 * Settings class.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\Framework\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\ABTesting\ABTesting;
use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Framework\Settings\Overflow;
use ET\Builder\Packages\GlobalData\GlobalData;

/**
 * Class for handling interacting with builder settings.
 *
 * @internal This class is equivalent of Divi 4's `ET_Builder_Settings` class with some adjustment to suit Divi 5.
 * Right now only `ET_Builder_Settings` methods that are used for Divi 5 that is being copied here.
 *
 * @since ??
 */
class Settings {

	/**
	 * Builder setting values.
	 *
	 * @var array
	 */
	private static $_builder_settings_values;

	/**
	 * Page setting fields value array.
	 *
	 * @var array
	 */
	private static $_page_settings_values;

	/**
	 * List of page setting fields slug whose values are default.
	 *
	 * @var array[]
	 */
	private static $_page_settings_is_default = [];

	/**
	 * Page setting fields.
	 *
	 * @var array
	 */
	private static $_page_settings_fields;

	/**
	 * Returns all taxonomy terms for a given post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return string
	 */
	protected static function _get_object_terms( $post_id, $taxonomy ) {
		$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
		return is_array( $terms ) ? implode( ',', $terms ) : '';
	}

	/**
	 * Get page settings values.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return mixed|void
	 */
	private static function _get_page_settings_values( $post_id ) {
		$post_id = $post_id ? $post_id : get_the_ID();

		if ( ! empty( self::$_page_settings_values[ $post_id ] ) ) {
			return self::$_page_settings_values[ $post_id ];
		}

		$overflow_default = Overflow::OVERFLOW_DEFAULT;
		$is_default       = [];

		// Page settings fields.
		$fields = [];
		if ( ! empty( self::$_page_settings_fields ) ) {
			$fields = self::$_page_settings_fields;
		}

		// Defaults.
		$default_bounce_rate_limit = 5;

		// Get values.
		$ab_bounce_rate_limit       = get_post_meta( $post_id, '_et_pb_ab_bounce_rate_limit', true );
		$et_pb_ab_bounce_rate_limit = '' !== $ab_bounce_rate_limit ? $ab_bounce_rate_limit : $default_bounce_rate_limit;
		$is_default[]               = $et_pb_ab_bounce_rate_limit === $default_bounce_rate_limit ? 'et_pb_ab_bounce_rate_limit' : '';

		$color_palette             = implode( '|', et_pb_get_default_color_palette() );
		$default                   = [ '#000000', '#FFFFFF', '#E02B20', '#E09900', '#EDF000', '#7CDA24', '#0C71C3', '#8300E9' ];
		$et_pb_saved_color_palette = '' !== $color_palette ? $color_palette : $default;
		$is_default[]              = $et_pb_saved_color_palette === $default ? 'et_pb_color_palette' : '';

		$gutter_width            = get_post_meta( $post_id, '_et_pb_gutter_width', true );
		$default                 = ArrayUtility::get_value( $fields, 'et_pb_page_gutter_width.default', et_get_option( 'gutter_width', '3' ) );
		$et_pb_page_gutter_width = '' !== $gutter_width ? $gutter_width : $default;
		$is_default[]            = $et_pb_page_gutter_width === $default ? 'et_pb_page_gutter_width' : '';

		$content_area_background_color = get_post_meta( $post_id, '_et_pb_content_area_background_color', true );

		$content_area_background_color       = et_builder_is_global_color( $content_area_background_color ) ? et_builder_get_global_color( $content_area_background_color ) : $content_area_background_color;
		$default                             = ArrayUtility::get_value( $fields, 'et_pb_content_area_background_color.default', '' );
		$et_pb_content_area_background_color = '' !== $content_area_background_color ? $content_area_background_color : $default;
		$is_default[]                        = strtolower( $et_pb_content_area_background_color ) === $default ? 'et_pb_content_area_background_color' : '';

		$section_background_color = get_post_meta( $post_id, '_et_pb_section_background_color', true );

		$section_background_color = et_builder_is_global_color( $section_background_color ) ? et_builder_get_global_color( $section_background_color ) : $section_background_color;

		$default                        = ArrayUtility::get_value( $fields, 'et_pb_section_background_color.default', '' );
		$et_pb_section_background_color = '' !== $section_background_color ? $section_background_color : $default;
		$is_default[]                   = strtolower( $et_pb_section_background_color ) === $default ? 'et_pb_section_background_color' : '';

		$overflow_x   = (string) get_post_meta( $post_id, Overflow::get_field_x( '_et_pb_' ), true );
		$is_default[] = empty( $overflow_x ) || $overflow_x === $overflow_default ? Overflow::get_field_x( 'et_pb_' ) : '';

		$overflow_y   = (string) get_post_meta( $post_id, Overflow::get_field_y( '_et_pb_' ), true );
		$is_default[] = empty( $overflow_y ) || $overflow_y === $overflow_default ? Overflow::get_field_y( 'et_pb_' ) : '';

		// Global colors.
		$et_pb_global_color_palette = et_get_option( 'et_global_colors' );
		$page_global_colors_info    = get_post_meta( $post_id, '_global_colors_info', true );

		// Convert Global Colors Data from D4 to D5 format.
		GlobalData::maybe_convert_global_colors_data();

		$et_pb_global_color_palette = GlobalData::get_global_colors();

		// Global variables.
		$et_pb_global_variables = GLobalData::get_global_variables();

		// When the post has no thumbnail, `get_post_thumbnail_id` returns `0`. Set empty string when the value is `0`
		// to prevent `Upload` component fetch media library item with id `0`.
		// See: https://elegantthemes.slack.com/archives/C01CW343ZJ9/p1725555516644059.
		$post_thumbnail      = get_post_thumbnail_id( $post_id );
		$post_settings_image = $post_thumbnail && '0' !== $post_thumbnail ? $post_thumbnail : '';

		$post   = get_post( $post_id );
		$values = [
			'et_pb_enable_ab_testing'                => ABTesting::is_active( $post_id ) ? 'on' : 'off',
			'et_pb_ab_bounce_rate_limit'             => $et_pb_ab_bounce_rate_limit,
			'et_pb_ab_stats_refresh_interval'        => ABTesting::get_refresh_interval( $post_id ),
			'et_pb_ab_subjects'                      => et_pb_ab_get_subjects( $post_id ),
			'et_pb_enable_shortcode_tracking'        => get_post_meta( $post_id, '_et_pb_enable_shortcode_tracking', true ),
			'et_pb_ab_current_shortcode'             => '[et_pb_split_track id="' . $post_id . '" /]',
			'et_pb_custom_css'                       => get_post_meta( $post_id, '_et_pb_custom_css', true ),
			'et_pb_color_palette'                    => $et_pb_saved_color_palette,
			'et_pb_gc_palette'                       => maybe_unserialize( $et_pb_global_color_palette ),
			'et_pb_global_variables'                 => maybe_unserialize( $et_pb_global_variables ),
			'et_pb_page_gutter_width'                => $et_pb_page_gutter_width,
			'et_pb_content_area_background_color'    => strtolower( $et_pb_content_area_background_color ),
			'et_pb_section_background_color'         => strtolower( $et_pb_section_background_color ),
			'et_pb_post_settings_title'              => $post ? $post->post_title : '',
			'et_pb_post_settings_excerpt'            => $post ? $post->post_excerpt : '',
			'et_pb_post_settings_image'              => $post_settings_image,
			'et_pb_post_settings_categories'         => self::_get_object_terms( $post_id, 'category' ),
			'et_pb_post_settings_tags'               => self::_get_object_terms( $post_id, 'post_tag' ),
			'et_pb_post_settings_project_categories' => self::_get_object_terms( $post_id, 'project_category' ),
			'et_pb_post_settings_project_tags'       => self::_get_object_terms( $post_id, 'project_tag' ),
			Overflow::get_field_x( 'et_pb_' )        => $overflow_x,
			Overflow::get_field_y( 'et_pb_' )        => $overflow_y,
			'et_pb_page_z_index'                     => get_post_meta( $post_id, '_et_pb_page_z_index', true ),
			'global_colors_info'                     => $page_global_colors_info ? $page_global_colors_info : '{}',
		];
		/**
		 * Filters Divi Builder page settings values.
		 *
		 * @since 3.0.45
		 *
		 * @param mixed[] $builder_settings {
		 *     Builder Settings Values
		 *
		 *     @type string $setting_name Setting value.
		 *     ...
		 * }
		 * @param string|int $post_id
		 */
		$values = apply_filters_deprecated(
			'et_builder_page_settings_values',
			[ $values, $post_id ],
			'5.0.0',
			'divi_framework_settings_get_page_settings_values'
		);

		/**
		 * Filters the Divi Builder's page settings values.
		 *
		 * @deprecated {@see 'et_builder_page_settings_values'}
		 *
		 * @since      2.7.0
		 * @since      3.0.45 Deprecation.
		 */
		$values = apply_filters_deprecated(
			'et_pb_get_builder_settings_values',
			[ $values, $post_id ],
			'5.0.0',
			'divi_framework_settings_get_page_settings_values'
		);

		$values = apply_filters( 'divi_framework_settings_get_page_settings_values', $values, $post_id );

		// Setup `$is_default` values. This need to be called after `divi_framework_settings_get_page_settings_values`
		// filter being executed since the auto populating mechanism relied on returned value of callback that is
		// executed at `divi_framework_settings_get_page_settings_values`.
		self::$_page_settings_is_default[ $post_id ] = apply_filters(
			'divi_framework_settings_get_page_settings_is_default',
			$is_default
		);

		self::$_page_settings_values[ $post_id ] = $values;

		return $values;
	}

	/**
	 * Returns the values of builder settings for the provided settings scope.
	 *
	 * @param string     $scope   Get values for scope (page|builder|all). Default 'page'.
	 * @param string|int $post_id Optional. If not provided, {@link get_the_ID()} will be used.
	 * @param bool       $exclude_defaults Optional. Whether to exclude default value.
	 *
	 * @return mixed[] {
	 *     Settings Values
	 *
	 *     @type mixed $setting_key The value for the setting.
	 *     ...
	 * }
	 */
	public static function get_values( $scope = 'page', $post_id = null, $exclude_defaults = false ) {
		$post_id = $post_id ? $post_id : get_the_ID();
		$result  = [];

		if ( 'builder' === $scope ) {
			$result = self::$_builder_settings_values;
		} elseif ( 'page' === $scope ) {
			$result = self::_get_page_settings_values( $post_id );
		} elseif ( 'all' === $scope ) {
			$result = [
				'page'    => self::_get_page_settings_values( $post_id ),
				'builder' => self::$_builder_settings_values,
			];
		}

		if ( $exclude_defaults ) {
			if ( 'all' !== $scope ) {
				$result = [ $result ];
			}

			foreach ( $result as $key => $settings ) {
				$result[ $key ] = array_diff_key( $result[ $key ], array_flip( self::$_page_settings_is_default[ $post_id ] ) );
			}

			if ( 'all' !== $scope ) {
				$result = $result[0];
			}
		}

		return $result;
	}

	/**
	 * Returns the values of builder settings for each Theme Builder Area combined.
	 *
	 * @param string     $scope   Get values for scope (page|builder|all). Default 'page'.
	 * @param string|int $post_id Optional. If not provided, {@link get_the_ID()} will be used.
	 * @param bool       $exclude_defaults Optional. Whether to exclude default value.
	 *
	 * @return array {
	 *     Settings Values
	 *
	 *     @type mixed $setting_key The value for the setting.
	 *     ...
	 * }
	 */
	public static function get_settings_values( $scope = 'page', $post_id = null, $exclude_defaults = false ) {
		$results                 = [];
		$post_id                 = $post_id ? $post_id : get_the_ID();
		$results['post_content'] = self::get_values( $scope, $post_id, $exclude_defaults );

		// In Theme Builder we can return results without TB Layouts.
		if ( et_builder_tb_enabled() ) {
			return $results;
		}

		$theme_builder_layouts = et_theme_builder_get_template_layouts();

		// Unset main template from Theme Builder layouts to avoid PHP Notices.
		if ( isset( $theme_builder_layouts['et_template'] ) ) {
			unset( $theme_builder_layouts['et_template'] );
		}

		// Check if any active Theme Builder Area is used and add settings.
		foreach ( $theme_builder_layouts as $key => $theme_builder_layout ) {
			if ( is_array( $theme_builder_layout ) && 0 !== $theme_builder_layout['id'] && $theme_builder_layout['enabled'] && $theme_builder_layout['override'] ) {
				$page_settings_values = self::get_values( $scope, $theme_builder_layout['id'], $exclude_defaults );
				$results[ $key ]      = $page_settings_values;
			}
		}

		return $results;
	}
}
