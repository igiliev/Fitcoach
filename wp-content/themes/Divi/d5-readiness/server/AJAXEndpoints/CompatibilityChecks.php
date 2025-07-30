<?php
/**
 * Class that handles endpoints callback for compatibility checks
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\AJAXEndpoints;

use Divi\D5_Readiness\Helpers;
use Divi\D5_Readiness\Server\Checks\PostFeatureCheckManager;
use Divi\D5_Readiness\Server\Checks\PluginHooksCheck;
use Divi\D5_Readiness\Server\Checks\PresetFeatureCheck;
use Divi\D5_Readiness\Server\Checks\WidgetFeatureCheck;
use Divi\D5_Readiness\Server\PostTypes;
use Divi\D5_Readiness\Server\Conversion;

/**
 * Class that handles endpoints callback for compatibility checks
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class CompatibilityChecks {

	/**
	 * List of modules.
	 *
	 * @var array
	 */
	protected static $_modules;

	/**
	 * List of third party module slugs.
	 *
	 * @var array
	 */
	protected static $_third_party_module_slugs;

	/**
	 * Register endpoints for compatibility checks.
	 *
	 * @since ??
	 */
	public static function register_endpoints() {
		add_action( 'wp_ajax_et_d5_readiness_get_overview_status', [ self::class, 'get_overview_status' ] );
		add_action( 'wp_ajax_et_d5_readiness_get_result_list', [ self::class, 'get_result_list' ] );
		add_action( 'wp_ajax_et_d5_readiness_get_preset_check_result_list', [ self::class, 'get_preset_check_result_list' ] );
		add_action( 'wp_ajax_et_d5_readiness_get_widget_check_result_list', [ self::class, 'get_widget_check_result_list' ] );
		add_action( 'wp_ajax_et_d5_readiness_get_active_plugins_check_result_list', [ self::class, 'get_active_plugins_result_list' ] );
		add_action( 'wp_ajax_et_d5_readiness_get_modules_conversation_status', [ self::class, 'get_modules_conversation_status' ] );
	}

	/**
	 * Setup the post feature check manager.
	 *
	 * @since ??
	 */
	public static function setup_post_feature_check_manager() {
		$post_feature_check_manager = new PostFeatureCheckManager();

		$post_feature_check_manager->register_check( 'Divi\D5_Readiness\Server\Checks\PostFeature\ModuleUsage' );
		$post_feature_check_manager->register_check( 'Divi\D5_Readiness\Server\Checks\PostFeature\SplitTestUsage' );

		return $post_feature_check_manager;
	}

	/**
	 * Get the overview status results.
	 *
	 * Extracted from get_overview_status() method to be used in D5 Migrator menu Notification count.
	 *
	 * @param bool $override_use_meta Whether to override the use_meta value. Override maybe required
	 *                                to obtain Notification bubble count.
	 * @param bool $updated_use_meta  The updated use_meta value.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_overview_status_results( $override_use_meta = false, $updated_use_meta = false ) {
		$post_types = PostTypes::get_post_type_slugs();

		if ( $override_use_meta ) {
			$use_meta = $updated_use_meta;
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification is done in get_overview_status().
			$use_meta = isset( $_POST['use_meta'] ) ? filter_var( $_POST['use_meta'], FILTER_VALIDATE_BOOLEAN ) : false;
		}

		$overview_status = [
			'conversion_completed' => [],
			'conversion_failed'    => [],
		];

		foreach ( $post_types as $post_type ) {
			$cache_key     = 'd5_readiness_overview_status_pending' . $post_type;
			$cached_status = et_core_cache_get( $cache_key );

			if ( false === $cached_status ) {
				$cached_status = Conversion::get_posts_pending_conversion( $post_type, $use_meta );
				et_core_cache_set( $cache_key, $cached_status );
			}

			$overview_status['conversion_completed'][ $post_type ] = empty( $cached_status );
		}

		foreach ( $post_types as $post_type ) {
			$cache_key     = 'd5_readiness_overview_status_failed' . $post_type;
			$cached_status = et_core_cache_get( $cache_key );

			if ( false === $cached_status ) {
				$cached_status = Conversion::get_posts_conversion_failed( $post_type, $use_meta );
				et_core_cache_set( $cache_key, $cached_status );
			}

			$overview_status['conversion_failed'][ $post_type ] = $cached_status;
		}

		return $overview_status;
	}

	/**
	 * Get the overview status.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function get_overview_status() {
		et_core_security_check( 'edit_posts', 'et_d5_readiness_overview_status', 'wp_nonce' );

		$overview_status = self::get_overview_status_results();

		wp_send_json_success( $overview_status );
	}

	/**
	 * Get the post data.
	 *
	 * @param string $post_type Post type.
	 * @param array  $results   Results.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function get_post_data( $post_type, $results ) {
		return [
			'id'      => get_the_ID(),
			'title'   => get_the_title(),
			'url'     => 'et_pb_layout' === $post_type
				? get_edit_post_link( get_the_ID(), '' )
				: (
						in_array(
							$post_type,
							[
								ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE,
								ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE,
								ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE,
							],
							true
						)
						? admin_url( 'admin.php?page=et_theme_builder' )
						: get_permalink()
				),
			'edit'    => get_edit_post_link(),
			'results' => $results,
		];
	}

	/**
	 * Initialize modules before use.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function initialze_modules_before_use() {
		if ( empty( self::$_modules ) ) {
			self::$_modules = \ET_Builder_Element::get_modules();
		}

		if ( empty( self::$_third_party_module_slugs ) ) {
			// Initialize Divi third party modules with these actions.
			do_action( 'divi_extensions_init' );
			do_action( 'et_builder_ready' );

			self::$_third_party_module_slugs = \ET_Builder_Element::get_third_party_modules();

			// Wihtout this filter, we can't get the name of the third party module from content.
			add_filter( 'divi_frontend_assets_shortcode_whitelist', function( $valid_module_slugs ) {
				return array_merge(
					$valid_module_slugs,
					array_keys( self::$_third_party_module_slugs ),
				);
			} );
		}
	}

	/**
	 * Get the initial used modules names.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	protected static function _get_initial_used_modules_names() {
		$used_modules_names = get_transient( 'et_d5_readiness_used_modules' );

		if ( ! $used_modules_names ) {
			$used_modules_names = [
				'will_convert'     => [],
				'will_not_convert' => [],
			];
		}

		return maybe_unserialize( $used_modules_names );
	}

	/**
	 * Get the used modules name from content.
	 * Keep track of used modules names from every post content.
	 *
	 * @param string $post_content       The post content.
	 * @param array  $used_modules_names The used modules names.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	protected static function _get_used_modules_name_from_content( $post_content, &$used_modules_names ) {
		$modules_from_content = Helpers\readiness_get_modules_names_from_content(
			$post_content,
			self::$_modules,
			self::$_third_party_module_slugs
		);

		return array_merge_recursive( $used_modules_names, $modules_from_content );
	}

	/**
	 * Get the result list.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function get_result_list() {
		// TODO fix(D5, D5 Readiness), use diff role.
		et_core_security_check( 'edit_posts', 'et_d5_readiness_get_result_list', 'wp_nonce' );

		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';

		if ( ! in_array( $post_type, PostTypes::get_post_type_slugs(), true ) ) {
			wp_send_json_error();
		}

		self::initialze_modules_before_use();

		$used_modules_names = self::_get_initial_used_modules_names();

		$post_feature_check_manager = self::setup_post_feature_check_manager();

		$use_meta = in_array( $post_type, et_builder_get_enabled_builder_post_types(), true );

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'post_status'    => 'any',
			'meta_query'     => Conversion::_get_pending_conversion_meta_query( $post_type, $use_meta ),
		];

		$query = new \WP_Query( $args );

		$result_list = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_status  = get_post_status();
				$post_content = get_the_content();

				$used_modules_names = self::_get_used_modules_name_from_content( $post_content, $used_modules_names );

				$results = $post_feature_check_manager->run_all_checks( get_the_ID(), $post_content );

				if ( empty( $result_list[ $post_status ] ) ) {
					$result_list[ $post_status ] = [
						'all'          => [],
						'compatible'   => [],
						'incompatible' => [],
					];
				}

				$post_data = self::get_post_data( $post_type, $results );

				$result_list[ $post_status ]['all'][] = $post_data;

				if ( ! $results ) {
					$result_list[ $post_status ]['compatible'][] = $post_data;
				} else {
					$result_list[ $post_status ]['incompatible'][] = $post_data;
				}
			}

			wp_reset_postdata(); // Reset after the loop.
		}

		Helpers\readiness_update_used_modules_names( $used_modules_names );

		wp_send_json_success( $result_list );
	}

	/**
	 * Run the checks for presets.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function get_preset_check_result_list() {
		et_core_security_check( 'edit_posts', 'et_d5_readiness_get_result_list', 'wp_nonce' );

		$check_instance = new PresetFeatureCheck();

		$check_instance->run_check();

		$detected = $check_instance->detected();

		wp_send_json_success( $detected );
	}

	/**
	 * Run the checks for widget areas.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function get_widget_check_result_list() {
		// fix(D5, D5 Readiness) TODO, use diff role.
		et_core_security_check( 'edit_posts', 'et_d5_readiness_get_result_list', 'wp_nonce' );

		$check_instance = new WidgetFeatureCheck();

		$check_instance->run_check();

		$detected = $check_instance->detected();

		$results = [
			'detected'    => is_array( $detected ) && count( $detected['results'] ) > 0,
			'results'     => is_array( $detected ) ? $detected['results'] : [],
			'description' => is_array( $detected ) ? $detected['description'] : '',
		];

		wp_send_json_success( $results );
	}

	/**
	 * Run the checks for plugins that use divi hooks.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function get_active_plugins_result_list() {
		et_core_security_check( 'edit_posts', 'et_d5_readiness_get_result_list', 'wp_nonce' );

		// Create an instance of Plugin_Hooks_Check.
		$plugin_hooks_check = new PluginHooksCheck();

		// Run the check.
		$plugin_hooks_check->run_check();

		// Get the results.
		$results = [
			'detected' => $plugin_hooks_check->detected(),
			'results'  => $plugin_hooks_check->get_detected_plugins(),
		];

		wp_send_json_success( $results );
	}

	/**
	 * Get the list of modules that are ready and not ready for D5 conversation.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function get_modules_conversation_status() {
		et_core_security_check( 'edit_posts', 'et_d5_readiness_modules_status', 'wp_nonce' );

		$modules_status = Helpers\get_modules_conversation_status();

		wp_send_json_success( $modules_status );
	}
}
