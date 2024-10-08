<?php
/**
 * YITH Subscription compatibility.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddons
 */

defined( 'YITH_YWSBS_PREMIUM' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Subscription_Compatibility' ) ) {
    /**
     * Compatibility Class
     *
     * @class   YITH_WAPO_Subscription_Compatibility
     * @since   4.2.1
     */
    class YITH_WAPO_Subscription_Compatibility {

        /**
         * Single instance of the class
         *
         * @var YITH_WAPO_Subscription_Compatibility
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WAPO_Subscription_Compatibility
         */
        public static function get_instance() {
            return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
        }

        /**
         * YITH_WAPO_Subscription_Compatibility constructor
         */
        private function __construct() {
            add_filter( 'yith_wapo_display_edit_product_link', array( $this, 'display_edit_product_link' ), 10, 2 );
        }

        /**
         * Display or not the edit product link depending if is the subscription being switching in the cart.
         *
         * @param boolean $value The boolean value.
         * @param array $cart_item The cart item array.
         * @return false|mixed
         */
        public function display_edit_product_link( $value, $cart_item ) {

            $is_switching = isset( $cart_item['ywsbs-subscription-switch'] );

            return $is_switching ? false : $value;

        }

    }
}
