<?php

namespace SoftWP;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT');
}

class AI{

	static function enqueue_scripts(){
		
		global $softaculous_pro;

		wp_enqueue_style('softaculous-pro-ai', SOFTACULOUS_PRO_PLUGIN_URL . '/assets/css/ai.css', [], SOFTACULOUS_PRO_VERSION);
		wp_enqueue_script('softaculous_ai_base', SOFTACULOUS_PRO_PLUGIN_URL . 'assets/js/ai/ai.js', ['jquery'], SOFTACULOUS_PRO_VERSION, ['strategy' => 'defer']);

		$soft_ai = [
			'i18n' => [],
			'nonce' => wp_create_nonce('softaculous_ai_nonce'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'ai_icon_url' => SOFTACULOUS_PRO_PLUGIN_URL .'/assets/images/softaculous-ai.svg',
			'buy' => esc_url(SOFTACULOUS_PRO_AI_BUY.'&softwp_lic='.$softaculous_pro['license']['license'])
		];
		
		$tokens = get_option('softaculous_ai_tokens', []);
		if(!empty($tokens)){
			$soft_ai['tokens'] = $tokens;
		}

		wp_localize_script('softaculous_ai_base', 'soft_ai', $soft_ai);
		
		// Loading the assets data
		$asset_file = [];
		$asset_file = include_once(SOFTACULOUS_PRO_PLUGIN_PATH . 'assets/js/ai/build/index.asset.php');
		if(is_array($asset_file)){
			array_push($asset_file['dependencies'], 'softaculous_ai_base'); // Adding the ai base js dep
		}

		wp_enqueue_script(
			'softaculous_ai_gb',
			SOFTACULOUS_PRO_PLUGIN_URL . 'assets/js/ai/build/index.js', // Load from the build directory
			$asset_file['dependencies'],
			$asset_file['version'],
			['strategy' => 'defer']
		);	
		
		// The API provides better output as markdown compared to html, thats why we need this markdown library to convert the markdown to html.
		wp_enqueue_script(
			'softaculous_ai_marked',
			SOFTACULOUS_PRO_PLUGIN_URL . 'assets/js/marked.min.js',
			array('softaculous_ai_base'),
			SOFTACULOUS_PRO_VERSION,
			['strategy' => 'defer']
		);
	}
	
	static function generate(){
		global $softaculous_pro, $softaculous_pro_settings;
		
		check_admin_referer('softaculous_ai_nonce', 'nonce');
		
		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required privilege', 'softaculous-pro'));
		}
		
		set_time_limit(100);
		
		$content = !empty($_REQUEST['content']) ? wp_kses_post(wp_unslash($_REQUEST['content'])) : '';
		$prompt = !empty($_REQUEST['prompt']) ? wp_kses_post(wp_unslash($_REQUEST['prompt'])) : '';
		$shortcut = !empty($_REQUEST['shortcut']) ? sanitize_text_field(wp_unslash($_REQUEST['shortcut'])) : '';
		$license = !empty($softaculous_pro['license']['license']) ? $softaculous_pro['license']['license'] : '';

		if(empty($license)){
			wp_send_json_error(__('Please link a license to keep using the AI feature', 'softaculous-pro'));
		}
		
		// We need atleast a prompt or a shortcut
		if(empty($prompt) && empty($shortcut)){
			wp_send_json_error(__('Please write a Prompt, for the AI to generate content', 'softaculous-pro'));
		}
		
		// Shortcut requires content, but a prompt does not,
		// shortcuts are meant to be used to process content,
		// where as a Prompt can be used to process as well as generate content.
		if(!empty($shortcut) && empty($content)){
			wp_send_json_error(__('AI shortcut can not be applied without any content.', 'softaculous-pro'));
		}

		$res = wp_remote_post(SOFTACULOUS_PRO_AI_API, [
			'timeout' => 90,
			'body' => [
				'prompt' => $prompt,
				'shortcut' => $shortcut,
				'content' => $content,
				'license' => $license,
				'url' => site_url()
			]
		]);

		if(empty($res)){
			wp_send_json_error(__('Unable to complete this request', 'softaculous-pro'));
		}
		
		if(is_wp_error($res)){
			$error_string = $res->get_error_message();
			wp_send_json_error($error_string);
		}

		$res_code = wp_remote_retrieve_response_code($res);
		$body = wp_remote_retrieve_body($res);
		
		if(empty($body)){
			wp_send_json_error(__('The AI API responsed with empty response, Response Code:', 'softaculous-pro') . $res_code);
		}

		$ai_content = json_decode($body, true);

		if($res_code > 299){
			$error = !empty($ai_content['error']) ? sanitize_text_field($ai_content['error']) : __('Unexpected response code returned from AI API: ', 'softaculous-pro') . $res_code;
			
			wp_send_json_error($error);
		}

		// Building the history data
		if(!isset($ai_content['error']) && isset($softaculous_pro_settings['ai_history_duration']) && $softaculous_pro_settings['ai_history_duration'] > 0 && !empty($ai_content['ai'])){
			$history_data = [];
			if(!empty($content)){
				$history['content'] = wp_kses_post(wp_unslash($content));
				$title = (strlen($content) > 100 ? substr($content, 100) : $content);
			}

			if(!empty($prompt)){
				$history['prompt'] = wp_kses_post(wp_unslash($prompt));
				$title = (strlen($prompt) > 100 ? substr($prompt, 100) : $prompt);
			}

			if(empty($prompt) && !empty($shortcut)){
				$history['shortcut'] = sanitize_text_field($shortcut);
				$title = self::parse_shortcuts($shortcut);
			}

			if(!empty($ai_content['ai'])){
				$history['assistant'] = wp_kses_post(wp_unslash($ai_content['ai']));
			}
			
			if(!empty($ai_content['total_tokens'])){
				$title .= '|||'.$ai_content['total_tokens'];
			}

			// Saving the history
			wp_insert_post([
				'post_title' => sanitize_text_field($title),
				'post_type' => 'spro_ai_history',
				'post_content' => serialize($history),
			]);
		}

		// Saving token data
		if(!empty($ai_content['remaining_tokens'])){
			update_option('softaculous_ai_tokens', [
				'remaining_tokens' => sanitize_text_field($ai_content['remaining_tokens'])
			], false);
		}
		
		wp_send_json_success($ai_content);

	}
	
