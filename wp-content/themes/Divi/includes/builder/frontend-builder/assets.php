<?php
add_action( 'wp_enqueue_scripts', 'et_builder_enqueue_assets_head', 99999999 );
add_action( 'wp_enqueue_scripts', 'et_builder_enqueue_assets_main', 99999999 );

function et_fb_enqueue_google_maps_dependency( $dependencies ) {

	if ( et_pb_enqueue_google_maps_script() ) {
		$dependencies[] = 'google-maps-api';
	}

	return $dependencies;
}
add_filter( 'et_fb_bundle_dependencies', 'et_fb_enqueue_google_maps_dependency' );

function et_fb_load_portability() {
	et_core_register_admin_assets();
	et_core_load_component( 'portability' );

	// Register the Builder individual layouts portability.
	et_core_portability_register(
		'et_builder',
		array(
			'title' => esc_html__( 'Import & Export Layouts', 'et_builder' ),
			'name'  => esc_html__( 'Divi Builder Layout', 'et_builder' ),
			'type'  => 'post',
			'view'  => true,
		)
	);
}

function et_fb_get_dynamic_asset( $prefix, $post_type = false, $update = false ) {

	if ( false === $post_type ) {
		global $post;
		$post_type = isset( $post->post_type ) ? $post->post_type : 'post';
	}

	$post_type = apply_filters( 'et_builder_cache_post_type', $post_type, $prefix );

	$post_type = sanitize_file_name( $post_type );

	if ( ! in_array( $prefix, array( 'helpers', 'definitions' ) ) ) {
		$prefix = '';
	}

	// Per language Cache due to definitions/helpers being localized.
	$lang   = sanitize_file_name( get_user_locale() );
	$cache  = sprintf( '%s/%s', ET_Core_PageResource::get_cache_directory(), $lang );
	$files  = glob( sprintf( '%s/%s-%s-*.js', $cache, $prefix, $post_type ) );
	$exists = is_array( $files ) && count( $files ) > 0;

	if ( $exists ) {
		$file = $files[0];
		$uniq = array_reverse( explode( '-', basename( $file, '.js' ) ) );
		$uniq = $uniq[0];
	}

	$updated = false;

	if ( $update || ! $exists ) {
		// Make sure cache folder exists
		wp_mkdir_p( $cache );

		// We (currently) use just 2 prefixes: 'helpers' and 'definitions'.
		// Each prefix has its content generated via a custom function called via the hook system:
		// add_filter( 'et_fb_get_asset_definitions', 'et_fb_get_asset_definitions', 10, 2 );
		// add_filter( 'et_fb_get_asset_helpers', 'et_fb_get_asset_helpers', 10, 2 );
		$content = apply_filters( "et_fb_get_asset_$prefix", false, $post_type );
		if ( $exists && $update ) {
			// Compare with old one (when a previous version exists)
			$update = et_()->WPFS()->get_contents( $file ) !== $content;
		}
		if ( ( $update || ! $exists ) ) {

			if ( ET_BUILDER_KEEP_OLDEST_CACHED_ASSETS && count( $files ) > 0 ) {
				// Files are ordered by timestamp, first one is always the oldest
				array_shift( $files );
			}

			if ( ET_BUILDER_PURGE_OLD_CACHED_ASSETS ) {
				foreach ( $files as $file ) {
					// Delete old version.
					@unlink( $file );
				}
			}

			// Write the file only if it did not exist or its content changed
			$uniq = str_replace( '.', '', (string) microtime( true ) );
			$file = sprintf( '%s/%s-%s-%s.js', $cache, $prefix, $post_type, $uniq );

			if ( wp_is_writable( dirname( $file ) ) && et_()->WPFS()->put_contents( $file, $content ) ) {
				$updated = true;
				$exists  = true;
			}
		}
	}

	$url = ! $exists ? false : sprintf(
		'%s/%s-%s-%s.js',
		et_()->path( et_core_cache_dir()->url, $lang ),
		$prefix,
		$post_type,
		$uniq
	);

	return array(
		'url'     => $url,
		'updated' => $updated,
	);
}

