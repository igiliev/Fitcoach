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

function softaculous_pro_license(){
	
	global $spro_error;
	
	// Is there a license key ?
	if(isset($_POST['save_sp_license'])){
		
		check_admin_referer('softaculous-pro-options');
	
		$license = softaculous_pro_optPOST('softaculous_pro_license');
		
		// Check if its a valid license
		if(empty($license)){
			$spro_error['lic_invalid'] = __('The license key was not submitted', 'softaculous-pro');
			return softaculous_pro_license_T();
		}
		
		$resp = wp_remote_get(softaculous_pro_api_url(1).'license.php?license='.$license.'&url='.rawurlencode(site_url()), array('timeout' => 30));
		
		if(is_array($resp)){
			$json = json_decode($resp['body'], true);
			// print_r($json);
		}else{
			$spro_error['resp_invalid'] = __('The response was malformed', 'softaculous-pro').'<br />'.var_export($resp, true);
			return softaculous_pro_license_T();
		}
		
		// Save the License
		if(empty($json['license'])){
			
			$spro_error['lic_invalid'] = __('The license key is invalid', 'softaculous-pro');
			return softaculous_pro_license_T();
			
		}else{
			
			update_option('softaculous_pro_license', $json);
			
			// Load license
			softaculous_pro_load_license();
			
			$GLOBALS['spl_saved'] = true;
		}
		
	}
	
	softaculous_pro_license_T();
	
}

// The License Page
function softaculous_pro_license_T(){
	
    global $softaculous_pro, $spro_error;
	
	softaculous_pro_header(0);

	// Saved ?
	if(!empty($GLOBALS['spl_saved'])){
		echo '<div class="notice notice-success"><p>'. __('The license has been saved successfully', 'softaculous-pro'). '</p></div><br />';
	}
	
	// Any errors ?
	if(!empty($spro_error)){
		softaculous_pro_report_error($spro_error);
	}
	
	?>
	
	<div class="metabox-holder">
	<div class="postbox">
	
		<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span><?php echo __('System Information', 'softaculous_pro'); ?></span>
		</h2>
		</div>
		
		<div class="inside">
		
		<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('softaculous-pro-options'); ?>
		<table class="wp-list-table fixed striped users" cellspacing="1" border="0" width="95%" cellpadding="10" align="center">
		<?php
			echo '
			<tr>				
				<th align="left" width="25%">'.__('SoftWP version', 'softaculous-pro').'</th>
				<td>'.SOFTACULOUS_PRO_VERSION.'</td>
			</tr>';
			
			echo '
			<tr>			
				<th align="left" valign="top">'.__('SoftWP License', 'softaculous-pro').'</th>
				<td align="left">
					'.(empty($softaculous_pro['license']) ? '<span style="color:red">Unlicensed</span> &nbsp; &nbsp;' : '').' 
					<input type="text" name="softaculous_pro_license" value="'.(empty($softaculous_pro['license']) ? '' : esc_html($softaculous_pro['license']['license'])).'" size="30" placeholder="e.g. SOFTWP-11111-22222-33333-44444" style="width:300px;" /> &nbsp; 
					<input name="save_sp_license" class="button button-primary" value="'.__('Update License', 'softaculous-pro').'" type="submit" />';
					
					if(!empty($softaculous_pro['license'])){
						
						$expires = $softaculous_pro['license']['expires'];
						$expires = substr($expires, 0, 4).'/'.substr($expires, 4, 2).'/'.substr($expires, 6);
						
						echo '<div style="margin-top:10px;">'.__('License Status : ', 'softaculous-pro').(empty($softaculous_pro['license']['status_txt']) ? 'N.A.' : wp_kses_post($softaculous_pro['license']['status_txt'])).' &nbsp; &nbsp; &nbsp; 
						'.($softaculous_pro['license']['expires'] <= date('Ymd') ? __('License Expires : ', 'softaculous-pro').' <span style="color:red">'.esc_html($expires).'</span>' : (empty($softaculous_pro['license']['has_plid']) ? __('License Expires : ', 'softaculous-pro').' '.esc_html($expires) : '')).'
						</div>';
					}
					
					
				echo 
				'</td>
			</tr>';
			
			echo '<tr>
				<th align="left">'.__('URL', 'softaculous-pro').'</th>
				<td>'.get_site_url().'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Path', 'softaculous-pro').'</th>
				<td>'.ABSPATH.'</td>
			</tr>
			<tr>				
				<th align="left">'.__('Server\'s IP Address', 'softaculous-pro').'</th>
				<td>'.$_SERVER['SERVER_ADDR'].'</td>
			</tr>';
		?>
		</table>
		</form>
		
		</div>
	</div>
	</div>
    <?php
	softaculous_pro_footer();
}
