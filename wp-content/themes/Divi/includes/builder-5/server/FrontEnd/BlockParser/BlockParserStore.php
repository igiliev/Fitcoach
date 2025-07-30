<?php
/**
 * Class BlockParserStore
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\FrontEnd\BlockParser;

// phpcs:disable ET.Sniffs.ValidVariableName.PropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Packages\Module\Options\Conditions\ConditionsRenderer;
use WP_Block;

/**
 * Class BlockParserStore
 *
 * Holds the block structure in memory as flatten associative array. This class is counterparts of EditPostStore in VB, with a slight
 * difference that this class can have multiple instances. A new store instance will be created when `do_blocks` function is invoked. This is intended to prevent
 * the data for previous call of `do_blocks` get overridden by a later call of `do_blocks`.
 * Each item stored in the store will have a `storeInstance` property that hold the data to which store instance is the item belongs to.
 *
 * @since ??
 */
class BlockParserStore {


	/**
	 * Add root block.
	 *
	 * Root is a read-only and unique block. It can only to be added using this method.
	 * The `innerBlocks` data will be populated when calling `BlockParserStore::get('divi/root')`.
	 *
	 * @since ??
	 *
	 * @param int $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return void
	 */
	protected static function _add_root( $instance = null ) {
		$use_instance = self::_use_instance( $instance );

		self::$_data[ $use_instance ]['divi/root'] = new BlockParserBlockRoot( $use_instance );
	}


	/**
	 * Add item to store.
	 *
	 * @since ??
	 *
	 * @param BlockParserBlock $block The block object.
	 *
	 * @return BlockParserBlock
	 */
	public static function add( BlockParserBlock $block ) {
		if ( ! $block->blockName ) {
			return $block;
		}

		if ( self::_is_root( $block->id ) || self::has( $block->id, $block->storeInstance ) ) {
			return $block;
		}

		self::$_data[ self::_use_instance( $block->storeInstance ) ][ $block->id ] = $block;

		return $block;
	}


	/**
	 * Find the ancestor of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string   $child_id The unique ID of the child block.
	 * @param callable $matcher  Callable function that will be invoked to determine if the ancestor is match.
	 * @param int      $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock|null
	 */
	public static function find_ancestor( $child_id, $matcher, $instance = null ) {
		return ArrayUtility::find( self::get_ancestors( $child_id, $instance ), $matcher );
	}


	/**
	 * Get all of existing items in the store.
	 *
	 * @since ??
	 *
	 * @param int $instance The instance of the store you want to use.
	 *
	 * @return BlockParserBlock[]
	 */
	public static function get_all( $instance = null ) {
		$all_blocks = self::$_data[ self::_use_instance( $instance ) ];

		if ( isset( $all_blocks['divi/root'] ) ) {
			$all_blocks['divi/root'] = self::get( 'divi/root', $instance );
		}

		return $all_blocks;
	}

	/**
	 * Get the ancestors of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string $child_id The unique ID of the child block.
	 * @param int    $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock[] An array of ancestors sorted from bottom to the very top level of the structure tree.
	 */
	public static function get_ancestors( $child_id, $instance = null ) {
		$ancestors = [];
		$parent    = self::get_parent( $child_id, $instance );

		while ( $parent ) {
			$ancestors[] = $parent;

			$parent = self::get_parent( $parent->id, $instance );
		}

		return $ancestors;
	}

	/**
	 * Get the ancestor ids of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string   $child_id The unique ID of the child block.
	 * @param int|null $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock[] An array of ancestors sorted from bottom to the very top level of the structure tree.
	 */
	public static function get_ancestor_ids( string $child_id, $instance = null ): array {
		$ancestors = [];
		$parent    = self::get_parent( $child_id, $instance );

		while ( $parent ) {
			if ( isset( $parent->blockName ) && 'divi/placeholder' !== $parent->blockName ) {
				$ancestors[] = $parent->id;
			}

			$parent = self::get_parent( $parent->id, $instance );
		}

		return $ancestors;
	}

	/**
	 * Get the ancestor of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string   $child_id The unique ID of the child block.
	 * @param callable $matcher  Optional.
	 *                           Callable function that will be invoked to determine to return early if it returns a `true`.
	 *                           If not provided, it will match up to the very top level of the structure tree.
	 *                           Default `null`.
	 * @param int      $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock|null
	 */
	public static function get_ancestor( $child_id, $matcher = null, $instance = null ) {
		$ancestor = null;
		$parent   = self::get_parent( $child_id, $instance );

		while ( $parent ) {
			$ancestor = $parent;

			if ( is_callable( $matcher ) && true === call_user_func( $matcher, $ancestor ) ) {
				return $ancestor;
			}

			$parent = self::get_parent( $ancestor->id, $instance );
		}

		return $ancestor;
	}


