<?php
/**
 * SiteSettings class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * SiteSettings class.
 *
 * This class contains methods to work with site settings.
 *
 * @since ??
 */
class SiteSettings {

	/**
	 * Get GMT offset string from options.
	 *
	 * @param string $gmt_offset GMT offset.
	 *
	 * @since ??
	 *
	 * @return string GMT offset string in the format of `GMT+HHMM` or `GMT-HHMM`.
	 */
	public static function get_gmt_offset_string( $gmt_offset = '0' ) {

		$gmt_divider       = strpos( $gmt_offset, '-' ) === 0 ? '-' : '+';
		$gmt_offset_hour   = str_pad( abs( (int) $gmt_offset ), 2, '0', STR_PAD_LEFT );
		$gmt_offset_minute = str_pad( ( ( abs( $gmt_offset ) * 100 ) % 100 ) * ( 60 / 100 ), 2, '0', STR_PAD_LEFT );

		return "GMT{$gmt_divider}{$gmt_offset_hour}{$gmt_offset_minute}";

	}
}
