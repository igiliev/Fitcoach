<?php

//////////////////////////////////////////////////////////////
//===========================================================
// PAGELAYER
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit Gupta
// Date:	   23rd Jan 2017
// Time:	   23:00 hrs
// Site:	   http://pagelayer.com/wordpress (PAGELAYER)
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

add_action( 'wp', 'pagelayer_pro_wc_customization' );
function pagelayer_pro_wc_customization(){
	
	$options = pagelayer_get_customize_options();
	
	if(!is_product()){
		return;
	}
			
	if(!empty($options['woo_enable_product_zoom'] ) && $options['woo_enable_product_zoom'] == 'disable'){
		remove_theme_support( 'wc-product-gallery-zoom' );
	}
	
	if(!empty($options['woo_enable_product_zoom'] ) && $options['woo_enable_product_zoom'] == 'enable'){
		add_theme_support( 'wc-product-gallery-zoom' );
	}
	
	// Disable Product description
	if(!empty($options['woo_disable_product_desc'])){
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	}
	
	// Disable up sell products
	if(!empty($options['woo_disable_upsells'])){
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	}
	
	// Disable Related Products
	if(!empty($options['woo_disable_related_product'])){
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}

}

// WooCommerce Checkout Fields Hook
add_filter('woocommerce_checkout_fields','pagelayer_wc_checkout_fields_no_label',10);
function pagelayer_wc_checkout_fields_no_label($fields) {
	
	$options = pagelayer_get_customize_options();
	
	if(empty($options['woo_checkout_label_placeholder'])){
		return $fields;
	}
		
	// loop by category
	foreach ($fields as $category => $value) {
		// loop by fields
		foreach ($fields[$category] as $field => $property) {
			
			//Add label as placeholder
			if( $fields[$category][$field]['required'] == true ){
				//Add required * in placeholder
				$fields[$category][$field]['placeholder'] = $fields[$category][$field]['label'] .' *';
			}else{
				//Add (optional) in placeholder
				$fields[$category][$field]['placeholder'] = $fields[$category][$field]['label'] .'(optional)';
			}
			
			// remove label property
			unset($fields[$category][$field]['label']);
		}
	}
	
	return $fields;
}

// Disable sale flash
add_filter( 'woocommerce_sale_flash', 'pagelayer_woo_sale_flash', 10, 3 );
function pagelayer_woo_sale_flash( $html, $post, $product  ) {
	
	$options = pagelayer_get_customize_options();
	
	if(empty($options['woo_disable_onsale'])){
		return $html;
	}
	
	return '';
}

// Change number of related product on single page
add_filter( 'woocommerce_output_related_products_args', 'pagelayer_single_product_number_related_products', 99 );
function pagelayer_single_product_number_related_products( $args ) {
	
	$options = pagelayer_get_customize_options();

	if(empty($options['woo_number_related_product'])){
		return $args;
	}
	
	$args['posts_per_page'] = $options['woo_number_related_product']; // # Of related products
	
	if(!empty($options['woo_col_related_product'])){
		$args['columns'] = $options['woo_col_related_product'];
	}
	
	return $args;
}

