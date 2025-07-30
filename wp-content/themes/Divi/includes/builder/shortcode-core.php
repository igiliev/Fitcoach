<?php

/**
 * AJAX Callback: Create widget area from wp dashbaoard Widgets screen.
 */
function et_pb_add_widget_area() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		die( -1 );
	}

	$et_pb_widgets = get_theme_mod( 'et_pb_widgets' );

	$number = $et_pb_widgets ? intval( $et_pb_widgets['number'] ) + 1 : 1;

	$et_widget_area_name                                      = isset( $_POST['et_widget_area_name'] ) ? sanitize_text_field( $_POST['et_widget_area_name'] ) : '';
	$et_pb_widgets['areas'][ 'et_pb_widget_area_' . $number ] = $et_widget_area_name;
	$et_pb_widgets['number']                                  = $number;

	set_theme_mod( 'et_pb_widgets', $et_pb_widgets );

	et_pb_force_regenerate_templates();

	printf(
	// translators: %1$s: widget area name.
		et_get_safe_localization( __( '<strong>%1$s</strong> widget area has been created. You can create more areas, once you finish update the page to see all the areas.', 'et_builder' ) ),
		esc_html( $et_widget_area_name )
	);

	die();
}
add_action( 'wp_ajax_et_pb_add_widget_area', 'et_pb_add_widget_area' );

/**
 * AJAX Callback: Remove widget area from wp dashbaoard Widgets screen.
 */
function et_pb_remove_widget_area() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		die( -1 );
	}

	$et_pb_widgets = get_theme_mod( 'et_pb_widgets' );

	$et_widget_area_name = isset( $_POST['et_widget_area_name'] ) ? sanitize_text_field( $_POST['et_widget_area_name'] ) : '';
	unset( $et_pb_widgets['areas'][ $et_widget_area_name ] );

	set_theme_mod( 'et_pb_widgets', $et_pb_widgets );

	et_pb_force_regenerate_templates();

	die( esc_html( $et_widget_area_name ) );
}
add_action( 'wp_ajax_et_pb_remove_widget_area', 'et_pb_remove_widget_area' );

/**
 * AJAX Callback: Check if current user has permission to lock/unlock content.
 */
function et_pb_current_user_can_lock() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	$permission = et_pb_is_allowed( 'lock_module' );
	$permission = wp_json_encode( (bool) $permission );

	die( et_core_esc_previously( $permission ) );
}
add_action( 'wp_ajax_et_pb_current_user_can_lock', 'et_pb_current_user_can_lock' );


/**
 * Return an array of post types which have the frontend builder enabled.
 *
 * @return mixed|void
 */
function et_builder_get_fb_post_types() {
	/**
	 * Array of post types which have the frontend builder enabled.
	 *
	 * @since 3.10
	 *
	 * @param string[]
	 */
	return apply_filters( 'et_fb_post_types', et_builder_get_enabled_builder_post_types() );
}



/**
 * Retrieve similar post types for the given post type.
 *
 * @param string $post_type The post type for which retrieve similar post types.
 *
 * @return array
 */
function et_pb_show_all_layouts_built_for_post_type( $post_type ) {
	$similar_post_types = array(
		'post',
		'page',
		'project',
	);

	if ( in_array( $post_type, $similar_post_types, true ) ) {
		return $similar_post_types;
	}

	return $post_type;
}
add_filter( 'et_pb_show_all_layouts_built_for_post_type', 'et_pb_show_all_layouts_built_for_post_type' );

/**
 * AJAX Callback :: Load layouts.
 */
