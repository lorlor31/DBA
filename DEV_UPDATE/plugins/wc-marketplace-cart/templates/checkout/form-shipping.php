<?php
/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
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

$hasCheckoutFieldsSet = MarketplaceCart::hasCheckoutFieldsSet('shipping');
?>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span><?php _e( 'Ship to a different address?', 'wc-marketplace-cart' ); ?></span>  <?php if (is_user_logged_in()) { ?><small><a href="#" class="woocommerce-shipping-fields_change"><?php esc_html_e('Change', 'wc-marketplace-cart'); ?></a><?php } ?></small>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<div class="shipping_preview <?php echo $hasCheckoutFieldsSet ? 'user_logged_in' : ''; ?>">
					<div class="shipping_first_name_last_name"><span class="shipping_first_name"><?php echo $checkout->get_value('shipping_first_name'); ?></span> <span class="shipping_last_name"><?php echo $checkout->get_value('shipping_last_name'); ?></span></div>
					<div class="shipping_address_1"><?php echo $checkout->get_value('shipping_address_1'); ?></div>
					<div class="shipping_address_2"><?php echo $checkout->get_value('shipping_address_2'); ?></div>
					<div class="shipping_city_state_postcode"><span class="shipping_city"><?php echo $checkout->get_value('shipping_city'); ?></span>, <span class="shipping_state"><?php echo $checkout->get_value('shipping_state'); ?></span> <span class="shipping_postcode"><?php echo $checkout->get_value('shipping_postcode'); ?></span></div>
					<div class="shipping_country"><?php echo $checkout->get_value('shipping_country'); ?></div>
				</div>
				<div class="shipping_form <?php echo $hasCheckoutFieldsSet ? 'user_logged_in' : ''; ?>">
				<?php
					$fields = $checkout->get_checkout_fields( 'shipping' );

					foreach ( $fields as $key => $field ) {
						if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
							$field['country'] = $checkout->get_value( $field['country_field'] );
						}
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
				?>
				</div>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

	<?php endif; ?>
</div>
