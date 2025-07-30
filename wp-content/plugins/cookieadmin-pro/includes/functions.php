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

function cookieadmin_pro_activation(){
	update_option('cookieadmin_pro_version', COOKIEADMIN_PRO_VERSION);
	
	include_once(COOKIEADMIN_PRO_DIR . 'includes/database.php');
	
	\CookieAdminPro\Database::activate();
}

function cookieadmin_pro_is_network_active($plugin_name){
	$is_network_wide = false;
	
	// Handling network site
	if(!is_multisite()){
		return $is_network_wide;
	}
	
	$_tmp_plugins = get_site_option('active_sitewide_plugins');

	if(!empty($_tmp_plugins) && preg_grep('/.*\/'.$plugin_name.'\.php$/', array_keys($_tmp_plugins))){
		$is_network_wide = true;
	}
	
	return $is_network_wide;
}

function cookieadmin_pro_update_checker(){
	
	$current_version = get_option('cookieadmin_pro_version', '0.0');
	$version = (int) str_replace('.', '', $current_version);

	// No update required
	if($current_version == COOKIEADMIN_PRO_VERSION){
		return true;
	}
	
	$is_network_wide = cookieadmin_pro_is_network_active('cookieadmin');
	
	if($is_network_wide){
		$free_ins = get_site_option('cookieadmin_free_installed');
	}else{
		$free_ins = get_option('cookieadmin_free_installed');
	}
	
	// If plugin runing reached here it means CookieAdmin free installed 
	if(empty($free_ins)){
		if($is_network_wide){
			update_site_option('cookieadmin_free_installed', time());
		}else{
			update_option('cookieadmin_free_installed', time());
		}
	}
	
	update_option('cookieadmin_version_pro_nag', time());
	update_option('cookieadmin_version_free_nag', time());
	update_option('cookieadmin_pro_version', COOKIEADMIN_PRO_VERSION);
}


// Load license data
function cookieadmin_pro_load_license($parent = 0){
	
	global $cookieadmin, $lic_resp;
	
	$license_field = 'cookieadmin_license';
	$license_api_url = COOKIEADMIN_API;
	
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
			$soft_lic = get_option('softaculous_pro_license', []);
			
			if(!empty($soft_lic)){
				return cookieadmin_pro_load_license(1);
			}
		}
	}
	
	if(!empty($lic['license'])){
		$cookieadmin['license'] = $lic;
	}
	
}

add_filter('softaculous_pro_products', 'cookieadmin_pro_softaculous_pro_products', 10, 1);
function cookieadmin_pro_softaculous_pro_products($r = []){
	$r['cookieadmin'] = 'cookieadmin';
	return $r;
}

// Add our license key if ANY
function cookieadmin_pro_updater_filter_args($queryArgs){
	
	global $cookieadmin;
	
	if (!empty($cookieadmin['license']['license'])){
		$queryArgs['license'] = $cookieadmin['license']['license'];
	}
	
	$queryArgs['url'] = rawurlencode(site_url());
	
	return $queryArgs;
}

