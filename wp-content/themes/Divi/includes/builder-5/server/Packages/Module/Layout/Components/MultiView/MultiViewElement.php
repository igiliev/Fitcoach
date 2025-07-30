<?php
/**
 * Module: MultiViewElement class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\MultiView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\VisualBuilder\Saving\SavingUtility;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewElementValue;

/**
 * Module: MultiViewElement class.
 *
 * This class is used to render a module elements that support MultiView functionality.
 *
 * it utilizes MultiViewScriptData class to populate data for the element.
 *
 * @since ??
 */
class MultiViewElement {

	/**
	 * Module ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Module name
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Module selector
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $selector;

	/**
	 * Module hover selector
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $hover_selector;

	/**
	 * The ID of instance where this block stored in BlockParserStore class.
	 *
	 * @since ??
	 *
	 * @var int|null
	 */
	public $store_instance;

	/**
	 * A key-value pair array of custom sanitizers that will be used to override the default sanitizer.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_attributes_sanitizers = [];

	/**
	 * The function that will be invoked to sanitize/escape the children element. Default is `esc_html`.
	 *
	 * @since ??
	 *
	 * @var callable
	 */
	private $_children_sanitizer = 'esc_html';

	/**
	 * Whether the element is hidden on page load in tablet breakpoint.
	 *
	 * @since ??
	 *
	 * @var bool
	 */
	private $_hidden_on_load_tablet = false;

	/**
	 * Whether the element is hidden on page load in phone breakpoint.
	 *
	 * @since ??
	 *
	 * @var bool
	 */
	private $_hidden_on_load_phone = false;

	/**
	 * Create an instance of the MultiViewElement class.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id            Optional. The Module unique ID. Default empty string.
	 *     @type string $name          Optional. The Module name. Default empty string.
	 *     @type string $selector      Optional. The selector of element to be updated. Default empty string.
	 *     @type string $hoverSelector Optional. The selector to trigger hover event. Default `null`.
	 *     @type string $storeInstance Optional. The ID of instance where this block stored in BlockParserStore. Default `null`.
	 * }
	 */
	public function __construct( array $args ) {
		$this->id             = $args['id'] ?? '';
		$this->name           = $args['name'] ?? '';
		$this->selector       = $args['selector'] ?? '';
		$this->hover_selector = $args['hoverSelector'] ?? null;
		$this->store_instance = $args['storeInstance'] ?? null;
	}

	/**
	 * Create a new instance of the MultiViewElement class with the given arguments.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id            The Module unique ID.
	 *     @type string $name          The Module name.
	 *     @type string $selector      The selector of element to be updated.
	 *     @type string $hoverSelector Optional. The selector to trigger hover event. Default `null`.
	 * }
	 *
	 * @return MultiViewElement A new instance of the MultiViewElement class.
	 */
	public static function create( array $args ): MultiViewElement {
		return new MultiViewElement( $args );
	}

	/**
	 * Populates a class name array with data and returns the processed array.
	 *
	 * @since ??
	 *
	 * @param array $class_name_data An array of data where the keys are class names and the values can be either a
	 *                               boolean or an instance of MultiViewElementValue class.
	 *
	 * @return arrayAn array of processed class data.
	 */
	private function _populate_class_name( array $class_name_data ): array {
		$processed = [];
		$sanitizer = $this->_attributes_sanitizers['class'] ?? null;

		if ( ! is_callable( $sanitizer ) ) {
			$sanitizer = 'esc_attr';
		}

		foreach ( $class_name_data as $class_name => $value ) {
			if ( $value instanceof MultiViewElementValue ) {
				$populated = MultiViewScriptData::set_class_name(
					[
						'id'            => $this->id,
						'name'          => $this->name,
						'data'          => [
							$class_name => $value->get_data(),
						],
						'subName'       => $value->get_sub_name(),
						'valueResolver' => $value->get_value_resolver(),
						'sanitizer'     => $sanitizer,
						'selector'      => $value->get_selector() ?? $this->selector,
						'hoverSelector' => $value->get_hover_selector() ?? $this->hover_selector,
						'storeInstance' => $this->store_instance,
						'switchOnLoad'  => false,
					]
				);

				if ( 'et_multi_view_hidden' === $class_name ) {
					if ( in_array( $class_name, ( $populated['tablet']['add'] ?? [] ), true ) ) {
						$this->_hidden_on_load_tablet = true;

						if ( ! isset( $populated['phone'] ) ) {
							$this->_hidden_on_load_phone = true;
						}
					}

					if ( in_array( $class_name, ( $populated['phone']['add'] ?? [] ), true ) ) {
						$this->_hidden_on_load_phone = true;
					}
				}

				// Processed and sanitized class name array for the desktop breakpoint.
				$processed[ $class_name ] = in_array( $class_name, ( $populated['desktop']['add'] ?? [] ), true );
				continue;
			}

			if ( preg_match( '/[a-z]/i', $class_name ) ) { // Array key is the class name.
				$class_name = call_user_func( $sanitizer, $class_name );
			} elseif ( is_string( $value ) && preg_match( '/[a-z]/i', $value ) ) { // Array value is the class name.
				$value = call_user_func( $sanitizer, $value );
			}

			$processed[ $class_name ] = $value;
		}

		// At this point, we have all the class has been sanitized, so we can safely skip the sanitization process in the HTMLUtility::render().
		$this->_attributes_sanitizers['class'] = 'et_core_esc_previously';

		return $processed;
	}

