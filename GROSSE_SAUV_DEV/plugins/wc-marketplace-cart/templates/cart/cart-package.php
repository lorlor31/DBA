<?php
/**
 * Cart package
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
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
			<?php
				do_action('woocommerce_before_cart_package_header', array(
					'packageIdx' => $packageIdx, 
					'package' => $package, 
				)); 
			?>
			<tr>
				<?php
					do_action('woocommerce_before_cart_package_header_columns', array(
						'packageIdx' => $packageIdx, 
						'package' => $package, 
					)); 
				?>
				<th class="product-thumbnail"><?php
					do_action('woocommerce_cart_package_header_thumbnail_column_contents', array(
						'packageIdx' => $packageIdx, 
						'package' => $package, 
					)); 
				?><?php esc_html_e('&nbsp;', 'wc-marketplace-cart'); ?></th>
				<th class="product-name"><?php esc_html_e('Product', 'wc-marketplace-cart'); ?></th>
				<th class="product-price"><?php esc_html_e('Price', 'wc-marketplace-cart'); ?></th>
				<th class="product-quantity"><?php esc_html_e('Quantity', 'wc-marketplace-cart'); ?></th>
				<th></th>
				<?php
					do_action('woocommerce_after_cart_package_header_columns', array(
						'packageIdx' => $packageIdx, 
						'package' => $package, 
					)); 
				?>
			</tr>
			<?php
				do_action('woocommerce_after_cart_package_header', array(
					'packageIdx' => $packageIdx, 
					'package' => $package, 
				)); 
			?>
		</thead>
		<tbody>
			<?php do_action('woocommerce_before_cart_package_contents', array('packageIdx' => $packageIdx, 'package' => $package)); ?>
		
			<?php
			$subTotalQuantity = 0;
			$subTotalAmount = 0;

			foreach ($package['contents'] as $cart_item_key => $cart_item) {
				$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
				$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
				
				if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
					$subTotalAmount += $_product->get_price() * $cart_item['quantity'];
					$subTotalQuantity += $cart_item['quantity'];

					$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
						<?php
							do_action('woocommerce_before_cart_package_item_columns', array(
								'packageIdx' => $packageIdx, 
								'package' => $package, 
								'product' => $_product, 
								'cartItem' => $cart_item, 
								'cartItemKey' => $cart_item_key
							)); 
						?>

						<td class="product-thumbnail"><?php
						$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

						if (! $product_permalink) {
							echo $thumbnail;
						} else {
							printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
						}
						?></td>

						<td class="product-name"><?php
						if (! $product_permalink) {
							echo apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;';
						} else {
							echo apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key);
						}

						// Meta data.
						echo wc_get_formatted_cart_item_data($cart_item);

						// Backorder notification.
						if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
							echo '<p class="backorder_notification">' . esc_html__('Available on backorder', 'wc-marketplace-cart') . '</p>';
						}
						?></td>

						<td class="product-price" data-title="<?php esc_attr_e('Price', 'wc-marketplace-cart'); ?>">
							<?php
								echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'wc-marketplace-cart'); ?>"><?php
						if ($_product->is_sold_individually()) {
							$product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
						} else {
							$product_quantity = woocommerce_quantity_input(array(
								'input_name'    => "cart[{$cart_item_key}][qty]",
								'input_value'   => $cart_item['quantity'],
								'max_value'     => $_product->get_max_purchase_quantity(),
								'min_value'     => '0',
								'product_name'  => $_product->get_name(),
							), $_product, false);
						}

						echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
						?></td>

						<td class="product-extras"><?php
							do_action('woocommerce_after_cart_package_item', $_product, $cart_item_key);
						?></td>

						<?php
							do_action('woocommerce_after_cart_package_item_columns', array(
								'packageIdx' => $packageIdx, 
								'package' => $package, 
								'product' => $_product, 
								'cartItem' => $cart_item, 
								'cartItemKey' => $cart_item_key
							)); 
						?>
					</tr>
					<tr class="product-actions">
						<td colspan="5"><?php do_action('woocommerce_after_cart_package_item_line', $_product, $cart_item_key); ?></td>
					</tr>
					<?php
				}
			}
		?>
		<tr>
			<td colspan="6" class="package-subtotal">
				<span class="package-subtotal-quantity"><?php printf(__('Subtotal (%s items):', 'wc-marketplace-cart'), $subTotalQuantity); ?></span>
				<span class="package-subtotal-amount"><?php echo wc_price($subTotalAmount); ?></span>
			</td>
		</tr>
		<?php 
		do_action('woocommerce_after_cart_package_contents', array('packageIdx' => $packageIdx, 'shippingPackageIdx' => $shippingPackageIdx, 'package' => $package));
		?>
		</tbody>
	</table>