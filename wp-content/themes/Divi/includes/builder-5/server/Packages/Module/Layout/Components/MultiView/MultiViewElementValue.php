<?php
/**
 * Module: MultiViewElementValue class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * Module: MultiViewElementValue class.
 *
 * The MultiViewElementValue class is a helper class that provides methods to get, set, and manipulate
 * data related to a formatted breakpoint and state array, selector, hover selector, and value resolver
 * that will be used to render an element in MultiViewElement class.
 *
 * @since ??
 */
class MultiViewElementValue {

	/**
	 * A formatted breakpoint and state array.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_data;

	/**
	 * Module attribute sub name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_sub_name;

	/**
	 * The selector of element to be updated.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_selector;

	/**
	 * The selector to trigger hover event.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_hover_selector;

	/**
	 * A function that will be invoked to resolve the value. Default is `null`.
	 *
	 * @since ??
	 *
	 * @var callable
	 */
	private $_value_resolver;

	/**
	 * Create an instance of MultiViewElement class.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Module attribute sub name.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 * }
	 */
	public function __construct( array $args ) {
		$this->_set_properties( $args );
	}

	/**
	 * Create a new instance of the MultiViewElementValue class with the given arguments.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Module attribute sub name.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 * }
	 *
	 * @return MultiViewElementValue A new instance of the MultiViewElementValue class.
	 */
	public static function create( array $args ): MultiViewElementValue {
		return new MultiViewElementValue( $args );
	}

	/**
	 * Get the formatted breakpoint and state.
	 *
	 * @since ??
	 *
	 * @return array The formatted breakpoint and state.
	 */
	public function get_data(): array {
		return $this->_data;
	}

	/**
	 * Set the formatted breakpoint and state.
	 *
	 * @since ??
	 *
	 * @param array $data The formatted breakpoint and state.
	 */
	public function set_data( array $data ): void {
		$this->_data = $data;
	}

	/**
	 * Get the selector of the element to be updated.
	 *
	 * @since ??
	 *
	 * @return string|null The selector of the element to be updated.
	 */
	public function get_selector(): ?string {
		return $this->_selector;
	}

	/**
	 * Set the selector of the element to be updated.
	 *
	 * @since ??
	 *
	 * @param string $selector The selector of the element to be updated.
	 */
	public function set_selector( string $selector ): void {
		$this->_selector = $selector;
	}

	/**
	 * Get the selector used to trigger hover event.
	 *
	 * @since ??
	 *
	 * @return string|null The value of the selector used to trigger hover event.
	 */
	public function get_hover_selector(): ?string {
		return $this->_hover_selector;
	}

	/**
	 * Set the selector used to trigger hover event.
	 *
	 * @since ??
	 *
	 * @param string $hover_selector The selector used to trigger hover event.
	 */
	public function set_hover_selector( string $hover_selector ): void {
		$this->_hover_selector = $hover_selector;
	}

	/**
	 * Get the module attribute sub name.
	 *
	 * @since ??
	 *
	 * @return string|array The module attribute sub name.
	 */
	public function get_sub_name() {
		return $this->_sub_name;
	}

	/**
	 * Set the module attribute sub name.
	 *
	 * @since ??
	 *
	 * @param string $sub_name The module attribute sub name.
	 */
	public function set_sub_name( string $sub_name ): void {
		$this->_sub_name = $sub_name;
	}

	/**
	 * Get the function that will be invoked to resolve the value.
	 *
	 * @since ??
	 *
	 * @return callable|null The function that will be invoked to resolve the value.
	 */
	public function get_value_resolver(): ?callable {
		return $this->_value_resolver;
	}

	/**
	 * Set the function that will be invoked to resolve the value.
	 *
	 * @since ??
	 *
	 * @param callable $value_resolver The function that will be invoked to resolve the value.
	 */
	public function set_value_resolver( callable $value_resolver ): void {
		$this->_value_resolver = $value_resolver;
	}

	/**
	 * Set the value of the instance.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *    An array of arguments.
	 *
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Module attribute sub name.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 * }
	 * @param bool  $create_new_instance Optional. Whether to create a new instance or not. Default `true`.
	 *
	 * @return MultiViewElementValue A new instance of MultiViewElementValue or the same instance if `$create_new_instance` is `false`.
	 */
	public function set( array $args, bool $create_new_instance = true ): MultiViewElementValue {
		if ( $create_new_instance ) {
			return self::create(
				[
					'data'          => $args['data'] ?? $this->get_data(),
					'subName'       => $args['subName'] ?? $this->get_sub_name(),
					'valueResolver' => $args['valueResolver'] ?? $this->get_value_resolver(),
					'selector'      => $args['selector'] ?? $this->get_selector(),
					'hoverSelector' => $args['hoverSelector'] ?? $this->get_hover_selector(),
				]
			);
		}

		$this->_set_properties( $args );

		return $this;
	}

	/**
	 * Checks if a given attribute has a value across breakpoints and states.
	 *
	 * This method is a wrapper for the `ModuleUtils::has_value()` method.
	 *
	 * @since ??
	 *
	 * @param array $options {
	 *     Optional. An array of options.
	 *
	 *     @type string   $breakpoint    Optional. The breakpoint to check for values. Default `null`.
	 *     @type string   $state         Optional. The state to check for values. Default `null`.
	 *     @type string   $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable $valueResolver Optional. A callback function to resolve the value. Default `null`.
	 *     @type string   $inheritedMode Optional. The mode how is the attribute value will be inherited. Default null.
	 * }
	 *
	 * @return bool Whether the given attribute has a value or not.
	 */
	public function has_value( array $options = [] ):bool {
		return ModuleUtils::has_value(
			$this->get_data(),
			array_merge(
				$options,
				[
					'subName'       => $options['subName'] ?? $this->get_sub_name(),
					'valueResolver' => $options['valueResolver'] ?? $this->get_value_resolver(),
				]
			)
		);
	}

	/**
	 * Sets properties based on an array of arguments using a mapping array.
	 *
	 * This internal method is used to set the instance properties with a correct type.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *    An array of arguments.
	 *
	 *     @type array    $data          A formatted breakpoint and state array.
	 *     @type string   $subName       Module attribute sub name.
	 *     @type callable $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string   $selector      The selector of element to be updated.
	 *     @type string   $hoverSelector Optional. The selector to trigger hover event. Default `{{selector}}`.
	 * }
	 *
	 * @return void
	 */
	private function _set_properties( array $args ): void {
		$mapping = [
			'data'          => 'set_data',
			'subName'       => 'set_sub_name',
			'valueResolver' => 'set_value_resolver',
			'selector'      => 'set_selector',
			'hoverSelector' => 'set_hover_selector',
		];

		foreach ( $mapping as $key => $method ) {
			if ( isset( $args[ $key ] ) ) {
				call_user_func( [ $this, $method ], $args[ $key ] );
			}
		}
	}
}
