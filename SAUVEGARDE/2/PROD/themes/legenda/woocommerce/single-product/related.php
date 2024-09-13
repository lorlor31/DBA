<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

// updated for woocommerce v3.0
$related = array_map( 'absint', array_values( wc_get_related_products( $product->get_id(), 10 ) ) );

if ( sizeof( $related ) == 0 ) return;

if ( etheme_get_option('hide_out_of_stock') ) {
	$args = apply_filters( 'woocommerce_related_products_args', array(
		'post_type'				=> 'product',
		'ignore_sticky_posts'	=> 1,
		'no_found_rows' 		=> 1,
		'posts_per_page' 	    => 30,
		'orderby' 				=> $orderby,
		'post__in' 				=> $related,
		'post__not_in'			=> array( $product->get_id() ),
		'meta_key'             => '_stock_status',
		'meta_value'           => 'instock',
		'compare'              => '!='
	) );
} else {
	$args = apply_filters( 'woocommerce_related_products_args', array(
		'post_type'				=> 'product',
		'ignore_sticky_posts'	=> 1,
		'no_found_rows' 		=> 1,
		'posts_per_page' 	    => 30,
		'orderby' 				=> $orderby,
		'post__in' 				=> $related,
		'post__not_in'			=> array( $product->get_id() ),
	) );
}

$slider_args = array(
	'title' => esc_html__( 'Related Products', 'legenda' )
);

etheme_create_slider( $args, $slider_args );

wp_reset_postdata();