function et_pb_show_all_layouts() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	printf(
		'
		<label for="et_pb_load_layout_replace">
			<input name="et_pb_load_layout_replace" type="checkbox" id="et_pb_load_layout_replace" %2$s/>
			<span>%1$s</span>
		</label>',
		esc_html__( 'Replace the existing content with loaded layout', 'et_builder' ),
		checked( get_theme_mod( 'et_pb_replace_content', 'on' ), 'on', false )
	);

	$post_type    = ! empty( $_POST['et_layouts_built_for_post_type'] ) ? sanitize_text_field( $_POST['et_layouts_built_for_post_type'] ) : 'post';
	$layouts_type = ! empty( $_POST['et_load_layouts_type'] ) ? sanitize_text_field( $_POST['et_load_layouts_type'] ) : 'predefined';

	$predefined_operator = 'predefined' === $layouts_type ? 'EXISTS' : 'NOT EXISTS';

	$post_type = apply_filters( 'et_pb_show_all_layouts_built_for_post_type', $post_type, $layouts_type );

	$query_args = array(
		'meta_query'       => array(
			'relation' => 'AND',
			array(
				'key'     => '_et_pb_predefined_layout',
				'value'   => 'on',
				'compare' => $predefined_operator,
			),
			array(
				'key'     => '_et_pb_built_for_post_type',
				'value'   => $post_type,
				'compare' => 'IN',
			),
			array(
				'key'     => '_et_pb_layout_applicability',
				'value'   => 'product_tour',
				'compare' => 'NOT EXISTS',
			),
		),
		'tax_query'        => array(
			array(
				'taxonomy' => 'layout_type',
				'field'    => 'slug',
				'terms'    => array( 'section', 'row', 'module', 'fullwidth_section', 'specialty_section', 'fullwidth_module' ),
				'operator' => 'NOT IN',
			),
		),
		'post_type'        => ET_BUILDER_LAYOUT_POST_TYPE,
		'posts_per_page'   => '-1',
		'suppress_filters' => 'predefined' === $layouts_type,
	);

	$query = new WP_Query( $query_args );

	if ( $query->have_posts() ) :

		echo '<ul class="et-pb-all-modules et-pb-load-layouts">';

		while ( $query->have_posts() ) :
			$query->the_post();

			$button_html = 'predefined' !== $layouts_type ?
				sprintf(
					'<a href="#" class="button et_pb_layout_button_delete">%1$s</a>',
					esc_html__( 'Delete', 'et_builder' )
				)
				: '';

			printf(
				'<li class="et_pb_text" data-layout_id="%2$s">%1$s<span class="et_pb_layout_buttons"><a href="#" class="button-primary et_pb_layout_button_load">%3$s</a>%4$s</span></li>',
				esc_html( get_the_title() ),
				esc_attr( get_the_ID() ),
				esc_html__( 'Load', 'et_builder' ),
				et_core_esc_previously( $button_html )
			);

		endwhile;

		echo '</ul>';
	endif;

	wp_reset_postdata();

	die();
}
add_action( 'wp_ajax_et_pb_show_all_layouts', 'et_pb_show_all_layouts' );

/**
 * AJAX Callback :: Retrieves saved builder layouts.
 */
function et_pb_get_saved_templates() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	$layout_type     = ! empty( $_POST['et_layout_type'] ) ? sanitize_text_field( $_POST['et_layout_type'] ) : 'layout';
	$module_width    = ! empty( $_POST['et_module_width'] ) && 'module' === $layout_type ? sanitize_text_field( $_POST['et_module_width'] ) : '';
	$is_global       = ! empty( $_POST['et_is_global'] ) ? sanitize_text_field( $_POST['et_is_global'] ) : 'false';
	$specialty_query = ! empty( $_POST['et_specialty_columns'] ) && 'row' === $layout_type ? sanitize_text_field( $_POST['et_specialty_columns'] ) : '0';
	$post_type       = ! empty( $_POST['et_post_type'] ) ? sanitize_text_field( $_POST['et_post_type'] ) : 'post';

	$templates_data = et_pb_retrieve_templates( $layout_type, $module_width, $is_global, $specialty_query, $post_type );

	if ( empty( $templates_data ) ) {
		$templates_data = array( 'error' => esc_html__( 'You have not saved any items to your Divi Library yet. Once an item has been saved to your library, it will appear here for easy use.', 'et_builder' ) );
	}

	$json_templates = wp_json_encode( $templates_data );

	die( et_core_esc_previously( $json_templates ) );
}
add_action( 'wp_ajax_et_pb_get_saved_templates', 'et_pb_get_saved_templates' );

/**
 * Retrieves saved builder layouts.
 *
 * @since 2.0
 *
 * @param string $layout_type     Accepts 'section', 'row', 'module', 'fullwidth_section',
 *                                'specialty_section', 'fullwidth_module'.
 * @param string $module_width    Accepts 'regular', 'fullwidth'.
 * @param string $is_global       Filter layouts based on their scope. Accepts 'global' to include
 *                                only global layouts, 'false' to include only non-global layouts,
 *                                or 'all' to include both global and non-global layouts.
 * @param string $specialty_query Limit results to layouts of type 'row' that can be put inside
 *                                specialty sections. Accepts '3' to include only 3-column rows,
 *                                '2' for 2-column rows, or '0' to disable the specialty query. Default '0'.
 * @param string $post_type       Limit results to layouts built for this post type.
 * @param string $deprecated      Deprecated.
 * @param array  $boundaries      {
 *
 *     Return a subset of the total results.
 *
 *     @type int $offset Start from this point in the results. Default `0`.
 *     @type int $limit  Maximum number of results to return. Default `-1`.
 * }
 *
 * @return array[] $layouts {
 *
 *     @type mixed[] {
 *
 *         Layout Data
 *
 *         @type int      $ID               The layout's post id.
 *         @type string   $title            The layout's title/name.
 *         @type string   $shortcode        The layout's shortcode content.
 *         @type string   $is_global        The layout's scope. Accepts 'global', 'non_global'.
 *         @type string   $layout_type      The layout's type. See {@see self::$layout_type} param for accepted values.
 *         @type string   $applicability    The layout's applicability.
 *         @type string   $layouts_type     Deprecated. Will always be 'library'.
 *         @type string   $module_type      For layouts of type 'module', the module type/slug (eg. et_pb_blog).
 *         @type string[] $categories       This layout's assigned categories (slugs).
 *         @type string   $row_layout       For layout's of type 'row', the row layout type (eg. 4_4).
 *         @type mixed[]  $unsynced_options For global layouts, the layout's unsynced settings.
 *     }
 *     ...
 * }
 */
