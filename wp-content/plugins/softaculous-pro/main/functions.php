<?php

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

$softaculous_pro_settings = get_option('softaculous_pro_settings', array());

if(!empty($softaculous_pro_settings['disable_comments'])){
	include_once SOFTACULOUS_PRO_PLUGIN_PATH . '/main/disable-comments.php';
}

if(is_admin() && empty($softaculous_pro_settings['disable_ai'])){
	add_action('enqueue_block_editor_assets', '\SoftWP\AI::enqueue_scripts');

	// AJAX ACTIONS
	add_action('wp_ajax_softaculous_ai_generation', '\SoftWP\AI::generate');
	add_action('wp_ajax_softaculous_ai_history', '\SoftWP\AI::load_history');
	add_action('wp_ajax_softaculous_ai_refresh_tokens', '\SoftWP\AI::update_tokens');
}

$spro_page = softaculous_pro_optGET('page');
if(is_admin() && !empty($spro_page) && $spro_page == 'assistant'){
	$spro_act = softaculous_pro_optGET('act');
	if(!empty($spro_act) && $spro_act == 'onboarding'){
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/onboarding.php');
	}
}

function softaculous_pro_activation_hook(){
	softaculous_pro_update_check();
}

function softaculous_pro_deactivation_hook() {
	
}

function softaculous_pro_uninstall_hook() {
	delete_option('softaculous_pro_version');
	delete_option('softaculous_pro_settings');
	delete_option('softaculous_pro_onboarding_notice_dismiss');
	delete_option('softaculous_pro_ai_notice_dismiss');
	delete_option('softaculous_pro_onboarding_done');
	delete_option('softaculous_pro_onboarding_dismiss');
	delete_option('softaculous_pro_setup_info');
	delete_option('softaculous_pro_onboarding_shown');
	delete_option('softaculous_pro_ai_token_notice_dismiss');

	wp_clear_scheduled_hook('softaculous_pro_ai_history_cron');
}

