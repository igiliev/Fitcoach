<?php

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

function softaculous_pro_page_settings($title = 'Softaculous Dashboard'){
	
	global $softaculous_pro_error, $softaculous_pro_msg, $spro_tour_status, $spro_tours, $softaculous_pro_settings;
		
	if(!current_user_can('manage_options')){
		wp_die(__('Sorry, but you do not have permissions to save settings.', 'softaculous-pro'));
	}
	
	$user_id = get_current_user_id();
	$meta_key = 'spro_tour_status';
	$spro_tour_status = get_user_meta($user_id, $meta_key, true);
	
	return softaculous_pro_page_settings_theme();
	
}

function softaculous_pro_page_settings_theme(){
	
	global $softaculous_pro_error, $softaculous_pro_msg, $spro_tour_status, $spro_tours, $softaculous_pro_settings, $softaculous_pro;
	
	softaculous_pro_header();
	
	softaculous_pro_admin_notice(1);
	
	include_once(SOFTACULOUS_PRO_PLUGIN_PATH.'main/onboarding.php');
	$spro_features = spro_get_features_list();
	$ai_tokens = get_option('softaculous_ai_tokens', []);
	
	$installed_plugins = get_plugins();
	
	if(!empty($softaculous_pro_error)){
		echo '<div id="message" class="error"><p>'.esc_html($softaculous_pro_error).'</p></div>';
	}
	
	if(!empty($softaculous_pro_msg)){
		echo '<div id="message" class="updated"><p>'.esc_html($softaculous_pro_msg).'</p></div>';
	}
	
	echo '
	<div class="srow sm-2 spro-justify-center">
	<div class="scol-4 spro-box-holder" id="spro-tours">
		<div class="spro-box-heading">
			'.__('Tours', 'softaculous-pro').'
			<hr />
		</div>
		
		<div class="spro-box-body">
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="assistant">
				<i class="fa-solid '.(!empty($spro_tour_status['assistant']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('Assistant', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="sidebar">
				<i class="fa-solid '.(!empty($spro_tour_status['sidebar']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('WordPress Sidebar', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="dashboard">
				<i class="fa-solid '.(!empty($spro_tour_status['dashboard']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('WordPress admin Dashboard', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="plugins">
				<i class="fa-solid '.(!empty($spro_tour_status['plugins']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('Plugins Management', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="themes">
				<i class="fa-solid '.(!empty($spro_tour_status['themes']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('Themes Management', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="pages">
				<i class="fa-solid '.(!empty($spro_tour_status['pages']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('Pages Management', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="posts">
				<i class="fa-solid '.(!empty($spro_tour_status['posts']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('Posts Management', 'softaculous-pro').'
			</div>
			<div class="spro-action-tile spro-tour-btn" spro-tour-id="users">
				<i class="fa-solid '.(!empty($spro_tour_status['users']['done']) ? 'fa-rotate-right' : 'fa-play').'"></i>&nbsp;&nbsp;&nbsp;'.__('Users Management', 'softaculous-pro').'
			</div>
		</div>
	</div>
	
	<div class="scol-4 spro-box-holder" id="spro-features">
		<div class="spro-box-heading" style="display:inline-block;">
			'.__('Recommended Features', 'softaculous-pro').'
		</div>
		<a style="float:right;text-decoration:none;" class="smr-2" href="'.admin_url('admin.php?page=assistant&act=manage-plugins').'">'.__('Show More', 'softaculous-pro').'</a>
		<hr />
		
		<div class="spro-box-body">';
		foreach($spro_features as $feature => $info){
			foreach($info['plugin'] as $data){
				
				if(empty($data['featured'])) continue;
				
				$i_pending = $a_pending = 0;
				if(!empty($data['plugin_init'])){
					if(empty($installed_plugins[$data['plugin_init']])){
						$i_pending = 1;
					}elseif(!is_plugin_active($data['plugin_init'])){
						$a_pending = 1;
					}
				}
				
				if(!empty($data['plugin_init_pro'])){
					if(empty($installed_plugins[$data['plugin_init_pro']])){
						$i_pending = 1;
					}elseif(!is_plugin_active($data['plugin_init_pro'])){
						$a_pending = 1;
					}
				}
				
				echo '<div class="spro-rec-plugin sp-2" style="display:flex;">
						<div>
							<div class="spro-plugin-title">'.$info['name'].'</div>
							<div><i>'.$info['info'].'</i></div>
						</div>';
				
				if(!empty($i_pending)){
					echo '<input type="button" name="'.key($info['plugin']).'" class="spro-plugin-install-btn sp-2" value="'.__('Install', 'softaculous-pro').'">';
				}elseif(!empty($a_pending)){
					echo '<input type="submit" name="'.key($info['plugin']).'" class="spro-plugin-install-btn spro-active-plugin sp-2" value="'.__('Activate', 'softaculous-pro').'">';
				}else{
					echo '<span class="spro-plugin-active spt-2"><i class="fa-solid fa-check"></i>&nbsp;'.__('Active', 'softaculous-pro').'</span>';
				}
				
				echo '</div>';
			}
		}
		
	echo '</div>
	</div>
	
	<div class="scol-4 spro-box-holder">
		<div id="spro-quick-links" style="margin-left:-15px; margin-right:-15px; padding-left:15px; padding-right:15px;">
			<div class="spro-box-heading">
				'.__('Quick Links', 'softaculous-pro').'
				<hr />
			</div>
			
			<div class="spro-box-body spro-quick-links">
				<ul>
					<li class="smb-3">
						<i class="fa-solid fa-flag"></i>&nbsp;
						<a href="'.admin_url('admin.php?page=assistant&act=license').'">'.(!empty($softaculous_pro['branding']['rebranded']) ? __('Manage Pro License', 'softaculous-pro') : __('Manage SoftWP License', 'softaculous-pro')).'&nbsp; &nbsp;'.(empty($softaculous_pro['license']) ? '<span style="color:red">Unlicensed</span>' : (!empty($softaculous_pro['license']['status_txt']) ? wp_kses_post($softaculous_pro['license']['status_txt']) : '')).'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-laptop"></i>&nbsp;
						<a href="'.home_url().'" target="_blank">'.__('View Site', 'softaculous-pro').'</a>
						&nbsp; &nbsp;
						<i class="fa-solid fa-rotate"></i>&nbsp;
						<a href="'.admin_url('update-core.php').'" target="_blank">'.__('Check Updates', 'softaculous-pro').'</a>
						&nbsp;  &nbsp;
						<i class="fa-solid fa-link"></i>&nbsp;
						<a href="'.admin_url('options-permalink.php').'" target="_blank">'.__('Permalinks', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-table-columns"></i>&nbsp;
						<a href="'.admin_url('edit.php?post_type=page').'" target="_blank">'.__('Manage Pages', 'softaculous-pro').'</a> 
						&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp; 
						<a href="'.admin_url('post-new.php?post_type=page').'" target="_blank">'.__('Add New', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-file-lines"></i>&nbsp;
						<a href="'.admin_url('edit.php').'" target="_blank">'.__('Manage Posts', 'softaculous-pro').'</a> 
						&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp; 
						<a href="'.admin_url('post-new.php').'" target="_blank">'.__('Add New', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-regular fa-images"></i>&nbsp;
						<a href="'.admin_url('upload.php').'" target="_blank">'.__('Manage Media', 'softaculous-pro').'</a>
						&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp;
						<a href="'.admin_url('media-new.php').'" target="_blank">'.__('Add New', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-plug" style="font-size:1.2em;"></i>&nbsp;
						<a href="'.admin_url('plugins.php').'" target="_blank">'.__('Manage Plugins', 'softaculous-pro').'</a> 
						&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp;
						<a href="'.admin_url('plugin-install.php').'" target="_blank">'.__('Add New', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-brush"></i>&nbsp;
						<a href="'.admin_url('themes.php').'" target="_blank">'.__('Manage Themes', 'softaculous-pro').'</a> 
						&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp;
						<a href="'.admin_url('theme-install.php').'" target="_blank">'.__('Add New', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-users"></i>&nbsp;
						<a href="'.admin_url('users.php').'" target="_blank">'.__('Manage Users', 'softaculous-pro').'</a> 
						&nbsp;<i class="fa-solid fa-arrow-right"></i>&nbsp;
						<a href="'.admin_url('user-new.php').'" target="_blank">'.__('Add New', 'softaculous-pro').'</a>
					</li>
					<li class="smb-3">
						<i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;
						<a href="'.admin_url('admin.php?page=assistant&act=onboarding').'" target="_blank">'.__('Launch Onboarding', 'softaculous-pro').'</a>
					</li>
				</ul>
			</div>
		</div>
		
		<div id="spro-settings" style="margin-left:-15px; margin-right:-15px; padding-left:15px; padding-right:15px;">
			<div class="spro-box-heading">
				'.__('Settings', 'softaculous-pro').'
				<hr />
			</div>
			
			<div class="spro-box-body spro-settings smt-2 smb-4">
				<label class="spro-toggle">
					<input type="checkbox" name="disable_comments" id="spro-disable-comments" '.(!empty($softaculous_pro_settings['disable_comments']) ? 'checked' : '').' requires-reload="1">
					<span class="spro-slider"></span>
				</label>
				<label for="spro-disable-comments" class="sml-2">'.__('Completely Disable Comments across the site', 'softaculous-pro').'</label>
			</div>
		</div>
		
		<div id="spro-ai" style="margin-left:-15px; margin-right:-15px; padding-left:15px; padding-right:15px;">			
			<div class="spro-box-heading">
				'.__('AI', 'softaculous-pro').
				(!empty($ai_tokens) ? ' <span style="border-radius:.25rem; padding: .125rem .625rem; background-color:rgb(237 235 254); border:1px dashed rgb(172 148 250); color:rgb(85 33 181); font-weight:500; font-size: .8rem;">'.($ai_tokens['remaining_tokens'] < 0 ? 0 : esc_html(number_format((int)$ai_tokens['remaining_tokens']))).' tokens remaining</span>' : '').
				'<span><a href="'.esc_url(SOFTACULOUS_PRO_AI_BUY.'&softwp_lic='.$softaculous_pro['license']['license']).'" target="_blank" style="font-weight:500; font-size: .8rem; margin-left:6px;">'.__('Buy AI Tokens', 'softaculous-pro').'</a></span>
				<hr />
			</div>
			
			<div class="spro-box-body spro-settings smt-2">
				<label class="spro-toggle">
					<input type="checkbox" name="disable_ai" id="spro-disable-ai" '.(!empty($softaculous_pro_settings['disable_ai']) ? 'checked' : '').'>
					<span class="spro-slider"></span>
				</label>
				<label for="spro-disable-ai" class="sml-2">'.__('Disable Softaculous AI', 'softaculous-pro').'</label>
			</div>
			<div class="spro-box-body spro-settings">
				<label>
					<select name="ai_history_duration" id="spro-ai-history-duration" value="'.(!empty($softaculous_pro_settings['ai_history_duration']) ? esc_attr($softaculous_pro_settings['ai_history_duration']) : '').'" requires-reload="1">
						<option value="90" '.(isset($softaculous_pro_settings['ai_history_duration']) ? selected($softaculous_pro_settings['ai_history_duration'] , '90', false) : '').'>90 '.__('Days', 'softaculous-pro').'</option>
						<option value="60" '.(isset($softaculous_pro_settings['ai_history_duration']) ? selected($softaculous_pro_settings['ai_history_duration'] , '60', false) : '').'>60 '.__('Days', 'softaculous-pro').'</option>
						<option value="30" '.(isset($softaculous_pro_settings['ai_history_duration']) ? selected($softaculous_pro_settings['ai_history_duration'] , '30', false) : '').'>30 '.__('Days', 'softaculous-pro').'</option>
						<option value="-1" '.(isset($softaculous_pro_settings['ai_history_duration']) ? selected($softaculous_pro_settings['ai_history_duration'] , '-1', false) : '').'>'.__('Never', 'softaculous-pro').'</option>
					</select>
				</label>
				<label for="spro-ai-history-duration" class="sml-2">'.__('AI history retention', 'softaculous-pro').'</label>
			</div>
		</div>
	</div>
	</div>
	
	<script>
	var spro_tours = JSON.parse("'.addslashes(json_encode($spro_tours)).'");
	var spro_admin_url = "'.admin_url().'";
	var spro_dashboard_url = "'.admin_url('admin.php?page=assistant').'";
	
	jQuery(".spro-tour-btn").click(function(){
		jQuery(this).find("i").removeClass("fa-play");
		jQuery(this).find("i").addClass("fa-rotate-right");
		var spro_tour_page = jQuery(this).attr("spro-tour-id");
		spro_setcookie("spro-load-tour", spro_tour_page, 1);
		window.location = spro_tours[spro_tour_page];
	});
	
	jQuery(document).ready(function() {
		
		var has_hash = document.location.href.indexOf("#");
		if( has_hash > -1){
			var to_highlight = document.location.href.substr(has_hash+1);
			spro_highlight(to_highlight);
		}
		
		jQuery(".spro-settings input").each(function(){
			jQuery(this).click(function(){
				var requires_reload = jQuery(this).attr("requires-reload");
				var data = {
					action: "softaculous_pro_wp_ajax",
					softaculous_pro_update_option: 1,
					option_name: jQuery(this).attr("name"),
					option_value: (jQuery(this).is(":checked") ? "1" : "0"),
					softaculous_pro_security: soft_pro_obj.nonce
				};
				
				jQuery.post(soft_pro_obj.ajax_url, data, function(response){})
				
				.done(function(){
					if(requires_reload && requires_reload == 1){
						window.location = spro_dashboard_url;
					}
				});
			});
		});
	});
	
	jQuery("#spro-ai-history-duration").change(function(e){
		var requires_reload = jQuery(this).attr("requires-reload");
		var data = {
			action: "softaculous_pro_wp_ajax",
			softaculous_pro_update_option: 1,
			option_name: jQuery(this).attr("name"),
			option_value: jQuery(this).val(),
			softaculous_pro_security: soft_pro_obj.nonce
		};
		
		jQuery.post(soft_pro_obj.ajax_url, data, function(response){})
		.done(function(){
			if(requires_reload && requires_reload == 1){
				window.location = spro_dashboard_url;
			}
		});
	})
	
	function spro_highlight(box){
		spro_goto_id("spro-"+box);
		jQuery("#spro-"+box).css({"box-shadow": "rgba(255, 33, 33, 0.8) 0px 0px 1px 2px, rgba(33, 33, 33, 0.5) 0px 0px 0px 0px"});
		setTimeout(function () {
			jQuery("#spro-"+box).css({"box-shadow": "none"});
        }, 1500);
	}
	
	function spro_goto_id(id){
		// Scroll
		jQuery("html,body").animate({
			scrollTop: jQuery("#"+id).offset().top},
			"slow");
	}
	
	</script>';
	
	softaculous_pro_footer();
}

