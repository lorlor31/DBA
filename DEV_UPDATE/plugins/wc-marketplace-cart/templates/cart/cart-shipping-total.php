<?php
/**
 * Cart shipping total
 *
 * Since we show shipping costs under each package we can just show totals here
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.0.0
 */
 ?>
			<tr class="shipping">
				<td><?php _e( 'Shipping and Handling', 'wc-marketplace-cart' ); ?></td>
				<td data-title="<?php esc_attr_e( 'Shipping and Handling', 'wc-marketplace-cart' ); ?>">
				<?php 
				if (WC()->cart->display_prices_including_tax()) {
					echo wc_price( WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax() );

					if ( WC()->cart->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
					?>
					<small class="tax_label"><?php echo WC()->countries->inc_tax_or_vat(); ?></small>
					<?php
					}
				} else {
					echo wc_price(WC()->cart->get_shipping_total()); 
				}
				?>
				</td>
			</tr>