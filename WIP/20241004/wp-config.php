<?php
define( 'WP_CACHE', false ); // Added by WP Rocket
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
// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mdev_armoireplus' );
/** MySQL database username */
define( 'DB_USER', 'mdev_armoireplus' );
/** MySQL database password */
define( 'DB_PASSWORD', 'aq3xtt1rPwvG%F1&' );
/** MySQL hostname */
define( 'DB_HOST', 'localhost' );
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
/**
* Authentication Unique Keys and Salts.
*
* Change these to different unique phrases!
* You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
* You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
*
* @since 2.6.0
*/
define( 'AUTH_KEY',          'ph<7=:I1Y{M cn9Rb1wU?l5,,$+=pyC8|tJ(DA0y!AGU@$(B8#VINW,sd8$?f7y^' );
define( 'SECURE_AUTH_KEY',   'y#a/W0#(hC!T#a;s0<hwMLe*NsFm= i6.XTejuGOM@4r8Pv?LR)a|qM,j]+IG@tU' );
define( 'LOGGED_IN_KEY',     'SydQK##:o**MwS}<F}hH+KO%e.KKdM7JfjI*dKkw=sS9^/:A?cB5z]Cmd*rG@R$T' );
define( 'NONCE_KEY',         '~-?8Yom:dY`FWp=Uv8=c$q`0b&CcRy`!9eV<jI;~4NlX1z>PATDDvORv(W-v^JxU' );
define( 'AUTH_SALT',         ',HF6eeN_IA!mjGsQ~^H%WSYWH[7%$5zSF>1{|`YT/|$D_5_aOv<B4|v{H?7y0@Yk' );
define( 'SECURE_AUTH_SALT',  'O<xmJoo3]F$5X*KBX2|wLF$`12cA@kL,puES0MHr;tt-er^TD296+e-H*42ACKrv' );
define( 'LOGGED_IN_SALT',    'Fidz!.0~/(ZD!FA&V<~F>+:_ZVqfV {%_UlB& M1TC1Oxfhc}oHB6sSS;/wTz%;U' );
define( 'NONCE_SALT',        'awOP~8RRtw[a_?.xO?[`S8lCtl/1;]P*A` u(BX{34sDUKes)StwcuJg*82+hH:/' );
define( 'WP_CACHE_KEY_SALT', 'MyAj^:WIQ!*%G .Ij~@+I*fNdY0G}?)E/1ke&2SJw~]AfoDs5?Zwms]Dxw!l4P:v' );
/**
* WordPress Database Table prefix.
*
* You can have multiple installations in one database if you give each
* a unique prefix. Only numbers, letters, and underscores please!
*/
$table_prefix = 'jmlkf_';
define('WP_POST_REVISIONS', 5);

define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', true);
define('WP_DEBUG_LOG', true);

define('WP_AUTO_UPDATE_CORE', false);
define('DISALLOW_FILE_EDIT', true);
define('DISABLE_WP_CRON', false);
define('WPLANG', 'fr_FR');
define('ALLOW_UNFILTERED_UPLOADS', true);

/* That's all, stop editing! Happy blogging. */
define( 'WC_TS_EASY_INTEGRATION_ENCRYPTION_KEY', 'a846e2934f24cbea9636e47f86c4823b034024540cb08cbdf1e03507407a9130' );
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
