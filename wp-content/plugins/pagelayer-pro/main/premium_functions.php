<?php

//////////////////////////////////////////////////////////////
//===========================================================
// class.php
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

// Audio Handler
function pagelayer_sc_audio(&$el){
	global $pagelayer;
	
	if(empty($pagelayer->sc_audio_enqueued) && !pagelayer_is_live_iframe()){
		wp_enqueue_script('wp-mediaelement');
		wp_enqueue_style( 'wp-mediaelement' );
		$pagelayer->sc_audio_enqueued = 1;
	}
	
	return;
	
	$el['atts']['a_url'] = '';

	if ($el['atts']['source'] == 'external'){
		$el['atts']['a_url'] = $el['atts']['url'];
	}
	
	if ($el['atts']['source'] == 'library'){
		
		$el['atts']['a_url'] = wp_get_attachment_url($el['atts']['id']); 
	}
	if(!empty($el['atts']['a_url'])){
		
		$filename=$el['atts']['a_url'];
		
		//Get the file extension 
		
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
	

		//Create source tag according to audio file
		switch($extension){
			
			default:
			case 'mp3':
				$el['atts']['a_type'] = 'audio/mpeg';
				break;
			
			case 'ogg':
				$el['atts']['a_type']= 'audio/ogg';
				break;
			
			case 'wav':
				$el['atts']['a_type'] = 'audio/wav';
				break;
		}
	}

	 if(!empty($el['atts']['a_url']) && !empty($el['atts']['a_type'])){
		$el['attr'][]= ['source' => 'src="{{a_url}}'];
		$el['attr'][]= ['source' => 'type="{{a_type}}'];
	} 

}

// Image Portfolio
function pagelayer_sc_single_img(&$el){
	
	// Decide the image URL
	$img_size = pagelayer_isset($el['atts'], 'img-size');
	$el['atts']['func_img'] = pagelayer_isset($el['tmp'], 'img-'.$img_size.'-url');
	$el['atts']['func_img'] = empty($el['atts']['func_img']) ? pagelayer_isset($el['tmp'], 'img-url') : $el['atts']['func_img'];
	
	// What is the link ?
	if(!empty($el['atts']['link_type'])){
		
		// Custom url
		if($el['atts']['link_type'] == 'custom_url'){
			// Backward compatibility for new link props
			pagelayer_add_link_backward($el, array( 'rel' => '', 'selector' => '.pagelayer-ele-link'));
			$el['atts']['func_link'] = empty($el['tmp']['link']) ? '' : $el['tmp']['link'];
		}
		
		// Link to the media file itself
		if($el['atts']['link_type'] == 'media_file'){
			$el['atts']['func_link'] = $el['atts']['func_img'];
		}
		
		// Lightbox
		if($el['atts']['link_type'] == 'lightbox'){
			$el['atts']['func_link'] = $el['atts']['func_img'];
		}
		
	}
	
}

// Posts Grid
function pagelayer_sc_wp_posts_grid($atts, $content = '', $tag = ''){
	
	$args = array(
			'numberposts' => -1,
			'post_type' => 'post',
			'post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash')
	); 
	$all_posts = get_posts($args);
	
	$html = '<div '.pagelayer_create_sc($tag, $atts, 'pagelayer-posts-grid').'>';
	
	//pagelayer_print($all_posts);
	foreach($all_posts as $pk => $pv){
		$post_link = get_permalink($pv->ID);
		$html .= '<div>
			<h2><a href="'.$post_link.'">'.$pv->post_title.'</a></h2>
			<p>'.date('F jS, Y', strtotime($pv->post_date)).' | Published by <a href="'.site_url('author/'.get_the_author_meta('user_login', $pv->post_author)).'">'.get_the_author_meta('display_name', $pv->post_author).'</a></p>
			<p>'.pagelayer_the_content($pv->post_content).'</p>
			<p><a href="'.$post_link.'">Read More</a></p>
		</div>';
	}
	
	$html .= '</div>';
	
	return $html;
}

