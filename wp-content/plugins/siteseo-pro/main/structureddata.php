<?php
/*
* SITESEO
* https://siteseo.io
* (c) SITSEO Team
*/

namespace SiteSEOPro;

if(!defined('ABSPATH')){
	die('Hacking Attempt !');
}

class StructuredData{
	
	static function enqueue_metabox(){	
		$post_id = get_the_ID();

		wp_enqueue_style('siteseo-structured-data-metabox', SITESEO_PRO_ASSETS_URL.'/css/metabox.css');
		wp_enqueue_script('siteseo-index-highlight', SITESEO_PRO_ASSETS_URL.'/js/index-highlight.js', ['jquery'], SITESEO_PRO_VERSION);
		wp_enqueue_script('siteseo-structured-data-metabox', SITESEO_PRO_ASSETS_URL.'/js/metabox.js', ['jquery'], SITESEO_PRO_VERSION);

		wp_localize_script('siteseo-structured-data-metabox', 'structuredDataMetabox', [
			'propertyTemplates' => \SiteSEOPro\StructuredData::get_schema_properties(),
			'currentPostUrl' => get_permalink($post_id)
		]);
	}

	static function display_metabox(){
		global $post;
		
		if(is_front_page() || is_home()){
			$post_id = get_option('page_on_front');

			if(!$post_id && is_home()){
				$post_id = get_option('page_for_posts');
			}
			
		} else{
			$post_id = $post ? $post->ID : 0;
		}
		
		if(!empty($post_id)){
			$schema_type = !empty(get_post_meta($post_id, '_siteseo_structured_data_type', true)) ? get_post_meta($post_id, '_siteseo_structured_data_type', true) : '';
			$schema_properties = !empty(get_post_meta($post_id, '_siteseo_structured_data_properties', true)) ? get_post_meta($post_id, '_siteseo_structured_data_properties', true) : '';
			$custom_schema = !empty(get_post_meta($post_id, '_siteseo_structured_data_custom', true)) ? get_post_meta($post_id, '_siteseo_structured_data_custom', true) : '';
		}
		
		$schema_types['Article'] = 'Article';
		$schema_types['NewsArticle'] = 'News Article';
		$schema_types['Blogposting'] = 'Blog post';
		$schema_types['Product'] = 'Product';
		$schema_types['Recipe'] = 'Recipe';
		$schema_types['Restaurant'] = 'Restaurant';
		$schema_types['Course'] = 'Course';
		$schema_types['LocalBusiness'] = 'Local Business';
		$schema_types['Person'] = 'Person';
		$schema_types['Organization'] = 'Organization';
		$schema_types['Book'] = 'Book Recording';
		$schema_types['MusicRecording'] = 'Music Album';
		$schema_types['SoftwareApplication'] = 'Software Application';
		$schema_types['VideoObject'] = 'Video';
		$schema_types['Event'] = 'Event';
		$schema_types['JobPosting'] = 'Job Posting';
		$schema_types['CustomSchema'] = 'Custom Schema';
		
		
		$custom_schema_placeholder = json_encode([
			'type' => 'object',
			'properties' => [
				'placeholder' => ['type' => 'string'],
				'description' => ['type' => 'integer']
			]
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		
		echo'<div class="siteseo-metabox-option-wrap">
			<div class="siteseo-metabox-label-wrap">
				<label for="siteseo_structured_data_type">'.esc_html__('Select Schema Types','siteseo-pro').'</label>
			</div>
			<div class="siteseo-metabox-input-wrap">
				<select name="siteseo_structured_data_type" class="siteseo_structured_data_type" id="siteseo_structured_data_type">
					<option value="">'.esc_html__('None', 'siteseo-pro').'</option>';
					foreach($schema_types as $type => $label){
						echo '<option value="'.esc_attr($type).'" '.selected($schema_type, $type, false).'>'.esc_html($label).'</option>';
					}
				echo'</select>
				</input>
			</div>
		</div>';
		
		$schema_template = self::get_schema_properties();
		
		echo'<div class="siteseo-metabox-schema" id="siteseo_schema_properties_container" class="'.(empty($schema_type) || $schema_type === 'CustomSchema' ? 'hidden' : '').'">
					<div class="siteseo-schema-properties">';

					if(!empty($schema_type) && isset($schema_template[$schema_type]) && $schema_type !== 'CustomSchema'){
						
						$is_textarea = ['description', 'instructions', 'reviewBody', 'questions', 'step', 'ingredients', 'recipeInstructions', 'courseDescription', 'bookDescription', 'softwareRequirements', 'menu', 'name'];
						
						$is_date_type = ['datePublished', 'dateModified', 'uploadDate', 'startDate', 'endDate', 'foundingDate', 'releaseDate'];
						
						foreach($schema_template[$schema_type] as $property => $default){
							echo '<p><h4 for="siteseo_schema_property_'.esc_attr($property).'">'.esc_html(ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $property))).':</h4>';

							if(in_array($property, $is_textarea)){
								echo '<textarea name="schema_properties['.esc_attr($property).']" id="siteseo_schema_property_' .esc_attr($property).'" rows="3" class="widefat">'.esc_textarea(isset($schema_properties[$property]) ? $schema_properties[$property] : '').'</textarea>';
							} else if(in_array($property, $is_date_type)){
								echo '<input type="datetime-local" name="schema_properties['.esc_attr($property).']" id="siteseo_schema_property_'.esc_attr($property).'" value="' .esc_attr(isset($schema_properties[$property]) ? $schema_properties[$property] : '').'" class="widefat">';
							} else {
								echo '<input type="text" name="schema_properties['.esc_attr($property).']" id="siteseo_schema_property_'.esc_attr($property).'" value="' .esc_attr(isset($schema_properties[$property]) ? $schema_properties[$property] : '').'" class="widefat">';
							}

							echo '</p>';
						}
					}
		
				echo '</div>
			</div>
		
		<div  id="siteseo_custom_schema_container" class="'.($schema_type !== 'CustomSchema' ? 'hidden' : '').'">
			<h4>'.esc_html__('Custom Schema', 'siteseo-pro').'</h4>
				<p>
					<textarea name="siteseo_structured_data_custom" placeholder="'.esc_attr($custom_schema_placeholder).'" rows="10" class="siteseo_structured_data_custom widefat code">'.
					esc_textarea($custom_schema).'</textarea>
				</p>
				<p class="description">'.
				// translators: %1$s and %2$s are the opening and closing <a> tags around "Read here".
				sprintf(esc_html__('Create your custom schema as per guidelines. %1$sRead here%2$s.', 'siteseo-pro'),
				'<a href="https://schema.org/docs/schemas.html" target="_blank" rel="noopener noreferrer">',
				'</a>').'</p>
		</div>
		
		<div class="siteseo-metabox-option-wrap">
			<div class="siteseo-schema-preview">
				<h4>'.esc_html__('JSON-LD Preview', 'siteseo-pro').'</h4>
				<a class="button" id="siteseo_validate_schema">'.esc_html__('Google Validation', 'siteseo-pro').'</a>
				
				<pre id="siteseo_schema_preview" class="siteseo_schema_preview">';
				if($schema_type === 'CustomSchema' && !empty($custom_schema)){
					echo '<div id="siteseo_highlighter">'.esc_html($custom_schema).'</div>';
				} elseif (!empty($schema_type) && !empty($schema_properties)){
					$schema_data = array(
						'@context' => 'https://schema.org',
						'@type' => $schema_type
					);

					foreach($schema_properties as $key => $value){
						if(!empty($value)){
							$schema_data[$key] = $value;
						}
					}
					
					echo'<div id="siteseo_highlighter" class="siteseo_highlighter">'.esc_html(json_encode($schema_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)).'</div>';
				} else{
					echo'<div>'.esc_html__('No schema has been selected.', 'siteseo-pro').'</div>';
				}
				echo'</pre>
			</div>
		</div>';		
	}
	
