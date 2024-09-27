<?php

namespace Ademti\WoocommerceProductFeeds\Feeds\ReviewFeeds;

use Ademti\WoocommerceProductFeeds\Cache\Cache;
use Ademti\WoocommerceProductFeeds\Helpers\ProductFeedItemExclusionService;
use Ademti\WoocommerceProductFeeds\Helpers\ProductFeedItemFactory;
use WC_Product;
use WC_Product_Variable;
use function array_map;
use function serialize;
use function unserialize;
use function wc_get_product;
use function array_merge_recursive;

class ReviewProductInfo {

	// Dependencies.
	private Cache $cache;
	private ProductFeedItemExclusionService $exclusion_service;
	private ProductFeedItemFactory $feed_item_factory;

	/**
	 * WoocommercePrfGoogleReviewProductInfo constructor.
	 *
	 * Instantiate a cache item.
	 *
	 * @param Cache $woocommerce_gpf_cache
	 * @param ProductFeedItemExclusionService $exclusion_service
	 * @param ProductFeedItemFactory $feed_item_factory
	 */
	public function __construct(
		Cache $woocommerce_gpf_cache,
		ProductFeedItemExclusionService $exclusion_service,
		ProductFeedItemFactory $feed_item_factory
	) {
		$this->cache             = $woocommerce_gpf_cache;
		$this->exclusion_service = $exclusion_service;
		$this->feed_item_factory = $feed_item_factory;
	}

	/**
	 * Rebuild the cache for an item.
	 *
	 * @param WC_Product $wc_product
	 *
	 * @return array
	 */
	public function rebuild_item( WC_Product $wc_product ): array {
		if ( is_null( $wc_product ) ) {
			return [];
		}
		if ( $wc_product instanceof WC_Product_Variable ) {
			$product_info = $this->get_product_info_variable( $wc_product );
			$this->cache->store( $wc_product->get_id(), 'googlereview', serialize( $product_info ) );

			return $product_info;
		}
		$product_info = $this->get_product_info_simple( $wc_product, true );
		$this->cache->store( $wc_product->get_id(), 'googlereview', serialize( $product_info ) );

		return $product_info;
	}

	/**
	 * Pull product identifiers based on Google Product Feed configuration.
	 *
	 * May retrieve results from the cache, or generate them.
	 *
	 * @param int $product_id The product ID to fetch information for.
	 *
	 * @return array               The product info array.
	 */
	public function get_product_info( int $product_id ): array {
		$cached_info = $this->cache->fetch( $product_id, 'googlereview' );
		if ( ! empty( $cached_info ) ) {
			return unserialize( $cached_info, [ 'allowed_classes' => false ] );
		}

		return $this->rebuild_item( wc_get_product( $product_id ) );
	}

	/**
	 * Generate product info for a simple product.
	 *
	 * @param WC_Product $wc_product
	 * @param bool $include_internal_ids
	 *
	 * @return array
	 */
	protected function get_product_info_simple(
		WC_Product $wc_product,
		bool $include_internal_ids = true
	): array {
		if ( empty( $wc_product ) ) {
			return [];
		}
		if ( 'product_variation' === $wc_product->get_type() ) {
			$gpf_feed_item = $this->feed_item_factory->create(
				'all',
				$wc_product,
				wc_get_product( $wc_product->get_parent_id() )
			);
		} else {
			$gpf_feed_item = $this->feed_item_factory->create( 'all', $wc_product, $wc_product );
		}
		if ( ! $gpf_feed_item ) {
			return [];
		}

		$product_info             = [
			'gtins'  => [],
			'mpns'   => [],
			'brands' => [],
			'skus'   => [],
		];
		$product_info['excluded'] = $this->exclusion_service->should_exclude( $wc_product, 'googlereview' ) ||
									empty( $gpf_feed_item->price_inc_tax );

		if ( ! empty( $gpf_feed_item->additional_elements['gtin'] ) ) {
			$product_info['gtins'] = $gpf_feed_item->additional_elements['gtin'];
		}
		if ( ! empty( $gpf_feed_item->additional_elements['mpn'] ) ) {
			$product_info['mpns'] = $gpf_feed_item->additional_elements['mpn'];
		}
		if ( ! empty( $gpf_feed_item->additional_elements['brand'] ) ) {
			$product_info['brands'] = $gpf_feed_item->additional_elements['brand'];
		}
		if ( ! empty( $gpf_feed_item->sku ) ) {
			$product_info['skus'][] = $gpf_feed_item->sku;
		}
		// We're done if internal IDs aren't requested as a fallback.
		if ( ! $include_internal_ids ) {
			return $product_info;
		}

		// Internal IDs are requested as a fallback, add them if necessary.
		if ( empty( $product_info['gtins'] ) &&
			( empty( $product_info['mpns'] ) || empty( $product_info['brands'] ) )
		) {
			$product_info['skus'][] = $gpf_feed_item->guid;
		}

		return $product_info;
	}

	/**
	 * Generate product info for a variable product.
	 *
	 * @param WC_Product_Variable $wc_product
	 *
	 * @return array
	 */
	protected function get_product_info_variable( WC_Product_Variable $wc_product ): array {
		$product_info = [
			'gtins'    => [],
			'mpns'     => [],
			'brands'   => [],
			'skus'     => [],
			'excluded' => [],
		];

		// Get the information for all the child variations.
		$child_ids = $wc_product->get_children();
		foreach ( $child_ids as $child_id ) {
			$child_info   = $this->get_product_info_simple( wc_get_product( $child_id ), true );
			$product_info = array_merge_recursive( $product_info, $child_info );
			$product_info = array_map( 'array_unique', $product_info );
		}

		// Get the parent product information, and merge in.
		$parent_info  = $this->get_product_info_simple( $wc_product, false );
		$product_info = array_merge_recursive( $product_info, $parent_info );
		// The excluded flag on the parent should override child values if set, not be merged with.
		if ( true === $parent_info['excluded'] ) {
			$product_info['excluded'] = [ true ];
		}
		$product_info = array_map( 'array_unique', $product_info );

		// If any variants are not excluded, then the product as a whole won't be excluded.
		if ( in_array( false, $product_info['excluded'], true ) ) {
			$product_info['excluded'] = false;
		} else {
			$product_info['excluded'] = true;
		}

		return $product_info;
	}
}
