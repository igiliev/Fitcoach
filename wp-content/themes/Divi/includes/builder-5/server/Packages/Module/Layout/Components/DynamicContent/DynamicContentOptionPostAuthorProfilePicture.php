<?php
/**
 * Module: DynamicContentOptionPostAuthorProfilePicture class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptionPostAuthorProfilePicture class.
 *
 * @since ??
 */
class DynamicContentOptionPostAuthorProfilePicture extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post author profile picture option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post author profile picture option.
	 */
	public function get_name(): string {
		return 'post_author_profile_picture';
	}

	/**
	 * Get the label for the post author profile picture option.
	 *
	 * This function retrieves the localized label for the post author profile picture option,
	 * which is used to describe the post author profile picture in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post author profile picture option.
	 */
	public function get_label(): string {
		return esc_html__( 'Author Profile Picture', 'et_builder' );
	}

	/**
	 * Callback for registering post author profile picture option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post author profile picture by adding them to the options array passed to the function .
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
		}

		return $options;
	}

	/**
	 * Render callback for post author profile picture option.
	 *
	 * Retrieves the value of post author profile picture option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post author profile picture option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post author profile picture.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the post author profile picture option.
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

		if ( $this->get_name() !== $name ) {
			return $value;
		}

		$post   = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;
		$author = null;

		if ( $post ) {
			$author = get_userdata( $post->post_author );
		} elseif ( is_author() ) {
			$author = get_queried_object();
		}

		if ( ! $author ) {
			return '';
		}

		return DynamicContentElements::get_wrapper_element(
			[
				'post_id'  => $post_id,
				'name'     => $name,
				'value'    => get_avatar_url( $author->ID ),
				'settings' => $settings,
			]
		);
	}
}