function softaculous_pro_load_plugin(){
	
	global $softaculous_pro, $softaculous_pro_settings, $spro_tours;
	
	
	add_action('softaculous_pro_ai_history_cron', '\SoftWP\AI::delete_history'); // Cron for AI History deletion
	if(!is_admin()){
		return false;
	}
	
	// Do we have to redirect the user to onboarding ? 
	if(is_admin() && current_user_can('administrator') && !wp_doing_ajax() && (empty($_GET['act']) || ($_GET['act'] != 'onboarding' && $_GET['act'] != 'license'))){
		
		$spro_is_dismissed = get_option('softaculous_pro_onboarding_dismiss');
		$spro_is_done = get_option('softaculous_pro_onboarding_done');
		$spro_is_shown = get_option('softaculous_pro_onboarding_shown');
		
		if(empty($spro_is_dismissed) && empty($spro_is_done) && empty($spro_is_shown)){
			wp_safe_redirect(admin_url('admin.php?page=assistant&act=onboarding'));
			exit(0);
		}
	}

	softaculous_pro_load_license();
	
	softaculous_pro_rebranding();
	
	// Show key details in Plugins data
	add_filter('plugin_row_meta', 'softaculous_pro_add_plugin_row_links', 10, 2);
	
	// Check for updates
	include_once(SOFTACULOUS_PRO_PLUGIN_PATH.'/lib/plugin-update-checker.php');
	$softaculous_pro_updater = SoftaculousPro_PucFactory::buildUpdateChecker(softaculous_pro_api_url().'updates.php?version='.SOFTACULOUS_PRO_VERSION.'&url='.rawurlencode(site_url()), SOFTACULOUS_PRO_FILE);
	
	// Add the license key to query arguments
	$softaculous_pro_updater->addQueryArgFilter('softaculous_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-softaculous-pro', 'softaculous_pro_updater_check_link', 10, 1);
	
	softaculous_pro_update_check();
	
	// Register AI Post type
	add_action('init', 'softaculous_pro_register_post_type');
	
	$spro_tours = array('assistant' => 'admin.php?page=assistant', 'sidebar' => 'admin.php?page=assistant', 'dashboard' => 'index.php', 'plugins' => 'plugins.php', 'themes' => 'themes.php', 'users' => 'users.php', 'pages' => 'edit.php?post_type=page', 'posts' => 'edit.php');
	
	// Enqueues scripts and styles
	if(softaculous_pro_can_enqueue_assets()){
		add_action('admin_init', 'softaculous_pro_admin');
		add_action('admin_enqueue_scripts', 'softaculous_pro_enqueue_scripts');
	}
	
	if(is_admin() && !empty($_GET['page']) && $_GET['page'] == 'assistant'){
		add_filter('screen_options_show_screen', '__return_false');
	}
	
	add_action('admin_notices', 'softaculous_pro_license_notice');
	add_filter('softaculous_expired_licenses', 'softaculous_pro_plugins_expired');
	
	// Are you the Admin ?
	if(current_user_can('administrator')){
	
		if(softaculous_pro_is_display_notice()){
			add_action('admin_notices', 'softaculous_pro_admin_notice');
		}
		
		add_action('wp_ajax_softaculous_pro_dismissnotice', 'softaculous_pro_dismiss_notice');
		
		// This adds the left menu in WordPress Admin page
		add_action('admin_menu', 'softaculous_pro_admin_menu', 5);
	
		include_once SOFTACULOUS_PRO_PLUGIN_PATH . '/main/admin.php';
		add_action('admin_print_footer_scripts', 'softaculous_pro_assistant', 5);
		
		add_action('wp_ajax_softaculous_pro_wp_ajax', 'softaculous_pro_wp_ajax');
		add_action('wp_ajax_softaculous_pro_dismiss_expired_licenses', 'softaculous_pro_dismiss_expiry_notice');
		
		add_action('admin_menu', 'softaculous_pro_remove_admin_elements');

		// Template Installation related ajax calls
		add_action('wp_ajax_softaculous_pro_template_info', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_start_install_template', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_selected_plugin_install', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_download_template', 'softaculous_pro_templates_ajax');
		add_action('wp_ajax_softaculous_pro_import_template', 'softaculous_pro_templates_ajax');
		
		// Setup information
		add_action('wp_ajax_softaculous_pro_setup_info', 'softaculous_pro_templates_ajax');
		
		// dismiss
		add_action('wp_ajax_softaculous_pro_onboarding_dismiss', 'softaculous_pro_templates_ajax');
		
		add_action('wp_ajax_softaculous_pro_option_value', 'softaculous_pro_templates_ajax');
		
	}
	
	// Manage Media hooks
	if(is_admin() && empty($softaculous_pro_settings['disable_manage_media']) && current_user_can('upload_files') /* && !empty($post) && !empty($post->post_type) && $post->post_type == 'attachment' */){
		
		add_action('add_meta_boxes_attachment', '\SoftWP\Media::register_meta_box');
		add_filter('media_row_actions', '\SoftWP\Media::add_media_row_action', 11, 2);
		
		// ajax calls
		add_action('wp_ajax_softaculous_pro_upload_photopea_image', '\SoftWP\Media::upload_photopea_image');
		add_action('wp_ajax_softaculous_pro_calculate_compressed_size', '\SoftWP\Media::calculate_compressed_size');
		add_action('wp_ajax_softaculous_pro_replace_compressed_image', '\SoftWP\Media::replace_compressed_image');
	}
}

function softaculous_pro_templates_ajax() {
	
	if(!current_user_can('administrator')){
		return false;
	}

	include_once(dirname(__FILE__).'/onboarding.php');
	
	if($_REQUEST['action'] == 'softaculous_pro_template_info'){
		softaculous_pro_ajax_template_info();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_selected_plugin_install'){
		softaculous_pro_ajax_selected_plugin();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_start_install_template'){
		softaculous_pro_ajax_start_install_template();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_download_template'){
		softaculous_pro_ajax_download_template();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_import_template'){
		softaculous_pro_ajax_import_template();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_setup_info'){
		softaculous_pro_save_setup_info();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_option_value'){
		softaculous_pro_get_options();
	}
	
	if($_REQUEST['action'] == 'softaculous_pro_onboarding_dismiss'){
		softaculous_pro_onboarding_dismiss();
	}

}

function softaculous_pro_remove_admin_elements(){
	
	if(!empty($_GET['page']) && $_GET['page'] === 'assistant'){
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
	}
}

// Update check
function softaculous_pro_update_check(){
	global $softaculous_pro_settings;

	$current_version = get_option('softaculous_pro_version', 0);
	$version = (int) str_replace('.', '', $current_version);

	if($current_version == SOFTACULOUS_PRO_VERSION){
		return true;
	}
	
	// AI is enabled by default so the cron to delete AI history should be added at activation.
	wp_schedule_event(time(), 'daily', 'softaculous_pro_ai_history_cron');
	
	if(empty($softaculous_pro_settings) || !isset($softaculous_pro_settings['ai_history_duration'])){
		$softaculous_pro_settings['ai_history_duration'] = 90; // Setting default AI history duration.
		update_option('softaculous_pro_settings', $softaculous_pro_settings);
	}

	// Save the new Version
	update_option('softaculous_pro_version', SOFTACULOUS_PRO_VERSION);
}

function softaculous_pro_admin(){
	
	global $spro_tours;
	
	include_once SOFTACULOUS_PRO_PLUGIN_PATH . '/main/admin.php';
	
}

// Shows the admin menu of Softaculous
function softaculous_pro_admin_menu() {
	
	$capability = 'activate_plugins';// TODO : Capability for accessing this page

	// Add the menu page
	add_menu_page(__('Assistant'), __('Assistant', 'softaculous-pro'), $capability, 'assistant', 'softaculous_pro_page_handler', 'dashicons-businessperson', 1);
	
}

// The Softaculous Settings Page
function softaculous_pro_page_handler(){
	
	global $softaculous_pro;

	if(!current_user_can('manage_options')){
		wp_die(__('Sorry, but you do not have permissions to change settings.', 'softaculous-pro'));
	}
	
	$act = softaculous_pro_optGET('act');
	
	switch($act){
		
		case 'onboarding':
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/setup.php');
		break;
		
		case 'license':
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/license.php');
		softaculous_pro_license();
		break;
		
		case 'manage-plugins':
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH . '/main/manage-plugins.php');
		softaculous_pro_manage_plugins();
		break;
		
		case 'media-replace':
		\SoftWP\Media::replace_media_page();
		break;
		
		default:
		include_once(dirname(__FILE__).'/admin.php');
		softaculous_pro_page_settings();
	}

}