// Posts Slider
function pagelayer_sc_wp_posts_slider(&$el){
	$params = array();
	$params['post'] = array();
	
	if($el['atts']['post_type']) $params['post']['post_type'] = $el['atts']['post_type'];
	if($el['atts']['post_count']) $params['post']['post_count'] = $el['atts']['post_count'];
	if($el['atts']['category']) $params['post']['category'] = $el['atts']['category'];
	if($el['atts']['tags']) $params['post']['tags'] = $el['atts']['tags'];
	if($el['atts']['order_by']) $params['post']['order_by'] = $el['atts']['order_by'];
	if($el['atts']['sort_order']) $params['post']['sort_order'] = $el['atts']['sort_order'];
	if($el['atts']['image_size']) $params['post']['image_size'] = $el['atts']['image_size'];
	if($el['atts']['show_excerpt']) $params['post']['show_excerpt'] = $el['atts']['show_excerpt'];
	
	$el['atts']['posts_slides'] = pagelayer_posts_slider($params);
	//wp_reset_postdata();
}

// Search function 
function pagelayer_sc_search(&$el){
	if(!empty($el['atts']['placeholder'])){
		$el['tmp']['placeholder'] = htmlspecialchars($el['atts']['placeholder']);
	}	
}

// Post portfolio
function pagelayer_sc_post_folio(&$el){
	
	$args = array();

	if($el['atts']['type']) $args['post_type'] = $el['atts']['type'];
	
	// Filter by
	if($el['atts']['filter_by']) $args['filter_by'] = $el['atts']['filter_by'];
	
	// Page count
	if($el['atts']['count']) $args['posts_per_page'] = $el['atts']['count'];
	
	$el['atts']['post_html'] = pagelayer_widget_posts($args);
	
}

// Posts Handler
function pagelayer_sc_posts(&$el){
	
	global $pagelayer;

	$allow_param = array('show_thumb', 'thumb_size', 'show_content', 'show_title', 'more', 'btn_type', 'size', 'icon_position', 'icon', 'show_more', 'meta_sep', 'exc_length', 'post_type', 'exc_term', 'exc_author', 'offset','ignore_sticky', 'orderby', 'by_period', 'before_date', 'after_date', 'thumb_img_type', 'infinite_types' );
	
	$param = array();
	
	// Page count
	$param['posts_per_page'] = !empty($el['atts']['count']) ?  $el['atts']['count'] : '';
	
	$param['order'] = !empty($el['atts']['posts_order']) ?  $el['atts']['posts_order'] : '';
	$param['term'] = !empty($el['atts']['inc_term']) ?  $el['atts']['inc_term'] : '';
	$param['author_name'] = !empty($el['atts']['inc_author']) ?  $el['atts']['inc_author'] : '';
	
	if(!empty($el['atts']['thumb_img_type'])){
		$thumb_size = pagelayer_isset($el['atts'], 'thumb_size');
		$img_size = pagelayer_isset($el['tmp'], 'def_thumb_img-'.$thumb_size.'-url');
		$param['def_thumb_img'] = empty($img_size) ? pagelayer_isset($el['tmp'], 'def_thumb_img-url') : $img_size;	
	}
	
	foreach($allow_param as $val){
		$param[$val] = !empty($el['atts'][$val]) ?  $el['atts'][$val] : '';
	}
	
	if(!empty($el['atts']['meta'])){
		
		$meta_arr = explode(',',$el['atts']['meta']);
		//pagelayer_print($el['atts']['meta']);
		foreach($meta_arr as $arr){
			$param[$arr] = $arr;
		}		
		
	}
	
	if(wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] == 'pagelayer_infinite_posts'){
		$param['paged'] = $el['atts']['paged'];
	}else{
		$data = array('tag' => $el['tag'],'atts' => $el['oAtts']);
		$pagelayer->localScript['pagelayer_post_'.$el['id']] = $data;
	}
	
	//pagelayer_print($param);
	$el['atts']['post_html'] = pagelayer_posts($param);
	
}

