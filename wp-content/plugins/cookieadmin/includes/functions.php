<?php

if (!defined('ABSPATH')){
    exit;
}

/* function cookieadmin_died(){
	print_r(error_get_last());
}
register_shutdown_function('cookieadmin_died'); */

// Checks if we are to update ?
function cookieadmin_update_check(){
	global $wpdb;

	$current_version = get_option('cookieadmin_version');
	$version = (int) str_replace('.', '', $current_version);

	// No update required
	if($current_version == COOKIEADMIN_VERSION){
		return true;
	}

	// Save the new Version
	update_option('cookieadmin_version', COOKIEADMIN_VERSION);
	
}

function cookieadmin_load_plugin(){
	
	global $cookieadmin, $cookieadmin_settings;
	
	// Check if the installed version is outdated
	cookieadmin_update_check();
	
	///////////////////////////
	// Common loading
	///////////////////////////
	
	if(wp_doing_ajax()){
		add_action('wp_ajax_cookieadmin_ajax_handler', 'cookieadmin_ajax_handler');
		add_action('wp_ajax_nopriv_cookieadmin_ajax_handler', 'cookieadmin_ajax_handler');
	}
	
	///////////////////////////
	// Admin loading
	///////////////////////////
	
	if(is_admin()){
		return cookieadmin_load_plugin_admin();
	}
	
	///////////////////////////
	// Enduser loading
	///////////////////////////
	
	add_action('wp_enqueue_scripts', '\CookieAdmin\Enduser::enqueue_scripts');
	
	// Insert Cookie blocker in the head.
	//add_action('send_headers', '\CookieAdmin\Enduser::cookieadmin_block_cookie_init_php', 100);
	// add_action('init', '\CookieAdmin\Enduser::cookieadmin_block_cookie_head_js', 0);
	
	//add Cookie Banner to user page
	add_action('wp_footer', '\CookieAdmin\Enduser::cookieadmin_show_banner');
	
	add_filter('script_loader_tag', '\CookieAdmin\Enduser::check_if_cookies_allowed', 10, 3);
	
}

function cookieadmin_load_plugin_admin(){
	
	global $cookieadmin, $cookieadmin_settings;
	
	if(!is_admin() || !current_user_can('administrator')){
		return false;
	}
	
	add_action('admin_enqueue_scripts', '\CookieAdmin\Admin::enqueue_scripts');
	
	add_action('admin_menu', '\CookieAdmin\Admin::cookieadmin_plugin_menu');
	
}

