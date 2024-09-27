<?php
/**
 * Plugin Name: Shipping Packages for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/wc-shipping-packages
 * Description: Groups products in the cart into packages, so they can be shipped with different shipping methods.
 * Version: 1.1.34
 * Tested up to: 6.6
 * Author: OneTeamSoftware
 * Author URI: http://oneteamsoftware.com/
 * Developer: OneTeamSoftware
 * Developer URI: http://oneteamsoftware.com/
 * Text Domain: wc-shipping-packages
 * Domain Path: /languages
 *
 * Copyright: © 2024 FlexRC, 604-1097 View St, V8V 0G9, Canada. Voice 604 800-7879
 */

namespace OneTeamSoftware\WooCommerce\ShippingPackages;

require_once(__DIR__ . '/includes/ShippingPackages.php');

(new ShippingPackages())->register();