function et_pb_retrieve_templates( $layout_type = 'layout', $module_width = '', $is_global = 'false', $specialty_query = '0', $post_type = 'post', $deprecated = '', $boundaries = array() ) {
	$templates_data         = array();
	$suppress_filters       = false;
	$extra_layout_post_type = 'layout';
	$module_icons           = ET_Builder_Element::get_module_icons();
	$utils                  = ET_Core_Data_Utils::instance();
	$similar_post_types     = array_keys( ET_Builder_Settings::get_registered_post_type_options() );

	// All default and 3rd party post types considered similar and share the same library items, so retrieve all items for any post type from the list.
	$post_type = in_array( $post_type, $similar_post_types, true ) ? $similar_post_types : $post_type;

	// need specific query for the layouts.
	if ( 'layout' === $layout_type ) {

		if ( 'all' === $post_type ) {
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => '_et_pb_built_for_post_type',
					'value'   => $extra_layout_post_type,
					'compare' => 'NOT IN',
				),
			);
		} else {
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => '_et_pb_built_for_post_type',
					'value'   => $post_type,
					'compare' => 'IN',
				),
			);
		}

		$tax_query        = array(
			array(
				'taxonomy' => 'layout_type',
				'field'    => 'slug',
				'terms'    => array( 'section', 'row', 'module', 'fullwidth_section', 'specialty_section', 'fullwidth_module' ),
				'operator' => 'NOT IN',
			),
		);
		$suppress_filters = 'predefined' === $layout_type;
	} else {
		$additional_condition = '' !== $module_width ?
			array(
				'taxonomy' => 'module_width',
				'field'    => 'slug',
				'terms'    => $module_width,
			) : '';

		$meta_query = array();

		if ( '0' !== $specialty_query ) {
			$columns_val  = '3' === $specialty_query ? array( '4_4', '1_2,1_2', '1_3,1_3,1_3' ) : array( '4_4', '1_2,1_2' );
			$meta_query[] = array(
				'key'     => '_et_pb_row_layout',
				'value'   => $columns_val,
				'compare' => 'IN',
			);
		}

		$post_type    = apply_filters( 'et_pb_show_all_layouts_built_for_post_type', $post_type, $layout_type );
		$meta_query[] = array(
			'key'     => '_et_pb_built_for_post_type',
			'value'   => $post_type,
			'compare' => 'IN',
		);

		$tax_query = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'layout_type',
				'field'    => 'slug',
				'terms'    => $layout_type,
			),
			$additional_condition,
		);

		if ( 'all' !== $is_global ) {
			$global_operator = 'global' === $is_global ? 'IN' : 'NOT IN';
			$tax_query[]     = array(
				'taxonomy' => 'scope',
				'field'    => 'slug',
				'terms'    => array( 'global' ),
				'operator' => $global_operator,
			);
		}
	}

	$start_from = 0;
	$limit_to   = '-1';

	if ( ! empty( $boundaries ) ) {
		$start_from = $boundaries[0];
		$limit_to   = $boundaries[1];
	}

	/**
	 * Filter suppress_filters argument.
	 *
	 * @since 4.4.5
	 *
	 * @param boolean $suppress_filters
	 */
	$suppress_filters = wp_validate_boolean( apply_filters( 'et_pb_show_all_layouts_suppress_filters', $suppress_filters ) );

	$query = new WP_Query(
		array(
			'tax_query'        => $tax_query,
			'post_type'        => ET_BUILDER_LAYOUT_POST_TYPE,
			'posts_per_page'   => $limit_to,
			'meta_query'       => $meta_query,
			'offset'           => $start_from,
			'suppress_filters' => $suppress_filters,
		)
	);

	if ( ! empty( $query->posts ) ) {
		// Call the_post() to properly configure post data. Make sure to call the_post() and
		// wp_reset_postdata() only if the posts result exist to avoid unexpected issues.
		$query->the_post();

		wp_reset_postdata();

		foreach ( $query->posts as $single_post ) {

			if ( 'module' === $layout_type ) {
				$module_type = get_post_meta( $single_post->ID, '_et_pb_module_type', true );
			} else {
				$module_type = '';
			}

			// add only modules allowed for current user.
			if ( '' === $module_type || et_pb_is_allowed( $module_type ) ) {
				$categories                = wp_get_post_terms( $single_post->ID, 'layout_category' );
				$scope                     = wp_get_post_terms( $single_post->ID, 'scope' );
				$global_scope              = isset( $scope[0] ) ? $scope[0]->slug : 'non_global';
				$categories_processed      = array();
				$row_layout                = '';
				$this_layout_type          = '';
				$this_layout_applicability = '';

				if ( ! empty( $categories ) ) {
					foreach ( $categories as $category_data ) {
						$categories_processed[] = esc_html( $category_data->slug );
					}
				}

				if ( 'row' === $layout_type ) {
					$row_layout = get_post_meta( $single_post->ID, '_et_pb_row_layout', true );
				}

				if ( 'layout' === $layout_type ) {
					$this_layout_type          = 'on' === get_post_meta( $single_post->ID, '_et_pb_predefined_layout', true ) ? 'predefined' : 'library';
					$this_layout_applicability = get_post_meta( $single_post->ID, '_et_pb_layout_applicability', true );
				}

				// get unsynced global options for module.
				if ( 'module' === $layout_type && 'false' !== $is_global ) {
					$unsynced_options = get_post_meta( $single_post->ID, '_et_pb_excluded_global_options' );
				}

				$templates_datum = array(
					'ID'               => (int) $single_post->ID,
					'title'            => esc_html( $single_post->post_title ),
					'shortcode'        => et_core_intentionally_unescaped( $single_post->post_content, 'html' ),
					'is_global'        => esc_html( $global_scope ),
					'layout_type'      => esc_html( $layout_type ),
					'applicability'    => esc_html( $this_layout_applicability ),
					'layouts_type'     => esc_html( $this_layout_type ),
					'module_type'      => esc_html( $module_type ),
					'categories'       => et_core_esc_previously( $categories_processed ),
					'row_layout'       => esc_html( $row_layout ),
					'unsynced_options' => ! empty( $unsynced_options ) ? $utils->esc_array( json_decode( $unsynced_options[0], true ), 'sanitize_text_field' ) : array(),
				);

				if ( $module_type ) {

					// Append icon if there's any.
					$template_icon = $utils->array_get( $module_icons, "{$module_type}.icon", false );
					if ( $template_icon ) {
						$templates_datum['icon'] = $template_icon;
					}

					// Append svg icon if there's any.
					$template_icon_svg = $utils->array_get( $module_icons, "{$module_type}.icon_svg", false );
					if ( $template_icon_svg ) {
						$templates_datum['icon_svg'] = $template_icon_svg;
					}
				}

				$templates_data[] = $templates_datum;
			}
		}
	}

	return $templates_data;
}

