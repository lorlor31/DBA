<?php

// **********************************************************************//
// ! Add Shortcodes Buttons to editor
// **********************************************************************//

if ( ! function_exists( 'etheme_add_buttons_shortcodes' ) ) :
function etheme_add_buttons_shortcodes() {
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
   if ( get_user_option('rich_editing') == 'true') {
     add_filter('mce_external_plugins', 'shortcodes_tinymce_plugin');
     add_filter('mce_buttons_3', 'register_shortcodes_button');
   }
}
endif;

if ( ! function_exists( 'etheme_add_buttons2_shortcodes' ) ) :
function etheme_add_buttons2_shortcodes() {
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
   if ( get_user_option('rich_editing') == 'true') {
     add_filter('mce_external_plugins', 'shortcodes_tinymce_plugin2');
     add_filter('mce_buttons_4', 'register_shortcodes_button2');
   }
}
endif;

add_action('init', 'etheme_add_buttons_shortcodes');
add_action('init', 'etheme_add_buttons2_shortcodes');

if ( ! function_exists( 'register_shortcodes_button' ) ) :
function register_shortcodes_button($buttons) {
   array_push($buttons, "et_featured", "et_new_products", "et_button", "et_blockquote", "et_list", "eth_dropcap", "et_alert", "et_progress", "et_ptable");
   return $buttons;
}
endif;

if ( ! function_exists( 'shortcodes_tinymce_plugin' ) ) :
function shortcodes_tinymce_plugin($plugin_array) {
   if(class_exists('WooCommerce')){
	   $plugin_array['et_featured'] = $plugin_array['et_new_products'] = get_template_directory_uri().'/framework/js/editor_plugin.js';
   }
   $plugin_array['et_button'] = 
   $plugin_array['et_blockquote'] = 
   $plugin_array['et_list'] = 
   $plugin_array['eth_dropcap'] = 
   $plugin_array['et_alert'] = 
   $plugin_array['et_progress'] = 
   $plugin_array['et_ptable'] = get_template_directory_uri().'/framework/js/editor_plugin.js';

   return $plugin_array;
}
endif;

if ( ! function_exists( 'register_shortcodes_button2' ) ) :
function register_shortcodes_button2($buttons) {
   array_push($buttons, "et_row", "et_column1_2", "et_column1_3", "et_column1_4", "et_column3_4", "et_column2_3", "et_tabs", "et_gmaps", "et_icon", "et_tm");
   return $buttons;
}
endif;

if ( ! function_exists( 'shortcodes_tinymce_plugin2' ) ) :
function shortcodes_tinymce_plugin2($plugin_array) {
   $plugin_array['et_row'] = 
   $plugin_array['et_column1_2'] = 
   $plugin_array['et_column1_3'] = 
   $plugin_array['et_column2_3'] = 
   $plugin_array['et_column1_4'] = 
   $plugin_array['et_column3_4'] = 
   $plugin_array['et_tabs'] = 
   $plugin_array['et_gmaps'] = 
   $plugin_array['et_icon'] = 
   $plugin_array['et_tm'] = get_template_directory_uri().'/framework/js/editor_plugin.js';

   return $plugin_array;
}
endif;

if ( ! function_exists( 'etheme_refresh_mce' ) ) :
function etheme_refresh_mce($ver) {
  $ver += 3;
  return $ver;
}
endif;

add_filter( 'tiny_mce_version', 'etheme_refresh_mce');

?>
