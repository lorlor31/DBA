<?php
/**
 * YITH Composite Products for WooCommerce compatibility.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddons
 */

defined( 'YITH_WCP_PREMIUM' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Composite_Compatibility' ) ) {
    /**
     * Compatibility Class
     *
     * @class   YITH_WAPO_Composite_Compatibility
     * @since   4.2.1
     */
    class YITH_WAPO_Composite_Compatibility {

        /**
         * Single instance of the class
         *
         * @var YITH_WAPO_Composite_Compatibility
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WAPO_Composite_Compatibility
         */
        public static function get_instance() {
            return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
        }

        /**
         * YITH_WAPO_Composite_Compatibility constructor
         */
        private function __construct() {

            add_filter( 'yith_wapo_product_price_on_cart', array( $this, 'fix_product_pricing' ), 10, 4 );


        }

        /**
         * Fix Cart price when composite is calculated per item pricing.
         *
         * @param $price
         * @param $product
         * @param $cart_item
         * @return float|int|mixed
         */
        public function fix_product_pricing( $price, $product, $cart_item, $is_cart_item = false ) {

            if ( 'yith-composite' === $product->get_type() && ! empty( $cart_item['yith_wcp_component_data'] ) ) {

                if ( $product->isPerItemPricing() ) {
                    $component_data = $cart_item['yith_wcp_component_data'];
                    $composite_base_price = isset( $cart_item['data'] ) ? yit_get_display_price( $cart_item['data'] ) : $component_data['product_base_price'];
                    $composite_total = $this->getPricePerPricing( $product, $component_data, $cart_item['key'], $cart_item['quantity'] );

                    $new_subtotal = ( $composite_base_price * $cart_item['quantity'] ) + $composite_total;

                    if ( $is_cart_item ) {
                        $new_subtotal = $composite_total;
                    }

                    $price = $new_subtotal;

                }
            }

            return $price;

        }


        /**
         * @param $product
         * @param $component_data
         * @param $cart_item_key
         *
         * @return int|string
         */
        private function getPricePerPricing( $_product, $component_data, $cart_item_key, $global_quantity ) {

            $total_price              = 0;
            $selection_data           = $component_data['selection_data'] ?? array();
            $selection_variation_data = $component_data['selection_variation_data'] ?? array();

            foreach( $selection_data as $product_id ) {

                $product = wc_get_product( $product_id );
                if ( $product instanceof WC_Product_Variable ) {
                    continue;
                }
                if ( $product instanceof WC_Product ) {
                    $price = yit_get_display_price( $product );
                    $total_price += $price;
                }

            }

            foreach( $selection_variation_data as $variation_id ) {
                $variation = wc_get_product( $variation_id );

                if ( $variation instanceof WC_Product_Variation ) {
                    $price = yit_get_display_price( $variation );
                    $total_price += $price;
                }
            }


            return $total_price;
        }



    }
}
