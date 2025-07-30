<?php
/*
Plugin Name: CookieAdmin Pro
Plugin URI: https://cookieadmin.net
Description: CookieAdmin provides easy to configure cookie consent banner with GDPR and CCPA law support.
Version: 1.0.2
Author: Softaculous
Author URI: https://www.softaculous.com
License: LGPL v2.1
License URI: https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html
Text Domain: cookieadmin-pro
*/

/*
 * This file belongs to the CookieAdmin plugin.
 *
 * (c) Softaculous <sales@softaculous.com>
 *
 * You can view the LICENSE file that was distributed with this source code
 * for copywright and license information.
 */

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

// If COOKIEADMIN_PREMIUM exists then the plugin is loaded already !
if(defined('COOKIEADMIN_PREMIUM')) {
	return;
}

define('COOKIEADMIN_PRO_VERSION', '1.0.2');
define('COOKIEADMIN_PRO_DIR', plugin_dir_path(__FILE__));
define('COOKIEADMIN_API', 'https://api.cookieadmin.net/');
define('COOKIEADMIN_PRO_FILE', __FILE__);
define('COOKIEADMIN_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once(COOKIEADMIN_PRO_DIR.'includes/functions.php');

$cookieadmin_tmp_plugins = get_option('active_plugins', []);

if(
	!defined('SITEPAD') && (
	!(in_array('cookieadmin/cookieadmin.php', $cookieadmin_tmp_plugins) || 
	cookieadmin_pro_is_network_active('cookieadmin')) || 
	!file_exists(WP_PLUGIN_DIR . '/cookieadmin/cookieadmin.php'))
){
	include_once(COOKIEADMIN_PRO_DIR .'/includes/short-cirtuit.php');
	return;
}

define('COOKIEADMIN_PREMIUM', plugin_basename(__FILE__));

include_once(dirname(__FILE__).'/init.php');

register_activation_hook(__FILE__, 'cookieadmin_pro_activation');