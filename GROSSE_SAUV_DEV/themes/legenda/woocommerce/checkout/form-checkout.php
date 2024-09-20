<?php
/**
 * Checkout Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


global $woocommerce;

$checkoutType = etheme_get_option('checkout_page');
//$checkoutType = 'default';

if($checkoutType == 'default') {
	require_once('form-checkout-default.php');
	return;
} else if ($checkoutType == 'quick') {
	require_once('form-checkout-quick.php');
	return;
}

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'legenda' ) ) );
	return;
}

// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters( 'woocommerce_get_checkout_url', wc_get_checkout_url() ); ?>



<ul class="checkout-steps-nav">
	<?php if (!is_user_logged_in()): ?>
		<li id="tostep1"><a href="#" class="button filled active" data-step="1"><?php esc_html_e('Checkout method', 'legenda') ?></a></li>
		<?php if ( ! empty( $checkout->checkout_fields['account'] ) ) : ?>
			<li id="tostep2"><a href="#" class="button" data-step="2"><?php esc_html_e('Create an account', 'legenda') ?></a></li>
		<?php endif ?>
	<?php endif ?>
	<li id="tostep3"><a href="#" class="button <?php if (is_user_logged_in()): ?>filled active<?php endif; ?>" data-step="3"><?php esc_html_e('Billing Address', 'legenda') ?></a></li>
	<li id="tostep4"><a href="#" class="button" data-step="4"><?php esc_html_e('Shipping Address', 'legenda') ?></a></li>
	<li id="tostep5"><a href="#" class="button" data-step="5"><?php esc_html_e('Your order', 'legenda') ?></a></li>
</ul>

	<?php if ( sizeof( $checkout->checkout_fields ) > 0 ) : ?>

		<div class="checkout-steps">
			<?php if (!is_user_logged_in()): ?>	
				<div class="checkout-step active" id="step1">

					<h3 class="step-title"><?php esc_html_e('Checkout Method', 'legenda'); ?></h3>

					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div class="row-fluid">
						<div class="span5 new-customers">
							<h5><?php esc_html_e('New Customers', 'legenda') ?></h5>
							
							<p><?php esc_html_e('Register with us for future convenience: fast and easy check out, easy access to your orders history and statuses', 'legenda') ?></p>

							<form class="checkout-methods">
								<?php if ($checkout->enable_guest_checkout): ?>
			                        <div class="method-radio">
			                            <input type="radio"  id="method1" checked name="method" value="1">
			                            <label for="method1"><?php esc_html_e('Checkout as Guest', 'legenda') ?></label>
			                            <div class="clear"></div>
			                        </div>
								<?php endif ?>
		                        <div class="method-radio">
		                            <input type="radio" id="method2" <?php if (!$checkout->enable_guest_checkout): ?> checked <?php endif; ?> name="method" value="2">
		                            <label for="method2"><?php esc_html_e('Create an Account', 'legenda') ?></label>
		                            <div class="clear"></div>
		                        </div>
		                        <div class="clear"></div>
		                    </form>

		                    <button class="button active fl-r continue-checkout" data-next="<?php if ( ! empty( $checkout->checkout_fields['account'] ) ) : ?>2<?php else: ?>3<?php endif; ?>"><?php esc_html_e('Continue', 'legenda') ?></button>
							<div class="clear"></div>
						</div>

						<div class="span5 offset2">
							<h5><?php esc_html_e('Returning Customers', 'legenda') ?></h5>
							<p><?php esc_html_e('If you have shopped with us before, please enter your details in the boxes below. If you are a new customer please proceed to the Billing & Shipping section.', 'legenda') ?></p>

							<?php 
							if ( !is_user_logged_in()  ||  $checkout->enable_signup ) 
								woocommerce_login_form(
									array(
										'redirect' => get_permalink( wc_get_page_id( 'checkout') ),
										'hidden'   => false
									)
								);
							?>
						</div>
					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				</div> <!-- //step1 -->

				<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
					<?php if ( ! empty( $checkout->checkout_fields['account'] ) ) : ?>
						<div class="checkout-step" id="step2">
	
							<h3 class="step-title"><?php esc_html_e('Create an Account', 'legenda'); ?></h3>
	
							<?php if ($checkout->enable_signup ) : ?>
	
								<?php if ( $checkout->enable_guest_checkout ) : ?>

									<p class="form-row form-row-wide hidden">
										<input class="input-checkbox" id="createaccount" <?php checked($checkout->get_value('createaccount'), true) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" class="checkbox"><?php esc_html_e( 'Create an account?', 'legenda' ); ?></label>
									</p>
	
								<?php endif; ?>
	
							<?php endif; ?>
	
							<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>
	
							<div class="et-create-account">
	
								<div class="row-fluid">
									<div class="span4">
										<p><?php esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'legenda' ); ?></p>
										<p><?php echo wc_privacy_policy_text(); ?></p>
										
										<?php  foreach ($checkout->checkout_fields['account'] as $key => $field) : ?>
	
											<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
	
										<?php endforeach; ?>	
									</div>
								</div>
								
	                    		<a href="#" class="button active arrow-right fl-r continue-checkout" data-next="3"><?php esc_html_e('Continue', 'legenda') ?></a>
							
								<div class="clear"></div>
	
							</div>
	
							<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	
						</div> <!-- //step2 -->
					<?php else: ?>
							<?php if ($checkout->enable_signup ) : ?>
								<?php if ( $checkout->enable_guest_checkout ) : ?>
									<p class="form-row form-row-wide hidden">
										<input class="input-checkbox" id="createaccount" <?php checked($checkout->get_value('createaccount'), true) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" class="checkbox"><?php esc_html_e( 'Create an account?', 'legenda' ); ?></label>
									</p>
								<?php endif; ?>
							<?php endif; ?>
					<?php endif; ?>

			<?php else: ?>
				<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
			<?php endif; // first two steps only for loogged users ?>

				<div class="checkout-step <?php if (is_user_logged_in()): ?>active<?php endif; ?>" id="step3">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div> <!-- //step3 -->

				<div class="checkout-step" id="step4">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div> <!-- //step4 -->

				<div class="checkout-step" id="step5">
					<h3 class="step-title"><?php esc_html_e('Your Order', 'legenda'); ?></h3>
					<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

					<div id="order_review" class="woocommerce-checkout-review-order">
						<?php do_action( 'woocommerce_checkout_order_review' ); ?>
					</div>

					<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
				</div> <!-- //step5 -->
			</form>
		</div>

	<?php endif; ?>

	


<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>