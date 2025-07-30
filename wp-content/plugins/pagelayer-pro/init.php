<?php

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

// Ok so we are now ready to go
register_activation_hook(PAGELAYER_PRO_FILE, 'pagelayer_pro_activation');

// Prevent update of pagelayer free
// This also work for auto update
add_filter('site_transient_update_plugins', 'pagelayer_pro_disable_manual_update_for_plugin');
add_filter('pre_site_transient_update_plugins', 'pagelayer_pro_disable_manual_update_for_plugin');

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'pagelayer_pro_update_free_after_pro', 10, 2);

// Add the action to load the plugin
add_action('plugins_loaded', 'pagelayer_pro_load_plugin');

// The function that will be called when the plugin is loaded
function pagelayer_pro_load_plugin(){

	global $pagelayer;
	
	// Load license
	pagelayer_pro_load_license();
	
	// Check if the installed version is outdated
	pagelayer_pro_update_check();
	
	// Load the language
	load_plugin_textdomain('pagelayer-pro', false, PAGELAYER_PRO_SLUG.'/languages/');
	
	// Check for updates
	include_once(PAGELAYER_PRO_DIR.'/main/plugin-update-checker.php');
	$pagelayer_updater = Pagelayer_PucFactory::buildUpdateChecker(pagelayer_pro_api_url().'updates.php?version='.PAGELAYER_PRO_VERSION, PAGELAYER_PRO_FILE);
	
	// Add the license key to query arguments
	$pagelayer_updater->addQueryArgFilter('pagelayer_pro_updater_filter_args');
	
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-pagelayer-pro', 'pagelayer_pro_updater_check_link', 10, 1);
	
	// Load the template builder
	include_once(PAGELAYER_PRO_DIR.'/main/template-builder.php');
	
	$pagelayer->allowed_mime_type = array(
		'otf' => 'font/otf',
		'ttf' => 'font/ttf',
		'woff' => 'font/woff|application/font-woff|application/x-font-woff',
		'woff2' => 'font/woff2|font/x-woff2'
	);
	
	// Load the pagelayer custom fonts
	include_once(PAGELAYER_PRO_DIR.'/main/custom_fonts.php');
	
	// Are we to disable the notice
	if(current_user_can('activate_plugins')){
		if(isset($_GET['pagelayer-pro-version-notice']) && (int)$_GET['pagelayer-pro-version-notice'] == 0){
			check_ajax_referer('pagelayer_pro_version_nonce', 'pagelayer_nonce');
			
			if(!empty($_REQUEST['type'])){
				// Notice dismiss for 7 days
				update_option('pagelayer_pro_'.$_REQUEST['type'].'_version_nag', time() + (7 * 86400));
			}
			die('DONE');
		}
		
		// Show the version notice
		add_action('admin_notices', 'pagelayer_pro_free_version_nag');
	}
	
}

// Nag when plugins dont have same version.
function pagelayer_pro_free_version_nag(){
	if(!defined('PAGELAYER_VERSION')){
		return;
	}
	
	$sctipt_enqueue = false;
	$older_pro = get_option('pagelayer_pro_older_pro_version_nag');
	$older_free = get_option('pagelayer_pro_older_free_version_nag');
	
	if(version_compare(PAGELAYER_VERSION, PAGELAYER_PRO_VERSION) > 0 && (empty($older_pro) || $older_pro < time())){
		echo '<div class="pagelayer-pro-version-notice notice notice-warning is-dismissible" data-notice="older_pro" onclick="pagelayer_pro_notice_dismiss(event)">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of Pagelayer Pro. We recommend updating to the latest version to ensure seamless and uninterrupted use of the plugin.').'</p>
		</div>';
	
		$sctipt_enqueue = true;
	}elseif(version_compare(PAGELAYER_VERSION, PAGELAYER_PRO_VERSION) < 0 && (empty($older_free) || $older_free < time())){
		echo '<div class="pagelayer-pro-version-notice notice notice-warning is-dismissible" data-notice="older_free" onclick="pagelayer_pro_notice_dismiss(event)">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of Pagelayer. We recommend updating to the latest free version to ensure smooth and uninterrupted use of the plugin.') .'</p>
		</div>';
		
		$sctipt_enqueue = true;
	}
	
	if($sctipt_enqueue){
		echo '
	<script type="application/javascript">
		function pagelayer_pro_notice_dismiss(e){
			
			 e.preventDefault();
			 var target = jQuery(e.target);
			 
			if(!target.hasClass("notice-dismiss")){
				return;
			}
				
			var jEle = target.closest(".pagelayer-pro-version-notice");
			
			var data = {};
			data["type"] = jEle.data("notice");
			
			jEle.hide();
			
			// Save this preference
			jQuery.post("'.admin_url('?pagelayer-pro-version-notice=0&pagelayer_nonce='.wp_create_nonce("pagelayer_pro_version_nonce") ).'", data, function(response) {
			//alert(response);
			});
			return false;
		};
	</script>';
	}
}

