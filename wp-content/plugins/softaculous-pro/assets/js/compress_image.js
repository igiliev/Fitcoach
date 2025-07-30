jQuery(document).ready(function($) {
	$('.compress-image-button').click(function() {
		var imageId = $(this).data('image-id');
		var originalSize = $(this).data('original-size');
		var imageType = $(this).data('image-type');
		
		const supportedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
		if (!supportedTypes.includes(imageType.toLowerCase())) {
			alert('Image format not supported. Supported formats: JPEG, JPG, PNG, and WebP.');
			return;
		}

		const modalHtml = `
			<div id="compress-image-modal" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); z-index: 10000; width: 300px; text-align: center;">
				<p>Original Size: ${originalSize} KB</p>
				<p>Compressed Size: <span id="compressed-size">Calculating...</span> KB</p>
				<button id="replace-compress" data-image-id="${imageId}" disabled style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: not-allowed;">Compress and Replace</button>
				<button id="cancel-compress" style="padding: 10px 20px; background-color: #a00; color: white; border: none; border-radius: 5px; margin-left: 10px; cursor: pointer;">Cancel</button>
			</div>
			<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;"></div>
		`;

		$('body').append(modalHtml);
		const $modal = $('#compress-image-modal');
		const $overlay = $('#modal-overlay');
		const $replaceButton = $('#replace-compress');
		const $compressedSizeDisplay = $('#compressed-size');
		$modal.fadeIn();

		// Trigger size calculation
		$.post(spro_compress.ajax_url, {
			action: 'softaculous_pro_calculate_compressed_size',
			image_id: imageId,
			softaculous_pro_security: spro_compress.nonce
		}, function(response) {
			if (response.success) {
				var compressedSize = response.data.compressed_size;
				$('#compressed-size').text(compressedSize);

				if (Math.round(compressedSize) >= Math.round(originalSize)) {
					alert('The image cannot be compressed further.');
					$modal.add($overlay).fadeOut().remove();
				}else {
					// Update the compressed size in the modal and enable the replace button
					$compressedSizeDisplay.text(response.data.compressed_size);
					$replaceButton.prop('disabled', false).css('cursor', 'pointer');

					// Handle "Compress and Replace" (replaces the current image in place)
					$replaceButton.click(function() {
						var replaceImageId = $(this).data('image-id');
						$.post(spro_compress.ajax_url, {
							action: 'softaculous_pro_replace_compressed_image',
							image_id: replaceImageId,
							softaculous_pro_security: spro_compress.nonce
						}, function(replaceResponse) {
							if (replaceResponse.success) {
								alert('Image has been compressed and replaced.');
								$('#compress-image-modal, #modal-overlay').fadeOut().remove();
								location.reload();
							} else {
								alert('Compression and replacement failed: ' + replaceResponse.data);
							}
						});
					});
				}
			}else {
				alert('Size calculation failed: ' + response.data);
			}
		});

		$('#cancel-compress').click(function() {
			$('#compress-image-modal, #modal-overlay').fadeOut().remove();
		});
	});
});
