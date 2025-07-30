<?php
/**
 * Frontend Style
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\FrontEnd\Module;

use ET\Builder\FrontEnd\Assets\CriticalCSS;
use ET\Builder\FrontEnd\Assets\StaticCSS;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\GlobalData\GlobalData;
use ET_Theme_Builder_Layout;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Frontend Style class.
 *
 * This class is used to store and enqueue module styles.
 */
class Style {

	/**
	 * Media queries key value pairs. {@see get_media_quries()}
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_media_queries = [];

	/**
	 * Styles data holder.
	 *
	 * This static property is used to store an array of styles for
	 * different parts of the Divi. Each style is represented
	 * by an associative array, using a style key determined by {@see self::get_style_key()} and
	 * the 'group' parameter in the {@see self::add()} function.
	 * The styles are structured by a group(string), key(int/string), and other details for each style item.
	 *
	 * Each style item is an array including:
	 * - The media query under which this style item falls or 'general' if it's not specific to any.
	 * - The CSS selector to which these styles apply.
	 * - The CSS declarations which are the styles to be applied.
	 * - The priority of the style, which indicates its order of application.
	 * - An optional 'critical' key indicating if the style is critical (above the fold).
	 *
	 * The $_styles property holds the styles until they are rendered using {@see self::render()}.
	 *
	 * Please note, modifying $_styles directly could lead to inconsistent behavior
	 * and it is recommended to use the provided 'add()' method instead.
	 *
	 * @since ??
	 *
	 * @var array An array of styles, each represented by an associative array with keys for the 'name' and 'value'
	 *      properties.
	 */
	private static $_styles = [];

	/**
	 * Holds an array of already processed preset style selector.
	 *
	 * This static property is utilized in the context of preset style processing.
	 *
	 * It basically acts as a cache mechanism so that once a preset selector has been successfully processed,
	 * the system would not re-process it every time, so that the same processing logic is not repetitively performed.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_preset_selector_processed = [];

	/**
	 * Check if a preset selector has been processed.
	 *
	 * @since ??
	 *
	 * @param string $preset_selector_classname The classname of the preset selector to check.
	 *
	 * @return bool True if the preset selector has already been processed, false otherwise.
	 */
	public static function is_preset_selector_processed( string $preset_selector_classname ): bool {
		if ( ! isset( self::$_preset_selector_processed[ $preset_selector_classname ] ) ) {
			// Track processed item to the static variable.
			self::$_preset_selector_processed[ $preset_selector_classname ] = $preset_selector_classname;

			return false;
		}

		return true;
	}

	/**
	 * Retrieve Post ID from 1 of 3 sources depending on which exists:
	 * - get_the_ID()
	 * - $_GET['post']
	 * - $_POST['et_post_id']
	 *
	 * @since ??
	 *
	 * @return int|bool
	 */
	public static function get_current_post_id_reverse() {
		// phpcs:disable WordPress.Security.NonceVerification -- This function does not change any state, and is therefore not susceptible to CSRF.
		$post_id = et_core_get_main_post_id();

		// try to get post id from get_post_ID().
		if ( false !== $post_id ) {
			return $post_id;
		}

		if ( wp_doing_ajax() ) {
			// get the post ID if loading data for VB.
			return isset( $_POST['et_post_id'] ) ? absint( $_POST['et_post_id'] ) : false;
		}

		// fallback to $_GET['post'] to cover the BB data loading.
		return isset( $_GET['post'] ) ? absint( $_GET['post'] ) : false;
		// phpcs:enable
	}

	/**
	 * Get the current TB layout ID if we are rendering one or the current post ID instead.
	 *
	 * @since ??
	 *
	 * @return integer
	 */
	public static function get_layout_id() {
		// TB Layout ID.
		$layout_id = ET_Theme_Builder_Layout::get_theme_builder_layout_id();
		if ( $layout_id ) {
			return $layout_id;
		}

		// WP Template ID.
		$template_id = StaticCSS::get_wp_editor_template_id();
		if ( $template_id ) {
			return $template_id;
		}

		// Post ID by default.
		return self::get_current_post_id_reverse();
	}

