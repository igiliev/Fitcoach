<?php
/**
 * Security class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Security;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Security\DynamicContent\DynamicContentFixes;
use ET\Builder\Framework\DependencyManagement\DependencyTree;


/**
 * Security Class.
 *
 * This class is responsible for loading all the security functionalities. It accepts
 * a DependencyTree on construction, specifying the dependencies and their priorities for loading.
 *
 * @since ??
 *
 * @param DependencyTree $dependencyTree The dependency tree instance specifying the dependencies and priorities.
 */
class Security {
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
	 * $security = new Security($dependency_tree);
	 * ```
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}

	/**
	 * Loads and initializes all the functionalities related to the Security area.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function initialize() {
		$this->_dependency_tree->load_dependencies();

		// Security Audit - elegantthemes/Divi#41951 .
		add_filter( 'wp_insert_post_data', array( $this, 'sanitize_dynamic_content_fields' ), 10, 2 );
	}

	/**
	 * Sanitize dynamic content on save.
	 *
	 * Check on save post if the user has the unfiltered_html capability,
	 * if they do, we can bail, because they can save whatever they want,
	 * if they don't, we need to strip the enable_html flag from the dynamic content item,
	 * and then re-encode it, and put the new value back in the post content.
	 *
	 * @since ??
	 *
	 * @param array $data  An array of slashed post data.
	 *
	 * @return array $data Modified post data.
	 */
	public function sanitize_dynamic_content_fields( $data ) {
		// Exit early if there's nothing to fix or user has `unfiltered_html` capability.
		if ( strpos( $data['post_content'], 'enable_html' ) === false || current_user_can( 'unfiltered_html' ) ) {
			return $data;
		}

		return DynamicContentFixes::disable_html( $data );
	}
}

// Class doesn't have any dependencies yet but it might in the future.
$dependency_tree = new DependencyTree();

$security = new Security( $dependency_tree );

$security->initialize();
