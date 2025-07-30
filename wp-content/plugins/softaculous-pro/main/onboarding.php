<?php

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

add_action('admin_print_styles', 'softaculous_pro_admin_print_styles');
function softaculous_pro_admin_print_styles() {
	
	$act = softaculous_pro_optGET('act');
	$page = softaculous_pro_optGET('page');
	
	// Check if the current page is the onboarding wizard
	if($page == 'assistant' && $act == 'onboarding'){
		echo '<style>
			#adminmenuback, #adminmenuwrap, #wpadminbar  { display: none !important; }
		</style>';
	}
}

add_action('admin_menu', 'softaculous_pro_onboarding_admin_menu', 6);
function softaculous_pro_onboarding_admin_menu() {

	$capability = 'activate_plugins';
	
	// Onboarding
	//add_submenu_page('assistant', __('Onboarding', 'softaculous-pro'), __('Onboarding', 'softaculous-pro'), $capability, 'softaculous_pro_onboarding', 'softaculous_pro_page_handler');
	
	$act = softaculous_pro_optGET('act');
	$page = softaculous_pro_optGET('page');
	
	// Check if the current page is the onboarding wizard
	if($page == 'assistant' && $act == 'onboarding'){
		// Remove admin menu
		remove_menu_page('index.php'); // Dashboard
		remove_menu_page('edit.php'); // Posts
		remove_menu_page('upload.php'); // Media
		remove_menu_page('edit.php?post_type=page'); // Pages
		remove_menu_page('edit-comments.php'); // Comments
		remove_menu_page('themes.php'); // Appearance
		remove_menu_page('plugins.php'); // Plugins
		remove_menu_page('users.php'); // Users
		remove_menu_page('tools.php'); // Tools
		remove_menu_page('options-general.php'); // Settings
		add_filter('show_admin_bar', '__return_false');
		remove_filter('update_footer', 'core_update_footer');
		add_filter('screen_options_show_screen', '__return_false');
		add_filter('admin_footer_text', '__return_empty_string');
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices');
	}

}

// Add the action to load the plugin 
add_action('plugins_loaded', 'softaculous_pro_onboarding_load_plugin');

// The function that will be called when the plugin is loaded
function softaculous_pro_onboarding_load_plugin(){

	global $pagelayer, $softaculous_pro;
	
	add_action('admin_enqueue_scripts', 'softaculous_pro_onboarding_enqueue_scripts');

	/* // Load the freemium widgets
	if(!defined('PAGELAYER_PREMIUM')){
		add_action('pagelayer_load_custom_widgets', 'spro_freemium_shortcodes');
	} */
	
	// Are we to setup a template ?
	$slug = get_option('softaculous_pro_setup_template');
	if(!empty($slug)){
		add_action('after_setup_theme', 'spro_setup_template_import');
	}

}