/**
 * AJAX Callback :: Add template meta.
 */
function et_pb_add_template_meta() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	$post_id = ! empty( $_POST['et_post_id'] ) ? sanitize_text_field( $_POST['et_post_id'] ) : '';

	if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		die( -1 );
	}

	$value        = ! empty( $_POST['et_meta_value'] ) ? sanitize_text_field( $_POST['et_meta_value'] ) : '';
	$custom_field = ! empty( $_POST['et_custom_field'] ) ? sanitize_text_field( $_POST['et_custom_field'] ) : '';

	$allowlisted_meta_keys = array(
		'_et_pb_row_layout',
		'_et_pb_module_type',
	);

	if ( in_array( $custom_field, $allowlisted_meta_keys, true ) ) {
		update_post_meta( $post_id, $custom_field, $value );
	}
}
add_action( 'wp_ajax_et_pb_add_template_meta', 'et_pb_add_template_meta' );

if ( ! function_exists( 'et_pb_add_new_layout' ) ) {
	/**
	 * AJAX Callback :: Save layout to database.
	 */
	function et_pb_add_new_layout() {
		if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
			die( -1 );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			die( -1 );
		}

		// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput -- et_layout_options is json object, and this function sanitize object values at the time of using it.
		$fields_data = isset( $_POST['et_layout_options'] ) ? trim( $_POST['et_layout_options'] ) : '';

		if ( empty( $fields_data ) ) {
			die();
		}

		et_builder_load_library();

		$fields_data_json     = str_replace( '\\', '', $fields_data );
		$fields_data_array    = json_decode( $fields_data_json, true );
		$processed_data_array = array();

		// prepare array with fields data in convenient format.
		if ( ! empty( $fields_data_array ) ) {
			foreach ( $fields_data_array as $index => $field_data ) {
				$processed_data_array[ $field_data['field_id'] ] = $field_data['field_val'];
			}
		}

		$processed_data_array = apply_filters( 'et_pb_new_layout_data_from_form', $processed_data_array, $fields_data_array );

		if ( empty( $processed_data_array ) ) {
			die();
		}

		$layout_location  = et_()->array_get_sanitized( $processed_data_array, 'et_pb_template_cloud', 'local' );
		$layout_type      = et_()->array_get_sanitized( $processed_data_array, 'new_template_type', 'layout' );
		$layout_is_global = 'global' === et_()->array_get( $processed_data_array, 'et_pb_template_global', 'not_global' );
		if ( 'layout' === $layout_type ) {
			// Layouts of type 'layout' are not allowed to be global.
			$layout_is_global = false;
		}

		$args = array(
			'layout_type'          => $layout_type,
			'template_type'        => $layout_type,
			'layout_selected_cats' => ! empty( $processed_data_array['selected_cats'] ) ? sanitize_text_field( $processed_data_array['selected_cats'] ) : '',
			'layout_selected_tags' => ! empty( $processed_data_array['selected_tags'] ) ? sanitize_text_field( $processed_data_array['selected_tags'] ) : '',
			'built_for_post_type'  => ! empty( $processed_data_array['et_builder_layout_built_for_post_type'] ) ? sanitize_text_field( $processed_data_array['et_builder_layout_built_for_post_type'] ) : 'page',
			'layout_new_cat'       => ! empty( $processed_data_array['et_pb_new_cat_name'] ) ? sanitize_text_field( $processed_data_array['et_pb_new_cat_name'] ) : '',
			'layout_new_tag'       => ! empty( $processed_data_array['et_pb_new_tag_name'] ) ? sanitize_text_field( $processed_data_array['et_pb_new_tag_name'] ) : '',
			'columns_layout'       => ! empty( $processed_data_array['et_columns_layout'] ) ? sanitize_text_field( $processed_data_array['et_columns_layout'] ) : '0',
			'module_type'          => ! empty( $processed_data_array['et_module_type'] ) ? sanitize_text_field( $processed_data_array['et_module_type'] ) : 'et_pb_unknown',
			'layout_scope'         => $layout_is_global ? 'global' : 'not_global',
			'layout_location'      => $layout_location,
			'module_width'         => 'regular',
			'layout_content'       => ! empty( $processed_data_array['template_shortcode'] ) ? $processed_data_array['template_shortcode'] : '',
			'layout_name'          => ! empty( $processed_data_array['et_pb_new_template_name'] ) ? sanitize_text_field( $processed_data_array['et_pb_new_template_name'] ) : '',
		);

		// construct the initial shortcode for new layout.
		switch ( $args['layout_type'] ) {
			case 'row':
				$args['layout_content'] = '<!-- wp:divi/section /-->';
				$args['layout_type']    = 'row';
				break;
			case 'section':
				$args['layout_content'] = '<!-- wp:divi/section /-->';
				$args['layout_type']    = 'section';
				break;
			case 'module':
				$args['layout_content'] = '<!-- wp:divi/placeholder -->
<!-- wp:divi/section -->
<!-- wp:divi/row {"module":{"advanced":{"columnStructure":{"desktop":{"value":"4_4"}}}}} -->
<!-- wp:divi/column {"module":{"advanced":{"type":{"desktop":{"value":"4_4"}}}}} /-->
<!-- /wp:divi/row -->
<!-- /wp:divi/section --><!-- /wp:divi/placeholder -->';
				break;
			case 'fullwidth_module':
				$args['layout_content'] = '<!-- wp:divi/placeholder --><!-- wp:divi/section {"module":{"advanced":{"type":{"desktop":{"value":"fullwidth"}}}}} /--><!-- /wp:divi/placeholder -->';
				$args['module_width']   = 'fullwidth';
				$args['layout_type']    = 'module';
				break;
			case 'fullwidth_section':
				$args['layout_content'] = '<!-- wp:divi/placeholder --><!-- wp:divi/section {"module":{"advanced":{"type":{"desktop":{"value":"fullwidth"}}}}} /--><!-- /wp:divi/placeholder -->';
				$args['layout_type']    = 'section';
				break;
			case 'specialty_section':
				$args['layout_content'] = '<!-- wp:divi/placeholder  /-->';
				$args['layout_type']    = 'section';
				break;
		}

		$new_layout_meta = et_pb_submit_layout( apply_filters( 'et_pb_new_layout_args', $args ) );
		die( et_core_esc_previously( $new_layout_meta ) );
	}
}
add_action( 'wp_ajax_et_pb_add_new_layout', 'et_pb_add_new_layout' );

