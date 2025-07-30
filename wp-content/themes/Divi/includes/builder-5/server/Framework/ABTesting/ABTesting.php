<?php
/**
 * ABTesting
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\ABTesting;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * ABTesting class.
 *
 * @since ??
 */
class ABTesting {

	/**
	 * Check whether AB Testing is active.
	 *
	 * @since ??
	 *
	 * @internal equivalent of Divi 4's `et_is_ab_testing_active()`.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function is_active( $post_id = 0 ) {
		$post_id = $post_id > 0 ? $post_id : get_the_ID();
		$post_id = apply_filters_deprecated(
			'et_is_ab_testing_active_post_id',
			[ $post_id ],
			'5.0.0',
			'divi_framework_ab_testing_is_active_post_id'
		);
		$post_id = apply_filters( 'divi_framework_ab_testing_is_active_post_id', $post_id );

		$ab_testing_status = 'on' === get_post_meta( $post_id, '_et_pb_use_ab_testing', true );

		$fb_enabled = function_exists( 'et_core_is_fb_enabled' ) ? et_core_is_fb_enabled() : false;

		if ( ! $ab_testing_status && $fb_enabled && 'publish' !== get_post_status() ) {
			$ab_testing_status = 'on' === get_post_meta( $post_id, '_et_pb_use_ab_testing_draft', true );
		}

		return $ab_testing_status;
	}

	/**
	 * Get refresh interval of particular AB Testing
	 *
	 * @since ??
	 *
	 * @internal equivalent of Divi 4's `et_pb_ab_get_refresh_interval()`
	 *
	 * @param int    $post_id post ID.
	 * @param string $default default interval.
	 *
	 * @return string interval used in particular AB Testing.
	 */
	public static function get_refresh_interval( $post_id, $default = 'hourly' ) {
		$interval = get_post_meta( $post_id, '_et_pb_ab_stats_refresh_interval', true );

		if ( in_array( $interval, array_keys( self::get_refresh_interval_durations() ), true ) ) {
			$interval = apply_filters_deprecated(
				'et_pb_ab_get_refresh_interval',
				[ $interval, $post_id ],
				'5.0.0',
				'divi_framework_ab_testing_refresh_interval'
			);

			$interval = apply_filters( 'divi_framework_ab_testing_refresh_interval', $interval, $post_id );

			return $interval;
		}

		$default_interval = apply_filters_deprecated(
			'et_pb_ab_default_refresh_interval',
			[ $default, $post_id ],
			'5.0.0',
			'divi_framework_ab_testing_default_refresh_interval'
		);

		$default_interval = apply_filters(
			'divi_framework_ab_testing_default_refresh_interval',
			$default_interval,
			$post_id
		);

		return $default_interval;
	}

	/**
	 * List of valid AB Testing refresh interval duration
	 *
	 * @since ??
	 *
	 * @internal equivalent of Divi 4's `et_pb_ab_refresh_interval_durations()`
	 *
	 * @return array
	 */
	public static function get_refresh_interval_durations() {
		$refresh_interval_durations = [
			'hourly' => 'day',
			'daily'  => 'week',
		];

		$refresh_interval_durations = apply_filters_deprecated(
			'et_pb_ab_refresh_interval_durations',
			[ $refresh_interval_durations ],
			'5.0.0',
			'divi_framework_ab_testing_refresh_interval_durations'
		);

		$refresh_interval_durations = apply_filters(
			'divi_framework_ab_testing_refresh_interval_durations',
			$refresh_interval_durations
		);

		return $refresh_interval_durations;
	}
}
