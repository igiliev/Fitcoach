<?php
/**
 * PackageBuild class.
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\VisualBuilder\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class for handling visual builder's package build.
 *
 * Divi 5's visual builder is organized as monorepo. This means instead of one big bundle file, visual builder
 * is organized as multiple packages and being orchestrated together. These package are bundled into mainly a
 * javascript style, PHP array file containing build version and dependency, and sometimes, static css style.
 * This package's build output is what is being referred as `PackageBuild`.
 *
 * This class is responsible for handling PackageBuild as a single PackageBuild entity.
 *
 * @since ??
 */
class PackageBuild {
	/**
	 * Package build's name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Package build's version.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Package build's script settings.
	 *
	 * @var array
	 */
	public $script;

	/**
	 * Package build's style settings.
	 *
	 * @var array
	 */
	public $style;

	/**
	 * Package build's constructor.
	 *
	 * @since ??
	 *
	 * @param array $params Package build's constructor params.
	 */
	public function __construct( $params ) {
		$this->set_properties( $params );
	}

	/**
	 * Set package build's properties.
	 *
	 * @since ??
	 *
	 * @param array $params Package build's params.
	 */
	public function set_properties( $params ) {
		$default = $this->get_default_properties();

		// Set name.
		$this->name = $params['name'] ?? $default['name'];

		// Make params filterable.
		$params = apply_filters( 'divi_visual_builder_package_build_params_' . $this->name, $params );

		// Set version.
		$this->version = $params['version'] ?? $default['version'];

		// Set script settings.
		$this->script = array_merge(
			$default['script'],
			$params['script'] ?? []
		);

		// Set style settings.
		$this->style = array_merge(
			$default['style'],
			$params['style'] ?? []
		);
	}

	/**
	 * Get default properties.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_default_properties() {
		return [
			'name'    => '',
			'version' => '',
			'script'  => [
				'src'                => '',
				'args'               => [],
				'deps'               => [],
				'data_top_window'    => [],
				'data_app_window'    => [],
				'enqueue_top_window' => true,
				'enqueue_app_window' => true,
			],
			'style'   => [
				'enqueue_top_window' => true,
				'enqueue_app_window' => true,
				'defer'              => false,
				'src'                => '',
				'args'               => [],
				'media'              => 'all',
				'deps'               => [],
			],
		];
	}

	/**
	 * Get package build's properties.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_properties() {
		return [
			'name'    => $this->name,
			'version' => $this->version,
			'script'  => $this->script,
			'style'   => $this->style,
		];
	}
}
