<?php
/**
 * ThemeBuilder: Theme Builder Class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\DependencyTree;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\ThemeBuilder\Admin;

/**
 * ThemeBuilder class.
 *
 * This class initializes Theme Builder. Most of theme builder functionalities remains at Divi 4's `includes/builder`
 * directory. What is defined here is the mechanism on Divi 5 that is required to make the theme builder works.
 *
 * @since ??
 */
class ThemeBuilder {
	/**
	 * Stores dependencies that were passed to constructor.
	 *
	 * @since ??
	 *
	 * @var DependencyTree Dependency tree for VisualBuilder to load.
	 */
	private $_dependency_tree;

	/**
	 * Create an instance of the VisualBuilder class.
	 *
	 * Constructs class and sets dependencies for `VisualBuilder` to load.
	 *
	 * @since ??
	 *
	 * @param DependencyTree $dependency_tree Dependency tree for VisualBuilder to load.
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}

	/**
	 * Initialize Theme Builder.
	 *
	 * @since ??
	 */
	public function initialize() {
		$this->_dependency_tree->load_dependencies();

		add_action( 'et_theme_builder_begin_layout', [ self::class, 'set_theme_builder_location' ] );
		add_action( 'et_theme_builder_end_layout', [ self::class, 'reset_theme_builder_location' ] );
	}

	/**
	 * Set theme builder location before theme builder layout is being rendered.
	 *
	 * @since ??
	 *
	 * @param array $theme_builder_layout Theme builder layout settings.
	 */
	public static function set_theme_builder_location( $theme_builder_layout ) {
		BlockParserStore::set_layout( $theme_builder_layout );
	}

	/**
	 * Reset theme builder location after theme builder layout is being rendered into `default`.
	 *
	 * @since ??
	 *
	 * @param array $theme_builder_layout Theme builder layout settings.
	 */
	public static function reset_theme_builder_location( $theme_builder_layout ) {
		BlockParserStore::reset_layout( $theme_builder_layout );
	}
}

$dependency_tree = new DependencyTree();
$dependency_tree->add_dependency( new Admin() );

$theme_builder = new ThemeBuilder( $dependency_tree );
$theme_builder->initialize();
