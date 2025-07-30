<?php
/**
 * Builder bootstrap file.
 *
 * @since ??
 * @package Builder
 */

use ET\Builder\Framework\Utility\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Requires Autoloader.
 */
require __DIR__ . '/vendor/autoload.php';


/**
 * Define constants.
 */
if ( ! defined( 'ET_BUILDER_5_URI' ) ) {
	/**
	 * Defines ET_BUILDER_5_URI constant.
	 *
	 * @var string ET_BUILDER_5_URI The builder directory URI.
	 */
	define( 'ET_BUILDER_5_URI', get_template_directory_uri() . '/includes/builder' );
}

// Require root files from `/server`.

// Require security fixes early.
require_once __DIR__ . '/Security/Security.php';

/*
 * Only load lf we are:
 * - on a theme builder page,
 * - or on a WP post edit screen,
 * - or on a VB page,
 * - or in ajax request,
 * - or in a REST API request,
 * - but otherwise, not ever in admin
 */
if (
	Conditions::is_tb_admin_screen()
	|| Conditions::is_wp_post_edit_screen()
	|| Conditions::is_vb_app_window()
	|| Conditions::is_ajax_request()
	|| Conditions::is_rest_api_request()
	|| ! Conditions::is_admin_request()
) {
	require_once __DIR__ . '/VisualBuilder/VisualBuilder.php';
}

/*
 * Only load lf we are:
 * - on a theme builder page,
 * - or on a VB page,
 * - or in ajax request,
 * - or in a REST API request,
 * - but otherwise, not ever in admin
 */
if (
	Conditions::is_tb_admin_screen()
	|| Conditions::is_vb_app_window()
	|| Conditions::is_ajax_request()
	|| Conditions::is_rest_api_request()
	|| ! Conditions::is_admin_request()
) {
	require_once __DIR__ . '/ThemeBuilder/ThemeBuilder.php';
	require_once __DIR__ . '/Packages/ShortcodeModule/ShortcodeModule.php';
	require_once __DIR__ . '/Packages/ModuleLibrary/Modules.php';
	require_once __DIR__ . '/Packages/Module/Layout/Components/DynamicContent/DynamicContent.php';
}

/*
 * Only load if we are not in admin.
 * This is for frontend.
 */
if ( ! Conditions::is_admin_request() ) {
	require_once __DIR__ . '/FrontEnd/FrontEnd.php';
}

/*
 * Only load if we are in admin.
 * This is for admin area functionality only.
 */
if ( Conditions::is_admin_request() ) {
	require_once __DIR__ . '/Admin/Admin.php';
}

/*
 * Load the migration system.
 * It is loaded directly here to avoid any conflicts with the other files.
 * So, it can run all kind of migrations.
 */
require_once __DIR__ . '/Migration/Migration.php';
