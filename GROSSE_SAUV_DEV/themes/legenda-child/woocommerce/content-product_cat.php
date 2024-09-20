<?php
/**
 * The template for displaying product category thumbnails within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product_cat.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version 4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( trim($woocommerce_loop['columns']) ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Increase loop count
$woocommerce_loop['loop']++;

if ( etheme_get_option( 'product_page_image_width' ) != '' && etheme_get_option( 'product_page_image_height' ) != '' ) {
	$image_size 	= array();
	$image_size[] 	= etheme_get_option('product_page_image_width');
	$image_size[] 	= etheme_get_option('product_page_image_height');
} else {
	$image_size = apply_filters( 'single_product_large_thumbnail_size', 'shop_catalog' );
}

remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );																								 
?>
<?php
if (! in_array($category->term_id, array(173,175,176,177,350,351,570,853))){
	echo '<div class=" product-category ';
	if ( !isset($category_in_carousel) ) {
        echo 'span4';
    }		  
		if ( ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] == 0 || $woocommerce_loop['columns'] == 1)
			echo ' first';
		if ( $woocommerce_loop['loop'] % $woocommerce_loop['columns'] == 0 )
			echo ' last';
	echo '">';
	do_action( 'woocommerce_before_subcategory', $category );
	do_action( 'woocommerce_before_subcategory_title', $category );
	echo '<a href="' . get_term_link( $category->slug, 'product_cat' ) . '"><div class="title_h5_cat_pdt">';
		echo esc_html($category->name);
		if ( $category->count > 0 )
				echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $category->count . ')', $category );
	echo '</div></a>';
	/**
	 * woocommerce_after_subcategory_title hook
	 */
	do_action( 'woocommerce_after_subcategory_title', $category );
	do_action( 'woocommerce_after_subcategory', $category );
	echo '</div>';
}
add_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
add_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
add_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );
?>
