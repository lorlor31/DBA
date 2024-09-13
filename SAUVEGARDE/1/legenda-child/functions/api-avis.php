<?php 

// if( !defined( 'ABSPATH' ) )
// die( 'Cheatin\' uh?' );
/*
*Dans ce fichier ce trouve toutes les fonctions en lien avec l'installation de l'api de demandes d'avis client
*/
// if (is_admin()){
// cette fonction gère l'envoie des commandes 
function envoi_commande_avis_v2($order_list_formated){
    // Define API endpoint
    $apiEndPoint = "https://api.guaranteed-reviews.com/private/v3/orders";
    
    $post = $order_list_formated;
    
    // Prepare CURL request 
    $ch = curl_init($apiEndPoint); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 

    // Execute CURL request 
    $response = curl_exec($ch); 
    $decodedResponse = json_decode($response, true);

    // Check if response indicates missing fields and update the message
    if (isset($decodedResponse['success']) && $decodedResponse['success'] == 0 && strpos($decodedResponse['message'], 'Missing fields for array') !== false) {
        $missing_orders_ids = [];
        
        foreach (json_decode($order_list_formated['orders'], true) as $order) {
            if (empty($order['firstname']) || empty($order['lastname'])) {
                $missing_orders_ids[] = $order['id_order'];
            }
        }

        $decodedResponse['message'] .= ' Numéros de commande concernés: ' . implode(', ', $missing_orders_ids);
        $response = json_encode($decodedResponse);
    }

    // Close the connection, release resources used 
    curl_close($ch); 
    return($response);
}
    // cette fonction formatte les commande à envoyer 
    function formattage_commande($order_list){
       $apiKey = "9257/fr/e47d02180e0af593c7cc311cc05be1d4c9a13627d5ff20badf1382163a902096";
       $formatted_orders = array( 
            'api_key' => $apiKey, 
            'orders' => array()
        );
       $i = 0;
       foreach($order_list as $element){
           $order = wc_get_order($element);
           if ($order) {
           // La commande a été trouvée, vous pouvez maintenant accéder à ses propriétés
               $formatted_orders['orders'][] = 
                   array(
                       "id_order" => $order->get_id(),
                       "reference" => $order->get_order_number(),
                       "order_date" => $order->get_date_created()->format('Y-m-d H:i:s'),
                       "total_paid_tax_incl" => $order->get_total(),
                       "firstname" => $order->get_billing_first_name(),
                       "lastname" => $order->get_billing_last_name(),
                       "email" => $order->get_billing_email(),
                       "products" => array()
                   );
               // Ajouter les produits commandés à la commande formatée
				foreach ($order->get_items() as $item_id => $item) {
					$product = $item->get_product();
					
					$product_id = $product->get_id();
					$sku = $product->get_sku();
					$name = $product->get_name();
					$url = get_permalink($product_id);
					
					// If the product is a variation, append the variation attributes to the name
					if ($product->is_type('variation')) {
						$attributes = $product->get_variation_attributes();
						$attr_list = [];
						
						foreach ($attributes as $attr_name => $attr_value) {
							$attr_list[] = wc_attribute_label(str_replace('attribute_', '', $attr_name)) . ': ' . ucfirst($attr_value);
						}

						// Appending attributes to the name
						$name .= ' (' . implode(', ', $attr_list) . ')';
					}

					$formatted_orders['orders'][$i]["products"][] = array(
						"id" => $product_id,
						"ean13" => $sku,
						"upc" => "",
						"name" => $name,
						"url" => $url
					);
				}

           }
           $i++;
       }
       $formatted_orders['orders'] = json_encode($formatted_orders['orders']);
       return $formatted_orders;

    }

    // cette fonction calcul les délais d'expedition pour envoyer les demandes d'avis au plus près apres la livraison
    add_action( 'reviews_woocommerce_send_email_trigger', 'envoi_commande_avis',99999 );
    function envoi_commande_avis(  ) {
		include_once( WP_PLUGIN_DIR . '/woocommerce-tracking-table/woocommerce-tracking-table.php' );
		include_once( WP_PLUGIN_DIR . '/woocommerce-dropshipping/woocommerce-dropshipping.php' );
		$dropshipping_instance = w_c_dropshipping();
		if (!isset($dropshipping_instance->orders)) {
			$dropshipping_instance->init();
		}
        $order_list = array();
        global $wpdb;
        $range = 45; // 45 days
        $delay = [
            'Formule CLÉ EN MAIN'   => 20,
            'Formule CLÉ EN MAIN HARTMANN'   => 15,
            'Formule ECO Plus'      => 10,
            'Formule ECO [OFFERTE]' => 10,
            'Formule ECO' => 10,
            'Messagerie'            => 10,
            'Formule Messagerie HARTMANN'   => 10
        ];
		$default_delay = 20;
        $completed_orders = $wpdb->get_col(
            $wpdb->prepare(
            "SELECT posts.ID
             FROM {$wpdb->prefix}posts AS posts
             WHERE posts.post_type = 'shop_order'
             AND posts.post_status = 'wc-completed'
             AND posts.post_modified >= '%s'
             AND posts.post_modified <= '%s'",
                date( 'Y/m/d H:i:s', absint( strtotime( '-' . absint( $range ) . ' DAYS', current_time( 'timestamp' ) ) ) ),
                date( 'Y/m/d H:i:s', absint( current_time( 'timestamp' ) ) )
            )
        );
        if ( $completed_orders  && class_exists('WC_TrackingTable') && class_exists('WC_Dropshipping') ) {
            $content = '';
            $date_now = new DateTime('now');
            foreach ( $completed_orders as $order_id ) {
                $order = wc_get_order( $order_id );
                try {
                    $drop_order = w_c_dropshipping()->orders->get_order_info($order);
                    $shipping_methods = (array) $drop_order['order']->get_shipping_methods();
                    if ( sizeof($shipping_methods) > 0 && empty(get_post_meta( $order_id, WC_TrackingTable::$meta_review, true )) ) {
                        $delay_sent = 0;
                        $date_sent = new DateTime($order->get_date_completed());
                        foreach ( $shipping_methods as $shipping_method ) {
							$shipping_name = $shipping_method->get_name();
							$current_delay = isset($delay[$shipping_name]) ? $delay[$shipping_name] : $default_delay;
							if ($delay_sent < $current_delay) {
								$delay_sent = $current_delay;
							}
                        }
                        $date_sent->add(new DateInterval('P' . $delay_sent . 'D'));
                        if ( $date_sent <= $date_now ) {
                                $order_list[] = $order_id;
								require_once WCTT_PATH . 'includes/class-wc-tracking-table-alert.php';
								update_post_meta( $order_id, WC_TrackingTable::$meta_review, 1 );

                        }
                    }
                }
                catch (Error $e) {
                $content .= sprintf( __("- <b style='color:#b90d0d;'>Erreur </b> sur #%s : %s,<br><br>"), $order->get_order_number(), $e->getMessage() );
                continue;
                }
            }
        }
        $filter_order_list = verif_comande_bloque($order_list);
        $formatedOrders = formattage_commande($filter_order_list);
        $posted = envoi_commande_avis_v2($formatedOrders);
        $result = wp_mail('ndrouet@armoireplus.fr', 'test',$posted  );
        return $posted;
    }
    
    // permet d'envoyer une commande plus tôt grâce à un boutton sur le tableau de suivi
    function envoi_commande_avis_button() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $order_list = array();
            $order_list[]=$id;
            $filter_order_list = verif_comande_bloque($order_list);
            $formatedOrders = formattage_commande($filter_order_list);
            $posted = envoi_commande_avis_v2($formatedOrders);
            $test = json_encode( $filter_order_list);
            require_once WCTT_PATH . 'includes/class-wc-tracking-table-alert.php';
            update_post_meta( $id, WC_TrackingTable::$meta_review, 1 );
            $result = wp_mail('ndrouet@armoireplus.fr', 'test',$posted  );
            return $posted;
        }
		 wp_die();
    }
      
    add_action( 'wp_ajax_envoi_commande_avis_button', 'envoi_commande_avis_button' );
	add_action( 'wp_ajax_nopriv_envoi_commande_avis_button', 'envoi_commande_avis_button' );
    
    add_action( 'wp_ajax_bloquer_commande_avis_button', 'bloquer_commande_avis_button' );
	add_action( 'wp_ajax_nopriv_bloquer_commande_avis_button', 'bloquer_commande_avis_button' );

    // permet de bloquer la demande d'avis d'une commande grâce à un boutton sur le tableau de suivi
    function bloquer_commande_avis_button() {
        global $wpdb;
        if (isset($_GET['id'])) {
            $order_id = $_GET['id'];
            $nouvelle_valeur = $order_id;
            $nom_table = $wpdb->prefix . 'commande_bloque_avis';
            require_once WCTT_PATH . 'includes/class-wc-tracking-table-alert.php';
            update_post_meta( $order_id, WC_TrackingTable::$meta_review, 1 );
            $commande_bloque = $wpdb->get_col("SELECT orderid FROM $nom_table");
            if (!in_array($nouvelle_valeur,$commande_bloque)){
                $table_mon_tableau = $wpdb->prefix . 'commande_bloque_avis';
                $wpdb->insert(
                    $table_mon_tableau,
                    array(
                        'orderid' => $order_id
                    )
                );
            }
        }
        wp_die();
    }
    function verif_comande_bloque($order_list) {
        global $wpdb;
        $table_mon_tableau = $wpdb->prefix . 'commande_bloque_avis';
		$commande_bloque = array_map('intval', $wpdb->get_col("SELECT orderid FROM $table_mon_tableau"));
		$order_list = array_map('intval', $order_list);
        $communes = array_intersect($commande_bloque, $order_list);
        $filter_order_list = array_diff($order_list, $communes);
        return $filter_order_list;
    }	
	
	
// }