<?php
/*
* CookieAdmin
* https://cookieadmin.net
* (c) Softaculous Team
*/

if(!defined('ABSPATH')){
	die('Hacking Attempt!');
}

function cookieadmin_pro_autoloader($class){
	
	if(!preg_match('/^CookieAdminPro\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	if(strpos($class, 'CookieAdminPro\lib') === 0){
		if(file_exists(COOKIEADMIN_PRO_DIR.$m[1].'.php')){
			include_once(COOKIEADMIN_PRO_DIR.$m[1].'.php');
		}
	}

	// For Pro
	if(file_exists(COOKIEADMIN_PRO_DIR.'includes/'.strtolower($m[1]).'.php')){
		include_once(COOKIEADMIN_PRO_DIR.'includes/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register(__NAMESPACE__.'\cookieadmin_pro_autoloader');


if(!class_exists('CookieAdminPro')){
#[\AllowDynamicProperties]
class CookieAdminPro{
}
}

// Prevent update of cookieadmin free
// This also work for auto update
add_filter('site_transient_update_plugins', 'cookieadmin_pro_disable_manual_update_for_plugin');
add_filter('pre_site_transient_update_plugins', 'cookieadmin_pro_disable_manual_update_for_plugin');

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'cookieadmin_pro_update_free_after_pro', 10, 2);

// Add action to load CookieAdmin
add_action('plugins_loaded', 'cookieadmin_pro_load_plugin');
function cookieadmin_pro_load_plugin(){
	global $cookieadmin;
	
	// Load license
	cookieadmin_pro_load_license();
	
	cookieadmin_pro_update_checker();

	if(current_user_can('activate_plugins')){
		add_action('admin_notices', 'cookieadmin_pro_free_version_nag');
		
		// Softaculous Common notice to show that the license has expired.
		if(!empty($cookieadmin['license']) && empty($cookieadmin['license']['active']) && strpos($cookieadmin['license']['license'], 'SOFTWP') !== FALSE){
			add_action('admin_notices', 'cookieadmin_pro_expiry_notice');
			add_filter('softaculous_expired_licenses', 'cookieadmin_pro_plugins_expired');
		}
	}
	
	if(wp_doing_ajax()){
		add_action('wp_ajax_cookieadmin_pro_ajax_handler', 'cookieadmin_pro_ajax_handler');
		add_action('wp_ajax_nopriv_cookieadmin_pro_ajax_handler', 'cookieadmin_pro_ajax_handler');
	}
	
	// Check for updates
	include_once(COOKIEADMIN_PRO_DIR.'/includes/plugin-update-checker.php');
	$cookieadmin_updater = CookieAdmin_PucFactory::buildUpdateChecker(cookieadmin_pro_api_url().'updates.php?version='.COOKIEADMIN_PRO_VERSION, COOKIEADMIN_PRO_FILE);
	
	// Add the license key to query arguments
	$cookieadmin_updater->addQueryArgFilter('cookieadmin_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-cookieadmin-pro', 'cookieadmin_pro_updater_check_link', 10, 1);
	
	if(is_admin()){
		return cookieadmin_pro_load_plugin_admin();
	}
	
	add_action('wp_enqueue_scripts', '\CookieAdminPro\Enduser::enqueue_scripts');
	
}

function cookieadmin_pro_load_plugin_admin(){
	
	global $cookieadmin;
	
	if(!is_admin() || !current_user_can('administrator')){
		return false;
	}
	
	add_action('admin_enqueue_scripts', '\CookieAdminPro\Admin::enqueue_scripts');
	
	add_action('admin_menu', '\CookieAdminPro\Admin::plugin_menu');
	
}

function cookieadmin_pro_free_version_nag(){
	
	if(!defined('COOKIEADMIN_VERSION')){
		return;
	}

	$dismissed_free = (int) get_option('cookieadmin_version_free_nag');
	$dismissed_pro = (int) get_option('cookieadmin_version_pro_nag');

	// Checking if time has passed since the dismiss.
	if(!empty($dismissed_free) && time() < $dismissed_pro && !empty($dismissed_pro) && time() < $dismissed_pro){
		return;
	}

	$showing_error = false;
	if(version_compare(COOKIEADMIN_VERSION, COOKIEADMIN_PRO_VERSION) > 0 && (empty($dismissed_pro) || time() > $dismissed_pro)){
		$showing_error = true;

		echo '<div class="notice notice-warning is-dismissible" id="cookieadmin-pro-version-notice" onclick="cookieadmin_pro_dismiss_notice(event)" data-type="pro">
		<p style="font-size:16px;">'.esc_html__('You are using an older version of CookieAdmin Pro. We recommend updating to the latest version to ensure seamless and uninterrupted use of the application.', 'cookieadmin-pro').'</p>
	</div>';
	}elseif(version_compare(COOKIEADMIN_VERSION, COOKIEADMIN_PRO_VERSION) < 0 && (empty($dismissed_free) || time() > $dismissed_free)){
		$showing_error = true;

		echo '<div class="notice notice-warning is-dismissible" id="cookieadmin-pro-version-notice" onclick="cookieadmin_pro_dismiss_notice(event)" data-type="free">
		<p style="font-size:16px;">'.esc_html__('You are using an older version of CookieAdmin. We recommend updating to the latest free version to ensure smooth and uninterrupted use of the application.', 'cookieadmin-pro').'</p>
	</div>';
	}
	
	if(!empty($showing_error)){
		wp_register_script('cookieadmin-pro-version-notice', '', array('jquery'), COOKIEADMIN_PRO_VERSION, true );
		wp_enqueue_script('cookieadmin-pro-version-notice');
		wp_add_inline_script('cookieadmin-pro-version-notice', '
	function cookieadmin_pro_dismiss_notice(e){
		e.preventDefault();
		let target = jQuery(e.target);

		if(!target.hasClass("notice-dismiss")){
			return;
		}

		let jEle = target.closest("#cookieadmin-pro-version-notice"),
		type = jEle.data("type");

		jEle.slideUp();
		
		jQuery.post("'.admin_url('admin-ajax.php').'", {
			cookieadmin_pro_security : "'.wp_create_nonce('cookieadmin_pro_admin_js_nonce').'",
			action: "cookieadmin_pro_ajax_handler",
			cookieadmin_act: "version_notice",
			type: type
		}, function(res){
			if(!res["success"]){
				alert(res["data"]);
			}
		}).fail(function(data){
			alert("There seems to be some issue dismissing this alert");
		});
	}');
	}
}
