<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Sequential Order Number Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'YITH_WooCommerce_Sequential_Order_Number' ) ) {

	/**
	 * YITH_WooCommerce_Sequential_Order_Number
	 */
	class YITH_WooCommerce_Sequential_Order_Number {

		/**Single instance of the class
		 *
		 * @var YITH_WooCommerce_Sequential_Order_Number
		 * #@since 1.0.0
		 */
		protected static $instance;
		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {
			// Load Plugin Framework !
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

			if ( is_admin() ) {
				YITH_Sequential_Order_Admin();
			}

			YWSON_Manager();
		}


		/**Returns single instance of the class
		 *
		 * @since 1.0.0
		 * @return YITH_WooCommerce_Sequential_Order_Number
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Load plugin_fw
		 *
		 * @since 1.0.0
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * This method is deprecated, valid for old custom codes
		 *
		 *
		 * @param WC_Order $order order .
		 *
		 * @deprecated since 1.1.0 Use YWSON_Manager()->generate_sequential_order_number
		 */
		public function create_progressive_numeration_new( $order ) {
			_deprecated_function( __METHOD__, '1.1.0', 'YWSON_Manager()->generate_sequential_order_number( $order )' );
			YWSON_Manager()->generate_sequential_order_number( $order );
		}
	}
}

/**
 * YITH_Sequential_Order_Number
 *
 * @return YITH_WooCommerce_Sequential_Order_Number
 */
function YITH_Sequential_Order_Number() { // phpcs:ignore WordPress.NamingConventions
	return YITH_WooCommerce_Sequential_Order_Number::get_instance();
}
