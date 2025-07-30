<?php
/**
 * Handle Dynamic Assets
 *
 * @since ?? Deprecated. Please see: includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php
 * @deprecated
 *
 * @package Builder
 */

/**
 * Gets the assets directory.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_dynamic_assets_path() instead.
 * @since 4.10.0
 *
 * @param bool $url check if url.
 *
 * @return string
 *
 * @deprecated
 */
function et_get_dynamic_assets_path( $url = false ) {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_dynamic_assets_path() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	// Value for the filter.
	$template_address = \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_dynamic_assets_path( $url );

	/**
	 * Filters prefix for assets path.
	 *
	 * This filter is the replacement of Divi 4 filter `et_dynamic_assets_prefix`.
	 *
	 * @since ?? Deprecated. Use `divi_frontend_assets_dynamic_assets_utils_prefix` filter instead.
	 *
	 * @param string $template_address
	 *
	 * @deprecated
	 */
	return apply_filters_deprecated(
		'et_dynamic_assets_prefix',
		[ $template_address ],
		'5.0',
		'divi_frontend_assets_dynamic_assets_utils_prefix'
	);
}

/**
 * Checks if current post/page is built-in.
 *
 * @since ?? Deprecated. No longer in use.
 * @since 4.10.0
 *
 * @return bool
 *
 * @deprecated
 */
function et_is_cpt() {
	et_debug( "You're Doing It Wrong! The function " . __FUNCTION__ . '() is deprecated and no longer in use. Please review your code for alternatives.' );

	static $is_cpt = null;

	if ( null === $is_cpt ) {
		global $post;

		$custom_post_types = get_post_types( array( '_builtin' => false ) );
		$custom_types      = array();
		$is_cpt            = false;

		if ( ! empty( $custom_post_types ) ) {
			$custom_types = array_keys( $custom_post_types );
		}

		$post_type = get_post_type( $post );

		if ( in_array( $post_type, $custom_types, true ) && is_singular() && 'project' !== $post_type ) {
			$is_cpt = true;
		}
	}

	return $is_cpt;
}

/**
 * Extracts gutter width values from post/page content.
 *
 * @since ?? Deprecated. No longer in use.
 * @since 4.10.0
 *
 * @param array $matches matched gutters.
 *
 * @return array
 *
 * @deprecated
 */
function et_get_content_gutter_widths( $matches ) {
	et_debug( "You're Doing It Wrong! The function " . __FUNCTION__ . '() is deprecated and no longer in use. Please review your code for alternatives.' );

	$gutters = array();

	foreach ( $matches as $match ) {
		preg_match_all( '/"([^"]+)"/', $match, $matches );
		$gutters = array_merge( $gutters, $matches[1] );
	}

	// Convert strings to integers.
	$gutters = array_map( 'intval', $gutters );

	return $gutters;
}

/**
 * Check if any widgets are currently active.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::has_builder_widgets() instead.
 * @since 4.10.0
 *
 * @return bool
 *
 * @deprecated
 */
function et_pb_are_widgets_used() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::has_builder_widgets() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::has_builder_widgets();
}

/**
 * Check if a specific value is "on" on the page.
 *
 * @param array $values matched values.
 *
 * @since ?? Deprecated. No longer in use.
 * @since 4.10.0
 *
 * @return bool
 *
 * @deprecated
 */
function et_check_if_particular_value_is_on( $values ) {
	et_debug( "You're Doing It Wrong! The function " . __FUNCTION__ . '() is deprecated and no longer in use. Please review your code for alternatives.' );

	foreach ( $values as $match ) {
		preg_match_all( '/"([^"]+)"/', $match, $matches );
		if ( in_array( 'on', $matches[1], true ) ) {
			return true;
		};
	}

	return false;
}

/**
 * Get if a non-default preset value.
 *
 * @param array $values Matched values.
 *
 * @since ?? Deprecated. No longer in use.
 * @since 4.10.0
 *
 * @return array
 *
 * @deprecated
 */
function et_get_non_default_preset_ids( $values ) {
	et_debug( "You're Doing It Wrong! The function " . __FUNCTION__ . '() is deprecated and no longer in use. Please review your code for alternatives.' );

	$result = array();

	foreach ( $values as $match ) {

		preg_match_all( '/"([^"]+)"/', $match, $matches );

		if ( ! in_array( 'default', $matches[1], true ) ) {
			$result = array_merge( $result, $matches[1] );
		}
	}

	return $result;
}

