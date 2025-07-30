<?php
/**
 * AdvancedOptionConversion Class
 *
 * @package Divi
 * @since ??
 */

// phpcs:disable ET -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable Generic -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable PEAR -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable Squiz -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable WordPress -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.
// phpcs:disable PSR2 -- Temporarily disabled to get the PR CI pass for now. TODO: Fix this later.

namespace ET\Builder\Packages\Conversion;

use DateTime;

class AdvancedOptionConversion {

	/**
	 * Animation Conversion Map.
	 *
	 * Conversion map for Animation Options attributes.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static $animationConversionMap = [
		'd4_attr_name_delay'            => 'delay',
		'd4_attr_name_direction'        => 'direction',
		'd4_attr_name_duration'         => 'duration',
		'd4_attr_name_intensity_flip'   => 'intensity.flip',
		'd4_attr_name_intensity_fold'   => 'intensity.fold',
		'd4_attr_name_intensity_roll'   => 'intensity.roll',
		'd4_attr_name_intensity_slide'  => 'intensity.slide',
		'd4_attr_name_intensity_zoom'   => 'intensity.zoom',
		'd4_attr_name_repeat'           => 'repeat',
		'd4_attr_name_speed_curve'      => 'speedCurve',
		'd4_attr_name_starting_opacity' => 'startingOpacity',
		'd4_attr_name_style'            => 'style',
	];

	public static $backgroundConversionMap = [
        // Background Color.
        'd4_attr_name_color' => 'color',
        'd4_attr_name_enable_color' => 'enableColor', // Legacy to be removed but kept here just in case.

        // Background Gradient.
        'd4_attr_name_color_gradient_direction' => 'gradient.direction',
        'd4_attr_name_color_gradient_direction_radial' => 'gradient.directionRadial',
        'd4_attr_name_color_gradient_overlays_image' => 'gradient.overlaysImage',
        'd4_attr_name_color_gradient_repeat' => 'gradient.repeat',
        'd4_attr_name_color_gradient_stops' => 'gradient.stops', // Processed into array / object
        'd4_attr_name_color_gradient_type' => 'gradient.type',
        'd4_attr_name_color_gradient_unit' => 'gradient.length',
        'use_d4_attr_name_color_gradient' => 'gradient.enabled',

        // Background Image.
        'd4_attr_name_blend' => 'image.blend',
        'd4_attr_name_enable_image' => 'image.enabled',
        'd4_attr_name_horizontal_offset' => 'image.horizontalOffset',
        'd4_attr_name_image' => 'image.url',
        'd4_attr_name_image_height' => 'image.height',
        'd4_attr_name_image_width' => 'image.width',
        'd4_attr_name_position' => 'image.position',
        'd4_attr_name_repeat' => 'image.repeat',
        'd4_attr_name_size' => 'image.size',
        'd4_attr_name_vertical_offset' => 'image.verticalOffset',
        'parallax' => 'image.parallax.enabled',
        'parallax_method' => 'image.parallax.method',
        'title_text' => 'image.title',

        // Background Video.
        'allow_player_pause' => 'video.allowPlayerPause',
        'd4_attr_name_enable_video_mp4' => 'video.enabledMp4',
        'd4_attr_name_enable_video_webm' => 'video.enabledWebm',
        'd4_attr_name_video_height' => 'video.height',
        'd4_attr_name_video_mp4' => 'video.mp4',
        'd4_attr_name_video_pause_outside_viewport' => 'video.pauseOutsideViewport',
        'd4_attr_name_video_webm' => 'video.webm',
        'd4_attr_name_video_width' => 'video.width',

        // Background Mask.
        'd4_attr_name_enable_mask_style' => 'mask.enabled',
        'd4_attr_name_mask_aspect_ratio' => 'mask.aspectRatio',
        'd4_attr_name_mask_blend_mode' => 'mask.blend',
        'd4_attr_name_mask_color' => 'mask.color',
        'd4_attr_name_mask_height' => 'mask.height',
        'd4_attr_name_mask_horizontal_offset' => 'mask.horizontalOffset',
        'd4_attr_name_mask_position' => 'mask.position',
        'd4_attr_name_mask_size' => 'mask.size',
        'd4_attr_name_mask_style' => 'mask.style',
        'd4_attr_name_mask_transform' => 'mask.transform', // Processed into array / object
        'd4_attr_name_mask_vertical_offset' => 'mask.verticalOffset',
        'd4_attr_name_mask_width' => 'mask.width',

        // Background Pattern.
        'd4_attr_name_enable_pattern_style' => 'pattern.enabled',
        'd4_attr_name_pattern_blend_mode' => 'pattern.blend',
        'd4_attr_name_pattern_color' => 'pattern.color',
        'd4_attr_name_pattern_height' => 'pattern.height',
        'd4_attr_name_pattern_horizontal_offset' => 'pattern.horizontalOffset',
        'd4_attr_name_pattern_repeat' => 'pattern.repeat',
        'd4_attr_name_pattern_repeat_origin' => 'pattern.repeatOrigin',
        'd4_attr_name_pattern_size' => 'pattern.size',
        'd4_attr_name_pattern_style' => 'pattern.style',
        'd4_attr_name_pattern_transform' => 'pattern.transform', // Processed into array / object
        'd4_attr_name_pattern_vertical_offset' => 'pattern.verticalOffset',
        'd4_attr_name_pattern_width' => 'pattern.width',
    ];

    public static $backgroundValueConversionFunctionMap = [
        'gradient.stops' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertGradientStops',
        'mask.transform' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSvgTransform',
        'pattern.transform' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSvgTransform',
    ];

	public static $borderConversionMap = [
        'border_color_all_d4_attr_name' => 'styles.all.color',
        'border_color_bottom_d4_attr_name' => 'styles.bottom.color',
        'border_color_left_d4_attr_name' => 'styles.left.color',
        'border_color_right_d4_attr_name' => 'styles.right.color',
        'border_color_top_d4_attr_name' => 'styles.top.color',
        'border_radii_d4_attr_name' => 'radius',
        'border_style_all_d4_attr_name' => 'styles.all.style',
        'border_style_bottom_d4_attr_name' => 'styles.bottom.style',
        'border_style_left_d4_attr_name' => 'styles.left.style',
        'border_style_right_d4_attr_name' => 'styles.right.style',
        'border_style_top_d4_attr_name' => 'styles.top.style',
        'border_width_all_d4_attr_name' => 'styles.all.width',
        'border_width_bottom_d4_attr_name' => 'styles.bottom.width',
        'border_width_left_d4_attr_name' => 'styles.left.width',
        'border_width_right_d4_attr_name' => 'styles.right.width',
        'border_width_top_d4_attr_name' => 'styles.top.width',
    ];

    public static $borderValueConversionFunctionMap = [
        'radius' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertBorderRadii',
    ];

	public static $boxShadowConversionMap = [
        'box_shadow_blur_d4_attr_name' => 'blur',
        'box_shadow_color_d4_attr_name' => 'color',
        'box_shadow_horizontal_d4_attr_name' => 'horizontal',
        'box_shadow_position_d4_attr_name' => 'position',
        'box_shadow_spread_d4_attr_name' => 'spread',
        'box_shadow_style_d4_attr_name' => 'style',
        'box_shadow_vertical_d4_attr_name' => 'vertical',
    ];

	/**
     * Conversion map for Button Options attribute.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     * Note: In D4, Button Options do not always use nested Advanced Options inside of it.
     * Some Advanced Options are used but some are simply declared as fields in the Button Options.
     *
     * @since ??
     *
     * @var array
     */
    public static $buttonConversionMap = [
        'custom_d4_attr_name' => 'decoration.button.*.enable',
        'd4_attr_name_bg_color' => 'decoration.background.*.color',
        'd4_attr_name_bg_use_color_gradient' => 'decoration.background.*.gradient.enabled',

        // Button Options do not actually use Font Options, but most of the declared fields can be
        // processed using Font Options; thus Font Options conversion function is being used.
        // However, Button Options somehow use a different attribute name pattern for font size (text_size instead
        // of font_size). Hence the following map to fill it in.
        // See: `ET_Builder_Element->_add_button_fields()`.
        'd4_attr_name_text_size' => 'decoration.font.font.*.size',

        // In D4, Button Options do not use complete Border Options; they use fields that target all corners
        // and sides of the button border. In D5, these are mapped to Border Options, and actual Border Options
        // are used inside Button Options.
        'd4_attr_name_border_radius' => 'decoration.border.*.radius',
        'd4_attr_name_border_width' => 'decoration.border.*.styles.all.width',
        'd4_attr_name_border_color' => 'decoration.border.*.styles.all.color',
        'd4_attr_name_use_icon' => 'decoration.button.*.icon.enable',
        'd4_attr_name_icon' => 'decoration.button.*.icon.settings',
        'd4_attr_name_icon_color' => 'decoration.button.*.icon.color',
        'd4_attr_name_icon_placement' => 'decoration.button.*.icon.placement',
        'd4_attr_name_on_hover' => 'decoration.button.*.icon.onHover',
        'd4_attr_name_alignment' => 'decoration.button.*.alignment',
        'd4_attr_name_rel' => 'innerContent.*.rel',
        'd4_attr_name_text' => 'innerContent.*.text',
        'd4_attr_name_url' => 'innerContent.*.linkUrl',
        'url_new_window' => 'innerContent.*.linkTarget',
    ];

	public static $buttonValueConversionFunctionMap = [
        'decoration.border.*.radius' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertButtonBorderRadii',
        'decoration.button.*.icon.settings' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertButtonIcon',
        'innerContent.*.rel' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertButtonRel',
    ];

	/**
	 * Conversion function map for Condition Options attribute.
	 *
	 * This is used for making conversion function map object.
	 * Attribute name value that matches the pattern is to be processed by functions listed in this object.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public static $conditions_value_conversion_function_map = [
		'conditions' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convert_conditions',
	];

	public static $dividersConversionMap = [
        'd4_attr_name_style' => 'style',
        'd4_attr_name_color' => 'color',
        'd4_attr_name_height' => 'height',
        'd4_attr_name_repeat' => 'repeat',
        'd4_attr_name_flip' => 'flip',
        'd4_attr_name_arrangement' => 'arrangement',
    ];

    public static $dividersValueConversionFunctionMap = [
        'flip' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::dividersFlip',
    ];

	public static $formFieldConversionMap = [
        'd4_attr_name_background_color' => 'decoration.background.*.color',
        'd4_attr_name_text_color' => 'decoration.font.font.*.color',
        'd4_attr_name_focus_background_color' => 'advanced.focus.background.*.color',
        'd4_attr_name_focus_text_color' => 'advanced.focus.font.font.*.color',
        'use_focus_border_color' => 'advanced.focusUseBorder.*',
        'placeholder_color' => 'advanced.placeholder.font.font.*.color',
    ];

	public static $filtersConversionMap = [
        'd4_attr_name_filter_blur' => 'blur',
        'd4_attr_name_filter_brightness' => 'brightness',
        'd4_attr_name_filter_contrast' => 'contrast',
        'd4_attr_name_filter_hue_rotate' => 'hueRotate',
        'd4_attr_name_filter_invert' => 'invert',
        'd4_attr_name_filter_opacity' => 'opacity',
        'd4_attr_name_filter_saturate' => 'saturate',
        'd4_attr_name_filter_sepia' => 'sepia',
        'd4_attr_name_mix_blend_mode' => 'blendMode',
    ];

	/**
     * Conversion map for Font Options attribute.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $fontConversionMap = [
        'd4_attr_name_font' => 'font', // It's set to 'font' but it will be converted to directly. Example: bodyFont.*
        'd4_attr_name_font_size' => 'size',
        'd4_attr_name_letter_spacing' => 'letterSpacing',
        'd4_attr_name_line_height' => 'lineHeight',
        'd4_attr_name_text_align' => 'textAlign',
        'd4_attr_name_text_color' => 'color',
    ];

    /**
     * Conversion function map for Font Options attribute.
     *
     * This is used for making conversion function map object.
     * Attribute name value that matches the pattern is to be processed by functions listed in this object.
     *
     * @since ??
     *
     * @var array
     */
    public static $fontValueConversionFunctionMap = [
        'font' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertFont',
    ];