	/**
	 * Get style key.
	 *
	 * @return int|string
	 */
	public static function get_style_key() {
		if ( ET_Theme_Builder_Layout::is_theme_builder_layout() || StaticCSS::is_wp_editor_template() ) {
			return self::get_layout_id();
		}

		// Use a generic key in all other cases.
		// For example, injector plugins that repeat a layout in a loop
		// need to group that CSS under the same key.
		return 'post';
	}

	/**
	 * Return style array from {@see self::$internal_modules_styles} or {@see self::$styles}.
	 *
	 * @param string     $group Style Group.
	 * @param int|string $key   Style Key.
	 *
	 * @return array
	 */
	public static function get_style_array( string $group = 'module', $key = 0 ): array {
		$styles_raw = self::$_styles;

		if ( 0 === $key ) {
			$key = self::get_style_key();
		}

		return $styles_raw[ $key ][ $group ] ?? [];
	}

	/**
	 * Return media query from the media query name.
	 * E.g For max_width_767 media query name, this function return "@media only screen and ( max-width: 767px )".
	 *
	 * @since ??
	 *
	 * @param string $name Media query name e.g max_width_767, max_width_980.
	 *
	 * @return bool|mixed
	 */
	public static function get_media_query( string $name ) {
		if ( ! isset( self::$_media_queries[ $name ] ) ) {
			return false;
		}

		return self::$_media_queries[ $name ];
	}

	/**
	 * Return media query key value pairs.
	 *
	 * @since ??
	 *
	 * @param bool $for_js Whether media queries is for js ETBuilderBackend.et_builder_css_media_queries variable.
	 *
	 * @return array|mixed|void
	 */
	public static function get_media_quries( bool $for_js = false ) {
		$media_queries = array(
			'min_width_1405' => '@media only screen and ( min-width: 1405px )',
			'1100_1405'      => '@media only screen and ( min-width: 1100px ) and ( max-width: 1405px)',
			'981_1405'       => '@media only screen and ( min-width: 981px ) and ( max-width: 1405px)',
			'981_1100'       => '@media only screen and ( min-width: 981px ) and ( max-width: 1100px )',
			'min_width_981'  => '@media only screen and ( min-width: 981px )',
			'max_width_980'  => '@media only screen and ( max-width: 980px )',
			'768_980'        => '@media only screen and ( min-width: 768px ) and ( max-width: 980px )',
			'min_width_768'  => '@media only screen and ( min-width: 768px )',
			'max_width_767'  => '@media only screen and ( max-width: 767px )',
			'max_width_479'  => '@media only screen and ( max-width: 479px )',
		);

		$media_queries['mobile'] = $media_queries['max_width_767'];

		$media_queries = apply_filters( 'et_builder_media_queries', $media_queries );

		if ( 'for_js' === $for_js ) {
			$processed_queries = array();

			foreach ( $media_queries as $key => $value ) {
				$processed_queries[] = array( $key, $value );
			}
		} else {
			$processed_queries = $media_queries;
		}

		return $processed_queries;
	}

	/**
	 * Set media queries key value pairs.
	 *
	 * @since ??
	 */
	public static function set_media_queries() {
		self::$_media_queries = self::get_media_quries();
	}

