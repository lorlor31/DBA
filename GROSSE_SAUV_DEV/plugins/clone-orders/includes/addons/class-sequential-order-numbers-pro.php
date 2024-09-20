<?php

namespace Vibe\Clone_Orders\Addons;

use Vibe\Clone_Orders\Clone_Orders;
use WC_Order;
use WC_Seq_Order_Number_Pro;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for Sequential Order Numbers Pro plugin
 *
 * @since 1.1
 */
class Sequential_Order_Numbers_Pro {

	/**
	 * Creates an instance and sets up hooks to integrate with the rest of the extension
	 */
	public function __construct() {
		add_action( Clone_Orders::hook_prefix( 'after_order_cloned' ), array( __CLASS__, 'order_cloned' ) );
	}

	/**
	 * Assigns a sequential order number to newly created orders after a clone
	 *
	 * @param WC_Order $new_order The newly created order
	 */
	public static function order_cloned( WC_Order $new_order ) {
		if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			WC_Seq_Order_Number_Pro::instance()->set_sequential_order_number( $new_order );
		}
	}
}
