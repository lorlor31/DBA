<?php

namespace Vibe\Clone_Orders\Addons;

use Vibe\Clone_Orders\Clone_Orders;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Provides support for Subscriptions plugin
 *
 * @since 1.4
 */
class Subscriptions {

	/**
	 * Creates an instance and sets up hooks to integrate with the rest of the extension, only if Subscriptions is
	 * installed
	 */
	public function __construct() {
		add_action( Clone_Orders::hook_prefix( 'is_cloneable_screen' ), array( __CLASS__, 'is_cloneable_screen' ) );
	}

	/**
	 * Filters the screens the clone action is displayed on to remove from subscription orders
	 *
	 * Subscription parent orders are cloneable as they may contain non-subscription products.
	 *
	 * @param bool $is_cloneable If the current screen is one to support a clone action or not
	 * @return bool False if the order is for a subscription renewal, resubscribe or switch, otherwise the core logic is
	 *              used to determine if the screen is suitable for cloning.
	 */
	public static function is_cloneable_screen( $is_cloneable ) {
		if ( $is_cloneable && function_exists( 'wcs_order_contains_subscription' ) ) {
			// Attempt to get current order
			$order = wc_get_order();

			if ( $order && wcs_order_contains_subscription( $order, array( 'renewal', 'switch', 'resubscribe' ) ) ) {
				$is_cloneable = false;
			}
		}

		return $is_cloneable;
	}
}
