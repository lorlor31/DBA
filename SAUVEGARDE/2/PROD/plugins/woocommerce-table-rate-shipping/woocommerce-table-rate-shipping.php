<?php
/**
 * Plugin Name: WooCommerce Table Rate Shipping
 * Plugin URI: https://woocommerce.com/products/table-rate-shipping/
 * Description: Table rate shipping lets you define rates depending on location vs shipping class, price, weight, or item count.
 * Version: 3.1.4
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Requires at least: 4.0
 * Tested up to: 6.3
 * Text Domain: woocommerce-table-rate-shipping
 * Copyright: © 2023 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * WC tested up to: 8.0
 * WC requires at least: 3.0
 *
 * Woo: 18718:3034ed8aff427b0f635fe4c86bbf008a
 *
 * @package woocommerce-shipping-table-rate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Class.
 */
class WC_Table_Rate_Shipping {

	/**
	 * Abort session key.
	 *
	 * @var string
	 */
	public static $abort_key = 'wc_table_rate_abort';

	/**
	 * Constructor.
	 */
	public function __construct() {
		define( 'TABLE_RATE_SHIPPING_VERSION', '3.1.4' ); // WRCS: DEFINED_VERSION.
		define( 'TABLE_RATE_SHIPPING_DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG && ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY ) );
		define( 'WC_TABLE_RATE_SHIPPING_MAIN_FILE', __FILE__ );

		add_filter( 'pre_site_transient_update_plugins', array( $this, 'filter_out_trs' ) );
		add_filter( 'site_transient_update_plugins', array( $this, 'filter_out_trs' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}

	/**
	 * We need to filter out woocommerce-table-rate-shipping from .org since that's a different
	 * plugin and users should not be redirected there.
	 *
	 * Note that in case this plugin is not activated, users will still be taken to the wrong .org
	 * site if they click on "View Details".
	 *
	 * See https://github.com/woocommerce/woocommerce-table-rate-shipping/issues/70 for more information.
	 *
	 * @param array $value List of plugins information.
	 * @return array
	 */
	public function filter_out_trs( $value ) {
		$plugin_base = plugin_basename( WC_TABLE_RATE_SHIPPING_MAIN_FILE );

		if ( isset( $value->no_update[ $plugin_base ] ) ) {
			unset( $value->no_update[ $plugin_base ] );
		}

		return $value;
	}

	/**
	 * Register method for usage.
	 *
	 * @param  array $shipping_methods List of shipping methods.
	 * @return array
	 */
	public function woocommerce_shipping_methods( $shipping_methods ) {
		$shipping_methods['table_rate'] = 'WC_Shipping_Table_Rate';
		return $shipping_methods;
	}

	/**
	 * Init TRS.
	 */
	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_deactivated' ) );
			return;
		}

		include_once __DIR__ . '/includes/functions-ajax.php';
		include_once __DIR__ . '/includes/functions-admin.php';

		/**
		 * Install check (for updates).
		 */
		if ( get_option( 'table_rate_shipping_version' ) < TABLE_RATE_SHIPPING_VERSION ) {
			$this->install();
		}

		add_filter( 'woocommerce_shipping_methods', array( $this, 'woocommerce_shipping_methods' ) );

		// Hooks.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'woocommerce_translations_updates_for_woocommerce-table-rate-shipping', '__return_true' );
		add_action( 'woocommerce_shipping_init', array( $this, 'shipping_init' ) );
		add_action( 'delete_product_shipping_class', array( $this, 'update_deleted_shipping_class' ) );
		add_action( 'woocommerce_before_cart', array( $this, 'maybe_show_abort' ), 1 );
		add_action( 'woocommerce_before_checkout_form_cart_notices', array( $this, 'maybe_show_abort' ), 20 );
	}

	/**
	 * Declare High-Performance Order Storage (HPOS) compatibility
	 *
	 * @see https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 *
	 * @return void
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php' );
		}
	}

	/**
	 * Localisation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-table-rate-shipping', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Row meta.
	 *
	 * @param  array  $links List of plugin links.
	 * @param  string $file  Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'woocommerce_table_rate_shipping_docs_url', 'https://docs.woocommerce.com/document/table-rate-shipping/' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'woocommerce-table-rate-shipping' ) ) . '">' . __( 'Docs', 'woocommerce-table-rate-shipping' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'woocommerce_table_rate_support_url', 'https://woocommerce.com/support/' ) ) . '" title="' . esc_attr( __( 'Visit Premium Customer Support Forum', 'woocommerce-table-rate-shipping' ) ) . '">' . __( 'Premium Support', 'woocommerce-table-rate-shipping' ) . '</a>',
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	/**
	 * Admin styles + scripts.
	 */
	public function admin_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'woocommerce_shipping_table_rate_styles', plugins_url( '/assets/css/admin.css', __FILE__ ), array(), TABLE_RATE_SHIPPING_VERSION );
		wp_register_script( 'woocommerce_shipping_table_rate_rows', plugins_url( '/assets/js/table-rate-rows' . $suffix . '.js', __FILE__ ), array( 'jquery', 'wp-util' ), TABLE_RATE_SHIPPING_VERSION, true );
		wp_localize_script(
			'woocommerce_shipping_table_rate_rows',
			'woocommerce_shipping_table_rate_rows',
			array(
				'i18n'               => array(
					'order'        => __( 'Order', 'woocommerce-table-rate-shipping' ),
					'item'         => __( 'Item', 'woocommerce-table-rate-shipping' ),
					'line_item'    => __( 'Line Item', 'woocommerce-table-rate-shipping' ),
					'class'        => __( 'Class', 'woocommerce-table-rate-shipping' ),
					'delete_rates' => __( 'Delete the selected rates?', 'woocommerce-table-rate-shipping' ),
					'dupe_rates'   => __( 'Duplicate the selected rates?', 'woocommerce-table-rate-shipping' ),
				),
				'delete_rates_nonce' => wp_create_nonce( 'delete-rate' ),
			)
		);
	}

	public function frontend_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'woocommerce_shipping_table_rate_checkout', plugins_url( '/assets/js/frontend-checkout' . $suffix . '.js', __FILE__ ), array( 'jquery' ), TABLE_RATE_SHIPPING_VERSION, true );
	}

	/**
	 * Load shipping class.
	 */
	public function shipping_init() {
		include_once __DIR__ . '/includes/class-wc-shipping-table-rate.php';
		include_once __DIR__ . '/includes/class-wc-shipping-table-rate-privacy.php';
	}

	/**
	 * Installer.
	 */
	public function install() {
		include_once __DIR__ . '/installer.php';
		update_option( 'table_rate_shipping_version', TABLE_RATE_SHIPPING_VERSION );
	}

	/**
	 * Delete table rates when deleting shipping class.
	 *
	 * @param int $term_id Term ID.
	 */
	public function update_deleted_shipping_class( $term_id ) {
		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'woocommerce_shipping_table_rates', array( 'rate_class' => $term_id ) );
	}

	/**
	 * WooCommerce Deactivated Notice.
	 */
	public function woocommerce_deactivated() {
		/* translators: %s: WooCommerce link */
		echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Table Rate Shipping requires %s to be installed and active.', 'woocommerce-table-rate-shipping' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
	}

	/**
	 * Show abort message when shipping methods are loaded from cache.
	 *
	 * @since 3.0.25
	 * @return void
	 */
	public function maybe_show_abort() {
		$abort = WC()->session->get( self::$abort_key );

		if ( ! is_array( $abort ) ) {
			return;
		}

        $packages = WC()->cart->get_shipping_packages();

        if ( count( $packages ) ) {
            foreach( $packages as $package_id => $package ) {
                $package_hash = WC_Table_Rate_Shipping::create_package_hash( $package );

                if ( isset( $abort[ $package_hash ] ) && ! wc_has_notice( $abort[ $package_hash ], 'notice' ) ) {
                    wc_add_notice( $abort[ $package_hash ], 'notice', array( 'wc_trs' => 'yes' ) );
                }

            }
        }
	}

    /**
     * Create hash string based on package.
     *
     * @since 3.0.26
     * @param array $package Shipping package.
     * @return string
     */
    public static function create_package_hash( $package ) {
        // Remove data objects so hashes are consistent.
        if ( isset( $package['contents'] ) ) {
            foreach ( $package['contents'] as $item_id => $item ) {
                if ( isset( $package['contents'][ $item_id ]['data'] ) ) {
                    unset( $package['contents'][ $item_id ]['data'] );
                }
            }
        }

        $package_to_hash = array_filter( $package, function( $key ) {
            return in_array( $key, array( 'contents', 'contents_cost', 'applied_coupons', 'user', 'destination' ) );
        }, ARRAY_FILTER_USE_KEY );

        // Calculate the hash for this package so we can tell if it's changed since last calculation.
        return 'wc_table_rate_' . md5( wp_json_encode( $package_to_hash ) . WC_Cache_Helper::get_transient_version( 'shipping' ) );
    }

}

new WC_Table_Rate_Shipping();

/**
 * Callback function for loading an instance of this method.
 *
 * @param mixed $instance Table Rate instance.
 * @return WC_Shipping_Table_Rate
 */
function woocommerce_get_shipping_method_table_rate( $instance = false ) {
	return new WC_Shipping_Table_Rate( $instance );
}