function et_fb_backend_helpers_boot( $helpers ) {
	$helpers['boot'] = 'fast';
	return $helpers;
}

function et_fb_app_only_bundle_deps( $deps = null ) {
	static $_deps = array();

	// Set deps if argument is passed.
	if ( $deps ) {
		// Some bundle deps are still required in top window.
		$top   = array(
			'jquery',
			'underscore',
			'jquery-ui-core',
			'jquery-ui-draggable',
			'jquery-ui-resizable',
			'jquery-ui-sortable',
			'jquery-effects-core',
			'iris',
			'wp-color-picker',
			'wp-color-picker-alpha',
			'et-profiler',
			'react-tiny-mce',
			'et_pb_admin_date_addon_js',
			'google-maps-api',
			'react',
			'react-dom',
			'wp-hooks',

			// If minified JS is served, minified JS script name is outputted instead
			et_get_combined_script_handle(),
		);
		$_deps = array_diff( $deps, $top );
	}

	return $_deps;
}

function et_fb_app_src( $tag, $handle, $src ) {
	// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
	// Replace boot with bundle in app window.
	if ( 'et-frontend-builder' === $handle ) {
		$bundle_url = esc_url( str_replace( 'boot.js', 'bundle.js', $src ) );
		return str_replace( 'src=', sprintf( 'data-et-vb-app-src="%1$s" src=', $bundle_url ), $tag );
	}

	// Only load (most) bundle deps in app window.
	if ( in_array( $handle, et_fb_app_only_bundle_deps(), true ) ) {
		return sprintf( '<script data-et-vb-app-src="%1$s"></script>', esc_url( $src ) );
	}
	return $tag;
	// phpcs:enable
}

/**
 * Disable google maps api script. Google maps api script dynamically injects scripts in the head
 * which will be blocked by Preboot.js while DOM move resources from top window to app window.
 * The google maps script will be reenable once the resources has been moved into iframe.
 *
 * @param string $tag    The `<script>` tag for the enqueued script.
 * @param string $handle The script's registered handle.
 * @param string $src    The script's source URL.
 */
function et_fb_disable_google_maps_script( $tag, $handle, $src ) {
	if ( 'google-maps-api' !== $handle || ! et_core_is_fb_enabled() || et_builder_bfb_enabled() || et_builder_tb_enabled() ) {
		return $tag;
	}

	return str_replace( "type='text/javascript'", "type='text/tempdisablejs' data-et-type='text/javascript'", $tag );
}

/**
 * Disable admin bar styling for HTML in VB. BFB doesn't loaded admin bar and  VB loads admin bar
 * on top window which makes built-in admin bar styling irrelevant because admin bar is affected by
 * top window width instead of app window width (while app window width changes based on preview mode)
 *
 * @see _admin_bar_bump_cb()
 */
function et_fb_disable_admin_bar_style() {
	add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );
}
add_action( 'wp', 'et_fb_disable_admin_bar_style', 15 );


function et_fb_output_wp_auth_check_html() {
	// A <button> element is used for the close button which looks ugly in Chrome. Use <a> element instead.
	ob_start();
	wp_auth_check_html();
	$output = ob_get_contents();
	ob_end_clean();

	$output = str_replace(
		array( '<button type="button"', '</button>' ),
		array( '<a href="#"', '</a>' ),
		$output
	);

	echo et_core_intentionally_unescaped( $output, 'html' );
}


function et_fb_set_editor_available_cookie() {
	global $post;
	$post_id = isset( $post->ID ) ? $post->ID : false;
	if ( ! headers_sent() && ! empty( $post_id ) ) {
		setcookie( 'et-editor-available-post-' . $post_id . '-fb', 'fb', time() + ( MINUTE_IN_SECONDS * 30 ), SITECOOKIEPATH, false, is_ssl() );
	}
}
add_action( 'et_fb_framework_loaded', 'et_fb_set_editor_available_cookie' );
