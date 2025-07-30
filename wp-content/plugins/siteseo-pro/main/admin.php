<?php
/*
* SITESEO
* https://siteseo.io
* (c) SITSEO Team
*/

namespace SiteSEOPro;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Admin{
	
	static function init(){
		global $siteseo;

		add_action('admin_enqueue_scripts', '\SiteSEOPro\Admin::enqueue_script');
		add_action('admin_menu', '\SiteSEOPro\Admin::add_menu', 100);
		add_action('init', '\SiteSEOPro\RedirectManager::setup_log_scheduled');
		add_action('siteseo_structured_data_types_enqueue', '\SiteSEOPro\StructuredData::enqueue_metabox');
		add_action('siteseo_display_structured_data_types', '\SiteSEOPro\StructuredData::display_metabox');
		add_action('siteseo_display_video_sitemap', '\SiteSEOPro\VideoSitemap::display_metabox');
		add_action('siteseo_display_google_news', '\SiteSEOPro\GoogleNews::display_metabox');
		add_action('admin_notices', '\SiteSEOPro\Admin::free_version_nag');
		
		if(current_user_can('activate_plugins') && !empty($siteseo->license) && empty($siteseo->license['active']) && strpos($siteseo->license['license'], 'SOFTWP') !== FALSE){
			add_action('admin_notices', '\SiteSEOPro\Admin::license_expired_notice');
			add_filter('softaculous_expired_licenses', '\SiteSEOPro\Admin::plugins_expired');
		}
	}
	
	static function enqueue_script(){
		
		if(empty($_GET['page']) || strpos($_GET['page'], 'siteseo') === FALSE){
			return;
		}

		wp_enqueue_media();
		
		wp_enqueue_script('siteseo-pro-admin', SITESEO_PRO_URL.'assets/js/admin.js', ['jquery'], SITESEO_PRO_VERSION, true);

		wp_localize_script('siteseo-pro-admin', 'siteseo_pro', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('siteseo_pro_nonce'),
		]);

