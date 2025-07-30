<?php

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

$steps = array(
	'welcome' => __('Welcome', 'softaculous-pro'),
	'type' => __('Business Type','softaculous-pro'),
	'title' => __('Site Title','softaculous-pro'),
	'features' => __('Goals','softaculous-pro'),
	'import_theme' => __('Choose Template','softaculous-pro'),
);

$active_step = isset($_GET['step']) && array_key_exists($_GET['step'], $steps) ? $_GET['step'] : 'welcome';

include_once(dirname(__FILE__).'/onboarding.php');
$softaculous_pro['templates'] = softaculous_pro_get_templates_list();
$spro_setup_info = get_option('softaculous_pro_setup_info');
$spro_onboarding_done = get_option('softaculous_pro_onboarding_done');

update_option('softaculous_pro_onboarding_shown', time());

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$installed_plugins = get_plugins();

if(!empty($softaculous_pro['branding']['default_hf_bg'])){
	echo '
	<style>
	.softaculous-pro-wizard-sidebar {
		background-color:'.$softaculous_pro['branding']['default_hf_bg'].' !important;
	}
	</style>';
}

if(!empty($softaculous_pro['branding']['default_hf_text'])){
	echo '
	<style>
	.softaculous-pro-wizard-steps li, .softaculous-pro-wizard-steps li::before {
		color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
		border-color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
	}
	.softaculous_pro_return_btn span, .dashicons-exit::before {
		color:'.$softaculous_pro['branding']['default_hf_text'].' !important;
	}
	</style>';
}

?>
<style>
*,
*::before,
*::after {
	box-sizing: content-box;
}
</style>

