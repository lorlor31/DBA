<?php


/***************************************************************************\
 *
 * Nexilogic Override Plugin
 *
 * ..........................................................................
 *
 * Fichier: functions.php
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

function nxover_WooCommerce_Product_Fees__get_fees($obj, $cart)
{
    // nxover_log('nxover_WooCommerce_Product_Fees__get_fees()');
    if (NXOVER_DEBUG) {
        //     die("----- UHU -----");
        //     echo "<pre>-- UHU --";
        //     print_r($line_items_fee);
        //     echo "</pre>";
    }
    $fees = [];
    error_log('coucou1');


    if ($obj->maybe_remove_fees_for_coupon($cart))
        return $fees;

    foreach ($cart->get_cart() as $cart_item => $item) {

        // base
        $item_data = [
            'id'           => $item['data']->get_id(),
            'variation_id' => $item['variation_id'],
            'parent_id'    => $item['data']->get_parent_id(),
            'qty'          => $item['quantity'],
            'price'        => $item['data']->get_price()
        ];

        if ($fee = $obj->get_fee_data($item_data)) {
            $fee_id        = strtolower($fee['name']);
            $fee_tax_class = $obj->get_fee_tax_class($item['data']);

            if (isset($fees[$fee_id]) && 'combine' === get_option('wcpf_name_conflicts', 'combine'))
                $fees[$fee_id]['amount'] += $fee['amount'];
            else
                $fees[$fee_id] = apply_filters('wcpf_filter_fee_data', [
                    'name'      => $fee['name'],
                    'amount'    => $fee['amount'],
                    'taxable'   => ($fee_tax_class !== '_no_tax'),
                    'tax_class' => $fee_tax_class
                ], $item_data);
        }

        $qty_addon = $item['yith_wapo_qty_options'];
        $options = $item['yith_wapo_options'] ?? false;
        if ($options) {
            // nxover_log('options:');
            // nxover_log($options);
            // nxover_log('qty_addon:');
            // nxover_log($qty_addon);
            foreach ($options as $opt) {
                foreach ($opt as $key => $v) {
                    // nxover_log("meta value = $v");

                    if ($p_id = nxover_addon_id($v)) {
                        // nxover_log("product = $p_id");

                        $item_data['id']            = $p_id;
                        $item_data['variation_id']  = 0;
                        error_log(print_r('$p_id est: ' . $p_id, true));
                        error_log(print_r('$qty_addon' . $qty_addon, true));
                        error_log(print_r('$options' . $options, true));

                        $quantity = $qty_addon[$key] ?? 1;
                        // nxover_log("$p_id = $quantity");	
                        if ($fee = $obj->get_fee_data($item_data)) {
                            error_log('coucou2');

                            // nxover_log("fee = $fee_id - ".$fee['amount']);
                            $fee['amount'] = $fee['amount'] * $quantity;
                            $fee_id        = strtolower($fee['name']);
                            $fee_tax_class = $obj->get_fee_tax_class($item['data']);

                            if (isset($fees[$fee_id]))
                                $fees[$fee_id]['amount'] += $fee['amount'];
                            else
                                $fees[$fee_id] = apply_filters('wcpf_filter_fee_data', [
                                    'name'      => $fee['name'],
                                    'amount'    => $fee['amount'],
                                    'taxable'   => ($fee_tax_class !== '_no_tax'),
                                    'tax_class' => $fee_tax_class
                                ], $item_data);
                        }
                    }
                }
            }
        } else {
            // nxover_log('** no options for item:');
            // nxover_log($item);
        }
    }

    // nxover_log('fees:');
    // nxover_log($fees);

    return $fees;
}

function nxover_order_before_calculate_totals($and_taxes, $order)
{
    // nxover_log('nxover_order_before_calculate_totals called.');
    error_log('coucou4');

    // // Code pour récupérer l'id de l'addon et sa quantité depuis $order
    // Récupérer les meta de la commande
    $all_meta = $order->get_meta_data();
    // $raqContent contient infos sur le produit et ses addons
    if ($order->get_meta('_raq_request')) {
        $raqContent = $order->get_meta('_raq_request')["raq_content"];
    }
    //$addonsQty contiendra l'id du produit correspondant à l'addon et la quantité
    $addonsQty =[];
    //Tableau pour stocker $yithPid et $yithFormattedId 
    $yithIdArray=[];
    // Parcourir $raqContent pour en extraire les infos de l'addon
    foreach ($raqContent as $raqKey => $raqValue) {
        foreach ($raqValue as $raqValK => $raqValV) {
            if ($raqValK == 'yith_wapo_options') {
                $yithWapoOpt = $raqValue[$raqValK]; // tableau contenant des tableaux avec key="361-0" et value = "product-3648-1" par exp
            }
            foreach ($yithWapoOpt as $k => $v) {
                foreach ($v as $yithPid => $yithCompleteId) {
                    // Formatter l'id yith en id de produit WC
                    preg_match('/product-(\d+)-\d+/', $yithCompleteId, $matches);
                    $addonId = $matches[1] ;
                    $yithIdArray[$yithPid] = $addonId;
                }
            }
            if ($raqValK == 'yith_wapo_qty_options') {
                $yithWapoQtyOpt = $raqValue[$raqValK];
            }
            // Pour chq addon, récupérer l'id et la quantité 
            foreach ($yithWapoQtyOpt as $yithRéf => $yithQty) {
                // Comparer le tableau des ids et celui des quantités
                foreach ($yithIdArray as $yithPid => $addonId) {
                    if ($yithRéf == $yithPid) {
                        $addonsQty[$addonId] = $yithQty;
                    }
                }
            }
        }
    }
    ob_start();
    var_dump($addonsQty);
    // Initialisation dui total de l'ecotax avec le total de l'ecotax des addons
    $total_et = 0;

    foreach($addonsQty as $k =>$v) {
        $product_fee_amount = get_post_meta($k, 'product-fee-amount', true);
        // var_dump($product_fee_amount);
        $total_et += (int)$product_fee_amount*(int)$v ;
        var_dump($total_et);
    }

    $output = ob_get_clean();
    $total = "total_et  :\n" . $output;
    error_log($total);



    foreach ($order->get_items() as $item) {
        $p      = $item->get_product();
        $qty    = $item->get_quantity();
        $p_id   = $item->get_product_id();

        ob_start();  
        var_dump($total_et);
        $output = ob_get_clean();
        $errorMessage = "    $total_et boucle1:\n" . $output;
        error_log($errorMessage);
        // nxover_log("Main item: $p_id");
        // nxover_log("Quantity item: $qty");

        if ($et = nxover_get_product_ecotax($p_id, $item->get_variation_id(), $qty, $item->get_total() / $qty)) {
            // nxover_log('ET = '.$et['amount']);
            //             nxover_log($item);
            $total_et += $et['amount'];
            ob_start();  
        var_dump($total_et);
        $output = ob_get_clean();
        $errorMessage = "    $total_et boucle2:\n" . $output;
        error_log($errorMessage);
        }
        // On rentre pas dans cette boucle bien qu'on ait des addons au produit
        if ($addons = nxover_get_order_item_addons($item)) {
            foreach ($addons as $p) {
                // nxover_log('addon: '.$p['p_id']);

                if ($et = nxover_get_product_ecotax($p['p_id'], 0, $qty)) {
                    // nxover_log('ET = '.$et['amount']);
                    //                     nxover_log($p);
                    $total_et += $et['amount'];
                    ob_start();  
        var_dump($total_et);
        $output = ob_get_clean();
        $errorMessage = "    $total_et boucle3:\n" . $output;
        error_log($errorMessage);
                }
            }
        }
    }

    if ($total_et > 0) {
        $new = true;

        foreach ($order->get_items('fee') as $item_id => $item) {
            if ($item->get_name() == NXOVER_ECOTAX_FEE_NAME) {
                // nxover_log('ET exists : '.$item->get_total());
                // nxover_log("replace : $total_et");
                ob_start();  
                var_dump($total_et);
                $output = ob_get_clean();
                $errorMessage = "    $total_et boucle4:\n" . $output;
                error_log($errorMessage);
                $item->set_total($total_et);
                $item->save();
                $new = false;
                break;
            }
        }

        if ($new) {
            // nxover_log('ET new');
            // nxover_log("create : $total_et");

            $item = new WC_Order_Item_Fee();
            $item->set_name(NXOVER_ECOTAX_FEE_NAME);
            $item->set_total($total_et);
            $item->save();
            $order->add_item($item);
            $order->save();
        }
    }
}

function nxover_get_order_item_addons($item)
{
    error_log('coucou6');
    error_log($item);
    $products = [];

    //laure à virer pour debug
    foreach ($item->get_meta_data() as $m) {
        foreach ($m as $key=>$value) {
            ob_start();  
            var_dump($key,$value);
            $output = ob_get_clean();
            $errorMessage = "keyetval:\n" . $output;
            error_log($errorMessage);
        }


        if (nxover_is_ywapo_meta($m->key)) {
            foreach ($m->value as $opt) {
                foreach ($opt as $k => $v) {      
                    if ($p_id = nxover_addon_id($v))
                    $products[] = ['opt' => explode('-', $k), 'p_id' => $p_id];
            }
        }
    }
}


    return $products;
}



function nxover_get_product_ecotax($p_id, $v_id, $qty, $price = 0)
{
    error_log('coucou3');

ob_start();  
// Infos du produit principal
var_dump($p_id, $v_id, $qty, $price);
$output = ob_get_clean();
$errorMessage = "Une erreur est survenue :\n" . $output;
error_log($errorMessage);

    static $_wcpf = null;

    // nxover_log("nxover_get_product_ecotax($p_id, $v_id, $qty, $price)");

    if ($_wcpf === null) {
        error_log('coucou200');

        if (! class_exists('WooCommerce_Product_Fees'))
            require NXOVER_WCPF_CLASS_FILE;
            error_log('coucou2000');

        $_wcpf = new WooCommerce_Product_Fees();
    }

    if (

        $_wcpf
        &&
        //NB get_fee_data récupère les fees sur les cart_item
        ($fee = $_wcpf->get_fee_data([
            'id'           => ($v_id ? $v_id : $p_id),
            'variation_id' => $v_id,
            'parent_id'    => $p_id,
            'qty'          => $qty,
            'price'        => $price,
        ]))
        &&
        $fee['name'] == NXOVER_ECOTAX_FEE_NAME
    ) {
        ob_start();  
        // Infos du produit principal
        var_dump($fee);
        $output = ob_get_clean();
        $errorMessage = "coucou3ecotaxfee:\n" . $output;
        error_log($errorMessage);
        return $fee;

    }
    else {
        error_log('coucou400');

        // nxover_log('** nope');
        //         nxover_log($fee);
        //         ob_start();
        //         var_dump($fee['name'], NXOVER_ECOTAX_FEE_NAME);
        //         nxover_log(ob_get_clean());
        return false;
    }
    error_log('ecotaxnxover');

}

function nxover_cart_options_shipping_delay($item, $method)
{
    error_log('coucou8nxover_cart_options_shipping_delay');

    $delay = false;

    if ($options = ($item['yith_wapo_options'] ?? false)) {
        foreach ($options as $opt) {
            if (($d = nxover_option_delay('S', $opt, $method->label, true)) !== false)
                $delay = max($d, (int) $delay);
        }
    }

    return $delay;
}

function nxover_cart_options_manuf_delay($item)
{
    error_log('coucou9nxover_cart_options_manuf_delay');

    nxover_log('** nxover_cart_options_manuf_delay');
    $delay = false;
    // nxover_log('ITEM ON CART ' . print_r($item, true));
    if ($options = ($item['yith_wapo_options'] ?? false)) {
        foreach ($options as $opt) {
            if (($d = nxover_option_delay('M', $opt)) !== false)
                $delay = max($d, (int) $delay);
        }
    }
    return $delay;
}

function nxover_order_options_shipping_delay($item, $method_name, $order_id = null, $add_exp = true)
{
    error_log('coucou30');

    //     nxover_log('nxover_order_options_shipping_delay');

    //     echo "<pre>nxover_order_options_shipping_delay(, $method_name, $order_id, $add_exp)\n";
    //     print_r($item);

    $delay = false;

    foreach ($item->get_meta_data() as $m) {
        //         print_r($m);
        if (nxover_is_ywapo_meta($m->key)) {
            //             nxover_log($m);
            //             echo "WAPO !\n";
            foreach ($m->value as $opt) {
                if (($d = nxover_option_delay('S', $opt, $method_name, false, $order_id, $add_exp)) !== false) {
                    $delay = max($d, (int) $delay);
                    //                     echo "delay = $delay\n";
                }
            }
        }
    }

    //     nxover_log($item->get_meta_data());
    //     echo "</pre>";
    return $delay;
}
function get_product_taxonomy_term_nxover($p_id, $taxonomy)
{
    error_log('coucou10');

    global $wpdb;

    $query = $wpdb->prepare(
        "SELECT terms.name
        FROM {$wpdb->term_relationships} AS rel
        INNER JOIN {$wpdb->term_taxonomy} AS tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
        INNER JOIN {$wpdb->terms} AS terms ON tax.term_id = terms.term_id
        WHERE rel.object_id = %d
        AND tax.taxonomy = %s",
        $p_id,
        $taxonomy
    );

    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}
function nxover_option_delay($type, $opt, $method = '', $vinco = false, $order_id = null, $add_exp = true)
{
    error_log('coucou11nxover_option_delay');

    $delay = false;

    foreach ($opt as $opt_id => $v) {
        $p_id = nxover_addon_id($v);
        if ('product_variation' === get_post_type($p_id)) {
            $parent_id = wp_get_post_parent_id($p_id);
        } else {
            $parent_id = $p_id;
        }

        $delai = intval(preg_replace('/[^0-9.]/', '', wc_get_product_terms($cart_item['product_id'], 'pa_delai-dexpedition', array('fields' => 'names'))[0]));


        nxover_log("delai");
        nxover_log(print_r($delai, true));

        if (($p_id = nxover_addon_id($v)) && ($t = get_the_terms($p_id, 'pa_delai-dexpedition', ['fields' => 'names']))) {
            $d  = (int) preg_replace('/[^0-9.]/', '', $t[0]);

            // echo "   addon $p_id / ".$t[0]." / $d\n";


            if ($type == 'S') {
                // $s  = get_post_meta($p_id, 'supplier', true);

                // if ($vinco && strtolower($s) == 'vinco')
                $d = Livraison::getInstance()->getLivraison($p_id, false, [], $d, $method, $order_id, $add_exp);
            } elseif ($type == 'M')
                $d = Livraison::getInstance()->getFabrication($p_id, false, [], $d);
            else
                continue;

            $delay = max($d, (int) $delay);
        }
        //         else
        //             echo "   ** nope $p_id\n";
    }

    return $delay;
}

/*
function nxover_get_order_item_addons($item)
{
    $addons = [];
    
    foreach($item->get_meta_data() as $m)
    {
//         print_r($m);
        
        if (nxover_is_ywapo_meta($m->key))
        {
            foreach($m->value as $o)
            {
                foreach($o as $v)
                {
                    if (($p_id = nxover_addon_id($v)) && ($p = wc_get_product($p_id)))
                        $addons[$p_id] = clone $p;
                }
            }
        }
    }
    
    return $addons;
}
*/

