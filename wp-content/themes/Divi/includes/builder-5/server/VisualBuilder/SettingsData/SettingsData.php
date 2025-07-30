<?php
/**
 * Visual Builder Settings.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\SettingsData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\VisualBuilder\SettingsData\SettingsDataCallbacks;

/**
 * Class for handling Visual Builder settings data.
 *
 * @since ??
 */
class SettingsData implements DependencyInterface {
	/**
	 * Array of functions that returns setting data that is executed on app load.
	 *
	 * @var array
	 */
	private static $_settings_data_functions_on_app_load = [];

	/**
	 * Array of functions that returns setting data that is executed after app load (delivered via REST request).
	 *
	 * @var array
	 */
	private static $_settings_data_functions_after_app_load = [];

	/**
	 * Load the class.
	 */
	public function load() {
		self::register_items();

		// Inserted settings data on app load via filter.
		add_filter(
			'divi_visual_builder_settings_data',
			[ self::class, 'insert_settings_data_functions_on_app_load' ]
		);
	}

	/**
	 * Registering settings data items.
	 *
	 * @since ??
	 */
	public static function register_items() {
		self::register_item(
			[
				'name'               => 'breakpoints',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'breakpoints' ],
			]
		);

		self::register_item(
			[
				'name'               => 'conditionalTags',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'conditional_tags' ],
			]
		);

		self::register_item(
			[
				'name'               => 'currentPage',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'current_page' ],
			]
		);

		self::register_item(
			[
				'name'               => 'currentUser',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'current_user' ],
			]
		);

		self::register_item(
			[
				'name'               => 'customizer',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'customizer' ],
			]
		);

		self::register_item(
			[
				'name'               => 'dynamicContent',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'dynamic_content' ],
			]
		);

		self::register_item(
			[
				'name'               => 'fonts',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'fonts' ],
			]
		);

		self::register_item(
			[
				'name'               => 'globalPresets',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'global_presets' ],
			]
		);

		self::register_item(
			[
				'name'               => 'google',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'google' ],
			]
		);

		self::register_item(
			[
				'name'               => 'layout',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'layout' ],
			]
		);

		self::register_item(
			[
				'name'               => 'markups',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'markups' ],
			]
		);

		self::register_item(
			[
				'name'               => 'navMenus',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'nav_menus' ],
			]
		);

		self::register_item(
			[
				'name'               => 'nonces',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'nonces' ],
			]
		);

		self::register_item(
			[
				'name'               => 'post',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'post' ],
			]
		);

		self::register_item(
			[
				'name'               => 'preferences',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'preferences' ],
			]
		);

		self::register_item(
			[
				'name'               => 'services',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'services' ],
			]
		);

		self::register_item(
			[
				'name'               => 'settings',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'settings' ],
			]
		);

		self::register_item(
			[
				'name'               => 'shortcodeModuleDefinitions',
				'usage'              => 'after_app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'shortcode_module_definitions' ],
			]
		);

		self::register_item(
			[
				'name'               => 'shortcodeTags',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'shortcode_tags' ],
			]
		);

		self::register_item(
			[
				'name'               => 'styles',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'styles' ],
			]
		);

		self::register_item(
			[
				'name'               => 'taxonomy',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'taxonomy' ],
			]
		);

		self::register_item(
			[
				'name'               => 'themeBuilder',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'theme_builder' ],
			]
		);

		self::register_item(
			[
				'name'               => 'tinymce',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'tinymce' ],
			]
		);

		self::register_item(
			[
				'name'               => 'urls',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'urls' ],
			]
		);

		self::register_item(
			[
				'name'               => 'woocommerce',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'woocommerce' ],
			]
		);

		self::register_item(
			[
				'name'               => 'workspaces',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'workspaces' ],
			]
		);
		self::register_item(
			[
				'name'               => 'builderVersion',
				'usage'              => 'app_load',
				'get_value_function' => [ SettingsDataCallbacks::class, 'get_the_builder_version' ],
			]
		);
	}

	/**
	 * Register a settings data item.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *   Array of arguments.
	 *
	 *   @type string $name               Name of the item.
	 *   @type string $usage              Usage of the item. 'app_load', 'after_app_load', or 'both'.
	 *   @type callable $get_value_function Function that returns the value of the item.
	 * }
	 */
	public static function register_item( $args ) {
		// Get the parameters.
		$name               = $args['name'] ?? '';
		$get_value_function = $args['get_value_function'] ?? null;
		$usage              = $args['usage'] ?? 'all';

		// Decide the usage timing.
		$on_app_load    = 'app_load' === $usage;
		$after_app_load = 'after_app_load' === $usage;
		$both           = ! $on_app_load && ! $after_app_load;

		// Required arguments should be given in expected type.
		if ( empty( $name ) || ! is_callable( $get_value_function ) ) {
			return;
		}

		// Register setting data function that will be executed on app load.
		if ( $on_app_load || $both ) {
			self::$_settings_data_functions_on_app_load[ $name ] = $get_value_function;
		}

		// Register settings data function that will be executed after app load.
		if ( $after_app_load || $both ) {
			self::$_settings_data_functions_after_app_load[ $name ] = $get_value_function;
		}
	}

	/**
	 * Get settings data: array of returned value of registered settings data functions.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *   Array of arguments.
	 *
	 *  @type string $usage Usage of the settings data. 'app_load' or 'after_app_load'.
	 * }
	 */
	public static function get_settings_data( $args ) {
		$usage       = $args['usage'] ?? 'app_load';
		$on_app_load = 'app_load' === $usage;

		// Array of returned values of registered settings data functions.
		$values = [];

		// Get array of registered settings data functions.
		$registered_settings_data = $on_app_load
			? self::$_settings_data_functions_on_app_load
			: self::$_settings_data_functions_after_app_load;

		// Populate the returned values.
		foreach ( $registered_settings_data as $item_name => $get_value_function ) {
			if ( is_callable( $get_value_function ) ) {
				$values[ $item_name ] = $get_value_function();
			}
		}

		return $values;
	}

	/**
	 * Callback function that is used to insert settings data to DiviSettingsData value on app load.
	 *
	 * @since ??
	 *
	 * @param array $settings Array of settings data.
	 */
	public static function insert_settings_data_functions_on_app_load( $settings ) {
		return array_merge(
			$settings,
			self::get_settings_data( [ 'usage' => 'app_load' ] )
		);
	}
}
