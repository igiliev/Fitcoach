(function($, i18n){

const supported_languages = ['arabic', 'chinese', 'czech', 'danish', 'dutch', 'english', 'finnish', 'french', 'german', 'greek', 'hindi', 'hebrew', 'hungarian', 'indonesian', 'italian', 'japanese', 'korean', 'marathi', 'punjabi', 'persian', 'polish', 'portuguese', 'russian', 'spanish', 'swedish', 'thai', 'turkish', 'vietnamese'],
supported_tones = ['casual', 'confidence', 'formal', 'friendly', 'inspirational', 'motivational', 'nostalgic', 'playful', 'professional', 'scientific', 'straightforward', 'witty'];
let old_date_string = '';

	
$(document).ready(function(){
	let chat_tmpl = build_chat();

	$('body').append(chat_tmpl);
	
	const updateActiveEle = () => {
		// If we find iframe then we dont need this as it wont work
		if(soft_ai.contentDocument){
			document.removeEventListener('focusin', updateActiveEle);
			return;
		}

		// We need to make sure the element is a block, is selected and is not a toolbar item
		if(
			(document.activeElement.closest('.is-selected') && 
			document.activeElement.closest('.wp-block') && 
			!document.activeElement.dataset['toolbarItem'])
		){
			soft_ai.lastActiveEle = document.activeElement;
		}
	}

	// This is to save the activeEvent which can be used in the AI content
	const ownerListener = document.addEventListener('focusin', updateActiveEle);
	
	const observer = new MutationObserver(() => {
		const editorIframe = document.querySelector('iframe[name="editor-canvas"]');

		if(editorIframe){
      observer.disconnect();
			editorIframe.addEventListener('load', () => {
				soft_ai.contentDocument = editorIframe.contentDocument;

				editorIframe.contentDocument.addEventListener('focusin', function(e){
					if(soft_ai.contentDocument.activeElement.closest('.wp-block')){
						soft_ai.lastActiveEle = soft_ai.contentDocument.activeElement;
					}
				});
			});
		}
	});

	observer.observe(document.body, { childList: true, subtree: true});
	setTimeout(() => {observer.disconnect()}, 10000); // If we dont find the I frame disconnect the observer
	
	$('#soft-ai-generation').click(request_soft_ai);
	$('.spro-ai-shortcut').click(request_soft_ai);
	$('.spro-ai-shortcut-select').change(request_soft_ai);
	$('.spro-ai-chat-history-icon').click(toggle_history);
	$('.spro-ai-suggestion-btns').click(handle_suggestions);
	$(document).on('click', '.spro-refresh-ai-tokens', refresh_ai_tokens);

	// Handling Enter press when user is focused writing AI Prompt.
	$('#spro_prompt_input').on('keydown', function(e){
		if(e.shiftKey || e.key != 'Enter'){
			return;
		}

		e.preventDefault();

		let jEle = $(e.target);		
		if(jEle.val().trim() === ''){
			return;
		}

		request_soft_ai(e);
	});
	
	$('.spro-ai-chat-close').click(function(e){
		let jEle = $(e.target);

		jEle.closest('.spro-chat').css('right', '-27vw');
		$('.spro-ai-chat-overlay').hide();
	})

	// To close AI chat when user gets into something else
	$('.spro-ai-chat-overlay').on('click', function(){
		let soft_chat = $('.spro-chat')

		if(soft_chat.css('right') != '0px'){
			return;
		}

		soft_chat.css('right', '-27vw');
		$('.spro-ai-chat-overlay').hide();
	});
	
	$(document).on('click', '.spro-copy-ai-response', function(e){
		e.preventDefault();
		let jEle = $(e.target).closest('.spro-copy-ai-response'),
		response = jEle.closest('.spro-response-actions').prev();
		jEle.addClass('active');

		navigator.clipboard.writeText(response.html())
		
		setTimeout(() => {jEle.removeClass('active');}, 2000);
	});
	
	$(document).on('click', '.spro-chat-history-link,.spro-chat-history-link *', function(e){
		let jEle = $(e.target).closest('.spro-chat-history-link'),
		history_id = jEle.data('id');
		
		if(history_id == 0){
			return;
		}
		
		// We won't process if we do not have the base class
		if($('.spro-ai-chat-history-view').length < 1){
			return;
		}

		request_history(history_id);
		e.stopPropagation(); // Prevent bubbling to the parent
	});
	
	// Handling history arrow icon
	$('.spro-chat').on('click', '.spro-ai-history-single-close', function(e){
		$('.spro-ai-chat-history-view').empty();
		$(e.target).css('visibility', 'hidden'); // This is to prevent the other spans from moving positions
		$('.spro-ai-chat-history-list').show();
	});
	
	// Handling history close icon
	$('.spro-chat').on('click', '.spro-ai-history-close', function(e){
		$('.spro-ai-chat-history-view').empty();
		$('.spro-ai-chat-history-list').show();
		$('.spro-ai-chat-history-icon').trigger('click');
	});

	// Handle the Use this button to add AI content to the editor
	$(document).on('click', 'button.spro-ai-use-this', use_ai_content);
	
	// Load more history on scroll
	$('.spro-ai-chat-history').scroll(function (e){ 
		if(parseInt(e.target.scrollTop + e.target.clientHeight) >= parseInt(e.target.scrollHeight) - 2) {
			request_history();
		}
	});
});

function refresh_ai_tokens(e){

	let pEle = $(e.target).closest('div');
	pEle.addClass('spro-loading-div');

	$.ajax({
		url : soft_ai.ajax_url,
		method : 'POST',
		data : {
			action : 'softaculous_ai_refresh_tokens',
			nonce : soft_ai.nonce,
		},
		success : function(res){
			if(!res.success){
				if(res.data){
					show_snackbar(res.data);
					return;
				}

				show_snackbar("Something went wrong fetching token data");
				return;
			}

			update_tokens(res.data);
		},
	}).always(() =>{pEle.removeClass('spro-loading-div');});
}

function build_chat(){
	return `<div class="spro-chat">
		<div class="spro-snackbar"></div>
		<div class="spro-chat-header">
			<span class="dashicons dashicons-arrow-right-alt spro-ai-chat-close"></span>
			<span>Softaculous AI&nbsp;<img src="${soft_ai.ai_icon_url}" class="spro-ai-icon" width="20"/></span>
			<span class="dashicons dashicons-backup spro-ai-chat-history-icon"></span>
		</div>
		<div class="spro-ai-token-count">${(soft_ai.tokens ? 'Tokens remaining ' + (soft_ai.tokens.remaining_tokens < 0 ? 0 : parseInt(soft_ai.tokens.remaining_tokens).toLocaleString('en')) + (soft_ai.tokens.remaining_tokens < 900 ? '<a href="'+soft_ai.buy+'" target="_blank">Buy More</a>' : '') + '<span class="dashicons dashicons-image-rotate spro-refresh-ai-tokens" title="Refresh tokens"></span>' : '')}</div>
		<div class="spro-chat-response-section">
		<div class="spro-chat-startup-placeholder">
			<h1>What can I help you with?</h1>
			<div class="spro-prompt-suggestions">
				<p>Suggestions</p>
				<button class="spro-ai-suggestion-btns" data-prompt="blog_post">Write me a blog post about</button>
				<button class="spro-ai-suggestion-btns" data-prompt="create_table">Create a table of</button>
				<button class="spro-ai-suggestion-btns" data-prompt="desc_title">Write a title based on description</button>
				<button class="spro-ai-suggestion-btns" data-prompt="p_50">Write a 50 word paragraph about</button>
			</div>
		</div>
		</div>
		<div class="spro-ai-chat-history">
			<div class="spro-chat-history-header">
				<span class="dashicons dashicons-arrow-right-alt spro-ai-history-single-close" title="Go back to history list"></span>
				<span class="spro-history-header-label">History <span class="spro-spinner spro-spinner__default spro-spinner__dark"></span></span>
				<span class="dashicons dashicons-no-alt spro-ai-history-close" title="Close History"></span>
			</div>
			<div class="spro-ai-chat-history-view"></div>
			<div class="spro-ai-chat-history-list"></div>
		</div>
		
		<div class="spro-ai-chat-options-section">
			<div class="spro-prompt-shortcuts">
				<button class="soft-btn spro-ai-shortcut" action="shorter">Make it shorter</button>
				<button class="soft-btn spro-ai-shortcut" action="longer">Make it longer</button>
				<button class="soft-btn spro-ai-shortcut" action="simplify">Simplify the language</button>
				<button class="soft-btn spro-ai-shortcut" action="grammar">Fix spelling & grammar</button>
				<div>
					<select name="tone" class="spro-ai-select spro-ai-shortcut-select">
						<option value="" selected disabled>Change Tone</option>
						${supported_tones.map((tone) => (`<option value="${tone}">${tone.charAt(0).toUpperCase() + tone.slice(1)}</option>`)).join('')}
					</select>
					<select name="translate" class="spro-ai-select spro-ai-shortcut-select">
						<option value="" selected disabled>Translate</option>
						${supported_languages.map((lang) => (`<option value="${lang}">${lang.charAt(0).toUpperCase() + lang.slice(1)}</option>`)).join('')}
						</select>
				</div>
			</div>
			<div class="soft-prompt-input">
				<textarea id="spro_prompt_input" name="soft_prompt" rows="3"></textarea>
				<div class="spro-prompt-action"><button id="soft-ai-generation" class="soft-btn soft-btn-black">Generate<span class="spro-spinner spro-spinner__default spro-spinner__light"></span></button></div>
			</div>
			
			<p class="description">AI responses can be inaccurate, please double check</p>
		</div>
	</div>
	<div class="spro-ai-chat-overlay"></div>`;
}

function show_snackbar(msg){
	if(!msg){
		msg = 'Something went wrong!';
	}

	snack_bar = $('.spro-snackbar');
	snack_bar.text(msg);
	snack_bar.addClass('show');
	setTimeout(function(){ snack_bar.removeClass('show'); }, 3500);
}

function request_soft_ai(e){
	e.preventDefault();
	
	let jEle = $(e.target),
	ai_prompt = $('#spro_prompt_input').val(),
	options_section = jEle.closest('.spro-ai-chat-options-section'),
	shortcut = jEle.attr('action'),
	spinner = options_section.find('span.spro-spinner'),
	response_section = jQuery('.spro-chat-response-section');

	if(!ai_prompt && !shortcut){
		shortcut = jEle.val();
	}

	if(!ai_prompt && !shortcut){
		alert('Enter a prompt');
		return;
	}

	options_section.addClass('disabled');
	
	// Scrolling to the bottom so the softaculous thinking feedback could be visible
	response_section.scrollTop(response_section[0].scrollHeight);

	spinner.addClass('spro-spinner-active');
	
	// We do not want to add a section when using shortcut
	if(!shortcut){
		soft_handle_ai_content(ai_prompt, 'prompt');
	}
	
	response_section.append('<div class="soft-ai-chat-loader">Softaculous AI Is Thinking<div class="spro-dot-loader"></div></div>');
	
	$('#spro_prompt_input').val(''); // Unset the prompt textarea

	// Making call to get AI reponse
	$.ajax({
		method : 'POST',
		url : soft_ai.ajax_url,
		data : {
			'nonce' : soft_ai.nonce,
			'prompt' : ai_prompt ?? '',
			'shortcut' : shortcut ?? '',
			'content': soft_ai.content ?? '',
			'action' : 'softaculous_ai_generation',
		},
		success: function(res){
			if(!res.success){
				if(res.data){
					show_snackbar(res.data);
				}

				// If it failed we should remove the prompt aswell.
				chat_type = $('.spro-chat-response-section .spro-chat-response:last').data('type');
				if(chat_type && chat_type == 'prompt'){
                    $('.spro-chat-response-section .spro-chat-response:last').remove();
				}

				return;
			}

			if(res.data.error){
				show_snackbar(res.data.error);
				return;
			}

			// Updating the UI
			$('.spro-chat-startup-placeholder').slideUp();
			if(!res.data.ai){
				snack_bar("Did not receive any response from the API");
				return;
			}

			soft_handle_ai_content(res.data.ai, 'assistant');
		}
	}).always(function(res){
		if(res.data && res.data.remaining_tokens){
			update_tokens(res.data.remaining_tokens);
		}
  
    // Resetting select shortcut options
		if(jEle.hasClass('spro-ai-shortcut-select')){
		    jEle.val('');
		}

		options_section.removeClass('disabled');
		spinner.removeClass('spro-spinner-active');
		response_section.find('.soft-ai-chat-loader').remove(); // Removing the loader.
		
		if($('.spro-chat-response-section .spro-chat-response').length){
			$('.spro-prompt-shortcuts').show();
		}
	});
}

function use_ai_content(e){
	e.preventDefault();

	let jEle = $(e.target);
	content = jEle.closest('p').prev().html();
	content = content.trim();
	content = content.replace(/<\/?p>(<br\/?>)?/gm, '');

	let blocks = wp.blocks.rawHandler({HTML:content}),
	selected_block = wp.data.select('core/block-editor').getSelectedBlock(),
	selected_block_id = 0,
	is_list = false;

	if(selected_block){	
		selected_block_id = selected_block.clientId;

		// We need to handle this because in case of li it does not allow any other block tag inside it.
		is_list = selected_block.name.indexOf('list-item') > -1;
	}
	
	if(!selected_block_id){
		wp.data.dispatch('core/block-editor').insertBlocks(blocks);
	} else if(is_list) {
		// To handle appending inside a li tag/block, and will only add if the new tag is paragraph
		if(blocks[0].name.indexOf('paragraph') > -1){
			wp.data.dispatch('core/block-editor').updateBlockAttributes(selected_block_id, {
				content: blocks[0].attributes.content,
			});
		}
	} else {
		if(selected_block.name.indexOf('core/paragraph') > -1){
			wp.data.dispatch('core/block-editor').updateBlockAttributes(selected_block_id, {
				content: blocks[0].attributes.content,
			}).then((res) => {
				if(!res.attributes.content){
					wp.data.dispatch('core/block-editor').replaceBlock(selected_block_id, blocks);
				}
			});

			return;
		}

		// We do not know exactly which attribute has the value of the block we are Updating
		// As other block providers do not use the standard way, so we have to loop over all the
		// attributes to get attribiute which we should update. And we can not update the DOM directly
		// becuase Gutenberg is a React based editor which uses Virtual DOM and changing the DOM directly
		// could cause unexpected behavious.
		let block_attributes = selected_block.attributes,
		// This is a simpler way but does not work with deeper arrays
		text_attribute = Object.entries(block_attributes).find(([key, value]) => {
			return typeof value === 'string' && value.includes(soft_ai.lastActiveEle.textContent)
		})

		if(text_attribute && text_attribute[0]){
			let updated_attributes = {},
			updated_content = '';

			updated_content = block_attributes[text_attribute[0]].replace(soft_ai.lastActiveEle.textContent, content);
			updated_attributes[text_attribute[0]] = updated_content;

			wp.data.dispatch('core/block-editor').updateBlockAttributes(selected_block_id, updated_attributes);
			return;
		}

		soft_ai.lastActiveEle.textContent = content; // Some blocks are overly nexted so here we will directly update the text.
	}
}

function toggle_history(e){

	let jEle = $(e.target);
	chat_wrap = jEle.closest('.spro-chat'),
	history_tab = chat_wrap.find('.spro-ai-chat-history'),
	chat_response = chat_wrap.find('.spro-chat-response-section'),
	chat_options = chat_wrap.find('.spro-ai-chat-options-section');
	
	if(history_tab.css('display') == 'none'){
		history_tab.show();
		chat_options.hide();
		chat_response.hide();

		// We dont want to request more if we already have some history.
		if($('.spro-chat-history-link').length > 0){
			return;
		}

		history_tab.find('.spro-chat-history-header span.spro-spinner').addClass('spro-spinner-active');
		request_history();
		
		return;
	}
	
	history_tab.hide();
	chat_options.show();
	chat_response.show();	
}

function show_single_history(response){
	html = '';
	
	$('.spro-ai-chat-history-list').hide();
	$('.spro-ai-chat-history-view').empty();
	
	if(response.content){
		html += `<div class="spro-chat-response" data-type="content">
		<p><b>Content:</b></p>
		<p>${spro_markdown_to_html(response.content)}</p>
	</div>`;
	}
	
	if(response.prompt){
		html += `<div class="spro-chat-response" data-type="prompt">
		<p><b>Prompt:</b></p>
		<p>${response.prompt}</p>
	</div>`;
	}
	
	if(response.assistant){
		response.assistant = spro_markdown_to_html(response.assistant);

		html += `<div class="spro-chat-response" data-type="assistant">
		<p><b>Assistant:</b></p>
		<div>${response.assistant}</div>
		<p class="spro-response-actions"><button class="spro-ai-use-this">Use This</button><button class="spro-copy-ai-response"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 7c0-.932 0-1.398-.152-1.765a2 2 0 0 0-1.083-1.083C12.398 4 11.932 4 11 4H8c-1.886 0-2.828 0-3.414.586S4 6.114 4 8v3c0 .932 0 1.398.152 1.765a2 2 0 0 0 1.083 1.083C5.602 14 6.068 14 7 14" stroke="#222"/><rect x="10" y="10" width="10" height="10" rx="2" stroke="#222"/></svg></button></p>
	</div>`;
	}
	
	$('.spro-ai-chat-history-view').append(html);
	$('.spro-ai-history-single-close').css('visibility', 'visible');
	
}

function update_tokens(remaining_tokens){
	$('.spro-ai-token-count').html('Tokens remaining ' + (remaining_tokens < 0 ? 0 : parseInt(remaining_tokens).toLocaleString('en')) + (remaining_tokens < 900 ? '<a href="'+soft_ai.buy+'" target="_blank">Buy More</a>' : '') + '<span class="dashicons dashicons-image-rotate spro-refresh-ai-tokens" title="Refresh tokens"></span>');
}

function request_history(history_id = 0){
	
	let history_links = $('.spro-chat-history-link'),
	offset = history_links.length,
	total_links = $('.spro-ai-chat-history-list').data('total');

	// We should not send ajax request if all the history items are visible.
	if(offset == total_links && !history_id){
		return;
	}

	$.ajax({
		method : 'POST',
		url : soft_ai.ajax_url,
		data : {
			'nonce' : soft_ai.nonce,
			'action' : 'softaculous_ai_history',
			'history_id' : history_id,
			'offset' : offset,
		},
		success: function(res){
			if(!res.success){
				alert(res.message);
				return;
			}
			
			// In case of single history we want to handle the append in different place
			if(history_id != 0){
				show_single_history(res.data);
				return;
			}
			
			if(res.data.total == 0){
				show_snackbar("No history found");
			}
			
			// Updating the UI
			append_history(res.data);
		}
	}).always(function(){
		$('.spro-chat-history-header span.spro-spinner').removeClass('spro-spinner-active');
	});
}

function append_history(history){

	let html = '',
	total = history['total'];
	history = history['history'];

	for(let i in history){
		let date = history[i]['date'],
		date_obj = new Date(date),
		current_date = new Date(),
		date_string = '';

		if(date_obj.getDate() == current_date.getDate() && date_obj.getMonth() == current_date.getMonth()){
			date_string = 'Today';
		} else if(date_obj.getDate() == (current_date.getDate() - 1) && date_obj.getMonth() == current_date.getMonth()){
			date_string = 'Yesterday';
		} else {
			date_string = date_obj.toLocaleString('en-US', {month:'long'})
		}

		if(!date_string || old_date_string != date_string){
			html += `<p><em><strong>${date_string}</strong></em></p>`;
		}
		
		old_date_string = date_string;

		html += `<div class="spro-chat-response spro-chat-history-link" data-id="${history[i]['id']}"><div>${history[i]['title']}</div>${history[i]['token_used'] ? '<div class="spro-token-used">-'+history[i]['token_used']+'</div>' : ''}</div>`;
	}

	$('.spro-ai-chat-history-list').append(html);
	$('.spro-ai-chat-history-list').attr('data-total', total);
}

function handle_suggestions(e){
	e.preventDefault();
	
	let jEle = $(e.target),
	suggestion = jEle.data('prompt'),
	prompts = {
		'p_50': 'Write a 50 word paragraph about [write the topic name]',
		'desc_title': 'Write a title based on description [write a description you want the title on]',
		'create_table': 'Create a table of [topic of the table you want]',
		'blog_post': 'Write me a blog post about [write your topic here]',
	};

	if(!prompts.hasOwnProperty(suggestion)){
		return;
	}

	let input_field = $('#spro_prompt_input');
	
	input_field.val(prompts[suggestion]).focus();
	let length = input_field.val().length; 
	input_field[0].setSelectionRange(length, length); // Moving the focus cursor to the end of the added text
}

})(jQuery, soft_ai.i18n)