// Author Box
function pagelayer_sc_author_box(&$el){
	
	global $post;
	
	if($el['atts']['box_source'] == 'current'){		
		$author_data = pagelayer_author_data ($post->ID);
		$el['atts']['display_name'] = $author_data['display_name'];
		$el['atts']['description'] = $author_data['description'];
		$el['atts']['user_url'] = $author_data['user_url'];
		$el['tmp']['avatar-url'] = $author_data['avatar'];
		$el['tmp']['avatar-title'] = '';
		$el['tmp']['avatar-alt'] = '';
	}else{
		$el['tmp']['avatar-url'] = empty($el['tmp']['avatar-url']) ? $el['atts']['avatar'] : $el['tmp']['avatar-url'];
	}
	
	$el['atts']['display_html'] = '<'.$el['atts']['name_style'].'>'.$el['atts']['display_name'].'</'.$el['atts']['name_style'].'>';
	
}

//Grid Gallery Handler
function pagelayer_sc_login(&$el){
	
	if(pagelayer_is_live()){
		$el['atts']['login_cap'] = '';
		return false;
	}
	
	ob_start();
	
	if(!did_action( 'login_enqueue_scripts' )){
		do_action( 'login_enqueue_scripts' );
	}
	
	do_action( 'login_form' );
	$el['atts']['login_cap'] = ob_get_clean();
}

// Load all tags
function pagelayer_get_tags(){
	$tags = get_tags(['hide_empty' => false]);
	$taglist = array();
	$taglist[] = 'Default';
	foreach ($tags as $tag) {
		$taglist[$tag->name] = $tag->name ;
	}
	return $taglist;
}

// Load all categories
function pagelayer_get_categories(){
	$categories = get_categories(['hide_empty' => 0]);
	$category_list = array();
	$category_list[] = 'Default';
	foreach($categories as $category) {
		$category_list[$category->name] = $category->name ;
	}
	return $category_list;
}

// Templates Handler - 2C
function pagelayer_sc_templates(&$el){

	global $pagelayer, $post;
	
	if( !empty($el['atts']['templates']) ) $id = $el['atts']['templates'];
	
	$post_obj = get_post($id);
	$content = '';
	
	// If both current post and tempate post are same
	if (empty( $post_obj) || (!empty( $post_obj) && $post_obj == $post) ) {
		$el['atts']['template_content'] = $content;
		return;
	}
	
	$pagelayer->dont_make_editable = true;
	$content = $post_obj->post_content;
	$content = apply_filters( 'the_content', $content );
	$pagelayer->dont_make_editable = false;
	
	if(pagelayer_is_live()){
		// Create the HTML object
		$node = pagelayerQuery::parseStr($content);
		$node->query('.pagelayer-ele')->removeClass('pagelayer-ele');
		$content = $node->html();
	}
	
	$el['atts']['template_content'] = $content;
}

// Get the list of post by post type - 2C
function pagelayer_post_list_by_type($post_type = 'post'){
	$postlist = [];
	
	$posts_list = get_posts(array(
			'post_type' => $post_type,
			'numberposts' => -1
		));
	
	foreach($posts_list as $post){
		$postlist[$post->ID] = $post->post_title;
	}
	
	return $postlist;
}

/////////////////////////////////////
// WooCommerce Shortcode Functions
/////////////////////////////////////

// Product Images Handler - 2C
function pagelayer_sc_product_images(&$el){
	global $product;
	
	$product = pagelayer_get_product();
	
	$images_templ = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$images_templ = __pl('no_woo_product');
	}
	
	if( empty( $product ) ) {
		$el['atts']['product_images_templ'] = $images_templ;
		return;
	}
	
	// Start the output buffer
	ob_start();
	
	if( !empty($el['atts']['sale_flash']) ){
		wc_get_template( 'loop/sale-flash.php' );
	}
	
	wc_get_template( 'single-product/product-image.php' );
	
	$el['atts']['product_images_templ'] = ob_get_clean();

}

