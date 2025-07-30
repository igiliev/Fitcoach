/*
PAGELAYER
http://pagelayer.com/
(c) Pagelayer Team
*/

// Things to do on document load
jQuery(document).ready(function(){
	
	jQuery('.pagelayer-slides').each(function(){
		pagelayer_pl_slides(jQuery(this));
	});
	
	jQuery('.pagelayer-featured_img').each(function(){
		pagelayer_pl_image(jQuery(this));
	});
	
	jQuery('.pagelayer-wp_posts_slider').each(function(){
		pagelayer_wp_posts_slider(jQuery(this));
	});
  
	jQuery('.pagelayer-review_slider').each(function(){
		pagelayer_reviews_slider(jQuery(this));
	});
	
	// Facebook Apps
	if(jQuery('[pagelayer-facebook-load]')){
		pagelayer_fb_apps(jQuery(this));
	}
	
	// For Pagelayer Pro
	jQuery('.pagelayer-image_hotspot').each(function(){
		pagelayer_image_hotspot(jQuery(this));
	});
	
	jQuery('.pagelayer-chart').each(function(){
		pagelayer_chart(jQuery(this));
	});
	
	jQuery('.pagelayer-table').each(function(){
		pagelayer_table(jQuery(this));
	});
	
	jQuery('.pagelayer-audio').each(function(){
		pagelayer_audio(jQuery(this));
	});
	
	jQuery('.pagelayer-post_folio').each(function(){
		pagelayer_post_folio(jQuery(this));
	});
	
	jQuery('.pagelayer-search').each(function(){
		pagelayer_search_form(jQuery(this));
	});
	
	jQuery('.pagelayer-sitemap').each(function(){
		pagelayer_sitemap_add_attributes(jQuery(this));
	});
	
	jQuery('.pagelayer-posts').each(function(){
		var is_slider = jQuery(this).find('.pagelayer-posts-container').data('enable_slider');
		if(is_slider){
			pagelayer_pl_posts(jQuery(this));
		}
	});
	
	jQuery('.pagelayer-video_slider').each(function(){
		pagelayer_pl_video_slider(jQuery(this));
	});
	
	jQuery('.pagelayer-woo_menu_cart').each(function(){
		pagelayer_woo_menu_cart(jQuery(this));
	});
		
	jQuery('.pagelayer-popup-modal').each(function(){
		pagelayer_popup_setup(jQuery(this));
	});
	
	jQuery('.pagelayer-sticky-ele').each(function(){
		pagelayer_ele_sticky_handler(jQuery(this));
	});
	
	jQuery('.pagelayer-scrolling-effects').each(function(){
		pagelayer_scrolling_effects(jQuery(this));
	});
	
	jQuery('.pagelayer-mouse-effects').each(function(){
		pagelayer_mouse_effects(jQuery(this));
	});
	
	jQuery('.pagelayer-img_portfolio').each(function(){
		pagelayer_pl_img_portfolio(jQuery(this));
	});
	
	jQuery('.pagelayer-single_img').each(function(){
		pagelayer_pl_image(jQuery(this));
	});
	
	jQuery('.pagelayer-splash-container').each(function(){
		pagelayer_pl_splash_screen(jQuery(this));
	});
	
	jQuery('.pagelayer-fb-page-container, .pagelayer-fb-embed-container').each(function(){
		pagelayer_fb_width(jQuery(this));
	});

	jQuery('.pagelayer-before_after').each(function(){
		pagelayer_before_after_slider(jQuery(this));
	});
	
	jQuery('.pagelayer-image_map').each(function(){
		pagelayer_pl_image_map(jQuery(this));
		
		// Make image map editable
		if(!pagelayer_empty(pagelayer_is_live)){
			pagelayer_imgmap_handler(jQuery(this));
		}
	});
  
});

jQuery(window).resize(function(){
	jQuery('.pagelayer-scrolling-effects').each(function(){
		pagelayer_scrolling_effects(jQuery(this));
	});
});

// Add all shuffle instance in this object
var shuffleInstance = {};

// Portfolio Gallery
function pagelayer_pl_img_portfolio(jEle){
	
	var id = jEle.attr('pagelayer-id');
	var arr = [];
	
	// Fetch all categories in array 
	jEle.find('[port-cat]').each(function(){
		
		var cat = jQuery(this).attr('port-cat');
		
		if(cat){
			
			//var sel = jQuery(this).parent('.pagelayer-ele-wrap') ? jQuery(this).parent('.pagelayer-ele-wrap') : jQuery(this);
			
			jQuery(this).attr('data-groups','["'+cat+'"]');
			
			if(!arr.includes(cat)){
				arr.push(cat);
				
			}
		}
	})
	
	// Create category button html
	var html = '<button class="pagelayer-pfbtn" data-pgl_pfbtn="all">All</button>';
	
	arr.forEach(function(val){
		if(val && val != 'all'){
			html += '<button class="pagelayer-pfbtn" data-pgl_pfbtn="'+val+'">'+val+'</button>';
		}
	});
	
	// Append category buttons to holder
	jEle.find('.pagelayer-category-holder').empty().append(html);
	
	
	var setup = jEle.attr('pagelayer-setup');
	
	// Already setup ? then distroy shuffle
	if(setup && setup.length > 0 && !pagelayer_empty(shuffleInstance[id])){
		shuffleInstance[id].destroy();
	}
	
	// Create an shuffle instance
	var Shuffle = window.Shuffle;
	var element = jEle.find('.pagelayer-img_portfolio-holder');
	var sizer = jEle.find('.pagelayer-img_portfolio-holder');
	
	shuffleInstance[id] = new Shuffle(element,{
	  itemSelector: '.pagelayer-img_portfolio-holder>div',
	  //sizer: sizer // could also be a selector: '.my-sizer-element'
	});
	
	// set click events on category buttons
	if(!window.location.href.includes('pagelayer-live=1')){
		jEle.find('button').on("click", function(){
			shuffleInstance[id].filter(jQuery(this).attr('data-pgl_pfbtn'));
		});
	}
	
	// Set that we have setup everything
	jEle.attr('pagelayer-setup', 1);
	
}

// Reset Scrolling effects
function pagelayer_reset_scrolling(jEle, parentEle){
	
	if(jEle.length < 1){
		return;
	}
	
	var style = jEle.get(0).style;
	style.setProperty("--transY", 0 +"px");
	style.setProperty("--transX", 0 +"px");
	style.setProperty("--rot", 0 +"deg");
	jEle.css({"transform":"" , "opacity":"1" , "filter":"none"});
	parentEle.off('scroll.'+jEle.attr('pagelayer-id'));
}