if ( ! function_exists( 'et_pb_submit_layout' ) ) :
	/**
	 * Handles saving layouts to the database for the builder. Essentially just a wrapper for
	 * {@see et_pb_create_layout()} that processes the data from the builder before passing it on.
	 *
	 * @since 1.0
	 *
	 * @param array $args {
	 *     Layout Data.
	 *
	 *     @type string $layout_type          Accepts 'layout', 'section', 'row', 'module'.
	 *     @type string $layout_selected_cats Categories to which the layout should be added. This should
	 *                                        be one or more IDs separated by pipe symbols. Example: '1|2|3'.
	 *     @type string $built_for_post_type  The post type for which the layout was built.
	 *     @type string $layout_new_cat       Name of a new category to which the layout should be added.
	 *     @type string $columns_layout       When 'layout_type' is 'row', the row's columns structure. Example: '1_4'.
	 *     @type string $module_type          When 'layout_type' is 'module', the module type. Example: 'et_pb_blurb'.
	 *     @type string $layout_scope         Optional. The layout's scope. Accepts: 'global', 'not_global'.
	 *     @type string $module_width         When 'layout_type' is 'module', the module's width. Accepts: 'regular', 'fullwidth'.
	 *     @type string $layout_content       The layout's content (unprocessed shortcodes).
	 *     @type string $layout_name          The layout's name.
	 * }
	 *
	 * @return string $layout_data The 'post_id' and 'edit_link' for the saved layout (JSON encoded).
	 */
	function et_pb_submit_layout( $args ) {
		/**
		 * Filters the layout data passed to {@see et_pb_submit_layout()}.
		 *
		 * @since 3.0.99
		 *
		 * @param string[] $args See {@see et_pb_submit_layout()} for array structure definition.
		 */
		$args = apply_filters( 'et_pb_submit_layout_args', $args );

		if ( empty( $args ) ) {
			return '';
		}

		$layout_cats_processed = array();
		$layout_tags_processed = array();

		if ( '' !== $args['layout_selected_cats'] ) {
			$layout_cats_array     = explode( ',', $args['layout_selected_cats'] );
			$layout_cats_processed = array_map( 'intval', $layout_cats_array );
		}

		if ( '' !== $args['layout_selected_tags'] ) {
			$layout_tags_array     = explode( ',', $args['layout_selected_tags'] );
			$layout_tags_processed = array_map( 'intval', $layout_tags_array );
		}

		$meta = array();

		if ( 'row' === $args['layout_type'] && '0' !== $args['columns_layout'] ) {
			$meta = array_merge( $meta, array( '_et_pb_row_layout' => $args['columns_layout'] ) );
		}

		if ( 'module' === $args['layout_type'] ) {
			$meta = array_merge( $meta, array( '_et_pb_module_type' => $args['module_type'] ) );

			// save unsynced options for global modules. Always empty for new modules.
			if ( 'global' === $args['layout_scope'] ) {
				$meta = array_merge( $meta, array( '_et_pb_excluded_global_options' => wp_json_encode( array() ) ) );
			}
		}

		// et_layouts_built_for_post_type.
		$meta = array_merge(
			$meta,
			array(
				'_et_pb_built_for_post_type' => $args['built_for_post_type'],
				'_et_pb_template_type'       => $args['template_type'] ?? '',
			)
		);

		$tax_input = array(
			'scope'           => $args['layout_scope'],
			'layout_type'     => $args['layout_type'],
			'module_width'    => $args['module_width'],
			'layout_category' => $layout_cats_processed,
			'layout_tag'      => $layout_tags_processed,
			'layout_location' => et_()->array_get_sanitized( $args, 'layout_location', 'local' ),
		);

		$new_layout_id            = et_pb_create_layout( $args['layout_name'], $args['layout_content'], $meta, $tax_input, $args['layout_new_cat'], $args['layout_new_tag'] );
		$new_post_data['post_id'] = (int) $new_layout_id;

		$new_post_data['edit_link'] = esc_url_raw( get_edit_post_link( $new_layout_id ) );
		$json_post_data             = wp_json_encode( $new_post_data );

		return $json_post_data;
	}
