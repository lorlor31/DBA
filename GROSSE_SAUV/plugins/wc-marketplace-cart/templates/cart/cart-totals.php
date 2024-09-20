<?php
/**
 * Cart totals
 *
 * Customized Cart totals template based on version 2.3.6
 *
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.0.0
 */

defined('ABSPATH') || exit;

?>
<div class="cart_totals <?php echo (WC()->customer->has_calculated_shipping()) ? 'calculated_shipping' : ''; ?>">

	<?php do_action('woocommerce_before_cart_totals'); ?>

	<table cellspacing="0" class="shop_table shop_table_responsive">
		<tr>
			<th colspan="2"><?php _e('Order summary', 'wc-marketplace-cart'); ?></th>
		</tr>

		<tr class="cart-subtotal">
			<td><?php _e('Subtotal', 'wc-marketplace-cart'); ?></td>
			<td data-title="<?php esc_attr_e('Subtotal', 'wc-marketplace-cart'); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
				<td><?php wc_cart_totals_coupon_label($coupon); ?></td>
				<td data-title="<?php echo esc_attr(wc_cart_totals_coupon_label($coupon, false)); ?>"><?php wc_cart_totals_coupon_html($coupon); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

			<?php do_action('woocommerce_cart_totals_before_shipping'); ?>

			<?php do_action('woocommerce_cart_shipping_total'); ?>

			<?php do_action('woocommerce_cart_totals_after_shipping'); ?>

		<?php elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc')) : ?>

			<tr class="shipping">
				<td><?php _e('Shipping', 'wc-marketplace-cart'); ?></td>
				<td data-title="<?php esc_attr_e('Shipping', 'wc-marketplace-cart'); ?>"><?php _e('Calculated at checkout', 'wc-marketplace-cart'); ?></td>
			</tr>

		<?php endif; ?>

		<?php foreach (WC()->cart->get_fees() as $fee) : ?>
			<tr class="fee">
				<td><?php echo esc_html($fee->name); ?></td>
				<td data-title="<?php echo esc_attr($fee->name); ?>"><?php wc_cart_totals_fee_html($fee); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) :
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
					? sprintf(' <small>' . __('(estimated for %s)', 'wc-marketplace-cart') . '</small>', WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[ $taxable_address[0] ])
					: '';

			if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
					<tr class="tax-rate tax-rate-<?php echo sanitize_title($code); ?>">
						<td><?php echo esc_html($tax->label) . $estimated_text; ?></td>
						<td data-title="<?php echo esc_attr($tax->label); ?>"><?php echo wp_kses_post($tax->formatted_amount); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<td><?php echo esc_html(WC()->countries->tax_or_vat()) . $estimated_text; ?></td>
					<td data-title="<?php echo esc_attr(WC()->countries->tax_or_vat()); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action('woocommerce_cart_totals_before_order_total'); ?>

		<tr class="order-total">
			<td><?php _e('Total', 'wc-marketplace-cart'); ?></td>
			<td data-title="<?php esc_attr_e('Total', 'wc-marketplace-cart'); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action('woocommerce_cart_totals_after_order_total'); ?>
	</table>

	<?php do_action('woocommerce_before_proceed_to_checkout'); ?>

	<div class="wc-proceed-to-checkout">
		<?php do_action('woocommerce_proceed_to_checkout'); ?>
	</div>

	<?php do_action('woocommerce_after_cart_totals'); ?>

</div>
