<?php
/**
 * Static CSS
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\FrontEnd\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\FrontEnd\Page;
use ET_GB_Block_Layout;

/**
 * Static CSS class.
 *
 * @since ??
 */
class StaticCSS implements DependencyInterface {
	/**
	 * `ET_Core_PageResource` class instance.
	 *
	 * @var \ET_Core_PageResource
	 */
	public static $styles_manager = null;

	/**
	 * `ET_Core_PageResource` class instance.
	 *
	 * @var \ET_Core_PageResource
	 */
	public static $deferred_styles_manager = null;

	/**
	 * A stack of the current active WP Editor template post type such as:
	 * - wp_template
	 * - wp_template_part
	 *
	 * @var array[]
	 */
	public static $wp_editor_template = [];

	/**
	 * Load Static CSS class for rendering style as either inline or enqueued
	 *
	 * @since ??
	 */
	public function load(): void {
		// Use `wp` hook because $post should've been ready so `et_core_page_resource_get_the_ID()` returns correct post ID.
		add_action( 'wp', [ self::class, 'setup' ] );
	}

	/**
	 * Whether to force inline styles.
	 *
	 * @var bool
	 */
	public static $forced_inline_styles = false;

	/**
	 * Setup page resource.
	 *
	 * @since ??
	 *
	 * @internal This is the same mechanism that is used on ET_Builder_Element->__construct() to load page resource.
	 * @internal et_fb_is_enabled() is replaced by Conditions::is_vb_enabled()
	 */
	public static function setup() {
		if ( null === self::$styles_manager && ! is_admin() && ! Conditions::is_vb_enabled() ) {
			// This is needed to set only once.
			Style::set_media_queries();

			$result               = self::setup_styles_manager();
			self::$styles_manager = $result['manager'];

			if ( isset( $result['deferred'] ) ) {
				self::$deferred_styles_manager = $result['deferred'];
			}

			if ( $result['add_hooks'] ) {
				// Schedule callback to run in the footer so we can pass the module design styles to the page resource.
				add_action( 'wp_footer', [ self::class, 'enqueue_or_render_style' ], 19 );

				// Add filter for the resource data so we can prevent theme customizer css from being
				// included with the builder css inline on first-load (since its in the head already).
				add_filter( 'et_core_page_resource_get_data', [ self::class, 'filter_page_resource_data' ], 10, 3 );
			}

			add_action( 'wp_footer', [ self::class, 'maybe_force_inline_styles' ], 19 );
		}
	}

	/**
	 * Begin Divi Builder block output on WP Editor template.
	 *
	 * As identifier od Divi Builder block render template location and the template ID.
	 * Introduced to handle Divi Layout block render on WP Template outside Post Content.
	 * WP Editor templates:
	 * - wp_template
	 * - wp_template_part
	 *
	 * @since ??
	 *
	 * @param int $template_id Template post ID.
	 *
	 * @return void
	 */
	public static function begin_wp_editor_template( int $template_id ) {
		$type = get_post_type( $template_id );

		if ( ! et_builder_is_wp_editor_template_post_type( $type ) ) {
			$type = 'default';
		}

		self::$wp_editor_template[] = array(
			'id'   => $template_id,
			'type' => $type,
		);
	}

	/**
	 * End Divi Builder block output on WP Editor template.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function end_wp_editor_template() {
		array_pop( self::$wp_editor_template );
	}

	/**
	 * Whether a module is rendered in WP Editor template or not.
	 *
	 * @since ??
	 *
	 * @return bool WP Editor template status.
	 */
	public static function is_wp_editor_template(): bool {
		return 'default' !== self::get_wp_editor_template_type();
	}

	/**
	 * Get the current WP Editor template id.
	 *
	 * Returns 0 if no template has been started.
	 *
	 * @since ??
	 *
	 * @return integer Template post ID (wp_id).
	 */
	public static function get_wp_editor_template_id() {
		$count = count( self::$wp_editor_template );
		$id    = 0;

		if ( $count > 0 ) {
			$id = et_()->array_get( self::$wp_editor_template, array( $count - 1, 'id' ), 0 );
		}

		// Just want to be safe to not return any unexpected result.
		return is_int( $id ) ? $id : 0;
	}

