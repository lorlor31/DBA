<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'et-font-awesome',get_stylesheet_directory_uri().'/css/font-awesome.css', array( 'fonts' ) );

    if ( is_rtl() ) {
        wp_enqueue_style("rtl-style",get_stylesheet_directory_uri().'/rtl.css', array('parent-style') );
    }
}