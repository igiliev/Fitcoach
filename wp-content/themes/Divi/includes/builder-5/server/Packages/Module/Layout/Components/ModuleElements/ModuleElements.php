<?php
/**
 * Module Element Class
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Packages\Module\Layout\Components\ModuleElements;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Options\BoxShadow\BoxShadowClassnames;
use WP_Block_Type;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewElement;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewElementValue;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewScriptData;
use ET\Builder\Packages\Module\Layout\Components\MultiView\MultiViewUtils;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET\Builder\Packages\Module\Options\Button\ButtonComponent;
use ET\Builder\Packages\Module\Options\Element\ElementStyle;
use ET\Builder\Packages\Module\Options\Element\ElementScriptData;
use ET\Builder\Packages\GlobalData\GlobalPresetItem;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\Framework\Utility\FormattingUtility;

/**
 * Module related helper class.
 *
 * @since ??
 */
class ModuleElements {

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
	 * A key-value pair of module attributes data where the key is the module attribute name and the value is the formatted attribute array.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public $module_attrs = [];

	/**
	 * Module attributes original data.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_module_attrs_original;

	/**
	 * A key-value pair of selectors where the key is the module attribute name and the value is the selector.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public $selectors = [];

	/**
	 * Key-value pair of module metadata (module.json config file).
	 *
	 * @since ??
	 *
	 * @var WP_Block_Type
	 */
	public $module_metadata;

	/**
	 * Base order classname.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $base_order_class = '';

	/**
	 * The selector class name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $order_class = '';

	/**
	 * Base wrapper order classname.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $base_wrapper_order_class = '';

	/**
	 * The selector class name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $wrapper_order_class = '';

	/**
	 * Module name class name.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $module_name_class = '';

	/**
	 * Module order ID.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $order_id = '';

	/**
	 *
	 * Module order index.
	 *
	 * @since ??
	 *
	 * @var mixed|null
	 */
	public $order_index;

	/**
	 *
	 * Module store instance.
	 *
	 * @since ??
	 *
	 * @var int|null
	 */
	public $store_instance;

	/**
	 * The group of the style where it will be added.
	 *
	 * @var string
	 */
	private $_style_group = 'module';

	/**
	 * Whether current post type is custom post type or not
	 *
	 * @since ??
	 *
	 * @var boolean
	 */
	private $_is_custom_post_type = false;

	/**
	 * Whether current module is inside another sticky module or not.
	 *
	 * @since ??
	 *
	 * @var boolean
	 */
	private $_is_inside_sticky_module = false;

	/**
	 * Whether current module is nested or not.
	 *
	 * @since ??
	 *
	 * @var boolean
	 */
	private $_is_nested_module = false;

	/**
	 * Default printed styles.
	 *
	 * @var array
	 */
	public $default_printed_style_attrs = [];

	/**
	 * Placeholder for merged attributes.
	 *
	 * @var array
	 */
	private $_merged_attrs;

	/**
	 * Whether parent module is flex or not.
	 *
	 * @since ??
	 *
	 * @var boolean
	 */
	private $_is_parent_layout_flex;

	/**
	 * Create an instance of ModuleElements class.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     Optional. An array of arguments. Default `[]`.
	 *
	 *     @type string $id                The Module unique ID.
	 *     @type string $name              The Module name.
	 *     @type array  $moduleAttrs       A key-value pair of module attributes data where the key is
	 *                                     the module attribute name and the value is the formatted attribute array.
	 *     @type array  $selectors         Optional. A key-value pair of selectors where the key is the module attribute
	 *                                     name and the value is the selector. If not provided, the selectors will be
	 *                                     retrieved from the module.json config file.
	 *                                     Default `ModuleRegistration::get_selectors( $this->name )`.
	 *     @type int    $storeInstance     Optional. The ID of instance where the module object is stored in BlockParserStore.
	 *                                     Default `null`.
	 *     @type int    $orderIndex        Optional. The order index of the module. Default `null`.
	 *     @type WP_Block_Type|array $moduleMetadata Optional. The module metadata. Could be an instance of WP_Block_Type or an array to be converted into WP_Block_Type instance.
	 *     @type boolean $is_custom_post_type Optional. Whether current post type is custom post type or not. Default `false`.
	 *     @type boolean $is_parent_layout_flex Optional. Whether parent module is flex or not. Default `false`.
	 * }
	 */
	public function __construct( array $args = [] ) {
		$this->id              = $args['id'] ?? '';
		$this->name            = $args['name'] ?? '';
		$this->module_attrs    = $args['moduleAttrs'] ?? [];
		$this->module_metadata = $args['moduleMetadata'] ?? null;

		$this->default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];

		// Normalize module metadata.
		if ( is_array( $this->module_metadata ) ) {
			$block_type_args = $this->module_metadata;

			// Replacing certain keys in the array with new keys based on a predefined mapping.
			// @see https://github.com/WordPress/wordpress-develop/blob/d065eedd0d88215637f3468c49a76057f4ca731f/src/wp-includes/blocks.php#L412C29-L412C29.
			$property_mappings = array(
				'apiVersion'      => 'api_version',
				'providesContext' => 'provides_context',
				'usesContext'     => 'uses_context',
			);

			foreach ( $property_mappings as $old_key => $new_key ) {
				if ( isset( $block_type_args[ $old_key ] ) ) {
					// Insert the new key-value pair.
					$block_type_args[ $new_key ] = $block_type_args[ $old_key ];

					// Remove the old key-value pair.
					unset( $block_type_args[ $old_key ] );
				}
			}

			$this->module_metadata = new WP_Block_Type( $block_type_args['name'] ?? $this->name, $block_type_args );
		}

		// Override the module name if the module metadata is an instance of WP_Block_Type.
		if ( $this->module_metadata instanceof WP_Block_Type ) {
			$this->name = $this->module_metadata->name ?? '';
		}

		$this->selectors      = $args['selectors'] ?? ModuleRegistration::get_selectors( $this->name );
		$this->order_index    = $args['orderIndex'] ?? null;
		$this->store_instance = $args['storeInstance'] ?? null;

		// Set $is_custom_post_type property.
		if ( isset( $args['is_custom_post_type'] ) ) {
			$this->_is_custom_post_type = $args['is_custom_post_type'];
		}

		// Set $_is_inside_sticky_module property.
		if ( isset( $args['is_inside_sticky_module'] ) ) {
			$this->_is_inside_sticky_module = $args['is_inside_sticky_module'];
		}

		// Set $_is_nested_module property.
		if ( isset( $args['is_nested_module'] ) ) {
			$this->_is_nested_module = $args['is_nested_module'];
		}

