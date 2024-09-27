<?php

namespace Ademti\WoocommerceProductFeeds\Helpers;

use Ademti\WoocommerceProductFeeds\DTOs\PromotionFeedItem;
use Ademti\WoocommerceProductFeeds\DTOs\StoreInfo;
use DateTime;
use WC_Coupon;
use function count;
use function wp_cache_flush;

class CouponRepository {

	/**
	 * @var StoreInfo
	 */
	private StoreInfo $store_info;

	/**
	 * @var int
	 */
	private int $chunk_size;

	/**
	 * @param StoreInfo $store_info
	 */
	public function __construct( StoreInfo $store_info ) {
		$this->store_info = $store_info;
	}

	/**
	 * @return int
	 */
	public function get_chunk_size(): int {
		if ( ! isset( $this->chunk_size ) ) {
			$this->chunk_size = apply_filters( 'woocommerce_gpf_promotions_chunk_size', 20 );
		}

		return $this->chunk_size;
	}

	/**
	 * Retrieve a chunk of coupons.
	 *
	 * @param int $offset
	 *
	 * @return array
	 */
	public function get_coupons( int $offset = 0 ): array {
		return get_posts(
			[
				'posts_per_page' => $this->get_chunk_size(),
				'offset'         => $offset,
				'post_type'      => 'shop_coupon',
				'meta_query'     => [
					[
						'key'   => 'woocommerce_gpf_visibility',
						'value' => true,
					],
					[
						'key'     => 'discount_type',
						'compare' => 'IN',
						'value'   => [ 'fixed_cart', 'fixed_product', 'percent' ],
					],
				],
			]
		);
	}

	/**
	 * @return array
	 */
	public function generate_category_coupon_map() {

		global $_wp_using_ext_object_cache;

		$chunk_size = $this->get_chunk_size();
		$offset     = 0;
		$map        = [
			'categories'          => [],
			'excluded_categories' => [],
		];

		$now = new DateTime();

		$coupon_posts      = $this->get_coupons( $offset );
		$coupon_post_count = count( $coupon_posts );
		while ( $coupon_post_count ) {
			foreach ( $coupon_posts as $coupon_post ) {
				// Grab the WC_Coupon instance.
				$coupon = new WC_Coupon( $coupon_post->ID );
				// Ignore it if it has ended.
				$expires = $coupon->get_date_expires();
				if ( $expires && $expires < $now ) {
					continue;
				}
				// Create a PromotionFeedItem.
				$coupon_feed_item = new PromotionFeedItem( $coupon, $this->store_info );
				// Ignore the coupon if it was ineligible for the feed.
				if ( ! $coupon_feed_item->is_eligible() ) {
					continue;
				}
				// Grab the categories/excluded categories for this coupon.
				$categories          = $coupon_feed_item->get_product_types();
				$excluded_categories = $coupon_feed_item->get_product_type_exclusions();
				// Skip it if there are none.
				if ( empty( $categories ) && empty( $excluded_categories ) ) {
					continue;
				}
				// Add them to the map.
				foreach ( $categories as $selected_category_id ) {
					$id_and_descendants   = get_term_children( $selected_category_id, 'product_cat' );
					$id_and_descendants[] = $selected_category_id;
					foreach ( $id_and_descendants as $category_id ) {
						if ( empty( $map['categories'][ $category_id ] ) ) {
							$map['categories'][ $category_id ] = [];
						}
						$map['categories'][ $category_id ][] = $coupon_feed_item->get_promotion_id();
					}
				}
				foreach ( $excluded_categories as $excluded_category_id ) {
					$id_and_descendants   = get_term_children( $excluded_category_id, 'product_cat' );
					$id_and_descendants[] = $excluded_category_id;
					foreach ( $id_and_descendants as $category_id ) {
						if ( empty( $map['excluded_categories'][ $category_id ] ) ) {
							$map['excluded_categories'][ $category_id ] = [];
						}
						$map['excluded_categories'][ $category_id ][] = $coupon_feed_item->get_promotion_id();
					}
				}
			}
			$offset += $chunk_size;

			// If we're using the built-in object cache then flush it every chunk so
			// that we don't keep churning through memory.
			if ( ! $_wp_using_ext_object_cache ) {
				wp_cache_flush();
			}

			$coupon_posts      = $this->get_coupons( $offset );
			$coupon_post_count = count( $coupon_posts );
		}

		return $map;
	}
}
