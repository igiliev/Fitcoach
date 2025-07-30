<?php
/**
 * Module: MultiViewUtils::is_only_value() method.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtilsTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use InvalidArgumentException;

trait IsOnlyValueTrait {

	/**
	 * Determines if the given value is not a many value.
	 *
	 * @since ??
	 *
	 * @param array  $value              The value to check.
	 * @param string $default_breakpoint The default breakpoint to remove from the value array.
	 * @param string $default_state      The default state to remove from the value array.
	 *
	 * @throws InvalidArgumentException If the value is empty.
	 *
	 * @return bool Returns true if the value is not a many value.
	 */
	public static function is_only_value( array $value, string $default_breakpoint = null, string $default_state = null ) {
		if ( ! $value ) {
			throw new InvalidArgumentException( 'Value cannot be empty.' );
		}

		if ( ! $default_breakpoint || ! $default_state ) {
			$breakpoints_states_info = MultiViewUtils::get_breakpoints_states_info();

			if ( ! $default_breakpoint ) {
				$default_breakpoint = $breakpoints_states_info->default_breakpoint();
			}

			if ( ! $default_state ) {
				$default_state = $breakpoints_states_info->default_state();
			}
		}

		$has_default_breakpoint_state = isset( $value[ $default_breakpoint ][ $default_state ] );

		if ( $has_default_breakpoint_state ) {
			// Remove the default state value from the default breakpoint.
			unset( $value[ $default_breakpoint ][ $default_state ] );

			// If there is no states remains within the default breakpoint, remove the default breakpoint.
			if ( empty( $value[ $default_breakpoint ] ) ) {
				unset( $value[ $default_breakpoint ] );

				return empty( $value );
			}
		}

		return false;
	}
}
