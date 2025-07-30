<?php
/**
 * Admin: DiviExtensions init.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Admin\DiviExtensions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\DependencyManagement\DependencyTree;
use ET\Builder\Framework\Utility\Conditions;


/**
 * Admin's DiviExtensions initialize class.
 *
 * This class is responsible for initializing DiviExtensions class on the admin area. It accepts
 * a DependencyTree on construction, specifying the dependencies and their priorities for loading.
 *
 * @since ??
 *
 * @param DependencyTree $dependencyTree The dependency tree instance specifying the dependencies and priorities.
 */
class DiviExtensions implements DependencyInterface {

	/**
	 * Initialize DiviExtensions class.
	 *
	 * @since ??
	 */
	public function load() {
		// Need to be loaded this early or else some 3P plugins will break.
		if ( Conditions::has_divi_4_only_extension() ) {
			require_once get_template_directory() . '/includes/builder/api/DiviExtensions.php';
		}
	}
}
