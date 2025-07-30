<?php
/*
* CookieAdmin Pro
* https://cookieadmin.net
* (c) Softaculous Team
*/

namespace CookieAdminPro;

if(!defined('ABSPATH')){
	die('Hacking Attempt!');
}

class License{

	static function cookieadmin_pro_license(){
		
		global $lic_resp, $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg;
		
		if(empty($_POST['cookieadmin_pro_license_nonce']) || !wp_verify_nonce($_POST['cookieadmin_pro_license_nonce'], 'cookieadmin_pro_license')){
			$cookieadmin_error = __('Security Check Failed', 'cookieadmin-pro');
			return;
		}

		$license = !empty($_POST['cookieadmin_pro_license']) ? sanitize_key(wp_unslash($_POST['cookieadmin_pro_license'])) : '';
		
		if(empty($license)){
			$cookieadmin_error = __('The license key was not submitted', 'cookieadmin-pro');
			return;
		}
		
		cookieadmin_pro_load_license($license);
		
		if(!is_array($lic_resp)){
			$cookieadmin_error = __('The response was malformed<br>'.var_export($lic_resp, true), 'cookieadmin-pro');
			return;
		}

		$json = json_decode($lic_resp['body'], true);

		if(empty($json['license'])){
			$cookieadmin_error = __('The license key is invalid', 'cookieadmin-pro');
			return;
		}
		
		$cookieadmin_msg = __('Successfully updated the license key', 'cookieadmin-pro');
		
	}
	
		
	static function cookieadmin_show_license(){
		global $cookieadmin;
		
		if(isset($_REQUEST['save_cookieadmin_pro_license'])){
			\CookieAdminPro\License::cookieadmin_pro_license();
		}
		?>
		<div class="cookieadmin-pro-license-content wrap" >
			<?php \CookieAdmin\Admin::header_theme(__('License', 'cookieadmin-pro')); ?>
			<div class="cookieadmin-pro-tab-group"  style=" width:100% ;background:white; padding:20px;">
				<table class="wp-list-table fixed striped users cookieadmin-pro-license-table" cellspacing="1" border="0" width="100%" cellpadding="10" align="center">
					<tbody>
						<tr>				
							<th align="left" width="25%"><?php esc_html_e('CookieAdmin Version', 'cookieadmin-pro'); ?></th>
							<td><?php
								echo COOKIEADMIN_PRO_VERSION.' ('.esc_html__('Pro Version', 'cookieadmin-pro').')';
							?>
							</td>
						</tr>
						<tr>			
							<th align="left" valign="top"><?php esc_html_e('CookieAdmin License', 'cookieadmin-pro'); ?></th>
							<td align="left">
								<form method="post" action="">
									<?php echo (defined('COOKIEADMIN_PREMIUM') && empty($cookieadmin['license']['license']) ? '<span style="color:red">'.esc_html__('Unlicensed', 'cookieadmin-pro').'</span> &nbsp; &nbsp;' : '')?>
									<input type="hidden" name="cookieadmin_pro_license_nonce" value="<?php echo wp_create_nonce('cookieadmin_pro_license');?>"/>
									<input type="text" name="cookieadmin_pro_license" value="<?php echo (empty($cookieadmin['license']['license']) ? '': esc_html($cookieadmin['license']['license']))?>" size="30" placeholder="e.g. COOKA-11111-22222-33333-44444" style="width:300px;"> &nbsp; 
									<input name="save_cookieadmin_pro_license" class="cookieadmin-btn cookieadmin-btn-primary dosmtp-sumbit-licence" value="Update License" type="submit">
								</form>
								<?php if(!empty($cookieadmin['license']['license'])){
										
										$expires = $cookieadmin['license']['expires'];
										$expires = substr($expires, 0, 4).'/'.substr($expires, 4, 2).'/'.substr($expires, 6);
										
										echo '<div style="margin-top:10px;">'.esc_html__('License Status', 'cookieadmin-pro').' : '.(empty($cookieadmin['license']['status_txt']) ? 'N.A.' : wp_kses_post($cookieadmin['license']['status_txt'])).' &nbsp; &nbsp; &nbsp; 
										'.($cookieadmin['license']['expires'] <= date('Ymd') ? esc_html__('License Expires', 'cookieadmin-pro').' : <span style="color:red;">'.esc_attr($expires).'</span>' : (empty($cookieadmin['license']['has_plid']) ? esc_html__('License Expires', 'cookieadmin-pro').' : '.esc_html($expires) : '')).'
										</div>';
										
								}?>
							</td>
						</tr>
						<tr>
							<th align="left">URL</th>
							<td><?php echo esc_url(get_site_url()); ?></td>
						</tr>
						<tr>				
							<th align="left">Path</th>
							<td><?php echo ABSPATH; ?></td>
						</tr>
						<tr>
							<th align="left"><?php esc_html_e('Server\'s IP Address', 'cookieadmin-pro') ?></th>
							<td><?php echo esc_attr($_SERVER['SERVER_ADDR']); ?></td>
						</tr>		
					</tbody>
				</table>
			</div>
		</div>
		<?php \CookieAdmin\Admin::footer_theme(); 
	}
	 
	
}