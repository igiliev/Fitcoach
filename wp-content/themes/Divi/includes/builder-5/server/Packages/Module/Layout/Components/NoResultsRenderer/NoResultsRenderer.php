<?php
/**
 * Module: NoResultsRenderer class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\NoResultsRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\Loop\LoopUtils;
use ET\Builder\Framework\Utility\HTMLUtility;

/**
 * NoResultsRenderer class
 *
 * Handles rendering of "No Results Found" messages for loop-enabled modules
 * when no query results are available.
 *
 * @since ??
 */
class NoResultsRenderer {

	/**
	 * Renders the "No Results Found" message with module wrapper.
	 *
	 * This function creates a standardized no-results display that maintains
	 * the module's class structure and styling while showing the error message.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for rendering the no-results message.
	 *
	 *     @type string $moduleClassName     The CSS class name for the module (e.g., 'et_pb_blurb').
	 *     @type string $moduleOrderClass    The order-based CSS class for the module.
	 *     @type string $additionalClasses   Additional CSS classes to apply.
	 *     @type array  $htmlAttrs          Custom HTML attributes for the wrapper.
	 *     @type string $tag                HTML tag for the wrapper. Default 'div'.
	 *     @type string $moduleStyles       Any module-specific style components.
	 * }
	 *
	 * @return string The rendered HTML for the no-results message.
	 */
	public static function render( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'moduleClassName'   => '',
				'moduleOrderClass'  => '',
				'additionalClasses' => '',
				'htmlAttrs'         => [],
				'tag'               => 'div',
				'moduleStyles'      => '',
			]
		);

		$module_class_name  = $args['moduleClassName'];
		$module_order_class = $args['moduleOrderClass'];
		$additional_classes = $args['additionalClasses'];
		$html_attrs         = $args['htmlAttrs'];
		$tag                = $args['tag'];
		$module_styles      = $args['moduleStyles'];

		// Build the CSS classes for the wrapper.
		$wrapper_classes = HTMLUtility::classnames(
			[
				$module_class_name  => ! empty( $module_class_name ),
				$module_order_class => ! empty( $module_order_class ),
				$additional_classes => ! empty( $additional_classes ),
			]
		);

		// Get the standardized no-results message content.
		$no_results_content = LoopUtils::render_no_results_found_message();

		// Prepare HTML attributes.
		$html_attrs_all = array_merge(
			[
				'class' => $wrapper_classes,
			],
			$html_attrs
		);

		// Combine module styles with the no-results content.
		$children = $module_styles . $no_results_content;

		return HTMLUtility::render(
			[
				'tag'               => $tag,
				'attributes'        => $html_attrs_all,
				'children'          => $children,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);
	}
}