	static function get_schema_properties(){
		
		return [
			'Article'=> [
				'headline' => '',
				'author' => '',
				'datePublished' => '',
				'dateModified' => '',
				'publisher' => '',
				'description' => '',
			],
			'Blogposting' => [
				'headline' => '',
				'author' => '',
				'datePublished' => '',
				'dateModified' => '',
				'publisher' => '',
				'description' => '',
			],
			'Course' => [
				'name' => '',
				'Description' => '',
				'provider' => '',
				'availableLanguage' => '',
				'coursePrerequisites' =>'',
				'courseCode' => '',
				'hasCourseInstance' => '',
				'timeRequired' => '',
				'educationalCredentialAwarded' => '',
			],
			'MusicRecording' => [
				'name' => '',
				'byArtist' => '',
				'duration' => '',
				'recordingOf' => '',
				'inAlbum' => '',
				'datePublished' => '',
				'releasedEvent' => '',
				'abstract' => '',
			],
			'Book' => [
				'name' => '',
				'author' => '',
				'bookEdition' => '',
				'isbn' => '',
				'publisher' => '',
				'datePublished' => '',
				'abstract' => '',
				'inLanguage' => '',
			],
			'Restaurant' => [
				'name' => '',
				'address' => '',
				'hasMenu' => '',
				'telephone' => '',
				'priceRange' => '',
				'openingHours' => '',
			],
			'SoftwareApplication' => [
				'name' => '',
				'applicationCategory' => '',
				'applicationSubCategory' => '',
				'availableOnDevice' => '',
				'operatingSystem' => '',
				'softwareVersion' => '',
				'softwareRequirements' => '',
				'downloadUrl' => '',
			],
			'VideoObject' => [
				'name' => '',
				'description' => '',
				'thumbnailUrl' => '',
				'uploadDate' => '',
				'embedUrl' => '',
				'publisher' =>'',
				'creator' => '',
			],
			'Event' => [
				'name' => '',
				'startDate' => '',
				'endDate' => '',
				'location' => '',
				'description' => '',
				'offers' => '',
				'organizer' => '',
				'performer' => '',
			],
			'Recipe' => [
				'name' => '',
				'author' => '',
				'description' => '',
				'cookTime' => '',
				'cookingMethod' => '',
				'prepTime' => '',
				'totalTime' => '',
				'recipeYield' => '',
				'recipeCategory' => '',
				'recipeCuisine' => '',
				'recipeInstructions' => '',
			],
			'Person' =>[
				'name' => '',
				'jobTitle' => '',
				'email' => '',
				'telephone' => '',
				'address' => '',
			],
			'Organization' => [
				'name' => '',
				'url' =>'',
				'description' => '',
				'email' => '',
				'founder' => '',
				'foundingDate' => '',
				'numberOfEmployees' => '',
				'location' => '',
			],
			'JobPosting' =>[
				'name' => '',
				'industry' => '',
				'title' => '',
				'totalJobOpenings' => '',
				'skills' => '',
				'jobBenefits' => '',
				'jobLocationType' => '',
			],
			'NewsArticle' =>[
				'headline' => '',
				'author' => '',
				'datePublished' => '',
				'dateModified' => '',
				'publisher' => '',
				'description' => '',
			],
			'Product' =>[
				'name' => '',
				'description' => '',
				'brand' => '',
				'category' => '',
				'releaseDate' => '',
				'size' => '',	
			],

			'LocalBusiness' =>[
				'legalName' => '',
				'founder' => '',
				'address' => '',
				'email' => '',
				'numberOfEmployees' => '',
				'telephone' => '',
				'taxID' => '',
				'vatID' => '',
			],
		]; 
	}
	