add_filter( 'pagelayer_wc_styles_array', 'pagelayer_pro_wc_styles_array');
function pagelayer_pro_wc_styles_array($woo_styles){
	
	$styles = array(
		'woo_shop_pagi_bg_color' => array(
			'.woocommerce .woocommerce-pagination ul li a, .woocommerce .woocommerce-pagination a.page-numbers' => 'background-color: {{color}}',
		),
		'woo_shop_pagi_color' => array(
			'.woocommerce .woocommerce-pagination ul li a, .woocommerce .woocommerce-pagination a.page-numbers' => 'color: {{color}}',
		),
		'woo_shop_pagi_borderwidth' => array(
			'.woocommerce .woocommerce-pagination ul li a, .woocommerce .woocommerce-pagination a.page-numbers' => 'border: {{val}}px solid',
			'.woocommerce .woocommerce-pagination ul li a:focus, .woocommerce .woocommerce-pagination ul li a:hover, .woocommerce .woocommerce-pagination ul li span.current, .woocommerce .woocommerce-pagination a.page-numbers:hover, .woocommerce .woocommerce-pagination a.page-numbers:focus, .woocommerce .woocommerce-pagination span.page-numbers.current' => 'border: {{val}}px solid',
		),
		'woo_shop_pagi_border_color' => array(
			'.woocommerce .woocommerce-pagination ul li a, .woocommerce .woocommerce-pagination a.page-numbers' => 'border-color: {{color}}',
		),
		'woo_shop_pagi_borderradius' => array(
			'.woocommerce .woocommerce-pagination ul li a, .woocommerce .woocommerce-pagination a.page-numbers' => 'border-radius: {{val}}px',
			'.woocommerce .woocommerce-pagination ul li a:focus, .woocommerce .woocommerce-pagination ul li a:hover, .woocommerce .woocommerce-pagination ul li span.current, .woocommerce .woocommerce-pagination a.page-numbers:hover, .woocommerce .woocommerce-pagination a.page-numbers:focus, .woocommerce .woocommerce-pagination span.page-numbers.current' => 'border-radius: {{val}}px',
		),
		'woo_shop_pagi_bg_hover_color' => array(
			'.woocommerce .woocommerce-pagination ul li a:focus, .woocommerce .woocommerce-pagination ul li a:hover, .woocommerce .woocommerce-pagination ul li span.current, .woocommerce .woocommerce-pagination a.page-numbers:hover, .woocommerce .woocommerce-pagination a.page-numbers:focus, .woocommerce .woocommerce-pagination span.page-numbers.current' => 'background-color: {{color}}',
		),
		'woo_shop_pagi_hover_color' => array(
			'.woocommerce .woocommerce-pagination ul li a:focus, .woocommerce .woocommerce-pagination ul li a:hover, .woocommerce .woocommerce-pagination ul li span.current, .woocommerce .woocommerce-pagination a.page-numbers:hover, .woocommerce .woocommerce-pagination a.page-numbers:focus, .woocommerce .woocommerce-pagination span.page-numbers.current' => 'color: {{color}}',
		),
		'woo_shop_pagi_hover_border_color' => array(
			'.woocommerce .woocommerce-pagination ul li a:focus, .woocommerce .woocommerce-pagination ul li a:hover, .woocommerce .woocommerce-pagination ul li span.current, .woocommerce .woocommerce-pagination a.page-numbers:hover, .woocommerce .woocommerce-pagination a.page-numbers:focus, .woocommerce .woocommerce-pagination span.page-numbers.current' => 'border-color: {{color}}',
		),
		'woo_onsale_bg_color' => array(
			'.woocommerce .product span.onsale' => 'background-color:{{color}};',
		),
		'woo_onsale_color' => array(
			'.woocommerce .product span.onsale' => 'color:{{color}};',
		),
		'woo_onsale_radius' => array(
			'.woocommerce .product span.onsale' => 'border-radius:{{color}}%;',
		),
		'woo_product_breadcrumb_color' => array(
			'.single-product .woocommerce-breadcrumb, .single-product .woocommerce-breadcrumb *' => 'color: {{color}}'
		),
		'woo_product_description_color' => array(
			'.single-product div.product .woocommerce-product-details__short-description, .single-product div.product .woocommerce-product-details__short-description p, .single-product div.product .product_meta, .single-product div.product .entry-content' => 'color: {{color}}'
		),
		'woo_product_price_color' => array(
			'.single-product div.product p.price, .single-product div.product span.price' => 'color: {{color}}'
		),
		'woo_product_title_color' => array(
			'.single-product .product .entry-title' => 'color: {{color}}'
		)
	);
	
	return array_merge( $woo_styles, $styles ); 
}
	

