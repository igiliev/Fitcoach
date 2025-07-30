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

class Ajax{

	static function hooks(){
		add_action('wp_ajax_siteseo_pro_get_pagespeed_insights', '\SiteSEOPro\Ajax::get_pagespeed');
		add_action('wp_ajax_siteseo_pro_pagespeed_insights_remove_results', '\SiteSEOPro\Ajax::delete_speed_scores');

		//toogle option pro
		add_action('wp_ajax_siteseo_pro_save_woocommerce', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_edd', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_dublin', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_local', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_structured' , '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_404_monitoring', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_google_news', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_video_sitemap', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_save_rss_sitemap', '\SiteSEOPro\Ajax::save_toggle');
		add_action('wp_ajax_siteseo_pro_update_htaccess', '\SiteSEOPro\Ajax::update_htaccess');
		add_action('wp_ajax_siteseo_pro_update_robots', '\SiteSEOPro\Ajax::update_robots');
		add_action('wp_ajax_siteseo_pro_export_redirect_csv', '\SiteSEOPro\Ajax::export_csv_redirect_logs');
		add_action('wp_ajax_siteseo_pro_clear_all_logs', '\SiteSEOPro\Ajax::redirect_clear_all_logs');
		add_action('wp_ajax_siteseo_pro_remove_selected_logs', '\SiteSEOPro\Ajax::delete_selected_log');
		add_action('wp_ajax_siteseo_pro_delete_robots_txt', '\SiteSEOPro\Ajax::delete_robots_txt');
		add_action('wp_ajax_siteseo_pro_version_notice', '\SiteSEOPro\Ajax::version_notice');
		add_action('wp_ajax_siteseo_pro_dismiss_expired_licenses', '\SiteSEOPro\Ajax::dismiss_expired_licenses');
	}

	static function save_toggle(){

		check_ajax_referer('siteseo_pro_toggle_nonce', 'nonce');

		$action = sanitize_text_field(wp_unslash($_POST['action']));
		switch($action){
			case 'siteseo_pro_save_woocommerce':
				$toggle_key = 'toggle_state_woocommerce';
				break;
			case 'siteseo_pro_save_edd':
				$toggle_key = 'toggle_state_easy_digital';
				break;
			case 'siteseo_pro_save_dublin':
				$toggle_key = 'toggle_state_dublin_core';
				break;
			case 'siteseo_pro_save_local':
				$toggle_key = 'toggle_state_local_buz';		
				break;
			case 'siteseo_pro_save_structured':
				$toggle_key = 'toggle_state_stru_data';
				break;
			case 'siteseo_pro_save_404_monitoring':
				$toggle_key = 'toggle_state_redirect_monitoring';
				break;
			case 'siteseo_pro_save_google_news':
				$toggle_key = 'toggle_state_google_news';
				break;
			case 'siteseo_pro_save_video_sitemap':
				$toggle_key = 'toggle_state_video_sitemap';
				break;
			case 'siteseo_pro_save_rss_sitemap':
				$toggle_key = 'toogle_state_rss_sitemap';
				break;
			default:
				wp_send_json_error(['message' => 'Invalid action']);
				return;
		}

		$toggle_value = isset($_POST['toggle_value']) ? sanitize_text_field(wp_unslash($_POST['toggle_value'])) : '0';

		$options = get_option('siteseo_pro_options', []);
		$options[$toggle_key] = $toggle_value;
		
		if($toggle_key == 'toggle_state_redirect_monitoring'){
			\SiteSEOPro\Settings\Util::maybe_create_404_table();
		}
		
		
		$updated = update_option('siteseo_pro_options', $options);

		if($updated){
			wp_send_json_success([
				'message' => ucfirst($toggle_key) . ' toggle state saved successfully',
				'value' => $toggle_value
			]);
		} else{
			wp_send_json_error(['message' => 'Failed to save toggle state']);
		}
	
	}
	
	static function get_pagespeed(){
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have enough privilege to use this feature', 'siteseo-pro'));
		}

		global $siteseo;

		$api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
		$api_key = $siteseo->pro['ps_api_key'];
		$site_url = isset($_POST['test_url']) ? sanitize_url($_POST['test_url']) : site_url();
		
		if(empty($api_key)){
			wp_send_json_error(__('You have not saved the API key', 'siteseo-pro'));
		}
		
		if(empty($site_url)){
			wp_send_json_error(__('The URL you have provided is not valid', 'siteseo-pro'));
		}
		
		$device = (!empty($_REQUEST['is_mobile']) && $_REQUEST['is_mobile'] != 'false') ? 'mobile' : 'desktop';
		$request_url = $api_url . '?url=' . urlencode($site_url) . '&strategy='.$device.'&key='.$api_key;

		$response = wp_remote_get($request_url, array('timeout' => 60)); // 60 sec wait time 

		if(is_wp_error($response)){
			$error_message = is_wp_error($response) ? $response->get_error_message() : $response->get_error_message();

			wp_send_json_error($error_message);
		}

		$body = wp_remote_retrieve_body($response);

		if(empty($body)){
			wp_send_json_error(__('Response body is empty', 'siteseo-pro'));
		}

		$result = json_decode($body, true);
		
		$page_speed = get_option('siteseo_pro_page_speed', []);

		// Handling Pagespeed insight result.
		foreach($result['lighthouseResult']['audits'] as $key => $audit){

			if(isset($audit['title']) && isset($audit['description']) && !isset($audit['details']['type'])){
				$page_speed[$device][$key] = [
					'id' => $audit['id'],
					'score' => $audit['score'],
					'title' => $audit['title'],
					'description' => $audit['description']
				];
			}

			if(isset($audit['details']['type']) && $audit['details']['type'] === 'opportunity'){
				$page_speed[$device]['opportunities'][] = [
					'title' => $audit['title'],
					'description' => $audit['description'],
					'score' => isset($audit['score']) ? $audit['score'] : null
				];
			}

			if(!isset($page_speed[$device]['diagnostics'])){
				$page_speed[$device]['diagnostics'] = [];
			}

			if(isset($audit['score']) && isset($audit['details']['type']) && $audit['score'] <= 0.89 && $audit['details']['type'] != 'opportunity'){
				$page_speed[$device]['diagnostics'][] = [
					'title' => $audit['title'],
					'description' => $audit['description'],
					'score' => isset($audit['score']) ? $audit['score'] : null
				];
			}
		}

		$page_speed[$device]['fetchTime'] = $result['lighthouseResult']['fetchTime'];
		$page_speed[$device]['score'] = $result['lighthouseResult']['categories']['performance']['score'];
		
		update_option('siteseo_pro_page_speed', $page_speed);
		
		wp_send_json_success();
	}
	