	/**
	 * Add a new style.
	 *
	 * Adds a new style to the CSS styles data. The style will be enqueued by `self::enqueue()`.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for adding a style.
	 *
	 *     @type string    $id              The ID of the style.
	 *     @type int       $orderIndex      The order index of the style.
	 *     @type array     $styles          Optional. An array of CSS styles for the style. Default `[]`.
	 *     @type object    $storeInstance   Optional. The instance of the store. Default `null`.
	 *     @type int       $priority        Optional. The priority of the style. Default `10`.
	 *     @type string    $group           Optional. The group of the style. Default `module`.
	 * }
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * self::add( [
	 *     'id'          => 'style-1',
	 *     'styles'      => ['color' => '#000', 'font-size' => '16px'],
	 *     'storeInstance' => $store,
	 *     'orderIndex'  => 1,
	 *     'priority'    => 20,
	 * ] );
	 * ```
	 */
	public static function add( array $args ): void {
		$id             = $args['id'];
		$styles         = $args['styles'] ?? [];
		$store_instance = $args['storeInstance'] ?? null;
		$priority       = $args['priority'] ?? 10;
		$group          = $args['group'] ?? self::get_group_style();
		$order_index    = $args['orderIndex'];

		// Warn when $styles is string.
		if ( is_string( $styles ) ) {
			et_error( "You're Doing It Wrong! Provided styles must be in array format." );
		}

		// Remove empty styles.
		$styles = is_array( $styles ) ? array_filter( $styles ) : [];

		// Bail when there are no styles found.
		if ( ! $styles ) {
			return;
		}

		// Get the ancestor ids.
		$parent_ids = BlockParserStore::get_ancestor_ids(
			$id,
			$store_instance
		);

		// We're padding block index and parent counts into priority to sort items by priority and parents.
		$priority = (int) ( $priority . $order_index . count( $parent_ids ) );

		/*
		 * When critical CSS should be generated, styles are split into two:
		 * - Above the fold styles is marked as `critical`.
		 * - Below the fold styles doesn't have `critical` mark.
		 *
		 * When critical CSS should not be generated, all styles doesn't have the`critical` mark.
		 */
		if ( CriticalCSS::should_generate_critical_css() ) {
			if ( CriticalCSS::is_above_the_fold() ) {
				$style_type = 'critical';
			} else {
				$style_type = 'default';
			}
		} else {
			$style_type = 'default';
		}

		$style_key        = self::get_style_key();
		$styles_flattened = self::get_style_array( $group );

		foreach ( $styles as $item ) {
			// Ignore string data.
			if ( is_string( $item ) ) {
				continue;
			}

			// Remove empty styles.
			$item_styles = array_filter( $item ) ?? [];

			if ( ! $item_styles ) {
				continue;
			}

			foreach ( $item_styles as $item_style ) {
				// Skip if $item_style is empty or not an array.
				if ( ! $item_style || ! is_array( $item_style ) ) {
					continue;
				}

				$media_query = ! empty( $item_style['atRules'] ) ? $item_style['atRules'] : 'general';
				$selector    = $item_style['selector'];
				$declaration = $item_style['declaration'];

				// prepare styles for internal content. Used in Blog/Slider modules if they contain Divi modules.
				if ( isset( $styles_flattened[ $media_query ][ $selector ]['declaration'] ) ) {
					$existing_declaration = $styles_flattened[ $media_query ][ $selector ]['declaration'];

					if ( $declaration !== $existing_declaration ) {
						$styles_flattened[ $media_query ][ $selector ]['declaration'] = sprintf(
							'%1$s %2$s',
							$existing_declaration,
							$declaration
						);
					}
				} else {
					$styles_flattened[ $media_query ][ $selector ]['declaration'] = $declaration;
				}

				$styles_flattened[ $media_query ][ $selector ]['priority'] = $priority;

				if ( 'critical' === $style_type ) {
					$styles_flattened[ $media_query ][ $selector ]['critical'] = 1;
				}
			}
		}

		// Sometimes module will have set setting only on desktop and mobile while tablet being turned off.
		// In that case there will be only two @-rules inside the $styles_flattened (desktop and mobile).
		// If one of the next attribute has tablet styles, they will be added after mobile styles in
		// $styles_flattened, effectively enabling tablet styles to override the mobile ones in FE.
		// For this reason we need to make sure @-rules are sorted properly by priority.
		// Preprocess media queries for sorting priorities.
		$media_priorities = array_map(
			function( $key ) {
				if ( 'general' === $key ) {
					// Ensure 'general' styles appear first.
					return -PHP_INT_MAX;
				}

				// Match media queries min-width and max-width values.
				// https://regex101.com/r/mL9v1T/1.
				$pattern = '/@media only screen and \((min|max)-width:\s*(\d+)px\)/';
				if ( preg_match( $pattern, $key, $matches ) ) {
					$type  = $matches[1];
					$value = (int) $matches[2];

					// Return a calculated priority: max-width sorted descending, min-width ascending.
					return 'max' === $type ? -$value : $value + PHP_INT_MAX;
				}

				// Default for unknown media queries.
				return PHP_INT_MAX;
			},
			array_keys( $styles_flattened )
		);

		// Sort the flattened styles by their media query priorities.
		uksort(
			$styles_flattened,
			function ( $a, $b ) use ( $media_priorities, $styles_flattened ) {
				return $media_priorities[ array_search( $a, array_keys( $styles_flattened ), true ) ]
					<=> $media_priorities[ array_search( $b, array_keys( $styles_flattened ), true ) ];
			}
		);

		self::$_styles[ $style_key ][ $group ] = $styles_flattened;
	}

