<?php
define('WP_CACHE', true); // Added by SpeedyCache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'razhodim_fitcoach' );

/** Database username */
define( 'DB_USER', 'razhodim_fitcoach' );

/** Database password */
define( 'DB_PASSWORD', 'p2X99-S2!7' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'tnzv4hgvnaff5f6dcrlbrndm6kjfl7w292soa04hn3qc6rglsbx6uzg2jnrt4koo' );
define( 'SECURE_AUTH_KEY',  'uwxucajj3xszhjxrgxy5azriko61br20oe581keyulhnzjnxoetsdqnfiveiiwmf' );
define( 'LOGGED_IN_KEY',    'gnqox8j6hinqhzwd35jk7vakuldaj1qdnbm8d9fph1hgli38ppxfvztaqw1gzbie' );
define( 'NONCE_KEY',        'a0u3adwmumivb4umlzz7e2rgeewyuxogqadklmlcfziyoc5hn8xjnh1bbkpms32n' );
define( 'AUTH_SALT',        'zanj3s89vwhpomycmkgbfesvnsalcwfjipvya9s1557fsqnfjpyjftzr3jiblfvm' );
define( 'SECURE_AUTH_SALT', 'torovqwusppsj2jh2yywfrc5vj72nfn6wrhp8w9grebbdg88pmx1v7zgbmaxyaf1' );
define( 'LOGGED_IN_SALT',   'xyhj51lz3vrbojcvk0jjoaok7g8daapo4gtusbn5cqn6mfqxaj2b4dgbzdw6b5wp' );
define( 'NONCE_SALT',       'kaktaopxo1hhsphrblbip4ssxjnhsszycz9uesc23ppauhfinc59lxtd7z1tdwlt' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wpqv_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
