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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          'cOTm^/Lu43[)mt_8xK1 Otliuo;ZW_W)?cFT,OQi.6RQe}S;bo]zI/$lN*BM#gVq' );
define( 'SECURE_AUTH_KEY',   'S1eo!l3lE-.`|%e{BFT-GBGuEn3jG5?&_.d,6ehLX?j4#FXA55}x_/ ;(XUc*+v,' );
define( 'LOGGED_IN_KEY',     ']#UA+~G7Go0alZb!YsL^hN_|(g7(gs/f:N$hFD->8.aLEXHMV01F8 W.Bm3Yhs95' );
define( 'NONCE_KEY',         '!Nu#p-9p&x4J{FuBedT(m7&mudK:e] h?lkc2oP0Khn|lF,U^o:c%>q{Q.0lilR7' );
define( 'AUTH_SALT',         'd:l.w4++B`)RM;]7pCV,d%QY cR33Rz{A$e%Ud<y6q)h:@uT*,(#/No#_2`FPD(c' );
define( 'SECURE_AUTH_SALT',  '.EnAhi=:!aV/u%56 X+N%4+o(btar<IbvPml0nq)0/-;G!+]t[&zd<w&+|fT<H+E' );
define( 'LOGGED_IN_SALT',    'EH/`shfKUFGcy@?kEH$ U-y/?6J?Z6ED>iC?b0of!=brlg?8O`6lYvEF37k7Z_K?' );
define( 'NONCE_SALT',        '^2}FRg!rHqOif$9U?X3g6 }l2UdvQ&M5jRKUVOhTh =>Xyx;TSDLKc/ *Ng*Adb,' );
define( 'WP_CACHE_KEY_SALT', 'sT[r`+nB<=.;gw~B?qxNEeXbT:;0Tr1;fT~^^UWo1B*nP:dk?zVe<ulK>G-E9yO3' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