function softaculous_pro_onboarding_enqueue_scripts(){	
			
	$softwp_onboarding_lang = array(
		'conf_data_loss' => __( 'Please confirm that you accept data loss since you have already run the onboarding process previously', 'softaculous-pro' ),
		'select_atleast_one' => __( 'Please select atleast one page to import', 'softaculous-pro' ),
		'downloading_installing_plugins' => __( "Downloading and installing required plugins", 'softaculous-pro' ),
		'wordpress_require_ftp' => __( "WordPress requires FTP details, without them you won't be able to install a plugin/theme", 'softaculous-pro' ),		
		'downloading_template' => __( "Downloading the template", 'softaculous-pro' ),
		'buildWebsite' => __( 'Building your website...', 'softaculous-pro' ),
		'checkRequirements' => __( 'Checking the requirements ...', 'softaculous-pro' ),
		'importTemplate' => __( 'Importing the template', 'softaculous-pro' ),
		'setupCompleted' => __( 'Your website setup is completed', 'softaculous-pro' ),
		'congratulations' => __( 'Congratulations', 'softaculous-pro'),
	);

	if(!empty($_GET['act']) && $_GET['act'] === 'onboarding') {
		
		wp_enqueue_script('softaculous-pro-script-onboarding', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/js/onboarding.js', array('jquery'), SOFTACULOUS_PRO_VERSION, false);
		wp_localize_script('softaculous-pro-script-onboarding', 'softwp_onboarding_lang', $softwp_onboarding_lang);
		
		wp_enqueue_style( 'softaculous-pro-style-onboarding', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/css/onboarding.css', [], SOFTACULOUS_PRO_VERSION, 'all' );
		
		wp_enqueue_style( 'softaculous-pro-style-font-awesome', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/font-awesome/css/all.min.css', [], SOFTACULOUS_PRO_VERSION, 'all' );
	}
}

function spro_get_features_list(){
	
    $features_list = array(
        
		"seo" => array(
		"name" => __('Increase Website Traffic (SEO)','softaculous-pro'),
		"info" => __("Improve your site's ranking on search engines","softaculous-pro"),
		"icon" => 'dashicons dashicons-chart-bar',
		"plugin"=> array(
			"siteseo" => array(
				"plugin_name" => __("SiteSEO – One Click SEO for WordPress","softaculous-pro"),
				"plugin_url"=> "https://siteseo.io/",
				'plugin_init' => 'siteseo/siteseo.php',
				'plugin_download_url' => softaculous_pro_api_url(0, 'siteseo').'files/versions/latest-stable-free.zip',
				'plugin_init_pro' => 'siteseo-pro/siteseo-pro.php',
				'plugin_download_url_pro' => softaculous_pro_api_url(0, 'siteseo').'download.php',
				'plugin_desc' => __('Boost your website\'s search rankings today with the most powerful WordPress SEO plugin. Its lightweight, optimized, and delivers exceptional performance.', 'softaculous-pro'),
				'pro' => 1,
				'featured' => 1,
				'requires_php' => 7.2,
                ),
            )
        ),
		
        "speedycache" => array(
            "name" => __("Improve Page Speed","softaculous-pro"),
            "info" => __("Improve speed by cache, minify, compress", 'softaculous-pro'),
            "icon" => "fa-solid fa-gauge-high",
            "plugin"=> array(
                "speedycache" => array(
                    "plugin_name" => __("SpeedyCache – Cache, Optimization, Performance","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/speedycache/",
					'plugin_init' => 'speedycache/speedycache.php',
					'plugin_init_pro' => 'speedycache-pro/speedycache-pro.php',
					'plugin_download_url_pro' => softaculous_pro_api_url(0, 'speedycache').'download.php',
					'plugin_desc' => __('SpeedyCache is an easy to use and powerful WordPress Cache Plugin, it helps you reduce page load time improving User Experience and boosting your Google PageSpeed.', 'softaculous-pro'),
					'pro' => 1,
					'featured' => 1,
					'requires_php' => 7.3,
                ),
            )
        ),
		
        "backuply" => array(
            "name" => __("Schedule Backups","softaculous-pro"),
            "info" => __("Backup your site on local or remote servers","softaculous-pro"),
            "icon" => "fa-regular fa-file-zipper",
            "plugin"=> array(
                "backuply" => array(
                    "plugin_name" => __("Backuply – Backup, Restore, Migrate and Clone","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/backuply/",
					'plugin_init' => 'backuply/backuply.php',
					'plugin_init_pro' => 'backuply-pro/backuply-pro.php',
					'plugin_download_url_pro' => softaculous_pro_api_url(0, 'backuply').'download.php',
					'plugin_desc' => __('Backuply is a WordPress backup plugin that helps you backup your WordPress website, saving you from loss of data because of server crashes, hacks, dodgy updates, or bad plugins.', 'softaculous-pro'),
					'pro' => 1,
					'featured' => 1,
					'requires_php' => 5.5,
                ),
            )
        ),
        
		"sell_products" => array(
            "name" => __("Sell Products","softaculous-pro"),
            "info" => __("Sell physical or digital products","softaculous-pro"),
            "icon" => "fa-solid fa-tag",
            "plugin"=> array(
                "woocommerce" => array(
                    "plugin_name" => __("WooCommerce","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/woocommerce/",
					'plugin_init' => 'woocommerce/woocommerce.php',
                ),
            )
        ),
		
        "loginizer" => array(
            "name" => __("Limit Login Attempts","softaculous-pro"),
            "info" => __("Brute force protection, 2FA, login captcha", 'softaculous-pro'),
            "icon" => "fa-solid fa-user-lock",
            "plugin"=> array(
                "loginizer" => array(
                    "plugin_name" => __("Loginizer","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/loginizer/",
					'plugin_init' => 'loginizer/loginizer.php',
					'plugin_init_pro' => 'loginizer-security/loginizer-security.php',
					'plugin_download_url_pro' => softaculous_pro_api_url(0, 'loginizer').'download.php',
					'plugin_desc' => __('Loginizer is a simple and effortless solution that takes care of all your security problems. It comes with default optimal configuration to protect your site from Brute Force attacks.', 'softaculous-pro'),
					'pro' => 1,
					'featured' => 1,
					'requires_php' => 5.5,
                ),
            )
        ),
		
        "pagelayer" => array(
            "name" => __("Page Builder","softaculous-pro"),
            "info" => __("Page Builder, Drag and Drop website builder", 'softaculous-pro'),
            "icon" => "fa-solid fa-paintbrush",
            "plugin"=> array(
                "pagelayer" => array(
                    "plugin_name" => __("Pagelayer","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/pagelayer/",
					'plugin_init' => 'pagelayer/pagelayer.php',
					'plugin_init_pro' => 'pagelayer-pro/pagelayer-pro.php',
					'plugin_download_url_pro' => softaculous_pro_api_url(0, 'pagelayer').'download.php',
					'plugin_desc' => __('Pagelayer is an awesome page builder that allows you to create and design your website instantly in the simplest way possible. Take control over your page content with the most advanced page builder plugin available.', 'softaculous-pro'),
					'pro' => 1,
					'featured' => 1,
					'requires_php' => 5.5,
                ),
            )
        ),
		
        "gosmtp" => array(
            "name" => __("Send Email with SMTP","softaculous-pro"),
            "info" => __("Providers: Gmail, Outlook, AWS SES & more", 'softaculous-pro'),
            "icon" => "fa-solid fa-envelope-circle-check",
            "plugin"=> array(
                "gosmtp" => array(
                    "plugin_name" => __("GoSMTP – SMTP for WordPress","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/gosmtp/",
					'plugin_init' => 'gosmtp/gosmtp.php',
					'plugin_init_pro' => 'gosmtp-pro/gosmtp-pro.php',
					'plugin_download_url_pro' => softaculous_pro_api_url(0, 'gosmtp').'download.php',
					'plugin_desc' => __('GoSMTP allows you to send emails from your WordPress over SMTP or many popular outgoing email service providers. Using these improves your email deliverability.', 'softaculous-pro'),
					'pro' => 1,
					'featured' => 1,
					'requires_php' => 5.5,
                ),
            )
        ),
		
        "fileorganizer" => array(
            "name" => __("File Manager","softaculous-pro"),
            "info" => __("Manage files with drag & drop editor", 'softaculous-pro'),
            "icon" => "fa-regular fa-folder-open",
            "plugin"=> array(
                "fileorganizer" => array(
                    "plugin_name" => __("FileOrganizer – Manage WordPress and Website Files","softaculous-pro"),
                    "plugin_url"=> "https://wordpress.org/plugins/fileorganizer/",
					'plugin_init' => 'fileorganizer/fileorganizer.php',
					'plugin_init_pro' => 'fileorganizer-pro/fileorganizer-pro.php',
					'plugin_download_url_pro' => softaculous_pro_api_url(0, 'fileorganizer').'download.php',
					'plugin_desc' => __('FileOrganizer is a lightweight and easy-to-use file management plugin for WordPress. Organize and manage your WordPress files with FileOrganizer without any control panel or FTP access. ', 'softaculous-pro'),
					'pro' => 1,
					'featured' => 1,
					'requires_php' => 5.5,
                ),
            )
        ),
    );
	
	$features_list = apply_filters('softaculous_pro_features_list', $features_list);
	
	return $features_list;
}


function softaculous_pro_ajax_output($data){
	
	echo json_encode($data);
	
	wp_die();
	
}

function softaculous_pro_ajax_output_xmlwrap($data){
	
	echo '<softaculous-pro-xmlwrap>'.json_encode($data).'</softaculous-pro-xmlwrap>';
	
	wp_die();
}

function softaculous_pro_import_template($slug, $items = array()){
	global $pl_error;

	$data = [];
	
	$destination = popularfx_templates_dir(false).'/'.$slug;
	
	include_once(PAGELAYER_DIR.'/main/import.php');
	
	// Our function needs to efficiently replace the variables
	$GLOBALS['softaculous_pro_template_import_slug'] = $slug;	
	add_filter('pagelayer_start_insert_content', 'softaculous_pro_pagelayer_start_insert_content', 10);
	
	// Now import the template
	if(!pagelayer_import_theme($slug, $destination, $items)){
		$data['error']['import_err'] = __('Could not import the template !', 'softaculous-pro');
		$data['error'] = array_merge($data['error'], $pl_error);
		return $data;
	}
	
	// Save the name of the slug
	set_theme_mod('popularfx_template', $slug);
	
	// onboarding done
	update_option('softaculous_pro_onboarding_done', time());
	
	// Set default left menu folded
	//set_user_setting('mfold', 'f');
	
	$data['done'] = 1;
	
	return $data;
	
}

// Download the template
function softaculous_pro_download_template($slug){
	
	global $softaculous_pro, $pl_error;	

	set_time_limit(300);
	
	$data = [];

	// Now lets download the templates
	if(!function_exists( 'download_url' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	
	$spro_setup_info = get_option('softaculous_pro_setup_info');
	
	$url = softaculous_pro_pfx_api_url().'/givetemplate.php?slug='.$spro_setup_info['theme_slug'].'&license='.@$softaculous_pro['license']['license'].'&url='.site_url();
	//echo $url;
	
	$popular_fx_dir = popularfx_templates_dir(false);
	$destination = $popular_fx_dir.'/'.$slug;

	// Check if FTP is required
	ob_start();
	$credentials = request_filesystem_credentials('');
	ob_end_clean();

	if(false === $credentials || !WP_Filesystem($credentials)){
		$data['error']['download'] = __('Theme template can only be uploaded using FTP !', 'softaculous-pro');
		return $data;
	}
	
	global $wp_filesystem;
	
	// For FTP have to use relative paths
	if(is_array($credentials)){
		$abspath_relative = $wp_filesystem->find_folder(ABSPATH);
		$replacer = str_replace($abspath_relative, '', ABSPATH);
		if($replacer !== ABSPATH){
			$popular_fx_dir = str_replace($replacer, '', $popular_fx_dir);
			$destination = str_replace($replacer, '', $destination);
		}
	}

	$tmp_file = download_url($url);
	//echo filesize($tmp_file);
	//var_dump($tmp_file);
	
	// Error downloading
	if(is_wp_error($tmp_file) || filesize($tmp_file) < 1){
		if(!empty($tmp_file->errors)){			
			$data['error']['download_err'] = __('Could not download the theme !', 'softaculous-pro').var_export($tmp_file->errors, true);
			return $data;
		}
	}
	
	$wp_filesystem->mkdir($popular_fx_dir);
	$wp_filesystem->mkdir($destination);
	//echo $destination;

	$ret = unzip_file($tmp_file, $destination);
	//r_print($ret);
	
	// Try to delete
	@unlink($tmp_file);
	
	// Error downloading
	if(is_wp_error($ret) || !file_exists($destination.'/style.css')){
		if(!empty($ret->errors)){
			$data['error']['download'] = __('Could not extract the template !', 'softaculous-pro').var_export($ret->errors, true);
			return $data;
		}
	}

	return $data;

}

// Get list of templates
function softaculous_pro_get_templates_list(){
	
	$data = get_transient('softaculous_pro_templates');

	// Get any existing copy of our transient data
	if(false === $data){
	
		// Start checking for an update
		$send_for_check = array(
			'timeout' => 90,
			'user-agent' => 'WordPress'		
		);
		
		$raw_response = wp_remote_post( softaculous_pro_pfx_api_url().'templates.json', $send_for_check );
		//pagelayer_print($raw_response);die();
	
		// Is the response valid ?
		if ( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ){		
			$data = json_decode($raw_response['body'], true);
		}
		//pagelayer_print($data);die();
	
		// Feed the updated data into the transient
		if(!empty($data['list']) && count($data['list']) > 10){
			set_transient('softaculous_pro_templates', $data, 2 * HOUR_IN_SECONDS);
		}
		
	}
	
	return $data;
	
}

// Get the template info from our servers
function softaculous_pro_onboarding_dismiss(){

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	if(isset($_REQUEST['dismiss'])){
		update_option('softaculous_pro_onboarding_dismiss', time());
	}
	
	$data['done'] = 1;
	
	softaculous_pro_ajax_output($data);
	
}

// Get the template info from our servers
function softaculous_pro_ajax_template_info(){

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$data = [];
	
	if(isset($_REQUEST['slug'])){		
		$resp = wp_remote_get(softaculous_pro_pfx_api_url().'template-info.php?slug='.$_REQUEST['slug'], array('timeout' => 30));
	
		// Is the response valid ?
		if ( !is_wp_error( $resp ) && ( $resp['response']['code'] == 200 ) ){		
			$data = json_decode($resp['body'], true);
		}
	}
		
	$setup_info = get_option('softaculous_pro_setup_info');
	$setup_info = !empty($setup_info) ? $setup_info : array();
	$setup_info['theme_slug'] = $_REQUEST['slug'];

	update_option('softaculous_pro_setup_info',$setup_info);
	
	softaculous_pro_ajax_output($data);
	
}

// Start the installation of the template
function softaculous_pro_ajax_start_install_template(){
	
	global $softaculous_pro;
	
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');

	set_time_limit(300);
	
	// Handling Access through FTP
	ob_start();
	// Check if FTP is required
	$have_credentials = request_filesystem_credentials('');

	if(false === $have_credentials){
		$form_html = ob_get_clean();
		$ftp_modal = '<div id="request-filesystem-credentials-dialog" class="notification-dialog-wrap request-filesystem-credentials-dialog">
		<div class="notification-dialog-background"></div>
		<div class="notification-dialog" role="dialog" aria-labelledby="request-filesystem-credentials-title" tabindex="0">
		<div class="request-filesystem-credentials-dialog-content">'. $form_html . '</div></div></div>';

		wp_send_json_error(['form' => $ftp_modal]);
	}

	ob_end_clean(); // Just in case there was any output till now it will be cleaned.

	$data = [];
	
	//pagelayer_print($_POST);die();
	$license = softaculous_pro_optPOST('softaculous_pro_license');
	
	// Check if its a valid license
	if(!empty($license)){
	
		$resp = wp_remote_get(softaculous_pro_api_url(1).'license.php?license='.$license.'&url='.rawurlencode(site_url()), array('timeout' => 30));
	
		if(is_array($resp)){
			$json = json_decode($resp['body'], true);
			//print_r($json);
		}else{
		
			$data['error']['resp_invalid'] = __('The response from the server was malformed. Please try again in sometime !', 'softaculous-pro').var_export($resp, true);
			softaculous_pro_ajax_output($data);
			
		}
	
		// Save the License
		if(empty($json['license'])){
		
			$data['error']['lic_invalid'] = __('The license key is invalid', 'softaculous-pro');
			softaculous_pro_ajax_output($data);
			
		}else{
			
			update_option('softaculous_pro_license', $json);
	
			// Load license
			spro_load_license();
			
		}
		
	}
	
	// Load templates
	$softaculous_pro['templates'] = softaculous_pro_get_templates_list();
	
	$slug = softaculous_pro_optPOST('theme');
	
	if(!defined('PAGELAYER_VERSION')){
		
		$res = spro_install_required_plugin('pagelayer', array('plugin_init' => 'pagelayer/pagelayer.php'));
		
		if(empty($res['success'])){
			$data['error']['pl_req'] = __('Pagelayer is required to use the templates !', 'softaculous-pro');
			softaculous_pro_ajax_output($data);
		}
	}
	
	if(!defined('PFX_VERSION')){
		
		$res = spro_install_required_plugin('popularfx-templates', array('plugin_init' => 'popularfx-templates/popularfx-templates.php', 'plugin_download_url' => softaculous_pro_api_url(0, 'popularfx').'update2.php?give=1'));
		
		if(empty($res['success'])){
			$data['error']['pl_req'] = __('PopularFX plugin is required to use the templates !', 'softaculous-pro');
			softaculous_pro_ajax_output($data);
		}
	}
	
	if(!function_exists('popularfx_templates_dir')){

		$res = spro_install_required_theme('popularfx');
		
		if(empty($res['success'])){
			$data['error']['pfx_req'] = __('PopularFX theme is required to use the templates !', 'softaculous-pro');
			softaculous_pro_ajax_output($data);
		}
		
	}
	
	if(empty($softaculous_pro['templates']['list'][$slug])){
		$data['error']['template_invalid'] = __('The template you submitted is invalid !', 'softaculous-pro');
		softaculous_pro_ajax_output($data);
	}
	
	$template = $softaculous_pro['templates']['list'][$slug];
	
	// Do we have the req PL version ?
	if(!empty($template['pl_ver']) && version_compare(PAGELAYER_VERSION, $template['pl_ver'], '<')){
		$data['error']['pl_ver'] = sprintf(__('Your Pagelayer version is %1$s while the template requires Pagelayer version higher than or equal to %2$s ', 'softaculous-pro'), PAGELAYER_VERSION, $template['pl_ver']);
		softaculous_pro_ajax_output($data);
	}
	
	// Do we have the req PL version ?
	if(version_compare(PAGELAYER_VERSION, '1.8.9', '<')){
		$data['error']['pl_ver'] = sprintf(__('Your Pagelayer version is %1$s while the onboarding requires Pagelayer version higher than or equal to 1.8.9', 'softaculous-pro'), PAGELAYER_VERSION);
		softaculous_pro_ajax_output($data);
	}
	
	// Do we have the req PFX Plugin version ?
	if(!empty($template['pfx_ver']) && version_compare(PFX_VERSION, $template['pfx_ver'], '<')){
		$data['error']['pfx_ver'] = sprintf(__('Your PopularFX Plugin version is %1$s while the template requires PopularFX version higher than or equal to %2$s', 'softaculous-pro'), PFX_VERSION, $template['pfx_ver']);
		softaculous_pro_ajax_output($data);
	}
	
	// Is it a pro template ?
	if($template['type'] > 1 && empty($softaculous_pro['license']['active'])){
		$data['error']['template_pro'] = sprintf(__('The selected template is a Pro template and you have a free or expired license. Please enter your license key %1$shere%2$s.', 'softaculous-pro'), 
			'<a href="'.admin_url('admin.php?page=assistant&act=license').'" target="_blank" style="color:blue;">',
			'</a>'
			);
		softaculous_pro_ajax_output($data);
	}
	
	$do_we_have_pro = defined('PAGELAYER_PREMIUM');
	
	// Do we need to install Pagelayer or Pagelayer PRO ?
	if(!function_exists('pagelayer_theme_import_notices') || (empty($do_we_have_pro) && $template['type'] > 1)){
		if($template['type'] > 1){
			$download_url = SOFTACULOUS_PRO_PAGELAYER_API.'download.php?version=latest&license='.$softaculous_pro['license']['license'].'&url='.rawurlencode(site_url());
			$installed = spro_install_required_plugin('pagelayer-pro', array('plugin_init' => 'pagelayer-pro/pagelayer-pro.php', 'plugin_download_url' => $download_url));
		}else{
			$installed = spro_install_required_plugin('pagelayer', array('plugin_init' => 'pagelayer/pagelayer.php'));
		}
		
		// Did we fail to install ?
		if(is_wp_error($installed) || empty($installed)){
			$install_url = admin_url('admin.php?page=softaculous_pro_install_pagelayer&license=').@$softaculous_pro['license']['license'];
			$data['error']['pagelayer'] = sprintf(__('There was an error in installing Pagelayer which is required by this template. Please install Pagelayer manually by clicking %1$shere%2$s and then install the template !', 'softaculous-pro'), '<a href="%1$s" target="_blank">'.$install_url, '</a>');
			if(!empty($installed->errors)){
				$data['error']['pagelayer_logs'] = var_export($installed->errors, true);
			}
			softaculous_pro_ajax_output_xmlwrap($data);
		}
		
	}
	
	// Lets notify to download
	// $data['download'] = 1;
	$data['sel_plugin'] = 1;
	
	softaculous_pro_ajax_output_xmlwrap($data);
	
}

function softaculous_pro_ajax_selected_plugin (){
    
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
    
	if ( ! current_user_can( 'edit_posts' ) ) {    
        wp_send_json_error();
    }
	
    $results = array();
    $options = get_option('softaculous_pro_setup_info');
    $sel_features = $options['features'];
    if(!empty($sel_features)){
        $feature_list = spro_get_features_list();
        foreach($feature_list as $slug => $features){
            if (in_array($slug, $sel_features)) {
                foreach($features['plugin'] as $plugin_slug => $plugin_data){
					$res = spro_install_required_plugin($plugin_slug, $plugin_data);
					$results[] = array(
						'plugin_slug' => $plugin_slug,
						'status' => $res,
					);
                }
            }
        }
        foreach ($results as $item) {
            if (isset($item['status']['error'])) {
                $data['failed_plugin'][$item['plugin_slug']] = $item['status']['error'];
            }
        }
        $data['download'] = 1;
        softaculous_pro_ajax_output($data);
    }
}

// Download template
function softaculous_pro_ajax_download_template(){
	
	global $softaculous_pro;
	
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$slug = softaculous_pro_optPOST('theme');
	
	// Do the download
	$data = softaculous_pro_download_template($slug);
	
	// Any error ?
	if(!empty($data['error'])){
		softaculous_pro_ajax_output($data);
	}
	
	// Lets import then
	$data['import'] = 1;
	
	softaculous_pro_ajax_output($data);
	
}

// Import template
function softaculous_pro_ajax_import_template(){ 
	
	global $softaculous_pro, $pl_error;

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$slug = softaculous_pro_optPOST('theme');
	$to_import = softaculous_pro_optPOST('to_import');
	$_POST['set_home_page'] = 1;
	
	if(!empty($to_import)){
		$to_import[] = 'blog';
		$items = ['page' => $to_import];
	}else{
		$items = [];
	}
	
	// Import the template
	$data = softaculous_pro_import_template($slug, $items);
	
	softaculous_pro_ajax_output($data);
	
}

function softaculous_pro_save_setup_info(){
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$step = $_POST['step'];
	$post_data = wp_unslash($_POST['data']);
	$setup_info = get_option('softaculous_pro_setup_info');
	
	$setup_info = !empty($setup_info) ? $setup_info : array();

	if($step === 'type' && !empty($post_data) ){
		$setup_info['type'] = strtolower(str_replace(" ", "", $post_data));
	}
	
	if($step === 'title' && !empty($post_data)){
		update_option('blogname', $post_data);
	}
	
	if($step === 'features' && !empty($post_data)){
		$setup_info['features'] = $post_data;
	}
	
	$steps = array( 'type', 'title', 'features', 'import');
	update_option('softaculous_pro_setup_info', $setup_info);
}

function softaculous_pro_get_options(){
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	$options = get_option('softaculous_pro_setup_info');
	wp_send_json($options);
}

function spro_install_required_plugin($slug, $plugin, $pro = 0){
	
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/update.php';	
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	$res = array();
	
	try{
		if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['plugin_init'] ) && is_plugin_inactive( $plugin['plugin_init'] ) ) {
			activate_plugin($plugin['plugin_init']);
			$res['success'] = __("Plugin activated successfully.", 'softaculous-pro');
		}
		elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['plugin_init'] ) ){
			
			if (!empty($plugin['requires_php']) && version_compare(PHP_VERSION, $plugin['requires_php'], '<')) {
				throw new Exception(sprintf(__('Plugin installation failed. %1$s requires PHP version %2$s or higher. Your PHP version is %3$s.', 'softaculous-pro'),
					$plugin['plugin_name'],
					$plugin->requires_php,
					PHP_VERSION
				));
			}
			
			if(!empty($plugin['plugin_download_url'])){
				$download_url = $plugin['plugin_download_url'];
			}else{
				$api = plugins_api(
					'plugin_information',
					array(
							'slug'   => sanitize_key( wp_unslash( $slug ) ),
							'fields' => array(
								'sections' => false,
							),
						)
					);
				$download_url = $api->download_link;
			}
			
			$skin = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install($download_url);
				
			if ( is_wp_error($result) ) {
				throw new Exception(sprintf(__("Plugin installation failed. %1$s", 'softaculous-pro'), $result->get_error_message()));
			}
			elseif ( is_wp_error( $skin->result ) ) {
				throw new Exception(sprintf(__("Plugin installation failed. %1$s", 'softaculous-pro'), $skin->result->get_error_message()));
			} 
			elseif ( $skin->get_errors()->has_errors() ) {
				throw new Exception(sprintf(__("Plugin installation failed. %1$s", 'softaculous-pro'), $skin->get_error_messages()));
			} else {
				activate_plugin($plugin['plugin_init']);
				$res['success'] = __("Plugin installed and activated successfully.", 'softaculous-pro');
			}
		}else{
			$res['success'] = __("Plugin already installed.", 'softaculous-pro');
		}
	}
	catch( Exception $e){
		$res['error']  = $e->getMessage();
	}
	
	// Do we need to install the pro plugin as well ?
	if(empty($pro)){
		if(!empty($plugin['pro']) && !empty($plugin['plugin_download_url_pro'])){
			$plugin['plugin_download_url'] = softaculous_pro_add_params($plugin['plugin_download_url_pro']);
			$plugin['plugin_init'] = $plugin['plugin_init_pro'];
			$res['pro'] = spro_install_required_plugin($slug, $plugin, 1);
		}
	}
	
	return $res;
}

function spro_install_required_theme($slug, $theme = array()){
	
	$res = [];
	
	try {

		// Check if user is an admin and has appropriate permissions
		if(!current_user_can('install_themes')){
			throw new Exception(__("You do not have enough permissions to install theme", 'softaculous-pro'));
			return [];
		}

		if(!empty($theme['theme_download_url'])){
			$download_url = $theme['theme_download_url'];
		}else{
			$api = themes_api(
				'theme_information',
				array(
						'slug'   => sanitize_key( wp_unslash( $slug ) ),
						'fields' => array(
							'sections' => false,
							'downloadlink' => true,
						),
					)
				);
			$download_url = $api->download_link;
		}

		$theme_name = $slug;

		if(wp_get_theme($theme_name)->exists()){
			
			// Activate the theme
			switch_theme($theme_name);
			$res['success'] = __("Theme activated successfully.", 'softaculous-pro');
			
			return $res;
		}

		// Use WP Filesystem API to manage theme installation
		if(!function_exists('WP_Filesystem')){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		// Check if FTP is required
		ob_start();
		$credentials = request_filesystem_credentials('');
		ob_end_clean();

		if(false === $credentials || !WP_Filesystem($credentials)){
			$res['error'] = __('The filesystem could not write files to the server!', 'softaculous-pro');
			return $res;
		}

		global $wp_filesystem;

		// The directory where themes are installed
		$theme_dir = $wp_filesystem->wp_themes_dir();

		// Download the theme zip file
		$theme_zip = download_url($download_url);

		// Check for errors during download
		if(is_wp_error($theme_zip)){
			throw new Exception(sprintf(__('Error downloading theme: %1$s', 'softaculous-pro'), $theme_zip->get_error_message()));
		}

		// Unzip the downloaded theme file
		$unzip_result = unzip_file($theme_zip, $theme_dir);

		// Check for errors during unzip
		if(is_wp_error($unzip_result)){
			throw new Exception(sprintf(__('Error unzipping theme: ', 'softaculous-pro'), $unzip_result->get_error_message()));
		}

		// Delete the temporary zip file
		unlink($theme_zip);

		// Activate the theme after installation
		switch_theme($theme_name);
	}catch(\Exception $e){
		$res['error'] = __('Theme installation failed ', 'softaculous-pro') . $e->getMessage();
		return $res;
	}

	$res['success'] = __("Theme installed and activated successfully.", 'softaculous-pro');

	return $res;
}

// This is to replace the image variables for the template URL
function softaculous_pro_pagelayer_start_insert_content($post){
	
	$url = popularfx_templates_dir_url().'/'.$GLOBALS['softaculous_pro_template_import_slug'].'/';
	
	$replacers['{{theme_url}}/images/'] = $url.'images/';
	$replacers['{{theme_url}}'] = $url;
	$replacers['{{theme_images}}'] = $url.'images/';
	$replacers['{{themes_dir}}'] = dirname(get_stylesheet_directory_uri());
	
	foreach($replacers as $key => $val){
		$post['post_content'] = str_replace($key, $val, $post['post_content']);
	}
		
	return $post;
	
}

if(!function_exists('softaculous_pro_templates')){

// The Templates Page
function softaculous_pro_templates(){

	global $softaculous_pro, $pl_error, $spro_setup_info;
	
	$softaculous_pro['templates'] = softaculous_pro_get_templates_list();

	$spro_setup_info = get_option('softaculous_pro_setup_info');
	
	if(isset($_REQUEST['install'])){
		check_admin_referer('softaculous-pro-template');
	}

	// Is there a license key ?
	if(isset($_POST['install'])){
		
		$done = 1;
		
	}
	
	softaculous_pro_templates_T();
	
}

// The License Page - THEME
function softaculous_pro_templates_T(){
	
	global $softaculous_pro, $pagelayer, $pl_error, $spro_setup_info;
	
	// Any errors ?
	if(!empty($pl_error)){
		pagelayer_report_error($pl_error);echo '<br />';
	}
	
?>
<div id="softaculous_pro_theme_title">
	<h1 style="text-align:center;"><?php _e('Choose a design', 'softaculous-pro'); ?></h1><br /><br />
</div>
<div id="softaculous_pro_search" class="softaculous-pro-row">
	<div class="softaculous-pro-search">
		<input type="text" class="softaculous-pro-search-field" placeholder="<?php _e('Search for theme', 'softaculous-pro'); ?>" />    
		<div id="softaculous-pro-suggestion"></div>
	</div>
    <div class="softaculous-pro-dropdown softaculous-pro-categories">
		<div class="softaculous-pro-current-cat"><?php _e('All', 'softaculous-pro'); ?></div><span class="dashicons dashicons-arrow-down-alt2"></span>
		<div class="softaculous-pro-dropdown-content"><div class="softaculous-pro-cat-holder softaculous-pro-row" style="justify-content:flex-start;"></div></div>
	</div>
</div>
<div class="softaculous-pro-page" id="softaculous-pro-templates-holder">
	<div id="softaculous-pro-pagination"></div>
	<div id="softaculous-pro-templates" class="softaculous-pro-row" style="justify-content:flex-start"></div>
	<div id="softaculous-pro-single-template">
		<div style="margin-bottom: 20px; margin-top: 10px;  text-align: left;">
			<h1 style="display: inline-block;margin: 0px;vertical-align: middle;" id="softaculous-pro-template-name"></h1>
			<?php if (empty($softaculous_pro['branding']['rebranded'])): ?>
				<a href="" id="softaculous-pro-demo" class="button softaculous-pro-demo-btn" target="_blank"><?php _e('Demo', 'softaculous-pro'); ?></a>
			<?php endif; ?>
		</div>
		<div style="margin: 0px; vertical-align: top;" class="single-template-div">
			<div style="text-align: center;  position:relative; width:80%;">
				<div style="width: 100%; max-height: 500px; overflow: auto; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);" class="single-templates-content">
				<div class="loader-container" >
					<div class="loader" style="display:none"></div>
				</div>
					<img id="softaculous_pro_display_image" src="" width="100%" style="position:relative; z-index:2;">
				</div>
			</div>
			<div class="softaculous_pro_single_content">
				<h1 style="font-size:16px;"><?php _e('Select pages to import', 'softaculous-pro'); ?></h1><br />
				<div id="softaculous_pro_screenshots"></div>
			<div class="softaculous_pro_import_img_notice">
				<form id="softaculous-pro-import-form" method="post" enctype="multipart/form-data">
					<?php wp_nonce_field('softaculous-pro-template');?>
					<input name="theme" id="softaculous-pro-template-install" value="" type="hidden" />
					<input type="checkbox" name="download_imgs" id="download_imgs" value="1" checked/> <label for="download_imgs" class="spro-tool-tip" style="cursor:pointer;"><?php _e('Import stock images ?', 'softaculous-pro'); ?></label><br />
					<i>
						<?php _e('We try our best to use images that are free to use from legal perspectives. However, we are not liable for any copyright infringement for your site.', 'softaculous-pro'); ?>
					</i>
					<input name="install" value="1" type="hidden" />
				</form>
			</div>
			</div>
		</div>

		<div style="position:fixed; bottom: 15px; right: 20px; z-index: 10;">
			<div class="button button-softaculous-pro softaculous-pro-back"  style="display:none;"><?php _e('Go Back', 'softaculous-pro'); ?></div>&nbsp;
			<input name="import_theme" class="button button-softaculous-pro" id="spro_import_content" value="<?php _e('Import Theme Content', 'softaculous-pro'); ?>" type="button" onclick="softaculous_pro_modal('#SproTemplatesModal')" style="display:none;"/> &nbsp;
		</div>	
	
	</div>
</div>

<!-- The Modal -->
<div id="SproTemplatesModal" class="softaculous-pro-modal">

	<!-- Modal holder -->
	<div class="softaculous-pro-modal-holder">

		<!-- Modal header -->
		<div class="softaculous-pro-modal-header">
			<h1><?php _e('Import Theme Contents', 'softaculous-pro'); ?></h1> 
			<!-- <span class="softaculous-pro-modal-close">&times;</span> -->
		</div>
		<!-- Modal content -->
		<div class="softaculous-pro-modal-content">
			<div class="softaculous-pro-import">
				<div id="softaculous-pro-error-template"></div>
				<div id="softaculous-pro-progress-template">
					<img src="<?php echo esc_attr(SOFTACULOUS_PRO_PLUGIN_URL) .'assets/images/progress.svg';?>" width="20" /> <span id="softaculous-pro-progress-txt"></span>
				</div>
			</div>
			<div class="softaculous-pro-done" style="display: block;">
				<h3 style="margin-top: 0px;"><?php _e('Congratulations, the template was imported successfully !', 'softaculous-pro'); ?></h3>
				<?php _e('You can now customize the website as per your requirements with the help of Pagelayer or the Customizer.', 'softaculous-pro')
				.'<br /><br />
				<b>Note</b> : '.
				_e('We strongly recommend you change all images and media. We try our best to use images which are copyright free or are allowed under their licensing. However, we take no responsibilities for the same and recommend you change all media and images !', 'softaculous-pro'); ?>
			</div>
		</div>
		
		<!-- Modal footer -->
		<div class="softaculous-pro-modal-footer">
			<div class="softaculous-pro-done">
				<a class="button softaculous-pro-demo-btn" href="<?php echo site_url();?>" target="_blank"><?php _e('Visit Website', 'softaculous-pro'); ?></a> &nbsp;&nbsp;
				<a class="button softaculous-pro-demo-btn" href="<?php echo admin_url();?>" target="_blank"><?php _e('WordPress Dashboard', 'softaculous-pro'); ?></a> &nbsp;&nbsp;
				<a class="button softaculous-pro-demo-btn" href="<?php echo admin_url('admin.php?page=assistant');?>" target="_blank"><?php _e('Assistant', 'softaculous-pro'); ?></a>
			</div>
		</div>
	</div>

</div>

<script>

var softaculous_pro_setup_info = JSON.parse('<?php echo json_encode((!empty($spro_setup_info) ? $spro_setup_info : array())); ?>');

// Add to tabs override
jQuery(document).ready(function(){
	softaculous_pro_templates_fn(jQuery);
	if(softaculous_pro_setup_info && softaculous_pro_setup_info.type){
		softaculous_pro_setup_info.type = softaculous_pro_setup_info.type.replace("-", "");
		jQuery('#cat_button_'+softaculous_pro_setup_info.type).trigger("click");
	}
});

var softaculous_pro_ajax_nonce = '<?php echo wp_create_nonce('softaculous_pro_ajax');?>';
var softaculous_pro_ajax_url = '<?php echo admin_url( 'admin-ajax.php' );?>?&';
var softaculous_pro_demo = 'https://demos.popularfx.com/';

softaculous_pro_templates = <?php echo json_encode($softaculous_pro['templates']);?>;
var themes = softaculous_pro_templates['list'];
var categories = softaculous_pro_templates['categories'];
var mirror = '<?php echo softaculous_pro_sp_api_url("-1");?>files/themes/';

function softaculous_pro_update_cat_input(other_cat){
	jQuery('#cat_input').val(other_cat);
}

function softaculous_pro_templates_fn($){

// Back button handler
jQuery('.softaculous-pro-back').click(function(){
	softaculous_pro_show_themes(softaculous_pro_setup_info.type !== undefined ? softaculous_pro_setup_info.type.toLowerCase() : '');
	jQuery("#spro_import_content").hide();
	jQuery(".softaculous-pro-back").hide();
});

jQuery('.softaculous-pro-back-theme').click(function(){
	jQuery('#softaculous-pro-templates-holder').show();
	jQuery(this).parent().hide();
	jQuery('#SproTemplatesModal').hide();
});

jQuery('#cat_input').keyup(function() {
	
	var query = jQuery(this).val().toLowerCase();
	var cat_displayed = 0;
	
	jQuery(".category_btn").each(function( index ){
		var cslug = jQuery(this).find("input").attr("data-target");
		if(cslug.toLowerCase().includes(query)){
			jQuery(this).show();
			cat_displayed++;
		}else{
			jQuery(this).hide();
		}
		
		if(cat_displayed > 0){
			jQuery("#spro_no_cat_results").hide();
		}else{
			jQuery("#spro_no_cat_results").show();
		}
	});
	
});

// Fill the categories
var chtml = '<div class="softaculous-pro-md-4 softaculous-pro-cat" data-cat="">All</div>';
for(var x in categories){
	chtml += '<div class="softaculous-pro-md-4 softaculous-pro-cat" data-cat="'+x+'">'+categories[x]['en']+'</div>';
}

jQuery('.softaculous-pro-cat-holder').html(chtml);
jQuery('.softaculous-pro-cat-holder').find('.softaculous-pro-cat').click(function(){
	softaculous_pro_show_themes(jQuery(this).data('cat'));
});

jQuery('.softaculous-pro-categories-list').find('.category_btn').click(function(){
	var childEle = jQuery(this).children('input');
	var jEle_parent =  jQuery(this).parent().find(".active_category");
	var real_val = jQuery(this).children('input').val();
	var val = jQuery(this).children('input').val().toLowerCase();
	var inputSection = jQuery(".softaculous-pro-category-input");


	if (jQuery(this).hasClass("active_category")) {
		jQuery(this).removeClass("active_category");
		inputSection.find('input').val('')
		return;
	}
	
	jEle_parent.removeClass("active_category");
	jQuery(this).addClass("active_category");
	
	if(real_val){
		inputSection.find('input').val(real_val)
	}
	
	softaculous_pro_show_themes(val);
});

// Search Clear
jQuery('.softaculous-pro-sf-empty').click(function(){
	jQuery('.softaculous-pro-search-field').val('');
	softaculous_pro_show_themes();
});

// Search
jQuery('.softaculous-pro-search-field').on('keyup', function(e){
	softaculous_pro_show_themes('', jQuery(this).val());
});

// Sort themes
jQuery('.softaculous-pro-sortby').change(function(){
	softaculous_pro_show_themes(jQuery('.softaculous-pro-current-cat').data('cat'), jQuery('.softaculous-pro-search-field').val());
});

};

// Show the themes
function softaculous_pro_show_themes(cat, search, page){
	
	softaculous_pro_show_themes_loaded = 1;
	
	var sortby = 'latest';
	jQuery("#softaculous_pro_search").show();	
	jQuery("#softaculous_pro_theme_title").show();	
	jQuery("#softaculous-pro-suggestion").hide();	
	jQuery("#softaculous-pro-single-template").hide();
	jQuery("#softaculous-pro-pagination").show();
	jQuery("#softaculous-pro-templates").show();
	
	// Blank html
	jQuery('#softaculous-pro-templates').html('');
	jQuery('#softaculous-pro-pagination').html('');

	var search = search || "";
	var cat =   cat || softaculous_pro_setup_info.type || "";
	var cat = cat.replace("-", "");
	softaculous_pro_setup_info.type = cat;
	var cat = (categories[cat] === undefined && (cat && cat.length) > 0 ? 'others' : cat) || "" ;

	var num = 60;
	var page = page || 1;
	var start = num * (page - 1);
	var end = num + start;
	var i = 0;
	var cat_appender = categories[cat] === undefined ? 'Others' : categories[cat]['en']

	if(cat.length > 0){
		jQuery('.softaculous-pro-current-cat').html(cat_appender);
		jQuery('.softaculous-pro-current-cat').data('cat', cat);
	}else{
		jQuery('.softaculous-pro-current-cat').html('All');
		jQuery('.softaculous-pro-current-cat').data('cat', '');
	}
	
	var allowed_list = [];
	
	if(search.length > 0){
		search = search.toLowerCase();
		
		for(var x in softaculous_pro_templates['tags']){
			if(x.toLowerCase().indexOf(search) >= 0){
				allowed_list = allowed_list.concat(softaculous_pro_templates['tags'][x]);
			}
		}
	}
	
	if(allowed_list.length > 0){
		allowed_list = Array.from(new Set(allowed_list));
	}
	
	var themeids = [];
	var sorted = {};
	var rsorted = {};
	
	for(var x in themes){
		themeids.push(parseInt(themes[x].thid));
	}	
	
	if(sortby == "latest"){
		var datatheme = Object.values(themes);
		var rsorted_ids = themeids.sort().reverse();
		for(var x of rsorted_ids){
			for( var y in datatheme){
				if(datatheme[y].thid == x){
					rsorted[datatheme[y].slug] = datatheme[y];
				}
			}
		}
		themes = rsorted;
	
	}else if(sortby == "oldest"){
		var datatheme = Object.values(themes);
		var sorted_ids = themeids.sort();
		for(var x of sorted_ids){
			for( var y in datatheme){
				if(datatheme[y].thid == x){
					sorted[datatheme[y].slug] = datatheme[y];
				}
			}
		}
		
		themes = sorted;
		
	}else{
		themes = softaculous_pro_templates['list'];		
	}	
	
	for(var x in themes){
		
		// Is it same category
		if(cat.length > 0 && cat != themes[x].category){
			continue;
		}
		
		// Is it a searched item
		if(search.length > 0 && themes[x].name.toLowerCase().indexOf(search) === -1 && allowed_list.indexOf(themes[x].thid) === -1){
			continue;
		}
		
		if(i >= start && i < end){
			//console.log(x+' '+i+' '+start+' '+end);
			softaculous_pro_show_theme_tile(themes[x], x);
		}
		
		i++;
		
	}
	
	jQuery('.softaculous-pro-theme-details').click(function(){
		var jEle = jQuery(this);
		softaculous_pro_show_theme_details(jEle.attr('slug'));
	});
	
	var pages = Math.ceil(i/num);
	
	if(pages > 1){
		
		var html = '<ul class="pagination">';
		
		for(var p = 1; p <= pages; p++){
			html += '<li class="page-item '+(page == p ? 'active' : '')+'"><a class="page-link" href="#" data-cat="'+cat+'" data-search="'+search+'" data-page="'+p+'">'+p+'</a></li>';
		}
		
		html += '</ul>';
		
		jQuery('#softaculous-pro-pagination').html(html);
		
		jQuery('#softaculous-pro-pagination').find('.page-link').click(function(){
			var j = jQuery(this);
			softaculous_pro_show_themes(j.data('cat'), j.data('search'), j.data('page'));
		});
		
	}
	
}

function softaculous_pro_show_theme_tile(theme, x){
	var html = '<div class="softaculous-pro-md-4">'+
		'<div class="softaculous-pro-theme-details" slug="'+theme['slug']+'" thid="'+theme['thid']+'">'+
			'<div class="softaculous-pro-theme-screenshot">'+
				'<img src="'+mirror+'/'+theme['slug']+'/screenshot.jpg" loading="lazy" alt="" />'+
			'</div>'+
			'<div class="softaculous-pro-theme-name">'+theme['name']+'</div>'+
		'</div>'+
	'</div>';
	jQuery('#softaculous-pro-templates').append(html);
}

function softaculous_pro_strip_extension(str){
    return str.substr(0,str.lastIndexOf('.'));
}

// Show the theme details
function softaculous_pro_show_theme_details(slug){
	
	var theme = themes[slug];
	
	jQuery("#softaculous-pro-suggestion").hide();	
	jQuery("#softaculous_pro_search").hide();	
	jQuery("#softaculous_pro_theme_title").hide();	
	jQuery("#softaculous-pro-single-template").show();
	jQuery("#softaculous-pro-pagination").hide();
	jQuery("#softaculous-pro-templates").hide();
	
	// Set install value
	jQuery('#softaculous-pro-template-install').val(slug);
			
	// Set name
	jQuery("#softaculous-pro-template-name").html(theme['name']);
			
	// Demo URL
	jQuery("#softaculous-pro-demo").attr("href", softaculous_pro_demo+(theme['name'].replace(' ', '_')));
	
	// Blank screenshots
	jQuery("#softaculous_pro_screenshots").html('');
	
	// Is the license PRO ?
	if(theme['type'] >= 2){
		jQuery('#softaculous_pro_license_div').css('display', 'inline-block');
	}else{
		jQuery('#softaculous_pro_license_div').hide();
	}
	
	var url = mirror+'/'+theme['slug'];
	
	// Show home image
	jQuery("#softaculous_pro_display_image").attr("src", "");
	jQuery("#softaculous_pro_display_image").attr("src", url+'/screenshots/home.jpg');
	jQuery("#softaculous_pro_display_image").parent().scrollTop(0);
	
	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_template_info',
		type: 'POST',
		data: {
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			slug: slug
		},
		dataType: 'json',
		success:function(theme) {
			
			jQuery("#spro_import_single").addClass("hidden");
			
			var sc = '';
			// var test= '';
			// Show the screenshots
			for(var x in theme['screenshots']){
				var page_name = softaculous_pro_strip_extension(theme['screenshots'][x]);
				sc += '<div class="softaculous_pro_img_screen" page="'+x+'" page-name="'+page_name+'">'+
				'<div class="spro_page_selector"><input type="checkbox" checked="checked" class="checkbox" id="'+page_name+'">'+
				'<label for="'+page_name+'" class="softaculous_pro_img_name">'+page_name+'</label></div>'+
				'<a href="'+url+'/screenshots/'+theme['screenshots'][x] +'" class="softaculous_pro_img_views view-'+page_name+' dashicons dashicons-visibility"></a>'+
				'</div>';
				
			}
			
			jQuery("#softaculous_pro_screenshots").html(sc);
			jQuery("#spro_import_content").show();
			jQuery(".softaculous-pro-back").show();
			jQuery('.softaculous_pro_img_screen:first').children('a').addClass('spro_img_inview');


			jQuery("#softaculous_pro_screenshots").find('.softaculous_pro_img_views').click(function(e){
				e.preventDefault();
				var jEle = jQuery(this);
				jQuery("#softaculous_pro_display_image").hide();
				jQuery(".loader").show();
				
				if(jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').hasClass('spro_img_inview')){
					jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').removeClass('spro_img_inview');
				}
				
				var newImageSrc = jEle.attr("href");
				jQuery("#softaculous_pro_display_image").attr("src", newImageSrc);

				// Handle image load event
				jQuery("#softaculous_pro_display_image").on('load', function() {
					jQuery(".loader").hide(); // Hide loader
					jQuery(this).show(); // Show image
				});

				// In case the image is cached and loads immediately
				if (jQuery("#softaculous_pro_display_image")[0].complete) {
					jQuery(".loader").hide(); // Hide loader
					jQuery("#softaculous_pro_display_image").show(); // Show image
				}

				jQuery("#softaculous_pro_display_image").parent().scrollTop(0);
				jEle.addClass('spro_img_inview');
			});

			// need to refactor it its create multiple in html
			jQuery("#softaculous_pro_screenshots").find('.softaculous_pro_img_views').on('mouseenter',function(e){
				var imgUrl = jQuery(this).attr('href');
				if(!jQuery(this).attr('loaded')){
					jQuery('<img>').attr('src', imgUrl).on('load', function() {}).appendTo('body').css('display', 'none');
				}
				jQuery(this).attr('loaded',true);
			});

			jQuery("#softaculous_pro_screenshots").find('.spro_page_selector').click(function(event){
				var jEle = jQuery(this);
				jQuery("#softaculous_pro_display_image").hide();
				jQuery(".loader").show();

				var checkbox = jQuery(this).find('.checkbox');
				if (jQuery(event.target).is('.checkbox') || jQuery(event.target).is('.softaculous_pro_img_name')) {
					return;
				}
				jEle.siblings('.softaculous_pro_img_views').trigger('click');
				
				
			});
			 // Change event on the checkbox
			 jQuery("#softaculous_pro_screenshots").find('.checkbox').change(function() {
				var checked_div = jQuery(this).siblings('.softaculous_pro_img_name');

				if(jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').hasClass('spro_img_inview')){
					jQuery('.softaculous_pro_img_screen .softaculous_pro_img_views').removeClass('spro_img_inview');
				}
				var newImageSrc = jQuery(this).parent().siblings('.softaculous_pro_img_views').attr("href");

				jQuery(this).parent().siblings('.softaculous_pro_img_views').addClass('spro_img_inview');
				jQuery("#softaculous_pro_display_image").attr("src", newImageSrc);

				jQuery("#softaculous_pro_display_image").on('load', function() {
					jQuery(".loader").hide(); // Hide loader
					jQuery(this).show(); // Show image
				});

				if (jQuery(this).is(':checked')) {
					checked_div.addClass("softaculous_pro_img_selected");
				} else {
					checked_div.removeClass("softaculous_pro_img_selected");
				}
			});
			
		}
	});
	
}

function softaculous_pro_onboarding_dismiss(e){
	
	jQuery.ajax({
		type: 'post',
		url: soft_pro_obj.ajax_url,
		data: {
			action: 'softaculous_pro_onboarding_dismiss',
			dismiss: 1,
			softaculous_pro_nonce: softaculous_pro_ajax_nonce,
			data: [],
		},
		complete: function (response) {
			window.location = soft_pro_obj.admin_url+"admin.php?page=assistant";
		},
	});
	
}

</script>

<?php


}

}