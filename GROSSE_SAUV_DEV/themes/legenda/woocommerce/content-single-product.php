
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
	global $product;
	extract(etheme_get_single_product_sidebar());
?>

<?php
	/**
	 * Single Product Content
	 *
	 * @author 		WooThemes
	 * @package 	WooCommerce/Templates
	 * @version     3.6.0
	 */
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked wc_print_notices - 10
	 */
	 do_action( 'woocommerce_before_single_product' );
?>

<?php $tabs_position = ( etheme_get_option( 'tabs_position' ) == 'tabs-under' ) ? 'under' : 'inside'; ?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('single-product-page', $product); ?>>

	<div class="row product-info sidebar-position-<?php echo esc_attr($position); ?> responsive-sidebar-<?php echo esc_attr($responsive); ?>">
		<?php
			/**
			 * woocommerce_before_single_product_summary hook
			 *
			 * @hooked woocommerce_show_product_sale_flash - 10
			 * @hooked woocommerce_show_product_images - 20
			 */

			if ( ! class_exists( 'YITH_WCMG_Frontend' ) ) {
				do_action( 'woocommerce_before_single_product_summary' );
			}
		?>
		<div class="content-area <?php if ( $position == 'left' ) { echo esc_attr('pull-right'); } ?>">
			<div class="span<?php echo esc_attr($images_span); ?>">
				<?php 
					if ( class_exists( 'YITH_WCMG_Frontend' ) ) {
						do_action( 'woocommerce_before_single_product_summary' );
					} else {
						woocommerce_show_product_images();
					}
				 ?>
			</div>
			<div class="span<?php echo esc_attr($meta_span); ?> product_meta">
				<?php if (etheme_get_option('show_name_on_single')): ?>
					<h2 class="product-name product_title"><?php the_title(); ?></h2>
				<?php else : ?>
					<div style="display:none;" itemprop="name" class="product-name-hiden"><?php the_title(); ?></div>
				<?php endif; ?>
				<h4><?php esc_html_e('Product Information', 'legenda') ?></h4>

				<?php woocommerce_template_single_rating(); ?>

				<?php if ( $product->is_type( array( 'simple', 'variable' ) ) && $product->get_sku() ) : ?>
					<span itemprop="productID" class="sku_wrapper"><?php esc_html_e( 'Product code', 'legenda' ); ?>: <span class="sku"><?php echo esc_html($product->get_sku()); ?></span></span>
				<?php endif; ?>
				
				<?php $category_title = ( count( $product->get_category_ids() ) == 1 ) ? esc_html__( 'Category:', 'legenda' ) : esc_html__( 'Categories:', 'legenda' ); ?>

				<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . $category_title . ' ', '</span>' ); ?>

				<?php
					/**
					 * woocommerce_single_product_summary hook
					 *
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 */
					do_action( 'woocommerce_single_product_summary' );
				?>

			    <?php if ( etheme_get_custom_field('size_guide_img') ) : ?>
			        <div class="size_guide">
			    	 <a rel="lightbox" href="<?php etheme_custom_field('size_guide_img'); ?>"><?php esc_html_e('SIZING GUIDE', 'legenda'); ?></a>
			        </div>
			    <?php endif; ?>

				<?php woocommerce_template_single_add_to_cart(); ?>

				<?php woocommerce_template_single_meta(); ?>

	            <?php if(etheme_get_option('share_icons')) echo do_shortcode('[share text="'.get_the_title().'"]'); ?>

				<?php woocommerce_template_single_sharing(); ?>

			</div>

			<?php if ( $tabs_position == 'inside' && etheme_get_option( 'tabs_position' ) != 'tabs-disable' ) : ?>
				<div class="tabs-under-product">
					<?php woocommerce_output_product_data_tabs(); ?>
				</div>
			<?php endif; ?>
		</div>
			<div class="span3 sidebar sidebar-<?php echo esc_attr($position); ?> pull-<?php echo esc_attr($position); ?> mobile-sidebar-<?php echo esc_attr($responsive); ?> single-product-sidebar">
				<?php et_product_brand_image(); ?>
				<?php if(etheme_get_option('upsell_location') == 'sidebar') woocommerce_upsell_display(); ?>
				<?php dynamic_sidebar('single-sidebar'); ?>
			</div>
	</div>

	<?php

		if ( $tabs_position != 'inside' && etheme_get_option( 'tabs_position' ) != 'tabs-disable' ) {
			woocommerce_output_product_data_tabs();
		}

		if(etheme_get_custom_field('additional_block') != '') {
			echo '<div class="sidebar-position-without">';
			if ( etheme_get_option('enable_static_blocks') == 'off' ) {
                echo '<p>' . esc_html__( 'to use this widget enable "static blocks" in the module section of theme options', 'legenda' ) . '</p>';
            } else {
            	et_show_block(etheme_get_custom_field('additional_block'));
            }
			echo '</div>';
		}

	  	if(etheme_get_option('upsell_location') == 'after_content') woocommerce_upsell_display();
	  	if(etheme_get_option('show_related'))
			woocommerce_output_related_products();
	?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
