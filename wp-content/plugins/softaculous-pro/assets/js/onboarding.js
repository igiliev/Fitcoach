
var softaculous_pro_show_themes_loaded = 0;

jQuery(document).ready(function(){
	
	let url = new URL(window.location.href);
	let step = url.searchParams.get('step');
	
	if(step === 'import_theme'){
		jQuery('.softaculous-pro-wizard-sidebar').addClass('compact');
		jQuery('.softaculous-pro-setup-logo').hide();
		jQuery('.active_step').find('span').css('min-width', '0');
	
		if(!softaculous_pro_show_themes_loaded){
			softaculous_pro_show_themes();
		}
	}
	
	var feature_holder = jQuery('.softaculous-pro-features');
	feature_holder.each(function(){
		var feature_checked = jQuery(this).find('input').is(":checked");
		if(feature_checked){
			jQuery(this).addClass("feature-border");
		}
	});
	
	feature_holder.click(function(){
		var feature_disabled = jQuery(this).find('input').is(":disabled");
		if(feature_disabled && !jQuery(this).hasClass("spro-erro")){
			var selected_erro = jQuery(this).find('input').attr('spro-erro');
			const para = jQuery('<p>'+selected_erro+'</p>').css('color', 'red');
			jQuery(this).find('.softaculous-pro-features-text').find('p').replaceWith(para);
			jQuery(this).addClass("spro-erro");
		}
	});
	
	if(jQuery('.softaculous-pro-wizard').length > 0){
		window.addEventListener('popstate', function(event){
			// Get the current URL
			let url = new URL(window.location.href);
			let step = url.searchParams.get('step');
			
			if(step){
				softaculous_pro_set_active_step(step);
			}
		});
	}

});


function softaculous_pro_set_active_step(step) {
	
	// Active Tab
	jQuery('.softaculous-pro-steps-holder ol li').removeClass('active_step');

	if(step ==='import_theme'){
		jQuery('.softaculous-pro-steps-holder ol li [data-step="'+step+'"]').closest('li').addClass('active_step');
		jQuery('.softaculous-pro-wizard-sidebar').addClass('compact');
		jQuery('.softaculous-pro-setup-logo').hide();
		jQuery('.active_step').find('span').css('min-width', '0');
	}else{
		jQuery('.softaculous-pro-steps-holder ol li [data-step="'+step+'"]').closest('li').addClass('active_step');

	}
	
	// Active Panel
	jQuery('.softaculous-pro-wizard .softaculous-pro-wizard-content').attr('data-active-panel', step);
}

function softaculous_pro_next_handler(element) {
	
	var ele = jQuery(element);
	var nextStep = ele.data('step');
	var currentStep = ele.closest('[data-panel]').data('panel');
	// Save the current URL
	let currentUrl = window.location.href;
	
	if(currentStep  === 'welcome'){
		if(jQuery("#onboarding_done_confirm").length > 0 && !jQuery("#onboarding_done_confirm").is(":checked")){
			alert(softwp_onboarding_lang.conf_data_loss);
			return false;
		}
	}
	
	// Get the current URL
	let url = new URL(currentUrl);

	// Add a query parameter
	url.searchParams.set('step', nextStep);
	// Replace the current URL without refreshing the page
	window.history.pushState({ path: currentUrl }, '', url);
	softaculous_pro_set_active_step(nextStep);
	jQuery('.softaculous-pro-steps-holder ol li').removeClass('active_step');
	jQuery('.softaculous-pro-steps-holder ol li [data-step="'+nextStep+'"]').closest('li').addClass('active_step');
	
	var post_data = null;
	
	if(currentStep  === 'type'){
		var active_step  =  jQuery('.active_category').children('input').val();

		if(active_step === 'Others'){
			post_data = jQuery('.softaculous-pro-category-input').children('input').val();
		}
		else{
			post_data = active_step;
		}
	}
	
	if(currentStep === 'title'){
		var site_input = jQuery('.softaculous-pro-title-input');
		var new_site_title = site_input.children('input').val();
		post_data = new_site_title ;
	}
	
	if(currentStep === 'features'){
		var selected_feats = [];
		var feature_holder = jQuery('.softaculous-pro-features');
		feature_holder.each(function(){
            var feature_checked = jQuery(this).find('input').is(":checked");
			if(feature_checked){
				var selected_feat = jQuery(this).attr('data-slug');
				selected_feats.push(selected_feat);
			}
        });
		post_data = selected_feats;
	}
	
	if(nextStep === 'import_theme'){
		if(!softaculous_pro_show_themes_loaded){
			softaculous_pro_show_themes();
		}
	}
	
	if(post_data){
		jQuery.ajax({
			type: 'post',
			url: soft_pro_obj.ajax_url,
			data: {
				action: 'softaculous_pro_setup_info',
				step: currentStep,
				softaculous_pro_nonce: softaculous_pro_ajax_nonce,
				data: post_data,
			},
			success: function (response) {},
		});
	}
}

