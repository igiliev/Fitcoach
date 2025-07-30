<?php
/**
 * HTMLUtility class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Utility;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\VisualBuilder\Saving\SavingUtility;
use ET\Builder\Framework\Utility\SanitizerUtility;
use ET_Builder_Module_Shortcode_Manager;
use ET_Theme_Builder_Layout;

/**
 * HTMLUtility class.
 *
 * This class contains methods to work with HTML elements.
 *
 * @since ??
 */
class HTMLUtility {

	/**
	 * A simple utility for conditionally joining classNames together.
	 *
	 * This function is equivalent of JS function `classnames` by JedWatson.
	 * With a slight difference that this function will account a class considered as valid
	 * when it contains at least single lowercase letter character ([a-z]).
	 * This is intended to make this function more predictable.
	 *
	 * @link https://github.com/JedWatson/classnames
	 *
	 * @since ??
	 *
	 * @param string|string[] ...$args The class names to be merged.
	 *
	 * @return string Unique and trimmed class names, or empty if no class names found.
	 */
	public static function classnames( ...$args ) {
		$classnames = [];

		foreach ( $args as $arg ) {
			if ( is_string( $arg ) && preg_match( '/[a-z]/i', $arg ) ) { // $arg is the class.
					$classnames[ trim( $arg ) ] = true;
			} elseif ( is_array( $arg ) ) {
				foreach ( $arg as $key => $value ) {
					if ( preg_match( '/[a-z]/i', $key ) ) { // Array key is the class.
						$classnames[ trim( $key ) ] = ! ! $value;
					} elseif ( is_string( $value ) && preg_match( '/[a-z]/i', $value ) ) { // Array value is the class.
						$classnames[ trim( $value ) ] = true;
					}
				}
			}
		}

		if ( $classnames ) {
			$filtered = [];

			foreach ( $classnames as $class_name => $bool ) {
				if ( $bool ) {
					$filtered[] = $class_name;
				}
			}

			$filtered = implode( ' ', $filtered );

			if ( false !== strpos( $filtered, '  ' ) ) {
				// Transform "multiple whitespace" -> "single whitespace".
				$filtered = preg_replace( '/\s+/', ' ', $filtered );
			}

			return trim( $filtered );
		}

		return '';
	}

	/**
	 * Get all HTML element attributes.
	 *
	 * This function merges the values of `HTMLUtility::get_event_handler_attributes()`,
	 * `HTMLUtility::get_fixed_name_attributes()` and `HTMLUtility::get_wildcard_name_attributes()`.
	 *
	 * @link https://html.spec.whatwg.org/multipage/indices.html#attributes-1
	 *
	 * @since ??
	 *
	 * @return array {
	 *   A key-value pair array with the attribute name as the key and the attribute details as the value.
	 *
	 *   @type array $attribute {
	 *     Attribute details.
	 *
	 *     @type array    $elements         List of elements where the attribute can be used. Will be empty
	 *                                      when the is global attribute and can be used to any elements.
	 *     @type bool     $booleanAttribute Optional. Boolean attribute flag. Default `false`.
	 *     @type callable $sanitizer        Optional. Function that will be used to sanitize the attribute value.
	 *                                      Only applicable for key-value pair attributes. Default `esc_attr` or `esc_js`.
	 *     }
	 * }
	 **/
	public static function get_all_attributes() {
		return array_merge(
			self::get_event_handler_attributes(),
			self::get_fixed_name_attributes(),
			self::get_wildcard_name_attributes()
		);
	}

	/**
	 * Get HTML element attribute details.
	 *
	 * @since ??
	 *
	 * @param string $attribute The attribute name.
	 *
	 * @return array {
	 *        Attribute details. Will return empty array on failure.
	 *
	 *        @type array    $elements         List of elements where the attribute can be used. Will be empty
	 *                                         when the is global attribute and can be used to any elements.
	 *        @type bool     $booleanAttribute Optional. Boolean attribute flag. Attribute can be used to any elements. Default `false`.
	 *        @type callable $sanitizer        Optional. Function that will be used to sanitize the attribute value. Default `esc_attr`.
	 * }
	 **/
	public static function get_attribute_details( $attribute ) {
		// Normalize data-* attributes.
		if ( 'data-*' !== $attribute && 0 === strpos( $attribute, 'data-' ) ) {
			$attribute = 'data-*';
		}

		// Normalize aria-* attributes.
		if ( 'aria-*' !== $attribute && 0 === strpos( $attribute, 'aria-' ) ) {
			$attribute = 'aria-*';
		}

		$all_attributes = self::get_all_attributes();

		if ( isset( $all_attributes[ $attribute ] ) ) {
			$details = $all_attributes[ $attribute ];

			if ( ! isset( $details['booleanAttribute'] ) ) {
				$details['booleanAttribute'] = false;
			}

			if ( true !== $details['booleanAttribute'] && ! isset( $details['sanitizer'] ) ) {
				$details['sanitizer'] = 'esc_attr';
			}

			return $details;
		}

		return [];
	}