// Handle the scrolling effects
function pagelayer_scrolling_effects(jEle, parentEle){
	
	parentEle = parentEle || jQuery(pagelayerGetDocumentElement());
	
	var win = pagelayerGetCurrentWindow();
	
	pagelayer_reset_scrolling(jEle, parentEle);
	
	// Is this element to be scrolled on this Screen Size ?
	var screens = jEle.attr('ele_motion_effect_on');
	if(pagelayer_empty(screens) || screens.search(pagelayer_get_media_mode()) == -1){
		return;
	}
	
	var style = jEle.get(0).style;
	var totalProperty = '';
	var documentHeight = parentEle.height();
	var screenMiddle = documentHeight/2;	
	var eMid = documentHeight/2;// By default motion_area-entire_page
	
	if(jEle.attr('motion_area') == 'viewport'){
		jEle.css('top', '0px');
		// For viewPort in Gutenberg
		if (parentEle.hasClass('interface-interface-skeleton__content')) {
			var scrollTopOfDiv = parentEle.scrollTop();
			var offsetFromTopOfDiv = jEle.parent().offset().top - parentEle.offset().top + scrollTopOfDiv;
			eMid = offsetFromTopOfDiv + jEle.parent().height() / 2;
		}else{
			eMid = jEle.parent().offset().top + jEle.parent().height() / 2;
		}

		screenMiddle = jQuery(win).height() / 2;
	}
	
	var screenHeightPercent = screenMiddle/100;
	
	// Vertical Scroll
	if(jEle.attr('ele_vertical_scroll')){
		if(jEle.attr('motion_area')=='entire_page'){
			jEle.offset({top:(documentHeight/2)});
		}
		totalProperty += "translateY(var(--transY))";
		var vertical_top = Math.ceil(jEle.attr('ele_v_sc_top_viewport')*(screenHeightPercent));
		var vertical_bottom = -Math.ceil(jEle.attr('ele_v_sc_bottom_viewport')*(screenHeightPercent));
	}
	
	// Horizontal scroll
	if(jEle.attr('ele_horizontal_scroll')){		
		totalProperty += "translateX(var(--transX))";
		var screenWidthPercent = jQuery(win).width()/200;
		var horizontal_right = (Math.ceil(jEle.attr('ele_h_sc_right_viewport')*(screenWidthPercent)));
		var horizontal_left = -(Math.ceil(jEle.attr('ele_h_sc_left_viewport')*(screenWidthPercent)));	
	}
	
	// Transparency
	if(jEle.attr('ele_transparency')){
		var fadingLevel = 1-jEle.attr('ele_transp_level')/10;			
		var transparency_top = Math.ceil(jEle.attr('ele_transp_top_viewport')*(screenHeightPercent));
		var transparency_bottom = Math.ceil(jEle.attr('ele_transp_bottom_viewport')*(screenHeightPercent));
	}
	
	// Blur
	if(jEle.attr('ele_blur')){	
		var blurringLevel = jEle.attr('ele_blur_level');		
		var blur_top = Math.ceil(jEle.attr('ele_blur_top_viewport')*(screenHeightPercent));
		var blur_bottom = Math.ceil(jEle.attr('ele_blur_bottom_viewport')*(screenHeightPercent));		
	}
	
	// Rotate
	if(jEle.attr('ele_rotate')){
		totalProperty += "rotate(var(--rot))";
		var rotate_top = Math.ceil(jEle.attr('ele_rot_top_viewport')*(screenHeightPercent));
		var rotate_bottom = -Math.ceil(jEle.attr('ele_rot_bottom_viewport')*(screenHeightPercent));
	}
	
	// Scale
	if(jEle.attr('ele_scale')){	
		var childScale = jEle.children("div");
		var scalingLevel = jEle.attr('ele_scl_level');		
		var scale_top = Math.ceil(jEle.attr('ele_scl_top_viewport')*(screenHeightPercent));
		var scale_bottom = Math.ceil(jEle.attr('ele_scl_bottom_viewport')*(screenHeightPercent));	
	}
	
	if(totalProperty != ''){
		jEle.css('transform', totalProperty);
	}
	
	// On Scroll calc and set
	parentEle.on('scroll.'+jEle.attr('pagelayer-id'), function(){	
	
		var vpMid = parentEle.scrollTop() + jQuery(win).height()/2;
		
		if(jEle.attr('ele_vertical_scroll')){
			var preCalc = ((eMid-vpMid)/10)*(jEle.attr('ele_v_sc_speed'));
			if(jEle.attr('ele_v_sc_direction') == 'opposite'){
				if(-preCalc<=vertical_top && -preCalc>vertical_bottom){
					style.setProperty("--transY", preCalc +"px");
				}
			}else if(jEle.attr('ele_v_sc_direction') == 'similar'){
				if(preCalc<=vertical_top && preCalc>vertical_bottom){
					style.setProperty("--transY", -preCalc +"px");	
				}
			}
		}
		
		if(jEle.attr('ele_horizontal_scroll')){
			var preCalc = (eMid-vpMid)*(jEle.attr('ele_h_sc_speed')/2);
			if(jEle.attr('ele_h_sc_direction')=='toleft'){
				if(preCalc<=horizontal_right && preCalc>horizontal_left)
					style.setProperty("--transX", preCalc +"px");	
			}else if(jEle.attr('ele_h_sc_direction')=='toright'){
				if(-preCalc<=horizontal_right && -preCalc>horizontal_left)
					style.setProperty("--transX", -preCalc +"px");					
			}
		}

		if(jEle.attr('ele_transparency')){			
			var preCalc = (vpMid)-eMid;
			
			if(preCalc>(-transparency_bottom) && preCalc<=transparency_top){
				
				var eMidB = eMid-transparency_bottom;
				var eMidT = eMid+transparency_top;
				var eMidTB = eMidT-eMidB;
				
				if(jEle.attr('ele_transp_type')=='fadein'){
					var fadeIn = ((vpMid-eMidB)/eMidTB)+fadingLevel;
					jEle.css("opacity",fadeIn);
					
				}else if(jEle.attr('ele_transp_type')=='fadeout'){
					var fadeOut = ((eMidT-vpMid)/eMidTB)+fadingLevel;
					jEle.css("opacity",fadeOut);
					
				}else if(jEle.attr('ele_transp_type')=='fadeinout'){
					var fadeInOut1 = ((vpMid-eMidB)/(eMidTB/2))+fadingLevel;
					var fadeInOut2 = ((eMidT-vpMid)/(eMidTB/2))+fadingLevel;
					
					if(fadeInOut1<1+fadingLevel && fadeInOut1>0+fadingLevel){
						jEle.css("opacity",fadeInOut1);					
					}else if(fadeInOut2<1+fadingLevel && fadeInOut2>0+fadingLevel){
						jEle.css("opacity",fadeInOut2);										
					}
					
				}else if(jEle.attr('ele_transp_type')=='fadeoutin'){
					var fadeOutIn1 = (((vpMid-eMidB)/(eMidTB/2)-1))+fadingLevel;
					var fadeOutIn2 = (((eMidT-vpMid)/(eMidTB/2)-1))+fadingLevel;
					
					if(fadeOutIn2<1+fadingLevel && fadeOutIn2>0+fadingLevel){
						jEle.css("opacity",fadeOutIn2);					
					}else if(fadeOutIn1<1+fadingLevel && fadeOutIn1>0+fadingLevel){
						jEle.css("opacity",fadeOutIn1);										
					}
				}					
			}				
		}
		
		if(jEle.attr('ele_blur')){			
			var preCalc = vpMid-eMid;
			
			if(preCalc>-(blur_bottom) && preCalc<=blur_top){
				
				var eMidB = eMid-blur_bottom;
				var eMidT = eMid+blur_top;
				var eMidTB = eMidT-eMidB;
				
				if(jEle.attr('ele_blur_type')=='blurin'){
					var blurIn = ((eMidT-vpMid)/eMidTB)*blurringLevel;					
					jEle.css("filter","blur("+blurIn+"px)");
				}else if(jEle.attr('ele_blur_type')=='blurout'){
					var blurOut = ((vpMid-eMidB)/eMidTB)*blurringLevel;
					jEle.css("filter","blur("+blurOut+"px)");
				}else if(jEle.attr('ele_blur_type')=='blurinout'){
					var blurInOut1 = (vpMid-eMidB)/(eMidTB/2)*blurringLevel;
					var blurInOut2 = (eMidT-vpMid)/(eMidTB/2)*blurringLevel;
					
					if(blurInOut1<1*blurringLevel && blurInOut1>0.01*blurringLevel){
						jEle.css("filter","blur("+blurInOut1+"px)");					
					}else if(blurInOut2<1*blurringLevel && blurInOut2>0.01*blurringLevel){
						jEle.css("filter","blur("+blurInOut2+"px)");										
					}
				}else if(jEle.attr('ele_blur_type')=='bluroutin'){
					var blurOutIn1 = ((vpMid-eMidB)/(eMidTB/2)-1)*blurringLevel;
					var blurOutIn2 = ((eMidT-vpMid)/(eMidTB/2)-1)*blurringLevel;
					
					if(blurOutIn2<1*blurringLevel && blurOutIn2>0.01*blurringLevel){
						jEle.css("filter","blur("+blurOutIn2+"px)");					
					}else if(blurOutIn1<1*blurringLevel && blurOutIn1>0.01*blurringLevel){							
						jEle.css("filter","blur("+blurOutIn1+"px)");					
					}
				}			
			}
		}
		
		if(jEle.attr('ele_rotate')){
			var preCalc = ((vpMid-eMid)/2);
			var rotSpeed = jEle.attr('ele_rot_speed')/2;
			if(jEle.attr('ele_rot_direction')=='clockwise'){
				if(preCalc<=rotate_top && preCalc>rotate_bottom){
					style.setProperty("--rot", preCalc*rotSpeed +"deg");
				}
			}else if(jEle.attr('ele_rot_direction')=='anticlockwise'){
				if(preCalc<=rotate_top && preCalc>rotate_bottom){
					style.setProperty("--rot", -preCalc*rotSpeed +"deg");
				}
			}
		}
		
		if(jEle.attr('ele_scale')){
			
			var preCalc = vpMid-eMid;
			
			if(preCalc>-(scale_bottom) && preCalc<=scale_top){
				
				var eMidB = eMid-scale_bottom;
				var eMidT = eMid+scale_top;
				var eMidTB = eMidT-eMidB;
				
				if(jEle.attr('ele_scl_direction')=='scaleup'){
					var scaleUp = ((vpMid-eMidB)/eMidTB)*scalingLevel+1;
					childScale.css("transform","scale("+ scaleUp +")");
				}else if(jEle.attr('ele_scl_direction')=='scaledown'){
					var scaleDown = ((eMidT-vpMid)/eMidTB)*scalingLevel+1;
					childScale.css("transform","scale("+ scaleDown +")");
				}else if(jEle.attr('ele_scl_direction')=='scaleupdown'){
					var scaleUpDown2 = (vpMid-eMidB)/(eMidTB/2)*scalingLevel+1;
					var scaleUpDown1 = (eMidT-vpMid)/(eMidTB/2)*scalingLevel+1;
					
					if(scaleUpDown1<1*scalingLevel && scaleUpDown1>0.1*scalingLevel){
						childScale.css("transform","scale("+ scaleUpDown1 +")");
					}else if(scaleUpDown2<1*scalingLevel && scaleUpDown2>0.1*scalingLevel){
						childScale.css("transform","scale("+ scaleUpDown2 +")");
					}
				}else if(jEle.attr('ele_scl_direction')=='scaledownup'){
					var scaleDownUp1 = ((vpMid-eMidB)/(eMidTB/2)-1)*scalingLevel+1;
					var scaleDownUp2 = ((eMidT-vpMid)/(eMidTB/2)-1)*scalingLevel+1;
					
					if(scaleDownUp1<1*scalingLevel && scaleDownUp1>0.1*scalingLevel){
						childScale.css("transform","scale("+ scaleDownUp1 +")");		
					}else if(scaleDownUp2<1*scalingLevel && scaleDownUp2>0.1*scalingLevel){		
						childScale.css("transform","scale("+ scaleDownUp2 +")");		
					}
				}
			}			
		}			
	});
};

