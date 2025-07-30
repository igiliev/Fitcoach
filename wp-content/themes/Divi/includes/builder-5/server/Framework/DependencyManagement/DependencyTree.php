<?php
/**
 * DependencyManagement: Dependency tree class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\DependencyManagement;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;

/**
 * `DependencyTree` class is used as a utility to manage loading classes in a meaningful manner.
 *
 * Any class passed to `DependencyTree` should implement `DependencyInterface`.
 *
 * @since ??
 */
class DependencyTree {

	/**
	 * Stores dependencies that was passed to constructor.
	 *
	 * @var array
	 *
	 * @since ??
	 */
	private $_dependency_tree = [];

	/**
	 * Add a new dependency to the dependency tree.
	 *
	 * @param DependencyInterface $dependency Dependency class.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * $dependency_tree = new DependencyTree();
	 *
	 * $dependency_tree->add_dependency( new DynamicContentOptionProductTitle() );
	 * $dependency_tree->add_dependency( new DynamicContentOptionPostTitle() );
	 * $dependency_tree->add_dependency( new DynamicContentOptionPostExcerpt() );
	 *
	 * // ... Add more dependencies ...
	 * ```
	 */
	public function add_dependency( DependencyInterface $dependency ): void {
		$this->_dependency_tree[] = $dependency;
	}

	/**
	 * Loads all the dependencies registered in the dependency tree.
	 *
	 * This function iterates through the dependency tree and loads each dependency
	 * by calling the `load()` method on each dependency object.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * $dependency_tree = new DependencyTree();
	 *
	 * $dependency_tree->add_dependency( new DynamicContentOptionProductTitle() );
	 * $dependency_tree->add_dependency( new DynamicContentOptionPostTitle() );
	 * $dependency_tree->add_dependency( new DynamicContentOptionPostExcerpt() );
	 *
	 * // ... Add more dependencies ...
	 *
	 * $dependency_tree->load_dependencies();
	 * ```
	 */
	public function load_dependencies(): void {
		foreach ( $this->_dependency_tree as $dependency ) {
			$dependency->load();
		}
	}

}
