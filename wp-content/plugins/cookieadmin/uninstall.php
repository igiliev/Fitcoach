<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

delete_option('cookieadmin_version');
delete_option('cookieadmin_law');
delete_option('cookieadmin_consent_settings');

$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix . 'cookieadmin_cookies');