function pagelayer_mouse_reset(jEle){
	
	var win = pagelayerGetCurrentWindow();
	
	jEle.css("transform", "");
	var style = jEle.get(0).style;
	style.setProperty("--transY", 0 +"px");
	style.setProperty("--transX", 0 +"px");
	style.setProperty("--rotX", 0 +"deg");
	style.setProperty("--rotY", 0 +"deg");
	jQuery(win).off('mousemove.'+jEle.attr('pagelayer-id'));
}

// Mouse Effects setup
function pagelayer_mouse_effects(jEle){
	
	pagelayer_mouse_reset(jEle);
	
	var style = jEle.get(0).style;
	var mouseproperty = '';
	
	var halfWidth = jEle.parent().offset().left+jEle.parent().width()/2;
	var halfHeight = jEle.parent().offset().top+jEle.parent().height()/2;
	
	if(jEle.attr('ele_m_track')){
		mouseproperty += 'translateX(var(--transX)) translateY(var(--transY))';
		var mouseLevel = jEle.attr('ele_m_tr_level');
	}
	
	if(jEle.attr('ele_3d_tilt')){
		mouseproperty += ' rotateX(var(--rotX)) rotateY(var(--rotY))';
		jEle.parent().css("perspective", "1200px");
		var tiltLevel = jEle.attr('ele_3d_tilt_level');
	}
	
	if(mouseproperty != ''){
		jEle.css("transform", mouseproperty);
	}
	
	var win = pagelayerGetCurrentWindow();
	
	jQuery(win).bind('mousemove.'+jEle.attr('pagelayer-id'), function(event){
		
		if(jEle.attr('ele_m_track')){		
			if(jEle.attr('ele_m_tr_direction') == 'opposite'){
				var x =((event.pageX - halfWidth)*mouseLevel)/10;
				var y =((event.pageY - halfHeight)*mouseLevel)/10;
				style.setProperty("--transX",-x +"px");
				style.setProperty("--transY",-y +"px");
				//console.log(halfWidth +"  "+ mouseLevel);
			}else if(jEle.attr('ele_m_tr_direction') == 'same'){
				var x =((event.pageX - halfWidth)*mouseLevel)/10;
				var y = ((event.pageY-halfHeight)*mouseLevel)/10;
				style.setProperty("--transX",x +"px");
				style.setProperty("--transY",y +"px");
			}
		}
	
		if(jEle.attr('ele_3d_tilt')){				
			if(jEle.attr('ele_3d_tilt_direction')=='opposite'){
				var x=((event.pageX - halfWidth)*tiltLevel)/100;
				var y=((event.pageY - halfHeight)*tiltLevel)/100;
				style.setProperty("--rotX", y +"deg");
				style.setProperty("--rotY",-x + "deg");
			}else if(jEle.attr('ele_3d_tilt_direction')=='same'){
				var x=((event.pageX - halfWidth)*tiltLevel)/100;
				var y=((event.pageY - halfHeight)*tiltLevel)/100;				
				style.setProperty("--rotX",-y + "deg");
				style.setProperty("--rotY",x +"deg");
			}
		}
	});
}

