<?php
/**
 * Single Product Up-Sells
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version      3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce, $woocommerce_loop;

$upsells = $product->get_upsell_ids();

if ( sizeof( $upsells ) == 0 ) return;

$meta_query = $woocommerce->query->get_meta_query();

$args = array(
	'post_type'           => array( 'product', 'product_variation' ),
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => 1,
	'posts_per_page'      => $posts_per_page,
	'orderby'             => $orderby,
	'post__in'            => $upsells,
	'post__not_in'        => array( $product->get_id() ),
	'meta_query'          => $meta_query
);

$sidebar_slider = false;

if( etheme_get_option( 'upsell_location' ) == 'sidebar' ) {
	etheme_create_flex_slider( $args, esc_html__( 'Our offers', 'legenda' ), false, true );
} else {
	$slider_args = array(
		'title' => esc_html__( 'Our offers', 'legenda') 
	);
	etheme_create_slider( $args, $slider_args );
}


wp_reset_postdata();