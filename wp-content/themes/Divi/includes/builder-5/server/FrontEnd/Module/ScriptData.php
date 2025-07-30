<?php
/**
 * Frontend Script Data
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\FrontEnd\Module;

use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewAssets;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Frontend Script Data Class.
 *
 * The ScriptData class is used to manage and manipulate script data. It provides methods to add,
 * enqueue, retrieve, and reset script data items, as well as get information about the script.
 *
 * @since ??
 */
class ScriptData {

	/**
	 * Get the script data properties for the module.
	 *
	 * Retrieves an array of script data properties for the current module. These properties can be
	 * used to pass data from the server to the client when enqueuing the module script.
	 *
	 * @since ??
	 *
	 * @var array $_script_data An associative array of script data properties. This array includes keys and values for
	 *                          various properties that will be passed to the module script.
	 *
	 * @return array An associative array of script data properties.
	 *
	 * @example
	 * ```php
	 * $module = new My_Module_Class();
	 * $script_data = $module->get_script_data();
	 * // Returns array( 'property1' => 'value1', 'property2' => 'value2' )
	 * ```
	 *
	 * @example
	 * ```php
	 * use My\Namespace\My_Module_Class;
	 * $module = new My_Module_Class();
	 * $script_data = $module->get_script_data();
	 * // Returns array( 'property1' => 'value1', 'property2' => 'value2' )
	 * ```
	 *
	 * @example
	 * ```php
	 * use My\Namespace\My_Trait;
	 * class My_Module_Class {
	 *     use My_Trait;
	 * }
	 * $module = new My_Module_Class();
	 * $script_data = $module->get_script_data();
	 * // Returns array( 'property1' => 'value1', 'property2' => 'value2' )
	 * ```
	 */
	private static $_script_data = [
		'scroll'    => [],
		'sticky'    => [],
		'animation' => [],
		'link'      => [],
	];

	/**
	 * Retrieves the mapping of object name to script handle.
	 *
	 * This function returns an associative array that maps script data keys to their corresponding script handles.
	 * The mapping allows easy access to the script handle for a given object name.
	 *
	 * @since ??
	 *
	 * @return array An associative array mapping script data keys to their corresponding script handles.
	 *
	 * @example
	 * ```php
	 * $scriptMapping = get_script_mapping();
	 *
	 * echo $scriptMapping['object_name']; // Retrieves the script handle for the 'object_name' object
	 * ```
	 */
	public static function get_object_name_to_script_handle_mapping(): array {
		return [
			'diviElementScrollData'             => 'et-builder-modules-script-motion',
			'diviElementStickyData'             => 'et-builder-modules-script-sticky',
			'diviElementAnimationData'          => 'divi-script-library-animation',
			'diviElementLinkData'               => 'divi-script-library-link',
			'diviModuleCircleCounterData'       => 'divi-module-library-script-circle-counter',
			'diviModuleContactFormData'         => 'divi-module-library-script-contact-form',
			'diviModuleNumberCounterData'       => 'divi-module-library-script-number-counter',
			'diviModuleSignupData'              => 'divi-module-library-script-signup',
			'diviElementBackgroundParallaxData' => 'divi-module-script-background-parallax',
			'diviElementBackgroundVideoData'    => 'divi-module-script-background-video',
			'diviElementMultiViewData'          => MultiViewAssets::script_handle(),

			// System.
			'diviBreakpointData'                => [
				MultiViewAssets::script_handle(),
				'et-builder-modules-script-motion',
			],

			// Hypothetical.
			'diviScriptData'                    => 'divi-script-library',
		];
	}

