<?php
/**
 * Module: DynamicContentOptionPostLink class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;

/**
 * Module: DynamicContentOptionPostLink class.
 *
 * @since ??
 */
class DynamicContentOptionPostLink extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post link option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post link option.
	 */
	public function get_name(): string {
		return 'post_link';
	}

	/**
	 * Get the label for the post link option.
	 *
	 * This function retrieves the localized label for the post link option,
	 * which is used to describe the post link in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post link option.
	 */
	public function get_label(): string {
		// Translators: %1$s: Post type name.
		return __( '%1$s Link', 'et_builder' );
	}

	/**
	 * Callback for registering post link option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post link by adding them to the options array passed to the function .
	 * It checks if the current module's name exists as a key in the options array.
	 * If not, it adds the module's name as a key and the specific options for that module as the value.
	 *
	 * @since ??
	 *
	 * @param array  $options The options array to be registered.
	 * @param int    $post_id The post ID.
	 * @param string $context The context in which the options are retrieved e.g `edit`, `display`.
	 *
	 * @return array The options array.
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array {
		if ( ! isset( $options[ $this->get_name() ] ) ) {
			$fields = [
				'before'      => [
					'label'   => esc_html__( 'Before', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'after'       => [
					'label'   => esc_html__( 'After', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'text'        => [
					'label'   => esc_html__( 'Link Text', 'et_builder' ),
					'type'    => 'select',
					'options' => [
						// Translators: %1$s: Post type name.
						'post_title' => esc_html( sprintf( __( '%1$s Title', 'et_builder' ), DynamicContentUtils::get_post_type_label( $post_id ) ) ),
						'custom'     => esc_html__( 'Custom', 'et_builder' ),
					],
					'default' => 'post_title',
				],
				'custom_text' => [
					'label'   => esc_html__( 'Custom Link Text', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
					'show_if' => [
						'text' => 'custom',
					],
				],
			];

			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => esc_html( sprintf( $this->get_label(), DynamicContentUtils::get_post_type_label( $post_id ) ) ),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Default',
				'fields' => $fields,
			];

			$options[ 'loop_' . $this->get_name() ] = [
				'id'     => 'loop_' . $this->get_name(),
				'label'  => esc_html( sprintf( $this->get_label(), 'Loop' ) ),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Loop',
				'fields' => array_merge(
					[
						'loop_position' => [
							'label'       => esc_html__( 'Loop Position', 'et_builder' ),
							'type'        => 'text',
							'default'     => '',
							'renderAfter' => 'n',
						],
					],
					$fields
				),
			];
		}

		return $options;
	}

	/**
	 * Render callback for post link option.
	 *
	 * Retrieves the value of post link option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post link option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post link.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the post link option.
	 *
	 * @example:
	 * ```php
	 *  $element = new MyDynamicContentElement();
	 *
	 *  // Render the element with a specific value and data arguments.
	 *  $html = $element->render_callback( $value, [
	 *      'name'     => 'my_element',
	 *      'settings' => [
	 *          'post_id' => 123,
	 *          'foo'     => 'bar',
	 *      ],
	 *      'post_id'  => 456,
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name     = $data_args['name'] ?? '';
		$settings = $data_args['settings'] ?? [];
		$post_id  = $data_args['post_id'] ?? null;

		if ( $name !== $this->get_name() ) {
			return $value;
		}

		$post = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;

		if ( $post ) {
			$text        = $settings['text'] ?? 'post_title';
			$custom_text = $settings['custom_text'] ?? '';
			$label       = 'custom' === $text ? $custom_text : get_the_title( $post_id );
			$value       = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( get_permalink( $post_id ) ),
				esc_html( $label )
			);
		}

		return DynamicContentElements::get_wrapper_element(
			[
				'post_id'  => $post_id,
				'name'     => $name,
				'value'    => $value,
				'settings' => $settings,
			]
		);
	}
}
