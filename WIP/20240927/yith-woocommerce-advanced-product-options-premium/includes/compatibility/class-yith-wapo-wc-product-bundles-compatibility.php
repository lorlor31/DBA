<?php
/**
 * YITH Multi Currency Switcher for WooCommerce compatibility.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddons
 */

defined('WC_PB_VERSION' ) || exit; // Exit if accessed directly.

if ( ! class_exists('YITH_WAPO_WC_Product_Bundles_Compatibility' ) ) {
    /**
     * Compatibility Class
     *
     * @class   YITH_WAPO_WC_Product_Bundles_Compatibility
     * @since   4.10.1
     */
    class YITH_WAPO_WC_Product_Bundles_Compatibility {
        /**
         * Single instance of the class
         *
         * @var YITH_WAPO_WC_Product_Bundles_Compatibility
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WAPO_WC_Product_Bundles_Compatibility
         */
        public static function get_instance() {
            return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
        }


        private function __construct() {
            add_filter( 'yith_wapo_totals_product_price', array( $this, 'yith_wapo_totals_bundle_price' ), 10, 2 );
            add_filter( 'yith_wapo_convert_price', array( $this, 'yith_wapo_convert_bundle_price' ), 10, 3 );
            add_filter( 'yith_wapo_product_price', array( $this, 'yith_wapo_totals_bundle_price' ), 10, 2 );
            add_filter( 'yith_wapo_product_quantity_selector', array( $this, 'exclude_bundled_quantity_from_quantity_selector' ), 10 );
        }

        public function yith_wapo_totals_bundle_price( $price, $product ) {

            $bundle_price = 0;

            if ( $product instanceof WC_Product_Bundle ) {
                $bundle_price = $product->get_bundle_price();
            }

            return $bundle_price > 0 ? $bundle_price : $price;

        }

        public function yith_wapo_convert_bundle_price( $price, $boolean, $product ) {

            $bundle_price = 0;

            if ( $product instanceof WC_Product_Bundle ) {
                $bundle_price = $product->get_bundle_price();
            }

            return $bundle_price > 0 ? $bundle_price : $price;

        }

        public function exclude_bundled_quantity_from_quantity_selector ( $selector ){
            $selector = 'form.cart .quantity input.qty:not(.wapo-product-qty):not(.bundled_qty)';
            return $selector;
        }
    }
}
