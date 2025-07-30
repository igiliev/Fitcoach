<?php
/**
 * GroupedStatements class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\Style\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * GroupedStatements class.
 *
 * This class is equivalent of JS class:
 * {@link /docs/builder-api/js-beta/divi-module/functions/GroupedStatements GroupedStatements} in:
 * `@divi/module` package.
 *
 * @since ??
 */
class GroupedStatements {

	/**
	 * Items data holder.
	 *
	 * @var array
	 */
	private $_item;

	/**
	 * Create an instance of GroupedStatements class.
	 *
	 * @since ??
	 */
	public function __construct() {
		$this->_item = [];
	}

	/**
	 * Add statement's atRules, selector, and declaration.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string|boolean $atRules     Given atRules. This can be `false` to indicate no `atRules`.
	 *     @type string         $selector    Given selector.
	 *     @type string         $declaration Given declaration.
	 * }
	 *
	 * @return void
	 */
	public function add( array $args ): void {
		$at_rules    = $args['atRules'];
		$selector    = $args['selector'];
		$declaration = $args['declaration'];

		$item_key = $at_rules && is_string( $at_rules ) ? $at_rules . $selector : $selector;

		if ( isset( $this->_item[ $item_key ] ) ) {
			$this->_item[ $item_key ]['declaration'] = $this->_item[ $item_key ]['declaration'] . ' ' . $declaration;
		} else {
			$this->_item[ $item_key ] = [
				'atRules'     => $at_rules,
				'selector'    => $selector,
				'declaration' => $declaration,
			];
		}
	}

	/**
	 * Get grouped statements.
	 *
	 * @since ??
	 *
	 * @return string The value of the property.
	 */
	public function value(): string {
		$statements = [];

		foreach ( $this->_item as $statement_item ) {
			$statements[] = Utils::get_statement(
				[
					'atRules' => $statement_item['atRules'],
					'ruleset' => Utils::get_ruleset(
						[
							'selector'    => $statement_item['selector'],
							'declaration' => $statement_item['declaration'],
						]
					),
				]
			);
		}

		return implode( ' ', $statements );
	}

	/**
	 * Get grouped statements in array format.
	 *
	 * @since ??
	 *
	 * @return array The value of the property.
	 */
	public function value_as_array(): array {
		$statements = [];

		foreach ( $this->_item as $statement_item ) {
			$statements[] = $statement_item;
		}

		return $statements;
	}
}
