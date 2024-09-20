<?php 

defined('ABSPATH') || exit; 

?>
<?php if (wc_coupons_enabled()): ?>
<form class="woocommerce-cart-coupon-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents coupon" cellspacing="0">
	<tbody>
		<tr>
			<td class="actions">
				<?php do_action('woocommerce_cart_coupon_field'); ?>
			</td>
		</tr>
	</tbody>
</table>
</form>
<?php endif; ?>