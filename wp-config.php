<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_lpm2016');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '7PZ4x8,DvOi_P5x#Uscg]0Gkp|r/Qsp-XO!XZ]{eb_BD_:n{t/nSaBou]?nj}&@Y');
define('SECURE_AUTH_KEY',  '`7nBz}ep]+lH7.|6Q]u5gx=I,q.CF{Qad_<<bcE*WQi)^32:6wHE#<gdd:02W-@.');
define('LOGGED_IN_KEY',    'k/1[jFa)lNm*4{ff}-53@v{/K+M<KcM^Ow{$It8NZ>Tp+iKzB|5kXw{=elSw|*DP');
define('NONCE_KEY',        'Hl-9t~o{w ~o%7YG[:D<n`|<`UjW0bf320dI$B5&`~!84SD(XxXnj=A~P{)GwsG~');
define('AUTH_SALT',        'x}IcrFm1*O-xC_];|sY-i8X7.0~I{p8jS#FeP-Dgm=11JThs:+guN^Ki$I2Xei$p');
define('SECURE_AUTH_SALT', 'kDos9h%0tvSMnzq;}{|*t:pIe[tPf+X7)CT]ckTC#n[j>T!2G+2BFA@.l#!@DHp,');
define('LOGGED_IN_SALT',   '#~bl*q+_[VHlLM|RX${(>J:`P8A@7(ni?@o}Q[WmW@ HLW@6ty;K}my]Ech:0Z}0');
define('NONCE_SALT',       ']+G-w`.``7Q/5zBpb%[FZkIXhko-}mGrZOeyoFO{ ){A}dAPb$*[XsvZsB`O*|B,');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
