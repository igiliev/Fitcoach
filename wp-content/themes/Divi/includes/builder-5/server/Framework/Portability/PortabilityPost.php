<?php
/**
 * PortabilityPost
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Portability;

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\VisualBuilder\Hooks\HooksRegistration;
use ET_Builder_Module_Settings_Migration;
use ET\Builder\Packages\Conversion\ShortcodeMigration;
use ET\Builder\Framework\Utility\Filesystem;
use ET\Builder\Packages\GlobalData\GlobalData;
use ET\Builder\Packages\GlobalData\GlobalPreset;

use WP_Error;
use WP_Filesystem_Direct;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Handles the portability of Posts.
 *
 * @since ??
 */
class PortabilityPost {

	use PortabilityPostTraits\GetGlobalColorsDataTrait;

	/**
	 * Params data holder.
	 *
	 * Holds the current request parameters.
	 *
	 * @since ??
	 *
	 * @var array $_params Defaults to `[]`.
	 */
	private $_params = [];

	/**
	 * Whether or not an import is in progress.
	 *
	 * @since 3.0.99
	 *
	 * @var bool
	 */
	protected static $_doing_import = false;

	/**
	 * Current instance.
	 *
	 * @since 2.7.0
	 *
	 * @var object $instance The current instance of the parent class (`PortabilityPost`) that uses this trait.
	 */
	public $instance;

	/**
	 * Create an instance of the `PortabilityPost` class.
	 *
	 * @since ??
	 *
	 * @param string $context Portability context previously registered.
	 *
	 * @return void
	 */
	public function __construct( string $context ) {
		$this->instance = et_core_cache_get( $context, 'et_core_portability' );
	}

	/**
	 * Injects the given Global Presets settings into the imported layout.
	 *
	 * @since 3.26
	 *
	 * @param array $shortcode_object {
	 *     The multidimensional array representing a page/module structure.
	 *     Note: Passed by reference.
	 *
	 *     @type array  attrs   Module attributes.
	 *     @type string content Module content.
	 *     @type string type    Module type.
	 * }
	 * @param array $global_presets   - The Global Presets to be applied.
	 *
	 * @return void
	 */
	public function apply_global_presets( &$shortcode_object, $global_presets ) {
		$global_presets_manager  = \ET_Builder_Global_Presets_Settings::instance();
		$module_preset_attribute = \ET_Builder_Global_Presets_Settings::MODULE_PRESET_ATTRIBUTE;

		foreach ( $shortcode_object as &$module ) {
			$module_type = $global_presets_manager->maybe_convert_module_type( $module['type'], $module['attrs'] );

			if ( isset( $global_presets[ $module_type ] ) ) {
				$default_preset_id = et_()->array_get( $global_presets, "{$module_type}.default", null );
				$module_preset_id  = et_()->array_get( $module, "attrs.{$module_preset_attribute}", $default_preset_id );

				if ( 'default' === $module_preset_id ) {
					$module_preset_id = $default_preset_id;
				}

				if ( isset( $global_presets[ $module_type ]['presets'][ $module_preset_id ] ) ) {
					$module['attrs'] = array_merge( $global_presets[ $module_type ]['presets'][ $module_preset_id ]['settings'], $module['attrs'] );
				} else {
					if ( isset( $global_presets[ $module_type ]['presets'][ $default_preset_id ]['settings'] ) ) {
						$module['attrs'] = array_merge( $global_presets[ $module_type ]['presets'][ $default_preset_id ]['settings'], $module['attrs'] );
					}
				}
			}

			if ( isset( $module['content'] ) && is_array( $module['content'] ) ) {
				$this->apply_global_presets( $module['content'], $global_presets );
			}
		}
	}

	/**
	 * Restrict data according the argument registered.
	 *
	 * @since 2.7.0
	 *
	 * @param array  $data   Array of data the query is applied on.
	 * @param string $method Whether data should be set or reset. Accepts `set` or `unset` which
	 *                       should be used when treating existing data in the db.
	 *
	 * @return array
	 */
	public function apply_query( $data, $method ) {
		$operator = ( 'set' === $method ) ? true : false;
		$ids      = array_keys( $data );

		foreach ( $ids as $id ) {
			if ( ! empty( $this->instance->exclude ) && isset( $this->instance->exclude[ $id ] ) === $operator ) {
				unset( $data[ $id ] );
			}

			if ( ! empty( $this->instance->include ) && isset( $this->instance->include[ $id ] ) === ! $operator ) {
				unset( $data[ $id ] );
			}
		}

		return $data;
	}

	/**
	 * Serialize images in chunks.
	 *
	 * @since 4.0
	 *
	 * @param array   $images A list of all the images to be processed.
	 * @param string  $method Method applied on images.
	 * @param string  $id     Unique ID to use for temporary files.
	 * @param integer $chunk  Optional. Current chunk. Defaults to 0.
	 *
	 * @return array {
	 *     @type bool  ready  Whether we have iterated over all the available chunks.
	 *     @type int   chunks The number of chunks.
	 *     @type array images The serialized images.
	 * }
	 */
	public function chunk_images( array $images, string $method, string $id, int $chunk = 0 ): array {
		$images_per_chunk = 5;
		$chunks           = 1;

		// Whether to paginate images.
		$paginate_images = true;

		/**
		 * Filters whether or not images in the file being imported should be paginated.
		 *
		 * @since 3.0.99
		 * @deprecated 5.0.0 Use `divi_framework_portability_paginate_images` hook instead.
		 *
		 * @param bool $paginate_images Whether to paginate images. Default is `true`.
		 */
		$paginate_images = apply_filters(
			'et_core_portability_paginate_images',
			$paginate_images
		);

		// Type cast for the filter hook.
		$paginate_images = (bool) $paginate_images;

		/**
		 * Filters the error message shown when `ET_Core_Portability::import()` fails.
		 *
		 * @since ??
		 *
		 * @param bool $paginate_images Whether to paginate images. Default is `true`.
		 */
		$paginate_images = apply_filters( 'divi_framework_portability_paginate_images', $paginate_images );

		if ( $paginate_images && count( $images ) > $images_per_chunk ) {
			$chunks       = ceil( count( $images ) / $images_per_chunk );
			$slice        = $images_per_chunk * $chunk;
			$images       = array_slice( $images, $slice, $images_per_chunk );
			$images       = $this->$method( $images );
			$filesystem   = $this->get_filesystem();
			$temp_file_id = sanitize_file_name( "images_{$id}" );
			$temp_file    = $this->temp_file( $temp_file_id, 'et_core_export' );
			$temp_images  = json_decode( $filesystem->get_contents( $temp_file ), true );

			if ( is_array( $temp_images ) ) {
				$images = array_merge( $temp_images, $images );
			}

			if ( $chunk + 1 < $chunks ) {
				$filesystem->put_contents( $temp_file, wp_json_encode( (array) $images ) );
			} else {
				$this->delete_temp_files( 'et_core_export', array( $temp_file_id => $temp_file ) );
			}
		} else {
			$images = $this->$method( $images );
		}

		return array(
			'ready'  => $chunk + 1 >= $chunks,
			'chunks' => $chunks,
			'images' => $images,
		);
	}