// Related products Handler - 2C
function pagelayer_sc_product_related(&$el){
	global $product;
	
	$product = pagelayer_get_product();
	
	$related_templ = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$related_templ = __pl('no_woo_product');
	}
	
	if( empty( $product ) ) {
		$el['atts']['related_products'] = $related_templ;
		return;
	}
	
	// start output buffer
	ob_start();
	
	// If is related
	if($el['atts']['select_product'] == 'related'){
		
		$args = [
			'posts_per_page' => 4,
			'columns' => 4,
			'orderby' => $el['atts']['order_by'],
			'order' => $el['atts']['order'],
		];

		if( ! empty( $el['atts']['posts_per_page'] ) ) {
			$args['posts_per_page'] = $el['atts']['posts_per_page'];
		}

		if( ! empty( $el['atts']['columns'] ) ) {
			$args['columns'] = $el['atts']['columns'];
		}
		
		if(function_exists( 'woocommerce_related_products' )){
			woocommerce_related_products($args);	
		}
			
	// If is upsel;
	}elseif($el['atts']['select_product'] == 'upsell'){
		
		$limit = '-1';
		$columns = 4;
		$orderby = $el['atts']['order_by'];
		$order =  $el['atts']['order'];

		if( ! empty( $el['atts']['columns'] ) ) {
			$columns = $el['atts']['columns'];
		}
				
		if(function_exists( 'woocommerce_upsell_display' )){
			woocommerce_upsell_display( $limit, $columns, $orderby, $order );	
		}
		
	}
	
	// Get data and clean output buffer
	$el['atts']['related_products'] = ob_get_clean();
	
}

// WooCommers Pages - 2C
function pagelayer_sc_woo_pages(&$el){
	
	// if is not empty
	if(!empty($el['atts']['pages'])){
		
		$shortcode = '['. $el['atts']['pages'] .']';
		$content = pagelayer_the_content($shortcode);
		
		// if is checkout page
		if ( 'woocommerce_checkout' === $el['atts']['pages'] && '<div class="woocommerce"></div>' ==  $content ) {
			$content = '<div class="woocommerce">' . __( 'Your cart is currently empty.') . '</div>';
		}
		
	}
	
	// If the content is empty
	if(empty($content)){
		$content = '<div class="woocommerce">' . __( 'Page content not found.') . '</div>';
	}

	$el['atts']['page_content'] = $content;
}

// Product Pages - 2C
function pagelayer_sc_product_categories(&$el){
	
	$attributes = '';
	$attributes .= ' number="'. (isset($el['atts']['number']) ? $el['atts']['number'] : '').'" ';
	$attributes .= ' columns="'. (isset($el['atts']['columns']) ? $el['atts']['columns'] : '').'" ';
	$attributes .= ' hide_empty="'. (!empty($el['atts']['hide_empty']) ? 1 : 0) .'" ';
	$attributes .= ' orderby="'. (isset($el['atts']['nuorderbymber']) ? $el['atts']['nuorderbymber'] : '') .'" ';
	$attributes .= ' order="'. (isset($el['atts']['order']) ? $el['atts']['order'] : '') .'" ';	
	
	$source = isset($el['atts']['source']) ? $el['atts']['source'] : '';
	
	if ( 'by_id' === $source ) {
		$attributes .= ' ids="'. $el['atts']['by_id'] .'" ';
	} elseif ( 'by_parent' === $source ) {
		$attributes .= ' parent="'. $el['atts']['parent'] .'" ';
	} elseif ( 'current_subcategories' === $source ) {
		$attributes .= ' parent="'. get_queried_object_id() .'" ';
	}

	$shortcode = '[product_categories '. $attributes .']';
	
	// do_shortcode the shortcode
	$el['atts']['product_categories'] = pagelayer_the_content($shortcode);
	
}
	