// Element sticky handler
function pagelayer_ele_sticky_handler(jEle, parentEle){
	
	// Set element stickied
	if(jEle.attr('pagelayer-stickied-ele') == 1){
		return;
	}
	
	parentEle = parentEle || jQuery(window);

	jEle.attr('pagelayer-stickied-ele', 1);
	
	var jEleTop = jEle.offset().top;
	var jEleW = jEle[0].clientWidth +'px';
	var oldstyle = jEle.attr('style') || '';	
	
	var pagelayer_sticky = function(){
		var position = jEle.attr('data-sticky-position') || '';
		var offset = jEle.attr('data-sticky-offset') || 0;
		var sticky_in_col = jEle.attr('data-sticky_in_col') || '';
		var sticky_on = jEle.attr('data-sticky-on') || '';
		sticky_on = sticky_on.split(',');
	
		// If match the media
		var do_sticky = false;
		for(var x in sticky_on){
			if(pagelayer_get_media_mode() == sticky_on[x]){
				do_sticky = true;
			}
		}
		
		// If there is a wrapper
		var wrapper = jEle.parent('.pagelayer-ele-wrap');
		if(wrapper.length > 0 ){
			
			// Reset jEle
			jEle.next('.pagelayer-sticky-space-holder').remove();
			jEle.attr('style', oldstyle);
			
			// Get wrapper old syle
			oldstyle = wrapper.attr('oldstyle') || '';
			if(pagelayer_empty(oldstyle)){
				oldstyle = wrapper.attr('style') || '1';
				wrapper.attr('oldstyle', oldstyle);
			}else if(oldstyle == '1'){
				oldstyle = '';
			}
			
		}else{
			wrapper = jEle;
		}
		
		var parentHeight = parentEle.height();
		var parentScrollTop = parentEle.scrollTop();
		var topToCheck = jEleTop - parentScrollTop;
		
		// Do fixed the element
		var pagelayer_fixed = function(){
			
			var opp_position = (position == 'top') ? 'bottom' : 'top';
			var fixed_css = {'position': 'fixed', 'width': jEleW, 'max-width': jEleW, [position] : offset +'px', [opp_position]: 'auto','z-index' : '99'}
			
			// For the sticky in columns 
			if(!pagelayer_empty(sticky_in_col)){
				//fixed_css['position'] = 'sticky';
				var container = wrapper.parent();
				var containerTop = container.offset().top;
				if((containerTop + container.height()) - parentScrollTop <= wrapper.outerHeight(true) + offset || containerTop - parentScrollTop >= parentHeight - wrapper.outerHeight(true) -  offset){
					fixed_css['position'] = 'absolute';
					fixed_css[opp_position] = '0px';
					fixed_css[position] = 'auto';
				}
			}
			
			// Add position holder element
			if(wrapper.next('.pagelayer-sticky-space-holder').length < 1 ){
				var clone = wrapper.clone(true);
				clone.addClass('pagelayer-sticky-space-holder');
				clone.css({'visibility' : 'hidden', 'transition': 'none 0s ease 0s', 'animation': '0s ease 0s 1 normal none running none'});
				
				// Remove pagelayer-parent attributes to avoid group children list double
				if(!pagelayer_empty(pagelayer_is_live)){
					clone.removeClass('pagelayer-ele-wrap');
					clone.removeClass('pagelayer-ele');
					clone.find('.pagelayer-ele').removeClass('pagelayer-ele');
					clone.find('[pagelayer-id]').removeAttr('pagelayer-id');
				}
				
				// Add 
				wrapper.after(clone);
				
				//wrapper.after('<div class="pagelayer-sticky-space-holder" style="width:'+wrapper.outerWidth(true)+'px;height:'+wrapper.outerHeight(true)+'px;visibility:hidden;margin:0px;"></div>');
			}
			
			// Css for fixed the element
			wrapper.css(fixed_css);
		}
		
		// TODO: manage margin
		var mTopEle = parseFloat(wrapper.css('margin-top'));
		var mBottomEle = parseFloat(wrapper.css('margin-bottom'));
		
		// Fixed on top
		if( position == 'top' && (topToCheck <= offset+mTopEle && do_sticky) ){
			pagelayer_fixed();
		// Fixed on bottom
		}else if( position == 'bottom' && topToCheck + wrapper.outerHeight()+mBottomEle >= parentHeight - offset && do_sticky) {
			pagelayer_fixed();
		// Set old style
		}else{
			wrapper.attr('style', oldstyle);
			wrapper.next('.pagelayer-sticky-space-holder').remove();
			jEleW = wrapper[0].clientWidth +'px';
			jEleTop = wrapper.offset().top;
		}
		
		return true;
	};
	
	// Run for the first time
	pagelayer_sticky();
	
	// On window scroll
	parentEle.scroll(pagelayer_sticky);	
	parentEle.resize(pagelayer_sticky);	
	
}

function pagelayer_pl_posts(jEle){
	
	var ul = jEle.find('.pagelayer-posts-container');
	
	ul.addClass('pagelayer-owl-carousel pagelayer-owl-theme');
	
	// Build the options
	var options = pagelayer_fetch_dataAttrs(ul, 'data-owl-');
	
	// Already setup ?
	var setup = jEle.attr('pagelayer-setup');
	
	// If already setup then Destroy Owl
	if(setup && setup.length > 0){
		ul.pagelayerOwlCarousel('destroy');
		ul.find('[class^="pagelayer-owl-"]').remove();
	}
	
	//console.log(options);
	ul.pagelayerOwlCarousel(options);
	
	// Set that we have setup everything
	jEle.attr('pagelayer-setup', 1);
}

