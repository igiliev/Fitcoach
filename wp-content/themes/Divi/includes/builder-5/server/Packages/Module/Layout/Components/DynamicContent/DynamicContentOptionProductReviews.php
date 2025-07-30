<?php
/**
 * Module: DynamicContentOptionProductReviews class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Module: DynamicContentOptionProductReviews class.
 *
 * @since ??
 */
class DynamicContentOptionProductReviews extends DynamicContentOptionBase implements DynamicContentOptionInterface {

	/**
	 * Get the name of the product reviews option.
	 *
	 * @since ??
	 *
	 * @return string The name of the product reviews option.
	 */
	public function get_name(): string {
		return 'product_reviews';
	}

	/**
	 * Get the label for the product reviews option.
	 *
	 * This function retrieves the localized label for the product reviews option,
	 * which is used to describe the product reviews in user interfaces.
	 *
	 * @since ??
	 *
	 * @return string The label for the product reviews option.
	 */
	public function get_label(): string {
		return esc_html__( 'Product Reviews', 'et_builder' );
	}

	/**
	 * Callback for registering product reviews option .
	 *
	 * This function is a callback for the `divi_module_dynamic_content_options` filter .
	 * This function is used to register options for product reviews by adding them to the options array passed to the function .
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
		$post_type = get_post_type( $post_id );

		// TODO feat(D5, Theme Builder): Replace `et_theme_builder_is_layout_post_type` once
		// the Theme Builder is implemented in D5.
		// @see https://github.com/elegantthemes/Divi/issues/25149.
		$is_tb_layout_post_type = et_theme_builder_is_layout_post_type( $post_type );

		if ( ! isset( $options[ $this->get_name() ] ) && et_is_woocommerce_plugin_active() && ( 'product' === $post_type || $is_tb_layout_post_type ) ) {
			$options[ $this->get_name() ] = [
				'id'     => $this->get_name(),
				'label'  => $this->get_label(),
				'type'   => 'text',
				'custom' => false,
				'group'  => 'Default',
				'fields' => [
					'before'       => [
						'label'   => esc_html__( 'Before', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'after'        => [
						'label'   => esc_html__( 'After', 'et_builder' ),
						'type'    => 'text',
						'default' => '',
					],
					'enable_title' => [
						'label'   => esc_html__( 'Enable Title', 'et_builder' ),
						'type'    => 'yes_no_button',
						'options' => [
							'on'  => et_builder_i18n( 'Yes' ),
							'off' => et_builder_i18n( 'No' ),
						],
						'default' => 'on',
					],
				],
			];
		}

		return $options;
	}

	/**
	 * Render callback for product reviews option.
	 *
	 * Retrieves the value of product reviews option based on the provided arguments and settings.
	 * This is a callback for `divi_module_dynamic_content_resolved_value` filter.
	 *
	 * @since ??
	 *
	 * @param mixed $value     The current value of the product reviews option.
	 * @param array $data_args {
	 *     Optional. An array of arguments for retrieving the product reviews.
	 *     Default `[]`.
	 *
	 *     @type string  $name       Optional. Option name. Default empty string.
	 *     @type array   $settings   Optional. Option settings. Default `[]`.
	 *     @type integer $post_id    Optional. Post Id. Default `null`.
	 * }
	 *
	 * @return string The formatted value of the product reviews option.
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
	 *        'product_title' => 'Product reviews',
	 *      ],
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

		$post = is_int( $post_id ) && 0 !== $post_id ? get_post( $post_id ) : false;

		if ( $post ) {
			// TODO feat(D5, Woo Modules): Replace it later once the foundation of Woo
			// module is implemented in D5.
			$dynamic_product = \ET_Builder_Module_Helper_Woocommerce_Modules::get_product( $post_id );
			$value           = '';

			if ( $dynamic_product && comments_open( $dynamic_product->get_id() ) ) {
				// Product description refers to Product short description.
				// Product short description is nothing but post excerpt.
				$comments_args = [ 'post_id' => $dynamic_product->get_id() ];
				$comments      = get_comments( $comments_args );
				$total_pages   = get_comment_pages_count( $comments );
				$value         = wp_list_comments(
					[
						'callback' => 'woocommerce_comments',
						'echo'     => false,
					],
					$comments
				);

				// Pass $dynamic_product, $reviews to unify the flow of data.
				// TODO feat(D5, Woo Modules): Replace it later once the foundation of Woo
				// module is implemented in D5.
				$reviews_title        = \ET_Builder_Module_Helper_Woocommerce_Modules::get_reviews_title( $dynamic_product );
				$reviews_comment_form = \ET_Builder_Module_Helper_Woocommerce_Modules::get_reviews_comment_form( $dynamic_product, $comments );
				$no_reviews_text      = sprintf(
					'<p class="woocommerce-noreviews">%s</p>',
					esc_html__( 'There are no reviews yet.', 'et_builder' )
				);

				$no_reviews    = is_array( $comments ) && count( $comments ) > 0 ? '' : $no_reviews_text;
				$is_show_title = 'on' === ( $settings['enable_title'] ?? 'on' );

				if ( wp_doing_ajax() ) {
					$page = get_query_var( 'cpage' );
					if ( ! $page ) {
						$page = 1;
					}

					$paginate_links_args = [
						'base'         => add_query_arg( 'cpage', '%#%' ),
						'format'       => '',
						'total'        => $total_pages,
						'current'      => $page,
						'echo'         => false,
						'add_fragment' => '#comments',
						'type'         => 'list',
					];

					global $wp_rewrite;

					if ( $wp_rewrite->using_permalinks() ) {
						$paginate_links_args['base'] = user_trailingslashit( trailingslashit( get_permalink() ) . $wp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
					}

					$pagination = paginate_links( $paginate_links_args );
				} else {
					$pagination = paginate_comments_links(
						[
							'echo'  => false,
							'type'  => 'list',
							'total' => $total_pages,
						]
					);
				}

				$title = $is_show_title
					? sprintf( '<h2 class="woocommerce-Reviews-title">%s</h2>', et_core_esc_previously( $reviews_title ) )
					: '';

				$value = sprintf(
					'
						<div id="reviews" class="woocommerce-Reviews">
							%1$s
							<div id="comments">
								<ol class="commentlist">
								%2$s
								</ol>
								<nav class="woocommerce-pagination">
									%5$s
								</nav>
								%4$s
							</div>
							<div id="review_form_wrapper">
								%3$s
							</div>
						</div>
						',
					/* 1$s */ et_core_esc_previously( $title ),
					/* 2$s */ et_core_esc_previously( $value ),
					/* 3$s */ et_core_esc_previously( $reviews_comment_form ),
					/* 4$s */ et_core_esc_previously( $no_reviews ),
					/* 5$s */ et_core_esc_previously( $pagination )
				);

				// Wrap non plain text woo data to add custom selector for styling inheritance.
				$value = DynamicContentElements::get_wrapper_woo_module_element(
					[
						'name'  => $name,
						'value' => $value,
					]
				);
			}
		}

		return $value;
	}
}