	/**
	 * Populates an array with processed data, including styles, from a given array of data.
	 *
	 * @since ??
	 *
	 * @param array $style_data A key-value array of style data where the keys are style properties and the values can
	 *                          be either a scalar or an instance of MultiViewElementValue class.
	 *
	 * @return array An array of processed style data.
	 */
	private function _populate_style( array $style_data ): array {
		$processed = [];
		$sanitizer = $this->_attributes_sanitizers['style'] ?? null;

		if ( ! is_callable( $sanitizer ) ) {
			$sanitizer = [ SavingUtility::class, 'sanitize_css_properties' ];
		}

		foreach ( $style_data as $property => $value ) {
			if ( $value instanceof MultiViewElementValue ) {
				$populated = MultiViewScriptData::set_style(
					[
						'id'            => $this->id,
						'name'          => $this->name,
						'data'          => [
							$property => $value->get_data(),
						],
						'subName'       => $value->get_sub_name(),
						'valueResolver' => $value->get_value_resolver(),
						'sanitizer'     => $sanitizer,
						'selector'      => $value->get_selector() ?? $this->selector,
						'hoverSelector' => $value->get_hover_selector() ?? $this->hover_selector,
						'storeInstance' => $this->store_instance,
						'switchOnLoad'  => false,
					]
				);

				// Processed and sanitized style for the desktop breakpoint.
				$processed[ $property ] = $populated['desktop'][ $property ] ?? '';
				continue;
			}

			$sanitized = call_user_func( $sanitizer, $property . ':' . $value );

			if ( $sanitized ) {
				$sanitized = substr_replace( $sanitized, '', 0, strlen( $property . ':' ) );
			}

			$processed[ $property ] = $value;
		}

		// At this point, we have all the style has been sanitized, so we can safely skip the sanitization process in the HTMLUtility::render().
		$this->_attributes_sanitizers['style'] = 'et_core_esc_previously';

		return $processed;
	}

	/**
	 * Populates and processes attributes for a given tag in an array format.
	 *
	 * @since ??
	 *
	 * @param array  $attributes_data A key-value array of attributes data where the keys are attribute names and the
	 *                                values can be either a scalar, array or an instance of MultiViewElementValue class.
	 *                                A scalar value will be used as the attribute value.
	 *                                An array value is only applicable for `class` and style attributes.
	 *                                An instance of MultiViewElementValue class will be used to populate the multi-view data.
	 * @param string $tag             The HTML tag for on which the attributes are used.
	 *
	 * @return array An array of processed attributes.
	 */
	private function _populate_attributes( array $attributes_data, string $tag ): array {
		$processed = [];

		foreach ( $attributes_data as $attr_name => $value ) {
			if ( null === $value || is_scalar( $value ) ) {
				$processed[ $attr_name ] = $value;
				continue;
			}

			if ( 'class' === $attr_name ) {
				if ( is_array( $value ) ) {
					$processed[ $attr_name ] = $this->_populate_class_name( $value );
				}
				continue;
			}

			if ( 'style' === $attr_name ) {
				if ( is_array( $value ) ) {
					$processed[ $attr_name ] = $this->_populate_style( $value );
				}
				continue;
			}

			if ( $value instanceof MultiViewElementValue ) {
				$populated = MultiViewScriptData::set_attrs(
					[
						'id'            => $this->id,
						'name'          => $this->name,
						'data'          => [
							$attr_name => $value->get_data(),
						],
						'subName'       => $value->get_sub_name(),
						'valueResolver' => $value->get_value_resolver(),
						'sanitizers'    => [
							$attr_name => $this->_attributes_sanitizers[ $attr_name ] ?? null,
						],
						'selector'      => $value->get_selector() ?? $this->selector,
						'hoverSelector' => $value->get_hover_selector() ?? $this->hover_selector,
						'tag'           => $tag,
						'storeInstance' => $this->store_instance,
						'switchOnLoad'  => false,
					]
				);

				// At this point, we have all the attribute value has been sanitized, so we can safely skip the sanitization process in the HTMLUtility::render()
				// for the attributes that have been sanitized by MultiViewScriptData::set_attrs().
				$this->_attributes_sanitizers[ $attr_name ] = 'et_core_esc_previously';

				// Processed and sanitized attribute for the desktop breakpoint.
				$processed[ $attr_name ] = $populated['desktop'][ $attr_name ] ?? '';
			}
		}

		return $processed;
	}

