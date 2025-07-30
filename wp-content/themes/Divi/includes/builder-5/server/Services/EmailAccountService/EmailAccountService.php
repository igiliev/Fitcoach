<?php
/**
 * EmailAccountService class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Services\EmailAccountService;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET_Core_API_Email_Providers;
use ET_Core_API_Email_Provider;
use ET\Builder\Services\EmailAccountService\EmailAccountServiceProvider;
use ET\Builder\Services\EmailAccountService\EmailAccountServiceAccount;

/**
 * EmailAccountService class.
 *
 * @since ??
 */
/**
 * EmailAccountService class.
 *
 * @since ??
 */
class EmailAccountService {

	/**
	 * Populate the email service data.
	 *
	 * @since ??
	 *
	 * @return EmailAccountServiceAccount[] Returns the email service data.
	 */
	public static function populate_data(): array {
		$populated = [];

		$instance = ET_Core_API_Email_Providers::instance();

		if ( $instance instanceof ET_Core_API_Email_Providers ) {
			$names_by_slug = $instance->names_by_slug();
			$accounts      = $instance->accounts();

			foreach ( $names_by_slug as $slug => $label ) {
				$name_field_only = $instance->get( $slug, '' )->name_field_only ?? false;

				$email_service_provider = new EmailAccountServiceProvider( $slug, $label );

				$email_service_provider->set_custom_fields(
					[
						'enable'       => in_array( $instance->get( $slug, '' )->custom_fields, [ 'dynamic', 'predefined' ], true ),
						'isPredefined' => 'predefined' === $instance->get( $slug, '' )->custom_fields,
					]
				);

				$email_service_provider->set_name_fields(
					[
						'name'               => $name_field_only,
						'useSingleNameField' => ! $name_field_only,
						'showFirstNameField' => ! $name_field_only,
						'showLastNameField'  => ! $name_field_only,
					]
				);

				$new_account_fields = $instance->get( $slug, '' )->get_account_fields() ?? [];

				if ( $new_account_fields ) {
					$email_service_provider->set_new_account_fields( $new_account_fields );
				}

				$provider_accounts = $accounts[ $slug ] ?? [];

				if ( $provider_accounts ) {
					foreach ( $provider_accounts as $account_name => $account_details ) {
						if ( empty( $account_details ) ) {
							continue;
						}

						$email_service_account = new EmailAccountServiceAccount( $account_name, $slug );

						foreach ( $account_details as $account_detail_key => $account_detail_value ) {
							if ( 'true' === $account_detail_value ) {
								$account_detail_value = true;
							}

							if ( 'false' === $account_detail_value ) {
								$account_detail_value = false;
							}

							$is_credentials_data = isset( $new_account_fields[ $account_detail_key ] );

							if ( $is_credentials_data ) {
								$email_service_account->set_credential( $account_detail_key, $account_detail_value );
							} else {
								switch ( $account_detail_key ) {
									case 'lists':
										$email_service_account->set_lists( $account_detail_value );
										break;
									case 'is_authorized':
										$email_service_account->set_authorized( $account_detail_value );
										break;
									case 'custom_fields':
										$email_service_account->set_custom_fields( $account_detail_value );
										break;

									default:
										$email_service_account->set_custom_data( $account_detail_key, $account_detail_value );
										break;
								}
							}
						}

						$email_service_provider->set_account( $email_service_account );
					}
				}

				$populated[ $slug ] = $email_service_provider;
			}
		}

		ksort( $populated );

		return $populated;
	}

	/**
	 * Get the email service providers definition.
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public static function definition(): array {
		$definition = [];
		$populated  = self::populate_data();

		foreach ( $populated as $slug => $email_service_provider ) {
			$definition[ $slug ] = $email_service_provider->to_array();
		}

		return $definition;
	}

	/**
	 * Get the email service provider object.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 *
	 * @return EmailAccountServiceProvider|null Returns the email service providers class. Will return null if the provider doesn't exist.
	 */
	public static function get_provider( string $provider ): ?EmailAccountServiceProvider {
		$populated = self::populate_data();

		return $populated[ $provider ] ?? $populated['mailchimp'] ?? null;
	}

