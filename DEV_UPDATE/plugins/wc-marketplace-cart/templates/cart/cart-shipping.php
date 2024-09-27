<?php
/**
 * Shipping Methods Display
 *
 * Customized shipping methods templates based on version 3.2.0.
 * In our case we just show totals as shipping methods are available per package
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.0.0
 */
defined('ABSPATH') || exit;

?>
<tr class="woocommerce-shipping-totals shipping">
	<td colspan="2">
	<?php if (isset($packageName)): ?><strong class="package-name"><?php echo wp_kses_post($packageName); ?></strong><?php endif; ?>
	<?php include(__DIR__ . '/cart-shipping-methods.php'); ?>
	</td>
</tr>