function softaculous_pro_prev_handler(element) {
	var ele = jQuery(element);
	var currentStep = ele.closest('[data-panel]').data('panel');
	var prevStep = ele.data('step');
	let currentUrl = window.location.href;
	let url = new URL(currentUrl);
	url.searchParams.set('step', prevStep);
	window.history.pushState({ path: currentUrl }, '', url);
	softaculous_pro_set_active_step(prevStep);
	jQuery('.softaculous-pro-steps-holder ol li').removeClass('active_step');
	jQuery('.softaculous-pro-steps-holder ol li [data-step="'+prevStep+'"]').closest('li').addClass('active_step');

}

function softaculous_pro_selected_features(element) {
	var ele_parent = jQuery(element).parent().parent();
	if(jQuery(element).is(":checked")){
		ele_parent.addClass("feature-border");
	}else{
		ele_parent.removeClass("feature-border");
	}
}

function softaculous_pro_modal(sel){
	
	var page_names = [];
	
	jQuery('.softaculous_pro_img_screen').each(function() {
		
		var is_selected = jQuery(this).find('input').is(':checked');
		if(is_selected){
			var page_name = jQuery(this).attr('page-name');
		}
		
		page_names.push(page_name);
	});
	
	page_names.forEach(function(value) {
		var input = jQuery('<input>').attr('type', 'hidden').attr('name', 'to_import[]').val(value);
		jQuery('#softaculous-pro-import-form').append(input);
	});
	
	if(page_names.length == 0){
		alert(softwp_onboarding_lang.select_atleast_one);
	} else{
		jQuery('#softaculous-pro-templates-holder').hide();
		var modal = jQuery(sel);
		modal.show();
		modal.find('.softaculous-pro-done').hide();
		modal.find('.softaculous-pro-import').show();
		
		var spro_temp_form_data = jQuery('#softaculous-pro-import-form').serialize();
		softaculous_pro_handle_templates(spro_temp_form_data);
	}
	
}

function softaculous_pro_handle_templates(spro_temp_form_data){
	jQuery('#softaculous-pro-templates-holder').remove();
	jQuery('#SproTemplatesModal').hide();
	
	var message = softwp_onboarding_lang.checkRequirements;
	
	softaculous_pro_create_html(message, 10, true); // Start progress at 1%
	
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_start_install_template',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce,
		success: function(response){
			if(!response.success && response.data && response.data.form){
			  softaculous_handle_ftp_form(response.data.form, spro_temp_form_data);
				return;
			}

			// Install plugin gives too much output, hence match the data
			var data = response.match(/<softaculous\-pro\-xmlwrap>(.*?)<\/softaculous\-pro\-xmlwrap>/is);
			
			if(data){
				data = data[1];
			}
			data = JSON.parse(data);
			softaculous_pro_selected_plugin(data, spro_temp_form_data);
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	});
}

function softaculous_pro_selected_plugin(data, spro_temp_form_data){
	
	if(typeof data === 'object' && 'error' in data){
		softaculous_pro_show_error(data['error']);
		return false;
	}
	var message = softwp_onboarding_lang.downloading_installing_plugins;
	softaculous_pro_create_html(message, 45, true); // Update progress to 45%
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_selected_plugin_install',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce,
		dataType: 'json',
		success: function(data){
			softaculous_pro_download_template(data, spro_temp_form_data);
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	})
}

function softaculous_pro_download_template(data, spro_temp_form_data){
	
	var failed = [];
	if(typeof data === 'object' && 'error' in data){
		softaculous_pro_show_error(data['error']);
		return false;
	}
	if(typeof data === 'object' && 'failed_plugin' in data){
		failed.push(data['failed_plugin']);
	}
	var message = softwp_onboarding_lang.downloading_template;
	softaculous_pro_create_html(message, 80, true, failed); // Update progress to 80%
	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_download_template',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce,
		dataType: 'json',
		success: function(data){
			data.failed_plugin = failed;
			softaculous_pro_import_template(data, spro_temp_form_data);
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	});
  
}

function softaculous_handle_ftp_form(form, form_data){
	// Handling FTP Form
	jQuery('body').append(form);

	var ftp_modal = jQuery('#request-filesystem-credentials-dialog');
	ftp_modal.show();

	// Handling the close btn of the FTP form.
	ftp_modal.find('.cancel-button').on('click', function(event){
    event.preventDefault();
		ftp_modal.hide();
		alert(softwp_onboarding_lang.wordpress_require_ftp);
	});

  ftp_modal.on('submit', 'form', function(event){
		event.preventDefault();

		let serialized_data = jQuery(event.target).serialize();
		form_data += '&'+serialized_data;
		ftp_modal.hide();
		softaculous_pro_handle_templates(form_data);
	});
}

