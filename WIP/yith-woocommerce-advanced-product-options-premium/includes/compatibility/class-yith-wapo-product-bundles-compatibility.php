<?php
/**
 * YITH Product Bundles compatibility.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddons
 */

defined( 'YITH_WCPB_PREMIUM' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Product_Bundles_Compatibility' ) ) {
    /**
     * Compatibility Class
     *
     * @class   YITH_WAPO_Product_Bundles_Compatibility
     * @since   4.2.1
     */
    class YITH_WAPO_Product_Bundles_Compatibility {

        /**
         * Single instance of the class
         *
         * @var YITH_WAPO_Product_Bundles_Compatibility
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WAPO_Product_Bundles_Compatibility
         */
        public static function get_instance() {
            return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
        }

        /**
         * YITH_WAPO_Product_Bundles_Compatibility constructor
         */
        private function __construct() {
            add_filter( 'yith_wapo_display_edit_product_link', array( $this, 'display_edit_product_link' ), 10, 2 );

            // Bundle item price on cart.
            add_filter( 'yith_wcpb_woocommerce_cart_item_price', array( $this, 'ywcpb_woocommerce_cart_item_price' ), 10, 3 );
        }

        /**
         * Display or not the edit product link depending on product bundles.
         *
         * @param boolean $value The boolean value.
         * @param array $cart_item The cart item array.
         * @return false|mixed
         */
        public function display_edit_product_link( $value, $cart_item ) {

            $is_bundled = isset( $cart_item['bundled_by'] ) && isset( $cart_item['bundled_item_id'] );

            return $is_bundled ? false : $value;

        }

        /**
         * Filter price in cart for items included in a bundle (support for YITH WooCommerce Product Bundle).
         *
         * @param string $price Cart item price.
         * @param float  $bundled_items_price Bundle items price.
         * @param array  $cart_item Cart item.
         *
         * @return string
         */
        public function ywcpb_woocommerce_cart_item_price( $price, $bundled_items_price, $cart_item ) {
            if ( isset( $cart_item['yith_wapo_options'] ) ) {

                $types_total_price = YITH_WAPO_Cart::get_instance()->get_total_add_ons_price( $cart_item );
                if ( isset( $cart_item['yith_wapo_sold_individually'] ) && $cart_item['yith_wapo_sold_individually'] ) {
                    $bundled_items_price = 0;
                }

                $price = wc_price( $bundled_items_price + $types_total_price );

                return $price;
            }
        }
    }
}
