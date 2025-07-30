<?php

//////////////////////////////////////////////////////////////
//===========================================================
// premium.php
//===========================================================
// PAGELAYER
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit Gupta
// Date:       23rd Jan 2017
// Time:       23:00 hrs
// Site:       http://pagelayer.com/wordpress (PAGELAYER)
// ----------------------------------------------------------
// Please Read the Terms of use at http://pagelayer.com/tos
// ----------------------------------------------------------
//===========================================================
// (c)Pagelayer Team
//===========================================================
//////////////////////////////////////////////////////////////

// Are we being accessed directly ?
if(!defined('PAGELAYER_PRO_VERSION')) {
	exit('Hacking Attempt !');
}

global $pagelayer;

// Image Hotspot
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_image_hotspot', array(
		'name' => __pl('Image Hotspot'),
		'group' => 'image',
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'holder' => '.pagelayer-icon-holder',
		'html' => '<div class="pagelayer-image-hotspot-holder">
			<img class="pagelayer-img" src="{{{img-url}}}" title="{{{img-title}}}" alt="{{{img-alt}}}" />
			<div class="pagelayer-icon-holder pagelayer-hotspots-{{common_tip_show}} pagelayer-{{common_tip_theme}}"></div>
		</div>',
		'params' => array(
			'elements' => array(
				'type' => 'group',
				'label' => __pl('hotspots'),
				'sc' => PAGELAYER_SC_PREFIX.'_hotspot',
				'item_label' => array(
					'default' => __pl('hotspot'),
					'param' => 'title',
				),
				'count' => 1,
				'text' => __pl('Add New Hotspot'),
			),
			'img' => array(
				'type' => 'image',
				'label' => __pl('Main Image'),
				'default' => PAGELAYER_URL.'/images/default-image.png',
				'desc' => __pl('Choose an image from Media Library'),
				'css' => ['{{element}} .pagelayer-icon-holder' => 'position:absolute; top:0; left:0; height:100%; width:100%;', '{{element}} .pagelayer-icon-holder .pagelayer-ele-wrap' => 'display: contents', '{{element}} .pagelayer-image-hotspot-holder' => 'position:relative'],
			),
			'icon_anim_hover' => array(
				'type' => 'select',
				'label' => __pl('icon_animation'),
				'list' => [
					'' => __pl('none'),
					'grow' => __pl('Grow'),
					'shrink' => __pl('Shrink'),
					'pulse' => __pl('Pulse'),
					'pulse-grow' => __pl('Pulse Grow'),
					'pulse-shrink' => __pl('Pulse Shrink'),
					'push' => __pl('Push'),
					'pop' => __pl('Pop'),
					'buzz' => __pl('Buzz'),
					'buzz-out' => __pl('Buzz Out'),
					'float' => __pl('Float'),
					'sink' => __pl('Sink'),
					'bob' => __pl('Bob'),
					'hang' => __pl('Hang'),
					'bounce-in' => __pl('Bounce In'),
					'bounce-out' => __pl('Bounce Out'),
					'rotate' => __pl('Rotate'),
					'grow-rotate' => __pl('Grow Rotate'),
					'skew-forward' => __pl('Skew Forward'),
					'skew-backward' => __pl('Skew Backward'),
					'wobble-vertical' => __pl('Wobble Vertical'),
					'wobble-horizontal' => __pl('Wobble Horizontal'),
					'wobble-bottom-to-right' => __pl('Wobble Bottom To Right'),
					'wobble-top-to-right' => __pl('Wobble Top To Right'),
					'wobble-top' => __pl('Wobble Top'),
					'wobble-bottom' => __pl('Wobble Bottom'),
					'wobble-skew' => __pl('Wobble Skew'),
				],
				'addAttr' => ['{{element}} .pagelayer-icon-holder' => 'pagelayer-animation="{{icon_anim_hover}}"'],
			),
			/* 'common_hotspot_color' => array(
				'type' => 'color',
				'label' => __pl('Hotspot Theme'),
				'default' => 'mp-text-color-red',
				'list' => array(
					'mp-text-color-red' => __pl('red'),
					'mp-text-color-dark-grey' => __pl('grey'),
					'mp-text-color-black' => __pl('black'),
					'custom' => __pl('custom'),
				),
			),
			'common_hotspot_custom_color' => array(
				'type' => 'color',
				'label' => __pl('Hotspot Color'),
				'default' => '#e25441',
				'req' => array(
					'common_hotspot_color' => 'custom'
				)
			),
			'common_plus_color' => array(
				'type' => 'color',
				'label' => __pl('Hotspot Icon Color'),
				'default' => '#ffffff',
				'req' => array(
					'common_hotspot_color' => 'custom'
				)
			),
			'common_hotspot_size' => array(
				'type' => 'radio',
				'label' => __pl('Hotspot Size'),
				'default' => 'normal',
				'list' => array(
					'small' => __pl('small'),
					'normal' => __pl('middle'),
					'big' => __pl('large'),
				)
			),*/
			'common_tip_show' => array(
				'type' => 'select',
				'label' => __pl('tooltip_display'),
				'default' => 'hover',
				'list' => array(
					'hover' => __pl('On Hover'),
					'always' => __pl('always'),
					'click' => __pl('On Click'),
				)
			),
		)
	)
);

// Hotspot
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_hotspot', array(
		'name' => __pl('hotspot'),
		'group' => 'image',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_image_hotspot'],
		'innerHTML' => 'tooltip_text',
		'html' => '<div class="pagelayer-hotspots-icon-holder">
			<div if-ext="{{icon_pulse}}" class="pagelayer-image-hotspots-anim">
				<i class="{{icon}}"></i>
				<div if="{{tooltip_text}}" class="pagelayer-tooltip-text pagelayer-tooltip-{{tooltip_position}}">
					{{tooltip_text}}
				</div>
			</div>
		</div>',
		'params' => array(
			'title' => array(
				'type' => 'text',
				'label' => __pl('title'),
				'default' => 'Lorem',
			),
			'align_top' => array(
				'type' => 'slider',
				'label' => __pl('verticle_postion'),
				'default' => 50,
				'min' => 0,
				'max' => 100,
				'css' =>  ['{{element}}' => 'top: {{val}}%'],
			),
			'align_left' => array(
				'type' => 'slider',
				'label' => __pl('horizontal_pos'),
				'default' => 50,
				'min' => 0,
				'max' => 100,
				'css' =>  ['{{element}}' => 'left: {{val}}%'],
			),
		),
		'icon_style' => [
			'icon' => array(
				'type' => 'icon',
				'label' => __pl('icon'),
				'default' => 'fas fa-map-pin',
				'css' => ['{{element}}' => 'position: absolute;transform: translateY(-{{align_top}}%) translateX(-{{align_left}}%);display: inline-block; cursor: pointer;'],
			),
			'icon_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' =>  ['{{element}} .pagelayer-hotspots-icon-holder .fas' => 'color:{{val}}'],
			),
			'icon_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#0986c0',
				'css' =>  ['{{element}} .pagelayer-hotspots-icon-holder .fas' => 'background-color:{{val}}'],
			),
			'icon_pulse' => array(
				'type' => 'checkbox',
				'label' => __pl('pulse'),
				'default' => 'true',
			),
			'icon_pluse_color' => array(
				'type' => 'color',
				'label' => __pl('pulse_color'),
				'default' => '#ffffff',
				'css' =>  ['{{element}} .pagelayer-image-hotspots-anim::before' => 'background-color:{{val}}'],
			),
			'icon_rounded' => array(
				'type' => 'checkbox',
				'label' => __pl('rounded'),
				'default' => 'true',
				'css' =>  ['{{element}} .pagelayer-hotspots-icon-holder .fas' => 'border-radius:100%', '{{element}} .pagelayer-image-hotspots-anim::before ' => 'border-radius:100%'],
			),
			'icon_padding' => array(
				'type' => 'dimension',
				'label' => __pl('padding'),
				'screen' => 1,
				'default' => '15,18',
				'css' =>  ['{{element}} .pagelayer-hotspots-icon-holder .fas' => 'padding:{{val[0]}}px {{val[1]}}px;margin-right:1px'],
			),
		],
		'tooltip_style' => [
			'tooltip_text' => array(
				'type' => 'editor',
				'label' => __pl('text'),
				'default' => 'Lorem ipsum dolor sit amet',
				'edit' => '.pagelayer-tooltip-text',
			),
			'tooltip_position' => array(
				'type' => 'select',
				'label' => __pl('position'),
				'default' => 'top',
				'list' => array(
					'top' => __pl('top'),
					'right' => __pl('right'),
					'bottom' => __pl('bottom'),
					'left' => __pl('left'),
				)
			),
			'common_tip_theme' => array(
				'type' => 'select',
				'label' => __pl('theme'),
				'default' => 'tooltipster-default',
				'list' => array(
					'tooltipster-default' => __pl('dark'),
					/* 'tooltipster-light' => __pl('silver'),
					'tooltipster-noir' => __pl('noir'),
					'tooltipster-shadow' => __pl('shadow'), */
					'custom' => __pl('custom'),
				),
			),
			'common_custom_bg_theme' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#eb002c',
				'req' => array(
					'common_tip_theme' => 'custom'
				),
				'css' => ['{{element}} .pagelayer-tooltip-text' => 'background-color:{{val}}', '{{element}} .pagelayer-tooltip-text:after' => 'border-color:transparent !important ;border-{{tooltip_position}}-color:{{val}} !important']
			),
			'common_custom_font_theme' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'req' => array(
					'common_tip_theme' => 'custom'
				),
				'css' => ['{{element}} .pagelayer-tooltip-text' => 'color:{{val}}']
			),
		],
		'styles' => [
			'icon_style' => __pl('icon'),
			'tooltip_style' => __pl('tooltip')
		]
	)
);

// Audio
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_audio', array(
		'name' => __pl('audio'),
		'group' => 'media',
		'html' => '<div class="pagelayer-audio-container">
			<audio controls>
				<source src="{{{src-url}}}"></source>
			</audio>
		</div>',
		'params' => array(
			'src' => array(
				'type' => 'audio',
				'label' => __pl('wp_audio_source_title'),
				'desc' => __pl('wp_audio_source_desc'),
				'default' => 'http://wpcom.files.wordpress.com/2007/01/mattmullenweg-interview.mp3',
			),
			'autoplay' => array(
				'type' => 'checkbox',
				'label' => __pl('wp_audio_autoplay_title'),
				'desc' => __pl('wp_audio_autoplay_desc'),
				'default' => '',
				'addAttr' => ['{{element}} audio' => 'autoplay="autoplay"'],
			),
			'loop' => array(
				'type' => 'checkbox',
				'label' => __pl('wp_audio_loop_title'),
				'desc' => __pl('wp_audio_loop_desc'),
				'default' => '',
				'addAttr' => ['{{element}} audio' => 'loop="loop"'],
			),
			'width' => array(
				'type' => 'slider',
				'label' => __pl('shape_width'),
				'units' => ['%', 'px'],
				'min' => 0,
				'max' => 100,
				'screen' => 1,
				'css' => 'width: {{val}} !important',
			),
			'padding' => array(
				'type' => 'dimension',
				'label' => __pl('padding'),
				'default' => '10,10',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-audio-container' => 'padding-top: {{val[0]}}px; padding-bottom: {{val[0]}}px; padding-left: {{val[1]}}px; padding-right: {{val[1]}}px;'],
			),
		),
		'playpause_style' => [
			'playpause_size' => array(
				'type' => 'spinner',
				'label' => __pl('size'),
				'default' => 12,
				'min' => 0,
				'max' => 200,
				'screen' => 1,
				'css' => ['{{element}} .mejs-playpause-button button' => 'font-size:{{val}}px;']
			),
			'playpause_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}} .mejs-playpause-button button:after' => 'color:{{val}}']
			),
			'playpause_space' => array(
				'type' => 'spinner',
				'label' => __pl('space_around'),
				'default' => 5,
				'min' => -100,
				'max' => 100,
				'screen' => 1,
				'css' => ['{{element}} .mejs-playpause-button button' => 'padding:calc( 1em + {{val}}px ) !important;']
			),
			'playpause_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#E5E5E5',
				'css' => ['{{element}} .mejs-playpause-button button' => 'background-color:{{val}} !important;']
			),
			'playpause_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'default' => 50,
				'min' => 0,
				'max' => 50,
				'screen' => 1,
				'css' => ['{{element}} .mejs-playpause-button button' => 'border-radius: {{val}}%;']
			),
		],
		'duration_style' => [
			'show_duration' => array(
				'type' => 'checkbox',
				'label' => __pl('show'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-audio-container' => 'show_duration="{{show_duration}}"'],
			),
			'duration_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}} .mejs-duration' => 'color:{{val}} !important;']
			),
		],
		'progress_style' => [
			'show_progress' => array(
				'type' => 'checkbox',
				'label' => __pl('show'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-audio-container' => 'show_progress="{{show_progress}}"'],
			),
			'progress_height' => array(
				'type' => 'slider',
				'label' => __pl('progress_height'),
				'units' => ['px', '%'],
				'min' => 0,
				'max' => 100,
				'screen' => 1,
				'default' => 10,
				'css' => [
					'{{element}} .mejs-time-total' => 'height:{{val}} !important;',
				],
			),
			'progress_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => 'rgba(0,0,0,0.1)',
				'css' => ['{{element}} .mejs-controls .mejs-time-rail .mejs-time-total' => 'background:{{val}} !important;']
			),
			'progress_loaded' => array(
				'type' => 'color',
				'label' => __pl('loaded_color'),
				'default' => '#777777',
				'css' => ['{{element}} .mejs-time-loaded' => 'background:{{val}} !important;']
			),
			'progress_current' => array(
				'type' => 'color',
				'label' => __pl('current_color'),
				'default' => '#0986c0',
				'css' => ['{{element}} .mejs-time-current' => 'background:{{val}} !important;']
			),
			'progress_hovered' => array(
				'type' => 'color',
				'label' => __pl('hovered_color'),
				'default' => '#b5d2f9',
				'css' => ['{{element}} .mejs-time-hovered' => 'background:{{val}} !important;']
			),
			'progress_handle' => array(
				'type' => 'color',
				'label' => __pl('handle_color'),
				'default' => '#000000',
				'css' => [
					'{{element}} .mejs-time-handle' => 'background:{{val}} !important;',
					'{{element}} .mejs-time-handle-content' => 'background:{{val}} !important;',
				]
			),
		],
		'current_style' => [
			'show_current' => array(
				'type' => 'checkbox',
				'label' => __pl('show'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-audio-container' => 'show_current="{{show_current}}"'],
			),
			'current_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}} .mejs-currenttime' => 'color:{{val}} !important;']
			),
		],
		'volume_style' => [
			'show_volume' => array(
				'type' => 'checkbox',
				'label' => __pl('show'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-audio-container' => 'show_volume="{{show_volume}}"'],
			),
			'volume_size' => array(
				'type' => 'spinner',
				'label' => __pl('size'),
				'default' => 12,
				'min' => 0,
				'max' => 200,
				'screen' => 1,
				'css' => ['{{element}} .mejs-volume-button button' => 'font-size:{{val}}px;']
			),
			'volume_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}} .mejs-volume-button button:after' => 'color:{{val}}']
			),
			'volume_space' => array(
				'type' => 'spinner',
				'label' => __pl('space_around'),
				'default' => 5,
				'min' => -100,
				'max' => 100,
				'screen' => 1,
				'css' => ['{{element}} .mejs-volume-button button' => 'padding:calc( 1em + {{val}}px ) !important;']
			),
			'volume_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#E5E5E5',
				'css' => ['{{element}} .mejs-volume-button button' => 'background-color:{{val}} !important;']
			),
			'volume_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'default' => 50,
				'min' => 0,
				'max' => 50,
				'screen' => 1,
				'css' => ['{{element}} .mejs-volume-button button' => 'border-radius: {{val}}%;']
			),
		],
		'volume_slider' => [
			'volume_height' => array(
				'type' => 'slider',
				'label' => __pl('progress_height'),
				'units' => ['px', '%'],
				'min' => 0,
				'max' => 100,
				'screen' => 1,
				'default' => 10,
				'css' => [
					'{{element}} .mejs-horizontal-volume-total' => 'height:{{val}} !important;'
				],
			),
			'volume_width' => array(
				'type' => 'slider',
				'label' => __pl('shape_width'),
				'min' => 0,
				'max' => 100,
				'default' => 35,
				'screen' => 1,
				'css' => [
					'{{element}} .mejs-horizontal-volume-total' => 'width:{{val}}px !important;',
					'{{element}} .mejs-horizontal-volume-slider' => 'width: calc( {{val}}px + 10px ) !important;',
				],
			),
			'vslides_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => 'rgba(0,0,0,0.1)',
				'css' => ['{{element}} .mejs-horizontal-volume-total' => 'background:{{val}} !important;']
			),
			'vslider_current' => array(
				'type' => 'color',
				'label' => __pl('current_color'),
				'default' => '#777777',
				'css' => ['{{element}} .mejs-horizontal-volume-current' => 'background:{{val}} !important;']
			),
		],
		'styles' => [
			'playpause_style' => __pl('playpause_style'),
			'duration_style' => __pl('duration_style'),
			'progress_style' => __pl('progress_style'),
			'current_style' => __pl('current_style'),
			'volume_style' => __pl('volume_style'),
			'volume_slider' => __pl('volume_slider'),
		]
	)
);

// Video Slider
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_video_slider', array(
		'name' => __pl('video_slider'),
		'group' => 'media',
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'prevent_inside' => ['pl_slides'],
		'holder' => '.pagelayer-video-slider-holder',
		'child_selector' => '>.pagelayer-owl-stage-outer>.pagelayer-owl-stage>.pagelayer-owl-item', // Make it very specifc
		'html' => '<div class="pagelayer-video-slider-holder pagelayer-owl-holder pagelayer-owl-carousel pagelayer-owl-theme"></div>',
		'params' => array(
			'elements' => array(
				'type' => 'group',
				'label' => __pl('Videos'),
				'sc' => PAGELAYER_SC_PREFIX.'_video',
				'item_label' => array(
					'default' => __pl('video'),
					'param' => 'video_type'
				),
				'count' => 2,
				'text' => __pl('add_media'),
			),
		),
		'slider_options' => $pagelayer->slider_options,
		'arrow_styles' => $pagelayer->slider_arrow_styles,
		'pager_styles' => $pagelayer->slider_pager_styles,
		'styles' => [
			'slider_options' => __pl('slider_options'),
			'arrow_styles' => __pl('arrow_styles'),
			'pager_styles' => __pl('pager_styles'),
		],
	)
);

// Download Button
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_download', array(
		'name' => __pl('Download Button'),
		'group' => 'button',
		'html' => '<a href="{{{attachment-url}}}" target="_blank" class="pagelayer-btn-holder pagelayer-ele-link {{type}} {{size}} {{icon_position}}" download="">
					<i class="{{icon}} pagelayer-btn-icon"></i>
					<span class="pagelayer-btn-text">{{text}}</span>
					<i class="{{icon}} pagelayer-btn-icon"></i>
				</a>',
		'params' => array(
			'text' => array(
				'type' => 'text',
				'label' => __pl('button_text_label'),
				'default' => 'Download',
				'edit' => '.pagelayer-btn-text'
			),
			'attachment' => array(
				'type' => 'media',
				'label' => __pl('media_file'),
				'desc' => __pl('media_description'),
				'default' => '',
			),
			'file_name' => array(
				'type' => 'text',
				'label' => __pl('Download File Name'),
				'addAttr' => ['{{element}} a.pagelayer-btn-holder' => 'download="{{file_name}}"']
			),
			'full_width' => array(
				'type' => 'checkbox',
				'label' => __pl('stretch'),
				'screen' => 1,
				'css' => ['{{element}} a' => 'width: 100%; text-align: center;']
			),
			'align' => array(
				'type' => 'radio',
				'label' => __pl('obj_align_label'),
				'default' => 'left',
				'screen' => 1,
				'css' => 'text-align: {{val}}',
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				),
				'req' => array(
					'full_width' => ''
				)
			),
			'btn_typo' => array(
				'type' => 'typography',
				'label' => __pl('quote_content_typo'),
				'css' => [
					'{{element}} .pagelayer-btn-text' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
					'{{element}} .pagelayer-btn-holder' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				],
			),
		),
		'btn_style' => [
			'type' => array(
				'type' => 'select',
				'label' => __pl('button_type_label'),
				'default' => 'pagelayer-btn-default',
				'list' => array(
					'pagelayer-btn-default' => __pl('btn_type_default'),
					'pagelayer-btn-primary' => __pl('btn_type_primary'),
					'pagelayer-btn-secondary' => __pl('btn_type_secondary'),
					'pagelayer-btn-success' => __pl('btn_type_success'),
					'pagelayer-btn-info' => __pl('btn_type_info'),
					'pagelayer-btn-warning' => __pl('btn_type_warning'),
					'pagelayer-btn-danger' => __pl('btn_type_danger'),
					'pagelayer-btn-dark' => __pl('btn_type_dark'),
					'pagelayer-btn-light' => __pl('btn_type_light'),
					'pagelayer-btn-link' => __pl('btn_type_link'),
					'pagelayer-btn-custom' => __pl('btn_type_custom')
				),
			),
			'size' => array(
				'type' => 'select',
				'label' => __pl('button_size_label'),
				'default' => 'pagelayer-btn-large',
				'list' => array(
					'pagelayer-btn-mini' => __pl('mini'),
					'pagelayer-btn-small' => __pl('small'),
					'pagelayer-btn-large' => __pl('large'),
					'pagelayer-btn-extra-large' => __pl('extra_large'),
					'pagelayer-btn-double-large' => __pl('double_large'),
					'pagelayer-btn-custom' => __pl('custom'),
				)
			),
			'btn_custom_size' => array(
				'type' => 'spinner',
				'label' => __pl('btn_custom_size'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'padding: {{val}}px calc({{val}}px *2)'],
				'req' => array(
					'size' => 'pagelayer-btn-custom'
				),
			),
			'btn_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_label'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'background-color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_color' => array(
				'type' => 'color',
				'label' => __pl('btn_color_label'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('btn_hover_delay_label'),
				'desc' => __pl('btn_hover_delay_desc'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-btn-holder' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'background-color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
		],
		'icon_style' => [
			'icon' => array(
				'type' => 'icon',
				'label' => __pl('service_box_font_icon_label'),
				'default' => 'fas fa-download',
			),
			'icon_position' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'pagelayer-btn-icon-left',
				'list' => array(
					'pagelayer-btn-icon-left' => __pl('left'),
					'pagelayer-btn-icon-right' => __pl('right')
				),
				'req' => array(
					'!icon' => ''
				)
			),
			'icon_spacing' => array(
				'type' => 'slider',
				'label' => __pl('icon_spacing'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-icon' => 'padding: 0 {{val}}px;'],
				'req' => array(
					'!icon' => ''
				),
			),
		],
		'border_style' => [
			'btn_bor_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),	
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type' => ''
				],
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color_hover_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width_hover'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type_hover' => ''
				],
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius_hover'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
		],
		'styles' => [
			'btn_style' => __pl('btn_style'),
			'icon_style' => __pl('icon_style'),
			'border_style' => __pl('border_style'),
		],
	)
);

// Table
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_table', array(
		'name' => __pl('table'),
		'group' => 'other',
		'no_gt' => 1,
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'holder' => '.pagelayer-data-holder',
		'html' => '<div class="pagelayer-data-holder"></div>
		<table class="pagelayer-table-holder"></table>',
		'params' => array(
			'elements' => array(
				'type' => 'group',
				'label' => __pl('table_row'),
				'sc' => PAGELAYER_SC_PREFIX.'_table_row',
				'item_label' => array(
					'default' => __pl('table_row'),
					//'param' => 'title',
				),
				'count' => 3,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('table_row')))
			),
			'table_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'screen' => 1,
				'units' => ['%', 'px'],
				'min' => 0,
				'css' => ['{{element}} .pagelayer-table-holder' => 'width:{{val}}'],
			),
			'table_height' => array(
				'type' => 'slider',
				'label' => __pl('height'),
				'units' => ['px', 'em', '%'],
				'screen' => 1,
				'min' => 0,
				'css' => ['{{element}} .pagelayer-table-holder' => 'height:{{val}}'],
			),
			'table_td_padding' => [
				'type' => 'padding',
				'label' => __pl('table_td_padding'),
				'screen' => 1,
				'units' => ['px', 'em'],
				'css' => ['{{element}} td, {{element}} th' => 'padding-top: {{val[0]}}; padding-right: {{val[1]}}; padding-bottom: {{val[2]}}; padding-left: {{val[3]}}'],
			],
		),
		'table_style' => array(
			'table_position' => array(
				'type' => 'select',
				'label' => __pl('table_position'),
				'css' => ['{{element}} .pagelayer-table-holder' => '{{val}}:auto'],
				'list' => array(
					'margin-right' => __pl('left'),
					'margin' => __pl('center'),
					'margin-left' => __pl('right'),
				),
			),
			'table_content_align' => array(
				'type' => 'select',
				'label' => __pl('content_align'),
				'css' => ['{{element}} .pagelayer-table-holder' => 'text-align:{{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
			),
			'table_verti_align' => array(
				'type' => 'select',
				'label' => __pl('vertical_align'),
				'css' => ['{{element}} .pagelayer-table-holder *' => 'vertical-align:{{val}}'],
				'list' => array(
					'' => __pl('none'),
					'top' => __pl('top'),
					'middle' => __pl('middle'),
					'bottom' => __pl('bottom'),
				),
			),
			'even_odd_row' => array(
				'type' => 'radio',
				'label' => __pl('colors'),
				'default' => 'odd_row',
				'list' => array(
					'odd_row' => __pl('odd_row'),
					'even_row' => __pl('even_row'),
					'hover' => __pl('hover'),
				),
			),
			'odd_row_text_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} tr:nth-child(odd) td, {{element}} tr:nth-child(odd) th' => 'color:{{val}}'],
				'show' => ['even_odd_row' => 'odd_row'],
			),
			'odd_row_color' => array(
				'type' => 'color',
				'label' => __pl('odd_row_bg'),
				'default' => '#ffffff',
				'css' => ['{{element}} tr:nth-child(odd) td, {{element}} tr:nth-child(odd) th' => 'background-color:{{val}}'],
				'show' => ['even_odd_row' => 'odd_row'],
			),
			'even_row_text_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} tr:nth-child(even) td, {{element}} tr:nth-child(even) th' => 'color:{{val}}'],
				'show' => ['even_odd_row' => 'even_row'],
			),
			'even_row_color' => array(
				'type' => 'color',
				'label' => __pl('even_row_bg'),
				'default' => '#e3e3e3',
				'css' => ['{{element}}  tr:nth-child(even) td, {{element}}  tr:nth-child(even) th' => 'background-color:{{val}}'],
				'show' => ['even_odd_row' => 'even_row'],
			),
			'tr_hover_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}}  tr:hover td, {{element}}  tr:hover th' => 'color:{{val}} !important'],
				'show' => ['even_odd_row' => 'hover'],
			),
			'tr_hover_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#e3e3e3',
				'css' => ['{{element}} tr:hover td, {{element}} tr:hover th' => 'background-color:{{val}} !important'],
				'show' => ['even_odd_row' => 'hover'],
			),
			'table_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .pagelayer-table-holder' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				],
			),
			'table_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} table' =>'border-style: {{val}};',
					'{{element}} th' =>'border-style: {{val}};',
					'{{element}} td' =>'border-style: {{val}};'
				],
				'default' => 'solid',
				'list' => [
					'none' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'table_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#42414f',
				'css' => ['{{element}} table' =>'border-color: {{val}};',
					'{{element}} th' =>'border-color: {{val}};',
					'{{element}} td' =>'border-color: {{val}};'
				],
				'req' => ['!table_border_type' => 'none']
			),
			'table_border_width' => array(
				'type' => 'spinner',
				'label' => __pl('border_width'),
				'default' => 1,
				'screen' => 1,
				'css' => ['{{element}} table' =>'border-width: {{val}}px;',
					'{{element}} th' =>'border-width: {{val}}px;',
					'{{element}} td' =>'border-width: {{val}}px;'
				],
				'req' => ['!table_border_type' => 'none']
			),
		),
		'styles' => array(
			'table_style' => __pl('table_style'),
		),
	)
);

// Table row
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_table_row', array(
		'name' => __pl('table_row'),
		'group' => 'other',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_table'],
		'has_group' => [
			'section' => 'params', 
			'prop' => 'col_elements'
		],
		'holder' => '.pagelayer-table-row-holder',
		'html' => '<div class="pagelayer-table-row-holder"></div>',
		'params' => array(
			'col_elements' => array(
				'type' => 'group',
				'label' => __pl('table_cell'),
				'sc' => PAGELAYER_SC_PREFIX.'_table_col',
				'item_label' => array(
					'default' => __pl('table_cell'),
					'param' => 'title',
				),
				'count' => 3,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('table_cell')))
			),
			'tr_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),				
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'tr_color' => array(
				'type' => 'color',
				'label' => __pl('color'),				
				'css' => ['.pagelayer-table [pagelayer-table-id="{{ele_id}}"] td, .pagelayer-table [pagelayer-table-id="{{ele_id}}"] th' => 'color:{{val}} !important'],
				'show' => ['tr_colors' => ''],
			),
			'tr_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.pagelayer-table [pagelayer-table-id="{{ele_id}}"] td, .pagelayer-table [pagelayer-table-id="{{ele_id}}"] th' => 'background-color:{{val}} !important'],
				'show' => ['tr_colors' => ''],
			),
			'tr_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),				
				'css' => ['.pagelayer-table [pagelayer-table-id="{{ele_id}}"]:hover td, .pagelayer-table [pagelayer-table-id="{{ele_id}}"]:hover th' => 'color:{{val}} !important'],
				'show' => ['tr_colors' => 'hover'],
			),
			'tr_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.pagelayer-table [pagelayer-table-id="{{ele_id}}"]:hover td, .pagelayer-table [pagelayer-table-id="{{ele_id}}"]:hover th' => 'background-color:{{val}} !important'],
				'show' => ['tr_colors' => 'hover'],
			),
			'tr_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'.pagelayer-table [pagelayer-table-id="{{ele_id}}"]' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				],
			),
			'tr_td_padding' => [
				'type' => 'padding',
				'label' => __pl('row_data_padding'),
				'screen' => 1,
				'units' => ['px', 'em'],
				'css' => ['.pagelayer-table [pagelayer-table-id="{{ele_id}}"] td, .pagelayer-table [pagelayer-table-id="{{ele_id}}"] th' => 'padding-top: {{val[0]}}; padding-right: {{val[1]}}; padding-bottom: {{val[2]}}; padding-left: {{val[3]}}'],
			],
		)
	)
);

// Table col
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_table_col', array(
		'name' => __pl('table_cell'),
		'group' => 'other',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_table_row'],
		'innerHTML' => 'data',
		'html' => '<div class="pagelayer-col-data"></div>',
		'params' => array(
			'data' => array(
				'type' => 'textarea',
				'label' => __pl('data'),
				'default' => 'Lorem ipsum',
				'addAttr' => ['{{element}} .pagelayer-col-data' => 'data-td="{{data}}"'],
			),
			'td_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'screen' => 1,
				'units' => ['%', 'px'],
				'css' => ['.pagelayer-table tr [pagelayer-table-id="{{ele_id}}"]' => 'width:{{val}}'],
			),
			'td_tag' => array(
				'type' => 'select',
				'label' => __pl('cell_type'),
				'default' => 'td',
				'list' => array(
					'td' => __pl('standard_cell'),
					'th' => __pl('header_cell'),
				),
				'addAttr' => ['{{element}} .pagelayer-col-data' => 'data-tag="{{td_tag}}"'],
			),
			'td_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),				
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'td_color' => array(
				'type' => 'color',
				'label' => __pl('color'),				
				'css' => ['.pagelayer-table tr [pagelayer-table-id="{{ele_id}}"]' => 'color:{{val}} !important'],
				'show' => ['td_colors' => ''],
			),
			'td_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.pagelayer-table tr [pagelayer-table-id="{{ele_id}}"]' => 'background-color:{{val}} !important'],
				'show' => ['td_colors' => ''],
			),
			'td_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),				
				'css' => ['.pagelayer-table tr [pagelayer-table-id="{{ele_id}}"]:hover' => 'color:{{val}} !important'],
				'show' => ['td_colors' => 'hover'],
			),
			'td_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.pagelayer-table tr [pagelayer-table-id="{{ele_id}}"]:hover' => 'background-color:{{val}} !important'],
				'show' => ['td_colors' => 'hover'],
			),
			'td_colspan' => array(
				'type' => 'spinner',
				'label' => __pl('colspan'),
				'min' => 1,
				'addAttr' => ['{{element}} .pagelayer-col-data' => 'data-colspan="{{td_colspan}}"'],
			),
			'td_rowspan' => array(
				'type' => 'spinner',
				'label' => __pl('rowspan'),
				'min' => 1,
				'addAttr' => ['{{element}} .pagelayer-col-data' => 'data-rowspan="{{td_rowspan}}"'],
			),
		),
	)
);

// Call To Action
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_call', array(
		'name' => __pl('cta'),
		'group' => 'other',
		'innerHTML' => 'content',
		'html' => '<div if={{show_ribbon}} class="pagelayer-cta-ribbon pagelayer-cta-ribbon-{{ribbon_pos}}"><div class="pagelayer-cta-ribbon-text">{{ribbon_text}}</div></div>
				<div class="pagelayer-cta-img-holder">
					<div if="{{cta_image}}" class="pagelayer-cta-image" style="background-image: url(\'{{{cta_image-url}}}\');">
					</div>
				</div>
				<div class="pagelayer-cta-content-holder">
					<div class="pagelayer-cta-content">
						<div if="{{heading}}" class="pagelayer-cta-heading">{{heading}}</div>
						<div if="{{subheading}}" class="pagelayer-cta-subheading">{{subheading}}</div>
						<div if="{{content}}" class="pagelayer-cta-text">{{content}}</div>
						<a if={{button_text}} class="pagelayer-btn-holder pagelayer-ele-link {{btn_type}} {{btn_size}}" href="{{{button_link}}}">{{button_text}}</a>
					</div>
				</div>',
		'params' => array(
			'layout' => array(
				'type' => 'select',
				'label' => __pl('cta_layout_label'),
				'default' => 'normal',
				'addClass' => 'pagelayer-cta-layout-{{val}}',
				'list' => array(
					'normal' => __pl('normal'),
					'overlay' => __pl('overlay')
				)
			),
			'align' => array(
				'type' => 'radio',
				'label' => __pl('cta_align_label'),
				'default' => 'left',
				'addClass' => 'pagelayer-cta-align-{{val}}',
				'list' => array(
					'left' => __pl('left'),
					'' => __pl('center'),
					'right' => __pl('right'),
				),
				'req' => array(
					'layout' => 'normal'
				)
			),
		),
		'image_style' => [
			'cta_image' => array(
				'type' => 'image',
				'label' => __pl('cta_image_label'),
				'default' => PAGELAYER_URL.'/images/default-image.png'
			),
			'height' => array(
				'type' => 'slider',
				'label' => __pl('cta_img_height_label'),
				'css' => ['{{element}} .pagelayer-cta-image' => 'min-height: {{val}}px;'],
				'screen' => 1,
				'default' => 300,
				'min' => 0,
				'max' => 1000,
				'step' => 1,
			),
			'width' => array(
				'type' => 'slider',
				'label' => __pl('cta_img_width_label'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-cta-img-holder' => 'flex-basis: {{val}}%;',
					'{{element}} .pagelayer-cta-content-holder' => 'flex-basis: calc(100% - {{val}}%);'],
				'default' => 50,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'req' => array(
					'layout' => 'normal',
					'!align' => ''
				)
			),
			'img_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),
			'img_overlay' => array(
				'type' => 'color',
				'label' => __pl('overlay'),
				'css' => ['{{element}} .pagelayer-cta-image:before' => 'background-color: {{val}}'],
				'show' => ['img_hover' => '']
			),
			'img_opacity' => array(
				'type' => 'slider',
				'label' => __pl('opacity'),
				'css' => ['{{element}} .pagelayer-cta-image:before' => 'opacity: {{val}};'],
				'min' => 0,
				'max' => 1,
				'step' => 0.1,
				'show' => ['img_hover' => '']
			),
			'hov_delay' => array(
				'type' => 'slider',
				'label' => __pl('delay'),
				'css' => ['{{element}} .pagelayer-cta-image:before, {{element}} .pagelayer-cta-image' => 'transition: all {{val}}ms;'],
				'default' => 800,
				'min' => 200,
				'max' => 10000,
				'step' => 100,
				'show' => ['img_hover' => 'hover']
			),
			'img_overlay_hover' => array(
				'type' => 'color',
				'label' => __pl('overlay'),
				'default' => '#333333',
				//'css' => ['{{element}} .pagelayer-cta-img-overlay' => 'background-color: {{val}}'],
				'css' => ['{{element}}:hover .pagelayer-cta-image:before' => 'background-color: {{val}}'],
				'show' => ['img_hover' => 'hover']
			),
			'img_opacity_hover' => array(
				'type' => 'slider',
				'label' => __pl('opacity'),
				'css' => ['{{element}}:hover .pagelayer-cta-image:before' => 'opacity: {{val}};'],
				'default' => 0.3,
				'min' => 0,
				'max' => 1,
				'step' => 0.1,
				'show' => ['img_hover' => 'hover']
			),
			'hover_anim' => array(
				'type' => 'select',
				'label' => __pl('cta_hover_anim_label'),
				'default' => 'pagelayer-cta-zoomin',
				'addClass' => '{{val}}',
				'list' => array(
					'' => __pl('cta_anim_none'),
					'pagelayer-cta-zoomin' => __pl('cta_anim_zoomin'),
					'pagelayer-cta-zoomout' => __pl('cta_anim_zoomout'),
					'pagelayer-cta-moveup' => __pl('cta_anim_moveup'),
					'pagelayer-cta-movedown' => __pl('cta_anim_movedown'),
					'pagelayer-cta-moveleft' => __pl('cta_anim_moveleft'),
					'pagelayer-cta-moveright' => __pl('cta_anim_moveright'),
				),
				'show' => ['img_hover' => 'hover']
			),
		],
		'heading_styles' => [
			'heading' => array(
				'type' => 'text',
				'label' => __pl('heading_name'),
				'default' => 'Time for action',
				'edit' => '.pagelayer-cta-heading',
			),
			'heading_hover' => array(
				'type' => 'radio',
				'label' => __pl(''),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),
			'heading_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-cta-heading' => 'color: {{val}}'],
				'show' => ['heading_hover' => '']
			),
			'heading_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'default' => 'Advent Pro,40,,700,,,solid,1.7,,,',
				'css' => ['{{element}} .pagelayer-cta-heading' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['heading_hover' => '']
			),
			'heading_shadow' => array(
				'type' => 'shadow',
				'label' => __pl('heading_shadow'),
				'css' => ['{{element}} .pagelayer-cta-heading' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}};'],
				'show' => ['heading_hover' => '']
			),
			'heading_delay' => array(
				'type' => 'slider',
				'label' => __pl('delay'),
				'css' => ['{{element}} .pagelayer-cta-heading' => 'transition: all {{val}}ms;'],
				'default' => 800,
				'min' => 200,
				'max' => 3000,
				'step' => 100,
				'show' => ['heading_hover' => 'hover']
			),
			'heading_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}}:hover .pagelayer-cta-heading' => 'color: {{val}}'],
				'show' => ['heading_hover' => 'hover']
			),
			'heading_typo_hover' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['{{element}}:hover .pagelayer-cta-heading' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['heading_hover' => 'hover']
			),
			'heading_shadow_hover' => array(
				'type' => 'shadow',
				'label' => __pl('heading_shadow'),
				'css' => ['{{element}}:hover .pagelayer-cta-heading' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}};'],
				'show' => ['heading_hover' => 'hover']
			),
		],
		'subheading_styles' => [
			'subheading' => array(
				'type' => 'text',
				'label' => __pl('subheading_name'),
				'edit' => '.pagelayer-cta-subheading',
			),
			'subheading_hover' => array(
				'type' => 'radio',
				'label' => __pl(''),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),
			'subheading_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#555555',
				'css' => ['{{element}} .pagelayer-cta-subheading' => 'color: {{val}}'],
				'show' => ['subheading_hover' => '']
			),
			'subheading_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['{{element}} .pagelayer-cta-subheading' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['subheading_hover' => '']
			),
			'subheading_shadow' => array(
				'type' => 'shadow',
				'label' => __pl('heading_shadow'),
				'css' => ['{{element}} .pagelayer-cta-subheading' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}};'],
				'show' => ['subheading_hover' => '']
			),
			'subheading_delay' => array(
				'type' => 'slider',
				'label' => __pl('delay'),
				'css' => ['{{element}} .pagelayer-cta-subheading' => 'transition: all {{val}}ms;'],
				'default' => 800,
				'min' => 200,
				'max' => 3000,
				'step' => 100,
				'show' => ['subheading_hover' => 'hover']
			),
			'subheading_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}}:hover .pagelayer-cta-subheading' => 'color: {{val}}'],
				'show' => ['subheading_hover' => 'hover']
			),
			'subheading_typo_hover' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['{{element}}:hover .pagelayer-cta-subheading' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['subheading_hover' => 'hover']
			),
			'subheading_shadow_hover' => array(
				'type' => 'shadow',
				'label' => __pl('heading_shadow'),
				'css' => ['{{element}}:hover .pagelayer-cta-subheading' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}};'],
				'show' => ['subheading_hover' => 'hover']
			),
		],
		'text_style' => [
			'content' => array(
				'type' => 'editor',
				'label' => __pl('text'),
				'default' => '<p><span style="color: rgb(255, 255, 255);">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor. consectetur adipiscing elit, sed do eiusmod tempor</span></p>',
				'edit' => '.pagelayer-cta-text',
			),
		],
		'content_styles' => [
			'content_spacing' => [
				'type' => 'padding',
				'label' => __pl('cta_content_spacing_label'),
				'default' => '25,25,25,25',
				'screen' => 1,
				//'units' => ['px', 'em', '%'],
				'css' => ['{{element}} .pagelayer-cta-content' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px'],
			],
			'content-align' => array(
				'type' => 'radio',
				'label' => __pl('cta_content_align_label'),
				'default' => 'left',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-cta-content-holder' => 'text-align: {{val}};'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				)
			),
			'content_valign' => array(
				'type' => 'radio',
				'label' => __pl('cta_valign_label'),
				'default' => 'flex-start',
				'screen' => 1,
				'addClass' => 'pagelayer-cta-align-{{val}}',
				'css' => ['{{element}} .pagelayer-cta-content-holder' => 'align-items: {{val}};'],
				'list' => array(
					'flex-start' => __pl('top'),
					'center' => __pl('center'),
					'flex-end' => __pl('bottom'),
				)
			),
			'content_hover' => array(
				'type' => 'radio',
				'label' => __pl(''),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),
			'content_bg' => array(
				'type' => 'color',
				'label' => __pl('cta_content_bg_label'),
				'default' => '#4B6270',
				'css' => ['{{element}} .pagelayer-cta-content-holder' => 'background-color: {{val}}'],
				'show' => ['content_hover' => ''],
				'req' => array(
					'layout' => 'normal'
				)
			),
			'content_delay' => array(
				'type' => 'slider',
				'label' => __pl('delay'),
				'css' => ['{{element}} .pagelayer-cta-content-holder' => 'transition: all {{val}}ms;'],
				'default' => 800,
				'min' => 200,
				'max' => 3000,
				'step' => 100,
				'show' => ['content_hover' => 'hover'],
			),
			'content_bg_hover' => array(
				'type' => 'color',
				'label' => __pl('cta_content_bg_label'),
				'default' => '#E88987',
				'css' => ['{{element}}:hover .pagelayer-cta-content-holder' => 'background-color: {{val}}'],
				'show' => ['content_hover' => 'hover'],
				'req' => array(
					'layout' => 'normal'
				)
			),
		],
		'button_style' => [
			'button_text' => array(
				'type' => 'text',
				'label' => __pl('text'),
				'default' => __pl('button_name'),
				'edit' => '.pagelayer-btn-holder',
			),
			'button_link' => array(
				'type' => 'link',
				'label' => __pl('link_settings'),
				'selector' => '.pagelayer-ele-link',
				'desc' => __pl('button_link_desc'),
			),
			'btn_type' => array(
				'type' => 'select',
				'label' => __pl('button_type_label'),
				'default' => 'pagelayer-btn-success',
				'list' => array(
					'pagelayer-btn-default' => __pl('btn_type_default'),
					'pagelayer-btn-primary' => __pl('btn_type_primary'),
					'pagelayer-btn-secondary' => __pl('btn_type_secondary'),
					'pagelayer-btn-success' => __pl('btn_type_success'),
					'pagelayer-btn-info' => __pl('btn_type_info'),
					'pagelayer-btn-warning' => __pl('btn_type_warning'),
					'pagelayer-btn-danger' => __pl('btn_type_danger'),
					'pagelayer-btn-dark' => __pl('btn_type_dark'),
					'pagelayer-btn-light' => __pl('btn_type_light'),
					'pagelayer-btn-link' => __pl('btn_type_link'),
					'pagelayer-btn-custom' => __pl('btn_type_custom'),
				),
			),
			'btn_size' => array(
				'type' => 'select',
				'label' => __pl('button_size_label'),
				'default' => 'pagelayer-btn-mini',
				'list' => array(
					'pagelayer-btn-mini' => __pl('mini'),
					'pagelayer-btn-small' => __pl('small'),
					'pagelayer-btn-large' => __pl('large'),
					'pagelayer-btn-extra-large' => __pl('extra_large'),
					'pagelayer-btn-double-large' => __pl('double_large'),
					'pagelayer-btn-custom' => __pl('custom'),
				)
			),
			'btn_custom_size' => array(
				'type' => 'dimension',
				'label' => __pl('btn_custom_size'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'padding: {{val[0]}}px {{val[1]}}px;'],
				'req' => array(
					'btn_size' => 'pagelayer-btn-custom'
				),
			),
			'btn_spacing' => array(
				'type' => 'slider',
				'label' => __pl('cta_btn_spacing_label'),
				'css' => ['{{element}} .pagelayer-btn-holder' => 'margin-top: {{val}}px;'],
				'default' => 5,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'screen' => 1,
			),
			'btn_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_label'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'background-color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_color' => array(
				'type' => 'color',
				'label' => __pl('btn_color_label'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('btn_hover_delay_label'),
				'desc' => __pl('btn_hover_delay_desc'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-btn-holder' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_hover_label'),
				'default' => '',
				'css' => ['{{element}}:hover .pagelayer-btn-holder' => 'background-color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_color_hover_label'),
				'default' => '',
				'css' => ['{{element}}:hover .pagelayer-btn-holder' => 'color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type' => ''
				],
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'default' => '40,40,40,40',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}}:hover .pagelayer-btn-holder' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color_hover_label'),
				'default' => '#42414f',
				'css' => ['{{element}}:hover .pagelayer-btn-holder' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width_hover'),
				'screen' => 1,
				'css' => ['{{element}}:hover .pagelayer-btn-holder' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type_hover' => ''
				],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius_hover'),
				'screen' => 1,
				'css' => ['{{element}}:hover .pagelayer-btn-holder' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
		],
		'ribbon_style' => [
			'show_ribbon' => array(
				'type' => 'checkbox',
				'label' => __pl('cta_show_ribbon'),
				'css' => ['{{element}} .pagelayer-cta-ribbon' => 'position: absolute; z-index: 2;'],
				'default' => 'true'
			),
			'ribbon_style' => array(
				'type' => 'select',
				'label' => __pl('style'),
				//'css' => ['{{element}} .pagelayer-cta-ribbon' => '{{val}}: 0px;'],
				'default' => '',
				'list' => array(
					'' => __pl('default')
				),
				'req' => [
					'show_ribbon' => 'true'
				]
			),
			'ribbon_text' => array(
				'type' => 'text',
				'label' => __pl('cta_ribbon_text_label'),
				'default' => __pl('ribbon_text_default'),
				'edit' => '.pagelayer-cta-ribbon-text',
				'req' => array(
					'show_ribbon' => 'true'
				),
			),
			'ribbon_bg' => array(
				'type' => 'color',
				'label' => __pl('cta_ribbon_bg_label'),
				'default' => '#0986c0',
				'css' => [
					'{{element}} .pagelayer-cta-ribbon' => 'background-color: {{val}}',
					'{{element}} .pagelayer-cta-ribbon-text:before' => 'border-top-color: {{val}}; border-bottom-color: {{val}};'
				],
				'req' => array(
					'show_ribbon' => 'true'
				),
			),
			'ribbon_color' => array(
				'type' => 'color',
				'label' => __pl('cta_ribbon_color_label'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-cta-ribbon' => 'color: {{val}}'],
				'req' => array(
					'show_ribbon' => 'true'
				),
			),
			'ribbon_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['{{element}} .pagelayer-cta-ribbon-text' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => [
					'show_ribbon' => 'true'
				]
			),
			'ribbon_pos' => array(
				'type' => 'radio',
				'label' => __pl('ribbon_pos_label'),
				'css' => ['{{element}} .pagelayer-cta-ribbon' => '{{val}}: 0px;'],
				'default' => 'left',
				'screen' => 1,
				'list' => array(
					'left' => __pl('left'),
					'right' => __pl('right')
				),
				'req' => [
					'show_ribbon' => 'true',
					'ribbon_style' => ''
				]
			),
			'ribbon_top' => array(
				'type' => 'slider',
				'label' => __pl('verticle_postion'),
				'css' => ['{{element}} .pagelayer-cta-ribbon' => 'top: {{val}}%;'],
				'screen' => 1,
				'default' => 5,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'req' => [
					'show_ribbon' => 'true',
					'ribbon_style' => ''
				]
			),
			'ribbon_spacing' => array(
				'type' => 'dimension',
				'label' => __pl('space_around'),
				'default' => '10,10',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-cta-ribbon-text' => 'padding-left: {{val[0]}}px; padding-right: {{val[1]}}px;'],
				'req' => [
					'show_ribbon' => 'true',
					'ribbon_style' => ''
				]
			),
			'ribbon_height' => array(
				'type' => 'slider',
				'label' => __pl('block_height'),
				'css' => [
					'{{element}} .pagelayer-cta-ribbon' => 'height: {{val}}px;',
					'{{element}} .pagelayer-cta-ribbon-left:before' => 'border-width: calc( {{val}}px / 2 ); right: calc( -{{val}}px / 2 );',
					'{{element}} .pagelayer-cta-ribbon-right:before' => 'border-width: calc( {{val}}px / 2 ); left: calc( -{{val}}px / 2 );',
					'{{element}} .pagelayer-cta-ribbon-left .pagelayer-cta-ribbon-text:before' => 'border-width: calc( {{val}}px / 2 ); right: calc( -{{val}}px / 2 );',
					'{{element}} .pagelayer-cta-ribbon-right .pagelayer-cta-ribbon-text:before' => 'border-width: calc( {{val}}px / 2 ); left: calc( -{{val}}px / 2 );',
					'{{element}} .pagelayer-cta-ribbon-text' => 'line-height: {{val}}px;'
				],
				'default' => 35,
				'min' => 0,
				'max' => 200,
				'screen' => 1,
				'step' => 1,
				'screen' => 1,
				'req' => [
					'show_ribbon' => 'true',
					'ribbon_style' => ''
				]
			),
			'ribbon_shadow' => array(
				'type' => 'color',
				'label' => __pl('shadow_color'),
				'default' => 'rgba(0,0,0,0.5)',
				'css' => [
					'{{element}} .pagelayer-cta-ribbon:after' => 'background-color: {{val}}',
					/* '{{element}} .pagelayer-cta-ribbon-left:before' => 'border-top-color: {{val}}; border-bottom-color: {{val}}; border-left-color: {{val}}',
					'{{element}} .pagelayer-cta-ribbon-right:before' => 'border-top-color: {{val}}; border-bottom-color: {{val}}; border-right-color: {{val}}', */
					'{{element}} .pagelayer-cta-ribbon:before' => 'border-top-color: {{val}}; border-bottom-color: {{val}};'
				],
				'req' => array(
					'show_ribbon' => 'true',
					'ribbon_style' => ''
				),
			),
			'shadow_height' => array(
				'type' => 'slider',
				'label' => __pl('shadow_pos'),
				'css' => [
					'{{element}} .pagelayer-cta-ribbon:before' => 'top: {{val}}px',
					'{{element}} .pagelayer-cta-ribbon:after' => 'height: {{val}}px; bottom: -{{val}}px;'
				],
				'default' => 3,
				'min' => 0,
				'max' => 50,
				'screen' => 1,
				'step' => 1,
				'screen' => 1,
				'req' => [
					'show_ribbon' => 'true',
					'ribbon_style' => ''
				]
			),
		],
		'styles' => [
			'image_style' => __pl('image_style'),
			'heading_styles' => __pl('heading_styles'),
			'subheading_styles' => __pl('subheading_styles'),
			'text_style' => __pl('text_style'),
			'content_styles' => __pl('content_styles'),
			'button_style' => __pl('button_style'),
			'ribbon_style' => __pl('ribbon_style'),
		]
	)
);

// Modal
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_modal', array(
		'name' => __pl('modal'),
		'group' => 'other',
		'innerHTML' => 'content',
		'html' => '<div class="pagelayer-modal-container">
			<div class="pagelayer-modal-button">
				<a class="pagelayer-btn-holder pagelayer-ele-link {{type}} {{size}} {{button_icon_position}}" onclick="pagelayer_render_pl_modal(event)">
					<i if="{{button_icon}}" class="{{button_icon}} pagelayer-btn-icon pagelayer-icon-left"></i>
					<span class="pagelayer-btn-text" if="{{button_text}}">{{button_text}}</span>
					<i if="{{button_icon}}" class="{{button_icon}} pagelayer-btn-icon pagelayer-icon-right"></i>
				</a>
			</div>
			<div class="pagelayer-modal-content pagelayer-modal-{{modal_style}}">
				<div if={{close_by_overlay}} class="pagelayer-modal-bg-close" onclick="pagelayer_pl_modal_close(event)"></div>
				<div class="pagelayer-modal-close" onclick="pagelayer_pl_modal_close(event)"></div>
				<div class="pagelayer-modal-body">
					<div if="{{modal_title}}"class="pagelayer-modal-title">{{modal_title}}</div>
					<div class="pagelayer-modal-content-overflow" if-ext="{{content}}">
						<div class="pagelayer-modal-bottom-content" if-ext="{{content}}">{{content}}</div>
					</div>
				</div>
			</div>
		</div>',
		'params' => array(
			'modal_style' => array(
				'type' => 'radio',
				'label' => __pl('obj_style'),
				'default' => 'dark',
				'list' => array(
					'dark' => __pl('dark'),
					'light' => __pl('light'),
					'custom' => __pl('custom')
				)
			),
			'modal_shadow_color' => array(
				'type' => 'color',
				'label' => __pl('obj_shadow_color_label'),
				'default' => '#0b0b0b',
				'req' => array(
					'modal_style' => 'custom'
				),
				'css' => ['{{element}} .pagelayer-modal-content' => 'background-color: {{val}}'],
			),
			'modal_popup_width' => array(
				'type' => 'slider',
				'label' => __pl('modal_popup_width'),
				'default' => 70,
				'min' => 20,
				'max' => 100,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-modal-body' => 'width: {{val}}%'],
			),
			'modal_popup_height' => array(
				'type' => 'slider',
				'label' => __pl('content_max_height'),
				'min' => 100,
				'max' => 1500,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-modal-content-overflow' => 'max-height: {{val}}px'],
			),
		),
		'icon_style' => [
			'button_icon' => array(
				'type' => 'icon',
				'label' => __pl('icon'),
				'list' => pagelayer_icon_class_list(true)
			),
			'icon_spacing' => array(
				'type' => 'spinner',
				'label' => __pl('icon_spacing'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-icon' => 'padding: 0 {{val}}px;'],
				'req' => array(
					'!button_icon' => ''
				),
			),
			'button_icon_position' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'pagelayer-btn-icon-left',
				'list' => array(
					'pagelayer-btn-icon-left' => __pl('left'),
					'pagelayer-btn-icon-right' => __pl('right')
				),
				'req' => array(
					'!button_icon' => ''
				),
			),
		],
		'btn_styles' => [
			'button_text' => array(
				'type' => 'text',
				'label' => __pl('button_text_label'),
				'default' => 'Open Modal Box',
			),
			'size' => array(
				'type' => 'select',
				'label' => __pl('button_size_label'),
				'default' => 'pagelayer-btn-large',
				'list' => array(
					'pagelayer-btn-mini' => __pl('mini'),
					'pagelayer-btn-small' => __pl('small'),
					'pagelayer-btn-large' => __pl('large'),
					'pagelayer-btn-extra-large' => __pl('extra_large'),
					'pagelayer-btn-double-large' => __pl('double_large'),
					'pagelayer-btn-custom' => __pl('custom'),
				)
			),
			'btn_custom_size' => array(
				'type' => 'spinner',
				'label' => __pl('btn_custom_size'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'padding: {{val}}px {{val}}px;'],
				'req' => array(
					'size' => 'pagelayer-btn-custom'
				)
			),
			'type' => array(
				'type' => 'select',
				'label' => __pl('button_type_label'),
				'default' => 'pagelayer-btn-default',
				'list' => array(
					'pagelayer-btn-default' => __pl('btn_type_default'),
					'pagelayer-btn-primary' => __pl('btn_type_primary'),
					'pagelayer-btn-secondary' => __pl('btn_type_secondary'),
					'pagelayer-btn-success' => __pl('btn_type_success'),
					'pagelayer-btn-info' => __pl('btn_type_info'),
					'pagelayer-btn-warning' => __pl('btn_type_warning'),
					'pagelayer-btn-danger' => __pl('btn_type_danger'),
					'pagelayer-btn-dark' => __pl('btn_type_dark'),
					'pagelayer-btn-light' => __pl('btn_type_light'),
					'pagelayer-btn-link' => __pl('btn_type_link'),
					'pagelayer-btn-custom' => __pl('btn_type_custom')
				),
			),
			'button_full_width' => array(
				'type' => 'checkbox',
				'label' => __pl('stretch'),
				'screen' => 1,
				'css' => ['{{element}} a' => 'width: 100%; text-align: center;']
			),
			'button_align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'left',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-modal-button' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				),
				'req' => array(
					'button_full_width' => ''
				)
			),
			'btn_hover' => array(
				'type' => 'radio',
				'label' => '',
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_label'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'background-color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_color' => array(
				'type' => 'color',
				'label' => __pl('btn_color_label'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('btn_hover_delay_label'),
				'desc' => __pl('btn_hover_delay_desc'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-btn-holder' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'background-color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type' => ''
				],
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color_hover_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width_hover'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type_hover' => ''
				],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius_hover'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-btn-holder:hover' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
		],
		'title_style' => [
			'modal_title' => array(
				'type' => 'textarea',
				'label' => __pl('title'),
				'default' => __pl('modal_title_content_default'),
				'edit' => '.pagelayer-modal-title'
			),
			'title_align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'center',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-modal-title' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				),
				'req' => [
					'!modal_title' => ''
				],
			),
			'title_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-modal-title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => [
					'!modal_title' => ''
				],
			),
			'padding' => array(
				'type' => 'padding',
				'label' => __pl('stars_spacing'),
				'default' => '25,25,25,25',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-modal-title' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px'],
				'req' => [
					'!modal_title' => ''
				],
			),
			'modal_title_bgcolor' => array(
				'type' => 'color',
				'label' =>  __pl('bg_color'),
				'default' => '#3D54DF',
				'css' => ['{{element}} .pagelayer-modal-title' => 'background-color: {{val}}'],
				'req' => [
					'!modal_title' => '',
					'modal_style' => 'custom'
				],
			),
			'modal_title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-modal-title' => 'color: {{val}}'],
				'req' => [
					'!modal_title' => '',
					'modal_style' => 'custom'
				],
			)
		],
		'content_style' => [
			'content' => array(
				'type' => 'editor',
				'label' => __pl('obj_content'),
				'default' => __pl('content_with_tags_default'),
				'edit' => '.pagelayer-modal-bottom-content'
			),
			'content_padding' => array(
				'type' => 'padding',
				'label' => __pl('stars_spacing'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-modal-bottom-content' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px']
			),
			'modal_content_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#000000',
				'req' => array(
					'modal_style' => 'custom'
				),
				'css' => ['{{element}} .pagelayer-modal-body' => 'color: {{val}}'],
			),
			'modal_content_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#ffffff',
				'req' => array(
					'modal_style' => 'custom'
				),
				'css' => ['{{element}} .pagelayer-modal-bottom-content' => 'background-color: {{val}}'],
			),
		],
		'close_style' => [
			'close_size' => array(
				'type' => 'spinner',
				'label' => __pl('size'),
				'min' => 0,
				'step' => 1,
				'max' => 200,
				'default' => 50,
				'screen' => 1,
				'css' => [
					'{{element}} .pagelayer-modal-close:before' => 'height: {{val}}px; right: calc( {{val}}px / 2 );',
					'{{element}} .pagelayer-modal-close' => 'height: {{val}}px; width: {{val}}px;',
					'{{element}} .pagelayer-modal-close:after' => 'height: {{val}}px; right: calc( {{val}}px / 2 );'
				]
			),
			'modal_close_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-modal-close::before' => 'background-color:{{val}}', '{{element}} .pagelayer-modal-close::after' => 'background-color:{{val}}'],
				'req' => array(
					'modal_style' => 'custom',
				)
			),
			'close_by_overlay' => array(
				'type' => 'checkbox',
				'label' => __pl('close_by_overlay')
			)
		],
		'styles' => [
			'btn_styles' => __pl('btn_style'),
			'icon_style' => __pl('icon_style'),
			'title_style' => __pl('title_style'),
			'content_style' => __pl('content_style'),
			'close_style' => __pl('close_style'),
		]
	)
);

// Splash Screen
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_splash', array(
		'name' => __pl('splash_screen'),
		'group' => 'other',
		'innerHTML' => 'content',
		'html' => '<div class="pagelayer-splash-container  pagelayer-splash-{{style}}">
			<div if={{close_by_overlay}} class="pagelayer-splash-bg-close"></div>
			<span class="pagelayer-splash-close"></span>
			<div class="pagelayer-splash-body">
					<div if="{{title}}"class="pagelayer-splash-title">{{title}}</div>
					<div class="pagelayer-splash-content-overflow" if-ext="{{content}}">
						<div class="pagelayer-splash-bottom-content">{{content}}</div>
					</div>	
				</div>
		</div>',
		'params' => array(
			'style' => array(
				'type' => 'radio',
				'label' => __pl('style'),
				'default' => 'dark',
				'list' => array(
					'dark' => __pl('dark'),
					'light' => __pl('light'),
					'custom' => __pl('custom')
				)
			),
			'shadow_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#0b0b0b',
				'css' => ['{{element}} .pagelayer-splash-container ' => 'background:{{val}}'],
				'req' => array(
					'style' => 'custom'
				)
			),
			'display' => array(
				'type' => 'select',
				'label' => __pl('display'),
				'list' => array(
					'once' => __pl('once'),
					'always' => __pl('always')
				),
				'default' => 'once',
				'addAttr' => ['{{element}} .pagelayer-splash-container' => 'display_type="{{display}}"']
			),
			'delay' => array(
				'type' => 'spinner',
				'label' => __pl('animation_delay'),
				'default' => 1000,
				'min' => 100,
				'addAttr' => ['{{element}} .pagelayer-splash-container' => 'delay="{{delay}}"']
			),
			'width' => array(
				'type' => 'slider',
				'label' => __pl('icon_border_width'),
				'screen' => 1,
				'default' => '90',
				'css' => ['{{element}} .pagelayer-splash-body' => 'width:{{val}}%'],
			),
			'splash_popup_height' => array(
				'type' => 'slider',
				'label' => __pl('content_max_height'),
				'min' => 100,
				'max' => 1500,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-splash-content-overflow' => 'max-height: {{val}}px'],
			),
		),
		'title_style' => [
			'title' => array(
				'type' => 'textarea',
				'label' => __pl('title'),
				'default' => __pl('splash_title_content_default'),
				'edit' => '.pagelayer-splash-title'
			),
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'center',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-splash-title' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				),
				'req' => [
					'!title' => ''
				],
			),
			'title_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-splash-title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => [
					'!title' => ''
				],
			),
			'padding' => array(
				'type' => 'padding',
				'label' => __pl('space_around'),
				'default' => '25,25,25,25',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-splash-title' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px'],
				'req' => [
					'!title' => ''
				],
			),
			'title_bg' => array(
				'type' => 'color',
				'label' =>  __pl('bg_color'),
				'default' => '#3D54DF',
				'css' => ['{{element}} .pagelayer-splash-title' => 'background-color: {{val}}'],
				'req' => [
					'!title' => '',
					'style' => 'custom'
				],
			),
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-splash-title' => 'color: {{val}}'],
				'req' => [
					'!title' => '',
					'style' => 'custom'
				],
			)
		],
		'content_style' => [
			'content' => array(
				'type' => 'editor',
				'label' => __pl('obj_content'),
				'default' => __pl('content_with_tags_default'),
				'edit' => '.pagelayer-splash-bottom-content'
			),
			'content_color' => array(
				'type' => 'color',
				'label' => __pl('obj_content_color_label'),
				'default' => '#0b0b0b',
				'css' => ['{{element}} .pagelayer-splash-container ' => 'color:{{val}}'],
				'req' => array(
					'style' => 'custom'
				)
			),
			'content_padding' => array(
				'type' => 'padding',
				'label' => __pl('space_around'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-splash-bottom-content' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px'],
			),
		],
		'close_style' => [
			'close_size' => array(
				'type' => 'spinner',
				'label' => __pl('size'),
				'min' => 0,
				'step' => 1,
				'max' => 200,
				'default' => 50,
				'screen' => 1,
				'css' => [
					'{{element}} .pagelayer-splash-close:before' => 'height: {{val}}px; right: calc( {{val}}px / 2 );',
					'{{element}} .pagelayer-splash-close' => 'height: {{val}}px; width: {{val}}px;',
					'{{element}} .pagelayer-splash-close:after' => 'height: {{val}}px; right: calc( {{val}}px / 2 );'
				]
			),
			'close_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-splash-close::after, {{element}} .pagelayer-splash-close::before ' => 'background-color:{{val}}'],
				'req' => array(
					'style' => 'custom'
				)
			),
			'close_by_overlay' => array(
				'type' => 'checkbox',
				'label' => __pl('close_by_overlay')
			),
		],
		'styles' => [
			'title_style' => __pl('title_style'),
			'content_style' => __pl('content_style'),
			'close_style' => __pl('close_style'),
		]
	)
);

// Chart
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_chart', array(
		'name' => __pl('chart'),
		'group' => 'other',
		'has_group' => [
			'section' => 'datasets', 
			'prop' => 'elements'
		],
		'holder' => '.pagelayer-chart-child-holder',
		'html' => '<div if="{{title}}" class="pagelayer-chart-title">{{title}}</div>
			<div class="pagelayer-chart-child-holder"></div>
			<canvas class="pagelayer-chart-holder"></canvas>',
		'params' => array(
			'type' => array(
				'type' => 'select',
				'label' => __pl('type'),
				'desc' => __pl('google_charts_type_desc'),
				'default' => 'bar',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'chart-type="{{type}}"'],
				'list' => array(
					'line' => __pl('google_charts_type_list_line'),
					'bar' => __pl('google_charts_type_list_bar'),
					'horizontalBar' => __pl('horizontalbar_chart'),
					'radar' => __pl('radar'),
					'doughnut' => __pl('doughnut_chart'),
					'pie' => __pl('google_charts_type_list_pie'),
					'polarArea' => __pl('polararea'),
				)
			),
			'legend_pos' => array(
				'type' => 'select',
				'label' => __pl('legend_pos'),
				//'desc' => __pl('legend_pos_desc'),
				'default' => 'top',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'chart-legend="{{legend_pos}}"'],
				'list' => array(
					'' => __pl('none'),
					'top' => __pl('top'),
					'left' => __pl('left'),
					'bottom' => __pl('bottom'),
					'right' => __pl('right'),
				)
			),
			'custom_dimension' => array(
				'type' => 'checkbox',
				'label' => __pl('custom_dimension'),
				'default' => '',
			),
			'chart_height' => array(
				'type' => 'slider',
				'label' => __pl('custom_height'),
				'screen' => 1,
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'default' => 350,
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'chart-height="{{chart_height}}"'],
				'css' => ['{{element}} .pagelayer-chart-holder' => 'height: {{val}}px !important;'],
				'req' => array(
					'custom_dimension' => 'true'
				),
			),
			'chart_width' => array(
				'type' => 'slider',
				'label' => __pl('custom_width'),
				'screen' => 1,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'default' => 100,
				'css' => ['{{element}} .pagelayer-chart-holder' => 'width: {{val}}% !important;'],
				'req' => array(
					'custom_dimension' => 'true'
				),
			),
			/* 'colors' => array(
				'type' => 'textarea',
				'label' => __pl('google_charts_colors_label'),
				'desc' => 'You can give multiple colors here and separate them by coma(,) E.g. #000000, #ffffff, #f4c63d, #923a3a',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'chart-colors="{{colors}}"'],
			), */
			/* 'width' => array(
				'type' => 'slider',
				'label' => __pl('Chart_width'),
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-width="{{width}}%"'],
			), */
		),
		'label_style' => [
			'labels' => array(
				'type' => 'textarea',
				'label' => __pl('label_style'),
				'desc' => __pl('Enter labels with comma(,) separeted'),
				'default' => 'Jan,Feb,Mar,Apr,May',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'chart-labels="{{labels}}"'],
			),
			'label_colors' => array(
				'type' => 'textarea',
				'label' => __pl('colors'),
				'desc' => __pl('Enter hex color code with comma(,) separeted'),
				'default' => '#3e95cd,#8e5ea2,#3cba9f,#e8c3b9,#c45850',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'chart-colors="{{label_colors}}"'],
				'req' => [
					'type' => ['doughnut', 'pie', 'polarArea']
				]
			),
		],
		'datasets' => [
			'elements' => array(
				'type' => 'group',
				'label' => __pl('datasets'),
				'sc' => PAGELAYER_SC_PREFIX.'_chart_datasets',
				'item_label' => array(
					'default' => __pl('dataset'),
					'param' => 'datasets',
				),
				'count' => 1,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('dataset_name'))),
			),
		],
		'ticks_style' => [
			'tick_type' => array(
				'type' => 'radio',
				'label' => __pl('axis'),
				'default' => 'xaxis',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'xaxis' => __pl('x-axis'),
					'yaxis' => __pl('y-axis'),
				)
			),
			'xcolor' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-xcolor="{{xcolor}}"'],
				'show' => array(
					'tick_type' => 'xaxis'
				),
			),
			'xsize' => array(
				'type' => 'slider',
				'label' => __pl('size'),
				'min' => 0,
				'max' => 100,
				'screen' => 1,
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-xsize="{{xsize}}"'],
				'show' => array(
					'tick_type' => 'xaxis'
				),
			),
			'xrotate' => array(
				'type' => 'slider',
				'label' => __pl('Rotate'),
				'min' => 0,
				'max' => 360,
				'screen' => 1,
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-xrotate="{{xrotate}}"'],
				'show' => array(
					'tick_type' => 'xaxis'
				),
			),
			'xbegin' => array(
				'type' => 'checkbox',
				'label' => __pl('begin_at_zero'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-xbegin="{{xbegin}}"'],
				'show' => array(
					'tick_type' => 'xaxis'
				),
			),
			'ycolor' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-ycolor="{{ycolor}}"'],
				'show' => array(
					'tick_type' => 'yaxis'
				),
			),
			'ysize' => array(
				'type' => 'slider',
				'label' => __pl('size'),
				'min' => 0,
				'max' => 100,
				'screen' => 1,
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-ysize="{{ysize}}"'],
				'show' => array(
					'tick_type' => 'yaxis'
				),
			),
			'yrotate' => array(
				'type' => 'slider',
				'label' => __pl('Rotate'),
				'min' => 0,
				'max' => 360,
				'screen' => 1,
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-yrotate="{{yrotate}}"'],
				'show' => array(
					'tick_type' => 'yaxis'
				),
			),
			'ybegin' => array(
				'type' => 'checkbox',
				'label' => __pl('begin_at_zero'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-chart-holder' => 'data-ybegin="{{ybegin}}"'],
				'show' => array(
					'tick_type' => 'yaxis'
				),
			),
		],
		'title_style' => [
			'title' => array(
				'type' => 'text',
				'label' => __pl('title'),
				'default' => __pl('company_performance'),
				'edit' => '.pagelayer-chart-title',
			),
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#47425d',
				'css' => ['{{element}} .pagelayer-chart-title' => 'color:{{val}}'],
				'req' => [
					'!title' => ''
				],
			),
			'title_align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'center',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-chart-title' => 'text-align:{{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'req' => [
					'!title' => ''
				],
			),
			'title_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-chart-title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => [
					'!title' => ''
				],
			),
		],
		'styles' => [
			'label_style' => __pl('label_style'),
			'datasets' => __pl('datasets'),
			'ticks_style' => __pl('ticks_style'),
			'title_style' => __pl('title_style'),
		],
	)
);

// Chart
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_chart_datasets', array(
		'name' => __pl('chart_dataset'),
		'group' => 'other',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_chart'],
		'holder' => '.pagelayer-chart-datasets',
		'html' => '<div class="pagelayer-chart-datasets"></div>',
		'params' => array(
			'label' => array(
				'type' => 'text',
				'label' => __pl('label'),
				'default' => __pl('dataset_name'),
				'addAttr' => ['{{element}} .pagelayer-chart-datasets' => 'datasets-label="{{label}}"'],
			),
			'datasets' => array(
				'type' => 'textarea',
				'label' => __pl('dataset_name'),
				'desc' => __pl('Enter the datasets and separate by coma(,)'),
				'default' => '5,9,7,8,5',
				'addAttr' => ['{{element}} .pagelayer-chart-datasets' => 'chart-datasets="{{datasets}}"'],
			),
			'bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'addAttr' => ['{{element}} .pagelayer-chart-datasets' => 'dataset-bg="{{bg_color}}"'],
			),
			'chart_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'addAttr' => ['{{element}} .pagelayer-chart-datasets' => 'border-color="{{chart_border_color}}"'],
			),
			'fill_color' => array(
				'type' => 'checkbox',
				'label' => __pl('fill_color'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-chart-datasets' => 'dataset-fill="{{fill_color}}"']
			),
		),
	)
);

// Menu list
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_menu_list', array(
		'name' => __pl('menu_list'),
		'group' => 'other',
		'holder' => '.pagelayer-menu-list',
		//'innerHTML' => 'text',
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'html' => '<div class="pagelayer-menu-list"></div>',
		'params' => [
			'elements' => array(
				'type' => 'group',
				'label' => __pl('menu_item'),
				'sc' => PAGELAYER_SC_PREFIX.'_menu_item',
				'item_label' => array(
					'default' => __pl('menu_item'),
					'param' => 'title'
				),
				'count' => 3,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('menu_name'))),
			),
			'item_space' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'default' => 10,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-item' => 'padding-bottom: calc({{val}}px / 2); margin-bottom: calc({{val}}px / 2);'],
			),
			'valign' => array(
				'type' => 'select',
				'label' => __pl('badge_vertical_align'),
				'default' => 'center',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-item' => '-webkit-box-align:{{val}}; -webkit-align-items:{{val}}; -ms-flex-align:{{val}}; align-items:{{val}};'],
				'list' => [
					'flex-start' => __pl('top'),
					'center' => __pl('center'),
					'flex-end' => __pl('bottom'),
				],
			),
			'item_border_type' => array(
				'type' => 'select',
				'label' => __pl('divider'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-menu-item' => 'border-bottom-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'item_border_color' => array(
				'type' => 'color',
				'label' => __pl('divider_color'),
				'default' => '#dbdbdb',
				'css' => ['{{element}} .pagelayer-menu-item' => 'border-bottom-color: {{val}};'],
				'req' => ['!item_border_type' => '']
			),
			'item_border_width' => array(
				'type' => 'slider',
				'label' => __pl('thickness'),
				'min' => 0,
				'max' => 20,
				'step' => 1,
				'default' => 2,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-item' => 'border-bottom-width: {{val}}px;'],
				'req' => ['!item_border_type' => '']
			),
		],
		'title_styles' => [
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#0c0901',
				'css' => ['{{element}} .pagelayer-menu-title' => 'color:{{val}}'],
			),
			'title_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',19,,700,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-menu-title span:first-child' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'space_bottom' => array(
				'type' => 'slider',
				'label' => __pl('space_bottom'),
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-title' => 'padding-bottom:{{val}}px;']
			),
		],
		'item_details' => [
			'includes_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#484745',
				'css' => ['{{element}} .pagelayer-menu-includes' => 'color:{{val}}'],
			),
			'includes_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',12,italic,200,,,,,,,',
				'css' => ['{{element}} .pagelayer-menu-includes' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		],
		'separator_style' => [
			'separater' => array(
				'type' => 'select',
				'label' => __pl('type'),
				'default' => 'dotted',
				'css' => ['{{element}} .pagelayer-menu-separeter' =>'border-top-style:{{val}};'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
				]
			),
			'separater_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#777777',
				'css' => ['{{element}} .pagelayer-menu-separeter' => 'border-top-color:{{val}};'],
				'req' => ['!separater' => '']
			),
			'separater_width' => array(
				'type' => 'slider',
				'label' => __pl('shape_width'),
				'min' => 0,
				'max' => 20,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-separeter' => 'border-top-width:{{val}}px;'],
				'default' => 1,
				'req' => ['!separater' => '']
			),
			'separater_spacing' => array(
				'type' => 'slider',
				'label' => __pl('stars_spacing'),
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-separeter' => 'margin: 0 {{val}}px;'],
				'default' => 10,
				'req' => ['!separater' => '']
			),
		],
		'price_style' => [
			'price_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#c02530',
				'css' => ['{{element}} .pagelayer-menu-price' => 'color:{{val}}'],
			),
			'price_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',25,,600,,,,,,,',
				'css' => ['{{element}} .pagelayer-menu-price' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		],
		'desc_style' => [
			'desc_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#484745',
				'css' => ['{{element}} .pagelayer-menu-desc' => 'color:{{val}}'],
			),
			'desc_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',14,,,,,solid,1.5,,,',
				'css' => ['{{element}} .pagelayer-menu-desc' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		],
		'image_style' => [
			'custom_size' => array(
				'type' => 'spinner',
				'label' => __pl('shape_width'),
				'min' => 0,
				'max' => 2000,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-img img' => 'width: {{val}}px;'],
				'default' => 400,
			),
			'img_spacing' => array(
				'type' => 'slider',
				'label' => __pl('stars_spacing'),
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-img' => 'padding-right: {{val}}px;'],
				'default' => 10,
			),
			'img_border_hover' => array(
				'type' => 'radio',
				'label' => '',
				'default' => '',
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'img_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-menu-img img' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'img_border_hover' => ''
				),
			),
			'img_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-menu-img img' => 'border-color: {{val}};'],
				'req' => array(
					'!img_border_type' => ''
				),
				'show' => array(
					'img_border_hover' => ''
				),
			),
			'img_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-img img' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!img_border_type' => ''
				],
				'show' => array(
					'img_border_hover' => ''
				),
			),
			'img_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-menu-img img' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'show' => array(
					'img_border_hover' => ''
				),
			),
			'img_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}}:hover .pagelayer-menu-img img' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'img_border_hover' => 'hover'
				),
			),
			'img_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}}:hover .pagelayer-menu-img img' => 'border-color: {{val}};'],
				'default' => '#0986c0',
				'req' => array(
					'!img_border_type' => ''
				),
				'show' => array(
					'img_border_hover' => 'hover'
				),
			),
			'img_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}}:hover .pagelayer-menu-img img' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!img_border_type' => ''
				],
				'show' => array(
					'img_border_hover' => 'hover'
				),
			),
			'img_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}}:hover .pagelayer-menu-img img' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'show' => array(
					'img_border_hover' => 'hover'
				),
			),
		],
		'styles' => [
			'title_styles' => __pl('title_style'),
			'item_details' => __pl('item_details'),
			'price_style' => __pl('price_style'),
			'desc_style' => __pl('desc_style'),
			'image_style' => __pl('image_style'),
			'separator_style' => __pl('separator_style'),
		]
	)
);

// Menu item
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_menu_item', array(
		'name' => __pl('menu_item'),
		'group' => 'other',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_menu_list'],
		//'innerHTML' => 'text',
		'html' => '<div class="pagelayer-menu-item">
			<div if="{{img}}" class="pagelayer-menu-img">
				<img class="pagelayer-img" src="{{{img-url}}}" title="{{{img-title}}}" alt="{{{img-alt}}}"/>
			</div>
			<div class="pagelayer-menu-details">
				<div class="pagelayer-menu-text">
					<div class="pagelayer-menu-title">
						<span if="{{title}}">
							<div class="pagelayer-menu-title-holder">{{title}}</div>
							<div if="{{includes}}" class="pagelayer-menu-includes">{{includes}}</div>
						</span>
						<span class="pagelayer-menu-separeter"></span>
						<span if="{{price}}" class="pagelayer-menu-price">{{price}}</span>
					</div>
					<div if="{{desc}}" class="pagelayer-menu-desc">{{desc}}</div>
				</div>
			</div>
		</div>',
		'params' => array(
			'title' => array(
				'type' => 'text',
				'label' => __pl('title'),
				'default' => __pl('menu_item'),
				'edit' => '.pagelayer-menu-title-holder',
			),
			'includes' => array(
				'type' => 'text',
				'label' => __pl('includes_styles'),
				'default' => __pl('includes_default'),
				'edit' => '.pagelayer-menu-includes',
			),
			'desc' => array(
				'type' => 'textarea',
				'label' => __pl('desc_style'),
				'default' => __pl('desc_default'),
				'edit' => '.pagelayer-menu-desc',
			),
			'price' => array(
				'type' => 'text',
				'label' => __pl('price_style'),
				'default' => __pl('$59'),
				'edit' => '.pagelayer-menu-price',
			),
			'img' => array(
				'type' => 'image',
				'label' => __pl('Image'),
			),
		)
		
	)
);

// Post Portfolio
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_post_folio', array(
		'name' => __pl('post_folio'),
		'group' => 'other',
		'html' => '<div class="pagelayer-postfolio-section">{{post_html}}</div>',
		'params' => array(
			'type' => array(
				'type' => 'select',
				'label' => __pl('type'),
				'default' => 'post',
				'list' => array(
					'post' => __pl('post'),
					'page' => __pl('page'),
				),
			),
			'columns' => array(
				'type' => 'select',
				'label' => __pl('columns'),
				'default' => '3',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-container' => 'grid-template-columns: repeat({{val}},1fr);'],
				'list' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
			),
			'count' => array(
				'type' => 'spinner',
				'label' => __pl('post_per_page'),
				'min' => 1,
				'step' => 1,
				'default' => 6,
			),
			'ratio' => array(
				'type' => 'spinner',
				'label' => __pl('ratio'),
				'min' => 0,
				'step' => 0.1,
				'max' => 2,
				'default' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-thumb' => 'padding: calc(50% * {{val}}) 0;'],
			),
			'col_gap' => array(
				'type' => 'slider',
				'label' => __pl('col_gap'),
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-container' => 'grid-column-gap: {{val}}px;'],
			),
			'row_gap' => array(
				'type' => 'slider',
				'label' => __pl('row_gap'),
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-container' => 'grid-row-gap: {{val}}px;'],
			),
			'thumb_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'min' => 0,
				'step' => 1,
				'max' => 50,
				'default' => 0,
				'screen' => 1,
				'css' => [
					'{{element}} .pagelayer-postfolio-thumb' => 'border-radius:{{val}}%;',
					'{{element}} .pagelayer-postfolio-content' => 'border-radius:{{val}}%;'
				],
			),
		),
		'title_style' => [
			'title_hover' => [
				'type' => 'radio',
				'label' => '',
				'default' => '',
				'list' => [
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				],
			],
			'show_title' => array(
				'type' => 'checkbox',
				'label' => __pl('show_title'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-entry-title' => 'opacity:1;'],
				'show' => ['title_hover' => '']
			),
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}} .pagelayer-entry-title' => 'color:{{val}};'],
				'show' => ['title_hover' => ''],
				'req' => ['show_title' => 'true']
			),
			'title_typo' => array(
				'type' => 'typography',
				'label' => __pl('title_typo'),
				'default' => ',20,,600,,,,,,,',
				'css' => ['{{element}} .pagelayer-entry-title' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['title_hover' => ''],
				'req' => ['show_title' => 'true']
			),
			'show_title_hover' => array(
				'type' => 'checkbox',
				'label' => __pl('show_title'),
				'default' => 'true',
				'css' => ['{{element}} .pagelayer-postfolio-content:hover .pagelayer-entry-title' => 'opacity:1;'],
				'show' => ['title_hover' => 'hover']
			),
			'title_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#333333',
				'css' => ['{{element}} .pagelayer-postfolio-content:hover .pagelayer-entry-title' => 'color:{{val}};'],
				'show' => ['title_hover' => 'hover'],
				'req' => ['show_title_hover' => 'true']
			),
			'title_typo_hover' => array(
				'type' => 'typography',
				'label' => __pl('title_typo'),
				'default' => ',20,,600,,,,,,,',
				'css' => ['{{element}} .pagelayer-postfolio-content:hover .pagelayer-entry-title' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['title_hover' => 'hover'],
				'req' => ['show_title_hover' => 'true']
			),
		],
		'overlay_style' => [
			'overlay_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#dadada',
				'css' => ['{{element}} .pagelayer-postfolio-thumb' => 'background-color:{{val}};']
			),
			'overlay_color_hover' => array(
				'type' => 'color',
				'label' => __pl('on_hover'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-postfolio-content:hover' => 'background-color:{{val}};']
			),
			'overlay_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('overlay_hover_delay'),
				'min' => 0,
				'step' => 100,
				'max' => 3000,
				'default' => 400,
				'css' => [
					'{{element}} .pagelayer-postfolio-content' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;',
					'{{element}} .pagelayer-entry-title' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;',
				],
			),
		],
		'filter_style' => [
			'filter_by' => array(
				'type' => 'select',
				'label' => __pl('filter_by'),
				'default' => 'category',
				'list' => array(
					'none' => __pl('none'),
					'category' => __pl('category'),
					'tags' => __pl('tags'),
				),
			),
			'filter_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#8DBCFA',
				'css' => ['{{element}} .pagelayer-postfolio-btn' => 'color:{{val}};'],
				'req' => ['!filter_by' => '']
			),
			'filter_bg' => array(
				'type' => 'color',
				'label' => __pl('background_color'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-postfolio-btn' => 'background-color:{{val}};'],
				'req' => ['!filter_by' => '']
			),
			'filter_typo' => array(
				'type' => 'typography',
				'label' => __pl('title_typo'),
				'default' => ',20,,,,,,,,,',
				'css' => ['{{element}} .pagelayer-postfolio-btn' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => ['!filter_by' => '']
			),
			'filter_size' => array(
				'type' => 'dimension',
				'label' => __pl('btn_padding'),
				'default' => '5,10',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-btn' => 'padding-top: {{val[0]}}px; padding-bottom: {{val[0]}}px; padding-left: {{val[1]}}px; padding-right: {{val[1]}}px;'],
				'req' => ['!filter_by' => '']
			),
			'filter_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-btn' => 'border-radius:{{val}}px;'],
				'req' => ['!filter_by' => '']
			),
			'filter_spacing' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-btn' => 'margin:0 {{val}}px;'],
				'req' => ['!filter_by' => '']
			),
			'space_bottom' => array(
				'type' => 'slider',
				'label' => __pl('space_bottom'),
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 10,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-postfolio-filter' => 'padding-bottom:{{val}}px;'],
				'req' => ['!filter_by' => '']
			),
		],
		'styles' => [
			'title_style' => __pl('title_style'),
			'overlay_style' => __pl('overlay_style'),
			'filter_style' => __pl('filter_style'),
		]
	)
);

// Posts
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_posts', array(
		'name' => __pl('posts'),
		'group' => 'other',
		'prevent_inside' => ['pl_slides'],
		'html' => '<div class="pagelayer-posts-container">{{post_html}}</div>
		<div if="{{infinite_types}}" class="pagelayer_load_button" data-text={{infinite_final}}>
			<a data-max="{{max_pages}}" class="pagelayer-btn-holder pagelayer-btn-load pagelayer-ele-link {{infinite_btn_type}} {{infinite_btn_size}} {{load_btn_icon_position}}">
				<i if="{{load_btn_icon}}" class="{{load_btn_icon}} pagelayer-btn-load-icon"></i>
				<span if="{{infinite_text}}" class="pagelayer-btn-load-text" >{{infinite_text}}</span>
				<i if="{{load_btn_icon}}" class="{{load_btn_icon}} pagelayer-btn-load-icon"></i>
			</a>
			<div class="pagelayer-loader-holder" >
				<i class="fa fa-spinner fa-spin fa-3x fa-fw" aria-hidden="true"></i>
			</div>
		</div>',
		'params' => array(
			'post_type' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_post_type_label'),
				'desc' => __pl('posts_grid_post_type_desc'),
				'list' => array(
					'post' => __pl('post'),
					//'page' => __pl('page')
				)
			),
			'by_period' => array(
				'type' => 'select',
				'label' => __pl('period'),
				'default' => '',
				'list' => array(
					'' => __pl('all'),
					'last_day' => __pl('last_day'),
					'last_week' => __pl('last_week'),
					'last_month' => __pl('last_month'),
					'last_year' => __pl('last_year'),
					'custom' => __pl('custom'),
					//'ID' => __pl('ID'),
				)
			),
			'before_date' => array(
				'type' => 'datetime',
				'displayMode' => 'datetime', // date | datetime (default)
				'returnMode' => 'YYYY-MM-DD H:m:s', // mysql format uses here (default: Y-m-d H:i:s )
				'label' => __pl('before'),
				'default' => '',
				'req' => ['by_duration' => 'custom']
			),
			'after_date' => array(
				'type' => 'datetime',
				'displayMode' => 'datetime', // date | datetime (default)
				'returnMode' => 'YYYY-MM-DD H:m:s', // mysql format uses here (default: Y-m-d H:i:s )
				'label' => __pl('after'),
				'default' => '',
				'req' => ['by_duration' => 'custom']
			),
			'orderby' => array(
				'type' => 'select',
				'label' => __pl('posts_order_by'),
				'default' => 'date',
				'list' => array(
					'date' => __pl('date'),
					'title' => __pl('title'),
					'modified' => __pl('posts_order_by_modified'),
					'rand' => __pl('posts_order_by_random'),
					'menu_order' => __pl('posts_order_by_menu_order'),
					'author' => __pl('author'),
					'ID' => __pl('ID'),
				)
			),
			'posts_order' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_sort_order'),
				'default' => 'DESC',
				'list' => array(
					'ASC' => __pl('posts_grid_sort_order_ascending'),
					'DESC' => __pl('posts_grid_sort_order_descending')
				)
			),
			'count' => array(
				'type' => 'spinner',
				'label' => __pl('post_per_page'),
				'min' => 1,
				'step' => 1,
				'default' => 6,
			),
			'que_sec' => array(
				'type' => 'radio',
				'label' => '',
				'default' => 'include',
				'list' => array(
					'include' => __pl('include'),
					'exclude' => __pl('exclude')
				)
			),
			'include_by' => array(
				'type' => 'multiselect',
				'label' => __pl('include_by'),
				'list' => array(
					'term' => __pl('term'),
					'author' => __pl('author')
				),
				'show' => ['que_sec' => 'include']
			),
			'inc_term' => array(
				'type' => 'multiselect',
				'label' => __pl('term'),
				'list' => pagelayer_get_post_term(),
				'req' => ['include_by' => ['term','term,author']],
				'show' => ['que_sec' => 'include']
			),
			'inc_author' => array(
				'type' => 'select',
				'label' => __pl('author'),
				'list' => pagelayer_get_post_author(),
				'req' => ['include_by' => ['author','term,author']],
				'show' => ['que_sec' => 'include']
			),
			'ignore_sticky' => array(
				'label' => __pl('ignore_sticky_posts'),
				'type' => 'checkbox',
				'show' => ['que_sec' => 'include']
			),
			'exclude_by' => array(
				'type' => 'multiselect',
				'label' => __pl('exclude_by'),
				'list' => array(
					'term' => __pl('term'),
					'author' => __pl('author')
				),
				'show' => ['que_sec' => 'exclude']
			),
			'exc_term' => array(
				'type' => 'multiselect',
				'label' => __pl('term'),
				'list' => pagelayer_get_post_term(),
				'req' => ['exclude_by' => ['term','term,author']],
				'show' => ['que_sec' => 'exclude']
			),
			'exc_author' => array(
				'type' => 'select',
				'label' => __pl('author'),
				'list' => pagelayer_get_post_author(),
				'req' => ['exclude_by' => ['author','term,author']],
				'show' => ['que_sec' => 'exclude']
			),
			'offset' => array(
				'type' => 'spinner',
				'label' => __pl('offset'),
				'min' => 0,
				'step' => 1,
				'max' => 50,
				'show' => ['que_sec' => 'exclude']
			),
		),
		'posts_options' => $pagelayer_posts_options,
		'thumb_style' => $pagelayer_thumb_style,
		'title_style' => $pagelayer_title_style,
		'meta_options' => $pagelayer_meta_style,
		'content_style' => $pagelayer_content_style,
		'more_style' => $pagelayer_more_style,
		'btn_border_style' => $pagelayer_btn_border_style,
		'slider_options' => [
			'enable_slider' => array(
				'type' => 'checkbox',
				'label' => __pl('enable_slider'),
				'default' => '',
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-enable_slider="enable"']
			),
			'slide_items' => array(
				'type' => 'spinner',
				'label' => __pl('number_of_items'),
				'min' => 1,
				'step' => 1,
				'max' => 10,
				'default' => 1,
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-items="{{slide_items}}"'],
				'req' => ['enable_slider' => 'true']
			),
			'slide_margin' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 10,
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-margin="{{slide_margin}}"'],
				'req' => [
					'!slide_items' => '1',
					'enable_slider' => 'true'
				]
			),
			'slide_loop' => array(
				'type' => 'checkbox',
				'label' => __pl('loop'),
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-loop="{{slide_loop}}"'],
				'req' => ['enable_slider' => 'true']
			),
			'slide_nav' => array(
				'type' => 'checkbox',
				'label' => __pl('navigation'),
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-nav="{{slide_nav}}"'],
				'req' => ['enable_slider' => 'true']
			),
			'slide_dots' => array(
				'type' => 'checkbox',
				'label' => __pl('bullets'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-dots="{{slide_dots}}"'],
				'req' => ['enable_slider' => 'true']
			),
			'slide_autoplay' => array(
				'type' => 'checkbox',
				'label' => __pl('autoplay'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-autoplay="{{slide_autoplay}}"'],
				'req' => ['enable_slider' => 'true']
			),
			'slide_timeout' => array(
				'type' => 'spinner',
				'label' => __pl('autoplay_timeout'),
				'min' => 1000,
				'step' => 200,
				'max' => 10000,
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-autoplay-timeout="{{slide_timeout}}"'],
				'req' => [
					'slide_autoplay' => 'true',
					'enable_slider' => 'true'
				]
			),
			'slide_hoverpause' => array(
				'type' => 'checkbox',
				'label' => __pl('autoplay_hover_pause'),
				'addAttr' => ['{{element}} .pagelayer-posts-container' => 'data-owl-autoplay-hover-pause="{{slide_hoverpause}}"'],
				'req' => [
					'slide_autoplay' => 'true',
					'enable_slider' => 'true'
				]
			),
		],
		'infinite' => $pagelayer_infinite_style,
		'arrow_styles' => $pagelayer->slider_arrow_styles,
		'pager_styles' => $pagelayer->slider_pager_styles,
		'styles' => [
			'posts_options' => __pl('posts_options'),
			'thumb_style' => __pl('thumb_style'),
			'title_style' => __pl('title_style'),
			'meta_options' => __pl('meta_options'),
			'content_style' => __pl('content_style'),
			'more_style' => __pl('more_style'),
			'btn_border_style' => __pl('border_styles'),
			'slider_options' => __pl('slider_options'),
			'arrow_styles' => __pl('arrow_styles'),
			'pager_styles' => __pl('pager_styles'),
			'infinite' => __pl('infinite_scroll'),
		]
	)
);

// Slides
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_slides', array(
		'name' => __pl('slides'),
		'group' => 'other',
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'prevent_inside' => ['pl_slides'],
		'holder' => '.pagelayer-slides-holder',
		'child_selector' => '>.pagelayer-owl-stage-outer>.pagelayer-owl-stage>.pagelayer-owl-item', // Make it very specifc
		'html' => '<div class="pagelayer-slides-holder pagelayer-owl-holder pagelayer-owl-carousel pagelayer-owl-theme"></div>',
		'params' => array(
			'elements' => array(
				'type' => 'group',
				'label' => __pl('slide'),
				'sc' => PAGELAYER_SC_PREFIX.'_content_slide',
				'item_label' => array(
					'default' => __pl('slide'),
					'param' => 'item'
				),
				'count' => 2,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('slide'))),
			),
			'height' => array(
				'type' => 'slider',
				'label' => __pl('height'),
				'screen' => 1,
				'min' => 1,
				'step' => 1,
				'max' => 1500,
				'units' => ['vh','px','%'],
				'css' => [
					'{{element}} .pagelayer-slide, {{element}} .pagelayer-content-slide' => 'max-height: {{val}}; height:{{val}};',
				],
			),
		),
		'slider_options' => $pagelayer->slider_options,
		'arrow_styles' => $pagelayer->slider_arrow_styles,
		'pager_styles' => $pagelayer->slider_pager_styles,
		'styles' => [
			'slider_options' => __pl('slider_options'),
			'arrow_styles' => __pl('arrow_styles'),
			'pager_styles' => __pl('pager_styles'),
		]
	)
);

// Content Slide
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_content_slide', array(
	'name' => __pl('content_slide'),
	'group' => 'other',
	'has_group' => [
		'section' => 'params', 
		'prop' => 'elements',
	],
	'not_visible' => 1,
	'parent' => [PAGELAYER_SC_PREFIX.'_slides'],
	'holder' => '.pagelayer-content-slide',
	'html' => '<div class="pagelayer-content-slide-bg"></div>
	<div class="pagelayer-slide-bg-overlay"></div>
	<div class="pagelayer-content-slide"></div>',
	'params' => array(
		'elements' => array(
			'type' => 'group',
			'label' => __pl('Inner Row'),
			'sc' => PAGELAYER_SC_PREFIX.'_inner_row',
			'count' => 1,
			'item_label' => array(
				'default' => __pl('Slide Inner Row'),
			),
			'inner_content' => [
				['pl_col' => [
					'atts' => ['ele_padding' => '8%,8%,8%,8%', 'content_pos' => 'center'],
					'inner_content' => [
						['pl_anim_heading' => [ 
							'atts' => ['text' => 'Faster & Easiest way to make a slide  with', 'type' => 'rotating', 'align' => 'center', 'animations' => 'pagelayer-aheading-push', 'color' => '#fff', 'color_type' => 'color', 'rotate_color' => '#ff7a03', 'rotate_text' => 'Drag&Drop,Animations,&More', 'typo' => ',30,,700,,,Solid,,,,']
						]],
						['pl_heading' => [ 
							'atts' => ['text' => '<h4>Slide show Sub-Heading</h4>', 'color' => '#ffffff', 'align' => 'center']
						]],
						['pl_text' => [
							'atts' => ['text' => '<p style="text-align: center; color: rgb(255, 255, 255);">This is the default Slide show content. Feel free to delete it.</p>']
						]],
						['pl_btn' => [
							'atts' => ['align' => 'center', 'type' => 'pagelayer-btn-custom', 'size' => 'pagelayer-btn-mini', 'btn_bg_color' => '', 'btn_color' => '#fff', 'btn_border_type' => 'solid', 'btn_border_color' => '#fff', 'btn_color_hover' => '#fff']
						]],
					]
				]],
			],
			'hide' => 1,
		),
		'type' => array(
			'label' => __pl('background_type'),
			'type' => 'radio',
			'list' => array(
				'' => __pl('color'),
				'image' => __pl('image'),
				'gradient' => __pl('gradient')
			),
		),
		'bg_color' => [
			'type' => 'color',
			'label' => __pl('bg_color'),
			'default' => '#0986c0',
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background: {{val}};'],
			'req' => ['type' => '']
		],
		'gradient' => [
			'type' => 'gradient',
			'label' => '',
			'default' => '150,#44d3f6,23,#72e584,45,#2ca4eb,100',
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],
			'req' => ['type' => 'gradient']
		],
		'img_color' => [
			'type' => 'color',
			'label' => __pl('color'),
			'desc' => __pl('fallback_color'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background: {{val}};'],
			'req' => ['type' => 'image']
		],
		'bg_img' => [
			'type' => 'image',
			'label' => __pl('Image'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background: url("{{{bg_img-url}}}");'],
			'req' => ['type' => 'image']
		],
		'bg_attachment' => [
			'type' => 'select',
			'label' => __pl('ele_bg_attachment'),
			'list' => [
				'' => __pl('default'),
				'scroll' => __pl('scroll'),
				'fixed' => __pl('fixed')
			],
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background-attachment: {{val}};', '{{element}} .pagelayer-content-slide-bg' => 'background-attachment: {{val}};'],
			'req' => ['type' => 'image']
		],
		'bg_posx' => [
			'type' => 'select',
			'label' => __pl('ele_bg_posx'),
			'list' => [
				'' => __pl('default'),
				'center' => __pl('center'),
				'left' => __pl('left'),
				'right' => __pl('right')
			],
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background-position-x: {{val}};'],
			'req' => ['type' => 'image']
		],
		'bg_posy' => [
			'type' => 'select',
			'label' => __pl('ele_bg_posy'),
			'list' => [
				'' => __pl('default'),
				'center' => __pl('center'),
				'top' => __pl('top'),
				'bottom' => __pl('bottom')
			],
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background-position-y: {{val}};'],
			'req' => ['type' => 'image']
		],
		'bg_repeat' => [
			'type' => 'select',
			'label' => __pl('ele_bg_repeat'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background-repeat: {{val}};'],
			'list' => [
				'' => __pl('default'),
				'repeat' => __pl('repeat'),
				'no-repeat' => __pl('no-repeat'),
				'repeat-x' => __pl('repeat-x'),
				'repeat-y' => __pl('repeat-y'),
			],
			'req' => ['type' => 'image']
		],
		'bg_size' => [
			'type' => 'select',
			'label' => __pl('ele_bg_size'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'background-size: {{val}};'],
			'list' => [
				'' => __pl('default'),
				'cover' => __pl('cover'),
				'contain' => __pl('contain')
			],
			'req' => ['type' => 'image']
		],
		'bg_ken_burn' => array(
			'type' => 'checkbox',
			'label' => __pl('ken_burn_effect'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'animation-name: pagelayerKenBurn;'],
			'req' => ['type' => 'image']
		),
		'bg_ken_burn_speed' => array(
			'type' => 'spinner',
			'label' => __pl('ken_burn_speed'),
			'min' => 1,
			'step' => 1,
			'default' => '20',
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'animation-duration:{{val}}s;'],
			'req' => ['!bg_ken_burn' => '', 'type' => 'image'],
		),
		'bg_ken_burn_rev' => array(
			'type' => 'checkbox',
			'label' => __pl('ken_burn_reverse'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'animation-name: pagelayerKenBurnReverse;'],
			'req' => ['!bg_ken_burn' => '', 'type' => 'image'],
		),
		'bg_ken_burn_loop' => array(
			'type' => 'checkbox',
			'label' => __pl('loop_kenburn'),
			'css' => ['{{element}} .pagelayer-content-slide-bg' => 'animation-iteration-count: infinite;'],
			'req' => ['!bg_ken_burn' => '', 'type' => 'image'],
		),
		'bg_overlay' => [
			'type' => 'checkbox',
			'label' => __pl('row_bg_overlay'),
		],
		'bg_overlay_color' => [
			'type' => 'color',
			'label' => __pl('color'),
			'css' => ['{{element}} .pagelayer-slide-bg-overlay' => 'background-color: {{val}}'],
			'req' => ['!bg_overlay' => '']
		],
		'bg_overlay_blend_mode' => [
			'type' => 'select',
			'label' => __pl('blend_mode'),
			'list' => [
				'' => __pl('Normal'),
				'multiply' => __pl('Multiply'),
				'screen' => __pl('Screen'),
				'overlay' => __pl('Overlay'),
				'darken' => __pl('Darken'),
				'lighten' => __pl('Lighten'),
				'color-dodge' => __pl('Color Dodge'),
				'color-burn' => __pl('Color Burn'),
				'hue' => __pl('Hue'),
				'saturation' => __pl('Saturation'),
				'color' => __pl('Color'),
				'exclusion' => __pl('Exclusion'),
				'luminosity' => __pl('Luminosity'),
			],
			'css' => ['{{element}} .pagelayer-slide-bg-overlay' => 'mix-blend-mode:{{val}};'],
			'req' => ['!bg_overlay' => ''],
		],
		
	),
));

// Slide, for backward compatibility of slides child
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_slide', array(
		'name' => __pl('slide'),
		'group' => 'other',
		'holder' => '.pagelayer-slide-btns',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_slides'],
		'html' => '<div class="pagelayer-slide-holder">
			<div if="{{heading}}" class="pagelayer-slide-heading">{{heading}}</div>
			<div if="{{sub}}" class="pagelayer-slide-sub">{{sub}}</div>
			<div if="{{desc}}" class="pagelayer-slide-desc">{{desc}}</div>
			<div class="pagelayer-slide-btns"></div>
		</div>',
		//'innerHTML' => 'desc',
		'has_group' => [
			'section' => 'btn_style', 
			'prop' => 'elements'
		],
		'params' => array(
			'type' => array(
				'label' => __pl('background_type'),
				'type' => 'radio',
				'default' => 'color',
				'list' => array(
					'color' => __pl('color'),
					'image' => __pl('image'),
					'gradient' => __pl('gradient')
				)
			),
			'color' => [
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#0986c0',
				'css' => 'background: {{val}};',
				'req' => ['type' => 'color']
			],
			'gradient' => [
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6,23,#72e584,45,#2ca4eb,100',
				'css' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);',
				'req' => ['type' => 'gradient']
			],
			'img_color' => [
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '',
				'desc' => __pl('fallback_color'),
				'css' => 'background: {{val}};',
				'req' => ['type' => 'image']
			],
			'bg_img' => [
				'type' => 'image',
				'label' => __pl('Image'),
				'css' => 'background: url("{{{bg_img-url}}}");',
				'req' => ['type' => 'image']
			],
			'bg_attachment' => [
				'type' => 'select',
				'label' => __pl('ele_bg_attachment'),
				'list' => [
					'' => __pl('default'),
					'scroll' => __pl('scroll'),
					'fixed' => __pl('fixed')
				],
				'css' => 'background-attachment: {{val}};',
				'req' => ['type' => 'image']
			],
			'bg_posx' => [
				'type' => 'select',
				'label' => __pl('ele_bg_posx'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'left' => __pl('left'),
					'right' => __pl('right')
				],
				'css' => 'background-position-x: {{val}};',
				'req' => ['type' => 'image']
			],
			'bg_posy' => [
				'type' => 'select',
				'label' => __pl('ele_bg_posy'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'top' => __pl('top'),
					'bottom' => __pl('bottom')
				],
				'css' => 'background-position-y: {{val}};',
				'req' => ['type' => 'image']
			],
			'bg_repeat' => [
				'type' => 'select',
				'label' => __pl('ele_bg_repeat'),
				'css' => 'background-repeat: {{val}};',
				'list' => [
					'' => __pl('default'),
					'repeat' => __pl('repeat'),
					'no-repeat' => __pl('no-repeat'),
					'repeat-x' => __pl('repeat-x'),
					'repeat-y' => __pl('repeat-y'),
				],
				'req' => ['type' => 'image']
			],
			'bg_size' => [
				'type' => 'select',
				'label' => __pl('ele_bg_size'),
				'css' => 'background-size: {{val}};',
				'list' => [
					'' => __pl('default'),
					'cover' => __pl('cover'),
					'contain' => __pl('contain')
				],
				'req' => ['type' => 'image']
			],
		),
		'content_box_style' => [
			'content_width' => array(
				'type' => 'spinner',
				'label' => __pl('width'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 80,
				'css' => ['{{element}} .pagelayer-slide-holder' => 'width: {{val}}%'],
			),
			'content_posx' => array(
				'type' => 'slider',
				'label' => __pl('horizontal_pos'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 50,
				'css' => ['{{element}} .pagelayer-slide-holder' => 'left: {{val}}%;'],
			),
			'content_posy' => array(
				'type' => 'slider',
				'label' => __pl('verticle_postion'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 50,
				'css' => ['{{element}} .pagelayer-slide-holder' => 'top: {{val}}%; transform: translate(-{{content_posx}}%, -{{val}}%);'],
			),
			'content_bg' => [
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#46494a98',
				'css' => ['{{element}} .pagelayer-slide-holder' => 'background: {{val}}'],
			],
			'content_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'screen' => 1,
				'default' => '50,50,50,50',
				'css' => ['{{element}} .pagelayer-slide-holder' => 'padding-top:{{val[0]}}px; padding-right:{{val[1]}}px; padding-bottom:{{val[2]}}px; padding-left:{{val[3]}}px'],
			),
			'align' => array(
				'label' => __pl('alignment'),
				'type' => 'radio',
				'default' => 'center',
				'css' => ['{{element}} .pagelayer-slide-holder' => 'text-align:{{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				)
			),
		],
		'heading_style' => [
			'heading' => array(
				'type' => 'text',
				'label' => __pl('heading_style'),
				'default' => __pl('This is Heading'),
				'edit' => '.pagelayer-slide-heading',
			),
			'heading_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-slide-heading' => 'color: {{val}};'],
			),
			'heading_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',44,,700,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-slide-heading' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'heading_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'default' => 0,
				'max' => 200,
				'css' => ['{{element}} .pagelayer-slide-heading' => 'padding-bottom: {{val}}px'],
			),
		],
		'sub_style' => [
			'sub' => array(
				'type' => 'textarea',
				'label' => __pl('sub_style'),
				'default' => __pl('This is Sub-Heading'),
				'edit' => '.pagelayer-slide-sub',
			),
			'sub_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-slide-sub' => 'color: {{val}};'],
			),
			'sub_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',26,,,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-slide-sub' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'sub_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'default' => 20,
				'max' => 200,
				'css' => ['{{element}} .pagelayer-slide-sub' => 'padding-bottom: {{val}}px'],
			),
		],
		'btn_style' => [
			'elements' => array(
				'type' => 'group',
				'label' => __pl('buttons'),
				'sc' => PAGELAYER_SC_PREFIX.'_btn',
				'item_label' => array(
					'default' => __pl('button'),
					'param' => 'text'
				),
				'count' => 2,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('button_name'))),
			),
			'btn_space' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'max' => 100,
				'default' => 10,
				'css' => ['{{element}} .pagelayer-btn' => 'padding-right: {{val}}px;'],
			)
		],
		'desc_style' => [
			'desc' => array(
				'type' => 'textarea',
				'label' => __pl('desc_style'),
				'default' => __pl('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer ac leo ut arcu dictum viverra at eu magna.'),
				'edit' => '.pagelayer-slide-desc',
			),
			'desc_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-slide-desc' => 'color: {{val}};'],
			),
			'desc_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',15,,,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-slide-desc' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'desc_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'screen' => 1,
				'min' => 0,
				'step' => 1,
				'default' => 10,
				'max' => 200,
				'css' => ['{{element}} .pagelayer-slide-desc' => 'padding-bottom: {{val}}px'],
			),
		],
		'styles' => [
			'content_box_style' => __pl('content_box_style'),
			'heading_style' => __pl('heading_style'),
			'sub_style' => __pl('sub_style'),
			'desc_style' => __pl('desc_style'),
			'btn_style' => __pl('btn_style'),
		]
	)
);

// Author Box
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_author_box', array(
		'name' => __pl('author_box'),
		'group' => 'archive',
		'html' => '<div class="pagelayer-author-box-div pagelayer-author-box-holder pagelayer-layout-{{layout}}">
					<div class="pagelayer-author-profile-pic" if={{author_picture}}>
						<a if-ext={{show_website}} href="{{{author_website}}}" class="pagelayer-link-sel">
							<img class="pagelayer-img pagelayer-author-image" src="{{{avatar-url}}}" title="{{{avatar-title}}}" alt="{{{avatar-alt}}}" />
						</a>
					</div>
					<div class="pagelayer-author-profile-desc">
						<a href="{{{author_website}}}" if-ext={{show_website}}>
							<div class="pagelayer-author-title" if={{show_name}}>{{display_html}}</div>
						</a>
						<p class="pagelayer-author-bio" if={{show_bio}}>{{description}}</p>
						<a href="{{user_url}}" class="pagelayer-author-btn {{type}} pagelayer-button {{size}} pagelayer-ele-link" if={{archive_btn}}>{{archive_btn_txt}}</a>
						<a href="{{{author_website}}}" class="pagelayer-author-btn {{type}} pagelayer-button {{size}} pagelayer-ele-link" if={{show_as_button}}>{{btn_txt}}</a>
					</div>
				</div>',
		'params' => array(
			'box_source' => array(
				'type' => 'select',
				'label' => __pl('box_source_type'),
				'default' => 'current',
				'list' => array(
					'current' => __pl('current_author'),
					'custom' => __pl('custom'),
				),
			),
			'avatar' => array(
				'type' => 'image',
				'label' => __pl('custom_image'),
				'default' => PAGELAYER_URL.'/images/default-image.png',
				'req' => ['box_source' => 'custom'],	
			),
			'author_picture' => array(
				'type' => 'checkbox',
				'label' => __pl('author_picture'),
				'default' => true,				
			),
			'show_name' => array(
				'type' => 'checkbox',
				'label' => __pl('show_name'),
				'default' => true,
			),
			'display_name' => array(
				'type' => 'text',
				'label' => __pl('display_name'),
				'req' => ['box_source' => 'custom', 'show_name' => 'true' ],
				'default' => 'Author',
			),
			'name_style' => array(
				'type' => 'select',
				'label' => __pl('name_style'),
				'default' => 'h4',
				'list' => array(
					'h1' => __pl('H1'),
					'h2' => __pl('H2'),
					'h3' => __pl('H3'),
					'h4' => __pl('H4'),
					'h5' => __pl('H5'),
					'h6' => __pl('H6'),
				),
			),
			'user_url' => array(
				'type' => 'select',
				'label' => __pl('link'),
				'default' => 'none',
				'list' => array(
					'none' => __pl('none'),
					'archives' => __pl('archives'),
				),
				'req' => ['box_source' => 'current'],	
			),
			'show_website' => array(
				'type' => 'checkbox',
				'label' => __pl('show_site'),
				'default' => '',	
			),
			'author_website' => array(
				'type' => 'link',
				'label' => __pl('author_website'),
				'selector' => '.pagelayer-link-sel',
				'req' => ['show_website' => 'true'],
			),
			'show_as_button' => array(
				'type' => 'checkbox',
				'label' => __pl('show_website_btn'),
				'default' => '',
				'req' => ['show_website' => 'true'],
			),
			'btn_txt' => array(
				'type' => 'text',
				'label' => __pl('button_text'),
				'default' => 'Website',
				'req' => array(
					'show_as_button' => 'true',
					'show_website' => 'true'
				),
			),
			'show_bio' => array(
				'type' => 'checkbox',
				'label' => __pl('show_bio'),
				'req' => ['box_source' => 'custom'],
				'default' => 'true',
			),
			'description' => array(
				'type' => 'textarea',
				'label' => __pl('description'),
				'default' => 'This is my Bio!',
				'req' => array(
					'box_source' => 'custom',
					'show_bio' => 'true'
				),
			),
			'archive_btn' => array(
				'type' => 'checkbox',
				'label' => __pl('show_archive_btn'),
				'default' => '',
				'req' => ['box_source' => 'current'],
			),
			'archive_btn_txt' => array(
				'type' => 'text',
				'label' => __pl('archive_btn_txt'),
				'default' => 'All Post',
				'req' => array(
					'archive_btn' => 'true',
					'box_source' => 'current'
				),
			),
			'layout' => array(
				'type' => 'radio',
				'label' => __pl('layout'),
				'default' => 'left',
				//'css' => ['{{element}} .pagelayer-author-profile-pic' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				)
			),
			'alignment' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'left',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-profile-desc' => 'text-align: {{val}}',
				'{{element}} .pagelayer-author-profile-pic' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				)
			),
		),
		'image_style' => [
			'image_size' => array(
				'type' => 'slider',
				'label' => __pl('image_size'),
				'step' => 1,
				'max' => 200,
				'min' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-image' => 'height: {{val}}px; width: {{val}}px;'],
			),
			'image_gap' => array(
				'type' => 'slider',
				'label' => __pl('gap'),
				'step' => 1,
				'max' => 100,
				'min' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-profile-pic' => 'padding-right: {{val}}px;'],
			),
			'image_border' => array(
				'type' => 'checkbox',
				'label' => __pl('border'),
				'default' => '',
			),
			'image_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'req' => ['image_border' => 'true'],
				'css' => ['{{element}} .pagelayer-author-image' => 'border-color: {{val}};'],
			),
			'image_border_width' => array(
				'type' => 'slider',
				'label' => __pl('border_width'),
				'max' => 20,
				'step' => 1,
				'min' => 0,
				'screen' => 1,
				'req' => ['image_border' => 'true'],
				'css' => ['{{element}} .pagelayer-author-image' => 'border-width: {{val}}px;'],
			),
			'image_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-author-image' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'req' => ['image_border' => 'true'],
			),
			'image_border_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'step' => 1,
				'max' => 100,
				'min' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-image' => 'border-radius: {{val}}px;'],
			),
			'image_border_shadow' => array(
				'type' => 'box_shadow',
				'label' => __pl('shadow'),
				'css' => ['{{element}} .pagelayer-author-image' =>'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[4]}}px {{val[3]}} {{val[5]}} !important;'],
			)
		],
		'text_style' => [
			'name_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-author-title *' => 'color: {{val}};'],
			),
			'name_typography' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-author-title *' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				'{{element}} .pagelayer-author-title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'title_gap' => array(
				'type' => 'slider',
				'label' => __pl('gap'),
				'step' => 1,
				'max' => 100,
				'min' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-title' => 'padding-bottom: {{val}}px;'],
			),
			'bio_color' => array(
				'type' => 'color',
				'label' => __pl('bio_color'),
				'css' => ['{{element}} .pagelayer-author-bio' => 'color: {{val}};'],
			),
			'bio_typography' => array(
				'type' => 'typography',
				'label' => __pl('bio_typo'),
				'css' => ['{{element}} .pagelayer-author-bio *' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				'{{element}} .pagelayer-author-bio' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'bio_gap' => array(
				'type' => 'slider',
				'label' => __pl('bio_gap'),
				'step' => 1,
				'max' => 100,
				'min' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-bio' => 'padding-bottom: {{val}}px;'],
			),
		],
		'button_style' => [
			'type' => array(
				'type' => 'select',
				'label' => __pl('button_type_label'),
				'default' => 'pagelayer-btn-default',
				//'addClass' => ['{{element}} .pagelayer-btn-holder' => '{{val}}'],
				'list' => array(
					'pagelayer-btn-default' => __pl('btn_type_default'),
					'pagelayer-btn-primary' => __pl('btn_type_primary'),
					'pagelayer-btn-secondary' => __pl('btn_type_secondary'),
					'pagelayer-btn-success' => __pl('btn_type_success'),
					'pagelayer-btn-info' => __pl('btn_type_info'),
					'pagelayer-btn-warning' => __pl('btn_type_warning'),
					'pagelayer-btn-danger' => __pl('btn_type_danger'),
					'pagelayer-btn-dark' => __pl('btn_type_dark'),
					'pagelayer-btn-light' => __pl('btn_type_light'),
					'pagelayer-btn-link' => __pl('btn_type_link'),
					'pagelayer-btn-custom' => __pl('btn_type_custom')
				),
			),
			'size' => array(
				'type' => 'select',
				'label' => __pl('button_size_label'),
				'default' => 'pagelayer-btn-large',
				'list' => array(
					'pagelayer-btn-mini' => __pl('mini'),
					'pagelayer-btn-small' => __pl('small'),
					'pagelayer-btn-large' => __pl('large'),
					'pagelayer-btn-extra-large' => __pl('extra_large'),
					'pagelayer-btn-double-large' => __pl('double_large'),
					'pagelayer-btn-custom' => __pl('custom'),
				)
			),
			'btn_custom_size' => array(
				'type' => 'spinner',
				'label' => __pl('btn_custom_size'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 5,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-btn' => 'padding: calc({{val}}px / 2) {{val}}px;'],
				'req' => array(
					'size' => 'pagelayer-btn-custom'
				),
			),
			'btn_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_label'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-author-btn' => 'background-color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_color' => array(
				'type' => 'color',
				'label' => __pl('btn_color_label'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-author-btn' => 'color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('btn_hover_delay_label'),
				'desc' => __pl('btn_hover_delay_desc'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-author-btn' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-author-btn:hover' => 'background-color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-author-btn:hover' => 'color: {{val}};'],
				'req' => array(
					'type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bor_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),	
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-author-btn' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-author-btn' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['{{element}} .pagelayer-author-btn' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type' => ''
				],
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'css' => ['{{element}} .pagelayer-author-btn' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-author-btn:hover' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color_hover_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-author-btn:hover' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width_hover'),
				'css' => ['{{element}} .pagelayer-author-btn:hover' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type_hover' => ''
				],
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius_hover'),
				'css' => ['{{element}} .pagelayer-author-btn:hover' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_gap' => array(
				'type' => 'slider',
				'label' => __pl('btn_gap'),
				'step' => 1,
				'max' => 100,
				'min' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-author-btn' => 'margin-bottom: {{val}}px;'],
			),
		],
		'styles' => [
			'image_style' => __pl('image'),
			'text_style' => __pl('text'),
			'button_style' => __pl('button'),
		]		
	)
);

// Login
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_login', array(
		'name' => __pl('login'),
		'group' => 'other',
		'html' => '<div class="pagelayer-login-div pagelayer-login-holder" >
					<form class="pagelayer-login-form" onsubmit="return pagelayer_login_submit(this, event)" method="POST">
						<div>
							<label if={{show_label}} class="pagelayer-login-username">{{custom_label_username}}</label>
							<input type="text" name="username" id="username" placeholder="{{custom_placeholder_login}}" class="pagelayer-login-input-field" spellcheck="false"/>
						</div>
						<div class="user-pass-wrap">
							<label if={{show_label}} class="pagelayer-login-password">{{custom_label_password}}</label>
							<input type="password" name="password" id="password" placeholder="{{custom_placeholder_password}}" class="pagelayer-login-input-field" spellcheck="false"/>
						</div>
						<div class="pagelayer-login-cap" if="{{login_cap}}">{{login_cap}}</div>
						<div>
							<input type="checkbox" name="remember_me" if="{{remember_me}}" class="pagelayer-rememberMe"/><label if="{{remember_me}}" class="pagelayer-rememberMe">Remember Me</label>
						</div>
						<div class="wrapper pagelayer-login-btn-{{alignment}} pagelayer-login-wrappers">
							<input type="hidden" name="login_url" value="{{redirect_url}}" if="{{redirect_login}}" />
							<input type="hidden" name="logout_url" value="{{logout_url}}" if="{{redirect_logout}}" />
							<button name="submit" class="pagelayer-ele-link pagelayer-login-btn {{btn_type}} pagelayer-button {{btn_size}}">{{login_text}}</button>
						</div>
						<div class="pagelayer-login-wrappers">
						<a if="{{lost_pass}}" href="'. esc_url( wp_lostpassword_url( get_permalink() ) ) .'" class="pagelayer-ele-link">Lost your Password?</a>
						</div>
					</form>
					<div class="pagelayer-login-error-box"></div>
				</div>
				<div class="pagelayer-logout-txt" if={{display_logouttxt}}>{{display_logouttxt}}</div>',
		'params' => array(
			'show_label' => array(
				'type' => 'checkbox',
				'label' => __pl('show_label'),
				'default' => 'true'
			),
			'remember_me' => array(
				'type' => 'checkbox',
				'label' => __pl('remember_me'),
				'default' => 'true'
			),
			'lost_pass' => array(
				'type' => 'checkbox',
				'label' => __pl('lost_pass'),
				'default' => 'true'
			),
			'custom_label' => array(
				'type' => 'checkbox',
				'label' => __pl('custom_label'),
				'default' => '',
			),
			'custom_label_username' => array(
				'type' => 'text',
				'label' => __pl('custom_label_username'),
				'default' => 'Username',
				'show' => ['custom_label' => 'true'],
				'edit' => '.pagelayer-login-username',
			),
			'custom_placeholder_login' => array(
				'type' => 'text',
				'label' => __pl('custom_placeholder_login'),
				'default' => 'Username',
				'show' => ['custom_label' => 'true'],
			),
			'custom_label_password' => array(
				'type' => 'text',
				'label' => __pl('custom_label_password'),
				'default' => 'Password',
				'show' => ['custom_label' => 'true'],
				'edit' => '.pagelayer-login-password',
			),
			'custom_placeholder_password' => array(
				'type' => 'text',
				'label' => __pl('custom_placeholder_password'),
				'default' => 'Password',
				'show' => ['custom_label' => 'true'],
			),
			'redirect_login' => array(
				'type' => 'checkbox',
				'label' => __pl('redirect_login'),
				'default' => '',
			),
			'redirect_url' => array(
				'type' => 'text',
				'label' => __pl('login_redirect_url'),
				'desc' => __pl('redirect_url_desc'),
				'default' => '',
				'req' => ['redirect_login' => 'true'],
			),
			'redirect_logout' => array(
				'type' => 'checkbox',
				'label' => __pl('redirect_logout'),
				'default' => '',
			),
			'logout_url' => array(
				'type' => 'text',
				'label' => __pl('logout_redirect_url'),
				'desc' => __pl('redirect_url_desc'),
				'default' => '',
				'req' => ['redirect_logout' => 'true'],
			),
		),
		'form_style' => array(
			'row_gap' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'step' => 1,
				'screen' => 1,
				'max' => 60,
				'css' => ['{{element}} .pagelayer-login-div input, .pagelayer-login-div .pagelayer-login-wrappers' => 'margin-bottom: {{val}}px;'],
			),
			'alignment' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'screen' => 1,
				'default' => 'left',
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'css' => ['{{element}} .pagelayer-login-div, .pagelayer-login-input-field' => 'text-align: {{val}};'],
				'addClass' => ['{{element}} .pagelayer-login-input-field' => 'pagelayer-login-input-{{val}}']
			),
			'link_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'css' => ['{{element}} .pagelayer-login-form a' => 'color: {{val}};'],
			),
			'link_color_hover' => array(
				'type' => 'color',
				'label' => __pl('link_color_hover'),
				'css' => ['{{element}} .pagelayer-login-form a:hover' => 'color: {{val}};'],
			),
		),
		'label_style' => array(
			'label_gap' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'step' => 1,
				'screen' => 1,
				'max' => 60,
				'css' => ['{{element}} .pagelayer-login-div label' => 'margin-bottom: {{val}}px;'],
			),
			'text_color' => array(
				'type' => 'color',
				'label' => __pl('text_color'),
				'css' => ['{{element}} .pagelayer-login-form label, {{element}} .pagelayer-login-cap' => 'color: {{val}};'],
			),
			'label_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-login-form label, {{element}} .pagelayer-login-cap' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		),
		'fields_style' => array(
			'field_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'desc' => __pl('login_widget_width_desc'),
				'min' => 10,
				'max' => 100,
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}}  .pagelayer-login-form .pagelayer-login-input-field' => 'width: {{val}}%;']
			),
			'field_color' => array(
				'type' => 'color',
				'label' => __pl('text_color'),
				'css' => ['{{element}} .pagelayer-login-form .pagelayer-login-input-field, {{element}} .pagelayer-login-cap input' => 'color: {{val}};', '{{element}} .pagelayer-login-form .pagelayer-login-input-field::selection' => 'color:white; background-color: {{val}};'],
			),
			'field_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-login-form input' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'field_bgcolor' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-login-form .pagelayer-login-input-field, {{element}} .pagelayer-login-cap input' => 'background-color: {{val}};'],
			),
			'field_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-login-form .pagelayer-login-input-field, {{element}} .pagelayer-login-cap input' => 'border-color: {{val}};'],
			),
			'field_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-login-form .pagelayer-login-input-field, {{element}} .pagelayer-login-cap input' => 'border-width: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
			),
			'field_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-login-form .pagelayer-login-input-field, {{element}} .pagelayer-login-cap input' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
			),
		),
		'buttons_style' => array(
			'login_text' => array(
				'type' => 'text',
				'label' => __pl('text'),
				'default' => 'Log In',
				'edit' => '.pagelayer-login-btn',
			),
			'btn_stretch' => array(
				'type' => 'checkbox',
				'label' => __pl('stretch'),
				'css' => ['{{element}} .pagelayer-login-btn' => 'width:100%;'],
			),
			'btn_gap' => array(
				'type' => 'padding',
				'label' => __pl('spacing'),
				'step' => 1,
				'screen' => 1,
				'max' => 60,
				'default' => '10',
				'css' => ['{{element}} .pagelayer-login-btn' => 'margin-top: {{val[0]}}px; margin-right: {{val[1]}}px; margin-bottom: {{val[2]}}px; margin-left: {{val[3]}}px'],
			),
			'btn_type' => array(
				'type' => 'select',
				'label' => __pl('button_type_label'),
				'default' => 'pagelayer-btn-default',
				//'addClass' => ['{{element}} .pagelayer-btn-holder' => '{{val}}'],
				'list' => array(
					'pagelayer-btn-default' => __pl('btn_type_default'),
					'pagelayer-btn-primary' => __pl('btn_type_primary'),
					'pagelayer-btn-secondary' => __pl('btn_type_secondary'),
					'pagelayer-btn-success' => __pl('btn_type_success'),
					'pagelayer-btn-info' => __pl('btn_type_info'),
					'pagelayer-btn-warning' => __pl('btn_type_warning'),
					'pagelayer-btn-danger' => __pl('btn_type_danger'),
					'pagelayer-btn-dark' => __pl('btn_type_dark'),
					'pagelayer-btn-light' => __pl('btn_type_light'),
					'pagelayer-btn-link' => __pl('btn_type_link'),
					'pagelayer-btn-custom' => __pl('btn_type_custom')
				),
			),
			'btn_size' => array(
				'type' => 'select',
				'label' => __pl('button_size_label'),
				'default' => 'pagelayer-btn-large',
				'list' => array(
					'pagelayer-btn-mini' => __pl('mini'),
					'pagelayer-btn-small' => __pl('small'),
					'pagelayer-btn-large' => __pl('large'),
					'pagelayer-btn-extra-large' => __pl('extra_large'),
					'pagelayer-btn-double-large' => __pl('double_large'),
					'pagelayer-btn-custom' => __pl('custom'),
				)
			),
			'btn_custom_size' => array(
				'type' => 'spinner',
				'label' => __pl('btn_custom_size'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'screen' => 1,
				'default' => 5,
				'css' => ['{{element}} .pagelayer-login-btn' => 'padding: calc({{val}}px / 2) {{val}}px;'],
				'req' => array(
					'btn_size' => 'pagelayer-btn-custom'
				),
			),
			'btn_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_label'),
				'default' => '#0986c0',
				'css' => ['{{element}} .pagelayer-login-btn' => 'background-color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_color' => array(
				'type' => 'color',
				'label' => __pl('btn_color_label'),
				'default' => '#ffffff',
				'css' => ['{{element}} .pagelayer-login-btn' => 'color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => ''
				),
			),
			'btn_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('btn_hover_delay_label'),
				'desc' => __pl('btn_hover_delay_desc'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-login-btn' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_bg_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-login-btn:hover' => 'background-color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_color_hover' => array(
				'type' => 'color',
				'label' => __pl('btn_color_hover_label'),
				'default' => '',
				'css' => ['{{element}} .pagelayer-login-btn:hover' => 'color: {{val}};'],
				'req' => array(
					'btn_type' => 'pagelayer-btn-custom',
				),
				'show' => array(
					'btn_hover' => 'hover'
				),
			),
			'btn_bor_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => '',
				//'no_val' => 1,// Dont set any value to element
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				)
			),	
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-login-btn' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-login-btn' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-login-btn' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type' => ''
				],
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-login-btn' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type' => ''
				),
				'show' => array(
					'btn_bor_hover' => ''
				),
			),
			'btn_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-login-btn:hover' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color_hover_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-login-btn:hover' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width_hover'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-login-btn:hover' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type_hover' => ''
				],
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
			'btn_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius_hover'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-login-btn:hover' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type_hover' => ''
				),
				'show' => array(
					'btn_bor_hover' => 'hover'
				),
			),
		),
		'styles' => [
			'form_style' => __pl('Form'),
			'label_style' => __pl('Label'),
			'fields_style' => __pl('Fields'),
			'buttons_style' => __pl('Button'),
		]		
	)
);

// SiteMap
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_sitemap', array(
		'name' => __pl('sitemap'),
		'group' => 'other',
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'holder' => '.pagelayer-sitemap-div-holder',
		'html' => '<div class="pagelayer-sitemap-div pagelayer-sitemap-holder">
				<div class="pagelayer-sitemap-div-holder pagelayer-sitemap-flex-wrapper">
				</div>
			</div>',
		'params' => array(
			'elements' => array(
				'type' => 'group',
				'label' => __pl('Label'),
				'sc' => PAGELAYER_SC_PREFIX.'_sitemap_item',
				'item_label' => array(
					'default' => __pl('Label'),
					'param' => 'title'
				),
				'count' => 1,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('sitemap_item'))),
			),
			'columns' => array(
				'type' => 'select',
				'label' => __pl('columns'),
				'default' => '2',
				'list' => array(
					'1' => __pl('1'),
					'2' => __pl('2'),
					'3' => __pl('3'),
					'4' => __pl('4'),
					'5' => __pl('5'),
					'6' => __pl('6'),
				),
				'css' => [ '{{element}} .pagelayer-sitemap-div-holder > *' => 'flex-basis: calc( 1 / {{val}} * 100% );' ],
			),
			'nofollow' => array(
				'type' => 'checkbox',
				'label' => __pl('nofollow'),
				'default' => '',
				'addAttr' => ['{{element}} .pagelayer-sitemap-list-item a' => 'rel="nofollow"',
				'{{element}} .pagelayer-sitemap-div-holder' => 'data-nofollow="true"'],
			),
		),
		'additional_opt' => [
			'protected' => array(
				'type' => 'checkbox',
				'label' => __pl('protected_post'),
				'default' => '',
			),
		],
		'list_styles' => [
			'indent' => array(
				'type' => 'slider',
				'label' => __pl('indent'),
				'default' => 0,
				'min' => 0,				
				'max' => 100,
				'css' => ['{{element}} .pagelayer-sitemap-section li' => 'margin-left: {{val}}px'],
			),
			'padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-sitemap-section' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px'],
			),
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('title_color'),
				'css' => ['{{element}} .pagelayer-sitemap-section span' => 'color: {{val}}'],
			),
			'title_typography' => array(
				'type' => 'typography',
				'label' => __pl('title_typography'),
				'css' => ['{{element}} .pagelayer-sitemap-section span' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'list_color' => array(
				'type' => 'color',
				'label' => __pl('list_color'),
				'css' => ['{{element}} .pagelayer-sitemap-section li' => 'color: {{val}}',
				'{{element}} .pagelayer-sitemap-section a' => 'color: {{val}}'],
			),
			'list_typography' => array(
				'type' => 'typography',
				'label' => __pl('list_typography'),
				'css' => ['{{element}} .pagelayer-sitemap-section li, {{element}} .pagelayer-sitemap-section a' =>'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'list_type' => array(
				'type' => 'radio',
				'label' => __pl('list_type'),
				'default' => 'disc',
				'list' => array(
					'disc' => __pl('sitemap_disc'),
					'circle' => __pl('sitemap_circle'),
					'square' => __pl('sitemap_square'),
					'none' => __pl('sitemap_none')
				),
				'css' => ['{{element}} .pagelayer-sitemap-section li' => 'list-style-type: {{val}}'],
			),
		],
		'styles' => [
			'additional_opt' => __pl('additional_option'),
			'list_styles' => __pl('list_option'),
		]
		
		
	)
);

// SiteMap Builder
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_sitemap_item', array(
		'name' => __pl('sitemap_item'),
		'group' => 'other',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_sitemap'],
		'html' => '{{sitemap_html}}',
		'params' => array(
			'sitemap_type' => array(
				'type' => 'select',
				'label' => __pl('type'),
				'default' => 'post_type',
				'list' => array(
					'post_type' => __pl('post_type'),
					'taxonomy' => __pl('taxonomy'),
				)
			),
			'source_post' => array(
				'type' => 'select',
				'label' => __pl('source'),
				'default' => 'page',
				'list' => array(
					'post' => __pl('Posts'),
					'page' => __pl('Pages'),
				),
				'show' => array(
					'sitemap_type' => 'post_type',
				),
			),
			'source_taxonomy' => array(
				'type' => 'select',
				'label' => __pl('source'),
				'default' => 'category',
				'list' => array(
					'category' => __pl('categories'),
					'post_tag' => __pl('tags'),
					'post_format' => __pl('format'),
				),
				'show' => array(
					'sitemap_type' => 'taxonomy'
				),
			),
			'title' => array(
				'type' => 'text',
				'label' => __pl('title'),
				'default' => '',
			),
			'order_post' => array(
				'type' => 'select',
				'label' => __pl('order_by'),
				'default' => 'random',
				'list' => array(
					'post_date' => __pl('date'),
					'post_title' => __pl('title'),
					'menu_order' => __pl('menu_order'),
					'rand' => __pl('random'),
				),
				'show' => array(
					'sitemap_type' => 'post_type',
				),
			),
			'order_taxonomy' => array(
				'type' => 'select',
				'label' => __pl('order_by'),
				'default' => 'name',
				'list' => array(
					'ID' => __pl('id'),
					'name' => __pl('name'),
				),
				'show' => array(
					'sitemap_type' => 'taxonomy'
				),
			),
			'order' => array(
				'type' => 'select',
				'label' => __pl('order'),
				'default' => 'DESC',
				'list' => array(
					'ASC' => __pl('asc'),
					'DESC' => __pl('desc'),
				)
			),
			'hide_empty' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_empty'),
				'default' => true,
				'show' => array(
					'sitemap_type' => 'taxonomy'
				),
			),
			'hierarchical' => array(
				'type' => 'checkbox',
				'label' => __pl('hierarchical_view'),
				'default' => '',
			),
			'depth' => array(
				'type' => 'select',
				'label' => __pl('depth'),
				'list' => array(
					'0' => __pl('all'),
					'1' => __pl('1'),
					'2' => __pl('2'),
					'3' => __pl('3'),
					'4' => __pl('4'),
					'5' => __pl('5'),
					'6' => __pl('6'),
				),
				'show' => array(
					'hierarchical' => 'true'
				),
			),			
		),
	)
);

// Search Form
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_search', array(
		'name' => __pl('search_form'),
		'group' => 'other',
		'html' => '<div class="pagelayer-search-container pagelayer-search-{{type}}">
			<button class="pagelayer-search-toggle pagelayer-search-button">
				<span if="{{button_text}}" class="pagelayer-search-submit-label">{{button_text}}</span>					
				<i if="{{button_icon}}" class="pagelayer-search-submit-icon {{button_icon}}"></i>
			</button>
			<form role="search" method="get" class="pagelayer-searchform" action="'.esc_url( home_url( '/' ) ).'">
				<div class="pagelayer-search-fields">
					<input class="pagelayer-search-input" type="text" value="" name="s" placeholder="{{{placeholder}}}" />
					<input type="hidden" if="{{post_type}}" value="{{post_type}}" name="post_type"/>
					<button type="submit" class="pagelayer-search-submit pagelayer-search-button">
						<span if="{{button_text}}" class="pagelayer-search-submit-label">{{button_text}}</span>					
						<i if="{{button_icon}}" class="pagelayer-search-submit-icon {{button_icon}}"></i>					
					</button>
				</div>
			</form> 
		</div>',
		'params' => array(
			'post_type' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_post_type_label'),
				'desc' => __pl('posts_grid_post_type_desc'),
				'list' => array_merge(['' => __pl('All Post Types')], pagelayer_post_types(true))
			),
			'type' => array(
				'type' => 'select',
				'label' => __pl('form_type'),
				'default' => 'classic',
				'list' => array(
					'classic' => __pl('classic'),
					'full-screen' => __pl('full_screen'),
				),
			),
			'placeholder' => array(
				'type' => 'text',
				'label' => __pl('placeholder'),
				'default' => __pl('search'),
			),
			'button_type' => array(
				'type' => 'radio',
				'label' => __pl('button_type'),
				'default' =>  'icon',
				'list' => array(
					'icon' =>  __pl('list_icon_label'),
					'text' =>  __pl('text'),
				),
			),
			'button_icon' => array(
				'type' => 'icon',
				'label' => __pl('button_icon'),
				'default' => 'fas fa-search',
				'req' => ['button_type' => 'icon']
			),
			'button_text' => array(
				'type' => 'text',
				'label' => __pl('button_text'),
				'default' =>  __pl('search'),
				'req' => ['button_type' => 'text']
			),
			'field-width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-fields'  => 'max-width:{{val}}%;'],
				'req' => ['type' => 'classic'],
			),
			'size' => array(
				'type' => 'slider',
				'label' => __pl('min_height'),
				'default' => 40,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-fields, {{element}} .pagelayer-search-full-screen input'  => 'min-height:{{val}}px;',
				'{{element}} .pagelayer-search-fields input'  => 'padding-left: calc({{val}}px / 3);padding-right: calc({{val}}px / 3);',
				'{{element}} .pagelayer-search-fields button'  => 'padding-left: calc({{val}}px / 3)'],
				
			),
			'field_align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'center',
				'screen' => 1,
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'css' => ['{{element}} .pagelayer-search-fields'  => 'margin:auto;margin-{{val}}:0;'],
				'req' => ['type' => 'classic'],
			),
			'toggle_align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'left',
				'screen' => 1,
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'css' => ['{{element}} .pagelayer-search-container'  => 'text-align:{{val}};'],
				'req' => ['type' => 'full-screen'],
			),
			'holder_border_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-search-full-screen .pagelayer-search-fields' => 'background-color: {{val}};'],
				'req' => ['type' => 'full-screen'],
			),
			'holder_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_styles'),
				'default' => 'solid',
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'css' => ['{{element}} .pagelayer-search-fields' =>'border-style: {{val}};'],
			),
			'holder_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-search-fields' => 'border-color: {{val}};'],
				'req' => ['!holder_border_type' => '']
			),
			'holder_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '1,1,1,1',
				'screen' => 1,
				'css' =>  ['{{element}} .pagelayer-search-fields' =>'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px;'],
				'req' => ['!holder_border_type' => '']
			),
			'holder_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' =>  ['{{element}} .pagelayer-search-fields' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => ['!holder_border_type' => '']
			),
			'holder_shadow' => array(
				'type' => 'box_shadow',
				'label' => __pl('shadow'),
				'css' => ['{{element}} .pagelayer-search-fields' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[4]}}px {{val[3]}} {{val[5]}};'],
			),
		),
		'input_style' => array(
			'input_colors' => array(
				'type' => 'radio',
				'label' => '',
				'default' => '',
				'list' => array(
					'normal' => __pl('normal'),
					'focus' => __pl('focus'),
				),
			),
			'input_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-search-input' => 'color:{{val}}'],
				'show' => ['input_colors' => 'normal'],
			),
			'input_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-search-input' => 'background-color:{{val}}'],
				'show' => ['input_colors' => 'normal'],
			),
			'input_hover_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-search-input:focus' => 'color:{{val}}'],
				'show' => ['input_colors' => 'focus'],
			),
			'input_bg_hover_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-search-input:focus' => 'background-color:{{val}}'],
				'show' => ['input_colors' => 'focus'],
			),
			'input_size' => array(
				'type' => 'slider',
				'label' => __pl('font_size'),
				'css' => ['{{element}} .pagelayer-search-input' => 'font-size:{{val}}px'],
			),
			'input_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-search-input' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'input_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'default' => 100,
				'max' => 100,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-input' => 'width:{{val}}%'],
			),
			'input_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_styles'),
				'css' => ['{{element}} .pagelayer-search-input' =>'border-style: {{val}};'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'input_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-search-input' => 'border-color: {{val}};'],
				'req' => ['!input_border_type' => '']
			),
			'input_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '0,0,0,0',
				'css' =>  ['{{element}} .pagelayer-search-input' =>'border-width: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px !important;'],
				'req' => ['!input_border_type' => '']
			),
			'input_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'css' =>  ['{{element}} .pagelayer-search-input' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => ['!input_border_type' => '']
			),
		),
		'button_style' => array(
			'button_colors' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => 'normal',
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'button_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-search-button' => 'color:{{val}}'],
				'show' => ['button_colors' => 'normal'],
			),
			'button_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-search-button' => 'background-color:{{val}}'],
				'show' => ['button_colors' => 'normal'],
			),
			'button_hover_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-search-button:hover' => 'color:{{val}}'],
				'show' => ['button_colors' => 'hover'],
			),
			'button_bg_hover_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-search-button:hover' => 'background-color:{{val}}'],
				'show' => ['button_colors' => 'hover'],
			),
			'button_position' => array(
				'type' => 'radio',
				'label' => __pl('position'),
				'screen' => 1,
				'default' => 'row',
				'list' => array(
					'row-reverse' => __pl('left'),
					'row' => __pl('right'),
				),
				'css' => ['{{element}} .pagelayer-search-fields' => 'flex-direction:{{val}}'],
				'req' => ['type' => 'classic'],
			),
			'button_size' => array(
				'type' => 'slider',
				'label' => __pl('font_size'),
				'units' => ['px', 'em'],
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-submit-label' => 'font-size:{{val}}',
				'{{element}} .pagelayer-search-submit-icon' => 'font-size:{{val}}'],
			),
			'button_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-search-button' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => ['button_type' => 'text']
			),
			'button_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'units' => ['px', '%'],
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-button' => 'min-width:{{val}}'],
			),
			'button_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => ['px', 'em','%'],
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-button' => 'padding:{{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}'],
			),
			'submit_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_styles'),
				'css' => ['{{element}} .pagelayer-search-submit' =>'border-style: {{val}};'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'submit_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-search-submit' => 'border-color: {{val}};'],
				'req' => ['!submit_border_type' => '']
			),
			'submit_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '0,0,0,0',
				'css' =>  ['{{element}} .pagelayer-search-submit' =>'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px;'],
				'req' => ['!submit_border_type' => '']
			),
			'button_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'max' => 100,
				'units' => ['px', '%'],
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-search-button' => 'border-radius:{{val}} !important'],
			),
		),
		'styles' => [
			'input_style' => __pl('input_style'),
			'button_style' => __pl('button_style'),
		]
	)
);

//FACEBOOK buttons
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_fb_btn', array(
		'name' => __pl('Facebook Button'),
		'group' => 'other',
		'html' =>  '<div class="pagelayer-fb-btn-container" pagelayer-facebook-load="1">
				<span class="pagelayer-app-details" pagelayer-app-id="{{fb-app-id}}"></span>
				<div id="fb-root"></div>
				<div if="{{link_type}}" class="fb-like pagelayer-fb-btn-details" id="fb-like-btn" data-href="{{custom-url}}" data-layout="{{layout}}" data-action="{{btn_action}}" data-show-faces="{{peoples_faces}}" data-size="{{btn_size}}" data-share="{{share_btn}}">			
				</div>
			</div>',
		'params' => array(
			'fb-app-id' => array(
				'type' => 'text',
				'label' => __pl('app_id'),
				'default' => get_option('pagelayer-fbapp-id'),
			),
			'link_type' => array(
				'type' => 'select',
				'label' => __pl('post_link_type'),
				'default' => 'current',
				'list' => array(
					'current' => __pl('current_url'),
					'custom_link' => __pl('custom_url')
				),
			),
			'custom-url' => array(
				'type' => 'text',
				'label' => __pl('page_url'),
				'default' => 'https://www.facebook.com/',
				'req' => array(
					'link_type' => 'custom_link',
				)
			),
			'share_btn' => array(
				'type' => 'checkbox',
				'label' => __pl('share_btn'),
			),
			'peoples_faces' => array(
				'type' => 'checkbox',
				'label' => __pl('liked_faces'),
			),
			'layout' => array(
				'type' => 'select',
				'label' => __pl('layout_type'),
				'default' => 'standard',
				'list' => array(
					'standard' => __pl('standard'),
					'box_count' => __pl('box_count'),
					'button_count' => __pl('button_count'),
					'button' => __pl('button'),
				),
			),
			'btn_action' => array(
				'type' => 'select',
				'label' => __pl('btn_action'),
				'default' => 'Like',
				'list' => array(
					'like' => __pl('like'),
					'recommend' => __pl('recommend'),
				),
			),
			'btn_size' => array(
				'type' => 'select',
				'label' => __pl('btn_size'),
				'default' => 'small',
				'list' => array(
					'small' => __pl('small'),
					'large' => __pl('large'),
				),
			),
		),
	)
);

//FACEBOOK embed
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_fb_embed', array(
		'name' => __pl('Facebook Embed'),
		'group' => 'other',
		'html' =>  '<div class="pagelayer-fb-embed-container" pagelayer-facebook-load="1">
				<span class="pagelayer-app-details" pagelayer-app-id={{fb-app-id}}></span>
				<div id="fb-root"></div>
				<div if={{embed_type}} class="fb-embed fb-{{embed_type}}" data-href={{post-url}} data-show-text={{post_text}} data-width=500 data-adapt-container-width="true" data-allowfullscreen={{fullscreen_video}} data-autoplay={{autoplay_video}} data-show-captions={{video_captions}} data-include-parent={{parent_comments}}>			
				</div>
			</div>',
		'params' => array(
			'fb-app-id' => array(
				'type' => 'text',
				'label' => __pl('app_id'),
				'default' => get_option('pagelayer-fbapp-id'),
			),
			'embed_type' => array(
				'type' => 'select',
				'label' => __pl('embed_type'),
				'default' => 'post',
				'list' => array(
					'post' => __pl('post'),
					'video' => __pl('video'),
					'comment-embed' => __pl('comment'),
				),
			),
			'post-url' => array(
				'type' => 'text',
				'label' => __pl('post-url'),
				'addAttr' => ['{{element}} .fb-post' => 'data-href="{{post-url}}"'],
				'default' => 'https://www.facebook.com/pagelayer/posts/528135551039110',
				'req' => array(
					'embed_type' => 'post',
				),
			),
			'video-url' => array(
				'type' => 'text',
				'label' => __pl('video-url'),
				'addAttr' => ['{{element}} .fb-video' => 'data-href="{{video-url}}"'],
				'default' => 'https://www.facebook.com/seekahost/videos/1027688264058449/',
				'req' => array(
					'embed_type' => 'video',
				),
			),
			'comment-url' => array(
				'type' => 'text',
				'label' => __pl('comment-url'),
				'addAttr' => ['{{element}} .fb-comment' => 'data-href="{{comment-url}}"'],
				'default' => 'https://www.facebook.com/sitepad/posts/751812071877561?comment_id=830006664058101&reply_comment_id=831580997234001&comment_tracking=%7B%22tn%22%3A%22R%22%7D',
				'req' => array(
					'embed_type' => 'comment-embed',
				),
			),
			'post_text' => array(
				'type' => 'checkbox',
				'label' => __pl('full_post'),
				'req' => array(
					'!embed_type' => 'comment-embed',
				),
			),
			'autoplay_video' => array(
				'type' => 'checkbox',
				'label' => __pl('autoplay'),
				'req' => array(
					'embed_type' => 'video',
				),
			),
			'fullscreen_video' => array(
				'type' => 'checkbox',
				'label' => __pl('full_screen'),
				'req' => array(
					'embed_type' => 'video',
				),
			),
			'video_captions' => array(
				'type' => 'checkbox',
				'label' => __pl('captions'),
				'req' => array(
					'embed_type' => 'video',
				),
			),
			'parent_comments' => array(
				'type' => 'checkbox',
				'label' => __pl('parent_comments'),
				'default' => 'true',
				'req' => array(
					'embed_type' => 'comment-embed',
				),
			),
		),
	)
);

//FACEBOOK comments
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_fb_comments', array(
		'name' => __pl('Facebook Comments'),
		'group' => 'other',
		'html' =>  '<div class="pagelayer-fb-comments-container" pagelayer-facebook-load="1">
			<span class="pagelayer-app-details" pagelayer-app-id={{fb-app-id}}></span>
			<div id="fb-root"></div>
			<div  class="fb-comments" data-href="{{custom-url}}" data-colorscheme="{{color_scheme}}" data-numposts="{{number-of-comments}}" data-order-by="{{comments_order}}">
			</div>
		</div>',
		'params' => array(
			'fb-app-id' => array(
				'type' => 'text',
				'label' => __pl('app_id'),
				'default' => get_option('pagelayer-fbapp-id'),
			),
			'link_type' => array(
				'type' => 'select',
				'label' => __pl('post_link_type'),
				'default' => 'current',
				'list' => array(
					'current' => __pl('current_url'),
					'custom_link' => __pl('custom_url')
				),
			),
			'custom-url' => array(
				'type' => 'text',
				'label' => __pl('page_url'),
				'default' => 'https://www.facebook.com/pagelayer',
				'req' => array(
					'link_type' => 'custom_link',
				)
			),
			'color_scheme' => array(
				'type' => 'select',
				'label' => __pl('color_scheme'),
				'default' => 'light',
				'list' => array(
					'light' => __pl('Light'),
					'dark' => __pl('Dark'),
				),
			),
			'number-of-comments' => array(
				'type' => 'slider',
				'label' => __pl('comments_count'),
				'min' => '1',
				'max' => '100',
				'default' => '10',
			),
			'comments_order' => array(
				'type' => 'select',
				'label' => __pl('comments_order'),
				'default' => '',
				'list' => array(
					'social' => __pl('social'),
					'reverse_time' => __pl('reverse_time'),
					'time' => __pl('time'),
				),
			),
		),
	)
);

//FACEBOOK page
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_fb_page', array(
		'name' => __pl('Facebook Page'),
		'group' => 'other',
		'html' =>  '<div class="pagelayer-fb-page-container" pagelayer-facebook-load="1">
			<span class="pagelayer-app-details" pagelayer-app-id={{fb-app-id}}></span>
			<div id="fb-root"></div>
			<div if={{page_url}} class="fb-page" data-href={{page_url}} data-width="500" data-hide-cta={{cta-button}} data-tabs={{tabs_types}} data-small-header={{small_header}} data-adapt-container-width="true" data-hide-cover={{hide_cover}} data-show-facepile={{show_friends_faces}}>			
			</div>
		</div>',
		'params' => array(
			'fb-app-id' => array(
				'type' => 'text',
				'label' => __pl('app_id'),
				'default' => get_option('pagelayer-fbapp-id'),
			),
			'page_url' => array(
				'type' => 'text',
				'label' => __pl('page_link'),
				'default' => 'https://www.facebook.com/pagelayer',
			),
			'tabs_types' => array(
				'type' => 'multiselect',
				'label' => __pl('tabs'),
				'default' => 'timeline',
				'list' => array(
					'timeline' => __pl('timeline'),
					'events' => __pl('events'),
					'messages' => __pl('messages')
				),
			),
			'height' => array(
				'type' => 'spinner',
				'label' => __pl('page_height'),
				'default' => 500,
				'min' => 70,
				'max' => 1000,
				'step' => 10,
				'addAttr' => ['{{element}} .pagelayer-fb-page-container .fb-page' => 'data-height="{{height}}"'],
			),
 			'small_header' => array(
				'type' => 'checkbox',
				'label' => __pl('small_header'),
			),
			'hide_cover' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_cover'),
			),			
			'show_friends_faces' => array(
				'type' => 'checkbox',
				'label' => __pl('liked_faces'),
				'default' => "true",
			),
			'cta-button' => array(
				'type' => 'checkbox',
				'label' => __pl('cta_button'),
				'default' => "true",
			),			
		),
	)
);

// Review Item
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_review', array(
		'name' => __pl('review'),
		'group' => 'button',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_review_slider'],
		'html' => '<div class="pagelayer-review-slide">
			<div class="pagelayer-review-author">
				<div if={{author_image}} class="pagelayer-review-author-img">
					<img class="pagelayer-img" title="{{{author_image-title}}}" alt="{{{author_image-alt}}}"/>
				</div>				
				<div class="pagelayer-review-author-details">
					<div if={{author_name}} class="pagelayer-review-author-name">{{author_name}}</div>
					<div if={{author_title}} class="pagelayer-review-author-title">{{author_title}}</div>
					<div if={{number_of_ratings}} class="pagelayer-stars-container" title="{{number_of_ratings}}/5" pagelayer-stars-value="{{number_of_ratings}}" pagelayer-stars-count="5">	
					</div>					
				</div>
				<div class="pagelayer-icon-holder pagelayer-{{icon}}-icon">
					<a if-ext="{{icon_url}}" class="pagelayer-ele-link pagelayer-review-icon-link" href="{{{icon_url}}}">
						<i class="pagelayer-social-fa {{icon}} {{bg_shape}} {{icon_size}} pagelayer-animation-{{anim_hover}}"></i>
					</a>
				</div>			
			</div>
			<hr>
			<div class="pagelayer-review-text">
				<p if={{review_text}} class="pagelayer-review-p">{{review_text}}</p>
			</div>			
		</div>',
		'params' => array(
			'author_name' => array(
				'type' => 'text',
				'label' => __pl('author_Name'),
				'default' => 'Jane Doe',
				'edit' => '.pagelayer-review-author-name',
			),
			'author_title' => array(
				'type' => 'text',
				'label' => __pl('author_title'),
				'default' => '@janedoe',
				'edit' => '.pagelayer-review-author-title',
			),
			'show_avatar' => array(
				'type' => 'checkbox',
				'label' => __pl('show_avatar'),
			),
			'author_image' => array(
				'type' => 'image',
				'label' => __pl('avatar_style'),
				'addAttr' => ['{{element}} .pagelayer-review-author-img img' => 'src="{{{author_image-url}}}"'],
				'req' => ['show_avatar' => 'true'],
				'ai' => false,
			),
			'number_of_ratings' => array(
				'type' => 'spinner',
				'label' => __pl('author_rating'),
				'min' => '0',
				'max' => '5',
				'step' => '.1',
			),
			'icon_url' => array(
				'type' => 'link',
				'label' => __pl('author_url'),
				'selector' => '.pagelayer-review-icon-link',
			),
			'icon' => array(
				'type' => 'icon',
				'label' => __pl('author_Icon'),
				'default' => 'fab fa-facebook',
				'list' => ['facebook', 'facebook-official', 'facebook-square', 'twitter', 'twitter-square', 'x-twitter', 'x-twitter-square', 'google-plus', 'google-plus-square', 'instagram', 'linkedin', 'linkedin-square', 'behance', 'behance-square', 'pinterest', 'pinterest-p', 'pinterest-square', 'reddit-alien', 'reddit-square', 'reddit', 'rss', 'rss-square', 'skype', 'slideshare', 'snapchat', 'snapchat-ghost', 'snapchat-square', 'soundcloud', 'spotify', 'stack-overflow', 'steam', 'steam-square', 'stumbleupon', 'telegram', 'thumb-tack', 'tripadvisor', 'tumblr', 'tumblr-square', 'twitch', 'vimeo', 'vimeo-square', 'vk', 'weibo', 'weixin', 'whatsapp', 'wordpress', 'xing', 'xing-square', 'yelp', 'youtube', 'youtube-square', 'youtube-play', '500px', 'flickr', 'android', 'github', 'github-square', 'gitlab', 'apple', 'jsfiddle', 'houzz', 'bitbucket', 'bitbucket-square', 'codepen', 'delicious', 'medium', 'meetup', 'mixcloud', 'dribbble', 'foursquare'],
			),
			'review_text' => array(
				'type' => 'textarea',
				'label' => __pl('review'),
				'default' => '"There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which look even slightly believable."',
				'edit' => '.pagelayer-review-p',
				'e' => [ 'v', 'f', 'c', 'r'],
			),
		)
	)
);

// Reviews Slider
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_review_slider', array(
		'name' => __pl('reviews'),
		'group' => 'other',
		'prevent_inside' => ['pl_slides'],
		'holder' => '.pagelayer-reviews-holder',
		'child_selector' => '>.pagelayer-owl-stage-outer>.pagelayer-owl-stage>.pagelayer-owl-item', // Make it very specifc
		'html' => '<div class="pagelayer-review-slides-container">
			<div class="pagelayer-reviews-holder pagelayer-owl-holder pagelayer-owl-carousel pagelayer-owl-theme"></div>
		</div>',
		'has_group' => [
			'section' => 'params', 
			'prop' => 'elements'
		],
		'params' => array(
			'elements' => array(
				'type' => 'group',
				'label' => __pl('review'),
				'sc' => PAGELAYER_SC_PREFIX.'_review',
				'item_label' => array(
					'default' => __pl('review'),
					'param' => 'author_name',
				),
				'count' => 3,
				'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('review'))),
			),			
			'width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'default' => 100,
				'min' => 30,
				'max' => 100,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-reviews-holder' => 'width:{{val}}%;']
			),
		),
		'author_style' => array(
			'author_name_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#000000',
				'css' => ['{{element}} .pagelayer-review-author-name' => 'color:{{val}} !important;'],
			),
			'author_name' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',16,,500,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-review-author-name' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		),
		'title_style' => array(
			'author_title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#000000',
				'css' => ['{{element}} .pagelayer-review-author-title' => 'color:{{val}} !important;'],
			),			
			'author_title' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',14,,500,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-review-author-title' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		),
		'review_style' => array(
			'review_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#000000',
				'css' => ['{{element}} .pagelayer-review-text p' => 'color: {{val}} !important;'],
			),			
			'review_typography' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',18,,500,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-review-text p' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),			
		),
		'icon_style' => array(
			'icon_color' => array(
				'type' => 'color',
				'label' => __pl('icon_color'),
				'css' => ['{{element}} .pagelayer-social-fa' => 'color: {{val}};']
			),
			'icon_size_custom' => array(
				'type' => 'spinner',
				'label' => __pl('service_box_icon_custom_size_label'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'default' => 20,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-social-fa' => 'font-size: {{val}}px']
			)
		),
		'seperator_style' => array(
			'seperator_width' => array(
				'type' => 'slider',
				'label' => __pl('seperator_width'),
				'default' => 1,
				'min' => 0,
				'max' => 20,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-slide hr' => 'border-top:{{val}}px solid;'],
			),
			'seperator_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#c5c5c5',
				'css' => ['{{element}} .pagelayer-review-slide hr' => 'border-top-color:{{val}};'],
			),
		),
		'stars_style' => array(
			'stars_color' => array(
				'type' => 'color',
				'label' => __pl('stars_color'),
				'default' => '#FFEB3B',
				'css' => ['{{element}} .pagelayer-stars-icon:before' => 'color: {{val}}'],
			),
			'unmarked_stars_color' => array(
				'type' => 'color',
				'label' => __pl('unmarked_star_color'),
				'default' => '#ccd6df',
				'css' => ['{{element}} .pagelayer-stars-container' => 'color: {{val}}'],
			),
			'stars_font_size' => array(
				'label' => __pl('stars_font_size'),
				'type' => 'slider',
				'min' => 5,
				'max' => 100,
				'default' => 20,
				'screen' => 1,
				'css' => ['{{element}}  .pagelayer-stars-container' => 'font-size:{{val}}px;'],
			),
			'stars_spacing' => array(
				'label' => __pl('stars_spacing'),
				'type' => 'slider',
				'min' => 0,
				'max' => 20,
				'default' => 2,
				'screen' => 1,
				'css' => ['{{element}}  .pagelayer-stars-icon:not(:first-child)' => 'margin-left:{{val}}px;'],
			),
		),
		'avatar_style' => array(
			'author_image_width' => array(
				'type' => 'slider',
				'label' => __pl('rw_image_width'),
				'min' => 50,
				'max' => 120,
				'default' => 70,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-author-img img' => 'width:{{val}}px !important; height: {{val}}px !important;'],
			),
			'author_image_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'max' => 100,
				'default' => 0,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-author-img img' => 'border-radius:{{val}}px !important;'],
			),
		),
		'slide_style' => array(
			'background-color' => array(
				'type' => 'color',
				'label' => __pl('background_color'),
				'css' => ['{{element}} .pagelayer-review-slide' =>'background-color: {{val}};']
			),
			'slide_border_hover' => array(
				'type' => 'radio',
				'label' => __pl('border_state'),
				'default' => '',
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'slide_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-review-slide' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'slide_border_hover' => ''
				),
			),
			'slide_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-review-slide' => 'border-color: {{val}}!important;'],
				'req' => array(
					'!slide_border_type' => ''
				),
				'show' => array(
					'slide_border_hover' => ''
				),
			),
			'slide_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-slide' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!slide_border_type' => ''
				],
				'show' => array(
					'slide_border_hover' => ''
				),
			),
			'slide_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-slide' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!slide_border_type' => ''
				),
				'show' => array(
					'slide_border_hover' => ''
				),
			),
			'slide_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-review-slide:hover' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'slide_border_hover' => 'hover'
				),
			),
			'slide_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-review-slide:hover' => 'border-color: {{val}} !important;'],
				'default' => '#0986c0',
				'req' => array(
					'!slide_border_type_hover' => ''
				),
				'show' => array(
					'slide_border_hover' => 'hover'
				),
			),
			'slide_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-slide:hover' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!slide_border_type_hover' => ''
				],
				'show' => array(
					'slide_border_hover' => 'hover'
				),
			),
			'slide_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-review-slide:hover' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!slide_border_type_hover' => ''
				),
				'show' => array(
					'slide_border_hover' => 'hover'
				),
			),
			'slide_box_shadow' => array(
				'type' => 'box_shadow',
				'label' => __pl('shadow'),
				'css' => ['{{element}} .pagelayer-review-slide' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[4]}}px {{val[3]}} {{val[5]}} !important;'],
			),
		),
		'slider_options' => $pagelayer->slider_options,
		'arrow_styles' => $pagelayer->slider_arrow_styles,
		'pager_styles' => $pagelayer->slider_pager_styles,
		'styles' => array(
			'author_style' => __pl('author_style'),
			'title_style' => __pl('title_style'),
			'review_style' => __pl('review_style'),
			'icon_style' => __pl('icon_style'),
			'seperator_style' => __pl('seperator_style'),
			'stars_style' => __pl('stars_style'),
			'avatar_style' => __pl('avatar_style'),
			'slide_style' => __pl('slide_style'),
			'slider_options' => __pl('slider_options'),			
			'arrow_styles' => __pl('arrow_styles'),			
			'pager_styles' => __pl('pager_styles'),			
		)
	)
);

// Template list
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_templates', array(
		'name' => __pl('templates'),
		'group' => 'other',
		'no_gt' => 1,
		'html' => '<div class="pagelayer-template-content">{{template_content}}</div>',
		'params' => array(
			'templates' => array(
				'type' => 'select',
				'label' => __pl('select_templates'),
				'default' => '0',
				'list' => ['0' => __pl('none')] + pagelayer_post_list_by_type('pagelayer-template'),
			),
		)
	)
);

// Posts Grid
/* pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_wp_posts_grid', array(
		'name' => __pl('Posts Grid'),
		'group' => 'wordpress',
		'html' => '<div class="pagelayer-wp-posts-grid-container"></div>',
		'params' => array(
			'query_type' => array(
				'type' => 'radio',
				'label' => __pl('posts_grid_query_type_label'),
				'desc' => __pl('posts_grid_query_type_desc'),
				'default' => 'simple',
				'list' => array(
					'simple' => __pl('simple'),
					'custom' => __pl('posts_grid_custom_query_label'),
					'ids' => __pl('ids'),
				)
			),
			'post_type' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_post_type_label'),
				'desc' => __pl('posts_grid_post_type_desc'),
				'list' => pagelayer_post_types(),
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'category' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_category_label'),
				'desc' => __pl('posts_grid_category_desc'),
				'req' => array(
					'post_type' => 'post'
				)
			),
			'tag' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_tag_label'),
				'desc' => __pl('posts_grid_tag_desc'),
				'req' => array(
					'post_type' => 'post'
				)
			),
			'custom_tax' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_custom_tax_label'),
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'custom_tax_field' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_custom_tax_field_label'),
				'default' => 'slug',
				'list' => array(
					'term_id' => __pl('term_id'),
					'slug' => __pl('slug'),
					'name' => __pl('name')
				),
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'custom_tax_terms' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_custom_tax_term_label'),
				'desc' => __pl('posts_grid_custom_tax_term_desc'),
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'posts_per_page' => array(
				'type' => 'spinner',
				'label' => __pl('posts_grid_posts_per_page_label'),
				'default' => 4, // For backward compatibility in lite version must be 3 posts per page
				'min' => 1,
				'max' => 40,
				'step' => 1,
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'posts_order' => array(
				'type' => 'radio',
				'label' => __pl('posts_grid_sort_order'),
				'default' => 'DESC',
				'list' => array(
					'ASC' => __pl('posts_grid_sort_order_ascending'),
					'DESC' => __pl('posts_grid_sort_order_descending')
				),
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'custom_query' => array(
				'type' => 'textarea',
				'label' => __pl('posts_grid_custom_query_label'),
				'desc' => __pl('posts_grid_custom_query_desc'),
				'req' => array(
					'query_type' => 'custom'
				)
			),
			'ids' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_ids_label'),
				'desc' => __pl('posts_grid_ids_desc'),
				'req' => array(
					'query_type' => 'ids'
				)
			),
			'columns' => array(
				'type' => 'radio',
				'label' => __pl('columns_count'),
				'default' => 2,
				'list' => array( 
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
					6 => 6
				)
			),
			'template' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_template_label'),
				'list' => array(),
			),
			'posts_gap' => array(
				'type' => 'slider',
				'label' => __pl('posts_grid_posts_gap_label'),
				'default' => 30,
				'min' => 0,
				'max' => 100,
				'step' => 10,
			),
			'show_featured_image' => array(
				'type' => 'checkbox',
				'label' => __pl('posts_grid_show_featured_image'),
				'default' => 'true',
			),
			'image_size' => array(
				'type' => 'radio',
				'label' => __pl('obj_image_size_label'),
				'default' => 'large',
				'list' => array(
					'full' => __pl('full'),
					'large' => __pl('large'),
					'medium' => __pl('medium'),
					'thumbnail' => __pl('thumbnail'),
					'custom' => __pl('custom')
				),
				'req' => array(
					'show_featured_image' => 'true'
				),
			),
			'image_custom_size' => array(
				'type' => 'text',
				'desc' => __pl('image_custom_size_label'),
				'req' => array(
					'image_size' => 'custom'
				),
			),
			'title_tag' => array(
				'type' => 'radio',
				'label' => __pl('posts_grid_title_tag'),
				'default' => 'h2',
				'list' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'hide' => __pl('posts_grid_title_tag_none'),
				)
			),
			'show_date_comments' => array(
				'type' => 'checkbox',
				'label' => __pl('posts_grid_show_date_comments'),
				'default' => 'true',
			),
			'show_content' => array(
				'type' => 'radio',
				'label' => __pl('posts_grid_show_content'),
				'default' => 'short',
				'list' => array(
					'short' => __pl('posts_grid_show_content_short'),
					'full' => __pl('posts_grid_show_content_full'),
					'excerpt' => __pl('posts_grid_show_content_excerpt'),
					'hide' => __pl('posts_grid_show_content_none'),
				)
			),
			'short_content_length' => array(
				'type' => 'slider',
				'label' => __pl('posts_grid_short_content_length'),
				'default' => 200,
				'min' => 0,
				'max' => 1000,
				'step' => 20,
				'req' => array(
					'show_content' => 'short'
				),
			),
			'read_more_text' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_read_more_text_label'),
				'default' => __pl('posts_grid_read_more_text')
			),
			'display_style' => array(
				'type' => 'radio',
				'label' => __pl('posts_grid_display_style'),
				'default' => 'show_all',
				'list' => array(
					'show_all' => __pl('show_all'),
					'load_more' => __pl('posts_grid_display_style_load_more'),
					'pagination' => __pl('posts_grid_display_style_pagination')
				)
			),
			'load_more_text' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_load_more_text_label'),
				'default' => __pl('posts_grid_load_more_text_default'), // "Load More"
				'req' => array(
					'display_style' => 'load_more'
				)
			),
			'filter' => array(
				'type' => 'radio',
				'label' => __pl('posts_grid_filter_label'),
				'desc' => __pl('posts_grid_filter_desc'),
				'default' => 'none',
				'list' => array(
					'none' => __pl('none'),
					'cats' => __pl('posts_grid_filter_by_first_tax'),
					'tags' => __pl('posts_grid_filter_by_second_tax'),
					'both' => __pl('posts_grid_filter_by_both')
				),
				'req' => array(
					'query_type' => 'simple'
				)
			),
			'filter_tax_1' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_filter_first_tax_name'),
				'default' => 'category',
				'list' => pagelayer_tax_list('category'),
				'req' => array(
					'filter' => array( 'cats', 'both' )
				)
			),
			'filter_tax_2' => array(
				'type' => 'select',
				'label' => __pl('posts_grid_filter_second_tax_name'),
				'default' => 'post_tag',
				'list' => pagelayer_tax_list('post_tag'),
				'req' => array(
					'filter' => array( 'tags', 'both' )
				)
			),
			'filter_btn_color' => array(
				'type' => 'color',
				'label' => __pl('button_color_label'),
				'default' => '#333333',
				'req' => array(
					'!filter' => 'none'
				)
			),
			'filter_btn_divider' => array(
				'type' => 'text',
				'label' => __pl('filter_links_divider'),
				'default' => '/',
				'req' => array(
					'filter_btn_color' => 'none'
				)
			),
			'filter_cats_text' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_filter_first_tax_text_label'),
				'default' => __pl('categories') . ':',
				'req' => array(
					'filter' => array('cats', 'both')
				)
			),
			'filter_tags_text' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_filter_second_tax_text_label'),
				'default' => __pl('tags') . ':',
				'req' => array(
					'filter' => array('tags', 'both')
				)
			),
			'filter_all_text' => array(
				'type' => 'text',
				'label' => __pl('posts_grid_filter_view_all_text_label'),
				'default' => __pl('all'),
				'req' => array(
					'!filter' => 'none'
				)
			)
		)
	)
); */

// Posts Slider
/* pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_wp_posts_slider', array(
		'name' => __pl('Posts Slider'),
		'group' => 'wordpress',
		'html' => '<div class="pagelayer-posts-slider-container" slider-autoplay="{{slider_autoplay}}" count="{{items_to_display}}" post-slider-pause="{{pause_on_hover}}" bullets="{{show_bullets}}" controlbtn ="{{show_nav}}" hide-posts-title="{{post_title}}" hide-posts-image="{{posts_image}}" hide-posts-date="{{post_date}}" post-content="{{show_content}}" hide-post-link ="{{read_more}}">
			<ul class="pagelayer-posts-slider-main">
			{{posts_slides}}
			</ul>
		</div>',
		'params' => array(			
			'post_count' => array(
				'type' => 'spinner',
				'label' => __pl('posts_count'),
				'default' => 1,
				'min' => 1,
				'max' => 10,
				'step' => 1
			),			
			'post_type' => array(
				'type' => 'select',
				'label' => __pl('post_types'),
				'default' => 'post',
				'list' => pagelayer_post_types(true),
			),
			'category' => array(
				'type' => 'select',
				'label' => __pl('categories'),
				'list' => pagelayer_get_categories(),
				'req' => array(
					'post_type' => 'post'
				),
			),
			'tags' => array(
				'type' => 'select',
				'label' => __pl('tags'),
				'list' => pagelayer_get_tags(),
				'req' => array(
					'post_type' => 'post'
				),
			),
			'order_by' => array(
				'type' => 'select',
				'label' => __pl('posts_order_by'),
				'default' => 'date',
				'list' => array(
					'ID' => __pl('posts_order_by_id'),
					'date' => __pl('posts_order_by_date'),
					'author' => __pl('posts_order_by_author'),
					'modified' => __pl('posts_order_by_modified'),
					'rand' => __pl('posts_order_by_random'),
					'comment_count' => __pl('posts_order_by_comment_count'),
					'menu_order' => __pl('posts_order_by_menu_order'),
				),
			),
			'sort_order' => array(
				'type' => 'radio',
				'label' => __pl('post_sort_order'),
				'default' => 'DESC',
				'list' => array(
					'ASC' => __pl('sort_order_ascending'),
					'DESC' => __pl('sort_order_descending'),
				),
			),
			'show_excerpt' => array(
				'type' => 'checkbox',
				'label' => __pl('show_excerpt'),
			),
		),
		'title_style' => [
			'post_title' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_title'),
			),
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('title_color'),
				'default' => '#000000',
				'css' =>  ['{{element}} .pagelayer-posts-slider-title' => 'color:{{val}}'],
				'req' => array(
					'post_title' => '',
				),
			),
			'title_spacing' => array(
				'type' => 'padding',
				'label' => __pl('spacing'),
				'css' => ['{{element}} .pagelayer-posts-slider-title' => 'margin:{{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'post_title' => '',
				),
			),
			'post_title_style' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',20,,500,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-posts-slider-title' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => array(
					'post_title' => '',
				),
			),
		],
		'image_style' => [
			'posts_image' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_image'),
			),
			'image_size' => array(
				'type' => 'radio',
				'label' => __pl('image_size'),
				'default' => 'full',
				'list' => array(
					'full' => __pl('full'),
					'thumbnail' => __pl('thumbnail'),
					'custom' => __pl('custom')
				),
				'req' => array(
					'posts_image' => '',
				),
			),			
			'img_height' => array(
				'type' => 'slider',
				'label' => __pl('img_height'),
				'css' => ['{{element}} .pagelayer-posts-slider-img' => 'height: {{val}}em;'],
				'default' => 10,
				'min' => 10,
				'max' => 20,
				'step' => 0.2,
				'req' => array(
					'image_size' => 'custom',
				),
			),
			'image_spacing' => array(
				'type' => 'padding',
				'label' => __pl('spacing'),
				'css' => ['{{element}} .pagelayer-posts-slider-featured-img' => 'margin: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'posts_image' => '',
				),
			),
		],
		'content_style' => [
			'content_color' => array(
				'type' => 'color',
				'label' => __pl('content_color'),
				'default' => '#000000',
				'css' =>  ['{{element}} .pagelayer-posts-slider-excerpt' => 'color:{{val}}'],
			),
			'post_content_style' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',16,,300,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-posts-slider-excerpt ' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
		],
		'date_style' => [
			'post_date' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_date'),
			),
			'date_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#000000',
				'css' =>  ['{{element}} .pagelayer-post-slider-date p' => 'color:{{val}}'],
				'req' => array(
					'post_date' => '',
				),
			),
			'post_date_style' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',14,,500,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-post-slider-date p' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}}!important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing:{{val[10]}}px !important;'],
				'req' => array(
					'post_date' => '',
				),
			),
		],
		'post_style' => [
			'read_more' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_link'),
			),
			'link_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'default' => '#e82121',
				'css' =>  ['{{element}} .pagelayer-posts-slider-link' => 'color:{{val}}'],
			),
			'background_color' => array(
				'type' => 'color',
				'label' => __pl('background_color'),
				'css' =>  ['{{element}} .pagelayer-posts-slider-post' => 'background-color:{{val}}'],
			),
			'link_style' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'default' => ',16,,500,,,solid,,,,',
				'css' => ['{{element}} .pagelayer-posts-slider-link' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'post_shadow' => array(
				'type' => 'box_shadow',
				'label' => __pl('post_shadow'),
				'css' => ['{{element}} .pagelayer-posts-slider-post' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}};'],
			),			
			'post_border_hover' => array(
				'type' => 'radio',
				'label' => __pl('border_state'),
				'default' => '',
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'post_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-posts-slider-post' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'post_border_hover' => ''
				),
			),
			'post_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-posts-slider-post' => 'border-color: {{val}}!important;'],
				'req' => array(
					'!post_border_type' => ''
				),
				'show' => array(
					'post_border_hover' => ''
				),
			),
			'post_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['{{element}} .pagelayer-posts-slider-post' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!post_border_type' => ''
				],
				'show' => array(
					'post_border_hover' => ''
				),
			),
			'post_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'css' => ['{{element}} .pagelayer-posts-slider-post' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!post_border_type' => ''
				),
				'show' => array(
					'post_border_hover' => ''
				),
			),
			'post_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-posts-slider-post:hover' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => array(
					'post_border_hover' => 'hover'
				),
			),
			'post_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-posts-slider-post:hover' => 'border-color: {{val}} !important;'],
				'default' => '#0986c0',
				'req' => array(
					'!post_border_type_hover' => ''
				),
				'show' => array(
					'post_border_hover' => 'hover'
				),
			),
			'post_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['{{element}} .pagelayer-posts-slider-post:hover' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!post_border_type_hover' => ''
				],
				'show' => array(
					'post_border_hover' => 'hover'
				),
			),
			'post_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'css' => ['{{element}} .pagelayer-posts-slider-post:hover' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!post_border_type_hover' => ''
				),
				'show' => array(
					'post_border_hover' => 'hover'
				),
			),			
		],
		'slider_options' => [
			'items_to_display' => array(
				'type' => 'spinner',
				'label' => __pl('slider_items_display'),
				'default' => 1,
				'min' => 1,
				'max' => 10,
				'step' => 1
			),
			'slider_autoplay' => array(
				'type' => 'checkbox',
				'label' => __pl('autoplay'),
				'default' => 'true',
			),
			'show_nav' => array(
				'type' => 'checkbox',
				'label' => __pl('slider_navigations'),
				'default' => 'true',
			),
			'nav_arrow_color' => array(
				'type' => 'color',
				'label' => __pl('slider_arrows_color'),
				'css' => ['{{element}} .pagelayer-prev-arrow:before' => 'color:{{val}} !important;',
				'{{element}} .pagelayer-next-arrow:before' => 'color:{{val}} !important;'],
				'req' => array(
					'show_nav' => 'true',
				)
			),
			'show_bullets' => array(
				'type' => 'checkbox',
				'label' => __pl('bullets'),
				'default' => 'true',
			),
			'pause_on_hover' => array(
				'type' => 'checkbox',
				'label' => __pl('slider_pause_on_hover'),
			),
		],
		'styles' => [
			'title_style' => __pl('title_style'),
			'image_style' => __pl('image_style'),
			'content_style' => __pl('content_style'),
			'date_style' => __pl('date_style'),
			'post_style' => __pl('posts_style'),
			'slider_options' => __pl('slider_options'),
		],
	)
); */

// Image Portfolio
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_img_portfolio', array(
	'name' => __pl('img_portfolio'),
	'group' => 'image',
	'icon' => 'fas fa-briefcase',
	'has_group' => [
		'section' => 'params',
		'prop' => 'elements'
	],
	//'child_selector' => '>.pagelayer-owl-stage-outer>.pagelayer-owl-stage>.pagelayer-owl-item', // Make it very specifc
	'holder' => '.pagelayer-img_portfolio-holder',
	'html' => '<div class="pagelayer-category-holder"></div>
		<div class="pagelayer-img_portfolio-holder"></div>',
	'params' => array(
		'elements' => array(
			'type' => 'group',
			'label' => __pl('image'),
			'sc' => PAGELAYER_SC_PREFIX.'_single_img',
			'item_label' => array(
				'default' => __pl('image'),
				'param' => 'title'
			),
			'count' => 1,
			'text' => strtr(__pl('add_new_item'), array('%name%' => __pl('image')))
		)
	),
	'img_style' =>[
		'img_width' => array(
			'type' => 'slider',
			'label' => __pl('width'),
			'screen' => 1,
			'min' => 0,
			'step' => 1,
			'max' => 100,
			'css' => ['{{element}} .pagelayer-img_portfolio-holder>div' => 'width: {{val}}%;'],
		),
		'img_spacing' => array(
			'type' => 'padding',
			'label' => __pl('space_around'),
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-single_img' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px'],
		),
		'img_height' => array(
			'type' => 'spinner',
			'label' => __pl('height'),
			'screen' => 1,
			'min' => 0,
			'step' => 1,
			'css' => ['{{element}} .pagelayer-img_portfolio-holder>div' => 'height: {{val}}px; overflow:hidden;'],
		),
		'img_stretch' => array(
			'type' => 'checkbox',
			'label' => __pl('stretch'),
			'css' => [
				'{{element}}, {{element}} .pagelayer-pf-img, {{element}} .pagelayer-pf-img img'=> 'height:100%; width:100%;'
			],
			'req' => ['!img_height' => '']
		),
		'img_hover' => array(
			'type' => 'radio',
			'label' => __pl('state'),
			'list' => array(
				'' => __pl('normal'),
				'hover' => __pl('hover'),
			),
		),
		'img_filter' => array(
			'type' => 'filter',
			'label' => __pl('filter'),
			'css' => ['{{element}} .pagelayer-pf-img img' => 'filter: blur({{val[0]}}px) brightness({{val[1]}}%) contrast({{val[2]}}%) grayscale({{val[3]}}%) hue-rotate({{val[4]}}deg) opacity({{val[5]}}%) saturate({{val[6]}}%)'],
			'show' => ['img_hover' => '']
		),
		'img_shadow' => array(
			'type' => 'box_shadow',
			'label' => __pl('shadow'),
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-pf-img img' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[4]}}px {{val[3]}} {{val[5]}} !important;'],
			'show' => ['img_hover' => '']
		),
		'img_rotate' => array(
			'type' => 'spinner',
			'label' => __pl('Rotate'),
			'min' => 0,
			'max' => 360,
			'step' => 1,
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-pf-img img' => 'transform: rotate({{val}}deg)'],
			'show' => ['img_hover' => '']
		),
		'img_hover_delay' => array(
			'type' => 'spinner',
			'label' => __pl('btn_hover_delay_label'),
			'desc' => __pl('btn_hover_delay_desc'),
			'min' => 0,
			'step' => 100,
			'max' => 5000,
			'css' => ['{{element}} .pagelayer-pf-img img' => '-webkit-transition: all {{val}}ms; transition: all {{val}}ms;',],
			'show' => ['img_hover' => 'hover']
		),
		'img_filter_hover' => array(
			'type' => 'filter',
			'label' => __pl('filter'),
			'css' => ['{{element}} .pagelayer-pf-img img:hover' => 'filter: blur({{val[0]}}px) brightness({{val[1]}}%) contrast({{val[2]}}%) grayscale({{val[3]}}%) hue-rotate({{val[4]}}deg) opacity({{val[5]}}%) saturate({{val[6]}}%)'],
			'show' => ['img_hover' => 'hover']
		),
		'img_shadow_hover' => array(
			'type' => 'box_shadow',
			'label' => __pl('shadow'),
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-pf-img img:hover' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[4]}}px {{val[3]}} {{val[5]}} !important;'],
			'show' => ['img_hover' => 'hover']
		),
		'img_rotate_hover' => array(
			'type' => 'spinner',
			'label' => __pl('Rotate'),
			'min' => 0,
			'max' => 360,
			'step' => 1,
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-pf-img img:hover' => 'transform: rotate({{val}}deg)'],
			'show' => ['img_hover' => 'hover']
		)
	],
	'btns_style' => [
		'disable_category' => array(
			'type' => 'checkbox',
			'label' => __pl('disable_category'),
			'css' => ['{{element}} .pagelayer-category-holder' => 'display:none']
		),
		'btn_align' => array(
			'label' => __pl('alignment'),
			'type' => 'select',
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-category-holder' => 'text-align:{{val}};'],
			'list' => array(
				'left' => __pl('left'),
				'center' => __pl('center'),
				'right' => __pl('right'),
				'justify' => __pl('justify'),
			)
		),
		'btn_size' => array(
			'type' => 'dimension',
			'label' => __pl('size'),
			'screen' => 1,
			'css' => ['{{element}} button' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[0]}}px; padding-left: {{val[1]}}px;'],
		),
		'btn_spacing' => array(
			'type' => 'dimension',
			'label' => __pl('space_around'),
			'screen' => 1,
			'css' => ['{{element}} button' => 'margin-top: {{val[0]}}px; margin-right: {{val[1]}}px; margin-bottom: {{val[0]}}px; margin-left: {{val[1]}}px;'],
		),
		'btn_space_bottom' => array(
			'type' => 'slider',
			'label' => __pl('space_bottom'),
			'screen' => 1,
			'min' => 0,
			'step' => 1,
			'max' => 1000,
			'css' => ['{{element}} .pagelayer-category-holder' => 'padding-bottom: {{val}}px;'],
		),
		'btn_hover' => [
			'type' => 'radio',
			'label' => '',
			'list' => [
				'' => __pl('normal'),
				'hover' => __pl('hover'),
			],
		],
		'btn_color' => array(
			'label' => __pl('color'),
			'type' => 'color',
			'css' => ['{{element}} button' => 'color: {{val}};'],
			'show' => ['btn_hover' => ''],
		),
		'btn_bg' => array(
			'label' => __pl('bg_color'),
			'type' => 'color',
			'css' => ['{{element}} button' => 'background-color: {{val}};'],
			'show' => ['btn_hover' => ''],
		),
		'btn_typo' => array(
			'type' => 'typography',
			'label' => __pl('typography'),
			'css' => ['{{element}} button' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			'show' => ['btn_hover' => ''],
		),
		'btn_border_type' => [
			'type' => 'select',
			'label' => __pl('border_type'),
			'screen' => 1,
			'list' => [
				'' => __pl('none'),
				'solid' => __pl('solid'),
				'double' => __pl('double'),
				'dotted' => __pl('dotted'),
				'dashed' => __pl('dashed'),
				'groove' => __pl('groove'),
			],
			'show' => ['btn_hover' => ''],
			'css' => ['{{element}} button' => 'border-style: {{val}}'],
		],
		'btn_border_width' => [
			'type' => 'padding',
			'label' => __pl('border_width'),
			'units' => ['px', 'em'],
			'screen' => 1,
			'show' => [
				'btn_hover' => ''
			],
			'req' => [
				'!btn_border_type' => ''
			],
			'css' => ['{{element}} button' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
		],
		'btn_border_color' => [
			'type' => 'color',
			'label' => __pl('border_color'),
			'screen' => 1,
			'show' => [
				'btn_hover' => ''
			],
			'req' => [
				'!btn_border_type' => ''
			],
			'css' => ['{{element}} button' => 'border-color: {{val}}'],
		],
		'btn_border_radius' => [
			'type' => 'padding',
			'label' => __pl('border_radius'),
			'units' => ['px', 'em'],
			'screen' => 1,
			'show' => ['btn_hover' => ''],
			'css' => ['{{element}} button' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
		],
		'btn_hover_delay' => array(
			'type' => 'spinner',
			'label' => __pl('btn_hover_delay_label'),
			'desc' => __pl('btn_hover_delay_desc'),
			'min' => 0,
			'step' => 100,
			'max' => 5000,
			'css' => ['{{element}} button' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
			'show' => array(
				'btn_hover' => 'hover'
			),
		),
		'btn_color_hover' => array(
			'label' => __pl('color'),
			'type' => 'color',
			'css' => ['{{element}} button:hover' => 'color: {{val}};'],
			'show' => ['btn_hover' => 'hover'],
		),
		'btn_bg_hover' => array(
			'label' => __pl('bg_color'),
			'type' => 'color',
			'css' => ['{{element}} button:hover' => 'background-color: {{val}};'],
			'show' => ['btn_hover' => 'hover'],
		),
		'btn_typo_hover' => array(
			'type' => 'typography',
			'label' => __pl('typography'),
			'css' => ['{{element}} button:hover' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			'show' => ['btn_hover' => 'hover'],
		),
		'btn_btype_hover' => [
			'type' => 'select',
			'label' => __pl('border_type'),
			'screen' => 1,
			'list' => [
				'' => __pl('none'),
				'solid' => __pl('solid'),
				'double' => __pl('double'),
				'dotted' => __pl('dotted'),
				'dashed' => __pl('dashed'),
				'groove' => __pl('groove'),
			],
			'show' => ['btn_hover' => 'hover'],
			'css' => ['{{element}} button:hover' => 'border-style: {{val}}'],
		],
		'btn_bwidth_hover' => [
			'type' => 'padding',
			'label' => __pl('border_width'),
			'units' => ['px', 'em'],
			'screen' => 1,
			'show' => [
				'btn_hover' => 'hover'
			],
			'req' => [
				'!btn_btype_hover' => ''
			],
			'css' => ['{{element}} button:hover' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
		],
		'btn_bcolor_hover' => [
			'type' => 'color',
			'label' => __pl('border_color'),
			'screen' => 1,
			'show' => [
				'btn_hover' => 'hover'
			],
			'req' => [
				'!btn_btype_hover' => ''
			],
			'css' => ['{{element}} button:hover' => 'border-color: {{val}}'],
		],
		'btn_bradius_hover' => [
			'type' => 'padding',
			'label' => __pl('border_radius'),
			'screen' => 1,
			'units' => ['px', 'em'],
			'show' => ['btn_hover' => 'hover'],
			'css' => ['{{element}} button:hover' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
		],
	],
	'styles' => [
		'img_style' => __pl('service_img_style'),
		'btns_style' => __pl('button_style'),
	]
));

// Image Portfolio
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_single_img', array(
	'name' => __pl('image'),
	'group' => 'other',
	'not_visible' => 1,
	'parent' => [PAGELAYER_SC_PREFIX.'_img_portfolio'],
	'html' => '<div class="pagelayer-pf-img">
		<a if-ext="{{link_type}}" class="pagelayer-ele-link" href="{{func_link}}" pagelayer-image-link-type="{{link_type}}">
			<img class="pagelayer-img" src="{{func_img}}" title="{{{img-title}}}" alt="{{{img-alt}}}" />
			<div if="{{overlay}}" class="pagelayer-image-overlay">
				<div class="pagelayer-image-overlay-content">
					<i if="{{icon}}" class="pagelayer-image-overlay-icon {{icon}}"></i>
					<h3 if="{{heading}}" class="pagelayer-overlay-heading">{{heading}}</h3>
					<div if="{{text}}" class="pagelayer-image-overlay-text">{{text}}</div>
				</div>
			</div>
		</a>
	</div>',
	'params' => array(
		'title' => array(
				'type' => 'text',
				'label' => __pl('title'),
				'default' => 'Lorem',
		),
		'img' => array(
			'label' => __pl('image_src_label'),
			'type' => 'image',
			'default' => PAGELAYER_URL.'/images/default-image.png',
			'addAttr' => 'port-cat="all"',
		),
		'img-size' => array(
			'label' => __pl('obj_image_size_label'),
			'type' => 'select',
			'default' => 'full',
			'list' => array(
				'full' => __pl('full'),
				'large' => __pl('large'),
				'medium' => __pl('medium'),
				'thumbnail' => __pl('thumbnail'),
				'custom' => __pl('custom')
			)
		),
		'custom_size' => array(
			'label' => __pl('image_custom_size_label'),
			'type' => 'dimension',
			'default' => '100,100',
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-img' => 'width: {{val[0]}}px; height: {{val[1]}}px;'],
			'req' => array(
				'img-size' => 'custom'
			),
		),
		'cat_name' => array(
			'type' => 'text',
			'addAttr' => 'port-cat="{{cat_name}}"',
			'label' => __pl('category'),
			'default' => __pl('Technology'),
		),
		'link_type' => array(
			'label' => __pl('image_link_label'),
			'type' => 'select',
			'list' => array(
				'' => __pl('none'),
				'custom_url' => __pl('custom_url'),
				'media_file' => __pl('media_file'),
				'lightbox' => __pl('lightbox')
			)
		),
		'link' => array(
			'type' => 'link',
			'label' => __pl('image_link_label'),
			'desc' => __pl('image_link_desc'),
			'selector' => '.pagelayer-ele-link',
			'req' => array(
				'link_type' => 'custom_url'
			)
		),
		'rel' => array(
			'label' => __pl('image_rel_label'),
			'type' => 'text',
			'addAttr' => ['{{element}} a' => 'rel="{{rel}}"'],
			'req' => array(
				'link_type' => 'media_file'
			)
		),
		'target' => array(
			'label' => __pl('open_link_in_new_window'),
			'type' => 'checkbox',
			'addAttr' => ['{{element}} a' => 'target="_blank"'],
			'req' => ['link_type' => ['custom_url', 'media_file']], // For backward compatibility of the new link property in version 1.5.8, hide it for custom_url.
			'show' => ['link_type' => 'media_file']
		),
	),
	'overlay_style' => [
		'overlay' => array(
			'label' => __pl('image_overlay_effect_label'),
			'desc' => __pl('image_overlay_effect_desc'),
			'type' => 'checkbox',
		),
		'icon' => array(
			'label' => __pl('icon'),
			'type' => 'icon',
			'default' => 'far fa-eye',
			'req' => array(
				'overlay' => 'true'
			)
		),
		'icon_color' => array(
			'label' => __pl('icon_color'),
			'type' => 'color',
			'css' => ['{{element}} .pagelayer-image-overlay-icon' => 'color: {{val}}'],
			'req' => array(
				'overlay' => 'true'
			)
		),
		'icon_size' => array(
			'label' => __pl('icon_custom_size'),
			'desc' => __pl('icon_custom_size_desc'),
			'type' => 'spinner',
			'min' => 0,
			'step' => 1,
			'max' => 500,
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-image-overlay-icon' => 'font-size: {{val}}px'],
			'req' => array(
				'overlay' => 'true'
			)
		),
		'heading' => array(
			'label' => __pl('title'),
			'type' => 'text',
			'edit' => '.pagelayer-overlay-heading',
			'req' => array(
				'overlay' => 'true'
			)
		),
		'heading_color' => array(
			'label' => __pl('title_color'),
			'type' => 'color',
			'css' => ['{{element}} .pagelayer-overlay-heading, {{element}} .pagelayer-overlay-heading *' => 'color: {{val}}'],
			'req' => array(
				'overlay' => 'true'
			)
		),
		'heading_typo' => array(
			'type' => 'typography',
			'label' => __pl('heading_typo'),
			'css' => [
				'{{element}} .pagelayer-overlay-heading, {{element}} .pagelayer-overlay-heading *' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
			],
			'req' => array(
				'overlay' => 'true'
			)
		),
		'text' => array(
			'label' => __pl('content'),
			'type' => 'editor',
			'default' => 'Lorem Ipsum',
			'edit' => '.pagelayer-image-overlay-text',
			'req' => array(
				'overlay' => 'true'
			)
		),
		'overlay_bg_type' => array(
			'label' => __pl('background_type'),
			'type' => 'radio',
			'list' => array(
				'' => __pl('color'),
				'gradient' => __pl('gradient')
			),
			'req' => array(
				'overlay' => 'true'
			)
		),
		'overlay_bg' => array(
			'label' => __pl('image_overlay_background'),
			'type' => 'color',
			'css' => ['{{element}} .pagelayer-image-overlay' => 'background: {{val}}'],
			'req' => array(
				'overlay' => 'true',
				'overlay_bg_type' => ''
			)
		),
		'overlay_gradient' => [
			'type' => 'gradient',
			'label' => '',
			'css' => ['{{element}} .pagelayer-image-overlay' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],
			'req' => array(
				'overlay' => 'true',
				'overlay_bg_type' => 'gradient'
			)
		],
		'content_align' => array(
			'label' => __pl('alignment'),
			'type' => 'radio',
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-image-overlay-content' => 'text-align: {{val}};'],
			'list' => array(
				'left' => __pl('left'),
				'center' => __pl('center'),
				'right' => __pl('right'),
			),
			'req' => array(
				'overlay' => 'true'
			)
		),
		'content_position' => array(
			'label' => __pl('overlay_cont_pos'),
			'type' => 'radio',
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-image-overlay' => 'display:-webkit-flex;display:flex;-webkit-align-items:{{val}}; align-items:{{val}};'],
			'list' => array(
				'flex-start' => __pl('Top'),
				'center' => __pl('Middle'),
				'flex-end' => __pl('Bottom'),
			),
			'req' => array(
				'overlay' => 'true'
			)
		),
		'content_padding' => array(
			'type' => 'padding',
			'label' => __pl('padding'),
			'screen' => 1,
			'css' => ['{{element}}:hover .pagelayer-image-overlay' => 'padding-top: {{val[0]}}px; padding-right: {{val[1]}}px; padding-bottom: {{val[2]}}px; padding-left: {{val[3]}}px;'],
			'req' => array(
				'overlay' => 'true'
			)
		),
		'show_always' => array(
			'label' => __pl('image_show_always'),
			'type' => 'checkbox',
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-image-overlay' => 'height:100%;'],
			'req' => array(
				'overlay' => 'true'
			)
		)
	],
	'styles' => [
		'overlay_style' => __pl('overlay_style')
	]
));

/////////////////////////////////////
// WooCommerce Shortcodes
/////////////////////////////////////

if(class_exists( 'woocommerce' )){

// Product Images
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_images', array(
		'name' => __pl('product_images'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-product-images-container product pagelayer-woo-gallery-{{gposition}}">
		{{product_images_templ}}
		</div>',
		'params' => array(
			'sale_flash' => array(
				'type' => 'checkbox',
				'label' => __pl('sale_flash'),
			),
			'image_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['.woocommerce {{element}} .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper' => 'border-style: {{val}}',
					'.woocommerce {{element}} .flex-viewport' => 'border-style: {{val}}',
				],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'image_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['.woocommerce {{element}} .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper' => 'border-color: {{val}};',
					'.woocommerce {{element}} .flex-viewport' => 'border-color: {{val}};',
				],
				'req' => array(
					'!image_border_type' => ''
				),
			),
			'image_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['.woocommerce {{element}} .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px',
					'.woocommerce {{element}} .flex-viewport' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px',
				],
				'req' => [
					'!image_border_type' => ''
				],
			),
			'image_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => [ 'px', '%' ],
				'css' => ['.woocommerce {{element}} .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
					'.woocommerce {{element}} .flex-viewport' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
				],
			),
		),
		'thumbnails' => array(
			'disable_gallery' => array(
				'type' => 'checkbox',
				'label' => __pl('disable_gallery'),
				'css' => ['{{element}} ol.flex-control-thumbs' => 'display:none !important'],
			),
			'gposition' => array(
				'label' => __pl('gallery_position'),
				'type' => 'radio',
				'default' => 'bottom',
				'list' => array(
					'left' => __pl('left'),
					'top' => __pl('top'),
					'right' => __pl('right'),
					'bottom' => __pl('bottom')
				),
				'req' => array(
					'disable_gallery' => '',
				),
			),
			'thumb_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['.woocommerce {{element}} .flex-control-thumbs img' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'req' => array(
					'disable_gallery' => '',
				),
			),
			'thumb_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['.woocommerce {{element}} .flex-control-thumbs img' => 'border-color: {{val}};'],
				'req' => array(
					'disable_gallery' => '',
					'!thumb_border_type' => ''
				),
			),
			'thumb_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['.woocommerce {{element}} .flex-control-thumbs img' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'disable_gallery' => '',
					'!thumb_border_type' => ''
				],
			),
			'thumb_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => [ 'px', '%' ],
				'css' => ['.woocommerce {{element}} .flex-control-thumbs img' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
				'req' => array(
					'disable_gallery' => '',
				),
			),
			'thumb_space' => array(
				'type' => 'slider',
				'label' => __pl('horizontal_space'),
				'units' => [ 'px', '%' ],
				'css' => ['.woocommerce {{element}} .flex-control-thumbs li' => 'padding-right: calc({{val}} / 2); padding-left: calc({{val}} / 2); padding-bottom: {{val}}',
					'.woocommerce {{element}} .flex-control-thumbs' => 'margin-right: calc(-{{val}} / 2); margin-left: calc(-{{val}} / 2)'
				],
				'req' => array(
					'disable_gallery' => '',
				),
			),
			'thumb_vertical_space' => array(
				'type' => 'slider',
				'label' => __pl('vertical_space'),
				'units' => ['px','%'],
				'css' => ['.woocommerce {{element}} .flex-control-thumbs li' => 'padding-top: {{val}} ; padding-bottom: {{val}};',
					'.woocommerce {{element}} .flex-control-thumbs' => 'margin-top: calc(-{{val}} / 2); margin-bottom: calc(-{{val}} / 2)'
				],
				'req' => array(
					'disable_gallery' => '',
				),
			),
		),
		'styles' => [
			'thumbnails' => __pl('thumbnails'),
		],
	)
);

// Product Price
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_price', array(
		'name' => __pl('product_price'),
		'group' => 'woocommerce',
		'html' => '<div class=" product pagelayer-product-price-container">
		{{pagelayer-product-price}}
		</div>',
		'params' => array(
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'css' => ['.woocommerce {{element}} .pagelayer-product-price-container' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
			),
			'price_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .price .woocommerce-Price-amount' => 'color: {{val}} !important',
					'.woocommerce {{element}} .price' => 'color: {{val}} !important'
				],
			),
			'price_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['.woocommerce {{element}} .price .woocommerce-Price-amount' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
					'.woocommerce {{element}} .price' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
		),
		'sale_price' => array(
			'sale_price_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .price ins .woocommerce-Price-amount' => 'color: {{val}} !important',
					'.woocommerce {{element}} .price ins' => 'color: {{val}} !important'
				],
			),
			'sale_price_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['.woocommerce {{element}} .price ins .woocommerce-Price-amount' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
					'.woocommerce {{element}} .price ins .woocommerce-Price-amount' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'sale_space_bottom' => array(
				'type' => 'checkbox',
				'label' => __pl('space_bottom'),
				'addAttr' => ['{{element}} .price' => 'pagelayer-bottom="yes"'],
			),
			'sale_price_space' => array(
				'type' => 'slider',
				'label' => __pl('space'),
				'units' => [ 'px', '%' ],
				'step' => 0.1,
				'css' => ['body:not(.rtl) {{element}} .price:not([pagelayer-bottom="yes"]) del' => 'margin-right: {{val}}',
					'body.rtl {{element}} .price:not([pagelayer-bottom="yes"]) del' => 'margin-right: {{val}}',
					'{{element}} .price[pagelayer-bottom="yes"] del' => 'margin-bottom: {{val}}'
				],
			),
		),
		'styles' => [
			'sale_price' => __pl('sale_price'),
		],
	)
);

// Add to cart
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_add_to_cart', array(
		'name' => __pl('add_to_cart'),
		'group' => 'woocommerce',
		'html' => '<div class=" product pagelayer-add-to-cart-holder">{{product_add_to_cart}}<div>',
		'params' => array(
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'css' => ['.woocommerce {{element}}' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
			),
			'cart_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['.woocommerce {{element}} .cart button' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'cart_colors' => array(
				'type' => 'radio',
				'label' => __pl('color'),
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
			),
			'cart_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .cart button' => 'color: {{val}} !important'],
				'show' => ['cart_colors' => ''],
			),
			'cart_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} .cart button' => 'background-color: {{val}} !important'],
				'show' => ['cart_colors' => ''],
			),
			'cart_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['.woocommerce {{element}} .cart button' => 'border-color: {{val}} !important'],
				'show' => ['cart_colors' => ''],
			),
			'cart_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .cart button:hover' => 'color: {{val}} !important'],
				'show' => ['cart_colors' => 'hover'],
			),
			'cart_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} .cart button:hover' => 'background-color: {{val}} !important'],
				'show' => ['cart_colors' => 'hover'],
			),
			'cart_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['.woocommerce {{element}} .cart button:hover' => 'border-color: {{val}} !important'],
				'show' => ['cart_colors' => 'hover'],
			),
			'cart_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['.woocommerce {{element}} .cart button' => 'border-style: {{val}}',
				],
				'list' => [
					'' => __pl('default'),
					'none' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'cart_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['.woocommerce {{element}} .cart button' => 'border-top-width: {{val[0]}}px !important; border-right-width: {{val[1]}}px !important; border-bottom-width: {{val[2]}}px !important; border-left-width: {{val[3]}}px !important',
				],
				'req' => [
					'!cart_border_type' => ['', 'none'],
				],
			),
			'cart_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => [ 'px', '%' ],
				'css' => ['.woocommerce {{element}} .cart button' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;',
				],
			),
			'cart_border_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => [ 'px', '%', 'em' ],
				'css' => ['.woocommerce {{element}} .cart button' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;'],
			),
		),
		'cart_quantity' => array(
			'quantity_space' => array(
				'type' => 'slider',
				'label' => __pl('space'),
				'units' => [ 'px', 'em' ],
				'step' => 0.1,
				'css' => ['body:not(.rtl) {{element}} .quantity + .button' => 'margin-left: {{val}}',
					'body.rtl {{element}} .quantity + .button' => 'margin-right: {{val}}'
				],
			),
			'quantity_width' => array(
				'type' => 'slider',
				'label' => __pl('Width'),
				'units' => [ 'px', 'em' ],
				'step' => 0.1,
				'css' => ['.woocommerce {{element}}  .quantity .qty' => 'width: {{val}} !important'],
			),
			'quantity_typo' => array(
				'type' => 'typography',
				'label' => __pl('heading_typo'),
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'quantity_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),
				'list' => array(
					'' => __pl('normal'),
					'focus' => __pl('focus'),
				),
			),
			'quantity_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'color: {{val}} !important'],
				'show' => ['quantity_colors' => ''],
			),
			'quantity_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'background-color: {{val}} !important'],
				'show' => ['quantity_colors' => ''],
			),
			'quantity_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'border-color: {{val}} !important'],
				'show' => ['quantity_colors' => ''],
			),
			'quantity_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .quantity .qty:focus' => 'color: {{val}} !important'],
				'show' => ['quantity_colors' => 'focus'],
			),
			'quantity_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} .quantity .qty:focus' => 'background-color: {{val}} !important'],
				'show' => ['quantity_colors' => 'focus'],
			),
			'quantity_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .quantity .qty:focus' => 'border-color: {{val}} !important'],
				'show' => ['quantity_colors' => 'focus'],
			),
			'quantity_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'border-style: {{val}} !important',
				],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'quantity_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'border-top-width: {{val[0]}}px !important; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px !important; border-left-width: {{val[3]}}px !important',
				],
				'req' => [
					'!quantity_border_type' => ''
				],
			),
			'quantity_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => [ 'px', '%' ],
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;',
				],
			),
			'quantity_border_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => [ 'px', '%', 'em' ],
				'css' => ['.woocommerce {{element}} .quantity .qty' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;'],
			),
		),
		'cart_variations' => array(
			'variations_space' => array(
				'type' => 'slider',
				'label' => __pl('space'),
				'units' => [ 'px', 'em' ],
				'step' => 0.1,
				'css' => ['.woocommerce {{element}} form.cart .variations' => 'margin-bottom: {{val}} !important'],
			),
			'variations_space_between' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'units' => [ 'px', 'em' ],
				'step' => 0.1,
				'css' => ['.woocommerce {{element}} form.cart table.variations tr:not(:last-child)' => 'margin-bottom: {{val}} !important'],
			),
			'variations_label' => array(
				'type' => 'heading',
				'label' => __pl('label'),
			),
			'variations_label_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['.woocommerce {{element}} form.cart table.variations label' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'variations_label_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} form.cart table.variations label' => 'color: {{val}} !important'],
			),
			'sel_label' => array(
				'type' => 'heading',
				'label' => __pl('select'),
			),
			'variations_sel_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['.woocommerce {{element}} form.cart table.variations td.value select' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'variations_sel_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} form.cart table.variations td.value select' => 'color: {{val}} !important'],
			),
			'variations_sel_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} form.cart table.variations td.value select' => 'background-color: {{val}} !important'],
			),
			'variations_border_sel_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['.woocommerce {{element}} form.cart table.variations td.value:before' => 'border-color: {{val}} !important'],
			),
			'variations_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => [ 'px', '%' ],
				'css' => ['.woocommerce {{element}} form.cart table.variations td.value select' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;',
				],
			),
			
		),
		'styles' => [
			'cart_quantity' => __pl('quantity'),
			'cart_variations' => __pl('variations'),
		],
	)
);

// Product Rating
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_rating', array(
		'name' => __pl('product_rating'),
		'group' => 'woocommerce',
		'html' => '<div class=" product pagelayer-product-rating">{{product_rating}}</div>',
		'params' => array(
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'css' => ['.woocommerce {{element}}' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
			),
			'star_color' => array(
				'type' => 'color',
				'label' => __pl('star_color'),
				'css' => ['.woocommerce {{element}} .star-rating' => 'color: {{val}}'],
			),
			'star_emp_color' => array(
				'type' => 'color',
				'label' => __pl('empty_star_color'),
				'css' => ['.woocommerce {{element}} .star-rating::before' => 'color: {{val}}'],
			),
			'review_link_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'css' => ['.woocommerce {{element}} .woocommerce-review-link' => 'color: {{val}}'],
			),
			'typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['.woocommerce {{element}} .woocommerce-review-link' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'star_size' => array(
				'type' => 'slider',
				'label' => __pl('star_size'),
				'units' => [ 'px', 'em' ],
				'step' => 0.1,
				'css' => ['.woocommerce {{element}} .star-rating' => 'font-size:{{val}}'],
			),
			'space_between' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'units' => [ 'px', 'em' ],
				'step' => 0.1,
				'css' => [
					'.woocommerce:not(.rtl) {{element}} .star-rating' => 'margin-right: {{val}}',
					'.woocommerce.rtl {{element}} .star-rating' => 'margin-left: {{val}}'
				],
			),
		),
		'review_link' => array(
			'disable_review_link' => array(
				'type' => 'checkbox',
				'label' => __pl('disable_link'),
				'css' => ['.woocommerce {{element}} .woocommerce-review-link' => 'display:none !important'],
			),
			'link_hover' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'list' => array(
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => array(
					'disable_review_link' => '',
				),
			),
			'link_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'css' => ['.woocommerce {{element}} .woocommerce-review-link' => 'color: {{val}}; transition: all 0.5s ;'],
				'req' => array(
					'disable_review_link' => '',
				),
				'show' => ['link_hover' => ''],
			),
			'link_hover_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'css' => ['.woocommerce {{element}} .woocommerce-review-link:hover' => 'color: {{val}} !important;'],
				'req' => array(
					'disable_review_link' => '',
				),
				'show' => ['link_hover' => 'hover'],
			),
		),
		'styles' => [
			'review_link' => __pl('Review Link'),

		]
	)
);

// Product Meta
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_meta', array(
		'name' => __pl('product_meta'),
		'group' => 'woocommerce',
		'html' => '<div class=" product pagelayer-product-meta pagelayer-meta-{{display}}">{{product_meta}}</div>',
		'params' => array(
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'css' => ['.woocommerce {{element}} .pagelayer-product-meta' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
			),
			'display' => array(
				'type' => 'radio',
				'label' => __pl('display'),
				'css' => ['.woocommerce {{element}} .product_meta > span' => 'display: {{val}}'],
				'list' => array(
					'inline-block' => __pl('inline_block'),
					'block' => __pl('block'),
				),
			),
			'space_between' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'step' => 0.1,
				'css' => [
					'.woocommerce {{element}} .pagelayer-product-meta:not(.pagelayer-meta-block) .product_meta > span:not(:first-child)' => 'margin-left: {{val}}px',
					'.woocommerce {{element}} .pagelayer-product-meta.pagelayer-meta-block .product_meta > span:not(:last-child)' => 'margin-bottom: {{val}}px',
				],
			),
			'meta_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .product_meta span' => 'color: {{val}}'],
			),
			'typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['.woocommerce {{element}} .product_meta span' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'link_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'css' => ['.woocommerce {{element}} .product_meta a' => 'color: {{val}}'],
			),
			'linl_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['.woocommerce {{element}} .product_meta a' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			
		),
	)
);

// Product short description
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_short_desc', array(
		'name' => __pl('product_short_desc'),
		'group' => 'woocommerce',
		'html' => '<div class=" product pagelayer-product-short-desc">{{product_short_desc}}</div>',
		'params' => array(
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'css' => ['.woocommerce {{element}} .pagelayer-product-short-desc' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
			),
			'meta_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .pagelayer-product-short-desc *' => 'color: {{val}}'],
			),
			'typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['.woocommerce {{element}} .pagelayer-product-short-desc' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),			
		),
	)
);

// Products style
$products_style = array(
	'column_gap' => array(
		'type' => 'slider',
		'label' => __pl('column_gap'),
		'units' => ['px', '%'],
		'screen' => 1,
		'default' => 20,
		'step' => 0.2,
		'max' => 100,
		'css' => ['{{element}} ul.products li.product' => 'margin-right: {{val}}'],
	),
	'row_gap' => array(
		'type' => 'slider',
		'label' => __pl('row_gap'),
		'units' => ['px', '%'],
		'screen' => 1,
		'default' => 20,
		'step' => 0.2,
		'max' => 100,
		'css' => ['{{element}} ul.products li.product' => 'margin-bottom: {{val}}'],
	),
	'align' => array(
		'type' => 'radio',
		'label' => __pl('alignment'),
		'list' => array(
			'left' => __pl('left'),
			'center' => __pl('center'),
			'right' => __pl('right'),
		),
		'addAttr' => ['{{element}} .pagelayer-product-related-container' => 'pagelayer-content-align="{{align}}"'],
		'css' => ['{{element}} ul.products li.product' => 'text-align:{{val}}'],
	),
	'img_lable' => array(
		'type' => 'heading',
		'label' => __pl('image'),
	),
	'img_border_type' => array(
		'type' => 'select',
		'label' => __pl('border_type'),
		'css' => ['{{element}} .attachment-woocommerce_thumbnail' => 'border-style: {{val}}',
		],
		'list' => [
			'' => __pl('none'),
			'solid' => __pl('solid'),
			'double' => __pl('double'),
			'dotted' => __pl('dotted'),
			'dashed' => __pl('dashed'),
			'groove' => __pl('groove'),
		],
	),
	'img_border_width' => array(
		'type' => 'padding',
		'label' => __pl('border_width'),
		'screen' => 1,
		'css' => ['{{element}} .attachment-woocommerce_thumbnail' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'
		],
		'req' => [
			'!img_border_type' => ''
		],
	),
	'img_border_color_hover' => array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => ['{{element}} .attachment-woocommerce_thumbnail' => 'border-color: {{val}}'],
		'show' => ['img_border_type' => ''],
	),
	'img_border_radius' => array(
		'type' => 'padding',
		'label' => __pl('border_radius'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} .attachment-woocommerce_thumbnail' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
		],
	),
	'img_spacing' => array(
		'type' => 'slider',
		'label' => __pl('spacing'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} .attachment-woocommerce_thumbnail' => 'margin-bottom: {{val}}'],
	),
	'title_lable' => array(
		'type' => 'heading',
		'label' => __pl('title'),
	),
	'title_color' => array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => [
			'{{element}} ul.products li.product .woocommerce-loop-product__title' => 'color: {{val}}', 
			'{{element}} ul.products li.product .woocommerce-loop-category__title' => 'color: {{val}}' 
		],
	),
	'title_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => [
			'{{element}}  ul.products li.product .woocommerce-loop-product__title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
			'{{element}}  ul.products li.product .woocommerce-loop-category__title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
		],
	),
	'title_spacing' => array(
		'type' => 'slider',
		'label' => __pl('spacing'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => [
			'{{element}} ul.products li.product .woocommerce-loop-product__title' => 'margin-bottom: {{val}}', 
			'{{element}} ul.products li.product .woocommerce-loop-category__title' => 'margin-bottom: {{val}}' 
		],
	),
	'rating_lable' => array(
		'type' => 'heading',
		'label' => __pl('stars_rating'),
	),
	'star_color' => array(
		'type' => 'color',
		'label' => __pl('star_color'),
		'css' => ['{{element}} ul.products li.product .star-rating' => 'color: {{val}}'],
	),
	'empty_star_color' => array(
		'type' => 'color',
		'label' => __pl('empty_star_color'),
		'css' => ['{{element}} ul.products li.product .star-rating::before' => 'color: {{val}}'],
	),
	'star_size' => array(
		'type' => 'slider',
		'label' => __pl('star_size'),
		'max' => 5,
		'step' => 0.1,
		'css' => ['{{element}} ul.products li.product .star-rating' => 'font-size: {{val}}em'],
	),
	'star_spacing' => array(
		'type' => 'slider',
		'label' => __pl('spacing'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product .star-rating' => 'margin-bottom: {{val}}'],
	),
	'price_lable' => array(
		'type' => 'heading',
		'label' => __pl('price_style'),
	),
	'price_color' => array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => [
			'{{element}} ul.products li.product .price' => 'color: {{val}}',
			'{{element}} ul.products li.product .price ins' => 'color: {{val}}',
			'{{element}} ul.products li.product .price ins .amount' => 'color: {{val}}',
		],
	),
	'price_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => ['{{element}} ul.products li.product .price' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
	),
	'reg_price_lable' => array(
		'type' => 'heading',
		'label' => __pl('regular_price'),
	),
	'reg_price_color' => array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => [
			'{{element}} ul.products li.product .price del .amount' => 'color:{{val}}',
			'{{element}} ul.products li.product .price del' => 'color:{{val}}'
		]
	),
	'reg_price_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => [
			'{{element}} ul.products li.product .price del .amount' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
			'{{element}} ul.products li.product .price del' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
		],
	),
	'button_lable' => array(
		'type' => 'heading',
		'label' => __pl('button'),
	),
	'button_colors'=> array(
		'type' => 'radio',
		'label' => '',
		'list' => array(
			'' => __pl('normal'),
			'hover' => __pl('hover'),
		),
	),
	'btn_color'=> array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => ['{{element}} ul.products li.product .button' => 'color:{{val}}'],
		'show' => [ 'button_colors' => '' ],
	),
	'btn_bg_color'=> array(
		'type' => 'color',
		'label' => __pl('bg_color'),
		'css' => ['{{element}} ul.products li.product .button' => 'background-color:{{val}}'],
		'show' => [ 'button_colors' => '' ],
	),
	'btn_border_color'=> array(
		'type' => 'color',
		'label' => __pl('border_color'),
		'css' => ['{{element}} ul.products li.product .button' => 'border-color:{{val}}'],
		'show' => [ 'button_colors' => '' ],
	),
	'btn_hover_color'=> array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => ['{{element}} ul.products li.product .button:hover' => 'color:{{val}}'],
		'show' => [ 'button_colors' => 'hover' ],
	),
	'btn_bg_hover_color'=> array(
		'type' => 'color',
		'label' => __pl('bg_color'),
		'css' => ['{{element}} ul.products li.product .button:hover' => 'background-color:{{val}}'],
		'show' => [ 'button_colors' => 'hover' ],
	),
	'btn_border_hover_color'=> array(
		'type' => 'color',
		'label' => __pl('border_color'),
		'css' => ['{{element}} ul.products li.product .button:hover' => 'border-color:{{val}}'],
		'show' => [ 'button_colors' => 'hover' ],
	),
	'btnb_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => ['{{element}} ul.products li.product .button' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
	),
	'btn_border_type' => array(
		'type' => 'select',
		'label' => __pl('border_type'),
		'css' => ['{{element}} ul.products li.product .button' => 'border-style: {{val}}',
		],
		'list' => [
			'' => __pl('none'),
			'solid' => __pl('solid'),
			'double' => __pl('double'),
			'dotted' => __pl('dotted'),
			'dashed' => __pl('dashed'),
			'groove' => __pl('groove'),
		],
	),
	'btn_border_width' => array(
		'type' => 'padding',
		'label' => __pl('border_width'),
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product .button' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px',
		],
		'req' => [
			'!btn_border_type' => ''
		],
	),
	'btn_border_radius' => array(
		'type' => 'padding',
		'label' => __pl('border_radius'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product .button' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
		],
	),
	'btn_text_padding' => array(
		'type' => 'padding',
		'label' => __pl('padding'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product .button' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
		],
	),
	'btn_spacing' => array(
		'type' => 'slider',
		'label' => __pl('spacing'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product .button' => 'margin-top:{{val}}'],
	),			
	'view_cart_lable' => array(
		'type' => 'heading',
		'label' => __pl('view_cart'),
	),
	'view_cart_color' => array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => ['{{element}} .added_to_cart' => 'color: {{val}}'],
	),
	'view_cart_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => ['{{element}} .added_to_cart' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
	),
);

// Products heading style
$products_heading = array(
	'heading_show'=> array(
		'type' => 'checkbox',
		'label' => __pl('heading_style'),
		'default' => 'true',
		'addAttr' => ['{{element}} .pagelayer-product-related-container' => 'pagelayer-heading-show="{{heading_show}}"'],
	),
	'heading_color'=> array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => ['{{element}} .products > h2' => 'color: {{val}}'],
		'req' => ['heading_show' => 'true'],
	),
	'heading_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => ['{{element}} .products > h2' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
		'req' => ['heading_show' => 'true'],
	),
	'heading_align'=> array(
		'type' => 'radio',
		'label' => __pl('alignment'),
		'list' => array(
			'left' => __pl('left'),
			'center' => __pl('center'),
			'right' => __pl('right'),
		),
		'css' => ['{{element}} .products > h2' => 'text-align: {{val}}'],
		'req' => ['heading_show' => 'true'],
	),
	'heading_spacing' => array(
		'type' => 'slider',
		'label' => __pl('spacing'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} .products > h2' => 'margin-bottom: {{val}}'],
		'req' => ['heading_show' => 'true'],
	),	
);

// Products box style
$products_box = array(
	'box_border_type' => array(
		'type' => 'select',
		'label' => __pl('border_type'),
		'css' => ['{{element}} ul.products li.product' => 'border-style: {{val}}',
		],
		'list' => [
			'' => __pl('none'),
			'solid' => __pl('solid'),
			'double' => __pl('double'),
			'dotted' => __pl('dotted'),
			'dashed' => __pl('dashed'),
			'groove' => __pl('groove'),
		],
	),
	'box_border_width' => array(
		'type' => 'padding',
		'label' => __pl('border_width'),
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px',
		],
		'req' => [
			'!box_border_type' => ''
		],
	),
	'box_border_radius' => array(
		'type' => 'padding',
		'label' => __pl('border_radius'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
		],
	),
	'box_padding' => array(
		'type' => 'padding',
		'label' => __pl('padding'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} ul.products li.product' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
		],
	),
	'box_colors' => array(
		'type' => 'radio',
		'label' => '',
		'list' => array(
			'' => __pl('normal'),
			'hover' => __pl('hover'),
		),
	),
	'box_bg_color' => array(
		'type' => 'color',
		'label' => __pl('bg_color'),
		'css' => ['{{element}} ul.products li.product' => 'background-color: {{val}}'],
		'show' => ['box_colors' => ''],
	),
	'box_border_color' => array(
		'type' => 'color',
		'label' => __pl('border_color'),
		'css' => ['{{element}} ul.products li.product' => 'border-color: {{val}}'],
		'show' => ['box_colors' => ''],
	),
	'box_shadow' => [
		'type' => 'box_shadow',
		'label' => __pl('shadow'),
		'css' => ['{{element}} ul.products li.product' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}} !important;'],
		'show' => ['box_colors' => ''],
	],
	'box_bg_hover_color' => array(
		'type' => 'color',
		'label' => __pl('bg_color'),
		'css' => ['{{element}} ul.products li.product:hover' => 'background-color: {{val}}'],
		'show' => ['box_colors' => 'hover'],
	),
	'box_border_hover_color' => array(
		'type' => 'color',
		'label' => __pl('border_color'),
		'css' => ['{{element}} ul.products li.product:hover' => 'border-color: {{val}}'],
		'show' => ['box_colors' => 'hover'],
	),
	'box_hover_shadow' => [
		'type' => 'box_shadow',
		'label' => __pl('shadow'),
		'css' => ['{{element}} ul.products li.product:hover' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}} !important;'],
		'show' => ['box_colors' => 'hover'],
	],
);

// Products sale flash style
$products_sale_flash = array(
	'sale_flash' => array(
		'type' => 'checkbox',
		'label' => __pl('sale_flash'),
		'default' => 'true', 
		'addAttr' => ['{{element}} .pagelayer-product-related-container' => 'pagelayer-sale-flash="{{sale_flash}}"'],
	),
	'flash_color' => array(
		'type' => 'color',
		'label' => __pl('color'),
		'css' => ['{{element}} ul.products li.product span.onsale' => 'color: {{val}}'],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_bg_color' => array(
		'type' => 'color',
		'label' => __pl('bg_color'),
		'css' => ['{{element}} ul.products li.product span.onsale' => 'background-color: {{val}}'],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_typo' => array(
		'type' => 'typography',
		'label' => __pl('typography'),
		'css' => ['{{element}} ul.products li.product span.onsale' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_border_radius' => array(
		'type' => 'padding',
		'label' => __pl('border_radius'),
		'units' => [ 'px', '%' ],
		'screen' => 1,
		'css' => ['{{element}} .ul.products li.product span.onsale' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
		],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_width' => array(
		'type' => 'slider',
		'label' => __pl('width'),
		'units' => [ 'px', '%' ],
		'css' => ['{{element}} ul.products li.product span.onsale' => 'min-width: {{val}};'],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_height' => array(
		'type' => 'slider',
		'label' => __pl('height'),
		'units' => [ 'px', '%' ],
		'css' => ['{{element}} ul.products li.product span.onsale' => 'min-height: {{val}}; line-height: {{val}};'],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_distance' => array(
		'type' => 'slider',
		'label' => __pl('distance'),
		'units' => [ 'px', '%' ],
		'max' => 20,
		'css' => ['{{element}} ul.products li.product span.onsale' => 'margin: {{val}};'],
		'req' => [ 'sale_flash' => 'true'],
	),
	'flash_position' => array(
		'type' => 'radio',
		'label' => __pl('position'),
		'list' => array(
			'left' => __pl('left'),
			'right' => __pl('right'),
		),
		'css' => ['{{element}} ul.products li.product span.onsale' => 'left:auto; right:auto; {{val}} : 0;'],
		'req' => [ 'sale_flash' => 'true'],
	),
);

// Product related
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_related', array(
		'name' => __pl('related_upsell_products'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-product-related-container">
			{{related_products}}
		</div>',
		'params' => array(
			'select_product' => array(
				'type' => 'select',
				'label' => __pl('product_type'),
				'default' => 'related',
				'list' => array(
					'related' => __pl('related'),
					'upsell' => __pl('upsell'),
				),
			),
			'posts_per_page' => array(
				'type' => 'spinner',
				'label' => __pl('products_per_page'),
				'default' => 4,
				'max' => 20,
				'req' => ['select_product' => 'related'],
			),
			'columns' => array(
				'type' => 'spinner',
				'label' => __pl('columns'),
				'screen' => 1,
				'default' => 4,
				'min' => 1,
				'max' => 6,
			),		
			'order_by' => array(
				'type' => 'select',
				'label' => __pl('order_by'),
				'default' => 'date',
				'list' => array(
					'date' => __pl('date'),
					'title' => __pl('title'),
					'price' => __pl('price'),
					'popularity' => __pl('popularity'),
					'rating' => __pl('rating'),
					'rand' => __pl('random'),
					'menu_order' => __pl('menu_order'),
				),
			),
			'order' => array(
				'type' => 'select',
				'label' => __pl('order'),
				'default' => 'asc',
				'list' => array(
					'asc' => __pl('asc'),
					'desc' => __pl('desc'),
				),
			),
		),
		'products_style' => $products_style,
		'heading_style' => $products_heading,
		'box_style' => $products_box,
		'sale_flash_style' => $products_sale_flash,
		'styles' =>[
			'products_style' => __pl('products_style'),
			'heading_style' => __pl('heading_styles'),
			'box_style' => __pl('box_style'),
			'sale_flash_style' => __pl('sale_flash_style'),
		],
	)
);

// woocommerce breadcrumb
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_woo_breadcrumb', array(
		'name' => __pl('woo_breadcrumb'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-woo-breadcrumb-container">'. pagelayer_woo_breadcrumb() .'</div>',
		'params' => array(
			'color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-breadcrumb' => 'color:{{val}}'],
 			),
			'link_color' => array(
				'type' => 'color',
				'label' => __pl('link_color'),
				'css' => ['{{element}} .woocommerce-breadcrumb > a' => 'color:{{val}}'],
 			),
			'typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .woocommerce-breadcrumb' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
			),
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'css' => ['{{element}} .woocommerce-breadcrumb' => 'text-align:{{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
 			),
		),
	)
);

// Product pages
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_woo_pages', array(
		'name' => __pl('woo_pages'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-product-pages-container">{{page_content}}</div>',
		'params' => array(
			'pages' => array(
				'type' => 'select',
				'label' => __pl('pages'),
				'css' => ['{{element}} .woocommerce-breadcrumb' => 'color:{{val}}'],
				'list' => array(
					'' => __pl( 'Select' ),
					'woocommerce_cart' => __pl('cart_page'),
					//'product_page' => __pl('single_product_page'),
					'woocommerce_checkout' => __pl('checkout_page'),
					'woocommerce_order_tracking' => __pl('order_tracking_form'),
					'woocommerce_my_account' => __pl('my_account'),
				),
 			),
		),
	)
);

// Product pages
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_categories', array(
		'name' => __pl('product_categories'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-product-categories-container">{{product_categories}}</div>',
		'params' => array(
			'columns' => array(
				'type' => 'spinner',
				'label' => __pl('columns'),
				'screen' => 1,
				'default' => 4,
				'max' => 12,
			),
			'number' => array(
				'type' => 'spinner',
				'label' => __pl('limit'),
				'default' => 4,
			),
			'source' => array(
				'type' => 'select',
				'label' => __pl('source'),
				'list' => array(
					'' => __pl('show_all'),
					'by_id' => __pl('manual_selection'),
					'by_parent' => __pl('by_parent'),
					'current_subcategories' => __pl('current_subcategories'),
				),
			),
			'by_id' => array(
				'type' => 'multiselect',
				'label' => __pl('categories'),
				'list' => pagelayer_get_product_cat(),
				'req' => ['source' => 'by_id'],
			),
			'parent' => array(
				'type' => 'select',
				'label' => __pl('parent'),
				'list' => [ '0' => __pl('only_top_level') ] + pagelayer_get_product_cat(),
				'req' => ['source' => 'by_parent'],
			),
			'hide_empty' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_empty'),
			),
			'orderby' => array(
				'type' => 'select',
				'label' => __pl('order_by'),
				'default' => 'name',
				'list' => array(
					'name' => __pl('name'),
					'slug' => __pl('slug'),
					'description' => __pl('description'),
					'count' => __pl('count'),
				),
			),
			'order' => array(
				'type' => 'select',
				'label' => __pl('order'),
				'default' => 'desc',
				'list' => array(
					'asc' => __pl('asc'),
					'desc' => __pl('desc'),
				),
			),
		),
		'products_style' => array(
			'column_gap' => array(
				'type' => 'slider',
				'label' => __pl('column_gap'),
				'units' => ['px', '%'],
				'screen' => 1,
				'default' => 20,
				'step' => 0.2,
				'max' => 100,
				'css' => ['{{element}} ul.products li.product' => 'margin-right: {{val}}'],
			),
			'row_gap' => array(
				'type' => 'slider',
				'label' => __pl('row_gap'),
				'units' => ['px', '%'],
				'screen' => 1,
				'default' => 20,
				'step' => 0.2,
				'max' => 100,
				'css' => ['{{element}} ul.products li.product' => 'margin-bottom: {{val}}'],
			),
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'css' => ['{{element}} ul.products li.product' => 'text-align:{{val}}'],
			),
			'img_lable' => array(
				'type' => 'heading',
				'label' => __pl('image'),
			),
			'img_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} a > img' => 'border-style: {{val}}',
				],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'img_border_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} a > img' => 'border-color: {{val}}'],
				'show' => ['!img_border_type' => ''],
			),
			'img_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} a > img' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'
				],
				'req' => [
					'!img_border_type' => ''
				],
			),
			'img_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => [ 'px', '%' ],
				'screen' => 1,
				'css' => ['{{element}} a > img' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};',
				],
			),
			'img_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'units' => [ 'px', '%' ],
				'screen' => 1,
				'css' => ['{{element}} a > img' => 'margin-bottom: {{val}}'],
			),
			'title_lable' => array(
				'type' => 'heading',
				'label' => __pl('title'),
			),
			'title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-loop-category__title' => 'color: {{val}}'],
			),
			'title_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-loop-category__title' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'title_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'units' => [ 'px', '%' ],
				'screen' => 1,
				'css' => [
					'{{element}} .woocommerce-loop-category__title' => 'margin-bottom: {{val}}' 
				],
			),
			'count_lable' => array(
				'type' => 'heading',
				'label' => __pl('count'),
			),
			'count_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-loop-category__title .count' => 'color: {{val}}'],
			),
			'count_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-loop-category__title .count' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
		),
		'styles' =>[
			'products_style' => __pl('products_style'),
		],
	)
);

// Archives Product
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_archives', array(
		'name' => __pl('product_archives'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-product-archives-container">{{product_archives}}</div>',
		'params' => array(
			'allow_order' => array(
				'type' => 'checkbox',
				'label' => __pl('allow_order'),
				'default' => 'true',
 			),
			'show_result' => array(
				'type' => 'checkbox',
				'label' => __pl('show_result_counter'),
				'default' => 'true',
 			),
			'no_found' => array(
				'type' => 'textarea',
				'label' => __pl('no_found_msg'),
				'default' => __pl('Products not found.'),
 			),
		),
		'products_style' => $products_style,
		'box_style' => $products_box,
		'sale_flash_style' => $products_sale_flash,
		'pagination_style' => array(
			'pagination_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'css' => ['{{element}} nav.woocommerce-pagination' => 'margin-top:{{val}}px'],
 			),
			'pagination_border' => array(
				'type' => 'checkbox',
				'label' => __pl('border'),
				//'css' => ['{{element}} nav.woocommerce-pagination' => 'margin-top:{{val}}px'],
 			),
			'pagination_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => [
					'{{element}}nav.woocommerce-pagination ul' => 'border-color:{{val}}',
					'{{element}} nav.woocommerce-pagination ul li' => 'border-right-color: {{val}}; border-left-color: {{val}}',
				],
 			),
			'pagination_padding' => array(
				'type' => 'slider',
				'label' => __pl('padding'),
				'screen' => 1,
				'units' => [ 'em' ],
				'min' => 0,
				'max' => 4,
				'step' => 0.1,
				'css' => [
					'{{element}} nav.woocommerce-pagination ul li a' => 'padding: {{val}}',
					'{{element}} nav.woocommerce-pagination ul li span' => 'padding: {{val}}'
				],
 			),
			'pagination_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} nav.woocommerce-pagination' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'pagination_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
					'active' => __pl('active'),
				),
 			),
			'pagination_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a' => 'color:{{val}}'],
				'show' => ['pagination_colors' => 'normal'],
 			),
			'pagination_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a' => 'background-color:{{val}}'],
				'show' => ['pagination_colors' => 'normal'],
 			),
			'pagination_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a:hover' => 'color:{{val}}'],
				'show' => ['pagination_colors' => 'hover'],
 			),
			'pagination_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a:hover' => 'background-color:{{val}}'],
				'show' => ['pagination_colors' => 'hover'],
 			),
			'pagination_color_active' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => [ '{{element}} nav.woocommerce-pagination ul li span.current' => 'color:{{val}};' ],
				'show' => ['pagination_colors' => 'active'],
 			),
			'pagination_bg_color_active' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li span.current' => 'background-color:{{val}}'],
				'show' => ['pagination_colors' => 'active'],
 			),
			
		),
		'no_found_style' => array(
			'nf_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-product-no-found' => 'color:{{val}}'],
 			),
			'nf_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .pagelayer-product-no-found' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
		),
		'styles' =>[
			'products_style' => __pl('products_style'),
			'box_style' => __pl('box_style'),
			'pagination_style' => __pl('pagination_style'),
			'sale_flash_style' => __pl('sale_flash_style'),
			'no_found_style' => __pl('no_found_style'),
		],
	)
);

// Products
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_products', array(
		'name' => __pl('products'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-products-container">{{products_content}}</div>',
		'params' => array(
			'columns' => array(
				'type' => 'spinner',
				'label' => __pl('columns'),
				'screen' => 1,
				'default' => 4,
				'max' => 12,
			),
			'rows' => array(
				'type' => 'spinner',
				'label' => __pl('rows'),
				'default' => 4,
			),
			'paginate' => array(
				'type' => 'checkbox',
				'label' => __pl('pagination'),
			),
			'allow_order' => array(
				'type' => 'checkbox',
				'label' => __pl('allow_order'),
				'req' => ['paginate' => 'true'], 
 			),
			'show_result' => array(
				'type' => 'checkbox',
				'label' => __pl('show_result_counter'),
				'req' => ['paginate' => 'true'],
 			),
			'no_found' => array(
				'type' => 'textarea',
				'label' => __pl('no_found_msg'),
				'default' => __pl('Products not found.'),
 			),
		),
		'query' => array(
			'source' => array(
				'type' => 'select',
				'label' => __pl('source'),
				'default' => 'recent_products',
				'list' => array(
					'pagelayer_current_query' => __pl('currunt_query'),
					'recent_products' => __pl('recent_products'),
					'sale_products' => __pl('sale_products'),
					'best_selling_products' => __pl('best_selling_products'),
					'top_rated_products' => __pl('top_rated_products'),
					'featured_products' => __pl('featured_product'),
					'by_id' => __pl('manual_selection'),
				),
			),
			'ids' => array(
				'type' => 'multiselect',
				'label' => __pl('products'),
				'list' => pagelayer_post_list_by_type('product'),
				'req' => ['source' => 'by_id'],
			),
			'orderby' => array(
				'type' => 'select',
				'label' => __pl('order_by'),
				'default' => 'date',
				'list' => array(
					'date' => __pl('date'),
					'title' => __pl('title'),
					'price' => __pl('price'),
					'popularity' => __pl('popularity'),
					'rating' => __pl('rating'),
					'rand' => __pl('rand'),
					'menu_order' => __pl('menu_order'),
				),
			),
			'order' => array(
				'type' => 'select',
				'label' => __pl('order'),
				'default' => 'ASC',
				'list' => array(
					'ASC' => __pl('ASC'),
					'DESC' => __pl('DESC'),
				),
			),
		),
		'products_style' => $products_style,
		'box_style' => $products_box,
		'sale_flash_style' => $products_sale_flash,
		'pagination_style' => array(
			'pagination_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'css' => ['{{element}} nav.woocommerce-pagination' => 'margin-top:{{val}}px'],
 			),
			'pagination_border' => array(
				'type' => 'checkbox',
				'label' => __pl('border'),
				//'css' => ['{{element}} nav.woocommerce-pagination' => 'margin-top:{{val}}px'],
 			),
			'pagination_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => [
					'{{element}}nav.woocommerce-pagination ul' => 'border-color:{{val}}',
					'{{element}} nav.woocommerce-pagination ul li' => 'border-right-color: {{val}}; border-left-color: {{val}}',
				],
 			),
			'pagination_padding' => array(
				'type' => 'slider',
				'label' => __pl('padding'),
				'screen' => 1,
				'units' => [ 'em' ],
				'min' => 0,
				'max' => 4,
				'step' => 0.1,
				'css' => [
					'{{element}} nav.woocommerce-pagination ul li a' => 'padding: {{val}}',
					'{{element}} nav.woocommerce-pagination ul li span' => 'padding: {{val}}'
				],
 			),
			'pagination_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} nav.woocommerce-pagination' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'pagination_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
					'active' => __pl('active'),
				),
 			),
			'pagination_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a' => 'color:{{val}}'],
				'show' => ['pagination_colors' => 'normal'],
 			),
			'pagination_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a' => 'background-color:{{val}}'],
				'show' => ['pagination_colors' => 'normal'],
 			),
			'pagination_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a:hover' => 'color:{{val}}'],
				'show' => ['pagination_colors' => 'hover'],
 			),
			'pagination_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li a:hover' => 'background-color:{{val}}'],
				'show' => ['pagination_colors' => 'hover'],
 			),
			'pagination_color_active' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => [ '{{element}} nav.woocommerce-pagination ul li span.current' => 'color:{{val}};' ],
				'show' => ['pagination_colors' => 'active'],
 			),
			'pagination_bg_color_active' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} nav.woocommerce-pagination ul li span.current' => 'background-color:{{val}}'],
				'show' => ['pagination_colors' => 'active'],
 			),
			
		),
		'no_found_style' => array(
			'nf_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-product-no-found' => 'color:{{val}}'],
 			),
			'nf_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .pagelayer-product-no-found' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
		),
		'styles' =>[
			'query' => __pl('query'),
			'products_style' => __pl('products_style'),
			'box_style' => __pl('box_style'),
			'pagination_style' => __pl('pagination_style'),
			'sale_flash_style' => __pl('sale_flash_style'),
			'no_found_style' => __pl('no_found_style'),
		],
	)
);

// Product Archives description
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_archives_desc', array(
		'name' => __pl('product_archives_desc'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-archives-desc-container">'. pagelayer_get_product_archives_desc() .'</div>',
		'params' => array(
			'align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'css' => ['{{element}} .pagelayer-archives-desc-container' => 'text-align:{{val}}'],
 			),
			'color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-product-details__short-description' => 'color:{{val}}'],
 			),
			'typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-product-details__short-description' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
		),
	)
);

// Product Additional Information
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_addi_info', array(
		'name' => __pl('product_addi_info'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-addi-info-container product">{{product_additional_info}}</div>',
		'params' => array(
			'color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .shop_attributes' => 'color:{{val}}'],
 			),
			'typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'.woocommerce {{element}} .shop_attributes' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'heading' => array(
				'type' => 'checkbox',
				'label' => __pl('heading_style'),
				'default' => 'true',
				'addAttr' => ['{{element}} .pagelayer-addi-info-container' => 'pagelayer-show-heading="{{heading}}"'],
 			),
			'heading_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-addi-info-container h2' => 'color:{{val}}'],
				'req' => ['heading' => 'true'],
 			),
			'heading_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .pagelayer-addi-info-container h2' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
				'req' => ['heading' => 'true'],
			),
		),
	)
);

// Product Additional Information
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_product_data_tabs', array(
		'name' => __pl('product_data_tabs'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-data-tabs-container product">{{product_data_tab}}</div>',
		'params' => array(
			'tabs_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),
				'list' => array(
					'normal' => __pl('normal'),
					'active' => __pl('active'),
				),
 			),
			'tabs_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li:not(.active) a' => 'color:{{val}} !important;'],
				'show' => ['tabs_colors' => 'normal'],
 			),
			'tabs_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li:not(.active)' => 'background-color:{{val}} !important;'],
				'show' => ['tabs_colors' => 'normal'],
 			),
			'tabs_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li:not(.active)' => 'border-color:{{val}}',
					'.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-color:{{val}}'
				],
				'show' => ['tabs_colors' => 'normal'],
			),
			'tabs_color_active' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li.active a' => 'color:{{val}} !important;'],
				'show' => ['tabs_colors' => 'active'],
 			),
			'tabs_bg_color_active' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li.active' => 'background-color:{{val}} !important;'],
				'show' => ['tabs_colors' => 'active'],
 			),
			'tabs_border_color_active' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-color: {{val}}',	
					'.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li.active' => 'border-color: {{val}} !important;'
				],
				'show' => ['tabs_colors' => 'active'],
			),
			'tabs_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li' => 'border-style: {{val}} !important',
				],
				'list' => [
					'' => __pl('default'),
					'none' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => ['tabs_colors' => 'normal'],
			),
			'tabs_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li' => 'border-width: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px !important;',
				],
				'req' => [
					'!tabs_border_type' => ['', 'none'],
				],
			),
			'tabs_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li a' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'border_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs li' => 'border-radius: {{val}}px !important;'],
			),
		),
		'panel_style' => array(
			'panel_color' => array(
				'type' => 'color',
				'label' => __pl('desc_color'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'color: {{val}}'],
 			),
			'panel_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'panel_heading' => array(
				'type' => 'heading',
				'label' => __pl('heading_style'),
			),
			'panel_heading_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel h2' => 'color: {{val}}'],
 			),
			'panel_heading_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel h2' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'panel_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('default'),
					'none' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'panel_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-color: {{val}} !important;'],
				'req' => array(
					'!panel_border_type' => ['none'],
				),
			),
			'panel_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-width: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px !important; margin-top: -{{val[0]}}px !important;'],
				'req' => [
					'!panel_border_type' => ['none'],
				],
			),
			'panel_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'css' => [
					'.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;',
					'.woocommerce {{element}} .woocommerce-tabs ul.wc-tabs' => 'margin-left: {{val[0]}}px; margin-right: {{val[1]}};'
				],
			),
			'panel_shadow' => array(
				'type' => 'box_shadow',
				'label' => __pl('box_shadow'),
				'css' => ['.woocommerce {{element}} .woocommerce-tabs .woocommerce-Tabs-panel' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}};'],
			),
		),
		'styles' => array(
			'panel_style' => __pl('panel_style'),
		),
	)
);

// WooCommerce Menu cart
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_woo_menu_cart', array(
		'name' => __pl('woo_menu_cart'),
		'group' => 'woocommerce',
		'html' => '<div class="pagelayer-woo-menu-cart-container">{{cart_html}}</div>',
		'params' => array(
			'icon_type' => array(
				'type' => 'select',
				'label' => __pl('icon'),
				'default' => 'fa fa-shopping-cart',
				'list' => array(
					'fa fa-shopping-cart' => __pl('cart'),
					'fa fa-shopping-basket' => __pl('basket'),
					'fa fa-shopping-bag' => __pl('bag'),
				),
 			),
			'items_indicator' => array(
				'type' => 'select',
				'label' => __pl('items_indicator'),
				'default' => 'bubble',
				'list' => array(
					'' => __pl('none'),
					'bubble' => __pl('bubble'),
					'plain' => __pl('plain'),
				),
				'addAttr' => [ '{{element}} .pagelayer-menu-cart-toggle' => 'pagelayer-icon="{{items_indicator}}"'],
 			),
			'empty_indicator' => array(
				'type' => 'checkbox',
				'label' => __pl('empty_indicator'),
				'addAttr' => ['{{element}} .pagelayer-menu-cart-toggle' => 'pagelayer-empty-indicator="{{empty_indicator}}"'],
 			),
			'sub_total' => array(
				'type' => 'checkbox',
				'label' => __pl('hide_sub_total'),
				'css' => ['{{element}} .pagelayer-cart-button-text' => 'display:none;']
 			),
			'cart_align' => array(
				'type' => 'radio',
				'label' => __pl('alignment'),
				'default' => 'left',
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right'),
				),
				'css' => [
					'{{element}} .pagelayer-menu-cart-toggle' => 'text-align:{{val}}'
				],
 			),			
		),
		'mini_cart_style' => array(
			'mini_cart_colors' => array(
				'type' => 'radio',
				'label' => '',
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
				),
 			),
			'mini_cart_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-cart-button' => 'color:{{val}}'],
				'show' => ['mini_cart_colors' => 'normal'],
 			),
			'cart_icon_color' => array(
				'type' => 'color',
				'label' => __pl('cart_icon_color'),
				'css' => ['{{element}} .pagelayer-cart-button-icon' => 'color:{{val}}'],
				'show' => ['mini_cart_colors' => 'normal'],
 			),
			'mini_cart_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-cart-button' => 'background-color:{{val}}'],
				'show' => ['mini_cart_colors' => 'normal'],
 			),
			'mini_cart_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-cart-button' => 'border-color:{{val}}'],
				'show' => ['mini_cart_colors' => 'normal'],
 			),
			'mini_cart_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-cart-button:hover' => 'color:{{val}}'],
				'show' => ['mini_cart_colors' => 'hover'],
 			),
			'cart_icon_color_hover' => array(
				'type' => 'color',
				'label' => __pl('cart_icon_color'),
				'css' => ['{{element}} .pagelayer-cart-button-icon:hover' => 'color:{{val}}'],
				'show' => ['mini_cart_colors' => 'hover'],
 			),
			'cart_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-cart-button:hover' => 'background-color:{{val}}'],
				'show' => ['mini_cart_colors' => 'hover'],
 			),
			'cart_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-cart-button:hover' => 'border-color:{{val}}'],
				'show' => ['mini_cart_colors' => 'hover'],
 			),
			'cart_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-cart-button' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'cart_border_width' => array(
				'type' => 'slider',
				'label' => __pl('border_width'),
				'css' => ['{{element}} .pagelayer-cart-button' => 'border-width:{{val}}px'],
				'req' => ['!cart_border_type' => ''],
 			),
			'cart_border_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'units' => [ 'px', 'em', '%' ],
				'css' => ['{{element}} .pagelayer-cart-button' => 'border-radius:{{val}}'],
 			),
			'cart_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .pagelayer-cart-button' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'mini_cart_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => [ 'px', 'em', '%' ],
				'css' => ['{{element}} .pagelayer-cart-button' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			),
			'mini_cart_icon' => array(
				'type' => 'heading',
				'label' => __pl('icon'),
			),
			'mini_icon_size' => array(
				'type' => 'slider',
				'label' => __pl('size'),
				'units' => [ 'px', 'em' ],
				'css' => ['{{element}} .pagelayer-cart-button-icon' => 'font-size:{{val}}'],
			),
			'mini_icon_spacing' => array(
				'type' => 'slider',
				'label' => __pl('spacing'),
				'units' => [ 'px', 'em' ],
				'min' => 0,
				'max' => 50,
				'css' => [
					'body:not(.rtl) {{element}} .pagelayer-cart-button-text' => 'margin-right: {{val}}',
					'body.rtl {{element}} .pagelayer-cart-button-text' => 'margin-left: {{val}}',
				],
			),
			'mini_bubble_icon' => array(
				'type' => 'heading',
				'label' => __pl('bubble'),
				'req' => ['items_indicator' => 'bubble'],
			),
			'bubble_colors' => array(
				'type' => 'radio',
				'label' => '',
				'default' => 'normal',
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => ['items_indicator' => 'bubble'],
			),
			'bubble_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} [pagelayer-icon="bubble"]  .pagelayer-cart-button-icon[data-counter]:before' => 'color:{{val}}'],
				'show' => ['bubble_colors' => 'normal'],
			),
			'bubble_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} [pagelayer-icon="bubble"] .pagelayer-cart-button-icon[data-counter]:before' => 'background-color:{{val}}'],
				'show' => ['bubble_colors' => 'normal'],
			),
			'bubble_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} [pagelayer-icon="bubble"] .pagelayer-cart-button-icon[data-counter]:hover:before' => 'color:{{val}}'],
				'show' => ['bubble_colors' => 'hover'],
			),
			'bubble_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} [pagelayer-icon="bubble"] .pagelayer-cart-button-icon[data-counter]:hover:before' => 'background-color:{{val}}'],
				'show' => ['bubble_colors' => 'hover'],
			),
		),
		'container_style' => array(
			'container_position' => array(
				'type' => 'select',
				'label' => __pl('container_position'),
				'default' => 'fixed_right',
				'list' => array(
					'fixed_right' => __pl('fixed_right'),
					'fixed_left' => __pl('fixed_left'),
					'dropdown' => __pl('dropdown'),
				),
				'addAttr' => [ '{{element}} .pagelayer-woo-menu-cart-container' => 'pagelayer-container-position="{{container_position}}"'],
 			),
			'container_align' => array(
				'type' => 'radio',
				'label' => __pl('position'),
				'default' => 'left',
				'list' => array(
					'left' => __pl('left'),
					'right' => __pl('right'),
				),
				'css' => ['{{element}} .pagelayer-menu-cart-container' => '{{val}}:0 !important;'],
				'req' => ['container_position' => 'dropdown'],
 			),
			'cart_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-menu-cart-container' => 'color:{{val}}']
			),
			'cart_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-menu-cart-container' => 'background-color:{{val}}']
			),
			'container_close' => array(
				'type' => 'slider',
				'label' => __pl('container_close_size'),
				'css' => ['{{element}} .pagelayer-menu-cart-close' => 'font-size:{{val}}px;']
 			),
			'container_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'screen' => 1,
				'units' => ['px', '%'],
				'css' => ['{{element}} .pagelayer-menu-cart-container' => 'width:{{val}};']
 			),
			'sub_total_label' => array(
				'type' => 'heading',
				'label' => __pl('sub_total'),
			),
			'sub_total_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-mini-cart__total' => 'color: {{val}}'],
 			),
			'sub_total_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-mini-cart__total' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
		),
		'products_style' => array(
			'products_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-mini-cart > .woocommerce-mini-cart-item' => 'color:{{val}}']
			),
			'products_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .woocommerce-mini-cart > .woocommerce-mini-cart-item' => 'background-color:{{val}}']
			),
			'products_list_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => [ 'px', 'em'],
				'css' => ['{{element}} .woocommerce-mini-cart > .woocommerce-mini-cart-item' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;'],
			),
			'img_label' => array(
				'type' => 'heading',
				'label' => __pl('image'),
			),
			'products_img_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'default' => '50px',
				'units' => ['px', 'em', '%'],
				'css' => ['{{element}} .woocommerce-mini-cart-item img' => 'width: {{val}}'],
 			),
			'title_label' => array(
				'type' => 'heading',
				'label' => __pl('product_title'),
			),
			'products_title_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-mini-cart-item a' => 'color: {{val}}'],
 			),
			'products_title_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-mini-cart-item a' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'price_label' => array(
				'type' => 'heading',
				'label' => __pl('product_price'),
			),
			'products_price_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .woocommerce-mini-cart-item .quantity' => 'color: {{val}}'],
 			),
			'products_price_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-mini-cart-item .quantity' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'remove_label' => array(
				'type' => 'heading',
				'label' => __pl('remove_items'),
			),
			'remove_icon_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .remove_from_cart_button' => 'color: {{val}} !important'],
 			),
			'remove_icon_size' => array(
				'type' => 'slider',
				'label' => __pl('size'),
				'css' => ['{{element}} .remove_from_cart_button' => 'font-size: {{val}}px'],
			),
		),
		'buttons_style' => array(
			'button_display' => array(
				'type' => 'select',
				'label' => __pl('display'),
				'default' => 'inline',
				'list' => array(
					'inline' => __pl('inline'),
					'block' => __pl('block'),
				),
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'display: {{val}}'],
				'addAttr' => ['{{element}} .woocommerce-mini-cart__buttons' => 'pagelayer-display="{{button_display}}"'],
 			),
			'space_between_btn' => array(
				'type' => 'slider',
				'label' => __pl('space_between'),
				'default' => 5,
				'css' => [
					'{{element}} .woocommerce-mini-cart__buttons[pagelayer-display="inline"] a' => 'margin-left: {{val}}px',
					'{{element}} .woocommerce-mini-cart__buttons[pagelayer-display="block"] a' => 'margin-bottom: {{val}}px'
				],
 			),
			'btn_padding' => array(
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => [ 'px', 'em'],
				'default' => '5,5,5,5',
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}} !important;'],
			),
			'btn_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => [
					'{{element}} .woocommerce-mini-cart__buttons' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'
				],
			),
			'btn_border_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'units' => [ 'px', 'em'],
				'default' => '50',
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'border-radius: {{val}} !important'],
 			),
			'btn_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '#ffffff',
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'color: {{val}}'],
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#585757',
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'background-color: {{val}}'],
			),
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'border-style: {{val}} !important'],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'default' => '#42414f',
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'border-color: {{val}} !important'],
				'req' => array(
					'!btn_border_type' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .woocommerce-mini-cart__buttons a' => 'border-width: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px !important'],
				'req' => [
					'!btn_border_type' => ''
				],
			),
		),
		'styles' => array(
			'mini_cart_style' => __pl('mini_cart_style'),
			'container_style' => __pl('container_style'),
			'products_style' => __pl('products_style'),
			'buttons_style' => __pl('buttons_style'),
		),
	)
);

}// class_exists('woocommerce') end

// Popup Settings
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_popup', array(
		'name' => __pl('Popup Layout'),
		'group' => 'other',
		'not_visible' => 1,
		'no_gt' => 1,
		'icon' => 'fas fa-sort-amount-up-alt',
		'overide_css_selector' => '[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content',
		'hide_active' => 1,
		'skip_props_cat' => ['position_styles', 'animation_styles', 'responsive_styles'],
		'skip_props' => ['ele_zindex',
						'ele_shadow', 'border_shadow_hover',
						'hide_desktop',	'hide_tablet', 'hide_mobile', 'ele_sticky_pos'],
		'params' => array(
			'post_popup_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'default' => '50',
				'screen' => 1,
				'units' => ['%', 'px'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'width:{{val}}'],
			),
			'post_popup_height' => array(
				'type' => 'slider',
				'label' => __pl('height'),
				'screen' => 1,
				'units' => ['%', 'px', 'vh'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'height:{{val}}'],
			),
			'popup_position' => array(
				'type' => 'heading',
				'label' => __pl('position'),
			),
			'popup_hori_position' => array(
				'type' => 'radio',
				'label' => __pl('horizontal'),
				'list' => array(
					'flex-start' => __pl('left'),
					'center' => __pl('center'),
					'flex-end' => __pl('right'),
				),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"]' => 'justify-content:{{val}}'],
			),
			'popup_ver_position' => array(
				'type' => 'radio',
				'label' => __pl('vertical'),
				'list' => array(
					'flex-start' => __pl('top'),
					'center' => __pl('center'),
					'flex-end' => __pl('bottom'),
				),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"]' => 'align-items:{{val}}'],
			),
			'popup_animation' => array(
				'type' => 'select',
				'label' => __pl('animation'),
				'default' => '',
				'addClass' => ['{{val}}',( !pagelayer_is_live() ? 'pagelayer-wow' : '' )],
				'addAttr' =>  'data-popup_animation="{{popup_animation}}',
				'list' => $pagelayer->anim_in_options,
			),
			'popup_animation_delay' => [
				'type' => 'spinner',
				'label' => __pl('animation_delay'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => '-webkit-animation-delay: {{val}}ms; animation-delay: {{val}}ms;'],
				'default' => 600,
				'min' => 0,
				'max' => 90000,
				'step' => 100,
				'req' => [
					'!popup_animation' => ''
				]
			],
		),
		'popup_styles' => array(
			'popup_content_back' => array(
				'type' => 'radio',
				'label' => __pl('bg_color'),
				'list' => array(
					'color' => __pl('color'),
					'gradient' => __pl('gradient'),
				),
			),
			'popup_content_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'background-color:{{val}}'],
				'req' => [ 'popup_content_back' => 'color'],
			),
			'popup_content_bg_gradient' => [
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6ff,23,#72e584ff,45,#2ca4ebff,100',
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],
				'req' => ['popup_content_back' => 'gradient'],
			],		
			'popup_border_type' => [
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'border-style: {{val}}'],
			],
			'popup_border_width' => [
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '1,1,1,1',
				'units' => ['px', 'em'],
				'screen' => 1,
				'req' => [
					'!popup_border_type' => ''
				],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			],
			'popup_border_color' => [
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#CCC',
				'screen' => 1,
				'req' => [
					'!popup_border_type' => ''
				],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'border-color: {{val}}'],
			],
			'popup_border_radius' => [
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => ['px', 'em', '%'],
				'screen' => 1,
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			],
			'popup_shadow' => [
				'type' => 'box_shadow',
				'label' => __pl('shadow'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-modal-content' => 'box-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[4]}}px {{val[3]}} {{val[5]}} !important;'],
			]
		),
		'overlay' => array(
			'popup_overlay' => array(
				'type' => 'checkbox',
				'label' => __pl('enable_overlay'),
				'default' => true,
				'css' => ['[pagelayer-popup-id="{{ele_id}}"]' => 'pointer-events: all'],
			),
			'popup_overlay_back' => array(
				'type' => 'radio',
				'label' => __pl('bg_color'),
				'default' => 'bg_color',
				'list' => array(
					'bg_color' => __pl('color'),
					'gradient' => __pl('gradient'),
				),
				'req' => [ 'popup_overlay' => 'true'],
			),
			'popup_overlay_bg' => array(
				'type' => 'color',
				'default' => '#000000cc',
				'label' => __pl('bg_color'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"]' => 'background-color:{{val}}'],
				'req' => [ 'popup_overlay_back' => 'bg_color', 'popup_overlay' => 'true'],
			),
			'popup_overlay_bg_gradient' => [
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6ff,23,#72e584ff,45,#2ca4ebff,100',
				'css' => ['[pagelayer-popup-id="{{ele_id}}"]' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],
				'req' => ['popup_overlay_back' => 'gradient', 'popup_overlay' => 'true'],
			],
		),
		'close_button' => array(
			'popup_overlay_close' => array(
				'type' => 'checkbox',
				'label' => __pl('close_by_overlay'),
				'addAttr' => 'data-overlay_close="{{popup_overlay_close}}"',
				'css' => ['[pagelayer-popup-id="{{ele_id}}"]' => 'pointer-events: all'],
			),
			'popup_sel_close' => array(
				'type' => 'checkbox',
				'label' => __pl('close_by_selector'),
			),
			'popup_selector_close' => array(
				'type' => 'text',
				'label' => __pl('ele_selector'),
				'addAttr' => 'data-selector_close="{{popup_selector_close}}"',
				'req' => [ 'popup_sel_close' => 'true'],
			),
			'popup_cbtn' => array(
				'type' => 'checkbox',
				'label' => __pl('close_button'),
				'default' => true,
				'addAttr' => 'data-popup_cbtn="{{popup_cbtn}}"',
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'display:block'],
			),
			'popup_cbtn_position' => array(
				'type' => 'select',
				'label' => __pl('position'),
				'default' => 'inside',
				'list' => array(
					'inside' => __pl('inside'),
					'outside' => __pl('outside'),
				),
				'show' => [ 'popup_cbtn' => 'true'],
				'addAttr' => 'data-popup_cbtn_position="{{popup_cbtn_position}}"',
			),
			'popup_cbtn_v_position' => array(
				'type' => 'slider',
				'label' => __pl('verticle_postion'),
				'screen' => 1,
				'units' => ['%', 'px'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'top:{{val}}'],
				'show' => [ 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_h_position' => array(
				'type' => 'slider',
				'label' => __pl('horizontal_pos'),
				'screen' => 1,
				'units' => ['%', 'px'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'left:{{val}}'],
				'show' => [ 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_size' => array(
				'type' => 'slider',
				'label' => __pl('size'),
				'screen' => 1,
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'font-size:{{val}}px'],
				'show' => [ 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_colors' => array(
				'type' => 'radio',
				'label' => __pl('colors'),
				'default' => 'normal',
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'show' => [ 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'color:{{val}}'],
				'show' => [ 'popup_cbtn_colors' => 'normal', 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'background-color:{{val}}'],
				'show' => [ 'popup_cbtn_colors' => 'normal', 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close:hover' => 'color:{{val}}'],
				'show' => [ 'popup_cbtn_colors' => 'hover', 'popup_cbtn' => 'true'],
			),
			'popup_cbtn_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close:hover' => 'background-color:{{val}}'],
				'show' => [ 'popup_cbtn_colors' => 'hover', 'popup_cbtn' => 'true'],
			),	
			'popup_cbtn_border_type' => [
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => [ 'popup_cbtn' => 'true'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'border-style: {{val}}'],
			],
			'popup_cbtn_border_width' => [
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '1,1,1,1',
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => [ 'popup_cbtn' => 'true'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			],
			'popup_cbtn_border_color' => [
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#CCC',
				'screen' => 1,
				'show' => [ 'popup_cbtn' => 'true'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'border-color: {{val}}'],
			],
			'popup_cbtn_border_radius' => [
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => ['px', 'em', '%'],
				'screen' => 1,
				'show' => [ 'popup_cbtn' => 'true'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			],
			'popup_cbtn_border_padding' => [
				'type' => 'padding',
				'label' => __pl('padding'),
				'units' => ['px', 'em', '%'],
				'screen' => 1,
				'show' => [ 'popup_cbtn' => 'true'],
				'css' => ['[pagelayer-popup-id="{{ele_id}}"] .pagelayer-popup-close' => 'padding: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			],
		),
		'action_triggers' => array(
			'trig_click' => array(
				'type' => 'checkbox',
				'label' => __pl('trig_onclick'),
				'addAttr' => 'data-trig_click="{{trig_click}}"',
			),
			'trig_click_ele' => array(
				'type' => 'text',
				'label' => __pl('ele_selector'),
				'addAttr' => 'data-trig_click_ele="{{trig_click_ele}}"',
				'req' => [ 'trig_click' => 'true'],
			),
			'trig_load' => array(
				'type' => 'checkbox',
				'label' => __pl('trig_onload'),
				'addAttr' => 'data-trig_load="{{trig_load}}"',
			),
			'trig_load_sec' => array(
				'type' => 'spinner',
				'label' => __pl('within_sec'),
				'min' => 1,
				'step' => 0.1,
				'req' => [ 'trig_load' => 'true'],
				'addAttr' => 'data-trig_load_sec="{{trig_load_sec}}"',
			),
			'trig_scroll' => array(
				'type' => 'checkbox',
				'label' => __pl('trig_onscroll'),
				'addAttr' => 'data-trig_scroll="{{trig_scroll}}"',
			),
			'trig_scroll_dir' => array(
				'type' => 'select',
				'label' => __pl('scroll_direction'),
				'default' => 'down',
				'list' => array(
					'down' => __pl('down'),
					'up' => __pl('up'),
				),
				'addAttr' => 'data-trig_scroll_dir="{{trig_scroll_dir}}"',
				'req' => [ 'trig_scroll' => 'true'],
			),
			'trig_scroll_per' => array(
				'type' => 'spinner',
				'label' => __pl('within_per'),
				'min' => 1,
				'step' => 1,
				'max' => 100,
				'req' => ['trig_scroll' => 'true', 'trig_scroll_dir' => 'down'],
				'addAttr' => 'data-trig_scroll_per="{{trig_scroll_per}}"',
			),
			'trig_scroll_to_ele' => array(
				'type' => 'checkbox',
				'label' => __pl('trig_onscroll_to_ele'),
				'addAttr' => 'data-trig_scroll_to_ele="{{trig_scroll_to_ele}}"',
			),
			'trig_scroll_to_ele_sel' => array(
				'type' => 'text',
				'label' => __pl('ele_selector'),
				'req' => [ 'trig_scroll_to_ele' => 'true'],
				'addAttr' => 'data-trig_scroll_to_ele_sel="{{trig_scroll_to_ele_sel}}"',
			),
			'trig_page_exit_intent' => array(
				'type' => 'checkbox',
				'label' => __pl('trig_onpage_exit_intent'),
				'addAttr' => 'data-trig_page_exit_intent="{{trig_page_exit_intent}}"',
			),
			'trig_before_load' => array(
				'type' => 'checkbox',
				'label' => __pl('trig_beforeLoad'),
				'addAttr' => 'data-trig_before_load="{{trig_before_load}}"',
				'desc' => __pl('popup_load_desc')
			),
		),
		'advance_options' =>  array(
			'popup_multi_time' => array(
				'type' => 'checkbox',
				'label' => __pl('popup_multi_time'),
				'addAttr' => 'data-popup_multi_time="{{popup_multi_time}}"',
			),
			'popup_cookie_session' => array(
				'type' => 'checkbox',
				'label' => __pl('popup_cookie_session'),
				'addAttr' => 'data-popup_cookie_session="{{popup_cookie_session}}"',
			),
			'popup_cookie_close' => array(
				'type' => 'checkbox',
				'label' => __pl('popup_cookie_close'),
				'addAttr' => 'data-popup_cookie_close="1"',
				'req' => ['popup_cookie_session' => 'true']
			),
			'popup_cookie_selector' => array(
				'type' => 'text',
				'label' => __pl('popup_cookie_selector'),
				'desc' => __pl('popup_cookie_selector_exp'),
				'addAttr' => 'data-popup_cookie_selector="{{popup_cookie_selector}}"',
				'req' => ['popup_cookie_session' => 'true']
			),
			'popup_cookie_name' => array(
				'type' => 'text',
				'label' => __pl('popup_cookie_name'),
				'default' => '', // Make it blank to assigned from live.php
				'addAttr' => 'data-popup_cookie_name="{{popup_cookie_name}}"',
				'req' => ['popup_cookie_session' => 'true']
			),
			'popup_cookie_exp' => array(
				'type' => 'text',
				'label' => __pl('popup_cookie_exp'),
				'default' => 30,
				'addAttr' => 'data-popup_cookie_exp="{{popup_cookie_exp}}"',
				'req' => ['popup_cookie_session' => 'true']
			),
			'popup_auto_close' => array(
				'type' => 'spinner',
				'label' => __pl('auto_close'),
				'addAttr' => 'data-popup_auto_close="{{popup_auto_close}}"',
			),
		),
		'styles' => array(
			'popup_styles' => __pl('popup_styles'),
			'overlay' => __pl('overlay'),
			'close_button' => __pl('close_style'),
			'action_triggers' => __pl('action_triggers'),
			'advance_options' => __pl('advance_options'),
		),
	)
);

// Timeline widget
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_timeline', array(
	'name' => __pl('timeline'),
	'group' => 'other',
	'icon' => 'fas fa-stream',
	'has_group' => [
		'section' => 'params', 
		'prop' => 'elements'
	],
	'holder' => '.pagelayer-timeline',
	'innerHTML' => 'text',
	'html' => '<div class="pagelayer-timeline">
				</div>',
	'params' => array(	
		'elements' => array(
			'type' => 'group',
			'label' => __pl('timeline_item'),
			'sc' => PAGELAYER_SC_PREFIX.'_timeline_item',
			'item_label' => array(
				'default' => __pl('timeline_item'),
				'param' => 'text'
			),
			'count' => 2,
			'text' => __pl('add_timeline_item'),
		),
		
		'timeline_centerline_bg_color' => array(
			'type' => 'color',
			'label' => __pl('seperator_color'),
			'default' => 'grey',
			'css' => ['{{element}} .pagelayer-timeline::after' => 'background-color: {{val}};'],
		),
		'vindent' => array(
			'type' => 'spinner',
			'label' => __pl('space_between_col'),
			'min' => 10,
			'step' => 1,
			'max' => 100,
			'default' => 20,
			'screen' => 1,
			'css' => ['{{element}} .pagelayer-timeline-container' => 'padding-top:{{val}}px; padding-bottom:{{val}}px;'],
		)
	),
));

// Timeline items
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_timeline_item', array(
		'name' => __pl('timeline_item'),
		'icon' => 'fas fa-stream',
		'not_visible' => 1,
		'parent' => [PAGELAYER_SC_PREFIX.'_timeline'],
		'html' => '<div class="pagelayer-timeline-container pagelayer-timeline-{{side}}">
						<div  class="pagelayer-timeline-container-left">
							<div class="pagelayer-timeline-content">
								<div if={{left_heading_text}} class="pagelayer-heading-holder">{{left_heading_text}}</div>
								<div if={{left_paragraph_text}} class="pagelayer-text-holder">{{left_paragraph_text}}</div>
							</div>
						</div>
						<div class="pagelayer-timeline-center-circle-container">
							<div class="pagelayer-timeline-center-circle">
								<i if="{{timeline_circle_icon}}" class="{{timeline_circle_icon}} pagelayer-timeline-circle-icon"></i>
								<div if="{{timeline_circle_text}}" class="pagelayer-text-holder pagelayer-timeline-circle-text">{{timeline_circle_text}}</div>
							</div>
						</div>
						<div  class="pagelayer-timeline-container-right">
							<div class="pagelayer-timeline-content">
								<div if={{right_heading_text}} class="pagelayer-heading-holder">{{right_heading_text}}</div>
								<div if={{right_paragraph_text}} class="pagelayer-text-holder">{{right_paragraph_text}}</div>
							</div>
						</div>
				  </div>',
		'params' => array(
			'text' => array(
				'type' => 'text',
				'label' => __pl('text'),
				'default' => __pl('timeline_item')
			),
			'side' => array(
				'label' => __pl('side'),
				'type' => 'radio',
				'default' => 'both',
				'list' => array(
					'left' => __pl('left'),
					'right' => __pl('right'),
					'both' => __pl('both')
				)
			),
			'hindent' => array(
				'type' => 'spinner',
				'label' => __pl('icon_spacing'),
				'screen' => 1,
				'step' => 1,
				'min' => 0,
				'max' => 40,
				'default' => 5,
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'margin-right:{{val}}px;',
				'{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'margin-left:{{val}}px;'],
			)
		),
		
		'timeline_circle' => [
			'timeline_pos_y' => array(
				'label' => __pl('verticle_postion'),
				'type' => 'slider',
				'step' => 1,
				'min' => 0,
				'max' => 80,
				'default' => 20,
				'screen' => 1,
				'units' => ['%'],
				'css' => ['{{element}} .pagelayer-timeline-center-circle' => 'top: {{val}};'],
			),
			'timeline_circle_radius' => array(
				'type' => 'slider',
				'label' => __pl('radius'),
				'screen' => 1,
				'step' => 1,
				'min' => 20,
				'max' => 70,
				'default' => 40,
				'units' => ['px'],
				'css' => ['{{element}} .pagelayer-timeline-center-circle'  => 'width:{{val}}; height:{{val}}; left:calc(50% - {{val}}/2);'],
			),
			'timeline_circle_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-timeline-center-circle' => 'background-color: {{val}};'],
			),
			'timeline_circle_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'default' => 'solid',
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'css' => ['{{element}} .pagelayer-timeline-center-circle'=> 'border-style: {{val}}'],
			),
			'timeline_circle_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '4,4,4,4',
				'units' => ['px', 'em'],
				'screen' => 1,
				'req' => [
					'!timeline_circle_border_type' => ''
				],
				'css' =>['{{element}} .pagelayer-timeline-center-circle' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			),
			'timeline_circle_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#000',
				'screen' => 1,
				'req' => [
					'!timeline_circle_border_type' => ''
				],
				'css' =>['{{element}} .pagelayer-timeline-center-circle' => 'border-color: {{val}}'],
			),
			'timeline_circle_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => ['px', 'em', '%'],
				'screen' => 1,
				'show' => ['timeline_circle_border_hover' => ''],
				'req' => [
					'!timeline_circle_border_type' => ''
				],
				'default' => '50,50,50,50',
				'css' =>['{{element}} .pagelayer-timeline-center-circle' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			),
			'timeline_circle_icon' => array(
				'type' => 'icon',
				'label' => __pl('service_box_font_icon_label'),
				'default' => '',
			),
			'timeline_circle_icon_size' => array(
				'label' => __pl('icon_size'),
				'type' => 'slider',
				'step' => 1,
				'min' => 5,
				'max' => 40,
				'default' => 10,
				'screen' => 1,
				'units' => ['px'],
				'css' => ['{{element}} .pagelayer-timeline-center-circle .pagelayer-timeline-circle-icon' => 'font-size:{{val}}'],
				'show' => ['!timeline_circle_icon' => ''],
			),
			'timeline_circle_icon_color' => array(
				'type' => 'color',
				'label' => __pl('icon_color'),
				'show' => ['!timeline_circle_icon' => ''],
				'css' => ['{{element}} .pagelayer-timeline-center-circle .pagelayer-timeline-circle-icon' => 'color: {{val}};'],
			),
			'timeline_circle_text' => array(
				'type' => 'editor',
				'label' => __pl('text'),
				'default' => '',
				'desc' => __pl('Edit the content here or edit directly in the Editor'),
				'edit' => '.pagelayer-timeline-circle-text', // Edit the text and also mirror the same
				'keep_prop' => 1
			)
		],
		
		// timeline left part code starts
		'left' => [
			'left_heading_text' => array(
				'type' => 'textarea',
				'label' => __pl('heading_name'),
				'default' => '<h2>1998</h2>',
				'desc' => __pl('Edit the heading here'),
				'edit' => '.pagelayer-timeline-container-left .pagelayer-timeline-content .pagelayer-heading-holder', // Edit the text and also mirror the same
				'req' => ['side' => ['both','left']],
			),
			'left_paragraph_text' => array(
				'type' => 'editor',
				'label' => __pl('text'),
				'default' => 'Lorem ipsum dolor sit amet',
				'desc' => __pl('Edit the content here or edit directly in the Editor'),
				'edit' => '.pagelayer-timeline-container-left .pagelayer-timeline-content .pagelayer-text-holder', // Edit the text and also mirror the same
				'req' => ['side' => ['both','left']]
			),
			'left_align' => array(
				'label' => __pl('content_align'),
				'type' => 'radio',
				'addAttr' => 'align="{{align}}"',
				'screen' => 1,
				'default' => 'left',
				'css' => ['{{element}} .pagelayer-timeline-container-left' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				),
				'req' => ['side' => ['both','left']]
			),
			'left_part_width' => array(
				'type' => 'spinner',
				'label' => __pl('width'),
				'screen' => 1,
				'step' => 1,
				'min' => 0,
				'max' => 100,
				'default' => 90,
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'width:{{val}}%'],
			),
			'left_heading_state' => array(
				'type' => 'radio',
				'label' => __pl('state'),
				'default' => 'normal',
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => ['side' => ['both','light']]
			),
			'left_color' => array(
				'type' => 'color',
				'label' => __pl('heading_color'),
				'default' => '#111111',
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder *' => 'color:{{val}}'],
				'show' => ['left_heading_state' => 'normal']
			),
			'left_heading_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder *' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				'{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['left_heading_state' => 'normal']
			),
			'left_heading_text_shadow' => array(
				'type' => 'shadow',
				'label' => __pl('text_shadow'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}} !important;'],
				'show' => ['left_heading_state' => 'normal']
			),
			'left_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder:hover *' => 'color:{{val}}', '{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder:hover' => 'color:{{val}}'],
				'show' => ['left_heading_state' => 'hover']
			),
			'left_heading_typo_hover' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder:hover *' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				'{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder:hover' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['left_heading_state' => 'hover']
			),
			'left_heading_text_shadow_hover' => array(
				'type' => 'shadow',
				'label' => __pl('text_shadow'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-heading-holder:hover' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}} !important;'],
				'show' => ['left_heading_state' => 'hover']
			),
			'left_bg_hover' => array(
				'type' => 'radio',
				'label' => __pl('row_bg_styles'),
				'default' => '',
				'list' => [
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				],
				'req' => ['side' => ['both','left']]
			),
			'left_bg_type' => array(
				'type' => 'radio',
				'label' => __pl('background_type'),
				'default' => '',
				'list' => [
					'' => __pl('none'),
					'color' => __pl('color'),
					'gradient' => __pl('gradient'),
					'image' => __pl('image'),
				],
				'show' => ['left_bg_hover' => '']
			),
			'left_bg_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}}  .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-color: {{val}};'],
				'show' => ['left_bg_hover' => ''],
				'req' => ['left_bg_type' => 'color']
			),
			'left_timeline_gradient' => array(
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6,23,#72e584,45,#2ca4eb,100',			
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],			
				'show' => ['left_bg_hover' => ''],
				'req' => ['left_bg_type' => 'gradient']
			),
			'left_img_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '',
				'desc' => __pl('fallback_color'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-color: {{val}};'],
				'show' => ['left_bg_hover' => ''],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_img' => array(
				'type' => 'image',
				'label' => __pl('image'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-image: url("{{{left_bg_img-url}}}");'],
				'show' => ['left_bg_hover' => ''],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_attachment' => array(
				'type' => 'select',
				'label' => __pl('bg_attachment'),
				'list' => [
					'' => __pl('default'),
					'scroll' => __pl('scroll'),
					'fixed' => __pl('fixed')
				],
				'show' => ['left_bg_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-attachment: {{val}};'],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_posx' => array(
				'type' => 'select',
				'label' => __pl('bg_posx'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'left' => __pl('left'),
					'right' => __pl('right'),
					'custom' => __pl('custom')
				],
				'show' => ['left_bg_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-position-x: {{val}};'],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_posx_custom' => array(
				'label' => __pl('custom_x'),
				'type' => 'slider',
				'step' => 1,
				'min' => -5000,
				'max' => 5000,
				'screen' => 1,
				'units' => ['px', 'em', '%'],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-position-x: {{val}};'],
				'req' => array(
					'left_bg_posx' => 'custom'
				),
			),	
			'left_bg_posy' => array(
				'type' => 'select',
				'label' => __pl('bg_posy'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'top' => __pl('top'),
					'bottom' => __pl('bottom'),
					'custom' => __pl('custom')
				],
				'show' => ['left_bg_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-position-y: {{val}};'],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_posy_custom' => array(
				'label' => __pl('custom_y'),
				'type' => 'slider',
				'step' => 1,
				'min' => -5000,
				'max' => 5000,
				'screen' => 1,
				'units' => ['px', 'em', '%'],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-position-y: {{val}};'],
				'req' => array(
					'left_bg_posy' => 'custom'
				),
			),
			'left_bg_repeat' => array(
				'type' => 'select',
				'label' => __pl('bg_repeat'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-repeat: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'repeat' => __pl('repeat'),
					'no-repeat' => __pl('no-repeat'),
					'repeat-x' => __pl('repeat-x'),
					'repeat-y' => __pl('repeat-y'),
				],
				'show' => ['left_bg_hover' => ''],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_size' => array(
				'type' => 'select',
				'label' => __pl('bg_size'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'background-size: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'cover' => __pl('cover'),
					'contain' => __pl('contain')
				],
				'show' => ['left_bg_hover' => ''],
				'req' => ['left_bg_type' => 'image']
			),
			'left_bg_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('bg_hover_delay'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => ['left_bg_hover' => 'hover']
			),
			'left_bg_type_hover' => array(
				'type' => 'radio',
				'label' => __pl('background_type'),
				'default' => '',
				'list' => [
					'' => __pl('none'),
					'color' => __pl('color'),
					'gradient' => __pl('gradient'),
					'image' => __pl('image'),
				],
				'show' => ['left_bg_hover' => 'hover']
			),
			'left_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color_hover'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background: {{val}};'],
				'show' => ['left_bg_hover' => 'hover'],
				'req' => ['left_bg_type_hover' => 'color']
			),
			'left_bg_gradient_hover' => array(
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6,25,#72e584,75,#2ca4eb,100',
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],
				'show' => ['left_bg_hover' => 'hover'],
				'req' => ['left_bg_type_hover' => 'gradient']
			),
			'left_bg_img_hover' => array(
				'type' => 'image',
				'label' => __pl('image_hover'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background: url("{{{left_bg_img_hover-url}}}");'],
				'show' => ['left_bg_hover' => 'hover'],
				'req' => ['left_bg_type_hover' => 'image']
			),
			'left_bg_attachment_hover' => array(
				'type' => 'select',
				'label' => __pl('background_attachment'),
				'list' => [
					'' => __pl('default'),
					'scroll' => __pl('scroll'),
					'fixed' => __pl('fixed')
				],
				'show' => ['left_bg_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background-attachment: {{val}};'],
				'req' => ['left_bg_type_hover' => 'image']
			),
			'left_bg_posx_hover' => array(
				'type' => 'select',
				'label' => __pl('horizontal_pos'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'left' => __pl('left'),
					'right' => __pl('right')
				],
				'show' => ['left_bg_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background-position-x: {{val}};'],
				'req' => ['left_bg_type_hover' => 'image']
			),
			'left_bg_posy_hover' => array(
				'type' => 'select',
				'label' => __pl('verticle_pos'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'top' => __pl('top'),
					'bottom' => __pl('bottom')
				],
				'show' => ['left_bg_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background-position-y: {{val}};'],
				'req' => ['left_bg_type_hover' => 'image']
			),
			'left_bg_repeat_hover' => array(
				'type' => 'select',
				'label' => __pl('repeat'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background-repeat: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'repeat' => __pl('repeat'),
					'no-repeat' => __pl('no-repeat'),
					'repeat-x' => __pl('repeat-x'),
					'repeat-y' => __pl('repeat-y'),
				],
				'show' => ['left_bg_hover' => 'hover'],
				'req' => ['left_bg_type_hover' => 'image']
			),
			'left_bg_size_hover' => array(
				'type' => 'select',
				'label' => __pl('size'),
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'background-size: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'cover' => __pl('cover'),
					'contain' => __pl('contain')
				],
				'show' => ['left_bg_hover' => 'hover'],
				'req' => ['left_bg_type_hover' => 'image']
			),
			'left_timeline_border_hover' => array(
				'type' => 'radio',
				'label' => '',
				'default' => '',
				'list' => [
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				],
				'req' => ['side' => ['both','left']]
			),
			'left_timeline_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'default' => 'solid',
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => ['left_timeline_border_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content '=> 'border-style: {{val}}'],
			),
			'left_timeline_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '1,1,1,1',
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => [
					'left_timeline_border_hover' => ''
				],
				'req' => [
					'!left_timeline_border_type' => ''
				],
				'css' =>['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'border-top-width: {{val[0]}}; 
				border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			),
			'left_timeline_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#CCC',
				'screen' => 1,
				'show' => [
					'left_timeline_border_hover' => ''
				],
				'req' => [
					'!left_timeline_border_type' => ''
				],
				'css' =>['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'border-color: {{val}}'],
			),
			'left_timeline_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => ['left_timeline_border_hover' => ''],
				'req' => [
					'!left_timeline_border_type' => ''
				],
				'default' => '6,6,6,6',
				'css' =>['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			),
			'left_timeline_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => ['left_timeline_border_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'border-style: {{val}}'],
			),
			'left_timeline_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => [	
					'left_timeline_border_hover' => 'hover'
				],
				'req' => [
					'!left_timeline_border_type_hover' => ''
				],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			),
			'left_timeline_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'screen' => 1,
				'show' => [
					'left_timeline_border_hover' => 'hover'
				],
				'req' => [
					'!left_timeline_border_type_hover' => ''
				],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'border-color: {{val}}'],
			),
			'left_timeline_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'units' => ['px', 'em'],
				'show' => [	
					'left_timeline_border_hover' => 'hover'
				],
				'req' => [
					'!left_timeline_border_type_hover' => ''
				],
				'css' => ['{{element}} .pagelayer-timeline-container-left .pagelayer-timeline-content:hover' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			),
		],
		
		// timeline right part code starts
		'right' => [
			'right_heading_text' => array(
				'type' => 'textarea',
				'label' => __pl('heading_name'),
				'default' => '<h2>1998</h2>',
				'desc' => __pl('Edit the heading here'),
				'edit' => '.pagelayer-timeline-container-right .pagelayer-timeline-content .pagelayer-heading-holder', // Edit the text and also mirror the same
				'req' => ['side' => ['both','right']]
			),		
			'right_paragraph_text' => array(
				'type' => 'editor',
				'label' => __pl('text'),
				'default' => 'Lorem ipsum dolor sit amet',
				'desc' => __pl('Edit the content here or edit directly in the Editor'),
				'edit' => '.pagelayer-timeline-container-right .pagelayer-timeline-content .pagelayer-text-holder', // Edit the text and also mirror the same
				'req' => ['side' => ['both','right']]
			),		
			'right_align' => array(
				'label' => __pl('content_align'),
				'type' => 'radio',
				'addAttr' => 'align="{{align}}"',
				'screen' => 1,
				'default' => 'left',
				'css' => ['{{element}} .pagelayer-timeline-container-right' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				),
				'req' => ['side' => ['both','right']]
			),	
			'right_part_width' => array(
				'type' => 'spinner',
				'label' => __pl('width'),
				'default' => '400',
				'screen' => 1,
				'step' => 1,
				'min' => 0,
				'max' => 100,
				'default' => 90,
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'width:{{val}}%'],
			),
			'right_heading_state' => array(
				'type' => 'radio',
				'label' =>  __pl('state'),
				'default' => 'normal',
				'list' => array(
					'normal' => __pl('normal'),
					'hover' => __pl('hover'),
				),
				'req' => ['side' => ['both','right']]
			),
			
			'right_color' => array(
				'type' => 'color',
				'label' => __pl('heading_color'),
				'default' => '#111111',
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder *' => 'color:{{val}}'],
				'show' => ['right_heading_state' => 'normal']
			),
			
			'right_heading_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder *' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				'{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['right_heading_state' => 'normal']
			),
			
			'right_heading_text_shadow' => array(
				'type' => 'shadow',
				'label' => __pl('text_shadow'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}} !important;'],
				'show' => ['right_heading_state' => 'normal']
			),
			
			'right_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder:hover *' => 'color:{{val}}', '{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder:hover' => 'color:{{val}}'],
				'show' => ['right_heading_state' => 'hover']
			),
			'right_heading_typo_hover' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder:hover *' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;',
				'{{element}} .pagelayer-timeline-container-right .pagelayer-heading-holder:hover' => 'font-family: {{val[0]}} !important; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'show' => ['right_heading_state' => 'hover']
			),
			'right_heading_text_shadow_hover' => array(
				'type' => 'shadow',
				'label' => __pl('text_shadow'),
				'css' => ['{{element}} .pagelayer-timeline-container-right  .pagelayer-heading-holder:hover' => 'text-shadow: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}} !important;'],
				'show' => ['right_heading_state' => 'hover']
			),
			
			'right_bg_hover' => array(
				'type' => 'radio',
				'label' => __pl('row_bg_styles'),
				'default' => '',
				'list' => [
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				],
				'show' => ['side' => ['both','right']]
			),
			'right_bg_type' => array(
				'type' => 'radio',
				'label' => __pl('background_type'),
				'default' => '',
				'list' => [
					'' => __pl('none'),
					'color' => __pl('color'),
					'gradient' => __pl('gradient'),
					'image' => __pl('image'),
				],
				'show' => ['right_bg_hover' => '']
			),
			
			'right_bg_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}}  .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-color: {{val}};'],
				'show' => ['right_bg_hover' => ''],
				'req' => ['right_bg_type' => 'color']
			),
			'right_bg_gradient' => array(
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6,23,#72e584,45,#2ca4eb,100',			
				'css' => ['{{element}}  .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],			
				'show' => ['right_bg_hover' => ''],
				'req' => ['right_bg_type' => 'gradient']
			),
			'right_img_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'default' => '',
				'desc' => __pl('fallback_color'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-color: {{val}};'],
				'show' => ['right_bg_hover' => ''],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_img' => array(
				'type' => 'image',
				'label' => __pl('image'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-image: url("{{{right_bg_img-url}}}");'],
				'show' => ['right_bg_hover' => ''],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_attachment' => array(
				'type' => 'select',
				'label' => __pl('bg_attachment'),
				'list' => [
					'' => __pl('default'),
					'scroll' => __pl('scroll'),
					'fixed' => __pl('fixed')
				],
				'show' => ['right_bg_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-attachment: {{val}};'],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_posx' => array(
				'type' => 'select',
				'label' => __pl('bg_posx'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'left' => __pl('left'),
					'right' => __pl('right'),
					'custom' => __pl('custom')
				],
				'show' => ['right_bg_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-position-x: {{val}};'],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_posx_custom' => array(
				'label' => __pl('custom_x'),
				'type' => 'slider',
				'step' => 1,
				'min' => -5000,
				'max' => 5000,
				'screen' => 1,
				'units' => ['px', 'em', '%'],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-position-x: {{val}};'],
				'req' => array(
					'right_bg_posx' => 'custom'
				),
			),	
			'right_bg_posy' => array(
				'type' => 'select',
				'label' => __pl('bg_posy'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'top' => __pl('top'),
					'bottom' => __pl('bottom'),
					'custom' => __pl('custom')
				],
				'show' => ['right_bg_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-position-y: {{val}};'],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_posy_custom' => array(
				'label' => __pl('custom_y'),
				'type' => 'slider',
				'step' => 1,
				'min' => -5000,
				'max' => 5000,
				'screen' => 1,
				'units' => ['px', 'em', '%'],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-position-y: {{val}};'],
				'req' => array(
					'right_bg_posy' => 'custom'
				),
			),
			'right_bg_repeat' => array(
				'type' => 'select',
				'label' => __pl('bg_repeat'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-repeat: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'repeat' => __pl('repeat'),
					'no-repeat' => __pl('no-repeat'),
					'repeat-x' => __pl('repeat-x'),
					'repeat-y' => __pl('repeat-y'),
				],
				'show' => ['right_bg_hover' => ''],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_size' => array(
				'type' => 'select',
				'label' => __pl('bg_size'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'background-size: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'cover' => __pl('cover'),
					'contain' => __pl('contain')
				],
				'show' => ['right_bg_hover' => ''],
				'req' => ['right_bg_type' => 'image']
			),
			'right_bg_hover_delay' => array(
				'type' => 'spinner',
				'label' => __pl('ele_bg_hover_delay'),
				'min' => 0,
				'step' => 100,
				'max' => 5000,
				'default' => 400,
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => '-webkit-transition: all {{val}}ms !important; transition: all {{val}}ms !important;'],
				'show' => ['right_bg_hover' => 'hover']
			),
			'right_bg_type_hover' => array(
				'type' => 'radio',
				'label' => __pl('background_type'),
				'default' => '',
				'list' => [
					'' => __pl('none'),
					'color' => __pl('color'),
					'gradient' => __pl('gradient'),
					'image' => __pl('image'),
				],
				'show' => ['right_bg_hover' => 'hover']
			),
			'right_bg_color_hover' => array(
				'type' => 'color',
				'label' => __pl('color_hover'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background: {{val}};'],
				'show' => ['right_bg_hover' => 'hover'],
				'req' => ['right_bg_type_hover' => 'color']
			),
			'right_bg_gradient_hover' => array(
				'type' => 'gradient',
				'label' => '',
				'default' => '150,#44d3f6,25,#72e584,75,#2ca4eb,100',
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background: linear-gradient({{val[0]}}deg, {{val[1]}} {{val[2]}}%, {{val[3]}} {{val[4]}}%, {{val[5]}} {{val[6]}}%);'],
				'show' => ['right_bg_hover' => 'hover'],
				'req' => ['right_bg_type_hover' => 'gradient']
			),
			'bg_img_hover' => array(
				'type' => 'image',
				'label' => __pl('image_hover'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background: url("{{{bg_img_hover-url}}}");'],
				'show' => ['right_bg_hover' => 'hover'],
				'req' => ['right_bg_type_hover' => 'image']
			),
			'right_bg_attachment_hover' => array(
				'type' => 'select',
				'label' => __pl('background_attachment'),
				'list' => [
					'' => __pl('default'),
					'scroll' => __pl('scroll'),
					'fixed' => __pl('fixed')
				],
				'show' => ['right_bg_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background-attachment: {{val}};'],
				'req' => ['right_bg_type_hover' => 'image']
			),
			'right_bg_posx_hover' => array(
				'type' => 'select',
				'label' => __pl('horizontal_pos'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'left' => __pl('left'),
					'right' => __pl('right')
				],
				'show' => ['right_bg_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background-position-x: {{val}};'],
				'req' => ['right_bg_type_hover' => 'image']
			),
			'right_bg_posy_hover' => array(
				'type' => 'select',
				'label' => __pl('verticle_pos'),
				'list' => [
					'' => __pl('default'),
					'center' => __pl('center'),
					'top' => __pl('top'),
					'bottom' => __pl('bottom')
				],
				'show' => ['right_bg_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background-position-y: {{val}};'],
				'req' => ['right_bg_type_hover' => 'image']
			),
			'right_bg_repeat_hover' => array(
				'type' => 'select',
				'label' => __pl('repeat'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background-repeat: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'repeat' => __pl('repeat'),
					'no-repeat' => __pl('no-repeat'),
					'repeat-x' => __pl('repeat-x'),
					'repeat-y' => __pl('repeat-y'),
				],
				'show' => ['right_bg_hover' => 'hover'],
				'req' => ['right_bg_type_hover' => 'image']
			),
			'right_bg_size_hover' => array(
				'type' => 'select',
				'label' => __pl('size'),
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'background-size: {{val}};'],
				'list' => [
					'' => __pl('default'),
					'cover' => __pl('cover'),
					'contain' => __pl('contain')
				],
				'show' => ['right_bg_hover' => 'hover'],
				'req' => ['right_bg_type_hover' => 'image']
			),
			
			
			
			'right_timeline_border_hover' => array(
				'type' => 'radio',
				'label' => '',
				'default' => '',
				'list' => [
					'' => __pl('normal'),
					'hover' => __pl('hover'),
				],
				'req' => ['side' => ['both','right']]
			),
			'right_timeline_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'default' => 'solid',
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => ['right_timeline_border_hover' => ''],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content '=> 'border-style: {{val}}'],
			),
			
			'right_timeline_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'default' => '1,1,1,1',
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => [
					'right_timeline_border_hover' => ''
				],
				'req' => [
					'!right_timeline_border_type' => ''
				],
				'css' =>['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			),
			'right_timeline_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'default' => '#CCC',
				'screen' => 1,
				'show' => [
					'right_timeline_border_hover' => ''
				],
				'req' => [
					'!right_timeline_border_type' => ''
				],
				'css' =>['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'border-color: {{val}}'],
			),
			'right_timeline_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => ['right_timeline_border_hover' => ''],
				'req' => [
					'!right_timeline_border_type' => ''
				],
				'default' => '6,6,6,6',
				'css' =>['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			),
			'right_timeline_border_type_hover' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'screen' => 1,
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'show' => ['right_timeline_border_hover' => 'hover'],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'border-style: {{val}}'],
			),
			'right_timeline_border_width_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'units' => ['px', 'em'],
				'screen' => 1,
				'show' => [
					'right_timeline_border_hover' => 'hover'
				],
				'req' => [
					'!right_timeline_border_type_hover' => ''
				],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'border-top-width: {{val[0]}}; border-right-width: {{val[1]}}; border-bottom-width: {{val[2]}}; border-left-width: {{val[3]}}'],
			),
			'right_timeline_border_color_hover' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'screen' => 1,
				'show' => [
					'right_timeline_border_hover' => 'hover'
				],
				'req' => [
					'!right_timeline_border_type_hover' => ''
				],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'border-color: {{val}}'],
			),
			'right_timeline_border_radius_hover' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'units' => ['px', 'em'],
				'show' => ['right_timeline_border_hover' => 'hover'],
				'req' => [
					'!right_timeline_border_type_hover' => ''
				],
				'css' => ['{{element}} .pagelayer-timeline-container-right .pagelayer-timeline-content:hover' => 'border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}}; -webkit-border-radius:  {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};-moz-border-radius: {{val[0]}} {{val[1]}} {{val[2]}} {{val[3]}};'],
			),
		],
		
		'styles' => [
			'timeline_circle' => __pl('timeline_center_circle'),
			'left' => __pl('left'),
			'right' => __pl('right'),	
		]
	)
);

// Before After Slider
pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_before_after', array(
		'name' => __pl('before_after_image'),
		'group' => 'other',
		'html' => '<div class="pagelayer-before-after-container" data-resize-event="{{resize_event}}">
			<div class="pagelayer-before-after-slider">
				<div class="pagelayer-before-image">
					<img src="{{{before_image-url}}}"/>
				</div>
				<div class="pagelayer-after-image">
					<img src="{{{after_image-url}}}"/>
				</div>
				<div class="pagelayer-resizer {{icon}} {{icon_size}} "></div>
				<button if="{{bf_button}}" class="pagelayer-before-btn">{{before_btn_text}}</button>
				<button if="{{bf_button}}" class="pagelayer-after-btn">{{after_btn_text}}</button>
			</div>
		</div>',
		'params' => array(
			'before_image' => array(
				'type' => 'image',
				'label' => __pl('before_image'),
				'default' => PAGELAYER_URL.'/images/default-image.png',
				'retina' => 1,
			),
			'after_image' => array(
				'type' => 'image',
				'label' => __pl('after_image'),
				'default' => PAGELAYER_URL.'/images/default-image.png',
				'retina' => 1,
			),
			'before_after_direction' => array(
				'type' => 'select',
				'label' => __pl('slider_direction'),
				'default' => 'horizontal',
				'addClass' => ['{{element}} .pagelayer-before-after-slider' => 'pagelayer-before-after-slider-{{val}}'],
				'list' => array(
					'horizontal' => __pl('horizontal'),
					'vertical' => __pl('vertical'),
				)
			),
			'offset_horizontal' => array(
				'type' => 'spinner',
				'label' => __pl('slider_offset'),
				'min' => '1',
				'max' => '99.5',
				'step' => '1',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-after-image' => 'clip-path:polygon({{val}}% 0%, 100% 0%, 100% 100%, {{val}}% 100%);','{{element}} .pagelayer-resizer' => 'left:{{val}}%;'],
				'req' => ['before_after_direction' => 'horizontal']
			),
			'offset_vertical' => array(
				'type' => 'spinner',
				'label' => __pl('slider_offset'),
				'min' => '1',
				'max' => '100',
				'step' => '1',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-after-slider-vertical .pagelayer-after-image' => 'clip-path:polygon(0px {{val}}%, 100% {{val}}%, 100% 100%, 0% 100%)','{{element}} .pagelayer-before-after-slider-vertical .pagelayer-resizer' => 'top:{{val}}%;'],
				'req' => ['before_after_direction' => 'vertical']
			),
			'resize_event' => array(
				'type' => 'radio',
				'label' => __pl('slider_type'),
				'default' => 'drag',
				'screen' => 1,
				'list' => array(
					'none' => __pl('none'),
					'drag' => __pl('drag'),
					'hover' => __pl('hover')
				)
			),
			'custom_height' => array(
				'type' => 'checkbox',
				'label' => __pl('custom_height'),
			),
			'height' => array(
				'type' => 'spinner',
				'label' => __pl('slider_height'),
				'min' => '0',
				'max' => '1000',
				'step' => 1,
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-after-slider' => 'height:{{val}}px;'],
				'req' => ["custom_height" => 'true']
			),
			'delay_control' => array(
				'type' => 'slider',
				'label' => __pl('time'),
				'min' => '0.0',
				'max' => '1.0',
				'step' => '0.1',
				'css' => ['{{element}} .pagelayer-resizer,{{element}} .pagelayer-after-image' => 'transition-duration:{{val}}s;'],
			)
			
		),
		'icon_style' => [
			'icon' => array(
				'type' => 'icon',
				'label' => __pl('icon'),
				'default' => 'fas fa-arrows-alt-h',
				'list' => ['arrows-alt-h', 'arrows-alt-v','arrows-alt-h', 'arrow-right', 'arrow-left', 'arrow-circle-right', 'arrow-circle-left', 'arrow-alt-circle-left','arrow-alt-circle-right'],
			),
			'icon_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'css' => ['{{element}} .pagelayer-resizer:before' => 'color:{{val}};'],
			),
			'bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#42414f',
				'css' => ['{{element}} .pagelayer-resizer:before' => 'background: {{val}};'],
			),
			'icon_size' => array(
				'type' => 'select',
				'label' => __pl('icon_size'),
				'default' => 'pagelayer-icon-mini',
				'list' => array(
					'pagelayer-icon-mini' => __pl('mini'),
					'pagelayer-icon-small' => __pl('small'),
					'pagelayer-icon-large' => __pl('large'),
					'pagelayer-icon-extra-large' => __pl('extra_large'),
					'pagelayer-icon-double-large' => __pl('double_large'),
					'pagelayer-icon-custom' => __pl('custom'),
				),
			),
			'icon_size_custom' => array(
				'type' => 'spinner',
				'label' => __pl('icon_size'),
				'min' => '1',
				'max' => '100',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-resizer:before' => 'font-size: {{val}}px'],
				'req' => array(
					'icon_size' => 'pagelayer-icon-custom'
				),
			),
			'icon_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => ['{{element}} .pagelayer-resizer:before' => 'border-style: {{val}}'],
				'list' => [
					'' => __pl('default'),
					'none' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
			),
			'icon_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color'),
				'css' => ['{{element}} .pagelayer-resizer:before' => 'border-color: {{val}};'],
				'req' => array(
					'!icon_border_type' => ['', 'none'],
				),
			),
			'icon_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-resizer:before' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!icon_border_type' => ['', 'none'],
				],
			),
			'icon_border_radius' => array(
				'type' => 'slider',
				'label' => __pl('border_radius'),
				'step' => '1',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-resizer:before' => 'border-radius: {{val}}%; -webkit-border-radius:  {{val}}%;-moz-border-radius: {{val}}%;'],
			),
			'bg_size' => array(
				'type' => 'slider',
				'label' => __pl('icon_bg_size'),
				'step' => '1',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-resizer:before' => 'padding: {{val}}px;'],
			),
			'bg_positionHorizontalY' => array(
				'type' => 'slider',
				'label' => __pl('icon_position'),
				'max' => '100',
				'min' => '0',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-after-slider-horizontal .pagelayer-resizer:before' => 'top: {{val}}%; transform: TranslateX(-50%) TranslateY(-{{val}}%) '],
				'req' => ['before_after_direction' => 'horizontal'],
			),
			'bg_positionVerticalX' => array(
				'type' => 'slider',
				'label' => __pl('icon_position'),
				'max' => '100',
				'min' => '0',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-after-slider-vertical .pagelayer-resizer:before' => 'left: {{val}}%; transform: TranslateX(-{{val}}%) TranslateY(-50%)'],
				'req' => ['before_after_direction' => 'vertical'],
			),
		],
		'resizer_style' => [
			'resizer' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'css' => ['{{element}} .pagelayer-resizer' => 'background:{{val}};'],
			),
			'resizer_width' => array(
				'type' => 'slider',
				'label' => __pl('width'),
				'min' => '0',
				'max' => '20',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-resizer' => 'width: {{val}}px;'],
				'req' => ['before_after_direction' => 'horizontal']
			),
			'resizer_width_vertical' => array(
				'type' => 'slider',
				'label' => __pl('height'),
				'min' => '0',
				'max' => '20',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-resizer' => 'height: {{val}}px;'],
				'req' => ['before_after_direction' => 'vertical']
			),
		],
		'bf_btn_style' =>[
			'bf_button' => array(
				'type' => 'checkbox',
				'label' => __pl('show_btn')
			),
			'button_color' => array(
				'type' => 'color',
				'label' => __pl('color'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'color:{{val}};'],
				'req' => array(
					'bf_button' => 'true'
				),
			),
			'btn_bg_color' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'background-color:{{val}};'],
				'req' => array(
					'bf_button' => 'true'
				),
			),
			'bf_btn_size' => array(
				'type' => 'padding',
				'label' => __pl('size'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'padding-top:{{val[0]}}px;padding-right:{{val[1]}}px;padding-bottom:{{val[0]}}px;padding-left:{{val[1]}}px;'],
				'req' => [
					'bf_button' => 'true',
				]
			),
			'btn_border_type' => array(
				'type' => 'select',
				'label' => __pl('border_type'),
				'css' => [
					'{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'border-style: {{val}};',
					],
				'list' => [
					'' => __pl('none'),
					'solid' => __pl('solid'),
					'double' => __pl('double'),
					'dotted' => __pl('dotted'),
					'dashed' => __pl('dashed'),
					'groove' => __pl('groove'),
				],
				'req' => array(
					'bf_button' => 'true'
				),
			),
			'btn_border_color' => array(
				'type' => 'color',
				'label' => __pl('border_color_label'),
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'border-color: {{val}};'],
				'req' => array(
					'!btn_border_type' => ''
				),
			),
			'btn_border_width' => array(
				'type' => 'padding',
				'label' => __pl('border_width'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'border-top-width: {{val[0]}}px; border-right-width: {{val[1]}}px; border-bottom-width: {{val[2]}}px; border-left-width: {{val[3]}}px'],
				'req' => [
					'!btn_border_type' => ''
				],
			),
			'btn_border_radius' => array(
				'type' => 'padding',
				'label' => __pl('border_radius'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px; -webkit-border-radius:  {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;-moz-border-radius: {{val[0]}}px {{val[1]}}px {{val[2]}}px {{val[3]}}px;'],
				'req' => array(
					'!btn_border_type' => '',
				),
			),
			'btn_position' => array(
				'type' => 'slider',
				'label' => __pl('button_position'),
				'max' => '100',
				'min' => '0',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-after-slider-horizontal .pagelayer-before-btn, {{element}} .pagelayer-before-after-slider-horizontal .pagelayer-after-btn' => 'top: {{val}}%;transform: translateY(-{{val}}%);','{{element}} .pagelayer-before-after-slider-vertical .pagelayer-before-btn, {{element}} .pagelayer-before-after-slider-vertical .pagelayer-after-btn' => 'left: {{val}}%;transform: translateX(-{{val}}%);'],
				'req' => array(
					'bf_button' => 'true',
				),
			),
			'before_btn_text' => array(
				'type' => 'text',
				'label' => __pl('before_text'),
				'default' => __pl('Before'),
				'req' => array(
					'bf_button' => 'true'
				),
			),
			'after_btn_text' => array(
				'type' => 'text',
				'label' => __pl('after_text'),
				'default' => __pl('After'),
				'req' => array(
					'bf_button' => 'true'
				),
			),
			'btn_typo' => array(
				'type' => 'typography',
				'label' => __pl('typography'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn, {{element}} .pagelayer-after-btn' => 'font-family: {{val[0]}}; font-size: {{val[1]}}px !important; font-style: {{val[2]}} !important; font-weight: {{val[3]}} !important; font-variant: {{val[4]}} !important; text-decoration-line: {{val[5]}} !important; text-decoration-style: {{val[6]}} !important; line-height: {{val[7]}}em !important; text-transform: {{val[8]}} !important; letter-spacing: {{val[9]}}px !important; word-spacing: {{val[10]}}px !important;'],
				'req' => ['bf_button' => 'true']
			),
			'before_spacing' => array(
				'type' => 'padding',
				'label' => __pl('before_button_spacing'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-before-btn' => 'margin-top:{{val[0]}}px; margin-right:{{val[1]}}px; margin-bottom:{{val[2]}}px; margin-left:{{val[3]}}px;'],
				'req' => ['bf_button' => 'true']
			),
			'after_spacing' => array(
				'type' => 'padding',
				'label' => __pl('after_button_spacing'),
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-after-btn' => 'margin-top:{{val[0]}}px; margin-right:{{val[1]}}px; margin-bottom:{{val[2]}}px; margin-left:{{val[3]}}px;'],
				'req' => ['bf_button' => 'true']
			)
		],
		'styles' => [
			'icon_style' => __pl('icon_style'),
			'resizer_style' => __pl('resizer'),
			'bf_btn_style' => __pl('Before After Button'),
		]
	)
);

pagelayer_add_shortcode(PAGELAYER_SC_PREFIX.'_image_map', array(
		'name' => __pl('image_map'),
		'group' => 'other',
		'html' => '
		<div class="pagelayer-imgmap-container">
			<div class="pagelayer-imgmap-wrapper">
				<svg class="pagelayer-imgmap-svg">
					<g>{{pagelayer_map_path}}</g>
				</svg>
				<div class="pagelayer-imgmap-coordinates-wraper"></div>
			</div>
			<img class="pagelayer-img pagelayer-animation-{{anim_hover}}" src="{{map_img_id}}" title="{{{map_img-id-title}}}" alt="{{{map_img-id-alt}}}" srcset="{{pagelayer-srcset}}" />
		</div>',
		'params' => array(
			'map_img-id' => array(
				'type' => 'image',
				'label' => __pl('Image'),
				'desc' => __pl('image_src_desc'),
				'default' => PAGELAYER_URL.'/images/default-image.png',
				'retina' => 0,
			),
			'img_map-size' => array(
				'label' => __pl('obj_image_size_label'),
				'type' => 'select',
				'default' => 'full',
				'list' => array(
					'full' => __pl('full'),
					'large' => __pl('large'),
					'medium' => __pl('medium'),
					'thumbnail' => __pl('thumbnail'),
					'custom' => __pl('custom')
				)
			),
			'custom_size' => array(
				'label' => __pl('image_custom_size_label'),
				'type' => 'text',
				'screen' => 1,
				'default' => '100x100',
				'sep' => 'x',
				'css' => ['{{element}} img' => 'width: {{val[0]}}px; height: {{val[1]}}px;'],
				'req' => array(
					'img_map-size' => 'custom'
				),
			),
			'align' => array(
				'label' => __pl('obj_align_label'),
				'type' => 'radio',
				'default' => 'center',
				'screen' => 1,
				'css' => ['{{element}} .pagelayer-imgmap-container' => 'text-align: {{val}}'],
				'list' => array(
					'left' => __pl('left'),
					'center' => __pl('center'),
					'right' => __pl('right')
				)
			),
			'max-width' => array(
				'label' => __pl('max-width-percent'),
				'type' => 'slider',
				'min' => 0,
				'max' => 100,
				'screen' => 1,
				'css' => ['{{element}} img' => 'max-width: {{val}}%'],
			),
			'pagelayer_image_map' => array(
				'type' => 'hidden',
				'default' => ''
			),
		),
		'area_styles' => [
			'show_area' =>array(
				'label' => __pl('show_area' ),
				'desc' => __pl('show_area_to_live'),
				'type' => 'checkbox',
				'css' => ['{{element}} .pagelayer-imgmap-item.pagelayer-map-item-active, {{element}} .pagelayer-imgmap-hover-active .pagelayer-imgmap-item:hover' => 'opacity: 1'],
			),
			'area_bg' => array(
				'type' => 'color',
				'label' => __pl('bg_color'),
				'default' => '#FFABAB',
				'css' => ['{{element}} .pagelayer-imgmap-item.pagelayer-map-item-active, {{element}} .pagelayer-map-item-active_is_editable, {{element}} .pagelayer-imgmap-hover-active .pagelayer-imgmap-item:hover' => 'fill: {{val}}'],
				'req' => array(
					'show_area' => 'true'
				)
			),
			'area_cord_color' => array(
				'type' => 'color',
				'label' => __pl('cord_color'),
				'default' => '#F70E0E',
				'css' => ['{{element}} .pagelayer-imgmap-item.pagelayer-map-item-active, {{element}} .pagelayer-map-item-active_is_editable, {{element}} .pagelayer-imgmap-hover-active .pagelayer-imgmap-item:hover' => 'stroke: {{val}}'],
				'req' => array(
					'show_area' => 'true'
				)
			)
		],
		'styles' => [
			'area_styles' => __pl('Area Style')
		],
	)
);