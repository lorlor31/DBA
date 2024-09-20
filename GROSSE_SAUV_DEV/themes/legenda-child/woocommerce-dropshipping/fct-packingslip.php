<?php
// fct-packingslip.php


/***
**** Produit en Promotion
***/
if (!function_exists('product_info_promo')) {
function product_info_promo($prod_info, $supplier_info, $wpdb, $order_id, $is_email, $promo_ids, $promo_dates) {
    $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $prod_info['sku']));
    $product = wc_get_product($product_id);
	$promo_ids = get_post_meta($order_id, '_promo_pdt_ids', true) ?: '';
	$promo_dates = get_post_meta($order_id, '_promo_pdt_dates', true) ?: '';
	if ($product !== false && $product->get_type() == 'variation') {
		$category = get_the_terms($product->get_parent_id(), 'product_cat')[0]->slug;
	} else {
		$category = get_the_terms($product_id, 'product_cat')[0]->slug;
	}
	$is_variation = ($product !== false && $product->get_type() == 'variation');
    $vendor_price = get_post_meta($product_id, '_wc_cog_cost', true);

    $fournisseur_tab_promo = [71, 683];
    $nouvelle_promo = [61149,63990,4548];
    $fournisseur_tab_promo = array_merge($fournisseur_tab_promo, $nouvelle_promo);
    $fournisseur_tab_promo = array_unique($fournisseur_tab_promo);

    $attribut_promo = [];
    $is_promo = false;
    $promo_date_readable = '';
    $key_promo = false;
    $parent_id = $product->get_parent_id();
    $tab_promo = !empty($promo_ids) ? explode(',', $promo_ids) : [];
    $date_promo = !empty($promo_dates) ? explode(',', $promo_dates) : [];

    if (in_array($product_id, $fournisseur_tab_promo) || (!empty($parent_id) && in_array($parent_id, $fournisseur_tab_promo))) {
        $is_promo = true;
        $sale_price_dates_to = get_post_meta($product_id, '_sale_price_dates_to', true);
		
		if (!empty($sale_price_dates_to) && intval($sale_price_dates_to) > 0) {
            $promo_date_readable = 'jusqu\'au ' . date('d/m/Y', intval($sale_price_dates_to));
        } else {
            $key_promo = array_search($product_id, $tab_promo, true);
            if ($key_promo === false && !empty($parent_id)) {
                $key_promo = array_search($parent_id, $tab_promo, true);
            }
            if ($key_promo !== false && isset($date_promo[$key_promo])) {
                $promo_date_readable = $date_promo[$key_promo];
            }

        }
    }
	
    foreach ($attribut_promo as $attribut) {
        if ($product_id == $attribut['id_produit'] || $attribut['id_produit'] == '*') {
            $attribut_a_chercher = $attribut['name'];
            $attributs = $product->get_attributes();
            if (array_key_exists($attribut_a_chercher, $attributs) || $attribut_a_chercher == '*') {
                $valeur_attribut = $product->get_attribute($attribut_a_chercher);
                if ($valeur_attribut == $attribut['attr'] || $attribut['attr'] == '*') {
                    $key_promo = false;
                }
            }
        } else {
            $parent_id = wp_get_post_parent_id($product_id);
            if ($parent_id == $attribut['id_produit'] || $attribut['id_produit'] == '*') {
                $attribut_a_chercher = $attribut['name'];
                $attributs = $product->get_attributes();
                if (array_key_exists($attribut_a_chercher, $attributs) || $attribut_a_chercher == '*') {
                    $valeur_attribut = $product->get_attribute($attribut_a_chercher);
                    if ($valeur_attribut == $attribut['attr'] || $attribut['attr'] == '*') {
                        $key_promo = false;
                    }
                }
            }
        }
    }
    return [
        'category' => $category,
        'vendor_price' => $vendor_price,
        'is_promo' => $is_promo,
        'promo_date_readable' => $promo_date_readable,
        'key_promo' => $key_promo, 
		'product_id' => $product_id, 
		'is_variation' => $is_variation
    ];
}
}

if (!function_exists('get_product_ugs')) {
    function get_product_ugs($product_id, $wpdb) {
		global $wpdb;
        $meta_value_serialized = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = '_woocommerce_gpf_data'",
            $product_id
        ));
        $product_mpn = 'N/A';
        if ($meta_value_serialized) {
            $meta_value = maybe_unserialize($meta_value_serialized);
            if (is_array($meta_value) && isset($meta_value['mpn'])) {
                $product_mpn = $meta_value['mpn'];
            }
        }
        return $product_mpn;
    }
}


