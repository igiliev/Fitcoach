<?php

class ET_Theme_Builder_Layout {

	/**
	 * A stack of the current active theme builder layout post type.
	 *
	 * @var string[]
	 */
	protected static $theme_builder_layout = array();

	/**
	 * Get the current theme builder layout id.
	 * Returns 0 if no layout has been started.
	 *
	 * @since 4.0
	 *
	 * @return integer
	 */
	public static function get_theme_builder_layout_id() {
		$count = count( self::$theme_builder_layout );

		if ( $count > 0 ) {
			return self::$theme_builder_layout[ $count - 1 ]['id'];
		}

		return 0;
	}

	/**
	 * Begin a theme builder layout.
	 *
	 * @since 4.0
	 *
	 * @param integer $layout_id Layout post id.
	 *
	 * @return void
	 */
	public static function begin_theme_builder_layout( $layout_id ) {
		$type = get_post_type( $layout_id );

		if ( ! et_theme_builder_is_layout_post_type( $type ) ) {
			$type = 'default';
		}

		$theme_builder_layout_item = array(
			'id'   => (int) $layout_id,
			'type' => $type,
		);

		self::$theme_builder_layout[] = $theme_builder_layout_item;

		/**
		 * Fired before theme builder area is being rendered.
		 *
		 * @since ??
		 *
		 * @param array $theme_builder_layout_item Array of theme builder layout item.
		 */
		do_action( 'et_theme_builder_begin_layout', $theme_builder_layout_item );
	}

	/**
	 * End the current theme builder layout.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public static function end_theme_builder_layout() {
		/**
		 * Fired after theme builder area is being rendered.
		 *
		 * @since ??
		 *
		 * @param array $theme_builder_layout_item Array of theme builder layout item.
		 */
		do_action( 'et_theme_builder_end_layout', end( self::$theme_builder_layout ) );

		array_pop( self::$theme_builder_layout );
	}

	/**
	 * Get the current theme builder layout.
	 * Returns 'default' if no layout has been started.
	 *
	 * @since 4.0
	 *
	 * @return string
	 */
	public static function get_theme_builder_layout_type() {
		$count = count( self::$theme_builder_layout );

		if ( $count > 0 ) {
			return self::$theme_builder_layout[ $count - 1 ]['type'];
		}

		return 'default';
	}

	public static function is_theme_builder_layout() {
		// return 'default' !== self::get_theme_builder_layout_type();
		return 'default' !== self::get_theme_builder_layout_type();
	}
}
