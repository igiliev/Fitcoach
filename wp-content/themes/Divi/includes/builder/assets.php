<?php

function et_builder_enqueue_assets_head() {
	// Setup WP media.
	// Around 5.2-alpha, `wp_enqueue_media` started using a function defined in a file
	// which is only included in admin. Unfortunately there's no safe/reliable way to conditionally
	// load this other than checking the WP version.
	if ( version_compare( $GLOBALS['wp_version'], '5.2-alpha-44947', '>=' ) ) {
		require_once ABSPATH . 'wp-admin/includes/post.php';
	}

	wp_enqueue_media();

	// Setup Builder Media Library
	wp_enqueue_script( 'et_pb_media_library', ET_BUILDER_URI . '/scripts/ext/media-library.js', array( 'media-editor' ), ET_BUILDER_PRODUCT_VERSION, true );
}

// TODO, make this fire late enough, so that the_content has fired and ET_Builder_Element::get_computed_vars() is ready
// currently its being called in temporary_app_boot() in view.php
function et_builder_enqueue_assets_main() {
	$ver    = ET_BUILDER_VERSION;
	$root   = ET_BUILDER_URI;
	$assets = ET_BUILDER_URI . '/frontend-builder/assets';

	wp_register_script( 'wp-color-picker-alpha', ET_BUILDER_URI . '/scripts/ext/wp-color-picker-alpha.min.js', array( 'jquery', 'wp-color-picker' ) );
	wp_localize_script(
		'wp-color-picker-alpha',
		'et_pb_color_picker_strings',
		apply_filters(
			'et_pb_color_picker_strings_builder',
			array(
				'legacy_pick'    => esc_html__( 'Select', 'et_builder' ),
				'legacy_current' => esc_html__( 'Current Color', 'et_builder' ),
			)
		)
	);

	wp_enqueue_script( 'wp-color-picker-alpha' );
	wp_enqueue_style( 'wp-color-picker' );

	wp_enqueue_style( 'et-core-admin', ET_CORE_URL . 'admin/css/core.css', array(), ET_CORE_VERSION );
	wp_enqueue_style( 'et-core-portability', ET_CORE_URL . 'admin/css/portability.css', array(), ET_CORE_VERSION );

	wp_register_style( 'et_pb_admin_date_css', "{$root}/styles/jquery-ui-1.12.1.custom.css", array(), $ver );
	wp_register_style( 'et-fb-top-window', "{$assets}/css/fb-top-window.css", array(), $ver );

	$conditional_deps = array();

	if ( ! et_builder_bfb_enabled() && ! et_builder_tb_enabled() ) {
		$conditional_deps[] = 'et-fb-top-window';
	}

	// Enqueue the appropriate bundle CSS (hot/start/build)
	$deps = array_merge(
		array(
			'et_pb_admin_date_css',
			'wp-mediaelement',
			'wp-color-picker',
			'et-core-admin',
		),
		$conditional_deps
	);

	et_fb_enqueue_bundle( 'et-frontend-builder', 'bundle.css', $deps );

	// Load Divi Builder style.css file with hardcore CSS resets and Full Open Sans font if the Divi Builder plugin is active
	if ( et_is_builder_plugin_active() ) {
		// `bundle.css` was removed from `divi-builder-style.css` and is now enqueued separately for the DBP as well.
		wp_enqueue_style(
			'et-builder-divi-builder-styles',
			"{$assets}/css/divi-builder-style.css",
			array_merge( array( 'et-core-admin', 'wp-color-picker' ), $conditional_deps ),
			$ver
		);
	}

	wp_enqueue_script( 'mce-view' );

	if ( ! et_core_use_google_fonts() || et_is_builder_plugin_active() ) {
		et_builder_enqueue_open_sans();
	}

	wp_enqueue_style( 'et-frontend-builder-failure-modal', "{$assets}/css/failure_modal.css", array(), $ver );
	wp_enqueue_style( 'et-frontend-builder-notification-modal', "{$root}/styles/notification_popup_styles.css", array(), $ver );
}
