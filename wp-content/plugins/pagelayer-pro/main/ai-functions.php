<?php
// Are we being accessed directly ?
if(!defined('PAGELAYER_VERSION')) {
	exit('Hacking Attempt !');
}

// Add tmp attribute to block code
function pagelayer_pro_replace_content_atts($content, $new_atts){
	
	$blocks = parse_blocks( $content );
	$output = '';
	
	foreach ( $blocks as $block ) {
		$block_name = $block['blockName'];
		
		// Is pagelayer block
		if ( is_string( $block_name ) && 0 === strpos( $block_name, 'pagelayer/' ) ) {
			$_block = pagelayer_pro_add_atts_block($block, $new_atts);
			$output .= serialize_block($_block);
			continue;
		}
		
		$output .= serialize_block($block);
	}
		
	return $output;
}

function pagelayer_pro_add_atts_block($block, $new_atts){
	global $pagelayer;
	
	// Load shortcode
	pagelayer_load_shortcodes();
	
	if(empty($block['attrs']['pagelayer-id'])) return $block;
	
	$pl_id = $block['attrs']['pagelayer-id'];
	if (isset($new_atts[$pl_id]) && is_array($new_atts[$pl_id])){
		$block['attrs'] = array_merge($block['attrs'], $new_atts[$pl_id]);
		
		// If block saved by Pagelayer Editor
		if(in_array( $block['blockName'], ['pagelayer/pl_inner_col', 'pagelayer/pl_inner_row'])){
			$block['blockName'] = str_replace('inner_', '', $block['blockName']);
		}
		
		$tag = substr( $block['blockName'], 10 );
		$pl_tag = str_replace('-', '_', $tag);
		
		if(isset($pagelayer->shortcodes[$pl_tag])){
			// Create attribute Object
			
			$innerHTML = pagelayer_isset($pagelayer->shortcodes[$pl_tag], 'innerHTML');
			if(!empty($innerHTML) && isset($block['attrs'][$innerHTML])){
				$block['innerHTML'] = $block['attrs'][$innerHTML];
				$block['innerContent'] = array($block['attrs'][$innerHTML]);
			}
			
		}
	}
	
	// This have innerBlocks
	if(!empty($block['innerBlocks']) && is_array($block['innerBlocks'])){
		foreach($block['innerBlocks'] as $key => $inner_block){
			$block['innerBlocks'][$key] = pagelayer_pro_add_atts_block($inner_block, $new_atts);
		}
	}
	
	return $block;
}

// Add tmp attribute to block code
function pagelayer_pro_extract_editable_atts($content){
	
	$blocks = parse_blocks( $content );
	
	$el_atts = array();
	foreach( $blocks as $block ){
		$block_name = $block['blockName'];
		
		// Is pagelayer block
		if( is_string( $block_name ) && 0 === strpos( $block_name, 'pagelayer/' ) ){
			pagelayer_pro_parse_ai_atts($block, $el_atts);
		}
		
	}
		
	return array_filter($el_atts);
}