	/**
	 * Sort an array of items by their priority.
	 *
	 * This function takes an array of items. The function then sorts the array of priorities in ascending
	 * order. If two items have the same priority, they will be sorted by their original index
	 * within the input array.
	 *
	 * @since ??
	 *
	 * @param array $collection The array to be sorted. Each child item in the array should have a 'priority' key.
	 *
	 * @return array An array of items sorted by priority. The array will maintain the same keys as the input array.
	 *
	 * @example
	 * ```php
	 * $collection = [
	 *     'selector1' => ['priority' => 5, 'item' => 'A'],
	 *     'selector2' => ['priority' => 10, 'item' => 'B'],
	 *     'selector3' => ['priority' => 5, 'item' => 'C'],
	 * ];
	 *
	 * $sortedCollection = sort_by_priority($collection);
	 *
	 * // $sortedCollection will be:
	 * // [
	 * //     'selector1' => ['priority' => 5, 'item' => 'A'],
	 * //     'selector3' => ['priority' => 5, 'item' => 'C'],
	 * //     'selector2' => ['priority' => 10, 'item' => 'B'],
	 * // ]
	 * ```
	 */
	public static function sort_by_priority( array &$collection ): array {
		$keys_order = array_flip( array_keys( $collection ) );

		uksort(
			$collection,
			function ( $a, $b ) use ( $keys_order, $collection ) {
				if ( $collection[ $a ]['priority'] === $collection[ $b ]['priority'] ) {
					return $keys_order[ $a ] - $keys_order[ $b ];
				}

				return $collection[ $a ]['priority'] - $collection[ $b ]['priority'];
			}
		);

		unset( $keys_order );

		return $collection;
	}

	/**
	 * Enqueue styles from the Style class.
	 *
	 * This function retrieves the styles data from the Style class and enqueues the styles on the
	 * page. It concatenates the styles into a single string and echoes them within `style` tags.
	 * The styles are sanitized and escaped before being output to the page.
	 *
	 * @since ??
	 *
	 * @param string $style_type The type of styles to enqueue.
	 * @param string $group The group of styles to enqueue. Default is 'module'.
	 * @param string $key   Optional. The element id.
	 *
	 * @return void
	 *
	 * @example: Enqueue styles
	 * ```php
	 * MyStyles::enqueue();
	 * ```
	 */
	public static function enqueue( string $style_type = 'default', string $group = 'module', $key = 0 ): void {
		$styles_output = self::render( $style_type, $group, $key );

		if ( $styles_output ) {
			echo '<style>';
			echo et_core_esc_previously( $styles_output );
			echo '</style>';
		}
	}

