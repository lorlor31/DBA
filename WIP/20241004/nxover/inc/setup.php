<?php

/***************************************************************************\
 *
 * Nexilogic Override Plugin
 *
 * ..........................................................................
 *
 * Fichier: setup.php
 * Version: 1.0
 * Auteur : Nexilogic
 *
 * ...........................................................................
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Nexilogic.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Nexilogic is strictly forbidden.
 * In order to obtain a license, please use the following contact form:
 * http://www.nexilogic.com/en/module/LICENCE.txt
 *
 * ...........................................................................
 *
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise à une licence commerciale
 * concédée par la société Nexilogic.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de Nexilogic est 
 * expressément interdite.
 * Pour obtenir une licence, veuillez contacter Nexilogic à l'adresse:
 * http://web.nexilogic.eu/module/LICENSE.txt
 *
 * ..........................................................................
 *
 * Copyright (c) 2023, Nexilogic. Tous droits réservés.                      
 *
\***************************************************************************/

define('NXOVER_DEBUG', true /*($_SERVER['REMOTE_ADDR'] == '88.169.186.216' || $_SERVER['REMOTE_ADDR'] == '2a01:e0a:a6e:b620:64a:73a8:cb8:5c5')*/);
define('NXOVER_LOG', true);

define('NXOVER_PLUGIN_INC_DIR', NXOVER_PLUGIN_DIR.'inc');
define('NXOVER_CLASS_DIR', NXOVER_PLUGIN_INC_DIR.'/classes');
define('NXOVER_DATA_DIR', NXOVER_PLUGIN_INC_DIR.'/data');
define('NXOVER_LOG_DIR', NXOVER_PLUGIN_INC_DIR.'/log');

define('NXOVER_FEE_AMOUNT_META_KEY', 'product-fee-amount');
define('NXOVER_ADDON_META_KEY', 'product-addon-id');
define('NXOVER_WCPF_CLASS_FILE', WP_PLUGIN_DIR.'/woocommerce-product-fees/classes/class-woocommerce-product-fees.php');

define('NXOVER_ECOTAX_FEE_NAME', 'éco-participation');

// -------------------------------------------------------------

if (NXOVER_DEBUG)
    ini_set('display_errors', 1);


// -------------------------------------------------------------



// -------------------------------------------------------------

require NXOVER_PLUGIN_INC_DIR.'/functions.php';

// require NXOVER_PLUGIN_INC_DIR.'/classes/options.class.php';
// require NXOVER_PLUGIN_INC_DIR.'/classes/log.class.php';
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        error_log('WooCommerce is loaded.');
        
        // Register hooks
        add_action('woocommerce_order_before_calculate_totals', 'nxover_order_before_calculate_totals', 100, 2);
        error_log('Hook woocommerce_order_before_calculate_totals registered.');
    } else {
        error_log('WooCommerce is not loaded.');
    }
}, 20);

// add_action('woocommerce_new_order_item', 'nxover_new_order_item', 10 ,3);
// add_action('woocommerce_checkout_create_order', 'nxover_woocommerce_checkout_create_order', 10 ,2);
// add_action('woocommerce_checkout_create_order_line_item', 'nxover_woocommerce_checkout_create_order_line_item', 10, 4);
add_action('ywraq_from_cart_to_order_item', 'nxover_ywraq_from_cart_to_order_item', 10, 4);
add_action('woocommerce_order_before_calculate_totals', 'nxover_order_before_calculate_totals', 100, 2);

// add_action('update_order_item_meta', 'nxover_update_order_item_meta', 10, 4);

if (NXOVER_DEBUG)
{

}