<div class="softaculous-pro-wizard">
	<div class="softaculous-pro-wizard-sidebar">
		<div class="softaculous-pro-setup-logo">
			<a href="<?php echo admin_url('admin.php?page=assistant&act=onboarding'); ?>">
				<img src="<?php echo esc_attr($softaculous_pro['branding']['logo_url']);?>" style="max-width:200px;" />
			</a>
		</div>
		<div class="softaculous-pro-steps-holder">
			<ol class="softaculous-pro-wizard-steps">
				<?php foreach ($steps as $key => $name) : ?>
                <a href="admin.php?page=assistant&act=onboarding&step=<?php echo $key; ?>"><li class="<?php echo ($key == $active_step ? 'active_step' : ''); ?>"><span
                        data-step="<?php echo $key; ?>"><?php echo $name; ?></span></li>
				</a>
				<?php endforeach; ?>
			</ol>
		</div>
		<a class="softaculous_pro_return_btn" style="cursor:pointer;" onclick="return softaculous_pro_onboarding_dismiss(event);">
		<span class="dashicons dashicons-exit"></span><span><?php _e('Exit', 'softaculous-pro'); ?></span></a>
	</div>
	
	<div class="softaculous-pro-wizard-content" data-active-panel="<?php echo $active_step; ?>">
		<!-- Step Welcome -->
		<div class="softaculous-pro-wizard-inner" data-panel="welcome">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('Welcome to the Onboarding process!', 'softaculous-pro'); ?></h1>
				<p><?php _e('This process will help you choose a professional template for your website and install plugins that you might need to achieve your goal for creating this website', 'softaculous-pro'); ?>
				</p>
				<?php if (!empty($spro_onboarding_done)): ?>
				<div class="softaculous-pro-wizard-buttons">
					<input type="checkbox" id="onboarding_done_confirm" name="onboarding_done_confirm" style="margin:0px;" /> &nbsp;&nbsp;
					<label for="onboarding_done_confirm" style="cursor:pointer;"><?php _e('It looks like you have already completed the onboarding process. You might lose data if you run the onboarding process again. Select this checkbox to confirm that you agree.', 'softaculous-pro'); ?></label>
				</div>
				<?php endif; ?>
				<div class="softaculous-pro-wizard-buttons">
                    <button class="step_btn step_next_btn" data-step="type"
                        onclick="softaculous_pro_next_handler(this)"><?php _e('Get Started', 'softaculous-pro'); ?><span
                            class="dashicons dashicons-arrow-right-alt"></span></button>
		
                    <button class="step_btn step_next_btn step_dismiss_btn" data-step="type"
                        onclick="softaculous_pro_onboarding_dismiss(event);"><?php _e('No, I don\'t want to try an easy setup process', 'softaculous-pro'); ?><span
                            class="dashicons dashicons-no-alt"></span></button>
				</div>
			</div>
		</div>
		<!-- Step Type -->
		<div class="softaculous-pro-wizard-inner" data-panel="type">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('How would you categorize your website ?', 'softaculous-pro'); ?></h1>
				<p><?php _e('This helps us recommend design and functionalities for your website', 'softaculous-pro'); ?></p>
			</div>
			<div class="softaculous-pro-category-input">
                <input type="text" class="softaculous_pro_input" id="cat_input" placeholder="<?php _e('Search for a category', 'softaculous-pro'); ?>" />
			</div>
			<div class="softaculous-pro-category-holder">
				<div class="softaculous-pro-categories-list">
					<?php foreach ($softaculous_pro['templates']['categories'] as $cslug => $cdata) : ?>
                    <div class="category_btn">
						<input type="button" id="cat_button_<?php echo $cslug; ?>" value= <?php echo esc_html($cdata['en']); ?> data-target=<?php echo $cslug; ?> />
					</div>
					<?php endforeach; ?>
                    <div id="spro_no_cat_results" style="display:none;">
						<h3><i><?php echo __('No results match your search criteria', 'softaculous-pro'); ?></i></h3>
					</div>
				</div>
			</div>
			<br /><br />
			<div class="softaculous-pro-wizard-buttons">
                <button onclick="softaculous_pro_prev_handler(this)" data-step="welcome"
                    class="step_btn step_prev_btn"><?php _e('Previous Step', 'softaculous-pro'); ?></button>
                <button class="step_btn step_next_btn" data-step="title"
                    onclick="softaculous_pro_next_handler(this)"><?php _e('Continue', 'softaculous-pro'); ?> <span
                        class="dashicons dashicons-arrow-right-alt"></span></button>
            </div>
		</div>
		<!-- Step Title -->
		<div class="softaculous-pro-wizard-inner" data-panel="title">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('Enter the title of your new site', 'softaculous-pro'); ?></h1>
				<p><?php _e('Can be changed later', 'softaculous-pro'); ?></p>
			</div>
			<div class="softaculous-pro-title-input">
				<input type="text" class="softaculous_pro_input" placeholder="<?php _e('Enter a title for your website', 'softaculous-pro'); ?>" value="<?php echo esc_html(get_bloginfo('name')); ?>" autocomplete="off"/>
			</div>
            <div class="softaculous-pro-wizard-buttons">
                <button onclick="softaculous_pro_prev_handler(this)" data-step="type"
                    class="step_btn step_prev_btn"><?php _e('Previous Step', 'softaculous-pro'); ?></button>
                <button class="step_btn step_next_btn" data-step="features"
                    onclick="softaculous_pro_next_handler(this)"><?php _e('Continue', 'softaculous-pro'); ?><span
                        class="dashicons dashicons-arrow-right-alt"></span></button>
            </div>
		</div>
		<!-- Step Features -->
		<div class="softaculous-pro-wizard-inner" data-panel="features">
			<div class="softaculous-pro-wizard-inner-content">
				<h1><?php _e('What are you looking to achieve with your new site ?', 'softaculous-pro'); ?></h1>
				<p><?php _e('We will install the appropriate plugins that will add the required functionality to your website', 'softaculous-pro'); ?></p>
			</div>
            <div class="softaculous-pro-features-container">
                <?php foreach(spro_get_features_list() as $slug => $feature):?>
                <label for="<?php echo $slug;?>_input" style="cursor:pointer;">
		<div class="softaculous-pro-features" data-slug="<?php echo $slug; ?>">
                    <div class="softaculous-pro-features-icon">
                        <span class="<?php echo $feature['icon']; ?>"></span>
                    </div>
                    <div class="softaculous-pro-features-text">
                        <h3><?php echo $feature['name']; ?></h3>
                        <p><?php echo $feature['info']; ?></p>
                    </div>
                    <div class="softaculous-pro-features-input">
                        <input type="checkbox" onclick="softaculous_pro_selected_features(this)" id="<?php echo $slug;?>_input" <?php echo (!empty($spro_setup_info) && !empty($spro_setup_info['features']) && in_array($slug, $spro_setup_info['features']) ? 'checked="checked"' : '') ;
						
				foreach($feature['plugin'] as $info){
					if (!empty($info['requires_php']) && version_compare(PHP_VERSION, $info['requires_php'], '<')) {
						echo ' disabled';
						echo ' spro-erro="'.sprintf(__('Requires PHP version %1$s or higher', 'softaculous-pro'), $info['requires_php']).'"';
						break;
					}
					echo (!empty($installed_plugins[$info['plugin_init']]) ? 'checked="checked"' : '');
				} ?>/>
                    </div>
                </div>
	    	</label>
                <?php endforeach; ?>
            </div>
            <div class="softaculous-pro-wizard-buttons">
                <button onclick="softaculous_pro_prev_handler(this)" data-step="title"
                    class="step_btn step_prev_btn"><?php _e('Previous Step', 'softaculous-pro'); ?> </button>
                <button class="step_btn step_next_btn" data-step="import_theme"
                    onclick="softaculous_pro_next_handler(this)"><?php _e('Continue', 'softaculous-pro'); ?> <span
                        class="dashicons dashicons-arrow-right-alt"></span></button>
            </div>
        </div>
        <!-- Step Import theme -->
        <div class="softaculous-pro-wizard-inner" data-panel="import_theme">
            <?php
				softaculous_pro_templates();
			?>
        </div>
	</div>
</div>
