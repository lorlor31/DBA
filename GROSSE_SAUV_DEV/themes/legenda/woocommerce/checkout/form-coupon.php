<?php
/**
 * Checkout coupon form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $woocommerce;

if ( ! wc_coupons_enabled() ) return;

$woo_new_7_0_1_version = etheme_woo_version_check();
$button_class = '';
if ( $woo_new_7_0_1_version ) {
	$button_class = wc_wp_theme_get_element_class_name( 'button' );
}


$info_message = apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'legenda' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'legenda' ) . '</a>' );
wc_print_notice( $info_message, 'notice' );
?>


<form class="checkout_coupon" method="post" style="display:none">

	<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'legenda' ); ?>" id="coupon_code" value="" />
	<button type="submit" class="button <?php echo esc_attr( $button_class ? ' ' . $button_class : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'legenda' ); ?>"><?php esc_html_e( 'Apply coupon', 'legenda' ); ?></button>
	<div class="clear"></div>
</form>