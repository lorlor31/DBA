<?php
/**
 * The template for displaying main content of upsells popup
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-boost-sales/upsells/popup-main.php
 *
 * @see     https://villatheme.com/knowledge-base/override-templates-of-villatheme-plugins-via-a-theme/
 * @version 1.4.12
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wbs_data = VI_WBOOSTSALES_Data::get_instance();
global $wbs_language, $wbs_upsell_count;
$message_bought  = $wbs_data->get_option( 'message_bought', $wbs_language );
$select_template = $wbs_data->get_option( 'select_template' );

$main_product = wc_get_product( $product_id );
//		$product_image    = woocommerce_get_product_thumbnail();
$product_image    = VI_WBOOSTSALES_Upsells::get_product_image( $main_product );
$product_title    = $main_product->get_name();
$main_product_url = $main_product->get_permalink();
$added_to_cart    = VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart;
if ( isset( $added_to_cart[ $product_id ] ) && $added_to_cart[ $product_id ]['quantity'] && $added_to_cart[ $product_id ]['price'] !== false ) {
	$quantity      = $added_to_cart[ $product_id ]['quantity'];
	$total_product = wc_price( $added_to_cart[ $product_id ]['price'] * $added_to_cart[ $product_id ]['quantity'] );
	if ( $added_to_cart[ $product_id ]['variation'] ) {
		$product_title .= ' - ' . implode( ', ', $added_to_cart[ $product_id ]['variation'] );
	}
	if ( $added_to_cart[ $product_id ]['variation_id'] ) {
		$variation = wc_get_product( $added_to_cart[ $product_id ]['variation_id'] );
		if ( $variation ) {
			$variation_image = VI_WBOOSTSALES_Upsells::get_product_image( $variation );
			if ( $variation_image ) {
				$product_image = $variation_image;
			}
			$main_product_url = $variation->get_permalink();
		}
	}
} else {
	$upsell_price = wc_get_price_to_display( $main_product );
	if ( ! is_numeric( $upsell_price ) ) {
		$upsell_price = floatval( $upsell_price );
	}
	$total_product = wc_price( $upsell_price * $quantity );

	if ( $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( $variation ) {
			$variation_image = VI_WBOOSTSALES_Upsells::get_product_image( $variation );
			if ( $variation_image ) {
				$product_image = $variation_image;
			}
			$product_title    = $variation->get_name();
			$main_product_url = $variation->get_permalink();
			$upsell_price     = wc_get_price_to_display( $variation );
			if ( ! is_numeric( $upsell_price ) ) {
				$upsell_price = floatval( $upsell_price );
			}
			$total_product = wc_price( $upsell_price * $quantity );
		}
	}
}
if ( $cart_item_key && class_exists( 'WC_PB_Display' ) ) {
	$pb = WC_PB_Display::instance();
	if ( ! $cart ) {
		$cart = wc()->cart;
	}
	$total_product = $pb->cart_item_price( $total_product, $cart->get_cart_item( $cart_item_key ), $cart_item_key );
}
?>
<div class="wbs-content-inner">
    <span class="wbs-close"
          title="<?php esc_html_e( 'Close', 'woocommerce-boost-sales' ) ?>"><span>X</span></span>
    <div class="wbs-breadcrum">
		<?php
		if ( $select_template == 1 ) {
			?>
            <p class="wbs-notify_added wbs-title_style1">
                <span class="wbs-icon-added"></span> <?php $wbs_data->get_option( 'ajax_button' ) ? esc_html_e( 'New item(s) have been added to your cart.', 'woocommerce-boost-sales' ) : printf( _n( '<span class="wbs-notify_added-quantity">%s</span>  new item has been added to your cart', '<span class="wbs-notify_added-quantity">%s</span>  new items have been added to your cart', $quantity, 'woocommerce-boost-sales' ), $quantity ); ?>
            </p>
			<?php
		}
		?>
        <div class="wbs-header-right">
			<?php wbs_get_template( 'upsells/buttons.php', array(
				'select_template' => $select_template,
				'wbs_data'        => $wbs_data,
				'wbs_language'    => $wbs_language,
			), '', VI_WBOOSTSALES_TEMPLATES ); ?>
        </div>
        <div class="wbs-product">
			<?php
			if ( $select_template == 2 ) { ?>
                <p class="wbs-notify_added wbs-title_style2">
                    <span class="wbs-icon-added"></span> <?php $wbs_data->get_option( 'ajax_button' ) ? esc_html_e( 'New item(s) have been added to your cart.', 'woocommerce-boost-sales' ) : printf( _n( '<span class="wbs-notify_added-quantity">%s</span>  new item has been added to your cart', '<span class="wbs-notify_added-quantity">%s</span>  new items have been added to your cart', $quantity, 'woocommerce-boost-sales' ), $quantity ) ?>
                </p>
				<?php
			}
			?>
            <div class="wbs-p-image">
				<?php
				if ( $main_product_url ) {
					?>
                    <a href="<?php echo $main_product_url ?>" class="wbs-p-url"
                       target="_self"><?php echo $product_image; ?></a>
					<?php
				} else {
					echo $product_image;
				}
				?>
            </div>
			<?php
			if ( $select_template == 2 ) {
				echo '<div class="wbs-combo_popup_style2 wbs-added-products-list">';
			}
			?>
            <div class="wbs-p-title">
				<?php
				if ( $main_product_url ) {
					?>
                    <a href="<?php echo $main_product_url ?>" class="wbs-p-url"
                       target="_self"><?php echo $product_title; ?></a>
					<?php
				} else {
					echo $product_title;
				}
				?>
            </div>
            <div class="wbs-p-price">
                <div class="wbs-p-quantity">
                    <span class="wbs-p-quantity-text"><?php esc_html_e( 'Quantity:', 'woocommerce-boost-sales' ); ?></span>
                    <span class="wbs-p-quantity-number"
                          style="float: none;"><?php echo esc_html( $quantity ); ?></span>
                </div>
                <div class="wbs-price-total">
                    <div class="wbs-total-price"><?php esc_html_e( 'Total', 'woocommerce-boost-sales' ) ?>
                        <span class="wbs-money"
                              style="float: none;"><?php echo $total_product; ?></span>
                    </div>
                </div>
            </div>
			<?php
			if ( $select_template == 2 ) {
				echo '</div>';
			}
			?>
        </div>
    </div>
	<?php
	if ( $wbs_upsell_count > 0 ) {
		?>
        <div class="wbs-bottom">
			<?php
			if ( $message_bought ) {
				?>
                <h3 class="upsell-title"><?php echo str_replace( '{name_product}', $product_title, strip_tags( $message_bought ) ); ?></h3>
                <hr/>
				<?php
			}
			/*upsells list here*/
			echo $upsells_list;
			do_action( 'woocommerce_boost_sales_after_upsells_list', $main_product, $upsells );
			?>
        </div>
		<?php
	}
	?>
</div>