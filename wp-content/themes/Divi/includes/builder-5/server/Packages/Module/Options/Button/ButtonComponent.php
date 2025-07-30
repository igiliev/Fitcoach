<?php
/**
 * Module: ButtonComponent class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\Button;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;

/**
 * ButtonComponent class
 *
 * @since ??
 */
class ButtonComponent {

	/**
	 * Determines whether to render a button component based on the provided arguments.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for rendering the component.
	 *
	 *     @type string $type          Optional. The type of the button. Can be 'button' or 'link'. Default `link`.
	 *     @type bool   $allowEmptyUrl Optional. Whether to allow the component to render even if the URL is empty. Default `false`.
	 *     @type string $text          Optional. The text of the button.
	 *     @type string $linkUrl       Optional. The URL of the button. Only required if the type is 'link'.
	 * }
	 *
	 * @return bool Returns true if the button component should be rendered, false otherwise.
	 */
	public static function is_render( array $args ): bool {
		$type            = $args['type'] ?? 'link';
		$allow_empty_url = $args['allowEmptyUrl'] ?? false;
		$text            = $args['text'] ?? '';
		$url             = $args['linkUrl'] ?? '';

		// If the type is button or empty URL is allowed, we should render when there is text.
		// If the type is link, we should render when there is text and url.
		$is_render = ( $allow_empty_url || 'button' === $type ) ? '' !== $text : ( '' !== $text && '' !== $url );

		return $is_render;
	}

