<?php
defined( 'ABSPATH' ) || exit;

class WBS_Product_Data_Store extends WC_Product_Data_Store_CPT implements WC_Object_Data_Store_Interface {

	/**
	 * Read product data. Can be overridden by child classes to load other props.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @since 3.0.0
	 */
	protected function read_product_data( &$product ) {
		$id                = $product->get_id();
		$post_meta_values  = get_post_meta( $id );
		$meta_key_to_props = array(
			'_sku'                   => 'sku',
			'_regular_price'         => 'regular_price',
			'_sale_price'            => 'sale_price',
			'_price'                 => 'price',
			'_sale_price_dates_from' => 'date_on_sale_from',
			'_sale_price_dates_to'   => 'date_on_sale_to',
			'total_sales'            => 'total_sales',
			'_tax_status'            => 'tax_status',
			'_tax_class'             => 'tax_class',
			'_manage_stock'          => 'manage_stock',
			'_backorders'            => 'backorders',
			'_low_stock_amount'      => 'low_stock_amount',
			'_sold_individually'     => 'sold_individually',
			'_weight'                => 'weight',
			'_length'                => 'length',
			'_width'                 => 'width',
			'_height'                => 'height',
			'_upsell_ids'            => 'upsell_ids',
			'_crosssell_ids'         => 'cross_sell_ids',
			'_purchase_note'         => 'purchase_note',
			'_default_attributes'    => 'default_attributes',
			'_virtual'               => 'virtual',
			'_downloadable'          => 'downloadable',
			'_download_limit'        => 'download_limit',
			'_download_expiry'       => 'download_expiry',
			'_thumbnail_id'          => 'image_id',
			'_stock'                 => 'stock_quantity',
			'_stock_status'          => 'stock_status',
			'_wc_average_rating'     => 'average_rating',
			'_wc_rating_count'       => 'rating_counts',
			'_wc_review_count'       => 'review_count',
			'_product_image_gallery' => 'gallery_image_ids',
		);

		$set_props     = array();
		$dynamic_price = get_post_meta( $id, '_wbs_dynamic_price', true );

		foreach ( $meta_key_to_props as $meta_key => $prop ) {
			if ( $meta_key == '_price' && $dynamic_price ) {
				$bundled_items = get_post_meta( $id, '_wbs_wcpb_bundle_data', true );

				$original_price = 0;
				if ( ! empty( $bundled_items ) && is_array( $bundled_items ) ) {
					foreach ( $bundled_items as $item ) {
						$item_id      = $item['product_id'];
						$item_product = wc_get_product( $item_id );

						if ( ! $item_product ) {
							continue;
						}

						$quantity       = $item['bp_quantity'] ?? 0;
						$original_price += $quantity * $item_product->get_price( 'edit' );
					}
				}
				$discount_type   = get_post_meta( $id, '_wbs_discount_type', true );
				$discount_amount = get_post_meta( $id, '_wbs_discount_amount', true );
				$bundle_price    = $original_price;

				if ( $discount_amount ) {
					if ( $discount_type === 'percent' ) {
						$discount_amount = $discount_amount / 100;
						$bundle_price    = $bundle_price * ( 1 - $discount_amount );
					} else {
						$bundle_price    = $bundle_price - $discount_amount;
					}
					if ( $bundle_price < 0 ) {
						$bundle_price = 0;
					}
					$bundle_price = VI_WBOOSTSALES_Data::convert_price_to_float( $bundle_price );
				}

				$meta_value = $bundle_price;
			} else {
				$meta_value = isset( $post_meta_values[ $meta_key ][0] ) ? $post_meta_values[ $meta_key ][0] : null;
			}

			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_post_meta only unserializes single values.
		}

		$set_props['category_ids']      = $this->get_term_ids( $product, 'product_cat' );
		$set_props['tag_ids']           = $this->get_term_ids( $product, 'product_tag' );
		$set_props['shipping_class_id'] = current( $this->get_term_ids( $product, 'product_shipping_class' ) );
		$set_props['gallery_image_ids'] = array_filter( explode( ',', $set_props['gallery_image_ids'] ?? '' ) );

		$product->set_props( $set_props );
	}
}

