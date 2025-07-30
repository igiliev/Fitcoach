<?php
/**
 * Declarations::transform_declaration()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transform\TransformTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait ValueTrait {

	/**
	 * Parse and return the percentage value as a decimal.
	 *
	 * This function takes a string representation of a percentage value and returns the corresponding decimal value.
	 * If the input is already a decimal value, it will be returned as is.
	 *
	 * @since ??
	 *
	 * @param string $percentage_value The input percentage value.
	 *
	 * @return string The decimal representation of the percentage value.
	 *
	 * @example:
	 * ```php
	 *     // Convert a percentage value to decimal
	 *     $percentage_value = '50%';
	 *     $decimal_value = Transform::render_percentage($percentage_value);
	 *     // $decimal_value will be 0.5
	 *
	 *     // Do not convert if the input is already a decimal
	 *     $percentage_value = '0.5';
	 *     $decimal_value = Transform::render_percentage($percentage_value);
	 *     // $decimal_value will be '0.5'
	 * ```
	 */
	public static function render_percentage( string $percentage_value ): string {
		if ( ( floatval( $percentage_value ) . substr( $percentage_value, -1 ) ) === $percentage_value ) {
			return floatval( $percentage_value ) / 100;
		}

		return $percentage_value;
	}

	/**
	 * Get the CSS transform declaration based on the given attribute values.
	 *
	 * This function takes an array of attribute values and constructs the CSS transform declaration based on those values.
	 * The attribute values include scale, translate, rotate, and skew. Each of these values can have different coordinate properties such as x and y.
	 *
	 * @param array  $attr_value {
	 *     An array of attribute values. The value (breakpoint > state > value) of module attribute.
	 *
	 *     @type array $scale     An array of scale values with x and y coordinates.
	 *     @type array $translate An array of translate values with x and y coordinates.
	 *     @type array $rotate    An array of rotate values with x, y, and z coordinates.
	 *     @type array $skew      An array of skew values with x and y coordinates.
	 * }
	 *
	 * @param string $return_type Optional. This is the type of value that the function will return.
	 *                            Can be either `string` or `key_value_pair`. Default `string`.
	 *
	 * @return array|string The CSS transform declaration constructed from the attribute values.
	 *
	 * @since ??
	 *
	 * @example:
	 * ```php
	 * $attr_value = [
	 *     'scale' => [
	 *         'x' => 1.5,
	 *         'y' => 1.2,
	 *     ],
	 *     'translate' => [
	 *         'x' => 10,
	 *         'y' => -5,
	 *     ],
	 *     'rotate' => [
	 *         'x' => 45,
	 *         'y' => -30,
	 *         'z' => 180,
	 *     ],
	 *     'skew' => [
	 *         'x' => 10,
	 *         'y' => -5,
	 *     ],
	 * ];
	 * $transform_declaration = Transform::value( $attr_value );
	 *
	 * // Returns: "scaleX(1.5) scaleY(1.2) translateX(10) translateY(-5) rotateX(45) rotateY(-30) rotateZ(180) skewX(10) skewY(-5)"
	 * ```
	 */
	public static function value( array $attr_value, string $return_type = 'string' ) {
		$scale     = $attr_value['scale'] ?? null;
		$translate = $attr_value['translate'] ?? null;
		$rotate    = $attr_value['rotate'] ?? null;
		$skew      = $attr_value['skew'] ?? null;

		$transform_declaration = [];

		if ( 'key_value_pair' === $return_type ) {
			if ( isset( $scale['x'] ) ) {
				$transform_declaration['scaleX'] = self::render_percentage( $scale['x'] );
			}

			if ( isset( $scale['y'] ) ) {
				$transform_declaration['scaleY'] = self::render_percentage( $scale['y'] );
			}

			if ( isset( $translate['x'] ) ) {
				$transform_declaration['translateX'] = $translate['x'];
			}

			if ( isset( $translate['y'] ) ) {
				$transform_declaration['translateY'] = $translate['y'];
			}

			if ( isset( $rotate['x'] ) ) {
				$transform_declaration['rotateX'] = $rotate['x'];
			}

			if ( isset( $rotate['y'] ) ) {
				$transform_declaration['rotateY'] = $rotate['y'];
			}

			if ( isset( $rotate['z'] ) ) {
				$transform_declaration['rotateZ'] = $rotate['z'];
			}

			if ( isset( $skew['x'] ) ) {
				$transform_declaration['skewX'] = $skew['x'];
			}

			if ( isset( $skew['y'] ) ) {
				$transform_declaration['skewY'] = $skew['y'];
			}

			return $transform_declaration;
		}

		if ( isset( $scale['x'] ) ) {
			$transform_declaration[] = 'scaleX(' . self::render_percentage( $scale['x'] ) . ')';
		}

		if ( isset( $scale['y'] ) ) {
			$transform_declaration[] = 'scaleY(' . self::render_percentage( $scale['y'] ) . ')';
		}

		if ( isset( $translate['x'] ) ) {
			$transform_declaration[] = 'translateX(' . $translate['x'] . ')';
		}

		if ( isset( $translate['y'] ) ) {
			$transform_declaration[] = 'translateY(' . $translate['y'] . ')';
		}

		if ( isset( $rotate['x'] ) ) {
			$transform_declaration[] = 'rotateX(' . $rotate['x'] . ')';
		}

		if ( isset( $rotate['y'] ) ) {
			$transform_declaration[] = 'rotateY(' . $rotate['y'] . ')';
		}

		if ( isset( $rotate['z'] ) ) {
			$transform_declaration[] = 'rotateZ(' . $rotate['z'] . ')';
		}

		if ( isset( $skew['x'] ) ) {
			$transform_declaration[] = 'skewX(' . $skew['x'] . ')';
		}

		if ( isset( $skew['y'] ) ) {
			$transform_declaration[] = 'skewY(' . $skew['y'] . ')';
		}

		return implode( ' ', $transform_declaration );
	}
}