function pagelayer_pro_parse_ai_atts($block, &$el_atts){
	global $pagelayer;
	
	// Load shortcode
	pagelayer_load_shortcodes();
	
	// TODO: if empty then assign id and updated content
	if(empty($block['attrs']['pagelayer-id'])){
		return;
	}
	
	// If block saved by Pagelayer Editor
	if(in_array( $block['blockName'], ['pagelayer/pl_inner_col', 'pagelayer/pl_inner_row'])){
		$block['blockName'] = str_replace('inner_', '', $block['blockName']);
	}
	
	$tag = substr( $block['blockName'], 10 );
	$pl_tag = str_replace('-', '_', $tag);
	
	if(isset($pagelayer->shortcodes[$pl_tag])){
	
		// Create attribute Object
		$pl_props = $pagelayer->shortcodes[$pl_tag];
		$pl_id = $block['attrs']['pagelayer-id'];
		$el_atts[$pl_id] = array();
		
		foreach($pagelayer->tabs as $tab){
			
			if(empty($pl_props[$tab])){
				continue;
			}
			
			foreach($pl_props[$tab] as $section => $_props){
				
				$props = !empty($pl_props[$section]) ? $pl_props[$section] : $pagelayer->styles[$section];
				
				if(empty($props)){
					continue;
				}
				
				// Reset / Create the cache
				foreach($props as $prop => $param){
					
					// No value set
					if(empty($block['attrs'][$prop]) || (isset($param['ai']) && $param['ai'] === false)){
						continue;
					}
					
					$has_attrs = false;
					
					// is editable?
					if(!empty($param['edit'])){
						$el_atts[$pl_id][$prop] = $block['attrs'][$prop];
						$has_attrs = true;
					}
					
					// is editable?
					if(!empty($param['type']) && $param['type'] == 'image'){
						$el_atts[$pl_id]['img_urls'][] = $block['attrs'][$prop];
					}
					
					// is editable?
					if(!empty($param['type']) && $param['type'] == 'multi_image'){
						$el_atts[$pl_id]['img_urls'][] = $block['attrs'][$prop];
					}
					
					if($has_attrs){
						$el_atts[$pl_id]['blockName'] = $pl_props['name'];
					}
					
				}
			}
		}
		
	}
		
	// This have innerBlocks
	if(!empty($block['innerBlocks'])){
		foreach($block['innerBlocks'] as $key => $inner_block){
			pagelayer_pro_parse_ai_atts($inner_block, $el_atts);
		}
	}
	
}

// Call to AI server
function pagelayer_pro_ai_prompt_run($ai_data = array()){
	global $pagelayer;

	if(empty($pagelayer->license) || empty($pagelayer->license['license'])){
		return null;
	}
	
	// Only SoftWP license works
	$ai_data['license'] = $pagelayer->license['license'];
	$ai_data['url'] = site_url();
	
	$response = wp_remote_post(PAGELAYER_AI_API, [
		'body'	=> $ai_data,
		'timeout' => 600,
	]);

	$body = wp_remote_retrieve_body($response);
	$result = json_decode($body, true);

	// pagelayer_print($result);
	if(isset($result['error'])){
		// TODO: show this error error if possible
		error_log('API Request Failed: ' . $result['error']);
		return null;
	}
	
	if(isset($result['response'])){
		$json_content = $result['response'];
		
		// Remove markdown code fences if they exist
		if(strpos($json_content, '```') !== false){
			$json_content = preg_replace('/^```(?:json)?\s*/', '', trim($json_content));
			$json_content = preg_replace('/\s*```$/', '', $json_content);
		}

		$generated = json_decode($json_content, true);
	
		if (json_last_error() === JSON_ERROR_NONE) {
			return $generated;
		} else {
			error_log("JSON decode error: " . json_last_error_msg());
			return null;
		}
	}
	
	return null;
}

