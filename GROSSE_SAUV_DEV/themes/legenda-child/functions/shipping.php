<?php

/**********
 * SHIPPING
 *********/

/**
 * Hide shipping rates when free shipping is available
 */
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100,2 );
function my_hide_shipping_when_free_is_available( $rates,$package ) {
	$nofreeshipping = array();
	$vcaisson = array();
	$vtaq = array();
	if (('vinco' !== $package['packageKey']) && ('armoire-plus' !== $package['packageKey'])){
		// Suppr. Livr. Offerte / freeshipping sauf Vinco
		foreach ( $rates as $rate_id => $rate ) {
			if ($rate !== null) {
				if ('free_shipping' !== $rate->method_id){
					$nofreeshipping[$rate_id] = $rate;
				}
			}
		}
		return !empty($nofreeshipping) ? $nofreeshipping : $rates;
	}
	elseif('vinco' === $package['packageKey']){
		// Conditions Vinco
		foreach ( $rates as $rate_id => $rate ) {
			if ($rate !== null) {
				// Si sup. 1500€
				if ( ( ( (0 < $rate->cost) && ('Messagerie' !== $rate->label) ) || ('Formule ECO [OFFERTE]' === $rate->label) ) && (1500 <= $package['contents_cost']) ) {
						$vcaisson[$rate_id] = $rate;
				// Si inf. 1500e, on suppr. Livr. offerte
				}elseif ( (0 < $rate->cost) && (1500 > $package['contents_cost']) ){
					$vcaisson[$rate_id] = $rate;
				}
			}
		}
		return !empty ($vcaisson) ? $vcaisson : $rates ;
	}
	elseif('armoire-plus' === $package['packageKey']){
		// Conditions Taquets Armoireplus
		foreach ( $rates as $rate_id => $rate ) {
			if ($rate !== null) {
				if ( 0 < $rate->cost) {
						$vtaq[$rate_id] = $rate;
				}
			}
		}
		return !empty ($vtaq) ? $vtaq : $rates ;
	}
	return $rates ;
}

/**
* Exclude certain zip codes (or post codes) from WooCommerce shipping
* @return array $available_methods
*/
// add_filter( 'woocommerce_package_rates', 'bryce_dont_ship_zip', 10 );
// function bryce_dont_ship_zip( $available_methods ) {
	// global $woocommerce;
	// $excluded_zips = array( '17111','17190','17123','17310','17360','17370','17390','17410','17480','17550','17580','17590','17630','17650','17670','17740','17840','17880','17940','22870','29242','56170','56317','56360','56510','56590','85330','85350','85630','85680','85740' );
	// if( in_array( $woocommerce->customer->get_shipping_postcode(), $excluded_zips ) ) {
		// $available_methods = array();
	// }
	// return $available_methods;
// }



// Ajoute les hooks pour l'action AJAX
// Ajoute des actions pour gérer les requêtes AJAX
// Ajoute des actions pour gérer les requêtes AJAX
add_action('wp_ajax_check_exclusion_status', 'handle_ajax_check_exclusion_status');
add_action('wp_ajax_nopriv_check_exclusion_status', 'handle_ajax_check_exclusion_status');
function handle_ajax_check_exclusion_status() {
    session_start(); // Assurez-vous que la session est démarrée
    $postcode = isset($_POST['postcode']) ? sanitize_text_field($_POST['postcode']) : '';
	$context = isset($_POST['context']) ? $_POST['context'] : 'panier'; 
	$suppliers_in_context = ($context === 'panier') ? get_suppliers_from_cart() : [];
    error_log("Suppliers in cart for AJAX: " . implode(", ", $suppliers_in_context));  // Log pour diagnostic
	
    $is_excluded = check_postcode_for_exclusion($postcode, null, $suppliers_in_context);
    $_SESSION['exclude_shipping'] = $is_excluded;
	 error_log("AJAX check exclusion status: " . ($is_excluded ? "Excluded by $is_excluded" : "Not excluded"));
    wp_send_json_success(array('exclude_shipping' => $is_excluded));
}
function check_postcode_for_exclusion($postcode, $order = null, $suppliers_in_context = null) {
    $json_path = get_stylesheet_directory() . '/functions/excluded_postcodes.json';
    $json_data = file_get_contents($json_path);
    if ($json_data === false) {
        return false;
    }
    $data = json_decode($json_data, true);
    if (is_null($data)) {
        return false;
    }
    if ($suppliers_in_context === null) {
        $suppliers_in_context = $order ? get_suppliers_from_order($order) : [];
    }
    foreach ($data as $supplier => $postcodes) {
        if (in_array($postcode, $postcodes)) {
			error_log("Postcode $postcode found under supplier $supplier");
			error_log("Suppliers in cart at check: " . implode(", ", $suppliers_in_context));  // Diagnostic supplémentaire
             if ($supplier === 'tous' || in_array($supplier, $suppliers_in_context)) {
				 error_log("Postcode $postcode is excluded by $supplier");
                return $supplier;
            } else {
            error_log("Supplier $supplier not in cart: " . implode(", ", $suppliers_in_context));
        }
        }
    }
	error_log("No exclusion found for postcode $postcode");
    return false;
}
function get_suppliers_from_order($order) {
    $products_by_supplier = array();
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $product_id = $item->get_product_id();
        if (get_post_type($product_id) == 'product_variation') {
            $parent_id = wp_get_post_parent_id($product_id);
            $parent_product = wc_get_product($parent_id);
            $supplier = $parent_product->get_meta('supplier');
        } else {
            if ($product) {
                $supplier = $product->get_meta('supplier');
            }
        }
        if (!empty($supplier)) {
            if (!isset($products_by_supplier[$supplier])) {
                $products_by_supplier[$supplier] = array();
            }
            $products_by_supplier[$supplier][] = $item;
        }
    }
    return array_keys($products_by_supplier);
}
function get_suppliers_from_cart() {
    $cart = WC()->cart->get_cart();
    $suppliers_in_cart = array();
    foreach ($cart as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $product->get_id();
		$supplier = '';
        if ($product->is_type('variation')) {
            $parent_id = wp_get_post_parent_id($product_id);
            $parent_product = wc_get_product($parent_id);
             $supplier = $parent_product ? $parent_product->get_meta('supplier') : '';
        } else {
            $supplier = $product->get_meta('supplier');
        }
        if (!empty($supplier) && !in_array($supplier, $suppliers_in_cart)) {
            $suppliers_in_cart[] = $supplier;
        }
    }
	error_log("Suppliers in cart: " . implode(", ", $suppliers_in_cart));
    return $suppliers_in_cart;
}