	// Internal Slack discussion about this property: https://elegantthemes.slack.com/archives/C01CW343ZJ9/p1667488870252499.
	/**
	 * Map of script data name to its actual script object name.
	 *
	 * This property is used to store a mapping between the script data name and its actual script object name.
	 * In most cases, scripts are still using the object name from the original D4 script. However, as scripts
	 * are refactored, the object names are likely to be changed as well. This mapping allows for easy reference
	 * and retrieval of the correct script object name based on the script data name.
	 *
	 * @since ??
	 *
	 * @var array $_data_name_to_script_object_name_map Map of script data name to its actual script object name. The
	 *                                                  array keys are the script data names, and the values are the
	 *                                                  corresponding script object names.
	 *
	 * @return array The mapping of script data name to script object name.
	 *
	 * @example
	 * ```php
	 * // Create an instance of the ScriptManager class
	 * $script_manager = new ScriptManager();
	 *
	 * // Retrieve the object name for a specific script data name
	 * $object_name = $script_manager->getScriptObjectName('script1');
	 * ```
	 */
	private static $_data_name_to_script_object_name_map = [
		// D4 script object name map.

		// System.
		'breakpoint'          => 'diviBreakpointData',

		// Element Options.
		'animation'           => 'diviElementAnimationData',
		'background_parallax' => 'diviElementBackgroundParallaxData',
		'background_video'    => 'diviElementBackgroundVideoData',
		'link'                => 'diviElementLinkData',
		'scroll'              => 'diviElementScrollData',
		'sticky'              => 'diviElementStickyData',
		'multi_view'          => 'diviElementMultiViewData',

		// Module.
		'circle_counter'      => 'diviModuleCircleCounterData',
		'contact_form'        => 'diviModuleContactFormData',
		'number_counter'      => 'diviModuleNumberCounterData',
		'signup'              => 'diviModuleSignupData',
	];

	/**
	 * Add data item to the script data.
	 *
	 * This function adds a data item to the script data array. The script data array stores information
	 * related to various modules and actions performed on them.
	 *
	 * The data item includes the action, module ID, module name, selector, hover selector, and data
	 * for the different viewports. The data item ID is set to null, so the item will be appended as
	 * a zero-indexed array.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for adding the data item.
	 *
	 *     @type string $data_name    The name of the data. Data can have multiple data items associated with it.
	 *     @type string $data_item_id The identifier for the data item.
	 *     @type array  $data_item    The data item to be added.
	 * }
	 *
	 * @return boolean Returns true if the data item was successfully added to the script data array, false otherwise.
	 *
	 * @example: Adding a data item to `multi_view` in the script data array.
	 * ```php
	 * self::add_data_item(array(
	 *     'data_name'    => 'multi_view',
	 *     'data_item_id' => null,
	 *     'data_item'    => array(
	 *         'action'        => 'setAttrs',
	 *         'moduleId'      => 'divi/cta-0',
	 *         'moduleName'    => 'CTA',
	 *         'selector'      => '.et_pb_cta_0',
	 *         'hoverSelector' => '.et_pb_cta_0_hover',
	 *         'data'          => array(
	 *             'desktop'        => array(
	 *                 'src' => 'http://example.com/desktop.jpg',
	 *                 'alt' => 'Desktop Image',
	 *             ),
	 *             'tablet'         => array(
	 *                 'src' => 'http://example.com/tablet.jpg',
	 *                 'alt' => 'Tablet Image',
	 *             ),
	 *             'phone'          => array(
	 *                 'src' => 'http://example.com/phone.jpg',
	 *                 'alt' => 'Phone Image',
	 *             ),
	 *         ),
	 *     ),
	 * ));
	 * ```
	 */
	public static function add_data_item( array $args ): bool {
		$args = wp_parse_args(
			$args,
			[
				'data_name'    => '',
				'data_item_id' => null,
				'data_item'    => [],
			]
		);

		if ( ! isset( self::$_script_data[ $args['data_name'] ] ) ) {
			self::$_script_data[ $args['data_name'] ] = [];
		}

		// TODO feat(D5, FE, Script Data & JS) Refactor this later to make it to have the same data structure with the other script data.
		// When the data_item_id is null, then append the the as zero indexed array. The use case of this scenario is for the animation script data.
		if ( null === $args['data_item_id'] ) {
			self::$_script_data[ $args['data_name'] ][] = $args['data_item'];
		} else {
			self::$_script_data[ $args['data_name'] ][ $args['data_item_id'] ] = $args['data_item'];
		}

		return isset( self::$_script_data[ $args['data_name'] ] );
	}