function pagelayer_pl_slides(jEle){
	var ul = jEle.find('.pagelayer-slides-holder');
	
	// Build the options
	var options = pagelayer_fetch_dataAttrs(ul, 'data-slides-');
	var blurNav = false;
	
	if(pagelayer_is_live){
		
		if(!options.nav && !options.dots) {
			options.nav = true;
			options.dots = true;
			blurNav = true;
		}
		
		options.mouseDrag = false;
	}
	
	pagelayer_owl_init(jEle, ul, options);
	
	if(blurNav){
		ul.find('.pagelayer-owl-nav button').css({'filter': 'blur(2px)'});
		ul.find('.pagelayer-owl-dot').css({'filter': 'blur(2px)'});
	}
	
	// Shows element animations after slide change
	ul.on('refresh.owl.carousel translate.owl.carousel', function(e) {
		var slide = jQuery(this);
    
		if(!pagelayer_is_live) {
			slide.find('.pagelayer-wow').addClass('animated');
			return; 
		}
			
		slide.find('.pagelayer-ele').each(function() {
			var ele = jQuery(this);
			
			if(ele.css('animation-name') == 'none') {
				return;
			}
			
			ele.addClass('pagelayer-wow animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
				ele.removeClass('pagelayer-wow animated');
			});
		});
	});
	
}

// For Pagelayer Pro
// Show tooltip in image hotspot
function pagelayer_image_hotspot(jEle){
	
	// Drag and Drop function for image
	if (typeof pagelayer_preDAndD_image !== "undefined") {
		pagelayer_preDAndD_image(jEle);
	}
	
	var tooltip_click = jEle.find('.pagelayer-icon-holder');
	var icon_holder = jEle.find('.pagelayer-hotspots-icon-holder');
	icon_holder.off('click');
	icon_holder.find('.pagelayer-tooltip-text').removeClass('pagelayer-tooltip-on-click');
	
	if(tooltip_click.hasClass('pagelayer-hotspots-click')){
		icon_holder.each(function(){
			
			jQuery(this).click(function(){
				jQuery(this).find( '.pagelayer-tooltip-text' ).toggleClass('pagelayer-tooltip-on-click');
			})	

		});
	}	
	
	// Assigning animation classes to icon holder
	if(!pagelayer_empty(tooltip_click.attr('pagelayer-animation'))){
		icon_holder.addClass('pagelayer-animation-'+tooltip_click.attr('pagelayer-animation'));
	}
	
}

var pagelayer_chart_objects = {};

// Show Chart render
function pagelayer_chart(jEle){
	
	var holder = jEle.find('.pagelayer-chart-holder');
	var id = jEle.attr('pagelayer-id');

	var type = holder.attr('chart-type');
	var labels = holder.attr('chart-labels') || '';
	var legend = holder.attr('chart-legend');
	
	var chart_datasets = [];
	labels = labels.split(',');
	//console.log(labels);
	
	jEle.find('.pagelayer-chart-child-holder').find('.pagelayer-chart-datasets').each(function(){
		var tmp_dataset = {};
		//console.log(jQuery(this).attr('chart-datasets'));
		var chartDatasets = jQuery(this).attr('chart-datasets') || '';
		tmp_dataset['data'] = chartDatasets.split(',');
		//tmp_dataset['data'] = [734,784,2478,5267,433];
		tmp_dataset['label'] = jQuery(this).attr('datasets-label');
		tmp_dataset['backgroundColor'] = ( holder.attr('chart-colors') ? holder.attr('chart-colors').split(',') : jQuery(this).attr('dataset-bg') );
		tmp_dataset['borderColor'] = jQuery(this).attr('border-color');
		tmp_dataset['borderWidth'] = 1;
		tmp_dataset['fill'] = ( jQuery(this).attr('dataset-fill') ? true : false );
		chart_datasets.push(tmp_dataset);
	});
	//console.log(chart_datasets);
	//chart_datasets = chart_datasets.join(',');
	
	if(id in pagelayer_chart_objects && typeof pagelayer_chart_objects[id].destroy == 'function'){
		pagelayer_chart_objects[id].destroy();
	}
	
	pagelayer_chart_objects[id] = new Chart(jEle.find('.pagelayer-chart-holder'), {
		type: type,
		data: {
		  labels: labels,
		  datasets: 
		  chart_datasets
		},
		options: {
			//rotation: holder.attr('data-pierotate') * Math.PI,
			//circumference: holder.attr('data-circumference') * Math.PI,
			maintainAspectRatio: ( holder.attr('chart-height') ? false : true ),
			legend: { 
				display: ( legend ? true : false ),
				position: legend,
			},
			scales: {
				xAxes: [
					{
						barPercentage: 1,
						categoryPercentage: 0.9,
						ticks:{
							beginAtZero: holder.attr('data-xbegin'),
							fontColor: holder.attr('data-xcolor'),
							fontSize: holder.attr('data-xsize'),
							autoSkip: false,
							maxRotation: holder.attr('data-xrotate'),
							minRotation: holder.attr('data-xrotate')
						},
						//stacked: true
					}
				],
				yAxes:[
					{
						ticks:{
							beginAtZero: holder.attr('data-ybegin'),
							fontColor: holder.attr('data-ycolor'),
							fontSize: holder.attr('data-ysize'),
							autoSkip: false,
							maxRotation: holder.attr('data-yrotate'),
							minRotation: holder.attr('data-yrotate')
						},
						//stacked: true
					}
				]
			}
		}
	});

}

// Show table render
function pagelayer_table(jEle){
	
	var tHolder = jEle.find('.pagelayer-table-holder');
	var dHolder = jEle.find('.pagelayer-data-holder');
	var trEle = dHolder.find(".pagelayer-table_row");
	var tdlength = 0;
	tHolder.empty();
	
	/*trEle.each(function(){
		var tdEle = jQuery(this).find(".pagelayer-table_col").length;
		if(tdlength < tdEle){tdlength = tdEle}
	});*/
	
	// Add rows
	trEle.each(function(){
		var this_trEle = jQuery(this);
		var this_trId = this_trEle.attr('pagelayer-id');
		var tdEle = this_trEle.find(".pagelayer-table_col");
		
		var html = '';
		
		// Add columns
		tdEle.each(function(){
			var td_data_Holder = jQuery(this).find('.pagelayer-col-data');
			var td_id = jQuery(this).attr('pagelayer-id');
			var tdata = td_data_Holder.attr('data-td') || '';
			var t_tag = td_data_Holder.attr('data-tag') || '';
			var attrs = {};
			var col_attr = '';
			
			attrs['colspan']= td_data_Holder.attr('data-colspan') || '';
			attrs['rowspan'] = td_data_Holder.attr('data-rowspan') || '';
			
			if(!pagelayer_empty(pagelayer_is_live)){
				 attrs['pagelayer-ref-id'] = td_id;
				 attrs['pagelayer-editable'] = 'data';
				 attrs['contenteditable'] = 'true';
			}
			
			for(var key in attrs){
				if(!pagelayer_empty(attrs[key])){
					col_attr += key+'="'+attrs[key]+'"';
				}
				
			}
			
			html = html+'<'+t_tag +' '+col_attr+' pagelayer-table-id="'+td_id+'">'+tdata+'</'+t_tag+'>';
		});
		
		/* if(tdlength > tdEle.length){
			
			var extra_td = tdlength - tdEle.length;
			for(var i=0; extra_td >i; i++){
				html = html+'<td></td>';
			}
		} */
		
		tHolder.append('<tr pagelayer-table-id="'+this_trId+'">'+html+'</tr>');
	});
	
	if(!pagelayer_empty(pagelayer_is_live)){
		tHolder.on('input', '[contenteditable]', function(){
			var jEle = jQuery(this);
			var val = pagelayer_trim(jEle.html());
			var id = jEle.closest('[pagelayer-ref-id]').attr('pagelayer-ref-id');
			
			if(pagelayer_empty(id)){
				return;
			}
			
			jQuery('[pagelayer-id='+id+']').find('.pagelayer-col-data').attr('data-td', val);
			
		});
	}
	
}

