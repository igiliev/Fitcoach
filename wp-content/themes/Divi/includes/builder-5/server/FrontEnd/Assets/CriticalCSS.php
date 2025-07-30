<?php
/**
 * Extract Critical CSS
 *
 * @package Divi
 *
 * @since ??
 */

namespace ET\Builder\FrontEnd\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\FrontEnd\BlockParser\BlockParser;
use ET\Builder\FrontEnd\BlockParser\BlockParserBlock;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Critical CSS class.
 * Perform content analysis that marks that content should considered `above the fold` or `below the fold`.
 *
 * @since ??
 */
class CriticalCSS implements DependencyInterface {
	// Include in Critical CSS the Required Assets (those which don't depends on Content).
	// To force some of the Required Assets in the BTF, check `maybe_defer_global_asset` method.
	const INCLUDE_REQUIRED = true;

	// Used to estimate height for percentage based units like `vh`,`em`, etc.
	const VIEWPORT_HEIGHT = 1000;
	const FONT_HEIGHT     = 16;

	// Activate / deactivate Critical CSS debugging.
	// When set to `true`, a colored box will be displayed on the top of the section to indicate whether
	// it is above or below the fold.
	const DEBUG = false;

	/**
	 * Root element.
	 *
	 * @var \stdClass
	 */
	protected $_root;

	/**
	 * Modules.
	 *
	 * @var array
	 */
	protected $_modules = [];

	/**
	 * ATF/BTF Content.
	 *
	 * @var \stdClass
	 */
	protected $_content;

	/**
	 * Above The Fold Sections.
	 *
	 * @var array
	 */
	protected $_atf_sections = [];

	/**
	 * Builder Style Manager.
	 *
	 * @var array
	 */
	protected $_builder_styles = [];

	/**
	 * Keeping track whether current module is above the fold or not.
	 *
	 * @var bool
	 */
	private static $_above_the_fold = true;

	/**
	 * (current) section's horizontal offset.
	 * This value is accumulated based on accumulation of previous sections' offset + current section's height.
	 *
	 * @var int
	 */
	protected $_section_horizontal_offset = 0;

	/**
	 * Critical CSS' height threshold.
	 *
	 * @var int
	 */
	protected $_above_the_fold_height;

	/**
	 * Module defaults.
	 * Once a module defaults are retrieved from ModuleRegistration class, the copy of the default attributes
	 * are kept here so it doesn't need to keep retrieving value from MdouleRegistration.
	 *
	 * @var array
	 */
	protected $_module_defaults = [];

	/**
	 * Module id of current section that is being measured.
	 *
	 * @var string
	 */
	protected $_current_section = '';

	/**
	 * Current section type that is being measured.
	 *
	 * @var string
	 */
	protected $_current_section_type = 'regular';

	/**
	 * Module id of current row that is being measured.
	 *
	 * @var string
	 */
	protected $_current_row = '';

	/**
	 * Module id of current column that is being measured.
	 *
	 * @var string
	 */
	protected $_current_column = '';

	/**
	 * Module id of current row-inner that is being measured.
	 *
	 * @var string
	 */
	protected $_current_row_inner = '';

	/**
	 * Module id of current column-inner that is being measured.
	 *
	 * @var string
	 */
	protected $_current_column_inner = '';

	/**
	 * Counter for above the fold section. This counter is later used to split parsed block into above the fold and
	 * below the fold parsed blocks.
	 *
	 * @var int section counter.
	 */
	protected $_above_the_fold_section_counter = 0;

	/**
	 * Kept copies of properties that is necessary for dynamic assets calculation.
	 * This needs to be copied because the timing of execution is later than usual class property reset.
	 *
	 * @var array dynamic assets properties.
	 */
	protected $_dynamic_assets = [
		'parsed_blocks'                  => [],
		'above_the_fold_section_counter' => 0,
	];

	/**
	 * Accumulated data regarding the layout that is being measured by Critical CSS.
	 *
	 * @var array
	 */
	protected $_layout_data = [];

	/**
	 * List of rendered modules.
	 *
	 * @var array
	 */
	protected $_rendered_modules = [
		'all'            => [],
		'above_the_fold' => [],
		'below_the_fold' => [],
	];

	/**
	 * Load Critical CSS class.
	 *
	 * @since ??
	 */
	public function load() {
		global $shortname;

		// Exit early if critical CSS is not enabled (eg. disabled via Theme Options).
		if ( ! self::is_enabled() ) {
			return;
		}

		// Enable Critical CSS generation flag.
		add_filter( 'divi_frontend_assets_critical_css_should_generate_critical_css', '__return_true' );

		// Analyze Builder style manager.
		add_filter( 'divi_frontend_assets_static_css_module_style_manager', [ $this, 'enable_builder' ] );

		// Dynamic CSS content block.
		add_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', [ $this, 'maybe_defer_global_asset' ], 99 );

		if ( self::INCLUDE_REQUIRED ) {
			add_filter( 'divi_frontend_assets_dynamic_assets_atf_includes_required', '__return_true' );
		}

		// Define Critical CSS threshold height based on the height chosen on Theme Options.
		if ( et_is_builder_plugin_active() ) {
			$options                   = get_option( 'et_pb_builder_options', array() );
			$critical_threshold_height = $options['performance_main_critical_threshold_height'] ?? 'Medium';
		} else {
			$critical_threshold_height = et_get_option( $shortname . '_critical_threshold_height', 'Medium' );
		}

		if ( 'High' === $critical_threshold_height ) {
			$this->_above_the_fold_height = 1500;
		} elseif ( 'Medium' === $critical_threshold_height ) {
			$this->_above_the_fold_height = 1000;
		} else {
			$this->_above_the_fold_height = 500;
		}

		// Populate layout data AND measure above the fold status.
		// This needs to be done on `render_block_data` level because at this point, the process performed from
		// top to bottom such as section > row > column > module. Block rendering actually done from bottom to
		// top such as module > column > row > section.
		add_filter( 'render_block_data', [ $this, 'populate_layout_data' ], 2 );

		// Measure section height.
		// Above / below the fold status is being decided on section level. Once a section is considered passing
		// the above the fold treshold, the section, all of its children and the next sections are considered
		// below the fold content.
		add_filter( 'render_block_divi/section', [ $this, 'measure_section_height' ], 2, 3 );

		// Detect when rendering Above The Fold sections for shortcode content .
		add_filter( 'pre_do_shortcode_tag', [ $this, 'check_section_start' ], 98, 4 );
		add_filter( 'do_shortcode_tag', [ $this, 'check_section_end' ], 99, 2 );

		// Pass list of modules that are rendered above the fold for dynamic assets.
		add_filter( 'divi_frontend_assets_dynamic_assets_modules_atf', [ $this, 'get_above_the_fold_modules_for_dynamic_assets' ], 10, 2 );

		if ( self::DEBUG ) {
			// Render debug box information on section level.
			add_filter( 'render_block_divi/section', [ $this, 'render_section_debug_info' ], 2, 3 );
		}
	}

