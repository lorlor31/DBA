<?php

namespace Ademti\WoocommerceProductFeeds\Integrations;

use Ademti\WoocommerceProductFeeds\DTOs\ProductFeedItem;
use WC_Product;

/**
 * Integration for:
 * https://woocommerce.com/products/minmax-quantities
 */
class MinMaxQuantities {
	/**
	 * Run the integration.
	 */
	public function run(): void {
		add_filter(
			'woocommerce_gpf_feed_item',
			[ $this, 'multiply_out_by_minimum_quantities' ],
			10,
			2
		);
	}

	/**
	 * Multiplies prices out based on the minimum product quantities
	 *
	 * @param ProductFeedItem $feed_item
	 * @param WC_Product $wc_product
	 *
	 * @return mixed
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	public function multiply_out_by_minimum_quantities( $feed_item, $wc_product ) {
		if ( ! apply_filters( 'woocommerce_gpf_integration_minmax', true ) ) {
			return $feed_item;
		}
		// Get the relevant minimum quantity & group by settings.
		if ( $feed_item->specific_id !== $feed_item->general_id ) {
			$min_max_rules = get_post_meta( $feed_item->specific_id, 'min_max_rules', true );
			if ( 'yes' === $min_max_rules ) {
				$minimum_quantity  = absint( get_post_meta( $feed_item->specific_id, 'variation_minimum_allowed_quantity', true ) );
				$group_of_quantity = absint( get_post_meta( $feed_item->specific_id, 'variation_group_of_quantity', true ) );
			} else {
				$minimum_quantity  = absint( get_post_meta( $feed_item->general_id, 'minimum_allowed_quantity', true ) );
				$group_of_quantity = absint( get_post_meta( $feed_item->general_id, 'group_of_quantity', true ) );
			}
		} else {
			$minimum_quantity  = absint( get_post_meta( $feed_item->general_id, 'minimum_allowed_quantity', true ) );
			$group_of_quantity = absint( get_post_meta( $feed_item->general_id, 'group_of_quantity', true ) );
		}
		// Use the group of quantity if we have one, and do not have a minimum_quantity.
		if ( empty( $minimum_quantity ) && ! empty( $group_of_quantity ) ) {
			$minimum_quantity = $group_of_quantity;
		}
		if ( $minimum_quantity > 0 ) {
			$specific_product                 = wc_get_product( $feed_item->specific_id );
			$feed_item->sale_price_ex_tax     = wc_get_price_excluding_tax(
				$specific_product,
				[
					'qty'   => $minimum_quantity,
					'price' => $feed_item->raw_sale_price,
				]
			);
			$feed_item->sale_price_inc_tax    = wc_get_price_including_tax(
				$specific_product,
				[
					'qty'   => $minimum_quantity,
					'price' => $feed_item->raw_sale_price,
				]
			);
			$feed_item->regular_price_ex_tax  = wc_get_price_excluding_tax(
				$specific_product,
				[
					'qty'   => $minimum_quantity,
					'price' => $feed_item->raw_regular_price,
				]
			);
			$feed_item->regular_price_inc_tax = wc_get_price_including_tax(
				$specific_product,
				[
					'qty'   => $minimum_quantity,
					'price' => $feed_item->raw_regular_price,
				]
			);
			$feed_item->price_ex_tax          = wc_get_price_excluding_tax(
				$specific_product,
				[
					'qty'   => $minimum_quantity,
					'price' => $feed_item->raw_price,
				]
			);
			$feed_item->price_inc_tax         = wc_get_price_including_tax(
				$specific_product,
				[
					'qty'   => $minimum_quantity,
					'price' => $feed_item->raw_price,
				]
			);
			if ( empty( $feed_item->additional_elements['unit_pricing_measure'] ) &&
				empty( $feed_item->additional_elements['unit_pricing_base_measure'] ) &&
				apply_filters( 'woocommerce_gpf_minmax_send_unit_pricing', true ) ) {
				$feed_item->additional_elements['unit_pricing_measure']      = [ $minimum_quantity . ' ct' ];
				$feed_item->additional_elements['unit_pricing_base_measure'] = [ '1 ct' ];
			}
		}

		return $feed_item;
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
}
