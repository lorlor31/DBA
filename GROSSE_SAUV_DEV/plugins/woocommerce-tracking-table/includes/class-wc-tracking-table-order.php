<?php
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once WCTT_PATH . 'includes/class-wp-list-table.php';
}

class WC_TrackingTableOrder extends WP_List_Table {

    /**
     * Single instance of WC_TrackingTableOrder
     * @var 	object
     * @access  private
     */
    private static $_instance = null;

    /**
     * Orders list
     * @var 	object
     * @access  private
     */
    private $orders;

    /**
     * Current page
     * @var 	int
     * @access  private
     */
    private $paged = 1;

    /**
     * Limit request
     * @var 	int
     * @access  private
     */
    private $limit = 50;

    /**
     * Prepare the items for the table to process
     *
     * @return WC_TrackingTableOrder
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->paged = $this->get_pagenum();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $this->limit = isset($_REQUEST['plugin-action']) || isset($_REQUEST['search']) ? 2000 : $this->limit;
        $this->set_pagination_args(array(
            'total_items' => $this->orders->total,
            'per_page'    => $this->limit
        ));

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;

        return $this;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        $data = [];
        $status = isset( $_REQUEST['status'] ) && !empty( $_REQUEST['status'] ) ? array($_REQUEST['status']) : array('wc-processing', 'wc-completed', 'wc-partial-shipped');
        if ( isset( $_REQUEST['search'] ) && !empty( $_REQUEST['search'] ) ) {
            $request = trim($_REQUEST['search']);
            $post = get_posts( array(
                'numberposts'    => '-1',
                'meta_key'       => '_ywson_custom_number_order_complete',
                'meta_value'     => $request,
                'meta_compare'   => 'LIKE',
                'post_type'      => 'shop_order',
                'post_status'    => $status
            ) );
            $this->orders = new stdClass();
            $this->orders->max_num_pages = 0;
            if ( sizeof($post) > 0 ) {
                $this->orders->total = sizeof($post);
                foreach ($post as $order) {
                    $this->orders->orders[] = wc_get_order($order->ID);
                }
            } else {
                $this->orders->total = 0;
                $this->orders->orders = [];
            }
        } else if ( isset( $_REQUEST['methods'] ) && !empty( $_REQUEST['methods'] ) ) {
            global $wpdb;
            $methods_list = [
                'cle-en-main'   => 'Formule CLÉ EN MAIN',
                'eco-plus'      => 'Formule ECO Plus',
                'eco-offerte'   => 'Formule ECO [OFFERTE]',
                'eco'   => 'Formule ECO',
                'messagerie'    => 'Messagerie'
            ];
            $orders = $wpdb->get_results(
                $wpdb->prepare( "SELECT item.order_id FROM {$wpdb->prefix}woocommerce_order_items as item INNER JOIN {$wpdb->prefix}posts as post 
                     ON item.order_id = post.ID 
                     WHERE post.post_type = 'shop_order' AND post.post_status RLIKE '%s' AND item.order_item_type = 'shipping' AND item.order_item_name = '%s' AND post.post_date >= '%s'
                     ORDER BY item.order_id DESC", implode('|', $status), $methods_list[$_REQUEST['methods']], date('Y-m-d', strtotime('-12 months')) )
            );
            $this->orders = new stdClass();
            $this->orders->max_num_pages = 0;
            if ( sizeof($orders) > 0 ) {
                $this->orders->total = sizeof($orders);
                foreach ( $orders as $order ) {
                    $this->orders->orders[] = wc_get_order($order->order_id);
                }
            } else {
                $this->orders->total = 0;
                $this->orders->orders = [];
            }
        } else if ( isset($_REQUEST['plugin-action']) && $_REQUEST['plugin-action'] == 'supplier-action' ) {
            $post = get_posts(array(
                'numberposts' => '-1',
                'meta_key' => WC_TrackingTable::$meta_date,
                'meta_value' => '',
                'meta_compare' => '!=',
                'post_type' => 'shop_order',
                'post_status' => array('wc-processing', 'wc-partial-shipped'),
                'date_query' => array(
                    'after' => date('Y-m-d', strtotime('-12 months'))
                )
            ));
            $this->orders = new stdClass();
            $this->orders->max_num_pages = 0;
            if ( sizeof($post) > 0 ) {
                $this->orders->total = sizeof($post);
                foreach ( $post as $order ) {
                    $this->orders->orders[] = wc_get_order($order->ID);
                }
            } else {
                $this->orders->total = 0;
                $this->orders->orders = [];
            }
        } else if ( isset($_REQUEST['plugin-action']) && $_REQUEST['plugin-action'] == 'no-supplier-action' ) {
            $post = get_posts(array(
                'numberposts' => '-1',
                'meta_key' => WC_TrackingTable::$meta_date,
                'meta_compare' => 'NOT EXISTS',
                'post_type' => 'shop_order',
                'post_status' => array('wc-processing', 'wc-partial-shipped'),
                'date_query' => array(
                    'after' => date('Y-m-d', strtotime('-12 months'))
                )
            ));
            $this->orders = new stdClass();
            $this->orders->max_num_pages = 0;
            if ( sizeof($post) > 0 ) {
                $this->orders->total = sizeof($post);
                foreach ( $post as $order ) {
                    $this->orders->orders[] = wc_get_order($order->ID);
                }
            } else {
                $this->orders->total = 0;
                $this->orders->orders = [];
            }
        } else if ( isset($_REQUEST['plugin-action']) && $_REQUEST['plugin-action'] == 'delay-action' ) {
            $orders = wc_get_orders( array(
                'type' => 'shop_order',
                'limit' => '-1',
                'status' => array('wc-processing', 'wc-partial-shipped'),
                'date_query' => array(
                    'after' => date('Y-m-d', strtotime('-12 months'))
                )
            ));
            $i = 0;
            $today = new DateTime('now');
            foreach ( $orders as $order ) {
                $date_exp = null;
                $armoire_date = $this->check_date_shipment( $order );
                if ( is_array($armoire_date) ) {
                    foreach ( $armoire_date as $date ) {
                        if ( $date['supplier'] == 'Vinco' && $date['date_exp'] != 'error' ) {
                            $date_exp = $date['date_exp'];
                            break;
                        }
                    }
                }
                if ( !is_null($date_exp) ) {
                    $date_exp = new DateTime(str_replace('/', '-', $date_exp));
                    $interval = $date_exp->diff($today);
                    if ($interval->format('%R%a') <= 0) {
                        unset($orders[$i]);
                    }
                } else {
                    unset($orders[$i]);
                }
                $i++;
            }
            $this->orders = new stdClass();
            $this->orders->total = sizeof($orders);
            $this->orders->max_num_pages = 0;
            $this->orders->orders = $orders;
            $orders = null;
        } else if ( isset($_REQUEST['plugin-action']) && $_REQUEST['plugin-action'] == 'no-delivery-action' ) {
            global $wpdb;
            $orders = $wpdb->get_results(
                $wpdb->prepare( "SELECT item.order_id FROM {$wpdb->prefix}woocommerce_order_items as item INNER JOIN {$wpdb->prefix}posts as post 
                     ON item.order_id = post.ID 
                     WHERE post.post_type = 'shop_order' AND post.post_status LIKE '%s' AND item.order_item_type = 'shipping' AND item.order_item_name = '%s' AND post.post_date >= '%s'
                     ORDER BY item.order_id DESC", 'wc-completed', 'Formule CLÉ EN MAIN', date('Y-m-d', strtotime('-12 months')) )
            );
            $this->orders = new stdClass();
            $this->orders->total = 0;
            $this->orders->max_num_pages = 0;
            if ( sizeof($orders) > 0 ) {
                foreach ( $orders as $order ) {
                    if ( empty(get_post_meta( $order->order_id, WC_TrackingTable::$meta_delivery, true)) ) {
                        $this->orders->total += 1;
                        $this->orders->orders[] = wc_get_order($order->order_id);
                    }
                }
            } else {
                $this->orders->orders = [];
            }
        } else if ( isset($_REQUEST['plugin-action']) && ($_REQUEST['plugin-action'] == 'follow-action' || $_REQUEST['plugin-action'] == 'litigation-action') ) {
            $search = $_REQUEST['plugin-action'] == 'follow-action' ? 1 : 2;
            $post = get_posts(array(
                'numberposts' => '-1',
                'meta_key' => WC_TrackingTable::$meta_follow,
                'meta_value' => $search,
                'meta_compare' => '=',
                'post_type' => 'shop_order',
                'post_status' => array('wc-processing', 'wc-completed', 'wc-partial-shipped'),
                'date_query' => array(
                    'after' => date('Y-m-d', strtotime('-12 months'))
                )
            ));
            $this->orders = new stdClass();
            $this->orders->max_num_pages = 0;
            if ( sizeof($post) > 0 ) {
                $this->orders->total = sizeof($post);
                foreach ( $post as $order ) {
                    $this->orders->orders[] = wc_get_order($order->ID);
                }
            } else {
                $this->orders->total = 0;
                $this->orders->orders = [];
            }
        } else {
            $this->orders = wc_get_orders( array(
                'type' => 'shop_order',
                'limit' => $this->limit,
                'paged' => $this->paged,
                'status' => $status,
                'paginate' => true
            ));
        }
        foreach ( $this->orders->orders as $order ) {
            $data[] = $this->create_line_order($order);
        }
        return $data;
    }

    /**
     * Prepare the items parser in csv for the table to process
     *
     * @return WC_TrackingTableOrder
     */
    public function prepare_items_csv() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->paged = $this->get_pagenum();

