<?php
/**
 * Module: DynamicContentOptionPostFeaturedImage class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentElements;

/**
 * Module: DynamicContentOptionPostFeaturedImage class.
 *
 * @since ??
 */
class DynamicContentOptionPostFeaturedImage extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post featured image option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post featured image option.
	 */
	public function get_name(): string {
		return 'post_featured_image';
	}

	/**
	 * Get the label for the post featured image option.
	 *
	 * This function retrieves the localized label for the post featured image option,
	 * which is used to describe the post featured image in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post featured image option.
	 */
	public function get_label(): string {
		return esc_html__( 'Featured Image', 'et_builder' );
	}

	/**
	 * Callback for registering post featured image option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post featured image by adding them to the options array passed to the function .
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
			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => $this->get_label(),
				'type'   => 'image',
				'custom' => false,
				'group'  => 'Default',
			];

			$options[ 'loop_' . $this->get_name() ] = [
				'id'     => 'loop_' . $this->get_name(),
				'label'  => esc_html__( 'Loop', 'et_builder' ) . ' ' . $this->get_label(),
				'type'   => 'image',
				'custom' => false,
				'group'  => 'Loop',
				'fields' => [
					'loop_position' => [
						'label'       => esc_html__( 'Loop Position', 'et_builder' ),
						'type'        => 'text',
						'default'     => '',
						'renderAfter' => 'n',
					],
				],
			];
		}

		return $options;
	}

	/**
	 * Render callback for post featured image option.
	 *
	 * Retrieves the value of post featured image option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post featured image option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post featured image.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 *     @type array   $overrides  Optional. An associative array of `option_name => value` to override option value.
	 *                               Default `[]`.
	 * }
	 *
	 * @return string The formatted value of the post featured image option.
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
	 *        'product_featured image' => 'post featured image',
	 *      ],
	 *  ] );
	 * ```
	 */
	public function render_callback( $value, array $data_args = [] ): string {
		global $wp_query;

		$name      = $data_args['name'] ?? '';
		$settings  = $data_args['settings'] ?? [];
		$post_id   = $data_args['post_id'] ?? null;
		$overrides = $data_args['overrides'] ?? [];

		if ( $this->get_name() !== $name ) {
			return $value;
		}

		$post          = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;
		$img_url       = '';
		$is_blog_query = $wp_query->et_pb_blog_query ?? false;

		if ( isset( $overrides[ $name ] ) ) {
			$attachment_id = (int) $overrides[ $name ];
			$img_url       = wp_get_attachment_url( $attachment_id );
		} elseif ( ! $is_blog_query && ( is_category() || is_tag() || is_tax() ) ) {
			$term_id       = (int) get_queried_object_id();
			$attachment_id = (int) get_term_meta( $term_id, 'thumbnail_id', true );
			$img_url       = wp_get_attachment_url( $attachment_id );
		} elseif ( $post ) {
			$img_url = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		$value = ! empty( $img_url ) ? esc_url( $img_url ) : '';

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