	/**
	 * Analyze Builder style manager.
	 *
	 * @since ??
	 *
	 * @param array $styles Style Managers.
	 *
	 * @return array
	 */
	public function enable_builder( array $styles ): array {
		$this->_builder_styles = $styles;

		// There are cases where external assets generation might be disabled at runtime,
		// ensure Critical CSS and Dynamic Assets use the same logic to avoid side effects.
		if ( ! DynamicAssetsUtils::should_generate_dynamic_assets() ) {
			$this->disable();
			return $styles;
		}

		add_filter( 'et_core_page_resource_force_write', [ $this, 'force_resource_write' ], 10, 2 );
		add_filter( 'et_core_page_resource_tag', [ $this, 'builder_style_tag' ], 10, 5 );

		if ( et_builder_is_mod_pagespeed_enabled() ) {
			// PageSpeed filters out `preload` links so we gotta use `prefetch` but
			// Safari doesn't support the latter....
			add_action( 'wp_body_open', [ $this, 'add_safari_prefetch_workaround' ], 1 );
		}

		return $styles;
	}

	/**
	 * Disable Critical CSS.
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function disable() {
		remove_filter( 'divi_frontend_assets_critical_css_should_generate_critical_css', '__return_true' );
		remove_filter( 'divi_frontend_assets_static_css_module_style_manager', [ $this, 'enable_builder' ] );
		remove_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', [ $this, 'maybe_defer_global_asset' ] );
		remove_filter( 'divi_frontend_assets_dynamic_assets_modules_atf', [ $this, 'get_above_the_fold_modules_for_dynamic_assets' ] );
		remove_filter( 'render_block_data', [ $this, 'populate_layout_data' ] );
		remove_filter( 'render_block_divi/section', [ $this, 'measure_section_height' ] );
		remove_filter( 'pre_do_shortcode_tag', [ $this, 'check_section_start' ] );
		remove_filter( 'do_shortcode_tag', [ $this, 'check_section_end' ] );
	}

	/**
	 * Force a PageResource to write its content on file, even when empty
	 *
	 * @since ??
	 *
	 * @param bool   $force Default value.
	 * @param object $resource Critical/Deferred PageResources.
	 *
	 * @return bool
	 */
	public function force_resource_write( bool $force, object $resource ): bool {
		$styles = $this->_builder_styles;

		if ( empty( $styles ) ) {
			return $force;
		}

		$forced_slugs = [ $styles['manager']->slug ];

		if ( isset( $styles['deferred'] ) ) {
			$forced_slugs[] = $styles['deferred']->slug;
		}

		return in_array( $resource->slug, $forced_slugs, true ) ? true : $force;
	}

	/**
	 * Prints deferred Critical CSS stlyesheet.
	 *
	 * @since ??
	 *
	 * @param string $tag stylesheet template.
	 * @param string $slug stylesheet slug.
	 * @param string $scheme stylesheet URL.
	 * @param string $onload stylesheet onload attribute.
	 *
	 * @return string
	 */
	public function builder_style_tag( string $tag, string $slug, string $scheme, string $onload ): string {
		$deferred = $this->_builder_styles['deferred'] ?? null;
		$inlined  = $this->_builder_styles['manager'] ?? null;

		// reason: Stylesheet needs to be printed on demand.
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		// reason: Snake case requires refactor of PageResource.php.
		// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase
		if ( isset( $deferred ) && $slug === $deferred->slug ) {
			// Don't enqueue empty resources.
			if ( 0 === et_()->WPFS()->size( $deferred->path ) ) {
				return '';
			}

			// Use 'prefetch' when Mod PageSpeed is detected because it removes 'preload' links.
			$rel = et_builder_is_mod_pagespeed_enabled() ? 'prefetch' : 'preload';

			/**
			 * Filter deferred styles rel attribute.
			 *
			 * Mod PageSpeed removes 'preload' links and we attempt to fix that by trying to detect if
			 * the 'x-mod-pagespeed' (Apache) or 'x-page-speed' (Nginx) header is present and if it is,
			 * replace 'preload' with 'prefetch'. However, in some cases, like when the request goes through
			 * a CDN first, we are unable to detect the header. This hook can be used to change the 'rel'
			 * attribute to use 'prefetch' when our et_builder_is_mod_pagespeed_enabled() function fails
			 * to detect Mod PageSpeed.
			 *
			 * With that out of the way, the only reason I wrote this detailed description is to make Fabio proud.
			 *
			 * This filter is the replacement of Divi 4 filter `et_deferred_styles_rel`.
			 *
			 * @since ??
			 *
			 * @param string $rel
			 */
			$rel = apply_filters( 'divi_frontend_assets_ctitical_css_deferred_styles_rel', $rel );

			// Defer the stylesheet.
			$template = '<link rel="%4$s" as="style" id="%1$s" href="%2$s" onload="this.onload=null;this.rel=\'stylesheet\';%3$s" />';

			return sprintf( $template, $slug, $scheme, $onload, $rel );
		}

		if ( isset( $inlined ) && $slug === $inlined->slug ) {
			// Inline the stylesheet.
			$template = "<style id=\"et-critical-inline-css\">%1\$s</style>\n";
			$content  = et_()->WPFS()->get_contents( $inlined->path );

			return sprintf( $template, $content );
		}
		// phpcs:enable

		return $tag;
	}

