<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'txlxmqol_WPVUS');

/** Database username */
define('DB_USER', 'txlxmqol_WPVUS');

/** Database password */
define('DB_PASSWORD', 'k9FdLKMzdspoOGFV<');

/** Database hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY', 'e4eab28fdc7f78bd9ae4311dad3f149b455ba8260714654d505065c6a0538ebf');
define('SECURE_AUTH_KEY', 'a4017960a6de3557a66495a585eb9093971e0be8afae3e2d0cc60f8ecbfc35cb');
define('LOGGED_IN_KEY', 'a8c5c6504a0067f8c1a50ee85cafa013d59beba4200dff789d867e20e404e50a');
define('NONCE_KEY', 'ffac9096ddedf9958da29a25162e5cd81b273d7c2c50430469de5bb25ed2002b');
define('AUTH_SALT', '8834cf0a9bc101e1abfb5288e3918e1c12f9913436b4c333defb0dbe83b8502e');
define('SECURE_AUTH_SALT', 'e683a4da3d70f15541f9efd4293be283d53678b011d8c0787f27c4367e5cff3c');
define('LOGGED_IN_SALT', 'e694a0d0ef8c2fde61f104d46d437504d81d0a81f3a8b09e39d01aa4eae49140');
define('NONCE_SALT', '31efff862b3be3f07c7a72c2171569e14f3917756c8064452fa054fbd71de9ae');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = '2Q8_';
define('WP_CRON_LOCK_TIMEOUT', 120);
define('AUTOSAVE_INTERVAL', 300);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 7);
define('WP_AUTO_UPDATE_CORE', true);

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
