<?php
/*
* CookieAdmin
* https://cookieadmin.net
* (c) Softaculous Team
*/

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

define('COOKIEADMIN_BASE', plugin_basename(COOKIEADMIN_FILE));
define('COOKIEADMIN_DIR', plugin_dir_path(__FILE__));
define('COOKIEADMIN_VERSION', '1.0.2');
define('COOKIEADMIN_URL', plugins_url('', COOKIEADMIN_FILE));
define('COOKIEADMIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COOKIEADMIN_PRO_URL', 'https://cookieadmin.net/pricing?from=plugin');
define('COOKIEADMIN_WWW_URL', 'https://cookieadmin.net/');

include_once(COOKIEADMIN_DIR.'includes/functions.php');

function cookieadmin_autoloader($class){
	
	if(!preg_match('/^CookieAdmin\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	if(strpos($class, 'CookieAdmin\lib') === 0){
		if(file_exists(COOKIEADMIN_DIR.$m[1].'.php')){
			include_once(COOKIEADMIN_DIR.$m[1].'.php');
		}
	}

	// For Pro
	if(file_exists(COOKIEADMIN_DIR.'includes/'.strtolower($m[1]).'.php')){
		include_once(COOKIEADMIN_DIR.'includes/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register(__NAMESPACE__.'\cookieadmin_autoloader');


if(!class_exists('CookieAdmin')){
#[\AllowDynamicProperties]
class CookieAdmin{
}
}

add_action('plugins_loaded', 'cookieadmin_load_plugin');