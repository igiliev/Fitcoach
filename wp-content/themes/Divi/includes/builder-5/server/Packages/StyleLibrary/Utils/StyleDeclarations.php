<?php
/**
 * ModuleStyleLibrary\StyleDeclarations class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * StyleDeclarations class is a helper class with methods to work with the style library.
 *
 * This class is equivalent of JS class:
 * {@link /docs/builder-api/js/style-library/style-declarations} in
 * `@divi/style-library` package.
 *
 * @since ??
 */
class StyleDeclarations {

	/**
	 * This is the type of value that the function will return. Can be either string or key_value_pair.
	 *
	 * @var string
	 */
	private $_return_type;

	/**
	 * A parameter to add !important statement.
	 *
	 * @var bool|array
	 */
	private $_important;

	/**
	 * Declarations data
	 *
	 * @var array
	 */
	private $_declarations = [];

	/**
	 * Create an instance of StyleDeclarations class.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type bool|array $important  Optional. Whether to add `!important` tag. Default `false`.
	 *     @type string     $returnType Optional.
	 *                                  This is the type of value that the function will return.
	 *                                  Can be either `string` or `key_value_pair`. Default `string`.
	 * }
	 */
	public function __construct( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'important'  => false,
				'returnType' => 'string',
			]
		);

		$this->_important   = $args['important'];
		$this->_return_type = $args['returnType'];
	}

	/**
	 * Add declaration's property and value.
	 *
	 * @since ??
	 *
	 * @param string $property The CSS property to add.
	 * @param string $value    The value of the property.
	 *
	 * @return void
	 */
	public function add( string $property, string $value ): void {
		$is_important = is_array( $this->_important )
		? ( isset( $this->_important[ $property ] ) ? $this->_important[ $property ] : false )
		: $this->_important;

		$important_tag = $is_important ? ' !important' : '';

		if ( 'key_value_pair' === $this->_return_type ) {
			$this->_declarations[ $property ] = $value . $important_tag;
		} else {
			$this->_declarations[] = $property . ': ' . $value . $important_tag;
		}
	}

	/**
	 * Get style declaration.
	 *
	 * Returns either array of declarations or string of declarations based on the specified return type.
	 *
	 * @since ??
	 *
	 * @return array|string|null Returns either array of declarations or string of declarations based on the specified return type.
	 */
	public function value() {
		if ( 'key_value_pair' === $this->_return_type ) {
			return $this->_declarations;
		}

		return Utils::join_declarations( $this->_declarations );
	}
}
