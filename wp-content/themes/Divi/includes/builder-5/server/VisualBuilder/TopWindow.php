<?php
/**
 * Visual Builder's Top Window Class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;

/**
 * Class for handling Visual Builder's Top Window rendering.
 *
 * @since ??
 */
class TopWindow implements DependencyInterface {

	/**
	 * Load the class.
	 *
	 * Mandatory method for loading the class for class that implements DependencyInterface.
	 *
	 * @since ??
	 */
	public function load() {
		add_filter( 'template_include', [ self::class, 'include' ] );
	}

	/**
	 * Use top window's page template for rendering visual builder's top window document.
	 *
	 * @since ??
	 *
	 * @param string $template Template file path.
	 *
	 * @return string
	 */
	public static function include( $template ) {
		if ( Conditions::is_vb_top_window() ) {
			return ET_BUILDER_5_DIR . 'server/templates/visual-builder-top-window.php';
		}

		return $template;
	}
}
