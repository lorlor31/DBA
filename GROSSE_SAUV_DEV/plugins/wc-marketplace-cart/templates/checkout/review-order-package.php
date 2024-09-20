<?php
/**
 * Review order package
 *
 * Cart can contain more than one package, so here we implement template
 * for a single package
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.0.0
 */

defined('ABSPATH') || exit;

?>	
<table class="shop_table shop_table_responsive cart checkout woocommerce-cart-form__contents">
	<thead>
		<?php
			do_action('woocommerce_before_review_order_package_header', array(
				'packageIdx' => $packageIdx, 
				'package' => $package, 
			)); 
		?>
		<tr>
			<?php
				do_action('woocommerce_before_review_order_package_header_columns', array(
					'packageIdx' => $packageIdx, 
					'package' => $package, 
				)); 
			?>
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"><?php _e('Product', 'wc-marketplace-cart'); ?></th>
			<th class="product-total"><?php _e('Total', 'wc-marketplace-cart'); ?></th>
			<?php
				do_action('woocommerce_after_review_order_package_header_columns', array(
					'packageIdx' => $packageIdx, 
					'package' => $package, 
				)); 
			?>
		</tr>
		<?php
			do_action('woocommerce_after_review_order_package_header', array(
				'packageIdx' => $packageIdx, 
				'package' => $package, 
			)); 
		?>
	</thead>
	<tbody>
		<?php
			do_action('woocommerce_before_review_order_package_contents', array('packageIdx' => $packageIdx, 'package' => $package));
			
			$subTotalQuantity = 0;
			$subTotalAmount = 0;

			foreach ($package['contents'] as $cart_item_key => $cart_item) {
				$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
				$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
	
				if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
					$subTotalAmount += $_product->get_price() * $cart_item['quantity'];
					$subTotalQuantity += $cart_item['quantity'];
		?>
					<tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
						<?php
							do_action('woocommerce_before_review_order_package_item_columns', array(
								'packageIdx' => $packageIdx, 
								'package' => $package, 
								'product' => $_product, 
								'cartItem' => $cart_item, 
								'cartItemKey' => $cart_item_key
							)); 
						?>
						<td class="product-thumbnail"><?php
						$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
						$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
						
						if (! $product_permalink) {
							echo $thumbnail;
						} else {
							printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
						}
						?></td>
						<td class="product-name">
							<?php echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;'; ?>
							<?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times; %s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
							<?php echo wc_get_formatted_cart_item_data($cart_item); ?>
						</td>
						<td class="product-total" data-title="<?php esc_attr_e('Total', 'wc-marketplace-cart'); ?>">
							<?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
						</td>
						<?php
							do_action('woocommerce_after_review_order_package_item_columns', array(
								'packageIdx' => $packageIdx, 
								'package' => $package, 
								'product' => $_product, 
								'cartItem' => $cart_item, 
								'cartItemKey' => $cart_item_key
							)); 
						?>
					</tr>
					<?php
				}
			}
		?>
		<tr>
			<td colspan="3" class="package-subtotal">
				<span class="package-subtotal-quantity"><?php printf(__('Subtotal (%s items):', 'wc-marketplace-cart'), $subTotalQuantity); ?></span>
				<span class="package-subtotal-amount"><?php echo wc_price($subTotalAmount); ?></span>
			</td>
		</tr>
		<?php
		do_action('woocommerce_after_review_order_package_contents', array('packageIdx' => $packageIdx, 'shippingPackageIdx' => $shippingPackageIdx, 'package' => $package));
		?>
	</tbody>
</table>