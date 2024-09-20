<?php 

defined('ABSPATH') || exit; 

?>
<div class="coupon">
	<label for="coupon_code"><?php esc_html_e('Coupon:', 'wc-marketplace-cart'); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Coupon code', 'wc-marketplace-cart'); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'wc-marketplace-cart'); ?>" />
	<?php do_action('woocommerce_cart_coupon'); ?>
</div>