// Products - 2C
function pagelayer_sc_products(&$el){
	
	if( WC()->session ){
		wc_print_notices();
	}
	
	$no_found = $el['atts']['no_found'];
		
	$attributes = '';
	$type = $el['atts']['source'];
	$attributes .= ' columns="'. $el['atts']['columns'] .'" ';
	$attributes .= ' rows="'. $el['atts']['rows'] .'" ';
	$attributes .= ' paginate="'. (!empty($el['atts']['paginate']) ? true : false) .'" ';
	$attributes .= ' orderby="'. $el['atts']['orderby'] .'" ';
	$attributes .= ' order="'. $el['atts']['order'] .'" ';	
	$attributes .= ' cache="false" ';	
	
	// Hide the catalog order
	if( empty($el['atts']['allow_order']) ){
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	}
	
	// Hide the result count
	if( empty($el['atts']['show_result']) ){
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	}
	
	if( $type == 'by_id' ){
		$type = 'products';
		$attributes .= ' ids="'. (!empty($el['atts']['ids']) ? $el['atts']['ids'] : '') .'" ';	
	}elseif( $type == 'pagelayer_current_query' ){
		
		$atts['paginate'] = (!empty($el['atts']['paginate']) ? true : false);
		$atts['cache'] = false;
				
		$type = 'pagelayer_current_query';
		
		// Set the current query
		add_action( 'woocommerce_shortcode_products_query', 'pagelayer_shortcode_current_query', 10, 10);
		
		// If product not found
		add_action( "woocommerce_shortcode_{$type}_loop_no_results", function ($attributes) use ($no_found){
			echo '<div class="pagelayer-product-no-found">'.$no_found.'</div>';
		} );
	
		$shortcode = new WC_Shortcode_Products( $atts, $type );
			
		$el['atts']['products_content'] = $shortcode->get_content();
		return true;
	}
		
	$shortcode = '['.$type.' '. $attributes .']';
	
	$content = pagelayer_the_content($shortcode);
	
	// If product not found
	if('<div class="woocommerce columns-'.$el['atts']['columns'] .' "></div>' == $content){
		$content = '<div class="pagelayer-product-no-found">'. __($no_found) .'</div>';
	}
	
	$el['atts']['products_content'] = $content;
}

// Archives Product Pages - 2C
function pagelayer_sc_product_archives(&$el){
	global $post;
	
	if ( WC()->session ) {
		wc_print_notices();
	}
	
	$atts['paginate'] = true;
	$atts['cache'] = false;
	$no_found = $el['atts']['no_found'];
		
	if( empty($el['atts']['allow_order']) ){
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	}
	if( empty($el['atts']['show_result']) ){
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	}
	
	$type = 'pagelayer_current_query';
	
	// We need to define costom  
	if( (isset($post->post_type) && $post->post_type == 'pagelayer-template') || wp_doing_ajax()){
		$type = '';
	}
	
	// Set the current query
	add_action( 'woocommerce_shortcode_products_query', 'pagelayer_shortcode_current_query', 10, 10);
	
	// If product not found
	add_action( "woocommerce_shortcode_{$type}_loop_no_results", function ($attributes) use ($no_found){
		echo '<div class="pagelayer-product-no-found">'.$no_found.'</div>';
	} );
	
	$shortcode = new WC_Shortcode_Products( $atts, $type );
	
	$el['atts']['product_archives'] = $shortcode->get_content();
}

// Product Price render - 2C
function pagelayer_sc_product_price(&$el) {
	global $product;
	
	$product = pagelayer_get_product();
	
	$price = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$price = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['pagelayer-product-price'] = $price;
		return;
	}
	
	ob_start();
	
	wc_get_template( '/single-product/price.php' );
	
	$el['atts']['pagelayer-product-price'] = ob_get_clean();
	
}

// Product add to cart render - 2C
function pagelayer_sc_add_to_cart(&$el) {
	
	global $product;

	$product = pagelayer_get_product();
	
	$cart = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$cart = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['product_add_to_cart'] = $cart;
		return;
	}
	
	ob_start();
	
	woocommerce_template_single_add_to_cart();
	
	$el['atts']['product_add_to_cart'] = '<div class="pagelayer-add-to-cart-holder pagelayer-product-'. esc_attr( $product->get_type() ) .'">
		'. ob_get_clean() .'
	</div>';
	
}

