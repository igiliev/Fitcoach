<?php
/**
 * SpamProtectionService class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Services\SpamProtectionService;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET_Core_API_Spam_Providers;
use ET_Core_API_Spam_Provider;

/**
 * SpamProtectionService class.
 *
 * @since ??
 */
class SpamProtectionService {

	/**
	 * Get the spam protection service providers definition.
	 *
	 * @since ??
	 *
	 * @param bool $mask_credentials_data Whether to mask credentials data.
	 *
	 * @return array
	 */
	public static function definition( bool $mask_credentials_data = true ): array {
		$populated = [];

		$instance = ET_Core_API_Spam_Providers::instance();

		if ( $instance instanceof ET_Core_API_Spam_Providers ) {
			$names_by_slug = $instance->names_by_slug();
			$accounts      = $instance->accounts();

			foreach ( $names_by_slug as $slug => $label ) {
				$item = [
					'slug'  => $slug,
					'label' => $label,
				];

				$provider_class = ET_Core_API_Spam_Providers::instance()->get( $slug, '', 'builder' );

				if ( $provider_class instanceof ET_Core_API_Spam_Provider ) {
					if ( isset( $provider_class->max_accounts ) && is_int( $provider_class->max_accounts ) ) {
						$item['maxAccounts'] = $provider_class->max_accounts;
					}

					$new_account_fields = $provider_class->get_account_fields();

					if ( $new_account_fields ) {
						$item['newAccountFields'] = $new_account_fields;
					}

					$provider_accounts = $accounts[ $slug ] ?? [];

					if ( $provider_accounts ) {
						foreach ( $provider_accounts as $account_name => $account_details ) {
							if ( empty( $account_details ) ) {
								continue;
							}

							foreach ( $account_details as $account_detail_key => $account_detail_value ) {
								if ( 'true' === $account_detail_value ) {
									$account_detail_value = true;
								}

								if ( 'false' === $account_detail_value ) {
									$account_detail_value = false;
								}

								$is_credentials_data = isset( $new_account_fields[ $account_detail_key ] );

								if ( $is_credentials_data && $mask_credentials_data ) {
									$account_detail_value = str_repeat( '*', strlen( $account_detail_value ) );
								}

								if ( $is_credentials_data ) {
									$item['accounts'][ $account_name ]['credentials'][ $account_detail_key ] = $account_detail_value;
								} else {
									$item['accounts'][ $account_name ][ $account_detail_key ] = $account_detail_value;
								}
							}
						}
					}
				}

				$populated[ $slug ] = $item;
			}
		}

		ksort( $populated );

		return $populated;
	}

	/**
	 * Delete an account for specific spam protection service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 *
	 * @return array Returns the spam protection service providers definition.
	 */
	public static function delete_account( string $provider, string $account ): array {
		$provider_class = ET_Core_API_Spam_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Spam_Provider ) {
			$provider_class->delete();
		}

		return self::definition();
	}

	/**
	 * Create an account for specific spam protection service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider The provider slug.
	 * @param string $account  The account ID.
	 * @param array  $data     The account data.
	 *
	 * @return array Returns the spam protection service providers definition.
	 */
	public static function create_account( string $provider, string $account, array $data ): array {
		if ( self::validate_account_data( $provider, $account, $data ) ) {
			$provider_class = ET_Core_API_Spam_Providers::instance()->get( $provider, $account, 'builder' );

			if ( $provider_class instanceof ET_Core_API_Spam_Provider ) {
				$data = self::sanitize_account_data( $provider, $account, $data );

				foreach ( $data as $field_name => $value ) {
					$provider_class->data[ $field_name ] = $value;
				}

				if ( ! $provider_class->account_name ) {
					$provider_class->set_account_name( $account );
				}

				$provider_class->save_data();
			}
		}

		return self::definition();
	}

	/**
	 * Validate account data for specific spam protection service provider.
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
		$provider_class = ET_Core_API_Spam_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Spam_Provider ) {
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
	 * Sanitize account data for specific spam protection service provider.
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

		$provider_class = ET_Core_API_Spam_Providers::instance()->get( $provider, $account, 'builder' );

		if ( $provider_class instanceof ET_Core_API_Spam_Provider ) {
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

	/**
	 * Validate token for specific spam protection service provider.
	 *
	 * @since ??
	 *
	 * @param string $provider  The provider slug.
	 * @param string $account   The account ID.
	 * @param float  $min_score The minimum score required to pass the validation.
	 *
	 * @return bool Returns true if the token is valid or the provider is not enabled.
	 */
	public static function validate_token( string $provider, string $account, ?float $min_score = 0.0 ):bool {
		if ( $provider && $account ) {
			$service = ET_Core_API_Spam_Providers::instance()->get( $provider, $account );
		} else {
			$service = et_core_api_spam_find_provider_account();
		}

		// If no service is found, or the service is not enabled, return true.
		if ( ! $service || ! $service->is_enabled() ) {
			return true;
		}

		$result = $service->verify_form_submission();

		// If the result is not an array, or the result is not successful, return false.
		if ( ! is_array( $result ) || ! $result['success'] ) {
			return false;
		}

		return $min_score <= ( $result['score'] ?? 1 );
	}

}
