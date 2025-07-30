<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Database{
	
	static $wpdb = '';
	static $consent_table = '';
	
	static function activate(){
		
		global $wpdb;
		
		self::$wpdb = $wpdb;
		self::$consent_table = esc_sql(self::$wpdb->prefix . 'cookieadmin_consents');
		self::cookieadmin_create_tables();
	}
	
	static function cookieadmin_create_tables() {
		
		$charset_collate = self::$wpdb->get_charset_collate();
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		//Create Consent table
		$sql = "CREATE TABLE IF NOT EXISTS ".self::$consent_table." (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			consent_id VARCHAR(128) NOT NULL UNIQUE,  -- Designed to store up to 128 characters for future expansion
			user_ip VARBINARY(16) DEFAULT NULL,         -- For storing anonymized IP (IPv4 or IPv6)
			consent_time INT NOT NULL,
			country VARCHAR(150) DEFAULT NULL,          -- Full country name
			browser TEXT DEFAULT NULL,                  -- Browser User agent string
			domain VARCHAR(255) DEFAULT NULL,           -- Domain from which consent was submitted
			consent_status VARCHAR(50) NOT NULL         -- Stores 'accepted', 'rejected', 'partially accepted', etc.
		) {$charset_collate};";
		
		dbDelta($sql);
	}
}