	/**
	 * Check whether or not an import is in progress.
	 *
	 * This is a getter function for the protected variable `$_doing_import`.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public static function doing_import() {
		return self::$_doing_import;
	}

	/**
	 * Encode an image attachment.
	 *
	 * If a given Post ID has a valid attached file, return that file as a Base64 encoded string.
	 *
	 * @since 3.22.3
	 *
	 * @param int $id Attachment image ID.
	 *
	 * @return string The encoded image, or empty string if attachment is not found.
	 */
	public function encode_attachment_image( $id ) {
		global $wp_filesystem;

		if ( ! current_user_can( 'read_post', $id ) ) {
			return '';
		}

		$file = get_attached_file( $id );

		if ( ! $wp_filesystem->exists( $file ) ) {
			return '';
		}

		$image = $wp_filesystem->get_contents( $file );

		if ( empty( $image ) ) {
			return '';
		}

		return base64_encode( $image ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Intentionally encoding the image during export process.
	}

	/**
	 * Encode image(s) in a base64 format.
	 *
	 * @since 2.7.0
	 *
	 * @param array $images {
	 *     Array of URLs for images to encode.
	 *
	 *     @type string|int    $key The key for the image.
	 *     @type string $value The URL for the image.
	 * }
	 *
	 * @return array {
	 *     An array of the the encoded image(s).
	 *     If an image is not found, it is not added to the result array hence the result array can be empty if none of the images were found.
	 *
	 *     @type array {
	 *         @type string|int $key The key.
	 *         @type string encoded The encoded image data.
	 *         @type string url The URL of the image.
	 *     }
	 * }
	 */
	public function encode_images( $images ) {
		$encoded = array();

		foreach ( $images as $url ) {
			$id    = 0;
			$image = '';

			if ( is_int( $url ) ) {
				$id  = $url;
				$url = wp_get_attachment_url( $id );
			} else {
				$id = $this->get_attachment_id_by_url( $url );
			}

			if ( $id > 0 ) {
				$image = $this->encode_attachment_image( $id );
			}

			if ( empty( $image ) ) {
				// Case 1: No attachment found.
				// Case 2: Attachment found, but file does not exist (may be stored on a CDN, for example).
				$image = $this->encode_remote_image( $url );
			}

			if ( empty( $image ) ) {
				// All fetching methods have failed - bail on encoding.
				continue;
			}

			$encoded[ $url ] = array(
				'encoded' => $image,
				'url'     => $url,
			);

			// Add image id for replacement purposes.
			if ( $id > 0 ) {
				$encoded[ $url ]['id'] = $id;
			}
		}

		return $encoded;
	}

	/**
	 * Base64 encode a remote image.
	 *
	 * This function uses `wp_remote_get` and associated methods to retrieve the image and process the result.
	 *
	 * @since 3.22.3
	 *
	 * @param string $url URL to be encoded.
	 *
	 * @return string The encoded image, or empty string if the remote image could not be retrieved.
	 */
	public function encode_remote_image( $url ) {
		$request = wp_remote_get(
			esc_url_raw( $url ),
			array(
				'timeout'     => 2,
				'redirection' => 2,
			)
		);

		if ( ! is_array( $request ) || is_wp_error( $request ) ) {
			return '';
		}

		if ( false === strpos( $request['headers']['content-type'], 'image' ) ) {
			return '';
		}

		$image = wp_remote_retrieve_body( $request );

		if ( ! $image ) {
			return '';
		}

		return base64_encode( $image );  // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Intentionally encoding the image during export process.
	}

	/**
	 * Get selected posts data.
	 *
	 * The post type retrieved is based on the current instance target value: `$this->instance->target`
	 * If post ID(s) are provided via `URL PARAMS -> selection` are provided, the retrieved posts will be limited to these ID(s).
	 *
	 * @since 2.7.0
	 *
	 * @return array An array of WP_Post objects.
	 */
	public function export_posts_query(): array {
		et_core_nonce_verified_previously();

		$args = array(
			'post_type'      => $this->instance->target,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		);

		// Only include selected posts if set and not empty.
		$selection = $this->get_param( 'selection' );

		if ( null !== $selection ) {
			$include = json_decode( stripslashes( $selection ), true );

			if ( ! empty( $include ) ) {
				$include          = array_map( 'intval', array_values( $include ) );
				$args['post__in'] = $include;
			}
		}

		// Context from the current instance.
		$context = $this->instance->context;

		/**
		 * Filters the posts/layout export WP_Query.
		 *
		 * @since 4.x
		 * @deprecated 5.0.0 Use `divi_framework_portability_export_wp_query_{$current_instance_context}` hook instead.
		 *
		 * @param string $context Context of the current instance.
		 * @param array  $args    WP_Query arguments.
		 */
		$context = apply_filters(
			"et_core_portability_export_wp_query_{$context}",
			$args
		);

		/**
		 * Filters the posts/layout export WP_Query.
		 *
		 * @since ??
		 *
		 * @param string $context Context of the current instance.
		 * @param array  $args    WP_Query arguments.
		 */
		$export_wp_query = apply_filters( "divi_framework_portability_export_wp_query_{$context}", $args );

		$get_posts  = get_posts( $export_wp_query );
		$taxonomies = get_object_taxonomies( $this->instance->target );
		$posts      = array();

		foreach ( $get_posts as $post ) {
			unset(
				$post->post_author,
				$post->guid
			);

			$posts[ $post->ID ] = $post;

			// Include post meta.
			$post_meta = (array) get_post_meta( $post->ID );

			if ( isset( $post_meta['_edit_lock'] ) ) {
				unset(
					$post_meta['_edit_lock'],
					$post_meta['_edit_last']
				);
			}

			$posts[ $post->ID ]->post_meta = $post_meta;

			// Include terms.
			$get_terms = (array) wp_get_object_terms( $post->ID, $taxonomies );
			$terms     = array();

			// Order terms to make sure children are after the parents.
			while ( true ) {
				$term = array_shift( $get_terms );

				if ( ! $term ) {
					break;
				}

				if ( 0 === $term->parent || isset( $terms[ $term->parent ] ) ) {
					$terms[ $term->term_id ] = $term;
				} else {
					// if parent category is also exporting then add the term to the end of the list and process it later
					// otherwise add a term as usual.
					if ( $this->is_parent_term_included( $get_terms, $term->parent ) ) {
						$get_terms[] = $term;
					} else {
						$terms[ $term->term_id ] = $term;
					}
				}
			}

			$posts[ $post->ID ]->terms = array();

			foreach ( $terms as $term ) {
				$parents_data = array();

				if ( $term->parent ) {
					$parent_slug  = isset( $terms[ $term->parent ] ) ? $terms[ $term->parent ]->slug : $this->get_parent_slug( $term->parent, $term->taxonomy );
					$parents_data = $this->get_all_parents( $term->parent, $term->taxonomy );
				} else {
					$parent_slug = 0;
				}

				$posts[ $post->ID ]->terms[ $term->term_id ] = array(
					'name'        => $term->name,
					'slug'        => $term->slug,
					'taxonomy'    => $term->taxonomy,
					'parent'      => $parent_slug,
					'all_parents' => $parents_data,
					'description' => $term->description,
				);
			}
		}

		return $posts;
	}

	/**
	 * Proxy method for set_filesystem() to avoid calling it multiple times.
	 *
	 * @since ??
	 *
	 * @return WP_Filesystem_Direct
	 */
	public function get_filesystem(): WP_Filesystem_Direct {
		return $this->set_filesystem();
	}

	/**
	 * Set WP filesystem to direct. This should only be use to create a temporary file.
	 *
	 * @since ??
	 *
	 * @return WP_Filesystem_Direct
	 */
	public function set_filesystem(): WP_Filesystem_Direct {
		return Filesystem::set();
	}

	/**
	 * Check if a temporary file is registered. Returns temporary file if it exists.
	 *
	 * @since ??
	 *
	 * @param string $id    Unique id used when the temporary file was created.
	 * @param string $group Group name in which one or more files are grouped.
	 *
	 * @return bool|string Returns false if the temporary file does not exist, otherwise returns the file.
	 */
	public function has_temp_file( string $id, string $group ) {
		$temp_files = get_option( '_et_core_portability_temp_files', array() );

		if ( isset( $temp_files[ $group ][ $id ] ) && file_exists( $temp_files[ $group ][ $id ] ) ) {
			return $temp_files[ $group ][ $id ];
		}

		return false;
	}

	/**
	 * Create a temp file and register it.
	 *
	 * @since 2.7.0
	 * @since 4.0 Made method public. Added $content parameter.
	 *
	 * @param string      $id        Unique id reference for the temporary file.
	 * @param string      $group     Group name in which files are grouped.
	 * @param string|bool $temp_file Optional. Path to the temporary file.
	 *                               Passing `false` will create a new temporary file.
	 *                               Defaults to `false`.
	 * @param string      $content   Optional. The temporary file content. Defaults to empty string.
	 *
	 * @return bool|string
	 */
	public function temp_file( string $id, string $group, $temp_file = false, string $content = '' ) {
		$temp_files = get_option( '_et_core_portability_temp_files', array() );

		if ( ! isset( $temp_files[ $group ] ) ) {
			$temp_files[ $group ] = array();
		}

		if ( isset( $temp_files[ $group ][ $id ] ) && file_exists( $temp_files[ $group ][ $id ] ) ) {
			return $temp_files[ $group ][ $id ];
		}

		$temp_file                   = $temp_file ? $temp_file : wp_tempnam();
		$temp_files[ $group ][ $id ] = $temp_file;

		update_option( '_et_core_portability_temp_files', $temp_files, false );

		if ( ! empty( $content ) ) {
			$this->get_filesystem()->put_contents( $temp_file, $content );
		}

		return $temp_file;
	}

	/**
	 * Import a previously exported layout.
	 *
	 * @since 2.7.0
	 * @since 3.10 Return the result of the import instead of dying.
	 * @since ?? Removed `$file_context` because 'upload' is the only file context used.
	 *
	 * @param array                $files        Array of file objects.
	 * @param WP_Filesystem_Direct $filesystem   The filesystem object.
	 * @param string               $temp_file_id The ID of the temp file for upload.
	 *
	 * @return array {
	 *   Array of import result.
	 *
	 *   @type string $message The import result message.
	 * }
	 */
	public function upload_file( array $files, WP_Filesystem_Direct $filesystem, string $temp_file_id ): array {
		if ( ! isset( $files['file']['name'] ) || ! et_()->ends_with( sanitize_file_name( $files['file']['name'] ), '.json' ) ) {
			return array( 'message' => 'invalideFile' );
		}

		// phpcs:ignore ET.Sniffs.DangerousFunctions.ET_handle_upload -- test_type is enabled and proper type and extension checking are implemented.
		$upload = wp_handle_upload(
			$files['file'],
			array(
				'test_size' => false,
				'test_type' => true,
				'test_form' => false,
			)
		);

		// The absolute path to the uploaded JSON file's temporary location.
		$file = $upload['file'];

		/**
		 * Fires before an uploaded Portability JSON file is processed.
		 *
		 * This is for backward compatibility with hooks written for Divi version <5.0.0.
		 *
		 * @since 3.0.99
		 * @deprecated 5.0.0 Use `divi_framework_portability_import_file` hook instead.
		 *
		 * @param string $file The absolute path to the uploaded JSON file's temporary location.
		 */
		do_action(
			'et_core_portability_import_file',
			$file
		);

		/**
		 * Fires before an uploaded Portability JSON file is processed.
		 *
		 * @param string $file The absolute path to the uploaded JSON file's temporary location.
		 *
		 * @since ??
		 */
		do_action( 'divi_framework_portability_import_file', $file );

		$temp_file = $this->temp_file( $temp_file_id, 'et_core_import', $upload['file'] );
		$import    = json_decode( $filesystem->get_contents( $temp_file ), true );
		$import    = $this->validate( $import );

		$import['data'] = $this->apply_query( $import['data'], 'set' );

		$filesystem->put_contents( $upload['file'], wp_json_encode( (array) $import ) );

		return array( 'message' => 'success' );
	}

	/**
	 * Delete all the temp files.
	 *
	 * @since 2.7.0
	 *
	 * @param bool|string $group         Optional. Group name in which files are grouped.
	 *                                   Set to `true` to remove all groups and files. Defaults to `false`.
	 * @param array|bool  $defined_files Optional. Array or temporary files to delete.
	 *                                   Passing `false`/no argument deletes all temp files. Defaults to `false`.
	 *
	 * @return void
	 */
	public function delete_temp_files( $group = false, $defined_files = false ) {
		$filesystem = $this->get_filesystem();
		$temp_files = get_option( '_et_core_portability_temp_files', array() );

		// Remove all temp files accross all groups if group is true.
		if ( true === $group ) {
			foreach ( $temp_files as $group_id => $_group ) {
				$this->delete_temp_files( $group_id );
			}
		}

		if ( ! isset( $temp_files[ $group ] ) ) {
			return;
		}

		$delete_files = ( is_array( $defined_files ) && ! empty( $defined_files ) ) ? $defined_files : $temp_files[ $group ];

		foreach ( $delete_files as $id => $temp_file ) {
			if ( isset( $temp_files[ $group ][ $id ] ) && $filesystem->delete( $temp_files[ $group ][ $id ] ) ) {
				unset( $temp_files[ $group ][ $id ] );
			}
		}

		if ( empty( $temp_files[ $group ] ) ) {
			unset( $temp_files[ $group ] );
		}

		if ( empty( $temp_files ) ) {
			delete_option( '_et_core_portability_temp_files' );
		} else {
			update_option( '_et_core_portability_temp_files', $temp_files, false );
		}
	}

	/**
	 * Decode base64 formatted image and upload it to WP media.
	 *
	 * @since 2.7.0
	 *
	 * @param array $images {
	 *     Array of encoded images which needs to be uploaded.
	 *
	 *     @type array $image {
	 *         Array of  image attributes.
	 *
	 *         @type string|int $id      The image ID.
	 *         @type string     $url     The image URL.
	 *         @type string     $encoded The encoded value of the image.
	 *     }
	 * }
	 *
	 * @return array  {
	 *     Array of encoded images which needs to be uploaded.
	 *
	 *     @type array $image {
	 *         Array of  image attributes.
	 *
	 *         @type string|int $id              The image ID.
	 *         @type string     $url             The image URL.
	 *         @type string     $encoded         The encoded value of the image.
	 *         @type string     $replacement_url Optional. The replacement URL.
	 *     }
	 * }
	 */
	public function upload_images( array $images ): array {
		$filesystem = $this->get_filesystem();

		// Whether to allow duplicates. Default `false`.
		$allow_duplicates = false;

		/**
		 * Filters whether or not to allow duplicate images to be uploaded during Portability import.
		 *
		 * This is for backward compatibility with hooks written for Divi version <5.0.0.
		 *
		 * @since 4.14.8
		 * @deprecated 5.0.0 Use `divi_framework_portability_import_attachment_allow_duplicates` hook instead.
		 *
		 * @param bool $allow_duplicates Whether to allow duplicates. Default `false`.
		 */
		$allow_duplicates = apply_filters(
			'et_core_portability_import_attachment_allow_duplicates',
			$allow_duplicates
		);

		// Type cast for the filter hook.
		$allow_duplicates = (bool) $allow_duplicates;

		/**
		 * Filters whether or not to allow duplicate images to be uploaded during Portability import.
		 *
		 * @since ??
		 *
		 * @param bool $allow_duplicates Whether or not to allow duplicates. Default `false`.
		 */
		$allow_duplicates = apply_filters( 'divi_framework_portability_import_attachment_allow_duplicates', $allow_duplicates );

		foreach ( $images as $key => $image ) {
			$basename = sanitize_file_name( wp_basename( $image['url'] ) );
			$id       = 0;
			$url      = '';

			if ( ! $allow_duplicates ) {
				$attachments = get_posts(
					array(
						'posts_per_page' => -1,
						'post_type'      => 'attachment',
						'meta_key'       => '_wp_attached_file',
						'meta_value'     => pathinfo( $basename, PATHINFO_FILENAME ),
						'meta_compare'   => 'LIKE',
					)
				);

				// Avoid duplicates.
				if ( ! is_wp_error( $attachments ) && ! empty( $attachments ) ) {
					foreach ( $attachments as $attachment ) {
						$attachment_url = wp_get_attachment_url( $attachment->ID );
						$file           = get_attached_file( $attachment->ID );
						$filename       = sanitize_file_name( wp_basename( $file ) );

						// Use existing image only if the content matches.
						if ( $filesystem->get_contents( $file ) === base64_decode( $image['encoded'] ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Intentionally decoding the image during import process.
							$id  = isset( $image['id'] ) ? $attachment->ID : 0;
							$url = $attachment_url;

							break;
						}
					}
				}
			}

			// Create new image.
			if ( empty( $url ) ) {
				$temp_file = wp_tempnam();
				$filesystem->put_contents( $temp_file, base64_decode( $image['encoded'] ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Intentionally decoding the image during import process.
				$filetype = wp_check_filetype_and_ext( $temp_file, $basename );

				if ( ! $allow_duplicates && ! empty( $attachments ) && ! is_wp_error( $attachments ) ) {
					// Avoid further duplicates if the proper_filename matches an existing image.
					if ( isset( $filetype['proper_filename'] ) && $filetype['proper_filename'] !== $basename ) {
						foreach ( $attachments as $attachment ) {
							$attachment_url = wp_get_attachment_url( $attachment->ID );
							$file           = get_attached_file( $attachment->ID );
							$filename       = sanitize_file_name( wp_basename( $file ) );

							if ( isset( $filename ) && $filename === $filetype['proper_filename'] ) {
								// Use existing image only if the basenames and content match.
								if ( $filesystem->get_contents( $file ) === $filesystem->get_contents( $temp_file ) ) {
									$id  = isset( $image['id'] ) ? $attachment->ID : 0;
									$url = $attachment_url;

									$filesystem->delete( $temp_file );

									break;
								}
							}
						}
					}
				}

				$file = array(
					'name'     => $basename,
					'tmp_name' => $temp_file,
				);

				// Require necessary files for media_handle_sideload to work.
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';

				$upload        = media_handle_sideload( $file );
				$attachment_id = is_wp_error( $upload ) ? 0 : $upload;

				/**
				 * Fires when image attachments are created during portability import.
				 *
				 * @since 4.14.6
				 * @deprecated 5.0.0 Use `divi_framework_portability_import_attachment_created` hook instead.
				 *
				 * @param int $attachment_id The attachment id or 0 if attachment upload failed.
				 */
				do_action(
					'et_core_portability_import_attachment_created',
					$attachment_id
				);

				/**
				 * Fires when image attachments are created during portability import.
				 *
				 * @param int $attachment_id The attachment id or 0 if attachment upload failed.
				 *
				 * @since ??
				 */
				do_action( 'divi_framework_portability_import_attachment_created', $attachment_id );

				if ( ! is_wp_error( $upload ) ) {
					// Set the replacement as an id if the original image was set as an id (for gallery).
					$id  = isset( $image['id'] ) ? $upload : 0;
					$url = wp_get_attachment_url( $upload );
				} else {
					// Make sure the temporary file is removed if media_handle_sideload didn't take care of it.
					$filesystem->delete( $temp_file );
				}
			}

			// Only declare the replace if a url is set.
			if ( $id > 0 ) {
				$images[ $key ]['replacement_id'] = $id;
			}

			if ( ! empty( $url ) ) {
				$images[ $key ]['replacement_url'] = $url;
			}

			unset( $url );
		}

		return $images;
	}

	/**
	 * Filters a variable with string filter
	 *
	 * @since ??
	 *
	 * @param mixed $data - Value to filter.
	 *
	 * @return mixed
	 */
	public function filter_post_data( $data ) {
		return filter_var( $data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
	}

	/**
	 * Prepare array of all parents so the correct hierarchy can be restored during the import.
	 *
	 * @since 2.7.0
	 *
	 * @param int    $parent_id .
	 * @param string $taxonomy  .
	 *
	 * @return array
	 */
	public function get_all_parents( $parent_id, $taxonomy ) {
		$parents_data_array = array();
		$parent             = $parent_id;

		// retrieve data for all parent categories.
		if ( 0 !== $parent ) {
			while ( 0 !== $parent ) {
				$parent_term_data                              = get_term( $parent, $taxonomy );
				$parents_data_array[ $parent_term_data->slug ] = array(
					'name'        => $parent_term_data->name,
					'description' => $parent_term_data->description,
					'parent'      => 0 !== $parent_term_data->parent ? $this->get_parent_slug( $parent_term_data->parent, $taxonomy ) : 0,
				);

				$parent = $parent_term_data->parent;
			}
		}

		// Reverse order of items, to simplify the restoring process.
		return array_reverse( $parents_data_array );
	}

	/**
	 * Get the attachment post id for the given url.
	 *
	 * @since 3.22.3
	 *
	 * @param string $url The url of an attachment file.
	 *
	 * @return int
	 */
	public function get_attachment_id_by_url( $url ) {
		global $wpdb;

		// Remove any thumbnail size suffix from the filename and use that as a fallback.
		$fallback_url = preg_replace( '/-\d+x\d+(\.[^.]+)$/i', '$1', $url );

		// Scenario: Trying to find the attachment for a file called x-150x150.jpg.
		// 1. Since WordPress adds the -150x150 suffix for thumbnail sizes we cannot be
		// sure if this is an attachment or an attachment's generated thumbnail.
		// 2. Since both x.jpg and x-150x150.jpg can be uploaded as separate attachments
		// we must decide which is a better match.
		// 3. The above is why we order by guid length and use the first result.
		$attachment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT id
				FROM $wpdb->posts
				WHERE `post_type` = %s
				AND `guid` IN ( %s, %s )
				ORDER BY CHAR_LENGTH( `guid` ) DESC
			",
				'attachment',
				esc_url_raw( $url ),
				esc_url_raw( $fallback_url )
			)
		);

		return $attachment_id;
	}

	/**
	 * Get all images in the data given.
	 *
	 * @since 2.7.0
	 *
	 * @param array $data  Array of data.
	 * @param bool  $force Set whether the value should be added by force. Usually used for image ids.
	 *
	 * @return array
	 */
	public function get_data_images( $data, $force = false ) {
		if ( empty( $data ) ) {
			return array();
		}

		$images     = array();
		$images_src = array();
		$basenames  = array(
			'src',
			'image_url',
			'background_image',
			'image',
			'url',
			'bg_img_?\d?',
		);
		$suffixes   = array(
			'__hover',
			'_tablet',
			'_phone',
		);

		foreach ( $basenames as $basename ) {
			$images_src[] = $basename;
			foreach ( $suffixes as $suffix ) {
				$images_src[] = $basename . $suffix;
			}
		}

		foreach ( $data as $value ) {
			// If the $value is an object and there is no post_content property,
			// it's unlikely to contain any image data so we can continue with the next iteration.
			if ( is_object( $value ) && ! property_exists( $value, 'post_content' ) ) {
				continue;
			}

			if ( is_array( $value ) || is_object( $value ) ) {
				// If the $value contains the post_content property, set $value to use
				// this object's property value instead of the entire object.
				if ( is_object( $value ) && property_exists( $value, 'post_content' ) ) {
					$value = $value->post_content;
				}

				$images = array_merge( $images, $this->get_data_images( (array) $value ) );
				continue;
			}

			// Extract images from Gutenberg formatted content.
			// Gutenberg format uses HTML comments similar to this one to store the blocks:
			// <!-- wp:divi/section [module JSON goes here] -->
			// We test the passed value against the "<!-- wp:" string.
			$maybe_gutenberg_format = preg_match( '/<!-- wp:/', $value );

			// If there is a match the content is tested for all the image attributes in JSON format.
			// The regex tests for all the attributes in the $images_src array.
			// For example: "src":"https://url-goes-here/image.png"
			// $matches[2] holds an array of all the image URLS matches this way.
			if ( $maybe_gutenberg_format && preg_match_all( '/"(' . implode( '|', $images_src ) . ')":"(.*?)"/i', $value, $matches ) && $matches[2] ) {
				$images = array_merge( array_unique( $matches[2] ), $images );
			}

			// Extract images from HTML or shortcodes.
			if ( preg_match_all( '/(' . implode( '|', $images_src ) . ')="(?P<src>\w+[^"]*)"/i', $value, $matches ) ) {
				foreach ( array_unique( $matches['src'] ) as $key => $src ) {
					$images = array_merge( $images, $this->get_data_images( array( $key => $src ) ) );
				}
			}

			// Extract images from gutenberg/shortcodes gallery.
			if ( $maybe_gutenberg_format ) {
				preg_match_all( '/galleryIds":\{"(?P<type>[^"]+)":\{"value":"(?P<ids>[^"]+)"\}\}/i', $value, $matches );
			} else {
				preg_match_all( '/gallery_ids="(?P<ids>\w+[^"]*)"/i', $value, $matches );
			}

			if ( ! empty( $matches['ids'] ) ) {
				foreach ( array_unique( $matches['ids'] ) as $galleries ) {
					$explode = explode( ',', str_replace( ' ', '', $galleries ) );

					foreach ( $explode as $image_id ) {
						$images = array_merge( $images, $this->get_data_images( array( (int) $image_id ), true ) );
					}
				}
			}

			if ( preg_match( '/^.+?\.(jpg|jpeg|jpe|png|gif|svg|webp)/', $value, $match ) || $force ) {
				$basename = basename( $value );

				// Skip if the value is not a valid URL or an image ID (integer).
				if ( ! ( wp_http_validate_url( $value ) || is_int( $value ) ) ) {
					continue;
				}

				// Skip if the images array already contains the value to avoid duplicates.
				if ( isset( $images[ $value ] ) ) {
					continue;
				}

				$images[ $value ] = $value;
			}
		}

		return $images;
	}

	/**
	 * Retrieve the layout content.
	 *
	 * @since ??
	 *
	 * @param array $data Layout content data.
	 *
	 * @return string The layout content.
	 */
	public function get_layout_content( $data ) {
		$first_data     = reset( $data );
		$layout_content = '';

		if ( is_string( $first_data ) || ! array_key_exists( 'post_content', $first_data ) ) {
			// D4 cloud item has no post_content.
			$layout_content = $first_data;
		} else {
			$layout_content = $first_data['post_content'];
		}

		return $layout_content;
	}

	/**
	 * Retrieve the term slug.
	 *
	 * @since 2.7.0
	 *
	 * @param int    $parent_id The ID of the parent term.
	 * @param string $taxonomy  The taxonomy name that the term is part of.
	 *
	 * @return int|string
	 */
	public function get_parent_slug( $parent_id, $taxonomy ) {
		$term_data = get_term( $parent_id, $taxonomy );
		$slug      = '' === $term_data->slug ? 0 : $term_data->slug;

		return $slug;
	}

	/**
	 * Get all thumbnail images in the data given.
	 *
	 * @since 4.7.4
	 *
	 * @param array $data Array of WP_Post objects.
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_post/ Definition of the core class used to implement the WP_Post object.
	 *
	 * @return array
	 */
	public function get_thumbnail_images( $data ) {
		$thumbnails = array();

		foreach ( $data as $post_data ) {
			// If post has thumbnail.
			if ( ! empty( $post_data->post_meta ) && ! empty( $post_data->post_meta->_thumbnail_id ) ) {
				$post_thumbnail = get_the_post_thumbnail_url( $post_data->ID );

				// If thumbnail image found in the WP Media library.
				if ( $post_thumbnail ) {
					$thumbnail_id    = (int) $post_data->post_meta->_thumbnail_id[0];
					$thumbnail_image = $this->encode_images( array( $thumbnail_id ) );

					$thumbnails[ $thumbnail_id ] = $thumbnail_image;
				}
			}
		}

		return $thumbnails;
	}

	/**
	 * Get timestamp or create one if it isn't set.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_timestamp() {
		$timestamp = $this->get_param( 'timestamp' );

		if ( $timestamp ) {
			return sanitize_text_field( $timestamp );
		}

		return (string) microtime( true );
	}

	/**
	 * Get List of global colors used in shortcode.
	 *
	 * @since 4.10.8
	 *
	 * @param array $shortcode_object {
	 *     The multidimensional array representing a page structure.
	 *     Note: Passed by reference.
	 *
	 *     @type array  attrs    Module attributes.
	 *     @type string content Module content.
	 * }
	 * @param array $used_global_colors List of global colors to merge with.
	 *
	 * @return array - The list of the Global Colors.
	 */
	public function get_used_global_colors( $shortcode_object, $used_global_colors = array() ) {
		foreach ( $shortcode_object as $module ) {
			if ( isset( $module['attrs']['global_colors_info'] ) ) {
				// Retrieve global_colors_info from post meta, which saved as string[][].
				$gc_info_prepared   = str_replace(
					array( '&#91;', '&#93;' ),
					array( '[', ']' ),
					$module['attrs']['global_colors_info']
				);
				$used_global_colors = array_merge( $used_global_colors, json_decode( $gc_info_prepared, true ) );
			}

			if ( isset( $module['content'] ) && is_array( $module['content'] ) ) {
				$used_global_colors = array_merge( $used_global_colors, $this->get_used_global_colors( $module['content'], $used_global_colors ) );
			}
		}

		return $used_global_colors;
	}

	/**
	 * Returns Global Presets used for a given shortcode only
	 *
	 * @since 3.26
	 *
	 * @param array $shortcode_object {
	 *     The multidimensional array representing a page structure.
	 *     Note: Passed by reference.
	 *
	 *     @type array  attrs    Module attributes.
	 *     @type string content Module content.
	 *     @type string type    Module type.
	 * }
	 * @param array $used_global_presets The multidimensional array representing used global presets.
	 *
	 * @return array - The list of the Global Presets
	 */
	public function get_used_global_presets( $shortcode_object, $used_global_presets = array() ) {
		$global_presets_manager = \ET_Builder_Global_Presets_Settings::instance();

		foreach ( $shortcode_object as $module ) {
			$module_type = $global_presets_manager->maybe_convert_module_type( $module['type'], $module['attrs'] );
			$preset_id   = $global_presets_manager->get_module_preset_id( $module_type, $module['attrs'] );
			$preset      = $global_presets_manager->get_module_preset( $module_type, $preset_id );

			if ( 'default' !== $preset_id && count( (array) $preset ) !== 0 && count( (array) $preset->settings ) !== 0 ) {
				if ( ! isset( $used_global_presets[ $module_type ] ) ) {
					$used_global_presets[ $module_type ] = (object) array(
						'presets' => (object) array(),
					);
				}

				if ( ! isset( $used_global_presets[ $module_type ]->presets->$preset_id ) ) {
					$used_global_presets[ $module_type ]->presets->{$preset_id} = (object) array(
						'name'     => $preset->name,
						'version'  => $preset->version,
						'settings' => $preset->settings,
					);
				}

				if ( ! isset( $used_global_presets[ $module_type ]->default ) ) {
					$used_global_presets[ $module_type ]->default = $global_presets_manager->get_module_default_preset_id( $module_type );
				}
			}

			if ( isset( $module['content'] ) && is_array( $module['content'] ) ) {
				$used_global_presets = array_merge( $used_global_presets, $this->get_used_global_presets( $module['content'], $used_global_presets ) );
			}
		}

		return $used_global_presets;
	}

	/**
	 * Convert global colors data from Import file.
	 *
	 * @since ??
	 *
	 * @param array $incoming_global_colors Global Colors Array.
	 *
	 * @return array
	 */
	public function import_global_colors( array $incoming_global_colors ): array {
		// Sanity check.
		if ( empty( $incoming_global_colors ) ) {
			$incoming_global_colors = [];
		}

		// Convert global colors data format from the $incoming_global_colors.
		return GlobalData::get_imported_global_colors( $incoming_global_colors );
	}

	/**
	 * Import Global Variables from Import file.
	 *
	 * @since ??
	 *
	 * @param array $incoming_global_variables Global Variables Array.
	 *
	 * @return array
	 */
	public function import_global_variables( array $incoming_global_variables ): array {
		// Convert global variables data format from the $incoming_global_variables.
		return GlobalData::import_global_variables( $incoming_global_variables );
	}

	/**
	 * Imports Global Presets
	 *
	 * @since 4.0.10 Made public.
	 *
	 * @param array $presets - The Global Presets to be imported.
	 *
	 * @return boolean
	 */
	public function import_global_presets( $presets ) {
		if ( ! is_array( $presets ) ) {
			return false;
		}

		$all_modules            = \ET_Builder_Element::get_modules();
		$module_presets_manager = \ET_Builder_Global_Presets_Settings::instance();
		$global_presets         = $module_presets_manager->get_global_presets();
		$presets_to_import      = array();

		foreach ( $presets as $module_type => $module_presets ) {
			$presets_to_import[ $module_type ] = array(
				'presets' => array(),
			);

			if ( ! isset( $global_presets->$module_type->presets ) ) {
				$initial_preset_structure = \ET_Builder_Global_Presets_Settings::generate_module_initial_presets_structure( $module_type, $all_modules );

				$global_presets->$module_type = $initial_preset_structure;
			}

			$local_presets      = $global_presets->$module_type->presets;
			$local_preset_names = array();

			foreach ( $local_presets as $preset ) {
				array_push( $local_preset_names, $preset->name );
			}

			foreach ( $module_presets['presets'] as $preset_id => $preset ) {
				$imported_name = sanitize_text_field( $preset['name'] );
				$name          = in_array( $imported_name, $local_preset_names, true )
					? $imported_name . ' ' . esc_html__( 'imported', 'et-core' )
					: $imported_name;

				$presets_to_import[ $module_type ]['presets'][ $preset_id ] = array(
					'name'     => $name,
					'created'  => time() * 1000,
					'updated'  => time() * 1000,
					'version'  => $preset['version'],
					'settings' => $preset['settings'],
				);
			}
		}

		// Merge existing Global Presets with imported ones.
		foreach ( $presets_to_import as $module_type => $module_presets ) {
			foreach ( $module_presets['presets'] as $preset_id => $preset ) {
				$global_presets->$module_type->presets->$preset_id           = (object) array();
				$global_presets->$module_type->presets->$preset_id->name     = sanitize_text_field( $preset['name'] );
				$global_presets->$module_type->presets->$preset_id->created  = $preset['created'];
				$global_presets->$module_type->presets->$preset_id->updated  = $preset['updated'];
				$global_presets->$module_type->presets->$preset_id->version  = $preset['version'];
				$global_presets->$module_type->presets->$preset_id->settings = (object) array();

				foreach ( $preset['settings'] as $setting_name => $value ) {
					$setting_name_sanitized = sanitize_text_field( $setting_name );
					$value_sanitized        = sanitize_text_field( $value );

					$global_presets->$module_type->presets->$preset_id->settings->$setting_name_sanitized = $value_sanitized;
				}

				// Inject Global colors into imported presets.
				$preset_settings = (array) $global_presets->$module_type->presets->$preset_id->settings;
				$global_presets->$module_type->presets->$preset_id->settings = \ET_Builder_Global_Presets_Settings::maybe_set_global_colors( $preset_settings );
			}
		}

		et_update_option( \ET_Builder_Global_Presets_Settings::GLOBAL_PRESETS_OPTION, $global_presets );

		$global_presets_history = \ET_Builder_Global_Presets_History::instance();
		$global_presets_history->add_global_history_record( $global_presets );

		return true;
	}

	/**
	 * Import post(s).
	 *
	 * Applies `et_core_portability_import_posts` and `divi_framework_portability_import_posts` filters before processing the posts.
	 *
	 * @since 2.7.0
	 *
	 * @param array $posts          {
	 *     Array of data formatted by the portability exporter.
	 *
	 *     @type string $post_status The status of the post e.g `auto-draft`, `published`.
	 *                              Posts with `auto-draft` status will not be imported.
	 *     @type string $post_name   The slug of the post.
	 *     @type string $post_title  The title of the post.
	 *     @type string $post_type   The post type e.g `post`, `page`.
	 *     @type int    $ID          The post ID.
	 *     @type int    $post_author The ID of the author.
	 *     @type int    $import_id   The post import ID.
	 *     @type array  $terms       The post taxonomy terms.
	 *     @type array  $post_meta   The post meta data.
	 *     @type string $thumbnail   The post thumbnail.
	 * }
	 *
	 * @return bool Returns `false` if the posts array is empty,
	 */
	public function import_posts( array $posts ): bool {
		// Type cast for the filter hook.
		$posts = (array) $posts;

		/**
		 * Filters the array of builder layouts to import.
		 *
		 * Returning an empty value will short-circuit the import process.
		 *
		 * This is for backward compatibility with hooks written for Divi version <5.0.0.
		 *
		 * @since 3.0.99
		 * @deprecated 5.0.0 Use `divi_framework_portability_import_error_message` hook instead.
		 *
		 * @param array $posts The posts to be imported.
		 */
		$posts = apply_filters(
			'et_core_portability_import_posts',
			$posts
		);

		/**
		 * Filters the array of builder layouts to import.
		 *
		 * Returning an empty value will short-circuit the import process.
		 *
		 * @since ??
		 *
		 * @param array $posts The posts to be imported.
		 */
		$posts = apply_filters( 'divi_framework_portability_import_posts', $posts );

		if ( empty( $posts ) ) {
			return false;
		}

		foreach ( $posts as $post ) {
			if ( isset( $post['post_status'] ) && 'auto-draft' === $post['post_status'] ) {
				continue;
			}

			$fields_validation = array(
				'ID'         => 'intval',
				'post_title' => 'sanitize_text_field',
				'post_type'  => 'sanitize_text_field',
			);

			$post = $this->validate( $post, $fields_validation );

			if ( ! $post ) {
				continue;
			}

			$layout_exists = self::layout_exists( $post['post_title'], $post['post_name'] );

			if ( $layout_exists && get_post_type( $layout_exists ) === $post['post_type'] ) {
				// Make sure the post is published.
				if ( 'publish' !== get_post_status( $layout_exists ) ) {
					wp_update_post(
						array(
							'ID'          => intval( $layout_exists ),
							'post_status' => 'publish',
						)
					);
				}

				continue;
			}

			$post['import_id'] = $post['ID'];
			unset( $post['ID'] );

			$post['post_author'] = (int) get_current_user_id();

			// Insert or update post.
			$post_id = wp_insert_post( $post, true );

			if ( ! $post_id || is_wp_error( $post_id ) ) {
				continue;
			}

			// Insert and set terms.
			if ( isset( $post['terms'] ) && is_array( $post['terms'] ) ) {
				$processed_terms = array();

				foreach ( $post['terms'] as $term ) {
					$fields_validation = array(
						'name'        => 'sanitize_text_field',
						'slug'        => 'sanitize_title',
						'taxonomy'    => 'sanitize_title',
						'parent'      => 'sanitize_title',
						'description' => 'wp_kses_post',
					);

					$term = $this->validate( $term, $fields_validation );

					if ( ! $term ) {
						continue;
					}

					if ( empty( $term['parent'] ) ) {
						$parent = 0;
					} else {
						if ( isset( $term['all_parents'] ) && ! empty( $term['all_parents'] ) ) {
							$this->restore_parent_categories( $term['all_parents'], $term['taxonomy'] );
						}

						$parent = term_exists( $term['parent'], $term['taxonomy'] );

						if ( is_array( $parent ) ) {
							$parent = $parent['term_id'];
						}
					}

					$insert = term_exists( $term['slug'], $term['taxonomy'] );

					if ( ! $insert ) {
						$insert = wp_insert_term(
							$term['name'],
							$term['taxonomy'],
							array(
								'slug'        => $term['slug'],
								'description' => $term['description'],
								'parent'      => intval( $parent ),
							)
						);
					}

					if ( is_array( $insert ) && ! is_wp_error( $insert ) ) {
						$processed_terms[ $term['taxonomy'] ][] = $term['slug'];
					}
				}

				// Set post terms.
				foreach ( $processed_terms as $taxonomy => $ids ) {
					wp_set_object_terms( $post_id, $ids, $taxonomy );
				}
			}

			// Insert or update post meta.
			if ( isset( $post['post_meta'] ) && is_array( $post['post_meta'] ) ) {
				foreach ( $post['post_meta'] as $meta_key => $meta ) {

					$meta_key = sanitize_text_field( $meta_key );

					if ( count( $meta ) < 2 ) {
						$meta = wp_kses_post( $meta[0] );
					} else {
						$meta = array_map( 'wp_kses_post', $meta );
					}

					update_post_meta( $post_id, $meta_key, $meta );
				}
			}

			// Assign new thumbnail if provided.
			if ( isset( $post['thumbnail'] ) ) {
				set_post_thumbnail( $post_id, $post['thumbnail'] );
			}
		}

		return true;
	}

	/**
	 * Check whether the provided `$parent_id` is included in the $terms_list.
	 *
	 * @since 2.7.0
	 *
	 * @param array $terms_list Array of term objects.
	 * @param int   $parent_id  The ID of the parent term.
	 *
	 * @return bool
	 */
	public function is_parent_term_included( $terms_list, $parent_id ) {
		$is_parent_found = false;

		foreach ( $terms_list as $term_details ) {
			if ( $parent_id === $term_details->term_id ) {
				$is_parent_found = true;
				break;
			}
		}

		return $is_parent_found;
	}

	/**
	 * Check if a layout exists in the database already based on both its title and its slug.
	 *
	 * @param string $title The title of the layout.
	 * @param string $slug  The slug of the layout.
	 *
	 * @return int $post_id The post id if it exists, zero otherwise.
	 */
	public static function layout_exists( $title, $slug ) {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_name = %s",
				array(
					wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) ),
					wp_unslash( sanitize_post_field( 'post_name', $slug, 0, 'db' ) ),
				)
			)
		);
	}

	/**
	 * Paginate images processing.
	 *
	 * @since    1.0.0
	 *
	 * @param array  $images    A list of all the images to be processed.
	 * @param string $method    Method applied on images.
	 * @param int    $timestamp Timestamp used to store data upon pagination.
	 *
	 * @return array
	 * @internal param array $data Array of images.
	 */
	public function maybe_paginate_images( $images, $method, $timestamp ) {
		et_core_nonce_verified_previously();

		$page   = $this->get_param( 'page' );
		$page   = isset( $page ) ? (int) $page : 1;
		$result = $this->chunk_images( $images, $method, $timestamp, max( $page - 1, 0 ) );

		if ( ! $result['ready'] ) {
			wp_send_json(
				array(
					'page'       => strval( $page ),
					'totalPages' => strval( $result['chunks'] ),
					'timestamp'  => $timestamp,
				)
			);
		}

		return $result['images'];
	}

	/**
	 * Prevent import and export timeout or memory failure.
	 *
	 * Sets request time limit to `infinity` and increases memory limit to `256M`
	 * It doesn't need to be reset as in both cases the request will exit.
	 *
	 * @since 2.7.0
	 *
	 * @return void
	 */
	public static function prevent_failure() {
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Custom memory limit needed.

		$memory_limit = et_core_get_memory_limit();

		// Increase memory which is safe at this stage of the request.
		if ( $memory_limit > -1 && $memory_limit < 256 ) {
			@ini_set( 'memory_limit', '256M' ); // phpcs:ignore -- Custom memory limit needed. Cannot use wp_set_memory_limit.
		}
	}

	/**
	 * Set param data.
	 *
	 * Save a request parameter to the `$_params` member variable.
	 *
	 * @since ??
	 *
	 * @param string $key       Param key.
	 * @param mixed  $value     Param value.
	 */
	public function set_param( $key, $value ) {
		$this->_params[ $key ] = $value;
	}

	/**
	 * Get param data.
	 *
	 * Retrieves a value previously saved via `set-param` from the `$_params` member variable.
	 * This function does not sanitize values. Sanitization is required for further usage.
	 *
	 * @since ??
	 *
	 * @param string $key      Param key.
	 * @param mixed  $fallback Fallback value used when the parameter `$key` does not exist. Default is null.
	 *
	 * @return mixed
	 */
	public function get_param( $key, $fallback = null ) {
		if ( $this->has_param( $key ) ) {
			return $this->_params[ $key ];
		}

		return $fallback;
	}

	/**
	 * Check if param exists.
	 *
	 * Checks if the provided parameter key/value exists in the request parameters saved in `$_params`
	 *
	 * @since ??
	 *
	 * @param string $key Parameter key to look for.
	 *
	 * @return bool
	 */
	public function has_param( $key ) {
		return isset( $this->_params[ $key ] );
	}

	/**
	 * Generates UUIDs for the presets to avoid collisions.
	 *
	 * @since 4.5.0
	 *
	 * @param array $global_presets - The Global Presets to be imported.
	 *
	 * @return array The list of module types whose preset IDs changed.
	 */
	public function prepare_to_import_layout_presets( &$global_presets ) {
		$preset_rewrite_map = array();
		$initial_preset_id  = \ET_Builder_Global_Presets_Settings::MODULE_INITIAL_PRESET_ID;

		foreach ( $global_presets as $component_type => &$component_presets ) {
			$preset_rewrite_map[ $component_type ] = array();
			foreach ( $component_presets['presets'] as $preset_id => $preset ) {
				$new_id                                  = \ET_Core_Data_Utils::uuid_v4();
				$component_presets['presets'][ $new_id ] = $preset;
				$preset_rewrite_map[ $component_type ][ $preset_id ] = $new_id;
				unset( $component_presets['presets'][ $preset_id ] );
			}

			if ( $component_presets['default'] === $initial_preset_id && ! isset( $preset_rewrite_map[ $component_type ][ $initial_preset_id ] ) ) {
				$new_id                       = \ET_Core_Data_Utils::uuid_v4();
				$component_presets['default'] = $new_id;
				if ( isset( $component_presets['presets'][ $initial_preset_id ] ) ) {
					$component_presets['presets'][ $new_id ] = $component_presets['presets'][ $initial_preset_id ];
					unset( $component_presets['presets'][ $initial_preset_id ] );
				}
				$preset_rewrite_map[ $component_type ][ $initial_preset_id ] = $new_id;
			} else {
				$component_presets['default'] = $preset_rewrite_map[ $component_type ][ $component_presets['default'] ];
			}
		}

		return $preset_rewrite_map;
	}

	/**
	 * Replace post(s) image URLs with newly uploaded images.
	 *
	 * @since 2.7.0
	 *
	 * @param array $images {
	 *     Array of new images uploaded.
	 *
	 *     @type string|int $key   The key
	 *     @type string     $value The newly uploaded image URL.
	 * }
	 * @param array $data Array of post objects.
	 *
	 * @return array|mixed|object
	 */
	public function replace_images_urls( $images, $data ) {
		foreach ( $data as $post_id => &$post_data ) {
			foreach ( $images as $image ) {
				if ( is_array( $post_data ) ) {
					foreach ( $post_data as $post_param => &$param_value ) {
						if ( ! is_array( $param_value ) ) {
							$data[ $post_id ][ $post_param ] = $this->replace_image_url( $param_value, $image );
						}
					}
					unset( $param_value );
				} else {
					$data[ $post_id ] = $this->replace_image_url( $post_data, $image );
				}
			}
		}

		unset( $post_data );

		return $data;
	}

	/**
	 * Replace encoded image URL with a real URL.
	 *
	 * @param string $subject The string to perform replacing for.
	 * @param array  $image {
	 *     The image settings.
	 *
	 *     @type string|int id              The image ID.
	 *     @type string     url             The image URL.
	 *     @type string     replacement_id  The image replacement ID.
	 * }
	 *
	 * @return string|null
	 */
	public function replace_image_url( $subject, $image ) {
		$maybe_gutenberg_format = preg_match( '/<!-- wp:/', $subject );

		if ( isset( $image['replacement_id'] ) && isset( $image['id'] ) ) {
			$search      = $image['id'];
			$replacement = $image['replacement_id'];

			if ( $maybe_gutenberg_format ) {
				// https://regex101.com/r/0i8W9Y/1 - Regex.
				$pattern = "/(galleryIds.*?\"[^\"]*\".*?\"value\":\".*?){$search}(.*?\")/";
			} else {
				// https://regex101.com/r/HrJ2Ju/1 - Regex.
				$pattern = "/(gallery_ids=.*){$search}(.*\")/";
			}

			$subject = preg_replace( $pattern, "\${1}{$replacement}\${2}", $subject );
		}

		if ( isset( $image['url'] ) && isset( $image['replacement_url'] ) && $image['url'] !== $image['replacement_url'] ) {
			$search      = $image['url'];
			$replacement = $image['replacement_url'];
			$subject     = str_replace( $search, $replacement, $subject );
		}

		return $subject;
	}

	/**
	 * Restore the categories hierarchy in library.
	 *
	 * @since 2.7.0
	 *
	 * @param array  $parents_array {
	 *     Array of parent categories data.
	 *
	 *     @type string $slug   The category slug/key.
	 *     @type string name   The category name.
	 *     @type string parent The category parent name.
	 *     @type string description The category description.
	 * }
	 * @param string $taxonomy      Current taxonomy slug.
	 *
	 * @return void
	 */
	public function restore_parent_categories( $parents_array, $taxonomy ) {
		foreach ( $parents_array as $slug => $category_data ) {
			$current_category = term_exists( $slug, $taxonomy );

			if ( ! is_array( $current_category ) ) {
				$parent_id = 0 !== $category_data['parent'] ? term_exists( $category_data['parent'], $taxonomy ) : 0;
				wp_insert_term(
					$category_data['name'],
					$taxonomy,
					array(
						'slug'        => $slug,
						'description' => $category_data['description'],
						'parent'      => is_array( $parent_id ) ? $parent_id['term_id'] : $parent_id,
					)
				);
			} elseif ( ( ! isset( $current_category['parent'] ) || 0 === $current_category['parent'] ) && 0 !== $category_data['parent'] ) {
				$parent_id = 0 !== $category_data['parent'] ? term_exists( $category_data['parent'], $taxonomy ) : 0;
				wp_update_term( $current_category['term_id'], $taxonomy, array( 'parent' => is_array( $parent_id ) ? $parent_id['term_id'] : $parent_id ) );
			}
		}
	}

	/**
	 * Injects the given Global Presets settings into the imported layout
	 *
	 * @since 4.5.0
	 *
	 * @param array $shortcode_object {
	 *     The multidimensional array representing a page/module structure.
	 *     Note: Passed by reference.
	 *
	 *     @type array  attrs    Module attributes.
	 *     @type string content Module content.
	 *     @type string type    Module type.
	 * }
	 * @param array $global_presets     The Global Presets to be imported.
	 * @param array $preset_rewrite_map The list of module types for which preset ids have been changed.
	 *
	 * @return void
	 */
	public function rewrite_module_preset_ids( &$shortcode_object, $global_presets, $preset_rewrite_map ) {
		$global_presets_manager  = \ET_Builder_Global_Presets_Settings::instance();
		$module_preset_attribute = \ET_Builder_Global_Presets_Settings::MODULE_PRESET_ATTRIBUTE;

		foreach ( $shortcode_object as &$module ) {
			$module_type      = $global_presets_manager->maybe_convert_module_type( $module['type'], $module['attrs'] );
			$module_preset_id = et_()->array_get( $module, "attrs.{$module_preset_attribute}", 'default' );

			if ( 'default' === $module_preset_id ) {
				$module['attrs'][ $module_preset_attribute ] = et_()->array_get( $global_presets, "{$module_type}.default", 'default' );
			} else {
				if ( isset( $preset_rewrite_map[ $module_type ][ $module_preset_id ] ) ) {
					$module['attrs'][ $module_preset_attribute ] = $preset_rewrite_map[ $module_type ][ $module_preset_id ];
				} else {
					$module['attrs'][ $module_preset_attribute ] = et_()->array_get( $global_presets, "{$module_type}.default", 'default' );
				}
			}

			if ( isset( $module['content'] ) && is_array( $module['content'] ) ) {
				$this->rewrite_module_preset_ids( $module['content'], $global_presets, $preset_rewrite_map );
			}
		}
	}

	/**
	 * Validate data and remove any malicious code.
	 *
	 * If the provided data contains nested arrays, this function will call itself.
	 *
	 * @since 2.7.0
	 *
	 * @param array $data {
	 *     Array of data which needs to be validated.
	 *
	 *     @type string|int                  $key The key.
	 *     @type string|int|float|bool|array $value The data to be validated,
	 * }
	 * @param array $fields_validation {
	 *     Array of field and validation callback.
	 *
	 *     @type string|int $key   The key.
	 *     @type string     $value The function to be used to validate.
	 *                              The key should match the respective key of the value from `$data`.
	 * }
	 *
	 * @return array|bool Returns `false` if the data is not an array, otherwise returns an array if validated data.
	 */
	public function validate( $data, $fields_validation = array() ) {
		if ( ! is_array( $data ) ) {
			return false;
		}

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = $this->validate( $value, $fields_validation );
			} else {
				if ( isset( $fields_validation[ $key ] ) ) {
					$data[ $key ] = call_user_func( $fields_validation[ $key ], $value ); // @phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- The callable function is hard-coded.
				} else {
					if ( current_user_can( 'unfiltered_html' ) ) {
						$data[ $key ] = $value;
					} else {
						$data[ $key ] = wp_kses_post( $value );
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Initiate Import.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_handle_upload/ WordPress handler for the `upload` file context.
	 * @since 2.7.0
	 * @since ?? Removed `$file_context` because 'upload' is the only file context used.
	 *
	 * @param array      $files                         File objects.
	 * @param string     $layout                        Layout data.
	 * @param int        $post_id                       Post ID.
	 * @param null|bool  $include_global_presets_option Optional. Whether global options should be included.
	 * @param null|array $overrides                     Optional. Argument overrides passed to the WordPress handler for the given file context.
	 *
	 * @return null|array Data to be returned to the client, or null if the import failed.
	 */
	public function import( array $files, string $layout, int $post_id, ?bool $include_global_presets_option, ?array $overrides ): ?array {
		$this->prevent_failure();

		self::$_doing_import = true;

		$timestamp    = $this->get_timestamp();
		$filesystem   = $this->get_filesystem();

		/**
		 * TODO fix(D5, Cloud App): Implement local MIME Type validation in
		 *  https://github.com/elegantthemes/Divi/issues/34198 so that valid
		 *  JSON files are handled consistently.
		 */
		// phpcs:ignore ET.Sniffs.DangerousFunctions.ET_handle_upload -- test_type is enabled and proper type and extension checking are implemented.
		$upload = $layout ? null : wp_handle_upload(
			$files['file'],
			wp_parse_args(
				[
					'test_size' => false,
					'test_type' => true,
					'test_form' => false,
				],
				$overrides
			)
		);

		// If there is an error at this point, exit early and return the error message.
		if ( isset( $upload['error'] ) ) {
			$this->delete_temp_files( 'et_core_import' );

			$error_message = $upload['error'];
			/**
			 * Filters the error message shown when {@see ET_Core_Portability::import()} fails at the file upload stage.
			 *
			 * @since ??
			 *
			 * @param string $error_message Default is empty string.
			 *
			 * @return string Error message when import fails at the file upload stage.
			 */
			$error_message = apply_filters( 'divi_framework_portability_import_upload_error_message', $error_message );

			if ( $error_message ) {
				$error_message = [ 'message' => $error_message ];
			}

			return $error_message;
		}

		$temp_file_id = sanitize_file_name( $timestamp . $upload['file'] );
		$temp_file    = $layout ? null : $this->temp_file( $temp_file_id, 'et_core_import', $upload['file'] );

		$import    = $layout ? json_decode( $layout, true ) : json_decode( $filesystem->get_contents( $temp_file ), true );
		$import    = $this->validate( $import );

		// phpcs:disable ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- it is passed to wp_validate_boolean().
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verification is done in et_core_security_check_passed.
		$include_global_presets = isset( $include_global_presets_option ) && wp_validate_boolean( $include_global_presets_option );

		$global_presets = '';

		// Handle file upload if content is not provided to Portability.
		if ( ! $layout && $temp_file ) {
			$import = json_decode( $filesystem->get_contents( $temp_file ), true );
		} elseif ( ! $layout ) {
			$this->upload_file( $files, $filesystem, $temp_file_id );
		}

		if ( ! isset( $import['context'] ) || $import['context'] !== $this->instance->context ) {
			$this->delete_temp_files( 'et_core_import', [ $temp_file_id => $temp_file ] );

			return array( 'message' => esc_html__( 'This file should not be imported in this context.', 'et_builder' ) );
		}

		$post_type        = get_post_type( $post_id );
		$is_theme_builder = et_theme_builder_is_layout_post_type( $post_type );

		if ( ! $is_theme_builder ) {
			// https://regex101.com/r/Wm0BRb/1 - Regex for both D5 and D4.
			$pattern = '/<!-- wp:divi\/post-content.*?-->|\[et_pb_post_content.*?\/et_pb_post_content]/s';

			// Do not import post content module if it is not a theme builder -> visual builder.
			if ( is_array( $import['data'] ) ) {
				$post_content_data_id = key( $import['data'] );

				if ( isset( $import['data'][ $post_content_data_id ]['post_content'] ) ) {
					$import['data'][ $post_content_data_id ]['post_content'] = preg_replace( $pattern, '', $import['data'][ $post_content_data_id ]['post_content'] );
				} else {
					$import['data'][ $post_content_data_id ] = preg_replace( $pattern, '', $import['data'][ $post_content_data_id ] );
				}
			} else {
				$import['data'] = preg_replace( $pattern, '', $import['data'] );
			}
		}

		// Add filter to allow importing images.
		add_filter( 'wp_check_filetype_and_ext', [ HooksRegistration::class, 'check_filetype_and_ext_image' ], 999, 3 );

		// Upload images and replace current urls.
		if ( isset( $import['images'] ) ) {
			$images         = $this->maybe_paginate_images( (array) $import['images'], 'upload_images', $timestamp );
			$import['data'] = $this->replace_images_urls( $images, $import['data'] );
		}

		$data    = $import['data'];
		$success = [ 'timestamp' => $timestamp ];

		$this->delete_temp_files( 'et_core_import' );

		// Pass the post content and let js save the post.
		if ( 'post' === $this->instance->type ) {
			$success['postContent'] = $layout ? $this->get_layout_content( $data ) : reset( $data );

			// this will strip unwanted `<p>` tags that are added by `wpautop()` in some library/pre-made layouts.
			$success['postContent'] = HTMLUtility::fix_shortcodes( $success['postContent'] );

			if ( ! class_exists( 'ET_Builder_Module_Settings_Migration' ) ) {
				require_once ET_BUILDER_DIR . 'module/settings/Migration.php';
			}

			$success['postContent'] = ShortcodeMigration::maybe_migrate_legacy_shortcode( $success['postContent'] );
			$success['migrations']  = ET_Builder_Module_Settings_Migration::$migrated;
			$success['presets']     = isset( $import['presets'] ) && is_array( $import['presets'] ) ? $import['presets'] : (object) [];

			// Attempt to merge D4 presets with content only when presets should be imported.
			if ( $include_global_presets && is_array( $success['presets'] ) && ! empty( $success['presets'] ) ) {
				$presets = $success['presets'];
				// Skip merging if presets are in D5 format.
				if ( ! isset( $presets['module'] ) && ! isset( $presets['group'] ) ) {
					$success['postContent'] = $this->merge_legacy_presets( $success['postContent'], $success['presets'] );
				}
			}
		}

		if ( 'post_type' === $this->instance->type ) {
			$preset_rewrite_map = [];
			if ( ! empty( $import['presets'] ) && $include_global_presets ) {
				$preset_rewrite_map = $this->prepare_to_import_layout_presets( $import['presets'] );
				$global_presets     = $import['presets'];
			}
			foreach ( $data as &$post ) {
				$shortcode_object = et_fb_process_shortcode( $post['post_content'] );

				if ( ! empty( $import['presets'] ) ) {
					if ( $include_global_presets ) {
						$this->rewrite_module_preset_ids( $shortcode_object, $import['presets'], $preset_rewrite_map );
					} else {
						$this->apply_global_presets( $shortcode_object, $import['presets'] );
					}
				}

				$post_content = et_fb_process_to_shortcode( $shortcode_object, [], '', false );
				// Add slashes for post content to avoid unwanted un-slashing (by wp_un-slash) while post is inserting.
				$post['post_content'] = wp_slash( $post_content );

				// Upload thumbnail image if exist.
				if ( ! empty( $post['post_meta'] ) && ! empty( $post['post_meta']['_thumbnail_id'] ) ) {
					$post_thumbnail_origin_id = (int) $post['post_meta']['_thumbnail_id'][0];

					if ( ! empty( $import['thumbnails'] ) && ! empty( $import['thumbnails'][ $post_thumbnail_origin_id ] ) ) {
						$post_thumbnail_new = $this->upload_images( $import['thumbnails'][ $post_thumbnail_origin_id ] );
						$new_thumbnail_data = reset( $post_thumbnail_new );

						// New thumbnail image was uploaded and it should be updated.
						if ( isset( $new_thumbnail_data['replacement_id'] ) ) {
							$new_thumbnail_id  = $new_thumbnail_data['replacement_id'];
							$post['thumbnail'] = $new_thumbnail_id;
							if ( ! function_exists( 'wp_crop_image' ) ) {
								include ABSPATH . 'wp-admin/includes/image.php';
							}

							$thumbnail_path = get_attached_file( $new_thumbnail_id );

							// Generate all the image sizes and update thumbnail metadata.
							$new_metadata = wp_generate_attachment_metadata( $new_thumbnail_id, $thumbnail_path );
							wp_update_attachment_metadata( $new_thumbnail_id, $new_metadata );
						}
					}
				}
			}

			if ( ! $this->import_posts( $data ) ) {
				// Default value for error message.
				$error_message = false;

				/**
				 * Filters the error message shown when `ET_Core_Portability::import()` fails.
				 *
				 * This is for backward compatibility with hooks written for Divi version <5.0.0.
				 *
				 * @since 3.0.99 This filter was introduced.
				 * @deprecated 5.0.0 Use `divi_framework_portability_import_error_message` hook instead.
				 *
				 * @param mixed $error_message The error message. Default `false`.
				 */
				$error_message = apply_filters(
					'et_core_portability_import_error_message',
					$error_message
				);

				// D4 Compatibility measure. Change `$error_message` to empty string (D5 default filter value) if it is set to `false` (D4 default filter value).
				$error_message = false === $error_message ? '' : $error_message;

				/**
				 * Filters the error message shown when [ET_Core_Portability::import()](/docs/builder-api/php/Framework/Portability/PortabilityPost#import) fails.
				 *
				 * @since ??
				 *
				 * @param string $error_message The error message. Default empty string.
				 */
				$error_message = apply_filters( 'divi_framework_portability_import_error_message', $error_message );

				if ( $error_message ) {
					$error_message = [ 'message' => $error_message ];
				}

				return $error_message;
			}
		}

		// Reset the `wp_check_filetype_and_ext` filter after uploading image files.
		remove_filter( 'wp_check_filetype_and_ext', [ HooksRegistration::class, 'check_filetype_and_ext_image' ], 999, 3 );

		// import global colors start.
		if ( ! empty( $global_presets ) ) {
			if ( ! $this->import_global_presets( $global_presets ) ) {
				// Default value for error message.
				$error_message = false;

				/**
				 * This filter is documented in /builder-5/server/Framework/Portability/PortabilityPost.php
				 *
				 * @ignore
				 */
				$error_message = apply_filters(
					'et_core_portability_import_error_message',
					$error_message
				);

				// D4 Compatibility measure. Change `$error_message` to empty string (D5 default filter value) if it is set to `false` (D4 default filter value).
				$error_message = false === $error_message ? '' : $error_message;

				/**
				 * This filter is documented in /builder-5/server/Framework/Portability/PortabilityPost.php
				 *
				 * @ignore
				 */
				$error_message = apply_filters( 'divi_framework_portability_import_error_message', $error_message );

				if ( $error_message ) {
					$error_message = [ 'message' => $error_message ];
				}

				return $error_message;
			}
		}

		if ( ! empty( $import['global_colors'] ) ) {
			$success['globalColors'] = $this->import_global_colors( $import['global_colors'] );
		}

		if ( ! empty( $import['global_variables'] ) ) {
			$success['globalVariables'] = $this->import_global_variables( $import['global_variables'] );
		}

		return $success;
	}

	/**
	 * Initiate Export.
	 *
	 * @since 2.7.0
	 *
	 * @return null|array|WP_Error
	 */
	public function export() {
		$this->prevent_failure();
		et_core_nonce_verified_previously();

		$data                 = [];
		$timestamp            = $this->get_timestamp();
		$filesystem           = $this->get_filesystem();
		$temp_file_id         = sanitize_file_name( $timestamp );
		$temp_file            = $this->has_temp_file( $temp_file_id, 'et_core_export' );
		$apply_global_presets = $this->has_param( 'apply_global_presets' ) && wp_validate_boolean( $this->get_param( 'apply_global_presets' ) );
		$global_presets       = '';
		$global_colors        = '';
		$global_variables     = '';

		if ( $temp_file ) {
			$file_data        = json_decode( $filesystem->get_contents( $temp_file ) );
			$data             = (array) $file_data->data;
			$global_presets   = $file_data->presets;
			$global_colors    = $file_data->global_colors;
			$global_variables = $file_data->global_variables;
		} else {
			$temp_file = $this->temp_file( $temp_file_id, 'et_core_export' );

			if ( 'post' === $this->instance->type ) {
				$post    = $this->get_param( 'post' );
				$content = $this->get_param( 'content' );

				if ( $post && ! $content ) {
					$content = get_post_field( 'post_content', $post );
				}

				if ( null === $post || null === $content ) {
					return new WP_Error( 'broke', __( 'No post ID or content provided.', 'et_builder' ) );
				}

				$fields_validation = [
					'ID' => 'intval',
					// no post_content as the default case for no fields_validation will run it through perms based wp_kses_post, which is exactly what we want.
				];

				$post_data = [
					'post_content' => $content, // No need to run this through stripcslashes() like in D4 as the page content is saved in Gutenberg block format.
					'ID'           => $post,
				];

				$post_data = $this->validate( $post_data, $fields_validation );

				$data = [ $post_data['ID'] => $post_data['post_content'] ];

				$include_global_presets = $this->get_param( 'include_global_presets' );

				if ( null !== $include_global_presets ) {
					// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- filter_post_data() function does sanitation.
					$post_global_presets = $this->filter_post_data( $include_global_presets );
					$global_presets      = json_decode( stripslashes( $post_global_presets ) );
				}

				$include_global_colors = $this->get_param( 'include_global_colors' );

				if ( null !== $include_global_colors ) {
					// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- filter_post_data() function does sanitation.
					$post_global_colors = $this->filter_post_data( $include_global_colors );
					$global_colors      = json_decode( stripslashes( $post_global_colors ) );
				}

				$include_global_variables = $this->get_param( 'include_global_variables' );

				if ( null !== $include_global_variables ) {
					// phpcs:ignore ET.Sniffs.ValidatedSanitizedInput.InputNotSanitized -- filter_post_data() function does sanitation.
					$post_global_variables = $this->filter_post_data( $include_global_variables );
					$global_variables      = json_decode( stripslashes( $post_global_variables ) );
				}
			}

			if ( 'post_type' === $this->instance->type ) {
				$data = $this->export_posts_query();
			}

			$data = $this->apply_query( $data, 'set' );

			if ( 'post_type' === $this->instance->type ) {
				$used_global_presets   = [];
				$used_global_colors    = [];
				$used_global_variables = [];
				$options               = [
					'apply_global_presets' => true,
				];

				foreach ( $data as $post ) {
					$shortcode_object = et_fb_process_shortcode( $post->post_content );

					if ( 'post_type' === $this->instance->type ) {
						$used_global_colors = $this->get_used_global_colors( $shortcode_object, $used_global_colors );
					}

					if ( $apply_global_presets ) {
						$post->post_content = et_fb_process_to_shortcode( $shortcode_object, $options, '', false );
					} else {
						$used_global_presets = array_merge(
							$this->get_used_global_presets( $shortcode_object, $used_global_presets ),
							$used_global_presets
						);
					}
				}

				if ( ! empty( $used_global_presets ) ) {
					$global_presets = (object) $used_global_presets;
				}

				if ( ! empty( $used_global_colors ) ) {
					$global_colors = $this->get_global_colors_data( $used_global_colors );
				}
			}

			// put contents into file, this is temporary,
			// if images get paginated, this content will be brought back out
			// of a temp file in paginated request.
			$file_data = [
				'data'             => $data,
				'presets'          => $global_presets,
				'global_colors'    => $global_colors,
				'global_variables' => $global_variables,
			];

			$filesystem->put_contents( $temp_file, wp_json_encode( $file_data ) );
		}

		$thumbnails = $this->get_thumbnail_images( $data );

		$images = $this->get_data_images( $data );

		$data = [
			'context'          => $this->instance->context,
			'data'             => $data,
			'presets'          => $global_presets,
			'global_colors'    => $global_colors,
			'global_variables' => $global_variables,
			'images'           => $this->maybe_paginate_images( $images, 'encode_images', $timestamp ),
			'thumbnails'       => $thumbnails,
		];

		$filesystem->put_contents( $temp_file, wp_json_encode( $data ) );

		if ( $this->get_param( 'return_content' ) ) {
			return $data;
		}

		return [
			'timestamp' => $timestamp,
		];
	}

	/**
	 * Merges presets with legacy presets (D4) and applies them to the post content.
	 *
	 * @since ??
	 *
	 * @param string $post_content The post content to apply presets to.
	 * @param array  $presets      The presets to merge and apply.
	 *
	 * @return string The modified post content with presets applied.
	 */
	public function merge_legacy_presets( $post_content, $presets ) {
		// Get legacy preset data to exclude existing D4 presets.
		$legacy_data = GlobalPreset::get_legacy_data();

		$merged_presets = [];
		foreach ( $presets as $module_type => $module_presets ) {
			if ( isset( $module_presets['presets'] ) ) {
				$merged_presets[ $module_type ] = [
					'presets' => [],
					'default' => isset( $module_presets['default'] ) ? $module_presets['default'] : null,
				];

				foreach ( $module_presets['presets'] as $preset_id => $preset ) {
					// Only merge presets not present in legacy data.
					if ( ! isset( $legacy_data[ $module_type ]['presets'][ $preset_id ] ) ) {
						$merged_presets[ $module_type ]['presets'][ $preset_id ] = $preset;
					}
				}

				// Remove module type if no presets were added.
				if ( empty( $merged_presets[ $module_type ]['presets'] ) ) {
					unset( $merged_presets[ $module_type ] );
				}
			}
		}

		if ( ! empty( $merged_presets ) ) {
			// This needed because reasons.
			et_builder_add_main_elements();
			// This too.
			add_filter( 'et_builder_module_force_render', '__return_true' );
			$shortcode_object = et_fb_process_shortcode( $post_content );
			remove_filter( 'et_builder_module_force_render', '__return_true' );

			$stack = $shortcode_object;
			// phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found -- count() is fine.
			while ( count( $stack ) > 0 ) {
				$module = array_shift( $stack );
				if ( empty( $module ) ) {
					// If the module is empty, it means it's a D5 module,
					// merging legacy presets isn't possible.
					return $post_content;
				}
				if ( isset( $module['content'] ) && is_array( $module['content'] ) ) {
					// Add the module's content to the stack.
					$stack = array_merge( $stack, $module['content'] );
				}
			}

			// Apply presets and rebuild the shortcode.
			$this->apply_global_presets( $shortcode_object, $merged_presets );
			$post_content = et_fb_process_to_shortcode( $shortcode_object, [], '', false );
		}

		return $post_content;
	}

}
