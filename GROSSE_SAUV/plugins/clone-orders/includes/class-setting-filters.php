<?php

namespace Vibe\Clone_Orders;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Handles filtering the plugin behaviour based on setting values
 *
 * @since 1.5.0
 */
class Setting_Filters {

	public function __construct() {
		add_filter( Clone_Orders::hook_prefix( 'line_item' ), array( __CLASS__, 'maybe_clone_instock_only' ), 10, 4 );
	}

	/**
	 * Filter the target item to set the quantity to only what is in stock, only if the instock only setting is enabled
	 *
	 * @param \WC_Order_Item $target_item The item being prepared to add to the clone. It could be of any line item type.
	 * @param \WC_Order_Item $source_item The original order item being cloned
	 * @param \WC_Order $target_order
	 * @param \WC_Order $source_order
	 *
	 * @return \WC_Order_Item|null The original line item, with the quantity maybe updated
	 */
	public static function maybe_clone_instock_only( $target_item, $source_item, $target_order, $source_order ) {
		if ( ! Settings::enable_cloning_instock_only() ) {
			return $target_item;
		}

		// We can't set quantities on other order item types like shipping and tax so bail without changing
		if ( $target_item->get_type() !== 'line_item' ) {
			return $target_item;
		}

		/**
		 * As target item is of type line_item it must be a WC_Order_Item_Product
		 *
		 * @var \WC_Order_Item_Product $target_item
		 */
		$product = $target_item->get_product();

		// Only get involved if stock is being managed and limited
		if ( $product->backorders_allowed() || ( ! $product->managing_stock() && $product->is_in_stock() ) ) {
			return $target_item;
		}

		// Limit the target item quantity to what is actually in stock. If not managing stock, it must be out of stock
		$stock_quantity  = $product->managing_stock() ? $product->get_stock_quantity() : 0;
		$target_quantity = $target_item->get_quantity();
		$update_quantity = ( $target_quantity > $stock_quantity ) ? $stock_quantity : $target_quantity;

		if ( $update_quantity <= 0 ) {
			// Set to null to prevent creating an item with quantity 0
			$target_item = null;
		} else if ( $target_quantity != $update_quantity ) {
			// If we're changing the quantity, we also need to change the subtotal, total, subtotal_tax and total_tax
			$subtotal = $source_order->get_item_subtotal( $source_item, false, false ) * $update_quantity;
			$total    = $source_order->get_item_total( $source_item, false, false ) * $update_quantity;

			$target_item->set_quantity( $update_quantity );
			$target_item->set_subtotal( $subtotal );
			$target_item->set_total( $total );
		}

		return $target_item;
	}
}