function softaculous_pro_header($is_home = 1){
    
	global $softaculous_pro;
	
	echo '
	<div class="spro-header">
		<div class="scol-3">
			<a href="'.admin_url('admin.php?page=assistant').'"><img src="'.$softaculous_pro['branding']['logo_url'].'" alt="'.$softaculous_pro['branding']['sn'].'" title="'.__('WordPress Assistant', 'softaculous-pro').'" id="soft-main-logo"></a>
		</div>
		<div class="scol-9">
			<ul class="spro-header-menu" style="margin-top:0px; margin-bottom:-15px; padding-top:15px; padding-bottom:15px;">
				<li><a href="'.admin_url('admin.php?page=assistant').'"><i class="fa-solid fa-gauge-high"></i>&nbsp;&nbsp;'.__('Dashboard', 'softaculous-pro').'</a></li>';
				
				if(!empty($is_home)){
					echo '
					<li onclick="spro_highlight(\'ai\');return false;"><a href=""><i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;&nbsp;'.__('AI', 'softaculous-pro').'</a></li>
					<li onclick="spro_highlight(\'tours\');return false;"><a href=""><i class="fa-solid fa-play"></i>&nbsp;&nbsp;'.__('Tours', 'softaculous-pro').'</a></li>
					<li onclick="spro_highlight(\'features\');return false;"><a href=""><i class="fa-solid fa-list-check"></i>&nbsp;&nbsp;'.__('Extend', 'softaculous-pro').'</a></li>
					<li onclick="spro_highlight(\'quick-links\');return false;"><a href=""><i class="fa-solid fa-link" style="font-size:1.1em;"></i>&nbsp;&nbsp;'.__('Quick Links', 'softaculous-pro').'</a></li>';
				}else{
					echo '
					<li><a href="'.admin_url('admin.php?page=assistant').'#ai"><i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;&nbsp;'.__('AI', 'softaculous-pro').'</a></li>
					<li><a href="'.admin_url('admin.php?page=assistant').'#tours"><i class="fa-solid fa-play"></i>&nbsp;&nbsp;'.__('Tours', 'softaculous-pro').'</a></li>
					<li><a href="'.admin_url('admin.php?page=assistant').'#features"><i class="fa-solid fa-list-check"></i>&nbsp;&nbsp;'.__('Extend', 'softaculous-pro').'</a></li>
					<li><a href="'.admin_url('admin.php?page=assistant').'#quick-links"><i class="fa-solid fa-link" style="font-size:1.1em;"></i>&nbsp;&nbsp;'.__('Quick Links', 'softaculous-pro').'</a></li>';
				}
		echo '
			</ul>
		</div>
	</div>
	<div class="spro-body">';
	
	softaculous_pro_license_notice();
}

