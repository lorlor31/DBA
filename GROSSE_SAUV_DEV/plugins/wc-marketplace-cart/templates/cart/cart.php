<?php
/**
 * Cart Page
 *
 * Customized Woocommerce cart template based on version 3.3.0
 * 
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 1.0.0
 */
namespace OneTeamSoftware\WooCommerce\MarketplaceCart;

defined('ABSPATH') || exit;

$formClasses = '';
// support for flatsome Auto Refresh on Quantity change option
if (function_exists('get_theme_mod') && get_theme_mod('cart_auto_refresh')) {
	$formClasses .= ' cart-auto-refresh';
}

$packages = MarketplaceCart::getPackages();

wc_print_notices();

do_action('woocommerce_before_cart');
?>
<form class="woocommerce-cart-form <?php echo $formClasses; ?>" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
<div class="woocoomerce-cart-packages">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<?php do_action( 'woocommerce_before_cart_packages' ); ?>

	<?php
	$shippingPackageIdx = 0;
	foreach ($packages as $packageIdx => $package) {
		$parameters = array(
			'packageIdx' => $packageIdx, 
			'shippingPackageIdx' => $shippingPackageIdx, 
			'package' => $package
		);

		do_action('woocommerce_before_cart_package', $parameters);
		do_action('woocommerce_cart_package', $parameters);
		do_action('woocommerce_after_cart_package', $parameters);

		if (!empty($package['needs_shipping']) && !empty($package['rates'])) {
			$shippingPackageIdx++;
		}
	}//package loop
	?>

	<?php do_action( 'woocommerce_after_cart_packages' ); ?>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents coupon" cellspacing="0">
		<tbody>
			<tr>
				<td class="actions">
					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</div>
<div class="woocommerce-cart-totals">
<?php do_action( 'woocommerce_cart_totals' ); ?>
</div>
</form>

<div class="cart-collaterals wc-cart-packages-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
