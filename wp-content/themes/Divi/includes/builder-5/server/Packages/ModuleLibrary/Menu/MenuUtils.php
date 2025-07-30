<?php
/**
 * Menu: MenuUtils.
 *
 * @package Builder\Framework\Route
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleLibrary\Menu\MenuUtils;
use ET\Builder\Framework\Utility\HTMLUtility;

/**
 * Menu Utils class.
 *
 * @since ??
 */
class MenuUtils {

	/**
	 * Render Menu HTML.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *    An array of arguments.
	 *
	 *    @type string $menuId The menu ID.
	 *    @type string $menuDropdownDirection The submenu direction.
	 * }
	 *
	 * @return string The Menu HTML.
	 */
	public static function render_menu( array $args ): string {
		$menu_dropdown_direction = $args['menuDropdownDirection'] ?? '';
		$menu_id                 = $args['menuId'] ?? '';

		// Modify the menu item to include the required data.
		add_filter( 'wp_setup_nav_menu_item', [ self::class, 'modify_menu_item' ] );

		$menu_class = 'et-menu nav';

		// The divi_disable_toptier option available in Divi theme only.
		if ( ! et_is_builder_plugin_active() && 'on' === et_get_option( 'divi_disable_toptier' ) ) {
			$menu_class .= ' et_disable_top_tier';
		}

		if ( '' !== $menu_dropdown_direction ) {
			$menu_class .= ' ' . $menu_dropdown_direction;
		}

		$menu_args = array(
			'theme_location' => '',
			'container'      => '',
			'fallback_cb'    => '',
			'menu_class'     => $menu_class,
			'menu_id'        => '',
			'echo'           => false,
		);

		if ( '' !== $menu_id ) {
			$menu_args['menu'] = (int) $menu_id;
		} else {
			// When menu ID is not preset, let's use the primary menu.
			// However, it's highly unlikely that the menu module won't have an ID.
			// When were're using menu module via the `menu_id` we dont need the menu's theme location.
			// We only need it when the menu doesn't have any ID and that occurs only used on headers and/or footers,
			// Or any other static places where we need menu by location and not by ID.
			$menu_args['theme_location'] = 'primary-menu';
		}

		/**
		 * Filters the menu arguments.
		 *
		 * @since ??
		 *
		 * @param array $menu_args The menu arguments to be filtered.
		 */
		$primary_nav = wp_nav_menu( apply_filters( 'divi_menu_args', $menu_args ) );

		if ( empty( $primary_nav ) ) {
			$primary_nav_item_home = ! et_is_builder_plugin_active() && 'on' === et_get_option( 'divi_home_link' ) ? HTMLUtility::render(
				[
					'tag'               => 'li',
					'attributes'        => [
						'class' => is_home() ? 'current_page_item' : null,
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => HTMLUtility::render(
						[
							'tag'        => 'a',
							'attributes' => [
								'href' => home_url( '/' ),
							],
							'children'   => __( 'Home', 'et_builder' ),
						]
					),
				]
			) : '';

			ob_start();

			if ( et_is_builder_plugin_active() ) {
				wp_page_menu();
			} else {
				show_page_menu( $menu_class, false, false );
				show_categories_menu( $menu_class, false );
			}

			$primary_nav = HTMLUtility::render(
				[
					'tag'               => 'ul',
					'attributes'        => [
						'class' => $menu_class,
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => [
						$primary_nav_item_home,
						ob_get_contents(),
					],
				]
			);

			ob_end_clean();
		}

		$menu = HTMLUtility::render(
			[
				'tag'               => 'nav',
				'attributes'        => [
					'class' => 'et-menu-nav',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $primary_nav,
			]
		);

		remove_filter( 'wp_setup_nav_menu_item', [ self::class, 'modify_menu_item' ] );

		return $menu;
	}

	/**
	 * Add the class with page ID to menu item so it can be easily found by ID in Frontend Builder.
	 *
	 * @since ??
	 *
	 * @param object $menu_item The Menu Item Object.
	 *
	 * @return object The Menu Item Object.
	 */
	public static function modify_menu_item( $menu_item ) {
		// Since PHP 7.1 silent conversion to array is no longer supported.
		$menu_item->classes = (array) $menu_item->classes;

		if ( esc_url( home_url( '/' ) ) === $menu_item->url ) {
			$fw_menu_custom_class = 'et_pb_menu_page_id-home';
		} else {
			$fw_menu_custom_class = 'et_pb_menu_page_id-' . $menu_item->object_id;
		}

		$menu_item->classes[] = $fw_menu_custom_class;
		return $menu_item;
	}

}