endif;

if ( ! function_exists( 'et_pb_create_layout' ) ) :
	/**
	 * Create new layout.
	 *
	 * @param string $name The layout name.
	 * @param string $content The layout content.
	 * @param array  $meta The layout meta.
	 * @param array  $tax_input  Array of taxonomy terms keyed by their taxonomy name.
	 * @param string $new_category The layout category.
	 */
	function et_pb_create_layout( $name, $content, $meta = array(), $tax_input = array(), $new_category = '', $new_tag = '', $post_status = 'publish' ) {
		$layout = array(
			'post_title'   => sanitize_text_field( $name ),
			'post_content' => $content,
			'post_status'  => $post_status,
			'post_type'    => ET_BUILDER_LAYOUT_POST_TYPE,
		);

		$layout_id = wp_insert_post( $layout );

		if ( ! empty( $meta ) ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				add_post_meta( $layout_id, $meta_key, sanitize_text_field( $meta_value ) );
			}
		}

		if ( '' !== $new_category ) {
			// Multiple categories could be provided.
			$category_names = explode( ',', $new_category );

			foreach ( $category_names as $term_name ) {
				$new_term = wp_insert_term( $term_name, 'layout_category' );

				if ( ! is_wp_error( $new_term ) && isset( $new_term['term_id'] ) ) {
					$tax_input['layout_category'][] = (int) $new_term['term_id'];
				}
			}
		}

		if ( '' !== $new_tag ) {
			// Multiple tags could be provided.
			$tag_names = explode( ',', $new_tag );

			foreach ( $tag_names as $term_name ) {
				$new_term = wp_insert_term( $term_name, 'layout_tag' );

				if ( ! is_wp_error( $new_term ) && isset( $new_term['term_id'] ) ) {
					$tax_input['layout_tag'][] = (int) $new_term['term_id'];
				}
			}
		}

		if ( ! empty( $tax_input ) ) {
			foreach ( $tax_input as $taxonomy => $terms ) {
				wp_set_post_terms( $layout_id, $terms, $taxonomy );
			}

			return $layout_id;
		}
	}