		// Set $_is_parent_layout_flex property.
		if ( isset( $args['is_parent_layout_flex'] ) ) {
			$this->_is_parent_layout_flex = $args['is_parent_layout_flex'];
		}
	}

	/**
	 * Create a new instance of the ModuleElements class with the given arguments.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $id          The Module unique ID.
	 *     @type string $name        The Module name.
	 *     @type array  $moduleAttrs A key-value pair of module attributes data where the key is the module attribute name
	 *                               and the value is the formatted attribute array.
	 *     @type array  $selectors   Optional. A key-value pair of selectors where the key is the module attribute name and
	 *                               the value is the selector.
	 *                               If not provided, the selectors will be retrieved from the module.json config file.
	 *                               Default `ModuleRegistration::get_selectors( $this->name )`.
	 * }
	 *
	 * @return ModuleElements A new instance of the ModuleElements class.
	 */
	public static function create( array $args ): ModuleElements {
		return new ModuleElements( $args );
	}

	/**
	 * Retrieve a selector from the given ModuleElementsAttr instance.
	 *
	 * @since ??
	 *
	 * @param ModuleElementsAttr $instance The instance of ModuleElementsAttr class.
	 *
	 * @return string
	 */
	private function _resolve_selector( ModuleElementsAttr $instance ): string {
		$selector = $instance->get_selector();

		if ( is_string( $selector ) ) {
			return $selector;
		}

		$attr_name = $instance->get_attr_name();

		if ( $attr_name ) {
			return $this->selectors[ $attr_name ] ?? '';
		}

		return '';
	}

	/**
	 * Retrieve module formatted attribute array based from the given ModuleElementsAttr instance.
	 *
	 * @since ??
	 *
	 * @param ModuleElementsAttr $instance The instance of ModuleElementsAttr class.
	 *
	 * @return array
	 */
	private function _resolve_attr( ModuleElementsAttr $instance ): array {
		$attr_name = $instance->get_attr_name();

		if ( $attr_name ) {
			// Element-based attribute structure enforces element content to be located inside `innerContent`
			// property. Therefore automatically retrieve the `innerContent` property if the attribute name is given.
			$attr = $this->module_attrs[ $attr_name ]['innerContent'] ?? $this->module_attrs[ $attr_name ] ?? [];

			// If $attr is not an array, or decoration, advanced, meta is presents in $attr, return an empty array.
			if ( ! is_array( $attr ) || isset( $attr['decoration'] ) || isset( $attr['advanced'] ) || isset( $attr['meta'] ) ) {
				return [];
			}

			return $attr;
		}

		$attr = $instance->get_attr();

		if ( is_array( $attr ) ) {
			return $attr;
		}

		return [];
	}

	/**
	 * Checks if an array has either 'attrName' or 'attr' keys.
	 *
	 * @since ??
	 *
	 * @param array $array The array to check.
	 *
	 * @return bool
	 */
	private function _is_attr_array( array $array ): bool {
		return isset( $array['attrName'] ) || isset( $array['attr'] );
	}

	/**
	 * Check if the HTML markup for self-closing tags should be rendered or not.
	 *
	 * This is achieved by checking the required attributes values.
	 *
	 * This function is only applicable if the required attributes value is instance of ModuleElementsAttr class.
	 *
	 * @since ??
	 *
	 * @param array  $attributes  A key-value pair array of attributes data to check.
	 * @param string $tag         HTML Element tag to check.
	 * @param string $parent_tag  Optional. The parent HTML Element tag where this element will be rendered.
	 *                            This is used to compute the required attributes for certain self-closing tags
	 *                            like `source` which needs to know the parent tag to compute the required attributes list.
	 *                            Default empty string.
	 *
	 * @return bool
	 */
	private function _is_render_self_closing_tag( array $attributes, string $tag, string $parent_tag = '' ): bool {
		$is_render = true;
		$required  = HTMLUtility::get_self_closing_tag_required_attrs( $tag, $parent_tag );

		if ( $required ) {
			$required_all        = $required['requiredAll'];
			$required_attributes = $required['attributes'];
			$required_count      = count( $required_attributes );

			foreach ( $required_attributes as $index => $required_attribute ) {
				$populated_attribute = $attributes[ $required_attribute ] ?? null;

				if ( ! $populated_attribute instanceof MultiViewElementValue ) {
					continue;
				}

				$has_value = $populated_attribute->has_value();

				if ( ! $required_all ) {
					if ( $has_value ) {
						break;
					}

					if ( ( $required_count - 1 ) === $index ) {
						$is_render = $has_value;
					}
				}

				if ( $required_all && ! $has_value ) {
					$is_render = false;
					break;
				}
			}
		}

		return $is_render;
	}

	/**
	 * Check if the HTML markup for paired tags should be rendered or not.
	 *
	 * This is achieved by checking the children.
	 *
	 * The function is only applicable if the children is an instance of ModuleElementsAttr class.
	 *
	 * @since ??
	 *
	 * @param string|array|ModuleElementsAttr $children The children element to check.
	 *
	 * @return bool
	 */
	private function _is_render_paired_tag( $children ): bool {
		return $children instanceof MultiViewElementValue ? $children->has_value() : true;
	}

	/**
	 * Populate and convert passed class name data to an instance of MultiViewElementValue class if needed.
	 *
	 * Array of ModuleElementsAttr constructor arguments will be converted to an instance of ModuleElementsAttr class.
	 *
	 * Instance of ModuleElementsAttr will be converted to an instance of MultiViewElementValue class.
	 *
	 * Other values will be returned as is.
	 *
	 * @since ??
	 *
	 * @param array $class_name_data A key-value array of attributes data where the keys are class name and the values
	 *                               can be either a scalar, instance of ModuleElementsAttr or array of
	 *                               ModuleElementsAttr constructor arguments.
	 *
	 * @return array
	 */
	private function _populate_class_name( array $class_name_data ): array {
		$processed = [];

		foreach ( $class_name_data as $class_name => $value ) {
			// Convert class name data to an instance of ModuleElementsAttr class if it's an array and has `attr` key.
			if ( is_array( $value ) && $this->_is_attr_array( $value ) ) {
				$value = ModuleElementsAttr::create(
					[
						'attrName'      => $value['attrName'] ?? null,
						'attr'          => $value['attr'] ?? null,
						'subName'       => $value['subName'] ?? null,
						'valueResolver' => $value['valueResolver'] ?? null,
						'selector'      => $value['selector'] ?? null,
						'hoverSelector' => $value['hoverSelector'] ?? null,
					]
				);
			};

			if ( $value instanceof ModuleElementsAttr ) {
				$processed[ $class_name ] = new MultiViewElementValue(
					[
						'data'          => $this->_resolve_attr( $value ),
						'subName'       => $value->get_sub_name(),
						'valueResolver' => $value->get_value_resolver(),
						'selector'      => $this->_resolve_selector( $value ),
						'hoverSelector' => $value->get_hover_selector(),
					]
				);

				continue;
			}

			$processed[ $class_name ] = $value;
		}

		return $processed;
	}

	/**
	 * Populate and convert passed styles data to an instance of MultiViewElementValue class if needed.
	 *
	 * Array of ModuleElementsAttr constructor arguments will be converted to an instance of ModuleElementsAttr class.
	 *
	 * Instance of ModuleElementsAttr will be converted to an instance of MultiViewElementValue class.
	 *
	 * Other values will be returned as is.
	 *
	 * @since ??
	 *
	 * @param array $style_data A key-value array of attributes data where the keys are style properties and the values can be
	 *                          either a scalar, instance of ModuleElementsAttr or array of ModuleElementsAttr constructor arguments.
	 *
	 * @return array An array of processed style data.
	 */
	private function _populate_style( array $style_data ): array {
		$processed = [];

		foreach ( $style_data as $property => $value ) {
			// Convert style data to an instance of ModuleElementsAttr class if it's an array and has `attr` key.
			if ( is_array( $value ) && $this->_is_attr_array( $value ) ) {
				$value = ModuleElementsAttr::create(
					[
						'attrName'      => $value['attrName'] ?? null,
						'attr'          => $value['attr'] ?? null,
						'subName'       => $value['subName'] ?? null,
						'valueResolver' => $value['valueResolver'] ?? null,
						'selector'      => $value['selector'] ?? null,
						'hoverSelector' => $value['hoverSelector'] ?? null,
					]
				);
			};

			if ( $value instanceof ModuleElementsAttr ) {
				$processed[ $property ] = new MultiViewElementValue(
					[
						'data'          => $this->_resolve_attr( $value ),
						'subName'       => $value->get_sub_name(),
						'valueResolver' => $value->get_value_resolver(),
						'selector'      => $this->_resolve_selector( $value ),
						'hoverSelector' => $value->get_hover_selector(),
					]
				);

				continue;
			}

			$processed[ $property ] = $value;
		}

		return $processed;
	}

	/**
	 * Populate and convert passed attributes data to an instance of MultiViewElementValue class if needed.
	 *
	 * Array of ModuleElementsAttr constructor arguments will be converted to an instance of ModuleElementsAttr class.
	 *
	 * Instance of ModuleElementsAttr will be converted to an instance of MultiViewElementValue class.
	 *
	 * Other values will be returned as is.
	 *
	 * @since ??
	 *
	 * @param array $attributes_data A key-value array of attributes data where the keys are HTML attribute names and the values can be
	 *                               either a scalar, instance of ModuleElementsAttr or array of ModuleElementsAttr constructor arguments.
	 *
	 * @return array An array of processed attributes.
	 */
	private function _populate_attributes( array $attributes_data ):array {
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

			// Convert attribute data to an instance of ModuleElementsAttr class if it's an array and has `attr` key.
			if ( is_array( $value ) && $this->_is_attr_array( $value ) ) {
				$value = ModuleElementsAttr::create(
					[
						'attrName'      => $value['attrName'] ?? null,
						'attr'          => $value['attr'] ?? null,
						'subName'       => $value['subName'] ?? null,
						'valueResolver' => $value['valueResolver'] ?? null,
						'selector'      => $value['selector'] ?? null,
						'hoverSelector' => $value['hoverSelector'] ?? null,
					]
				);
			}

			if ( $value instanceof ModuleElementsAttr ) {
				$processed[ $attr_name ] = new MultiViewElementValue(
					[
						'data'          => $this->_resolve_attr( $value ),
						'subName'       => $value->get_sub_name(),
						'valueResolver' => $value->get_value_resolver(),
						'selector'      => $this->_resolve_selector( $value ),
						'hoverSelector' => $value->get_hover_selector(),
					]
				);
			}
		}

		return $processed;
	}

	/**
	 * Populate children elements and returns a MultiViewElementValue object.
	 *
	 * If the children are not of type ModuleElementsAttr or array of ModuleElementsAttr constructor arguments,
	 * the children are returned as is.
	 *
	 * @since ??
	 *
	 * @param string|array|ModuleElementsAttr $children The children to be populated.
	 *
	 * @return string|array|MultiViewElementValue
	 */
	private function _populate_children( $children ) {
		// Convert children param into an instance of ModuleElementsAttr if the children param is an array and has attr key.
		if ( is_array( $children ) && $this->_is_attr_array( $children ) ) {
			$children = ModuleElementsAttr::create(
				[
					'attrName'      => $children['attrName'] ?? null,
					'attr'          => $children['attr'] ?? null,
					'subName'       => $children['subName'] ?? null,
					'valueResolver' => $children['valueResolver'] ?? null,
					'selector'      => $children['selector'] ?? null,
					'hoverSelector' => $children['hoverSelector'] ?? null,
				]
			);
		};

		if ( $children instanceof ModuleElementsAttr ) {
			return new MultiViewElementValue(
				[
					'data'          => $this->_resolve_attr( $children ),
					'subName'       => $children->get_sub_name(),
					'valueResolver' => $children->get_value_resolver(),
					'selector'      => $this->_resolve_selector( $children ),
					'hoverSelector' => $children->get_hover_selector(),
				]
			);
		}

		return $children;
	}

	/**
	 * Get inside sticky module status.
	 *
	 * @since ??
	 *
	 * @return boolean Whether current module is inside another sticky module or not.
	 */
	public function get_is_inside_sticky_module() {
		return $this->_is_inside_sticky_module;
	}

	/**
	 * Get parent layout flex status.
	 *
	 * @since ??
	 *
	 * @return boolean Whether current module is parent layout flex or not.
	 */
	public function get_is_parent_layout_flex() {
		return $this->_is_parent_layout_flex;
	}

	/**
	 * Render HTML code with specified attributes and children.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string                           $tagName              Optional. HTML Element tag. Default `div`.
	 *     @type string                           $parentTag            Optional. The parent HTML Element tag where this element will be rendered. Default empty string.
	 *                                                                  This is used to compute the required attributes for certain self-closing tags like `source` which
	 *                                                                  needs to know the parent tag to compute the required attributes list.
	 *     @type array                            $attributes           Optional. A key-value pair array of attributes data. Default `[]`.
	 *                                                                    - The array item key must be a string.
	 *                                                                    - For boolean attributes, the array item value must be a `true`.
	 *                                                                    - For key-value pair attributes, the array item value must be a MultiViewElementValue object,
	 *                                                                      array of ModuleElementsAttr constructor arguments, int, float, string, boolean, array or null.
	 *                                                                    - `ModuleElementsAttr` or array of ModuleElementsAttr constructor arguments value will be
	 *                                                                       computed with multi view data.
	 *                                                                    - `boolean` value will be stringified to avoid `true` get printed as `1` and `false` get
	 *                                                                       printed as `0`.
	 *                                                                    - `array` value only applicable for `style` attribute.
	 *                                                                    - `null` value will skip the attribute to be rendered.
	 *     @type string|array|ModuleElementsAttr $children              Optional. The children element. Default `null`.
	 *                                                                    - Pass instance of ModuleElementsAttr or array of ModuleElementsAttr constructor arguments to
	 *                                                                      compute multi view data.
	 *                                                                    - Pass string for single children element.
	 *                                                                    - Pass array for multiple children elements and nested children elements.
	 *                                                                    - Only applicable for non self-closing tags.
	 *     @type callable                         $childrenSanitizer    Optional. The function that will be invoked to sanitize/escape the children element. Default `esc_html`.
	 *     @type array                            $attributesSanitizers Optional. A key-value pair array of custom sanitizers that will be used to override the default sanitizer.
	 *                                                                  Default `[]`.
	 *     @type string                           $attrName             Optional. The Module attribute name. Default empty string.
	 *     @type array                            $attr                 Optional. The Module formatted attribute array. Default `[]`.
	 *     @type string                           $attrSubName          Optional. The attribute sub name that will be queried. Default `null`.
	 *     @type callable                         $valueResolver        Optional. A function that will be invoked to resolve the value. Default `null`.
	 *     @type string                           $selector             Optional. The selector of element to be updated. Default `null`.
	 *     @type string                           $hoverSelector        Optional. The selector to trigger hover event. Default `null`.
	 *     @type bool                             $forceRender          Optional. Flag to keep render the HTML code even if the children element is empty
	 *                                                                  or the required attributes in certain self-closing tags are not provided, or the module attribute that
	 *                                                                  passed into the `hiddenIfFalsy` param has no value across all breakpoints and states is empty.
	 *                                                                  Default `false`.
	 *     @type array|ModuleElementsAttr         $hiddenIfFalsy        Optional. Parameter that will be computed to determine if the element should be hidden if
	 *                                                                  certain module attribute value is falsy. Default ``.
	 *                                                                     - Array of ModuleElementsAttr constructor arguments.
	 *                                                                     - Instance of ModuleElementsAttr.
	 *     @type string                             $elementType        Optional. The element type. Default `element`.
	 *     @type array                              $elementProps       Optional. The element props. Default `[]`.
	 *
	 * }
	 *
	 * @return string The rendered HTML code.
	 */
	public function render( array $args ): string {
		// Attribute name. Attribute settings from metadata and module attributes are retrieved from this.
		$attr_name = $args['attrName'] ?? '';

		// Attribute subName.
		// TODO feat(D5, Refactor) rename `subName` into `attrSubName`.
		$attr_sub_name = $args['attrSubName'] ?? null;

		// Element attributes.
		$element_attr = $args['elementAttr'] ?? $this->module_attrs[ $attr_name ] ?? [];

		// Element settings retrieved from metadata (module.json).
		$element_settings = $this->module_metadata->attributes[ $attr_name ] ?? [];

		// Element type. Some arguments and rendered output are adjusted by this.
		$element_type = $args['elementType'] ?? $element_settings['elementType'] ?? 'element';

		// Element props.
		$element_props = $args['elementProps'] ?? $element_settings['elementProps'] ?? [];

		// Element tag name.
		$tag_name = tag_escape( ( $args['tagName'] ?? $element_settings['tagName'] ?? 'div' ) );

		// Element children's sanitizer.
		switch ( $element_type ) {
			case 'wrapper':
			case 'content':
				$children_sanitizer = $args['childrenSanitizer'] ?? $element_settings['childrenSanitizer'] ?? 'et_core_esc_previously';
				break;

			default:
				// Element children's sanitizer.
				$children_sanitizer = $args['childrenSanitizer'] ?? $element_settings['childrenSanitizer'] ?? 'esc_html';
				break;
		}

		// Check element's type and adjust element property accordingly.
		switch ( $element_type ) {
			case 'heading':
				// `heading` tagName changes based on the selected heading level on `decoration.font.font` attribute.
				$tag_name = tag_escape( $element_attr['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? $tag_name );
				break;

			case 'headingLink':
				// `headingLink` tagName changes based on the selected heading level on `decoration.font.font` attribute.
				$tag_name = tag_escape( $element_attr['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? $tag_name );

				// $attr_sub_name is automatically set for `headingLink` type element.
				$attr_sub_name = 'text';
				break;

			case 'button':
				// $attr_sub_name is automatically set for `button` type element.
				$attr_sub_name = 'text';
				break;

			default:
				break;
		}

		if ( ! is_string( $tag_name ) || ! $tag_name ) {
			return '';
		}

		$is_self_closing_tag   = HTMLUtility::is_self_closing_tag( $tag_name );
		$attributes_sanitizers = $args['attributesSanitizers'] ?? [];
		$attributes            = $args['attributes'] ?? $element_settings['attributes'] ?? [];
		$children              = $args['children'] ?? null;
		$hidden_if_falsy       = $args['hiddenIfFalsy'] ?? [];
		$force_render          = $args['forceRender'] ?? false;
		$parent_tag            = $args['parentTag'] ?? '';
		$allow_empty_value     = $args['allowEmptyValue'] ?? false;

		// Prepare element to be rendered.
		$element = '';

		// Check element's type and adjust rendered element accordingly.
		switch ( $element_type ) {
			case 'button':
				$button_attr = ModuleElementsAttr::create(
					[
						'attrName'      => $args['attrName'] ?? null,
						'attr'          => $args['attr'] ?? null,
						'subName'       => $args['attrSubName'] ?? $attr_sub_name,
						'valueResolver' => function ( $value, $resolver_args ) use ( $args ) {
							$value_resolver = $args['valueResolver'] ?? null;

							if ( null !== $value_resolver ) {
								$value = call_user_func( $value_resolver, $value, $resolver_args );
							}

							// Check if the button link text is plain text or wrapped in a HTML tag.
							// If the text is wrapped in a HTML tag, extract the text title from the tag.
							// Test regex: https://regex101.com/r/E5rBze/3.
							if ( ( preg_match( '/<[^<]+?>/', $value ) ) ) {
								// Extract the title text from the link.
								$value = ModuleUtils::extract_link_title( $value );
							};

							return $value;
						},
						'selector'      => $args['selector'] ?? null,
						'hoverSelector' => $args['hoverSelector'] ?? null,
					]
				);

				$inner_content = $element_props['innerContent'] ?? $element_attr['innerContent'] ?? [];

				$is_force_render = ModuleUtils::has_value(
					$inner_content,
					[
						'valueResolver' => function( $value, array $resolver_args ) use ( $element_props ) {
							$breakpoint = $resolver_args['breakpoint'] ?? 'desktop';
							$state      = $resolver_args['state'] ?? 'value';

							if ( 'desktop' === $breakpoint && 'value' === $state ) {
								return false;
							}

							return ButtonComponent::is_render(
								array_merge(
									[
										'text'    => $value['text'] ?? '',
										'linkUrl' => $value['linkUrl'] ?? '',
									],
									$element_props
								)
							);
						},
					]
				);

				// Get button classnames.
				$button_classnames = HTMLUtility::classnames(
					$attributes['class'] ?? '',
					MultiViewUtils::hidden_on_load_class_name(
						$inner_content,
						[
							'valueResolver' => function( $value ) use ( $element_props ) {
								$is_render_args = array_merge(
									[
										'text'    => $value['text'] ?? '',
										'linkUrl' => $value['linkUrl'] ?? '',
									],
									$element_props
								);

								return ButtonComponent::is_render(
									$is_render_args
								) ? 'visible' : 'hidden';
							},
						]
					)
				);

				// TODO: feat(D5, Improvement) Make `et_pb_newsletter_button_text` class name to be more generic and configurable.
				$text_wrapper_class_name         = 'et_pb_newsletter_button_text';
				$has_text_wrapper                = $element_props['hasTextWrapper'] ?? false;
				$multi_view_set_content_selector = $this->_resolve_selector( $button_attr );

				if ( $has_text_wrapper && $text_wrapper_class_name ) {

					// Split the selector by comma. Append the text wrapper class name to each selector
					// if the selector doesn't contain the text wrapper class name.
					$multi_view_set_content_selector_parts = explode( ',', $multi_view_set_content_selector );

					$multi_view_set_content_selector_parts = array_map(
						function( $selector ) use ( $text_wrapper_class_name ) {
							if ( false === strpos( $selector, $text_wrapper_class_name ) ) {
								return $selector . ' .' . $text_wrapper_class_name;
							}

							return $selector;
						},
						$multi_view_set_content_selector_parts
					);

					$multi_view_set_content_selector = implode( ',', $multi_view_set_content_selector_parts );
				}

				$resolved_data = $this->_resolve_attr( $button_attr );

				// If the innerContent key is present into $resolved_data array then only
				// assign that value to $resolved_data otherwise keep it as it is.
				if ( is_array( $resolved_data ) && isset( $resolved_data['innerContent'] ) ) {
					$resolved_data = $resolved_data['innerContent'] ?? [];
				}

				$hover_selector = $button_attr->get_hover_selector() ?? '{{selector}}';

				MultiViewScriptData::set(
					[
						'id'            => $this->id,
						'name'          => $this->name,
						'storeInstance' => $this->store_instance,
						'setContent'    => [
							[
								'data'          => $resolved_data,
								'selector'      => $multi_view_set_content_selector,
								'hoverSelector' => $hover_selector,
								'subName'       => $button_attr->get_sub_name(),
								'valueResolver' => $button_attr->get_value_resolver(),
							],
						],
						'setVisibility' => [
							[
								'data'          => $resolved_data,
								'selector'      => $this->_resolve_selector( $button_attr ),
								'hoverSelector' => $hover_selector,
								'subName'       => $button_attr->get_sub_name(),
								'valueResolver' => $button_attr->get_value_resolver(),
							],
						],
					]
				);

				// Render button element.
				$element = ButtonComponent::component(
					array_merge(
						[
							'className'    => $button_classnames,
							'innerContent' => $element_attr['innerContent'] ?? [],
							'buttonAttr'   => $element_attr['decoration']['button'] ?? [],
							'forceRender'  => $is_force_render,
						],
						$element_props
					)
				);
				break;

			case 'image':
				$inner_content     = ModuleElementsUtils::populate_image_element_attrs( $element_attr['innerContent'] ?? [] );
				$has_values        = [];
				$attrs_to_populate = [];
				$attr_keys_mapping = [
					'src'    => 'src',
					'alt'    => 'alt',
					'title'  => 'title',
					'width'  => 'width',
					'height' => 'height',
				];

				if ( et_is_responsive_images_enabled() ) {
					$attr_keys_mapping['srcset'] = 'srcset';
					$attr_keys_mapping['sizes']  = 'sizes';
				}

				foreach ( $attr_keys_mapping as $attr_key => $populate_sub_name ) {
					$has_value = ModuleUtils::has_value(
						$inner_content,
						[
							'subName' => $populate_sub_name,
						]
					);

					if ( $has_value ) {
						$attrs_to_populate[ $attr_key ] = [
							'attr'          => $inner_content,
							'selector'      => $args['selector'] ?? $this->selectors[ $attr_name ] ?? '',
							'subName'       => $populate_sub_name,
							'hoverSelector' => $args['hoverSelector'] ?? null,
							'valueResolver' => $args['valueResolver'] ?? null,
						];
					}

					$has_values[ $attr_key ] = $has_value;
				}

				if ( $has_values['src'] || $allow_empty_value ) {
					$wp_image_classes = [];

					foreach ( $inner_content as $breakpoint => $states ) {
						foreach ( $states as $state => $state_value ) {
							$attachment_id = $state_value['id'] ?? 0;

							if ( $attachment_id ) {
								$wp_image_classes[ 'wp-image-' . $attachment_id ] = [
									'attr'          => $inner_content,
									'selector'      => $args['selector'] ?? $this->selectors[ $attr_name ] ?? '',
									'hoverSelector' => $args['hoverSelector'] ?? null,
									'valueResolver' => function( $value, array $resolver_args ) use ( $breakpoint, $state ) {
										if ( $resolver_args['breakpoint'] === $breakpoint && $resolver_args['state'] === $state ) {
											return 'add';
										}

										return 'remove';
									},
								];
							}
						}
					}

					// Merge custom class names passed from the attributes.
					if ( isset( $attributes['class'] ) ) {
						$wp_image_classes = is_string( $attributes['class'] ) ? array_merge(
							[
								$attributes['class'] => true,
							],
							$wp_image_classes
						) : array_merge( $attributes['class'], $wp_image_classes );

						unset( $attributes['class'] );
					}

					if ( ! empty( $wp_image_classes ) ) {
						$attrs_to_populate['class'] = $wp_image_classes;
					}

					$image_attributes = $this->_populate_attributes( $attrs_to_populate );
					$image_attributes = array_merge( $attributes, $image_attributes );

					$element = MultiViewElement::create(
						[
							'id'            => $this->id,
							'name'          => $this->name,
							'storeInstance' => $this->store_instance,
						]
					)->render(
						[
							'tag'                  => 'img',
							'tagEscaped'           => true,
							'attributes'           => $image_attributes,
							'children'             => null,
							'attributesSanitizers' => [],
							'childrenSanitizer'    => null,
						]
					);
				}
				break;

			case 'imageLink':
				$inner_content     = ModuleElementsUtils::populate_image_element_attrs( $element_attr['innerContent'] ?? [] );
				$has_values        = [];
				$attrs_to_populate = [];
				$attr_keys_mapping = [
					'src'    => 'src',
					'alt'    => 'alt',
					'title'  => 'titleText',
					'width'  => 'width',
					'height' => 'height',
				];

				if ( et_is_responsive_images_enabled() ) {
					$attr_keys_mapping['srcset'] = 'srcset';
					$attr_keys_mapping['sizes']  = 'sizes';
				}

				$image_selector = $args['selector'] ?? $this->selectors[ $attr_name ] ?? '';

				foreach ( $attr_keys_mapping as $attr_key => $populate_sub_name ) {
					$has_value = ModuleUtils::has_value(
						$inner_content,
						[
							'subName' => $populate_sub_name,
						]
					);

					if ( $has_value ) {
						$attrs_to_populate[ $attr_key ] = [
							'attr'          => $inner_content,
							'selector'      => $image_selector,
							'subName'       => $populate_sub_name,
							'hoverSelector' => $args['hoverSelector'] ?? null,
							'valueResolver' => $args['valueResolver'] ?? null,
						];
					}

					$has_values[ $attr_key ] = $has_value;
				}

				if ( $has_values['src'] || $allow_empty_value ) {
					$wp_image_classes = [];

					foreach ( $inner_content as $breakpoint => $states ) {
						foreach ( $states as $state => $state_value ) {
							$attachment_id = $state_value['id'] ?? 0;

							if ( $attachment_id ) {
								$wp_image_classes[ 'wp-image-' . $attachment_id ] = [
									'attr'          => $inner_content,
									'selector'      => $image_selector,
									'hoverSelector' => $args['hoverSelector'] ?? null,
									'valueResolver' => function( $value, array $resolver_args ) use ( $breakpoint, $state ) {
										if ( $resolver_args['breakpoint'] === $breakpoint && $resolver_args['state'] === $state ) {
											return 'add';
										}

										return 'remove';
									},
								];
							}
						}
					}

					// Merge custom class names passed from the attributes.
					if ( isset( $attributes['class'] ) ) {
						$wp_image_classes = is_string( $attributes['class'] ) ? array_merge(
							[
								$attributes['class'] => true,
							],
							$wp_image_classes
						) : array_merge( $attributes['class'], $wp_image_classes );

						unset( $attributes['class'] );
					}

					if ( ! empty( $wp_image_classes ) ) {
						$attrs_to_populate['class'] = $wp_image_classes;
					}

					$image_attributes = $this->_populate_attributes( $attrs_to_populate );
					$image_attributes = array_merge( $attributes, $image_attributes );

					$image = MultiViewElement::create(
						[
							'id'            => $this->id,
							'name'          => $this->name,
							'storeInstance' => $this->store_instance,
						]
					)->render(
						[
							'tag'                  => 'img',
							'tagEscaped'           => true,
							'attributes'           => $image_attributes,
							'children'             => null,
							'attributesSanitizers' => [],
							'childrenSanitizer'    => null,
						]
					);

					$url              = $element_attr['innerContent']['desktop']['value']['linkUrl'] ?? '';
					$url_target       = $element_attr['innerContent']['desktop']['value']['linkTarget'] ?? null;
					$show_in_lightbox = $element_attr['advanced']['lightbox']['desktop']['value'] ?? 'off';
					$use_overlay      = $element_attr['advanced']['overlay']['desktop']['value']['use'] ?? 'off';
					$is_lightbox      = 'on' === $show_in_lightbox;
					$is_overlay       = 'on' === $use_overlay && ( $is_lightbox || ( ! $is_lightbox && '' !== $url ) );

					// Overlay.
					$hover_icon        = Utils::process_font_icon( $element_attr['advanced']['overlayIcon']['desktop']['value']['hoverIcon'] ?? null );
					$hover_icon_sticky = Utils::process_font_icon( $element_attr['advanced']['overlayIcon']['desktop']['sticky']['hoverIcon'] ?? null );
					$hover_icon_tablet = Utils::process_font_icon( $element_attr['advanced']['overlayIcon']['tablet']['value']['hoverIcon'] ?? null );
					$hover_icon_phone  = Utils::process_font_icon( $element_attr['advanced']['overlayIcon']['phone']['value']['hoverIcon'] ?? null );
					$overlay           = $is_overlay ? HTMLUtility::render(
						[
							'tag'        => 'span',
							'attributes' => [
								'class'            => HTMLUtility::classnames(
									[
										'et_overlay' => true,
										'et_pb_inline_icon' => ! empty( $hover_icon ),
										'et_pb_inline_icon_tablet' => ! empty( $hover_icon_tablet ),
										'et_pb_inline_icon_phone' => ! empty( $hover_icon_phone ),
										'et_pb_inline_icon_sticky' => ! empty( $hover_icon_sticky ),
									]
								),
								'data-icon'        => $hover_icon,
								'data-icon-sticky' => $hover_icon_sticky,
								'data-icon-tablet' => $hover_icon_tablet,
								'data-icon-phone'  => $hover_icon_phone,
							],
						]
					) : '';

					$box_shadow_classname = BoxShadowClassnames::has_overlay( $element_attr['decoration']['boxShadow'] ?? [] );

					$image_wrap = $has_values['src'] && ! empty( $args['imageWrapperClassName'] ) ? HTMLUtility::render(
						[
							'tag'               => 'span',
							'attributes'        => [
								'class' => HTMLUtility::classnames(
									[
										'et_pb_image_wrap' => true,
									],
									$box_shadow_classname
								),
							],
							'childrenSanitizer' => 'et_core_esc_previously',
							'children'          => [
								ElementComponents::component(
									[
										'attrs'         => $element_attr['decoration'] ?? [],
										'id'            => $this->id,
										'background'    => false,
										'boxShadow'     => [
											'settings' => [
												'overlay' => true,
											],
										],
										'orderIndex'    => $this->order_index,
										'storeInstance' => $this->store_instance,
									]
								),
								$image,
								$overlay,
							],
						]
					) : $image . $overlay;

					if ( $is_lightbox ) {
						$link_selector   = '{{selector}} a.et_pb_lightbox_image';
						$link_attributes = $this->_populate_attributes(
							[
								'href'  => [
									'attr'     => $inner_content,
									'selector' => $link_selector,
									'subName'  => 'src',
								],
								'title' => [
									'attr'     => $inner_content,
									'selector' => $link_selector,
									'subName'  => 'alt',
								],
								'class' => 'et_pb_lightbox_image',
							]
						);

						if ( ! $has_values['alt'] ) {
							unset( $link_attributes['title'] );
						}

						$element = MultiViewElement::create(
							[
								'id'            => $this->id,
								'name'          => $this->name,
								'storeInstance' => $this->store_instance,
							]
						)->render(
							[
								'tag'                  => 'a',
								'tagEscaped'           => true,
								'attributes'           => $link_attributes,
								'childrenSanitizer'    => 'et_core_esc_previously',
								'children'             => $image_wrap,
								'attributesSanitizers' => [
									'href' => [
										'ET\Builder\Framework\Utility\SanitizerUtility',
										'sanitize_image_src',
									],
								],
							]
						);
					} elseif ( ! empty( $url ) ) {
						$element = HTMLUtility::render(
							[
								'tag'               => 'a',
								'tagEscaped'        => true,
								'attributes'        => [
									'href'   => $url,
									'target' => 'on' === $url_target ? '_blank' : null,
								],
								'childrenSanitizer' => 'et_core_esc_previously',
								'children'          => $image_wrap,
							]
						);
					} else {
						$element = $image_wrap;
					}
				}

				break;

			case 'wrapper':
				$populated_attributes = $this->_populate_attributes( $attributes );
				$populated_children   = $this->_populate_children( $args['children'] ?? '' );
				$element              = HTMLUtility::render(
					[
						'tag'                  => $tag_name,
						'attributes'           => $populated_attributes,
						'children'             => $populated_children,
						'attributesSanitizers' => $attributes_sanitizers,
						'childrenSanitizer'    => $children_sanitizer,
					]
				);
				break;

			case 'content':
				$has_value_content = ModuleUtils::has_value(
					$element_attr['innerContent'] ?? [],
					[
						'subName' => $attr_sub_name,
					]
				);

				if ( $has_value_content || $allow_empty_value ) {
					$populated_attributes = $this->_populate_attributes(
						[
							'class' => $attributes['class'] ?? null,
						]
					);

					$populated_children = $this->_populate_children(
						ModuleElementsAttr::create(
							[
								'attrName'      => $args['attrName'] ?? null,
								'attr'          => $args['attr'] ?? null,
								'subName'       => $attr_sub_name,
								'valueResolver' => function ( $value, $resolver_args ) use ( $args, $element_settings ) {
									$value_resolver = $args['valueResolver'] ?? null;

									if ( null !== $value_resolver ) {
										$value = call_user_func( $value_resolver, $value, $resolver_args );
									}

									$apply_wpautop = $args['applyWpautop'] ?? $element_settings['applyWpautop'] ?? true;

									// Do not apply wpautop if arguments or element settings of applyWpautop set to false.
									if ( ! $apply_wpautop ) {
										return $value;
									}

									return FormattingUtility::maybe_wpautop( $value );
								},
								'selector'      => $args['selector'] ?? null,
								'hoverSelector' => $args['hoverSelector'] ?? null,
							]
						)
					);

					$element = MultiViewElement::create(
						[
							'id'            => $this->id,
							'name'          => $this->name,
							'storeInstance' => $this->store_instance,
						]
					)->render(
						[
							'tag'                  => $tag_name,
							'tagEscaped'           => true,
							'attributes'           => $populated_attributes,
							'children'             => $populated_children,
							'attributesSanitizers' => $attributes_sanitizers,
							'childrenSanitizer'    => $children_sanitizer,
						]
					);
				}
				break;

			case 'headingLink':
				$heading_link        = $element_attr['innerContent']['desktop']['value']['url'] ?? '';
				$heading_text        = $element_attr['innerContent']['desktop']['value']['text'] ?? '';
				$heading_link_target = $element_attr['innerContent']['desktop']['value']['target'] ?? '';

				// Convert attrName or attr param into an instance of ModuleElementsAttr and override the children param.
				// The attrName or attr param is prioritized over the children param.
				if ( $this->_is_attr_array( $args ) ) {
					$children = ModuleElementsAttr::create(
						[
							'attrName'      => $args['attrName'] ?? null,
							'attr'          => $args['attr'] ?? null,
							'subName'       => $attr_sub_name,
							'valueResolver' => $args['valueResolver'] ?? null,
							'selector'      => $args['selector'] ?? null,
							'hoverSelector' => $args['hoverSelector'] ?? null,
						]
					);
				}

				$children = $heading_link ? HTMLUtility::render(
					[
						'tag'               => 'a',
						'attributes'        => [
							'href'   => $heading_link,
							'target' => 'on' === $heading_link_target ? '_blank' : null,
						],
						'childrenSanitizer' => 'et_core_esc_previously',
						'children'          => $heading_text,
					]
				) : $children;

				$populated_attributes = $this->_populate_attributes( $attributes );
				$populated_children   = $this->_populate_children( $children );

				$element = MultiViewElement::create(
					[
						'id'            => $this->id,
						'name'          => $this->name,
						'storeInstance' => $this->store_instance,
					]
				)->render(
					[
						'tag'                  => $tag_name,
						'tagEscaped'           => true,
						'attributes'           => $populated_attributes,
						'children'             => $populated_children,
						'attributesSanitizers' => $attributes_sanitizers,
						'childrenSanitizer'    => $children_sanitizer,
					]
				);
				break;

			default:
				if ( $hidden_if_falsy ) {
					if ( is_array( $hidden_if_falsy ) && $this->_is_attr_array( $hidden_if_falsy ) ) {
						$hidden_if_falsy = ModuleElementsAttr::create(
							[
								'attrName'      => $hidden_if_falsy['attrName'] ?? null,
								'attr'          => $hidden_if_falsy['attr'] ?? null,
								'subName'       => $hidden_if_falsy['subName'] ?? null,
								'valueResolver' => $hidden_if_falsy['valueResolver'] ?? null,
								'selector'      => $hidden_if_falsy['selector'] ?? null,
								'hoverSelector' => $hidden_if_falsy['hoverSelector'] ?? null,
							]
						);
					}

					if ( $hidden_if_falsy instanceof ModuleElementsAttr ) {
						if ( ! $force_render ) {
							$hidden_if_falsy_has_value = ModuleUtils::has_value(
								$this->_resolve_attr( $hidden_if_falsy ),
								[
									'subName'       => $hidden_if_falsy->get_sub_name(),
									'valueResolver' => $hidden_if_falsy->get_value_resolver(),
								]
							);

							// Bail early if the `hiddenIfFalsy` module attribute has no value.
							if ( ! $hidden_if_falsy_has_value ) {
								return '';
							}
						}

						$attributes_class = $attributes['class'] ?? [];

						if ( ! is_array( $attributes_class ) ) {
							$attributes_class = [ $attributes_class ];
						}

						$attributes['class'] = array_merge(
							$attributes_class,
							[
								'et_multi_view_hidden' => $hidden_if_falsy->set(
									[
										// Override the valueResolver param to check if the value is falsy.
										'valueResolver' => function( $value, array $resolver_args ) use ( $hidden_if_falsy ) {
											$value_resolver_original = $hidden_if_falsy->get_value_resolver();

											if ( is_callable( $value_resolver_original ) ) {
												$value = call_user_func( $value_resolver_original, $value, $resolver_args );
											}

											return empty( $value ) ? 'add' : 'remove'; // Add class `et_multi_view_hidden` if the value is falsy.
										},
									]
								),
							]
						);
					}
				}

				if ( $is_self_closing_tag ) {
					$children = null;
				} else {
					// Convert attrName or attr param into an instance of ModuleElementsAttr and override the children param.
					// The attrName or attr param is prioritized over the children param.
					if ( $this->_is_attr_array( $args ) ) {
						$children = ModuleElementsAttr::create(
							[
								'attrName'      => $args['attrName'] ?? null,
								'attr'          => $args['attr'] ?? null,
								'subName'       => $attr_sub_name,
								'valueResolver' => $args['valueResolver'] ?? null,
								'selector'      => $args['selector'] ?? null,
								'hoverSelector' => $args['hoverSelector'] ?? null,
							]
						);
					}
				}

				$populated_attributes = $this->_populate_attributes( $attributes );
				$populated_children   = $this->_populate_children( $children );

				if ( ! $force_render ) {
					if ( $is_self_closing_tag ) {
						$is_render = $this->_is_render_self_closing_tag( $populated_attributes, $tag_name, $parent_tag );
					} else {
						$is_render = $this->_is_render_paired_tag( $populated_children );
					}

					// Bail early if the children element is empty or the attributes that required by certain tag is empty.
					if ( ! $is_render ) {
						return '';
					}
				}
				$element = MultiViewElement::create(
					[
						'id'            => $this->id,
						'name'          => $this->name,
						'storeInstance' => $this->store_instance,
					]
				)->render(
					[
						'tag'                  => $tag_name,
						'tagEscaped'           => true,
						'attributes'           => $populated_attributes,
						'children'             => $populated_children,
						'attributesSanitizers' => $attributes_sanitizers,
						'childrenSanitizer'    => $children_sanitizer,
					]
				);
				break;
		}

		/**
		 * Filter the element before rendered module element.
		 *
		 * @since ??
		 *
		 * @param string $before_element The element before rendered module element.
		 * @param array  $args           Module element parameters.
		 * @param object $this           The ModuleElements instance.
		 */
		$before_element = apply_filters( 'divi_module_elements_before_render', '', $args, $this );

		/**
		 * Filter the rendered module element.
		 *
		 * @since ??
		 *
		 * @param string $element The rendered module element.
		 * @param array  $args    Module element parameters.
		 * @param object $this    The ModuleElements instance.
		 */
		$element = apply_filters( 'divi_module_elements_render', $element, $args, $this );

		/**
		 * Filter the element after rendered module element.
		 *
		 * @since ??
		 *
		 * @param string $after_element The element after rendered module element.
		 * @param array  $args          Module element parameters.
		 * @param object $this          The ModuleElements instance.
		 */
		$after_element = apply_filters( 'divi_module_elements_after_render', '', $args, $this );

		return $before_element . $element . $after_element;
	}

	/**
	 * Set base order class.
	 *
	 * @since ??
	 *
	 * @param string $base_order_class The base order class.
	 */
	public function set_base_order_class( string $base_order_class ): void {
		$this->base_order_class = $base_order_class;
	}

	/**
	 * Set the order class.
	 *
	 * @since ??
	 *
	 * @param string $order_class The order class.
	 *
	 * @return void
	 */
	public function set_order_class( string $order_class ): void {
		$this->order_class = $order_class;
	}

	/**
	 * Set base wrapper order class.
	 *
	 * @since ??
	 *
	 * @param string $base_wrapper_order_class The base wrapper order class.
	 */
	public function set_base_wrapper_order_class( string $base_wrapper_order_class ): void {
		$this->base_wrapper_order_class = $base_wrapper_order_class;
	}

	/**
	 * Set the wrapper order class.
	 *
	 * @since ??
	 *
	 * @param string $wrapper_order_class The order class.
	 *
	 * @return void
	 */
	public function set_wrapper_order_class( string $wrapper_order_class ): void {
		$this->wrapper_order_class = $wrapper_order_class;
	}

	/**
	 * Set module name class.
	 *
	 * @since ??
	 *
	 * @param string $module_name_class The module name class.
	 *
	 * @return void
	 */
	public function set_module_name_class( string $module_name_class ): void {
		$this->module_name_class = $module_name_class;
	}

	/**
	 * Set the module order ID.
	 *
	 * @since ??
	 *
	 * @param string $order_id The order ID.
	 *
	 * @return void
	 */
	public function set_order_id( string $order_id ): void {
		$this->order_id = $order_id;
	}

	/**
	 * Set module script data.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string   $attrName        Optional. The attribute name declared in module.json config file. Default empty string.
	 *     @type array    $scriptDataProps Optional. A key-value pair array of script data props. Default `[]`.
	 *     @type callable $attrsResolver   Optional. A function that will be called to filter/resolve the attributes data. Default `null`.
	 * }
	 *
	 * @return void
	 */
	public function script_data( array $args ): void {
		$attr_name         = $args['attrName'] ?? '';
		$script_data_props = $args['scriptDataProps'] ?? [];
		$attrs_resolver    = $args['attrsResolver'] ?? null;
		$style_group       = $args['group'] ?? $this->_style_group;

		$merged_attrs    = $this->get_merged_attrs();
		$decoration_attr = $merged_attrs[ $attr_name ]['decoration'] ?? [];
		$settings        = $this->module_metadata->attributes[ $attr_name ] ?? [];

		$settings_script_data_props = $settings['scriptDataProps'] ?? [];

		$is_preset_style_group = 'preset' === $style_group;

		if ( $is_preset_style_group ) {
			// For preset style group, always use original order class.
			$setting_selector = $this->order_class;
		} else {
			// Get settings selector. Custom post type can have its own selector if the auto-prefixed selector is not
			// suitable compared to the default selector (eg. button module).
			$setting_selector = $this->_is_custom_post_type && isset( $settings['customPostTypeSelector'] )
				? $settings['customPostTypeSelector']
				: $settings['selector'] ?? '';
		}

		$element_selector = isset( $setting_selector ) ? ModuleElementsUtils::interpolate_selector(
			[
				'selectorTemplate' => $setting_selector,
				'value'            => $this->order_class,
			]
		) : '';

		if ( isset( $this->order_id ) ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->order_id,
					'placeholder'      => '{{orderId}}',
				]
			);
		}

		if ( $this->base_order_class ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->base_order_class,
					'placeholder'      => '{{baseSelector}}',
				]
			);
		}

		// There are various selectors, such as CPT prefixes and wrappers, used for styling that aren't needed for script data.
		// Instead of processing them all for no reason, we strip out the un-interpolated placeholders.
		// We just need the orderID or baseClass to target the correct element. CSS priority isn't important.
		$element_selector = preg_replace( '/\{\{[^}]+\}\}/', '', $element_selector );

		// TODO feat(D5, Module Attribute Refactor) Once link is merged as part of options property, remove this.
		if ( 'module' === $attr_name && ! isset( $decoration_attr['link'] ) ) {
			$link_attr = $this->module_attrs['module']['advanced']['link'] ?? [];

			if ( ! empty( $link_attr ) ) {
				$decoration_attr['link'] = $link_attr;
			}
		}

		$script_data_params = array_merge(
			[
				'id'            => $this->id,
				'selector'      => $element_selector,
				'attrs'         => $decoration_attr,

				// FE only.
				'storeInstance' => $this->store_instance,
			],
			// From module.json.
			$settings_script_data_props,
			// Overridden.
			$script_data_props
		);

		// If attrsResolver is provided, call it to filter/resolve the attributes.
		if ( is_callable( $attrs_resolver ) ) {
			$script_data_params['attrs'] = call_user_func( $attrs_resolver, $script_data_params['attrs'] ?? [] );
		}

		ElementScriptData::set( $script_data_params );
	}

	/**
	 * Set the style group which will be used to calculate the attributes data that will be used to render the style.
	 *
	 * @since ??
	 *
	 * @param string $group The style group.
	 *
	 * @return void
	 */
	public function set_style_group( string $group ) {
		$this->_style_group = $group;
	}

	/**
	 * Render style declaration.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $attrName   Optional. The attribute name declared in module.json config file. Default empty string.
	 *     @type array  $styleProps Optional. A key-value pair array of style props. Default `[]`.
	 *     @type string $group      Optional. The style group. This group will be used to calculate the attributes data that will be used to render the style. Default `module`.
	 * }
	 *
	 * @return string|array|null
	 */
	public function style( array $args ) {
		$attr_name   = $args['attrName'] ?? '';
		$style_props = $args['styleProps'] ?? [];
		$style_group = $args['group'] ?? $this->_style_group;

		// Merge the decoration attributes to ensure transitions are included.
		// The 'transition' attribute is critical for applying CSS transition properties
		// (e.g., duration, timing-function) defined in the module's decoration. By merging
		// it here, we ensure that transitions are consistently applied across modules that contains buttons.
		$decoration_attr = array_merge(
			$this->module_attrs[ $attr_name ]['decoration'] ?? [],
			[
				'transition' => $this->module_attrs['module']['decoration']['transition'] ?? [],
			]
		);
		$settings        = $this->module_metadata->attributes[ $attr_name ] ?? [];

		$is_preset_style_group       = 'preset' === $style_group;
		$is_preset_group_style_group = 'presetGroup' === $style_group;

		if ( $is_preset_style_group || $is_preset_group_style_group ) {
			// For preset style group, always use settings selector or original order class.
			$setting_selector = $settings['selector'] ?? $this->order_class;
		} else {
			// Get settings selector. Custom post type can have its own selector if the auto-prefixed selector is not
			// suitable compared to the default selector (eg. button module).
			$setting_selector = $this->_is_custom_post_type && isset( $settings['customPostTypeSelector'] )
				? $settings['customPostTypeSelector']
				: $settings['selector'] ?? '';
		}

		$element_selector = isset( $setting_selector ) ? ModuleElementsUtils::interpolate_selector(
			[
				'selectorTemplate' => $setting_selector,
				'value'            => $this->order_class,
			]
		) : '';

		if ( $this->base_order_class ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->base_order_class,
					'placeholder'      => '{{baseSelector}}',
				]
			);
		}

		if ( $this->base_wrapper_order_class ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->base_wrapper_order_class,
					'placeholder'      => '{{baseWrapperSelector}}',
				]
			);
		}

		if ( $this->wrapper_order_class ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->wrapper_order_class,
					'placeholder'      => '{{wrapperSelector}}',
				]
			);
		}

		if ( $this->module_name_class ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->_is_nested_module ? " .{$this->module_name_class} " : '',
					'placeholder'      => '{{nestedModuleNameSelector}}',
				]
			);
		}

		if ( $this->_is_custom_post_type ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => '.et-db #et-boc .et-l ',
					'placeholder'      => '{{selectorPrefix}}',
				]
			);
		} else {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => '',
					'placeholder'      => '{{selectorPrefix}}',
				]
			);
		}

		if ( isset( $this->order_id ) ) {
			$element_selector = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $element_selector,
					'value'            => $this->order_id,
					'placeholder'      => '{{orderId}}',
				]
			);
		}

		$settings_style_props = isset( $settings['styleProps'] ) ? ModuleElementsUtils::interpolate_selector(
			[
				'selectorTemplate' => $settings['styleProps'],
				'value'            => $this->order_class,
			]
		) : [];

		if ( $this->base_order_class ) {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => $this->base_order_class,
					'placeholder'      => '{{baseSelector}}',
				]
			);
		}

		if ( $this->base_wrapper_order_class ) {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => $this->base_wrapper_order_class,
					'placeholder'      => '{{baseWrapperSelector}}',
				]
			);
		}

		if ( $this->wrapper_order_class ) {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => $this->wrapper_order_class,
					'placeholder'      => '{{wrapperSelector}}',
				]
			);
		}

		if ( $this->module_name_class ) {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => $this->_is_nested_module ? " .{$this->module_name_class} " : '',
					'placeholder'      => '{{nestedModuleNameSelector}}',
				]
			);
		}

		if ( $this->_is_custom_post_type ) {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => '.et-db #et-boc .et-l ',
					'placeholder'      => '{{selectorPrefix}}',
				]
			);
		} else {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => '',
					'placeholder'      => '{{selectorPrefix}}',
				]
			);
		}

		if ( isset( $this->order_id ) ) {
			$settings_style_props = ModuleElementsUtils::interpolate_selector(
				[
					'selectorTemplate' => $settings_style_props,
					'value'            => $this->order_id,
					'placeholder'      => '{{orderId}}',
				]
			);
		}

		$settings_element_type = $settings['elementType'] ?? null;

		switch ( $settings_element_type ) {
			case 'element':
			case 'button':
				$settings_style_props['type'] = $settings_element_type;
				break;

			default:
				// Do nothing.
				break;
		}

		return ElementStyle::style(
			array_merge(
				[
					'attrs'                => $decoration_attr,
					'orderClass'           => $this->order_class, // Module orderClass.
					'selector'             => $element_selector,
					'isInsideStickyModule' => $this->_is_inside_sticky_module,
					'isParentLayoutFlex'   => $this->_is_parent_layout_flex,

					// We need to set `returnType` as `array` so that `Style::render` can reduce style-outputs by
					// combining styles based on declaration.
					'returnType'           => 'array',
				],
				// From module.json.
				$settings_style_props,
				// Overriden.
				$style_props
			)
		);
	}

	/**
	 * Set custom module attributes.
	 *
	 * This method is used to set custom module attributes that will be used in the current module instance.
	 *
	 * @param array $attrs An array of custom module attributes.
	 * @return void
	 */
	public function use_custom_module_attrs( array $attrs ) {
		$this->_module_attrs_original = $this->module_attrs;
		$this->module_attrs           = $attrs;
	}

	/**
	 * Clear custom module attributes.
	 *
	 * This method is used to clear custom module attributes that have been set using `use_custom_module_attrs` method.
	 *
	 * @return void
	 */
	public function clear_custom_attributes() {
		$this->module_attrs           = $this->_module_attrs_original;
		$this->_module_attrs_original = null;
	}

	/**
	 * Render element style components.
	 *
	 * @since ??
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $attrName             Optional. The attribute name declared in module.json config file. Default empty string.
	 *     @type array  $styleComponentsProps Optional. A key-value pair array of component props. Default `[]`.
	 * }
	 *
	 * @return string|null
	 */
	public function style_components( array $args ) {
		$attr_name       = $args['attrName'] ?? '';
		$component_props = $args['styleComponentsProps'] ?? [];

		$merged_attrs    = $this->get_merged_attrs();
		$decoration_attr = $merged_attrs[ $attr_name ]['decoration'] ?? [];
		$settings        = $this->module_metadata->attributes[ $attr_name ] ?? [];

		$settings_component_props = $settings['styleComponentsProps'] ?? [];

		return ElementComponents::component(
			array_merge(
				[
					'id'            => $this->id,
					'attrs'         => $decoration_attr,

					// FE Only.
					'orderIndex'    => $this->order_index,
					'storeInstance' => $this->store_instance,
				],
				// From module.json.
				$settings_component_props,
				// Overridden.
				$component_props
			)
		);
	}

	/**
	 * Merges module attributes with preset and group preset attributes.
	 *
	 * This method retrieves and merges attributes from a specified module,
	 * its selected preset, and any applicable group presets.
	 *
	 * @since ??
	 *
	 * @return array The merged attributes array.
	 */
	public function get_merged_attrs():array {
		if ( is_array( $this->_merged_attrs ) ) {
			return $this->_merged_attrs;
		}

		$this->_merged_attrs = GlobalPreset::get_merged_attrs(
			[
				'moduleName'  => $this->name,
				'moduleAttrs' => $this->_module_attrs_original ?? $this->module_attrs,
			]
		);

		return $this->_merged_attrs;
	}
}