		wp_enqueue_style('siteseo-pro-admin', SITESEO_PRO_URL . 'assets/css/admin.css');

		
	}
	
	static function add_menu(){
		$capability = 'manage_options';

		add_submenu_page('siteseo', __('PRO', 'siteseo-pro'), __('PRO', 'siteseo-pro'), $capability, 'siteseo-pro-page', '\SiteSEOPro\Settings\Pro::home');

		add_submenu_page('siteseo', __('License', 'siteseo-pro'), __('License', 'siteseo-pro'), $capability, 'siteseo-license', '\SiteSEOPro\Settings\License::template');
	}
	

	static function local_business_block(){

		wp_register_script('local-business-block-script',SITESEO_PRO_URL . 'assets/js/block.js', array('wp-blocks', 'wp-element', 'wp-editor'), filemtime(SITESEO_PRO_DIR . 'assets/js/block.js'));
		
		$data = \SiteSEOPro\Tags::local_business();
		
		// Localize
		wp_localize_script('local-business-block-script', 'siteseoProLocalBusiness', array(
			'previewData' => $data,
		));

		register_block_type('siteseo-pro/local-business', array(
			'editor_script' => 'local-business-block-script',
			'render_callback' => '\SiteSEOPro\Tags::load_data_local_business'
		));
	}
	
	// Nag when plugins dont have same version.
	static function free_version_nag(){

		if(!defined('SITESEO_VERSION')){
			return;
		}

		$dismissed_free = (int) get_option('siteseo_version_free_nag');
		$dismissed_pro = (int) get_option('siteseo_version_pro_nag');

		// Checking if time has passed since the dismiss.
		if(!empty($dismissed_free) && time() < $dismissed_pro && !empty($dismissed_pro) && time() < $dismissed_pro){
			return;
		}

		$showing_error = false;
		if(version_compare(SITESEO_VERSION, SITESEO_PRO_VERSION) > 0 && (empty($dismissed_pro) || time() > $dismissed_pro)){
			$showing_error = true;

			echo '<div class="notice notice-warning is-dismissible" id="siteseo-pro-version-notice" onclick="siteseo_pro_dismiss_notice(event)" data-type="pro">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of SiteSEO Pro. We recommend updating to the latest version to ensure seamless and uninterrupted use of the application.', 'siteseo-pro').'</p>
		</div>';
		}elseif(version_compare(SITESEO_VERSION, SITESEO_PRO_VERSION) < 0 && (empty($dismissed_free) || time() > $dismissed_free)){
			$showing_error = true;

			echo '<div class="notice notice-warning is-dismissible" id="siteseo-pro-version-notice" onclick="siteseo_pro_dismiss_notice(event)" data-type="free">
			<p style="font-size:16px;">'.esc_html__('You are using an older version of SiteSEO. We recommend updating to the latest free version to ensure smooth and uninterrupted use of the application.', 'siteseo-pro').'</p>
		</div>';
		}
		
		if(!empty($showing_error)){
			wp_register_script('siteseo-pro-version-notice', '', ['jquery'], SITESEO_PRO_VERSION, true );
			wp_enqueue_script('siteseo-pro-version-notice');
			wp_add_inline_script('siteseo-pro-version-notice', '
		function siteseo_pro_dismiss_notice(e){
			e.preventDefault();
			let target = jQuery(e.target);

			if(!target.hasClass("notice-dismiss")){
				return;
			}

			let jEle = target.closest("#siteseo-pro-version-notice"),
			type = jEle.data("type");

			jEle.slideUp();

			jQuery.post("'.admin_url('admin-ajax.php').'", {
				security : "'.wp_create_nonce('siteseo_version_notice').'",
				action: "siteseo_pro_version_notice",
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
	
	static function plugins_expired($plugins){
		$plugins[] = 'SiteSEO';
		return $plugins;
	}

	static function license_expired_notice(){
		global $siteseo;

		// The combined notice for all Softaculous plugin to show that the license has expired
		$dismissed_at = get_option('softaculous_expired_licenses', 0);
		$expired_plugins = apply_filters('softaculous_expired_licenses', []);
		$soft_wp_buy = 'https://www.softaculous.com/clients?ca=softwp_buy';

		if(
			!empty($expired_plugins) && 
			is_array($expired_plugins) &&
			count($expired_plugins) > 0 && 
			!defined('SOFTACULOUS_EXPIRY_LICENSES') && 
			(empty($dismissed_at) || ($dismissed_at + WEEK_IN_SECONDS) < time())
		){

			define('SOFTACULOUS_EXPIRY_LICENSES', true); // To make sure other plugins don't return a Notice
			$soft_rebranding = get_option('softaculous_pro_rebranding', []);

			if(!empty($siteseo->license['has_plid'])){
				if(!empty($soft_rebranding['sn']) && $soft_rebranding['sn'] != 'Softaculous'){
					
					$msg = sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please contact %3$s to continue receiving uninterrupted updates and support for %4$s.', 'siteseo-pro'),
						'<font style="color:red;"><b>',
						'</b></font>',
						esc_html($soft_rebranding['sn']),
						esc_html(implode(', ', $expired_plugins))
					);
					
				}else{
					$msg = sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please contact your hosting provider to continue receiving uninterrupted updates and support for %3$s.', 'siteseo-pro'),
						'<font style="color:red;"><b>',
						'</b></font>',
						esc_html(implode(', ', $expired_plugins))
					);
				}
			}else{
				$msg = sprintf(__('Your SoftWP license has %1$sexpired%2$s. Please %3$srenew%4$s it to continue receiving uninterrupted updates and support for %5$s.', 'siteseo-pro'),
					'<font style="color:red;"><b>',
					'</b></font>',
					'<a href="'.esc_url($soft_wp_buy.'&license='.$siteseo['license']['license'].'&plan='.$siteseo['license']['plan']).'" target="_blank">',
					'</a>',
					esc_html(implode(', ', $expired_plugins))
				);
			}
			
			
			echo '<div class="notice notice-error is-dismissible" id="siteseo-pro-expiry-notice">
					<p>'.$msg.'</p>
				</div>';

			wp_register_script('siteseo-pro-expiry-notice', '', array('jquery'), SITESEO_PRO_VERSION, true);
			wp_enqueue_script('siteseo-pro-expiry-notice');
			wp_add_inline_script('siteseo-pro-expiry-notice', '
			jQuery(document).ready(function(){
				jQuery("#siteseo-pro-expiry-notice").on("click", ".notice-dismiss", function(e){
					e.preventDefault();
					let target = jQuery(e.target);

					let jEle = target.closest("#siteseo-pro-expiry-notice");
					jEle.slideUp();
					
					jQuery.post("'.admin_url('admin-ajax.php').'", {
						security : "'.wp_create_nonce('siteseo_expiry_notice').'",
						action: "siteseo_pro_dismiss_expired_licenses",
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