endif;

/**
 * AJAX Callback :: Save layout to the database for the builder.
 */
function et_pb_save_layout() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	if ( empty( $_POST['et_layout_name'] ) ) {
		die();
	}

	$args = array(
		'layout_type'          => isset( $_POST['et_layout_type'] ) ? sanitize_text_field( $_POST['et_layout_type'] ) : 'layout',
		'layout_selected_cats' => isset( $_POST['et_layout_cats'] ) ? sanitize_text_field( $_POST['et_layout_cats'] ) : '',
		'built_for_post_type'  => isset( $_POST['et_post_type'] ) ? sanitize_text_field( $_POST['et_post_type'] ) : 'page',
		'layout_new_cat'       => isset( $_POST['et_layout_new_cat'] ) ? sanitize_text_field( $_POST['et_layout_new_cat'] ) : '',
		'layout_new_tag'       => isset( $_POST['et_layout_new_tag'] ) ? sanitize_text_field( $_POST['et_layout_new_tag'] ) : '',
		'columns_layout'       => isset( $_POST['et_columns_layout'] ) ? sanitize_text_field( $_POST['et_columns_layout'] ) : '0',
		'module_type'          => isset( $_POST['et_module_type'] ) ? sanitize_text_field( $_POST['et_module_type'] ) : 'et_pb_unknown',
		'layout_scope'         => isset( $_POST['et_layout_scope'] ) ? sanitize_text_field( $_POST['et_layout_scope'] ) : 'not_global',
		'module_width'         => isset( $_POST['et_module_width'] ) ? sanitize_text_field( $_POST['et_module_width'] ) : 'regular',
		'layout_content'       => isset( $_POST['et_layout_content'] ) ? $_POST['et_layout_content'] : '', // phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- wp_insert_post function does sanitization.
		'layout_name'          => isset( $_POST['et_layout_name'] ) ? sanitize_text_field( $_POST['et_layout_name'] ) : '',
	);

	$new_layout_meta = et_pb_submit_layout( $args );
	die( et_core_esc_previously( $new_layout_meta ) );
}
add_action( 'wp_ajax_et_pb_save_layout', 'et_pb_save_layout' );

/**
 * AJAX Callback :: Get layouts.
 */
