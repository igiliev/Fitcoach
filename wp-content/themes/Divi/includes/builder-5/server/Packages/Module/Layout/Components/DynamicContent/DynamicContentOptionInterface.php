<?php
/**
 * Module: DynamicContentOptionInterface interface.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

interface DynamicContentOptionInterface {

	/**
	 * Get the name of the option.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Get the label of the option.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_label(): string;
}
