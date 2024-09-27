<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
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
 * @version 7.9.0
 */

defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_before_cart' );


$woo_new_7_0_1_version = etheme_woo_version_check();
$button_class = '';
if ( $woo_new_7_0_1_version ) {
	$button_class = wc_wp_theme_get_element_class_name( 'button' );
}

?>

<div class="row-fluid">
	<div clas="span12"> 
		<form action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" class="cart-items woocommerce-cart-form">

			<?php do_action( 'woocommerce_before_cart_table' ); ?>
			<div class="row-fluid">
				<div class="span8 cart-table-section">
					<table class="shop_table table cart shop_table_responsive woocommerce-cart-form__contents" cellspacing="0">
						<thead>
							<tr>
								<th class="product-thumbnail hidden-phone a-center">&nbsp;</th>
								<th class="product-name"><?php esc_html_e( 'Product', 'legenda' ); ?></th>
								<th class="product-price a-center"><?php esc_html_e( 'Price', 'legenda' ); ?></th>
								<th class="product-quantity a-center"><?php esc_html_e( 'Quantity', 'legenda' ); ?></th>
								<th class="product-subtotal a-center"><?php esc_html_e( 'Subtotal', 'legenda' ); ?></th>
								<th class="product-remove">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php do_action( 'woocommerce_before_cart_contents' ); ?>

							<?php
							foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
								$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

								if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

									$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
									?>

									<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

										<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'legenda' ); ?>">
					                        <div class="product-thumbnail">
					                            <?php
					                                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

					                                    if ( ! $_product->is_visible() )
					                                            echo $thumbnail;
					                                    else
					                                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
					                            ?>
					                        </div>
										</td>
										<td class="product-details">
					                        <div class="cart-item-details">
					                            <?php
					                                    if ( ! $_product->is_visible() )
					                                        	echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', esc_html($_product->get_name()), $cart_item, $cart_item_key ) . '&nbsp;' );
					                                    else
					                                           echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), esc_html($_product->get_name()) ), $cart_item, $cart_item_key ) );
					                                    // Meta data
					                                    echo wc_get_formatted_cart_item_data( $cart_item );

					                    // Backorder notification
					                    if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
					                            echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'legenda' ) . '</p>';
					                            ?>
					                            <span class="mobile-price">
					                            	<?php
														echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
													
													?>
					                            </span>
					                        </div>
										</td>

										<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'legenda' ); ?>">
											<?php
												echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
												// var_dump($_product);
											?>
										</td>

										<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'legenda' ); ?>">
											<?php
												if ( $_product->is_sold_individually() ) {
													$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
												} else {
													$product_quantity = woocommerce_quantity_input( array(
														'input_name'  => "cart[{$cart_item_key}][qty]",
														'input_value' => $cart_item['quantity'],
														'max_value'   => $_product->get_max_purchase_quantity(),
														'min_value'   => '0',
														'product_name'  => $_product->get_name(), 
													), $_product, false );
												}

												echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
											?>
										</td>

										<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'legenda' ); ?>">
											<?php
												echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
											?>
										</td>
										<td class="product-remove">
											<?php
												echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													'woocommerce_cart_item_remove_link',
													sprintf(
														'<a href="%s" class="btn remove-item remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" title="%s">X</a>',
														esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
														esc_html__( 'Remove this item', 'legenda' ),
														esc_attr( $product_id ),
														esc_attr( $_product->get_sku() ),
														esc_html__( 'Remove this item', 'legenda' )
													),
													$cart_item_key
												);
											?>
										</td>
									</tr>
									<?php
								}
							}

							do_action( 'woocommerce_cart_contents' );
							?>
							<tr>
								<td colspan="6" class="actions">
                                     <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'legenda' ); ?>"><?php esc_html_e( 'Update cart', 'legenda' ); ?></button>
                                    <a href="/livraison/" title="Informations livraison" class="button">Informations livraison</a>
                                    <button type="button" onClick="window.ps.show({ id: 23299 });" class="button popup_acces" >Vérifier vos accès</button>
									<?php if ( wc_coupons_enabled() ) { ?>
										<div class="coupon" style="width:180px; margin-top:-10px;margin-right:-5px;float:right;border: 1px solid #ebebeb;padding: 5px 5px;border-top: 0;">
											<?php wp_nonce_field( 'woocommerce-coupon' ); ?>
											<label style="text-align:center;font-size:15px;" for="coupon_code"><?php esc_html_e( 'Coupon', 'legenda' ); ?></label> <input name="coupon_code" style="width:180px" class="input-text" id="coupon_code" value="" placeholder="<?php esc_html_e( 'Coupon code', 'legenda' ); ?>" /> <input type="submit" style="margin-top:5px;width:180px;" class="button" name="apply_coupon" value="<?php _e( 'Apply Coupon', 'legenda' ); ?>" />
											<?php do_action('woocommerce_cart_coupon'); ?>
										</div>
										<?php } ?>
										<?php do_action( 'woocommerce_cart_actions' ); ?>
										<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
								</td>
							</tr>
						</tbody>
					</table>
                    <?php do_action( 'woocommerce_after_cart_table' ); ?>
				</div><!-- END .span8 cart-table-section -->

				<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

				<div class="span4 cart-totals-section">
					<div class="cart-totals-block">
						
					<?php
						/**
						 * woocommerce_cart_collaterals hook.
						 *
						 * @hooked woocommerce_cross_sell_display
						 * @hooked woocommerce_cart_totals - 10
						 */
					 	do_action( 'woocommerce_cart_collaterals' );
					?>

					</div>
					<?php dynamic_sidebar('cart-sidebar'); ?>
                    <div class="row-fluid cart-options-row" style="padding-left:20px;">
                        <!-- <div>
						<?php //if ( wc_coupons_enabled() ) { ?>
                                <div class="coupon">
                                    <?php// wp_nonce_field( 'woocommerce-coupon' ); ?>
                                    <label for="coupon_code"><?php // esc_html_e( 'Coupon', 'legenda' ); ?>:</label> <input name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php //esc_html_e( 'Coupon code', 'legenda' ); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php // _e( 'Apply Coupon', 'legenda' ); ?>" />
                                    <?php // do_action('woocommerce_cart_coupon'); ?>
                                </div>
                            <?php // } ?>
                            <?php // do_action( 'woocommerce_cart_actions' ); ?>
                            <?php // wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                            <?php // do_action( 'woocommerce_after_cart_contents' ); ?>
                        </div>-->
                    </div><!-- END .cart-options-row -->
				</div><!-- END .pan4 cart-totals-section -->
			</div><!-- END .row-fluid -->
		</form>
	</div>
</div>


<?php woocommerce_cross_sell_display(); ?>

<?php do_action( 'woocommerce_after_cart' ); ?>