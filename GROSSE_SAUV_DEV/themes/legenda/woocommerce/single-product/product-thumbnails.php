<?php
/**
 * Single Product Thumbnails
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-thumbnails.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product, $woocommerce;

$attachment_ids = $product->get_gallery_image_ids();

$zoom = etheme_get_option('zoom_effect');

$has_video = false;

$video_attachments = array();
$videos = et_get_attach_video( $product->get_id() ); 
//$videos = explode(',', $videos[0]);
if(isset($videos[0]) && $videos[0] != '') {
	$video_attachments = get_posts( array(
		'post_type' => 'attachment',
		'include' => $videos[0]
	) ); 
}

if(count($video_attachments)>0 || et_get_external_video( $product->get_id() ) != '') {
	$has_video = true;
}


if ( ($product->get_image() && ( $has_video || $attachment_ids)) || ( $has_video && $attachment_ids) ) {

		$loop = 0;
		$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );
		
		$image_size = apply_filters( 'single_product_small_thumbnail_size', 'shop_catalog' );

	?>
	<div class="thumbnails">
	
	<div class="product-thumbnails-slider">
			
		<ul class="slides">
			<?php if( $product->get_image() ): ?>
				<li>
					<a href="#" class="main-image">
						<?php 

							if ( has_post_thumbnail() ) :
								echo sprintf( '<img src="%s" alt="%s"/>', esc_url( get_the_post_thumbnail_url( $post->ID, 'shop_catalog' ) ), get_post_meta( $post->ID, '_wp_attachment_image_alt', true ) );
							else :
								echo sprintf( '<img src="%s" alt="%s"/>', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'legenda' ) );
							endif;

						?>
					</a>
				</li>
				<?php if ($attachment_ids): ?>
					<?php foreach ($attachment_ids as $key => $value): ?>
						<?php

						$full_size_image = wp_get_attachment_image_src( $value, 'full' );
						$attributes      = array(
						    'title'                   => get_post_field( 'post_title', $value ),
						    'data-caption'            => get_post_field( 'post_excerpt', $value ),
						);

						if ($full_size_image){
							$attributes['data-src'] = $full_size_image[0];
							$attributes['data-large_image'] = $full_size_image[0];
							$attributes['data-large_image_width'] = $full_size_image[1];
							$attributes['data-large_image_height'] = $full_size_image[2];
                        }

						 ?>
						<?php $value = wp_get_attachment_image_url( $value, $image_size, false, $attributes ); ?>
						<li><a href="#" class="main-image"><img src="<?php echo esc_url($value); ?>" ></a></li>
					<?php endforeach ?>
				<?php endif ?>
			<?php endif; ?>
			
			<?php if(et_get_external_video( $product->get_id() )): ?>
				<li class="video-thumbnail">
					<span><?php _e('Video', 'legenda'); ?></span>
				</li>
			<?php endif; ?>
			
			<?php if(count($video_attachments)>0): ?>
				<li class="video-thumbnail">
					<span><?php _e('Video', 'legenda'); ?></span>
				</li>
			<?php endif; ?>
		</ul>
	</div>

			
	<script type="text/javascript">
	"use strict";

		jQuery(document).ready(function($) {
			
		  var $window = jQuery(window),
		      flexslider = { vars:{} };
		 
		  function getGridSize() {
		    return (window.innerWidth < 600) ? 3 :
		           (window.innerWidth < 1200) ? 3 : 3;
		  }
		 		 
		  jQuery('.product-thumbnails-slider').flexslider({
						animation: "slide",
						slideshow: false,
						animationLoop: false,
						controlNav: true,
						directionNav:true,
						itemWidth:120,
						itemMargin:30,
						minItems: getGridSize(),
		      			maxItems: getGridSize(),
						asNavFor: '.main-image-slider'
					});
		 
		  $window.resize(function() {
		    var gridSize = getGridSize();
		 
		    flexslider.vars.minItems = gridSize;
		    flexslider.vars.maxItems = gridSize;
		  });

		});
	</script>
</div>
	<?php
}