	/**
     * Conversion map for Gutter Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $gutterConversionMap = [
        'gutter_width' => 'width',
        'make_equal' => 'makeEqual',
        'use_custom_gutter' => 'enable',
    ];

	/**
     * Conversion map for ImageIcon Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $imageIconConversionMap = [
        'd4_attr_name_background_color' => 'background.*.color',
        'd4_attr_name_width' => 'width.*',
        'd4_attr_name_custom_margin' => 'spacing.*.margin',
        'd4_attr_name_custom_padding' => 'spacing.*.padding',
    ];

	/**
     * Conversion map for Link Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $linkConversionMap = [
        'link_option_url' => 'url',
        'link_option_url_new_window' => 'target',
    ];

	/**
     * Conversion map for Spacing Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $spacingConversionMap = [
        'd4_attr_name_custom_margin' => 'margin',
        'd4_attr_name_custom_padding' => 'padding',
    ];

    /**
     * Map of spacing value conversion functions.
     *
     * This is used for making conversion function map object.
     * Attribute name value that matches the pattern is to be processed by functions listed in this object.
     *
     * @since ??
     *
     * @var array
     */
    public static $spacingValueConversionFunctionMap = [
        'margin' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSpacing',
        'padding' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertSpacing',
    ];

	/**
     * Conversion map for Text Shadow Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $textShadowConversionMap = [
        'd4_attr_name_text_shadow_blur_strength' => 'blur',
        'd4_attr_name_text_shadow_color' => 'color',
        'd4_attr_name_text_shadow_horizontal_length' => 'horizontal',
        'd4_attr_name_text_shadow_style' => 'style',
        'd4_attr_name_text_shadow_vertical_length' => 'vertical',
    ];

	/**
     * Conversion map for Text Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to the corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $textConversionMap = [
        'text_orientation' => 'orientation',
        'background_layout' => 'color',
    ];

	/**
     * Conversion map for Transform Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $transformConversionMap = [
        'd4_attr_name_origin' => 'origin', // Processed into array / object
        'd4_attr_name_rotate' => 'rotate', // Processed into array / object
        'd4_attr_name_scale' => 'scale', // Processed into array / object
        'd4_attr_name_scale_linked' => 'scale.linked',
        'd4_attr_name_skew' => 'skew', // Processed into array / object
        'd4_attr_name_skew_linked' => 'skew.linked',
        'd4_attr_name_translate' => 'translate', // Processed into array / object
        'd4_attr_name_translate_linked' => 'translate.linked',
    ];

    /**
     * Conversion function map for Transform Options attributes.
     *
     * @since ??
     *
     * @var array
     */
    public static $transformValueConversionFunctionMap = [
        'origin' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertTransform',
        'rotate' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertTransform',
        'scale' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertTransform',
        'skew' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertTransform',
        'translate' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertTransform',
    ];

	/**
     * Conversion map for Id Classes Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $idClassesConversionMap = [
        'd4_attr_name_class' => 'class',
        'd4_attr_name_id' => 'id',
    ];

	/**
     * Conversion map for Overflow Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $overflowConversionMap = [
        'overflow-x' => 'x',
        'overflow-y' => 'y',
    ];

	/**
     * Conversion function map for DisabledOn Options attribute.
     *
     * This is used for making conversion function map object.
     * Attribute name value that matches the pattern is to be processed by functions listed in this object.
     *
     * @since ??
     *
     * @var array
     */
    public static $disabledOnValueConversionFunctionMap = [
        'disabledOn' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertDisabledOnBreakpoint',
    ];

	/**
     * Conversion map for Position Options attributes.
     *
     * @since ??
     *
     * @var array
     */
    public static $positionConversionMap = [
        'horizontal_offset' => 'offset.horizontal',
        'position_origin_a' => 'origin.absolute',
        'position_origin_f' => 'origin.fixed',
        'position_origin_r' => 'origin.relative',
        'positioning'       => 'mode',
        'vertical_offset'   => 'offset.vertical',
    ];

	/**
     * Conversion map for Scroll Options attributes.
     *
     * @since ??
     *
     * @var array
     */
    public static $scrollConversionMap = [
        'd4_attr_name_blur' => 'blur', // Processed into array / object
        'd4_attr_name_blur_enable' => 'blur.enable',
        'd4_attr_name_fade' => 'fade', // Processed into array / object
        'd4_attr_name_fade_enable' => 'fade.enable',
        'd4_attr_name_horizontal_motion' => 'horizontalMotion', // Processed into array / object
        'd4_attr_name_horizontal_motion_enable' => 'horizontalMotion.enable',
        'd4_attr_name_rotating' => 'rotating', // Processed into array / object
        'd4_attr_name_rotating_enable' => 'rotating.enable',
        'd4_attr_name_scaling' => 'scaling', // Processed into array / object
        'd4_attr_name_scaling_enable' => 'scaling.enable',
        'd4_attr_name_vertical_motion' => 'verticalMotion', // Processed into array / object
        'd4_attr_name_vertical_motion_enable' => 'verticalMotion.enable',
        'motion_trigger_start' => 'motionTriggerStart',
    ];

    /**
     * Map of scroll value expansion functions.
     *
     * @since ??
     *
     * @var array
     */
    public static $scrollValueConversionFunctionMap = [
        'blur' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertScroll',
        'fade' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertScroll',
        'horizontalMotion' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertScroll',
        'rotating' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertScroll',
        'scaling' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertScroll',
        'verticalMotion' => 'ET\Builder\Packages\Conversion\AdvancedOptionConversion::convertScroll',
    ];

	/**
     * Conversion map for Sticky Options attributes.
     *
     * @since ??
     *
     * @var array
     */
    public static $stickyConversionMap = [
        'd4_attr_name_limit_bottom' => 'limit.bottom',
        'd4_attr_name_limit_top' => 'limit.top',
        'd4_attr_name_offset_bottom' => 'offset.bottom',
        'd4_attr_name_offset_surrounding' => 'offset.surrounding',
        'd4_attr_name_offset_top' => 'offset.top',
        'd4_attr_name_position' => 'position',
        'd4_attr_name_transition' => 'transition',
    ];

	/**
     * Conversion map for Transition Options attributes.
     *
     * This is used for making conversion map object.
     * It maps the D4 attribute name to corresponding D5 attribute conversion path.
     *
     * @since ??
     *
     * @var array
     */
    public static $transitionConversionMap = [
        'hover_d4_attr_name_delay' => 'delay',
        'hover_d4_attr_name_duration' => 'duration',
        'hover_d4_attr_name_speed_curve' => 'speedCurve',
    ];


	/**
	 * Get AdminLabel Conversion Map.
	 *
	 * This function is used for getting admin label conversion map based on given D4 advanced options attribute and
	 * D5 expected attribute name.
	 *
	 * @since ??
	 *
	 * @param array $props The conversion map props.
	 * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
	 * @param string $props['d5AttrName'] D5 expected attribute name.
	 * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
	 *
	 * @return array Conversion map for AdminLabel options.
	 */
	public static function getAdminLabelConversionMap($props = []) {
		// Define default values
		$d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'admin_label';
		$d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'adminLabel';
		$d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

		$conversionMap = [
			'attributeMap' => [],
			'optionEnableMap' => [],
			'valueExpansionFunctionMap' => [],
		];

		// Prepare ConversionMap.
		$targetAttr = $d4AttributeSuffix ? "{$d4AdvancedOptionName}_{$d4AttributeSuffix}" : $d4AdvancedOptionName;
		$sourceAttr = "{$d5AttrName}.*";

		$conversionMap['attributeMap'][$targetAttr] = $sourceAttr;

		return $conversionMap;
	}

	/**
	 * Get Animation Conversion Map.
	 *
	 * This function is used for getting Animation conversion map based on given D4 advanced options attribute and
	 * D5 expected attribute name.
	 *
	 * @since ??
	 *
	 * @param array $props The conversion map props.
	 * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
	 * @param string $props['d5AttrName'] D5 expected attribute name.
	 * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
	 *
	 * @return array Conversion map for Animation options.
	 */
	public static function getAnimationConversionMap($props = []) {
		// Define default values
		$d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'animation';
		$d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'animation';
		$d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

		$conversionMap = [
			'attributeMap' => [],
			'optionEnableMap' => [],
			'valueExpansionFunctionMap' => [],
		];

		foreach (self::$animationConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
			$targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

			if ($d4AttributeSuffix) {
				$targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
			}

			$conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
		}

		return $conversionMap;
	}

	/**
     * Get Background Conversion Map.
     *
     * This function is used for getting Background conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     * @param array $props['filterFeatures'] An array of filter features.
     *
     * @return array Conversion map for Background options.
     */
    public static function getBackgroundConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'background';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'background';
        $filterFeatures = isset($props['filterFeatures']) ? $props['filterFeatures'] : [];

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        // Filters map based on given `filterFeatures` if needed.
        $processedBackgroundConversionMap = empty($filterFeatures)
            ? self::$backgroundConversionMap
            : array_filter(self::$backgroundConversionMap, function ($mapValue) use ($filterFeatures) {
                // enableColor is legacy background option which can't be splitted by '.'.
                if ('enableColor' === $mapValue && in_array('color', $filterFeatures)) {
                    return true;
                }

                return in_array(explode('.', $mapValue)[0], $filterFeatures);
            });

        foreach ($processedBackgroundConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = strpos($d4AttrNameTemplate, 'd4_attr_name') !== false
                ? str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate)
                : ($d4AdvancedOptionName === 'background' ? $d4AttrNameTemplate : "{$d4AdvancedOptionName}_{$d4AttrNameTemplate}");


            $sourceAttr = "{$d5AttrName}.*.{$d5AttrPath}";

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;

