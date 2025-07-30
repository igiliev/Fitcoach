<?php
/**
 * Module: SpamProtectionPresetAttrsMap class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Module\Options\SpamProtection;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * SpamProtectionPresetAttrsMap class.
 *
 * This class provides the static map for the spamProtection preset attributes.
 *
 * @since ??
 */
class SpamProtectionPresetAttrsMap {
	/**
	 * Get the map for the spamProtection preset attributes.
	 *
	 * @since ??
	 *
	 * @param string $attr_name The attribute name.
	 *
	 * @return array The map for the spamProtection preset attributes.
	 */
	public static function get_map( string $attr_name ) {
		return [
			"{$attr_name}__enabled"         => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'enabled',
			],
			"{$attr_name}__provider"        => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'provider',
			],
			"{$attr_name}__account"         => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'account',
			],
			"{$attr_name}__minScore"        => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'minScore',
			],
			"{$attr_name}__useBasicCaptcha" => [
				'attrName' => $attr_name,
				'preset'   => 'content',
				'subName'  => 'useBasicCaptcha',
			],
		];
	}
}
