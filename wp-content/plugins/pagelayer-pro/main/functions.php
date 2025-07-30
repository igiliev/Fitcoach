<?php

//////////////////////////////////////////////////////////////
//===========================================================
// functions.php
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

// Is called when the ADMIN enables the plugin
function pagelayer_pro_activation(){

	global $wpdb;

	$sql = array();

	add_option('pagelayer_pro_version', PAGELAYER_PRO_VERSION);
	add_option('pagelayer_pro_options', array());	
	
	// Update free verion
	pagelayer_pro_update_free_after_pro(null, array(
		'action' => 'update',
		'type' => 'plugin',
		'plugin' => 'pagelayer-pro/pagelayer-pro.php',
		'silent_skin' => true,
	));

}

// Checks if we are to update ?
function pagelayer_pro_update_check(){

global $wpdb;

	$sql = array();
	$current_version = get_option('pagelayer_pro_version');
	$version = (int) str_replace('.', '', $current_version);

	// No update required
	if($current_version == PAGELAYER_PRO_VERSION){
		return true;
	}

	// Is it first run ?
	if(empty($current_version)){

		// Reinstall
		pagelayer_pro_activation();

		// Trick the following if conditions to not run
		$version = (int) str_replace('.', '', PAGELAYER_PRO_VERSION);

	}
	
	$is_network_wide = pagelayer_pro_is_network_active('pagelayer-pro');
	
	if($is_network_wide){
		$free_ins = get_site_option('pagelayer_free_installed');
	}else{
		$free_ins = get_option('pagelayer_free_installed');
	}
		
	// If plugin runing reached here it means Pagelayer free installed 
	if(empty($free_ins)){
		if($is_network_wide){
			update_site_option('pagelayer_free_installed', time());
		}else{
			update_option('pagelayer_free_installed', time());
		}
	}
	
	// Re-init NAG dismiss expire time
	update_option('pagelayer_pro_older_pro_version_nag', time());
	update_option('pagelayer_pro_older_free_version_nag', time());
	
	// Save the new Version
	update_option('pagelayer_pro_version', PAGELAYER_PRO_VERSION);

}

// Load license data
function pagelayer_pro_load_license($parent = 0){
	
	global $pagelayer, $lic_resp;
	
	$license_field = 'pagelayer_license';
	$license_api_url = PAGELAYER_PRO_API;
	
	// Save license
	if(!empty($parent) && is_string($parent) && strlen($parent) > 5){		
		$lic['license'] = $parent;
	
	// Load license of Soft Pro
	}elseif(!empty($parent)){
		$license_field = 'softaculous_pro_license';
		$lic = get_option('softaculous_pro_license', []);
	
	// My license
	}else{
		$lic = get_option($license_field, []);
	}
	
	// Loaded license is a Soft Pro
	if(!empty($lic['license']) && preg_match('/^softwp/is', $lic['license'])){
		$license_field = 'softaculous_pro_license';
		$license_api_url = 'https://a.softaculous.com/softwp/';
		$prods = apply_filters('softaculous_pro_products', []);
	}else{
		$prods = [];
	}

	if(empty($lic['last_update'])){
		$lic['last_update'] = time() - 86600;
	}
	
	// Update license details as well
	if(!empty($lic) && !empty($lic['license']) && (time() - @$lic['last_update']) >= 86400){
		
		$url = $license_api_url.'/license.php?license='.$lic['license'].'&prods='.implode(',', $prods).'&url='.rawurlencode(site_url());
		$resp = wp_remote_get($url);
		$lic_resp = $resp;

		//Did we get a response ?
		if(is_array($resp)){
			
			$tosave = json_decode($resp['body'], true);
			
			//Is it the license ?
			if(!empty($tosave['license'])){
				$tosave['last_update'] = time();
				update_option($license_field, $tosave);
				$lic = $tosave;
			}
		}
	}
	
	// If the license is Free or Expired check for Softaculous Pro license
	if(empty($lic) || empty($lic['active'])){
		
		if(function_exists('softaculous_pro_load_license')){
			$softaculous_license = softaculous_pro_load_license();
			if(!empty($softaculous_license['license']) && 
				(!empty($softaculous_license['active']) || empty($lic['license']))
			){
				$lic = $softaculous_license;
			}
		}elseif(empty($parent)){
			$lic = get_option('softaculous_pro_license', []);
			
			if(!empty($lic)){
				return pagelayer_pro_load_license(1);
			}
		}
	}
	
	if(!empty($lic['license'])){
		$pagelayer->license = $lic;
	}
	
}

add_filter('softaculous_pro_products', 'pagelayer_softaculous_pro_products', 10, 1);
function pagelayer_softaculous_pro_products($r = []){
	$r['pagelayer'] = 'pagelayer';
	return $r;
}

// Add our license key if ANY
function pagelayer_pro_updater_filter_args($queryArgs) {
	
	global $pagelayer;
	
	if ( !empty($pagelayer->license['license']) ) {
		$queryArgs['license'] = $pagelayer->license['license'];
	}
	
	$queryArgs['url'] = rawurlencode(site_url());
	
	return $queryArgs;
}

// Handle the Check for update link and ask to install license key
function pagelayer_pro_updater_check_link($final_link){
	
	global $pagelayer;
	
	if(empty($pagelayer->license['license'])){
		return '<a href="'.admin_url('admin.php?page=pagelayer_license').'">Install Pagelayer Pro License Key</a>';
	}
	
	return $final_link;
}