	static function delete_speed_scores(){
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have enough privilege to use this feature', 'siteseo-pro'));
		}
		
		delete_option('siteseo_pro_page_speed');
		wp_send_json_success();
	}

	static function update_htaccess(){
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'siteseo-pro'));
		}

		$htaccess_enable = isset($_POST['htaccess_enable']) ? intval(sanitize_text_field(wp_unslash($_POST['htaccess_enable']))) : 0;
		$htaccess_rules = isset($_POST['htaccess_code']) ? sanitize_textarea_field(wp_unslash($_POST['htaccess_code'])) : '';

		if(empty($htaccess_enable)){
			wp_send_json_error(__('Please accept the warning first before proceeding with saving the htaccess', 'siteseo-pro'));
		}

		$htaccess_file = ABSPATH . '.htaccess';
		$backup_file = ABSPATH . '.htaccess_backup.siteseo';

		if(!is_writable($htaccess_file)){
			wp_send_json_error(__('.htaccess file is not writable so the ', 'siteseo-pro'));
		}

		// Backup .htaccess file
		if(!copy($htaccess_file, $backup_file)){
			wp_send_json_error(__('Failed to create backup of .htaccess file.', 'siteseo-pro'));
		}

		// Update the .htaccess file
		if(file_put_contents($htaccess_file, $htaccess_rules) === false){
			wp_send_json_error(__('Failed to update .htaccess file.', 'siteseo-pro'));
		}

		$response = wp_remote_get(site_url());
		$response_code = wp_remote_retrieve_response_code($response);
		
		// Restore the backup if something goes wrong.
		if($response_code > 299){
			copy($backup_file, $htaccess_file);
			wp_send_json_error(__('There was a syntax error in the htaccess rules you provided as the response to your website with the new htaccess gave response code of', 'siteseo-pro') . ' ' . $response_code);
		}

		wp_send_json_success(__('Successfully updated .htaccess file', 'siteseo-pro'));
	}
	
	static function update_robots(){
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'siteseo-pro'));
		}
		
		$robots_txt = '';
		if(!empty($_POST['robots'])){
			$robots_txt = sanitize_textarea_field(wp_unslash($_POST['robots']));
		}
		
		// Updating the physical robots
		if(file_exists(ABSPATH . 'robots.txt')){
			if(!is_writable(ABSPATH . 'robots.txt')){
				wp_send_json_error(__('robots.txt file is not writable', 'siteseo-pro'));
			}
			
			if(file_put_contents(ABSPATH . 'robots.txt', $robots_txt)) {
				wp_send_json_success(__('Successfully updated the physical robots.txt file', 'siteseo-pro'));
			}
			
			wp_send_json_error(__('Unable to update the robots.txt file', 'siteseo-pro'));
		}

		// Updating option for virtual robots
		if(update_option('siteseo_pro_virtual_robots_txt', $robots_txt)) {
			wp_send_json_success(__('Successfully updated the virtual robots.txt rules', 'siteseo-pro'));
		}
		
		wp_send_json_error(__('Unable to update the virtual robots.txt rules', 'siteseo-pro'));
	}
	
	static function export_csv_redirect_logs(){
		
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required permission to edit this file.', 'siteseo-pro'));
		}
		
		$file_name = 'siteseo-redirect-data-' . current_time('Y-m-d') . '.csv';
		
		global $wpdb;

		$results = $wpdb->get_results("SELECT url, ip_address, timestamp, user_agent, referer, hit_count FROM {$wpdb->prefix}siteseo_redirect_logs ORDER BY timestamp DESC", ARRAY_A);
		
		if(empty($results)){
			wp_send_json_error('No data found');
			exit;
		}
		
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $file_name . '"');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		$output = fopen('php://output', 'w');
		
		// Add headers
		fputcsv($output, array('URL', 'IP Address', 'Timestamp', 'User Agent', 'Referer', 'Hit Count'));
		
		foreach($results as $row){
			fputcsv($output, $row);
		}
		
		fclose($output);
		exit;
	}
	
	static function redirect_clear_all_logs(){
		
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have permission to clear logs.', 'siteseo-pro'));
		}
		
		global $wpdb;
		$table_name = $wpdb->prefix . "siteseo_redirect_logs";
		
		$result = $wpdb->query("TRUNCATE TABLE $table_name");

		if($result !== false){
			wp_send_json_success(__('All logs have been cleared.', 'siteseo-pro'));
		}
		
		wp_send_json_error(__('Failed to clear logs.', 'siteseo-pro'));
	}
	
	static function delete_selected_log(){
		global $wpdb;
		
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have permission to clear logs.', 'siteseo-pro'));
		}
		
		$selected_ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
		
		if(empty($selected_ids)){
			wp_send_json_error('No logs selected');
			return;
		}
		
		$placeholders = array_fill(0, count($selected_ids), '%d');
		$placeholders_string = implode(',', $placeholders);
				
		// Delete
		$result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}siteseo_redirect_logs WHERE id IN ($placeholders_string)", $selected_ids));
		
		if($result !== false){
			wp_send_json_success(array(
				'message' => 'Selected logs deleted successfully',
				'deleted_count' => $result
			));
		} else{
			wp_send_json_error('Failed to delete logs');
		}
	}
	
	static function delete_robots_txt(){
		
		check_ajax_referer('siteseo_pro_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have permission for delete file.', 'siteseo-pro'));
		}
		
		$robots_txt_path = ABSPATH . 'robots.txt';
		
		if(file_exists($robots_txt_path)){
			if(!unlink($robots_txt_path)){
				wp_send_json_error(__('Failed to delete robots.txt file.', 'siteseo-pro'));
			}
		}
		
		wp_send_json_success();
	}
	
	// Version nag ajax
	static function version_notice(){
		check_admin_referer('siteseo_version_notice', 'security');

		if(!current_user_can('activate_plugins')){
			wp_send_json_error(__('You do not have required access to do this action', 'siteseo-pro'));
		}
		
		$type = '';
		if(!empty($_REQUEST['type'])){
			$type = sanitize_text_field(wp_unslash($_REQUEST['type']));
		}

		if(empty($type)){
			wp_send_json_error(__('Unknown version difference type', 'siteseo-pro'));
		}
		
		update_option('siteseo_version_'. $type .'_nag', time() + WEEK_IN_SECONDS);
		wp_send_json_success();
	}
	
	static function dismiss_expired_licenses(){
		check_admin_referer('siteseo_expiry_notice', 'security');

		if(!current_user_can('activate_plugins')){
			wp_send_json_error(__('You do not have required access to do this action', 'siteseo-pro'));
		}

		update_option('softaculous_expired_licenses', time());
		wp_send_json_success();
	}
}
