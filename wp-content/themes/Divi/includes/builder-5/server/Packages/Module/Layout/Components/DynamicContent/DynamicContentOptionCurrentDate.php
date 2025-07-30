<?php
/**
 * Module: DynamicContentOptionCurrentDate class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptionCurrentDate class.
 *
 * @since ??
 */
class DynamicContentOptionCurrentDate extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Retrieves the name of the current date option.
	 *
	 * This function returns the name of the current date option as a string.
	 *
	 * @since ??
	 *
	 * @return string The name of the current date option.
	 */
	public function get_name(): string {
		return 'current_date';
	}

	/**
	 * Get the label of the current date option.
	 *
	 * Retrieves the localized label of the option.
	 * The label is used to describe the option in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label of the option.
	 *
	 * @example:
	 * ```php
	 *     $example = new DynamicContentOptionBase();
	 *     echo $example->get_label();
	 * ```
	 *
	 * @output:
	 * ```php
	 *  Current Date
	 * ```
	 */
	public function get_label(): string {
		return esc_html__( 'Current Date', 'et_builder' );
	}

	/**
	 * Register option callback.
	 *
	 * This is a callback for `divi_module_dynamic_content_options` filter.
	 * This function add the current date option to the options array if it doesn't exist.
	 *
	 * @since ??
	 *
	 * @param array  $options  The options array.
	 * @param int    $post_id  The post ID.
	 * @param string $context  The context e.g `edit`, `display`.
	 *
	 * @return array The options array.
	 *
	 * @example:
	 * ```php
	 *  $options = ['option1' => 'value1', 'option2' => 'value2'];
	 *  $post_id = 123;
	 *  $context = 'display';
	 *  $registered_options = register_option_callback($options, $post_id, $context);
	 * ```
	 *
	 * @output:
	 * ```php
	 *  [
	 *    'option1' => 'value1',
	 *    'option2' => 'value2',
	 *    'current_date' => [
	 *    'id' => 'current_date',
	 *    'label' => 'Current Date',
	 *    'type' => 'text',
	 *    'custom' => false,
	 *    'group' => 'Default',
	 *    'fields' => [
	 *        'before' => [
	 *            'label' => 'Before',
	 *            'type' => 'text',
	 *            'default' => '',
	 *        ],
	 *        'after' => [
	 *            'label' => 'After',
	 *            'type' => 'text',
	 *            'default' => '',
	 *        ],
	 *        'date_format' => [
	 *            'label' => 'Date Format',
	 *            'type' => 'select',
	 *            'options' => [
	 *                'default' => 'Default',
	 *                'M j, Y' => 'Aug 6, 1999 (M j, Y)',
	 *                'F d, Y' => 'August 06, 1999 (F d, Y)',
	 *                'm/d/Y' => '08/06/1999 (m/d/Y)',
	 *                'm.d.Y' => '08.06.1999 (m.d.Y)',
	 *                'j M, Y' => '6 Aug, 1999 (j M, Y)',
	 *                'l, M d' => 'Tuesday, Aug 06 (l, M d)',
	 *                'custom' => 'Custom',
	 *            ],
	 *            'default' => 'default',
	 *        ],
	 *        'custom_date_format' => [
	 *            'label' => 'Custom Date Format',
	 *            'type' => 'text',
	 *            'default' => '',
	 *            'show_if' => [
	 *                'date_format' => 'custom',
	 *            ],
	 *        ],
	 *    ],
	 * ],
	 * ]
	 * ```
	 */
	public function register_option_callback( array $options, int $post_id, string $context ): array {
		if ( ! isset( $options[ $this->get_name() ] ) ) {
			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => $this->get_label(),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Default',
				'fields' => [
					'before'             => [
						'label'   => esc_html__( 'Before', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'after'              => [
						'label'   => esc_html__( 'After', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'date_format'        => [
						'label'   => esc_html__( 'Date Format', 'et_builder' ),
						'type'    => 'select',
						'options' => [
							'default' => et_builder_i18n( 'Default' ),
							'M j, Y'  => esc_html__( 'Aug 6, 1999 (M j, Y)', 'et_builder' ),
							'F d, Y'  => esc_html__( 'August 06, 1999 (F d, Y)', 'et_builder' ),
							'm/d/Y'   => esc_html__( '08/06/1999 (m/d/Y)', 'et_builder' ),
							'm.d.Y'   => esc_html__( '08.06.1999 (m.d.Y)', 'et_builder' ),
							'j M, Y'  => esc_html__( '6 Aug, 1999 (j M, Y)', 'et_builder' ),
							'l, M d'  => esc_html__( 'Tuesday, Aug 06 (l, M d)', 'et_builder' ),
							'custom'  => esc_html__( 'Custom', 'et_builder' ),
						],
						'default' => 'default',
					],
					'custom_date_format' => [
						'label'   => esc_html__( 'Custom Date Format', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
						'show_if' => [
							'date_format' => 'custom',
						],
					],
				],
			];
		}

		return $options;
	}

	/**
	 * Render callback for a dynamic content element.
	 *
	 * Retrieves the value of a dynamic content element based on the provided arguments and settings,
	 * formats the value as a date using the specified format (default or custom), and returns the formatted value.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value      The current value of the dynamic content element.
	 * @param array $data_args  {
	 *   Optional. An array of arguments for retrieving the dynamic content.
	 *   Default `[]`.
	 *
	 *   @type string  $name       Optional. Option name. Default empty string.
	 *   @type array   $settings   Optional. Option settings. Default `[]`.
	 *   @type integer $post_id    Optional. Post Id. Default `null`.
	 *   @type string  $context    Context e.g `edit`, `display`.
	 *   @type array   $overrides  An associative array of option_name => value to override option value(s).
	 *   @type bool    $is_content Whether dynamic content used in module's main_content field.
	 * }
	 *
	 * @return string           The formatted value of the dynamic content element.
	 *
	 * @example:
	 * ```php
	 * // render the current value of a dynamic content element named "my_date" with default settings.
	 * $value = $this->render_callback( '', [
	 *     'name' => 'my_date'
	 * ] );
	 * ```

	 * @example:
	 * ```php
	 * // render the value of a dynamic content element named "my_date" with custom settings.
	 * $value = $this->render_callback( '', [
	 *     'name'     => 'my_date',
	 *     'settings' => [
	 *         'date_format'         => 'custom',
	 *         'custom_date_format'  => 'Y-m-d',
	 *     ],
	 * ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		$name     = $data_args['name'] ?? '';
		$settings = $data_args['settings'] ?? [];
		$post_id  = $data_args['post_id'] ?? null;

		if ( $this->get_name() !== $name ) {
			return $value;
		}

		$format        = $settings['date_format'] ?? 'default';
		$custom_format = $settings['custom_date_format'] ?? '';

		// Different than `post_date` case, the `date_format` option may return `custom`
		// string as the value. Hence we need to check for `default` value first.
		if ( 'default' === $format ) {
			$format = strval( get_option( 'date_format' ) );
		}

		if ( 'custom' === $format ) {
			// Convert double backslashes to single for PHP date functions.
			$format = str_replace( '\\\\', '\\', $custom_format );
		}

		$value = esc_html( date_i18n( $format ) );

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
