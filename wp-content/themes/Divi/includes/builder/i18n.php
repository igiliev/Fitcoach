<?php

require_once ET_BUILDER_DIR . 'feature/I18n.php';

/**
 * Retrieve a commonly used translation.
 *
 * @since 4.4.9
 *
 * @param string  $key Translation key.
 * @param boolean $reset Reset cache.
 *
 * @return string
 */
function et_builder_i18n( $key, $reset = false ) {
	static $cache;

	if ( true === $reset ) {
		$cache = array();
	}

	if ( ! is_user_logged_in() ) {
		return $key;
	}

	if ( ! isset( $cache[ $key ] ) ) {
		$cache[ $key ] = ET_Builder_I18n::get( $key );
	}

	return $cache[ $key ];

}

/**
 * Resets commonly used translations cache.
 *
 * @since 4.4.9
 *
 * @return void
 */
function et_builder_i18n_reset_cache() {
	et_builder_i18n( '', true );
}