function pagelayer_pro_is_network_active($pluign){
	$is_network_wide = false;
	
	// Handling network site
	if(!is_multisite()){
		return $is_network_wide;
	}
	
	$_tmp_plugins = get_site_option('active_sitewide_plugins');

	if(!empty($_tmp_plugins) && preg_grep('/.*\/'.$pluign.'\.php$/', array_keys($_tmp_plugins))){
		$is_network_wide = true;
	}
	
	return $is_network_wide;
}

// Prevent update of pagelayer free
function pagelayer_pro_get_free_version_num(){
		
	if(defined('PAGELAYER_VERSION')){
		return PAGELAYER_VERSION;
	}
	
	// In case of pagelayer deactive
	return pagelayer_pro_file_get_version_num('pagelayer/pagelayer.php');
}

// Prevent update of pagelayer free
function pagelayer_pro_file_get_version_num($plugin){
	
	$file = WP_PLUGIN_DIR . '/'.$plugin;
	
	if(!file_exists($file)){
		return false;
	}
	
	// In case of pagelayer deactive
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	
	$plugin_data = get_plugin_data($file);
	
	if(empty($plugin_data)){
		return false;
	}
	
	return $plugin_data['Version'];
	
}

// Prevent update of pagelayer free
function pagelayer_pro_disable_manual_update_for_plugin($transient){
	$plugin = 'pagelayer/pagelayer.php';
	
	// Is update available?
	if(!isset($transient->response) || !isset($transient->response[$plugin])){
		return $transient;
	}
	
	$free_version = pagelayer_pro_get_free_version_num();
	$pro_version = PAGELAYER_PRO_VERSION;
	
	if(!empty($GLOBALS['pagelayer_pro_is_upgraded'])){
		$pro_version = pagelayer_pro_file_get_version_num('pagelayer-pro/pagelayer-pro.php');
	}
	
	// Update the Pagelayer version to the equivalent of Pro version
	if(!empty($pro_version) && version_compare($free_version, $pro_version, '<')){
		$transient->response[$plugin]->new_version = $pro_version;
		$transient->response[$plugin]->package = 'https://downloads.wordpress.org/plugin/pagelayer.'.$pro_version.'.zip';
	}else{
		unset($transient->response[$plugin]);
	}

	return $transient;
}

// Auto update free version after update pro version
function pagelayer_pro_update_free_after_pro($upgrader_object, $options){
	
	// Check if the action is an update for the plugins
	if($options['action'] != 'update' || $options['type'] != 'plugin'){
		return;
	}
		
	// Define the slugs for the free and pro plugins
	$free_slug = 'pagelayer/pagelayer.php'; 
	$pro_slug = 'pagelayer-pro/pagelayer-pro.php';

	// Check if the pro plugin is in the list of updated plugins
	if( 
		(isset($options['plugins']) && in_array($pro_slug, $options['plugins']) && !in_array($free_slug, $options['plugins'])) ||
		(isset($options['plugin']) && $pro_slug == $options['plugin'])
	){
	
		// Trigger the update for the free plugin
		$current_version = pagelayer_pro_get_free_version_num();
		
		if(empty($current_version)){
			return;
		}
		
		$GLOBALS['pagelayer_pro_is_upgraded'] = true;
		
		// This will set the 'update_plugins' transient again
		wp_update_plugins();

		// Check for updates for the free plugin
		$update_plugins = get_site_transient('update_plugins');
		
		if(empty($update_plugins) || !isset($update_plugins->response[$free_slug]) || version_compare($update_plugins->response[$free_slug]->new_version, $current_version, '<=')){
			return;
		}
		
		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/misc.php');
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
		
		$skin = wp_doing_ajax()? new WP_Ajax_Upgrader_Skin() : null;
		
		if(!empty($options['silent_skin'])){
			class Pagelayer_Silent_Upgrader_Skin extends WP_Upgrader_Skin {
				public function feedback($string, ...$args){}
				public function header(){}
				public function footer(){}
				public function error($errors){}
				public function before(){}
				public function after(){}
			}
			$skin = new Pagelayer_Silent_Upgrader_Skin();
		}
		
		$upgrader = new Plugin_Upgrader($skin);
		$upgraded = $upgrader->upgrade($free_slug);
		
		if(!is_wp_error($upgraded) && $upgraded){
			// Re-active free plugins
			if( file_exists( WP_PLUGIN_DIR . '/'.  $free_slug ) && is_plugin_inactive($free_slug) ){
				activate_plugin($free_slug); // TODO for network
			}
			
			// Re-active pro plugins
			if( file_exists( WP_PLUGIN_DIR . '/'.  $pro_slug ) && is_plugin_inactive($pro_slug) ){
				activate_plugin($pro_slug); // TODO for network
			}
		}
	}
}

function pagelayer_pro_api_url($main_server = 0, $suffix = 'pagelayer'){
	
	global $pagelayer;
	
	$r = array(
		'https://s0.softaculous.com/a/softwp/',
		'https://s1.softaculous.com/a/softwp/',
		'https://s2.softaculous.com/a/softwp/',
		'https://s3.softaculous.com/a/softwp/',
		'https://s4.softaculous.com/a/softwp/',
		'https://s5.softaculous.com/a/softwp/',
		'https://s7.softaculous.com/a/softwp/',
		'https://s8.softaculous.com/a/softwp/'
	);
	
	$mirror = $r[array_rand($r)];
	
	// If the license is newly issued, we need to fetch from API only
	if(!empty($main_server) || empty($pagelayer->license['last_edit']) || 
		(!empty($pagelayer->license['last_edit']) && (time() - 3600) < $pagelayer->license['last_edit'])
	){
		$mirror = PAGELAYER_PRO_API;
	}
	
	if(!empty($suffix)){
		$mirror = str_replace('/softwp', '/'.$suffix, $mirror);
	}
	
	return $mirror;
	
}