<?php
/**
 * TeamMemberModule::module_styles().
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\TeamMember\TeamMemberModuleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;

trait ModuleStylesTrait {

	use CustomCssTrait;
	use StyleDeclarationTrait;

	/**
	 * Load TeamMember module style components.
	 *
	 * This function is responsible for loading styles for the module. It takes an array of arguments
	 * which includes the module ID, name, attributes, settings, and other details. The function then
	 * uses these arguments to dynamically generate and add the required styles.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/team-member-module-styles ModuleStyles}
	 * located in `@divi/module-library` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id               The module ID. In Visual Builder (VB), the ID of the module is a UUIDV4 string.
	 *                                    In FrontEnd (FE), the ID is the order index.
	 *     @type string $name             The module name.
	 *     @type array  $attrs            Optional. The module attributes. Default `[]`.
	 *     @type array  $settings         Optional. The module settings. Default `[]`.
	 *     @type string $orderClass       The selector class name.
	 *     @type int    $orderIndex       The order index of the module.
	 *     @type int    $storeInstance    The ID of instance where this block stored in BlockParserStore.
	 *     @type ModuleElements $elements ModuleElements instance.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 *     TeamMemberModule::module_styles([
	 *         'id'        => 'module-1',
	 *         'name'      => 'Accordion Module',
	 *         'attrs'     => [],
	 *         'elements'  => $elementsInstance,
	 *         'settings'  => $moduleSettings,
	 *         'orderClass'=> '.accordion-module'
	 *     ]);
	 * ```
	 */
	public static function module_styles( array $args ): void {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					// Module.
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn'     => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'advancedStyles' => [
									[
										'componentName' => 'divi/text',
										'props'         => [
											'selector' => implode(
												',',
												[
													"{$args['orderClass']} .et_pb_module_header",
													"{$args['orderClass']} .et_pb_member_position",
													"{$args['orderClass']} .et_pb_team_member_description p",
												]
											),
											'attr'     => $attrs['module']['advanced']['text'] ?? [],
										],
									],
									[
										'componentName' => 'divi/css',
										'props'         => [
											'attr'      => $attrs['css'] ?? [],
											'cssFields' => self::custom_css(),
										],
									],
								],
							],
						]
					),

					// Image.
					$elements->style(
						[
							'attrName'   => 'image',
							'styleProps' => [
								'attrsFilter'    => function( $decoration_attrs ) {
									$has_hover_value            = ! empty( $decoration_attrs['border']['desktop']['hover']['styles'] ?? [] );
									$has_sticky_value           = ! empty( $decoration_attrs['border']['desktop']['sticky']['styles'] ?? [] );
									$is_empty_border_in_desktop = empty( $decoration_attrs['border']['desktop']['value']['styles'] ?? [] ) ||
									empty( $decoration_attrs['border']['desktop']['value']['styles']['all']['width'] ?? '' );

									if ( ( $has_hover_value || $has_sticky_value ) && $is_empty_border_in_desktop ) {
										// Add border width to `0px` if hover and/or sticky has value. But desktop don't have value.
										// We need it to resolve the issue for transition. In D4, there was a `et_pb_with_border` class that
										// is adding a static CSS `border: 0 solid #333;`. But in D5, we don't have that class anymore.
										return array_replace_recursive(
											$decoration_attrs,
											[
												'border' => [
													'desktop' => [
														'value' => [
															'styles' => [
																'all' => [
																	'width' => '0px',
																],
															],
														],
													],
												],
											]
										);
									}
									return $decoration_attrs;
								},
								'advancedStyles' => [
									[
										'componentName' => 'divi/common',
										'props'         => [
											'attr' => $attrs['image']['decoration']['border'] ?? [],
											'declarationFunction' => [ self::class, 'image_overflow_style_declaration' ],
										],
									],
								],
							],
						]
					),
					// Social Icon.
					$elements->style(
						[
							'attrName' => 'social',
						]
					),

					// Title.
					$elements->style(
						[
							'attrName' => 'name',
						]
					),

					// Position Text.
					$elements->style(
						[
							'attrName' => 'position',
						]
					),

					// Content.
					$elements->style(
						[
							'attrName' => 'content',
						]
					),
				],
			]
		);
	}

}
