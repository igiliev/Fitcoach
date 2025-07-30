<?php
/**
 * App Preferences.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\AppPreferences;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class for handling application preferences.
 *
 * @since ??
 */
class AppPreferences {
	/**
	 * Get an array mapping of application preferences.
	 *
	 * This function returns an associative array that maps various application preferences to
	 * their corresponding keys, locations, types, and default values.
	 * These preferences are used for configuring the application behavior and user interface settings.
	 *
	 * @since ??
	 *
	 * @return array The array mapping of application preferences.
	 *
	 * @example:
	 * ```php
	 * $preferences = mapping();
	 * ```
	 *
	 * @output:
	 * ```php
	 *  [
	 *     'settingsBarLocation' => [
	 *         'key' => 'settings_bar_location',
	 *         'location' => 'pageSettingsBar.position',
	 *         'type' => 'string',
	 *         'default' => 'bottom',
	 *         'options' => [
	 *             'top-left',
	 *             'top',
	 *             'top-right',
	 *             'right',
	 *             'bottom-right',
	 *             'bottom',
	 *             'bottom-left',
	 *             'left',
	 *         ],
	 *     ],
	 *     ...
	 *  ]
	 * ```
	 */
	public static function mapping(): array {
		return array(
			'settingsBarLocation'           => array(
				'key'      => 'settings_bar_location',
				'location' => 'pageSettingsBar.position',
				'type'     => 'string',
				'default'  => 'bottom',
				'options'  => array(
					'top-left',
					'top',
					'top-right',
					'right',
					'bottom-right',
					'bottom',
					'bottom-left',
					'left',
				),
			),
			'toolbarClick'                  => array(
				'key'      => 'toolbar_click',
				'location' => 'pageSettingsBar.modes',
				'type'     => 'bool',
				'default'  => false,
			),
			'toolbarDesktop'                => array(
				'key'      => 'toolbar_desktop',
				'location' => 'pageSettingsBar.views',
				'type'     => 'bool',
				'default'  => true,
			),
			'toolbarGrid'                   => array(
				'key'      => 'toolbar_grid',
				'location' => 'pageSettingsBar.modes',
				'type'     => 'bool',
				'default'  => false,
			),
			'toolbarHover'                  => array(
				'key'      => 'toolbar_hover',
				'location' => 'pageSettingsBar.modes',
				'type'     => 'bool',
				'default'  => false,
			),
			'toolbarPhone'                  => array(
				'key'      => 'toolbar_phone',
				'location' => 'pageSettingsBar.views',
				'type'     => 'bool',
				'default'  => true,
			),
			'toolbarTablet'                 => array(
				'key'      => 'toolbar_tablet',
				'location' => 'pageSettingsBar.views',
				'type'     => 'bool',
				'default'  => true,
			),
			'toolbarWireframe'              => array(
				'key'      => 'toolbar_wireframe',
				'location' => 'pageSettingsBar.views',
				'type'     => 'bool',
				'default'  => true,
			),
			'toolbarZoom'                   => array(
				'key'      => 'toolbar_zoom',
				'location' => 'pageSettingsBar.views',
				'type'     => 'bool',
				'default'  => true,
			),
			'builderAnimation'              => array(
				'key'      => 'builder_animation',
				'location' => 'interface.animation',
				'type'     => 'bool',
				'default'  => true,
			),
			'builderEnableDummyContent'     => array(
				'key'      => 'builder_enable_dummy_content',
				'location' => 'module.dummyContent',
				'type'     => 'bool',
				'default'  => true,
			),
			'hideDisabledModules'           => array(
				'key'      => 'hide_disabled_modules',
				'location' => 'module.disabled',
				'type'     => 'bool',
				'default'  => false,
			),
			'eventMode'                     => array(
				'key'      => 'event_mode',
				'location' => 'app.mode',
				'type'     => 'string',
				'default'  => 'hover',
				'options'  => array(
					'hover',
					'click',
					'grid',
				),
			),
			'viewMode'                      => array(
				'key'      => 'view_mode',
				'location' => 'app.view',
				'type'     => 'string',
				'default'  => et_builder_bfb_enabled() ? 'wireframe' : 'desktop',
				'options'  => array(
					'desktop',
					'tablet',
					'phone',
					'wireframe',
				),
			),
			'appTheme'                      => array(
				'key'      => 'app_theme',
				'location' => 'app.theme',
				'type'     => 'string',
				'default'  => 'd5-enhanced',
				'options'  => array(
					'd5-standard',
					'd5-enhanced',
				),
			),
			'appColorMode'                  => array(
				'key'      => 'app_color_mode',
				'location' => 'app.colorMode',
				'type'     => 'string',
				'default'  => 'light',
				'options'  => array(
					'light',
					'dark',
				),
			),
			'appColorScheme'                => array(
				'key'      => 'app_color_scheme',
				'location' => 'app.colorScheme',
				'type'     => 'string',
				'default'  => 'blue',
				'options'  => array(
					'blue',
					'purple',
					'green',
					'red',
					'orange',
				),
			),
			'appAdminBar'                   => [
				'key'      => 'app_admin_bar',
				'location' => 'app.adminBar',
				'type'     => 'array',
				'default'  => [
					'visible' => false,
				],
			],
			'appInteractionLayers'          => array(
				'key'      => 'app.interaction_layers',
				'location' => 'app.interactionLayers',
				'type'     => 'array',
				'default'  => array(
					'actionOnHover'       => true,
					'parentActionOnHover' => true,
					'xRay'                => false,
				),
			),
			'historyIntervals'              => array(
				'key'      => 'history_intervals',
				'location' => 'history.interval',
				'type'     => 'int',
				'default'  => 1,
				'options'  => array(
					'1',
					'10',
					'20',
					'30',
					'40',
				),
			),
			'pageCreationFlow'              => array(
				'key'      => 'page_creation_flow',
				'location' => 'pageCreationFlow.onStart',
				'type'     => 'string',
				'default'  => 'default',
				'options'  => et_builder_page_creation_settings(),
			),
			'quickActionsAlwaysStartWith'   => array(
				'key'      => 'quick_actions_always_start_with',
				'location' => 'quickActions.startWith',
				'type'     => 'string',
				'default'  => 'nothing',
			),
			'quickActionsShowRecentQueries' => array(
				'key'      => 'quick_actions_show_recent_queries',
				'location' => 'quickActions.showRecentQueries',
				'type'     => 'string',
				'default'  => 'off',
			),
			'quickActionsRecentQueries'     => array(
				'key'      => 'quick_actions_recent_queries',
				'location' => 'quickActions.recentQueries',
				'type'     => 'string',
				'default'  => '',
			),
			'quickActionsRecentCategory'    => array(
				'key'      => 'quick_actions_recent_category',
				'location' => 'quickActions.recentCategories',
				'type'     => 'string',
				'default'  => '',
			),
			'modalPreference'               => array(
				'key'      => 'modal_preference',
				'location' => 'modal.preference',
				'type'     => 'string',
				'default'  => 'right',
			),
			'modalPositionX'                => array(
				'key'      => 'modal_position_x',
				'location' => 'modal.positionX',
				'type'     => 'int',
				'default'  => 50,
			),
			'modalPositionY'                => array(
				'key'      => 'modal_position_y',
				'location' => 'modal.positionY',
				'type'     => 'int',
				'default'  => 50,
			),
			'modalSnapLocation'             => array(
				'key'      => 'modal_snap_location',
				'location' => 'modal.snapLocation',
				'type'     => 'string',
				'default'  => 'right',
			),
			'modalSnap'                     => array(
				'key'      => 'modal_snap',
				'location' => 'modal.snap',
				'type'     => 'bool',
				'default'  => false,
			),
			'modalDimensionWidth'           => array(
				'key'      => 'modal_dimension_width',
				'location' => 'modal.dimensionWidth',
				'type'     => 'int',
				'default'  => 280,
			),
			'modalDimensionHeight'          => array(
				'key'      => 'modal_dimension_height',
				'location' => 'modal.dimensionHeight',
				'type'     => 'int',
				'default'  => 320,
			),
			'modalAlwaysCollapseGroups'     => array(
				'key'      => 'modal_always_collapse_groups',
				'location' => 'modal.alwaysCollapseGroups',
				'type'     => 'bool',
				'default'  => true,
			),
		);
	}
}
