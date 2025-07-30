<?php
/**
 * Menu: FullwidthMenuUtils.
 *
 * @package Builder\Framework\Route
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\FullwidthMenu;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleLibrary\Menu\MenuUtils;
use ET\Builder\Framework\Utility\HTMLUtility;

/**
 * Fullwidth Menu Utils class.
 *
 * @since ??
 */
class FullwidthMenuUtils {

	/**
	 * Render Fullwidth Menu HTML.
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
	 * @return string The Fullwidth Menu HTML.
	 */
	public static function render_fullwidth_menu( array $args ): string {
		$menu_dropdown_direction = $args['menuDropdownDirection'] ?? '';
		$menu_id                 = $args['menuId'] ?? '';

		// Modify the menu item to include the required data.
		add_filter( 'wp_setup_nav_menu_item', [ MenuUtils::class, 'modify_menu_item' ] );

		$menu_class = 'et-menu fullwidth-menu nav';

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
		$primary_nav = wp_nav_menu( apply_filters( 'divi_fullwidth_menu_args', $menu_args ) );

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
					'class' => HTMLUtility::classnames(
						[
							'et-menu-nav'        => true,
							'fullwidth-menu-nav' => true,
						]
					),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $primary_nav,
			]
		);

		remove_filter( 'wp_setup_nav_menu_item', [ MenuUtils::class, 'modify_menu_item' ] );

		return $menu;
	}

}
