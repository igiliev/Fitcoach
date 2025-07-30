<?php
/**
 * EmailAccountServiceProvider class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Services\EmailAccountService;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Services\EmailAccountService\EmailAccountServiceAccount;

/**
 * EmailAccountServiceProvider class.
 *
 * @since ??
 */
class EmailAccountServiceProvider {

	/**
	 * Email account service provider slug.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_slug = '';

	/**
	 * Email account service provider label.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_label = '';

	/**
	 * Email account service provider name fields.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_name_fields = [
		'name'               => false,
		'useSingleNameField' => false,
		'showFirstNameField' => false,
		'showLastNameField'  => false,
	];

	/**
	 * Email account service provider new account fields.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_new_account_fields = [];

	/**
	 * Email account service provider accounts.
	 *
	 * @since ??
	 *
	 * @var EmailAccountServiceAccount[]
	 */
	private $_accounts = [];

	/**
	 * Email account service provider custom fields definition.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_custom_fields = [
		'enable'       => false,
		'isPredefined' => false,
	];

	/**
	 * EmailAccountServiceProvider constructor.
	 *
	 * @param string $slug Email account service provider slug.
	 * @param string $label Email account service provider label.
	 */
	public function __construct( string $slug, string $label ) {
		$this->_slug  = $slug;
		$this->_label = $label;
	}

	/**
	 * Set the email account service provider custom field definition.
	 *
	 * @since ??
	 *
	 * @param string $key   The custom field definition key.
	 * @param bool   $value The custom field definition value.
	 */
	public function set_custom_field( string $key, bool $value ) {
		if ( array_key_exists( $key, $this->_custom_fields ) && is_bool( $value ) ) {
			$this->_custom_fields[ $key ] = $value;
		}
	}

	/**
	 * Set the email account service provider custom field definitions.
	 *
	 * @since ??
	 *
	 * @param array $custom_fields The custom field definitions.
	 */
	public function set_custom_fields( array $custom_fields ) {
		foreach ( $custom_fields as $key => $value ) {
			$this->set_custom_field( $key, $value );
		}
	}

	/**
	 * Set the email account service provider name field.
	 *
	 * @since ??
	 *
	 * @param string $name_field Email account service provider name field name.
	 * @param bool   $value      Email account service provider name fields value.
	 */
	public function set_name_field( string $name_field, bool $value ) {
		if ( array_key_exists( $name_field, $this->_name_fields ) && is_bool( $value ) ) {
			$this->_name_fields[ $name_field ] = $value;
		}
	}

	/**
	 * Set the email account service provider name fields.
	 *
	 * @since ??
	 *
	 * @param array $name_fields Email account service provider name fields.
	 */
	public function set_name_fields( array $name_fields ) {
		foreach ( $name_fields as $name_field => $value ) {
			$this->set_name_field( $name_field, $value );
		}
	}

	/**
	 * Set the email account service provider new account fields.
	 *
	 * @since ??
	 *
	 * @param array $new_account_fields Email account service provider new account fields.
	 */
	public function set_new_account_fields( array $new_account_fields ) {
		$this->_new_account_fields = $new_account_fields;
	}

	/**
	 * Set the email service account.
	 *
	 * @since ??
	 *
	 * @param EmailAccountServiceAccount $account Email account service provider account.
	 */
	public function set_account( EmailAccountServiceAccount $account ) {
		$this->_accounts[ $account->get_slug() ] = $account;
	}

	/**
	 * Set the email service accounts.
	 *
	 * @since ??
	 *
	 * @param array $accounts Email account service provider accounts.
	 */
	public function set_accounts( array $accounts ) {
		foreach ( $accounts as $account ) {
			$this->set_account( $account );
		}
	}

	/**
	 * Check if the email account service provider account is valid.
	 *
	 * @since ??
	 *
	 * @param string $slug Email account service provider account slug.
	 *
	 * @return bool
	 */
	public function is_valid_account( string $slug ): bool {
		if ( false !== strpos( $slug, '|' ) ) {
			$parts = explode( '|', $slug );
			$slug  = $parts[0];
		}

		return isset( $this->_accounts[ $slug ] );
	}

	/**
	 * Get the email service account.
	 *
	 * @since ??
	 *
	 * @param string $slug Email account service provider account slug.
	 *
	 * @return EmailAccountServiceAccount|null
	 */
	public function get_account( string $slug ): ?EmailAccountServiceAccount {
		if ( false !== strpos( $slug, '|' ) ) {
			$parts = explode( '|', $slug );
			$slug  = $parts[0];
		}

		return $this->_accounts[ $slug ] ?? null;
	}

	/**
	 * Get the email service accounts.
	 *
	 * @since ??
	 *
	 * @return EmailAccountServiceAccount[]
	 */
	public function get_accounts(): array {
		return $this->_accounts;
	}

	/**
	 * Get the email account service provider slug.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_slug():string {
		return $this->_slug;
	}

	/**
	 * Get the email account service provider label.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_label():string {
		return $this->_label;
	}

	/**
	 * Get the email account service provider custom fields definition.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_custom_fields():array {
		return $this->_custom_fields;
	}

	/**
	 * Get the email account service provider name fields.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_name_fields():array {
		return $this->_name_fields;
	}

	/**
	 * Check if the email account service provider name field is enabled.
	 *
	 * @since ??
	 *
	 * @param string $key Email account service provider name field name.
	 *
	 * @return bool
	 */
	public function show_name_field( string $key ):bool {
		return $this->_name_fields[ $key ] || false;
	}

	/**
	 * Get the email account service provider new account fields.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_new_account_fields():array {
		return $this->_new_account_fields;
	}

	/**
	 * Convert the object to its array representation.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function to_array():array {
		$accounts = [];

		foreach ( $this->_accounts as $account ) {
			$accounts[ $account->get_slug() ] = $account->to_array();
		}

		return [
			'slug'             => $this->_slug,
			'label'            => $this->_label,
			'nameFields'       => $this->_name_fields,
			'newAccountFields' => $this->_new_account_fields,
			'customFields'     => $this->_custom_fields,
			'accounts'         => $accounts,
		];
	}
}
