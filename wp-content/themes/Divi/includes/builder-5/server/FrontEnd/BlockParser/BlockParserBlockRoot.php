<?php
/**
 * Class BlockParserBlockRoot
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\FrontEnd\BlockParser;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

// phpcs:disable ET.Sniffs.ValidVariableName.PropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

/**
 * Class BlockParserBlockRoot
 *
 * Holds the block structure in memory
 *
 * @since ??
 */
class BlockParserBlockRoot extends BlockParserBlock {

	/**
	 * Create an instance of `BlockParserBlockRoot`.
	 *
	 * Root is a read-only and unique block. It can only to be added using this method.
	 * The `innerBlocks` data will be populated when calling `BlockParserStore::get('divi/root')`.
	 *
	 * @since ??
	 *
	 * @param int $store_instance The store instance where this block will be stored.
	 */
	public function __construct( $store_instance ) {
		$this->blockName    = 'divi/root';
		$this->attrs        = [];
		$this->innerBlocks  = [];
		$this->innerHTML    = '';
		$this->innerContent = [];

		$this->id            = 'divi/root';
		$this->parentId      = null;
		$this->orderIndex    = 0;
		$this->storeInstance = $store_instance;
	}
}