// Product rating render - 2C
function pagelayer_sc_product_rating(&$el) {
	
	if( ! post_type_supports( 'product', 'comments' ) ){
		return;
	}

	global $product;
	
	$product = pagelayer_get_product();
	
	$product_rating = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$product_rating = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['product_rating'] = $product_rating;
		return;
	}
	
	ob_start();
	
	wc_get_template( '/single-product/rating.php' );
	
	$product_rating =  ob_get_clean();
	
	if( empty( $product_rating ) && pagelayer_is_live_template()){
		$product_rating = __('No Rating Found!');
	}
	
	$el['atts']['product_rating'] = $product_rating;
	
}
/* 
// Product stock render - 2C
function pagelayer_product_stock() {
	
	global $product;
	$product = wc_get_product();

	if ( empty( $product ) ) {
		return;
	}

	return wc_get_stock_html( $product );
	
} */

// Product meta render - 2C
function pagelayer_sc_product_meta(&$el) {
	
	global $product;
	
	$product = pagelayer_get_product();
	
	$product_meta = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$product_meta = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['product_meta'] = $product_meta;
		return;
	}

	ob_start();
	wc_get_template( '/single-product/meta.php' );
	
	$el['atts']['product_meta'] = ob_get_clean();
	
}

// Product short description render - 2C
function pagelayer_sc_product_short_desc(&$el) {
	global $product, $post;
	
	$product = pagelayer_get_product();
	
	$product_short_desc = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$product_short_desc = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['product_short_desc'] = $product_short_desc;
		return;
	}
		
	if((isset($post->post_type) && $post->post_type == 'pagelayer-template') || wp_doing_ajax()){
		$el['atts']['product_short_desc'] = '<div class="woocommerce-product-details__short-description"><p>'.$product->get_short_description().'</p></div>';
		return;
	}
	
	ob_start();
	
	wc_get_template( 'single-product/short-description.php' );
	
	$el['atts']['product_short_desc'] = ob_get_clean();
	
}

// WooCommerce breadcrumb render - 2C
function pagelayer_woo_breadcrumb() {
	ob_start();
	woocommerce_breadcrumb();
	return ob_get_clean();
}

// Get product categories - 2C
function pagelayer_get_product_cat() {
	$categories = get_terms( 'product_cat' );

	$options = [];
	foreach ( $categories as $category ) {
		$options[ $category->term_id ] = $category->name;
	}
	
	return $options;
}

// Get product categories - 2C
function pagelayer_get_product_archives_desc() {
	ob_start();
	do_action( 'woocommerce_archive_description' );
	return ob_get_clean();
}

// Get product additional Information - 2C
function pagelayer_sc_product_addi_info(&$el) {
	global $product;
	
	$product = pagelayer_get_product();
	
	$product_additional_info = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$product_additional_info = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['product_additional_info'] = $product_additional_info;
		return;
	}
	
	ob_start();
	wc_get_template( 'single-product/tabs/additional-information.php' );

	$el['atts']['product_additional_info'] = ob_get_clean();
}

// Get product data tab Information - 2C
function pagelayer_sc_product_data_tabs(&$el) {
	global $product, $post;
	
	$product = pagelayer_get_product();
	
	$product_data_tab = '';
	
	if( empty( $product ) && (pagelayer_is_live_template() || wp_doing_ajax())){
		$product_data_tab = __pl('no_woo_product');
	}
	
	if ( empty( $product ) ) {
		$el['atts']['product_data_tab'] = $product_data_tab;
		return;
	}
	
	// We need load  Pagelayer shortcodes
	pagelayer_load_shortcodes();
	setup_postdata( $product->get_id());

	ob_start();
	wc_get_template( 'single-product/tabs/tabs.php' );	
	
	$data_tabs = ob_get_clean();
	
	// If no data tabs 
	if(empty($data_tabs)){
		$data_tabs =  __('Data tab not found');
	}
	
	$el['atts']['product_data_tab'] = $data_tabs;
}