add_filter( 'customize_register', 'pagelayer_pro_customizer_get_fields', 11);
function pagelayer_pro_customizer_get_fields($wp_customize){
	
	// General Setting
	
	$wp_customize->add_setting( 'pagelayer_lable_onsale', array(
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control( new Pagelayer_Customize_Control(
		$wp_customize, 'pagelayer_lable_onsale', array(
			'type' => 'hidden',
			'section' => 'pgl_woo_general',
			'description' => __('<div class="pagelayer-customize-heading"><div>Onsale Style</div></div>', 'pagelayer'),
			'li_class' => 'pagelayer-accordion-tab',
			'priority' => 2
		)
	));
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_disable_onsale]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',						
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Custom_Control( $wp_customize, 'pagelayer_customizer_options[woo_disable_onsale]', array(
			'type' => 'checkbox',
			'label' => __('Disable Sale Notification'),
			'section' => 'pgl_woo_general',
			'priority' => 2
		))
	);
	
	// Adds Customizer settings
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_onsale_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_onsale_color]', array(
			'label' => __('Sale Text Color'),
			'section' => 'pgl_woo_general',
			'priority' => 3
		) )
	);
	
	// Adds Customizer settings
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_onsale_bg_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_onsale_bg_color]', array(
			'label' => __('Sale Background'),
			'section' => 'pgl_woo_general',
			'priority' => 4
		) )
	);
	
	// General Setting
	pagelayer_register_slider_custoze_control($wp_customize, array(
		'control' => 'pagelayer_customizer_options[woo_onsale_radius]',
		'section' => 'pgl_woo_general',
		'setting_type' => 'option',
		'label' => __( 'Sale Notification Radius'),
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'priority' => 4,
		'input_attrs' => array(
			'min' => 0,
			'max' => 100,
			'step' => 1,
		)
	));
	
	pagelayer_register_slider_custoze_control($wp_customize, array(
		'control' => 'pagelayer_customizer_options[woo_product_image_width]',
		'section' => 'pgl_woo_single_product',
		'setting_type' => 'option',
		'label' => __('Image Width'),
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'priority' => 3,
		'input_attrs' => array(
			'min' => 0,
			'max' => 70,
			'step' => 1,
		)
	));
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_enable_product_zoom]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',						
		)
	);
	
	$wp_customize->add_control( 'pagelayer_customizer_options[woo_enable_product_zoom]', array(
			'type' => 'select',
			'label' => __('Image Zoom Effect'),
			'section' => 'pgl_woo_single_product',
			'priority' => 4,
			'choices' => array(
				'' => __('Default'),
				'enable' => __('Enable'),
				'disable' => __('Disable'),
			)
		)
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_disable_product_desc]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',						
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Custom_Control( $wp_customize, 'pagelayer_customizer_options[woo_disable_product_desc]', array(
			'type' => 'checkbox',
			'label' => __('Hide Products Description'),
			'section' => 'pgl_woo_single_product',
			'priority' => 5
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_disable_upsells]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',						
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Custom_Control( $wp_customize, 'pagelayer_customizer_options[woo_disable_upsells]', array(
			'type' => 'checkbox',
			'label' => __('Disable Products Up Sells'),
			'section' => 'pgl_woo_single_product',
			'priority' => 6
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_disable_related_product]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',						
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Custom_Control( $wp_customize, 'pagelayer_customizer_options[woo_disable_related_product]', array(
			'type' => 'checkbox',
			'label' => __('Disable Related Products'),
			'section' => 'pgl_woo_single_product',
			'priority' => 7
		))
	);
	
	pagelayer_register_slider_custoze_control($wp_customize, array(
		'control' => 'pagelayer_customizer_options[woo_number_related_product]',
		'section' => 'pgl_woo_single_product',
		'setting_type' => 'option',
		'label' => __('No. of Related Products'),
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'priority' => 8,
		'input_attrs' => array(
			'min' => 1,
			'max' => 10,
			'step' => 1,
		),
	));
	
	pagelayer_register_slider_custoze_control($wp_customize, array(
		'control' => 'pagelayer_customizer_options[woo_col_related_product]',
		'section' => 'pgl_woo_single_product',
		'setting_type' => 'option',
		'label' => __('Related Products columns'),
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'priority' => 8,
		'input_attrs' => array(
			'min' => 1,
			'max' => 6,
			'step' => 1,
		),
	));
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_product_title_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_product_title_color]', array(
			'label' => __('Title Color'),
			'section' => 'pgl_woo_single_product',
			'priority' => 9
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_product_price_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_product_price_color]', array(
			'label' => __('Price Color'),
			'section' => 'pgl_woo_single_product',
			'priority' => 10
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_product_description_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_product_description_color]', array(
			'label' => __('Description Color'),
			'section' => 'pgl_woo_single_product',
			'priority' => 11
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_product_breadcrumb_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_product_breadcrumb_color]', array(
			'label' => __('Breadcrumb Color'),
			'section' => 'pgl_woo_single_product',
			'priority' => 12
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_checkout_label_placeholder]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',						
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Custom_Control( $wp_customize, 'pagelayer_customizer_options[woo_checkout_label_placeholder]', array(
			'type' => 'checkbox',
			'label' => __('Show Label as Placeholder'),
			'section' => 'pgl_woo_checkout',
			'priority' => 4
		))
	);
	
	// Shop page settings
	$wp_customize->add_setting( 'pagelayer_lable_shop_pagi', array(
		'capability' => 'edit_theme_options',
	));

	$wp_customize->add_control( new Pagelayer_Customize_Control(
		$wp_customize, 'pagelayer_lable_shop_pagi', array(
			'type' => 'hidden',
			'section' => 'pgl_woo_product_catalog',
			'description' => __('<div class="pagelayer-customize-heading"><div>Pagination Style</div></div>', 'pagelayer'),
			'li_class' => 'pagelayer-accordion-tab',
			'priority' => 11
		)
	));
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_shop_pagi_bg_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_shop_pagi_bg_color]', array(
			'label' => __('Background Color'),
			'section' => 'pgl_woo_product_catalog',
			'priority' => 11
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_shop_pagi_bg_hover_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh'
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_shop_pagi_bg_hover_color]', array(
			'label' => __('Background Hover Color'),
			'section' => 'pgl_woo_product_catalog',
			'priority' => 11
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_shop_pagi_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_shop_pagi_color]', array(
			'label' => __('Text Color'),
			'section' => 'pgl_woo_product_catalog',
			'priority' => 11
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_shop_pagi_hover_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_shop_pagi_hover_color]', array(
			'label' => __('Text Hover Color'),
			'section' => 'pgl_woo_product_catalog',
			'priority' => 11
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_shop_pagi_border_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_shop_pagi_border_color]', array(
			'label' => __('Border Color'),
			'section' => 'pgl_woo_product_catalog',
			'priority' => 11
		))
	);
	
	$wp_customize->add_setting( 'pagelayer_customizer_options[woo_shop_pagi_hover_border_color]', array(
			'type' => 'option',
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
		)
	);
	
	$wp_customize->add_control( new Pagelayer_Customize_Alpha_Color_Control( $wp_customize, 'pagelayer_customizer_options[woo_shop_pagi_hover_border_color]', array(
			'label' => __('Border Hover Color'),
			'section' => 'pgl_woo_product_catalog',
			'priority' => 11
		))
	);
	
	pagelayer_register_slider_custoze_control($wp_customize, array(
		'control' => 'pagelayer_customizer_options[woo_shop_pagi_borderwidth]',
		'section' => 'pgl_woo_product_catalog',
		'setting_type' => 'option',
		'label' => __('Pagination Border Width'),
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'absint',
		'priority' => 11,
		'input_attrs' => array(
			'min' => 0,
			'max' => 50,
			'step' => 1,
		),
		'responsive' => 1,
	));
	
	pagelayer_register_slider_custoze_control($wp_customize, array(
		'control' => 'pagelayer_customizer_options[woo_shop_pagi_borderradius]',
		'section' => 'pgl_woo_product_catalog',
		'setting_type' => 'option',
		'label' => __('Border Radius'),
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'absint',
		'priority' => 11,
		'input_attrs' => array(
			'min' => 0,
			'max' => 100,
			'step' => 1,
		),
	));
		
}