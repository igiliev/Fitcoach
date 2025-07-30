<?php

//////////////////////////////////////////////////////////////
//===========================================================
// license.php
//===========================================================
// softaculous-pro
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit Gupta
// Date:	   12 Sep 2024
// Time:	   23:00 hrs
// Site:	   https://www.softaculous.com/
// ----------------------------------------------------------
// Please Read the Terms of use at http://softaculous.com/
// ----------------------------------------------------------
//===========================================================
// (c)softaculous Team
//===========================================================
//////////////////////////////////////////////////////////////

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

if(defined('SOFTACULOUS_PRO_VERSION')) {
	return;
}

function softaculous_pro_manage_plugins(){
	
	global $spro_error;
	
	softaculous_pro_header(0);
	
	include_once(SOFTACULOUS_PRO_PLUGIN_PATH.'main/onboarding.php');
	$spro_manage_features = spro_get_features_list();
	
	$manage_installed_plugins = get_plugins();
	
	if(!empty($softaculous_pro_error)){
		echo '<div id="message" class="error"><p>'.esc_html($softaculous_pro_error).'</p></div>';
	}
	
	if(!empty($softaculous_pro_msg)){
		echo '<div id="message" class="updated"><p>'.esc_html($softaculous_pro_msg).'</p></div>';
	}
	
	echo '
	<div class="spro-box-heading"><h3 class="smb-2 spb-1 sml-2">
		'.__('Manage Plugins', 'softaculous-pro').'</h3>
		<hr />
		<br />
	</div>
	
	<div class="spro-container">
	<div class="srow">';
		foreach($spro_manage_features as $feature => $info){
			foreach($info['plugin'] as $pslug => $data){
				
				if(empty($data['featured'])) continue;
				
				$disabled = '';
				if (!empty($data['requires_php']) && version_compare(PHP_VERSION, $data['requires_php'], '<')) {
					$data['plugin_desc'] .= '<br><span style="color: red;">'.sprintf(__('Requires PHP version %1$s or higher', 'softaculous-pro'), $data['requires_php']).'</span>';
					$disabled ='disabled';
				}
				
				echo '
				<div class="scol-6 spro-box-holder">
				<div class="spro-mng-plugin-header spx-2">
					<img src="'.(!empty($data['plugin_img']) ? $data['plugin_img'] : SOFTACULOUS_PRO_PLUGIN_URL.'/assets/images/plugins/'.$pslug.'.png').'" width="50" />
					<div class="spro-plugin-info-con sp-2">
						<div class="spro-manage-plugin-title spx-2">'.$data['plugin_name'].'</div>
						<div class="sp-2 spro-manage-plugin-desc">'.$data['plugin_desc'].'</div>
					</div>
				</div>
				<div class="spro-rec-plugin spx-2 smy-1">';
				
				$i_pending = $a_pending = 0;
				if(!empty($data['plugin_init'])){
				
					if(empty($manage_installed_plugins[$data['plugin_init']])){
						$status_free = __('Not Installed', 'softaculous-pro');
						$i_pending = 1;
					}elseif(!is_plugin_active($data['plugin_init'])){
						$status_free = __('Installed', 'softaculous-pro');
						$a_pending = 1;
					}else{
						$status_free = __('Active', 'softaculous-pro');
					}
				
					echo '<span class="spro-status"><b>'.__('Free', 'softaculous-pro').'</b>: '.$status_free.'</span>';
					
				}
				
				if(!empty($data['plugin_init_pro'])){
				
					if(empty($manage_installed_plugins[$data['plugin_init_pro']])){
						$status_pro = __('Not Installed', 'softaculous-pro');
						$i_pending = 1;
					}elseif(!is_plugin_active($data['plugin_init_pro'])){
						$status_pro = __('Installed', 'softaculous-pro');
						$a_pending = 1;
					}else{
						$status_pro = __('Active', 'softaculous-pro');
					}
				
					echo '<br /><span class="spro-status"><b>'.__('Pro', 'softaculous-pro').'</b>: '.$status_pro.'</span>';
				}
				
				echo '<span style="float:right;">';
				if(!empty($i_pending)){
					echo '<input type="button" name="'.$pslug.'" class="spro-plugin-install-btn sp-2" value="'.__('Install for Free', 'softaculous-pro').'" '.$disabled.'>';
				}elseif(!empty($a_pending)){
					echo '<input type="submit" name="'.$pslug.'" class="spro-plugin-install-btn spro-active-plugin sp-2" value="'.__('Activate', 'softaculous-pro').'" '.$disabled.'>';
				}else{
					echo '<p class="spro-plugin-active"><i class="fa-solid fa-check"></i>&nbsp;'.__('Active', 'softaculous-pro').'</p>';
				}
				echo '</span>';
				
				
				echo '
				</div>
				<i style="color:green;" class="spl-2">'.__('Pro included with your subscription', 'softaculous-pro').'</i>
				</div>';
			}
	}
	echo '
	</div>
	</div>';
	softaculous_pro_footer();
	
}
