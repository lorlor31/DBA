<?php
/**
 * Simple product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $product;

if ( ! $product->is_purchasable() ) return;

$woo_new_7_0_1_version = etheme_woo_version_check();
$button_class = '';
if ( $woo_new_7_0_1_version ) {
	$button_class = wc_wp_theme_get_element_class_name( 'button' );
}
?>

<?php
	// Availability
	$availability = $product->get_availability();

	if ( $availability['availability'] )
		echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] );
?>

<?php if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" method="post" enctype='multipart/form-data' action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>">
	 	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
		<?php do_action( 'woocommerce_before_add_to_cart_quantity' ); ?>
	 	<?php
	 		if ( ! $product->is_sold_individually() )
	 			woocommerce_quantity_input( array(
					'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
					'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
					'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
				) );
	 	?>

	 	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

		<?php do_action( 'woocommerce_after_add_to_cart_quantity' ); ?>

	 	<button type="submit" class="<?php if(etheme_get_option('ajax_addtocart')): ?>etheme-simple-product<?php endif; ?> filled big font2 single_add_to_cart_button button big alt <?php echo esc_attr( $button_class ? ' ' . $button_class : '' ); ?>">
	 	<?php echo esc_html($product->single_add_to_cart_text()); ?></button>
	 	
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>