/*
function nxover_filter_qpdf_item($item, $html)
{
    $sku = [];
    $opt = [];
    
    echo "<pre>meta:\n";
    var_dump($item->get_meta_data(NXOVER_ADDON_META_KEY));
    echo "all:\n";
    var_dump($item->get_all_formatted_meta_data());
    echo "</pre>";
    
    foreach($item->get_meta_data() as $m)
    {
        if ($m->key ==  '_ywraq_wc_ywapo')
        {
            foreach($m->value as $o)
            {
                foreach($o as $v)
                {
                    if ($p_id = nxover_addon_id($v))
                        $opt[$p_id] = 1;
                }
            }
        }

        elseif ($i = stripos($m->key, '(optionnel)'))
        {
            $name = trim(preg_replace('/\d x (.*) \(\+[0-9.]+\s€\)/u', '$1', 
                            html_entity_decode(strip_tags($m->value), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')));
                            
            if ($p = nxover_product_by_name($name))
                $sku[$p->get_id()] = ['sku' => $p->get_sku(), 'name' => $name, 'key' => trim(str_replace(substr($m->key, $i, strlen('(optionnel)')), '', $m->key))];
        }
    }
        
    if ($todo = array_intersect_key($sku, $opt))
    {
        $rowset = explode('<br>', $html);
        foreach($todo as $p)
        {
            foreach($rowset as &$row)
            {                
                if (stripos($row, $p['key'].' (optionnel)') !== false)
                {
                    $row = preg_replace('/\s(\(\+\<span class.+&euro;.+\))/u', ' - '.$p['sku'].' $1', $row);
                    break;
                }
            }
        }
        $html = implode('<br>', $rowset);
    }
    
    return $html;
}
*/

