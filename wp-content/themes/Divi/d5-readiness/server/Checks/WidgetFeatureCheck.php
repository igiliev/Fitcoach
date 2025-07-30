<?php
/**
 * Class for checking if widget areas containst shortcode-based module that is not compatible with Divi 5.
 *
 * @since ??
 *
 * @package D5_Readiness
 */

namespace Divi\D5_Readiness\Server\Checks;

use Divi\D5_Readiness\Helpers;
use Divi\D5_Readiness\Server\Checks\FeatureCheck;
use ET\Builder\FrontEnd\Assets\DetectFeature;

/**
 * Class for checking if widget areas containst shortcode-based module that is not compatible with Divi 5.
 *
 * @since ??
 *
 * @package D5_Readiness
 */
class WidgetFeatureCheck extends FeatureCheck {

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
	 * Constructor.
	 *
	 * @since ??
	 */
	public function __construct() {
		$this->_feature_name = __( 'Shortcodes In Widget Area Use', 'et_builder' );
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
	 * Keep track of used modules names from every widget content.
	 *
	 * @param string $post_content       The widget content.
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
	 * Get Module Name from Registered Modules.
	 *
	 * @since ??
	 *
	 * @param string $slug The module slug.
	 *
	 * @return string $results Comma separated list of shortcode names found in widget areas.
	 */
	protected static function _get_module_name_from_slug( $slug ) {
		return isset( self::$_modules[ $slug ] ) ? self::$_modules[ $slug ]->name : $slug;
	}

	/**
	 * Detect and process shortcodes found in widget areas.
	 *
	 * @since ??
	 *
	 * @param string $content The content to check for shortcodes.
	 *
	 * @return string $results Comma separated list of shortcode names found in widget areas.
	 */
	protected static function _get_module_names_from_content( $content ) {
		// bail if content is emoty.
		if ( empty( $content ) ) {
			return '';
		}

		$shortcode_slugs = DetectFeature::get_shortcode_names( $content );

		$shortcode_names_found = [];
		foreach ( $shortcode_slugs as $shortcode_slug ) {
			if ( 0 === strpos( $shortcode_slug, 'et_pb_wc' ) ) {
				$shortcode_names_found[] = self::_get_module_name_from_slug( $shortcode_slug );
			}
		}

		return implode( ', ', $shortcode_names_found );
	}

	/**
	 * Get sidebar name by ID.
	 *
	 * @since ??
	 *
	 * @param string $sidebar_id The sidebar ID.
	 *
	 * @return string|null The sidebar name if found, null otherwise.
	 */
	protected static function _get_sidebar_name_by_id( $sidebar_id ) {
		global $wp_registered_sidebars;

		if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
			return $wp_registered_sidebars[ $sidebar_id ]['name'];
		}

		if ( 'wp_inactive_widgets' === $sidebar_id ) {
			return __( 'Inactive Widgets', 'et_builder' );
		}

		return null;
	}

	/**
	 * Check widget areas for any shortcode usage.
	 *
	 * @since ??
	 *
	 * @return array|bool Array with results if any shortcode usage was detected, false otherwise.
	 */
	protected function _check_widget_areas() {
		$sidebars_widgets = wp_get_sidebars_widgets();
		$widget_areas     = [];
		$widget_types     = [];

		$used_modules_names = self::_get_initial_used_modules_names();

		foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
			$sidebar_name = self::_get_sidebar_name_by_id( $sidebar_id );

			// Get the widget's content, so we can check for shortcodes.
			foreach ( $widgets as $widget_id ) {
				$widget_id_parts = explode( '-', $widget_id );
				$widget_type     = $widget_id_parts[0];
				$widget_id_real  = $widget_id_parts[1];

				if ( empty( $widget_types[ $widget_type ] ) ) {
					$widget_type_content          = get_option( 'widget_' . $widget_type );
					$widget_types[ $widget_type ] = $widget_type_content;
				}

				$widget_content = $widget_types[ $widget_type ][ $widget_id_real ];

				$used_modules_names = self::_get_used_modules_name_from_content( $widget_content['content'], $used_modules_names );

				if ( ! empty( $widget_content['content'] ) ) {
					if ( strpos( $widget_content['content'], '[et_pb_wc_' ) !== false ) {
						$widget_areas[] = sprintf(
							__( 'Sidebar: %1$s, contains the following Divi WooCommerce module(s): %2$s', 'et_builder' ),
							$sidebar_name,
							self::_get_module_names_from_content( $widget_content['content'] )
						);
					}

					if ( ! empty( self::$_third_party_module_slugs ) ) {
						foreach ( self::$_third_party_module_slugs as $third_party_module ) {
							if ( strpos( $widget_content['content'], $third_party_module['slug'] ) !== false ) {
								$widget_areas[] = sprintf(
									__( 'Sidebar: %1$s contains third party module(s): %2$s', 'et_builder' ),
									$sidebar_name,
									self::_get_module_name_from_slug( $third_party_module['slug'] )
								);
							}
						}
					}
				}
			}
		}

		Helpers\readiness_update_used_modules_names( $used_modules_names );

		if ( empty( $widget_areas ) ) {
			return false;
		}

		$results = [
			'detected'    => true,
			'results'     => $widget_areas,
			'description' => 'Shortcode usage found in sidebars: ' . implode( '. ', $widget_areas ),
		];

		return $results;
	}

	/**
	 * Run widget check.
	 *
	 * @since ??
	 */
	public function run_check() {
		// Load Divi shortcode framework, so we can check for shortcodes.
		et_load_shortcode_framework();

		// Execute actions where third party modules are initialized.
		// Without this the third party modules hasn't registered and list of third party modules hasn't been populated.
		do_action( 'divi_extensions_init' );
		do_action( 'et_builder_ready' );

		self::$_modules = \ET_Builder_Element::get_modules();

		// Get list of third party module slugs.
		self::$_third_party_module_slugs = \ET_Builder_Element::get_third_party_modules();

		$this->_detected = $this->_check_widget_areas();
	}
}