/***
**** Groupe Attribut / addon simple et addon Produit
***/
if (!function_exists('identify_attributes_and_addons')) {
    function identify_attributes_and_addons($order_item_meta) {
        $attributes = [];
        $addons = [];
error_log("Lancement de identify_attributes_and_addons");
        foreach ($order_item_meta as $item_meta) {
            $key = $item_meta->key;
            $value = trim(strip_tags($item_meta->display_value));
error_log("item_meta de identify_attributes_and_addons " . print_r($item_meta, true));
            if (strpos($key, 'pa_') === 0) {
                $attributes[] = ['key' => $key, 'value' => $value];
            } else {
                $addons[] = ['key' => $key, 'value' => $value];
            }
        }

        return [
            'attributes' => $attributes,
            'addons' => $addons,
        ];
    }
}
if (!function_exists('get_id_with_sku')) {
	function get_id_with_sku($sku){
		global $wpdb;

		$product_id = $wpdb->get_var($wpdb->prepare("
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key='_sku' AND meta_value='%s'
		", $sku));
		return $product_id;
	}
}
if (!function_exists('process_ywapo_meta_data')) {
    function process_ywapo_meta_data($ywapo_meta_data, $order_item_meta) {

        global $wpdb;
        $addons_info = [];

        foreach ($ywapo_meta_data as $meta_item) {
			
            if (is_array($meta_item)) {
                foreach ($meta_item as $meta_key => $meta_value) {
                    if (strpos($meta_value, 'product-') === 0) {
						$addon_id = explode('-', $meta_key)[0];
						$addon_settings = get_addon_settings($addon_id);
						$addon_title = $addon_settings['title'] ?? '';
                        $quantity = 1; // Quantité par défaut

                        foreach ($order_item_meta as $meta) {
                            if ($meta->key === $addon_title) {
                                $quantity = extract_quantity_from_string($meta->value);
								$addon_info = preg_replace('/\s*\(.*?\)/', '', $meta->value);
                                break;
                            }
                        }

                        preg_match('/product-(\d+)/', $meta_value, $matches);
                        $product_id = $matches[1] ?? 0;

                        $product_cog = get_post_meta($product_id, '_wc_cog_cost', true) ?: "N/A";
                        $product_mpn = get_product_ugs($product_id, $wpdb);

                        $addons_info[] = [
                            'key' => $meta_key,
                            'value' => $meta_value,
                            'settings' => $addon_settings,
                            'title' => $addon_title,
                            'info' => $addon_info,
                            'quantity' => $quantity,
                            'product_id' => $product_id,
                            'product_cog' => $product_cog,
                            'product_mpn' => $product_mpn,
                        ];
                    }
                }
            }
        }
        return $addons_info;
    }
}

if (!function_exists('get_addon_settings')) {
    function get_addon_settings($addon_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yith_wapo_addons';
        $query = $wpdb->prepare("SELECT settings FROM {$table_name} WHERE ID = %d", $addon_id);
        $result = $wpdb->get_var($query);
        return maybe_unserialize($result);
    }
}
if (!function_exists('extract_quantity_from_string')) {
    function extract_quantity_from_string($value) {
        if (preg_match('/^(\d+)\s*x\s*/', $value, $matches)) {
            return (int)$matches[1];
        }
        return 1; // Quantité par défaut si non spécifiée
    }
}
if (!function_exists('get_attribute_name')) {
    function get_attribute_name($attribute_key) {
        // Supprimer le préfixe "pa_" pour obtenir le nom de la taxonomie
        $taxonomy = str_replace('pa_', '', $attribute_key);
        $taxonomy = 'pa_' . $taxonomy; // Just to ensure it is prefixed correctly
        // Récupérer la taxonomie
        $taxonomy_obj = get_taxonomy($taxonomy);
        if ($taxonomy_obj) {
            $attribute_name = $taxonomy_obj->labels->name;
            // Supprimer le préfixe "Produit " si présent
            $attribute_name = str_replace('Produit ', '', $attribute_name);
            return $attribute_name;
        }
        return $attribute_key; // Retourner la clé si la taxonomie n'existe pas
    }
}
if (!function_exists('process_addons_for_order_items')) {
    function process_addons_for_order_items($item_order) {
		error_log("LANCEMENT  de process_addons_for_order_items ");
		$addons_for_items  = [];
        foreach ($item_order as $order_item) {
			$order_item_id = $order_item->get_id();
			$addons_simple = [];
			$addons_product = [];
            $meta_data_array = $order_item->get_meta_data();
            foreach ($meta_data_array as $meta_data) {
					error_log("meta_data de process_addons_for_order_items " . print_r($meta_data, true));
                if (($meta_data->key === '_ywapo_meta_data') || ($meta_data->key ==='_ywraq_wc_ywapo')) {
                    $ywapo_meta_data = $meta_data->value;
                    $addons_info = process_ywapo_meta_data($ywapo_meta_data, $order_item->get_meta_data());
                    foreach ($ywapo_meta_data as $meta_item) {
                        if (is_array($meta_item)) {
                            foreach ($meta_item as $meta_key => $meta_value) {
                                if (strpos($meta_value, 'product-') === 0) {
                                    $product_addon_found = false;
                                    foreach ($addons_info as $addon) {
                                        if ($addon['key'] === $meta_key) {
                                            $addons_product[] = $addon;
                                            $product_addon_found = true;
                                            break; // Sortir de la boucle des addons_info
                                        }
                                    }
                                    if (!$product_addon_found) {
                                        $addons_product[] = [
                                            'key' => $meta_key,
                                            'value' => $meta_value
                                        ];
                                    }
                                } else {
									$addon_id = explode('-', $meta_key)[0];
									$addon_settings = get_addon_settings($addon_id);
									$addon_title = $addon_settings['title'] ?? '';
									
                                    $addons_simple[] = [
                                        'key' => $meta_key,
                                        'value' => $meta_value,
                                        'title' => $addon_title
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            $addons_for_items[$order_item_id] = [
                'addons_simple' => $addons_simple,
                'addons_product' => $addons_product
            ];
        }
        return $addons_for_items;
    }
}

?>