        $import = WC_TrackingTableParser::instance();
        $this->orders = new stdClass();
        $this->orders->orders = $import->getOrders();
        $this->orders->total = sizeof($this->orders->orders);
        $data = [];

        if ( $this->orders->total > 0 ) {
            foreach ( $this->orders->orders as $order ) {
                $data[] = $this->create_line_order($order);
            }
            usort( $data, array( &$this, 'sort_data' ) );
            $this->set_pagination_args(array(
                'total_items' => $this->orders->total,
                'per_page'    => 1000
            ));
        }

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;

        return $this;
    }

    /**
     * Create lines for table orders
     *
     * @return array
     */
    private function create_line_order($order) {
        $line = [];
        // Order name
        $order_name = empty($order->get_billing_last_name()) ? $order->get_order_number() . ' ' . $order->get_billing_company() : $order->get_order_number() . ' ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        if ( !is_null($order->get_date_paid()) ) {
            $date_cmd = new DateTime($order->get_date_paid());
        } else {
            $date_cmd = new DateTime($order->get_date_created());
        }
        $ref_vinco = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_vinco, true );
        $ref_vinco = empty($ref_vinco) ? '' : '[' . $ref_vinco . ']';
        $line['num_commande'] = '<a class="order-view" href="post.php?post=' . $order->get_id() . '&action=edit"><strong>#' . $order_name . ' du ' . $date_cmd->format('d/m/Y') . ' '. $ref_vinco .'</strong></a>';
        // Delivery date
        $date_liv = $this->check_date_delivery( $order );
        if ( is_array($date_liv) ) {
            $line['date_livraison'] = '';
            foreach ( $date_liv as $date ) {
                if ( $date['date_min'] != 'error' ) {
                    $method_id = $date['method_id'];
					$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
					$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
					if (!empty($custom_shipping_date)) {
						$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
						if ($dateObject) {
							$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
							$custom_shipping_date = ucwords($formatter->format($dateObject));
							$expeditionDate = (clone $dateObject)->modify('-7 days');
						}
					}
					if (!empty($custom_shipping_date) || !empty($custom_shipping_text)) {
						// Afficher custom_shipping_date ou custom_shipping_text si disponible
						$line['date_livraison'] .= '<strong>[' . $date['supplier'] . '] ' . str_replace('Formule', '', $date['method']) . '</strong><br><span class="order-date">' . (!empty($custom_shipping_date) ? $custom_shipping_date : $custom_shipping_text) . '</span><hr>';
					} else {
						// Sinon, afficher la date de livraison estimée
						$line['date_livraison'] .= '<strong>[' . $date['supplier'] . '] ' . str_replace('Formule', '', $date['method']) . '</strong><br><span class="order-date">' . $date['delay'] . ' sem. entre ' . $date['date_min'] . ' et ' . $date['date_max'] . '</span><hr>';
					}
                } else {
                    $line['date_livraison'] .= '<strong>[' . $date['supplier'] . '] ' . str_replace('Formule', '', $date['method']) . '</strong><br><span class="order-date alert-text">Erreur d\'estimation</span><hr>';
                }
            }
            $line['date_livraison'] = substr($line['date_livraison'], 0, -4);
        } else {
            $line['date_livraison'] = '<strong class="alert-text">' . $date_liv . '</strong>';
        }
        // Shipping date
        $shipping_date = '';
        $today = new DateTime('now');
        $date_exp = $this->check_date_shipment( $order );	
        $supplier_date = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_date, true );
        if ( $order->get_status() == 'completed' ) {
            delete_post_meta( $order->get_id(), WC_TrackingTable::$meta_date );
            delete_post_meta( $order->get_id(), WC_TrackingTable::$meta_blocked );
            delete_post_meta( $order->get_id(), WC_TrackingTable::$meta_process );
            $date_exp = new DateTime($order->get_date_completed());
            $shipping_date .= '<b data-date="'. $date_exp->format('Ymd') .'"><span class="info-text">' . $date_exp->format('d/m/Y') . '</span></b>';
        } elseif ( !empty($supplier_date) ) {
            if ( is_array($date_exp) ) {
                foreach ( $date_exp as $date ) {
                    if ( $date['supplier'] == 'Vinco' && $date['date_exp'] != 'error' ) {
						if (!empty($custom_shipping_date)) {
                        $estimation_armoirep = $estimation_supplier = new DateTime('now');;
						}else{
                        $estimation_armoirep = new DateTime(str_replace('/', '-', $date['date_exp']));
                        $estimation_supplier = new DateTime(str_replace('/', '-', $supplier_date));
						}
						
						
						
                        $estimation_armoirep = new DateTime(str_replace('/', '-', $date['date_exp']));
                        $estimation_supplier = new DateTime(str_replace('/', '-', $supplier_date));
                        $interval_supplier = $estimation_supplier->diff($today);
                        $interval_armoirep = $estimation_armoirep->diff($today);
                        if ( $interval_armoirep->format('%R%a') > 0 ) {
                            $color = 'alert';
                            $interval = $interval_armoirep->format('%R%a');
                        } else {
                            $color = 'success';
                            if ($interval_supplier->format('%R%a') > 0) {
                                $color = 'warning';
                                $interval = $interval_supplier->format('%R%a');
                            } else {
                                $interval = $interval_armoirep->format('%R%a');
                            }
                        }
						if (!empty($custom_shipping_date)) {
							$interval = $expeditionDate->diff($today);
							$shipping_date .= '<b data-date="'. $estimation_armoirep->format('Ymd') .'" class="' . $color . '-text"><span class="info-text">' . $expeditionDate->format('d/m/Y') . '</span>';
							$shipping_date .= ' <span class="interval ' . $color . '-interval">' . ($interval->format('%R%a')) . ' jour(s)</span></b><hr>';
						}else{
							$shipping_date .= '<b data-date="'. $estimation_armoirep->format('Ymd') .'" class="' . $color . '-text">' . $supplier_date . ' <span class="info-text">(' . $date_exp[0]['date_exp'] . ')</span>';
							$shipping_date .= ' <span class="interval ' . $color . '-interval">' . $interval . ' jour(s)</span></b><hr>';
						}
						

                    } elseif ($date['date_exp'] == 'error'){
						$shipping_date .= '<b ><span class="info-text">PROBLEME DE CALCUL</span></b><hr>';
                    } else {
                        $target = new DateTime(str_replace('/', '-', $date['date_exp']));
                        $interval = $target->diff($today);
                        $color = $interval->format('%R%a') > 0 ? 'warning' : 'success';
                        $shipping_date .= '<b data-date="'. $target->format('Ymd') .'" class="' . $color . '-text"><span class="info-text">' . $date['date_exp'] . '</span>';
                        $shipping_date .= ' <span class="interval ' . $color . '-interval">' . ($interval->format('%R%a')) . ' jour(s)</span></b><hr>';
                    }
                }
                $shipping_date = substr($shipping_date, 0, -4);
            } else {
                $shipping_date .= '<small><i>'. $date_exp .'</i></small><hr>';
            }
        } elseif ( $order->get_status() == 'processing' ) {
            if ( is_array($date_exp) ) {
                foreach ( $date_exp as $date ) {
                    $method_id = $date['method_id'];
					$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
					$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
					if (!empty($custom_shipping_date)) {
						$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
						if ($dateObject) {
							$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
							$custom_shipping_date = ucwords($formatter->format($dateObject));
							$expeditionDate = (clone $dateObject)->modify('-7 days');
						}
					}
                    if ( $date['date_exp'] != 'error' ) {
                        $target = new DateTime(str_replace('/', '-', $date['date_exp']));
                        $interval = $target->diff($today);
                        $color = $interval->format('%R%a') > 0 ? 'warning' : 'success';
						if (!empty($custom_shipping_date)) {
							$interval = $expeditionDate->diff($today);
							$shipping_date .= '<b data-date="'. $target->format('Ymd') .'" class="' . $color . '-text"><span class="info-text">' . $expeditionDate->format('d/m/Y') . '</span>';
							$shipping_date .= ' <span class="interval ' . $color . '-interval">' . ($interval->format('%R%a')) . ' jour(s)</span></b><hr>';
						}elseif (!empty($custom_shipping_text)) {
							$current_date = new DateTime(); // Date actuelle
							$current_year = $current_date->format('Y'); // Année actuelle
							if (preg_match('/semaine (\d+)/', $custom_shipping_text, $matches)) {
								$first_number = $matches[1] ?? null;
								if ($first_number !== null) {
									$new_date = new DateTime();
									$new_date->setISODate($current_year, $first_number);
									if ($new_date < $current_date) {
										$new_date->setISODate($current_year + 1, $first_number);
									}
									$new_date->modify('-5 days');
								}
							} elseif (preg_match('/(\d{1,2} [A-Za-zéû.]+)/', $custom_shipping_text, $matches)) {
								$startDateStr = $matches[1] . ' ' . $current_year;
								$monthReplacements = array(
									"Janv." => "Jan", "Févr." => "Feb", "Mars" => "Mar",
									"Avr." => "Apr", "Mai" => "May", "Juin" => "Jun",
									"Juil." => "Jul", "Août" => "Aug", "Sept." => "Sep",
									"Oct." => "Oct", "Nov." => "Nov", "Déc." => "Dec"
								);
								$startDateStr = str_replace(array_keys($monthReplacements), array_values($monthReplacements), $startDateStr);
								$new_date = DateTime::createFromFormat('d M Y', $startDateStr);
								if ($new_date < $current_date) {
									$new_date->modify('+1 year');
								}
								$new_date->modify('-7 days');
							}
							if (isset($new_date)) {
								$firstDayOfWeek = $new_date->format('d/m/Y');
								$interval = $new_date->diff($today);
								$shipping_date .= '<b data-date="'. $target->format('Ymd') .'" class="' . $color . '-text"><span class="info-text">' . $firstDayOfWeek . '</span>';
								$shipping_date .= ' <span class="interval ' . $color . '-interval">' . ($interval->format('%R%a')) . ' jour(s)</span></b><hr>';
							}
						}else{
							$shipping_date .= '<b data-date="'. $target->format('Ymd') .'" class="' . $color . '-text"><span class="info-text">' . $date['date_exp'] . '</span>';
							$shipping_date .= ' <span class="interval ' . $color . '-interval">' . ($interval->format('%R%a')) . ' jour(s)</span></b><hr>';
						}
                    } else {
                        $shipping_date .= '<small><i>Estimation impossible</i></small><hr>';
                    }
                }
                $shipping_date = substr($shipping_date, 0, -4);
            } else {
                $shipping_date .= '<small><i>'. $date_exp .'</i></small><hr>';
            }
        }
        $line['date_expedition'] = $shipping_date;
        // Supplier blocked
        $is_block = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_blocked, true );
        $is_process = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_process, true );
		$is_fabrication = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_date, true );
        if ( !empty($is_block) && $is_block ) {
           // $line['fdr'] = '<b class="blocked">Bloquée</b>';   //valeur a changer
		   $line['fdr'] = '<b class="blocked">Bloquée</b>';
        } elseif ( !empty($is_process) && $is_process ) {
            //$line['fdr'] = '<b class="process">En préparation</b>';   //valeur a changer
			$line['fdr'] = '<b class="process">Départ imminent</b>';
        } elseif ( !empty($is_fabrication) && $is_fabrication ) {
            //$line['fdr'] = '';   //valeur a changer
			$line['fdr'] = '<b class="fabrication">En fabrication</b>'; 
		}
		else {
			$line['fdr'] ='';
		}
        // Order note
        $line['detail'] = '<div class="order-action">';
        $count = sizeof($order->get_items());
        $order_note = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_note, true );
        $line['detail'] .= '<div class="editor"><a class="link-editor tips" data-id="' . $order->get_id() . '" data-tip="Ajouter ou modifier une note"><img src="' . WCTT_ASSETS . '/icon_tracking-edit.png"></a><span class="order-note input-'.$order->get_id().' tips" data-tip="' . $order_note . '">' . $order_note . '</span></div><hr>';
        // Order status
        $line['detail'] .= '<mark class="order-status status-' . $order->get_status() . '"><span>' . __( ucfirst( $order->get_status() ), 'woocommerce' ) . '</span></mark>';
        // Order total
        $s = $count > 1 ? 's' : '';
        $line['detail'] .= '<div class="total">' . $order->get_formatted_order_total() . '<br><span class="order-items">' . $count . ' article'.$s.'</span></div>';
        // Follow box
        $is_follow = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_follow, true ) ?: 0;
        if ( $is_follow == 2 ) {
            $line['detail'] .= '<div class="follow-' . $order->get_id() . ' is-litigation"><a href="#" data-id="' . $order->get_id() . '" class="button-follow alert-follow tips" data-tip="Modifier le suivi"><img src="' . WCTT_ASSETS . '/icon_tracking-litigation.png"></a></div>';
        } elseif ( $is_follow == 1 ) {
            $line['detail'] .= '<div class="follow-' . $order->get_id() . ' is-follow"><a href="#" data-id="' . $order->get_id() . '" class="button-follow alert-follow tips" data-tip="Modifier le suivi"><img src="' . WCTT_ASSETS . '/icon_tracking-follow.png"></a></div>';
        } else {
            $line['detail'] .= '<div class="follow-' . $order->get_id() . '"><a href="#" data-id="' . $order->get_id() . '" class="button-follow alert-follow tips" data-tip="Modifier le suivi"><img src="' . WCTT_ASSETS . '/icon_tracking-no-follow.png"></a></div>';
        }
        // Delivery box
        if ( $order->get_status() == 'completed' && $this->check_shipping_methods( $order, 'Formule CLÉ EN MAIN' ) ) {
            $is_delivery = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_delivery, true ) ?: 0;
            if ( !empty($is_delivery) ) {
                $line['detail'] .= '<div class="delivery-' . $order->get_id() . ' is-delivery"><a href="#" data-id="' . $order->get_id() . '" class="button-delivery alert-delivery tips" data-tip="Retirer la livraison"><img src="' . WCTT_ASSETS . '/icon_tracking-delivery.png"></a></div>';
            } else {
                $line['detail'] .= '<div class="delivery-' . $order->get_id() . '"><a href="#" data-id="' . $order->get_id() . '" class="button-delivery alert-no-delivery tips" data-tip="Confirmer la livraison"><img src="' . WCTT_ASSETS . '/icon_tracking-no-delivery.png"></a></div>';
            }
        } else {
            $line['detail'] .= '<div class="delivery-' . $order->get_id() . '"><a href="#" data-id="' . $order->get_id() . '" class="button-delivery alert-no-delivery tips disabled" data-tip="Confirmer la livraison"><img src="' . WCTT_ASSETS . '/icon_tracking-no-delivery.png"></a></div>';
        }
        // Alert box
        $count_delay = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_delay, true ) ?: 0;
        $disabled = $order->get_status() != 'processing' ? 'disabled' : '';
        $shipping_email = $order->get_meta('_shipping_email', true);
        // Utiliser l'adresse de livraison si elle existe, sinon utiliser l'adresse de facturation
        $email_to_use = !empty($shipping_email) ? $shipping_email : $billing_email;
        $email =  $email_to_use;
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-alert.php';
        $line['detail'] .= '<div class="delay-' . $order->get_id() . '"><a href="#" data-email="' . $email . '" data-id="' . $order->get_id() . '" data-order="' . $order->get_order_number() . '" class="button-alert alert-delay tips ' . $disabled . '" data-tip="Ouvrir un email de signalement de retard"><img src="' . WCTT_ASSETS . '/icon_tracking-delay.png"></a><br><span class="order-alert"><b class="alert-count">' . $count_delay . '</b> retard(s)</span></div>';
        // Review box
        $count_review = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_review, true ) ?: 0;
        if ( $order->get_status() == 'completed' ) {
            $is_active = $count_review > 0 ? 'no-' : '';
            $disabled = $count_review > 0 ? 'disabled' : '';
        } else {
            $is_active = 'no-';
            $disabled = 'disabled';
        }
        $line['detail'] .= '<div class="bloqued-' . $order->get_id() . '"><a href="#" data-email="' . $email . '" data-id="' . $order->get_id() . '" data-order="' . $order->get_order_number() . '" class="class_for_disable bloqued-review alert-' . $is_active . 'review tips ' . $disabled . '" data-tip="bloquer avis"><img src="' . WCTT_ASSETS . '/Red_Prohibited_sign_No_icon_warning_or_stop_symbol_safety_danger_isolated_vector_illustration.jpg"></a><br><span class="order-alert"><b class="alert-count">' . $count_review . '</b> avis bloqués</span></div>';
        $line['detail'] .= '<div class="review-' . $order->get_id() . '"><a href="#" data-email="' . $email . '" data-id="' . $order->get_id() . '" data-order="' . $order->get_order_number() . '" class="class_for_disable envoie-avis alert-' . $is_active . 'review tips ' . $disabled . '" data-tip="Ouvrir un email avec le lien de demande d\'avis"><img src="' . WCTT_ASSETS . '/icon_tracking-' . $is_active . 'review.png"></a><br><span class="order-alert"><b class="alert-count">' . $count_review . '</b> avis</span></div>';
        $line['detail'] .= '</div>'; // close div.order-action
        return $line;
    }

    /**
     * Override extra_tablenav method.
     *
     * @return WC_TrackingTableOrder
     */
    public function extra_tablenav($which) {
        if ($which == 'top') {
            $search = isset( $_REQUEST['search'] ) ? esc_attr( wp_unslash( $_REQUEST['search'] ) ) : '';
            $methods = isset( $_REQUEST['methods'] ) ? esc_attr( wp_unslash( $_REQUEST['methods'] ) ) : '';
            $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';
            ?>
            <form method="get">
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input">Recherche</label>
                    <input type="search" id="post-search-input" name="search" placeholder="Numéro commande" value="<?= $search ?>" minlength="5" />
                    <select id="post-methods-input" name="methods" style="display:none;">
                        <option value="">Méthode</option>
                        <option value="cle-en-main" <?= ($methods == 'cle-en-main') ? 'selected' : '' ?>>Clé en main</option>
                        <option value="eco-plus" <?= ($methods == 'eco-plus') ? 'selected' : '' ?>>ECO Plus</option>
                        <option value="eco-offerte" <?= ($methods == 'eco-offerte') ? 'selected' : '' ?>>ECO Offerte</option>
                        <option value="eco" <?= ($methods == 'eco') ? 'selected' : '' ?>>ECO</option>
                        <option value="messagerie" <?= ($methods == 'messagerie') ? 'selected' : '' ?>>Messagerie</option>
                    </select>
                    <select id="post-status-input" name="status" style="display:none;">
                        <option value="">Statut</option>
                        <option value="wc-processing" <?= ($status == 'wc-processing') ? 'selected' : '' ?>>En cours</option>
                        <option value="wc-partial-shipped" <?= ($status == 'wc-partial-shipped') ? 'selected' : '' ?>>Partiellement expédiée</option>
                        <option value="wc-completed" <?= ($status == 'wc-completed') ? 'selected' : '' ?>>Terminée</option>
                    </select>
                    <input type="hidden" name="page" value="woocommerce-tracking-table">
                    <?php submit_button( 'OK', '', '', false, array( 'id' => 'search-submit' ) ); ?>
                </p>
            </form>
            <form method="post" action="admin.php?page=<?= WCTT_SLUG ?>">
                <p class="action-box">
                    <select id="post-actions-input" name="plugin-action">
                        <option value="">Sélectionner une action</option>
                        <option value="delay-action">Retard départ usine</option>
                        <option value="supplier-action">Cde Vinco avec Date Maq</option>
                        <option value="no-supplier-action">Cde Vinco sans Date Maq</option>
                        <option value="no-delivery-action">Cde DROP non livrée</option>
                        <option value="follow-action">Cde en favoris (suivi)</option>
                        <option value="litigation-action">Cde en favoris (litige)</option>
                        <option value="reset-track-action" disabled>Supprimer import CSV</option>
                        <option value="reset-follow-action" style="display:none;">Supprimer les favoris</option>
                    </select>
                    <?php submit_button( 'OK', '', '', false, array( 'id' => 'action-submit' ) ); ?>
                </p>
            </form>
            <form method="post" enctype="multipart/form-data" action="admin.php?page=<?= WCTT_SLUG ?>">
                <p class="import-box">
                    <label class="screen-reader-text" for="post-file-input">Importer CSV</label>
                    <input type="file" id="post-file-input" name="i" />
                    <?php submit_button( 'IMPORTER', '', '', false, array( 'id' => 'import-submit' ) ); ?>
                    <span class="woocommerce-help-tip tips" data-tip='<b>Portefeuille :</b><br><small>Dt Maq | Réf Vinco | Réf CMD | Fdr Blq | Dt Fdr</small><hr><b>Reporting :</b><br><small>Réf CMD | Comment</small><hr><b>Auto-detect delimiter</b>'></span>
                </p>
            </form>
            <?php
        }
        return $this;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        return array(
            'num_commande'      => 'Commande',
            'date_livraison'    => 'Date Livraison',
            'date_expedition'   => 'Date Expédition️',
            'fdr'               => 'Fdr',
            'detail'            => 'Détails'
        );
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        if ( !isset($_REQUEST['plugin-action']) ) {
            return array(
                'num_commande'      => array('num_commande', true),
                'date_expedition'   => array('date_expedition', false)
            );
        } else {
            return array();
        }
    }

    /**
     * Get estimation shipment date
     *
     * @return String|Array
     * @throws Exception
     */
    public static function check_date_shipment( $order ) {
        global $wpdb;
        try {
            $drop_order = w_c_dropshipping()->orders->get_order_info($order);
            $shipping_methods = $drop_order['order']->get_shipping_methods();
        } catch (Error $e) {
            return 'Non compatible multi-fournisseur';
        }
        if ( sizeof($shipping_methods) == 0 ) {
            return 'Aucune méthode de livraison';
        }
        $estimate_shipment = [];
        $order_items = $order->get_items();
        foreach ( $shipping_methods as $shipping_method ) {
			$method_id = $shipping_method->get_id();
            $max_delay = 0;
            $supplier_name = '';
            if ( $order->get_date_paid() !== null ) {
                $date_expedition = new DateTime($order->get_date_paid());
                $date_expedition_max = new DateTime($order->get_date_paid()); // juste pour declarer date_expedition_max car ne sert à rien
            } else {
                $date_expedition = new DateTime($order->get_date_created());
                $date_expedition_max = new DateTime($order->get_date_created());
            }
            $method_name = $shipping_method->get_name();
            $shipping_item = $shipping_method->get_meta('Articles');
            foreach ( $order_items as $item ) {
                if ( strpos( $shipping_item, $item->get_name() ) !== false ) {
                    $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                    $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                    $is_variation = $product->is_type('variation') ? true : false;
                    $attributes = $is_variation ? $product->get_variation_attributes() : [];
                    $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                    $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
                    $delay = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                    $delay = Livraison::getInstance()->getFabrication($product->get_id(), $is_variation, $attributes, $delay);
                    $max_delay = max($delay, $max_delay);
                }
                // NXOVER [[
                if (function_exists('nxover_order_options_manuf_delay') && ($o_delay = nxover_order_options_manuf_delay($item, $method_name)) !== false)
                    $max_delay = max($max_delay, $o_delay);
                // ]] NXOVER
            }
			Livraison::getInstance()->fourchette($date_expedition,$date_expedition_max,$max_delay,$supplier_name,$order);
            if ( $max_delay > 0 ) {
                // $date_expedition->add(new DateInterval('P' . $max_delay . 'W'));
                $date_expedition->sub(new DateInterval('P2D'));
                $estimate_shipment[] = array(
				'method_id' => $method_id,
                    'method'    => $method_name,
                    'supplier'  => $supplier_name,
                    'delay'     => $max_delay,
                    'date_exp'  => $date_expedition->format('d/m/Y')
                );
            } else {
                $estimate_shipment[] = array(
				'method_id' => $method_id,
                    'method'    => $method_name,
                    'supplier'  => $supplier_name,
                    'delay'     => 0,
                    // 'date_exp'  => 'error'
                    'date_exp'  => $date_expedition->format('d/m/Y')
                );
            }
        }
        return $estimate_shipment;
    }

    /**
     * Get estimation delivery date
     *
     * @return String|Array
     * @throws Exception
     */
    public static function check_date_delivery( $order ) {
        global $wpdb;
        try {
            $drop_order = w_c_dropshipping()->orders->get_order_info($order);
            $shipping_methods = $drop_order['order']->get_shipping_methods();
        } catch (Error $e) {
            return 'Non compatible multi-fournisseur';
        }
        if ( sizeof($shipping_methods) == 0 ) {
            return 'Aucune méthode de livraison';
        }
        $estimate_delivery = [];
        $order_items = $order->get_items();
        foreach ( $shipping_methods as $shipping_method ) {
			$method_id = $shipping_method->get_id();
            $max_delay = 0;
            $supplier_name = '';
            if ( $order->get_date_paid() !== null ) {
                $date_livraison = new DateTime($order->get_date_paid());
                $date_livraison_max = new DateTime($order->get_date_paid());
            } else {
                $date_livraison = new DateTime($order->get_date_created());
                $date_livraison_max = new DateTime($order->get_date_created());
            }
            $method_name = $shipping_method->get_name();
            $shipping_item = $shipping_method->get_meta('Articles');
            foreach ( $order_items as $item ) {
                if ( strpos( $shipping_item, $item->get_name() ) !== false ) {
                    $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                    $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                    $is_variation = $product->is_type('variation') ? true : false;
                    $attributes = $is_variation ? $product->get_variation_attributes() : [];
                    $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                    $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
                    $delay = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                    $delay = Livraison::getInstance()->getLivraison($product->get_id(), $is_variation, $attributes, $delay, $method_name, $order->get_id());
                    $max_delay = max($delay, $max_delay);
                }
                // NXOVER [[
                if (function_exists('nxover_order_options_shipping_delay') && 
                        ($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
                    $max_delai = max($max_delai, $o_delay);
                // ]] NXOVER
            }
			Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delay,$supplier_name,$order);
			$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
			$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
            if ( $max_delay > 0 ) {
                $estimate_delivery[] = array(
				'method_id' => $method_id,
                    'method'    => $method_name,
                    'supplier'  => $supplier_name,
                    'delay'     => $max_delay,
					'date_min'  => $date_livraison,
                    'date_max'  => $date_livraison_max,
                );
            } else {
                $estimate_delivery[] = array(
				'method_id' => $method_id,
                    'method'    => $method_name,
                    'supplier'  => $supplier_name,
                    'delay'     => 0,
					'date_min'  => $date_livraison,
                    'date_max'  => $date_livraison_max,
                );
            }
        }
        return $estimate_delivery;
    }

    /**
     * Get shipping methods
     *
     * @return bool
     * @throws Exception
     */
    public static function check_shipping_methods( $order, $method ) {
        try {
            $drop_order = w_c_dropshipping()->orders->get_order_info($order);
            $shipping_methods = $drop_order['order']->get_shipping_methods();
        } catch (Error $e) {
            return false;
        }
        foreach ( $shipping_methods as $shipping_method ) {
            if ( $shipping_method->get_name() == $method ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item - Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'num_commande':
            case 'date_livraison':
            case 'date_expedition':
            case 'fdr':
            case 'detail':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'num_commande';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if ( !empty($_GET['orderby']) ) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if ( !empty($_GET['order']) ) {
            $order = $_GET['order'];
        }

        if ( isset($_REQUEST['plugin-action']) ) {
            $orderby = 'date_expedition';
            $order = 'asc';
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if ( $order === 'asc' ) {
            return $result;
        }

        return -$result;
    }

    public static function instance () {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
