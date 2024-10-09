<?php
/**
 * WooCommerce Multi Currency compatibility.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddons
 */

defined( 'WCML_VERSION' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_WCML_Multi_Currency_Compatibility' ) ) {
    /**
     * Compatibility Class
     *
     * @class   YITH_WAPO_WCML_Multi_Currency_Compatibility
     * @since   4.15.0
     */
    class YITH_WAPO_WCML_Multi_Currency_Compatibility {

        /**
         * Single instance of the class
         *
         * @var YITH_WAPO_WCML_Multi_Currency_Compatibility
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WAPO_WCML_Multi_Currency_Compatibility
         */
        public static function get_instance() {
            return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
        }

        /**
         * YITH_WAPO_WCML_Multi_Currency_Compatibility constructor
         */
        private function __construct() {
            add_filter( 'yith_wapo_get_addon_price', array( $this, 'modify_addon_price' ), 10, 5 );
            add_filter( 'yith_wapo_get_addon_sale_price', array( $this, 'modify_addon_price' ), 10, 5 );
            add_filter( 'yith_wapo_get_product_addon_price', array( $this, 'modify_addon_price' ), 10, 5 );
            add_filter( 'yith_wapo_print_blocks_currency', array( $this, 'get_current_currency' ) );
            add_filter( 'yith_wapo_blocks_product_price', array( $this, 'modify_blocks_product_price' ), 10 );
            add_filter( 'woocommerce_available_variation', array( $this, 'modify_available_variation' ), 10 );
        }

        /**
         * Modify the variation currency when loading in the Cart modal.
         *
         * @param array $args The array of variation arguments from get_available_variation() function.
         * @return mixed
         */
        public function modify_available_variation( $args ) {
            if ( isset( $_POST['action'] ) && 'ywapo_load_product_template' === $_POST['action'] ) {
                if ( isset( $args['display_price'] ) && $args['display_price'] > 0 ) {
                    $args['display_price'] = $this->convert_price( $args['display_price'] );
                }
            }

            return $args;
        }

        /**
         * Modify the block product price when loading the variation.
         *
         * @param float $price The price to convert.
         * @return float|int|mixed
         */
        public function modify_blocks_product_price( $price = 0 ) {
            return isset( $_POST['action'] ) && 'live_print_blocks' === $_POST['action'] ? $this->convert_price( $price ) : $price;
        }

        /**
         * Modify the current price depending on currency
         *
         * @param float   $price The current price.
         * @param boolean $allow_modification Force to allow the convert of the price.
         * @param string  $price_method The price method of the add-on option.
         * @param string  $price_type The price type of the add-on option.
         * @param YITH_WAPO_Addon $addon The add-on.
         *
         * @return float
         */
        public function modify_addon_price( $price, $allow_modification = false, $price_method = 'free', $price_type = 'fixed', $addon = null ) {

            if ( $addon instanceof YITH_WAPO_Addon && 'product' === $addon->get_type() && isset( $_POST['action'] ) && ( 'live_print_blocks' === $_POST['action'] ) ) {
                $allow_modification = true;
            }
            if ( isset( $_POST['action'] ) && 'ywapo_load_product_template' === $_POST['action'] ) {
                $allow_modification = true;
            }

            if ( 'free' !== $price_method || $allow_modification ) {
                if ( 'percentage' !== $price_type || $allow_modification ) {
                    $this->convert_price( $price );
                }
            }

            return $price;
        }

        /**
         * Convert the price passed.
         * @param float $price The price to convert.
         * @return float|int|mixed
         */
        public function convert_price( $price = 0 ) {
            if ( function_exists( 'wcml_convert_price' ) ) {
                $price = wcml_convert_price( $price, $this->get_current_currency() );
            }
            return $price;
        }

        /**
         * Get current currency from user.
         * @return string
         */
        public function get_current_currency() {
            if ( class_exists( 'woocommerce_wpml' ) ) {
                global $woocommerce_wpml;
                $multi_currency = $woocommerce_wpml->multi_currency;

                if ( method_exists( $multi_currency, 'get_client_currency' ) ) {
                    return $multi_currency->get_client_currency();
                }
            }

            return get_woocommerce_currency(); // Retorna la moneda por defecto si no está activado WPML WooCommerce Multilingual
        }
    }
}
