<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    vosfactures.fr
 *  @copyright 2020 vosfactures.fr
 *  @license   LICENSE.txt
*/

/**
	 * The file that defines the core plugin class
	 *
	 * A class definition that includes attributes and functions used across both the
	 * public-facing side of the site and the admin area.
	 *
	 * @link  http://example.com
	 * @since 1.0.0
	 *
	 * @package    firmlet
	 * @subpackage firmlet/includes
	 */

	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      1.0.0
	 * @package    firmlet
	 * @subpackage firmlet/includes
	 * @author     VosFactures
	 */
class Vosfactures {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    VosfacturesLoader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $name The string used to uniquely identify this plugin.
	 */
	protected $name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	private static $instance;

	/**
	 * Returns a reference to static module in order not to copy
	 * this class whenever this is called.
	 *
	 * @note Calling this function should be done by " & self::get_instance() "
	 */
	public static function & getInstance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		self::$instance     = $this;
		$this->version      = '1.3.1';
		$this->name         = 'vosfactures';
		$this->display_name = 'VosFactures';
		$this->firmlet      = 'VF';

		$this->load_dependencies();
		$this->set_locale();
		$this->plugin_admin = new VosfacturesAdmin( $this->get_name(), $this->get_version(), $this->get_firmlet() );
		$this->define_admin_hooks( $this->plugin_admin );
		$this->define_public_hooks();
		$this->define_metabox_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - VosfacturesLoader. Orchestrates the hooks of the plugin.
	 * - Vosfacturesi18n. Defines internationalization functionality.
	 * - VosfacturesAdmin. Defines all hooks for the admin area.
	 * - VosfacturesPublic. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-firmlet-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-firmlet-public.php';

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-api.php';

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-invoice.php';

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-database.php';

		$this->loader = new VosfacturesLoader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Vosfacturesi18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Vosfacturesi18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */


	private function define_admin_hooks( $plugin_admin ) {
		// $plugin_admin = new VosfacturesAdmin($this->get_name(), $this->get_version(), $this->get_firmlet());

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'invoices_with_errors_check' );
		$this->loader->add_action( 'wp_ajax_invoice_handler', $plugin_admin, 'invoice_handler' );
		$this->loader->add_action( 'wp_ajax_nopriv_invoice_handler', $plugin_admin, 'invoice_handler' );
		$this->loader->add_action( 'wp_ajax_recreate_table', $plugin_admin, 'recreate_table' );
		$this->loader->add_action( 'wp_ajax_nopriv_recreate_table', $plugin_admin, 'recreate_table' );

		$this->loader->add_action( 'woocommerce_checkout_fields', $plugin_admin, 'tax_no_override_checkout_fields' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_admin, 'tax_no_checkout_field_update_order_meta' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_tax_no_after_order_details', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_admin, 'firmlet_completed', 10, 1 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'firmlet_error_notice' );
		$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_admin, 'firmlet_after_processing_checkout', 10, 1 );
		$this->loader->add_action( 'woocommerce_process_shop_order_meta', $plugin_admin, 'firmlet_after_new_admin_order', 51, 1 );
		$this->loader->add_action( 'woocommerce_view_order', $plugin_admin, 'vf_view_order', 20 );
		$this->loader->add_action( 'before_delete_post', $plugin_admin, 'vf_after_order_delete', 10, 1 );

		$this->loader->add_filter( 'woocommerce_admin_billing_fields', $plugin_admin, 'tax_no_checkout_field_display_admin_order_meta' );
		$this->loader->add_filter( 'is_protected_meta', $plugin_admin, 'tax_no_exclude_custom_fields', 10, 2 );
		$this->loader->add_filter( 'plugin_action_links_' . FIRMLET_FILE, $plugin_admin, 'link_settings' );

		$this->loader->add_filter( 'woocommerce_integrations', $plugin_admin, 'add_integration' );
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_admin, 'firmlet_processing', 10, 1 );

		$this->loader->add_action( 'woocommerce_scheduled_subscription_payment', $plugin_admin, 'firmlet_after_scheduled_subscription_payment', 10, 1 );

		$this->loader->add_action( 'woocommerce_payment_complete', $plugin_admin, 'firmlet_after_payment_complete', 10, 1 );

		$this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'vf_shop_order_column' );
		$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'vf_orders_list_column_content', 10, 2 );

		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'vf_wp_dashboard_setup' );
		$this->loader->add_action( 'woocommerce_payment_complete', $plugin_admin, 'firmlet_after_payment_complete', 10, 1 );

		$this->loader->add_filter( 'bulk_actions-edit-shop_order', $plugin_admin,'firmlet_bulk_actions_edit_shop_order' , 10, 1);
		$this->loader->add_filter( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'firmlet_issue_bulk_action_edit_shop_order', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new VosfacturesPublic( $this->get_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to metaboxes
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_metabox_hooks() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-firmlet-admin-metaboxes.php';
		$plugin_metaboxes = new VosfacturesAdmin_Metaboxes( $this->get_name(), $this->get_version() );

		$this->loader->add_action( 'add_meta_boxes', $plugin_metaboxes, 'add_metaboxes' );
	} // define_metabox_hooks()

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string    The name of the plugin.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return VosfacturesLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * Retrieve the firmlet version of the plugin.
	 */
	public function get_firmlet() {
		return $this->firmlet;
	}

	/**
	 * Checks if $this->firmlet is in a list of permitted firmlets to be run
	 *
	 * @example correct_firmlet('FT', 'VF');
	 *
	 * @param  mixed Permitted Firmlet (string or array)
	 * @param  string ...
	 * @return bool is this firmlet in a list?
	 */
	public function correct_firmlet() {
		$args = func_get_args();
		return in_array(
			$this->firmlet,
			gettype( reset( $args ) ) === 'array' ? reset( $args ) : $args
		);
	}

	public function is_configured() {
		$success = empty( get_option( 'woocommerce_firmlet_settings' )['errors'] );
		$module  = firmlet_vosfactures();
		if ( $module->correct_firmlet( 'FT' ) ) {
			$success = $success && ! empty( get_option( 'woocommerce_firmlet_settings' )['auto_issue'] ) && ! empty( get_option( 'woocommerce_firmlet_settings' )['issue_kind'] );
		}
		return $success;
	}
}