	/**
	 * Populates children data for a multi-view element.
	 *
	 * @since ??
	 *
	 * @param string|array|MultiViewElementValue $children_data The children data to be populated.
	 *
	 * @return string|array The populated children data.
	 */
	private function _populate_children( $children_data ) {
		if ( $children_data instanceof MultiViewElementValue ) {
			$populated = MultiViewScriptData::set_content(
				[
					'id'            => $this->id,
					'name'          => $this->name,
					'data'          => $children_data->get_data(),
					'subName'       => $children_data->get_sub_name(),
					'valueResolver' => $children_data->get_value_resolver(),
					'sanitizer'     => $this->_children_sanitizer,
					'selector'      => $children_data->get_selector() ?? $this->selector,
					'hoverSelector' => $children_data->get_hover_selector() ?? $this->hover_selector,
					'storeInstance' => $this->store_instance,
					'switchOnLoad'  => false,
				]
			);

			// At this point, we have the children has been sanitized, we can safely skip the sanitization process in the HTMLUtility::render().
			$this->_children_sanitizer = 'et_core_esc_previously';

			if ( isset( $populated['tablet'] ) ) {
				$this->_hidden_on_load_tablet = true;

				if ( ! isset( $populated['phone'] ) ) {
					$this->_hidden_on_load_phone = true;
				}
			}

			if ( isset( $populated['phone'] ) ) {
				$this->_hidden_on_load_phone = true;
			}

			// Processed and sanitized children for the desktop breakpoint.
			return $populated['desktop'] ?? '';
		}

		return $children_data;
	}

	/**
	 * Renders HTML code with specified attributes and children that support multi-view.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string                             $tag                  Optional. HTML Element tag. Default `div`.
	 *     @type bool                               $tagEscaped           Optional. Whether the tag name has been escaped or not. Default `false`.
	 *     @type array                              $attributes           Optional. A key-value pair array of attributes data. Default `[]`.
	 *                                                                       - The array item key must be a string.
	 *                                                                       - For boolean attributes, the array item value must be a `true`.
	 *                                                                       - For key-value pair attributes, the array item value must be a
	 *                                                                         MultiViewElementValue object, int, float, string, boolean, array or null.
	 *                                                                          -- `MultiViewElementValue` value will be populated with multi view data.
	 *                                                                          -- `boolean` value will be stringified to avoid `true` get printed as `1`
	 *                                                                              and `false` get printed as `0`.
	 *                                                                          -- `array` value only applicable for `style` attribute.
	 *                                                                          -- `null` value will skip the attribute to be rendered.
	 *     @type string|array|MultiViewElementValue $children             Optional. The children element. Default `null`.
	 *                                                                       - Pass instance of MultiViewElementValue object for multi view children element.
	 *                                                                       - Pass string for single children element.
	 *                                                                       - Pass array for multiple children elements and nested children elements.
	 *                                                                       - Only applicable for non self-closing tags.
	 *     @type callable                           $childrenSanitizer    Optional. The function that will be invoked to sanitize/escape the children element.
	 *                                                                    Default `esc_html`.
	 *     @type array                              $attributesSanitizers Optional. A key-value pair array of custom sanitizers that will be used to override
	 *                                                                    the default sanitizer. Default `[]`.
	 * }
	 *
	 * @return string The rendered HTML code.
	 */
	public function render( array $args ): string {
		$tag         = $args['tag'] ?? 'div';
		$tag_escaped = $args['tagEscaped'] ?? false;

		if ( ! $tag_escaped ) {
			$tag = tag_escape( $tag );
		}

		if ( ! is_string( $tag ) || ! $tag ) {
			return '';
		}

		$attributes                   = $args['attributes'] ?? [];
		$children                     = $args['children'] ?? null;
		$this->_attributes_sanitizers = $args['attributesSanitizers'] ?? [];
		$this->_children_sanitizer    = $args['childrenSanitizer'] ?? 'esc_html';

		// Reset hidden on load flags.
		// It will be re-populated by `populate_children()` method and `populate_class_name()` method.
		$this->_hidden_on_load_tablet = false;
		$this->_hidden_on_load_phone  = false;

		// Populate attributes and children.
		$attributes = $this->_populate_attributes( $attributes, $tag );

		if ( $children ) {
			$children = HTMLUtility::is_self_closing_tag( $tag ) ? null : $this->_populate_children( $children );
		}

		if ( $this->_hidden_on_load_tablet ) {
			$attributes['data-et-multi-view-load-tablet-hidden'] = 'true';
		}

		if ( $this->_hidden_on_load_phone ) {
			$attributes['data-et-multi-view-load-phone-hidden'] = 'true';
		}

		return HTMLUtility::render(
			[
				'tag'                  => $tag,
				'tagEscaped'           => true,
				'attributes'           => $attributes,
				'children'             => $children,
				'attributesSanitizers' => $this->_attributes_sanitizers,
				'childrenSanitizer'    => $this->_children_sanitizer,
			]
		);
	}

}