	static function load_history(){
		check_admin_referer('softaculous_ai_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required privilege', 'softaculous-pro'));
		}
		
		$history_id = !empty($_POST['history_id']) ? sanitize_text_field(wp_unslash($_POST['history_id'])) : 0;
		$offset = !empty($_POST['offset']) ? (int) sanitize_text_field(wp_unslash($_POST['offset'])) : 0;
		$history = [];
		
		if(!empty($history_id)){
			$query = new \WP_Query([
				'post_type' => ['spro_ai_history'],
				'page_id' => $history_id,
			]);
			
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					$history = unserialize(get_the_content());
				}
			}
			
			// Restore to original post
			wp_reset_postdata();
			wp_send_json_success($history);

		} else {
			$query = new \WP_Query([
				'post_type' => ['spro_ai_history'],
				'posts_per_page' => 20,
				'offset' => $offset,
				'orderby' => 'date',
				'order' => 'DESC',
			]);
		}

		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				$single['token_used'] = 0;
				$single['title'] = get_the_title();
				$single['date'] = get_the_date();
				$single['id'] = get_the_ID();
				
				if(strpos($single['title'], '|||') !== FALSE) {
					$get_count = explode('|||', $single['title']);
					$single['title'] = $get_count[0];
					$single['token_used'] = $get_count[1];
				}

				array_push($history, $single);
			}
		}
		
		// Restore to original post
		wp_reset_postdata();
		
		$total_count = wp_count_posts('spro_ai_history')->draft; // Getting the total count
		wp_send_json_success(['history' => $history , 'total' => $total_count]);
	}
	
	static function parse_shortcuts($shortcut){
		$supported_languages = ['arabic', 'chinese', 'czech', 'danish', 'dutch', 'english', 'finnish', 'french', 'german', 'greek', 'hindi', 'hebrew', 'hungarian', 'indonesian', 'italian', 'japanese', 'korean', 'marathi', 'punjabi', 'persian', 'polish', 'portuguese', 'russian', 'spanish', 'swedish', 'thai', 'turkish', 'vietnamese'];
		$supported_tones = ['casual', 'confidence', 'formal', 'friendly', 'inspirational', 'motivational', 'nostalgic', 'playful', 'professional', 'scientific', 'straightforward', 'witty'];
		$other_shortcuts = ['shorter' => 'Make it shorter', 'longer' => 'Make it longer', 'simplify' => 'Simplify the language', 'grammar' => 'Fix Spelling and Grammar'];
		
		if(in_array($shortcut, $supported_languages)){
			return 'Translate to ' . ucfirst($shortcut);
		}
		
		if(in_array($shortcut, $supported_tones)){
			return 'Change the tone of the content to ' . ucfirst($shortcut);
		}
		
		if(array_key_exists($shortcut, $other_shortcuts)){
			return $other_shortcuts[$shortcut];
		}

		return $shortcut;
	}
	
	static function delete_history($delete_all = false){

		$settings = get_option('softaculous_pro_settings', []);
		$history_duration = (int) $settings['ai_history_duration'];

		// If it is off then we wont delete the history.
		if($history_duration < 1){
			return;
		}

		$x_days_ago = date('Y-m-d', strtotime('-'.$history_duration.' days'));

		$query = new \WP_Query([
			'post_type' => 'spro_ai_history',
			'post_status' => 'draft',
			'date_query'=> [
				[
					'column' => 'post_date',
					'before' => $x_days_ago,
				],
			],
			'posts_per_page' => 100,
		]);

		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				wp_delete_post(get_the_ID(), true);
			}

			wp_reset_postdata();
		}
	}
	
	static function update_tokens(){
		global $softaculous_pro;

		check_admin_referer('softaculous_ai_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(__('You do not have required privilege', 'softaculous-pro'));
		}

		$license = !empty($softaculous_pro['license']['license']) ? $softaculous_pro['license']['license'] : '';

		if(empty($license)){
			wp_send_json_error(__('Please link a license to keep using the AI feature', 'softaculous-pro'));
		}

		$res = wp_remote_post(SOFTACULOUS_PRO_AI_API, [
			'timeout' => 30,
			'body' => [
				'license' => $license,
				'url' => site_url(),
				'request_type' => 'token_refresh',
			]
		]);

		if(empty($res)){
			wp_send_json_error(__('Unable to complete this request', 'softaculous-pro'));
		}

		if(is_wp_error($res)){
			$error_string = $res->get_error_message();
			wp_send_json_error($error_string);
		}

		$res_code = wp_remote_retrieve_response_code($res);
		$body = wp_remote_retrieve_body($res);

		if(empty($body)){
			wp_send_json_error(__('The AI API responded with empty response, Response Code:', 'softaculous-pro') . $res_code);
		}

		$token_data = json_decode($body, true);

		if($res_code > 299){
			$error = !empty($token_data['error']) ? sanitize_text_field($token_data['error']) : __('Unexpected response code returned from AI API: ', 'softaculous-pro') . $res_code;
			
			wp_send_json_error($error);
		}

		if(!isset($token_data['remaining_tokens'])){
			wp_send_json_error(__('No token data returned', 'softaculous-pro'));
		}
		
		update_option('softaculous_ai_tokens', [
			'remaining_tokens' => sanitize_text_field($token_data['remaining_tokens'])
		], false);

		wp_send_json_success(esc_html($token_data['remaining_tokens']));
	}
	
}
