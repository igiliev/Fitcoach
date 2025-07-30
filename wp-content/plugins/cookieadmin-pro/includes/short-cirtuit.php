<?php
/*
* CookieAdmin
* https://cookieadmin.net
* (c) Softaculous Team
*/

// Are we being accessed directly ?
if(!defined('COOKIEADMIN_PRO_VERSION')) {
	exit('Hacking Attempt !');
}

// Prevent update of cookieadmin free
// This also work for auto update
add_filter('site_transient_update_plugins', 'cookieadmin_pro_disable_manual_update_for_plugin');
add_filter('pre_site_transient_update_plugins', 'cookieadmin_pro_disable_manual_update_for_plugin');

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'cookieadmin_pro_update_free_after_pro', 10, 2);

add_action('plugins_loaded', 'cookieadmin_pro_load_plugin');
function cookieadmin_pro_load_plugin(){
	global $cookieadmin;

	// Load license
	cookieadmin_pro_load_license();
	
	cookieadmin_pro_update_checker();
	
	// Check for updates
	include_once(COOKIEADMIN_PRO_DIR.'/includes/plugin-update-checker.php');
	$cookieadmin_updater = CookieAdmin_PucFactory::buildUpdateChecker(cookieadmin_pro_api_url().'updates.php?version='.COOKIEADMIN_PRO_VERSION, COOKIEADMIN_PRO_FILE);
	
	// Add the license key to query arguments
	$cookieadmin_updater->addQueryArgFilter('cookieadmin_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-cookieadmin-pro', 'cookieadmin_pro_updater_check_link', 10, 1);
	
	// Nag informing the user to install the free version.
	if(current_user_can('activate_plugins')){
		add_action('admin_notices', 'cookieadmin_pro_free_version_nag', 9);
		add_action('admin_menu', 'cookieadmin_pro_add_menu', 9);
	}

	$is_network_wide = cookieadmin_pro_is_network_active('cookieadmin-pro');
	$_do_version = get_option('cookieadmin_version');
	$req_free_update = !empty($_do_version) && version_compare($_do_version, COOKIEADMIN_PRO_VERSION, '<');
	
	if($is_network_wide){
		$free_installed = get_site_option('cookieadmin_free_installed');
	}else{
		$free_installed = get_option('cookieadmin_free_installed');
	}
	
	if(!empty($free_installed)){
		return;
	}
	
	// Include the necessary stuff
	include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	include_once(ABSPATH . 'wp-admin/includes/file.php');
	
	if(file_exists(WP_PLUGIN_DIR . '/cookieadmin/cookieadmin.php') && is_plugin_inactive('/cookieadmin/cookieadmin.php') && empty($req_free_update)) {
		
		if($is_network_wide){
			update_site_option('cookieadmin_free_installed', time());
		}else{
			update_option('cookieadmin_free_installed', time());
		}
		
		activate_plugin('/cookieadmin/cookieadmin.php', '', $is_network_wide);
		remove_action('admin_notices', 'cookieadmin_pro_free_version_nag', 9);
		remove_action('admin_menu', 'cookieadmin_pro_add_menu', 9);
		return;
	}
	
	// Includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
	include_once(ABSPATH . 'wp-admin/includes/misc.php');
	include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

	// Filter to prevent the activate text
	add_filter('install_plugin_complete_actions', 'cookieadmin_pro_prevent_activation_text', 10, 3);
	 
	$upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
	
	// Upgrade the plugin to the latest version of free already installed.
	if(!empty($req_free_update)){
		$installed = $upgrader->upgrade('cookieadmin/cookieadmin.php');
	}else{
		$installed = $upgrader->install('https://downloads.wordpress.org/plugin/cookieadmin.zip');
	}
	
	if(!is_wp_error($installed) && $installed){
		
		if($is_network_wide){
			update_site_option('cookieadmin_free_installed', time());
		}else{
			update_option('cookieadmin_free_installed', time());
		}
		
		activate_plugin('cookieadmin/cookieadmin.php', '', $is_network_wide);
		remove_action('admin_notices', 'cookieadmin_pro_free_version_nag', 9);
		remove_action('admin_menu', 'cookieadmin_pro_add_menu', 9);
	}
}

// Do not shows the activation text if 
function cookieadmin_pro_prevent_activation_text($install_actions, $api, $plugin_file){
	if($plugin_file == 'cookieadmin/cookieadmin.php'){
		return array();
	}

	return $install_actions;
}

function cookieadmin_pro_free_version_nag(){
	
	$cookieadmin_version = get_option('cookieadmin_version');
	
	$lower_version = __('You have not installed the free version of CookieAdmin. CookieAdmin Pro depends on the free version, so you must install it first in order to use CookieAdmin Pro.', 'cookieadmin-pro');

	echo '<div class="notice notice-error">
		<p style="font-size:16px;">'.esc_html($lower_version).' <a href="'.admin_url('plugin-install.php?s=cookieadmin&tab=search').'" class="button button-primary">Install/Update Now</a></p>
	</div>';
}

function cookieadmin_pro_add_menu(){
	add_menu_page('CookieAdmin Settings', 'CookieAdmin', 'activate_plugins', 'cookieadmin', 'cookieadmin_pro_menu_page');
}

function cookieadmin_pro_menu_page(){
	echo '<div style="color: #333;padding: 50px;text-align: center;">
		<h1 style="font-size: 2em;margin-bottom: 10px;">CookieAdmin Free version is not installed / outdated!</h>
		<p style=" font-size: 16px;margin-bottom: 20px; font-weight:400;">'.esc_html__('CookieAdmin Pro depends on the free version of CookieAdmin, so you need to install / update the free version first.', 'cookieadmin-pro').'</p>
		<a href="'.admin_url('plugin-install.php?s=cookieadmin&tab=search').'" style="text-decoration: none;font-size:16px;">'.esc_html__('Install/Update Now', 'cookieadmin-pro').'</a>
	</div>';
}