	/**
	 * Get an array of all the children of a given block.
	 *
	 * @since ??
	 *
	 * @param string $id       The id of the block you want to get.
	 * @param int    $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock[] An array of the children of the block.
	 */
	public static function get_children( $id, $instance = null ) {
		$current = self::get( $id, $instance );

		if ( ! $current ) {
			return [];
		}

		$inner_blocks = [];
		$all_blocks   = self::$_data[ self::_use_instance( $instance ) ];

		foreach ( $all_blocks as $block ) {
			if ( $block->parentId === $current->id ) {
				$inner_blocks[] = self::get( $block->id, $instance );
			}
		}

		if ( 1 < count( $inner_blocks ) ) {
			usort(
				$inner_blocks,
				function( BlockParserBlock $a, BlockParserBlock $b ) {
					if ( $a->index === $b->index ) {
						return 0;
					}

					return ( $a->index < $b->index ) ? -1 : 1;
				}
			);
		}

		return $inner_blocks;
	}


	/**
	 * Combine post attributes with unsynced attributes.
	 *
	 * Combine by swapping out the values of the post attributes with the unsynced attributes.
	 *
	 * @since ??
	 *
	 * @param array $attrs Original attributes.
	 * @param array $unsync_attrs Unsynced attributes.
	 *
	 * @return array Combined attributes.
	 */
	public static function combine_unsync_attrs( array &$attrs, array $unsync_attrs ): array {
		foreach ( $unsync_attrs as $key => $value ) {
			if ( is_array( $value ) ) {
				self::combine_unsync_attrs( $attrs[ $key ], $value );
			} else {
				$attrs[ $key ] = $value;
			}
		}

		return $attrs;
	}

	/**
	 * Get `post_content` of a global layout if the post exists and matches the given arguments.
	 *
	 * This helper is intended to simplify the way to get `post_content` object of a global layout since we already know the ID.
	 * Instead of using the complex and heavy `WP_Query` class, we use the light and cached `get_post` build-in function.
	 *
	 * @since ??
	 *
	 * @param string  $content The content of the global layout.
	 * @param string  $post_id The ID of the post.
	 * @param array   $fields Optional. An array of `key => value` arguments to match against the post object. Default `[]`.
	 * @param array   $capabilities Optional. An array of user capability to match against the current user. Default `[]`.
	 * @param boolean $mask_post_password Optional. Whether to mask `post_password` field. Default `true`.
	 *
	 * @return string|null The post content or null on failure.
	 */
	public static function get_global_layout_content( string $content, string $post_id, array $fields = array(), array $capabilities = array(), bool $mask_post_password = true ) {
		global $_is_parsing_global_layout;

		$post = get_post( $post_id );

		if ( ! $post ) {
			return null;
		}

		// Set $_is_parsing_global_layout so parser knows that it's parsing a global layout.
		$_is_parsing_global_layout = true;

		$parsed_global_layout = parse_blocks( $content );
		$parsed_actual_post   = parse_blocks( $post->post_content );

		// Unset $_is_parsing_global_layout so parser can continue working normally.
		$_is_parsing_global_layout = false;

		$post_attrs   = $parsed_actual_post[0]['attrs'] ?? [];
		$unsync_attrs = $parsed_global_layout[0]['attrs']['unsyncAttrs'] ?? [];

		// Update post attributes with the ones updated with unsynced attributes.
		$parsed_actual_post[0]['attrs'] = self::combine_unsync_attrs( $post_attrs, $unsync_attrs );

		// serialize updated post content and add it to the post object.
		$post->post_content = serialize_blocks( $parsed_actual_post );

		$match = true;

		if ( $fields ) {
			foreach ( $fields as $field => $value ) {
				if ( ! isset( $post->{$field} ) ) {
					$match = false;
					break;
				}

				$match = is_array( $value ) && ! is_array( $post->{$field} ) ? in_array( $post->{$field}, $value, true ) : $post->{$field} === $value;

				if ( ! $match ) {
					break;
				}
			}
		}

		if ( $match && $capabilities ) {
			foreach ( $capabilities as $capability ) {
				if ( ! current_user_can( $capability, $post->ID ) ) {
					$match = false;
					break;
				}
			}
		}

		if ( $match ) {
			if ( $mask_post_password && $post->post_password ) {
				$post->post_password = '***';
			}

			return $post->post_content;
		}

		return null;
	}