	/**
	 * Get HTML element event handler attributes.
	 *
	 * @link https://html.spec.whatwg.org/multipage/indices.html#attributes-1
	 *
	 * @since ??
	 *
	 * @return array {
	 *   A key-value pair array with the attribute name as the key and the attribute details as the value.
	 *
	 *   @type array $attribute {
	 *     Attribute details.
	 *
	 *     @type array    $elements         List of HTML elements where the attribute can be used. Will be empty
	 *                                      when the is global attribute and can be used to any elements.
	 *     @type bool     $booleanAttribute Optional. Boolean attribute flag. Default `false`.
	 *     @type callable $sanitizer        Optional. Function that will be used to sanitize the attribute value.
	 *                                      Only applicable for key-value pair attributes. Default `esc_js`.
	 *    }
	 * }
	 **/
	public static function get_event_handler_attributes() {
		return [
			'onauxclick'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onafterprint'              => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onbeforematch'             => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onbeforeprint'             => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onbeforeunload'            => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onblur'                    => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncancel'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncanplay'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncanplaythrough'          => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onchange'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onclick'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onclose'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncontextlost'             => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncontextmenu'             => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncontextrestored'         => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncopy'                    => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncuechange'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oncut'                     => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondblclick'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondrag'                    => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondragend'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondragenter'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondragleave'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondragover'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondragstart'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondrop'                    => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ondurationchange'          => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onemptied'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onended'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onerror'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onfocus'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onformdata'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onhashchange'              => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'oninput'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'oninvalid'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onkeydown'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onkeypress'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onkeyup'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onlanguagechange'          => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onload'                    => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onloadeddata'              => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onloadedmetadata'          => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onloadstart'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmessage'                 => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onmessageerror'            => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onmousedown'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmouseenter'              => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmouseleave'              => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmousemove'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmouseout'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmouseover'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onmouseup'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onoffline'                 => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'ononline'                  => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onpagehide'                => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onpageshow'                => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onpaste'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onpause'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onplay'                    => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onplaying'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onpopstate'                => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onprogress'                => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onratechange'              => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onreset'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onresize'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onrejectionhandled'        => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onscroll'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onscrollend'               => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onsecuritypolicyviolation' => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onseeked'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onseeking'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onselect'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onslotchange'              => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onstalled'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onstorage'                 => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onsubmit'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onsuspend'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ontimeupdate'              => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'ontoggle'                  => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onunhandledrejection'      => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onunload'                  => [
				'elements'  => [ 'body' ],
				'sanitizer' => 'esc_js',
			],
			'onvolumechange'            => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onwaiting'                 => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
			'onwheel'                   => [
				'elements'  => [],
				'sanitizer' => 'esc_js',
			],
		];
	}