function soft_handle_ai_content(props, role = 'content'){

	let content = '';
	
	if(!props){
		return;
	}

	if(role == 'content'){
		if(props.attributes.content){
			content = props.attributes.content;
		} else if(soft_ai.lastActiveEle){
			// We have to use document.activeElement because third party block providers do not use
      // standard attributes name, so we directly take the text out from the DOM.
			content = soft_ai.lastActiveEle.innerText;
		} else {
			var span = document.createElement('span');
			span.innerHTML = document.activeElement;
			content = span.textContent;
		}

		soft_ai.gutenberg = props;
	} else {
		content = props;
	}
	
	// If the content is empty no need to go further.
	if(!content){
	  return;
	}

	response_section = jQuery('.spro-chat-response-section');
	
	// Updating our global
	if(role == 'content' && ((typeof(content) == 'object' && content.text) || (typeof(content) == 'string'))){
		soft_ai.content = (content.text) ? content.text : content;
	} else if(role == 'assistant'){
		// We update the content to assistants content as the next we will process the AI generated content.
		soft_ai.content = content;
		content = spro_markdown_to_html(content);
	}

	chat_response = `<div class="spro-chat-response" data-type="${role}">
		<p><b>${role.charAt(0).toUpperCase() + role.slice(1)}:</b></p>
		<div>${content}</div>
			${role === 'assistant' ? '<p class="spro-response-actions"><button class="spro-ai-use-this">Use This</button><button class="spro-copy-ai-response"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 7c0-.932 0-1.398-.152-1.765a2 2 0 0 0-1.083-1.083C12.398 4 11.932 4 11 4H8c-1.886 0-2.828 0-3.414.586S4 6.114 4 8v3c0 .932 0 1.398.152 1.765a2 2 0 0 0 1.083 1.083C5.602 14 6.068 14 7 14" stroke="#222"/><rect x="10" y="10" width="10" height="10" rx="2" stroke="#222"/></svg></button></p>':''}
	</div>`;

	response_section.append(chat_response);

	// Bringing the response in the view port.
	response_section.find('.spro-chat-response').last().get(0).scrollIntoView({
		behavior: 'smooth',
		block: 'start'
	});
}

function spro_markdown_to_html(markdown){
	// We are using markdown because the html responses are not that good, and can have unexpected tags, having markdown makes sure the tags which we will have to handle will be basic which won't cause any issue getting added to WordPress.
	let content = marked.parse(markdown);
	
	// We need to remove the p tag in li as that breaks the gutenberg editor formatting for list.
	content = content.replace(/<li>(.*?)<\/li>/gs, (match) => {
		return match.replace(/<\/?p>(<br\/?>)?/gm, '');
	});
	
	return content;
}