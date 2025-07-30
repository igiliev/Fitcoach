<?php
/**
 * Class BlockParserFrame
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\FrontEnd\BlockParser;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class BlockParserFrame
 *
 * Holds partial blocks in memory while parsing
 *
 * @internal
 * @since ??
 */
class BlockParserFrame extends \WP_Block_Parser_Frame {
	/**
	 * Full or partial block
	 *
	 * @since ??
	 * @var BlockParserBlock
	 */
	public $block;

	/**
	 * Byte offset into document for start of parse token
	 *
	 * @since ??
	 * @var int
	 */
	public $token_start;

	/**
	 * Byte length of entire parse token string
	 *
	 * @since ??
	 * @var int
	 */
	public $token_length;

	/**
	 * Byte offset into document for after parse token ends
	 * (used during reconstruction of stack into parse production)
	 *
	 * @since ??
	 * @var int
	 */
	public $prev_offset;

	/**
	 * Byte offset into document where leading HTML before token starts
	 *
	 * @since ??
	 * @var int
	 */
	public $leading_html_start;

	/**
	 * Create an instance of `BlockParserFrame`.
	 *
	 * Create an instance of `BlockParserFrame` and populate the object properties from the provided arguments.
	 *
	 * @since ??
	 *
	 * @param BlockParserBlock $block              Full or partial block.
	 * @param int              $token_start        Byte offset into document for start of parse token.
	 * @param int              $token_length       Byte length of entire parse token string.
	 * @param int              $prev_offset        Optional. Byte offset into document for after parse token ends. Default `null`.
	 * @param int              $leading_html_start Optional. Byte offset into document where leading HTML before token starts. Default `null`.
	 */
	public function __construct( $block, $token_start, $token_length, $prev_offset = null, $leading_html_start = null ) {
		$this->block              = $block;
		$this->token_start        = $token_start;
		$this->token_length       = $token_length;
		$this->prev_offset        = isset( $prev_offset ) ? $prev_offset : $token_start + $token_length;
		$this->leading_html_start = $leading_html_start;
	}
}
