<?php
/**
 * Class that handles D5 Readiness admin page.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server;

use Divi\D5_Readiness\Helpers;

/**
 * Class that handles D5 Readiness admin page.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class AdminPage {
	/**
	 * Add the d5-readiness submenu menu item.
	 *
	 * @since ??
	 *
	 * @return string|false
	 */
	public static function add_admin_submenu_item() {
		return add_submenu_page(
			'et_divi_options',
			esc_html__( 'Divi 5 Migrator', 'Divi' ),
			0 === Helpers\get_incompatible_items_count()
				? esc_html__( 'Divi 5 Migrator', 'Divi' )
				: esc_html__( 'Divi 5 Migrator', 'Divi' ) . ' <span class="awaiting-mod">' . Helpers\get_incompatible_items_count() . '</span>',
			'manage_options',
			'et_d5_readiness',
			array( '\Divi\D5_Readiness\Server\AdminPage', 'render_page' ),
			1
		);
	}

	/**
	 * Render the D5 Readiness page.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public static function render_page() {
		?>
		<div id="et-d5-readiness"></div>
		<?php
	}


	/**
	 * Load the d5-readiness scripts.
	 *
	 * @since ??
	 *
	 * @param bool $enqueue_prod_scripts Whether to enqueue the production scripts.
	 * @param bool $skip_react_loading Whether to skip the React loading.
	 *
	 * @return void
	 */
	public static function load_js( $enqueue_prod_scripts = true, $skip_react_loading = false ) {
		if ( defined( 'ET_BUILDER_PLUGIN_ACTIVE' ) ) {
			if ( ! defined( 'ET_D5_READINESS_URI' ) ) {
				define( 'ET_D5_READINESS_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
			}

			if ( ! defined( 'ET_D5_READINESS_DIR' ) ) {
				define( 'ET_D5_READINESS_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			}
		} else {
			if ( ! defined( 'ET_D5_READINESS_URI' ) ) {
				define( 'ET_D5_READINESS_URI', get_template_directory_uri() . '/d5-readiness' );
			}

			if ( ! defined( 'ET_D5_READINESS_DIR' ) ) {
				define( 'ET_D5_READINESS_DIR', get_template_directory() . '/d5-readiness' );
			}
		}

		$core_version = defined( 'ET_CORE_VERSION' ) ? ET_CORE_VERSION : '';
		$et_debug     = defined( 'ET_DEBUG' ) && ET_DEBUG;
		$debug        = $et_debug;

		$home_url       = wp_parse_url( get_site_url() );
		$build_dir_uri  = ET_D5_READINESS_URI . '/build';
		$common_scripts = ET_COMMON_URL . '/scripts';
		$cache_buster   = $debug ? wp_rand() / mt_getrandmax() : $core_version;
		$asset_path     = ET_D5_READINESS_DIR . '/build/et-d5-readiness.bundle.js';

		if ( file_exists( $asset_path ) ) {
			wp_enqueue_style( 'et-d5-readiness-styles', "{$build_dir_uri}/et-d5-readiness.bundle.css", [], (string) $cache_buster );
		}

		wp_enqueue_script( 'es6-promise', "{$common_scripts}/es6-promise.auto.min.js", [], '4.2.2', true );

		$bundle_deps = [
			'jquery',
			'react',
			'react-dom',
			'es6-promise',
		];

		wp_dequeue_script( 'react' );
		wp_dequeue_script( 'react-dom' );
		wp_deregister_script( 'react' );
		wp_deregister_script( 'react-dom' );

		$react_version  = '18.2.0';
		wp_enqueue_script( 'react', "https://cdn.jsdelivr.net/npm/react@{$react_version}/umd/react.development.js", array(), $react_version, true );
		wp_enqueue_script( 'react-dom', "https://cdn.jsdelivr.net/npm/react-dom@{$react_version}/umd/react-dom.development.js", array( 'react' ), $react_version, true );
		add_filter( 'script_loader_tag', 'et_core_add_crossorigin_attribute', 10, 3 );

		if ( $debug || $enqueue_prod_scripts || file_exists( $asset_path ) ) {
			$bundle_uri = ! file_exists( $asset_path ) ? "{$home_url['scheme']}://{$home_url['host']}:31497/et-d5-readiness.bundle.js" : "{$build_dir_uri}/et-d5-readiness.bundle.js";

			wp_enqueue_script( 'et-d5-readiness', $bundle_uri, $bundle_deps, (string) $cache_buster, true );
			wp_localize_script( 'et-d5-readiness', 'et_d5_readiness_data', self::get_settings() );
		}
	}

	/**
	 * ET_D5_Readiness helpers.
	 *
	 * @since ??
	 */
	public static function get_settings() {
		if ( ! defined( 'ET_D5_READINESS_DIR' ) ) {
			define( 'ET_D5_READINESS_DIR', get_template_directory() . '/d5-readiness' );
		}

		$images_url = ET_D5_READINESS_URI . '/images';

		return array(
			'i18n'                        => [
				'conversionSummary' => require ET_D5_READINESS_DIR . '/i18n/conversionSummary.php',
				'dashboard'         => require ET_D5_READINESS_DIR . '/i18n/dashboard.php',
				'hero'              => require ET_D5_READINESS_DIR . '/i18n/hero.php',
				'roadmap'           => require ET_D5_READINESS_DIR . '/i18n/roadmap.php',
			],
			'ajaxurl'                     => is_ssl() ? admin_url( 'admin-ajax.php' ) : admin_url( 'admin-ajax.php', 'http' ),
			'product_version'             => ET_BUILDER_PRODUCT_VERSION,
			'images_url'                  => $images_url,
			'nonces'                      => [
				'et_d5_readiness_nonce'                  => wp_create_nonce( 'et_d5_readiness_nonce' ),
				'et_d5_readiness_overview_status'        => wp_create_nonce( 'et_d5_readiness_overview_status' ),
				'et_d5_readiness_get_result_list'        => wp_create_nonce( 'et_d5_readiness_get_result_list' ),
				'et_d5_readiness_convert_d4_to_d5_nonce' => wp_create_nonce( 'et_d5_readiness_convert_d4_to_d5_nonce' ),
				'et_d5_readiness_prepare_rollback_ids'   => wp_create_nonce( 'et_d5_readiness_prepare_rollback_ids' ),
				'et_d5_readiness_rollback_d5_to_d4'      => wp_create_nonce( 'et_d5_readiness_rollback_d5_to_d4' ),
				'et_d5_readiness_modules_status'         => wp_create_nonce( 'et_d5_readiness_modules_status' ),
			],
			'postTypes'                   => PostTypes::get_post_types(),
			'roadmap_items'               => Helpers\get_cached_roadmap_items(),
			'rollback_needed'             => Helpers\is_rollback_needed(),
			'conversion_finished'         => Helpers\is_conversion_finished(),
		);
	}
}
