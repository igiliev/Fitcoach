<?php
/**
 * Visual Builder Settings.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\SettingsData;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\Customizer\Customizer;
use ET\Builder\Framework\Settings\PageSettings;
use ET\Builder\Framework\Settings\Settings;
use ET\Builder\Framework\Theme\Theme;
use ET\Builder\Framework\Utility\ArrayUtility;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\SiteSettings;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptions;
use ET\Builder\Packages\WooCommerce\WooCommerceUtils;
use ET\Builder\Services\EmailAccountService\EmailAccountService;
use ET\Builder\Services\SpamProtectionService\SpamProtectionService;
use ET\Builder\ThemeBuilder\Layout;
use ET\Builder\VisualBuilder\AppPreferences\AppPreferences;
use ET\Builder\VisualBuilder\REST\Nonce;
use ET\Builder\VisualBuilder\Saving\SavingUtility;
use ET\Builder\VisualBuilder\Shortcode\ShortcodeUtility;
use ET\Builder\VisualBuilder\Taxonomy;
use ET\Builder\VisualBuilder\TemplatePlaceholder;
use ET\Builder\VisualBuilder\Workspace\Workspace;
use ET\Builder\Packages\Conversion\ShortcodeMigration;

/**
 * Class that provides Settings Data callbacks.
 *
 * @since ??
 */
class SettingsDataCallbacks {
	/**
	 * Get `breakpoints` setting data.
	 *
	 * @since ??
	 */
	public static function breakpoints() {
		return Breakpoint::get_settings_values();
	}

	/**
	 * Get `conditionalTags` setting data.
	 *
	 * @since ??
	 */
	public static function conditional_tags() {
		static $return = null;

		if ( null === $return ) {
			$return = et_fb_conditional_tag_params();
		}

		return $return;
	}

	/**
	 * Get `currentPage` setting data.
	 *
	 * @since ??
	 */
	public static function current_page() {
		static $return = null;

		if ( null === $return ) {
			$return = et_fb_current_page_params();
		}

		return $return;
	}

	/**
	 * Get `currentUser` setting data.
	 *
	 * @since ??
	 */
	public static function current_user() {
		static $return = null;

		$user         = wp_get_current_user();
		$capabilities = $user->allcaps;

		/**
		 * Handle multisite subsite capabilities issue:
		 * In WordPress multisite, user capabilities are stored per-site in the wp_X_usermeta table.
		 * When a user is added to a subsite, they need to be explicitly granted capabilities for that site.
		 * If the user hasn't been properly added to the subsite or their capabilities haven't been set,
		 * wp_get_current_user()->allcaps will return an empty array on subsites.
		 * As a fallback, we check the main site's capabilities since users typically have their
		 * full set of capabilities defined there.
		 */
		if ( empty( $capabilities ) && is_multisite() && ! is_main_site() ) {
			// Temporarily switch to the main site to get the user's capabilities.
			switch_to_blog( get_main_site_id() );
			$capabilities = wp_get_current_user()->allcaps;
			// Restore the current site context.
			restore_current_blog();
		}

		if ( null === $return ) {
			$return = [
				'role'         => et_pb_get_current_user_role(),
				'capabilities' => $capabilities,
			];
		}

		return $return;
	}

	/**
	 * Get `customizer` setting data.
	 */
	public static function customizer() {
		static $return = null;

		if ( null === $return ) {
			$return = Customizer::get_settings_values();
		}

		return $return;
	}

	/**
	 * Get `dynamicContent` setting data.
	 *
	 * @since ??
	 */
	public static function dynamic_content() {
		static $return = null;

		if ( null === $return ) {
			global $post;

			$post_id = $post->ID ?? '';

			$return = [
				'options' => DynamicContentOptions::get_options( $post_id, 'display' ),
			];
		}

		return $return;
	}

