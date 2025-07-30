document.addEventListener("DOMContentLoaded", function() {
	
	var modal = document.getElementById('softaculous-pro-photopea-modal');
	var iframe = document.getElementById('softaculous-pro-photopea-iframe');
	var saveBtn = document.getElementById('softaculous-pro-save-photopea');
	var saveCloseBtn = document.getElementById('softaculous-pro-save-close-photopea');
	var cancelBtn = document.getElementById('softaculous-pro-cancel-photopea');
	var saveAsBtn = document.getElementById('softaculous-pro-save-as-photopea');
	var qualityInput = document.getElementById('softaculous-pro-quality-input');
	var qualityInputContainer = document.getElementById('softaculous-pro-quality-input-container');
	var imageUrl = null;  // Cache the image URL to prevent repeated fetch requests	

	function openPhotopea() {
		event.preventDefault(); // Prevent form submission
		if (!imageUrl) {
			imageUrl = this.getAttribute('data-image-url');
			iframe.src = ''; 

			var customIO = { save: 'app.echoToOE("Save");' };
			var photopeaConfig = {
				files: [],
				environment: {
					lang: "en",
					customIO: customIO
				}
			}

			const allowedTypes = [
				'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 
				'image/x-ms-bmp', 'image/tiff', 'image/webp', 
				'image/vnd.adobe.photoshop', 'application/pdf', 
				'image/x-portable-pixmap', 'image/x-icon', 'image/vnd-ms.dds'
			];

			// Fetch the image and convert it to base64
			fetch(imageUrl)
			.then(response => {
				const contentType = response.headers.get('Content-Type');
				
				if (!allowedTypes.includes(contentType)) {
					throw new Error(`Unsupported image type: ${contentType}`);
				}

				const qualityAllowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
				if (qualityAllowedTypes.includes(contentType)) {
					qualityInputContainer.style.display = 'block'; 
				} else {
					qualityInputContainer.style.display = 'none'; 
				}

				return response.arrayBuffer();
			})
			.then(arrayBuffer => {
				var blob = new Blob([arrayBuffer]);
				var objectURL = URL.createObjectURL(blob);
				// Convert ArrayBuffer to a URL and pass to Photopea
				iframe.src = '';  
				var photopeaUrl = 'https://www.photopea.com/#' + encodeURIComponent(JSON.stringify(photopeaConfig));

				// Send the ArrayBuffer to Photopea using postMessage
				iframe.onload = function() {
					iframe.contentWindow.postMessage(arrayBuffer, '*');
				};

				iframe.src = photopeaUrl;

				modal.style.display = 'block';
			})
			.catch(error => console.error('Error loading image:', error));
		}
	}

	// Add event listener for Photopea button in both List View and Attachment Modal
	document.addEventListener('click', function(event) {
		if (event.target && event.target.id === 'edit_with_photopea') {
			event.preventDefault();
			var photopeaButton = event.target;
			// Ensure photopeaButton exists and has the data-image-url attribute
			if (photopeaButton && photopeaButton.getAttribute) {
				var imageUrl = photopeaButton.getAttribute('data-image-url');
				openPhotopea.call(photopeaButton);
			} else {
				console.error('Photopea button or image URL not found.');
			}
		}
	});

	function closePhotopea() {
		modal.style.display = 'none'; // Hide the modal
		iframe.src = ''; // Clear the iframe source
		imageUrl = null; // Reset the image URL

		setTimeout(function() {
			const imgElement = document.querySelector('.wp_attachment_image img');
			if (imgElement) {
				imgElement.src = imgElement.src.split('?')[0] + '?v=' + new Date().getTime();
			}
			const imgElement2 = document.querySelector('.details-image');
			if (imgElement2) {
				imgElement2.src = imgElement2.src.split('?')[0] + '?v=' + new Date().getTime();
			}
		}, 2000);
	}

	cancelBtn.addEventListener('click', function() {
		closePhotopea()
	});

	saveBtn.addEventListener('click', function() {
		photopeaButton = document.querySelector('#edit_with_photopea'); // Dynamically query the button
		var imageUrl = photopeaButton.getAttribute('data-image-url');
		var fileExtension = imageUrl ? imageUrl.split('.').pop() : 'jpg';
		const origin = 'https://www.photopea.com'; 
		
		if (iframe && iframe.contentWindow) {
			iframe.contentWindow.postMessage('app.activeDocument.saveToOE("' + fileExtension + '")', origin);
		} else {
			console.error('iframe or its contentWindow is undefined');
		}
	});

	saveCloseBtn.addEventListener('click', function(event) {
		photopeaButton = document.querySelector('#edit_with_photopea'); // Dynamically query the button
		var imageUrl = photopeaButton.getAttribute('data-image-url');
		var fileExtension = imageUrl ? imageUrl.split('.').pop() : 'jpg';
		const origin = 'https://www.photopea.com';
		
		if (iframe && iframe.contentWindow) {
			iframe.contentWindow.postMessage('app.activeDocument.saveToOE("' + fileExtension + '")', origin);
		} else {
			console.error('iframe or its contentWindow is undefined');
		}
		
		function handlePhotopeaMessage(event) {
			if (event.origin === origin) {
				const data = typeof event.data === 'string' ? event.data : '';
				if (data.includes('done')) {
					closePhotopea(); 
					window.removeEventListener('message', handlePhotopeaMessage);
				}
			}
		}

		window.addEventListener('message', handlePhotopeaMessage);
	});

	saveAsBtn.addEventListener('click', function() {
		photopeaButton = document.querySelector('#edit_with_photopea'); // Dynamically query the button
		modal.style.display = 'none'; 
		var imageUrl = photopeaButton.getAttribute('data-image-url');

		var baseFileName = imageUrl ? imageUrl.substring(imageUrl.lastIndexOf('/') + 1, imageUrl.lastIndexOf('.')) : 'image';
		var fileExtension = imageUrl ? imageUrl.split('.').pop() : 'jpg';
		const origin = 'https://www.photopea.com';
		
		function photopea_generate_randname(baseFileName, fileExtension) {
			
			return new Promise((resolve) => {
				const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
				let suffix = '';
				for (let i = 0; i < 5; i++) {
					suffix += chars.charAt(Math.floor(Math.random() * chars.length));
				}
				resolve(baseFileName + '-' + suffix + '.' + fileExtension);
			});
			
		}

		// Check for an existing file name
		photopea_generate_randname(baseFileName, fileExtension).then(uniqueFileName => {
			var saveAsModalHtml = `
				<div id="saveAsModal" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
					background-color: white; padding: 20px; border-radius: 5px; z-index: 10000; box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);">
					<label for="newFileName" style="cursor:pointer;">New File Name:</label>
					<input type="text" id="newFileName" value="${uniqueFileName}" />
					<button id="confirmSaveAs" style="padding: 8px 15px; background-color: #0073aa; color: white; border: none; border-radius: 3px;cursor:pointer;">Save</button>
					<button id="cancelSaveAs" style="padding: 8px 15px; background-color: #999; color: white; border: none; border-radius: 3px;cursor:pointer;">Cancel</button>
				</div>
			`;
			
			document.body.insertAdjacentHTML('beforeend', saveAsModalHtml);

			document.getElementById('cancelSaveAs').addEventListener('click', function() {
				document.getElementById('saveAsModal').remove(); 
			});

			document.getElementById('confirmSaveAs').addEventListener('click', function() {
				var newFileName = document.getElementById('newFileName').value;
				if (!newFileName) {
					alert('Please enter a valid name');
					return;
				}

				if (iframe && iframe.contentWindow) {
					window.saveAsMode = true;  
					window.newFileName = newFileName;  
					iframe.contentWindow.postMessage('app.activeDocument.saveToOE("' + fileExtension + '")', '*');
				} else {
					console.error('iframe or its contentWindow is undefined');
				}

				document.getElementById('saveAsModal').remove();
				
				function handlePhotopeaMessage(event) {
					if (event.origin === origin) {
						const data = typeof event.data === 'string' ? event.data : '';
						if (data.includes('done')) {
							closePhotopea(); 
							window.removeEventListener('message', handlePhotopeaMessage);
						}
					}
				}
				window.addEventListener('message', handlePhotopeaMessage);
			});
			
		});
	});
	
	window.addEventListener("message", function(event) {
		const origin = 'https://www.photopea.com';
		if (event.origin !== origin) {
			return;
		}

		if (typeof event.data === 'string') {
			if (event.data === 'done') {
				return;
			}
		} 

		let message = event.data;
		
		// For SVG - Convert to ArrayBuffer
		if (message instanceof Uint8Array) {
			message = message.slice().buffer;
		}
		
		// Check if message is an ArrayBuffer (image data)
		if (message instanceof ArrayBuffer) {
			// Convert the ArrayBuffer to Blob
			const blob = new Blob([message], { type: 'image/jpeg' });
			const reader = new FileReader();
			reader.onloadend = function() {
				var base64data = reader.result;

				if (window.saveAsMode) {
					fetch(spro_photopea.ajax_url + '?action=softaculous_pro_upload_photopea_image', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({
							source: base64data,
							new_image_name: window.newFileName,
							quality: qualityInput.value,
							save_as_new: "1",
							softaculous_pro_security: spro_photopea.nonce
						}),
					})
					.then(response => response.json())
					.catch(error => {
						console.error('Error in saving the image:', error);
					});

					// Reset the flag
					window.saveAsMode = false;
				} else {
					fetch(spro_photopea.ajax_url + '?action=softaculous_pro_upload_photopea_image', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({
							source: base64data,
							original_image_url: photopeaButton.getAttribute('data-image-url'),
							quality: qualityInput.value,
							softaculous_pro_security: spro_photopea.nonce
						}),
					})
					.then(response => response.json())
					.catch(error => {
						console.error('Error in saving the image:', error);
					});
				}
			};

			// Read the blob as a Data URL (base64)
			reader.readAsDataURL(blob);
		} else {
			console.error("Unknown message format received:", message);
		}
	});	
});