	/**
	 * Get the ID of the currently active store instance.
	 *
	 * @since ??
	 *
	 * @return int|null The active store instance ID. Will return `null` when no instance has been created.
	 */
	public static function get_instance() {
		return self::$_instance;
	}


	/**
	 * Get the parent of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string $child_id The unique ID of the child block.
	 * @param int    $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock|null
	 */
	public static function get_parent( $child_id, $instance = null ) {
		$current = self::$_data[ self::_use_instance( $instance ) ][ $child_id ] ?? null;

		if ( ! $current || ! $current->parentId || ! self::has( $current->parentId, $instance ) ) {
			return null;
		}

		return self::get( $current->parentId, $instance );
	}


	/**
	 * Get the siblings of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string $id       The ID of the block you want to get the sibling of.
	 * @param string $location Sibling location. Can be either `before` or `after`.
	 * @param int    $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return BlockParserBlock[] Array of siblings sorted from the closest sibling. Will return empty array on failure.
	 */
	public static function get_siblings( $id, $location, $instance = null ) {
		$parent = self::get_parent( $id, $instance );

		if ( ! $parent ) {
			return [];
		}

		$inner_blocks = [];
		$all_blocks   = self::$_data[ self::_use_instance( $instance ) ];

		foreach ( $all_blocks as $block ) {
			if ( $block->parentId === $parent->id ) {
				$inner_blocks[] = $block;
			}
		}

		$inner_blocks_count = count( $inner_blocks );

		if ( 1 < $inner_blocks_count ) {
			usort(
				$inner_blocks,
				function( $a, $b ) {
					if ( $a->index === $b->index ) {
						return 0;
					}

					return ( $a->index < $b->index ) ? -1 : 1;
				}
			);
		}

		$siblings    = [];
		$index_found = null;
		$index_last  = $inner_blocks_count - 1;

		foreach ( $inner_blocks as $index => $inner_block ) {
			if ( $id === $inner_block->id ) {
				$index_found = $index;
				break;
			}
		}

		if ( null !== $index_found ) {
			if ( 'before' === $location && 0 < $index_found ) {
				$inner_blocks_before = array_reverse( array_slice( $inner_blocks, 0, $index_found ) );

				foreach ( $inner_blocks_before as $inner_block ) {
					$siblings[] = self::get( $inner_block->id, $instance );
				}
			}

			if ( 'after' === $location && $index_last > $index_found ) {
				$inner_blocks_after = array_slice( $inner_blocks, ( $index_found + 1 ) );

				foreach ( $inner_blocks_after as $inner_block ) {
					$siblings[] = self::get( $inner_block->id, $instance );
				}
			}
		}

		return $siblings;
	}

	/**
	 * Get the direct sibling of existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string $id       The ID of the block you want to get the sibling of.
	 * @param string $location Sibling location. Can be either `before` or `after`.
	 * @param int    $instance Optional. The instance of the store you want to use. Default null.
	 *
	 * @return BlockParserBlock|null
	 */
	public static function get_sibling( $id, $location, $instance = null ): ?BlockParserBlock {
		$siblings = self::get_siblings( $id, $location, $instance );

		if ( ! $siblings ) {
			return null;
		}

		return $siblings[0] ?? null;
	}


	/**
	 * Get existing item in the store.
	 *
	 * @since ??
	 *
	 * @param string $id       The unique ID of the block.
	 * @param int    $instance The instance of the store you want to use.
	 *
	 * @return BlockParserBlock|null
	 */
	public static function get( $id, $instance = null ) {
		if ( ! self::has( $id, $instance ) ) {
			return null;
		}

		$use_instance = self::_use_instance( $instance );

		$item = self::$_data[ $use_instance ][ $id ];

		// Populate `innerBlocks` data for root block.
		if ( self::_is_root( $id ) ) {
			$inner_blocks = [];

			foreach ( self::$_data[ $use_instance ] as $block ) {
				// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
				if ( ! self::_is_root( $block->parentId ) ) {
					continue;
				}

				$inner_blocks[] = (array) $block;
			}

			// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
			$item->innerBlocks = $inner_blocks;
		}

		return $item;
	}

