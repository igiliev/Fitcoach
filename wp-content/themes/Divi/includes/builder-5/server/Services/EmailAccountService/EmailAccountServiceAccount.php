<?php
/**
 * EmailAccountServiceAccount class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Services\EmailAccountService;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * EmailAccountServiceAccount class.
 *
 * @since ??
 */
class EmailAccountServiceAccount {

	/**
	 * Email service account provider slug.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_provider = '';

	/**
	 * Email service account slug.
	 *
	 * @since ??
	 *
	 * @var string
	 */
	private $_slug = '';

	/**
	 * Email service account credentials.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_credentials = [];

	/**
	 * Email service account custom data.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_custom_data = [];

	/**
	 * Email service account custom fields.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_custom_fields = [];

	/**
	 * Email service account lists.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_lists = [];

	/**
	 * Email service account authorized.
	 *
	 * @since ??
	 *
	 * @var bool
	 */
	private $_authorized = false;

	/**
	 * EmailAccountServiceAccount constructor.
	 *
	 * @param string $slug Email service account slug.
	 * @param string $provider Email service account provider slug.
	 */
	public function __construct( string $slug, string $provider ) {
		$this->_slug     = $slug;
		$this->_provider = $provider;
	}

	/**
	 * Set the email service account credential.
	 *
	 * @since ??
	 *
	 * @param string $credential_name Email service account credential name.
	 * @param mixed  $credential_value Email service account credential value.
	 *
	 * @return void
	 */
	public function set_credential( string $credential_name, $credential_value ) {
		$this->_credentials[ $credential_name ] = $credential_value;
	}

	/**
	 * Set the email service account credentials.
	 *
	 * @since ??
	 *
	 * @param array $credentials Email service account credentials.
	 *
	 * @return void
	 */
	public function set_credentials( array $credentials ) {
		foreach ( $credentials as $credential_name => $credential_value ) {
			$this->set_credential( $credential_name, $credential_value );
		}
	}

	/**
	 * Get the email service account credentials.
	 *
	 * @since ??
	 *
	 * @param bool $mask_data Whether to mask credentials data.
	 *
	 * @return array
	 */
	public function get_credentials( bool $mask_data = true ): array {
		if ( $mask_data ) {
			$credentials = [];

			foreach ( $this->_credentials as $key => $value ) {
				$credentials[ $key ] = str_repeat( '*', strlen( $value ) );
			}

			return $credentials;
		}

		return $this->_credentials;
	}

	/**
	 * Set the email service account custom data.
	 *
	 * @since ??
	 *
	 * @param string $custom_data_name Email service account custom data name.
	 * @param mixed  $custom_data_value Email service account custom data value.
	 *
	 * @return void
	 */
	public function set_custom_data( string $custom_data_name, $custom_data_value ) {
		$this->_custom_data[ $custom_data_name ] = $custom_data_value;
	}

	/**
	 * Set the email service account custom data.
	 *
	 * @since ??
	 *
	 * @param array $custom_data Email service account custom data.
	 *
	 * @return void
	 */
	public function set_custom_datas( array $custom_data ) {
		foreach ( $custom_data as $custom_data_name => $custom_data_value ) {
			$this->set_custom_data( $custom_data_name, $custom_data_value );
		}
	}

	/**
	 * Get the email service account custom data.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_custom_data(): array {
		return $this->_custom_data;
	}

	/**
	 * Set the email service account custom field.
	 *
	 * @since ??
	 *
	 * @param string $custom_field_name Email service account custom field name.
	 * @param mixed  $custom_field_value Email service account custom field value.
	 *
	 * @return void
	 */
	public function set_custom_field( string $custom_field_name, $custom_field_value ) {
		$this->_custom_fields[ $custom_field_name ] = $custom_field_value;
	}

	/**
	 * Set the email service account custom fields.
	 *
	 * @since ??
	 *
	 * @param array $custom_fields Email service account custom fields.
	 *
	 * @return void
	 */
	public function set_custom_fields( array $custom_fields ) {
		foreach ( $custom_fields as $custom_field_name => $custom_field_value ) {
			$this->set_custom_field( $custom_field_name, $custom_field_value );
		}
	}

	/**
	 * Get the email service account custom fields.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_custom_fields(): array {
		return $this->_custom_fields;
	}

	/**
	 * Set the email service account authorized.
	 *
	 * @since ??
	 *
	 * @param bool $authorized Email service account authorized.
	 *
	 * @return void
	 */
	public function set_authorized( bool $authorized ) {
		$this->_authorized = $authorized;
	}

	/**
	 * Set the email service account list.
	 *
	 * @since ??
	 *
	 * @param string $list_name Email service account list name.
	 * @param array  $list_value Email service account list value.
	 *
	 * @return void
	 */
	public function set_list( string $list_name, array $list_value ) {
		$this->_lists[ $list_name ] = $list_value;
	}

	/**
	 * Set the email service account lists.
	 *
	 * @since ??
	 *
	 * @param array $lists Email service account lists.
	 *
	 * @return void
	 */
	public function set_lists( array $lists ) {
		foreach ( $lists as $list_name => $list_value ) {
			$this->set_list( $list_name, $list_value );
		}
	}

	/**
	 * Get the email service account lists.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_lists(): array {
		return $this->_lists;
	}

	/**
	 * Get the email service account authorized.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public function is_authorized(): bool {
		return $this->_authorized;
	}

	/**
	 * Get the email service account slug.
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_slug():string {
		return $this->_slug;
	}

	/**
	 * Convert the object to its array representation.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function to_array():array {
		return [
			'provider'      => $this->_provider,
			'slug'          => $this->_slug,
			'credentials'   => $this->get_credentials( true ),
			'is_authorized' => $this->_authorized,
			'custom_fields' => $this->_custom_fields,
			'custom_data'   => $this->_custom_data,
			'lists'         => $this->_lists,
		];
	}
}
