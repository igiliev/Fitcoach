<?php

//////////////////////////////////////////////////////////////
//===========================================================
// pagelayer-init.php
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

// Ok so we are now ready to go
register_activation_hook(PAGELAYER_PRO_FILE, 'pagelayer_pro_activation');

// Prevent update of pagelayer free
// This also work for auto update
add_filter('site_transient_update_plugins', 'pagelayer_pro_disable_manual_update_for_plugin');
add_filter('pre_site_transient_update_plugins', 'pagelayer_pro_disable_manual_update_for_plugin');

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'pagelayer_pro_update_free_after_pro', 10, 2);

add_action('plugins_loaded', 'pagelayer_pro_load_plugin');
function pagelayer_pro_load_plugin(){
	global $pagelayer;
	
	if(empty($pagelayer)){
		$pagelayer = new stdClass();
	}
	
	// Load license
	pagelayer_pro_load_license();
	
	// Check if the installed version is outdated
	pagelayer_pro_update_check();
	
	// Check for updates
	include_once(PAGELAYER_PRO_DIR.'/main/plugin-update-checker.php');
	$pagelayer_updater = Pagelayer_PucFactory::buildUpdateChecker(pagelayer_pro_api_url().'updates.php?version='.PAGELAYER_PRO_VERSION, PAGELAYER_PRO_FILE);
	
	// Add the license key to query arguments
	$pagelayer_updater->addQueryArgFilter('pagelayer_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-pagelayer-pro', 'pagelayer_pro_updater_check_link', 10, 1);
	
	// Nag informing the user to install the free version.
	if(current_user_can('activate_plugins')){
		add_action('admin_notices', 'pagelayer_pro_free_version_nag', 9);
		add_action('admin_menu', 'pagelayer_pro_add_menu', 9);
	}
	
	$is_network_wide = pagelayer_pro_is_network_active('pagelayer-pro');
	$_pl_version = get_option('pagelayer_version');
	$req_free_update = !empty($_pl_version) && version_compare($_pl_version, '1.8.7', '<');
	
	if($is_network_wide){
		$pl_free_installed = get_site_option('pagelayer_free_installed');
	}else{
		$pl_free_installed = get_option('pagelayer_free_installed');
	}
	
	if(!empty($pl_free_installed)){
		return;
	}
	
	// Include the necessary stuff
	include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	include_once(ABSPATH . 'wp-admin/includes/file.php');
	
	if( file_exists( WP_PLUGIN_DIR . '/pagelayer/pagelayer.php' ) && is_plugin_inactive( '/pagelayer/pagelayer.php' ) && empty($req_free_update) ) {
		
		if($is_network_wide){
			update_site_option('pagelayer_free_installed', time());
		}else{
			update_option('pagelayer_free_installed', time());
		}
		
		activate_plugin('/pagelayer/pagelayer.php', '', $is_network_wide);
		remove_action('admin_notices', 'pagelayer_pro_free_version_nag', 9);
		remove_action('admin_menu', 'pagelayer_pro_add_menu', 9);
		return;
	}
	
	// Includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
	include_once(ABSPATH . 'wp-admin/includes/misc.php');
	include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

	// Filter to prevent the activate text
	add_filter('install_plugin_complete_actions', 'pagelayer_pro_prevent_activation_text', 10, 3);
	 
	$upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
	
	// Upgrade the plugin to the latest version of free already installed.
	if(!empty($req_free_update) && file_exists( WP_PLUGIN_DIR . '/pagelayer/pagelayer.php' )){
		$installed = $upgrader->upgrade('pagelayer/pagelayer.php');
	}else{
		$installed = $upgrader->install('https://downloads.wordpress.org/plugin/pagelayer.zip');
	}
	
	if(!is_wp_error($installed) && $installed){
		
		if($is_network_wide){
			update_site_option('pagelayer_free_installed', time());
		}else{
			update_option('pagelayer_free_installed', time());
		}
		
		activate_plugin('pagelayer/pagelayer.php', '', $is_network_wide);
		remove_action('admin_notices', 'pagelayer_pro_free_version_nag', 9);
		remove_action('admin_menu', 'pagelayer_pro_add_menu', 9);
		//wp_safe_redirect(admin_url('/'));
	}
}

// Do not shows the activation text if 
function pagelayer_pro_prevent_activation_text($install_actions, $api, $plugin_file){
	if($plugin_file == 'pagelayer/pagelayer.php'){
		return array();
	}

	return $install_actions;
}

function pagelayer_pro_free_version_nag(){
	
	$pl_version = get_option('pagelayer_version');
	
	$lower_version = __('You have not installed/activated the free version of Pagelayer. Pagelayer Pro depends on the free version, so you must install/activate it first in order to use Pagelayer Pro.');
	$btn_text = __('Install / Activate Now');
	
	if(!empty($pl_version) && version_compare($pl_version, '1.8.6', '<')){
		$lower_version = __('You are using an older version of the free version of Pagelayer, please update Pagelayer to work without any issues');
		$btn_text = __('Update Now');
	}

	echo '<div class="notice notice-error">
		<p style="font-size:16px;">'.esc_html($lower_version).' <a href="'.admin_url('plugin-install.php?s=pagelayer&tab=search').'" class="button button-primary">'.esc_html($btn_text).'</a></p>
	</div>';
}

function pagelayer_pro_add_menu(){
	add_menu_page('Pagelayer', 'Pagelayer Pro', 'activate_plugins', 'pagelayer-pro', 'pagelayer_pro_menu_page', PAGELAYER_PRO_URL.'/images/pagelayer-logo-19.png');
}

function pagelayer_pro_menu_page(){
	echo '<div style="color: #333;padding: 50px;text-align: center;">
		<h1 style="font-size: 2em;margin-bottom: 10px;">Pagelayer Free version is not installed / outdated!</h>
		<p style=" font-size: 16px;margin-bottom: 20px; font-weight:400;">Pagelayer Pro depends on the free version of Pagelayer, so you need to install / update the free version first.</p>
		<a href="'.admin_url('plugin-install.php?s=pagelayer&tab=search').'" style="text-decoration: none;font-size:16px;">Install/Update Now</a>
	</div>';
}