function nxover_filter_qpdf_item($item, $html)
{
    error_log('coucou12');

    $opt = [];
    //     echo "<pre>";

    foreach ($item->get_meta_data() as $m) {
        //         print_r($m);

        if (nxover_is_ywapo_meta($m->key)) {
            foreach ($m->value as $o) {
                foreach ($o as $v) {
                    if ($p_id = nxover_addon_id($v)) {
                        $p = wc_get_product($p_id);
                        $opt[$p_id] = [$p->get_name(), $p->get_sku()];
                    }
                }
            }
        }
    }

    //     echo "--------------------------\n";
    //     print_r($opt);

    $rowset = explode('<br>', $html);

    foreach ($rowset as &$row) {
        foreach ($opt as $p) {
            if (strpos($row, $p[0]) !== false) {
                $row = str_replace($p[0], $p[0] . ' - ' . $p[1], $row);
                break;
            }
        }
    }

    $html = implode('<br>', $rowset);

    //     echo "</pre>";

    return $html;
}

function nxover_is_ywapo_meta($key)
{
    error_log('coucou13');

    static $_meta = ['_ywapo_meta_data', '_ywraq_wc_ywapo'];

    return in_array($key, $_meta);
}

function nxover_product_by_name($name)
{
    error_log('coucou14');

    global $wpdb;

    $post_table = $wpdb->prefix . "posts";
    $result = $wpdb->get_col($sql = '
        SELECT ID
        FROM `' . $wpdb->prefix . 'posts`
        WHERE post_title LIKE \'' . esc_sql($name) . '\'
        AND post_type LIKE \'product\'
    ');

    return (empty($result[0]) ? null : wc_get_product((int) $result[0]));
}

function nxover_addon_id($meta_value)
{
    error_log('coucou15nxover_addon_id');
    ob_start();
    var_dump($meta_value);
    $output = ob_get_clean();
    $errorMessage = "nxoveraddonvalue :\n" . $output;
    error_log($errorMessage);
    $addonId = preg_match('/product-(\d+)-.*/i', $meta_value, $match) ? (int) $match[1] : null;
    // if (gettype($meta_value)=='string'){
    // $addonId = preg_match('/product-(\d+)-.*/i', $meta_value, $match) ? (int) $match[1] : null;
    // }
    // if (gettype($meta_value)=='array'){
    //     foreach ($meta_value as $k=>$v) {
    //         $addonId = preg_match('/product-(\d+)-.*/i', $k, $match) ? (int) $match[1] : null;
    //     }
    //     }
    // Retourne l'id de l'addon
    return $addonId;
}

function nxover_new_order_item($item_id, $cart_item, $cart_item_key) {}

function nxover_woocommerce_checkout_create_order($order, $data) {}

function nxover_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
    error_log('coucou16');

    if (isset($values['yith_wapo_options'])) {
        foreach ($values['yith_wapo_options'] as $opt) {
            foreach ($opt as $v) {
                if ($p_id = nxover_addon_id($v))
                    $item->add_meta_data(NXOVER_ADDON_META_KEY, $p_id);
            }
        }
    }
}