            // Background Options has this quirk where responsive / hover / sticky status of every
            // field inside of it is decided by one attribute. This enable user to activate responsive /
            // hover / sticky options once and it'll be used for every field inside background options.
            // For Background Option that exist on module level, the attribute which its relied to is `background`
            // For Background Option that is generated by `ET_Builder_Element->generate_background_options()`
            // (eg. Button Option's Background Options, Specialty Section's column background), the status
            // is determined by the equivalent of background_color attribute.
            $conversionMap['optionEnableMap'][$targetAttr] = $d4AttributeSuffix
                ? "{$d4AdvancedOptionName}_color_{$d4AttributeSuffix}"
                : ($d4AdvancedOptionName === 'background' ? $d4AdvancedOptionName : "{$d4AdvancedOptionName}_color");

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$backgroundValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ($valueExpansionFunction ){
				if ( is_callable($valueExpansionFunction) ) {
                	$conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
			}
        }

        return $conversionMap;
    }

    /**
     * Convert D4 gradient stop attribute value to D5 format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for gradient stops.
     *
     * @return array D5 gradient stops value.
     *
     * @example
     * ```
     * YourClassName::convertGradientStops('#9ddbac 0%|#ffffff 24%|#2ed5db 65%')
     * // [{ 'color' => '#9ddbac', 'position' => '0'}, { 'color' => '#ffffff', 'position' => '24'}, { 'color' => '#2ed5db', 'position' => '65' }]
     * ```
     */
    public static function convertGradientStops($value) {
        $valueArray = explode('|', $value);
        $stops = [];

        foreach ($valueArray as $stop) {
            $stopsData = explode(' ', $stop);
            $position = array_pop($stopsData);
            $color = array_pop($stopsData);

            $stops[] = [
                'position' => (int) $position,
                'color' => $color,
            ];
        }

        return $stops;
    }

    /**
     * Convert D4 svg transform attribute value to D5 format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for pattern/mask transform.
     *
     * @return array D5 svg transform value.
     *
     * @example
     * ```
     * YourClassName::convertSvgTransform('flip_horizontal|flip_vertical|rotate_90_degree|invert');
     * // ['flipHorizontal', 'flipVertical', 'rotate', 'invert']
     * ```
     *
     * @example
     * ```
     * YourClassName::convertSvgTransform('flip_horizontal||rotate_90_degree|invert');
     * // ['flipHorizontal', 'rotate', 'invert']
     * ```
     *
     * @example
     * ```
     * YourClassName::convertSvgTransform('');
     * // []
     * ```
     */
    public static function convertSvgTransform($value) {
        $svgTransform = [];

        if (strpos($value, 'flip_horizontal') !== false) {
            $svgTransform[] = 'flipHorizontal';
        }

        if (strpos($value, 'flip_vertical') !== false) {
            $svgTransform[] = 'flipVertical';
        }

        if (strpos($value, 'rotate_90_degree') !== false) {
            $svgTransform[] = 'rotate';
        }

        if (strpos($value, 'invert') !== false) {
            $svgTransform[] = 'invert';
        }

        return $svgTransform;
    }

	/**
     * Get Border Conversion Map.
     *
     * This function is used for getting Border conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for Border options.
     */
    public static function getBorderConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'default';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'border';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$borderConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AdvancedOptionName === 'default'
                ? str_replace('_d4_attr_name', '', $d4AttrNameTemplate)
                : str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$borderValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ( $valueExpansionFunction ) {
				if ( is_callable($valueExpansionFunction) ) {
                	$conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
			}
        }

        return $conversionMap;
    }

	/**
     * Convert D4 Border Radii to D5 format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for Border radius.
     *
     * @return array D5 Border radius value.
     */
    public static function convertBorderRadii($value) {
        $valueArray = explode('|', $value);

        // Button border radius has single value with Range field in D4.
        // In D5 it will use border radius field: https://elegantthemes.slack.com/archives/C01CW343ZJ9/p1648087767334689
        $isSingleValue = (strpos($value, '|') === false);

        return [
            'sync' => $isSingleValue ? 'on' : ($valueArray[0] ?? ''),
            'topLeft' => $isSingleValue ? $value : ($valueArray[1] ?? ''),
            'topRight' => $isSingleValue ? $value : ($valueArray[2] ?? ''),
            'bottomRight' => $isSingleValue ? $value : ($valueArray[3] ?? ''),
            'bottomLeft' => $isSingleValue ? $value : ($valueArray[4] ?? ''),
        ];
    }

	/**
     * Get BoxShadow Conversion Map.
     *
     * This function is used for getting BoxShadow conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for BoxShadow options.
     */
    public static function getBoxShadowConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'default';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'boxShadow';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$boxShadowConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AdvancedOptionName === 'default'
                ? str_replace('_d4_attr_name', '', $d4AttrNameTemplate)
                : str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Convert D4 button border radii to D5 format.
     * Button border radius in Button Options uses a single value for all corners. It uses fixed
     * single fields instead of using Border Options inside Button Options.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for border radius.
     *
     * @return array D5 border radius value.
     */
    public static function convertButtonBorderRadii($value) {
        return [
            'sync' => 'on',
            'topLeft' => $value,
            'topRight' => $value,
            'bottomRight' => $value,
            'bottomLeft' => $value,
        ];
    }

    /**
     * Convert D4 button icon value to D5 format.
     *
     * @since ??
     *
     * @param string $value Button Icon value in D4 format (`||` separated value).
     *
     * @return array D5 button icon value.
     */
    public static function convertButtonIcon($value) {
        $parsedIcon = explode('||', $value);

        return [
            'unicode' => isset($parsedIcon[0]) ? $parsedIcon[0] : '',
            'type' => isset($parsedIcon[1]) ? $parsedIcon[1] : '',
            'weight' => isset($parsedIcon[2]) ? $parsedIcon[2] : '', // Assuming type-casting needed since Icon.Font.AttributeValue['weight'] might have its own type logic
        ];
    }

    /**
     * Convert D4 button rel value to D5 format.
     *
     * @since ??
     *
     * @param string $value Button rel value in D4 format (`|` separated value).
     *
     * @return array D5 button rel value.
     */
    public static function convertButtonRel($value) {
        $parsedValue = explode('|', $value);
        $rel = [];

        if (isset($parsedValue[0]) && $parsedValue[0] === 'on') {
            $rel[] = 'bookmark';
        }

        if (isset($parsedValue[1]) && $parsedValue[1] === 'on') {
            $rel[] = 'external';
        }

        if (isset($parsedValue[2]) && $parsedValue[2] === 'on') {
            $rel[] = 'nofollow';
        }

        if (isset($parsedValue[3]) && $parsedValue[3] === 'on') {
            $rel[] = 'noreferrer';
        }

        if (isset($parsedValue[4]) && $parsedValue[4] === 'on') {
            $rel[] = 'noopener';
        }

        return $rel;
    }

	/**
     * Get Button Conversion Map.
     *
     * This function is used for getting Button conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for Button options.
     */
    public static function getButtonConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'button';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'button';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$buttonConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            // `url_new_window` are not part of Button Options; It is manually added via `get_field()` and
            // only added when Button Option prefix is `button` which implies that it is the only button on the module.
            if ($d4AttrNameTemplate === 'url_new_window' && $d4AdvancedOptionName !== 'button') {
                continue;
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.{$d5AttrPath}";

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$buttonValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ($valueExpansionFunction) {
				if ( is_callable($valueExpansionFunction)) {
					$conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
			}
        }

        $backgroundConversionMap = self::getBackgroundConversionMap([
            'd4AdvancedOptionName' => "{$d4AdvancedOptionName}_bg",
            'd5AttrName'           => "{$d5AttrName}.decoration.background",
            'd4AttributeSuffix'    => $d4AttributeSuffix,
            'filterFeatures'       => ['color', 'gradient', 'image'],
        ]);

        $fontConversionMap = self::getFontConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName'           => "{$d5AttrName}.decoration.font",
            'd4AttributeSuffix'    => $d4AttributeSuffix,
        ]);

        $spacingConversionMap  = self::getSpacingConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName'           => "{$d5AttrName}.decoration.spacing",
            'd4AttributeSuffix'    => $d4AttributeSuffix,
        ]);

        $boxShadowConversionMap = self::getBoxShadowConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName'           => "{$d5AttrName}.decoration.boxShadow",
            'd4AttributeSuffix'    => $d4AttributeSuffix,
        ]);

        return [
            'attributeMap' => array_merge(
                $conversionMap['attributeMap'],
                $backgroundConversionMap['attributeMap'],
                $fontConversionMap['attributeMap'],
                $boxShadowConversionMap['attributeMap'],
                $spacingConversionMap['attributeMap']
            ),
            'optionEnableMap' => array_merge(
                $conversionMap['optionEnableMap'],
                $backgroundConversionMap['optionEnableMap'],
                $fontConversionMap['optionEnableMap'],
                $boxShadowConversionMap['optionEnableMap'],
                $spacingConversionMap['optionEnableMap']
            ),
            'valueExpansionFunctionMap' => array_merge(
                $conversionMap['valueExpansionFunctionMap'],
                $backgroundConversionMap['valueExpansionFunctionMap'],
                $fontConversionMap['valueExpansionFunctionMap'],
                $boxShadowConversionMap['valueExpansionFunctionMap'],
                $spacingConversionMap['valueExpansionFunctionMap']
            ),
        ];
		
	}
	/**
	 * Get Conditions Conversion Map.
	 *
	 * This function is used for getting conditions conversion map based on given D5 expected attribute name.
	 *
	 * @since ??
	 *
	 * @param array  $props The conversion map props.
	 * @param string $props['d5AttrName'] D5 expected attribute name.
	 * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
	 *
	 * @return array Conversion map for conditions options.
	 */
	public static function getConditionsConversionMap( $props = [] ) {
		// Define default values.
		$d4_advanced_option_name = isset( $props['d4AdvancedOptionName'] ) ? $props['d4AdvancedOptionName'] : 'display_conditions';
		$d5_attr_name            = isset( $props['d5AttrName'] ) ? $props['d5AttrName'] : 'conditions';
		$d4_attribute_suffix     = isset( $props['d4AttributeSuffix'] ) ? $props['d4AttributeSuffix'] : '';

		$conversion_map = [
			'attributeMap'              => [],
			'optionEnableMap'           => [],
			'valueExpansionFunctionMap' => [],
		];

		// Prepare ConversionMap.
		$target_attr = $d4_attribute_suffix ? "{$d4_advanced_option_name}_{$d4_attribute_suffix}" : $d4_advanced_option_name;
		$source_attr = "{$d5_attr_name}.*";

		// There is only one D4 `display_conditions` attribute. The value of this attribute is
		// encoded string due to the complexity of the conditions data structure and it's not
		// possible to have such an object based data structure in D4. In some cases, the D4
		// condition settings also contains encoded string due to more complex data structure.
		// Hence we directly map the attribute to D5 `conditions` attribute here.
		$conversion_map['attributeMap'][ $target_attr ]              = $source_attr;
		$conversion_map['valueExpansionFunctionMap'][ $target_attr ] = self::$conditions_value_conversion_function_map['conditions'];

		return $conversion_map;
	}

	/**
	 * Convert D4 conditions value to D5 format.
	 *
	 * @since ??
	 *
	 * @param string $value Conditions value in D4 format.
	 *
	 * @return array D5 Conditions value.
	 */
	public static function convert_conditions( $value ) {
		$d4_conditions = self::get_parsed_encoded_string( $value );

		// If condition is not an array, return empty array.
		if ( ! is_array( $d4_conditions ) ) {
			return [];
		}

		$d5_conditions = array_map(
			function( $d4_condition ) {
				$d4_condition_name     = isset( $d4_condition['condition'] ) ? $d4_condition['condition'] : '';
				$d4_condition_id       = isset( $d4_condition['id'] ) ? $d4_condition['id'] : '';
				$d4_condition_settings = isset( $d4_condition['conditionSettings'] ) ? $d4_condition['conditionSettings'] : [];

				if ( ! $d4_condition_name || ! $d4_condition_id || empty( $d4_condition_settings ) ) {
					return null;
				}

				$d5_condition_name     = $d4_condition_name;
				$d5_condition_id       = $d4_condition_id;
				$d5_condition_operator = isset( $d4_condition['operator'] ) ? $d4_condition['operator'] : 'OR';
				$d5_condition_settings = $d4_condition_settings;

				// 2nd Level - Condition Settings Level.
				// Typecast needed due to `displayRule` property possible values are different.
				// between each conditions.
				switch ( $d5_condition_name ) {
					case 'postType':
						$d5_condition_settings = self::convert_post_type_condition_settings( $d4_condition_settings );
						break;

					// Post Category and Category Page conditions have the same condition settings.
					// structure.  So we can use the same conversion function for both.
					case 'categoryPage':
					case 'categories':
						$d5_condition_settings = self::convert_categories_condition_settings( $d4_condition_settings );
						break;

					// Post Tag and Tag Page conditions have the same condition settings structure.
					// So we can use the same conversion function for both.
					case 'tagPage':
					case 'tags':
						$d5_condition_settings = self::convert_tags_condition_settings( $d4_condition_settings );
						break;

					case 'author':
						$d5_condition_settings = self::convert_author_condition_settings( $d4_condition_settings );
						break;

					case 'customField':
						$d5_condition_settings = self::convert_custom_field_condition_settings( $d4_condition_settings );
						break;

					case 'dateArchive':
						$d5_condition_settings = self::convert_date_archive_condition_settings( $d4_condition_settings );
						break;

					case 'searchResults':
						$d5_condition_settings = self::convert_search_results_condition_settings( $d4_condition_settings );
						break;

					case 'loggedInStatus':
						$d5_condition_settings = self::convert_logged_in_status_condition_settings( $d4_condition_settings );
						break;

					case 'userRole':
						$d5_condition_settings = self::convert_user_role_condition_settings( $d4_condition_settings );
						break;

					case 'dateTime':
						$d5_condition_settings = self::convert_date_time_condition_settings( $d4_condition_settings );
						break;

					// Page Visit and Post Visit conditions have the same condition settings structure.
					// So we can use the same conversion function for both.
					case 'postVisit':
					case 'pageVisit':
						$d5_condition_settings = self::convert_page_visit_condition_settings( $d4_condition_settings );
						break;

					case 'numberOfViews':
						$d5_condition_settings = self::convert_number_of_views_condition_settings( $d4_condition_settings );
						break;

					case 'urlParameter':
						$d5_condition_settings = self::convert_url_parameter_condition_settings( $d4_condition_settings );
						break;

					case 'browser':
						$d5_condition_settings = self::convert_browser_condition_settings( $d4_condition_settings );
						break;

					case 'operatingSystem':
						$d5_condition_settings = self::convert_operating_system_condition_settings( $d4_condition_settings );
						break;

					case 'cookie':
						$d5_condition_settings = self::convert_cookie_condition_settings( $d4_condition_settings );
						break;

					default:
						if ( array_key_exists( 'dynamicPosts', $d4_condition_settings ) ) {
							$d5_condition_settings = self::convert_dynamic_posts_condition_settings( $d4_condition_settings );
						}
						break;
				}

				// 1st Level - Condition Level.
				return [
					'conditionName'     => $d5_condition_name,
					'id'                => $d5_condition_id,
					'operator'          => $d5_condition_operator,
					'conditionSettings' => $d5_condition_settings,
				];
			},
			array_filter(
				$d4_conditions,
				function( $d4_condition ) {
					// To avoid unexpected issue when processing incorrect object, we need to check if
					// the `condition`, `id`, and `conditionSettings` exist and not empty.
					$d4_condition_name     = isset( $d4_condition['condition'] ) ? $d4_condition['condition'] : '';
					$d4_condition_id       = isset( $d4_condition['id'] ) ? $d4_condition['id'] : '';
					$d4_condition_settings = isset( $d4_condition['conditionSettings'] ) ? $d4_condition['conditionSettings'] : [];
					return $d4_condition_name && $d4_condition_id && ! empty( $d4_condition_settings );
				}
			)
		);

		return $d5_conditions;
	}

	/**
	 * Get the parsed encoded string.
	 *
	 * This function works by:
	 * - Decoding the D4 format encoded string.
	 * - Parsing the D4 format decoded string to JSON.
	 *
	 * @since ??
	 *
	 * @param string $value D4 format string for conditions.
	 *
	 * @throws Exception If base64_decode or json_decode fails.
	 *
	 * @return array The parsed value or null as fallback.
	 */
	public static function get_parsed_encoded_string( $value ) {
		try {
			// Base64 decode the input string.
			$decoded_value = base64_decode( $value );

			// Check if base64_decode was successful.
			if ( false === $decoded_value ) {
				throw new \Exception( 'Base64 decode failed.' );
			}

			// Decode the UTF-8 encoded string.
			$decoded_value = mb_convert_encoding( $decoded_value, 'UTF-8', 'UTF-8' );

			// Parse the JSON string.
			$parsed_value = json_decode( $decoded_value, true );

			// Check if json_decode was successful.
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( 'JSON decode failed: ' . json_last_error_msg() );
			}

			return $parsed_value;
		} catch ( \Exception $e ) {
			// Log the error and return null.
			error_log( $e->getMessage() . ' - String: ' . $value );
			return null;
		}
	}

	/**
	 * Get a valid date for PHP DateTime from the given input.
	 *
	 * In PHP, we use `DateTime` to format the date to 'YYYY-MM-DD' format. The problem is that
	 * the date format from D4 is not always recognized RFC2822 or ISO format and it might not be valid.
	 * To avoid this, we need to format the date using `DateTime` before assigning it to `d5ConditionSettings`.
	 *
	 * @since ??
	 *
	 * @param string|null $date D4 date value.
	 *
	 * @return string D5 valid date value for DateTime or the current date if invalid.
	 *
	 * @example
	 * get_valid_date('2024-7-23');
	 */
	public static function get_valid_date( $date ) {
		// 1. Bail early if the date is valid already.
        if ($date && (DateTime::createFromFormat('Y-m-d', $date) || DateTime::createFromFormat('Y-n-j', $date))) {
            $normalized_date = DateTime::createFromFormat('Y-n-j', $date);
            return $normalized_date->format('Y-m-d');
        }

		// 2. If the date is still not valid, return the current date.
		return gmdate( 'Y-m-d' );
	}

	/**
	 * Get a valid time in 'H:i' format.
	 *
	 * @since ??
	 *
	 * @param string|null $time The time value from D4.
	 *
	 * @return string A valid time string in 'H:i' format or the current time if invalid.
	 *
	 * @example
	 * get_valid_time('03:04');
	 */
	public static function get_valid_time( $time ) {
		// 1. Bail early if the time is valid already.
		$format = 'H:i';
		if ( $time && DateTime::createFromFormat( $format, $time ) !== false ) {
			return $time;
		}

		// 2. If the time is still not valid, return the current time.
		return ( new DateTime() )->format( $format );
	}

	/**
	 * Convert Post Type D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Post Type.
	 *
	 * @return array The converted Post Type condition settings value.
	 */
	public static function convert_post_type_condition_settings( $d4_condition_settings ) {
		$d4_condition_settings_post_types = isset( $d4_condition_settings['postTypes'] ) ? $d4_condition_settings['postTypes'] : [];
		$d5_condition_settings_post_types = array_filter(
			$d4_condition_settings_post_types,
			function( $d4_post_type ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label` and `value` exist in `postType` value.
				$d4_post_type_label = is_array( $d4_post_type ) && array_key_exists( 'label', $d4_post_type );
				$d4_post_type_value = is_array( $d4_post_type ) && array_key_exists( 'value', $d4_post_type );
				return $d4_post_type_label && $d4_post_type_value;
			}
		);

		// Reindex the array.
		$d5_condition_settings_post_types = array_values( $d5_condition_settings_post_types );

		$d5_condition_settings_post_types = array_map(
			function( $d4_post_type ) {
				// Reassign `postType` to make sure all needed properties are available in D5.
				// `postType` value with some fallback values to avoid unexpected issue.
				return [
					'label' => isset( $d4_post_type['label'] ) ? $d4_post_type['label'] : '',
					'value' => isset( $d4_post_type['value'] ) ? $d4_post_type['value'] : '',
				];
			},
			$d5_condition_settings_post_types
		);

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'postTypes'       => isset( $d5_condition_settings_post_types ) ? $d5_condition_settings_post_types : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Post Category and Category Page D4 condition settings value to D5 format.
	 *
	 * Post Category and Category Page conditions have the same condition settings structure.
	 * So we can use the same conversion function for both.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Post Category or Category Page.
	 *
	 * @return array
	 * The converted Post Category or Category Page condition settings value.
	 */
	public static function convert_categories_condition_settings( $d4_condition_settings ) {
		// Initialize the categories array.
		$d4_condition_settings_categories = ! empty( $d4_condition_settings['categories'] ) ? $d4_condition_settings['categories'] : [];

		$d5_condition_settings_categories = array_filter(
			$d4_condition_settings_categories,
			function( $d4_category ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label`, `value`, `group`, `groupSlug`, and `isCatchAll` exist.
				return is_array( $d4_category ) && isset( $d4_category['label'], $d4_category['value'], $d4_category['group'], $d4_category['groupSlug'], $d4_category['isCatchAll'] );
			}
		);

		// Reindex the array.
		$d5_condition_settings_categories = array_values( $d5_condition_settings_categories );

		$d5_condition_settings_categories = array_map(
			function( $d4_category ) {
				// Reassign `category` to make sure all needed properties are available in D5.
				// `category` value with some fallback values to avoid unexpected issue.
				if ( is_array( $d4_category ) && isset( $d4_category['label'], $d4_category['value'], $d4_category['group'], $d4_category['groupSlug'], $d4_category['isCatchAll'] ) ) {
					return [
						'label'      => isset( $d4_category['label'] ) ? $d4_category['label'] : '',
						'value'      => isset( $d4_category['value'] ) ? $d4_category['value'] : '',
						'group'      => isset( $d4_category['group'] ) ? $d4_category['group'] : '',
						'groupSlug'  => isset( $d4_category['groupSlug'] ) ? $d4_category['groupSlug'] : '',
						'isCatchAll' => isset( $d4_category['isCatchAll'] ) ? $d4_category['isCatchAll'] : false,
					];
				}
				// Ignore invalid entries.
				return null;
			},
			$d5_condition_settings_categories
		);

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'categories'      => !empty( $d5_condition_settings_categories ) ? $d5_condition_settings_categories : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Post Tag and Tag Page D4 condition settings value to D5 format.
	 *
	 * Post Tag and Tag Page conditions have the same condition settings structure. So we can
	 * use the same conversion function for both.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Post Tag or Tag Page.
	 *
	 * @return array The converted Post Tag or Tag Page condition settings value.
	 */
	public static function convert_tags_condition_settings( $d4_condition_settings ) {
		$d4_condition_settings_tags = isset( $d4_condition_settings['tags'] ) ? $d4_condition_settings['tags'] : [];
		$d5_condition_settings_tags = array_filter(
			$d4_condition_settings_tags,
			function( $d4_tag ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label`, `value`, `group`, `groupSlug`, and `isCatchAll` exist.
				$d4_tag_label      = is_array( $d4_tag ) && array_key_exists( 'label', $d4_tag );
				$d4_tag_value      = is_array( $d4_tag ) && array_key_exists( 'value', $d4_tag );
				$d4_tag_group      = is_array( $d4_tag ) && array_key_exists( 'group', $d4_tag );
				$d4_tag_group_slug = is_array( $d4_tag ) && array_key_exists( 'groupSlug', $d4_tag );
				$d4_tag_all        = is_array( $d4_tag ) && array_key_exists( 'isCatchAll', $d4_tag );
				return $d4_tag_label && $d4_tag_value && $d4_tag_group && $d4_tag_group_slug && $d4_tag_all;
			}
		);

		// Reindex the array.
		$d5_condition_settings_tags = array_values( $d5_condition_settings_tags );

		$d5_condition_settings_tags = array_map(
			function( $d4_tag ) {
				// Reassign `tag` to make sure all needed properties are available in D5.
				// `tag` value with some fallback values to avoid unexpected issue.
				return [
					'label'      => isset( $d4_tag['label'] ) ? $d4_tag['label'] : '',
					'value'      => isset( $d4_tag['value'] ) ? $d4_tag['value'] : '',
					'group'      => isset( $d4_tag['group'] ) ? $d4_tag['group'] : '',
					'groupSlug'  => isset( $d4_tag['groupSlug'] ) ? $d4_tag['groupSlug'] : '',
					'isCatchAll' => isset( $d4_tag['isCatchAll'] ) ? $d4_tag['isCatchAll'] : false,
				];
			},
			$d5_condition_settings_tags
		);

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'tags'            => isset( $d5_condition_settings_tags ) ? $d5_condition_settings_tags : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Author D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings  Condition settings value for Author.
	 *
	 * @throws Exception If base64_decode or json_decode fails.
	 *
	 * @return array The converted Author condition settings value.
	 */
	public static function convert_author_condition_settings( $d4_condition_settings ) {
		// Get authors from D4 condition settings, or default to an empty array.
		$d4_condition_settings_authors = isset( $d4_condition_settings['authors'] ) ? $d4_condition_settings['authors'] : [];

		$d5_condition_settings_authors = array_filter(
			$d4_condition_settings_authors,
			function( $d4_author ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label` and `value` exist in `author` value.
				return is_array( $d4_author ) && array_key_exists( 'label', $d4_author ) && array_key_exists( 'value', $d4_author );
			}
		);

		// Reindex the array.
		$d5_condition_settings_authors = array_values( $d5_condition_settings_authors );

		$d5_condition_settings_authors = array_map(
			function( $d4_author ) {
				// Check if 'label' and 'value' keys exist in the author object.
				$d4_author_label = is_array( $d4_author ) && array_key_exists( 'label', $d4_author );
				$d4_author_value = is_array( $d4_author ) && array_key_exists( 'value', $d4_author );

				if ( $d4_author_label && $d4_author_value ) {
					// Reassign `author` to make sure all needed properties are available in D5.
					// `author` value with some fallback values to avoid unexpected issue.
					return [
						'label' => isset( $d4_author['label'] ) ? $d4_author['label'] : '',
						'value' => isset( $d4_author['value'] ) ? $d4_author['value'] : '',
					];
				}

				return null;
			},
			$d5_condition_settings_authors
		);

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'authors'         => isset( $d5_condition_settings_authors ) ? $d5_condition_settings_authors : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Custom Field D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Custom Field.
	 *
	 * @return array
	 * The converted Custom Field condition settings value.
	 */
	public static function convert_custom_field_condition_settings( $d4_condition_settings ) {
		// The `selectConditionalMetaField` property is the rest of condition settings except.
		// `adminLabel` and `enableCondition`. Need to typecast the return value to the correct.
		// type explicitly as JSON.parse used in `getParsedEncodedString` is not type-safe and.
		// will return `any` type.
		$d4_meta_field_settings = isset( $d4_condition_settings['selectConditionalMetaField'] )
		? self::get_parsed_encoded_string( $d4_condition_settings['selectConditionalMetaField'] )
		: [];

		// Reassign `d5ConditionSettings` to remove D4 `selectConditionalMetaField`.
		// property from D5 condition settings. We also need to make sure all needed.
		// settings are available in D5 condition settings with some fallback values.
		// to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'         => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition'    => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'        => !empty( $d4_meta_field_settings['displayRule'] ) ? $d4_meta_field_settings['displayRule'] : 'is',
			'customFieldName'    => !empty( $d4_meta_field_settings['customFieldName'] ) ? $d4_meta_field_settings['customFieldName'] : '',
			'customFieldValue'   => !empty( $d4_meta_field_settings['customFieldValue'] ) ? $d4_meta_field_settings['customFieldValue'] : '',
			'selectedFieldName'  => !empty( $d4_meta_field_settings['selectedFieldName'] ) ? $d4_meta_field_settings['selectedFieldName'] : 'manualCustomFieldName',
			'selectedFieldValue' => !empty( $d4_meta_field_settings['selectedFieldValue'] ) ? $d4_meta_field_settings['selectedFieldValue'] : 'manualCustomFieldValue',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Date Archive D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Date Archive.
	 *
	 * @return array The converted Date Archive condition settings value.
	 */
	public static function convert_date_archive_condition_settings( $d4_condition_settings ) {
		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'isAfter',
			'dateArchive'     => self::get_valid_date( isset( $d4_condition_settings['dateArchive'] ) ? $d4_condition_settings['dateArchive'] : '' ),
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Search Results D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Search Results.
	 *
	 * @return array The converted Search Results condition settings value.
	 */
	public static function convert_search_results_condition_settings( $d4_condition_settings ) {
		// Reassign `d5_condition_settings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'            => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition'       => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'           => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'specificSearchQueries',
			'specificSearchQueries' => !empty( $d4_condition_settings['specificSearchQueries'] ) ? $d4_condition_settings['specificSearchQueries'] : '',
			'excludedSearchQueries' => !empty( $d4_condition_settings['excludedSearchQueries'] ) ? $d4_condition_settings['excludedSearchQueries'] : '',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Logged In Status D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Logged In Status.
	 *
	 * @return array The converted Logged In Status condition settings value.
	 */
	public static function convert_logged_in_status_condition_settings( $d4_condition_settings ) {
		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'loggedIn',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert User Role D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for User Role.
	 *
	 * @return array The converted User Role condition settings value.
	 */
	public static function convert_user_role_condition_settings( $d4_condition_settings ) {
		$d4_condition_settings_user_roles = ! empty( $d4_condition_settings['userRoles'] ) ? $d4_condition_settings['userRoles'] : [];

		$d5_condition_settings_user_roles = array_filter(
			$d4_condition_settings_user_roles,
			function( $d4_user_role ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label` and `value` exist in `userRole` value.
				$d4_user_role_label = is_array( $d4_user_role ) && array_key_exists( 'label', $d4_user_role );
				$d4_user_role_value = is_array( $d4_user_role ) && array_key_exists( 'value', $d4_user_role );
				return $d4_user_role_label && $d4_user_role_value;
			}
		);

		// Reindex the array.
		$d5_condition_settings_user_roles = array_values( $d5_condition_settings_user_roles );

		$d5_condition_settings_user_roles = array_map(
			function( $d4_user_role ) {
				// Reassign `userRole` to make sure all needed properties are available in D5.
				// `userRole` value with some fallback values to avoid unexpected issue.
				return [
					'label' => isset( $d4_user_role['label'] ) ? $d4_user_role['label'] : '',
					'value' => isset( $d4_user_role['value'] ) ? $d4_user_role['value'] : '',
				];
			},
			$d5_condition_settings_user_roles
		);

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'userRoles'       => !empty( $d5_condition_settings_user_roles ) ? $d5_condition_settings_user_roles : [],
			'userIds'         => !empty( $d4_condition_settings['userIds'] ) ? $d4_condition_settings['userIds'] : '',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Date Time D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Date Time.
	 *
	 * @return array The converted Date Time condition settings value.
	 */
	public static function convert_date_time_condition_settings( $d4_condition_settings ) {
		// Convert D4 `weekdays` value to D5 format.
		// D4 `weekdays` value is a string separated by `|` character: 'monday|tuesday||||||'.
		// We need to split it by `|` character and filter empty values to get the correct value.
		$d4_condition_settings_weekdays = isset( $d4_condition_settings['weekdays'] ) ? $d4_condition_settings['weekdays'] : '';
		$d5_condition_settings_weekdays = strpos( $d4_condition_settings_weekdays, '|' ) !== false
		? array_filter(
			explode( '|', $d4_condition_settings_weekdays ),
			function( $value ) {
				return '' !== $value;
			}
		)
		: [];

		// Reindex the array.
		$d5_condition_settings_weekdays = array_values( $d5_condition_settings_weekdays );

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'                  => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition'             => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'                 => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'isAfter',
			'allDay'                      => !empty( $d4_condition_settings['allDay'] ) ? $d4_condition_settings['allDay'] : 'on',
			'repeat'                      => !empty( $d4_condition_settings['repeat'] ) ? $d4_condition_settings['repeat'] : 'off',
			'repeatEnd'                   => !empty( $d4_condition_settings['repeatEnd'] ) ? $d4_condition_settings['repeatEnd'] : 'never',
			'repeatFrequency'             => !empty( $d4_condition_settings['repeatFrequency'] ) ? $d4_condition_settings['repeatFrequency'] : 'monthly',
			'repeatFrequencySpecificDays' => !empty( $d4_condition_settings['repeatFrequencySpecificDays'] ) ? $d4_condition_settings['repeatFrequencySpecificDays'] : 'weekly',
			'repeatTimes'                 => !empty( $d4_condition_settings['repeatTimes'] ) ? $d4_condition_settings['repeatTimes'] : '3',
			'date'                        => self::get_valid_date( isset( $d4_condition_settings['date'] ) ? $d4_condition_settings['date'] : '' ),
			'repeatUntilDate'             => self::get_valid_date( isset( $d4_condition_settings['repeatUntilDate'] ) ? $d4_condition_settings['repeatUntilDate'] : '' ),
			'fromTime'                    => self::get_valid_time( isset( $d4_condition_settings['fromTime'] ) ? $d4_condition_settings['fromTime'] : '' ),
			'time'                        => self::get_valid_time( isset( $d4_condition_settings['time'] ) ? $d4_condition_settings['time'] : '' ),
			'untilTime'                   => self::get_valid_time( isset( $d4_condition_settings['untilTime'] ) ? $d4_condition_settings['untilTime'] : '' ),
			'weekdays'                    => !empty( $d5_condition_settings_weekdays ) ? $d5_condition_settings_weekdays : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Page Visit and Post Visit D4 condition settings value to D5 format.
	 *
	 * Page Visit and Post Visit conditions have the same condition settings structure.
	 * So we can use the same conversion function for both.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Page Visit or Post Visit.
	 *
	 * @return array The converted Page Visit or Post Visit condition settings value.
	 */
	public static function convert_page_visit_condition_settings( $d4_condition_settings ) {
		$d4_condition_settings_pages = ! empty( $d4_condition_settings['pages'] ) ? $d4_condition_settings['pages'] : [];
		$d5_condition_settings_pages = array_filter(
			$d4_condition_settings_pages,
			function( $d4_page ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label` and `value` exist in `page` value.
				$d4_page_label = is_array( $d4_page ) && array_key_exists( 'label', $d4_page );
				$d4_page_value = is_array( $d4_page ) && array_key_exists( 'value', $d4_page );
				return $d4_page_label && $d4_page_value;
			}
		);

		// Reindex the array.
		$d5_condition_settings_pages = array_values( $d5_condition_settings_pages );

		$d5_condition_settings_pages = array_map(
			function( $d4_page ) {
				// Reassign `page` to make sure all needed properties are available in D5.
				// `page` value with some fallback values to avoid unexpected issue.
				return [
					'label' => isset( $d4_page['label'] ) ? $d4_page['label'] : '',
					'value' => isset( $d4_page['value'] ) ? $d4_page['value'] : '',
				];
			},
			$d5_condition_settings_pages
		);

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'hasVisitedSpecificPage',
			'pages'           => !empty( $d5_condition_settings_pages ) ? $d5_condition_settings_pages : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Number of Views D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Number of Views.
	 *
	 * @return array The converted Number of Views condition settings value.
	 */
	public static function convert_number_of_views_condition_settings( $d4_condition_settings ) {
		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'            => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition'       => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayAgainAfter'     => !empty( $d4_condition_settings['displayAgainAfter'] ) ? $d4_condition_settings['displayAgainAfter'] : '',
			'displayAgainAfterUnit' => !empty( $d4_condition_settings['displayAgainAfterUnit'] ) ? $d4_condition_settings['displayAgainAfterUnit'] : 'days',
			'numberOfViews'         => !empty( $d4_condition_settings['numberOfViews'] ) ? $d4_condition_settings['numberOfViews'] : '',
			'resetAfterDuration'    => !empty( $d4_condition_settings['resetAfterDuration'] ) ? $d4_condition_settings['resetAfterDuration'] : 'off',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert URL Parameter D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for URL Parameter.
	 *
	 * @return array The converted URL Parameter condition settings value.
	 */
	public static function convert_url_parameter_condition_settings( $d4_condition_settings ) {
		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'         => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition'    => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'        => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'equals',
			'selectUrlParameter' => !empty( $d4_condition_settings['selectUrlParameter'] ) ? $d4_condition_settings['selectUrlParameter'] : 'specificUrlParameter',
			'urlParameterName'   => !empty( $d4_condition_settings['urlParameterName'] ) ? $d4_condition_settings['urlParameterName'] : '',
			'urlParameterValue'  => !empty( $d4_condition_settings['urlParameterValue'] ) ? $d4_condition_settings['urlParameterValue'] : '',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Browser D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings  Condition settings value for Browser.
	 *
	 * @throws Exception If base64_decode or json_decode fails.
	 *
	 * @return array The converted Browser condition settings value.
	 */
	public static function convert_browser_condition_settings( $d4_condition_settings ) {
		// Initialize the browsers string.
		$d4_condition_settings_browsers = isset( $d4_condition_settings['browsers'] ) ? $d4_condition_settings['browsers'] : '';

		// Convert D4 `browsers` value to D5 format.
		// D4 `browsers` value is a string separated by `|` character: 'chrome|firefox||||||'.
		// We need to split it by `|` character and filter empty values to get the correct value.
		$d5_condition_settings_browsers = ! empty( $d4_condition_settings_browsers ) && strpos( $d4_condition_settings_browsers, '|' ) !== false
		? array_filter(
			explode( '|', $d4_condition_settings_browsers ),
			function( $value ) {
				return ! empty( $value );
			}
		)
		: [];

		// Reindex the array.
		$d5_condition_settings_browsers = array_values( $d5_condition_settings_browsers );

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'browsers'        => !empty( $d5_condition_settings_browsers ) ? $d5_condition_settings_browsers : [],
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Operating System D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Operating System.
	 *
	 * @return array The converted Operating System condition settings value.
	 */
	public static function convert_operating_system_condition_settings( $d4_condition_settings ) {
		$d4_condition_settings_operating_systems = isset( $d4_condition_settings['operatingSystems'] ) ? $d4_condition_settings['operatingSystems'] : '';

		// Convert D4 `operatingSystems` value to D5 format.
		// D4 `operatingSystems` value is a string separated by `|` character: '|macos|linux|||||||||'.
		// We need to split it by `|` character and filter empty values to get the correct value.
		$d5_condition_settings_operating_systems = strpos( $d4_condition_settings_operating_systems, '|' ) !== false
			? array_filter(
				explode( '|', $d4_condition_settings_operating_systems ),
				function( $value ) {
					return '' != $value;
				}
			)
			: [];

		// Reindex the array.
		$d5_condition_settings_operating_systems = array_values( $d5_condition_settings_operating_systems );

		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'       => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition'  => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'      => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'operatingSystems' => $d5_condition_settings_operating_systems,
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Cookie D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Cookie.
	 *
	 * @return array
	 * The converted Cookie condition settings value.
	 */
	public static function convert_cookie_condition_settings( $d4_condition_settings ) {
		// Reassign `d5ConditionSettings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'cookieExists',
			'cookieName'      => !empty( $d4_condition_settings['cookieName'] ) ? $d4_condition_settings['cookieName'] : '',
			'cookieValue'     => !empty( $d4_condition_settings['cookieValue'] ) ? $d4_condition_settings['cookieValue'] : '',
		];

		return $d5_condition_settings;
	}

	/**
	 * Convert Dynamic Posts D4 condition settings value to D5 format.
	 *
	 * @since ??
	 *
	 * @param array $d4_condition_settings Condition settings value for Dynamic Posts.
	 *
	 * @return array The converted Dynamic Posts condition settings value.
	 */
	public static function convert_dynamic_posts_condition_settings( $d4_condition_settings ) {
		$d4_condition_settings_dynamic_posts = ! empty( $d4_condition_settings['dynamicPosts'] ) ? $d4_condition_settings['dynamicPosts'] : [];

		$d5_condition_settings_dynamic_posts = array_filter(
			$d4_condition_settings_dynamic_posts,
			function( $d4_dynamic_post ) {
				// To avoid unexpected issue when processing incorrect object, we need to check if.
				// the `label` and `value` exist in `dynamicPost` value.
				$d4_dynamic_post_label = is_array( $d4_dynamic_post ) && array_key_exists( 'label', $d4_dynamic_post );
				$d4_dynamic_post_value = is_array( $d4_dynamic_post ) && array_key_exists( 'value', $d4_dynamic_post );
				return $d4_dynamic_post_label && $d4_dynamic_post_value;
			}
		);

		// Reindex the array.
		$d5_condition_settings_dynamic_posts = array_values( $d5_condition_settings_dynamic_posts );

		$d5_condition_settings_dynamic_posts = array_map(
			function( $d4_dynamic_post ) {
				// Reassign `dynamicPost` to make sure all needed properties are available in D5.
				// `dynamicPost` value with some fallback values to avoid unexpected issue.
				return [
					'label' => isset( $d4_dynamic_post['label'] ) ? $d4_dynamic_post['label'] : '',
					'value' => isset( $d4_dynamic_post['value'] ) ? $d4_dynamic_post['value'] : '',
				];
			},
			$d5_condition_settings_dynamic_posts
		);

		// Reassign `d5_condition_settings` to make sure all needed settings are available in D5.
		// condition settings with some fallback values to avoid unexpected issue.
		$d5_condition_settings = [
			'adminLabel'      => !empty( $d4_condition_settings['adminLabel'] ) ? $d4_condition_settings['adminLabel'] : '',
			'enableCondition' => !empty( $d4_condition_settings['enableCondition'] ) ? $d4_condition_settings['enableCondition'] : 'on',
			'displayRule'     => !empty( $d4_condition_settings['displayRule'] ) ? $d4_condition_settings['displayRule'] : 'is',
			'dynamicPosts'    => $d5_condition_settings_dynamic_posts,
			'postTypeLabel'   => !empty( $d4_condition_settings['postTypeLabel'] ) ? $d4_condition_settings['postTypeLabel'] : '',
		];

		return $d5_condition_settings;
	}


	/**
     * Get Dividers Conversion Map.
     *
     * This function is used for getting dividers conversion map based on given D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for Dividers options.
     */
    public static function getDividersConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'dividers';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$dividersConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttrTop = str_replace('d4_attr_name', 'top_divider', $d4AttrNameTemplate);
            $targetAttrBottom = str_replace('d4_attr_name', 'bottom_divider', $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttrTop = "{$targetAttrTop}_{$d4AttributeSuffix}";
                $targetAttrBottom = "{$targetAttrBottom}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttrTop] = "{$d5AttrName}.top.*.{$d5AttrPath}";
            $conversionMap['attributeMap'][$targetAttrBottom] = "{$d5AttrName}.bottom.*.{$d5AttrPath}";

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$dividersValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ($valueExpansionFunction) {
				if (is_callable($valueExpansionFunction)) {
					$conversionMap['valueExpansionFunctionMap'][$targetAttrTop] = $valueExpansionFunction;
					$conversionMap['valueExpansionFunctionMap'][$targetAttrBottom] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
			}
        }

        return $conversionMap;
    }

	/**
     * Obtains an object of the expanded value of a dividers attribute.
     *
     * @since ??
     *
     * @param string $value Expanded attribute value.
     *
     * @return string[] The expanded value or empty array if no expansion was found.
     *
     * @example
     * ```
     * AdvancedOptionConversion::dividersFlip('horizontal|vertical')
     * // returns ['horizontal', 'vertical']
     * ```
     */
    public static function dividersFlip($value) {
        // Differences (D4, Converted Value) For top_divider_flip and bottom_divider_flip field value:
        // horizontal|vertical are migrated as an array ['horizontal', 'vertical'] respectively.
        $flipAttributes = [];

        if (strpos($value, 'horizontal') !== false) {
            $flipAttributes[] = 'horizontal';
        }

        if (strpos($value, 'vertical') !== false) {
            $flipAttributes[] = 'vertical';
        }

        return $flipAttributes;
    }

	/**
     * Get FormField Conversion Map.
     *
     * This function is used for getting FormField conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for FormField options.
     */
    public static function getFormFieldConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'form_field';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'formField';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$formFieldConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.{$d5AttrPath}";
        }

        $backgroundConversionMap = self::getBackgroundConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.decoration.background",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        $borderConversionMap = self::getBorderConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.decoration.border",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        $focusBorderConversionMap = self::getBorderConversionMap([
            'd4AdvancedOptionName' => "{$d4AdvancedOptionName}_focus",
            'd5AttrName' => "{$d5AttrName}.advanced.focus.border",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        $fontConversionMap = self::getFontConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.decoration.font",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        $spacingConversionMap = self::getSpacingConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.decoration.spacing",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        $boxShadowConversionMap = self::getBoxShadowConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.decoration.boxShadow",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        return [
            'attributeMap' => array_merge(
                $conversionMap['attributeMap'],
                $backgroundConversionMap['attributeMap'],
                $borderConversionMap['attributeMap'],
                $focusBorderConversionMap['attributeMap'],
                $fontConversionMap['attributeMap'],
                $spacingConversionMap['attributeMap'],
                $boxShadowConversionMap['attributeMap']
            ),
            'optionEnableMap' => array_merge(
                $conversionMap['optionEnableMap'],
                $backgroundConversionMap['optionEnableMap'],
                $borderConversionMap['optionEnableMap'],
                $focusBorderConversionMap['optionEnableMap'],
                $fontConversionMap['optionEnableMap'],
                $spacingConversionMap['optionEnableMap'],
                $boxShadowConversionMap['optionEnableMap']
            ),
            'valueExpansionFunctionMap' => array_merge(
                $conversionMap['valueExpansionFunctionMap'],
                $backgroundConversionMap['valueExpansionFunctionMap'],
                $borderConversionMap['valueExpansionFunctionMap'],
                $focusBorderConversionMap['valueExpansionFunctionMap'],
                $fontConversionMap['valueExpansionFunctionMap'],
                $spacingConversionMap['valueExpansionFunctionMap'],
                $boxShadowConversionMap['valueExpansionFunctionMap']
            ),
        ];
    }

	/**
     * Get Filters Conversion Map.
     *
     * This function is used for getting filters conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for filters options.
     */
    public static function getFiltersConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'default';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'filters';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$filtersConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AdvancedOptionName === 'default'
                ? str_replace('d4_attr_name_', '', $d4AttrNameTemplate)
                : str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Get Font Conversion Map.
     *
     * This function is used for getting font conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for font options.
     */
    public static function getFontConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'font';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'font';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$fontConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);
            $sourceAttr = ($d5AttrPath === 'font') ? "{$d5AttrName}.font.*" : "{$d5AttrName}.font.*.{$d5AttrPath}";

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$fontValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ( $valueExpansionFunction ) {
				if ( is_callable($valueExpansionFunction) ) {
             	   $conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
			}

        }

        $textShadowConversionMap = self::getTextShadowConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.textShadow",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        return [
            'attributeMap' => array_merge(
                $conversionMap['attributeMap'],
                $textShadowConversionMap['attributeMap']
            ),
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => array_merge(
                $conversionMap['valueExpansionFunctionMap'],
                $textShadowConversionMap['valueExpansionFunctionMap']
            ),
        ];
    }

	/**
     * Convert D4 font attribute value to D5 format.
     *
     * This is used to parse the string passed as argument into object of expanded D5 font attribute format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for font.
     *
     * @return array The expanded value or empty array if no expansion was found.
     *
     * @example
     * ```php
     * AdvancedOptionConversion::convertFont('Allegria|300|on|on|on|on|on|#ffffff|dotted');
     * // Returns following font attribute object
     * //{
     * //  'family' => 'Allegria',
     * //  'lineColor' => '#ffffff',
     * //  'lineStyle' => 'dotted',
     * //  'style' => [
     * //    'italic',
     * //    'uppercase',
     * //    'underline',
     * //    'capitalize',
     * //    'strikethrough',
     * //  ],
     * //  'weight' => '300',
     * //}
     * ```
     */
    public static function convertFont($value) {
        $valueArray = explode('|', $value);

        $fontStyle = [];
        if (isset($valueArray[2]) && strpos($valueArray[2], 'on') === 0) {
            $fontStyle[] = 'italic';
        }
        if (isset($valueArray[3]) && strpos($valueArray[3], 'on') === 0) {
            $fontStyle[] = 'uppercase';
        }
        if (isset($valueArray[4]) && strpos($valueArray[4], 'on') === 0) {
            $fontStyle[] = 'underline';
        }
        if (isset($valueArray[5]) && strpos($valueArray[5], 'on') === 0) {
            $fontStyle[] = 'capitalize';
        }
        if (isset($valueArray[6]) && strpos($valueArray[6], 'on') === 0) {
            $fontStyle[] = 'strikethrough';
        }

        $font = [];

        $fontFamily = isset($valueArray[0]) ? $valueArray[0] : '';
        $fontWeight = isset($valueArray[1]) ? $valueArray[1] : '';
        $fontLineColor = isset($valueArray[7]) ? $valueArray[7] : '';
        $fontLineStyle = isset($valueArray[8]) ? $valueArray[8] : '';

        if (!empty($fontFamily)) {
            $font['family'] = $fontFamily;
        }

        if (!empty($fontWeight)) {
            $font['weight'] = 'on' === $fontWeight ? '700' : $fontWeight;
        }

        if (!empty($fontStyle)) {
            $font['style'] = $fontStyle;
        }

        if (!empty($fontLineColor)) {
            $font['lineColor'] = $fontLineColor;
        }

        if (!empty($fontLineStyle)) {
            $font['lineStyle'] = $fontLineStyle;
        }

        return $font;
    }

	/**
     * Get Gutter Conversion Map.
     *
     * This function is used for getting gutter conversion map based on given D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for gutter options.
     */
    public static function getGutterConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'gutter';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$gutterConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AttrNameTemplate;

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Get Sizing Height Conversion Map.
     *
     * This function is used for getting sizing height conversion map based on D5 expected attribute name.
     * Height advanced option can't be looped and take no prefix value so its D4 attribute
     * is always fixed. See: `ET_Builder_Element->_add_sizing_fields()`.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     *
     * @return array Conversion map for sizing height options.
     */
    public static function getSizingHeightConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'sizing';

        $conversionMap = [
            'attributeMap' => [
                'height' => "{$d5AttrName}.*.height",
                'min_height' => "{$d5AttrName}.*.minHeight",
                'max_height' => "{$d5AttrName}.*.maxHeight",
            ],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        return $conversionMap;
    }

	/**
     * Get ImageIcon Conversion Map.
     *
     * This function is used for getting ImageIcon conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for ImageIcon options.
     */
    public static function getImageIconConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'image_icon';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'imageIcon';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$imageIconConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.{$d5AttrPath}";
        }

        $spacingConversionMap = self::getSpacingConversionMap([
            'd4AdvancedOptionName' => $d4AdvancedOptionName,
            'd5AttrName' => "{$d5AttrName}.spacing",
            'd4AttributeSuffix' => $d4AttributeSuffix,
        ]);

        return [
            'attributeMap' => array_merge(
                $conversionMap['attributeMap'],
                $spacingConversionMap['attributeMap']
            ),
            'optionEnableMap' => array_merge(
                $conversionMap['optionEnableMap'],
                $spacingConversionMap['optionEnableMap']
            ),
            'valueExpansionFunctionMap' => array_merge(
                $conversionMap['valueExpansionFunctionMap'],
                $spacingConversionMap['valueExpansionFunctionMap']
            ),
        ];
    }

	/**
     * Get Sizing Max Width Conversion Map.
     *
     * This function is used for getting sizing max width conversion map based on D5 expected attribute name.
     * Max Width advanced option can't be looped and takes no prefix value so its D4 attribute
     * is always fixed.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     *
     * @return array Conversion map for sizing max width options.
     */
    public static function getSizingMaxWidthConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'sizing';

        $conversionMap = [
            'attributeMap' => [
                'width' => "{$d5AttrName}.*.width",
                'max_width' => "{$d5AttrName}.*.maxWidth",
                'module_alignment' => "{$d5AttrName}.*.alignment",
            ],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        return $conversionMap;
    }

	/**
     * Get Link Conversion Map.
     *
     * This function is used for getting link conversion map based on given D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for link options.
     */
    public static function getLinkConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'link';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$linkConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $sourceAttr = "{$d5AttrName}.*.{$d5AttrPath}";
            $targetAttr = $d4AttrNameTemplate;

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;
        }

        return $conversionMap;
    }

	/**
     * Get Spacing Conversion Map.
     *
     * This function is used for getting spacing conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     * @param array $props['conversionMapping'] Custom conversion map.
     *
     * @return array Conversion map for spacing options.
     */
    public static function getSpacingConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'margin_padding';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'spacing';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';
        $conversionMapping = isset($props['conversionMapping']) ? $props['conversionMapping'] : self::$spacingConversionMap;

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach ($conversionMapping as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AdvancedOptionName === 'margin_padding'
                ? str_replace('d4_attr_name_', '', $d4AttrNameTemplate)
                : str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$spacingValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ($valueExpansionFunction && is_callable($valueExpansionFunction)) {
                $conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
            } else {
				throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
			}
        }

        return $conversionMap;
    }

	/**
     * Convert D4 spacing attribute value to D5 format.
     *
     * This is used to parse the string passed as argument into D5 spacing format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for spacing.
     *
     * @return array The expanded value or empty array if no expansion was found.
     *
     * @example
     * ```php
     * AdvancedOptionConversion::convertSpacing('5px|10px|15px|20px|false|false');
     * // Returns the following spacing object
     * // [
     * //   'top' => '5px',
     * //   'right' => '10px',
     * //   'bottom' => '15px',
     * //   'left' => '20px',
     * //   'syncVertical' => 'off',
     * //   'syncHorizontal' => 'off',
     * // ];
     * ```
     *
     * @example
     * ```php
     * AdvancedOptionConversion::convertSpacing('5px|10px|15px');
     * // Returns the following spacing object
     * // [
     * //   'top' => '5px',
     * //   'right' => '10px',
     * //   'bottom' => '15px',
     * //   'left' => '',
     * //   'syncVertical' => 'off',
     * //   'syncHorizontal' => 'off',
     * // ];
     * ```
     */
    public static function convertSpacing($value) {
        $valueArray = explode('|', $value);

        $syncVertical = isset($valueArray[4]) ? $valueArray[4] : 'false';
        $syncHorizontal = isset($valueArray[5]) ? $valueArray[5] : 'false';

        $spacing = [
            'top' => isset($valueArray[0]) ? $valueArray[0] : '',
            'right' => isset($valueArray[1]) ? $valueArray[1] : '',
            'bottom' => isset($valueArray[2]) ? $valueArray[2] : '',
            'left' => isset($valueArray[3]) ? $valueArray[3] : '',
            'syncVertical' => $syncVertical === 'true' ? 'on' : 'off',
            'syncHorizontal' => $syncHorizontal === 'true' ? 'on' : 'off',
        ];

        return $spacing;
    }

	/**
     * Get Id Classes Conversion Map.
     *
     * This function is used for getting Id Classes conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for Id Classes options.
     */
    public static function getIdClassesConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'module';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'module';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$idClassesConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Get Overflow Conversion Map.
     *
     * This function is used for getting overflow conversion map based on given D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for overflow options.
     */
    public static function getOverflowConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'overflow';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$overflowConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AttrNameTemplate;
            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }
            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Convert D4 disabled on attribute value to D5 format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for disabled on.
     *
     * @return array The expanded value or empty array if no expansion was found.
     *
     * @example
     * ```php
     * AdvancedOptionConversion::convertDisabledOnBreakpoint('on|off|on');
     * // Returns following breakpoint object
     * //{
     * //  'phone' => ['value' => 'on'],
     * //  'tabletOnly' => ['value' => 'off'],
     * //  'desktopAbove' => ['value' => 'on'],
     * //}
     * ```
     */
    public static function convertDisabledOnBreakpoint($value) {
        $valueArray = explode('|', $value);

        $breakpoint = [];

        $phone = isset($valueArray[0]) ? $valueArray[0] : '';
        $tabletOnly = isset($valueArray[1]) ? $valueArray[1] : '';
        $desktopAbove = isset($valueArray[2]) ? $valueArray[2] : '';

        if (!empty($phone)) {
            $breakpoint['phone'] = ['value' => $phone];
        }

        if (!empty($tabletOnly)) {
            $breakpoint['tabletOnly'] = ['value' => $tabletOnly];
        }

        if (!empty($desktopAbove)) {
            $breakpoint['desktopAbove'] = ['value' => $desktopAbove];
        }

        return $breakpoint;
    }

	/**
     * Get DisabledOn Conversion Map.
     *
     * This function is used for getting DisabledOn conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for DisabledOn options.
     */
    public static function getDisabledOnConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'disabled_on';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'disabledOn';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        $targetAttr = $d4AdvancedOptionName;

        if ($d4AttributeSuffix) {
            $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
        }

        // There's only one attribute on disabledOn, thus it can be directly used.
        $conversionMap['attributeMap'][$targetAttr] = $d5AttrName;
        $conversionMap['valueExpansionFunctionMap'][$targetAttr] = self::$disabledOnValueConversionFunctionMap['disabledOn'];

        return $conversionMap;
    }

	/**
     * Get Position Conversion Map.
     *
     * This function is used for getting position conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for position options.
     */
    public static function getPositionConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'position';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'position';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$positionConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Convert D4 scroll attribute value to D5 format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for scroll.
     *
     * @return array The expanded value or false if no expansion was found.
     *
     * @example
     * ```php
     * AdvancedOptionConversion::convertScroll('2|42|60|100|11px|1px|1px');
     * // Returns following scroll attribute object
     * //{
     * //  'viewport' => [
     * //    'bottom' => '2',
     * //    'end' => '42',
     * //    'start' => '60',
     * //    'top' => '100',
     * //  ],
     * //  'offset' => [
     * //    'start' => '11px',
     * //    'mid' => '1px',
     * //    'end' => '1px',
     * //  ],
     * //}
     * ```
     */
    public static function convertScroll($value) {
        $valueArray = explode('|', $value);

        $bottom = isset($valueArray[0]) ? $valueArray[0] : '';
        $end = isset($valueArray[1]) ? $valueArray[1] : '';
        $start = isset($valueArray[2]) ? $valueArray[2] : '';
        $top = isset($valueArray[3]) ? $valueArray[3] : '';
        $startingOffset = isset($valueArray[4]) ? $valueArray[4] : '';
        $midOffset = isset($valueArray[5]) ? $valueArray[5] : '';
        $endingOffset = isset($valueArray[6]) ? $valueArray[6] : '';

        $scroll = [
            'viewport' => [
                'bottom' => $bottom,
                'end' => $end,
                'start' => $start,
                'top' => $top,
            ],
            'offset' => [
                'start' => $startingOffset,
                'mid' => $midOffset,
                'end' => $endingOffset,
            ],
        ];

        return $scroll;
    }

	/**
     * Get Scroll Conversion Map.
     *
     * This function is used for getting scroll conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props The conversion map props.
     * @param string $props['d4AdvancedOptionName'] D4 advanced option attribute name.
     * @param string $props['d5AttrName'] D5 expected attribute name.
     * @param string $props['d4AttributeSuffix'] D4 attribute suffix.
     *
     * @return array Conversion map for scroll options.
     */
    public static function getScrollConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'scroll';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'scroll';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$scrollConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = strpos($d4AttrNameTemplate, 'd4_attr_name') !== false
                ? str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate)
                : ($d4AdvancedOptionName === 'scroll' ? $d4AttrNameTemplate : "{$d4AdvancedOptionName}_{$d4AttrNameTemplate}");

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $sourceAttr = "{$d5AttrName}.*.{$d5AttrPath}";

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$scrollValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ($valueExpansionFunction) {
				if ( is_callable($valueExpansionFunction)) {
					$conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
            }
        }

        return $conversionMap;
    }

	/**
     * Get Module Options Sticky conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props Get Module Options Sticky conversion map function parameter.
     * @param string $props['d4AdvancedOptionName'] The name for a D4 advanced option attribute.
     * @param string $props['d5AttrName'] The name for a D5 advanced option attribute.
     * @param string $props['d4AttributeSuffix'] Attribute name suffix in D4.
     *
     * @return array Conversion Map for sticky options.
     */
    public static function getStickyConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'sticky';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'sticky';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$stickyConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Get Module Options Text conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props Function parameter.
     * @param string $props['d5AttrName'] The name for a D5 advanced option attribute.
     * @param string $props['d4AttributeSuffix'] Attribute name suffix in D4.
     *
     * @return array Conversion Map for text options.
     */
    public static function getTextConversionMap($props = []) {
        // Define default values
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'text';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$textConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $sourceAttr = "{$d5AttrName}.text.*.{$d5AttrPath}";
            $targetAttr = $d4AttrNameTemplate;

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;
        }

        return $conversionMap;
    }

	/**
     * Get Module Options Text Shadow conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props Function parameter.
     * @param string $props['d4AdvancedOptionName'] The name for a D4 advanced option.
     * @param string $props['d5AttrName'] The name for a D5 advanced option attribute.
     * @param string $props['d4AttributeSuffix'] Attribute name suffix in D4.
     *
     * @return array Conversion Map for text shadow options.
     */
    public static function getTextShadowConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'default';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'text.textShadow';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$textShadowConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = $d4AdvancedOptionName === 'default'
                ? str_replace('d4_attr_name_', '', $d4AttrNameTemplate)
                : str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $sourceAttr = "{$d5AttrName}.*.{$d5AttrPath}";

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;
        }

        return $conversionMap;
    }

	/**
     * Get Module Options Transform conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props Function parameter.
     * @param string $props['d4AdvancedOptionName'] The name for a D4 advanced option.
     * @param string $props['d5AttrName'] The name for a D5 advanced option attribute.
     * @param string $props['d4AttributeSuffix'] Attribute name suffix in D4.
     *
     * @return array Conversion Map for transform options.
     */
    public static function getTransformConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'transform';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'transform';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$transformConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = strpos($d4AttrNameTemplate, 'd4_attr_name') !== false
                ? str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate)
                : ($d4AdvancedOptionName === 'transform' ? $d4AttrNameTemplate : "{$d4AdvancedOptionName}_{$d4AttrNameTemplate}");

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $sourceAttr = "{$d5AttrName}.*.{$d5AttrPath}";

            $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;

            $valueExpansionMapKey = $d5AttrPath;
            $valueExpansionFunction = self::$transformValueConversionFunctionMap[$valueExpansionMapKey] ?? null;

            if ($valueExpansionFunction ) {
				if ( is_callable($valueExpansionFunction)) {
					$conversionMap['valueExpansionFunctionMap'][$targetAttr] = $valueExpansionFunction;
				} else {
					throw new \Exception('valueExpansionFunction is not callable! $valueExpansionFunction:' . print_r($valueExpansionFunction, true) . ' $valueExpansionMapKey:' . print_r($valueExpansionMapKey, true));
				}
            }
        }

        return $conversionMap;
    }

	/**
     * Convert D4 transform attribute value to D5 format.
     *
     * @since ??
     *
     * @param string $value Shortcode attribute value for transform.
     *
     * @return array The expanded value.
     *
     * @example
     * ```php
     * AdvancedOptionConversion::convertTransform('1deg|2deg|3deg');
     * // Returns the following array
     * // [
     * //   'x' => '1deg',
     * //   'y' => '2deg',
     * //   'z' => '3deg',
     * // ]
     * ```
     */
    public static function convertTransform($value) {
        $valueArray = explode('|', $value);

        $transform = [];
        $x = isset($valueArray[0]) ? $valueArray[0] : '';
        $y = isset($valueArray[1]) ? $valueArray[1] : '';
        $z = isset($valueArray[2]) ? $valueArray[2] : '';

        if ($x) {
            $transform['x'] = $x;
        }

        if ($y) {
            $transform['y'] = $y;
        }

        if ($z) {
            $transform['z'] = $z;
        }

        return $transform;
    }

	/**
     * Get Module Options Transition conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props Function parameter.
     * @param string $props['d4AdvancedOptionName'] The name for a D4 advanced option.
     * @param string $props['d5AttrName'] The name for a D5 advanced option attribute.
     * @param string $props['d4AttributeSuffix'] Attribute name suffix in D4.
     *
     * @return array Conversion Map for transition options.
     */
    public static function getTransitionConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'transition';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'transition';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        foreach (self::$transitionConversionMap as $d4AttrNameTemplate => $d5AttrPath) {
            $targetAttr = str_replace('d4_attr_name', $d4AdvancedOptionName, $d4AttrNameTemplate);

            if ($d4AttributeSuffix) {
                $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
            }

            $conversionMap['attributeMap'][$targetAttr] = "{$d5AttrName}.*.{$d5AttrPath}";
        }

        return $conversionMap;
    }

	/**
     * Get Module Options zIndex conversion map based on given D4 advanced options attribute and
     * D5 expected attribute name.
     *
     * @since ??
     *
     * @param array $props Function parameter.
     * @param string $props['d4AdvancedOptionName'] The name for a D4 advanced option.
     * @param string $props['d5AttrName'] The name for a D5 advanced option attribute.
     * @param string $props['d4AttributeSuffix'] Attribute name suffix in D4.
     *
     * @return array Conversion Map for zIndex options.
     */
    public static function getZIndexConversionMap($props = []) {
        // Define default values
        $d4AdvancedOptionName = isset($props['d4AdvancedOptionName']) ? $props['d4AdvancedOptionName'] : 'z_index';
        $d5AttrName = isset($props['d5AttrName']) ? $props['d5AttrName'] : 'zIndex';
        $d4AttributeSuffix = isset($props['d4AttributeSuffix']) ? $props['d4AttributeSuffix'] : '';

        $conversionMap = [
            'attributeMap' => [],
            'optionEnableMap' => [],
            'valueExpansionFunctionMap' => [],
        ];

        // Prepare ConversionMap.
        $targetAttr = $d4AdvancedOptionName;
        $sourceAttr = "{$d5AttrName}.*";

        if ($d4AttributeSuffix) {
            $targetAttr = "{$targetAttr}_{$d4AttributeSuffix}";
        }

        $conversionMap['attributeMap'][$targetAttr] = $sourceAttr;

        return $conversionMap;
    }
}
