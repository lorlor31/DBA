<?php

defined('ABSPATH') || exit;

?>
<?php if (!empty($chooseShippingMethod) && !empty($available_methods) && count($available_methods) > 1): ?>
	<p class="shipping-method-selection-title multiple"><?php echo _e("Choose preferred delivery option:", 'wc-marketplace-cart'); ?></p>
	<ul class="woocommerce-shipping-methods">
		<?php foreach ($available_methods as $method) : ?>
			<li>
				<?php
					printf('<label for="shipping_method_%1$d_%2$s"><input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />%5$s</label>',
						$packageIdx, 
						sanitize_title($method->id), 
						esc_attr($method->id), 
						checked($method->id, $chosen_method, false), 
						wc_cart_totals_shipping_method_label($method));

					do_action('woocommerce_after_shipping_rate', $method, $packageIdx);
				?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php elseif (!empty($available_methods)) :  ?>
<p class="shipping-method-selection-title single"><?php echo _e("Delivery method:", 'wc-marketplace-cart'); ?></p>
	<?php
		$method = null;

		if (!empty($chosen_method) && isset($available_methods[$chosen_method])) {
			$method = $available_methods[$chosen_method];
		} else {
			$method = current($available_methods);
		}

		printf('%3$s <input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d" value="%2$s" class="shipping_method" />', 
			$packageIdx, 
			esc_attr($method->id), 
			wc_cart_totals_shipping_method_label($method)
		);

		do_action('woocommerce_after_shipping_rate', $method, $packageIdx);
	?>
<?php elseif (WC()->customer->has_calculated_shipping() && !empty($package['needs_shipping'])) : ?>
	<?php echo apply_filters(is_cart() ? 'woocommerce_cart_no_shipping_available_html' : 'woocommerce_no_shipping_available_html', wpautop(__('There are no shipping methods available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'wc-marketplace-cart'))); ?>
<?php elseif (!isset($package['needs_shipping']) || !empty($package['needs_shipping'])) : ?>
	<?php echo wpautop(__('Enter your full address to see shipping costs.', 'wc-marketplace-cart')); ?>
<?php endif; ?>