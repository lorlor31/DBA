<?php

class WCCT_Compatibility_WCFF {

	private $discount = true;

	public function __construct() {
		add_filter( 'wcct_skip_discounts', [ $this, 'skip_discount' ], 1000, 3 );

		add_filter( 'woocommerce_add_cart_item', [ $this, 'remove_discount' ], 990, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'remove_discount' ], 990, 2 );

		add_filter( 'woocommerce_add_cart_item', [ $this, 'apply_discount' ], 1010, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'apply_discount' ], 1010, 2 );
	}

	function remove_discount( $citem, $cart_item_key ) {
		$this->discount = false;

		return $citem;
	}

	function apply_discount( $citem = [], $cart_item_key = '' ) {
		$this->discount = true;

		return $citem;
	}

	function skip_discount( $bool, $price, $product ) {
		if ( ! class_exists( 'Wcff' ) ) {
			return $bool;
		}

		if ( ! is_cart() ) {
			return $bool;
		}
		
		if ( false == $this->discount && ( $product instanceof WC_Product ) && in_array( $product->get_id(), WCCT_Core()->discount->excluded ) ) {
			return true;
		}

		return $bool;
	}


}

new WCCT_Compatibility_WCFF();
