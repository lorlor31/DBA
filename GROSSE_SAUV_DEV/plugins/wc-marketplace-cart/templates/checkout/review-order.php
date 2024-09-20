<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */
namespace OneTeamSoftware\WooCommerce\MarketplaceCart;

defined('ABSPATH') || exit;

$packages = MarketplaceCart::getPackages();
$order_button_text = apply_filters('woocommerce_order_button_text', __('Place order', 'wc-marketplace-cart'));

do_action('woocommerce_before_review_order');
?>
<div class="woocommerce-cart-form">
	<div class="woocoomerce-cart-packages">
<?php
do_action('woocommerce_before_review_order_packages');

$shippingPackageIdx = 0;
foreach ($packages as $packageIdx => $package) {
	$parameters = array(
		'packageIdx' => $packageIdx, 
		'shippingPackageIdx' => $shippingPackageIdx, 
		'package' => $package
	);

	do_action('woocommerce_before_review_order_package', $parameters);
	do_action('woocommerce_review_order_package', $parameters);
	do_action('woocommerce_after_review_order_package', $parameters);

	if (!empty($package['needs_shipping']) && !empty($package['rates'])) {
		$shippingPackageIdx++;
	}
}//package loop

do_action('woocommerce_after_review_order_packages');

?>
	</div>
	<div class="woocommerce-cart-totals">
		<table class="cart_totals shop_table">
			<thead>
				<tr>
					<th colspan="2"><?php _e('Order summary', 'wc-marketplace-cart'); ?></th>
				</tr>
			</thead>
			<tfoot>

				<tr class="cart-subtotal">
					<td><?php _e('Subtotal', 'wc-marketplace-cart'); ?></td>
					<td data-title="<?php _e('Subtotal', 'wc-marketplace-cart'); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
				</tr>

				<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
					<tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
						<td><?php wc_cart_totals_coupon_label($coupon); ?></td>
						<td data-title="<?php wc_cart_totals_coupon_label($coupon); ?>"><?php wc_cart_totals_coupon_html($coupon); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>

					<?php do_action('woocommerce_review_order_before_shipping'); ?>

					<?php do_action('woocommerce_cart_shipping_total'); ?>

					<?php do_action('woocommerce_review_order_after_shipping'); ?>

				<?php endif; ?>

				<?php foreach (WC()->cart->get_fees() as $fee) : ?>
					<tr class="fee">
						<td><?php echo esc_html($fee->name); ?></td>
						<td data-title="<?php echo esc_html($fee->name); ?>"><?php wc_cart_totals_fee_html($fee); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) : ?>
					<?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
						<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
							<tr class="tax-rate tax-rate-<?php echo sanitize_title($code); ?>">
								<td><?php echo esc_html($tax->label); ?></td>
								<td data-title="<?php echo esc_html($tax->label); ?>"><?php echo wp_kses_post($tax->formatted_amount); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="tax-total">
							<td><?php echo esc_html(WC()->countries->tax_or_vat()); ?></td>
							<td data-title="<?php echo esc_html(WC()->countries->tax_or_vat()); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<?php do_action('woocommerce_review_order_before_order_total'); ?>

				<tr class="order-total">
					<td><?php _e('Total', 'wc-marketplace-cart'); ?></td>
					<td data-title="<?php _e('Total', 'wc-marketplace-cart'); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
				</tr>

				<?php do_action('woocommerce_review_order_after_order_total'); ?>

			</tfoot>
		</table>
		<div class="woocommerce-review-order-payment">
		<?php do_action('woocommerce_review_order_payment'); ?>
		</div>
	</div>
</div>