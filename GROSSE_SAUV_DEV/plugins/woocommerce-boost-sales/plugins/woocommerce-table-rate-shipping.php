<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**WooCommerce Table Rate Shipping from Bolder Elements
 * Class VI_WBOOSTSALES_PLUGINS_WOOCOMMERCE_TABLE_RATE_SHIPPING
 */
class VI_WBOOSTSALES_PLUGINS_WOOCOMMERCE_TABLE_RATE_SHIPPING {
	public function __construct() {
		add_filter( 'betrs_calculated_totals-per_order', array( $this, 'calculated_totals' ) );
	}

	public function calculated_totals( $data ) {
		$cart_contents  = ! empty( WC()->cart ) ? WC()->cart->cart_contents : '';
		$method         = new BE_Table_Rate_Method();
		$betrs_shipping = new BE_Table_Rate_Calculate( $method, get_option( $method->get_options_save_name() ) );
		foreach ( $cart_contents as $cart_item => $item_ar ) {
			if ( isset( $item_ar['wbs_bundled_items'] ) ) {
				if ( isset( $item_ar['data'] ) ) {
					$data['subtotal'] += $betrs_shipping->get_line_item_price( $item_ar );
				}
			}
		}

		return $data;
	}
}
