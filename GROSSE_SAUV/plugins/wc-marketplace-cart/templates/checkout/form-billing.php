<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.9
 */

namespace OneTeamSoftware\WooCommerce\MarketplaceCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** @global WC_Checkout $checkout */

$hasCheckoutFieldsSet = MarketplaceCart::hasCheckoutFieldsSet('billing');

?>
<div class="woocommerce-billing-fields">
	<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

<h3><?php _e( 'Billing &amp; Shipping', 'wc-marketplace-cart' ); ?> <?php if (is_user_logged_in()) { ?><small><a href="#" class="woocommerce-billing-fields_change"><?php _e( 'Change', 'wc-marketplace-cart' );?></a></small><?php } ?></h3>

	<?php else : ?>

		<h3><?php _e( 'Billing details', 'wc-marketplace-cart' ); ?> <?php if (is_user_logged_in()) { ?><small><a href="#" class="woocommerce-billing-fields_change"><?php _e( 'Change', 'wc-marketplace-cart' );?></a></small><?php } ?></h3>

	<?php endif; ?>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<div class="billing_preview <?php echo $hasCheckoutFieldsSet ? 'user_logged_in' : ''; ?>">
			<div class="billing_first_name_last_name"><span class="billing_first_name"><?php echo $checkout->get_value('billing_first_name'); ?></span> <span class="billing_last_name"><?php echo $checkout->get_value('billing_last_name'); ?></span></div>
			<div class="billing_address_1"><?php echo $checkout->get_value('billing_address_1'); ?></div>
			<div class="billing_address_2"><?php echo $checkout->get_value('billing_address_2'); ?></div>
			<div class="billing_city_state_postcode"><span class="billing_city"><?php echo $checkout->get_value('billing_city'); ?></span>, <span class="billing_state"><?php echo $checkout->get_value('billing_state'); ?></span> <span class="billing_postcode"><?php echo $checkout->get_value('billing_postcode'); ?></span></div>
			<div class="billing_country"><?php echo $checkout->get_value('billing_country'); ?></div>
			<div class="billing_phone"><?php echo $checkout->get_value('billing_phone'); ?></div>
		</div>
		<div class="billing_form <?php echo $hasCheckoutFieldsSet ? 'user_logged_in' : ''; ?>">
		<?php
			$fields = $checkout->get_checkout_fields( 'billing' );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
					$field['country'] = $checkout->get_value( $field['country_field'] );
				}
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			}
		?>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>

			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ) ?> type="checkbox" name="createaccount" value="1" /> <span><?php _e( 'Create an account?', 'wc-marketplace-cart' ); ?></span>
				</label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