	/**
	 * Block Parser Store: Instance check.
	 *
	 * Check if a store ID exists in the current instance's `$_data`.
	 *
	 * @since ??
	 *
	 * @param int $instance The instance ID of the store.
	 *
	 * @return bool
	 */
	public static function has_instance( $instance ) {
		return isset( self::$_data[ $instance ] );
	}


	/**
	 * Block Parser Store: Block check.
	 *
	 * Check if a particular block exists in the instance store.
	 *
	 * @since ??
	 *
	 * @param string $id       The unique ID of the block.
	 * @param int    $instance Optional. The instance of the store you want to use. Default `null`.
	 *
	 * @return bool
	 */
	public static function has( $id, $instance = null ) {
		return isset( self::$_data[ self::_use_instance( $instance ) ][ $id ] );
	}

	/**
	 * Block Parser Store: Is First check.
	 *
	 * Check if the given block is the first block in the parent block.
	 *
	 * @since ??
	 *
	 * @param string $id       The ID of the block you want to check.
	 * @param int    $instance The instance of the store you want to use.
	 *
	 * @return bool
	 */
	public static function is_first( $id, $instance = null ) {
		if ( self::_is_root( $id ) ) {
			return true;
		}

		$parent = self::get_parent( $id, $instance );

		if ( ! $parent ) {
			return false;
		}

		$children = self::get_children( $parent->id, $instance );

		$first_child_id = $children[0]->id ?? null;

		// Checking if children blocks is renderable or not.
		foreach ( $children as $index => $child_block ) {
			$child_block_arr = get_object_vars( $child_block ) ?? [];
			// First parameter `Block` is for the fake block_content, if condition doesn't meet, it will return empty string.
			// We need to check ConditionsRenderer::should_render() to ensure the child block is renderable.
			// If the child block is not renderable, we need to remove this block from the $children.
			if ( ConditionsRenderer::should_render( true, new WP_Block( $child_block_arr ), $child_block_arr['attrs'] ?? [] ) ) {
				$first_child_id = $child_block->id ?? null;
				break;
			}
		}

		return $id === $first_child_id;
	}

	/**
	 * Block Parser Store: Is Last check.
	 *
	 * Check if the given block is the last block in the parent block.
	 *
	 * @since ??
	 *
	 * @param string $id       The ID of the block you want to check.
	 * @param int    $instance The instance of the store you want to use.
	 *
	 * @return bool
	 */
	public static function is_last( $id, $instance = null ) {
		if ( self::_is_root( $id ) ) {
			return true;
		}

		$parent = self::get_parent( $id, $instance );

		if ( ! $parent ) {
			return false;
		}

		$children = self::get_children( $parent->id, $instance );

		if ( ! $children ) {
			return false;
		}

		$last_index = count( $children ) - 1;

		return isset( $children[ $last_index ]->id ) && $id === $children[ $last_index ]->id;
	}

	/**
	 * Block Parser Store: Is Nested Module.
	 *
	 * Check if the given block is a nested module (eg. row inside row module).
	 *
	 * @since ??
	 *
	 * @param string $id       The ID of the block you want to check.
	 * @param int    $instance The instance of the store you want to use.
	 *
	 * @return bool
	 */
	public static function is_nested_module( $id, $instance = null ) {
		$module = self::get( $id, $instance );

		$ancestor_module_name = array_map(
			function( $module ) {
				return $module->blockName;
			},
			self::get_ancestors(
				$id,
				$instance
			)
		);

		return in_array( $module->blockName, $ancestor_module_name, true );
	}

	/**
	 * Check if given block is root block.
	 *
	 * Checks if the given ID is equal to `divi/root`.
	 *
	 * @since ??
	 *
	 * @param string $id The ID of the block you want to check.
	 */
	protected static function _is_root( $id ) {
		return 'divi/root' === $id;
	}
		/**
		 * Set layout area before parsing module / block.
		 * This allows module to know which area it is being rendered in.
		 *
		 * @since ??
		 *
		 * @param array $layout The layout area. The format is matched to layout array passed by `et_theme_builder_begin_layout` filter.
		 */
	public static function set_layout( $layout ) {

		// Set the param as current layout.
		self::$_layout = [
			'id'   => $layout['id'],
			'type' => $layout['type'] ?? 'default',
		];

		// Append the given layout to array of layouts. This will be used when resetting layout.
		self::$_layouts[] = self::$_layout;
	}

