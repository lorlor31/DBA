<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

$hover = etheme_get_option('product_img_hover');

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 3 );

// Ensure visibility
if ( ! $product->is_visible() )
	return;

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] )
	$classes[] = 'first';
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] )
	$classes[] = 'last';
?>
<div <?php wc_product_class( $classes, $product ); ?>>

		<?php 
			remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		    do_action( 'woocommerce_before_shop_loop_item' );
		    add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	    ?>

		<?php 

			if ( etheme_get_option( 'product_page_image_width' ) != '' && etheme_get_option( 'product_page_image_height' ) != '' ) {
				$image_size 	= array();
				$image_size[] 	= etheme_get_option('product_page_image_width');
				$image_size[] 	= etheme_get_option('product_page_image_height');
			} else {
				$image_size = apply_filters( 'single_product_large_thumbnail_size', 'woocommerce_thumbnail' );
			}

			$hoverUrl = '';

            if ( $hover == 'swap' ) {

            	$hoverUrl = etheme_get_custom_field( 'hover_img' );

            	if ( $hoverUrl != '' ) {
					$hoverImg = et_attach_id_from_src( $hoverUrl );
					$hoverImg = new_etheme_get_image( $hoverImg, $image_size );
					if ( $hoverImg == '' ) $hoverImg = '<img src=' . $hoverUrl . '>';
            	}

            }

		?>


		<div class="product-image-wrapper hover-effect-<?php if ( $product->get_image() || $hover == 'swap' ) echo esc_attr($hover); ?>">
			<?php etheme_wc_product_labels(); ?>
			<?php if ( has_post_thumbnail() ): ?>
				<?php
					$img_id = get_post_thumbnail_id($post->ID);
					$alt_text = get_post_meta($img_id , '_wp_attachment_image_alt', true);

					$effect = array( 'class' => '', 'extra' => '' );

					switch ( $hover ) {
						case 'slider':
							$effect['class'] = 'imageSlider';
							break;

						case 'tooltip':
							$effect['class'] = 'imageTooltip';
							break;

						case 'swap':
							$effect['class'] = ( $hoverUrl != '' ) ? 'with-hover' : '';
							break;
					}

				 ?>
				<a href="<?php the_permalink(); ?>" class="product-content-image <?php echo esc_attr($effect['class']); ?>" <?php 
					if ( $hover == 'slider' ) {
						$effect['extra'] = wp_get_attachment_image_url( $img_id, $image_size ) . ', ';
						
						$attachment_ids = $product->get_gallery_image_ids();

						foreach ( $attachment_ids as $ids ) {
							$effect['extra'] .= wp_get_attachment_image_url( $ids, $image_size ) . ', ';
						}

						$effect['extra'] = trim( $effect['extra'], ', ' );
						echo 'data-images-list="' . $effect['extra'] . '"';
					}
				?>>
					<?php
						echo ( '' != $hoverUrl ) ? $hoverImg : '';
						$hide_image = ( '' != $hoverUrl ) ? 'hide-image ' : '';
						echo wp_get_attachment_image( $img_id, $image_size, 0 , $attr = array( 'class' => $hide_image.'main-image', 'alt' => $alt_text, ) );
					?>

				</a>
			<?php else: ?>
				<a href="<?php the_permalink(); ?>" class="product-content-image">
					<?php echo ( '' != $hoverUrl ) ? $hoverImg : ''; ?>
					<?php echo wc_placeholder_img( $image_size ) ?>
				</a>
			<?php endif ?>


			<?php if ($hover == 'description'): ?>
				<div class="product-mask">
					<div class="mask-text">
						<h4><?php esc_html_e('Product description', 'legenda') ?></h4>
						<?php remove_filter('get_the_excerpt', 'et_add_hatom_data') ?>
						<?php echo trunc( get_the_excerpt(), etheme_get_option('descr_length')) ?>
						<?php add_filter('get_the_excerpt', 'et_add_hatom_data') ?>
						<p><a href="<?php the_permalink(); ?>" class="read-more-link button"><?php esc_html_e('Read More', 'legenda'); ?></a></p>
					</div>
				</div>
			<?php endif ?>

			<?php if (etheme_get_option('quick_view')): ?>
				<span class="show-quickly" data-prodid="<?php echo esc_attr($post->ID);?>" style="font-size:11px; cursor: pointer;"><?php esc_html_e('Quick View', 'legenda') ?></span>
			<?php endif ?>
		</div>
		
		<?php if (etheme_get_option('product_page_productname')): ?>
			<h3 class="product-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php endif ?>

		<?php if (etheme_get_option('product_page_cats')): ?>
			<div class="products-page-cats">
				<?php wc_get_product_tag_list( $product->get_id(), ', ' ); ?>
			</div>
		<?php endif ?>


		<?php woocommerce_template_loop_rating(); ?>
		
		<div class="product-excerpt">
			<?php the_excerpt(); ?>
		</div>
		
		<div class="add-to-container">
			
			<?php
				/**
				 * woocommerce_after_shop_loop_item_title hook
				 *
				 * @hooked woocommerce_template_loop_price - 10
				 */
				if (etheme_get_option('product_page_price')) {
					do_action( 'woocommerce_after_shop_loop_item_title' );
				}
			?>
			
	        <?php 
	        	if (etheme_get_option('product_page_addtocart')) {
	        		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
                    do_action( 'woocommerce_after_shop_loop_item' );
                    add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	        	} 
	        ?>
        </div>

	<div class="clear"></div>
</div>