function pagelayer_audio(jEle){
	
	var audio = jQuery(jEle.find('audio'));
	var container = jEle.find('.pagelayer-audio-container');
	var features = ['playpause','tracks','fullscreen'];
	
	(container.attr('show_duration') ? features.push('duration') : '' );
	(container.attr('show_progress') ? features.push('progress') : '' );
	(container.attr('show_current') ? features.push('current') : '' );
	(container.attr('show_volume') ? features.push('volume') : '' );
	
	audio.mediaelementplayer({
		//features: ['playpause','duration','progress','current','volume','tracks','fullscreen']
		features: features
	});
}

// Post Portfolio Handler - Premium
function pagelayer_post_folio(jEle){
	var btn = jEle.find('.pagelayer-postfolio-btn');
	var thumb = jEle.find('.pagelayer-postfolio-thumb');
	
	btn.unbind('click');
	btn.on('click', function(){
		var btn_cat = jQuery(this).data("filter");
		
		if(btn_cat == 'all'){
			jQuery(thumb).fadeIn(1200);
		}else{
			thumb.each(function(){
				var thumb_cat = jQuery(this).data("category");
				if(!pagelayer_empty(thumb_cat)){
					thumb_cat = thumb_cat.split(' ');
				
					if(jQuery.inArray( btn_cat, thumb_cat ) == -1){
						jQuery(this).hide();
					}else{
						jQuery(this).fadeIn(600);
					}
				}else{
					jQuery(this).hide();
				}
			});
		}
	});
}

// Search Form handler - Premium
function pagelayer_search_form(jEle){
	
	// In full screen mode set auto complete offscreenBuffering
	jEle.find('.pagelayer-search-full-screen form').attr('autocomplete', 'off');
	
	jEle.find('.pagelayer-search-toggle').click(function(){
		jEle.find('.pagelayer-search-fields').toggleClass('show');
	});
	
	jEle.find('.pagelayer-search-fields').click(function(e){
		 e = window.event || e; 
		if(this === e.target) {
			jQuery(this).removeClass('show');
		}
	});
}

// Login Form handler - Premium
function pagelayer_login_submit(jEle, e){
	e.preventDefault();
	var fdata = new FormData( jQuery(jEle).closest('form')[0] );
	
	// Append the nonce
	fdata.append('pagelayer_nonce', pagelayer_global_nonce);
	
	jQuery.ajax({
		url: pagelayer_ajaxurl+'action=pagelayer_login_submit',
		type: "POST",
		data: fdata,
		processData: false,
		contentType: false,
		cache:false,
		success:function(result){				
			var json = jQuery.parseJSON(result);
			// console.log(json);
			if(!pagelayer_empty(json['error'])){
				jQuery(".pagelayer-login-error-box").html(json['error']).fadeIn().delay(10000).fadeOut();  
			}else{
				if(!pagelayer_empty(json['redirect'])){
					window.location.href = json['redirect'];
				}else{
					location.reload(true);
				}
			}			 
		}
	});
}

//Review slider handler - Premium
function pagelayer_reviews_slider(jEle){
	
	var ul = jEle.find('.pagelayer-reviews-holder');
	
	// Build the options
	var options = pagelayer_fetch_dataAttrs(ul, 'data-slides-');
	
	pagelayer_owl_init(jEle, ul, options);
	
}		

// Facebook App ID handler - Premium
function pagelayer_fb_apps(jEle){
	var app_id = jEle.find('.pagelayer-app-details').attr('pagelayer-app-id');
	
	if(pagelayer_empty(app_id) || app_id == "{{fb-app-id}}"){
		app_id = pagelayer_facebook_id;
	}
	
	// If still empty
	if(pagelayer_empty(app_id)){
		return;
	}
	
	var win = pagelayerGetCurrentWindow();
	var doc = pagelayerGetDocumentElement();
	
	win.fbAsyncInit = function() {		
		win.FB.init({
			appId            : app_id,
			autoLogAppEvents : true,
			xfbml            : true,
			version          : 'v3.3'
		});
	};
	win.FB = null;
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		js = d.createElement(s); js.id = id;
		js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0";
		fjs.parentNode.insertBefore(js, fjs);
	}(doc, 'script', 'facebook-jssdk'));
}

// SiteMap Attribute Handler- Premium
function pagelayer_sitemap_add_attributes(jEle){
	
	var nofollow = jEle.find('.pagelayer-sitemap-div-holder').data('nofollow');
	
	if(!pagelayer_empty(nofollow)){
		jEle.find('.pagelayer-sitemap-section .pagelayer-sitemap-list-item a').attr('rel','nofollow');		
	}

}

// Initialize video slider
function pagelayer_pl_video_slider(jEle){
	
	var ul = jEle.find('.pagelayer-video-slider-holder');
	
	// Build the options
	var options = pagelayer_fetch_dataAttrs(ul, 'data-slides-');
	
	pagelayer_owl_init(jEle, ul, options);
	
}

// Splash widget
function pagelayer_pl_splash_screen(jEle){
	
	if(!pagelayer_empty(pagelayer_is_live)){
		return;
	}
	
	if( jEle.attr('display_type') == "once"){
	
		if (!sessionStorage.isVisited) {
			sessionStorage.isVisited = "true";
			jEle.delay(jEle.attr('delay')).fadeIn();
		}
	}else{
			jEle.delay(jEle.attr('delay')).fadeIn();
	}
	
	jEle.find('.pagelayer-splash-close, .pagelayer-splash-bg-close').on('click', function(){
		jEle.fadeOut();
	});		
}

// Facebook width adjustment function
function pagelayer_fb_width(jEle){
	
	var facebook = (jEle.find('.fb-page').length ? jEle.find('.fb-page') : jEle.find('.fb-embed'));
	
	var fb_resize = function(){
		facebook.attr('data-width', jEle.width());	
		FB.XFBML.parse();
	};
  
	setTimeout(fb_resize, 1000);
	
	var win = pagelayerGetCurrentWindow();
	
	jQuery(win).on('resize', function(){
		setTimeout(fb_resize, 1000);
	});
}

// WooCommenrce cart
function pagelayer_woo_menu_cart(jEle){
	
	// On click menu cart
	jEle.find('.pagelayer-menu-cart-toggle').click(function(e){
		e.preventDefault();
		jEle.find('.pagelayer-menu-cart-container').toggle();
	});
	
	// On click menu cart close
	jEle.find('.pagelayer-menu-cart-close').click(function(){
		jEle.find('.pagelayer-menu-cart-container').hide();
	});
	
}