	/**
	 * Get the current WP Editor template type.
	 *
	 * Returns 'default' if no template has been started.
	 *
	 * @since ??
	 *
	 * @param boolean $is_id_needed Whether template ID is needed or not.
	 *
	 * @return string Template type.
	 */
	public static function get_wp_editor_template_type( bool $is_id_needed = false ): string {
		$count = count( self::$wp_editor_template );
		$type  = '';

		if ( $count > 0 ) {
			$type = et_()->array_get( self::$wp_editor_template, array( $count - 1, 'type' ) );

			// Page may have more than one template parts. So, the wp_id is needed in certain
			// situation as unique identifier.
			if ( $is_id_needed && ET_WP_EDITOR_TEMPLATE_PART_POST_TYPE === $type ) {
				$id    = self::get_wp_editor_template_id();
				$type .= "-{$id}";
			}
		}

		// Just want to be safe to not return any unexpected result.
		return ! empty( $type ) && is_string( $type ) ? $type : 'default';
	}

	/**
	 * Setup the advanced styles manager
	 *
	 * @since ??
	 *
	 * @param int $post_id The post ID.
	 */
	public static function setup_styles_manager( int $post_id = 0 ) {
		static $cached = null;

		if ( 0 === $post_id && et_core_page_resource_is_singular() ) {
			// It doesn't matter if post id is 0 because we're going to force inline styles.
			$post_id = et_core_page_resource_get_the_ID();
		}

		$should_generate_critical_css = CriticalCSS::should_generate_critical_css();
		$cache_key                    = $post_id . intval( $should_generate_critical_css );

		if ( isset( $cached[ $cache_key ] ) ) {
			return $cached[ $cache_key ];
		}

		$deferred         = false;
		$is_preview       = is_preview() || is_et_pb_preview();
		$forced_in_footer = $post_id && 'on' === et_get_option( 'et_pb_css_in_footer', 'off' );

		// Check is static CSS is disabled.
		$static_css_is_disabled = ! et_core_is_static_css_enabled();

		// All things considered, should we force inline styles?
		$forced_inline = ! $post_id || $is_preview || $forced_in_footer || $static_css_is_disabled || et_core_is_safe_mode_active() || ET_GB_Block_Layout::is_layout_block_preview();

		// Are we using unified styles?
		$unified_styles = ! $forced_inline && ! $forced_in_footer;

		$resource_owner = $unified_styles ? 'core' : 'builder';
		$resource_slug  = $unified_styles ? 'unified' : 'module-design';

		$resource_slug .= $unified_styles && et_builder_post_is_of_custom_post_type( $post_id ) ? '-cpt' : '';

		// Temporarily keep resource slug before TB slug processing.
		$temp_resource_slug = $resource_slug;

		$resource_slug = et_theme_builder_decorate_page_resource_slug( $post_id, $resource_slug );

		// TB should be prioritized over WP Editor. If resource slug is not changed, it is
		// not for TB. Ensure current module is one of WP Editor template before checking.
		if ( $temp_resource_slug === $resource_slug && self::is_wp_editor_template() ) {
			$resource_slug = et_builder_wp_editor_decorate_page_resource_slug( $post_id, $resource_slug );
		}

		// If the post is password protected and a password has not been provided yet,
		// no content (including any custom style) will be printed.
		// When static css file option is enabled this will result in missing styles.
		if ( ! $forced_inline && post_password_required( $post_id ? $post_id : null ) ) {
			$forced_inline = true;
		}

		if ( $is_preview ) {
			// Don't let previews cause existing saved static css files to be modified.
			$resource_slug .= '-preview';
		}

		$manager  = et_core_page_resource_get( $resource_owner, $resource_slug, $post_id, 40 );
		$has_file = $manager->has_file();

		$manager_data = [
			'manager'   => $manager,
			'add_hooks' => true,
		];

		if ( $should_generate_critical_css ) {
			$deferred                 = et_core_page_resource_get( $resource_owner, $resource_slug . '-deferred', $post_id, 40 );
			$has_file                 = $has_file && $deferred->has_file();
			$manager_data['deferred'] = $deferred;
		}

		if ( ! $forced_inline && ! $forced_in_footer && $has_file ) {
			// This post currently has a fully configured styles manager.
			$manager_data['add_hooks'] = false;

			/**
			 * Filters the Style Managers used to output Critical/Deferred Builder CSS.
			 *
			 * This filter is the replacement of Divi 4 filter `et_builder_module_style_manager`.
			 *
			 * @since ??
			 *
			 * @param array $manager_data Style Managers.
			 */
			$manager_data = apply_filters( 'divi_frontend_assets_static_css_module_style_manager', $manager_data );

			return $manager_data;
		}

		$manager->forced_inline       = $forced_inline;
		$manager->write_file_location = 'footer';

		if ( $deferred ) {
			$deferred->forced_inline       = $forced_inline;
			$deferred->write_file_location = 'footer';
		}

		if ( $forced_in_footer || $forced_inline ) {
			// Restore legacy behavior--output inline styles in the footer.
			$manager->set_output_location( 'footer' );
			if ( $deferred ) {
				$deferred->set_output_location( 'footer' );
			}
		}

		/** This filter is documented in StaticCSS.php */
		$manager_data = apply_filters( 'divi_frontend_assets_static_css_module_style_manager', $manager_data );

		// Cache $manager_data.
		$cached[ $cache_key ] = $manager_data;

		return $manager_data;
	}

