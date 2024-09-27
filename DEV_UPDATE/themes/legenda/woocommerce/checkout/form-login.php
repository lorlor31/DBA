<?php
/**
 * Checkout login form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_user_logged_in()  || ! $checkout->enable_signup ) return;

$info_message = apply_filters( 'woocommerce_checkout_login_message', esc_html__( 'Returning customer?', 'legenda' ) );
?>

<p><?php echo esc_html( $info_message ); ?> <a href="#" class="showlogin"><?php esc_html_e( 'Click here to login', 'legenda' ); ?></a></p>

<?php
	woocommerce_login_form(
		array(
			'message'  => esc_html__( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer please proceed to the Billing &amp; Shipping section.', 'legenda' ),
			'redirect' => wc_get_checkout_url(),
			'hidden'   => true
		)
	);
?>