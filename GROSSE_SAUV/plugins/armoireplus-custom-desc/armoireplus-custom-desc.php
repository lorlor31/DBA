<?php
/**
 * Plugin Name: Armoire plus - Custom product desc
 * Description: Add custom desc under product title on category page. Allow personalized descriptions according to the category displayed
 * Version: 1.1
 * Author:      Reuhno
 * Author URI:  http://www.reuhno.fr/
 * License:     GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright SARL LA BOITE A RE 2022
 *
 */
 
 
 
// Standard plugin security, keep this line in place.
 
 if ( !defined('ABSPATH') ) {
	 header( 'HTTP/1.1 403 Forbidden' );
	 exit(   'HTTP/1.1 403 Forbidden' );
 }




 add_action( 'armoireplus_product_desc_with_context', 'armoireplus_custom_product_desc', 10 );
 
 if(!function_exists('armoireplus_custom_product_desc')){
	 function armoireplus_custom_product_desc(){
		 if ( is_product_category() ) {
			 $current_category_object = get_queried_object();
                 if(is_object($current_category_object) && isset($current_category_object->slug)){
                     global $product;
                     $caract = get_post_meta($product->get_id(), 'catalogue_details_'.$current_category_object->slug, true);
                     if ( !empty($caract) ) {
                      echo '<div class="armoireplus-custom-desc-box">';
                      echo $caract;
                      echo '</div>';
                     }  
            }
		 }
	 }
 }
 
 
 
 
 add_action('init', 'armoireplus_product_desc_register_script');
 
 function armoireplus_product_desc_register_script() {
     wp_register_style( 'armoireplus_product_desc_css', plugins_url('/css/armoireplus-custom-desc.css', __FILE__), false, '1.0.0', 'all');
 }

  
 
 // use the registered jquery and style above
 add_action('wp_enqueue_scripts', 'armoireplus_product_desc_enqueue_style');
 
 function armoireplus_product_desc_enqueue_style(){
    wp_enqueue_style( 'armoireplus_product_desc_css' );
 }