// The actual function to import the theme
function pagelayer_pro_generate_ai_contents($content, $args = array()){	
	
	if(empty($args['description'])){		
		return $content;
	}
	
	if(defined('PAGELAYER_BLOCK_PREFIX') && PAGELAYER_BLOCK_PREFIX == 'wp'){
		$content = str_replace('<!-- sp:pagelayer', '<!-- wp:pagelayer', $content);
		$content = str_replace('<!-- /sp:pagelayer', '<!-- /wp:pagelayer', $content);
	}
	
	if(!pagelayer_has_blocks($content)){
		return $content;
	}
	
	$site_name = get_bloginfo('name');
	$user_context = $args['description'];
	
	$widgets_attrs = pagelayer_pro_extract_editable_atts($content);
	
	if(empty($widgets_attrs)){
		return $content;
	}
	
	$images = !empty($args['images']) ? $args['images'] : [];
	
	$image_urls = json_encode(array_map(function($item) {
		$url = $item['image_url']; 
		if (strpos($url, 'https://images.pexels.com/photos/') === 0 ) {
			return strtok( $url, '?' ); 
		}
		return $url;
	}, $images));

	$widgets_attrs_json = json_encode( $widgets_attrs );
	
	$data = [
		'request_type' => 'builder_replacer',
		'site_name' => $site_name,
		'user_context' => $user_context,
		'image_urls' => $image_urls,
		'widgets_attrs_json' => $widgets_attrs_json
	];

	$new_atts = pagelayer_pro_ai_prompt_run($data);
	
	if(empty($new_atts)){
		
		if(empty($args['retry'])){
			$args['retry'] = 1;
			
			$content = pagelayer_pro_generate_ai_contents($content, $args);
		}

		return $content;
	}
	
	$updated_content = !empty($new_atts['updated_content'])? $new_atts['updated_content'] : array();
	$updated_images = !empty($new_atts['images'])? $new_atts['images'] : array();
	
	$unconverted = pagelayer_pro_unconverted_fields($widgets_attrs, $updated_content, $updated_images);
	
	// Regenerate if missing anything
	if(!empty($unconverted)){
		$data['widgets_attrs_json'] = json_encode($unconverted);

		$_new_atts = pagelayer_pro_ai_prompt_run($data);
		
		if(!empty($_new_atts)){
			
			if(!empty($_new_atts['updated_content']) && is_array($_new_atts['updated_content'])){
				$new_atts['updated_content'] = array_merge($new_atts['updated_content'], $_new_atts['updated_content']);
			}
			
			if(!empty($_new_atts['images']) && is_array($_new_atts['images'])){
				foreach($_new_atts['images'] as $img){
					$new_atts['images'][] = $img;
				}
			}
		}
	}
	
	$content = pagelayer_pro_replace_content_atts($content, $new_atts['updated_content']);
	// Replace old images with new images
	if(!empty($new_atts['images']) && is_array($new_atts['images'])){
		
		// Build a lookup map for replaced images
		$mapImages = [];
		foreach($new_atts['images'] as $img) {
			if (isset($img['old'], $img['new'])) {
				$mapImages[$img['old']] = $img['new'];
			}
		}
	
		foreach($mapImages as $oldImg => $newImg){
			// Get the dimensions of the old image (assuming the path is local)
			$template_path = $GLOBALS['softwpai_template_path'];
			$oldImagePath = str_replace('{{theme_url}}', $template_path, $oldImg); // Replace theme_url placeholder
			
			// Assuming that the old image is local and can be fetched
			$size = @getimagesize($oldImagePath);
			$size = $size ? $size : [0, 0];
		
			list($width, $height) = $size;
			$new_url = $newImg;
			
			// Check if the new image URL is from Pexels
			if (strpos($new_url, 'https://images.pexels.com/photos/') === 0 && $width && $height) {
				// Parse existing query parameters and remove width/height
				$parsed_url = parse_url( $new_url );
				parse_str( isset( $parsed_url['query'] ) ? $parsed_url['query'] : '', $query );
				unset( $query['w'], $query['h'] );

				// Ensure optimization parameters are included
				$optimize_defaults = [
					'auto' => 'compress',
					'cs'   => 'tinysrgb',
					'dpr'  => '1',
					'fit'  => 'crop',
					'pl'  => pagelayer_RandomString(4), // Set unique ID for each image to import each suggested image
				];

				// Merge defaults if not already set
				$query = array_merge( $optimize_defaults, $query );

				// Add width and height
				$query['w'] = (int) $width;
				$query['h'] = (int) $height;

				// Rebuild the full URL
				$base = strtok( $new_url, '?' );
				$new_url = $base . '?' . http_build_query( $query );
			}
		
			// Replace old image URL with the new one in the content
			$content = str_replace($oldImg, $new_url, $content);
		}
	}

	return $content;
}

// Helper function to detect image URLs for import only
function pagelayer_pro_is_image_url($str) {
	return is_string($str) && preg_match('/\.(jpe?g|png|gif)(\?.*)?$/i', $str) || strpos($str, '{{theme_url}}') !== false;
}

