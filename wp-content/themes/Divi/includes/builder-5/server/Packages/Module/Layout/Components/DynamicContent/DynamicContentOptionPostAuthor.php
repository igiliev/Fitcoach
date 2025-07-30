<?php
/**
 * Module: DynamicContentOptionPostAuthor class.
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
 * Module: DynamicContentOptionPostAuthor class.
 *
 * @since ??
 */
class DynamicContentOptionPostAuthor extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the post author option.
	 *
	 * @since ??
	 *
	 * @return string The name of the post author option.
	 */
	public function get_name(): string {
		return 'post_author';
	}

	/**
	 * Get the label for the post author option.
	 *
	 * This function retrieves the localized label for the post author option,
	 * which is used to describe the post author in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the post author option.
	 */
	public function get_label(): string {
		// Translators: %1$s: Post type name.
		return __( '%1$s Author', 'et_builder' );
	}

	/**
	 * Callback for registering post author option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for post author by adding them to the options array passed to the function .
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
				'before'           => [
					'label'   => esc_html__( 'Before', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'after'            => [
					'label'   => esc_html__( 'After', 'et_builder' ),
					'type'    => 'text',
					'default' => '',
				],
				'name_format'      => [
					'label'   => esc_html__( 'Name Format', 'et_builder' ),
					'type'    => 'select',
					'options' => [
						'display_name'    => esc_html__( 'Public Display Name', 'et_builder' ),
						'first_last_name' => esc_html__( 'First & Last Name', 'et_builder' ),
						'last_first_name' => esc_html__( 'Last, First Name', 'et_builder' ),
						'first_name'      => esc_html__( 'First Name', 'et_builder' ),
						'last_name'       => esc_html__( 'Last Name', 'et_builder' ),
						'nickname'        => esc_html__( 'Nickname', 'et_builder' ),
						'username'        => esc_html__( 'Username', 'et_builder' ),
					],
					'default' => 'display_name',
				],
				'link'             => [
					'label'   => esc_html__( 'Link Name', 'et_builder' ),
					'type'    => 'yes_no_button',
					'options' => [
						'on'  => et_builder_i18n( 'Yes' ),
						'off' => et_builder_i18n( 'No' ),
					],
					'default' => 'off',
				],
				'link_destination' => [
					'label'   => esc_html__( 'Link Destination', 'et_builder' ),
					'type'    => 'select',
					'options' => [
						'author_archive' => esc_html__( 'Author Archive Page', 'et_builder' ),
						'author_website' => esc_html__( 'Author Website', 'et_builder' ),
					],
					'default' => 'author_archive',
					'show_if' => [
						'link' => 'on',
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
	 * Render callback for post author option.
	 *
	 * Retrieves the value of post author option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the post author option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the post author.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the post author option.
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

		$post   = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;
		$author = null;

		if ( $post ) {
			$author = get_userdata( $post->post_author );
		} elseif ( is_author() ) {
			$author = get_queried_object();
		}

		if ( ! $author ) {
			$value = '';
		} else {
			$name_format      = $settings['name_format'] ?? 'display_name';
			$link             = $settings['link'] ?? 'off';
			$link_destination = $settings['link_destination'] ?? '';
			$is_link          = 'on' === $link;
			$link_target      = 'author_archive' === $link_destination ? '_self' : '_blank';
			$label            = '';
			$url              = '';

			switch ( $name_format ) {
				case 'display_name':
					$label = $author->display_name;
					break;
				case 'first_last_name':
					$label = $author->first_name . ' ' . $author->last_name;
					break;
				case 'last_first_name':
					$label = $author->last_name . ', ' . $author->first_name;
					break;
				case 'first_name':
					$label = $author->first_name;
					break;
				case 'last_name':
					$label = $author->last_name;
					break;
				case 'nickname':
					$label = $author->nickname;
					break;
				case 'username':
					$label = $author->user_login;
					break;
			}

			switch ( $link_destination ) {
				case 'author_archive':
					$url = get_author_posts_url( $author->ID );
					break;
				case 'author_website':
					$url = $author->user_url;
					break;
			}

			$value = esc_html( $label );

			if ( $is_link && ! empty( $url ) ) {
				$value = sprintf(
					'<a href="%1$s" target="%2$s">%3$s</a>',
					esc_url( $url ),
					et_core_intentionally_unescaped( $link_target, 'fixed_string' ),
					et_core_esc_previously( $value )
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
