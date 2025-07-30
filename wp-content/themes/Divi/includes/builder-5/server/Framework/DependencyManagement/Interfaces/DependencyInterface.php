<?php
/**
 * DependencyManagement: DependencyInterface
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\DependencyManagement\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

interface DependencyInterface {
	/**
	 * This function registers and initiates all the logic the class implements.
	 *
	 * @return void
	 */
	public function load();
}