	static function save_metabox($post_id, $post){
		
		// Security Check
		if(!isset($_POST['siteseo_metabox_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['siteseo_metabox_nonce']), 'siteseo_metabox_nonce')){
			return $post_id;
		}

		//Post type object
		$post_type = get_post_type_object($post->post_type);

		//Check permission
		if(!current_user_can($post_type->cap->edit_post, $post_id)){
			return $post_id;
		}
		
		if(isset($_POST['siteseo_structured_data_type'])){
			update_post_meta($post_id, '_siteseo_structured_data_type', sanitize_text_field($_POST['siteseo_structured_data_type']));
		} else{
			delete_post_meta($post_id, '_siteseo_structured_data_type');
		}
		
		if(isset($_POST['siteseo_structured_data_custom'])){
			update_post_meta($post_id, '_siteseo_structured_data_custom', wp_kses_post($_POST['siteseo_structured_data_custom']));
		} else{
			delete_post_meta($post_id, '_siteseo_structured_data_custom');
		}
		
		if(isset($_POST['schema_properties']) && is_array($_POST['schema_properties'])){
			
			$properties = array();
			$text_area_fields = array('description', 'instructions', 'reviewBody', 'questions', 'step', 'ingredients','recipeInstructions', 'courseDescription', 'bookDescription', 'softwareRequirements', 'menu');
			
			foreach($_POST['schema_properties'] as $key => $value){
				if(in_array($key, $text_area_fields)){
					$properties[$key] = sanitize_textarea_field($value);
				} else{
					$properties[$key] = sanitize_text_field($value);
				}
			}
			
			update_post_meta($post_id, '_siteseo_structured_data_properties', $properties);
		} else{
			delete_post_meta($post_id, '_siteseo_structured_data_properties');
		}
	}
	
	static function render(){
		global $siteseo, $post;
		
		if(empty($siteseo->pro['enable_structured_data']) || empty($siteseo->pro['toggle_state_stru_data'])){
			return; // disable
		}
		
		if(!is_singular()){
			return;
		}

		$schema_type = !empty(get_post_meta($post->ID , '_siteseo_structured_data_type', true)) ? get_post_meta($post->ID, '_siteseo_structured_data_type', true) : '';
		
		if($schema_type === 'CustomSchema'){
			$custom_schema = !empty(get_post_meta($post->ID , '_siteseo_structured_data_custom', true)) ? get_post_meta($post->ID, '_siteseo_structured_data_custom', true) : '';
			if(!empty($custom_schema)){
				echo'<script type="application/ld+json">'.json_encode($custom_schema, JSON_UNESCAPED_SLASHES).'</script>' . "\n";
			}

		} else {
			
			$schema_properties = !empty(get_post_meta($post->ID, '_siteseo_structured_data_properties', true)) ? get_post_meta($post->ID, '_siteseo_structured_data_properties', true) : '';
			
			if(!empty($schema_type) && is_array($schema_properties)){
				$schema_data = array(
					'@context' => 'https://schema.org',
					'@type' => $schema_type
				);
				
				foreach($schema_properties as $key => $value){
					if(!empty($value)){
						$schema_data[$key] = $value;
					}
				}
				
				echo'<script type="application/ld+json">'.json_encode($schema_data, JSON_UNESCAPED_SLASHES).'</script>' . "\n";
				
			}
		}
	}
}
