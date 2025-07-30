<?php
/*
Plugin Name: Pagelayer Pro
Plugin URI: https://pagelayer.com/
Description: Pagelayer is a WordPress page builder plugin. Its very easy to use and very light on the browser.
Version: 2.0.1
Author: Pagelayer Team
Author URI: https://pagelayer.com/
Text Domain: pagelayer-pro
*/

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

// If PAGELAYER_PRO_VERSION exists then the plugin is loaded already !
if(defined('PAGELAYER_PRO_VERSION')) {
	return;
}

define('PAGELAYER_PRO_FILE', __FILE__);
define('PAGELAYER_PRO_URL', plugins_url('', PAGELAYER_PRO_FILE));
define('PAGELAYER_PRO_API', 'https://api.pagelayer.com/');
define('PAGELAYER_PRO_VERSION', '2.0.1');
define('PAGELAYER_PRO_DIR', dirname(PAGELAYER_PRO_FILE));
define('PAGELAYER_PRO_BASE', plugin_basename(PAGELAYER_PRO_FILE));
define('PAGELAYER_PRO_SLUG', 'pagelayer-pro');
define('PAGELAYER_PRO_CSS', PAGELAYER_PRO_URL.'/css');
define('PAGELAYER_PRO_JS', PAGELAYER_PRO_URL.'/js');
define('PAGELAYER_PRO_DEV', file_exists(dirname(__FILE__).'/dev.php') ? 1 : 0);

include_once(PAGELAYER_PRO_DIR.'/main/functions.php');

$_tmp_plugins = get_option('active_plugins');
$_pl_version = get_option('pagelayer_version');

if(
	!defined('SITEPAD') && (
	!(in_array('pagelayer/pagelayer.php', $_tmp_plugins) || 
	pagelayer_pro_is_network_active('pagelayer')) || 
	!file_exists(WP_PLUGIN_DIR . '/pagelayer/pagelayer.php') || 
	(!empty($_pl_version) && version_compare($_pl_version, '1.8.7', '<')))
){
	include_once(PAGELAYER_PRO_DIR .'/main/pagelayer-init.php');
	return;
}

// Load this after active free version
define('PAGELAYER_PREMIUM', plugin_basename(__FILE__));

include_once(dirname(__FILE__).'/init.php');
