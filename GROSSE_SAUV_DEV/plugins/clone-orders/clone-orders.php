<?php
/**
 * Plugin Name: WooCommerce Clone Orders
 * Plugin URI: https://woocommerce.com/products/clone-orders
 * Description: Clone customer orders to create new orders with the same customer and order details
 * Version: 1.5.7
 * Author: Vibe Agency
 * Author URI: https://vibeagency.uk
 * Developer: Vibe Agency
 * Developer URI: https://vibeagency.uk
 * Text Domain: clone-orders
 * Domain path: /languages
 *
 * Woo: 6505072:8df1e996d9ba6910c2cb1e9fbba8db17
 * WC requires at least: 6.9
 * WC tested up to: 7.5
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

use Vibe\Clone_Orders\Clone_Orders;

define( 'VIBE_CLONE_ORDERS_VERSION', '1.5.7' );

// Autoloader for all classes
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// Protect against conflicts and loading multiple copies of plugin
if ( ! function_exists( 'vibe_clone_orders' ) ) {
	// HPOS compatibility
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );

	/**
	 * Returns the singleton instance of the main plugin class
	 *
	 * @return Clone_Orders The singleton
	 */
	function vibe_clone_orders() {
		return Clone_Orders::instance();
	}

	// Initialise the plugin
	vibe_clone_orders();
}
