<?php

namespace SoftWP;

// Are we being accessed directly ?
if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Media{
	
	static $compress_content_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
	
	static $edit_content_types = [
							'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 
							'image/x-ms-bmp', 'image/tiff', 'image/webp', 
							'image/vnd.adobe.photoshop', 'application/pdf', 
							'image/x-portable-pixmap', 'image/x-icon', 'image/vnd-ms.dds'
							];
							
	static $edit_quality_content_types = ['image/jpeg', 'image/png', 'image/webp'];

	static function can_manage_media(){
		
		global $post;
		
		if(!is_admin() || !current_user_can('upload_files') || empty($post) || empty($post->ID) || $post->post_type != 'attachment'){
			define('SOFTACULOUS_PRO_CAN_PHOTOPEA_EDIT', 0);
			define('SOFTACULOUS_PRO_CAN_COMPRESS', 0);
			define('SOFTACULOUS_PRO_CAN_REPLACE_MEDIA', 0);
			return false;
		}
		
		define('SOFTACULOUS_PRO_CAN_REPLACE_MEDIA', 1);
		
		$attachment_path = get_attached_file($post->ID);
		$original_size = filesize($attachment_path);
		$original_size_kb = round($original_size / 1024);
		$attachment_mime_type = mime_content_type($attachment_path);

		// The action buttons
		if(in_array($attachment_mime_type, \SoftWP\Media::$edit_content_types)){
			add_action('admin_enqueue_scripts', '\SoftWP\Media::enqueue_photopea_scripts');
			define('SOFTACULOUS_PRO_CAN_PHOTOPEA_EDIT', 1);
		}else{
			define('SOFTACULOUS_PRO_CAN_PHOTOPEA_EDIT', 0);
		}
		
		if(0 && in_array($attachment_mime_type, \SoftWP\Media::$compress_content_types)){
			add_action('admin_enqueue_scripts', '\SoftWP\Media::enqueue_compress_scripts');
			define('SOFTACULOUS_PRO_CAN_COMPRESS', 1);
		}else{
			define('SOFTACULOUS_PRO_CAN_COMPRESS', 0);
		}
		
		return true;
	}

	static function can_photopea_edit(){
		
		if(!defined('SOFTACULOUS_PRO_CAN_PHOTOPEA_EDIT')){
			\SoftWP\Media::can_manage_media();
		}
		
		return SOFTACULOUS_PRO_CAN_PHOTOPEA_EDIT;
	}

	static function can_compress(){
		
		if(!defined('SOFTACULOUS_PRO_CAN_COMPRESS')){
			\SoftWP\Media::can_manage_media();
		}
		
		return SOFTACULOUS_PRO_CAN_COMPRESS;
	}

	static function can_replace_media(){
		
		if(!defined('SOFTACULOUS_PRO_CAN_REPLACE_MEDIA')){
			\SoftWP\Media::can_manage_media();
		}
		
		return SOFTACULOUS_PRO_CAN_REPLACE_MEDIA;
	}

	// Register the meta box
	static function register_meta_box(){
		
		if(!\SoftWP\Media::can_manage_media()){
			return false;
		}
		
		add_meta_box(
			'softaculous_pro_media_meta_box',
			__('Action Buttons', 'softaculous-pro'),
			'\SoftWP\Media::meta_box_callback',
			'attachment',
			'side',
			'low'
		);
	}

	// Callback function to render the meta box
	static function meta_box_callback($post){
		
		// Nonce field for security
		//wp_nonce_field('softaculous_pro_media_meta_box_nonce', 'custom_nonce');

		// The action buttons
		if(\SoftWP\Media::can_photopea_edit()){
			echo '<button type="button" class="button" id="edit_with_photopea" data-image-url="' . esc_url(wp_get_attachment_url($post->ID)) . '" data-original-image-url="' . esc_url(wp_get_attachment_url($post->ID)) . '" style="margin-right: 5px; margin-top: 10px;">'.__('Edit with Photopea', 'softaculous-pro').'</button>';
		}
		
		if(\SoftWP\Media::can_compress()){
		
			$attachment_path = get_attached_file($post->ID);
			$original_size = filesize($attachment_path);
			$original_size_kb = round($original_size / 1024);
			
			echo '<button type="button" class="button compress-image-button" data-image-id="' . $post->ID . '" data-original-size="' . $original_size_kb . '" data-image-type="' . esc_attr(get_post_mime_type($post)) . '"  style="margin-right: 5px; margin-top: 10px;">'.__('Compress Image', 'softaculous-pro').'</button>';
		}
		
		if(\SoftWP\Media::can_replace_media()){
		
			echo '<a href="' . esc_url(admin_url('upload.php?page=assistant&act=media-replace&id=' . $post->ID)) . '" class="button replace-media-button" style="margin-right: 5px; margin-top: 10px; display: inline-block;">'.__('Replace Media', 'softaculous-pro').'</a>';
			
		}
		
	}

	//////////////////////////////////
	// Edit with Photopea 
	//////////////////////////////////

	// Function to enqueue scripts and styles for the Photopea modal
	static function enqueue_photopea_scripts(){
		
		if(!\SoftWP\Media::can_photopea_edit()){
			return false;
		}
		
		add_action('admin_footer', '\SoftWP\Media::enqueue_photopea_button_script');
		
		wp_enqueue_script('softaculous-pro-photopea-edit', SOFTACULOUS_PRO_PLUGIN_URL . 'assets/js/photopea_edit.js', ['jquery'], SOFTACULOUS_PRO_VERSION, true);

		wp_localize_script('softaculous-pro-photopea-edit', 'spro_photopea', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('softaculous_pro_js_nonce')));
		
	}

	// Enqueue the Photopea modal and button script in WordPress
	static function enqueue_photopea_button_script(){
		
		global $post;
		
		if(!\SoftWP\Media::can_photopea_edit()){
			return false;
		}
		
	?>
	<style>
	#softaculous-pro-photopea-modal {
		display: none;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.8);
		z-index: 999999;
		pointer-events: auto; 
	}
	#softaculous-pro-photopea-white-bar {
		position: relative;
		top: 0;
		left: 0;
		width: 100%;
		height: 30px;
		background-color: white;
		z-index: 1000; 
	}
	#softaculous-pro-photopea-iframe {
		width: 100%;
		height: calc(100% - 80px); 
		border: none;
		z-index: 9999; 
		pointer-events: auto; 
	}
	#softaculous-pro-photopea-actions {
		position: absolute;
		bottom: 0;
		left: 0;
		width: 100%;
		height: 50px;
		background: #fff;
		display: flex;
		justify-content: flex-end; 
		align-items: center;
		box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
	}
	#softaculous-pro-photopea-actions .softaculous-pro-quality-section {
		display: flex;
		align-items: center;
		margin-right: auto;
		margin-left: 10px;
	}
	#softaculous-pro-photopea-actions .softaculous-pro-quality-section label {
		margin-right: 5px;
		font-size: 15px;
		font-weight: bold;
		color: black;
		cursor:pointer;
	}
	#softaculous-pro-photopea-actions .softaculous-pro-quality-section input {
		width: 70px;
		padding: 3px;
		text-align: center;
		border: 1px solid #9fa1a6;
		border-radius: 3px;
		color:black;
	}
	#softaculous-pro-photopea-actions button {
		padding: 8px 15px;
		background-color: #0073aa;
		color: white;
		border: none;
		border-radius: 3px;
		cursor: pointer;
		margin-left: 3px; 
	}
	#softaculous-pro-photopea-actions button:hover {
		background-color: #005177;
	}
	</style>

	<div id="softaculous-pro-photopea-modal">
		<div id="softaculous-pro-photopea-white-bar"></div>
		<iframe id="softaculous-pro-photopea-iframe" src=""></iframe>
		<div id="softaculous-pro-photopea-actions">
			<div class="softaculous-pro-quality-section" id="softaculous-pro-quality-input-container" style="display: none;">
				<label for="softaculous-pro-quality-input"><?php _e('Quality', 'softaculous-pro');?>: </label>
				<input type="number" id="softaculous-pro-quality-input" value="100" min="1" max="100" />
			</div>
			<button id="softaculous-pro-save-photopea"><?php _e('Save', 'softaculous-pro');?></button>
			<button id="softaculous-pro-save-close-photopea"><?php _e('Save & Close', 'softaculous-pro');?></button>
			<button id="softaculous-pro-save-as-photopea"><?php _e('Save As', 'softaculous-pro');?></button>
			<button id="softaculous-pro-cancel-photopea"><?php _e('Cancel', 'softaculous-pro');?></button>
		</div>
	</div>
	<?php
	}
	
	static function upload_photopea_image() {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
			$jsonData = file_get_contents('php://input');
			$p = json_decode($jsonData);
			
			if(!is_admin() || !current_user_can('upload_files') || !isset($p->softaculous_pro_security) || !wp_verify_nonce(sanitize_text_field(wp_unslash($p->softaculous_pro_security)), 'softaculous_pro_js_nonce')){
				wp_send_json_error(__('Security Check Failed!', 'softaculous-pro'));
			}

			// Check if a quality parameter is provided, defaulting to 100
			$quality = isset($p->quality) ? intval($p->quality) : 100;
			$quality = max(1, min($quality, 100));

			$base64ImageData = $p->source;
			$save_as_new = (isset($p->save_as_new) ? 1 : 0);
			
			if(empty($save_as_new)){
				$originalImageUrl = $p->original_image_url;
				$attachment_id = attachment_url_to_postid($originalImageUrl);
				if (!$attachment_id) {
					wp_send_json_error(__("Original image attachment not found", 'softaculous-pro'));
					return;
				}

				$filePath = get_attached_file($attachment_id);
				if (!$filePath) {
					wp_send_json_error(__("Original image path not found", 'softaculous-pro'));
					return;
				}
			}else{

				$newImageName = sanitize_file_name($p->new_image_name);  

				$extension = pathinfo($newImageName, PATHINFO_EXTENSION);
				$baseFileName = pathinfo($newImageName, PATHINFO_FILENAME);

				// Sanitize and create a proper path to save the new image in the uploads directory
				$upload_dir = wp_upload_dir();
				$filePath = $upload_dir['path'] . '/' . $newImageName;
				
				$newFileName = basename($filePath);
				
				if(file_exists($filePath)){
					wp_send_json_error(__("File already exists. Please choose another file name", 'softaculous-pro'));
					return;
				}
			}

			// Decode the base64 image data
			$imageData = base64_decode(preg_replace('/^data:image\/(jpeg|png);base64,/', '', $base64ImageData));

			if ($imageData === false) {
				wp_send_json_error(__("Failed to decode base64 image data", 'softaculous-pro'));
				return;
			}
			
			if(empty($quality) || $quality > 99){
				$saved = file_put_contents($filePath, $imageData);
			}else{

				// Create an image resource from the decoded data
				$image = imagecreatefromstring($imageData);
				if ($image === false) {
					wp_send_json_error(__("Failed to create image from data", 'softaculous-pro'));
					return;
				}

				$saved = false;
				if (strpos($filePath, '.jpg') !== false || strpos($filePath, '.jpeg') !== false) {
					
					$saved = imagejpeg($image, $filePath, $quality);

				}elseif (strpos($filePath, '.png') !== false) {
					
					$paletteImage = imagecreatetruecolor(imagesx($image), imagesy($image));
					
					imagesavealpha($paletteImage, true);
					imagealphablending($paletteImage, false);
					$transparency = imagecolorallocatealpha($paletteImage, 0, 0, 0, 127);
					imagecolortransparent($paletteImage, $transparency);
					imagecopyresampled($paletteImage, $image, 0, 0, 0, 0, imagesx($image), imagesy($image), imagesx($image), imagesy($image));

					// Reduce to a set number of colors to save space
					$maxColors = ($quality < 100) ? max((int)($quality / 10), 20) : 256;
					imagetruecolortopalette($paletteImage, true, $maxColors);

					// Set compression level based on quality (0 = no compression, 9 = max compression)
					$compressionLevel = round((100 - $quality) / 100 * 9);
					
					// Don't pass 0 otherwise it will create image larger in size than the original one
					if(empty($compressionLevel)){
						$compressionLevel = 1;
					}
					
					$saved = imagepng($paletteImage, $filePath, $compressionLevel);

				}elseif (strpos($filePath, '.webp') !== false) {
					
					$saved = imagewebp($image, $filePath, $quality);

				}else{
					wp_send_json_error(__("Unsupported image format", 'softaculous-pro'));
					return;
				}

				imagedestroy($image);
			}

			if (!$saved) {
				wp_send_json_error(__("Failed to save the image", 'softaculous-pro'));
				return;
			}
			
			if(!empty($save_as_new)){

				// Attach the new file to the WordPress media library
				$attachment = array(
						'guid'           => $upload_dir['url'] . '/' . basename($filePath),
						'post_mime_type' => mime_content_type($filePath),
						'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filePath)),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

				$attachment_id = wp_insert_attachment($attachment, $filePath);
				
				if(is_wp_error($attachment_id)) {
					wp_send_json_error(__("Failed to insert the new image as attachment", 'softaculous-pro'));
				}
			}
			
			$metadata = wp_generate_attachment_metadata($attachment_id, $filePath);
			if (is_wp_error($metadata)) {
				wp_send_json_error(__("Failed to regenerate image sub-sizes", 'softaculous-pro'));
			}

			wp_update_attachment_metadata($attachment_id, $metadata);
			$new_image_url = wp_get_attachment_url($attachment_id);

			wp_send_json_success(array(
				'new_image_url' => $new_image_url,
				'message' => __('Image saved successfully', 'softaculous-pro'),
				));
		}
	}

	//////////////////////////////////
	// Compress Image 
	//////////////////////////////////

	static function enqueue_compress_scripts(){
		
		if(!\SoftWP\Media::can_compress()){
			return false;
		}
		
		wp_enqueue_script('softaculous_pro_enqueue_compress_scripts', SOFTACULOUS_PRO_PLUGIN_URL .  '/assets/js/compress_image.js', ['jquery'], SOFTACULOUS_PRO_VERSION, true);
		wp_localize_script('softaculous_pro_enqueue_compress_scripts', 'spro_compress', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('softaculous_pro_js_nonce')
			));
		
	}
	
	static function calculate_compressed_size() {

		if(!is_admin() || !current_user_can('upload_files') || !isset($_POST['softaculous_pro_security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['softaculous_pro_security'])), 'softaculous_pro_js_nonce')){
			wp_send_json_error(__('Security Check Failed!', 'softaculous-pro'));
		}

		if (isset($_POST['image_id'])) {
			$image_id = intval($_POST['image_id']);
			$image_path = get_attached_file($image_id);

			if ($image_path) {
				$compressed_image_path = \SoftWP\Media::compress_image($image_path, 40, false);
				$compressed_size = round(filesize($compressed_image_path) / 1024);

				wp_send_json_success(array('compressed_size' => $compressed_size));
			}else {
				wp_send_json_error(__('Invalid image path.', 'softaculous-pro'));
			}
		}else {
			wp_send_json_error(__('No image ID provided.', 'softaculous-pro'));
		}
	}

	static function compress_image($image_path, $quality, $save_image = true) {
		$info = getimagesize($image_path);
		$compressed_image_path = '';

		if ($info['mime'] == 'image/jpeg') {
	  $image = imagecreatefromjpeg($image_path);
	  $compressed_image_path = $save_image ? $image_path : str_replace('.jpg', '-temp.jpg', $image_path);
	  imagejpeg($image, $compressed_image_path, $quality);

		}elseif ($info['mime'] == 'image/png') {
				$image = imagecreatefrompng($image_path);
				$compressed_image_path = $save_image ? $image_path : str_replace('.png', '-temp.png', $image_path);

				// Convert to a palette-based image to reduce bit depth
				$paletteImage = imagecreatetruecolor(imagesx($image), imagesy($image));
				imagecopy($paletteImage, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

				// Reduce to a set number of colors to save space
				$maxColors = ($quality < 100) ? max((int)($quality / 10), 20) : 256;
				imagetruecolortopalette($paletteImage, true, $maxColors);

				// Set compression level based on quality (0 = no compression, 9 = max compression)
				$compressionLevel = ($quality < 100) ? 9 - (int)($quality / 10) : 9;

				imagepng($paletteImage, $compressed_image_path, $compressionLevel);

		}elseif ($info['mime'] == 'image/webp') {
				$image = imagecreatefromwebp($image_path);
				$compressed_image_path = $save_image ? $image_path : str_replace('.webp', '-temp.webp', $image_path);
				imagewebp($image, $compressed_image_path, $quality);
		}else {
				return __('Unsupported image type.', 'softaculous-pro');
		}

		return $compressed_image_path;
	}
	
	static function replace_compressed_image() {

		if(!is_admin() || !current_user_can('upload_files') || !isset($_POST['softaculous_pro_security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['softaculous_pro_security'])), 'softaculous_pro_js_nonce')){
			wp_send_json_error(__('Security Check Failed!', 'softaculous-pro'));
		}

		if (isset($_POST['image_id'])) {
			$image_id = intval($_POST['image_id']);
			$image_path = get_attached_file($image_id);

			if ($image_path) {
				$compressed_image_path = \SoftWP\Media::compress_image($image_path, 40, true);
				if ($compressed_image_path) {
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata($image_id, $compressed_image_path);
					wp_update_attachment_metadata($image_id, $attach_data);

					$new_image_url = wp_get_attachment_url($image_id);

					wp_send_json_success(array('message' => __('Image has been successfully compressed and replaced.', 'softaculous-pro'), 'new_image_url' => $new_image_url));
				}else {
					wp_send_json_error(__('Compression and replacement failed.', 'softaculous-pro'));
				}
			}else {
				wp_send_json_error(__('Invalid image path.', 'softaculous-pro'));
			}
		}else {
			wp_send_json_error(__('No image ID provided.', 'softaculous-pro'));
		}
	}

	//////////////////////////////////
	// Replace Media 
	//////////////////////////////////
	
	// Add Replace Media link on Media page in List Mode
	static function add_media_row_action($actions, $post){
		
		if(!\SoftWP\Media::can_replace_media()){
			return $actions;
		}
		
		$url = admin_url('upload.php');
		
		$url = add_query_arg(array(
			'page' => 'assistant',
			'act' => 'media-replace',
			'id' => $post->ID,
		), $url);
		
		$actions['softaculous_pro_replace_media'] = '<a href="'.esc_url($url).'" rel="permalink">'.esc_html__('Replace Media', 'softaculous_pro').'</a>';
		
		// Remove it from Pagelayer as we are adding our own
		if(isset($actions['pagelayer_replace_media'])){
			unset($actions['pagelayer_replace_media']);
		}
		
		return $actions;
		
	}

	//function is called first to select the route 
	static function replace_media_page(){
		
		global $pl_error;

		if(!current_user_can('upload_files')){
			wp_die(esc_html__('You do not have permission to upload files.', 'softaculous-pro'));
		}
		
		$post_id = (int) $_GET['id'];
		
		if(empty($post_id)){
			wp_die(esc_html__('Invalid Media ID', 'softaculous-pro'));
		}
		
		// Load the attachment
		$post = get_post($post_id);
		
		if(empty($post) || is_wp_error($post)){
			wp_die(esc_html__('Media not found', 'softaculous-pro'));
		}
		
		// Process the POST !
		if(isset($_FILES['userfile'])){
		
			if(!check_admin_referer('softaculous-pro-media')){
				wp_die('Invalid Nonce');
			}
			
			/** Check if file is uploaded properly **/
			if(!is_uploaded_file($_FILES['userfile']['tmp_name'])){
				$pl_error['upload_error'] = __('No file was uploaded ! Please try again.', 'softaculous-pro');
				\SoftWP\Media::replace_media_theme();
				return;
			}
			
			if(isset($_FILES['userfile']['error']) && $_FILES['userfile']['error'] > 0){
				$pl_error['upload_error'] = __('There was some error uploading the file ! Please try again.', 'softaculous-pro');
				\SoftWP\Media::replace_media_theme();
				return;
			}
			
			$filedata = wp_check_filetype_and_ext($_FILES['userfile']['tmp_name'], $_FILES['userfile']['name']);
			
			if ($filedata['ext'] == false){
				$pl_error['ext_error'] = __('The File type could not be determined. Please upload a permitted file type.', 'softaculous-pro');
				\SoftWP\Media::replace_media_theme();
				return;
			}
			
			$result = \SoftWP\Media::replace_attachment($_FILES['userfile']['tmp_name'], $post_id, $err);
			
			if(empty($result)){
				$pl_error['replace_error'] = $err;
				\SoftWP\Media::replace_media_theme();
				return;
			}
			
			$redirect_success = admin_url('post.php');
			$redirect_success = add_query_arg(array(
				'action' => 'edit', 
				'post' => $post_id,
			), $redirect_success);
			
			echo '<meta http-equiv="refresh" content="0;url='.$redirect_success.'" />';
		
		}
		
		// Show the theme
		\SoftWP\Media::replace_media_theme();
		
	}

	// Report an error
	static function report_error($error = array()){

		if(empty($error)){
			return true;
		}

		$error_string = '<b>'.__('Please fix the below errors', 'softaculous-pro').' :</b> <br />';

		foreach($error as $ek => $ev){
			$error_string .= '* '.$ev.'<br />';
		}

		echo '<div id="message" class="error"><p>'
						. __($error_string, 'softaculous-pro')
						. '</p></div>';
	}


	// Theme of the page
	static function replace_media_theme(){
		
		global $pl_error;
		
		\SoftWP\Media::report_error($pl_error);echo '<br />';
		
		$id = (int) $_GET['id'];
		
	?>
	<div class="wrap">
	<h1><?php echo esc_html__("Replace Media File", 'softaculous-pro'); ?></h1>
	<form enctype="multipart/form-data" method="POST">
		<div class="editor-wrapper">
			<section class="image_chooser wrapper">
				<input type="hidden" name="ID" id="ID" value="<?php echo $id ?>" />
				<p><?php echo esc_html__("Choose a file to upload from your computer", 'softaculous-pro'); ?></p>
				<div class="drop-wrapper">
					<p><input type="file" name="userfile" id="userfile" /></p>
					<?php wp_nonce_field('softaculous-pro-media'); ?>
				</div>
			</section>
			<section class="form_controls wrapper">
				<input id="submit" type="submit" class="button button-primary" name="submit" value="<?php echo esc_attr__("Upload", 'softaculous-pro');?>" />
			</section>
		</div>
	</form>
	<?php

	}

	// Replace the uploaded media with the new one
	static function replace_attachment($file, $post_id, &$error = ''){

		if(!current_user_can('upload_files')){
			wp_die(esc_html__('You do not have permission to upload files.', 'softaculous-pro'));
		}

		if(function_exists('wp_get_original_image_path')){
			$targetFile = wp_get_original_image_path($post_id);
		}else{
			$targetFile = trim(get_attached_file($post_id));
		}
		
		$fileparts = pathinfo($targetFile);
		$filePath = isset($fileparts['dirname']) ? trailingslashit($fileparts['dirname']) : '';
		$fileName = isset($fileparts['basename']) ? $fileparts['basename'] : '';
		$filedata = wp_check_filetype_and_ext($targetFile, $fileName);
		$fileMime = (isset($filedata['type'])) ? $filedata['type'] : false;
		$permissions = fileperms($targetFile) & 0644;
		
		if(empty($targetFile)){
			$error = __('Failed to determine the media file path !', 'softaculous-pro');
			return false;
		}
		
		if(empty($filePath)){
			$error = __('No folder for the target found !', 'softaculous-pro');
			return false;
		}
		
		// Remove the files of the original attachment
		\SoftWP\Media::remove_attachment_files($post_id);
		
		$result_moved = move_uploaded_file($file, $targetFile);
		
		if (false === $result_moved){
			$error = sprintf( esc_html__('The uploaded file could not be moved to %1$s. This is most likely an issue with permissions, or upload failed.', 'softaculous-pro'), $targetFile );
			return false;
		}
		
		if ($permissions > 0){
			chmod( $targetFile, $permissions ); // restore permissions
		}
		
		$updated = update_attached_file($post_id, $targetFile);
		
		$target_url = wp_get_attachment_url($post_id);
		
		// Run the filter, so other plugins can hook if needed.
		$filtered = apply_filters( 'wp_handle_upload', array(
			'file' => $targetFile,
			'url'  => $target_url,
			'type' => $fileMime,
		), 'sideload');
		
		// Check if file changed during filter. Set changed to attached file meta properly.
		if (isset($filtered['file']) && $filtered['file'] != $targetFile ){
			update_attached_file($post_id, $filtered['file']);
		}

		$metadata = wp_generate_attachment_metadata($post_id, $targetFile);
		wp_update_attachment_metadata($post_id, $metadata);

		return true;
		
	}

	static function remove_attachment_files($post_id){

		if(!current_user_can('upload_files')){
			wp_die(esc_html__('You do not have permission to upload files.', 'softaculous-pro'));
		}
		
		$meta = wp_get_attachment_metadata( $post_id );

		if (function_exists('wp_get_original_image_path')){ // WP 5.3+
			$fullFilePath = wp_get_original_image_path($post_id);
		}else{
			$fullFilePath = trim(get_attached_file($post_id));
		}

		$backup_sizes = get_post_meta( $post_id, '_wp_attachment_backup_sizes', true );
		
		$file = $fullFilePath;
		
		$result = wp_delete_attachment_files($post_id, $meta, $backup_sizes, $file);

		// If attached file is not the same path as file, this indicates a -scaled images is in play.
		$attached_file = get_attached_file($post_id);
		
		if ($file !== $attached_file && file_exists($attached_file)){
			@unlink($attached_file);
		}
	}
	
}