function nxover_ywraq_from_cart_to_order_item($values, $cart_item_key, $item_id, $order)
{
    error_log('coucou17');

    $item = $order->get_item($item_id);

    if (isset($values['yith_wapo_options'])) {
        foreach ($values['yith_wapo_options'] as $opt) {
            foreach ($opt as $v) {
                if ($p_id = nxover_addon_id($v))
                    $item->add_meta_data(NXOVER_ADDON_META_KEY, $p_id);
            }
        }
    }
}

function nxover_update_order_item_meta($meta_id, $object_id, $meta_key, $_meta_value) {}

function nxover_WC_COG__set_item_cost_meta($obj, $item_id, $item_cost, $qty)
{
    error_log('coucou18');

    if (empty($item_cost) || ! is_numeric($item_cost))
        $item_cost = '0';

    // format the single item cost
    $formatted_cost = wc_format_decimal($item_cost);

    // format the total item cost
    $formatted_total = wc_format_decimal($item_cost * $qty);



    try {
        wc_update_order_item_meta($item_id, '_wc_cog_item_cost', $formatted_cost);
        wc_update_order_item_meta($item_id, '_wc_cog_item_total_cost', $formatted_total);
    } catch (\Exception $e) {
    }
}

function nxover_add_new_order_item_cost($item_id, $item, $order_id) {}

