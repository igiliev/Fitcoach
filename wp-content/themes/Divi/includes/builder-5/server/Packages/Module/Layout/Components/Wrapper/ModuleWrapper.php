<?php
/**
 * Module: ModuleWrapper class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;

/**
 * ModuleWrapper class
 *
 * @since ??
 */
class ModuleWrapper {

	/**
	 * Module wrapper renderer.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ModuleWrapper ModuleWrapper}
	 * in `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string  $children             Optional. The children element. Default empty string.
	 *     @type string  $classname            Optional. Custom CSS class attribute. Default empty string.
	 *     @type array   $styles               Optional. Custom inline style attribute. Default `[]`.
	 *     @type string  $dataColumnStructure  Optional. Data column structure attribute. Default `null`.
	 *     @type string  $dataColumnType       Optional. Data column type attribute. Default `null`.
	 *     @type array   $htmlAttrs            Optional. Custom HTML attributes. Default `[]`.
	 *     @type string  $tag                  Optional. HTML tag.  Default `div`.
	 *     @type bool    $hasModuleWrapper     Optional. Has module wrapper.  Default `false`.
	 *     @type string  $wrapperTag           Optional. Wrapper HTML tag. Default `div`.
	 *     @type array   $wrapperHtmlAttrs     Optional. Wrapper custom html attributes. Default `[]`.
	 *     @type string  $wrapperClassname     Optional. Wrapper custom CSS class. Default `null`.
	 * }
	 *
	 * @return string
	 */
	public static function render( array $args ): string {
		$args = wp_parse_args(
			$args,
			[
				'children'            => '',
				'classname'           => '',
				'styles'              => [],
				'dataColumnStructure' => null,
				'dataColumnType'      => null,
				'htmlAttrs'           => [],
				'tag'                 => 'div',
				'hasModuleWrapper'    => false,
				'wrapperTag'          => 'div',
				'wrapperHtmlAttrs'    => [],
				'wrapperClassname'    => null,
			]
		);

		$children              = $args['children'];
		$classname             = $args['classname'];
		$styles                = $args['styles'];
		$data_column_structure = $args['dataColumnStructure'];
		$data_column_type      = $args['dataColumnType'];
		$tag                   = $args['tag'];
		$html_attrs            = $args['htmlAttrs'];
		$has_module_wrapper    = $args['hasModuleWrapper'];
		$wrapper_tag           = $args['wrapperTag'];
		$wrapper_html_attrs    = $args['wrapperHtmlAttrs'];
		$wrapper_classname     = $args['wrapperClassname'];

		$html_attrs_all = [
			'class' => $classname,
		];

		if ( $styles ) {
			$html_attrs_all['style'] = $styles;
		}

		if ( $data_column_type ) {
			$html_attrs_all['data-column-type'] = $data_column_type;
		}

		if ( $data_column_structure ) {
			$html_attrs_all['data-column-structure'] = $data_column_structure;
		}

		if ( $html_attrs ) {
			$html_attrs_excludes = array_keys( $html_attrs_all );

			foreach ( $html_attrs_excludes as $html_attrs_exclude ) {
				unset( $html_attrs[ $html_attrs_exclude ] );
			}

			$html_attrs_all = array_merge(
				$html_attrs_all,
				$html_attrs
			);
		}

		$module_container = HTMLUtility::render(
			[
				'tag'               => $tag,
				'attributes'        => $html_attrs_all,
				'children'          => $children,
				'childrenSanitizer' => 'et_core_esc_previously',
			]
		);

		if ( $has_module_wrapper ) {
			$wrapper_html_attrs_all = [];

			if ( $wrapper_classname ) {
				$wrapper_html_attrs_all['class'] = $wrapper_classname;
			}

			if ( $wrapper_html_attrs ) {
				$wrapper_html_attrs_excludes = array_keys( $wrapper_html_attrs_all );

				foreach ( $wrapper_html_attrs_excludes as $wrapper_html_attrs_exclude ) {
					unset( $wrapper_html_attrs[ $wrapper_html_attrs_exclude ] );
				}

				$wrapper_html_attrs_all = array_merge(
					$wrapper_html_attrs_all,
					$wrapper_html_attrs
				);
			}

			return HTMLUtility::render(
				[
					'tag'               => $wrapper_tag,
					'attributes'        => $wrapper_html_attrs_all,
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => $module_container,
				]
			);
		}

		return $module_container;
	}
}
