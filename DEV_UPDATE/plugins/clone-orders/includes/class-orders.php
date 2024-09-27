<?php

namespace Vibe\Clone_Orders;

use WC_Data_Exception;
use WC_Order;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Handles order functions such as cloning orders and adding notes
 *
 * @since 1.0
 */
class Orders {

	/**
	 * Returns true if the given order can be cloned by the current user
	 *
	 * @param int $order_id The ID of the order to check
	 *
	 * @return bool True if the order can be cloned and false otherwise
	 */
	public static function can_clone( $order_id ) {
		$user_can = current_user_can( 'edit_shop_orders', $order_id );

		return apply_filters( Clone_Orders::hook_prefix( 'can_clone' ), $user_can, wc_get_order( $order_id ) );
	}

	/**
	 * Clones an order to create a new order with the same details as the original and optionally including order items.
	 *
	 * The cloned order will be assigned the following information to match the original order:
	 *
	 * - Billing address
	 * - Shipping address
	 * - Date created
	 * - Currency
	 * - Customer IP
	 * - Customer user agent
	 *
	 * Order line items will also be copied to the new order if the $clone_items argument is set to true, otherwise the
	 * new order will be empty.
	 *
	 * @param int   $order_id    The ID of the order to clone
	 * @param bool  $clone_items Whether order items should be copied to the new order or not
	 * @param array $meta_fields The meta data fields to copy if they exist on the source order
	 *
	 * @return WC_Order The new order that was created
	 * @throws WC_Data_Exception Exception thrown if products or meta data cannot be assigned to the new order
	 */
	public static function clone_order( $order_id, $clone_items = false, $meta_fields = array() ) {
		$old_order = wc_get_order( $order_id );

		$billing_address     = $old_order->get_address( 'billing' );
		$shipping_address    = $old_order->get_address( 'shipping' );
		$date_created        = $old_order->get_date_created();
		$currency            = $old_order->get_currency();
		$prices_include_tax  = $old_order->get_prices_include_tax();
		$customer_ip         = $old_order->get_customer_ip_address();
		$customer_user_agent = $old_order->get_customer_user_agent();
		$customer_note       = $old_order->get_customer_note();

		// Disable emails temporarily
		static::maybe_disable_emails( $old_order );

		/* @var WC_Order $new_order */
		$new_order = wc_create_order( array(
			'status'      => apply_filters( Clone_Orders::hook_prefix( 'clone_order_status' ), $old_order->get_status(), $old_order, $clone_items ),
			'customer_id' => $old_order->get_customer_id(),
			'created_via' => __( 'Order clone', 'clone-orders' )
		) );

		do_action( Clone_Orders::hook_prefix( 'order_created' ), $new_order, $old_order );

		// Restore emails
		static::maybe_restore_emails( $old_order );

		if ( $clone_items ) {
			static::clone_line_items( $old_order, $new_order );

			$should_reduce_stock = in_array( $new_order->get_status(), array( 'completed', 'processing', 'on-hold' ) );

			if ( apply_filters( Clone_Orders::hook_prefix( 'reduce_stock' ), $should_reduce_stock, $new_order, $old_order ) ) {
				wc_reduce_stock_levels( $new_order );
			}
		}

		if ( apply_filters( Clone_Orders::hook_prefix( 'clone_date_created' ), true, $new_order, $old_order ) ) {
			$new_order->set_date_created( $date_created );
		}

		$new_order->set_address( $billing_address, 'billing' );
		$new_order->set_address( $shipping_address, 'shipping' );
		$new_order->set_currency( $currency );
		$new_order->set_prices_include_tax( $prices_include_tax );
		$new_order->set_customer_ip_address( $customer_ip );
		$new_order->set_customer_user_agent( $customer_user_agent );
		$new_order->set_customer_note( $customer_note );

		// Copy any additional requested meta fields
		static::clone_meta( $old_order, $new_order, $meta_fields );

		// Copy the address indexes to retain search - WooCommerce doesn't set these up automatically when setting address
		if ( Admin::is_using_hpos() ) {
			$new_order->update_meta_data( '_billing_address_index', $old_order->get_meta( '_billing_address_index' ) );
			$new_order->update_meta_data( '_shipping_address_index', $old_order->get_meta( '_shipping_address_index' ) );
		} else {
			update_post_meta( $new_order->get_id(), '_billing_address_index', get_post_meta( $old_order->get_id(), '_billing_address_index', true ) );
			update_post_meta( $new_order->get_id(), '_shipping_address_index', get_post_meta( $old_order->get_id(), '_shipping_address_index', true ) );
		}

		$new_order->add_meta_data( '_vibe_clone_orders_cloned_from', $old_order->get_id() );

		$new_order->calculate_totals();
		$new_order->save();

		$old_order = wc_get_order( $old_order->get_id() );
		$new_order = wc_get_order( $new_order->get_id() );

		do_action( Clone_Orders::hook_prefix( 'after_order_cloned' ), $new_order, $old_order );

		return $new_order;
	}

