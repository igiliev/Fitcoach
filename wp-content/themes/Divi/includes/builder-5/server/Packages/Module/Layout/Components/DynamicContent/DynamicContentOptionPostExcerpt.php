<?php
/**
 * Module: DynamicContentOptionPostExcerpt class.
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
 * Module: DynamicContentOptionPostExcerpt class.
 *
 * @since ??
 */
class DynamicContentOptionPostExcerpt extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post excerpt option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post excerpt option.
	 */
	public function get_name(): string {
		return 'post_excerpt';
	}

	/**
	 * Get the label for the post excerpt option.
	 *
	 * This function retrieves the localized label for the post excerpt option,
	 * which is used to describe the post excerpt in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post excerpt option.
	 */
	public function get_label(): string {
		// Translators: %1$s: Post type name.
		return __( '%1$s Excerpt', 'et_builder' );
	}

	/**
	 * Callback for registering post excerpt option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post excerpt by adding them to the options array passed to the function .
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
				'before'          => [
					'label'   => esc_html__( 'Before', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'after'           => [
					'label'   => esc_html__( 'After', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'words'           => [
					'label'   => esc_html__( 'Number of Words', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'read_more_label' => [
					'label'   => esc_html__( 'Read More Text', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
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
	 * Render callback for post excerpt option.
	 *
	 * Retrieves the value of post excerpt option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post excerpt option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post excerpt.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 *     @type array   $overrides  Optional. An associative array of `option_name => value` to override option value.
	 *                               Default `[]`.
	 * }
	 *
	 * @return string The formatted value of the post excerpt option.
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
	 *      'overrides' => [
	 *        'my_element' => 'My Element',
	 *        'product_excerpt' => 'post excerpt',
	 *      ],
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name      = $data_args['name'] ?? '';
		$settings  = $data_args['settings'] ?? [];
		$post_id   = $data_args['post_id'] ?? null;
		$overrides = $data_args['overrides'] ?? [];

		if ( $name !== $this->get_name() ) {
			return $value;
		}

		/**
		 * Since passing `null`, `false`, `0` and other PHP falsey values to `get_post()` return the current global post inside the loop
		 * we have to make sure the post_id is a valid number and not 0.
		 */
		$is_post_id_number_valid = is_int( $post_id ) && 0 !== $post_id;

		$post = $is_post_id_number_valid ? get_post( $post_id ) : null;

		if ( ! $post ) {
			$value = '';
		} else {
			$value     = $overrides[ $name ] ?? $post->post_excerpt;
			$read_more = $settings['read_more_label'] ?? '';
			$words     = (int) ( $settings['words'] ?? '' );

			if ( $words > 0 ) {
				$value = wp_trim_words( $value, $words );
			}

			if ( ! empty( $read_more ) ) {
				$value .= sprintf(
					' <a href="%1$s">%2$s</a>',
					esc_url( get_permalink( $post_id ) ),
					esc_html( $read_more )
				);
			}
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
