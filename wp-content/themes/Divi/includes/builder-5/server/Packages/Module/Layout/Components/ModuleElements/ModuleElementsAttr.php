<?php
/**
 * Element Inner Content Attr Class
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Layout\Components\ModuleElements;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use InvalidArgumentException;

/**
 * Module related helper class.
 *
 * @since ??
 */
class ModuleElementsAttr {

	/**
	 * Module attribute name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_attr_name;

	/**
	 * Module formatted attribute array.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_attr;

	/**
	 * Module attribute sub name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_sub_name;

	/**
	 * A function that will be invoked to resolve the value.
	 *
	 * @since ??
	 *
	 * @var callable
	 */
	private $_value_resolver;

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
	 * Create an instance of ModuleElementsAttr class.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *    An array of arguments.
	 *
	 *     @type string       $attrName      The module attribute name. Optional when `attr` is defined.
	 *     @type array        $attr          The module formatted attribute array. Optional when `attrName` is defined.
	 *     @type string       $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable     $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string       $selector      Optional. The selector of element to be updated. Default `null`.
	 *     @type string       $hoverSelector Optional. The selector to trigger hover event. Default `null`.
	 * }
	 */
	public function __construct( array $args ) {
		$this->_set_properties( $args );
		$this->_validate();
	}

	/**
	 * Creates a new instance of the ModuleElementsAttr class with the given arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *    An array of arguments.
	 *
	 *     @type string       $attrName      The module attribute name. Optional when `attr` is defined.
	 *     @type array        $attr          The module formatted attribute array. Optional when `attrName` is defined.
	 *     @type string       $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable     $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string       $selector      Optional. The selector of element to be updated. Default `null`.
	 *     @type string       $hoverSelector Optional. The selector to trigger hover event. Default `null`.
	 * }
	 *
	 * @return ModuleElementsAttr A new instance of the ModuleElementsAttr class.
	 */
	public static function create( array $args ): ModuleElementsAttr {
		return new ModuleElementsAttr( $args );
	}

	/**
	 * Get the module attribute name.
	 *
	 * @since ??
	 *
	 * @return string|null The module attribute name.
	 */
	public function get_attr_name(): ?string {
		return $this->_attr_name;
	}

	/**
	 * Get the module formatted attribute.
	 *
	 * @since ??
	 *
	 * @return array The module formatted attribute.
	 */
	public function get_attr(): array {
		return $this->_attr;
	}

	/**
	 * Get the module attribute sub name.
	 *
	 * @since ??
	 *
	 * @return string|null The module attribute sub name.
	 */
	public function get_sub_name(): ?string {
		return $this->_sub_name;
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
	 * Get the selector of element to be updated.
	 *
	 * @since ??
	 *
	 * @return string|null The selector of element to be updated.
	 */
	public function get_selector(): ?string {
		return $this->_selector;
	}

	/**
	 * Get the selector to trigger hover event.
	 *
	 * @since ??
	 *
	 * @return string|null The selector to trigger hover event.
	 */
	public function get_hover_selector(): ?string {
		return $this->_hover_selector;
	}

	/**
	 * Set the module attribute name.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The module attribute name.
	 *
	 * @return void
	 */
	public function set_attr_name( string $attr_name ): void {
		$this->_attr_name = $attr_name;
	}

	/**
	 * Set the module formatted attribute array.
	 *
	 * @since ??
	 *
	 * @param array $attr The module formatted attribute array.
	 *
	 * @return void
	 */
	public function set_attr( array $attr ): void {
		$this->_attr = $attr;
	}

	/**
	 * Set the module attribute sub name.
	 *
	 * @since ??
	 *
	 * @param string $sub_name The module attribute sub name.
	 *
	 * @return void
	 */
	public function set_sub_name( string $sub_name ): void {
		$this->_sub_name = $sub_name;
	}

	/**
	 * Set the function that will be invoked to resolve the value.
	 *
	 * @since ??
	 *
	 * @param callable $value_resolver The function that will be invoked to resolve the value.
	 *
	 * @return void
	 */
	public function set_value_resolver( callable $value_resolver ): void {
		$this->_value_resolver = $value_resolver;
	}

	/**
	 * Set the selector of element to be updated.
	 *
	 * @since ??
	 *
	 * @param string $selector The selector of element to be updated.
	 *
	 * @return void
	 */
	public function set_selector( string $selector ): void {
		$this->_selector = $selector;
	}

	/**
	 * Set the selector to trigger hover event.
	 *
	 * @since ??
	 *
	 * @param string $hover_selector The selector to trigger hover event.
	 *
	 * @return void
	 */
	public function set_hover_selector( string $hover_selector ): void {
		$this->_hover_selector = $hover_selector;
	}

	/**
	 * Set the value of the instance properties.
	 *
	 * @since ??
	 *
	 * @param array $args                The arguments to set the value.
	 * @param bool  $create_new_instance Optional. Whether to create a new instance or not. Default `true`.
	 *
	 * @return ModuleElementsAttr A new instance, or the same instance if `$create_new_instance` is `false`.
	 */
	public function set( array $args, $create_new_instance = true ): ModuleElementsAttr {
		if ( $create_new_instance ) {
			return self::create(
				[
					'attrName'      => $args['attrName'] ?? $this->_attr_name,
					'attr'          => $args['attr'] ?? $this->_attr,
					'subName'       => $args['subName'] ?? $this->_sub_name,
					'valueResolver' => $args['valueResolver'] ?? $this->_value_resolver,
					'selector'      => $args['selector'] ?? $this->_selector,
					'hoverSelector' => $args['hoverSelector'] ?? $this->_hover_selector,
				]
			);
		}

		$this->_set_properties( $args );
		$this->_validate();

		return $this;
	}

	/**
	 * Validate the instance properties.
	 *
	 * @since ??
	 *
	 * @throws InvalidArgumentException If both the `$this->attr` and `$this->attrName` parameters are defined.
	 *
	 * @return void.
	 */
	private function _validate(): void {
		if ( null !== $this->_attr_name && null !== $this->_attr ) {
			throw new InvalidArgumentException( 'Both the `attr` and `attrName` parameters cannot be defined simultaneously.' );
		}
	}

	/**
	 * Set properties based on an array of arguments using a mapping array.
	 *
	 * This internal method is used to set the instance properties with a correct type.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *    An array of arguments.
	 *
	 *     @type string       $attrName      The module attribute name. Optional when `attr` is defined.
	 *     @type array        $attr          The module formatted attribute array. Optional when `attrName` is defined.
	 *     @type string       $subName       Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable     $valueResolver Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string       $selector      Optional. The selector of element to be updated. Default `null`.
	 *     @type string       $hoverSelector Optional. The selector to trigger hover event. Default `null`.
	 * }
	 */
	private function _set_properties( array $args ): void {
		$mapping = [
			'attrName'      => 'set_attr_name',
			'attr'          => 'set_attr',
			'subName'       => 'set_sub_name',
			'valueResolver' => 'set_value_resolver',
			'selector'      => 'set_selector',
			'hoverSelector' => 'set_hover_selector',
		];

		foreach ( $mapping as $key => $method ) {
			if ( isset( $args[ $key ] ) ) {
				call_user_func( [ $this, $method ], $args[ $key ], false );
			}
		}
	}
}