function softaculous_pro_can_enqueue_assets(){
	
	global $spro_tours;
	
	if(!is_admin()){
		return false;
	}
	
	$current_page = basename($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	
	if(!empty($_COOKIE['spro-load-tour']) && (!empty($spro_tours)) && array_key_exists($_COOKIE['spro-load-tour'], $spro_tours) && $current_page == $spro_tours[$_COOKIE['spro-load-tour']]){
		return $_COOKIE['spro-load-tour'];
	}
	
	$is_admin_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	if(!empty($_GET['page']) && $_GET['page'] == 'assistant' && $is_admin_page == 'admin.php'){
		return true;
	}
	
	return false;
	
}

function softaculous_pro_enqueue_scripts(){
	
	wp_enqueue_style('softaculous-pro-style-admin', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/css/admin.css', [], SOFTACULOUS_PRO_VERSION);

	wp_enqueue_style('softaculous-pro-style-intro', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/css/intro.css', [], SOFTACULOUS_PRO_VERSION);
	
	wp_enqueue_style( 'softaculous-pro-style-font-awesome', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/font-awesome/css/all.min.css', [], SOFTACULOUS_PRO_VERSION, 'all' );

	wp_enqueue_script('softaculous-pro-script-admin', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/js/admin.js', [], SOFTACULOUS_PRO_VERSION, true);

	wp_enqueue_script('softaculous-pro-script-intro', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/js/intro.js', [], SOFTACULOUS_PRO_VERSION, false);
	
	wp_localize_script('softaculous-pro-script-admin', 'soft_pro_obj', array(
		'admin_url' => esc_url(admin_url()),
		'site_url' => esc_url(site_url()),
		'nonce' => wp_create_nonce('softaculous_pro_js_nonce'),
		'ajax_url' => admin_url('admin-ajax.php')
	));
}

/**
 * Display Softaculous notice on the basis of last dismiss date. When user manually dismisses the notice, it remains for 1 month
 *
 * @since		1.0
 */
function softaculous_pro_is_display_notice(){
	
	$soft_dismissable_notice_date = get_option('softaculous_pro_dismiss_notice_date');
	
	if(empty($soft_dismissable_notice_date)){
		return true;
	}
	
	$soft_dismissable_notice_date2 = new DateTime($soft_dismissable_notice_date);
	$current_date = new DateTime(date('Y-m-d'));
	$date_diff_month = $soft_dismissable_notice_date2->diff($current_date);

	//Do not display notice again for a month
	if($date_diff_month->m < 1){
		return false;
	}
	
	return true;
}

/**
 * Display Softaculous notice in dashboard
 *
 * @since		1.0
 */
function softaculous_pro_admin_notice($force = 0){
	
	if(!empty($_GET['page']) && $_GET['page'] == 'softaculous' && empty($force)){
		return '';
	}
	
	return '';
}

function softaculous_pro_license_notice(){
	
	global $softaculous_pro;
	
	if(is_admin() && current_user_can('activate_plugins') && !wp_doing_ajax()){
		
		$spro_add_nonce_vars = 0;
		
		if(empty($softaculous_pro['license']['license'])){
			$msg = sprintf(__('Your SoftWP plugin is %1$sUnlicensed%2$s. Please enter the license key %3$s here%4$s.', 'softaculous-pro'), 
						'<font style="color:red;"><b>',
						'</b></font>',
						'<a href="'.admin_url('admin.php?page=assistant&act=license').'">',
						'</a>'
						);
		}else{
			
			if(!empty($softaculous_pro['license']['url_mismatch'])){
				$msg = sprintf(__('Your SoftWP plugin license is %1$snot authorized%2$s to be used on  %3$s. You can generate a new license for your domain from the %4$s panel.', 'softaculous-pro'), 
							'<b><font style="color:red;">',
							'</font></b>',
							'<b><i>'.site_url().'</i></b>',
							'<b>'.($softaculous_pro['branding']['sn'] == 'SoftWP' ? 'Softaculous' : $softaculous_pro['branding']['sn']).'</b>'
							);
			}elseif(empty($softaculous_pro['license']['active'])){
				
				// The combined notice for all Softaculous plugin to show that the license has expired
				$expired_plugins = apply_filters('softaculous_expired_licenses', []);
				$dismissed_at = get_option('softaculous_expired_licenses', 0);
				if(
					!empty($expired_plugins) && 
					is_array($expired_plugins) && 
					!defined('SOFTACULOUS_EXPIRY_LICENSES') &&
					(empty($dismissed_at) || ($dismissed_at + WEEK_IN_SECONDS) < time())
				){

					define('SOFTACULOUS_EXPIRY_LICENSES', true); // To make sure other plugins don't return a Notice
					
					if(!empty($softaculous_pro['license']['has_plid'])){
						
						if(!empty($softaculous_pro['branding']['rebranded'])){
							
							$msg = sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please contact %3$s to continue receiving uninterrupted updates and support for %4$s.', 'softaculous-pro'),
								'<font style="color:red;"><b>',
								'</b></font>',
								esc_html($softaculous_pro['branding']['sn']),
								esc_html(implode(', ', $expired_plugins))
							);
							
						}else{
							
							$msg = sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please contact your hosting provider to continue receiving uninterrupted updates and support for %3$s.', 'softaculous-pro'),
								'<font style="color:red;"><b>',
								'</b></font>',
								esc_html(implode(', ', $expired_plugins))
							);
							
						}
						
					}else{
						
						$msg = sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please %3$srenew%4$s it to continue receiving uninterrupted updates and support for %5$s.', 'softaculous-pro'),
							'<font style="color:red;"><b>',
							'</b></font>',
							'<a href="'.esc_url(SOFTACULOUS_PRO_BUY.'&license='.$softaculous_pro['license']['license'].'&plan='.$softaculous_pro['license']['plan']).'" target="_blank">',
							'</a>',
							esc_html(implode(', ', $expired_plugins))
						);
					}

					wp_register_script('softaculous-pro-expiry-notice', '', array('jquery'), SOFTACULOUS_PRO_VERSION, true );
					wp_enqueue_script('softaculous-pro-expiry-notice');
					wp_add_inline_script('softaculous-pro-expiry-notice', '
					jQuery(document).ready(function(){
						jQuery("#softaculous-pro-notice").on("click", ".notice-dismiss", function(e){
							e.preventDefault();
							let target = jQuery(e.target);

							let jEle = target.closest("#softaculous-pro-notice");
							jEle.slideUp();
							
							jQuery.post("'.admin_url('admin-ajax.php').'", {
								security : "'.wp_create_nonce('softaculous_pro_expiry_notice').'",
								action: "softaculous_pro_dismiss_expired_licenses",
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
		}
		
		if(!empty($msg)){
			echo '
			<div class="notice notice-error is-dismissible" id="softaculous-pro-notice">
				<p>'.$msg.'</p>
			</div>';
		}
		
		// The notice to complete onboarding
		//$spro_is_shown = get_option('softaculous_pro_onboarding_shown');
		$spro_is_done = get_option('softaculous_pro_onboarding_done');
		$spro_is_dismissed = get_option('softaculous_pro_onboarding_notice_dismiss');
		
		if(empty($spro_is_done) && empty($spro_is_dismissed)){
			echo '
			<div class="notice notice-error is-dismissible" id="softaculous-onboarding-notice">
				<p>'.sprintf(__('Your Onboarding process is not completed yet! %1$sClick here%2$s to complete the simple setup process and build your site in a few steps.', 'softaculous-pro'),
							'<a href="'.admin_url('admin.php?page=assistant&act=onboarding').'" target="_blank">',
							'</a>').'</p>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$("#softaculous-onboarding-notice").on("click", ".notice-dismiss", function() {
						// Send an AJAX request to the server to dismiss the notice
						jQuery.ajax({
							type: "post",
							url: softwp_obj.ajax_url,
							data: {
								action: "softaculous_pro_wp_ajax",
								softaculous_pro_onboarding_notice_dismiss: 1,
								softaculous_pro_security: softwp_obj.nonce,
								data: [],
							},
						});
					});
				});
			</script>
			';
			
			$spro_add_nonce_vars = 1;
		}
		
		$softwp_ai_is_dismissed = get_option('softaculous_pro_ai_notice_dismiss');
		
		if(empty($softwp_ai_is_dismissed) && !empty($_GET['page']) && $_GET['page'] == 'assistant'){
			echo '
			<div class="notice notice-info is-dismissible" id="softaculous-ai-notice">
				<h3 style="margin: 0.5em 0;">'.__('Enhance Content Writing with AI Assistant', 'softaculous-pro').'
				</h3>
				<p>'.__('Use cutting-edge AI to write blog posts or content for your pages. Create a table, write a paragraph, change tone, translate, fix spelling & grammar and so much more.', 'softaculous-pro').' <br />
				<b>'.__('Start Exploring', 'softaculous-pro').'</b> : 
				<a href="'.admin_url('post-new.php?post_type=page').'" target="_blank" style="text-decoration:none;">'.__('New Page', 'softaculous-pro').'</a>&nbsp; &#9679; &nbsp;
				<a href="'.admin_url('post-new.php').'" target="_blank" style="text-decoration:none;">'.__('New Post', 'softaculous-pro').'</a>&nbsp; &#9679; &nbsp;
				<a href="'.admin_url('edit.php?post_type=page').'" target="_blank" style="text-decoration:none;">'.__('Existing Page', 'softaculous-pro').'</a>&nbsp; &#9679; &nbsp;
				<a href="'.admin_url('edit.php').'" target="_blank" style="text-decoration:none;">'.__('Existing Post', 'softaculous-pro').'</a> 
				</p>
			</div>
			
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$("#softaculous-ai-notice").on("click", ".notice-dismiss", function() {
						// Send an AJAX request to the server to dismiss the notice
						jQuery.ajax({
							type: "post",
							url: softwp_obj.ajax_url,
							data: {
								action: "softaculous_pro_wp_ajax",
								softaculous_pro_ai_notice_dismiss: 1,
								softaculous_pro_security: softwp_obj.nonce,
								data: [],
							},
						});
					});
				});
			</script>
			';
			
			$spro_add_nonce_vars = 1;
		}
		
		
		$softwp_ai_token_is_dismissed = get_option('softaculous_pro_ai_token_notice_dismiss');
		if(empty($softwp_ai_token_is_dismissed) && !empty($_GET['page']) && $_GET['page'] == 'assistant'){
			echo '<div class="notice notice-info is-dismissible" id="softaculous-ai-token-notice">
				<p><b>'.esc_html__('Free AI Tokens', 'softaculous-pro').' : </b>'.esc_html__('You now get 3,000 AI tokens per month for free!', 'softaculous-pro').'</p>
			</div>
			
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$("#softaculous-ai-token-notice").on("click", ".notice-dismiss", function() {
						// Send an AJAX request to the server to dismiss the notice
						jQuery.ajax({
							type: "post",
							url: softwp_obj.ajax_url,
							data: {
								action: "softaculous_pro_wp_ajax",
								softaculous_pro_ai_token_notice_dismiss: 1,
								softaculous_pro_security: softwp_obj.nonce,
								data: [],
							},
						});
					});
				});
			</script>';
			
			$spro_add_nonce_vars = 1;
		}

		if(!empty($spro_add_nonce_vars)){
			
			echo '<script type="text/javascript">
			var softwp_obj = {
			  admin_url: "'.esc_url(admin_url()).'",
			  site_url: "'.esc_url(site_url()).'",
			  nonce: "'.wp_create_nonce('softaculous_pro_js_nonce').'",
			  ajax_url: "'.admin_url('admin-ajax.php').'"
			};
			</script>';
			
		}
		
	}
	
	return '';
}

function softaculous_pro_get_option($option_name, $default_value = false, $site_id = null){
	
    if($site_id !== null && is_multisite()){
        return get_site_option($option_name, $default_value);
    }
    return get_option($option_name, $default_value);
}

/**
 * Takes care of Slashes
 *
 * @param		string $string The string that will be processed
 * @return		string A string that is safe to use for Database Queries, etc
 * @since		1.0
 */
function softaculous_pro_inputsec($string){

	//get_magic_quotes_gpc is deprecated in php 7.4
	if(version_compare(PHP_VERSION, '7.4', '<')){
		if(!get_magic_quotes_gpc()){
		
			$string = addslashes($string);
		
		}else{
		
			$string = stripslashes($string);
			$string = addslashes($string);
		
		}
	}else{
		$string = addslashes($string);
	}
	
	// This is to replace ` which can cause the command to be executed in exec()
	$string = str_replace('`', '\`', $string);
	
	return $string;

}

/**
 * Converts Special characters to html entities
 *
 * @param        string $string The string containing special characters
 * @return       string A string containing special characters replaced by html entities of the format &#ASCIICODE;
 * @since     	 1.0
 */
function softaculous_pro_htmlizer($string){

	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	
	preg_match_all('/(&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});)/', $string, $matches);
	
	foreach($matches[1] as $mk => $mv){		
		$tmp_m = softaculous_pro_entity_check($matches[2][$mk]);
		$string = str_replace($matches[1][$mk], $tmp_m, $string);
	}
	
	return $string;
	
}

/**
 * Used in function htmlizer()
 *
 * @param        string $string
 * @return       string
 * @since     	 1.0
 */
function softaculous_pro_entity_check($string){
	
	//Convert Hexadecimal to Decimal
	$num = ((substr($string, 0, 1) === 'x') ? hexdec(substr($string, 1)) : (int) $string);
	
	//Squares and Spaces - return nothing 
	$string = (($num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num < 0x20) ? '' : '&#'.$num.';');
	
	return $string;
			
}

/**
 * OPTIONAL REQUEST of the given REQUEST Key
 *
 * @param        string $name The key of the $_REQUEST array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_REQUEST[$name] is NOT SET
 * @return       string Returns the string if the REQUEST is there otherwise the default value given.
 * @since     	 1.0
 */
function softaculous_pro_optREQ($name, $default = ''){

global $softaculous_error;

	//Check the POSTED NAME was posted
	if(isset($_REQUEST[$name])){
	
		return trim(sanitize_text_field($_REQUEST[$name]));
		
	}else{
		return $default;
	}
}

/**
 * OPTIONAL POST of the given POST Key
 *
 * @param        string $name The key of the $_POST array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_POST[$name] is NOT SET
 * @return       string Returns the string if the POST is there otherwise the default value given.
 * @since		1.4.6
 */
function softaculous_pro_optPOST($name, $default = ''){

global $softaculous_error;

	//Check the POSTED NAME was posted
	if(isset($_POST[$name])){
		
		if(is_array($_POST[$name])){
			$values = array_map('trim', $_POST[$name]);
			return array_map('sanitize_text_field', $values);
		}
	
		return trim(sanitize_text_field($_POST[$name]));
		
	}else{
		return $default;
	}

}

/**
 * OPTIONAL GET of the given GET Key i.e. dont throw a error if not there
 *
 * @param        string $name The key of the $_GET array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_GET[$name] is NOT SET
 * @return       string Returns the string if the GET is there otherwise the default value given.
 * @since     	 1.0
 */
function softaculous_pro_optGET($name, $default = ''){

global $softaculous_error;

	//Check the GETED NAME was GETed
	if(isset($_GET[$name])){
	
		return trim(sanitize_text_field($_GET[$name]));
		
	}else{
		return $default;
	}

}
	
function softaculous_pro_sp_api_url($main_server = 0){
	
	global $softaculous_pro;
	
	return softaculous_pro_api_url($main_server, 'sitepad');
	
}
	
function softaculous_pro_pfx_api_url($main_server = 0){
	
	global $softaculous_pro;
	
	return softaculous_pro_api_url($main_server, 'popularfx');
	
}
	
function softaculous_pro_api_url($main_server = 0, $suffix = ''){
	
	global $softaculous_pro;
	
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
	if(!empty($main_server) || empty($softaculous_pro['license']['last_edit']) || 
		(!empty($softaculous_pro['license']['last_edit']) && (time() - 3600) < $softaculous_pro['license']['last_edit'])
	){
		$mirror = 'https://a.softaculous.com/softwp/';
	}
	
	// -1 indicates that we need to force the mirror server used for rendering static files e.g. screenshots
	if(!empty($main_server) && $main_server == '-1'){
		$mirror = $r[array_rand($r)];
	}
	
	if(!empty($suffix)){
		$mirror = str_replace('/softwp', '/'.$suffix, $mirror);
	}
	
	return $mirror;
	
}

// Load license data
function softaculous_pro_load_license(){
	
	global $softaculous_pro;

	// Load license
	$softaculous_pro['license'] = get_option('softaculous_pro_license', array());
	
	if(empty($softaculous_pro['license'])){
		return false;
	}
	
	$prods = apply_filters('softaculous_pro_products', []);
	
	// Update license details as well
	if(empty($softaculous_pro['license']['last_update']) || 
		(!empty($softaculous_pro['license']['last_update']) && (time() - $softaculous_pro['license']['last_update']) >= 86400)
	){
		
		$resp = wp_remote_get(softaculous_pro_api_url(1).'license.php?license='.$softaculous_pro['license']['license'].'&prods='.implode(',', $prods).'&url='.rawurlencode(site_url()));
		
		// Did we get a response ?
		if(is_array($resp)){
			
			$tosave = json_decode($resp['body'], true);
			
			// Is it the license ?
			if(!empty($tosave['license'])){
				$softaculous_pro['license'] = $tosave;
			}
			
		}
		
		// Save the old data only
		if(empty($tosave['license'])){
			$tosave = get_option('softaculous_pro_license', array());
		}
		
		$tosave['last_update'] = time();
		update_option('softaculous_pro_license', $tosave);
		
	}
	
	return $softaculous_pro['license'];
}

add_filter('softaculous_pro_products', 'softaculous_softwp_pro_products', 10, 1);
function softaculous_softwp_pro_products($r = []){
	$r['softwp'] = 'softwp';
	return $r;
}

// Load Softaculous rebranding settings
function softaculous_pro_rebranding(){
	
	global $softaculous_pro;
	
	$softaculous_pro['branding']['sn'] = 'SoftWP';
	$softaculous_pro['branding']['logo_url'] = SOFTACULOUS_PRO_PLUGIN_URL.'assets/images/logo-white.png';
	$softaculous_pro['branding']['rebranded'] = 0;

	//Getting info if Softaculous rebranding done or not?
	$soft_rebranding = get_option('softaculous_pro_rebranding', '[]');
	
	if(!empty($soft_rebranding['logo_url'])){
		$softaculous_pro['branding']['logo_url'] = $soft_rebranding['logo_url'];
	}
	
	if(!empty($soft_rebranding['sn']) && $soft_rebranding['sn'] != 'Softaculous'){
		$softaculous_pro['branding']['sn'] = $soft_rebranding['sn'];
		$softaculous_pro['branding']['rebranded'] = 1;
	}
	
	if(!empty($soft_rebranding['default_hf_bg'])){
		$softaculous_pro['branding']['default_hf_bg'] = $soft_rebranding['default_hf_bg'];
	}
	
	if(!empty($soft_rebranding['default_hf_text'])){
		$softaculous_pro['branding']['default_hf_text'] = $soft_rebranding['default_hf_text'];
	}
	
	return true;
}

// Add our license key if ANY
function softaculous_pro_updater_filter_args($queryArgs) {
	
	global $softaculous_pro;
	
	if ( !empty($softaculous_pro['license']['license']) ) {
		$queryArgs['license'] = $softaculous_pro['license']['license'];
	}
	
	return $queryArgs;
}

// Handle the Check for update link and ask to install license key
function softaculous_pro_updater_check_link($final_link){
	
	global $softaculous_pro;
	
	if(empty($softaculous_pro['license']['license'])){
		return '<a href="'.admin_url('admin.php?page=assistant&act=license').'">'.__('Enter Pro License Key', 'softaculous-pro').'</a>';
	}
	
	return $final_link;
}

function softaculous_pro_report_error($error = array()) {
	
	if(empty($error)){
		return true;
	}
	
	$error_string = '<b>' . esc_html__('Please fix the below error(s):', 'softaculous-pro') . '</b><br />';
	
	foreach($error as $ev){
		$error_string .= '* ' . esc_html($ev) . '<br />';
	}
	
	echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
}

function softaculous_pro_add_plugin_row_links($links, $slug) {

	if(is_multisite() && is_network_admin()){
		return $links;
	}

	if ($slug !== SOFTACULOUS_PRO_BASE) {
		return $links;
	}

	if(!current_user_can('activate_plugins')){
		return $links;
	}
	
	$new_links = array(
		'plugins' => '<a href="admin.php?page=assistant&act=manage-plugins"><font style="color:green;">'.__('Explore Pro Plugins', 'softaculous-pro').'</font></a>',
		'tours' => '<a href="admin.php?page=assistant#tours">'.__('Tour', 'softaculous-pro').'</a>',
		'ai' => '<a href="admin.php?page=assistant#ai"><font style="color:green;">'.__('AI', 'softaculous-pro').'</font></a>',
	);

	$links = array_merge($links, $new_links);

	return $links;
}

function softaculous_pro_add_params($link){
	
	global $softaculous_pro;
	
	$link = rtrim($link, '?&');

	$query = parse_url($link, PHP_URL_QUERY);

	if ($query) {
		$link .= '&';
	} else {
		$link .= '?';
	}

	$link .= 'version=latest&license='.$softaculous_pro['license']['license'].'&url='.site_url();
	
	return $link;
}

function softaculous_pro_register_post_type(){
	register_post_type(
		'spro_ai_history',
		[
			'labels' => [
				'name' => __('AI History', 'softaculous-pro'),
				'singular_name' => __('AI History', 'softaculous-pro'),
			],
			'public' => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'can_export' => false,
			'delete_with_user' => true,
			'supports' => array('author'),
		]
	);
}

function softaculous_pro_plugins_expired($plugins = []){
	global $softaculous_pro;
	
	if(!empty($softaculous_pro['license']) && empty($softaculous_pro['license']['active'])){
		$plugins[] = 'SoftWP';
	}

	return $plugins;
}
