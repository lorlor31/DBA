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
	 * @link    vosfactures.fr
	 * @since   1.0.0
	 * @package Vosfactures
	 *
	 * Plugin Name: Vosfactures
	 * Plugin URI: https://woocommerce.com/products/vosfactures/
	 * Description: The module allows you to issue documents (VAT invoices and receipts) for orders in your VosFactures account.
	 * Version: 1.3.9
	 * Author: VosFactures
	 * Author URI: vosfactures.fr
	 * Text Domain: firmlet
	 * Domain Path: /languages
	 *
	 * Woo: 3965320:410ee7b489a165da74edcc0f691dab47
	 * WC requires at least: 3.2
	 * WC tested up to: 3.4
	 *
	 * Copyright: © 2009-2019 WooCommerce.
	 * License: Software License Agreement
	 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Used for referring to the plugin file or basename
if ( ! defined( 'FIRMLET_FILE' ) ) {
	define( 'FIRMLET_FILE', plugin_basename( __FILE__ ) );
}

	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define( 'VOSFACTURES_DEBUG', 0 );
	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-firmlet-activator.php
	 */
function firmlet_activate() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-firmlet-activator.php';
	load_plugin_textdomain( 'firmlet', false, '/vosfactures/languages' );
	$activate = new VosfacturesActivator();
	$activate->activate();
	$module    = firmlet_vosfactures();
	$notices   = get_option( 'firmlet_deferred_admin_notices', array() );
	$notices[] = sprintf( esc_html__( 'To get started, fill in the API token of your %s account in the', 'firmlet' ), $module->display_name ) . ' ' . sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-settings&tab=integration&section=firmlet' ) ), esc_html__( 'Settings page', 'firmlet' ) );
	update_option( 'firmlet_deferred_admin_notices', $notices );
}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-firmlet-deactivator.php
	 */
function firmlet_deactivate() {
	 include_once plugin_dir_path( __FILE__ ) . 'includes/class-firmlet-deactivator.php';
	VosfacturesDeactivator::deactivate();
}

	register_activation_hook( __FILE__, 'firmlet_activate' );
	register_deactivation_hook( __FILE__, 'firmlet_deactivate' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-firmlet.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since 1.0.0
	 */
function firmlet_run() {
	$plugin = new Vosfactures();
	$plugin->run();
}

	/**
	 * Returns a reference to static module in order not to copy
	 * this class whenever this is called.
	 *
	 * @since 1.0.0
	 */
function firmlet_vosfactures() {
	$module = &Vosfactures::getInstance();
	return $module;
}

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

firmlet_run();