/**
 * Check to see if this is a front end request applicable to Dynamic Assets.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_dynamic_front_end_request() instead.
 * @since 4.10.0
 *
 * @return bool
 *
 * @deprecated
 */
function et_is_dynamic_front_end_request() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_dynamic_front_end_request() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_dynamic_front_end_request();
}

/**
 * Check if Dynamic Icons are enabled.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_icons() instead.
 * @since 4.10.0
 *
 * @deprecated
 */
function et_use_dynamic_icons() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_icons() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_icons();
}

/**
 * Check if JavaScript On Demand is enabled.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_icons() instead.
 * @since 4.10.0
 *
 * @return bool
 *
 * @deprecated
 */
function et_disable_js_on_demand() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_icons() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	// Value for the filter.
	$et_disable_js_on_demand = \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::use_dynamic_icons();

	/**
	 * Filters whether to disable JS on demand.
	 *
	 * @since ?? Deprecated. Use `divi_frontend_assets_dynamic_assets_utils_disable_js_on_demand` filter instead.
	 * @since 4.10.6
	 *
	 * @param bool $et_disable_js_on_demand
	 *
	 * @deprecated
	 */
	return apply_filters_deprecated(
		'et_disable_js_on_demand',
		[ (bool) $et_disable_js_on_demand ],
		'5.0',
		'divi_frontend_assets_dynamic_assets_utils_disable_js_on_demand'
	);
}

/**
 * Get all active block widgets.
 *
 * This method will collect all active block widgets first. Later on, the result will be
 * cached to improve the performance.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_active_block_widgets() instead.
 * @since 4.10.5
 *
 * @return array List of active block widgets.
 *
 * @deprecated
 */
function et_get_active_block_widgets() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_active_block_widgets() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_active_block_widgets();
}

/**
 * Check whether current block widget is active or not.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_active_block_widget() instead.
 * @since 4.10.5
 *
 * @param string $block_widget_name Block widget name.
 *
 * @return boolean Whether current block widget is active or not.
 *
 * @deprecated
 */
function et_is_active_block_widget( $block_widget_name ) {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_active_block_widget() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_active_block_widget( $block_widget_name );
}

/**
 * Check whether Extra Home layout is being used.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_extra_layout_used_as_front() instead.
 * @since 4.17.5
 *
 * @return boolean whether Extra Home layout is being used.
 *
 * @deprecated
 */
function et_is_extra_layout_used_as_front() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_extra_layout_used_as_front() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_extra_layout_used_as_front();
}

/**
 * Check whether Extra Home layout is being used.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_extra_layout_used_as_home() instead.
 * @since 4.17.5
 *
 * @return boolean whether Extra Home layout is being used.
 *
 * @deprecated
 */
function et_is_extra_layout_used_as_home() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_extra_layout_used_as_home() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_extra_layout_used_as_home();
}

/**
 * Get Extra Home layout ID.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_extra_home_layout_id() instead.
 * @since 4.17.5
 *
 * @return int|null
 *
 * @deprecated
 */
function et_get_extra_home_layout_id() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_extra_home_layout_id() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_extra_home_layout_id();
}

/**
 *  Get Extra Taxonomy layout ID.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_extra_tax_layout_id() instead.
 * @since 4.17.5
 *
 * @return int|null
 *
 * @deprecated
 */
function et_get_extra_tax_layout_id() {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_extra_tax_layout_id() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_extra_tax_layout_id();
}

/**
 * Get embeded media from post content.
 *
 * @since ?? Deprecated. Please use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_media_embedded_in_content() instead.
 * @since 4.20.1
 *
 * @param int $content Post Content.
 *
 * @return boolean false on failure, true on success.
 *
 * @deprecated
 */
function et_is_media_embedded_in_content( $content ) {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . '(), use \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_media_embedded_in_content() instead.' );

	// Ensure DynamicAssetsUtils is loaded before using it.
	require_once get_template_directory() . '/includes/builder-5/server/FrontEnd/Assets/DynamicAssetsUtils.php';

	return \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::is_media_embedded_in_content( $content );
}
