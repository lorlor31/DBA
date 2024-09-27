<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
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
 * @version     8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header('shop'); ?>

<?php
	extract(etheme_get_shop_sidebar());
	$sidebarname = 'shop';
?>

<?php if ($page_heading != 'disable' && ($page_slider == 'no_slider' || $page_slider == '')): ?>
	<?php et_page_heading(); ?>
<?php endif ?>

<?php if($page_slider != 'no_slider' && $page_slider != ''): ?>

	<?php echo do_shortcode('[rev_slider_vc alias="'.$page_slider.'"]'); ?>

<?php endif; ?>

<?php $image_size_customizer = get_option('woocommerce_thumbnail_image_width');

?>

<div class="container">
	<div class="page-content sidebar-position-<?php echo esc_attr($position); ?> responsive-sidebar-<?php echo esc_attr($responsive); ?> sidebar-mobile-position-<?php echo etheme_get_option('sidebar_position_mobile'); ?>">

		<div class="row-fluid">
			<?php if($position == 'left'): ?>
				<div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-left">
					<?php do_action( 'woocommerce_sidebar' ); ?>
				</div>
			<?php endif; ?>

			<div class="content <?php echo esc_attr($content_span); ?>">


					<?php if ( woocommerce_product_loop() ) : ?>

						<?php
						/**
						 * Hook: woocommerce_shop_loop_header.
						 *
						 * @since 8.6.0
						 *
						 * @hooked woocommerce_product_taxonomy_archive_header - 10
						 */
						do_action( 'woocommerce_shop_loop_header' );
						?>

    					<?php etheme_category_header();?>


    					<?php if ( etheme_get_option('category_description_position') == "above" ) {
    					    do_action( 'woocommerce_archive_description' );
    					} ?>

							<div class="toolbar toolbar-top">
								<?php
									/**
									 * woocommerce_before_shop_loop hook
									 *
									 * @hooked woocommerce_result_count - 20
									 * @hooked woocommerce_catalog_ordering - 30
									 */
									do_action( 'woocommerce_before_shop_loop' );
								?>
								<div class="clear"></div>
							</div>

						<?php woocommerce_product_loop_start(); ?>
						<?php echo "<div class='products'>"; ?>
						<?php if ( wc_get_loop_prop( 'total' ) ) { ?>

							<?php while ( have_posts() ) : the_post(); ?>
								<?php do_action( 'woocommerce_shop_loop' ); ?>
								<?php wc_get_template_part( 'content', 'product' ); ?>

							<?php endwhile; // end of the loop. ?>

						<?php } ?>

							<?php if (etheme_get_option('product_img_hover') == 'tooltip'): ?>
								<script type="text/javascript">imageTooltip(jQuery('.imageTooltip'));</script>
							<?php endif ?>
							<?php echo "</div>"; ?>
							<div class="clear"></div>

						<?php woocommerce_product_loop_end(); ?>

						<div class="toolbar toolbar-bottom">
							<?php
								/**
								 * woocommerce_after_shop_loop hook
								 *
								 * @hooked woocommerce_pagination - 10
								 */
								do_action( 'woocommerce_after_shop_loop' );
							?>
							<div class="clear"></div>
						</div>

						<?php if ( etheme_get_option('category_description_position') == "under" ) { do_action( 'woocommerce_archive_description' ); } ?>

					<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

						<?php do_action( 'woocommerce_no_products_found' ); ?>

					<?php endif; ?>

				<?php
					/**
					 * woocommerce_after_main_content hook
					 *
					 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
					 */
					do_action('woocommerce_after_main_content');
				?>



			</div>

			<?php if($position == 'right'): ?>
				<div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-right">
					<?php do_action( 'woocommerce_sidebar' ); ?>
				</div>
			<?php endif; ?>
		</div>

	</div>
</div>
<?php get_footer('shop'); ?>