	/**
	 * Reset layout area.
	 * After any (theme builder) layout is done rendered, its layout should be reset.
	 *
	 * @since ??
	 */
	public static function reset_layout() {
		// Remove the last (to be reset) layout from array of layouts.
		array_pop( self::$_layouts );

		// Get the previous (last layout after the to be reset layout is removed) layout from array of layouts.
		$last_layout = end( self::$_layouts );

		// Set previous (or default) layout as current layout.
		self::$_layout = $last_layout ? $last_layout : [
			'id'   => '',
			'type' => 'default',
		];
	}

	/**
	 * Get layout type.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public static function get_layout_type() {
		return self::$_layout['type'];
	}

	/**
	 * Get layout types.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_layout_types() {
		return apply_filters(
			'et_theme_builder_layout_types',
			[
				'default',
				'et_header_layout',
				'et_body_layout',
				'et_footer_layout',
			]
		);
	}

		/**
		 * Create or return existing instance.
		 *
		 * Create new store instance when no instance has created yet.
		 * Otherwise returns existing latest instance.
		 *
		 * @since ??
		 *
		 * @internal Do not use this method outside the `BlockParser::parse()`.
		 *
		 * @return int The store instance ID.
		 */
	public static function maybe_new_instance() {
		if ( null !== self::$_instance ) {
			return self::$_instance;
		}

		return self::new_instance();
	}

	/**
	 * Create new store instance and switch to the new instance instantly.
	 *
	 * @since ??
	 *
	 * @internal Do not use this method outside the `BlockParser::parse()`.
	 *
	 * @return int The new store instance ID.
	 */
	public static function new_instance() {
		self::$_instance = null === self::$_instance ? 0 : count( self::$_data );

		self::_add_root( self::$_instance );

		return self::$_instance;
	}

		/**
		 * Reset specific store instance.
		 *
		 * Will reset the store to an empty array `[]`.
		 *
		 * @since ??
		 *
		 * @param int $instance The instance of the store you want to reset.
		 *
		 * @return int|null The given store instance ID or `null` if the given ID is not found.
		 */
	public static function reset_instance( $instance ) {
		if ( self::has_instance( $instance ) ) {
			self::$_data[ $instance ] = [];

			self::_add_root( $instance );

			return $instance;
		}

		return null;
	}

	/**
	 * Store active instance
	 *
	 * @since ??
	 *
	 * @var int
	 */
	protected static $_instance = null;

	/**
	 * Store data
	 *
	 * @since ??
	 *
	 * @var BlockParserBlock[]
	 */
	protected static $_data = [];

	/**
	 * Current layout area.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	protected static $_layout = [
		'id'   => '',
		'type' => 'default',
	];

	/**
	 * Array of currently used layouts.
	 *
	 * Collect all currently used layout so when there are nested layout like body > post content, correct previous
	 * layout gets restored correctly when the layout is being reset.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	protected static $_layouts = [];

		/**
		 * Reset whole store data.
		 *
		 * @since ??
		 */
	public static function reset() {
		self::$_data     = [];
		self::$_instance = null;
	}

	/**
	 * Set property of existing block item in the store.
	 *
	 * @since ??
	 *
	 * @param string $id       The ID of the block you want to set the property for.
	 * @param string $property The property/key you want to set.
	 * @param mixed  $value    The value to set.
	 * @param int    $instance Optional. The instance of the store you want to use. Default `null`.
	 */
	public static function set_property( $id, $property, $value, $instance = null ) {
		$use_instance = self::_use_instance( $instance );

		if (
			self::_is_root( $id )
			|| ! self::has( $id, $instance )
			|| ! property_exists( self::$_data[ $use_instance ][ $id ], $property )
		) {
			return;
		}

		self::$_data[ $use_instance ][ $id ]->{$property} = $value;
	}

	/**
	 * Switch to specific store instance.
	 *
	 * @since ??
	 *
	 * @param int $instance The instance you want to switch to.
	 *
	 * @return int|null The previous instance before the switch. Will return null on failure or when no instance created yet.
	 */
	public static function switch_instance( int $instance ) {
		if ( self::$_instance !== $instance && self::has_instance( $instance ) ) {
			$previous_instance = self::$_instance;
			self::$_instance   = $instance;

			return $previous_instance;
		}

		return null;
	}


	/**
	 * Get the store instance that will be used.
	 *
	 * @since ??
	 *
	 * @param int $instance The instance of the store you want to use.
	 *
	 * @return int The instance of the store that will be used.
	 */
	private static function _use_instance( $instance ) {
		return self::has_instance( $instance ) ? $instance : self::$_instance;
	}


}
