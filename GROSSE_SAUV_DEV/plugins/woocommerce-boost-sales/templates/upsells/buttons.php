<?php
/**
 * The template for displaying buttons list inside upsells popup
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-boost-sales/upsells/buttons.php
 *
 * @see     https://villatheme.com/knowledge-base/override-templates-of-villatheme-plugins-via-a-theme/
 * @version 1.4.12
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wbs_language;
$wbs_data                 = VI_WBOOSTSALES_Data::get_instance();
$continue_shopping_action = $wbs_data->get_option( 'continue_shopping_action' );
switch ( $continue_shopping_action ) {
	case 'shop':
		$shop_url = wc_get_page_permalink( 'shop' );
		break;
	case 'home':
		$shop_url = wc_get_page_permalink( 'home' );
		break;
	case 'stay':
	default:
		$shop_url = '#';
}
$cart            = WC()->cart;
$number_cart     = $cart->get_cart_contents_count();
$total_cart      = $cart->get_cart_subtotal();
$min_amount      = $wbs_data->get_coupon( 'min' );
$select_template = $wbs_data->get_option( 'select_template' );
?>
    <a href="<?php echo esc_url( wc_get_cart_url() ) ?>"
       class="wbs-button-view"><?php esc_html_e( 'View Cart', 'woocommerce-boost-sales' ) ?></a>
    <a href="<?php echo esc_url( $shop_url ) ?>"
       class="wbs-button-continue <?php esc_attr_e( 'wbs-button-continue-' . $continue_shopping_action ) ?> <?php if ( $total_cart < $min_amount ) {
		   echo 'goto';
	   } ?>"><?php esc_html_e( $wbs_data->get_option( 'continue_shopping_title', $wbs_language ) ) ?></a>
    <a href="<?php echo esc_url( wc_get_checkout_url() ) ?>"
       class="wbs-button-check <?php if ( $total_cart >= $min_amount ) {
		   echo 'goto';
	   } ?>">
		<?php
		$checkout_text = apply_filters( 'woocommerce_boost_sales_upsells_checkout_text', esc_html__( 'Checkout', 'woocommerce-boost-sales' ) );
		echo $checkout_text;
		?>
    </a>
<?php
if ( $select_template == 2 ) {
	if ( $wbs_data->get_option( 'ajax_button' ) && is_product() ) {
		?>
        <p class="wbs-current_total_cart"></p>
		<?php
	} else {
		$temporary_number = sprintf( _n( 'Your current cart(%s product): %s', 'Your current cart(%s products): %s', $number_cart, 'woocommerce-boost-sales' ), $number_cart, $total_cart );
		?>
        <p class="wbs-current_total_cart"><?php echo $temporary_number ?></p>
		<?php
	}
}