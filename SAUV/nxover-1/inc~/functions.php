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
//     echo "<pre>";
    $fees = [];

    if ($obj->maybe_remove_fees_for_coupon( $cart))
        return $fees;

    foreach($cart->get_cart() as $cart_item => $item)
    {
        error_log(print_r("macléPourRetrouverMaVariable: " . $item, true));        

        // base
        $item_data = [
            'id'           => $item['data']->get_id(),
            'variation_id' => $item['variation_id'],
            'parent_id'    => $item['data']->get_parent_id(),
            'qty'          => $item['quantity'],
            'price'        => $item['data']->get_price()
        ];

        if ($fee = $obj->get_fee_data($item_data))
        {
            $fee_id        = strtolower($fee['name']);
            $fee_tax_class = $obj->get_fee_tax_class($item['data']);
            
            if (isset($fees[$fee_id]) && 'combine' === get_option('wcpf_name_conflicts', 'combine'))
                $fees[$fee_id]['amount'] += $fee['amount'];
            else
                $fees[$fee_id] = apply_filters( 'wcpf_filter_fee_data', [
                        'name'      => $fee['name'],
                        'amount'    => $fee['amount'],
                        'taxable'   => ($fee_tax_class !== '_no_tax'),
                        'tax_class' => $fee_tax_class
					], $item_data);
        }
        
        if ($options = ($item['yith_wapo_options'] ?? false))
        {
            foreach($options as $opt)
            {
                foreach($opt as $v)
                {
                    if ($p_id = nxover_addon_id($v))
                    {
                        $item_data['id']            = $p_id;
                        $item_data['variation_id']  = 0;
                        
                        if ($fee = $obj->get_fee_data($item_data))
                        {
                            $fee_id        = strtolower($fee['name']);
                            $fee_tax_class = $obj->get_fee_tax_class($item['data']);
                            
                            if (isset($fees[$fee_id]))
                                $fees[$fee_id]['amount'] += $fee['amount'];
                            else
                                $fees[$fee_id] = apply_filters( 'wcpf_filter_fee_data', [
                                        'name'      => $fee['name'],
                                        'amount'    => $fee['amount'],
                                        'taxable'   => ($fee_tax_class !== '_no_tax'),
                                        'tax_class' => $fee_tax_class
                                    ], $item_data);
                        }
                    }
                }
            }
        }
	}
	
    return $fees;
}

function nxover_cart_options_shipping_delay($item, $method)
{
    $delay = false;
    
    if ($options = ($item['yith_wapo_options'] ?? false))
    {
        foreach($options as $opt)
        {
            if (($d = nxover_option_delay('S', $opt, $method->label, true)) !== false)
                $delay = max($d, (int) $delay);
        }
    }
    
    return $delay;
}

function nxover_cart_options_manuf_delay($item)
{
    $delay = false;
    
    if ($options = ($item['yith_wapo_options'] ?? false))
    {
        foreach($options as $opt)
        {
            if (($d = nxover_option_delay('M', $opt)) !== false)
                $delay = max($d, (int) $delay);            
        }
    }
    
    return $delay;
}

function nxover_order_options_shipping_delay($item, $method_name, $order_id = null, $add_exp = true)
{
    $delay = false;
    
    foreach($item->get_meta_data() as $m)
    {
        if ($m->key ==  '_ywapo_meta_data')
        {
            foreach($m->value as $opt)
            {
                if (($d = nxover_option_delay('S', $opt, $method_name, false, $order_id, $add_exp)) !== false)
                    $delay = max($d, (int) $delay);
            }
        }
    }
    
    return $delay;
}

function nxover_option_delay($type, $opt, $method = '', $vinco = false, $order_id = null, $add_exp = true)
{
    $delay = false;
    
    foreach($opt as $v)
    {
        if (($p_id = nxover_addon_id($v)) && ($t = wp_get_post_terms($p_id, 'pa_delai-dexpedition', ['fields' => 'names'])))
        {
            $d  = (int) preg_replace('/[^0-9.]/', '', $t[0]);
            
            if ($type = 'S')
            {
                $s  = get_post_meta($p_id, 'supplier', true);
                        
                if ($vinco && strtolower($s) == 'vinco')
                    $d = Livraison::getInstance()->getLivraison($p_id, false, [], $d, $method, $order_id, $add_exp);
            }
            elseif ($type == 'M')
                $d = Livraison::getInstance()->getFabrication($p_id, false, [], $d);
            else
                continue;
                
            $delay = max($d, (int) $delay);
        }
    }
    
    return $delay;
}

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
    $opt = [];
        
    foreach($item->get_meta_data() as $m)
    {
        if ($m->key ==  '_ywraq_wc_ywapo')
        {
            foreach($m->value as $o)
            {
                foreach($o as $v)
                {
                    if ($p_id = nxover_addon_id($v))
                    {
                        $p = wc_get_product($p_id);
                        $opt[$p_id] = [$p->get_name(), $p->get_sku()];
                    }
                }
            }
        }
    }
    
    $rowset = explode('<br>', $html);
    
    foreach($rowset as &$row)
    {                
        foreach($opt as $p)
        {
            if (strpos($row, $p[0]) !== false)
            {
                $row = str_replace($p[0], $p[0].' - '.$p[1], $row);
                break;
            }
        }
    }
    
    $html = implode('<br>', $rowset);
        
    return $html;
}

function nxover_product_by_name($name)
{
    global $wpdb;

    $post_table = $wpdb->prefix . "posts";
    $result = $wpdb->get_col($sql = '
        SELECT ID
        FROM `'.$wpdb->prefix . 'posts`
        WHERE post_title LIKE \''.esc_sql($name).'\'
        AND post_type LIKE \'product\'
    ');

    return (empty($result[0]) ? null : wc_get_product((int) $result[0]));
}

function nxover_addon_id($meta_value)
{
    return (preg_match('/product-(\d+)-.*/i', $meta_value, $match) ? (int) $match[1] : null);
}

function nxover_new_order_item($item_id, $cart_item, $cart_item_key)
{
}

function nxover_woocommerce_checkout_create_order($order, $data)
{
}

function nxover_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
    if (isset($values['yith_wapo_options']))
    {
        foreach($values['yith_wapo_options'] as $opt)
        {
            foreach($opt as $v)
            {
                if ($p_id = nxover_addon_id($v))
                    $item->add_meta_data(NXOVER_ADDON_META_KEY, $p_id);
            }
        }
    }
}

function nxover_ywraq_from_cart_to_order_item($values, $cart_item_key, $item_id, $order)
{
    $item = $order->get_item($item_id);
    
    if (isset($values['yith_wapo_options']))
    {
        foreach($values['yith_wapo_options'] as $opt)
        {
            foreach($opt as $v)
            {
                if ($p_id = nxover_addon_id($v))
                    $item->add_meta_data(NXOVER_ADDON_META_KEY, $p_id);
            }
        }
    }
}

function nxover_install()
{
}

function nxover_uninstall()
{
}