// Add filter to load custom widgets functions
add_action('pagelayer_load_shortcode_functions', 'pagelayer_pro_load_shortcode_functions');
function pagelayer_pro_load_shortcode_functions(){
	include_once(PAGELAYER_PRO_DIR.'/main/freemium_functions.php');
	include_once(PAGELAYER_PRO_DIR.'/main/premium_functions.php');
}

// Apply filter to load custom widgets after shortcodes
add_action('pagelayer_after_add_shortcode', 'pagelayer_pro_after_add_shortcode');
function pagelayer_pro_after_add_shortcode(){
	include_once(PAGELAYER_PRO_DIR.'/main/freemium.php');
	include_once(PAGELAYER_PRO_DIR.'/main/premium.php');
}

// Load customizer setting
add_action('pagelayer_after_wc_customization', 'pagelayer_pro_after_wc_customization');
function pagelayer_pro_after_wc_customization(){
	include_once(PAGELAYER_PRO_DIR.'/main/premium-woocommerce.php');
}

// Load Local google fonts
add_action('pagelayer_google_fonts_url', 'pagelayer_pro_google_fonts_url');
function pagelayer_pro_google_fonts_url($fonts_url){
	
	// Is google font serve locally?
	if(get_option('pagelayer_local_gfont') != 1){
		return $fonts_url;
	}
	
	$upload_dir = wp_upload_dir();
	$local_font_md5 = md5($fonts_url);
	$_fonts_url = $upload_dir['baseurl'].'/pl-google-fonts/'.$local_font_md5.'.css';
	$_fonts_path = $upload_dir['basedir'].'/pl-google-fonts/'.$local_font_md5.'.css';
	
	if(!file_exists($_fonts_path) && file_exists(PAGELAYER_PRO_DIR.'/main/download_google_fonts.php')){
		include_once(PAGELAYER_PRO_DIR.'/main/download_google_fonts.php');
		pagelayer_pro_download_google_fonts($fonts_url);
	}
	
	return $_fonts_url;
}

// Load js files for editor
add_action('pagelayer_editor_give_js', 'pagelayer_pro_editor_give_js');
function pagelayer_pro_editor_give_js($js){
	$js.= '&premium=premium.js';
	return $js;
}

// Load js files
add_action('pagelayer_add_give_js', 'pagelayer_pro_add_give_js');
function pagelayer_pro_add_give_js($js){
	$js.= '&premium=chart.min.js,premium-frontend.js,shuffle.min.js';
	return $js;
}

// Load css files
add_action('pagelayer_add_give_css', 'pagelayer_pro_add_give_css');
function pagelayer_pro_add_give_css($css){
	$css.= '&premium=premium-frontend.css';
	return $css;
}

// Load this For audio widget
add_action('pagelayer_load_audio_widget', 'pagelayer_pro_load_audio_widget');
function pagelayer_pro_load_audio_widget($is_audio){
	global $pagelayer;
	
	if($is_audio || pagelayer_is_live_iframe()){
		wp_enqueue_script('wp-mediaelement');
		wp_enqueue_style( 'wp-mediaelement' );
		$pagelayer->sc_audio_enqueued = 1;
	}
}

// Load the langs
add_action('pagelayer_load_languages', 'pagelayer_pro_load_languages');
function pagelayer_pro_load_languages($langs){
	
	$_langs = @file_get_contents(PAGELAYER_PRO_DIR.'/languages/en.json');
	$_langs = @json_decode($_langs, true);
		
	if(!empty($_langs)){
		$langs = array_merge($langs, $_langs);
	}
	
	return $langs;
}
