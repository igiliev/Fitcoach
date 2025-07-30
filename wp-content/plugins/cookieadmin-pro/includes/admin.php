<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Admin{
	
	static function enqueue_scripts(){
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		$is_admin_page = basename(parse_url($request_uri, PHP_URL_PATH));
		if(!is_admin() || empty($_GET['page']) || !in_array($_GET['page'], array('cookieadmin-consent-logs')) || $is_admin_page != 'admin.php'){
			return false;
		}
		
		// Add condition to load only on our settings page
		//Consent Page Css
		wp_enqueue_style('cookieadmin-pro-style', COOKIEADMIN_PRO_PLUGIN_URL . 'assets/css/cookie.css', [], COOKIEADMIN_PRO_VERSION);
		
		wp_enqueue_script('cookieadmin_pro_js', COOKIEADMIN_PRO_PLUGIN_URL . 'assets/js/cookie.js', [], COOKIEADMIN_PRO_VERSION);
	
		$policy['admin_url'] = admin_url('admin-ajax.php');
		$policy['cookieadmin_nonce'] = wp_create_nonce('cookieadmin_pro_admin_js_nonce');
		//cookieadmin_r_print($policy);die();
		
		wp_localize_script('cookieadmin_pro_js', 'cookieadmin_pro_policy', $policy);
	}
	
	//Add Main Menu
	static function plugin_menu(){
		
	}

	static function show_settings($title = 'CookieAdmin Pro'){
		
	}

	static function cookieadmin_pro_table_exists($table_name) {
		global $wpdb;
		
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
		
		return $wpdb->get_var($query) === $table_name;
	}

	static function show_consent_logs(){
		
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg;
		
		\CookieAdmin\Admin::header_theme(__('Consent Logs', 'cookieadmin-pro'));
		
		$log_data  = \CookieAdminPro\Admin::get_consent_logs();
		$consent_logs = (!empty($log_data['logs']) ? $log_data['logs'] : array());
		
		echo '
			
		<div class="cookieadmin_pro_consent-wrap" style="max-width: 85vw;">
			<form action="" method="post">

			<div class="cookieadmin_consent-contents">
				<div class="cookieadmin_consent">
					
					<div class="contents cookieadmin_manager">
						
						<div class="cookieadmin-setting cookieadmin-manager-consent-logs">
							<label class="cookieadmin-title"></label>
							<div class="cookieadmin-setting-contents cookieadmin-consent-logs">
								<input type="button" class="cookieadmin-btn cookieadmin-btn-primary cookieadmin-consent-logs-export" value="Export CSV">
							</div>
							
							<div class="cookieadmin-manager-scan-result">
								<table class="cookieadmin-table cookieadmin-consent-logs-result">
								<thead>
									<tr>
										<th width="30%">'.esc_html__('Consent Id', 'cookieadmin-pro').'</th>
										<th width="20%">'.esc_html__('Status', 'cookieadmin-pro').'</th>
										<th>'.esc_html__('Country', 'cookieadmin-pro').'</th>
										<th>'.esc_html__('User IP (Anonymized)', 'cookieadmin-pro').'</th>
										<th>'.esc_html__('Time', 'cookieadmin-pro').'</th>
									</tr>
								</thead>
								<tbody>';
									
									if(!empty($consent_logs)){
										foreach ($consent_logs as $log){
											
											$status_badge = 'warning';
											if(strtolower($log['consent_status_raw']) == 'accept'){
												$status_badge = 'success';
											}elseif(strtolower($log['consent_status_raw']) == 'reject'){
												$status_badge = 'danger';
											}
											
											echo '
											<tr>
												<td>'.esc_html($log['consent_id']).'</td>
												<td><span class="cookieadmin-badge cookieadmin-'.$status_badge.'">'.esc_html($log['consent_status']).'</span></td>
												<td>'.(!empty($log['country']) ? esc_html($log['country']) : '—').'</td>
												<td>'.esc_html($log['user_ip']).'</td>
												<td>'.esc_html($log['consent_time']).'</td>
											</tr>';
										}
									}else{
										echo '
										<tr>
											<td colspan="4">'.esc_html__('No consent logs recorded yet!', 'cookieadmin-pro').'</td>
										</tr>';
									}
									
									echo '
								</tbody>
								</table>
							</div>';
							
							if(!empty($consent_logs)){
								echo '
								<div class="cookieadmin-consent-logs-pagination" style="text-align:right;">
										'.esc_html__('Displaying', 'cookieadmin-pro').' <span class="displaying-num">'.esc_html($log_data['min_items'].' - '.$log_data['max_items']).'</span> '.esc_html__('of', 'cookieadmin-pro').' <span class="max-num">'.esc_html($log_data['total_logs']).'</span> '.esc_html__('item(s)', 'cookieadmin-pro').'
										&nbsp;
										<!-- First Page Consent logs -->
										<a class="first-page cookieadmin-logs-paginate" id="cookieadmin-first-consent-logs" href="javascript:void(0)">
										<span aria-hidden="true">«</span>
										</a>
										&nbsp;
										<!-- Previous Page Consent logs -->
										<a class="prev-page cookieadmin-logs-paginate" id="cookieadmin-previous-consent-logs" href="javascript:void(0)">
										<span aria-hidden="true">‹</span>
										</a>
										&nbsp;
										<!-- Current Page logs -->
										<span class="paging-input">
											<label for="current-page-selector" class="screen-reader-text">Current Page</label>
											<input class="current-page" id="current-page-selector" name="current-page-selector" value="'.(!empty($log_data['current_page']) ? esc_attr($log_data['current_page']) : '').'" size="3"  aria-describedby="table-paging" type="text" style="text-align: center;">
											<span class="tablenav-paging-text"> of 
												<span class="total-pages">'.esc_html($log_data['total_pages']).'</span>
											</span>
										</span>
										&nbsp;
										<!-- Next Page Consent Logs -->
										<a class="next-page cookieadmin-logs-paginate"  id="cookieadmin-next-consent-logs" href="javascript:void(0)">
											<span aria-hidden="true">›</span>
										</a>
										&nbsp;
										<!-- Last Page Consent logs -->
										<a class="last-page cookieadmin-logs-paginate" 
										id="cookieadmin-last-consent-logs" href="javascript:void(0)">
											<span aria-hidden="true">»</span>
										</a>
										&nbsp;
								</div>';
							}
						echo '
						</div>
					</div>
				</div>';
				
				wp_nonce_field('cookieadmin_pro_admin_nonce', 'cookieadmin_pro_security');
				
				echo '<br/>
				<br/>
			</div>
			</form>
		</div>';
		
		\CookieAdmin\Admin::footer_theme();
	
	}

	//Load Consent logs data from the database
	static function get_consent_logs(){
		
		global $wpdb;
		
		if($_POST && count($_POST) > 0){
			$nonce_slug = (wp_doing_ajax() ? 'cookieadmin_pro_admin_js_nonce' : 'cookieadmin_pro_admin_nonce');
			check_admin_referer($nonce_slug, 'cookieadmin_pro_security');
		}
	 
		if(!current_user_can('administrator')){
			wp_send_json_error(array('message' => 'Sorry, but you do not have permissions to perform this action'));
		}
		
		$num_items = 0;
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_consents');
		$current_page = isset($_POST['current_page']) ? intval($_POST['current_page']) : 1;

		if (!\CookieAdminPro\Admin::cookieadmin_pro_table_exists($table_name)) {
			// wp_send_json_error(['message' => 'Table does not exist']);
			return array();
		}
		
		// Get total number of logs
		$total_consent_logs = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");	
		$logs_per_page = 25;

		// Calculate max pages
		$max_page = ceil($total_consent_logs / $logs_per_page);

		// Ensure current page is within valid range
		if ($current_page > $max_page) {
			$current_page = $max_page;
		} elseif ($current_page < 1) {
			$current_page = 1;
		}

		// Calculate pagination offset
		$offset = ($current_page - 1) * $logs_per_page;
		
		// Fetch paginated logs
		$consent_logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
				$logs_per_page,
				$offset
			),
			ARRAY_A
		);
		
		if(!empty($consent_logs)){
			
			foreach($consent_logs as $lk => $log){
				
				if(!empty($log['consent_status'])){
					$_consent_status = json_decode($log['consent_status'], true)[0];
					
					if($_consent_status == 'accept'){
						$consent_logs[$lk]['consent_status_raw'] = 'accept';
						$consent_logs[$lk]['consent_status'] = __('Accepted', 'cookieadmin-pro');
					}elseif($_consent_status == 'reject'){
						$consent_logs[$lk]['consent_status_raw'] = 'reject';
						$consent_logs[$lk]['consent_status'] = __('Rejected', 'cookieadmin-pro');
					}else{
						$consent_logs[$lk]['consent_status_raw'] = 'partially_accepted';
						$consent_logs[$lk]['consent_status'] = __('Partially Accepted', 'cookieadmin-pro');
					}
				}
				
				if(!empty($log['consent_time'])){
					$consent_logs[$lk]['consent_time'] = cookieadmin_pro_human_readable_time($log['consent_time']);
				}
			
				if(!empty($log['user_ip'])){
					$consent_logs[$lk]['user_ip'] = inet_ntop($log['user_ip']);
				}
			}
			
			$num_items = count($consent_logs);
		}
		
		$min_items = $offset + 1;
		$max_items = $min_items + ($num_items - 1);
		
		$return = [
				'logs' => $consent_logs,
				'total_logs' => $total_consent_logs,
				'logs_per_page' => $logs_per_page,
				'current_page' => $current_page,
				'total_pages' => $max_page,
				'min_items' => $min_items,
				'max_items' => $max_items
			];
		
		// Return logs as JSON response
		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_send_json_success($return);
		}
		
		// Return paginated data
		return $return;
	}

	// Export Consent Logs from the Database
	static function export_logs() {
		global $wpdb;
		
		$cookieadmin_export_type = !empty($_REQUEST['cookieadmin_export_type']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_export_type'])) : '';
		
		if(!empty($cookieadmin_export_type)){
			if($cookieadmin_export_type == 'consent_logs'){

				$table_name = esc_sql($wpdb->prefix . 'cookieadmin_consents');
				
				//First will check if the table in the database exists or not?
				if(!self::cookieadmin_pro_table_exists($table_name)){
					wp_send_json_error(['message' => 'Table does not exists']);
				}
				
				$logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
				$filename = 'cookieadmin-consent-logs';
			}
		}
		
		if(empty($logs)){
			echo -1;
			echo __('No data to export', 'cookieadmin-pro');
			wp_die();
		}
		
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename.'.csv');
		
		$allowed_fields = array('consent_id' => 'Consent Id', 'consent_status' => 'Consent Status', 'country' => 'Country', 'user_ip' => 'User IP (Anonymized)', 'consent_time' => 'Consent Time');

		$file = fopen("php://output","w");
		
		fputcsv($file, array_values($allowed_fields));
		
		foreach($logs as $ik => $log){
				
			if(!empty($log['consent_status'])){
				$_consent_status = json_decode($log['consent_status'], true)[0];
				if($_consent_status == 'accept'){
					$log['consent_status'] = __('Accepted', 'cookieadmin-pro');
				}elseif($_consent_status == 'reject'){
					$log['consent_status'] = __('Rejected', 'cookieadmin-pro');
				}else{
					$log['consent_status'] = __('Partially Accepted', 'cookieadmin-pro');
				}
			}
			
			if(!empty($log['consent_time'])){
				$log['consent_time'] = wp_date('M j Y g:i A T', $log['consent_time']);
			}
			
			if(!empty($log['user_ip'])){
				$log['user_ip'] = inet_ntop($log['user_ip']);
			}
			
			$log['country'] = (!empty($log['country']) ? $log['country'] : '—');
			
			$row = array();
			foreach($allowed_fields as $ak => $av){
				$row[$ak] = $log[$ak];
			}
			
			fputcsv($file, $row);
		}

		fclose($file);
		
		wp_die();
		
	}

	function version_notice(){
		
		$type = '';
		if(!empty($_REQUEST['type'])){
			$type = sanitize_text_field(wp_unslash($_REQUEST['type']));
		}

		if(empty($type)){
			wp_send_json_error(__('Unknow version difference type', 'cookieadmin-pro'));
		}
		
		update_option('cookieadmin_version_'. $type .'_nag', time() + WEEK_IN_SECONDS);
		wp_send_json_success();
	}

	function dismiss_expired_licenses(){

		update_option('softaculous_expired_licenses', time());
		wp_send_json_success();
	}
	
}