	/**
	 * Enqueue data as an object into the assigned script.
	 *
	 * This function is used to enqueue a data object into the assigned script. The data object is used
	 * to provide dynamic data to the script during execution.
	 *
	 * @since ??
	 *
	 * @param string $data_name The name of the data object to enqueue.
	 *
	 * @throws \Exception When the data object is not found.
	 *
	 * @return void
	 *
	 * @example: Enqueue script data
	 * ```php
	 * MyScript::enqueue_data( 'my_data' );
	 * ```
	 *
	 * @example: Enqueue script data at the footer using a class method
	 * ```php
	 * MyClass::enqueue_script_at_footer();
	 * ```
	 *
	 * @example: Enqueue fonts in the footer
	 * ```php
	 * enqueue_fonts_in_footer();
	 * ```
	 */
	public static function enqueue_data( string $data_name ): void {
		$info = self::get_script_info( $data_name );
		$data = self::get_data( $data_name );

		if ( empty( $data ) ) {
			return;
		}

		// Value and object name is being used by one script handle.
		$is_single_script_name = is_string( $info['object_name'] ) && is_string( $info['script_name'] );

		// Value and object name is being used by multiple script handles.
		$is_multiple_script_names = is_string( $info['object_name'] ) && is_array( $info['script_name'] );

		if ( $is_single_script_name ) {

			// Enqueue script if it's registered and not enqueued.
			if ( wp_script_is( $info['script_name'], 'registered' ) && ! wp_script_is( $info['script_name'], 'enqueued' ) ) {
				wp_enqueue_script( $info['script_name'] );
			}

			wp_localize_script(
				$info['script_name'],
				$info['object_name'],
				self::get_data( $data_name )
			);

		} elseif ( $is_multiple_script_names ) {
			foreach ( $info['script_name'] as $script_name ) {
				if ( wp_script_is( $script_name, 'registered' ) && ! wp_script_is( $script_name, 'enqueued' ) ) {
					wp_enqueue_script( $script_name );
				}

				wp_localize_script(
					$script_name,
					$info['object_name'],
					self::get_data( $data_name )
				);
			}
		}
	}

	/**
	 * Retrieves a specific data item based on the given data name and data item identifier.
	 *
	 * This function is used to retrieve a specific data item from the $_script_data array, which
	 * stores data items for different data names.
	 *
	 * If the specified data item is found, it will be stored in the $data_item variable. Otherwise,
	 * an empty array will be returned.
	 *
	 * @since ??
	 *
	 * @param string $data_name    The name of the data.
	 * @param string $data_item_id The identifier of the data item.
	 *
	 * @return array The retrieved data item. If the data item is not found, an empty array is returned.
	 *
	 * @example: Retrieve data item
	 * ```php
	 * $data_name = 'link';
	 * $data_item_id = 'divi/cta-0';
	 * $data_item = self::get_data_item($data_name, $data_item_id);
	 * ```
	 */
	public static function get_data_item( string $data_name, string $data_item_id ): array {
		return self::$_script_data[ $data_name ][ $data_item_id ] ?? [];
	}

	/**
	 * Get data collection based on the given data name.
	 *
	 * This function retrieves the entire data collection (an array of data items) associated with the
	 * given data name. The data collection is used to provide dynamic data to the script during execution.
	 *
	 * @since ??
	 *
	 * @param string $data_name The name of the data collection to retrieve.
	 *
	 * @throws \Exception When the data collection is not found.
	 *
	 * @return array The retrieved data collection. Returns an empty array if the data collection is not found.
	 *
	 * @example: Get data collection
	 * ```php
	 * $data = self::get_data( 'my_data' );
	 * ```
	 */
	public static function get_data( string $data_name ): array {
		return self::$_script_data[ $data_name ] ?? [];
	}

	/**
	 * Get script info related to the given data name.
	 *
	 * This function retrieves the relevant information about a script assigned to a data object.
	 * The data object provides dynamic data to the script during execution. The script handle may
	 * not have the same name as the data name, hence this function returns the script and object
	 * names associated with the data name.
	 *
	 * @since ??
	 *
	 * @param string $data_name The name of the data object.
	 *
	 * @throws \Exception When the data object is not found.
	 *
	 * @return array Associative array containing 'object_name' and 'script_name'.
	 *
	 * @example
	 * ```php
	 * MyNamespace\MyClass::get_script_info( 'my_data' );
	 * // Returns: ['object_name' => 'object_name_value', 'script_name' => 'script_name_value']
	 * ```
	 */
	public static function get_script_info( string $data_name ): array {
		$object_name_to_script_handle_mapping = self::get_object_name_to_script_handle_mapping();
		$object_name                          = self::$_data_name_to_script_object_name_map[ $data_name ] ?? false;

		$script_name = is_string( $object_name ) && isset( $object_name_to_script_handle_mapping[ $object_name ] )
			? $object_name_to_script_handle_mapping[ $object_name ]
			: false;

		return [
			'object_name' => $object_name,
			'script_name' => $script_name,
		];
	}

	/**
	 * Reset the state of the script data property.
	 *
	 * This function resets the state of the script data property to its initial state. The script
	 * data property is used to store relevant data for script execution.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * resetScriptData();
	 * ```
	 */
	public static function reset(): void {
		self::$_script_data = [];
	}

}
