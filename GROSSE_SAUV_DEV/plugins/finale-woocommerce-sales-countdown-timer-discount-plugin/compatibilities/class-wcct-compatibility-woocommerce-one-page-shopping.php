<?php

class WCCT_Compatibility_With_WooCommerce_One_Page_Shopping {

	public function __construct() {
		add_filter( 'wcct_display_campaign_elements_on_checkout', array( $this, 'wcct_if_wc_one_page_shopping_plugin' ), 5 );
	}

	/**
	 * Checking if woocommerce_one_page_shopping class exists
	 *
	 * @param $bool
	 *
	 * @return bool
	 */
	public function wcct_if_wc_one_page_shopping_plugin( $bool ) {
		if ( class_exists( 'woocommerce_one_page_shopping' ) ) {
			return true;
		}

		return $bool;
	}

}

new WCCT_Compatibility_With_WooCommerce_One_Page_Shopping();
