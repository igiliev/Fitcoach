<?php

namespace CookieAdmin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Enduser{
	
	static $http_cookies = array();
	static $categorized_cookies = array();
	
	static function enqueue_scripts(){
		global $wpdb;
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');	
		$policy = cookieadmin_load_policy();
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		//cookieadmin_r_print($view);
		//cookieadmin_r_print($policy);
		
		if(!empty($policy) && !empty($view)){
		
			wp_enqueue_style('cookieadmin-style', COOKIEADMIN_PLUGIN_URL . 'assets/css/cookie.css', [], COOKIEADMIN_VERSION);
			
			wp_enqueue_script('cookieadmin_js', COOKIEADMIN_PLUGIN_URL . 'assets/js/consent.js', [], COOKIEADMIN_VERSION, 'async');
		
			$policy[$view]['ajax_url'] = admin_url('admin-ajax.php');
			$policy[$view]['nonce'] = wp_create_nonce('cookieadmin_js_nonce');
			$policy[$view]['http_cookies'] = self::$http_cookies;
			$policy[$view]['home_url'] = home_url();
			$policy[$view]['plugin_url'] = COOKIEADMIN_URL;
			$policy[$view]['is_pro'] = (defined('COOKIEADMIN_PREMIUM') ? COOKIEADMIN_PREMIUM : 0);
			// cookieadmin_r_print($policy);die();
			
			$rows = $wpdb->get_results("SELECT cookie_name, category, expires, description, patterns FROM {$table_name}");
			$cookie_data = array();

			foreach ($rows as $row) {
				$cookie_data[$row->cookie_name] = $row;
			}
			
			$policy[$view]['categorized_cookies'] = self::$categorized_cookies = $cookie_data;
			
			wp_localize_script('cookieadmin_js', 'cookieadmin_policy', $policy[$view]);
		}
	}

	/* static function cookieadmin_block_cookie_init_php(){
		
		//New - To catch, remove and send cookies in WP enqueue
		$http_cookies = array();
		$headers = headers_list();

		foreach($headers as $header) {
			
			if (stripos(trim($header), 'Set-Cookie:') === 0) {
				$header = trim(substr($header, strlen('Set-Cookie:')));
				$name = trim(explode('=', $header)[0]);
				$http_cookies[$name]['string'] = trim($header);
				setcookie($name, '', time() - 999999, '/');
			}
		}

		$http_cookies['cookieadmin_consent'] = ["string" => "cookieadmin_consent=CookieAdmin Cookie Initialization"];
		
		self::$http_cookies = $http_cookies;
	} */
	
	static function check_if_cookies_allowed($tag, $handle, $src){

		$cookieadmin_consent = isset($_COOKIE['cookieadmin_consent'])
							? json_decode(wp_unslash($_COOKIE['cookieadmin_consent']), true)
							: [];

		array_walk( $cookieadmin_consent, function( $value, $key ) use ( &$cookieadmin_consent ) {
			$sanitized_key = sanitize_key( $key );
			$cookieadmin_consent[ $sanitized_key ] = sanitize_text_field($value);
		} );
		
		foreach (self::$categorized_cookies as $item) {
			$category = strtolower($item->category);
			$patterns = json_decode($item->patterns, true);
			
			if (!empty($patterns) && !empty($category)) {
				foreach ($patterns as $pattern) {
					if (strpos($src, $pattern) !== false) {
						
						if ( $category !== 'necessary' && 
							(empty($cookieadmin_consent) || 
							(!empty($cookieadmin_consent[$category]) && $cookieadmin_consent[$category] == 'false') || 
							$cookieadmin_consent['reject'] == 'true')
							) {
							
							// User has NOT consented -> block the script

							// Option 1 - completely remove script:
							// return '';

							// Option 2 - transform to type="text/plain"
							$tag = str_replace(
								'<script ',
								'<script type="text/plain" data-cookieadmin-category="' . esc_attr($category) . '" ',
								$tag
							);
							
							return $tag;
						}
					}
				}
			}
		}

		return $tag;
	}
	
	static function cookieadmin_show_banner(){
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');	
		$policy = cookieadmin_load_policy();
		
		$allowed_tags = wp_kses_allowed_html( 'post' );

		// Add input tag for cookie consent form
		$allowed_tags['input'] = array(
			'type'    => true,
			'name'    => true,
			'value'   => true,
			'class'   => true,
			'id'      => true,
			'checked' => true,
			'disabled' => true,
			'placeholder' => true,
		);
		
		$templates = wp_kses(implode("", cookieadmin_load_consent_template($policy[$view], $view)), $allowed_tags);
		
		// var_dump($policy[$view]);
		echo $templates;
	}

	static function cookieadmin_table_exists($table_name) {
		global $wpdb;
		
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
		
		return $wpdb->get_var($query) === $table_name;
	}

}