	/**
	 * Component function for rendering buttons or links.
	 *
	 * This function takes an array of arguments and returns a button or a link based on the `$type` argument.
	 * The function supports both button and link types, with options to customize the appearance and behavior of the component.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js-beta/divi-module/functions/ButtonComponent/ ButtonComponent} in
	 * `@divi/module` package.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments for rendering the component.
	 *
	 *     @type bool   $allowEmptyUrl             Optional. Whether to allow the component to render even if the URL is empty. Default `false`.
	 *     @type array  $buttonAttr                Optional. Additional attributes for the button element. Default `[]`.
	 *     @type string $className                 Optional. Additional classes for the component. Default `null`.
	 *     @type bool   $hasWrapper                Optional. Whether to wrap the button or link with a wrapper div. Default `true`.
	 *     @type string $id                        Optional. ID attribute for the component element. Default `null`.
	 *     @type array  $innerContent              Optional. Inner content of the component, expected to have a 'desktop' key
	 *                                             with a 'value' array. Default `[]`.
	 *     @type string $name                      Optional. Name attribute for the button element. Default is an empty string.
	 *     @type bool   $renderIconAsDataAttribute Optional. Whether to render the button icon as a data attribute. Default `true`.
	 *     @type string $type                      Optional. Type of the component. Default is `'link'`.
	 *     @type boolean $hasPreloader             Optional. Whether the button has preloader or not. Defaults to `false`.
	 *     @type boolean $hasTextWrapper           Optional. Whether the button has text wrapper or not. Defaults to `false`.
	 *     @type boolean $forceRender              Optional. Whether to force render the component. Defaults to `false`.
	 * }
	 *
	 * @return string The rendered button or link component.
	 *                Returns an empty string if there is no text or URL provided and the empty URL is not allowed.
	 *
	 * @example:
	 * ```php
	 * use MyApp\Components\Button;
	 *
	 * // Render a link button with custom attributes
	 * $args = [
	 *     'allowEmptyUrl' => false,
	 *     'buttonAttr' => [
	 *         'desktop' => [
	 *             'value' => [
	 *                 'enable' => 'on',
	 *                 'icon' => [
	 *                     'settings' => [
	 *                         'name' => 'fa fa-globe',
	 *                         'style' => 'solid',
	 *                         'size' => 'md',
	 *                     ],
	 *                 ],
	 *             ],
	 *         ],
	 *     ],
	 *     'className' => 'my-custom-class',
	 *     'hasWrapper' => true,
	 *     'id' => 'my-button',
	 *     'innerContent' => [
	 *         'desktop' => [
	 *             'value' => [
	 *                 'text' => 'Click me',
	 *                 'linkUrl' => 'https://example.com',
	 *                 'rel' => ['noopener'],
	 *             ],
	 *         ],
	 *     ],
	 *     'name' => 'my-button-name',
	 *     'renderIconAsDataAttribute' => true,
	 *     'type' => 'link',
	 * ];
	 *
	 * $button = Button::component($args);
	 * echo $button;
	 * ```
	 */
	public static function component( array $args ): string {
		$allow_empty_url               = $args['allowEmptyUrl'] ?? false;
		$button_attr                   = $args['buttonAttr'] ?? [];
		$class_name                    = $args['className'] ?? null;
		$has_wrapper                   = $args['hasWrapper'] ?? true;
		$id                            = $args['id'] ?? null;
		$inner_content                 = $args['innerContent'] ?? [];
		$name                          = $args['name'] ?? '';
		$render_icon_as_data_attribute = $args['renderIconAsDataAttribute'] ?? true;
		$type                          = $args['type'] ?? 'link';
		$has_preloader                 = $args['hasPreloader'] ?? false;
		$has_text_wrapper              = $args['hasTextWrapper'] ?? false;
		$force_render                  = $args['forceRender'] ?? false;

		// TODO: feat(D5, Responsive Content) implement responsive content implementation for multiViewData.
		$text = $inner_content['desktop']['value']['text'] ?? '';
		$url  = $inner_content['desktop']['value']['linkUrl'] ?? '';

		// Check if the button link text is plain text or wrapped in a HTML tag.
		// If the text is wrapped in a HTML tag, extract the text title from the tag.
		// Test regex: https://regex101.com/r/E5rBze/3.
		if ( ( preg_match( '/<[^<]+?>/', $text ) ) ) {
			// Extract the title text from the link.
			$text = ModuleUtils::extract_link_title( $text );
		};

		$is_render = self::is_render(
			[
				'type'          => $type,
				'allowEmptyUrl' => $allow_empty_url,
				'text'          => $text,
				'linkUrl'       => $url,
			]
		);

		// Hide button if there is no text and url and NOT force render.
		if ( ! $is_render && ! $force_render ) {
			return '';
		}

		$rel              = $inner_content['desktop']['value']['rel'] ?? [];
		$target_new_tab   = $inner_content['desktop']['value']['linkTarget'] ?? '';
		$use_custom_style = $button_attr['desktop']['value']['enable'] ?? 'off';
		$is_custom_style  = 'on' === $use_custom_style;

		// Classname.
		$button_class_names = [ 'et_pb_button' ];

		if ( $class_name ) {
			$button_class_names[] = $class_name;
		}

		// Data icon.
		$icon_desktop = $render_icon_as_data_attribute && $is_custom_style && isset( $button_attr['desktop']['value']['icon']['settings'] )
			? Utils::escape_font_icon( Utils::process_font_icon( $button_attr['desktop']['value']['icon']['settings'] ) )
			: null;
		$icon_tablet  = $render_icon_as_data_attribute && $is_custom_style && isset( $button_attr['tablet']['value']['icon']['settings'] )
			? Utils::escape_font_icon( Utils::process_font_icon( $button_attr['tablet']['value']['icon']['settings'] ) )
			: null;
		$icon_phone   = $render_icon_as_data_attribute && $is_custom_style && isset( $button_attr['phone']['value']['icon']['settings'] )
			? Utils::escape_font_icon( Utils::process_font_icon( $button_attr['phone']['value']['icon']['settings'] ) )
			: null;

		$empty_rel = empty( $rel ) && 'on' === $target_new_tab ? 'noreferrer' : null;

		// TODO: feat(D5, Improvement) Make `et_subscribe_loader` class name to be more generic and configurable.
		$preloader_class_name = 'et_subscribe_loader';

		// TODO: feat(D5, Improvement) Make `et_pb_newsletter_button_text` class name to be more generic and configurable.
		$text_wrapper_class_name = 'et_pb_newsletter_button_text';

		// TODO: feat(D5, Frontend Rendering) implement responsive content implementation for multiViewData.
		$button = 'button' === $type
		? HTMLUtility::render(
			[
				'tag'               => 'button',
				'attributes'        => [
					'type'             => 'submit',
					'name'             => $name,
					'id'               => $id,
					'class'            => implode( ' ', $button_class_names ),
					'data-icon'        => $icon_desktop,
					'data-icon-tablet' => $icon_tablet,
					'data-icon-phone'  => $icon_phone,
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					$has_preloader ? HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class' => $preloader_class_name,
							],
						]
					) : '',
					$has_text_wrapper ? HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class' => $text_wrapper_class_name,
							],
							'children'   => $text,
						]
					) : esc_html( $text ),
				],
			]
		)
		: HTMLUtility::render(
			[
				'tag'               => 'a',
				'attributes'        => [
					'id'               => $id,
					'class'            => implode( ' ', $button_class_names ),
					'target'           => 'on' === $target_new_tab ? '_blank' : null,
					'href'             => esc_url( $url ),
					'data-icon'        => $icon_desktop,
					'data-icon-tablet' => $icon_tablet,
					'data-icon-phone'  => $icon_phone,
					'rel'              => empty( $rel ) ? $empty_rel : implode( ' ', $rel ),
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => [
					$has_preloader ? HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class' => $preloader_class_name,
							],
						]
					) : '',
					$has_text_wrapper ? HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class' => $text_wrapper_class_name,
							],
							'children'   => $text,
						]
					) : esc_html( $text ),
				],
			]
		);

		return $has_wrapper
			? HTMLUtility::render(
				[
					'tag'               => 'div',
					'attributes'        => [
						'class' => 'et_pb_button_wrapper',
					],
					'childrenSanitizer' => 'et_core_esc_previously',
					'children'          => $button,
				]
			) : $button;
	}

}
