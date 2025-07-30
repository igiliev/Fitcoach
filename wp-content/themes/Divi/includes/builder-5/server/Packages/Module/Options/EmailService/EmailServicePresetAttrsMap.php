<?php
/**
 * Module: EmailServicePresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\EmailService;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * EmailServicePresetAttrsMap class.
 *
 * This class provides the static map for the emailService preset attributes.
 *
 * @since ??
 */
class EmailServicePresetAttrsMap {
	/**
	 * Get the map for the emailService preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the emailService preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__provider" => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'provider',
			],
			"{$attr_name}__account"  => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'account',
			],
		];
	}
}