	/**
	 * Render sorted styles as string.
	 *
	 * @since ??
	 *
	 * @param string $style_type The type of styles to enqueue.
	 * @param string $group The group of styles to enqueue. Default is 'module'.
	 * @param string $key        Optional. The element id.
	 *
	 * @example: Render styles
	 * ```php
	 * MyStyles::render();
	 * ```
	 */
	public static function render( string $style_type = 'default', string $group = 'module', $key = 0 ): string {
		$styles_data = self::get_style_array( $group, $key );

		// Bail, if there are np data to process.
		if ( ! $styles_data ) {
			return '';
		}

		$critical                = 'critical' === $style_type;
		$styles_by_media_queries = $styles_data;
		$media_queries_order     = array_merge( array( 'general' ), array_values( self::$_media_queries ) );

		// make sure styles in the array ordered by media query correctly from bigger to smaller screensize.
		$styles_by_media_queries_sorted = array_merge( array_flip( $media_queries_order ), $styles_by_media_queries );

		$output = '';

		global $et_user_fonts_queue;

		// TODO feat(D5, FE Rendering): Need to rewrite et_builder_enqueue_user_fonts in D5.
		if ( ! empty( $et_user_fonts_queue ) ) {
			$output .= et_builder_enqueue_user_fonts( $et_user_fonts_queue );
		}

		foreach ( $styles_by_media_queries_sorted as $media_query => $styles ) {
			// Skip wrong values which were added during the array sorting.
			if ( ! is_array( $styles ) ) {
				continue;
			}

			$media_query_output    = '';
			$wrap_into_media_query = 'general' !== $media_query;

			// Sort styles by priority.
			self::sort_by_priority( $styles );

			// Merge styles with identical declarations.
			$merged_declarations = [];
			foreach ( $styles as $selector => $settings ) {

				if ( false === $critical && isset( $settings['critical'] ) ) {
					continue;
				} elseif ( true === $critical && empty( $settings['critical'] ) ) {
					continue;
				}

				$this_declaration = md5( $settings['declaration'] );

				// We want to skip combining anything with psuedo selectors or keyframes or free-form-css (which has
				// empty selector) or preset selectors.
				if (
					false !== strpos( $selector, ':-' ) ||
					false !== strpos( $selector, '@keyframes' ) ||
					'' === $selector ||
					false !== strpos( $selector, 'preset--' )
				) {
					// set unique key so that it cant be matched.
					$unique_key                         = $this_declaration . '-' . uniqid();
					$merged_declarations[ $unique_key ] = [
						'declaration' => $settings['declaration'],
						'selector'    => $selector,
					];

					if ( ! empty( $settings['priority'] ) ) {
						$merged_declarations[ $unique_key ]['priority'] = $settings['priority'];
					}

					continue;
				}

				if ( empty( $merged_declarations[ $this_declaration ] ) ) {
					$merged_declarations[ $this_declaration ] = [
						'selector' => '',
						'priority' => '',
					];
				}

				$new_selector = ! empty( $merged_declarations[ $this_declaration ]['selector'] )
					? $merged_declarations[ $this_declaration ]['selector'] . ', ' . $selector
					: $selector;

				$merged_declarations[ $this_declaration ] = [
					'declaration' => $settings['declaration'],
					'selector'    => $new_selector,
				];

				if ( ! empty( $settings['priority'] ) ) {
					$merged_declarations[ $this_declaration ]['priority'] = $settings['priority'];
				}
			}

			$styles_index = 0;

			// Get each rule in a media query.
			foreach ( $merged_declarations as $settings ) {
				if ( empty( $settings['selector'] ) ) {
					// If the selector is empty, just append the declaration directly without brackets.
					// This is needed for free-form-css output.
					$media_query_output .= sprintf(
						'%3$s%4$s%1$s%2$s',
						'',
						$settings['declaration'],
						( 0 === $styles_index ) ? '' : "\n",
						( $wrap_into_media_query ? "\t" : '' )
					);
				} else {
					// If the selector is not empty, use sprintf with brackets.
					$media_query_output .= sprintf(
						'%3$s%4$s%1$s {%2$s}',
						$settings['selector'],
						$settings['declaration'],
						( 0 === $styles_index ) ? '' : "\n",
						( $wrap_into_media_query ? "\t" : '' )
					);
				}

				$styles_index++;
			}

			// All css rules that don't use media queries are assigned to the "general" key.
			// Wrap all non-general settings into media query.
			if ( $wrap_into_media_query && '' !== $media_query_output ) {
				$media_query_output = sprintf(
					'%3$s%3$s%1$s {%2$s%3$s}',
					$media_query,
					$media_query_output,
					"\n"
				);
			}

			$output .= $media_query_output;
		}

		return $output;
	}