	/**
	 * Get fixed name attributes.
	 *
	 * @link https://html.spec.whatwg.org/multipage/indices.html#attributes-1
	 *
	 * @since ??
	 *
	 * @return array {
	 *   A key-value pair array with the attribute name as the key and the attribute details as the value.
	 *
	 *   @type array $attribute {
	 *     Attribute details.
	 *
	 *     @type array    $elements         List of HTML elements where the attribute can be used. Will be empty
	 *                                      when the is global attribute and can be used to any elements.
	 *     @type bool     $booleanAttribute Optional. Boolean attribute flag. Default `false`.
	 *     @type callable $sanitizer        Optional. Function that will be used to sanitize the attribute value.
	 *                                      Only applicable for key-value pair attributes. Default `esc_attr`.
	 *    }
	 * }
	 **/
	public static function get_fixed_name_attributes() {
		return [
			'abbr'            => [
				'elements'  => [ 'th' ],
				'sanitizer' => 'esc_attr',
			],
			'accept'          => [
				'elements'  => [ 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'accept-charset'  => [
				'elements'  => [ 'form' ],
				'sanitizer' => 'esc_attr',
			],
			'accesskey'       => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'action'          => [
				'elements'  => [ 'form' ],
				'sanitizer' => 'esc_url',
			],
			'allow'           => [
				'elements'  => [ 'iframe' ],
				'sanitizer' => 'esc_attr',
			],
			'allowfullscreen' => [
				'elements'         => [ 'iframe' ],
				'booleanAttribute' => true,
			],
			'alt'             => [
				'elements'  => [ 'area', 'img', 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'as'              => [
				'elements'  => [ 'link' ],
				'sanitizer' => 'esc_attr',
			],
			'async'           => [
				'elements'         => [ 'script' ],
				'booleanAttribute' => true,
			],
			'autocapitalize'  => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'autocomplete'    => [
				'elements'  => [ 'form', 'input', 'select', 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'autofocus'       => [
				'elements'         => [],
				'booleanAttribute' => true,
			],
			'autoplay'        => [
				'elements'         => [ 'audio', 'video' ],
				'booleanAttribute' => true,
			],
			'blocking'        => [
				'elements'  => [ 'link', 'script', 'style' ],
				'sanitizer' => 'esc_attr',
			],
			'charset'         => [
				'elements'  => [ 'meta' ],
				'sanitizer' => 'esc_attr',
			],
			'checked'         => [
				'elements'         => [ 'input' ],
				'booleanAttribute' => true,
			],
			'cite'            => [
				'elements'  => [ 'blockquote', 'del', 'ins', 'q' ],
				'sanitizer' => 'esc_url',
			],
			'class'           => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'color'           => [
				'elements'  => [ 'link' ],
				'sanitizer' => 'esc_attr',
			],
			'cols'            => [
				'elements'  => [ 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'colspan'         => [
				'elements'  => [ 'td', 'th' ],
				'sanitizer' => 'esc_attr',
			],
			'content'         => [
				'elements'  => [ 'meta' ],
				'sanitizer' => 'esc_attr',
			],
			'contenteditable' => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'controls'        => [
				'elements'         => [ 'audio', 'video' ],
				'booleanAttribute' => true,
			],
			'coords'          => [
				'elements'  => [ 'area' ],
				'sanitizer' => 'esc_attr',
			],
			'crossorigin'     => [
				'elements'  => [ 'audio', 'img', 'link', 'script', 'video' ],
				'sanitizer' => 'esc_attr',
			],
			'data'            => [
				'elements'  => [ 'object' ],
				'sanitizer' => 'esc_url',
			],
			'datetime'        => [
				'elements'  => [ 'del', 'ins', 'time' ],
				'sanitizer' => 'esc_attr',
			],
			'decoding'        => [
				'elements'  => [ 'img' ],
				'sanitizer' => 'esc_attr',
			],
			'default'         => [
				'elements'         => [ 'track' ],
				'booleanAttribute' => true,
			],
			'defer'           => [
				'elements'         => [ 'script' ],
				'booleanAttribute' => true,
			],
			'dir'             => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'dirname'         => [
				'elements'  => [ 'input', 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'disabled'        => [
				'elements'         => [
					'button',
					'input',
					'optgroup',
					'option',
					'select',
					'textarea',
					'fieldset',
					'link',
				],
				'booleanAttribute' => true,
			],
			'download'        => [
				'elements'  => [ 'a', 'area' ],
				'sanitizer' => 'esc_attr',
			],
			'draggable'       => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'enctype'         => [
				'elements'  => [ 'form' ],
				'sanitizer' => 'esc_attr',
			],
			'enterkeyhint'    => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'for'             => [
				'elements'  => [ 'label', 'output' ],
				'sanitizer' => 'esc_attr',
			],
			'form'            => [
				'elements'  => [
					'button',
					'fieldset',
					'input',
					'object',
					'output',
					'select',
					'textarea',
				],
				'sanitizer' => 'esc_attr',
			],
			'formaction'      => [
				'elements'  => [ 'button', 'input' ],
				'sanitizer' => 'esc_url',
			],
			'formenctype'     => [
				'elements'  => [ 'button', 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'formmethod'      => [
				'elements'  => [ 'button', 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'formnovalidate'  => [
				'elements'         => [ 'button', 'input' ],
				'booleanAttribute' => true,
			],
			'formtarget'      => [
				'elements'  => [ 'button', 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'headers'         => [
				'elements'  => [ 'td', 'th' ],
				'sanitizer' => 'esc_attr',
			],
			'height'          => [
				'elements'  => [
					'canvas',
					'embed',
					'iframe',
					'img',
					'input',
					'object',
					'source',
					'picture',
					'video',
				],
				'sanitizer' => 'esc_attr',
			],
			'hidden'          => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'high'            => [
				'elements'  => [ 'meter' ],
				'sanitizer' => 'esc_attr',
			],
			'href'            => [
				'elements'  => [ 'a', 'area', 'link', 'base' ],
				'sanitizer' => 'esc_url',
			],
			'hreflang'        => [
				'elements'  => [ 'a', 'link' ],
				'sanitizer' => 'esc_attr',
			],
			'http-equiv'      => [
				'elements'  => [ 'meta' ],
				'sanitizer' => 'esc_attr',
			],
			'id'              => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'imagesizes'      => [
				'elements'  => [ 'link' ],
				'sanitizer' => 'esc_attr',
			],
			'imagesrcset'     => [
				'elements'  => [ 'link' ],
				'sanitizer' => 'esc_attr',
			],
			'inert'           => [
				'elements'         => [],
				'booleanAttribute' => true,
			],
			'inputmode'       => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'integrity'       => [
				'elements'  => [ 'link', 'script' ],
				'sanitizer' => 'esc_attr',
			],
			'is'              => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'ismap'           => [
				'elements'         => [ 'img' ],
				'booleanAttribute' => true,
			],
			'itemid'          => [
				'elements'  => [],
				'sanitizer' => 'esc_url',
			],
			'itemprop'        => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'itemref'         => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'itemscope'       => [
				'elements'         => [],
				'booleanAttribute' => true,
			],
			'itemtype'        => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'kind'            => [
				'elements'  => [ 'track' ],
				'sanitizer' => 'esc_attr',
			],
			'label'           => [
				'elements'  => [ 'optgroup', 'option', 'track' ],
				'sanitizer' => 'esc_attr',
			],
			'lang'            => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'list'            => [
				'elements'  => [ 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'loading'         => [
				'elements'  => [ 'iframe', 'img' ],
				'sanitizer' => 'esc_attr',
			],
			'loop'            => [
				'elements'         => [ 'audio', 'video' ],
				'booleanAttribute' => true,
			],
			'low'             => [
				'elements'  => [ 'meter' ],
				'sanitizer' => 'esc_attr',
			],
			'max'             => [
				'elements'  => [ 'input', 'meter', 'progress' ],
				'sanitizer' => 'esc_attr',
			],
			'maxlength'       => [
				'elements'  => [ 'input', 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'media'           => [
				'elements'  => [ 'link', 'meta', 'source', 'picture', 'style' ],
				'sanitizer' => 'esc_attr',
			],
			'method'          => [
				'elements'  => [ 'form' ],
				'sanitizer' => 'esc_attr',
			],
			'min'             => [
				'elements'  => [ 'input', 'meter' ],
				'sanitizer' => 'esc_attr',
			],
			'minlength'       => [
				'elements'  => [ 'input', 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'multiple'        => [
				'elements'         => [ 'input', 'select' ],
				'booleanAttribute' => true,
			],
			'muted'           => [
				'elements'         => [ 'audio', 'video' ],
				'booleanAttribute' => true,
			],
			'name'            => [
				'elements'  => [
					'button',
					'fieldset',
					'input',
					'output',
					'select',
					'textarea',
					'form',
					'iframe',
					'object',
					'map',
					'meta',
					'slot',
				],
				'sanitizer' => 'esc_attr',
			],
			'nomodule'        => [
				'elements'         => [ 'script' ],
				'booleanAttribute' => true,
			],
			'nonce'           => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'novalidate'      => [
				'elements'         => [ 'form' ],
				'booleanAttribute' => true,
			],
			'open'            => [
				'elements'         => [ 'details', 'dialog' ],
				'booleanAttribute' => true,
			],
			'optimum'         => [
				'elements'  => [ 'meter' ],
				'sanitizer' => 'esc_attr',
			],
			'pattern'         => [
				'elements'  => [ 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'ping'            => [
				'elements'  => [ 'a', 'area' ],
				'sanitizer' => 'esc_attr',
			],
			'placeholder'     => [
				'elements'  => [ 'input', 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'playsinline'     => [
				'elements'         => [ 'video' ],
				'booleanAttribute' => true,
			],
			'poster'          => [
				'elements'  => [ 'video' ],
				'sanitizer' => 'esc_url',
			],
			'preload'         => [
				'elements'  => [ 'audio', 'video' ],
				'sanitizer' => 'esc_attr',
			],
			'readonly'        => [
				'elements'         => [ 'input', 'textarea' ],
				'booleanAttribute' => true,
			],
			'referrerpolicy'  => [
				'elements'  => [ 'a', 'area', 'iframe', 'img', 'link', 'script' ],
				'sanitizer' => 'esc_attr',
			],
			'rel'             => [
				'elements'  => [ 'a', 'area', 'link' ],
				'sanitizer' => 'esc_attr',
			],
			'required'        => [
				'elements'         => [ 'input', 'select', 'textarea' ],
				'booleanAttribute' => true,
			],
			'reversed'        => [
				'elements'         => [ 'ol' ],
				'booleanAttribute' => true,
			],
			'role'            => [
				'elements'         => [],
				'globalAttribute'  => true,
				'booleanAttribute' => false,
				'sanitizer'        => 'esc_attr',
			],
			'rows'            => [
				'elements'  => [ 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
			'rowspan'         => [
				'elements'  => [ 'td', 'th' ],
				'sanitizer' => 'esc_attr',
			],
			'sandbox'         => [
				'elements'  => [ 'iframe' ],
				'sanitizer' => 'esc_attr',
			],
			'scope'           => [
				'elements'  => [ 'th' ],
				'sanitizer' => 'esc_attr',
			],
			'selected'        => [
				'elements'         => [ 'option' ],
				'booleanAttribute' => true,
			],
			'shape'           => [
				'elements'  => [ 'area' ],
				'sanitizer' => 'esc_attr',
			],
			'size'            => [
				'elements'  => [ 'input', 'select' ],
				'sanitizer' => 'esc_attr',
			],
			'sizes'           => [
				'elements'  => [ 'link', 'img', 'source' ],
				'sanitizer' => 'esc_attr',
			],
			'slot'            => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'span'            => [
				'elements'  => [ 'col', 'colgroup' ],
				'sanitizer' => 'esc_attr',
			],
			'spellcheck'      => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'src'             => [
				'elements'  => [
					'audio',
					'embed',
					'iframe',
					'img',
					'input',
					'script',
					'source',
					'video',
					'audio',
					'track',
					'video',
				],
				'sanitizer' => 'esc_url',
			],
			'srcdoc'          => [
				'elements'  => [ 'iframe' ],
				'sanitizer' => 'esc_attr',
			],
			'srclang'         => [
				'elements'  => [ 'track' ],
				'sanitizer' => 'esc_attr',
			],
			'srcset'          => [
				'elements'  => [ 'img', 'source' ],
				'sanitizer' => 'esc_attr',
			],
			'start'           => [
				'elements'  => [ 'ol' ],
				'sanitizer' => 'esc_attr',
			],
			'step'            => [
				'elements'  => [ 'input' ],
				'sanitizer' => 'esc_attr',
			],
			'style'           => [
				'elements'  => [],
				'sanitizer' => function( $value ) {
					return SavingUtility::sanitize_css_properties( $value );
				},
			],
			'tabindex'        => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'target'          => [
				'elements'  => [ 'a', 'area', 'base', 'form' ],
				'sanitizer' => 'esc_attr',
			],
			'title'           => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'translate'       => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'type'            => [
				'elements'  => [
					'a',
					'link',
					'button',
					'embed',
					'object',
					'source',
					'input',
					'ol',
					'script',
				],
				'sanitizer' => 'esc_attr',
			],
			'usemap'          => [
				'elements'  => [ 'img' ],
				'sanitizer' => 'esc_attr',
			],
			'value'           => [
				'elements'  => [
					'button',
					'option',
					'data',
					'input',
					'li',
					'meter',
					'progress',
				],
				'sanitizer' => 'esc_attr',
			],
			'width'           => [
				'elements'  => [
					'canvas',
					'embed',
					'iframe',
					'img',
					'input',
					'object',
					'source',
					'picture',
					'video',
				],
				'sanitizer' => 'esc_attr',
			],
			'wrap'            => [
				'elements'  => [ 'textarea' ],
				'sanitizer' => 'esc_attr',
			],
		];
	}

	/**
	 * Get Wildcard name attributes.
	 *
	 * @link https://html.spec.whatwg.org/multipage/indices.html#attributes-1
	 *
	 * @since ??
	 *
	 * @return array {
	 *   A key-value pair array with the attribute name as the key and the attribute details as the value.
	 *
	 *   @type array $attribute {
	 *     Attribute details.
	 *
	 *     @type array    $elements         List of HTML elements where the attribute can be used. Will be empty
	 *                                          when the is global attribute and can be used to any elements.
	 *     @type bool     $booleanAttribute Optional. Boolean attribute flag. Default `false`.
	 *     @type callable $sanitizer        Optional. Function that will be used to sanitize the attribute value.
	 *                                      Only applicable for key-value pair attributes. Default `esc_attr`.
	 *     }
	 * }
	 **/
	public static function get_wildcard_name_attributes() {
		return [
			'data-*' => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
			'aria-*' => [
				'elements'  => [],
				'sanitizer' => 'esc_attr',
			],
		];
	}

	/**
	 * Check whether an HTML attribute is valid or not.
	 *
	 * @since ??
	 *
	 * @param string $attribute The attribute name.
	 * @param string $tag       The HTML element tag where the attributes will be used.
	 *
	 * @return array|false The attribute details on success or `false` on failure.
	 **/
	public static function is_valid_attribute( $attribute, $tag ) {
		static $cache = [];

		if ( isset( $cache[ $tag ][ $attribute ] ) ) {
			return $cache[ $tag ][ $attribute ];
		}

		$details = self::get_attribute_details( $attribute );

		if ( ! $details ) {
			$details = false;
		}

		if ( $details && ! empty( $details['elements'] ) && ! in_array( $tag, $details['elements'], true ) ) {
			$details = false;
		}

		// Set custom sanitizer for `src` attribute of `img` element to allow `data:image` value.
		if ( $details && 'src' === $attribute && 'img' === $tag ) {
			$details['sanitizer'] = function( $value ) {
				return SanitizerUtility::sanitize_image_src( $value );
			};
		}

		if ( ! isset( $cache[ $tag ] ) ) {
			$cache[ $tag ] = [];
		}

		$cache[ $tag ][ $attribute ] = $details;

		return $details;
	}

	/**
	 * Render HTML attributes.
	 *
	 * @since ??
	 *
	 * @param array  $attributes A key-value pair array of attributes data.
	 *                           The array item key must be a string.
	 *                           For boolean attributes, the array item value must be `true`.
	 *                           For key-value pair attributes, the array item value must be an `int`, `float`, `string`, `boolean`, `array` or `null`.
	 *                             `boolean`  value will be stringified to avoid `true` getting printed as `1` and `false` getting printed as `0`.
	 *                             `array` value is only applicable for `style` attribute.
	 *                             `null` value will result in the attribute not being rendered.
	 * @param string $tag        The HTML element tag where the attributes will be used.
	 * @param array  $sanitizers Optional. A key-value pair array of custom sanitizers that will be used to override the default sanitizer.
	 *                           The array key is the attribute name and the array value is the callable function.
	 *                           Only applicable for key-value pair attributes. Default `[]`.
	 *
	 * @return string
	 **/
	public static function render_attributes( $attributes, $tag, $sanitizers = [] ) {
		$output = [];

		foreach ( $attributes as $key => $value ) {
			if ( ! is_string( $key ) || null === $value ) {
				continue;
			}

			$is_valid = self::is_valid_attribute( $key, $tag );

			if ( ! $is_valid ) {
				continue;
			}

			if ( true === ( $is_valid['booleanAttribute'] ?? false ) ) {  // Boolean attributes.
				if ( true === $value ) {
					$output[] = esc_html( $key );
				}
			} else {  // Key-value pair attributes.
				$default_sanitizer = isset( $is_valid['sanitizer'] ) && is_callable( $is_valid['sanitizer'] ) ? $is_valid['sanitizer'] : 'esc_attr';
				$sanitizer         = isset( $sanitizers[ $key ] ) && is_callable( $sanitizers[ $key ] ) ? $sanitizers[ $key ] : $default_sanitizer;

				if ( 'style' === $key && is_array( $value ) ) {
					$output[] = 'style="' . self::render_styles( $value, $sanitizer ) . '"';
				} elseif ( 'class' === $key && is_array( $value ) ) {
					$output[] = 'class="' . call_user_func( $sanitizer, self::classnames( $value ) ) . '"';
				} elseif ( is_scalar( $value ) ) {
					// Stringify boolean to avoid `true` get printed as `1` and `false` get printed as `0`.
					if ( is_bool( $value ) ) {
						$value = $value ? 'true' : 'false';
					}

					$output[] = esc_html( $key ) . '="' . call_user_func( $sanitizer, $value ) . '"';
				}
			}
		}

		return implode( ' ', $output );
	}

	/**
	 * Render element styles.
	 *
	 * @since ??
	 *
	 * @param array    $styles    A key-value pair array of styles data.
	 *                            The array key is the style property name.
	 *                            The array value is the style property value which must be a `string` or `null`.
	 *                            Note: `null` result in the style property being skipped.
	 * @param callable $sanitizer Optional. A callable function that will be used to override the default sanitizer. Default `null`.
	 *
	 * @return string
	 **/
	public static function render_styles( $styles, $sanitizer = null ) {
		$output = [];

		foreach ( $styles as $key => $value ) {
			if ( null === $value || ! is_scalar( $value ) ) {
				continue;
			}

			// Stringify boolean to avoid `true` getting printed as `1` and `false` getting printed as `0`.
			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			}

			$output[] = $key . ':' . $value;
		}

		$output = implode( ';', $output );

		if ( '' === $output ) {
			return '';
		}

		if ( $sanitizer && is_callable( $sanitizer ) ) {
			return call_user_func( $sanitizer, $output );
		}

		return SavingUtility::sanitize_css_properties( $output );
	}

	// TODO feat(D5, Frontend Rendering) Need improvement to be able to render `svg` element properly.
	/**
	 * Render HTML element.
	 *
	 * @since ??
	 *
	 * @param array $props {
	 *     An array of arguments.
	 *
	 *     @type string       $tag                   HTML Element tag.
	 *     @type bool         $tagEscaped            Optional. Whether the tag name has been escaped or not. Default `false`.
	 *     @type array        $attributesSanitizers  Optional. A key-value pair array of custom sanitizers that will be used to override the default sanitizer.
	 *                                               The array key is the attribute name and the array value is the callable function.
	 *                                               Only applicable for key-value pair attributes.
	 *                                               Default `[]`.
	 *     @type array        $attributes            Optional. A key-value pair array of attributes data.
	 *                                               The array item key must be a string.
	 *                                               For boolean attributes, the array item value must be `true`.
	 *                                               For key-value pair attributes, the array item value must be a `int`, `float`, `string`, `boolean`, `array` or `null`.
	 *                                                 `boolean`  value will be stringified to avoid `true` getting printed as `1` and `false` getting printed as `0`.
	 *                                                 `array` value only applicable for `style` attribute.
	 *                                                 `null` value will skip the attribute to be rendered.
	 *     @type callable     $childrenSanitizer    Optional. The function that will be invoked to sanitize/escape the children element. Default `esc_html`.
	 *     @type string|array $children             Optional. The children element. Default `null`.
	 *                                              Pass string for single children element.
	 *                                              Pass array for multiple children elements and nested children elements.
	 *                                              Only applicable for non self-closing tags.
	 * }
	 *
	 * @return string
	 **/
	public static function render( $props ) {
		$attributes            = $props['attributes'] ?? [];
		$attributes_sanitizers = $props['attributesSanitizers'] ?? [];
		$children              = $props['children'] ?? null;
		$children_sanitizer    = $props['childrenSanitizer'] ?? 'esc_html';
		$tag                   = $props['tag'] ?? '';
		$tag_escaped           = $props['tagEscaped'] ?? false;

		if ( ! $tag_escaped ) {
			$tag = tag_escape( $tag );
		}

		if ( ! is_string( $tag ) || ! $tag ) {
			return '';
		}

		if ( self::is_self_closing_tag( $tag ) ) {
			$attributes = self::render_attributes( $attributes, $tag, $attributes_sanitizers );

			return strtr(
				'<{{tag}}{{attributes}} />',
				[
					'{{tag}}'        => $tag, // Escaped using `tag_escape` above.
					'{{attributes}}' => $attributes ? ' ' . et_core_esc_previously( $attributes ) : '', // Escaped in HTMLUtility::render_attributes.
				]
			);
		}

		if ( is_array( $children ) ) {
			if ( isset( $children['tag'] ) ) {
				$children = self::render( $children );
			} else {
				$children_concat = '';

				foreach ( $children as $child ) {
					if ( is_string( $child ) ) {
						$children_concat .= $child;
					} elseif ( is_array( $child ) && isset( $child['tag'] ) ) {
						$children_concat .= self::render( $child );
					}
				}

				$children = $children_concat;
			}
		}

		$attributes = self::render_attributes( $attributes, $tag, $attributes_sanitizers );

		return strtr(
			'<{{tag}}{{attributes}}>{{children}}</{{tag}}>',
			[
				'{{tag}}'        => $tag, // Escaped using `tag_escape` above.
				'{{attributes}}' => $attributes ? ' ' . et_core_esc_previously( $attributes ) : '', // Escaped in HTMLUtility::render_attributes.
				'{{children}}'   => is_string( $children ) ? call_user_func( $children_sanitizer, $children ) : '',
			]
		);

	}

	/**
	 * List of HTML Self-Closing Tags
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_self_closing_tags = [
		'area',
		'base',
		'br',
		'col',
		'command',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'menuitem',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
	];

	/**
	 * Check whether an HTML tag is self-closing or not.
	 *
	 * @since ??
	 *
	 * @param string $tag The HTML element tag to be checked.
	 *
	 * @return bool `true` if the tag is self-closing, `false` otherwise.
	 **/
	public static function is_self_closing_tag( string $tag ):bool {
		static $cached = [];

		if ( isset( $cached[ $tag ] ) ) {
			return $cached[ $tag ];
		}

		$cached[ $tag ] = in_array( $tag, self::$_self_closing_tags, true );

		return $cached[ $tag ];
	}

	/**
	 * List of HTML Self-Closing Tags mapped to their required attributes.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_self_closing_tag_required_attrs_mapping = [
		/**
		 * <area>: The Image Map Area element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/area#attributes
		 */
		'area'   => [
			'attributes'  => [
				'shape'  => [],
				'coords' => [],
			],
			'requiredAll' => false,
		],
		/**
		 * <base>: The Document Base URL element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/base#attributes
		 */
		'base'   => [
			'attributes'  => [
				'href'   => [],
				'target' => [],
			],
			'requiredAll' => false,
		],
		/**
		 * <col>: The Table Column element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/col#attributes
		 */
		'col'    => [
			'attributes' => [
				'span' => [],
			],
		],
		/**
		 * <embed>: The Embed External Content element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/embed#attributes
		 */
		'embed'  => [
			'attributes' => [
				'src' => [],
			],
		],
		/**
		 * <img>: The Image Embed element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attributes
		 */
		'img'    => [
			'attributes' => [
				'src' => [],
			],
		],
		/**
		 * <input>: The Input (Form Input) element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attributes
		 */
		'input'  => [
			'attributes' => [
				'type' => [],
			],
		],
		/**
		 * <link>: The External Resource Link element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link#attributes
		 */
		'link'   => [
			'attributes' => [
				'href' => [],
			],
		],
		/**
		 * <meta>: The metadata element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta#attributes
		 */
		'meta'   => [
			'attributes' => [
				'name'    => [],
				'content' => [],
			],
		],
		/**
		 * <param>: The Object Parameter element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/param#attributes
		 */
		'param'  => [
			'attributes' => [
				'name' => [],
			],
		],
		/**
		 * <source>: The Media or Image Source element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/source#attributes
		 */
		'source' => [
			'attributes' => [
				'src'    => [ 'audio', 'video' ], // The `src` attribute is required if the parent tag is `audio` or `video`.
				'srcset' => [ 'picture' ], // The `srcset` attribute is required if the parent tag is `picture`.
			],
		],
		/**
		 * <track>: The Embed Text Track element
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/track#attributes
		 */
		'track'  => [
			'attributes' => [
				'src' => [],
			],
		],
	];

	/**
	 * Get list of attributes required for certain self-closing HTML tags.
	 *
	 * These tags include track, source, param, meta, link, input, img, embed, col, area, and base.
	 *
	 * @since ??
	 *
	 * @param string $tag        The self-closing HTML tag to be checked.
	 * @param string $parent_tag Optional. The parent HTML element tag where this element will be rendered.
	 *                           It is used for certain self-closing tags that need to know the parent tag in order to retrieve
	 *                           the required attributes list, such as the source tag. Default empty string.
	 *
	 * @return array {
	 *   A key-value pair array with the attribute name as the key and the attribute details as the value.
	 *   Will return an empty array on failure.
	 *
	 *   @type array $attributes  List of required attributes.
	 *   @type bool  $requiredAll Whether all attributes are required or not.
	 * }
	 **/
	public static function get_self_closing_tag_required_attrs( string $tag, string $parent_tag = '' ):array {
		static $cached = [];

		if ( isset( $cached[ $tag ][ $parent_tag ] ) ) {
			return $cached[ $tag ][ $parent_tag ];
		}

		$result = [];

		if ( isset( self::$_self_closing_tag_required_attrs_mapping[ $tag ] ) ) {
			$mapping = self::$_self_closing_tag_required_attrs_mapping[ $tag ];

			foreach ( $mapping['attributes'] as $attribute => $parent ) {
				if ( $parent && ! in_array( $parent_tag, $parent, true ) ) {
					continue;
				}

				$result[] = $attribute;
			}

			if ( $result ) {
				$result = [
					'attributes'  => $result,
					'requiredAll' => $mapping['requiredAll'] ?? true,
				];
			}
		}

		if ( ! isset( $cached[ $tag ] ) ) {
			$cached[ $tag ] = [];
		}

		$cached[ $tag ][ $parent_tag ] = $result;

		return $result;
	}

	/**
	 * Convert line breaks.
	 *
	 * It converts line breaks to `<br>` or `\n`.
	 *
	 * @since ??
	 *
	 * @param string $content            Content.
	 * @param string $line_breaks_format Line break format e.g `<br>` or `\n`.
	 *
	 * @return string Processed content.
	 */
	public static function convert_line_breaks( string $content, string $line_breaks_format = "\n" ): string {
		// Before we swap out the placeholders, remove `<p>` and `\n` that wpautop added!
		$content = preg_replace( '/\n/smi', '', $content );
		$content = preg_replace( '/<p>/smi', '', $content );
		$content = preg_replace( '/<\/p>/smi', '', $content );

		$content = str_replace( array( '<!– [et_pb_line_break_holder] –>', '<!-- [et_pb_line_break_holder] -->', '||et_pb_line_break_holder||' ), $line_breaks_format, $content );
		$content = str_replace( '<!–- [et_pb_br_holder] -–>', '<br />', $content );

		// Convert the `<pee` tags back to `<p`.
		// @see et_pb_prep_code_module_for_wpautop().
		$content = str_replace( '<pee', '<p', $content );
		$content = str_replace( '</pee>', '</p> ', $content );

		return $content;
	}

	/**
	 * Replace selected entities in content.
	 *
	 * The do_shortcode() replaces square brackers with HTML entities. We need to convert
	 * them back to make sure JS code works properly.
	 *
	 * @since ??
	 *
	 * @param string $content Content.
	 *
	 * @return string Processed content.
	 */
	public static function replace_code_content_entities( $content ): string {
		$content = str_replace( '&#091;', '[', $content );
		$content = str_replace( '&#093;', ']', $content );
		$content = str_replace( '&#215;', 'x', $content );

		return $content;
	}

	/**
	 * Convert smart quotes and &amp; entity to their applicable characters.
	 *
	 * This function is the replacement of Divi 4 function `ET_Builder_Element::convert_smart_quotes_and_amp`.
	 *
	 * @since ??
	 *
	 * @param string $text Input text.
	 *
	 * @return string
	 */
	public static function convert_smart_quotes_and_amp( $text ): string {
		$smart_quotes = array(
			'&#8220;',
			'&#8221;',
			'&#8243;',
			'&#8216;',
			'&#8217;',
			'&#x27;',
			'&amp;',
		);

		$replacements = array(
			'&quot;',
			'&quot;',
			'&quot;',
			'&#39;',
			'&#39;',
			'&#39;',
			'&',
		);

		if ( 'fr_FR' === get_locale() ) {
			$french_smart_quotes = array(
				'&nbsp;&raquo;',
				'&Prime;&gt;',
			);

			$french_replacements = array(
				'&quot;',
				'&quot;&gt;',
			);

			$smart_quotes = array_merge( $smart_quotes, $french_smart_quotes );
			$replacements = array_merge( $replacements, $french_replacements );
		}

		$text = str_replace( $smart_quotes, $replacements, $text );

		return $text;
	}

	/**
	 * Check if a string contains HTML tags.
	 *
	 * @since ??
	 *
	 * @param string $string The string to be checked.
	 *
	 * @return bool `true` if the string contains HTML tags, `false` otherwise.
	 **/
	public static function contains_html_tags( string $string ):bool {
		return wp_strip_all_tags( $string ) !== $string;
	}

	/**
	 * Determines the target attribute for a link based on the provided link target attributes.
	 *
	 * @param string $link_target_attrs The link target attributes.
	 * @return string The target attribute value for the link.
	 */
	public static function link_target( $link_target_attrs ) {
		return 'on' === $link_target_attrs ? '_blank' : '_self';
	}

	/**
	 * Fix shortcodes in the content by manipulating attributes and patterns.
	 *
	 * This function is the replacement of Divi 4 function `et_pb_fix_shortcodes`.
	 *
	 * @since ??
	 *
	 * @param string $content        The content with shortcodes to fix.
	 * @param bool   $is_raw_content Whether the content is raw or needs additional processing.
	 *
	 * @return string The fixed content with adjusted shortcodes.
	 */
	public static function fix_shortcodes( string $content, bool $is_raw_content = false ): string {
		// Turn back the "data-et-target-link" attribute as "target" attribte
		// that has been made before saving the content in "et_fb_process_to_shortcode" function.
		if ( false !== strpos( $content, 'data-et-target-link=' ) ) {
			$content = str_replace( ' data-et-target-link=', ' target=', $content );
		}

		if ( $is_raw_content ) {
			$content = self::replace_code_content_entities( $content );
			$content = self::convert_smart_quotes_and_amp( $content );
		}

		static $shortcode_slugs = null;

		if ( null === $shortcode_slugs ) {
			/*
			 * Instead of using `ET_Builder_Element::get_module_slugs_by_post_type` which requires shortcode
			 * framework to be loaded first, use `ET_Builder_Module_Shortcode_Manager::get_modules_map` to prepare
			 * the shortcode slugs that extends ET_Builder_Element.
			 */
			$shortcode_slugs = array_merge(
				array_keys( ET_Builder_Module_Shortcode_Manager::get_modules_map( 'structural_modules' ) ),
				// As et_pb_column_inner isn't included in `structural_modules`, we need to add it here.
				[ 'et_pb_column_inner' ],
				array_keys( ET_Builder_Module_Shortcode_Manager::get_modules_map() )
			);

			// Prepare it to be used in the regex.
			$shortcode_slugs = implode( '|', $shortcode_slugs );
		}

		// The current patterns take care to replace only the shortcodes that extends `ET_Builder_Element` class
		// In order to avoid cases like this: `[3:45]<br>`
		// The pattern looks like this `(\[\/?(et_pb_section|et_pb_column|et_pb_row)[^\]]*\])`.
		$shortcode_pattern = sprintf( '(\[\/?(%s)[^\]]*\])', $shortcode_slugs );
		$opening_pattern   = '(<br\s*\/?>|<p>|\n)+';
		$closing_pattern   = '(<br\s*\/?>|<\/p>|\n)+';
		$space_pattern     = '[\s*|\n]*';

		// Replace `]</p>`, `]<br>` `]\n` with `]`
		// Make sure to remove any closing `</p>` tags or line breaks or new lines after shortcode tag.
		$pattern_1 = sprintf( '/%1$s%2$s%3$s/', $shortcode_pattern, $space_pattern, $closing_pattern );

		// Replace `<p>[`, `<br>[` `\n[` with `[`
		// Make sure to remove any opening `<p>` tags or line breaks or new lines before shortcode tag.
		$pattern_2 = sprintf( '/%1$s%2$s%3$s/', $opening_pattern, $space_pattern, $shortcode_pattern );

		$content = preg_replace( $pattern_1, '$1', $content );
		$content = preg_replace( $pattern_2, '$2', $content );

		return $content;
	}

	/**
	 * If the builder is used for the page, get rid of random p tags.
	 *
	 * This function is the replacement of Divi 4 function `et_pb_fix_builder_shortcodes`.
	 *
	 * @param string $content content.
	 *
	 * @return string|string[]|null
	 */
	public static function fix_builder_shortcodes( string $content ) {
		if ( is_admin() ) {
			// ET_Theme_Builder_Layout is not loaded in the administration and some plugins call
			// the_content there (e.g. WP File Manager).
			return $content;
		}

		$is_theme_builder = ET_Theme_Builder_Layout::is_theme_builder_layout();
		$is_singular      = is_singular() && 'on' === get_post_meta( get_the_ID(), '_et_pb_use_builder', true );

		// if the builder is used for the page, get rid of random p tags.
		if ( $is_theme_builder || $is_singular ) {
			$content = self::fix_shortcodes( $content );
		}

		return $content;
	}
}