function cookieadmin_pro_ajax_handler(){
	
	$cookieadmin_fn = (!empty($_REQUEST['cookieadmin_act']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_act'])) : '');
	
	if(empty($cookieadmin_fn)){
		wp_send_json_error(array('message' => 'Action not posted'));
	}
	
	// Define a whitelist of allowed functions
	$user_allowed_actions = array(
		'save_consent' => 'save_consent'
	);
	
	$admin_allowed_actions = array(
		'export_logs' => 'export_logs',
		'get_consent_logs' => 'get_consent_logs',
		'dismiss_expired_licenses' => 'dismiss_expired_licenses',
		'version_notice' => 'version_notice'
	);
	
	if(array_key_exists($cookieadmin_fn, $user_allowed_actions)){
		
		check_ajax_referer('cookieadmin_pro_js_nonce', 'cookieadmin_pro_security');
		header_remove('Set-Cookie');
		call_user_func('\CookieAdminPro\Enduser::'.$user_allowed_actions[$cookieadmin_fn]);
		
	}elseif(array_key_exists($cookieadmin_fn, $admin_allowed_actions)){
		
		check_ajax_referer('cookieadmin_pro_admin_js_nonce', 'cookieadmin_pro_security');
	 
		if(!current_user_can('administrator')){
			wp_send_json_error(array('message' => 'Sorry, but you do not have permissions to perform this action'));
		}
		
		call_user_func('\CookieAdminPro\Admin::'.$admin_allowed_actions[$cookieadmin_fn]);
		
	}else{
		wp_send_json_error(array('message' => 'Unauthorized action'));
	}
	
}

// Handle the Check for update link and ask to install license key
function cookieadmin_pro_updater_check_link($final_link){
	
	global $cookieadmin;
	
	if(empty($cookieadmin['license']['license'])){
		return '<a href="'.admin_url('admin.php?page=cookieadmin-license').'">'.esc_html__('Install CookieAdmin Pro License Key', 'cookieadmin-pro').'</a>';
	}
	
	return $final_link;
}

// Prevent update of cookieadmin free
function cookieadmin_pro_get_free_version_num(){
		
	if(defined('COOKIEADMIN_VERSION')){
		return COOKIEADMIN_VERSION;
	}
	
	// In case of cookieadmin deactive
	return cookieadmin_pro_file_get_version_num('cookieadmin/cookieadmin.php');
}

// Prevent update of cookieadmin free
function cookieadmin_pro_file_get_version_num($plugin){
	
	// In case of cookieadmin deactive
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/'.$plugin);
	
	if(empty($plugin_data)){
		return false;
	}
	
	return $plugin_data['Version'];
	
}

// Prevent update of cookieadmin free
function cookieadmin_pro_disable_manual_update_for_plugin($transient){
	$plugin = 'cookieadmin/cookieadmin.php';
	
	// Is update available?
	if(!isset($transient->response) || !isset($transient->response[$plugin])){
		return $transient;
	}
	
	$free_version = cookieadmin_pro_get_free_version_num();
	$pro_version = COOKIEADMIN_PRO_VERSION;
	
	if(!empty($GLOBALS['cookieadmin_pro_is_upgraded'])){
		$pro_version = cookieadmin_pro_file_get_version_num('cookieadmin-pro/cookieadmin-pro.php');
	}
	
	// Update the cookieadmin version to the equivalent of Pro version
	if(!empty($pro_version) && version_compare($free_version, $pro_version, '<')){
		$transient->response[$plugin]->new_version = $pro_version;
		$transient->response[$plugin]->package = 'https://downloads.wordpress.org/plugin/cookieadmin.'.$pro_version.'.zip';
	}else{
		unset($transient->response[$plugin]);
	}

	return $transient;
}

// Auto update free version after update pro version
function cookieadmin_pro_update_free_after_pro($upgrader_object, $options){
	
	// Check if the action is an update for the plugins
	if($options['action'] != 'update' || $options['type'] != 'plugin'){
		return;
	}
		
	// Define the slugs for the free and pro plugins
	$free_slug = 'cookieadmin/cookieadmin.php'; 
	$pro_slug = 'cookieadmin-pro/cookieadmin-pro.php';

	// Check if the pro plugin is in the list of updated plugins
	if( 
		(isset($options['plugins']) && in_array($pro_slug, $options['plugins']) && !in_array($free_slug, $options['plugins'])) ||
		(isset($options['plugin']) && $pro_slug == $options['plugin'])
	){
	
		// Trigger the update for the free plugin
		$current_version = cookieadmin_pro_get_free_version_num();
		
		if(empty($current_version)){
			return;
		}
		
		$GLOBALS['cookieadmin_pro_is_upgraded'] = true;
		
		// This will set the 'update_plugins' transient again
		wp_update_plugins();

		// Check for updates for the free plugin
		$update_plugins = get_site_transient('update_plugins');
		
		if(empty($update_plugins) || !isset($update_plugins->response[$free_slug]) || version_compare($update_plugins->response[$free_slug]->new_version, $current_version, '<=')){
			return;
		}
		
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
		
		$skin = wp_doing_ajax()? new WP_Ajax_Upgrader_Skin() : null;
		
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

function cookieadmin_pro_api_url($main_server = 0, $suffix = 'cookieadmin'){
	
	global $cookieadmin;
	
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
	
	if(!empty($main_server) && $main_server < 0){
		return $mirror;
	}
	
	// If the license is newly issued, we need to fetch from API only
	if(!empty($main_server) || empty($cookieadmin['license']['last_edit']) || 
		(!empty($cookieadmin['license']['last_edit']) && (time() - 3600) < $cookieadmin['license']['last_edit'])
	){
		$mirror = COOKIEADMIN_API;
	}
	
	if(!empty($suffix)){
		$mirror = str_replace('/softwp', '/'.$suffix, $mirror);
	}
	
	return $mirror;
	
}

function cookieadmin_pro_plugins_expired($plugins){
	$plugins[] = 'CookieAdmin';
	return $plugins;
}

function cookieadmin_pro_expiry_notice(){
	global $cookieadmin;

	// The combined notice for all Softaculous plugin to show that the license has expired
	$dismissed_at = get_option('softaculous_expired_licenses', 0);
	$expired_plugins = apply_filters('softaculous_expired_licenses', []);
	if(
		!empty($expired_plugins) && 
		is_array($expired_plugins) && 
		!defined('SOFTACULOUS_EXPIRY_LICENSES') && 
		(empty($dismissed_at) || ($dismissed_at + WEEK_IN_SECONDS) < time())
	){

		define('SOFTACULOUS_EXPIRY_LICENSES', true); // To make sure other plugins don't return a Notice
		echo '<div class="notice notice-error is-dismissible" id="cookieadmin-pro-expiry-notice">
				<p>'.sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please renew it to continue receiving uninterrupted updates and support for %3$s.', 'cookieadmin-pro'),
				'<font style="color:red;"><b>',
				'</b></font>',
				esc_html(implode(', ', $expired_plugins))
				). '</p>
			</div>';

		wp_register_script('cookieadmin-pro-expiry-notice', '', ['jquery'], COOKIEADMIN_PRO_VERSION, true);
		wp_enqueue_script('cookieadmin-pro-expiry-notice');
		wp_add_inline_script('cookieadmin-pro-expiry-notice', '
		jQuery(document).ready(function(){
			jQuery("#cookieadmin-pro-expiry-notice").on("click", ".notice-dismiss", function(e){
				e.preventDefault();
				let target = jQuery(e.target);

				let jEle = target.closest("#cookieadmin-pro-expiry-notice");
				jEle.slideUp();

				jQuery.post("'.admin_url('admin-ajax.php').'", {
					cookieadmin_pro_security : "'.wp_create_nonce('cookieadmin_pro_admin_js_nonce').'",
					action: "cookieadmin_pro_ajax_handler",
					cookieadmin_act: "dismiss_expired_licenses",
				}, function(res){
					if(!res["success"]){
						alert(res["data"]);
					}
				}).fail(function(data){
					alert("There seems to be some issue dismissing this alert");
				});
			});
		})');
	}
}

function cookieadmin_pro_human_readable_time($timestamp){
	
	$now = time();
	$today_start = strtotime('today');
	$yesterday_start = strtotime('yesterday');

	if ($timestamp >= $today_start) {
		return 'Today ' . wp_date('g:i A T', $timestamp);
	} elseif ($timestamp >= $yesterday_start) {
		return 'Yesterday ' . wp_date('g:i A T', $timestamp);
	} else {
		return wp_date('M j Y g:i A T', $timestamp); // e.g., Dec 6 2024 6:00 AM UTC
	}
}