function pagelayer_pro_unconverted_fields($inputWidgets, $updatedContent, $updatedImages){
	$unconverted = [];
	
	if(empty($updatedContent)){
		return $inputWidgets;
	}

	// Build a lookup map for replaced images
	$convertedImages = [];
	foreach ($updatedImages as $img) {
		if (isset($img['old'], $img['new'])) {
			$convertedImages[$img['old']] = $img['new'];
		}
	}

	foreach($inputWidgets as $widgetId => $widgetData){
		foreach ($widgetData as $field => $value) {
			
			// Skip field
			if($field == 'blockName'){
				continue;
			}
			
			// IMAGE FIELD CHECK (dynamic detection)
			if($field == 'img_urls'){
				foreach ($value as $imgUrl) {
					$imgUrls = is_string($imgUrl) ? explode(',', $imgUrl) : (is_array($imgUrl) ? $imgUrl : []);
					foreach ($imgUrls as $url) {
						if (is_string($url)) {
							$url = trim($url);
							if (pagelayer_pro_is_image_url($url) && !isset($convertedImages[$url])) {
								$unconverted[$widgetId][$field][] = $url;
							}
						}
					}
				}
				
				continue;
			}
			
			// TEXT FIELD CHECK
			if (is_string($value) && strip_tags($value) !== '' && !is_numeric($value)) {
				$original = trim($value);
				$updated = $updatedContent[$widgetId][$field] ? $updatedContent[$widgetId][$field] : '';
				if (trim($updated) === '' || trim($updated) === $original) {
					$unconverted[$widgetId][$field] = $original;
				}
			}
		}
	}

	return $unconverted;
}

// Download external images like pexels
function pagelayer_pro_download_external_images($content) {
	global $pagelayer;
	
	if (empty($content)){ 
		return $content;
	}

	if (defined('PAGELAYER_BLOCK_PREFIX') && PAGELAYER_BLOCK_PREFIX == 'wp') {
		$content = str_replace('<!-- sp:pagelayer', '<!-- wp:pagelayer', $content);
		$content = str_replace('<!-- /sp:pagelayer', '<!-- /wp:pagelayer', $content);
	}

	if (!pagelayer_has_blocks($content)) return $content;

	$widgets_attrs = pagelayer_pro_extract_editable_atts($content);
	if (empty($widgets_attrs)) return $content;

	$processed_images = [];

	foreach($widgets_attrs as $widget_id => $attrs) {
		if( empty($attrs['img_urls']) || !is_array($attrs['img_urls'])){
			continue;
		}
		
		foreach($attrs['img_urls'] as $image_url) {
			$imgUrls = is_string($image_url) ? explode(',', $image_url) : (is_array($image_url) ? $image_url : []);
			foreach ($imgUrls as $url) {
				if (!is_string($url)){
					continue;
				}
				
				$url = trim($url);
				
				// Caching the image
				if(strpos($url, 'https://images.pexels.com/photos/') === false || isset($pagelayer->import_media[$url])){
					continue;
				}

				// Get ilename
				$filename = basename(strtok($url, '?'));
				
				// We are going to create a loop to find the image
				for($i = 1; $i <= 3; $i++){
					// Upload the image
					$ret = pagelayer_upload_media($filename, file_get_contents($url));
					
					// Lets check the file exists ?
					if(!empty($ret)){
						
						// Lets check if the file exists
						$tmp_image_path = pagelayer_cleanpath(get_attached_file($ret));
						
						// If the file does not exist, simply delete the old upload as well
						if(!file_exists($tmp_image_path)){
							wp_delete_attachment($ret, true);
							$ret = false;
						
						// The image does exist and we can continue
						}else{
							break;
						}
						
					}
				}
				
				if(empty($ret)){
					continue;
				}
				
				// This replaces images when inserting content
				$pagelayer->import_media[$url] = $ret;
				
				$imgs_json = array('sitepad_img_source' => 'pexels.com', 'sitepad_download_url' => $url, 'sitepad_img_lic' => '');
				$fields = array('sitepad_img_source', 'sitepad_download_url', 'sitepad_img_lic');
				
				foreach($fields as $field){
					if(!empty($imgs_json[$field])){
						update_post_meta($ret, $field, $imgs_json[$field]);
					}
				}
			}
		}
	}

	return $content;
}
