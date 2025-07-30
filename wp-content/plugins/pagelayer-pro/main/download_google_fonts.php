<?php

//////////////////////////////////////////////////////////////
//===========================================================
// download_google_fonts.php
//===========================================================
// PAGELAYER
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit Gupta
// Date:	   23rd Jan 2017
// Time:	   23:00 hrs
// Site:	   http://pagelayer.com/wordpress (PAGELAYER)
// ----------------------------------------------------------
// Please Read the Terms of use at http://pagelayer.com/tos
// ----------------------------------------------------------
//===========================================================
// (c)Pagelayer Team
//===========================================================
//////////////////////////////////////////////////////////////

// Are we being accessed directly ?
if(!defined('PAGELAYER_PRO_VERSION')) {
	exit('Hacking Attempt !');
}

// Download google fonts
function pagelayer_pro_download_google_fonts($url){
	global $wp_filesystem;
	
	if (!defined('FS_METHOD')) { 
		define( 'FS_METHOD', 'direct' );
	}

	$uploads_dir = wp_upload_dir();
	$font_url = $uploads_dir['baseurl'].'/pl-google-fonts';
	$font_dir = $uploads_dir['basedir'].'/pl-google-fonts';
	
	// Find hash of current url
	$url_md5 = md5($url);
	$local_path = $font_dir.'/'.$url_md5.'.css';

	if(file_exists( $local_path ) ){
		return $local_path;
	}

	// Load Wp filesystem class
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		$file_path = ABSPATH . 'site-admin/includes/file.php';
		$file_path = file_exists($file_path) ? $file_path : ABSPATH . 'wp-admin/includes/file.php';
		include $file_path;	
	}

	WP_Filesystem();

	// Is google fonts directory exists?
	if( !file_exists( $font_dir ) ){		
		wp_mkdir_p( $font_dir );
	}

	//verify fonts upload directory exists or not
	$fonts_dir = $font_dir.'/fonts';

	if( !file_exists( $fonts_dir ) ){		
		wp_mkdir_p($fonts_dir);
	}
	
	// Fetch stylesheet
	$useragent = array( 'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36');

	$response = wp_remote_get($url, $useragent);
	$response_code = wp_remote_retrieve_response_code($response);

	if ( is_wp_error( $response ) || $response_code !== 200 ) {
		return false;
	}
	
	$remote_file = wp_remote_retrieve_body($response);
	
	// Collect google fonts urls
	preg_match_all( '/url\(.*?\)/i', $remote_file, $font_links );

	if(!isset( $font_links[0] ) || count( $font_links[0] ) < 1 ) {
		return false;
	}

	foreach( $font_links[0] as $key => $_fontlink ){

		$download_url = str_ireplace( array('url(',')'), '', $_fontlink );
		
		if(empty($download_url)){
			continue;
		}

		$parse_url = parse_url($download_url);
		$font_name = basename($parse_url['path']);
		
		$local_file = $font_dir.'/fonts/'.$font_name;
		$_local_file = './fonts/'.$font_name;
		
		if( empty( $font_name ) || strpos($font_name, '.woff2') === false ){
			continue;
		}

		// Download font files
		if(!file_exists( $local_file ) ){

			$tmp_file = download_url(  $download_url );

			if(is_wp_error( $tmp_file )){
				continue;
			}

			$wp_filesystem->copy( $tmp_file, $local_file );
			$wp_filesystem->delete( $tmp_file );
		
		}

		// Update font links
		$remote_file = str_replace( $download_url, $_local_file, $remote_file );

	}
	
	// Save local stylesheet
	$wp_filesystem->put_contents( $local_path, $remote_file );
	
	return $local_path;
}