	/**
	 * Enqueue or render the styles.
	 *
	 * This method passes the styles to the advanced style manager (ET_Core_PageResource) instance and it'll decide
	 * whether the style should be rendered as inline style or enqueued.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function enqueue_or_render_style(): void {
		if ( ! is_admin() && ! Conditions::is_vb_enabled() ) {
			// Get the cutom css, if any.
			$custom = et_core_is_builder_used_on_current_request() ? Page::custom_css() : '';

			// Pass styles to the page resource.
			self::style_output(
				[
					'styles_manager'          => self::$styles_manager,
					'deferred_styles_manager' => self::$deferred_styles_manager ?? null,
					'custom'                  => $custom,
					'element_id'              => 0,
				]
			);
		}
	}

	/**
	 * Output the styles.
	 *
	 * @since ??
	 *
	 * @param array $params {
	 *     Parameters for styling.
	 *
	 *     @var \ET_Core_PageResource $styles_manager          The style manager.
	 *     @var \ET_Core_PageResource $deferred_styles_manager The deferred style manager.
	 *     @var string                $custom                  The custom CSS.
	 *     @var string                $element_id              The Element ID.
	 * }
	 */
	public static function style_output( array $params ) {
		/**
		 * Style Manager.
		 *
		 * @var \ET_Core_PageResource $styles_manager.
		 */
		$styles_manager = $params['styles_manager'] ?? null;

		/**
		 * Deferred Style Manager.
		 *
		 * @var \ET_Core_PageResource $deferred_styles_manager.
		 */
		$deferred_styles_manager = $params['deferred_styles_manager'] ?? null;

		// Get the provided CSS.
		$custom = $params['custom'] ?? '';

		// Get element ID.
		$element_id = $params['element_id'] ?? 0;

		/**
		 * Filters whether Critical CSS should be generated or not.
		 *
		 * @since ??
		 *
		 * @param bool $enabled Critical CSS enabled value.
		 */
		$should_generate_critical_css = CriticalCSS::should_generate_critical_css();

		$critical = '';

		/*
		 * When critical CSS should be generated, styles are split into two:
		 * - Above the fold styles is marked as `critical`.
		 * - Below the fold styles doesn't have `critical` mark.
		 *
		 * When critical CSS should not be generated, all styles doesn't have the`critical` mark.
		 *
		 * The order of the styles is:
		 * - The preset styles can be overridden by the group preset styles.
		 * - The group preset styles can be overridden by the module styles.
		 */
		if ( $should_generate_critical_css ) {
			$critical = Style::render(
				'critical',
				'preset',
				$element_id
			) . Style::render(
				'critical',
				'presetGroup',
				$element_id
			) . Style::render(
				'critical',
				'module',
				$element_id
			);

			$styles = Style::render(
				'default',
				'preset',
				$element_id
			) . Style::render(
				'default',
				'presetGroup',
				$element_id
			) . Style::render(
				'default',
				'module',
				$element_id
			);
		} else {
			$styles = Style::render(
				'default',
				'preset',
				$element_id
			) . Style::render(
				'default',
				'presetGroup',
				$element_id
			) . Style::render(
				'default',
				'module',
				$element_id
			);
		}

		// if the shortcode framework is loaded, get the shortcode element styles.
		if ( et_is_shortcode_framework_loaded() ) {
			if ( $should_generate_critical_css ) {
				$critical = $critical . \ET_Builder_Element::get_style( false, $element_id, true ) . \ET_Builder_Element::get_style( true, $element_id, true );
			}

			$styles = $styles . \ET_Builder_Element::get_style( false, $element_id ) . \ET_Builder_Element::get_style( true, $element_id );
		}

		if ( empty( $critical ) ) {
			// No critical styles defined, just enqueue everything as usual.
			$styles = $custom . $styles;
			if ( ! empty( $styles ) ) {
				if ( isset( $deferred_styles_manager ) ) {
					$deferred_styles_manager->set_data( $styles, 40 );
				} else {
					$styles_manager->set_data( $styles, 40 );
				}
			}
		} else {
			// Add page css to the critical section.
			$critical = $custom . $critical;
			$styles_manager->set_data( $critical, 40 );
			if ( ! empty( $styles ) && isset( $deferred_styles_manager ) ) {
				// Defer everything else.
				$deferred_styles_manager->set_data( $styles, 40 );
			}
		}

		// Cleanup.
		unset( $styles_manager, $deferred_styles_manager, $custom, $critical, $styles );
	}

