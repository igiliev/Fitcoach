<?php
/**
 * Admin class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\DependencyTree;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Admin\WooCommerce\WooCommerce;
use ET\Builder\Admin\DiviExtensions\DiviExtensions;


/**
 * Admin Class.
 *
 * This class is responsible for loading all the related functionalities on the admin area. It accepts
 * a DependencyTree on construction, specifying the dependencies and their priorities for loading.
 *
 * @since ??
 *
 * @param DependencyTree $dependencyTree The dependency tree instance specifying the dependencies and priorities.
 */
class Admin {
	/**
	 * Stores the dependencies that were passed to the constructor.
	 *
	 * This property holds an instance of the DependencyTree class that represents the dependencies
	 * passed to the constructor of the current object.
	 *
	 * @since ??
	 *
	 * @var DependencyTree $dependencies An instance of DependencyTree representing the dependencies.
	 */
	private $_dependency_tree;

	/**
	 * Constructs a new instance of the class and sets its dependencies.
	 *
	 * @param DependencyTree $dependency_tree The dependency tree for the class to load.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $dependency_tree = new DependencyTree();
	 * $admin = new Admin($dependency_tree);
	 * ```
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}

	/**
	 * Loads and initializes all the functionalities related to the Admin area.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function initialize(): void {
		/**
		* Note: The "Admin" class is only loaded on WP admin area, for more info please checkout the bootstrap.php.
		*/
		if ( ! Conditions::is_d5_enabled() ) {
			return;
		}

		$this->_dependency_tree->load_dependencies();
	}
}

$dependency_tree = new DependencyTree();

$dependency_tree->add_dependency( new WooCommerce() );
$dependency_tree->add_dependency( new DiviExtensions() );

$admin = new Admin( $dependency_tree );

$admin->initialize();
