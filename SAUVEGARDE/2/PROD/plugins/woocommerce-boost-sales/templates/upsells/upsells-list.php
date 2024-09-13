<?php
/**
 * The template for displaying list of upselling products in the upsells popup
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-boost-sales/upsells/upsells-list.php
 *
 * @see     https://villatheme.com/knowledge-base/override-templates-of-villatheme-plugins-via-a-theme/
 * @version 1.4.12
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wbs_upsell_count;
$wbs_upsell_count = 0;
$wbs_data         = VI_WBOOSTSALES_Data::get_instance();
$upsell_item_link = $wbs_data->get_option( 'upsell_item_link' );
$item_per_row     = $wbs_data->get_option( 'item_per_row' );
$get_detect       = $wbs_data->get_detect();
$atc_style        = $get_detect === 'mobile' ? $wbs_data->get_option( 'add_to_cart_style_mobile' ) : $wbs_data->get_option( 'add_to_cart_style' );
if ( $get_detect === 'mobile' && $wbs_data->get_option( 'upsell_mobile_template' ) === 'scroll' ) {
	?>
    <div class="wbs-upsells-items wbs-upsells-items-mobile">
		<?php
		foreach ( $upsells as $upsell_id ) {
			$upsell_product = wc_get_product( $upsell_id );
			if ( $upsell_product ) {
				if ( ! $upsell_product->is_in_stock() && $wbs_data->get_option( 'hide_out_stock' ) ) {
					continue;
				}
				$wbs_upsell_count ++;
				?>
                <div class="wbs-upsells-item">
                    <div class="wbs-upsells-item-main">
                        <div class="wbs-upsells-item-left">
							<?php
							$product_url = $upsell_product->get_permalink();
							if ( $product_url && $upsell_item_link !== 'off' ) {
								?>
                                <a href="<?php echo esc_url( $product_url ) ?>"
                                   target="<?php echo esc_attr( $upsell_item_link === 'new_tab' ? '_blank' : '_self' ) ?>"
                                   class="wbs-upsells-item-url">
									<?php
									do_action( 'woocommerce_boost_sales_before_shop_loop_item_title', $upsell_product );
									?>
                                </a>
								<?php
							} else {
								do_action( 'woocommerce_boost_sales_before_shop_loop_item_title', $upsell_product );
							}
							?>
                        </div>
                        <div class="wbs-upsells-item-right">
							<?php
							if ( $product_url && $upsell_item_link !== 'off' ) {
								?>
                                <a href="<?php echo esc_url( $product_url ) ?>"
                                   target="<?php echo esc_attr( $upsell_item_link === 'new_tab' ? '_blank' : '_self' ) ?>"
                                   class="wbs-upsells-item-url">
									<?php
									do_action( 'woocommerce_boost_sales_shop_loop_item_title', $upsell_product );
									?>
                                </a>
								<?php
							} else {
								do_action( 'woocommerce_boost_sales_shop_loop_item_title', $upsell_product );
							}
							do_action( 'woocommerce_boost_sales_after_shop_loop_item_title', $upsell_product );
							?>
                            <div class="product-controls">
                                <div class="wbs-cart">
									<?php do_action( 'woocommerce_boost_sales_single_product_summary_mobile', $upsell_product ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wbs-upsells-item-added-to-cart">
                        <span class="wbs-icon-added"></span>
						<?php printf( __( '"%s" has been added to your cart', 'woocommerce-boost-sales' ), get_the_title(), 'woocommerce-boost-sales' ) ?>
                    </div>
                </div>
				<?php
			}
		}
		?>
    </div>
	<?php
} else {
	$atc_style = $get_detect === 'mobile' ? $wbs_data->get_option( 'add_to_cart_style_mobile' ) : $wbs_data->get_option( 'add_to_cart_style' );
	?>
    <div class="vi-flexslider" id="flexslider-up-sell"
         data-rtl="<?php echo esc_attr( is_rtl() ? 1 : 0 ) ?>"
         data-item-per-row="<?php echo esc_attr( $item_per_row ); ?>"
         data-item-per-row-mobile="<?php echo esc_attr( $wbs_data->get_option( 'item_per_row_mobile' ) ); ?>">
        <div class="wbs-upsells wbs-vi-slides <?php echo esc_attr( "wbs-upsells-atc-style-{$atc_style}" ) ?>">
			<?php
			foreach ( $upsells as $upsell_id ) {
				$upsell_product = wc_get_product( $upsell_id );
				if ( $upsell_product ) {
					if ( ! $upsell_product->is_in_stock() && $wbs_data->get_option( 'hide_out_stock' ) ) {
						continue;
					}
					$wbs_upsell_count ++;
					$product_url = $upsell_product->get_permalink();
					?>
                    <div class="vi-wbs-chosen wbs-variation wbs-product">
                        <div class="wbs-upsells-add-items"></div>
                        <div class="product-top">
							<?php
							if ( $product_url && $upsell_item_link !== 'off' && $atc_style !== 'hover' ) {
								?>
                                <a href="<?php echo esc_url( $product_url ) ?>"
                                   target="<?php echo esc_attr( $upsell_item_link === 'new_tab' ? '_blank' : '_self' ) ?>"
                                   class="wbs-upsells-item-url">
									<?php
									do_action( 'woocommerce_boost_sales_before_shop_loop_item_title', $upsell_product );
									?>
                                </a>
								<?php
							} else {
								do_action( 'woocommerce_boost_sales_before_shop_loop_item_title', $upsell_product );
							}
							?>
                        </div>
                        <div class="product-desc">
							<?php
							if ( $product_url && $upsell_item_link !== 'off' && $get_detect !== 'mobile' ) {
								?>
                                <a href="<?php echo esc_url( $product_url ) ?>"
                                   target="<?php echo esc_attr( $upsell_item_link === 'new_tab' ? '_blank' : '_self' ) ?>"
                                   class="wbs-upsells-item-url">
									<?php
									do_action( 'woocommerce_boost_sales_shop_loop_item_title', $upsell_product );
									?>
                                </a>
								<?php
							} else {
								do_action( 'woocommerce_boost_sales_shop_loop_item_title', $upsell_product );
							}
							do_action( 'woocommerce_boost_sales_after_shop_loop_item_title', $upsell_product );
							?>
                        </div>
						<?php
						if ( $atc_style !== 'hide' ) {
							?>
                            <div class="product-controls">
                                <div class="wbs-cart">
									<?php do_action( 'woocommerce_boost_sales_single_product_summary', $upsell_product ) ?>
                                </div>
                            </div>
							<?php
						}
						do_action( 'woocommerce_boost_sales_upsells_slider_item_end', $upsell_product );
						?>
                    </div>
					<?php
				}
			}
			?>
        </div>
    </div>
	<?php
}