function nxover_set_order_cost_meta($order_id, $force = false) {}

function nxover_add_refund_order_costs($refund_id) {}

function nxover_set_item_cost_meta($item_id, $item_cost, $quantity)
{
    error_log('coucou19');

    // NXOVER [[
    //         if (function_exists('nxover_WC_COG__set_item_cost_meta'))
    //             return nxover_WC_COG__set_item_cost_meta($this, $item_id, $item_cost, $quantity);
    // ]] NXOVER

    if (empty($item_cost) || ! is_numeric($item_cost))
        $item_cost = '0';

    // format the single item cost
    $formatted_cost = wc_format_decimal($item_cost);

    // format the total item cost
    $formatted_total = wc_format_decimal($item_cost * $quantity);

    try {
        wc_update_order_item_meta($item_id, '_wc_cog_item_cost', $formatted_cost);
        wc_update_order_item_meta($item_id, '_wc_cog_item_total_cost', $formatted_total);
    } catch (\Exception $e) {
    }
}

function nxover_add_cart_item_data($cart_item_data, $product_id, $post_data = null, $sold_individually = false)
{
    error_log('coucou20');

    if (0/*NXOVER_DEBUG*/) {
        // echo "<pre>U\n";
        // var_dump($post_data);
        // print_r($cart_item_data);
        exit;
    }
}

function nxover_install() {}

function nxover_uninstall() {}

function nxover_log($x)
{
    error_log('coucou21-nxoverlog');

    static $fh = null;

    if (NXOVER_LOG) {
        if ($fh === null)
            $fh = fopen($filename = NXOVER_LOG_DIR . '/plugin.log', 'a');

        //         var_dump($filename, $fh);
        //         exit;

        if ($fh) {
            if (is_object($x) || is_array($x))
                $s = print_r($x, true);
            elseif (is_bool($x))
                $s = ($x ? 'true' : 'false');
            else
                $s = (string) $x;

            fputs($fh, date('Ymd-H:i:s') . "> $s\n");
        }
    }
}
