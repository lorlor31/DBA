<?php
/**
 * Plugin Name: WooCommerce Marketplace Cart
 * Plugin URI: https://1teamsoftware.com/product/woocommerce-marketplace-cart/
 * Description: Redesigns Cart, Checkout and Order Review pages to show contents grouped into packages 
 *              with shipping method selection under each package to offer similar to Amazon and eBay shopping experience.
 * Version: 1.1.2
 * Tested up to: 6.1
 * WC requires at least: 5.0
 * WC tested up to: 7.3
 * Author: OneTeamSoftware
 * Author URI: http://oneteamsoftware.com
 * Text Domain: wc-marketplace-cart
 * Domain Path: /languages
 *
 * Copyright: © 2022 FlexRC, Canada.
 */

/*********************************************************************/
/*  PROGRAM          FlexRC                                          */
/*  PROPERTY         3-7170 Ash Cres                                 */
/*  OF               Vancouver BC   V6P 3K7                          */
/*  				 Voice 604 800-7879                              */
/*                                                                   */
/*  Any usage / copying / extension or modification without          */
/*  prior authorization is prohibited                                */
/*********************************************************************/

namespace OneTeamSoftware\WooCommerce\MarketplaceCart;

defined('ABSPATH') || exit;

if (file_exists(__DIR__ . '/includes/autoloader.php')) {
	require_once(__DIR__ . '/includes/autoloader.php');
} else if (file_exists('phar://' . __DIR__ . '/includes.phar/autoloader.php')) {
	require_once('phar://' . __DIR__ . '/includes.phar/autoloader.php');
}

if (class_exists(__NAMESPACE__ . '\\MarketplaceCart')) {
    (new MarketplaceCart(__FILE__))->register();
}
