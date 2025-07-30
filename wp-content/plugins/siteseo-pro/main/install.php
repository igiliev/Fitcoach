<?php
/*
* SITESEO
* https://siteseo.io
* (c) SiteSEO Team
*/

namespace SiteSEOPro;

if(!defined('ABSPATH')){
    die('HACKING ATTEMPT!');
}


class Install{

	static function activate(){
		update_option('siteseo_pro_version', SITESEO_PRO_VERSION);
	}
	
	static function deactivate(){
		global $wpdb;

		wp_clear_scheduled_hook('siteseo_send_404_report_email');
		wp_clear_scheduled_hook('siteseo_404_cleanup');
	}
	
	static function uninstall(){
		global $wpdb;
		
		$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."siteseo_redirect_logs`");

		delete_option('siteseo_pro_version');
		delete_option('siteseo_pro_options');
		delete_option('siteseo_pro_page_speed');
		delete_option('siteseo_license');
	}
	
}