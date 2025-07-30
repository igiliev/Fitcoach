<?php

require_once ET_BUILDER_DIR . 'autoload.php';
require_once ET_BUILDER_DIR . 'i18n.php';

require_once ET_BUILDER_DIR . 'compat/early.php';
require_once ET_BUILDER_DIR . 'compat/scripts.php';

require_once ET_BUILDER_DIR . 'core.php';
require_once ET_BUILDER_DIR . 'functions.php';
require_once ET_BUILDER_DIR . 'conditions.php';

// Always load this base class.
require_once ET_BUILDER_DIR . 'class-et-builder-plugin-compat-base.php';
// Always load this loader base class.
require_once ET_BUILDER_DIR . 'class-et-builder-plugin-compat-loader-base.php';
// Always load this loader class, its loading plugin compat files for the entire framework.
require_once ET_BUILDER_DIR . 'class-et-builder-framework-plugin-compat-loader.php';

require_once ET_BUILDER_DIR . 'compat/woocommerce.php';
require_once ET_BUILDER_DIR . 'post/PostStack.php';
require_once ET_BUILDER_DIR . 'class-et-theme-builder-layout.php';

// TODO make this not need to be loaded on FE.
require_once ET_BUILDER_DIR . 'feature/gutenberg/blocks/Layout.php';
require_once ET_BUILDER_DIR . 'feature/gutenberg/utils/Editor.php';

require_once ET_BUILDER_DIR . 'feature/icon-manager/ExtendedFontIcons.php';

// Dynamic Assets deprecated functions for backward-compatibility.
require_once ET_BUILDER_DIR . 'feature/dynamic-assets/dynamic-assets.php';

if ( is_admin() || et_is_test_env() ) {
	require_once ET_BUILDER_DIR . 'feature/gutenberg/blocks/PostExcerpt.php';
	require_once ET_BUILDER_DIR . 'feature/gutenberg/utils/Conversion.php';
	require_once ET_BUILDER_DIR . 'feature/gutenberg/EditorTypography.php';
}


if ( is_admin() ) {
	require_once ET_BUILDER_DIR . 'feature/ClassicEditor.php';
	require_once ET_BUILDER_DIR . 'feature/BlockEditorIntegration.php';
}

require_once ET_BUILDER_DIR . 'feature/content-retriever/ContentRetriever.php';


// TODO, this needs to be worked on for D5
require_once ET_BUILDER_DIR . 'class-et-builder-dynamic-assets-feature.php';
require_once ET_BUILDER_DIR . 'ab-testing.php';
require_once ET_BUILDER_DIR . 'class-et-builder-settings.php';

require_once ET_BUILDER_DIR . 'feature/window.php';
require_once ET_BUILDER_DIR . 'feature/search-posts.php';

// TODO, rework this to work with VB Demo
// if ( is_user_logged_in() ) {
	require_once ET_BUILDER_DIR . 'feature/ErrorReport.php';
// }

// TODO - optimize this, all of this doesnt need to load on every pageload.
require_once ET_BUILDER_DIR . 'frontend-builder/theme-builder/theme-builder.php';

if ( is_admin() ) {
	require_once ET_BUILDER_DIR . 'feature/gutenberg/BlockTemplates.php';
}

// TODO, rework this to work with VB Demo
// if ( is_user_logged_in() ) {
	require_once ET_BUILDER_DIR . 'feature/local-library.php';
	require_once ET_BUILDER_DIR . 'feature/ai-button.php';
// }


global $shortname;

if (
	apply_filters( 'et_builder_enable_jquery_body', true ) &&
	! (
		is_admin() ||
		wp_doing_ajax() ||
		et_is_builder_plugin_active() ||
		is_customize_preview() ||
		is_et_pb_preview()
	) &&
	'on' === et_get_option( $shortname . '_enable_jquery_body', 'on' )
) {
	require_once ET_BUILDER_DIR . 'feature/JQueryBody.php';
}

// Register assets that need to be fired at head
require_once ET_BUILDER_DIR . 'assets.php';

function et_builder_load_d5_frontend_builder() {
	global $et_current_memory_limit;

	$et_current_memory_limit = et_core_get_memory_limit();

	// Set memory limit when there is a limit set, and that is less than 256M.
	if ( $et_current_memory_limit && $et_current_memory_limit < 256 ) {
		@ini_set( 'memory_limit', '256M' );
	}

	require_once ET_BUILDER_DIR . 'frontend-builder/init.php';
}

add_action( 'wp', 'et_builder_load_d5_frontend_builder' );
