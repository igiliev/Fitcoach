<?php
/*
* GoSMTP
* https://gosmtp.net
* (c) Softaculous Team
*/

if(!defined('GOSMTP_VERSION')){
	die('Hacking Attempt!');
}

add_action('wp_ajax_gosmtp_test_mail', 'gosmtp_test_mail');
function gosmtp_test_mail(){
	
	global $phpmailer;

	// Check nonce
	check_admin_referer( 'gosmtp_ajax' , 'gosmtp_nonce' );

	$to = gosmtp_optpost('reciever_test_email');
	$subject = gosmtp_optpost('smtp_test_subject');
	$body = gosmtp_optpost('smtp_test_message');
	
	// TODO: send debug param
	if(isset($_GET['debug'])){
		// show wp_mail() errors
		add_action( 'wp_mail_failed', function( $wp_error ){
			echo "<pre>";
			print_r($wp_error);
			echo "</pre>";
		}, 10, 1 );
	}
	
	$msg = array();
	
	// TODO check for mailer
	if(!get_option('gosmtp_options')){
		$msg['error'] = _('You have not configured SMTP settings yet !');
	}else{
		$result = wp_mail($to, $subject, $body);

		if(!$result){
			$msg['error'] = __('Unable to send mail !').(empty($phpmailer->ErrorInfo) ? '' : ' '.__('Error : ').$phpmailer->ErrorInfo);
		}else{
			$msg['response'] = __('Message sent successfully !');
		}
	}
	
	gosmtp_json_output($msg);
}

