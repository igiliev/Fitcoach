<?php

/**
 * Autoloader for non-shortcode framework classes.
 *
 * @param string $class The class name.
 */
function _et_pb_light_autoload( $class ) {
	if ( 'ET_Builder_I18n' === $class ) {
		require_once ET_BUILDER_DIR . 'feature/I18n.php';
	} elseif ( 'ET_Builder_Global_Feature_Base' === $class ) {
		require_once 'class-et-builder-global-feature-base.php';
	} elseif ( 'ET_Builder_Post_Feature_Base' === $class ) {
		require_once 'class-et-builder-post-feature-base.php';
	} elseif ( 'ET_Builder_Post_Features' === $class ) {
		require_once 'class-et-builder-post-features.php';
	} elseif ( 'ET_Builder_Global_Presets_History' === $class ) {
		require_once ET_BUILDER_DIR . 'feature/global-presets/History.php';
	} elseif ( 'ET_Builder_Google_Fonts_Feature' === $class ) {
		require_once 'class-et-builder-google-fonts-feature.php';
	} elseif ( 'ET_Builder_Google_Fonts_Feature' === $class ) {
		require_once 'class-et-builder-google-fonts-feature.php';
	} elseif ( 'ET_Builder_Dynamic_Assets_Feature' === $class ) {
		require_once 'class-et-builder-dynamic-assets-feature.php';
	} elseif ( 'ET_Builder_Settings' === $class ) {
		require_once 'class-et-builder-settings.php';
	} elseif ( 'ET_Global_Settings' === $class ) {
		require_once ET_BUILDER_DIR . 'class-et-global-settings.php';
	} elseif ( 'ET_Builder_Module_Helper_Overflow' === $class ) {
		require_once ET_BUILDER_DIR . 'module/helpers/Overflow.php';
	} elseif ( 'ET_Item_Library_Local' === $class ) {
		require_once ET_CORE_PATH . '/item-library-local/ItemLibraryLocal.php';
	} elseif ( 'ET_Code_Snippets_Library_Local' === $class ) {
		require_once ET_CORE_PATH . '/code-snippets/code-snippets-library-local/CodeSnippetsLibraryLocal.php';
	} elseif ( 'ET_Theme_Options_Library_Local' === $class ) {
		require_once ET_EPANEL_DIR . '/theme-options-library/theme-options-library-local/ThemeOptionsLibraryLocal.php';
	} elseif ( 'ET_Theme_Builder_Library_Local' === $class ) {
		require_once ET_BUILDER_DIR . 'frontend-builder/theme-builder/theme-builder-library-local/ThemeBuilderLibraryLocal.php';
	}
}

spl_autoload_register( '_et_pb_light_autoload' );