	/**
	 * Reset styles data.
	 *
	 * Resets the styles data to an empty array `[]`.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function reset() {
		self::$_styles = [];
	}

	/**
	 * Provides styles for global colors.
	 *
	 * This function retrieves and prepares style data from global colors data. The values are then
	 * sanitized and escaped for secure use.
	 *
	 * It can be used in two ways:
	 * 1. Without any parameters - In this case, it returns styles for all available global colors.
	 * 2. With an array of $global_color_ids - It only returns styles for the colors associated with the provided ids.
	 *
	 * @since ??
	 *
	 * @param array $global_color_ids An optional parameter. When provided, the function will only include
	 *                                the styles for the global colors associated with these ids.
	 *                                If not provided or an empty array is passed, styles for all global colors
	 *                                will be included.
	 *
	 * @return string Returns a string containing the styles for the global colors.
	 */
	public static function get_global_colors_style( array $global_color_ids = [] ): string {
		$global_colors_style = '';
		$global_colors       = GlobalData::get_global_colors();

		foreach ( $global_colors as $key => $value ) {
			if ( ! empty( $value['color'] ) ) {
				$color = $value['color'];

				// When ids are provided, include the styles for the global colors associated with the ids.
				if ( ! empty( $global_color_ids ) && in_array( $key, $global_color_ids, true ) ) {
					$global_colors_style .= '--' . esc_html( $key ) . ': ' . esc_html( $color ) . ';';
				}

				// If there are no ids provided, include the styles for all the global colors.
				if ( empty( $global_color_ids ) ) {
					$global_colors_style .= '--' . esc_html( $key ) . ': ' . esc_html( $color ) . ';';
				}
			}
		}

		if ( ! empty( $global_colors_style ) ) {
			$global_colors_style = ':root{' . $global_colors_style . '}';
		}

		return $global_colors_style;
	}

	/**
	 * The group of the style where it will be added.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private static $_group_style = 'module';

	/**
	 * Set the group of the style where it will be added.
	 *
	 * @since ??
	 *
	 * @param string $group The group of the style.
	 *
	 * @return void
	 */
	public static function set_group_style( string $group ): void {
		self::$_group_style = $group;
	}

	/**
	 * Get the group of the style where it will be added.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public static function get_group_style(): string {
		return self::$_group_style;
	}

	/**
	 * Get global numeric and fonts variables as CSS styles.
	 *
	 * This function retrieves numeric and fonts global variables from the global data and formats them
	 * into CSS variable declarations for use in stylesheets.
	 *
	 * @since ??
	 *
	 * @return string The generated CSS style block containing global numeric and fonts variables.
	 */
	public static function get_global_numeric_and_fonts_vars_style(): string {
		$global_variables         = GlobalData::get_global_variables();
		$numeric_global_variables = $global_variables['numbers'] ?? (object) [];
		$font_global_variables    = $global_variables['fonts'] ?? (object) [];
		$css_statements           = '';

		$merged_global_variables = array_merge( (array) $numeric_global_variables, (array) $font_global_variables );

		foreach ( $merged_global_variables as $key => $value ) {
			if ( is_array( $value ) ) {
				$id     = $value['id'];
				$result = $value['value'];

				// If there are no ids provided, include the styles for all the global variables.
				if ( ! empty( $result ) ) {
					$css_statements .= '--' . esc_html( $id ) . ': ' . esc_html( $result ) . ';';
				}
			}
		}

		if ( ! empty( $css_statements ) ) {
			$css_statements = ':root{' . $css_statements . '}';
		}

		return $css_statements;
	}
}
