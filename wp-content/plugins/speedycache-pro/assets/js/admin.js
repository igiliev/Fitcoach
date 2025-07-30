jQuery(document).ready(function($) {
	$('.speedycache-test-notice .speedycache-custom-dismiss').on('click', function(e) {
		e.preventDefault();
		$('.speedycache-test-notice').slideUp();
			$.ajax({
				url: speedycache_pro_ajax.url,
				type: 'POST',
				data: {
					action: 'speedycache_dismiss_test_notice',
					security: speedycache_pro_ajax.nonce
				}
			});
		});	

	$('.speedycache-copy-test-settings').on('click', function(e){
		e.preventDefault();
		$.ajax({
			method : 'GET',
			url : speedycache_pro_ajax.url + '?action=speedycache_copy_test_settings&security='+speedycache_pro_ajax.nonce,
			success: function(res){
				if(res.success){
					alert('The settings has been successfully saved!');
					location.reload(true);
					return;
				}
				if(res.data){
					alert(res.data);
				}
			}
		});
	});
})