// Setup the pop-ups
function pagelayer_popup_setup(popEle){
	
	var jEle = popEle.find('.pagelayer-popup').first();
	var id = jEle.attr('pagelayer-id');
	var popup_content = popEle.find('.pagelayer-popup-modal-content');
	var data = new Object();
	
	// Set the Pou-up id
	popEle.attr('pagelayer-popup-id', id);
	
	if(jEle.length < 1) return;
	
	jQuery.each(jEle[0].attributes, function(index, att){
		if(att.name.match(/data\-/i)){
			data[att.name.substr(5)] = att.value;
		}
	});	
	
	// Show pop-up
	var popup_show = function(force){
				
		// Is there a cookie?
		if( !pagelayer_empty(data['popup_cookie_session']) && !pagelayer_empty(data['popup_cookie_name']) && pagelayer_empty(force)){
			var name = data['popup_cookie_name']+ "=";
			var decodedCookie = decodeURIComponent(document.cookie);
			var ca = decodedCookie.split(';');

			for(var i = 0; i <ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}

				if (c.indexOf(name) == 0) {
					return;
				}
			}
		}
		
		// Avoid multi time open
		if(!pagelayer_empty(popEle.attr('pagelayer-popup-Shown')) && pagelayer_empty(data['popup_multi_time'])){
			return;
		}
			
		popEle.css({'display' : 'flex'});
		popEle.attr('pagelayer-popup-Shown', 1);
		
		// Animate the Pop-up content
		if(!pagelayer_empty(data['popup_animation'])){
			popup_content.addClass(data['popup_animation'] + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
				jQuery(this).removeClass(data['popup_animation']+ ' animated');
			});
		}
		
		// Auto close
		if(!pagelayer_empty(data['popup_auto_close'])){
			var cSecond = parseFloat(data['popup_auto_close']);
			setTimeout(function(){
				popEle.find('.pagelayer-popup-close').click();
			}, cSecond * 1000);
		}
		
	}
	
	// Append the close element
	// remove close element
	popEle.find('.pagelayer-popup-close').remove();
	
	// Put close button inside or outside
	if(data['popup_cbtn_position'] == 'outside'){
		popEle.append('<div class="pagelayer-popup-close"><i class="fas fa-times" ></i></div>');
	}else{
		popEle.find('.pagelayer-popup-modal-content').append('<div class="pagelayer-popup-close"><i class="fas fa-times" ></i></div>');
	}  
  
	var closer = popEle.find('.pagelayer-popup-close');
	
	// On click close
	closer.click(function(e){
		jQuery(document).trigger('pagelayer_popup_close', e, popEle);
		e.preventDefault();
		e.stopPropagation();
		popEle.hide();
	});
	
	// Hide pop-up on click modal container
	if ( !pagelayer_empty(data['overlay_close']) ){ 
		popEle.click(function(e){
			if(jQuery(e.target).closest('.pagelayer-popup-modal-content').length > 0) return;
			closer.click();
		});
	}
	
	// Hide pop-up click on selector
	if ( !pagelayer_empty(data['selector_close']) ){ 
		jQuery(data['selector_close']).click(function(e){
			closer.click();
		});
	}
	
	// Is there a cookie to be set to be remembered ?
	if( !pagelayer_empty(data['popup_cookie_session']) && !pagelayer_empty(data['popup_cookie_name']) ){
		
		// Set cookie on close as well ?
		if(!pagelayer_empty(data['popup_cookie_close'])){
			data['popup_cookie_selector'] = '.pagelayer-popup-close'+(pagelayer_empty(data['popup_cookie_selector']) ? '' : ','+data['popup_cookie_selector']);
		}
		
		if(!pagelayer_empty(data['popup_cookie_selector'])){		
			pagelayer_popup_set_cookie(data, closer);
		}
	}
	
	// Pop-up triggers
	// If we are in editor
	if ( !pagelayer_empty(popEle.attr('pagelayer-popup-editor')) ) {
		closer.unbind('click');
		popEle.unbind('click');
		popEle.removeAttr('pagelayer-popup-Shown');
		popup_show(true);
	}
	
	// Click popup show
	if(!pagelayer_empty(data['trig_click'])){
		jQuery(data['trig_click_ele']).click(popup_show);
	}
	
	// Popup show on load
	if(!pagelayer_empty(data['trig_load'])){
		var second = parseFloat(data['trig_load_sec'] || 0);
		setTimeout(popup_show, second * 1000);
	}
	
	// Popup show on scroll
 	if(!pagelayer_empty(data['trig_scroll'])){

		// Initial state
		var scrollPos = 0;
		
		// adding scroll event
		jQuery(window).on('scroll', function(){
			
			// detects new state and compares it with the new one
			if ( scrollY > scrollPos){
				if(data['trig_scroll_dir'] == 'down'){
					let scroll_per = scrollY*100/(jQuery(document).height() - innerHeight);
					let scr_per =parseInt(data['trig_scroll_per']) || 0;
					
					if( scr_per <=  scroll_per){
						popup_show();
					}
				}
			}else if(data['trig_scroll_dir'] == 'up'){
				popup_show();
			}
			
			// saves the new position for iteration.
			scrollPos = scrollY;
		});
	}
	
	// Popup show on scroll to element
	if(!pagelayer_empty(data['trig_scroll_to_ele'])){
		
		// adding scroll event
		jEle.find(data['trig_scroll_to_ele_sel']).on('scroll', function(){
			popup_show();
		});
	}
	
	// On scroll popup show
	if(!pagelayer_empty(data['trig_page_exit_intent'])){
		jQuery(document).on('mouseleave', popup_show);
	}

	// When page load popup hide
	if(!pagelayer_empty(data['trig_before_load'])){
		closer.click();
	}
}

