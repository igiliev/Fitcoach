<?php
/**
 * Module: MultiViewInfo class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: MultiViewInfo class.
 *
 * @since ??
 */
class MultiViewInfo {

	/**
	 * The data array.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_data = [];

	/**
	 * Constructor.
	 *
	 * @param array $args The data array.
	 *
	 * @return void
	 */
	public function __construct( array $args ) {
		$this->_data = $args;
	}

	/**
	 * Get the mapping.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function mapping():array {
		return $this->_data['mapping'];
	}

	/**
	 * Get the default breakpoint.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function default_breakpoint():string {
		return $this->_data['defaultBreakpoint'];
	}

	/**
	 * Get the default state.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function default_state():string {
		return $this->_data['defaultState'];
	}

}
