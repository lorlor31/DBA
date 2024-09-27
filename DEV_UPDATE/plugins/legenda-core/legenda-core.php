<?php
/*
Plugin Name: Legenda Core
Plugin URI: http://8theme.com
Author: 8theme
Author URI: http://themeforest.net/user/8theme
Description: Legenda Core Plugin for Legenda theme
Version: 1.2.9
Text Domain: legenda-core
*/


if( !defined( 'WPINC' ) ) {
    die();
}


$theme = wp_get_theme(get_option('template'));


if ( $theme->get('Name') != 'Legenda' || ( $theme->get('Name') != 'Legenda' && get_option('template') != 'legenda' ) ) {
    return false;
}

include 'inc/plugin-compatibilty.php';

if ( !legenda_plugin_compatible() ) {
    return; 
}

/**
 * Load functions.
 * 
 * @since 1.0
 */
include 'inc/functions.php';

/**
 * Load shortcodes.
 * 
 * @since 1.0
 */
include 'inc/shortcodes.php';

/**
 * Load post-types.
 * 
 * @since 1.0
 */
include 'inc/post-types.php';

/**
 * Load plugin testimonials.
 * Do it to prevent errors with old 8theme themes
 * @since 1.1
 */

add_action( 'after_setup_theme', 'legenda_load_thirdparty', 999 );

function legenda_load_thirdparty() {

    if ( function_exists('etheme_get_option') && etheme_get_option( 'enable_testimonials' ) ) {
        include 'inc/testimonials/woothemes-testimonials.php';
    }

    if ( ! class_exists( 'TwitterOAuth' ) ) {
		include 'inc/twitteroauth/twitteroauth.php';
	}

}

/**
 * Load widgets.
 * 
 * @since 1.0
 */
include 'inc/widgets.php';

if ( is_admin() ) {
	/**
	 * Load import.
	 * 
	 * @since 1.0
	 */
	include 'inc/import.php';
	} 