// Sets the cookie
function pagelayer_popup_set_cookie(data, closer){
	jQuery(data['popup_cookie_selector']).click(function(e){
		var exdays = data['popup_cookie_exp'] || 0;
		var d = new Date();
		d.setTime(d.getTime() + ( parseFloat(exdays) *24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = data['popup_cookie_name'] + "=1;" + expires + ";path=/";
		if(closer && !jQuery(e.target).is(closer)){
			closer.click();
		}
	});
}

// Before After Slider Handler
function pagelayer_before_after_slider(jEle){
	
	let event = jEle.find('.pagelayer-before-after-container').attr('data-resize-event'),
		slider = jEle.find('.pagelayer-before-after-slider'),
		after = jEle.find('.pagelayer-after-image'),
		resizer = jEle.find('.pagelayer-resizer'),
		buttons = jEle.find('.pagelayer-before-btn, .pagelayer-after-btn'),
		plID = jEle.attr('pagelayer-id');
	
	resizer.removeAttr('style');
	after.removeAttr('style');
	
	// z - Custom value for both
	var slideIt = function(e, z){
		
		z = z || false;
		
		let x, y;
		
		if(z != false){
			x =	y = z;
		}else{
			let pos = slider.offset(),
				eleWidth = slider.width(),
				eleHeight = slider.height();
						
			x = Math.min( (e.pageX - pos.left), eleWidth ) * 100 / eleWidth;
			y = Math.min( (e.pageY - pos.top), eleHeight) * 100 / eleHeight;
		}
		
		if(x < 0 || x > 99.7 || y < 0 || y > 99.7){
			return false;
		}

		if(slider.hasClass('pagelayer-before-after-slider-vertical')){
			resizer.css({'top': y+'%'});
			after.css({'clip-path': 'polygon(0px '+y+'%, 100% '+y+'%, 100% 100%, 0% 100%)'});
			return;
		}
		
		resizer.css({'left': x+'%'});
		after.css({'clip-path': 'polygon('+x+'% 0%, 100% 0%, 100% 100%, '+x+'% 100%)'});
	}
	
	var resizeOn = function(){
		slider.addClass('resize');
	}
	
	var resizeOff = function(){
		slider.removeClass('resize');
	}
	
	// Before After Button click handler
	jEle.on('click', '.pagelayer-after-btn, .pagelayer-before-btn', function(e){
		e.stopPropagation();
		e.preventDefault();
		
		var slide = (jQuery(this).hasClass('pagelayer-before-btn')) ? 90 : 10;
		
		// Start resizing
		resizeOn();

		slideIt(e, slide);
		
		// Stop resizing
		resizeOff();
	});
	
	var win = pagelayerGetCurrentWindow();
	var doc = pagelayerGetDocumentElement();
	
	// Unbind All events
	jQuery(win).unbind('mousemove.bf_slider'+plID);
	slider.unbind('click mouseover mouseout');
	resizer.unbind('mousedown');
	jQuery(doc).unbind('mouseup.bf_slider'+plID);
	
	// If there is no events
	if(event == 'none' || pagelayer_empty(event)){
		return;
	}
		
	if(event == 'hover'){
		// No need of buttons while hover effect
		buttons.fadeOut();
		slider.on('mouseover', resizeOn);
		slider.on('mouseout', resizeOff);
	}else{		
		resizer.on('mousedown', function(e){
			// To prevent widgets from being dragged.
			e.stopPropagation();
			e.preventDefault();
			resizeOn();
			buttons.fadeOut();
		});
		
		jQuery(doc).on('mouseup.bf_slider'+plID, function(e){
			resizeOff();
			buttons.fadeIn();
		});
	}

	jQuery(win).on('mousemove.bf_slider'+plID, function(e){
		
		if(!slider.hasClass('resize')){
			return;
		}
		
		// Stop divs being selected.
		e.stopPropagation();
		e.preventDefault();
		
		slideIt(e);
	});
	
	slider.on('click', slideIt);
}

// Image Map Widget
function pagelayer_pl_image_map(jEle){		
	var svgEle = jEle.find('.pagelayer-imgmap-svg');
	var data = [];

	svgEle.find('.pagelayer-imgmap-item').each(function(){
		var data_link = jQuery(this).attr('data-link');
		var data_id = jQuery(this).attr('data-id');
		data[data_id] = data_link;
	});

	pagelayer_resize_imgmap(jEle);

	jQuery(pagelayerGetCurrentWindow()).off('resize.plimgmap').on('resize.plimgmap',function(){
		pagelayer_resize_imgmap(jEle);
	});

	if(!pagelayer_is_live){
		for (const key in data) {
			if(data == undefined ||  data[key].length === 0){
				continue;
			}
			var elem = jEle.find('[data-id='+key+']');
			elem.css({'cursor':'pointer'});
			elem.off('click').on('click',function(){
				window.open(data[key], "_blank");				
			});
		}
		svgEle.addClass('pagelayer-imgmap-hover-active');
	}
}

function pagelayer_resize_imgmap(jEle){
	var svgEle = jEle.find('svg');
	var pathEles = svgEle.find('path');

	if(pathEles.length > 0){
		for (var i = 0; i < pathEles.length; i++) {
			pagelayer_resize_imgmap_item(jQuery(pathEles[i]), jEle);
		}
	}
}

function pagelayer_resize_imgmap_item(path_ele, jEle = ''){

	var wrap = jEle.find('.pagelayer-imgmap-wrapper');
	var img_ele = jEle.find('img');

	// Delay while resizing map
	setTimeout(() => {

		// Auto resize wrapper
		wrap.css('cssText','width:'+img_ele.width()+'px;height:'+img_ele.height()+'px;left:'+img_ele.position().left+'px;top:'+img_ele.position().top+'px;');

		var mapWidth = wrap.width();
		var mapHeight = wrap.height();
		var cords = path_ele.attr('data-cord');
		var isActive = path_ele.hasClass('pagelayer-map-item-active') ? true : false;
		
		cords = cords.split(',');
	
		var tmpArr = [];
		var tmpCord = '';
		var count = 0;
		
		cords.forEach((ele,index) => {
			var prefix = index == 1 ? 'M' : 'L';
			var perc = ele;
	
			if(tmpCord.length == 0){
				tmpCord = (mapWidth / 100) * perc;
			}else{
				var cord = (mapHeight / 100) * perc;
	
				if(pagelayer_is_live){
					var cordWrap = jEle.find('.pagelayer-imgmap-coordinates-wraper');
					var mapId = path_ele.attr('data-id');
					var cord_ele = jEle.find('.pl-cord-'+mapId).eq(count);
					
					if(cord_ele.length == 0){
						var cordHTML = `<div class="pagelayer-imgmap-coord pl-cord-`+mapId+`" title="Drag to reposition" data-id="`+count+`" style="left:`+(tmpCord)+`px;top:`+(cord)+`px" style="display:block;">
							<span class="pagelayer-imgmap-remove-cord fa fa-trash"></span>
						</div>`;
						cordWrap.append(cordHTML);
					}else{
						var visibility = isActive ? 'display:block' : '';
						cord_ele.css('cssText','left:'+tmpCord+'px;top:'+cord+'px;'+visibility);
					}
				}
	
				tmpArr.push(prefix + tmpCord+' '+cord);
	
				tmpCord = '';
				count++;
			}
		});
	
		// Close path denoted by z
		var endOpr = pagelayer_is_live ? '' : 'z';
		var value = tmpArr.join('').toString() + endOpr;
		cords = cords.toString();
		
		path_ele.attr('data-cord',cords);
		path_ele.attr('d',value);

	}, 10);

}

function pagelayer_create_imgmap_svg(classes = [], attrs = []){

	// Create svg path element and append in the svg
	var elem = document.createElementNS('http://www.w3.org/2000/svg', 'path');
	if(classes.length > 0){
		classes.forEach(cls => {
			elem.classList.add(cls);
		});
	}

	if(attrs.length > 0){
		attrs.forEach(attr => {
			elem.setAttribute(attr[0],attr[1]);
		});
	}
	
	return elem;
}