function et_pb_get_global_module() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	$global_shortcode = array();

	$utils = ET_Core_Data_Utils::instance();

	$post_id = isset( $_POST['et_global_id'] ) ? (int) $_POST['et_global_id'] : '';

	if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		die( -1 );
	}

	$global_autop = isset( $_POST['et_global_autop'] ) ? sanitize_text_field( $_POST['et_global_autop'] ) : 'apply';

	if ( ! empty( $post_id ) ) {
		$query = new WP_Query(
			array(
				'p'         => $post_id,
				'post_type' => ET_BUILDER_LAYOUT_POST_TYPE,
			)
		);

		if ( ! empty( $query->post ) ) {
			// Call the_post() to properly configure post data. Make sure to call the_post() and
			// wp_reset_postdata() only if the posts result exist to avoid unexpected issues.
			$query->the_post();

			wp_reset_postdata();

			if ( 'skip' === $global_autop ) {
				$post_content = $query->post->post_content;
			} else {
				$post_content = $query->post->post_content;
				// do prep.
				$post_content = et_pb_prep_code_module_for_wpautop( $post_content );

				// wpautop does its "thing".
				$post_content = wpautop( $post_content );

				// undo prep.
				$post_content = et_pb_unprep_code_module_for_wpautop( $post_content );
			}

			$global_shortcode['shortcode'] = et_core_intentionally_unescaped( $post_content, 'html' );
			$excluded_global_options       = get_post_meta( $post_id, '_et_pb_excluded_global_options' );
			$selective_sync_status         = empty( $excluded_global_options ) ? '' : 'updated';

			$global_shortcode['sync_status'] = et_core_intentionally_unescaped( $selective_sync_status, 'fixed_string' );
			// excluded_global_options is an array with single value which is json string, so just `sanitize_text_field`, because `esc_html` converts quotes and breaks the json string.
			$global_shortcode['excluded_options'] = $utils->esc_array( $excluded_global_options, 'sanitize_text_field' );
		}
	}

	if ( empty( $global_shortcode ) ) {
		$global_shortcode['error'] = 'nothing';
	}

	$json_post_data = wp_json_encode( $global_shortcode );

	die( et_core_esc_previously( $json_post_data ) );
}
add_action( 'wp_ajax_et_pb_get_global_module', 'et_pb_get_global_module' );

/**
 * AJAX Callback :: Update layouts.
 */
function et_pb_update_layout() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	$post_id     = isset( $_POST['et_template_post_id'] ) ? absint( $_POST['et_template_post_id'] ) : '';
	$new_content = isset( $_POST['et_layout_content'] ) ? $_POST['et_layout_content'] : ''; // phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- wp_insert_post function does sanitization.
	$layout_type = isset( $_POST['et_layout_type'] ) ? sanitize_text_field( $_POST['et_layout_type'] ) : '';

	if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		die( -1 );
	}

	$update = array(
		'ID'           => $post_id,
		'post_content' => $new_content,
	);

	$result = wp_update_post( $update );

	if ( ! $result || is_wp_error( $result ) ) {
		wp_send_json_error();
	}

	ET_Core_PageResource::remove_static_resources( 'all', 'all' );

	if ( 'module' === $layout_type && isset( $_POST['et_unsynced_options'] ) ) {
		$unsynced_options = stripslashes( sanitize_text_field( $_POST['et_unsynced_options'] ) );

		update_post_meta( $post_id, '_et_pb_excluded_global_options', $unsynced_options );
	}

	die();
}
add_action( 'wp_ajax_et_pb_update_layout', 'et_pb_update_layout' );

/**
 * AJAX Callback :: Get/load layout.
 */
function et_pb_load_layout() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		die( -1 );
	}

	$layout_id = ! empty( $_POST['et_layout_id'] ) ? (int) $_POST['et_layout_id'] : 0;

	if ( empty( $layout_id ) || ! current_user_can( 'edit_post', $layout_id ) ) {
		die( -1 );
	}

	// sanitize via allowlisting.
	$replace_content = isset( $_POST['et_replace_content'] ) && 'on' === $_POST['et_replace_content'] ? 'on' : 'off';

	set_theme_mod( 'et_pb_replace_content', $replace_content );

	$layout = get_post( $layout_id );

	if ( $layout ) {
		echo et_core_esc_previously( $layout->post_content );
	}

	die();
}
add_action( 'wp_ajax_et_pb_load_layout', 'et_pb_load_layout' );

/**
 * AJAX Callback :: Delete layout.
 */
function et_pb_delete_layout() {
	if ( ! isset( $_POST['et_admin_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['et_admin_load_nonce'] ), 'et_admin_load_nonce' ) ) {
		die( -1 );
	}

	$layout_id = ! empty( $_POST['et_layout_id'] ) ? (int) $_POST['et_layout_id'] : '';

	if ( empty( $layout_id ) ) {
		die( -1 );
	}

	if ( ! current_user_can( 'delete_post', $layout_id ) ) {
		die( -1 );
	}

	wp_delete_post( $layout_id );

	die();
}
add_action( 'wp_ajax_et_pb_delete_layout', 'et_pb_delete_layout' );