function softaculous_pro_footer(){
    
	global $softaculous_pro;
    
	echo '</div>';
	
	if(!empty($softaculous_pro['branding']['default_hf_bg'])){
		echo '
		<style>
		.spro-header {
			background-color:'.$softaculous_pro['branding']['default_hf_bg'].' !important;
		}
		</style>';
	}

	if(!empty($softaculous_pro['branding']['default_hf_text'])){
		echo '
		<style>
		ul.spro-header-menu a{
			color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
		}
		</style>';
	}
}

function softaculous_pro_assistant(){
	
	global $softaculous_pro_error, $softaculous_pro_msg, $spro_tour_status, $spro_tours, $spro_tour_content, $spro_load_tour;
	
	$spro_load_tour = softaculous_pro_can_enqueue_assets();
	
	if(empty($spro_load_tour)){
		return true;
	}
	
	include_once(SOFTACULOUS_PRO_PLUGIN_PATH.'main/intros.php');
	
	if(empty($spro_tour_content[$spro_load_tour])){
		return true;
	}
	
	return softaculous_pro_assistant_theme();
}

function softaculous_pro_assistant_theme(){
	
	global $softaculous_pro_error, $softaculous_pro_msg, $spro_tour_status, $spro_tours, $spro_tour_content, $spro_load_tour;
	
    echo '
    <script>
	
	var spro_load_tour = "'.$spro_load_tour.'";
	var spro_tour_retries = 0;
	
	function spro_can_init_introjs(){
		
		if(spro_load_tour != "themes" || jQuery(".add-new-theme").length > 0 || spro_tour_retries >= 600){
			spro_init_introjs();
			return true;
		}
		
		spro_tour_retries++;
		
		setTimeout(spro_can_init_introjs, 100); // Try again
	}
	
	function spro_init_introjs(){
		
        var spro_intro = introJs();
			
		spro_intro.setOptions({
			steps: [';
				foreach($spro_tour_content[$spro_load_tour] as $key => $values){
					echo '
					{
						'.(!empty($key) ? 'element: document.querySelector("'.$key.'"),' : '').'
						title: "'.$values['title'].'",
						intro: "'.$values['intro'].'",
						'.(!empty($values['position']) ? 'position: "'.$values['position'].'",' : '').'
						'.(!empty($values['hover']) ? 'hover: "'.$values['hover'].'",' : '').'
						'.(!empty($values['hover_selector']) ? 'hover_selector: "'.$values['hover_selector'].'",' : '').'
						'.(!empty($values['hover_class']) ? 'hover_class: "'.$values['hover_class'].'",' : '').'
					},
				';}
				echo '
			]
		});
        
		var intro_content = "'.$spro_load_tour.'";
		var previousElement = null;                
		var previousHoverClass = null;                

		//Proccesing on each step before step change
		spro_intro.onbeforechange(function(targetElement) {
			
			var currentStep = spro_intro._currentStep;
			var currStep = spro_intro._introItems[currentStep];
			
			if(currStep){
				var hover = currStep.hover;
				var hover_selector = currStep.hover_selector;
				var hover_class =  (hover && currStep.hover_class) ? currStep.hover_class : "hover";
			}
			
			var css = document.createElement("style");
			
			// Disable back button on the first step
			if (currentStep === 0) {
				css.type = "text/css"; 
				css.innerHTML = ".introjs-prevbutton {display: none !important;}"; 
				document.body.appendChild(css);
			}else{
				css.type = "text/css";
				css.innerHTML = ".introjs-prevbutton {display: block !important;}"; 
				document.body.appendChild(css);
			}
			
			//Refreshing the intro on each step change
			spro_intro.refresh();

			// Remove hover class and aria-expanded to false to display the Next/Previous element
			if (previousElement){

				// Remove hover class 
				if (previousHoverClass){
					previousElement.classList.remove(previousHoverClass);
				}
				
				var previousATag = previousElement.querySelector("a");
				if (previousATag) {
					previousATag.setAttribute("aria-expanded", "false");
				}
			}
			
			// Add hover class and aria-expanded to true to display the Next/Previous element
			if (hover == "true") {

				//Handling case where we if we did not pass any ids or class in hover except true
				if(hover_selector){

					var element = hover_selector.trim();
					var liElement = document.querySelector(element);
					liElement.classList.add(hover_class);
				}
				else{
					var liElement = targetElement;
					liElement.classList.add(hover_class);
				}
				var aTag = liElement.querySelector("a");
				if (aTag) {
					aTag.setAttribute("aria-expanded", "true");
				}
				
				// Update the previousElement to the current target element
				previousElement = liElement;
				previousHoverClass = hover_class;
			}
		});
		
		spro_intro.start();
        
        introCompleted = false;   //Setting Intro Complete to False.
        
        // If User has completed watching the intro
        spro_intro.oncomplete(function(){

			// Remove hover class and aria-expanded to false to display the Next/Previous element
			if (previousElement){

				// Remove hover class 
				if (previousHoverClass){
					previousElement.classList.remove(previousHoverClass);
				}
				
				var previousATag = previousElement.querySelector("a");
				if (previousATag) {
					previousATag.setAttribute("aria-expanded", "false");
				}
			}
			
			introCompleted = true;  //Setting Intro Complete to True.
			var spro_tour = spro_getcookie("spro-load-tour");
			spro_removecookie("spro-load-tour");
			
			if(spro_tour){
				var data = {
					action: "softaculous_pro_wp_ajax",
					tour_done: spro_tour,
					softaculous_pro_security: soft_pro_obj.nonce
				};
				
				jQuery.post(soft_pro_obj.ajax_url, data, function(response){});
			}
			
        });
		
		spro_intro.onbeforeexit(function () {
			if(!introCompleted){
				return confirm("'.__('Are you sure you want to skip the Tour?', 'softaculous-pro').'");
			}
			return true;
		});
        
        spro_intro.onexit(function() {
			introCompleted = true;  // Set flag to indicate confirmation
			var spro_tour = spro_getcookie("spro-load-tour");
			spro_removecookie("spro-load-tour");
			
			if(spro_tour){
				var data = {
					action: "softaculous_pro_wp_ajax",
					tour_done: spro_tour,
					softaculous_pro_security: soft_pro_obj.nonce
				};
				
				jQuery.post(soft_pro_obj.ajax_url, data, function(response){});
			}
        });
		
	}
	
    jQuery( document ).ready(function() {
		spro_can_init_introjs();
    });
	
    </script>';
	
}

function softaculous_pro_wp_ajax(){
	
	global $spro_tours, $spro_manage_features;
	
	if(!current_user_can('manage_options') || !isset($_POST['softaculous_pro_security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['softaculous_pro_security'])), 'softaculous_pro_js_nonce')){
		wp_send_json_error('Security Check Failed!');
	}
	
	if(isset($_REQUEST['tour_done'])){
		
		$user_id = get_current_user_id();
		$meta_key = 'spro_tour_status';
		$spro_tour_status = get_user_meta($user_id, $meta_key, true);
		
		if(empty($spro_tour_status)){
			$spro_tour_status = array();
		}
		
		$tour_done = softaculous_pro_optREQ('tour_done');
		
		if(!empty($spro_tours[$tour_done])){
			$spro_tour_status[$tour_done]['done'] = 1;
			update_user_meta($user_id, $meta_key, $spro_tour_status);
		}
	}
	
	if(isset($_REQUEST['softaculous_pro_update_option'])){
		
		$softaculous_pro_settings = get_option('softaculous_pro_settings', array());
		$option_name = softaculous_pro_optREQ('option_name');
		$option_value = softaculous_pro_optREQ('option_value');
		
		// Setting up the trainsiet to delete the history
		if($option_name === 'ai_history_duration' && $option_value > 1){
			wp_schedule_event(time(), 'daily', 'softaculous_pro_ai_history_cron');
		} else if(($option_name === 'disable_ai' && empty($option_value)) || ($option_name === 'ai_history_duration')){
			wp_clear_scheduled_hook('softaculous_pro_ai_history_cron');
		}

		$softaculous_pro_settings[$option_name] = $option_value;

		update_option('softaculous_pro_settings', $softaculous_pro_settings);
	}
	
	if(isset($_REQUEST['softaculous_pro_onboarding_notice_dismiss'])){
		update_option('softaculous_pro_onboarding_notice_dismiss', time());
	}
	
	if(isset($_REQUEST['softaculous_pro_ai_notice_dismiss'])){
		update_option('softaculous_pro_ai_notice_dismiss', time());
	}

	if(isset($_REQUEST['softaculous_pro_ai_token_notice_dismiss'])){
		update_option('softaculous_pro_ai_token_notice_dismiss', time());
	}
	
	if(isset($_REQUEST['softaculous_pro_install_plugin']) && current_user_can('install_plugins')){
	
		include_once(SOFTACULOUS_PRO_PLUGIN_PATH.'main/onboarding.php');
		$spro_manage_features = spro_get_features_list();

		$ftp_form_url = wp_nonce_url(admin_url('admin-ajax.php'), 'filesystem-ajax-nonce');

		ob_start();
		// Check if FTP is required
		$have_credentials = request_filesystem_credentials($ftp_form_url);

		if(false === $have_credentials){
			$form_html = ob_get_clean();
			$ftp_modal = '<div id="request-filesystem-credentials-dialog" class="notification-dialog-wrap request-filesystem-credentials-dialog">
			<div class="notification-dialog-background"></div>
			<div class="notification-dialog" role="dialog" aria-labelledby="request-filesystem-credentials-title" tabindex="0">
			<div class="request-filesystem-credentials-dialog-content">'. $form_html . '</div></div></div>';

			wp_send_json_error(['form' => $ftp_modal]);
		}

		ob_end_clean(); // Just in case there was any output till now it will be cleaned.
		
		$install_plugin = softaculous_pro_optREQ('plugin');
		
		foreach($spro_manage_features as $feature => $info){
			if(!empty($info['plugin'][$install_plugin])){
				spro_install_required_plugin($install_plugin, $info['plugin'][$install_plugin]);
				break;
			}
		}
	}

}

// Ajax to dismiss the expiry notice
function softaculous_pro_dismiss_expiry_notice(){
	check_admin_referer('softaculous_pro_expiry_notice', 'security');

	if(!current_user_can('activate_plugins')){
		wp_send_json_error(__('You do not have required access to do this action', 'backuply'));
	}
	
	update_option('softaculous_expired_licenses', time());
	wp_send_json_success();
}