function cookieadmin_ajax_handler(){
	
	$cookieadmin_fn = (!empty($_REQUEST['cookieadmin_act']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_act'])) : '');
	
	if(empty($cookieadmin_fn)){
		wp_send_json_error(array('message' => 'Action not posted'));
	}
	
	// Define a whitelist of allowed functions
	$user_allowed_actions = array();
	
	$admin_allowed_actions = array(
		'scan_cookies' => 'cookieadmin_scan_cookies',
		'cookieadmin-auto-categorize' => 'cookieadmin_auto_configure_cookies',
		'cookieadmin-edit-cookie' => 'cookieadmin_edit_cookies',
	);
	
	$general_actions = array(
		'categorize_cookies' => 'cookieadmin_categorize_cookies',
	);
	
	if(array_key_exists($cookieadmin_fn, $user_allowed_actions)){
		
		check_ajax_referer('cookieadmin_js_nonce', 'cookieadmin_security');
		header_remove('Set-Cookie');
		call_user_func('\CookieAdmin\Enduser::'.$user_allowed_actions[$cookieadmin_fn]);
		
	}elseif(array_key_exists($cookieadmin_fn, $admin_allowed_actions)){
		
		check_ajax_referer('cookieadmin_admin_js_nonce', 'cookieadmin_security');
	 
		if(!current_user_can('administrator')){
			wp_send_json_error(array('message' => 'Sorry, but you do not have permissions to perform this action'));
		}
		
		call_user_func('\CookieAdmin\Admin::'.$admin_allowed_actions[$cookieadmin_fn]);
		
	}elseif(array_key_exists($cookieadmin_fn, $general_actions)){
		
		check_ajax_referer('cookieadmin_js_nonce', 'cookieadmin_security');
		header_remove('Set-Cookie');
		call_user_func($general_actions[$cookieadmin_fn]);
		
	}else{
		wp_send_json_error(array('message' => 'Unauthorized action'));
	}
	
}

// Load policies from the file and database and merge them.
function cookieadmin_load_policy(){
	
	$policy = get_option('cookieadmin_consent_settings', array());
	
	if(empty($policy) || !is_array($policy)){
		$policy = array();
	}
	
	if(!file_exists(COOKIEADMIN_DIR.'assets/cookie/policy.json')){
		return $policy;
	}
	
	$j_policy = file_get_contents(COOKIEADMIN_DIR.'assets/cookie/policy.json');
	
	$j_policy = json_decode($j_policy, true);
	
	if(empty($j_policy) || !is_array($j_policy)){
		return $policy;
	}

	return array_replace_recursive($j_policy, $policy);
}


//Loads consent data from file
function cookieadmin_load_consent_template($policy, $view){
	
	if(!file_exists(COOKIEADMIN_DIR.'assets/cookie/template.json')){
		return false;
	}
	
	$content = json_decode(file_get_contents(COOKIEADMIN_DIR.'assets/cookie/template.json'), true);
	
	if(empty($content)){
		return false;
	}
	
	$template = array();
	$template[$view] = $content["cookieadmin_layout"][$policy["cookieadmin_layout"]];
	$template[$view] .= $content["cookieadmin_modal"][$policy["cookieadmin_modal"]];
	$template[$view] .= str_replace( '[[plugin_url]]', esc_url(COOKIEADMIN_PLUGIN_URL), $content["cookieadmin_reconsent"] );
	
	return $template;
	
}

// Still in progress| No use for now.
function cookieadmin_compare_consent_id($consent_id) {
	
	if (strlen($consent_id) !== 32) {
        return false;
    }
	
	// Split into random part and signature
    $random_part = substr($consent_id, 0, 16);
    $provided_signature = substr($consent_id, 16, 16);
    
    // Recompute the HMAC
    $expected_hmac = hash_hmac('sha256', $random_part . $domain, $secret_key);
    $expected_signature = substr($expected_hmac, 0, 16);
	
    return hash_equals($provided_signature, $expected_signature);
}

function cookieadmin_r_print($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function cookieadmin_load_cookies_csv($cookie_names = array(), $like = 0) {
    global $wpdb;
		
	$cookies_list = [];
	
    $csv_file = COOKIEADMIN_DIR . 'assets/open-cookie-database/list.csv';
	
    // Check if file exists
    if ( ! file_exists( $csv_file ) ) {
		return new WP_Error( 'csv_missing', 'The cookie CSV file is missing: '.$csv_file );
    }
    
    if ( ( $handle = fopen( $csv_file, 'r' ) ) !== FALSE ) {
		
		$cookies_list = [];

        $headers = fgetcsv( $handle, 10000, ",", "\"", "\\" );

        while ( ( $data = fgetcsv( $handle, 10000, ",", "\"", "\\" ) ) !== FALSE ) {
            // 0: cookie_id, 1: Platform, 2: Category, 3: Cookie / Data Key name,
            // 4: Domain, 5: Description, 6: Retention period, 7: Data Controller,
            // 8: User Privacy & GDPR Rights Portals, 9: Wildcard match

            $cookie_id    = isset( $data[0] ) ? trim( $data[0] ) : '';
            $cookie_name  = isset( $data[3] ) ? trim( $data[3] ) : '';
            $platform     = isset( $data[1] ) ? trim( $data[1] ) : '';
            $category     = isset( $data[2] ) ? trim( $data[2] ) : '';
            $domain       = isset( $data[4] ) ? trim( $data[4] ) : '';
            $description  = isset( $data[5] ) ? trim( $data[5] ) : '';
            $retention    = isset( $data[6] ) ? trim( $data[6] ) : '';
            $wildcard     = isset( $data[9] ) ? (int) $data[9] : 0;
            $patterns     = isset( $data[10] ) ? trim($data[10]) : '';

            if ( empty( $cookie_id ) || empty( $cookie_name ) ) {
                continue;
            }
			
			if(!empty($cookie_names)){
				
				if(!empty($like)){
					
					$matched = 0;
					foreach($cookie_names as $prefix){
						if (substr($cookie_name, 0, strlen($prefix)) === $prefix) {
							$matched = 1;
							break;
						}
					}
					
					if(empty($matched)){
						continue;
					}
					
				}else{
					if(!in_array($cookie_name, $cookie_names)){
						continue;
					}
				}
			}

			// Add the row to the current batch
			$cookies_list[] = [
				'cookie_id' => $cookie_id,
				'cookie_name' => $cookie_name,
				'platform' => $platform,
				'category' => $category,
				'domain' => $domain,
				'description' => $description,
				'retention' => $retention,
				'wildcard' => $wildcard,
				'patterns' => $patterns
			];
        }

        fclose( $handle );
		
    } else {
		return new WP_Error( 'csv_open_fail', 'Failed to open Cookies CSV file: '.$csv_file );
    }
	
    return $cookies_list;
}

function cookieadmin_categorize_cookies($cookies = []){
	
	global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg, $wpdb;
	
	if(!empty($_REQUEST['cookieadmin_cookies'])){
		
		$raw_cookies = json_decode( wp_unslash( $_REQUEST['cookieadmin_cookies'] ), true );

		if ( is_array( $raw_cookies ) ) {
			$sanitized_cookies = [];

			array_walk( $raw_cookies, function( $value, $key ) use ( &$sanitized_cookies ) {
				$sanitized_key = sanitize_key( $key );
				$sanitized_cookies[ $sanitized_key ] = sanitize_text_field($value);
			} );
			
			unset($raw_cookies);
		}
	}else{
		$sanitized_cookies = $cookies;
	}

	
	if(empty($sanitized_cookies)){
		return [
            'success' => false,
            'data'    => null,
            'error'   => 'Please provide valid cookie names.',
        ];
	}
	
	$cookies_info = cookieadmin_load_cookies_csv(array_keys($sanitized_cookies));
	
	if(empty($cookies_info) || is_wp_error($cookies_info)){
		return [
	            'success' => false,
	            'data'    => null,
	            'error'   => 'Failed to load Cookies list',
	        ];
	}
	
	foreach($cookies_info as $info){
		$sanitized_cookies[$info['cookie_name']]['source'] = !empty($info['domain']) ? $info['domain'] : "unknown";
		$sanitized_cookies[$info['cookie_name']]['category'] = !empty($info['category']) ? strtolower($info['category']) : "un_c";
		$sanitized_cookies[$info['cookie_name']]['description'] = !empty($info['description']) ? $info['description'] : "unknown";
		$sanitized_cookies[$info['cookie_name']]['duration'] = !empty($info['retention']) ? $info['retention'] : "unknown";
		$sanitized_cookies[$info['cookie_name']]['platform'] = !empty($info['platform']) ? $info['platform'] : "unknown";;
	}
	
	if(wp_doing_ajax()){
		wp_send_json_success($sanitized_cookies);
	}
	
	return $sanitized_cookies;
}