	/**
	 * Get the email service account object.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 *
	 * @return EmailAccountServiceAccount|null Returns the email service account class. Will return null if the provider or the account doesn't exist.
	 */
	public static function get_account( string $provider, string $account ): ?EmailAccountServiceAccount {
		$provider_class = self::get_provider( $provider );

		if ( ! $provider_class ) {
			return null;
		}

		return $provider_class->get_account( $account );
	}

	/**
	 * Fetch an account for specific email service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 *
	 * @return array Returns the email service providers definition.
	 */
	public static function fetch_account( string $provider, string $account ): array {
		$provider_class = ET_Core_API_Email_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Email_Provider ) {
			$provider_class->fetch_subscriber_lists();
		}

		return self::definition();
	}

	/**
	 * Delete an account for specific email service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 *
	 * @return array Returns the email service providers definition.
	 */
	public static function delete_account( string $provider, string $account ): array {
		$provider_class = ET_Core_API_Email_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Email_Provider ) {
			$provider_class->delete();
		}

		return self::definition();
	}

	/**
	 * Create an account for specific email service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 * @param array  $data     The account data.
	 *
	 * @return array Returns the email service providers definition.
	 */
	public static function create_account( string $provider, string $account, array $data ): array {
		if ( self::validate_account_data( $provider, $account, $data ) ) {
			$provider_class = ET_Core_API_Email_Providers::instance()->get( $provider, $account, 'builder' );

			if ( $provider_class instanceof ET_Core_API_Email_Provider ) {
				$data = self::sanitize_account_data( $provider, $account, $data );

				foreach ( $data as $field_name => $value ) {
					$provider_class->data[ $field_name ] = sanitize_text_field( $value );
				}

				if ( ! $provider_class->account_name ) {
					$provider_class->set_account_name( $account );
				}

				$provider_class->fetch_subscriber_lists();
			}
		}

		return self::definition();
	}

	/**
	 * Validate account data for specific email service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 * @param array  $data     The account data.
	 *
	 * @return bool Returns true if the data is valid.
	 */
	public static function validate_account_data( string $provider, string $account, array $data ): bool {
		$provider_class = ET_Core_API_Email_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Email_Provider ) {
			$fields = $provider_class->get_account_fields();

			if ( empty( $fields ) ) {
				return true;
			}

			foreach ( $fields as $field_key => $field ) {
				$field_value  = $data[ $field_key ] ?? '';
				$protocol     = $field['required'] ?? '';
				$not_required = $field['not_required'] ?? false;

				// skip fields when the protocol doesn't match.
				if ( 'https' === $protocol && ! is_ssl() ) {
					continue;
				}

				// skip fields when the protocol doesn't match.
				if ( 'http' === $protocol && is_ssl() ) {
					continue;
				}

				if ( true !== $not_required && '' === $field_value ) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Sanitize account data for specific email service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 * @param array  $data     The account data.
	 *
	 * @return array Returns the sanitized account data.
	 */
	public static function sanitize_account_data( string $provider, string $account, array $data ): array {
		$sanitized = [];

		$provider_class = ET_Core_API_Email_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Email_Provider ) {
			$fields = $provider_class->get_account_fields();

			if ( empty( $fields ) ) {
				return [];
			}

			foreach ( $fields as $field_key => $field ) {
				$field_value = $data[ $field_key ] ?? '';
				$protocol    = $field['required'] ?? '';

				// skip fields when the protocol doesn't match.
				if ( 'https' === $protocol && ! is_ssl() ) {
					continue;
				}

				// skip fields when the protocol doesn't match.
				if ( 'http' === $protocol && is_ssl() ) {
					continue;
				}

				$sanitized[ $field_key ] = sanitize_text_field( $field_value );
			}
		}

		return $sanitized;
	}

}