	/**
	 * Get `fonts` setting data.
	 *
	 * @since ??
	 */
	public static function fonts() {
		static $return = null;

		if ( null === $return ) {
			$heading_font        = et_get_option( 'heading_font', 'Open Sans' );
			$body_font           = et_get_option( 'body_font', 'Open Sans' );
			$heading_font_weight = et_get_option( 'heading_font_weight', '500' );
			$body_font_weight    = et_get_option( 'body_font_weight', '500' );

			$customizer_fonts = [
				'heading' => [
					'label'      => esc_html__( 'Headings', 'et_builder' ),
					'fontId'     => '--et_global_heading_font',
					'fontName'   => $heading_font ? $heading_font : 'Open Sans',
					'fontWeight' => $heading_font_weight ? $heading_font_weight : '500',
				],
				'body'    => [
					'label'      => esc_html__( 'Body', 'et_builder' ),
					'fontId'     => '--et_global_body_font',
					'fontName'   => $body_font ? $body_font : 'Open Sans',
					'fontWeight' => $body_font_weight ? $body_font_weight : '500',
				],
			];

			$google_fonts = array_merge(
				[ 'Default' => [] ],
				et_builder_get_websafe_fonts(),
				et_builder_get_google_fonts()
			);

			ksort( $google_fonts );

			$return = [
				'custom'     => et_builder_get_custom_fonts(),
				'customizer' => $customizer_fonts,
				'formats'    => et_pb_get_supported_font_formats(),
				'google'     => $google_fonts,
				'icons'      => et_pb_get_font_icon_symbols(),
				'iconsDown'  => et_pb_get_font_down_icon_symbols(),
				'removed'    => et_builder_old_fonts_mapping(),
			];
		}

		return $return;
	}

	/**
	 * Get `globalPresets` settings data.
	 *
	 * @since ??
	 */
	public static function global_presets() {
		static $return = null;

		if ( null === $return ) {
			$return = [
				'data'                 => (object) GlobalPreset::get_data(),
				'legacyData'           => (object) GlobalPreset::get_legacy_data(),
				'isLegacyDataImported' => 'yes' === GlobalPreset::is_legacy_presets_imported(),
			];
		}

		return $return;
	}

	/**
	 * Get `google` settings data.
	 *
	 * @since ??
	 */
	public static function google() {
		static $return = null;

		if ( null === $return ) {
			$google_api_settings = et_pb_is_allowed( 'theme_options' ) ? get_option( 'et_google_api_settings' ) : [];
			$google_api_key      = $google_api_settings['api_key'] ?? '';

			$return = [
				// phpcs:ignore Generic.Commenting.Todo.TaskFound -- Valid D5 Todo task.
				// TODO feat(D5, Refactor) this should be secret.
				'APIKey'           => $google_api_key,
				'mapsScriptNotice' => ! et_pb_enqueue_google_maps_script(),
			];
		}

		return $return;
	}

