jQuery(document).ready(function($){
	
	// video thumbnail upload
	$(document).on('click', '.siteseo-video-thumbnail-upload', function(e){
    e.preventDefault();
    var button = $(this);
      var frame = wp.media({
            title: 'Select or Upload Video Thumbnail',
            button: {
              text: 'Use this image'
            },
            multiple: false
      });
       
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            button.prev('input').val(attachment.url);
        });
        
        frame.open();
	});
	
	$(document).on('click', '#siteseo_validate_schema', function(e){
		e.preventDefault();

		// Fetch Schema
		var schemaContentWrap = $('.siteseo_schema_preview #siteseo_raw_schema');
		if(!schemaContentWrap.length){
			schemaContentWrap = $('.siteseo_schema_preview');
		}

		let schemaContent = '';
		
		// This is to ensure we dont end up having 2 values of the schema.
		if(schemaContentWrap.length > 1){
			schemaContent = schemaContentWrap.eq(0).text();
		} else {
			schemaContent = schemaContentWrap.text();
		}

		let $form = $('<form>', {
			'method': 'POST',
			'action': 'https://search.google.com/test/rich-results',
			'target': '_blank'
		}),
		$input = $('<input>', {
			'type': 'hidden',
			'name': 'code_snippet',
			'value': schemaContent
		});

		$form.append($input);
		$("body").append($form);

		$form.submit();
		$form.remove();
	});
	
	$(document).on('change', '.siteseo_structured_data_type', function(e){
		e.preventDefault();
		
		let schemaType = $(this).val(),
		propertiesContainer = $('.siteseo_schema_properties_container'),
		customSchemaContainer = $('.siteseo_custom_schema_container'),
		propertiesDiv = $('.siteseo-schema-properties');

		$('.siteseo_structured_data_type').val(schemaType);
		
		propertiesDiv.empty();
		
		if(schemaType === ''){
			propertiesContainer.addClass('hidden');
			customSchemaContainer.addClass('hidden');
			$('.siteseo_schema_preview').html('');
			$('#siteseo_raw_schema').text('');
			return;
		}
	   
		if(schemaType === 'CustomSchema'){
			propertiesContainer.addClass('hidden');
			customSchemaContainer.removeClass('hidden');
			updateCustomSchemaPreview();
			return;
		} else{
			propertiesContainer.removeClass('hidden');
			customSchemaContainer.addClass('hidden');
		}
		
		// schemas load
		let properties = structuredDataMetabox.propertyTemplates[schemaType] || {};
		// Create form fields for each property
		$.each(properties, function(property, defaultValue){
			var label = property.replace(/([a-z])([A-Z])/g, '$1 $2');
			label = label.charAt(0).toUpperCase() + label.slice(1);

			let field = '',
			is_textarea_fields = ['description', 'instructions', 'reviewBody', 'questions', 'step', 'ingredients', 'recipeInstructions', 'courseDescription', 'bookDescription', 'softwareRequirements', 'menu'],
			is_date_type_fields = ['datePublished', 'dateModified', 'uploadDate', 'startDate', 'endDate', 'foundingDate', 'releaseDate'];
			
			if(is_textarea_fields.includes(property)){
				field = $('<textarea/>').attr({ name: 'schema_properties[' + property + ']', id: 'siteseo_schema_property_' + property, rows: 3, class: 'widefat'}).val(defaultValue);
			} else if (is_date_type_fields.includes(property)){
				field = $('<input/>').attr({ type: 'datetime-local', name: 'schema_properties[' + property + ']', id: 'siteseo_schema_property_' + property,
				class: 'widefat'}).val(defaultValue);
			} else {
				field = $('<input/>').attr({ type: 'text', name: 'schema_properties[' + property + ']', id: 'siteseo_schema_property_' + property,
				class: 'widefat'}).val(defaultValue);
			}
			
			$('<p/>')
				.append($('<label/>').attr('for', 'siteseo_schema_property_' + property).text(label + ':'))
				.append(field)
				.appendTo(propertiesDiv);
			});
			
			// preview update
			updatePreview();
			$(document).on('input', '.siteseo-schema-properties input, #schema_properties textarea', updatePreview);
	});
	
	// preview function
	function updatePreview(){
		var schemaType = $('.siteseo_structured_data_type').val();
		if(schemaType === 'CustomSchema'){
			updateCustomSchemaPreview();
			return;
		}

		var schemaData = {
			'@context': 'https://schema.org',
			'@type': schemaType
		};
	   
		$('.siteseo-schema-properties input, .siteseo-schema-properties textarea').each(function(){
			var propertyName = $(this).attr('name').match(/\[(.*?)\]/)[1];
			var propertyValue = $(this).val();

			if(propertyValue !== ''){
			schemaData[propertyName] = propertyValue;
			}
		});
	   
		var jsonString = JSON.stringify(schemaData, null, 2);
		$('#siteseo_raw_schema').text(jsonString);
	   
	   // Make sure highlighter element exists
		if($('.siteseo_schema_preview .siteseo_highlighter').length === 0){
		   $('.siteseo_schema_preview').html('<div id="siteseo_highlighter" class="siteseo_highlighter"></div><div id="siteseo_raw_schema" style="display:none;"></div>');
		}
	   
		$('.siteseo_schema_preview .siteseo_highlighter').html(highlightJson(jsonString));
	}
	
	// Custom schema preview
	function updateCustomSchemaPreview(){
		var customSchema = $('.siteseo_structured_data_custom').val() || '';
		$('#siteseo_raw_schema').text(customSchema);
	   
		// highlighter element exists
		if($('.siteseo_schema_preview .siteseo_highlighter').length === 0){
			$('.siteseo_schema_preview').html('<div id="siteseo_highlighter" class="siteseo_highlighter"></div><div id="siteseo_raw_schema" style="display:none;"></div>');
		}
	   
		try{
			
			if(customSchema.trim()){
				var jsonObj = JSON.parse(customSchema);
				$('.siteseo_schema_preview .siteseo_highlighter').html(highlightJson(jsonObj));
			} else{
				$('.siteseo_schema_preview .siteseo_highlighter').html('');
			}
		} catch(e){
			
			$('.siteseo_schema_preview .siteseo_highlighter').text(customSchema);
		}
	}
   
	// as per schema change update preview
	$(document).on('input', '.siteseo_structured_data_custom', updateCustomSchemaPreview);
   
	// Initial preview update
	if($('.siteseo_structured_data_type').val() !== ''){
		if($('.siteseo_structured_data_type').val() === 'CustomSchema'){
			updateCustomSchemaPreview();
		} else{
			updatePreview();
			$(document).on('input', '.siteseo-schema-properties input, .siteseo-schema-properties textarea', updatePreview);
		}
	}
});