	/**
	 * Filters the unified page resource data. The data is an array of arrays of strings keyed by
	 * priority. The builder's styles are set with a priority of 40. Here we want to make sure
	 * only the builder's styles are output in the footer on first-page load so we aren't
	 * duplicating the customizer and custom css styles which are already in the <head>.
	 * {@see 'et_core_page_resource_get_data'}
	 *
	 * @since ??
	 *
	 * @param array[]               $data     {
	 *     Arrays of strings keyed by priority.
	 *
	 *     @type string[]           $priority Resource data.
	 *     ...
	 * }.
	 *
	 * @param string                $context  Where the data will be used. Accepts 'inline', 'file'.
	 * @param \ET_Core_PageResource $resource The resource instance.
	 *
	 * @return array
	 */
	public static function filter_page_resource_data( array $data, string $context, \ET_Core_PageResource $resource ): array {
		global $wp_current_filter;

		if ( 'inline' !== $context || ! in_array( 'wp_footer', $wp_current_filter, true ) ) {
			return $data;
		}

		if ( false === strpos( $resource->slug, 'unified' ) ) {
			return $data;
		}

		if ( 'footer' !== $resource->location ) {
			// This is the first load of a page that doesn't currently have a unified static css file.
			// The theme customizer and custom css have already been inlined in the <head> using the
			// unified resource's ID. It's invalid HTML to have duplicated IDs on the page so we'll
			// fix that here since it only applies to this page load anyway.
			$resource->slug = $resource->slug . '-2';
		}

		return isset( $data[40] ) ? array( 40 => $data[40] ) : array();
	}

	/**
	 * Set {@see StaticCSS::$styles_manager} to force inline styles.
	 */
	public static function maybe_force_inline_styles() {
		if ( et_core_is_fb_enabled() || self::$styles_manager->forced_inline || ! self::$forced_inline_styles ) {
			return;
		}

		self::$styles_manager->forced_inline       = true;
		self::$styles_manager->write_file_location = 'footer';
		self::$styles_manager->set_output_location( 'footer' );

		if ( isset( self::$deferred_styles_manager ) ) {
			self::$deferred_styles_manager->forced_inline       = true;
			self::$deferred_styles_manager->write_file_location = 'footer';
			self::$deferred_styles_manager->set_output_location( 'footer' );
		}
	}
}
