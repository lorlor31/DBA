
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

	<div class="row product-info sidebar-position-<?php echo $position; ?> responsive-sidebar-<?php echo $responsive; ?>">
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
		<div class="content-area <?php echo ( $position == 'left' ) ? 'pull-right' : '' ; ?>">
			<div class="span<?php echo $images_span; ?>">
				<?php 
					if ( class_exists( 'YITH_WCMG_Frontend' ) ) {
						do_action( 'woocommerce_before_single_product_summary' );
					} else {
						woocommerce_show_product_images();
					}
				 ?>
			</div>
			<div class="span<?php echo $meta_span; ?> product_meta">
				<!-- <?php // if (etheme_get_option('show_name_on_single')): ?>
					<h2 class="product-name product_title"><?php the_title(); ?></h2>
					<?php // else : ?>
					<div style="display:none;" itemprop="name" class="product-name-hiden"><?php the_title(); ?></div>
				<?php // endif; ?> -->
				<h1 class="product-name product_title"><?php the_title(); ?></h1>


                <?php et_product_brand_image(); ?>

				<?php woocommerce_template_single_rating(); ?>
				<?php  echo do_shortcode('[wcsag_summary]'); ?>
                <?php if ( $product->is_type( array( 'simple', 'variable' ) ) && $product->get_sku() ) : ?>
                <span class="sku_wrapper">
                    <i style="color:#ff1800">Référence produit : </i><span class="sku"><?= $product->get_sku(); ?></span><br>
                    <?php
                    if ( !function_exists('export_variations') ) {
                        function export_variations( $pdt ) {
                            $variations = $pdt->get_available_variations();
                            $array = [];
                            foreach ($variations as $variation) {
                                $array[$variation['variation_id']] = trim(str_replace(['<p>','</p>'], '', $variation['variation_description']));
                            }
                            return json_encode($array, JSON_HEX_QUOT);
                        }
                    }
                    $supplier = wc_dropshipping_get_dropship_supplier_by_product_id($product->get_ID());
                    $display_css = $supplier['name'] != 'Vinco' ? 'display:none;' : '';
                    ?>
                    <div style="display:inline-block;<?= $display_css ?>">
                        <i class="sku-title">Référence VINCO : </i><span class="sku-mpn" data-mpn='<?= (!$product->has_child()) ? (woocommerce_gpf_get_element_values('mpn')[0] ?? '') : export_variations($product); ?>'><?= woocommerce_gpf_get_element_values('mpn')[0] ?? 'Sélectionner une déclinaison'; ?></span>

                    </div>
                </span>
                <?php endif; ?>

				<?php
					/**
					 * woocommerce_single_product_summary hook
					 *
					 * @hooked woocommerce_template_loop_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 */
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
					add_action( 'woocommerce_single_product_summary', 'woocommerce_template_loop_price', 10);
					do_action( 'woocommerce_single_product_summary' );
				?>
				
				<?php
					$classeF = get_post_meta( $product->get_id(), 'protect_fire', true );			
					$classeE = get_post_meta( $product->get_id(), 'protect_effractions', true );
					 if ($classeE != null && $classeE >= 0 && $classeE <= 5 && $classeF != null & $classeF >= 0 && $classeF <= 5) {
						if ($classeF == '0'){
							echo '<div class="label-protect"> <div class="protect-effraction" style="display:flex;"> <div class="protect-effraction-text"> <p style="font-size:13px;margin-top:2px;color:#6F6F6F;">Résistance effraction :</p></div> <div class="protect-effraction-img" style="margin-left:25px;"> <img src="https://www.armoireplus.fr/wp-content/uploads/2022/11/protect-effraction.label-'. $classeE .'.png" width="95" height="20" alt="résistance au effraction"> </div> </div></div>';

						}
						else {
						echo '<div class="label-protect"> <div class="protect-fire" style="display:flex;"> <div class="protect-fire-text"><hp style="font-size:13px;margin-top:2px;color:#6F6F6F;">Protection contre le feu :</p></div> <div class="protect-fire-img" style="margin-left:5px;"> <img src="https://www.armoireplus.fr/wp-content/uploads/2022/11/protect-fire.label-'. $classeF .'.png" width="95" height="20" alt="résistance au feu"> </div> </div> <div class="protect-effraction" style="display:flex;"> <div class="protect-effraction-text"> <p style="font-size:13px;margin-top:2px;color:#6F6F6F;">Résistance effraction :</p></div> <div class="protect-effraction-img" style="margin-left:25px;"> <img src="https://www.armoireplus.fr/wp-content/uploads/2022/11/protect-effraction.label-'. $classeE .'.png" width="95" height="20" alt="résistance au effraction"> </div> </div></div>';
						}
					}
				?>	
				
				

			    <?php if ( etheme_get_custom_field('size_guide_img') ) : ?>
			    	<?php $lightbox_rel = 'lightbox'; ?>
			        <div class="size_guide">
			    	 <a rel="<?php echo $lightbox_rel; ?>" href="<?php etheme_custom_field('size_guide_img'); ?>"><?php esc_html_e('SIZING GUIDE', 'legenda'); ?></a>
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
			<div class="span3 sidebar sidebar-<?php echo $position; ?> pull-<?php echo $position; ?> mobile-sidebar-<?php echo $responsive; ?> single-product-sidebar">
				<?php et_product_brand_image(); ?>
				<?php if(etheme_get_option('upsell_location') == 'sidebar') woocommerce_upsell_display(); ?>
				<?php dynamic_sidebar('single-sidebar'); ?>
			</div>
	</div>

	<?php if(etheme_get_option('show_related')) 
		echo '<div id="pdt_lies_pg_pdt">';
			woocommerce_output_related_products(12,6); 
			echo '</div>';
	?>

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
		?>
	
	<?php
	
	  	if(etheme_get_option('upsell_location') == 'after_content') woocommerce_upsell_display();
	  	// if(etheme_get_option('show_related'))
			// woocommerce_output_related_products();
	?>
	    <?php 
		echo do_shortcode('[block id="50441"]'); 
		?>
	

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