// Import template
function softaculous_pro_import_template(data, spro_temp_form_data){

	if(typeof data === 'object' && 'error' in data){
		softaculous_pro_show_error(data['error']);
		return false;
	}
	
	var message = softwp_onboarding_lang.importTemplate;
	softaculous_pro_create_html(message, 90); // Update progress to 100%

	// Make the call
	jQuery.ajax({
		url: softaculous_pro_ajax_url+'action=softaculous_pro_import_template',
		type: 'POST',
		data: spro_temp_form_data+'&softaculous_pro_nonce='+softaculous_pro_ajax_nonce,
		dataType: 'json',
		success: function(data){
			var modal = jQuery('.progress-bar');
			if(typeof data === 'object' && 'done' in data){		
				softaculous_pro_create_html(softwp_onboarding_lang.setupCompleted, 100, true); // Update message here
				modal.find('.progress-text').text(softwp_onboarding_lang.congratulations+'🎊');
				modal.find('.skeleton-loader').hide();
				modal.find('.softaculous-pro-done').show();
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			softaculous_pro_show_error({err: 'AJAX failure ! Status : '+textStatus+' | Error : '+errorThrown});
		}
	});
  
}

function softaculous_pro_show_error(err){

	var html = '<div class="setup-error"><div class="setup-error-message"><span class="dashicons dashicons-info-outline"></span><h1>Error</h1></div><ul>';

	for(var x in err){
		html += '<li>'+err[x]+'</li>';
	}

	html += '</ul></div>';

	jQuery('.softaculous-pro-wizard-content').append(html);
	jQuery('#softaculous-pro-error-template').html(html).show();
	jQuery('.progress-bar').hide();

}

function softaculous_pro_create_html(message, finalPercentage, slowAnimation, logs) {
	// Check if progress bar already exists
	var progressBar = jQuery('.progress-bar');
	if (progressBar.length > 0) {
		// Update the message and animate the progress
		jQuery('.progress-indicator').text(message);
		softaculous_pro_animateProgress(progressBar.find('.progress-float-r'), progressBar.find('.setup-progress-counter'), finalPercentage, slowAnimation);
	} else {
		// Create the progress bar
		var html = `<div class="progress-bar">
			<h1 class="progress-text">${softwp_onboarding_lang.buildWebsite}</h1>
			<div class="setup-progress-bar">
				<span class="progress-indicator">${message}</span>
				<span class="progress-float-r">0%</span>
				<div class="progress-bar-par">
					<div class="setup-progress-counter" style="width: 0%; background-color: blue"></div>
				</div>
				<div class="skeleton-loader" style="display:none;">
					<div class="skeleton-loader-shadow"></div>
					<div class="skeleton-row">
						<div class="skeleton-row-heading" style="width:50px; height:50px;border-radius:100%;"></div>
						<div class="skeleton-row-para" style="width:70%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-img" style="width:100%;"></div>
					</div>
					<div class="skeleton-row">
						<div class="skeleton-row-heading" style="width:50px; height:50px;border-radius:100%;"></div>
						<div class="skeleton-row-para" style="width:70%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-row-para" style="width:100%; height:20px"></div>
						<div class="skeleton-img"  style="width:100%;"></div>
						</div>
					</div>
				</div>					
				<div class="softaculous-pro-done" style="display: none">
				<a class="button softaculous-pro-demo-btn" href="${soft_pro_obj.site_url}" target="_blank"
				>Visit Website</a> &nbsp;&nbsp;
				<a class="button softaculous-pro-demo-btn" href="${soft_pro_obj.admin_url}" target="_blank"
				>WordPress Dashboard</a> &nbsp;&nbsp;
				<a class="button softaculous-pro-demo-btn" href="${soft_pro_obj.admin_url}admin.php?page=assistant" target="_blank"
				>Assistant</a>
			</div>
		</div>
		<div class="spro-setup-progress-logs">
			<b> Some Error Occurred: </b>
			<ul class="failed-progress-logs">
			</ul>
		</div>`;
		
		jQuery('.softaculous-pro-wizard-content').append(html);
		softaculous_pro_animateProgress(jQuery('.progress-float-r'), jQuery('.setup-progress-counter'), finalPercentage, slowAnimation);
	}
	
	if(logs && typeof ele === 'object'){
		jQuery('.spro-setup-progress-logs').show();
		Object.entries(logs[0]).map(entry => {
			console.log(entry[0], entry[1]);			
			jQuery('.failed-progress-logs').append('<li class="spro-failed-ins-li">'+ entry[1] + '</li>');
		});
	}
}
	
function softaculous_pro_animateProgress($progressText, $progressBar, finalPercentage, slowAnimation) {
	var currentPercentage = parseInt($progressText.text());
	var increment = 1; 
	var duration = 10;
	
	if (slowAnimation) {
		duration = 100;
	}
	
	var interval = setInterval(function() {
		if (currentPercentage >= finalPercentage) {  
			clearInterval(interval);
			$progressText.text(finalPercentage + '%');
		} else {
			currentPercentage += increment;
			$progressText.text(currentPercentage + '%');
			$progressBar.animate({ width: currentPercentage + '%' }, duration).css('background-color', 'blue');
		}
		
		if(currentPercentage === 100){

		}
	}, duration);
}