	/**
	 * Get `layout` settings data.
	 *
	 * @since ??
	 */
	public static function layout() {
		static $return = null;

		if ( null === $return ) {
			global $post;

			$post_id        = isset( $post->ID ) ? $post->ID : '';
			$post_type      = isset( $post->post_type ) ? $post->post_type : 'post';
			$layout_type    = '';
			$remote_item_id = '';
			$template_type  = '';

			// phpcs:ignore Generic.Commenting.Todo.TaskFound -- Valid D5 Todo task.
			// TODO feat(D5, Coverage) more will happen here. See: et_fb_get_dynamic_backend_helpers().
			if ( 'et_pb_layout' === $post_type ) {
				$layout_type   = et_fb_get_layout_type( $post_id );
				$template_type = get_post_meta( $post_id, '_et_pb_template_type', true );

				// Only set the remote_item_id if temp post still exists.
				if ( ! empty( $_GET['cloudItem'] ) && get_post_status( $post_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- This function does not change any state, and is therefore not susceptible to CSRF.
					$remote_item_id = (int) sanitize_text_field( $_GET['cloudItem'] ); // phpcs:ignore WordPress.Security.NonceVerification -- This function does not change any state, and is therefore not susceptible to CSRF.
				}
			}

			$return = [
				'type'         => $layout_type,
				'templateType' => $template_type,
				'remoteItemId' => $remote_item_id,
			];
		}

		return $return;
	}

	/**
	 * Get `markups` setting data.
	 *
	 * @since ??
	 */
	public static function markups() {
		static $return = null;

		if ( null === $return ) {
			$return = [
				'commentsModule' => TemplatePlaceholder::comments(),
			];
		}

		return $return;
	}

	/**
	 * Get `navMenus` setting data.
	 *
	 * @since ??
	 */
	public static function nav_menus() {
		static $return = null;

		if ( null === $return ) {
			$return = [
				'options' => et_builder_get_nav_menus_options(),
			];
		}

		return $return;
	}

	/**
	 * Get `nonces` setting data.
	 *
	 * @since ??
	 */
	public static function nonces() {
		static $return = null;

		if ( null === $return ) {
			$return = Nonce::get_data();
		}

		return $return;
	}

	/**
	 * Get `post` setting data.
	 *
	 * @since ??
	 */
	public static function post() {
		static $return = null;

		if ( null === $return ) {
			global $post;

			$post_id      = isset( $post->ID ) ? $post->ID : '';
			$post_content = isset( $post->post_content ) ? $post->post_content : '';
			$post_type    = isset( $post->post_type ) ? $post->post_type : 'post';
			$post_status  = isset( $post->post_status ) ? $post->post_status : false;

			// Migration of legacy shortcodes if needed.
			if ( $post_content && 'on' !== get_post_meta( $post_id, '_et_pb_use_divi_5', true ) ) {
				$post_content = ShortcodeMigration::maybe_migrate_legacy_shortcode( $post_content );
			}

			/**
			 * Filters the raw post content that is used for the visual builder.
			 *
			 * @since      ??
			 *
			 * @param string $post_content Raw post content that is used for the visual builder.
			 * @param int    $post_id      Post ID.
			 *
			 * @deprecated 5.0.0 Use the {@see 'divi_visual_builder_settings_data_post_content'} filter instead.
			 */
			$post_content = apply_filters(
				'et_fb_load_raw_post_content',
				$post_content,
				$post_id
			);

			/**
			 * Filters the raw post content that is used for the visual builder.
			 *
			 * @since ??
			 *
			 * @param string $post_content Raw post content that is used for the visual builder.
			 * @param int    $post_id      Post ID.
			 */
			$raw_post_content = apply_filters( 'divi_visual_builder_settings_data_post_content', $post_content, $post_id );

			// If page is not singular and uses theme builder, set $post_status to 'publish'
			// to get the 'Save' button instead of 'Draft' and 'Publish'.
			if ( ! is_singular() && et_fb_is_theme_builder_used_on_page() && et_pb_is_allowed( 'theme_builder' ) ) {
				$post_status = 'publish';
			}

			$request_type = $post_type;

			// Set request_type on 404 pages.
			if ( is_404() ) {
				$request_type = '404';
			}

			// Set request_type on Archive pages.
			if ( is_archive() ) {
				$request_type = 'archive';
			}

			// Set request_type on the homepage.
			if ( is_home() ) {
				$request_type = 'home';
			}

			$return = [
				'content'     => $raw_post_content,
				'id'          => $post_id,
				'title'       => get_the_title( $post_id ),
				'type'        => $post_type,
				'requestType' => $request_type,
				'status'      => $post_status,
				'url'         => get_permalink( $post_id ),
				'editUrl'     => get_edit_post_link( $post_id, 'raw' ),
				'iframeSrc'   => ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) ?
					( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( $_SERVER['HTTP_HOST'] )
					. sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '',
			];
		}

		return $return;
	}

	/**
	 * Get `preferences` setting data.
	 *
	 * @since ??
	 *
	 * @return array Array of app preferences.
	 */
	public static function preferences(): array {
		static $return = null;

		if ( null === $return ) {
			$clean_preferences = array();
			$app_preferences   = AppPreferences::mapping();

			foreach ( $app_preferences as $preference_key => $preference ) {
				$option_name  = 'et_fb_pref_' . $preference['key'];
				$option_value = et_get_option( $option_name, $preference['default'], '', true );

				// If options available, verify returned value against valid options. Return default if fails.
				if ( isset( $preference['options'] ) ) {
					$options       = $preference['options'];
					$valid_options = isset( $options[0] ) ? $options : array_keys( $options );
					// phpcs:ignore WordPress.PHP.StrictInArray -- $valid_options array has strings and numbers values.
					if ( ! in_array( (string) $option_value, $valid_options ) ) {
						$option_value = $preference['default'];
					}
				}

				/**
				 * Fix(D5, Theme): Manually set 'd5-enhanced' as app theme for the entire Visual Builder.
				 * We have completely migrated to the new d5-enhanced design. This is to be removed
				 * once all d4 variants of components are removed.
				 */
				if ( 'et_fb_pref_app_theme' === $option_name ) {
					$option_value = 'd5-enhanced';
				}

				$option_value                         = SavingUtility::parse_value_type( $option_value, $preference['type'] );
				$clean_preferences[ $preference_key ] = $option_value;
			}

			/**
			 * Filter to modify Divi Builder app preferences data.
			 *
			 * @since ??
			 *
			 * @param array $clean_preferences Array of preferences.
			 */
			$return = apply_filters( 'divi_visual_builder_preferences_data', $clean_preferences );
		}

		return $return;
	}

	/**
	 * Get `services` setting data.
	 *
	 * @since ??
	 */
	public static function services() {
		static $return = null;

		if ( null === $return ) {
			$return = [
				'email'          => EmailAccountService::definition(),
				'spamProtection' => SpamProtectionService::definition(),
			];
		}

		return $return;
	}

	/**
	 * Get `settings` setting data.
	 *
	 * @since ??
	 */
	public static function settings() {
		static $return = null;

		if ( null === $return ) {
			// GMT Offset.
			$gmt_offset = get_option( 'gmt_offset' );

			// Get Sidebar values.
			$sidebar_values = Theme::get_sidebar_areas();

			$return = [
				'cookiePath'   => SITECOOKIEPATH,
				'page'         => [
					'items'  => PageSettings::get_registered_items(),
					'values' => Settings::get_settings_values(),
				],
				'role'         => et_pb_get_role_settings(),
				'site'         => [
					'gmtOffsetString' => SiteSettings::get_gmt_offset_string( $gmt_offset ),
					'url'             => get_site_url(),
				],
				'theme'        => [
					'widgetAreas' => $sidebar_values['widget_areas'],
					'defaultArea' => $sidebar_values['area'],
				],
				'previewNonce' => wp_create_nonce( 'et_pb_preview_nonce' ),
			];
		}

		return $return;
	}

	/**
	 * Shortcode module definitions setting data.
	 *
	 * @since ??
	 */
	public static function shortcode_module_definitions() {
		static $return = null;

		if ( null === $return ) {
			// fire the actions to initialize any Divi Extensions.
			do_action( 'divi_extensions_init' );
			do_action( 'et_builder_ready' );
			do_action( 'divi_visual_builder_before_get_shortcode_module_definitions' );

			$return = \ET_Builder_Element::get_shortcode_module_definitions();
		}

		return $return;
	}

	/**
	 * Get `shortcodeTags` setting data.
	 *
	 * @since ??
	 */
	public static function shortcode_tags() {
		static $return = null;

		if ( null === $return ) {
			$return = ShortcodeUtility::get_shortcode_tags();
		}

		return $return;
	}

	/**
	 * Get `styles` setting data.
	 *
	 * @since ??
	 */
	public static function styles() {
		static $return = null;

		if ( null === $return ) {
			$return = [
				'acceptableCSSStringValues' => et_builder_get_acceptable_css_string_values( 'all' ),
				'customizer'                => [
					'body'   => [
						'fontHeight' => floatval( et_get_option( 'body_font_height', '1.7' ) ),
						'fontSize'   => absint( et_get_option( 'body_font_size', '14' ) ),
					],
					'layout' => [
						'contentWidth' => absint( et_get_option( 'content_width', '1080' ) ),
					],
				],
			];
		}

		return $return;
	}

	/**
	 * Get `taxonomy` setting data.
	 *
	 * @since ??
	 */
	public static function taxonomy() {
		static $return = null;

		if ( null === $return ) {
			// Divi Taxonomies.
			$layout_taxonomies = Taxonomy::get_terms();

			/**
			 * Filters the taxonomies that are used for the layout category and layout tag.
			 *
			 * @since      ??
			 *
			 * @param array $layout_taxonomies Taxonomies that are used for the layout category and layout tag.
			 *
			 * @deprecated 5.0.0 Use the {@see 'divi_visual_builder_settings_data_layout_taxonomies'} filter instead.
			 */
			$layout_taxonomies = apply_filters(
				'et_fb_taxonomies',
				$layout_taxonomies
			);

			/**
			 * Filters the taxonomies that are used for the layout category and layout tag.
			 *
			 * @since ??
			 *
			 * @param array $layout_taxonomies Taxonomies that are used for the layout category and layout tag.
			 */
			$get_taxonomies = apply_filters( 'divi_visual_builder_settings_data_layout_taxonomies', $layout_taxonomies );

			$return = array(
				'layoutCategory'    => array_key_exists( 'layout_category', $get_taxonomies ) ? $get_taxonomies['layout_category'] : array(),
				'layoutTag'         => array_key_exists( 'layout_tag', $get_taxonomies ) ? $get_taxonomies['layout_tag'] : array(),
				'projectCategories' => array_key_exists( 'project_category', $layout_taxonomies ) ? $layout_taxonomies['project_category'] : (object) array(),
				'postCategories'    => array_key_exists( 'category', $layout_taxonomies ) ? $layout_taxonomies['category'] : (object) array(),
				'productCategories' => array_key_exists( 'product_cat', $layout_taxonomies ) ? $layout_taxonomies['product_cat'] : (object) array(),
			);

			// Add all other taxonomies dynamically to support external modules.
			foreach ( $get_taxonomies as $taxonomy_name => $terms ) {
				if ( ! in_array( $taxonomy_name, array( 'layout_category', 'layout_tag', 'project_category', 'category', 'product_cat' ), true ) ) {
					// Convert taxonomy name to camelCase format for consistency.
					$camel_case_name            = lcfirst( str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', $taxonomy_name ) ) ) );
					$return[ $camel_case_name ] = $terms;
				}
			}
		}

		return $return;
	}

	/**
	 * Get `themeBuilder` setting data.
	 *
	 * @since ??
	 */
	public static function theme_builder() {
		static $return = null;

		if ( null === $return ) {
			global $post;
			$post_type = $post->post_type ?? 'post';

			// TODO feat(D5, Theme Builder) Maybe remove these parameters. Check whether these are used or not.
			// At the moment these are straight copy from Divi 4 counterpart.
			// Validate the Theme Builder body layout and its post content module, if any.
			$theme_builder_layouts    = et_theme_builder_get_template_layouts();
			$has_tb_layouts           = ! empty( $theme_builder_layouts );
			$is_tb_layout             = et_theme_builder_is_layout_post_type( $post_type );
			$tb_body_layout           = ArrayUtility::get_value( $theme_builder_layouts, ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE, array() );
			$tb_body_has_post_content = $tb_body_layout && et_theme_builder_layout_has_post_content( $tb_body_layout );
			$has_valid_body_layout    = ! $has_tb_layouts || $is_tb_layout || $tb_body_has_post_content;

			$return = [
				'layout'             => Layout::get_layout_based_on_post_type( $post_type ),

				// TODO feat(D5, Theme Builder) Maybe remove these parameters. Check whether these are used or not.
				// At the moment these are straight copy from Divi 4 counterpart.
				'isLayout'           => et_theme_builder_is_layout_post_type( $post_type ),
				'layoutPostTypes'    => et_theme_builder_get_layout_post_types(),
				'bodyLayoutPostType' => ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE,
				'postContentModules' => et_theme_builder_get_post_content_modules(),
				'hasValidBodyLayout' => $has_valid_body_layout,
				'themeBuilderAreas'  => et_theme_builder_get_template_layouts(),
			];
		}

		return $return;
	}

	/**
	 * Get `tinymce` setting data.
	 *
	 * @since ??
	 */
	public static function tinymce() {
		static $return = null;

		if ( null === $return ) {
			$tinymce_default_plugins = [
				'autolink',
				'autoresize',
				'charmap',
				'emoticons',
				'fullscreen',
				'image',
				'link',
				'lists',
				'paste',
				'preview',
				'print',
				'table',
				'textcolor',
				'wpview',
			];

			/**
			 * Filters the TinyMCE plugins that are used for the visual builder.
			 *
			 * @since      ??
			 *
			 * @param array $tinymce_defaults_plugins TinyMCE plugins that are used for the visual builder.
			 *
			 * @deprecated 5.0.0 Use the {@see 'divi_visual_builder_tinymce_plugins'} filter instead.
			 */
			$tinymce_default_plugins = apply_filters(
				'et_fb_tinymce_plugins',
				$tinymce_default_plugins
			);

			/**
			 * Filters the TinyMCE plugins that are used for the visual builder.
			 *
			 * @since ??
			 *
			 * @param array $tinymce_default_plugins TinyMCE plugins that are used for the visual builder.
			 */
			$tinymce_plugins = apply_filters( 'divi_visual_builder_tinymce_plugins', $tinymce_default_plugins );

			$return = [
				'skinUrl'  => ET_BUILDER_5_URI . '/visual-builder-assets/tinymce-skin',
				'cssFiles' => esc_url( includes_url( 'js/tinymce' ) . '/skins/wordpress/wp-content.css' ),
				'plugins'  => $tinymce_plugins,
			];
		}

		return $return;
	}

	/**
	 * Get `urls` setting data.
	 *
	 * @since ??
	 */
	public static function urls() {
		static $return = null;

		if ( null === $return ) {
			$return = [
				'admin'                  => admin_url(),
				'adminOptionsGeneralUrl' => esc_url( admin_url( 'options-general.php' ) ),
				'ajax'                   => is_ssl() ? admin_url( 'admin-ajax.php' ) : admin_url( 'admin-ajax.php', 'http' ),
				'builderImages'          => esc_url( ET_BUILDER_URI . '/images' ),
				'builder5Images'         => esc_url( ET_BUILDER_5_URI . '/images' ),
				'themeOptions'           => esc_url( et_pb_get_options_page_link() ),
				'homeUrl'                => esc_url( home_url( '/' ) ),
				'restRootUrl'            => esc_url( get_rest_url() ),
			];
		}

		return $return;
	}

	/**
	 * Retrieve WooCommerce settings data.
	 *
	 * @since ??
	 *
	 * @return array Associative array containing WooCommerce settings and default values.
	 */
	public static function woocommerce(): array {
		if ( ! et_get_experiment_flag( 'wooProductPageModules' ) ) {
			return [];
		}

		static $return = null;

		if ( null === $return ) {
			$return = [
				'defaults'             => [
					'columnsPosts' => WooCommerceUtils::get_default_columns_posts(),
					'pageType'     => WooCommerceUtils::get_default_page_type(),
					'product'      => WooCommerceUtils::get_default_product(),
					'productTabs'  => WooCommerceUtils::get_default_product_tabs(),
				],
				'inactiveModuleNotice' => esc_html__(
					'WooCommerce must be active for this module to appear',
					'et_builder'
				),
				'isWooCommerceActive'  => Conditions::is_woocommerce_enabled(),
				'productTabsOptions'   => Conditions::is_tb_enabled() && Conditions::is_woocommerce_enabled()
					? WooCommerceUtils::set_default_product_tabs_options()
					: WooCommerceUtils::get_product_tabs_options(),
			];
		}

		return $return;
	}

	/**
	 * Get workspaces data.
	 *
	 * @since ??
	 */
	public static function workspaces() {
		static $return = null;

		if ( null === $return ) {
			$return = Workspace::get_items();
		}

		return $return;
	}

	/**
	 * Get the builder version.
	 *
	 * @since ??
	 */
	public static function get_the_builder_version() {
		if ( ! defined( 'ET_BUILDER_VERSION' ) ) {
			return '0';
		}
		return ET_BUILDER_VERSION;
	}
}
