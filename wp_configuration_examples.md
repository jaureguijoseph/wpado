<?php

/\*\*

\* The base configuration for WordPress

\*

\* The wp-config.php creation script uses this file during the installation.

\* You don't have to use the website, you can copy this file to "wp-config.php"

\* and fill in the values.

\*

\* This file contains the following configurations:

\*

\* \* Database settings

\* \* Secret keys

\* \* Database table prefix

\* \* ABSPATH

\*

\* @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/

\*

\* @package WordPress

\*/

// \*\* Database settings - You can get this info from your web host \*\* //

/\*\* The name of the database for WordPress \*/

define( 'DB\_NAME', 'database\_name\_here' );

/\*\* Database username \*/

define( 'DB\_USER', 'username\_here' );

/\*\* Database password \*/

define( 'DB\_PASSWORD', 'password\_here' );

/\*\* Database hostname \*/

define( 'DB\_HOST', 'localhost' );

/\*\* Database charset to use in creating database tables. \*/

define( 'DB\_CHARSET', 'utf8' );

/\*\* The database collate type. Don't change this if in doubt. \*/

define( 'DB\_COLLATE', '' );

/\*\*#@+

\* Authentication unique keys and salts.

\*

\* Change these to different unique phrases! You can generate these using

\* the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.

\*

\* You can change these at any point in time to invalidate all existing cookies.

\* This will force all users to have to log in again.

\*

\* @since 2.6.0

\*/

define( 'AUTH\_KEY', 'put your unique phrase here' );

define( 'SECURE\_AUTH\_KEY', 'put your unique phrase here' );

define( 'LOGGED\_IN\_KEY', 'put your unique phrase here' );

define( 'NONCE\_KEY', 'put your unique phrase here' );

define( 'AUTH\_SALT', 'put your unique phrase here' );

define( 'SECURE\_AUTH\_SALT', 'put your unique phrase here' );

define( 'LOGGED\_IN\_SALT', 'put your unique phrase here' );

define( 'NONCE\_SALT', 'put your unique phrase here' );

/\*\*#@-\*/

/\*\*

\* WordPress database table prefix.

\*

\* You can have multiple installations in one database if you give each

\* a unique prefix. Only numbers, letters, and underscores please!

\*

\* At the installation time, database tables are created with the specified prefix.

\* Changing this value after WordPress is installed will make your site think

\* it has not been installed.

\*

\* @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix

\*/

$table\_prefix = 'wp\_';

/\*\*

\* For developers: WordPress debugging mode.

\*

\* Change this to true to enable the display of notices during development.

\* It is strongly recommended that plugin and theme developers use WP\_DEBUG

\* in their development environments.

\*

\* For information on other constants that can be used for debugging,

\* visit the documentation.

\*

\* @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/

\*/

define( 'WP\_DEBUG', false );

/\* Add any custom values between this line and the "stop editing" line. \*/

/\* That's all, stop editing! Happy publishing. \*/

/\*\* Absolute path to the WordPress directory. \*/

if ( ! defined( 'ABSPATH' ) ) {

define( 'ABSPATH', \_\_DIR\_\_ . '/' );

}

/\*\* Sets up WordPress vars and included files. \*/

require\_once ABSPATH . 'wp-settings.php';