	/**
	 * Copies meta data from source order to target
	 *
	 * @param WC_Order $source_order The order to copy meta data from
	 * @param WC_Order $target_order The order to save the meta data to
	 * @param array    $meta_fields  The meta data fields to copy if they exist on the source order
	 */
	public static function clone_meta( WC_Order $source_order, WC_Order $target_order, array $meta_fields ) {
		foreach ( $meta_fields as $meta_field ) {
			if ( $source_order->meta_exists( $meta_field ) ) {
				$meta_values = $source_order->get_meta( $meta_field, false, 'edit' );

				foreach ( $meta_values as $meta_value ) {
					$target_order->add_meta_data( $meta_field, $meta_value->value );
				}
			}
		}

		$target_order->save_meta_data();
	}

	/**
	 * Copies line items from source order to target order
	 *
	 * @param WC_Order $source_order
	 * @param WC_Order $target_order
	 */
	public static function clone_line_items( WC_Order $source_order, WC_Order $target_order ) {
		$items = $source_order->get_items( array( 'line_item', 'tax', 'shipping', 'fee', 'coupon' ) );

		$excluded_meta = array( '_reduced_stock' );

		foreach ( $items as $source_item ) {
			$target_item = clone $source_item;
			$target_item->set_id( 0 );

			// All meta is copied, so remove any that should be excluded
			foreach ( $excluded_meta as $excluded_meta_field ) {
				$target_item->delete_meta_data( $excluded_meta_field );
			}

			$target_item = apply_filters( Clone_Orders::hook_prefix( 'line_item' ), $target_item, $source_item, $target_order, $source_order );

			// Allow the target item to be filtered to null for it not to be cloned
			if ($target_item) {
				$target_order->add_item( $target_item );
			}
		}
	}

	/**
	 * Disable customer and admin emails unless vibe_clone_orders_disable_emails filter returns false for this order
	 *
	 * Emails that will be disabled are:
	 *
	 * - Customer - Processing order
	 * - Customer - Completed order
	 * - Customer - Refunded order
	 * - Customer - On-hold order
	 * - Admin - New order
	 * - Admin - Cancelled order
	 * - Admin - Failed order
	 *
	 * @param WC_Order $order The order that is being cloned used for filtering
	 */
	public static function maybe_disable_emails( WC_Order $order ) {
		if ( apply_filters( Clone_Orders::hook_prefix( 'disable_emails' ), true, $order ) ) {
			add_action( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_customer_completed_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_customer_refunded_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_cancelled_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_failed_order', '__return_false' );
			add_action( 'woocommerce_email_enabled_new_order', '__return_false' );

			do_action( Clone_Orders::hook_prefix( 'emails_disabled' ), $order );
		}
	}

	/**
	 * Restore customer and admin emails unless vibe_clone_orders_disable_emails filter returns false for this order
	 *
	 * Emails that will be restored are:
	 *
	 * - Customer - Processing order
	 * - Customer - Completed order
	 * - Customer - Refunded order
	 * - Customer - On-hold order
	 * - Admin - New order
	 * - Admin - Cancelled order
	 * - Admin - Failed order
	 *
	 * @param WC_Order $order The order that is being cloned used for filtering
	 */
	public static function maybe_restore_emails( WC_Order $order ) {
		if ( apply_filters( Clone_Orders::hook_prefix( 'disable_emails' ), true, $order ) ) {
			remove_action( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_customer_completed_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_customer_refunded_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_cancelled_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_failed_order', '__return_false' );
			remove_action( 'woocommerce_email_enabled_new_order', '__return_false' );

			do_action( Clone_Orders::hook_prefix( 'emails_restored' ), $order );
		}
	}
}
