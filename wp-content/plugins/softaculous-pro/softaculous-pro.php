<?php
/*
Plugin Name: SoftWP
Plugin URI: https://softwp.net/
Description: SoftWP plugin helps users get familiar with WordPress and build a functional website with a seamless onboarding process and Softaculous AI to help build content. Softaculous also offers an assistant which provides tour of essential aspects of WordPress for the user to understand the functionality.
Version: 2.2.3
Author: Softaculous
Author URI: https://www.softaculous.com
License: LGPL v2.1
License URI: https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html
Text Domain: softaculous-pro
*/

/*
 * This file belongs to the softaculous plugin.
 *
 * (c) Softaculous <sales@softaculous.com>
 *
 * You can view the LICENSE file that was distributed with this source code
 * for copywright and license information.
 */

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

if(defined('SOFTACULOUS_PRO_VERSION')) {
	return;
}

define('SOFTACULOUS_PRO_FILE', __FILE__);
define('SOFTACULOUS_PRO_DIR', dirname(__FILE__));
define('SOFTACULOUS_PRO_VERSION', '2.2.3');
define('SOFTACULOUS_PRO_BASE', 'softaculous-pro/softaculous-pro.php');
define('SOFTACULOUS_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SOFTACULOUS_PRO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SOFTACULOUS_PRO_WWW_URL', 'https://www.softaculous.com/');
define('SOFTACULOUS_PRO_AI_API', 'https://s2.softaculous.com/a/softai/ai.php');
define('SOFTACULOUS_PRO_URL', 'https://www.softaculous.com/');
define('SOFTACULOUS_PRO_PFX_API', 'https://a.softaculous.com/popularfx/');
define('SOFTACULOUS_PRO_PAGELAYER_API', 'https://api.pagelayer.com/');
define('SOFTACULOUS_PRO_BUY', 'https://www.softaculous.com/clients?ca=softwp_buy');
define('SOFTACULOUS_PRO_AI_BUY', 'https://www.softaculous.com/clients?ca=softai_buy');

function softaculous_pro_autoloader($class){
	
	if(!preg_match('/^SoftWP\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	if(strpos($class, 'SoftWP\lib') === 0){
		if(file_exists(SOFTACULOUS_PRO_DIR.'/'.$m[1].'.php')){
			include_once(SOFTACULOUS_PRO_DIR.'/'.$m[1].'.php');
		}
	}

	// For Pro
	if(file_exists(SOFTACULOUS_PRO_DIR.'/main/'.strtolower($m[1]).'.php')){
		include_once(SOFTACULOUS_PRO_DIR.'/main/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register(__NAMESPACE__.'\softaculous_pro_autoloader');


if(!class_exists('SoftWP')){
#[\AllowDynamicProperties]
class SoftWP{
}
}

include_once SOFTACULOUS_PRO_PLUGIN_PATH . 'main/functions.php';

// Activation Hook
register_activation_hook(__FILE__, 'softaculous_pro_activation_hook');

// De-activation Hook
register_deactivation_hook(__FILE__, 'softaculous_pro_deactivation_hook');

// Uninstall hook
register_uninstall_hook(__FILE__, 'softaculous_pro_uninstall_hook');

add_action('plugins_loaded', 'softaculous_pro_load_plugin');

//we need to load textdomain for language translation
function softaculous_pro_load_textdomain() {
    load_plugin_textdomain( 'softaculous-pro', false, dirname(plugin_basename( __FILE__ ) ) . '/languages/');
}

add_action( 'plugins_loaded', 'softaculous_pro_load_textdomain' );