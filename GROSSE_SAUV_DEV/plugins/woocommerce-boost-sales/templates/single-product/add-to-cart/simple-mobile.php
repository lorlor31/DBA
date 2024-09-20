<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product );
$settings = VI_WBOOSTSALES_Data::get_instance();
$action   = '';
if ( $settings->get_option( 'go_to_cart' ) && ! is_checkout() ) {
	$action = wc_get_cart_url();
}
$class = array( 'woocommerce-boost-sales-cart-form' );
if ( $hide_quantity ) {
	$class[] = 'wbs-upsells-hide-quantity';
}
if ( $product->is_in_stock() ) : ?>
    <form class="<?php echo esc_attr( implode( ' ', $class ) ) ?>" action="<?php echo esc_attr( $action ) ?>"
          method="post"
          enctype='multipart/form-data'>
		<?php
		wbs_woocommerce_quantity_input(
			array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? 1 : $product->get_min_purchase_quantity(),
			), $product
		);

		?>
        <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
                class="wbs-single_add_to_cart_button button alt"><?php echo esc_html__( 'Add to cart', 'woocommerce' ); ?></button>
    </form>
<?php endif; ?>
