<?php
/**
 * Classnames class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;

/**
 * Class for populating classnames.
 *
 * This class is equivalent of JS function:
 * {@link /docs/builder-api/js-beta/divi-module/functions/Classnames Classnames} in
 * `@divi/module` package.
 *
 * @since ??
 */
class Classnames {

	/**
	 * Classnames data placeholder.
	 *
	 * @var array
	 */
	private $_values = [];

	/**
	 * Create an instance of Classnames.
	 *
	 * @since ??
	 *
	 * @param array $initial_classnames Optional. Classnames collection that will be defined as initial values.
	 *                                  Default `[]`.
	 */
	public function __construct( array $initial_classnames = [] ) {
		foreach ( $initial_classnames as $key => $value ) {
			if ( is_string( $key ) && is_bool( $value ) ) {
				$this->_values[ $key ] = $value;
			}
		}
	}

	/**
	 * Add class name into classname object instance.
	 *
	 * @since ??
	 *
	 * @param string|array $classname       Class name to be added.
	 * @param bool         $should_be_added Optional. Whether the given classname should be added or not.
	 *                                      Default `true`.
	 *
	 * @return void
	 */
	public function add( $classname, bool $should_be_added = true ): void {
		if ( true === $should_be_added ) {
			if ( is_string( $classname ) && $classname ) {
				$this->_values[ $classname ] = true;
			} elseif ( is_array( $classname ) ) {
				foreach ( $classname as $key => $value ) {
					if ( is_numeric( $key ) && $value ) {
						$this->_values[ $value ] = true;
					}
				}

				foreach ( $classname as $key => $value ) {
					if ( is_string( $key ) && is_bool( $value ) ) {
						$this->_values[ $key ] = $value;
					}
				}
			}
		}
	}

	/**
	 * Remove class name from classname instance.
	 *
	 * This function explicity checks if the provided class bane is a string.
	 * If the provided class name is not a string, the function will return immediately.
	 *
	 * @since ??
	 *
	 * @param string $classname Class name to remove.
	 *
	 * @return void
	 */
	public function remove( string $classname ): void {
		if ( is_string( $classname ) ) {
			unset( $this->_values[ $classname ] );
		}
	}

	/**
	 * Get the value of the classnames.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function value(): string {
		return HTMLUtility::classnames( $this->_values );
	}
}