// Get the HTML for menu cart
function pagelayer_sc_woo_menu_cart(&$el){
	
	// Maybe init cart
	$has_cart = is_a( WC()->cart, 'WC_Cart' );

	if ( ! $has_cart ) {
		$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
		WC()->session = new $session_class();
		WC()->session->init();
		WC()->cart = new \WC_Cart();
		WC()->customer = new \WC_Customer( get_current_user_id(), true );
	}
	
	// Get the cart values
	$widget_cart_is_hidden = apply_filters( 'woocommerce_widget_cart_is_hidden', is_cart() || is_checkout() );
	$product_count = WC()->cart->get_cart_contents_count();
	$sub_total = WC()->cart->get_cart_subtotal();
	$cart_items = WC()->cart->get_cart();

	$toggle_button_link = $widget_cart_is_hidden ? wc_get_cart_url() : '#';
	/** workaround WooCommerce Subscriptions issue that changes the behavior of is_cart() */
	$toggle_button_classes = 'pagelayer-cart-button pagelayer-size-sm';
	$toggle_button_classes .= $widget_cart_is_hidden ? ' pagelayer-menu-cart-hidden' : '';
	$counter_attr = 'data-counter="' . $product_count . '"';
	
	$cart_html = '<div class="pagelayer-menu-cart-toggle">
		<a href="'. esc_attr( $toggle_button_link ) .'" class="'. $toggle_button_classes .'">
			<span class="pagelayer-cart-button-text">'. $sub_total .'</span>
			<span class="pagelayer-cart-button-icon" '. $counter_attr .'>
				<i class="'.$el['atts']['icon_type'].'" aria-hidden="true"></i>
			</span>
		</a>
	</div>';
	
	// If is cart and checkout page the except this
	if ( ! $widget_cart_is_hidden ){
		ob_start();
		wc_get_template( 'cart/mini-cart.php' );	
		$mini_cart_html = ob_get_clean();
				
		$cart_html .= '<div class="pagelayer-menu-cart-container">
			<form class="pagelayer-menu-cart-main woocommerce-cart-form" action="'. esc_url( wc_get_cart_url() ) .'" method="post">
				<div class="pagelayer-menu-cart-close">&times;</div>
				'. $mini_cart_html .'
			</form>
		</div>';
	}
	
	$el['atts']['cart_html'] = $cart_html;
}

// SiteMap Item Box - 2C
function pagelayer_sc_sitemap_item(&$el){
	$html_element = '';
	$hier = ''; 
	$depth = '';
	$option = '';
	$id = $el['id'];
	
	$option = '<div class="pagelayer-sitemap-section">';
	$hier = (empty($el['atts']['hierarchical']) ? '' : $el['atts']['hierarchical']);
	$depth = (empty($el['atts']['depth']) ? '' : $el['atts']['depth']);
	
	
	if($el['atts']['sitemap_type'] == 'post_type'){
		
		if(empty($el['atts']['title'])){
			$el['atts']['title'] = 'Pages';
		}
		
		$html_element .= $el['atts']['title'];
		
		$args = array(
			'post_type' => $el['atts']['source_post'],
			'orderby' => $el['atts']['order_post'],
			'order' => $el['atts']['order'],
			'hierarchical' => $hier,
			'number' => $depth,
			'posts_per_page' => -1,
		);
		
		$option .= '<span>'.$html_element.'</span>';
		$option .= '<ul>';
		$pages = new WP_Query($args);
		$posts = $pages->posts;
		foreach ( $posts as $page ) {
			$option .= '<li class="pagelayer-sitemap-list-item" data-postID="'.$page->ID.'"><a class="pagelayer-ele-link" href="'.get_permalink($page->ID).'">'.$page->post_name.'</a></li>';
		}
		$option .= '</ul>';	
		
	}else{
		if(empty($el['atts']['title'])){
			$el['atts']['title'] = 'Categories';
		}
		
		$html_element .= $el['atts']['title'];
		
		$args = array(
			'title_li' => 0,
			'orderby' => $el['atts']['order_taxonomy'],
			'order' => $el['atts']['order'],
			'style' => '',
			'hide_empty' => $el['atts']['hide_empty'],
			'echo' => false,
			'hierarchical' => $hier,
			'taxonomy' => $el['atts']['source_taxonomy'],
			'depth' => $depth,		
		);

		$taxonomies = get_categories( $args );
		
		$option .= '<span>'.$html_element.'</span>';
		$option .= '<ul>';	
		foreach ( $taxonomies as $taxonomy ) {
			$option .= '<li class="pagelayer-sitemap-list-item" data-postID="'.$taxonomy->term_id.'"><a class="pagelayer-ele-link" href="'.get_term_link($taxonomy->term_id).'">'.$taxonomy->name.'</a></li>';
		}
		$option .= '</ul>'; 
	}
	
	$option .= '</div>';

	$el['atts']['sitemap_html'] = $option;

}

