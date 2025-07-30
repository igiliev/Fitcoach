<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

delete_option('cookieadmin_pro_version');
delete_option('cookieadmin_version_free_nag');
delete_option('cookieadmin_version_pro_nag');
delete_option('cookieadmin_free_installed');

$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix . 'cookieadmin_consents');