	/**
	 * Safari doesn't support `prefetch`......
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function add_safari_prefetch_workaround() {
		// .... so we turn it into `preload` using JS.
		?>
		<script type="application/javascript">
			(function() {
				var relList = document.createElement('link').relList;
				if (!!(relList && relList.supports && relList.supports('prefetch'))) {
					// Browser supports `prefetch`, no workaround needed.
					return;
				}

				var links = document.getElementsByTagName('link');
				for (var i = 0; i < links.length; i++) {
					var link = links[i];
					if ('prefetch' === link.rel) {
						link.rel = 'preload';
					}
				}
			})();
		</script>
		<?php
	}

	/**
	 * Check if Critical CSS is enabled based on specific conditions.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		if (
			Conditions::is_rest_api_request() ||
			Conditions::is_ajax_request() ||
			Conditions::is_vb_top_window() ||
			Conditions::is_vb_enabled()
		) {
			return false;
		}

		return et_builder_is_critical_enabled();
	}

	/**
	 * Checks if Critical CSS should be generated or not.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function should_generate_critical_css(): bool {
		// Initial value for the filter.
		$should_generate_critical_css = false;

		/**
		 * Filters whether Critical CSS should be generated or not.
		 *
		 * This filter is the replacement of Divi 4 filter `et_builder_critical_css_enabled`.
		 *
		 * @since ??
		 *
		 * @param bool $enabled Critical CSS enabled value.
		 */
		return (bool) apply_filters( 'divi_frontend_assets_critical_css_should_generate_critical_css', $should_generate_critical_css );
	}

	/**
	 * Populate layout data and measure above the fold status when section data is populated.
	 * This filter callback doesn't modify parsed block data; This only piggy-backing the filter due to its proper
	 * timing to measure whether the current section is considered above the fold or not.
	 *
	 * @since ??
	 *
	 * @param array $parsed_block Parsed block data.
	 *
	 * @return array
	 */
	public function populate_layout_data( array $parsed_block ): array {
		// If no `parentId` is found, this block isn't Divi 5 module thus it can be skipped.
		if ( empty( $parsed_block['parentId'] ) ) {
			return $parsed_block;
		}

		// Module name.
		$module_name = $parsed_block['blockName'];

		// Module Id.
		$module_id = $parsed_block['id'];

		// If no default attributes found on `_module_defaults` properties, set it.
		if ( ! isset( $this->_module_defaults[ $module_name ] ) ) {
			$this->_module_defaults[ $module_name ] = [
				'default_attrs'               => ModuleRegistration::get_default_attrs( $module_name, 'default' ),
				'default_printed_style_attrs' => ModuleRegistration::get_default_attrs( $module_name, 'defaultPrintedStyle' ),
			];
		}

		// Get module default attributes.
		$default_attrs               = $this->_module_defaults[ $module_name ]['default_attrs'] ?? [];
		$default_printed_style_attrs = $this->_module_defaults[ $module_name ]['default_printed_style_attrs'] ?? [];

		// Get module attributes.
		$attrs = $parsed_block['attrs'] ?? [];

		// Get margin values.
		$margin_top = self::get_spacing_value(
			'margin',
			'top',
			'',
			$attrs,
			$default_attrs,
			$default_printed_style_attrs
		);

		$margin_bottom = self::get_spacing_value(
			'margin',
			'bottom',
			'',
			$attrs,
			$default_attrs,
			$default_printed_style_attrs
		);

		// Check if currently parsed module is section.
		$is_section = 'divi/section' === $module_name;

		// Section type. Default value of different section type is different.
		$section_type = $attrs['module']['advanced']['type']['desktop']['value'] ?? 'regular';

		// Get default vertical padding.
		$default_vertical_padding = $is_section
			? ( 'fullwidth' === $section_type ? '' : '54px' )
			: '';

		// Get default vertical padding for Row. The value was set as default printed style attributes on Row module. But,
		// we decided to move it here as hardcoded value because it was printed as placeholder value on the padding settings
		// of Row module. It should be removed from there for better user experience and consistency with other modules.
		if ( 'divi/row' === $module_name ) {
			$default_vertical_padding = '27px';
		}

		// Get padding values.
		$padding_top = self::get_spacing_value(
			'padding',
			'top',
			$default_vertical_padding,
			$attrs,
			$default_attrs,
			$default_printed_style_attrs
		);

		$padding_bottom = self::get_spacing_value(
			'padding',
			'bottom',
			$default_vertical_padding,
			$attrs,
			$default_attrs,
			$default_printed_style_attrs
		);

		// (Assumed) Inner Height based on module name.
		$inner_height = self::get_assumed_module_inner_height( $module_name, 100 );

		// Spacing Height.
		// Structure module's inner height is accumulation of its children height. Thus structural
		// module saves its spacing height instead of total height on populate_layout data phase.
		// During section height measurement, the module height is calculated by addinng its inner height.
		$spacing_height = $margin_top + $padding_top + $padding_bottom + $margin_bottom;

		// Module Height.
		$module_height = $spacing_height + $inner_height;

		// Populate layout data.
		if ( 'divi/section' === $module_name ) {
			$this->_current_section = $module_id;

			$this->_current_section_type = $section_type;

			$this->_layout_data[ $module_id ] = [
				'children'       => [],
				'spacing_height' => $spacing_height,
				'type'           => $section_type,
			];
		} else {
			switch ( $this->_current_section_type ) {
				case 'fullwidth':
					// Do not populate child module data; the height is going to be assumed from the module's parent module only.
					if ( ModuleRegistration::is_child_module( $parsed_block['blockName'] ) ) {
						break;
					}

					$this->_layout_data[ $this->_current_section ]['children'][ $module_id ] = $module_height;
					break;

				case 'specialty':
					switch ( $module_name ) {
						case 'divi/column':
							// Set the current column ID for future nested blocks.
							$this->_current_column = $module_id;

							// Initialize the structure for the column with an empty children array and the calculated spacing height.
							$this->_layout_data[ $this->_current_section ]['children'][ $module_id ] = [
								'children'       => [],
								'spacing_height' => $spacing_height,
							];
							break;

						case 'divi/row-inner':
							// Set the current row-inner ID for future nested blocks.
							$this->_current_row_inner = $module_id;

							// Initialize the structure for the row-inner with an empty children array and the calculated spacing height.
							$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_column ]['children'][ $module_id ] = [
								'children'       => [],
								'spacing_height' => $spacing_height,
							];
							break;

						case 'divi/column-inner':
							// Set the current column-inner ID for future nested blocks.
							$this->_current_column_inner = $module_id;

							// Initialize the structure for the column-inner with an empty children array and the calculated spacing height.
							$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_column ]['children'][ $this->_current_row_inner ]['children'][ $module_id ] = [
								'children'       => [],
								'spacing_height' => $spacing_height,
							];
							break;

						default:
							// Check if the module is a child module (e.g., inside another parent module like a row or column).
							// If it is, we skip processing its height individually because it will be determined by its parent.
							if ( ModuleRegistration::is_child_module( $parsed_block['blockName'] ) ) {
								break;
							}

							// Skip placeholder blocks, which wrap global modules.
							if ( 'divi/placeholder' === $module_name ) {
								break;
							}

							// Determine the correct nesting level for this module.
							// If the module is nested in a column-inner, place it accordingly.
							if ( isset( $this->_current_column_inner ) && $this->_current_column_inner ) {
								$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_column ]['children'][ $this->_current_row_inner ]['children'][ $this->_current_column_inner ]['children'][ $module_id ] = $module_height;
							} elseif ( isset( $this->_current_row_inner ) && $this->_current_row_inner ) {
								// If the module is nested in a row-inner but not a column-inner, place it at the row-inner level.
								$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_column ]['children'][ $this->_current_row_inner ]['children'][ $module_id ] = $module_height;
							} else {
								// If the module is directly under a column, place it there.
								$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_column ]['children'][ $module_id ] = $module_height;
							}
							break;
					}
					break;

				default:
					if ( 'divi/row' === $module_name ) {
						$this->_current_row = $module_id;

						$this->_layout_data[ $this->_current_section ]['children'][ $module_id ] = [
							'children'       => [],
							'spacing_height' => $spacing_height,
						];
					} elseif ( 'divi/column' === $module_name ) {
						$this->_current_column = $module_id;

						$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_row ]['children'][ $module_id ] = [
							'children'       => [],
							'spacing_height' => $spacing_height,
						];
					} else {
						// Do not populate child module data; the height is going to be assumed from the module's parent module only.
						if ( ModuleRegistration::is_child_module( $parsed_block['blockName'] ) ) {
							break;
						}

						// Skip placeholder blocks, which wrap global modules.
						if ( 'divi/placeholder' === $module_name ) {
							break;
						}

						$this->_layout_data[ $this->_current_section ]['children'][ $this->_current_row ]['children'][ $this->_current_column ]['children'][ $module_id ] = $module_height;
					}
					break;
			}
		}

		// Measurement only took place when current module is `divi/section`.
		if ( 'divi/section' === $parsed_block['blockName'] ) {
			// Measure whether current module is above the fold or not.
			// Above the fold status measurement has to be done on render data hook because it happens from top to bottom.
			// This ensure the above the fold status has been updated when child module of the section gets the above
			// the fold status inside of it.
			self::$_above_the_fold = $this->_section_horizontal_offset < $this->_above_the_fold_height;

			// Update number of above the fold section.
			if ( self::$_above_the_fold ) {
				$this->_above_the_fold_section_counter++;
			}
		}

		// Keep track of rendered modules (all, above the folds, and below the folds) as reference for Dynamic Assets.
		if ( ! in_array( $module_name, $this->_rendered_modules['all'], true ) ) {
			$this->_rendered_modules['all'][] = $module_name;

			if ( self::$_above_the_fold ) {
				$this->_rendered_modules['above_the_fold'][] = $module_name;
			} else {
				$this->_rendered_modules['below_the_fold'][] = $module_name;
			}
		}

		return $parsed_block;
	}

	/**
	 * Get spacing value.
	 *
	 * @since ??
	 *
	 * @param string $property CSS property.
	 * @param string $side     CSS side.
	 * @param string $default_value Default value.
	 * @param array  $attrs Module attributes.
	 * @param array  $default_attrs Default attributes.
	 * @param array  $default_printed_style_attrs Default printed style attributes.
	 *
	 * @return int
	 */
	public static function get_spacing_value( string $property, string $side, string $default_value, array $attrs, array $default_attrs, array $default_printed_style_attrs ) {
		$default = $default_attrs['module']['decoration']['spacing']['desktop']['value'][ $property ][ $side ]
			?? $default_printed_style_attrs['module']['decoration']['spacing']['desktop']['value'][ $property ][ $side ]
			?? $default_value;

		$value = self::format_value( $attrs['module']['decoration']['spacing']['desktop']['value'][ $property ][ $side ] ?? $default );

		return $value;
	}

	/**
	 * Get column height based on column data.
	 *
	 * @since ??
	 *
	 * @param array $column_data Column data.
	 *
	 * @return int
	 */
	public static function get_column_height( array $column_data ): int {
		$column_spacing_height = $column_data['spacing_height'] ?? 0;

		$column_module_height = array_sum( $column_data['children'] );

		$column_height = $column_spacing_height + $column_module_height;

		return $column_height;
	}

	/**
	 * Get column height data based on column parents (row) data
	 *
	 * @since ??
	 *
	 * @param array $columns_data Columns data.
	 *
	 * @return array
	 */
	public static function get_column_height_data( array $columns_data = [] ): array {
		$column_height_data = [];

		foreach ( $columns_data as $column_id => $column_data ) {
			$column_height = self::get_column_height( $column_data );

			$column_height_data[ $column_id ] = $column_height;
		}

		return $column_height_data;
	}

	/**
	 * Get section children height.
	 *
	 * @since ??
	 *
	 * @param array $section_children Section children.
	 *
	 * @return int
	 */
	public static function get_section_children_height( array $section_children ): int {
		$section_children_height = 0;

		foreach ( $section_children as $row_data ) {

			$column_height_data = self::get_column_height_data( $row_data['children'] );

			$highest_column_height = max( $column_height_data );

			$row_height = ( $row_data['spacing_height'] ?? 0 ) + $highest_column_height;

			$section_children_height += $row_height;
		}

		return $section_children_height;
	}

	/**
	 * Filter callback for masuring section height.
	 * This needs to be done separately than `populate_layout_data` because the height measurement is done after
	 * section's children layout data has been populated. Structure module's inner height is basically accumulation
	 * of its children's height.
	 *
	 * @since ??
	 *
	 * @param string $block_content  The block content.
	 * @param array  $parsed_block   The full block, including name and attributes.
	 *
	 * @return string Block content.
	 */
	public function measure_section_height( string $block_content, array $parsed_block ): string {
		$module_id = $parsed_block['id'];

		$section_data = $this->_layout_data[ $module_id ] ?? [
			'children'       => [],
			'spacing_height' => 0,
			'type'           => 'regular',
		];

		$section_type = $section_data['type'] ?? 'regular';

		$section_spacing_height = $section_data['spacing_height'] ?? 0;
		$section_children       = $section_data['children'] ?? [];

		switch ( $section_type ) {
			case 'specialty':
				$specialty_section_data = [];

				foreach ( $section_data['children'] as $column_id => $column_data ) {
					$column_children_names = array_keys( $column_data['children'] );

					$first_column_children_name = $column_children_names[0] ?? '';

					if ( false !== strpos( $first_column_children_name, 'divi/row-inner' ) ) {
						$column_height = self::get_section_children_height( $column_data['children'] );

						$specialty_section_data[ $column_id ] = $column_height;
					} else {
						$column_height = self::get_column_height( $column_data );

						$specialty_section_data[ $column_id ] = $column_height;
					}
				}

				$section_height = $section_spacing_height + max( $specialty_section_data );
				break;

			case 'fullwidth':
				$section_height = $section_spacing_height + array_sum( $section_children );
				break;

			default:
				$section_height = $section_spacing_height + self::get_section_children_height( $section_children );
				break;
		}

		$this->_layout_data[ $module_id ]['height'] = $section_height;

		$this->_section_horizontal_offset = $this->_section_horizontal_offset + $section_height;

		return $block_content;
	}

	/**
	 * Render section debug information.
	 * Optional UI that display debug information that is only being rendered when `DEBUG` constant is set to `true`.
	 *
	 * @since ??
	 *
	 * @param string $block_content  The block content.
	 * @param array  $parsed_block   The full block, including name and attributes.
	 *
	 * @return string Modified block content.
	 */
	public function render_section_debug_info( string $block_content, array $parsed_block ): string {
		// Debug box style.
		$background_color = self::$_above_the_fold ? 'rgba(0, 255, 0, 0.8)' : 'rgba(255, 0, 0, 0.8)';
		$info_style       = 'background: ' . $background_color . '; color: #FFFFFF; font-size: 12px; line-height: 1; padding: 10px; position: absolute; transform: translateY(-100%) translateX(5%); width: 90%; z-index: 10;';

		// Debug information.
		$status_label   = self::$_above_the_fold ? 'Above The Fold' : 'Below The Fold';
		$status         = '<p>' . $status_label . '</p>';
		$section_offset = '<p>Section Offset: ' . $this->_section_horizontal_offset . '</p>';
		$above_the_hold = '<p>Above The Fold Limit: ' . $this->_above_the_fold_height . '</p>';
		$section_height = '<p>Section Total Height: ' . $this->_layout_data[ $parsed_block['id'] ]['height'] . '</p>';

		// Debug box.
		$info = '<div style="' . $info_style . '">' . $status . $section_offset . $above_the_hold . $section_height . '</div>';

		return $block_content . $info;
	}

	/**
	 * Format CSS property value in string with unit to integer.
	 *
	 * @since ??
	 *
	 * @param string $value CSS property value.
	 *
	 * @return int
	 */
	public static function format_value( string $value ): int {
		if ( empty( $value ) ) {
			return 0;
		}

		$unit = et_pb_get_value_unit( $value );

		// Remove the unit, if present.
		if ( false !== strpos( $value, $unit ) ) {
			$value = substr( $value, 0, -strlen( $unit ) );
		}

		$value = (int) $value;

		switch ( $unit ) {
			case 'rem':
			case 'em':
				$value *= self::FONT_HEIGHT;
				break;
			case 'vh':
				$value = ( $value * self::VIEWPORT_HEIGHT ) / 100;
				break;
			case 'px':
				break;
			default:
				$value = 0;
		}

		return $value;
	}

	/**
	 * Check if current module is above the fold.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function is_above_the_fold(): bool {
		return self::$_above_the_fold;
	}

	/**
	 * Get assumed module inner height based on module name.
	 * The assumed module inner height (module's height without spacing and margin values) is based on the rendered
	 * module with placeholder values as its content being rendered in 4_4-sized column.
	 *
	 * TODO feat(D5, Refactor) Move these assumed inner height value to `module.json` to make it more modular.
	 *
	 * @since ??
	 *
	 * @param string $module_name Module name.
	 * @param int    $default_value Default value.
	 *
	 * @return int
	 */
	public static function get_assumed_module_inner_height( string $module_name, int $default_value = 100 ): int {
		// Value for the filter.
		$assumed_module_heights = [

			// Modules.
			'divi/accordion'                   => 213,
			'divi/audio'                       => 176,
			'divi/blog'                        => 2153,
			'divi/blurb'                       => 404,
			'divi/button'                      => 190,
			'divi/circle-counter'              => 272,
			'divi/code'                        => 23,
			'divi/comments'                    => 100,
			'divi/contact-form'                => 296,
			'divi/countdown-timer'             => 135,
			'divi/counters'                    => 141,
			'divi/cta'                         => 103,
			'divi/divider'                     => 23,
			'divi/filterable-portfolio'        => 952,
			'divi/gallery'                     => 320,

			// `divi/heading`'s inner height with placeholder content is `40`. However most of the heading used seems to be
			// higher than this, so it is being rounded up to `100`.
			'divi/heading'                     => 100,
			'divi/icon'                        => 96,

			// `divi/image`'s inner height is actually `539`. However most of the image used seems to be not this
			// high than this, so it is being divided by 2 into `269`.
			'divi/image'                       => 269,
			'divi/login'                       => 208,
			'divi/map'                         => 440,
			'divi/menu'                        => 30,
			'divi/number-counter'              => 120,
			'divi/portfolio'                   => 853,
			'divi/post-nav'                    => 23,
			'divi/post-slider'                 => 576,
			'divi/post-title'                  => 75,
			'divi/pricing-tables'              => 539,
			'divi/search'                      => 39,
			'divi/sidebar'                     => 319,
			'divi/signup'                      => 228,
			'divi/slider'                      => 477,
			'divi/social-media-follow'         => 40,
			'divi/tabs'                        => 128,
			'divi/team-member'                 => 320,
			'divi/testimonial'                 => 109,
			'divi/text'                        => 47,
			'divi/toggle'                      => 16,
			'divi/video'                       => 607,
			'divi/video-slider'                => 819,

			// Fullwidth Modules.
			'divi/fullwidth-code'              => 23,
			'divi/fullwidth-header'            => 177,
			'divi/fullwidth-image'             => 674,
			'divi/fullwidth-map'               => 440,
			'divi/fullwidth-menu'              => 76,
			'divi/fullwidth-portfolio'         => 252,
			'divi/fullwidth-post-slider'       => 576,
			'divi/fullwidth-post-title'        => 75,
			'divi/fullwidth-slider'            => 553,

			// Child Modules.
			'divi/accordion-item'              => 83,
			'divi/contact-field'               => 51,
			'divi/counter'                     => 40,
			'divi/map-pin'                     => 0,
			'divi/pricing-table'               => 539,
			'divi/signup-custom-field'         => 51,
			'divi/slide'                       => 576,
			'divi/social-media-follow-network' => 32,
			'divi/tab'                         => 47,
			'divi/video-slider-item'           => 607,
		];

		/**
		 * Filters assumed block inner heights.
		 *
		 * This filter is the replacement of Divi 4 filter `et_builder_assumed_module_inner_heights`.
		 *
		 * @since ??
		 *
		 * @param string $rel
		 */
		$assumed_module_heights = apply_filters(
			'divi_frontend_assets_ctitical_css_assumed_block_inner_heights',
			$assumed_module_heights
		);

		return $assumed_module_heights[ $module_name ] ?? $default_value;
	}

	/**
	 * Get modules that are rendered above the fold for dynamic assets.
	 * The "For Dynamic Assets" part needs to be highlighted. This callback is hooked at `et_dynamic_assets_modules_atf`
	 * which happens so early, even before `render_block_data` filter. Thus this callback does pre-parse of the content
	 * to retrieve list of modules that are being rendered above the fold using actual parser, then reset
	 * BlockParserBlock's reset index so it doesn't affect module ordering of actual render process.
	 *
	 * @since ??
	 *
	 * @param array  $value Default Blocks list (empty).
	 * @param string $content TB/Post Content.
	 *
	 * @return array List of ATF Blocks.
	 */
	public function get_above_the_fold_modules_for_dynamic_assets( array $value, string $content = '' ): array {
		if ( empty( $content ) ) {
			return $value;
		}

		// Divi 5 block parser.
		$parser = new BlockParser();

		// Sometimes empty line is parsed as block with empty property values. It might affecting above the fold
		// calculation thus before proceeding, verified the parsed blocks first.
		$unverified_parsed_blocks = $parser->parse( $content );

		$parsed_blocks = [];

		foreach ( $unverified_parsed_blocks as $pre_parsed_block ) {
			if ( isset( $pre_parsed_block['orderIndex'] ) ) {
				$parsed_blocks[] = $pre_parsed_block;
			}
		}

		// Set copy of the parsed blocks for dynamic assets calculation.
		$this->_dynamic_assets['parsed_blocks'] = $parsed_blocks;

		$this->populate_dynamic_assets_layout_data( $parsed_blocks );

		// Get modules that are being rendered above the fold.
		$above_the_fold_modules = $this->_rendered_modules['above_the_fold'];

		// Once above the fold calculation is done, keep a copy of section counter for dynamic assets calculation.
		$this->_dynamic_assets['above_the_fold_section_counter'] = $this->_above_the_fold_section_counter;

		// Reset block parser store and block parser block order index so it doesn't affect the actual parse / render of
		// the layout.
		BlockParserStore::reset();
		BlockParserBlock::reset_order_index();

		// Reset class' properties Dynamic Assets calculation so it doesn't affect the actual parse / render of the layout.
		self::$_above_the_fold                 = true;
		$this->_section_horizontal_offset      = 0;
		$this->_module_defaults                = [];
		$this->_current_section                = '';
		$this->_current_section_type           = 'regular';
		$this->_current_row                    = '';
		$this->_current_column                 = '';
		$this->_current_row_inner              = '';
		$this->_current_column_inner           = '';
		$this->_layout_data                    = [];
		$this->_rendered_modules               = [
			'all'            => [],
			'above_the_fold' => [],
			'below_the_fold' => [],
		];
		$this->_above_the_fold_section_counter = 0;

		if ( ! empty( $above_the_fold_modules ) ) {
			// Register callback to return above and below the fold modules (in form of blocks) for dynamic assets.
			add_filter( 'divi_frontend_assets_dynamic_assets_content', [ $this, 'get_above_and_below_the_fold_modules_for_dynamic_assets' ] );

			return $above_the_fold_modules;
		}

		// When there are no blocks found, calculate ATF/BTF based on shortcode, if any.
		$above_the_fold_modules = $this->extract( $content );

		if ( ! empty( $above_the_fold_modules ) ) {
			// Register callback to return above and below the fold modules (in form of shortcode) for dynamic assets.
			add_filter( 'divi_frontend_assets_dynamic_assets_content', [ $this, 'dynamic_assets_content' ] );
		}

		return $above_the_fold_modules;
	}

	/**
	 * Get above and below the fold modules for dynamic assets.
	 *
	 * @return object above the fold and bellow the fold content.
	 *
	 * @since ??
	 */
	public function get_above_and_below_the_fold_modules_for_dynamic_assets(): object {
		// Slice parsed blocks into above and below the fold blocks.
		$above_the_fold_parsed_blocks = array_slice( $this->_dynamic_assets['parsed_blocks'], 0, $this->_dynamic_assets['above_the_fold_section_counter'] );
		$below_the_fold_parsed_blocks = array_slice( $this->_dynamic_assets['parsed_blocks'], $this->_dynamic_assets['above_the_fold_section_counter'] );

		// Once splitting is done, reset the value.
		$this->_dynamic_assets = [
			'parsed_blocks'                  => [],
			'above_the_fold_section_counter' => 0,
		];

		return (object) [
			'atf' => serialize_blocks( $above_the_fold_parsed_blocks ),
			'btf' => serialize_blocks( $below_the_fold_parsed_blocks ),
		];
	}

	/**
	 * Populate dynamic assets layout data by processing parsed block object.
	 *
	 * @since ??
	 *
	 * @param array $parsed_blocks Parsed blocks.
	 */
	public function populate_dynamic_assets_layout_data( array $parsed_blocks ) {
		foreach ( $parsed_blocks as $parsed_block ) {
			$this->populate_layout_data( $parsed_block );

			$inner_blocks = $parsed_block['innerBlocks'] ?? [];

			if ( ! empty( $inner_blocks ) ) {
				$this->populate_dynamic_assets_layout_data( $inner_blocks );
			}

			if ( 'divi/section' === $parsed_block['blockName'] ) {
				$this->measure_section_height( '', $parsed_block );
			}
		}
	}

	/**
	 * Defer some global assets if threshold is met.
	 *
	 * @since 4.10.0
	 *
	 * @param array $assets assets to defer.
	 *
	 * @return array $assets assets to be deferred.
	 */
	public function maybe_defer_global_asset( array $assets ): array {
		$defer = [
			'et_divi_footer',
			'et_divi_gutters_footer',
			'et_divi_comments',
		];

		foreach ( $defer as $key ) {
			if ( isset( $assets[ $key ] ) ) {
				$assets[ $key ]['maybe_defer'] = true;
			}
		}

		return $assets;
	}

	/**
	 * Returns splitted (ATF/BFT) Content based on shortcode.
	 *
	 * @since ??
	 *
	 * @return \stdClass
	 */
	public function dynamic_assets_content() {
		return $this->_content;
	}

	/**
	 * While the filter is applied, any rendered style will be considered critical based on shortcode.
	 *
	 * @since ??
	 *
	 * @param array $style Style.
	 *
	 * @return array
	 */
	public function set_style( array $style ) {
		$style['critical'] = true;
		return $style;
	}

	/**
	 * Add `set_style` filter when rendering an ATF section based on shortcode.
	 *
	 * @since ??
	 *
	 * @param false|string $value Short-circuit return value. Either false or the value to replace the shortcode with.
	 * @param string       $tag   Shortcode name.
	 * @param array|string $attr  Shortcode attributes array or empty string.
	 * @param array        $m     Regular expression match array.
	 *
	 * @return false|string
	 */
	public function check_section_start( $value, string $tag, $attr, array $m ) {
		if ( 'et_pb_section' !== $tag ) {
			return $value;
		}

		$attrs  = $m[3];
		$action = 'et_builder_set_style';
		$filter = [ $this, 'set_style' ];
		$active = has_filter( $action, $filter );

		if ( ! empty( $this->_atf_sections[ $attrs ] ) ) {
			$this->_atf_sections[ $attrs ]--;

			if ( ! $active ) {
				add_filter( $action, [ $this, 'set_style' ], 10 );
			}
		}

		return $value;
	}

	/**
	 * Remove `set_style` filter after rendering an ATF section based on shortcode.
	 *
	 * @since ??
	 *
	 * @param string $output Shortcode output.
	 * @param string $tag    Shortcode name.
	 *
	 * @return string
	 */
	public function check_section_end( string $output, string $tag ) {
		static $section = 0;

		if ( 'et_pb_section' !== $tag ) {
			return $output;
		}

		$action = 'et_builder_set_style';
		$filter = [ $this, 'set_style' ];

		if ( has_filter( $action, $filter ) ) {
			remove_filter( $action, $filter, 10 );
		}

		return $output;
	}

	/**
	 * Parse Content into shortcodes.
	 *
	 * @since ??
	 *
	 * @param string $content TB/Post Content.
	 *
	 * @return array|boolean
	 */
	public static function parse_shortcode( string $content ) {
		static $regex;

		if ( false === strpos( $content, '[' ) ) {
			return false;
		}

		if ( empty( $regex ) ) {
			$regex = '/' . get_shortcode_regex() . '/';

			// Add missing child shortcodes (because dynamically added).
			$existing   = 'et_pb_pricing_tables';
			$shortcodes = [
				$existing,
				'et_pb_pricing_item',
			];

			$regex = str_replace( $existing, join( '|', $shortcodes ), $regex );
		}

		preg_match_all( $regex, $content, $matches, PREG_SET_ORDER );

		return $matches;
	}

	/**
	 * Estimates height to split Content in ATF/BTF based on shortcode.
	 *
	 * @since ??
	 *
	 * @param string $content TB/Post Content.
	 *
	 * @return array List of ATF shortcodes.
	 */
	public function extract( string $content ): array {
		// Create root object when needed.
		if ( empty( $this->_root ) ) {
			$this->_root = (object) [
				'tag'    => 'root',
				'height' => 0,
			];
		}

		if ( $this->_root->height >= $this->_above_the_fold_height ) {
			// Do nothing when root already exists and its height >= treshold.
			return [];
		}

		$shortcodes = self::parse_shortcode( $content );

		if ( ! is_array( $shortcodes ) ) {
			return [];
		}

		// Measure whether current module is above the fold or not.
		// Above the fold status measurement has to be done on render data hook because it happens from top to bottom.
		// This ensure the above the fold status has been updated when child module of the section gets the above
		// the fold status inside of it.
		self::$_above_the_fold = true;

		$shortcodes  = array_reverse( $shortcodes );
		$root        = $this->_root;
		$root->count = count( $shortcodes );
		$stack       = [ $root ];
		$parent      = end( $stack );
		$tags        = [];
		$atf_content = '';
		$btf_content = '';

		$structure_slugs = [
			'et_pb_section',
			'et_pb_row',
			'et_pb_row_inner',
			'et_pb_column',
			'et_pb_column_inner',
		];

		$section           = '';
		$section_shortcode = '';

		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- Intentionally done.
		while ( self::$_above_the_fold && $shortcode = array_pop( $shortcodes ) ) {
			list( $raw,, $tag, $attrs,, $content ) = $shortcode;

			$tags[]   = $tag;
			$children = self::parse_shortcode( $content );
			$element  = (object) [
				'tag'      => $tag,
				'children' => [],
				'height'   => 0,
				'margin'   => 0,
				'padding'  => 0,
				'attrs'    => [],
			];

			switch ( $tag ) {
				case 'et_pb_pricing_table':
					$lines   = array_filter( explode( "\n", str_replace( array( '<p>', '</p>', '<br />' ), "\n", $content ) ) );
					$content = '';

					foreach ( $lines as $line ) {
						$content .= sprintf( '[et_pb_pricing_item]%s[/et_pb_pricing_item]', trim( $line ) );
					}

					$children = self::parse_shortcode( $content );
					break;
				case 'et_pb_section':
					$section           = $attrs;
					$section_shortcode = $raw;
					break;
			}

			$props = shortcode_parse_atts( $attrs );

			if ( isset( $props['custom_margin'] ) ) {
				$margin          = self::get_margin_padding_height( $props['custom_margin'] );
				$element->margin = $margin;
				if ( $margin > 0 ) {
					$element->height += $margin;
					$element->attrs[] = 'margin:' . $props['custom_margin'] . "-> $margin";
				}
			}

			if ( isset( $props['custom_padding'] ) ) {
				$padding          = self::get_margin_padding_height( $props['custom_padding'] );
				$element->padding = $padding;
				if ( $padding > 0 ) {
					$element->height += $padding;
					$element->attrs[] = 'padding:' . $props['custom_padding'] . "-> $padding";
				}
			}

			if ( false !== $children ) {
				// Non empty structure element.
				$element->count = count( $children );
				$stack[]        = $element;
				$shortcodes     = array_merge( $shortcodes, array_reverse( $children ) );
			} else {
				// Only add default content height for modules, not empty structure.
				if ( ! in_array( $tag, $structure_slugs, true ) ) {
					$element->height += 100;
				}
				do {
					$parent = end( $stack );

					switch ( $element->tag ) {
						case 'et_pb_column':
						case 'et_pb_column_inner':
							// Do nothing.
							break;
						case 'et_pb_row':
						case 'et_pb_row_inner':
							// Row height is determined by its tallest column.
							$max = 0;

							foreach ( $element->children as $column ) {
								$max = max( $max, $column->height );
							}

							$element->height += $max;
							$parent->height  += $element->height;
							break;
						case 'et_pb_section':
							// Update Above The Fold Sections.
							if ( isset( $this->_atf_sections[ $section ] ) ) {
								$this->_atf_sections[ $section ]++;
							} else {
								$this->_atf_sections[ $section ] = 1;
							}

							$atf_content  .= $section_shortcode;
							$root->height += $element->height;

							if ( $root->height >= $this->_above_the_fold_height ) {
								self::$_above_the_fold = false;
							}
							break;
						default:
							$parent->height += $element->height;
					}

					$parent->children[] = $element;

					if ( 0 !== --$parent->count ) {
						break;
					}

					$element = $parent;
					array_pop( $stack );
					if ( empty( $stack ) ) {
						break;
					}
				} while ( self::$_above_the_fold && 0 !== --$parent->count );
			}
		}

		foreach ( $shortcodes as $shortcode ) {
			$btf_content .= $shortcode[0];
		}

		$tags           = array_unique( $tags );
		$this->_modules = array_unique( array_merge( $this->_modules, $tags ) );
		$this->_content = (object) [
			'atf' => $atf_content,
			'btf' => $btf_content,
		];

		return array_map( [ DynamicAssetsUtils::class, 'get_block_name_from_shortcode' ], $tags );
	}

	/**
	 * Calculate margin and padding based on shortcode.
	 *
	 * @since ??
	 *
	 * @param string $value Margin and padding values.
	 *
	 * @return int margin/padding height value.
	 */
	public static function get_margin_padding_height( string $value ) {
		$values = explode( '|', $value );

		if ( empty( $values ) ) {
			return 0;
		}

		// Only top/bottom values are needed.
		$values = array_map( 'trim', [ $values[0], $values[2] ] );
		$total  = 0;

		foreach ( $values as $value ) {
			if ( '' === $value ) {
				continue;
			}

			$unit = et_pb_get_value_unit( $value );

			// Remove the unit, if present.
			if ( false !== strpos( $value, $unit ) ) {
				$value = substr( $value, 0, -strlen( $unit ) );
			}

			$value = (int) $value;

			switch ( $unit ) {
				case 'rem':
				case 'em':
					$value *= self::FONT_HEIGHT;
					break;
				case 'vh':
					$value = ( $value * self::VIEWPORT_HEIGHT ) / 100;
					break;
				case 'px':
					break;
				default:
					$value = 0;
			}

			$total += $value;
		}

		return $total;
	}

}
