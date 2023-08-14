<?php

// SSL + Varnish
define('FORCE_SSL_LOGIN', true);
define('FORCE_SSL_ADMIN', true);
define('CONCATENATE_SCRIPTS', false);
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
        $_SERVER['HTTPS'] = 'on';
}

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
define( 'DB_NAME', "scnegociosimob01" );

/** Database username */
define( 'DB_USER', "scnegociosimob01" );

/** Database password */
define( 'DB_PASSWORD', "Zinho2023" );

/** Database hostname */
define( 'DB_HOST', "mysql.scnegociosimobiliarios.com.br" );

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
define('AUTH_KEY',         'Bg9fF/Lyx728tJkwVAIhel74y11RDIL/SIJd3IrJVfyGh0zcmMnLsAqF0qWGXRq/IfmOrL+TfyptRUZzpPsG1Q==');
define('SECURE_AUTH_KEY',  'f7N6PgnukZ8+R/vuDwKnf4fvWqXQTgRIpN9763j9y8rGRdd/UfuDVl1vK5Exh7zSzKV+4OFU95Jg8ACDh/07Ug==');
define('LOGGED_IN_KEY',    'k2wnx3WfT6uHNZ05VFJvdTwG8r8W/AgX+35xYtKeU91rn5pjih75EL4Fm5+l+UZRbLVe+DcXrgUcwrASwmU03Q==');
define('NONCE_KEY',        'hMgHZYcZIPYAp/jS5T+kxh9AjB8FtJKEDn3dFra7Ji5XqEvnnTVT367vwo2wDFRKE89k9XORrDU7lqqwrziS9Q==');
define('AUTH_SALT',        'NNfbvdXTJ4vLCwRA7L5CI5TI2iLInyNqdTyyE1X7QgOpeHsyEjPdz734kdrdVf0AUSSnPkrcQFeWMh40LR1N9g==');
define('SECURE_AUTH_SALT', 'xRPJW+r/EO/CQtxqtib5oazEmKwXKTjkznHkNtRCaqIpO0AIktWn3kVvZ8y3NJCmBcwWZ9uyPqAxM3KRYipLDg==');
define('LOGGED_IN_SALT',   '2eAPkd4TWODfs2J2uUtCrmb0kpgdTZzBsafxO5yT2KVa3gWtR0cbY93xk8EL40heKPDbWfsERLaX5CQbvC4KDQ==');
define('NONCE_SALT',       '494bYTU4zKOkEP2qAOeQ46Ku+XdNLugA/afQZqIyZMISG8OOu7ukM79sHeXRgaSZQlocBgZczveVDaP/5Sh49w==');


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
define('WP_DEBUG', false);
/* define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 ); */

define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'WP_SITEURL', 'https://scnegociosimobiliarios.com.br/' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname(__FILE__) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);