function pagelayer_sc_slides(&$el) {
	
	if( !pagelayer_is_live() ) {
		return;
	}
	
	foreach($el['inner_blocks'] as $key => $inner_block) {
		
		if('pagelayer/pl_slide' != $inner_block['blockName']) {
			continue;
		}
		
		$slide = serialize_block($inner_block);
		$col = get_comment_delimited_block_content('pagelayer/pl_col', [] , $slide);
		$row = get_comment_delimited_block_content('pagelayer/pl_inner_row', ['col_gap' => '0.0'] , $col);
		
		$el['inner_blocks'][$key] = array(
			'blockName' => 'pagelayer/pl_content_slide',
			'innerBlocks' => parse_blocks($row),
			'innerHTML' => '',
			'attrs' => array(),
			'innerContent' => array(),
		);
	}	
}

function pagelayer_sc_chart(&$el){
	$el['atts']['xcolor'] = empty($el['atts']['xcolor']) ? '' : pagelayer_parse_color($el['atts']['xcolor'], false);
	$el['atts']['ycolor'] = empty($el['atts']['ycolor']) ? '' : pagelayer_parse_color($el['atts']['ycolor'], false);
}

function pagelayer_sc_chart_datasets(&$el){
	$el['atts']['chart_border_color'] = empty($el['atts']['chart_border_color']) ? '' : pagelayer_parse_color($el['atts']['chart_border_color'], false);
	$el['atts']['bg_color'] = empty($el['atts']['bg_color']) ? '' : pagelayer_parse_color($el['atts']['bg_color'], false);
}

// Render the image map
function pagelayer_sc_image_map(&$el) {
	
	$map_size = pagelayer_isset($el['atts'], 'img_map-size');
	$map_key = 'img_map-id-' . $map_size . '-url';
	$el['atts']['map_img_id'] = isset($el['tmp'][$map_key]) ? $el['tmp'][$map_key] : pagelayer_isset($el['tmp'], 'map_img-id-url');

	$el['atts']['pagelayer-srcset'] = $el['atts']['map_img_id'] . ', ' . $el['atts']['map_img_id'] . ' 1x, ';

	// Handle multiple paths
	if(isset($el['atts']['pagelayer_image_map']) && is_array($el['atts']['pagelayer_image_map'])){
		$el['atts']['pagelayer_map_path'] = ''; // Initialize as empty
		foreach ($el['atts']['pagelayer_image_map'] as $key => $data) {
			$data_cord = isset($data['path']) ? $data['path'] : '';
			$data_id = isset($key) ? $key : '';
			$data_link = isset($data['link']) ? $data['link'] : '';
			$el['atts']['pagelayer_map_path'] .= "<path class='pagelayer-imgmap-item' d='' stroke-width='2' data-cord='" . $data_cord . "' data-id='" . $data_id . "' fill-opacity='0.3' fill-rule='evenodd' data-link='" . $data_link . "'></path>";
		}
	}
	
	if(empty($el['atts']['pagelayer_map_path'])){
		$el['atts']['pagelayer_map_path'] = ' ';
	}
	
	$image_atts = array(
		'name' => 'img_map-id',
		'size' => 'img_map-size'
	);

	pagelayer_get_img_srcset($el, $image_atts);
}