(function($, nonce, admin_url, ajax_url){
$(document).ready(function(){

	$(document).on("click", ".my-soft-pro-dismiss-notice .notice-dismiss", function(){
		var data = {
			action: "softaculous_pro_wp_ajax",
			softaculous_pro_security: nonce
		};
		$.post(ajax_url, data, function(response){});
	});

	//View connection key script
	var soft_pro_conn_key_dialog = $("#soft_pro_connection_key_dialog");
	$("#soft_pro_connection_key").click(function(e) {
		e.preventDefault();
		soft_pro_conn_key_dialog.dialog({
			draggable: false,
			resizable: false,
			modal: true,
			width: "1070px",
			height: "auto",
			title: "Softaculous Connection Key",
			close: function() {
				$(this).dialog("destroy");
			}
		});
	});

	$("#soft_pro_promo .soft_pro_promo-close").click(function(){
		var data = {
			softaculous_pro_security: nonce
		};

		// Hide it
		$("#soft_pro_promo").hide();
		
		// Save this preference
		$.post(admin_url + '?softaculous_pro_promo=0', data, function(response) {
			//alert(response);
		});
	});

	function dotweet(ele){
		window.open($("#"+ele.id).attr("action")+"?"+$("#"+ele.id).serialize(), "_blank", "scrollbars=no, menubar=no, height=400, width=500, resizable=yes, toolbar=no, status=no");
		return false;
	}
		
	$('.spro-plugin-install-btn').click(function(){

		$(this).replaceWith('<i class="fa fa-spin fa-2x fa-circle-notch sp-2 smr-3"></i>');

		var data = {
			action: "softaculous_pro_wp_ajax",
			softaculous_pro_install_plugin: 1,
			plugin: $(this).attr("name"),
			softaculous_pro_security: soft_pro_obj.nonce
		};

		$.post(soft_pro_obj.ajax_url, data, function(response){
			if(!response.data || !response.data.form){
				return;
			}

			// Handling FTP Form
			$('body').append(response.data.form);

			var ftp_modal = $('#request-filesystem-credentials-dialog');
			ftp_modal.show();

			// Handling the close btn of the FTP form.
			ftp_modal.find('.cancel-button').on('click', function(event){
				event.preventDefault();
				ftp_modal.hide();
				window.location.reload();
			});

			ftp_modal.on('submit', 'form', function(event){
				event.preventDefault();

				data['hostname'] = ftp_modal.find('#hostname').val();
				data['username'] = ftp_modal.find('#username').val();
				data['password'] = ftp_modal.find('#password').val();
				data['connection_type'] = ftp_modal.find('input[name="connection_type"]:checked').val();
				data['public_key'] = ftp_modal.find('#public_key').val();
				data['private_key'] = ftp_modal.find('#private_key').val();
				data['_fs_nonce'] = ftp_modal.find('#_fs_nonce').val();

				ftp_modal.hide()
				spro_install_plugin_with_ftp(data);
			});
		})
		.done(function(res){
			if(!res.data || !res.data.form){
				window.location.reload();
			}
		});
	});
});
})(jQuery, soft_pro_obj.nonce, soft_pro_obj.admin_url, soft_pro_obj.ajax_url);


// Plugin install request with FTP credentials
function spro_install_plugin_with_ftp(data){
	jQuery.post(soft_pro_obj.ajax_url, data, function(response){})
	.done(function(res){
		window.location.reload();
	});
}

//Cookie setter
function spro_setcookie(name, value, duration){
	value = escape(value);
	if(duration){
		var date = new Date();
		date.setTime(date.getTime() + (duration * 86400000));
		value += "; expires=" + date.toGMTString();
	}
	document.cookie = name + "=" + value;
};

//Gets the cookie value
function spro_getcookie(name){
	value = document.cookie.match('(?:^|;)\\s*'+name+'=([^;]*)');
	return value ? unescape(value[1]) : false;
};

//Removes the cookies
function spro_removecookie(name){
	spro_setcookie(name, '', -1);
};